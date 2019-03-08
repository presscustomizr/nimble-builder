//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // Fired on api 'ready', in reaction to ::setContextualCollectionSettingIdWhenSkopeSet => ::localSectionsSettingId
            // 1) register the collection setting nimble___[{$skope_id}] ( ex : nimble___[skp__post_page_20] )
            // 2) validate that the setting is well formed before being changed
            // 3) schedule reactions on change ?
            // @return void()
            setupSettingsToBeSaved : function() {
                  var self = this,
                      serverCollection;

                  // maybe register the sektion_collection settings
                  var _settingsToRegister_ = {
                        'local' : { collectionSettingId : self.localSectionsSettingId() },//<= "nimble___[skp__post_page_10]"
                        'global' : { collectionSettingId : self.getGlobalSectionsSettingId() }//<= "nimble___[skp__global]"
                  };

                  _.each( _settingsToRegister_, function( settingData, localOrGlobal ) {
                        serverCollection = api.czr_skopeBase.getSkopeProperty( 'sektions', localOrGlobal ).db_values;
                        if ( _.isEmpty( settingData.collectionSettingId ) ) {
                              throw new Error( 'setupSettingsToBeSaved => the collectionSettingId is invalid' );
                        }
                        // if the collection setting is not registered yet
                        // => register it and bind it
                        // => ensure that it will be bound only once, because the setting are never unregistered
                        if ( ! api.has( settingData.collectionSettingId ) ) {
                              var __collectionSettingInstance__ = api.CZR_Helpers.register({
                                    what : 'setting',
                                    id : settingData.collectionSettingId,
                                    value : self.validateSettingValue( _.isObject( serverCollection ) ? serverCollection : self.getDefaultSektionSettingValue( localOrGlobal )  ),
                                    transport : 'postMessage',//'refresh'
                                    type : 'option',
                                    track : false,//don't register in the self.registered()
                                    origin : 'nimble'
                              });


                              //if ( sektionsLocalizedData.isDevMode ) {}
                              api( settingData.collectionSettingId, function( sektionSetInstance ) {

                                    // Schedule reactions to a collection change
                                    sektionSetInstance.bind( _.debounce( function( newSektionSettingValue, previousValue, params ) {
                                          // api.infoLog( 'sektionSettingValue is updated',
                                          //       {
                                          //             newValue : newSektionSettingValue,
                                          //             previousValue : previousValue,
                                          //             params : params
                                          //       }
                                          // );

                                          // Track changes, if not already navigating the logs
                                          if ( !_.isObject( params ) || true !== params.navigatingHistoryLogs ) {
                                                try { self.trackHistoryLog( sektionSetInstance, params ); } catch(er) {
                                                      api.errare( 'setupSettingsToBeSaved => trackHistoryLog', er );
                                                }
                                          }

                                    }, 1000 ) );
                              });//api( settingData.collectionSettingId, function( sektionSetInstance ){}
                        }//if ( ! api.has( settingData.collectionSettingId ) ) {
                  });//_.each(

                  // global options for all collection setting of this skope_id
                  // loop_start, before_content, after_content, loop_end

                  // Global Options : section
                  // api.CZR_Helpers.register({
                  //       what : 'section',
                  //       id : sektionsLocalizedData.optPrefixForSektionGlobalOptsSetting,//'__sektions__'
                  //       title: 'Global Options',
                  //       priority : 1000,
                  //       constructWith : SektionPanelConstructor,
                  //       track : false//don't register in the self.registered()
                  // });

                  // // => register a control
                  // // Template
                  // api.CZR_Helpers.register({
                  //       what : 'control',
                  //       id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                  //       title: 'Main sektions panel',
                  //       priority : 1000,
                  //       constructWith : SektionPanelConstructor,
                  //       track : false//don't register in the self.registered()
                  // });
            },// SetupSettingsToBeSaved()

            // Fired :
            // 1) when instantiating the setting
            // 2) on each setting change, as an override of api.Value::validate( to ) @see customize-base.js
            // @return {} or null if did not pass the checks
            validateSettingValue : function( valCandidate ) {
                  if ( ! _.isObject( valCandidate ) ) {
                        api.errare('validation error => the setting should be an object', valCandidate );
                        return null;
                  }
                  var parentLevel = {},
                      errorDetected = false,
                      levelIds = [];
                  // walk the collections tree and verify it passes the various consistency checks
                  var _errorDetected_ = function( msg ) {
                        api.errare( msg , valCandidate );
                        api.previewer.trigger('sek-notify', {
                              type : 'error',
                              duration : 30000,
                              message : [
                                    '<span style="font-size:0.95em">',
                                      '<strong>' + msg + '</strong>',
                                      '<br>',
                                      sektionsLocalizedData.i18n['If this problem locks Nimble Builder, you can try resetting the sections of this page.'],
                                      '<br>',
                                      '<span style="text-align:center;display:block">',
                                        '<button type="button" class="button" aria-label="' + sektionsLocalizedData.i18n.Reset + '" data-sek-reset="true">' + sektionsLocalizedData.i18n.Reset + '</button>',
                                      '</span>',
                                    '</span>'
                              ].join('')

                        });
                        errorDetected = true;
                  };
                  var _checkWalker_ = function( level ) {
                      if ( errorDetected ) {
                            return;
                      }
                      if ( _.isUndefined( level ) && _.isEmpty( parentLevel ) ) {
                            // we are at the root level
                            level = $.extend( true, {}, valCandidate );
                            if ( _.isUndefined( level.id ) || _.isUndefined( level.level ) ) {
                                  // - there should be no 'level' property or 'id'
                                  // - there should be a collection of registered locations
                                  // - there should be no parent level defined
                                  if ( _.isUndefined( level.collection ) ) {
                                        _errorDetected_( 'validation error => the root level is missing the collection of locations' );
                                        return;
                                  }
                                  if ( ! _.isEmpty( level.level ) || ! _.isEmpty( level.id ) ) {
                                        _errorDetected_( 'validation error => the root level should not have a "level" or an "id" property' );
                                        return;
                                  }

                                  // Walk the section collection
                                  _.each( valCandidate.collection, function( _l_ ) {
                                        // Set the parent level now
                                        parentLevel = level;
                                        // walk
                                        _checkWalker_( _l_ );
                                  });
                            }
                      } else {
                            // we have a level.
                            // - make sure we have at least the following properties : id, level

                            // ID
                            if ( _.isEmpty( level.id ) || ! _.isString( level.id )) {
                                  _errorDetected_('validation error => a ' + level.level + ' level must have a valid id' );
                                  return;
                            } else if ( _.contains( levelIds, level.id ) ) {
                                  _errorDetected_('validation error => duplicated level id : ' + level.id );
                                  return;
                            } else {
                                  levelIds.push( level.id );
                            }

                            // OPTIONS
                            // if ( _.isEmpty( level.options ) || ! _.isObject( level.options )) {
                            //       _errorDetected_('validation error => a ' + level.level + ' level must have a valid options property' );
                            //       return;
                            // }

                            // LEVEL
                            if ( _.isEmpty( level.level ) || ! _.isString( level.level ) ) {
                                  _errorDetected_('validation error => a ' + level.level + ' level must have a level property' );
                                  return;
                            } else if ( ! _.contains( [ 'location', 'section', 'column', 'module' ], level.level ) ) {
                                  _errorDetected_('validation error => the level "' + level.level + '" is not authorized' );
                                  return;
                            }

                            // - Unless we are in a module, there should be a collection property
                            // - make sure a module doesn't have a collection property
                            if ( 'module' == level.level ) {
                                  if ( ! _.isUndefined( level.collection ) ) {
                                        _errorDetected_('validation error => a module can not have a collection property' );
                                        return;
                                  }
                            } else {
                                  if ( _.isUndefined( level.collection ) ) {
                                        _errorDetected_( 'validation error => missing collection property for level => ' + level.level + ' ' + level.id );
                                        return;
                                  }
                            }

                            // a level should always have a version "ver_ini" property
                            if ( _.isUndefined( level.ver_ini ) ) {
                                  //_errorDetected_('validation error => a ' + level.level + ' should have a version property : "ver_ini"' );
                                  //return;
                                  api.errare( 'validateSettingValue() => validation error => a ' + level.level + ' should have a version property : "ver_ini"' );
                            }

                            // Specific checks by level type
                            switch ( level.level ) {
                                  case 'location' :
                                        if ( ! _.isEmpty( parentLevel.level ) ) {
                                              _errorDetected_('validation error => the parent of location ' + level.id +' should have no level set' );
                                              return;
                                        }
                                  break;

                                  case 'section' :
                                        if ( level.is_nested && 'column' != parentLevel.level ) {
                                              _errorDetected_('validation error => the nested section ' + level.id +' must be child of a column' );
                                              return;
                                        }
                                        if ( ! level.is_nested && 'location' != parentLevel.level ) {
                                              _errorDetected_('validation error => the section ' + level.id +' must be child of a location' );
                                              return;
                                        }
                                  break;

                                  case 'column' :
                                        if ( 'section' != parentLevel.level ) {
                                              _errorDetected_('validation error => the column ' + level.id +' must be child of a section' );
                                              return;
                                        }
                                  break;

                                  case 'module' :
                                        if ( 'column' != parentLevel.level ) {
                                              _errorDetected_('validation error => the module ' + level.id +' must be child of a column' );
                                              return;
                                        }
                                  break;
                            }

                            // If we are not in a module, keep walking the collections
                            if ( 'module' != level.level ) {
                                  _.each( level.collection, function( _l_ ) {
                                        // Set the parent level now
                                        parentLevel = $.extend( true, {}, level );
                                        if ( ! _.isUndefined( _l_ ) ) {
                                              // And walk sub levels
                                              _checkWalker_( _l_ );
                                        } else {
                                              _errorDetected_('validation error => undefined level ' );
                                        }
                                  });
                            }
                      }
                  };
                  _checkWalker_();

                  //api.infoLog('in ::validateSettingValue', valCandidate );
                  // if null is returned, the setting value is not set @see customize-base.js
                  return errorDetected ? null : valCandidate;
            },//validateSettingValue



            // triggered when clicking on [data-sek-reset="true"]
            // scheduled in ::initialize()
            // Note :
            // 1) this is not a real reset, the customizer setting is set to self.getDefaultSektionSettingValue( 'local' )
            // @see php function which defines the defaults
            // function sek_get_default_location_model() {
            //     $defaut_sektions_value = [ 'collection' => [], 'options' => [] ];
            //     foreach( sek_get_locations() as $location ) {
            //         $defaut_sektions_value['collection'][] = [
            //             'id' => $location,
            //             'level' => 'location',
            //             'collection' => [],
            //             'options' => []
            //         ];
            //     }
            //     return $defaut_sektions_value;
            // }
            // 2) a real reset should delete the sektion post ( nimble_post_type, with for example title nimble___skp__post_page_21 ) and its database option storing its id ( for example : nimble___skp__post_page_21 )
            resetCollectionSetting : function() {
                  var self = this;
                  if ( _.isEmpty( self.localSectionsSettingId() ) ) {
                        throw new Error( 'setupSettingsToBeSaved => the collectionSettingId is invalid' );
                  }
                  // reset the setting to default
                  api( self.localSectionsSettingId() )( self.getDefaultSektionSettingValue( 'local' ) );
                  // refresh the preview
                  api.previewer.refresh();
                  // remove any previous notification
                  api.notifications.remove( 'sek-notify' );
                  // display a success msg
                  api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                        api.notifications.add( new api.Notification( 'sek-reset-done', {
                              type: 'success',
                              message: sektionsLocalizedData.i18n['Reset complete'],
                              dismissible: true
                        } ) );

                        // Removed if not dismissed after 5 seconds
                        _.delay( function() {
                              api.notifications.remove( 'sek-reset-done' );
                        }, 5000 );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );