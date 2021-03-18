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
            //-- INJECT FROM TMPL GALLERY
            //-------------------------------------------------------------------------------------------------
            // self.updAPISetParams.params : {
            //    action: "sek-inject-tmpl-from-gallery"
            //    assign_missing_locations: undefined
            //    cloneId: ""
            //    injected_content: {data: {…}, metas: {…}, img_errors: Array(0)}
            //    is_global_location: false
            //    scope: "local"
            //    tmpl_inject_mode: "replace"
            // }
            _updAPISet_sek_inject_tmpl_from_gallery : function() {
                  var self = this,
                      params;

                  params = self.updAPISetParams.params;

                  //api.infoLog( 'api update params for sek-inject-tmpl-from-gallery', params );

                  // DO WE HAVE PROPER CONTENT DO INJECT ?
                  if ( _.isUndefined( params.injected_content.data ) || _.isUndefined( params.injected_content.metas ) ) {
                        api.errare( 'updateAPISetting::sek-inject-tmpl-from-gallery => invalid imported content', injected_content );
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
                        return params.injected_content && params.injected_content.metas && loc_id === params.injected_content.metas.tmpl_header_location;
                  };

                  // @return bool
                  var _isTmplFooterLocId = function( loc_id ) {
                        return params.injected_content && params.injected_content.metas && loc_id === params.injected_content.metas.tmpl_footer_location;
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

                  var tmplCollection = _.isArray( params.injected_content.data.collection ) ? $.extend( true, [], params.injected_content.data.collection ) : [],
                      tmplLocations = params.injected_content.metas.tmpl_locations,
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
                        api.errare( 'updateAPISetting::sek-inject-tmpl-from-gallery => invalid imported template locations', params );
                        return;
                  }

                  // TEMPLATE LOCAL OPTIONS and FONTS
                  // Important :
                  // - Local options is structured as an object : { local_header_footer: {…}, widths: {…}} }. But when not populated, it can be an array []. So make sure the type if set as object before merging it with current page local options
                  // - Fonts is a collection described with an array
                  var tmplLocalOptions = params.injected_content.data.local_options;
                  tmplLocalOptions = $.extend( true, {}, _.isObject( tmplLocalOptions ) ? tmplLocalOptions : {} );
                  var tmplFonts = params.injected_content.data.fonts;
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
                  switch( params.tmpl_inject_mode ) {
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
                                          api.errare( 'updateAPISetting::sek-inject-tmpl-from-gallery => target location id is empty' );
                                          break;
                                    }

                                    // Get the current target location model
                                    targetLocationModel = self.getLevelModel( targetLocationId, newSetValueCollection );
                                    if ( 'no_match' === targetLocationModel ) {
                                          api.errare('::_updAPISet_sek_inject_tmpl_from_gallery => error => target location id ' + targetLocationId );
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
                                                            api.errare('::_updAPISet_sek_inject_tmpl_from_gallery => error => location id ' + loc_id +' not found in template collection');
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
                                                api.errare('::_updAPISet_sek_inject_tmpl_from_gallery => error => location id ' + loc_id +' not found in current setting collection');
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
                                          api.errare('::_updAPISet_sek_inject_tmpl_from_gallery => error => location id not found' + loc_id );
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
                                    api.errare( 'updateAPISetting::sek-inject-tmpl-from-gallery => target location id is empty' );
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
                                                api.errare('::_updAPISet_sek_inject_tmpl_from_gallery => error => location id not found' + loc_id );
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
                                          api.errare('::_updAPISet_sek_inject_tmpl_from_gallery => error => location id not found' + loc_id );
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
                                    api.errare( 'updateAPISetting::sek-inject-tmpl-from-gallery => target location id is empty' );
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
                                                api.errare('::_updAPISet_sek_inject_tmpl_from_gallery => error => loc id not found' + loc_id );
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
                  // 1) user has created NB sections on a single post and wants to insert a NB template before the existing sections ( 'before' inject_mode )
                  // => in this case, we need to keep the default theme template, local options must be the existing ones => no extension of local options.
                  //
                  // 2) the current page has no NB sections yet, import mode is 'replace' by default
                  // => it means that if the imported template uses NB template as canvas, it must be set in local options => extension of local options
                  if ( !_.isEmpty( tmplLocalOptions ) && 'replace' === params.tmpl_inject_mode ) {
                        var currentLocalOptions = self.updAPISetParams.newSetValue.local_options;
                        currentLocalOptions = $.extend( true, {}, _.isObject( currentLocalOptions ) ? currentLocalOptions : {} );
                        self.updAPISetParams.newSetValue.local_options = _.extend( currentLocalOptions, tmplLocalOptions );
                  }

                  // FONTS
                  // If there are imported fonts, we need to merge when import mode is not 'replace', otherwise we need to copy the imported font collection in .fonts property of the API setting.
                  if ( _.isArray( tmplFonts ) && !_.isEmpty( tmplFonts ) ) {
                        if ( 'replace' != params.tmpl_inject_mode ) {
                              var currentFonts = self.updAPISetParams.newSetValue.fonts;
                              currentFonts = $.extend( true, [], _.isArray( currentFonts ) ? currentFonts : [] );
                              // merge two collection of fonts without duplicates
                              self.updAPISetParams.newSetValue.fonts = _.uniq( _.union( tmplFonts, currentFonts ));
                        } else {
                              self.updAPISetParams.newSetValue.fonts = tmplFonts;
                        }
                  }

                  //api.infoLog('SETTING VALUE AFTER ?', self.updAPISetParams.newSetValue );
            }//_updAPISet_sek_inject_tmpl_from_gallery

      });//$.extend()
})( wp.customize, jQuery );