//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired on Dom Ready, in ::initialize()
            setupUiHoverVisibility : function() {
                  var self = this;
                  var tmpl,
                      level,
                      params;
                  // Level's overlay
                  $('.sektion-wrapper').on( 'mouseenter', '[data-sek-level]', function( evt ) {
                        // if ( $(this).children('.sek-block-overlay').length > 0 )
                        //   return;
                        level = $(this).data('sek-level');
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
                              $(this).find('.sek-block-overlay').fadeIn( 300 );
                        });

                  }).on( 'mouseleave', '[data-sek-level]', function( evt ) {
                          $(this).children('.sek-block-overlay').fadeOut( {
                                duration : 200,
                                complete : function() { $(this).remove(); }
                          });
                  });



                  // Add content button between sections
                  $('body').on( 'mouseenter', function( evt ) {
                        if ( $(this).find('.sek-add-content-button').length > 0 )
                          return;

                        $sectionCollection = $('.sektion-wrapper').children( 'div[data-sek-level="section"]' );
                        tmpl = self.parseTemplate( '#sek-tmpl-add-content-button' );
                        // nested sections are not included
                        $sectionCollection.each( function() {
                              $.when( $(this).prepend( tmpl({}) ) ).done( function() {
                                    $(this).find('.sek-add-content-button').fadeIn( 300 );
                              });
                              //console.log('$sectionCollection.length', $sectionCollection.length, $(this).index() + 1 );
                              //if is last section, append also
                              if ( $sectionCollection.length == $(this).index() + 1 ) {
                                    $.when( $(this).append( tmpl({ is_last : true }) ) ).done( function() {
                                          $(this).find('.sek-add-content-button').fadeIn( 300 );
                                    });
                              }
                        });
                  }).on( 'mouseleave', function( evt ) {
                        // nested sections are not included
                        $('.sektion-wrapper').find('.sek-add-content-button').each( function() {
                              $(this).fadeOut( {
                                    duration : 200,
                                    complete : function() { $(this).remove(); }
                              });
                        });
                  });


                  // $('div[data-sek-level="section"]').on( 'mouseenter mouseleave', '.sek-add-content-btn', function( evt ) {
                  //       $(this).closest( '.sek-add-content-button' ).toggleClass( 'sek-add-content-hovering', 'mouseenter' == evt.type );
                  // });


                  return this;
            },//setupUiHoverVisibility

            // setupSectionUiOverlay : function( eventType, id ) {

            // }
      });//$.extend()
})( wp.customize, jQuery, _ );
