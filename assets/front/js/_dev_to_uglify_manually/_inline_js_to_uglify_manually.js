// march 2020 : wp_head js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
// printed in function sek_initialize_front_js_app()

// global sekFrontLocalized, nimbleListenTo
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
    var has = function(obj, path) {
      return obj != null && hasOwnProperty.call(obj, path);
    };
    // helper for nb_.throttle()
    var _now = Date.now || function() {
      return new Date().getTime();
    };

    window.nb_ = {
        isArray : function(obj) {
            return Array.isArray(obj) || toString.call(obj) === '[object Array]';
        },
        inArray : function(obj, value) {
          if ( !nb_.isArray(obj) || nb_.isUndefined(value) )
            return false;
          return obj.indexOf(value) > -1;
        },
        isUndefined : function(obj) {
          return obj === void 0;
        },
        isObject : function(obj) {
          var type = typeof obj;
          return type === 'function' || type === 'object' && !!obj;
        },
        has : function(obj, path) {
          if (!_.isArray(path)) {
            return has(obj, path);
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
        listenTo : function( evt, func ) {
            var bools = {
                'nimble-jquery-loaded' : typeof undefined !== typeof jQuery,
                'nimble-app-ready' : typeof undefined !== typeof window.nb_ && nb_.isListenedTo('nimble-jquery-loaded'),
                'nimble-magnific-popup-loaded' : typeof undefined !== typeof jQuery && typeof undefined !== typeof jQuery.fn.magnificPopup,
                'nimble-swiper-script-loaded' : typeof undefined !== typeof window.Swiper
            };
            if ( 'function' === typeof func ) {
              // For event without a boolean check, if the event has been emitted before the listener is fired, we know it's been emitted if stored in [] emittedEvents
              if ( true === bools[evt] || ( nb_.isUndefined( bools[evt] ) && nb_.isListenedTo( nb_.eventsListenedTo, evt ) ) ) {
                  func();
                  // store it, so if the event has been emitted before the listener is fired, we know it's been emitted
                  nb_.eventsListenedTo.push(evt);
              }
              else {
                  document.addEventListener(evt,function() {
                      func();
                      // store it, so if the event has been emitted before the listener is fired, we know it's been emitted
                      nb_.eventsListenedTo.push(evt);
                  });
              }
            }
        },
        emittedEvents : [],
        eventsListenedTo : [],
        emit : function( evt ) {
            var _evt = document.createEvent('Event');
            _evt.initEvent(evt, true, true); //can bubble, and is cancellable
            document.dispatchEvent(_evt);
            nb_.emittedEvents.push(evt);
        },
        isListenedTo : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsListenedTo, evt );
        }
    };//window.nb_
}(window, document ));


// nb_.listenTo = function( evt, func ) {
//     var bools = {
//         'nimble-jquery-loaded' : typeof undefined !== typeof jQuery,
//         'nimble-app-ready' : typeof undefined !== typeof window.nb_ && nb_.isReady === true,
//         'nimble-magnific-popup-loaded' : typeof undefined !== typeof jQuery && typeof undefined !== typeof jQuery.fn.magnificPopup,
//         'nimble-swiper-script-loaded' : typeof undefined !== typeof window.Swiper
//     };
//     if ( 'function' === typeof func ) {
//       if ( true === bools[evt] ) func();
//       else document.addEventListener(evt,func);
//     }
// }

// printed in function sek_handle_jquery()
// march 2020 : wp_footer js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
( function() {
      // recursively try to load jquery every 200ms during 6s ( max 30 times )
      var sayWhenJqueryIsReady = function( attempts ) {
          attempts = attempts || 0;
          if ( typeof undefined !== typeof jQuery ) {
              nb_.emit('nimble-jquery-loaded');
          } else if ( attempts < 30 ) {
              setTimeout( function() {
                  attempts++;
                  sayWhenJqueryIsReady( attempts );
              }, 200 );
          } else {
              alert('Nimble Builder problem : jQuery.js was not detected on your website');
          }
      };
      sayWhenJqueryIsReady();
})();

// printed in function sek_handle_jquery()
( function() {
      // Load jQuery
      setTimeout( function() {
          var script = document.createElement('script');
          script.setAttribute('src', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js');
          script.setAttribute('type', 'text/javascript');
          script.setAttribute('id', 'nimble-jquery');
          script.setAttribute('defer', 'defer');//https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
          document.getElementsByTagName('head')[0].appendChild(script);
      }, 0 );//<= add a delay to test 'nimble-jquery-loaded' and mimic the 'defer' option of a cache plugin
})();


// printed in function sek_customizr_js_stuff()
// global sekFrontLocalized, nimbleListenTo
(function(w, d){
      var callbackFunc = function() {
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
            });//jQuery( function($){
      };// onJQueryReady

      nb_.listenTo('nimble-app-ready', callbackFunc );
}(window, document));