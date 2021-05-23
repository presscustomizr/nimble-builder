//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // invoked on api('ready') from self::initialize()
            // update the main setting OR generate a UI in the panel
            // AND
            // always send back a confirmation to the preview, so we can fire the ajax actions
            // the message sent back is used in particular to
            // - always pass the location_skope_id, which otherwise would be impossible to get in ajax
            // - in a duplication case, to pass the the newly generated id of the cloned level
            reactToPreviewMsg : function() {
                  var self = this,
                      apiParams = {},
                      uiParams = {},
                      sendToPreview = true, //<= the default behaviour is to send a message to the preview when the setting has been changed
                      msgCollection = {
                            // A section can be added in various scenarios :
                            // - when clicking on the ( + ) Insert content => @see preview::scheduleUiClickReactions() => addContentButton
                            // - when adding a nested section to a column
                            // - when dragging a module in a 'between-sections' or 'in-empty-location' drop zone
                            //
                            // Note : if the target location level already has section(s), then the section is appended in ajax, at the right place
                            // Note : if the target location is empty ( is_first_section is true ), nothing is send to the preview when updating the api setting, and we refresh the location level. => this makes sure that we removes the placeholder printed in the previously empty location
                            'sek-add-section' : {
                                  callback : function( params ) {
                                        // July 2020 => for #728
                                        api.previewedDevice( 'desktop' );

                                        sendToPreview = ! _.isUndefined( params.send_to_preview ) ? params.send_to_preview : true;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-add-section',
                                              id : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid(),
                                              location : params.location,
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                              before_section : params.before_section,
                                              after_section : params.after_section,
                                              is_first_section : params.is_first_section
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        // When a section is created ( not duplicated )
                                        if ( params.apiParams.is_first_section ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.location
                                              });
                                        }
                                        api.previewer.trigger( 'sek-pick-content', {
                                              // the "id" param is added to set the target for double click insertion
                                              // implemented for https://github.com/presscustomizr/nimble-builder/issues/317
                                              id : params.apiParams ? params.apiParams.id : '',
                                              content_type : 'section'
                                        });
                                        api.previewer.send('sek-animate-to-level', { id : params.apiParams.id });
                                  }
                            },


                            'sek-add-column' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid(),
                                              action : 'sek-add-column',
                                              in_sektion : params.in_sektion,
                                              autofocus : params.autofocus
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        // When adding a section, a nested column is automatically added
                                        // We want to focus on the module picker in this case, that's why the autofocus is set to false
                                        // @see 'sek-add-section' action description
                                        if ( false !== params.apiParams.autofocus ) {
                                              api.previewer.trigger( 'sek-pick-content', {});
                                        }
                                  }
                            },
                            'sek-add-module' : {
                                  callback :function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid(),
                                              action : 'sek-add-module',
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              module_type : params.content_id,

                                              before_module_or_nested_section : params.before_module_or_nested_section,
                                              after_module_or_nested_section : params.after_module_or_nested_section
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-edit-module', {
                                              id : params.apiParams.id,
                                              level : 'module',
                                              in_sektion : params.apiParams.in_sektion,
                                              in_column : params.apiParams.in_column
                                        });
                                        // always update the root fonts property after a module addition
                                        // because there might be a google font specified in the starting value
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });

                                        // Refresh the stylesheet to generate the css rules of the clone
                                        // api.previewer.send( 'sek-refresh-stylesheet', {
                                        //       location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        // });
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              id : params.apiParams.in_column,
                                              location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )//<= send skope id to the preview so we can use it when ajaxing
                                        });
                                  }
                            },
                            'sek-remove' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                  var sektionToRemove = self.getLevelModel( params.id );
                                                  if ( 'no_match' === sektionToRemove ) {
                                                        api.errare( 'reactToPreviewMsg => sek-remove-section => no sektionToRemove matched' );
                                                        break;
                                                  }
                                                  apiParams = {
                                                        action : 'sek-remove-section',
                                                        id : params.id,
                                                        location : params.location,
                                                        in_sektion : params.in_sektion,
                                                        in_column : params.in_column,
                                                        is_nested : sektionToRemove.is_nested
                                                  };
                                              break;
                                              case 'column' :
                                                  apiParams = {
                                                        action : 'sek-remove-column',
                                                        id : params.id,
                                                        in_sektion : params.in_sektion
                                                  };
                                              break;
                                              case 'module' :
                                                  apiParams = {
                                                        action : 'sek-remove-module',
                                                        id : params.id,
                                                        in_sektion : params.in_sektion,
                                                        in_column : params.in_column
                                                  };
                                              break;
                                              default :
                                                  api.errare( '::reactToPreviewMsg => sek-remove => missing level ', params );
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-pick-content', {});
                                        // always update the root fonts property after a removal
                                        // because the removed level(s) might had registered fonts
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });

                                        // When the last section of a location gets removed, make sure we refresh the location level, to print the sek-empty-location-placeholder
                                        if ( 'sek-remove-section' === params.apiParams.action ) {
                                              var locationLevel = self.getLevelModel( params.apiParams.location );
                                              if ( _.isEmpty( locationLevel.collection ) ) {
                                                    api.previewer.trigger( 'sek-refresh-level', {
                                                          level : 'location',
                                                          id :  params.apiParams.location
                                                    });
                                              }
                                        }
                                  }
                            },

                            'sek-move' : {
                                  callback  : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                    apiParams = {
                                                          action : 'sek-move-section',
                                                          id : params.id,
                                                          is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                                          newOrder : params.newOrder,
                                                          from_location : params.from_location,
                                                          to_location : params.to_location
                                                    };
                                              break;
                                              case 'column' :
                                                    apiParams = {
                                                          action : 'sek-move-column',
                                                          id : params.id,
                                                          newOrder : params.newOrder,
                                                          from_sektion : params.from_sektion,
                                                          to_sektion : params.to_sektion,
                                                    };
                                              break;
                                              case 'module' :
                                                    apiParams = {
                                                          action : 'sek-move-module',
                                                          id : params.id,
                                                          newOrder : params.newOrder,
                                                          from_column : params.from_column,
                                                          to_column : params.to_column,
                                                          from_sektion : params.from_sektion,
                                                          to_sektion : params.to_sektion,
                                                    };
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.apiParams.action ) {
                                              case 'sek-move-section' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'section',
                                                          in_sektion : params.apiParams.id
                                                    });
                                                    // refresh location levels if the source and target location are differents
                                                    if ( params.apiParams.from_location != params.apiParams.to_location ) {
                                                          api.previewer.trigger( 'sek-refresh-level', {
                                                                level : 'location',
                                                                id :  params.apiParams.to_location
                                                          });
                                                          api.previewer.trigger( 'sek-refresh-level', {
                                                                level : 'location',
                                                                id :  params.apiParams.from_location
                                                          });
                                                    }
                                              break;
                                              case 'sek-move-column' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'column',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                              case 'sek-refresh-modules-in-column' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          id : params.apiParams.id,
                                                          level : 'module',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                        }
                                  }
                            },//sek-move


                            'sek-move-section-up' : {
                                  callback  : function( params ) {
                                        sendToPreview = false;
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-move-section-up-down',
                                              direction : 'up',
                                              id : params.id,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                              location : params.location,
                                              in_column : params.in_column//<= will be used when moving a nested section
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-refresh-level', {
                                              level : 'location',
                                              id :  params.apiParams.location,

                                              // added for https://github.com/presscustomizr/nimble-builder/issues/471
                                              original_action : 'sek-move-section-up',
                                              moved_level_id : params.apiParams.id
                                        });

                                        // Introduced for https://github.com/presscustomizr/nimble-builder/issues/521
                                        if ( params.apiParams.new_location ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.new_location,

                                                    // added for https://github.com/presscustomizr/nimble-builder/issues/471
                                                    original_action : 'sek-move-section-down',
                                                    moved_level_id : params.apiParams.id
                                              });
                                        }
                                  }
                            },

                            'sek-move-section-down' : {
                                  callback  : function( params ) {
                                        sendToPreview = false;
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-move-section-up-down',
                                              direction : 'down',
                                              id : params.id,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                              location : params.location,
                                              in_column : params.in_column//<= will be used when moving a nested section
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-refresh-level', {
                                              level : 'location',
                                              id :  params.apiParams.location,

                                              // added for https://github.com/presscustomizr/nimble-builder/issues/471
                                              original_action : 'sek-move-section-down',
                                              moved_level_id : params.apiParams.id
                                        });

                                        // Introduced for https://github.com/presscustomizr/nimble-builder/issues/521
                                        if ( params.apiParams.new_location ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.new_location,

                                                    // added for https://github.com/presscustomizr/nimble-builder/issues/471
                                                    original_action : 'sek-move-section-down',
                                                    moved_level_id : params.apiParams.id
                                              });
                                        }
                                  }
                            },

                            // the level will be cloned and walked to replace all ids by new one
                            // then the level clone id will be send back to the preview for the ajax rendering ( this is done in updateAPISetting() promise() )
                            'sek-duplicate' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-section',
                                                          id : params.id,
                                                          location : params.location,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column,
                                                          is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column )
                                                    };
                                              break;
                                              case 'column' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-column',
                                                          id : params.id,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column
                                                    };
                                              break;
                                              case 'module' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-module',
                                                          id : params.id,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column
                                                    };
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        var idForStyleSheetRefresh;
                                        switch( params.apiParams.action ) {
                                              case 'sek-duplicate-section' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'section',
                                                          in_sektion : params.apiParams.id
                                                    });
                                                    idForStyleSheetRefresh = params.apiParams.location;

                                                    //introduced for https://github.com/presscustomizr/nimble-builder/issues/617
                                                    if ( params.apiParams.is_nested ) {
                                                          api.previewer.refresh();
                                                    }

                                                    // Focus on the cloned level
                                                    api.previewer.send('sek-animate-to-level', { id : params.apiParams.id });
                                              break;
                                              case 'sek-duplicate-column' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'column',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                                    idForStyleSheetRefresh = params.apiParams.in_sektion;
                                              break;
                                              case 'sek-duplicate-module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          id : params.apiParams.id,
                                                          level : 'module',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                                    idForStyleSheetRefresh = params.apiParams.in_column;
                                              break;
                                        }
                                        // Refresh the stylesheet to generate the css rules of the clone
                                        // api.previewer.send( 'sek-refresh-stylesheet', {
                                        //       location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        // });
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              id : idForStyleSheetRefresh,
                                              location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )//<= send skope id to the preview so we can use it when ajaxing
                                        });

                                  }
                            },
                            'sek-resize-columns' : function( params ) {
                                  sendToPreview = true;
                                  uiParams = {};
                                  apiParams = params;
                                  return self.updateAPISetting( apiParams );
                            },










                            // @params {
                            //       drop_target_element : $(this),
                            //       position : _position,
                            //       before_section : $(this).data('sek-before-section'),
                            //       after_section : $(this).data('sek-after-section'),
                            //       content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                            //       content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                            // }
                            'sek-add-content-in-new-sektion' : {
                                  callback : function( params ) {
                                        sendToPreview = ! _.isUndefined( params.send_to_preview ) ? params.send_to_preview : true;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = params;
                                        apiParams.action = 'sek-add-content-in-new-sektion';
                                        apiParams.id = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                        switch( params.content_type) {
                                              // When a module is dropped in a section + column structure to be generated
                                              case 'module' :
                                                    apiParams.droppedModuleId = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                              break;

                                              // When a preset section is dropped
                                              case 'preset_section' :
                                                    api.previewer.send( 'sek-maybe-print-loader', { loader_located_in_level_id : params.location });
                                                    api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true });
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.apiParams.content_type) {
                                              case 'module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          level : 'module',
                                                          id : params.apiParams.droppedModuleId
                                                    });
                                              break;
                                              // Clean the full page loader if not autocleaned yet
                                              case 'preset_section' :
                                                    api.previewer.send( 'sek-clean-loader', { cleanFullPageLoader : true });
                                              break;
                                        }

                                        // Always update the root fonts property after a module addition
                                        // => because there might be a google font specified in the starting value or in a preset section
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });

                                        // Refresh the stylesheet to generate the css rules of the clone
                                        // api.previewer.send( 'sek-refresh-stylesheet', {
                                        //       location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        // });

                                        // Use the location_skope_id provided if set, otherwise generate it
                                        var location_skope_id = params.location_skope_id;
                                        if ( _.isUndefined( location_skope_id ) ) {
                                              location_skope_id = true === params.is_global_location ? sektionsLocalizedData.globalSkopeId : api.czr_skopeBase.getSkopeProperty( 'skope_id' );
                                        }
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              //id : params.apiParams.location,
                                              location_skope_id : location_skope_id,//<= send skope id to the preview so we can use it when ajaxing
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });

                                        // Refresh when a section is created ( not duplicated )
                                        if ( params.apiParams.is_first_section ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.location
                                              });
                                        }

                                        // Remove the sektion_to_replace when dropping a preset_section in an empty section ( <= the one to replace )
                                        if ( params.apiParams.sektion_to_replace ) {
                                              api.previewer.trigger( 'sek-remove', {
                                                    id : params.apiParams.sektion_to_replace,
                                                    location : params.apiParams.location,
                                                    in_column : params.apiParams.in_column,//needed when removing a nested column
                                                    level : 'section'
                                              });
                                        }

                                        // Refresh the stylesheet again after a delay
                                        // For the moment, some styling, like fonts are not
                                        // @todo fix => see why we need to do it.
                                        // _.delay( function() {
                                        //       // Refresh the stylesheet to generate the css rules of the module
                                        //       api.previewer.send( 'sek-refresh-stylesheet', {
                                        //             location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        //       });
                                        // }, 1000 );
                                  }
                            },//'sek-add-content-in-new-sektion'


                            // @params {
                            //       drop_target_element : $(this),
                            //       position : _position,
                            //       before_section : $(this).data('sek-before-section'),
                            //       after_section : $(this).data('sek-after-section'),
                            //       content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                            //       content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                            // }
                            'sek-add-preset-section-in-new-nested-sektion' : {
                                  callback : function( params ) {
                                        sendToPreview = false;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = params;
                                        apiParams.action = 'sek-add-preset-section-in-new-nested-sektion';
                                        api.previewer.send( 'sek-maybe-print-loader', { loader_located_in_level_id : params.location });
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        // Always update the root fonts property after a module addition
                                        // => because there might be a google font specified in the starting value or in a preset section
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });
                                        // Refresh the stylesheet to generate the css rules of the clone
                                        // api.previewer.send( 'sek-refresh-stylesheet', {
                                        //       location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        // });
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              id : params.apiParams.in_sektion,
                                              location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )//<= send skope id to the preview so we can use it when ajaxing
                                        });


                                        api.previewer.trigger( 'sek-refresh-level', {
                                              level : 'section',
                                              id :  params.apiParams.in_sektion
                                        });
                                  }
                            },













                            // GENERATE UI ELEMENTS
                            // June 2020 :
                            // When a user creates a new section, the content type switcher is set to section
                            // For all other cases, when user clicks on the + icon, the content type switcher is set to module
                            'sek-pick-content' : function( params ) {
                                  params = _.isObject(params) ? params : {};
                                  // Set the active content type here
                                  // This is used in api.czrInputMap.content_type_switcher()
                                  // Fixes issue https://github.com/presscustomizr/nimble-builder/issues/248
                                  api.czr_sektions.currentContentPickerType = api.czr_sektions.currentContentPickerType || new api.Value();

                                  // Set the last clicked target element id now => will be used for double click insertion of module / section
                                  if ( _.isObject( params ) && params.id ) {
                                        // self reset after a moment.
                                        // @see CZRSeksPrototype::initialize
                                        // implemented for https://github.com/presscustomizr/nimble-builder/issues/317
                                        self.lastClickedTargetInPreview( { id : params.id } );
                                  }

                                  params = params || {};
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : params.content_type || 'module',
                                        // <= the "was_triggered" param can be used to determine if we need to animate the picker control or not. @see ::generateUI() case 'sek-generate-draggable-candidates-picker-ui'
                                        // true by default, because this is the most common scenario ( when adding a section, a column ... )
                                        // but false when clicking on the + ui icon in the preview
                                        was_triggered : _.has( params, 'was_triggered' ) ? params.was_triggered : true,
                                        focus : _.has( params, 'focus' ) ? params.focus : true
                                  };
                                  return self.generateUI( uiParams );
                            },

                            'sek-edit-options' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  if ( _.isEmpty( params.id ) ) {
                                        return $.Deferred( function() {
                                              this.reject( 'missing id' );
                                        });
                                  }
                                  uiParams = {
                                        action : 'sek-generate-level-options-ui',
                                        location : params.location,//<= added June 2020 for https://github.com/presscustomizr/nimble-builder-pro/issues/6
                                        level : params.level,
                                        id : params.id,
                                        in_sektion : params.in_sektion,
                                        in_column : params.in_column,
                                        options : params.options || []
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-edit-module' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-module-ui',
                                        level : params.level,
                                        id : params.id,
                                        in_sektion : params.in_sektion,
                                        in_column : params.in_column,
                                        options : params.options || []
                                  };
                                  return self.generateUI( uiParams );
                            },


                            // OTHER MESSAGE TYPES
                            // @params {
                            //  type : info, error, success
                            //  notif_id : '',
                            //  is_pro_notif: '',
                            //  message : '',
                            //  duration : in ms,
                            //  button_see_me : true
                            // }
                            'sek-notify' : function( params ) {
                                  sendToPreview = false;
                                  var notif_id = params.notif_id || 'sek-notify';
                                  params.button_see_me = _.isUndefined(params.button_see_me) ? true : params.button_see_me;

                                  // Make sure we clean the last printed notification
                                  if ( self.lastNimbleNotificationId ) {
                                        api.notifications.remove( self.lastNimbleNotificationId );
                                  }

                                  return $.Deferred(function() {
                                        api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                              api.notifications.add( new api.Notification( notif_id, {
                                                    type: params.type || 'info',
                                                    message:  params.message,
                                                    dismissible: true
                                              }));

                                              self.lastNimbleNotificationId = notif_id;
                                                var _doThingsWhenRendered = function() {
                                                      if ( params.is_pro_notif ) {
                                                            api.notifications( notif_id ).container.css('background', '#ffff88');
                                                      }
                                                      if ( params.button_see_me ) {
                                                            api.notifications( notif_id ).container.addClass('button-see-me-twice');
                                                            _.delay( function() {
                                                                  api.notifications.container.removeClass('button-see-me-twice');
                                                            }, 2000 );
                                                      }
                                                      api.notifications.unbind('rendered', _doThingsWhenRendered );
                                                };
                                                if ( api.notifications.has( notif_id ) ) {
                                                      api.notifications.bind('rendered', _doThingsWhenRendered );
                                                }
                                              // Removed if not dismissed after 5 seconds
                                              _.delay( function() {
                                                    api.notifications.remove( notif_id );
                                              }, params.duration || 5000 );
                                        });
                                        // always pass the local or global skope of the currently customized location id when resolving the promise.
                                        // It will be send to the preview and used when ajaxing
                                        this.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            },

                            'sek-refresh-level' : function( params ) {
                                  sendToPreview = true;
                                  return $.Deferred(function(_dfd_) {
                                        apiParams = {
                                              action : 'sek-refresh-level',
                                              level : params.level,
                                              id : params.id,

                                              // added for https://github.com/presscustomizr/nimble-builder/issues/471
                                              original_action : params.original_action,
                                              moved_level_id : params.moved_level_id
                                        };
                                        uiParams = {};
                                        // always pass the local or global skope of the currently customized location id when resolving the promise.
                                        // It will be send to the preview and used when ajaxing
                                        _dfd_.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            },

                            'sek-refresh-stylesheet' : function( params ) {
                                  sendToPreview = true;
                                  params = params || {};
                                  return $.Deferred(function(_dfd_) {
                                        apiParams = {id : params.id};
                                        uiParams = {};
                                        // always pass the local or global skope of the currently customized location id when resolving the promise.
                                        // It will be send to the preview and used when ajaxing
                                        _dfd_.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            },

                            // Updated June 2020 for https://github.com/presscustomizr/nimble-builder/issues/520
                            'sek-toggle-save-section-ui' : function( params ) {
                                  sendToPreview = false;
                                  self.idOfSectionToSave = params.id;
                                  self.saveSectionDialogVisible( true );
                                  return $.Deferred(function(_dfd_) {
                                        apiParams = {
                                              // action : 'sek-refresh-level',
                                              // level : params.level,
                                              // id : params.id
                                        };
                                        uiParams = {};
                                        // always pass the local or global skope of the currently customized location id when resolving the promise.
                                        // It will be send to the preview and used when ajaxing
                                        _dfd_.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            },


                              // RESET
                              'sek-reset-collection' : {
                                    callback : function( params ) {
                                          sendToPreview = false;//<= when the level is refreshed when complete, we don't need to send to preview.
                                          uiParams = {};
                                          apiParams = params;
                                          apiParams.action = 'sek-reset-collection';
                                          apiParams.scope = params.scope;
                                          var _dfd_ = self.updateAPISetting( apiParams )
                                                .done( function( resp) {
                                                      api.previewer.refresh();
                                                      api.previewer.trigger('sek-notify', {
                                                            notif_id : 'reset-success',
                                                            type : 'success',
                                                            duration : 8000,
                                                            message : [
                                                                  '<span>',
                                                                  '<strong>',
                                                                  sektionsLocalizedData.i18n['Reset complete'],
                                                                  '</strong>',
                                                                  '</span>'
                                                            ].join('')
                                                      });
                                                      if ( 'local' === params.scope ) {
                                                            var _doThingsAfterRefresh = function() {
                                                                  // INHERITANCE
                                                                  // solves the problem of preventing group template inheritance after a local reset
                                                                  var _is_inheritance_enabled_in_local_options = true,
                                                                        currentSetValue = api( self.localSectionsSettingId() )(),
                                                                        localOptions = currentSetValue.local_options;

                                                                  if ( localOptions && _.isObject(localOptions) && localOptions.local_reset && !_.isUndefined( localOptions.local_reset.inherit_group_scope ) ) {
                                                                        _is_inheritance_enabled_in_local_options = localOptions.local_reset.inherit_group_scope;
                                                                  }
                                                                  //api.infoLog('RESET MAIN LOCAL SETTING ON NEW SKOPES SYNCED', self.localSectionsSettingId() );
                                                                  // Keep only the settings for global option, local options, content picker
                                                                  // Remove all the others
                                                                  // ( local options are removed below )
                                                                  self.cleanRegisteredLevelSettings();

                                                                  // Removes the local sektions setting
                                                                  api.remove( self.localSectionsSettingId() );

                                                                  // RE-register the local sektions setting with values sent from the server
                                                                  // If the local page inherits a group skope, those will be set as local
                                                                  // To prevent saving server sets property __inherits_group_skope_tmpl_when_exists__ = true
                                                                  // set the param { dirty : true } => because otherwise, if user saves right after a reset, local option won't be ::updated() server side.
                                                                  // Which means that the page will keep its previous aspect
                                                                  try { self.setupSettingsToBeSaved( { dirty : true, is_group_inheritance_enabled : _is_inheritance_enabled_in_local_options } ); } catch( er ) {
                                                                        api.errare( 'Error in self.localSectionsSettingId.callbacks => self.setupSettingsToBeSaved()' , er );
                                                                  }

                                                                  api.trigger('nimble-update-topbar-skope-status', { after_reset : true } );

                                                                  // Removes and RE-register local settings and controls
                                                                  self.generateUI({
                                                                        action : 'sek-generate-local-skope-options-ui',
                                                                        clean_settings_and_controls_first : true//<= see self.generateUIforLocalSkopeOptions()
                                                                  });
                                                                  // 'czr-new-skopes-synced' is always sent on a previewer.refresh()
                                                                  api.previewer.unbind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                                                            };
                                                            api.previewer.bind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                                                      }//if ( 'local' === params.scope ) {
                                                })
                                                .fail( function( response ) {
                                                      api.errare( 'reset_button input => error when firing ::updateAPISetting', response );
                                                      api.previewer.trigger('sek-notify', {
                                                            notif_id : 'reset-failed',
                                                            type : 'error',
                                                            duration : 8000,
                                                            message : [
                                                                  '<span>',
                                                                  '<strong>',
                                                                  sektionsLocalizedData.i18n['Reset failed'],
                                                                  '<br/>',
                                                                  '<i>' + response + '</i>',
                                                                  '</strong>',
                                                                  '</span>'
                                                            ].join('')
                                                      });
                                                });

                                          return _dfd_;
                                    },
                                    complete : function( params ) {
                                          // api.previewer.refresh();
                                          // api.previewer.trigger('sek-notify', {
                                          //       notif_id : 'reset-success',
                                          //       type : 'success',
                                          //       duration : 8000,
                                          //       message : [
                                          //             '<span>',
                                          //                   '<strong>',
                                          //                   sektionsLocalizedData.i18n['Reset complete'],
                                          //                   '</strong>',
                                          //             '</span>'
                                          //       ].join('')
                                          // });
                                    }
                              },
                      };//msgCollection

                  // Schedule the reactions
                  // May be send a message to the preview
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.previewer.bind( msgId, function( params ) {
                              var _cb_;
                              if ( _.isFunction( callbackFn ) ) {
                                    _cb_ = callbackFn;
                              } else if ( _.isFunction( callbackFn.callback ) ) {
                                    _cb_ = callbackFn.callback;
                              } else {
                                   api.errare( '::reactToPreviewMsg => invalid callback for action ' + msgId );
                                   return;
                              }

                              // Close template gallery, template saver
                              // do nothing when we notify
                              if ( 'sek-notify' !== msgId ) {
                                  self.templateGalleryExpanded(false);
                                  self.tmplDialogVisible(false);
                              }

                              try { _cb_( params )
                                    // the cloneId is passed when resolving the ::updateAPISetting() promise()
                                    // they are needed on level duplication to get the newly generated level id.
                                    .done( function( promiseParams ) {
                                          promiseParams = promiseParams || {};
                                          // Send to the preview
                                          if ( sendToPreview ) {
                                                var messageToSend = {
                                                      location_skope_id : true === promiseParams.is_global_location ? sektionsLocalizedData.globalSkopeId : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                      local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                                                      apiParams : apiParams,
                                                      uiParams : uiParams,
                                                      cloneId : ! _.isEmpty( promiseParams.cloneId ) ? promiseParams.cloneId : false
                                                }, isError = false;

                                                // when using api.previewer.send, the data are sent as a JSON ( see customize-base.js::send )
                                                // If the object message to send has a circular reference, the JSON.stringify will break ( TypeError: Converting circular structure to JSON )
                                                // fixes https://github.com/presscustomizr/nimble-builder/issues/848
                                                try { JSON.stringify( messageToSend ); } catch( er ) {
                                                      api.errare( 'JSON.stringify problem when executing the callback of ' + msgId, messageToSend );
                                                      isError = true;
                                                }
                                                if ( ! isError ) {
                                                      api.previewer.send(
                                                            msgId,
                                                            messageToSend
                                                      );
                                                }
                                          } else {
                                                // if nothing was sent to the preview, trigger the '*_done' action so we can execute the 'complete' callback
                                                api.previewer.trigger( [ msgId, 'done' ].join('_'), { apiParams : apiParams, uiParams : uiParams } );
                                          }
                                          // say it
                                          self.trigger( [ msgId, 'done' ].join('_'), params );
                                    })
                                    .fail( function( errorMsg ) {
                                          api.errare( 'reactToPreviewMsg => problem or error when running action ' + msgId, errorMsg );
                                          // api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                          //       api.notifications.add( new api.Notification( 'sek-react-to-preview', {
                                          //             type: 'info',
                                          //             message:  errorMsg,
                                          //             dismissible: true
                                          //       } ) );

                                          //       // Removed if not dismissed after 5 seconds
                                          //       _.delay( function() {
                                          //             api.notifications.remove( 'sek-react-to-preview' );
                                          //       }, 5000 );
                                          // });

                                          if ( !_.isEmpty( errorMsg ) && sektionsLocalizedData.isDevMode ) {
                                                api.previewer.trigger('sek-notify', {
                                                      type : 'error',
                                                      duration : 30000,
                                                      message : [
                                                            '<span style="font-size:0.95em">',
                                                              '<strong>' + errorMsg + '</strong>',
                                                              '<br>',
                                                              sektionsLocalizedData.i18n['If this problem locks Nimble Builder, you can try resetting the sections of this page.'],
                                                              '<br>',
                                                              '<span style="text-align:center;display:block">',
                                                                '<button type="button" class="button" aria-label="' + sektionsLocalizedData.i18n.Reset + '" data-sek-reset="true">' + sektionsLocalizedData.i18n.Reset + '</button>',
                                                              '</span>',
                                                            '</span>'
                                                      ].join('')
                                                });
                                          }//if ( sektionsLocalizedData.isDevMode ) {
                                    }); } catch( _er_ ) {
                                          api.errare( 'reactToPreviewMsg => error when receiving ' + msgId, _er_ );
                                    }
                          });
                  });


                  // Schedule actions when callback done msg is sent by the preview
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.previewer.bind( [ msgId, 'done' ].join('_'), function( params ) {
                              if ( _.isFunction( callbackFn.complete ) ) {
                                    try { callbackFn.complete( params ); } catch( _er_ ) {
                                          api.errare( 'reactToPreviewMsg done => error when receiving ' + [msgId, 'done'].join('_') , _er_ );
                                    }
                              }
                        });
                  });
            },//reactToPreview();






            // Fired in initialized on api(ready)
            schedulePrintSectionJson : function() {
                  var self = this;
                  var popupCenter = function ( content ) {
                        w = 400;
                        h = 300;
                        // Fixes dual-screen position                         Most browsers      Firefox
                        var dualScreenLeft = ! _.isUndefined( window.screenLeft ) ? window.screenLeft : window.screenX;
                        var dualScreenTop = ! _.isUndefined( window.screenTop ) ? window.screenTop : window.screenY;

                        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
                        var top = ((height / 2) - (h / 2)) + dualScreenTop;
                        var newWindow = window.open("about:blank", null, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
                        var doc = newWindow.document;
                        doc.open("text/html");
                        doc.write( content );
                        doc.close();
                        // Puts focus on the newWindow
                        if (window.focus) {
                            newWindow.focus();
                        }
                  };

                  api.previewer.bind( 'sek-to-json', function( params ) {
                        var sectionModel = $.extend( true, {}, self.getLevelModel( params.id ) );
                        console.log( JSON.stringify( self.cleanIds( sectionModel ) ) );
                        //popupCenter( JSON.stringify( cleanIds( sectionModel ) ) );
                  });
            }//schedulePrintSectionJson
      });//$.extend()
})( wp.customize, jQuery );