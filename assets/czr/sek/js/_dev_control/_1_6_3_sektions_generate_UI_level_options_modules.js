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
            generateUIforLevelOptions : function( params, dfd ) {
                  var self = this;
                  // Get this level options
                  var levelOptionValues = self.getLevelProperty({
                            property : 'options',
                            id : params.id
                      });
                  levelOptionValues = _.isObject( levelOptionValues ) ? levelOptionValues : {};


                  // Prepare the module map to register
                  var levelRegistrationParams = {};

                  if ( 'section' === params.level ) {
                        $.extend( levelRegistrationParams, {
                              layout : {
                                    settingControlId : params.id + '__sectionLayout_options',
                                    module_type : 'sek_level_section_layout_module',
                                    controlLabel : sektionsLocalizedData.i18n['Layout settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level]
                              }
                        });
                  }

                  $.extend( levelRegistrationParams, {
                        bg_border : {
                              settingControlId : params.id + '__bgBorder_options',
                              module_type : 'sek_level_bg_border_module',
                              controlLabel : sektionsLocalizedData.i18n['Background and border settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level]
                        },
                        spacing : {
                              settingControlId : params.id + '__spacing_options',
                              module_type : 'sek_level_spacing_module',
                              controlLabel : sektionsLocalizedData.i18n['Padding and margin settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level]
                        },
                        height : {
                              settingControlId : params.id + '__height_options',
                              module_type : 'sek_level_height_module',
                              controlLabel : sektionsLocalizedData.i18n['Height settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level]
                        },
                        anchor : {
                              settingControlId : params.id + '__anchor_options',
                              module_type : 'sek_level_anchor_module',
                              controlLabel : sektionsLocalizedData.i18n['Set a custom anchor for the'] + ' ' + sektionsLocalizedData.i18n[params.level]
                        },
                  });

                  if ( 'module' === params.level ) {
                        $.extend( levelRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_module',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level]
                              }
                        });
                  }



                  // @return void()
                  _do_register_ = function() {
                        _.each( levelRegistrationParams, function( optionData, optionType ){
                               // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, simply focus on the section
                              if ( self.isUIControlAlreadyRegistered( optionData.settingControlId ) ) {
                                    api.section( api.control( optionData.settingControlId ).section() ).expanded( true );
                                    return;
                              }
                              if( ! api.has( optionData.settingControlId ) ) {
                                    // Schedule the binding to synchronize the options with the main collection setting
                                    // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                    // They need to be kept in order to keep track of the changes in the customizer.
                                    // => that's why we check if ! api.has( ... )
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( function( to, from, args ) {
                                                try { self.updateAPISettingAndExecutePreviewActions({
                                                      defaultPreviewAction : 'refresh_stylesheet',
                                                      uiParams : params,
                                                      options_type : optionType,// <= this is the options sub property where we will store this setting values. @see updateAPISetting case 'sek-generate-level-options-ui'
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
                                    startingModuleValue = self.getModuleStartingValue( optionData.module_type );
                                    initialModuleValues = levelOptionValues[ optionType ] || {};
                                    if ( 'no_starting_value' !== startingModuleValue ) {
                                          initialModuleValues = $.extend( startingModuleValue, initialModuleValues );
                                    }

                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : initialModuleValues,
                                          transport : 'postMessage',// 'refresh',
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option //sekData.settingType
                                    });
                              }//if( ! api.has( optionData.settingControlId ) ) {

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    level_id : params.id,
                                    what : 'control',
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : params.id,
                                    priority : 0,
                                    settings : { default : optionData.settingControlId }
                              }).done( function() {
                                    api.control( optionData.settingControlId ).focus({
                                          completeCallback : function() {}
                                    });
                              });
                        });//_.each()
                  };//_do_register_()



                  // Defer the registration when the parent section gets added to the api
                  api.section.when( params.id, function() {
                        _do_register_();
                  });

                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : params.id,
                        title: sektionsLocalizedData.i18n['Settings for the'] + ' ' + params.level,
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 10,
                        track : false//don't register in the self.registered()
                        //constructWith : MainSectionConstructor,
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );