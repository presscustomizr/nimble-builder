//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
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
            generateUIforFrontModules : function( params, dfd ) {
                  var self = this;
                  if ( _.isEmpty( params.id ) ) {
                        dfd.reject( 'generateUI => missing id' );
                  }
                  // Is the UI currently displayed the one that is being requested ?
                  // If so, don't generate the ui again, simply focus on it
                  if ( self.isUIControlAlreadyRegistered( params.id ) ) {
                        api.control( params.id ).focus({
                              completeCallback : function() {}
                        });
                        return dfd;
                  }


                  // Clean previously generated UI elements
                  self.cleanRegistered();

                  // For modules, we need to generate a UI for the module value
                  var moduleValue = self.getLevelProperty({
                        property : 'value',
                        id : params.id
                  });

                  var moduleType = self.getLevelProperty({
                        property : 'module_type',
                        id : params.id
                  });

                  if ( _.isEmpty( moduleType ) ) {
                        dfd.reject( 'generateUI => module => invalid module_type' );
                  }

                  _do_register_ = function() {
                        // Make sure this setting is bound only once !
                        if ( ! api.has( params.id ) ) {
                              // Schedule the binding to synchronize the module setting with the main collection setting
                              // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                              // They need to be kept in order to keep track of the changes in the customizer.
                              // => that's why we check if ! api.has( ... )
                              api( params.id, function( _setting_ ) {
                                    _setting_.bind( _.debounce( function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh_markup',
                                                uiParams : _.extend( params, { action : 'sek-set-module-value' } ),
                                                //options_type : 'spacing',
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                              });

                              api.CZR_Helpers.register({
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'setting',
                                    id : params.id,
                                    dirty : false,
                                    value : moduleValue,
                                    transport : 'postMessage',// 'refresh',
                                    type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                              });
                        }//if ( ! api.has( params.id ) )



                        api.CZR_Helpers.register( {
                              origin : 'nimble',
                              level : params.level,
                              what : 'control',
                              id : params.id,
                              label : sektionsLocalizedData.i18n['Customize the options for module :'] + ' ' + api.czrModuleMap[ moduleType ].name,
                              type : 'czr_module',//sekData.controlType,
                              module_type : moduleType,
                              section : params.id,
                              priority : 10,
                              settings : { default : params.id }
                        }).done( function() {
                              api.control( params.id ).focus({
                                    completeCallback : function() {}
                              });
                        });
                  };

                  // Defer the registration when the parent section gets added to the api
                  api.section.when( params.id, function() {
                        _do_register_();
                  });

                  // MAIN CONTENT SECTION
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : params.id,
                        title: sektionsLocalizedData.i18n['Content for'] + ' ' + api.czrModuleMap[ moduleType ].name,
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 20,
                        //track : false//don't register in the self.registered()
                        //constructWith : MainSectionConstructor,
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );