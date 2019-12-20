// global sekFrontLocalized
/* ------------------------------------------------------------------------- *
 *  SWIPER CAROUSEL implemented for the simple slider module czr_img_slider_module
 *  dependency : $.fn.nimbleCenterImages()
/* ------------------------------------------------------------------------- */
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
                    // center images with Nimble wizard when needed
                    if ( 'nimble-wizard' === $swiperWrapper.data('sek-image-layout') ) {
                        $swiperWrapper.find('.sek-carousel-img').each( function() {
                            var $_imgsToSimpleLoad = $(this).nimbleCenterImages({
                                  enableCentering : 1,
                                  zeroTopAdjust: 0,
                                  setOpacityWhenCentered : false,//will set the opacity to 1
                                  oncustom : [ 'simple_load', 'smartload', 'sek-nimble-refreshed' ]
                            })
                            //images with src which starts with "data" are our smartload placeholders
                            //we don't want to trigger the simple_load on them
                            //the centering, will be done on the smartload event (see onCustom above)
                            .find( 'img:not([src^="data"])' );

                            //trigger the simple load
                            _utils_.delay( function() {
                                triggerSimpleLoad( $_imgsToSimpleLoad );
                            }, 10 );
                        });//each()
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
              if ( _utils_.contains( ['arrows_dots', 'arrows'], $swiperWrapper.data('sek-navtype') ) ) {
                  $.extend( swiperParams, {
                      navigation: {
                        nextEl: '.sek-swiper-next' + $swiperWrapper.data('sek-swiper-id'),
                        prevEl: '.sek-swiper-prev' + $swiperWrapper.data('sek-swiper-id')
                      }
                  });
              }
              if ( _utils_.contains( ['arrows_dots', 'dots'], $swiperWrapper.data('sek-navtype') ) ) {
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

          mySwipers.push( new Swiper(
              '.' + swiperClass,//$(this)[0],
              swiperParams
          ));
    };
    var doAllSwiperInstanciation = function() {
          $('.sektion-wrapper').find('[data-sek-swiper-id]').each( function() {
                doSingleSwiperInstantiation.call($(this));
          });
    };

    // On custom events
    $( 'body').on( 'sek-columns-refreshed sek-modules-refreshed sek-section-added sek-level-refreshed', '[data-sek-level="location"]',
          function() {
            if ( ! _utils_.isEmpty( mySwipers ) ) {
                  _utils_.each( mySwipers, function( _swiperInstance ){
                        _swiperInstance.destroy();
                  });
            }
            mySwipers = [];
            doAllSwiperInstanciation();

            $(this).find('.swiper-container img').each( function() {
                  $(this).trigger('sek-nimble-refreshed');
            });
          }
    );

    // When the stylesheet is refreshed, update the centering with a custom event
    // this is needed when setting the custom height of the slider wrapper
    $( 'body').on( 'sek-stylesheet-refreshed', '[data-sek-module-type="czr_img_slider_module"]',
          function() {
            $(this).find('.swiper-container img').each( function() {
                  $(this).trigger('sek-nimble-refreshed');
            });
          }
    );


    // on load
    $('.sektion-wrapper').find('.swiper-container').each( function() {
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
        var swiperInstance = $(this).closest('.swiper-container')[0].swiper;
        if ( ! _utils_.isUndefined( swiperInstance ) && true === swiperInstance.params.autoplay.disableOnInteraction ) {
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
    if ( window.wp && ! _utils_.isUndefined( wp.customize ) ) {
          wp.customize.preview.bind('sek-item-focus', function( params ) {

                var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]', '.swiper-container').first();
                if ( 1 > $itemEl.length )
                  return;
                var $swiperContainer = $itemEl.closest('.swiper-container');
                if ( 1 > $swiperContainer.length )
                  return;

                var activeSwiperInstance = $itemEl.closest('.swiper-container')[0].swiper;

                if ( _utils_.isUndefined( activeSwiperInstance ) )
                  return;
                // we can't rely on internal indexing system of swipe, because it uses duplicate item when infinite looping is enabled
                // jQuery is our friend
                var slideIndex = $( '.swiper-slide', $swiperContainer ).index( $itemEl );
                //http://idangero.us/swiper/api/#methods
                //mySwiper.slideTo(index, speed, runCallbacks);
                activeSwiperInstance.slideTo( slideIndex, 100 );
          });
    }
});

