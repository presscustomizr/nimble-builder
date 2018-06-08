//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            //Fired in ::initialize()
            setupLoader : function() {
                  var self = this;
                  // Cache our loader
                  this._css_loader_html = '<div class="sek-css-loader sek-mr-loader" style="display:none"><div></div><div></div><div></div></div>';

                  // Loader Cleaning <= the element printed when refreshing a level
                  // @see ::mayBePrintLoader
                  $( 'body').on( 'sek-modules-refreshed sek-columns-refreshed sek-section-added sek-level-refreshed', function( evt ) {
                        self.cleanLoader();
                  });

                  // Declare and bind a state to help us monitor the existence of a loader, and the need for an auto-removal of it after a while.
                  // this.loaderActive = this.loaderActive || new api.Value( false );
                  // this.loaderActive.bind( function( isActive ) {

                  // });
            },

            // @return void()
            // Insert a clone ( same dimensions ) div element of a level currently being refreshed, including a centered loading animation
            // + adds a .sek-refreshing css class to the element being refreshed
            //
            // Invoked when
            // - user click on an icon action in the preview that trigger a partial reflush of the DOM. For example, adding a column, duplicating a module, etc.
            // - a module / section is dropped in the preview
            // - a module is being edited
            // - a column is resized
            // @params {
            //    element : $(),
            //    action : '',
            //    level,
            //    loader_located_in_level_id
            // }
            mayBePrintLoader : function( params ) {
                  var self = this,
                      levelIdForTheLoader = params.loader_located_in_level_id;

                  //self.infoLog('control:: mayBePrintLoader() => params ', params );

                  // if the level if where to insert the loader has not been specified, let's determinate it
                  if ( _.isEmpty( levelIdForTheLoader ) ) {
                        if ( params.element.length < 1 ) {
                              self.errare( '::mayBePrintLoader => the provided params.element does not exist in the DOM.');
                              return;
                        }

                        if ( _.isEmpty( params.action ) ) {
                              self.errare( '::mayBePrintLoader => missing level param.');
                              return;
                        }

                        switch( params.action ) {
                              case 'add-column' :
                                    levelIdForTheLoader = params.element.closest('[data-sek-level="section"]').data('sek-id');
                              break;
                              case 'add-section' :
                                    // this is the nested section case
                                    levelIdForTheLoader = params.element.closest('[data-sek-level="column"]').data('sek-id');
                              break;
                              case 'duplicate' :
                                    if ( _.isEmpty( params.level ) ) {
                                          self.errare( '::mayBePrintLoader => missing level param.');
                                          break;
                                    }
                                    switch( params.level ) {
                                          case 'module' :
                                                levelIdForTheLoader = params.element.closest('[data-sek-level="column"]').data('sek-id');
                                          break;
                                          case 'column' :
                                                levelIdForTheLoader = params.element.closest('[data-sek-level="section"]').data('sek-id');
                                          break;
                                          case 'section' :
                                                levelIdForTheLoader = params.element.closest('[data-sek-level="location"]').data('sek-id');
                                          break;
                                          case 'default' :
                                                self.errare( '::mayBePrintLoader => unrecognized level param.', params.level );
                                          break;
                                    }
                              break;
                              case 'remove' :
                                    switch( params.level ) {
                                          case 'module' :
                                                levelIdForTheLoader = params.element.closest('[data-sek-level="column"]').data('sek-id');
                                          break;
                                          case 'column' :
                                                levelIdForTheLoader = params.element.closest('[data-sek-level="section"]').data('sek-id');
                                          break;
                                          case 'section' :
                                                levelIdForTheLoader = params.element.closest('[data-sek-level="location"]').data('sek-id');
                                          break;
                                    }
                              break;
                              case 'sek-add-module' :
                                    levelIdForTheLoader = params.loader_located_in_level_id;
                              break;
                              default :
                                    self.infoLog( '::mayBePrintLoader => unrecognized action param.', params.action );
                              break;
                        }
                  }//if ( _.isEmpty( params.loader_located_in_level_id ) )

                  if ( ! _.isEmpty( levelIdForTheLoader ) ) {
                        var $levelElementForTheLoader = $('[data-sek-id="' + levelIdForTheLoader +'"]');
                        if ( $levelElementForTheLoader.length > 0 && 1 > $('.sek-level-clone ').length ) {
                              $levelClone = $('<div>', { class : 'sek-level-clone' });
                              // blur all children levels
                              $levelElementForTheLoader.find('[data-sek-level]').each( function() {
                                    $(this).addClass('sek-refreshing');
                              });

                              // print the absolute positionned clone on top
                              $levelElementForTheLoader.prepend( $levelClone );
                              $levelClone.css({
                                    width : $levelElementForTheLoader.outerWidth() +'px',
                                    height : $levelElementForTheLoader.outerHeight() + 'px'
                              }).append( self._css_loader_html ).find('.sek-css-loader').fadeIn( 'fast' );

                              // Start the countdown for auto-cleaning
                              clearTimeout( $.data( this, '_nimble_loader_active_timer_') );
                              $.data( this, '_nimble_loader_active_timer_', setTimeout(function() {
                                    self.cleanLoader();
                              }, 4000 ) );
                        }
                  }
            },

            // scheduled in ::initialize(), on 'sek-modules-refreshed sek-columns-refreshed sek-section-added sek-refresh-level'
            // invoked in ::mayBePrintLoader() in an auto-clean scenario
            cleanLoader : function() {
                  var self = this;
                  $('.sek-level-clone').remove();
                  $('[data-sek-level]').each( function() {
                        $(this).removeClass('sek-refreshing');
                  });
            }

      });//$.extend()
})( wp.customize, jQuery, _ );
