/* ------------------------------------------------------------------------- *
 *  IMAGE MAIN SETTINGS
/* ------------------------------------------------------------------------- */
//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;

                  // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

                  //SET THE CONTENT PICKER DEFAULT OPTIONS
                  //@see ::setupContentPicker()
                  module.bind( 'set_default_content_picker_options', function( params ) {
                        params.defaultContentPickerOption.defaultOption = {
                              'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                              'type'       : '',
                              'type_label' : '',
                              'object'     : '',
                              'id'         : '_custom_',
                              'url'        : ''
                        };
                        return params;
                  });
            },//initialize


            // _isChecked : function( v ) {
            //       return 0 !== v && '0' !== v && false !== v && 'off' !== v;
            // },
            //////////////////////////////////////////////////////////
            /// ITEM CONSTRUCTOR
            //////////////////////////////////////////
            CZRItemConstructor : {
                  //overrides the parent ready
                  ready : function() {
                        var item = this;
                        //wait for the input collection to be populated,
                        //and then set the input visibility dependencies
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()

                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );
                  },


                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;

                        //Internal item dependencies
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    // case 'img' :
                                    //       api.czr_sektions.scheduleVisibilityOfInputId.call( input, 'img-size', function() {
                                    //             return ! _.isEmpty( input()+'' ) && _.isNumber( input() );
                                    //       });
                                    // break;
                                    case 'link-to' :
                                          _.each( [ 'link-pick-url', 'link-custom-url', 'link-target' ] , function( _inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'link-custom-url' :
                                                                  bool = 'url' === input() && '_custom_' == item.czr_Input('link-pick-url')().id;
                                                            break;
                                                            case 'link-pick-url' :
                                                                  bool = 'url' === input();
                                                            break;
                                                            case 'link-target' :
                                                                  bool = ! _.contains( [ 'no-link', 'img-lightbox' ], input() );
                                                            break;
                                                      }
                                                      return bool;
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'link-pick-url' :
                                          api.czr_sektions.scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                return '_custom_' == input().id && 'url' == item.czr_Input('link-to')();
                                          });
                                    break;
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'use_custom_width' :
                                          _.each( [ 'custom_width' ] , function( _inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'use_custom_height' :
                                          _.each( [ 'custom_height' ] , function( _inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'use_custom_title_attr' :
                                          _.each( [ 'heading_title' ] , function( _inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });

                        // Sept 2020 for https://github.com/presscustomizr/nimble-builder-pro/issues/23
                        api.trigger('nb_setup_visibility_deps_for_img_module', {
                              item : item,
                              module : module
                        });
                  }
            },//CZRItemConstructor

      };//Constructor

      //provides a description of each module
      //=> will determine :
      //1) how to initialize the module model. If not crud, then the initial item(s) model shall be provided
      //2) which js template(s) to use : if crud, the module template shall include the add new and pre-item elements.
      //   , if crud, the item shall be removable
      //3) how to render : if multi item, the item content is rendered when user click on edit button.
      //    If not multi item, the single item content is rendered as soon as the item wrapper is rendered.
      //4) some DOM behaviour. For example, a multi item shall be sortable.
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_image_main_settings_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_image_main_settings_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_image_main_settings_child' )
            },
      });
})( wp.customize , jQuery, _ );








/* ------------------------------------------------------------------------- *
 *  IMAGE BORDERS AND BORDER RADIUS
/* ------------------------------------------------------------------------- */
//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;

                  // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize


            // _isChecked : function( v ) {
            //       return 0 !== v && '0' !== v && false !== v && 'off' !== v;
            // },
            //////////////////////////////////////////////////////////
            /// ITEM CONSTRUCTOR
            //////////////////////////////////////////
            CZRItemConstructor : {
                  //overrides the parent ready
                  ready : function() {
                        var item = this;
                        //wait for the input collection to be populated,
                        //and then set the input visibility dependencies
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()

                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );
                  },


                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        // input controller instance == this
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              //Fire on init
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              //React on change
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        //Internal item dependencies
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            },//CZRItemConstructor

      };//Constructor

      //provides a description of each module
      //=> will determine :
      //1) how to initialize the module model. If not crud, then the initial item(s) model shall be provided
      //2) which js template(s) to use : if crud, the module template shall include the add new and pre-item elements.
      //   , if crud, the item shall be removable
      //3) how to render : if multi item, the item content is rendered when user click on edit button.
      //    If not multi item, the single item content is rendered as soon as the item wrapper is rendered.
      //4) some DOM behaviour. For example, a multi item shall be sortable.
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_image_borders_corners_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_image_borders_corners_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_image_borders_corners_child' )
            },
      });
})( wp.customize , jQuery, _ );