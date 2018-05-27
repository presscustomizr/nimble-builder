//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired on Dom Ready, in ::initialize()
            setupUiHoverVisibility : function() {
                  var self = this;
                  var tmpl,
                      level,
                      params,
                      $levelEl;

                  // Level's overlay with delegation
                  $('body').on( 'mouseenter', '[data-sek-level]', function( evt ) {
                        // if ( $(this).children('.sek-block-overlay').length > 0 )
                        //   return;
                        level = $(this).data('sek-level');
                        // we don't print a ui for locations
                        if ( 'location' == level )
                          return;

                        params = {
                              id : $(this).data('sek-id'),
                              level : $(this).data('sek-level')
                        };
                        switch ( level ) {
                              case 'section' :
                                    //$el = $('.sektion-wrapper').find('[data-sek-id="' + id + '"]');
                                    params = _.extend( params, {
                                          is_last_possible_section : true === $(this).data('sek-is-nested'),
                                          can_have_more_columns : $(this).find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 12
                                    });
                              break;
                              case 'column' :
                                    var $parent_sektion = $(this).closest('div[data-sek-level="section"]');
                                    params = _.extend( params, {
                                          parent_can_have_more_columns : $parent_sektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 12,
                                          parent_is_single_column : $parent_sektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length < 2,
                                          parent_is_last_allowed_nested : true === $parent_sektion.data('sek-is-nested')
                                    });
                              break;
                              case 'module' :
                                    params = _.extend( params, {});
                              break;
                        }
                        // don't display the column and module ui when resizing columns
                        if ( true === $('.sektion-wrapper').data('sek-resizing-columns') && _.contains( ['column', 'module'], level ) ) {
                              return;
                        }

                        tmpl = self.parseTemplate( '#sek-tmpl-overlay-ui-' + level );
                        $.when( $(this).prepend( tmpl( params ) ) ).done( function() {
                              $levelEl = $(this);
                              $levelEl.find('.sek-block-overlay').stop( true, true ).fadeIn( {
                                  duration : 300,
                                  complete : function() {}
                              } );
                        });

                  }).on( 'mouseleave', '[data-sek-level]', function( evt ) {
                          $levelEl = $(this);
                          $levelEl.children('.sek-block-overlay').stop( true, true ).fadeOut( {
                                duration : 200,
                                complete : function() {
                                      $(this).remove();
                                }
                          });

                  });



                  // Add content button between sections
                  // <script type="text/html" id="sek-tmpl-add-content-button">
                  //     <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                  //       <div class="sek-add-content-button-wrapper">
                  //         <button data-sek-action="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:60px;">
                  //           <span title="<?php _e('Add Content', 'text_domain_to_be_replaced' ); ?>" class="sek-action-button-icon fas fa-plus-circle sek-action"></span><span class="action-button-text"><?php _e('Add Content', 'text_domain_to_be_replaced' ); ?></span>
                  //         </button>
                  //       </div>
                  //     </div>
                  // </script>
                  var _printAddContentButton_ = function( evt ) {
                        $('body').find( 'div[data-sek-level="location"]' ).each( function() {
                              $sectionCollection = $(this).children( 'div[data-sek-level="section"]' );
                              tmpl = self.parseTemplate( '#sek-tmpl-add-content-button' );
                              var $btn_el,
                                  _location = $(this).data('sek-id');

                              // nested sections are not included
                              $sectionCollection.each( function() {
                                    if ( $(this).find('.sek-add-content-button').length > 0 )
                                      return;
                                    $.when( $(this).prepend( tmpl({ location : _location }) ) ).done( function() {
                                          $btn_el = $(this).find('.sek-add-content-button');
                                          //console.log( "$(this).data('sek-id') ", $btn_el, $(this).data('sek-id')  );
                                          if ( $(this).data('sek-id') ) {
                                                $btn_el.attr('data-sek-before-section', $(this).data('sek-id') );//Will be used to insert the section at the right place
                                          }
                                          $btn_el.fadeIn( 300 );
                                    });
                                    //console.log('$sectionCollection.length', $sectionCollection.length, $(this).index() + 1 );
                                    //if is last section, append also
                                    //console.log('IS LAST ? => ', $sectionCollection.length, $(this).index() );
                                    if ( $sectionCollection.length == $(this).index() + 1 ) {
                                          $.when( $(this).append( tmpl({ is_last : true, location : _location }) ) ).done( function() {
                                                $btn_el = $(this).find('.sek-add-content-button').last();
                                                if ( $(this).data('sek-id') ) {
                                                      $btn_el.attr('data-sek-after-section', $(this).data('sek-id') );//Will be used to insert the section at the right place
                                                }
                                                $btn_el.fadeIn( 300 );
                                          });
                                    }
                              });//$sectionCollection.each( function() )
                        });//$( 'div[data-sek-level="location"]' ).each( function() {})



                        // .sek-empty-location-placeholder container is printed when the location has no section yet in its collection
                        $('.sek-empty-location-placeholder').each( function() {
                              if ( $(this).find('.sek-add-content-button').length > 0 )
                                return;
                              $.when( $(this).append( tmpl({ location : $(this).closest( 'div[data-sek-level="location"]' ).data('sek-id') } ) ) ).done( function() {
                                    $btn_el = $(this).find('.sek-add-content-button');
                                    $btn_el.attr('data-sek-is-first-section', true );
                                    $btn_el.fadeIn( 300 );
                              });
                        });
                  };//_printAddContentButton_


                  // Schedule the printing / removal of the add content button
                  self.mouseMovedRecently = new api.Value( {} );
                  self.mouseMovedRecently.bind( function( position ) {
                        if ( ! _.isEmpty( position) ) {
                              _printAddContentButton_();
                        } else {
                              $('body').stop( true, true ).find('.sek-add-content-button').each( function() {
                                    $(this).fadeOut( {
                                          duration : 200,
                                          complete : function() { $(this).remove(); }
                                    });
                              });
                        }
                  });
                  $(window).on( 'mousemove scroll', _.throttle( function( evt ) {
                        self.mouseMovedRecently( { x : evt.clientX, y : evt.clientY } );
                        clearTimeout( $.data( this, '_scroll_move_timer_') );
                        $.data( this, '_scroll_move_timer_', setTimeout(function() {
                              self.mouseMovedRecently.set( {} );
                        }, 2000 ) );
                  }, 50 ) );

                  // Always remove when a dragging action is started
                  api.preview.bind( 'sek-drag-start', function() {
                        self.mouseMovedRecently.set( {} );
                  });

                  return this;
            },//setupUiHoverVisibility

            // setupSectionUiOverlay : function( eventType, id ) {

            // }
      });//$.extend()
})( wp.customize, jQuery, _ );
