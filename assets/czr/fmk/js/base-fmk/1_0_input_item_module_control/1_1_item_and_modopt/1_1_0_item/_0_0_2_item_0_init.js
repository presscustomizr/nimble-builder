//extends api.Value
//options:
  // id : item.id,
  // initial_item_model : item,
  // defaultItemModel : module.defaultItemModel,
  // module : module,
  // is_added_by_user : is_added_by_user || false

var CZRItemMths = CZRItemMths || {};
( function ( api, $, _ ) {
$.extend( CZRItemMths , {
      initialize: function( id, options ) {
            if ( _.isUndefined(options.module) || _.isEmpty(options.module) ) {
              throw new Error('No module assigned to item ' + id + '. Aborting');
            }

            var item = this;
            api.Value.prototype.initialize.call( item, null, options );

            //DEFERRED STATES
            //store the state of ready.
            //=> we don't want the ready method to be fired several times
            item.isReady = $.Deferred();
            //will store the embedded and content rendered state
            item.embedded = $.Deferred();
            item.container = null;//will store the item $ dom element
            item.contentContainer = null;//will store the item content $ dom element

            // this collection will be populated based on the DOM rendered input candidates
            // will allows us to set and get any individual input : item.czr_Input('font-family')()
            // declaring the collection Values here allows us to schedule actions for not yet registered inputs
            // like for example :
            // => when the font-family input is registered, then listen to it
            // item.czr_Input.when( 'font-family', function( _input_ ) {
            //       _input_.bind( function( to, from ) {
            //             console.log('font-family input changed ', to ,from );
            //       });
            // });
            item.czr_Input = new api.Values();

            // the item.inputCollection stores all instantiated input from DOM at the end of api.CZR_Helpers.setupInputCollectionFromDOM.call( item );
            // the collection of each individual input object is stored in item.czr_Input()
            // this inputCollection is designed to be listened to, in order to fire action when the collection has been populated.
            item.inputCollection = new api.Value({});

            //VIEW STATES FOR ITEM AND REMOVE DIALOG
            //viewState stores the current expansion status of a given view => one value by created by item.id
            //viewState can take 3 values : expanded, expanded_noscroll (=> used on view creation), closed
            item.viewState = new api.Value( 'closed' );
            item.removeDialogVisible = new api.Value( false );

            //input.options = options;
            //write the options as properties, name is included
            $.extend( item, options || {} );

            //declares a default model
            item.defaultItemModel = _.clone( options.defaultItemModel ) || { id : '', title : '' };

            //set initial values
            var _initial_model = $.extend( item.defaultItemModel, options.initial_item_model );

            // Check initial model here : to be overriden in each module
            _initial_model = item.validateItemModelOnInitialize( _initial_model );

            //this won't be listened to at this stage
            item.set( _initial_model );

            //USER EVENT MAP
            item.userEventMap = new api.Value( [
                  //toggles remove view alert
                  {
                        trigger   : 'click keydown',
                        selector  : [ '.' + item.module.control.css_attr.display_alert_btn, '.' + item.module.control.css_attr.cancel_alert_btn ].join(','),
                        name      : 'toggle_remove_alert',
                        actions   : ['toggleRemoveAlert']
                  },
                  //removes item and destroys its view
                  {
                        trigger   : 'click keydown',
                        selector  : '.' + item.module.control.css_attr.remove_view_btn,
                        name      : 'remove_item',
                        actions   : ['removeItem']
                  },
                  //edit view
                  {
                        trigger   : 'click keydown',
                        selector  : [ '.' + item.module.control.css_attr.edit_view_btn, '.' + item.module.control.css_attr.item_title ].join(','),
                        name      : 'edit_view',
                        actions   : [ 'setViewVisibility' ]
                  },
                  //clone view
                  {
                        trigger   : 'click keydown',
                        selector  : '.czr-clone-item',
                        name      : 'clone_view',
                        actions   : function( args ) {
                              args = args || {};
                              var _cloned_item_model = $.extend( {}, true, item() );
                              _cloned_item_model.id = '';
                              this.module.addItem( args, _cloned_item_model ).done( function() {
                                    // Nimble Builder => make sure the dynamic stylesheet is refreshed
                                    if ( window.sektionsLocalizedData && api.czr_skopeBase ) {
                                          api.previewer.trigger( 'sek-refresh-stylesheet', {
                                                local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                                                location_skope_id : sektionsLocalizedData.globalSkopeId
                                          });
                                    }
                              });
                        }
                  },
                  //tabs navigation
                  {
                        trigger   : 'click keydown',
                        selector  : '.tabs nav li',
                        name      : 'tab_nav',
                        actions   : function( args ) {
                              //toggleTabVisibility is declared in the module ctor and its "this" is the item or the modOpt
                              var tabIdSwitchedTo = $( args.dom_event.currentTarget, args.dom_el ).data('tab-id');
                              this.module.toggleTabVisibility.call( this, tabIdSwitchedTo );
                              this.trigger( 'tab-switch', { id : tabIdSwitchedTo } );
                        }
                  }
            ]);




            //ITEM IS READY
            //1) push it to the module item collection
            //2) observe its changes
            item.isReady.done( function() {
                  //push it to the collection
                  item.module.updateItemsCollection( { item : item() } );
                  //listen to each single item change
                  item.callbacks.add( function() { return item.itemReact.apply(item, arguments ); } );

                  //SCHEDULE INPUTS SETUP
                  //=> when the item content has been rendered. Typically on item expansion for a multi-items module.
                  // => or for mono item, right on item.renderItemWrapper()
                  item.bind( 'contentRendered', function() {
                        //create the collection of inputs if needed
                        //first time or after a removal
                        // previous condition included :  ! _.has( item, 'czr_Input' )
                        if ( _.isEmpty( item.inputCollection() ) ) {
                              if ( serverControlParams.isDevMode ) {
                                    api.CZR_Helpers.setupInputCollectionFromDOM.call( item );
                                    //the item.container is now available
                                    //Setup the tabs navigation
                                    //setupTabNav is defined in the module ctor and its this is the item or the modOpt
                                    item.module.setupTabNav.call( item );
                              } else {
                                    try {
                                          api.CZR_Helpers.setupInputCollectionFromDOM.call( item );
                                          //the item.container is now available
                                          //Setup the tabs navigation
                                          //setupTabNav is defined in the module ctor and its this is the item or the modOpt
                                          item.module.setupTabNav.call( item );
                                    } catch( er ) {
                                          api.errorLog( 'In item.isReady.done : ' + er );
                                    }
                              }
                        }
                  });

                  //SCHEDULE INPUTS DESTROY
                  item.bind( 'contentRemoved', function() {
                        if ( _.has( item, 'czr_Input' ) )
                          api.CZR_Helpers.removeInputCollection.call( item );
                  });

                  //When shall we render the item ?
                  //If the module is part of a simple control, the item can be render now,
                  if ( item.canBeRendered() ) {
                        item.mayBeRenderItemWrapper();
                  }

                  //ITEM WRAPPER VIEW SETUP
                  //defer actions on item view embedded
                  item.embedded.done( function() {
                        //define the item view DOM event map
                        //bind actions when the item is embedded : item title, etc.
                        item.itemWrapperViewSetup( _initial_model );
                  });
            });//item.isReady.done()

            //if an item is manually added : open it
            // if ( item.is_added_by_user ) {
            //   item.setViewVisibility( {}, true );//empty obj because this method can be fired by the dom chain actions, always passing an object. true for added_by_user
            // }
            //item.setViewVisibility( {}, item.is_added_by_user );

      },//initialize

      //overridable method
      //Fired if the item has been instantiated
      //The item.callbacks are declared.
      ready : function() {
            // July 2021 introduced so we can remotely add visibility functions
            api.trigger('czr_module_item_is_ready', {
                  module_type : this.module.module_type,
                  item : this
            });
            this.isReady.resolve();
      },

      // overridable method introduced with the flat skope
      // problem to solve => an instantiated item, doesn't necessary have to be rendered in a given context.
      canBeRendered : function() {
            return true;
      },

      // @return validated model object
      // To be overriden in each module
      validateItemModelOnInitialize : function( item_model_candidate ) {
            return item_model_candidate;
      },

      // React to a single item change
      // cb of module.czr_Item( item.id ).callbacks
      // the params can typically hold informations passed by the input that has been changed and its specific preview transport (can be PostMessage )
      // params looks like :
      // {
      //  module : {}
      //  input_changed     : string input.id
      //  input_transport   : 'postMessage' or '',
      //  not_preview_sent  : bool
      // }
      itemReact : function( to, from, params ) {
            var item = this,
                module = item.module;

            params = params || {};

            //update the collection
            module.updateItemsCollection( { item : to, params : params } ).done( function() {
                  //Always update the view title when the item collection has been updated
                  item.writeItemViewTitle( to, params );
            });

            //send item to the preview. On update only, not on creation.
            // if ( ! _.isEmpty(from) || ! _.isUndefined(from) ) {
            //       api.consoleLog('DO WE REALLY NEED TO SEND THIS TO THE PREVIEW WITH _sendItem(to, from) ?');
            //       item._sendItem(to, from);
            // }
      }
});//$.extend
})( wp.customize , jQuery, _ );