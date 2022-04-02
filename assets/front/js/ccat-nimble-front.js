// global sekFrontLocalized, nb_
if ( window.nb_ === void 0 && window.console && window.console.log ) {
    console.log('Nimble error => window.nb_ global not instantiated');
}


// add an helper to get the query variable
// used for grid module
window.nb_.getQueryVariable = function(variable) {
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == variable){return pair[1];}
       }
       return(false);
};

// adds jQuery dependant methods to window.nb_
(function(w, d){
    var callbackFunc = function() {
        jQuery( function($){
              // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp, isError, isMap, isWeakMap, isSet, isWeakSet
              // see https://underscorejs.org/docs/underscore.html#section-149
              jQuery.each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp', 'Error', 'Symbol', 'Map', 'WeakMap', 'Set', 'WeakSet'], function(index, name) {
                window.nb_['is' + name] = function(obj) {
                    // https://developer.mozilla.org/fr/docs/Web/JavaScript/Reference/Objets_globaux/Object/toString
                    var _toString = Object.prototype.toString;
                    return _toString.call(obj) === '[object ' + name + ']';
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
                          var $_el = !( element instanceof $ ) ? $(element) : element;
                          if ( !( $_el instanceof $ ) ) {
                              nb_.errorLog('invalid element in nb_.elOrFirstVisibleParentIsInWindow', $_el );
                              return;
                          }
                          if ( threshold && !nb_.isNumber( threshold ) ) {
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
                              it  = $el_candidate.offset().top,
                              ib  = it + $el_candidate.height(),
                              th = threshold || 0;

                          return ib >= wt - th && it <= wb + th;
                    },//elOrFirstVisibleParentIsInWindow

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
                    //             bool = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window.safari || (typeof safari !== 'undefined' && safari.pushNotification));
                    //         break;
                    //         case 'firefox' :
                    //             bool = typeof InstallTrigger !== 'undefined';
                    //         break;
                    //         case 'IE' :
                    //             // https://stackoverflow.com/questions/19999388/check-if-user-is-using-ie
                    //             bool = isIE && /MSIE|Trident/.test(window.navigator.userAgent);
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
                      $this.css('font-size', Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)) + 'px');
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
            window.nb_allImagesLazyLoadedForScrollToAnchor = false;
            // this = $nimbleTargetCandidate
            var _doAnimateToTarget = function() {
                  var $target = $(this);
                  // Check is scrollIntoView is fully supported, in particular the options for smooth behavior
                  // https://stackoverflow.com/questions/46919627/is-it-possible-to-test-for-scrollintoview-browser-compatibility
                  // if not, fallback on jQuery animate()
                  if( 'scrollBehavior' in document.documentElement.style ) {
                        $target[0].scrollIntoView( { behavior: "smooth" } );
                  } else {
                        $root.animate({ scrollTop : $target.offset().top - 150 }, 400 );
                  }
            };
            var runTime = 0;
            // this = $nimbleTargetCandidate
            var _checkThatAllImgAreLoaded = function() {
                  var $el = $(this);
                  // If all images (except the ones in error ) are loaded animate
                  // if not, loop until images are loaded
                  // do not loop more than 2000 ms
                  if ( $('img[data-sek-src]').not('.sek-lazy-load-error').length < 1 ) {
                        window.nb_allImagesLazyLoadedForScrollToAnchor = true;
                        _doAnimateToTarget.call($el);
                  } else if ( runTime < 20 ) {
                        runTime++;
                        // Loop on myself, maximum 20 times until all images are lazyloaded
                        nb_.delay( function() {
                              _checkThatAllImgAreLoaded.call($el);
                        }, 100 );
                        // Start animating after 200ms so that user doesn't wait too long
                        // even if another animation may take over after all remaining images have been loaded
                        nb_.delay( function() {
                              _doAnimateToTarget.call($el);
                        }, 200 );
                  } else {
                        _doAnimateToTarget.call($el);
                  }
            };
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

                  // Sept 2020 => LAYOUT SHIFT PROBLEMS
                  // => if lazy load is enabled and there are still images to load, make sure all images are loaded before scrolling to an anchor
                  // => lazyload all images + add a tiny delay before scrolling
                  // otherwise, the scroll might no land to the right place, due to image dimensions not OK ( occurs on chrome and edge at least )
                  // see https://github.com/presscustomizr/nimble-builder/issues/744
                  // additional issue : https://github.com/presscustomizr/nimble-builder/issues/748
                  var _scrollDelay = 0;
                  if ( sekFrontLocalized.lazyload_enabled && false === window.nb_allImagesLazyLoadedForScrollToAnchor && $('img[data-sek-src]').not('.sek-lazy-load-error').length > 0 ) {
                        $('body').one( 'smartload', 'img', function() { _checkThatAllImgAreLoaded.call( $nimbleTargetCandidate );} );
                        $('img[data-sek-src]').trigger('sek_load_img');
                  } else {
                        _doAnimateToTarget.call( $nimbleTargetCandidate );
                  }
            };

            // animate menu item to Nimble anchors
            nb_.cachedElements.$body.find('.menu-item' ).on( 'click', 'a', maybeScrollToAnchor );

            // animate an anchor link inside Nimble sections
            // fixes https://github.com/presscustomizr/nimble-builder/issues/443
            $('[data-sek-level="location"]' ).on( 'click', 'a', maybeScrollToAnchor );
        });
    };/////////////// callbackFunc

    nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));/* ===================================================
 * jquerynimbleLazyLoad.js v1.0.0
 * ===================================================
 *
 * Replace all img src placeholder in the $element by the real src on scroll window event
 * Handles background image for sections
 * Hacked to lazyload iframes
 *
 * Note : the data-src (data-srcset) attr has to be pre-processed before the actual page load
 * Example of regex to pre-process img server side with php :
 * preg_replace_callback('#<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>#', 'regex_callback' , $_html)
 *
 * Note May 2020 : lazyload can be skipped by adding data-skip-lazyload="true" to the img src when generating the HTML markup
 *
 * (c) 2020 Nicolas Guillaume, Nice, France
 *
 * Example of gif 1px x 1px placeholder :
 * 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
 *
 * inspired by the work of Luís Almeida
 * http://luis-almeida.github.com/unveil
 *
 * Requires requestAnimationFrame polyfill:
 * http://paulirish.com/2011/requestanimationframe-for-smart-animating/
 *
 * Feb 2019 : added support for iframe lazyloading for https://github.com/presscustomizr/nimble-builder/issues/361
 * =================================================== */
// global sekFrontLocalized, nimbleListenTo
(function(w, d){
      var callbackFunc = function() {
           (function ( $, window ) {
              //defaults
              var pluginName = 'nimbleLazyLoad',
                  defaults = {
                        load_all_images_on_first_scroll : false,
                        //attribute : [ 'data-sek-src' ],
                        threshold : 100,
                        fadeIn_options : { duration : 400 },
                        delaySmartLoadEvent : 0,
                        candidateSelectors : '[data-sek-src], [data-sek-iframe-src]',
                        force:false//<= can be useful when nb_.isCustomizing()
                  },
                  //- to avoid multi processing in general
                  _skipLoadClass = 'sek-lazy-loaded';


              function Plugin( element, options ) {
                    this.element = element;
                    this.options = $.extend( {}, defaults, options);
                    var allowLazyLoad = sekFrontLocalized.lazyload_enabled;
                    if ( this.options.force ) {
                        allowLazyLoad = true;
                    }

                    if ( !allowLazyLoad )
                      return;
                    // Do we already have an instance for this element ?
                    if ( $(this.element).data('nimbleLazyLoadDone') ) {
                        $(this.element).trigger('nb-trigger-lazyload' );
                        return;
                    }

                    this._defaults = defaults;
                    this._name = pluginName;
                    var self = this;
                    // 'nb-trigger-lazyload' can be fired from the slider module
                    $(this.element).on('nb-trigger-lazyload', function() {
                          self._maybe_trigger_load( 'nb-trigger-lazyload' );
                    });
                    this.init();
              }

              Plugin.prototype._getCandidateEls = function() {
                    return $( this.options.candidateSelectors, this.element );
              };

              //can access this.element and this.option
              Plugin.prototype.init = function () {
                    var self        = this;
                    // img to be lazy loaded looks like data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7
                    // [src*="data:image"] =>
                    // [data-sek-src*="http"] => background images, images in image modules, wp editor module, post grids, slider module, etc..
                    // [data-sek-iframe-src] => ?

                    // Bind with delegation
                    // April 2020 : implemented for https://github.com/presscustomizr/nimble-builder/issues/669
                    $('body').on( 'sek_load_img sek_load_iframe', self.options.candidateSelectors , function( evt ) {
                            // has this image been lazy loaded ?
                            if ( true === $(this).data( 'sek-lazy-loaded' ) )
                              return;
                            if ( 'sek_load_img' === evt.type ) {
                                self._load_img(this);
                            } else if ( 'sek_load_iframe' === evt.type ) {
                                self._load_iframe(this);
                            }

                    });

                    //the scroll event gets throttled with the requestAnimationFrame
                    nb_.cachedElements.$window.on('scroll', function( _evt ) {
                          self._better_scroll_event_handler( _evt );
                    });
                    //debounced resize event
                    nb_.cachedElements.$window.on('resize', nb_.debounce( function( _evt ) {
                          self._maybe_trigger_load( _evt );
                    }, 100 ) );

                    // on DOM ready
                    this._maybe_trigger_load('dom-ready');
                    // Wait and trigger the dom-ready again, so we don't miss any image initially below the viewport
                    // ( can happen if the height of a page element like a slider is modified at dom ready )
                    setTimeout( function() {
                        self._maybe_trigger_load('dom-ready');
                    }, 1000 );

                    // flag so we can check whether his element has been lazyloaded
                    $(this.element).data('nimbleLazyLoadDone', true );

              };


              /*
              * @param : array of $img
              * @param : current event
              * @return : void
              * scroll event performance enhancer => avoid browser stack if too much scrolls
              */
              Plugin.prototype._better_scroll_event_handler = function( _evt ) {
                    var self = this;
                    if ( ! this.doingAnimation ) {
                          this.doingAnimation = true;
                          window.requestAnimationFrame(function() {
                                self._maybe_trigger_load(_evt );
                                self.doingAnimation = false;
                          });
                    }
              };


              /*
              * @param : array of $img
              * @param : current event
              * @return : void
              */
              Plugin.prototype._maybe_trigger_load = function(_evt ) {
                    var self = this,
                        $_imgs = self._getCandidateEls(),
                        // get the visible images list
                        // don't apply a threshold on page load so that Google audit is happy
                        // for https://github.com/presscustomizr/nimble-builder/issues/619
                        threshold = ( _evt && 'scroll' === _evt.type ) ? this.options.threshold : 0;

                        _visible_list = $_imgs.filter( function( ind, _el ) {
                            //force all images to visible if first scroll option enabled
                            if ( _evt && 'scroll' == _evt.type && self.options.load_all_images_on_first_scroll )
                              return true;
                            return nb_.elOrFirstVisibleParentIsInWindow( _el, threshold );
                        });

                    //trigger load_img event for visible images
                    _visible_list.map( function( ind, _el ) {
                        // trigger a lazy load if image not processed yet
                        if ( true !== $(_el).data( 'sek-lazy-loaded' ) ) {
                            if ( 'IFRAME' === $(_el).prop("tagName") ) {
                                  $(_el).trigger( 'sek_load_iframe' );
                            } else {
                                  $(_el).trigger( 'sek_load_img' );
                            }
                        }
                    });
              };


              /*
              * @param single $img object
              * @return void
              * replace src place holder by data-src attr val which should include the real src
              */
              Plugin.prototype._load_img = function( _el_ ) {
                    var $_el    = $(_el_);

                    // Stop here if
                    // - the image has no data-sek-src attribute
                    // - the image has already been lazyloaded
                    // - the image is being lazyloaded
                    if ( !$_el.attr( 'data-sek-src' ) || $_el.hasClass( _skipLoadClass ) || $_el.hasClass( 'lazy-loading' ) )
                        return;

                    var _src     = $_el.attr( 'data-sek-src' ),
                        _src_set = $_el.attr( 'data-sek-srcset' ),
                        _sizes   = $_el.attr( 'data-sek-sizes' ),
                        self = this,
                        $jQueryImgToLoad = $("<img />", { src : _src } );

                    $_el.addClass('lazy-loading');
                    $_el.off('sek_load_img');

                    $jQueryImgToLoad
                          // .hide()
                          .on( 'load', function () {
                                //https://api.jquery.com/removeAttr/
                                //An attribute to remove; as of version 1.7, it can be a space-separated list of attributes.
                                //minimum supported wp version (3.4+) embeds jQuery 1.7.2
                                $_el.removeAttr( [ 'data-sek-src', 'data-sek-srcset', 'data-sek-sizes' ].join(' ') );
                                // Case of a lazyloaded background
                                if( $_el.data("sek-lazy-bg") ){
                                      $_el.css('backgroundImage', 'url('+_src+')');
                                } else {
                                // Case of a regular image
                                      $_el.attr("src", _src );
                                      if ( _src_set ) {
                                            $_el.attr("srcset", _src_set );
                                      }
                                      if ( _sizes ) {
                                            $_el.attr("sizes", _sizes );
                                      }
                                }
                                //prevent executing this twice on an already smartloaded img
                                if ( ! $_el.hasClass(_skipLoadClass) ) {
                                      $_el.addClass(_skipLoadClass);
                                }
                                //Following would be executed twice if needed, as some browsers at the
                                //first execution of the load callback might still have not actually loaded the img

                                $_el.trigger('smartload');
                                //flag to avoid double triggering
                                $_el.data('sek-lazy-loaded', true );
                                self._clean_css_loader( $_el );

                          })//<= create a load() fn
                          .on('error', function( evt, error ) {
                                $_el.addClass('sek-lazy-load-error');
                          });// on error
                    //http://stackoverflow.com/questions/1948672/how-to-tell-if-an-image-is-loaded-or-cached-in-jquery
                    if ( $jQueryImgToLoad[0].complete ) {
                          $jQueryImgToLoad.trigger( 'load' );
                    }
                    $_el.removeClass('lazy-loading');
              };

              // Remove CSS loaded markup close to the element if any
              Plugin.prototype._clean_css_loader = function( $_el ) {
                    // maybe remove the CSS loader
                    $.each( [ $_el.find('.sek-css-loader'),  $_el.parent().find('.sek-css-loader') ], function( k, $_el ) {
                        if ( $_el.length > 0 )
                          $_el.remove();
                    });
              };


              /*
              * @param single iframe el object
              * @return void
              */
              Plugin.prototype._load_iframe = function( _el_ ) {
                    var $_el    = $(_el_),
                        self = this;

                    //$_el.addClass('lazy-loading');
                    $_el.off('sek_load_iframe');

                    $_el.attr( 'src', function() {
                          var src = $(this).attr('data-sek-iframe-src');
                          $(this).removeAttr('data-sek-iframe-src');
                          $_el.data('sek-lazy-loaded', true );
                          $_el.trigger('smartload');
                          if ( ! $_el.hasClass(_skipLoadClass) ) {
                                $_el.addClass(_skipLoadClass);
                          }
                          return src;
                    });
                    //$_el.removeClass('lazy-loading');
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

      };////////////// callbackFunc
      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', function(){
          callbackFunc();
          // Sept 2020 => always emit lazyload parsed event when customizing
          if ( sekFrontLocalized.lazyload_enabled || nb_.isCustomizing() ) { nb_.emit('nb-lazyload-parsed'); }
      });
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

            if ( handlerParams.scrollHandler && nb_.scrollHandlers[id].loaded ) {
                nb_.cachedElements.$window.off('scroll', handlerParams.scrollHandler );
            }
        });
    };//_loadAssetWhenElementVisible

    nb_.loopOnScrollHandlers = function() {
        var _scrollHandler;
        jQuery(function($){
            $.each( nb_.scrollHandlers, function( id, handlerParams ) {
                // has it been loaded already ?
                if ( handlerParams.loaded )
                  return true;//<=> continue see https://api.jquery.com/jquery.each/

                // do nothing if dynamic asset loading is not enabled for js and css AND the assets in not in "force" mode
                var load_authorized = sekFrontLocalized.load_front_assets_on_dynamically;
                if ( true === handlerParams.force_loading ) {
                    load_authorized = true;
                }
                if ( !load_authorized )
                  return;

                if ( 1 > handlerParams.elements.length )
                  return true;

                // try on load
                try{ nb_.loadAssetWhenElementVisible( id, handlerParams ); } catch(er){
                    nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers', er, handlerParams );
                }

                // schedule on scroll
                // the scroll event is unbound once the scrollhandler is executed
                if( nb_.isFunction( handlerParams.func ) && nb_.isUndefined( handlerParams.scrollHandler ) ) {
                    handlerParams.scrollHandler = nb_.throttle( function() {
                        try{ nb_.loadAssetWhenElementVisible( id, handlerParams ); } catch(er){
                            nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers', er, handlerParams );
                        }
                    }, 100 );

                    nb_.cachedElements.$window.on( 'scroll', handlerParams.scrollHandler );
                } else if ( !nb_.isFunction( handlerParams.func ) ) {
                    nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers => wrong callback func param', handlerParams );
                }

            });
        });
    };

    nb_.listenTo('nb-app-ready', function() {
        jQuery(function($){
            // nb_.scrollHandlers = [
            //    { id : 'swiper', elements : $(), func : function(){} }
            //    ...
            // ]

            // each time a new scroll handler is added, it emits the event 'nimble-new-scroll-handler-added'
            // so when caught, let's try to detect any dependant element is visible in the page
            // and if so, load.
            // Typically useful on page load if for example the slider is on top of the page and we need to load swiper-bundle.js right away before scrolling
            nb_.listenTo('nimble-new-scroll-handler-added', nb_.loopOnScrollHandlers );

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
                var handlerParams = { elements : params.elements, func : params.func, force_loading : params.force_loading };
                nb_.scrollHandlers[params.id] = handlerParams;
                nb_.emit('nimble-new-scroll-handler-added', { fire_once : false } );
            };
            nb_.emit('nimble-ready-to-load-assets-on-scroll');
        });//jQuery(function($){})
    });//'nb-app-ready'
}(window, document));





/* ------------------------------------------------------------------------- *
 *  LOAD SWIPEBOX
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
                return;

            var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
            $linkCandidates = $linkCandidates.add($('[data-sek-level="module"]').find('.sek-gal-link-to-img-lightbox'));
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $linkCandidates.length < 1 )
              return;
            var doLoad = function() {
                  //Load the style
                  if ( $('head').find( '#nb-swipebox' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'nb-swipebox',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/swipebox.min.css?' + sekFrontLocalized.assetVersion
                        }) );
                  }

                  if ( !nb_.isFunction( $.fn.swipebox ) && sekFrontLocalized.load_front_assets_on_dynamically ) {
                        nb_.ajaxLoadScript({
                            path : 'js/libs/jquery-swipebox.min.js',
                            loadcheck : function() { return nb_.isFunction( $.fn.swipebox ); }
                        });
                  }
              };// doLoad

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-swipebox-parsed' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'swipebox',
                elements : $linkCandidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    //When loaded with defer, we can not be sure that jQuery will be loaded before
    nb_.listenTo( 'nb-app-ready', function() {
        nb_.listenTo( 'nb-needs-swipebox', callbackFunc );
    });
}(window, document));



/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
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
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/swiper-bundle.min.css?'+sekFrontLocalized.assetVersion
                        }) );
                  }
                  nb_.ajaxLoadScript({
                      path : 'js/libs/swiper-bundle.min.js?'+sekFrontLocalized.assetVersion,
                      loadcheck : function() { return nb_.isFunction( window.Swiper ); },
                      // complete : function() {
                      //     nb_.ajaxLoadScript({
                      //         path : 'js/prod-front-simple-slider-module.min.js',
                      //     });
                      // }
                  });
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
 *  LOAD VIDEO BACKGROUND JS
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
              return;
            var $candidates = $('[data-sek-video-bg-src]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-..-parsed' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'nb-video-bg',
                elements : $candidates,
                func : function() {
                    //Load js
                    nb_.ajaxLoadScript({
                        path : 'js/libs/nimble-video-bg.min.js?'+sekFrontLocalized.assetVersion
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
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            // we don't need to inject font awesome if already enqueued by a theme
            if ( sekFrontLocalized.fontAwesomeAlreadyEnqueued )
              return;
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
              return;
            var $candidates = $('i[class*=fa-]');

            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits "nb-needs-fa" listened to by nb_.listenTo()
            var doLoad = function() {
                  //Load the style
                  if ( $('head').find( '#nb-font-awesome' ).length < 1 ) {
                        var link = document.createElement('link');
                        link.setAttribute('href', sekFrontLocalized.frontAssetsPath + 'fonts/css/fontawesome-all.min.css?'+sekFrontLocalized.assetVersion );
                        link.setAttribute('id', 'nb-font-awesome');
                        link.setAttribute('data-sek-injected-dynamically', 'yes');
                        link.setAttribute('rel', nb_.hasPreloadSupport() ? 'preload' : 'stylesheet' );
                        link.setAttribute('as', 'style');
                        link.onload = function() {
                            this.onload=null;
                            if ( nb_.hasPreloadSupport() ) {
                                this.rel='stylesheet';
                            }
                        };
                        document.getElementsByTagName('head')[0].appendChild(link);
                  }
            };// doLoad
            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-...-parsed' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen({
                id : 'font-awesome',
                elements : $candidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    // When loaded with defer, we can not be sure that jQuery will be loaded before
    //  on 'nb-app-ready', jQuery is loaded
    nb_.listenTo( 'nb-app-ready', function() {
        nb_.listenTo( 'nb-needs-fa', callbackFunc );
    });
}(window, document));// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX SWIPEBOX ( April 2022 for #886)
 /* ------------------------------------------------------------------------- */
 (function(w, d){
      nb_.listenTo('nb-swipebox-parsed', function() {
            jQuery(function($){
                  if ( nb_.isCustomizing() )
                        return;
      
                  var $linkCandidates = [
                        $('[data-sek-level="module"]').find('.sek-link-to-img-lightbox'),// image module
                        $('[data-sek-level="module"]').find('.sek-gal-link-to-img-lightbox')// gallery module
                  ];

                  //https://github.com/brutaldesign/swipebox
                  var _params = {
                        loopAtEnd: true
                  };
                  //var $linkCand;
                  $.each( $linkCandidates, function(_k, $linkCand) {
                        // Abort if no link candidate
                        if ( $linkCand.length < 1 ) {
                              return;
                        }
                        // Abort if candidate already setup
                        if ( $linkCand.data('nimble-swiperbox-done') )
                              return;
                        try { $linkCand.swipebox( _params ); } catch( er ) {
                              nb_.errorLog( 'error in callback of nb-swipebox-parsed => ', er );
                        }
                        $linkCand.data('nimble-swiperbox-done', true );
                  });

                  // July 2021, prevent gallery images to be clicked when no link is specified
                  $('.sek-gallery-lightbox').on('click', '.sek-no-img-link', function(evt) {
                        evt.preventDefault();
                  });

            });//jQuery(function($){})
      });
  }(window, document));







/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
// nimble-lazyload-parsed is fired in lazyload plugin, only when sekFrontLocalized.lazyload_enabled OR when nb_.isCustomizing()
(function(w, d){
    nb_.listenTo('nb-lazyload-parsed', function() {
        jQuery(function($){
              var _do = function(evt) {
                    $(this).each( function() {
                          var _maybeDoLazyLoad = function() {
                                // if the element already has an instance of nimbleLazyLoad, simply trigger an event
                                if ( !$(this).data('nimbleLazyLoadDone') ) {
                                    $(this).nimbleLazyLoad({force : nb_.isCustomizing()});
                                } else {
                                    $(this).trigger('nb-trigger-lazyload');
                                }
                          };
                          try { _maybeDoLazyLoad.call($(this)); } catch( er ) {
                                nb_.errorLog( 'error with nimbleLazyLoad => ', er );
                          }
                    });
              };
              // on page load
              _do.call( $('.sektion-wrapper') );
              // when customizing
              nb_.cachedElements.$body.on( 'sek-section-added sek-level-refreshed sek-location-refreshed sek-columns-refreshed sek-modules-refreshed', '[data-sek-level="location"]', function(evt) {
                    _do.call( $(this), evt );
                    _.delay( function() {
                            nb_.cachedElements.$window.trigger('resize');
                    }, 200 );
              });


              // TO EXPLORE : implement a mutation observer like in Hueman theme for images dynamically inserted in the DOM via ajax ?
              // Is it really needed now that lazyload uses event delegation to trigger image loading ?
              // ( see https://github.com/presscustomizr/nimble-builder/issues/669 )
              // Observer Mutations of the DOM for a given element selector
              // <=> of previous $(document).bind( 'DOMNodeInserted', fn );
              // implemented to fix https://github.com/presscustomizr/hueman/issues/880
              // see https://stackoverflow.com/questions/10415400/jquery-detecting-div-of-certain-class-has-been-added-to-dom#10415599
              //   observeAddedNodesOnDom : function(containerSelector, elementSelector, callback) {
              //       var onMutationsObserved = function(mutations) {
              //               mutations.forEach(function(mutation) {
              //                   if (mutation.addedNodes.length) {
              //                       var elements = $(mutation.addedNodes).find(elementSelector);
              //                       for (var i = 0, len = elements.length; i < len; i++) {
              //                           callback(elements[i]);
              //                       }
              //                   }
              //               });
              //           },
              //           target = $(containerSelector)[0],
              //           config = { childList: true, subtree: true },
              //           MutationObserver = window.MutationObserver || window.WebKitMutationObserver,
              //           observer = new MutationObserver(onMutationsObserved);

              //       observer.observe(target, config);
              // }
              // Observer Mutations off the DOM to detect images
              // <=> of previous $(document).bind( 'DOMNodeInserted', fn );
              // implemented to fix https://github.com/presscustomizr/hueman/issues/880
              // this.observeAddedNodesOnDom('body', 'img', _.debounce( function(element) {
              //       _doLazyLoad();
              // }, 50 ));

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


/* ------------------------------------------------------------------------- *
 *  GRID MODULE
/* ------------------------------------------------------------------------- */
// June 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/716
nb_.listenTo('nb-docready', function() {
      if ( window.nb_ && window.nb_.getQueryVariable ) {
            var anchorId = window.nb_.getQueryVariable('nb_grid_module_go_to'),
                  el = document.getElementById(anchorId);
            // Then clean the url
            var _cleanUrl = function() {
                  var currPathName = window.location.pathname; //get current address
                  //1- get the part before '?go_to'
                  var beforeQueryString = currPathName.split("?go_to")[0];
                  window.history.replaceState({}, document.title,  beforeQueryString );
            };
            if( anchorId && el ) {
                  setTimeout( function() { el.scrollIntoView();}, 200 );
                  try{ _cleanUrl(); } catch(er) {
                        if( window.console && window.console.log ) {
                              console.log( 'NB => error when cleaning url "go_to" param');
                        }
                  }
            }
      }
});

// September 2021 => Solves the problem of CSS loaders not cleaned
// see https://github.com/presscustomizr/nimble-builder/issues/874
nb_.listenTo('nb-app-ready', function() {
      jQuery(function($){
            var $cssLoaders = $('.sek-css-loader');
            if ( $cssLoaders.length < 1 )
                  return;

            var $el, 
                  removeCssLoaderAfterADelay = nb_.throttle( function() {
                        $cssLoaders = $('.sek-css-loader');
                        $.each($cssLoaders, function(){
                              $el = $(this);
                              if ( nb_.elOrFirstVisibleParentIsInWindow($el) ) {
                                    nb_.delay( function() {
                                          if ( $el.length > 0 ) {
                                                $el.remove();
                                          }
                                          
                                    }, 1000);
                              }
                        });
                        
                        if ( $cssLoaders.length < 1 ) {
                              // When no more loaders to remove, remove scroll listener
                              nb_.cachedElements.$window.off('scroll', removeCssLoaderAfterADelay );
                        }
                  }, 200 );
            nb_.cachedElements.$window.on('scroll', removeCssLoaderAfterADelay );
      });
});