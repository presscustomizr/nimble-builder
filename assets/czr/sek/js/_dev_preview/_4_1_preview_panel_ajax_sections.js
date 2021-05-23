//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // this method is used when creating or duplicating a sektion
            // @return a promise()
            ajaxAddSektion : function( params ) {
                  var self = this;
                  // will be cleaned on ajax.done()
                  // @see ::scheduleTheLoaderCleaning
                  self.mayBePrintLoader({
                        loader_located_in_level_id : params.apiParams.location
                  });
                  return self.doAjax({
                        action : 'sek_get_content',
                        id : params.apiParams.id,
                        in_sektion : params.apiParams.in_sektion,
                        in_column : params.apiParams.in_column,
                        location_skope_id : params.location_skope_id,
                        local_skope_id : params.local_skope_id,
                        sek_action : params.apiParams.action,
                        is_nested : params.apiParams.is_nested,

                        // The following params have been introduced when implementing support for multi-section pre-build sections
                        // @see https://github.com/presscustomizr/nimble-builder/issues/489
                        content_type : ( params.apiParams && params.apiParams.content_type ) ? params.apiParams.content_type : null,
                        collection_of_preset_section_id : ( params.apiParams && params.apiParams.collection_of_preset_section_id ) ? params.apiParams.collection_of_preset_section_id : []
                  }).done( function( _r_ ) {
                        var html_content = '';
                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                        if ( _r_.data && _r_.data.contents ) {
                              html_content = _r_.data.contents;
                        } else {
                              self.errare( 'SekPreviewPrototype::ajaxAddSektion => ajax_response.data.contents is undefined ', _r_ );
                              self.errare( 'params ?', params );
                        }

                        // Embed
                        // is it a nested sektion ?
                        var $parentColumn;
                        if ( params.apiParams.is_nested ) {
                              $parentColumn = $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_column + '"]' );
                              if ( 1 > $parentColumn.length ) {
                                    self.errare( 'preview => reactToPanelMsg => sek-add-column => no DOM node for parent column => ', params.apiParams.in_column );
                              }
                              var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.in_column + '"></span>';
                              $parentColumn.before( placeholderHtml );
                              // remove and re-render the entire column
                              $parentColumn.remove();
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).after( html_content );
                              $( '.sektion-wrapper').find( '.sek-placeholder' ).remove();
                        } else {
                              // DUPLICATE CASE
                              // Insert the clone section right after its cloned sister
                              if ( 'sek-duplicate-section' == params.apiParams.action && ! _.isEmpty( params.cloneId ) ) {
                                    $( '.sektion-wrapper').find( 'div[data-sek-id="' + params.apiParams.in_sektion + '"]' ).after( html_content );
                              }
                              // GENERATED WHEN ADDING A MODULE
                              else {
                                    // When a section has been created by adding a module ( @see sek-add-content-in-new-sektion )
                                    // we need to append it to a specific location
                                    // otherwise, we append it at the end of the section collection
                                    $beforeCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.before_section + '"]' );
                                    $afterCandidate = $( '.sektion-wrapper[data-sek-id="' + params.apiParams.location + '"]').find( 'div[data-sek-id="' + params.apiParams.after_section + '"]' );

                                    if ( ! _.isEmpty( params.apiParams.before_section ) && $beforeCandidate.length > 0 ) {
                                          $beforeCandidate.before( html_content );
                                    } else if ( ! _.isEmpty( params.apiParams.after_section ) && $afterCandidate.length > 0 ) {
                                          $afterCandidate.after( html_content );
                                    } else {
                                          $( '[data-sek-id="' + params.apiParams.location + '"]').append( html_content );
                                    }
                              }
                        }

                        // When a section is duplicated, fire sortable for the inner-column modules
                        if ( 'sek-duplicate-section' == params.apiParams.action ) {
                              // re-instantiate sortable in the refreshed columns of the section
                              // + make columns resizable
                              $( 'div[data-sek-id="' + params.cloneId + '"]', '.sektion-wrapper').each( function() {
                                    $(this).trigger('sek-columns-refreshed');
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
                        //=> will be listened to clean the loader overlay just in time
                        if ( params.cloneId ) {
                              $( 'div[data-sek-id="' + params.cloneId + '"]' ).trigger('sek-section-added', params );
                        }
                        // @todo is the params.apiParams.id correct ? Does it actually exists in the DOM?
                        $( 'div[data-sek-id="' + params.apiParams.id + '"]' ).trigger('sek-section-added', params );

                        // added to fix resizable not instantiated when adding column modules
                        // @see https://github.com/presscustomizr/nimble-builder/issues/523
                        $( 'div[data-sek-id="' + params.apiParams.location + '"]' ).trigger('sek-location-refreshed', params );
                  }).fail( function( _r_ ) {
                        self.errare( 'ERROR in sek_get_html_for_injection ? ' , _r_ );
                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                              .trigger( 'sek-ajax-error' );
                  });
            }//ajaxAddSektion()

      });//$.extend()
})( wp.customize, jQuery, _ );
