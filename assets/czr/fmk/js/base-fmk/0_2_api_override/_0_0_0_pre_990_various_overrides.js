
( function ( api, $, _ ) {
      /* monkey patch for the content height set */
      //wp.customize.Section is not available before wp 4.1
      if ( 'function' == typeof api.Section ) {
            // backup the original function
            var _original_section_initialize = api.Section.prototype.initialize;
            api.Section.prototype.initialize = function( id, options ) {
                  //call the original constructor
                  _original_section_initialize.apply( this, [id, options] );
                  var section = this;

                  this.expanded.callbacks.add( function( _expanded ) {
                    if ( ! _expanded )
                      return;

                  var container = section.container.closest( '.wp-full-overlay-sidebar-content' ),
                        content = section.container.find( '.accordion-section-content' );
                    //content resizing to the container height
                    _resizeContentHeight = function() {
                      content.css( 'height', container.innerHeight() );
                  };
                    _resizeContentHeight();
                    //this is set to off in the original expand callback if 'expanded' is false
                    $( window ).on( 'resize.customizer-section', _.debounce( _resizeContentHeight, 110 ) );
                  });
            };
      }
})( wp.customize , jQuery, _ );