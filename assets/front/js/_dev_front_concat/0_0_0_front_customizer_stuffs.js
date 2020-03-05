// global sekFrontLocalized, fireOnNimbleAppReady
(function(w, d){
      var onNimbleAppReady = function() {
            jQuery( function($){
                //PREVIEWED DEVICE ?
                //Listen to the customizer previewed device
                if ( nb_.isCustomizing() ) {
                      var _setPreviewedDevice = function() {
                            wp.customize.preview.bind( 'previewed-device', function( device ) {
                                  nb_.previewedDevice = device;// desktop, tablet, mobile
                            });
                      };
                      if ( wp.customize.preview ) {
                          _setPreviewedDevice();
                      } else {
                            wp.customize.bind( 'preview-ready', function() {
                                  _setPreviewedDevice();
                            });
                      }
                }
            });//jQuery( function($){
      };// onJQueryReady

      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));
