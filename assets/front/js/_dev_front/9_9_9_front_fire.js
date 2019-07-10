// global sekFrontLocalized
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('[data-sek-module-type="czr_image_module"]').each( function() {
            $linkCandidate = $(this).find('.sek-link-to-img-lightbox');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $linkCandidate.length < 1 || 'string' !== typeof( $linkCandidate[0].protocol ) || -1 !== $linkCandidate[0].protocol.indexOf('javascript') )
              return;
            if ( 'function' !== typeof( $.fn.magnificPopup ) )
              return;
            try { $linkCandidate.magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                closeBtnInside: true,
                fixedContentPos: true,
                mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
                image: {
                  verticalFit: true
                },
                zoom: {
                  enabled: true,
                  duration: 300 // don't foget to change the duration also in CSS
                }
            }); } catch( er ) {
                  if ( typeof window.console.log === 'function' ) {
                        console.log( er );
                  }
            }
      });
});


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
      $('[data-sek-bg-parallax="true"]').each( function() {
            $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
      });
      var _setParallaxWhenCustomizing = function() {
            $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
            // hack => always trigger a 'resize' event with a small delay to make sure bg positions are ok
            setTimeout( function() {
                 $('body').trigger('resize');
            }, 500 );
      };
      // When previewing, react to level refresh
      // This can occur to any level. We listen to the bubbling event on 'body' tag
      // and salmon up to maybe instantiate any missing candidate
      // Example : when a preset_section is injected
      $('body').on('sek-level-refreshed sek-section-added', function( evt ){
            if ( "true" === $(this).data('sek-bg-parallax') ) {
                  _setParallaxWhenCustomizing.call(this);
            } else {
                  $(this).find('[data-sek-bg-parallax="true"]').each( function() {
                        _setParallaxWhenCustomizing.call(this);
                  });
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

    // does the same as new URL(url)
    // but support IE.
    // @see https://stackoverflow.com/questions/736513/how-do-i-parse-a-url-into-hostname-and-path-in-javascript
    // @see https://gist.github.com/acdcjunior/9820040
    // @see https://developer.mozilla.org/en-US/docs/Web/API/URL#Properties
    var parseURL = function(url) {
          var parser = document.createElement("a");
          parser.href = url;
          // IE 8 and 9 dont load the attributes "protocol" and "host" in case the source URL
          // is just a pathname, that is, "/example" and not "http://domain.com/example".
          parser.href = parser.href;

          // copies all the properties to this object
          var properties = ['host', 'hostname', 'hash', 'href', 'port', 'protocol', 'search'];
          for (var i = 0, n = properties.length; i < n; i++) {
            this[properties[i]] = parser[properties[i]];
          }

          // pathname is special because IE takes the "/" of the starting of pathname
          this.pathname = (parser.pathname.charAt(0) !== "/" ? "/" : "") + parser.pathname;
    };

    var $root = $('html, body');
    var maybeScrollToAnchor = function( evt ){
          // problem to solve : users want to define anchor links that work inside a page, but also from other pages.
          // @see https://github.com/presscustomizr/nimble-builder/issues/413
          var clickedItemUrl = $(this).attr('href');
          if ( '' === clickedItemUrl || null === clickedItemUrl || 'string' !== typeof( clickedItemUrl ) || -1 === clickedItemUrl.indexOf('#') )
            return;

          // an anchor link looks like this : http://mysite.com/contact/#anchor
          var itemURLObject = new parseURL( clickedItemUrl ),
              _currentPageUrl = new parseURL( window.document.location.href );

          if( itemURLObject.pathname !== _currentPageUrl.pathname )
            return;
          if( 'string' !== typeof(itemURLObject.hash) || '' === itemURLObject.hash )
            return;
          var $nimbleTargetCandidate = $('[data-sek-level="location"]' ).find( '[id="' + itemURLObject.hash.replace('#','') + '"]');
          if ( 1 !== $nimbleTargetCandidate.length )
            return;

          evt.preventDefault();
          $root.animate({ scrollTop : $nimbleTargetCandidate.offset().top - 150 }, 400 );
    };

    // animate menu item to Nimble anchors
    $('body').find('.menu-item' ).on( 'click', 'a', maybeScrollToAnchor );

    // animate an anchor link inside Nimble sections
    // fixes https://github.com/presscustomizr/nimble-builder/issues/443
    $('[data-sek-level="location"]' ).on( 'click', 'a', maybeScrollToAnchor );
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
                $('[data-target=#'+$(this).attr('id')+']').removeClass( 'hovering' );
                $(window).trigger('scroll');
          });

    // How to have a logo plus an hamburger in mobiles on the same line?
    // => clone the menu module, and append it to the closest sektion-inner wrapper
    // => this way it will occupy 100% of the width
    // => and also the clone inherits the style of the module
    // https://github.com/presscustomizr/nimble-builder/issues/368
    $( document ).on( 'ready', function() {
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
            $('body').on('sek-level-refreshed sek-modules-refreshed sek-columns-refreshed sek-section-added', function( evt ){
                    // clean the previously duplicated menu if any
                    $('.sek-mobile-menu-expanded-below').remove();
                    _doMobileMenuSetup();
            });
    });
});


/* ------------------------------------------------------------------------- *
 *  SWIPER
/* ------------------------------------------------------------------------- */
jQuery( function($){
    var mySwipers = [];
    var triggerSimpleLoad = function( $_imgs ) {
          if ( 0 === $_imgs.length )
            return;

          $_imgs.map( function( _ind, _img ) {
            $(_img).load( function () {
              $(_img).trigger('simple_load');
            });//end load
            if ( $(_img)[0] && $(_img)[0].complete )
              $(_img).load();
          } );//end map
    };//end of fn


    // Each swiper is instantiated with a unique id
    // so that if we have several instance on the same page, they are totally independant.
    // If we don't use a unique Id for swiper + navigation buttons, a click on a button, make all slider move synchronously.
    var doSingleSwiperInstantiation = function() {
          console.log('MY SWIPER ??', mySwipers );
          var $swiperWrapper = $(this), swiperClass = 'sek-swiper' + $swiperWrapper.data('sek-swiper-id');
          console.log('swiperClass ??', swiperClass );
          var swiperParams = {
              // spaceBetween: 30,
              // effect: 'fade',
              // pagination: {
              //   el: '.swiper-pagination',
              //   clickable: true,
              // },
              loop : true === $swiperWrapper.data('sek-loop'),//Set to true to enable continuous loop mode
              navigation: {
                nextEl: '.swiper-button-next' + $swiperWrapper.data('sek-swiper-id'),
                prevEl: '.swiper-button-prev' + $swiperWrapper.data('sek-swiper-id')
              },
              on : {
                init : function() {
                    console.log('DO ON INIT');
                    if ( 'nimble-wizard' === $swiperWrapper.data('sek-image-layout') ) {
                        $swiperWrapper.find('.sek-carousel-img').each( function() {
                            var $_imgsToSimpleLoad = $(this).nimbleCenterImages({
                                  enableCentering : 1,
                                  enableGoldenRatio : false,
                                  disableGRUnder : 0,//<= don't disable golden ratio when responsive,
                                  zeroTopAdjust: 0,
                                  setOpacityWhenCentered : false,//will set the opacity to 1
                                  oncustom : [ 'simple_load', 'smartload', 'sek-nimble-refreshed' ]
                            })
                            //images with src which starts with "data" are our smartload placeholders
                            //we don't want to trigger the simple_load on them
                            //the centering, will be done on the smartload event (see onCustom above)
                            .find( 'img:not([src^="data"])' );

                            //trigger the simple load
                            _utils_.delay( function() {
                                triggerSimpleLoad( $_imgsToSimpleLoad );
                            }, 10 );

                        });//each()
                    }
                }
              }
          };
          if ( true === $swiperWrapper.data('sek-autoplay') ) {
              $.extend( swiperParams, {
                  autoplay : {
                      delay : $swiperWrapper.data('sek-autoplay-delay'),
                      disableOnInteraction : $swiperWrapper.data('sek-pause-on-hover')
                  }
              });
          } else {
              $.extend( swiperParams, {
                  autoplay : {
                      delay : 999999999//<= the autoplay:false doesn't seem to work...
                  }
              });
          }
          console.log('swiperParams ??',$swiperWrapper.data('sek-autoplay'), swiperParams );
          mySwipers.push( new Swiper(
              '.' + swiperClass,//$(this)[0],
              swiperParams
          ));
    };
    var doAllSwiperInstanciation = function() {
          $('.sektion-wrapper').find('[data-sek-swiper-id]').each( function() {
                doSingleSwiperInstantiation.call($(this));
          });
    };

    // On custom events
    $( 'body').on( 'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed', '[data-sek-level="location"]',
          function() {
            if ( ! _utils_.isEmpty( mySwipers ) ) {
                  _utils_.each( mySwipers, function( _swiperInstance ){
                        _swiperInstance.destroy();
                  });
            }
            mySwipers = [];
            doAllSwiperInstanciation();

            $(this).find('.swiper-container img').each( function() {
                  $(this).trigger('sek-nimble-refreshed');
            });
          }
    );

    // When the stylesheet is refreshed, update the centering with a custom event
    // this is needed when setting the custom height of the slider wrapper
    $( 'body').on( 'sek-stylesheet-refreshed', '[data-sek-module-type="czr_img_slider_module"]',
          function() {
            $(this).find('.swiper-container img').each( function() {
                  $(this).trigger('sek-nimble-refreshed');
            });
          }
    );


    // on load
    $('.sektion-wrapper').find('.swiper-container').each( function() {
          doAllSwiperInstanciation();
    });




    // Behaviour on mouse hover
    // @seehttps://stackoverflow.com/questions/53028089/swiper-autoplay-stop-the-swiper-when-you-move-the-mouse-cursor-and-start-playba
    $('.swiper-slide').on('mouseover mouseout', function( evt ) {
        var swiperInstance = $(this).closest('.swiper-container')[0].swiper;
        if ( ! _utils_.isUndefined( swiperInstance ) && true === swiperInstance.params.autoplay.disableOnInteraction ) {
            switch( evt.type ) {
                case 'mouseover' :
                    swiperInstance.autoplay.stop();
                break;
                case 'mouseout' :
                    swiperInstance.autoplay.start();
                break;
            }
        }
    });

    // When customizing, focus on the currently expanded / edited item
    // @see CZRItemConstructor in api.czrModuleMap.czr_img_slider_collection_child
    if ( ! _utils_.isUndefined( wp.customize ) ) {
          wp.customize.preview.bind('sek-item-focus', function( params ) {

                var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]').first();
                if ( 1 > $itemEl.length )
                  return;
                var $swiperContainer = $itemEl.closest('.swiper-container');
                if ( 1 > $swiperContainer.length )
                  return;

                var activeSwiperInstance = $itemEl.closest('.swiper-container')[0].swiper;

                if ( _utils_.isUndefined( activeSwiperInstance ) )
                  return;
                // we can't rely on internal indexing system of swipe, because it uses duplicate item when infinite looping is enabled
                // jQuery is our friend
                var slideIndex = $( '.swiper-slide', $swiperContainer ).index( $itemEl );
                //http://idangero.us/swiper/api/#methods
                //mySwiper.slideTo(index, speed, runCallbacks);
                activeSwiperInstance.slideTo( slideIndex, 100 );
          });
    }
});












/* ===================================================
 * jqueryCenterImages.js v1.0.0
 * ===================================================
 * (c) 2015 Nicolas Guillaume, Nice, France
 * CenterImages plugin may be freely distributed under the terms of the GNU GPL v2.0 or later license.
 *
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Center images in a specified container
 *
 * =================================================== */
(function ( $, window ) {
      //defaults
      var pluginName = 'nimbleCenterImages',
          defaults = {
                enableCentering : true,
                onresize : true,
                onInit : true,//<= shall we smartload on init or wait for a custom event, typically smartload ?
                oncustom : [],//list of event here
                $containerToListen : null,//<= we might want to listen to custom event trigger to a parent container.Should be a jQuery obj
                imgSel : 'img',
                defaultCSSVal : { width : 'auto' , height : 'auto' },
                leftAdjust : 0,
                zeroLeftAdjust : 0,
                topAdjust : 0,
                zeroTopAdjust : -2,//<= top ajustement for h-centered
                enableGoldenRatio : false,
                goldenRatioLimitHeightTo : 350,
                goldenRatioVal : 1.618,
                skipGoldenRatioClasses : ['no-gold-ratio'],
                disableGRUnder : 767,//in pixels
                useImgAttr:false,//uses the img height and width attributes if not visible (typically used for the customizr slider hidden images)
                setOpacityWhenCentered : false,//this can be used to hide the image during the time it is centered
                addCenteredClassWithDelay : 0,//<= a small delay can be required when we rely on the v-centered or h-centered css classes to set the opacity for example
                opacity : 1
          };

      function Plugin( element, options ) {
            var self = this;
            this.container  = element;
            this.options    = $.extend( {}, defaults, options) ;
            this._defaults  = defaults;
            this._name      = pluginName;
            this._customEvt = $.isArray(self.options.oncustom) ? self.options.oncustom : self.options.oncustom.split(' ');
            this.init();
      }

      //can access this.element and this.option
      //@return void
      Plugin.prototype.init = function () {
            var self = this,
                _do = function( _event_ ) {
                    _event_ = _event_ || 'init';
                    //applies golden ratio to all containers ( even if there are no images in container )
                    self._maybe_apply_golden_r();

                    //parses imgs ( if any ) in current container
                    var $_imgs = $( self.options.imgSel , self.container );

                    //WINDOW RESIZE EVENT ACTIONS
                    //GOLDEN RATIO (before image centering)
                    //creates a golden ratio fn on resize
                    if ( self.options.enableGoldenRatio ) {
                          $(window).bind(
                                'resize',
                                {},
                                _utils_.debounce( function( evt ) { self._maybe_apply_golden_r( evt ); }, 200 )
                          );
                    }


                    //if no images or centering is not active, only handle the golden ratio on resize event
                    if ( 1 <= $_imgs.length && self.options.enableCentering ) {
                          self._parse_imgs( $_imgs, _event_ );
                    }
                };

            //fire
            if ( self.options.onInit ) {
                  _do();
            }

            //console.log('$( self.container )', $( self.container ) );
            //bind the container element with custom events if any
            //( the images will also be bound )
            if ( $.isArray( self._customEvt ) ) {
                  self._customEvt.map( function( evt ) {
                        var $_containerToListen = ( self.options.$containerToListen instanceof $ && 1 < self.options.$containerToListen.length ) ? self.options.$containerToListen : $( self.container );
                        //console.log('container to listen',$_containerToListen, evt  );
                        $_containerToListen.bind( evt, {} , function() {
                              _do( evt );
                        });
                  } );
            }
      };


      //@return void
      Plugin.prototype._maybe_apply_golden_r = function() {
            //check if options are valids
            if ( ! this.options.enableGoldenRatio || ! this.options.goldenRatioVal || 0 === this.options.goldenRatioVal )
              return;

            //make sure the container has not a forbidden class
            if ( ! this._is_selector_allowed() )
              return;
            //check if golden ratio can be applied under custom window width
            if ( ! this._is_window_width_allowed() ) {
                  //reset inline style for the container
                  $(this.container).attr('style' , '');
                  return;
            }

            var new_height = Math.round( $(this.container).width() / this.options.goldenRatioVal );
            //check if the new height does not exceed the goldenRatioLimitHeightTo option
            new_height = new_height > this.options.goldenRatioLimitHeightTo ? this.options.goldenRatioLimitHeightTo : new_height;
            $(this.container)
                  .css({
                        'line-height' : new_height + 'px',
                        height : new_height + 'px'
                  })
                  .trigger('golden-ratio-applied');
      };


      /*
      * @params string : ids or classes
      * @return boolean
      */
      Plugin.prototype._is_window_width_allowed = function() {
            return $(window).width() > this.options.disableGRUnder - 15;
      };


      //@return void
      Plugin.prototype._parse_imgs = function( $_imgs, _event_ ) {
            var self = this;
            $_imgs.each(function ( ind, img ) {
                  var $_img = $(img);
                  self._pre_img_cent( $_img, _event_ );

                  // IMG CENTERING FN ON RESIZE ?
                  // Parse Img can be fired several times, so bind once
                  if ( self.options.onresize && ! $_img.data('resize-react-bound' ) ) {
                        $_img.data('resize-react-bound', true );
                        $(window).resize( _utils_.debounce( function() {
                              self._pre_img_cent( $_img, 'resize');
                        }, 100 ) );
                  }

            });//$_imgs.each()

            // Mainly designed to check if a container is not getting parsed too many times
            if ( $(self.container).attr('data-img-centered-in-container') ) {
                  var _n = parseInt( $(self.container).attr('data-img-centered-in-container'), 10 ) + 1;
                  $(self.container).attr('data-img-centered-in-container', _n );
            } else {
                  $(self.container).attr('data-img-centered-in-container', 1 );
            }
      };



      //@return void
      Plugin.prototype._pre_img_cent = function( $_img ) {

            var _state = this._get_current_state( $_img ),
                self = this,
                _case  = _state.current,
                _p     = _state.prop[_case],
                _not_p = _state.prop[ 'h' == _case ? 'v' : 'h'],
                _not_p_dir_val = 'h' == _case ? ( this.options.zeroTopAdjust || 0 ) : ( this.options.zeroLeftAdjust || 0 );

            var _centerImg = function( $_img ) {
                  $_img
                      .css( _p.dim.name , _p.dim.val )
                      .css( _not_p.dim.name , self.options.defaultCSSVal[ _not_p.dim.name ] || 'auto' )
                      .css( _p.dir.name, _p.dir.val ).css( _not_p.dir.name, _not_p_dir_val );

                  if ( 0 !== self.options.addCenteredClassWithDelay && _utils_.isNumber( self.options.addCenteredClassWithDelay ) ) {
                        _utils_.delay( function() {
                              $_img.addClass( _p._class ).removeClass( _not_p._class );
                        }, self.options.addCenteredClassWithDelay );
                  } else {
                        $_img.addClass( _p._class ).removeClass( _not_p._class );
                  }

                  // Mainly designed to check if a single image is not getting parsed too many times
                  if ( $_img.attr('data-img-centered') ) {
                        var _n = parseInt( $_img.attr('data-img-centered'), 10 ) + 1;
                        $_img.attr('data-img-centered', _n );
                  } else {
                        $_img.attr('data-img-centered', 1 );
                  }
                  return $_img;
            };
            if ( this.options.setOpacityWhenCentered ) {
                  $.when( _centerImg( $_img ) ).done( function( $_img ) {
                        $_img.css( 'opacity', self.options.opacity );
                  });
            } else {
                  _utils_.delay(function() { _centerImg( $_img ); }, 0 );
            }
      };




      /********
      * HELPERS
      *********/
      //@return object with initial conditions : { current : 'h' or 'v', prop : {} }
      Plugin.prototype._get_current_state = function( $_img ) {
            var c_x     = $_img.closest(this.container).outerWidth(),
                c_y     = $(this.container).outerHeight(),
                i_x     = this._get_img_dim( $_img , 'x'),
                i_y     = this._get_img_dim( $_img , 'y'),
                up_i_x  = i_y * c_y !== 0 ? Math.round( i_x / i_y * c_y ) : c_x,
                up_i_y  = i_x * c_x !== 0 ? Math.round( i_y / i_x * c_x ) : c_y,
                current = 'h';
            //avoid dividing by zero if c_x or i_x === 0
            if ( 0 !== c_x * i_x ) {
                  current = ( c_y / c_x ) >= ( i_y / i_x ) ? 'h' : 'v';
            }

            var prop    = {
                  h : {
                        dim : { name : 'height', val : c_y },
                        dir : { name : 'left', val : ( c_x - up_i_x ) / 2 + ( this.options.leftAdjust || 0 ) },
                        _class : 'h-centered'
                  },
                  v : {
                        dim : { name : 'width', val : c_x },
                        dir : { name : 'top', val : ( c_y - up_i_y ) / 2 + ( this.options.topAdjust || 0 ) },
                        _class : 'v-centered'
                  }
            };

            return { current : current , prop : prop };
      };

      //@return img height or width
      //uses the img height and width if not visible and set in options
      Plugin.prototype._get_img_dim = function( $_img, _dim ) {
            if ( ! this.options.useImgAttr )
              return 'x' == _dim ? $_img.outerWidth() : $_img.outerHeight();

            if ( $_img.is(":visible") ) {
                  return 'x' == _dim ? $_img.outerWidth() : $_img.outerHeight();
            } else {
                  if ( 'x' == _dim ){
                        var _width = $_img.originalWidth();
                        return typeof _width === undefined ? 0 : _width;
                  }
                  if ( 'y' == _dim ){
                        var _height = $_img.originalHeight();
                        return typeof _height === undefined ? 0 : _height;
                  }
            }
      };

      /*
      * @params string : ids or classes
      * @return boolean
      */
      Plugin.prototype._is_selector_allowed = function() {
            //has requested sel ?
            if ( ! $(this.container).attr( 'class' ) )
              return true;

            //check if option is well formed
            if ( ! this.options.skipGoldenRatioClasses || ! $.isArray( this.options.skipGoldenRatioClasses )  )
              return true;

            var _elSels       = $(this.container).attr( 'class' ).split(' '),
                _selsToSkip   = this.options.skipGoldenRatioClasses,
                _filtered     = _elSels.filter( function(classe) { return -1 != $.inArray( classe , _selsToSkip ) ;});

            //check if the filtered selectors array with the non authorized selectors is empty or not
            //if empty => all selectors are allowed
            //if not, at least one is not allowed
            return 0 === _filtered.length;
      };


      // prevents against multiple instantiations
      $.fn[pluginName] = function ( options ) {
            return this.each(function () {
                if (!$.data(this, 'plugin_' + pluginName)) {
                    $.data(this, 'plugin_' + pluginName,
                    new Plugin( this, options ));
                }
            });
      };

})( jQuery, window );