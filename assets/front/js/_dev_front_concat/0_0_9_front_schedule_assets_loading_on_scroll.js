// global sekFrontLocalized, nimbleListenTo, nb_
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
                var handlerParams = { elements : params.elements, func : params.func, force_loading : params.force_loading };
                nb_.scrollHandlers[params.id] = handlerParams;
                nb_.emit('nimble-new-scroll-handler-added', { fire_once : false } );
            };
            nb_.emit('nimble-ready-to-load-assets-on-scroll');
        });//jQuery(function($){})
    });//'nb-app-ready'
}(window, document));





/* ------------------------------------------------------------------------- *
 *  LOAD SWIPEBOX
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
                return;

            var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
            $linkCandidates = $linkCandidates.add($('[data-sek-level="module"]').find('.sek-gal-link-to-img-lightbox'));
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $linkCandidates.length < 1 )
              return;
            var doLoad = function() {
                  //Load the style
                  if ( $('head').find( '#nb-swipebox' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'nb-swipebox',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/swipebox.min.css?' + sekFrontLocalized.assetVersion
                        }) );
                  }

                  if ( !nb_.isFunction( $.fn.swipebox ) && sekFrontLocalized.load_front_assets_on_dynamically ) {
                        nb_.ajaxLoadScript({
                            path : 'js/libs/jquery-swipebox.min.js',
                            loadcheck : function() { return nb_.isFunction( $.fn.swipebox ); }
                        });
                  }
              };// doLoad

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-swipebox-parsed' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'swipebox',
                elements : $linkCandidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    //When loaded with defer, we can not be sure that jQuery will be loaded before
    nb_.listenTo( 'nb-app-ready', function() {
        nb_.listenTo( 'nb-needs-swipebox', callbackFunc );
    });
}(window, document));



/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
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
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/swiper-bundle.min.css?'+sekFrontLocalized.assetVersion
                        }) );
                  }
                  nb_.ajaxLoadScript({
                      path : 'js/libs/swiper-bundle.min.js?'+sekFrontLocalized.assetVersion,
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
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
              return;
            var $candidates = $('[data-sek-video-bg-src]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nb-..-parsed' listened to by nb_.listenTo()
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
            if ( !sekFrontLocalized.load_front_assets_on_dynamically )
              return;
            var $candidates = $('i[class*=fa-]');

            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits "nb-needs-fa" listened to by nb_.listenTo()
            var doLoad = function() {
                  //Load the style
                  if ( $('head').find( '#nb-font-awesome' ).length < 1 ) {
                        var link = document.createElement('link');
                        link.setAttribute('href', sekFrontLocalized.frontAssetsPath + 'fonts/css/fontawesome-all.min.css?'+sekFrontLocalized.assetVersion );
                        link.setAttribute('id', 'nb-font-awesome');
                        link.setAttribute('data-sek-injected-dynamically', 'yes');
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
            // when the plugin is loaded => it emits 'nb-...-parsed' listened to by nb_.listenTo()
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
}(window, document));