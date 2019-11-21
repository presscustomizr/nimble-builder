// global sekFrontLocalized
/* ------------------------------------------------------------------------- *
 *  LIGHT BOX WITH MAGNIFIC POPUP
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('[data-sek-module-type="czr_image_module"]').each( function() {
            $linkCandidate = $(this).find('.sek-link-to-img-lightbox');
            // Abort if no link candidate, or if the link href looks like :javascript:void(0) <= this can occur with the default image for example.
            if ( $linkCandidate.length < 1 || 'string' !== typeof( $linkCandidate[0].protocol ) || -1 !== $linkCandidate[0].protocol.indexOf('javascript') )
              return;
            if ( 'function' !== typeof( $.fn.magnificPopup ) )
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
                  if ( typeof window.console.log === 'function' ) {
                        console.log( er );
                  }
            }
      });
});


/* ------------------------------------------------------------------------- *
 *  SMARTLOAD
/* ------------------------------------------------------------------------- */
jQuery(function($){
      $('.sektion-wrapper').each( function() {
            try { $(this).nimbleLazyLoad(); } catch( er ) {
                  if ( typeof window.console.log === 'function' ) {
                        console.log( er );
                  }
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
                 nimbleFront.cachedElements.$body.trigger('resize');
            }, 500 );
      };
      // When previewing, react to level refresh
      // This can occur to any level. We listen to the bubbling event on 'body' tag
      // and salmon up to maybe instantiate any missing candidate
      // Example : when a preset_section is injected
      nimbleFront.cachedElements.$body.on('sek-level-refreshed sek-section-added', function( evt ){
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
 *  FITTEXT
/* ------------------------------------------------------------------------- */
jQuery( function($){
    var doFitText = function() {
          $(".sek-module-placeholder").each( function() {
                $(this).fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
          });
          // Delegate instantiation
          $('.sektion-wrapper').on(
                'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed',
                'div[data-sek-level="section"]',
                function( evt ) {
                      $(this).find(".sek-module-placeholder").fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                }
          );

    };
    //doFitText();
    // if ( 'function' == typeof(_) && window.wp && ! _utils_.isUndefined( wp.customize ) ) {
    //     wp.customize.selectiveRefresh.bind('partial-content-rendered' , function() {
    //         doFitText();
    //     });
    // }

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
    nimbleFront.cachedElements.$body.find('.menu-item' ).on( 'click', 'a', maybeScrollToAnchor );

    // animate an anchor link inside Nimble sections
    // fixes https://github.com/presscustomizr/nimble-builder/issues/443
    $('[data-sek-level="location"]' ).on( 'click', 'a', maybeScrollToAnchor );
});

/* ------------------------------------------------------------------------- *
 *  VIDEO BACKGROUND FOR SECTIONS
/* ------------------------------------------------------------------------- */
// - insert bg video container
// - inject player api script
// - print video iframe
// - on api ready, do stuff
jQuery( function($){
    $('[data-sek-video-bg-src]').each(function() {
        if ( 'section' === $(this).data('sek-level') ) {
            $(this).nimbleLoadVideoBg();
        }
    });
});