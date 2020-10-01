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

}(window, document));