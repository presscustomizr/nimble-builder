// global sekFrontLocalized, fireOnNimbleAppReady
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
/* ------------------------------------------------------------------------- */
(function(w, d){
      var onNimbleAppReady = function() {
          jQuery(function($){
              var $linkCandidates = $('[data-sek-module-type="czr_image_module"]').find('.sek-link-to-img-lightbox');
              // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
              if ( $linkCandidates.length < 1 )
                return;

              // fire the fn when we know that it's been loaded
              // either enqueued with possible defer or injected with js
              nb_.fireOnMagnificPopupReady(
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

              //Load the style
              if ( sekFrontLocalized.load_js_on_scroll ) {
                  if ( $('head').find( '#czr-magnific-popup' ).length < 1 ) {
                        $('head').append( $('<link/>' , {
                              rel : 'stylesheet',
                              id : 'czr-magnific-popup',
                              type : 'text/css',
                              href : sekFrontLocalized.frontAssetsPath + 'css/libs/magnific-popup.min.css'
                        }) );
                  }
              }

              // Load js plugin if needed
              // when the plugin is loaded => it emits 'nimble-magnific-popup-ready' listened to by nb_.fireOnMagnificPopupReady()
              if ( !nb_.isFunction( $.fn.magnificPopup ) && sekFrontLocalized.load_js_on_scroll ) {
                    var _scrollHandle = function() {},//abstract that we can unbind
                        doLoadMagnificPopup = function() {
                          // I've been executed forget about me
                          nb_.cachedElements.$window.unbind( 'scroll', _scrollHandle );

                          // Check if the load request has already been made, but not yet finished.
                          if ( nb_.scriptsLoadingStatus.czrMagnificPopup && 'pending' === nb_.scriptsLoadingStatus.czrMagnificPopup.state() ) {
                                nb_.scriptsLoadingStatus.czrMagnificPopup.done( function() {
                                      _doMagnificPopupWhenScriptAndStyleLoaded($linkCandidates);
                                });
                                return;
                          }

                          // set the script loading status now to avoid several calls
                          nb_.scriptsLoadingStatus.czrMagnificPopup = nb_.scriptsLoadingStatus.czrMagnificPopup || $.Deferred();

                          $.ajax( {
                                url : sekFrontLocalized.frontAssetsPath + 'js/libs/jquery-magnific-popup.min.js',
                                cache : true,// use the browser cached version when available
                                dataType: "script"
                          }).done(function() {
                                if ( 'function' != typeof( $.fn.magnificPopup ) )
                                  return;
                                //the script is loaded. Say it globally.
                                nb_.scriptsLoadingStatus.czrMagnificPopup.resolve();
                                // instantiate if not done yet
                                //if ( ! $lightBoxCandidate.data( 'magnificPopup' ) )
                                //_doMagnificPopupWhenScriptAndStyleLoaded($linkCandidates);
                          }).fail( function() {
                                nb_.errorLog('Magnific popup instantiation failed for candidate');
                          });
                    };// doLoadMagnificPopup
                    // Fire now or schedule when becoming visible.
                    if ( nb_.isInWindow( $linkCandidates.first() ) ) {
                          doLoadMagnificPopup();
                    } else {
                          _scrollHandle = nb_.throttle( function() {
                                if ( nb_.isInWindow( $linkCandidates.first() ) ) {
                                      doLoadMagnificPopup();
                                }
                          }, 100 );
                          nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
                    }
              } // if( sekFrontLocalized.load_js_on_scroll )

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





      };/////////////// onJQueryReady

      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));
