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
                                          console.log('UPDATE SKOPE COLLECTION ERROR', er);
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
})( wp.customize , jQuery, _);