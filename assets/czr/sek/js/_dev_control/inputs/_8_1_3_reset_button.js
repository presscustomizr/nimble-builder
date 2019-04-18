//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            reset_button : function( params ) {
                  var input = this;

                  // Schedule choice changes on button click
                  input.container.on( 'click', '[data-sek-reset-scope]', function( evt, params ) {
                        evt.stopPropagation();
                        var scope = $(this).data( 'sek-reset-scope' );

                        if ( _.isEmpty( scope ) || !_.contains(['local', 'global'], scope ) ) {
                              api.errare( 'reset_button input => invalid scope provided.', scope );
                              return;
                        }
                        api.czr_sektions.updateAPISetting({
                              action : 'sek-reset-collection',
                              scope : scope,//<= will determine which setting will be updated,
                              // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                        }).done( function() {
                              //_notify( sektionsLocalizedData.i18n['The revision has been successfully restored.'], 'success' );
                              api.previewer.refresh();
                              api.previewer.trigger('sek-notify', {
                                    notif_id : 'reset-success',
                                    type : 'success',
                                    duration : 8000,
                                    message : [
                                          '<span>',
                                            '<strong>',
                                            sektionsLocalizedData.i18n['Reset complete'],
                                            '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        }).fail( function( response ) {
                              api.errare( 'reset_button input => error when firing ::updateAPISetting', response );
                              api.previewer.trigger('sek-notify', {
                                    notif_id : 'reset-failed',
                                    type : 'error',
                                    duration : 8000,
                                    message : [
                                          '<span>',
                                            '<strong>',
                                            sektionsLocalizedData.i18n['Reset failed'],
                                            '<br/>',
                                            '<i>' + response + '</i>',
                                            '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        });
                  });//on('click')
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );