//global sektionsLocalizedData, serverControlParams
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // user action => this utility must be used to set the main setting value
            // params = {
            //    action : 'sek-add-section', 'sek-add-column', 'sek-add-module',...
            //    in_sektion
            //    in_column
            // }
            updateAPISetting : function( params ) {
                  var self = this,
                      __updateAPISettingDeferred__ = $.Deferred();

                  // Are we in global location ?
                  // Add the global information to the params
                  // => is used to determine the skope id when resolving the promise in reactToPreviewMsg
                  params = params || {};
                  params.is_global_location = 'global' === params.scope || self.isGlobalLocation( params );

                  var _collectionSettingId_ = params.is_global_location ? self.getGlobalSectionsSettingId() : self.localSectionsSettingId();
                  var _do_update_setting_id = function() {
                        // api( _collectionSettingId_)() = {
                        //    collection : [
                        //       'loop_start' :  { level : location,  collection : [ 'sek124' : { collection : [], level : section, options : {} }], options : {}},
                        //       'loop_end' : { level : location, collection : [], options : {}}
                        //        ...
                        //    ],
                        //    options : {}
                        //
                        // }
                        var currentSetValue = api( _collectionSettingId_ )(),
                            newSetValue = _.isObject( currentSetValue ) ? $.extend( true, {}, currentSetValue ) : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ),
                            locationCandidate,
                            sektionCandidate,
                            columnCandidate,
                            moduleCandidate,
                            // move variables
                            originalCollection,
                            reorderedCollection,
                            //duplication variable
                            cloneId, //will be passed in resolve()
                            startingModuleValue,// will be populated by the optional starting value specificied on module registration
                            __presetSectionInjected__ = '_not_injection_scenario_',//this property is turned into a $.Deferred() object in a scenario of section injection
                            parentSektionCandidate;

                        // make sure we have a collection array to populate
                        newSetValue.collection = _.isArray( newSetValue.collection ) ? newSetValue.collection : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ).collection;

                        switch( params.action ) {
                              //-------------------------------------------------------------------------------------------------
                              //-- SEKTION
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-section' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    if ( _.isEmpty( params.location ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                                    }
                                    // Is this a nested sektion ?
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          // can we add this nested sektion ?
                                          // if the parent sektion of the column has is_nested = true, then we can't
                                          parentSektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                          if ( 'no_match' == parentSektionCandidate ) {
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                                break;
                                          }
                                          if ( true === parentSektionCandidate.is_nested ) {
                                                __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ]);
                                                break;
                                          }
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.push({
                                                id : params.id,
                                                level : 'section',
                                                collection : [{
                                                      id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                      level : 'column',
                                                      collection : [],
                                                      ver_ini : sektionsLocalizedData.nimbleVersion
                                                }],
                                                is_nested : true,
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          });
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
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
                                                      id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                      level : 'column',
                                                      collection : [],
                                                      ver_ini : sektionsLocalizedData.nimbleVersion
                                                }],
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          });
                                    }
                              break;


                              case 'sek-duplicate-section' :
                                    //api.infoLog('PARAMS IN sek-duplicate-section', params );
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    if ( _.isEmpty( params.location ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                                    }
                                    var deepClonedSektion;
                                    try { deepClonedSektion = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          break;
                                    }

                                    // items id of multi-items module must always be unique
                                    // this recursive method sniff and does the job
                                    self.maybeGenerateNewItemIdsForCrudModules( deepClonedSektion );

                                    var _position_ = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    // Is this a nested sektion ?
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }

                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );


                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          // @see reactToCollectionSettingIdChange
                                          locationCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );

                                    }
                                    cloneId = deepClonedSektion.id;//will be passed in resolve()
                              break;

                              // in the case of a nested sektion, we have to remove it from a column
                              // otherwise from the root sektion collection
                              case 'sek-remove-section' :
                                    //api.infoLog('PARAMS IN sek-remove-sektion', params );
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          if ( 'no_match' != columnCandidate ) {
                                                columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                                columnCandidate.collection = _.filter( columnCandidate.collection, function( col ) {
                                                      return col.id != params.id;
                                                });
                                          } else {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          }
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.filter( locationCandidate.collection, function( sek ) {
                                                return sek.id != params.id;
                                          });
                                    }
                              break;

                              case 'sek-move-section' :
                                    //api.infoLog('PARAMS in sek-move-section', params );
                                    var toLocationCandidate = self.getLevelModel( params.to_location, newSetValue.collection ),
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
                                          var fromLocationCandidate = self.getLevelModel( params.from_location, newSetValue.collection );
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

                              break;


                              // Fired on click on up / down arrows in the section ui menu
                              // This handles the nested sections case
                              case 'sek-move-section-up-down' :
                                    //api.infoLog('PARAMS in sek-move-section-up', params );
                                    parentCandidate = self.getLevelModel( params.is_nested ? params.in_column : params.location , newSetValue.collection );
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
                                    var direction = params.direction || 'up';

                                    // prevent absurd movements of a section
                                    // this should not happen because up / down arrows are not displayed when section is positionned top / bottom
                                    // but safer to add it
                                    if ( 'up' !== direction && originalCollection.length === _indexInOriginal + 1 ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => bottom reached' );
                                    } else if ( 'up' === direction && 0 === _indexInOriginal ){
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => top reached' );
                                    }

                                    reorderedCollection[ _indexInOriginal ] = originalCollection[ 'up' === direction ? _indexInOriginal - 1 : _indexInOriginal + 1 ];
                                    reorderedCollection[ 'up' === direction ? _indexInOriginal - 1 : _indexInOriginal + 1 ] = originalCollection[ _indexInOriginal ];
                                    parentCandidate.collection = reorderedCollection;
                              break;








                              //-------------------------------------------------------------------------------------------------
                              //-- COLUMN
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-column' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == sektionCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    // can we add another column ?
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }

                                    // RESET ALL COLUMNS WIDTH
                                    // _.each( sektionCandidate.collection, function( colModel ) {
                                    //       colModel.width = '';
                                    // });
                                    self.resetColumnsWidthInSection( sektionCandidate );

                                    sektionCandidate.collection.push({
                                          id :  params.id,
                                          level : 'column',
                                          collection : [],
                                          ver_ini : sektionsLocalizedData.nimbleVersion
                                    });
                              break;


                              case 'sek-remove-column' :
                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' != sektionCandidate ) {
                                          // can we remove the column ?
                                          if ( 1 === _.size( sektionCandidate.collection ) ) {
                                                __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n["A section must have at least one column."]);
                                                break;
                                          }
                                          sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                          sektionCandidate.collection = _.filter( sektionCandidate.collection, function( column ) {
                                                return column.id != params.id;
                                          });
                                          // RESET ALL COLUMNS WIDTH
                                          // _.each( sektionCandidate.collection, function( colModel ) {
                                          //       colModel.width = '';
                                          // });
                                          self.resetColumnsWidthInSection( sektionCandidate );
                                    } else {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                    }

                              break;

                              case 'sek-duplicate-column' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == sektionCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    // can we add another column ?
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }

                                    var deepClonedColumn;
                                    try { deepClonedColumn = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          break;
                                    }

                                    // items id of multi-items module must always be unique
                                    // this recursive method sniff and does the job
                                    self.maybeGenerateNewItemIdsForCrudModules( deepClonedColumn );

                                    var _position = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    cloneId = deepClonedColumn.id;//will be passed in resolve()
                                    sektionCandidate.collection.splice( parseInt( _position + 1, 10 ), 0, deepClonedColumn );
                                    // RESET ALL COLUMNS WIDTH
                                    // _.each( sektionCandidate.collection, function( colModel ) {
                                    //       colModel.width = '';
                                    // });
                                    self.resetColumnsWidthInSection( sektionCandidate );
                              break;


                              // Note : the css rules are generated in Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
                              case 'sek-resize-columns' :
                                    if ( params.col_number < 2 )
                                      break;

                                    var resizedColumn = self.getLevelModel( params.resized_column, newSetValue.collection ),
                                        sistercolumn = self.getLevelModel( params.sister_column, newSetValue.collection );

                                    //api.infoLog( 'updateAPISetting => ' + params.action + ' => ', params );

                                    // SET RESIZED COLUMN WIDTH
                                    if ( 'no_match' == resizedColumn ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no resized column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no resized column matched');
                                          break;
                                    }

                                    var _getColumnWidth = function( _candidate_ ) {
                                          var _width = '_not_set_';
                                          var _options = _.isObject( _candidate_.options ) ? _candidate_.options : {};
                                          if ( ! _.isEmpty( _options ) && _options.width && _options.width['custom-width'] ) {
                                                _width = parseFloat( _options.width['custom-width'] * 1 );
                                          }
                                          return _width;
                                    };

                                    var _setColumnWidth = function( _candidate_, newWidthValue ) {
                                          // start from a deep cloned object
                                          // important => fixes https://github.com/presscustomizr/nimble-builder/issues/455
                                          var _new_options_values = $.extend( true, {}, _candidate_.options || {} );

                                          _new_options_values.width = _.isObject( _new_options_values.width ) ? _new_options_values.width : {};
                                          _new_options_values.width['custom-width'] = newWidthValue;
                                          _candidate_.options = _new_options_values;

                                          // Live update the input value ( when rendered )
                                          $('body').find('[data-sek-width-range-column-id="'+ _candidate_.id +'"]').val( newWidthValue ).trigger('input', { is_resize_column_trigger : true } );
                                          return newWidthValue;
                                    };
                                    ///


                                    // DEPRECATED SINCE JUNE 2019 => resizedColumn.width = parseFloat( params.resizedColumnWidthInPercent );

                                    var resizedColumnWidthInPercent = _setColumnWidth( resizedColumn, parseFloat( params.resizedColumnWidthInPercent ) );
                                    // cast to number
                                    resizedColumnWidthInPercent = parseFloat( resizedColumnWidthInPercent );

                                    // SET OTHER COLUMNS WIDTH
                                    var parentSektion = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    var otherColumns = _.filter( parentSektion.collection, function( _col_ ) {
                                          return _col_.id != resizedColumn.id && _col_.id != sistercolumn.id;
                                    });
                                    var otherColumnsWidth = parseFloat( resizedColumnWidthInPercent.toFixed(3) );

                                    if ( ! _.isEmpty( otherColumns ) ) {
                                         _.each( otherColumns, function( colModel ) {
                                                currentColWidth = _getColumnWidth( colModel );
                                                if ( '_not_set_' === currentColWidth || ! _.isNumber( currentColWidth * 1 ) || _.isEmpty( currentColWidth + '' ) || 1 > currentColWidth ) {
                                                      // DEPRECATED SINCE JUNE 2019 => colModel.width = parseFloat( ( 100 / params.col_number ).toFixed(3) );
                                                      currentColWidth = _setColumnWidth( colModel, parseFloat( ( 100 / params.col_number ).toFixed(3) ) );
                                                }

                                                // sum up all other column's width, excluding the resized and sister one.
                                                otherColumnsWidth = parseFloat( ( otherColumnsWidth  +  currentColWidth ).toFixed(3) );
                                          });
                                    }

                                    // SET SISTER COLUMN WIDTH
                                    // sum up all other column's width, excluding the resized and sister one.
                                    // api.infoLog( "resizedColumn.width", resizedColumn.width  );
                                    // api.infoLog( "otherColumns", otherColumns );

                                    // then calculate the sistercolumn so we are sure that we feel the entire space of the sektion
                                    // DEPRECATED SINCE JUNE 2019 => sistercolumn.width = parseFloat( ( 100 - otherColumnsWidth ).toFixed(3) );
                                    _setColumnWidth( sistercolumn, parseFloat( ( 100 - otherColumnsWidth ).toFixed(3) ) );
                                    // api.infoLog('otherColumnsWidth', otherColumnsWidth );
                                    // api.infoLog("sistercolumn.width", sistercolumn.width );
                                    // api.infoLog( "parseFloat( ( 100 - otherColumnsWidth ).toFixed(3) )" , parseFloat( ( 100 - otherColumnsWidth ).toFixed(3) ) );
                                    //api.infoLog('COLLECTION AFTER UPDATE ', parentSektion.collection );
                              break;




                              case 'sek-move-column' :
                                    var toSektionCandidate = self.getLevelModel( params.to_sektion, newSetValue.collection ),
                                        movedColumnCandidate,
                                        copyOfMovedColumnCandidate;

                                    if ( _.isEmpty( toSektionCandidate ) || 'no_match' == toSektionCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target sektion' );
                                    }

                                    if ( params.from_sektion != params.to_sektion ) {
                                          // Remove the moved column from the source sektion
                                          var fromSektionCandidate = self.getLevelModel( params.from_sektion, newSetValue.collection );
                                          if ( _.isEmpty( fromSektionCandidate ) || 'no_match' == fromSektionCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source column' );
                                          }

                                          fromSektionCandidate.collection =  _.isArray( fromSektionCandidate.collection ) ? fromSektionCandidate.collection : [];
                                          // Make a copy of the column candidate now, before removing it
                                          movedColumnCandidate = self.getLevelModel( params.id, fromSektionCandidate.collection );
                                          copyOfMovedColumnCandidate = $.extend( true, {}, movedColumnCandidate );
                                          // remove the column from its previous sektion
                                          fromSektionCandidate.collection = _.filter( fromSektionCandidate.collection, function( column ) {
                                                return column.id != params.id;
                                          });
                                          // Reset the column's width in the target sektion
                                          // _.each( fromSektionCandidate.collection, function( colModel ) {
                                          //       colModel.width = '';
                                          // });
                                          self.resetColumnsWidthInSection( fromSektionCandidate );
                                    }

                                    // update the target sektion
                                    toSektionCandidate.collection =  _.isArray( toSektionCandidate.collection ) ? toSektionCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toSektionCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          // in the case of a cross sektion movement, we need to add the moved column to the target sektion
                                          if ( params.from_sektion != params.to_sektion && _id_ == copyOfMovedColumnCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedColumnCandidate );
                                          } else {
                                                columnCandidate = self.getLevelModel( _id_, originalCollection );
                                                if ( _.isEmpty( columnCandidate ) || 'no_match' == columnCandidate ) {
                                                      throw new Error( 'updateAPISetting => moveColumn => missing columnCandidate' );
                                                }
                                                reorderedCollection.push( columnCandidate );
                                          }
                                    });
                                    toSektionCandidate.collection = reorderedCollection;

                                    // Reset the column's width in the target sektion
                                    // _.each( toSektionCandidate.collection, function( colModel ) {
                                    //       colModel.width = '';
                                    // });
                                    self.resetColumnsWidthInSection( toSektionCandidate );

                              break;












                              //-------------------------------------------------------------------------------------------------
                              //-- MODULE
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-module' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    // a module_type must be provided
                                    if ( _.isEmpty( params.module_type ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing module_type' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' === columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    var position = 0;
                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                    // get the position of the before or after module
                                    _.each( columnCandidate.collection, function( moduleModel, index ) {
                                          if ( params.before_module === moduleModel.id ) {
                                                position = index;
                                          }
                                          if ( params.after_module === moduleModel.id ) {
                                                position = index + 1;
                                          }
                                    });

                                    var _moduleParams = {
                                          id : params.id,
                                          level : 'module',
                                          module_type : params.module_type,
                                          ver_ini : sektionsLocalizedData.nimbleVersion
                                    };
                                    // Let's add the starting value if provided when registrating the module
                                    startingModuleValue = self.getModuleStartingValue( params.module_type );
                                    if ( 'no_starting_value' !== startingModuleValue ) {
                                          _moduleParams.value = startingModuleValue;
                                    }

                                    columnCandidate.collection.splice( position, 0, _moduleParams );
                              break;

                              case 'sek-duplicate-module' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' == columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                    var deepClonedModule;
                                    try { deepClonedModule = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => error when cloning the level');
                                          break;
                                    }
                                    // items id of multi-items module must always be unique
                                    // this recursive method sniff and does the job
                                    self.maybeGenerateNewItemIdsForCrudModules( deepClonedModule );

                                    var insertInposition = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    cloneId = deepClonedModule.id;//will be passed in resolve()
                                    columnCandidate.collection.splice( parseInt( insertInposition + 1, 10 ), 0, deepClonedModule );
                              break;

                              case 'sek-remove-module' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' != columnCandidate ) {
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection = _.filter( columnCandidate.collection, function( module ) {
                                                return module.id != params.id;
                                          });

                                    } else {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                    }
                              break;

                              case 'sek-move-module' :
                                    var toColumnCandidate,
                                        movedModuleCandidate,
                                        copyOfMovedModuleCandidate;

                                    // loop on the sektions to find the toColumnCandidate
                                    // _.each( newSetValue.collection, function( _sektion_ ) {
                                    //       _.each( _sektion_.collection, function( _column_ ) {
                                    //             if ( _column_.id == params.to_column ) {
                                    //                  toColumnCandidate = _column_;
                                    //             }
                                    //       });
                                    // });
                                    toColumnCandidate = self.getLevelModel( params.to_column, newSetValue.collection );

                                    if ( _.isEmpty( toColumnCandidate ) || 'no_match' == toColumnCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target column' );
                                    }

                                    // If the module has been moved to another column
                                    // => remove the moved module from the source column
                                    if ( params.from_column != params.to_column ) {
                                          var fromColumnCandidate;
                                          fromColumnCandidate = self.getLevelModel( params.from_column, newSetValue.collection );

                                          if ( _.isEmpty( fromColumnCandidate ) || 'no_match' == fromColumnCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source column' );
                                          }

                                          fromColumnCandidate.collection =  _.isArray( fromColumnCandidate.collection ) ? fromColumnCandidate.collection : [];
                                          // Make a copy of the module candidate now, before removing it
                                          movedModuleCandidate = self.getLevelModel( params.id, newSetValue.collection );
                                          copyOfMovedModuleCandidate = $.extend( true, {}, movedModuleCandidate );
                                          // remove the module from its previous column
                                          fromColumnCandidate.collection = _.filter( fromColumnCandidate.collection, function( module ) {
                                                return module.id != params.id;
                                          });
                                    }// if params.from_column != params.to_column

                                    // update the target column
                                    toColumnCandidate.collection =  _.isArray( toColumnCandidate.collection ) ? toColumnCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toColumnCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          if ( params.from_column != params.to_column && _id_ == copyOfMovedModuleCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedModuleCandidate );
                                          } else {
                                                moduleCandidate = self.getLevelModel( _id_, newSetValue.collection );
                                                if ( _.isEmpty( moduleCandidate ) || 'no_match' == moduleCandidate ) {
                                                      throw new Error( 'updateAPISetting => ' + params.action + ' => missing moduleCandidate' );
                                                }
                                                reorderedCollection.push( moduleCandidate );
                                          }
                                    });
                                    // Check if we have duplicates ?
                                    if ( reorderedCollection.length != _.uniq( reorderedCollection ).length ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => there are duplicated modules in column : ' + toColumnCandidate.id );
                                    } else {
                                          toColumnCandidate.collection = reorderedCollection;
                                    }
                              break;


                              case 'sek-set-module-value' :
                                    moduleCandidate = self.getLevelModel( params.id, newSetValue.collection );

                                    var _modValueCandidate = {};
                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _modValueCandidate[ _key_ ] = _val_;
                                    });
                                    if ( 'no_match' == moduleCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no module matched', params );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => error no module matched');
                                          break;
                                    }
                                    if ( _.isEmpty( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                          break;
                                    }

                                    // Is this a father module ?
                                    // If yes, the module value is structured by option group, each option group being updated by a child module
                                    // If no, the default option type is : '__no_option_group_to_be_updated_by_children_modules__'
                                    if ( '__no_option_group_to_be_updated_by_children_modules__' === params.options_type ) {
                                          moduleCandidate.value = _modValueCandidate;
                                    } else {
                                          // start from a deep cloned object
                                          // prevents issues like https://github.com/presscustomizr/nimble-builder/issues/455
                                          var _new_module_values = $.extend( true, {}, _.isEmpty( moduleCandidate.value ) ? {} : moduleCandidate.value );
                                          _new_module_values[ params.options_type ] = _modValueCandidate;
                                          moduleCandidate.value = _new_module_values;
                                    }

                              break;






                              //-------------------------------------------------------------------------------------------------
                              //-- LEVEL OPTIONS
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-generate-level-options-ui' :
                                    var _candidate_ = self.getLevelModel( params.id, newSetValue.collection ),
                                        _valueCandidate = {};

                                    if ( 'no_match'=== _candidate_ ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }
                                    // start from a deep cloned object
                                    // important => fixes https://github.com/presscustomizr/nimble-builder/issues/455
                                    var _new_options_values = $.extend( true, {}, _candidate_.options || {} );

                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _valueCandidate[ _key_ ] = _val_;
                                    });

                                    if ( _.isEmpty( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                    }

                                    _new_options_values[ params.options_type ] = _valueCandidate;
                                    _candidate_.options = _new_options_values;
                              break;





                              //-------------------------------------------------------------------------------------------------
                              //-- LOCAL SKOPE OPTIONS
                              //-------------------------------------------------------------------------------------------------
                              // Note : this is saved in "local_options"
                              case 'sek-generate-local-skope-options-ui' :
                                    _valueCandidate = {};

                                    var _currentOptions = $.extend( true, {}, _.isObject( newSetValue.local_options ) ? newSetValue.local_options : {} );
                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _valueCandidate[ _key_ ] = _val_;
                                    });
                                    if ( _.isEmpty( params.options_type ) || ! _.isString( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                    } else {
                                          var newOptionsValues = {};
                                          newOptionsValues[ params.options_type ] = _valueCandidate;
                                          newSetValue.local_options = $.extend( _currentOptions, newOptionsValues );
                                    }
                              break;









                              //-------------------------------------------------------------------------------------------------
                              //-- CONTENT IN NEW SEKTION
                              //-------------------------------------------------------------------------------------------------
                              // @params {
                              //   drop_target_element : $(this),
                              //   position : _position,// <= top or bottom
                              //   before_section : $(this).data('sek-before-section'),
                              //   after_section : $(this).data('sek-after-section'),
                              //   content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ), //<= module or preset_section
                              //   content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                              // }
                              case 'sek-add-content-in-new-sektion' :
                                    // api.infoLog('update API Setting => sek-add-content-in-new-sektion => PARAMS', params );
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    // get the position of the before or after section
                                    position = 0;
                                    locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                    if ( 'no_match' == locationCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                          break;
                                    }
                                    locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                    _.each( locationCandidate.collection, function( secModel, index ) {
                                          if ( params.before_section === secModel.id ) {
                                                position = index;
                                          }
                                          if ( params.after_section === secModel.id ) {
                                                position = index + 1;
                                          }
                                    });

                                    switch( params.content_type) {
                                          // When a module is dropped in a section + column structure to be generated
                                          case 'module' :
                                                // Let's add the starting value if provided when registrating the module
                                                // Note : params.content_id is the module_type
                                                startingModuleValue = self.getModuleStartingValue( params.content_id );

                                                // insert the section in the collection at the right place
                                                locationCandidate.collection.splice( position, 0, {
                                                      id : params.id,
                                                      level : 'section',
                                                      collection : [
                                                            {
                                                                  id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                                  level : 'column',
                                                                  collection : [
                                                                        {
                                                                              id : params.droppedModuleId,
                                                                              level : 'module',
                                                                              module_type : params.content_id,
                                                                              value : 'no_starting_value' !== startingModuleValue ? startingModuleValue : null,
                                                                              ver_ini : sektionsLocalizedData.nimbleVersion
                                                                        }
                                                                  ],
                                                                  ver_ini : sektionsLocalizedData.nimbleVersion
                                                            }
                                                      ],
                                                      ver_ini : sektionsLocalizedData.nimbleVersion
                                                });
                                          break;

                                          // When a preset section is dropped
                                          case 'preset_section' :
                                                // insert the section in the collection at the right place
                                                __presetSectionInjected__ = $.Deferred();//defined at the beginning of the method

                                                var _doWhenPresetSectionCollectionFetched = function( presetColumnCollection ) {
                                                      self.preparePresetSectionForInjection( presetColumnCollection )
                                                            .fail( function( _er_ ){
                                                                  __updateAPISettingDeferred__.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                                                  // Used when updating the setting
                                                                  // @see end of this method
                                                                  __presetSectionInjected__.reject( _er_ );
                                                            })
                                                            .done( function( sectionReadyToInject ) {
                                                                  //api.infoLog( 'sectionReadyToInject', sectionReadyToInject );

                                                                  // If the preset_section is inserted in a an empty nested section, add it at the right place in the parent column of the nested section.
                                                                  // Otherwise, add the preset section at the right position in the parent location of the section.
                                                                  var insertedInANestedSektion = false;
                                                                  if ( ! _.isEmpty( params.sektion_to_replace ) ) {
                                                                        var sektionToReplace = self.getLevelModel( params.sektion_to_replace, newSetValue.collection );
                                                                        if ( 'no_match' === sektionToReplace ) {
                                                                              api.errare( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched' );
                                                                              __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched');
                                                                        }
                                                                        insertedInANestedSektion = true === sektionToReplace.is_nested;
                                                                  }

                                                                  if ( ! insertedInANestedSektion ) {
                                                                        locationCandidate.collection.splice( position, 0, {
                                                                              id : params.id,
                                                                              level : 'section',
                                                                              collection : sectionReadyToInject.collection,
                                                                              options : sectionReadyToInject.options || {},
                                                                              ver_ini : sektionsLocalizedData.nimbleVersion
                                                                        });
                                                                  } else {
                                                                        columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                                                        if ( 'no_match' === columnCandidate ) {
                                                                              api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                                              __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                                        }

                                                                        columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                                                        // get the position of the before or after module
                                                                        _.each( columnCandidate.collection, function( moduleOrSectionModel, index ) {
                                                                              if ( params.before_section === moduleOrSectionModel.id ) {
                                                                                    position = index;
                                                                              }
                                                                              if ( params.after_section === moduleOrSectionModel.id ) {
                                                                                    position = index + 1;
                                                                              }
                                                                        });
                                                                        columnCandidate.collection.splice( position, 0, {
                                                                              id : params.id,
                                                                              is_nested : true,
                                                                              level : 'section',
                                                                              collection : sectionReadyToInject.collection,
                                                                              options : sectionReadyToInject.options || {},
                                                                              ver_ini : sektionsLocalizedData.nimbleVersion
                                                                        });
                                                                  }

                                                                  // Used when updating the setting
                                                                  // @see end of this method
                                                                  __presetSectionInjected__.resolve();
                                                            });// self.preparePresetSectionForInjection.done()
                                                };//_doWhenPresetSectionCollectionFetched()


                                                // Try to fetch the sections from the server
                                                // if sucessfull, resolve __presetSectionInjected__.promise()
                                                self.getPresetSectionCollection({
                                                            is_user_section : params.is_user_section,
                                                            presetSectionId : params.content_id,
                                                            section_id : params.id//<= we need to use the section id already generated, and passed for ajax action @see ::reactToPreviewMsg, case "sek-add-section"
                                                      })
                                                      .fail( function( _er_ ) {
                                                            api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                            __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                                      })
                                                      .done( function( presetColumnCollection ) {
                                                            if ( ! _.isObject( presetColumnCollection ) || _.isEmpty( presetColumnCollection ) ) {
                                                                  api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnCollection );
                                                                  __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                            }
                                                            // OK. time to resolve __presetSectionInjected__.promise()
                                                            _doWhenPresetSectionCollectionFetched( presetColumnCollection );
                                                      });//self.getPresetSectionCollection().done()
                                          break;
                                    }//switch( params.content_type)
                              break;



                              //-------------------------------------------------------------------------------------------------
                              //-- CONTENT IN NEW NESTED SEKTION
                              //-------------------------------------------------------------------------------------------------
                              // @params {
                              //   drop_target_element : $(this),
                              //   position : _position,// <= top or bottom
                              //   before_section : $(this).data('sek-before-section'),
                              //   after_section : $(this).data('sek-after-section'),
                              //   content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ), //<= module or preset_section
                              //   content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                              // }
                              case 'sek-add-preset-section-in-new-nested-sektion' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );

                                    // can we add this nested sektion ?
                                    // if the parent sektion of the column has is_nested = true, then we can't
                                    parentSektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == parentSektionCandidate ) {
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                          break;
                                    }
                                    if ( true === parentSektionCandidate.is_nested ) {
                                          __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ]);
                                          break;
                                    }
                                    if ( 'no_match' == columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }
                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                    // insert the section in the collection at the right place
                                    var presetColumnCollection;
                                    __presetSectionInjected__ = $.Deferred();//defined at the beginning of the method

                                    _doWhenPresetSectionCollectionFetched = function( presetColumnCollection ) {
                                          self.preparePresetSectionForInjection( presetColumnCollection )
                                                .fail( function( _er_ ){
                                                      __updateAPISettingDeferred__.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                                      // Used when updating the setting
                                                      // @see end of this method
                                                      __presetSectionInjected__.reject( _er_ );
                                                })
                                                .done( function( sectionReadyToInject ) {
                                                      columnCandidate.collection.push({
                                                            id : params.id,
                                                            level : 'section',
                                                            collection : sectionReadyToInject.collection,
                                                            options : sectionReadyToInject.options || {},
                                                            is_nested : true,
                                                            ver_ini : sektionsLocalizedData.nimbleVersion
                                                      });

                                                      // Used when updating the setting
                                                      // @see end of this method
                                                      __presetSectionInjected__.resolve();
                                                });//self.preparePresetSectionForInjection.done()
                                    };//_doWhenPresetSectionCollectionFetched


                                    // Try to fetch the sections from the server
                                    // if sucessfull, resolve __presetSectionInjected__.promise()
                                    self.getPresetSectionCollection({
                                                is_user_section : params.is_user_section,
                                                presetSectionId : params.content_id,
                                                section_id : params.id//<= we need to use the section id already generated, and passed for ajax action @see ::reactToPreviewMsg, case "sek-add-section"
                                          })
                                          .fail( function() {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                          })
                                          .done( function( presetColumnCollection ) {
                                                if ( ! _.isObject( presetColumnCollection ) || _.isEmpty( presetColumnCollection ) ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnCollection );
                                                      __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                }
                                                // OK. time to resolve __presetSectionInjected__.promise()
                                                _doWhenPresetSectionCollectionFetched( presetColumnCollection );
                                          });//self.getPresetSectionCollection().done()
                              break;












                              //-------------------------------------------------------------------------------------------------
                              //-- POPULATE GOOGLE FONTS
                              //-------------------------------------------------------------------------------------------------
                              //@params {
                              //       action : 'sek-update-fonts',
                              //       font_family : newFontFamily,
                              // }
                              case 'sek-update-fonts' :
                                    // Get the gfonts from the level options and modules values
                                    var currentGfonts = self.sniffGFonts( { is_global_location : ( params && true === params.is_global_location ) } );
                                    if ( ! _.isEmpty( params.font_family ) && _.isString( params.font_family ) && ! _.contains( currentGfonts, params.font_family ) ) {
                                          if ( params.font_family.indexOf('gfont') < 0 ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont');
                                                break;
                                          }
                                          currentGfonts.push( params.font_family );
                                    }
                                    // update the global gfonts collection
                                    // this is then used server side in Sek_Dyn_CSS_Handler::sek_get_gfont_print_candidates to build the Google Fonts request
                                    newSetValue.fonts = currentGfonts;
                              break;

                              //-------------------------------------------------------------------------------------------------
                              //-- RESTORE A REVISION
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-restore-revision' :
                                    //api.infoLog( 'sek-restore-revision', params );
                                    newSetValue = params.revision_value;
                              break;

                              //-------------------------------------------------------------------------------------------------
                              //-- FILE IMPORT
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-import-from-file' :
                                    api.infoLog( 'sek-import-from-file', params );
                                    if ( _.isUndefined( params.imported_content.data ) || _.isUndefined( params.imported_content.metas ) ) {
                                          api.errare( 'updateAPISetting::sek-import-from-file => invalid imported content', imported_content );
                                          break;
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
                                                    firstCurrentLocationData = self.getLevelModel( firstCurrentActiveLocationId, newSetValue.collection ),
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
                                    // loop on each location of the imported content
                                    // if the current setting value has sections in a location, add them before the imported ones
                                    // keep_existing_sections is a user check option
                                    // @see PHP sek_get_module_params_for_sek_local_imp_exp()
                                    if ( true === params.keep_existing_sections ) {
                                        // note that importedCollection is a unlinked clone of params.imported_content.data.collection
                                        // merge sections
                                        _.each( importedCollection, function( imp_location_data ) {
                                              var currentLocationData = self.getLevelModel( imp_location_data.id, newSetValue.collection );
                                              if ( _.isEmpty( currentLocationData.collection ) )
                                                return;

                                              var importedLocationData = self.getLevelModel( imp_location_data.id, params.imported_content.data.collection );
                                              importedLocationData.collection = _.union( currentLocationData.collection, importedLocationData.collection );
                                        });

                                        // merge fonts if needed
                                        if ( newSetValue.fonts && !_.isEmpty( newSetValue.fonts ) && _.isArray( newSetValue.fonts ) ) {
                                              params.imported_content.data.fonts = _.isArray( params.imported_content.data.fonts ) ? params.imported_content.data.fonts : [];
                                              // merge and remove duplicated fonts
                                              params.imported_content.data.fonts =  _.uniq( _.union( newSetValue.fonts, params.imported_content.data.fonts ) );
                                        }
                                    }// if true === params.merge

                                    newSetValue = params.imported_content.data;
                              break;

                              //-------------------------------------------------------------------------------------------------
                              //-- RESET COLLECTION, LOCAL OR GLOBAL
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-reset-collection' :
                                    //api.infoLog( 'sek-import-from-file', params );
                                    try { newSetValue = api.czr_sektions.resetCollectionSetting( params.scope ); } catch( er ) {
                                          api.errare( 'sek-reset-collection => error when firing resetCollectionSetting()', er );
                                    }
                              break;
                        }// switch



                        // if we did not already rejected the request, let's check if the setting object has actually been modified
                        // at this point it should have been.
                        if ( 'pending' == __updateAPISettingDeferred__.state() ) {
                              var mayBeUpdateSektionsSetting = function() {

                                    // When a sektion setting is changed, "from" and "to" are passed to the .settingParams property
                                    // settingParams : {
                                    //       to : to,
                                    //       from : from,
                                    //       args : args
                                    // }
                                    // @see for example ::generateUIforFrontModules or ::generateUIforLevelOptions
                                    var isSettingValueChangeCase = params.settingParams && params.settingParams.from && params.settingParams.to;


                                    // in a setting value change case, the from and to must be different
                                    // implemented when fixing https://github.com/presscustomizr/nimble-builder/issues/455
                                    if ( isSettingValueChangeCase && _.isEqual( params.settingParams.from, params.settingParams.to ) ) {
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => main sektion setting change => the new setting value is unchanged when firing action : ' + params.action );
                                    } else if ( ! isSettingValueChangeCase && _.isEqual( currentSetValue, newSetValue ) ) {
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => the new setting value is unchanged when firing action : ' + params.action );
                                    } else {
                                          if ( null !== self.validateSettingValue( newSetValue, params.is_global_location ? 'global' : 'local' ) ) {
                                                api( _collectionSettingId_ )( newSetValue, params );
                                                // Add the cloneId to the params when we resolve
                                                // the cloneId is only needed in the duplication scenarii
                                                params.cloneId = cloneId;
                                                __updateAPISettingDeferred__.resolve( params );
                                          } else {
                                                __updateAPISettingDeferred__.reject( 'Validation problem for action ' + params.action );
                                          }
                                          //api.infoLog('COLLECTION SETTING UPDATED => ', _collectionSettingId_, api( _collectionSettingId_ )() );
                                    }
                              };//mayBeUpdateSektionsSetting()

                              // For all scenarios but section injection, we can update the sektion setting now
                              // otherwise we need to wait for the injection to be processed asynchronously
                              // CRITICAL => __updateAPISettingDeferred__ has to be resolved / rejected
                              // otherwise this can lead to scenarios where a change is not taken into account in ::updateAPISettingAndExecutePreviewActions
                              // like in https://github.com/presscustomizr/nimble-builder/issues/373
                              if ( '_not_injection_scenario_' === __presetSectionInjected__ ) {
                                    mayBeUpdateSektionsSetting();
                                    // At this point the __updateAPISettingDeferred__ obj can't be in a 'pending' state
                                    if ( 'pending' === __updateAPISettingDeferred__.state() ) {
                                          api.errare( '::updateAPISetting => The __updateAPISettingDeferred__ promise has not been resolved properly.');
                                    }
                              } else {
                                    __presetSectionInjected__
                                          .done( function() {
                                               mayBeUpdateSektionsSetting();
                                               // At this point the __updateAPISettingDeferred__ obj can't be in a 'pending' state
                                               if ( 'pending' === __updateAPISettingDeferred__.state() ) {
                                                    api.errare( '::updateAPISetting => The __updateAPISettingDeferred__ promise has not been resolved properly.');
                                               }
                                          })
                                          .fail( function( _er_ ) {
                                                api.errare( 'updateAPISetting => __presetSectionInjected__ failed', _er_ );
                                          });
                              }
                        }
                  };//_do_update_setting_id()


                  // Update the sektion collection
                  api( _collectionSettingId_, function( sektionSetInstance ) {
                        _do_update_setting_id();
                  });
                  return __updateAPISettingDeferred__.promise();
            },//updateAPISetting


            // used on :
            // - add column
            // - remove column
            // - duplicate column
            // - move column
            // added in June 2019 for https://github.com/presscustomizr/nimble-builder/issues/279
            resetColumnsWidthInSection : function( sektionCandidate ) {
                  // RESET ALL COLUMNS WIDTH
                  _.each( sektionCandidate.collection, function( colModel ) {
                        if ( colModel.options && colModel.options.width && colModel.options.width['custom-width'] ) {
                              colModel.options.width = _.omit( colModel.options.width, 'custom-width' );
                        }
                        colModel.width = '';// For backward compat since June 2019
                  });
            },


            // @return a promise()
            // caches the sections in api.sek_presetSections when api.section( '__content_picker__') is registered
            // caches the user saved sections on the first drag and drop of a user-saved section
            // @params {
            //  is_user_section : sectionParams.is_user_section
            //  preset_section_id : '' <= used for user_saved section
            // }
            _maybeFetchSectionsFromServer : function( params ) {
                  var dfd = $.Deferred(),
                      _ajaxRequest_;

                  params = params || { is_user_section : false };
                  if ( true === params.is_user_section ) {
                        if ( ! _.isEmpty( api.sek_userSavedSections ) && ! _.isEmpty( api.sek_userSavedSections[ params.preset_section_id ] ) ) {
                              dfd.resolve( api.sek_userSavedSections );
                        } else {
                              api.sek_userSavedSections = api.sek_userSavedSections || {};
                              if ( ! _.isUndefined( api.sek_fetchingUserSavedSections ) && 'pending' == api.sek_fetchingUserSavedSections.state() ) {
                                    _ajaxRequest_ = api.sek_fetchingUserSavedSections;
                              } else {
                                    _ajaxRequest_ = wp.ajax.post( 'sek_get_user_saved_sections', {
                                          nonce: api.settings.nonce.save,
                                          preset_section_id : params.preset_section_id
                                    });
                                    api.sek_fetchingUserSavedSections = _ajaxRequest_;
                              }
                              _ajaxRequest_.done( function( _sectionData_ ) {
                                    //api.sek_presetSections = JSON.parse( _collection_ );
                                    api.sek_userSavedSections[ params.preset_section_id ] = _sectionData_;
                                    dfd.resolve( api.sek_userSavedSections );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });
                        }
                  } else {
                        if ( ! _.isEmpty( api.sek_presetSections ) ) {
                              dfd.resolve( api.sek_presetSections );
                        } else {
                              if ( ! _.isUndefined( api.sek_fetchingPresetSections ) && 'pending' == api.sek_fetchingPresetSections.state() ) {
                                    _ajaxRequest_ = api.sek_fetchingPresetSections;
                              } else {
                                    _ajaxRequest_ = wp.ajax.post( 'sek_get_preset_sections', { nonce: api.settings.nonce.save } );
                                    api.sek_fetchingPresetSections = _ajaxRequest_;
                              }
                              _ajaxRequest_.done( function( _collection_ ) {
                                    //api.sek_presetSections = JSON.parse( _collection_ );
                                    api.sek_presetSections = _collection_;
                                    dfd.resolve( api.sek_presetSections );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });
                        }
                  }

                  return dfd.promise();
            },




            // First run : fetches the collection from the server
            // Next runs : uses the cached collection
            //
            // @return a JSON parsed string,
            // + guid() ids for each levels
            // ready for insertion
            //
            // @sectionParams : {
            //       is_user_section : bool, //<= is this section a "saved" section ?
            //       presetSectionId : params.content_id,
            //       section_id : params.id
            // }
            // Why is the section_id provided ?
            // Because this id has been generated ::reactToPreviewMsg, case "sek-add-section", and is the identifier that we'll need when ajaxing ( $_POST['id'])
            getPresetSectionCollection : function( sectionParams ) {
                  var self = this,
                      __dfd__ = $.Deferred();

                  self._maybeFetchSectionsFromServer({
                        is_user_section : sectionParams.is_user_section,
                        preset_section_id : sectionParams.presetSectionId
                  })
                        .fail( function( er ) {
                              __dfd__.reject( er );
                        })
                        .done( function( _collection_ ) {
                              //api.infoLog( 'preset_sections fetched', api.sek_presetSections );
                              var presetSection,
                                  allPresets = $.extend( true, {}, _.isObject( _collection_ ) ? _collection_ : {} );

                              if ( _.isEmpty( allPresets ) ) {
                                    throw new Error( 'getPresetSectionCollection => Invalid collection');
                              }
                              if ( _.isEmpty( allPresets[ sectionParams.presetSectionId ] ) ) {
                                    throw new Error( 'getPresetSectionCollection => the preset section : "' + sectionParams.presetSectionId + '" has not been found in the collection');
                              }
                              var presetCandidate = allPresets[ sectionParams.presetSectionId ];

                              // Ensure we have a string that's JSON.parse-able
                              // if ( typeof presetCandidate !== 'string' || presetCandidate[0] !== '{' ) {
                              //       throw new Error( 'getPresetSectionCollection => ' + sectionParams.presetSectionId + ' is not JSON.parse-able');
                              // }
                              // presetCandidate = JSON.parse( presetCandidate );

                              var setIds = function( collection ) {
                                    _.each( collection, function( levelData ) {
                                          levelData.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                                          if ( _.isArray( levelData.collection ) ) {
                                                setIds( levelData.collection );
                                          }
                                    });
                                    return collection;
                              };

                              var setVersion = function( collection ) {
                                    _.each( collection, function( levelData ) {
                                          levelData.ver_ini = sektionsLocalizedData.nimbleVersion;
                                          if ( _.isArray( levelData.collection ) ) {
                                                setVersion( levelData.collection );
                                          }
                                    });
                                    return collection;
                              };

                              // ID's
                              // set the section id provided.
                              presetCandidate.id = sectionParams.section_id;
                              // the other level's id have to be generated
                              presetCandidate.collection = setIds( presetCandidate.collection );

                              // NIMBLE VERSION
                              // set the section version
                              presetCandidate.ver_ini = sektionsLocalizedData.nimbleVersion;
                              // the other level's version have to be added
                              presetCandidate.collection = setVersion( presetCandidate.collection );
                              __dfd__.resolve( presetCandidate );
                        });//_maybeFetchSectionsFromServer.done()

                  return __dfd__.promise();
            },




            // Walk the column collection of a preset section, and replace '__img_url__*' pattern by image ids that we get from ajax calls
            // Is designed to handle multiple ajax calls in parallel if the preset_section includes several images
            // @return a promise()
            preparePresetSectionForInjection : function( columnCollection ) {
                var self = this,
                    deferreds = {},
                    preparedSection = {},
                    _dfd_ = $.Deferred();

                // items id of multi-items module must always be unique
                // this recursive method sniff and does the job
                self.maybeGenerateNewItemIdsForCrudModules( columnCollection );

                // walk the column collection and populates the deferreds object recursively
                var _sniffImg = function( data ) {
                      _.each( data, function( val, key ) {
                            if ( _.isObject( val ) || _.isArray( val ) ) {
                                  _sniffImg( val );
                            } else if ( _.isString( val ) && -1 != val.indexOf( '__img_url__' ) ) {
                                  // scenario when a section uses an image more than once.
                                  // => we don't need to fire a new ajax request for an image already sniffed
                                  if ( ! _.has( deferreds, val ) ) {
                                        deferreds[ val ] = self.importAttachment( val.replace( '__img_url__', '' ) );
                                  }
                            }
                      });
                      return deferreds;
                };

                // walk the column collection and populates the deferreds object recursively
                // imdList is formed this way :
                // __img_url__/assets/img/1.jpg : {id: 2547, url: "http://customizr-dev.test/wp-content/uploads/2018/09/nimble_asset_1.jpg"}
                // __img_url__/assets/img/2.jpg : {id: 2548, url: "http://customizr-dev.test/wp-content/uploads/2018/09/nimble_asset_2.jpg"}
                // __img_url__/assets/img/3.jpg : {id: 2549, url: "http://customizr-dev.test/wp-content/uploads/2018/09/nimble_asset_3.jpg"}
                var _replaceImgPlaceholderById = function( data, imgList) {
                      _.each( data, function( val, key ) {
                            if ( _.isObject( val ) || _.isArray( val ) ) {
                                  _replaceImgPlaceholderById( val, imgList );
                            } else if ( _.isString( val ) && -1 != val.indexOf( '__img_url__' ) && _.has( imgList, val ) && _.isObject( imgList[ val ] ) ) {
                                  data[ key ] = imgList[ val ].id;
                            }
                      });
                      return columnCollection;
                };

                self.whenAllPromisesInParallel( _sniffImg( columnCollection ) )
                    .done( function( imgList ) {
                          var imgReadySection = _replaceImgPlaceholderById( columnCollection, imgList );
                          _dfd_.resolve( imgReadySection );
                    })
                    .fail( function( _er_ ){
                          _dfd_.reject( _er_ );
                    });

                return _dfd_.promise();
            }
      });//$.extend()
})( wp.customize, jQuery );