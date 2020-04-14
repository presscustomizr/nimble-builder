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
                  self.cachedElements.$body.on([
                        'sek-modules-refreshed',
                        'sek-columns-refreshed',
                        'sek-section-added',
                        'sek-level-refreshed',
                        'sek-stylesheet-refreshed',
                        'sek-ajax-error'
                  ].join(' '), function( evt ) {
                        self.cleanLoader();
                  });
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
            // - on 'sek-maybe-print-loader'
            // @params {
            //    element : $(),
            //    action : '',
            //    level,
            //    loader_located_in_level_id,
            //    fullPageLoader: false,
            //    duration:4000
            // }
            mayBePrintLoader : function( params ) {
                  params = _.isObject( params ) ? params : {};
                  var self = this,
                      levelIdForTheLoader = params.loader_located_in_level_id;

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
                              }, params.duration || 4000 ) );
                        }
                  }
                  if ( true === params.fullPageLoader ) {
                        var $loaderWrapper = $('<div>', { id : 'nimble-full-page-loader-wrapper', class: 'white-loader'} );
                        self.cachedElements.$body.append($loaderWrapper);
                        $loaderWrapper.fadeIn('fast').append( self._css_loader_html ).find('.sek-css-loader').fadeIn( 'fast' );
                        // Blur locations
                        $('[data-sek-level="location"]').each( function() {
                              $(this).addClass('sek-blur');
                        });

                        // Start the countdown for auto-cleaning
                        clearTimeout( $.data( this, '_nimble_full_page_loader_active_timer_') );
                        $.data( this, '_nimble_full_page_loader_active_timer_', setTimeout(function() {
                              self.cleanLoader( { cleanFullPageLoader : true });
                        }, params.duration || 6000 ) );
                  }
            },

            // scheduled in ::initialize(), on 'sek-modules-refreshed sek-columns-refreshed sek-section-added sek-refresh-level'
            // invoked in ::mayBePrintLoader() in an auto-clean scenario
            // or on wp.customize.send('sek-clean-loader', { cleanFullPageLoader : true })
            // {
            //  cleanFullPageLoader : true
            // }
            cleanLoader : function( params ) {
                  var self = this;
                  $('.sek-level-clone').remove();
                  $('[data-sek-level]').each( function() {
                        $(this).removeClass('sek-refreshing');
                  });
                  params = params || {};
                  if ( true === params.cleanFullPageLoader ) {
                        // Unblur locations
                        $('[data-sek-level="location"]').each( function() {
                              $(this).removeClass('sek-blur');
                        });
                        $('#nimble-full-page-loader-wrapper').remove();
                  }
            }

      });//$.extend()
})( wp.customize, jQuery, _ );
