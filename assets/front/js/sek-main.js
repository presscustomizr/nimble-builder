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
        $this.css('font-size', Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)));
      };

      // Call once to set.
      resizer();

      // Call on resize. Opera debounces their resize by default.
      $(window).on('resize.fittext orientationchange.fittext', resizer);

    });
  };

})( jQuery );


jQuery( function($){
    var doFitText = function() {
          $(".sek-module-placeholder").each( function() {
                $(this).fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
          });
          // Delegate instantiation
          $('.sektion-wrapper').on(
                'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-refresh-level',
                'div[data-sek-level="section"]',
                function( evt ) {
                      $(this).find(".sek-module-placeholder").fitText( 0.4, { minFontSize: '50px', maxFontSize: '300px' } ).data('sek-fittext-done', true );
                }
          );

    };
    //doFitText();
    // if ( 'function' == typeof(_) && ! _.isUndefined( wp.customize ) ) {
    //     wp.customize.selectiveRefresh.bind('partial-content-rendered' , function() {
    //         doFitText();
    //     });
    // }

    // animate menu item to Nimble anchors
    $('body').on( 'click', '.menu .menu-item [href^="#"]', function( evt){
          evt.preventDefault();
          var anchorCandidate = $(this).attr('href');
          anchorCandidate = _.isString( anchorCandidate ) ? anchorCandidate.replace('#','') : '';

          if ( !_.isEmpty( anchorCandidate ) ) {
                var $anchorCandidate = $('[data-sek-level="location"]' ).find( '[id="' + anchorCandidate + '"]');
                if ( 1 === $anchorCandidate.length ) {
                      $('html, body').animate({
                            scrollTop : $anchorCandidate.offset().top - 150
                      }, 'slow');
                }
          }
    });

});








/* ===================================================
 * jquerynimbleLazyLoad.js v1.0.0
 * ===================================================
 *
 * Replace all img src placeholder in the $element by the real src on scroll window event
 * Bind a 'smartload' event on each transformed img
 *
 * Note : the data-src (data-srcset) attr has to be pre-processed before the actual page load
 * Example of regex to pre-process img server side with php :
 * preg_replace_callback('#<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>#', 'regex_callback' , $_html)
 *
 * (c) 2018 Nicolas Guillaume, Nice, France
 *
 * Example of gif 1px x 1px placeholder :
 * 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
 *
 * inspired by the work of Luís Almeida
 * http://luis-almeida.github.com/unveil
 *
 * Requires requestAnimationFrame polyfill:
 * http://paulirish.com/2011/requestanimationframe-for-smart-animating/
 * =================================================== */
(function ( $, window ) {
      //defaults
      var pluginName = 'nimbleLazyLoad',
          defaults = {
                load_all_images_on_first_scroll : false,
                //attribute : [ 'data-sek-src' ],
                excludeImg : [],
                threshold : 200,
                fadeIn_options : { duration : 400 },
                delaySmartLoadEvent : 0,

          },
          //with intersecting cointainers:
          //- to avoid race conditions
          //- to avoid multi processing in general
          skipImgClass = 'tc-smart-load-skip';


      function Plugin( element, options ) {
            this.element = element;
            this.options = $.extend( {}, defaults, options) ;
            //add .tc-smart-load-skip to the excludeImg
            if ( _.isArray( this.options.excludeImg ) ) {
                  this.options.excludeImg.push( '.'+skipImgClass );
            } else {
                  this.options.excludeImg = [ '.'+skipImgClass ];
            }

            this._defaults = defaults;
            this._name = pluginName;
            this.init();
      }


      //can access this.element and this.option
      Plugin.prototype.init = function () {
            var self        = this,
                $_bgElements   = $( '[data-sek-src]:not('+ this.options.excludeImg.join() +')' , this.element );

            this.increment  = 1;//used to wait a little bit after the first user scroll actions to trigger the timer
            this.timer      = 0;


            $_bgElements
                  //avoid intersecting containers to parse the same images
                  .addClass( skipImgClass )
                  //attach action to the load event
                  .bind( 'load_bg_img', {}, function() {
                        self._load_img(this);
                  });

            //the scroll event gets throttled with the requestAnimationFrame
            $(window).scroll( function( _evt ) { self._better_scroll_event_handler( $_bgElements, _evt ); } );
            //debounced resize event
            $(window).resize( _.debounce( function( _evt ) { self._maybe_trigger_load( $_bgElements, _evt ); }, 100 ) );
            //on load
            this._maybe_trigger_load( $_bgElements );
      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      * scroll event performance enhancer => avoid browser stack if too much scrolls
      */
      Plugin.prototype._better_scroll_event_handler = function( $_bgElements , _evt ) {
            var self = this;
            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self._maybe_trigger_load( $_bgElements , _evt );
                        self.doingAnimation = false;
                  });
            }
      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      */
      Plugin.prototype._maybe_trigger_load = function( $_bgElements , _evt ) {
            var self = this,
                //get the visible images list
                _visible_list = $_bgElements.filter( function( ind, _el ) { return self._is_visible( _el ,  _evt ); } );
            //trigger load_bg_img event for visible images
            _visible_list.map( function( ind, _el ) {
                  $(_el).trigger( 'load_bg_img' );
            });
      };


      /*
      * @param single $img object
      * @param : current event
      * @return bool
      * helper to check if an image is the visible ( viewport + custom option threshold)
      */
      Plugin.prototype._is_visible = function( element, _evt ) {
            var $element       = $(element),
                wt = $(window).scrollTop(),
                wb = wt + $(window).height(),
                it  = $element.offset().top,
                ib  = it + $element.height(),
                th = this.options.threshold;

            //force all images to visible if first scroll option enabled
            if ( _evt && 'scroll' == _evt.type && this.options.load_all_images_on_first_scroll )
              return true;

            return ib >= wt - th && it <= wb + th;
      };


      /*
      * @param single $img object
      * @return void
      * replace src place holder by data-src attr val which should include the real src
      */
      Plugin.prototype._load_img = function( _el_ ) {
            var $_el_    = $(_el_),
                _src     = $_el_.attr( 'data-sek-src' ),
                self = this,
                $jQueryImgToLoad = $("<img />", { src : _src } );

            $_el_.addClass('lazy-loading');
            $_el_.unbind('load_bg_img');

            $jQueryImgToLoad
                  // .hide()
                  // //https://api.jquery.com/removeAttr/
                  // //An attribute to remove; as of version 1.7, it can be a space-separated list of attributes.
                  // //minimum supported wp version (3.4+) embeds jQuery 1.7.2
                  // .removeAttr( this.options.attribute.join(' ') )
                  //.css( 'src', _src )
                  .load( function () {
                        if($_el_.data("sek-lazy-bg")){
                              $_el_.css('backgroundImage', 'url('+_src+')');
                        } else {
                              $_el_.attr("src", _src );
                        }
                        //prevent executing this twice on an already smartloaded img
                        if ( ! $_el_.hasClass('bg-lazy-loaded') ) {
                              $_el_.addClass('bg-lazy-loaded');
                        }
                        //Following would be executed twice if needed, as some browsers at the
                        //first execution of the load callback might still have not actually loaded the img

                        $_el_.trigger('smartload');
                        //flag to avoid double triggering
                        $_el_.data('bg-lazy-loaded', true );
                  });//<= create a load() fn
            //http://stackoverflow.com/questions/1948672/how-to-tell-if-an-image-is-loaded-or-cached-in-jquery
            if ( $jQueryImgToLoad[0].complete ) {
                  $jQueryImgToLoad.load();
            }
            $_el_.removeClass('lazy-loading');
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


jQuery(function($){
      $('.sektion-wrapper').each( function() {
          $(this).nimbleLazyLoad();
      });
});