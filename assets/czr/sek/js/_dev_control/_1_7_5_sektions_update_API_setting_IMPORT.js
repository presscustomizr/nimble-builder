//global sektionsLocalizedData, serverControlParams
//self.updAPISetParams = {
//       params : params,
//       promise : $.Deferred(),
//       newSetValue : _.isObject( _currentSetValue ) ? $.extend( true, {}, _currentSetValue ) : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ),
//       cloneId : '',
//       sectionInjectPromise
// };
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            //-------------------------------------------------------------------------------------------------
            //-- FILE IMPORT
            //-------------------------------------------------------------------------------------------------
            _updAPISet_sek_import_from_file :  function() {
                  var self = this,
                      params;

                  params = self.updAPISetParams.params;

                  //api.infoLog( 'sek-import-from-file', params );

                  if ( _.isUndefined( params.imported_content.data ) || _.isUndefined( params.imported_content.metas ) ) {
                        api.errare( 'updateAPISetting::sek-import-from-file => invalid imported content', imported_content );
                        return;
                  }

                  var importedCollection = _.isArray( params.imported_content.data.collection ) ? $.extend( true, [], params.imported_content.data.collection ) : [];

                  // SHALL WE ASSIGN SECTIONS FROM MISSING LOCATIONS TO THE FIRST ACTIVE LOCATION ?
                  // For example the current page has only the 'loop_start' location, whereas the imported content includes 3 locations :
                  // - after_header
                  // - loop_start
                  // - before_footer
                  // Among those 3 locations, 2 are not active in the page.
                  // We will merge all section collections from the 3 imported locations one new collection, that will be assigned to 'loop_start'
                  // Note that the active imported locations are ordered like they were on the page when exported.
                  //
                  // So :
                  // 1) identify the first active location of the page
                  // 2) populate a new collection of combined sections from all active imported locations.
                  // 3) updated the imported collection with this
                  if ( true === params.assign_missing_locations ) {
                        var importedActiveLocations = params.imported_content.metas.active_locations,
                            currentActiveLocations = api.czr_sektions.activeLocations();

                        // console.log('Current set value ?', api( _collectionSettingId_ )() );
                        // console.log('import params', params );
                        // console.log('importedCollection?', importedCollection );
                        // console.log('importedActiveLocations', importedActiveLocations );

                        // first active location of the current setting
                        var firstCurrentActiveLocationId = _.first( currentActiveLocations );

                        if ( !_.isEmpty( firstCurrentActiveLocationId ) && !_.isEmpty( importedActiveLocations ) && _.isArray( importedActiveLocations ) ) {
                              // importedActiveLocationsNotAvailableInCurrentActiveLocations
                              // Example :
                              // active location in the page : loop_start, loop_end
                              // active locations imported : after_header, loop_start, before_footer
                              // importedActiveLocationsNotAvailableInCurrentActiveLocations => after_header, before_footer
                              var importedActiveLocationsNotAvailableInCurrentActiveLocations = $(importedActiveLocations).not(currentActiveLocations).get(),
                                  firstCurrentLocationData = self.getLevelModel( firstCurrentActiveLocationId, self.updAPISetParams.newSetValue.collection ),
                                  importedTargetLocationData = self.getLevelModel( firstCurrentActiveLocationId, params.imported_content.data.collection ),
                                  newCollectionForTargetLocation = [];// the collection that will hold the merge of all active imported collections

                              // normalize
                              // => make sure we have a collection array, even empty
                              firstCurrentLocationData.collection = _.isArray( firstCurrentLocationData.collection ) ? firstCurrentLocationData.collection : [];
                              importedTargetLocationData.collection = _.isArray( importedTargetLocationData.collection ) ? importedTargetLocationData.collection : [];

                              // loop on the active imported locations
                              // Example : ["__after_header", "__before_main_wrapper", "loop_start", "__before_footer"]
                              // and populate newCollectionForTargetLocation, with locations ordered as they were on export
                              // importedCollection is a clone
                              _.each( importedActiveLocations, function( impLocationId ){
                                    var impLocationData = self.getLevelModel( impLocationId, importedCollection );
                                    if ( _.isEmpty( impLocationData.collection ) )
                                      return;
                                    newCollectionForTargetLocation = _.union( newCollectionForTargetLocation, impLocationData.collection );
                              });//_.each( importedActiveLocations

                              // replace the previous collection of the target location, by the union of all collections.
                              // for example, if 'loop_start' is the target location, all sections will be added to it.
                              importedTargetLocationData.collection = newCollectionForTargetLocation;

                              // remove the missing locations from the imported collection
                              // importedActiveLocationsNotAvailableInCurrentActiveLocations
                              params.imported_content.data.collection = _.filter( params.imported_content.data.collection, function( _location ) {
                                    return !_.contains( importedActiveLocationsNotAvailableInCurrentActiveLocations, _location.id );
                              });
                        }//if ( !_.isEmpty( firstCurrentActiveLocationId ) )
                  }//if ( true === params.assign_missing_locations )


                  // SHALL WE MERGE ?
                  // Sept 2019 note : for local import only. Not implemented for global https://github.com/presscustomizr/nimble-builder/issues/495
                  // loop on each location of the imported content
                  // if the current setting value has sections in a location, add them before the imported ones
                  // keep_existing_sections is a user check option
                  // @see PHP sek_get_module_params_for_sek_local_imp_exp()
                  if ( true === params.keep_existing_sections ) {
                      // note that importedCollection is a unlinked clone of params.imported_content.data.collection
                      // merge sections
                      _.each( importedCollection, function( imp_location_data ) {
                            var currentLocationData = self.getLevelModel( imp_location_data.id, self.updAPISetParams.newSetValue.collection );
                            if ( _.isEmpty( currentLocationData.collection ) )
                              return;

                            var importedLocationData = self.getLevelModel( imp_location_data.id, params.imported_content.data.collection );
                            importedLocationData.collection = _.union( currentLocationData.collection, importedLocationData.collection );
                      });

                      // merge fonts if needed
                      if ( self.updAPISetParams.newSetValue.fonts && !_.isEmpty( self.updAPISetParams.newSetValue.fonts ) && _.isArray( self.updAPISetParams.newSetValue.fonts ) ) {
                            params.imported_content.data.fonts = _.isArray( params.imported_content.data.fonts ) ? params.imported_content.data.fonts : [];
                            // merge and remove duplicated fonts
                            params.imported_content.data.fonts =  _.uniq( _.union( self.updAPISetParams.newSetValue.fonts, params.imported_content.data.fonts ) );
                      }
                  }// if true === params.merge

                  self.updAPISetParams.newSetValue = params.imported_content.data;
            },












            //-------------------------------------------------------------------------------------------------
            //-- IMPORT FROM TMPL GALLERY
            //-------------------------------------------------------------------------------------------------
            _updAPISet_sek_import_tmpl_from_gallery : function() {
                  var self = this,
                      params;

                  params = self.updAPISetParams.params;

                  api.infoLog( 'sek-import-tmpl-from-gallery', params );

                  if ( _.isUndefined( params.imported_content.data ) || _.isUndefined( params.imported_content.metas ) ) {
                        api.errare( 'updateAPISetting::sek-import-tmpl-from-gallery => invalid imported content', imported_content );
                        return;
                  }

                  var importedCollection = _.isArray( params.imported_content.data.collection ) ? $.extend( true, [], params.imported_content.data.collection ) : [];

                  // SHALL WE ASSIGN SECTIONS FROM MISSING LOCATIONS TO THE FIRST ACTIVE LOCATION ?
                  // For example the current page has only the 'loop_start' location, whereas the imported content includes 3 locations :
                  // - after_header
                  // - loop_start
                  // - before_footer
                  // Among those 3 locations, 2 are not active in the page.
                  // We will merge all section collections from the 3 imported locations one new collection, that will be assigned to 'loop_start'
                  // Note that the active imported locations are ordered like they were on the page when exported.
                  //
                  // So :
                  // 1) identify the first active location of the page
                  // 2) populate a new collection of combined sections from all active imported locations.
                  // 3) updated the imported collection with this
                  if ( true === params.assign_missing_locations ) {
                        var importedActiveLocations = params.imported_content.metas.tmpl_locations,
                            currentActiveLocations = api.czr_sektions.activeLocations();

                        console.log('SO COLLECTION BEFORE ?', params.imported_content.data.collection );

                        // console.log('Current set value ?', api( _collectionSettingId_ )() );
                        // console.log('import params', params );
                        console.log('currentActiveLocations?', currentActiveLocations );
                        console.log('importedCollection?', importedCollection );
                        console.log('importedActiveLocations?', importedActiveLocations );

                        // first active location of the current setting
                        var firstCurrentActiveLocationId = _.first( currentActiveLocations );

                        if ( !_.isEmpty( firstCurrentActiveLocationId ) && !_.isEmpty( importedActiveLocations ) && _.isArray( importedActiveLocations ) ) {
                              // importedActiveLocationsNotAvailableInCurrentActiveLocations
                              // Example :
                              // active location in the page : loop_start, loop_end
                              // active locations imported : after_header, loop_start, before_footer
                              // importedActiveLocationsNotAvailableInCurrentActiveLocations => after_header, before_footer
                              var importedActiveLocationsNotAvailableInCurrentActiveLocations = $(importedActiveLocations).not(currentActiveLocations).get(),
                                  firstCurrentLocationData = self.getLevelModel( firstCurrentActiveLocationId, self.updAPISetParams.newSetValue.collection ),
                                  importedTargetLocationData = self.getLevelModel( firstCurrentActiveLocationId, params.imported_content.data.collection ),
                                  newCollectionForTargetLocation = [];// the collection that will hold the merge of all active imported collections

                              // normalize
                              // => make sure we have a collection array, even empty
                              firstCurrentLocationData.collection = _.isArray( firstCurrentLocationData.collection ) ? firstCurrentLocationData.collection : [];
                              importedTargetLocationData.collection = _.isArray( importedTargetLocationData.collection ) ? importedTargetLocationData.collection : [];

                              // loop on the active imported locations
                              // Example : ["__after_header", "__before_main_wrapper", "loop_start", "__before_footer"]
                              // and populate newCollectionForTargetLocation, with locations ordered as they were on export
                              // importedCollection is a clone
                              _.each( importedActiveLocations, function( impLocationId ){
                                    var impLocationData = self.getLevelModel( impLocationId, importedCollection );
                                    if ( _.isEmpty( impLocationData.collection ) )
                                      return;
                                    newCollectionForTargetLocation = _.union( newCollectionForTargetLocation, impLocationData.collection );
                              });//_.each( importedActiveLocations

                              // replace the previous collection of the target location, by the union of all collections.
                              // for example, if 'loop_start' is the target location, all sections will be added to it.
                              importedTargetLocationData.collection = newCollectionForTargetLocation;



                              // remove the missing locations from the imported collection
                              // importedActiveLocationsNotAvailableInCurrentActiveLocations
                              // params.imported_content.data.collection = _.filter( params.imported_content.data.collection, function( _location ) {
                              //       return !_.contains( importedActiveLocationsNotAvailableInCurrentActiveLocations, _location.id );
                              // });
                              params.imported_content.data.collection = _.filter( params.imported_content.data.collection, function( _location ) {
                                    return _location.id === firstCurrentActiveLocationId;
                              });
                              console.log('SO NEW COLLECTION ?', params.imported_content.data.collection );
                        }//if ( !_.isEmpty( firstCurrentActiveLocationId ) )
                  }//if ( true === params.assign_missing_locations )


                  // SHALL WE MERGE ?
                  // Sept 2019 note : for local import only. Not implemented for global https://github.com/presscustomizr/nimble-builder/issues/495
                  // loop on each location of the imported content
                  // if the current setting value has sections in a location, add them before the imported ones
                  // keep_existing_sections is a user check option
                  // @see PHP sek_get_module_params_for_sek_local_imp_exp()
                  if ( true === params.keep_existing_sections ) {
                      // note that importedCollection is a unlinked clone of params.imported_content.data.collection
                      // merge sections
                      _.each( importedCollection, function( imp_location_data ) {
                            var currentLocationData = self.getLevelModel( imp_location_data.id, self.updAPISetParams.newSetValue.collection );
                            if ( _.isEmpty( currentLocationData.collection ) )
                              return;

                            var importedLocationData = self.getLevelModel( imp_location_data.id, params.imported_content.data.collection );
                            importedLocationData.collection = _.union( currentLocationData.collection, importedLocationData.collection );
                      });

                      // merge fonts if needed
                      if ( self.updAPISetParams.newSetValue.fonts && !_.isEmpty( self.updAPISetParams.newSetValue.fonts ) && _.isArray( self.updAPISetParams.newSetValue.fonts ) ) {
                            params.imported_content.data.fonts = _.isArray( params.imported_content.data.fonts ) ? params.imported_content.data.fonts : [];
                            // merge and remove duplicated fonts
                            params.imported_content.data.fonts =  _.uniq( _.union( self.updAPISetParams.newSetValue.fonts, params.imported_content.data.fonts ) );
                      }
                  }// if true === params.merge

                  self.updAPISetParams.newSetValue = params.imported_content.data;
            }

      });//$.extend()
})( wp.customize, jQuery );