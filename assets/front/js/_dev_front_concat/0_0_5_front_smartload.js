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
                        threshold : 200,
                        fadeIn_options : { duration : 400 },
                        delaySmartLoadEvent : 0,

                  },
                  //with intersecting cointainers:
                  //- to avoid race conditions
                  //- to avoid multi processing in general
                  skipLazyLoadClass = 'smartload-skip';


              function Plugin( element, options ) {
                    if ( !sekFrontLocalized.lazyload_enabled )
                      return;

                    this.element = element;
                    this.options = $.extend( {}, defaults, options) ;
                    // //add .smartload-skip to the excludeImg
                    // if ( nb_.isArray( this.options.excludeImg ) ) {
                    //       this.options.excludeImg.push( '.'+skipLazyLoadClass );
                    // } else {
                    //       this.options.excludeImg = [ '.'+skipLazyLoadClass ];
                    // }

                    this._defaults = defaults;
                    this._name = pluginName;
                    var self = this;
                    // 'nb-trigger-lazyload' can be fired from
                    $(this.element).on('nb-trigger-lazyload', function() {
                          self.init();
                    });
                    this.init();
              }


              //can access this.element and this.option
              Plugin.prototype.init = function () {
                    var self        = this,
                        // img to be lazy loaded looks like data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7
                        // [src*="data:image"] =>
                        // [data-sek-src*="http"] => background images, images in image modules, wp editor module, post grids, slider module, etc..
                        // [data-sek-iframe-src] => ?
                        $_ImgOrDivOrIFrameElements  = $( '[data-sek-src*="http"], [data-sek-iframe-src]' , this.element );
                    // flag so we can check wether his element has been lazyloaded
                    $(this.element).data('nimbleLazyLoadDone', true );

                    this.increment  = 1;//used to wait a little bit after the first user scroll actions to trigger the timer
                    this.timer      = 0;

                    $_ImgOrDivOrIFrameElements
                          //avoid intersecting containers to parse the same images
                          // .addClass( skipLazyLoadClass )
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
                        // get the visible images list
                        // don't apply a threshold on page load so that Google audit is happy
                        // for https://github.com/presscustomizr/nimble-builder/issues/619
                        threshold = ( _evt && 'scroll' === _evt.type ) ? this.options.threshold : 0;

                        _visible_list = $_Elements.filter( function( ind, _el ) {
                            //force all images to visible if first scroll option enabled
                            if ( _evt && 'scroll' == _evt.type && self.options.load_all_images_on_first_scroll )
                              return true;
                            return nb_.elOrFirstVisibleParentIsInWindow( _el, threshold );
                        });

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
                                self._clean_css_loader( $_el );

                          });//<= create a load() fn
                    //http://stackoverflow.com/questions/1948672/how-to-tell-if-an-image-is-loaded-or-cached-in-jquery
                    if ( $jQueryImgToLoad[0].complete ) {
                          $jQueryImgToLoad.trigger( 'load' );
                    }
                    $_el.removeClass('lazy-loading');
              };

              Plugin.prototype._clean_css_loader = function( $_el ) {
                    // maybe remove the CSS loader
                    $.each( [ $_el.find('.sek-css-loader'),  $_el.parent().find('.sek-css-loader') ], function( k, $_el ) {
                        if ( $_el.length > 0 )
                          $_el.remove();
                    });
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

      };////////////// callbackFunc
      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', function(){
          callbackFunc();
          if ( sekFrontLocalized.lazyload_enabled ) { nb_.emit('nb-lazyload-parsed'); }
      });
}(window, document));