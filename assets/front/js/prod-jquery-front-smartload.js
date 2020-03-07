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
 *
 * Feb 2019 : added support for iframe lazyloading for https://github.com/presscustomizr/nimble-builder/issues/361
 * =================================================== */
// global sekFrontLocalized, nimbleListenTo
(function(w, d){
      var callbackFunc = function() {
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
                  skipLazyLoadClass = 'smartload-skip';


              function Plugin( element, options ) {
                    this.element = element;
                    this.options = $.extend( {}, defaults, options) ;
                    //add .smartload-skip to the excludeImg
                    if ( nb_.isArray( this.options.excludeImg ) ) {
                          this.options.excludeImg.push( '.'+skipLazyLoadClass );
                    } else {
                          this.options.excludeImg = [ '.'+skipLazyLoadClass ];
                    }

                    this._defaults = defaults;
                    this._name = pluginName;
                    this.init();
              }


              //can access this.element and this.option
              Plugin.prototype.init = function () {
                    var self        = this,
                        $_ImgOrDivOrIFrameElements  = $( '[data-sek-src]:not('+ this.options.excludeImg.join() +'), [data-sek-iframe-src]' , this.element );

                    this.increment  = 1;//used to wait a little bit after the first user scroll actions to trigger the timer
                    this.timer      = 0;

                    $_ImgOrDivOrIFrameElements
                          //avoid intersecting containers to parse the same images
                          .addClass( skipLazyLoadClass )
                          .bind( 'sek_load_img', {}, function() { self._load_img(this); })
                          .bind( 'sek_load_iframe', {}, function() { self._load_iframe(this); });

                    //the scroll event gets throttled with the requestAnimationFrame
                    nb_.cachedElements.$window.scroll( function( _evt ) {
                          self._better_scroll_event_handler( $_ImgOrDivOrIFrameElements, _evt );
                    });
                    //debounced resize event
                    nb_.cachedElements.$window.resize( nb_.debounce( function( _evt ) {
                          self._maybe_trigger_load( $_ImgOrDivOrIFrameElements, _evt );
                    }, 100 ) );
                    //on load
                    this._maybe_trigger_load( $_ImgOrDivOrIFrameElements);

              };


              /*
              * @param : array of $img
              * @param : current event
              * @return : void
              * scroll event performance enhancer => avoid browser stack if too much scrolls
              */
              Plugin.prototype._better_scroll_event_handler = function( $_Elements , _evt ) {
                    var self = this;
                    if ( ! this.doingAnimation ) {
                          this.doingAnimation = true;
                          window.requestAnimationFrame(function() {
                                self._maybe_trigger_load( $_Elements , _evt );
                                self.doingAnimation = false;
                          });
                    }
              };


              /*
              * @param : array of $img
              * @param : current event
              * @return : void
              */
              Plugin.prototype._maybe_trigger_load = function( $_Elements , _evt ) {
                    var self = this,
                        //get the visible images list
                        _visible_list = $_Elements.filter( function( ind, _el ) { return self._is_visible( _el ,  _evt ); } );

                    _visible_list.map( function( ind, _el ) {
                          if ( 'IFRAME' === $(_el).prop("tagName") ) {
                                $(_el).trigger( 'sek_load_iframe' );
                          } else {
                                $(_el).trigger( 'sek_load_img' );
                          }
                    });
              };


              /*
              * @param single $img object
              * @param : current event
              * @return bool
              * helper to check if an image is the visible ( viewport + custom option threshold)
              */
              Plugin.prototype._is_visible = function( element, _evt ) {
                    var sniffFirstVisiblePrevElement = function( $el ) {
                          if ( $el.length > 0 && $el.is(':visible') )
                            return $el;
                          var $prev = $el.prev();
                          // if there's a previous sibling and this sibling is visible, use it
                          if ( $prev.length > 0 && $prev.is(':visible') ) {
                              return $prev;
                          }
                          // if there's a previous sibling but it's not visible, let's try the next previous sibling
                          if ( $prev.length > 0 && !$prev.is(':visible') ) {
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
                    if ( !$el_candidate || $el_candidate.length < 1 )
                      return false;

                    var wt = nb_.cachedElements.$window.scrollTop(),
                        wb = wt + nb_.cachedElements.$window.height(),
                        it  = $el_candidate.offset().top,
                        ib  = it + $el_candidate.height(),
                        // don't apply a threshold on page load so that Google audit is happy
                        // for https://github.com/presscustomizr/nimble-builder/issues/619
                        th = ( _evt && 'scroll' === _evt.type ) ? this.options.threshold : 0;

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
                          .on( 'load', function () {
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
                          $jQueryImgToLoad.trigger( 'load' );
                    }
                    $_el.removeClass('lazy-loading');
              };


              /*
              * @param single iframe el object
              * @return void
              */
              Plugin.prototype._load_iframe = function( _el_ ) {
                    var $_el    = $(_el_),
                        self = this;

                    //$_el.addClass('lazy-loading');
                    $_el.unbind('sek_load_iframe');

                    $_el.attr( 'src', function() {
                          var src = $(this).attr('data-sek-iframe-src');
                          $(this).removeAttr('data-sek-iframe-src');
                          $_el.data('sek-lazy-loaded', true );
                          $_el.trigger('smartload');
                          if ( ! $_el.hasClass('sek-lazy-loaded') ) {
                                $_el.addClass('sek-lazy-loaded');
                          }
                          return src;
                    });
                    //$_el.removeClass('lazy-loading');
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

      };////////////// onJQueryReady
      window.nb_.listenTo('nimble-app-ready', callbackFunc );
}(window, document));
