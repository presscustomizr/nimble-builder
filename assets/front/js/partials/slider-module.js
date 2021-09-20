// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SWIPER CAROUSEL implemented for the simple slider module czr_img_slider_module
 *  doc : https://swiperjs.com/api/
 *  dependency : $.fn.nimbleCenterImages()
/* ------------------------------------------------------------------------- */

(function(w, d){
      var callbackFunc = function() {
          jQuery( function($){
              var mySwipers = [];
              var triggerSimpleLoad = function( $_imgs ) {
                    if ( 0 === $_imgs.length )
                      return;

                    $_imgs.map( function( _ind, _img ) {
                      $(_img).load( function () {
                        $(_img).trigger('simple_load');
                      });//end load
                      if ( $(_img)[0] && $(_img)[0].complete )
                        $(_img).load();
                    } );//end map
              };//end of fn


              // Each swiper is instantiated with a unique id
              // so that if we have several instance on the same page, they are totally independant.
              // If we don't use a unique Id for swiper + navigation buttons, a click on a button, make all slider move synchronously.
              var doSingleSwiperInstantiation = function() {
                    var $swiperWrapper = $(this), swiperClass = 'sek-swiper' + $swiperWrapper.data('sek-swiper-id');
                    var swiperParams = {
                        // slidesPerView: 3,
                        // spaceBetween: 30,
                        loop : true === $swiperWrapper.data('sek-loop') && true === $swiperWrapper.data('sek-is-multislide'),//Set to true to enable continuous loop mode
                        grabCursor : true === $swiperWrapper.data('sek-is-multislide'),
                        on : {
                              init : function() {
                                    // remove the .sek-swiper-loading class from the wrapper => remove the display:none rule
                                    $swiperWrapper.removeClass('sek-swiper-loading');

                                    // remove the css loader
                                    $swiperWrapper.parent().find('.sek-css-loader').remove();

                                    // lazy load the first slider image with Nimble if not done already
                                    // the other images will be lazy loaded by swiper if the option is activated
                                    // if ( sekFrontLocalized.lazyload_enabled && $.fn.nimbleLazyLoad ) {

                                    // }
                                    $swiperWrapper.trigger('nb-trigger-lazyload');

                                    // center images with Nimble wizard when needed
                                    if ( 'nimble-wizard' === $swiperWrapper.data('sek-image-layout') ) {
                                          $swiperWrapper.find('.sek-carousel-img').each( function() {
                                                var $_imgsToSimpleLoad = $(this).nimbleCenterImages({
                                                      enableCentering : 1,
                                                      zeroTopAdjust: 0,
                                                      setOpacityWhenCentered : false,//will set the opacity to 1
                                                      oncustom : [ 'simple_load', 'smartload', 'sek-nimble-refreshed', 'recenter']
                                                })
                                                //images with src which starts with "data" are our smartload placeholders
                                                //we don't want to trigger the simple_load on them
                                                //the centering, will be done on the smartload event (see onCustom above)
                                                .find( 'img:not([src^="data"])' );

                                                //trigger the simple load
                                                nb_.delay( function() {
                                                    triggerSimpleLoad( $_imgsToSimpleLoad );
                                                }, 50 );
                                          });//each()
                                    }
                              },// init
                              // make sure slides are always lazyloaded
                              slideChange : function(params) {
                                  // when lazy load is active, we want to lazy load the first image of the slider if offscreen
                                  // img to be lazy loaded looks like data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7
                                  $swiperWrapper.trigger('nb-trigger-lazyload');

                                  if ( $swiperWrapper.find('[src*="data:image/gif;"]').length > 0 ) {
                                      // Make sure we load clean lazy loaded slides on change
                                      // for https://github.com/presscustomizr/nimble-builder/issues/677
                                      $swiperWrapper.find('[src*="data:image/gif;"]').each( function() {
                                          var $img = $(this);
                                          if ( $img.attr('data-sek-img-sizes') ) {
                                              $img.attr('sizes', $img.attr('data-sek-img-sizes') );
                                              $img.removeAttr('data-sek-img-sizes');
                                          }
                                          if ( $img.attr('data-src') ) {
                                              $img.attr('src', $img.attr('data-src') );
                                              $img.removeAttr('data-src');
                                          }
                                          if ( $img.attr('data-sek-src') ) {
                                              $img.attr('src', $img.attr('data-sek-src') );
                                              $img.removeAttr('data-sek-src');
                                          }
                                          if ( $img.attr('data-srcset') ) {
                                              $img.attr('srcset', $img.attr('data-srcset') );
                                              $img.removeAttr('data-srcset');
                                          }
                                      });
                                  }
                              }
                        }//on
                    };

                    // AUTOPLAY
                    if ( true === $swiperWrapper.data('sek-autoplay') ) {
                          $.extend( swiperParams, {
                                autoplay : {
                                      delay : $swiperWrapper.data('sek-autoplay-delay'),
                                      disableOnInteraction : $swiperWrapper.data('sek-pause-on-hover')
                                }
                          });
                    } else {
                          $.extend( swiperParams, {
                                autoplay : {
                                      delay : 999999999//<= the autoplay:false doesn't seem to work...
                                }
                          });
                    }

                    // NAVIGATION ARROWS && PAGINATION DOTS
                    if ( true === $swiperWrapper.data('sek-is-multislide') ) {
                        var navType = $swiperWrapper.data('sek-navtype');
                        if ( 'arrows_dots' === navType || 'arrows' === navType ) {
                            $.extend( swiperParams, {
                                navigation: {
                                  nextEl: '.sek-swiper-next' + $swiperWrapper.data('sek-swiper-id'),
                                  prevEl: '.sek-swiper-prev' + $swiperWrapper.data('sek-swiper-id')
                                }
                            });
                        }
                        if ( 'arrows_dots' === navType || 'dots' === navType  ) {
                            $.extend( swiperParams, {
                                pagination: {
                                  el: '.swiper-pagination' + $swiperWrapper.data('sek-swiper-id'),
                                  clickable: true,
                                }
                            });
                        }
                    }

                    // LAZYLOAD @see https://swiperjs.com/api/#lazy
                    if ( true === $swiperWrapper.data('sek-lazyload') ) {
                        $.extend( swiperParams, {
                            // Disable preloading of all images
                            preloadImages: false,
                            lazy : {
                              // By default, Swiper will load lazy images after transition to this slide, so you may enable this parameter if you need it to start loading of new image in the beginning of transition
                              loadOnTransitionStart : true
                            }
                        });
                    }

                  // Prepare Pro slider effects
                  var _effect = '',
                        _duration = 300;//default Swiper value

                  if ( $swiperWrapper && $swiperWrapper.length > 0 ) {
                        _effect = $swiperWrapper.data('sek-slider-effect');
                        _duration =  parseInt( $swiperWrapper.data('sek-effect-duration'), 10 );
                  }
                  if ( _duration > 0 ) {
                        $.extend( swiperParams, {
                              speed : _duration
                        });
                  }
                  if ( nb_.isString( _effect) && 0 !== _effect.length ) {
                        $.extend( swiperParams, {
                              effect: _effect
                        });
                        // See doc here : https://swiperjs.com/swiper-api#fade-effect
                        switch (_effect) {
                              case 'fade':
                                    swiperParams[_effect + 'Effect'] = {
                                    crossFade: true
                              };
                              break;
                              case 'coverflow':
                                    swiperParams[_effect + 'Effect'] = {
                                          rotate: 30,
                                          slideShadows: false
                                    };
                              break;
                              case 'flip':
                              case 'cube':
                                    swiperParams[_effect + 'Effect'] = {
                                          slideShadows: false
                                    };
                              break;
                              case 'cards':
                                    swiperParams[_effect + 'Effect'] = {};
                              break;
                        }
                  }

                    mySwipers.push( new Swiper(
                        '.' + swiperClass,//$(this)[0],
                        swiperParams
                    ));

                    // On Swiper Lazy Loading
                    // https://swiperjs.com/api/#lazy
                    $.each( mySwipers, function( ind, _swiperInstance ){
                          _swiperInstance.on( 'lazyImageReady', function( slideEl, imageEl ) {
                              $(imageEl).trigger('recenter');
                          });
                          _swiperInstance.on( 'lazyImageLoad', function( slideEl, imageEl ) {
                              // clean the extra attribute added when preprocessing for lazy loading
                              var $img = $(imageEl);
                              if ( $img.attr('data-sek-img-sizes') ) {
                                  $img.attr('sizes', $img.attr('data-sek-img-sizes') );
                                  $img.removeAttr('data-sek-img-sizes');
                              }
                              // add 'recenter' events to fix https://github.com/presscustomizr/nimble-builder/issues/855
                              nb_.delay( function() {$img.trigger('recenter');}, 200 );
                              nb_.delay( function() {$img.trigger('recenter');}, 800 );
                          });
                    });

              };

              var doAllSwiperInstanciation = function() {
                    $('.sektion-wrapper').find('[data-sek-swiper-id]').each( function() {
                          doSingleSwiperInstantiation.call($(this));
                    });
              };



              // On custom events
              nb_.cachedElements.$body.on( 'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed', '[data-sek-level="location"]',
                    function(evt) {
                          if ( 0 !== mySwipers.length ) {
                                $.each( mySwipers, function( ind, _swiperInstance ){
                                      _swiperInstance.destroy();
                                });
                          }
                          mySwipers = [];
                          doAllSwiperInstanciation();

                          $(this).find('.swiper img').each( function() {
                                $(this).trigger('sek-nimble-refreshed');
                          });
                    }
              );

              // When the stylesheet is refreshed, update the centering with a custom event
              // this is needed when setting the custom height of the slider wrapper
              nb_.cachedElements.$body.on( 'sek-stylesheet-refreshed', '[data-sek-module-type="czr_img_slider_module"]',
                    function() {
                          $(this).find('.swiper img').each( function() {
                                $(this).trigger('sek-nimble-refreshed');
                          });
                    }
              );


              // on load
              $('.sektion-wrapper').find('.swiper').each( function() {
                    doAllSwiperInstanciation();
              });


              // Action on click
              // $( 'body').on( 'click', '[data-sek-module-type="czr_img_slider_module"]', function(evt ) {
              //         // $(this).find('[data-sek-swiper-id]').each( function() {
              //         //       $(this).trigger('sek-nimble-refreshed');
              //         // });
              //       }
              // );


              // Behaviour on mouse hover
              // @seehttps://stackoverflow.com/questions/53028089/swiper-autoplay-stop-the-swiper-when-you-move-the-mouse-cursor-and-start-playba
              $('.swiper-slide').on('mouseover mouseout', function( evt ) {
                  var swiperInstance = $(this).closest('.swiper')[0].swiper;
                  if ( ! nb_.isUndefined( swiperInstance ) && true === swiperInstance.params.autoplay.disableOnInteraction ) {
                      switch( evt.type ) {
                          case 'mouseover' :
                              swiperInstance.autoplay.stop();
                          break;
                          case 'mouseout' :
                              swiperInstance.autoplay.start();
                          break;
                      }
                  }
              });

              // When customizing, focus on the currently expanded / edited item
              // @see CZRItemConstructor in api.czrModuleMap.czr_img_slider_collection_child
              if ( window.wp && ! nb_.isUndefined( wp.customize ) ) {
                    wp.customize.preview.bind('sek-item-focus', function( params ) {

                          var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]', '.swiper').first();
                          if ( 1 > $itemEl.length )
                            return;
                          var $swiperContainer = $itemEl.closest('.swiper');
                          if ( 1 > $swiperContainer.length )
                            return;

                          var activeSwiperInstance = $itemEl.closest('.swiper')[0].swiper;

                          if ( nb_.isUndefined( activeSwiperInstance ) )
                            return;
                          // we can't rely on internal indexing system of swipe, because it uses duplicate item when infinite looping is enabled
                          // jQuery is our friend
                          var slideIndex = $( '.swiper-slide', $swiperContainer ).index( $itemEl );
                          //http://idangero.us/swiper/api/#methods
                          //mySwiper.slideTo(index, speed, runCallbacks);
                          activeSwiperInstance.slideTo( slideIndex, 100 );
                    });

                    // Trigger a window resize when control send a 'sek-preview-device-changed'
                    // wp.customize.preview.bind('sek-preview-device-changed', nb_.debounce( function( params ) {
                    //       nb_.cachedElements.$window.trigger('resize');
                    // }, 1000 ));
              }
          });











          /* ===================================================
           * jquerynimbleCenterImages.js v1.0.0
           * ( inspired by Customizr theme jQuery plugin )
           * ===================================================
           * (c) 2019 Nicolas Guillaume, Nice, France
           * CenterImages plugin may be freely distributed under the terms of the GNU GPL v2.0 or later license.
           *
           * License URI: http://www.gnu.org/licenses/gpl-2.0.html
           *
           * Center images in a specified container
           *
           * =================================================== */
          (function ( $, window ) {
                //defaults
                var pluginName = 'nimbleCenterImages',
                    defaults = {
                          enableCentering : true,
                          onresize : true,
                          onInit : true,//<= shall we smartload on init or wait for a custom event, typically smartload ?
                          oncustom : [],//list of event here
                          $containerToListen : null,//<= we might want to listen to custom event trigger to a parent container.Should be a jQuery obj
                          imgSel : 'img',
                          defaultCSSVal : { width : 'auto' , height : 'auto' },
                          leftAdjust : 0,
                          zeroLeftAdjust : 0,
                          topAdjust : 0,
                          zeroTopAdjust : -2,//<= top ajustement for sek-h-centrd
                          useImgAttr:false,//uses the img height and width attributes if not visible (typically used for the customizr slider hidden images)
                          setOpacityWhenCentered : false,//this can be used to hide the image during the time it is centered
                          addCenteredClassWithDelay : 0,//<= a small delay can be required when we rely on the sek-v-centrd or sek-h-centrd css classes to set the opacity for example
                          opacity : 1
                    };

                function Plugin( element, options ) {
                      var self = this;
                      this.container  = element;
                      this.options    = $.extend( {}, defaults, options) ;
                      this._defaults  = defaults;
                      this._name      = pluginName;
                      this._customEvt = nb_.isArray(self.options.oncustom) ? self.options.oncustom : self.options.oncustom.split(' ');
                      this.init();
                }

                //can access this.element and this.option
                //@return void
                Plugin.prototype.init = function () {
                      var self = this,
                          _do = function( _event_ ) {
                              _event_ = _event_ || 'init';

                              //parses imgs ( if any ) in current container
                              var $_imgs = $( self.options.imgSel , self.container );

                              //if no images or centering is not active, only handle the golden ratio on resize event
                              if ( 1 <= $_imgs.length && self.options.enableCentering ) {
                                    self._parse_imgs( $_imgs, _event_ );
                              }
                          };

                      //fire
                      if ( self.options.onInit ) {
                            _do();
                      }

                      //bind the container element with custom events if any
                      //( the images will also be bound )
                      if ( nb_.isArray( self._customEvt ) ) {
                            self._customEvt.map( function( evt ) {
                                  var $_containerToListen = ( self.options.$containerToListen instanceof $ && 1 < self.options.$containerToListen.length ) ? self.options.$containerToListen : $( self.container );
                                  $_containerToListen.bind( evt, {} , function() {
                                        _do( evt );
                                  });
                            } );
                      }
                };


                //@return void
                Plugin.prototype._parse_imgs = function( $_imgs, _event_ ) {
                      var self = this;
                      $_imgs.each(function ( ind, img ) {
                            var $_img = $(img);
                            self._pre_img_cent( $_img, _event_ );

                            // IMG CENTERING FN ON RESIZE ?
                            // Parse Img can be fired several times, so bind once
                            if ( self.options.onresize && ! $_img.data('resize-react-bound' ) ) {
                                  $_img.data('resize-react-bound', true );
                                  nb_.cachedElements.$window.on('resize', nb_.debounce( function() {
                                        self._pre_img_cent( $_img, 'resize');
                                  }, 100 ) );
                            }

                      });//$_imgs.each()

                      // Mainly designed to check if a container is not getting parsed too many times
                      if ( $(self.container).attr('data-img-centered-in-container') ) {
                            var _n = parseInt( $(self.container).attr('data-img-centered-in-container'), 10 ) + 1;
                            $(self.container).attr('data-img-centered-in-container', _n );
                      } else {
                            $(self.container).attr('data-img-centered-in-container', 1 );
                      }
                };



                //@return void
                Plugin.prototype._pre_img_cent = function( $_img ) {

                      var _state = this._get_current_state( $_img ),
                          self = this,
                          _case  = _state.current,
                          _p     = _state.prop[_case],
                          _not_p = _state.prop[ 'h' == _case ? 'v' : 'h'],
                          _not_p_dir_val = 'h' == _case ? ( this.options.zeroTopAdjust || 0 ) : ( this.options.zeroLeftAdjust || 0 );

                      var _centerImg = function( $_img ) {
                            $_img
                                .css( _p.dim.name , _p.dim.val )
                                .css( _not_p.dim.name , self.options.defaultCSSVal[ _not_p.dim.name ] || 'auto' )
                                .css( _p.dir.name, _p.dir.val ).css( _not_p.dir.name, _not_p_dir_val );

                            if ( 0 !== self.options.addCenteredClassWithDelay && nb_.isNumber( self.options.addCenteredClassWithDelay ) ) {
                                  nb_.delay( function() {
                                        $_img.addClass( _p._class ).removeClass( _not_p._class );
                                  }, self.options.addCenteredClassWithDelay );
                            } else {
                                  $_img.addClass( _p._class ).removeClass( _not_p._class );
                            }

                            // Mainly designed to check if a single image is not getting parsed too many times
                            if ( $_img.attr('data-img-centered') ) {
                                  var _n = parseInt( $_img.attr('data-img-centered'), 10 ) + 1;
                                  $_img.attr('data-img-centered', _n );
                            } else {
                                  $_img.attr('data-img-centered', 1 );
                            }
                            return $_img;
                      };
                      if ( this.options.setOpacityWhenCentered ) {
                            $.when( _centerImg( $_img ) ).done( function( $_img ) {
                                  $_img.css( 'opacity', self.options.opacity );
                            });
                      } else {
                            nb_.delay(function() { _centerImg( $_img ); }, 0 );
                      }
                };




                /********
                * HELPERS
                *********/
                //@return object with initial conditions : { current : 'h' or 'v', prop : {} }
                Plugin.prototype._get_current_state = function( $_img ) {
                      var c_x     = $_img.closest(this.container).outerWidth(),
                          c_y     = $(this.container).outerHeight(),
                          i_x     = this._get_img_dim( $_img , 'x'),
                          i_y     = this._get_img_dim( $_img , 'y'),
                          up_i_x  = i_y * c_y !== 0 ? Math.round( i_x / i_y * c_y ) : c_x,
                          up_i_y  = i_x * c_x !== 0 ? Math.round( i_y / i_x * c_x ) : c_y,
                          current = 'h';
                      //avoid dividing by zero if c_x or i_x === 0
                      if ( 0 !== c_x * i_x ) {
                            current = ( c_y / c_x ) >= ( i_y / i_x ) ? 'h' : 'v';
                      }

                      var prop    = {
                            h : {
                                  dim : { name : 'height', val : c_y },
                                  dir : { name : 'left', val : ( c_x - up_i_x ) / 2 + ( this.options.leftAdjust || 0 ) },
                                  _class : 'sek-h-centrd'
                            },
                            v : {
                                  dim : { name : 'width', val : c_x },
                                  dir : { name : 'top', val : ( c_y - up_i_y ) / 2 + ( this.options.topAdjust || 0 ) },
                                  _class : 'sek-v-centrd'
                            }
                      };

                      return { current : current , prop : prop };
                };

                //@return img height or width
                //uses the img height and width if not visible and set in options
                Plugin.prototype._get_img_dim = function( $_img, _dim ) {
                      if ( ! this.options.useImgAttr )
                        return 'x' == _dim ? $_img.outerWidth() : $_img.outerHeight();

                      if ( $_img.is(":visible") ) {
                            return 'x' == _dim ? $_img.outerWidth() : $_img.outerHeight();
                      } else {
                            if ( 'x' == _dim ){
                                  var _width = $_img.originalWidth();
                                  return typeof _width === undefined ? 0 : _width;
                            }
                            if ( 'y' == _dim ){
                                  var _height = $_img.originalHeight();
                                  return typeof _height === undefined ? 0 : _height;
                            }
                      }
                };

                /*
                * @params string : ids or classes
                * @return boolean
                */
                Plugin.prototype._is_selector_allowed = function() {
                      //has requested sel ?
                      if ( ! $(this.container).attr( 'class' ) )
                        return true;

                      var _elSels       = $(this.container).attr( 'class' ).split(' '),
                          _selsToSkip   = [],
                          _filtered     = _elSels.filter( function(classe) { return -1 != $.inArray( classe , _selsToSkip ) ;});

                      //check if the filtered selectors array with the non authorized selectors is empty or not
                      //if empty => all selectors are allowed
                      //if not, at least one is not allowed
                      return 0 === _filtered.length;
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
      };/////////////// callbackFunc

      // When loaded with defer, we can not be sure that jQuery will be loaded before
      // so let's make sure that we have both the plugin and jQuery loaded
      nb_.listenTo( 'nb-app-ready', function() {
          // on 'nb-app-ready', jQuery is loaded
          nb_.listenTo( 'nb-main-swiper-parsed', callbackFunc );
      });
}(window, document));