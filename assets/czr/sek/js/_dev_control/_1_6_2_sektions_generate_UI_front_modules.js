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

                  // For modules, we need to generate a UI for the module value
                  var moduleValue = self.getLevelProperty({
                        property : 'value',
                        id : params.id
                  });

                  var moduleType = self.getLevelProperty({
                        property : 'module_type',
                        id : params.id
                  });

                  var moduleName = self.getRegisteredModuleProperty( moduleType, 'name' );

                  if ( _.isEmpty( moduleType ) ) {
                        dfd.reject( 'generateUI => module => invalid module_type' );
                  }

                  // Prepare the module map to register
                  var modulesRegistrationParams = {};

                  if ( true === self.getRegisteredModuleProperty( moduleType, 'is_father' ) ) {
                        var _childModules_ = self.getRegisteredModuleProperty( moduleType, 'children' );
                        if ( _.isEmpty( _childModules_ ) ) {
                              throw new Error('::generateUIforFrontModules => a father module ' + moduleType + ' is missing children modules ');
                        } else {
                              _.each( _childModules_, function( mod_type, optionType ){
                                    modulesRegistrationParams[ optionType ] = {
                                          settingControlId : params.id + '__' + optionType,
                                          module_type : mod_type,
                                          controlLabel : self.getRegisteredModuleProperty( mod_type, 'name' )
                                          //icon : '<i class="material-icons sek-level-option-icon">code</i>'
                                    };
                              });
                        }
                  } else {
                        modulesRegistrationParams.__no_option_group_to_be_updated_by_children_modules__ = {
                              settingControlId : params.id,
                              module_type : moduleType,
                              controlLabel : moduleName
                              //icon : '<i class="material-icons sek-level-option-icon">code</i>'
                        };
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
                  self.cleanRegisteredAndLargeSelectInput();

                  _do_register_ = function() {
                        _.each( modulesRegistrationParams, function( optionData, optionType ){
                              // Make sure this setting is bound only once !
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh_markup',
                                                uiParams : _.extend( params, { action : 'sek-set-module-value' } ),
                                                options_type : optionType,
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforFrontModules => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };

                                    // Schedule the binding to synchronize the module setting with the main collection setting
                                    // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                    // They need to be kept in order to keep track of the changes in the customizer.
                                    // => that's why we check if ! api.has( ... )
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });

                                    var settingValueOnRegistration = $.extend( true, {}, moduleValue );
                                    if ( '__no_option_group_to_be_updated_by_children_modules__' !== optionType ) {
                                          settingValueOnRegistration = ( !_.isEmpty( settingValueOnRegistration ) && _.isObject( settingValueOnRegistration ) && _.isObject( settingValueOnRegistration[optionType] ) ) ? settingValueOnRegistration[optionType] : {};
                                    }
                                    api.CZR_Helpers.register({
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : settingValueOnRegistration,
                                          transport : 'postMessage',// 'refresh',
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                    });
                              }//if ( ! api.has( optionData.settingControlId ) )


                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'control',
                                    module_id : params.id,// <= the id of the corresponding module level as saved in DB. Can be needed, @see image slider module
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    //label : sektionsLocalizedData.i18n['Customize the options for module :'] + ' ' + optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : params.id,
                                    priority : 20,
                                    settings : { default : optionData.settingControlId }
                              }).done( function() {});

                              // Implement the animated arrow markup, and the initial state of the module visibility
                              api.control( optionData.settingControlId, function( _control_ ) {
                                    api.control( optionData.settingControlId ).focus({
                                          completeCallback : function() {}
                                    });

                                    // Hide the item wrapper
                                    // @see css
                                    _control_.container.attr('data-sek-expanded', "false" );

                                    var $title = _control_.container.find('label > .customize-control-title'),
                                        // store the title text in a var + decode html entities ( added by WP )
                                        // @see https://stackoverflow.com/questions/1147359/how-to-decode-html-entities-using-jquery
                                        _titleContent = $("<div/>").html( $title.html() ).text();

                                    $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent , '</span>' ].join('') );
                                    // if this level has an icon, let's prepend it to the title
                                    if ( ! _.isUndefined( optionData.icon ) ) {
                                          $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                    }
                                    // prepend the animated arrow
                                    $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                    // setup the initial state + initial click
                                    _control_.container.attr('data-sek-expanded', "false" );
                              });
                        });//each()
                  };//_do_register()


                  // Defer the registration when the parent section gets added to the api
                  api.section( params.id, function( _section_ ) {
                        api.section(params.id).focus();
                        // Generate the UI for module option switcher
                        // introduded in july 2019 for https://github.com/presscustomizr/nimble-builder/issues/135
                        self.generateModuleOptionSwitcherUI( params.id, params.action );
                        _do_register_();

                        // don't display the clickable section title in the nimble root panel
                        _section_.container.find('.accordion-section-title').first().hide();

                        // Style the section title
                        var $panelTitleEl = _section_.container.find('.customize-section-title h3');

                        // The default title looks like this : <span class="customize-action">Customizing</span> Title
                        if ( 0 < $panelTitleEl.length ) {
                              $panelTitleEl.find('.customize-action').after( '<i class="fas fa-pencil-alt sek-level-option-icon"></i>' );
                        }

                        // Schedule the accordion behaviour
                        self.scheduleModuleAccordion.call( _section_, { expand_first_control : true } );
                  });

                  // Register the module content section
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : params.id,
                        title: sektionsLocalizedData.i18n['Content for'] + ' ' + moduleName,
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 1000,
                        //track : false//don't register in the self.registered()
                        //constructWith : MainSectionConstructor,
                  }).done( function() {});

                  return dfd;
            },

            // Generate the UI for module option switcher
            // introduded in july 2019 for https://github.com/presscustomizr/nimble-builder/issues/135
            // REGISTER MODULE OPTION SWITCHER SETTING AND CONTROL
            generateModuleOptionSwitcherUI : function( module_id, ui_action ) {
                  var setCtrlId = module_id + '__' + 'option_switcher';

                  if ( ! api.has( setCtrlId ) ) {
                        // synchronize the module setting with the main collection setting
                        api( setCtrlId, function( _setting_ ) {
                              _setting_.bind( function( to, from ) {
                                    api.errare('generateUIforDraggableContent => the setting() should not changed');
                              });
                        });
                        api.CZR_Helpers.register( {
                              origin : 'nimble',
                              level : 'module',
                              what : 'setting',
                              id : setCtrlId,
                              dirty : false,
                              value : '',
                              transport : 'postMessage',// 'refresh',
                              type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                        });
                  }

                  api.CZR_Helpers.register( {
                        origin : 'nimble',
                        level : 'module',
                        what : 'control',
                        module_id : module_id,// <= the id of the corresponding module level as saved in DB
                        id : setCtrlId,
                        label : '',
                        type : 'czr_module',//sekData.controlType,
                        module_type : 'sek_mod_option_switcher_module',
                        section : module_id,
                        priority : 10,
                        settings : { default : setCtrlId },
                        has_accordion : false,
                        ui_action : ui_action // 'sek-generate-module-ui' or 'sek-generate-level-options-ui' // <= will be used to determine which button is selected
                  }).done( function() {
                        api.control( setCtrlId, function( _control_ ) {
                              _control_.deferred.embedded.done( function() {
                                    // Hide the control label
                                    _control_.container.find('.customize-control-title').hide();
                                    // don't setup the accordion
                                    _control_.container.attr('data-sek-accordion', 'no');
                              });
                        });
                  });
            }//generateModuleOptionSwitcherUI

      });//$.extend()
})( wp.customize, jQuery );