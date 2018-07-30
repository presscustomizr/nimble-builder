//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            getLocalSkopeOptionId : function() {
                  var skope_id = api.czr_skopeBase.getSkopeProperty( 'skope_id' );
                  if ( _.isEmpty( skope_id ) ) {
                        api.errare( 'czr_sektions::getLocalSkopeOptionId => empty skope_id ');
                        return '';
                  }
                  return sektionsLocalizedData.optPrefixForSektionsNotSaved + skope_id + '__localSkopeOptions';
            },
            // @params = {
            //    action : 'sek-generate-module-ui' / 'sek-generate-level-options-ui'
            //    level : params.level,
            //    id : params.id,
            //    in_sektion : params.in_sektion,
            //    in_column : params.in_column,
            //    options : params.options || []
            // }
            // @dfd = $.Deferred()
            // @return the state promise dfd
            generateUIforLocalSkopeOptions : function( params, dfd ) {
                  var self = this,
                      _id_ = self.getLocalSkopeOptionId();
                  // Is the UI currently displayed the one that is being requested ?
                  // If so, visually remind the user that a module should be dragged
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                        return dfd;
                  }

                  _do_register_ = function() {
                        if ( ! api.has( _id_ ) ) {
                              // Schedule the binding to synchronize the options with the main collection setting
                              // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                              // They need to be kept in order to keep track of the changes in the customizer.
                              // => that's why we check if ! api.has( ... )
                              api( _id_, function( _setting_ ) {
                                    _setting_.bind( _.debounce( function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh',
                                                uiParams : params,
                                                options_type : 'general',
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                              });//api( Id, function( _setting_ ) {})

                              // Let's add the starting values if provided when registrating the module
                              var initialModuleValues = {},
                                  startingModuleValue = self.getModuleStartingValue( 'sek_local_skope_options_module' ),
                                  currentSetValue = api( self.sekCollectionSettingId() )(),
                                  allSkopeOptions = $.extend( true, {}, _.isObject( currentSetValue.options ) ? currentSetValue.options : {} ),
                                  generalOptions = _.isObject( allSkopeOptions.general ) ? allSkopeOptions.general : {};

                              if ( 'no_starting_value' !== startingModuleValue && _.isObject( startingModuleValue ) ) {
                                    // make sure the starting values are deeped clone now, before being extended
                                    var clonedStartingModuleValue = $.extend( true, {}, startingModuleValue );
                                    initialModuleValues = $.extend( clonedStartingModuleValue, generalOptions );
                              }

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'setting',
                                    id : _id_,
                                    dirty : false,
                                    value : initialModuleValues,
                                    transport : 'postMessage',//'refresh',//// ,
                                    type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                              });
                        }

                        api.CZR_Helpers.register( {
                              origin : 'nimble',
                              level : params.level,
                              what : 'control',
                              id : _id_,
                              label : sektionsLocalizedData.i18n['Local options for the sections of the current page'],
                              type : 'czr_module',//sekData.controlType,
                              module_type : 'sek_local_skope_options_module',
                              section : '__globalAndLocalOptionsSection',
                              priority : 10,
                              settings : { default : _id_ },
                              //track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        }).done( function() {
                              // api.control( _id_ ).focus({
                              //     completeCallback : function() {}
                              // });
                        });
                  };

                  // Defer the registration when the parent section gets added to the api
                  // the section '__globalAndLocalOptionsSection' is registered in ::initialize()
                  api.section( '__globalAndLocalOptionsSection', function( _section_ ) {
                        api( self.sekCollectionSettingId(), function() {
                              _do_register_();
                              _section_.container.on('click', '.accordion-section-title',function() {
                                    // Generate UI for the local skope options
                                    self.generateUI({ action : 'sek-generate-local-skope-options-ui'});
                              });
                        });
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );