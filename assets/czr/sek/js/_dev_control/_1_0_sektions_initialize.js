//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {

            initialize: function() {
                  var self = this;
                  if ( _.isUndefined( window.sektionsLocalizedData ) ) {
                        throw new Error( 'CZRSeksPrototype => missing localized server params sektionsLocalizedData' );
                  }
                  // this class is skope dependant
                  if ( ! _.isFunction( api.czr_activeSkopes ) ) {
                        throw new Error( 'CZRSeksPrototype => api.czr_activeSkopes' );
                  }
                  // Max possible number of columns in a section
                  self.MAX_NUMBER_OF_COLUMNS = 12;

                  // _.debounce param when updating the UI setting
                  // prevent hammering server
                  self.SETTING_UPDATE_BUFFER = 50;

                  // Define a default value for the sektion setting value, used when no server value has been sent
                  // @see php function
                  // function sek_get_default_sektions_value() {
                  //     $defaut_sektions_value = [ 'collection' => [], 'options' => [] ];
                  //     foreach( sek_get_locations() as $location ) {
                  //         $defaut_sektions_value['collection'][] = [
                  //             'id' => $location,
                  //             'level' => 'location',
                  //             'collection' => [],
                  //             'options' => []
                  //         ];
                  //     }
                  //     return $defaut_sektions_value;
                  // }
                  self.defaultSektionSettingValue = sektionsLocalizedData.defaultSektionSettingValue;

                  // Store the contextual setting prefix
                  self.sekCollectionSettingId = new api.Value( {} );

                  // Keep track of the registered ui elements dynamically registered
                  // this collection is populated in ::register(), if the track param is true
                  // this is used to know what ui elements are currently being displayed
                  self.registered = new api.Value([]);

                  api.bind( 'ready', function() {
                        // the main sektion panel
                        self.registerAndSetupDefaultPanelSectionOptions();

                        // Setup the collection setting => register the main setting and bind it
                        // schedule reaction to collection setting ids => the setup of the collection setting when the collection setting ids are set
                        //=> on skope change
                        //@see setContextualCollectionSettingIdWhenSkopeSet
                        self.sekCollectionSettingId.callbacks.add( function( collectionSettingIds, previousCollectionSettingIds ) {
                              // register the collection setting id
                              // and schedule the reaction to different collection changes : refreshModules, ...
                              try { self.setupSettingToBeSaved(); } catch( er ) {
                                    api.errare( 'Error in self.sekCollectionSettingId.callbacks => self.setupSettingsToBeSaved()' , er );
                              }
                        });

                        // POPULATE THE MAIN SETTING ID NOW
                        // + GENERATE UI FOR THE LOCAL SKOPE OPTIONS
                        // populate the settingids now if skopes are set
                        if ( ! _.isEmpty( api.czr_activeSkopes().local ) ) {
                              self.setContextualCollectionSettingIdWhenSkopeSet();
                              // Generate UI for the local skope options
                              self.generateUI({ action : 'sek-generate-local-skope-options-ui'});
                        }

                        // ON SKOPE READY
                        // - Set the contextual setting prefix
                        // - Generate UI for Nimble local skope options
                        // - Generate the content picker
                        api.czr_activeSkopes.callbacks.add( function( newSkopes, previousSkopes ) {
                              self.setContextualCollectionSettingIdWhenSkopeSet( newSkopes, previousSkopes );
                              // Generate UI for the local skope options
                              self.generateUI({ action : 'sek-generate-local-skope-options-ui'});

                              // Generate the content picker
                              api.previewer.trigger( 'sek-pick-module', { focus : false });

                        });


                        // Communicate with the preview
                        self.reactToPreviewMsg();

                        // Setup Dnd
                        self.setupDnd();


                        // setup the tinyMce editor used for the tiny_mce_editor input
                        // => one object listened to by each tiny_mce_editor input
                        self.setupTinyMceEditor();

                        // print json
                        self.schedulePrintSectionJson();

                        // Always set the previewed device back to desktop on ui change
                        // event 'sek-ui-removed' id triggered when cleaning the registered ui controls
                        // @see ::cleanRegistered()
                        self.bind( 'sek-ui-removed', function() {
                              api.previewedDevice( 'desktop' );
                        });

                        // Synchronize api.previewedDevice with the currently rendered ui
                        // ensure that the selected device tab of the spacing module is the one being previewed
                        // =>@see spacing module, in item constructor CZRSpacingItemMths
                        api.previewedDevice.bind( function( device ) {
                              var currentControls = _.filter( self.registered(), function( uiData ) {
                                    return 'control' == uiData.what;
                              });
                              _.each( currentControls || [] , function( ctrlData ) {
                                    api.control( ctrlData.id, function( _ctrl_ ) {
                                          _ctrl_.container.find('[data-sek-device="' + device + '"]').each( function() {
                                                $(this).trigger('click');
                                          });
                                    });
                              });
                        });

                        // Schedule a reset
                        $('#customize-notifications-area').on( 'click', '[data-sek-reset="true"]', function() {
                              self.resetCollectionSetting();
                        });


                        // CLEAN UI BEFORE REMOVAL
                        // 'sek-ui-pre-removal' is triggered in ::cleanRegistered
                        // @params { what : control, id : '' }
                        self.bind( 'sek-ui-pre-removal', function( params ) {
                              // CLEAN DRAG N DROP
                              if ( 'control' == params.what && -1 < params.id.indexOf( 'draggable') ) {
                                    api.control( params.id, function( _ctrl_ ) {
                                          _ctrl_.container.find( '[draggable]' ).each( function() {
                                                $(this).off( 'dragstart dragend' );
                                          });
                                    });
                              }

                              // CLEAN SELECT2
                              // => we need to destroy the select2 instance, otherwise it can stay open when switching to another ui.
                              if ( 'control' == params.what ) {
                                    api.control( params.id, function( _ctrl_ ) {
                                          _ctrl_.container.find( 'select' ).each( function() {
                                                if ( ! _.isUndefined( $(this).data('select2') ) ) {
                                                      $(this).select2('destroy');
                                                }
                                          });
                                    });
                              }
                        });

                        // TEST
                        // @see php wp_ajax_sek_import_attachment
                        // wp.ajax.post( 'sek_import_attachment', {
                        //       rel_path : '/assets/img/41883.jpg'
                        // }).done( function( data) {
                        //       console.log('DATA', data );
                        // }).fail( function( _er_ ) {
                        //       api.errare( 'sek_import_attachment ajax action failed', _er_ );
                        // });

                        // POPULATE THE REGISTERED COLLECTION
                        // 'czr-new-registered' is fired in api.CZR_Helpers.register()
                        api.bind( 'czr-new-registered', function( params ) {
                              //console.log( 'czr-new-registered => ', params );
                              // Check that we have an origin property and that make sure we populate only the registration emitted by 'nimble'
                              if ( _.isUndefined( params.origin ) ) {
                                    throw new Error( 'czr-new-registered event => missing params.origin' );
                              }
                              if ( 'nimble' !== params.origin )
                                return;

                              // when no collection is provided, we use
                              if ( false !== params.track ) {
                                    var currentlyRegistered = self.registered();
                                    var newRegistered = $.extend( true, [], currentlyRegistered );
                                    //Check for duplicates
                                    var duplicateCandidate = _.findWhere( newRegistered, { id : params.id } );
                                    if ( ! _.isEmpty( duplicateCandidate ) && _.isEqual( duplicateCandidate, params ) ) {
                                          throw new Error( 'register => duplicated element in self.registered() collection ' + params.id );
                                    }
                                    newRegistered.push( params );
                                    self.registered( newRegistered );

                                    // say it
                                    //this.trigger( [params.what, params.id , 'registered' ].join('__'), params );
                              }
                        });


                        // TOP BAR
                        // Setup the topbar including do/undo action buttons
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

                  });//api.bind( 'ready' )

            },// initialize()








            // MAYBE REGISTER THE ADD NEW PANEL
            // Fired in initialize()
            registerAndSetupDefaultPanelSectionOptions : function() {
                  var self = this;

                  // MAIN SEKTION PANEL
                  var SektionPanelConstructor = api.Panel.extend({
                        //attachEvents : function () {},
                        // Always make the panel active, event if we have no sections / control in it
                        isContextuallyActive : function () {
                          return this.active();
                        },
                        _toggleActive : function(){ return true; }
                  });

                  // Prepend the Nimble logo in the main panel title
                  // the panel.expanded() Value is not the right candidate to be observed because it gets changed on too many events, when generating the various UI.
                  api.panel( sektionsLocalizedData.sektionsPanelId, function( _mainPanel_ ) {
                        _mainPanel_.deferred.embedded.done( function() {
                              var $sidePanelTitleEl = _mainPanel_.container.find('h3.accordion-section-title'),
                                  $topPanelTitleEl = _mainPanel_.container.find('.panel-meta .accordion-section-title'),
                                  logoHtml = [ '<img class="sek-nimble-logo" alt="'+ _mainPanel_.params.title +'" src="', sektionsLocalizedData.baseUrl, '/assets/img/nimble/nimble_horizontal.svg', '"/>' ].join('');

                              if ( 0 < $sidePanelTitleEl.length ) {
                                    // Attach click event
                                    // $sidePanelTitleEl.on( 'click', function( evt ) {
                                    //       api.previewer.trigger('sek-pick-module');
                                    // });
                                    // The default title looks like this : Nimble Builder <span class="screen-reader-text">Press return or enter to open this section</span>
                                    // we want to style "Nimble Builder" only.
                                    var $sidePanelTitleElSpan = $sidePanelTitleEl.find('span');
                                    $sidePanelTitleEl
                                          .addClass('sek-side-nimble-logo-wrapper')
                                          .html( logoHtml )
                                          .append( $sidePanelTitleElSpan );
                              }

                              // default looks like
                              // <span class="preview-notice">You are customizing <strong class="panel-title">Nimble Builder</strong></span>
                              // if ( 0 < $topPanelTitleEl.length ) {
                              //       var $topPanelTitleElInner = $topPanelTitleEl.find('.panel-title');
                              //       $topPanelTitleElInner.html( logoHtml );
                              // }
                        });
                  });

                  // The parent panel for all ui sections + global options section
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'panel',
                        id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                        title: sektionsLocalizedData.i18n['Nimble Builder'],
                        priority : -1000,
                        constructWith : SektionPanelConstructor,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                  });

            },//mayBeRegisterAndSetupAddNewSektionSection()




            //@return void()
            // sektionsData is built server side :
            //array(
            //     'db_values' => sek_get_skoped_seks( $skope_id ),
            //     'setting_id' => sek_get_seks_setting_id( $skope_id )//nimble___[skp__post_page_home]
            // )
            setContextualCollectionSettingIdWhenSkopeSet : function( newSkopes, previousSkopes ) {
                  var self = this;

                  // Clear all previous sektions if the main panel is expanded and we're coming from a previousSkopes
                  if ( ! _.isEmpty( previousSkopes.local ) && api.panel( sektionsLocalizedData.sektionsPanelId ).expanded() ) {
                        //api.previewer.trigger('sek-pick-section');
                        api.previewer.trigger('sek-pick-module');
                  }

                  // set the sekCollectionSettingId now, and update it on skope change
                  sektionsData = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local');
                  if ( sektionsLocalizedData.isDevMode ) {
                        api.infoLog( '::setContextualCollectionSettingIdWhenSkopeSet => SEKTIONS DATA ? ', sektionsData );
                  }
                  if ( _.isEmpty( sektionsData ) ) {
                        api.errare('::setContextualCollectionSettingIdWhenSkopeSet() => no sektionsData');
                  }
                  if ( _.isEmpty( sektionsData.setting_id ) ) {
                        api.errare('::setContextualCollectionSettingIdWhenSkopeSet() => missing setting_id');
                  }
                  self.sekCollectionSettingId( sektionsData.setting_id );
            },








            // TOP BAR
            // fired in ::initialize()
            toggleTopBar : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndSetupTopBarTmpl({}) ).done( function( $_el ) {
                                  self.topBarContainer = $_el;
                                  //display
                                  _.delay( function() {
                                      $('body').addClass('nimble-top-bar-open');
                                  }, 200 );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            $('body').removeClass('nimble-top-bar-open');
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
                  $(  '[data-nimble-history]', '#nimble-top-bar' ).on( 'click', function(evt) {
                        try { self.navigateHistory( $(this).data( 'nimble-history') ); } catch( er ) {
                              api.errare( 'Error when firing self.navigateHistory', er );
                        }
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
                        api( self.sekCollectionSettingId() )( self.validateSettingValue( newSettingValue ), { navigatingHistoryLogs : true } );

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
                        api.previewer.trigger( 'sek-pick-module', {});
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
