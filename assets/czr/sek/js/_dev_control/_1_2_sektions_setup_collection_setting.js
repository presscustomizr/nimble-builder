//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // Fired on api 'ready', in reaction to ::setContextualCollectionSettingIdWhenSkopeSet => ::localSectionsSettingId
            // 1) register the collection setting nimble___[{$skope_id}] ( ex : nimble___[skp__post_page_20] )
            // 2) validate that the setting is well formed before being changed
            // 3) schedule reactions on change ?
            // @return void()
            setupSettingsToBeSaved : function( params ) {
                  var self = this,
                      serverCollection;

                  params = params || { dirty : false, is_group_inheritance_enabled : true };

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
                              var inital_setting_value = _.isObject( serverCollection ) ? serverCollection : self.getDefaultSektionSettingValue( localOrGlobal );
                              if ( 'local' == localOrGlobal && !params.is_group_inheritance_enabled ) {
                                    inital_setting_value.local_options.local_reset = _.isObject( inital_setting_value.local_options.local_reset ) ? inital_setting_value.local_options.local_reset : {};
                                    inital_setting_value.local_options.local_reset.inherit_group_scope = false;
                              }

                              var __collectionSettingInstance__ = api.CZR_Helpers.register({
                                    what : 'setting',
                                    id : settingData.collectionSettingId,
                                    value : self.validateSettingValue( inital_setting_value, localOrGlobal ),
                                    transport : 'postMessage',//'refresh'
                                    type : 'option',
                                    track : false,//don't register in the self.registered()
                                    origin : 'nimble',
                                    dirty : params.dirty
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

                                          // console.log('MAIN SETTING CHANGED', params );
                                          //console.log('NEW MAIN SETTING VALUE', newSektionSettingValue );


                                          // Track changes, if not already navigating the logs
                                          if ( !_.isObject( params ) || true !== params.navigatingHistoryLogs ) {
                                                try { self.trackHistoryLog( sektionSetInstance, params ); } catch(er) {
                                                      api.errare( 'setupSettingsToBeSaved => trackHistoryLog', er );
                                                }
                                          }

                                          // April 2021 : for site templates
                                          if ( 'local' === localOrGlobal ) {
                                                api.trigger('nimble-update-topbar-skope-status');
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
            // 3) directly when navigating the history log
            // 4) when importing locally or globally
            // @return {} or null if did not pass the checks
            // @param scope = string, local or global
            validateSettingValue : function( valCandidate, scope ) {
                  if ( ! _.isObject( valCandidate ) ) {
                        api.errare('::validateSettingValue => validation error => the setting should be an object', valCandidate );
                        return null;
                  }
                  if ( _.isEmpty( scope ) || !_.contains(['local', 'global'], scope ) ) {
                        api.errare( '::validateSettingValue =>  invalid scope provided.', scope );
                        return;
                  }
                  var parentLevel = {},
                      errorDetected = false,
                      levelIds = [],
                      authorized_local_option_groups = ['collection', 'local_options', 'fonts', '__inherits_group_skope_tmpl_when_exists__' ];

                  // walk the collections tree and verify it passes the various consistency checks
                  var _errorDetected_ = function( msg ) {
                        api.errare( msg , valCandidate );
                        if ( sektionsLocalizedData.isDevMode ) {
                              api.previewer.trigger('sek-notify', {
                                    type : 'error',
                                    duration : 60000,
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
                        }
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

                                  // the local setting is structured this way:
                                  // {
                                  //    collection : [],
                                  //    local_options : {},
                                  //    fonts : []
                                  // }
                                  //
                                  // global_options like sitewide header and footer are saved in a specific option => NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS
                                  // the global setting is structured this way:
                                  // {
                                  //    collection : [],
                                  //    fonts : []
                                  // }
                                  // Make sure that there's no unauthorized option group at root level
                                  _.each( level, function( _opts, _opt_group_name) {
                                        switch( scope ) {
                                              case 'local' :
                                                    if( !_.contains( authorized_local_option_groups , _opt_group_name ) ) {
                                                          _errorDetected_( 'validation error => unauthorized option group for local setting value => ' + _opt_group_name );
                                                          return;
                                                    }
                                              break;
                                              case 'global' :
                                                    if( !_.contains( ['collection', 'fonts' ] , _opt_group_name ) ) {
                                                          _errorDetected_( 'validation error => unauthorized option group for global setting value => ' + _opt_group_name );
                                                          return;
                                                    }
                                              break;
                                        }
                                  });


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
                            // LEVEL should be an object
                            if ( _.isUndefined( level ) || !_.isObject( level ) ) {
                                  _errorDetected_('validation error => a level is invalid' );
                                  return;
                            }

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

                  if ( errorDetected ) {
                        api.infoLog('error in ::validateSettingValue', valCandidate );
                        return null;
                  }
                  //api.infoLog('in ::validateSettingValue', valCandidate );
                  // if null is returned, the setting value is not set @see customize-base.js
                  return valCandidate;
            },//validateSettingValue



            // triggered when clicking on [data-sek-reset="true"]
            // click event is scheduled in ::initialize()
            // Note : only the collection is set to self.getDefaultSektionSettingValue( 'local' )
            // @see php function which defines the defaults sek_get_default_location_model()
            resetCollectionSetting : function( scope, localOptions ) {
                  var self = this, newSettingValue;
                  if ( _.isEmpty( scope ) || !_.contains(['local', 'global'], scope ) ) {
                        throw new Error( 'resetCollectionSetting => invalid scope provided.', scope );
                  }
                  // INHERITANCE
                  // solves the problem of preventing group template inheritance after a local reset
                  var _is_inheritance_enabled_in_local_options = true;
                  if ( 'local' === scope ) {
                        if ( localOptions && _.isObject(localOptions) && localOptions.local_reset && !_.isUndefined( localOptions.local_reset.inherit_group_scope ) ) {
                              _is_inheritance_enabled_in_local_options = localOptions.local_reset.inherit_group_scope;
                        }
                  }

                  newSettingValue = $.extend( true, {}, self.getDefaultSektionSettingValue( scope ) );

                  if ( !_is_inheritance_enabled_in_local_options ) {
                        newSettingValue.local_options.local_reset = { inherit_group_scope : false };
                  }
                  // April 2021, for site templates #478 => the default local sektion model includes property __inherits_group_skope_tmpl_when_exists__, set to true
                  // => when reseting locally, if a group template is defined, it will be inherited

                  // How does it work?
                  // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
                  // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
                  // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true here
                  // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
                  return newSettingValue;
            }
      });//$.extend()
})( wp.customize, jQuery );