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
                  var modulesRegistrationParams = {};

                  $.extend( modulesRegistrationParams, {
                        bg : {
                              settingControlId : params.id + '__bg_options',
                              module_type : 'sek_level_bg_module',
                              controlLabel : sektionsLocalizedData.i18n['Background settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              expandAndFocusOnInit : true,
                              icon : '<i class="material-icons sek-level-option-icon">gradient</i>'//'<i class="material-icons sek-level-option-icon">brush</i>'
                        },
                        border : {
                              settingControlId : params.id + '__border_options',
                              module_type : 'sek_level_border_module',
                              controlLabel : sektionsLocalizedData.i18n['Borders settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              //expandAndFocusOnInit : true,
                              icon : '<i class="material-icons sek-level-option-icon">rounded_corner</i>'//'<i class="material-icons sek-level-option-icon">brush</i>'
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
                              controlLabel : sektionsLocalizedData.i18n['Custom anchor ( CSS ID ) and CSS classes for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
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
                              controlLabel : sektionsLocalizedData.i18n['Height and vertical alignment for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="fas fa-ruler-vertical sek-level-option-icon"></i>'
                        },
                  });

                  if ( 'section' === params.level ) {
                        $.extend( modulesRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_section',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                    icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                              }
                        });
                        // Deactivated
                        // => replaced by sek_level_width_section
                        // $.extend( modulesRegistrationParams, {
                        //       layout : {
                        //             settingControlId : params.id + '__sectionLayout_options',
                        //             module_type : 'sek_level_section_layout_module',
                        //             controlLabel : sektionsLocalizedData.i18n['Layout settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                        //             icon : '<i class="material-icons sek-level-option-icon">crop_din</i>'
                        //       }
                        // });
                        $.extend( modulesRegistrationParams, {
                              breakpoint : {
                                    settingControlId : params.id + '__breakpoint_options',
                                    module_type : 'sek_level_breakpoint_module',
                                    controlLabel : sektionsLocalizedData.i18n['Responsive settings : breakpoint, column direction'],
                                    icon : '<i class="material-icons sek-level-option-icon">devices</i>'
                              }
                        });
                  }
                  if ( 'column' === params.level ) {
                        $.extend( modulesRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_column',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                    icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                              }
                        });
                  }
                  if ( 'module' === params.level ) {
                        $.extend( modulesRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_module',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                    icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                              }
                        });
                  }


                  // BAIL WITH A SEE-ME ANIMATION IF THIS UI IS CURRENTLY BEING DISPLAYED
                  // Is the UI currently displayed the one that is being requested ?
                  // Check if the first control of the list is already registered
                  // If so, visually remind the user and break;
                  var firstKey = _.keys( modulesRegistrationParams )[0],
                      firstControlId = modulesRegistrationParams[firstKey].settingControlId;

                  if ( self.isUIControlAlreadyRegistered( firstControlId ) ) {
                        api.control( firstControlId ).focus({
                              completeCallback : function() {
                                    var $container = api.control( firstControlId ).container;
                                    // @use button-see-mee css class declared in core in /wp-admin/css/customize-controls.css
                                    if ( $container.hasClass( 'button-see-me') )
                                      return;
                                    $container.addClass('button-see-me');
                                    _.delay( function() {
                                         $container.removeClass('button-see-me');
                                    }, 800 );
                              }
                        });
                        return dfd;
                  }//if

                  // Clean previously generated UI elements
                  self.cleanRegistered();


                  // @return void()
                  _do_register_ = function() {
                        _.each( modulesRegistrationParams, function( optionData, optionType ){
                               // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, simply focus on the section
                              if ( self.isUIControlAlreadyRegistered( optionData.settingControlId ) ) {
                                    api.section( api.control( optionData.settingControlId ).section() ).expanded( true );
                                    return;
                              }
                              if( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
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
                                                api.errare( '::generateUIforLevelOptions => Error in updateAPISettingAndExecutePreviewActions', er );
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
                              }).done( function() {});

                              // Implement the animated arrow markup, and the initial state of the module visibility
                              api.control( optionData.settingControlId, function( _control_ ) {
                                    if ( true === optionData.expandAndFocusOnInit ) {
                                          _control_.focus({
                                                completeCallback : function() {}
                                          });
                                    }

                                    // Hide the item wrapper
                                    // @see css
                                    _control_.container.attr('data-sek-expanded', "false" );

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
                        });//_.each()
                  };//_do_register_()

                  // The section won't be tracked <= not removed on each ui update
                  // Note : the check on api.section.has( params.id ) is also performd on api.CZR_Helpers.register(), but here we use it to avoid setting up the click listeners more than once.
                  if ( ! api.section.has( params.id ) ) {
                        api.section( params.id, function( _section_ ) {
                              // Schedule the accordion behaviour
                              self.scheduleModuleAccordion.call( _section_, { expand_first_control : true } );
                        });
                  }

                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : params.id,
                        title: sektionsLocalizedData.i18n['Settings for the'] + ' ' + params.level,
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 10,
                        //track : false//don't register in the self.registered()
                        //constructWith : MainSectionConstructor,
                  }).done( function() {});

                  // - Defer the registration when the parent section gets added to the api
                  // - Implement the module visibility
                  api.section( params.id, function( _section_ ) {
                        _do_register_();
                        // don't display the clickable section title in the nimble root panel
                        _section_.container.find('.accordion-section-title').first().hide();

                        // Style the section title
                        var $panelTitleEl = _section_.container.find('.customize-section-title h3');

                        // The default title looks like this : <span class="customize-action">Customizing</span> Title
                        if ( 0 < $panelTitleEl.length && $panelTitleEl.find('.sek-level-option-icon').length < 1 ) {
                              $panelTitleEl.find('.customize-action').after( '<i class="fas fa-sliders-h sek-level-option-icon"></i>' );
                        }
                  });

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );