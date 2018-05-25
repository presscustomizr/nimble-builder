//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // this method is used when creating or duplicating a sektion
            // @return a promise()
            ajaxAddSektion : function( params ) {
                  var self = this;
                  return czrapp.doAjax( {
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        in_column : params.apiParams.in_column,
                        skope_id : params.skope_id,
                        sek_action : params.apiParams.action,
                        is_nested : params.apiParams.is_nested
                  }).done( function( _r_ ) {
                        // Embed
                        // is it a nested sektion ?
                        var $parentColumn;
                        if ( params.apiParams.is_nested ) {
                              $parentColumn = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' );
                              if ( 1 > $parentColumn.length ) {
                                    czrapp.errare( 'preview => reactToPanelMsg => sek-add-column => no DOM node for parent column => ', params.apiParams.in_column );
                              }
                              var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_column + '"></span>';
                              $parentColumn.before( placeholderHtml );
                              // remove and re-render the entire column
                              $parentColumn.remove();
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).after( _r_.data );
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).remove();
                        } else {
                              // DUPLICATE CASE
                              // Insert the clone section right after its cloned sister
                              if ( 'sek-duplicate-section' == params.apiParams.action && ! _.isEmpty( params.cloneId ) ) {
                                    $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).after( _r_.data );
                              }
                              // GENERATED WHEN ADDING A MODULE
                              else {
                                    // When a section has been created by adding a module ( @see sek-add-content-in-new-sektion )
                                    // we need to append it to a specific location
                                    // otherwise, we append it at the end of the section collection
                                    $beforeCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.before_section + '"]' );
                                    $afterCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.after_section + '"]' );

                                    if ( ! _.isEmpty( params.apiParams.before_section ) && $beforeCandidate.length > 0 ) {
                                          $beforeCandidate.before( _r_.data );
                                    } else if ( ! _.isEmpty( params.apiParams.after_section ) && $afterCandidate.length > 0 ) {
                                          $afterCandidate.after( _r_.data );
                                    } else {
                                          $( '[data-sek-id="' + params.apiParams.location + '"]').append( _r_.data );
                                    }
                              }
                        }

                        // When a section is duplicated, fire sortable for the inner-column modules
                        if ( 'sek-duplicate-section' == params.apiParams.action ) {
                              // re-instantiate sortable in the refreshed columns of the section
                              // make columns resizable
                              $( 'div[data-sek-id="' + params.cloneId + '"]', '.sektion-wrapper').each( function() {
                                    //self.maybeMakeColumnResizableInSektion.call( this );

                                    $(this).find(  'div[data-sek-level="column"]' ).each( function() {
                                          self.makeModulesSortableInColumn( $(this).data('sek-id') );
                                    });
                              });
                        }

                        // refresh sortable for the inner column if nested sektion case
                        if ( params.apiParams.is_nested ) {
                              self.makeModulesSortableInColumn( params.apiParams.in_column );
                              $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).each( function() {
                                    self.maybeMakeColumnResizableInSektion.call( this );
                              });
                        }

                        // say it to the parent sektion
                        //=> will be listened to by fittext
                        if ( params.cloneId ) {
                              $( 'div[data-sek-id="' + params.cloneId + '"]' ).trigger('sek-section-added', params );
                        }
                        $( 'div[data-sek-id="' + params.apiParams.id + '"]' ).trigger('sek-section-added', params );
                  }).fail( function( _r_ ) {
                        czrapp.errare( 'ERROR in sek_get_html_for_injection ? ' , _r_ );
                  });
            }//ajaxAddSektion()

      });//$.extend()
})( wp.customize, jQuery, _ );
