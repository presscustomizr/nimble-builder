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
                      initial_content_type,
                      nbApiInstance = api.czr_sektions;

                  if ( ! api.section.has( input.module.control.section() ) ) {
                        throw new Error( 'api.czrInputMap.content_type_switcher => section not registered' );
                  }
                  _section_ = api.section( input.module.control.section() );

                  var _do_ = function( contentType ) {
                        input.container.find( '[data-sek-content-type="' + ( contentType || 'module' ) + '"]').trigger('click');
                        _.each( _section_.controls(), function( _control_ ) {
                              if ( ! _.isUndefined( _control_.content_type ) ) {
                                    _control_.active( contentType === _control_.content_type );
                              }
                        });
                  };

                  // Initialize
                  // Fixes issue https://github.com/presscustomizr/nimble-builder/issues/248
                  api.czr_sektions.currentContentPickerType = api.czr_sektions.currentContentPickerType || new api.Value();
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

                  // initialize the content picker observer
                  api.czr_sektions.currentContentPickerType( input() );

                  // attach click event on data-sek-content-type buttons
                  input.container.on('click', '[data-sek-content-type]', function( evt ) {
                        evt.preventDefault();
                        var _contentType = $(this).data( 'sek-content-type');
                        // handle the aria-pressed state
                        input.container.find('[data-sek-content-type]').attr( 'aria-pressed', false );

                        // close other dialog
                        //nbApiInstance.templateGalleryExpanded(false);
                        nbApiInstance.levelTreeExpanded(false);
                        if ( nbApiInstance.tmplDialogVisible ) {
                              nbApiInstance.tmplDialogVisible(false);
                        }
                        if ( nbApiInstance.saveSectionDialogVisible ) {
                              nbApiInstance.saveSectionDialogVisible(false);
                        }

                        // April 2020 : template case added for https://github.com/presscustomizr/nimble-builder/issues/651
                        if ( 'template' === _contentType ) {
                              var _isExpanded = api.czr_sektions.templateGalleryExpanded();
                              $(this).attr( 'aria-pressed', !_isExpanded );
                              // When opening template gallery from the content type switcher, make sure NB reset the possible previous tmpl scope used in a site template picking scenario
                              self._site_tmpl_scope = null;
                              api.czr_sektions.templateGalleryExpanded(!_isExpanded);
                        } else {
                              // always close the template picker when selecting something else
                              api.czr_sektions.templateGalleryExpanded(false);

                              $(this).attr( 'aria-pressed', true );

                              // case for section and module content type
                              api.czr_sektions.currentContentPickerType( _contentType );
                        }
                  });

                  // Specific for templates
                  api.bind('nb-template-gallery-closed', function() {
                        input.container.find('[data-sek-content-type="template"]').attr( 'aria-pressed', false );
                  });

                  // initialize with module or section picker depending on the scenario :
                  // 1) new section created => section picker
                  // 2) all other cases => module picker
                  _do_( api.czr_sektions.currentContentPickerType() );
            }
      });
})( wp.customize, jQuery, _ );