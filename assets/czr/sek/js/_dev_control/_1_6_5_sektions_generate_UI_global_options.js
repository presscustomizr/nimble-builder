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
                      _id_ = sektionsLocalizedData.optPrefixForSektionsNotSaved + sektionsLocalizedData.optNameForGlobalOptions;

                  // Is the UI currently displayed the one that is being requested ?
                  // If so, visually remind the user that a module should be dragged
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                        return dfd;
                  }

                  // Prepare the module map to register
                  var registrationParams = {};

                  $.extend( registrationParams, {
                        breakpoint : {
                              settingControlId : _id_ + '__breakpoint',
                              module_type : 'sek_global_breakpoint',
                              controlLabel : sektionsLocalizedData.i18n['Site wide breakpoint for Nimble sections'],
                              expandAndFocusOnInit : true,
                              icon : '<i class="material-icons sek-level-option-icon">devices</i>'
                        },
                        widths : {
                              settingControlId : _id_ + '__widths',
                              module_type : 'sek_global_widths',
                              controlLabel : sektionsLocalizedData.i18n['Site wide inner and outer sections widths'],
                              icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                        },
                        performances : {
                              settingControlId : _id_ + '__performances',
                              module_type : 'sek_global_performances',
                              controlLabel : sektionsLocalizedData.i18n['Site wide page speed optimizations'],
                              icon : '<i class="fas fa-fighter-jet sek-level-option-icon"></i>'
                        }
                  });

                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    // Schedule the binding to synchronize the options with the main collection setting
                                    // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                    // They need to be kept in order to keep track of the changes in the customizer.
                                    // => that's why we check if ! api.has( ... )
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( function( to, from, args ) {
                                                try { self.updateAPISettingAndExecutePreviewActions({
                                                      isGlobalOptions : true,//<= indicates that we won't update the local skope setting id
                                                      defaultPreviewAction : 'refresh',
                                                      uiParams : params,
                                                      options_type : optionType,
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
                                    var dbValues = sektionsLocalizedData.globalOptionDBValues,
                                        startingModuleValue = self.getModuleStartingValue( optionData.module_type ),
                                        initialModuleValues = ( _.isObject( dbValues ) && ! _.isEmpty( dbValues[ optionType ] ) ) ? dbValues[ optionType ] : {};

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
                                    section : '__globalAndLocalOptionsSection',//registered in ::initialize()
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
                        });//_.each();
                  };//do register

                  // Defer the registration when the parent section gets added to the api
                  // the section '__globalAndLocalOptionsSection' is registered in ::initialize()
                  api.section( '__globalAndLocalOptionsSection', function( _section_ ) {
                        api( self.sekCollectionSettingId(), function() {
                              _do_register_();
                        });
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );