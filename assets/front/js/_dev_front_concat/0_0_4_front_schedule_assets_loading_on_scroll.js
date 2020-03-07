// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  LOAD MAGNIFIC POPUP
 /* ------------------------------------------------------------------------- */
(function(w, d){
      var callbackFunc = function() {
          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;
              var doLoad = function() {
                    if ( sekFrontLocalized.load_front_partial_css_on_scroll ) {
                        //Load the style
                        if ( $('head').find( '#czr-magnific-popup' ).length < 1 ) {
                              $('head').append( $('<link/>' , {
                                    rel : 'stylesheet',
                                    id : 'czr-magnific-popup',
                                    type : 'text/css',
                                    href : sekFrontLocalized.frontAssetsPath + 'css/libs/magnific-popup.min.css?' + sekFrontLocalized.assetVersion
                              }) );
                        }
                    }

                    if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_front_module_js_on_scroll ) {
                          nb_.ajaxLoadScript({
                              path : 'js/libs/jquery-magnific-popup.min.js',
                              loadcheck : function() { return nb_.isFunction( $.fn.magnificPopup ); }
                          });
                    }
                };// doLoad

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-magnific-popup-loaded' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'magnific-popup',
                elements : $linkCandidates,
                func : doLoad
            });

        });//jQuery(function($){})
    };/////////////// callbackFunc
    // When loaded with defer, we can not be sure that jQuery will be loaded before
    nb_.listenTo( 'nimble-app-ready', function() {
        console.log('SO??');
        nb_.listenTo( 'nimble-needs-magnific-popup', callbackFunc );
    });

}(window, document));






/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){

            // Load js plugin if needed
            // // when the plugin is loaded => it emits 'nimble-swiper-ready' listened to by nb_.listenTo()
            // if ( nb_.scriptsLoadingStatus.swiper && 'resolved' === nb_.scriptsLoadingStatus.swiper.state() )
            //   return;
            var doLoad = function() {
                  //Load the style
                  if ( sekFrontLocalized.load_front_partial_css_on_scroll ) {
                      if ( $('head').find( '#czr-swiper' ).length < 1 ) {
                            $('head').append( $('<link/>' , {
                                  rel : 'stylesheet',
                                  id : 'czr-swiper',
                                  type : 'text/css',
                                  href : sekFrontLocalized.frontAssetsPath + 'css/libs/swiper.min.css?'+sekFrontLocalized.assetVersion
                            }) );
                      }
                  }
                  if ( sekFrontLocalized.load_front_module_js_on_scroll ) {
                      nb_.ajaxLoadScript({
                          path : 'js/libs/swiper.min.js',
                          loadcheck : function() { return nb_.isFunction( window.Swiper ); },
                          complete : function() {
                              nb_.ajaxLoadScript({
                                  path : 'js/prod-front-simple-slider-module.min.js',
                              });
                          }
                      });
                  }
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
    // on 'nimble-app-ready', jQuery is loaded
    nb_.listenTo( 'nimble-app-ready', function() {
        nb_.listenTo('nimble-needs-swiper', callbackFunc );
    });
}(window, document));







/* ------------------------------------------------------------------------- *
 *  LOAD SMARTLOAD JQUERY PLUGIN
/* ------------------------------------------------------------------------- */
(function(w, d){
    nb_.listenTo('nimble-jquery-loaded', function() {
        if ( !sekFrontLocalized.load_front_module_js_on_scroll )
            return;
        jQuery(function($){
              nb_.ajaxLoadScript({
                  path : 'js/libs/nimble-smartload.min.js',
                  loadcheck : function() { return nb_.isFunction( $.fn.nimbleLazyLoad ); }
              });
        });
    });
}(window, document));








/* ------------------------------------------------------------------------- *
 *  LOAD PARALLAX BG JQUERY PLUGIN
/* ------------------------------------------------------------------------- */
(function(w, d){
    nb_.listenTo('nimble-app-ready', function() {
        jQuery(function($){
            // is it loaded already ?
            if ( nb_.isFunction( $.fn.parallaxBg ) )
              return;
            var $parallaxBGCandidates = $('[data-sek-bg-parallax="true"]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $parallaxBGCandidates.length < 1 )
              return;
            var doLoad = function() {
                if ( !nb_.isFunction( $.fn.parallaxBg ) && sekFrontLocalized.load_front_module_js_on_scroll ) {
                    nb_.ajaxLoadScript({
                        path : 'js/libs/nimble-parallax-bg.min.js',
                        loadcheck : function() { return nb_.isFunction( $.fn.parallaxBg ); }
                    });
                }
            };// doLoad

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-magnific-popup-loaded' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'parallax-bg',
                elements : $parallaxBGCandidates,
                func : doLoad
            });
        });// jQuery
    });/////////////// callbackFunc
}(window, document));



/* ------------------------------------------------------------------------- *
 *  LOAD ACCORDION JS JQUERY DEPENDANT
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            var $candidates = $('[data-sek-module-type="czr_accordion_module"]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $candidates.length < 1 )
              return;
            var doLoad = function() {
                //Load js
                nb_.ajaxLoadScript({
                    path : 'js/prod-front-accordion-module.js'
                });
            };// doLoad

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-magnific-popup-loaded' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'accordion',
                elements : $candidates,
                func : doLoad
            });
        });// jQuery
    };/////////////// callbackFunc
    nb_.listenTo('nimble-app-ready', function() {
        nb_.listenTo('nimble-needs-accordion', callbackFunc );
    });
}(window, document));








/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD FONTAWESOME ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            // we don't need to inject font awesome if already enqueued by a theme
            if ( !sekFrontLocalized.load_front_assets_on_scroll || sekFrontLocalized.fontAwesomeAlreadyEnqueued )
              return;

            var modulesPrintedOnPage = sekFrontLocalized.contextuallyActiveModules,
                fontAwesomeCandidates = [],
                $fontAwesomeCandidates;

            // $.each( modulesFADependant, function( key, moduleType ) {
            //     if ( !nb_.isUndefined( modulesPrintedOnPage[moduleType] ) ) {
            //         var _candidate = '[data-sek-module-type="'+ moduleType +'"]';
            //         if ( $(_candidate).length > 0 ) {
            //             fontAwesomeCandidates.push( _candidate );
            //         }
            //     }
            // });
            $fontAwesomeCandidates = $('i[class*=fa-]');

            if ( $fontAwesomeCandidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits "nimble-needs-fontawesome" listened to by nb_.listenTo()
            var _scrollHandle = function() {},//abstract that we can unbind
                doLoad = function() {
                  // I've been executed forget about me
                  // so we execute the callback only once
                  nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                  //Load the style
                  if ( $('head').find( '#czr-font-awesome' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'czr-font-awesome',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'fonts/css/fontawesome-all.min.css?'+sekFrontLocalized.assetVersion
                        }) );
                  }
            };// doLoad
            var isLoading = false;
            // Fire now or schedule when becoming visible.
            $.each( $fontAwesomeCandidates, function( k, el ) {
                if ( !isLoading && nb_.isInWindow($(el) ) ) {
                    isLoading = true;
                    doLoad();
                }
            });
            if ( !isLoading ) {
                  _scrollHandle = nb_.throttle( function() {
                        $.each( $fontAwesomeCandidates, function( k, el ) {
                            if ( !isLoading && nb_.isInWindow( $(el) ) ) {
                                isLoading = true;
                                doLoad();
                            }
                        });
                  }, 100 );
                  nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
            }
        });//jQuery(function($){})
    };/////////////// callbackFunc

    // When loaded with defer, we can not be sure that jQuery will be loaded before
    //  on 'nimble-app-ready', jQuery is loaded
    nb_.listenTo( 'nimble-app-ready', function() {
        nb_.listenTo( 'nimble-needs-fontawesome', callbackFunc );
    });
}(window, document));