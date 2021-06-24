
//BASE CONTROL CLASS
//extends api.CZRBaseControl
//define a set of methods, mostly helpers, to extend the base WP control class
//this will become our base constructor for main complex controls
//EARLY SETUP
var CZRBaseModuleControlMths = CZRBaseModuleControlMths || {};
( function ( api, $, _ ) {
$.extend( CZRBaseModuleControlMths, {
      //@param : module {}
      //@param : constructor string
      instantiateModule : function( module, constructor ) {
              if ( ! _.has( module,'id') ) {
                    throw new Error('CZRModule::instantiateModule() : a module has no id and could not be added in the collection of : ' + this.id +'. Aborted.' );
              }
              var control = this;
              //is a constructor provided ?
              //if not try to look in the module object if we an find one
              if ( _.isUndefined(constructor) || _.isEmpty(constructor) ) {
                    constructor = control.getModuleConstructor( module );
              }
              //on init, the module collection is populated with module already having an id
              //For now, let's check if the id is empty and is not already part of the collection.
              //@todo : improve this.
              if ( ! _.isEmpty( module.id ) && control.czr_Module.has( module.id ) ) {
                    throw new Error('The module id already exists in the collection in control : ' + control.id );
              }

              var module_api_ready = control.prepareModuleForAPI( module );

              //instanciate the module with the default constructor
              control.czr_Module.add( module_api_ready.id, new constructor( module_api_ready.id, module_api_ready ) );

              if ( ! control.czr_Module.has( module_api_ready.id ) ) {
                    throw new Error('instantiateModule() : instantiation failed for module id ' + module_api_ready.id + ' in control ' + control.id  );
              }
              //return the module instance for chaining
              return control.czr_Module(module_api_ready.id);
      },



      //@return a module constructor object
      getModuleConstructor : function( module ) {
              var control = this,
                  parentConstructor = {},
                  constructor = {};

              if ( ! _.has( module, 'module_type' ) ) {
                    throw new Error('CZRModule::getModuleConstructor : no module type found for module ' + module.id );
              }
              if ( ! _.has( api.czrModuleMap, module.module_type ) ) {
                    throw new Error('Module type ' + module.module_type + ' is not listed in the module map api.czrModuleMap.' );
              }

              var _mthds = api.czrModuleMap[ module.module_type ].mthds || {},
                  _is_crud = api.czrModuleMap[ module.module_type ].crud,
                  _base_constructor = _is_crud ? api.CZRDynModule : api.CZRModule;

              // June 2020 : introduced for https://github.com/presscustomizr/nimble-builder-pro/issues/6
              // so we can remotely extend the module constructor
              api.trigger('czr_setup_module_contructor', {
                    module_type : module.module_type,
                    methods : _mthds
              });

              constructor = _base_constructor.extend( _mthds );

              if ( _.isUndefined( constructor ) || _.isEmpty(constructor) || ! constructor ) {
                    throw new Error('CZRModule::getModuleConstructor : no constructor found for module type : ' + module.module_type +'.' );
              }
              return constructor;
      },





      //@return an API ready module object
      //To be instantiated in the API, the module model must have all the required properties defined in the defaultAPIModel properly set
      prepareModuleForAPI : function( module_candidate ) {
            if ( ! _.isObject( module_candidate ) ) {
                throw new Error('prepareModuleForAPI : a module must be an object to be instantiated.');
            }

            var control = this,
                api_ready_module = {};

            // Default module model
            //{
            //       id : '',//module.id,
            //       module_type : '',//module.module_type,
            //       modOpt : {},//the module modOpt property, typically high level properties that area applied to all items of the module
            //       items   : [],//$.extend( true, {}, module.items ),
            //       crud : false,
            //       hasPreItem : true,//a crud module has a pre item by default
            //       refresh_on_add_item : true,// the preview is refreshed on item add
            //       multi_item : false,
            //       sortable : false,//<= a module can be multi-item but not necessarily sortable
            //       control : {},//control,
            //       section : ''
            // };
            _.each( control.getDefaultModuleApiModel() , function( _defaultValue, _key ) {
                  var _candidate_val = module_candidate[_key];
                  switch( _key ) {
                        //PROPERTIES COMMON TO ALL MODULES IN ALL CONTEXTS
                        case 'id' :
                              if ( _.isEmpty( _candidate_val ) ) {
                                    api_ready_module[_key] = control.generateModuleId( module_candidate.module_type );
                              } else {
                                    api_ready_module[_key] = _candidate_val;
                              }
                        break;
                        case 'module_type' :
                              if ( ! _.isString( _candidate_val ) || _.isEmpty( _candidate_val ) ) {
                                    throw new Error('prepareModuleForAPI : a module type must a string not empty');
                              }
                              api_ready_module[_key] = _candidate_val;
                        break;
                        case 'items' :
                              if ( ! _.isArray( _candidate_val )  ) {
                                    throw new Error('prepareModuleForAPI : a module item list must be an array');
                              }
                              api_ready_module[_key] = _candidate_val;
                        break;
                        case 'modOpt' :
                              if ( ! _.isObject( _candidate_val )  ) {
                                    throw new Error('prepareModuleForAPI : a module modOpt property must be an object');
                              }
                              api_ready_module[_key] = _candidate_val;
                        break;
                        case 'crud' :
                              //get the value from the czrModuleMap
                              if ( _.has( api.czrModuleMap, module_candidate.module_type ) ) {
                                    _candidate_val = api.czrModuleMap[ module_candidate.module_type ].crud;
                                    if ( _.isUndefined( _candidate_val ) ) {
                                          _candidate_val = _defaultValue;
                                    }
                              } else if ( ! _.isUndefined( _candidate_val) && ! _.isBoolean( _candidate_val )  ) {
                                    throw new Error('prepareModuleForAPI : the module param "crud" must be a boolean');
                              }
                              api_ready_module[_key] = _candidate_val || false;
                        break;
                        case 'hasPreItem' :
                              //get the value from the czrModuleMap
                              if ( _.has( api.czrModuleMap, module_candidate.module_type ) ) {
                                    _candidate_val = api.czrModuleMap[ module_candidate.module_type ].hasPreItem;
                                    if ( _.isUndefined( _candidate_val ) ) {
                                          _candidate_val = _defaultValue;
                                    }
                              } else if ( ! _.isUndefined( _candidate_val) && ! _.isBoolean( _candidate_val )  ) {
                                    throw new Error('prepareModuleForAPI : the module param "hasPreItem" must be a boolean');
                              }
                              api_ready_module[_key] = _candidate_val || false;
                        break;
                        case 'refresh_on_add_item' :
                              //get the value from the czrModuleMap
                              if ( _.has( api.czrModuleMap, module_candidate.module_type ) ) {
                                    _candidate_val = api.czrModuleMap[ module_candidate.module_type ].refresh_on_add_item;
                                    if ( _.isUndefined( _candidate_val ) ) {
                                          _candidate_val = _defaultValue;
                                    }
                              } else if ( ! _.isUndefined( _candidate_val) && ! _.isBoolean( _candidate_val )  ) {
                                    throw new Error('prepareModuleForAPI : the module param "refresh_on_add_item" must be a boolean');
                              }
                              api_ready_module[_key] = _candidate_val || false;
                        break;
                        case 'multi_item' :
                              // get the value from the czrModuleMap
                              // fallback on "crud" param if set
                              if ( _.has( api.czrModuleMap, module_candidate.module_type ) ) {
                                    _candidate_val = api.czrModuleMap[ module_candidate.module_type ].multi_item;
                                    if ( _.isUndefined( _candidate_val ) ) {
                                          _candidate_val = api.czrModuleMap[ module_candidate.module_type ].crud;
                                    }
                              } else if ( ! _.isUndefined( _candidate_val) && ! _.isBoolean( _candidate_val )  ) {
                                    throw new Error('prepareModuleForAPI : the module param "multi_item" must be a boolean');
                              }
                              api_ready_module[_key] = _candidate_val || false;
                        break;
                        //if the sortable property is not set, then check if crud or multi-item
                        case 'sortable' :
                              //get the value from the czrModuleMap
                              if ( _.has( api.czrModuleMap, module_candidate.module_type ) ) {
                                    // if the sortable param is not specified, set it based on the "crud" and "multi_item" params
                                    _candidate_val = api.czrModuleMap[ module_candidate.module_type ].sortable;
                                    if ( _.isUndefined( _candidate_val ) ) {
                                          _candidate_val = api.czrModuleMap[ module_candidate.module_type ].crud;
                                    }
                                    if ( _.isUndefined( _candidate_val ) ) {
                                          _candidate_val = api.czrModuleMap[ module_candidate.module_type ].multi_item;
                                    }
                              } else if ( ! _.isUndefined( _candidate_val) && ! _.isBoolean( _candidate_val )  ) {
                                    throw new Error('prepareModuleForAPI : the module param "sortable" must be a boolean');
                              }
                              api_ready_module[_key] = _candidate_val || false;
                        break;
                        case  'control' :
                              api_ready_module[_key] = control;//this
                        break;



                        //PROPERTIES FOR MODULE EMBEDDED IN A CONTROL
                        case  'section' :
                              if ( ! _.isString( _candidate_val ) || _.isEmpty( _candidate_val ) ) {
                                    throw new Error('prepareModuleForAPI : a module section must be a string not empty');
                              }
                              api_ready_module[_key] = _candidate_val;
                        break;



                        //PROPERTIES FOR MODULE EMBEDDED IN A SEKTION
                        case 'dirty' :
                              api_ready_module[_key] = _candidate_val || false;
                        break;
                  }//switch
            });
            return api_ready_module;
      },


      //recursive
      generateModuleId : function( module_type, key, i ) {
              //prevent a potential infinite loop
              i = i || 1;
              if ( i > 100 ) {
                    throw new Error('Infinite loop when generating of a module id.');
              }
              var control = this;
              key = key || control._getNextModuleKeyInCollection();
              var id_candidate = module_type + '_' + key;

              //do we have a module collection value ?
              if ( ! _.has(control, 'czr_moduleCollection') || ! _.isArray( control.czr_moduleCollection() ) ) {
                    throw new Error('The module collection does not exist or is not properly set in control : ' + control.id );
              }

              //make sure the module is not already instantiated
              if ( control.isModuleRegistered( id_candidate ) ) {
                key++; i++;
                return control.generateModuleId( module_type, key, i );
              }

              return id_candidate;
      },


      //helper : return an int
      //=> the next available id of the module collection
      _getNextModuleKeyInCollection : function() {
              var control = this,
                _max_mod_key = {},
                _next_key = 0;

              //get the initial key
              //=> if we already have a collection, extract all keys, select the max and increment it.
              //else, key is 0
              if ( ! _.isEmpty( control.czr_moduleCollection() ) ) {
                  _max_mod_key = _.max( control.czr_moduleCollection(), function( _mod ) {
                      return parseInt( _mod.id.replace(/[^\/\d]/g,''), 10 );
                  });
                  _next_key = parseInt( _max_mod_key.id.replace(/[^\/\d]/g,''), 10 ) + 1;
              }
              return _next_key;
      }
});//$.extend//CZRBaseControlMths
})( wp.customize , jQuery, _ );