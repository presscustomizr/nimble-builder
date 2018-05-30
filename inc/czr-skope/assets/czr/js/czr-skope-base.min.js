//@global serverControlParams
var CZRSkopeBaseMths = CZRSkopeBaseMths || {};
(function ( api, $, _ ) {
      $.extend( CZRSkopeBaseMths, {

            initialize: function() {
                  var self = this;
                  ///////////////////// DEFINITIONS /////////////////////

                  //Store the state of the first skope collection state
                  api.czr_initialSkopeCollectionPopulated = $.Deferred();

                  //the czr_skopeCollection stores all skopes instantiated by the user
                  //this collection is not updated directly
                  //=> it's updated on skope() instance change
                  api.czr_skopeCollection         = new api.Value([]);//all available skope, including the current skopes
                  //the current skopes collection get updated each time the 'czr-new-skopes-synced' event is triggered on the api by the preview
                  api.czr_currentSkopesCollection = new api.Value([]);

                  //the currently active skopes
                  api.czr_activeSkopes           = new api.Value( { local : '', group : ''} );



                  ///////////////////// SKOPE COLLECTIONS SYNCHRONISATION AND LISTENERS /////////////////////
                  //LISTEN TO SKOPE SYNC => UPDATE SKOPE COLLECTION ON START AND ON EACH REFRESH
                  //the sent data look like :
                  //{
                  //  czr_new_skopes : _wpCustomizeSettings.czr_new_skopes || [],
                  //  isChangesetDirty : boolean
                  // }
                  api.bind( 'ready' , function() {
                        api.previewer.bind( 'czr-new-skopes-synced', function( skope_server_data ) {
                              if ( serverControlParams.isDevMode ) {
                                    api.infoLog( 'API SKOPE SYNCED', skope_server_data );
                              }

                              // set the currently active stylesheet
                              if ( ! _.has( skope_server_data, 'czr_stylesheet') ) {
                                    api.errorLog( "On 'czr-new-skopes-synced' : missing stylesheet in the server data" );
                                    return;
                              }

                              api.czr_skopeBase.stylesheet   = api.czr_skopeBase.stylesheet || new api.Value( api.settings.theme.stylesheet );
                              api.czr_skopeBase.stylesheet( skope_server_data.czr_stylesheet );

                              //api.consoleLog('czr-skopes-ready DATA', skope_server_data );
                              var preview = this,
                                  previousSkopeCollection = api.czr_currentSkopesCollection();
                              //initialize skopes with the server sent skope_server_data
                              //if skope has not been initialized yet and the server sent wrong skope_server_data, then reject the skope ready promise()
                              if ( ! _.has( skope_server_data, 'czr_new_skopes') ) {
                                    api.errorLog( "On 'czr-new-skopes-synced' : missing skopes in the server data" );
                                    return;
                              }

                              // If no "group" skope has been sent, check if we should have one
                              // array( 'home', 'search', '404', 'date' ) <= have no group
                              if ( _.isEmpty( _.findWhere( skope_server_data.czr_new_skopes, { skope : 'group' } ) ) ){
                                    var _local_ = _.findWhere( skope_server_data.czr_new_skopes, { skope : 'local' } );
                                    if ( ! _.isEmpty( _local_ ) && ! _.contains( FlatSkopeLocalizedData.noGroupSkopeList, _local_.level ) ) {
                                          api.errorLog( 'No group level skope sent while there should be one' );
                                    }
                              }

                              //1) Updated the collection with normalized skopes  => prepareSkopeForAPI + api.czr_currentSkopesCollection( collection )
                              //2) When the api.czr_currentSkopesCollection() Value is set => instantiates the missing skope
                              //3) Set the skope layout view when the skope embedded promise is resolved
                              if ( serverControlParams.isDevMode ) {
                                          api.czr_skopeBase.updateSkopeCollection( skope_server_data.czr_new_skopes , preview.channel() );
                              } else {
                                    try {
                                          api.czr_skopeBase.updateSkopeCollection( skope_server_data.czr_new_skopes , preview.channel() );
                                    } catch ( er ) {
                                          return;
                                    }
                              }

                              //@return void()
                              // => refresh skope notice below the skope switcher title
                              // => refresh bottom skope infos in the preview
                              // var _refreshSkopeInfosNotices = function() {
                              //   console.log('REFRESH SKOPE TITLE IF NEEDED ?');
                              //       //WRITE THE CURRENT SKOPE TITLE
                              //       //self._writeCurrentSkopeTitle();
                              // };

                              //Always wait for the initial collection to be populated
                              api.czr_initialSkopeCollectionPopulated.then( function() {
                                    //console.log('INITIAL SKOPE COLLECTION POPULATED');
                                    if ( ! _.has( skope_server_data, 'czr_new_skopes' ) || _.isEmpty( skope_server_data.czr_new_skopes ) ) {
                                          api.errorLog( 'Missing skope data after refresh', skope_server_data );
                                    }

                                    // set the local and group skope id
                                    api.czr_activeSkopes( {
                                          'local' : self.getSkopeProperty( 'skope_id', 'local' ),
                                          'group' : self.getSkopeProperty( 'skope_id', 'group' )
                                    });

                                    // if ( ! _.isEmpty( previousSkopeCollection ) ) { //Rewrite the title when the local skope has changed
                                    //       var _prevLoc = _.findWhere( previousSkopeCollection , { skope : 'local' } ).opt_name,
                                    //           _newLoc  =_.findWhere( skope_server_data.czr_new_skopes, { skope : 'local' } ).opt_name;

                                    //       if ( _newLoc !== _prevLoc ) {
                                    //             //REFRESH SKOPE INFOS IN TITLE AND PREVIEW FRAME
                                    //             _refreshSkopeInfosNotices();
                                    //       }
                                    // }

                              });
                        });//api.previewer.bind
                  });//api.bind( 'ready'


                  //CURRENT SKOPE COLLECTION LISTENER
                  //The skope collection is set on 'czr-new-skopes-synced' triggered by the preview
                  //setup the callbacks of the skope collection update
                  //on init and on preview change : the collection of skopes is populated with new skopes
                  //=> instanciate the relevant skope object + render them
                  api.czr_currentSkopesCollection.bind( function( to, from ) {
                        return self.currentSkopesCollectionReact( to, from );
                  }, { deferred : true });

                  ///////////////////// VARIOUS /////////////////////
                  //DECLARE THE LIST OF CONTROL TYPES FOR WHICH THE VIEW IS REFRESHED ON CHANGE
                  //self.refreshedControls = [ 'czr_cropped_image'];// [ 'czr_cropped_image', 'czr_multi_module', 'czr_module' ];
                  api.trigger( 'czr_skopeBase_initialized' );
            }//initialize
      });//$.extend()
})( wp.customize , jQuery, _);//@global serverControlParams
var CZRSkopeBaseMths = CZRSkopeBaseMths || {};
( function ( api, $, _ ) {
$.extend( CZRSkopeBaseMths, {

    //////////////////////////////////////////////////////////
    /// <SKOPE HELPERSS>
    // @return string
    getSkopeProperty : function( what, skope_level ) {
        what = what || 'skope_id';
        skope_level = skope_level || 'local';
        var _skope_data = _.findWhere( api.czr_currentSkopesCollection(), { skope : skope_level });
        if ( _.isEmpty( _skope_data ) ){
            // display an error message if local skope not found. Should always be defined.
            if ( 'local' == skope_level ) {
                api.errorLog( "getSkopeProperty => local skope missing, returning not_set" );
            }
            return "_skope_not_set_";//for example when trying to get a group skope_id in a context where there can't be a group skope like home or search.
        } else if ( _.isEmpty( _skope_data[ what ] ) ) {
            api.errorLog( "getSkopeProperty => " + what + " property does not exist" );
            return "_skope_not_set_";
        } else {
            return _skope_data[ what ];
        }
    },


    //@return string
    firstToUpperCase : function( str ) {
          return ! _.isString( str ) ? '' : str.substr(0, 1).toUpperCase() + str.substr(1);
    }



    // //@return string
    // buildSkopeLink : function( skope_id ) {
    //     if ( ! api.czr_skope.has( skope_id ) ) {
    //         api.errorLog( 'buildSkopeLink : the requested skope id is not registered : ' + skope_id );
    //         return '';
    //     }
    //     var _link_title = [ serverControlParams.i18n.skope['Switch to scope'], api.czr_skope( skope_id )().title ].join(' : ');
    //     return [
    //         '<span class="czr-skope-switch" title=" ' + _link_title + '" data-skope-id="' + skope_id + '">',
    //         api.czr_skope( skope_id )().title,
    //         '</span>'
    //     ].join( '' );
    // },
});//$.extend
})( wp.customize , jQuery, _ );//@global serverControlParams
var CZRSkopeBaseMths = CZRSkopeBaseMths || {};
(function ( api, $, _ ) {
      $.extend( CZRSkopeBaseMths, {
            //Fired on 'czr-new-skopes-synced' triggered by the preview, each time the preview is refreshed.
            //On a Save Action, api.czr_savedDirties has been populated =>
            // 1) check if the server sends the same saved values
            // 2) update the skope db properties with the latests saved ones
            //
            //A skope candidate is structured this way :
            //{
            // id:""
            // long_title:"Site wide options"
            // obj_id:""
            // skope:"global"
            // title:"Site wide options"
            //}
            //@see api_overrides
            updateSkopeCollection : function( sent_collection ) {
                  var self = this,
                  _api_ready_collection = [];

                  //normalize each sent skopes
                  _.each( sent_collection, function( _skope ) {
                        var skope_candidate = $.extend( true, {}, _skope );//deep clone to avoid any shared references
                        _api_ready_collection.push( self.prepareSkopeForAPI( skope_candidate ) );
                  });

                  //set the new collection of current skopes
                  //=> this will instantiate the not instantiated skopes
                  api.czr_currentSkopesCollection( _api_ready_collection );

                  // set the group skope id
                  //console.log( "SKOPE COLLECTION UPDATED", api.czr_currentSkopesCollection() );
            },


            //@param skope_candidate
            ////A skope candidate is structured this way :
            //{
            // id:""
            // long_title:"Site wide options"
            // obj_id:""
            // skope:"global"
            // title:"Site wide options"
            //}
            prepareSkopeForAPI : function( skope_candidate ) {
                  if ( ! _.isObject( skope_candidate ) ) {
                    throw new Error('prepareSkopeForAPI : a skope must be an object to be API ready');
                  }
                  var api_ready_skope = skope_candidate;

                  _.each( FlatSkopeLocalizedData.defaultSkopeModel , function( _value, _key ) {
                        var _candidate_val = skope_candidate[_key];
                        switch( _key ) {
                              case 'title' :
                                    if ( ! _.isString( _candidate_val ) ) {
                                          throw new Error('prepareSkopeForAPI : a skope title property must a string');
                                    }
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                              case 'long_title' :
                                    if ( ! _.isString( _candidate_val ) ) {
                                          throw new Error('prepareSkopeForAPI : a skope title property must a string');
                                    }
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                              case 'ctx_title' :
                                    if ( ! _.isString( _candidate_val ) ) {
                                          throw new Error('prepareSkopeForAPI : a skope context title property must a string');
                                    }
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                              case 'skope' :
                                    if ( ! _.isString( _candidate_val ) || _.isEmpty( _candidate_val ) ) {
                                          throw new Error('prepareSkopeForAPI : a skope "skope" property must a string not empty');
                                    }
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                              case 'obj_id' :
                                    if ( ! _.isString( _candidate_val ) ) {
                                          throw new Error('prepareSkopeForAPI : invalid "obj_id" for skope ' + _candidate_val.skope );
                                    }
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                              case 'skope_id' :
                              if ( ! _.isString( _candidate_val ) ) {
                                    throw new Error('prepareSkopeForAPI : invalid "skope_key" for skope ' + _candidate_val.skope );
                              }
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                              case 'values' :
                                    // if ( ! _.isString( _candidate_val ) ) {
                                    //       throw new Error('prepareSkopeForAPI : invalid "values" for skope ' + _candidate_val.skope );
                                    // }
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                              default :
                                    api_ready_skope[_key] = _candidate_val;
                              break;
                        }//switch
                  });

                  //Finally, generate the id and the title
                  api_ready_skope.id = api_ready_skope.skope + '_' + api_ready_skope.skope_id;
                  if ( ! _.isString( api_ready_skope.id ) || _.isEmpty( api_ready_skope.id ) ) {
                        throw new Error('prepareSkopeForAPI : a skope id must a string not empty');
                  }
                  if ( ! _.isString( api_ready_skope.title ) || _.isEmpty( api_ready_skope.title ) ) {
                        api_ready_skope.title = api_ready_skope.id;
                        api_ready_skope.long_title = api_ready_skope.id;
                  }
                  return api_ready_skope;
            },


            //cb of api.czr_currentSkopesCollection.callbacks
            //fired in initialize
            currentSkopesCollectionReact : function( to, from ) {
                  var  dfd = $.Deferred();

                  //ON INITIAL COLLECTION POPULATE, RESOLVE THE DEFERRED STATE
                  //=> this way we can defer earlier actions.
                  //For example when autofocus is requested, the section might be expanded before the initial skope collection is sent from the preview.
                  if ( _.isEmpty( from ) && ! _.isEmpty( to ) ) {
                        api.czr_initialSkopeCollectionPopulated.resolve();
                  }
                  return dfd.resolve( 'changed' ).promise();
            }//listenToSkopeCollection()

      });//$.extend()
})( wp.customize , jQuery, _);//@global serverControlParams
var CZRSkopeBaseMths = CZRSkopeBaseMths || {};
(function ( api, $ ) {
      // Skope
      $.extend( CZRSkopeBaseMths, api.Events );
      var CZR_SkopeBase   = api.Class.extend( CZRSkopeBaseMths );

      // Schedule skope instantiation on api ready
      // api.bind( 'ready' , function() {
      //       api.czr_skopeBase   = new api.CZR_SkopeBase();
      // });
      api.czr_skopeBase   = new CZR_SkopeBase();
})( wp.customize, jQuery );