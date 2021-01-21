// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  MENU
/* ------------------------------------------------------------------------- */
(function(w, d){
      var callbackFunc = function() {
          jQuery( function($){
              //DROPDOWN
              var Dropdown = function() {
                    //dropdown
                    var DATA_KEY  = 'sek.sekDropdown',
                        EVENT_KEY = '.' + DATA_KEY,
                        Event     = {
                          PLACE_ME  : 'placeme'+ EVENT_KEY,
                          PLACE_ALL : 'placeall' + EVENT_KEY,
                          SHOWN     : 'shown' + EVENT_KEY,
                          SHOW      : 'show' + EVENT_KEY,
                          HIDDEN    : 'hidden' + EVENT_KEY,
                          HIDE      : 'hide' + EVENT_KEY,
                          CLICK     : 'click' + EVENT_KEY,
                          TAP       : 'tap' + EVENT_KEY,
                        },
                        ClassName = {
                          DROPDOWN                : 'sek-dropdown-menu',
                          DROPDOWN_SUBMENU        : 'sek-dropdown-submenu',
                          SHOW                    : 'show',
                          PARENTS                 : 'menu-item-has-children',
                          ALLOW_POINTER_ON_SCROLL : 'allow-pointer-events-on-scroll'
                        },
                        Selector = {
                          DATA_TOGGLE              : '[data-toggle="sek-dropdown"]',
                          DATA_SHOWN_TOGGLE_LINK   : '.' +ClassName.SHOW+ '> a',
                          HOVER_MENU               : '.sek-nav-wrap',
                          HOVER_PARENT             : '.sek-nav-wrap .menu-item-has-children',
                          PARENTS                  : '.sek-nav-wrap .menu-item-has-children',
                          SNAKE_PARENTS            : '.sek-nav-wrap .menu-item-has-children',
                          CHILD_DROPDOWN           : 'ul.sek-dropdown-menu'
                        };

                    // unify all the dropdowns classes whether the menu is a proper menu or the all pages fall-back
                    $( '.sek-nav .children, .sek-nav .sub-menu' ).addClass( ClassName.DROPDOWN );
                    $( '.sek-nav-wrap .page_item_has_children' ).addClass( ClassName.PARENTS );
                    $( '.sek-nav' + ' .' + ClassName.DROPDOWN + ' .' + ClassName.PARENTS ).addClass( ClassName.DROPDOWN_SUBMENU );
                        
                        // this is the element
                        var _isMobileMenu = function() {
                              if ( this.length && this.length > 0 ) {
                                    return "yes" === this.closest('[data-sek-is-mobile-menu]').data('sek-is-mobile-menu');
                              }
                              return false;
                        };
                    //Handle dropdown on hover via js
                    var dropdownMenuOnHover = function() {
                          var _dropdown_selector = Selector.HOVER_PARENT;

                          enableDropdownOnHover();

                          function _addOpenClass( evt ) {
                                
                                var $_el = $(this),
                                    $_child_dropdown = $_el.find( Selector.CHILD_DROPDOWN ).first();

                                // Jan 2021 : start of a fix for https://github.com/presscustomizr/nimble-builder/issues/772
                                if ( nb_.cachedElements.$body.hasClass('is-touch-device') ) {
                                      // When navigating the regular menu ( horizontal ) on a mobile device, typically a tablet in landscape orientation
                                      // we want to prevent opening the link of a parent menu if the children are not displayed yet
                                      if ( "true" != $_child_dropdown.attr('aria-expanded') && !_isMobileMenu.call($_child_dropdown) ) {
                                            evt.preventDefault();
                                      }
                                }
                                //a little delay to balance the one added in removing the open class
                                var _debounced_addOpenClass = nb_.debounce( function() {
                                      //do nothing if menu is mobile
                                      if( 'static' == $_el.find( '.'+ClassName.DROPDOWN ).css( 'position' ) ) {
                                            return false;
                                      }
                                      var $_child_dropdown = $_el.find( Selector.CHILD_DROPDOWN ).first();

                                      if ( !$_el.hasClass(ClassName.SHOW) ) {
                                            nb_.cachedElements.$body.addClass( ClassName.ALLOW_POINTER_ON_SCROLL );

                                            $_el.trigger( Event.SHOW )
                                                  .addClass(ClassName.SHOW)
                                                  .trigger( Event.SHOWN);

                                            if ( $_child_dropdown.length > 0 ) {
                                                  $_child_dropdown[0].setAttribute('aria-expanded', 'true');
                                            }
                                      }
                                }, 30);

                                _debounced_addOpenClass();
                          }

                          function _removeOpenClass() {

                                var $_el = $(this),
                                    $_child_dropdown = $_el.find( Selector.CHILD_DROPDOWN ).first();

                                //a little delay before closing to avoid closing a parent before accessing the child
                                var _debounced_removeOpenClass = nb_.debounce( function() {
                                      if ( $_el.find("ul li:hover").length < 1 && ! $_el.closest('ul').find('li:hover').is( $_el ) ) {
                                            // april 2020 => some actions should be only done when not on a "touch" device
                                            // otherwise we have a bug on submenu expansion
                                            // see : https://github.com/presscustomizr/customizr/issues/1824
                                            //if ( !nb_.cachedElements.$body.hasClass('is-touch-device') ) {
                                                  // $_el.trigger( Event.HIDE )
                                                  //     .removeClass( ClassName.SHOW)
                                                  //     .trigger( Event.HIDDEN );
                                            //}
                                            $_el.trigger( Event.HIDE )
                                                  .removeClass( ClassName.SHOW)
                                                  .trigger( Event.HIDDEN );

                                            //make sure pointer events on scroll are still allowed if there's at least one submenu opened
                                            if ( $_el.closest( Selector.HOVER_MENU ).find( '.' + ClassName.SHOW ).length < 1 ) {
                                                  nb_.cachedElements.$body.removeClass( ClassName.ALLOW_POINTER_ON_SCROLL );
                                            }

                                            if ( $_child_dropdown.length > 0 ) {
                                                  $_child_dropdown[0].setAttribute('aria-expanded', 'false');
                                            }
                                      }
                                }, 30 );

                                _debounced_removeOpenClass();
                          }

                          function enableDropdownOnHover() {
                                // april 2020 : is-touch-device class is added on body on the first touch
                                // This way, we can prevent the problem reported on https://github.com/presscustomizr/customizr/issues/1824
                                // ( two touches needed to reveal submenus on touch devices )
                                nb_.cachedElements.$body.on('touchstart', function() {
                                      if ( !$(this).hasClass('is-touch-device') ) {
                                            $(this).addClass('is-touch-device');
                                      }
                                });
                                //BIND
                                nb_.cachedElements.$body.on( 'mouseenter', _dropdown_selector, _addOpenClass );
                                nb_.cachedElements.$body.on( 'mouseleave', _dropdown_selector , _removeOpenClass );
                                nb_.cachedElements.$body.on( 'click', _dropdown_selector, _addOpenClass );
                          }
                    },

                    //SNAKE
                    dropdownPlacement = function() {
                          var isRTL = 'rtl' === $('html').attr('dir'),
                              doingAnimation = false;

                          nb_.cachedElements.$window
                              //on resize trigger Event.PLACE on active dropdowns
                              .on( 'resize', function() {
                                      if ( ! doingAnimation ) {
                                            doingAnimation = true;
                                            window.requestAnimationFrame(function() {
                                              //trigger a placement on the open dropdowns
                                              $( Selector.SNAKE_PARENTS+'.'+ClassName.SHOW)
                                                  .trigger(Event.PLACE_ME);
                                              doingAnimation = false;
                                            });
                                      }

                              });

                          $( document )
                              .on( Event.PLACE_ALL, function() {
                                          //trigger a placement on all
                                          $( Selector.SNAKE_PARENTS )
                                              .trigger(Event.PLACE_ME);
                              })
                              //snake bound on menu-item shown and place
                              .on( Event.SHOWN+' '+Event.PLACE_ME, Selector.SNAKE_PARENTS, function(evt) {
                                evt.stopPropagation();
                                _do_snake( $(this), evt );
                              });


                          //snake
                          //$_el is the menu item with children whose submenu will be 'snaked'
                          function _do_snake( $_el, evt ) {
                                if ( !( evt && evt.namespace && DATA_KEY === evt.namespace ) ) {
                                      return;
                                }

                                var $_this       = $_el,
                                    $_dropdown   = $_this.children( '.'+ClassName.DROPDOWN );

                                if ( !$_dropdown.length ) {
                                      return;
                                }

                                //stage
                                /*
                                * we display the dropdown so that jQuery is able to retrieve exact size and positioning
                                * we also hide whatever overflows the menu item with children whose submenu will be 'snaked'
                                * this to avoid some glitches that would made it lose the focus:
                                * During RTL testing when a menu item with children reached the left edge of the window
                                * it happened that while the submenu was showing (because of the show class added, so not depending on the snake)
                                * this submenu (ul) stole the focus and then released it in a very short time making the mouseleave callback
                                * defined in dropdownMenuOnHover react, hence closing the whole submenu tree.
                                * This might be a false positive, as we don't really test RTL with RTL browsers (only the html direction changes),
                                * but since the 'cure' has no side effects, let's be pedantic!
                                */
                                $_el.css( 'overflow', 'hidden' );
                                $_dropdown.css( {
                                  'zIndex'  : '-100',
                                  'display' : 'block'
                                });

                                _maybe_move( $_dropdown, $_el );

                                //unstage
                                $_dropdown.css({
                                  'zIndex'  : '',
                                  'display' : ''
                                });
                                $_el.css( 'overflow', '' );
                          }//_so_snake


                          function _maybe_move( $_dropdown, $_el ) {
                                var Direction          = isRTL ? {
                                          //when in RTL we open the submenu by default on the left side
                                          _DEFAULT          : 'left',
                                          _OPPOSITE         : 'right'
                                    } : {
                                          //when in LTR we open the submenu by default on the right side
                                          _DEFAULT          : 'right',
                                          _OPPOSITE         : 'left'
                                    },
                                    ClassName          = {
                                          OPEN_PREFIX       : 'open-',
                                          DD_SUBMENU        : 'sek-dropdown-submenu',
                                          CARET_TITLE_FLIP  : 'sek-menu-link__row-reverse',
                                          //CARET             : 'caret__dropdown-toggler',
                                          DROPDOWN          : 'sek-dropdown-menu'
                                    },
                                    _caret_title_maybe_flip = function( $_el, _direction, _old_direction ) {
                                          $.each( $_el, function() {
                                              var $_el               = $(this),
                                                  $_a                = $_el.find( 'a' ).first();

                                              if ( 1 == $_a.length ) {
                                                    $_a.toggleClass( ClassName.CARET_TITLE_FLIP, _direction == Direction._OPPOSITE  );
                                              }
                                          });
                                    },
                                    _setOpenDirection       = function( _direction ) {
                                          //retrieve the old direction => used to remove the old direction class
                                          var _old_direction = _direction == Direction._OPPOSITE ? Direction._DEFAULT : Direction._OPPOSITE;

                                          //tell the dropdown to open on the direction _direction (hence remove the old direction class)
                                          $_dropdown.removeClass( ClassName.OPEN_PREFIX + _old_direction ).addClass( ClassName.OPEN_PREFIX + _direction );
                                          if ( $_el.hasClass( ClassName.DD_SUBMENU ) ) {
                                                _caret_title_maybe_flip( $_el, _direction, _old_direction );
                                                //make the first level submenus caret inherit this
                                                _caret_title_maybe_flip( $_dropdown.children( '.' + ClassName.DD_SUBMENU ), _direction, _old_direction );
                                          }
                                    };

                                //snake inheritance
                                if ( $_dropdown.parent().closest( '.'+ClassName.DROPDOWN ).hasClass( ClassName.OPEN_PREFIX + Direction._OPPOSITE ) ) {
                                      //open on the opposite direction
                                      _setOpenDirection( Direction._OPPOSITE );
                                } else {
                                      //open on the default direction
                                      _setOpenDirection( Direction._DEFAULT );
                                }

                                //let's compute on which side open the dropdown
                                if ( $_dropdown.offset().left + $_dropdown.width() > nb_.cachedElements.$window.width() ) {
                                      //open on the left
                                      _setOpenDirection( 'left' );
                                } else if ( $_dropdown.offset().left < 0 ) {
                                      //open on the right
                                      _setOpenDirection( 'right' );
                                }
                          }//_maybe_move
                    };//dropdownPlacement

                    //FireAll
                    dropdownMenuOnHover();
                    dropdownPlacement();
              },

              SimpleCollapse = function() {
                  var NAME = 'sekCollapse',
                      DATA_KEY = 'sek.sekCollapse',
                      EVENT_KEY = "." + DATA_KEY,
                      TRANSITION_DURATION = 400,
                      DATA_API_KEY = '.data-api',
                      Event = {
                        SHOW: "show" + EVENT_KEY,
                        SHOWN: "shown" + EVENT_KEY,
                        HIDE: "hide" + EVENT_KEY,
                        HIDDEN: "hidden" + EVENT_KEY,
                        CLICK_DATA_API: "click" + EVENT_KEY + DATA_API_KEY
                      },
                      ClassName = {
                        SHOW: 'show',
                        COLLAPSE: 'sek-collapse',
                        COLLAPSING: 'sek-collapsing',
                        COLLAPSED: 'sek-collapsed'
                      },
                      Selector = {
                        ACTIVES: '.show, .sek-collapsing',
                        DATA_TOGGLE: '[data-sek-toggle="sek-collapse"]'
                      },
                      _onSlidingCompleteResetCSS = function( $_el ) {
                            $_el   = $_el ? $_el : $(this);
                            $_el.css({
                                  'display'    : '',
                                  'paddingTop' : '',
                                  'marginTop' : '',
                                  'paddingBottom' : '',
                                  'marginBottom' : '',
                                  'height' : ''
                            });
                      };

                    //bind
                    $(document).on( Event.CLICK_DATA_API, Selector.DATA_TOGGLE, function (event) {
                          // preventDefault only for <a> elements (which change the URL) not inside the collapsible element
                          if (event.currentTarget.tagName === 'A') {
                                event.preventDefault();
                          }

                          var $toggler             = $(this),
                             //get the data toggle
                             _collapsible_selector = $toggler.data('target');

                          $(_collapsible_selector).each( function () {
                                var $collapsible = $(this),
                                    collapse = $collapsible.hasClass(ClassName.SHOW);

                                $collapsible.stop()[ collapse ? 'slideUp' : 'slideDown' ]({
                                      duration: TRANSITION_DURATION,
                                      start : function() {
                                            $collapsible.addClass(ClassName.COLLAPSING).trigger( collapse ? Event.HIDE : Event.SHOW );
                                            if ( collapse ) {
                                                $toggler.addClass( ClassName.COLLAPSED ).attr( 'aria-expanded', 'false' );
                                            } else {
                                                $toggler.removeClass( ClassName.COLLAPSED ).attr( 'aria-expanded', 'true' );
                                            }
                                      },
                                      complete: function() {
                                            var removeClass,
                                                addClass,
                                                event;

                                            if ( collapse ) {
                                                  removeClass = ClassName.SHOW;
                                                  addClass    = ClassName.COLLAPSE;
                                                  event       = Event.HIDDEN;
                                            } else {
                                                  removeClass = ClassName.COLLAPSE;
                                                  addClass    = ClassName.SHOW;
                                                  event       = Event.SHOWN;
                                            }
                                            $collapsible.removeClass(ClassName.COLLAPSING + ' ' + removeClass).addClass( addClass ).trigger(event);
                                            //remove all the inline style added by the slideUp/Down methods
                                            _onSlidingCompleteResetCSS( $collapsible );
                                      }
                                });//end slideUp/slideDown
                          });//end each
                    });//end document bind
              };


              Dropdown();
              SimpleCollapse();

              // handle the mobile hamburger hover effect
              $( document )
                    .on( 'mouseenter', '.sek-nav-toggler', function(){ $(this).addClass( 'hovering' ); } )
                    .on( 'mouseleave', '.sek-nav-toggler', function(){ $(this).removeClass( 'hovering' ); } )
                    .on( 'show.sek.sekCollapse hide.sek.sekCollapse', '.sek-nav-collapse', function() {
                          $('[data-target="#'+$(this).attr('id')+'"]').removeClass( 'hovering' );
                          nb_.cachedElements.$window.trigger('scroll');
                    });

              // How to have a logo plus an hamburger in mobiles on the same line?
              // => clone the menu module, and append it to the closest sektion-inner wrapper
              // => this way it will occupy 100% of the width
              // => and also the clone inherits the style of the module
              // https://github.com/presscustomizr/nimble-builder/issues/368
              var _doMobileMenuSetup = function() {
                      $( '[data-sek-module-type="czr_menu_module"]' ).find('[data-sek-expand-below="yes"]').each( function() {
                            // make sure we don't do the setup twice when customizing
                            if ( true === $(this).data('sek-setup-menu-mobile-expanded-below-done') )
                              return;

                            var $_mobile_menu_module  = $(this).closest('[data-sek-module-type="czr_menu_module"]').clone(true),
                                //create a new id for the mobile menu nav collapse that will used by the button toggler too
                                _new_id = $( '.sek-nav-collapse', this ).attr('id') + '-mobile';

                          $_mobile_menu_module
                                /// place the mobile menu at the end of this sektion inner
                                .appendTo( $(this).closest( '.sek-sektion-inner' ) )
                                //wrap in a convenient div for styling and targeting
                                .wrap( '<div class="sek-col-base sek-mobile-menu-expanded-below" id="'+_new_id+'-wrapper"></div>');

                          // assign the new id to the mobile nav collapse
                          $( '.sek-nav-collapse', '#'+_new_id+'-wrapper' ).attr( 'id', _new_id );
                          // add a data attribute so we can target the mobile menu with dynamic css rules
                          // @needed when coding : https://github.com/presscustomizr/nimble-builder/issues/491
                          $( '.sek-nav-wrap', '#'+_new_id+'-wrapper' ).attr('data-sek-is-mobile-menu', 'yes');
                          // remove the duplicate button
                          $( '.sek-nav-toggler', '#'+_new_id+'-wrapper' ).detach();
                          // update the toggler button so that will now refer to the "cloned" mobile menu
                          $( '.sek-nav-toggler', this ).data( 'target', '#' + _new_id )
                                                       .attr( 'aria-controls', _new_id );
                          // flag setup done
                          $(this).data('sek-setup-menu-mobile-expanded-below-done', true );
                    });//$.each()
              };
              _doMobileMenuSetup();
              // When previewing, react to level refresh
              // This can occur to any level. We listen to the bubbling event on 'body' tag
              nb_.cachedElements.$body.on('sek-level-refreshed sek-modules-refreshed sek-columns-refreshed sek-section-added', function( evt ){
                      // clean the previously duplicated menu if any
                      $('.sek-mobile-menu-expanded-below').remove();
                      _doMobileMenuSetup();
              });

          });//jQuery( function($){})

      };/////////////// callbackFunc
      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));
