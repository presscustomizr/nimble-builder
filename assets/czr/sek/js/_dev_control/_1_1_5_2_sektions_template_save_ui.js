//global sektionsLocalizedData
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // SAVE TMPL DIALOG BLOCK
            // fired in ::initialize()
            setupSaveTmplUI : function() {
                  var self = this;

                  // Declare api values and schedule reactions
                  self.tmplDialogVisible = new api.Value( false );// Hidden by default

                  if ( !sektionsLocalizedData.isTemplateSaveEnabled ) {
                     return;
                  }
                  self.tmplDialogVisible.bind( function( visible ){
                        if ( visible ) {
                              // close template gallery
                              // close level tree
                              self.templateGalleryExpanded(false);
                              self.levelTreeExpanded(false);
                              if ( self.saveSectionDialogVisible ) {
                                    self.saveSectionDialogVisible(false);
                              }
                        }
                        self.toggleSaveTmplUI(visible);
                  });

                  // Will store the collection of saved templates
                  self.allSavedTemplates = new api.Value('_not_populated_');
                  // When the collection is refreshed
                  // - populate select options
                  // - set the select value to default 'none'
                  self.allSavedTemplates.bind( function( tmpl_collection ) {
                        if ( !_.isObject(tmpl_collection) ) {
                              api.errare('error setupSaveTmplUI => tmpl collection should be an object');
                              return;
                        }
                        tmpl_collection = _.isEmpty(tmpl_collection) ? {} : tmpl_collection;
                        self.refreshTmplPickerHtml( tmpl_collection );
                  });

                  // Will store the collection of saved templates
                  self.allApiTemplates = new api.Value('_not_populated_');

                  self.tmplDialogMode = new api.Value('hidden');// 'save' default mode is set when dialog html is rendered
                  self.tmplDialogMode.bind( function(mode){
                        if ( !_.contains(['hidden', 'save', 'update', 'remove', 'edit' ], mode ) ) {
                              api.errare('::setupSaveTmplUI => unknown tmpl dialog mode', mode );
                              mode = 'save';
                        }

                        // Set the button pressed state
                        var $tmplDialogWrapper = $('#nimble-top-tmpl-save-ui'),
                            $titleInput = $tmplDialogWrapper.find('#sek-saved-tmpl-title'),
                            $descInput = $tmplDialogWrapper.find('#sek-saved-tmpl-description');

                        $tmplDialogWrapper.find('[data-tmpl-mode-switcher]').attr('aria-pressed', false );
                        $tmplDialogWrapper.find('[data-tmpl-mode-switcher="' + mode +'"]').attr('aria-pressed', true );

                        // update the current mode
                        $('#nimble-top-tmpl-save-ui').attr('data-sek-tmpl-dialog-mode', mode );

                        // make sure the remove dialog is hidden
                        $tmplDialogWrapper.removeClass('sek-removal-confirmation-opened');
                        var $selectEl;
                        // execute actions depending on the selected mode
                        switch( mode ) {
                              case 'save' :
                                    // When selecting 'save', make sure the title and description input are cleaned
                                    $titleInput.val('');
                                    $descInput.val('');
                              break;
                              case 'update' :
                              case 'remove' :
                                    $selectEl = $tmplDialogWrapper.find('.sek-saved-tmpl-picker');
                                    // Make sure the select value is always reset when switching mode
                                    $selectEl.val('none').trigger('change');

                                    self.setSavedTmplCollection().done( function( tmpl_collection ) {
                                          // refresh tmpl picker in case the user updated without changing anything
                                          self.refreshTmplPickerHtml();
                                          $selectEl.val( self.tmplToRemove || 'none' ).trigger('change');
                                          self.tmplToRemove = null;
                                    });
                              break;
                              case 'edit' :
                                    $selectEl = $tmplDialogWrapper.find('.sek-saved-tmpl-picker');
                                    // Make sure the select value is always reset when switching mode
                                    $selectEl.val('none').trigger('change');

                                    self.setSavedTmplCollection().done( function( tmpl_collection ) {
                                          // refresh tmpl picker in case the user updated without changing anything
                                          self.refreshTmplPickerHtml();
                                          $selectEl.val( self.tmplToEdit || 'none' ).trigger('change');
                                          self.tmplToEdit = null;
                                    });
                              break;
                        }//switch
                  });//self.tmplDialogMode.bind()

            },



            ///////////////////////////////////////////////
            ///// RENDER DIALOG BOX AND SCHEDULE CLICK ACTIONS
            refreshTmplPickerHtml : function( tmpl_collection ) {
                  tmpl_collection = tmpl_collection || this.allSavedTemplates();

                  var $tmplDialogWrapper = $('#nimble-top-tmpl-save-ui'),
                      $selectEl = $tmplDialogWrapper.find('.sek-saved-tmpl-picker');
                  // Make sure the select value is always reset when switching mode
                  $selectEl.val('none').trigger('change');

                  // empty all options but the default 'none' one
                  $selectEl.find('option').each( function() {
                        if ( 'none' !== $(this).attr('value') ) {
                              $(this).remove();
                        }
                  });

                  // Make sure we don't populate the collection twice ( if user clicks two times fast )
                  // if ( $tmplDialogWrapper.hasClass('tmpl-collection-populated') )
                  //   return;

                  var _default_title = 'template title not set',
                      _title,
                      _last_modified_date,
                      _html = '';
                  _.each( tmpl_collection, function( _tmpl_data, _tmpl_post_name ) {
                        if ( !_.isObject(_tmpl_data) )
                          return;
                        _last_modified_date = _tmpl_data.last_modified_date ? _tmpl_data.last_modified_date : '';
                        _title = _tmpl_data.title ? _tmpl_data.title : _default_title;
                        _html +='<option value="' + _tmpl_post_name + '">' + [ _title, sektionsLocalizedData.i18n['Last modified'] + ' : ' + _last_modified_date ].join(' | ') + '</option>';
                  });

                  $selectEl.append(_html);

                  // flag so we know it's done
                  // => controls the CSS visibility of the select element
                  $tmplDialogWrapper.addClass('tmpl-collection-populated');
            },


            //@param = { }
            renderTmplUI : function( params ) {
                  if ( $('#nimble-top-tmpl-save-ui').length > 0 )
                    return $('#nimble-top-tmpl-save-ui');

                  var self = this;

                  try {
                        _tmpl =  wp.template( 'nimble-top-tmpl-save-ui' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing nimble-top-tmpl-save-ui template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );
                  return $('#nimble-top-tmpl-save-ui');
            },



            ///////////////////////////////////////////////
            ///// DOM EVENTS
            // Fired once, on first rendering
            scheduleTmplSaveDOMEvents : function() {
                  var self = this, $tmplDialogWrapper = $('#nimble-top-tmpl-save-ui');
                  if ( $tmplDialogWrapper.data('nimble-tmpl-dom-events-scheduled') )
                    return;

                  // ATTACH DOM EVENTS
                  // Dialog Mode Switcher
                  $tmplDialogWrapper.on( 'click', '[data-tmpl-mode-switcher]', function(evt) {
                        evt.preventDefault();
                        self.tmplDialogMode($(this).data('tmpl-mode-switcher'));
                  });

                  // React to template select
                  // update title and description fields on template selection
                  $tmplDialogWrapper.on( 'change', '.sek-saved-tmpl-picker', function(evt){ self.reactOnTemplateSelection(evt, $(this) ); });

                  // SAVE
                  $tmplDialogWrapper.on( 'click', '.sek-do-save-tmpl', function(evt){
                        $tmplDialogWrapper.addClass('nimble-tmpl-processing-ajax');
                        self.saveOrUpdateTemplate(evt).done( function( response ) {
                              $tmplDialogWrapper.removeClass('nimble-tmpl-processing-ajax');
                              if ( response.success ) {
                                    self.tmplDialogVisible( false );
                                    self.setSavedTmplCollection( { refresh : true } );// <= true for refresh
                              }
                        });
                  });

                  // UPDATE
                  $tmplDialogWrapper.on( 'click', '.sek-do-update-tmpl', function(evt){
                        var $selectEl = $tmplDialogWrapper.find('.sek-saved-tmpl-picker'),
                            tmplPostNameCandidateForUpdate = $selectEl.val();
                        // make sure we don't try to remove the default option
                        if ( 'none' === tmplPostNameCandidateForUpdate || _.isEmpty(tmplPostNameCandidateForUpdate) )
                          return;

                        $tmplDialogWrapper.addClass('nimble-tmpl-processing-ajax');
                        self.saveOrUpdateTemplate(evt, tmplPostNameCandidateForUpdate).done( function(response) {
                              $tmplDialogWrapper.removeClass('nimble-tmpl-processing-ajax');
                              if ( response.success ) {
                                    self.tmplDialogVisible( false );
                                    self.setSavedTmplCollection( { refresh : true } )// <= true for refresh
                                          .done( function( tmpl_collection ) {
                                                // refresh tmpl picker in case the user updated without changing anything
                                                self.refreshTmplPickerHtml();
                                          });
                              }
                        });
                  });

                  // REMOVE
                  // Reveal remove dialog
                  $tmplDialogWrapper.on( 'click', '.sek-open-remove-confirmation', function(evt){
                        $tmplDialogWrapper.addClass('sek-removal-confirmation-opened');
                  });

                  // Do Remove
                  $tmplDialogWrapper.on( 'click', '.sek-do-remove-tmpl', function(evt){
                        var $selectEl = $tmplDialogWrapper.find('.sek-saved-tmpl-picker'),
                            tmplPostNameCandidateForRemoval = $selectEl.val();
                        // make sure we don't try to remove the default option
                        if ( 'none' === tmplPostNameCandidateForRemoval || _.isEmpty(tmplPostNameCandidateForRemoval) )
                          return;

                        $tmplDialogWrapper.addClass('nimble-tmpl-processing-ajax');
                        self.removeTemplate(evt, tmplPostNameCandidateForRemoval).done( function(response) {
                              $tmplDialogWrapper.removeClass('nimble-tmpl-processing-ajax');
                              $tmplDialogWrapper.removeClass('sek-removal-confirmation-opened');
                              if ( response.success ) {
                                    self.setSavedTmplCollection( { refresh : true } );// <= true for refresh
                              }
                        });
                  });

                  // Cancel Remove
                  $tmplDialogWrapper.on( 'click', '.sek-cancel-remove-tmpl', function(evt){
                        $tmplDialogWrapper.removeClass('sek-removal-confirmation-opened');
                  });


                  // Switch to update mode
                  //$tmplDialogWrapper.on( 'click', '[data-tmpl-mode-switcher="update"]', function(evt){  });

                  $('.sek-close-dialog', $tmplDialogWrapper ).on( 'click', function(evt) {
                        evt.preventDefault();
                        self.tmplDialogVisible(false);
                  });

                  // Say we're done with DOM event scheduling
                  $tmplDialogWrapper.data('nimble-tmpl-dom-events-scheduled', true );
            },



            // Is used in update and remove modes
            reactOnTemplateSelection : function(evt, $selectEl ){
                  var self = this,
                      $tmplDialogWrapper = $('#nimble-top-tmpl-save-ui'),
                      _tmplPostName = $selectEl.val(),
                      $titleInput = $tmplDialogWrapper.find('#sek-saved-tmpl-title'),
                      $descInput = $tmplDialogWrapper.find('#sek-saved-tmpl-description'),
                      // The informative class control the visibility of the title and the description in CSS
                      _informativeClass = 'update' === self.tmplDialogMode() ? 'sek-tmpl-update-selected' : 'sek-tmpl-remove-selected';

                  if ( 'none' === _tmplPostName ) {
                        $titleInput.val('');
                        $descInput.val('');
                        $tmplDialogWrapper.removeClass(_informativeClass);
                  } else {
                        var _allSavedTemplates = self.allSavedTemplates();
                        var _selectedTmpl = _tmplPostName;

                        // normalize
                        _allSavedTemplates = ( _.isObject(_allSavedTemplates) && !_.isArray(_allSavedTemplates) ) ? _allSavedTemplates : {};
                        _allSavedTemplates[_tmplPostName] = $.extend( {
                            title : '',
                            description : '',
                            last_modified_date : ''
                        }, _allSavedTemplates[_tmplPostName] || {} );

                        $titleInput.val( _allSavedTemplates[_tmplPostName].title );
                        $descInput.val( _allSavedTemplates[_tmplPostName].description );
                        $tmplDialogWrapper.addClass(_informativeClass);
                  }
            },




            ///////////////////////////////////////////////
            ///// AJAX ACTIONS
            // Fired on 'click' on .sek-do-save-tmpl btn
            saveOrUpdateTemplate : function(evt, tmplPostNameCandidateForUpdate ) {
                  var self = this, _dfd_ = $.Deferred();
                  evt.preventDefault();
                  var $_title = $('#sek-saved-tmpl-title'),
                      tmpl_title = $_title.val(),
                      tmpl_description = $('#sek-saved-tmpl-description').val(),
                      collectionSettingId = self.localSectionsSettingId(),
                      currentLocalSettingValue;

                  // Dec 2020 : remove locations not active on the page or empty before saving the tmpl
                  try { currentLocalSettingValue = self.preProcessTmpl( api( collectionSettingId )() ); } catch( er ) {
                      api.errorLog( 'error in ::saveOrUpdateTemplate', er );
                      _dfd_.resolve( {success:false});
                  }

                  if ( _.isEmpty( tmpl_title ) ) {
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

                  $('#sek-saved-tmpl-title').removeClass('error');

                  wp.ajax.post( 'sek_save_user_template', {
                        nonce: api.settings.nonce.save,
                        tmpl_data: 'edit' === self.tmplDialogMode() ? '' : JSON.stringify( currentLocalSettingValue ),
                        // the following will be saved in 'metas'
                        tmpl_title: tmpl_title,
                        tmpl_description: tmpl_description,
                        tmpl_post_name: tmplPostNameCandidateForUpdate || '',// <= provided when updating a template
                        edit_metas_only: 'edit' === self.tmplDialogMode() ? 'yes' : 'no',//<= in this case we only update title and description. Not the template content
                        skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                        tmpl_locations : self.getActiveLocationsForTmpl( currentLocalSettingValue ),
                        tmpl_header_location : self.getHeaderOrFooterLocationIdForTmpl( 'header', currentLocalSettingValue ),
                        tmpl_footer_location : self.getHeaderOrFooterLocationIdForTmpl( 'footer', currentLocalSettingValue )
                  })
                  .done( function( response ) {
                        //console.log('SAVED POST ID', response );
                        _dfd_.resolve( {success:true});
                        // response is {tmpl_post_id: 436}
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
                        api.errorLog( 'ajax sek_save_template => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Error when processing template'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });
                  return _dfd_;
            },//saveOrUpdateTemplate

            // return an array of the location used for the template, to be used as template meta for a future injection
            // we don't need to list all inactive locations of the current page in the template meta
            getActiveLocationsForTmpl : function( tmpl_data ) {
                  if ( !_.isObject( tmpl_data ) ) {
                      throw new Error('preProcess Tmpl => error : tmpl_data must be an object');
                  }
                  var _tmpl_locations = [];
                  _.each( tmpl_data.collection, function( _loc_params ) {
                        if ( !_.isObject( _loc_params ) || !_loc_params.id || !_loc_params.level )
                          return;

                        if ( 'location' === _loc_params.level ) {
                            _tmpl_locations.push( _loc_params.id );
                        }
                  });
                  return _tmpl_locations;
            },

            getHeaderOrFooterLocationIdForTmpl : function( headerOrFooter, tmpl_data) {
                  var self = this;
                  if ( !_.isObject( tmpl_data ) ) {
                      throw new Error('preProcess Tmpl => error : tmpl_data must be an object');
                  }
                  var _loc = '';
                  _.each( tmpl_data.collection, function( _loc_params ) {
                        if ( !_.isObject( _loc_params ) || !_loc_params.id || !_loc_params.level )
                          return;
                        if ( ( 'header' === headerOrFooter && self.isHeaderLocation( _loc_params.id ) ) || ( 'footer' === headerOrFooter && self.isFooterLocation( _loc_params.id ) ) ) {
                              _loc = _loc_params.id;
                        }
                  });
                  return _loc;
            },

            // @return a tmpl model
            // Note : ids are reset server side
            // Example of tmpl model before preprocessing
            // {
            //    collection: [{…}]
            //    id: "" //<= to remove
            //    level: "tmpl" // <= to remove
            //    options: {bg: {…}}
            //    ver_ini: "1.1.8"
            // }
            // Dec 2020 : remove locations not active on the page or empty before saving the tmpl
            preProcessTmpl : function( tmpl_data ) {
                  var self = this, tmpl_processed, activeLocations;
                  if ( !_.isObject( tmpl_data ) ) {
                      throw new Error('preProcess Tmpl => error : tmpl_data must be an object');
                  }
                  tmpl_processed = $.extend( true, {}, tmpl_data );
                  tmpl_processed.collection = [];
                  activeLocations = self.activeLocations();

                  //console.log('TO DO => make sure template is ok to be saved', tmpl_data, self.activeLocations() );
                  _.each( tmpl_data.collection, function( _loc_params ) {
                        if ( !_.isObject( _loc_params ) || !_loc_params.id || !_loc_params.collection )
                          return;

                        if ( _.contains( activeLocations, _loc_params.id ) && !_.isEmpty(_loc_params.collection) ) {
                            tmpl_processed.collection.push( _loc_params );
                        }
                  });
                  return tmpl_processed;
            },

            // Fired on 'click on .sek-do-remove-tmpl btn
            removeTemplate : function(evt, tmplPostNameCandidateForRemoval ) {
                  var self = this, _dfd_ = $.Deferred();
                  evt.preventDefault();
                  wp.ajax.post( 'sek_remove_user_template', {
                        nonce: api.settings.nonce.save,
                        tmpl_post_name: tmplPostNameCandidateForRemoval
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( response ) {
                        _dfd_.resolve( {success:true});
                        // response is {tmpl_post_id: 436}
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
                        api.errorLog( 'ajax sek_remove_template => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Error when processing templates'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });

                  return _dfd_;
            },




            ///////////////////////////////////////////////
            ///// REVEAL / HIDE DIALOG BOX
            /// react on self.tmplDialogVisible.bind(...)
            // @return void()
            // self.tmplDialogVisible.bind( function( visible ){
            //       self.toggleSaveTmplUI( visible );
            // });
            toggleSaveTmplUI : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderTmplUI({}) ).done( function( $_el ) {
                                  self.scheduleTmplSaveDOMEvents();
                                  self.saveUIContainer = $_el;
                                  //display
                                  _.delay( function() {
                                        // set dialog mode now so we display the relevant fields on init
                                        self.tmplDialogMode('save');// Default mode is save
                                        self.cachedElements.$body.addClass('sek-save-tmpl-ui-visible');
                                  }, 200 );
                                  // set tmpl id input value
                                  //$('#sek-saved-tmpl-id').val( tmplId );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            self.cachedElements.$body.removeClass('sek-save-tmpl-ui-visible');
                            if ( $( '#nimble-top-tmpl-save-ui' ).length > 0 ) {
                                  //remove Dom element after slide up
                                  _.delay( function() {
                                        // set dialog mode back to 'hidden' mode
                                        self.tmplDialogMode = self.tmplDialogMode ? self.tmplDialogMode : new api.Value();
                                        self.tmplDialogMode('hidden');
                                        self.saveUIContainer.remove();
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
                              self.tmplDialogVisible( false );//should be already false
                        });
                  }
            },




            ///////////////////////////////////////////////
            ///// TMPL COLLECTION
            // @return $.promise
            setSavedTmplCollection : function( params ) {
                  var self = this, _dfd_ = $.Deferred();

                  // refresh is true on save, update, remove success
                  params = params || {refresh : false};

                  // If the collection is already set, return it.
                  // unless this is a "refresh" case
                  if ( !params.refresh && '_not_populated_' !== self.allSavedTemplates() ) {
                        return _dfd_.resolve( self.allSavedTemplates() );
                  }

                  var _promise;
                  // Prevent a double request while ajax request is being processed
                  if ( self.templateCollectionPromise && 'pending' === self.templateCollectionPromise.state() ) {
                        _promise = self.templateCollectionPromise;
                  } else {
                        _promise = self.getSavedTmplCollection();
                  }
                  _promise.done( function( tmpl_collection ) {
                        self.allSavedTemplates( tmpl_collection );
                        _dfd_.resolve( tmpl_collection );
                  });
                  return _dfd_.promise();
            },

            // @return a promise
            getSavedTmplCollection : function() {
                  var self = this;
                  self.templateCollectionPromise = $.Deferred();

                  wp.ajax.post( 'sek_get_all_saved_tmpl', {
                        nonce: api.settings.nonce.save
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( tmpl_collection ) {
                        if ( _.isObject(tmpl_collection) && !_.isArray( tmpl_collection ) ) {
                              self.templateCollectionPromise.resolve( tmpl_collection );
                              //console.log('GET SAVED TMPL COLLECTION', tmpl_collection );
                        } else {
                              self.templateCollectionPromise.resolve( {} );
                              if ( !_.isEmpty( tmpl_collection ) ) {
                                    api.errare('control::getSavedTmplCollection => error => tmpl collection is invalid', tmpl_collection);
                              }
                        }
                  })
                  .fail( function( er ) {
                        api.errorLog( 'ajax sek_get_all_saved_tmpl => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>' + sektionsLocalizedData.i18n['Error when processing templates'] + '</strong>',
                                  '</span>'
                            ].join('')
                        });
                        self.templateCollectionPromise.resolve({});
                  });

                  return self.templateCollectionPromise;
            },



            // API TMPL COLLECTION
            // @return a promise
            getApiTmplCollection : function() {
                  var self = this,
                        dfd = $.Deferred(),
                        _collection = {};
                  if ( '_not_populated_' !== self.allApiTemplates() ) {
                        dfd.resolve( self.allApiTemplates() );
                  } else {
                        if ( !sektionsLocalizedData.useAPItemplates ) {
                              self.allApiTemplates([]);
                              dfd.resolve([]);
                        } else {
                              wp.ajax.post( 'sek_get_all_api_tmpl', {
                                    nonce: api.settings.nonce.save
                                    //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                              })
                              .done( function( tmpl_collection ) {
                                    if ( _.isObject(tmpl_collection) && !_.isArray( tmpl_collection ) ) {
                                          _collection = tmpl_collection;
                                          //console.log('AJAX GET API TMPL COLLECTION DONE', tmpl_collection );
                                    } else {
                                          api.errare('control::getApiTmplCollection => error => tmpl collection is invalid', tmpl_collection);
                                    }
                                    self.allApiTemplates( _collection );
                                    dfd.resolve( _collection );
                              })
                              .fail( function( er ) {
                                    api.errorLog( 'ajax sek_get_all_api_tmpl => error', er );
                                    api.previewer.trigger('sek-notify', {
                                    type : 'error',
                                    duration : 10000,
                                    message : [
                                          '<span style="font-size:0.95em">',
                                                '<strong>' + sektionsLocalizedData.i18n['Error when processing templates'] + '</strong>',
                                          '</span>'
                                    ].join('')
                                    });
                                    dfd.resolve({});
                              });
                        }
                  }
                  return dfd;
            },
      });//$.extend()
})( wp.customize, jQuery );
