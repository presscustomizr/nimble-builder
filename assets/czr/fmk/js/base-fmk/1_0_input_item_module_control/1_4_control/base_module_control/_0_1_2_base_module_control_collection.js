
//BASE CONTROL CLASS
//extends api.CZRBaseControl
//define a set of methods, mostly helpers, to extend the base WP control class
//this will become our base constructor for main complex controls
//EARLY SETUP
var CZRBaseModuleControlMths = CZRBaseModuleControlMths || {};
( function ( api, $, _ ) {
$.extend( CZRBaseModuleControlMths, {
      //@return void()
      //@param obj can be { collection : []}, or { module : {} }
      //Can be called :
      //- for all modules, in module.isReady.done() if the module is not registered in the collection yet.
      //- for all modules on moduleReact ( module.callbacks )
      //
      //=> sets the setting value through the module collection !
      updateModulesCollection : function( obj ) {
              var control = this,
                  _current_collection = control.czr_moduleCollection(),
                  _new_collection = $.extend( true, [], _current_collection);

              //if a collection is provided in the passed obj then simply refresh the collection
              //=> typically used when reordering the collection module with sortable or when a module is removed
              if ( _.has( obj, 'collection' ) ) {
                    //reset the collection
                    control.czr_moduleCollection.set( obj.collection, obj.data || {} );
                    return;
              }

              if ( ! _.has(obj, 'module') ) {
                    throw new Error('updateModulesCollection, no module provided ' + control.id + '. Aborting');
              }

              //normalizes the module for the API
              var module_api_ready = control.prepareModuleForAPI( _.clone( obj.module ) );

              //the module already exist in the collection
              if ( _.findWhere( _new_collection, { id : module_api_ready.id } ) ) {
                    _.each( _current_collection , function( _elt, _ind ) {
                          if ( _elt.id != module_api_ready.id )
                            return;

                          //set the new val to the changed property
                          _new_collection[_ind] = module_api_ready;
                    });
              }
              //the module has to be added
              else {
                    _new_collection.push( module_api_ready );
              }

              //WHAT ARE THE PARAMS WE WANT TO PASS TO THE NEXT ACTIONS
              var _params = {};
              //if a data property has been passed,
              //amend the data property with the changed module
              if ( _.has( obj, 'data') ) {
                    _params = $.extend( true, {}, obj.data );
                    $.extend( _params, { module : module_api_ready } );
              }
              //Inform the collection
              control.czr_moduleCollection.set( _new_collection, _params );
      },






      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      ////////////////////////////////////////////////////// WHERE THE STREETS HAVE NO NAMES //////////////////////////////////////////////////////
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // cb of control.czr_moduleCollection.callbacks
      // @params is an optional object. { silent : true }
      moduleCollectionReact : function( to, from, params ) {
            var control = this,
                is_module_added = _.size(to) > _.size(from),
                is_module_removed = _.size(from) > _.size(to),
                is_module_update = _.size(from) == _.size(to);
                is_collection_sorted = false;

            // MODULE REMOVED
            // Remove the module instance if needed
            if ( is_module_removed ) {
                  //find the module to remove
                  var _to_remove = _.filter( from, function( _mod ){
                      return _.isUndefined( _.findWhere( to, { id : _mod.id } ) );
                  });
                  _to_remove = _to_remove[0];
                  control.czr_Module.remove( _to_remove.id );
            }

            // is there a passed module param ?
            // if so prepare it for DB
            // if a module is provided, we also want to pass its id to the preview => can be used to target specific selectors in a partial refresh scenario
            if ( _.isObject( params  ) && _.has( params, 'module' ) ) {
                  params.module_id = params.module.id;
                  params.moduleRegistrationParams = params.module;
                  params.module = control.prepareModuleForDB( $.extend( true, {}, params.module  ) );
            }

            // Inform the the setting if the module is not being added to the collection for the first time,
            // We don't want to say it to the setting, because it might alter the setting dirtyness for nothing on init.
            if ( ! is_module_added ) {
                  // control.filterModuleCollectionBeforeAjax( to ) returns an array of items
                  // if the module has modOpt, the modOpt object is always added as the first element of the items array (unshifted)
                  if ( serverControlParams.isDevMode ) {
                        api( this.id ).set( control.filterModuleCollectionBeforeAjax( to ), params );
                  } else {
                        try { api( this.id ).set( control.filterModuleCollectionBeforeAjax( to ), params ); } catch( er ) {
                              api.errare( 'api.CZRBaseControl::moduleCollectionReact => error when firing control.filterModuleCollectionBeforeAjax( to )', er );
                        }
                  }
                  //.done( function( to, from, o ) {});
            }
      },
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      ////////////////////////////////////////////////////// WHERE THE STREETS HAVE NO NAMES //////////////////////////////////////////////////////
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////









      //an overridable method to act on the collection just before it is ajaxed
      //@return the collection array
      filterModuleCollectionBeforeAjax : function( collection ) {
              var control = this,
                  cloned_collection = $.extend( true, [], collection ),
                  _filtered_collection = [],
                  itemsToReturn;

              _.each( cloned_collection , function( _mod, _key ) {
                    var db_ready_mod = $.extend( true, {}, _mod );
                    _filtered_collection[_key] = control.prepareModuleForDB( db_ready_mod );
              });

              //=> in a control : we save
              //1) the collection of item(s)
              //2) the modOpt
              //at this point we should be in the case of a single module collection, typically use to populate a regular setting
              if ( _.size( cloned_collection ) > 1 ) {
                    throw new Error('There should not be several modules in the collection of control : ' + control.id );
              }
              if ( ! _.isArray( cloned_collection ) || _.isEmpty( cloned_collection ) || ! _.has( cloned_collection[0], 'items' ) ) {
                    throw new Error('The setting value could not be populated in control : ' + control.id );
              }
              var module_id = cloned_collection[0].id;

              if ( ! control.czr_Module.has( module_id ) ) {
                    throw new Error('The single module control (' + control.id + ') has no module registered with the id ' + module_id  );
              }
              var module_instance = control.czr_Module( module_id );
              if ( ! _.isArray( module_instance().items ) ) {
                    throw new Error('The module ' + module_id + ' should be an array in control : ' + control.id );
              }

              // items
              // For a mono-item module, we save the first and unique item object
              // For example :
              // {
              //  'heading_text' : "this is a heading"
              //  'font_size' : '10px'
              //  ...
              // }
              //
              // For a multi-item module, we save a collection of item objects, which may include a mod_opt item
              itemsToReturn = module_instance.isMultiItem() ? module_instance().items : ( module_instance().items[0] || [] );
              itemsToReturn = module_instance.filterItemsBeforeCoreApiSettingValue( itemsToReturn );

              //Add the modOpt if any
              return module_instance.hasModOpt() ? _.union( [ module_instance().modOpt ] , itemsToReturn ) : itemsToReturn;
      },


      // fired before adding a module to the collection of DB candidates
      // the module must have the control.getDefaultModuleDBModel structure :
      prepareModuleForDB : function ( module_db_candidate ) {
            var control = this;
            if ( ! _.isObject( module_db_candidate ) ) {
                  throw new Error('::prepareModuleForDB : a module must be an object.');
            }
            var db_ready_module = {};

            // The items property should be a collection, even for mono-item modules
            if ( ! _.isArray( module_db_candidate['items'] )  ) {
                  throw new Error('::prepareModuleForDB : a module item list must be an array');
            }

            // Let's loop on the item(s) to check if they are well formed
            _.each( module_db_candidate['items'], function( itm ) {
                  if ( ! _.isObject( itm ) ) {
                        throw new Error('::prepareModuleForDB : a module item must be an object');
                  }
            });

            db_ready_module['items'] = module_db_candidate['items'];
            return db_ready_module;
      }
});//$.extend//CZRBaseControlMths
})( wp.customize , jQuery, _ );