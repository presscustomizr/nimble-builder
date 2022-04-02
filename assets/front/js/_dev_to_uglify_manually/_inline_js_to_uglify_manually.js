// printed in function sek_customizr_js_stuff()
// global sekFrontLocalized, nimbleListenTo
(function(w, d){
      nb_.listenTo( 'nb-app-ready', function() {
          //PREVIEWED DEVICE ?
          //Listen to the customizer previewed device
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
      });// onJQueryReady
}(window, document));