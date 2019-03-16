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
                  var input = this;

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
                              break;
                              case 'sek-import' :
                                    var inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                                        $file_input = input.container.find('input[name=sek-import-file]');

                                    if ( _.isEmpty( $file_input.val() ) ) {
                                          api.previewer.trigger('sek-notify', {
                                                notif_id : 'missing-import-file',
                                                type : 'info',
                                                duration : 10000,
                                                message : [
                                                      '<span style="color:#0075a2">',
                                                        '<strong>',
                                                        '@missi18n Missing file',//sektionsLocalizedData.i18n['It is recommended to disable your cache plugin when customizing your website.'],
                                                        '</strong>',
                                                      '</span>'
                                                ].join('')
                                          });
                                    } else {
                                          // make sure a previous warning gets removed
                                          api.notifications.remove( 'missing-import-file' );

                                          // prevent browser to print the message "Are you sure you want to leave the page?"
                                          //$( window ).off( 'beforeunload' );

                                          input.container.find('.sek-uploading').show();


                                          var fd = new FormData();
                                          fd.append( 'file_candidate', $file_input[0].files[0] );
                                          fd.append( 'action', 'sek_get_imported_file_content' );
                                          fd.append( 'nonce', api.settings.nonce.save );
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
                                          }).done( function( server_data ){
                                                // If the setting value is unchanged, no need to go further
                                                // is_local is decided with the input id => @see revision_history input type.
                                                var setId = 'local' === inputRegistrationParams.scope ? api.czr_sektions.localSectionsSettingId() : api.czr_sektions.getGlobalSectionsSettingId(),
                                                    unserialized_file_content = server_data.data;
                                                if ( ! _.isObject( unserialized_file_content ) ) {
                                                      api.errare('sek-import input => invalid data sent from server', unserialized_file_content );
                                                      return;
                                                }
                                                if ( _.isEqual( api( setId )(), unserialized_file_content ) ) {
                                                      api.infoLog('sek-import input => Setting unchanged');
                                                      return;
                                                }
                                                console.log('api.czr_sektions.localSectionsSettingId()?', api.czr_sektions.localSectionsSettingId());
                                                console.log('AJAX SUCCESS file_content ', unserialized_file_content );

                                                api.czr_sektions.updateAPISetting({
                                                      action : 'sek-import-from-file',
                                                      is_global_location : 'local' === inputRegistrationParams.scope,//<= will determine which setting will be updated,
                                                      // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                                                      imported_data : unserialized_file_content
                                                }).done( function() {
                                                      //_notify( sektionsLocalizedData.i18n['The revision has been successfully restored.'], 'success' );
                                                      api.previewer.refresh();
                                                      api.previewer.trigger('sek-notify', {
                                                            notif_id : 'import-success',
                                                            type : 'success',
                                                            duration : 10000,
                                                            message : [
                                                                  '<span>',
                                                                    '<strong>',
                                                                    '@missi18n file successfully imported',
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
                                                                    '@missi18n import failed, invalid file content',
                                                                    '</strong>',
                                                                  '</span>'
                                                            ].join('')
                                                      });
                                                });
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
                                                              '@missi18n import failed, file problem',
                                                              '</strong>',
                                                            '</span>'
                                                      ].join('')
                                                });
                                          }).always( function() {
                                                input.container.find('.sek-uploading').hide();
                                          });
                                    }
                              break;
                        }
                  });
            },
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );