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
        },
        isInScreen : function(el, threshold) {
          var wt = window.pageYOffset || document.documentElement.scrollTop,
              wb = wt + window.innerHeight,
              it  = el.offsetTop,
              ib  = it + el.clientHeight,
              th = threshold || 0;

          return ib >= wt - th && it <= wb + th;
        },
        isCustomizing : function() {
          return true == '<?php echo skp_is_customizing(); ?>';
        },
        isLazyLoadEnabled : function() {
          return !nb_.isCustomizing() && true == '<?php echo sek_is_img_smartload_enabled(); ?>';
        }
    };//window.nb_

    //forEach not supported by IE
    //This polyfill adds compatibility to all Browsers supporting ES5:
    if (window.NodeList && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = function (callback, thisArg) {
            thisArg = thisArg || window;
            for (var i = 0; i < this.length; i++) {
                callback.call(thisArg, this[i], i, this);
            }
        };
    }
    // handle bg images when lazyloading off
    var _bgRevealed = false;
        _revealBGImages = function() {
        if ( !_bgRevealed ) {
            _bgRevealed = true;
            var matches = document.querySelectorAll('div.sek-has-bg');

            if ( nb_.isObject( matches ) && matches.length > 0 ) {
                var imgSrc, isInScreen = false;
                matches.forEach( function(el) {
                    if ( nb_.isObject(el) ) {
                        if ( !nb_.isLazyLoadEnabled() || ( nb_.isInScreen(el) && nb_.isLazyLoadEnabled() ) ) {
                            imgSrc = el.getAttribute('data-sek-src');
                            if ( imgSrc ) {
                                el.setAttribute( 'style', 'background-image:url("' + el.getAttribute('data-sek-src') +'")' );
                                el.className += ' smartload-skip';//<= so we don't parse it twice when lazyload is active
                                // clean css loader
                                var css_loaders = el.querySelectorAll('.sek-css-loader');
                                css_loaders.forEach( function(_cssl) {
                                    if ( nb_.isObject(_cssl) ) {
                                        _cssl.parentNode.removeChild(_cssl);
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }
    };
    // dom ready the jquery way
    // Use the handy event callback
    document.addEventListener( "DOMContentLoaded", _revealBGImages );
    // A fallback to window.onload, that will always work
    window.addEventListener( "load", _revealBGImages );
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


// printed in function ek_preload_jquery_from_dns()
// in <script id="nimble-load-jquery">
( function() {
      // Load jQuery
      setTimeout( function() {
          // Preload @see https://web.dev/uses-rel-preload/
          var appendScript = function() {
              var script = document.createElement('script');
              script.setAttribute('src', '<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>');
              script.setAttribute('id', '<?php echo NIMBLE_JQUERY_ID; ?>');
              script.setAttribute('defer', 'defer');//https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
              document.getElementsByTagName('head')[0].appendChild(script);
              // remove script wrapper when done
              var elem = document.getElementById("nimble-load-jquery");
              elem.parentNode.removeChild(elem);
          };
          // Firefox does not support preload
          // @see https://web.dev/preload-critical-assets/
          // https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
          if ( !nb_.assetPreloadSupported() ) {
              appendScript();
          } else {
              var link = document.createElement('link');
              link.setAttribute('href', '<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>');
              link.setAttribute('rel', 'preload');
              link.setAttribute('id', '<?php echo NIMBLE_JQUERY_ID; ?>');
              link.setAttribute('as', 'script');
              link.onload = function() {
                  this.onload=null;
                  this.rel='script';
                  appendScript();
              };
              document.getElementsByTagName('head')[0].appendChild(link);
          }
      }, 1000 );//<= add a delay to test 'nimble-jquery-loaded' and mimic the 'defer' option of a cache plugin
})();



// printed in function sek_detect_jquery()
// march 2020 : wp_footer js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
( function() {
      var _maybeEmit = function() {
        var evt = 'nimble-jquery-loaded';
        if ( !nb_.wasEmitted(evt) ) {
            nb_.emit(evt);
        }
      };
      // recursively try to load jquery every 200ms during 6s ( max 30 times )
      var _emitWhenJqueryIsReady = function( attempts ) {
          attempts = attempts || 0;
          if ( typeof undefined !== typeof window.jQuery ) {
              _maybeEmit();
          } else if ( attempts < 30 ) {
              setTimeout( function() {
                  attempts++;
                  _emitWhenJqueryIsReady( attempts );
              }, 200 );
          } else {
              alert('Nimble Builder problem : jQuery.js was not detected on your website');
          }
      };
      // if jQuery has already be printed, let's listen to the load event
      var jquery_script_el = document.getElementById('<?php echo NIMBLE_JQUERY_ID; ?>');
      if ( jquery_script_el ) {
          jquery_script_el.addEventListener('load', function() {
              _maybeEmit();
          });
      }
      _emitWhenJqueryIsReady();
})();


// printed in function sek_maybe_inject_jquery_migrate()
// march 2020 : wp_footer js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
// in script nimble-load-jquery-migrate
(function() {
    // Preload @see https://web.dev/uses-rel-preload/
    var appendScript = function() {
        var script = document.createElement('script');
        script.setAttribute('src', '<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>');
        script.setAttribute('id', 'nb-query-migrate');
        script.setAttribute('defer', 'defer');//https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
        document.getElementsByTagName('head')[0].appendChild(script);
        // remove script wrapper when done
        var elem = document.getElementById("nimble-load-jquery-migrate");
        elem.parentNode.removeChild(elem);
    };
    nb_.listenTo('nimble-jquery-loaded', function() {
        // Firefox does not support preload
        // @see https://web.dev/preload-critical-assets/
        // https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
        if ( !nb_.assetPreloadSupported() ) {
            appendScript();
        } else {
            var link = document.createElement('link');
            link.setAttribute('href', '<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>');
            link.setAttribute('rel', 'preload');
            link.setAttribute('id', 'nb-query-migrate');
            link.setAttribute('as', 'script');
            link.onload = function() {
                this.onload=null;
                this.rel='script';
                appendScript();
            };
            document.getElementsByTagName('head')[0].appendChild(link);
        }
    });
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