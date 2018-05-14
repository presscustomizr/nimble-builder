//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
              // refresh module is used to
              // 1) Add a new module
              // 2) re-render the module collection in a column, typically after a sortable move, or a module removal
              ajaxRefreshModulesAndNestedSections : function( params ) {
                    var self = this;
                    return czrapp.doAjax( {
                          action : 'sek_get_content',
                          id : params.apiParams.id,
                          in_sektion : params.apiParams.in_sektion,
                          in_column : params.apiParams.in_column,
                          skope_id : params.skope_id,
                          sek_action : params.apiParams.action, // can be sek-add-module / refresh-modules-in-column
                          is_nested : params.apiParams.is_nested
                    }).done( function( _r_ ) {
                          var $parentColumn = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' );
                          if ( 1 > $parentColumn.length ) {
                                czrapp.errare( 'reactToPanelMsg => ajaxRefreshModulesAndNestedSections => no DOM node for parent column => ', params.apiParams.in_column );
                          }
                          var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_column + '"></span>';
                          $parentColumn.before( placeholderHtml );
                          // remove and re-render the entire column
                          $parentColumn.remove();
                          $( '.sektion-wrapper').find( '[data-sek-placeholder-for="' + params.apiParams.in_column + '"]' ).after( _r_.data );
                          $( '.sektion-wrapper').find( '[data-sek-placeholder-for="' + params.apiParams.in_column + '"]' ).remove();

                          // say it to the column
                          //=> will be listened to by the column to re-instantiate sortable, resizable and fittext
                          $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' ).trigger('sek-modules-refreshed');

                    }).fail( function( _r_ ) {
                          czrapp.errare( 'ERROR reactToPanelMsg => sek-add-module => ' , _r_ );
                    });
              }//ajaxRefreshModulesAndNestedSections()
      });//$.extend()
})( wp.customize, jQuery, _ );
