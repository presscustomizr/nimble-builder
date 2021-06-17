//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            cachedElements : {
                $body : $('body'),
                $window : $(window)
            },

            initialize: function() {
                  var self = this;
                  if ( _.isUndefined( window.sektionsLocalizedData ) ) {
                        throw new Error( 'CZRSeksPrototype => missing localized server params sektionsLocalizedData' );
                  }
                  // this class is skope dependant
                  if ( ! _.isFunction( api.czr_activeSkopes ) ) {
                        throw new Error( 'CZRSeksPrototype => api.czr_activeSkopes' );
                  }
                  // SECTIONS ID FOR LOCAL AND GLOBAL OPTIONS
                  self.SECTION_ID_FOR_GLOBAL_OPTIONS = '__globalOptionsSectionId';
                  self.SECTION_ID_FOR_LOCAL_OPTIONS = '__localOptionsSection';

                  // SECTION ID FOR THE CONTENT PICKER
                  self.SECTION_ID_FOR_CONTENT_PICKER = '__content_picker__';

                  // Max possible number of columns in a section
                  self.MAX_NUMBER_OF_COLUMNS = 12;

                  // _.debounce param when updating the UI setting
                  // prevent hammering server + fixes https://github.com/presscustomizr/nimble-builder/issues/244
                  self.SETTING_UPDATE_BUFFER = 100;

                  // introduced for https://github.com/presscustomizr/nimble-builder/issues/403
                  self.TINYMCE_EDITOR_HEIGHT = 100;

                  // Define a default value for the sektion setting value, used when no server value has been sent
                  // @see php function
                  // function sek_get_default_location_model() {
                  //     $defaut_sektions_value = [ 'collection' => [], 'options' => [] ];
                  //     foreach( sek_get_locations() as $location ) {
                  //         $defaut_sektions_value['collection'][] = [
                  //             'id' => $location,
                  //             'level' => 'location',
                  //             'collection' => [],
                  //             'options' => []
                  //         ];
                  //     }
                  //     return $defaut_sektions_value;
                  // }
                  self.defaultLocalSektionSettingValue = self.getDefaultSektionSettingValue( 'local' );

                  // Store the contextual setting prefix
                  self.localSectionsSettingId = new api.Value( {} );

                  // Keep track of the registered ui elements dynamically registered
                  // this collection is populated in ::register(), if the track param is true
                  // this is used to know what ui elements are currently being displayed
                  self.registered = new api.Value([]);

                  // June 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/708
                  if ( wp.customize.apiIsReady ) {
                        self.doSektionThinksOnApiReady();
                  } else {
                        api.bind( 'ready', function() {
                              self.doSektionThinksOnApiReady();
                        });
                  }


                  // Add the skope id on save
                  // Uses a WP core hook to filter the query on a customize_save action
                  //
                  // This posted skope id is useful when we need to know the skope id during ajax.
                  // ( Note that with the nimble ajax action, the skope_id is always posted. Not in WP core ajax actions. )
                  // Example of use of $_POST['local_skope_id'] => @see sek_get_parent_level_model()
                  // Helps fixing : https://github.com/presscustomizr/nimble-builder/issues/242, for which sek_add_css_rules_for_spacing() couldn't be set for columns margins
                  api.bind( 'save-request-params', function( query ) {
                        $.extend( query, {
                              local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                              group_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id', 'group' ),//<= feb 2021, added for #478
                              active_locations : api.czr_sektions.activeLocations(),
                              inherit_group_template : true
                        });
                  });


                  // TINY MCE EDITOR
                  var clearActiveWPEditorsInstances = function() {
                        if ( _.isArray( api.czrActiveWPEditors ) ) {
                              _.each( api.czrActiveWPEditors, function( _id ) {
                                    wp.oldEditor.remove( _id );
                              });
                              api.czrActiveWPEditors = [];
                        }
                  };
                  // added for https://github.com/presscustomizr/nimble-builder/issues/403
                  // in fmk::setupTinyMceEditor => each id of newly instantiated editor is added to the [] api.czrActiveWPEditors
                  // We need to remove those instances when cleaning registered controls
                  api.bind( 'sek-before-clean-registered', clearActiveWPEditorsInstances );

                  // When using the text editor in the items of in a multi-item module
                  // We need to clear the editor instances each time all items are closed, before opening a new one
                  // 'czr-all-items-closed' is fired in CZRModuleMths.closeAllItems()
                  api.bind('czr-all-items-closed', clearActiveWPEditorsInstances );
            },// initialize()


            // @ API READY
            // Fired at api.bind( 'ready', function() {})
            doSektionThinksOnApiReady : function() {
                  var self = this;
                  // the main sektion panel
                  // the local and global options section
                  self.registerAndSetupDefaultPanelSectionOptions();

                  // Setup the collection settings => register the main settings for local and global skope and bind it
                  // schedule reaction to collection setting ids => the setup of the collection setting when the collection setting ids are set
                  //=> on skope change
                  //@see setContextualCollectionSettingIdWhenSkopeSet
                  //
                  // var _settingsToRegister_ = {
                  //       'local' : { collectionSettingId : self.localSectionsSettingId() },//<= "nimble___[skp__post_page_10]"
                  //       'global' : { collectionSettingId : self.getGlobalSectionsSettingId() }//<= "nimble___[skp__global]"
                  // };
                  self.localSectionsSettingId.callbacks.add( function( collectionSettingIds, previousCollectionSettingIds ) {
                        // register the collection setting id
                        // and schedule the reaction to different collection changes : refreshModules, ...
                        try { self.setupSettingsToBeSaved(); } catch( er ) {
                              api.errare( 'Error in self.localSectionsSettingId.callbacks => self.setupSettingsToBeSaved()' , er );
                        }

                        // Now that the local and global settings are registered, initialize the history log
                        self.initializeHistoryLogWhenSettingsRegistered();

                        // On init and when skope changes, request the contextually active locations
                        // We should not need this call, because the preview sends it on initialize
                        // But this is safer.
                        // The preview send back the list of active locations 'sek-active-locations-in-preview'
                        // introduced for the level tree, https://github.com/presscustomizr/nimble-builder/issues/359
                        api.previewer.send('sek-request-active-locations');
                  });


                  // POPULATE THE MAIN SETTING ID NOW
                  // + GENERATE UI FOR THE LOCAL SKOPE OPTIONS
                  // + GENERATE UI FOR THE GLOBAL OPTIONS
                  // + GENERATE UI FOR SITE TEMPLATES
                  var doSkopeDependantActions = function( newSkopes, previousSkopes ) {
                        self.setContextualCollectionSettingIdWhenSkopeSet( newSkopes, previousSkopes );

                        // Generate UI for the local skope options
                        api.section( self.SECTION_ID_FOR_LOCAL_OPTIONS, function( _section_ ) {
                              _section_.deferred.embedded.done( function() {
                                    if( true === _section_.boundForLocalOptionGeneration )
                                      return;
                                     // Defer the UI generation when the section is expanded
                                    _section_.boundForLocalOptionGeneration = true;
                                    _section_.expanded.bind( function( expanded ) {
                                          if ( true === expanded ) {
                                                self.generateUI({ action : 'sek-generate-local-skope-options-ui'});
                                          }
                                    });
                              });
                        });
                        

                        // The UI of the global option must be generated only once.
                        // We don't want to re-generate on each skope change
                        // fixes https://github.com/presscustomizr/nimble-builder/issues/271
                        api.section( self.SECTION_ID_FOR_GLOBAL_OPTIONS, function( _section_ ) {
                              if ( true === _section_.nimbleGlobalOptionGenerated )
                                return;
                              self.generateUI({ action : 'sek-generate-global-options-ui'});
                              _section_.nimbleGlobalOptionGenerated = true;
                              // Make sure template gallery is closed when opening/closing global options panel
                              // see https://github.com/presscustomizr/nimble-builder/issues/840
                              _section_.expanded.bind( function() {
                                    if ( !self.templateGalleryExpanded )
                                          return;
                                    self.templateGalleryExpanded(false);
                              });
                        });
                        
                        ////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        // June 2020 property introduced for https://github.com/presscustomizr/nimble-builder-pro/issues/12
                        self.nb_is_ready = true;

                        // This event has been introduced when implementing https://github.com/presscustomizr/nimble-builder/issues/304
                        api.trigger('nimble-ready-for-current-skope');
                         ////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////
                  };//doSkopeDependantActions()

                  // populate the setting ids now if skopes are set
                  if ( !_.isEmpty( api.czr_activeSkopes().local ) ) {
                        doSkopeDependantActions();
                  }
                  // ON SKOPE READY
                  // - Set the contextual setting prefix
                  // - Generate UI for Nimble local skope options
                  // - Generate the content picker
                  api.czr_activeSkopes.callbacks.add( function( newSkopes, previousSkopes ) {
                        doSkopeDependantActions( newSkopes, previousSkopes );
                  });


                  // Communicate with the preview
                  self.reactToPreviewMsg();

                  // Setup Dnd
                  self.setupDnd();


                  // setup the tinyMce editor used for the tiny_mce_editor input
                  // => one object listened to by each tiny_mce_editor input
                  self.setupTinyMceEditor();

                  // print json
                  self.schedulePrintSectionJson();

                  // Always set the previewed device back to desktop on ui change
                  // event 'sek-ui-removed' id triggered when cleaning the registered ui controls
                  // @see ::cleanRegisteredAndLargeSelectInput()
                  // July 2020 commented to fix https://github.com/presscustomizr/nimble-builder/issues/728
                  // self.bind( 'sek-ui-removed', function() {
                  //       api.previewedDevice( 'desktop' );
                  // });

                  // Synchronize api.previewedDevice with the currently rendered ui
                  // ensure that the selected device tab of the spacing module is the one being previewed
                  // =>@see spacing module, in item constructor CZRSpacingItemMths
                  api.previewedDevice.bind( function( device ) {
                        var currentControls = _.filter( self.registered(), function( uiData ) {
                              return 'control' == uiData.what;
                        });
                        _.each( currentControls || [] , function( ctrlData ) {
                              api.control( ctrlData.id, function( _ctrl_ ) {
                                    _ctrl_.container.find('[data-sek-device="' + device + '"]').each( function() {
                                          $(this).trigger('click');
                                    });
                              });
                        });

                        // Send the previewed device to the preview
                        //api.previewer.send( 'sek-preview-device-changed', { device : device });
                  });

                  // Schedule a reset
                  $('#customize-notifications-area').on( 'click', '[data-sek-reset="true"]', function() {
                        api.previewer.trigger('sek-reset-collection', { scope : 'local' } );
                  });


                  // CLEAN UI BEFORE REMOVAL
                  // 'sek-ui-pre-removal' is triggered in ::cleanRegisteredAndLargeSelectInput
                  // @params { what : control, id : '' }
                  self.bind( 'sek-ui-pre-removal', function( params ) {
                        // CLEAN DRAG N DROP
                        if ( 'control' == params.what && -1 < params.id.indexOf( 'draggable') ) {
                              api.control( params.id, function( _ctrl_ ) {
                                    _ctrl_.container.find( '[draggable]' ).each( function() {
                                          $(this).off( 'dragstart dragend' );
                                    });
                              });
                        }

                        // CLEAN SELECT2
                        // => we need to destroy the czrSelect2 instance, otherwise it can stay open when switching to another ui.
                        if ( 'control' == params.what ) {
                              api.control( params.id, function( _ctrl_ ) {
                                    _ctrl_.container.find( 'select' ).each( function() {
                                          if ( ! _.isUndefined( $(this).data('czrSelect2') ) ) {
                                                $(this).czrSelect2('destroy');
                                          }
                                    });
                              });
                        }
                  });


                  // POPULATE THE REGISTERED COLLECTION
                  // 'czr-new-registered' is fired in api.CZR_Helpers.register()
                  api.bind( 'czr-new-registered', function( params ) {
                        //console.log( 'czr-new-registered => ', params );
                        // Check that we have an origin property and that make sure we populate only the registration emitted by 'nimble'
                        if ( _.isUndefined( params.origin ) ) {
                              throw new Error( 'czr-new-registered event => missing params.origin' );
                        }
                        if ( 'nimble' !== params.origin )
                          return;

                        // when no collection is provided, we use
                        if ( false !== params.track ) {
                              var currentlyRegistered = self.registered();
                              var newRegistered = $.extend( true, [], currentlyRegistered );
                              //Check for duplicates
                              var duplicateCandidate = _.findWhere( newRegistered, { id : params.id } );
                              if ( ! _.isEmpty( duplicateCandidate ) && _.isEqual( duplicateCandidate, params ) ) {
                                    throw new Error( 'register => duplicated element in self.registered() collection ' + params.id );
                              }
                              newRegistered.push( params );
                              self.registered( newRegistered );

                              // say it
                              //this.trigger( [params.what, params.id , 'registered' ].join('__'), params );
                        }
                  });


                  // store active locations
                  self.activeLocations = new api.Value([]);// <= introduced for the level tree, https://github.com/presscustomizr/nimble-builder/issues/359
                  self.activeLocationsInfo = new api.Value([]);// <= introduced for better move up/down of sections https://github.com/presscustomizr/nimble-builder/issues/521
                  api.previewer.bind('sek-active-locations-in-preview', function( activelocs ){
                        self.activeLocations( ( _.isObject(activelocs) && _.isArray( activelocs.active_locations ) ) ? activelocs.active_locations : [] );
                        self.activeLocationsInfo( ( _.isObject(activelocs) && _.isArray( activelocs.active_locs_info ) ) ? activelocs.active_locs_info : [] );
                        // December 2020 => refresh local setting when an active location is available locally but not present in the local setting
                        // Fixes the problem of importing template from the gallery, with locations different than the current local page
                        // update : December 24th => deactivated because of https://github.com/presscustomizr/nimble-builder/issues/770
                        // if ( !_.isEmpty( api.dirtyValues() ) ) {
                        //       try{ self.updateAPISetting({ action : 'sek-maybe-add-missing-locations'}); } catch(er) {
                        //             api.errare( '::initialize => error with sek-maybe-add-missing-locations', er );
                        //       }
                        // }
                  });


                  // TOP BAR
                  // Setup the topbar including do/undo action buttons
                  self.setupTopBar();//@see specific dev file

                  // SAVE SECTION UI
                  // June 2020 : for https://github.com/presscustomizr/nimble-builder/issues/520 and https://github.com/presscustomizr/nimble-builder/issues/713
                  self.setupSaveSectionUI();

                  // SAVE TEMPLATE UI
                  // April 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/655
                  self.setupSaveTmplUI();

                  // SETUP DOUBLE CLICK INSERTION THINGS
                  // Stores the preview target for double click insertion
                  // implemented for https://github.com/presscustomizr/nimble-builder/issues/317
                  self.lastClickedTargetInPreview = new api.Value();
                  self.lastClickedTargetInPreview.bind( function( to, from ) {
                        // to and from are formed this way : { id : "__nimble__fb2ab3e47472" }
                        // @see 'sek-pick-content' event in ::reactToPreviewMsg()

                        // Send the level id of the current double-click insertion target
                        // => this will be used to style the level id container with a pulse animation
                        if ( _.isObject( to ) && to.id ) {
                              api.previewer.send( 'sek-set-double-click-target', to );
                        } else {
                              // Tell the preview to clean the target highlight effect
                              api.previewer.send( 'sek-reset-double-click-target' );
                        }

                        // reset after a delay
                        clearTimeout( self.cachedElements.$window.data('_preview_target_timer_') );
                        self.cachedElements.$window.data('_preview_target_timer_', setTimeout(function() {
                              // Reset the click target
                              self.lastClickedTargetInPreview( {} );
                              // Tell the preview to clean the target highlight effect
                              api.previewer.send( 'sek-reset-double-click-target' );
                        }, 20000 ) );
                  });

                  // React to the preview to clean any currently highlighted drop zone
                  // This event is triggered on all click in the preview iframe
                  // @see preview::scheduleUiClickReactions()
                  api.previewer.bind( 'sek-clean-target-drop-zone', function() {
                        // Reset the click target
                        self.lastClickedTargetInPreview({});
                  });

                  // Clean the current target when hitting escape
                  $(document).keydown(function( evt ) {
                        // ESCAPE key pressed
                        if ( evt && 27 === evt.keyCode ) {
                            self.lastClickedTargetInPreview({});
                        }
                  });

                  // PRINT A WARNING NOTICE FOR USERS OF CACHE PLUGIN
                  if ( sektionsLocalizedData.hasActiveCachePlugin ) {
                        _.delay( function() {
                            api.previewer.trigger('sek-notify', {
                                  notif_id : 'has-active-cache-plugin',
                                  type : 'info',
                                  duration : 20000,
                                  message : [
                                        '<span style="color:#0075a2">',
                                          sektionsLocalizedData.i18n['You seem to be using a cache plugin.'],
                                          ( ! _.isString( sektionsLocalizedData.hasActiveCachePlugin ) || sektionsLocalizedData.hasActiveCachePlugin.length < 2 ) ? '' : '<strong> (' + sektionsLocalizedData.hasActiveCachePlugin + ')</strong><br/>',
                                          ' <strong>',
                                          sektionsLocalizedData.i18n['It is recommended to disable your cache plugin when customizing your website.'],
                                          '</strong>',
                                        '</span>'
                                  ].join('')
                            });
                        }, 2000 );//delay()
                  }

                  // SCHEDULE AN AUTOFOCUS ON THE ITEM THAT HAS BEEN MODIFIED IN THE PREVIEW
                  // the 'multi-items-module-refreshed' event is sent on each preview update due to a Nimble change
                  // @see sendSuccessDataToPanel() in SekPreviewPrototype::schedulePanelMsgReactions
                  api.previewer.bind('multi-items-module-refreshed', function( params ) {
                        if ( _.isUndefined( params.apiParams.control_id ) )
                          return;
                        // the module_id param is added on control registration
                        // @see CZRSeksPrototype::generateUIforFrontModules
                        // we use it to identify that
                        api.control( params.apiParams.control_id, function( _control_ ) {
                              if ( _.isUndefined( _control_.params.sek_registration_params ) )
                                return;
                              if ( api.control( _control_.id ).params.sek_registration_params.module_id !== params.apiParams.id )
                                return;
                              _control_.czr_Module.each( function( _module_ ) {
                                    _module_.czr_Item.each( function( _item_ ) {
                                          if( 'expanded' === _item_.viewState() ) {
                                                _item_.trigger('sek-request-item-focus-in-preview');
                                          }
                                    });
                              });
                        });
                  });//api.previewer.bind()

                  // April 2020. For https://github.com/presscustomizr/nimble-builder/issues/651
                  self.setupTemplateGallery();

                  // March 2021. For #478. 'czr-new-skopes-synced' is sent by preview on each refresh
                  api.previewer.bind( 'czr-new-skopes-synced', function( skope_server_data ) {
                        //console.log('SKOPED SYNCED');
                        var localSektionsData = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local');
                        if ( sektionsLocalizedData.isDevMode ) {
                              api.infoLog( '::czr-new-skopes-synced => SEKTIONS DATA ? ', localSektionsData );
                        }
                        if ( _.isEmpty( localSektionsData ) ) {
                              api.errare('::czr-new-skopes-synced => no sektionsData');
                        }
                        if ( _.isEmpty( localSektionsData.setting_id ) ) {
                              api.errare('::czr-new-skopes-synced => missing setting_id');
                        }

                  });
            },//doSektionThinksOnApiReady







            // Fired at api "ready"
            registerAndSetupDefaultPanelSectionOptions : function() {
                  var self = this;

                  // MAIN SEKTION PANEL
                  var SektionPanelConstructor = api.Panel.extend({
                        //attachEvents : function () {},
                        // Always make the panel active, event if we have no sections / control in it
                        isContextuallyActive : function () {
                          return this.active();
                        },
                        _toggleActive : function(){ return true; }
                  });

                  // Prepend the Nimble logo in the main panel title
                  // the panel.expanded() Value is not the right candidate to be observed because it gets changed on too many events, when generating the various UI.
                  api.panel( sektionsLocalizedData.sektionsPanelId, function( _mainPanel_ ) {
                        _mainPanel_.deferred.embedded.done( function() {
                              var $sidePanelTitleEl = _mainPanel_.container.first().find('h3.accordion-section-title'),
                                  $topPanelTitleEl = _mainPanel_.container.first().find('.panel-meta .accordion-section-title'),
                                  logoHtml = [
                                      '<img class="sek-nimble-logo" alt="'+ _mainPanel_.params.title +'" src="',
                                      sektionsLocalizedData.baseUrl,
                                      '/assets/img/nimble/nimble_horizontal.svg?ver=' + sektionsLocalizedData.nimbleVersion,
                                      '"/>',
                                  ].join('');
                              // Add Pro
                              if ( sektionsLocalizedData.isPro ) {
                                  logoHtml += [
                                      '<img class="sek-nimble-logo sek-pro-pastil" src="',
                                      sektionsLocalizedData.baseUrl,
                                      '/assets/czr/sek/img/pro_white.svg?ver=' + sektionsLocalizedData.nimbleVersion,
                                      '"/>',
                                  ].join('');
                              }

                              if ( 0 < $sidePanelTitleEl.length ) {
                                    // The default title looks like this : Nimble Builder <span class="screen-reader-text">Press return or enter to open this section</span>
                                    // we want to style "Nimble Builder" only.
                                    var $sidePanelTitleElSpan = $sidePanelTitleEl.find('span');
                                    $sidePanelTitleEl
                                          .addClass('sek-side-nimble-logo-wrapper')
                                          .html( logoHtml )
                                          .append( $sidePanelTitleElSpan );
                              }

                              // default looks like
                              // <span class="preview-notice">You are customizing <strong class="panel-title">Nimble Builder</strong></span>
                              // if ( 0 < $topPanelTitleEl.length ) {
                              //       var $topPanelTitleElInner = $topPanelTitleEl.find('.panel-title');
                              //       $topPanelTitleElInner.html( logoHtml );
                              // }

                              // NOV FEEDBACK UI DISABLED in favor of an admin notice
                              // if ( sektionsLocalizedData.eligibleForFeedbackNotification ) {
                              //       _mainPanel_.expanded.bind( function( expanded ) {
                              //             if ( expanded && _.isUndefined( self.feedbackUIVisible ) ) {
                              //                   // FEEDBACK UI
                              //                   self.setupFeedBackUI();

                              //                   // march 2020 : print confettis
                              //                   // confettis script is enqueued in the preview
                              //                   setTimeout( function() {
                              //                       api.previewer.send('sek-print-confettis', { duration : Date.now() + (1 * 2000) } );
                              //                   }, 1000 );
                              //             }
                              //       });
                              // }//if ( sektionsLocalizedData.eligibleForFeedbackNotification )
                        });
                  });

                  // The parent panel for all ui sections + global options section
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'panel',
                        id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                        title: sektionsLocalizedData.i18n['Nimble Builder'],
                        priority : -1000,
                        constructWith : SektionPanelConstructor,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                  });


                  //GLOBAL OPTIONS SECTION
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : self.SECTION_ID_FOR_GLOBAL_OPTIONS,
                        title: sektionsLocalizedData.i18n['Site wide options'],
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 20,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        constructWith : api.Section.extend({
                              //attachEvents : function () {},
                              // Always make the section active, event if we have no control in it
                              isContextuallyActive : function () {
                                return this.active();
                              },
                              _toggleActive : function(){ return true; }
                        })
                  }).done( function() {
                        api.section( self.SECTION_ID_FOR_GLOBAL_OPTIONS, function( _section_ ) {
                              // Style the section title
                              var $sectionTitleEl = _section_.container.find('.accordion-section-title'),
                                  $panelTitleEl = _section_.container.find('.customize-section-title h3');

                              // The default title looks like this : Title <span class="screen-reader-text">Press return or enter to open this section</span>
                              if ( 0 < $sectionTitleEl.length ) {
                                    $sectionTitleEl.prepend( '<i class="fas fa-globe sek-level-option-icon"></i>' );
                              }

                              // The default title looks like this : <span class="customize-action">Customizing</span> Title
                              if ( 0 < $panelTitleEl.length ) {
                                    $panelTitleEl.find('.customize-action').after( '<i class="fas fa-globe sek-level-option-icon"></i>' );
                              }

                              // Schedule the accordion behaviour
                              self.scheduleModuleAccordion.call( _section_ );
                        });
                  });

                  //LOCAL OPTIONS SECTION
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : self.SECTION_ID_FOR_LOCAL_OPTIONS,//<= the section id doesn't need to be skope dependant. Only the control id is skope dependant.
                        title: sektionsLocalizedData.i18n['Current page options'],
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 10,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        constructWith : api.Section.extend({
                              //attachEvents : function () {},
                              // Always make the section active, event if we have no control in it
                              isContextuallyActive : function() {
                                return this.active();
                              },
                              _toggleActive : function(){ return true; }
                        })
                  }).done( function() {
                        api.section( self.SECTION_ID_FOR_LOCAL_OPTIONS, function( _section_ ) {
                              // Style the section title
                              var $sectionTitleEl = _section_.container.find('.accordion-section-title'),
                                  $panelTitleEl = _section_.container.find('.customize-section-title h3');

                              // The default title looks like this : Title <span class="screen-reader-text">Press return or enter to open this section</span>
                              if ( 0 < $sectionTitleEl.length ) {
                                    $sectionTitleEl.prepend( '<i class="fas fa-map-marker-alt sek-level-option-icon"></i>' );
                              }

                              // The default title looks like this : <span class="customize-action">Customizing</span> Title
                              if ( 0 < $panelTitleEl.length ) {
                                    $panelTitleEl.find('.customize-action').after( '<i class="fas fa-map-marker-alt sek-level-option-icon"></i>' );
                              }

                              // Schedule the accordion behaviour
                              self.scheduleModuleAccordion.call( _section_ );
                        });
                  });


                  // SITE WIDE GLOBAL OPTIONS SETTING
                  // Will Be updated in ::generateUIforGlobalOptions()
                  // has no control.
                  api.CZR_Helpers.register( {
                        origin : 'nimble',
                        //level : params.level,
                        what : 'setting',
                        id : sektionsLocalizedData.optNameForGlobalOptions,
                        dirty : false,
                        value : sektionsLocalizedData.globalOptionDBValues,
                        transport : 'postMessage',//'refresh',//// ,
                        type : 'option'
                  });

                  // CONTENT PICKER SECTION
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : self.SECTION_ID_FOR_CONTENT_PICKER,
                        title: sektionsLocalizedData.i18n['Content Picker'],
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 30,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        constructWith : api.Section.extend({
                              //attachEvents : function () {},
                              // Always make the section active, event if we have no control in it
                              isContextuallyActive : function() {
                                return this.active();
                              },
                              _toggleActive : function(){ return true; }
                        })
                  }).done( function() {
                        // generate the UI for the content picker if not done yet
                        // defer this action when the section is instantiated AND the api.previewer is active, so we can trigger event on it
                        // => we also need the local skope to be set, that's why api.czr_initialSkopeCollectionPopulated is convenient because it ensures the api.previewer is ready and we have a local skope set.
                        // @see czr-skope-base.js
                        // @fixes https://github.com/presscustomizr/nimble-builder/issues/187
                        api.section( self.SECTION_ID_FOR_CONTENT_PICKER, function( _section_ ) {
                              if ( 'resolved' != api.czr_initialSkopeCollectionPopulated.state() ) {
                                    api.czr_initialSkopeCollectionPopulated.done( function() {
                                          api.previewer.trigger('sek-pick-content', { focus : false });
                                    });
                              } else {
                                    api.previewer.trigger('sek-pick-content', { focus : false });
                              }
                        });
                  });
            },//registerAndSetupDefaultPanelSectionOptions()







            //@return void()
            // sektionsData is built server side :
            //array(
            //     'db_values' => sek_get_skoped_seks( $skope_id ),
            //     'setting_id' => sek_get_seks_setting_id( $skope_id )//nimble___[skp__post_page_home]
            // )
            setContextualCollectionSettingIdWhenSkopeSet : function( newSkopes, previousSkopes ) {
                  var self = this;
                  previousSkopes = previousSkopes || {};
                  // Clear all previous sektions if the main panel is expanded and we're coming from a previousSkopes
                  if ( !_.isEmpty( previousSkopes.local ) && api.panel( sektionsLocalizedData.sektionsPanelId ).expanded() ) {
                        // We don't want to change focus to content picker when setting site templates ( which forces a preview refresh on home when being opened and modified )
                        if ( _.isUndefined(api._nimbleRefreshingPreviewHomeWhenSettingSiteTemplate) || !api._nimbleRefreshingPreviewHomeWhenSettingSiteTemplate ) {
                              api.previewer.trigger('sek-pick-content');
                        }
                  }

                  // set the localSectionsSettingId now, and update it on skope change
                  sektionsData = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local');
                  if ( sektionsLocalizedData.isDevMode ) {
                        api.infoLog( '::setContextualCollectionSettingIdWhenSkopeSet => SEKTIONS DATA ? ', sektionsData );
                  }
                  if ( _.isEmpty( sektionsData ) ) {
                        api.errare('::setContextualCollectionSettingIdWhenSkopeSet() => no sektionsData');
                  }
                  if ( _.isEmpty( sektionsData.setting_id ) ) {
                        api.errare('::setContextualCollectionSettingIdWhenSkopeSet() => missing setting_id');
                  }
                  self.localSectionsSettingId( sektionsData.setting_id );
            }
      });//$.extend()
})( wp.customize, jQuery );
