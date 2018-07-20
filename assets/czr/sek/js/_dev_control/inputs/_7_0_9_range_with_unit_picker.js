//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            range_with_unit_picker : function( params ) {
                  var input = this,
                  $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                  $numberInput = $wrapper.find( 'input[type="number"]'),
                  $rangeInput = $wrapper.find( 'input[type="range"]'),
                  initial_unit = $wrapper.find('input[data-czrtype]').data('sek-unit'),
                  validateUnit = function( unit ) {
                        if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                              api.errare( 'error : invalid unit for input ' + input.id, unit );
                              unit = 'px';
                        }
                        return unit;
                  };
                  // initialize the unit with the value provided in the dom
                  input.css_unit = new api.Value( _.isEmpty( initial_unit ) ? 'px' : validateUnit( initial_unit ) );
                  // React to a unit change => trigger a number input change
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        $wrapper.find( 'input[type="number"]').trigger('input');
                  });

                  // synchronizes range input and number input
                  // number is the master => sets the input() val
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  $numberInput.on('input', function( evt ) {
                        input( $(this).val() + validateUnit( input.css_unit() ) );
                        $rangeInput.val( $(this).val() );
                  });
                  // trigger a change on init to sync the range input
                  $rangeInput.val( $numberInput.val() || 0 );

                  // Schedule unit changes on button click
                  $wrapper.on( 'click', '.sek-ui-button', function(evt) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        $wrapper.find('.sek-ui-button').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        // update the initial unit ( not mandatory)
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        // set the current unit Value
                        input.css_unit( $(this).data('sek-unit') );
                  });

                  // add is-selected button on init to the relevant unit button
                  $wrapper.find( '.sek-ui-button[data-sek-unit="'+ initial_unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );