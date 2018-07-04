//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            bg_position : function( input_options ) {
                  var input = this;
                  // Listen to user actions on the inputs and set the input value
                  $('.sek-bg-pos-wrapper', input.container ).on( 'change', 'input[type="radio"]', function(evt) {
                        input( $(this).val() );
                  });

                  // Synchronize on init
                  if ( ! _.isEmpty( input() ) ) {
                        input.container.find('input[value="'+ input() +'"]').attr('checked', true).trigger('click');
                  }
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );