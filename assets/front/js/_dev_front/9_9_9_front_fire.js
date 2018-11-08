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
      $('[data-sek-bg-parallax="true"]').parallaxBg();
      // When previewing, react to level refresh
      // This can occur to any level. We listen to the bubbling event on 'body' tag
      // and salmon up to maybe instantiate any missing candidate
      // Example : when a preset_section is injected
      $('body').on('sek-level-refreshed sek-section-added', function( evt ){
            if ( "true" === $(this).attr( 'data-sek-bg-parallax' ) ) {
                  $(this).parallaxBg();
            } else {
                  $(this).find('[data-sek-bg-parallax="true"]').parallaxBg();
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
    // if ( 'function' == typeof(_) && ! _utils_.isUndefined( wp.customize ) ) {
    //     wp.customize.selectiveRefresh.bind('partial-content-rendered' , function() {
    //         doFitText();
    //     });
    // }

    // animate menu item to Nimble anchors
    $('body').on( 'click', '.menu .menu-item [href^="#"]', function( evt){
          evt.preventDefault();
          var anchorCandidate = $(this).attr('href');
          anchorCandidate = 'string' === typeof( anchorCandidate ) ? anchorCandidate.replace('#','') : '';

          if ( '' !== anchorCandidate || null !== anchorCandidate ) {
                var $anchorCandidate = $('[data-sek-level="location"]' ).find( '[id="' + anchorCandidate + '"]');
                if ( 1 === $anchorCandidate.length ) {
                      $('html, body').animate({
                            scrollTop : $anchorCandidate.offset().top - 150
                      }, 'slow');
                }
          }
    });

});