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

                  // The following property is populated on each api setting update
                  // some properties are modified during the sub-callbacks of _updateSektionSettingInstanceWithAction
                  self.updAPISetParams = {
                        params : params,
                        promise : $.Deferred(),
                        newSetValue : _.isObject( currentSetValue ) ? $.extend( true, {}, currentSetValue ) : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ),
                        cloneId : '', // the cloneId is only needed in the duplication scenarii
                        sectionInjectPromise : '_not_injection_scenario_'//this property is turned into a $.Deferred() object in a scenario of section injection
                  };

                  // callback ran in api( _collectionSettingId_, function( sektionSetInstance ) {})
                  var _updateSektionSettingInstanceWithAction = function() {
                        // api( _collectionSettingId_)() = {
                        //    collection : [
                        //       'loop_start' :  { level : location,  collection : [ 'sek124' : { collection : [], level : section, options : {} }], options : {}},
                        //       'loop_end' : { level : location, collection : [], options : {}}
                        //        ...
                        //    ],
                        //    options : {}
                        //
                        // }

                        // make sure we have a collection array to populate
                        self.updAPISetParams.newSetValue.collection = _.isArray( self.updAPISetParams.newSetValue.collection ) ? self.updAPISetParams.newSetValue.collection : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ).collection;

                        switch( params.action ) {
                              //-------------------------------------------------------------------------------------------------
                              //-- LOCATION
                              //-------------------------------------------------------------------------------------------------
                              // December 2020 => this action is triggered in ::initialize self.activeLocations.bind()
                              // when injecting template from the gallery it may happen that the collection of location in the local setting value is not synchronized anymore with the actual active locations on the page
                              // update : December 24th => deactivated because of https://github.com/presscustomizr/nimble-builder/issues/770
                              case 'sek-maybe-add-missing-locations' :
                                    var activeLocations = self.activeLocations(),
                                          settingLocations = [],
                                          locInSetting,
                                          missingLoc,
                                          newSettingCollection = [],
                                          currentSettingCollection = $.extend( true, [], self.updAPISetParams.newSetValue.collection );

                                    //console.log('SOO current Setting Collection', currentSettingCollection, activeLocations );

                                    // loop on the active locations of the current page.
                                    // if one is missing in the setting value, let's add it.
                                    _.each( activeLocations, function( _loc_id ) {
                                          locInSetting = _.findWhere( self.updAPISetParams.newSetValue.collection, { id : _loc_id } );
                                          if ( _.isUndefined( locInSetting ) ) {
                                                missingLoc = $.extend( true, {}, sektionsLocalizedData.defaultLocationModel );
                                                missingLoc.id = _loc_id;
                                                api.infoLog('=> need to add missing location to api setting value', missingLoc );
                                                self.updAPISetParams.newSetValue.collection.push(missingLoc);
                                          }
                                    });
                              break;

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
                              //-- CONTENT IN NEW SEKTION
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-content-in-new-sektion' :
                                    self._updAPISet_sek_add_content_in_new_sektion();
                              break;
                              //-------------------------------------------------------------------------------------------------
                              //-- CONTENT IN NEW NESTED SEKTION
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-preset-section-in-new-nested-sektion' :
                                    self._updAPISet_sek_add_preset_sektion_in_new_nested_sektion();
                              break;


                              //-------------------------------------------------------------------------------------------------
                              //-- FILE IMPORT
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-import-from-file' :
                                    self._updAPISet_sek_import_from_file();
                              break;

                              //-------------------------------------------------------------------------------------------------
                              //-- INJECT TEMPLATE FROM GALLERY
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-inject-tmpl-from-gallery' :
                                    self._updAPISet_sek_inject_tmpl_from_gallery();
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
                              //-- RESET COLLECTION, LOCAL OR GLOBAL
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-reset-collection' :
                                    //api.infoLog( 'sek-import-from-file', params );
                                    var _localOptions;
                                    if ( 'local' === params.scope ) {
                                          _localOptions = $.extend( true, {}, _.isObject( self.updAPISetParams.newSetValue.local_options ) ? self.updAPISetParams.newSetValue.local_options : {} );
                                    }
                                    try { self.updAPISetParams.newSetValue = api.czr_sektions.resetCollectionSetting( params.scope, _localOptions ); } catch( er ) {
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
                                          // method ::validateSettingValue() returns null if there is at least one validation error
                                          var _settingValidationResult = self.validateSettingValue( self.updAPISetParams.newSetValue, params.is_global_location ? 'global' : 'local' );
                                          if ( null !== _settingValidationResult && !_.isUndefined(_settingValidationResult) ) {
                                                if ( !params.is_global_location ) {
                                                      // INHERITANCE
                                                      // solves the problem of preventing group template inheritance after a local reset
                                                      // on ::resetCollectionSetting(), the setting val is being modified to add this property local_reset.inherit_group_scope 
                                                      var _is_inheritance_enabled_in_local_options = true, newSetVal = self.updAPISetParams.newSetValue;
                                                      if ( newSetVal.local_options && newSetVal.local_options.local_reset && !_.isUndefined( newSetVal.local_options.local_reset.inherit_group_scope ) ) {
                                                            _is_inheritance_enabled_in_local_options = newSetVal.local_options.local_reset.inherit_group_scope;
                                                      }
                                                      // Added March 2021 for #478
                                                      // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
                                                      // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
                                                      // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
                                                      // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
                                                      self.updAPISetParams.newSetValue.__inherits_group_skope_tmpl_when_exists__ = 'sek-reset-collection' === params.action && _is_inheritance_enabled_in_local_options;
                                                }
                                                api( _collectionSettingId_ )( self.updAPISetParams.newSetValue, params );

                                                // April 2021 : for site templates
                                                if ( !params.is_global_location ) {
                                                      api.trigger('nimble-update-topbar-skope-status');
                                                }
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

                              // For all scenarios except section injection, we can update the sektion setting now
                              // otherwise we need to wait for the injection to be processed asynchronously
                              // CRITICAL => self.updAPISetParams.promise has to be resolved / rejected
                              // otherwise this can lead to scenarios where a change is not taken into account in ::updateAPISettingAndExecutePreviewActions
                              // like in https://github.com/presscustomizr/nimble-builder/issues/373
                              if ( '_not_injection_scenario_' === self.updAPISetParams.sectionInjectPromise ) {
                                    mayBeUpdateSektionsSetting();
                                    // At this point the self.updAPISetParams.promise obj can't be in a 'pending' state
                                    if ( 'pending' === self.updAPISetParams.promise.state() ) {
                                          api.errare( '::updateAPISetting => The self.updAPISetParams.promise has not been resolved properly.');
                                    }
                              } else {
                                    self.updAPISetParams.sectionInjectPromise
                                          .done( function() {
                                               mayBeUpdateSektionsSetting();
                                               // At this point the self.updAPISetParams.promise obj can't be in a 'pending' state
                                               if ( 'pending' === self.updAPISetParams.promise.state() ) {
                                                    api.errare( '::updateAPISetting => The self.updAPISetParams.promise has not been resolved properly.');
                                               }
                                          })
                                          .fail( function( _er_ ) {
                                                api.errare( 'updateAPISetting => self.updAPISetParams.sectionInjectPromise failed', _er_ );
                                          });
                              }
                        }
                  };//_updateSektionSettingInstanceWithAction()


                  // Update the sektion collection
                  api( _collectionSettingId_, function( sektionSetInstance ) {
                        _updateSektionSettingInstanceWithAction();
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
            // caches the api sections in api.nimble_ApiSections when api.section( '__content_picker__') is registered
            // caches the user saved sections on the first drag and drop of a user-saved section
            _getApiSingleSectionData : function( presetSectionId ) {
                  var dfd = $.Deferred(),
                      _ajaxRequest_;

                  // If already cached, resolve now
                  if ( ! _.isEmpty( api.nimble_ApiSections[presetSectionId] ) ) {
                        dfd.resolve( api.nimble_ApiSections[presetSectionId] );
                  } else {
                        if ( ! _.isUndefined( api.nimble_fetchingApiSection ) && 'pending' == api.nimble_fetchingApiSection.state() ) {
                              _ajaxRequest_ = api.nimble_fetchingApiSection;
                        } else {
                              _ajaxRequest_ = wp.ajax.post( 'sek_get_single_api_section_data', { 
                                    nonce: api.settings.nonce.save,
                                    api_section_id : presetSectionId
                              });
                              api.nimble_fetchingApiSection = _ajaxRequest_;
                        }
                        _ajaxRequest_.done( function( _section_data_ ) {
                              //api.nimble_ApiSections = JSON.parse( _section_data_ );
                              api.nimble_ApiSections[presetSectionId] = _section_data_;
                              dfd.resolve( _section_data_ );
                        }).fail( function( _r_ ) {
                              api.errorLog( 'ajax sek_get_single_api_section_data => error', _r_ );
                              var _msg = 'Error when fetching the section';
                              if ( _.isString( _r_ ) && !_.isEmpty( _r_ ) ) {
                                    _msg = _r_;
                              }
                              api.previewer.trigger('sek-notify', {
                                    type : 'error',
                                    duration : 60000,
                                    is_pro_notif : true,
                                    notif_id : 'pro_section_error',
                                    message : [
                                          '<span style="font-size:0.95em">',
                                          '<strong>'+ _msg + '</strong>',
                                          '</span>'
                                    ].join('')
                              });
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
            getPresetSectionCollectionData : function( sectionParams ) {
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
                                    api.errare( 'getPresetSectionCollectionData => preset section type not found or empty : ' + sectionParams.presetSectionId, userSection );
                                    throw new Error( 'getPresetSectionCollectionData => preset section type not found or empty');
                              }

                              var userSectionCandidate = $.extend( {}, true, userSection.data );

                              // ID's
                              // the level's id have to be generated
                              // 1) root level
                              userSectionCandidate.id = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();
                              // 2) children levels
                              userSectionCandidate.collection = self.setPresetOrUserSectionIds( userSectionCandidate.collection );

                              // NIMBLE VERSION
                              // set the section version
                              userSectionCandidate.ver_ini = sektionsLocalizedData.nimbleVersion;
                              // the other level's version have to be added
                              userSectionCandidate.collection = self.setPresetSectionVersion( userSectionCandidate.collection );

                              // OK. time to resolve self.updAPISetParams.sectionInjectPromise.promise()
                              __dfd__.resolve( userSectionCandidate );
                        })
                        .fail( function( er ) {
                               __dfd__.reject( er );
                        });
                  } else {
                        api.nimble_ApiSections = api.nimble_ApiSections || {};
                        var _doResolveDfdWithData = function( _section_data_ ) {
                              //api.infoLog( 'API SECTION fetched', sectionParams.presetSectionId, api.nimble_ApiSections );
                              if ( _.isEmpty( _section_data_ ) || !_.isObject(_section_data_) ) {
                                    throw new Error( 'getPresetSectionCollectionData => Invalid collection');
                              }
                              // if ( _.isEmpty( allPresets[ sectionParams.presetSectionId ] ) ) {
                              //       throw new Error( 'getPresetSectionCollectionData => the preset section : "' + sectionParams.presetSectionId + '" has not been found in the collection');
                              // }
                              var presetCandidate = $.extend( true, {}, _section_data_ );

                              // Ensure we have a string that's JSON.parse-able
                              // if ( typeof presetCandidate !== 'string' || presetCandidate[0] !== '{' ) {
                              //       throw new Error( 'getPresetSectionCollectionData => ' + sectionParams.presetSectionId + ' is not JSON.parse-able');
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
                        };
                        var _collection;
                        switch( sectionParams.presetSectionId ) {
                              case 'two_columns' :
                                    _collection = JSON.parse('{"collection":[{"id":"","level":"column","collection":[]},{"id":"","level":"column","collection":[]}]}');
                                    _doResolveDfdWithData(_collection);
                              break;
                              case 'three_columns' :
                                    _collection = JSON.parse('{"collection":[{"id":"","level":"column","collection":[]},{"id":"","level":"column","collection":[]},{"id":"","level":"column","collection":[]}]}');
                                    _doResolveDfdWithData(_collection);
                              break;
                              case 'four_columns' :
                                    _collection = JSON.parse('{"collection":[{"id":"","level":"column","collection":[]},{"id":"","level":"column","collection":[]},{"id":"","level":"column","collection":[]},{"id":"","level":"column","collection":[]}]}');
                                    _doResolveDfdWithData(_collection);
                              break;
                              default :
                                    self._getApiSingleSectionData( sectionParams.presetSectionId )
                                          .fail( function( er ) {
                                                __dfd__.reject( er );
                                          })
                                          .done( _doResolveDfdWithData );//_getApiSingleSectionData.done()
                              break;
                        }// Switch()
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
                              levelData.id = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();
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