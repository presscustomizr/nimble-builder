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
            // store it, so if the event has been emitted before the listener is fired, we know it's been emitted
            nb_.eventsListenedTo.push(evt);

            var canWeFireCallbackForEvent = {
                'nb-jquery-loaded' : function() { return typeof undefined !== typeof jQuery; },
                'nb-app-ready' : function() { return ( typeof undefined !== typeof window.nb_ ) && nb_.wasListenedTo('nb-jquery-loaded'); },
                'nb-swipebox-parsed' : function() { return ( typeof undefined !== typeof jQuery ) && ( typeof undefined !== typeof jQuery.fn.swipebox ); },
                'nb-main-swiper-parsed' : function() { return typeof undefined !== typeof window.Swiper; }
            };
            // e is the event object passed
            // it is possible to add params but we need to use new CustomEvent with a polyfill for IE
            // see : https://stackoverflow.com/questions/18613456/trigger-event-with-parameters
            var _executeAndLog = function(e) {
                if ( !nb_.isUndefined(canWeFireCallbackForEvent[evt]) && false === canWeFireCallbackForEvent[evt]() ) {
                    nb_.errorLog('Nimble error => an event callback could not be fired because conditions not met => ', evt, nb_.eventsListenedTo, func );
                    return;
                }
                func();
                // // store it, so if the event has been emitted before the listener is fired, we know it's been emitted
                // nb_.eventsListenedTo.push(evt);
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
        // params = {
        // id : 'nb-animate-css',
        // as : 'style',
        // href : "",
        // onEvent : 'nb-docready',
        // scriptEl : document.currentScript,
        // eventOnLoad : 'animate-css-loaded'
        // }
        // About preloading : rel="preload" tells the browser to start loading an important assets in priority
        // example :
        // - load late-discovered resources early
        // - early loading of fonts
        // NB asset strategy :
        // - use rel="preload" for webfonts like Font Awesome ( stylesheet + fonts )
        // - use defer attribute for all javascript files( see https://flaviocopes.com/javascript-async-defer/ ) "The best thing to do to speed up your page loading when using scripts is to put them in the head, and add a defer attribute to your script tag:"
        // see https://www.smashingmagazine.com/2016/02/preload-what-is-it-good-for/
        preloadOrDeferAsset : function(params) {
            params = params || {};
            // bail if preloaded already ?
            nb_.preloadedAssets = nb_.preloadedAssets || [];
            if ( nb_.inArray( nb_.preloadedAssets, params.id ) )
              return;

            var headTag = document.getElementsByTagName('head')[0],
                link,
                _injectFinalAsset = function() {
                    var link = this;
                    // this is the link element
                    if ( 'style' === params.as ) {
                       link.setAttribute('rel', 'stylesheet');
                       link.setAttribute('type', 'text/css');
                       link.setAttribute('media', 'all');
                    } else {
                        var _script = document.createElement("script");
                        _script.setAttribute('src', params.href );
                        _script.setAttribute('id', params.id );
                        if ( 'script' === params.as ) {
                            _script.setAttribute('defer', 'defer');
                        }
                        headTag.appendChild(_script);
                        // clean the loader link
                        _maybeRemoveScriptEl.call(link);
                    }
                    if ( params.eventOnLoad ) {
                        nb_.emit( params.eventOnLoad );
                    }
                },
                _maybeRemoveScriptEl = function() {
                    var _el = this;
                    if ( _el && _el.parentNode && _el.parentNode.contains(_el) ) {
                        try{_el.parentNode.removeChild(_el);} catch(er) {
                            nb_.errorLog('NB error when removing a script el', el);
                        }
                    }
                };

            // terminate here in the case of a font preload when preload not supported
            if ( 'font' === params.as && !nb_.hasPreloadSupport() )
              return;

            link = document.createElement('link');

            // script without preload support
            if ( 'script' === params.as ) {
                if ( params.onEvent ) {
                    nb_.listenTo( params.onEvent, function() { _injectFinalAsset.call(link); });
                } else {
                    _injectFinalAsset.call(link);
                }
            } else {
                // script, font and stylesheet
                link.setAttribute('href', params.href);
                if ( 'style' === params.as ) {
                    link.setAttribute('rel', nb_.hasPreloadSupport() ? 'preload' : 'stylesheet' );
                } else if ( 'font' === params.as && nb_.hasPreloadSupport() ) {
                    link.setAttribute('rel', 'preload' );
                }
                link.setAttribute('id', params.id );
                link.setAttribute('as', params.as);

                // attributes specific to fonts
                if ( 'font' === params.as ) {
                    link.setAttribute('type', params.type);
                    link.setAttribute('crossorigin', 'anonymous');
                }

                // watch load events
                link.onload = function() {
                    this.onload=null;
                    // if this is a font, let's only check if an event is scheduled on load
                    if ( 'font' === params.as ) {
                        if ( params.eventOnLoad ) {
                            nb_.emit( params.eventOnLoad );
                        }
                        // nothing left to do if this is a font. It can now be used by the stylesheet
                        return;
                    }

                    if ( params.onEvent ) {
                        nb_.listenTo( params.onEvent, function() { _injectFinalAsset.call(link); });
                    } else {
                        _injectFinalAsset.call(link);
                    }
                };
                link.onerror = function(er) {
                    nb_.errorLog('Nimble preloadOrDeferAsset error', er, params );
                };
            }
            // append link now
            headTag.appendChild(link);

            // store the asset as done
            nb_.preloadedAssets.push( params.id );

            // clean the script element from which preload has been requested
            _maybeRemoveScriptEl.call(params.scriptEl);
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


// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
// jQuery can potentially be loaded async, so let's react to its load or the presence of window.jQuery
// This relies on the fact that we use add_filter( 'script_loader_tag', array( $this, 'sek_filter_script_loader_tag' ), 10, 2 ); to add id 'nb-jquery'
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
            if ( window.console && window.console.log ) {
                console.log('Nimble Builder problem : jQuery.js was not detected on your website');
            }
        }
    };
    // if jQuery has already be printed, let's listen to the load event
    var jquery_script_el = document.getElementById('nb-jquery');
    if ( jquery_script_el ) {
        jquery_script_el.addEventListener('load', function() {
            _maybeEmit();
        });
    }
    _emitWhenJqueryIsReady();
})();



//printed in sek_maybe_load_scripts_in_ajax()
(function(w, d){
    nb_.listenTo( 'nb-jquery-loaded', function() {
        if ( !sekFrontLocalized.load_front_assets_on_dynamically )
            return;
        // params = {
        //  path : 'js/libs/swiper-bundle.min.js'
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
    });/////////////// callbackFunc

    nb_.listenTo('nb-jquery-loaded', function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
                return;
            // Main script
            nb_.ajaxLoadScript({ path : sekFrontLocalized.isDevMode ? 'js/ccat-nimble-front.js' : 'js/ccat-nimble-front.min.js'});
    
            // Partial scripts
            $.each( sekFrontLocalized.partialFrontScripts, function( _name, _event ){
                nb_.listenTo( _event, function() {
                    nb_.ajaxLoadScript({ path : sekFrontLocalized.isDevMode ? 'js/partials/' + _name + '.js' : 'js/partials/' + _name + '.min.js'});
                });
            });
    
        });
    });
}(window, document));


