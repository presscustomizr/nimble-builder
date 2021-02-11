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
            //-- ADD SECTION
            //-------------------------------------------------------------------------------------------------
            _updAPISet_sek_add_sektion :  function() {
                  var self = this,
                      params,
                      columnCandidate,
                      parentSektionCandidate,
                      locationCandidate;

                  params = self.updAPISetParams.params;

                  //console.log('ADD SECTION ?', params, self.updAPISetParams );
                  // an id must be provided
                  if ( _.isEmpty( params.id ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                  }

                  if ( _.isEmpty( params.location ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                  }
                  // Is this a nested sektion ?
                  if ( true === params.is_nested ) {
                        columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                        // can we add this nested sektion ?
                        // if the parent sektion of the column has is_nested = true, then we can't
                        parentSektionCandidate = self.getLevelModel( params.in_sektion, self.updAPISetParams.newSetValue.collection );
                        if ( 'no_match' == parentSektionCandidate ) {
                              self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                        } else if ( true === parentSektionCandidate.is_nested ) {
                              self.updAPISetParams.promise.reject('');
                              api.previewer.trigger('sek-notify', {
                                    type : 'info',
                                    duration : 30000,
                                    message : [
                                          '<span style="font-size:0.95em">',
                                            '<strong>' + sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ] + '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        } else if ( 'no_match' == columnCandidate ) {
                              api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                              self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                        } else {
                              columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                              columnCandidate.collection.push({
                                    id : params.id,
                                    level : 'section',
                                    collection : [{
                                          id : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid(),
                                          level : 'column',
                                          collection : [],
                                          ver_ini : sektionsLocalizedData.nimbleVersion
                                    }],
                                    is_nested : true,
                                    ver_ini : sektionsLocalizedData.nimbleVersion
                              });
                        }
                  } else {
                        locationCandidate = self.getLevelModel( params.location, self.updAPISetParams.newSetValue.collection );
                        if ( 'no_match' == locationCandidate ) {
                              api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                              self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                        } else {
                              var position = 0;
                              locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                              _.each( locationCandidate.collection, function( secModel, index ) {
                                    if ( params.before_section === secModel.id ) {
                                          position = index;
                                    }
                                    if ( params.after_section === secModel.id ) {
                                          position = index + 1;
                                    }
                              });

                              // @see reactToCollectionSettingIdChange
                              locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                              // insert the section in the collection at the right place
                              locationCandidate.collection.splice( position, 0, {
                                    id : params.id,
                                    level : 'section',
                                    collection : [{
                                          id : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid(),
                                          level : 'column',
                                          collection : [],
                                          ver_ini : sektionsLocalizedData.nimbleVersion
                                    }],
                                    ver_ini : sektionsLocalizedData.nimbleVersion
                              });
                        }
                  }
            },//_updAPISet_sek_add_sektion





            //-------------------------------------------------------------------------------------------------
            //-- DUPLICATE SECTION
            //-------------------------------------------------------------------------------------------------
            _updAPISet_sek_duplicate_sektion :  function() {
                  var self = this,
                      params,
                      columnCandidate,
                      locationCandidate,
                      deepClonedSektion;

                  params = self.updAPISetParams.params;

                  // an id must be provided
                  if ( _.isEmpty( params.id ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                  }
                  if ( _.isEmpty( params.location ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                  }
                  try { deepClonedSektion = self.cloneLevel( params.id ); } catch( er ) {
                        api.errare( 'updateAPISetting => ' + params.action, er );
                        return;
                  }

                  // items id of multi-items module must always be unique
                  // this recursive method sniff and does the job
                  self.maybeGenerateNewItemIdsForCrudModules( deepClonedSektion );

                  var _position_ = self.getLevelPositionInCollection( params.id, self.updAPISetParams.newSetValue.collection );
                  // Is this a nested sektion ?
                  if ( true === params.is_nested ) {
                        columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                        if ( 'no_match' == columnCandidate ) {
                              api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                              self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                              return;
                        }

                        columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                        columnCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );


                  } else {
                        locationCandidate = self.getLevelModel( params.location, self.updAPISetParams.newSetValue.collection );
                        if ( 'no_match' == locationCandidate ) {
                              api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                              self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                              return;
                        }
                        locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                        // @see reactToCollectionSettingIdChange
                        locationCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );

                  }
                  self.updAPISetParams.cloneId = deepClonedSektion.id;//will be passed in resolve()
            },//_updAPISet_sek_duplicate_sektion


            //-------------------------------------------------------------------------------------------------
            //-- REMOVE SECTION
            //-------------------------------------------------------------------------------------------------
            // in the case of a nested sektion, we have to remove it from a column
            // otherwise from the root sektion collection
            _updAPISet_sek_remove_sektion : function() {
                  var self = this,
                      params,
                      columnCandidate,
                      locationCandidate;

                  params = self.updAPISetParams.params;
                  //api.infoLog('PARAMS IN sek-remove-sektion', params );
                  if ( true === params.is_nested ) {
                        columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                        if ( 'no_match' != columnCandidate ) {
                              columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                              columnCandidate.collection = _.filter( columnCandidate.collection, function( col ) {
                                    return col.id != params.id;
                              });
                        } else {
                              api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                        }
                  } else {
                        locationCandidate = self.getLevelModel( params.location, self.updAPISetParams.newSetValue.collection );
                        if ( 'no_match' == locationCandidate ) {
                              api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                              self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                              return;
                        }
                        locationCandidate.collection = _.filter( locationCandidate.collection, function( sek ) {
                              return sek.id != params.id;
                        });
                  }
            },




            //-------------------------------------------------------------------------------------------------
            //-- MOVE SECTION (DRAG IN THE PAGE INSIDE A LOCATION, OR CROSS LOCATIONS)
            //-------------------------------------------------------------------------------------------------
            _updAPISet_sek_move_sektion : function() {
                  var self = this,
                      params,
                      originalCollection,
                      reorderedCollection,
                      locationCandidate;

                  params = self.updAPISetParams.params;

                  //api.infoLog('PARAMS in sek-move-section', params );
                  var toLocationCandidate = self.getLevelModel( params.to_location, self.updAPISetParams.newSetValue.collection ),
                      movedSektionCandidate,
                      copyOfMovedSektionCandidate;

                  if ( _.isEmpty( toLocationCandidate ) || 'no_match' == toLocationCandidate ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing target location' );
                  }

                  // MOVED CROSS LOCATIONS
                  // - make a copy of the moved sektion
                  // - remove the moved sektion from the source location
                  if ( params.from_location != params.to_location ) {
                        // Remove the moved sektion from the source location
                        var fromLocationCandidate = self.getLevelModel( params.from_location, self.updAPISetParams.newSetValue.collection );
                        if ( _.isEmpty( fromLocationCandidate ) || 'no_match' == fromLocationCandidate ) {
                              throw new Error( 'updateAPISetting => ' + params.action + ' => missing source location' );
                        }

                        fromLocationCandidate.collection =  _.isArray( fromLocationCandidate.collection ) ? fromLocationCandidate.collection : [];
                        // Make a copy of the sektion candidate now, before removing it
                        movedSektionCandidate = self.getLevelModel( params.id, fromLocationCandidate.collection );
                        copyOfMovedSektionCandidate = $.extend( true, {}, movedSektionCandidate );
                        // remove the sektion from its previous sektion
                        fromLocationCandidate.collection = _.filter( fromLocationCandidate.collection, function( sektion ) {
                              return sektion.id != params.id;
                        });
                  }

                  // UPDATE THE TARGET LOCATION
                  toLocationCandidate.collection = _.isArray( toLocationCandidate.collection ) ? toLocationCandidate.collection : [];
                  originalCollection = $.extend( true, [], toLocationCandidate.collection );
                  reorderedCollection = [];
                  _.each( params.newOrder, function( _id_ ) {
                        // in the case of a cross location movement, we need to add the moved sektion to the target location
                        if ( params.from_location != params.to_location && _id_ == copyOfMovedSektionCandidate.id ) {
                              reorderedCollection.push( copyOfMovedSektionCandidate );
                        } else {
                              sektionCandidate = self.getLevelModel( _id_, originalCollection );
                              if ( _.isEmpty( sektionCandidate ) || 'no_match' == sektionCandidate ) {
                                    throw new Error( 'updateAPISetting => ' + params.action + ' => missing section candidate' );
                              }
                              reorderedCollection.push( sektionCandidate );
                        }
                  });
                  toLocationCandidate.collection = reorderedCollection;
            },//_updAPISet_sek_move_sektion

            //-------------------------------------------------------------------------------------------------
            //-- MOVE SECTION UP DOWN
            //-------------------------------------------------------------------------------------------------
            _updAPISet_sek_move_sektion_up_down : function() {
                  var self = this,
                      params,
                      parentCandidate,
                      originalCollection,
                      reorderedCollection,
                      locationCandidate;

                  params = self.updAPISetParams.params;

                  parentCandidate = self.getLevelModel( params.is_nested ? params.in_column : params.location , self.updAPISetParams.newSetValue.collection );
                  if ( _.isEmpty( parentCandidate ) || 'no_match' == parentCandidate ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing target location' );
                  }
                  parentCandidate.collection = _.isArray( parentCandidate.collection ) ? parentCandidate.collection : [];
                  originalCollection = $.extend( true, [], parentCandidate.collection );
                  reorderedCollection = $.extend( true, [], parentCandidate.collection );

                  var _indexInOriginal = _.findIndex( originalCollection, function( _sec_ ) {
                        return _sec_.id === params.id;
                  });
                  // @see https://underscorejs.org/#findIndex
                  if ( -1 === _indexInOriginal ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => invalid index' );
                  }

                  // Swap up <=> down
                  var direction = params.direction || 'up',
                      isLastSectionInLocation = originalCollection.length === _indexInOriginal + 1,
                      isFirstSectionInLocation = 0 === _indexInOriginal,
                      _locInfo = self.activeLocationsInfo(),
                      _currentLocInfo = ! _.isArray( _locInfo ) ? {} : _.findWhere( _locInfo, { id : params.location } ),
                      isCurrentLocationGlobal = false,
                      isCurrentLocationHeaderOrFooter = false;

                  // Is current location global ?
                  isCurrentLocationGlobal = _.isObject( _currentLocInfo ) && _currentLocInfo.is_global;

                  // Is current location header footer ?
                  isCurrentLocationHeaderOrFooter = _.isObject( _currentLocInfo ) && _currentLocInfo.is_header_footer;

                  // When a section is last in location and there's another location below, let's move the section to this sibling location
                  // This is possible when :
                  // - when moved section is not nested
                  // - only in locations that are 'local', not header or footer

                  // Populate the eligible activeLocations in the page
                  var activeLocationsInPage = [];
                  // self.activeLocationsInfo() is set on ::initialized when send by the preview, and is structured the following way :
                  //  [
                  //  {
                  //   id: "loop_start"
                  //   is_global: false
                  //   is_header_footer: false
                  //  },
                  //  {..},
                  //  .
                  if ( _.isArray( _locInfo ) ) {
                        _.each( self.activeLocationsInfo(), function( _loc_ ) {
                              if ( ! _loc_.is_global && ! _loc_.is_header_footer ) {
                                    activeLocationsInPage.push( _loc_.id );
                              }
                        });
                  }

                  // Set the index of the current location
                  var indexOfCurrentLocation = _.findIndex( activeLocationsInPage, function( _loc_id ) {
                        return _loc_id === params.location;
                  });

                  var isCurrentLocationEligibleForSectionMoveOutside = ! params.is_nested && ! isCurrentLocationGlobal && ! isCurrentLocationHeaderOrFooter,
                      isFirstLocationInPage = 0 === indexOfCurrentLocation,
                      isLastLocationInPage = activeLocationsInPage.length === indexOfCurrentLocation + 1,
                      newLocationId, newLocationCandidate;

                  if ( isCurrentLocationEligibleForSectionMoveOutside && isLastSectionInLocation && 'up' !== direction && ! isLastLocationInPage ) {
                        newLocationId = activeLocationsInPage[ indexOfCurrentLocation + 1 ];
                        newLocationCandidate = self.getLevelModel( newLocationId , self.updAPISetParams.newSetValue.collection );

                        // Add the section in first position of the section below
                        newLocationCandidate.collection.unshift( originalCollection[ _indexInOriginal ] );
                        // Removes the section in last position of the original section
                        parentCandidate.collection.pop();
                        // the new_location param will be used in the 'complete' callback of 'sek-move-section-down' / 'sek-move-section-up'
                        params.new_location = newLocationId;

                  } else if ( isCurrentLocationEligibleForSectionMoveOutside && isFirstSectionInLocation && 'up' === direction && ! isFirstLocationInPage ) {
                        newLocationId = activeLocationsInPage[ indexOfCurrentLocation - 1 ];
                        newLocationCandidate = self.getLevelModel( newLocationId , self.updAPISetParams.newSetValue.collection );

                        // Add the section in first position of the section below
                        newLocationCandidate.collection.push( originalCollection[ _indexInOriginal ] );
                        // Removes the section in last position of the original section
                        parentCandidate.collection.shift();
                        // the new_location param will be used in the 'complete' callback of 'sek-move-section-down' / 'sek-move-section-up'
                        params.new_location = newLocationId;
                  } else {
                        // prevent absurd movements of a section
                        // this should not happen because up / down arrows are not displayed when section is positionned top / bottom
                        // but safer to add it
                        if ( 'up' !== direction && originalCollection.length === _indexInOriginal + 1 ) {
                              //throw new Error( 'updateAPISetting => ' + params.action + ' => bottom reached' );
                              api.previewer.trigger('sek-notify', {
                                    type : 'info',
                                    duration : 30000,
                                    message : [
                                          '<span style="font-size:0.95em">',
                                            '<strong>' + sektionsLocalizedData.i18n[ "The section cannot be moved lower." ] + '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        } else if ( 'up' === direction && 0 === _indexInOriginal ){
                              api.previewer.trigger('sek-notify', {
                                    type : 'info',
                                    duration : 30000,
                                    message : [
                                          '<span style="font-size:0.95em">',
                                            '<strong>' + sektionsLocalizedData.i18n[ "The section cannot be moved higher." ] + '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        } else {
                              reorderedCollection[ _indexInOriginal ] = originalCollection[ 'up' === direction ? _indexInOriginal - 1 : _indexInOriginal + 1 ];
                              reorderedCollection[ 'up' === direction ? _indexInOriginal - 1 : _indexInOriginal + 1 ] = originalCollection[ _indexInOriginal ];
                              parentCandidate.collection = reorderedCollection;
                        }
                  }
            }//_updAPISet_sek_move_sektion_up_down

      });//$.extend()
})( wp.customize, jQuery );