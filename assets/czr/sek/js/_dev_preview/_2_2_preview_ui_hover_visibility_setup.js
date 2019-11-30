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
                        if ( sekPreviewLocalized.isPreviewUIDebugMode || self.isDraggingElement )
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


                  var removeAddContentButtons = function() {
                        self.cachedElements.$body.stop( true, true ).find('.sek-add-content-button').each( function() {
                              $(this).fadeOut( {
                                    duration : 200,
                                    complete : function() { $(this).remove(); }
                              });
                        });
                  };

                  // clean add content buttons on section insertion
                  // solves the problem of button not being rendered in some case
                  // @see https://github.com/presscustomizr/nimble-builder/issues/545
                  self.cachedElements.$body.on( 'sek-location-refreshed sek-section-added sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
                        removeAddContentButtons();
                  });


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
                  self.cachedElements.$body.on( 'click', '.sek-dyn-ui-location-inner', function( evt )  {
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
                  self.cachedElements.$body.on( 'mouseenter mouseover mouseleave', '.sek-dyn-ui-wrapper', _.throttle( function( evt )  {
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
                  self.cachedElements.$body.on( 'click', '.sek-minimize-ui', function( evt )  {
                        $(this).closest('.sek-dyn-ui-location-type').slideToggle('fast');
                  });






                  // Ui for the WP content.
                  // Generated when is_singular() only
                  // @see SEK_Front::render()
                  var $wpContentEl;
                  self.cachedElements.$body.on( 'mouseenter', '.sek-wp-content-wrapper', function( evt ) {
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
                        self.cachedElements.$body.find( 'div[data-sek-level="location"]' ).each( function() {
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
                        self.cachedElements.$body.find('.sek-add-content-button').each( function() {
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
                              self.cachedElements.$body.find('[data-sek-level][data-sek-preview-level-guid="' + sekPreviewLocalized.previewLevelGuid +'"]').each( function() {
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
                              // when PHP constant NIMBLE_IS_PREVIEW_UI_DEBUG_MODE is true, the levels UI in the preview are not being auto removed, so we can inspect the markup and CSS
                              if ( ! sekPreviewLocalized.isPreviewUIDebugMode ) {
                                    // Mouse didn't move recently?
                                    // => remove all UIs
                                    // 1) add content buttons
                                    removeAddContentButtons();

                                    // 2) level UI's
                                    self.cachedElements.$body.stop( true, true ).find('[data-sek-level]').each( function() {
                                          // preserve if the ui menu is expanded, otherwise remove
                                          if ( $(this).children('.sek-dyn-ui-wrapper').find('.sek-is-expanded').length < 1 ) {
                                                removeLevelUI.call( $(this) );
                                          }
                                    });
                              }
                        }
                  });
                  // @return void()
                  var resetMouseMoveTrack = function() {
                        clearTimeout( self.cachedElements.$window.data('_scroll_move_timer_') );
                        self.mouseMovedRecently.set({});
                  };

                  self.cachedElements.$window.on( 'mousemove scroll', _.throttle( function( evt ) {
                        self.mouseMovedRecently( { x : evt.clientX, y : evt.clientY } );
                        clearTimeout( self.cachedElements.$window.data('_scroll_move_timer_') );
                        self.cachedElements.$window.data('_scroll_move_timer_', setTimeout(function() {
                              self.mouseMovedRecently.set({});
                        }, 4000 ) );
                  }, 50 ) );

                  // Always reset the move timer and the mouseMove Value when
                  // - a dragging action is started
                  // - a section is added <= fixes the addition of multiple "Add Section" button in the same location
                  api.preview.bind( 'sek-drag-start', function() {
                        resetMouseMoveTrack();
                  });

                  self.cachedElements.$body.on( 'sek-section-added', '[data-sek-level="location"]', function( evt, params  ) {
                        resetMouseMoveTrack();
                  });

                  return this;
            }//setupUiHoverVisibility

      });//$.extend()
})( wp.customize, jQuery, _ );
