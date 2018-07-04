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
                      $wrapper = $('.sek-font-size-wrapper', input.container ),
                      unit = 'px';

                  $wrapper.find( 'input[type="number"]').on('change', function() {
                        input( $(this).val() + unit );
                  }).stepper();

            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );