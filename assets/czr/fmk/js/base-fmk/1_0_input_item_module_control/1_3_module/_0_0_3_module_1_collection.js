//MULTI CONTROL CLASS
//extends api.CZRBaseControl
//
//Setup the collection of items
//renders the module view
//Listen to items collection changes and update the control setting

var CZRModuleMths = CZRModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRModuleMths, {
      //@fired in module ready on api('ready')
      //the module().items has been set in initialize
      //A collection of items can be supplied.
      populateSavedItemCollection : function( _itemCollection_ ) {
              var module = this,
                  _deepCopyOfItemCollection;

              if ( ! _.isArray( _itemCollection_ || module().items ) ) {
                    api.errorLog( 'populateSavedItemCollection : The saved items collection must be an array in module :' + module.id );
                    return;
              }
              _deepCopyOfItemCollection = $.extend( true, [], _itemCollection_ || module().items );

              //populates the collection with the saved items
              //the modOpt must be skipped
              //the saved items + modOpt is an array looking like :
              ////MODOPT IS THE FIRST ARRAY ELEMENT: A modOpt has no unique id and has the property is_mod_opt set to true
              //[
              //  is_mod_opt : true //<= inform us that this is not an item but a modOpt
              //],
              ////THEN COME THE ITEMS
              //[
              //  id : "czr_slide_module_0"
              //     slide-background : 21,
              //     ....
              //   ],
              //   [
              // id : "czr_slide_module_1"
              //     slide-background : 21,
              //     ....
              //   ]

              // CHECK THAT WE DON'T HAVE ANY MODOPT AT THIS STAGE
              //=> the items and the modOpt should already be split at this stage, because it's done before module instantiation... this check is totally paranoid.
              _.each( _deepCopyOfItemCollection , function( item_candidate , key ) {
                    if ( _.has( item_candidate, 'is_mod_opt' ) ) {
                          throw new Error( 'populateSavedItemCollection => there should be no mod opt to instantiate here.');
                    }
              });

              // allow modules to hook here
              module.trigger( 'filterItemCandidatesBeforeInstantiation', _deepCopyOfItemCollection );

              //INSTANTIATE THE ITEMS
              _.each( _deepCopyOfItemCollection, function( item_candidate , key ) {
                    //instantiates and fires ready
                    var _doInstantiate_ = function() {
                          var _item_instance_ = module.instantiateItem( item_candidate );
                          if ( _.isFunction( _item_instance_ ) ) {
                                _item_instance_.ready();
                          } else {
                                api.errare( 'populateSavedItemCollection => Could not instantiate item in module ' + module.id , item_candidate );
                          }
                    };
                    //adds it to the collection and fire item.ready()
                    if ( serverControlParams.isDevMode ) {
                          _doInstantiate_();
                    } else {
                          try { _doInstantiate_(); } catch( er ) {
                                api.errare( 'populateSavedItemCollection => ' + er );
                          }
                    }
              });

              //check if everything went well
              _.each( _deepCopyOfItemCollection, function( _item ) {
                    if ( ! _.isObject( _item ) ) {
                          return;
                    }
                    if ( _.isUndefined( _.findWhere( module.itemCollection(), _item.id ) ) ) {
                          throw new Error( 'populateSavedItemCollection => The saved items have not been properly populated in module : ' + module.id );
                    }
              });

              module.trigger( 'items-collection-populated' );
              //do we need to chain this method ?
              //return this;
      },


      instantiateItem : function( item_candidate, is_added_by_user ) {
              var module = this;

              // Cast to an object now.
              item_candidate = _.isObject( item_candidate ) ? item_candidate : {};

              // FIRST VALIDATION
              //allow modules to validate the item_candidate before addition
              item_candidate = module.validateItemBeforeAddition( item_candidate, is_added_by_user );

              // Abort here and display a simple console message if item is null or false, for example if validateItemBeforeAddition returned null or false
              if ( ! item_candidate || _.isNull( item_candidate ) ) {
                    api.errare( 'CZRModule::instantiateItem() => item_candidate did not pass validation in module ' + module.id );
                    return;
              }

              // NORMALIZE
              //Prepare the item, make sure its id is set and unique
              item_candidate = module.prepareItemForAPI( item_candidate );

              if ( ! _.isObject( item_candidate ) ) {
                    api.errare( 'CZRModule::instantiateItem() => an item should be described by an object in module type : ' + module.module_type, 'module id : '  + module.id );
                    return;
              }

              // Display a simple console message if item is null or false, for example if validateItemBeforeInstantiation returned null or false
              if ( ! item_candidate || _.isNull( item_candidate ) ) {
                    api.errare( 'CZRModule::instantiateItem() => item_candidate invalid in module ' + module.id );
                    return;
              }

              //ITEM ID CHECKS
              if ( ! _.has( item_candidate, 'id' ) ) {
                    throw new Error('CZRModule::instantiateItem() => an item has no id and could not be added in the collection of : ' + this.id );
              }
              if ( module.czr_Item.has( item_candidate.id ) ) {
                    throw new Error('CZRModule::instantiateItem() => the following item id ' + item_candidate.id + ' already exists in module.czr_Item() for module ' + this.id  );
              }
              //instantiate the item with the item constructor, default one or provided by the module
              module.czr_Item.add( item_candidate.id, new module.itemConstructor( item_candidate.id, item_candidate ) );

              if ( ! module.czr_Item.has( item_candidate.id ) ) {
                    throw new Error('CZRModule::instantiateItem() => instantiation failed for item id ' + item_candidate.id + ' for module ' + this.id  );
              }
              //the item is now ready and will listen to changes
              //return the instance
              return module.czr_Item( item_candidate.id );
      },


      // Designed to be overriden in modules
      validateItemBeforeAddition : function( item_candidate, is_added_by_user ) {
            return item_candidate;
      },

      //@return an API ready item object with the following properties
      // id : '',
      // initial_item_model : {},
      // defaultItemModel : {},
      // control : {},//control instance
      // module : {},//module instance
      // is_added_by_user : false
      prepareItemForAPI : function( item_candidate ) {
              var module = this,
                  api_ready_item = {};
              // if ( ! _.isObject( item_candidate ) ) {
              //       throw new Error('prepareitemForAPI : a item must be an object to be instantiated.');
              // }
              item_candidate = _.isObject( item_candidate ) ? item_candidate : {};

              _.each( module.defaultAPIitemModel, function( _value, _key ) {
                    var _candidate_val = item_candidate[_key];
                    switch( _key ) {
                          case 'id' :
                              // The id can be specified in a module ( ex: the pre defined item ids of the Font Customizer module )
                              // => that's why we need to check here if the item id is not already registered here
                              if ( _.isEmpty( _candidate_val ) ) {
                                    api_ready_item[_key] = module.generateItemId( module.module_type );
                              } else {
                                    if ( module.isItemRegistered( _candidate_val ) ) {
                                          module.generateItemId( _candidate_val );
                                    } else {
                                          api_ready_item[_key] = _candidate_val;
                                    }
                              }
                          break;
                          case 'initial_item_model' :
                              //make sure that the provided item has all the default properties set
                              _.each( module.getDefaultItemModel() , function( _value, _property ) {
                                    if ( ! _.has( item_candidate, _property) )
                                       item_candidate[_property] = _value;
                              });
                              api_ready_item[_key] = item_candidate;

                          break;
                          case  'defaultItemModel' :
                              api_ready_item[_key] = _.clone( module.defaultItemModel );
                          break;
                          case  'control' :
                              api_ready_item[_key] = module.control;
                          break;
                          case  'module' :
                              api_ready_item[_key] = module;
                          break;
                          case 'is_added_by_user' :
                              api_ready_item[_key] =  _.isBoolean( _candidate_val ) ? _candidate_val : false;
                          break;
                    }//switch
              });

              //if we don't have an id at this stage, let's generate it.
              if ( ! _.has( api_ready_item, 'id' ) ) {
                    api_ready_item.id = module.generateItemId( module.module_type );
              }

              //Now amend the initial_item_model with the generated id
              api_ready_item.initial_item_model.id = api_ready_item.id;

              return module.validateItemBeforeInstantiation( api_ready_item );
      },


      // Designed to be overriden in modules
      validateItemBeforeInstantiation : function( api_ready_item ) {
            return api_ready_item;
      },


      // recursive
      // will generate a unique id with the provided prefix
      generateItemId : function( prefix, key, i ) {
              //prevent a potential infinite loop
              i = i || 1;
              if ( i > 100 ) {
                    throw new Error( 'Infinite loop when generating of a module id.' );
              }
              var module = this;
              key = key || module._getNextItemKeyInCollection();
              var id_candidate = prefix + '_' + key;

              //do we have a module collection value ?
              if ( ! _.has( module, 'itemCollection' ) || ! _.isArray( module.itemCollection() ) ) {
                    throw new Error('The item collection does not exist or is not properly set in module : ' + module.id );
              }

              //make sure the module is not already instantiated
              if ( module.isItemRegistered( id_candidate ) ) {
                key++; i++;
                return module.generateItemId( prefix, key, i );
              }
              return id_candidate;
      },


      //helper : return an int
      //=> the next available id of the item collection
      _getNextItemKeyInCollection : function() {
              var module = this,
                _maxItem = {},
                _next_key = 0;

              //get the initial key
              //=> if we already have a collection, extract all keys, select the max and increment it.
              //else, key is 0
              if ( _.isEmpty( module.itemCollection() ) )
                return _next_key;
              if ( _.isArray( module.itemCollection() ) && 1 === _.size( module.itemCollection() ) ) {
                    _maxItem = module.itemCollection()[0];
              } else {
                    _maxItem = _.max( module.itemCollection(), function( _item ) {
                          if ( ! _.isNumber( _item.id.replace(/[^\/\d]/g,'') ) )
                            return 0;
                          return parseInt( _item.id.replace( /[^\/\d]/g, '' ), 10 );
                    });
              }

              //For a single item collection, with an index free id, it might happen that the item is not parsable. Make sure it is. Otherwise, use the default key 0
              if ( ! _.isUndefined( _maxItem ) && _.isNumber( _maxItem.id.replace(/[^\/\d]/g,'') ) ) {
                    _next_key = parseInt( _maxItem.id.replace(/[^\/\d]/g,''), 10 ) + 1;
              }
              return _next_key;
      },



      //this helper allows to check if an item has been registered in the collection
      //no matter if it's not instantiated yet
      isItemRegistered : function( id_candidate ) {
            var module = this;
            return ! _.isUndefined( _.findWhere( module.itemCollection(), { id : id_candidate}) );
      },


      //Fired in module.czr_Item.itemReact
      //@param args can be
      //{
      //  collection : [],
      //  params : params {}
      //},
      //
      //or {
      //  item : {}
      //  params : params {}
      //}
      //if a collection is provided in the passed args then simply refresh the collection
      //=> typically used when reordering the collection item with sortable or when a item is removed
      //
      //the args.params can typically hold informations passed by the input that has been changed and its specific preview transport (can be PostMessage )
      //params looks like :
      //{
      //  module : {}
      //  input_changed     : string input.id
      //  input_transport   : 'postMessage' or '',
      //  not_preview_sent  : bool
      //}
      //@return a deferred promise
      updateItemsCollection : function( args ) {
              var module = this,
                  _current_collection = module.itemCollection(),
                  _new_collection = _.clone(_current_collection),
                  dfd = $.Deferred();

              //if a collection is provided in the passed args then simply refresh the collection
              //=> typically used when reordering the collection item with sortable or when a item is removed
              if ( _.has( args, 'collection' ) ) {
                    //reset the collection
                    module.itemCollection.set( args.collection );
                    return;
              }

              if ( ! _.has( args, 'item' ) ) {
                  throw new Error('updateItemsCollection, no item provided ' + module.control.id + '. Aborting');
              }
              //normalizes with params
              args = _.extend( { params : {} }, args );

              var item_candidate = _.clone( args.item ),
                  hasMissingProperty = false;

              // Is the item well formed ? Does it have all the properties of the default model ?
              // Each module has to declare a defaultItemModel which augments the default one : { id : '', title : '' };
              // Let's loop on the defaultItemModel property and check that none is missing in the candidate
              _.each( module.defaultItemModel, function( itemData, key ) {
                    if ( ! _.has( item_candidate, key ) ) {
                          throw new Error( 'CZRModuleMths => updateItemsCollection : Missing property "' + key + '" for item candidate' );
                    }
              });

              if ( hasMissingProperty )
                return;

              //the item already exist in the collection
              if ( _.findWhere( _new_collection, { id : item_candidate.id } ) ) {
                    _.each( _current_collection , function( _item, _ind ) {
                          if ( _item.id != item_candidate.id )
                            return;

                          //set the new val to the changed property
                          _new_collection[_ind] = item_candidate;
                    });
              }
              //the item has to be added
              else {
                  _new_collection.push( item_candidate );
              }

              //updates the collection value
              //=> is listened to by module.itemCollectionReact
              module.itemCollection.set( _new_collection, args.params );
              return dfd.resolve( { collection : _new_collection, params : args.params } ).promise();
      },



      //fire on sortable() update callback
      //@returns a sorted collection as an array of item objects
      _getSortedDOMItemCollection : function( ) {
              var module = this,
                  _old_collection = _.clone( module.itemCollection() ),
                  _new_collection = [],
                  dfd = $.Deferred();

              //re-build the collection from the DOM
              $( '.' + module.control.css_attr.single_item, module.container ).each( function( _index ) {
                    var _item = _.findWhere( _old_collection, {id: $(this).attr('data-id') });
                    //do we have a match in the existing collection ?
                    if ( ! _item )
                      return;

                    _new_collection[_index] = _item;
              });

              if ( _old_collection.length != _new_collection.length ) {
                  throw new Error('There was a problem when re-building the item collection from the DOM in module : ' + module.id );
              }
              return dfd.resolve( _new_collection ).promise();
      },


      //This method should
      //1) remove the item views
      //2) remove the czr_items instances
      //3) remove the item collection
      //4) re-initialize items
      //5) re-setup the item collection
      //6) re-instantiate the items
      //7) re-render their views
      refreshItemCollection : function() {
            var module = this;
            //Remove item views and instances
            module.czr_Item.each( function( _itm ) {
                  if ( module.czr_Item( _itm.id ).container && 0 < module.czr_Item( _itm.id ).container.length ) {
                        $.when( module.czr_Item( _itm.id ).container.remove() ).done( function() {
                              //Remove item instances
                              module.czr_Item.remove( _itm.id );
                        });
                  }
            });

            // Reset the item collection
            // => the collection listeners will be setup after populate, on 'items-collection-populated'
            module.itemCollection = new api.Value( [] );
            module.populateSavedItemCollection();
      }
});//$.extend//CZRBaseControlMths
})( wp.customize , jQuery, _ );