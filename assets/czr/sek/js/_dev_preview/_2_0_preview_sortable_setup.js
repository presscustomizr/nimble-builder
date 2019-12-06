//global sekPreviewLocalized
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
                  self.cachedElements.$body.on( 'sek-section-added sek-level-refreshed sek-location-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
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
                  self.cachedElements.$body.on( 'sek-columns-refreshed sek-section-added', '[data-sek-level="section"]', function( evt ) {
                        self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                  });
                  // this case occurs when moving a section from one location to another for example
                  self.cachedElements.$body.on( 'sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
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
                  self.cachedElements.$body.on( 'sek-modules-refreshed', '[data-sek-level="column"]', function() {
                        self.makeModulesSortableInColumn( $(this).data('sek-id') );
                  });
                  self.cachedElements.$body.on( 'sek-columns-refreshed', '[data-sek-level="section"]', function() {
                        $(this).find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  // this case occurs when moving a section from one location to another for example
                  self.cachedElements.$body.on( 'sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
                        $(this).find( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  self.cachedElements.$body.on( 'sek-section-added', '[data-sek-level="location"]', function( evt, params  ) {
                        $(this).find( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  // added to fix impossibility to move an already inserted module in a freshly added multicolumn section
                  // @see https://github.com/presscustomizr/nimble-builder/issues/538
                  self.cachedElements.$body.on( 'sek-location-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
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


            // Print all level ui
            // fixes slowness when dragging @see https://github.com/presscustomizr/nimble-builder/issues/521
            printAllLevelsUi : function() {
                  var self = this;
                  self.cachedElements.$body.find( '[data-sek-level]' ).each( function() {
                        self.printLevelUI( $(this) );
                        $(this).find('.sek-dyn-ui-wrapper').stop( true, true ).show();
                  });
            },


            makeSektionsSortableInLocation : function( locationId ) {
                  var self = this;
                  var from_location, to_location, startOrder = [], newOrder = [], defaults;

                  if ( true === $('[data-sek-id="' + locationId +'"]').data('sek-is-global-location') )
                      return;

                  defaults = $.extend( true, {}, self.sortableDefaultParams );

                  $('[data-sek-id="' + locationId +'"]').sortable( _.extend( defaults, {
                        items: '[data-sek-level="section"]',
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
                              self.cachedElements.$body.addClass('sek-moving-section');
                              self.isDraggingElement = true;
                              $sourceLocation = ui.item.closest('[data-sek-level="location"]');
                              from_location = $sourceLocation.data('sek-id');

                              // Print all level ui
                              // fixes slowness when dragging @see https://github.com/presscustomizr/nimble-builder/issues/521
                              self.printAllLevelsUi();

                              // store the startOrder
                              // $sourceLocation.children( '[data-sek-level="section"]' ).each( function() {
                              //       startOrder.push( $(this).data('sek-id') );
                              // });
                        },
                        stop : function( event, ui ) {
                              self.cachedElements.$body.removeClass('sek-moving-section');
                              self.isDraggingElement = false;

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

                              // Print all level ui
                              // fixes slowness when dragging @see https://github.com/presscustomizr/nimble-builder/issues/553
                              self.printAllLevelsUi();
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

                              self.cachedElements.$body.addClass( 'sek-dragging-element' );
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

                              // Print all level ui
                              // fixes slowness when dragging @see https://github.com/presscustomizr/nimble-builder/issues/521
                              self.printAllLevelsUi();
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
                              self.cachedElements.$body.removeClass( 'sek-dragging-element' );
                        }
                  }));

                  $( '[data-sek-id="' + columnId + '"]').addClass('sek-module-sortable-setup');
            },//makeModulesSortableInColumn
      });//$.extend()
})( wp.customize, jQuery, _ );