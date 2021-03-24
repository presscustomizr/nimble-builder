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
                        })
                        .done( function( resp) {
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
                              if ( sektionsLocalizedData.isSiteTemplateEnabled && 'local' === scope ) {
                                    var _doThingsAfterRefresh = function() {
                                          // Keep only the settings for global option, local options, content picker
                                          // Remove all the others
                                          // ( local options are removed below )
                                          api.czr_sektions.cleanRegisteredLevelSettings();

                                          // Removes the local sektions setting
                                          api.remove( api.czr_sektions.localSectionsSettingId() );

                                          // RE-register the local sektions setting with values sent from the server
                                          // If the local page inherits a group skope, those will be set as local
                                          // To prevent saving server sets property __inherits_group_skope__ = true
                                          // set the param { dirty : true } => because otherwise, if user saves right after a reset, local option won't be ::updated() server side.
                                          // Which means that the page will keep its previous aspect
                                          try { api.czr_sektions.setupSettingsToBeSaved( { dirty : true } ); } catch( er ) {
                                                api.errare( 'Error in self.localSectionsSettingId.callbacks => self.setupSettingsToBeSaved()' , er );
                                          }

                                          // Removes and RE-register local settings and controls
                                          api.czr_sektions.generateUI({
                                                action : 'sek-generate-local-skope-options-ui',
                                                clean_settings_and_controls_first : true//<= see api.czr_sektions.generateUIforLocalSkopeOptions()
                                          });
                                          // 'czr-new-skopes-synced' is always sent on a previewer.refresh()
                                          api.previewer.unbind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                                    };
                                    api.previewer.bind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                              }//if ( sektionsLocalizedData.isSiteTemplateEnabled ) {
                              
                              
                        })
                        .fail( function( response ) {
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