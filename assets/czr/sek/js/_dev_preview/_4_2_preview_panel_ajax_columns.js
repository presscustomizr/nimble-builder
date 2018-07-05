//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // refresh column is used to
            // 1) Add a new column
            // 2) re-render the column collection in a sektion
            ajaxRefreshColumns : function( params ) {
                  //console.log('PARAMS in ajaxRefreshColumns', params );
                  var self = this;
                  // will be cleaned on 'sek-columns-refreshed'
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.in_sektion
                  });
                  return self.doAjax( {
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        skope_id : params.skope_id,
                        sek_action : params.apiParams.action// sek-add-column || sek-remove-column
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype => ajax_response.data.contents is undefined ', _r_ );
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
                        self.doAjax( {
                              action : 'sek_get_content',
                              skope_id : params.skope_id,
                              sek_action : 'sek-refresh-stylesheet'// sek-add-column
                        }).done( function( _r_ ) {
                              //console.log('sek-refresh-stylesheet done !',  html_content);
                              self.appendDynStyleSheet( params.skope_id, html_content );
                        }).fail( function( _r_ ) {
                              console.log('sek-refresh-stylesheet fail !');
                        });

                        // say it to the parent sektion
                        //=> will be listened to by the column to re-instantiate sortable, resizable
                        //=> also listened to clean the loader overalay in time
                        $('div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).trigger('sek-columns-refreshed');
                  }).fail( function( _r_ ) {
                        self.errare( 'ERROR reactToPanelMsg => sek-add-column => ' , _r_ );
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            },//ajaxRefreshColumns()


            ajaxResizeColumns : function( params ) {
                  //console.log('PREVIEW => REACT TO PANEL MSG => sek-resize-columns => ', params );
                  var self = this;
                  // will be cleaned on 'sek-module-refreshed'
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.in_sektion
                  });
                  return self.doAjax( {
                        action : 'sek_get_content',
                        resized_column : params.apiParams.resized_column,
                        sister_column : params.apiParams.sister_column,
                        skope_id : params.skope_id,
                        sek_action : 'sek-resize-columns'
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype => ajax_response.data.contents is undefined ', _r_ );
                        }
                        //self.errare('sek-preview => resize-column ajax response => ', html_content );
                        // Reset the automatic default resizable inline styling
                        $( '[data-sek-id="' + params.apiParams.resized_column + '"]' ).css({
                              width : '',
                              height: ''
                        });

                        //Append
                        self.appendDynStyleSheet( params.skope_id, html_content );

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
