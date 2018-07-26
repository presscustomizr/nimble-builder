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

                  // Clean previously generated UI elements
                  self.cleanRegistered();

                  // REGISTER SETTING AND CONTROL
                  switch ( params.action ) {
                        // Possible content types :
                        // 1) module
                        // 2) preset_section
                        case 'sek-generate-draggable-candidates-picker-ui' :
                              dfd = self.generateUIforDraggableContent( params, dfd );
                        break;

                        case 'sek-generate-module-ui' :
                              dfd = self.generateUIforFrontModules( params, dfd );
                        break;

                        case 'sek-generate-level-options-ui' :
                              dfd = self.generateUIforLevelOptions( params, dfd );
                        break;

                        case 'sek-generate-local-skope-options-ui' :
                              dfd = self.generateUIforLocalSkopeOptions( params, dfd );
                        break;
                  }//switch

                  return 'pending' == dfd.state() ? dfd.resolve().promise() : dfd.promise();//<= we might want to resolve on focus.completeCallback ?
            },//generateUI()


            // @params = {
            //     uiParams : params,
            //     options_type : 'spacing',
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
                      parentModuleType = null,
                      isMultiItemModule = false;

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
                  } else {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing parentModuleInstance', params );
                  }

                  // The new module value can be a single item object if monoitem module, or an array of item objects if multi-item crud
                  // Let's normalize it
                  if ( ! isMultiItemModule && _.isObject( rawModuleValue ) ) {
                        moduleValueCandidate = self.normalizeAndSanitizeSingleItemInputValues( rawModuleValue, parentModuleType );
                  } else {
                        moduleValueCandidate = [];
                        _.each( rawModuleValue, function( item ) {
                              moduleValueCandidate.push( self.normalizeAndSanitizeSingleItemInputValues( item, parentModuleType ) );
                        });
                  }

                  // WHAT TO REFRESH IN THE PREVIEW ? Markup, stylesheet, font ?
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
                      refresh_fonts = 'refresh_fonts' === params.defaultPreviewAction,
                      refresh_preview = 'refresh_preview' === params.defaultPreviewAction;

                  // Maybe set the input based value
                  var input_id = params.settingParams.args.input_changed;
                  var inputRegistrationParams;
                  if ( ! _.isUndefined( input_id ) ) {
                        inputRegistrationParams = self.getInputRegistrationParams( input_id, parentModuleType );
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_stylesheet ) ) {
                              refresh_stylesheet = Boolean( inputRegistrationParams.refresh_stylesheet );
                        }
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_markup ) ) {
                              refresh_markup = Boolean( inputRegistrationParams.refresh_markup );
                        }
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_fonts ) ) {
                              refresh_fonts = Boolean( inputRegistrationParams.refresh_fonts );
                        }
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_preview ) ) {
                              refresh_preview = Boolean( inputRegistrationParams.refresh_preview );
                        }
                  }

                  var _doUpdateWithRequestedAction = function() {
                        return self.updateAPISetting({
                              action : params.uiParams.action,// mandatory : 'sek-generate-level-options-ui', 'sek_local_skope_options_module',...
                              id : params.uiParams.id,
                              value : moduleValueCandidate,
                              in_column : params.uiParams.in_column,//not mandatory
                              in_sektion : params.uiParams.in_sektion,//not mandatory

                              // specific for level options and local skope options
                              options_type : params.options_type,// mandatory : 'layout', 'spacing', 'bg_border', 'height', ...

                              settingParams : params.settingParams
                        }).done( function( ) {
                              // STYLESHEET => default action when modifying the level options
                              if ( true === refresh_stylesheet ) {
                                    api.previewer.send( 'sek-refresh-stylesheet', {
                                          skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                          apiParams : {
                                                action : 'sek-refresh-stylesheet',
                                                id : params.uiParams.id,
                                                level : params.uiParams.level
                                          },
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

                              // REFRESH THE PREVIEW ?
                              if ( true === refresh_preview ) {
                                    api.previewer.refresh();
                              }
                        });//self.updateAPISetting()
                  };//_doUpdateWithRequestedAction

                  // if the changed input is a google font modifier ( <=> true === refresh_fonts )
                  // => we want to first refresh the google font collection, and then proceed the requested action
                  // this way we make sure that the customized value used when ajaxing will take into account when writing the google font http request link
                  if ( true === refresh_fonts ) {
                        var newFontFamily = params.settingParams.args.input_value;
                        if ( ! _.isString( newFontFamily ) ) {
                              api.errare( 'updateAPISettingAndExecutePreviewActions => font-family must be a string', newFontFamily );
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












            // @return a normalized and sanitized item value
            normalizeAndSanitizeSingleItemInputValues : function( _item_, parentModuleType ) {
                  var itemNormalized = {},
                      itemNormalizedAndSanitized = {},
                      inputDefaultValue = null,
                      inputType = null,
                      sanitizedVal,
                      self = this;

                  // NORMALIZE
                  // title, id and module_type don't need to be saved in database
                  // title and id are legacy entries that can be used in multi-items modules to identify and name the item
                  // @see ::getDefaultItemModelFromRegisteredModuleData()
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
                              itemNormalized[ input_id ] = _val;
                        }
                  });

                  // SANITIZE
                  _.each( itemNormalized, function( _val, input_id ) {
                        // @see extend_api_base.js
                        // @see sektions::_7_0_sektions_add_inputs_to_api.js
                        switch( self.getInputType( input_id, parentModuleType ) ) {
                              case 'text' :
                              case 'textarea' :
                              case 'check' :
                              case 'gutencheck' :
                              case 'select' :
                              case 'radio' :
                              case 'number' :
                              case 'upload' :
                              case 'upload_url' :
                              case 'color' :
                              case 'wp_color_alpha' :
                              case 'wp_color' :
                              case 'content_picker' :
                              case 'tiny_mce_editor' :
                              case 'password' :
                              case 'range' :
                              case 'range_slider' :
                              case 'hidden' :
                              case 'h_alignment' :
                              case 'h_text_alignment' :

                              case 'spacing' :
                              case 'bg_position' :
                              case 'v_alignment' :
                              case 'font_size' :
                              case 'line_height' :
                              case 'font_picker' :
                                  sanitizedVal = _val;
                              break;
                              default :
                                  sanitizedVal = _val;
                              break;
                        }

                        itemNormalizedAndSanitized[ input_id ] = sanitizedVal;
                  });
                  return itemNormalizedAndSanitized;
            },











            // Is the UI currently displayed the one that is being requested ?
            // If so, don't generate the ui again
            // @return bool
            isUIControlAlreadyRegistered : function( uiElementId ) {
                  var self = this,
                      uiCandidate = _.filter( self.registered(), function( registered ) {
                            return registered.id == uiElementId && 'control' === registered.what;
                      }),
                      controlIsAlreadyRegistered = false;

                  // If the control is not been tracked in our self.registered(), let's check if it is registered in the api
                  // Typically, the module / section picker will match that case, because we don't keep track of it ( so it's not cleaned )
                  if ( _.isEmpty( uiCandidate ) ) {
                        controlIsAlreadyRegistered = api.control.has( uiElementId );
                  } else {
                        controlIsAlreadyRegistered = true;
                        // we should have only one uiCandidate with this very id
                        if ( uiCandidate.length > 1 ) {
                              api.errare( 'generateUI => why is this control registered more than once ? => ' + uiElementId );
                        }
                  }
                  return controlIsAlreadyRegistered;
            }
      });//$.extend()
})( wp.customize, jQuery );