// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  MENU
/* ------------------------------------------------------------------------- */
(function(w, d){
      var callbackFunc = function() {
            jQuery( function($){
                  // Set the attribute data-sek-is-mobile-vertical-menu on page load and dynamically set on resize
                  var _setVerticalMobileBooleanAttribute = function() {
                        // Set vertical mobile boolean attribute
                        var breakpoint = 768, deviceWidth;
                        $('nav.sek-nav-wrap').each( function() {
                              breakpoint = $(this).data('sek-mobile-menu-breakpoint') || breakpoint;
                              // cast to integer
                              breakpoint = parseInt( breakpoint, 10 );
                              deviceWidth = window.innerWidth > 0 ? window.innerWidth : screen.width;
                              // console.log('window.innerWidth ??', window.innerWidth, window.innerWidth > 0 );
                              // console.log('SOO ? breakpoint | device width', breakpoint + ' | ' + deviceWidth );
                              
                              // add a data attribute so we can target the mobile menu with dynamic css rules
                              // @needed when coding : https://github.com/presscustomizr/nimble-builder/issues/491
                              $(this).attr('data-sek-is-mobile-vertical-menu', deviceWidth < breakpoint ? 'yes' : 'no');
                        });
                  };

                  _setVerticalMobileBooleanAttribute();
                  nb_.cachedElements.$window.on('resize', nb_.debounce( _setVerticalMobileBooleanAttribute, 100) );

                  // HELPER TO DETERMINE IF A NODE BELONGS TO A MOBILE MENU                              
                  // this is the element
                  var nodeBelongsToAMobileMenu = function() {
                        if ( this.length && this.length > 0 ) {
                              // Note that [data-sek-is-mobile-vertical-menu] value is set on page load and dynamically on window resize
                              return "yes" === this.closest('[data-sek-is-mobile-vertical-menu]').attr('data-sek-is-mobile-vertical-menu');
                        }
                        return false;
                  };





                  //DESKTOP DROPDOWN
                  var desktopDropdownOnHover = function() {
                        //dropdown
                        var   DATA_KEY  = 'sek.sekDropdown',
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


                        //Handle dropdown on hover via js
                        var dropdownMenuOnHover = function() {
                              var _dropdown_selector = Selector.HOVER_PARENT;

                              bindEvents();

                              function _addOpenClass( evt ) {
                                    var $_el = $(this),
                                          $_child_dropdown = $_el.find( Selector.CHILD_DROPDOWN ).first();

                                    // Jan 2021 : start of a fix for https://github.com/presscustomizr/nimble-builder/issues/772
                                    if ( nb_.cachedElements.$body.hasClass('is-touch-device') ) {
                                          // When navigating the regular menu ( horizontal ) on a mobile touch device, typically a tablet in landscape orientation
                                          // we want to prevent opening the link of a parent menu if the children are not displayed yet
                                          if ( "true" != $_child_dropdown.attr('aria-expanded') && !nodeBelongsToAMobileMenu.call($_child_dropdown) ) {
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

                              function bindEvents() {
                                    // april 2020 : is-touch-device class is added on body on the first touch
                                    // This way, we can prevent the problem reported on https://github.com/presscustomizr/customizr/issues/1824
                                    // ( two touches needed to reveal submenus on touch devices )
                                    nb_.cachedElements.$body.on('touchstart', function() {
                                          if ( !$(this).hasClass('is-touch-device') ) {
                                                $(this).addClass('is-touch-device');
                                          }
                                    });
                                    //BIND
                                    nb_.cachedElements.$body
                                          .on( 'mouseenter', _dropdown_selector, function(evt) {
                                                if ( !nodeBelongsToAMobileMenu.call($(this)) ) {
                                                      _addOpenClass.call($(this), evt );
                                                }
                                          })
                                          .on( 'mouseleave', _dropdown_selector , function(evt) {
                                                if ( !nodeBelongsToAMobileMenu.call($(this)) ) {
                                                      _removeOpenClass.call($(this), evt );
                                                }
                                          })
                                          .on( 'click', _dropdown_selector, function(evt) {
                                                if ( !nodeBelongsToAMobileMenu.call($(this)) ) {
                                                      _addOpenClass.call($(this), evt );
                                                }
                                          });
                              }
                        },

                        // DESKTOP SNAKE
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
                  };//desktopDropdownOnHover

            
                  // FIRE DESKTOP MENU METHODS
                  desktopDropdownOnHover();











                  // MOBILE MENU HAMBURGER BUTTON
                  // handle the mobile hamburger hover effect
                  $( document )
                        .on( 'mouseenter', '.sek-nav-toggler', function(){ $(this).addClass( 'hovering' ); } )
                        .on( 'mouseleave', '.sek-nav-toggler', function(){ $(this).removeClass( 'hovering' ); } )
                        .on( 'show.sek.sekCollapse hide.sek.sekCollapse', '.sek-nav-collapse', function() {
                              $('[data-target="#'+$(this).attr('id')+'"]').removeClass( 'hovering' );
                              nb_.cachedElements.$window.trigger('scroll');
                        });


                  // MOBILE MENU VISIBILITY
                  toggleMobileMenuVisibility = function() {
                        var EVENT_KEY = ".nbMobMenuBtn",
                        TRANSITION_DURATION = 400,
                        Event = {
                              SHOW: "show" + EVENT_KEY,
                              SHOWN: "shown" + EVENT_KEY,
                              HIDE: "hide" + EVENT_KEY,
                              HIDDEN: "hidden" + EVENT_KEY,
                              CLICK_EVENT: "click" + EVENT_KEY
                        },
                        ClassName = {
                              COLLAPSING: 'sek-collapsing',
                              COLLAPSED: 'sek-collapsed'
                        },
                        Selector = {
                              MM_TOGGLER: '.sek-nav-toggler'
                        };

                        // attach click event
                        nb_.cachedElements.$body.on( Event.CLICK_EVENT, Selector.MM_TOGGLER, function (event, params) {
                              // preventDefault only for <a> elements (which change the URL) not inside the collapsible element
                              if (event.currentTarget.tagName === 'A') {
                                    event.preventDefault();
                              }

                              var $toggler             = $(this),
                                    //get the data toggle
                                    _mob_menu_selector = $toggler.data('target');

                              $(_mob_menu_selector).each( function () {
                                    var $mobMenuWrapper = $(this),
                                          mobMenuIsExpanded = "expanded" === $mobMenuWrapper.attr('data-sek-mm-state'),
                                          $maybeHeaderParentEl = $mobMenuWrapper.closest('#nimble-header');

                                          // console.log('"$mobMenuWrapper ?', $mobMenuWrapper );
                                          // console.log('mobMenuIsExpanded ?', mobMenuIsExpanded );

                                    $mobMenuWrapper.stop()[ mobMenuIsExpanded ? 'slideUp' : 'slideDown' ]({
                                          duration: (params && params.close_fast) ? 0 : TRANSITION_DURATION,
                                          start : function() {
                                                $mobMenuWrapper.addClass(ClassName.COLLAPSING).trigger( mobMenuIsExpanded ? Event.HIDE : Event.SHOW );
                                                if ( mobMenuIsExpanded ) {
                                                      $toggler.addClass( ClassName.COLLAPSED ).attr( 'aria-expanded', 'false' );
                                                      if ( $maybeHeaderParentEl.length > 0 ) {
                                                            $maybeHeaderParentEl.removeClass('sek-header-mobile-menu-expanded');
                                                      }
                                                } else {
                                                      $toggler.removeClass( ClassName.COLLAPSED ).attr( 'aria-expanded', 'true' );
                                                      $mobMenuWrapper.attr('data-sek-mm-state', 'expanded');
                                                      if ( $maybeHeaderParentEl.length > 0 ) {
                                                            $maybeHeaderParentEl.addClass('sek-header-mobile-menu-expanded');
                                                      }
                                                }
                                          },
                                          complete: function() {
                                                // console.log('SOO DATA ?', mobMenuIsExpanded, $mobMenuWrapper.attr('data-sek-mm-state') );
                                                if ( mobMenuIsExpanded ) {
                                                      $mobMenuWrapper.removeClass(ClassName.COLLAPSING).trigger(Event.HIDDEN);
                                                      $mobMenuWrapper.attr('data-sek-mm-state', 'collapsed');
                                                } else {
                                                      $mobMenuWrapper.removeClass(ClassName.COLLAPSING).trigger(Event.SHOWN);
                                                }
                                                //remove all the inline style added by the slideUp/Down methods
                                                $mobMenuWrapper.css({
                                                      'display'    : '',
                                                      'paddingTop' : '',
                                                      'marginTop' : '',
                                                      'paddingBottom' : '',
                                                      'marginBottom' : '',
                                                      'height' : ''
                                                });
                                          }
                                    });//end slideUp/slideDown
                              });//end each
                        });//end attach click event

                        // close mobile menu on resize event
                        nb_.cachedElements.$window.on('resize', nb_.debounce( function() {
                              $(Selector.MM_TOGGLER).each(function() {
                                    var associated_mob_menu_selector = $(this).data('target');
                                    if ( 'true' == $(this).attr( 'aria-expanded' ) ) {
                                          if ( $(associated_mob_menu_selector).length && !nodeBelongsToAMobileMenu.call( $(associated_mob_menu_selector) ) ) {
                                                $(this).trigger(Event.CLICK_EVENT, {close_fast:true});
                                          }
                                    }
                              });
                        }, 100) );
                  };//toggleMobileMenuVisibility()

                  toggleMobileMenuVisibility();












                  ////////////////////////////////////////////////////////////////////////
                  //////////// COLLAPSIBLE MENU ( janv 2021 )
                  //hueman theme inspired
                  var maybeApplyCollapsibleMenu = function() {
                        var $mobMenuWrapper  = this;
                        if ( 'true' == $mobMenuWrapper.data('nb-mm-menu-is-instantiated') )
                              return;

                        // Flag so we don't instantiate twice ( typically when previewing)
                        $mobMenuWrapper.data('nb-mm-menu-is-instantiated', 'true');

                        //specific class added to this mobile menu which tells its submenus have to be expanded on click (purpose: style)
                        $mobMenuWrapper.addClass( 'nb-collapsible-mobile-menu' );
                        
                        var EVENT_KEY   = '.nb.submenu',
                        Event       = {
                              SHOW     : 'show' + EVENT_KEY,
                              HIDE     : 'hide' + EVENT_KEY,
                              CLICK    : 'mousedown' + EVENT_KEY,
                              FOCUSIN  : 'focusin' + EVENT_KEY,
                              FOCUSOUT : 'focusout' + EVENT_KEY
                        },
                        Classname   = {
                              DD_TOGGLE_ON_CLICK    : 'nb-collapsible-mobile-menu',
                              SHOWN                 : 'expanded',
                              DD_TOGGLE             : 'nb-dd-mm-toggle',
                              DD_TOGGLE_WRAPPER     : 'nb-dd-mm-toggle-wrapper',
                              SCREEN_READER         : 'screen-reader-text',

                        },
                        Selector    = {
                              DD_TOGGLE_PARENT      : '.menu-item-has-children, .page_item_has_children',
                              CURRENT_ITEM_ANCESTOR : '.current-menu-ancestor',
                              SUBMENU               : '.sub-menu'
                        },
                        // Add dropdown toggle that displays child menu items.
                        dropdownToggle        = $( '<button />', { 'class': Classname.DD_TOGGLE, 'aria-expanded': false })
                                                .append(' <i class="nb-arrow-for-mobile-menu"></i>' )
                                                .append( $( '<span />', { 'class': Classname.SCREEN_READER, text: 'Expand' } ) ),
                        dropdownToggleWrapper = $( '<span />', { 'class': Classname.DD_TOGGLE_WRAPPER })
                                                .append( dropdownToggle );

                        //add dropdown toggler button to each submenu parent item (li)
                        $mobMenuWrapper.find( Selector.DD_TOGGLE_PARENT ).children('a').after( dropdownToggleWrapper );

                        // Set the active submenu dropdown toggle button initial state.
                        // $mobMenuWrapper.find( Selector.CURRENT_ITEM_ANCESTOR +'>.'+ Classname.DD_TOGGLE_WRAPPER +' .'+ Classname.DD_TOGGLE )
                        //       .addClass( Classname.SHOWN )
                        //       .attr( 'aria-expanded', 'true' )
                        //       .find( '.'+Classname.SCREEN_READER )
                        //       .text( 'Collapse' );

                        // Set the active submenu initial state.
                        // $mobMenuWrapper.find( Selector.CURRENT_ITEM_ANCESTOR +'>'+ Selector.SUBMENU ).addClass( Classname.SHOWN );
                        // $mobMenuWrapper.find( Selector.CURRENT_ITEM_ANCESTOR ).addClass( Classname.SHOWN );

                        $( $mobMenuWrapper )
                              //when clicking on a menu item whose href is just a "#", let's emulate a click on the caret dropdown
                              .on( Event.CLICK, 'a[href="#"]', function(evt) {
                                    if ( !nodeBelongsToAMobileMenu.call( $mobMenuWrapper ) )
                                          return;

                                    evt.preventDefault();
                                    evt.stopPropagation();
                                    $(this).next('.'+Classname.DD_TOGGLE_WRAPPER).find('.'+Classname.DD_TOGGLE).trigger( Event.CLICK );
                              })
                              //when clicking on the toggle button
                              //1) trigger the appropriate "internal" event: hide or show
                              //2) maybe collapse all other open submenus within this menu
                              .on( Event.CLICK, '.'+Classname.DD_TOGGLE, function( e ) {
                                    e.preventDefault();

                                    var $_this = $( this );
                                    $_this.trigger( $_this.closest( Selector.DD_TOGGLE_PARENT ).hasClass( Classname.SHOWN ) ? Event.HIDE: Event.SHOW  );

                                    //close other submenus
                                    _clearMenus( $_this );
                              })
                              //when the hide/show event is triggered
                              //1) toggle the toggle parent menu item (li) expanded class
                              //2) expand/collapse the submenu(ul)
                              //2.1) on expansion/collapse completed change aria attribute and screenreader text
                              //2.2) toggle the subemnu (ul.sub-menu) expanded class
                              //2.3) clear any inline CSS applied by the slideDown/slideUp jQuery functions : the visibility is completely handled via CSS (expanded class)
                              //     we use the aforementioned method only for the animations
                              .on( Event.SHOW+' '+Event.HIDE, '.'+Classname.DD_TOGGLE, function( e ) {
                                    var $_this = $( this );

                                    $_this.closest( Selector.DD_TOGGLE_PARENT ).toggleClass( Classname.SHOWN );

                                    $_this.closest('.'+Classname.DD_TOGGLE_WRAPPER).next( Selector.SUBMENU )
                                          .stop()[Event.SHOW == e.type + '.' + e.namespace  ? 'slideDown' : 'slideUp']( {
                                                duration: 300,
                                                complete: function() {
                                                      var _to_expand =  'false' === $_this.attr( 'aria-expanded' );
                                                      $submenu   = $(this);

                                                      $_this.attr( 'aria-expanded', _to_expand )
                                                            .find( '.'+Classname.SCREEN_READER )
                                                            .text( _to_expand ? 'collapse' : 'expand' );

                                                      $submenu.toggleClass( Classname.SHOWN );
                                                      //resets remaining inline CSS rules
                                                      $submenu.css({
                                                            'display'    : '',
                                                            'paddingTop' : '',
                                                            'marginTop' : '',
                                                            'paddingBottom' : '',
                                                            'marginBottom' : '',
                                                            'height' : ''
                                                      });
                                                }
                                          });
                              })

                              // Keyboard navigation ( August 2019 )
                              // https://github.com/presscustomizr/hueman/issues/819
                              //when focusin on a menu item whose href is just a "#", let's emulate a click on the caret dropdown
                              .on( Event.FOCUSIN, 'a[href="#"]', function(evt) {
                                    if ( !nodeBelongsToAMobileMenu.call( $mobMenuWrapper ) )
                                          return;
                                    evt.preventDefault();
                                    evt.stopPropagation();
                                    $(this).next('.'+Classname.DD_TOGGLE_WRAPPER).find('.'+Classname.DD_TOGGLE).trigger( Event.FOCUSIN );
                              })
                              .on( Event.FOCUSOUT, 'a[href="#"]', function(evt) {
                                    if ( !nodeBelongsToAMobileMenu.call( $mobMenuWrapper ) )
                                          return;
                                    evt.preventDefault();
                                    evt.stopPropagation();
                                    nb_.delay( function() {
                                          $(this).next('.'+Classname.DD_TOGGLE_WRAPPER).find('.'+Classname.DD_TOGGLE).trigger( Event.FOCUSOUT );
                                    }, 250 );
                              })
                              //when focusin on the toggle button
                              //1) trigger the appropriate "internal" event: hide or show
                              //2) maybe collapse all other open submenus within this menu
                              .on( Event.FOCUSIN, '.'+Classname.DD_TOGGLE, function( e ) {
                                    e.preventDefault();

                                    var $_this = $( this );
                                    $_this.trigger( Event.SHOW );
                                    //close other submenus
                                    //_clearMenus( mobMenu, $_this );
                              })
                              .on( Event.FOCUSIN, function( evt ) {
                                    evt.preventDefault();
                                    if ( $(evt.target).length > 0 ) {
                                          $(evt.target).addClass( 'nb-mm-focused');
                                    }
                              })
                              .on( Event.FOCUSOUT,function( evt ) {
                                    evt.preventDefault();

                                    var $_this = $( this );
                                    nb_.delay( function() {
                                          if ( $(evt.target).length > 0 ) {
                                                $(evt.target).removeClass( 'nb-mm-focused');
                                          }
                                          // if ( $mobMenuWrapper.find('.nb-mm-focused').length < 1 ) {
                                          //       console.log('TOP DO => TRIGGER MOBILE MENU COLLAPSE NOW');
                                          //       //mobMenu( 'collapsed');
                                          // }
                                    }, 200 );

                              });

                        //bs dropdown inspired
                        var _clearMenus = function( $_toggle ) {
                              var _parentsToNotClear = $.makeArray( $_toggle.parents( Selector.DD_TOGGLE_PARENT ) ),
                                    _toggles           = $.makeArray( $( '.'+Classname.DD_TOGGLE, $mobMenuWrapper ) );

                              for (var i = 0; i < _toggles.length; i++) {
                                    var _parent = $(_toggles[i]).closest( Selector.DD_TOGGLE_PARENT )[0];

                                    if (!$(_parent).hasClass( Classname.SHOWN ) || $.inArray(_parent, _parentsToNotClear ) > -1 ){
                                          continue;
                                    }

                                    $(_toggles[i]).trigger( Event.HIDE );
                              }
                        };
                  };//maybeApplyCollapsibleMenu()



                  // Instanciate collabsible menu
                  $('.sek-nav-wrap').each( function() {
                        try { maybeApplyCollapsibleMenu.call($(this) ); } catch( er ) {
                              console.log('NB error => collapsible menu', er );
                        }
                  });
                  // When previewing, react to level refresh
                  // This can occur to any level. We listen to the bubbling event on 'body' tag
                  nb_.cachedElements.$body.on('sek-level-refreshed sek-modules-refreshed sek-columns-refreshed sek-section-added', function( evt ){
                        $('.sek-nav-wrap').each( function() {
                              try { maybeApplyCollapsibleMenu.call($(this) ); } catch( er ) {
                                    console.log('NB error => collapsible menu', er );
                              }
                        });
                  });







                  // How to have a logo plus an hamburger in mobiles on the same line?
                  // => clone the menu module, and append it to the closest sektion-inner wrapper
                  // => this way it will occupy 100% of the width
                  // => and also the clone inherits the style of the module
                  // https://github.com/presscustomizr/nimble-builder/issues/368
                  var mayBeCloneMobileMenu = function() {
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
                              // remove the duplicate button
                              $( '.sek-nav-toggler', '#'+_new_id+'-wrapper' ).detach();
                              // update the toggler button so that will now refer to the "cloned" mobile menu
                              $( '.sek-nav-toggler', this ).data( 'target', '#' + _new_id )
                                                            .attr( 'aria-controls', _new_id );
                              // flag setup done
                              $(this).data('sek-setup-menu-mobile-expanded-below-done', true );
                        });//$.each()
                  };
                  mayBeCloneMobileMenu();

                  // When previewing, react to level refresh
                  // This can occur to any level. We listen to the bubbling event on 'body' tag
                  nb_.cachedElements.$body.on('sek-level-refreshed sek-modules-refreshed sek-columns-refreshed sek-section-added', function( evt ){
                        // clean the previously duplicated menu if any
                        $('.sek-mobile-menu-expanded-below').remove();
                        mayBeCloneMobileMenu();
                  });




            });//jQuery( function($){})

      };/////////////// callbackFunc
      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));
