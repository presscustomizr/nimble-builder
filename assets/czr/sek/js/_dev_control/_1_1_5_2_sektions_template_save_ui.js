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
                  self.tmplDialogVisible.bind( function( to ){
                        self.toggleSaveTmplUI(to);
                  });

                  // Will store the collection of saved templates
                  self.allSavedTemplates = new api.Value('_not_populated_');


                  self.tmplDialogMode = new api.Value('hidden');// 'save' default mode is set when dialog html is rendered
                  self.tmplDialogMode.bind( function(mode){
                        console.log('TMPL DIALOG MODE ?', mode );
                        if ( !_.contains(['hidden', 'save', 'update', 'remove' ], mode ) ) {
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

                        // execute actions depending on the selected mode
                        switch( mode ) {
                              case 'save' :
                                    // When selecting 'save', make sure the title and description input are cleaned
                                    $titleInput.val('');
                                    $descInput.val('');
                              break;
                              case 'update' :
                              case 'remove' :
                                    var $selectEl = $tmplDialogWrapper.find('.sek-saved-tmpl-picker');
                                        // Make sure the select value is always reset when switching mode
                                        $selectEl.val('none').trigger('change');

                                    self.getAllSavedTemplate().done( function( template_collection ) {
                                          if ( _.isObject(template_collection) && !_.isEmpty(template_collection) ) {
                                                // update the saved value
                                                self.allSavedTemplates( template_collection );

                                                // Make sure we don't populate the collection twice ( if user clicks two times fast )
                                                if ( $tmplDialogWrapper.hasClass('tmpl-collection-populated') )
                                                  return;

                                                var _default_title = 'template title not set',
                                                    _title,
                                                    _html = '';
                                                _.each( template_collection, function( _tmpl_data, _tmpl_post_name ) {
                                                      if ( !_.isObject(_tmpl_data) )
                                                        return;

                                                      _title = _tmpl_data.title ? _tmpl_data.title : _default_title;
                                                      _html +='<option value="' + _tmpl_post_name + '">' + _title + '</option>';
                                                });
                                                console.log('_html ??', _html );
                                                $selectEl.append(_html);

                                                // flag so we know it's done
                                                $tmplDialogWrapper.addClass('tmpl-collection-populated');
                                          }
                                    });
                              break;
                        }//switch
                  });

            },



            ///////////////////////////////////////////////
            ///// RENDER DIALOG BOX AND SCHEDULE CLICK ACTIONS
            //@param = { }
            renderAndsetupSaveTmplUITmpl : function( params ) {
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

                  var $tmplDialogWrapper = $('#nimble-top-tmpl-save-ui');

                  // ATTACH DOM EVENTS
                  // Dialog Mode Switcher
                  $tmplDialogWrapper.on( 'click', '[data-tmpl-mode-switcher]', function(evt) {
                        evt.preventDefault();
                        self.tmplDialogMode($(this).data('tmpl-mode-switcher'));
                  });

                  // React to template select
                  // update title and description fields on template selection
                  $tmplDialogWrapper.on( 'change', '.sek-saved-tmpl-picker', function(evt){ self.reactOnTemplateSelection(evt, $(this) ); });

                  // Save
                  $tmplDialogWrapper.on( 'click', '.sek-do-save-tmpl', function(evt){ self.saveOrUpdateTemplate(evt); });

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

                        self.removeTemplate(evt, tmplPostNameCandidateForRemoval).done( function(response) {
                              $tmplDialogWrapper.removeClass('sek-removal-confirmation-opened');

                              // reset the select value
                              $selectEl.val('none').trigger('change');

                              //$tmplDialogWrapper.find('.sek-open-remove-confirmation').show('fast');
                              if ( response.success ) {
                                    // update the template collection
                                    var oldTmplCollection = self.allSavedTemplates(),
                                        newTmplCollection = {};

                                    console.log('oldTemplateCollection', oldTmplCollection );
                                    console.log('tmplPostNameCandidateForRemoval', tmplPostNameCandidateForRemoval );

                                    // populate new tmpl collection
                                    _.each( oldTmplCollection, function( _data, _key ) {
                                        if ( tmplPostNameCandidateForRemoval !== _key ) {
                                            newTmplCollection[_key] = _data;
                                        }
                                    });
                                    console.log('newTmplCollection', newTmplCollection );
                                    self.allSavedTemplates( newTmplCollection );



                                    // remove the select option ( if not the default one 'none')
                                    if ( 'none' !== tmplPostNameCandidateForRemoval ) {
                                          $selectEl.find('[value="' + tmplPostNameCandidateForRemoval +'"]').remove();
                                    }
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

                  return $tmplDialogWrapper;
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
                        console.log('ALORS SERVER RESP FOR REMOVED TEMPLATE ?', response );
                        _dfd_.resolve( {success:true});
                        // response is {tmpl_post_id: 436}
                        //self.tmplDialogVisible( false );
                        api.previewer.trigger('sek-notify', {
                            type : 'success',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>@missi18n Your template has been removed.</strong>',
                                  '</span>'
                            ].join('')
                        });
                  })
                  .fail( function( er ) {
                        console.log('ER ??', er );
                        _dfd_.resolve( {success:false});
                        api.errorLog( 'ajax sek_remove_template => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>@missi18n error when removing template</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });

                  return _dfd_;
            },

            // Fired on 'click' on .sek-do-save-tmpl btn
            saveOrUpdateTemplate : function(evt) {
                  var self = this;
                  evt.preventDefault();
                  var $_title = $('#sek-saved-tmpl-title'),
                      tmpl_title = $_title.val(),
                      tmpl_description = $('#sek-saved-tmpl-description').val(),
                      collectionSettingId = self.localSectionsSettingId(),
                      currentLocalSettingValue = self.preProcessTmpl( api( collectionSettingId )() );

                  if ( _.isEmpty( tmpl_title ) ) {
                      $_title.addClass('error');
                      api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>@missi18n You need to set a title</strong>',
                                  '</span>'
                            ].join('')

                      });
                      return;
                  }

                  $('#sek-saved-tmpl-title').removeClass('error');

                  wp.ajax.post( 'sek_save_user_template', {
                        nonce: api.settings.nonce.save,
                        tmpl_title: tmpl_title,
                        tmpl_description: tmpl_description,
                        tmpl_data: JSON.stringify( currentLocalSettingValue ),
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( response ) {
                        console.log('ALORS SERVER RESP FOR SAVED TEMPLATE ?', response );
                        // response is {tmpl_post_id: 436}
                        //self.tmplDialogVisible( false );
                        api.previewer.trigger('sek-notify', {
                            type : 'success',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>@missi18n Your template has been saved.</strong>',
                                  '</span>'
                            ].join('')
                        });
                  })
                  .fail( function( er ) {
                        console.log('ER ??', er );
                        api.errorLog( 'ajax sek_save_template => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>@missi18n error when saving template</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });
            },//saveOrUpdateTemplate




            // Is used in update and remove modes
            reactOnTemplateSelection : function(evt, $selectEl ){

                  //console.log('REACT ON TEMPLATE UPDATE SELECT', $selectEl, $selectEl.val() );
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
                        _allSavedTemplates = _.isObject(_allSavedTemplates) ? _allSavedTemplates : {};
                        _allSavedTemplates[_tmplPostName] = $.extend( {
                            title : '',
                            description : ''
                        }, _allSavedTemplates[_tmplPostName] || {} );

                        console.log('SOOO? _tmplPostName', _tmplPostName, _allSavedTemplates );
                        $titleInput.val( _allSavedTemplates[_tmplPostName].title );
                        $descInput.val( _allSavedTemplates[_tmplPostName].description );
                        $tmplDialogWrapper.addClass(_informativeClass);

                        console.log("$titleInput.closest('div')??", $titleInput.closest('div') );
                  }
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
                  //console.log('visible dialog?', visible );
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndsetupSaveTmplUITmpl({}) ).done( function( $_el ) {
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
            ///// AJAX ACTIONS
            // @return $.promise
            getAllSavedTemplate : function() {
                  var self = this,
                      $tmplDialogWrapper = $('#nimble-top-tmpl-save-ui'),
                      $selectEl = $tmplDialogWrapper.find('.sek-saved-tmpl-picker'),
                      templateCollection = self.allSavedTemplates();

                  // Make sure we don't fetch the collection twice
                  if ( $tmplDialogWrapper.hasClass('tmpl-collection-populated') )
                    return $.Deferred( function() { this.resolve( self.allSavedTemplates() );} );

                  // Prevent a double request while ajax request is being processed
                  if ( self.templateCollectionPromise && 'pending' === self.templateCollectionPromise.state() )
                    return self.templateCollectionPromise;

                  self.templateCollectionPromise = wp.ajax.post( 'sek_get_all_saved_tmpl', {
                        nonce: api.settings.nonce.save
                        //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                  })
                  .done( function( response ) {
                        console.log('SERVER RESP FOR GET ALL SAVED TEMPLATE ?', response );
                        console.log('typeof response', typeof response );
                        // response is {tmpl_post_id: 436}
                        //self.tmplDialogVisible( false );
                        // api.previewer.trigger('sek-notify', {
                        //     type : 'success',
                        //     duration : 10000,
                        //     message : [
                        //           '<span style="font-size:0.95em">',
                        //             '<strong>@missi18n Your template has been saved.</strong>',
                        //           '</span>'
                        //     ].join('')
                        // });
                  })
                  .fail( function( er ) {
                        console.log('ER ??', er );
                        api.errorLog( 'ajax sek_get_all_saved_tmpl => error', er );
                        api.previewer.trigger('sek-notify', {
                            type : 'error',
                            duration : 10000,
                            message : [
                                  '<span style="font-size:0.95em">',
                                    '<strong>@missi18n error when fetching the saved templates</strong>',
                                  '</span>'
                            ].join('')
                        });
                  });

                  return self.templateCollectionPromise;
            },

            // @return a tmpl model with clean ids
            // also removes the tmpl properties "id" and "level", which are dynamically set when dragging and dropping
            // Example of tmpl model before preprocessing
            // {
            //    collection: [{…}]
            //    id: "" //<= to remove
            //    level: "tmpl" // <= to remove
            //    options: {bg: {…}}
            //    ver_ini: "1.1.8"
            // }
            preProcessTmpl : function( tmpl_data ) {
                  console.log('TO DO => make sure template is ok to be saved');
                  return tmpl_data;
            },
      });//$.extend()
})( wp.customize, jQuery );
