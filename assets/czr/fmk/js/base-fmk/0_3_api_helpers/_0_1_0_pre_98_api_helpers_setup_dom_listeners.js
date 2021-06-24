
(function (api, $, _) {
api.CZR_Helpers = api.CZR_Helpers || {};
//////////////////////////////////////////////////
/// ACTIONS AND DOM LISTENERS
//////////////////////////////////////////////////
//adds action to an existing event map
//@event map = [ {event1}, {event2}, ... ]
//@new_event = {  trigger   : event name , actions   : [ 'cb1', 'cb2', ... ] }
api.CZR_Helpers = $.extend( api.CZR_Helpers, {
      addActions : function( event_map, new_events, instance ) {
              var control = this;
              instance = instance || control;
              instance[event_map] = instance[event_map] || [];
              new_event_map = _.clone( instance[event_map] );
              instance[event_map] = _.union( new_event_map, ! _.isArray(new_events) ? [new_events] : new_events );
      },

      doActions : function( action, $dom_el, obj ) {
              $dom_el.trigger( action, obj );
      },


      //@args = {model : model, dom_el : $_view_el, refreshed : _refreshed }
      setupDOMListeners : function( event_map , args, instance ) {
              var control = this,
                  _defaultArgs = {
                        model : {},
                        dom_el : {}
                  };

              instance = instance || control;
              //event_map : are we good ?
              if ( ! _.isArray( event_map ) ) {
                    api.errare( 'setupDomListeners : event_map should be an array', args );
                    return;
              }

              //args : are we good ?
              if ( ! _.isObject( args ) ) {
                    api.errare( 'setupDomListeners : args should be an object', event_map );
                    return;
              }

              args = _.extend( _defaultArgs, args );

              // => we need a dom_el as an existing jQuery object
              if ( ! ( args.dom_el instanceof jQuery ) || 1 > args.dom_el.length ) {
                    api.errare( 'setupDomListeners : dom element should be an existing dom element', args );
                    return;
              }

              //loop on the event map and map the relevant callbacks by event name
              // @param _event :
              //{
              //       trigger : '',
              //       selector : '',
              //       name : '',
              //       actions : ''
              // },
              _.map( event_map , function( _event ) {
                    if ( ! _.isString( _event.selector ) || _.isEmpty( _event.selector ) ) {
                          api.errare( 'setupDOMListeners : selector must be a string not empty. Aborting setup of action(s) : ' + _event.actions.join(',') );
                          return;
                    }

                    //Are we good ?
                    if ( ! _.isString( _event.selector ) || _.isEmpty( _event.selector ) ) {
                          api.errare( 'setupDOMListeners : selector must be a string not empty. Aborting setup of action(s) : ' + _event.actions.join(',') );
                          return;
                    }

                    // if ( ! _event.name && ! _.isEmpty( _event.name ) ) {
                    //     api.errare('in setupDOMListeners : missing name', _event );
                    // }

                    // DON'T CREATE THE SAME LISTENERS MULTIPLE TIMES
                    //Make sure that we add this listener only once to a particular dom element
                    //A listener id is a combination of event name + selector
                    //if not set, the name is a concatenation of trigger + selector
                    var _name = ( _event.name && ! _.isEmpty( _event.name ) ) ? _event.name : [ _event.trigger, _event.selector ].join('');

                    var _currentListenerCollection = args.dom_el.data( 'czr-listener-collection' );
                    if ( ! _currentListenerCollection || ! _.isArray( _currentListenerCollection ) ) {
                          _currentListenerCollection = [ _name ];
                    } else {
                          _currentListenerCollection = _.isArray( _currentListenerCollection ) ? _currentListenerCollection : [];
                          if ( ! _.contains( _currentListenerCollection, _name ) ) {
                                _currentListenerCollection.push( _name );
                          } else {
                                api.errare('setupDOMListeners : aborting because listener already created for event : ' + _name );
                                return;
                          }

                    }
                    // add this listener to the collection
                    args.dom_el.data( 'czr-listener-collection' , _currentListenerCollection );

                    //LISTEN TO THE DOM => USES EVENT DELEGATION
                    args.dom_el.on( _event.trigger , _event.selector, function( e, event_params ) {
                          //stop propagation to ancestors modules, typically a sektion
                          e.stopPropagation();
                          //particular treatment
                          if ( api.utils.isKeydownButNotEnterEvent( e ) ) {
                            return;
                          }
                          e.preventDefault(); // Keep this AFTER the key filter above

                          //It is important to deconnect the original object from its source
                          //=> because we will extend it when used as params for the action chain execution
                          var actionsParams = $.extend( true, {}, args );

                          //always get the latest model from the collection
                          if ( _.has( actionsParams, 'model') && _.has( actionsParams.model, 'id') ) {
                                if ( _.has( instance, 'get' ) )
                                  actionsParams.model = instance();
                                else
                                  actionsParams.model = instance.getModel( actionsParams.model.id );
                          }

                          //always add the event obj to the passed args
                          //+ the dom event
                          $.extend( actionsParams, { event : _event, dom_event : e } );

                          //add the event param => useful for triggered event
                          $.extend( actionsParams, event_params );

                          //SETUP THE EMITTERS
                          //inform the container that something has happened
                          //pass the model and the current dom_el
                          //the model is always passed as parameter
                          if ( ! _.has( actionsParams, 'event' ) || ! _.has( actionsParams.event, 'actions' ) ) {
                                api.errare( 'executeEventActionChain : missing obj.event or obj.event.actions' );
                                return;
                          }
                          if ( serverControlParams.isDevMode ) {
                              control.executeEventActionChain( actionsParams, instance );
                          } else {
                              try { control.executeEventActionChain( actionsParams, instance ); } catch( er ) {
                                    api.errare( 'In setupDOMListeners : problem when trying to fire actions : ' + actionsParams.event.actions , er );
                              }
                        }
                    });//.on()
              });//_.map()
      },//setupDomListeners



      //GENERIC METHOD TO SETUP EVENT LISTENER
      //NOTE : the args.event must alway be defined
      //Example of args :
      //  {
      //       trigger   : 'click keydown',
      //       selector  : [ '.' + module.control.css_attr.open_pre_add_btn, '.' + module.control.css_attr.cancel_pre_add_btn ].join(','),
      //       name      : 'pre_add_item',
      //       actions   : [
      //             'closeAllItems',
      //             'closeRemoveDialogs',
      //             function(obj) {
      //                   var module = this;
      //                   module.preItemExpanded.set( ! module.preItemExpanded() );
      //             },
      //       ],
      // },
      executeEventActionChain : function( args, instance ) {
              var control = this;

              //if the actions param is not an array but is an anonymous function, fire it and stop there
              if ( 'function' === typeof( args.event.actions ) )
                return args.event.actions.call( instance, args );

              //execute the various actions required
              //first normalizes the provided actions into an array of callback methods
              //then loop on the array and fire each cb if exists
              if ( ! _.isArray( args.event.actions ) )
                args.event.actions = [ args.event.actions ];

              //if one of the callbacks returns false, then we break the loop
              //=> allows us to stop a chain of callbacks if a condition is not met
              var _break = false;
              _.map( args.event.actions, function( _cb ) {
                    if ( _break )
                      return;

                    var _cbCandidate = function() {};

                    // is the _cb an anonymous function ?
                    // if not, we expect the method to exist in the provided object instance
                    if ( 'function' === typeof( _cb ) ) {
                          _cbCandidate = _cb;
                    } else {
                          if ( 'function' != typeof( instance[ _cb ] ) ) {
                                throw new Error( 'executeEventActionChain : the action : ' + _cb + ' has not been found when firing event : ' + args.event.selector );
                          } else {
                                _cbCandidate = instance[ _cb ];
                          }
                    }

                    // Allow other actions to be bound before action and after
                    //
                    // => we don't want the event in the object here => we use the one in the event map if set
                    // => otherwise will loop infinitely because triggering always the same cb from args.event.actions[_cb]
                    // => the dom element shall be get from the passed args and fall back to the controler container.
                    var $_dom_el = ( _.has(args, 'dom_el') && -1 != args.dom_el.length ) ? args.dom_el : control.container;

                    if ( 'string' === typeof( _cb ) ) {
                          $_dom_el.trigger( 'before_' + _cb, _.omit( args, 'event' ) );
                    }

                    //executes the _cb and stores the result in a local var
                    var _cb_return = _cbCandidate.call( instance, args );
                    //shall we stop the action chain here ?
                    if ( false === _cb_return )
                      _break = true;

                    if ( 'string' === typeof( _cb ) ) {
                          //allow other actions to be bound after
                          $_dom_el.trigger( 'after_' + _cb, _.omit( args, 'event' ) );
                    }
              });//_.map
      }
});//$.extend
})( wp.customize , jQuery, _);