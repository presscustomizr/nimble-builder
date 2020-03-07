// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
 /* ------------------------------------------------------------------------- */
(function(w, d){
      var callbackFunc = function() {
          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;
              var _scrollHandle = function() {},//abstract that we can unbind
                  doLoad = function() {
                    // I've been executed forget about me
                    nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

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
            // when the plugin is loaded => it emits 'nimble-magnific-popup-loaded' listened to by window.nb_.listenTo()
            if ( sekFrontLocalized.load_front_partial_css_on_scroll || sekFrontLocalized.load_front_module_js_on_scroll ) {
                nb_.maybeLoadAssetsWhenSelectorInScreen( {
                    elements : $linkCandidates,
                    func : doLoad
                });
            }

        });//jQuery(function($){})
    };/////////////// callbackFunc
    // When loaded with defer, we can not be sure that jQuery will be loaded before
    window.nb_.listenTo( 'nimble-magnific-popup-dependant', function() {
        window.nb_.listenTo('nimble-app-ready', callbackFunc );
    });

}(window, document));

(function(w, d){
      var callbackFunc = function() {
          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;

              $linkCandidates.each( function() {
                  $linkCandidate = $(this);
                  // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
                  if ( $linkCandidate.length < 1 || 'string' !== typeof( $linkCandidate[0].protocol ) || -1 !== $linkCandidate[0].protocol.indexOf('javascript') )
                    return;
                  // Abort if candidate already setup
                  if ( true === $linkCandidate.data('nimble-mfp-done') )
                    return;

                  try { $linkCandidate.magnificPopup({
                      type: 'image',
                      closeOnContentClick: true,
                      closeBtnInside: true,
                      fixedContentPos: true,
                      mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
                      image: {
                        verticalFit: true
                      },
                      zoom: {
                        enabled: true,
                        duration: 300 // don't foget to change the duration also in CSS
                      }
                  }); } catch( er ) {
                        nb_.errorLog( 'error in callback of nimble-magnific-popup-loaded => ', er );
                  }
                  $linkCandidate.data('nimble-mfp-done', true );
              });
          });//jQuery(function($){})
    };/////////////// callbackFunc

    window.nb_.listenTo('nimble-magnific-popup-loaded', callbackFunc );
}(window, document));










/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){

            // Load js plugin if needed
            // // when the plugin is loaded => it emits 'nimble-swiper-ready' listened to by window.nb_.listenTo()
            // if ( nb_.scriptsLoadingStatus.swiper && 'resolved' === nb_.scriptsLoadingStatus.swiper.state() )
            //   return;
            var _scrollHandle = function() {},//abstract that we can unbind
                doLoad = function() {
                  // I've been executed forget about me
                  // so we execute the callback only once
                  nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

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
                elements : $swiperCandidates,
                func : doLoad
            });
        });//jQuery(function($){})
    };/////////////// callbackFunc

    // When loaded with defer, we can not be sure that jQuery will be loaded before
    // on 'nimble-app-ready', jQuery is loaded
    window.nb_.listenTo( 'nimble-swiper-dependant', function() {
        window.nb_.listenTo('nimble-app-ready', callbackFunc );
    });
}(window, document));






/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
(function(w, d){
    window.nb_.listenTo('nimble-lazyload-ready', function() {
        jQuery(function($){
              $('.sektion-wrapper').each( function() {
                    try { $(this).nimbleLazyLoad(); } catch( er ) {
                          nb_.errorLog( 'error with nimbleLazyLoad => ', er );
                    }
              });
        });
    });

    window.nb_.listenTo('nimble-jquery-loaded', function() {
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
            // when the plugin is loaded => it emits "nimble-fa-dependant" listened to by window.nb_.listenTo()
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
    window.nb_.listenTo( 'nimble-fa-dependant', function() {
        window.nb_.listenTo( 'nimble-app-ready', callbackFunc );
    });
}(window, document));










/* ------------------------------------------------------------------------- *
 *  BG PARALLAX
/* ------------------------------------------------------------------------- */
(function(w, d){
    window.nb_.listenTo('nimble-parallax-ready', function() {
        jQuery(function($){
              $('[data-sek-bg-parallax="true"]').each( function() {
                    $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
              });
              var _setParallaxWhenCustomizing = function() {
                    $(this).parallaxBg( { parallaxForce : $(this).data('sek-parallax-force') } );
                    // hack => always trigger a 'resize' event with a small delay to make sure bg positions are ok
                    setTimeout( function() {
                         nb_.cachedElements.$body.trigger('resize');
                    }, 500 );
              };
              // When previewing, react to level refresh
              // This can occur to any level. We listen to the bubbling event on 'body' tag
              // and salmon up to maybe instantiate any missing candidate
              // Example : when a preset_section is injected
              nb_.cachedElements.$body.on('sek-level-refreshed sek-section-added', function( evt ){
                    if ( "true" === $(this).data('sek-bg-parallax') ) {
                          _setParallaxWhenCustomizing.call(this);
                    } else {
                          $(this).find('[data-sek-bg-parallax="true"]').each( function() {
                                _setParallaxWhenCustomizing.call(this);
                          });
                    }
              });
        });
    });
}(window, document));
