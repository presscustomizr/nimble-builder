// global sekFrontLocalized
var nimbleFront;
jQuery( function($){
    window.nimbleFront = {
        cachedElements : {
            $window : $(window),
            $body : $('body')
        },
        isMobile : function() {
              return ( _utils_.isFunction( window.matchMedia ) && matchMedia( 'only screen and (max-width: 768px)' ).matches ) || ( this.isCustomizing() && 'desktop' != this.previewedDevice );
        },
        isCustomizing : function() {
              return this.cachedElements.$body.hasClass('is-customizing') || ( 'undefined' !== typeof wp && 'undefined' !== typeof wp.customize );
        },
        previewedDevice : 'desktop',
        //Simple Utility telling if a given Dom element is currently in the window <=> visible.
        //Useful to mimic a very basic WayPoint
        isInWindow : function( $_el, threshold ) {
              if ( ! ( $_el instanceof $ ) )
                return;
              if ( threshold && ! _utils_.isNumber( threshold ) )
                return;

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
              var $el_candidate = sniffFirstVisiblePrevElement( $_el );
              if ( !$el_candidate || $el_candidate.length < 1 )
                return false;

              var wt = this.cachedElements.$window.scrollTop(),
                  wb = wt + this.cachedElements.$window.height(),
                  it  = $_el.offset().top,
                  ib  = it + $_el.height(),
                  th = threshold || 0;

              return ib >= wt - th && it <= wb + th;
        }
    };

    //PREVIEWED DEVICE ?
    //Listen to the customizer previewed device
    if ( nimbleFront.isCustomizing() ) {
          var _setPreviewedDevice = function() {
                wp.customize.preview.bind( 'previewed-device', function( device ) {
                      nimbleFront.previewedDevice = device;// desktop, tablet, mobile
                });
          };
          if ( wp.customize.preview ) {
              _setPreviewedDevice();
          } else {
                wp.customize.bind( 'preview-ready', function() {
                      _setPreviewedDevice();
                });
          }
    }
});
