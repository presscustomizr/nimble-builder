//global sekPreviewLocalized
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

                  var printLevelUI = function() {
                        level = $(this).data('sek-level');
                        // we don't print a ui for locations
                        if ( 'location' == level )
                          return;

                        $levelEl = $(this);

                        // stop here if the .sek-dyn-ui-wrapper is already printed for this level AND is not being faded out.
                        // if ( $levelEl.children('.sek-dyn-ui-wrapper').length > 0 && true !== $levelEl.data( 'UIisFadingOut' ) )
                        //   return;

                        if ( $levelEl.children('.sek-dyn-ui-wrapper').length > 0 )
                          return;
                        params = {
                              id : $levelEl.data('sek-id'),
                              level : $levelEl.data('sek-level')
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
                        $.when( $(this).prepend( tmpl( params ) ) ).done( function() {
                              $levelEl.find('.sek-dyn-ui-wrapper').stop( true, true ).fadeIn( {
                                    duration : 150,
                                    complete : function() {}
                              } );
                        });
                  };

                  var removeLevelUI = function() {
                        $levelEl = $(this);
                        if ( $levelEl.children('.sek-dyn-ui-wrapper').length < 1 )
                          return;
                        //stores ths status of 200 ms fading out. => will let us know if we can print again when moving the mouse fast back and forth between two levels.
                        $levelEl.data( 'UIisFadingOut', true );//<= we need to store a fadingOut status to not miss a re-print in case of a fast moving mouse

                        $levelEl.children('.sek-dyn-ui-wrapper').stop( true, true ).fadeOut( {
                              duration : 150,
                              complete : function() {
                                    $(this).remove();
                                    $levelEl.data( 'UIisFadingOut', false );
                              }
                        });
                  };

                  // Level's UI icons with delegation
                  // $('body').on( 'mouseenter', '[data-sek-level]', function( evt ) {
                  //       // if ( $(this).children('.sek-dyn-ui-wrapper').length > 0 )
                  //       //   return;


                  // }).on( 'mouseleave', '[data-sek-level]', function( evt ) {
                  //       console.log('MOUSE LEAVE');

                  // });



                  // UI MENU
                  // React to click
                  // + schedule auto collapse after n seconds of ui inactivity
                  var autoCollapser = function( $menu, $dynUiWrapper ) {
                        clearTimeout( $menu.data('_toggle_ui_menu_') );
                        $menu.data( '_toggle_ui_menu_', setTimeout(function() {
                              $menu.addClass('sek-collapsed');
                              $dynUiWrapper.removeClass('sek-is-expanded');
                        }, 5000 ) );
                  };
                  $('body').on( 'click', '.sek-dyn-ui-location-inner', function( evt )  {
                        var $menu = $(this).find('.sek-dyn-ui-hamb-menu-wrapper'),
                            $dynUiWrapper = $menu.closest( '.sek-dyn-ui-wrapper').find('.sek-dyn-ui-inner'),
                            $parentColumn = $(this).closest('[data-sek-level="column"]');
                        // Close all other expanded ui menu of the column
                        $parentColumn.find('.sek-dyn-ui-hamb-menu-wrapper').each( function() {
                              $(this).toggleClass('sek-collapsed');
                              $(this).closest( '.sek-dyn-ui-wrapper').find('.sek-dyn-ui-inner').removeClass('sek-is-expanded');
                        });

                        // expand the ui menu of the clicked level
                        $menu.removeClass('sek-collapsed');
                        $dynUiWrapper.addClass('sek-is-expanded');
                        autoCollapser( $menu, $dynUiWrapper );
                  });
                  // maintain expanded as long as it's being hovered
                  $('body').on( 'mouseenter mouseover mouseleave', '.sek-dyn-ui-wrapper', _.throttle( function( evt )  {
                        var $menu = $(this).find('.sek-dyn-ui-hamb-menu-wrapper'),
                            $dynUiWrapper = $(this).find('.sek-dyn-ui-inner');
                        if ( _.isUndefined( $menu.data('_toggle_ui_menu_') ) || $menu.hasClass('sek-collapsed') )
                          return;
                        if ( $menu.length > 0 ) {
                              autoCollapser( $menu, $dynUiWrapper );
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


                  // Add content button between sections
                  // <script type="text/html" id="sek-tmpl-add-content-button">
                  //     <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                  //       <div class="sek-add-content-button-wrapper">
                  //         <button data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:60px;">
                  //           <span title="<?php _e('Add Content', 'text_domain_to_be_replaced' ); ?>" class="sek-click-on-button-icon fas fa-plus-circle sek-click-on"></span><span class="action-button-text"><?php _e('Add Content', 'text_domain_to_be_replaced' ); ?></span>
                  //         </button>
                  //       </div>
                  //     </div>
                  // </script>
                  // fired on mousemove and scroll, every 50ms
                  var _printAddContentButtons = function() {
                        $('body').find( 'div[data-sek-level="location"]' ).each( function() {
                              $sectionCollection = $(this).children( 'div[data-sek-level="section"]' );
                              tmpl = self.parseTemplate( '#sek-tmpl-add-content-button' );
                              var $btn_el,
                                  _location = $(this).data('sek-id');

                              // nested sections are not included
                              $sectionCollection.each( function() {
                                    if ( $(this).find('.sek-add-content-button').length > 0 )
                                      return;

                                    $.when( $(this).prepend( tmpl({ location : _location }) ) ).done( function() {
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
                                          $.when( $(this).append( tmpl({ is_last : true, location : _location }) ) ).done( function() {
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
                              $.when( $(this).append( tmpl({ location : $(this).closest( 'div[data-sek-level="location"]' ).data('sek-id') } ) ) ).done( function() {
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

                  var _sniffLevelsAndPrintUI = function( position ) {
                        $('body').find('[data-sek-level]').each( function() {
                              var levelWrapperRect = $(this)[0].getBoundingClientRect(),
                                isInHorizontally = position.x <= levelWrapperRect.right && levelWrapperRect.left <= position.x,
                                isInVertically = position.y >= levelWrapperRect.top && levelWrapperRect.bottom >= position.y;

                              if ( isInHorizontally && isInVertically ) {
                                    printLevelUI.call( $(this) );
                              } else {
                                    removeLevelUI.call( $(this) );
                              }
                        });
                  };


                  // Schedule the printing / removal of the add content button
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
                              $('body').stop( true, true ).find('.sek-add-content-button').each( function() {
                                    $(this).fadeOut( {
                                          duration : 200,
                                          complete : function() { $(this).remove(); }
                                    });
                              });
                        }
                  });
                  $(window).on( 'mousemove scroll', _.throttle( function( evt ) {
                        self.mouseMovedRecently( { x : evt.clientX, y : evt.clientY } );
                        clearTimeout( $.data( this, '_scroll_move_timer_') );
                        $.data( this, '_scroll_move_timer_', setTimeout(function() {
                              self.mouseMovedRecently.set( {} );
                        }, 4000 ) );
                  }, 50 ) );

                  // Always remove when a dragging action is started
                  api.preview.bind( 'sek-drag-start', function() {
                        self.mouseMovedRecently.set( {} );
                  });

                  return this;
            }//setupUiHoverVisibility

      });//$.extend()
})( wp.customize, jQuery, _ );
