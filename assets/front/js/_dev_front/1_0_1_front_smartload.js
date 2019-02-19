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
          skipImgClass = 'smartload-skip';


      function Plugin( element, options ) {
            this.element = element;
            this.options = $.extend( {}, defaults, options) ;
            //add .smartload-skip to the excludeImg
            if ( _utils_.isArray( this.options.excludeImg ) ) {
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
                $_ImgOrBackgroundElements   = $( '[data-sek-src]:not('+ this.options.excludeImg.join() +')' , this.element );

            this.increment  = 1;//used to wait a little bit after the first user scroll actions to trigger the timer
            this.timer      = 0;

            $_ImgOrBackgroundElements
                  //avoid intersecting containers to parse the same images
                  .addClass( skipImgClass )
                  //attach action to the load event
                  .bind( 'sek_load_img', {}, function() {
                        self._load_img(this);
                  });

            //the scroll event gets throttled with the requestAnimationFrame
            $(window).scroll( function( _evt ) { self._better_scroll_event_handler( $_ImgOrBackgroundElements, _evt ); } );
            //debounced resize event
            $(window).resize( _utils_.debounce( function( _evt ) { self._maybe_trigger_load( $_ImgOrBackgroundElements, _evt ); }, 100 ) );
            //on load
            this._maybe_trigger_load( $_ImgOrBackgroundElements );
      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      * scroll event performance enhancer => avoid browser stack if too much scrolls
      */
      Plugin.prototype._better_scroll_event_handler = function( $_ImgOrBackgroundElements , _evt ) {
            var self = this;
            if ( ! this.doingAnimation ) {
                  this.doingAnimation = true;
                  window.requestAnimationFrame(function() {
                        self._maybe_trigger_load( $_ImgOrBackgroundElements , _evt );
                        self.doingAnimation = false;
                  });
            }
      };


      /*
      * @param : array of $img
      * @param : current event
      * @return : void
      */
      Plugin.prototype._maybe_trigger_load = function( $_ImgOrBackgroundElements , _evt ) {
            var self = this,
                //get the visible images list
                _visible_list = $_ImgOrBackgroundElements.filter( function( ind, _el ) { return self._is_visible( _el ,  _evt ); } );
            //trigger sek_load_img event for visible images
            _visible_list.map( function( ind, _el ) {
                  $(_el).trigger( 'sek_load_img' );
            });
      };


      /*
      * @param single $img object
      * @param : current event
      * @return bool
      * helper to check if an image is the visible ( viewport + custom option threshold)
      * Note that this helper is not able to determine the visibility of elements set to display:none;
      */
      Plugin.prototype._is_visible = function( element, _evt ) {
            var sniffFirstVisiblePrevElement = function( $el ) {
                  if ( $el.length > 0 && $el.is('visible') )
                    return $el;
                  var $prev = $el.prev();
                  // if there's a previous sibling and this sibling is visible, use it
                  if ( $prev.length > 0 && $prev.is(':visible') ) {
                      return $prev;
                  }
                  // if there's a previous sibling but it's not visible, let's try the next previous sibling
                  if ( $prev.length > 0 && !$prev.is('visible') ) {
                      return sniffFirstVisiblePrevElement( $prev );
                  }
                  // if no previous sibling visible, let's go up the parent level
                  var $parent = $el.parent();
                  if ( $parent.length > 0 ) {
                      return sniffFirstVisiblePrevElement( $parent );
                  }
                  // we don't have siblings or parent
                  return null;
            };

            // Is the candidate visible ? <= not display:none
            // If not visible, we can't determine the offset().top because of https://github.com/presscustomizr/nimble-builder/issues/363
            // So let's sniff up in the DOM to find the first visible sibling or container
            var $el_candidate = sniffFirstVisiblePrevElement( $(element) );
            if ( $el_candidate.length < 1 )
              return false;

            var wt = $(window).scrollTop(),
                wb = wt + $(window).height(),
                it  = $el_candidate.offset().top,
                ib  = it + $el_candidate.height(),
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
            var $_el    = $(_el_),
                _src     = $_el.attr( 'data-sek-src' ),
                _src_set = $_el.attr( 'data-sek-srcset' ),
                _sizes   = $_el.attr( 'data-sek-sizes' ),
                self = this,
                $jQueryImgToLoad = $("<img />", { src : _src } );

            $_el.addClass('lazy-loading');
            $_el.unbind('sek_load_img');

            $jQueryImgToLoad
                  // .hide()
                  .load( function () {
                        //https://api.jquery.com/removeAttr/
                        //An attribute to remove; as of version 1.7, it can be a space-separated list of attributes.
                        //minimum supported wp version (3.4+) embeds jQuery 1.7.2
                        $_el.removeAttr( [ 'data-sek-src', 'data-sek-srcset', 'data-sek-sizes' ].join(' ') );
                        if( $_el.data("sek-lazy-bg") ){
                              $_el.css('backgroundImage', 'url('+_src+')');
                        } else {
                              $_el.attr("src", _src );
                              if ( _src_set ) {
                                    $_el.attr("srcset", _src_set );
                              }
                              if ( _sizes ) {
                                    $_el.attr("sizes", _sizes );
                              }
                        }
                        //prevent executing this twice on an already smartloaded img
                        if ( ! $_el.hasClass('sek-lazy-loaded') ) {
                              $_el.addClass('sek-lazy-loaded');
                        }
                        //Following would be executed twice if needed, as some browsers at the
                        //first execution of the load callback might still have not actually loaded the img

                        $_el.trigger('smartload');
                        //flag to avoid double triggering
                        $_el.data('sek-lazy-loaded', true );
                  });//<= create a load() fn
            //http://stackoverflow.com/questions/1948672/how-to-tell-if-an-image-is-loaded-or-cached-in-jquery
            if ( $jQueryImgToLoad[0].complete ) {
                  $jQueryImgToLoad.load();
            }
            $_el.removeClass('lazy-loading');
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