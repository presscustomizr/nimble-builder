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

                var loadNimbleSliderModuleScript = function() {
                      $.ajax( {
                            url : ( sekFrontLocalized.frontAssetsPath + 'js/prod-front-simple-slider-module.min.js'),
                            cache : true,// use the browser cached version when available
                            dataType: "script"
                      }).done(function() {
                            //console.log('ALORS SWIPER Module LOADED ?');
                            if ( 'function' != typeof( window.Swiper ) )
                              return;
                            //the script is loaded. Say it globally.
                            //czrapp.base.scriptLoadingStatus.czrMagnificPopup.resolve();

                            // instantiate if not done yet
                            //if ( ! $lightBoxCandidate.data( 'magnificPopup' ) )
                            //$lightBoxCandidate.magnificPopup( params );
                      }).fail( function() {
                            //czrapp.errorLog( 'Magnific popup instantiation failed for candidate : '  + $lightBoxCandidate.attr( 'class' ) );
                      });
                };
                var loadSwiperScript = function() {
                      $.ajax( {
                            url : ( sekFrontLocalized.frontAssetsPath + 'js/libs/swiper.min.js'),
                            cache : true,// use the browser cached version when available
                            dataType: "script"
                      }).done(function() {
                            //console.log('ALORS SWIPER LIB LOADED ?', window.Swiper );
                            if ( 'function' != typeof( window.Swiper ) )
                              return;

                            loadNimbleSliderModuleScript();
                            //the script is loaded. Say it globally.
                            //czrapp.base.scriptLoadingStatus.czrMagnificPopup.resolve();

                            // instantiate if not done yet
                            //if ( ! $lightBoxCandidate.data( 'magnificPopup' ) )
                            //$lightBoxCandidate.magnificPopup( params );
                      }).fail( function() {
                            //czrapp.errorLog( 'Magnific popup instantiation failed for candidate : '  + $lightBoxCandidate.attr( 'class' ) );
                      });
                };
                if ( sekFrontLocalized.load_js_on_scroll ) {
                    loadSwiperScript();
                }

            });//jQuery( function($){
      };// onJQueryReady

      window.fireOnNimbleAppReady( onNimbleAppReady );
}(window, document));
