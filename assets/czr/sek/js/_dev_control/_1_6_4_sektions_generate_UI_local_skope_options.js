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

                  // Prepare the module map to register
                  var registrationParams = {};
                  $.extend( registrationParams, {
                        template : {
                              settingControlId : _id_ + '__template',
                              module_type : 'sek_local_template',
                              controlLabel : sektionsLocalizedData.i18n['Page template'],
                              expandAndFocusOnInit : false,
                              icon : '<i class="material-icons sek-level-option-icon">check_box_outline_blank</i>'
                        }
                  });

                  // Header and footer have been introduced in v1.4.0 but not enabled by default.
                  if ( sektionsLocalizedData.isNimbleHeaderFooterEnabled ) {
                        $.extend( registrationParams, {
                              local_header_footer : {
                                    settingControlId : _id_ + '__local_header_footer',
                                    module_type : 'sek_local_header_footer',
                                    controlLabel : sektionsLocalizedData.i18n['Page header and footer'],
                                    icon : '<i class="material-icons sek-level-option-icon">web</i>'
                              }
                        });
                  }

                  $.extend( registrationParams, {
                        widths : {
                              settingControlId : _id_ + '__widths',
                              module_type : 'sek_local_widths',
                              controlLabel : sektionsLocalizedData.i18n['Inner and outer widths'],
                              icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                        },
                        custom_css : {
                              settingControlId : _id_ + '__custom_css',
                              module_type : 'sek_local_custom_css',
                              controlLabel : sektionsLocalizedData.i18n['Custom CSS'],
                              icon : '<i class="material-icons sek-level-option-icon">code</i>'
                        },
                        local_performances : {
                              settingControlId : _id_ + '__local_performances',
                              module_type : 'sek_local_performances',
                              controlLabel : sektionsLocalizedData.i18n['Page speed optimizations'],
                              icon : '<i class="fas fa-fighter-jet sek-level-option-icon"></i>'
                        },
                        local_reset : {
                              settingControlId : _id_ + '__local_reset',
                              module_type : 'sek_local_reset',
                              controlLabel : sektionsLocalizedData.i18n['Remove the sections in this page'],
                              icon : '<i class="material-icons sek-level-option-icon">cached</i>'
                        }
                  });

                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh',
                                                uiParams : params,
                                                options_type : optionType,
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforLocalSkopeOptions => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };

                                    // Schedule the binding to synchronize the options with the main collection setting
                                    // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                    // They need to be kept in order to keep track of the changes in the customizer.
                                    // => that's why we check if ! api.has( ... )
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });//api( Id, function( _setting_ ) {})

                                    // Let's add the starting values if provided when registrating the module
                                    var startingModuleValue = self.getModuleStartingValue( optionData.module_type ),
                                        currentSetValue = api( self.localSectionsSettingId() )(),
                                        allSkopeOptions = $.extend( true, {}, _.isObject( currentSetValue.local_options ) ? currentSetValue.local_options : {} ),
                                        optionTypeValue = _.isObject( allSkopeOptions[ optionType ] ) ? allSkopeOptions[ optionType ]: {},
                                        initialModuleValues = optionTypeValue;

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
                                    section : self.SECTION_ID_FOR_LOCAL_OPTIONS,
                                    priority : 10,
                                    settings : { default : optionData.settingControlId },
                                    //track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
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
                                          var $title = _control_.container.find('label > .customize-control-title'),
                                              _titleContent = $title.html();
                                          // We wrap the original text content in this span.sek-ctrl-accordion-title in order to style it (underlined) independently ( without styling the icons next to it )
                                          $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );

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
                  };//_do_register()

                  // The parent section has already been added in ::initialize()
                  _do_register_();

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );