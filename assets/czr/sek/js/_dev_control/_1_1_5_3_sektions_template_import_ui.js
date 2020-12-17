//global sektionsLocalizedData
// introduced in december 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            ////////////////////////////////////////////////////////
            // IMPORT TEMPLATE FROM GALLERY => FROM USER SAVED COLLECTION OR REMOTE API
            ////////////////////////////////////////////////////////
            // @return promise
            getTmplJsonFromUserTmpl : function( template_name ) {
                  var self = this, _dfd_ = $.Deferred();
                  wp.ajax.post( 'sek_get_user_tmpl_json', {
                        nonce: api.settings.nonce.save,
                        tmpl_post_name: template_name
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( response ) {
                        _dfd_.resolve( {success:true, tmpl_json:response });
                  })
                  .fail( function( er ) {
                        _dfd_.resolve( {success:false});
                        api.errorLog( 'ajax getTmplJsonFromUserTmpl => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>@missi18n error when fetching the template</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });

                  return _dfd_;
            },

            // @return promise
            getTmplJsonFromApi : function( template_name ) {
                  var self, _dfd_ = $.Deferred();
                  $.getJSON( 'https://api.nimblebuilder.com/wp-json/nimble/v2/cravan' )
                            .done( function( resp ) {
                                  if ( !_.isObject( resp ) || !resp.lib || !resp.lib.templates ) {
                                        api.errare( '::get_gallery_tmpl_json_and_import success but invalid response => ', resp  );
                                        _dfd_.resolve({success:false});
                                  }
                                  var _json_data = resp.lib.templates[template_name];
                                  if ( !_json_data ) {
                                        api.errare( '::get_gallery_tmpl_json_and_import => the requested template is not available', resp.lib.templates  );
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
                                        _dfd_.resolve({success:false});
                                  }
                                  _dfd_.resolve( {success:true, tmpl_json:_json_data } );

                            })
                            .fail(function( er ) {
                                  api.errare( '::get_gallery_tmpl_json_and_import failed => ', er  );
                                  _dfd_.resolve({success:false});
                            });

                    return _dfd_.promise();
            },


            // April 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/651
            // @param params {
            //    template_name : string,
            //    from : nimble_api or user,
            //    tmpl_import_mode : 3 possible import modes : replace, before, after
            // }
            get_gallery_tmpl_json_and_import : function( params ) {
                  var self = this;
                  params = $.extend( {
                      template_name : '',
                      from : 'user',
                      tmpl_import_mode : 'replace'
                  }, params || {});
                  var tmpl_name = params.template_name;
                  if ( _.isEmpty( tmpl_name ) || ! _.isString( tmpl_name ) ) {
                        api.errare('::import => error => invalid template name');
                  }
                  //console.log('get_gallery_tmpl_json_and_import params ?', params );
                  var _promise;
                  if ( 'nimble_api' === params.from ) {
                        // doc : https://api.jquery.com/jQuery.getJSON/
                        _promise = self.getTmplJsonFromApi(tmpl_name);
                  } else {
                        _promise = self.getTmplJsonFromUserTmpl(tmpl_name);
                  }

                  // response object structure :
                  // {
                  //  data : { nimble content },
                  //  metas : {
                  //    skope_id :
                  //    version :
                  //    tmpl_locations :
                  //    date :
                  //    theme :
                  //  }
                  // }
                  _promise.done( function( response ) {
                        //console.log('get_gallery_tmpl_json_and_import', params, response );
                        if ( response.success ) {
                              //console.log('IMPORT NIMBLE TEMPLATE', response.lib.templates[template_name] );
                              self.import_tmpl_from_gallery({
                                    pre_import_check : false,
                                    template_name : tmpl_name,
                                    template_data : response.tmpl_json,
                                    tmpl_import_mode : params.tmpl_import_mode
                              });
                        }
                  });
            },

            // IMPORT TEMPLATE FROM GALLERY
            // => REMOTE API COLLECTION + USER COLLECTION
            // @param params
            // {
            //       pre_import_check : false,
            //       template_name : tmpl_name,
            //       template_data : response.tmpl_json,
            //       tmpl_import_mode : 3 possible import modes : replace, before, after
            // }
            import_tmpl_from_gallery : function( params ) {
                  //console.log('import_tmpl_from_gallery', params );
                  var self = this;
                  params = params || {};
                  // normalize params
                  params = $.extend({
                      is_file_import : false,
                      pre_import_check : false,
                      tmpl_import_mode : 'replace'
                  }, params );

                  // SETUP FOR MANUAL INPUT
                  var __request__,
                      _scope = 'local';//<= when importing a template not manually, scope is always local


                  // remote template import case
                  if ( !params.template_data ) {
                        throw new Error( '::import_template => missing remote template data' );
                  }
                  __request__ = wp.ajax.post( 'sek_process_template_json', {
                        nonce: api.settings.nonce.save,
                        template_data : JSON.stringify( params.template_data ),
                        pre_import_check : false//<= might be used in the future do stuffs. For example when importing manually, this property is used to skip the img sniffing on the first pass.
                        //sek_export_nonce : api.settings.nonce.save,
                        //skope_id : 'local' === params.scope ? api.czr_skopeBase.getSkopeProperty( 'skope_id' ) : sektionsLocalizedData.globalSkopeId,
                        //active_locations : api.czr_sektions.activeLocations()
                  }).done( function( server_resp ) {
                        //api.infoLog('TEMPLATE PRE PROCESS DONE', server_resp );
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



                  /////////////////////////////////////////////
                  /// NOW THAT WE HAVE OUR PROMISE
                  /// 1) CHECK IF CONTENT IS WELL FORMED AND ELIGIBLE FOR API
                  /// 2) LET'S PROCESS THE SETTING ID'S
                  /// 3) ATTEMPT TO UPDATE THE SETTING API, LOCAL OR GLOBAL. ( always local for template import )

                  // fire a previewer loader removed on .always()
                  api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true, duration : 30000 });

                  // After 30 s display a failure notification
                  // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                  _.delay( function() {
                        if ( 'pending' !== __request__.state() )
                          return;
                        api.previewer.trigger('sek-notify', {
                              notif_id : 'import-too-long',
                              type : 'error',
                              duration : 20000,
                              message : [
                                    '<span>',
                                      '<strong>',
                                      sektionsLocalizedData.i18n['Import exceeds server response time, try to uncheck "import images" option.'],
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                  }, 30000 );


                  // At this stage, we are not in a pre-check case
                  // the ajax request is processed and will upload images if needed
                  __request__
                        .done( function( server_resp ) {
                              // When manually importing a file, the server adds a "success" property
                              // When loading a template this property is not sent. Let's normalize.
                              if ( _.isObject(server_resp) ) {
                                    server_resp = {success:true, data:server_resp};
                              }
                              //console.log('SERVER RESP 2 ?', server_resp );
                              if ( !api.czr_sektions.isImportedContentEligibleForAPI( server_resp, params ) ) {
                                    api.infoLog('::import_template problem => !api.czr_sektions.isImportedContentEligibleForAPI', server_resp, params );
                                    return;
                              }

                              //console.log('MANUAL IMPORT DATA', server_resp );
                              server_resp.data.data.collection = self.setIdsForImportedTmpl( server_resp.data.data.collection );
                              // and try to update the api setting
                              api.czr_sektions.doUpdateApiSettingAfter_TmplGalleryImport( server_resp, params );
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
                        });
            },//import_tmpl_from_gallery


            // fired on ajaxrequest done
            // At this stage, the server_resp data structure has been validated.
            // We can try to the update the api setting
            // @param params
            // {
            //       pre_import_check : false,
            //       template_name : tmpl_name,
            //       template_data : response.tmpl_json,
            //       tmpl_import_mode : 3 possible import modes : replace, before, after,
            //       is_file_import : false
            // }
            doUpdateApiSettingAfter_TmplGalleryImport : function( server_resp, params ){
                  //console.log('doUpdateApiSettingAfter_TmplGalleryImport ???', params, server_resp );
                  params = params || {};
                  if ( !api.czr_sektions.isImportedContentEligibleForAPI( server_resp, params ) && params.is_file_import ) {
                        api.previewer.send( 'sek-clean-loader', { cleanFullPageLoader : true });
                        return;
                  }

                  var _scope = 'local';// <= always local when template gallery import

                  //api.infoLog('TODO => verify metas => version, active locations, etc ... ');

                  // Update the setting api via the normalized method
                  // the scope will determine the setting id, local or global
                  api.czr_sektions.updateAPISetting({
                        action : 'sek-import-tmpl-from-gallery',
                        scope : _scope,//'global' or 'local'<= will determine which setting will be updated,
                        // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                        imported_content : server_resp.data,
                        tmpl_import_mode : params.tmpl_import_mode
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
                                      sektionsLocalizedData.i18n['Template successfully imported'],
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                  }).fail( function( response ) {
                        api.errare( '::doUpdateApiSettingAfter_TmplGalleryImport => error when firing ::updateAPISetting', response );
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
            }//doUpdateApiSettingAfter_TmplGalleryImport()

      });//$.extend()
})( wp.customize, jQuery );
