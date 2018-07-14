//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            font_size : function( obj ) {
                  var input      = this,
                      $wrapper = $('.sek-font-size-line-height-wrapper', input.container ),
                      initial_unit = $wrapper.find('input[data-czrtype]').data('sek-unit'),
                      validateVal = function( num, unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'error : invalid unit for input ' + input.id );
                                  unit = 'px';
                            }
                            return num + unit;
                      };
                  // initialize the unit with the value provided in the dom
                  input.css_unit = new api.Value( _.isEmpty( initial_unit ) ? 'px' : initial_unit );
                  // React to a unit change
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        $wrapper.find( 'input[type="number"]').trigger('change');
                  });

                  // instantiate stepper and schedule change reactions
                  $wrapper.find( 'input[type="number"]').on('input change', function( evt ) {
                        input( validateVal( $(this).val(), input.css_unit() ) );
                  }).stepper();


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