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
 *  FITTEXT
/* ------------------------------------------------------------------------- */
jQuery( function($){
    var doFitText = function() {
          $(".sek-module-placeholder").each( function() {
                $(this).fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
          });
          // Delegate instantiation
          $('.sektion-wrapper').on(
                'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-refresh-level',
                'div[data-sek-level="section"]',
                function( evt ) {
                      $(this).find(".sek-module-placeholder").fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                }
          );

    };
    //doFitText();
    // if ( 'function' == typeof(_) && ! _.isUndefined( wp.customize ) ) {
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