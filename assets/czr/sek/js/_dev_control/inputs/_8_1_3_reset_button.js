//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            reset_button : function( params ) {
                  var input = this;

                  // Schedule choice changes on button click
                  input.container.on( 'click', '[data-sek-reset-scope]', function( evt, params ) {
                        evt.stopPropagation();
                        var scope = $(this).data( 'sek-reset-scope' );

                        if ( _.isEmpty( scope ) || !_.contains(['local', 'global'], scope ) ) {
                              api.errare( 'reset_button input => invalid scope provided.', scope );
                              return;
                        }
                        api.czr_sektions.updateAPISetting({
                              action : 'sek-reset-collection',
                              scope : scope,//<= will determine which setting will be updated,
                              // => self.getGlobalSectionsSettingId() or self.localSectionsSettingId()
                        })
                        .done( function( resp) {
                              api.previewer.refresh();
                              api.previewer.trigger('sek-notify', {
                                    notif_id : 'reset-success',
                                    type : 'success',
                                    duration : 8000,
                                    message : [
                                          '<span>',
                                            '<strong>',
                                            sektionsLocalizedData.i18n['Reset complete'],
                                            '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                              if ( sektionsLocalizedData.isSiteTemplateEnabled ) {
                                    // api.infoLog( 'SITE TEMPLATE => TODO => on local reset => set the local setting ID to group skope value. See ::resetCollectionSetting');
                                    // // Feb 2021 : do we have group template that applies to this context ?
                                    // var site_tmpl_opts = api(sektionsLocalizedData.optNameForSiteTmplOptions)(),
                                    //       group_skope_id = api.czr_skopeBase.getSkopeProperty( 'skope_id' ,'group'),
                                    //       group_skope_sektions = api.czr_skopeBase.getSkopeProperty( 'group_sektions' ,'group');
                                    
                                    // // console.log('ALORS SITE TMPL ?', site_tmpl_opts, group_skope_id, group_skope_sektions );
            
                                    // // // FEB 2021 => TEST FOR ALL PAGE SKOPE
                                    // if ( _.isObject( site_tmpl_opts ) && site_tmpl_opts.site_templates && _.isObject( site_tmpl_opts.site_templates ) && site_tmpl_opts.site_templates.pages ) {
                                    //       if ( 'skp__all_page' === group_skope_id ) {
                                    //             if ( group_skope_sektions && group_skope_sektions.db_values ) {
                                    //                   console.log('SET GROUP SKOPE SEKTION ?');
                                    //                   newSettingValue = self.validateSettingValue( _.isObject( group_skope_sektions.db_value ) ? group_skope_sektions.db_value : self.getDefaultSektionSettingValue( 'local' ), 'local' );
                                    //             }
                                    //       }
                                    // }
                                    var _doThingsAfterRefresh = function() {
                                          console.log('DO THINGS AFTER RESET AND REFRESH');
                                          // console.log('scope ON RESET COMPLETE ?', scope );
                                          var _settingsToRemove_ = {
                                                'local' : api.czr_sektions.localSectionsSettingId(),//<= "nimble___[skp__post_page_10]"
                                                'global' : api.czr_sektions.getGlobalSectionsSettingId()//<= "nimble___[skp__global]"
                                          };
                                          api.remove( _settingsToRemove_[scope] );
      
                                          api.czr_sektions.cleanRegisteredLevelSettings();
      
                                          try { api.czr_sektions.setupSettingsToBeSaved( { dirty : true } ); } catch( er ) {
                                                api.errare( 'Error in self.localSectionsSettingId.callbacks => self.setupSettingsToBeSaved()' , er );
                                          }
                                          api.previewer.unbind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                                    };
                                    api.previewer.bind( 'czr-new-skopes-synced', _doThingsAfterRefresh );
                              }//if ( sektionsLocalizedData.isSiteTemplateEnabled ) {
                              
                              
                        })
                        .fail( function( response ) {
                              api.errare( 'reset_button input => error when firing ::updateAPISetting', response );
                              api.previewer.trigger('sek-notify', {
                                    notif_id : 'reset-failed',
                                    type : 'error',
                                    duration : 8000,
                                    message : [
                                          '<span>',
                                            '<strong>',
                                            sektionsLocalizedData.i18n['Reset failed'],
                                            '<br/>',
                                            '<i>' + response + '</i>',
                                            '</strong>',
                                          '</span>'
                                    ].join('')
                              });
                        });
                  });//on('click')
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );