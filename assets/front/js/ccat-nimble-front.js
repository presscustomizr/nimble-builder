// global sekFrontLocalized, nb_
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

              //https://underscorejs.org/docs/underscore.html#section-17
              //helper for nb_.delay
              var _restArguments = function(func, startIndex) {
                  startIndex = startIndex == null ? func.length - 1 : +startIndex;
                  return function() {
                      var length = Math.max(arguments.length - startIndex, 0),
                          rest = Array(length),
                          index = 0;
                      for (; index < length; index++) {
                        rest[index] = arguments[index + startIndex];
                      }
                      switch (startIndex) {
                        case 0: return func.call(this, rest);
                        case 1: return func.call(this, arguments[0], rest);
                        case 2: return func.call(this, arguments[0], arguments[1], rest);
                      }
                      var args = Array(startIndex + 1);
                      for (index = 0; index < startIndex; index++) {
                        args[index] = arguments[index];
                      }
                      args[startIndex] = rest;
                      return func.apply(this, args);
                  };
              };
              // helper for nb_.throttle()
              var _now = function() {
                  return Date.now || new Date().getTime();
              };

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
                    elOrFirstVisibleParentIsInWindow : function( element, threshold ) {
                          var $_el = ! ( element instanceof $ ) ? $(element) : element;
                          if ( ! ( $_el instanceof $ ) ) {
                              nb_.errorLog('invalid element in nb_.elOrFirstVisibleParentIsInWindow', $_el );
                              return;
                          }
                          if ( threshold && ! nb_.isNumber( threshold ) ) {
                              nb_.errorLog('invalid threshold in nb_.elOrFirstVisibleParentIsInWindow');
                              return;
                          }

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
                    },//elOrFirstVisibleParentIsInWindow
                    // params = {
                    //  path : 'js/libs/swiper.min.js'
                    //  complete : function() {
                    //    $.ajax( {
                        //       url : sekFrontLocalized.frontAssetsPath + 'js/prod-front-simple-slider-module.min.js?'+sekFrontLocalized.assetVersion,
                        //       cache : true,// use the browser cached version when available
                        //       dataType: "script"
                        // }).done(function() {
                        // }).fail( function() {
                        //       nb_.errorLog('script instantiation failed');
                        // });
                    //  }
                    //  loadcheck : 'function' === typeof( window.Swiper )
                    // }
                    scriptsLoadingStatus : {},// <= will be populated with the script loading promises
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
                              //console.log( 'ASSET IS LOADED => ' + params.path, params );
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



                    // HELPERS COPIED FROM UNDERSCORE
                    has : function(obj, path) {
                        if (!_.isArray(path)) {
                          return obj != null && hasOwnProperty.call(obj, path);
                        }
                        var length = path.length;
                        for (var i = 0; i < length; i++) {
                          var key = path[i];
                          if (obj == null || !Object.prototype.hasOwnProperty.call(obj, key)) {
                            return false;
                          }
                          obj = obj[key];
                        }
                        return !!length;
                    },
                    // https://davidwalsh.name/javascript-debounce-function
                    debounce : function(func, wait, immediate) {
                        var timeout;
                        return function() {
                            var context = this, args = arguments;
                            var later = function() {
                              timeout = null;
                              if (!immediate) func.apply(context, args);
                            };
                            var callNow = immediate && !timeout;
                            clearTimeout(timeout);
                            timeout = setTimeout(later, wait);
                            if (callNow) func.apply(context, args);
                        };
                    },
                    // https://underscorejs.org/docs/underscore.html#section-85
                    throttle : function(func, wait, options) {
                        var timeout, context, args, result;
                        var previous = 0;
                        if (!options) options = {};

                        var later = function() {
                          previous = options.leading === false ? 0 : _now();
                          timeout = null;
                          result = func.apply(context, args);
                          if (!timeout) context = args = null;
                        };

                        var throttled = function() {
                            var now = _now();
                            if (!previous && options.leading === false) previous = now;
                            var remaining = wait - (now - previous);
                            context = this;
                            args = arguments;
                            if (remaining <= 0 || remaining > wait) {
                              if (timeout) {
                                clearTimeout(timeout);
                                timeout = null;
                              }
                              previous = now;
                              result = func.apply(context, args);
                              if (!timeout) context = args = null;
                            } else if (!timeout && options.trailing !== false) {
                              timeout = setTimeout(later, remaining);
                            }
                            return result;
                        };

                        throttled.cancel = function() {
                            clearTimeout(timeout);
                            previous = 0;
                            timeout = context = args = null;
                        };

                        return throttled;
                    },
                    delay : _restArguments(function(func, wait, args) {
                        return setTimeout(function() {
                          return func.apply(null, args);
                        }, wait);
                    })
                    // Browser detection
                    // @see https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
                    // browserIs : function( browser ) {
                    //     var bool = false,
                    //         isIE = false || !!document.documentMode;
                    //     switch( browser) {
                    //         case 'safari' :
                    //             bool = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));
                    //         break;
                    //         case 'firefox' :
                    //             bool = typeof InstallTrigger !== 'undefined';
                    //         break;
                    //         case 'IE' :
                    //             bool = isIE;
                    //         break;
                    //         case 'edge' :
                    //             bool = !isIE && !!window.StyleMedia;
                    //         break;
                    //         case 'chrome' :
                    //             bool = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);
                    //         break;
                    //     }
                    //     return bool;
                    // },
              });//$.extend( nb_

              // now that nb_ has been populated, let's say it to the app
              nb_.emit('nb-app-ready');
          });// jQuery( function($){
    };
    // 'nb-jquery-loaded' is fired @'wp_footer' see inline script in ::_schedule_front_assets_printing()
    nb_.listenTo('nb-jquery-loaded', callbackFunc );

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

      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', callbackFunc );
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

    nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SCROLL LISTENER FOR DYNAMIC ASSET LOADING
 /* ------------------------------------------------------------------------- */
(function(w, d){
    // Fire now or schedule when becoming visible.
    nb_.loadAssetWhenElementVisible = function( id, handlerParams ) {
        jQuery(function($){
            if ( nb_.scrollHandlers[id].loaded )
              return;
            nb_.scrollHandlers[id].loaded = false;
            var $elements = handlerParams.elements,
                loaderFunc = handlerParams.func;

            $.each( $elements, function( k, el ) {
                if ( !nb_.scrollHandlers[id].loaded && nb_.elOrFirstVisibleParentIsInWindow($(el) ) ) {
                    loaderFunc();
                    nb_.scrollHandlers[id].loaded = true;
                }
            });
            // check if we need to unbind the scroll handle when all assets are loaded
            var allAssetsLoaded = true;
            $.each( nb_.scrollHandlers, function( id, handlerParams ) {
                if ( true !== nb_.scrollHandlers[id].loaded ) {
                    allAssetsLoaded = false;
                }
                return false !== allAssetsLoaded;//break the look on the first asset not loaded found
            });
            if ( allAssetsLoaded ) {
                //console.log('ALL ASSETS LOADED');
                nb_.cachedElements.$window.unbind('scroll', nb_.scrollHandleForLoadingAssets );
            }
        });
    };//_loadAssetWhenElementVisible

    nb_.loopOnScrollHandlers = function() {
        jQuery(function($){
            $.each( nb_.scrollHandlers, function( id, handlerParams ) {
                // has it been loaded already ?
                if ( handlerParams.loaded )
                  return true;//<=> continue see https://api.jquery.com/jquery.each/

                if ( 1 > handlerParams.elements.length )
                  return true;

                if( nb_.isFunction( handlerParams.func ) ) {
                    try{ nb_.loadAssetWhenElementVisible( id, handlerParams ); } catch(er){
                        nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers', er, handlerParams );
                    }
                } else {
                    nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers => wrong callback func param', handlerParams );
                }

            });
        });
    };

    nb_.listenTo('nb-app-ready', function() {
        jQuery(function($){
            // do nothing if dynamic asset loading is not enabled for js and css
            if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
              return;
            // nb_.scrollHandlers = [
            //    { id : 'swiper', elements : $(), func : function(){} }
            //    ...
            // ]

            // each time a new scroll handler is added, it emits the event 'nimble-new-scroll-handler-added'
            // so when caught, let's try to detect any dependant element is visible in the page
            // and if so, load.
            // Typically useful on page load if for example the slider is on top of the page and we need to load swiper.js right away before scrolling
            nb_.listenTo('nimble-new-scroll-handler-added', nb_.loopOnScrollHandlers );

            // bound on scroll,
            // unbound when all assets are loaded
            nb_.scrollHandleForLoadingAssets = nb_.throttle( nb_.loopOnScrollHandlers, 100 );

            // schedule loading on scroll
            // unbound when all assets are loaded
            nb_.cachedElements.$window.on( 'scroll', nb_.scrollHandleForLoadingAssets );
        });//jQuery
    });
}(window, document));// global sekFrontLocalized, nimbleListenTo, nb_
(function(w, d){
    nb_.listenTo( 'nb-app-ready', function() {
        jQuery(function($){
            // params = {
            //  elements : $swiperCandidate,
            //  func : function() {}
            // }
            nb_.maybeLoadAssetsWhenSelectorInScreen = function( params ) {
                params = $.extend( { id : '', elements : '', func : '' }, params );

                if ( 1 > params.id.length ) {
                    nb_.errorLog('Nimble error => maybeLoadAssetsWhenSelectorInScreen => missing id', params );
                  return;
                }
                if ( 1 > $(params.elements).length )
                  return;
                if ( !nb_.isFunction( params.func ) )
                  return;

                // populate the collection of scroll handlers looped on ::loopOnScrollHandlers()
                // + emit
                nb_.scrollHandlers = nb_.scrollHandlers || {};
                var handlerParams = { elements : params.elements, func : params.func };
                nb_.scrollHandlers[params.id] = handlerParams;
                nb_.emit('nimble-new-scroll-handler-added' );

            };
        });//jQuery(function($){})
    });//'nb-app-ready'
}(window, document));






/* ------------------------------------------------------------------------- *
 *  LOAD MAGNIFIC POPUP
 /* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
                return;

            var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $linkCandidates.length < 1 )
              return;
            var doLoad = function() {
                  //Load the style
                  if ( $('head').find( '#czr-magnific-popup' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'czr-magnific-popup',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/magnific-popup.min.css?' + sekFrontLocalized.assetVersion
                        }) );
                  }

                  if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_front_module_assets_on_scroll ) {
                        nb_.ajaxLoadScript({
                            path : 'js/libs/jquery-magnific-popup.min.js',
                            loadcheck : function() { return nb_.isFunction( $.fn.magnificPopup ); }
                        });
                  }
              };// doLoad

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-jmp-parsed' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'magnific-popup',
                elements : $linkCandidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    //When loaded with defer, we can not be sure that jQuery will be loaded before
    nb_.listenTo( 'nb-app-ready', function() {
        nb_.listenTo( 'nb-needs-magnific-popup', callbackFunc );
    });
}(window, document));






/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
              return;
            // Load js plugin if needed
            // // when the plugin is loaded => it emits 'nimble-swiper-ready' listened to by nb_.listenTo()
            var doLoad = function() {
                  //Load the style
                  if ( $('head').find( '#czr-swiper' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'czr-swiper',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/swiper.min.css?'+sekFrontLocalized.assetVersion
                        }) );
                  }
                  if ( sekFrontLocalized.load_front_module_assets_on_scroll ) {
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
                id : 'swiper',
                elements : $swiperCandidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    // When loaded with defer, we can not be sure that jQuery will be loaded before
    // on 'nb-app-ready', jQuery is loaded
    nb_.listenTo( 'nb-app-ready', function() {
        nb_.listenTo('nb-needs-swiper', callbackFunc );
    });
}(window, document));



/* ------------------------------------------------------------------------- *
 *  LOAD MENU MODULE JS
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
              return;
            var $candidates = $('[data-sek-module-type="czr_menu_module"]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-jmp-parsed' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'menu',
                elements : $candidates,
                func : function() {
                    //Load js
                    nb_.ajaxLoadScript({
                        path : 'js/prod-front-menu-module.min.js'
                    });
                }// doLoad
            });
        });// jQuery
    };/////////////// callbackFunc
    nb_.listenTo('nb-app-ready', function() {
        nb_.listenTo('nb-needs-menu-js', callbackFunc );
    });
}(window, document));


/* ------------------------------------------------------------------------- *
 *  LOAD VIDEO BACKGROUND JS
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
              return;
            var $candidates = $('[data-sek-video-bg-src]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-jmp-parsed' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'menu',
                elements : $candidates,
                func : function() {
                    //Load js
                    nb_.ajaxLoadScript({
                        path : 'js/prod-front-video-bg.min.js'
                    });
                }// doLoad
            });
        });// jQuery
    };/////////////// callbackFunc
    nb_.listenTo('nb-app-ready', function() {
        nb_.listenTo('nb-needs-videobg-js', callbackFunc );
    });
}(window, document));






/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD FONTAWESOME ON SCROLL
/* ------------------------------------------------------------------------- */
// (function(w, d){
//     var callbackFunc = function() {
//         jQuery(function($){
//             // we don't need to inject font awesome if already enqueued by a theme
//             if ( sekFrontLocalized.fontAwesomeAlreadyEnqueued )
//               return;
//             if ( !sekFrontLocalized.load_font_awesome_on_scroll )
//               return;
//             var $candidates = $('i[class*=fa-]');

//             if ( $candidates.length < 1 )
//               return;

//             // Load js plugin if needed
//             // when the plugin is loaded => it emits "nb-needs-fontawesome" listened to by nb_.listenTo()
//             var doLoad = function() {
//                   //Load the style
//                   if ( $('head').find( '#czr-font-awesome' ).length < 1 ) {
//                         var link = document.createElement('link');
//                         link.setAttribute('href', sekFrontLocalized.frontAssetsPath + 'fonts/css/fontawesome-all.min.css?'+sekFrontLocalized.assetVersion );
//                         link.setAttribute('id', 'czr-font-awesome');
//                         link.setAttribute('rel', nb_.assetPreloadSupported() ? 'preload' : 'stylesheet' );
//                         link.setAttribute('as', 'style');
//                         link.onload = function() {
//                             this.onload=null;
//                             if ( nb_.assetPreloadSupported() ) {
//                                 this.rel='stylesheet';
//                             }

//                         };
//                         document.getElementsByTagName('head')[0].appendChild(link);
//                   }
//             };// doLoad
//             // Load js plugin if needed
//             // when the plugin is loaded => it emits 'nb-jmp-parsed' listened to by nb_.listenTo()
//             nb_.maybeLoadAssetsWhenSelectorInScreen({
//                 id : 'font-awesome',
//                 elements : $candidates,
//                 func : doLoad
//             });
//         });//jQuery(function($){})
//     };/////////////// callbackFunc

//     // When loaded with defer, we can not be sure that jQuery will be loaded before
//     //  on 'nb-app-ready', jQuery is loaded
//     nb_.listenTo( 'nb-app-ready', function() {
//         nb_.listenTo( 'nb-needs-fontawesome', callbackFunc );
//     });
// }(window, document));// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
 /* ------------------------------------------------------------------------- */
(function(w, d){
    nb_.listenTo('nb-jmp-parsed', function() {
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
    });
}(window, document));




/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
// nimble-lazyload-loaded is fired in lazyload plugin, only when sekFrontLocalized.lazyload_enabled
(function(w, d){
    nb_.listenTo('nb-lazyload-parsed', function() {
        jQuery(function($){
              $('.sektion-wrapper').each( function() {
                    try { $(this).nimbleLazyLoad(); } catch( er ) {
                          nb_.errorLog( 'error with nimbleLazyLoad => ', er );
                    }
              });
        });
    });
}(window, document));



/* ------------------------------------------------------------------------- *
 *  BG PARALLAX
/* ------------------------------------------------------------------------- */
(function(w, d){
    nb_.listenTo('nb-parallax-parsed', function() {
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
