//MULTI CONTROL CLASS
//extends api.Value
//
//Setup the collection of items
//renders the control view
//Listen to items collection changes and update the control setting
//MODULE OPTIONS :
  // control     : control,
  // crud        : bool
  // id          : '',
  // items       : [], module.items,
  // modOpt       : {}
  // module_type : module.module_type,
  // multi_item  : bool
  // section     : module.section,
var CZRModuleMths = CZRModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRModuleMths, {
      initialize: function( id, constructorOptions ) {
            if ( _.isUndefined(constructorOptions.control) || _.isEmpty(constructorOptions.control) ) {
                throw new Error('No control assigned to module ' + id );
            }
            var module = this;
            api.Value.prototype.initialize.call( this, null, constructorOptions );

            //store the state of ready.
            //=> we don't want the ready method to be fired several times
            module.isReady = $.Deferred();

            //write the module constructor options as properties
            // The default module model can be get with
            // and is formed this way :
            //@see getDefaultModuleApiModel : function() {
            //if embedded in a control, amend the common model with the section id
            //     return {
            //             id : '',//module.id,
            //             module_type : '',//module.module_type,
            //             modOpt : {},//the module modOpt property, typically high level properties that area applied to all items of the module
            //             items   : [],//$.extend( true, {}, module.items ),
            //             crud : false,
            //             hasPreItem : true,//a crud module has a pre item by default
            //             refresh_on_add_item : true,// the preview is refreshed on item add
            //             multi_item : false,
            //             sortable : false,//<= a module can be multi-item but not necessarily sortable
            //             control : {},//control,
            //             section : ''
            //       };
            // },

            $.extend( module, constructorOptions || {} );

            //extend the module with new template Selectors
            //can have been overriden at this stage from a module constructor
            $.extend( module, {
                  crudModulePart : module.crudModulePart || '', //'czr-crud-module-part',//create, read, update, delete
                  rudItemPart : module.rudItemPart || '',// 'czr-rud-item-part',//read, update, delete
                  ruItemPart : module.ruItemPart || '',// 'czr-ru-item-part',//read, update <= ONLY USED IN THE WIDGET MODULE
                  alertPart : module.alertPart || '',// 'czr-rud-item-alert-part',//used both for items and modules removal
                  itemInputList : module.itemInputList || '',//is specific for each crud module
                  modOptInputList : module.modOptInputList || ''//is specific for each module
            } );

            //embed : define a container, store the embed state, fire the render method
            module.embedded = $.Deferred();
            module.itemsWrapper = '';//will store the $ item container

            //if a module is embedded in a control, its container == the control container.
            module.container = $( module.control.selector );

            //render the item(s) wrapper
            //and resolve the module.embedded promise()
            module.renderModuleParts()
                  .done( function( $_module_items_wrapper ){
                        if ( false === $_module_items_wrapper.length ) {
                            throw new Error( 'The items wrapper has not been rendered for module : ' + module.id );
                        }
                        //stores the items wrapper ( </ul> el ) as a jQuery var
                        module.itemsWrapper = $_module_items_wrapper;
                        module.embedded.resolve();
                  })
                  .fail( function( _r_ ) {
                        throw new Error( [ "initialize module => failed module.renderModuleParts() for module : " , module.id , _r_ ].join(' '));
                  });

            /*-----------------------------------------------
            * MODULE OPTIONS
            ------------------------------------------------*/
            //declares a default Mod options API model
            module.defaultAPImodOptModel = {
                  initial_modOpt_model : {},
                  defaultModOptModel : {},
                  control : {},//control instance
                  module : {}//module instance
            };

            //declares a default modOpt model
            module.defaultModOptModel = {};

            //define a default Constructors
            module.modOptConstructor = module.modOptConstructor || api.CZRModOpt;

            /*-----------------------------------------------
            * ITEMS
            ------------------------------------------------*/
            module.itemCollection = new api.Value( [] );

            //declares a default Item API model
            module.defaultAPIitemModel = {
                  id : '',
                  initial_item_model : {},
                  defaultItemModel : {},
                  control : {},//control instance
                  module : {},//module instance
                  is_added_by_user : false
            };

            // declares a default item model
            module.defaultItemModel = api.czrModuleMap[ module.module_type ].defaultItemModel || { id : '', title : '' };

            // item constuctor : use the constructor already defined in a module, or fallback on the default one
            module.itemConstructor = module.itemConstructor || api.CZRItem;

            // czr_model stores the each model value => one value by created by model.id
            module.czr_Item = new api.Values();


            /*-----------------------------------------------
            * SET THE DEFAULT INPUT CONSTRUCTOR AND INPUT OPTIONS
            ------------------------------------------------*/
            // input constuctor : use the constructor already defined in a module, or fallback on the default one
            module.inputConstructor = module.inputConstructor || api.CZRInput;//constructor for the items input
            if ( module.hasModOpt() ) {
                  //use the constructor already defined in a module, or fallback on the default one
                  module.inputModOptConstructor = module.inputModOptConstructor || api.CZRInput;//constructor for the modOpt input
            }
            module.inputOptions = {};//<= can be set by each module specifically
            //For example, if I need specific options for the content_picker, this is where I will set them in the module extended object


            /*-----------------------------------------------
            * FIRE ON isReady
            ------------------------------------------------*/
            //module.ready(); => fired by children
            module.isReady.done( function() {
                  //store the module dirtyness, => no items set
                  module.isDirty = new api.Value( constructorOptions.dirty || false );

                  //initialize the module api.Value()
                  //constructorOptions has the same structure as the one described in prepareModuleforAPI
                  //setting the module Value won't be listen to at this stage
                  //api.infoLog('module.isReady.done() => constructorOptions',  constructorOptions);
                  module.initializeModuleModel( constructorOptions )
                        .done( function( initialModuleValue ) {
                              module.set( initialModuleValue );
                        })
                        .fail( function( response ){ api.errare( 'Module : ' + module.id + ' initialize module model failed : ', response ); })
                        .always( function( initialModuleValue ) {
                              //listen to each single module change
                              module.callbacks.add( function() { return module.moduleReact.apply( module, arguments ); } );

                              //if the module is not registered yet (for example when the module is added by user),
                              //=> push it to the collection of the module-collection control
                              //=> updates the wp api setting
                              if (  ! module.control.isModuleRegistered( module.id ) ) {
                                  module.control.updateModulesCollection( { module : constructorOptions, is_registered : false } );
                              }

                              module.bind('items-collection-populated', function( collection ) {
                                    //listen to item Collection changes
                                    module.itemCollection.callbacks.add( function() { return module.itemCollectionReact.apply( module, arguments ); } );

                                    // The sortable property is set on module registration
                                    // if not specified, the sortable will be set to true by default if the module is crud or multi-item
                                    if ( false !== module.sortable ) {
                                          module._makeItemsSortable();
                                    }

                                    // this event is listened to by Nimble Builder to expand the module once all the items collection is populated
                                    module.control.container.trigger('items-collection-populated');
                              });

                              //populate and instantiate the items now when a module is embedded in a regular control
                              module.populateSavedItemCollection();

                              //When the module has modOpt :
                              //=> Instantiate the modOpt and setup listener
                              if ( module.hasModOpt() ) {
                                    module.instantiateModOpt();
                              }
                        });
            });//module.isReady.done()


            /*-----------------------------------------------
            * Maybe resolve isReady() on parent section expanded
            ------------------------------------------------*/
            if ( true === api.czrModuleMap[ module.module_type ].ready_on_section_expanded ) {
                  //fired ready :
                  //1) on section expansion
                  //2) or in the case of a module embedded in a regular control, if the module section is already opened => typically when skope is enabled
                  if ( _.has( api, 'czr_activeSectionId' ) && module.control.section() == api.czr_activeSectionId() && 'resolved' != module.isReady.state() ) {
                        module.embedded.then( function() {
                              module.ready();
                        });
                  }

                  // defer the expanded callback when the section is instantiated
                  api.section( module.control.section(), function( _section_ ) {
                        _section_.expanded.bind(function(to) {
                              //set module ready on section expansion
                              if ( 'resolved' != module.isReady.state() ) {
                                    module.embedded.then( function() {
                                          module.ready();
                                    });
                              }
                        });
                  });
            }

            /*-----------------------------------------------
            * Maybe resolve isReady() on custom control event
            // To be specified when registering the control
            // used in Nimble to delay the instantiation of the input when the control accordion is expanded with event 'sek-accordion-expanded'
            ------------------------------------------------*/
            var _control_event = api.czrModuleMap[ module.module_type ].ready_on_control_event;
            if ( ! _.isUndefined( _control_event ) ) {
                  // defer the expanded callback when the section is instantiated
                  api.control( module.control.id, function( _control_ ) {
                        _control_.container.on( _control_event, function(evt) {
                              //set module ready on module accordion expansion
                              if ( 'resolved' != module.isReady.state() ) {
                                    module.embedded.then( function() {
                                          module.ready();
                                    });
                              }
                        });

                        // Nov 2020 => in WP 5.6, this setup was made too late
                        // That's why we need to introduce an event + a property informing Nimble that we're ready
                        // @see Nimble ::scheduleModuleAccordion
                        _control_.container.data('module_ready_on_custom_control_event_is_setup',true);
                        _control_.container.trigger('module_ready_on_custom_control_event_is_setup');
                  });
            }

            // Maybe instantiate and bind the api.Value() controlling the module option panel, for the module using it ( has_mod_opt : true on registration )
            this.maybeAwakeAndBindSharedModOpt();
      },//initialize()




      //////////////////////////////////
      ///READY
      //////////////////////////////////
      //When the control is embedded on the page, this method is fired in api.CZRBaseModuleControl:ready()
      //=> right after the module is instantiated.
      //If the module is a dynamic one (CRUD like), then this method is invoked by the child class
      ready : function() {
            var module = this;
            module.isReady.resolve();
      },



      //fired when module is initialized, on module.isReady.done()
      //designed to be extended or overridden to add specific items or properties
      initializeModuleModel : function( constructorOptions ) {
            var module = this, dfd = $.Deferred();
            if ( ! module.isMultiItem() && ! module.isCrud() ) {
                  //this is a static module. We only have one item
                  //init module item if needed.
                  if ( _.isEmpty( constructorOptions.items ) ) {
                        var def = _.clone( module.defaultItemModel );
                        constructorOptions.items = [ $.extend( def, { id : module.id } ) ];
                  }
            }
            return dfd.resolve( constructorOptions ).promise();
      },


      //cb of : module.itemCollection.callbacks
      //the data can typically hold informations passed by the input that has been changed and its specific preview transport (can be PostMessage )
      //data looks like :
      //{
      //  module : {}
      //  input_changed     : string input.id
      //  input_transport   : 'postMessage' or '',
      //  not_preview_sent  : bool
      //}
      itemCollectionReact : function( to, from, data ) {
            var module = this,
                _current_model = module(),
                _new_model = $.extend( true, {}, _current_model );
            _new_model.items = to;
            //update the dirtyness state
            module.isDirty.set(true);
            //set the the new items model
            module.set( _new_model, data || {} );
      },

      //This method is fired from the control
      filterItemsBeforeCoreApiSettingValue : function( itemsToReturn ) {
            return itemsToReturn;
      },

      //cb of module.callbacks
      //=> sets the setting value via the module collection !
      moduleReact : function( to, from, data ) {
            //cb of : module.callbacks
            var module            = this,
                control           = module.control,
                isItemUpdate    = ( _.size( from.items ) == _.size( to.items ) ) && ! _.isEmpty( _.difference( to.items, from.items ) ),
                isColumnUpdate  = to.column_id != from.column_id,
                refreshPreview    = function() {
                      api.previewer.refresh();
                };

            //update the collection + pass data
            control.updateModulesCollection( {
                  module : $.extend( true, {}, to ),
                  data : data//useful to pass contextual info when a change happens
            } );

            // //Always update the view title
            // module.writeViewTitle(to);

            // //@todo : do we need that ?
            // //send module to the preview. On update only, not on creation.
            // if ( ! _.isEmpty(from) || ! _.isUndefined(from) ) {
            //   module._sendModule(to, from);
            // }
      },

      //@todo : create a smart helper to get either the wp api section or the czr api sektion, depending on the module context
      getModuleSection : function() {
            return this.section;
      },

      //is this module multi item ?
      //@return bool
      isMultiItem : function() {
            return api.CZR_Helpers.isMultiItemModule( null, this );
      },

      //is this module crud ?
      //@return bool
      isCrud : function() {
            return api.CZR_Helpers.isCrudModule( null, this );
      },

      hasModOpt : function() {
            return api.CZR_Helpers.hasModuleModOpt( null, this );
      },


      //////////////////////////////////
      ///MODULE OPTION :
      ///1) PREPARE
      ///2) INSTANTIATE
      ///3) LISTEN TO AND SET PARENT MODULE ON CHANGE
      //////////////////////////////////
      //fired when module isReady
      instantiateModOpt : function() {
            var module = this;
            //Prepare the modOpt and instantiate it
            var modOpt_candidate = module.prepareModOptForAPI( module().modOpt || {} );
            module.czr_ModOpt = new module.modOptConstructor( modOpt_candidate );
            module.czr_ModOpt.ready();
            //update the module model on modOpt change
            module.czr_ModOpt.callbacks.add( function( to, from, data ) {
                  var _current_model = module(),
                      _new_model = $.extend( true, {}, _current_model );
                  _new_model.modOpt = to;
                  //update the dirtyness state
                  module.isDirty(true);
                  //set the the new items model
                  //the data can typically hold informations passed by the input that has been changed and its specific preview transport (can be PostMessage )
                  //data looks like :
                  //{
                  //  module : {}
                  //  input_changed     : string input.id
                  //  input_transport   : 'postMessage' or '',
                  //  not_preview_sent  : bool
                  //}
                  module( _new_model, data );
            });
      },

      //@return an API ready modOpt object with the following properties
      // initial_modOpt_model : {},
      // defaultModOptModel : {},
      // control : {},//control instance
      // module : {},//module instance
      //@param modOpt_candidate is an object. Can contain the saved modOpt properties on init.
      prepareModOptForAPI : function( modOpt_candidate ) {
            var module = this,
                api_ready_modOpt = {};
            // if ( ! _.isObject( modOpt_candidate ) ) {
            //       throw new Error('preparemodOptForAPI : a modOpt must be an object to be instantiated.');
            // }
            modOpt_candidate = _.isObject( modOpt_candidate ) ? modOpt_candidate : {};

            _.each( module.defaultAPImodOptModel, function( _value, _key ) {
                  var _candidate_val = modOpt_candidate[_key];
                  switch( _key ) {
                        case 'initial_modOpt_model' :
                            //make sure that the provided modOpt has all the default properties set
                            _.each( module.getDefaultModOptModel() , function( _value, _property ) {
                                  if ( ! _.has( modOpt_candidate, _property) )
                                     modOpt_candidate[_property] = _value;
                            });
                            api_ready_modOpt[_key] = modOpt_candidate;

                        break;
                        case  'defaultModOptModel' :
                            api_ready_modOpt[_key] = _.clone( module.defaultModOptModel );
                        break;
                        case  'control' :
                            api_ready_modOpt[_key] = module.control;
                        break;
                        case  'module' :
                            api_ready_modOpt[_key] = module;
                        break;
                  }//switch
            });
            return api_ready_modOpt;
      },

      //Returns the default modOpt defined in initialize
      //Each chid class can override the default item and the following method
      getDefaultModOptModel : function( id ) {
            var module = this;
            return $.extend( _.clone( module.defaultModOptModel ), { is_mod_opt : true } );
      },


      //The idea is to send only the currently modified item instead of the entire collection
      //the entire collection is sent anyway on api(setId).set( value ), and accessible in the preview via api(setId).bind( fn( to) )
      //This method can be called on input change and on czr-partial-refresh-done
      //{
      //  input_id :
      //  input_parent_id :
      //  is_mod_opt :
      //  to :
      //  from :
      //  isPartialRefresh : bool//<= let us know if it is a full wrapper refresh or a single input update ( true when fired from sendModuleInputsToPreview )
      //}
      sendInputToPreview : function( args ) {
            var module = this;
            //normalizes the args
            args = _.extend(
              {
                    input_id        : '',
                    input_parent_id : '',//<= can be the mod opt or an item
                    to              : null,
                    from            : null
              } , args );

            if ( _.isEqual( args.to, args.from ) )
              return;

            //This is listened to by the preview frame
            api.previewer.send( 'czr_input', {
                  set_id        : api.CZR_Helpers.getControlSettingId( module.control.id ),
                  module_id     : module.id,//<= will allow us to target the right dom element on front end
                  module        : { items : $.extend( true, {}, module().items ) , modOpt : module.hasModOpt() ?  $.extend( true, {}, module().modOpt ): {} },
                  input_parent_id : args.input_parent_id,//<= can be the mod opt or the item
                  input_id      : args.input_id,
                  value         : args.to,
                  isPartialRefresh : args.isPartialRefresh//<= let us know if it is a full wrapper refresh or a single input update ( true when fired from sendModuleInputsToPreview )
            });

            //add a hook here
            module.trigger( 'input_sent', { input : args.to , dom_el: module.container } );
      },


      //@return void()
      //Fired on partial refresh in base control initialize, only for module type controls
      //This method can be called when don't have input instances available
      //=> typically when reordering items, mod options and items are closed, therefore there's no input instances.
      //=> the input id are being retrieved from the input parent models : items and mod options.
      //@param args = { isPartialRefresh : bool }
      sendModuleInputsToPreview : function( args ) {
            var module = this,
                _sendInputData = function() {
                      var inputParent = this,//this is the input parent : item or modOpt
                          inputParentModel = $.extend( true, {}, inputParent() );
                      //we don't need to send the id, which is never an input, but generated by the api.
                      inputParentModel = _.omit( inputParentModel, 'id' );

                      _.each( inputParentModel, function( inputVal, inputId ) {
                            module.sendInputToPreview( {
                                  input_id : inputId,
                                  input_parent_id : inputParent.id,
                                  to : inputVal,
                                  from : null,
                                  isPartialRefresh : args.isPartialRefresh
                            });
                      });
                };

            module.czr_Item.each( function( _itm_ ) {
                  _sendInputData.call( _itm_ );
            });

            if ( module.hasModOpt() ) {
                  _sendInputData.call( module.czr_ModOpt );
            }
      },


      // Fired in ::initialize()
      // Maybe instantiate and bind the api.Value() controlling the visibility of the module option panel, for the module using it ( has_mod_opt : true on registration )
      maybeAwakeAndBindSharedModOpt : function() {
            if ( ! _.isUndefined( api.czr_ModOptVisible ) )
              return;

            //MOD OPT PANEL SETTINGS
            api.czr_ModOptVisible = new api.Value( false );

            //MOD OPT VISIBLE REACT
            // passing an optional args object allows us to expand the modopt panel and focus on a specific tab right after
            //@args : {
            //  module : module,//the current module for which the modOpt is being expanded
            //  focus : 'section-topline-2'//the id of the tab we want to focus on
            //}
            api.czr_ModOptVisible.bind( function( visible, from, args ) {
                  args = args || {};
                  if ( ! _.isFunction( args.module ) || ! _.isFunction( args.module.czr_ModOpt ) ) {
                        api.errare( 'moduleCtor::maybeAwakeAndBindSharedModOpt => api.czr_ModOptVisible.bind() => incorrect arguments', args );
                        return;
                  }
                  var modOpt = args.module.czr_ModOpt,
                      module = args.module;

                  // Append content on expansion
                  // Remove on collapse
                  if ( visible ) {
                        //first close all opened remove dialogs and opened items
                        module.closeRemoveDialogs().closeAllItems();

                        modOpt.modOptWrapperViewSetup( modOpt() ).done( function( $_container ) {
                              modOpt.container = $_container;
                              try {
                                    api.CZR_Helpers.setupInputCollectionFromDOM.call( modOpt ).toggleModPanelView( visible );
                              } catch(e) {
                                    api.consoleLog(e);
                              }
                              if ( module && args.focus ) {
                                    _.delay( function() {
                                          if ( _.isNull(  modOpt.container ) || ! modOpt.container.find('[data-tab-id="' + args.focus + '"] a').length )
                                            return;
                                          modOpt.container.find('[data-tab-id="' + args.focus + '"] a').trigger('click');
                                    }, 200 );
                              }
                        });

                  } else {
                        modOpt.toggleModPanelView( visible ).done( function() {
                              if ( modOpt.container && 0 < modOpt.container.length ) {
                                    $.when( modOpt.container.remove() ).done( function() {
                                          api.CZR_Helpers.removeInputCollection.call( modOpt );
                                    });
                              } else {
                                    api.CZR_Helpers.removeInputCollection.call( modOpt );
                              }
                              modOpt.container = null;
                        });
                  }
            } );
      }
});//$.extend//CZRBaseControlMths
})( wp.customize , jQuery, _ );