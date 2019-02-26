//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            /* HISTORY */
            // @param direction = string 'undo', 'redo'
            // @return void()
            navigateHistory : function( direction ) {
                  var self = this,
                      historyLog = $.extend( true, [], self.historyLog() );
                  // log model
                  // {
                  //       status : 'current', 'previous', 'future'
                  //       value : {},
                  //       action : 'sek-add-column'
                  // }

                  // UPDATE THE SETTING VALUE
                  var previous,
                      current,
                      future,
                      newHistoryLog = [],
                      newSettingValue,
                      previousSektionToRefresh,
                      currentSektionToRefresh;

                  _.each( historyLog, function( log ) {
                        if ( ! _.isEmpty( newSettingValue ) ) {
                              return;
                        }
                        switch( log.status ) {
                              case 'previous' :
                                    previous = log;
                              break;
                              case 'current' :
                                    current = log;
                              break;
                              case 'future' :
                                    future = log;
                              break;
                        }
                        switch( direction ) {
                              case 'undo' :
                                    // the last previous is our new setting value
                                    if ( ! _.isEmpty( current ) && ! _.isEmpty( previous ) ) {
                                          newSettingValue = previous.value;
                                          previousSektionToRefresh = current.sektionToRefresh;
                                          currentSektionToRefresh = previous.sektionToRefresh;
                                    }
                              break;
                              case 'redo' :
                                    // the first future is our new setting value
                                    if ( ! _.isEmpty( future ) ) {
                                          newSettingValue = future.value;
                                          previousSektionToRefresh = current.sektionToRefresh;
                                          currentSektionToRefresh = future.sektionToRefresh;
                                    }
                              break;
                        }
                  });

                  // set the new setting Value
                  if( ! _.isUndefined( newSettingValue ) ) {
                        if ( ! _.isEmpty( newSettingValue.local ) ) {
                              api( self.localSectionsSettingId() )( self.validateSettingValue( newSettingValue.local ), { navigatingHistoryLogs : true } );
                        }
                        if ( ! _.isEmpty( newSettingValue.global ) ) {
                              api( self.getGlobalSectionsSettingId() )( self.validateSettingValue( newSettingValue.global ), { navigatingHistoryLogs : true } );
                        }
                        // If the information is available, refresh only the relevant sections
                        // otherwise fallback on a full refresh
                        var previewHasBeenRefreshed = false;

                        // if ( ! _.isEmpty( previousSektionToRefresh ) ) {
                        //       api.previewer.trigger( 'sek-refresh-level', {
                        //             level : 'section',
                        //             id : previousSektionToRefresh
                        //       });
                        // } else {
                        //       api.previewer.refresh();
                        //       previewHasBeenRefreshed = true;
                        // }
                        // if ( currentSektionToRefresh != previousSektionToRefresh ) {
                        //     if ( ! _.isEmpty( currentSektionToRefresh ) ) {
                        //           api.previewer.trigger( 'sek-refresh-level', {
                        //                 level : 'section',
                        //                 id : currentSektionToRefresh
                        //           });
                        //     } else if ( ! previewHasBeenRefreshed ) {
                        //           api.previewer.refresh();
                        //     }
                        // }
                        api.previewer.refresh();

                        // Always make sure that the ui gets refreshed
                        api.previewer.trigger( 'sek-pick-content', {});
                        // Clean registered setting and control, even the level settings
                        // => otherwise the level settings won't be synchronized when regenerating their ui.
                        self.cleanRegistered();//<= normal cleaning
                        self.cleanRegisteredLevelSettingsAfterHistoryNavigation();// setting cleaning
                  }

                  // UPDATE THE HISTORY LOG
                  var currentKey = _.findKey( historyLog, { status : 'current'} );
                  currentKey = Number( currentKey );
                  if ( ! _.isNumber( currentKey ) ) {
                        api.errare( 'Error when navigating the history log, the current key should be a number');
                        return;
                  }

                  _.each( historyLog, function( log, key ) {
                        newLog = $.extend( true, {}, log );
                        // cast keys to number so we can compare them
                        key = Number( key );
                        switch( direction ) {
                              case 'undo' :
                                    if ( 0 < currentKey ) {
                                          if ( key === ( currentKey - 1 ) ) {
                                                newLog.status = 'current';
                                          } else if ( key === currentKey ) {
                                                newLog.status = 'future';
                                          }
                                    }
                              break;
                              case 'redo' :
                                    if ( historyLog.length > ( currentKey + 1 ) ) {
                                          if ( key === currentKey ) {
                                                newLog.status = 'previous';
                                          } else if ( key === ( currentKey + 1 ) ) {
                                                newLog.status = 'current';
                                          }
                                    }
                              break;
                        }
                        newHistoryLog.push( newLog );
                  });
                  self.historyLog( newHistoryLog );
            }
      });//$.extend()
})( wp.customize, jQuery );
