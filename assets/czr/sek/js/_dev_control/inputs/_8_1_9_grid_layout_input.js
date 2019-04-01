//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      $.extend( api.czrInputMap, {
            grid_layout : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-grid-layout-wrapper', input.container ),
                      $mainInput = $wrapper.find( 'input[type="hidden"]');

                  // SETUP
                  // Setup the initial state of the number input
                  $mainInput.val( input() );

                  // Schedule choice changes on button click
                  $wrapper.on( 'click', '[data-sek-grid-layout]', function( evt, params ) {
                        evt.stopPropagation();
                        // handle the is-selected css class toggling
                        $wrapper.find('[data-sek-grid-layout]').removeClass('selected').attr( 'aria-pressed', false );
                        $(this).addClass('selected').attr( 'aria-pressed', true );
                        var newChoice;
                        try { newChoice = $(this).data('sek-grid-layout'); } catch( er ) {
                              api.errare( input.type + ' => error when attaching click event', er );
                        }
                        input( newChoice );
                  });


                  // INITIALIZES
                  // trigger a click on the initial unit
                  $( '[data-sek-grid-layout="' + input() + '"]', $wrapper ).trigger('click');
            }
      });// $.extend( api.czrInputMap
})( wp.customize, jQuery, _ );