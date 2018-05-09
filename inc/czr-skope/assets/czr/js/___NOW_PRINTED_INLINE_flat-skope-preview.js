
( function( api, $, _ ) {
  $( function() {
        api.preview.bind( 'sync', function( events ) {
              api.preview.send( 'czr-new-skopes-synced', {
                    czr_new_skopes : _wpCustomizeSettings.czr_new_skopes || [],
                    czr_stylesheet : _wpCustomizeSettings.czr_stylesheet || '',
                    isChangesetDirty : _wpCustomizeSettings.isChangesetDirty || false,
                    skopeGlobalDBOpt : _wpCustomizeSettings.skopeGlobalDBOpt || [],
              } );
        });
  });


} )( wp.customize, jQuery, _ );