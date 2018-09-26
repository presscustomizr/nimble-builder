//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            buttons_choice : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-button-choice-wrapper', input.container ),
                      $mainInput = $wrapper.find( 'input[type="number"]'),
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  // SETUP
                  // Setup the initial state of the number input
                  $mainInput.val( input() );

                  // Schedule choice changes on button click
                  $wrapper.on( 'click', '[data-sek-choice]', function( evt, params ) {
                        evt.stopPropagation();
                        // handle the is-selected css class toggling
                        $wrapper.find('[data-sek-choice]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        var newChoice;
                        try { newChoice = $(this).data('sek-choice'); } catch( er ) {
                              api.errare( 'buttons_choice input type => error when attaching click event', er );
                        }
                        input( newChoice );
                  });


                  // INITIALIZES
                  // trigger a click on the initial unit
                  // => the initial unit could be set when fetching the server template but it's more convenient to handle it once the template is rendered
                  $( '[data-sek-choice="' + input() + '"]', $wrapper ).trigger('click', { initializing_the_unit : true } );
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );