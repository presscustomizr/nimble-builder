//@global serverControlParams
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
})( wp.customize , jQuery, _);