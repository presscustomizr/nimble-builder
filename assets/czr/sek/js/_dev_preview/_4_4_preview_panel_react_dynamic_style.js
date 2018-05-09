//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            ajaxRefreshStylesheet : function( params ) {
                  var self = this;
                  //console.log('preview => panel react => ajax refresh dyn style', params );
                  return czrapp.doAjax( {
                        action : 'sek_get_content',
                        skope_id : params.skope_id,
                        sek_action : 'sek-refresh-stylesheet'
                  }).done( function( _r_ ) {
                        //console.log('sek-refresh-stylesheet done !',  _r_.data);
                        self.appendDynStyleSheet( params.skope_id, _r_.data );
                  }).fail( function( _r_ ) {
                        czrapp.errare('sek-refresh-stylesheet fail !');
                  });
            },

            appendDynStyleSheet : function( skope_id, styleMarkup ) {
                var _stylesheet_id_ = '#sek-' + skope_id;

                //console.log('IN APPEND DYN STYLESHEET', styleMarkup, _stylesheet_id_, $('head').find( _stylesheet_id_ ) );

                // Remove a dynamic inline stylesheet if already printed
                if ( 0 < $('head').find( _stylesheet_id_ ).length ) {
                      $('head').find( _stylesheet_id_ ).remove();
                }
                $('head').append( styleMarkup );
                // if we have something to print ( styleMarkup not empty ), there should be a dom element
                if ( ! _.isEmpty( styleMarkup ) && 1 > $('head').find( _stylesheet_id_ ).length ) {
                      czrapp.errare( 'sek-preview => problem when printing the dynamic inline style for : '+ _stylesheet_id_ );
                } else {
                      $('head').find( _stylesheet_id_ ).attr('sek-data-origin', 'customizer' );
                }
            }//appendDynStyleSheet()
      });//$.extend()
})( wp.customize, jQuery, _ );
