//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            ajaxRefreshStylesheet : function( params ) {
                  var self = this;
                  // will be cleaned on 'sek-module-refreshed'
                  if ( true !== params.dont_print_loader ) {
                        self.mayBePrintLoader({
                              loader_located_in_level_id : params.apiParams.id
                        });
                  }
                  return self.doAjax( {
                        action : 'sek_get_content',
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : 'sek-refresh-stylesheet'
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        }
                        self.appendDynStyleSheet( params.location_skope_id, html_content );
                        //=> 'sek-level-refreshed' is listened to clean the loader overlay in time
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-stylesheet-refreshed', { level : params.apiParams.level, id : params.apiParams.id } );
                  }).fail( function( _r_ ) {
                        self.errare('sek-refresh-stylesheet fail !');
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            },

            appendDynStyleSheet : function( location_skope_id, styleMarkup ) {
                var _stylesheet_id_ = '#sek-' + location_skope_id,//@see php Sek_Dyn_CSS_Handler
                    _gfonts_id_ = '#' + sekPreviewLocalized.googleFontsStyleId,//@see php Sek_Dyn_CSS_Handler
                    _global_option_inline_style_id_ = '#' + sekPreviewLocalized.globalOptionsStyleId;

                // Remove a dynamic inline stylesheet if already printed
                if ( 0 < $('head').find( _stylesheet_id_ ).length ) {
                      $('head').find( _stylesheet_id_ ).remove();
                }
                if ( 0 < $('head').find( _gfonts_id_ ).length ) {
                      $('head').find( _gfonts_id_ ).remove();
                }
                if ( 0 < $('head').find( _global_option_inline_style_id_ ).length ) {
                      $('head').find( _global_option_inline_style_id_ ).remove();
                }
                if ( !_.isEmpty( styleMarkup ) ) {
                      $('head').append( styleMarkup );
                }
                // Has it be printed ?
                // if we have something to print ( styleMarkup not empty ), there should be a dom element
                if ( !_.isEmpty( styleMarkup ) &&  1 > $('head').find( _stylesheet_id_ ).length && 1 > $('head').find( _gfonts_id_ ).length && 1 > $('head').find( _global_option_inline_style_id_ ).length ) {
                      this.errare( 'sek-preview => problem when printing the dynamic inline style for : '+ _stylesheet_id_, styleMarkup );
                } else {
                      $('head').find( _stylesheet_id_ ).attr('sek-data-origin', 'customizer' );
                }
            }//appendDynStyleSheet()
      });//$.extend()
})( wp.customize, jQuery, _ );
