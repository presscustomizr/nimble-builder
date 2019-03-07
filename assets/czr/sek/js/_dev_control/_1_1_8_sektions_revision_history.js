//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // the input id determine if we fetch the revision history of the local or global setting
            // @return a deferred promise
            // @params object : { is_local:bool} <= 'local_revisions' === input.id
            getRevisionHistory : function(params) {
                  return wp.ajax.post( 'sek_get_revision_history', {
                        nonce: api.settings.nonce.save,
                        skope_id : params.is_local ? api.czr_skopeBase.getSkopeProperty( 'skope_id' ) : sektionsLocalizedData.globalSkopeId
                  });
            },

            // @return void()
            // Fetches the_content and try to set the setting value through normalized ::updateAPISetting method
            // @params {
            //    is_local : bool//<= 'local_revisions' === input.id
            //    revision_post_id : int
            // }
            setSingleRevision : function(params) {
                  var self = this;
                  var _notify = function( message, type ) {
                        api.previewer.trigger('sek-notify', {
                              notif_id : 'restore-revision-error',
                              type : type || 'info',
                              duration : 10000,
                              message : [
                                    '<span style="">',
                                      '<strong>',
                                      message || '',
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                  };
                  wp.ajax.post( 'sek_get_single_revision', {
                        nonce: api.settings.nonce.save,
                        //skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                        revision_post_id : params.revision_post_id
                  }).done( function( revision_value ){
                        // If the setting value is unchanged, no need to go further
                        // is_local is decided with the input id => @see revision_history input type.
                        var setId = params.is_local ? self.localSectionsSettingId() : self.getGlobalSectionsSettingId();
                        if ( _.isEqual( api( setId )(), revision_value ) ) {
                              _notify( sektionsLocalizedData.i18n['This is the current version.'], 'info' );
                              return;
                        }
                        // api.infoLog( 'getSingleRevision response', revision_value );
                        // api.infoLog( 'Current val', api(self.localSectionsSettingId())() );
                        self.updateAPISetting({
                              action : 'sek-restore-revision',
                              is_global_location : !params.is_local,//<= will determine which setting will be updated,
                              // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                              revision_value : revision_value
                        }).done( function() {
                              //_notify( sektionsLocalizedData.i18n['The revision has been successfully restored.'], 'success' );
                              api.previewer.refresh();
                        }).fail( function( response ) {
                              api.errare( '::setSingleRevision error when firing ::updateAPISetting', response );
                              _notify( sektionsLocalizedData.i18n['The revision could not be restored.'], 'error' );
                        });
                        //api.previewer.refresh();
                  }).fail( function( response ) {
                        api.errare( '::setSingleRevision ajax error', response );
                        _notify( sektionsLocalizedData.i18n['The revision could not be restored.'], 'error' );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );
