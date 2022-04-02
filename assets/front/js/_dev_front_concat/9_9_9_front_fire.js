// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX SWIPEBOX ( April 2022 for #886)
 /* ------------------------------------------------------------------------- */
 (function(w, d){
      nb_.listenTo('nb-swipebox-parsed', function() {
            jQuery(function($){
                  if ( nb_.isCustomizing() )
                        return;
      
                  var $linkCandidates = [
                        $('[data-sek-level="module"]').find('.sek-link-to-img-lightbox'),// image module
                        $('[data-sek-level="module"]').find('.sek-gal-link-to-img-lightbox')// gallery module
                  ];

                  //https://github.com/brutaldesign/swipebox
                  var _params = {
                        loopAtEnd: true
                  };
                  //var $linkCand;
                  $.each( $linkCandidates, function(_k, $linkCand) {
                        // Abort if no link candidate
                        if ( $linkCand.length < 1 ) {
                              return;
                        }
                        // Abort if candidate already setup
                        if ( $linkCand.data('nimble-swiperbox-done') )
                              return;
                        try { $linkCand.swipebox( _params ); } catch( er ) {
                              nb_.errorLog( 'error in callback of nb-swipebox-parsed => ', er );
                        }
                        $linkCand.data('nimble-swiperbox-done', true );
                  });

                  // July 2021, prevent gallery images to be clicked when no link is specified
                  $('.sek-gallery-lightbox').on('click', '.sek-no-img-link', function(evt) {
                        evt.preventDefault();
                  });

            });//jQuery(function($){})
      });
  }(window, document));







/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
// nimble-lazyload-parsed is fired in lazyload plugin, only when sekFrontLocalized.lazyload_enabled OR when nb_.isCustomizing()
(function(w, d){
    nb_.listenTo('nb-lazyload-parsed', function() {
        jQuery(function($){
              var _do = function(evt) {
                    $(this).each( function() {
                          var _maybeDoLazyLoad = function() {
                                // if the element already has an instance of nimbleLazyLoad, simply trigger an event
                                if ( !$(this).data('nimbleLazyLoadDone') ) {
                                    $(this).nimbleLazyLoad({force : nb_.isCustomizing()});
                                } else {
                                    $(this).trigger('nb-trigger-lazyload');
                                }
                          };
                          try { _maybeDoLazyLoad.call($(this)); } catch( er ) {
                                nb_.errorLog( 'error with nimbleLazyLoad => ', er );
                          }
                    });
              };
              // on page load
              _do.call( $('.sektion-wrapper') );
              // when customizing
              nb_.cachedElements.$body.on( 'sek-section-added sek-level-refreshed sek-location-refreshed sek-columns-refreshed sek-modules-refreshed', '[data-sek-level="location"]', function(evt) {
                    _do.call( $(this), evt );
                    _.delay( function() {
                            nb_.cachedElements.$window.trigger('resize');
                    }, 200 );
              });


              // TO EXPLORE : implement a mutation observer like in Hueman theme for images dynamically inserted in the DOM via ajax ?
              // Is it really needed now that lazyload uses event delegation to trigger image loading ?
              // ( see https://github.com/presscustomizr/nimble-builder/issues/669 )
              // Observer Mutations of the DOM for a given element selector
              // <=> of previous $(document).bind( 'DOMNodeInserted', fn );
              // implemented to fix https://github.com/presscustomizr/hueman/issues/880
              // see https://stackoverflow.com/questions/10415400/jquery-detecting-div-of-certain-class-has-been-added-to-dom#10415599
              //   observeAddedNodesOnDom : function(containerSelector, elementSelector, callback) {
              //       var onMutationsObserved = function(mutations) {
              //               mutations.forEach(function(mutation) {
              //                   if (mutation.addedNodes.length) {
              //                       var elements = $(mutation.addedNodes).find(elementSelector);
              //                       for (var i = 0, len = elements.length; i < len; i++) {
              //                           callback(elements[i]);
              //                       }
              //                   }
              //               });
              //           },
              //           target = $(containerSelector)[0],
              //           config = { childList: true, subtree: true },
              //           MutationObserver = window.MutationObserver || window.WebKitMutationObserver,
              //           observer = new MutationObserver(onMutationsObserved);

              //       observer.observe(target, config);
              // }
              // Observer Mutations off the DOM to detect images
              // <=> of previous $(document).bind( 'DOMNodeInserted', fn );
              // implemented to fix https://github.com/presscustomizr/hueman/issues/880
              // this.observeAddedNodesOnDom('body', 'img', _.debounce( function(element) {
              //       _doLazyLoad();
              // }, 50 ));

        });
    });
}(window, document));



/* ------------------------------------------------------------------------- *
 *  BG PARALLAX
/* ------------------------------------------------------------------------- */
(function(w, d){
    nb_.listenTo('nb-parallax-parsed', function() {
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


/* ------------------------------------------------------------------------- *
 *  GRID MODULE
/* ------------------------------------------------------------------------- */
// June 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/716
nb_.listenTo('nb-docready', function() {
      if ( window.nb_ && window.nb_.getQueryVariable ) {
            var anchorId = window.nb_.getQueryVariable('nb_grid_module_go_to'),
                  el = document.getElementById(anchorId);
            // Then clean the url
            var _cleanUrl = function() {
                  var currPathName = window.location.pathname; //get current address
                  //1- get the part before '?go_to'
                  var beforeQueryString = currPathName.split("?go_to")[0];
                  window.history.replaceState({}, document.title,  beforeQueryString );
            };
            if( anchorId && el ) {
                  setTimeout( function() { el.scrollIntoView();}, 200 );
                  try{ _cleanUrl(); } catch(er) {
                        if( window.console && window.console.log ) {
                              console.log( 'NB => error when cleaning url "go_to" param');
                        }
                  }
            }
      }
});

// September 2021 => Solves the problem of CSS loaders not cleaned
// see https://github.com/presscustomizr/nimble-builder/issues/874
nb_.listenTo('nb-app-ready', function() {
      jQuery(function($){
            var $cssLoaders = $('.sek-css-loader');
            if ( $cssLoaders.length < 1 )
                  return;

            var $el, 
                  removeCssLoaderAfterADelay = nb_.throttle( function() {
                        $cssLoaders = $('.sek-css-loader');
                        $.each($cssLoaders, function(){
                              $el = $(this);
                              if ( nb_.elOrFirstVisibleParentIsInWindow($el) ) {
                                    nb_.delay( function() {
                                          if ( $el.length > 0 ) {
                                                $el.remove();
                                          }
                                          
                                    }, 1000);
                              }
                        });
                        
                        if ( $cssLoaders.length < 1 ) {
                              // When no more loaders to remove, remove scroll listener
                              nb_.cachedElements.$window.off('scroll', removeCssLoaderAfterADelay );
                        }
                  }, 200 );
            nb_.cachedElements.$window.on('scroll', removeCssLoaderAfterADelay );
      });
});