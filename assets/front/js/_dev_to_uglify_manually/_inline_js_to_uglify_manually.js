// march 2020 : wp_head js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
// printed in function sek_initialize_front_js_app()
window.nimbleFireOn = function( evt, func ) {
    var bools = {
        'nimble-jquery-ready' : typeof undefined !== typeof jQuery,
        'nimble-app-ready' : typeof undefined !== typeof window.nb_ && nb_.isReady === true,
        'nimble-magnific-popup-ready' : typeof undefined !== typeof jQuery && typeof undefined !== typeof jQuery.fn.magnificPopup,
        'nimble-swiper-plugin-ready' : typeof undefined !== typeof window.Swiper
    };
    if ( 'function' === typeof func ) {
      if ( true === bools[evt] ) func();
      else document.addEventListener(evt,func);
    }
}

// printed in function sek_handle_jquery()
// march 2020 : wp_footer js code to be minified
// introduced for https://github.com/presscustomizr/nimble-builder/issues/626
( function() {
      // recursively try to load jquery every 200ms during 6s ( max 30 times )
      var sayWhenJqueryIsReady = function( attempts ) {
          attempts = attempts || 0;
          if ( typeof undefined !== typeof jQuery ) {
              var evt = document.createEvent('Event');
              evt.initEvent('nimble-jquery-ready', true, true); //can bubble, and is cancellable
              document.dispatchEvent(evt);
          } else if ( attempts < 30 ) {
              setTimeout( function() {
                  attempts++;
                  sayWhenJqueryIsReady( attempts );
              }, 200 );
          } else {
              alert('Nimble Builder problem : jQuery.js was not detected on your website');
          }
      };
      sayWhenJqueryIsReady();
})();

// printed in function sek_handle_jquery()
( function() {
      // Load jQuery
      setTimeout( function() {
          var script = document.createElement('script');
          script.setAttribute('src', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js');
          script.setAttribute('type', 'text/javascript');
          script.setAttribute('id', 'nimble-jquery');
          script.setAttribute('defer', 'defer');//https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
          document.getElementsByTagName('head')[0].appendChild(script);
      }, 0 );//<= add a delay to test 'nimble-jquery-ready' and mimic the 'defer' option of a cache plugin
})();


// printed in function sek_customizr_js_stuff()
// global sekFrontLocalized, nimbleFireOn
(function(w, d){
      var callbackFunc = function() {
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

      window.nimbleFireOn('nimble-app-ready', callbackFunc );
}(window, document));