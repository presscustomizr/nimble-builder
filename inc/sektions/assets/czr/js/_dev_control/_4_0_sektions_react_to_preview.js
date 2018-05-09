//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // invoked on api('ready') from self::initialize()
            // update the main setting OR generate a UI in the panel
            // AND
            // always send back a confirmation to the preview, so we can fire the ajax actions
            // the message sent back is used in particular to
            // - always pass the skope_id, which otherwise would be impossible to get in ajax
            // - in a duplication case, to pass the the newly generated id of the cloned level
            reactToPreviewMsg : function() {
                  var self = this,
                      apiParams = {},
                      uiParams = {},
                      msgCollection = {
                            // UPDATE THE MAIN SETTING
                            'sek-add-section' :{
                                  callback : function( params ) {
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-add-section',
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              location : params.location,
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column )
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        // When a section is created ( not duplicated )
                                        // Send back a msg to the panel to automatically add an initial column in the created sektion
                                        api.previewer.trigger( 'sek-add-column', {
                                              in_sektion : params.apiParams.id,
                                              autofocus : false//<=We want to focus on the section ui in this case, that's why the autofocus is set to false
                                        });
                                        api.previewer.trigger( 'sek-pick-module', {});
                                        api.previewer.send('sek-focus-on', { id : params.apiParams.id });
                                  }
                            },
                            'sek-add-column' : {
                                  callback : function( params ) {
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
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
                                              api.previewer.trigger( 'sek-pick-module', {});
                                        }
                                  }
                            },
                            'sek-add-module' : {
                                  callback :function( params ) {
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              action : 'sek-add-module',
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              module_type : params.content_id,
                                              position : params.position
                                        };
                                        return  self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger('sek-edit-module', {
                                              id : params.apiParams.id,
                                              level : 'module',
                                              in_sektion : params.apiParams.in_sektion,
                                              in_column : params.apiParams.in_column
                                        });
                                  }
                            },
                            'sek-remove' : {
                                  callback : function( params ) {
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                  apiParams = {
                                                        action : 'sek-remove-section',
                                                        id : params.id,
                                                        location : params.location,
                                                        in_sektion : params.in_sektion,
                                                        in_column : params.in_column,
                                                        is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column )
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
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function() {
                                        api.previewer.trigger( 'sek-pick-module', {});
                                  }
                            },
                            'sek-move' : {
                                  callback  : function( params ) {
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                    apiParams = {
                                                          action : 'sek-move-section',
                                                          id : params.id,
                                                          is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                                          newOrder : params.newOrder
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




                            // the level will be cloned and walked to replace all ids by new one
                            // then the level clone id will be send back to the preview for the ajax rendering ( this is done in updateAPISetting() promise() )
                            'sek-duplicate' : {
                                  callback : function( params ) {
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
                                        switch( params.apiParams.action ) {
                                              case 'sek-duplicate-section' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'section',
                                                          in_sektion : params.apiParams.id
                                                    });
                                              break;
                                              case 'sek-duplicate-column' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'column',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                              case 'sek-duplicate-module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          id : params.apiParams.id,
                                                          level : 'module',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                        }
                                        // Refresh the stylesheet to generate the css rules of the clone
                                        api.previewer.send( 'sek-refresh-stylesheet', {
                                              skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        });
                                        // Focus on the cloned level
                                        api.previewer.send('sek-focus-on', { id : params.apiParams.id });
                                  }
                            },
                            'sek-resize-columns' : function( params ) {
                                  uiParams = {};
                                  //console.log( 'panel => reactToPreviewMsg => ', params );
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
                                        uiParams = {};
                                        apiParams = params;
                                        apiParams.action = 'sek-add-content-in-new-sektion';
                                        apiParams.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                        switch( params.content_type) {
                                              // When a module is dropped in a section + column structure to be generated
                                              case 'module' :
                                                    apiParams.droppedModuleId = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                              break;

                                              // When a preset section is dropped
                                              case 'preset_section' :

                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.content_type) {
                                              case 'module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          level : 'module',
                                                          id : params.apiParams.droppedModuleId
                                                    });
                                              break;
                                        }
                                  }
                            },

                            'sek-set-level-options' : {
                                  callback : function( params ) {
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-set-level-options',
                                              options_type : params.options_type,//'spacing', 'layout_background_border'
                                              id : params.id,
                                              value : params.value,
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {}
                            },













                            // GENERATE UI ELEMENTS
                            'sek-pick-module' : function( params ) {
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : 'module'
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-pick-section' : function( params ) {
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : 'section'
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-edit-options' : function( params ) {
                                  //console.log('IN EDIT OPTIONS ', params );
                                  apiParams = {};
                                  if ( _.isEmpty( params.id ) ) {
                                        return $.Deferred( function() {
                                              this.reject( 'missing id' );
                                        });
                                  }
                                  uiParams = {
                                        action : 'sek-generate-level-options-ui',
                                        level : params.level,
                                        id : params.id,
                                        in_sektion : params.in_sektion,
                                        in_column : params.in_column,
                                        options : params.options || []
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-edit-module' : function( params ) {
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
                            //  message : ''
                            //  duration : in ms
                            // }
                            'sek-notify' : function( params ) {
                                  return $.Deferred(function() {
                                        api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                              api.notifications.add( new api.Notification( 'sek-notify', {
                                                    type: params.type || 'info',
                                                    message:  params.message,
                                                    dismissible: true
                                              } ) );

                                              // Removed if not dismissed after 5 seconds
                                              _.delay( function() {
                                                    api.notifications.remove( 'sek-notify' );
                                              }, params.duration || 5000 );
                                        });
                                        this.resolve();
                                  });
                            },

                            'sek-refresh-level' : function( params ) {
                                  return $.Deferred(function() {
                                        apiParams = {
                                              action : 'sek-refresh-level',
                                              level : params.level,
                                              id : params.id
                                        };
                                        uiParams = {};
                                        this.resolve();
                                  });
                            }


                      };//msgCollection

                  // Schedule
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

                              try { _cb_( params )
                                    // the cloneId is passed when resolving the ::updateAPISetting() promise()
                                    // they are needed on level duplication to get the newly generated level id.
                                    .done( function( cloneId ) {
                                          api.previewer.send(
                                                msgId,
                                                {
                                                      skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                      apiParams : apiParams,
                                                      uiParams : uiParams,
                                                      cloneId : ! _.isEmpty( cloneId ) ? cloneId : false
                                                }
                                          );
                                          // say it
                                          self.trigger( [ msgId, 'done' ].join('_'), params );
                                    })
                                    .fail( function( er ) {
                                          api.errare( 'reactToPreviewMsg => error when firing ' + msgId, er );
                                          api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                                api.notifications.add( new api.Notification( 'sek-react-to-preview', {
                                                      type: 'info',
                                                      message:  er,
                                                      dismissible: true
                                                } ) );

                                                // Removed if not dismissed after 5 seconds
                                                _.delay( function() {
                                                      api.notifications.remove( 'sek-react-to-preview' );
                                                }, 5000 );
                                          });

                                    }); } catch( _er_ ) {
                                          api.errare( 'reactToPreviewMsg => error when receiving ' + msgId, _er_ );
                                    }
                          });
                  });


                  // Schedule actions when callback done msg is sent by the preview
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.previewer.bind( [msgId, 'done'].join('_'), function( params ) {
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
                  var cleanIds = function( levelData ) {
                        levelData.id = "";
                        _.each( levelData.collection, function( levelData ) {
                              levelData.id = "";
                              if ( _.isArray( levelData.collection ) ) {
                                    cleanIds( levelData );
                              }
                        });
                        return levelData;
                  };

                  api.previewer.bind( 'sek-to-json', function( params ) {
                        var sectionModel = $.extend( true, {}, self.getLevelModel( params.id ) );
                        popupCenter( JSON.stringify( cleanIds( sectionModel ) ) );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );