// global sekFrontLocalized, nimbleFireOn
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
        scriptsLoadingStatus : {}// <= will be populated with the script loading promises
    };//window.nb_
}(window, document ));


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
              nb_.isReady = true;
              var evt = document.createEvent('Event');
              evt.initEvent('nimble-app-ready', true, true); //can bubble, and is cancellable
              document.dispatchEvent(evt);
          });// jQuery( function($){
    };
    // 'nimble-jquery-ready' is fired @'wp_footer' see inline script in ::_schedule_front_and_preview_assets_printing()
    window.nimbleFireOn('nimble-jquery-ready', callbackFunc );

}(window, document));