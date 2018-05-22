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
            // @return promise()
            generateUI : function( params ) {
                  var self = this,
                      dfd = $.Deferred(),
                      _do_register_;

                  if ( _.isEmpty( params.action ) ) {
                        dfd.reject( 'generateUI => missing action' );
                  }

                  // REGISTER SETTING AND CONTROL
                  switch ( params.action ) {








                        // Possible content types :
                        // 1) module
                        // 2) preset_section
                        case 'sek-generate-draggable-candidates-picker-ui' :
                              var _id_ = sektionsLocalizedData.optPrefixForSektionsNotSaved + ( 'module' === params.content_type ? '_sek_draggable_modules_ui' : '_sek_draggable_sections_ui' );
                              // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, imply focus on it
                              if ( self.isUIElementCurrentlyGenerated( _id_ ) ) {
                                    api.control( _id_ ).focus({
                                          completeCallback : function() {}
                                    });
                                    break;
                              }
                              // Clean previously generated UI elements
                              self.cleanRegistered();
                              _do_register_ = function() {
                                    if ( ! api.has( _id_ ) ) {
                                          // synchronize the module setting with the main collection setting
                                          api( _id_, function( _setting_ ) {
                                                _setting_.bind( function( to, from ) {
                                                      api.errare('MODULE / SECTION PICKER SETTING CHANGED');
                                                });
                                          });
                                          self.register( {
                                                level : params.level,
                                                what : 'setting',
                                                id : _id_,
                                                dirty : false,
                                                value : '',
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_'// columnData.settingType
                                          });
                                    }

                                    self.register( {
                                          level : params.level,
                                          what : 'control',
                                          id : _id_,
                                          label : 'module' === params.content_type ? '@missi18n Module Picker' : '@missi18n Section Picker',
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'module' === params.content_type ? 'sek_module_picker_module' : 'sek_section_picker_module',
                                          section : _id_,
                                          priority : 10,
                                          settings : { default : _id_ }
                                    }).done( function() {
                                          api.control( _id_ ).focus({
                                              completeCallback : function() {}
                                          });
                                    });
                              };

                              // Defer the registration when the parent section gets added to the api
                              api.section.when( _id_, function() {
                                    _do_register_();
                              });

                              // MODULE / SECTION PICKER SECTION
                              self.register({
                                    what : 'section',
                                    id : _id_,
                                    title: 'module' === params.content_type ? '@missi18n Module Picker' : '@missi18n Section Picker',
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 30,
                                    //track : false//don't register in the self.registered()
                                    //constructWith : MainSectionConstructor,
                              });
                        break;
















                        case 'sek-generate-module-ui' :
                              if ( _.isEmpty( params.id ) ) {
                                    dfd.reject( 'generateUI => missing id' );
                              }
                              // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, simply focus on it
                              if ( self.isUIElementCurrentlyGenerated( params.id ) ) {
                                    api.control( params.id ).focus({
                                          completeCallback : function() {}
                                    });
                                    break;
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

                                          self.register({
                                                level : params.level,
                                                what : 'setting',
                                                id : params.id,
                                                dirty : false,
                                                value : moduleValue,
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_'// columnData.settingType
                                          });
                                    }



                                    self.register( {
                                          level : params.level,
                                          what : 'control',
                                          id : params.id,
                                          label : '@missi18n Module ' + params.id,
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
                              self.register({
                                    what : 'section',
                                    id : params.id,
                                    title: '@missi18n Content for ' + api.czrModuleMap[ moduleType ].name,
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 20,
                                    //track : false//don't register in the self.registered()
                                    //constructWith : MainSectionConstructor,
                              });

                        break;














                        case 'sek-generate-level-options-ui' :
                              // Generate the UI for level options

                              var layoutBgBorderOptionsSetId = params.id + '__layoutBgBorder_options',
                                  spacingOptionsSetId = params.id + '__spacing_options';

                              // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, imply focus on it
                              if ( self.isUIElementCurrentlyGenerated( layoutBgBorderOptionsSetId ) || self.isUIElementCurrentlyGenerated( spacingOptionsSetId ) ) {
                                    api.control( layoutBgBorderOptionsSetId ).focus({
                                          completeCallback : function() {}
                                    });
                                    break;
                              }

                              // Clean previously generated UI elements
                              self.cleanRegistered();

                              var controlLabel = '',
                                  optionDBValue = self.getLevelProperty({
                                        property : 'options',
                                        id : params.id
                                  });
                              optionDBValue = _.isObject( optionDBValue ) ? optionDBValue : {};
                              _do_register_ = function() {
                                    // REGISTER LAYOUT BACKGROUND BORDER OPTIONS
                                    // Make sure this setting is bound only once !
                                    if( ! api.has( layoutBgBorderOptionsSetId ) ) {
                                          // Schedule the binding to synchronize the options with the main collection setting
                                          // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                          // They need to be kept in order to keep track of the changes in the customizer.
                                          // => that's why we check if ! api.has( ... )
                                          api( layoutBgBorderOptionsSetId, function( _setting_ ) {
                                                _setting_.bind( _.debounce( function( to, from, args ) {
                                                      try { self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_stylesheet',
                                                            uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                            options_type : 'layout_background_border',
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      }); } catch( er ) {
                                                            api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                                      }
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });//api( layoutBgBorderOptionsSetId, function( _setting_ ) {})


                                          self.register( {
                                                level : params.level,
                                                what : 'setting',
                                                id : layoutBgBorderOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.lbb || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_' //sekData.settingType
                                          });
                                    }//if( ! api.has( layoutBgBorderOptionsSetId ) ) {

                                    self.register( {
                                          level : params.level,
                                          level_id : params.id,
                                          what : 'control',
                                          id : layoutBgBorderOptionsSetId,
                                          label : '@missi18n Layout Background and Border',
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'sek_level_layout_bg_module',
                                          section : params.id,
                                          priority : 10,
                                          settings : { default : layoutBgBorderOptionsSetId }
                                    }).done( function() {
                                          api.control( layoutBgBorderOptionsSetId ).focus({
                                                completeCallback : function() {}
                                          });
                                    });




                                    // REGISTER SPAGING OPTIONS
                                    // Make sure this setting is bound only once !
                                    if( ! api.has( spacingOptionsSetId ) ) {
                                          // Schedule the binding to synchronize the options with the main collection setting
                                          // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                          // They need to be kept in order to keep track of the changes in the customizer.
                                          // => that's why we check if ! api.has( ... )
                                          api( spacingOptionsSetId, function( _setting_ ) {
                                                _setting_.bind( _.debounce( function( to, from, args ) {
                                                      try { self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_stylesheet',
                                                            uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                            options_type : 'spacing',
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      }); } catch( er ) {
                                                            api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                                      }
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });//api( layoutBgBorderOptionsSetId, function( _setting_ ) {})


                                          self.register( {
                                                level : params.level,
                                                what : 'setting',
                                                id : spacingOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.spacing || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_' //sekData.settingType
                                          });
                                    }



                                    self.register( {
                                          level : params.level,
                                          what : 'control',
                                          id : spacingOptionsSetId,
                                          label : '@missi18n Spacing : padding and margin',
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'sek_spacing_module',
                                          section : params.id,
                                          priority : 10,
                                          settings : { default : spacingOptionsSetId }
                                    }).done( function() {
                                          // synchronize the options with the main collection setting
                                          api.control( spacingOptionsSetId ).focus({
                                                completeCallback : function() {}
                                          });
                                    });
                              };//_do_register_

                              // Defer the registration when the parent section gets added to the api
                              api.section.when( params.id, function() {
                                    _do_register_();
                              });

                              self.register({
                                    what : 'section',
                                    id : params.id,
                                    title: '@missi18n Options for ' + params.level,
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 10,
                                    track : false//don't register in the self.registered()
                                    //constructWith : MainSectionConstructor,
                              });
                        break;
                  }//switch

                  return 'pending' == dfd.state() ? dfd.resolve().promise() : dfd.promise();//<= we might want to resolve on focus.completeCallback ?
            },//generateUI()




            // This method
            // @params = {
            //     uiParams : params,
            //     options_type : 'layout_background_border',
            //     settingParams : {
            //           to : to,
            //           from : from,
            //           args : args
            //     }
            // }
            //
            // @param settingParams.args = {
            //  inputRegistrationParams : {
            //     id :,
            //     type :
            //     refresh_markup : bool
            //     refresh_stylesheet : bool
            //     refresh_fonts : bool
            //  }
            //  input_changed : input_id
            //  input_transport : 'inherit'/'postMessage',
            //  module : { items : [...]}
            //  module_id :
            //  not_preview_sent : bool
            //}
            //
            // Note 1 : this method must handle two types of modules :
            // 1) mono item modules, for which the settingParams.to is an object, a single item object
            // 2) multi-items modules, for which the settingParams.to is an array, a collection of item objects
            // How do we know that we are a in single / multi item module ?
            //
            // Note 2 : we must also handle several scenarios of module value update :
            // 1) mono-items and multi-items module => input change
            // 2) crud multi item => item added or removed => in this case some args are not passed, like params.settingParams.args.inputRegistrationParams
            updateAPISettingAndExecutePreviewActions : function( params ) {
                  console.log('PARAMS in updateAPISettingAndExecutePreviewActions', params );
                  if ( _.isEmpty( params.settingParams ) || ! _.has( params.settingParams, 'to' ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing params.settingParams.to. The api main setting can not be updated', params );
                        return;
                  }
                  var self = this;

                  // NORMALIZE THE VALUE WE WANT TO WRITE IN THE MAIN SETTING
                  // 1) We don't want to store the default title and id module properties
                  // 2) We don't want to write in db the properties that are set to their default values
                  var rawModuleValue = params.settingParams.to,
                      moduleValueCandidate,// {} or [] if mono item of multi-item module
                      inputDefaultValue = null,
                      parentModuleType = null,
                      isMultiItemModule = false;

                  console.log('module control => ', params.settingParams.args.moduleRegistrationParams.control );
                  if ( _.isEmpty( params.settingParams.args ) || ! _.has( params.settingParams.args, 'moduleRegistrationParams' ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing params.settingParams.args.moduleRegistrationParams The api main setting can not be updated', params );
                        return;
                  }

                  var _ctrl_ = params.settingParams.args.moduleRegistrationParams.control,
                      _module_id_ = params.settingParams.args.moduleRegistrationParams.id,
                      parentModuleInstance = _ctrl_.czr_Module( _module_id_ );

                  if ( ! _.isEmpty( parentModuleInstance ) ) {
                        parentModuleType = parentModuleInstance.module_type;
                        isMultiItemModule = parentModuleInstance.isMultiItem();
                  }

                  // @return {}
                  var normalizeSingleItemValue = function( _item_ ) {
                        var itemCandidate = {};
                        _.each( _item_, function( _val, input_id ) {
                              if ( _.contains( ['title', 'id' ], input_id ) )
                                return;

                              if ( null !== parentModuleType ) {
                                    inputDefaultValue = self.getInputDefaultValue( input_id, parentModuleType );
                                    if ( 'no_default_value_specified' === inputDefaultValue ) {
                                          api.infoLog( '::updateAPISettingAndExecutePreviewActions => missing default value for input ' + input_id + ' in module ' + parentModuleType );
                                    }
                              }
                              if ( _val === inputDefaultValue ) {
                                    return;
                              } else {
                                    itemCandidate[ input_id ] = _val;
                              }
                        });
                        return itemCandidate;
                  };

                  // The new module value can be an single item object if monoitem module, or an array of item objects if multi-item crud
                  // Let's normalize it
                  if ( ! isMultiItemModule && ! _.isObject( rawModuleValue ) ) {
                        moduleValueCandidate = normalizeSingleItemValue( rawModuleValue );
                  } else {
                        moduleValueCandidate = [];
                        _.each( rawModuleValue, function( item ) {
                              moduleValueCandidate.push( normalizeSingleItemValue( item ) );
                        });
                  }

                  // What to do in the preview ?
                  // The action to trigger is determined by the changed input
                  // For the options of a level, the default action is to refresh the stylesheet.
                  // But we might need to refresh the markup in some cases. Like for example when a css class is added. @see the boxed-wide layout example
                  if ( _.isEmpty( params.defaultPreviewAction ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing defaultPreviewAction in passed params. No action can be triggered to the api.previewer.', params );
                        return;
                  }
                  // Set the default value
                  var refresh_stylesheet = 'refresh_stylesheet' === params.defaultPreviewAction,//<= default action for level options
                      refresh_markup = 'refresh_markup' === params.defaultPreviewAction,//<= default action for module options
                      refresh_fonts = 'refresh_fonts' === params.defaultPreviewAction;

                  // Maybe set the input based value
                  // Note : the inputRegistrationParams are passed in the args only when an module input is changed
                  // Example : For a crud module, when an item is added, there are no inputRegistrationParams, so we fallback on the default 'refresh_markup'
                  if ( ! _.isEmpty( params.settingParams.args.inputRegistrationParams ) ) {
                        if ( ! _.isUndefined( params.settingParams.args.inputRegistrationParams.refresh_stylesheet ) ) {
                              refresh_stylesheet = Boolean( params.settingParams.args.inputRegistrationParams.refresh_stylesheet );
                        }
                        if ( ! _.isUndefined( params.settingParams.args.inputRegistrationParams.refresh_markup ) ) {
                              refresh_markup = Boolean( params.settingParams.args.inputRegistrationParams.refresh_markup );
                        }
                        if ( ! _.isUndefined( params.settingParams.args.inputRegistrationParams.refresh_fonts ) ) {
                              refresh_fonts = Boolean( params.settingParams.args.inputRegistrationParams.refresh_fonts );
                        }
                  }

                  var _doUpdateWithRequestedAction = function() {
                        return self.updateAPISetting({
                              action : params.uiParams.action,
                              id : params.uiParams.id,
                              value : moduleValueCandidate,
                              in_column : params.uiParams.in_column,
                              in_sektion : params.uiParams.in_sektion,

                              // specific for level options
                              options_type : params.options_type,//'spacing', 'layout_background_border'

                        }).done( function( ) {
                              // STYLESHEET => default action when modifying the level options
                              if ( true === refresh_stylesheet ) {
                                    api.previewer.send( 'sek-refresh-stylesheet', {
                                          skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                    });
                              }

                              // MARKUP
                              if ( true === refresh_markup ) {
                                    api.previewer.send( 'sek-refresh-level', {
                                          apiParams : {
                                                action : 'sek-refresh-level',
                                                id : params.uiParams.id,
                                                level : params.uiParams.level
                                          },
                                          skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                    });
                              }
                        });//self.updateAPISetting()
                  };//_doUpdateWithRequestedAction

                  // if the changed input is a google font modifier ( <=> font_family_css input)
                  // => we want to first refresh the google font collection, and then proceed to the requested action
                  // this way we make sure that the customized value used when ajaxing will take into account when writing the google font http request link
                  if ( true === refresh_fonts ) {
                        var _getChangedFontFamily = function() {
                              if ( 'font_family_css' != params.settingParams.args.input_changed ) {
                                    api.errare( 'updateAPISettingAndExecutePreviewActions => Error when refreshing fonts => the input id is not font_family_css', params );
                                    return;
                              } else {
                                    return params.settingParams.args.input_value;
                              }
                        };
                        var newFontFamily = '';
                        try { newFontFamily = _getChangedFontFamily(); } catch( er) {
                              api.errare( 'updateAPISettingAndExecutePreviewActions => Error when refreshing fonts', er );
                              return;
                        }
                        if ( ! _.isString( newFontFamily ) ) {
                              api.errare( 'updateAPISettingAndExecutePreviewActions => font-family must be a string', er );
                              return;
                        }
                        // add it only if gfont
                        if ( newFontFamily.indexOf('gfont') > -1 ) {
                              self.updateAPISetting({
                                    action : 'sek-update-fonts',
                                    font_family : newFontFamily
                              }).done( function( ) {
                                    _doUpdateWithRequestedAction().then( function() {
                                          // always refresh again after
                                          // Why ?
                                          // Because the first refresh was done before actually setting the new font family, so based on a previous set of fonts
                                          // which leads to have potentially an additional google fonts that we don't need after the first refresh
                                          // that's why this second refresh is required. It wont trigger any preview ajax actions. Simply refresh the root fonts property of the main api setting.
                                          self.updateAPISetting({ action : 'sek-update-fonts' } );
                                    });
                              });
                        } else {
                             _doUpdateWithRequestedAction();
                        }
                  } else {
                        _doUpdateWithRequestedAction();
                  }
            },//updateAPISettingAndExecutePreviewActions






            // Is the UI currently displayed the one that is being requested ?
            // If so, don't generate the ui again
            // @return bool
            isUIElementCurrentlyGenerated : function( uiElementId ) {
                  var self = this,
                      uiCandidate = _.filter( self.registered(), function( registered ) {
                            return registered.id == uiElementId && 'control' == registered.what;
                      });
                  if ( _.isEmpty( uiCandidate ) ) {
                        return false;
                  } else {
                        // we have match => don't generate the ui
                        // we should have only one uiCandidate with this very id
                        if ( uiCandidate.length > 1 ) {
                             throw new Error( 'generateUI => why is this control registered more than once ? => ' + uiElementId );
                        } else {
                              return true;
                        }
                  }
            }
      });//$.extend()
})( wp.customize, jQuery );