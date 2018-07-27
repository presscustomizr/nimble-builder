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
                      dfd = $.Deferred();

                  // Update the sektion collection
                  api( self.sekCollectionSettingId(), function( sektionSetInstance ) {
                        // sektionSetInstance() = {
                        //    collection : [
                        //       'loop_start' :  { level : location,  collection : [ 'sek124' : { collection : [], level : section, options : {} }], options : {}},
                        //       'loop_end' : { level : location, collection : [], options : {}}
                        //        ...
                        //    ],
                        //    options : {}
                        //
                        // }
                        var currentSetValue = sektionSetInstance(),
                            newSetValue = _.isObject( currentSetValue ) ? $.extend( true, {}, currentSetValue ) : self.defaultSektionSettingValue,
                            locationCandidate,
                            sektionCandidate,
                            columnCandidate,
                            moduleCandidate,
                            // move variables
                            originalCollection,
                            reorderedCollection,
                            //duplication variable
                            cloneId, //will be passed in resolve()
                            startingModuleValue;// will be populated by the optional starting value specificied on module registration

                        // make sure we have a collection array to populate
                        newSetValue.collection = _.isArray( newSetValue.collection ) ? newSetValue.collection : self.defaultSektionSettingValue.collection;

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
                                          var parentSektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                          if ( 'no_match' == parentSektionCandidate ) {
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                                break;
                                          }
                                          if ( true === parentSektionCandidate.is_nested ) {
                                                dfd.reject( sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ]);
                                                break;
                                          }
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.push({
                                                id : params.id,
                                                level : 'section',
                                                collection : [{
                                                      id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                      level : 'column',
                                                      collection : []
                                                }],
                                                is_nested : true
                                          });
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
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
                                                      collection : []
                                                }]
                                          });
                                    }
                              break;


                              case 'sek-duplicate-section' :
                                    //console.log('PARAMS IN sek-duplicate-section', params );
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

                                    var _position_ = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    // Is this a nested sektion ?
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }

                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );


                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
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
                                    //console.log('PARAMS IN sek-remove-sektion', params );
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
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.filter( locationCandidate.collection, function( sek ) {
                                                return sek.id != params.id;
                                          });
                                    }
                              break;

                              case 'sek-move-section' :
                                    //console.log('PARAMS in sek-move-section', params );
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
                                    toLocationCandidate.collection =  _.isArray( toLocationCandidate.collection ) ? toLocationCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toLocationCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          // in the case of a cross location movement, we need to add the moved sektion to the target location
                                          if ( params.from_location != params.to_location && _id_ == copyOfMovedSektionCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedSektionCandidate );
                                          } else {
                                                sektionCandidate = self.getLevelModel( _id_, originalCollection );
                                                if ( _.isEmpty( sektionCandidate ) || 'no_match' == sektionCandidate ) {
                                                      throw new Error( 'updateAPISetting => move section => missing section candidate' );
                                                }
                                                reorderedCollection.push( sektionCandidate );
                                          }
                                    });
                                    toLocationCandidate.collection = reorderedCollection;

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
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    // can we add another column ?
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          dfd.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }

                                    // RESET ALL COLUMNS WIDTH
                                    _.each( sektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });
                                    sektionCandidate.collection.push({
                                          id :  params.id,
                                          level : 'column',
                                          collection : []
                                    });
                              break;


                              case 'sek-remove-column' :
                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' != sektionCandidate ) {
                                          // can we remove the column ?
                                          if ( 1 === _.size( sektionCandidate.collection ) ) {
                                                dfd.reject( sektionsLocalizedData.i18n["A section must have at least one column."]);
                                                break;
                                          }
                                          sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                          sektionCandidate.collection = _.filter( sektionCandidate.collection, function( column ) {
                                                return column.id != params.id;
                                          });
                                          // RESET ALL COLUMNS WIDTH
                                          _.each( sektionCandidate.collection, function( colModel ) {
                                                colModel.width = '';
                                          });
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
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    // can we add another column ?
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          dfd.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }

                                    var deepClonedColumn;
                                    try { deepClonedColumn = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          break;
                                    }
                                    var _position = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    cloneId = deepClonedColumn.id;//will be passed in resolve()
                                    sektionCandidate.collection.splice( parseInt( _position + 1, 10 ), 0, deepClonedColumn );
                                    // RESET ALL COLUMNS WIDTH
                                    _.each( sektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });
                              break;



                              case 'sek-resize-columns' :
                                    if ( params.col_number < 2 )
                                      break;

                                    var resizedColumn = self.getLevelModel( params.resized_column, newSetValue.collection ),
                                        sistercolumn = self.getLevelModel( params.sister_column, newSetValue.collection );

                                    //console.log( 'updateAPISetting => ' + params.action + ' => ', params );

                                    // SET RESIZED COLUMN WIDTH
                                    if ( 'no_match' == resizedColumn ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no resized column matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no resized column matched');
                                          break;
                                    }

                                    resizedColumn.width = parseFloat( params.resizedColumnWidthInPercent );


                                    // SET OTHER COLUMNS WIDTH
                                    var parentSektion = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    var otherColumns = _.filter( parentSektion.collection, function( _col_ ) {
                                              return _col_.id != resizedColumn.id && _col_.id != sistercolumn.id;
                                        });
                                    var otherColumnsWidth = parseFloat( resizedColumn.width.toFixed(3) );

                                    if ( ! _.isEmpty( otherColumns ) ) {
                                         _.each( otherColumns, function( colModel ) {
                                                currentColWidth = parseFloat( colModel.width * 1 );
                                                if ( ! _.has( colModel, 'width') || ! _.isNumber( currentColWidth * 1 ) || _.isEmpty( currentColWidth + '' ) || 1 > currentColWidth ) {
                                                      colModel.width = parseFloat( ( 100 / params.col_number ).toFixed(3) );
                                                }
                                                // sum up all other column's width, excluding the resized and sister one.
                                                otherColumnsWidth = parseFloat( ( otherColumnsWidth  +  colModel.width ).toFixed(3) );
                                          });
                                    }


                                    // SET SISTER COLUMN WIDTH

                                    // sum up all other column's width, excluding the resized and sister one.
                                    // console.log( "resizedColumn.width", resizedColumn.width  );
                                    // console.log( "otherColumns", otherColumns );

                                    // then calculate the sistercolumn so we are sure that we feel the entire space of the sektion
                                    sistercolumn.width = parseFloat( ( 100 - otherColumnsWidth ).toFixed(3) );

                                    // console.log('otherColumnsWidth', otherColumnsWidth );
                                    // console.log("sistercolumn.width", sistercolumn.width );
                                    // console.log( "sistercolumn.width + otherColumnsWidth" , Number( sistercolumn.width ) + Number( otherColumnsWidth ) );
                                    //console.log('COLLECTION AFTER UPDATE ', parentSektion.collection );
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
                                          _.each( fromSektionCandidate.collection, function( colModel ) {
                                                colModel.width = '';
                                          });
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
                                    _.each( toSektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });

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
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
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
                                          module_type : params.module_type
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
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                    var deepClonedModule;
                                    try { deepClonedModule = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => error when cloning the level');
                                          break;
                                    }
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
                                    var _value_ = {};
                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _value_[ _key_ ] = _val_;
                                    });
                                    if ( 'no_match' == moduleCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no module matched', params );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => error no module matched');
                                          break;
                                    }
                                    moduleCandidate.value = _value_;
                              break;






                              //-------------------------------------------------------------------------------------------------
                              //-- LEVEL OPTIONS
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-generate-level-options-ui' :
                                    var _candidate_ = self.getLevelModel( params.id, newSetValue.collection ),
                                        _valueCandidate = {};
                                    if ( 'no_match'=== _candidate_ ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }
                                    _candidate_.options = _candidate_.options || {};

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
                                    _candidate_.options[ params.options_type ] = _valueCandidate;
                              break;





                              //-------------------------------------------------------------------------------------------------
                              //-- LOCAL SKOPE OPTIONS
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-generate-local-skope-options-ui' :
                                    _valueCandidate = {};

                                    var _currentOptions = $.extend( true, {}, _.isObject( newSetValue.options ) ? newSetValue.options : {} );
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
                                    switch( params.options_type ) {
                                          case 'general' :
                                                newSetValue.options = $.extend( _currentOptions, { general : _valueCandidate } );
                                          break;

                                          default :
                                                api.errare( 'updateAPISetting => ' + params.action + 'unknown options_type param ' + options_type );
                                          break;
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
                                    // console.log('update API Setting => sek-add-content-in-new-sektion => PARAMS', params );
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    // get the position of the before or after section
                                    position = 0;
                                    locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                    if ( 'no_match' == locationCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
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
                                                                              value : 'no_starting_value' !== startingModuleValue ? startingModuleValue : null
                                                                        }
                                                                  ]
                                                            }
                                                      ]
                                                });
                                          break;

                                          // When a preset section is dropped
                                          case 'preset_section' :
                                                // insert the section in the collection at the right place
                                                var presetColumnCollection;
                                                try { presetColumnCollection = self.getPresetSectionCollection({
                                                            presetSectionType : params.content_id,
                                                            section_id : params.id//<= we need to use the section id already generated, and passed for ajax action @see ::reactToPreviewMsg, case "sek-add-section"
                                                      });
                                                } catch( _er_ ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                      dfd.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                                      break;
                                                }
                                                if ( ! _.isObject( presetColumnCollection ) || _.isEmpty( presetColumnCollection ) ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnCollection );
                                                      dfd.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                      break;
                                                }
                                                locationCandidate.collection.splice( position, 0, {
                                                      id : params.id,
                                                      level : 'section',
                                                      collection : presetColumnCollection.collection
                                                });
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
                              case 'sek-add-content-in-new-nested-sektion' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );

                                    // can we add this nested sektion ?
                                    // if the parent sektion of the column has is_nested = true, then we can't
                                    var parentSektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == parentSektionCandidate ) {
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                          break;
                                    }
                                    if ( true === parentSektionCandidate.is_nested ) {
                                          dfd.reject( sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ]);
                                          break;
                                    }
                                    if ( 'no_match' == columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }
                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                    // insert the section in the collection at the right place
                                    var presetColumnCollection;
                                    try { presetColumnCollection = self.getPresetSectionCollection({
                                                presetSectionType : params.content_id,
                                                section_id : params.id//<= we need to use the section id already generated, and passed for ajax action @see ::reactToPreviewMsg, case "sek-add-section"
                                          });
                                    } catch( _er_ ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                          break;
                                    }
                                    if ( ! _.isObject( presetColumnCollection ) || _.isEmpty( presetColumnCollection ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnCollection );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                          break;
                                    }
                                    columnCandidate.collection.push({
                                          id : params.id,
                                          level : 'section',
                                          collection : presetColumnCollection.collection,
                                          is_nested : true
                                    });
                              break;












                              //-------------------------------------------------------------------------------------------------
                              //-- POPULATE GOOGLE FONTS
                              //-------------------------------------------------------------------------------------------------
                              //@params {
                              //       action : 'sek-update-fonts',
                              //       font_family : newFontFamily,
                              // }
                              case 'sek-update-fonts' :
                                    //console.log('PARAMS in sek-add-fonts', params );
                                    // Get the gfonts from the level options and modules values
                                    var currentGfonts = self.sniffGFonts();
                                    if ( ! _.isEmpty( params.font_family ) && _.isString( params.font_family ) && ! _.contains( currentGfonts, params.font_family ) ) {
                                          if ( params.font_family.indexOf('gfont') < 0 ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont');
                                                break;
                                          }
                                          currentGfonts.push( params.font_family );
                                    }
                                    // update the global gfonts collection
                                    // this is then used server side in Sek_Dyn_CSS_Handler::sek_get_gfont_print_candidates to build the Google Fonts request
                                    newSetValue.fonts = currentGfonts;
                              break;
                        }// switch







                        // if we did not already rejected the request, let's check if the setting object has actually been modified
                        // at this point it should have been.
                        if ( 'pending' == dfd.state() ) {
                              //console.log('ALORS ?', currentSetValue, newSetValue );
                              if ( _.isEqual( currentSetValue, newSetValue ) ) {
                                    dfd.reject( 'updateAPISetting => the new setting value is unchanged when firing action : ' + params.action );
                              } else {
                                    if ( null !== self.validateSettingValue( newSetValue ) ) {
                                          sektionSetInstance( newSetValue, params );
                                          dfd.resolve( cloneId );// the cloneId is only needed in the duplication scenarii
                                    } else {
                                          dfd.reject( 'updateAPISetting => the new setting value did not pass the validation checks for action ' + params.action );
                                    }

                                    //console.log('COLLECTION SETTING UPDATED => ', self.sekCollectionSettingId(), api( self.sekCollectionSettingId() )() );

                              }
                        }
                  });//api( self.sekCollectionSettingId(), function( sektionSetInstance ) {}
                  return dfd.promise();
            },//updateAPISetting


            // @return a JSON parsed string,
            // + guid() ids for each levels
            // ready for insertion
            //
            // @sectionParams : {
            //       presetSectionType : params.content_id,
            //       section_id : params.id
            // }
            // Why is the section_id provided ?
            // Because this id has been generated ::reactToPreviewMsg, case "sek-add-section", and is the identifier that we'll need when ajaxing ( $_POST['id'])
            getPresetSectionCollection : function( sectionParams ) {
                  var self = this,
                      presetSection,
                      allPresets = $.extend( true, {}, sektionsLocalizedData.presetSections );

                  if ( ! _.isObject( allPresets ) || _.isEmpty( allPresets ) ) {
                        throw new Error( 'getPresetSectionCollection => Invalid sektionsLocalizedData.presetSections');
                  }
                  if ( _.isEmpty( allPresets[ sectionParams.presetSectionType ] ) ) {
                        throw new Error( 'getPresetSectionCollection => ' + sectionParams.presetSectionType + ' has not been found in sektionsLocalizedData.presetSections');
                  }
                  var presetCandidate = allPresets[ sectionParams.presetSectionType ];
                  // Ensure we have a string that's JSON.parse-able
                  if ( typeof presetCandidate !== 'string' || presetCandidate[0] !== '{' ) {
                        throw new Error( 'getPresetSectionCollection => ' + sectionParams.presetSectionType + ' is not JSON.parse-able');
                  }
                  presetCandidate = JSON.parse( presetCandidate );
                  var setIds = function( collection ) {
                        _.each( collection, function( levelData ) {
                              levelData.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                              if ( _.isArray( levelData.collection ) ) {
                                    setIds( levelData.collection );
                              }
                        });
                        return collection;
                  };

                  // set the section id provided.
                  presetCandidate.id = sectionParams.section_id;

                  // the other level's id have to be generated
                  presetCandidate.collection = setIds( presetCandidate.collection );
                  return presetCandidate;
            }
      });//$.extend()
})( wp.customize, jQuery );