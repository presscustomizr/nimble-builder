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
 *  ACCORDION MODULE
/* ------------------------------------------------------------------------- */
(function(w, d){
      var callbackFunc = function() {
          jQuery( function($){
              $( 'body' ).on( 'click sek-expand-accord-item', '.sek-accord-item > .sek-accord-title', function( evt ) {
                  //evt.preventDefault();
                  //evt.stopPropagation();
                  var $item = $(this).closest( '.sek-accord-item'),
                      $accordion = $(this).closest( '.sek-accord-wrapper');

                  // Note : cast the boolean to a string by adding +''
                  if ( "true" == $accordion.data('sek-one-expanded')+'' ) {
                      $accordion.find('.sek-accord-item').not( $item ).each( function() {
                            var $current_item = $(this);
                            $current_item.find('.sek-accord-content').stop( true, true ).slideUp( {
                                  duration : 200,
                                  start : function() {
                                        // If already expanded, make sure inline style display:block is set
                                        // otherwise, the CSS style display:none will apply first, making the transition brutal.
                                        if ( "true" == $current_item.attr('data-sek-expanded')+'' ) {
                                              $current_item.find('.sek-accord-content').css('display', 'block');
                                        }
                                        $current_item.attr('data-sek-expanded', "false" );
                                  }
                            });
                      });
                  }
                  if ( 'sek-expand-accord-item' === evt.type && "true" == $item.attr('data-sek-expanded')+'' ) {
                      return;
                  } else {
                      $item.find('.sek-accord-content').stop( true, true ).slideToggle({
                            duration : 200,
                            start : function() {
                                  // If already expanded, make sure inline style display:block is set
                                  // otherwise, the CSS style display:none will apply first, making the transition brutal.
                                  if ( "true" == $item.attr('data-sek-expanded')+'' ) {
                                        $item.find('.sek-accord-content').css('display', 'block');
                                  }
                                  $item.attr('data-sek-expanded', "false" == $item.attr('data-sek-expanded')+'' ? "true" : "false" );
                                  $item.trigger( "true" == $item.attr('data-sek-expanded') ? 'sek-accordion-expanded' : 'sek-accordion-collapsed' );
                            }
                      });
                  }

              });// on 'click'

              // When customizing, expand the currently edited item
              // @see CZRItemConstructor in api.czrModuleMap.czr_img_slider_collection_child
              if ( window.wp && ! nb_.isUndefined( wp.customize ) ) {
                    wp.customize.preview.bind('sek-item-focus', function( params ) {

                          var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]', '.sek-accord-wrapper').first();
                          if ( 1 > $itemEl.length )
                            return;

                          $itemEl.find('.sek-accord-title').trigger('sek-expand-accord-item');
                    });
              }
          });//jQuery()

      };/////////////// callbackFunc
      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));


// global sekFrontLocalized, nimbleListenTo
/* ===================================================
 * jquery.fn.parallaxBg v1.0.0
 * Created in October 2018.
 * Inspired from https://github.com/presscustomizr/front-jquery-plugins/blob/master/jqueryParallax.js
 * ===================================================
*/
(function(w, d){
      var callbackFunc = function() {
          (function ( $, window ) {
              //defaults
              var pluginName = 'parallaxBg',
                  defaults = {
                        parallaxForce : 40,
                        oncustom : [],//list of event here
                        matchMedia : 'only screen and (max-width: 800px)'
                  };

              function Plugin( element, options ) {
                    this.element         = $(element);
                    //this.element_wrapper = this.element.closest( '.parallax-wrapper' );
                    this.options         = $.extend( {}, defaults, options, this.parseElementDataOptions() ) ;
                    this._defaults       = defaults;
                    this._name           = pluginName;
                    this.init();
              }

              Plugin.prototype.parseElementDataOptions = function () {
                    return this.element.data();
              };

              //can access this.element and this.option
              //@return void
              Plugin.prototype.init = function () {
                    var self = this;
                    //cache some element
                    this.$_window     = nb_.cachedElements.$window;
                    this.doingAnimation = false;
                    this.isVisible = false;
                    this.isBefore = false;//the element is before the scroll point
                    this.isAfter = true;// the element is after the scroll point

                    // normalize the parallax ratio
                    // must be a number 0 > ratio > 100
                    if ( 'number' !== typeof( self.options.parallaxForce ) || self.options.parallaxForce < 0 ) {
                          if ( sekFrontLocalized.isDevMode ) {
                                console.log('parallaxBg => the provided parallaxForce is invalid => ' + self.options.parallaxForce );
                          }
                          self.options.parallaxForce = this._defaults.parallaxForce;
                    }
                    if ( self.options.parallaxForce > 100 ) {
                          self.options.parallaxForce = 100;
                    }

                    //the scroll event gets throttled with the requestAnimationFrame
                    this.$_window.scroll( function(_evt) { self.maybeParallaxMe(_evt); } );
                    //debounced resize event
                    this.$_window.resize( nb_.debounce( function(_evt) {
                          self.maybeParallaxMe(_evt);
                    }, 100 ) );

                    //on load
                    this.checkIfIsVisibleAndCacheProperties();
                    this.setTopPositionAndBackgroundSize();
              };

              //@see https://www.paulirish.com/2012/why-moving-elements-with-translate-is-better-than-posabs-topleft/
              Plugin.prototype.setTopPositionAndBackgroundSize = function() {
                    var self = this;

                    // options.matchMedia is set to 'only screen and (max-width: 768px)' by default
                    // if a match is found, then reset the top position
                    if ( nb_.isFunction( window.matchMedia ) && matchMedia( self.options.matchMedia ).matches ) {
                          this.element.css({'background-position-y' : '', 'background-attachment' : '' });
                          return;
                    }

                    var $element       = this.element,
                        elemHeight = $element.outerHeight(),
                        winHeight = this.$_window.height(),
                        offsetTop = $element.offset().top,
                        scrollTop = this.$_window.scrollTop(),
                        percentOfPage = 100;

                    // the percentOfPage can vary from -1 to 1
                    if ( this.isVisible ) {
                          //percentOfPage = currentDistanceToMiddleScreen / maxDistanceToMiddleScreen;
                          percentOfPage = ( offsetTop - scrollTop ) / winHeight;
                    } else if ( this.isBefore ) {
                          percentOfPage = 1;
                    } else if ( this.isAfter ) {
                          percentOfPage = - 1;
                    }

                    var maxBGYMove = this.options.parallaxForce > 0 ? winHeight * ( 100 - this.options.parallaxForce ) / 100 : winHeight,
                        bgPositionY = Math.round( percentOfPage *  maxBGYMove );

                    this.element.css({
                          'background-position-y' : [
                                'calc(50% ',
                                bgPositionY > 0 ? '+ ' : '- ',
                                Math.abs( bgPositionY ) + 'px)'
                          ].join('')
                    });
              };

              // When does the image enter the viewport ?
              Plugin.prototype.checkIfIsVisibleAndCacheProperties = function( _evt ) {
                  var $element = this.element;
                  // bail if the level is display:none;
                  // because $.offset() won't work
                  // see because of https://github.com/presscustomizr/nimble-builder/issues/363
                  if ( ! $element.is(':visible') )
                      return false;

                  var scrollTop = this.$_window.scrollTop(),
                      wb = scrollTop + this.$_window.height(),
                      offsetTop  = $element.offset().top,
                      ib  = offsetTop + $element.outerHeight();

                  // Cache now
                  this.isVisible = ib >= scrollTop && offsetTop <= wb;
                  this.isBefore = offsetTop > wb ;//the element is before the scroll point
                  this.isAfter = ib < scrollTop;// the element is after the scroll point
                  return this.isVisible;
              };

              // a throttle is implemented with window.requestAnimationFrame
              Plugin.prototype.maybeParallaxMe = function(evt) {
                    var self = this;
                    if ( ! this.checkIfIsVisibleAndCacheProperties() )
                      return;

                    if ( ! this.doingAnimation ) {
                          this.doingAnimation = true;
                          window.requestAnimationFrame(function() {
                                self.setTopPositionAndBackgroundSize();
                                self.doingAnimation = false;
                          });
                    }
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
      };/////////////// callbackFunc

      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', function(){
          callbackFunc();
          nb_.emit('nb-parallax-parsed');
      });
}(window, document));
/* ===================================================
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
                        candidateSelectors : '[data-sek-src], [data-sek-iframe-src]'
                  },
                  //- to avoid multi processing in general
                  _skipLoadClass = 'sek-lazy-loaded';


              function Plugin( element, options ) {
                    if ( !sekFrontLocalized.lazyload_enabled )
                      return;
                    // Do we already have an instance for this element ?
                    if ( $(this.element).data('nimbleLazyLoadDone') ) {
                        $(this.element).trigger('nb-trigger-lazyload' );
                        return;
                    }

                    this.element = element;
                    this.options = $.extend( {}, defaults, options);


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
                    nb_.cachedElements.$window.scroll( function( _evt ) {
                          self._better_scroll_event_handler( _evt );
                    });
                    //debounced resize event
                    nb_.cachedElements.$window.resize( nb_.debounce( function( _evt ) {
                          self._maybe_trigger_load( _evt );
                    }, 100 ) );

                    //on DOM ready
                    this._maybe_trigger_load('dom-ready');

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
                    $_el.unbind('sek_load_img');

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

                          });//<= create a load() fn
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
                    $_el.unbind('sek_load_iframe');

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
          if ( sekFrontLocalized.lazyload_enabled ) { nb_.emit('nb-lazyload-parsed'); }
      });
}(window, document));// global sekFrontLocalized, nimbleListenTo
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
                                var _debounced_addOpenClass = nb_.debounce( function() {
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
                                var _debounced_removeOpenClass = nb_.debounce( function() {
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

// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SWIPER CAROUSEL implemented for the simple slider module czr_img_slider_module
 *  doc : https://swiperjs.com/api/
 *  dependency : $.fn.nimbleCenterImages()
/* ------------------------------------------------------------------------- */

(function(w, d){
      var callbackFunc = function() {
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
                    var $swiperWrapper = $(this), swiperClass = 'sek-swiper' + $swiperWrapper.data('sek-swiper-id');
                    var swiperParams = {
                        // slidesPerView: 3,
                        // spaceBetween: 30,
                        loop : true === $swiperWrapper.data('sek-loop') && true === $swiperWrapper.data('sek-is-multislide'),//Set to true to enable continuous loop mode
                        grabCursor : true === $swiperWrapper.data('sek-is-multislide'),
                        on : {
                              init : function() {
                                    // remove the .sek-swiper-loading class from the wrapper => remove the display:none rule
                                    $swiperWrapper.removeClass('sek-swiper-loading');

                                    // remove the css loader
                                    $swiperWrapper.parent().find('.sek-css-loader').remove();

                                    // lazy load the first slider image with Nimble if not done already
                                    // the other images will be lazy loaded by swiper if the option is activated
                                    // if ( sekFrontLocalized.lazyload_enabled && $.fn.nimbleLazyLoad ) {

                                    // }
                                    $swiperWrapper.trigger('nb-trigger-lazyload');

                                    // center images with Nimble wizard when needed
                                    if ( 'nimble-wizard' === $swiperWrapper.data('sek-image-layout') ) {
                                          $swiperWrapper.find('.sek-carousel-img').each( function() {
                                                var $_imgsToSimpleLoad = $(this).nimbleCenterImages({
                                                      enableCentering : 1,
                                                      zeroTopAdjust: 0,
                                                      setOpacityWhenCentered : false,//will set the opacity to 1
                                                      oncustom : [ 'simple_load', 'smartload', 'sek-nimble-refreshed', 'recenter']
                                                })
                                                //images with src which starts with "data" are our smartload placeholders
                                                //we don't want to trigger the simple_load on them
                                                //the centering, will be done on the smartload event (see onCustom above)
                                                .find( 'img:not([src^="data"])' );

                                                //trigger the simple load
                                                nb_.delay( function() {
                                                    triggerSimpleLoad( $_imgsToSimpleLoad );
                                                }, 50 );
                                          });//each()
                                    }
                              },// init
                              // make sure slides are always lazyloaded
                              slideChange : function(params) {
                                  // when lazy load is active, we want to lazy load the first image of the slider if offscreen
                                  // img to be lazy loaded looks like data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7
                                  $swiperWrapper.trigger('nb-trigger-lazyload');

                                  if ( $swiperWrapper.find('[src*="data:image/gif;"]').length > 0 ) {
                                      // Make sure we load clean lazy loaded slides on change
                                      // for https://github.com/presscustomizr/nimble-builder/issues/677
                                      $swiperWrapper.find('[src*="data:image/gif;"]').each( function() {
                                          var $img = $(this);
                                          if ( $img.attr('data-sek-img-sizes') ) {
                                              $img.attr('sizes', $img.attr('data-sek-img-sizes') );
                                              $img.removeAttr('data-sek-img-sizes');
                                          }
                                          if ( $img.attr('data-src') ) {
                                              $img.attr('src', $img.attr('data-src') );
                                              $img.removeAttr('data-src');
                                          }
                                          if ( $img.attr('data-sek-src') ) {
                                              $img.attr('src', $img.attr('data-sek-src') );
                                              $img.removeAttr('data-sek-src');
                                          }
                                          if ( $img.attr('data-srcset') ) {
                                              $img.attr('srcset', $img.attr('data-srcset') );
                                              $img.removeAttr('data-srcset');
                                          }
                                      });
                                  }
                              }
                        }//on
                    };

                    // AUTOPLAY
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

                    // NAVIGATION ARROWS && PAGINATION DOTS
                    if ( true === $swiperWrapper.data('sek-is-multislide') ) {
                        var navType = $swiperWrapper.data('sek-navtype');
                        if ( 'arrows_dots' === navType || 'arrows' === navType ) {
                            $.extend( swiperParams, {
                                navigation: {
                                  nextEl: '.sek-swiper-next' + $swiperWrapper.data('sek-swiper-id'),
                                  prevEl: '.sek-swiper-prev' + $swiperWrapper.data('sek-swiper-id')
                                }
                            });
                        }
                        if ( 'arrows_dots' === navType || 'dots' === navType  ) {
                            $.extend( swiperParams, {
                                pagination: {
                                  el: '.swiper-pagination' + $swiperWrapper.data('sek-swiper-id'),
                                  clickable: true,
                                }
                            });
                        }
                    }

                    // LAZYLOAD @see https://swiperjs.com/api/#lazy
                    if ( true === $swiperWrapper.data('sek-lazyload') ) {
                        $.extend( swiperParams, {
                            // Disable preloading of all images
                            preloadImages: false,
                            lazy : {
                              // By default, Swiper will load lazy images after transition to this slide, so you may enable this parameter if you need it to start loading of new image in the beginning of transition
                              loadOnTransitionStart : true
                            }
                        });
                    }

                    mySwipers.push( new Swiper(
                        '.' + swiperClass,//$(this)[0],
                        swiperParams
                    ));

                    // On Swiper Lazy Loading
                    // https://swiperjs.com/api/#lazy
                    $.each( mySwipers, function( ind, _swiperInstance ){
                          _swiperInstance.on( 'lazyImageReady', function( slideEl, imageEl ) {
                              $(imageEl).trigger('recenter');
                          });
                          _swiperInstance.on( 'lazyImageLoad', function( slideEl, imageEl ) {
                              // clean the extra attribute added when preprocessing for lazy loading
                              var $img = $(imageEl);
                              if ( $img.attr('data-sek-img-sizes') ) {
                                  $img.attr('sizes', $img.attr('data-sek-img-sizes') );
                                  $img.removeAttr('data-sek-img-sizes');
                              }
                          });
                    });

              };

              var doAllSwiperInstanciation = function() {
                    $('.sektion-wrapper').find('[data-sek-swiper-id]').each( function() {
                          doSingleSwiperInstantiation.call($(this));
                    });
              };



              // On custom events
              nb_.cachedElements.$body.on( 'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed', '[data-sek-level="location"]',
                    function(evt) {
                          if ( 0 !== mySwipers.length ) {
                                $.each( mySwipers, function( ind, _swiperInstance ){
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
              nb_.cachedElements.$body.on( 'sek-stylesheet-refreshed', '[data-sek-module-type="czr_img_slider_module"]',
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


              // Action on click
              // $( 'body').on( 'click', '[data-sek-module-type="czr_img_slider_module"]', function(evt ) {
              //         // $(this).find('[data-sek-swiper-id]').each( function() {
              //         //       $(this).trigger('sek-nimble-refreshed');
              //         // });
              //       }
              // );


              // Behaviour on mouse hover
              // @seehttps://stackoverflow.com/questions/53028089/swiper-autoplay-stop-the-swiper-when-you-move-the-mouse-cursor-and-start-playba
              $('.swiper-slide').on('mouseover mouseout', function( evt ) {
                  var swiperInstance = $(this).closest('.swiper-container')[0].swiper;
                  if ( ! nb_.isUndefined( swiperInstance ) && true === swiperInstance.params.autoplay.disableOnInteraction ) {
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
              if ( window.wp && ! nb_.isUndefined( wp.customize ) ) {
                    wp.customize.preview.bind('sek-item-focus', function( params ) {

                          var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]', '.swiper-container').first();
                          if ( 1 > $itemEl.length )
                            return;
                          var $swiperContainer = $itemEl.closest('.swiper-container');
                          if ( 1 > $swiperContainer.length )
                            return;

                          var activeSwiperInstance = $itemEl.closest('.swiper-container')[0].swiper;

                          if ( nb_.isUndefined( activeSwiperInstance ) )
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
           * jquerynimbleCenterImages.js v1.0.0
           * ( inspired by Customizr theme jQuery plugin )
           * ===================================================
           * (c) 2019 Nicolas Guillaume, Nice, France
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
                          zeroTopAdjust : -2,//<= top ajustement for sek-h-centrd
                          useImgAttr:false,//uses the img height and width attributes if not visible (typically used for the customizr slider hidden images)
                          setOpacityWhenCentered : false,//this can be used to hide the image during the time it is centered
                          addCenteredClassWithDelay : 0,//<= a small delay can be required when we rely on the sek-v-centrd or sek-h-centrd css classes to set the opacity for example
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

                              //parses imgs ( if any ) in current container
                              var $_imgs = $( self.options.imgSel , self.container );

                              //if no images or centering is not active, only handle the golden ratio on resize event
                              if ( 1 <= $_imgs.length && self.options.enableCentering ) {
                                    self._parse_imgs( $_imgs, _event_ );
                              }
                          };

                      //fire
                      if ( self.options.onInit ) {
                            _do();
                      }

                      //bind the container element with custom events if any
                      //( the images will also be bound )
                      if ( $.isArray( self._customEvt ) ) {
                            self._customEvt.map( function( evt ) {
                                  var $_containerToListen = ( self.options.$containerToListen instanceof $ && 1 < self.options.$containerToListen.length ) ? self.options.$containerToListen : $( self.container );
                                  $_containerToListen.bind( evt, {} , function() {
                                        _do( evt );
                                  });
                            } );
                      }
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
                                  nb_.cachedElements.$window.resize( nb_.debounce( function() {
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

                            if ( 0 !== self.options.addCenteredClassWithDelay && nb_.isNumber( self.options.addCenteredClassWithDelay ) ) {
                                  nb_.delay( function() {
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
                            nb_.delay(function() { _centerImg( $_img ); }, 0 );
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
                                  _class : 'sek-h-centrd'
                            },
                            v : {
                                  dim : { name : 'width', val : c_x },
                                  dir : { name : 'top', val : ( c_y - up_i_y ) / 2 + ( this.options.topAdjust || 0 ) },
                                  _class : 'sek-v-centrd'
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

                      var _elSels       = $(this.container).attr( 'class' ).split(' '),
                          _selsToSkip   = [],
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
      };/////////////// callbackFunc

      // When loaded with defer, we can not be sure that jQuery will be loaded before
      // so let's make sure that we have both the plugin and jQuery loaded
      nb_.listenTo( 'nb-app-ready', function() {
          // on 'nb-app-ready', jQuery is loaded
          nb_.listenTo( 'nb-main-swiper-parsed', callbackFunc );
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
                nb_.cachedElements.$window.unbind('scroll', handlerParams.scrollHandler );
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
            // do nothing if dynamic asset loading is not enabled for js and css
            if ( !sekFrontLocalized.load_front_assets_on_scroll )
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
                nb_.emit('nimble-new-scroll-handler-added', { fire_once : false } );
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
            if ( !sekFrontLocalized.load_front_assets_on_scroll )
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

                  if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_front_assets_on_scroll ) {
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
            if ( !sekFrontLocalized.load_front_assets_on_scroll )
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
                  nb_.ajaxLoadScript({
                      path : 'js/libs/swiper.min.js?'+sekFrontLocalized.assetVersion,
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
            if ( !sekFrontLocalized.load_front_assets_on_scroll )
              return;
            var $candidates = $('[data-sek-video-bg-src]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-jmp-parsed' listened to by nb_.listenTo()
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
            if ( !sekFrontLocalized.load_front_assets_on_scroll )
              return;
            var $candidates = $('i[class*=fa-]');

            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits "nb-needs-fa" listened to by nb_.listenTo()
            var doLoad = function() {
                  //Load the style
                  if ( $('head').find( '#czr-font-awesome' ).length < 1 ) {
                        var link = document.createElement('link');
                        link.setAttribute('href', sekFrontLocalized.frontAssetsPath + 'fonts/css/fontawesome-all.min.css?'+sekFrontLocalized.assetVersion );
                        link.setAttribute('id', 'czr-font-awesome');
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
            // when the plugin is loaded => it emits 'nb-jmp-parsed' listened to by nb_.listenTo()
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
              var _do = function(evt) {
                    $(this).each( function() {
                          var _maybeDoLazyLoad = function() {
                                // if the element already has an instance of nimbleLazyLoad, simply trigger an event
                                if ( !$(this).data('nimbleLazyLoadDone') ) {
                                    $(this).nimbleLazyLoad();
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
        var anchorId = window.nb_.getQueryVariable('go_to'),
            el = document.getElementById(anchorId);
        if( anchorId && el ) {
              setTimeout( function() { el.scrollIntoView();}, 200 );
        }
    }
});