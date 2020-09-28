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
        hasPreloadSupport : function( browser ) {
          var link = document.createElement('link');
          var relList = link.relList;
          if (!relList || !relList.supports)
            return false;
          return relList.supports('preload');
        },
        listenTo : function( evt, func ) {
            var canWeFireCallbackForEvent = {
                'nb-jquery-loaded' : function() { return typeof undefined !== typeof jQuery; },
                'nb-app-ready' : function() { return ( typeof undefined !== typeof window.nb_ ) && nb_.wasListenedTo('nb-jquery-loaded'); },
                'nb-jmp-parsed' : function() { return ( typeof undefined !== typeof jQuery ) && ( typeof undefined !== typeof jQuery.fn.magnificPopup ); },
                'nb-main-swiper-parsed' : function() { return typeof undefined !== typeof window.Swiper; }
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
        // @param params { fire_once : false }
        // fire_once is used in nb_.maybeLoadAssetsWhenSelectorInScreen()
        emit : function(evt, params ) {
            var _fire_once = nb_.isUndefined( params ) || params.fire_once;
            if ( _fire_once && nb_.wasEmitted(evt) )
              return;
            //console.log('emitted event', evt );
            // it is possible to add params when dispatching the event, but we need to use new CustomEvent with a polyfill for IE
            // see : https://stackoverflow.com/questions/18613456/trigger-event-with-parameters
            var _evt = document.createEvent('Event');
            _evt.initEvent(evt, true, true); //can bubble, and is cancellable
            document.dispatchEvent(_evt);
            nb_.eventsEmitted.push(evt);
        },
        wasListenedTo : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsListenedTo, evt );
        },
        wasEmitted : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsEmitted, evt );
        },
        // https://stackoverflow.com/questions/5353934/check-if-element-is-visible-on-screen
        isInScreen : function(el) {
            if ( !nb_.isObject( el ) )
              return false;
            var rect = el.getBoundingClientRect(),
                viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
            return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
        },
        isCustomizing : function() {
            return true == '<?php echo skp_is_customizing(); ?>';
        },
        isLazyLoadEnabled : function() {
            return !nb_.isCustomizing() && true == '<?php echo sek_is_img_smartload_enabled(); ?>';
        },
        // params = {id, as, href, onEvent, scriptEl }
        preloadAsset : function(params) {
            //console.log('PARAMS ?', params, nb_.hasPreloadSupport() );
            params = params || {};

            // bail if preloaded already ?
            nb_.preloadedAssets = nb_.preloadedAssets || [];
            if ( nb_.inArray( nb_.preloadedAssets, params.id ) )
              return;

            var headTag = document.getElementsByTagName('head')[0],
                link = document.createElement('link'),
                link_rel = 'style' === params.as ? 'stylesheet' : 'script',
                _injectFinalAsset = function() {
                    var link = this;
                    // this is the link element
                    if ( 'style' === params.as ) {
                       link.setAttribute('rel', 'stylesheet');
                    } else {
                        var _script = document.createElement("script");
                        _script.setAttribute('src', params.href );
                        _script.setAttribute('id', params.id );
                        if ( !nb_.hasPreloadSupport() && 'script' === params.as ) {
                            _script.setAttribute('defer', 'defer');
                        }
                        headTag.appendChild(_script);
                        // clean the loader link
                        if ( link && link.parentNode ) {
                            link.parentNode.removeChild(link);
                        }
                    }
                  };
            if ( ! nb_.hasPreloadSupport() && 'script' === params.as ) {
                if ( params.onEvent ) {
                    nb_.listenTo( params.onEvent, function() { _injectFinalAsset.call(link); });
                } else {
                    _injectFinalAsset.call(link);
                }
            } else {
                link.setAttribute('href', params.href);
                link.setAttribute('rel', nb_.hasPreloadSupport() ? 'preload' : 'stylesheet' );
                link.setAttribute('id', params.id );
                link.setAttribute('as', params.as);
                link.onload = function() {
                    this.onload=null;
                    if ( params.onEvent ) {
                        nb_.listenTo( params.onEvent, function() { _injectFinalAsset.call(link); });
                    } else {
                        _injectFinalAsset.call(link);
                    }
                };
                link.onerror = function(er) {
                    nb_.errorLog('Nimble preloadAsset error', er, params );
                };
            }
            headTag.appendChild(link);

            // store the asset as done
            nb_.preloadedAssets.push( params.id );

            // clean the script element from which preload has been requested
            if ( params.scriptEl && params.scriptEl.parentNode ) {
                params.scriptEl.parentNode.removeChild(params.scriptEl);
            }
        },
        mayBeRevealBG : function() {
            var imgSrc = this.getAttribute('data-sek-src');
            if ( imgSrc ) {
                this.setAttribute( 'style', 'background-image:url("' + this.getAttribute('data-sek-src') +'")' );
                this.className += ' sek-lazy-loaded';//<= so we don't parse it twice when lazyload is active
                // clean css loader
                var css_loaders = this.querySelectorAll('.sek-css-loader');
                css_loaders.forEach( function(_cssl) {
                    if ( nb_.isObject(_cssl) ) {
                        _cssl.parentNode.removeChild(_cssl);
                    }
                });
            }
        }
    };//window.nb_

    // forEach not supported by IE
    // This polyfill adds compatibility to all Browsers supporting ES5:
    if (window.NodeList && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = function (callback, thisArg) {
            thisArg = thisArg || window;
            for (var i = 0; i < this.length; i++) {
                callback.call(thisArg, this[i], i, this);
            }
        };
    }
    // maybe reveal bg images on dom ready
    // if lazyload is not loaded yet, and container is visible
    // Sept 2020 : if lazyload disabled, make sure all background get revealed
    // because background are always printed as data-sek-src attribute for a level, lazyload or not, and therefore need to be inlined styled with javascript
    nb_.listenTo('nb-docready', function() {
        var matches = document.querySelectorAll('div.sek-has-bg');
        if ( !nb_.isObject( matches ) || matches.length < 1 )
          return;
        var imgSrc;
        matches.forEach( function(el) {
            if ( !nb_.isObject(el) )
              return;
            if ( window.sekFrontLocalized && window.sekFrontLocalized.lazyload_enabled ) {
                if ( nb_.isInScreen(el) ) {
                    nb_.mayBeRevealBG.call(el);
                }
            } else {
                nb_.mayBeRevealBG.call(el);
            }
        });
    });

    // Add an internal document ready listener the jquery way
    // Catch cases where $(document).ready() is called
    // after the browser event has already occurred.
    // Support: IE <=9 - 10 only
    // Older IE sometimes signals "interactive" too soon
    if ( document.readyState === "complete" || ( document.readyState !== "loading" && !document.documentElement.doScroll ) ) {
        nb_.emit('nb-docready');
    } else {
        var _docReady = function() {
            if ( !nb_.wasEmitted('nb-docready') ) {
                nb_.emit('nb-docready');
            }
        };
        // Use the handy event callback
        document.addEventListener( "DOMContentLoaded", _docReady );
        // A fallback to window.onload, that will always work
        window.addEventListener( "load", _docReady );
    }

}(window, document ));



















// printed in function ek_preload_jquery_from_dns()
// in <script id="nimble-load-jquery">


// nb_.listenTo = function( evt, func ) {
//     var bools = {
//         'nb-jquery-loaded' : typeof undefined !== typeof jQuery,
//         'nb-app-ready' : typeof undefined !== typeof window.nb_ && nb_.isReady === true,
//         'nb-jmp-parsed' : typeof undefined !== typeof jQuery && typeof undefined !== typeof jQuery.fn.magnificPopup,
//         'nb-main-swiper-parsed' : typeof undefined !== typeof window.Swiper
//     };
//     if ( 'function' === typeof func ) {
//       if ( true === bools[evt] ) func();
//       else document.addEventListener(evt,func);
//     }
// }


// printed in function sek_detect_jquery()
// march 2020 : wp_footer js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
( function() {
      var _maybeEmit = function() {
        var evt = 'nb-jquery-loaded';
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




// printed in sek_maybe_preload_front_scripts_and_styles
nb_.listenTo('nb-needs-magnific-popup', function() {
    nb_.preloadAsset( {
        id : 'nb-magnific-popup',
        as : 'script',
        href : "<?php echo $assets_urls['nb-magnific-popup']; ?>",
        onEvent : 'nb-docready',
        // scriptEl : document.currentScript
    });
    nb_.preloadAsset( {
      id : 'nb-magnific-popup-style',
      as : 'style',
      href : "<?php echo $assets_urls['nb-magnific-popup-style']; ?>",
      onEvent : 'nb-docready',
      // scriptEl : document.currentScript
    });
});
nb_.listenTo('nb-needs-swiper', function() {
    nb_.preloadAsset( {
        id : 'nb-swiper',
        as : 'script',
        href : "<?php echo $assets_urls['nb-swiper']; ?>",
        onEvent : 'nb-docready',
        // scriptEl : document.currentScript
    });
});
nb_.listenTo('nb-needs-videobg-js', function() {
    nb_.preloadAsset( {
        id : 'nb-video-bg-plugin',
        as : 'script',
        href : "<?php echo $assets_urls['nb-video-bg-plugin']; ?>",
        onEvent : 'nb-docready',
        // scriptEl : document.currentScript
    });
});



// printed in function sek_preload_jquery_from_dns()
// in <script id="nimble-load-jquery">
( function() {
      // Load jQuery
      setTimeout( function() {
          nb_.preloadAsset( {
              id : '<?php echo NIMBLE_JQUERY_ID; ?>',
              as : 'script',
              href : '<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>',
              scriptEl : document.currentScript
          });
      }, 1000 );//<= add a delay to test 'nb-jquery-loaded' and mimic the 'defer' option of a cache plugin
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
        document.currentScript.parentNode.removeChild(document.currentScript);
    };
    nb_.listenTo('nb-jquery-loaded', function() {
        // Firefox does not support preload
        // @see https://web.dev/preload-critical-assets/
        // https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
        if ( !nb_.hasPreloadSupport() ) {
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




//printed in sek_maybe_load_scripts_in_ajax()
(function(w, d){
    nb_.listenTo( 'nb-jquery-loaded', function() {
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
        nb_.scriptsLoadingStatus = {};
        nb_.ajaxLoadScript = function( params ) {
            jQuery(function($){
                params = $.extend( { path : '', complete : '', loadcheck : false }, params );
                // Bail if the load request has already been made, but not yet finished.
                if ( nb_.scriptsLoadingStatus[params.path] && 'pending' === nb_.scriptsLoadingStatus[params.path].state() ) {
                  return;
                }
                // set the script loading status now to avoid several calls
                nb_.scriptsLoadingStatus[params.path] = nb_.scriptsLoadingStatus[params.path] || $.Deferred();
                jQuery.ajax( {
                      url : sekFrontLocalized.frontAssetsPath + params.path + '?'+ sekFrontLocalized.assetVersion,
                      cache : true,// use the browser cached version when available
                      dataType: "script"
                }).done(function() {
                      if ( ('function' === typeof params.loadcheck) && !params.loadcheck() ) {
                          nb_.errorLog('ajaxLoadScript success but loadcheck failed for => ' + params.path );
                          return;
                      }

                      if ( 'function' === typeof params.complete ) {
                          params.complete();
                      }
                }).fail( function() {
                      nb_.errorLog('ajaxLoadScript failed for => ' + params.path );
                });
            });
        };//ajaxLoadScript


        jQuery(function($){
            if ( !sekFrontLocalized.load_front_assets_on_scroll )
                return;

            nb_.ajaxLoadScript({
                path : sekFrontLocalized.isDevMode ? 'js/ccat-nimble-front.js' : 'js/ccat-nimble-front.min.js'
            });
        });//jQuery(function($){})
    });/////////////// callbackFunc
}(window, document));







// // printed ::sek_maybe_load_font_awesome_icons()
// // in <script id="nimble-load-fa">
// (function() {
//       // Preload @see https://web.dev/uses-rel-preload/
//       // @see https://web.dev/preload-critical-assets/
//       // @see https://caniuse.com/#search=preload
//       // IE and Firefox do not support preload
//       // edge is supposed to support it, but when refreshing the page several times, google font are sometimes not loaded... so let's not preload with it as well
//       // https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser#9851769
//       var link = document.createElement('link');
//       link.setAttribute('href', '<?php echo NIMBLE_BASE_URL . "/assets/front/fonts/css/fontawesome-all.min.css"; ?>');
//       link.setAttribute('rel', nb_.hasPreloadSupport() ? 'preload' : 'stylesheet' );
//       link.setAttribute('id', 'czr-font-awesome' );
//       link.setAttribute('as', 'style');
//       link.onload = function() {
//           this.onload=null;
//           if ( nb_.hasPreloadSupport() ) {
//               this.rel='stylesheet';
//           }
//           // remove script wrapper when done
//           document.currentScript.parentNode.removeChild(document.currentScript);
//       };
//       document.getElementsByTagName('head')[0].appendChild(link);

// })();






// printed in function sek_customizr_js_stuff()
// global sekFrontLocalized, nimbleListenTo
(function(w, d){
      nb_.listenTo( 'nb-app-ready', function() {
          //PREVIEWED DEVICE ?
          //Listen to the customizer previewed device
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
      });// onJQueryReady
}(window, document));