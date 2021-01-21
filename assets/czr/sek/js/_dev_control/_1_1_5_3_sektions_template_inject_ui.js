//global sektionsLocalizedData
// introduced in december 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            ////////////////////////////////////////////////////////
            // INJECT TEMPLATE FROM GALLERY => FROM USER SAVED COLLECTION OR REMOTE API
            ////////////////////////////////////////////////////////
            // @return promise
            getTmplJsonFromUserTmpl : function( tmpl_name ) {
                  var self = this, _dfd_ = $.Deferred();
                  wp.ajax.post( 'sek_get_user_tmpl_json', {
                        nonce: api.settings.nonce.save,
                        tmpl_post_name: tmpl_name
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
                                    '<strong>error when fetching the template</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });

                  return _dfd_;
            },

            getTmplJsonFromApi : function( tmpl_name ) {
                  var self = this, _dfd_ = $.Deferred();
                  wp.ajax.post( 'sek_get_api_tmpl_json', {
                        nonce: api.settings.nonce.save,
                        api_tmpl_name: tmpl_name
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( response ) {
                        _dfd_.resolve( {success:true, tmpl_json:response });
                  })
                  .fail( function( er ) {
                        _dfd_.resolve( {success:false});
                        api.errorLog( 'ajax getTmplJsonFromApiTmpl => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>error when fetching the template</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });
                  return _dfd_;
            },

            


            // April 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/651
            // @param params {
            //    tmpl_name : string,
            //    tmpl_source : api_tmpl or user_tmpl,
            //    tmpl_inject_mode : 3 possible import modes : replace, before, after
            // }
            get_gallery_tmpl_json_and_inject : function( params ) {
                  var self = this;
                  params = $.extend( {
                      tmpl_name : '',
                      tmpl_source  : 'user',
                      tmpl_inject_mode : 'replace'
                  }, params || {});
                  var tmpl_name = params.tmpl_name;
                  if ( _.isEmpty( tmpl_name ) || ! _.isString( tmpl_name ) ) {
                        api.errare('::tmpl inject => error => invalid template name');
                  }
                  //console.log('get_gallery_tmpl_json_and_inject params ?', params );
                  var _promise;
                  if ( 'api_tmpl' === params.tmpl_source ) {
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
                        //console.log('get_gallery_tmpl_json_and_inject ??', params, response );
                        if ( response.success ) {
                              //console.log('INJECT NIMBLE TEMPLATE', response.lib.templates[tmpl_name] );
                              self.inject_tmpl_from_gallery({
                                    tmpl_name : tmpl_name,
                                    template_data : response.tmpl_json,
                                    tmpl_inject_mode : params.tmpl_inject_mode
                              });
                        }
                  });

                  // After 30 s display a failure notification
                  // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                  _.delay( function() {
                        if ( 'pending' !== _promise.state() )
                          return;
                        api.previewer.trigger('sek-notify', {
                              notif_id : 'import-too-long',
                              type : 'error',
                              duration : 20000,
                              message : [
                                    '<span>',
                                      '<strong>',
                                      'Template import failed',
                                      '</strong>',
                                    '</span>'
                              ].join('')
                        });
                  }, 30000 );
            },

            // INJECT TEMPLATE FROM GALLERY
            // => REMOTE API COLLECTION + USER COLLECTION
            // @param params
            // {
            //       tmpl_name : tmpl_name,
            //       template_data : response.tmpl_json,
            //       tmpl_inject_mode : 3 possible import modes : replace, before, after
            // }
            inject_tmpl_from_gallery : function( params ) {
                  //console.log('inject_tmpl_from_gallery', params );
                  var self = this;
                  params = params || {};
                  // normalize params
                  params = $.extend({
                      tmpl_inject_mode : 'replace'
                  }, params );

                  var _scope = 'local';//<= when injecting a template not manually, scope is always local
                      
                  // remote template inject case
                  if ( !params.template_data ) {
                        throw new Error( '::inject_tmpl => missing remote template data' );
                  }

                  /////////////////////////////////////////////
                  /// 1) CHECK IF CONTENT IS WELL FORMED AND ELIGIBLE FOR API
                  /// 2) LET'S PROCESS THE SETTING ID'S
                  /// 3) ATTEMPT TO UPDATE THE SETTING API, LOCAL OR GLOBAL. ( always local for template inject )
                  // fire a previewer loader removed on .always()
                  api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true, duration : 30000 });
                  //console.log('inject_tmpl_from_gallery ?', params );
                  if ( !api.czr_sektions.isImportedContentEligibleForAPI( {success:true, data:params.template_data}, params ) ) {
                        api.infoLog('::inject_tmpl problem => !api.czr_sektions.isImportedContentEligibleForAPI', params );
                        return;
                  }

                  params.template_data.data.collection = self.setIdsForImportedTmpl( params.template_data.data.collection );
                  // and try to update the api setting
                  api.czr_sektions.doUpdateApiSettingAfter_TmplGalleryImport( {success:true, data:params.template_data}, params );
            },//inject_tmpl_from_gallery


            // fired on ajaxrequest done
            // At this stage, the server_resp data structure has been validated.
            // We can try to the update the api setting
            // @param params
            // {
            //       tmpl_name : tmpl_name,
            //       template_data : response.tmpl_json,
            //       tmpl_inject_mode : 3 possible import modes : replace, before, after,
            // }
            doUpdateApiSettingAfter_TmplGalleryImport : function( server_resp, params ){
                  //console.log('doUpdateApiSettingAfter_TmplGalleryImport ???', params, server_resp );
                  params = params || {};
                  if ( !api.czr_sektions.isImportedContentEligibleForAPI( server_resp, params ) ) {
                        api.previewer.send( 'sek-clean-loader', { cleanFullPageLoader : true });
                        return;
                  }

                  var _scope = 'local';// <= always local when template gallery inject

                  //api.infoLog('TODO => verify metas => version, active locations, etc ... ');

                  // Update the setting api via the normalized method
                  // the scope will determine the setting id, local or global
                  api.czr_sektions.updateAPISetting({
                        action : 'sek-inject-tmpl-from-gallery',
                        scope : _scope,//'global' or 'local'<= will determine which setting will be updated,
                        // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                        injected_content : server_resp.data,
                        tmpl_inject_mode : params.tmpl_inject_mode
                  }).done( function() {
                        // Clean an regenerate the local option setting
                        // Settings are normally registered once and never cleaned, unlike controls.
                        // After the inject, updating the setting value will refresh the sections
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
