//global sektionsLocalizedData, serverControlParams
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // user action => this utility must be used to set the main setting value
            // params = {
            //    action : 'sek-add-section', 'sek-add-column', 'sek-add-module',...
            //    in_sektion
            //    in_column
            // }
            updateAPISetting : function( params ) {
                  var self = this;

                  // Are we in global location ?
                  // Add the global information to the params
                  // => is used to determine the skope id when resolving the promise in reactToPreviewMsg
                  params = params || {};
                  params.is_global_location = 'global' === params.scope || self.isGlobalLocation( params );

                  var _collectionSettingId_ = params.is_global_location ? self.getGlobalSectionsSettingId() : self.localSectionsSettingId();
                  var currentSetValue = api( _collectionSettingId_ )();

                  self.updAPISetParams = {
                        params : params,
                        promise : $.Deferred(),
                        newSetValue : _.isObject( currentSetValue ) ? $.extend( true, {}, currentSetValue ) : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ),
                        cloneId : '' // the cloneId is only needed in the duplication scenarii
                  };

                  var _do_update_setting_id = function() {
                        // api( _collectionSettingId_)() = {
                        //    collection : [
                        //       'loop_start' :  { level : location,  collection : [ 'sek124' : { collection : [], level : section, options : {} }], options : {}},
                        //       'loop_end' : { level : location, collection : [], options : {}}
                        //        ...
                        //    ],
                        //    options : {}
                        //
                        // }
                        var locationCandidate,
                            columnCandidate,
                            moduleCandidate,
                            // move variables
                            //duplication variable
                            __presetSectionInjected__ = '_not_injection_scenario_',//this property is turned into a $.Deferred() object in a scenario of section injection
                            parentSektionCandidate;

                        // make sure we have a collection array to populate
                        self.updAPISetParams.newSetValue.collection = _.isArray( self.updAPISetParams.newSetValue.collection ) ? self.updAPISetParams.newSetValue.collection : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ).collection;

                        switch( params.action ) {
                              //-------------------------------------------------------------------------------------------------
                              //-- SEKTION
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-section' :
                                    self._updAPISet_sek_add_sektion();
                              break;
                              case 'sek-duplicate-section' :
                                    self._updAPISet_sek_duplicate_sektion();
                              break;
                              case 'sek-remove-section' :
                                    self._updAPISet_sek_remove_sektion();
                              break;
                              case 'sek-move-section' :
                                    self._updAPISet_sek_move_sektion();
                              break;
                              // Fired on click on up / down arrows in the section ui menu
                              // This handles the nested sections case
                              case 'sek-move-section-up-down' :
                                    self._updAPISet_sek_move_sektion_up_down();
                              break;


                              //-------------------------------------------------------------------------------------------------
                              //-- COLUMN
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-column' :
                                    self._updAPISet_sek_add_column();
                              break;
                              case 'sek-remove-column' :
                                    self._updAPISet_sek_remove_column();
                              break;
                              case 'sek-duplicate-column' :
                                    self._updAPISet_sek_duplicate_column();
                              break;
                              // Note : the css rules are generated in Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
                              case 'sek-resize-columns' :
                                    self._updAPISet_sek_resize_column();
                              break;
                              case 'sek-move-column' :
                                    self._updAPISet_sek_move_column();
                              break;


                              //-------------------------------------------------------------------------------------------------
                              //-- MODULE
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-module' :
                                    self._updAPISet_sek_add_module();
                              break;
                              case 'sek-duplicate-module' :
                                    self._updAPISet_sek_duplicate_module();
                              break;
                              case 'sek-remove-module' :
                                    self._updAPISet_sek_remove_module();
                              break;
                              case 'sek-move-module' :
                                    self._updAPISet_sek_move_module();
                              break;
                              case 'sek-set-module-value' :
                                    self._updAPISet_sek_set_module_value();
                              break;






                              //-------------------------------------------------------------------------------------------------
                              //-- LEVEL OPTIONS
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-generate-level-options-ui' :
                                    var _candidate_ = self.getLevelModel( params.id, self.updAPISetParams.newSetValue.collection ),
                                        _valueCandidate = {};

                                    if ( 'no_match'=== _candidate_ ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }
                                    // start from a deep cloned object
                                    // important => fixes https://github.com/presscustomizr/nimble-builder/issues/455
                                    var _new_options_values = $.extend( true, {}, _candidate_.options || {} );

                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _valueCandidate[ _key_ ] = _val_;
                                    });

                                    if ( _.isEmpty( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                    }

                                    _new_options_values[ params.options_type ] = _valueCandidate;
                                    _candidate_.options = _new_options_values;
                              break;





                              //-------------------------------------------------------------------------------------------------
                              //-- LOCAL SKOPE OPTIONS
                              //-------------------------------------------------------------------------------------------------
                              // Note : this is saved in "local_options"
                              case 'sek-generate-local-skope-options-ui' :
                                    _valueCandidate = {};

                                    var _currentOptions = $.extend( true, {}, _.isObject( self.updAPISetParams.newSetValue.local_options ) ? self.updAPISetParams.newSetValue.local_options : {} );
                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _valueCandidate[ _key_ ] = _val_;
                                    });
                                    if ( _.isEmpty( params.options_type ) || ! _.isString( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                    } else {
                                          var newOptionsValues = {};
                                          newOptionsValues[ params.options_type ] = _valueCandidate;
                                          self.updAPISetParams.newSetValue.local_options = $.extend( _currentOptions, newOptionsValues );
                                    }
                              break;






















                              //-------------------------------------------------------------------------------------------------
                              //-- CONTENT IN NEW SEKTION
                              //-------------------------------------------------------------------------------------------------
                              // @params {
                              //   drop_target_element : $(this),
                              //   position : _position,// <= top or bottom
                              //   before_section : $(this).data('sek-before-section'),
                              //   after_section : $(this).data('sek-after-section'),
                              //   content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ), //<= module or preset_section
                              //   content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                              // }
                              case 'sek-add-content-in-new-sektion' :
                                    // get the position of the before or after section
                                    var positionIndex = 0,
                                        startingModuleValue;
                                    locationCandidate = self.getLevelModel( params.location, self.updAPISetParams.newSetValue.collection );
                                    if ( 'no_match' == locationCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                          self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                          break;
                                    }
                                    locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                    _.each( locationCandidate.collection, function( secModel, index ) {
                                          if ( params.before_section === secModel.id ) {
                                                positionIndex = index;
                                          }
                                          if ( params.after_section === secModel.id ) {
                                                positionIndex = index + 1;
                                          }
                                    });

                                    switch( params.content_type) {
                                          // When a module is dropped in a section + column structure to be generated
                                          case 'module' :
                                                // Let's add the starting value if provided when registrating the module
                                                // Note : params.content_id is the module_type
                                                startingModuleValue = self.getModuleStartingValue( params.content_id );

                                                // insert the section in the collection at the right place
                                                locationCandidate.collection.splice( positionIndex, 0, {
                                                      id : params.id,
                                                      level : 'section',
                                                      collection : [
                                                            {
                                                                  id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                                  level : 'column',
                                                                  collection : [
                                                                        {
                                                                              id : params.droppedModuleId,
                                                                              level : 'module',
                                                                              module_type : params.content_id,
                                                                              value : 'no_starting_value' !== startingModuleValue ? startingModuleValue : null,
                                                                              ver_ini : sektionsLocalizedData.nimbleVersion
                                                                        }
                                                                  ],
                                                                  ver_ini : sektionsLocalizedData.nimbleVersion
                                                            }
                                                      ],
                                                      ver_ini : sektionsLocalizedData.nimbleVersion
                                                });
                                          break;//case 'module' :

                                          // When a preset section is dropped
                                          case 'preset_section' :
                                                // insert the section in the collection at the right place
                                                __presetSectionInjected__ = $.Deferred();//defined at the beginning of the method

                                                // we use a section index here to display the section in the same order as in the json
                                                // introduced when implementing multi-section pre-build section
                                                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                                var _injectPresetSectionInLocationOrParentColumn = function( sectionReadyToInject, positionIndex ) {
                                                      // If the preset_section is inserted in a an empty nested section, add it at the right place in the parent column of the nested section.
                                                      // Otherwise, add the preset section at the right position in the parent location of the section.
                                                      var insertedInANestedSektion = false;
                                                      if ( ! _.isEmpty( params.sektion_to_replace ) ) {
                                                            var sektionToReplace = self.getLevelModel( params.sektion_to_replace, self.updAPISetParams.newSetValue.collection );
                                                            if ( 'no_match' === sektionToReplace ) {
                                                                  api.errare( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched' );
                                                                  self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched');
                                                            }
                                                            insertedInANestedSektion = true === sektionToReplace.is_nested;
                                                      }


                                                      // The following param "collection_of_preset_section_id" has been introduced when implementing support for multi-section pre-build sections
                                                      // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                                      // It is sent to the preview with ::reactToPreviewMsg, see bottom of the method.
                                                      var injected_section_id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                                                      params.collection_of_preset_section_id = params.collection_of_preset_section_id || [];
                                                      params.collection_of_preset_section_id.push( injected_section_id );

                                                      if ( ! insertedInANestedSektion ) {
                                                            locationCandidate.collection.splice( positionIndex, 0, {
                                                                  id : injected_section_id,//params.id,//self.guid()
                                                                  level : 'section',
                                                                  collection : sectionReadyToInject.collection,
                                                                  options : sectionReadyToInject.options || {},
                                                                  ver_ini : sektionsLocalizedData.nimbleVersion
                                                            });
                                                      } else {
                                                            columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                                                            if ( 'no_match' === columnCandidate ) {
                                                                  api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                                  self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                            }

                                                            columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                                            // get the position of the before or after module
                                                            _.each( columnCandidate.collection, function( moduleOrSectionModel, index ) {
                                                                  if ( params.before_section === moduleOrSectionModel.id ) {
                                                                        positionIndex = index;
                                                                  }
                                                                  if ( params.after_section === moduleOrSectionModel.id ) {
                                                                        positionIndex = index + 1;
                                                                  }
                                                            });

                                                            columnCandidate.collection.splice( positionIndex, 0, {
                                                                  id : injected_section_id,
                                                                  is_nested : true,
                                                                  level : 'section',
                                                                  collection : sectionReadyToInject.collection,
                                                                  options : sectionReadyToInject.options || {},
                                                                  ver_ini : sektionsLocalizedData.nimbleVersion
                                                            });
                                                      }
                                                };//_injectPresetSectionInLocationOrParentColumn
                                                var _doWhenPresetSectionCollectionFetched = function( presetColumnOrSectionCollection ) {
                                                      self.preparePresetSectionForInjection( presetColumnOrSectionCollection )
                                                            .fail( function( _er_ ){
                                                                  self.updAPISetParams.promise.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                                                  // Used when updating the setting
                                                                  // @see end of this method
                                                                  __presetSectionInjected__.reject( _er_ );
                                                            })
                                                            .done( function( maybeMultiSectionReadyToInject ) {
                                                                  var is_multi_section = 'section' === maybeMultiSectionReadyToInject.collection[0].level;
                                                                  // support for pre-built multi-section has been introduced in july 2019
                                                                  // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                                                  if ( is_multi_section ) {
                                                                        // we use a section index here to display the section in the same order as in the json
                                                                        var sectionIndex = 0;
                                                                        _.each( maybeMultiSectionReadyToInject.collection, function( sectionReadyToInject ) {
                                                                              _injectPresetSectionInLocationOrParentColumn( sectionReadyToInject, positionIndex );
                                                                              positionIndex++;
                                                                        });
                                                                  } else {
                                                                        _injectPresetSectionInLocationOrParentColumn( maybeMultiSectionReadyToInject, positionIndex );
                                                                  }

                                                                  // Used when updating the setting
                                                                  // @see end of this method
                                                                  __presetSectionInjected__.resolve();
                                                            });// self.preparePresetSectionForInjection.done()
                                                };//_doWhenPresetSectionCollectionFetched()

                                                // Try to fetch the sections from the server
                                                // if sucessfull, resolve __presetSectionInjected__.promise()
                                                self.getPresetSectionCollection({
                                                            is_user_section : params.is_user_section,
                                                            presetSectionId : params.content_id
                                                      })
                                                      .fail( function( _er_ ) {
                                                            api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                            self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                                      })
                                                      .done( function( presetColumnOrSectionCollection ) {
                                                            if ( ! _.isObject( presetColumnOrSectionCollection ) || _.isEmpty( presetColumnOrSectionCollection ) ) {
                                                                  api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnOrSectionCollection );
                                                                  self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                            }
                                                            // OK. time to resolve __presetSectionInjected__.promise()
                                                            _doWhenPresetSectionCollectionFetched( presetColumnOrSectionCollection );
                                                      });//self.getPresetSectionCollection().done()

                                          break;//case 'preset_section' :
                                    }//switch( params.content_type)
                              break;




















                              //-------------------------------------------------------------------------------------------------
                              //-- CONTENT IN NEW NESTED SEKTION
                              //-------------------------------------------------------------------------------------------------
                              // @params {
                              //   drop_target_element : $(this),
                              //   position : _position,// <= top or bottom
                              //   before_section : $(this).data('sek-before-section'),
                              //   after_section : $(this).data('sek-after-section'),
                              //   content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ), //<= module or preset_section
                              //   content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                              // }
                              case 'sek-add-preset-section-in-new-nested-sektion' :

                                    columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                                    if ( 'no_match' === columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    var positionIndexInColumn = 0;
                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                     // get the position of the before or after module or nested section
                                    _.each( columnCandidate.collection, function( moduleOrNestedSectionModel, index ) {
                                          if ( params.before_module_or_nested_section === moduleOrNestedSectionModel.id ) {
                                                positionIndexInColumn = index;
                                          }
                                          if ( params.after_module_or_nested_section === moduleOrNestedSectionModel.id ) {
                                                positionIndexInColumn = index + 1;
                                          }
                                    });

                                    // can we add this nested sektion ?
                                    // if the parent sektion of the column has is_nested = true, then we can't
                                    parentSektionCandidate = self.getLevelModel( params.in_sektion, self.updAPISetParams.newSetValue.collection );
                                    if ( 'no_match' == parentSektionCandidate ) {
                                          self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                          break;
                                    }
                                    if ( true === parentSektionCandidate.is_nested ) {
                                          self.updAPISetParams.promise.reject('');
                                          api.previewer.trigger('sek-notify', {
                                                type : 'info',
                                                duration : 30000,
                                                message : [
                                                      '<span style="font-size:0.95em">',
                                                        '<strong>' + sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ] + '</strong>',
                                                      '</span>'
                                                ].join('')
                                          });
                                          break;
                                    }

                                    // insert the nested section in the collection at the right place
                                    var presetColumnOrSectionCollection;
                                    __presetSectionInjected__ = $.Deferred();//defined at the beginning of the method

                                    var _doWhenPrebuiltSectionCollectionFetched = function( presetColumnOrSectionCollection ) {
                                          self.preparePresetSectionForInjection( presetColumnOrSectionCollection )
                                                .fail( function( _er_ ){
                                                      self.updAPISetParams.promise.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                                      // Used when updating the setting
                                                      // @see end of this method
                                                      __presetSectionInjected__.reject( _er_ );
                                                })
                                                .done( function( maybeMultiSectionReadyToInject ) {

                                                      var _injectNestedSectionInParentColumn = function( sectionReadyToInject, positionIndexInColumn  ) {
                                                            positionIndexInColumn = positionIndexInColumn || 0;

                                                            // The following param "collection_of_preset_section_id" has been introduced when implementing support for multi-section pre-build sections
                                                            // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                                            // It is sent to the preview with ::reactToPreviewMsg, see bottom of the method.
                                                            var injected_section_id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                                                            params.collection_of_preset_section_id = params.collection_of_preset_section_id || [];
                                                            params.collection_of_preset_section_id.push( injected_section_id );

                                                            columnCandidate.collection.splice( positionIndexInColumn, 0, {
                                                                  id : injected_section_id,
                                                                  level : 'section',
                                                                  collection : sectionReadyToInject.collection,
                                                                  options : sectionReadyToInject.options || {},
                                                                  is_nested : true,
                                                                  ver_ini : sektionsLocalizedData.nimbleVersion
                                                            });
                                                      };

                                                      // support for pre-built multi-section has been introduced in july 2019
                                                      // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                                      var is_multi_section = 'section' === maybeMultiSectionReadyToInject.collection[0].level;
                                                      if ( is_multi_section ) {
                                                            _.each( maybeMultiSectionReadyToInject.collection, function( sectionReadyToInject ) {
                                                                  _injectNestedSectionInParentColumn( sectionReadyToInject, positionIndexInColumn );
                                                                  positionIndexInColumn++;
                                                            });
                                                      } else {
                                                            _injectNestedSectionInParentColumn( maybeMultiSectionReadyToInject, positionIndexInColumn );
                                                      }

                                                      // Used when updating the setting
                                                      // @see end of this method
                                                      __presetSectionInjected__.resolve();
                                                });//self.preparePresetSectionForInjection.done()
                                    };//_doWhenPrebuiltSectionCollectionFetched


                                    // Try to fetch the sections from the server
                                    // if sucessfull, resolve __presetSectionInjected__.promise()
                                    self.getPresetSectionCollection({
                                                is_user_section : params.is_user_section,
                                                presetSectionId : params.content_id
                                          })
                                          .fail( function() {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                          })
                                          .done( function( presetColumnOrSectionCollection ) {
                                                if ( ! _.isObject( presetColumnOrSectionCollection ) || _.isEmpty( presetColumnOrSectionCollection ) ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnOrSectionCollection );
                                                      self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                }
                                                // OK. time to resolve __presetSectionInjected__.promise()
                                                _doWhenPrebuiltSectionCollectionFetched( presetColumnOrSectionCollection );
                                          });//self.getPresetSectionCollection().done()
                              break;









                              //-------------------------------------------------------------------------------------------------
                              //-- POPULATE GOOGLE FONTS
                              //-------------------------------------------------------------------------------------------------
                              //@params {
                              //       action : 'sek-update-fonts',
                              //       font_family : newFontFamily,
                              // }
                              case 'sek-update-fonts' :
                                    // Get the gfonts from the level options and modules values
                                    var currentGfonts = self.sniffGFonts( { is_global_location : ( params && true === params.is_global_location ) } );

                                    // add it only if gfont
                                    if ( ! _.isEmpty( params.font_family ) && _.isString( params.font_family )  ) {
                                          if ( params.font_family.indexOf('gfont') > -1 && ! _.contains( currentGfonts, params.font_family ) ) {
                                                currentGfonts.push( params.font_family );
                                          }
                                    }

                                    // update the global gfonts collection
                                    // this is then used server side in Sek_Dyn_CSS_Handler::sek_get_gfont_print_candidates to build the Google Fonts request
                                    self.updAPISetParams.newSetValue.fonts = currentGfonts;
                              break;

                              //-------------------------------------------------------------------------------------------------
                              //-- RESTORE A REVISION
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-restore-revision' :
                                    //api.infoLog( 'sek-restore-revision', params );
                                    self.updAPISetParams.newSetValue = params.revision_value;
                              break;

                              //-------------------------------------------------------------------------------------------------
                              //-- FILE IMPORT
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-import-from-file' :
                                    api.infoLog( 'sek-import-from-file', params );
                                    if ( _.isUndefined( params.imported_content.data ) || _.isUndefined( params.imported_content.metas ) ) {
                                          api.errare( 'updateAPISetting::sek-import-from-file => invalid imported content', imported_content );
                                          break;
                                    }

                                    var importedCollection = _.isArray( params.imported_content.data.collection ) ? $.extend( true, [], params.imported_content.data.collection ) : [];

                                    // SHALL WE ASSIGN SECTIONS FROM MISSING LOCATIONS TO THE FIRST ACTIVE LOCATION ?
                                    // For example the current page has only the 'loop_start' location, whereas the imported content includes 3 locations :
                                    // - after_header
                                    // - loop_start
                                    // - before_footer
                                    // Among those 3 locations, 2 are not active in the page.
                                    // We will merge all section collections from the 3 imported locations one new collection, that will be assigned to 'loop_start'
                                    // Note that the active imported locations are ordered like they were on the page when exported.
                                    //
                                    // So :
                                    // 1) identify the first active location of the page
                                    // 2) populate a new collection of combined sections from all active imported locations.
                                    // 3) updated the imported collection with this
                                    if ( true === params.assign_missing_locations ) {
                                          var importedActiveLocations = params.imported_content.metas.active_locations,
                                              currentActiveLocations = api.czr_sektions.activeLocations();

                                          // console.log('Current set value ?', api( _collectionSettingId_ )() );
                                          // console.log('import params', params );
                                          // console.log('importedCollection?', importedCollection );
                                          // console.log('importedActiveLocations', importedActiveLocations );

                                          // first active location of the current setting
                                          var firstCurrentActiveLocationId = _.first( currentActiveLocations );

                                          if ( !_.isEmpty( firstCurrentActiveLocationId ) && !_.isEmpty( importedActiveLocations ) && _.isArray( importedActiveLocations ) ) {
                                                // importedActiveLocationsNotAvailableInCurrentActiveLocations
                                                // Example :
                                                // active location in the page : loop_start, loop_end
                                                // active locations imported : after_header, loop_start, before_footer
                                                // importedActiveLocationsNotAvailableInCurrentActiveLocations => after_header, before_footer
                                                var importedActiveLocationsNotAvailableInCurrentActiveLocations = $(importedActiveLocations).not(currentActiveLocations).get(),
                                                    firstCurrentLocationData = self.getLevelModel( firstCurrentActiveLocationId, self.updAPISetParams.newSetValue.collection ),
                                                    importedTargetLocationData = self.getLevelModel( firstCurrentActiveLocationId, params.imported_content.data.collection ),
                                                    newCollectionForTargetLocation = [];// the collection that will hold the merge of all active imported collections

                                                // normalize
                                                // => make sure we have a collection array, even empty
                                                firstCurrentLocationData.collection = _.isArray( firstCurrentLocationData.collection ) ? firstCurrentLocationData.collection : [];
                                                importedTargetLocationData.collection = _.isArray( importedTargetLocationData.collection ) ? importedTargetLocationData.collection : [];

                                                // loop on the active imported locations
                                                // Example : ["__after_header", "__before_main_wrapper", "loop_start", "__before_footer"]
                                                // and populate newCollectionForTargetLocation, with locations ordered as they were on export
                                                // importedCollection is a clone
                                                _.each( importedActiveLocations, function( impLocationId ){
                                                      var impLocationData = self.getLevelModel( impLocationId, importedCollection );
                                                      if ( _.isEmpty( impLocationData.collection ) )
                                                        return;
                                                      newCollectionForTargetLocation = _.union( newCollectionForTargetLocation, impLocationData.collection );
                                                });//_.each( importedActiveLocations

                                                // replace the previous collection of the target location, by the union of all collections.
                                                // for example, if 'loop_start' is the target location, all sections will be added to it.
                                                importedTargetLocationData.collection = newCollectionForTargetLocation;

                                                // remove the missing locations from the imported collection
                                                // importedActiveLocationsNotAvailableInCurrentActiveLocations
                                                params.imported_content.data.collection = _.filter( params.imported_content.data.collection, function( _location ) {
                                                      return !_.contains( importedActiveLocationsNotAvailableInCurrentActiveLocations, _location.id );
                                                });
                                          }//if ( !_.isEmpty( firstCurrentActiveLocationId ) )
                                    }//if ( true === params.assign_missing_locations )


                                    // SHALL WE MERGE ?
                                    // Sept 2019 note : for local import only. Not implemented for global https://github.com/presscustomizr/nimble-builder/issues/495
                                    // loop on each location of the imported content
                                    // if the current setting value has sections in a location, add them before the imported ones
                                    // keep_existing_sections is a user check option
                                    // @see PHP sek_get_module_params_for_sek_local_imp_exp()
                                    if ( true === params.keep_existing_sections ) {
                                        // note that importedCollection is a unlinked clone of params.imported_content.data.collection
                                        // merge sections
                                        _.each( importedCollection, function( imp_location_data ) {
                                              var currentLocationData = self.getLevelModel( imp_location_data.id, self.updAPISetParams.newSetValue.collection );
                                              if ( _.isEmpty( currentLocationData.collection ) )
                                                return;

                                              var importedLocationData = self.getLevelModel( imp_location_data.id, params.imported_content.data.collection );
                                              importedLocationData.collection = _.union( currentLocationData.collection, importedLocationData.collection );
                                        });

                                        // merge fonts if needed
                                        if ( self.updAPISetParams.newSetValue.fonts && !_.isEmpty( self.updAPISetParams.newSetValue.fonts ) && _.isArray( self.updAPISetParams.newSetValue.fonts ) ) {
                                              params.imported_content.data.fonts = _.isArray( params.imported_content.data.fonts ) ? params.imported_content.data.fonts : [];
                                              // merge and remove duplicated fonts
                                              params.imported_content.data.fonts =  _.uniq( _.union( self.updAPISetParams.newSetValue.fonts, params.imported_content.data.fonts ) );
                                        }
                                    }// if true === params.merge

                                    self.updAPISetParams.newSetValue = params.imported_content.data;
                              break;

                              //-------------------------------------------------------------------------------------------------
                              //-- RESET COLLECTION, LOCAL OR GLOBAL
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-reset-collection' :
                                    //api.infoLog( 'sek-import-from-file', params );
                                    try { self.updAPISetParams.newSetValue = api.czr_sektions.resetCollectionSetting( params.scope ); } catch( er ) {
                                          api.errare( 'sek-reset-collection => error when firing resetCollectionSetting()', er );
                                    }
                              break;
                        }// switch



                        // if we did not already rejected the request, let's check if the setting object has actually been modified
                        // at this point it should have been.
                        if ( 'pending' == self.updAPISetParams.promise.state() ) {
                              var mayBeUpdateSektionsSetting = function() {

                                    // When a sektion setting is changed, "from" and "to" are passed to the .settingParams property
                                    // settingParams : {
                                    //       to : to,
                                    //       from : from,
                                    //       args : args
                                    // }
                                    // @see for example ::generateUIforFrontModules or ::generateUIforLevelOptions
                                    var isSettingValueChangeCase = params.settingParams && params.settingParams.from && params.settingParams.to;


                                    // in a setting value change case, the from and to must be different
                                    // implemented when fixing https://github.com/presscustomizr/nimble-builder/issues/455
                                    if ( isSettingValueChangeCase && _.isEqual( params.settingParams.from, params.settingParams.to ) ) {
                                          self.updAPISetParams.promise.reject( 'updateAPISetting => main sektion setting change => the new setting value is unchanged when firing action : ' + params.action );
                                    } else if ( ! isSettingValueChangeCase && _.isEqual( currentSetValue, self.updAPISetParams.newSetValue ) ) {
                                          self.updAPISetParams.promise.reject( 'updateAPISetting => the new setting value is unchanged when firing action : ' + params.action );
                                    } else {
                                          if ( null !== self.validateSettingValue( self.updAPISetParams.newSetValue, params.is_global_location ? 'global' : 'local' ) ) {
                                                api( _collectionSettingId_ )( self.updAPISetParams.newSetValue, params );
                                                // Add the cloneId to the params when we resolve
                                                // the cloneId is only needed in the duplication scenarii
                                                params.cloneId = self.updAPISetParams.cloneId;
                                                self.updAPISetParams.promise.resolve( params );
                                          } else {
                                                self.updAPISetParams.promise.reject( 'Validation problem for action ' + params.action );
                                          }
                                          //api.infoLog('COLLECTION SETTING UPDATED => ', _collectionSettingId_, api( _collectionSettingId_ )() );
                                    }
                              };//mayBeUpdateSektionsSetting()

                              // For all scenarios but section injection, we can update the sektion setting now
                              // otherwise we need to wait for the injection to be processed asynchronously
                              // CRITICAL => self.updAPISetParams.promise has to be resolved / rejected
                              // otherwise this can lead to scenarios where a change is not taken into account in ::updateAPISettingAndExecutePreviewActions
                              // like in https://github.com/presscustomizr/nimble-builder/issues/373
                              if ( '_not_injection_scenario_' === __presetSectionInjected__ ) {
                                    mayBeUpdateSektionsSetting();
                                    // At this point the self.updAPISetParams.promise obj can't be in a 'pending' state
                                    if ( 'pending' === self.updAPISetParams.promise.state() ) {
                                          api.errare( '::updateAPISetting => The self.updAPISetParams.promise has not been resolved properly.');
                                    }
                              } else {
                                    __presetSectionInjected__
                                          .done( function() {
                                               mayBeUpdateSektionsSetting();
                                               // At this point the self.updAPISetParams.promise obj can't be in a 'pending' state
                                               if ( 'pending' === self.updAPISetParams.promise.state() ) {
                                                    api.errare( '::updateAPISetting => The self.updAPISetParams.promise has not been resolved properly.');
                                               }
                                          })
                                          .fail( function( _er_ ) {
                                                api.errare( 'updateAPISetting => __presetSectionInjected__ failed', _er_ );
                                          });
                              }
                        }
                  };//_do_update_setting_id()


                  // Update the sektion collection
                  api( _collectionSettingId_, function( sektionSetInstance ) {
                        _do_update_setting_id();
                  });
                  return self.updAPISetParams.promise.promise();
            },//updateAPISetting


            // used on :
            // - add column
            // - remove column
            // - duplicate column
            // - move column
            // added in June 2019 for https://github.com/presscustomizr/nimble-builder/issues/279
            resetColumnsWidthInSection : function( sektionCandidate ) {
                  // RESET ALL COLUMNS WIDTH
                  _.each( sektionCandidate.collection, function( colModel ) {
                        if ( colModel.options && colModel.options.width && colModel.options.width['custom-width'] ) {
                              colModel.options.width = _.omit( colModel.options.width, 'custom-width' );
                        }
                        colModel.width = '';// For backward compat since June 2019
                  });
            },


            // @return a promise()
            // caches the sections in api.sek_presetSections when api.section( '__content_picker__') is registered
            // caches the user saved sections on the first drag and drop of a user-saved section
            _maybeFetchSectionsFromServer : function() {
                  var dfd = $.Deferred(),
                      _ajaxRequest_;

                  if ( ! _.isEmpty( api.sek_presetSections ) ) {
                        dfd.resolve( api.sek_presetSections );
                  } else {
                        if ( ! _.isUndefined( api.sek_fetchingPresetSections ) && 'pending' == api.sek_fetchingPresetSections.state() ) {
                              _ajaxRequest_ = api.sek_fetchingPresetSections;
                        } else {
                              _ajaxRequest_ = wp.ajax.post( 'sek_get_preset_sections', { nonce: api.settings.nonce.save } );
                              api.sek_fetchingPresetSections = _ajaxRequest_;
                        }
                        _ajaxRequest_.done( function( _collection_ ) {
                              //api.sek_presetSections = JSON.parse( _collection_ );
                              api.sek_presetSections = _collection_;
                              dfd.resolve( api.sek_presetSections );
                        }).fail( function( _r_ ) {
                              dfd.reject( _r_ );
                        });
                  }
                  return dfd.promise();
            },




            // First run : fetches the collection from the server
            // Next runs : uses the cached collection
            //
            // @return a JSON parsed string,
            // + guid() ids for each levels
            // ready for insertion
            //
            // @sectionParams : {
            //       is_user_section : bool, //<= is this section a "saved" section ?
            //       presetSectionId : params.content_id,
            // }
            getPresetSectionCollection : function( sectionParams ) {
                  var self = this,
                      __dfd__ = $.Deferred();
                  if ( sectionParams.is_user_section ) {
                        wp.ajax.post( 'sek_get_user_section_json', {
                              nonce: api.settings.nonce.save,
                              section_post_name: sectionParams.presetSectionId
                              //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                        })
                        .done( function( userSection ) {
                              // User section looks like
                              // {
                              //  data : {}
                              //  metas : {}
                              //  section_post_name : ''
                              // }
                              if ( ! _.isObject( userSection ) || _.isEmpty( userSection ) || _.isUndefined( userSection.data ) ) {
                                    api.errare( 'getPresetSectionCollection => preset section type not found or empty : ' + sectionParams.presetSectionId, userSection );
                                    throw new Error( 'getPresetSectionCollection => preset section type not found or empty');
                              }

                              var userSectionCandidate = $.extend( {}, true, userSection.data );

                              // ID's
                              // the level's id have to be generated
                              // 1) root level
                              userSectionCandidate.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                              // 2) children levels
                              userSectionCandidate.collection = self.setPresetOrUserSectionIds( userSectionCandidate.collection );

                              // NIMBLE VERSION
                              // set the section version
                              userSectionCandidate.ver_ini = sektionsLocalizedData.nimbleVersion;
                              // the other level's version have to be added
                              userSectionCandidate.collection = self.setPresetSectionVersion( userSectionCandidate.collection );

                              // OK. time to resolve __presetSectionInjected__.promise()
                              __dfd__.resolve( userSectionCandidate );
                        })
                        .fail( function( er ) {
                               __dfd__.reject( er );
                        });
                  } else {
                        self._maybeFetchSectionsFromServer()
                              .fail( function( er ) {
                                    __dfd__.reject( er );
                              })
                              .done( function( _collection_ ) {
                                    //api.infoLog( 'preset_sections fetched', api.sek_presetSections );
                                    var presetSection,
                                        allPresets = $.extend( true, {}, _.isObject( _collection_ ) ? _collection_ : {} );

                                    if ( _.isEmpty( allPresets ) ) {
                                          throw new Error( 'getPresetSectionCollection => Invalid collection');
                                    }
                                    if ( _.isEmpty( allPresets[ sectionParams.presetSectionId ] ) ) {
                                          throw new Error( 'getPresetSectionCollection => the preset section : "' + sectionParams.presetSectionId + '" has not been found in the collection');
                                    }
                                    var presetCandidate = allPresets[ sectionParams.presetSectionId ];

                                    // Ensure we have a string that's JSON.parse-able
                                    // if ( typeof presetCandidate !== 'string' || presetCandidate[0] !== '{' ) {
                                    //       throw new Error( 'getPresetSectionCollection => ' + sectionParams.presetSectionId + ' is not JSON.parse-able');
                                    // }
                                    // presetCandidate = JSON.parse( presetCandidate );

                                    // ID's
                                    // the level's id have to be generated
                                    presetCandidate.collection = self.setPresetOrUserSectionIds( presetCandidate.collection );

                                    // NIMBLE VERSION
                                    // set the section version
                                    presetCandidate.ver_ini = sektionsLocalizedData.nimbleVersion;
                                    // the other level's version have to be added
                                    presetCandidate.collection = self.setPresetSectionVersion( presetCandidate.collection );
                                    __dfd__.resolve( presetCandidate );
                              });//_maybeFetchSectionsFromServer.done()
                  }
                  return __dfd__.promise();
            },


            // SECTION HELPERS
            // Replaces __rep__me__ by an id looking like __nimble__1eda909f0cfe
            setPresetOrUserSectionIds : function( collection ) {
                  var self = this;
                  // Only collection set as arrays hold columns or modules
                  if ( _.isArray( collection ) ) {
                        _.each( collection, function( levelData ) {
                              levelData.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                              if ( _.isArray( levelData.collection ) ) {
                                    self.setPresetOrUserSectionIds( levelData.collection );
                              }
                        });
                  }
                  return collection;
            },

            setPresetSectionVersion : function( collection ) {
                  var self = this;
                  _.each( collection, function( levelData ) {
                        levelData.ver_ini = sektionsLocalizedData.nimbleVersion;
                        if ( _.isArray( levelData.collection ) ) {
                              self.setPresetSectionVersion( levelData.collection );
                        }
                  });
                  return collection;
            },



            // Walk the column collection of a preset section, and replace '__img_url__*' pattern by image ids that we get from ajax calls
            // Is designed to handle multiple ajax calls in parallel if the preset_section includes several images
            // @return a promise()
            preparePresetSectionForInjection : function( columnCollection ) {
                var self = this,
                    deferreds = {},
                    preparedSection = {},
                    _dfd_ = $.Deferred();

                // items id of multi-items module must always be unique
                // this recursive method sniff and does the job
                self.maybeGenerateNewItemIdsForCrudModules( columnCollection );

                // walk the column collection and populates the deferreds object recursively
                var _sniffImg = function( data ) {
                      _.each( data, function( val, key ) {
                            if ( _.isObject( val ) || _.isArray( val ) ) {
                                  _sniffImg( val );
                            } else if ( _.isString( val ) && -1 != val.indexOf( '__img_url__' ) ) {
                                  // scenario when a section uses an image more than once.
                                  // => we don't need to fire a new ajax request for an image already sniffed
                                  if ( ! _.has( deferreds, val ) ) {
                                        deferreds[ val ] = self.importAttachment( val.replace( '__img_url__', '' ) );
                                  }
                            }
                      });
                      return deferreds;
                };

                // walk the column collection and populates the deferreds object recursively
                // imdList is formed this way :
                // __img_url__/assets/img/1.jpg : {id: 2547, url: "http://customizr-dev.test/wp-content/uploads/2018/09/nimble_asset_1.jpg"}
                // __img_url__/assets/img/2.jpg : {id: 2548, url: "http://customizr-dev.test/wp-content/uploads/2018/09/nimble_asset_2.jpg"}
                // __img_url__/assets/img/3.jpg : {id: 2549, url: "http://customizr-dev.test/wp-content/uploads/2018/09/nimble_asset_3.jpg"}
                var _replaceImgPlaceholderById = function( data, imgList) {
                      _.each( data, function( val, key ) {
                            if ( _.isObject( val ) || _.isArray( val ) ) {
                                  _replaceImgPlaceholderById( val, imgList );
                            } else if ( _.isString( val ) && -1 != val.indexOf( '__img_url__' ) && _.has( imgList, val ) && _.isObject( imgList[ val ] ) ) {
                                  data[ key ] = imgList[ val ].id;
                            }
                      });
                      return columnCollection;
                };

                self.whenAllPromisesInParallel( _sniffImg( columnCollection ) )
                    .done( function( imgList ) {
                          var imgReadySection = _replaceImgPlaceholderById( columnCollection, imgList );
                          _dfd_.resolve( imgReadySection );
                    })
                    .fail( function( _er_ ){
                          _dfd_.reject( _er_ );
                    });

                return _dfd_.promise();
            }
      });//$.extend()
})( wp.customize, jQuery );