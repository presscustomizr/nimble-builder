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

                  // Add event listener to set the button state
                  $file_input.on('change', function( evt ) {
                        input.container.find('button[data-czr-action="sek-import"]').toggleClass( 'disabled', _.isEmpty( $(this).val() ) );
                  });

                  // Schedule action on button click
                  input.container.on( 'click', '[data-czr-action]', function( evt ) {
                        evt.stopPropagation();
                        var _action = $(this).data( 'czr-action' );
                        switch( _action ) {
                              case 'sek-export' :
                                    // prevent exporting if the customize changeset is dirty
                                    // => because the PHP sek_catch_export_action() doesn't have access to the customize changeset and needs the one persisted in DB
                                    if ( !_.isEmpty( wp.customize.dirtyValues() ) ) {
                                          alert(sektionsLocalizedData.i18n['You need to publish before exporting.']);
                                          break;
                                    }
                                    _export();
                              break;//'sek-export'

                              case 'sek-import' :
                                    _import();
                              break;//'sek-import'
                        }
                  });

                  // EXPORT
                  var _export = function() {
                          var query = [],
                              query_params = {
                                    sek_export_nonce : api.settings.nonce.save,
                                    skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                                    active_locations : api.czr_sektions.activeLocations()
                              };
                          _.each( query_params, function(v,k) {
                                query.push( encodeURIComponent(k) + '=' + encodeURIComponent(v) );
                          });

                          // The ajax action is used to make a pre-check
                          // the idea is to avoid a white screen when generating the download window afterwards
                          wp.ajax.post( 'sek_pre_export_checks', {
                                nonce: api.settings.nonce.save,
                                sek_export_nonce : api.settings.nonce.save,
                                skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                                active_locations : api.czr_sektions.activeLocations()
                          }).done( function() {
                                // disable the 'beforeunload' listeners generating popup window when the changeset is dirty
                                $( window ).off( 'beforeunload' );
                                // Generate a download window
                                // @see add_action( 'customize_register', '\Nimble\sek_catch_export_action', PHP_INT_MAX );
                                window.location.href = [
                                      sektionsLocalizedData.customizerURL,
                                      '?',
                                      query.join('&')
                                ].join('');
                                // re-enable the listeners
                                $( window ).on( 'beforeunload' );
                          }).fail( function( error_resp ) {
                                api.previewer.trigger('sek-notify', {
                                      notif_id : 'import-failed',
                                      type : 'error',
                                      duration : 10000,
                                      message : [
                                            '<span>',
                                              '<strong>',
                                              [ sektionsLocalizedData.i18n['Export failed'], encodeURIComponent( error_resp ) ].join(' '),
                                              '</strong>',
                                            '</span>'
                                      ].join('')
                                });
                          });
                  };//_export()


                  // IMPORT
                  var _import = function() {
                        var inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type );
                        // Bail here if the file input is invalid
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
                              return;
                        }


                        // make sure a previous warning gets removed
                        api.notifications.remove( 'missing-import-file' );
                        api.notifications.remove( 'import-success' );
                        api.notifications.remove( 'import-failed' );
                        api.notifications.remove( 'img-import-errors');

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
                                  unserialized_file_content = server_resp.data,
                                  import_success = server_resp.success,
                                  importErrorMsg = null;

                              // PHP generates the export like this:
                              // $export = array(
                              //     'data' => sek_get_skoped_seks( $_REQUEST['skope_id'] ),
                              //     'metas' => array(
                              //         'skope_id' => $_REQUEST['skope_id'],
                              //         'version' => NIMBLE_VERSION,
                              //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
                              //         'active_locations' => is_string( $_REQUEST['active_locations'] ) ? explode( ',', $_REQUEST['active_locations'] ) : array(),
                              //         'date' => date("Y-m-d")
                              //     )
                              // );
                              // @see sek_maybe_export()

                              api.infoLog('AJAX SUCCESS file_content ', server_resp, unserialized_file_content );
                              if ( !import_success ) {
                                   importErrorMsg = [ sektionsLocalizedData.i18n['Import failed'], unserialized_file_content ].join(' : ');
                              }

                              if ( _.isNull( importErrorMsg ) && ! _.isObject( unserialized_file_content ) ) {
                                    importErrorMsg = sektionsLocalizedData.i18n['Import failed, invalid file content'];
                              }

                              // Verify that we have the setting value and the import metas
                              var importSettingValue = unserialized_file_content.data,
                                  importMetas = unserialized_file_content.metas,
                                  imgImporErrors = unserialized_file_content.img_errors;

                              if ( _.isNull( importErrorMsg ) && ! _.isObject( importSettingValue ) ) {
                                    importErrorMsg = sektionsLocalizedData.i18n['Import failed, invalid file content'];
                              }

                              if ( _.isNull( importErrorMsg ) && ! _.isObject( importMetas ) ) {
                                    importErrorMsg = sektionsLocalizedData.i18n['Import failed, invalid file content'];
                              }

                              if ( _.isNull( importErrorMsg ) && _.isEqual( api( setId )(), importSettingValue ) ) {
                                    api.infoLog('sek-import input => Setting unchanged');
                                    return;
                              }

                              // bail here if we have an import error msg
                              if ( !_.isNull( importErrorMsg ) ) {
                                    api.errare('sek-import input => invalid data sent from server', unserialized_file_content );
                                    api.previewer.trigger('sek-notify', {
                                          notif_id : 'import-failed',
                                          type : 'error',
                                          duration : 30000,
                                          message : [
                                                '<span>',
                                                  '<strong>',
                                                  importErrorMsg,
                                                  '</strong>',
                                                '</span>'
                                          ].join('')
                                    });
                                    return;
                              }

                              // Img importation errors ?
                              if ( !_.isEmpty( imgImporErrors ) ) {
                                    api.previewer.trigger('sek-notify', {
                                          notif_id : 'img-import-errors',
                                          type : 'info',
                                          duration : 60000,
                                          message : [
                                                '<span style="color:#0075a2">',
                                                  [
                                                    '<strong>' + sektionsLocalizedData.i18n['Some image(s) could not be imported'] + '</strong><br/>',
                                                    '<span style="font-size:11px">' + imgImporErrors + '</span>'
                                                  ].join(' : '),
                                                '</span>'
                                          ].join('')
                                    });
                              }

                              api.infoLog('api.czr_sektions.localSectionsSettingId()?', api.czr_sektions.localSectionsSettingId());

                              api.infoLog('inputRegistrationParams.scope ?', inputRegistrationParams.scope );

                              api.infoLog('TODO => verify metas => version, active locations, etc ... ');

                              // Update the setting api via the normalized method
                              // the scope will determine the setting id, local or global
                              api.czr_sektions.updateAPISetting({
                                    action : 'sek-import-from-file',
                                    scope : 'global' === inputRegistrationParams.scope,//<= will determine which setting will be updated,
                                    // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                                    imported_content : unserialized_file_content
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
                                                  [ sektionsLocalizedData.i18n['Import failed'], response ].join(' : '),
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
                        });//$.ajax()
                  };//_import()
            }//import_export()
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );