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
                  };

                  // SEKTIONS
                  // On dom ready
                  $('[data-sek-level="location"]').each( function() {
                        self.makeSektionsSortableInLocation( $(this).data('sek-id') );
                  });

                  // Schedule
                  $( 'body').on( 'sek-section-added sek-refresh-level', '[data-sek-level="location"]', function( evt, params  ) {
                        self.makeSektionsSortableInLocation( $(this).data('sek-id') );
                  });

                  // COLUMNS
                  // On dom ready
                  $('[data-sek-level="location"]').each( function() {
                        $(this).find( '[data-sek-level="section"]' ).each( function() {
                              self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                        });
                  });
                  // Schedule
                  $('[data-sek-level="location"]').on( 'sek-columns-refreshed sek-section-added', '[data-sek-level="section"]', function( evt ) {
                        self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                  });

                  // MODULE
                  // On dom ready
                  $('[data-sek-level="location"]').each( function() {
                        $(this).find( '[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  // Schedule
                  $('[data-sek-level="location"]').on( 'sek-modules-refreshed', '[data-sek-level="column"]', function() {
                        self.makeModulesSortableInColumn( $(this).data('sek-id') );
                  });
                  $('[data-sek-level="location"]').on( 'sek-columns-refreshed', '[data-sek-level="section"]', function() {
                        $(this).find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
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
                        defaults = $.extend( true, {}, self.sortableDefaultParams );
                        $(this).sortable( _.extend( defaults, {
                              handle : '.sek-move-section',
                              connectWith : '[data-sek-level="location"]',
                              start: function( event, ui ) {
                                    $sourceLocation = ui.item.closest('[data-sek-level="location"]');
                                    from_location = $sourceLocation.data('sek-id');

                                    // store the startOrder
                                    $sourceLocation.children( '[data-sek-level="section"]' ).each( function() {
                                          startOrder.push( $(this).data('sek-id') );
                                    });
                                    //console.log('column moved from', from_sektion, ui );
                              },
                              stop : function( event, ui ) {
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
                              }
                        }));
                  });
            },



            // Instantiate sortable for a given column Id
            makeColumnsSortableInSektion : function( sektionId ) {
                  var self = this,
                      defaults = $.extend( true, {}, self.sortableDefaultParams ),
                      $sortableCandidate = $( '[data-sek-id="' + sektionId + '"]').find('.sek-sektion-inner').first();
                  // if ( $sortableCandidate.children('[data-sek-level="column"]').length > 11 ) {
                  //       self.errare('12 COLUMNS');
                  //       return;
                  // }
                  $sortableCandidate.sortable( _.extend( defaults, {
                        handle : '.sek-move-column',
                        connectWith: ".sek-sektion-inner",
                        remove : function( event, ui ) {
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
                              if ( _.isEmpty( startOrder ) ) {
                                    self.errare( 'column sortable => startOrder should not be empty' );
                                    return;
                              }
                              //console.log('column moved from', from_sektion, ui );
                        },

                        stop : function( event, ui ) {
                              // set destination
                              $targetSektion = ui.item.closest('[data-sek-level="section"]');
                              to_sektion = $targetSektion.data( 'sek-id');
                              //console.log('module moved to', to_column, from_column );
                              $targetSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
                                    newOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( newOrder ) ) {
                                    self.errare( 'column sortable =>  newOrder should not be empty' );
                                    return;
                              }
                              // console.log('ALORS SEKTIONS ?: ', to_sektion, from_sektion );
                              // console.log('ALORS ORDER ?: ', newOrder, startOrder);

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
                        }
                  }));
            },//self.makeColumnsSortableInSektion




            // Instantiate sortable for a given column Id
            makeModulesSortableInColumn : function( columnId ) {
                  var from_sektion, to_sektion, from_column, to_column, startOrder = [], newOrder = [], $targetSektion, $targetColumn, defaults;
                  var self = this;
                  defaults = $.extend( true, {}, self.sortableDefaultParams );
                  // Restrict to the .sek-column-inner for this very column id with first()
                  $( '[data-sek-id="' + columnId + '"]').find('.sek-column-inner').first().sortable( _.extend( defaults, {
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
                        start: function( event, ui ) {
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
                              //console.log('column moved from', from_sektion, ui );
                        },

                        stop : function( event, ui ) {
                              // set destination
                              $targetColumn = ui.item.closest('[data-sek-level="column"]');
                              to_column = $targetColumn.data( 'sek-id');
                              //console.log('module moved to', to_column, from_column );
                              $targetColumn.find('.sek-column-inner').first().children( '[data-sek-id]' ).each( function() {
                                    newOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( newOrder ) ) {
                                    self.errare( 'makeModulesSortableInColumn => newOrder should not be empty' );
                                    return;
                              }
                              // console.log('ALORS COLUMNS ?: ', to_column, from_column );
                              // console.log('ALORS ORDER ?: ', newOrder, startOrder);

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
})( wp.customize, jQuery, _ );