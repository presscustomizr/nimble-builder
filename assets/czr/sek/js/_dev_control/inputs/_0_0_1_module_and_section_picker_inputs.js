//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            module_picker : function( input_options ) {
                var input = this;
                // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                // input.container.find('[draggable]').each( function() {
                //       $(this).on( 'mousedown mouseup', function( evt ) {
                //             switch( evt.type ) {
                //                   case 'mousedown' :
                //                         //$(this).addClass('sek-grabbing');
                //                   break;
                //                   case 'mouseup' :
                //                         //$(this).removeClass('sek-grabbing');
                //                   break;
                //             }
                //       });
                // });
                api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'module', input_container : input.container } );
                //console.log( this.id, input_options );
            },

            // June 2020 : input type used for both prebuilt and user sections
            section_picker : function( input_options ) {
                  var input = this;
                  // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                  // input.container.find('[draggable]').each( function() {
                  //       $(this).on( 'mousedown mouseup', function( evt ) {
                  //             switch( evt.type ) {
                  //                   case 'mousedown' :
                  //                         //$(this).addClass('sek-grabbing');
                  //                   break;
                  //                   case 'mouseup' :
                  //                         //$(this).removeClass('sek-grabbing');
                  //                   break;
                  //             }
                  //       });
                  // });
                  api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'preset_section', input_container : input.container } );
            }
      });
})( wp.customize, jQuery, _ );