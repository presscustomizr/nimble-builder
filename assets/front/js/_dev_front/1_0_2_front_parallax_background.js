
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
                parallaxRatio : 0.5,
                parallaxDirection : 1,
                parallaxOverflowHidden : true,
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
            this.$_document   = $(document);
            this.$_window     = $(window);
            this.doingAnimation = false;

            //this.initWaypoints();
            //this.stageParallaxElements();
            _utils_.bindAll( this, 'maybeParallaxMe', 'parallaxMe' );
            //the scroll event gets throttled with the requestAnimationFrame
            $(window).scroll( function(_evt) { self.maybeParallaxMe(); } );
            //debounced resize event
            $(window).resize( _utils_.debounce( function(_evt) { self.maybeParallaxMe(); }, 100 ) );
            //on load
            self.maybeParallaxMe();
      };

      Plugin.prototype._is_visible = function( _evt ) {
          var $element       = this.element,
              wt = $(window).scrollTop(),
              wb = wt + $(window).height(),
              it  = $element.offset().top,
              ib  = it + $element.outerHeight(),
              threshold = 0;

          //force all images to visible if first scroll option enabled
          if ( _evt && 'scroll' == _evt.type && this.options.load_all_images_on_first_scroll )
            return true;

          return ib >= wt - threshold && it <= wb + threshold;
      };
      /*
      * In order to handle a smooth scroll
      */
      Plugin.prototype.maybeParallaxMe = function() {
            var self = this;
            if ( ! this._is_visible() )
              return;

            //options.matchMedia is set to 'only screen and (max-width: 768px)' by default
            //if a match is found, then reset the top position
            if ( _utils_.isFunction( window.matchMedia ) && matchMedia( self.options.matchMedia ).matches ) {
                  //return this.setTopPosition();
                  this.element.css({'background-position-y' : '', 'background-attachment' : '' });
                  return;
            }

            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self.parallaxMe();
                        self.doingAnimation = false;
                  });
            }
      };

      //@see https://www.paulirish.com/2012/why-moving-elements-with-translate-is-better-than-posabs-topleft/
      Plugin.prototype.setTopPosition = function( _top_ ) {
            _top_ = _top_ || 0;
            this.element.css({
                  // 'transform' : 'translate3d(0px, ' + _top_  + 'px, .01px)',
                  // '-webkit-transform' : 'translate3d(0px, ' + _top_  + 'px, .01px)'
                  'background-position-y' : ( -1 * _top_ ) + 'px',
                  'background-attachment' : 'fixed',
                  //'background-size' : 'auto ' + this.element.outerHeight() + 'px'
                  //top: _top_
            });
      };

      Plugin.prototype.parallaxMe = function() {
            //parallax only the current slide if in slider context?
            /*
            if ( ! ( this.element.hasClass( 'is-selected' ) || this.element.parent( '.is-selected' ).length ) )
              return;
            */
            var $element       = this.element;
            var ratio = this.options.parallaxRatio,
                parallaxDirection = this.options.parallaxDirection,
                ElementDistanceToTop  = $element.offset().top,
                value = ratio * parallaxDirection * ( this.$_document.scrollTop() - ElementDistanceToTop );

            this.setTopPosition( parallaxDirection * value );
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