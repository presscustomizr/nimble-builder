//global sektionsLocalizedData
//
//Note : idOfSectionToSave is set when user clicks on the save icon, and is reset when saving/updating closing save dialog
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // SAVE SECTION DIALOG BLOCK
            // fired in ::initialize()
            setupSaveSectionUI : function() {
                  var self = this;

                  // Declare api values and schedule reactions
                  self.saveSectionDialogVisible = new api.Value( false );// Hidden by default

                  // The observer for visibility
                  // connected to other observers
                  self.saveSectionDialogVisible.bind( function( visible ){
                        if ( visible ) {
                              // close template gallery
                              // close level tree
                              self.templateGalleryExpanded(false);
                              self.levelTreeExpanded(false);
                              if ( self.tmplDialogVisible ) {
                                    self.tmplDialogVisible(false);
                              }
                        }
                        self.toggleSaveSectionUI(visible);
                  });

                  // Will store the collection of saved sections
                  self.allSavedSections = new api.Value('_not_populated_');
                  // When the collection is refreshed
                  // - populate select options
                  // - set the select value to default 'none'
                  self.allSavedSections.bind( function( sec_collection ) {
                        if ( !_.isObject(sec_collection) ) {
                              api.errare('error setupSaveSectionUI => section collection should be an object');
                              return;
                        }
                        sec_collection = _.isEmpty(sec_collection) ? {} : sec_collection;
                        self.refreshSectionPickerHtml( sec_collection );
                  });

                  self.saveSectionDialogMode = new api.Value('hidden');// 'save' default mode is set when dialog html is rendered
                  self.saveSectionDialogMode.bind( function(mode){
                        if ( !_.contains(['hidden', 'save', 'update', 'remove', 'edit' ], mode ) ) {
                              api.errare('error setupSaveSectionUI => unknown section dialog mode', mode );
                              mode = 'save';
                        }

                        // Set the button pressed state
                        var $secSaveDialogWrap = $('#nimble-top-section-save-ui'),
                            $titleInput = $secSaveDialogWrap.find('#sek-saved-section-title'),
                            $descInput = $secSaveDialogWrap.find('#sek-saved-section-description');

                        $secSaveDialogWrap.find('[data-section-mode-switcher]').attr('aria-pressed', false );
                        $secSaveDialogWrap.find('[data-section-mode-switcher="' + mode +'"]').attr('aria-pressed', true );

                        // update the current mode
                        $('#nimble-top-section-save-ui').attr('data-sek-section-dialog-mode', mode );

                        // make sure the remove dialog is hidden
                        $secSaveDialogWrap.removeClass('sek-removal-confirmation-opened');
                        var $selectEl;
                        // execute actions depending on the selected mode
                        switch( mode ) {
                              case 'save' :
                                    // When selecting 'save', make sure the title and description input are cleaned
                                    $titleInput.val('');
                                    $descInput.val('');
                              break;
                              case 'update' :
                              case 'edit' :
                                    $selectEl = $secSaveDialogWrap.find('.sek-saved-section-picker');
                                    // Make sure the select value is always reset when switching mode
                                    $selectEl.val('none').trigger('change');

                                    self.setSavedSectionCollection().done( function( sec_collection ) {
                                          // refresh section picker in case the user updated without changing anything
                                          self.refreshSectionPickerHtml();
                                          $selectEl.val( self.userSectionToEdit || 'none' ).trigger('change');
                                          self.userSectionToEdit = null;
                                    });
                              break;
                              case 'remove' :
                                    console.log('sOOO ?', self.userSectionToRemove );
                                    $selectEl = $secSaveDialogWrap.find('.sek-saved-section-picker');
                                    // Make sure the select value is always reset when switching mode
                                    $selectEl.val('none').trigger('change');

                                    self.setSavedSectionCollection().done( function( sec_collection ) {
                                          // refresh section picker in case the user updated without changing anything
                                          self.refreshSectionPickerHtml();
                                          $selectEl.val( self.userSectionToRemove || 'none' ).trigger('change');
                                          self.userSectionToRemove = null;
                                    });
                              break;
                        }//switch

                        // when user clicks on the remove icon of a section in the collection
                        // => hide save and update buttons
                        if ( 'remove' === mode && _.isEmpty( self.idOfSectionToSave ) ) {
                            $secSaveDialogWrap.addClass('sek-is-removal-only');
                        } else {
                            $secSaveDialogWrap.removeClass('sek-is-removal-only');
                        }
                  });//self.saveSectionDialogMode.bind()

            },








            ///////////////////////////////////////////////
            ///// RENDER DIALOG BOX AND SCHEDULE CLICK ACTIONS
            refreshSectionPickerHtml : function( sec_collection ) {
                  sec_collection = sec_collection || this.allSavedSections();

                  var $secSaveDialogWrap = $('#nimble-top-section-save-ui'),
                      $selectEl = $secSaveDialogWrap.find('.sek-saved-section-picker');
                  // Make sure the select value is always reset when switching mode
                  $selectEl.val('none').trigger('change');

                  // empty all options but the default 'none' one
                  $selectEl.find('option').each( function() {
                        if ( 'none' !== $(this).attr('value') ) {
                              $(this).remove();
                        }
                  });

                  // Make sure we don't populate the collection twice ( if user clicks two times fast )
                  // if ( $secSaveDialogWrap.hasClass('sec-collection-populated') )
                  //   return;

                  var _default_title = 'section title not set',
                      _title,
                      _last_modified_date,
                      _html = '';
                  _.each( sec_collection, function( _sec_data, _sec_post_name ) {
                        if ( !_.isObject(_sec_data) )
                          return;
                        _last_modified_date = _sec_data.last_modified_date ? _sec_data.last_modified_date : '';
                        _title = _sec_data.title ? _sec_data.title : _default_title;
                        _html +='<option value="' + _sec_post_name + '">' + [ _title, sektionsLocalizedData.i18n['Last modified'] + ' : ' + _last_modified_date ].join(' | ') + '</option>';
                  });

                  $selectEl.append(_html);

                  // flag so we know it's done
                  // => controls the CSS visibility of the select element
                  $secSaveDialogWrap.addClass('section-collection-populated');
            },


            //@param = { }
            renderSectionSaveUI : function( params ) {
                  if ( $('#nimble-top-section-save-ui').length > 0 )
                    return $('#nimble-top-section-save-ui');

                  var self = this;

                  try {
                        _tmpl =  wp.template( 'nimble-top-section-save-ui' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing nimble-top-section-save-ui template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );
                  return $('#nimble-top-section-save-ui');
            },



            ///////////////////////////////////////////////
            ///// DOM EVENTS
            // Fired once, on first rendering
            maybeScheduleSectionSaveDOMEvents : function() {
                  var self = this, $secSaveDialogWrap = $('#nimble-top-section-save-ui');
                  if ( $secSaveDialogWrap.data('nimble-sec-save-dom-events-scheduled') )
                    return;

                  // ATTACH DOM EVENTS
                  // Dialog Mode Switcher
                  $secSaveDialogWrap
                        .on( 'click', '[data-section-mode-switcher]', function(evt) {
                              evt.preventDefault();
                              self.saveSectionDialogMode($(this).data('section-mode-switcher'));
                        })

                        // React to section select
                        // update title and description fields on section selection
                        .on( 'change', '.sek-saved-section-picker', function(evt){ self.reactOnSectionSelection(evt, $(this) ); })

                        // SAVE
                        .on( 'click', '.sek-do-save-section', function(evt){
                              $secSaveDialogWrap.addClass('nimble-section-processing-ajax');
                              self.saveOrUpdateSavedSection(evt).done( function( response ) {
                                    $secSaveDialogWrap.removeClass('nimble-section-processing-ajax');
                                    if ( response.success ) {
                                          self.saveSectionDialogVisible( false );
                                          self.setSavedSectionCollection( { refresh : true } );// <= true for refresh
                                    }
                              });
                        })

                        // UPDATE
                        .on( 'click', '.sek-do-update-section', function(evt){
                              var $selectEl = $secSaveDialogWrap.find('.sek-saved-section-picker'),
                              sectionPostNameCandidateForUpdate = $selectEl.val();
                              // make sure we don't try to remove the default option
                              if ( 'none' === sectionPostNameCandidateForUpdate || _.isEmpty(sectionPostNameCandidateForUpdate) )
                              return;

                              $secSaveDialogWrap.addClass('nimble-section-processing-ajax');
                              self.saveOrUpdateSavedSection(evt, sectionPostNameCandidateForUpdate).done( function(response) {
                                    $secSaveDialogWrap.removeClass('nimble-section-processing-ajax');
                                    if ( response.success ) {
                                          self.saveSectionDialogVisible( false );
                                          self.setSavedSectionCollection( { refresh : true } )// <= true for refresh
                                                .done( function( sec_collection ) {
                                                      // refresh section picker in case the user updated without changing anything
                                                      self.refreshSectionPickerHtml();
                                                });
                                    }
                              });
                        })

                        // REMOVE
                        // Reveal remove dialog
                        .on( 'click', '.sek-open-remove-confirmation', function(evt){
                              $secSaveDialogWrap.addClass('sek-removal-confirmation-opened');
                        })

                        // Do Remove
                        .on( 'click', '.sek-do-remove-section', function(evt){
                              var $selectEl = $secSaveDialogWrap.find('.sek-saved-section-picker'),
                              sectionPostNameCandidateForRemoval = $selectEl.val();
                              // make sure we don't try to remove the default option
                              if ( 'none' === sectionPostNameCandidateForRemoval || _.isEmpty(sectionPostNameCandidateForRemoval) )
                              return;

                              $secSaveDialogWrap.addClass('nimble-section-processing-ajax');
                              self.removeSavedSection(evt, sectionPostNameCandidateForRemoval).done( function(response) {
                                    $secSaveDialogWrap.removeClass('nimble-section-processing-ajax');
                                    $secSaveDialogWrap.removeClass('sek-removal-confirmation-opened');
                                    if ( response.success ) {
                                          self.setSavedSectionCollection( { refresh : true } );// <= true for refresh
                                    }
                              });
                        })

                        // Cancel Remove
                        .on( 'click', '.sek-cancel-remove-section', function(evt){
                              $secSaveDialogWrap.removeClass('sek-removal-confirmation-opened');
                        });


                  // Switch to update mode
                  //$secSaveDialogWrap.on( 'click', '[data-section-mode-switcher="update"]', function(evt){  });

                  $('.sek-close-dialog', $secSaveDialogWrap ).on( 'click', function(evt) {
                        evt.preventDefault();
                        self.saveSectionDialogVisible(false);
                  });

                  // Say we're done with DOM event scheduling
                  $secSaveDialogWrap.data('nimble-sec-save-dom-events-scheduled', true );
            },



            // Is used in update and remove modes
            reactOnSectionSelection : function(evt, $selectEl ){
                  var self = this,
                      $secSaveDialogWrap = $('#nimble-top-section-save-ui'),
                      _sectionPostName = $selectEl.val(),
                      $titleInput = $secSaveDialogWrap.find('#sek-saved-section-title'),
                      $descInput = $secSaveDialogWrap.find('#sek-saved-section-description'),
                      // The informative class control the visibility of the title and the description in CSS
                      _informativeClass = 'update' === self.saveSectionDialogMode() ? 'sek-section-update-selected' : 'sek-section-remove-selected';

                  if ( 'none' === _sectionPostName ) {
                        $titleInput.val('');
                        $descInput.val('');
                        $secSaveDialogWrap.removeClass(_informativeClass);
                  } else {
                        var _allSavedSections = self.allSavedSections();
                        var _selectedSection = _sectionPostName;

                        // normalize
                        _allSavedSections = ( _.isObject(_allSavedSections) && !_.isArray(_allSavedSections) ) ? _allSavedSections : {};
                        _allSavedSections[_sectionPostName] = $.extend( {
                            title : '',
                            description : '',
                            last_modified_date : ''
                        }, _allSavedSections[_sectionPostName] || {} );

                        $titleInput.val( _allSavedSections[_sectionPostName].title );
                        $descInput.val( _allSavedSections[_sectionPostName].description );
                        $secSaveDialogWrap.addClass(_informativeClass);
                  }
            },




            ///////////////////////////////////////////////
            ///// AJAX ACTIONS
            // Fired on 'click' on .sek-do-save-section btn
            // @param sectionPostNameCandidateForUpdate is only provided when saving
            saveOrUpdateSavedSection : function(evt, sectionPostNameCandidateForUpdate ) {
                  var self = this,
                        _dfd_ = $.Deferred(),
                        _isEditSectionMode = 'edit' === self.saveSectionDialogMode();

                  // idOfSectionToSave is set when reacting to click action
                  // @see react to preview 'sek-toggle-save-section-ui'
                  if ( !_isEditSectionMode ) {
                        if ( !self.idOfSectionToSave || _.isEmpty( self.idOfSectionToSave ) ) {
                              api.errare('saveOrUpdateSavedSection => error => missing section id');
                              return _dfd_.resolve( {success:false});
                        }
                  }
                  evt.preventDefault();
                  var $_title = $('#sek-saved-section-title'),
                      section_title = $_title.val(),
                      section_description = $('#sek-saved-section-description').val(),
                      sectionModel;

                  // Only get the section model when not in edit section mode 
                  if ( !_isEditSectionMode ) {
                        sectionModel = $.extend( true, {}, self.getLevelModel( self.idOfSectionToSave ) );
                        if ( 'no_match' == sectionModel ) {
                              api.errare('saveOrUpdateSavedSection => error => no section model with id ' + self.idOfSectionToSave );
                              return _dfd_.resolve( {success:false});
                        }
                        // Do some pre-processing before ajaxing
                        // Note : ids will be replaced server side
                        sectionModel = self.preProcessSection( sectionModel );
                        if ( !_.isObject( sectionModel ) ) {
                              api.errare('::saveOrUpdateSavedSection => error => invalid sectionModel');
                              return _dfd_.resolve( {success:false});
                        }
                  }

                  if ( _.isEmpty( section_title ) ) {
                        $_title.addClass('error');
                        api.previewer.trigger('sek-notify', {
                              type : 'error',
                              duration : 10000,
                              message : [
                                    '<span style="font-size:0.95em">',
                                      '<strong>' + sektionsLocalizedData.i18n['A title is required'] + '</strong>',
                                    '</span>'
                              ].join('')

                        });
                        return _dfd_.resolve( {success:false});
                  }

                  $('#sek-saved-section-title').removeClass('error');

                  wp.ajax.post( 'sek_save_user_section', {
                        nonce: api.settings.nonce.save,
                        section_data: _isEditSectionMode ? '' : JSON.stringify( sectionModel ),
                        // the following will be saved in 'metas'
                        section_title: section_title,
                        section_description: section_description,
                        section_post_name: sectionPostNameCandidateForUpdate || '',// <= provided when updating a section
                        skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                        edit_metas_only: _isEditSectionMode ? 'yes' : 'no'//<= in this case we only update title and description. Not the template content
                        //active_locations : api.czr_sektions.activeLocations()
                  })
                  .done( function( response ) {
                        //console.log('SAVED POST ID', response );
                        _dfd_.resolve( {success:true});

                        // response is {section_post_id: 436}
                        api.previewer.trigger('sek-notify', {
                            type : 'success',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Template saved'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                  })
                  .fail( function( er ) {
                        _dfd_.resolve( {success:false});
                        api.errorLog( 'ajax sek_save_section => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Error when processing template'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                  })
                  .always( function() {
                        // reset the id of section to save
                        // => because we need to know when we are in 'remove' mode when user clicked on remove icon in the section collection, => which hides save and update buttons
                        self.idOfSectionToSave = null;
                  });
                  return _dfd_;
            },//saveOrUpdateSavedSection


            // @return a section model
            // Note : ids are reset server side
            // Example of section model before preprocessing
            // {
            //    collection: [{…}]
            //    id: "" //<= reset server side
            //    level: "section"
            //    is_nested : false
            //    options: {bg: {…}}
            //    ver_ini: "1.1.8"
            // }
            preProcessSection : function( sectionModel ) {
                  if ( !_.isObject( sectionModel ) ) {
                        return null;
                  }
                  var preprocessedModel = $.extend( {}, true, sectionModel );
                  // Make sure a nested section is saved as normal
                  if ( _.has( preprocessedModel, 'is_nested') ) {
                        preprocessedModel = _.omit( preprocessedModel, 'is_nested' );
                  }
                  return preprocessedModel;
            },


            // Fired on 'click on .sek-do-remove-section btn
            removeSavedSection : function(evt, sectionPostNameCandidateForRemoval ) {
                  var self = this, _dfd_ = $.Deferred();
                  evt.preventDefault();
                  wp.ajax.post( 'sek_remove_user_section', {
                        nonce: api.settings.nonce.save,
                        section_post_name: sectionPostNameCandidateForRemoval
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( response ) {
                        _dfd_.resolve( {success:true});
                        // response is {section_post_id: 436}
                        api.previewer.trigger('sek-notify', {
                            type : 'success',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Template removed'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                  })
                  .fail( function( er ) {
                        _dfd_.resolve( {success:false});
                        api.errorLog( 'ajax sek_remove_section => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Error when processing templates'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                  })
                  .always( function() {
                        // reset the id of section to save
                        // => because we need to know when we are in 'remove' mode when user clicked on remove icon in the section collection, => which hides save and update buttons
                        self.idOfSectionToSave = null;
                  });

                  return _dfd_;
            },



            ///////////////////////////////////////////////
            ///// REVEAL / HIDE DIALOG BOX
            /// react on self.saveSectionDialogVisible.bind(...)
            // @return void()
            // self.saveSectionDialogVisible.bind( function( visible ){
            //       self.toggleSaveSectionUI( visible );
            // });
            toggleSaveSectionUI : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderSectionSaveUI({}) ).done( function( $_el ) {
                                  self.maybeScheduleSectionSaveDOMEvents();//<= schedule on the first display only
                                  self.saveUIContainer = $_el;
                                  //display
                                  _.delay( function() {
                                        // set dialog mode now so we display the relevant fields on init
                                        self.saveSectionDialogMode('save');// Default mode is save
                                        self.cachedElements.$body.addClass('sek-save-section-ui-visible');
                                  }, 200 );
                                  // set section id input value
                                  //$('#sek-saved-section-id').val( sectionId );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            self.cachedElements.$body.removeClass('sek-save-section-ui-visible');
                            if ( $( '#nimble-top-section-save-ui' ).length > 0 ) {
                                  //remove Dom element after slide up
                                  _.delay( function() {
                                        // set dialog mode back to 'hidden' mode
                                        self.saveSectionDialogMode = self.saveSectionDialogMode ? self.saveSectionDialogMode : new api.Value();
                                        self.saveSectionDialogMode('hidden');
                                        self.saveUIContainer.remove();
                                        // reset the id of section to save
                                        // => because we need to know when we are in 'remove' mode when user clicked on remove icon in the section collection, => which hides save and update buttons
                                        self.idOfSectionToSave = null;
                                        dfd.resolve();
                                  }, 250 );
                            } else {
                                dfd.resolve();
                            }
                            return dfd.promise();
                      };

                  if ( visible ) {
                        _renderAndSetup();
                  } else {
                        _hide().done( function() {
                              self.saveSectionDialogVisible( false );//should be already false
                        });
                  }
            },


            ///////////////////////////////////////////////
            ///// TMPL COLLECTION
            // @return $.promise
            // @param params = {refresh : false};
            setSavedSectionCollection : function( params ) {
                  var self = this, _dfd_ = $.Deferred();

                  // refresh is true on save, update, remove success
                  params = params || {refresh : false};

                  // If the collection is already set, return it.
                  // unless this is a "refresh" case
                  if ( !params.refresh && '_not_populated_' !== self.allSavedSections() ) {
                        return _dfd_.resolve( self.allSavedSections() );
                  }

                  var _promise;
                  // Prevent a double request while ajax request is being processed
                  if ( self.sectionCollectionPromise && 'pending' === self.sectionCollectionPromise.state() ) {
                        _promise = self.sectionCollectionPromise;
                  } else {
                        _promise = self.getSavedSectionCollection( params );
                  }
                  _promise.done( function( sec_collection ) {
                        self.allSavedSections( sec_collection );
                        _dfd_.resolve( sec_collection );
                  });
                  return _dfd_.promise();
            },

            // @return a promise
            // @param params = {refresh : false};
            // also used from the input Constructor of sek_my_sections_sec_picker_module
            // @param params = {refresh : false};
            getSavedSectionCollection : function( params ) {
                  var self = this;
                  // refresh is true on save, update, remove success
                  params = params || {refresh : false};

                  self.sectionCollectionPromise = $.Deferred();
                  if ( !params.refresh && '_not_populated_' !== self.allSavedSections() ) {
                        self.sectionCollectionPromise.resolve( self.allSavedSections() );
                        return self.sectionCollectionPromise;
                  }
                  wp.ajax.post( 'sek_get_all_saved_sections', {
                        nonce: api.settings.nonce.save
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( sec_collection ) {
                        if ( _.isObject(sec_collection) && !_.isArray( sec_collection ) ) {
                              self.sectionCollectionPromise.resolve( sec_collection );
                        } else {
                              self.sectionCollectionPromise.resolve( {} );
                              if ( !_.isEmpty( sec_collection ) ) {
                                    api.errorLog('control::getSavedSectionCollection => collection is empty or invalid');
                              }
                        }

                        // response is {section_post_id: 436}
                        //self.saveSectionDialogVisible( false );
                        // api.previewer.trigger('sek-notify', {
                        //     type : 'success',
                        //     duration : 10000,
                        //     message : [
                        //           '<span style="font-size:0.95em">',
                        //             '<strong>Your section has been saved.</strong>',
                        //           '</span>'
                        //     ].join('')
                        // });
                  })
                  .fail( function( er ) {
                        api.errorLog( 'ajax sek_get_all_saved_section => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Error when processing templates'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                        self.sectionCollectionPromise.resolve({});
                  });

                  return self.sectionCollectionPromise;
            }
      });//$.extend()
})( wp.customize, jQuery );
