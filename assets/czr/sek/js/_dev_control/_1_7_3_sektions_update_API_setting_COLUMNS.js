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
            _updAPISet_sek_add_column :  function() {
                  var self = this,
                      params,
                      sektionCandidate;

                  params = self.updAPISetParams.params;

                  // an id must be provided
                  if ( _.isEmpty( params.id ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                  }
                  sektionCandidate = self.getLevelModel( params.in_sektion, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' == sektionCandidate ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                        return;
                  }

                  sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                  // can we add another column ?
                  if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                        self.updAPISetParams.promise.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                        return;
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
            },

            _updAPISet_sek_remove_column :  function() {
                  var self = this,
                      params,
                      sektionCandidate;

                  params = self.updAPISetParams.params;

                  sektionCandidate = self.getLevelModel( params.in_sektion, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' != sektionCandidate ) {
                        // can we remove the column ?
                        if ( 1 === _.size( sektionCandidate.collection ) ) {
                              self.updAPISetParams.promise.reject( sektionsLocalizedData.i18n["A section must have at least one column."]);
                              return;
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
            },

            _updAPISet_sek_duplicate_column :  function() {
                  var self = this,
                      params,
                      sektionCandidate;

                  params = self.updAPISetParams.params;

                  // an id must be provided
                  if ( _.isEmpty( params.id ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                  }

                  sektionCandidate = self.getLevelModel( params.in_sektion, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' == sektionCandidate ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                        return;
                  }

                  sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                  // can we add another column ?
                  if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                        self.updAPISetParams.promise.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                        return;
                  }

                  var deepClonedColumn;
                  try { deepClonedColumn = self.cloneLevel( params.id ); } catch( er ) {
                        api.errare( 'updateAPISetting => ' + params.action, er );
                        return;
                  }

                  // items id of multi-items module must always be unique
                  // this recursive method sniff and does the job
                  self.maybeGenerateNewItemIdsForCrudModules( deepClonedColumn );

                  var _position = self.getLevelPositionInCollection( params.id, self.updAPISetParams.newSetValue.collection );
                  self.updAPISetParams.cloneId = deepClonedColumn.id;//will be passed in resolve()
                  sektionCandidate.collection.splice( parseInt( _position + 1, 10 ), 0, deepClonedColumn );
                  // RESET ALL COLUMNS WIDTH
                  // _.each( sektionCandidate.collection, function( colModel ) {
                  //       colModel.width = '';
                  // });
                  self.resetColumnsWidthInSection( sektionCandidate );
            },

            _updAPISet_sek_resize_column :  function() {
                  var self = this,
                      params;

                  params = self.updAPISetParams.params;

                  if ( params.col_number < 2 )
                    return;

                  var resizedColumn = self.getLevelModel( params.resized_column, self.updAPISetParams.newSetValue.collection ),
                      sistercolumn = self.getLevelModel( params.sister_column, self.updAPISetParams.newSetValue.collection );

                  //api.infoLog( 'updateAPISetting => ' + params.action + ' => ', params );

                  // SET RESIZED COLUMN WIDTH
                  if ( 'no_match' == resizedColumn ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no resized column matched' );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no resized column matched');
                        return;
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
                        // april 2020 : due to a previous implementation of width option as an array, there could be a bug when trying to set the width
                        // because the check on _.isObject() was true for an array
                        // Fixes https://github.com/presscustomizr/nimble-builder/issues/620
                        _new_options_values.width = ( _.isObject( _new_options_values.width ) && _new_options_values.width['custom-width'] ) ? _new_options_values.width : {};
                        _new_options_values.width['custom-width'] = newWidthValue;
                        _candidate_.options = _new_options_values;

                        // Live update the input value ( when rendered )
                        self.cachedElements.$body.find('[data-sek-width-range-column-id="'+ _candidate_.id +'"]').val( newWidthValue ).trigger('input', { is_resize_column_trigger : true } );
                        return newWidthValue;
                  };

                  // DEPRECATED SINCE JUNE 2019 => resizedColumn.width = parseFloat( params.resizedColumnWidthInPercent );
                  var resizedColumnWidthInPercent = _setColumnWidth( resizedColumn, parseFloat( params.resizedColumnWidthInPercent ) );
                  // cast to number
                  resizedColumnWidthInPercent = parseFloat( resizedColumnWidthInPercent );

                  // SET OTHER COLUMNS WIDTH
                  var parentSektion = self.getLevelModel( params.in_sektion, self.updAPISetParams.newSetValue.collection );
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
            },

            _updAPISet_sek_move_column :  function() {
                  var self = this,
                      params,
                      sektionCandidate;

                  params = self.updAPISetParams.params;

                  var toSektionCandidate = self.getLevelModel( params.to_sektion, self.updAPISetParams.newSetValue.collection ),
                      movedColumnCandidate,
                      copyOfMovedColumnCandidate;

                  if ( _.isEmpty( toSektionCandidate ) || 'no_match' == toSektionCandidate ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing target sektion' );
                  }

                  if ( params.from_sektion != params.to_sektion ) {
                        // Remove the moved column from the source sektion
                        var fromSektionCandidate = self.getLevelModel( params.from_sektion, self.updAPISetParams.newSetValue.collection );
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
            },

      });//$.extend()
})( wp.customize, jQuery );