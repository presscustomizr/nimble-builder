// global sekFrontLocalized
/* ===================================================
 * jquery.fn.parallaxBg v1.0.0
 * Created in October 2018.
 * Inspired from https://github.com/presscustomizr/front-jquery-plugins/blob/master/jqueryParallax.js
 * ===================================================
*/
(function ( $, window ) {
      //defaults
      var pluginName = 'parallaxBg',
          defaults = {
                parallaxForce : 40,
                oncustom : [],//list of event here
                matchMedia : 'only screen and (max-width: 800px)'
          };

      function Plugin( element, options ) {
            this.element         = $(element);
            //this.element_wrapper = this.element.closest( '.parallax-wrapper' );
            this.options         = $.extend( {}, defaults, options, this.parseElementDataOptions() ) ;
            this._defaults       = defaults;
            this._name           = pluginName;
            this.init();
      }

      Plugin.prototype.parseElementDataOptions = function () {
            return this.element.data();
      };

      //can access this.element and this.option
      //@return void
      Plugin.prototype.init = function () {
            var self = this;
            //cache some element
            this.$_window     = $(window);
            this.doingAnimation = false;
            this.isVisible = false;
            this.isBefore = false;//the element is before the scroll point
            this.isAfter = true;// the element is after the scroll point

            // normalize the parallax ratio
            // must be a number 0 > ratio > 100
            if ( 'number' !== typeof( self.options.parallaxForce ) || self.options.parallaxForce < 0 ) {
                  if ( sekFrontLocalized.isDevMode ) {
                        console.log('parallaxBg => the provided parallaxForce is invalid => ' + self.options.parallaxForce );
                  }
                  self.options.parallaxForce = this._defaults.parallaxForce;
            }
            if ( self.options.parallaxForce > 100 ) {
                  self.options.parallaxForce = 100;
            }

            console.log('PARALLAX RATIO ?', self.options.parallaxForce );

            //the scroll event gets throttled with the requestAnimationFrame
            this.$_window.scroll( function(_evt) { self.maybeParallaxMe(); } );
            //debounced resize event
            this.$_window.resize( _utils_.debounce( function(_evt) {
                  self.maybeParallaxMe();
            }, 100 ) );

            //on load
            this.checkIfIsVisibleAndCacheProperties();
            this.setTopPositionAndBackgroundSize();
      };

      //@see https://www.paulirish.com/2012/why-moving-elements-with-translate-is-better-than-posabs-topleft/
      Plugin.prototype.setTopPositionAndBackgroundSize = function() {
            var self = this,
                $element       = this.element,
                elemHeight = $element.outerHeight(),
                winHeight = this.$_window.height(),
                offsetTop = $element.offset().top,
                scrollTop = this.$_window.scrollTop(),
                percentOfPage = 100;

            // the percentOfPage can vary from -1 to 1
            if ( this.isVisible ) {
                  //percentOfPage = currentDistanceToMiddleScreen / maxDistanceToMiddleScreen;
                  percentOfPage = ( offsetTop - scrollTop ) / winHeight;
            } else if ( this.isBefore ) {
                  percentOfPage = 1;
            } else if ( this.isAfter ) {
                  percentOfPage = - 1;
            }

            var maxBGYMove = this.options.parallaxForce > 0 ? winHeight * ( 100 - this.options.parallaxForce ) / 100 : winHeight,
                bgPositionY = Math.round( percentOfPage *  maxBGYMove );

            this.element.css({
                  'background-position-y' : [
                        'calc(50% ',
                        bgPositionY > 0 ? '+ ' : '- ',
                        Math.abs( bgPositionY ) + 'px)'
                  ].join('')
            });
      };

      // When the image enters the viewport ?
      Plugin.prototype.checkIfIsVisibleAndCacheProperties = function( _evt ) {
          var $element = this.element,
              scrollTop = this.$_window.scrollTop(),
              wb = scrollTop + this.$_window.height(),
              offsetTop  = $element.offset().top,
              ib  = offsetTop + $element.outerHeight();

          // Cache now
          this.isVisible = ib >= scrollTop && offsetTop <= wb;
          this.isBefore = offsetTop > wb ;//the element is before the scroll point
          this.isAfter = ib < scrollTop;// the element is after the scroll point
          return this.isVisible;
      };

      // a throttle is implemented with window.requestAnimationFrame
      Plugin.prototype.maybeParallaxMe = function() {
            var self = this;
            //console.log('IS VISIBLE ?', this.checkIfIsVisibleAndCacheProperties() );
            if ( ! this.checkIfIsVisibleAndCacheProperties() )
              return;

            //options.matchMedia is set to 'only screen and (max-width: 768px)' by default
            //if a match is found, then reset the top position
            if ( _utils_.isFunction( window.matchMedia ) && matchMedia( self.options.matchMedia ).matches ) {
                  this.element.css({'background-position-y' : '', 'background-attachment' : '' });
                  return;
            }

            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self.setTopPositionAndBackgroundSize();
                        self.doingAnimation = false;
                  });
            }
      };

      // prevents against multiple instantiations
      $.fn[pluginName] = function ( options ) {
          return this.each(function () {
              if (!$.data(this, 'plugin_' + pluginName)) {
                  $.data(this, 'plugin_' + pluginName,
                  new Plugin( this, options ));
              }
          });
      };
})( jQuery, window );