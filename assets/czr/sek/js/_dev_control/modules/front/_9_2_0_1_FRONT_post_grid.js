//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;

                      //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                      module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                      // run the parent initialize
                      // Note : must be always invoked always after the input / item class extension
                      // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize

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
                                    case 'use_current_query' :
                                          _.each( [ 'replace_query', 'post_number', 'posts_per_page', 'include_sticky', 'categories', 'must_have_all_cats', 'order_by'] , function( _inputId_ ) {
                                              api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                var bool = false;
                                                      _replace_query = item.czr_Input('replace_query')(),
                                                      _display_pagination = item.czr_Input('display_pagination')();

                                                switch( _inputId_ ) {
                                                      case 'replace_query' :
                                                            bool = input();
                                                      break;
                                                      case 'post_number' :
                                                            bool = ( !input() && !_display_pagination ) || ( input() && _replace_query && !_display_pagination );
                                                      break;
                                                      case 'posts_per_page' :
                                                            bool = ( !input() && _display_pagination ) || ( input() && _replace_query && _display_pagination );
                                                      break;
                                                      case 'include_sticky' :
                                                      case 'categories' :
                                                      case 'must_have_all_cats' :
                                                      case 'order_by' :
                                                            bool = !input() || ( input() && item.czr_Input('replace_query')() );
                                                      break;
                                                }
                                                return bool;
                                              });
                                          });
                                    break;
                                    case 'replace_query' :
                                          _.each( [ 'post_number', 'posts_per_page', 'include_sticky', 'categories', 'must_have_all_cats', 'order_by'] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                  var   _use_current_query = item.czr_Input('use_current_query')(),
                                                        _display_pagination = item.czr_Input('display_pagination')(),
                                                        bool = false;

                                                  switch( _inputId_ ) {
                                                        case 'post_number' :
                                                              bool = (!_use_current_query && !_display_pagination ) || ( input() && !_display_pagination );
                                                        break;
                                                        case 'posts_per_page' :
                                                              bool = (!_use_current_query && _display_pagination ) || ( input() && _display_pagination );
                                                        break;
                                                        case 'include_sticky' :
                                                        case 'categories' :
                                                        case 'must_have_all_cats' :
                                                        case 'order_by' :
                                                              bool = !_use_current_query || input();
                                                        break;
                                                  }
                                                  return bool;
                                                });
                                            });
                                    break;
                                    case 'layout' :
                                            _.each( [ 'columns', 'img_column_width', 'has_tablet_breakpoint', 'has_mobile_breakpoint' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'columns' :
                                                                  bool = 'grid' === input();
                                                            break;
                                                            case 'has_tablet_breakpoint' :
                                                            case 'has_mobile_breakpoint' :
                                                            case 'img_column_width' :
                                                                  bool = 'list' === input();
                                                            break;
                                                      }
                                                      return bool;
                                                });
                                            });
                                      break;
                                      case 'categories' :
                                            _.each( [ 'must_have_all_cats' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var input_val = input();
                                                      return _.isArray( input_val ) && input_val.length>1;
                                                });
                                            });
                                      break;
                                      case 'display_pagination' :
                                            _.each( [ 'posts_per_page', 'post_number' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var _replace_query = item.czr_Input('replace_query')(),
                                                            _use_current_query = item.czr_Input('use_current_query')();
                                                            var bool = false;
                                                            switch( _inputId_ ) {
                                                                  case 'posts_per_page' :
                                                                        bool = input() && !_use_current_query || (input() && _use_current_query && _replace_query);
                                                                  break;
                                                                  case 'post_number' :
                                                                        bool = !input() && !_use_current_query || (!input() && _use_current_query && _replace_query);
                                                                  break;
                                                            }
                                                            return bool;
                                                });
                                            });
                                      break;
                                      case 'custom_grid_spaces' :
                                            _.each( [ 'column_gap', 'row_gap' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                });
                                            });
                                      break;
                                      case 'show_excerpt' :
                                            _.each( [ 'excerpt_length' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                });
                                            });
                                      break;
                                }
                          });
                    }
              }
      };
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
            czr_post_grid_main_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_post_grid_main_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_post_grid_main_child' )
            }
      });
})( wp.customize , jQuery, _ );




//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;

                      //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                      module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                      // run the parent initialize
                      // Note : must be always invoked always after the input / item class extension
                      // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize

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
                                      case 'show_thumb' :
                                            _.each( [ 'img_size', 'img_has_custom_height', 'img_height', 'border_radius_css', 'use_post_thumb_placeholder' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'img_height' :
                                                                  bool = input() && item.czr_Input('img_has_custom_height')();
                                                            break;
                                                            default :
                                                                  bool = input();
                                                            break;
                                                      }
                                                      return bool;
                                                });
                                            });
                                      break;
                                      case 'img_has_custom_height' :
                                            _.each( [ 'img_height' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input() && item.czr_Input('show_thumb')();
                                                });
                                            });
                                      break;
                                }
                          });
                    }
              }
      };
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
            czr_post_grid_thumb_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_post_grid_thumb_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_post_grid_thumb_child' )
            }
      });
})( wp.customize , jQuery, _ );




//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
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
            czr_post_grid_metas_child : {
                  //mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_post_grid_metas_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_post_grid_metas_child' )
            }
      });
})( wp.customize , jQuery, _ );




//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
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
            czr_post_grid_fonts_child : {
                  //mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_post_grid_fonts_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_post_grid_fonts_child' )
            }
      });
})( wp.customize , jQuery, _ );