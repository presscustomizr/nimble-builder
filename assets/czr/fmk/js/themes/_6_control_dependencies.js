
( function (api, $, _) {
      var $_nav_section_container,
          i18n = serverControlParams.i18n || {};

      api.czr_CrtlDependenciesReady = $.Deferred();

      api.bind( 'ready' , function() {
            if ( _.has( api, 'czr_ctrlDependencies') )
              return;

            api.czr_ctrlDependencies = new api.CZR_ctrlDependencies();
            api.czr_CrtlDependenciesReady.resolve();
      } );


      api.CZR_ctrlDependencies = api.Class.extend( {
              dominiDeps : [],
              initialize: function() {
                    var self = this;

                    this.defaultDominusParams = {
                          dominus : '',
                          servi : [],
                          visibility : null,
                          actions : null,
                          onSectionExpand : true
                    };

                    //store the default control dependencies
                    this.dominiDeps = _.extend( this.dominiDeps, this._getControlDeps() );
                    if ( ! _.isArray( self.dominiDeps ) ) {
                        throw new Error('Visibilities : the dominos dependency array is not an array.');
                    }
                    api.czr_activeSectionId.bind( function( section_id ) {
                          if ( ! _.isEmpty( section_id ) && api.section.has( section_id ) ) {
                                try {
                                      self.setServiDependencies( section_id );
                                } catch( er ) {
                                      api.errorLog( 'In api.CZR_ctrlDependencies : ' + er );
                                }
                          }
                    });


                    //@param target_source is an object :
                    // {
                    //    target : section_id to awake
                    //    source : section_id from which the request for awaking has been done
                    // }
                    api.bind( 'awaken-section', function( target_source ) {
                          try {
                                self.setServiDependencies( target_source.target, target_source.source );
                          } catch( er ) {
                                api.errorLog( 'On awaken-section, ctrl deps : ' + er );
                          }
                    });

                    //FAVICON SPECIFICS
                    //@todo => move to the theme ?
                    //favicon note on load and on change(since wp 4.3)
                    this._handleFaviconNote();
              },


              //Process the visibility callbacks for the controls of a target targetSectionId
              //@param targetSectionId : string
              //@param sourceSectionId : string, the section from which the request has been done
              setServiDependencies : function( targetSectionId, sourceSectionId, refresh ) {
                    var self = this, params, dfd = $.Deferred();

                    refresh = refresh || false;

                    if ( _.isUndefined( targetSectionId ) || ! api.section.has( targetSectionId ) ) {
                          throw new Error( 'Control Dependencies : the targetSectionId is missing or not registered : ' + targetSectionId );
                    }

                    //Assign a visibility state deferred to the target section
                    api.section( targetSectionId ).czr_ctrlDependenciesReady = api.section( targetSectionId ).czr_ctrlDependenciesReady || $.Deferred();

                    //Bail here if this section has already been setup for ctrl dependencies
                    if ( ! refresh && 'resolved' == api.section( targetSectionId ).czr_ctrlDependenciesReady.state() )
                      return dfd.resolve().promise();

                    //FIND DOMINI IN THE TARGET SECTION
                    //=> setup their callbacks
                    _.each( self.dominiDeps , function( params ) {
                          if ( ! _.has( params, 'dominus' ) || ! _.isString( params.dominus ) || _.isEmpty( params.dominus ) ) {
                                throw new Error( 'Control Dependencies : a dominus control id must be a not empty string.');
                          }

                          var wpDominusId = api.CZR_Helpers.build_setId( params.dominus );
                          if ( ! api.control.has( wpDominusId ) )
                            return;

                          if ( api.control( wpDominusId ).section() != targetSectionId )
                            return;

                          //Attempt to normalize the params
                          params = self._prepareDominusParams( params );
                          if ( _.isEmpty(params) )
                            return;

                          self._processDominusCallbacks( params.dominus, params, refresh )
                                .fail( function() {
                                      api.consoleLog( 'self._processDominusCallbacks fail for section ' + targetSectionId );
                                      dfd.reject();
                                })
                                .done( function() {
                                      dfd.resolve();
                                });
                    });


                    //EXTERNAL DOMINI : AWAKE THE SECTIONS
                    //check if any control of the current section is the servus of a dominus located in another section
                    var _secCtrls = api.CZR_Helpers.getSectionControlIds( targetSectionId ),
                        _getServusDomini = function( shortServudId ) {
                              var _dominiIds = [];
                              _.each( self.dominiDeps , function( params ) {
                                    if ( ! _.has( params, 'servi' ) || ! _.isArray( params.servi ) || ! _.has( params, 'dominus' ) || _.isEmpty( params.dominus ) ) {
                                          api.errorLog( 'Control Dependencies : wrong params in _getServusDomini.');
                                          return;
                                    }

                                    if ( _.contains( params.servi , shortServudId ) && ! _.contains( _dominiIds , params.dominus ) ) {
                                          //Attempt to normalize the params
                                          params = self._prepareDominusParams( params );
                                          if ( _.isEmpty(params) )
                                            return;
                                          else
                                            _dominiIds.push( params.dominus );
                                    }
                              });
                              return ! _.isArray( _dominiIds ) ? [] : _dominiIds;
                        },
                        _servusDominiIds = [];

                    //Build the domini array
                    _.each( _secCtrls, function( servusCandidateId ) {
                          if ( _.isEmpty( _getServusDomini( servusCandidateId ) ) )
                            return;

                          _servusDominiIds = _.union( _servusDominiIds, _getServusDomini( servusCandidateId ) );
                    });

                    //let's loop on the domini ids and check if we need to "awake" an external section
                    _.each( _servusDominiIds, function( shortDominusId ){

                          var wpDominusId = api.CZR_Helpers.build_setId( shortDominusId );
                          //This dominus must be located in another section
                          if ( api.control( wpDominusId ).section() == targetSectionId )
                              return;
                          //The dominus section can't be the current source if set. => otherwise potential infinite loop scenario.
                          if ( sourceSectionId == api.control( wpDominusId ).section() )
                              return;
                          //inform the api that a section has to be awaken
                          //=> first silently update the section controls if skope on
                          //=> then fire the visibilities
                          api.trigger( 'awaken-section', {
                                target : api.control( wpDominusId ).section(),
                                source : targetSectionId
                          } );
                    } );

                    //This section has been setup for ctrl dependencies
                    dfd.always( function() {
                          api.section( targetSectionId ).czr_ctrlDependenciesReady.resolve();
                    });
                    return dfd.promise();
              },


              //This method fires a callback when a control is registered in the api.
              //If the control is registered, then it fires the callback when it is embedded
              //If the control is embedeed, it fires the callback
              //=> typical use case : a control can be both removed from the API and the DOM, and then added back on skope switch
              //
              //@param wpCtrlId : string name of the control as registered in the WP API
              //@param callback : fn callback to fire
              //@param args : [] or callback arguments
              _deferCallbackForControl : function( wpCrtlId, callback, args ) {
                    var dfd = $.Deferred();
                    if ( _.isEmpty(wpCrtlId) || ! _.isString(wpCrtlId) ) {
                        throw new Error( '_deferCallbackForControl : the control id is missing.' );
                    }
                    if ( ! _.isFunction( callback ) ) {
                        throw new Error( '_deferCallbackForControl : callback must be a funtion.' );
                    }
                    args = ( _.isUndefined(args) || ! _.isArray( args ) ) ? [] : args;

                    if ( api.control.has( wpCrtlId ) ) {
                          if ( 'resolved' == api.control(wpCrtlId ).deferred.embedded.state() ) {
                                $.when( callback.apply( null, args ) )
                                      .fail( function() { dfd.reject(); })
                                      .done( function() { dfd.resolve(); });
                          } else {
                                api.control( wpCrtlId ).deferred.embedded.then( function() {
                                      $.when( callback.apply( null, args ) )
                                            .fail( function() { dfd.reject(); })
                                            .done( function() { dfd.resolve(); });
                                });
                          }
                    } else {
                          api.control.when( wpCrtlId, function() {
                                api.control( wpCrtlId ).deferred.embedded.then( function() {
                                      $.when( callback.apply( null, args ) )
                                            .fail( function() { dfd.reject(); })
                                            .done( function() { dfd.resolve(); });
                                });
                          });
                    }
                    return dfd.promise();
              },


              /*
              * @return void
              * show or hide setting according to the dependency + callback pair
              * @params setId = the short setting id, whitout the theme option prefix OR the WP built-in setting
              * @params o = { controls [], callback fn, onSectionExpand bool }
              */
              _processDominusCallbacks : function( shortDominusId, dominusParams, refresh ) {
                    var self = this,
                        wpDominusId = api.CZR_Helpers.build_setId( shortDominusId ),
                        dominusSetInst = api( wpDominusId ),
                        dfd = $.Deferred(),
                        hasProcessed = false;

                    //loop on the dominus servi and apply + bind the visibility cb
                    _.each( dominusParams.servi , function( servusShortSetId ) {
                            if ( ! api.control.has( api.CZR_Helpers.build_setId( servusShortSetId ) ) ) {
                                return;
                            }
                            //set visibility when control is embedded
                            //or when control is added to the api
                            //=> solves the problem of visibility callbacks lost when control are re-rendered
                            var _fireDominusCallbacks = function( dominusSetVal, servusShortSetId, dominusParams, refresh ) {
                                      var _toFire = [],
                                          _args = arguments;
                                      _.each( dominusParams, function( _item, _key ) {
                                            switch( _key ) {
                                                case 'visibility' :
                                                    self._setVisibility.apply( null, _args );
                                                break;
                                                case 'actions' :
                                                    if ( _.isFunction( _item ) )
                                                        _item.apply( null, _args );
                                                break;
                                            }
                                      });
                                },
                                _deferCallbacks = function( dominusSetVal ) {
                                      dominusSetVal = dominusSetVal  || dominusSetInst();
                                      var wpServusSetId = api.CZR_Helpers.build_setId( servusShortSetId );
                                      self._deferCallbackForControl(
                                                  wpServusSetId,
                                                  _fireDominusCallbacks,
                                                  [ dominusSetVal, servusShortSetId, dominusParams ]
                                            )
                                            .always( function() { hasProcessed = true; })
                                            .fail( function() { dfd.reject(); })
                                            .done( function() { dfd.resolve(); });
                                };


                            //APPLY THE DEPENDENCIES
                            _deferCallbacks();

                            //BIND THE DOMINUS SETTING INSTANCE
                            //store the visibility bound state
                            if ( ! _.has( dominusSetInst, 'czr_visibilityServi' ) )
                                dominusSetInst.czr_visibilityServi = new api.Value( [] );

                            //Maybe bind to react on setting _dirty change
                            var _currentDependantBound = dominusSetInst.czr_visibilityServi();
                            //Make sure a dependant visibility action is bound only once for a setting id to another setting control id
                            if ( ! _.contains( _currentDependantBound, servusShortSetId ) ) {
                                  dominusSetInst.bind( function( dominusSetVal ) {
                                      _deferCallbacks( dominusSetVal );
                                  });
                                  dominusSetInst.czr_visibilityServi( _.union( _currentDependantBound, [ servusShortSetId ] ) );
                            }
                    } );//_.each
                    if ( ! hasProcessed )
                      return dfd.resolve().promise();
                    return dfd.promise();
              },



              //@return void()
              _setVisibility : function ( dominusSetVal, servusShortSetId, dominusParams, refresh ) {
                    var wpServusSetId = api.CZR_Helpers.build_setId( servusShortSetId ),
                        visibility = dominusParams.visibility( dominusSetVal, servusShortSetId, dominusParams.dominus );

                    refresh = refresh || false;
                    //Allows us to filter between visibility callbacks and other actions
                    //a non visibility callback shall return null
                    if ( ! _.isBoolean( visibility ) || ( 'unchanged' == visibility && ! refresh ) )
                      return;

                    //when skope is enabled, we might be doing a silent update
                    //=> this method should be bailed if so
                    var _doVisibilitiesWhenPossible = function() {
                            if ( api.state.has( 'silent-update-processing' ) && api.state( 'silent-update-processing' )() )
                              return;
                            api.control( wpServusSetId, function( _controlInst ) {
                                  var _args = {
                                        duration : 'fast',
                                        completeCallback : function() {},
                                        unchanged : false
                                  };

                                  if ( _.has( _controlInst, 'active' ) )
                                    visibility = visibility && _controlInst.active();

                                  if ( _.has( _controlInst, 'defaultActiveArguments' ) )
                                    _args = control.defaultActiveArguments;

                                  _controlInst.onChangeActive( visibility , _controlInst.defaultActiveArguments );
                            });
                            if ( api.state.has( 'silent-update-processing' ) ) {
                                  api.state( 'silent-update-processing' ).unbind( _doVisibilitiesWhenPossible );
                            }
                    };

                    if ( api.state.has( 'silent-update-processing' ) && api.state( 'silent-update-processing' )() ) {
                          api.state( 'silent-update-processing' ).bind( _doVisibilitiesWhenPossible );
                    } else {
                          _doVisibilitiesWhenPossible();
                    }

              },










              /*****************************************************************************
              * HELPERS
              *****************************************************************************/
              /*
              * Abstract
              * Will be provided by the theme
              * @return main control dependencies object
              */
              _getControlDeps : function() {
                return {};
              },


              //@return a visibility ready object of param describing the dependencies between a dominus and its servi.
              //this.defaultDominusParams = {
              //       dominus : '',
              //       servi : [],
              //       visibility : fn() {},
              //       actions : fn() {},
              //       onSectionExpand : true
              // };
              _prepareDominusParams : function( params_candidate ) {
                    var self = this,
                        _ready_params = {};

                    //Check mandatory conditions
                    if ( ! _.isObject( params_candidate ) ) {
                          api.errorLog( 'Visibilities : a dominus param definition must be an object.');
                          return _ready_params;
                    }
                    if ( ! _.has( params_candidate, 'visibility' ) && ! _.has( params_candidate, 'actions' ) ) {
                          api.errorLog( 'Visibilities : a dominus definition must include a visibility or an actions callback.');
                          return _ready_params;
                    }
                    if ( ! _.has( params_candidate, 'dominus' ) || ! _.isString( params_candidate.dominus ) || _.isEmpty( params_candidate.dominus ) ) {
                          api.errorLog( 'Visibilities : a dominus control id must be a not empty string.');
                          return _ready_params;
                    }
                    var wpDominusId = api.CZR_Helpers.build_setId( params_candidate.dominus );
                    if ( ! api.control.has( wpDominusId ) ) {
                          api.errorLog( 'Visibilities : a dominus control id is not registered : ' + wpDominusId );
                          return _ready_params;
                    }
                    if ( ! _.has( params_candidate, 'servi' ) || _.isUndefined( params_candidate.servi ) || ! _.isArray( params_candidate.servi ) || _.isEmpty( params_candidate.servi ) ) {
                          api.errorLog( 'Visibilities : servi must be set as an array not empty.');
                          return _ready_params;
                    }

                    _.each( self.defaultDominusParams , function( _value, _key ) {
                        var _candidate_val = params_candidate[ _key ];

                        switch( _key ) {
                              case 'visibility' :
                                  if ( ! _.isUndefined( _candidate_val ) && ! _.isEmpty( _candidate_val ) && ! _.isFunction( _candidate_val ) ) {
                                        throw new Error( 'Visibilities : a dominus visibility callback must be a function : ' + params_candidate.dominus );
                                  }
                              break;
                              case 'actions' :
                                  if ( ! _.isUndefined( _candidate_val ) && ! _.isEmpty( _candidate_val ) && ! _.isFunction( _candidate_val ) ) {
                                        throw new Error( 'Visibilities : a dominus actions callback must be a function : ' + params_candidate.dominus );
                                  }
                              break;
                              case 'onSectionExpand' :
                                  if ( ! _.isUndefined( _candidate_val ) && ! _.isEmpty( _candidate_val ) && ! _.isBoolean( _candidate_val ) ) {
                                        throw new Error( 'Visibilities : a dominus onSectionExpand param must be a boolean : ' + params_candidate.dominus );
                                  }
                              break;
                        }
                        _ready_params[_key] = _candidate_val;
                    });

                    return _ready_params;
              },



              /*****************************************************************************
              * FAVICON SPECIFICS
              *****************************************************************************/
              /**
              * Fired on api ready
              * May change the site_icon description on load
              * May add a callback to site_icon
              * @return void()
              */
              _handleFaviconNote : function() {
                    var self = this,
                        _fav_setId = api.CZR_Helpers.build_setId( serverControlParams.faviconOptionName );
                    //do nothing if (||)
                    //1) WP version < 4.3 where site icon has been introduced
                    //2) User had not defined a favicon
                    //3) User has already set WP site icon
                    if ( ! api.has('site_icon') || ! api.control('site_icon') || ( api.has( _fav_setId ) && 0 === + api( _fav_setId )() ) || + api('site_icon')() > 0 )
                      return;

                    var _oldDes     = api.control('site_icon').params.description;
                        _newDes     = ['<strong>' , i18n.faviconNote || '' , '</strong><br/><br/>' ].join('') + _oldDes;

                    //on api ready
                    self._printFaviconNote(_newDes );

                    //on site icon change
                    api('site_icon').callbacks.add( function(to) {
                      if ( +to > 0 ) {
                        //reset the description to default
                        api.control('site_icon').container.find('.description').text(_oldDes);
                        //reset the previous favicon setting
                        if ( api.has( _fav_setId ) )
                          api( _fav_setId ).set("");
                      }
                      else {
                        self._printFaviconNote(_newDes );
                      }
                    });
              },

              //Add a note to the WP control description if user has already defined a favicon
              _printFaviconNote : function( _newDes ) {
                    api.control('site_icon').container.find('.description').html(_newDes);
              }
        }
      );//api.Class.extend() //api.CZR_ctrlDependencies
})( wp.customize, jQuery, _);