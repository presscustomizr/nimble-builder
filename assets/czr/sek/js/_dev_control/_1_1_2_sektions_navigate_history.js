//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // Fired in ::initialize(), at api 'ready'
            // March 2019 : history log tracks local and global section settings
            // no tracking of the global option sektionsLocalizedData.optNameForGlobalOptions
            initializeHistoryLogWhenSettingsRegistered : function() {
                  var self = this;
                  // This api.Value() is bound in ::setupTopBar
                  self.historyLog = new api.Value([{
                        status : 'current',
                        value : {
                              'local' : api( self.localSectionsSettingId() )(),//<= "nimble___[skp__post_page_10]"
                              'global' : api(  self.getGlobalSectionsSettingId() )()
                        },
                        action : 'initial'
                  }]);
                  // LISTEN TO HISTORY LOG CHANGES AND UPDATE THE BUTTON STATE
                  self.historyLog.bind( function( newLog ) {
                        if ( _.isEmpty( newLog ) )
                          return;

                        var newCurrentKey = _.findKey( newLog, { status : 'current'} );
                        newCurrentKey = Number( newCurrentKey );
                        $( '#nimble-top-bar' ).find('[data-nimble-history]').each( function() {
                              if ( 'undo' === $(this).data('nimble-history') ) {
                                    $(this).attr('data-nimble-state', 0 >= newCurrentKey ? 'disabled' : 'enabled');
                              } else {
                                    $(this).attr('data-nimble-state', newLog.length <= ( newCurrentKey + 1 ) ? 'disabled' : 'enabled');
                              }
                        });
                  });
            },

            // React to a local or global setting change api( settingData.collectionSettingId )
            // =>populates self.historyLog() observable value
            // invoked in ::setupSettingsToBeSaved, if params.navigatingHistoryLogs !== true <=> not already navigating
            trackHistoryLog : function( sektionSetInstance, params ) {
                  var self = this,
                      _isGlobal = sektionSetInstance.id === self.getGlobalSectionsSettingId();

                  // Safety checks
                  // trackHistoryLog must be invoked with a try catch statement
                  if ( !_.isObject( params ) || !_.isFunction( self.historyLog ) || !_.isArray( self.historyLog() ) ) {
                        api.errare( 'params, self.historyLog() ', params, self.historyLog() );
                        throw new Error('trackHistoryLog => invalid params or historyLog value');
                  }

                  // Always clean future values if the logs have been previously navigated back
                  var newHistoryLog = [],
                      historyLog = $.extend( true, [], self.historyLog() ),
                      sektionToRefresh;

                  if ( ! _.isEmpty( params.in_sektion ) ) {//<= module changed, column resized, removed...
                        sektionToRefresh = params.in_sektion;
                  } else if ( ! _.isEmpty( params.to_sektion ) ) {// column moved /
                        sektionToRefresh = params.to_sektion;
                  }

                  // Reset all status but 'future' to 'previous'
                  _.each( historyLog, function( log ) {
                        var newStatus = 'previous';
                        if ( 'future' == log.status )
                          return;
                        $.extend( log, { status : 'previous' } );
                        newHistoryLog.push( log );
                  });
                  newHistoryLog.push({
                        status : 'current',
                        value : _isGlobal ? { global : sektionSetInstance() } : { local : sektionSetInstance() },
                        action : _.isObject( params ) ? ( params.action || '' ) : '',
                        sektionToRefresh : sektionToRefresh
                  });
                  self.historyLog( newHistoryLog );
            },



            // @param direction = string 'undo', 'redo'
            // @return void()
            // Fired on click in the topbar or when hitting ctrl z / y
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
                  if( !_.isUndefined( newSettingValue ) ) {
                        if ( !_.isEmpty( newSettingValue.local ) ) {
                              api( self.localSectionsSettingId() )( self.validateSettingValue( newSettingValue.local, 'local' ), { navigatingHistoryLogs : true } );

                              // Clean and regenerate the local option setting
                              // Note that we also do it after a local import, tmpl injection or local reset
                              //
                              // Settings are normally registered once and never cleaned, unlike controls.
                              // Updating the setting value will refresh the sections
                              // but the local options, persisted in separate settings, won't be updated if the settings are not cleaned
                              // Example of local setting id :
                              // __nimble__skp__post_page_2__localSkopeOptions__template
                              // or
                              // __nimble__skp__home__localSkopeOptions__custom_css
                              api.czr_sektions.generateUI({
                                    action : 'sek-generate-local-skope-options-ui',
                                    clean_settings_and_controls_first : true//<= see api.czr_sektions.generateUIforLocalSkopeOptions()
                              });
                        }
                        if ( !_.isEmpty( newSettingValue.global ) ) {
                              api( self.getGlobalSectionsSettingId() )( self.validateSettingValue( newSettingValue.global, 'global' ), { navigatingHistoryLogs : true } );
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

                        // Clean registered control
                        self.cleanRegisteredAndLargeSelectInput();//<= normal cleaning
                        // Clean even the level settings
                        // => otherwise the level settings won't be synchronized when regenerating their ui.
                        self.cleanRegisteredLevelSettings();// setting cleaning
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
