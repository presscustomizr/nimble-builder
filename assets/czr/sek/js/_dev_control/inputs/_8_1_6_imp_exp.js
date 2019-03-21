//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            import_export : function() {
                  var input = this,
                      $file_input = input.container.find('input[name=sek-import-file]');

                  $file_input.on('change', function( evt ) {
                        input.container.find('button[data-czr-action="sek-import"]').toggleClass( 'disabled', _.isEmpty( $(this).val() ) );
                  });

                  // Schedule choice changes on button click
                  input.container.on( 'click', '[data-czr-action]', function( evt, params ) {
                        evt.stopPropagation();
                        var _action = $(this).data( 'czr-action' );
                        switch( _action ) {
                              case 'sek-export' :
                                    var query = [],
                                        query_params = {
                                        sek_export_nonce : api.settings.nonce.save,
                                        skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                                    };
                                    _.each( query_params, function(v,k) {
                                          query.push( encodeURIComponent(k) + '=' + encodeURIComponent(v) );
                                    });
                                    window.location.href = [
                                          sektionsLocalizedData.customizerURL ,
                                          '?',
                                          query.join('&')
                                    ].join('');
                              break;//'sek-export'

                              case 'sek-import' :
                                    var inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type );

                                    if ( $file_input.length < 1 || _.isUndefined( $file_input[0] ) || ! $file_input[0].files || _.isEmpty( $file_input.val() ) ) {
                                          api.previewer.trigger('sek-notify', {
                                                notif_id : 'missing-import-file',
                                                type : 'info',
                                                duration : 10000,
                                                message : [
                                                      '<span style="color:#0075a2">',
                                                        '<strong>',
                                                        sektionsLocalizedData.i18n['Missing file'],
                                                        '</strong>',
                                                      '</span>'
                                                ].join('')
                                          });
                                    } else {
                                          // make sure a previous warning gets removed
                                          api.notifications.remove( 'missing-import-file' );
                                          api.notifications.remove( 'import-success' );
                                          api.notifications.remove( 'import-failed' );

                                          // the uploading message is removed on .always()
                                          input.container.find('.sek-uploading').show();
                                          var fd = new FormData();
                                          fd.append( 'file_candidate', $file_input[0].files[0] );
                                          fd.append( 'action', 'sek_get_imported_file_content' );
                                          fd.append( 'nonce', api.settings.nonce.save );

                                          // Make sure we have a correct scope provided
                                          if ( !_.contains( ['local', 'global'], inputRegistrationParams.scope ) ) {
                                                api.errare('sek-import input => invalid scope provided', inputRegistrationParams.scope );
                                                return;
                                          }

                                          fd.append( 'skope', inputRegistrationParams.scope);
                                          $.ajax({
                                                url: wp.ajax.settings.url,
                                                data: fd,
                                                // Setting processData to false lets you prevent jQuery from automatically transforming the data into a query string. See the docs for more info. http://api.jquery.com/jQuery.ajax/
                                                // Setting the contentType to false is imperative, since otherwise jQuery will set it incorrectly. https://stackoverflow.com/a/5976031/33080
                                                processData: false,
                                                contentType: false,
                                                type: 'POST',
                                                // success: function(data){
                                                //   alert(data);
                                                // }
                                          }).done( function( server_resp ){
                                                // If the setting value is unchanged, no need to go further
                                                // is_local is decided with the input id => @see revision_history input type.
                                                var setId = 'local' === inputRegistrationParams.scope ? api.czr_sektions.localSectionsSettingId() : api.czr_sektions.getGlobalSectionsSettingId(),
                                                    unserialized_file_content = server_resp.data;
                                                if ( ! _.isObject( unserialized_file_content ) ) {
                                                      api.errare('sek-import input => invalid data sent from server', unserialized_file_content );
                                                      api.previewer.trigger('sek-notify', {
                                                            notif_id : 'import-failed',
                                                            type : 'error',
                                                            duration : 10000,
                                                            message : [
                                                                  '<span>',
                                                                    '<strong>',
                                                                    sektionsLocalizedData.i18n['Import failed, invalid file content'],
                                                                    '</strong>',
                                                                  '</span>'
                                                            ].join('')
                                                      });
                                                      return;
                                                }
                                                if ( _.isEqual( api( setId )(), unserialized_file_content ) ) {
                                                      api.infoLog('sek-import input => Setting unchanged');
                                                      return;
                                                }
                                                api.infoLog('api.czr_sektions.localSectionsSettingId()?', api.czr_sektions.localSectionsSettingId());
                                                api.infoLog('AJAX SUCCESS file_content ', unserialized_file_content );
                                                api.infoLog('inputRegistrationParams.scope ?', inputRegistrationParams.scope );

                                                // Update the setting api via the normalized method
                                                // the scope will determine the setting id, local or global
                                                api.czr_sektions.updateAPISetting({
                                                      action : 'sek-import-from-file',
                                                      scope : 'global' === inputRegistrationParams.scope,//<= will determine which setting will be updated,
                                                      // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                                                      imported_data : unserialized_file_content
                                                }).done( function() {
                                                      // Clean an regenerate the local option setting
                                                      // Settings are normally registered once and never cleaned, unlike controls.
                                                      // After the import, updating the setting value will refresh the sections
                                                      // but the local options, persisted in separate settings, won't be updated if the settings are not cleaned
                                                      if ( 'local' === inputRegistrationParams.scope ) {
                                                            api.czr_sektions.generateUI({
                                                                  action : 'sek-generate-local-skope-options-ui',
                                                                  clean_settings : true//<= see api.czr_sektions.generateUIforLocalSkopeOptions()
                                                            });
                                                      }

                                                      //_notify( sektionsLocalizedData.i18n['The revision has been successfully restored.'], 'success' );
                                                      api.previewer.refresh();
                                                      api.previewer.trigger('sek-notify', {
                                                            notif_id : 'import-success',
                                                            type : 'success',
                                                            duration : 10000,
                                                            message : [
                                                                  '<span>',
                                                                    '<strong>',
                                                                    sektionsLocalizedData.i18n['File successfully imported'],
                                                                    '</strong>',
                                                                  '</span>'
                                                            ].join('')
                                                      });
                                                }).fail( function( response ) {
                                                      api.errare( 'sek-import input => error when firing ::updateAPISetting', response );
                                                      api.previewer.trigger('sek-notify', {
                                                            notif_id : 'import-failed',
                                                            type : 'error',
                                                            duration : 10000,
                                                            message : [
                                                                  '<span>',
                                                                    '<strong>',
                                                                    sektionsLocalizedData.i18n['Import failed, invalid file content'],
                                                                    '</strong>',
                                                                  '</span>'
                                                            ].join('')
                                                      });
                                                });

                                                // Refresh the preview, so the markup is refreshed and the css stylesheet are generated
                                                api.previewer.refresh();
                                          }).fail( function( response ) {
                                                api.errare( 'sek-import input => ajax error', response );
                                                api.previewer.trigger('sek-notify', {
                                                      notif_id : 'import-failed',
                                                      type : 'error',
                                                      duration : 10000,
                                                      message : [
                                                            '<span>',
                                                              '<strong>',
                                                              sektionsLocalizedData.i18n['Import failed, file problem'],
                                                              '</strong>',
                                                            '</span>'
                                                      ].join('')
                                                });
                                          }).always( function() {
                                                input.container.find('.sek-uploading').hide();
                                                // Clean the file input val
                                                $file_input.val('').trigger('change');
                                          });
                                    }
                              break;//'sek-import'
                        }
                  });
            },
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );