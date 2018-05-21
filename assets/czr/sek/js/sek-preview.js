//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            initialize: function() {
                  var self = this;

                  // Set the skope_id
                  try { this.skope_id = _.findWhere( _wpCustomizeSettings.czr_new_skopes, { skope : 'local' }).skope_id; } catch( _er_ ) {
                        czrapp.errare('Preview => error when storing the skope_id', _er_ );
                        return;
                  }

                  // DOM READY
                  $( function() {
                        self.setupSortable();
                        self.setupResizable();
                        self.setupUiHoverVisibility();
                        self.scheduleUiClickReactions();

                        self.schedulePanelMsgReactions();
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );//global sektionsLocalizedData
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

                  // FIRE SORTABLE ON DOM READY
                  // ROOT SEKTIONS
                  var from_location, to_location, from_sektion, to_sektion, from_column, to_column, startOrder = [], newOrder = [], $targetSektion, $targetColumn, defaults;
                  $('.sektion-wrapper').each( function() {
                        defaults = $.extend( true, {}, self.sortableDefaultParams );
                        $(this).sortable( _.extend( defaults, {
                              handle : '.sek-move-section',
                              connectWith : '.sektion-wrapper',
                              start: function( event, ui ) {
                                    $sourceLocation = ui.item.closest('div[data-sek-level="location"]');
                                    from_location = $sourceLocation.data('sek-id');

                                    // store the startOrder
                                    $sourceLocation.children( '[data-sek-level="section"]' ).each( function() {
                                          startOrder.push( $(this).data('sek-id') );
                                    });
                                    //console.log('column moved from', from_sektion, ui );
                              },
                              stop : function( event, ui ) {
                                    newOrder = [];
                                    $targetLocation = ui.item.closest('div[data-sek-level="location"]');
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






                  // COLUMNS
                  $('.sektion-wrapper').each( function() {
                        $(this).find( 'div[data-sek-level="section"]' ).each( function() {
                              self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                        });
                  });
                  // Delegate instantiation
                  $('.sektion-wrapper').on( 'sek-columns-refreshed sek-section-added', 'div[data-sek-level="section"]', function() {
                        self.makeColumnsSortableInSektion( $(this).data('sek-id') );
                  });






                  // MODULE
                  $('.sektion-wrapper').each( function() {
                        $(this).find( 'div[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });
                  // Delegate instantiation
                  $('.sektion-wrapper').on( 'sek-modules-refreshed', 'div[data-sek-level="column"]', function() {
                        self.makeModulesSortableInColumn( $(this).data('sek-id') );
                  });
                  $('.sektion-wrapper').on( 'sek-columns-refreshed', 'div[data-sek-level="section"]', function() {
                        $(this).find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).each( function() {
                              self.makeModulesSortableInColumn( $(this).data('sek-id') );
                        });
                  });


                  // NESTED SEKTIONS
                  // $('.sek-column-inner', '[data-sek-level="section"]').children( '[data-sek-level="section"]' ).each( function() {
                  //       defaults = $.extend( true, {}, self.sortableDefaultParams );
                  //       $(this).sortable( _.extend( defaults, {
                  //           handle : '.sek-move-nested-section',
                  //           connectWith: ".sek-column-inner, .sektion-wrapper",
                  //           start: function( event, ui ) {
                  //               // store the startOrder
                  //               $('.sektion-wrapper').children( '[data-sek-level="section"]' ).each( function() {
                  //                     startOrder.push( $(this).data('sek-id') );
                  //               });
                  //               //console.log('column moved from', from_sektion, ui );
                  //           },
                  //           stop : function( event, ui ) {
                  //               newOrder = [];
                  //               // Restrict to the direct children
                  //               $('.sektion-wrapper').children( '[data-sek-level="section"]' ).each( function() {
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

            // Instantiate sortable for a given column Id
            makeColumnsSortableInSektion : function( sektionId ) {
                  var self = this,
                      defaults = $.extend( true, {}, self.sortableDefaultParams ),
                      $sortableCandidate = $( 'div[data-sek-id="' + sektionId + '"]').find('.sek-sektion-inner').first();
                  // if ( $sortableCandidate.children('div[data-sek-level="column"]').length > 11 ) {
                  //       czrapp.errare('12 COLUMNS');
                  //       return;
                  // }
                  $sortableCandidate.sortable( _.extend( defaults, {
                        handle : '.sek-move-column',
                        connectWith: ".sek-sektion-inner",
                        remove : function( event, ui ) {
                              $targetSektionCandidate = ui.item.closest('div[data-sek-level="section"]');
                              if ( $targetSektionCandidate.length > 0 && $targetSektionCandidate.find('.sek-sektion-inner').first().children('div[data-sek-level="column"]').length > 12 ) {
                                    api.preview.send( 'sek-notify', {
                                          message : sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]
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
                              from_sektion = ui.item.closest('div[data-sek-level="section"]').data( 'sek-id');
                              // store the startOrder
                              ui.item.closest('div[data-sek-level="section"]').find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
                                    startOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( startOrder ) ) {
                                    czrapp.errare( 'column sortable => startOrder should not be empty' );
                                    return;
                              }
                              //console.log('column moved from', from_sektion, ui );
                        },

                        stop : function( event, ui ) {
                              // set destination
                              $targetSektion = ui.item.closest('div[data-sek-level="section"]');
                              to_sektion = $targetSektion.data( 'sek-id');
                              //console.log('module moved to', to_column, from_column );
                              $targetSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).each( function() {
                                    newOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( newOrder ) ) {
                                    czrapp.errare( 'column sortable =>  newOrder should not be empty' );
                                    return;
                              }
                              // console.log('ALORS SEKTIONS ?: ', to_sektion, from_sektion );
                              // console.log('ALORS ORDER ?: ', newOrder, startOrder);

                              // don't send anything if the source and target columns are the same, and the order is unchanged
                              if ( _.isEqual( newOrder, startOrder ) && to_sektion === from_sektion ) {
                                    czrapp.errare( 'preview => makeModulesSortableInColumn => start and stop positions are identical' );
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
                  $( 'div[data-sek-id="' + columnId + '"]').find('.sek-column-inner').first().sortable( _.extend( defaults, {
                        handle : '.sek-move-module',
                        connectWith: ".sek-column-inner",
                        over : function( event, ui ) {
                              // Hide the module placeholder while overing, when the column is empty
                              // @see css rule .sek-sortable-overing > .sek-no-modules-column { display: none; }
                              $('.sektion-wrapper').find('.sek-sortable-overing').each( function() {
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
                              from_column = ui.item.closest('div[data-sek-level="column"]').data( 'sek-id');
                              from_sektion = ui.item.closest('div[data-sek-level="section"]').data( 'sek-id');
                              // store the startOrder
                              ui.item.closest('div[data-sek-level="column"]').find('.sek-column-inner').first().children( '[data-sek-level="module"]' ).each( function() {
                                    startOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( startOrder ) ) {
                                    czrapp.errare( 'makeModulesSortableInColumn => startOrder should not be empty' );
                                    return;
                              }
                              //console.log('column moved from', from_sektion, ui );
                        },

                        stop : function( event, ui ) {
                              // set destination
                              $targetColumn = ui.item.closest('div[data-sek-level="column"]');
                              to_column = $targetColumn.data( 'sek-id');
                              //console.log('module moved to', to_column, from_column );
                              $targetColumn.find('.sek-column-inner').first().children( '[data-sek-id]' ).each( function() {
                                    newOrder.push( $(this).data('sek-id') );
                              });
                              if ( _.isEmpty( newOrder ) ) {
                                    czrapp.errare( 'makeModulesSortableInColumn => newOrder should not be empty' );
                                    return;
                              }
                              // console.log('ALORS COLUMNS ?: ', to_column, from_column );
                              // console.log('ALORS ORDER ?: ', newOrder, startOrder);

                              // don't send anything if the source and target columns are the same, and the order is unchanged
                              if ( _.isEqual( newOrder, startOrder ) && to_column === from_column ) {
                                    czrapp.errare( 'preview => makeModulesSortableInColumn => start and stop positions are identical' );
                                    return;
                              }
                              api.preview.send( 'sek-move', {
                                    id : ui.item.data('sek-id'),
                                    level : 'module',
                                    newOrder : newOrder,
                                    from_column : from_column,
                                    to_column : to_column,
                                    from_sektion : from_sektion,
                                    to_sektion : ui.item.closest('div[data-sek-level="section"]').data( 'sek-id')
                              });

                              // Clean some css classes
                              $('body').removeClass( 'sek-dragging-element' );
                        }
                  }));
            },//makeModulesSortableInColumn
      });//$.extend()
})( wp.customize, jQuery, _ );//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired on Dom Ready, in ::initialize()
            setupResizable : function() {
                  var self = this;
                  $('.sektion-wrapper').find( 'div[data-sek-level="section"]' ).each( function() {
                        self.maybeMakeColumnResizableInSektion.call( this );
                  });
                  // Delegate instantiation when a module is added ( => column re-rendered )
                  $('.sektion-wrapper').on(
                        'sek-modules-refreshed sek-columns-refreshed sek-section-duplicated',
                        'div[data-sek-level="section"]',
                        function(evt) {
                              self.maybeMakeColumnResizableInSektion.call( this );
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

                  $directColumnChildren.each( function() {
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
//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired on Dom Ready, in ::initialize()
            setupUiHoverVisibility : function() {
                  var self = this;
                  var tmpl,
                      level,
                      params,
                      $levelEl;

                  // Level's overlay
                  $('.sektion-wrapper').on( 'mouseenter', '[data-sek-level]', function( evt ) {
                        // if ( $(this).children('.sek-block-overlay').length > 0 )
                        //   return;
                        level = $(this).data('sek-level');
                        params = {
                              id : $(this).data('sek-id'),
                              level : $(this).data('sek-level')
                        };
                        switch ( level ) {
                              case 'section' :
                                    //$el = $('.sektion-wrapper').find('[data-sek-id="' + id + '"]');
                                    params = _.extend( params, {
                                          is_last_possible_section : true === $(this).data('sek-is-nested'),
                                          can_have_more_columns : $(this).find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 12
                                    });
                              break;
                              case 'column' :
                                    var $parent_sektion = $(this).closest('div[data-sek-level="section"]');
                                    params = _.extend( params, {
                                          parent_can_have_more_columns : $parent_sektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 12,
                                          parent_is_single_column : $parent_sektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 2,
                                          parent_is_last_allowed_nested : true === $parent_sektion.data('sek-is-nested')
                                    });
                              break;
                              case 'module' :
                                    params = _.extend( params, {});
                              break;
                        }
                        // don't display the column and module ui when resizing columns
                        if ( true === $('.sektion-wrapper').data('sek-resizing-columns') && _.contains( ['column', 'module'], level ) ) {
                              return;
                        }

                        tmpl = self.parseTemplate( '#sek-tmpl-overlay-ui-' + level );
                        $.when( $(this).prepend( tmpl( params ) ) ).done( function() {
                              $levelEl = $(this);
                              $levelEl.find('.sek-block-overlay').stop( true, true ).fadeIn( {
                                  duration : 300,
                                  complete : function() {}
                              } );
                        });

                  }).on( 'mouseleave', '[data-sek-level]', function( evt ) {
                          $levelEl = $(this);
                          $levelEl.children('.sek-block-overlay').stop( true, true ).fadeOut( {
                                duration : 200,
                                complete : function() {
                                      $(this).remove();
                                }
                          });

                  });



                  // Add content button between sections
                  $('body').on( 'mouseenter', function( evt ) {
                        if ( $(this).find('.sek-add-content-button').length > 0 )
                          return;

                        $sectionCollection = $('.sektion-wrapper').children( 'div[data-sek-level="section"]' );
                        tmpl = self.parseTemplate( '#sek-tmpl-add-content-button' );
                        // nested sections are not included
                        $sectionCollection.each( function() {
                              $.when( $(this).prepend( tmpl({}) ) ).done( function() {
                                    $(this).find('.sek-add-content-button').fadeIn( 300 );
                              });
                              //console.log('$sectionCollection.length', $sectionCollection.length, $(this).index() + 1 );
                              //if is last section, append also
                              if ( $sectionCollection.length == $(this).index() + 1 ) {
                                    $.when( $(this).append( tmpl({ is_last : true }) ) ).done( function() {
                                          $(this).find('.sek-add-content-button').fadeIn( 300 );
                                    });
                              }
                        });
                  }).on( 'mouseleave', function( evt ) {
                        // nested sections are not included
                        $('.sektion-wrapper').find('.sek-add-content-button').each( function() {
                              $(this).fadeOut( {
                                    duration : 200,
                                    complete : function() { $(this).remove(); }
                              });
                        });
                  });


                  // $('div[data-sek-level="section"]').on( 'mouseenter mouseleave', '.sek-add-content-btn', function( evt ) {
                  //       $(this).closest( '.sek-add-content-button' ).toggleClass( 'sek-add-content-hovering', 'mouseenter' == evt.type );
                  // });


                  return this;
            },//setupUiHoverVisibility

            // setupSectionUiOverlay : function( eventType, id ) {

            // }
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            //Fired on Dom ready in initialize()
            scheduleUiClickReactions : function() {
                  var self = this;

                  $('body').on('click', function( evt ) {

                        var clickedOn = '',
                            $el = $(evt.target),
                            $hook_location = $el.closest('[data-sek-level="location"]'),
                            $closestLevelWrapper = $el.closest('[data-sek-level]'),
                            $closestActionIcon = $el.closest('[data-sek-action]'),
                            _action,
                            _location = $hook_location.data('sek-id'),
                            _level = $closestLevelWrapper.data('sek-level'),
                            _id = $closestLevelWrapper.data('sek-id');
                        if ( 'add-content' == $el.data('sek-action') || ( $el.closest('[data-sek-action]').length > 0 && 'add-content' == $el.closest('[data-sek-action]').data('sek-action') ) ) {
                              clickedOn = 'addContentButton';
                        } else if ( ! _.isEmpty( $el.data( 'sek-action' ) ) || $closestActionIcon.length > 0 ) {
                              clickedOn = 'overlayUiIcon';
                        } else if ( 'module' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'moduleWrapper';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') && true === $closestLevelWrapper.data('sek-no-modules') ) {
                              clickedOn = 'noModulesColumn';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'columnOutsideModules';
                        } else if ( 'section' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'sectionOutsideColumns';
                        } else if ( ! _.isEmpty( $el.data( 'sek-add' ) ) ) {
                              clickedOn = 'addSektion';
                        } else if ( $el.hasClass('sek-to-json') ) {
                              clickedOn = 'sekToJson';
                        } else {
                              clickedOn = 'inactiveZone';
                        }

                        //console.log('CLICKED', $(evt.target), clickedOn );

                        switch( clickedOn ) {
                              case 'addContentButton' :
                                    self._send_( $el, { action : 'pick-section' } );
                              break;
                              case 'overlayUiIcon' :

                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'ERROR => sek-front-preview => No valid level dom element found' );
                                    }
                                    _action = $el.data('sek-action');

                                    if ( _.isEmpty( _action ) ) {
                                        throw new Error( 'Invalid action' );
                                    }
                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'ERROR => sek-front-preview => No valid level id found' );
                                    }
                                    self._send_( $el, {
                                        action : _action,
                                        location : _location,
                                        level : _level,
                                        id : _id
                                    });
                              break;
                              case 'moduleWrapper' :
                                    // stop here if the ui overlay actions block was clicked
                                    if ( $el.parent('.sek-block-overlay-actions').length > 0 )
                                      return;
                                    self._send_( $el, { action : 'edit-module', level : _level , id : _id } );
                              break;
                              case 'noModulesColumn' :
                                    // stop here if the ui overlay actions block was clicked
                                    if ( $el.parent('.sek-block-overlay-actions').length > 0 )
                                      return;

                                    self._send_( $el, { action : 'pick-module', level : _level , id : _id } );
                              break;
                              case 'columnOutsideModules' :
                              case 'sectionOutsideColumns' :
                                    self._send_( $el, {
                                        action : 'edit-options',
                                        location : _location,
                                        level : _level,
                                        id : _id
                                    });
                              break;
                              case 'addSektion' :
                                    api.preview.send( 'sek-add-section', {
                                          location : _location,
                                          level : $el.data('sek-add')
                                    });
                              break;
                              case 'sekToJson' :
                                    api.preview.send( 'sek-to-json', { id : _id } );
                              break;
                              case 'inactiveZone' :
                                    api.preview.send( 'sek-click-on-inactive-zone');
                                    self._send_( $el, { action : 'pick-module' } );
                              break;
                        }
                  });
            },//scheduleUserReactions()


            _send_ : function( $el, params ) {
                  api.preview.send( 'sek-' + params.action, {
                        location : params.location,
                        level : params.level,
                        id : params.id,
                        in_column : $el.closest('div[data-sek-level="column"]').length > 0 ? $el.closest('div[data-sek-level="column"]').data( 'sek-id') : '',
                        in_sektion : $el.closest('div[data-sek-level="section"]').length > 0 ? $el.closest('div[data-sek-level="section"]').data( 'sek-id') : '',
                        clicked_input_type : $el.closest('div[data-sek-input-type]').length > 0 ? $el.closest('div[data-sek-input-type]').data('sek-input-type') : '',
                        clicked_input_id : $el.closest('div[data-sek-input-id]').length > 0 ? $el.closest('div[data-sek-input-id]').data('sek-input-id') : ''
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sektionsLocalizedData
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
                            'sek-add-column' : 'ajaxRefreshColumns',
                            'sek-add-module' : 'ajaxRefreshModulesAndNestedSections',
                            'sek-remove' : function( params ) {
                                  var removeCandidateId = params.apiParams.id,
                                      $candidateEl = $('div[data-sek-id="' + removeCandidateId + '"]' );
                                  switch ( params.apiParams.action ) {
                                        case 'sek-remove-section' :
                                              //console.log('SEK-remove-sektion', params );
                                              if ( true === params.apiParams.is_nested ) {
                                                    self.ajaxRefreshModulesAndNestedSections( params );
                                              } else {
                                                    if ( _.isEmpty( removeCandidateId ) || 1 > $candidateEl.length ) {
                                                          czrapp.errare( 'reactToPanelMsg => sek-remove => invalid candidate id => ', removeCandidateId );
                                                    }
                                                    $( '.sektion-wrapper').find( $candidateEl ).remove();
                                              }
                                              //console.log( params.apiParams.action, params );
                                              //self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                        case 'sek-remove-column' :
                                              //console.log( params.apiParams.action, params );
                                              self.ajaxRefreshColumns( params );
                                        break;
                                        case 'sek-remove-module' :
                                              //console.log( params.apiParams.action, params );
                                              self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                        default :

                                        break;
                                  }

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
                                  czrapp.doAjax( {
                                        skope_id : params.skope_id,
                                        action : 'sek_get_content',
                                        id : params.apiParams.id,
                                        level : params.apiParams.level,
                                        sek_action : params.apiParams.action
                                  }).fail( function( _r_ ) {
                                        czrapp.errare( 'ERROR reactToPanelMsg => sek-refresh-level => ' , _r_ );
                                  }).done( function( _r_ ) {
                                        var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.id + '"></span>',
                                            $currentLevelEl = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.id + '"]' );
                                        if ( $currentLevelEl.length < 1 ) {
                                              czrapp.errare( 'reactToPanelMsg => sek-refresh-level ajax done => the level to refresh is not rendered in the page' );
                                              return;
                                        }
                                        $currentLevelEl.before( placeholderHtml );
                                        var $placeHolder = $( '.sektion-wrapper').find( '[data-sek-placeholder-for="' + params.apiParams.id + '"]' );
                                        $currentLevelEl.remove();
                                        $placeHolder.after( _r_.data );
                                        $placeHolder.remove();

                                        $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.id + '"]' ).trigger( 'sek-refresh-level' );
                                  });
                            },

                            //'sek-set-level-options' : 'ajaxRefreshStylesheet',
                            'sek-refresh-stylesheet' : 'ajaxRefreshStylesheet',





                            // EDITING MODULE AND OPTIONS
                            'sek-move' : function( params ) {
                                  switch ( params.apiParams.action ) {
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
                                  }
                            },

                            'sek-resize-columns' : 'ajaxResizeColumns',




                            // GENERATE UI ELEMENTS
                            // when the options ui has been generated in the panel for a level, we receive back this msg
                            'sek-generate-level-options-ui' : function( params ) {},


                            // @params =  {
                            //   skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
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
                            'sek-generate-module-ui' : function( params ) {},

                            //@params { type : module || preset_section }
                            'sek-drag-start' : function( params ) {
                                  // append the drop zones between sections
                                  var i = 1;
                                  $('.sektion-wrapper').children('[data-sek-level="section"]').each( function() {
                                        // Always before
                                        if ( $('[data-sek-before-section="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                              $(this).before(
                                                '<div class="sek-content-' + params.type + '-drop-zone sek-drop-zone" data-sek-location="between-sections" data-sek-before-section="' + $(this).data('sek-id') +'"></div>'
                                              );
                                        }
                                        // After the last one
                                        if (  i == $('.sektion-wrapper').children('[data-sek-level="section"]').length ) {
                                              $(this).after(
                                                '<div class="sek-content-' + params.type + '-drop-zone sek-drop-zone" data-sek-location="between-sections" data-sek-after-section="' + $(this).data('sek-id') +'"></div>'
                                              );
                                        }
                                        i++;
                                  });

                                  $('body').addClass('sek-dragging');
                            },
                            'sek-drag-stop' : function( params ) {
                                  $('body').removeClass('sek-dragging');
                                  // Clean any remaining placeholder
                                  $('.sortable-placeholder').remove();
                                  // Remove the drop zone between sections
                                  $('.sek-drop-zone').remove();
                            },


                            // FOCUS
                            'sek-focus-on' : function( params ) {
                                  var $elToFocusOn = $('div[data-sek-id="' + params.id + '"]' );
                                  if ( $elToFocusOn.length > 0 ) {
                                        $('html, body').animate({
                                              scrollTop : $('div[data-sek-id="' + params.id + '"]' ).offset().top - 100
                                        }, 'slow');
                                  }
                            }

                      };//msgCollection

                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.preview.bind( msgId, function( params ) {
                              params = _.extend( {
                                  skope_id : '',
                                  apiParams : {},
                                  uiParams : {}
                              }, params || {} );

                              if ( _.isFunction( callbackFn ) ) {
                                    try {
                                          $.when( callbackFn( params ) ).done( function() {
                                                api.preview.send( [ msgId, 'done'].join('_'), params );
                                          }).fail( function() {
                                                api.preview.send( 'sek-notify', { type : 'error', duration : 10000, message : '@missi18n Something went wrong, please refresh this page.'});
                                          });
                                    } catch( _er_ ) {
                                          czrapp.errare( 'reactToPanelMsg => Error when firing the callback of ' + msgId , _er_  );
                                    }
                              } else {
                                    try {
                                          $.when( self[callbackFn].call( self, params ) ).done( function() {
                                                api.preview.send( [ msgId, 'done'].join('_'), params );
                                          }).fail( function() {
                                                api.preview.send( 'sek-notify', { type : 'error', duration : 10000, message : '@missi18n Something went wrong, please refresh this page.'});
                                          });
                                    } catch( _er_ ) {
                                          czrapp.errare( 'reactToPanelMsg => Error when firing the callback of ' + msgId , _er_  );
                                    }
                              }


                        });
                  });
            }//schedulePanelMsgReactions()
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // this method is used when creating or duplicating a sektion
            // @return a promise()
            ajaxAddSektion : function( params ) {
                  var self = this;
                  //console.log('preview => ajaxAddSektions', params );
                  return czrapp.doAjax( {
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        in_column : params.apiParams.in_column,
                        skope_id : params.skope_id,
                        sek_action : params.apiParams.action,
                        is_nested : params.apiParams.is_nested
                  }).done( function( _r_ ) {
                        // Embed
                        // is it a nested sektion ?
                        var $parentColumn;
                        if ( params.apiParams.is_nested ) {
                              $parentColumn = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' );
                              if ( 1 > $parentColumn.length ) {
                                    czrapp.errare( 'preview => reactToPanelMsg => sek-add-column => no DOM node for parent column => ', params.apiParams.in_column );
                              }
                              var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_column + '"></span>';
                              $parentColumn.before( placeholderHtml );
                              // remove and re-render the entire column
                              $parentColumn.remove();
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).after( _r_.data );
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).remove();
                        } else {
                              // DUPLICATE CASE
                              // Insert the clone section right after its cloned sister
                              if ( 'sek-duplicate-section' == params.apiParams.action && ! _.isEmpty( params.cloneId ) ) {
                                    $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).after( _r_.data );
                              }
                              // GENERATED WHEN ADDING A MODULE
                              else {
                                    // When a section has been created by adding a module ( @see sek-add-content-in-new-sektion )
                                    // we need to append it to a specific location
                                    // otherwise, we append it at the end of the section collection
                                    $beforeCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.before_section + '"]' );
                                    $afterCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.after_section + '"]' );

                                    if ( ! _.isEmpty( params.apiParams.before_section ) && $beforeCandidate.length > 0 ) {
                                          $beforeCandidate.before( _r_.data );
                                    } else if ( ! _.isEmpty( params.apiParams.after_section ) && $afterCandidate.length > 0 ) {
                                          $afterCandidate.after( _r_.data );
                                    } else {
                                          $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').first().find('.sek-add-button-wrapper').before( _r_.data );
                                    }
                              }
                        }

                        // When a section is duplicated, fire sortable for the inner-column modules
                        if ( 'sek-duplicate-section' == params.apiParams.action ) {
                              // re-instantiate sortable in the refreshed columns of the section
                              // make columns resizable
                              $( 'div[data-sek-id="' + params.cloneId + '"]', '.sektion-wrapper').each( function() {
                                    //self.maybeMakeColumnResizableInSektion.call( this );

                                    $(this).find(  'div[data-sek-level="column"]' ).each( function() {
                                          self.makeModulesSortableInColumn( $(this).data('sek-id') );
                                    });
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
                        //=> will be listened to by fittext
                        $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.cloneId + '"]' ).trigger('sek-section-added');
                        $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.id + '"]' ).trigger('sek-section-added');
                  }).fail( function( _r_ ) {
                        czrapp.errare( 'ERROR in sek_get_html_for_injection ? ' , _r_ );
                  });
            }//ajaxAddSektion()

      });//$.extend()
})( wp.customize, jQuery, _ );
//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // refresh column is used to
            // 1) Add a new column
            // 2) re-render the column collection in a sektion
            ajaxRefreshColumns : function( params ) {
                  var self = this;
                  return czrapp.doAjax( {
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        skope_id : params.skope_id,
                        sek_action : params.apiParams.action// sek-add-column || sek-remove-column
                  }).done( function( _r_ ) {
                        var $parentSektion = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' );
                        if ( 1 > $parentSektion.length ) {
                              czrapp.errare( 'reactToPanelMsg => sek-add-column => no DOM node for parent sektion => ', params.apiParams.in_sektion );
                        }
                        var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_sektion + '"></span>';
                        $parentSektion.before( placeholderHtml );
                        // remove and re-render the entire sektion
                        $parentSektion.remove();
                        $( '.sektion-wrapper').find( '.sek-placeholder' ).after( _r_.data );
                        $( '.sektion-wrapper').find( '.sek-placeholder' ).remove();


                        // re-generate the stylesheet => this will take into account the reset width of each column
                        czrapp.doAjax( {
                              action : 'sek_get_content',
                              skope_id : params.skope_id,
                              sek_action : 'sek-refresh-stylesheet'// sek-add-column
                        }).done( function( _r_ ) {
                              //console.log('sek-refresh-stylesheet done !',  _r_.data);
                              self.appendDynStyleSheet( params.skope_id, _r_.data );
                        }).fail( function( _r_ ) {
                              console.log('sek-refresh-stylesheet fail !');
                        });

                        // say it to the parent sektion
                        //=> will be listened to by the column to re-instantiate sortable, resizable
                        $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).trigger('sek-columns-refreshed');
                  }).fail( function( _r_ ) {
                        czrapp.errare( 'ERROR reactToPanelMsg => sek-add-column => ' , _r_ );
                  });
            },//ajaxRefreshColumns()


            ajaxResizeColumns : function( params ) {
                  //console.log('PREVIEW => REACT TO PANEL MSG => sek-resize-columns => ', params );
                  var self = this;
                  return czrapp.doAjax( {
                        action : 'sek_get_content',
                        resized_column : params.apiParams.resized_column,
                        sister_column : params.apiParams.sister_column,
                        skope_id : params.skope_id,
                        sek_action : 'sek-resize-columns'
                  }).done( function( _r_ ) {
                        //czrapp.errare('sek-preview => resize-column ajax response => ', _r_.data );
                        // Reset the automatic default resizable inline styling
                        $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.resized_column + '"]' ).css({
                              width : '',
                              height: ''
                        });

                        //Append
                        self.appendDynStyleSheet( params.skope_id, _r_.data );
                  }).fail( function( _r_ ) {
                        czrapp.errare( 'ERROR reactToPanelMsg => sek-resize-columns => ' , _r_ );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
              // refresh module is used to
              // 1) Add a new module
              // 2) re-render the module collection in a column, typically after a sortable move, or a module removal
              ajaxRefreshModulesAndNestedSections : function( params ) {
                    var self = this;
                    return czrapp.doAjax( {
                          action : 'sek_get_content',
                          id : params.apiParams.id,
                          in_sektion : params.apiParams.in_sektion,
                          in_column : params.apiParams.in_column,
                          skope_id : params.skope_id,
                          sek_action : params.apiParams.action, // can be sek-add-module / refresh-modules-in-column
                          is_nested : params.apiParams.is_nested
                    }).done( function( _r_ ) {
                          var $parentColumn = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' );
                          if ( 1 > $parentColumn.length ) {
                                czrapp.errare( 'reactToPanelMsg => ajaxRefreshModulesAndNestedSections => no DOM node for parent column => ', params.apiParams.in_column );
                          }
                          var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_column + '"></span>';
                          $parentColumn.before( placeholderHtml );
                          // remove and re-render the entire column
                          $parentColumn.remove();
                          $( '.sektion-wrapper').find( '[data-sek-placeholder-for="' + params.apiParams.in_column + '"]' ).after( _r_.data );
                          $( '.sektion-wrapper').find( '[data-sek-placeholder-for="' + params.apiParams.in_column + '"]' ).remove();

                          // say it to the column
                          //=> will be listened to by the column to re-instantiate sortable, resizable and fittext
                          $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' ).trigger('sek-modules-refreshed');

                    }).fail( function( _r_ ) {
                          czrapp.errare( 'ERROR reactToPanelMsg => sek-add-module => ' , _r_ );
                    });
              }//ajaxRefreshModulesAndNestedSections()
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            ajaxRefreshStylesheet : function( params ) {
                  var self = this;
                  //console.log('preview => panel react => ajax refresh dyn style', params );
                  return czrapp.doAjax( {
                        action : 'sek_get_content',
                        skope_id : params.skope_id,
                        sek_action : 'sek-refresh-stylesheet'
                  }).done( function( _r_ ) {
                        //console.log('sek-refresh-stylesheet done !',  _r_.data);
                        self.appendDynStyleSheet( params.skope_id, _r_.data );
                  }).fail( function( _r_ ) {
                        czrapp.errare('sek-refresh-stylesheet fail !');
                  });
            },

            appendDynStyleSheet : function( skope_id, styleMarkup ) {
                var _stylesheet_id_ = '#sek-' + skope_id,//@see php Sek_Dyn_CSS_Handler
                    _gfonts_id_ = '#sek-gfonts-' + skope_id;//@see php Sek_Dyn_CSS_Handler

                //console.log('IN APPEND DYN STYLESHEET', styleMarkup, _stylesheet_id_, $('head').find( _stylesheet_id_ ) );

                // Remove a dynamic inline stylesheet if already printed
                if ( 0 < $('head').find( _stylesheet_id_ ).length ) {
                      $('head').find( _stylesheet_id_ ).remove();
                }
                if ( 0 < $('head').find( _gfonts_id_ ).length ) {
                      $('head').find( _gfonts_id_ ).remove();
                }
                $('head').append( styleMarkup );
                // if we have something to print ( styleMarkup not empty ), there should be a dom element
                if ( ! _.isEmpty( styleMarkup ) && 1 > $('head').find( _stylesheet_id_ ).length ) {
                      czrapp.errare( 'sek-preview => problem when printing the dynamic inline style for : '+ _stylesheet_id_ );
                } else {
                      $('head').find( _stylesheet_id_ ).attr('sek-data-origin', 'customizer' );
                }
            }//appendDynStyleSheet()
      });//$.extend()
})( wp.customize, jQuery, _ );
//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // inspired from wp.template in wp-includes/js/wp-util.js
            parseTemplate : _.memoize(function ( id ) {
                  var compiled,
                    /*
                     * Underscore's default ERB-style templates are incompatible with PHP
                     * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
                     *
                     * @see trac ticket #22344.
                     */
                    options = {
                          evaluate:    /<#([\s\S]+?)#>/g,
                          interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                          escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
                          variable:    'data'
                    };

                  return function ( data ) {
                        if ( $( id ).length < 1 ) {
                            czrapp.errare( 'preview => parseTemplate => the requested tmpl does not exist =>' + id );
                            return '';
                        }
                        try { compiled = compiled || _.template( $( id ).html(),  options );} catch( _er_ ) {
                              czrapp.errare( 'preview => parseTemplate => problem when parsing tmpl =>' + id, _er_ );
                        }
                        return compiled( data );
                  };
            }),
      });//$.extend()
})( wp.customize, jQuery, _ );//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
(function( api, $, _ ) {
      $.extend( SekPreviewPrototype, api.Events );
      var SekPreviewConstructor   = api.Class.extend( SekPreviewPrototype );

      api.bind( 'preview-ready', function(){
              api.preview.bind( 'active', function() {
                  try { api.sekPreview = new SekPreviewConstructor(); } catch( _er_ ) {
                        czrapp.errare( 'SekPreviewConstructor => problem on instantiation', _er_ );
                  }
            });
      });
})( wp.customize, jQuery, _ );
