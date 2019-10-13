//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            initialize: function() {
                  var self = this;

                  // Set the skope_id
                  try { this.skope_id = _.findWhere( _wpCustomizeSettings.czr_new_skopes, { skope : 'local' }).skope_id; } catch( _er_ ) {
                        this.errare('Preview => error when storing the skope_id', _er_ );
                        return;
                  }

                  // Active UI
                  this.scheduleHighlightActiveLevel();

                  // The loading icon when a level is refreshed
                  self.setupLoader();

                  // DOM READY
                  $( function() {
                        self.setupSortable();
                        self.setupResizable();
                        self.setupUiHoverVisibility();
                        self.scheduleUiClickReactions();

                        self.schedulePanelMsgReactions();
                  });

                  // Make sure we don't force a minimum height to empty columns when a section has at least one module
                  // => allow a better previewing experience and more realistic spacing adjustments
                  // The css class .sek-has-modules is also printed server side
                  // @see php SEK_Front_Render::render()
                  $('body').on('sek-columns-refreshed sek-modules-refreshed', function( evt, params ) {
                        if ( !_.isUndefined( params ) && !_.isUndefined( params.in_sektion ) && $('[data-sek-id="' + params.in_sektion +'"]').length > 0 ) {
                              var $updatedSektion = $('[data-sek-id="' + params.in_sektion +'"]');
                              $updatedSektion.toggleClass( 'sek-has-modules', $updatedSektion.find('[data-sek-level="module"]').length > 0 );
                        }
                  });

                  // Deactivates the links
                  self.deactivateLinks();

                  $('body').on([
                        'sek-modules-refreshed',
                        'sek-columns-refreshed',
                        'sek-section-added',
                        'sek-level-refreshed',
                        'sek-edit-module'
                  ].join(' '), function( evt ) {
                        self.deactivateLinks(evt);
                  });


                  // Send the contextually active locations
                  // 1) on init
                  // 2) and when requested by the control panel
                  // introduced for the level tree, https://github.com/presscustomizr/nimble-builder/issues/359
                  var sendActiveLocations = function() {
                        var active_locs = [];
                        $('[data-sek-level="location"]').each( function() {
                              active_locs.push( $(this).data('sek-id') );
                        });
                        api.preview.send('sek-active-locations-in-preview', { active_locations : active_locs } );
                  };
                  api.preview.bind('sek-request-active-locations', sendActiveLocations );
                  sendActiveLocations();
            },

            // Fired on initialize()
            // and on user generated events
            deactivateLinks : function( evt ) {
                  evt = evt || {};
                  var _doSafe_ = function() {
                          if ( "yes" === $(this).data('sek-unlinked') )
                            return;
                          // Several cases :
                          // 1- internal link ( <=> api.isLinkPreviewable(... ) = true ) : we allow navigation with shift + click
                          // 2- extenal link => navigation is disabled.
                          // 3- server disabled links, with href attribute set to "javascript:void(0)", this case is checked isJavascriptProtocol
                          var isJavascriptProtocol = _.isString( $(this)[0].protocol ) && -1 !== $(this)[0].protocol.indexOf('javascript');
                          // the check on isJavascriptProtocol fixes issue https://github.com/presscustomizr/nimble-builder/issues/255
                          if ( ! isJavascriptProtocol && api.isLinkPreviewable( $(this)[0] ) ) {
                                $(this).addClass('nimble-shift-clickable');
                                $(this).data('sek-unlinked', "yes").attr('data-nimble-href', $(this).attr('href') ).attr('href', 'javascript:void(0)');
                                $(this).hover( function() {
                                        $(this).attr( 'title', sekPreviewLocalized.i18n['Shift-click to visit the link']);
                                }, function() {
                                      $(this).removeAttr( 'title' );
                                });
                                $(this).on('click', function(evt) {
                                      if ( ! evt.shiftKey ) {
                                        return;
                                      }
                                      evt.preventDefault();
                                      window.location.href = $(this).attr('data-nimble-href');
                                });
                          } else {
                                $(this).addClass('nimble-unclickable');
                                $(this).data('sek-unlinked', "yes").attr('data-nimble-href', $(this).attr('href') ).attr('href', 'javascript:void(0)');
                                $(this).hover( function() {
                                      $(this).attr( 'title', isJavascriptProtocol ? sekPreviewLocalized.i18n['Link deactivated while previewing'] : sekPreviewLocalized.i18n['External links are disabled when customizing']);
                                }, function() {
                                      $(this).removeAttr( 'title' );
                                });
                                $(this).on('click', function(evt) {
                                      evt.preventDefault();
                                });
                          }
                    };
                  $('body').find('[data-sek-level="module"]').each( function() {
                        $(this).find('a').each( function(){
                              try { _doSafe_.call( $(this) ); } catch(er) { api.errare( '::deactivateLinks => error ', er ); }
                        });
                  });
            },

            // Hightlight the currently level in the preview, corresponding to the active ui in the panel
            //
            // When a new ui is generated, the activeLevelUI is set @see ::schedulePanelMsgReactions()
            // When the level options are modidied ( 'sek-refresh-stylesheet', 'sek-refresh-level' ),
            scheduleHighlightActiveLevel : function() {
                  var self = this;
                  // Stores the currently edited level
                  this.activeLevelUI = new api.Value('');
                  this.activeUIChangedRecently = new api.Value( false );

                  this.activeLevelUI.bind( function( to, from ) {
                        var $activeLevel = $('[data-sek-id="' + to +'"]'),
                            $previousActiveLevel = $('[data-sek-id="' + from +'"]');
                        if ( $activeLevel.length > 0 ) {
                              $activeLevel.addClass('sek-active-ui sek-highlight-active-ui');
                        }
                        if ( $previousActiveLevel.length > 0 ) {
                              $previousActiveLevel.removeClass('sek-active-ui sek-highlight-active-ui');
                        }
                        self.activeUIChangedRecently( Date.now() );
                  });

                  // apiParams : {
                  //       action : 'sek-refresh-level',
                  //       id : params.uiParams.id,
                  //       level : params.uiParams.level
                  // },
                  // skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                  _.each( [ 'sek-refresh-stylesheet', 'sek-refresh-level' ], function( msgId ) {
                        api.preview.bind( msgId, function( params ) {
                              self.activeUIChangedRecently( Date.now() );
                        });
                  });


                  // This api.Value() is updated with Date.now() when something just happened and false after 3000 ms of inactivity
                  // so we can always react to changes, and refresh the timeOut
                  this.activeUIChangedRecently.bind( function( hasChangedRecently ) {
                        var $newActiveLevel = $('[data-sek-id="' + self.activeLevelUI() +'"]');
                        // remove the highlight class if it was previously set to another level
                        if ( $('.sek-highlight-active-ui').length ) {
                              $('.sek-highlight-active-ui').removeClass('sek-highlight-active-ui');
                        }
                        if ( $newActiveLevel.length > 0 ) {
                              $newActiveLevel.toggleClass( 'sek-highlight-active-ui', false !== hasChangedRecently );
                        }

                        clearTimeout( $.data( this, '_ui_change_timer_') );
                        $.data( this, '_ui_change_timer_', setTimeout(function() {
                              self.activeUIChangedRecently( false );
                        }, 3000 ) );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired on Dom Ready, in ::initialize()
            setupSortable: function() {
                  var self = this;
                  this.sortableDefaultParams = {
                        //forcePlaceholderSize: true,
                        //handle: '.fa-arrows-alt',<= set by level
                        placeholder: "sortable-placeholder",
                        over: function( event, ui ) {},
                        cursorAt: { top:0, left: 0 },//@fixes https://github.com/presscustomizr/nimble-builder/issues/114
                        tolerance: "pointer",//@fixes https://github.com/presscustomizr/nimble-builder/issues/114
                  };

                  // SEKTIONS
                  // On dom ready
                  $('[data-sek-level="location"]').each( function() {
                        self.makeSektionsSortableInLocation( $(this).data('sek-id') );
                  });

                  // Schedule with delegation
                  $( 'body').on( 'sek-section-added sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
                        self.makeSektionsSortableInLocation( $(this).data('sek-id') );
                  });


                  // COLUMNS
                  // On dom ready
                  $('[data-sek-level="location"]').each( function() {
                        $(this).find( '[data-sek-level="section"]' ).each( function() {
                              self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                        });
                  });
                  // Schedule with delegation
                  $('body').on( 'sek-columns-refreshed sek-section-added', '[data-sek-level="section"]', function( evt ) {
                        self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                  });
                  // this case occurs when moving a section from one location to another for example
                  $( 'body').on( 'sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
                        $(this).find( '[data-sek-level="section"]' ).each( function() {
                              self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                        });
                  });


                  // MODULES
                  // On dom ready
                  $('[data-sek-level="location"]').each( function() {
                        $(this).find( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  // Schedule with delegation
                  $('body').on( 'sek-modules-refreshed', '[data-sek-level="column"]', function() {
                        self.makeModulesSortableInColumn( $(this).data('sek-id') );
                  });
                  $('body').on( 'sek-columns-refreshed', '[data-sek-level="section"]', function() {
                        $(this).find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  // this case occurs when moving a section from one location to another for example
                  $( 'body').on( 'sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
                        $(this).find( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  $( 'body').on( 'sek-section-added', '[data-sek-level="location"]', function( evt, params  ) {
                        $(this).find( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });

                  // NESTED SEKTIONS
                  // $('.sek-column-inner', '[data-sek-level="section"]').children( '[data-sek-level="section"]' ).each( function() {
                  //       defaults = $.extend( true, {}, self.sortableDefaultParams );
                  //       $(this).sortable( _.extend( defaults, {
                  //           handle : '.sek-move-nested-section',
                  //           connectWith: ".sek-column-inner, [data-sek-level="location"]",
                  //           start: function( event, ui ) {
                  //               // store the startOrder
                  //               $('[data-sek-level="location"]').children( '[data-sek-level="section"]' ).each( function() {
                  //                     startOrder.push( $(this).data('sek-id') );
                  //               });
                  //               //console.log('column moved from', from_sektion, ui );
                  //           },
                  //           stop : function( event, ui ) {
                  //               newOrder = [];
                  //               // Restrict to the direct children
                  //               $('[data-sek-level="location"]').children( '[data-sek-level="section"]' ).each( function() {
                  //                     newOrder.push( $(this).data('sek-id') );
                  //               });

                  //               // api.preview.send( 'sek-move', {
                  //               //       id : ui.item.data('sek-id'),
                  //               //       level : 'section',
                  //               //       newOrder : newOrder
                  //               // });
                  //           }
                  //       }));
                  // });


                  // <SORTABLE>
                  // $('.sek-sektion-inner').each( function() {
                  //     $(this).sortable({
                  //         connectWith: ".sek-sektion-inner"
                  //     }).disableSelection();
                  // });

                  return this;
            },//setupSortable()


            makeSektionsSortableInLocation : function( locationId ) {
                  var self = this;
                  var from_location, to_location, startOrder = [], newOrder = [], defaults;
                  $('[data-sek-id="' + locationId +'"]').each( function() {
                        if ( true === $(this).data('sek-is-global-location') )
                          return;

                        defaults = $.extend( true, {}, self.sortableDefaultParams );
                        $(this).sortable( _.extend( defaults, {
                              //handle : '.sek-move-section, .sek-section-dyn-ui > .sek-dyn-ui-location-type',//@fixes https://github.com/presscustomizr/nimble-builder/issues/153
                              handle : '.sek-move-section',
                              connectWith : '[data-sek-is-global-location="false"]',
                              placeholder: {
                                    element: function(currentItem) {
                                        return $('<div class="sortable-placeholder"><div class="sek-module-placeholder-content"><p>' + sekPreviewLocalized.i18n['Insert here'] + '</p></div></div>')[0];
                                    },
                                    update: function(container, p) {
                                        return;
                                    }
                              },
                              start: function( event, ui ) {
                                    $('body').addClass('sek-moving-section');
                                    $sourceLocation = ui.item.closest('[data-sek-level="location"]');
                                    from_location = $sourceLocation.data('sek-id');

                                    // store the startOrder
                                    $sourceLocation.children( '[data-sek-level="section"]' ).each( function() {
                                          startOrder.push( $(this).data('sek-id') );
                                    });
                              },
                              stop : function( event, ui ) {
                                    $('body').removeClass('sek-moving-section');

                                    newOrder = [];
                                    $targetLocation = ui.item.closest('[data-sek-level="location"]');
                                    to_location = $targetLocation.data('sek-id');

                                    // Restrict to the direct children
                                    $targetLocation.children( '[data-sek-level="section"]' ).each( function() {
                                          newOrder.push( $(this).data('sek-id') );
                                    });

                                    api.preview.send( 'sek-move', {
                                          id : ui.item.data('sek-id'),
                                          level : 'section',
                                          newOrder : newOrder,
                                          from_location : from_location,
                                          to_location : to_location
                                    });
                              },
                              over : function( event, ui ) {
                                    ui.placeholder.addClass('sek-sortable-section-over');
                              },
                              out : function( event, ui  ) {
                                    ui.placeholder.removeClass('sek-sortable-section-over');
                              }
                        }));
                  });
            },//makeSektionsSortableInLocation







            // Instantiate sortable for a given column Id
            // Columns are a little more complex because we want to emulate the future layouts when moving a column from section to section
            // for that, we need to compute the number of columns and play with css classes.
            // During this process, we use two $.data to store informations :
            //    'sek-is-sender' => tells us if this is the sektion from which we started to drag a column
            //    '_sortable_columns_css_classes_' => stores the current and future css classes
            makeColumnsSortableInSektion : function( sektionId ) {
                  var self = this,
                      defaults = $.extend( true, {}, self.sortableDefaultParams ),
                      $fromLocation,
                      is_global_from_location,
                      $toLocation,
                      is_global_to_location,
                      startOrder = [],
                      newOrder = [],
                      $sortableCandidate = $( '[data-sek-id="' + sektionId + '"]').find('.sek-sektion-inner').first(),
                      getCurrentAndNextColNumberClasses = function( args ) {
                            args = $.extend( { forTarget : true }, args || {} );
                            if ( ! _.isEmpty( $(this).data('_sortable_columns_css_classes_' ) ) )
                              return $(this).data('_sortable_columns_css_classes_' );

                            var $targetSektion          = $(this).closest('[data-sek-level="section"]'),
                                $columnsInTargetSektion = $targetSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ),
                                currentColnumber        = $columnsInTargetSektion.length,
                                currentColCSSSuffix     = Math.floor( 100/currentColnumber );

                            // the future columns number is different for the source and target sektions.
                            var nextColNumber;
                            if ( true === args.forTarget ) {
                                  nextColNumber = 12 < ( currentColnumber + 1 ) ? 12 : currentColnumber + 1;
                            } else {
                                  nextColNumber = 1 > ( currentColnumber - 1 ) ? 1 : currentColnumber -1;
                            }

                            // this css suffix is consistent with the one written server side
                            // @see SEK_Front_Render::render() case 'column'
                            var nextColCSSSuffix        = Math.floor( 100/nextColNumber ),
                                current_columns_css_class = 'sek-col-' + currentColCSSSuffix,
                                next_columns_css_class = 'sek-col-' + nextColCSSSuffix,
                                _classes_ = { current : current_columns_css_class , next : next_columns_css_class  };

                            $(this).data('_sortable_columns_css_classes_', _classes_ );
                            return _classes_;
                      },
                      cleanOnStop = function() {
                            $( '[data-sek-level="section"]').find('.sek-sektion-inner').each( function() {
                                    $(this).data( 'sek-is-sender', null ).data('_sortable_columns_css_classes_', null );
                            });
                      };
                  // if ( $sortableCandidate.children('[data-sek-level="column"]').length > 11 ) {
                  //       self.errare('12 COLUMNS');
                  //       return;
                  // }

                  $sortableCandidate.sortable( _.extend( defaults, {
                        //handle : '.sek-move-column, .sek-column-dyn-ui > .sek-dyn-ui-location-type',//@fixes https://github.com/presscustomizr/nimble-builder/issues/153
                        handle : '.sek-move-column',
                        connectWith: ".sek-sektion-inner",
                        over : function( event, ui ) {
                              var $targetSektion          = $(this).closest('[data-sek-level="section"]'),
                                  $columnsInTargetSektion = $targetSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ),
                                  _classes_;

                              if ( true !== $(this).data('sek-is-sender' ) ) {
                                    _classes_ = getCurrentAndNextColNumberClasses.call( $(this) );
                                    if ( ! _.isEmpty( _classes_ ) ) {
                                          $columnsInTargetSektion.each( function() {
                                                $(this).removeClass( _classes_.current ).addClass( _classes_.next );
                                          });
                                    }
                              } else {
                                    _classes_ = getCurrentAndNextColNumberClasses.call( $(this), { forTarget : false } );
                                    if ( ! _.isEmpty( _classes_ ) ) {
                                          $columnsInTargetSektion.each( function() {
                                                $(this).addClass( _classes_.current ).removeClass( _classes_.next );
                                          });
                                    }
                              }
                        },
                        out : function( event, ui ) {
                              var $outedSektion = $(this).closest('[data-sek-level="section"]'),
                                  $columnsInOutedSektion = $outedSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ),
                                  _classes_;
                              if ( true !== $(this).data('sek-is-sender' ) ) {
                                    _classes_ = getCurrentAndNextColNumberClasses.call( $(this) );
                                    if ( ! _.isEmpty( _classes_ ) ) {
                                          $columnsInOutedSektion.each( function() {
                                                $(this).addClass( _classes_.current ).removeClass( _classes_.next );
                                          });
                                    }
                              } else {
                                    _classes_ = getCurrentAndNextColNumberClasses.call( $(this), { forTarget : false } );
                                    if ( ! _.isEmpty( _classes_ ) ) {
                                          $columnsInOutedSektion.each( function() {
                                                $(this).removeClass( _classes_.current ).addClass( _classes_.next );
                                          });
                                    }
                              }
                        },
                        remove : function( event, ui ) {
                              $toLocation = ui.item.closest('[data-sek-level="location"]');
                              to_location = $toLocation.data( 'sek-id');
                              is_global_to_location = true === $toLocation.data('sek-is-global-location');

                              var _isCrossSkope = is_global_from_location !== is_global_to_location,
                                  _isCrossLocation = to_location != from_location,
                                  _isGlobalToGlobal = true === is_global_from_location && true === is_global_to_location;

                              // Not possible to drag from a local location to a global
                              // Not possible to drag from a global header to a global footer
                              if ( _isCrossSkope || ( _isGlobalToGlobal && _isCrossLocation ) ) {
                                    api.preview.send( 'sek-notify', {
                                          message : sekPreviewLocalized.i18n["Moving elements between global and local sections is not allowed."]
                                    });
                                    return false;
                              }

                              $targetSektionCandidate = ui.item.closest('[data-sek-level="section"]');
                              if ( $targetSektionCandidate.length > 0 && $targetSektionCandidate.find('.sek-sektion-inner').first().children('[data-sek-level="column"]').length > 12 ) {
                                    api.preview.send( 'sek-notify', {
                                          message : sekPreviewLocalized.i18n["You've reached the maximum number of columns allowed in this section."]
                                    });
                                    return false;
                              } else {
                                    return true;
                              }
                        },
                        start: function( event, ui ) {
                              // Store the following for the "remove" callback
                              $fromLocation = ui.item.closest('[data-sek-level="location"]');
                              from_location = $fromLocation.data( 'sek-id');
                              is_global_from_location = true === $fromLocation.data('sek-is-global-location');

                              // Always reset startOrder and newOrder
                              startOrder = [];
                              newOrder = [];

                              //$('.sek-column-inner').css( {'min-height' : '20px'});
                              // Set source
                              from_sektion = ui.item.closest('[data-sek-level="section"]').data( 'sek-id');

                              // store the startOrder
                              ui.item.closest('[data-sek-level="section"]').find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
                                    startOrder.push( $(this).data('sek-id') );
                              });

                              $(this).data('sek-is-sender', true );
                              if ( _.isEmpty( startOrder ) ) {
                                    self.errare( 'column sortable => startOrder should not be empty' );
                                    return;
                              }
                        },

                        stop : function( event, ui ) {
                              // set destination
                              $targetSektion = ui.item.closest('[data-sek-level="section"]');
                              to_sektion = $targetSektion.data( 'sek-id');
                              $targetSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
                                    newOrder.push( $(this).data('sek-id') );
                              });

                              var $stopSektion = $(this).closest('[data-sek-level="section"]'),
                                  $columnsInstopSektion = $stopSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ),
                                  _classes_;
                              if ( true !== $(this).data('sek-is-sender' ) ) {
                                    _classes_ = getCurrentAndNextColNumberClasses.call( $(this) );
                                    if ( ! _.isEmpty( _classes_ ) ) {
                                          $columnsInstopSektion.each( function() {
                                                $(this).removeClass( _classes_.current ).addClass( _classes_.next );
                                          });
                                    }
                              } else {
                                    _classes_ = getCurrentAndNextColNumberClasses.call( $(this), { forTarget : false } );
                                    if ( ! _.isEmpty( _classes_ ) ) {
                                          $columnsInstopSektion.each( function() {
                                                $(this).addClass( _classes_.current ).removeClass( _classes_.next );
                                          });
                                    }
                              }
                              cleanOnStop();

                              if ( _.isEmpty( newOrder ) ) {
                                    self.errare( 'column sortable =>  newOrder should not be empty' );
                                    return;
                              }

                              // don't send anything if the source and target columns are the same, and the order is unchanged
                              if ( _.isEqual( newOrder, startOrder ) && to_sektion === from_sektion ) {
                                    if ( sekPreviewLocalized.isDevMode ) {
                                          self.errare( 'preview => makeModulesSortableInColumn => start and stop positions are identical' );
                                    }
                                    return;
                              }
                              api.preview.send( 'sek-move', {
                                    id : ui.item.data('sek-id'),
                                    level : 'column',
                                    newOrder : newOrder,
                                    from_sektion : from_sektion,
                                    to_sektion : to_sektion
                              });

                              // inform the parent section that it's been refreshed
                              //=> will be listened to by columns to re-instantiate sortable, resizable
                              $('div[data-sek-id="' + from_sektion + '"]' ).trigger('sek-level-refreshed');
                              if ( from_sektion !== to_sektion ) {
                                  $('div[data-sek-id="' + to_sektion + '"]' ).trigger('sek-level-refreshed');
                              }

                        }
                  }));
            },//self.makeColumnsSortableInSektion










            // Instantiate sortable for a given column Id
            makeModulesSortableInColumn : function( columnId ) {
                  var from_sektion,
                      to_sektion,
                      from_column,
                      to_column,
                      startOrder = [],
                      newOrder = [],
                      $targetSektion,
                      $targetColumn,
                      defaults,
                      $fromLocation,
                      is_global_from_location,
                      $toLocation,
                      is_global_to_location;

                  var self = this;
                  defaults = $.extend( true, {}, self.sortableDefaultParams );
                  // Restrict to the .sek-column-inner for this very column id with first()
                  $( '[data-sek-id="' + columnId + '"]').find('.sek-column-inner').first().sortable( _.extend( defaults, {
                        //handle : '.sek-move-module, .sek-module-dyn-ui > .sek-dyn-ui-location-type .sek-dyn-ui-level-type',//@fixes https://github.com/presscustomizr/nimble-builder/issues/153
                        handle : '.sek-move-module',
                        connectWith: ".sek-column-inner",
                        over : function( event, ui ) {
                              // Hide the module placeholder while overing, when the column is empty
                              // @see css rule .sek-sortable-overing > .sek-no-modules-column { display: none; }
                              $('[data-sek-level="location"]').find('.sek-sortable-overing').each( function() {
                                    $(this).removeClass('sek-sortable-overing');
                              });
                              $( event.target ).addClass('sek-sortable-overing');
                        },
                        remove : function( event, ui ) {
                              $toLocation = ui.item.closest('[data-sek-level="location"]');
                              to_location = $toLocation.data( 'sek-id');
                              is_global_to_location = true === $toLocation.data('sek-is-global-location');

                              var _isCrossSkope = is_global_from_location !== is_global_to_location,
                                  _isCrossLocation = to_location != from_location,
                                  _isGlobalToGlobal = true === is_global_from_location && true === is_global_to_location;

                              // Not possible to drag from a local location to a global
                              // Not possible to drag from a global header to a global footer
                              if ( _isCrossSkope || ( _isGlobalToGlobal && _isCrossLocation ) ) {
                                    api.preview.send( 'sek-notify', {
                                          message : sekPreviewLocalized.i18n["Moving elements between global and local sections is not allowed."]
                                    });
                                    return false;
                              } else {
                                    return true;
                              }
                        },
                        start: function( event, ui ) {
                              // Store the following for the "remove" callback
                              $fromLocation = ui.item.closest('[data-sek-level="location"]');
                              from_location = $fromLocation.data( 'sek-id');
                              is_global_from_location = true === $fromLocation.data('sek-is-global-location');

                              // Always reset startOrder and newOrder
                              startOrder = [];
                              newOrder = [];

                              $('body').addClass( 'sek-dragging-element' );
                              //$('.sek-column-inner').css( {'min-height' : '20px'});
                              // Set source
                              from_column = ui.item.closest('[data-sek-level="column"]').data( 'sek-id');
                              from_sektion = ui.item.closest('[data-sek-level="section"]').data( 'sek-id');
                              // store the startOrder
                              ui.item.closest('[data-sek-level="column"]').find('.sek-column-inner').first().children( '[data-sek-level="module"]' ).each( function() {
                                    startOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( startOrder ) ) {
                                    self.errare( 'makeModulesSortableInColumn => startOrder should not be empty' );
                                    return;
                              }
                        },

                        stop : function( event, ui ) {
                              // set destination
                              $targetColumn = ui.item.closest('[data-sek-level="column"]');
                              to_column = $targetColumn.data( 'sek-id');

                              $targetColumn.find('.sek-column-inner').first().children( '[data-sek-id]' ).each( function() {
                                    newOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( newOrder ) ) {
                                    self.errare( 'makeModulesSortableInColumn => newOrder should not be empty' );
                                    return;
                              }

                              // don't send anything if the source and target columns are the same, and the order is unchanged
                              if ( _.isEqual( newOrder, startOrder ) && to_column === from_column ) {
                                    self.errare( 'preview => makeModulesSortableInColumn => start and stop positions are identical' );
                                    return;
                              }
                              api.preview.send( 'sek-move', {
                                    id : ui.item.data('sek-id'),
                                    level : 'module',
                                    newOrder : newOrder,
                                    from_column : from_column,
                                    to_column : to_column,
                                    from_sektion : from_sektion,
                                    to_sektion : ui.item.closest('[data-sek-level="section"]').data( 'sek-id')
                              });

                              // Clean some css classes
                              $('body').removeClass( 'sek-dragging-element' );
                        }
                  }));
            },//makeModulesSortableInColumn
      });//$.extend()
})( wp.customize, jQuery, _ );//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired on Dom Ready, in ::initialize()
            setupResizable : function() {
                  var self = this;
                  $('.sektion-wrapper').find( 'div[data-sek-level="section"]' ).each( function() {
                        self.maybeMakeColumnResizableInSektion.call( this );
                  });
                  // Delegate instantiation when a level markup is refreshed
                  // Let the event bubble up to the location, and then visit all children section to maybe re-instantiate resizable
                  // @fixes https://github.com/presscustomizr/nimble-builder/issues/165
                  $('body').on(
                        'sek-level-refreshed sek-modules-refreshed sek-columns-refreshed sek-section-added sek-location-refreshed',
                        '[data-sek-level="location"]',
                        function( evt ) {
                              $(this).find('[data-sek-level="section"]').each( function() {
                                    self.maybeMakeColumnResizableInSektion.call( this );
                              });
                        }
                  );
                  return this;
            },//setupResizable()

            // this is the parent section jQuery object
            maybeMakeColumnResizableInSektion : function() {
                  var self = this,
                      $parentSektion,
                      parentSektionId,
                      parentSektionWidth,
                      $resizedColumn,
                      resizedColumnWidthInPercent,
                      //calculate the number of column in this section, excluding the columns inside nested sections if any
                      colNumber,
                      $sisterColumn,
                      isLastColumn;

                  // We won't fire resizable for single column sektions
                  var $directColumnChildren = $(this).find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' );
                  if ( 2 > $directColumnChildren.length )
                    return;

                  var $lastCol = $(this).find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).last();

                  $directColumnChildren.not($lastCol).each( function() {
                        $(this).resizable({
                                // handles: { 'e': '.ui-resizable-e', 'w': '.ui-resizable-w' },
                                resize : function( event, ui ) {
                                      $('.sektion-wrapper').data('sek-resizing-columns', true );
                                },
                                start : function( event, ui ) {
                                      $parentSektion = ui.element.closest('div[data-sek-level="section"]');
                                      parentSektionId = $parentSektion.data('sek-id');
                                      parentSektionWidth = $parentSektion.find('.sek-sektion-inner')[0].getBoundingClientRect().width;
                                      //calculate the number of column in this section, excluding the columns inside nested sections if any
                                      colNumber = $parentSektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length;

                                      // Resizable should not have been instantiated anyway
                                      if ( 2 > colNumber )
                                        return;

                                      $resizedColumn = ui.element.closest('div[data-sek-level="column"]');
                                      if ( 1 > $resizedColumn.length ) {
                                          throw new Error( 'ERROR => resizable => No valid level dom element found' );
                                      }

                                      isLastColumn = $resizedColumn.index() + 1 == colNumber;

                                      // Assomption : RTL user. LTR should be implemented also.
                                      // If parent section has at least 2 columns, the sister column is the one on the right if not in last position. On the left if last.
                                      $sisterColumn = isLastColumn ? $resizedColumn.prev() : $resizedColumn.next();

                                      // Implement a global state value()
                                      $('.sektion-wrapper').data('sek-resizing-columns', true );

                                      // auto set to false after a moment.
                                      _.delay( function() {
                                            $('.sektion-wrapper').data('sek-resizing-columns', false );
                                      }, 3000 );
                                },
                                stop : function( event, ui ) {
                                      // console.log('ON RESIZE STOP', event, ui, $resizedColumn );
                                      // console.log('colNumber', colNumber, '[data-sek-id="' + parentSektionId +'"] > .sek-sektion-inner' );
                                      // Skip the case when there's only one column
                                      // Resizable should not have been instantiated anyway
                                      if ( 2 > colNumber )
                                        return;

                                      if ( 1 > $resizedColumn.length ) {
                                          throw new Error( 'ERROR => resizable => No valid level dom element found' );
                                      }

                                      // Reset the automatic inline style
                                      $resizedColumn.css({
                                            width : '',
                                            height: ''
                                      });

                                      resizedColumnWidthInPercent = ( ( parseFloat( ui.size.width ) / parseFloat( parentSektionWidth ) ) * 100 ).toFixed(3);

                                      api.preview.send( 'sek-resize-columns', {
                                            action : 'sek-resize-columns',
                                            level : $resizedColumn.data( 'sek-level' ),
                                            in_sektion : $parentSektion.data('sek-id'),
                                            id : $resizedColumn.data('sek-id'),

                                            resized_column : $resizedColumn.data('sek-id'),
                                            sister_column : $sisterColumn.data('sek-id'),

                                            resizedColumnWidthInPercent : resizedColumnWidthInPercent,

                                            col_number : colNumber
                                      });

                                      $('.sektion-wrapper').data('sek-resizing-columns', false );
                                },
                                helper: "ui-resizable-helper",
                                handles :'e'
                        });//$(this).resizable({})

                        // Add a resizable icon in the handle
                        // revealed on section hovering @see sek-preview.css
                        var $column = $(this);
                        _.delay( function() {
                              var $resizableHandle = $column.find('.ui-resizable-handle');
                              if ( $resizableHandle.find('.fa-arrows-alt-h').length < 1 ) {
                                    $column.find('.ui-resizable-handle').append('<i class="fas fa-arrows-alt-h"></i>');
                              }
                        }, 500 );

                  });//$directColumnChildren.each()
            }

      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            printLevelUI : function( $el ) {
                  var self = this;
                  var tmpl,
                      level,
                      params,
                      $levelEl;
                  if ( _.isUndefined( $el ) || $el.length < 1 ) {
                        self.errare('sekPreview::printeLevelUI => invalid level element => ', $el );
                  }
                  level = $el.data('sek-level');
                  // we don't print a ui for locations
                  if ( 'location' == level )
                    return;

                  $levelEl = $el;

                  // stop here if the .sek-dyn-ui-wrapper is already printed for this level AND is not being faded out.
                  // if ( $levelEl.children('.sek-dyn-ui-wrapper').length > 0 && true !== $levelEl.data( 'UIisFadingOut' ) )
                  //   return;

                  if ( $levelEl.children('.sek-dyn-ui-wrapper').length > 0 )
                    return;

                  var levelRect = $levelEl[0].getBoundingClientRect(),
                      levelType = $levelEl.data('sek-level');

                  // Adapt the size of the UI icons and text in narrow containers
                  $levelEl.toggleClass( 'sek-shrink-my-ui', levelRect.width && levelRect.width < ( 'section' === levelType ? 350 : ( 'column' === levelType ? 300 : 200 ) ) );

                  params = {
                        id : $levelEl.data('sek-id'),
                        level : levelType
                  };
                  switch ( level ) {
                        case 'section' :
                              //$el = $('.sektion-wrapper').find('[data-sek-id="' + id + '"]');
                              // Let's prepare the is_last and is_first params that we are going to send to the js template
                              // => this will determine which up / down arrow to display in the UI menu for moving a section
                              var $parentLocation = $levelEl.closest('div[data-sek-level="location"]'),
                                  $parentColumn = $levelEl.closest('div[data-sek-level="column"]'),
                                  $sectionCollection,
                                  _is_last_section,
                                  _is_first_section,
                                  _is_nested =  true === $levelEl.data('sek-is-nested');

                              // information about first and last section is used when rendering the up / down moving arrows
                              if ( _is_nested ) {
                                    if ( $parentColumn.length > 0 ) {
                                          $sectionCollection = $parentColumn.find('.sek-column-inner').first().children( 'div[data-sek-level]' );
                                          _is_last_section = $sectionCollection.length == $levelEl.index() + 1;
                                          _is_first_section = 0 === $levelEl.index();
                                    }
                              } else {
                                    if ( $parentLocation.length > 0 ) {
                                          $sectionCollection = $parentLocation.children( 'div[data-sek-level="section"]' );
                                          _is_last_section = $sectionCollection.length == $levelEl.index() + 1;
                                          _is_first_section = 0 === $levelEl.index();
                                    }
                              }

                              params = _.extend( params, {
                                    is_nested : _is_nested,
                                    can_have_more_columns : $levelEl.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 12,
                                    is_global_location : true === $parentLocation.data('sek-is-global-location'),
                                    is_last_section_in_parent : _is_last_section,
                                    is_first_section_in_parent : _is_first_section,
                                    is_header_location : true === $parentLocation.data('sek-is-header-location'),
                                    is_footer_location : true === $parentLocation.data('sek-is-footer-location')
                              });
                        break;
                        case 'column' :
                              var $parent_sektion = $levelEl.closest('div[data-sek-level="section"]');
                              params = _.extend( params, {
                                    parent_can_have_more_columns : $parent_sektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 12,
                                    parent_is_single_column : $parent_sektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 2,
                                    parent_is_last_allowed_nested : true === $parent_sektion.data('sek-is-nested'),
                                    has_nested_section : $levelEl.find('[data-sek-is-nested="true"]').length > 0
                              });
                        break;
                        case 'module' :
                              var module_name = self.getRegisteredModuleProperty( $levelEl.data('sek-module-type'), 'name' );
                              params = _.extend( params, {
                                    module_name : 'not_set' != module_name ? module_name : ''
                              });
                        break;
                  }
                  // don't display the column and module ui when resizing columns
                  if ( true === $('.sektion-wrapper').data('sek-resizing-columns') && _.contains( ['column', 'module'], level ) ) {
                        return;
                  }

                  tmpl = self.parseTemplate( '#sek-dyn-ui-tmpl-' + level );
                  $.when( $levelEl.prepend( tmpl( params ) ) ).done( function() {
                        $levelEl.find('.sek-dyn-ui-wrapper').stop( true, true ).fadeIn( {
                              duration : 150,
                              complete : function() {}
                        } );
                  });
            },//printLevelUI()









            // Fired on Dom Ready, in ::initialize()
            setupUiHoverVisibility : function() {
                  var self = this;
                  var tmpl,
                      level,
                      params,
                      $levelEl;

                  var removeLevelUI = function() {
                        $levelEl = $(this);
                        if ( $levelEl.children('.sek-dyn-ui-wrapper').length < 1 )
                          return;
                        // when PHP constant NIMBLE_IS_PREVIEW_UI_DEBUG_MODE is true, the levels UI in the preview are not being auto removed, so we can inspect the markup and CSS
                        if ( sekPreviewLocalized.isPreviewUIDebugMode )
                          return;

                        //stores the status of 200 ms fading out. => will let us know if we can print again when moving the mouse fast back and forth between two levels.
                        $levelEl.data( 'UIisFadingOut', true );//<= we need to store a fadingOut status to not miss a re-print in case of a fast moving mouse

                        $levelEl.children('.sek-dyn-ui-wrapper').stop( true, true ).fadeOut( {
                              duration : 150,
                              complete : function() {
                                    $(this).remove();
                                    $levelEl.data( 'UIisFadingOut', false );
                              }
                        });
                  };//removeLevelUI




                  // UI MENU
                  // React to click and mouse actions. Uses delegation.
                  // + schedule auto collapse after n seconds of ui inactivity
                  var autoCollapser = function() {
                        var $menu = $(this);
                        clearTimeout( $menu.data('_toggle_ui_menu_') );
                        $menu.data( '_toggle_ui_menu_', setTimeout(function() {
                              setClassesAndVisibilities.call( $menu );
                        }, 10000 ) );
                      },
                      setClassesAndVisibilities = function( expand ) {
                            var $menu = $(this),
                                $levelTypeAndMenuWrapper = $(this).closest('.sek-dyn-ui-location-type'),
                                $dynUiWrapper = $menu.closest( '.sek-dyn-ui-wrapper').find('.sek-dyn-ui-inner');
                            if ( true === expand ) {
                                  $menu.removeClass('sek-collapsed');
                                  $dynUiWrapper.addClass('sek-is-expanded');
                                  $levelTypeAndMenuWrapper.hide();
                            } else {
                                  $menu.addClass('sek-collapsed');
                                  $dynUiWrapper.removeClass('sek-is-expanded');
                                  $levelTypeAndMenuWrapper.show();
                            }
                      };
                  $('body').on( 'click', '.sek-dyn-ui-location-inner', function( evt )  {
                        var $menu = $(this).find('.sek-dyn-ui-hamb-menu-wrapper'),
                            $parentSection = $(this).closest('[data-sek-level="section"]');
                        // Close all other expanded ui menu of the column
                        $parentSection.find('.sek-dyn-ui-hamb-menu-wrapper').each( function() {
                              setClassesAndVisibilities.call( $(this) );
                        });
                        // expand the ui menu of the clicked level
                        setClassesAndVisibilities.call( $menu, true );
                        // schedule autocollapsing
                        autoCollapser.call( $menu );
                  });
                  // maintain expanded as long as it's being hovered
                  $('body').on( 'mouseenter mouseover mouseleave', '.sek-dyn-ui-wrapper', _.throttle( function( evt )  {
                        var $menu = $(this).find('.sek-dyn-ui-hamb-menu-wrapper');
                        if ( _.isUndefined( $menu.data('_toggle_ui_menu_') ) || $menu.hasClass('sek-collapsed') )
                          return;
                        if ( $menu.length > 0 ) {
                              autoCollapser.call( $menu );
                        }
                  }, 50 ) );

                  // minimize on click
                  // solves the problem of a level ui on top of another one
                  // @ee https://github.com/presscustomizr/nimble-builder/issues/138
                  $('body').on( 'click', '.sek-minimize-ui', function( evt )  {
                        $(this).closest('.sek-dyn-ui-location-type').slideToggle('fast');
                  });






                  // Ui for the WP content.
                  // Generated when is_singular() only
                  // @see SEK_Front::render()
                  var $wpContentEl;
                  $('body').on( 'mouseenter', '.sek-wp-content-wrapper', function( evt ) {
                        $wpContentEl = $(this);
                        // stop here if the .sek-dyn-ui-wrapper is already printed for this level AND is not being faded out.
                        if ( $wpContentEl.children('.sek-dyn-ui-wrapper').length > 0 && true !== $wpContentEl.data( 'UIisFadingOut' ) )
                          return;

                        tmpl = self.parseTemplate( '#sek-dyn-ui-tmpl-wp-content');
                        $.when( $wpContentEl.prepend( tmpl( {} ) ) ).done( function() {
                              $wpContentEl.find('.sek-dyn-ui-wrapper').stop( true, true ).fadeIn( {
                                    duration : 150,
                                    complete : function() {}
                              } );
                        });
                  }).on( 'mouseleave', '.sek-wp-content-wrapper', function( evt ) {
                        $(this).data( 'UIisFadingOut', true );//<= we need to store a fadingOut status to not miss a re-print in case of a fast moving mouse
                        $wpContentEl = $(this);
                        $(this).children('.sek-dyn-ui-wrapper').stop( true, true ).fadeOut( {
                              duration : 150,
                              complete : function() {
                                    $(this).remove();
                                    $wpContentEl.data( 'UIisFadingOut', false );
                              }
                        });
                  });



                  // ADD SECTION BUTTONS
                  // Add content button between sections
                  // <script type="text/html" id="sek-tmpl-add-content-button">
                  //     <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                  //       <div class="sek-add-content-button-wrapper">
                  //         <button data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:60px;">
                  //           <span title="<?php _e('Add Content', 'text_doma' ); ?>" class="sek-click-on-button-icon fas fa-plus-circle sek-click-on"></span><span class="action-button-text"><?php _e('Add Content', 'text_doma' ); ?></span>
                  //         </button>
                  //       </div>
                  //     </div>
                  // </script>
                  // fired on mousemove and scroll, every 50ms
                  var _printAddContentButtons = function() {
                        var _location, _is_global_location;
                        $('body').find( 'div[data-sek-level="location"]' ).each( function() {
                              $sectionCollection = $(this).children( 'div[data-sek-level="section"]' );
                              tmpl = self.parseTemplate( '#sek-tmpl-add-content-button' );
                              var $btn_el;
                              _location = $(this).data('sek-id');
                              _is_global_location = true === $(this).data('sek-is-global-location');

                              // nested sections are not included
                              $sectionCollection.each( function() {
                                    if ( $(this).find('.sek-add-content-button').length > 0 )
                                      return;

                                    $.when( $(this).prepend( tmpl({
                                          location : _location,
                                          is_global_location : _is_global_location
                                    }) ) ).done( function() {
                                          $btn_el = $(this).find('.sek-add-content-button');
                                          if ( $(this).data('sek-id') ) {
                                                $btn_el.attr('data-sek-before-section', $(this).data('sek-id') );//Will be used to insert the section at the right place
                                          }
                                          $btn_el.fadeIn( 300 );
                                    });
                                    //console.log('$sectionCollection.length', $sectionCollection.length, $(this).index() + 1 );
                                    //if is last section, append also
                                    //console.log('IS LAST ? => ', $sectionCollection.length, $(this).index() );
                                    if ( $sectionCollection.length == $(this).index() + 1 ) {
                                          $.when( $(this).append( tmpl({
                                                is_last : true,
                                                location : _location,
                                                is_global_location : _is_global_location
                                          }) ) ).done( function() {
                                                $btn_el = $(this).find('.sek-add-content-button').last();
                                                if ( $(this).data('sek-id') ) {
                                                      $btn_el.attr('data-sek-after-section', $(this).data('sek-id') );//Will be used to insert the section at the right place
                                                }
                                                $btn_el.fadeIn( 300 );
                                          });
                                    }
                              });//$sectionCollection.each( function() )
                        });//$( 'div[data-sek-level="location"]' ).each( function() {})



                        // .sek-empty-location-placeholder container is printed when the location has no section yet in its collection
                        $('.sek-empty-location-placeholder').each( function() {
                              if ( $(this).find('.sek-add-content-button').length > 0 )
                                return;

                              _location = $(this).closest( 'div[data-sek-level="location"]' ).data('sek-id');
                              _is_global_location = true === $(this).closest( 'div[data-sek-level="location"]' ).data('sek-is-global-location');

                              $.when( $(this).append( tmpl({
                                          location : _location,
                                          is_global_location : _is_global_location
                              } ) ) ).done( function() {
                                    $btn_el = $(this).find('.sek-add-content-button');
                                    $btn_el.attr('data-sek-is-first-section', true );
                                    $btn_el.fadeIn( 300 );
                              });
                        });
                  };//_printAddContentButtons

                  // fired on mousemove and scroll, every 50ms
                  var _sniffAndRevealButtons = function( position ) {
                        $( 'body').find('.sek-add-content-button').each( function() {
                              var btnWrapperRect = $(this)[0].getBoundingClientRect(),
                                  yPos = position.y,
                                  xPos = position.x,
                                  isCloseThreshold = 40,
                                  mouseToBottom = Math.abs( yPos - btnWrapperRect.bottom ),
                                  mouseToTop = Math.abs( btnWrapperRect.top - yPos ),
                                  mouseToRight = xPos - btnWrapperRect.right,
                                  mouseToLeft = btnWrapperRect.left - xPos,
                                  isCloseVertically = ( mouseToBottom < isCloseThreshold ) || ( mouseToTop < isCloseThreshold ),
                                  isCloseHorizontally =  ( mouseToRight > 0 && mouseToRight < isCloseThreshold ) || ( mouseToLeft > 0 && mouseToLeft < isCloseThreshold ),
                                  isInHorizontally = xPos <= btnWrapperRect.right && btnWrapperRect.left <= xPos,
                                  isInVertically = yPos >= btnWrapperRect.top && btnWrapperRect.bottom >= yPos;

                              // var html = '';
                              // html += ' | mouseToBottom : ' + mouseToBottom + ' | mouseToTop : ' + mouseToTop;
                              // html += ' isCloseVertically : ' + isCloseVertically + ' | isInVertically : ' + isInVertically;
                              // $(this).html( '<span style="font-size:12px">' + html + '</span>');

                              $(this).toggleClass(
                                    'sek-mouse-is-close',
                                    ( isCloseVertically || isInVertically ) && ( isCloseHorizontally || isInHorizontally )
                              );
                        });
                  };

                  // Print / remove ui according to the mouse position
                  // The mouse position is provided by self.mouseMovedRecently()
                  // If the ui is expanded, remove after a delay to let user access all ui buttons, even those outside the $level.
                  // => the ui can be "outside" ( <=> out vertically and horizontally ) when columns are narrow.
                  var _sniffLevelsAndPrintUI = function( position, $candidateForRemoval ) {
                        var collectionOfLevelsToWalk = [], sniffCase;
                        if ( _.isUndefined( $candidateForRemoval ) || $candidateForRemoval.length < 1 ) {
                              // data-sek-preview-level-guid has been introduced in https://github.com/presscustomizr/nimble-builder/issues/494
                              // to fix a wrong UI generation leading to user unable to edit content
                              $('body').find('[data-sek-level][data-sek-preview-level-guid="' + sekPreviewLocalized.previewLevelGuid +'"]').each( function() {
                                    collectionOfLevelsToWalk.push( $(this) );
                              });
                              sniffCase = 'printOrScheduleRemoval';
                        } else {
                              collectionOfLevelsToWalk.push( $candidateForRemoval );
                              sniffCase = 'mayBeRemove';
                        }

                        _.each( collectionOfLevelsToWalk, function( $levelToWalk ) {
                              var levelWrapperRect = $levelToWalk[0].getBoundingClientRect(),
                                isInHorizontally = position.x <= levelWrapperRect.right && levelWrapperRect.left <= position.x,
                                isInVertically = position.y >= levelWrapperRect.top && levelWrapperRect.bottom >= position.y,
                                $levelEl = $levelToWalk;

                              switch( sniffCase ) {
                                    case 'mayBeRemove' :
                                          $levelEl.data('sek-ui-removal-scheduled', false );
                                          if ( ! isInHorizontally || ! isInVertically ) {
                                                removeLevelUI.call( $levelEl );
                                          }
                                    break;
                                    case 'printOrScheduleRemoval' :
                                          if ( isInHorizontally && isInVertically ) {
                                                $levelEl.data('sek-ui-removal-scheduled', false );
                                                self.printLevelUI( $levelEl );
                                          } else {
                                                if ( true !== $levelEl.data('sek-ui-removal-scheduled') ) {
                                                      $levelEl.data('sek-ui-removal-scheduled', true );
                                                      if ( $levelEl.children('.sek-dyn-ui-wrapper').find('.sek-is-expanded').length < 1 ) {
                                                            _sniffLevelsAndPrintUI( position, $levelEl );
                                                      } else {
                                                            _.delay( function() {
                                                                  if ( true === $levelEl.data('sek-ui-removal-scheduled') ) {
                                                                        // using the latest self.mouseMovedRecently(), instead of the initial "position" param, makes sure we don't miss the latest mouse movements
                                                                        _sniffLevelsAndPrintUI( self.mouseMovedRecently(), $levelEl );
                                                                  }
                                                            }, 3500 );
                                                      }
                                                }
                                          }
                                    break;
                              }
                        });//collectionOfLevelsToWalk.each
                  };


                  // SCHEDULE
                  // - the printing / removal of the add content button
                  // - the printing of the level's UI
                  self.mouseMovedRecently = new api.Value( {} );
                  self.mouseMovedRecently.bind( function( position ) {
                        if ( ! _.isEmpty( position) ) {
                              // print the buttons ( display:none)
                              _printAddContentButtons();
                              // sniff sections around pointer and reveal add content button for the collection of candidates
                              _sniffAndRevealButtons( position );
                              // sniff levels and print UI
                              _sniffLevelsAndPrintUI( position );
                        } else {
                              // Mouse didn't move recently?
                              // => remove all UIs
                              $('body').stop( true, true ).find('.sek-add-content-button').each( function() {
                                    $(this).fadeOut( {
                                          duration : 200,
                                          complete : function() { $(this).remove(); }
                                    });
                              });
                              $('body').stop( true, true ).find('[data-sek-level]').each( function() {
                                    // preserve if the ui menu is expanded, otherwise remove
                                    if ( $(this).children('.sek-dyn-ui-wrapper').find('.sek-is-expanded').length < 1 ) {
                                          removeLevelUI.call( $(this) );
                                    }
                              });
                        }
                  });
                  // @return void()
                  var resetMouseMoveTrack = function() {
                        clearTimeout( $(window).data('_scroll_move_timer_') );
                        self.mouseMovedRecently.set({});
                  };

                  $(window).on( 'mousemove scroll', _.throttle( function( evt ) {
                        self.mouseMovedRecently( { x : evt.clientX, y : evt.clientY } );
                        clearTimeout( $(window).data('_scroll_move_timer_') );
                        $(window).data('_scroll_move_timer_', setTimeout(function() {
                              self.mouseMovedRecently.set({});
                        }, 4000 ) );
                  }, 50 ) );

                  // Always reset the move timer and the mouseMove Value when
                  // - a dragging action is started
                  // - a section is added <= fixes the addition of multiple "Add Section" button in the same location
                  api.preview.bind( 'sek-drag-start', function() {
                        resetMouseMoveTrack();
                  });

                  $( 'body').on( 'sek-section-added', '[data-sek-level="location"]', function( evt, params  ) {
                        resetMouseMoveTrack();
                  });

                  return this;
            }//setupUiHoverVisibility

      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            //Fired on Dom ready in initialize()
            scheduleUiClickReactions : function() {
                  var self = this;

                  $('body').on('click', function( evt ) {
                        // First clean any currently highlighted target drop zone
                        // implemented for double-click insertion https://github.com/presscustomizr/nimble-builder/issues/317
                        api.preview.send( 'sek-clean-target-drop-zone' );

                        var clickedOn = 'inactiveZone',
                            $el = $(evt.target),
                            $hookLocation = $el.closest('[data-sek-level="location"][data-sek-preview-level-guid="' + sekPreviewLocalized.previewLevelGuid +'"]'),
                            $closestLevelWrapper = $el.closest('[data-sek-preview-level-guid="' + sekPreviewLocalized.previewLevelGuid +'"]'),
                            $closestActionIcon = $el.closest('[data-sek-click-on]'),
                            _action,
                            _location_id = $hookLocation.data('sek-id'),
                            _level = $closestLevelWrapper.data('sek-level'),
                            _id = $closestLevelWrapper.data('sek-id');

                        if ( 'add-content' == $el.data('sek-click-on') || ( $el.closest('[data-sek-click-on]').length > 0 && 'add-content' == $el.closest('[data-sek-click-on]').data('sek-click-on') ) ) {
                              clickedOn = 'addContentButton';
                        } else if ( ! _.isEmpty( $el.data( 'sek-click-on' ) ) || $closestActionIcon.length > 0 ) {
                              clickedOn = 'UIIcon';
                        } else if ( 'module' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'moduleWrapper';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') && true === $closestLevelWrapper.data('sek-no-modules') ) {
                              clickedOn = 'noModulesColumn';
                        } else if ( $el.hasClass('sek-to-json') ) {
                              clickedOn = 'sekToJson';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'columnOutsideModules';
                        } else if ( 'section' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'sectionOutsideColumns';
                        } else if ( ! _.isEmpty( $el.data( 'sek-add' ) ) ) {
                              clickedOn = 'addSektion';
                        } else if ( $el.hasClass('sek-wp-content-wrapper') || $el.hasClass( 'sek-wp-content-dyn-ui') ) {
                              clickedOn = 'wpContent';
                        } else if ( $el.hasClass('sek-edit-wp-content') ) {
                              clickedOn = 'editWpContent';
                        } else {
                              clickedOn = 'inactiveZone';
                        }

                        if ( _.isEmpty( _location_id ) ) {
                            self.errare( '::scheduleUiClickReactions => error location id can not be empty' );
                        }

                        switch( clickedOn ) {
                              case 'addContentButton' :
                                    var is_first_section = true === $el.closest('[data-sek-is-first-section]').data('sek-is-first-section');

                                    api.preview.send( 'sek-add-section', {
                                          location : _location_id,
                                          level : 'section',
                                          before_section : $el.closest('[data-sek-before-section]').data('sek-before-section'),
                                          after_section : $el.closest('[data-sek-after-section]').data('sek-after-section'),
                                          is_first_section : is_first_section,
                                          send_to_preview : ! is_first_section
                                    });
                              break;
                              case 'UIIcon' :
                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'ERROR => sek-front-preview => No valid level dom element found' );
                                    }
                                    _action = $el.closest('[data-sek-click-on]').data('sek-click-on');

                                    if ( _.isEmpty( _action ) ) {
                                        throw new Error( 'Invalid action' );
                                    }
                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'ERROR => sek-front-preview => No valid level id found' );
                                    }
                                    self._send_( $el, {
                                          action : _action,
                                          location : _location_id,
                                          level : _level,
                                          id : _id,
                                          was_triggered : false //<= indicates that the user clicked.
                                    });
                              break;
                              case 'moduleWrapper' :
                                    // stop here if the ui icons block was clicked
                                    if ( $el.parent('.sek-dyn-ui-icons').length > 0 )
                                      return;

                                    self._send_( $el, {
                                          action : 'edit-module',
                                          level : _level,
                                          id : _id
                                    });
                              break;
                              case 'noModulesColumn' :
                                    // stop here if the ui icons block was clicked
                                    if ( $el.parent('.sek-dyn-ui-icons').length > 0 )
                                      return;

                                    self._send_( $el, { action : 'pick-content', content_type : 'module', level : _level , id : _id } );
                              break;
                              case 'columnOutsideModules' :
                              case 'sectionOutsideColumns' :
                                    self._send_( $el, {
                                        action : 'edit-options',
                                        location : _location_id,
                                        level : _level,
                                        id : _id
                                    });
                              break;
                              case 'addSektion' :
                                    api.preview.send( 'sek-add-section', {
                                          location : _location_id,
                                          level : $el.data('sek-add')
                                    });
                              break;
                              case 'sekToJson' :
                                    api.preview.send( 'sek-to-json', { id : _id } );
                              break;
                              case 'wpContent' :
                                    api.preview.send( 'sek-notify', {
                                          type : 'info',
                                          duration : 8000,
                                          message : sekPreviewLocalized.i18n['This content has been created with the WordPress editor.']
                                    });
                              break;
                              case 'editWpContent' :
                                    // note : the edit url is printed as a data attribute to prevent being automatically parsed by wp when customizing and turned into a changeset url
                                    var edit_url = $el.closest('[data-sek-wp-edit-link]').data('sek-wp-edit-link');
                                    if ( ! _.isEmpty( edit_url ) ) {
                                          window.open( edit_url,'_blank' );
                                    }

                              break;
                              case 'inactiveZone' :
                                    api.preview.send( 'sek-click-on-inactive-zone');//<= for example, collapses the tinyMce editor if expanded
                                    //self._send_( $el, { action : 'pick-content' } );
                              break;
                        }

                        //console.log('CLICKED ?', clickedOn );
                  });//$('body').on('click', function( evt ) {}

            },//scheduleUserReactions()


            _send_ : function( $el, params ) {
                  //console.log('IN _send_', $el, params );
                  var clonedParams = $.extend( true, {}, params ),
                      syncedTinyMceInputId = '',
                      $moduleWrapper = $el.closest('div[data-sek-level="module"]'),
                      _module_type_ = 'module' === params.level ? $moduleWrapper.data( 'sek-module-type') : '';

                  if ( 'module' === params.level ) {
                        if ( 'czr_tiny_mce_editor_module' === _module_type_ ) {
                              syncedTinyMceInputId = $moduleWrapper.find('div[data-sek-input-id]').length > 0 ? $moduleWrapper.find('div[data-sek-input-id]').data('sek-input-id') : '';
                        }
                  }
                  api.preview.send( 'sek-' + params.action, _.extend( {
                        location : params.location,
                        level : params.level,
                        id : params.id,
                        content_type : $el.data( 'sek-content-type'),
                        module_type : _module_type_,
                        in_column : $el.closest('div[data-sek-level="column"]').length > 0 ? $el.closest('div[data-sek-level="column"]').data( 'sek-id') : '',
                        in_sektion : $el.closest('div[data-sek-level="section"]').length > 0 ? $el.closest('div[data-sek-level="section"]').data( 'sek-id') : '',
                        clicked_input_type : $el.closest('div[data-sek-input-type]').length > 0 ? $el.closest('div[data-sek-input-type]').data('sek-input-type') : '',
                        clicked_input_id : $el.closest('div[data-sek-input-id]').length > 0 ? $el.closest('div[data-sek-input-id]').data('sek-input-id') : '',
                        was_triggered : params.was_triggered,
                        // the syncedTinyMceInputId is used in the control::setupTinyMceEditor()
                        // Fixes : https://github.com/presscustomizr/nimble-builder/issues/251
                        syncedTinyMceInputId : syncedTinyMceInputId
                  }, clonedParams ) );
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            //Fired in ::initialize()
            setupLoader : function() {
                  var self = this;
                  // Cache our loader
                  this._css_loader_html = '<div class="sek-css-loader sek-mr-loader" style="display:none"><div></div><div></div><div></div></div>';

                  // Loader Cleaning <= the element printed when refreshing a level
                  // @see ::mayBePrintLoader
                  $( 'body').on([
                        'sek-modules-refreshed',
                        'sek-columns-refreshed',
                        'sek-section-added',
                        'sek-level-refreshed',
                        'sek-stylesheet-refreshed',
                        'sek-ajax-error'
                  ].join(' '), function( evt ) {
                        self.cleanLoader();
                  });
            },

            // @return void()
            // Insert a clone ( same dimensions ) div element of a level currently being refreshed, including a centered loading animation
            // + adds a .sek-refreshing css class to the element being refreshed
            //
            // Invoked when
            // - user click on an icon action in the preview that trigger a partial reflush of the DOM. For example, adding a column, duplicating a module, etc.
            // - a module / section is dropped in the preview
            // - a module is being edited
            // - a column is resized
            // - on 'sek-maybe-print-loader'
            // @params {
            //    element : $(),
            //    action : '',
            //    level,
            //    loader_located_in_level_id
            // }
            mayBePrintLoader : function( params ) {
                  var self = this,
                      levelIdForTheLoader = params.loader_located_in_level_id;

                  if ( ! _.isEmpty( levelIdForTheLoader ) ) {
                        var $levelElementForTheLoader = $('[data-sek-id="' + levelIdForTheLoader +'"]');
                        if ( $levelElementForTheLoader.length > 0 && 1 > $('.sek-level-clone ').length ) {
                              $levelClone = $('<div>', { class : 'sek-level-clone' });
                              // blur all children levels
                              $levelElementForTheLoader.find('[data-sek-level]').each( function() {
                                    $(this).addClass('sek-refreshing');
                              });

                              // print the absolute positionned clone on top
                              $levelElementForTheLoader.prepend( $levelClone );
                              $levelClone.css({
                                    width : $levelElementForTheLoader.outerWidth() +'px',
                                    height : $levelElementForTheLoader.outerHeight() + 'px'
                              }).append( self._css_loader_html ).find('.sek-css-loader').fadeIn( 'fast' );

                              // Start the countdown for auto-cleaning
                              clearTimeout( $.data( this, '_nimble_loader_active_timer_') );
                              $.data( this, '_nimble_loader_active_timer_', setTimeout(function() {
                                    self.cleanLoader();
                              }, 4000 ) );
                        }
                  }
                  if ( true === params.fullPageLoader ) {
                        var $loaderWrapper = $('<div>', { id : 'nimble-full-page-loader-wrapper', class: 'white-loader'} );
                        $('body').append($loaderWrapper);
                        $loaderWrapper.fadeIn('fast').append( self._css_loader_html ).find('.sek-css-loader').fadeIn( 'fast' );
                        // Blur locations
                        $('[data-sek-level="location"]').each( function() {
                              $(this).addClass('sek-blur');
                        });

                        // Start the countdown for auto-cleaning
                        clearTimeout( $.data( this, '_nimble_full_page_loader_active_timer_') );
                        $.data( this, '_nimble_full_page_loader_active_timer_', setTimeout(function() {
                              self.cleanLoader( { cleanFullPageLoader : true });
                        }, 6000 ) );
                  }
            },

            // scheduled in ::initialize(), on 'sek-modules-refreshed sek-columns-refreshed sek-section-added sek-refresh-level'
            // invoked in ::mayBePrintLoader() in an auto-clean scenario
            // or on wp.customize.send('sek-clean-loader', { cleanFullPageLoader : true })
            // {
            //  cleanFullPageLoader : true
            // }
            cleanLoader : function( params ) {
                  var self = this;
                  $('.sek-level-clone').remove();
                  $('[data-sek-level]').each( function() {
                        $(this).removeClass('sek-refreshing');
                  });
                  params = params || {};
                  if ( true === params.cleanFullPageLoader ) {
                        // Unblur locations
                        $('[data-sek-level="location"]').each( function() {
                              $(this).removeClass('sek-blur');
                        });
                        $('#nimble-full-page-loader-wrapper').remove();
                  }
            }

      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired in ::initialize()
            schedulePanelMsgReactions : function() {
                  var self = this,
                      apiParams = {},
                      uiParams = {},
                      msgCollection = {
                            // DOM MODIFICATION CASES
                            'sek-add-section' : 'ajaxAddSektion',
                            'sek-add-content-in-new-sektion' : 'ajaxAddSektion',
                            'sek-add-content-in-new-nested-sektion' : 'ajaxAddSektion',
                            'sek-add-column' : 'ajaxRefreshColumns',
                            'sek-add-module' : 'ajaxRefreshModulesAndNestedSections',
                            'sek-refresh-stylesheet' : 'ajaxRefreshStylesheet',

                            'sek-resize-columns' : 'ajaxResizeColumns',

                            'sek-maybe-print-loader' : function( params ) {
                                  try { self.mayBePrintLoader( params ); } catch( er ) {
                                        api.errare( 'sek-clean-loader => error', er );
                                  }
                            },
                            'sek-clean-loader' : function( params ) {
                                  try { self.cleanLoader( params ); } catch( er ) {
                                        api.errare( 'sek-clean-loader => error', er );
                                  }
                            },
                            'sek-remove' : function( params ) {
                                  var removeCandidateId = params.apiParams.id,
                                      $candidateEl = $('div[data-sek-id="' + removeCandidateId + '"]' ),
                                      dfd;
                                  switch ( params.apiParams.action ) {
                                        case 'sek-remove-section' :
                                              // will be cleaned on ajax.done()
                                              // @see ::scheduleTheLoaderCleaning
                                              self.mayBePrintLoader({
                                                    loader_located_in_level_id : params.apiParams.location
                                              });
                                              if ( true === params.apiParams.is_nested ) {
                                                    dfd = self.ajaxRefreshModulesAndNestedSections( params );
                                              } else {
                                                    if ( _.isEmpty( removeCandidateId ) || 1 > $candidateEl.length ) {
                                                          self.errare( 'reactToPanelMsg => sek-remove => invalid candidate id => ', removeCandidateId );
                                                    }
                                                    $('body').find( $candidateEl ).remove();
                                                    // say it
                                                    // listened to clean the loader just in time
                                                    $('[data-sek-id="' + params.apiParams.location + '"]').trigger( 'sek-level-refreshed');
                                              }
                                              //self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                        case 'sek-remove-column' :
                                              dfd = self.ajaxRefreshColumns( params );
                                        break;
                                        case 'sek-remove-module' :
                                              dfd = self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                        default :
                                        break;
                                  }
                                  // We should always return a promise
                                  return _.isEmpty( dfd ) ? $.Deferred( function() { this.resolve(); } ) : dfd;
                            },

                            'sek-duplicate' : function( params ) {
                                  var dfd;
                                  switch ( params.apiParams.action ) {
                                        case 'sek-duplicate-section' :
                                              // replace the original id by the new cloneId registered in the main setting, and sent by the panel
                                              params.apiParams.id = params.cloneId;
                                              dfd = self.ajaxAddSektion( params );
                                        break;
                                        case 'sek-duplicate-column' :
                                              // replace the original id by the new cloneId registered in the main setting, and sent by the panel
                                              params.apiParams.id = params.cloneId;
                                              dfd = self.ajaxRefreshColumns( params );
                                        break;
                                        case 'sek-duplicate-module' :
                                              // replace the original id by the new cloneId registered in the main setting, and sent by the panel
                                              params.apiParams.id = params.cloneId;
                                              dfd = self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                  }
                                  return dfd;
                            },

                            // Re-print a level
                            // Can be invoked when setting the section layout option boxed / wide, when we need to add a css class server side
                            // @params {
                            //   action : 'sek-refresh-level',
                            //   level : params.level,
                            //   id : params.id
                            // }
                            'sek-refresh-level' : function( params ) {
                                  // will be cleaned on 'sek-module-refreshed'
                                  self.mayBePrintLoader({
                                        loader_located_in_level_id : params.apiParams.id
                                  });
                                  return self.doAjax({
                                        location_skope_id : params.location_skope_id,
                                        local_skope_id : params.local_skope_id,
                                        action : 'sek_get_content',
                                        id : params.apiParams.id,
                                        level : params.apiParams.level,
                                        sek_action : params.apiParams.action
                                  }).fail( function( _r_ ) {
                                        self.errare( 'ERROR reactToPanelMsg => sek-refresh-level => ' , _r_ );
                                        $( '[data-sek-id="' + params.apiParams.id + '"]' ).trigger( 'sek-ajax-error' );
                                  }).done( function( _r_ ) {
                                        var html_content = '';
                                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                                        if ( _r_.data && _r_.data.contents ) {
                                              html_content = _r_.data.contents;
                                        } else {
                                              self.errare( 'SekPreviewPrototype::sek-refresh-level => ajax_response.data.contents is undefined ', _r_ );
                                              self.errare( 'params ?', params );
                                        }
                                        // _r_ is an array
                                        // @see SEK_Front_Ajax::sek_get_level_content_for_injection
                                        // _r_ = array(
                                        //     'contents' => $html,
                                        //     'setting_validities' => $exported_setting_validities
                                        // );
                                        var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.id + '"></span>',
                                            $currentLevelEl = $( 'div[data-sek-id="' + params.apiParams.id + '"]' );
                                        if ( $currentLevelEl.length < 1 ) {
                                              self.errare( 'reactToPanelMsg => sek-refresh-level ajax done => the level to refresh is not rendered in the page', _r_ );
                                              return;
                                        }
                                        $currentLevelEl.before( placeholderHtml );
                                        var $placeHolder = $( '[data-sek-placeholder-for="' + params.apiParams.id + '"]' );

                                        $currentLevelEl.remove();

                                        if ( _.isUndefined( html_content ) ) {
                                              self.errare( 'reactToPanelMsg => sek-refresh-level ajax done => missing html_content', _r_ );
                                        } else {
                                              $placeHolder.after( html_content );
                                        }

                                        $placeHolder.remove();

                                        //=> 'sek-level-refreshed' is listened to ( for example ) clean the loader overlay in time
                                        $( '[data-sek-id="' + params.apiParams.id + '"]' ).trigger( 'sek-level-refreshed', { level : params.apiParams.level, id : params.apiParams.id } );

                                        // When completing actions 'sek-move-section-down' && 'sek-move-section-up', a 'sek-refresh-level' is triggered.
                                        // We pass the moved_level_id so we can focus on it after it's been re-located in the DOM
                                        // implemented for https://github.com/presscustomizr/nimble-builder/issues/471
                                        if ( params.apiParams.moved_level_id ) {
                                              api.preview.trigger( 'sek-animate-to-level', { id : params.apiParams.moved_level_id } );
                                        }
                                  });
                            },






                            // EDITING MODULE AND OPTIONS
                            'sek-move' : function( params ) {
                                  switch ( params.apiParams.action ) {
                                        // case 'sek-move-section' :
                                        //       //always re-render the source sektion and target sektion if different
                                        //       //=> this will ensure a reset of the column's widths
                                        //       if ( params.apiParams.from_location != params.apiParams.to_location ) {
                                        //             var paramsForSourceSektion = $.extend( true, {}, params );
                                        //             var paramsForTargetSektion = $.extend( true, {}, params );

                                        //             // SOURCE SEKTION
                                        //             // if the source sektion has been emptied, let's populate it with a new column
                                        //             if ( $('[data-sek-id="' + params.apiParams.from_sektion +'"]', '.sektion-wrapper').find('div[data-sek-level="column"]').length < 1 ) {
                                        //                   api.preview.send( 'sek-add-column', {
                                        //                         in_sektion : params.apiParams.from_sektion,
                                        //                         autofocus:false//<= because we want to focus on the column that has been moved away from the section
                                        //                   });
                                        //             } else {
                                        //                   paramsForSourceSektion.apiParams =  _.extend( paramsForSourceSektion.apiParams, {
                                        //                         in_sektion : params.apiParams.from_sektion,
                                        //                         action : 'sek-refresh-columns-in-sektion'
                                        //                   });
                                        //                   self.ajaxRefreshColumns( paramsForSourceSektion );
                                        //             }

                                        //             // TARGET SEKTION
                                        //             paramsForTargetSektion.apiParams =  _.extend( paramsForTargetSektion.apiParams, {
                                        //                   in_sektion : params.apiParams.to_sektion,
                                        //                   action : 'sek-refresh-columns-in-sektion'
                                        //             });
                                        //             self.ajaxRefreshColumns( paramsForTargetSektion );

                                        //       }
                                        // break;
                                        case 'sek-move-column' :
                                              //always re-render the source sektion and target sektion if different
                                              //=> this will ensure a reset of the column's widths
                                              if ( params.apiParams.from_sektion != params.apiParams.to_sektion ) {
                                                    var paramsForSourceSektion = $.extend( true, {}, params );
                                                    var paramsForTargetSektion = $.extend( true, {}, params );

                                                    // SOURCE SEKTION
                                                    // if the source sektion has been emptied, let's populate it with a new column
                                                    if ( $('[data-sek-id="' + params.apiParams.from_sektion +'"]', '.sektion-wrapper').find('div[data-sek-level="column"]').length < 1 ) {
                                                          api.preview.send( 'sek-add-column', {
                                                                in_sektion : params.apiParams.from_sektion,
                                                                autofocus:false//<= because we want to focus on the column that has been moved away from the section
                                                          });
                                                    } else {
                                                          paramsForSourceSektion.apiParams =  _.extend( paramsForSourceSektion.apiParams, {
                                                                in_sektion : params.apiParams.from_sektion,
                                                                action : 'sek-refresh-columns-in-sektion'
                                                          });
                                                          self.ajaxRefreshColumns( paramsForSourceSektion );
                                                    }

                                                    // TARGET SEKTION
                                                    paramsForTargetSektion.apiParams =  _.extend( paramsForTargetSektion.apiParams, {
                                                          in_sektion : params.apiParams.to_sektion,
                                                          action : 'sek-refresh-columns-in-sektion'
                                                    });
                                                    self.ajaxRefreshColumns( paramsForTargetSektion );

                                              }
                                        break;
                                        case 'sek-move-module' :
                                              var paramsForSourceColumn = $.extend( true, {}, params ),
                                                  paramsForTargetColumn = $.extend( true, {}, params );
                                              // SOURCE COLUMN
                                              //always re-render the source column if different than the target column
                                              //=> this will ensure that we have the drop-zone placeholder printed for a no-module column
                                              //+ will refresh the sortable()
                                              if ( paramsForSourceColumn.apiParams.from_column != paramsForSourceColumn.apiParams.to_column ) {
                                                    paramsForSourceColumn.apiParams = _.extend( paramsForSourceColumn.apiParams, {
                                                          in_column : paramsForSourceColumn.apiParams.from_column,
                                                          in_sektion : paramsForSourceColumn.apiParams.from_sektion,
                                                          action : 'sek-refresh-modules-in-column'
                                                    });
                                                    self.ajaxRefreshModulesAndNestedSections( paramsForSourceColumn );
                                              }

                                              // TARGET COLUMN
                                              params.apiParams = _.extend( paramsForTargetColumn.apiParams, {
                                                    in_column : paramsForTargetColumn.apiParams.to_column,
                                                    in_sektion : paramsForTargetColumn.apiParams.to_sektion,
                                                    action : 'sek-refresh-modules-in-column'
                                              });
                                              self.ajaxRefreshModulesAndNestedSections( paramsForTargetColumn );

                                              // Re-instantiate sortable for the target column
                                              $('[data-sek-id="' + params.apiParams.to_column +'"]', '.sektion-wrapper').find('.sek-column-inner').sortable( "refresh" );
                                        break;
                                  }
                            },





                            // GENERATE UI ELEMENTS
                            // when the options ui has been generated in the panel for a level, we receive back this msg
                            // 'sek-generate-level-options-ui' : function( params ) {
                            //       api.infoLog('PANEL REACT? ', 'sek-generate-level-options-ui', params );
                            // },

                            'sek-edit-options' : function( params ) {
                                  // ::activeLevelUI is declared in ::initialized()
                                  self.activeLevelUI( params.uiParams.id );
                            },
                            'sek-edit-module' : function( params ) {
                                  // ::activeLevelUI is declared in ::initialized()
                                  self.activeLevelUI( params.uiParams.id );
                            },









                            // @params =  {
                            //   location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                            //   apiParams : apiParams,
                            //   uiParams : uiParams
                            // }
                            // uiParams = {
                            //       action : 'sek-edit-module',
                            //       level : params.level,
                            //       id : params.id,
                            //       in_sektion : params.in_sektion,
                            //       in_column : params.in_column,
                            //       options : params.options || []
                            // };
                            //
                            // when the module ui has been generated in the panel, we receive back this msg
                            //'sek-generate-module-ui' : function( params ) {},

                            //@params {
                            //    type : module || preset_section,
                            // }
                            'sek-drag-start' : function( params ) {
                                  // append the drop zones between sections
                                  var i = 1, previousSectionIsEmpty = false;
                                  $('[data-sek-level="location"]').children('[data-sek-level="section"]').each( function() {
                                        var sectionId = $(this).data('sek-id'),
                                            columnNb = $(this).find('[data-sek-level="column"]').length,
                                            moduleNb = $(this).find('[data-sek-level="module"]').length,
                                            isEmptySection = columnNb < 2 && moduleNb < 1,
                                            canPrintBefore = ! previousSectionIsEmpty && ! isEmptySection;

                                        // Print a dropzone before if the previous section and current section are not empty.
                                        if ( canPrintBefore && $('[data-drop-zone-before-section="' + sectionId +'"]').length < 1 ) {
                                              $(this).before(
                                                '<div class="sek-content-' + params.type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-sections" data-drop-zone-before-section="' + sectionId +'"></div>'
                                              );
                                        }
                                        // After the last one
                                        if ( ! isEmptySection && i == $('.sektion-wrapper').children('[data-sek-level="section"]').length ) {
                                              $(this).after(
                                                '<div class="sek-content-' + params.type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-sections" data-drop-zone-after-section="' + sectionId +'"></div>'
                                              );
                                        }
                                        i++;
                                        previousSectionIsEmpty = isEmptySection;
                                  });

                                  // Append the drop zone in empty locations
                                  $('.sek-empty-location-placeholder').each( function() {
                                        $.when( $(this).append(
                                              '<div class="sek-content-' + params.type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="in-empty-location"></div>'
                                        ));
                                  });

                                  // Append a drop zone between modules and nested sections in columns
                                  if ( 'module' ==  params.type ) {
                                        $('[data-sek-level="column"]').each( function() {
                                              // Our candidates are the modules and nested section which are direct children of this column
                                              // We don't want to include the modules inserted in the columns of a nested section.
                                              var $modules_and_nested_sections = $(this).children('.sek-column-inner').children( '[data-sek-level="module"]' );
                                              var $nested_sections = $(this).children('.sek-column-inner').children( '[data-sek-is-nested="true"]' );
                                              $modules_and_nested_sections = $modules_and_nested_sections.add( $nested_sections );

                                              var j = 1;
                                              $modules_and_nested_sections.each( function() {
                                                    // Always before
                                                    if ( $('[data-drop-zone-before-module-or-nested-section="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                                          $(this).before(
                                                              '<div class="sek-content-module-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-modules-and-nested-sections" data-drop-zone-before-module-or-nested-section="' + $(this).data('sek-id') +'"></div>'
                                                          );
                                                    }
                                                    // After the last one
                                                    if (  j == $modules_and_nested_sections.length && $('[data-drop-zone-after-module-or-nested-section="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                                          $(this).after(
                                                            '<div class="sek-content-module-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-modules-and-nested-sections" data-drop-zone-after-module-or-nested-section="' + $(this).data('sek-id') +'"></div>'
                                                          );
                                                    }
                                                    j++;
                                              });
                                        });
                                  }


                                  // toggle a parent css classes controlling some css rules @see preview.css
                                  $('body').addClass('sek-dragging');

                                  // Reveal all dynamic dropzones after a delay
                                  _.delay( function() {
                                        $('.sek-dynamic-drop-zone').css({ opacity : 1 });
                                  }, 100 );

                            },
                            // is sent on dragend and drop
                            'sek-drag-stop' : function( params ) {
                                  $('body').removeClass('sek-dragging');
                                  // Clean any remaining placeholder
                                  $('.sortable-placeholder').remove();

                                  // Remove the drop zone dynamically add on sek-drag-start
                                  $('.sek-dynamic-drop-zone').remove();
                            },















                            // FOCUS
                            // Sent from the panel when duplicating a section level for example
                            'sek-animate-to-level' : function( params ) {
                                  var $elToFocusOn = $('[data-sek-id="' + params.id + '"]' );
                                  if ( $elToFocusOn.length > 0 ) {
                                        $('html, body').animate({
                                              scrollTop : $elToFocusOn.offset().top - 100
                                        }, 200 );
                                  }
                            },


                            // LEVEL UI's
                            'sek-clean-level-uis' : function( params ) {
                                  $('.sek-dyn-ui-wrapper').each( function() {
                                        $(this).remove();
                                  });
                            },
                            // triggered when navigating the level tree
                            'sek-display-level-ui' : function( params ) {
                                  var $elToFocusOn = $('[data-sek-id="' + params.id + '"]' );
                                  if ( $elToFocusOn.length > 0 ) {
                                        //$elToFocusOn.trigger('click'); //<= the click is not needed anymore since June 2019, we trigger the generation of the level options on 'click' in the level tree
                                        self.printLevelUI($elToFocusOn);
                                  }
                            },


                            // DOUBLE CLICK INSERTION => HIGHLIGHTED TARGET
                            // implemented for double-click insertion
                            // https://github.com/presscustomizr/nimble-builder/issues/317
                            'sek-set-double-click-target' : function( params ) {
                                  // First clean any other highlighted target
                                  $('.sek-target-for-double-click-insertion').removeClass('sek-target-for-double-click-insertion');

                                  if ( _.isObject( params ) && params.id ) {
                                        var $elToHighlight = $('[data-sek-id="' + params.id + '"]' );
                                        if( 1 === $elToHighlight.length ) {
                                              $elToHighlight.addClass('sek-target-for-double-click-insertion');
                                        }
                                  }
                            },
                            'sek-reset-double-click-target' : function( params ) {
                                  $('.sek-target-for-double-click-insertion').removeClass('sek-target-for-double-click-insertion');
                            },

                            // introduced for https://github.com/presscustomizr/nimble-builder/issues/403
                            // this is fired for module with postMessage refresh, like text editor
                            // @see control::refreshMarkupWhenNeededForInput()
                            // July 2019 => since the new UI rendering with JS template ( https://github.com/presscustomizr/nimble-builder/issues/465 ), this action is fired too early when inserting a new module with postMessage refresh
                            // resulting in the target element not being rendered on first call
                            'sek-update-html-in-selector' : function( params ) {
                                  var $level_el = $('[data-sek-id="' + params.id + '"]' ),
                                      $target_el;

                                  // for multi-item modules, the changed item id is passed
                                  if ( !_.isEmpty( params.changed_item_id ) ) {
                                        $target_el = $( '[data-sek-item-id="' + params.changed_item_id + '"] ' + params.selector, $level_el);
                                  } else {
                                        $target_el = $(params.selector, $level_el);
                                  }

                                  if ( $level_el.length > 0 && $target_el.length > 0 ) {
                                        $target_el.html( params.html );
                                  } else {
                                        self.errare( 'reactToPanelMsg => sek-update-html-in-selector => missing level or target dom element', params );
                                  }
                            }

                      };//msgCollection

                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.preview.bind( msgId, function( params ) {
                              params = _.extend( {
                                  location_skope_id : '',
                                  apiParams : {},
                                  uiParams : {}
                              }, params || {} );

                              // If the ajax response is an array formed this way ( @see sek-refresh-level case ) :
                              // @see SEK_Front_Ajax::sek_get_level_content_for_injection
                              // _ajaxResponse_ = array(
                              //     'contents' => $html,
                              //     'setting_validities' => $exported_setting_validities
                              // );
                              // Then we send an additional setting-validity message to the control panel
                              // This is the same mechanism used by WP to handle the setting validity of the partial refresh

                              var sendSuccessDataToPanel = function( _ajaxResponse_ ) {
                                    // always send back the {msgId}_done message, so the control panel can fire the "complete" callback.
                                    // @see api.czr_sektions::reactToPreviewMsg
                                    api.preview.send( [ msgId, 'done'].join('_'), params );

                                    // For multi-items module, when the level is refreshed, we want to focus on the changed_item
                                    // @see CZRSeksPrototype::doSektionThinksOnApiReady
                                    if ( params.apiParams.is_multi_items && params.apiParams.action === 'sek-refresh-level' ) {
                                          api.preview.send( 'multi-items-module-refreshed', params );
                                    }

                                    if ( _.isUndefined( _ajaxResponse_ ) )
                                      return;

                                    if ( _ajaxResponse_.data && _ajaxResponse_.data.setting_validities ) {
                                          api.preview.send( 'selective-refresh-setting-validities', _ajaxResponse_.data.setting_validities );
                                    }
                              };
                              // the action being processed is added as a css class to the body of the preview
                              // it's used to enable/disable specific css properties during the action
                              // for example, we don't want css transitions while duplicating or removing a column
                              $('body').addClass( msgId );
                              try {
                                    $.when( _.isFunction( callbackFn ) ? callbackFn( params ) : self[callbackFn].call( self, params ) )
                                          .done( function( _ajaxResponse_ ) {
                                                sendSuccessDataToPanel( _ajaxResponse_ );
                                          })
                                          .fail( function() {
                                                api.preview.send( 'sek-notify', { type : 'error', duration : 10000, message : sekPreviewLocalized.i18n['Something went wrong, please refresh this page.'] });
                                          })
                                          .always( function( _ajaxResponse_ ) {
                                                $('body').removeClass( msgId );
                                          })
                                          .then( function() {
                                                api.preview.trigger( 'control-panel-requested-action-done', { action : msgId, args : params } );
                                          });
                              } catch( _er_ ) {
                                    self.errare( 'reactToPanelMsg => Error when firing the callback of ' + msgId , _er_  );
                                    $('body').removeClass( msgId );
                              }
                        });
                  });
            }//schedulePanelMsgReactions()
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // this method is used when creating or duplicating a sektion
            // @return a promise()
            ajaxAddSektion : function( params ) {
                  var self = this;
                  // will be cleaned on ajax.done()
                  // @see ::scheduleTheLoaderCleaning
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.location
                  });
                  return self.doAjax({
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        in_column : params.apiParams.in_column,
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : params.apiParams.action,
                        is_nested : params.apiParams.is_nested,

                        // The following params have been introduced when implementing support for multi-section pre-build sections
                        // @see https://github.com/presscustomizr/nimble-builder/issues/489
                        content_type : ( params.all_params && params.all_params.content_type ) ? params.all_params.content_type : null,
                        collection_of_preset_section_id : ( params.all_params && params.all_params.collection_of_preset_section_id ) ? params.all_params.collection_of_preset_section_id : []
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype::ajaxAddSektion => ajax_response.data.contents is undefined ', _r_ );
                              self.errare( 'params ?', params );
                        }

                        // Embed
                        // is it a nested sektion ?
                        var $parentColumn;
                        if ( params.apiParams.is_nested ) {
                              $parentColumn = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' );
                              if ( 1 > $parentColumn.length ) {
                                    self.errare( 'preview => reactToPanelMsg => sek-add-column => no DOM node for parent column => ', params.apiParams.in_column );
                              }
                              var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_column + '"></span>';
                              $parentColumn.before( placeholderHtml );
                              // remove and re-render the entire column
                              $parentColumn.remove();
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).after( html_content );
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).remove();
                        } else {
                              // DUPLICATE CASE
                              // Insert the clone section right after its cloned sister
                              if ( 'sek-duplicate-section' == params.apiParams.action && ! _.isEmpty( params.cloneId ) ) {
                                    $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).after( html_content );
                              }
                              // GENERATED WHEN ADDING A MODULE
                              else {
                                    // When a section has been created by adding a module ( @see sek-add-content-in-new-sektion )
                                    // we need to append it to a specific location
                                    // otherwise, we append it at the end of the section collection
                                    $beforeCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.before_section + '"]' );
                                    $afterCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.after_section + '"]' );

                                    if ( ! _.isEmpty( params.apiParams.before_section ) && $beforeCandidate.length > 0 ) {
                                          $beforeCandidate.before( html_content );
                                    } else if ( ! _.isEmpty( params.apiParams.after_section ) && $afterCandidate.length > 0 ) {
                                          $afterCandidate.after( html_content );
                                    } else {
                                          $( '[data-sek-id="' + params.apiParams.location + '"]').append( html_content );
                                    }
                              }
                        }

                        // When a section is duplicated, fire sortable for the inner-column modules
                        if ( 'sek-duplicate-section' == params.apiParams.action ) {
                              // re-instantiate sortable in the refreshed columns of the section
                              // + make columns resizable
                              $( 'div[data-sek-id="' + params.cloneId + '"]', '.sektion-wrapper').each( function() {
                                    $(this).trigger('sek-columns-refreshed');
                              });
                        }

                        // refresh sortable for the inner column if nested sektion case
                        if ( params.apiParams.is_nested ) {
                              self.makeModulesSortableInColumn( params.apiParams.in_column );
                              $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).each( function() {
                                    self.maybeMakeColumnResizableInSektion.call( this );
                              });
                        }

                        // say it to the parent sektion
                        //=> will be listened to clean the loader overlay just in time
                        if ( params.cloneId ) {
                              $( 'div[data-sek-id="' + params.cloneId + '"]' ).trigger('sek-section-added', params );
                        }
                        $( 'div[data-sek-id="' + params.apiParams.id + '"]' ).trigger('sek-section-added', params );

                        // added to fix resizable not instantiated when adding column modules
                        // @see https://github.com/presscustomizr/nimble-builder/issues/523
                        $( 'div[data-sek-id="' + params.apiParams.location + '"]' ).trigger('sek-location-refreshed', params );
                  }).fail( function( _r_ ) {
                        self.errare( 'ERROR in sek_get_html_for_injection ? ' , _r_ );
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            }//ajaxAddSektion()

      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // refresh column is used to
            // 1) Add a new column
            // 2) re-render the column collection in a sektion
            ajaxRefreshColumns : function( params ) {
                  var self = this;
                  // will be cleaned on 'sek-columns-refreshed'
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.in_sektion
                  });
                  return self.doAjax( {
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : params.apiParams.action// sek-add-column || sek-remove-column
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype::ajaxRefreshColumns => ajax_response.data.contents is undefined ', _r_ );
                              self.errare( 'params ?', params );
                        }

                        var $parentSektion = $( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' );
                        if ( 1 > $parentSektion.length ) {
                              self.errare( 'reactToPanelMsg => ' + params.apiParams.action + ' => no DOM node for parent sektion => ', params.apiParams.in_sektion );
                        }
                        var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_sektion + '"></span>';
                        $parentSektion.before( placeholderHtml );
                        // remove and re-render the entire sektion
                        $parentSektion.remove();
                        $( '.sektion-wrapper').find( '.sek-placeholder' ).after( html_content );
                        $( '.sektion-wrapper').find( '.sek-placeholder' ).remove();

                        // re-generate the stylesheet => this will take into account the reset width of each column
                        api.preview.trigger( 'sek-refresh-stylesheet', params );

                        // say it to the parent sektion
                        //=> will be listened to by the column to re-instantiate sortable, resizable
                        //=> also listened to clean the loader overalay in time
                        $('div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).trigger('sek-columns-refreshed', { in_sektion : params.apiParams.in_sektion } );
                  }).fail( function( _r_ ) {
                        self.errare( 'ERROR reactToPanelMsg => sek-add-column => ' , _r_ );
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            },//ajaxRefreshColumns()


            ajaxResizeColumns : function( params ) {
                  var self = this;
                  // will be cleaned on 'sek-module-refreshed'
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.in_sektion
                  });
                  return self.doAjax( {
                        action : 'sek_get_content',
                        resized_column : params.apiParams.resized_column,
                        sister_column : params.apiParams.sister_column,
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : 'sek-resize-columns'
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype::ajaxResizeColumns => ajax_response.data.contents is undefined ', _r_ );
                              self.errare( 'params ?', params );
                        }
                        //self.errare('sek-preview => resize-column ajax response => ', html_content );
                        // Reset the automatic default resizable inline styling
                        $( '[data-sek-id="' + params.apiParams.resized_column + '"]' ).css({
                              width : '',
                              height: ''
                        });

                        //Append
                        self.appendDynStyleSheet( params.location_skope_id, html_content );

                        // say it
                        // listened to clean the loader just in time
                        $('div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).trigger('sek-columns-refreshed');
                  }).fail( function( _r_ ) {
                        self.errare( 'ERROR reactToPanelMsg => sek-resize-columns => ' , _r_ );
                        $( '[data-sek-id="' + params.apiParams.in_sektion + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
              // refresh module is used to
              // 1) Add a new module
              // 2) re-render the module collection in a column, typically after a sortable move, or a module removal
              ajaxRefreshModulesAndNestedSections : function( params ) {
                    var self = this;
                    // will be cleaned on 'sek-module-refreshed'
                    self.mayBePrintLoader({
                          loader_located_in_level_id : params.apiParams.in_column
                    });
                    return self.doAjax( {
                          action : 'sek_get_content',
                          id : params.apiParams.id,
                          in_sektion : params.apiParams.in_sektion,
                          in_column : params.apiParams.in_column,
                          location_skope_id : params.location_skope_id,
                          local_skope_id : params.local_skope_id,
                          sek_action : params.apiParams.action, // can be sek-add-module / refresh-modules-in-column
                          is_nested : params.apiParams.is_nested
                    }).done( function( _r_ ) {
                          var html_content = '';
                          //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                          if ( _r_.data && _r_.data.contents ) {
                                html_content = _r_.data.contents;
                          } else {
                                self.errare( 'SekPreviewPrototype::ajaxRefreshModulesAndNestedSections => ajax_response.data.contents is undefined ', _r_ );
                                self.errare( 'params ?', params );
                          }

                          var $parentColumn = $('[data-sek-id="' + params.apiParams.in_column + '"]' );
                          if ( 1 > $parentColumn.length ) {
                                self.errare( 'reactToPanelMsg => ajaxRefreshModulesAndNestedSections => no DOM node for parent column => ', params.apiParams.in_column );
                          }
                          var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_column + '"></span>';
                          $parentColumn.before( placeholderHtml );
                          // remove and re-render the entire column
                          $parentColumn.remove();
                          $( '[data-sek-placeholder-for="' + params.apiParams.in_column + '"]' ).after( html_content );
                          $( '[data-sek-placeholder-for="' + params.apiParams.in_column + '"]' ).remove();

                          // say it to the column
                          //=> will be listened to by the column to re-instantiate sortable, resizable and fittext
                          $( '[data-sek-id="' + params.apiParams.in_column + '"]' ).trigger('sek-modules-refreshed', { in_column : params.apiParams.in_column, in_sektion : params.apiParams.in_sektion });

                    }).fail( function( _r_ ) {
                          self.errare( 'ERROR reactToPanelMsg => sek-add-module => ' , _r_ );
                          $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                    });
              }//ajaxRefreshModulesAndNestedSections()
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            ajaxRefreshStylesheet : function( params ) {
                  var self = this;
                  // will be cleaned on 'sek-module-refreshed'
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.id
                  });
                  return self.doAjax( {
                        action : 'sek_get_content',
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : 'sek-refresh-stylesheet'
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        }
                        self.appendDynStyleSheet( params.location_skope_id, html_content );
                        //=> 'sek-level-refreshed' is listened to clean the loader overlay in time
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-stylesheet-refreshed', { level : params.apiParams.level, id : params.apiParams.id } );
                  }).fail( function( _r_ ) {
                        self.errare('sek-refresh-stylesheet fail !');
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            },

            appendDynStyleSheet : function( location_skope_id, styleMarkup ) {
                var _stylesheet_id_ = '#sek-' + location_skope_id,//@see php Sek_Dyn_CSS_Handler
                    _gfonts_id_ = '#sek-gfonts-local-and-global';//@see php Sek_Dyn_CSS_Handler

                // Remove a dynamic inline stylesheet if already printed
                if ( 0 < $('head').find( _stylesheet_id_ ).length ) {
                      $('head').find( _stylesheet_id_ ).remove();
                }
                if ( 0 < $('head').find( _gfonts_id_ ).length ) {
                      $('head').find( _gfonts_id_ ).remove();
                }
                if ( !_.isEmpty( styleMarkup ) ) {
                      $('head').append( styleMarkup );
                }
                // if we have something to print ( styleMarkup not empty ), there should be a dom element
                if ( ! _.isEmpty( styleMarkup ) && 1 > $('head').find( _stylesheet_id_ ).length ) {
                      this.errare( 'sek-preview => problem when printing the dynamic inline style for : '+ _stylesheet_id_, styleMarkup );
                } else {
                      $('head').find( _stylesheet_id_ ).attr('sek-data-origin', 'customizer' );
                }
            }//appendDynStyleSheet()
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // inspired from wp.template in wp-includes/js/wp-util.js
            parseTemplate : _.memoize(function ( id ) {
                  var self = this;
                  var compiled,
                    //
                    // Underscore's default ERB-style templates are incompatible with PHP
                    // when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
                    //
                    // @see trac ticket #22344.
                    //
                    options = {
                          evaluate:    /<#([\s\S]+?)#>/g,
                          interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                          escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
                          variable:    'data'
                    };

                  return function ( data ) {
                        if ( $( id ).length < 1 ) {
                            self.errare( 'preview => parseTemplate => the requested tmpl does not exist =>' + id );
                            return '';
                        }
                        try { compiled = compiled || _.template( $( id ).html(),  options );} catch( _er_ ) {
                              self.errare( 'preview => parseTemplate => problem when parsing tmpl =>' + id, _er_ );
                        }
                        return compiled( data );
                  };
            }),



            //@return [] for console method
            //@bgCol @textCol are hex colors
            //@arguments : the original console arguments
            _prettyPrintLog : function( args ) {
                  var _defaults = {
                        bgCol : '#5ed1f5',
                        textCol : '#000',
                        consoleArguments : []
                  };
                  args = _.extend( _defaults, args );

                  var _toArr = Array.from( args.consoleArguments ),
                      _truncate = function( string ){
                            if ( ! _.isString( string ) )
                              return '';
                            return string.length > 300 ? string.substr( 0, 299 ) + '...' : string;
                      };

                  //if the array to print is not composed exclusively of strings, then let's stringify it
                  //else join(' ')
                  if ( ! _.isEmpty( _.filter( _toArr, function( it ) { return ! _.isString( it ); } ) ) ) {
                        _toArr =  JSON.stringify( _toArr.join(' ') );
                  } else {
                        _toArr = _toArr.join(' ');
                  }
                  return [
                        '%c ' + _truncate( _toArr ),
                        [ 'background:' + args.bgCol, 'color:' + args.textCol, 'display: block;' ].join(';')
                  ];
            },

            _wrapLogInsideTags : function( title, msg, bgColor ) {
                  //fix for IE, because console is only defined when in F12 debugging mode in IE
                  if ( ( _.isUndefined( console ) && typeof window.console.log != 'function' ) )
                    return;
                  if ( sekPreviewLocalized.isDevMode ) {
                        if ( _.isUndefined( msg ) ) {
                              console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ '<' + title + '>' ] } ) );
                        } else {
                              console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ '<' + title + '>' ] } ) );
                              console.log( msg );
                              console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ '</' + title + '>' ] } ) );
                        }
                  } else {
                        console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ title ] } ) );
                  }
            },

            errare : function( title, msg ) { this._wrapLogInsideTags( title, msg, '#ffd5a0' ); },
            infoLog : function( title, msg ) { this._wrapLogInsideTags( title, msg, '#5ed1f5' ); },

            //encapsulates a WordPress ajax request in a normalize method
            //@param queryParams = {}
            doAjax : function( queryParams ) {
                  var self = this;
                  //do we have a queryParams ?
                  queryParams = queryParams || ( _.isObject( queryParams ) ? queryParams : {} );

                  var ajaxUrl = queryParams.ajaxUrl || sekPreviewLocalized.ajaxUrl,//the ajaxUrl can be specified when invoking doAjax
                      nonce = sekPreviewLocalized.frontNonce,//{ 'id' => 'HuFrontNonce', 'handle' => wp_create_nonce( 'hu-front-nonce' ) },
                      dfd = $.Deferred(),
                      _query_ = _.extend( {
                                  action : '',
                                  withNonce : false
                            },
                            queryParams
                      );

                  // HTTP ajaxurl when site is HTTPS causes Access-Control-Allow-Origin failure in Desktop and iOS Safari
                  if ( "https:" == document.location.protocol ) {
                        ajaxUrl = ajaxUrl.replace( "http://", "https://" );
                  }

                  //check if we're good
                  if ( _.isEmpty( _query_.action ) || ! _.isString( _query_.action ) ) {
                        self.errare( 'self.doAjax : unproper action provided' );
                        return dfd.resolve().promise();
                  }
                  //setup nonce
                  //Note : the nonce might be checked server side ( not in all cases, only when writing in db )  with check_ajax_referer( 'hu-front-nonce', 'HuFrontNonce' )
                  _query_[ nonce.id ] = nonce.handle;
                  if ( ! _.isObject( nonce ) || _.isUndefined( nonce.id ) || _.isUndefined( nonce.handle ) ) {
                        self.errare( 'self.doAjax : unproper nonce' );
                        return dfd.resolve().promise();
                  }

                  // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
                  // september 2019
                  // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
                  // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
                  // otherwise the preview UI can be broken
                  _query_[ 'preview-level-guid' ] = sekPreviewLocalized.previewLevelGuid;

                  $.post( ajaxUrl, _query_ )
                        .done( function( _r ) {
                              // Check if the user is logged out.
                              if ( '0' === _r ||  '-1' === _r || false === _r.success ) {
                                    self.errare( 'self.doAjax : done ajax error for action : ' + _query_.action , _r );
                                    dfd.reject( _r );
                              }
                              dfd.resolve( _r );

                        })
                        .fail( function( _r ) {
                              self.errare( 'self.doAjax : failed ajax error for : ' + _query_.action, _r );
                              dfd.reject( _r );
                        });
                        //.always( function( _r ) { dfd.resolve( _r ); });
                  return dfd.promise();
            },//doAjax






            // @return boolean
            isModuleRegistered : function( moduleType ) {
                  return sekPreviewLocalized.registeredModules && ! _.isUndefined( sekPreviewLocalized.registeredModules[ moduleType ] );
            },


            //@return mixed
            getRegisteredModuleProperty : function( moduleType, property ) {
                  if ( ! this.isModuleRegistered( moduleType ) ) {
                        return 'not_set';
                  }
                  return sekPreviewLocalized.registeredModules[ moduleType ][ property ];
            },

            // @params = { id : '', level : '' }
            // Recursively walk the level tree until a match is found
            // @return the level model object
            getLevelModel : function( id, collection ) {
                  var self = this, _data_ = 'no_match';
                  // do we have a collection ?
                  // if not, let's use the root one
                  if ( _.isUndefined( collection ) ) {
                        self.errare( 'getLevelModel => a collection must be provided' );
                  }
                  _.each( collection, function( levelData ) {
                        // did we have a match recursively ?
                        if ( 'no_match' != _data_ )
                          return;
                        if ( id === levelData.id ) {
                              _data_ = levelData;
                        } else {
                              if ( _.isArray( levelData.collection ) ) {
                                    _data_ = self.getLevelModel( id, levelData.collection );
                              }
                        }
                  });
                  return _data_;
            },
      });//$.extend()
})( wp.customize, jQuery, _ );//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
(function( api, $, _ ) {
      $.extend( SekPreviewPrototype, api.Events );
      var SekPreviewConstructor   = api.Class.extend( SekPreviewPrototype );
      api.bind( 'preview-ready', function(){
              api.preview.bind( 'active', function() {
                  try { api.sekPreview = new SekPreviewConstructor(); } catch( _er_ ) {
                        SekPreviewPrototype.errare( 'SekPreviewConstructor => problem on instantiation', _er_ );
                  }
            });
      });
})( wp.customize, jQuery, _ );
