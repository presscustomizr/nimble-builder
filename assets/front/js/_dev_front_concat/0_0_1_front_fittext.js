/*global jQuery */
/*!
* FitText.js 1.2
*
* Copyright 2011, Dave Rupert http://daverupert.com
* Released under the WTFPL license
* http://sam.zoy.org/wtfpl/
*
* Date: Thu May 05 14:23:00 2011 -0600
*/
// global sekFrontLocalized, nimbleListenTo
(function(w, d){
      var callbackFunc = function() {
            (function( $ ){
                $.fn.fitText = function( kompressor, options ) {

                  // Setup options
                  var compressor = kompressor || 1,
                      settings = $.extend({
                        'minFontSize' : Number.NEGATIVE_INFINITY,
                        'maxFontSize' : Number.POSITIVE_INFINITY
                      }, options);

                  return this.each(function(){

                    // Store the object
                    var $this = $(this);

                    // Resizer() resizes items based on the object width divided by the compressor * 10
                    var resizer = function () {
                      $this.css('font-size', Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)) + 'px');
                    };

                    // Call once to set.
                    resizer();

                    // Call on resize. Opera debounces their resize by default.
                    nb_.cachedElements.$window.on('resize.fittext orientationchange.fittext', resizer);

                  });
                };
            })( jQuery );

            var doFitText = function() {
                  $(".sek-module-placeholder").each( function() {
                        $(this).fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                  });
                  // Delegate instantiation
                  $('.sektion-wrapper').on(
                        'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed',
                        'div[data-sek-level="section"]',
                        function( evt ) {
                              $(this).find(".sek-module-placeholder").fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                        }
                  );
            };
            //doFitText();
            // if ( 'function' == typeof(_) && window.wp && ! nb_.isUndefined( wp.customize ) ) {
            //     wp.customize.selectiveRefresh.bind('partial-content-rendered' , function() {
            //         doFitText();
            //     });
            // }
      };// onJQueryReady

      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));