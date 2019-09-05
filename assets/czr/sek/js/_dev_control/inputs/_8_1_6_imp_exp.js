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
                      $pre_import_button = input.container.find('button[data-czr-action="sek-pre-import"]'),
                      $file_input = input.container.find('input[name=sek-import-file]'),
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      currentScope = inputRegistrationParams.scope,
                      currentSetId = 'local' === inputRegistrationParams.scope ? api.czr_sektions.localSectionsSettingId() : api.czr_sektions.getGlobalSectionsSettingId();

                  if ( !_.contains(['local', 'global'], currentScope ) ) {
                        api.errare('api.czrInputMap.import_export => invalid currentScope', currentScope );
                  }
                  console.log('currentSetId', currentSetId, currentScope, inputRegistrationParams );
                  // Add event listener to set the button state
                  $file_input.on('change', function( evt ) {
                        $pre_import_button.toggleClass( 'disabled', _.isEmpty( $(this).val() ) );
                  });

                  // @return boolean
                  var customizeChangesetIncludesNimbleDirties = function() {
                        var hasNimbleDirties = false,
                            _dirties = wp.customize.dirtyValues();

                        if ( ! _.isEmpty( _dirties ) ) {
                              _.each( _dirties, function( _val, _setId ) {
                                    if ( hasNimbleDirties )
                                      return;
                                    // we're after setting id like
                                    // - nimble___[skp__post_post_1] <= local skope setting
                                    // - __nimble__4234ae1dc0fa__font_settings <= level setting
                                    // - __nimble_options__ <= global options
                                    // - __nimble__skp__post_post_1__localSkopeOptions__template <= local option setting
                                    hasNimbleDirties = -1 !== _setId.indexOf('nimble');
                              });
                        }
                        return hasNimbleDirties;
                  };

                  // Schedule action on button click
                  input.container.on( 'click', '[data-czr-action]', function( evt ) {
                        evt.stopPropagation();
                        var _action = $(this).data( 'czr-action' );
                        switch( _action ) {
                              case 'sek-export' :
                                    // prevent exporting if the customize changeset is dirty
                                    // => because the PHP sek_catch_export_action() doesn't have access to the customize changeset and needs the one persisted in DB
                                    if ( customizeChangesetIncludesNimbleDirties() ) {
                                          alert(sektionsLocalizedData.i18n['You need to publish before exporting.']);
                                          break;
                                    }
                                    // Is there something to export ?
                                    var currentVal = api( currentSetId )(),
                                        hasNoSections = true;
                                    _.each( currentVal.collection, function( locationData ){
                                          if ( !hasNoSections )
                                            return;
                                          if ( !_.isEmpty( locationData.collection ) ) {
                                              hasNoSections = false;
                                          }
                                    });
                                    if ( hasNoSections ) {
                                          alert(sektionsLocalizedData.i18n['Nothing to export.']);
                                          break;
                                    }
                                    _export( { scope : currentScope } );// local or global
                              break;//'sek-export'

                              case 'sek-pre-import' :
                                    // Can we import ?
                                    // => the current page must have at least one active location
                                    if( _.isEmpty( api.czr_sektions.activeLocations() ) ) {
                                          alert(sektionsLocalizedData.i18n['The current page has no available locations to import Nimble Builder sections.']);
                                          break;
                                    }

                                    // Before actually importing, let's do a preliminary
                                    _import( { pre_import_check : true } )
                                          .done( _pre_import_checks )
                                          .fail( function( error_resp ) {
                                                api.errare( 'sek_pre_import_checks failed', error_resp );
                                                _doAlwaysAfterImportApiSettingUpdate();
                                                _import();
                                          });
                              break;//'sek-import'
                              case 'sek-import-as-is' :
                                    _import();
                              break;
                              case 'sek-import-assign' :
                                    _import( { assign_missing_locations : true } );
                              break;
                              case 'sek-cancel-import' :
                                    _doAlwaysAfterImportApiSettingUpdate();
                              break;
                        }//switch
                  });//input.container.on( 'click' .. )


                  ////////////////////////////////////////////////////////
                  // PRE-IMPORT
                  ////////////////////////////////////////////////////////
                  // Compare current active locations with the imported ones
                  // if some imported locations are not rendered in the current context, reveal the import dialog
                  // before comparing locations, purge the collection of imported location from header and footer if any
                  // "nimble_local_header", "nimble_local_footer"
                  var _pre_import_checks = function( server_resp ) {
                        var currentActiveLocations = api.czr_sektions.activeLocations(),
                            importedActiveLocations = $.extend( true, [], _.isArray( server_resp.data.metas.active_locations ) ? server_resp.data.metas.active_locations : [] );

                        // filter to remove local header and footer before comparison with current active locations
                        importedActiveLocations = _.filter( importedActiveLocations, function( locId ) {
                              return !_.contains( ['nimble_local_header', 'nimble_local_footer'], locId );
                        });

                        if ( _.isArray( importedActiveLocations ) && _.isArray( currentActiveLocations ) ) {
                              var importedActiveLocationsNotAvailableInCurrentActiveLocations = $(importedActiveLocations).not(currentActiveLocations).get();

                              if ( !_.isEmpty( importedActiveLocationsNotAvailableInCurrentActiveLocations ) ) {
                                    $pre_import_button.hide();
                                    input.container.find('.czr-import-dialog').slideToggle();
                                    api.infoLog('sek-pre-import => imported locations missing in current page.', importedActiveLocationsNotAvailableInCurrentActiveLocations );
                              } else {
                                    _import();
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
                              _doAlwaysAfterImportApiSettingUpdate();
                        }
                  };//_pre_import_checks


                  ////////////////////////////////////////////////////////
                  // IMPORT
                  ////////////////////////////////////////////////////////
                  var _import = function( params ) {
                        params = params || {};
                        // Bail here if the file input is invalid
                        if ( $file_input.length < 1 || _.isUndefined( $file_input[0] ) || ! $file_input[0].files || _.isEmpty( $file_input.val() ) ) {
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


                        // make sure a previous warning gets removed
                        api.notifications.remove( 'missing-import-file' );
                        api.notifications.remove( 'import-success' );
                        api.notifications.remove( 'import-failed' );
                        api.notifications.remove( 'img-import-errors');

                        // display the uploading message
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
                        // When doing the pre_import_check, we inform the server about it
                        // so that the image sniff and upload is not processed at this stage.
                        if ( params.pre_import_check ) {
                              fd.append( 'pre_import_check', params.pre_import_check );
                        }

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

                        // When pre checking, return a promise
                        if ( params.pre_import_check ) {
                            return $.Deferred( function() {
                                  var dfd = this;
                                  __request__
                                        .done( function( server_resp ) {
                                              if( !server_resp.success ) {
                                                    dfd.reject( server_resp );
                                              }
                                              if ( !_isImportedContentEligibleForAPI( server_resp ) ) {
                                                    dfd.reject( server_resp );
                                              }
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

                        // fire a previewer loader
                        // and and uploading message
                        // both removed on .always()
                        input.container.find('.sek-uploading').show();
                        api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true });

                        // At this stage, we are not in a pre-check case
                        // the ajax request is processed and will upload images if needed
                        __request__
                              .done( function( server_resp ) {
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
                                    server_resp.data.data.collection = _setIds( server_resp.data.data.collection );
                                    // and try to update the api setting
                                    _doUpdateApiSetting( server_resp, params );
                              })
                              .fail( function( response ) {
                                    api.errare( 'sek-import input => ajax error', response );
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
                              .always( _doAlwaysAfterImportApiSettingUpdate );//$.ajax()
                  };//_import()


                  // @return a boolean
                  // server_resp : { success : true, data : {...} }
                  // check if :
                  // - server resp is a success
                  // - the server_response is well formed
                  var _isImportedContentEligibleForAPI = function( server_resp ) {
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

                        if ( _.isNull( importErrorMsg ) && _.isEqual( api( currentSetId )(), importSettingValue ) ) {
                              api.infoLog('sek-import input => Setting unchanged');
                              status = false;
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
                  };



                  // fired on ajaxrequest done
                  // At this stage, the server_resp data structure has been validated.
                  // We can try to the update the api setting
                  var _doUpdateApiSetting = function( server_resp, params ){
                        params = params || {};
                        if ( !_isImportedContentEligibleForAPI( server_resp ) ) {
                              _doAlwaysAfterImportApiSettingUpdate();
                              return;
                        }
                        // api.infoLog('api.czr_sektions.localSectionsSettingId()?', api.czr_sektions.localSectionsSettingId());
                        // api.infoLog('inputRegistrationParams.scope ?', inputRegistrationParams.scope );

                        //api.infoLog('TODO => verify metas => version, active locations, etc ... ');

                        // Update the setting api via the normalized method
                        // the scope will determine the setting id, local or global
                        api.czr_sektions.updateAPISetting({
                              action : 'sek-import-from-file',
                              scope : 'global' === inputRegistrationParams.scope,//<= will determine which setting will be updated,
                              // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                              imported_content : server_resp.data,
                              assign_missing_locations : params.assign_missing_locations,
                              keep_existing_sections : input.input_parent.czr_Input('keep_existing_sections')()
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
                              api.errare( 'sek-import input => error when firing ::updateAPISetting', response );
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
                  };//_doUpdateApiSetting()

                  var _doAlwaysAfterImportApiSettingUpdate = function() {
                        api.previewer.send( 'sek-clean-loader', { cleanFullPageLoader : true });
                        input.container.find('.sek-uploading').hide();
                        // Clean the file input val
                        $file_input.val('').trigger('change');
                        // Close the import dialog
                        input.container.find('.czr-import-dialog').hide();
                        // display back the pre import button
                        $pre_import_button.show();
                  };





                  ////////////////////////////////////////////////////////
                  // EXPORT
                  ////////////////////////////////////////////////////////
                  //@params { scope : 'local' or 'global' }
                  var _export = function( params ) {
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
                  };//_export()

            }//import_export()
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );