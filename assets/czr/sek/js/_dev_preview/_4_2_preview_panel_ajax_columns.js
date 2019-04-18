//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // refresh column is used to
            // 1) Add a new column
            // 2) re-render the column collection in a sektion
            ajaxRefreshColumns : function( params ) {
                  var self = this;
                  // will be cleaned on 'sek-columns-refreshed'
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.in_sektion
                  });
                  return self.doAjax( {
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : params.apiParams.action// sek-add-column || sek-remove-column
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype::ajaxRefreshColumns => ajax_response.data.contents is undefined ', _r_ );
                              self.errare( 'params ?', params );
                        }

                        var $parentSektion = $( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' );
                        if ( 1 > $parentSektion.length ) {
                              self.errare( 'reactToPanelMsg => ' + params.apiParams.action + ' => no DOM node for parent sektion => ', params.apiParams.in_sektion );
                        }
                        var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_sektion + '"></span>';
                        $parentSektion.before( placeholderHtml );
                        // remove and re-render the entire sektion
                        $parentSektion.remove();
                        $( '.sektion-wrapper').find( '.sek-placeholder' ).after( html_content );
                        $( '.sektion-wrapper').find( '.sek-placeholder' ).remove();

                        // re-generate the stylesheet => this will take into account the reset width of each column
                        api.preview.trigger( 'sek-refresh-stylesheet', params );

                        // say it to the parent sektion
                        //=> will be listened to by the column to re-instantiate sortable, resizable
                        //=> also listened to clean the loader overalay in time
                        $('div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).trigger('sek-columns-refreshed', { in_sektion : params.apiParams.in_sektion } );
                  }).fail( function( _r_ ) {
                        self.errare( 'ERROR reactToPanelMsg => sek-add-column => ' , _r_ );
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            },//ajaxRefreshColumns()


            ajaxResizeColumns : function( params ) {
                  var self = this;
                  // will be cleaned on 'sek-module-refreshed'
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.in_sektion
                  });
                  return self.doAjax( {
                        action : 'sek_get_content',
                        resized_column : params.apiParams.resized_column,
                        sister_column : params.apiParams.sister_column,
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : 'sek-resize-columns'
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype::ajaxResizeColumns => ajax_response.data.contents is undefined ', _r_ );
                              self.errare( 'params ?', params );
                        }
                        //self.errare('sek-preview => resize-column ajax response => ', html_content );
                        // Reset the automatic default resizable inline styling
                        $( '[data-sek-id="' + params.apiParams.resized_column + '"]' ).css({
                              width : '',
                              height: ''
                        });

                        //Append
                        self.appendDynStyleSheet( params.location_skope_id, html_content );

                        // say it
                        // listened to clean the loader just in time
                        $('div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).trigger('sek-columns-refreshed');
                  }).fail( function( _r_ ) {
                        self.errare( 'ERROR reactToPanelMsg => sek-resize-columns => ' , _r_ );
                        $( '[data-sek-id="' + params.apiParams.in_sektion + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
