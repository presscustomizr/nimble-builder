//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
(function( api, $, _ ) {
      $.extend( SekPreviewPrototype, api.Events );
      var SekPreviewConstructor   = api.Class.extend( SekPreviewPrototype );
      api.bind( 'preview-ready', function(){
              api.preview.bind( 'active', function() {
                  try { api.sekPreview = new SekPreviewConstructor(); } catch( _er_ ) {
                        SekPreviewPrototype.errare( 'SekPreviewConstructor => problem on instantiation', _er_ );
                  }
            });
      });
})( wp.customize, jQuery, _ );
