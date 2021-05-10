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
            generateUIforGlobalOptions : function( params, dfd ) {
                  var self = this,
                      _id_ = sektionsLocalizedData.prefixForSettingsNotSaved + sektionsLocalizedData.optNameForGlobalOptions;

                  // Is the UI currently displayed the one that is being requested ?
                  // If so, visually remind the user that a module should be dragged
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                        return dfd;
                  }

                  // Prepare the module map to register
                  var registrationParams = {};
                  if ( _.isUndefined( sektionsLocalizedData.globalOptionsMap ) || ! _.isObject( sektionsLocalizedData.globalOptionsMap ) ) {
                        api.errare( '::generateUIforGlobalOptions => missing or invalid globalOptionsMap');
                        return dfd;
                  }

                  // Populate the registration params
                  _.each( sektionsLocalizedData.globalOptionsMap, function( mod_type, opt_name ) {
                        switch( opt_name ) {
                              case 'site_templates' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__site_templates',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Site templates'],
                                          icon : '<i class="material-icons sek-level-option-icon">devices</i>'
                                    };
                              break;
                              // Header and footer have been beta tested during 5 months and released in June 2019, in version 1.8.0
                              case 'global_header_footer':
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__header_footer',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Site wide header and footer'],
                                          icon : '<i class="material-icons sek-level-option-icon">web</i>'
                                    };
                              break;
                              case 'global_text' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__global_text',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Global text options for Nimble sections'],
                                          icon : '<i class="material-icons sek-level-option-icon">text_format</i>'
                                    };
                              break;
                              case 'widths' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__widths',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Site wide inner and outer sections widths'],
                                          icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                                    };
                              break;
                              case 'breakpoint' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__breakpoint',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Site wide breakpoint for Nimble sections'],
                                          expandAndFocusOnInit : false,
                                          icon : '<i class="material-icons sek-level-option-icon">devices</i>'
                                    };
                              break;
                              case 'performances' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__performances',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Site wide page speed optimizations'],
                                          icon : '<i class="material-icons sek-level-option-icon">network_check</i>'
                                    };
                              break;
                              case 'recaptcha' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__recaptcha',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Protect your contact forms with Google reCAPTCHA'],
                                          icon : '<i class="material-icons sek-level-option-icon">security</i>'
                                    };
                              break;
                              case 'global_revisions' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__global_revisions',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Revision history of global sections'],
                                          icon : '<i class="material-icons sek-level-option-icon">history</i>'
                                    };
                              break;
                              case 'global_imp_exp' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__global_imp_exp',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Export / Import global sections'],
                                          icon : '<i class="material-icons sek-level-option-icon">import_export</i>'
                                    };
                              break;
                              case 'global_reset' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__global_reset',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Remove the sections displayed in global locations'],
                                          icon : '<i class="material-icons sek-level-option-icon">delete</i>'
                                    };
                              break;
                              case 'beta_features' :
                                    // may 2021 not rendered anymore
                                    // registrationParams[ opt_name ] = {
                                    //       settingControlId : _id_ + '__beta_features',
                                    //       module_type : mod_type,
                                    //       controlLabel : sektionsLocalizedData.i18n['Beta features'],
                                    //       icon : '<i class="material-icons sek-level-option-icon">widgets</i>'
                                    // };
                              break;
                              default :
                                    api.errare('::generateUIforGlobalOptions => an option group could not be registered => ' + mod_type, opt_name );
                              break;
                        }//switch
                  });//_.each

                  // Let assign the global options to a var
                  var globalOptionDBValues = sektionsLocalizedData.globalOptionDBValues;

                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
                              if ( 'site_templates' === optionType ) {
                                    var _doThingsAfterRefresh = function() {
                                          // setTimeout( function() {
                                          //       api.control( optionData.settingControlId ).focus();
                                          // }, 300 );
                                          api.trigger('nimble-update-topbar-skope-status');
                                          api.previewer.trigger('sek-notify', {
                                                type : 'info',
                                                duration : 20000,
                                                message : [
                                                      '<span style="">',
                                                            //'<strong>' + sektionsLocalizedData.i18n['Template saved'] + '</strong>',
                                                            sektionsLocalizedData.i18n['Refreshed to home page : site templates must be set when previewing home'],
                                                      '</span>'
                                                ].join('')
                                          });
                                          api.previewer.unbind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                                          setTimeout( function() {
                                                // This property is used to avoid the automatic focus on content picker when forcing preview on home while modifying site templates
                                                api._nimbleRefreshingPreviewHomeWhenSettingSiteTemplate = false;
                                          }, 1000);
                                    };
                              }

                              if ( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                isGlobalOptions : true,//<= indicates that we won't update the local skope setting id
                                                defaultPreviewAction : 'refresh_preview',
                                                uiParams : params,
                                                options_type : optionType,
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforGlobalOptions => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };

                                    // Schedule the binding to synchronize the options with the main collection setting
                                    // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                    // They need to be kept in order to keep track of the changes in the customizer.
                                    // => that's why we check if ! api.has( ... )
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          // SITE TEMPLATE STUFFS
                                          // Added March 2021 for #478
                                          // Force preview to home when modifying the site templates
                                          if ( 'site_templates' === optionType ) {
                                                _setting_.bind( function( to ) {
                                                      // This property is used to avoid the automatic focus on content picker when forcing preview on home while modifying site templates
                                                      api._nimbleRefreshingPreviewHomeWhenSettingSiteTemplate = true;//<= set to false in _doThingsAfterRefresh
                                                      api.previewer.bind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                                                      api.previewer.previewUrl( api.settings.url.home );
                                                      api.trigger('nimble-update-topbar-skope-status');
                                                });
                                          }

                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });//api( Id, function( _setting_ ) {})

                                    // Let's add the starting values if provided when registrating the module
                                    var startingModuleValue = self.getModuleStartingValue( optionData.module_type ),
                                        initialModuleValues = ( _.isObject( globalOptionDBValues ) && ! _.isEmpty( globalOptionDBValues[ optionType ] ) ) ? globalOptionDBValues[ optionType ] : {};

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
                                          transport : 'postMessage',//'refresh',//// ,
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                    });
                              }

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'control',
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : self.SECTION_ID_FOR_GLOBAL_OPTIONS,//registered in ::initialize()
                                    priority : 20,
                                    settings : { default : optionData.settingControlId },
                                    track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                              }).done( function() {
                                    // if ( true === optionData.expandAndFocusOnInit ) {
                                    //       api.control( optionData.settingControlId ).focus({
                                    //             completeCallback : function() {}
                                    //       });
                                    // }

                                    // Implement the animated arrow markup, and the initial state of the module visibility
                                    api.control( optionData.settingControlId, function( _control_ ) {
                                          // Hide the item wrapper
                                          // @see css
                                          _control_.container.attr('data-sek-expanded', "false" );
                                          var $title = _control_.container.find('label > .customize-control-title'),
                                              _titleContent = $title.html();
                                          // We wrap the original text content in this span.sek-ctrl-accordion-title in order to style it (underlined) independently ( without styling the icons next to it )
                                          $title.html( [
                                                '<span class="sek-ctrl-accordion-title">',
                                                _titleContent,
                                                //'site_templates' === optionType ? '&nbsp;<span class="sek-new-label">New!</span>' : '',
                                                '</span>'
                                          ].join('') );

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

                                          if ( 'site_templates' === optionType ) {
                                                _control_.container.one('click', '.customize-control-title', function() {
                                                      // This property is used to avoid the automatic focus on content picker when forcing preview on home while modifying site templates
                                                      api._nimbleRefreshingPreviewHomeWhenSettingSiteTemplate = true;//<= set to false in _doThingsAfterRefresh
                                                      api.previewer.bind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                                                      api.previewer.previewUrl( api.settings.url.home );
                                                      api.trigger('nimble-update-topbar-skope-status');
                                                });
                                          }

                                    });
                              });
                        });//_.each();
                  };//do register

                  // The parent section has already been added in ::initialize()
                  _do_register_();

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );