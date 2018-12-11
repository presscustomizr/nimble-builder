/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('.sektion-wrapper').each( function() {
            try { $(this).nimbleLazyLoad(); } catch( er ) {
                  if ( typeof window.console.log === 'function' ) {
                        console.log( er );
                  }
            }
      });
});


/* ------------------------------------------------------------------------- *
 *  BG PARALLAX
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('[data-sek-bg-parallax="true"]').parallaxBg();
      // When previewing, react to level refresh
      // This can occur to any level. We listen to the bubbling event on 'body' tag
      // and salmon up to maybe instantiate any missing candidate
      // Example : when a preset_section is injected
      $('body').on('sek-level-refreshed sek-section-added', function( evt ){
            if ( "true" === $(this).attr( 'data-sek-bg-parallax' ) ) {
                  $(this).parallaxBg();
            } else {
                  $(this).find('[data-sek-bg-parallax="true"]').parallaxBg();
            }
      });
});


/* ------------------------------------------------------------------------- *
 *  FITTEXT
/* ------------------------------------------------------------------------- */
jQuery( function($){
    var doFitText = function() {
          $(".sek-module-placeholder").each( function() {
                $(this).fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
          });
          // Delegate instantiation
          $('.sektion-wrapper').on(
                'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed',
                'div[data-sek-level="section"]',
                function( evt ) {
                      $(this).find(".sek-module-placeholder").fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                }
          );

    };
    //doFitText();
    // if ( 'function' == typeof(_) && ! _utils_.isUndefined( wp.customize ) ) {
    //     wp.customize.selectiveRefresh.bind('partial-content-rendered' , function() {
    //         doFitText();
    //     });
    // }

    // animate menu item to Nimble anchors
    $('body').on( 'click', '.menu .menu-item [href^="#"]', function( evt ){
          evt.preventDefault();
          var anchorCandidate = $(this).attr('href');
          anchorCandidate = 'string' === typeof( anchorCandidate ) ? anchorCandidate.replace('#','') : '';

          if ( '' !== anchorCandidate || null !== anchorCandidate ) {
                var $anchorCandidate = $('[data-sek-level="location"]' ).find( '[id="' + anchorCandidate + '"]');
                if ( 1 === $anchorCandidate.length ) {
                      $('html, body').animate({
                            scrollTop : $anchorCandidate.offset().top - 150
                      }, 'slow');
                }
          }
    });
});


/* ------------------------------------------------------------------------- *
 *  MENU
/* ------------------------------------------------------------------------- */
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
              },
              Selector = {
                DATA_TOGGLE              : '[data-toggle="sek-dropdown"]',
                DATA_SHOWN_TOGGLE_LINK   : '.' +ClassName.SHOW+ '> a',
                HOVER_MENU               : '.sek-nav-wrap',
                HOVER_PARENT             : '.sek-nav-wrap .menu-item-has-children',
                PARENTS                  : '.sek-nav-wrap .menu-item-has-children',
                SNAKE_PARENTS            : '.sek-nav-wrap .menu-item-has-children',
              };

          // unify all the dropdowns classes whether the menu is a proper menu or the all pages fall-back
          $( '.sek-nav .children, .sek-nav .sub-menu' ).addClass( ClassName.DROPDOWN );
          $( '.sek-nav-wrap .page_item_has_children' ).addClass( ClassName.PARENTS );
          $( '.sek-nav' + ' .' + ClassName.DROPDOWN + ' .' + ClassName.PARENTS ).addClass( ClassName.DROPDOWN_SUBMENU );

          //Handle dropdown on hover via js
          var dropdownMenuOnHover = function() {
                var _dropdown_selector = Selector.HOVER_PARENT;

                function _addOpenClass () {
                      var $_el = $(this);

                      //a little delay to balance the one added in removing the open class
                      var _debounced_addOpenClass = _utils_.debounce( function() {
                            //do nothing if menu is mobile
                            if( 'static' == $_el.find( '.'+ClassName.DROPDOWN ).css( 'position' ) ) {
                                  return false;
                            }
                            if ( ! $_el.hasClass(ClassName.SHOW) ) {
                                  $_el.trigger( Event.SHOW )
                                      .addClass(ClassName.SHOW)
                                      .trigger( Event.SHOWN);

                                  var $_data_toggle = $_el.children( Selector.DATA_TOGGLE );

                                  if ( $_data_toggle.length ) {
                                        $_data_toggle[0].setAttribute('aria-expanded', 'true');
                                  }
                            }
                      }, 30);

                      _debounced_addOpenClass();
                }

                function _removeOpenClass () {

                      var $_el = $(this);

                      //a little delay before closing to avoid closing a parent before accessing the child
                      var _debounced_removeOpenClass = _utils_.debounce( function() {
                            if ( $_el.find("ul li:hover").length < 1 && ! $_el.closest('ul').find('li:hover').is( $_el ) ) {
                                  $_el.trigger( Event.HIDE )
                                      .removeClass( ClassName.SHOW)
                                      .trigger( Event.HIDDEN );

                                  var $_data_toggle = $_el.children( Selector.DATA_TOGGLE );

                                  if ( $_data_toggle.length ) {
                                        $_data_toggle[0].setAttribute('aria-expanded', 'false');
                                  }
                            }
                      }, 30 );

                      _debounced_removeOpenClass();
                }

                //BIND
                $( document )
                    .on( 'mouseenter', _dropdown_selector, _addOpenClass )
                    .on( 'mouseleave', _dropdown_selector , _removeOpenClass );
          },

          //SNAKE
          dropdownPlacement = function() {
                var isRTL = 'rtl' === $('html').attr('dir'),
                    doingAnimation = false;

                $(window)
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
                      if ( $_dropdown.offset().left + $_dropdown.width() > $(window).width() ) {
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
            TRANSITION_DURATION = 600,
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
                $('[data-target=#'+$(this).attr('id')+']').removeClass( 'hovering' );
                $(window).trigger('scroll');
          });
});
