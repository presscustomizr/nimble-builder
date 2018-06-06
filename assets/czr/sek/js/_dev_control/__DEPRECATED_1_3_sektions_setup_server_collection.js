//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // register the collection setting nimble___{$location}[{$skope_id}] ( ex : nimble___[skp__post_page_20] )
            // schedule reactions to a collection change
            // @return void()
            setupServerCollection : function() {
                  var self = this,
                      // generate the sektions from the server collection
                      serverCollection = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local').loop_start.collection;

                  if ( _.isEmpty( serverCollection ) ) {
                        api.consoleLog( 'setupServerCollection => No initial server saved sektions to register for skope => ' + api.czr_skopeBase.getSkopeProperty( 'skope_id', 'local') );
                        return;
                  }

                  if ( ! _.has( serverCollection, 'collection') ) {
                        throw new Error( 'setupServerCollection => server collection invalid, missing collection property' );
                  }
                  // If we have a collection saved for this context, let's instantiate the various objects
                  console.log( "api.czr_skopeBase.getSkopeProperty( 'sektions', 'local')", api.czr_skopeBase.getSkopeProperty( 'sektions', 'local') );

                  // loop on the sektions > columns > modules and register them
                  _.each( serverCollection.collection, function( sektionData ) {
                        // INSTANTIATE SEKTIONS
                        if ( 'section' != sektionData.level ) {
                              throw new Error( "ERROR in reactToCollectionSettingIdChange => we should be in a sektion level" );
                        }
                        self.updateUI(
                              serverCollection,
                              null,
                              {
                                    action : 'addNewSektion',
                                    id : sektionData.id,
                                    options : sektionData.options,
                                    onInit : true
                              }
                        );

                        // INSTANTIATE COLUMNS
                        _.each( sektionData.collection, function( columnData ) {
                              if ( 'column' != columnData.level ) {
                                    throw new Error( "ERROR in reactToCollectionSettingIdChange => we should be in a column level" );
                              }
                              self.updateUI(
                                    serverCollection,
                                    null,
                                    {
                                          action : 'addNewColumn',
                                          id : columnData.id,
                                          in_sektion : sektionData.id,
                                          options : columnData.options,
                                          onInit : true
                                    }
                              );
                              // INSTANTIATE MODULES
                              _.each( columnData.collection, function( moduleData ) {
                                    if ( 'module' != moduleData.level ) {
                                          throw new Error( "ERROR in reactToCollectionSettingIdChange => we should be in a module level" );
                                    }
                                    self.updateUI(
                                          serverCollection,
                                          null,
                                          {
                                                action : 'addNewModule',
                                                id : moduleData.id,
                                                in_sektion : sektionData.id,
                                                in_column : columnData.id,
                                                value : moduleData.value,
                                                options : moduleData.options,
                                                module_type : moduleData.module_type,
                                                onInit : true
                                          }
                                    );
                              });//_.each( sektionData.collection
                        });//_.each( sektionData.collection
                  });//_.each( serverCollection,
            }
      });//$.extend()
})( wp.customize, jQuery );

