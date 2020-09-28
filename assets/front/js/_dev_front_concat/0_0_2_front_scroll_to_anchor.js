// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SCROLL TO ANCHOR
/* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
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
                  // Sept 2020 => if lazy load is enabled and there are still images to load, make sure all images are loaded before scrolling to an anchor
                  // => lazyload all images + add a tiny delay before scrolling
                  // otherwise, the scroll might no land to the right place, due to image dimensions not OK ( occurs on chrome and edge at least )
                  // see https://github.com/presscustomizr/nimble-builder/issues/744
                  var _scrollDelay = 0;
                  if ( sekFrontLocalized.lazyload_enabled && true !== nb_.cachedElements.allImgLoadedBeforeScrollToAnchor ) {
                        nb_.cachedElements.$body.find('img').trigger('sek_load_img');
                        nb_.cachedElements.allImgLoadedBeforeScrollToAnchor = true;
                        _scrollDelay = 100;//<= needed on browsers like chrome and edge, not on FF
                  }
                  nb_.delay( function() {
                        // Check is scrollIntoView is fully supported, in particular the options for smooth behavior
                        // https://stackoverflow.com/questions/46919627/is-it-possible-to-test-for-scrollintoview-browser-compatibility
                        // if not, fallback on jQuery animate()
                        if( 'scrollBehavior' in document.documentElement.style ) {
                              $nimbleTargetCandidate[0].scrollIntoView( { behavior: "smooth" } );
                        } else {
                              $root.animate({ scrollTop : $nimbleTargetCandidate.offset().top - 150 }, 400 );
                        }
                  }, _scrollDelay );
            };

            // animate menu item to Nimble anchors
            nb_.cachedElements.$body.find('.menu-item' ).on( 'click', 'a', maybeScrollToAnchor );

            // animate an anchor link inside Nimble sections
            // fixes https://github.com/presscustomizr/nimble-builder/issues/443
            $('[data-sek-level="location"]' ).on( 'click', 'a', maybeScrollToAnchor );
        });
    };/////////////// callbackFunc

    nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));