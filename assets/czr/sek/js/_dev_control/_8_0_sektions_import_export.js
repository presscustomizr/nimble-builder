//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            ////////////////////////////////////////////////////////
            // EXPORT
            ////////////////////////////////////////////////////////
            //@params { scope : 'local' or 'global' }
            export_template : function( params ) {
                  params = params || {};
                  // normalize params
                  params = $.extend({
                      scope : 'local',
                  }, params );

                  var query = [],
                      query_params = {
                            sek_export_nonce : api.settings.nonce.save,
                            skope_id : 'local' === params.scope ? api.czr_skopeBase.getSkopeProperty( 'skope_id' ) : sektionsLocalizedData.globalSkopeId,
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
                        skope_id : 'local' === params.scope ? api.czr_skopeBase.getSkopeProperty( 'skope_id' ) : sektionsLocalizedData.globalSkopeId,
                        active_locations : api.czr_sektions.activeLocations()
                  }).done( function() {
                        // disable the 'beforeunload' listeners generating popup window when the changeset is dirty
                        $(window).off( 'beforeunload' );
                        // Generate a download window
                        // @see add_action( 'customize_register', '\Nimble\sek_catch_export_action', PHP_INT_MAX );
                        window.location.href = [
                              sektionsLocalizedData.customizerURL,
                              '?',
                              query.join('&')
                        ].join('');
                        // re-enable the listeners
                        $(window).on( 'beforeunload' );
                  }).fail( function( error_resp ) {
                        api.previewer.trigger('sek-notify', {
                              notif_id : 'import-failed',
                              type : 'error',
                              duration : 30000,
                              message : [
                                    '<span>',
                                      '<strong>',
                                      [ sektionsLocalizedData.i18n['Export failed'], encodeURIComponent( error_resp ) ].join(' '),
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                  });
            },//export_template














            // @params
            // {
            //     is_manual_import : true,
            //     pre_import_check : false,
            //     assign_missing_locations : false,
            //     input : <= input instance when import is manual
            //     file_input : $file_input
            // }
            ////////////////////////////////////////////////////////
            // IMPORT
            ////////////////////////////////////////////////////////
            // April 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/651
            import_nimble_template : function( template_name ) {
                  template_name = template_name || 'test_one';
                  // doc : https://api.jquery.com/jQuery.getJSON/
                  $.getJSON( 'https://api.nimblebuilder.com/wp-json/nimble/v2/cravan' )
                      .done( function( resp ) {
                            if ( !_.isObject( resp ) || !resp.lib || !resp.lib.templates ) {
                                  api.errare( '::import_nimble_template success but invalid response => ', resp  );
                                  return;
                            }
                            var _json_data = resp.lib.templates[template_name];
                            if ( !_json_data ) {
                                  api.errare( '::import_nimble_template => the requested template is not available', resp.lib.templates  );
                                  api.previewer.trigger('sek-notify', {
                                        notif_id : 'missing-tmpl',
                                        type : 'info',
                                        duration : 10000,
                                        message : [
                                              '<span style="color:#0075a2">',
                                                '<strong>',
                                                '@missi18n the requested template is not available',
                                                '</strong>',
                                              '</span>'
                                        ].join('')
                                  });
                                  return;
                            }

                            //console.log('IMPORT NIMBLE TEMPLATE', resp.lib.templates[template_name] );
                            api.czr_sektions.import_template({
                                  is_manual_import : false,
                                  pre_import_check : false,
                                  template_name : 'test_one',
                                  template_data : _json_data
                            });
                      })
                      .fail(function( er ) {
                            api.errare( '::import_nimble_template failed => ', er  );
                      });


            },

            import_template : function( params ) {
                  //console.log('IN NEW IMPORT TEMPLATE', params );
                  params = params || {};
                  // normalize params
                  params = $.extend({
                      is_manual_import : true,
                      pre_import_check : false,
                      assign_missing_locations : false,
                      input : '',
                      file_input : ''
                  }, params );

                  // SETUP FOR MANUAL INPUT
                  var __request__,
                      _input = params.input,
                      _scope = 'local';//<= when importing a template not manually, scope is always local


                  /////////////////////////////////////////////
                  /// HANDLE TWO CASES :
                  /// 1) MANUAL IMPORT
                  /// 2) TEMPLATE IMPORT FROM COLLECTION
                  if ( params.is_manual_import ) {
                        // We must have a params.input when import is manual
                        if ( _.isEmpty( _input ) ) {
                            throw new Error( '::import_template => missing file_input param' );
                        }

                        // We must have a params.file_input when import is manual
                        if ( _.isEmpty( params.file_input ) ) {
                            throw new Error( '::import_template => missing file_input param' );
                        }

                        // Bail here if the file input is invalid
                        if ( params.file_input.length < 1 || _.isUndefined( params.file_input[0] ) || ! params.file_input[0].files || _.isEmpty( params.file_input.val() ) ) {
                              api.previewer.trigger('sek-notify', {
                                    notif_id : 'missing-import-file',
                                    type : 'info',
                                    duration : 30000,
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

                        // Set the scope in the case of a manual import
                        var inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( _input.id, _input.module.module_type );
                        _scope = inputRegistrationParams.scope;

                        // display the uploading message
                        _input.container.find('.sek-uploading').show();

                        // make sure a previous warning gets removed
                        api.notifications.remove( 'missing-import-file' );
                        api.notifications.remove( 'import-success' );
                        api.notifications.remove( 'import-failed' );
                        api.notifications.remove( 'img-import-errors');


                        //console.log('params.file_input[0].files[0] ??', params.file_input[0].files[0] );
                        var fd = new FormData();
                        fd.append( 'file_candidate', params.file_input[0].files[0] );
                        fd.append( 'action', 'sek_get_manually_imported_file_content' );
                        fd.append( 'nonce', api.settings.nonce.save );

                        // Make sure we have a correct scope provided
                        if ( !_.contains( ['local', 'global'], _scope ) ) {
                              api.errare('::import_template => invalid scope provided', _scope );
                              return;
                        }
                        fd.append( 'skope', _scope);
                        // When doing the pre_import_check, we inform the server about it
                        // so that the image sniff and upload is not processed at this stage.
                        if ( params.pre_import_check ) {
                              fd.append( 'pre_import_check', params.pre_import_check );
                        }
                        // fire an uploading message removed on .always()
                        _input.container.find('.sek-uploading').show();

                        __request__ = $.ajax({
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
                        });

                        // When pre checking on manual mode, return a promise
                        if ( params.pre_import_check ) {
                              return $.Deferred( function() {
                                    var dfd = this;
                                    __request__
                                          .done( function( server_resp ) {
                                                //console.log('__request__ done in pre_import_check', server_resp );
                                                if( !server_resp.success ) {
                                                      dfd.reject( server_resp );
                                                }
                                                if ( !api.czr_sektions.isImportedContentEligibleForAPI( server_resp, params ) ) {
                                                      dfd.reject( server_resp );
                                                }
                                                //console.log('ALORS DONE IN PRE IMPORT CHECK ?');
                                                dfd.resolve( server_resp );
                                          })
                                          .fail( function( server_resp ) {
                                                dfd.reject( server_resp );
                                          })
                                          .always( function() {
                                                //input.container.find('.sek-uploading').hide();
                                          });
                              });
                        }
                  }//params.is_manual_import
                  else {
                        // remote template import case
                        if ( !params.template_data ) {
                              throw new Error( '::import_template => missing remote template data' );
                        }
                        __request__ = wp.ajax.post( 'sek_process_template_file_content', {
                              nonce: api.settings.nonce.save,
                              template_data : JSON.stringify( params.template_data ),
                              pre_import_check : false//<= might be used in the future do stuffs. For example when importing manually, this property is used to skip the img sniffing on the first pass.
                              //sek_export_nonce : api.settings.nonce.save,
                              //skope_id : 'local' === params.scope ? api.czr_skopeBase.getSkopeProperty( 'skope_id' ) : sektionsLocalizedData.globalSkopeId,
                              //active_locations : api.czr_sektions.activeLocations()
                        }).done( function( server_resp ) {
                              api.infoLog('TEMPLATE PRE PROCESS DONE', server_resp );
                        }).fail( function( error_resp ) {
                              api.previewer.trigger('sek-notify', {
                                    notif_id : 'import-failed',
                                    type : 'error',
                                    duration : 30000,
                                    message : [
                                          '<span>',
                                            '<strong>',
                                            [ sektionsLocalizedData.i18n['Export failed'], encodeURIComponent( error_resp ) ].join(' '),
                                            '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        });
                  }


                  /////////////////////////////////////////////
                  /// NOW THAT WE HAVE OUR PROMISE
                  /// 1) CHECK IF CONTENT IS WELL FORMED AND ELIGIBLE FOR API
                  /// 2) LET'S PROCESS THE SETTING ID'S
                  /// 3) ATTEMPT TO UPDATE THE SETTING API, LOCAL OR GLOBAL. ( always local for template import )

                  // fire a previewer loader removed on .always()
                  api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true });

                  // At this stage, we are not in a pre-check case
                  // the ajax request is processed and will upload images if needed
                  __request__
                        .done( function( server_resp ) {

                              // When manually importing a file, the server adds a "success" property
                              // When loading a template this property is not sent. Let's normalize.
                              if ( !params.is_manual_import && _.isObject(server_resp) ) {
                                    server_resp = {success:true, data:server_resp};
                              }
                              //console.log('SERVER RESP ?', server_resp );
                              if ( !api.czr_sektions.isImportedContentEligibleForAPI( server_resp, params ) ) {
                                    api.infoLog('::import_template problem => !api.czr_sektions.isImportedContentEligibleForAPI', server_resp, params );
                                    return;
                              }

                              // we have a server_resp well structured { success : true, data : { data : , metas, img_errors } }
                              // Let's set the unique level ids
                              var _setIds = function( _data ) {
                                    if ( _.isObject( _data ) || _.isArray( _data ) ) {
                                          _.each( _data, function( _v, _k ) {
                                                // go recursive ?
                                                if ( _.isObject( _v ) || _.isArray( _v ) ) {
                                                      _data[_k] = _setIds( _v );
                                                }
                                                // double check on both the key and the value
                                                // also re-generates new ids when the export has been done without replacing the ids by '__rep__me__'
                                                if ( 'id' === _k && _.isString( _v ) && ( 0 === _v.indexOf( '__rep__me__' ) || 0 === _v.indexOf( '__nimble__' ) ) ) {
                                                      _data[_k] = sektionsLocalizedData.optPrefixForSektionsNotSaved + api.czr_sektions.guid();
                                                }
                                          });
                                    }
                                    return _data;
                              };

                              //console.log('MANUAL IMPORT DATA', server_resp );

                              server_resp.data.data.collection = _setIds( server_resp.data.data.collection );
                              // and try to update the api setting
                              api.czr_sektions.doUpdateApiSettingAfterTmplImport( server_resp, params );
                        })
                        .fail( function( response ) {
                              api.errare( '::import_template => ajax error', response );
                              api.previewer.trigger('sek-notify', {
                                    notif_id : 'import-failed',
                                    type : 'error',
                                    duration : 30000,
                                    message : [
                                          '<span>',
                                            '<strong>',
                                            sektionsLocalizedData.i18n['Import failed, file problem'],
                                            '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        })
                        .always( function() {
                              if ( params.is_manual_import ) {
                                    api.czr_sektions.doAlwaysAfterManualImportAndApiSettingUpdate( params );
                              }
                        });
            },//import_template












            ////////////////////////////////////////////////////////
            // PRE-IMPORT
            ////////////////////////////////////////////////////////
            // Compare current active locations with the imported ones
            // if some imported locations are not rendered in the current context, reveal the import dialog
            // before comparing locations, purge the collection of imported location from header and footer if any
            // "nimble_local_header", "nimble_local_footer"
            pre_import_checks : function( server_resp, params ) {
                  params = params || {};
                  // normalize params
                  params = $.extend({
                      is_manual_import : true,
                      pre_import_check : false,
                      assign_missing_locations : false,
                      input : '',
                      file_input : ''
                  }, params );

                  // We must have a params.input when import is manual
                  if ( params.is_manual_import && _.isEmpty( params.input ) ) {
                      throw new Error( 'api.czr_sektions.import_template => missing file_input param' );
                  }

                  var currentActiveLocations = api.czr_sektions.activeLocations(),
                      importedActiveLocations = $.extend( true, [], _.isArray( server_resp.data.metas.active_locations ) ? server_resp.data.metas.active_locations : [] ),
                      input = params.input,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type );

                  // filter to remove local header and footer before comparison with current active locations
                  importedActiveLocations = _.filter( importedActiveLocations, function( locId ) {
                        return !_.contains( ['nimble_local_header', 'nimble_local_footer'], locId );
                  });

                  if ( _.isArray( importedActiveLocations ) && _.isArray( currentActiveLocations ) ) {
                        var importedActiveLocationsNotAvailableInCurrentActiveLocations = $(importedActiveLocations).not(currentActiveLocations).get();

                        if ( !_.isEmpty( importedActiveLocationsNotAvailableInCurrentActiveLocations ) ) {
                              if ( params.is_manual_import ) {
                                    input.container.find('button[data-czr-action="sek-pre-import"]').hide();

                                    // Different messages for local and global
                                    // since sept 2019 for https://github.com/presscustomizr/nimble-builder/issues/495
                                    // @see tmpl-nimble-input___import_export input php template for messages
                                    if ( 'local' === inputRegistrationParams.scope ) {
                                          input.container.find('.czr-import-dialog.czr-local-import').slideToggle();
                                    } else {
                                          input.container.find('.czr-import-dialog.czr-global-import').slideToggle();
                                    }
                              }
                              api.infoLog('sek-pre-import => imported locations missing in current page.', importedActiveLocationsNotAvailableInCurrentActiveLocations );
                        } else {
                              api.czr_sektions.import_template( params );
                        }
                  } else {
                        // if current and imported location are not arrays, there's a problem.
                        api.previewer.trigger('sek-notify', {
                              notif_id : 'import-failed',
                              type : 'info',
                              duration : 30000,
                              message : [
                                    '<span style="color:#0075a2">',
                                      '<strong>',
                                      sektionsLocalizedData.i18n['Import failed'],
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                        if ( params.is_manual_import ) {
                              api.czr_sektions.doAlwaysAfterManualImportAndApiSettingUpdate( params );
                        }
                  }
            },//pre_import_checks







            // @return a boolean
            // server_resp : { success : true, data : {...} }
            // check if :
            // - server resp is a success
            // - the server_response is well formed
            isImportedContentEligibleForAPI : function( server_resp, params ) {
                  var status = true;
                  // If the setting value is unchanged, no need to go further
                  // is_local is decided with the input id => @see revision_history input type.
                  var unserialized_file_content = server_resp.data,
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

                  //api.infoLog('AJAX SUCCESS file_content ', server_resp, unserialized_file_content );
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

                  var currentSetId = api.czr_sektions.localSectionsSettingId();

                  // Manual import => set the relevant setting ID
                  if ( params.is_manual_import ) {
                      var _input = params.input,
                          inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( _input.id, _input.module.module_type );

                      currentSetId = 'local' === inputRegistrationParams.scope ? currentSetId : api.czr_sektions.getGlobalSectionsSettingId();
                  }


                  if ( _.isNull( importErrorMsg ) && _.isEqual( api( currentSetId )(), importSettingValue ) ) {
                        api.infoLog('::isImportedContentEligibleForAPI => Setting unchanged');
                        status = false;
                  }

                  // bail here if we have an import error msg
                  if ( !_.isNull( importErrorMsg ) ) {
                        api.errare('::isImportedContentEligibleForAPI => invalid data sent from server', unserialized_file_content );
                        api.errare('::isImportedContentEligibleForAPI => importErrorMsg', importErrorMsg );
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
                        status = false;
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
                  return status;
            },













            // fired on ajaxrequest done
            // At this stage, the server_resp data structure has been validated.
            // We can try to the update the api setting
            doUpdateApiSettingAfterTmplImport : function( server_resp, params ){
                  params = params || {};
                  if ( !api.czr_sektions.isImportedContentEligibleForAPI( server_resp, params ) && params.is_manual_import ) {
                        api.czr_sektions.doAlwaysAfterManualImportAndApiSettingUpdate( params );
                        return;
                  }

                  var _scope = 'local',
                      _keep_existing_sections = false;//<= only possibly true when importing manually

                  // Manual import => set the relevant scope
                  if ( params.is_manual_import ) {
                      var _input = params.input,
                          inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( _input.id, _input.module.module_type );

                      _scope = inputRegistrationParams.scope;
                      _keep_existing_sections = 'local' === inputRegistrationParams.scope ? _input.input_parent.czr_Input('keep_existing_sections')() : false;
                      // api.infoLog('api.czr_sektions.localSectionsSettingId()?', api.czr_sektions.localSectionsSettingId());
                      // api.infoLog('inputRegistrationParams.scope ?', inputRegistrationParams.scope );
                  }



                  //api.infoLog('TODO => verify metas => version, active locations, etc ... ');

                  // Update the setting api via the normalized method
                  // the scope will determine the setting id, local or global
                  api.czr_sektions.updateAPISetting({
                        action : 'sek-import-from-file',
                        scope : _scope,//'global' or 'local'<= will determine which setting will be updated,
                        // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                        imported_content : server_resp.data,
                        assign_missing_locations : params.assign_missing_locations,
                        keep_existing_sections : _keep_existing_sections
                  }).done( function() {
                        // Clean an regenerate the local option setting
                        // Settings are normally registered once and never cleaned, unlike controls.
                        // After the import, updating the setting value will refresh the sections
                        // but the local options, persisted in separate settings, won't be updated if the settings are not cleaned
                        if ( 'local' === _scope ) {
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
                              duration : 30000,
                              message : [
                                    '<span>',
                                      '<strong>',
                                      sektionsLocalizedData.i18n['File successfully imported'],
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                  }).fail( function( response ) {
                        api.errare( '::doUpdateApiSettingAfterTmplImport => error when firing ::updateAPISetting', response );
                        api.previewer.trigger('sek-notify', {
                              notif_id : 'import-failed',
                              type : 'error',
                              duration : 30000,
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
            },//doUpdateApiSettingAfterTmplImport()










            // Fired when params.is_manual_import only
            doAlwaysAfterManualImportAndApiSettingUpdate : function( params ) {
                  api.previewer.send( 'sek-clean-loader', { cleanFullPageLoader : true });

                  params = params || {};
                  // normalize params
                  params = $.extend({
                      is_manual_import : true,
                      pre_import_check : false,
                      assign_missing_locations : false,
                      input : '',
                      file_input : ''
                  }, params );

                  if ( !params.is_manual_import )
                    return;

                  var input = params.input;

                  input.container.find('.sek-uploading').hide();
                  // Clean the file input val
                  params.file_input.val('').trigger('change');
                  // Close the import dialog
                  input.container.find('.czr-import-dialog').hide();
                  // display back the pre import button
                  input.container.find('button[data-czr-action="sek-pre-import"]').show();
            }
      });//$.extend()
})( wp.customize, jQuery );