// march 2020 : wp_head js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
// printed in function sek_initialize_front_js_app()

// global sekFrontLocalized, nimbleListenTo
window.nb_ = {};
// Jquery agnostic
(function(w, d){
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
        // safe console log for
        errorLog : function() {
            //fix for IE, because console is only defined when in F12 debugging mode in IE
            if ( nb_.isUndefined( console ) || 'function' != typeof window.console.log )
              return;
            console.log.apply(console,arguments);
        },
        // Browser detection
        // @see https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
        browserIs : function( browser ) {
            var bool = false,
                isIE = false || !!document.documentMode;
            switch( browser) {
                case 'safari' :
                    bool = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));
                break;
                case 'firefox' :
                    bool = typeof InstallTrigger !== 'undefined';
                break;
                case 'IE' :
                    bool = isIE;
                break;
                case 'edge' :
                    bool = !isIE && !!window.StyleMedia;
                break;
                case 'chrome' :
                    bool = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);
                break;
            }
            return bool;
        },
        assetPreloadSupported : function() {
            return !nb_.browserIs('firefox') && !nb_.browserIs('IE') && !nb_.browserIs('edge');
        },
        listenTo : function( evt, func ) {
            var canWeFireCallbackForEvent = {
                'nimble-jquery-loaded' : function() { return typeof undefined !== typeof jQuery; },
                'nimble-app-ready' : function() { return ( typeof undefined !== typeof window.nb_ ) && nb_.wasListenedTo('nimble-jquery-loaded'); },
                'nimble-magnific-popup-loaded' : function() { return ( typeof undefined !== typeof jQuery ) && ( typeof undefined !== typeof jQuery.fn.magnificPopup ); },
                'nimble-swiper-script-loaded' : function() { return typeof undefined !== typeof window.Swiper; }
            };
            // e is the event object passed
            // it is possible to add params but we need to use new CustomEvent with a polyfill for IE
            // see : https://stackoverflow.com/questions/18613456/trigger-event-with-parameters
            var _executeAndLog = function(e) {
                if ( !nb_.isUndefined(canWeFireCallbackForEvent[evt]) && false === canWeFireCallbackForEvent[evt]() ) {
                    nb_.errorLog('Nimble error => an event callback could not be fired because conditions not met => ', evt, nb_.eventsListenedTo );
                    return;
                }
                func();
                // console.log('LISTENED TO', evt );
                // store it, so if the event has been emitted before the listener is fired, we know it's been emitted
                nb_.eventsListenedTo.push(evt);
            };
            // if the event requires a condition to be executed let's check it
            // if the event has alreay been listened to, let's fire the func, otherwise wait for its emission
            if ( 'function' === typeof func ) {
                if ( nb_.wasEmitted(evt) ) {
                    _executeAndLog();
                } else {
                    document.addEventListener(evt,_executeAndLog);
                }
            } else {
              nb_.errorLog('Nimble error => listenTo func param is not a function for event => ', evt );
            }
        },
        eventsEmitted : [],
        eventsListenedTo : [],
        emit : function(evt) {
            // it is possible to add params when dispatching the event, but we need to use new CustomEvent with a polyfill for IE
            // see : https://stackoverflow.com/questions/18613456/trigger-event-with-parameters
            var _evt = document.createEvent('Event');
            _evt.initEvent(evt, true, true); //can bubble, and is cancellable
            document.dispatchEvent(_evt);
            // console.log('EMITTED', evt );
            nb_.eventsEmitted.push(evt);
        },
        wasListenedTo : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsListenedTo, evt );
        },
        wasEmitted : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsEmitted, evt );
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
// in <script id="nimble-load-jquery">
( function() {
      // Load jQuery
      setTimeout( function() {
          // deferred script
          // var script = document.createElement('script');
          // script.setAttribute('src', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js');
          // script.setAttribute('id', 'nimble-jquery');
          // script.setAttribute('defer', 'defer');//https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
          // document.getElementsByTagName('head')[0].appendChild(script);

          // Preload @see https://web.dev/uses-rel-preload/
          var appendScript = function() {
              var script = document.createElement('script');
              script.setAttribute('src', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js');
              script.setAttribute('id', 'nimble-jquery');
              script.setAttribute('defer', 'defer');//https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
              document.getElementsByTagName('head')[0].appendChild(script);
              // remove script wrapper when done
              var elem = document.getElementById("nimble-load-jquery");
              elem.parentNode.removeChild(elem);
          };
          // Firefox does not support preload
          // @see https://web.dev/preload-critical-assets/
          // https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
          var isFirefox = typeof InstallTrigger !== 'undefined';
          if ( !nb_.assetPreloadSupported() ) {
              appendScript();
          } else {
              var link = document.createElement('link');
              link.setAttribute('href', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js');
              link.setAttribute('rel', 'preload');
              link.setAttribute('as', 'script');
              link.onload = function() {
                  this.onload=null;
                  this.rel='script';
                  appendScript();
              };
              document.getElementsByTagName('head')[0].appendChild(link);
          }
      }, 0 );//<= add a delay to test 'nimble-jquery-loaded' and mimic the 'defer' option of a cache plugin
})();


// printed ::sek_maybe_load_font_awesome_icons()
// in <script id="nimble-load-fa">
(function() {
      // Preload @see https://web.dev/uses-rel-preload/
      // @see https://web.dev/preload-critical-assets/
      // @see https://caniuse.com/#search=preload
      // IE and Firefox do not support preload
      // edge is supposed to support it, but when refreshing the page several times, google font are sometimes not loaded... so let's not preload with it as well
      // https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
      var link = document.createElement('link');
      link.setAttribute('href', '<?php echo NIMBLE_BASE_URL . "/assets/front/fonts/css/fontawesome-all.min.css"; ?>');
      link.setAttribute('rel', nb_.assetPreloadSupported() ? 'preload' : 'stylesheet' );
      link.setAttribute('id', 'czr-font-awesome' );
      link.setAttribute('as', 'style');
      link.onload = function() {
          this.onload=null;
          if ( nb_.assetPreloadSupported() ) {
              this.rel='stylesheet';
          }
          // remove script wrapper when done
          var elem = document.getElementById("nimble-load-fa");
          elem.parentNode.removeChild(elem);
      };
      document.getElementsByTagName('head')[0].appendChild(link);

})();




// printed SEK_Front_Render_Css::in sek_gfont_print_with_preload()
// in <script id="nimble-preload-gfonts">
(function() {
      // Preload @see https://web.dev/uses-rel-preload/
      // @see https://web.dev/preload-critical-assets/
      // @see https://caniuse.com/#search=preload
      // IE and Firefox do not support preload
      // edge is supposed to support it, but when refreshing the page several times, google font are sometimes not loaded... so let's not preload with it as well
      // https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
      var link = document.createElement('link');
      link.setAttribute('href', '//fonts.googleapis.com/css?family=<?php echo $print_candidates; ?>');
      link.setAttribute('rel', nb_.assetPreloadSupported() ? 'preload' : 'stylesheet' );
      link.setAttribute('as', 'style');
      link.onload = function() {
          this.onload=null;
          if ( nb_.assetPreloadSupported() ) {
              this.rel='stylesheet';
          }
          // remove script wrapper when done
          var elem = document.getElementById("nimble-preload-gfonts");
          elem.parentNode.removeChild(elem);

      };
      document.getElementsByTagName('head')[0].appendChild(link);
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