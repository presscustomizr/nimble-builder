//MULTI CONTROL CLASS
//extends api.CZRModule
//
//Setup the collection of items
//renders the module view
//Listen to items collection changes and update the control setting

var CZRDynModuleMths = CZRDynModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRDynModuleMths, {
      initialize: function( id, options ) {
            var module = this;
            api.CZRModule.prototype.initialize.call( module, id, options );

            //extend the module with new template Selectors
            $.extend( module, {
                itemPreAddEl : ''//is specific for each crud module
            } );

            module.preItemsWrapper = '';//will store the pre items wrapper

            //PRE MODEL VIEW STATE
            // => will control the rendering / destruction of the DOM view
            // => the instantiation / destruction of the input Value collection
            module.preItemExpanded = new api.Value( false );

            //EXTENDS THE DEFAULT MONO MODEL CONSTRUCTOR WITH NEW METHODS
            //=> like remove item
            //module.itemConstructor = api.CZRItem.extend( module.CZRItemDynamicMths || {} );

            //default success message when item added
            module.itemAddedMessage = serverControlParams.i18n.successMessage;

            ////////////////////////////////////////////////////
            /// MODULE DOM EVENT MAP
            ////////////////////////////////////////////////////<
            // addItem utility
            // @return void()
            // @param params : { dom_el : {}, dom_event : {}, event : {}, model {} }
            var _doAddItem = function( params ) {
                    module.addItem( params ).done( function( item_id ) {
                          module.czr_Item( item_id , function( _item_ ) {
                                _item_.embedded.then( function() {
                                      _item_.viewState( 'expanded' );
                                });
                          });
                  })
                  .fail( function( error ) {
                        api.errare( 'module.addItem failed on add_item', error );
                  });
            };

            module.userEventMap = new api.Value( [
                  //pre add new item : open the dialog box
                  {
                        trigger   : 'click keydown',
                        selector  : [ '.' + module.control.css_attr.open_pre_add_btn, '.' + module.control.css_attr.cancel_pre_add_btn ].join(','),
                        name      : 'pre_add_item',
                        actions   : [
                              'closeAllItems',
                              'closeRemoveDialogs',
                              // toggles the visibility of the Remove View Block
                              // => will render or destroy the pre item view
                              // @param : obj = { event : {}, item : {}, view : ${} }
                              function( params ) {
                                    var module = this,
                                        canWe = { addTheItem : true };
                                    // allow remote filtering of the condition for addition
                                    module.trigger( 'is-item-addition-possible', canWe );

                                    // if the module has a pre-item, let's expand it, otherwise, let's add the item right away
                                    if ( canWe.addTheItem && module.hasPreItem ) {
                                          module.preItemExpanded.set( ! module.preItemExpanded() );
                                    } else {
                                          _doAddItem( params );
                                    }
                              },
                        ],
                  },
                  //add new item
                  {
                        trigger   : 'click keydown',
                        selector  : '.' + module.control.css_attr.add_new_btn, //'.czr-add-new',
                        name      : 'add_item',
                        //@param params : { dom_el : {}, dom_event : {}, event : {}, model {} }
                        actions   : function( params ) {
                              module.closeRemoveDialogs( params ).closeAllItems( params );
                              _doAddItem( params );
                        }
                  }
            ]);//module.userEventMap
      },//initialize()



      //When the control is embedded on the page, this method is fired in api.CZRBaseModuleControl:ready()
      //=> right after the module is instantiated.
      ready : function() {
            var module = this;
            //Setup the module event listeners
            module.setupDOMListeners( module.userEventMap() , { dom_el : module.container } );

            // Pre Item Value => used to store the preItem model
            module.preItem = new api.Value( module.getDefaultItemModel() );

            // Action on pre Item expansion / collapsing
            module.preItemExpanded.callbacks.add( function( isExpanded ) {
                  if ( isExpanded ) {
                        module.renderPreItemView()
                              .done( function( $preWrapper ) {
                                    module.preItemsWrapper = $preWrapper;
                                    //Re-initialize the pre item model
                                    module.preItem( module.getDefaultItemModel() );

                                    module.trigger( 'before-pre-item-input-collection-setup' );
                                    // Setup the pre item input collection from dom
                                    module.setupPreItemInputCollection();

                              })
                              .fail( function( message ) {
                                    api.errorLog( 'Pre-Item : ' + message );
                              });
                  } else {
                        $.when( module.preItemsWrapper.remove() ).done( function() {
                              module.preItem.czr_Input = {};
                              module.preItemsWrapper = null;
                              module.trigger( 'pre-item-input-collection-destroyed' );
                        });
                  }

                  // Expand / Collapse
                  module._togglePreItemViewExpansion( isExpanded );
            });

            api.CZRModule.prototype.ready.call( module );//fires the parent
      },//ready()



      //PRE MODEL INPUTS
      //fired when preItem is embedded.done()
      setupPreItemInputCollection : function() {
            var module = this;

            //Pre item input collection
            module.preItem.czr_Input = new api.Values();

            //creates the inputs based on the rendered items
            $('.' + module.control.css_attr.pre_add_wrapper, module.container)
                  .find( '.' + module.control.css_attr.sub_set_wrapper)
                  .each( function( _index ) {
                        var _id = $(this).find('[data-czrtype]').attr('data-czrtype') || 'sub_set_' + _index;
                        //instantiate the input
                        module.preItem.czr_Input.add( _id, new module.inputConstructor( _id, {//api.CZRInput;
                              id : _id,
                              type : $(this).attr('data-input-type'),
                              container : $(this),
                              input_parent : module.preItem,
                              module : module,
                              is_preItemInput : true
                        } ) );

                        //fire ready once the input Value() instance is initialized
                        module.preItem.czr_Input( _id ).ready();
                  });//each

            module.trigger( 'pre-item-input-collection-ready' );
      },

      // Intended to be overriden in a module
      // introduced in July 2019 to make it simple for a multi-item module to set a default pre-item
      // typically, in the slider image, this is a way to have a default image when adding an item
      // @see https://github.com/presscustomizr/nimble-builder/issues/479
      getPreItem : function() {
            return this.preItem();
      },


      // overridable method introduced with the flat skope
      // problem to solve in skope => an item, can't always be instantiated in a given context.
      itemCanBeInstantiated : function() {
            return true;
      },

      //Fired on user Dom action.
      //the item is manually added.
      //@return a promise() with the item_id as param
      //@param params : { dom_el : {}, dom_event : {}, event : {}, model {} }
      //@param _cloned_item_model = { id : '', title : '', ... }
      addItem : function( params, _cloned_item_model ) {
            var dfd = $.Deferred();
            if ( ! this.itemCanBeInstantiated() ) {
                  return dfd.reject().promise();
            }
            var module = this,
                item_candidate = module.getPreItem(),
                collapsePreItem = function() {
                      module.preItemExpanded.set( false );
                      //module.toggleSuccessMessage('off');
                };
            // June 2021 => introduction of clone item
            if ( _cloned_item_model && _.has( _cloned_item_model, 'id' ) ) {
                  item_candidate = _cloned_item_model;
            }
            if ( _.isEmpty( item_candidate ) || ! _.isObject( item_candidate ) ) {
                  api.errorLog( 'addItem : an item_candidate should be an object and not empty. In : ' + module.id +'. Aborted.' );
                  return dfd.reject().promise();
            }
            //display a sucess message if item_candidate is successfully instantiated
            collapsePreItem = _.debounce( collapsePreItem, 200 );

            //instantiates and fires ready
            var _doInstantiate_ = function() {
                  var _item_instance_ = module.instantiateItem( item_candidate, true );//true == Added by user
                  if ( _.isFunction( _item_instance_ ) ) {
                        _item_instance_.ready();
                  } else {
                        api.errare( 'populateSavedItemCollection => Could not instantiate item in module ' + module.id , item_candidate );
                  }
                  return _item_instance_;
            };
            //adds it to the collection and fire item.ready()
            if ( serverControlParams.isDevMode ) {
                  _doInstantiate_();
            } else {
                  try { _doInstantiate_(); } catch( er ) {
                        api.errare( 'populateSavedItemCollection : ' + er );
                        return dfd.reject().promise();
                  }
            }

            if ( ! module.czr_Item.has( item_candidate.id ) ) {
                  return dfd.reject('populateSavedItemCollection : the item ' + item_candidate.id + ' has not been instantiated in module ' + module.id ).promise();
            }

            //this iife job is to close the pre item and to maybe refresh the preview
            //then once done the item view is expanded to start editing it
            //@return a promise()
            $.Deferred( function() {
                  var _dfd_ = this;
                  module.czr_Item( item_candidate.id ).isReady.then( function() {
                        //module.toggleSuccessMessage('on');
                        collapsePreItem();

                        module.trigger('item-added', item_candidate );

                        var resolveWhenPreviewerReady = function() {
                              api.previewer.unbind( 'ready', resolveWhenPreviewerReady );
                              _dfd_.resolve();
                        };
                        //module.doActions( 'item_added_by_user' , module.container, { item : item_candidate , dom_event : params.dom_event } );

                        //refresh the preview frame (only needed if transport is postMessage && has no partial refresh set )
                        //must be a dom event not triggered
                        //otherwise we are in the init collection case where the items are fetched and added from the setting in initialize
                        // The property "refresh_on_add_item" is declared when registrating the module to the api.czrModuleMap
                        if ( module.refresh_on_add_item ) {
                              if ( 'postMessage' == api(module.control.id).transport && _.has( params, 'dom_event') && ! _.has( params.dom_event, 'isTrigger' ) && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                                    // api.previewer.refresh().done( function() {
                                    //       _dfd_.resolve();
                                    // });
                                    // It would be better to wait for the refresh promise
                                    api.previewer.bind( 'ready', resolveWhenPreviewerReady );
                                    api.previewer.refresh();
                              } else {
                                    _dfd_.resolve();
                              }
                        } else {
                              _dfd_.resolve();
                        }
                  });
            }).always( function() {
                    dfd.resolve( item_candidate.id );
            });
            return dfd.promise();
      }
});//$.extend
})( wp.customize , jQuery, _ );