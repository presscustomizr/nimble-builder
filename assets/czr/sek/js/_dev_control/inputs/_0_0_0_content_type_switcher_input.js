//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};
      //input_type => callback fn to fire in the Input constructor on initialize
      //the callback can receive specific params define in each module constructor
      //For example, a content picker can be given params to display only taxonomies
      $.extend( api.czrInputMap, {
            content_type_switcher : function( input_options ) {
                  var input = this,
                      _section_,
                      initial_content_type;

                  if ( ! api.section.has( input.module.control.section() ) ) {
                        throw new Error( 'api.czrInputMap.content_type_switcher => section not registered' );
                  }
                  _section_ = api.section( input.module.control.section() );

                  // attach click event on data-sek-content-type buttons
                  input.container.on('click', '[data-sek-content-type]', function( evt ) {
                        evt.preventDefault();
                        var _contentType = $(this).data( 'sek-content-type');
                        // handle the is-selected css class toggling
                        input.container.find('[data-sek-content-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        if ( 'template' === _contentType ) {
                              api.czr_sektions.import_nimble_template();
                        } else {
                              // case for section and module content type
                              api.czr_sektions.currentContentPickerType( _contentType );
                        }
                  });


                  var _do_ = function( contentType ) {
                        console.log('contentType ??', contentType );
                        input.container.find( '[data-sek-content-type="' + ( contentType || 'module' ) + '"]').trigger('click');
                        _.each( _section_.controls(), function( _control_ ) {
                              if ( ! _.isUndefined( _control_.content_type ) ) {
                                    _control_.active( contentType === _control_.content_type );
                              }
                        });
                  };

                  // Initialize
                  // Fixes issue https://github.com/presscustomizr/nimble-builder/issues/248
                  api.czr_sektions.currentContentPickerType = api.czr_sektions.currentContentPickerType || new api.Value( input() );
                  // This event is emitted by ::generateUIforDraggableContent()
                  // this way we are sure that all controls for modules and sections are instantiated
                  // and we can use _section_.controls() to set the visibility of module / section controls when switching
                  api.bind('nimble-modules-and-sections-controls-registered', function() {
                        _do_( api.czr_sektions.currentContentPickerType() );
                  });


                  // Schedule a reaction to changes
                  api.czr_sektions.currentContentPickerType.bind( function( contentType ) {
                        _do_( contentType );
                  });
            }
      });
})( wp.customize, jQuery, _ );