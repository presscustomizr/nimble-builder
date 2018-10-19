var czrapp = czrapp || {};
/***************************
* ADD JQUERY PLUGINS METHODS
****************************/
(function($, czrapp, Waypoint ) {
      var _methods = {
            centerImagesWithDelay : function( delay ) {
                  var self = this;
                  //fire the center images plugin
                  //setTimeout( function(){ self.emit('centerImages'); }, delay || 300 );
                  setTimeout( function(){ self.emit('centerImages'); }, delay || 50 );
            },



            /**
            * CENTER VARIOUS IMAGES
            * @return {void}
            */
            centerImages : function() {
                  var $wrappersOfCenteredImagesCandidates = $('.sek-fp-widget .sek-fp-thumb-wrapper, .js-centering.entry-media__holder, .js-centering.entry-media__wrapper');

                  //Featured pages and classical grid are always centered
                  // $('.tc-grid-figure, .widget-front .tc-thumbnail').centerImages( {
                  //       enableCentering : 1,
                  //       oncustom : ['smartload', 'refresh-height', 'simple_load'],
                  //       zeroTopAdjust: 0,
                  //       enableGoldenRatio : false,
                  // } );
                  var _css_loader = '<div class="czr-css-loader czr-mr-loader" style="display:none"><div></div><div></div><div></div></div>';
                  $wrappersOfCenteredImagesCandidates.each( function() {
                        $( this ).append(  _css_loader ).find('.czr-css-loader').fadeIn( 'slow');
                  });
                  $wrappersOfCenteredImagesCandidates.centerImages({
                        onInit : true,
                        enableCentering : 1,
                        oncustom : ['smartload', 'refresh-height', 'simple_load'],
                        enableGoldenRatio : false, //true
                        zeroTopAdjust: 0,
                        setOpacityWhenCentered : false,//will set the opacity to 1
                        addCenteredClassWithDelay : 50,
                        opacity : 1
                  });
                  _.delay( function() {
                        $wrappersOfCenteredImagesCandidates.find('.czr-css-loader').fadeOut( {
                          duration: 500,
                          done : function() { $(this).remove();}
                        } );
                  }, 300 );


                  // if for any reasons, the centering did not happen, the imgs will not be displayed because opacity will stay at 0
                  // => the opacity is set to 1 as soon as v-centered or h-centered has been added to a img element candidate for centering
                  // @see css
                  var _mayBeForceOpacity = function( params ) {
                        params = _.extend( {
                              el : {},
                              delay : 0
                        }, _.isObject( params ) ? params : {} );

                        if ( 1 !== params.el.length  || ( params.el.hasClass( 'h-centered') || params.el.hasClass( 'v-centered') ) )
                          return;

                        _.delay( function() {
                              params.el.addClass( 'opacity-forced');
                        }, params.delay );

                  };
                  //For smartloaded image, let's wait for the smart load to happen, for the others, let's do it now without delay
                  if ( czrapp.localized.imgSmartLoadEnabled ) {
                        $wrappersOfCenteredImagesCandidates.on( 'smartload', 'img' , function( ev ) {
                              if ( 1 != $( ev.target ).length )
                                return;
                              _mayBeForceOpacity( { el : $( ev.target ), delay : 200 } );
                        });
                  } else {
                        $wrappersOfCenteredImagesCandidates.find('img').each( function() {
                              _mayBeForceOpacity( { el : $(this), delay : 100 } );
                        });
                  }

                  //then last check
                  _.delay( function() {
                        $wrappersOfCenteredImagesCandidates.find('img').each( function() {
                              _mayBeForceOpacity( { el : $(this), delay : 0 } );
                        });
                  }, 1000 );

            }//center_images
      };//_methods{}

      czrapp.methods.JQPlugins = {};
      $.extend( czrapp.methods.JQPlugins , _methods );


})(jQuery, czrapp, Waypoint);