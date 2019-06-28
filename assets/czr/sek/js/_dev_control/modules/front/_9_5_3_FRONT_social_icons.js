//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  //console.log('INITIALIZING FP MODULE', id, options );
                  var module = this;

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                  // module.isReady.then( function() {
                  //       if ( _.isUndefined( module.preItem ) )
                  //         return;
                  //       //specific update for the item preModel on social-icon change
                  //       module.preItem.bind( function( to, from ) {
                  //             if ( ! _.has(to, 'icon') )
                  //               return;
                  //             if ( _.isEqual( to['icon'], from['icon'] ) )
                  //               return;
                  //             module.updateItemModel( module.preItem, true );
                  //       });
                  // });

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize


            // overrides the default fmk method which generates a too long id for each item, like : "czr_social_icons_settings_child_2"
            // this method generates a uniq GUID id for each item
            generateItemId : function() {
                    return api.czr_sektions.guid();
            },


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

                        // //update the item model on social-icon change
                        // item.bind('icon:changed', function(){
                        //       console.log('MERDE ?');
                        //       //item.module.updateItemModel( item );
                        // });
                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );
                  },

                  //
                  _buildTitle : function( title, icon, color ) {
                          var item = this,
                              module     = item.module;
                          title = title || ( 'string' === typeof(icon) ? api.CZR_Helpers.capitalize( icon.replace( 'fa-', '') ) : '' );
                          title = api.CZR_Helpers.truncate(title, 20);
                          color = color || module.defaultSocialColor;

                          return '<div><span class="' + icon + '" style="color:' + color + '"></span> ' + title + '</div>';
                  },

                  //overrides the default parent method by a custom one
                  //at this stage, the model passed in the obj is up to date
                  writeItemViewTitle : function( model ) {
                          var item = this,
                              module     = item.module,
                              _model = model || item(),
                              _title = ( _model['icon'] ? _model['icon'] : '' ).replace('fa-', '').replace('envelope', 'email').replace( 'far', '').replace( 'fab', '').replace( 'fas', '');

                          $( '.' + module.control.css_attr.item_title , item.container ).html(
                            item._buildTitle( _title, _model['icon'], _model['color_css'] )
                          );
                  },

                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;

                        //Internal item dependencies
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'use_custom_color_on_hover' :
                                          _.each( [ 'social_color_hover' ] , function( _inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                }); } catch( er ) {
                                                      api.errare( 'Featured pages module => error in setInputVisibilityDeps', er );
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
            czr_social_icons_settings_child : {
                  mthds : Constructor,
                  crud : true,//api.czr_sektions.getRegisteredModuleProperty( 'czr_social_icons_settings_child', 'is_crud' ),
                  hasPreItem : false,//a crud module has a pre item by default
                  refresh_on_add_item : false,// the preview is refreshed on item add
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_social_icons_settings_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_social_icons_settings_child' )
            },
      });
})( wp.customize , jQuery, _ );

/* ------------------------------------------------------------------------- *
 *  SOCIAL ICONS OPTIONS
/* ------------------------------------------------------------------------- */
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
            czr_social_icons_style_child : {
                  //mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_social_icons_style_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_social_icons_style_child' )
            }
      });
})( wp.customize , jQuery, _ );