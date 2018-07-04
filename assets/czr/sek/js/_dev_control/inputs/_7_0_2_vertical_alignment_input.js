//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            v_alignment : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-v-align-wrapper', input.container );
                  // on init
                  $wrapper.find( 'div[data-sek-align="' + input() +'"]' ).addClass('selected');

                  // on click
                  $wrapper.on( 'click', '[data-sek-align]', function(evt) {
                        evt.preventDefault();
                        $wrapper.find('.selected').removeClass('selected');
                        $.when( $(this).addClass('selected') ).done( function() {
                              input( $(this).data('sek-align') );
                        });
                  });
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );