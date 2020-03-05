// global sekFrontLocalized, fireOnNimbleAppReady
(function(w, d){
      var onNimbleAppReady = function() {
          jQuery(function($){
              /* ------------------------------------------------------------------------- *
               *  LIGHT BOX WITH MAGNIFIC POPUP
              /* ------------------------------------------------------------------------- */
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;

              // fire the fn when we know that it's been loaded
              // either enqueued with possible defer or injected with js
              window.fireOnMagnificPopupReady(
                  function() {
                      // $candidates are .sek-link-to-img-lightbox selectors
                      $linkCandidates.each( function() {
                          $linkCandidate = $(this);
                          // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
                          if ( $linkCandidate.length < 1 || 'string' !== typeof( $linkCandidate[0].protocol ) || -1 !== $linkCandidate[0].protocol.indexOf('javascript') )
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
                                nb_.errorLog( 'error with fireOnMagnificPopupReady => ', er );
                          }
                      });
                  }
              );//nb_.fireOnMagnificPopupReady


              // Load js plugin if needed
              // when the plugin is loaded => it emits 'nimble-magnific-popup-ready' listened to by nb_.fireOnMagnificPopupReady()
              if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_front_assets_on_scroll ) {

                    var _scrollHandle = function() {},//abstract that we can unbind
                        doLoad = function() {
                          // I've been executed forget about me
                          nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                          // Bail if the load request has already been made, but not yet finished.
                          if ( nb_.scriptsLoadingStatus.czrMagnificPopup && 'pending' === nb_.scriptsLoadingStatus.czrMagnificPopup.state() ) {
                              return;
                          }

                          // set the script loading status now to avoid several calls
                          nb_.scriptsLoadingStatus.czrMagnificPopup = nb_.scriptsLoadingStatus.czrMagnificPopup || $.Deferred();

                          //Load the style
                          if ( $('head').find( '#czr-magnific-popup' ).length < 1 ) {
                                $('head').append( $('<link/>' , {
                                      rel : 'stylesheet',
                                      id : 'czr-magnific-popup',
                                      type : 'text/css',
                                      href : sekFrontLocalized.frontAssetsPath + 'css/libs/magnific-popup.min.css?' + sekFrontLocalized.assetVersion
                                }) );
                          }

                          $.ajax( {
                                url : sekFrontLocalized.frontAssetsPath + 'js/libs/jquery-magnific-popup.min.js?' + sekFrontLocalized.assetVersion,
                                cache : true,// use the browser cached version when available
                                dataType: "script"
                          }).done(function() {
                                if ( 'function' != typeof( $.fn.magnificPopup ) )
                                  return;
                                //the script is loaded. Say it globally.
                                nb_.scriptsLoadingStatus.czrMagnificPopup.resolve();
                          }).fail( function() {
                                nb_.errorLog('Magnific popup instantiation failed');
                          });
                    };// doLoad

                    // Fire now or schedule when becoming visible.
                    var isLoading = false;
                    $.each( $linkCandidates, function( k, el ) {
                        if ( !isLoading && nb_.isInWindow($(el) ) ) {
                            isLoading = true;
                            doLoad();
                        }
                    });
                    if ( !isLoading ) {
                          _scrollHandle = nb_.throttle( function() {
                                $.each( $linkCandidates, function( k, el ) {
                                    if ( !isLoading && nb_.isInWindow( $(el) ) ) {
                                        isLoading = true;
                                        doLoad();
                                    }
                                });
                          }, 100 );
                          nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
                    }
              } // if( sekFrontLocalized.load_front_assets_on_scroll )

        });//jQuery(function($){})


        /* ------------------------------------------------------------------------- *
         *  SMARTLOAD
        /* ------------------------------------------------------------------------- */
        jQuery(function($){
              $('.sektion-wrapper').each( function() {
                    try { $(this).nimbleLazyLoad(); } catch( er ) {
                          nb_.errorLog( 'error with nimbleLazyLoad => ', er );
                    }
              });
        });


        /* ------------------------------------------------------------------------- *
         *  BG PARALLAX
        /* ------------------------------------------------------------------------- */
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


        /* ------------------------------------------------------------------------- *
         *  SCROLL TO ANCHOR
        /* ------------------------------------------------------------------------- */
        jQuery( function($){
            // does the same as new URL(url)
            // but support IE.
            // @see https://stackoverflow.com/questions/736513/how-do-i-parse-a-url-into-hostname-and-path-in-javascript
            // @see https://gist.github.com/acdcjunior/9820040
            // @see https://developer.mozilla.org/en-US/docs/Web/API/URL#Properties
            var parseURL = function(url) {
                  var parser = document.createElement("a");
                  parser.href = url;
                  // IE 8 and 9 dont load the attributes "protocol" and "host" in case the source URL
                  // is just a pathname, that is, "/example" and not "http://domain.com/example".
                  parser.href = parser.href;

                  // copies all the properties to this object
                  var properties = ['host', 'hostname', 'hash', 'href', 'port', 'protocol', 'search'];
                  for (var i = 0, n = properties.length; i < n; i++) {
                    this[properties[i]] = parser[properties[i]];
                  }

                  // pathname is special because IE takes the "/" of the starting of pathname
                  this.pathname = (parser.pathname.charAt(0) !== "/" ? "/" : "") + parser.pathname;
            };

            var $root = $('html, body');
            var maybeScrollToAnchor = function( evt ){
                  // problem to solve : users want to define anchor links that work inside a page, but also from other pages.
                  // @see https://github.com/presscustomizr/nimble-builder/issues/413
                  var clickedItemUrl = $(this).attr('href');
                  if ( '' === clickedItemUrl || null === clickedItemUrl || 'string' !== typeof( clickedItemUrl ) || -1 === clickedItemUrl.indexOf('#') )
                    return;

                  // an anchor link looks like this : http://mysite.com/contact/#anchor
                  var itemURLObject = new parseURL( clickedItemUrl ),
                      _currentPageUrl = new parseURL( window.document.location.href );

                  if( itemURLObject.pathname !== _currentPageUrl.pathname )
                    return;
                  if( 'string' !== typeof(itemURLObject.hash) || '' === itemURLObject.hash )
                    return;
                  var $nimbleTargetCandidate = $('[data-sek-level="location"]' ).find( '[id="' + itemURLObject.hash.replace('#','') + '"]');
                  if ( 1 !== $nimbleTargetCandidate.length )
                    return;

                  evt.preventDefault();
                  $root.animate({ scrollTop : $nimbleTargetCandidate.offset().top - 150 }, 400 );
            };

            // animate menu item to Nimble anchors
            nb_.cachedElements.$body.find('.menu-item' ).on( 'click', 'a', maybeScrollToAnchor );

            // animate an anchor link inside Nimble sections
            // fixes https://github.com/presscustomizr/nimble-builder/issues/443
            $('[data-sek-level="location"]' ).on( 'click', 'a', maybeScrollToAnchor );
        });
    };/////////////// onNimbleAppReady

    window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));






/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD SWIPER ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var carouselTmplReadyCb = function() {
        jQuery(function($){

            if ( !sekFrontLocalized.load_front_assets_on_scroll )
              return;

            var $swiperCandidates = $('[data-sek-module-type="czr_img_slider_module"]');
            if ( $swiperCandidates.length < 1 )
              return;


            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-swiper-ready' listened to by nb_.fireOnSwiperReady()
            if ( nb_.scriptsLoadingStatus.swiper && 'resolved' === nb_.scriptsLoadingStatus.swiper.state() )
              return;
            var _scrollHandle = function() {},//abstract that we can unbind
                doLoad = function() {
                  // I've been executed forget about me
                  // so we execute the callback only once
                  nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                  // Bail if the load request has already been made, but not yet finished.
                  if ( nb_.scriptsLoadingStatus.swiper && 'pending' === nb_.scriptsLoadingStatus.swiper.state() ) {
                    return;
                  }

                  // set the script loading status now to avoid several calls
                  nb_.scriptsLoadingStatus.swiper = nb_.scriptsLoadingStatus.swiper || $.Deferred();

                  //Load the style
                  if ( $('head').find( '#czr-swiper' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'czr-swiper',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/swiper.min.css?'+sekFrontLocalized.assetVersion
                        }) );
                  }

                  $.ajax( {
                        url : sekFrontLocalized.frontAssetsPath + 'js/libs/swiper.min.js?'+sekFrontLocalized.assetVersion,
                        cache : true,// use the browser cached version when available
                        dataType: "script"
                  }).done(function() {
                        if ( 'function' != typeof( window.Swiper ) )
                          return;

                        $.ajax( {
                              url : sekFrontLocalized.frontAssetsPath + 'js/prod-front-simple-slider-module.min.js?'+sekFrontLocalized.assetVersion,
                              cache : true,// use the browser cached version when available
                              dataType: "script"
                        }).done(function() {
                              //the script is loaded. Say it globally.
                              nb_.scriptsLoadingStatus.swiper.resolve();
                        }).fail( function() {
                              nb_.errorLog('Swiper instantiation failed');
                        });
                  }).fail( function() {
                        nb_.errorLog('Swiper instantiation failed');
                  });
            };// doLoad

            // Fire now or schedule when becoming visible.
            var isLoading = false;
            $.each( $swiperCandidates, function( k, el ) {
                if ( !isLoading && nb_.isInWindow($(el) ) ) {
                    isLoading = true;
                    doLoad();
                }
            });
            if ( !isLoading ) {
                  _scrollHandle = nb_.throttle( function() {
                        $.each( $swiperCandidates, function( k, el ) {
                            if ( !isLoading && nb_.isInWindow( $(el) ) ) {
                                isLoading = true;
                                doLoad();
                            }
                        });
                  }, 100 );
                  nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
            }
        });//jQuery(function($){})
    };/////////////// onNimbleAppReady
    window.fireOnCarouselTmplRendered( carouselTmplReadyCb );
}(window, document));


/* ------------------------------------------------------------------------- *
 *  MAYBE LOAD FONTAWESOME ON SCROLL
/* ------------------------------------------------------------------------- */
(function(w, d){
    var onNimbleAppReady = function() {
        jQuery(function($){
            // we don't need to inject font awesome if already enqueued by a theme
            if ( !sekFrontLocalized.load_front_assets_on_scroll || sekFrontLocalized.fontAwesomeAlreadyEnqueued )
              return;

            var modulesPrintedOnPage = sekFrontLocalized.contextuallyActiveModules,
                modulesFADependant = sekFrontLocalized.modulesFontAwesomeDependant,
                fontAwesomeCandidates = [],
                $fontAwesomeCandidates;

            $.each( modulesFADependant, function( key, moduleType ) {
                if ( !nb_.isUndefined( modulesPrintedOnPage[moduleType] ) ) {
                    var _candidate = '[data-sek-module-type="'+ moduleType +'"]';
                    if ( $(_candidate).length > 0 ) {
                        fontAwesomeCandidates.push( _candidate );
                    }
                }
            });
            $fontAwesomeCandidates = $( fontAwesomeCandidates.join(',') );

            if ( $fontAwesomeCandidates.length < 1 )
              return;

            // Load js plugin if needed
            // when the plugin is loaded => it emits 'nimble-swiper-ready' listened to by nb_.fireOnSwiperReady()
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
    };/////////////// onNimbleAppReady
    window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));