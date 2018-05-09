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
                                                _setting_.bind( _.debounce( function( to, from ) {
                                                      // We don't want to store the default title and id module properties
                                                      var moduleValueCandidate = {};
                                                      _.each( to, function( _val, _property ) {
                                                            if ( ! _.contains( ['title', 'id' ], _property ) ) {
                                                                  moduleValueCandidate[ _property ] = _val;
                                                            }
                                                      });
                                                      self.updateAPISetting({
                                                            action : 'sek-set-module-value',
                                                            moduleId : params.id,
                                                            value : moduleValueCandidate,
                                                            in_column : params.in_column,
                                                            in_sektion : params.in_sektion
                                                      }).done( function() {
                                                            api.previewer.send(
                                                                  'sek-set-module-value',
                                                                  {
                                                                        skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                                        moduleId : params.id,
                                                                        value : moduleValueCandidate
                                                                  }
                                                            );
                                                      });
                                                }, 100 ) );
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
                                                _setting_.bind( _.debounce( function( to, from ) {
                                                      // We don't want to store the default title and id module properties
                                                      var moduleValueCandidate = {};
                                                      _.each( to, function( _val, _property ) {
                                                            if ( ! _.contains( ['title', 'id' ], _property ) ) {
                                                                  moduleValueCandidate[ _property ] = _val;
                                                            }
                                                      });
                                                      api.previewer.trigger( 'sek-set-level-options', {
                                                            options_type : 'layout_background_border',
                                                            id : params.id,
                                                            value : moduleValueCandidate,
                                                            in_sektion : params.in_sektion,
                                                            in_column : params.in_column
                                                      });
                                                }, 100 ) );
                                          });
                                          self.register( {
                                                level : params.level,
                                                what : 'setting',
                                                id : layoutBgBorderOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.lbb || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_' //sekData.settingType
                                          });
                                    }

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
                                                _setting_.bind( _.debounce( function( to, from ) {
                                                      // We don't want to store the default title and id module properties
                                                      var moduleValueCandidate = {};
                                                      _.each( to, function( _val, _property ) {
                                                            if ( ! _.contains( ['title', 'id' ], _property ) ) {
                                                                  moduleValueCandidate[ _property ] = _val;
                                                            }
                                                      });
                                                      api.previewer.trigger( 'sek-set-level-options', {
                                                            options_type : 'spacing',
                                                            id : params.id,
                                                            value : moduleValueCandidate,
                                                            in_sektion : params.in_sektion,
                                                            in_column : params.in_column
                                                      });
                                                }, 100 ) );
                                          });
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
            },







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