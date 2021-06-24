
//BASE CONTROL CLASS
//extends api.CZRBaseControl
//define a set of methods, mostly helpers, to extend the base WP control class
//this will become our base constructor for main complex controls
//EARLY SETUP
var CZRBaseModuleControlMths = CZRBaseModuleControlMths || {};
( function ( api, $, _ ) {
$.extend( CZRBaseModuleControlMths, {
      initialize: function( id, options ) {
              var control = this;
              if ( ! api.has( id ) ) {
                    throw new Error( 'Missing a registered setting for control : ' + id );
              }


              control.czr_Module = new api.Values();

              //czr_collection stores the module collection
              control.czr_moduleCollection = new api.Value();
              control.czr_moduleCollection.set([]);

              //let's store the state of the initial module collection
              control.moduleCollectionReady = $.Deferred();
              //and listen to changes when it's ready
              control.moduleCollectionReady.done( function( obj ) {
                    //if the module is not registered yet for a single module control
                    //=> push it to the collection now, before listening to the module collection changes
                    // if (  ! control.isModuleRegistered( module.id ) ) {
                    //     control.updateModulesCollection( { module : constructorOptions } );
                    // }

                    //LISTEN TO MODULE COLLECTION
                    control.czr_moduleCollection.callbacks.add( function() { return control.moduleCollectionReact.apply( control, arguments ); } );

                    //control.removeModule( _mod );
              } );

              api.CZRBaseControl.prototype.initialize.call( control, id, options );

              //close any open item and dialog boxes on section expansion
              api.section( control.section(), function( _section_ ) {
                    _section_.expanded.bind(function(to) {
                          control.czr_Module.each( function( _mod ){
                                _mod.closeAllItems().closeRemoveDialogs();
                                if ( _.has( _mod, 'preItem' ) ) {
                                      _mod.preItemExpanded(false);
                                }
                          });
                    });
              });

      },




      //////////////////////////////////
      ///READY = CONTROL INSTANTIATED AND DOM ELEMENT EMBEDDED ON THE PAGE
      ///FIRED BEFORE API READY ? still true ?
      //
      // WP CORE => After the control is embedded on the page, invoke the "ready" method.
      // control.deferred.embedded.done( function () {
      //   control.linkElements(); // Link any additional elements after template is rendered by renderContent().
      //   control.setupNotifications();
      //   control.ready();
      // });
      //////////////////////////////////
      ready : function() {
              var control = this,
                  single_module = {},
                  savedModules;

              // Get the saved module and its initial items, get from the db of when dynamically registrating the setting control.
              try { savedModules = control.getSavedModules(); } catch( er ) {
                    api.errare( 'api.CZRBaseControl::ready() => error on control.getSavedModules()', er );
                    control.moduleCollectionReady.reject();
                    return;
              }

              // inits the collection with the saved module => there's only one module to instantiate in this case.
              // populates the collection with the saved module
              _.each( control.getSavedModules() , function( _mod, _key ) {
                    //stores it
                    single_module = _mod;

                    //adds it to the collection
                    //=> it will be fired ready usually when the control section is expanded
                    if ( serverControlParams.isDevMode ) {
                          control.instantiateModule( _mod, {} );
                    } else {
                          try { control.instantiateModule( _mod, {} ); } catch( er ) {
                                api.errare( 'api.CZRBaseControl::Failed to instantiate module ' + _mod.id , er );
                                return;
                          }
                    }

                    //adds the module name to the control container element
                    control.container.attr('data-module', _mod.id );
              });
              //the module collection is ready
              control.moduleCollectionReady.resolve( single_module );
      },









      //////////////////////////////////
      /// VARIOUS HELPERS
      //////////////////////////////////
      ///
      //@return the default API model {} needed to instantiate a module
      getDefaultModuleApiModel : function() {
            //if embedded in a control, amend the common model with the section id
            return {
                  id : '',//module.id,
                  module_type : '',//module.module_type,
                  modOpt : {},//the module modOpt property, typically high level properties that area applied to all items of the module
                  items   : [],//$.extend( true, {}, module.items ),
                  crud : false,
                  hasPreItem : true,//a crud module has a pre item by default
                  refresh_on_add_item : true,// the preview is refreshed on item add
                  multi_item : false,
                  sortable : false,//<= a module can be multi-item but not necessarily sortable
                  control : {},//control,
                  section : ''
            };
      },


      // @return the collection [] of saved module(s) to instantiate
      // This method does not make sure that the module model is ready for API.
      // => it just returns an array of saved module candidates to instantiate.
      //
      // Before instantiation, we will make sure that all required property are defined for the modules with the method control.prepareModuleForAPI()
      // control     : control,
      // crud        : bool
      // id          : '',
      // items       : [], module.items,
      // modOpt       : {}
      // module_type : module.module_type,
      // multi_item  : bool
      // section     : module.section,
      getSavedModules : function() {
              var control = this,
                  _savedModulesCandidates = [],
                  _module_type = control.params.module_type,
                  _raw_saved_module_val = [],
                  _saved_items = [],
                  _saved_modOpt = {};

              // What is the current server saved value for this setting?
              // in a normal case, it should be an array of saved properties
              // But it might not be if coming from a previous option system.
              // => let's normalize it.
              //
              // First let's perform a quick check on the current saved db val.
              // If the module is not multi-item, the saved value should be an object or empty if not set yet
              if ( ! api.CZR_Helpers.isMultiItemModule( _module_type ) && ! _.isEmpty( api( control.id )() ) && ! _.isObject( api( control.id )() ) ) {
                    api.errare('api.CZRBaseControl::getSavedModules => module Control Init for ' + control.id + '  : a mono item module control value should be an object if not empty.');
              }

              //SPLIT ITEMS [] and MODOPT {}
              //In database, items and modOpt are saved in the same option array.
              //If the module has modOpt ( the slider module for example ), the modOpt are described by an object which is always unshifted at the beginning of the setting value.

              //the raw DB setting value is an array :  modOpt {} + the saved items :
              ////META IS THE FIRST ARRAY ELEMENT: A modOpt has no unique id and has the property is_modOpt set to true
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
              //  [...]

              // POPULATE THE ITEMS [] and the MODOPT {} FROM THE RAW DB SAVED SETTING VAL
              // OR with the value used when registrating the module
              //
              // Important note :
              // The items should be turned into a collection of items [].
              var settingId = api.CZR_Helpers.getControlSettingId( control.id ),
                  settingVal = api( settingId )();

              // TO FIX
              if ( _.isEmpty( settingVal ) ) {
                    _raw_saved_module_val = [];
              } else {
                    _raw_saved_module_val = _.isArray( settingVal ) ? settingVal : [ settingVal ];
              }


              _.each( _raw_saved_module_val, function( item_or_mod_opt_candidate , key ) {
                    if ( ! _.isObject( item_or_mod_opt_candidate ) ) {
                          api.errare( 'api.CZRBaseControl::::getSavedModules => an item must be an object in control ' + control.id + ' => module type => ' + control.params.module_type, _raw_saved_module_val );
                          return;
                    }

                    // An item or modOpt can be empty on init
                    // But if not empty, it has to be an associative object, with keys that are string typed
                    // Fixes the case where an item { null } was accepted
                    // https://github.com/presscustomizr/themes-customizer-fmk/issues/46
                    if ( ! _.isEmpty( item_or_mod_opt_candidate ) ) {
                          _.each( item_or_mod_opt_candidate, function( prop, _key_ ) {
                                if ( ! _.isString( _key_ ) ) {
                                      api.errare( 'api.CZRBaseControl::::getSavedModules => item not well formed in control : ' + control.id + ' => module type => ' + control.params.module_type, _raw_saved_module_val );
                                      return;
                                }
                          });
                    }


                    // Module options, if enabled, are always saved as first key
                    if ( api.CZR_Helpers.hasModuleModOpt( _module_type ) && 0*0 === key ) {
                          // a saved module mod_opt object should not have an id
                          if ( _.has( item_or_mod_opt_candidate, 'id') ) {
                                api.errare( 'api.CZRBaseControl::getSavedModules : the module ' + _module_type + ' in control ' + control.id + ' has no mod_opt defined while it should.' );
                          } else {
                                _saved_modOpt = item_or_mod_opt_candidate;
                          }
                    }
                    // else {
                    //       _saved_items.push( item_or_mod_opt_candidate );
                    // }
                    //Until April 30th 2018, was :
                    //A modOpt has the property is_modOpt set to true
                    if ( ! _.has( item_or_mod_opt_candidate, 'is_mod_opt' ) ) {
                          _saved_items.push( item_or_mod_opt_candidate );
                    }
              });


              // This is a collection with one module
              // Note : @todo : the fact that the module are saved as a collection is not relevant anymore
              // This was introduced back in 2016 when building the first version of the section plugin.
              // With Nimble, a control can have one module only.
              _savedModulesCandidates.push({
                    id : api.CZR_Helpers.getOptionName( control.id ) + '_' + control.params.type,
                    module_type : control.params.module_type,
                    section : control.section(),
                    modOpt : $.extend( true, {} , _saved_modOpt ),//disconnect with a deep cloning
                    items : $.extend( true, [] , _saved_items )//disconnect with a deep cloning
              });

              return _savedModulesCandidates;
      },


      //this helper allows to check if a module has been registered in the collection
      //no matter if it's not instantiated yet
      isModuleRegistered : function( id_candidate ) {
            var control = this;
            return ! _.isUndefined( _.findWhere( control.czr_moduleCollection(), { id : id_candidate}) );
      }
});//$.extend//CZRBaseControlMths
})( wp.customize , jQuery, _ );