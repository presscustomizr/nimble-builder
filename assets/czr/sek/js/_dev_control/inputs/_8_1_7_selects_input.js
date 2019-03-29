//global sektionsLocalizedData
( function ( api, $, _ ) {

      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            simpleselect : function( selectOptions ) {
                  api.czr_sektions.setupSelectInput.call(this);
            },
            multiselect : function( selectOptions ) {
                  api.czr_sektions.setupSelectInput.call(this);
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );