//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  //console.log('INITIALIZING FP MODULE', id, options );
                  var module = this;


                  module.crudModulePart = 'nimble-crud-module-part';
                  module.rudItemPart = 'nimble-rud-item-part';

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

            // Overrides the default fmk method, to disable the default preview refresh
            _makeItemsSortable : function(obj) {
                  if ( wp.media.isTouchDevice || ! $.fn.sortable )
                    return;
                  var module = this;
                  $( '.' + module.control.css_attr.items_wrapper, module.container ).sortable( {
                        handle: '.' + module.control.css_attr.item_sort_handle,
                        start: function() {},
                        update: function( event, ui ) {
                              var _sortedCollectionReact = function() {
                                    if ( _.has(module, 'preItem') ) {
                                          module.preItemExpanded.set(false);
                                    }

                                    module.closeAllItems().closeRemoveDialogs();
                                    // var refreshPreview = function() {
                                    //       api.previewer.refresh();
                                    // };
                                    // //refreshes the preview frame  :
                                    // //1) only needed if transport is postMessage, because is triggered by wp otherwise
                                    // //2) only needed when : add, remove, sort item(s).
                                    // //var isItemUpdate = ( _.size(from) == _.size(to) ) && ! _.isEmpty( _.difference(from, to) );
                                    // if ( 'postMessage' == api(module.control.id).transport  && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                                    //       refreshPreview = _.debounce( refreshPreview, 500 );//500ms are enough
                                    //       refreshPreview();
                                    // }

                                    module.trigger( 'item-collection-sorted' );
                              };
                              module._getSortedDOMItemCollection()
                                    .done( function( _collection_ ) {
                                          module.itemCollection.set( _collection_ );
                                    })
                                    .then( function() {
                                          _sortedCollectionReact();
                                    });
                              //refreshes the preview frame, only if the associated setting is a postMessage transport one, with no partial refresh
                              // if ( 'postMessage' == api( module.control.id ).transport && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                              //         _.delay( function() { api.previewer.refresh(); }, 100 );
                              // }
                        }//update
                      }
                  );
            },//_makeItemsSortable


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
                        //       //item.module.updateItemModel( item );
                        // });
                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );
                  },


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
                  },

                  // Overrides the default fmk method in order to disable the remove dialog box
                  toggleRemoveAlert : function() {
                        this.removeItem();
                  },

                  // Overrides the default fmk method, to disable the default preview refresh
                  //fired on click dom event
                  //for dynamic multi input modules
                  //@return void()
                  //@param params : { dom_el : {}, dom_event : {}, event : {}, model {} }
                  removeItem : function( params ) {
                        params = params || {};
                        var item = this,
                            module = this.module,
                            _new_collection = _.clone( module.itemCollection() );

                        //hook here
                        module.trigger('pre_item_dom_remove', item() );

                        //destroy the Item DOM el
                        item._destroyView();

                        //new collection
                        //say it
                        _new_collection = _.without( _new_collection, _.findWhere( _new_collection, {id: item.id }) );
                        module.itemCollection.set( _new_collection );
                        //hook here
                        module.trigger('pre_item_api_remove', item() );

                        var _item_ = $.extend( true, {}, item() );

                        // <REMOVE THE ITEM FROM THE COLLECTION>
                        module.czr_Item.remove( item.id );
                        // </REMOVE THE ITEM FROM THE COLLECTION>

                        //refresh the preview frame (only needed if transport is postMessage && has no partial refresh set )
                        //must be a dom event not triggered
                        //otherwise we are in the init collection case where the items are fetched and added from the setting in initialize
                        if ( 'postMessage' == api(module.control.id).transport && _.has( params, 'dom_event') && ! _.has( params.dom_event, 'isTrigger' ) && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                              // api.previewer.refresh().done( function() {
                              //       _dfd_.resolve();
                              // });
                              // It would be better to wait for the refresh promise
                              // The following approach to bind and unbind when refreshing the preview is similar to the one coded in module::addItem()
                              var triggerEventWhenPreviewerReady = function() {
                                    api.previewer.unbind( 'ready', triggerEventWhenPreviewerReady );
                                    module.trigger( 'item-removed', _item_ );
                              };
                              api.previewer.bind( 'ready', triggerEventWhenPreviewerReady );
                              //api.previewer.refresh();
                        } else {
                              module.trigger( 'item-removed', _item_ );
                              module.control.trigger( 'item-removed', _item_ );
                        }

                  },
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
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_social_icons_settings_child' ),
                  items_are_clonable : true
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