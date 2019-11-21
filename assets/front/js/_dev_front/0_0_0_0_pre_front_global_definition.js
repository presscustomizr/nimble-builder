// global sekFrontLocalized
var nimbleFront;
jQuery( function($){
    window.nimbleFront = {
        cachedElements : {
            $window : $(window),
            $body : $('body')
        },
        isMobile : function() {
              return ( _utils_.isFunction( window.matchMedia ) && matchMedia( 'only screen and (max-width: 768px)' ).matches ) || ( this.isCustomizing() && 'desktop' != this.previewedDevice );
        },
        isCustomizing : function() {
              return this.cachedElements.$body.hasClass('is-customizing') || ( 'undefined' !== typeof wp && 'undefined' !== typeof wp.customize );
        },
        previewedDevice : 'desktop'
    };

    //PREVIEWED DEVICE ?
    //Listen to the customizer previewed device
    if ( nimbleFront.isCustomizing() ) {
          var _setPreviewedDevice = function() {
                wp.customize.preview.bind( 'previewed-device', function( device ) {
                      nimbleFront.previewedDevice = device;// desktop, tablet, mobile
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
});
