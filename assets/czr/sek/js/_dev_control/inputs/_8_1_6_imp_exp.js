//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            import_export : function() {
                  console.log('setup import_export input');
                  var input = this;

                  // Schedule choice changes on button click
                  input.container.on( 'click', '[data-czr-action]', function( evt, params ) {
                        evt.stopPropagation();
                        var _action = $(this).data( 'czr-action' );
                        console.log('imp exp action', _action );
                  });
            },
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );