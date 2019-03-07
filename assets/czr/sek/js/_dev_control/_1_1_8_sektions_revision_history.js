//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            //@return a deferred promise
            getLocalRevisionList : function() {
                  return wp.ajax.post( 'sek_get_revision_list', {
                        nonce: api.settings.nonce.save,
                        skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  });
            },

            // @return void()
            // Fetches the_content and try to set the setting value through normalized ::updateAPISetting method
            setSingleRevision : function(revision_post_id) {
                  var self = this;
                  var _notify = function( message ) {
                        api.previewer.trigger('sek-notify', {
                              notif_id : 'restore-revision-error',
                              type : 'info',
                              duration : 10000,
                              message : [
                                    '<span style="color:#0075a2">',
                                      message || '',
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                  };
                  wp.ajax.post( 'sek_get_single_revision', {
                        nonce: api.settings.nonce.save,
                        //skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                        revision_post_id : revision_post_id
                  }).done( function( revision_value ){
                        // api.infoLog( 'getSingleRevision response', revision_value );
                        // api.infoLog( 'Current val', api(self.localSectionsSettingId())() );

                        //api( self.localSectionsSettingId() )( response );
                        // Always update the root fonts property after a module addition
                        // => because there might be a google font specified in the starting value or in a preset section
                        self.updateAPISetting({
                              action : 'sek-restore-revision',
                              is_global_location : false,
                              revision_value : revision_value
                        }).done( function() {
                              _notify( sektionsLocalizedData.i18n['The revision has been successfully restored.'] );
                              api.previewer.refresh();
                        }).fail( function( response ) {
                              api.errare( '::setSingleRevision error when firing ::updateAPISetting', response );
                              _notify( sektionsLocalizedData.i18n['The revision could not be restored.'] );
                        });
                        //api.previewer.refresh();
                  }).fail( function( response ) {
                        api.errare( '::setSingleRevision ajax error', response );
                        _notify( sektionsLocalizedData.i18n['The revision could not be restored.'] );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );
