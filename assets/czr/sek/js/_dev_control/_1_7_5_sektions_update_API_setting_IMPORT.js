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

                  // ASSIGN MISSING LOCATIONS => IF IMPORTED LOCATIONS DON'T MATCH CURRENT PAGE LOCATIONS
                  // NB will import sections in the first active location of the page
                  // Important : header and footer must be excluded from active locations
                  //
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
                            allActiveLocations = api.czr_sektions.activeLocations(),
                            currentActiveLocations;

                        // Set the current active locations excluding header and footer location
                        _.each( allActiveLocations, function( loc_id ) {
                              if( !self.isHeaderLocation( loc_id ) && !self.isFooterLocation( loc_id ) ) {
                                    currentActiveLocations.push(loc_id);
                              }
                        });

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
                      var currentFonts = self.updAPISetParams.newSetValue.fonts,
                          importedFonts = params.imported_content.data.fonts;

                      if ( currentFonts && !_.isEmpty( currentFonts ) && _.isArray( currentFonts ) ) {
                            importedFonts = _.isArray( importedFonts ) ? importedFonts : [];
                            // merge and remove duplicated fonts
                            params.imported_content.data.fonts =  _.uniq( _.union( currentFonts, importedFonts ) );
                      }
                  }// if true === params.merge

                  self.updAPISetParams.newSetValue = params.imported_content.data;
            },












            //-------------------------------------------------------------------------------------------------
            //-- IMPORT FROM TMPL GALLERY
            //-------------------------------------------------------------------------------------------------
            // self.updAPISetParams.params : {
            //    action: "sek-import-tmpl-from-gallery"
            //    assign_missing_locations: undefined
            //    cloneId: ""
            //    imported_content: {data: {…}, metas: {…}, img_errors: Array(0)}
            //    is_global_location: false
            //    scope: "local"
            //    tmpl_import_mode: "replace"
            // }
            _updAPISet_sek_import_tmpl_from_gallery : function() {
                  var self = this,
                      params;

                  params = self.updAPISetParams.params;

                  api.infoLog( 'sek-import-tmpl-from-gallery', params );

                  // DO WE HAVE PROPER CONTENT DO IMPORT ?
                  if ( _.isUndefined( params.imported_content.data ) || _.isUndefined( params.imported_content.metas ) ) {
                        api.errare( 'updateAPISetting::sek-import-tmpl-from-gallery => invalid imported content', imported_content );
                        return;
                  }

                  //-------------------------------------------------------------------------------------------------
                  //-- HELPERS
                  //-------------------------------------------------------------------------------------------------
                  var _allTmplLocationsExistLocally = function() {
                        var bool = true;
                        _.each( tmplLocations, function( loc_id ){
                              if (!bool)
                                return;
                              bool = _.contains(localLocations, loc_id);
                        });
                        return bool;
                  };

                  // @return bool
                  var _isTmplHeaderLocId = function( loc_id ) {
                        return params.imported_content && params.imported_content.metas && loc_id === params.imported_content.metas.tmpl_header_location;
                  };

                  // @return bool
                  var _isTmplFooterLocId = function( loc_id ) {
                        return params.imported_content && params.imported_content.metas && loc_id === params.imported_content.metas.tmpl_footer_location;
                  };

                  // The template has a header/footer if we find the header or the footer location
                  // AND
                  // if there's a local_header_footer property set in the local_options
                  var _hasTmplHeaderFooter = function() {
                        var hasHeaderFooterLoc = false;
                        _.each( tmplLocations, function( loc_id ){
                              if (hasHeaderFooterLoc)
                                return;

                              if ( _isTmplHeaderLocId( loc_id ) || _isTmplFooterLocId( loc_id ) ) {
                                    hasHeaderFooterLoc = self.getLevelModel( loc_id, tmplCollection );
                                    hasHeaderFooterLoc = 'no_match' != hasHeaderFooterLoc;
                              }
                        });
                        return hasHeaderFooterLoc && !_.isEmpty( tmplLocalOptions.local_header_footer );
                  };

                  var _tmplUsesNBtemplate = function() {
                        return tmplLocalOptions && tmplLocalOptions.template && 'nimble_template' === tmplLocalOptions.template.local_template;
                  };

                  var tmplCollection = _.isArray( params.imported_content.data.collection ) ? $.extend( true, [], params.imported_content.data.collection ) : [],
                      tmplLocations = params.imported_content.metas.tmpl_locations,
                      localLocations = [],
                      currentSettingCollection = self.updAPISetParams.newSetValue.collection;


                  // Set the current local locations, make sure we exclude all global locations
                  _.each( api.czr_sektions.activeLocations(), function( loc_id ) {
                        if( !self.isGlobalLocationId(loc_id) ) {
                              localLocations.push(loc_id);
                        }
                  });

                  // Imported Active Locations has to be an array not empty
                  if ( !_.isArray(tmplLocations) || _.isEmpty(tmplLocations) ) {
                        api.errare( 'updateAPISetting::sek-import-tmpl-from-gallery => invalid imported template locations', params );
                        return;
                  }

                  // TEMPLATE LOCAL OPTIONS and FONTS
                  // Important :
                  // - Local options is structured as an object : { local_header_footer: {…}, widths: {…}} }. But when not populated, it can be an array []. So make sure the type if set as object before merging it with current page local options
                  // - Fonts is a collection described with an array
                  var tmplLocalOptions = params.imported_content.data.local_options;
                  tmplLocalOptions = $.extend( true, {}, _.isObject( tmplLocalOptions ) ? tmplLocalOptions : {} );
                  var tmplFonts = params.imported_content.data.fonts;
                  tmplFonts = _.isArray( tmplFonts ) ? $.extend( true, [], tmplFonts ) : [];

                  // Define variables uses for all cases
                  var newSetValueCollection = $.extend( true, [], currentSettingCollection ),// Create a deep copy of the current API collection
                      _allContentSectionsInTmpl = [],
                      targetLocationId = '__not_set__',
                      locModel,
                      targetLocationModel,
                      tmplLocCandidate, localLocCandidate;

                  // Gather all template content sections from potentially multiple locations in one collection
                  // => header and footer locations are excluded from this collection
                  // This collection is used :
                  // - in 'replace' mode when template locations don't exists in the local context
                  // - in 'before' and 'after' mode
                  // Note : if this collection is used the template header and footer ( if any ) have to be added separately
                  _.each( tmplCollection, function( loc_data ){
                        if ( _isTmplHeaderLocId( loc_data.id ) || _isTmplFooterLocId( loc_data.id ) )
                          return;
                        if( !_.isEmpty( loc_data.collection ) ) {
                              _allContentSectionsInTmpl = _.union( _allContentSectionsInTmpl, loc_data.collection );
                        }
                  });



                  // console.log('_hasTmplHeaderFooter ?', _hasTmplHeaderFooter() );

                  // console.log('_allContentSectionsInTmpl ?',  _allContentSectionsInTmpl);
                  // console.log('NEW SET VALUE COLLECTION? ', $.extend( true, [], newSetValueCollection ) );
                  // If the current page already has NB sections, the user can chose 3 options : REPLACE, BEFORE, AFTER.
                  // when the page has no NB sections, the default option is REPLACE
                  switch( params.tmpl_import_mode ) {
                        //-------------------------------------------------------------------------------------------------
                        //-- REPLACE CASE ( default case )
                        //-------------------------------------------------------------------------------------------------
                        case 'replace' :
                              // api.infoLog('CURRENT SETTING VALUE ?', self.updAPISetParams.newSetValue );
                              // console.log('SO COLLECTION BEFORE ?', tmplCollection );
                              // return bool

                              // IF ALL TEMPLATE LOCATIONS EXIST IN CURRENT PAGE
                              // Loop on local locations, use template locations when exists, otherwise use local ones
                              if ( _allTmplLocationsExistLocally() ) {
                                    // Replace locations from local collection that are provided by the tmpl, and not empty
                                    // => if the header / footer template location is empty, keep the local one
                                    // => the tmpl location will replace the local location in the collection
                                    newSetValueCollection = [];
                                    var resetLocalLocation, newLocalLocation;
                                    _.each( currentSettingCollection, function( _localLocation ) {
                                          tmplLocCandidate = _.findWhere(tmplCollection, { id : _localLocation.id }) || {};
                                          if ( _.isEmpty( tmplLocCandidate.collection ) ) {
                                                if ( self.isHeaderLocation( _localLocation.id ) || self.isFooterLocation( _localLocation.id ) ) {
                                                      newSetValueCollection.push( _localLocation );
                                                } else {
                                                      // Reset previous local location to defaults
                                                      resetLocalLocation = { collection : [], options :[] };
                                                      newLocalLocation = $.extend( true, {}, _localLocation );
                                                      newLocalLocation = $.extend( newLocalLocation, resetLocalLocation );
                                                      newSetValueCollection.push( newLocalLocation );
                                                }
                                          } else {
                                                newSetValueCollection.push( tmplLocCandidate );
                                          }
                                    });
                                    // console.log('tmplCollection ??', tmplCollection );
                                    // console.log('localLocations ??', localLocations);
                              } else {
                                    // IF TEMPLATE LOCATIONS DO NOT MATCH THE ONES OF THE CURRENT PAGE => ASSIGN ALL TEMPLATE SECTIONS TO LOOP_START OR First local content location
                                    if ( _tmplUsesNBtemplate() ) {
                                          targetLocationId = 'loop_start';
                                    } else {
                                          if ( _.contains(localLocations, 'loop_start') ) {
                                                targetLocationId = 'loop_start';
                                          } else {
                                                _.each( localLocations, function( loc_id ) {
                                                      if ( !self.isHeaderLocation( loc_id ) && !self.isFooterLocation( loc_id ) ) {
                                                            targetLocationId = loc_id;
                                                      }
                                                });
                                          }
                                    }
                                    // At this point, we need a target location id
                                    if ( '__not_set__' === targetLocationId ) {
                                          api.errare( 'updateAPISetting::sek-import-tmpl-from-gallery => target location id is empty' );
                                          break;
                                    }

                                    // Get the current target location model
                                    targetLocationModel = self.getLevelModel( targetLocationId, newSetValueCollection );
                                    if ( 'no_match' === targetLocationModel ) {
                                          api.errare('::_updAPISet_sek_import_tmpl_from_gallery => error => target location id ' + targetLocationId );
                                          break;
                                    }
                                    targetLocationModel = $.extend( true, {}, targetLocationModel );// <= create a deep copy

                                    // Replace the target location collection with the template one
                                    targetLocationModel.collection = _allContentSectionsInTmpl;

                                    // remove all locations from future setting value
                                    newSetValueCollection = [];

                                    // If the template has a header/footer use it
                                    // else, if a header footer is defined locally
                                    if ( _hasTmplHeaderFooter() ) {
                                          _.each( tmplLocations, function( loc_id ) {
                                                if ( _isTmplHeaderLocId( loc_id ) || _isTmplFooterLocId( loc_id ) ) {
                                                      tmplLocCandidate = self.getLevelModel( loc_id, tmplCollection );
                                                      if ( 'no_match' === tmplLocCandidate ) {
                                                            api.errare('::_updAPISet_sek_import_tmpl_from_gallery => error => location id ' + loc_id +' not found in template collection');
                                                            return;
                                                      } else {
                                                            newSetValueCollection.push( tmplLocCandidate );
                                                      }
                                                }
                                          });
                                    }


                                    // Populate the local target location with the template section collection
                                    // AND
                                    // Re-populate the header and footer location, either with the local one, or the template one ( if any)
                                    _.each( localLocations, function( loc_id ) {
                                          if ( targetLocationId === loc_id ) {
                                                newSetValueCollection.push( targetLocationModel );
                                          }
                                          localLocModel = self.getLevelModel( loc_id, currentSettingCollection );
                                          if ( 'no_match' === localLocModel ) {
                                                api.errare('::_updAPISet_sek_import_tmpl_from_gallery => error => location id ' + loc_id +' not found in current setting collection');
                                                return;
                                          }
                                          // re-add header and footer if _hasTmplHeaderFooter()
                                          if ( !_hasTmplHeaderFooter() ) {
                                                if ( self.isHeaderLocation( loc_id ) || self.isFooterLocation( loc_id ) ) {
                                                      newSetValueCollection.push( localLocModel );
                                                }
                                          }
                                    });
                              }
                        break;


                        //-------------------------------------------------------------------------------------------------
                        //-- INJECT BEFORE CASE
                        //-------------------------------------------------------------------------------------------------
                        case 'before' :
                              // For the before case, we are sure that hasCurrentPageNBSectionsNotHeaderFooter() is true
                              // so there's at least one location that has section(s)
                              // Find the first non header/footer location not empty
                              _.each( localLocations, function( loc_id ){
                                    // stop if the location id has been found
                                    if ( '__not_set__' != targetLocationId )
                                      return;

                                    locModel = self.getLevelModel( loc_id, newSetValueCollection );
                                    if ( 'no_match' === locModel ) {
                                          api.errare('::_updAPISet_sek_import_tmpl_from_gallery => error => location id not found' + loc_id );
                                          return;
                                    }
                                    if ( !self.isHeaderLocation( loc_id ) && !self.isFooterLocation( loc_id ) ) {
                                          if ( !_.isEmpty( locModel.collection ) ) {
                                                targetLocationId = loc_id;
                                                targetLocationModel = locModel;
                                          }
                                    }
                              });

                              // At this point, we need a target location id
                              if ( '__not_set__' === targetLocationId ) {
                                    api.errare( 'updateAPISetting::sek-import-tmpl-from-gallery => target location id is empty' );
                                    break;
                              }

                              // Get the current target location model
                              targetLocationModel = $.extend( true, {}, targetLocationModel );

                              // Adds the template sections BEFORE the existing sections of the target location
                              targetLocationModel.collection = _.union( _allContentSectionsInTmpl, targetLocationModel.collection );

                              // remove all locations from future setting value
                              newSetValueCollection = [];

                              // Re-populate the location models previously removed the updated target location model
                              _.each( localLocations, function( loc_id ){
                                    if ( targetLocationId === loc_id ) {
                                          newSetValueCollection.push( targetLocationModel );
                                    } else {
                                          if ( 'no_match' === locModel ) {
                                                api.errare('::_updAPISet_sek_import_tmpl_from_gallery => error => location id not found' + loc_id );
                                                return;
                                          }
                                          newSetValueCollection.push( self.getLevelModel( loc_id, currentSettingCollection ) );
                                    }
                              });
                        break;

                        //-------------------------------------------------------------------------------------------------
                        //-- INJECT AFTER CASE
                        //-------------------------------------------------------------------------------------------------
                        case 'after' :
                              // For the after case, we are sure that hasCurrentPageNBSectionsNotHeaderFooter() is true
                              // so there's at least one location that has section(s)
                              // Find the last non header/footer location not empty
                              _.each( localLocations.reverse(), function( loc_id ){
                                    // stop if the location id has been found
                                    if ( '__not_set__' != targetLocationId )
                                      return;

                                    locModel = self.getLevelModel( loc_id, newSetValueCollection );
                                    if ( 'no_match' === locModel ) {
                                          api.errare('::_updAPISet_sek_import_tmpl_from_gallery => error => location id not found' + loc_id );
                                          return;
                                    }
                                    if ( !self.isHeaderLocation( loc_id ) && !self.isFooterLocation( loc_id ) ) {
                                          if ( !_.isEmpty( locModel.collection ) ) {
                                                targetLocationId = loc_id;
                                                targetLocationModel = locModel;
                                          }
                                    }
                              });

                              // At this point, we need a target location id
                              if ( '__not_set__' === targetLocationId ) {
                                    api.errare( 'updateAPISetting::sek-import-tmpl-from-gallery => target location id is empty' );
                                    break;
                              }

                              // Get the current target location model
                              targetLocationModel = $.extend( true, {}, targetLocationModel );

                              // Adds the template sections AFTER the existing sections of the target location
                              targetLocationModel.collection = _.union( targetLocationModel.collection, _allContentSectionsInTmpl );

                              // remove all locations from future setting value
                              newSetValueCollection = [];

                              // Re-populate the location models previously removed the updated target location model
                              _.each( localLocations, function( loc_id ){
                                    if ( targetLocationId === loc_id ) {
                                          newSetValueCollection.push( targetLocationModel );
                                    } else {
                                          locModel = self.getLevelModel( loc_id, currentSettingCollection );
                                          if ( 'no_match' === locModel ) {
                                                api.errare('::_updAPISet_sek_import_tmpl_from_gallery => error => loc id not found' + loc_id );
                                                return;
                                          }
                                          newSetValueCollection.push( locModel );
                                    }
                              });
                        break;
                  }

                  // update the API setting
                  // this is a candiate setting value, the new setting value will be validated in ::updateAPISetting => ::validateSettingValue()
                  self.updAPISetParams.newSetValue.collection = newSetValueCollection;

                  // LOCAL OPTIONS
                  // local_options states if the imported template uses nimble_template, or use custom_width, custom_css, performance, etc.. see the full list of local options in ::generateUIforLocalSkopeOptions
                  // Design decision : by default NB extends existing local options with the imported ones.
                  // import mode :
                  // 'replace' (default) => local options extended
                  // insert 'before' or 'after' => existing local options are preserved
                  //
                  // Scenario :
                  // 1) user has created NB sections on a single post and wants to insert a NB template before the existing sections ( 'before' import_mode )
                  // => in this case, we need to keep the default theme template, local options must be the existing ones => no extension of local options.
                  //
                  // 2) the current page has no NB sections yet, import mode is 'replace' by default
                  // => it means that if the imported template uses NB template as canvas, it must be set in local options => extension of local options
                  if ( !_.isEmpty( tmplLocalOptions ) && 'replace' === params.tmpl_import_mode ) {
                        var currentLocalOptions = self.updAPISetParams.newSetValue.local_options;
                        currentLocalOptions = $.extend( true, {}, _.isObject( currentLocalOptions ) ? currentLocalOptions : {} );
                        self.updAPISetParams.newSetValue.local_options = _.extend( currentLocalOptions, tmplLocalOptions );
                  }

                  // FONTS
                  // If there are imported fonts, we need to merge when import mode is not 'replace', otherwise we need to copy the imported font collection in .fonts property of the API setting.
                  if ( _.isArray( tmplFonts ) && !_.isEmpty( tmplFonts ) ) {
                        if ( 'replace' != params.tmpl_import_mode ) {
                              var currentFonts = self.updAPISetParams.newSetValue.fonts;
                              currentFonts = $.extend( true, [], _.isArray( currentFonts ) ? currentFonts : [] );
                              // merge two collection of fonts without duplicates
                              self.updAPISetParams.newSetValue.fonts = _.uniq( _.union( tmplFonts, currentFonts ));
                        } else {
                              self.updAPISetParams.newSetValue.fonts = tmplFonts;
                        }
                  }

                  //api.infoLog('SETTING VALUE AFTER ?', self.updAPISetParams.newSetValue );
            }//_updAPISet_sek_import_tmpl_from_gallery

      });//$.extend()
})( wp.customize, jQuery );