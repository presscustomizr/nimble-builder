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
                                    // - nimble_global_opts <= global options
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
                                    //_export( { scope : currentScope } );// local or global
                                    api.czr_sektions.export_template( { scope : currentScope }  );
                              break;//'sek-export'

                              case 'sek-pre-import' :
                                    // Can we import ?
                                    // => the current page must have at least one active location
                                    if( _.isEmpty( api.czr_sektions.activeLocations() ) ) {
                                          alert(sektionsLocalizedData.i18n['The current page has no available locations to import Nimble Builder sections.']);
                                          break;
                                    }

                                    api.czr_sektions.import_template_from_file({
                                        pre_import_check : true,
                                        input : input,
                                        file_input : $file_input
                                    })
                                    .done( function( server_resp ) {
                                          api.czr_sektions.pre_checks_from_file_import( server_resp, {
                                              pre_import_check : false,
                                              input : input,
                                              file_input : $file_input
                                          });
                                    })
                                    .fail( function( error_resp ) {
                                          api.errare( 'import_export_ input => pre_checks_from_file_import failed', error_resp );
                                          api.czr_sektions.doAlwaysAfterFileImportAndApiSettingUpdate({
                                              input : input,
                                              file_input : $file_input
                                          });
                                          api.czr_sektions.import_template_from_file({
                                              input : input,
                                              file_input : $file_input
                                          });
                                    });

                              break;//'sek-import'
                              case 'sek-import-as-is' :
                                    api.czr_sektions.import_template_from_file({
                                        input : input,
                                        file_input : $file_input
                                    });
                              break;
                              case 'sek-import-assign' :
                                    api.czr_sektions.import_template_from_file({
                                        assign_missing_locations : true,
                                        input : input,
                                        file_input : $file_input
                                    });
                              break;
                              case 'sek-cancel-import' :
                                    api.czr_sektions.doAlwaysAfterFileImportAndApiSettingUpdate({
                                        input : input,
                                        file_input : $file_input
                                    });
                              break;
                        }//switch
                  });//input.container.on( 'click' .. )
            }//import_export()
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );