//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            range_simple : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]');

                  // synchronizes range input and number input
                  // number is the master => sets the input() val
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  $numberInput.on('input', function( evt ) {
                        input( $(this).val() );
                        $rangeInput.val( $(this).val() );
                  });
                  // trigger a change on init to sync the range input
                  $rangeInput.val( $numberInput.val() || 0 );
            },

            number_simple : function( params ) {
                  var input = this,
                      $numberInput = input.container.find( 'input[type="number"]');

                  $numberInput.on('input', function( evt ) {
                        input( $(this).val() );
                  });
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );