// global sekFrontLocalized, nimbleListenTo, nb_
/* ------------------------------------------------------------------------- *
 *  LOAD MAGNIFIC POPUP
 /* ------------------------------------------------------------------------- */
(function(w, d){
      // params = {
      //  elements : $swiperCandidate,
      //  func : function() {}
      // }
      nb_.maybeLoadAssetsWhenSelectorInScreen = function( params ) {
          jQuery(function($){
              // do nothing if dynamic asset loading is not enabled for js and css
              if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
                return;

              params = $.extend( { id : '', elements : '', func : '' }, params );

              if ( 1 > params.id.length ) {
                  nb_.errorLog('Nimble error => maybeLoadAssetsWhenSelectorInScreen => missing id', params );
                return;
              }
              if ( 1 > $(params.elements).length )
                return;
              if ( !nb_.isFunction( params.func ) )
                return;
              nb_.scrollHandlers = nb_.scrollHandlers || {};
              var handlerParams = { elements : params.elements, func : params.func };
              nb_.scrollHandlers[params.id] = handlerParams;
              nb_.emit('nimble-new-scroll-handler-added', handlerParams);
          });//jQuery(function($){})
      };

      var callbackFunc = function() {
          if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
              return;

          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;
              var doLoad = function() {
                    //Load the style
                    if ( $('head').find( '#czr-magnific-popup' ).length < 1 ) {
                          $('head').append( $('<link/>' , {
                                rel : 'stylesheet',
                                id : 'czr-magnific-popup',
                                type : 'text/css',
                                href : sekFrontLocalized.frontAssetsPath + 'css/libs/magnific-popup.min.css?' + sekFrontLocalized.assetVersion
                          }) );
                    }

                    if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_front_module_assets_on_scroll ) {
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
        nb_.listenTo( 'nb-needs-magnific-popup', callbackFunc );
    });

}(window, document));






/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
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
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/swiper.min.css?'+sekFrontLocalized.assetVersion
                        }) );
                  }
                  if ( sekFrontLocalized.load_front_module_assets_on_scroll ) {
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
        nb_.listenTo('nb-needs-swiper', callbackFunc );
    });
}(window, document));



/* ------------------------------------------------------------------------- *
 *  LOAD MENU MODULE JS
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            if ( !sekFrontLocalized.load_front_module_assets_on_scroll )
              return;
            var $candidates = $('[data-sek-module-type="czr_menu_module"]');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-magnific-popup-loaded' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen( {
                id : 'menu',
                elements : $candidates,
                func : function() {
                    //Load js
                    nb_.ajaxLoadScript({
                        path : 'js/prod-front-menu-module.min.js'
                    });
                }// doLoad
            });
        });// jQuery
    };/////////////// callbackFunc
    nb_.listenTo('nimble-app-ready', function() {
        nb_.listenTo('nb-needs-menu-js', callbackFunc );
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

            var $candidates = $('i[class*=fa-]');

            if ( $candidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits "nb-needs-fontawesome" listened to by nb_.listenTo()
            var doLoad = function() {
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
            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-magnific-popup-loaded' listened to by nb_.listenTo()
            nb_.maybeLoadAssetsWhenSelectorInScreen({
                id : 'font-awesome',
                elements : $candidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    // When loaded with defer, we can not be sure that jQuery will be loaded before
    //  on 'nimble-app-ready', jQuery is loaded
    nb_.listenTo( 'nimble-app-ready', function() {
        nb_.listenTo( 'nb-needs-fontawesome', callbackFunc );
    });
}(window, document));