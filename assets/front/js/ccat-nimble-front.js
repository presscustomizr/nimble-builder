// global sekFrontLocalized, fireOnNimbleAppReady
window.nb_ = {};
// Jquery agnostic
(function(w, d){
    //https://underscorejs.org/docs/underscore.html#section-17
    var restArguments = function(func, startIndex) {
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
    var _now = Date.now || function() {
      return new Date().getTime();
    };

    window.nb_ = {
        isArray : function(obj) {
            return Array.isArray(obj) || toString.call(obj) === '[object Array]';
        },
        isUndefined : function(obj) {
          return obj === void 0;
        },
        isObject : function(obj) {
          var type = typeof obj;
          return type === 'function' || type === 'object' && !!obj;
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
        delay : restArguments(function(func, wait, args) {
          return setTimeout(function() {
            return func.apply(null, args);
          }, wait);
        }),
        // safe console log for
        errorLog : function() {
            //fix for IE, because console is only defined when in F12 debugging mode in IE
            if ( nb_.isUndefined( console ) || !nb_.isFunction( window.console.log ) )
              return;
            console.log.apply(console,arguments);
        },
        scriptsLoadingStatus : {},// <= will be populated with the script loading promises
        // func = fn()
        // 'nimble-jquery-ready' is fired @'wp_footer' see inline script in ::_schedule_front_and_preview_assets_printing()
        fireOnJqueryReady : function( func ) {
            if ( typeof undefined !== typeof jQuery ) {
                func();
            } else {
                document.addEventListener('nimble-jquery-ready', func );
            }
        },
        // func = fn()
        // 'nimble-jquery-ready' is fired @'wp_footer' see inline script in ::_schedule_front_and_preview_assets_printing()
        fireOnMagnificPopupReady : function( func ) {
            if ( typeof undefined !== typeof jQuery.fn.magnificPopup ) {
                func();
            } else {
                document.addEventListener('nimble-magnific-popup-ready', func );
            }
        },
    };//window.nb_
}(window, document ));


(function(w, d){
    var onJqueryReady = function() {
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
                    }//isInWindow
              });//$.extend( nb_

              // now that nb_ has been populated, let's say it to the app
              var evt = document.createEvent('Event');
              evt.initEvent('nimble-app-ready', true, true); //can bubble, and is cancellable
              document.dispatchEvent(evt);
              nb_.isReady = true;
          });// jQuery( function($){
    };
    nb_.fireOnJqueryReady( onJqueryReady );

}(window, document));// global sekFrontLocalized, fireOnNimbleAppReady
(function(w, d){
      var onNimbleAppReady = function() {
            jQuery( function($){
                //PREVIEWED DEVICE ?
                //Listen to the customizer previewed device
                if ( nb_.isCustomizing() ) {
                      var _setPreviewedDevice = function() {
                            wp.customize.preview.bind( 'previewed-device', function( device ) {
                                  nb_.previewedDevice = device;// desktop, tablet, mobile
                            });
                      };
                      if ( wp.customize.preview ) {
                          _setPreviewedDevice();
                      } else {
                            wp.customize.bind( 'preview-ready', function() {
                                  _setPreviewedDevice();
                            });
                      }
                }

                var loadNimbleSliderModuleScript = function() {
                      $.ajax( {
                            url : ( sekFrontLocalized.frontAssetsPath + 'js/prod-front-simple-slider-module.min.js'),
                            cache : true,// use the browser cached version when available
                            dataType: "script"
                      }).done(function() {
                            //console.log('ALORS SWIPER Module LOADED ?');
                            if ( 'function' != typeof( window.Swiper ) )
                              return;
                            //the script is loaded. Say it globally.
                            //czrapp.base.scriptLoadingStatus.czrMagnificPopup.resolve();

                            // instantiate if not done yet
                            //if ( ! $lightBoxCandidate.data( 'magnificPopup' ) )
                            //$lightBoxCandidate.magnificPopup( params );
                      }).fail( function() {
                            //czrapp.errorLog( 'Magnific popup instantiation failed for candidate : '  + $lightBoxCandidate.attr( 'class' ) );
                      });
                };
                var loadSwiperScript = function() {
                      $.ajax( {
                            url : ( sekFrontLocalized.frontAssetsPath + 'js/libs/swiper.min.js'),
                            cache : true,// use the browser cached version when available
                            dataType: "script"
                      }).done(function() {
                            //console.log('ALORS SWIPER LIB LOADED ?', window.Swiper );
                            if ( 'function' != typeof( window.Swiper ) )
                              return;

                            loadNimbleSliderModuleScript();
                            //the script is loaded. Say it globally.
                            //czrapp.base.scriptLoadingStatus.czrMagnificPopup.resolve();

                            // instantiate if not done yet
                            //if ( ! $lightBoxCandidate.data( 'magnificPopup' ) )
                            //$lightBoxCandidate.magnificPopup( params );
                      }).fail( function() {
                            //czrapp.errorLog( 'Magnific popup instantiation failed for candidate : '  + $lightBoxCandidate.attr( 'class' ) );
                      });
                };
                if ( sekFrontLocalized.load_js_on_scroll ) {
                    loadSwiperScript();
                }

            });//jQuery( function($){
      };// onJQueryReady

      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));
/*global jQuery */
/*!
* FitText.js 1.2
*
* Copyright 2011, Dave Rupert http://daverupert.com
* Released under the WTFPL license
* http://sam.zoy.org/wtfpl/
*
* Date: Thu May 05 14:23:00 2011 -0600
*/
// global sekFrontLocalized, fireOnNimbleAppReady
(function(w, d){
      var onNimbleAppReady = function() {
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
      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));/* ===================================================
 * jquerynimbleLazyLoad.js v1.0.0
 * ===================================================
 *
 * Replace all img src placeholder in the $element by the real src on scroll window event
 * Bind a 'smartload' event on each transformed img
 *
 * Note : the data-src (data-srcset) attr has to be pre-processed before the actual page load
 * Example of regex to pre-process img server side with php :
 * preg_replace_callback('#<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>#', 'regex_callback' , $_html)
 *
 * (c) 2018 Nicolas Guillaume, Nice, France
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
// global sekFrontLocalized, fireOnNimbleAppReady
(function(w, d){
      var onNimbleAppReady = function() {
           (function ( $, window ) {
              //defaults
              var pluginName = 'nimbleLazyLoad',
                  defaults = {
                        load_all_images_on_first_scroll : false,
                        //attribute : [ 'data-sek-src' ],
                        excludeImg : [],
                        threshold : 200,
                        fadeIn_options : { duration : 400 },
                        delaySmartLoadEvent : 0,

                  },
                  //with intersecting cointainers:
                  //- to avoid race conditions
                  //- to avoid multi processing in general
                  skipLazyLoadClass = 'smartload-skip';


              function Plugin( element, options ) {
                    this.element = element;
                    this.options = $.extend( {}, defaults, options) ;
                    //add .smartload-skip to the excludeImg
                    if ( nb_.isArray( this.options.excludeImg ) ) {
                          this.options.excludeImg.push( '.'+skipLazyLoadClass );
                    } else {
                          this.options.excludeImg = [ '.'+skipLazyLoadClass ];
                    }

                    this._defaults = defaults;
                    this._name = pluginName;
                    this.init();
              }


              //can access this.element and this.option
              Plugin.prototype.init = function () {
                    var self        = this,
                        $_ImgOrDivOrIFrameElements  = $( '[data-sek-src]:not('+ this.options.excludeImg.join() +'), [data-sek-iframe-src]' , this.element );

                    this.increment  = 1;//used to wait a little bit after the first user scroll actions to trigger the timer
                    this.timer      = 0;

                    $_ImgOrDivOrIFrameElements
                          //avoid intersecting containers to parse the same images
                          .addClass( skipLazyLoadClass )
                          .bind( 'sek_load_img', {}, function() { self._load_img(this); })
                          .bind( 'sek_load_iframe', {}, function() { self._load_iframe(this); });

                    //the scroll event gets throttled with the requestAnimationFrame
                    nb_.cachedElements.$window.scroll( function( _evt ) {
                          self._better_scroll_event_handler( $_ImgOrDivOrIFrameElements, _evt );
                    });
                    //debounced resize event
                    nb_.cachedElements.$window.resize( nb_.debounce( function( _evt ) {
                          self._maybe_trigger_load( $_ImgOrDivOrIFrameElements, _evt );
                    }, 100 ) );
                    //on load
                    this._maybe_trigger_load( $_ImgOrDivOrIFrameElements);

              };


              /*
              * @param : array of $img
              * @param : current event
              * @return : void
              * scroll event performance enhancer => avoid browser stack if too much scrolls
              */
              Plugin.prototype._better_scroll_event_handler = function( $_Elements , _evt ) {
                    var self = this;
                    if ( ! this.doingAnimation ) {
                          this.doingAnimation = true;
                          window.requestAnimationFrame(function() {
                                self._maybe_trigger_load( $_Elements , _evt );
                                self.doingAnimation = false;
                          });
                    }
              };


              /*
              * @param : array of $img
              * @param : current event
              * @return : void
              */
              Plugin.prototype._maybe_trigger_load = function( $_Elements , _evt ) {
                    var self = this,
                        //get the visible images list
                        _visible_list = $_Elements.filter( function( ind, _el ) { return self._is_visible( _el ,  _evt ); } );

                    _visible_list.map( function( ind, _el ) {
                          if ( 'IFRAME' === $(_el).prop("tagName") ) {
                                $(_el).trigger( 'sek_load_iframe' );
                          } else {
                                $(_el).trigger( 'sek_load_img' );
                          }
                    });
              };


              /*
              * @param single $img object
              * @param : current event
              * @return bool
              * helper to check if an image is the visible ( viewport + custom option threshold)
              */
              Plugin.prototype._is_visible = function( element, _evt ) {
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
                    var $el_candidate = sniffFirstVisiblePrevElement( $(element) );
                    if ( !$el_candidate || $el_candidate.length < 1 )
                      return false;

                    var wt = nb_.cachedElements.$window.scrollTop(),
                        wb = wt + nb_.cachedElements.$window.height(),
                        it  = $el_candidate.offset().top,
                        ib  = it + $el_candidate.height(),
                        // don't apply a threshold on page load so that Google audit is happy
                        // for https://github.com/presscustomizr/nimble-builder/issues/619
                        th = ( _evt && 'scroll' === _evt.type ) ? this.options.threshold : 0;

                    //force all images to visible if first scroll option enabled
                    if ( _evt && 'scroll' == _evt.type && this.options.load_all_images_on_first_scroll )
                      return true;

                    return ib >= wt - th && it <= wb + th;
              };


              /*
              * @param single $img object
              * @return void
              * replace src place holder by data-src attr val which should include the real src
              */
              Plugin.prototype._load_img = function( _el_ ) {
                    var $_el    = $(_el_),
                        _src     = $_el.attr( 'data-sek-src' ),
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
                                if( $_el.data("sek-lazy-bg") ){
                                      $_el.css('backgroundImage', 'url('+_src+')');
                                } else {
                                      $_el.attr("src", _src );
                                      if ( _src_set ) {
                                            $_el.attr("srcset", _src_set );
                                      }
                                      if ( _sizes ) {
                                            $_el.attr("sizes", _sizes );
                                      }
                                }
                                //prevent executing this twice on an already smartloaded img
                                if ( ! $_el.hasClass('sek-lazy-loaded') ) {
                                      $_el.addClass('sek-lazy-loaded');
                                }
                                //Following would be executed twice if needed, as some browsers at the
                                //first execution of the load callback might still have not actually loaded the img

                                $_el.trigger('smartload');
                                //flag to avoid double triggering
                                $_el.data('sek-lazy-loaded', true );
                          });//<= create a load() fn
                    //http://stackoverflow.com/questions/1948672/how-to-tell-if-an-image-is-loaded-or-cached-in-jquery
                    if ( $jQueryImgToLoad[0].complete ) {
                          $jQueryImgToLoad.trigger( 'load' );
                    }
                    $_el.removeClass('lazy-loading');
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
                          if ( ! $_el.hasClass('sek-lazy-loaded') ) {
                                $_el.addClass('sek-lazy-loaded');
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

      };////////////// onJQueryReady
      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));
// global sekFrontLocalized, fireOnNimbleAppReady
/* ===================================================
 * jquery.fn.parallaxBg v1.0.0
 * Created in October 2018.
 * Inspired from https://github.com/presscustomizr/front-jquery-plugins/blob/master/jqueryParallax.js
 * ===================================================
*/
(function(w, d){
      var onNimbleAppReady = function() {
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
      };/////////////// onJQueryReady
      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));
// global sekFrontLocalized, fireOnNimbleAppReady
/* ------------------------------------------------------------------------- *
 *  ACCORDION MODULE
/* ------------------------------------------------------------------------- */
(function(w, d){
      var onNimbleAppReady = function() {
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

      };/////////////// onJQueryReady
      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));


// global sekFrontLocalized, fireOnNimbleAppReady
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
/* ------------------------------------------------------------------------- */
(function(w, d){
      var onNimbleAppReady = function() {
          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;

              // fire the fn when we know that it's been loaded
              // either enqueued with possible defer or injected with js
              nb_.fireOnMagnificPopupReady(
                  function() {
                      // $candidates are .sek-link-to-img-lightbox selectors
                      $linkCandidates.each( function() {
                          $linkCandidate = $(this);
                          // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
                          if ( $linkCandidate.length < 1 || 'string' !== typeof( $linkCandidate[0].protocol ) || -1 !== $linkCandidate[0].protocol.indexOf('javascript') )
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
                  }
              );//nb_.fireOnMagnificPopupReady

              //Load the style
              if ( sekFrontLocalized.load_js_on_scroll ) {
                  if ( $('head').find( '#czr-magnific-popup' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'czr-magnific-popup',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/magnific-popup.min.css'
                        }) );
                  }
              }

              // Load js plugin if needed
              // when the plugin is loaded => it emits 'nimble-magnific-popup-ready' listened to by nb_.fireOnMagnificPopupReady()
              if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_js_on_scroll ) {
                    var _scrollHandle = function() {},//abstract that we can unbind
                        doLoadMagnificPopup = function() {
                          // I've been executed forget about me
                          nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                          // Check if the load request has already been made, but not yet finished.
                          if ( nb_.scriptsLoadingStatus.czrMagnificPopup && 'pending' === nb_.scriptsLoadingStatus.czrMagnificPopup.state() ) {
                                nb_.scriptsLoadingStatus.czrMagnificPopup.done( function() {
                                      _doMagnificPopupWhenScriptAndStyleLoaded($linkCandidates);
                                });
                                return;
                          }

                          // set the script loading status now to avoid several calls
                          nb_.scriptsLoadingStatus.czrMagnificPopup = nb_.scriptsLoadingStatus.czrMagnificPopup || $.Deferred();

                          $.ajax( {
                                url : sekFrontLocalized.frontAssetsPath + 'js/libs/jquery-magnific-popup.min.js',
                                cache : true,// use the browser cached version when available
                                dataType: "script"
                          }).done(function() {
                                if ( 'function' != typeof( $.fn.magnificPopup ) )
                                  return;
                                //the script is loaded. Say it globally.
                                nb_.scriptsLoadingStatus.czrMagnificPopup.resolve();
                                // instantiate if not done yet
                                //if ( ! $lightBoxCandidate.data( 'magnificPopup' ) )
                                //_doMagnificPopupWhenScriptAndStyleLoaded($linkCandidates);
                          }).fail( function() {
                                nb_.errorLog('Magnific popup instantiation failed for candidate');
                          });
                    };// doLoadMagnificPopup
                    // Fire now or schedule when becoming visible.
                    if ( nb_.isInWindow( $linkCandidates.first() ) ) {
                          doLoadMagnificPopup();
                    } else {
                          _scrollHandle = nb_.throttle( function() {
                                if ( nb_.isInWindow( $linkCandidates.first() ) ) {
                                      doLoadMagnificPopup();
                                }
                          }, 100 );
                          nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
                    }
              } // if( sekFrontLocalized.load_js_on_scroll )

        });//jQuery(function($){})


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


        /* ------------------------------------------------------------------------- *
         *  SCROLL TO ANCHOR
        /* ------------------------------------------------------------------------- */
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





      };/////////////// onJQueryReady

      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));
