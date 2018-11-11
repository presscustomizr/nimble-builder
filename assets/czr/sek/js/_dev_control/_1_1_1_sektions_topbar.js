//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // TOP BAR
            // fired in ::initialize()
            setupTopBar : function() {
                  var self = this;
                  self.topBarVisible = new api.Value( false );
                  self.topBarVisible.bind( function( visible ){
                        self.toggleTopBar( visible );
                  });

                  self.mouseMovedRecently = new api.Value( {} );
                  self.mouseMovedRecently.bind( function( position ) {
                        self.topBarVisible( ! _.isEmpty( position ) );
                  });

                  var trackMouseMovements = function( evt ) {
                        self.mouseMovedRecently( { x : evt.clientX, y : evt.clientY } );
                        clearTimeout( $(window).data('_scroll_move_timer_') );
                        $(window).data('_scroll_move_timer_', setTimeout(function() {
                              self.mouseMovedRecently.set( {} );
                        }, 4000 ) );
                  };
                  $(window).on( 'mousemove scroll,', _.throttle( trackMouseMovements , 50 ) );
                  api.previewer.bind('ready', function() {
                        $(api.previewer.targetWindow().document ).on( 'mousemove scroll,', _.throttle( trackMouseMovements , 50 ) );
                  });
                  self.historyLog = new api.Value([]);
                  // LISTEN TO HISTORY LOG CHANGES TO UPDATE THE BUTTON STATE
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


            // @return void()
            // self.topBarVisible.bind( function( visible ){
            //       self.toggleTopBar( visible );
            // });
            toggleTopBar : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndSetupTopBarTmpl({}) ).done( function( $_el ) {
                                  self.topBarContainer = $_el;
                                  //display
                                  _.delay( function() {
                                      $('body').addClass('nimble-top-bar-visible');
                                  }, 200 );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            $('body').removeClass('nimble-top-bar-visible');
                            if ( self.topBarContainer && self.topBarContainer.length ) {
                                  //remove Dom element after slide up
                                  _.delay( function() {
                                        //self.topBarContainer.remove();
                                        dfd.resolve();
                                  }, 300 );
                            } else {
                                dfd.resolve();
                            }
                            return dfd.promise();
                      };

                  if ( visible ) {
                        _renderAndSetup();
                  } else {
                        _hide().done( function() {
                              self.topBarVisible( false );//should be already false
                        });
                  }
            },


            //@param = { }
            renderAndSetupTopBarTmpl : function( params ) {
                  if ( $( '#nimble-top-bar' ).length > 0 )
                    return $( '#nimble-top-bar' );

                  var self = this;

                  try {
                        _tmpl =  wp.template( 'nimble-top-bar' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing the the top note template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );

                  // Attach click events
                  $('[data-nimble-history]', '#nimble-top-bar').on( 'click', function(evt) {
                        try { self.navigateHistory( $(this).data( 'nimble-history') ); } catch( er ) {
                              api.errare( 'Error when firing self.navigateHistory', er );
                        }
                  });
                  $('.sek-settings', '#nimble-top-bar').on( 'click', function(evt) {
                        // Focus on the Nimble panel
                        api.panel( sektionsLocalizedData.sektionsPanelId, function( _panel_ ) {
                              self.rootPanelFocus();
                              _panel_.focus();
                        });
                        // // Generate UI for the local skope options
                        // self.generateUI({ action : 'sek-generate-local-skope-options-ui'}).done( function() {
                        //       api.control( self.getLocalSkopeOptionId(), function( _control_ ) {
                        //             _control_.focus();
                        //       });
                        // });
                  });
                  $('.sek-add-content', '#nimble-top-bar').on( 'click', function(evt) {
                        evt.preventDefault();
                        api.previewer.trigger( 'sek-pick-content', {});
                  });
                  $('.sek-nimble-doc', '#nimble-top-bar').on( 'click', function(evt) {
                        evt.preventDefault();
                        window.open($(this).data('doc-href'), '_blank');
                  });
                  return $( '#nimble-top-bar' );
            },


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
                        api( self.localSectionsSettingId() )( self.validateSettingValue( newSettingValue ), { navigatingHistoryLogs : true } );
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
