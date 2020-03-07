// global sekFrontLocalized, nimbleListenTo
if ( window.nb_ === void 0 && window.console && window.console.log ) {
    console.log('Nimble error => window.nb_ global not instantiated');
}
(function(w, d){
    var callbackFunc = function() {
        jQuery( function($){
              // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp, isError, isMap, isWeakMap, isSet, isWeakSet
              // see https://underscorejs.org/docs/underscore.html#section-149
              jQuery.each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp', 'Error', 'Symbol', 'Map', 'WeakMap', 'Set', 'WeakSet'], function(index, name) {
                window.nb_['is' + name] = function(obj) {
                  return toString.call(obj) === '[object ' + name + ']';
                };
              });

              $.extend( nb_, {
                    cachedElements : {
                        $window : $(window),
                        $body : $('body')
                    },
                    isMobile : function() {
                          return ( nb_.isFunction( window.matchMedia ) && matchMedia( 'only screen and (max-width: 768px)' ).matches ) || ( this.isCustomizing() && 'desktop' != this.previewedDevice );
                    },
                    isCustomizing : function() {
                          return this.cachedElements.$body.hasClass('is-customizing') || ( 'undefined' !== typeof wp && 'undefined' !== typeof wp.customize );
                    },
                    previewedDevice : 'desktop',
                    //Simple Utility telling if a given Dom element is currently in the window <=> visible.
                    //Useful to mimic a very basic WayPoint
                    isInWindow : function( $_el, threshold ) {
                          if ( ! ( $_el instanceof $ ) )
                            return;
                          if ( threshold && ! nb_.isNumber( threshold ) )
                            return;

                          var sniffFirstVisiblePrevElement = function( $el ) {
                              if ( $el.length > 0 && $el.is(':visible') )
                                return $el;
                              var $prev = $el.prev();
                              // if there's a previous sibling and this sibling is visible, use it
                              if ( $prev.length > 0 && $prev.is(':visible') ) {
                                  return $prev;
                              }
                              // if there's a previous sibling but it's not visible, let's try the next previous sibling
                              if ( $prev.length > 0 && !$prev.is(':visible') ) {
                                  return sniffFirstVisiblePrevElement( $prev );
                              }
                              // if no previous sibling visible, let's go up the parent level
                              var $parent = $el.parent();
                              if ( $parent.length > 0 ) {
                                  return sniffFirstVisiblePrevElement( $parent );
                              }
                              // we don't have siblings or parent
                              return null;
                          };

                          // Is the candidate visible ? <= not display:none
                          // If not visible, we can't determine the offset().top because of https://github.com/presscustomizr/nimble-builder/issues/363
                          // So let's sniff up in the DOM to find the first visible sibling or container
                          var $el_candidate = sniffFirstVisiblePrevElement( $_el );
                          if ( !$el_candidate || $el_candidate.length < 1 )
                            return false;

                          var wt = this.cachedElements.$window.scrollTop(),
                              wb = wt + this.cachedElements.$window.height(),
                              it  = $_el.offset().top,
                              ib  = it + $_el.height(),
                              th = threshold || 0;

                          return ib >= wt - th && it <= wb + th;
                    },//isInWindow
                    // params = {
                    //  path : 'js/libs/swiper.min.js'
                    //  complete : function() {
                    //    $.ajax( {
                        //       url : sekFrontLocalized.frontAssetsPath + 'js/prod-front-simple-slider-module.min.js?'+sekFrontLocalized.assetVersion,
                        //       cache : true,// use the browser cached version when available
                        //       dataType: "script"
                        // }).done(function() {
                        //       //the script is loaded. Say it globally.
                        //       nb_.scriptsLoadingStatus.swiper.resolve();
                        // }).fail( function() {
                        //       nb_.errorLog('script instantiation failed');
                        // });
                    //  }
                    //  loadcheck : 'function' === typeof( window.Swiper )
                    // }
                    ajaxLoadScript : function( params ) {
                        params = $.extend( { path : '', complete : '', loadcheck : false }, params );
                        // Bail if the load request has already been made, but not yet finished.
                        if ( nb_.scriptsLoadingStatus[params.path] && 'pending' === nb_.scriptsLoadingStatus[params.path].state() ) {
                          return;
                        }
                        // set the script loading status now to avoid several calls
                        nb_.scriptsLoadingStatus[params.path] = nb_.scriptsLoadingStatus[params.path] || $.Deferred();
                        $.ajax( {
                              url : sekFrontLocalized.frontAssetsPath + params.path + '?'+ sekFrontLocalized.assetVersion,
                              cache : true,// use the browser cached version when available
                              dataType: "script"
                        }).done(function() {
                              console.log( params.path + ' is loaded', params );
                              if ( nb_.isFunction(params.loadcheck) && !params.loadcheck() ) {
                                  nb_.errorLog('ajaxLoadScript success but loadcheck failed for => ' + params.path );
                                  return;
                              }

                              if ( 'function' === typeof params.complete ) {
                                  params.complete();
                              }
                        }).fail( function() {
                              nb_.errorLog('ajaxLoadScript failed for => ' + params.path );
                        });
                    },//ajaxLoadScript
                    // params = {
                    //  elements : $swiperCandidate,
                    //  func : function() {}
                    // }
                    maybeLoadAssetsWhenSelectorInScreen : function( params ) {
                        params = $.extend( { elements : '', func : '' }, params );
                        console.log('params in maybeLoadScriptWhenSelectorInScreen', params );
                        if ( 1 > $(params.elements).length )
                          return;
                        if ( !nb_.isFunction( params.func ) )
                          return;

                        // Fire now or schedule when becoming visible.
                        var isLoading = false;
                        $.each( $(params.elements), function( k, el ) {
                            if ( !isLoading && nb_.isInWindow($(el) ) ) {
                                isLoading = true;
                                params.func();
                            }
                        });
                        if ( !isLoading ) {
                              _scrollHandle = nb_.throttle( function() {
                                    $.each( $(params.elements), function( k, el ) {
                                        if ( !isLoading && nb_.isInWindow( $(el) ) ) {
                                            isLoading = true;
                                            params.func();
                                        }
                                    });
                              }, 100 );
                              nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
                        }
                    }
              });//$.extend( nb_

              // now that nb_ has been populated, let's say it to the app
              nb_.emit('nimble-app-ready');
          });// jQuery( function($){
    };
    // 'nimble-jquery-loaded' is fired @'wp_footer' see inline script in ::_schedule_front_and_preview_assets_printing()
    window.nb_.listenTo('nimble-jquery-loaded', callbackFunc );

}(window, document));/*global jQuery */
/*!
* FitText.js 1.2
*
* Copyright 2011, Dave Rupert http://daverupert.com
* Released under the WTFPL license
* http://sam.zoy.org/wtfpl/
*
* Date: Thu May 05 14:23:00 2011 -0600
*/
// global sekFrontLocalized, nimbleListenTo
(function(w, d){
      var callbackFunc = function() {
            (function( $ ){
                $.fn.fitText = function( kompressor, options ) {

                  // Setup options
                  var compressor = kompressor || 1,
                      settings = $.extend({
                        'minFontSize' : Number.NEGATIVE_INFINITY,
                        'maxFontSize' : Number.POSITIVE_INFINITY
                      }, options);

                  return this.each(function(){

                    // Store the object
                    var $this = $(this);

                    // Resizer() resizes items based on the object width divided by the compressor * 10
                    var resizer = function () {
                      $this.css('font-size', Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)));
                    };

                    // Call once to set.
                    resizer();

                    // Call on resize. Opera debounces their resize by default.
                    nb_.cachedElements.$window.on('resize.fittext orientationchange.fittext', resizer);

                  });
                };
            })( jQuery );

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
            // if ( 'function' == typeof(_) && window.wp && ! nb_.isUndefined( wp.customize ) ) {
            //     wp.customize.selectiveRefresh.bind('partial-content-rendered' , function() {
            //         doFitText();
            //     });
            // }
      };// onJQueryReady

      // on 'nimble-app-ready', jQuery is loaded
      window.nb_.listenTo('nimble-app-ready', callbackFunc );
}(window, document));// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SCROLL TO ANCHOR
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery( function($){
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
            nb_.cachedElements.$body.find('.menu-item' ).on( 'click', 'a', maybeScrollToAnchor );

            // animate an anchor link inside Nimble sections
            // fixes https://github.com/presscustomizr/nimble-builder/issues/443
            $('[data-sek-level="location"]' ).on( 'click', 'a', maybeScrollToAnchor );
        });
    };/////////////// callbackFunc

    window.nb_.listenTo('nimble-app-ready', callbackFunc );
}(window, document));// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
 /* ------------------------------------------------------------------------- */
(function(w, d){
      var callbackFunc = function() {
          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;
              var _scrollHandle = function() {},//abstract that we can unbind
                  doLoad = function() {
                    // I've been executed forget about me
                    nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                    if ( sekFrontLocalized.load_front_partial_css_on_scroll ) {
                        //Load the style
                        if ( $('head').find( '#czr-magnific-popup' ).length < 1 ) {
                              $('head').append( $('<link/>' , {
                                    rel : 'stylesheet',
                                    id : 'czr-magnific-popup',
                                    type : 'text/css',
                                    href : sekFrontLocalized.frontAssetsPath + 'css/libs/magnific-popup.min.css?' + sekFrontLocalized.assetVersion
                              }) );
                        }
                    }

                    if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_front_module_js_on_scroll ) {
                          nb_.ajaxLoadScript({
                              path : 'js/libs/jquery-magnific-popup.min.js',
                              loadcheck : function() { return nb_.isFunction( $.fn.magnificPopup ); }
                          });
                    }
                };// doLoad

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-magnific-popup-loaded' listened to by window.nb_.listenTo()
            if ( sekFrontLocalized.load_front_partial_css_on_scroll || sekFrontLocalized.load_front_module_js_on_scroll ) {
                nb_.maybeLoadAssetsWhenSelectorInScreen( {
                    elements : $linkCandidates,
                    func : doLoad
                });
            }

        });//jQuery(function($){})
    };/////////////// callbackFunc
    // When loaded with defer, we can not be sure that jQuery will be loaded before
    window.nb_.listenTo( 'nimble-magnific-popup-dependant', function() {
        window.nb_.listenTo('nimble-app-ready', callbackFunc );
    });

}(window, document));

(function(w, d){
      var callbackFunc = function() {
          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;

              $linkCandidates.each( function() {
                  $linkCandidate = $(this);
                  // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
                  if ( $linkCandidate.length < 1 || 'string' !== typeof( $linkCandidate[0].protocol ) || -1 !== $linkCandidate[0].protocol.indexOf('javascript') )
                    return;
                  // Abort if candidate already setup
                  if ( true === $linkCandidate.data('nimble-mfp-done') )
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
                        nb_.errorLog( 'error in callback of nimble-magnific-popup-loaded => ', er );
                  }
                  $linkCandidate.data('nimble-mfp-done', true );
              });
          });//jQuery(function($){})
    };/////////////// callbackFunc

    window.nb_.listenTo('nimble-magnific-popup-loaded', callbackFunc );
}(window, document));










/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){

            // Load js plugin if needed
            // // when the plugin is loaded => it emits 'nimble-swiper-ready' listened to by window.nb_.listenTo()
            // if ( nb_.scriptsLoadingStatus.swiper && 'resolved' === nb_.scriptsLoadingStatus.swiper.state() )
            //   return;
            var _scrollHandle = function() {},//abstract that we can unbind
                doLoad = function() {
                  // I've been executed forget about me
                  // so we execute the callback only once
                  nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                  //Load the style
                  if ( sekFrontLocalized.load_front_partial_css_on_scroll ) {
                      if ( $('head').find( '#czr-swiper' ).length < 1 ) {
                            $('head').append( $('<link/>' , {
                                  rel : 'stylesheet',
                                  id : 'czr-swiper',
                                  type : 'text/css',
                                  href : sekFrontLocalized.frontAssetsPath + 'css/libs/swiper.min.css?'+sekFrontLocalized.assetVersion
                            }) );
                      }
                  }
                  if ( sekFrontLocalized.load_front_module_js_on_scroll ) {
                      nb_.ajaxLoadScript({
                          path : 'js/libs/swiper.min.js',
                          loadcheck : function() { return nb_.isFunction( window.Swiper ); },
                          complete : function() {
                              nb_.ajaxLoadScript({
                                  path : 'js/prod-front-simple-slider-module.min.js',
                              });
                          }
                      });
                  }
            };// doLoad

            // is it already loaded ?
            if ( nb_.isFunction( window.Swiper ) )
              return;

            // do we have candidate selectors printed on page ?
            var $swiperCandidates = $('[data-sek-module-type="czr_img_slider_module"]');
            if ( $swiperCandidates.length < 1 )
              return;

            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                elements : $swiperCandidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    // When loaded with defer, we can not be sure that jQuery will be loaded before
    // on 'nimble-app-ready', jQuery is loaded
    window.nb_.listenTo( 'nimble-swiper-dependant', function() {
        window.nb_.listenTo('nimble-app-ready', callbackFunc );
    });
}(window, document));






/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
(function(w, d){
    window.nb_.listenTo('nimble-lazyload-ready', function() {
        jQuery(function($){
              $('.sektion-wrapper').each( function() {
                    try { $(this).nimbleLazyLoad(); } catch( er ) {
                          nb_.errorLog( 'error with nimbleLazyLoad => ', er );
                    }
              });
        });
    });

    window.nb_.listenTo('nimble-jquery-loaded', function() {
        if ( !sekFrontLocalized.load_front_module_js_on_scroll )
            return;
        jQuery(function($){
              nb_.ajaxLoadScript({
                  path : 'js/libs/nimble-smartload.min.js',
                  loadcheck : function() { return nb_.isFunction( $.fn.nimbleLazyLoad ); }
              });
        });
    });
}(window, document));









/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD FONTAWESOME ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            // we don't need to inject font awesome if already enqueued by a theme
            if ( !sekFrontLocalized.load_front_assets_on_scroll || sekFrontLocalized.fontAwesomeAlreadyEnqueued )
              return;

            var modulesPrintedOnPage = sekFrontLocalized.contextuallyActiveModules,
                fontAwesomeCandidates = [],
                $fontAwesomeCandidates;

            // $.each( modulesFADependant, function( key, moduleType ) {
            //     if ( !nb_.isUndefined( modulesPrintedOnPage[moduleType] ) ) {
            //         var _candidate = '[data-sek-module-type="'+ moduleType +'"]';
            //         if ( $(_candidate).length > 0 ) {
            //             fontAwesomeCandidates.push( _candidate );
            //         }
            //     }
            // });
            $fontAwesomeCandidates = $('i[class*=fa-]');

            if ( $fontAwesomeCandidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits "nimble-fa-dependant" listened to by window.nb_.listenTo()
            var _scrollHandle = function() {},//abstract that we can unbind
                doLoad = function() {
                  // I've been executed forget about me
                  // so we execute the callback only once
                  nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                  //Load the style
                  if ( $('head').find( '#czr-font-awesome' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'czr-font-awesome',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'fonts/css/fontawesome-all.min.css?'+sekFrontLocalized.assetVersion
                        }) );
                  }
            };// doLoad
            var isLoading = false;
            // Fire now or schedule when becoming visible.
            $.each( $fontAwesomeCandidates, function( k, el ) {
                if ( !isLoading && nb_.isInWindow($(el) ) ) {
                    isLoading = true;
                    doLoad();
                }
            });
            if ( !isLoading ) {
                  _scrollHandle = nb_.throttle( function() {
                        $.each( $fontAwesomeCandidates, function( k, el ) {
                            if ( !isLoading && nb_.isInWindow( $(el) ) ) {
                                isLoading = true;
                                doLoad();
                            }
                        });
                  }, 100 );
                  nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
            }
        });//jQuery(function($){})
    };/////////////// callbackFunc

    // When loaded with defer, we can not be sure that jQuery will be loaded before
    //  on 'nimble-app-ready', jQuery is loaded
    window.nb_.listenTo( 'nimble-fa-dependant', function() {
        window.nb_.listenTo( 'nimble-app-ready', callbackFunc );
    });
}(window, document));










/* ------------------------------------------------------------------------- *
 *  BG PARALLAX
/* ------------------------------------------------------------------------- */
(function(w, d){
    window.nb_.listenTo('nimble-parallax-ready', function() {
        jQuery(function($){
              $('[data-sek-bg-parallax="true"]').each( function() {
                    $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
              });
              var _setParallaxWhenCustomizing = function() {
                    $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
                    // hack => always trigger a 'resize' event with a small delay to make sure bg positions are ok
                    setTimeout( function() {
                         nb_.cachedElements.$body.trigger('resize');
                    }, 500 );
              };
              // When previewing, react to level refresh
              // This can occur to any level. We listen to the bubbling event on 'body' tag
              // and salmon up to maybe instantiate any missing candidate
              // Example : when a preset_section is injected
              nb_.cachedElements.$body.on('sek-level-refreshed sek-section-added', function( evt ){
                    if ( "true" === $(this).data('sek-bg-parallax') ) {
                          _setParallaxWhenCustomizing.call(this);
                    } else {
                          $(this).find('[data-sek-bg-parallax="true"]').each( function() {
                                _setParallaxWhenCustomizing.call(this);
                          });
                    }
              });
        });
    });
}(window, document));
