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

                  $.extend( levelRegistrationParams, {
                        bg_border : {
                              settingControlId : params.id + '__bgBorder_options',
                              module_type : 'sek_level_bg_border_module',
                              controlLabel : sektionsLocalizedData.i18n['Background and border settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              expandAndFocusOnInit : true,
                              icon : '<i class="material-icons sek-level-option-icon">gradient</i>'//'<i class="material-icons sek-level-option-icon">brush</i>'
                        },
                        spacing : {
                              settingControlId : params.id + '__spacing_options',
                              module_type : 'sek_level_spacing_module',
                              controlLabel : sektionsLocalizedData.i18n['Padding and margin settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="material-icons sek-level-option-icon">center_focus_weak</i>'
                        },
                        anchor : {
                              settingControlId : params.id + '__anchor_options',
                              module_type : 'sek_level_anchor_module',
                              controlLabel : sektionsLocalizedData.i18n['Set a custom anchor for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="fas fa-anchor sek-level-option-icon"></i>'
                        },
                        visibility : {
                              settingControlId : params.id + '__visibility_options',
                              module_type : 'sek_level_visibility_module',
                              controlLabel : sektionsLocalizedData.i18n['Device visibility settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="far fa-eye sek-level-option-icon"></i>'
                        },
                        height : {
                              settingControlId : params.id + '__height_options',
                              module_type : 'sek_level_height_module',
                              controlLabel : sektionsLocalizedData.i18n['Height settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="fas fa-ruler-vertical sek-level-option-icon"></i>'
                        },
                  });

                  if ( 'section' === params.level ) {
                        $.extend( levelRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_section',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                    icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                              }
                        });
                        // Deactivated
                        // => replaced by sek_level_width_section
                        // $.extend( levelRegistrationParams, {
                        //       layout : {
                        //             settingControlId : params.id + '__sectionLayout_options',
                        //             module_type : 'sek_level_section_layout_module',
                        //             controlLabel : sektionsLocalizedData.i18n['Layout settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                        //             icon : '<i class="material-icons sek-level-option-icon">crop_din</i>'
                        //       }
                        // });
                        $.extend( levelRegistrationParams, {
                              breakpoint : {
                                    settingControlId : params.id + '__breakpoint_options',
                                    module_type : 'sek_level_breakpoint_module',
                                    controlLabel : sektionsLocalizedData.i18n['Breakpoint for responsive columns'],
                                    icon : '<i class="material-icons sek-level-option-icon">devices</i>'
                              }
                        });
                  }
                  if ( 'module' === params.level ) {
                        $.extend( levelRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_module',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                    icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
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
                                    var initialModuleValues = levelOptionValues[ optionType ] || {};
                                    var startingModuleValue = self.getModuleStartingValue( optionData.module_type );
                                    if ( 'no_starting_value' !== startingModuleValue && _.isObject( startingModuleValue ) ) {
                                          // make sure the starting values are deeped clone now, before being extended
                                          var clonedStartingModuleValue = $.extend( true, {}, startingModuleValue );
                                          initialModuleValues = $.extend( clonedStartingModuleValue, initialModuleValues );
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
                                    if ( true === optionData.expandAndFocusOnInit ) {
                                          api.control( optionData.settingControlId ).focus({
                                                completeCallback : function() {}
                                          });
                                    }

                                    // Implement the animated arrow markup, and the initial state of the module visibility
                                    api.control( optionData.settingControlId, function( _control_ ) {
                                          // Hide the item wrapper
                                          _control_.container.find('.czr-items-wrapper').hide();
                                          var $title = _control_.container.find('label > .customize-control-title');
                                          // if this level has an icon, let's prepend it to the title
                                          if ( ! _.isUndefined( optionData.icon ) ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                          }
                                          // prepend the animated arrow
                                          $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                          // setup the initial state + initial click
                                          _control_.container.attr('data-sek-expanded', "false" );
                                          if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                                $title.trigger('click');
                                          }
                                    });
                              });
                        });//_.each()
                  };//_do_register_()

                  // The section won't be tracked <= not removed on each ui update
                  // Note : the check on api.section.has( params.id ) is also performd on api.CZR_Helpers.register(), but here we use it to avoid setting up the click listeners more than once.
                  if ( ! api.section.has( params.id ) ) {
                        api.section( params.id, function( _section_ ) {
                              $( _section_.container ).on( 'click', '.customize-control label > .customize-control-title', function( evt ) {
                                    var $control = $(this).closest( '.customize-control');
                                    if ( "true" == $control.attr('data-sek-expanded' ) )
                                      return;
                                    _section_.container.find('.customize-control').each( function() {
                                          $(this).attr('data-sek-expanded', "false" );
                                          $(this).find('.czr-items-wrapper').stop( true, true ).slideUp( 'fast' );
                                    });


                                    $control.attr('data-sek-expanded', "false" == $control.attr('data-sek-expanded') ? "true" : "false" );
                                    $control.find('.czr-items-wrapper').stop( true, true ).slideToggle( 'fast' );
                              });
                        });
                  }

                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : params.id,
                        title: sektionsLocalizedData.i18n['Settings for the'] + ' ' + params.level,
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 10,
                        track : false//don't register in the self.registered()
                        //constructWith : MainSectionConstructor,
                  }).done( function() {
                        // - Defer the registration when the parent section gets added to the api
                        // - Implement the module visibility
                        api.section( params.id, function( _section_ ) {
                              _do_register_();
                        });
                  });

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );