
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {

            initialize: function() {
                  var self = this;
                  if ( _.isUndefined( window.sektionsLocalizedData ) ) {
                        throw new Error( 'CZRSeksPrototype => missing localized server params sektionsLocalizedData' );
                  }
                  if ( ! _.isFunction( api.czr_activeSkopes ) ) {
                        throw new Error( 'CZRSeksPrototype => api.czr_activeSkopes' );
                  }
                  self.SECTION_ID_FOR_GLOBAL_OPTIONS = '__globalOptionsSectionId';
                  self.SECTION_ID_FOR_LOCAL_OPTIONS = '__localOptionsSection';
                  self.SECTION_ID_FOR_CONTENT_PICKER = '__content_picker__';
                  self.MAX_NUMBER_OF_COLUMNS = 12;
                  self.SETTING_UPDATE_BUFFER = 100;
                  self.defaultLocalSektionSettingValue = self.getDefaultSektionSettingValue( 'local' );
                  self.localSectionsSettingId = new api.Value( {} );
                  self.registered = new api.Value([]);


                  api.bind( 'ready', function() {
                        self.doSektionThinksOnApiReady();
                  });//api.bind( 'ready' )
                  api.bind( 'save-request-params', function( query ) {
                        $.extend( query, { local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ) } );
                  });

            },// initialize()
            doSektionThinksOnApiReady : function() {
                  var self = this;
                  self.registerAndSetupDefaultPanelSectionOptions();
                  self.localSectionsSettingId.callbacks.add( function( collectionSettingIds, previousCollectionSettingIds ) {
                        try { self.setupSettingsToBeSaved(); } catch( er ) {
                              api.errare( 'Error in self.localSectionsSettingId.callbacks => self.setupSettingsToBeSaved()' , er );
                        }
                        self.initializeHistoryLogWhenSettingsRegistered();
                  });
                  var doSkopeDependantActions = function( newSkopes, previousSkopes ) {
                        self.setContextualCollectionSettingIdWhenSkopeSet( newSkopes, previousSkopes );
                        api.section( self.SECTION_ID_FOR_LOCAL_OPTIONS, function( _section_ ) {
                              _section_.deferred.embedded.done( function() {
                                    if( true === _section_.boundForLocalOptionGeneration )
                                      return;
                                    _section_.boundForLocalOptionGeneration = true;
                                    _section_.expanded.bind( function( expanded ) {
                                          if ( true === expanded ) {
                                                self.generateUI({ action : 'sek-generate-local-skope-options-ui'});
                                          }
                                    });
                              });
                        });
                        api.section( self.SECTION_ID_FOR_GLOBAL_OPTIONS, function( _section_ ) {
                              if ( true === _section_.nimbleGlobalOptionGenerated )
                                return;
                              self.generateUI({ action : 'sek-generate-global-options-ui'});
                              _section_.nimbleGlobalOptionGenerated = true;
                        });
                        api.trigger('nimble-ready-for-current-skope');
                  };//doSkopeDependantActions()
                  if ( ! _.isEmpty( api.czr_activeSkopes().local ) ) {
                        doSkopeDependantActions();
                  }
                  api.czr_activeSkopes.callbacks.add( function( newSkopes, previousSkopes ) {
                        doSkopeDependantActions( newSkopes, previousSkopes );
                  });
                  self.reactToPreviewMsg();
                  self.setupDnd();
                  self.setupTinyMceEditor();
                  self.schedulePrintSectionJson();
                  self.bind( 'sek-ui-removed', function() {
                        api.previewedDevice( 'desktop' );
                  });
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
                  });
                  $('#customize-notifications-area').on( 'click', '[data-sek-reset="true"]', function() {
                        self.resetCollectionSetting();
                  });
                  self.bind( 'sek-ui-pre-removal', function( params ) {
                        if ( 'control' == params.what && -1 < params.id.indexOf( 'draggable') ) {
                              api.control( params.id, function( _ctrl_ ) {
                                    _ctrl_.container.find( '[draggable]' ).each( function() {
                                          $(this).off( 'dragstart dragend' );
                                    });
                              });
                        }
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
                  api.bind( 'czr-new-registered', function( params ) {
                        if ( _.isUndefined( params.origin ) ) {
                              throw new Error( 'czr-new-registered event => missing params.origin' );
                        }
                        if ( 'nimble' !== params.origin )
                          return;
                        if ( false !== params.track ) {
                              var currentlyRegistered = self.registered();
                              var newRegistered = $.extend( true, [], currentlyRegistered );
                              var duplicateCandidate = _.findWhere( newRegistered, { id : params.id } );
                              if ( ! _.isEmpty( duplicateCandidate ) && _.isEqual( duplicateCandidate, params ) ) {
                                    throw new Error( 'register => duplicated element in self.registered() collection ' + params.id );
                              }
                              newRegistered.push( params );
                              self.registered( newRegistered );
                        }
                  });
                  self.setupTopBar();//@see specific dev file
                  if ( sektionsLocalizedData.isSavedSectionEnabled ) {
                        self.setupSaveUI();
                  }
                  self.lastClickedTargetInPreview = new api.Value();
                  self.lastClickedTargetInPreview.bind( function( to, from ) {
                        if ( _.isObject( to ) && to.id ) {
                              api.previewer.send( 'sek-set-double-click-target', to );
                        } else {
                              api.previewer.send( 'sek-reset-double-click-target' );
                        }
                        clearTimeout( $(window).data('_preview_target_timer_') );
                        $(window).data('_preview_target_timer_', setTimeout(function() {
                              self.lastClickedTargetInPreview( {} );
                              api.previewer.send( 'sek-reset-double-click-target' );
                        }, 20000 ) );
                  });
                  api.previewer.bind( 'sek-clean-target-drop-zone', function() {
                        self.lastClickedTargetInPreview({});
                  });
                  $(document).keydown(function( evt ) {
                        if ( evt && 27 === evt.keyCode ) {
                            self.lastClickedTargetInPreview({});
                        }
                  });
            },//doSektionThinksOnApiReady
            registerAndSetupDefaultPanelSectionOptions : function() {
                  var self = this;
                  var SektionPanelConstructor = api.Panel.extend({
                        isContextuallyActive : function () {
                          return this.active();
                        },
                        _toggleActive : function(){ return true; }
                  });
                  api.panel( sektionsLocalizedData.sektionsPanelId, function( _mainPanel_ ) {
                        _mainPanel_.deferred.embedded.done( function() {
                              var $sidePanelTitleEl = _mainPanel_.container.find('h3.accordion-section-title'),
                                  $topPanelTitleEl = _mainPanel_.container.find('.panel-meta .accordion-section-title'),
                                  logoHtml = [ '<img class="sek-nimble-logo" alt="'+ _mainPanel_.params.title +'" src="', sektionsLocalizedData.baseUrl, '/assets/img/nimble/nimble_horizontal.svg?ver=' + sektionsLocalizedData.nimbleVersion , '"/>' ].join('');

                              if ( 0 < $sidePanelTitleEl.length ) {
                                    var $sidePanelTitleElSpan = $sidePanelTitleEl.find('span');
                                    $sidePanelTitleEl
                                          .addClass('sek-side-nimble-logo-wrapper')
                                          .html( logoHtml )
                                          .append( $sidePanelTitleElSpan );
                              }
                        });
                  });
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'panel',
                        id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                        title: sektionsLocalizedData.i18n['Nimble Builder'],
                        priority : -1000,
                        constructWith : SektionPanelConstructor,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                  });
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : self.SECTION_ID_FOR_GLOBAL_OPTIONS,
                        title: sektionsLocalizedData.i18n['Site wide options'],
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 20,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        constructWith : api.Section.extend({
                              isContextuallyActive : function () {
                                return this.active();
                              },
                              _toggleActive : function(){ return true; }
                        })
                  }).done( function() {
                        api.section( self.SECTION_ID_FOR_GLOBAL_OPTIONS, function( _section_ ) {
                              var $sectionTitleEl = _section_.container.find('.accordion-section-title'),
                                  $panelTitleEl = _section_.container.find('.customize-section-title h3');
                              if ( 0 < $sectionTitleEl.length ) {
                                    $sectionTitleEl.prepend( '<i class="fas fa-globe sek-level-option-icon"></i>' );
                              }
                              if ( 0 < $panelTitleEl.length ) {
                                    $panelTitleEl.find('.customize-action').after( '<i class="fas fa-globe sek-level-option-icon"></i>' );
                              }
                              self.scheduleModuleAccordion.call( _section_ );
                        });
                  });
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : self.SECTION_ID_FOR_LOCAL_OPTIONS,//<= the section id doesn't need to be skope dependant. Only the control id is skope dependant.
                        title: sektionsLocalizedData.i18n['Current page options'],
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 10,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        constructWith : api.Section.extend({
                              isContextuallyActive : function () {
                                return this.active();
                              },
                              _toggleActive : function(){ return true; }
                        })
                  }).done( function() {
                        api.section( self.SECTION_ID_FOR_LOCAL_OPTIONS, function( _section_ ) {
                              var $sectionTitleEl = _section_.container.find('.accordion-section-title'),
                                  $panelTitleEl = _section_.container.find('.customize-section-title h3');
                              if ( 0 < $sectionTitleEl.length ) {
                                    $sectionTitleEl.prepend( '<i class="fas fa-map-marker-alt sek-level-option-icon"></i>' );
                              }
                              if ( 0 < $panelTitleEl.length ) {
                                    $panelTitleEl.find('.customize-action').after( '<i class="fas fa-map-marker-alt sek-level-option-icon"></i>' );
                              }
                              self.scheduleModuleAccordion.call( _section_ );
                        });
                  });
                  api.CZR_Helpers.register( {
                        origin : 'nimble',
                        what : 'setting',
                        id : sektionsLocalizedData.optNameForGlobalOptions,
                        dirty : false,
                        value : sektionsLocalizedData.globalOptionDBValues,
                        transport : 'refresh',//'refresh',//// ,
                        type : 'option'
                  });
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : self.SECTION_ID_FOR_CONTENT_PICKER,
                        title: sektionsLocalizedData.i18n['Content Picker'],
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 30,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        constructWith : api.Section.extend({
                              isContextuallyActive : function () {
                                return this.active();
                              },
                              _toggleActive : function(){ return true; }
                        })
                  }).done( function() {
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
            setContextualCollectionSettingIdWhenSkopeSet : function( newSkopes, previousSkopes ) {
                  var self = this;
                  previousSkopes = previousSkopes || {};
                  if ( ! _.isEmpty( previousSkopes.local ) && api.panel( sektionsLocalizedData.sektionsPanelId ).expanded() ) {
                        api.previewer.trigger('sek-pick-content');
                  }
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
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            setupTopBar : function() {
                  var self = this;
                  self.topBarId = '#nimble-top-bar';
                  self.topBarVisible = new api.Value( false );
                  self.topBarVisible.bind( function( visible ){
                        self.toggleTopBar( visible );
                  });

                  self.mouseMovedRecently = new api.Value( {} );
                  self.mouseMovedRecently.bind( function( position ) {
                        self.topBarVisible( ! _.isEmpty( position ) );
                  });

                  var trackMouseMovements = function( evt ) {
                        self.mouseMovedRecently( { x : evt.clientX, y : evt.clientY } );
                        clearTimeout( $(window).data('_scroll_move_timer_') );
                        $(window).data('_scroll_move_timer_', setTimeout(function() {
                              self.mouseMovedRecently.set( {} );
                        }, 4000 ) );
                  };
                  $(window).on( 'mousemove scroll,', _.throttle( trackMouseMovements , 50 ) );
                  api.previewer.bind('ready', function() {
                        $(api.previewer.targetWindow().document ).on( 'mousemove scroll,', _.throttle( trackMouseMovements , 50 ) );
                  });
            },
            toggleTopBar : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndSetupTopBarTmpl({}) ).done( function( $_el ) {
                                  self.topBarContainer = $_el;
                                  _.delay( function() {
                                      $('body').addClass('nimble-top-bar-visible');
                                  }, 200 );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            $('body').removeClass('nimble-top-bar-visible');
                            if ( self.topBarContainer && self.topBarContainer.length ) {
                                  _.delay( function() {
                                        dfd.resolve();
                                  }, 300 );
                            } else {
                                dfd.resolve();
                            }
                            return dfd.promise();
                      };

                  if ( visible ) {
                        _renderAndSetup();
                  } else {
                        _hide().done( function() {
                              self.topBarVisible( false );//should be already false
                        });
                  }
            },
            renderAndSetupTopBarTmpl : function( params ) {
                  var self = this;
                  if ( $( self.topBarId ).length > 0 )
                    return $( self.topBarId );
                  try {
                        _tmpl =  wp.template( 'nimble-top-bar' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing the the top note template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );
                  $(document).keydown( function( evt ) {
                        if ( evt.ctrlKey && _.contains( [89, 90], evt.keyCode ) ) {
                              try { self.navigateHistory( 90 === evt.keyCode ? 'undo' : 'redo'); } catch( er ) {
                                    api.errare( 'Error when firing self.navigateHistory', er );
                              }
                        }
                  });
                  $('[data-nimble-history]', self.topBarId).on( 'click', function(evt) {
                        try { self.navigateHistory( $(this).data( 'nimble-history') ); } catch( er ) {
                              api.errare( 'Error when firing self.navigateHistory', er );
                        }
                  });
                  $('.sek-settings', self.topBarId).on( 'click', function(evt) {
                        api.panel( sektionsLocalizedData.sektionsPanelId, function( _panel_ ) {
                              self.rootPanelFocus();
                              _panel_.focus();
                        });
                  });
                  $('.sek-add-content', self.topBarId).on( 'click', function(evt) {
                        evt.preventDefault();
                        api.previewer.trigger( 'sek-pick-content', { content_type : 'module' });
                  });
                  $('.sek-nimble-doc', self.topBarId).on( 'click', function(evt) {
                        evt.preventDefault();
                        window.open($(this).data('doc-href'), '_blank');
                  });
                  var maybePrintNotificationForUsageOfNimbleTemplate = function( templateSettingValue ) {
                        if ( $(self.topBarId).length < 1 )
                          return;
                        if ( _.isObject( templateSettingValue ) && templateSettingValue.local_template && 'default' !== templateSettingValue.local_template ) {
                              $(self.topBarId).find('.sek-notifications').html([
                                    '<span class="fas fa-info-circle"></span>',
                                    sektionsLocalizedData.i18n['This page uses a custom template.']
                              ].join(' '));
                        } else {
                              $(self.topBarId).find('.sek-notifications').html('');
                        }
                  };

                  var initOnSkopeReady = function() {
                        api( self.localSectionsSettingId(), function( _localSectionsSetting_ ) {
                              var localSectionsValue = _localSectionsSetting_(),
                                  initialLocalTemplateValue = ( _.isObject( localSectionsValue ) && localSectionsValue.local_options && localSectionsValue.local_options.template ) ? localSectionsValue.local_options.template : null;
                              maybePrintNotificationForUsageOfNimbleTemplate( initialLocalTemplateValue );
                        });
                        api( self.getLocalSkopeOptionId() + '__template', function( _set_ ) {
                              _set_.bind( function( to, from ) {
                                    maybePrintNotificationForUsageOfNimbleTemplate( to );
                              });
                        });
                  };
                  initOnSkopeReady();
                  api.bind('nimble-ready-for-current-skope', function() {
                        initOnSkopeReady();
                  });

                  return $( self.topBarId );
            },


            /* HISTORY */
            navigateHistory : function( direction ) {
                  var self = this,
                      historyLog = $.extend( true, [], self.historyLog() );
                  var previous,
                      current,
                      future,
                      newHistoryLog = [],
                      newSettingValue,
                      previousSektionToRefresh,
                      currentSektionToRefresh;

                  _.each( historyLog, function( log ) {
                        if ( ! _.isEmpty( newSettingValue ) ) {
                              return;
                        }
                        switch( log.status ) {
                              case 'previous' :
                                    previous = log;
                              break;
                              case 'current' :
                                    current = log;
                              break;
                              case 'future' :
                                    future = log;
                              break;
                        }
                        switch( direction ) {
                              case 'undo' :
                                    if ( ! _.isEmpty( current ) && ! _.isEmpty( previous ) ) {
                                          newSettingValue = previous.value;
                                          previousSektionToRefresh = current.sektionToRefresh;
                                          currentSektionToRefresh = previous.sektionToRefresh;
                                    }
                              break;
                              case 'redo' :
                                    if ( ! _.isEmpty( future ) ) {
                                          newSettingValue = future.value;
                                          previousSektionToRefresh = current.sektionToRefresh;
                                          currentSektionToRefresh = future.sektionToRefresh;
                                    }
                              break;
                        }
                  });
                  if( ! _.isUndefined( newSettingValue ) ) {
                        if ( ! _.isEmpty( newSettingValue.local ) ) {
                              api( self.localSectionsSettingId() )( self.validateSettingValue( newSettingValue.local ), { navigatingHistoryLogs : true } );
                        }
                        if ( ! _.isEmpty( newSettingValue.global ) ) {
                              api( self.getGlobalSectionsSettingId() )( self.validateSettingValue( newSettingValue.global ), { navigatingHistoryLogs : true } );
                        }
                        var previewHasBeenRefreshed = false;
                        api.previewer.refresh();
                        api.previewer.trigger( 'sek-pick-content', {});
                        self.cleanRegistered();//<= normal cleaning
                        self.cleanRegisteredLevelSettingsAfterHistoryNavigation();// setting cleaning
                  }
                  var currentKey = _.findKey( historyLog, { status : 'current'} );
                  currentKey = Number( currentKey );
                  if ( ! _.isNumber( currentKey ) ) {
                        api.errare( 'Error when navigating the history log, the current key should be a number');
                        return;
                  }

                  _.each( historyLog, function( log, key ) {
                        newLog = $.extend( true, {}, log );
                        key = Number( key );
                        switch( direction ) {
                              case 'undo' :
                                    if ( 0 < currentKey ) {
                                          if ( key === ( currentKey - 1 ) ) {
                                                newLog.status = 'current';
                                          } else if ( key === currentKey ) {
                                                newLog.status = 'future';
                                          }
                                    }
                              break;
                              case 'redo' :
                                    if ( historyLog.length > ( currentKey + 1 ) ) {
                                          if ( key === currentKey ) {
                                                newLog.status = 'previous';
                                          } else if ( key === ( currentKey + 1 ) ) {
                                                newLog.status = 'current';
                                          }
                                    }
                              break;
                        }
                        newHistoryLog.push( newLog );
                  });
                  self.historyLog( newHistoryLog );
            }
      });//$.extend()
})( wp.customize, jQuery );
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            setupSaveUI : function() {
                  var self = this;
                  self.saveUIVisible = new api.Value( false );
                  self.saveUIVisible.bind( function( to, from, params ){
                        self.toggleSaveUI( to, params ? params.id : null );
                  });
            },
            toggleSaveUI : function( visible, sectionId ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndSetupSaveUITmpl({}) ).done( function( $_el ) {
                                  self.saveUIContainer = $_el;
                                  _.delay( function() {
                                      $('body').addClass('nimble-save-ui-visible');
                                  }, 200 );
                                  $('#sek-saved-section-id').val( sectionId );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            $('body').removeClass('nimble-save-ui-visible');
                            if ( $( '#nimble-top-save-ui' ).length > 0 ) {
                                  _.delay( function() {

                                        self.saveUIContainer.remove();
                                        dfd.resolve();
                                  }, 300 );
                            } else {
                                dfd.resolve();
                            }
                            return dfd.promise();
                      };

                  if ( visible ) {
                        _renderAndSetup();
                  } else {
                        _hide().done( function() {
                              self.saveUIVisible( false );//should be already false
                        });
                  }
            },
            preProcessSektion : function( sectionModel ) {
                  var self = this, sektionCandidate = self.cleanIds( sectionModel );
                  return _.omit( sektionCandidate, function( val, key ) {
                        return _.contains( ['id', 'level'], key );
                  });
            },
            renderAndSetupSaveUITmpl : function( params ) {
                  if ( $( '#nimble-top-save-ui' ).length > 0 )
                    return $( '#nimble-top-save-ui' );

                  var self = this;

                  try {
                        _tmpl =  wp.template( 'nimble-top-save-ui' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing the the top note template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );
                  $('.sek-do-save-section', '#nimble-top-save-ui').on( 'click', function(evt) {
                        evt.preventDefault();
                        var sectionModel = $.extend( true, {}, self.getLevelModel( $('#sek-saved-section-id').val() ) ),
                            sek_title = $('#sek-saved-section-title').val(),
                            sek_description = $('#sek-saved-section-description').val(),
                            sek_id = self.guid(),
                            sek_data = self.preProcessSektion(sectionModel);

                        if ( _.isEmpty( sek_title ) ) {
                            $('#sek-saved-section-title').addClass('error');
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

                        $('#sek-saved-section-title').removeClass('error');

                        wp.ajax.post( 'sek_save_section', {
                              nonce: api.settings.nonce.save,
                              sek_title: sek_title,
                              sek_description: sek_description,
                              sek_id: sek_id,
                              sek_data: JSON.stringify( sek_data )
                        })
                        .done( function( response ) {
                              api.previewer.trigger('sek-notify', {
                                  type : 'success',
                                  duration : 10000,
                                  message : [
                                        '<span style="font-size:0.95em">',
                                          '<strong>@missi18n Your section has been saved.</strong>',
                                        '</span>'
                                  ].join('')
                              });
                        })
                        .fail( function( er ) {
                              api.errorLog( 'ajax sek_save_section => error', er );
                              api.previewer.trigger('sek-notify', {
                                  type : 'error',
                                  duration : 10000,
                                  message : [
                                        '<span style="font-size:0.95em">',
                                          '<strong>@missi18n You need to set a title</strong>',
                                        '</span>'
                                  ].join('')
                              });
                        });
                  });//on click

                  $('.sek-cancel-save', '#nimble-top-save-ui').on( 'click', function(evt) {
                        evt.preventDefault();
                        self.saveUIVisible(false);
                  });

                  return $( '#nimble-top-save-ui' );
            }
      });//$.extend()
})( wp.customize, jQuery );
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            setupSettingsToBeSaved : function() {
                  var self = this,
                      serverCollection;
                  var _settingsToRegister_ = {
                        'local' : { collectionSettingId : self.localSectionsSettingId() },//<= "nimble___[skp__post_page_10]"
                        'global' : { collectionSettingId : self.getGlobalSectionsSettingId() }//<= "nimble___[skp__global]"
                  };

                  _.each( _settingsToRegister_, function( settingData, localOrGlobal ) {
                        serverCollection = api.czr_skopeBase.getSkopeProperty( 'sektions', localOrGlobal ).db_values;
                        if ( _.isEmpty( settingData.collectionSettingId ) ) {
                              throw new Error( 'setupSettingsToBeSaved => the collectionSettingId is invalid' );
                        }
                        if ( ! api.has( settingData.collectionSettingId ) ) {
                              var __collectionSettingInstance__ = api.CZR_Helpers.register({
                                    what : 'setting',
                                    id : settingData.collectionSettingId,
                                    value : self.validateSettingValue( _.isObject( serverCollection ) ? serverCollection : self.getDefaultSektionSettingValue( localOrGlobal )  ),
                                    transport : 'postMessage',//'refresh'
                                    type : 'option',
                                    track : false,//don't register in the self.registered()
                                    origin : 'nimble'
                              });
                              api( settingData.collectionSettingId, function( sektionSetInstance ) {
                                    sektionSetInstance.bind( _.debounce( function( newSektionSettingValue, previousValue, params ) {
                                          self.trackHistoryLog( sektionSetInstance, params );

                                    }, 1000 ) );
                              });//api( settingData.collectionSettingId, function( sektionSetInstance ){}
                        }//if ( ! api.has( settingData.collectionSettingId ) ) {
                  });//_.each(
            },// SetupSettingsToBeSaved()
            trackHistoryLog : function( sektionSetInstance, params ) {
                  var self = this,
                      _isGlobal = sektionSetInstance.id === self.getGlobalSectionsSettingId();
                  if ( params && true !== params.navigatingHistoryLogs ) {
                        var newHistoryLog = [],
                            historyLog = $.extend( true, [], self.historyLog() ),
                            sektionToRefresh;

                        if ( ! _.isEmpty( params.in_sektion ) ) {//<= module changed, column resized, removed...
                              sektionToRefresh = params.in_sektion;
                        } else if ( ! _.isEmpty( params.to_sektion ) ) {// column moved /
                              sektionToRefresh = params.to_sektion;
                        }
                        _.each( historyLog, function( log ) {
                              var newStatus = 'previous';
                              if ( 'future' == log.status )
                                return;
                              $.extend( log, { status : 'previous' } );
                              newHistoryLog.push( log );
                        });
                        newHistoryLog.push({
                              status : 'current',
                              value : _isGlobal ? { global : sektionSetInstance() } : { local : sektionSetInstance() },
                              action : _.isObject( params ) ? ( params.action || '' ) : '',
                              sektionToRefresh : sektionToRefresh
                        });
                        self.historyLog( newHistoryLog );
                  }
            },
            initializeHistoryLogWhenSettingsRegistered : function() {
                  var self = this;
                  self.historyLog = new api.Value([{
                        status : 'current',
                        value : {
                              'local' : api( self.localSectionsSettingId() )(),//<= "nimble___[skp__post_page_10]"
                              'global' : api(  self.getGlobalSectionsSettingId() )()
                        },
                        action : 'initial'
                  }]);
                  self.historyLog.bind( function( newLog ) {
                        if ( _.isEmpty( newLog ) )
                          return;

                        var newCurrentKey = _.findKey( newLog, { status : 'current'} );
                        newCurrentKey = Number( newCurrentKey );
                        $( '#nimble-top-bar' ).find('[data-nimble-history]').each( function() {
                              if ( 'undo' === $(this).data('nimble-history') ) {
                                    $(this).attr('data-nimble-state', 0 >= newCurrentKey ? 'disabled' : 'enabled');
                              } else {
                                    $(this).attr('data-nimble-state', newLog.length <= ( newCurrentKey + 1 ) ? 'disabled' : 'enabled');
                              }
                        });
                  });
            },
            validateSettingValue : function( valCandidate ) {
                  if ( ! _.isObject( valCandidate ) ) {
                        api.errare('validation error => the setting should be an object', valCandidate );
                        return null;
                  }
                  var parentLevel = {},
                      errorDetected = false,
                      levelIds = [];
                  var _errorDetected_ = function( msg ) {
                        api.errare( msg , valCandidate );
                        api.previewer.trigger('sek-notify', {
                              type : 'error',
                              duration : 30000,
                              message : [
                                    '<span style="font-size:0.95em">',
                                      '<strong>' + msg + '</strong>',
                                      '<br>',
                                      sektionsLocalizedData.i18n['If this problem locks the Nimble builder, you might try to reset the sections for this page.'],
                                      '<br>',
                                      '<span style="text-align:center;display:block">',
                                        '<button type="button" class="button" aria-label="' + sektionsLocalizedData.i18n.Reset + '" data-sek-reset="true">' + sektionsLocalizedData.i18n.Reset + '</button>',
                                      '</span>',
                                    '</span>'
                              ].join('')

                        });
                        errorDetected = true;
                  };
                  var _checkWalker_ = function( level ) {
                      if ( errorDetected ) {
                            return;
                      }
                      if ( _.isUndefined( level ) && _.isEmpty( parentLevel ) ) {
                            level = $.extend( true, {}, valCandidate );
                            if ( _.isUndefined( level.id ) || _.isUndefined( level.level ) ) {
                                  if ( _.isUndefined( level.collection ) ) {
                                        _errorDetected_( 'validation error => the root level is missing the collection of locations' );
                                        return;
                                  }
                                  if ( ! _.isEmpty( level.level ) || ! _.isEmpty( level.id ) ) {
                                        _errorDetected_( 'validation error => the root level should not have a "level" or an "id" property' );
                                        return;
                                  }
                                  _.each( valCandidate.collection, function( _l_ ) {
                                        parentLevel = level;
                                        _checkWalker_( _l_ );
                                  });
                            }
                      } else {
                            if ( _.isEmpty( level.id ) || ! _.isString( level.id )) {
                                  _errorDetected_('validation error => a ' + level.level + ' level must have a valid id' );
                                  return;
                            } else if ( _.contains( levelIds, level.id ) ) {
                                  _errorDetected_('validation error => duplicated level id : ' + level.id );
                                  return;
                            } else {
                                  levelIds.push( level.id );
                            }
                            if ( _.isEmpty( level.level ) || ! _.isString( level.level ) ) {
                                  _errorDetected_('validation error => a ' + level.level + ' level must have a level property' );
                                  return;
                            } else if ( ! _.contains( [ 'location', 'section', 'column', 'module' ], level.level ) ) {
                                  _errorDetected_('validation error => the level "' + level.level + '" is not authorized' );
                                  return;
                            }
                            if ( 'module' == level.level ) {
                                  if ( ! _.isUndefined( level.collection ) ) {
                                        _errorDetected_('validation error => a module can not have a collection property' );
                                        return;
                                  }
                            } else {
                                  if ( _.isUndefined( level.collection ) ) {
                                        _errorDetected_( 'validation error => missing collection property for level => ' + level.level + ' ' + level.id );
                                        return;
                                  }
                            }
                            if ( _.isUndefined( level.ver_ini ) ) {
                                  api.errare( 'validateSettingValue() => validation error => a ' + level.level + ' should have a version property : "ver_ini"' );
                            }
                            switch ( level.level ) {
                                  case 'location' :
                                        if ( ! _.isEmpty( parentLevel.level ) ) {
                                              _errorDetected_('validation error => the parent of location ' + level.id +' should have no level set' );
                                              return;
                                        }
                                  break;

                                  case 'section' :
                                        if ( level.is_nested && 'column' != parentLevel.level ) {
                                              _errorDetected_('validation error => the nested section ' + level.id +' must be child of a column' );
                                              return;
                                        }
                                        if ( ! level.is_nested && 'location' != parentLevel.level ) {
                                              _errorDetected_('validation error => the section ' + level.id +' must be child of a location' );
                                              return;
                                        }
                                  break;

                                  case 'column' :
                                        if ( 'section' != parentLevel.level ) {
                                              _errorDetected_('validation error => the column ' + level.id +' must be child of a section' );
                                              return;
                                        }
                                  break;

                                  case 'module' :
                                        if ( 'column' != parentLevel.level ) {
                                              _errorDetected_('validation error => the module ' + level.id +' must be child of a column' );
                                              return;
                                        }
                                  break;
                            }
                            if ( 'module' != level.level ) {
                                  _.each( level.collection, function( _l_ ) {
                                        parentLevel = $.extend( true, {}, level );
                                        _checkWalker_( _l_ );
                                  });
                            }
                      }
                  };
                  _checkWalker_();
                  return errorDetected ? null : valCandidate;
            },//validateSettingValue
            resetCollectionSetting : function() {
                  var self = this;
                  if ( _.isEmpty( self.localSectionsSettingId() ) ) {
                        throw new Error( 'setupSettingsToBeSaved => the collectionSettingId is invalid' );
                  }
                  api( self.localSectionsSettingId() )( self.getDefaultSektionSettingValue( 'local' ) );
                  api.previewer.refresh();
                  api.notifications.remove( 'sek-notify' );
                  api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                        api.notifications.add( new api.Notification( 'sek-reset-done', {
                              type: 'success',
                              message: sektionsLocalizedData.i18n['Reset complete'],
                              dismissible: true
                        } ) );
                        _.delay( function() {
                              api.notifications.remove( 'sek-reset-done' );
                        }, 5000 );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            reactToPreviewMsg : function() {
                  var self = this,
                      apiParams = {},
                      uiParams = {},
                      sendToPreview = true, //<= the default behaviour is to send a message to the preview when the setting has been changed
                      msgCollection = {
                            'sek-add-section' : {
                                  callback : function( params ) {
                                        sendToPreview = ! _.isUndefined( params.send_to_preview ) ? params.send_to_preview : true;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-add-section',
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              location : params.location,
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                              before_section : params.before_section,
                                              after_section : params.after_section,
                                              is_first_section : params.is_first_section
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        if ( params.apiParams.is_first_section ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.location
                                              });
                                        }
                                        api.previewer.trigger( 'sek-pick-content', {
                                              id : params.apiParams ? params.apiParams.id : '',
                                              content_type : 'section'
                                        });
                                        api.previewer.send('sek-animate-to-level', { id : params.apiParams.id });
                                  }
                            },


                            'sek-add-column' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              action : 'sek-add-column',
                                              in_sektion : params.in_sektion,
                                              autofocus : params.autofocus
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        if ( false !== params.apiParams.autofocus ) {
                                              api.previewer.trigger( 'sek-pick-content', {});
                                        }
                                  }
                            },
                            'sek-add-module' : {
                                  callback :function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              action : 'sek-add-module',
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              module_type : params.content_id,

                                              before_module : params.before_module,
                                              after_module : params.after_module
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-edit-module', {
                                              id : params.apiParams.id,
                                              level : 'module',
                                              in_sektion : params.apiParams.in_sektion,
                                              in_column : params.apiParams.in_column
                                        });
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              id : params.apiParams.in_column,
                                              location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )//<= send skope id to the preview so we can use it when ajaxing
                                        });
                                  }
                            },
                            'sek-remove' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                  var sektionToRemove = self.getLevelModel( params.id );
                                                  if ( 'no_match' === sektionToRemove ) {
                                                        api.errare( 'reactToPreviewMsg => sek-remove-section => no sektionToRemove matched' );
                                                        break;
                                                  }
                                                  apiParams = {
                                                        action : 'sek-remove-section',
                                                        id : params.id,
                                                        location : params.location,
                                                        in_sektion : params.in_sektion,
                                                        in_column : params.in_column,
                                                        is_nested : sektionToRemove.is_nested
                                                  };
                                              break;
                                              case 'column' :
                                                  apiParams = {
                                                        action : 'sek-remove-column',
                                                        id : params.id,
                                                        in_sektion : params.in_sektion
                                                  };
                                              break;
                                              case 'module' :
                                                  apiParams = {
                                                        action : 'sek-remove-module',
                                                        id : params.id,
                                                        in_sektion : params.in_sektion,
                                                        in_column : params.in_column
                                                  };
                                              break;
                                              default :
                                                  api.errare( '::reactToPreviewMsg => sek-remove => missing level ', params );
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-pick-content', {});
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });
                                        if ( 'sek-remove-section' === params.apiParams.action ) {
                                              var locationLevel = self.getLevelModel( params.apiParams.location );
                                              if ( _.isEmpty( locationLevel.collection ) ) {
                                                    api.previewer.trigger( 'sek-refresh-level', {
                                                          level : 'location',
                                                          id :  params.apiParams.location
                                                    });
                                              }
                                        }
                                  }
                            },

                            'sek-move' : {
                                  callback  : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                    apiParams = {
                                                          action : 'sek-move-section',
                                                          id : params.id,
                                                          is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                                          newOrder : params.newOrder,
                                                          from_location : params.from_location,
                                                          to_location : params.to_location
                                                    };
                                              break;
                                              case 'column' :
                                                    apiParams = {
                                                          action : 'sek-move-column',
                                                          id : params.id,
                                                          newOrder : params.newOrder,
                                                          from_sektion : params.from_sektion,
                                                          to_sektion : params.to_sektion,
                                                    };
                                              break;
                                              case 'module' :
                                                    apiParams = {
                                                          action : 'sek-move-module',
                                                          id : params.id,
                                                          newOrder : params.newOrder,
                                                          from_column : params.from_column,
                                                          to_column : params.to_column,
                                                          from_sektion : params.from_sektion,
                                                          to_sektion : params.to_sektion,
                                                    };
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.apiParams.action ) {
                                              case 'sek-move-section' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'section',
                                                          in_sektion : params.apiParams.id
                                                    });
                                                    if ( params.apiParams.from_location != params.apiParams.to_location ) {
                                                          api.previewer.trigger( 'sek-refresh-level', {
                                                                level : 'location',
                                                                id :  params.apiParams.to_location
                                                          });
                                                          api.previewer.trigger( 'sek-refresh-level', {
                                                                level : 'location',
                                                                id :  params.apiParams.from_location
                                                          });
                                                    }
                                              break;
                                              case 'sek-move-column' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'column',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                              case 'sek-refresh-modules-in-column' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          id : params.apiParams.id,
                                                          level : 'module',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                        }
                                  }
                            },//sek-move


                            'sek-move-section-up' : {
                                  callback  : function( params ) {
                                        sendToPreview = false;
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-move-section-up-down',
                                              direction : 'up',
                                              id : params.id,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                              location : params.location
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-refresh-level', {
                                              level : 'location',
                                              id :  params.apiParams.location
                                        });
                                  }
                            },

                            'sek-move-section-down' : {
                                  callback  : function( params ) {
                                        sendToPreview = false;
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-move-section-up-down',
                                              direction : 'down',
                                              id : params.id,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                              location : params.location
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-refresh-level', {
                                              level : 'location',
                                              id :  params.apiParams.location
                                        });
                                  }
                            },
                            'sek-duplicate' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-section',
                                                          id : params.id,
                                                          location : params.location,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column,
                                                          is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column )
                                                    };
                                              break;
                                              case 'column' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-column',
                                                          id : params.id,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column
                                                    };
                                              break;
                                              case 'module' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-module',
                                                          id : params.id,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column
                                                    };
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        var idForStyleSheetRefresh;
                                        switch( params.apiParams.action ) {
                                              case 'sek-duplicate-section' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'section',
                                                          in_sektion : params.apiParams.id
                                                    });
                                                    idForStyleSheetRefresh = params.apiParams.location;
                                                    api.previewer.send('sek-animate-to-level', { id : params.apiParams.id });
                                              break;
                                              case 'sek-duplicate-column' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'column',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                                    idForStyleSheetRefresh = params.apiParams.in_sektion;
                                              break;
                                              case 'sek-duplicate-module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          id : params.apiParams.id,
                                                          level : 'module',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                                    idForStyleSheetRefresh = params.apiParams.in_column;
                                              break;
                                        }
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              id : idForStyleSheetRefresh,
                                              location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )//<= send skope id to the preview so we can use it when ajaxing
                                        });

                                  }
                            },
                            'sek-resize-columns' : function( params ) {
                                  sendToPreview = true;
                                  uiParams = {};
                                  apiParams = params;
                                  return self.updateAPISetting( apiParams );
                            },
                            'sek-add-content-in-new-sektion' : {
                                  callback : function( params ) {
                                        sendToPreview = ! _.isUndefined( params.send_to_preview ) ? params.send_to_preview : true;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = params;
                                        apiParams.action = 'sek-add-content-in-new-sektion';
                                        apiParams.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                        switch( params.content_type) {
                                              case 'module' :
                                                    apiParams.droppedModuleId = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                              break;
                                              case 'preset_section' :
                                                    api.previewer.send( 'sek-maybe-print-loader', { loader_located_in_level_id : params.location });
                                                    api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true });
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.apiParams.content_type) {
                                              case 'module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          level : 'module',
                                                          id : params.apiParams.droppedModuleId
                                                    });
                                              break;
                                              case 'preset_section' :
                                                    api.previewer.send( 'sek-clean-loader', { cleanFullPageLoader : true });
                                              break;
                                        }
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });
                                        var location_skope_id = params.location_skope_id;
                                        if ( _.isUndefined( location_skope_id ) ) {
                                              location_skope_id = true === params.is_global_location ? sektionsLocalizedData.globalSkopeId : api.czr_skopeBase.getSkopeProperty( 'skope_id' );
                                        }
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              location_skope_id : location_skope_id,//<= send skope id to the preview so we can use it when ajaxing
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });
                                        if ( params.apiParams.is_first_section ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.location
                                              });
                                        }
                                        if ( params.apiParams.sektion_to_replace ) {
                                              api.previewer.trigger( 'sek-remove', {
                                                    id : params.apiParams.sektion_to_replace,
                                                    location : params.apiParams.location,
                                                    in_column : params.apiParams.in_column,//needed when removing a nested column
                                                    level : 'section'
                                              });
                                        }
                                  }
                            },
                            'sek-add-preset-section-in-new-nested-sektion' : {
                                  callback : function( params ) {
                                        sendToPreview = false;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = params;
                                        apiParams.action = 'sek-add-preset-section-in-new-nested-sektion';
                                        apiParams.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                        api.previewer.send( 'sek-maybe-print-loader', { loader_located_in_level_id : params.location });
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger('sek-refresh-stylesheet', {
                                              id : params.apiParams.in_sektion,
                                              location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' )//<= send skope id to the preview so we can use it when ajaxing
                                        });
                                        self.updateAPISetting({
                                              action : 'sek-update-fonts',
                                              is_global_location : self.isGlobalLocation( params.apiParams )
                                        });

                                        api.previewer.trigger( 'sek-refresh-level', {
                                              level : 'section',
                                              id :  params.apiParams.in_sektion
                                        });
                                  }
                            },
                            'sek-pick-content' : function( params ) {
                                  params = _.isObject(params) ? params : {};
                                  api.czr_sektions.currentContentPickerType = api.czr_sektions.currentContentPickerType || new api.Value();
                                  api.czr_sektions.currentContentPickerType( params.content_type || 'module' );
                                  if ( _.isObject( params ) && params.id ) {
                                        self.lastClickedTargetInPreview( { id : params.id } );
                                  }

                                  params = params || {};
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : params.content_type || 'module',
                                        was_triggered : _.has( params, 'was_triggered' ) ? params.was_triggered : true,
                                        focus : _.has( params, 'focus' ) ? params.focus : true
                                  };
                                  return self.generateUI( uiParams );
                            },

                            'sek-edit-options' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  if ( _.isEmpty( params.id ) ) {
                                        return $.Deferred( function() {
                                              this.reject( 'missing id' );
                                        });
                                  }
                                  uiParams = {
                                        action : 'sek-generate-level-options-ui',
                                        level : params.level,
                                        id : params.id,
                                        in_sektion : params.in_sektion,
                                        in_column : params.in_column,
                                        options : params.options || []
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-edit-module' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-module-ui',
                                        level : params.level,
                                        id : params.id,
                                        in_sektion : params.in_sektion,
                                        in_column : params.in_column,
                                        options : params.options || []
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-notify' : function( params ) {
                                  sendToPreview = false;
                                  return $.Deferred(function() {
                                        api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                              api.notifications.add( new api.Notification( 'sek-notify', {
                                                    type: params.type || 'info',
                                                    message:  params.message,
                                                    dismissible: true
                                              } ) );
                                              _.delay( function() {
                                                    api.notifications.remove( 'sek-notify' );
                                              }, params.duration || 5000 );
                                        });
                                        this.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            },

                            'sek-refresh-level' : function( params ) {
                                  sendToPreview = true;
                                  return $.Deferred(function(_dfd_) {
                                        apiParams = {
                                              action : 'sek-refresh-level',
                                              level : params.level,
                                              id : params.id
                                        };
                                        uiParams = {};
                                        _dfd_.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            },

                            'sek-refresh-stylesheet' : function( params ) {
                                  sendToPreview = true;
                                  params = params || {};
                                  return $.Deferred(function(_dfd_) {
                                        apiParams = {id : params.id};
                                        uiParams = {};
                                        _dfd_.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            },

                            'sek-toggle-save-section-ui' : function( params ) {
                                  sendToPreview = false;
                                  self.saveUIVisible( true, params );
                                  return $.Deferred(function(_dfd_) {
                                        apiParams = {
                                        };
                                        uiParams = {};
                                        _dfd_.resolve({
                                              is_global_location : self.isGlobalLocation( params )
                                        });
                                  });
                            }
                      };//msgCollection
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.previewer.bind( msgId, function( params ) {
                              var _cb_;
                              if ( _.isFunction( callbackFn ) ) {
                                    _cb_ = callbackFn;
                              } else if ( _.isFunction( callbackFn.callback ) ) {
                                    _cb_ = callbackFn.callback;
                              } else {
                                   api.errare( '::reactToPreviewMsg => invalid callback for action ' + msgId );
                                   return;
                              }

                              try { _cb_( params )
                                    .done( function( promiseParams ) {
                                          promiseParams = promiseParams || {};
                                          if ( sendToPreview ) {
                                                api.previewer.send(
                                                      msgId,
                                                      {
                                                            location_skope_id : true === promiseParams.is_global_location ? sektionsLocalizedData.globalSkopeId : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                            local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                                                            apiParams : apiParams,
                                                            uiParams : uiParams,
                                                            cloneId : ! _.isEmpty( promiseParams.cloneId ) ? promiseParams.cloneId : false
                                                      }
                                                );
                                          } else {
                                                api.previewer.trigger( [ msgId, 'done' ].join('_'), { apiParams : apiParams, uiParams : uiParams } );
                                          }
                                          self.trigger( [ msgId, 'done' ].join('_'), params );
                                    })
                                    .fail( function( er ) {
                                          api.errare( 'reactToPreviewMsg => error when firing ' + msgId, er );
                                          api.previewer.trigger('sek-notify', {
                                                type : 'error',
                                                duration : 30000,
                                                message : [
                                                      '<span style="font-size:0.95em">',
                                                        '<strong>' + er + '</strong>',
                                                        '<br>',
                                                        sektionsLocalizedData.i18n['If this problem locks the Nimble builder, you might try to reset the sections for this page.'],
                                                        '<br>',
                                                        '<span style="text-align:center;display:block">',
                                                          '<button type="button" class="button" aria-label="' + sektionsLocalizedData.i18n.Reset + '" data-sek-reset="true">' + sektionsLocalizedData.i18n.Reset + '</button>',
                                                        '</span>',
                                                      '</span>'
                                                ].join('')

                                          });
                                    }); } catch( _er_ ) {
                                          api.errare( 'reactToPreviewMsg => error when receiving ' + msgId, _er_ );
                                    }
                          });
                  });
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.previewer.bind( [ msgId, 'done' ].join('_'), function( params ) {
                              if ( _.isFunction( callbackFn.complete ) ) {
                                    try { callbackFn.complete( params ); } catch( _er_ ) {
                                          api.errare( 'reactToPreviewMsg done => error when receiving ' + [msgId, 'done'].join('_') , _er_ );
                                    }
                              }
                        });
                  });
            },//reactToPreview();
            schedulePrintSectionJson : function() {
                  var self = this;
                  var popupCenter = function ( content ) {
                        w = 400;
                        h = 300;
                        var dualScreenLeft = ! _.isUndefined( window.screenLeft ) ? window.screenLeft : window.screenX;
                        var dualScreenTop = ! _.isUndefined( window.screenTop ) ? window.screenTop : window.screenY;

                        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
                        var top = ((height / 2) - (h / 2)) + dualScreenTop;
                        var newWindow = window.open("about:blank", null, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
                        var doc = newWindow.document;
                        doc.open("text/html");
                        doc.write( content );
                        doc.close();
                        if (window.focus) {
                            newWindow.focus();
                        }
                  };

                  api.previewer.bind( 'sek-to-json', function( params ) {
                        var sectionModel = $.extend( true, {}, self.getLevelModel( params.id ) );
                        console.log( JSON.stringify( self.cleanIds( sectionModel ) ) );
                  });
            }//schedulePrintSectionJson
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            generateUI : function( params ) {
                  var self = this,
                      dfd = $.Deferred();

                  if ( _.isEmpty( params.action ) ) {
                        dfd.reject( 'generateUI => missing action' );
                  }
                  switch ( params.action ) {
                        case 'sek-generate-module-ui' :
                              try{ dfd = self.generateUIforFrontModules( params, dfd ); } catch( er ) {
                                    api.errare( '::generateUI() => error', er );
                                    dfd = $.Deferred();
                              }
                        break;

                        case 'sek-generate-level-options-ui' :
                              try{ dfd = self.generateUIforLevelOptions( params, dfd ); } catch( er ) {
                                    api.errare( '::generateUI() => error', er );
                                    dfd = $.Deferred();
                              }
                        break;
                        case 'sek-generate-draggable-candidates-picker-ui' :
                              self.cleanRegistered();
                              try{ dfd = self.generateUIforDraggableContent( params, dfd ); } catch( er ) {
                                    api.errare( '::generateUI() => error', er );
                                    dfd = $.Deferred();
                              }
                        break;
                        case 'sek-generate-local-skope-options-ui' :
                              self.cleanRegistered();
                              try{ dfd = self.generateUIforLocalSkopeOptions( params, dfd ); } catch( er ) {
                                    api.errare( '::generateUI() => error', er );
                                    dfd = $.Deferred();
                              }
                        break;
                        case 'sek-generate-global-options-ui' :
                              self.cleanRegistered();
                              try{ dfd = self.generateUIforGlobalOptions( params, dfd ); } catch( er ) {
                                    api.errare( '::generateUI() => error', er );
                                    dfd = $.Deferred();
                              }
                        break;
                  }//switch

                  return 'pending' == dfd.state() ? dfd.resolve().promise() : dfd.promise();//<= we might want to resolve on focus.completeCallback ?
            },//generateUI()
            updateAPISettingAndExecutePreviewActions : function( params ) {
                  if ( _.isEmpty( params.settingParams ) || ! _.has( params.settingParams, 'to' ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing params.settingParams.to. The api main setting can not be updated', params );
                        return;
                  }
                  var self = this;
                  var rawModuleValue = params.settingParams.to,
                      moduleValueCandidate,// {} or [] if mono item of multi-item module
                      parentModuleType = null,
                      isMultiItemModule = false;

                  if ( _.isEmpty( params.settingParams.args ) || ! _.has( params.settingParams.args, 'moduleRegistrationParams' ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing params.settingParams.args.moduleRegistrationParams The api main setting can not be updated', params );
                        return;
                  }

                  var _ctrl_ = params.settingParams.args.moduleRegistrationParams.control,
                      _module_id_ = params.settingParams.args.moduleRegistrationParams.id,
                      parentModuleInstance = _ctrl_.czr_Module( _module_id_ );

                  if ( ! _.isEmpty( parentModuleInstance ) ) {
                        parentModuleType = parentModuleInstance.module_type;
                        isMultiItemModule = parentModuleInstance.isMultiItem();
                  } else {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing parentModuleInstance', params );
                  }
                  if ( ! isMultiItemModule && _.isObject( rawModuleValue ) ) {
                        moduleValueCandidate = self.normalizeAndSanitizeSingleItemInputValues( rawModuleValue, parentModuleType );
                  } else {
                        moduleValueCandidate = [];
                        _.each( rawModuleValue, function( item ) {
                              moduleValueCandidate.push( self.normalizeAndSanitizeSingleItemInputValues( item, parentModuleType ) );
                        });
                  }
                  if ( _.isEmpty( params.defaultPreviewAction ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing defaultPreviewAction in passed params. No action can be triggered to the api.previewer.', params );
                        return;
                  }
                  var refresh_stylesheet = 'refresh_stylesheet' === params.defaultPreviewAction,//<= default action for level options
                      refresh_markup = 'refresh_markup' === params.defaultPreviewAction,//<= default action for module options
                      refresh_fonts = 'refresh_fonts' === params.defaultPreviewAction,
                      refresh_preview = 'refresh_preview' === params.defaultPreviewAction;
                  var input_id = params.settingParams.args.input_changed;
                  var inputRegistrationParams;
                  if ( ! _.isUndefined( input_id ) ) {
                        inputRegistrationParams = self.getInputRegistrationParams( input_id, parentModuleType );
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_stylesheet ) ) {
                              refresh_stylesheet = Boolean( inputRegistrationParams.refresh_stylesheet );
                        }
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_markup ) ) {
                              refresh_markup = Boolean( inputRegistrationParams.refresh_markup );
                        }
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_fonts ) ) {
                              refresh_fonts = Boolean( inputRegistrationParams.refresh_fonts );
                        }
                        if ( ! _.isUndefined( inputRegistrationParams.refresh_preview ) ) {
                              refresh_preview = Boolean( inputRegistrationParams.refresh_preview );
                        }
                  }

                  var _doUpdateWithRequestedAction = function() {
                        if ( true === params.isGlobalOptions ) {
                              if ( _.isEmpty( params.options_type ) ) {
                                    api.errare( 'updateAPISettingAndExecutePreviewActions => error when updating the global options => missing options_type');
                                    return;
                              }
                              var rawGlobalOptions = api( sektionsLocalizedData.optNameForGlobalOptions )(),
                                  clonedGlobalOptions = $.extend( true, {}, _.isObject( rawGlobalOptions ) ? rawGlobalOptions : {} ),
                                  _valueCandidate = {};
                              _.each( moduleValueCandidate || {}, function( _val_, _key_ ) {
                                    if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                      return;
                                    _valueCandidate[ _key_ ] = _val_;
                              });

                              clonedGlobalOptions[ params.options_type ] = _valueCandidate;
                              api( sektionsLocalizedData.optNameForGlobalOptions )( clonedGlobalOptions );
                        } else {
                              return self.updateAPISetting({
                                    action : params.uiParams.action,// mandatory : 'sek-generate-level-options-ui', 'sek-generate-local-skope-options-ui',...
                                    id : params.uiParams.id,
                                    value : moduleValueCandidate,
                                    in_column : params.uiParams.in_column,//not mandatory
                                    in_sektion : params.uiParams.in_sektion,//not mandatory
                                    options_type : params.options_type,// mandatory : 'layout', 'spacing', 'bg_border', 'height', ...

                                    settingParams : params.settingParams
                              }).done( function( promiseParams ) {
                                    if ( true === refresh_stylesheet ) {
                                          api.previewer.send( 'sek-refresh-stylesheet', {
                                                location_skope_id : true === promiseParams.is_global_location ? sektionsLocalizedData.globalSkopeId : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                apiParams : {
                                                      action : 'sek-refresh-stylesheet',
                                                      id : params.uiParams.id,
                                                      level : params.uiParams.level
                                                },
                                          });
                                    }
                                    if ( true === refresh_markup ) {
                                          api.previewer.send( 'sek-refresh-level', {
                                                location_skope_id : true === promiseParams.is_global_location ? sektionsLocalizedData.globalSkopeId : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                apiParams : {
                                                      action : 'sek-refresh-level',
                                                      id : params.uiParams.id,
                                                      level : params.uiParams.level
                                                },
                                                skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                          });
                                    }
                                    if ( true === refresh_preview ) {
                                          api.previewer.refresh();
                                    }
                              });//self.updateAPISetting()
                        }
                  };//_doUpdateWithRequestedAction
                  if ( true === refresh_fonts ) {
                        var newFontFamily = params.settingParams.args.input_value;
                        if ( ! _.isString( newFontFamily ) ) {
                              api.errare( 'updateAPISettingAndExecutePreviewActions => font-family must be a string', newFontFamily );
                              return;
                        }
                        if ( newFontFamily.indexOf('gfont') > -1 ) {
                              self.updateAPISetting({
                                    action : 'sek-update-fonts',
                                    font_family : newFontFamily,
                                    is_global_location : self.isGlobalLocation( params.uiParams )
                              })
                              .always( function() {
                                    _doUpdateWithRequestedAction().then( function() {
                                          self.updateAPISetting({
                                                action : 'sek-update-fonts',
                                                is_global_location : self.isGlobalLocation( params.uiParams )
                                          });
                                    });
                              });
                        } else {
                             _doUpdateWithRequestedAction();
                        }
                  } else {
                        _doUpdateWithRequestedAction();
                  }
            },//updateAPISettingAndExecutePreviewActions
            normalizeAndSanitizeSingleItemInputValues : function( _item_, parentModuleType ) {
                  var itemNormalized = {},
                      itemNormalizedAndSanitized = {},
                      inputDefaultValue = null,
                      inputType = null,
                      sanitizedVal,
                      self = this,
                      isEqualToDefault = function( _val, _default ) {
                            var equal = false;
                            if ( _.isBoolean( _val ) || _.isBoolean( _default ) ) {
                                  equal = Boolean(_val) === Boolean(_default);
                            } else if ( _.isNumber( _val ) || _.isNumber( _default ) ) {
                                  equal = Number( _val ) === Number( _default );
                            } else if ( _.isString( _val ) || _.isString( _default ) ) {
                                  equal = _val+'' === _default+'';
                            } else if ( _.isObject( _val ) && _.isObject( _default ) ) {
                                  equal = _.isEqual( _val,_default );
                            } else if ( _.isArray( _val ) && _.isArray( _default ) ) {
                                  equal = JSON.stringify(_val.sort()) === JSON.stringify(_default.sort());
                            } else {
                                  equal = _val === _default;
                            }
                            return equal;
                      };
                  _.each( _item_, function( _val, input_id ) {
                        if ( _.contains( ['title', 'id' ], input_id ) )
                          return;

                        if ( null !== parentModuleType ) {
                              inputDefaultValue = self.getInputDefaultValue( input_id, parentModuleType );
                              if ( 'no_default_value_specified' === inputDefaultValue ) {
                                    api.infoLog( '::updateAPISettingAndExecutePreviewActions => missing default value for input ' + input_id + ' in module ' + parentModuleType );
                              }
                        }
                        if ( isEqualToDefault( _val, inputDefaultValue ) ) {
                              return;
                        } else if ( ( _.isString( _val ) || _.isObject( _val ) ) && _.isEmpty( _val ) ) {
                              return;
                        } else {
                              itemNormalized[ input_id ] = _val;
                        }
                  });
                  _.each( itemNormalized, function( _val, input_id ) {
                        switch( self.getInputType( input_id, parentModuleType ) ) {
                              case 'text' :
                              case 'textarea' :
                              case 'check' :
                              case 'gutencheck' :
                              case 'select' :
                              case 'radio' :
                              case 'number' :
                              case 'upload' :
                              case 'upload_url' :
                              case 'color' :
                              case 'wp_color_alpha' :
                              case 'wp_color' :
                              case 'content_picker' :
                              case 'tiny_mce_editor' :
                              case 'password' :
                              case 'range' :
                              case 'range_slider' :
                              case 'hidden' :
                              case 'h_alignment' :
                              case 'h_text_alignment' :

                              case 'spacing' :
                              case 'bg_position' :
                              case 'v_alignment' :
                              case 'font_size' :
                              case 'line_height' :
                              case 'font_picker' :
                                  sanitizedVal = _val;
                              break;
                              default :
                                  sanitizedVal = _val;
                              break;
                        }

                        itemNormalizedAndSanitized[ input_id ] = sanitizedVal;
                  });
                  return itemNormalizedAndSanitized;
            },
            isUIControlAlreadyRegistered : function( uiElementId ) {
                  var self = this,
                      uiCandidate = _.filter( self.registered(), function( registered ) {
                            return registered.id == uiElementId && 'control' === registered.what;
                      }),
                      controlIsAlreadyRegistered = false;
                  if ( _.isEmpty( uiCandidate ) ) {
                        controlIsAlreadyRegistered = api.control.has( uiElementId );
                  } else {
                        controlIsAlreadyRegistered = true;
                        if ( uiCandidate.length > 1 ) {
                              api.errare( 'generateUI => why is this control registered more than once ? => ' + uiElementId );
                        }
                  }
                  return controlIsAlreadyRegistered;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            generateUIforDraggableContent : function( params, dfd ) {
                  var self = this;
                  var registrationParams = {};

                  $.extend( registrationParams, {
                        content_type_switcher : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + '_sek_content_type_switcher_ui',
                              module_type : 'sek_content_type_switcher_module',
                              controlLabel :  sektionsLocalizedData.i18n['Select a content type'],
                              priority : 0,
                              settingValue : { content_type : params.content_type }
                        },
                        module_picker : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + '_sek_draggable_modules_ui',
                              module_type : 'sek_module_picker_module',
                              controlLabel : sektionsLocalizedData.i18n['Pick a module'],
                              content_type : 'module',
                              priority : 20,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },

                        sek_intro_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_intro_sec_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Sections for an introduction'],
                              content_type : 'section',
                              expandAndFocusOnInit : true,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },
                        sek_features_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_features_sec_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Sections for services and features'],
                              content_type : 'section',
                              expandAndFocusOnInit : false,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },
                        sek_contact_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_contact_sec_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Contact-us sections'],
                              content_type : 'section',
                              expandAndFocusOnInit : false,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },
                        sek_column_layouts_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_column_layouts_sec_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Empty sections with columns layout'],
                              content_type : 'section',
                              expandAndFocusOnInit : false,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        }
                  });
                  if ( sektionsLocalizedData.isNimbleHeaderFooterEnabled ) {
                        $.extend( registrationParams, {
                              sek_header_sec_picker_module : {
                                    settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                                    module_type : 'sek_header_sec_picker_module',
                                    controlLabel : sektionsLocalizedData.i18n['Header sections'],// sektionsLocalizedData.i18n['Header sections'],
                                    content_type : 'section',
                                    expandAndFocusOnInit : false,
                                    priority : 10,
                                    icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                              },
                              sek_footer_sec_picker_module : {
                                    settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                                    module_type : 'sek_footer_sec_picker_module',
                                    controlLabel : sektionsLocalizedData.i18n['Footer sections'],// sektionsLocalizedData.i18n['Header sections'],
                                    content_type : 'section',
                                    expandAndFocusOnInit : false,
                                    priority : 10,
                                    icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                              }
                        });
                  }

                  if ( sektionsLocalizedData.isSavedSectionEnabled ) {
                        $.extend( registrationParams, {
                              sek_my_sections_sec_picker_module : {
                                    settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                                    module_type : 'sek_my_sections_sec_picker_module',
                                    controlLabel :  '@missi18n My sections',
                                    content_type : 'section',
                                    expandAndFocusOnInit : false,
                                    priority : 10,
                                    icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                              }
                        });
                  }
                  var firstKey = _.keys( registrationParams )[0],
                      firstControlId = registrationParams[firstKey].settingControlId;

                  if ( self.isUIControlAlreadyRegistered( firstControlId ) ) {
                        api.control( firstControlId, function( _control_ ) {
                              _control_.focus({
                                    completeCallback : function() {
                                          var $container = _control_.container;
                                          if ( $container.hasClass( 'button-see-me') )
                                            return;
                                          $container.addClass('button-see-me');
                                          _.delay( function() {
                                               $container.removeClass('button-see-me');
                                          }, 800 );
                                    }
                              });
                        });

                        return dfd;
                  }//if
                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( function( to, from ) {
                                                api.errare('generateUIforDraggableContent => the setting() should not changed');
                                          });
                                    });
                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : optionData.settingValue || {},
                                          transport : 'postMessage',// 'refresh',
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                    });
                              }

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'control',
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : self.SECTION_ID_FOR_CONTENT_PICKER,
                                    priority : optionData.priority || 10,
                                    settings : { default : optionData.settingControlId },
                                    track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                              }).done( function() {
                                    api.control( optionData.settingControlId, function( _control_ ) {
                                          _control_.content_type = optionData.content_type;//<= used to handle visibility when switching content type with the "content_type_switcher" control
                                          if ( true === params.focus ) {
                                                _control_.focus({
                                                      completeCallback : function() {}
                                                });
                                          }

                                          var $title = _control_.container.find('label > .customize-control-title'),
                                              _titleContent = $title.html();
                                          $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );
                                          if ( ! _.isUndefined( optionData.icon ) ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                          }
                                          if ( 'section' === _control_.content_type ) {
                                                _control_.container.find('.czr-items-wrapper').hide();
                                                $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                                _control_.container.attr('data-sek-expanded', "false" );
                                                if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                                      _control_.container.find('.czr-items-wrapper').show();
                                                      $title.trigger('click');
                                                }
                                          } else {
                                                _control_.container.attr('data-sek-accordion', 'no');
                                          }

                                    });
                              });
                        });//_.each
                  };//_do_register_
                  api.section( self.SECTION_ID_FOR_CONTENT_PICKER, function( _section_ ) {
                        _do_register_();
                        var $sectionTitleEl = _section_.container.find('.accordion-section-title'),
                            $panelTitleEl = _section_.container.find('.customize-section-title h3');
                        if ( 0 < $sectionTitleEl.length && $sectionTitleEl.find('.sek-level-option-icon').length < 1 ) {
                              $sectionTitleEl.prepend( '<i class="fas fa-grip-vertical sek-level-option-icon"></i>' );
                        }
                        if ( 0 < $panelTitleEl.length && $panelTitleEl.find('.sek-level-option-icon').length < 1 ) {
                              $panelTitleEl.find('.customize-action').after( '<i class="fas fa-grip-vertical sek-level-option-icon"></i>' );
                        }
                        self.scheduleModuleAccordion.call( _section_, { expand_first_control : true } );
                        self._maybeFetchSectionsFromServer();
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            generateUIforFrontModules : function( params, dfd ) {
                  var self = this;
                  if ( _.isEmpty( params.id ) ) {
                        dfd.reject( 'generateUI => missing id' );
                  }
                  var moduleValue = self.getLevelProperty({
                        property : 'value',
                        id : params.id
                  });

                  var moduleType = self.getLevelProperty({
                        property : 'module_type',
                        id : params.id
                  });

                  var moduleName = self.getRegisteredModuleProperty( moduleType, 'name' );

                  if ( _.isEmpty( moduleType ) ) {
                        dfd.reject( 'generateUI => module => invalid module_type' );
                  }
                  var modulesRegistrationParams = {};

                  if ( true === self.getRegisteredModuleProperty( moduleType, 'is_father' ) ) {
                        var _childModules_ = self.getRegisteredModuleProperty( moduleType, 'children' );
                        if ( _.isEmpty( _childModules_ ) ) {
                              throw new Error('::generateUIforFrontModules => a father module ' + moduleType + ' is missing children modules ');
                        } else {
                              _.each( _childModules_, function( mod_type, optionType ){
                                    modulesRegistrationParams[ optionType ] = {
                                          settingControlId : params.id + '__' + optionType,
                                          module_type : mod_type,
                                          controlLabel : self.getRegisteredModuleProperty( mod_type, 'name' )
                                    };
                              });
                        }
                  } else {
                        modulesRegistrationParams.__no_option_group_to_be_updated_by_children_modules__ = {
                              settingControlId : params.id,
                              module_type : moduleType,
                              controlLabel : moduleName
                        };
                  }
                  var firstKey = _.keys( modulesRegistrationParams )[0],
                      firstControlId = modulesRegistrationParams[firstKey].settingControlId;

                  if ( self.isUIControlAlreadyRegistered( firstControlId ) ) {
                        api.control( firstControlId ).focus({
                              completeCallback : function() {
                                    var $container = api.control( firstControlId ).container;
                                    if ( $container.hasClass( 'button-see-me') )
                                      return;
                                    $container.addClass('button-see-me');
                                    _.delay( function() {
                                         $container.removeClass('button-see-me');
                                    }, 800 );
                              }
                        });
                        return dfd;
                  }//if
                  self.cleanRegistered();

                  _do_register_ = function() {
                        _.each( modulesRegistrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh_markup',
                                                uiParams : _.extend( params, { action : 'sek-set-module-value' } ),
                                                options_type : optionType,
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforFrontModules => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });

                                    var settingValueOnRegistration = $.extend( true, {}, moduleValue );
                                    if ( '__no_option_group_to_be_updated_by_children_modules__' !== optionType ) {
                                          settingValueOnRegistration = ( !_.isEmpty( settingValueOnRegistration ) && _.isObject( settingValueOnRegistration ) && _.isObject( settingValueOnRegistration[optionType] ) ) ? settingValueOnRegistration[optionType] : {};
                                    }
                                    api.CZR_Helpers.register({
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : settingValueOnRegistration,
                                          transport : 'postMessage',// 'refresh',
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                    });
                              }//if ( ! api.has( optionData.settingControlId ) )


                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'control',
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : params.id,
                                    priority : 10,
                                    settings : { default : optionData.settingControlId }
                              }).done( function() {});
                              api.control( optionData.settingControlId, function( _control_ ) {
                                    api.control( optionData.settingControlId ).focus({
                                          completeCallback : function() {}
                                    });
                                    _control_.container.find('.czr-items-wrapper').hide();
                                    var $title = _control_.container.find('label > .customize-control-title'),
                                        _titleContent = $title.html();

                                    $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );
                                    if ( ! _.isUndefined( optionData.icon ) ) {
                                          $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                    }
                                    $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                    _control_.container.attr('data-sek-expanded', "false" );
                              });
                        });//each()
                  };//_do_register()
                  api.section.when( params.id, function() {
                        api.section(params.id).focus();
                        _do_register_();
                  });
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : params.id,
                        title: sektionsLocalizedData.i18n['Content for'] + ' ' + moduleName,
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 1000,
                  }).done( function() {});

                  api.section( params.id, function( _section_ ) {
                        _section_.container.find('.accordion-section-title').first().hide();
                        var $panelTitleEl = _section_.container.find('.customize-section-title h3');
                        if ( 0 < $panelTitleEl.length ) {
                              $panelTitleEl.find('.customize-action').after( '<i class="fas fa-pencil-alt sek-level-option-icon"></i>' );
                        }
                        self.scheduleModuleAccordion.call( _section_, { expand_first_control : true } );
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            generateUIforLevelOptions : function( params, dfd ) {
                  var self = this;
                  var levelOptionValues = self.getLevelProperty({
                            property : 'options',
                            id : params.id
                      });
                  levelOptionValues = _.isObject( levelOptionValues ) ? levelOptionValues : {};
                  var modulesRegistrationParams = {};

                  $.extend( modulesRegistrationParams, {
                        bg : {
                              settingControlId : params.id + '__bg_options',
                              module_type : 'sek_level_bg_module',
                              controlLabel : sektionsLocalizedData.i18n['Background settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              expandAndFocusOnInit : true,
                              icon : '<i class="material-icons sek-level-option-icon">gradient</i>'//'<i class="material-icons sek-level-option-icon">brush</i>'
                        },
                        border : {
                              settingControlId : params.id + '__border_options',
                              module_type : 'sek_level_border_module',
                              controlLabel : sektionsLocalizedData.i18n['Borders settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="material-icons sek-level-option-icon">rounded_corner</i>'//'<i class="material-icons sek-level-option-icon">brush</i>'
                        },
                        spacing : {
                              settingControlId : params.id + '__spacing_options',
                              module_type : 'sek_level_spacing_module',
                              controlLabel : sektionsLocalizedData.i18n['Padding and margin settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="material-icons sek-level-option-icon">center_focus_weak</i>'
                        },
                        anchor : {
                              settingControlId : params.id + '__anchor_options',
                              module_type : 'sek_level_anchor_module',
                              controlLabel : sektionsLocalizedData.i18n['Set a custom anchor for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="fas fa-anchor sek-level-option-icon"></i>'
                        },
                        visibility : {
                              settingControlId : params.id + '__visibility_options',
                              module_type : 'sek_level_visibility_module',
                              controlLabel : sektionsLocalizedData.i18n['Device visibility settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="far fa-eye sek-level-option-icon"></i>'
                        },
                        height : {
                              settingControlId : params.id + '__height_options',
                              module_type : 'sek_level_height_module',
                              controlLabel : sektionsLocalizedData.i18n['Height settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                              icon : '<i class="fas fa-ruler-vertical sek-level-option-icon"></i>'
                        },
                  });

                  if ( 'section' === params.level ) {
                        $.extend( modulesRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_section',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                    icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                              }
                        });
                        $.extend( modulesRegistrationParams, {
                              breakpoint : {
                                    settingControlId : params.id + '__breakpoint_options',
                                    module_type : 'sek_level_breakpoint_module',
                                    controlLabel : sektionsLocalizedData.i18n['Responsive settings : breakpoint, column direction'],
                                    icon : '<i class="material-icons sek-level-option-icon">devices</i>'
                              }
                        });
                  }
                  if ( 'module' === params.level ) {
                        $.extend( modulesRegistrationParams, {
                              width : {
                                    settingControlId : params.id + '__width_options',
                                    module_type : 'sek_level_width_module',
                                    controlLabel : sektionsLocalizedData.i18n['Width settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                    icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                              }
                        });
                  }
                  var firstKey = _.keys( modulesRegistrationParams )[0],
                      firstControlId = modulesRegistrationParams[firstKey].settingControlId;

                  if ( self.isUIControlAlreadyRegistered( firstControlId ) ) {
                        api.control( firstControlId ).focus({
                              completeCallback : function() {
                                    var $container = api.control( firstControlId ).container;
                                    if ( $container.hasClass( 'button-see-me') )
                                      return;
                                    $container.addClass('button-see-me');
                                    _.delay( function() {
                                         $container.removeClass('button-see-me');
                                    }, 800 );
                              }
                        });
                        return dfd;
                  }//if
                  self.cleanRegistered();
                  _do_register_ = function() {
                        _.each( modulesRegistrationParams, function( optionData, optionType ){
                              if ( self.isUIControlAlreadyRegistered( optionData.settingControlId ) ) {
                                    api.section( api.control( optionData.settingControlId ).section() ).expanded( true );
                                    return;
                              }
                              if( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh_stylesheet',
                                                uiParams : params,
                                                options_type : optionType,// <= this is the options sub property where we will store this setting values. @see updateAPISetting case 'sek-generate-level-options-ui'
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforLevelOptions => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });//api( Id, function( _setting_ ) {})
                                    var initialModuleValues = levelOptionValues[ optionType ] || {};
                                    var startingModuleValue = self.getModuleStartingValue( optionData.module_type );
                                    if ( 'no_starting_value' !== startingModuleValue && _.isObject( startingModuleValue ) ) {
                                          var clonedStartingModuleValue = $.extend( true, {}, startingModuleValue );
                                          initialModuleValues = $.extend( clonedStartingModuleValue, initialModuleValues );
                                    }

                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : initialModuleValues,
                                          transport : 'postMessage',// 'refresh',
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option //sekData.settingType
                                    });
                              }//if( ! api.has( optionData.settingControlId ) ) {

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    level_id : params.id,
                                    what : 'control',
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : params.id,
                                    priority : 0,
                                    settings : { default : optionData.settingControlId }
                              }).done( function() {});
                              api.control( optionData.settingControlId, function( _control_ ) {
                                    if ( true === optionData.expandAndFocusOnInit ) {
                                          _control_.focus({
                                                completeCallback : function() {}
                                          });
                                    }
                                    _control_.container.find('.czr-items-wrapper').hide();
                                    var $title = _control_.container.find('label > .customize-control-title'),
                                        _titleContent = $title.html();
                                    $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );
                                    if ( ! _.isUndefined( optionData.icon ) ) {
                                          $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                    }
                                    $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                    _control_.container.attr('data-sek-expanded', "false" );
                              });
                        });//_.each()
                  };//_do_register_()
                  if ( ! api.section.has( params.id ) ) {
                        api.section( params.id, function( _section_ ) {
                              self.scheduleModuleAccordion.call( _section_, { expand_first_control : true } );
                        });
                  }

                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : params.id,
                        title: sektionsLocalizedData.i18n['Settings for the'] + ' ' + params.level,
                        panel : sektionsLocalizedData.sektionsPanelId,
                        priority : 10,
                  }).done( function() {});
                  api.section( params.id, function( _section_ ) {
                        _do_register_();
                        _section_.container.find('.accordion-section-title').first().hide();
                        var $panelTitleEl = _section_.container.find('.customize-section-title h3');
                        if ( 0 < $panelTitleEl.length && $panelTitleEl.find('.sek-level-option-icon').length < 1 ) {
                              $panelTitleEl.find('.customize-action').after( '<i class="fas fa-sliders-h sek-level-option-icon"></i>' );
                        }
                  });

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            getLocalSkopeOptionId : function() {
                  var skope_id = api.czr_skopeBase.getSkopeProperty( 'skope_id' );
                  if ( _.isEmpty( skope_id ) ) {
                        api.errare( 'czr_sektions::getLocalSkopeOptionId => empty skope_id ');
                        return '';
                  }
                  return sektionsLocalizedData.optPrefixForSektionsNotSaved + skope_id + '__localSkopeOptions';
            },
            generateUIforLocalSkopeOptions : function( params, dfd ) {
                  var self = this,
                      _id_ = self.getLocalSkopeOptionId();
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                        return dfd;
                  }
                  var registrationParams = {};
                  $.extend( registrationParams, {
                        template : {
                              settingControlId : _id_ + '__template',
                              module_type : 'sek_local_template',
                              controlLabel : sektionsLocalizedData.i18n['Page template'],
                              expandAndFocusOnInit : false,
                              icon : '<i class="material-icons sek-level-option-icon">check_box_outline_blank</i>'
                        }
                  });
                  if ( sektionsLocalizedData.isNimbleHeaderFooterEnabled ) {
                        $.extend( registrationParams, {
                              local_header_footer : {
                                    settingControlId : _id_ + '__local_header_footer',
                                    module_type : 'sek_local_header_footer',
                                    controlLabel : sektionsLocalizedData.i18n['Page header and footer'],
                                    icon : '<i class="material-icons sek-level-option-icon">web</i>'
                              }
                        });
                  }

                  $.extend( registrationParams, {
                        widths : {
                              settingControlId : _id_ + '__widths',
                              module_type : 'sek_local_widths',
                              controlLabel : sektionsLocalizedData.i18n['Inner and outer widths'],
                              icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                        },
                        custom_css : {
                              settingControlId : _id_ + '__custom_css',
                              module_type : 'sek_local_custom_css',
                              controlLabel : sektionsLocalizedData.i18n['Custom CSS'],
                              icon : '<i class="material-icons sek-level-option-icon">code</i>'
                        },
                        local_performances : {
                              settingControlId : _id_ + '__local_performances',
                              module_type : 'sek_local_performances',
                              controlLabel : sektionsLocalizedData.i18n['Page speed optimizations'],
                              icon : '<i class="fas fa-fighter-jet sek-level-option-icon"></i>'
                        },
                        local_reset : {
                              settingControlId : _id_ + '__local_reset',
                              module_type : 'sek_local_reset',
                              controlLabel : sektionsLocalizedData.i18n['Remove the sections in this page'],
                              icon : '<i class="material-icons sek-level-option-icon">cached</i>'
                        }
                  });

                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh',
                                                uiParams : params,
                                                options_type : optionType,
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforLocalSkopeOptions => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });//api( Id, function( _setting_ ) {})
                                    var startingModuleValue = self.getModuleStartingValue( optionData.module_type ),
                                        currentSetValue = api( self.localSectionsSettingId() )(),
                                        allSkopeOptions = $.extend( true, {}, _.isObject( currentSetValue.local_options ) ? currentSetValue.local_options : {} ),
                                        optionTypeValue = _.isObject( allSkopeOptions[ optionType ] ) ? allSkopeOptions[ optionType ]: {},
                                        initialModuleValues = optionTypeValue;

                                    if ( 'no_starting_value' !== startingModuleValue && _.isObject( startingModuleValue ) ) {
                                          var clonedStartingModuleValue = $.extend( true, {}, startingModuleValue );
                                          initialModuleValues = $.extend( clonedStartingModuleValue, initialModuleValues );
                                    }
                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : initialModuleValues,
                                          transport : 'postMessage',//'refresh',//// ,
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                    });
                              }

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'control',
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : self.SECTION_ID_FOR_LOCAL_OPTIONS,
                                    priority : 10,
                                    settings : { default : optionData.settingControlId },
                              }).done( function() {
                                    api.control( optionData.settingControlId, function( _control_ ) {
                                          _control_.container.find('.czr-items-wrapper').hide();
                                          var $title = _control_.container.find('label > .customize-control-title'),
                                              _titleContent = $title.html();
                                          $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );
                                          if ( ! _.isUndefined( optionData.icon ) ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                          }
                                          $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                          _control_.container.attr('data-sek-expanded', "false" );
                                          if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                                $title.trigger('click');
                                          }
                                    });
                              });
                        });//_.each()
                  };//_do_register()
                  _do_register_();

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            generateUIforGlobalOptions : function( params, dfd ) {
                  var self = this,
                      _id_ = sektionsLocalizedData.optPrefixForSektionsNotSaved + sektionsLocalizedData.optNameForGlobalOptions;
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                        return dfd;
                  }
                  var registrationParams = {};
                  if ( sektionsLocalizedData.isNimbleHeaderFooterEnabled ) {
                        $.extend( registrationParams, {
                              global_header_footer : {
                                    settingControlId : _id_ + '__header_footer',
                                    module_type : 'sek_global_header_footer',
                                    controlLabel : sektionsLocalizedData.i18n['Site wide header and footer'],
                                    icon : '<i class="material-icons sek-level-option-icon">web</i>'
                              }
                        });
                  }

                  $.extend( registrationParams, {
                        breakpoint : {
                              settingControlId : _id_ + '__breakpoint',
                              module_type : 'sek_global_breakpoint',
                              controlLabel : sektionsLocalizedData.i18n['Site wide breakpoint for Nimble sections'],
                              expandAndFocusOnInit : false,
                              icon : '<i class="material-icons sek-level-option-icon">devices</i>'
                        },
                        widths : {
                              settingControlId : _id_ + '__widths',
                              module_type : 'sek_global_widths',
                              controlLabel : sektionsLocalizedData.i18n['Site wide inner and outer sections widths'],
                              icon : '<i class="fas fa-ruler-horizontal sek-level-option-icon"></i>'
                        },
                        performances : {
                              settingControlId : _id_ + '__performances',
                              module_type : 'sek_global_performances',
                              controlLabel : sektionsLocalizedData.i18n['Site wide page speed optimizations'],
                              icon : '<i class="fas fa-fighter-jet sek-level-option-icon"></i>'
                        },
                        beta_features : {
                              settingControlId : _id_ + '__beta_features',
                              module_type : 'sek_global_beta_features',
                              controlLabel : sektionsLocalizedData.i18n['Beta features'],
                              icon : '<i class="material-icons">widgets</i>'
                        }
                  });

                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                isGlobalOptions : true,//<= indicates that we won't update the local skope setting id
                                                defaultPreviewAction : 'refresh',
                                                uiParams : params,
                                                options_type : optionType,
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforGlobalOptions => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });//api( Id, function( _setting_ ) {})
                                    var dbValues = sektionsLocalizedData.globalOptionDBValues,
                                        startingModuleValue = self.getModuleStartingValue( optionData.module_type ),
                                        initialModuleValues = ( _.isObject( dbValues ) && ! _.isEmpty( dbValues[ optionType ] ) ) ? dbValues[ optionType ] : {};

                                    if ( 'no_starting_value' !== startingModuleValue && _.isObject( startingModuleValue ) ) {
                                          var clonedStartingModuleValue = $.extend( true, {}, startingModuleValue );
                                          initialModuleValues = $.extend( clonedStartingModuleValue, initialModuleValues );
                                    }

                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : initialModuleValues,
                                          transport : 'postMessage',//'refresh',//// ,
                                          type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                    });
                              }

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'control',
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : self.SECTION_ID_FOR_GLOBAL_OPTIONS,//registered in ::initialize()
                                    priority : 20,
                                    settings : { default : optionData.settingControlId },
                                    track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                              }).done( function() {
                                    api.control( optionData.settingControlId, function( _control_ ) {
                                          _control_.container.find('.czr-items-wrapper').hide();
                                          var $title = _control_.container.find('label > .customize-control-title'),
                                              _titleContent = $title.html();
                                          $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );
                                          if ( ! _.isUndefined( optionData.icon ) ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                          }
                                          $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                          _control_.container.attr('data-sek-expanded', "false" );
                                          if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                                $title.trigger('click');
                                          }
                                    });
                              });
                        });//_.each();
                  };//do register
                  _do_register_();

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData, serverControlParams
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            updateAPISetting : function( params ) {

                  var self = this,
                      __updateAPISettingDeferred__ = $.Deferred();
                  params = params || {};
                  params.is_global_location = self.isGlobalLocation( params );

                  var _collectionSettingId_ = params.is_global_location ? self.getGlobalSectionsSettingId() : self.localSectionsSettingId();
                  api( _collectionSettingId_, function( sektionSetInstance ) {
                        var currentSetValue = sektionSetInstance(),
                            newSetValue = _.isObject( currentSetValue ) ? $.extend( true, {}, currentSetValue ) : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ),
                            locationCandidate,
                            sektionCandidate,
                            columnCandidate,
                            moduleCandidate,
                            originalCollection,
                            reorderedCollection,
                            cloneId, //will be passed in resolve()
                            startingModuleValue,// will be populated by the optional starting value specificied on module registration
                            __presetSectionInjected__ = false,
                            parentSektionCandidate;
                        newSetValue.collection = _.isArray( newSetValue.collection ) ? newSetValue.collection : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ).collection;

                        switch( params.action ) {
                              case 'sek-add-section' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    if ( _.isEmpty( params.location ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                                    }
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          parentSektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                          if ( 'no_match' == parentSektionCandidate ) {
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                                break;
                                          }
                                          if ( true === parentSektionCandidate.is_nested ) {
                                                __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ]);
                                                break;
                                          }
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.push({
                                                id : params.id,
                                                level : 'section',
                                                collection : [{
                                                      id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                      level : 'column',
                                                      collection : [],
                                                      ver_ini : sektionsLocalizedData.nimbleVersion
                                                }],
                                                is_nested : true,
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          });
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          _.each( locationCandidate.collection, function( secModel, index ) {
                                                if ( params.before_section === secModel.id ) {
                                                      position = index;
                                                }
                                                if ( params.after_section === secModel.id ) {
                                                      position = index + 1;
                                                }
                                          });
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          locationCandidate.collection.splice( position, 0, {
                                                id : params.id,
                                                level : 'section',
                                                collection : [{
                                                      id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                      level : 'column',
                                                      collection : [],
                                                      ver_ini : sektionsLocalizedData.nimbleVersion
                                                }],
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          });
                                    }
                              break;


                              case 'sek-duplicate-section' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    if ( _.isEmpty( params.location ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                                    }
                                    var deepClonedSektion;
                                    try { deepClonedSektion = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          break;
                                    }

                                    var _position_ = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }

                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );


                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          locationCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );

                                    }
                                    cloneId = deepClonedSektion.id;//will be passed in resolve()
                              break;
                              case 'sek-remove-section' :
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          if ( 'no_match' != columnCandidate ) {
                                                columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                                columnCandidate.collection = _.filter( columnCandidate.collection, function( col ) {
                                                      return col.id != params.id;
                                                });
                                          } else {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          }
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.filter( locationCandidate.collection, function( sek ) {
                                                return sek.id != params.id;
                                          });
                                    }
                              break;

                              case 'sek-move-section' :
                                    var toLocationCandidate = self.getLevelModel( params.to_location, newSetValue.collection ),
                                        movedSektionCandidate,
                                        copyOfMovedSektionCandidate;

                                    if ( _.isEmpty( toLocationCandidate ) || 'no_match' == toLocationCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target location' );
                                    }
                                    if ( params.from_location != params.to_location ) {
                                          var fromLocationCandidate = self.getLevelModel( params.from_location, newSetValue.collection );
                                          if ( _.isEmpty( fromLocationCandidate ) || 'no_match' == fromLocationCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source location' );
                                          }

                                          fromLocationCandidate.collection =  _.isArray( fromLocationCandidate.collection ) ? fromLocationCandidate.collection : [];
                                          movedSektionCandidate = self.getLevelModel( params.id, fromLocationCandidate.collection );
                                          copyOfMovedSektionCandidate = $.extend( true, {}, movedSektionCandidate );
                                          fromLocationCandidate.collection = _.filter( fromLocationCandidate.collection, function( sektion ) {
                                                return sektion.id != params.id;
                                          });
                                    }
                                    toLocationCandidate.collection = _.isArray( toLocationCandidate.collection ) ? toLocationCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toLocationCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          if ( params.from_location != params.to_location && _id_ == copyOfMovedSektionCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedSektionCandidate );
                                          } else {
                                                sektionCandidate = self.getLevelModel( _id_, originalCollection );
                                                if ( _.isEmpty( sektionCandidate ) || 'no_match' == sektionCandidate ) {
                                                      throw new Error( 'updateAPISetting => ' + params.action + ' => missing section candidate' );
                                                }
                                                reorderedCollection.push( sektionCandidate );
                                          }
                                    });
                                    toLocationCandidate.collection = reorderedCollection;

                              break;
                              case 'sek-move-section-up-down' :

                                    inLocationCandidate = self.getLevelModel( params.location, newSetValue.collection );

                                    if ( _.isEmpty( inLocationCandidate ) || 'no_match' == inLocationCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target location' );
                                    }
                                    inLocationCandidate.collection = _.isArray( inLocationCandidate.collection ) ? inLocationCandidate.collection : [];
                                    originalCollection = $.extend( true, [], inLocationCandidate.collection );
                                    reorderedCollection = $.extend( true, [], inLocationCandidate.collection );

                                    var _indexInOriginal = _.findIndex( originalCollection, function( _sec_ ) {
                                          return _sec_.id === params.id;
                                    });
                                    if ( -1 === _indexInOriginal ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => invalid index' );
                                    }
                                    var direction = params.direction || 'up';
                                    reorderedCollection[ _indexInOriginal ] = originalCollection[ 'up' === direction ? _indexInOriginal - 1 : _indexInOriginal + 1 ];
                                    reorderedCollection[ 'up' === direction ? _indexInOriginal - 1 : _indexInOriginal + 1 ] = originalCollection[ _indexInOriginal ];

                                    inLocationCandidate.collection = reorderedCollection;
                              break;
                              case 'sek-add-column' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == sektionCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }
                                    _.each( sektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });
                                    sektionCandidate.collection.push({
                                          id :  params.id,
                                          level : 'column',
                                          collection : [],
                                          ver_ini : sektionsLocalizedData.nimbleVersion
                                    });
                              break;


                              case 'sek-remove-column' :
                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' != sektionCandidate ) {
                                          if ( 1 === _.size( sektionCandidate.collection ) ) {
                                                __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n["A section must have at least one column."]);
                                                break;
                                          }
                                          sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                          sektionCandidate.collection = _.filter( sektionCandidate.collection, function( column ) {
                                                return column.id != params.id;
                                          });
                                          _.each( sektionCandidate.collection, function( colModel ) {
                                                colModel.width = '';
                                          });
                                    } else {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                    }

                              break;

                              case 'sek-duplicate-column' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == sektionCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }

                                    var deepClonedColumn;
                                    try { deepClonedColumn = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          break;
                                    }
                                    var _position = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    cloneId = deepClonedColumn.id;//will be passed in resolve()
                                    sektionCandidate.collection.splice( parseInt( _position + 1, 10 ), 0, deepClonedColumn );
                                    _.each( sektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });
                              break;
                              case 'sek-resize-columns' :
                                    if ( params.col_number < 2 )
                                      break;

                                    var resizedColumn = self.getLevelModel( params.resized_column, newSetValue.collection ),
                                        sistercolumn = self.getLevelModel( params.sister_column, newSetValue.collection );
                                    if ( 'no_match' == resizedColumn ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no resized column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no resized column matched');
                                          break;
                                    }

                                    resizedColumn.width = parseFloat( params.resizedColumnWidthInPercent );
                                    var parentSektion = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    var otherColumns = _.filter( parentSektion.collection, function( _col_ ) {
                                              return _col_.id != resizedColumn.id && _col_.id != sistercolumn.id;
                                        });
                                    var otherColumnsWidth = parseFloat( resizedColumn.width.toFixed(3) );

                                    if ( ! _.isEmpty( otherColumns ) ) {
                                         _.each( otherColumns, function( colModel ) {
                                                currentColWidth = parseFloat( colModel.width * 1 );
                                                if ( ! _.has( colModel, 'width') || ! _.isNumber( currentColWidth * 1 ) || _.isEmpty( currentColWidth + '' ) || 1 > currentColWidth ) {
                                                      colModel.width = parseFloat( ( 100 / params.col_number ).toFixed(3) );
                                                }
                                                otherColumnsWidth = parseFloat( ( otherColumnsWidth  +  colModel.width ).toFixed(3) );
                                          });
                                    }
                                    sistercolumn.width = parseFloat( ( 100 - otherColumnsWidth ).toFixed(3) );
                              break;




                              case 'sek-move-column' :
                                    var toSektionCandidate = self.getLevelModel( params.to_sektion, newSetValue.collection ),
                                        movedColumnCandidate,
                                        copyOfMovedColumnCandidate;

                                    if ( _.isEmpty( toSektionCandidate ) || 'no_match' == toSektionCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target sektion' );
                                    }

                                    if ( params.from_sektion != params.to_sektion ) {
                                          var fromSektionCandidate = self.getLevelModel( params.from_sektion, newSetValue.collection );
                                          if ( _.isEmpty( fromSektionCandidate ) || 'no_match' == fromSektionCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source column' );
                                          }

                                          fromSektionCandidate.collection =  _.isArray( fromSektionCandidate.collection ) ? fromSektionCandidate.collection : [];
                                          movedColumnCandidate = self.getLevelModel( params.id, fromSektionCandidate.collection );
                                          copyOfMovedColumnCandidate = $.extend( true, {}, movedColumnCandidate );
                                          fromSektionCandidate.collection = _.filter( fromSektionCandidate.collection, function( column ) {
                                                return column.id != params.id;
                                          });
                                          _.each( fromSektionCandidate.collection, function( colModel ) {
                                                colModel.width = '';
                                          });
                                    }
                                    toSektionCandidate.collection =  _.isArray( toSektionCandidate.collection ) ? toSektionCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toSektionCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          if ( params.from_sektion != params.to_sektion && _id_ == copyOfMovedColumnCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedColumnCandidate );
                                          } else {
                                                columnCandidate = self.getLevelModel( _id_, originalCollection );
                                                if ( _.isEmpty( columnCandidate ) || 'no_match' == columnCandidate ) {
                                                      throw new Error( 'updateAPISetting => moveColumn => missing columnCandidate' );
                                                }
                                                reorderedCollection.push( columnCandidate );
                                          }
                                    });
                                    toSektionCandidate.collection = reorderedCollection;
                                    _.each( toSektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });

                              break;
                              case 'sek-add-module' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    if ( _.isEmpty( params.module_type ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing module_type' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' === columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    var position = 0;
                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                    _.each( columnCandidate.collection, function( moduleModel, index ) {
                                          if ( params.before_module === moduleModel.id ) {
                                                position = index;
                                          }
                                          if ( params.after_module === moduleModel.id ) {
                                                position = index + 1;
                                          }
                                    });

                                    var _moduleParams = {
                                          id : params.id,
                                          level : 'module',
                                          module_type : params.module_type,
                                          ver_ini : sektionsLocalizedData.nimbleVersion
                                    };
                                    startingModuleValue = self.getModuleStartingValue( params.module_type );
                                    if ( 'no_starting_value' !== startingModuleValue ) {
                                          _moduleParams.value = startingModuleValue;
                                    }

                                    columnCandidate.collection.splice( position, 0, _moduleParams );
                              break;

                              case 'sek-duplicate-module' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' == columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                    var deepClonedModule;
                                    try { deepClonedModule = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => error when cloning the level');
                                          break;
                                    }
                                    var insertInposition = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    cloneId = deepClonedModule.id;//will be passed in resolve()
                                    columnCandidate.collection.splice( parseInt( insertInposition + 1, 10 ), 0, deepClonedModule );

                              break;

                              case 'sek-remove-module' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' != columnCandidate ) {
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection = _.filter( columnCandidate.collection, function( module ) {
                                                return module.id != params.id;
                                          });

                                    } else {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                    }
                              break;

                              case 'sek-move-module' :
                                    var toColumnCandidate,
                                        movedModuleCandidate,
                                        copyOfMovedModuleCandidate;
                                    toColumnCandidate = self.getLevelModel( params.to_column, newSetValue.collection );

                                    if ( _.isEmpty( toColumnCandidate ) || 'no_match' == toColumnCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target column' );
                                    }
                                    if ( params.from_column != params.to_column ) {
                                          var fromColumnCandidate;
                                          fromColumnCandidate = self.getLevelModel( params.from_column, newSetValue.collection );

                                          if ( _.isEmpty( fromColumnCandidate ) || 'no_match' == fromColumnCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source column' );
                                          }

                                          fromColumnCandidate.collection =  _.isArray( fromColumnCandidate.collection ) ? fromColumnCandidate.collection : [];
                                          movedModuleCandidate = self.getLevelModel( params.id, newSetValue.collection );
                                          copyOfMovedModuleCandidate = $.extend( true, {}, movedModuleCandidate );
                                          fromColumnCandidate.collection = _.filter( fromColumnCandidate.collection, function( module ) {
                                                return module.id != params.id;
                                          });
                                    }// if params.from_column != params.to_column
                                    toColumnCandidate.collection =  _.isArray( toColumnCandidate.collection ) ? toColumnCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toColumnCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          if ( params.from_column != params.to_column && _id_ == copyOfMovedModuleCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedModuleCandidate );
                                          } else {
                                                moduleCandidate = self.getLevelModel( _id_, newSetValue.collection );
                                                if ( _.isEmpty( moduleCandidate ) || 'no_match' == moduleCandidate ) {
                                                      throw new Error( 'updateAPISetting => ' + params.action + ' => missing moduleCandidate' );
                                                }
                                                reorderedCollection.push( moduleCandidate );
                                          }
                                    });
                                    if ( reorderedCollection.length != _.uniq( reorderedCollection ).length ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => there are duplicated modules in column : ' + toColumnCandidate.id );
                                    } else {
                                          toColumnCandidate.collection = reorderedCollection;
                                    }
                              break;


                              case 'sek-set-module-value' :
                                    moduleCandidate = self.getLevelModel( params.id, newSetValue.collection );

                                    var _value_ = {};
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _value_[ _key_ ] = _val_;
                                    });
                                    if ( 'no_match' == moduleCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no module matched', params );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => error no module matched');
                                          break;
                                    }
                                    if ( _.isEmpty( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                          break;
                                    }
                                    if ( '__no_option_group_to_be_updated_by_children_modules__' === params.options_type ) {
                                          moduleCandidate.value = _value_;
                                    } else {
                                          moduleCandidate.value = _.isEmpty( moduleCandidate.value ) ? {} : moduleCandidate.value;
                                          moduleCandidate.value[ params.options_type ] = _value_;
                                    }

                              break;
                              case 'sek-generate-level-options-ui' :
                                    var _candidate_ = self.getLevelModel( params.id, newSetValue.collection ),
                                        _valueCandidate = {};
                                    if ( 'no_match'=== _candidate_ ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }
                                    _candidate_.options = _candidate_.options || {};
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _valueCandidate[ _key_ ] = _val_;
                                    });
                                    if ( _.isEmpty( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                    }
                                    _candidate_.options[ params.options_type ] = _valueCandidate;
                              break;
                              case 'sek-generate-local-skope-options-ui' :
                                    _valueCandidate = {};

                                    var _currentOptions = $.extend( true, {}, _.isObject( newSetValue.local_options ) ? newSetValue.local_options : {} );
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _valueCandidate[ _key_ ] = _val_;
                                    });
                                    if ( _.isEmpty( params.options_type ) || ! _.isString( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                    } else {
                                          var newOptionsValues = {};
                                          newOptionsValues[ params.options_type ] = _valueCandidate;
                                          newSetValue.local_options = $.extend( _currentOptions, newOptionsValues );
                                    }
                              break;
                              case 'sek-add-content-in-new-sektion' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    position = 0;
                                    locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                    if ( 'no_match' == locationCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                          break;
                                    }
                                    locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                    _.each( locationCandidate.collection, function( secModel, index ) {
                                          if ( params.before_section === secModel.id ) {
                                                position = index;
                                          }
                                          if ( params.after_section === secModel.id ) {
                                                position = index + 1;
                                          }
                                    });

                                    switch( params.content_type) {
                                          case 'module' :
                                                startingModuleValue = self.getModuleStartingValue( params.content_id );
                                                locationCandidate.collection.splice( position, 0, {
                                                      id : params.id,
                                                      level : 'section',
                                                      collection : [
                                                            {
                                                                  id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                                  level : 'column',
                                                                  collection : [
                                                                        {
                                                                              id : params.droppedModuleId,
                                                                              level : 'module',
                                                                              module_type : params.content_id,
                                                                              value : 'no_starting_value' !== startingModuleValue ? startingModuleValue : null,
                                                                              ver_ini : sektionsLocalizedData.nimbleVersion
                                                                        }
                                                                  ],
                                                                  ver_ini : sektionsLocalizedData.nimbleVersion
                                                            }
                                                      ],
                                                      ver_ini : sektionsLocalizedData.nimbleVersion
                                                });
                                          break;
                                          case 'preset_section' :
                                                __presetSectionInjected__ = $.Deferred();//defined at the beginning of the method

                                                var _doWhenPresetSectionCollectionFetched = function( presetColumnCollection ) {
                                                      self.preparePresetSectionForInjection( presetColumnCollection )
                                                            .fail( function( _er_ ){
                                                                  __updateAPISettingDeferred__.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                                                  __presetSectionInjected__.reject( _er_ );
                                                            })
                                                            .done( function( sectionReadyToInject ) {
                                                                  var insertedInANestedSektion = false;
                                                                  if ( ! _.isEmpty( params.sektion_to_replace ) ) {
                                                                        var sektionToReplace = self.getLevelModel( params.sektion_to_replace, newSetValue.collection );
                                                                        if ( 'no_match' === sektionToReplace ) {
                                                                              api.errare( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched' );
                                                                              __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched');
                                                                        }
                                                                        insertedInANestedSektion = true === sektionToReplace.is_nested;
                                                                  }

                                                                  if ( ! insertedInANestedSektion ) {
                                                                        locationCandidate.collection.splice( position, 0, {
                                                                              id : params.id,
                                                                              level : 'section',
                                                                              collection : sectionReadyToInject.collection,
                                                                              options : sectionReadyToInject.options || {},
                                                                              ver_ini : sektionsLocalizedData.nimbleVersion
                                                                        });
                                                                  } else {
                                                                        columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                                                        if ( 'no_match' === columnCandidate ) {
                                                                              api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                                              __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                                        }

                                                                        columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                                                        _.each( columnCandidate.collection, function( moduleOrSectionModel, index ) {
                                                                              if ( params.before_section === moduleOrSectionModel.id ) {
                                                                                    position = index;
                                                                              }
                                                                              if ( params.after_section === moduleOrSectionModel.id ) {
                                                                                    position = index + 1;
                                                                              }
                                                                        });
                                                                        columnCandidate.collection.splice( position, 0, {
                                                                              id : params.id,
                                                                              is_nested : true,
                                                                              level : 'section',
                                                                              collection : sectionReadyToInject.collection,
                                                                              options : sectionReadyToInject.options || {},
                                                                              ver_ini : sektionsLocalizedData.nimbleVersion
                                                                        });
                                                                  }
                                                                  __presetSectionInjected__.resolve();
                                                            });// self.preparePresetSectionForInjection.done()
                                                };//_doWhenPresetSectionCollectionFetched()
                                                self.getPresetSectionCollection({
                                                            is_user_section : params.is_user_section,
                                                            presetSectionId : params.content_id,
                                                            section_id : params.id//<= we need to use the section id already generated, and passed for ajax action @see ::reactToPreviewMsg, case "sek-add-section"
                                                      })
                                                      .fail( function( _er_ ) {
                                                            api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                            __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                                      })
                                                      .done( function( presetColumnCollection ) {
                                                            if ( ! _.isObject( presetColumnCollection ) || _.isEmpty( presetColumnCollection ) ) {
                                                                  api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnCollection );
                                                                  __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                            }
                                                            _doWhenPresetSectionCollectionFetched( presetColumnCollection );
                                                      });//self.getPresetSectionCollection().done()
                                          break;
                                    }//switch( params.content_type)
                              break;
                              case 'sek-add-preset-section-in-new-nested-sektion' :
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    parentSektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == parentSektionCandidate ) {
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                          break;
                                    }
                                    if ( true === parentSektionCandidate.is_nested ) {
                                          __updateAPISettingDeferred__.reject( sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ]);
                                          break;
                                    }
                                    if ( 'no_match' == columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }
                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                    var presetColumnCollection;
                                    __presetSectionInjected__ = $.Deferred();//defined at the beginning of the method

                                    _doWhenPresetSectionCollectionFetched = function( presetColumnCollection ) {
                                          self.preparePresetSectionForInjection( presetColumnCollection )
                                                .fail( function( _er_ ){
                                                      __updateAPISettingDeferred__.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                                      __presetSectionInjected__.reject( _er_ );
                                                })
                                                .done( function( sectionReadyToInject ) {
                                                      columnCandidate.collection.push({
                                                            id : params.id,
                                                            level : 'section',
                                                            collection : sectionReadyToInject.collection,
                                                            options : sectionReadyToInject.options || {},
                                                            is_nested : true,
                                                            ver_ini : sektionsLocalizedData.nimbleVersion
                                                      });
                                                      __presetSectionInjected__.resolve();
                                                });//self.preparePresetSectionForInjection.done()
                                    };//_doWhenPresetSectionCollectionFetched
                                    self.getPresetSectionCollection({
                                                is_user_section : params.is_user_section,
                                                presetSectionId : params.content_id,
                                                section_id : params.id//<= we need to use the section id already generated, and passed for ajax action @see ::reactToPreviewMsg, case "sek-add-section"
                                          })
                                          .fail( function() {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                          })
                                          .done( function( presetColumnCollection ) {
                                                if ( ! _.isObject( presetColumnCollection ) || _.isEmpty( presetColumnCollection ) ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnCollection );
                                                      __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                }
                                                _doWhenPresetSectionCollectionFetched( presetColumnCollection );
                                          });//self.getPresetSectionCollection().done()
                              break;
                              case 'sek-update-fonts' :
                                    var currentGfonts = self.sniffGFonts( { is_global_location : params && true === params.is_global_location } );
                                    if ( ! _.isEmpty( params.font_family ) && _.isString( params.font_family ) && ! _.contains( currentGfonts, params.font_family ) ) {
                                          if ( params.font_family.indexOf('gfont') < 0 ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont' );
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont');
                                                break;
                                          }
                                          currentGfonts.push( params.font_family );
                                    }
                                    newSetValue.fonts = currentGfonts;
                              break;
                        }// switch
                        if ( 'pending' == __updateAPISettingDeferred__.state() ) {
                              var mayBeUpdateSektionsSetting = function() {
                                    if ( _.isEqual( currentSetValue, newSetValue ) ) {
                                          if ( sektionsLocalizedData.isDevMode ) {
                                                __updateAPISettingDeferred__.reject( 'updateAPISetting => the new setting value is unchanged when firing action : ' + params.action );
                                          }
                                    } else {
                                          if ( null !== self.validateSettingValue( newSetValue ) ) {
                                                sektionSetInstance( newSetValue, params );
                                                params.cloneId = cloneId;
                                                __updateAPISettingDeferred__.resolve( params );
                                          } else {
                                                __updateAPISettingDeferred__.reject( 'Validation problem for action ' + params.action );
                                          }
                                    }
                              };//mayBeUpdateSektionsSetting()

                              if ( false === __presetSectionInjected__ ) {
                                    mayBeUpdateSektionsSetting();
                              } else {
                                    __presetSectionInjected__
                                          .done( function() {
                                               mayBeUpdateSektionsSetting();
                                          })
                                          .fail( function( _er_ ) {
                                                api.errare( 'updateAPISetting => __presetSectionInjected__ failed', _er_ );
                                          });
                              }
                        }
                  });//api( _collectionSettingId_, function( sektionSetInstance ) {}
                  return __updateAPISettingDeferred__.promise();
            },//updateAPISetting
            _maybeFetchSectionsFromServer : function( params ) {
                  var dfd = $.Deferred(),
                      _ajaxRequest_;

                  params = params || { is_user_section : false };
                  if ( true === params.is_user_section ) {
                        if ( ! _.isEmpty( api.sek_userSavedSections ) && ! _.isEmpty( api.sek_userSavedSections[ params.preset_section_id ] ) ) {
                              dfd.resolve( api.sek_userSavedSections );
                        } else {
                              api.sek_userSavedSections = api.sek_userSavedSections || {};
                              if ( ! _.isUndefined( api.sek_fetchingUserSavedSections ) && 'pending' == api.sek_fetchingUserSavedSections.state() ) {
                                    _ajaxRequest_ = api.sek_fetchingUserSavedSections;
                              } else {
                                    _ajaxRequest_ = wp.ajax.post( 'sek_get_user_saved_sections', {
                                          nonce: api.settings.nonce.save,
                                          preset_section_id : params.preset_section_id
                                    });
                                    api.sek_fetchingUserSavedSections = _ajaxRequest_;
                              }
                              _ajaxRequest_.done( function( _sectionData_ ) {
                                    api.sek_userSavedSections[ params.preset_section_id ] = _sectionData_;
                                    dfd.resolve( api.sek_userSavedSections );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });
                        }
                  } else {
                        if ( ! _.isEmpty( api.sek_presetSections ) ) {
                              dfd.resolve( api.sek_presetSections );
                        } else {
                              if ( ! _.isUndefined( api.sek_fetchingPresetSections ) && 'pending' == api.sek_fetchingPresetSections.state() ) {
                                    _ajaxRequest_ = api.sek_fetchingPresetSections;
                              } else {
                                    _ajaxRequest_ = wp.ajax.post( 'sek_get_preset_sections', { nonce: api.settings.nonce.save } );
                                    api.sek_fetchingPresetSections = _ajaxRequest_;
                              }
                              _ajaxRequest_.done( function( _collection_ ) {
                                    api.sek_presetSections = _collection_;
                                    dfd.resolve( api.sek_presetSections );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });
                        }
                  }

                  return dfd.promise();
            },
            getPresetSectionCollection : function( sectionParams ) {
                  var self = this,
                      __dfd__ = $.Deferred();

                  self._maybeFetchSectionsFromServer({
                        is_user_section : sectionParams.is_user_section,
                        preset_section_id : sectionParams.presetSectionId
                  })
                        .fail( function( er ) {
                              __dfd__.reject( er );
                        })
                        .done( function( _collection_ ) {
                              var presetSection,
                                  allPresets = $.extend( true, {}, _.isObject( _collection_ ) ? _collection_ : {} );

                              if ( _.isEmpty( allPresets ) ) {
                                    throw new Error( 'getPresetSectionCollection => Invalid collection');
                              }
                              if ( _.isEmpty( allPresets[ sectionParams.presetSectionId ] ) ) {
                                    throw new Error( 'getPresetSectionCollection => the preset section : "' + sectionParams.presetSectionId + '" has not been found in the collection');
                              }
                              var presetCandidate = allPresets[ sectionParams.presetSectionId ];

                              var setIds = function( collection ) {
                                    _.each( collection, function( levelData ) {
                                          levelData.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                                          if ( _.isArray( levelData.collection ) ) {
                                                setIds( levelData.collection );
                                          }
                                    });
                                    return collection;
                              };

                              var setVersion = function( collection ) {
                                    _.each( collection, function( levelData ) {
                                          levelData.ver_ini = sektionsLocalizedData.nimbleVersion;
                                          if ( _.isArray( levelData.collection ) ) {
                                                setVersion( levelData.collection );
                                          }
                                    });
                                    return collection;
                              };
                              presetCandidate.id = sectionParams.section_id;
                              presetCandidate.collection = setIds( presetCandidate.collection );
                              presetCandidate.ver_ini = sektionsLocalizedData.nimbleVersion;
                              presetCandidate.collection = setVersion( presetCandidate.collection );
                              __dfd__.resolve( presetCandidate );
                        });//_maybeFetchSectionsFromServer.done()

                  return __dfd__.promise();
            },
            preparePresetSectionForInjection : function( columnCollection ) {
                var self = this,
                    deferreds = {},
                    preparedSection = {},
                    _dfd_ = $.Deferred();
                var _sniffImg = function( data ) {
                      _.each( data, function( val, key ) {
                            if ( _.isObject( val ) || _.isArray( val ) ) {
                                  _sniffImg( val );
                            } else if ( _.isString( val ) && -1 != val.indexOf( '::img-path::' ) ) {
                                  if ( ! _.has( deferreds, val ) ) {
                                        deferreds[ val ] = self.importAttachment( val.replace( '::img-path::', '' ) );
                                  }
                            }
                      });
                      return deferreds;
                };
                var _replaceImgPlaceholderById = function( data, imgList) {
                      _.each( data, function( val, key ) {
                            if ( _.isObject( val ) || _.isArray( val ) ) {
                                  _replaceImgPlaceholderById( val, imgList );
                            } else if ( _.isString( val ) && -1 != val.indexOf( '::img-path::' ) && _.has( imgList, val ) && _.isObject( imgList[ val ] ) ) {
                                  data[ key ] = imgList[ val ].id;
                            }
                      });
                      return columnCollection;
                };

                self.whenAllPromisesInParallel( _sniffImg( columnCollection ) )
                    .done( function( imgList ) {
                          var imgReadySection = _replaceImgPlaceholderById( columnCollection, imgList );
                          _dfd_.resolve( imgReadySection );
                    })
                    .fail( function( _er_ ){
                          _dfd_.reject( _er_ );
                    });

                return _dfd_.promise();
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            cleanRegistered : function( _id_ ) {
                  var self = this,
                      registered = $.extend( true, [], self.registered() || [] );

                  registered = _.filter( registered, function( _reg_ ) {
                        if ( 'setting' !== _reg_.what ) {
                              if ( api[ _reg_.what ].has( _reg_.id ) ) {
                                    if ( ! _.isEmpty( _id_ ) && _reg_.id !== _id_ )
                                      return;
                                    if (  _.isFunction( api[ _reg_.what ]( _reg_.id ).trigger ) ) {//<= Section and Panel constructor are not extended with the Event class, that's why we check if this method exists
                                           self.trigger( 'sek-ui-pre-removal', { what : _reg_.what, id : _reg_.id } );
                                    }
                                    $.when( api[ _reg_.what ]( _reg_.id ).container.remove() ).done( function() {
                                          api[ _reg_.what ].remove( _reg_.id );
                                          self.trigger( 'sek-ui-removed', { what : _reg_.what, id : _reg_.id } );
                                    });
                              }
                        }
                        return _reg_.what === 'setting';
                  });
                  self.registered( registered );
            },
            cleanRegisteredLevelSettingsAfterHistoryNavigation : function() {
                  var self = this,
                      registered = $.extend( true, [], self.registered() || [] );

                  registered = _.filter( registered, function( _reg_ ) {
                        if ( ! _.isEmpty( _reg_.level ) && 'setting' === _reg_.what ) {
                              if ( api.has( _reg_.id ) ) {
                                    api.remove( _reg_.id );
                              }
                        }
                        return _.isEmpty( _reg_.level ) && 'setting' !== _reg_.what ;
                  });
                  self.registered( registered );
            }

      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            rootPanelFocus : function() {
                  if ( api.section.has( api.czr_activeSectionId() ) ) {
                        api.section( api.czr_activeSectionId() ).expanded( false );
                  } else {
                        api.section.each( function( _s ) {
                            _s.expanded( false );
                        });
                  }
                  api.panel.each( function( _p ) {
                        _p.expanded( false );
                  });
            },
            guid : function() {
                  function s4() {
                        return Math.floor((1 + Math.random()) * 0x10000)
                          .toString(16)
                          .substring(1);
                  }
                  return s4() + s4() + s4();//s4() + s4() + s4() + s4() + s4() + s4();
            },
            getGlobalSectionsSettingId : function() {
                  return sektionsLocalizedData.settingIdForGlobalSections;
            },
            getLevelModel : function( id, collection ) {
                  var self = this, _data_ = 'no_match',
                      _walk_ = function( id, collection, collectionSettingId, localOrGlobal ) {
                            if ( _.isUndefined( collection ) ) {
                                  var currentSektionSettingValue = api( collectionSettingId )();
                                  var sektionSettingValue = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
                                  collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                            }
                            _.each( collection, function( levelData ) {
                                  if ( 'no_match' != _data_ )
                                    return;
                                  if ( id === levelData.id ) {
                                        _data_ = levelData;
                                  } else {
                                        if ( _.isArray( levelData.collection ) ) {
                                              _walk_( id, levelData.collection, collectionSettingId, localOrGlobal );
                                        }
                                  }
                            });
                            return _data_;
                      };
                  if ( ! _.isEmpty( collection ) ) {
                        _walk_( id, collection );
                  } else {
                        _.each( {
                              local : self.localSectionsSettingId(),
                              global : self.getGlobalSectionsSettingId()
                        }, function( collectionSettingId, localOrGlobal ) {
                              if ( 'no_match' === _data_ ) {
                                    _walk_( id, collection, collectionSettingId, localOrGlobal );
                              }
                        });
                  }

                  return _data_;
            },
            isGlobalLocation : function( params ) {
                  var self = this, is_global_location = false;
                  params = params || {};
                  if ( _.has( params, 'is_global_location' ) ) {
                        is_global_location = params.is_global_location;
                  } else if ( !_.isEmpty( params.location ) ) {
                        is_global_location = self.isChildOfAGlobalLocation( params.location );
                  } else if ( !_.isEmpty( params.in_sektion ) ) {
                        is_global_location = self.isChildOfAGlobalLocation( params.in_sektion );
                  } else if ( !_.isEmpty( params.id ) ) {
                        is_global_location = self.isChildOfAGlobalLocation( params.id );
                  }
                  return is_global_location;
            },
            isChildOfAGlobalLocation : function( id ) {
                  var self = this,
                      walkCollection = function( id, collection ) {
                            var _data_ = 'no_match';
                            if ( _.isUndefined( collection ) ) {
                                  var currentSettingValue = api( self.getGlobalSectionsSettingId() )();
                                  var sektionSettingValue = _.isObject( currentSettingValue ) ? $.extend( true, {}, currentSettingValue ) : self.getDefaultSektionSettingValue( 'global' );
                                  collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                            }
                            _.each( collection, function( levelData ) {
                                  if ( 'no_match' != _data_ )
                                    return;
                                  if ( id === levelData.id ) {
                                        _data_ = levelData;
                                  } else {
                                        if ( _.isArray( levelData.collection ) ) {
                                              _data_ = walkCollection( id, levelData.collection );
                                        }
                                  }
                            });
                            return _data_;
                      };
                  return walkCollection( id ) !== 'no_match';
            },


            getLevelPositionInCollection : function( id, collection ) {
                  var self = this, _position_ = 'no_match',
                  _walk_ = function( id, collection, collectionSettingId, localOrGlobal ) {
                        if ( _.isUndefined( collection ) ) {
                              var currentSektionSettingValue = api( collectionSettingId )();
                              var sektionSettingValue = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
                              collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                        }
                        _.each( collection, function( levelData, _key_ ) {
                              if ( 'no_match' != _position_ )
                                return;
                              if ( id === levelData.id ) {
                                    _position_ = _key_;
                              } else {
                                    if ( _.isArray( levelData.collection ) ) {
                                          _walk_( id, levelData.collection, collectionSettingId, localOrGlobal );
                                    }
                              }
                        });
                  };
                  if ( ! _.isEmpty( collection ) ) {
                        _walk_( id, collection );
                  } else {
                        _.each( {
                              local : self.localSectionsSettingId(),
                              global : self.getGlobalSectionsSettingId()
                        }, function( collectionSettingId, localOrGlobal ) {
                              if ( 'no_match' === _position_ ) {
                                    _walk_( id, collectionSettingId, localOrGlobal, collection );
                              }
                        });
                  }
                  return _position_;
            },
            getLevelProperty : function( params ) {
                  params = _.extend( {
                        id : '',
                        property : ''
                  }, params );
                  if ( _.isEmpty( params.id ) ) {
                        api.errare( 'getLevelProperty => invalid id provided' );
                        return;
                  }
                  var self = this,
                      modelCandidate = self.getLevelModel( params.id );

                  if ( 'no_match' == modelCandidate ) {
                        api.errare( 'getLevelProperty => no level model found for id : ' + params.id );
                        return;
                  }
                  if ( ! _.isObject( modelCandidate ) ) {
                        api.errare( 'getLevelProperty => invalid model for id : ' + params.id, modelCandidate );
                        return;
                  }
                  return modelCandidate[ params.property ];
            },
            cloneLevel : function( levelId ) {
                  var self = this;
                  var levelModelCandidate = self.getLevelModel( levelId );
                  if ( 'no_match' == levelModelCandidate ) {
                        throw new Error( 'cloneLevel => no match for level id : ' + levelId );
                  }
                  var deepClonedLevel = $.extend( true, {}, levelModelCandidate );
                  var newIdWalker = function( level_model ) {
                        if ( _.isEmpty( level_model.id ) ) {
                            throw new Error( 'cloneLevel => missing level id');
                        }
                        level_model.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                        if ( ! _.isEmpty( level_model.collection ) ) {
                              if ( ! _.isArray( level_model.collection ) ) {
                                    throw new Error( 'cloneLevel => the collection must be an array for level id : ' + level_model.id );
                              }
                              _.each( level_model.collection, function( levelData ) {
                                    levelData.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                                    newIdWalker( levelData );
                              });
                        }
                        return level_model;
                  };
                  return newIdWalker( deepClonedLevel );
            },
            getDefaultItemModelFromRegisteredModuleData : function( moduleType ) {
                  if ( ! this.isModuleRegistered( moduleType ) ) {
                        return {};
                  }
                  if ( sektionsLocalizedData.registeredModules[moduleType].is_father ) {
                        api.errare( 'getDefaultItemModelFromRegisteredModuleData => Father modules should be treated specifically' );
                        return;
                  }
                  var data = sektionsLocalizedData.registeredModules[ moduleType ].tmpl['item-inputs'],
                      defaultItemModel = {
                            id : '',
                            title : ''
                      },
                      self = this;

                  _.each( data, function( _d_, _key_ ) {
                        switch ( _key_ ) {
                              case 'tabs' :
                                    _.each( _d_ , function( _tabData_ ) {
                                          _.each( _tabData_.inputs, function( _inputData_, _id_ ) {
                                                defaultItemModel[ _id_ ] = _inputData_['default'] || '';
                                          });
                                    });
                              break;
                              default :
                                    defaultItemModel[ _key_ ] = _d_['default'] || '';
                              break;
                        }
                  });
                  return defaultItemModel;
            },
            getRegisteredModuleProperty : function( moduleType, property ) {
                  if ( ! this.isModuleRegistered( moduleType ) ) {
                        return 'not_set';
                  }
                  return sektionsLocalizedData.registeredModules[ moduleType ][ property ];
            },
            isModuleRegistered : function( moduleType ) {
                  return sektionsLocalizedData.registeredModules && ! _.isUndefined( sektionsLocalizedData.registeredModules[ moduleType ] );
            },
            sniffGFonts : function( args ) {
                  args = args || { is_global_location : false };
                  var self = this,
                  gfonts = [],
                  _snifff_ = function( collectionSettingId, localOrGlobal, level ) {
                        if ( _.isUndefined( level ) ) {
                              var currentSektionSettingValue = api( collectionSettingId )();
                              level = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
                        }
                        _.each( level, function( levelData, _key_ ) {
                              if ( _.isString( _key_ ) && '_css' === _key_.substr( _key_.length - 4 ) ) {
                                    if ( true === self.inputIsAFontFamilyModifier( _key_ ) ) {
                                          if ( levelData.indexOf('gfont') > -1 && ! _.contains( gfonts, levelData ) ) {
                                                gfonts.push( levelData );
                                          }
                                    }
                              }

                              if ( _.isArray( levelData ) || _.isObject( levelData ) ) {
                                    _snifff_( collectionSettingId, localOrGlobal, levelData );
                              }
                        });
                  };
                  if ( args.is_global_location ) {
                        _snifff_( self.getGlobalSectionsSettingId(), 'global' );
                  } else {
                        _snifff_( self.localSectionsSettingId(), 'local' );
                  }

                  return gfonts;
            },
            getInputDefaultValue : function( input_id, module_type, level ) {
                  var self = this;
                  self.cachedDefaultInputValues = self.cachedDefaultInputValues || {};
                  self.cachedDefaultInputValues[ module_type ] = self.cachedDefaultInputValues[ module_type ] || {};
                  if ( _.has( self.cachedDefaultInputValues[ module_type ], input_id ) ) {
                        return self.cachedDefaultInputValues[ module_type ][ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputDefaultValue => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[module_type] ) ) {
                        api.errare( 'getInputDefaultValue => missing ' + module_type + ' in sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( sektionsLocalizedData.registeredModules[module_type].is_father ) {
                        api.errare( 'getInputDefaultValue => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ].tmpl;
                  }
                  var _defaultVal_ = 'no_default_value_specified';
                  _.each( level, function( levelData, _key_ ) {
                        if ( 'no_default_value_specified' !== _defaultVal_ )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.default ) ) {
                              _defaultVal_ = levelData.default;
                        }
                        if ( 'no_default_value_specified' === _defaultVal_ && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _defaultVal_ = self.getInputDefaultValue( input_id, module_type, levelData );
                        }
                        if ( 'no_default_value_specified' !== _defaultVal_ ) {
                            self.cachedDefaultInputValues[ module_type ][ input_id ] = _defaultVal_;
                        }
                  });
                  return _defaultVal_;
            },
            getInputType : function( input_id, module_type, level ) {
                  var self = this;
                  self.cachedInputTypes = self.cachedInputTypes || {};
                  self.cachedInputTypes[ module_type ] = self.cachedInputTypes[ module_type ] || {};
                  if ( _.has( self.cachedInputTypes[ module_type ], input_id ) ) {
                        return self.cachedInputTypes[ module_type ][ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputType => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[module_type] ) ) {
                        api.errare( 'getInputType => missing ' + module_type + ' in sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( sektionsLocalizedData.registeredModules[module_type].is_father ) {
                        api.errare( 'getInputType => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ].tmpl;
                  }
                  var _inputType_ = 'no_input_type_specified';
                  _.each( level, function( levelData, _key_ ) {
                        if ( 'no_input_type_specified' !== _inputType_ )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.input_type ) ) {
                              _inputType_ = levelData.input_type;
                        }
                        if ( 'no_input_type_specified' === _inputType_ && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _inputType_ = self.getInputType( input_id, module_type, levelData );
                        }
                        if ( 'no_input_type_specified' !== _inputType_ ) {
                              self.cachedInputTypes[ module_type ][ input_id ] = _inputType_;
                        }
                  });
                  return _inputType_;
            },
            getInputRegistrationParams : function( input_id, module_type, level ) {
                  var self = this;
                  self.cachedInputRegistrationParams = self.cachedInputRegistrationParams || {};
                  self.cachedInputRegistrationParams[ module_type ] = self.cachedInputRegistrationParams[ module_type ] || {};
                  if ( _.has( self.cachedInputRegistrationParams[ module_type ], input_id ) ) {
                        return self.cachedInputRegistrationParams[ module_type ][ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputRegistrationParams => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[module_type] ) ) {
                        api.errare( 'getInputRegistrationParams => missing ' + module_type + ' in sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( sektionsLocalizedData.registeredModules[module_type].is_father ) {
                        api.errare( 'getInputRegistrationParams => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ].tmpl;
                  }
                  var _params_ = {};
                  _.each( level, function( levelData, _key_ ) {
                        if ( ! _.isEmpty( _params_ ) )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.input_type ) ) {
                              _params_ = levelData;
                        }
                        if ( _.isEmpty( _params_ ) && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _params_ = self.getInputRegistrationParams( input_id, module_type, levelData );
                        }
                        if ( ! _.isEmpty( _params_ ) ) {
                              self.cachedInputRegistrationParams[ module_type ][ input_id ] = _params_;
                        }
                  });
                  return _params_;
            },
            inputIsAFontFamilyModifier : function( input_id, level ) {
                  var self = this;
                  self.cachedFontFamilyModifier = self.cachedFontFamilyModifier || {};
                  if ( _.has( self.cachedFontFamilyModifier, input_id ) ) {
                        return self.cachedFontFamilyModifier[ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'inputIsAFontFamilyModifier => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules;
                  }
                  var _bool_ = 'not_set';
                  _.each( level, function( levelData, _key_ ) {
                        if ( 'not_set' !== _bool_ )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.input_type ) ) {
                              _bool_ = _.isUndefined( levelData.refresh_fonts ) ? false : levelData.refresh_fonts;
                        }
                        if ( 'not_set' === _bool_ && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _bool_ = self.inputIsAFontFamilyModifier( input_id, levelData );
                        }
                        if ( 'not_set' !== _bool_ ) {
                              self.cachedFontFamilyModifier[ input_id ] = _bool_;
                        }
                  });
                  return _bool_;
            },
            getModuleStartingValue : function( module_type ) {
                  if ( ! sektionsLocalizedData.registeredModules ) {
                        api.errare( 'getModuleStartingValue => missing sektionsLocalizedData.registeredModules' );
                        return 'no_starting_value';
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[ module_type ] ) ) {
                        api.errare( 'getModuleStartingValue => the module type ' + module_type + ' is not registered' );
                        return 'no_starting_value';
                  }
                  var starting_value = sektionsLocalizedData.registeredModules[ module_type ].starting_value;
                  return _.isEmpty( starting_value ) ? 'no_starting_value' : starting_value;
            },



            /*
            * Following two functions taken from jQuery.tabbable 1.0
            * see https://github.com/marklagendijk/jquery.tabbable/blob/master/jquery.tabbable.js
            *
            * Copyright 2013, Mark Lagendijk
            * Released under the MIT license
            */
            selectNextTabbableOrFocusable : function( selector ) {
                  var selectables = $( selector );
                  var current = $( ':focus' );
                  var nextIndex = 0;
                  if( current.length === 1 ) {
                        var currentIndex = selectables.index( current );
                        if( currentIndex + 1 < selectables.length ) {
                              nextIndex = currentIndex + 1;
                        }
                  }

                  selectables.eq( nextIndex ).focus();
            },

            selectPrevTabbableOrFocusable : function( selector ) {
                  var selectables = $( selector );
                  var current = $( ':focus' );
                  var prevIndex = selectables.length - 1;
                  if( current.length === 1 ) {
                        var currentIndex = selectables.index( current );
                        if( currentIndex > 0 ) {
                              prevIndex = currentIndex - 1;
                        }
                  }

                  selectables.eq( prevIndex ).focus();
            },
            setupSelectInput : function( selectOptions ) {
                  var input  = this,
                      item   = input.input_parent,
                      module = input.module,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type );

                  selectOptions = _.isUndefined( selectOptions ) ? inputRegistrationParams.choices : selectOptions;

                  if ( _.isEmpty( selectOptions ) || ! _.isObject( selectOptions ) ) {
                        api.errare( 'api.czr_sektions.setupSelectInput => missing select options for input id => ' + input.id + ' in image module');
                        return;
                  } else {
                        _.each( selectOptions , function( title, value ) {
                              var _attributes = {
                                        value : value,
                                        html: title
                                  };
                              if ( value == input() ) {
                                    $.extend( _attributes, { selected : "selected" } );
                              } else if ( 'px' === value ) {
                                    $.extend( _attributes, { selected : "selected" } );
                              }
                              $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                        });
                        $( 'select[data-czrtype]', input.container ).selecter();
                  }
            },
            setupFontSizeAndLineHeightInputs : function( obj ) {
                  var input      = this,
                      $wrapper = $('.sek-font-size-line-height-wrapper', input.container ),
                      initial_unit = $wrapper.find('input[data-czrtype]').data('sek-unit'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'error : invalid unit for input ' + input.id, unit );
                                  unit = 'px';
                            }
                            return unit;
                      };
                  input.css_unit = new api.Value( _.isEmpty( initial_unit ) ? 'px' : validateUnit( initial_unit ) );
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        $wrapper.find( 'input[type="number"]').trigger('change');
                  });
                  $wrapper.find( 'input[type="number"]').on('input change', function( evt ) {
                        input( $(this).val() + validateUnit( input.css_unit() ) );
                  }).stepper();
                  $wrapper.on( 'click', '[data-sek-unit]', function(evt) {
                        evt.preventDefault();
                        $wrapper.find('[data-sek-unit]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        input.css_unit( $(this).data('sek-unit') );
                  });
                  $wrapper.find( '.sek-ui-button[data-sek-unit="'+ initial_unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
            },
            maybeSetupDeviceSwitcherForInput : function() {
                  var input = this;
                  var deviceSwitcherHtml = [
                        '<span class="sek-input-device-switcher">',
                          '<i data-sek-device="desktop" class="sek-switcher preview-desktop active" title="'+ sektionsLocalizedData.i18n['Settings on desktops'] +'"></i>',
                          '<i data-sek-device="tablet" class="sek-switcher preview-tablet" title="'+ sektionsLocalizedData.i18n['Settings on tablets'] +'"></i>',
                          '<i data-sek-device="mobile" class="sek-switcher preview-mobile" title="'+ sektionsLocalizedData.i18n['Settings on mobiles'] +'"></i>',
                        '</span>'
                  ].join(' ');

                  input.container.find('.customize-control-title').prepend( deviceSwitcherHtml );
                  input.previewedDevice = new api.Value( api.previewedDevice() );


                  syncWithPreviewedDevice = function( evt ) {
                        evt.stopPropagation();
                        input.container.find( '[data-sek-device]' ).removeClass('active');
                        $(this).addClass('active');
                        var device = 'desktop';
                        try { device = $(this).data('sek-device'); } catch( er ) {
                              api.errare( 'maybeSetupDeviceSwitcherForInput => error when binding sek-switcher', er );
                        }
                        try { api.previewedDevice( device ); } catch( er ) {
                              api.errare( 'maybeSetupDeviceSwitcherForInput => error when setting the previewed device', er );
                        }
                        input.previewedDevice( device );
                  };
                  input.container.on( 'click', '[data-sek-device]', syncWithPreviewedDevice );
                  var $currentDeviceIcon = input.container.find('[data-sek-device="' + api.previewedDevice() + '"]');
                  if ( $currentDeviceIcon.length > 0 ) {
                        $currentDeviceIcon.trigger('click');
                  }
            },
            scheduleModuleAccordion : function( params ) {
                  params = params || { expand_first_control : true };
                  var _section_ = this;
                  $( _section_.container ).on( 'click', '.customize-control label > .customize-control-title', function( evt ) {
                        evt.stopPropagation();
                        var $control = $(this).closest( '.customize-control');

                        if ( "no" === $control.attr( 'data-sek-accordion' ))
                          return;

                        _section_.container.find('.customize-control').not( $control ).each( function() {
                              if ( $(this).attr( 'data-sek-accordion' ) )
                                return;
                              $(this).attr('data-sek-expanded', "false" );
                              $(this).find('.czr-items-wrapper').stop( true, true ).slideUp( 0 );
                        });
                        $control.find('.czr-items-wrapper').stop( true, true ).slideToggle({
                              duration : 0,
                              start : function() {
                                    $control.attr('data-sek-expanded', "false" == $control.attr('data-sek-expanded') ? "true" : "false" );
                                    $control.trigger( "true" == $control.attr('data-sek-expanded') ? 'sek-accordion-expanded' : 'sek-accordion-collapsed' );
                              }
                        });
                  });
                  if ( params.expand_first_control ) {
                        var firstControl = _.first( _section_.controls() );
                        if ( _.isObject( firstControl ) && ! _.isEmpty( firstControl.id ) ) {
                              api.control( firstControl.id, function( _ctrl_ ) {
                                    _ctrl_.container.trigger( 'sek-accordion-expanded' );
                                    _section_.container.find('.customize-control').first().find('label > .customize-control-title').trigger('click');
                              });
                        }
                  }
            },
            isPromise : function (fn) {
                  return fn && typeof fn.then === 'function' && String( $.Deferred().then ) === String( fn.then );
            },
            whenAllPromisesInParallel : function ( deferreds ) {
                var self = this,
                    mainDfd = $.Deferred(),
                    args = [],
                    _keys_ = _.keys( deferreds );

                _.each( deferreds, function( mayBeDfd, _k_ ) {
                      args.push( $.Deferred( function( _dfd_ ) {
                            var dfdCandidate = self.isPromise( mayBeDfd ) ? mayBeDfd : $.Deferred();
                            dfdCandidate
                                  .done( _dfd_.resolve )
                                  .fail( function (err) { _dfd_.reject( err ); } );
                      }) );
                });
                $.when.apply( this, args )
                      .done( function () {
                          var resObj = {},
                              resArgs = Array.prototype.slice.call( arguments );

                          _.each( resArgs, function( v, i ) {
                                resObj[ _keys_[i] ] = v;
                          });
                          mainDfd.resolve( resObj );
                      })
                      .fail( mainDfd.reject );

                return mainDfd;
            },
            whenAllPromisesInSerie : function ( deferreds, ind, promiseMessages, mainDfd ) {
                ind = ind || 0;
                promiseMessages = promiseMessages || {};
                mainDfd = mainDfd || $.Deferred();
                var self = this;
                if ( _.isArray( deferreds ) ) {
                      var mayBeDfd = deferreds[ind],
                          dfdCandidate = self.isPromise( mayBeDfd ) ? mayBeDfd : $.Deferred( function( _d_ ) { _d_.resolve(); } );

                      dfdCandidate.always( function( msg ) {
                            promiseMessages[ ind ] = msg;
                            if ( ( ind + 1 ) == deferreds.length ) {
                                  mainDfd.resolve( promiseMessages );
                            } else {
                                  if ( ind + 1 < deferreds.length ) {
                                      self.whenAllPromisesInSerie( deferreds, ind + 1, promiseMessages, mainDfd );
                                  }
                            }
                      });
                }//if
                return mainDfd;
            },
            importAttachment : function( relpath ) {
                  return wp.ajax.post( 'sek_import_attachment', {
                        rel_path : relpath,
                        nonce: api.settings.nonce.save//<= do we need to set a specific nonce to fetch the attachment
                  })
                  .fail( function( _er_ ) {
                        api.errare( 'sek_import_attachment ajax action failed for image ' +  relpath, _er_ );
                  });
            },
            cleanIds : function( levelData ) {
                  levelData.id = "";
                  var self = this;
                  _.each( levelData.collection, function( levelData ) {
                        levelData.id = "";
                        if ( _.isArray( levelData.collection ) ) {
                              self.cleanIds( levelData );
                        }
                  });
                  return levelData;
            },
            getDefaultSektionSettingValue : function( localOrGlobal ) {
                  if ( _.isUndefined( localOrGlobal ) || !_.contains( [ 'local', 'global' ], localOrGlobal ) ) {
                        api.errare( 'getDefaultSektionSettingValue => the skope should be set to local or global');
                  }
                  return 'global' === localOrGlobal ? sektionsLocalizedData.defaultGlobalSektionSettingValue : sektionsLocalizedData.defaultLocalSektionSettingValue;
            }

      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
/**
 * @https://github.com/StackHive/DragDropInterface
 * @https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API
 * @https://html.spec.whatwg.org/multipage/dnd.html#dnd
 * @https://caniuse.com/#feat=dragndrop
 */
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            setupDnd : function() {
                  var self = this;
                  self.bind( 'sek-refresh-dragzones', function( params ) {
                        if (  true !== 'draggable' in document.createElement('span') ) {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  sektionsLocalizedData.i18n['This browser does not support drag and drop. You might need to update your browser or use another one.'],
                                          dismissible: true
                                    } ) );
                                    _.delay( function() {
                                          api.notifications.remove( 'drag-drop-support' );
                                    }, 10000 );
                              });

                        }

                        self.setupNimbleDragZones( params.input_container );//<= module or section picker
                  });
                  api.previewer.bind( 'ready', function() {
                        try { self.setupNimbleDropZones();//<= module or section picker
                        } catch( er ) {
                              api.errare( '::setupDnd => error on self.setupNimbleDropZones()', er );
                        }
                        if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_intro_sec_picker_module' } ) ) ) {
                              self.rootPanelFocus();
                        } else if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_module_picker_module' } ) ) ) {
                              self.rootPanelFocus();
                        }
                  });
                  self.reactToDrop();
            },
            setupNimbleDragZones : function( $draggableWrapper ) {
                  var self = this;
                  var _onStart = function( evt ) {
                        self.lastClickedTargetInPreview({});

                        evt.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                        evt.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                        evt.originalEvent.dataTransfer.setData( "sek-section-type", $(this).data('sek-section-type') );
                        evt.originalEvent.dataTransfer.setData( "sek-is-user-section", $(this).data('sek-is-user-section') );
                        self.dndData = {
                              content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                              content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" ),
                              section_type : evt.originalEvent.dataTransfer.getData( "sek-section-type" ),
                              is_user_section : "true" === evt.originalEvent.dataTransfer.getData( "sek-is-user-section" )
                        };
                        try {
                              evt.originalEvent.dataTransfer.setData( 'browserSupport', 'browserSupport' );
                              evt.originalEvent.dataTransfer.clearData( 'browserSupport' );
                        } catch ( er ) {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  sektionsLocalizedData.i18n['This browser does not support drag and drop. You might need to update your browser or use another one.'],
                                          dismissible: true
                                    } ) );
                                    _.delay( function() {
                                          api.notifications.remove( 'drag-drop-support' );
                                    }, 10000 );
                              });
                        }
                        $(this).addClass('sek-dragged');
                        $('body').addClass('sek-dragging');
                        api.previewer.send( 'sek-drag-start', { type : self.dndData.content_type } );//fires the rendering of the dropzones
                  };
                  var _onEnd = function( evt ) {
                        $('body').removeClass('sek-dragging');
                        $(this).removeClass('sek-dragged');
                        api.previewer.send( 'sek-drag-stop' );
                  };
                  var _onDoubleClick = function( evt ) {
                        var _targetCandidate = self.lastClickedTargetInPreview();// { id : "__nimble__fb2ab3e47472" }
                        var $dropTarget;
                        if ( ! _.isEmpty( _targetCandidate ) && _targetCandidate.id ) {
                              $dropTarget = self.dnd_getDropZonesElements().find('[data-sek-id="' + _targetCandidate.id + '"]').find('.sek-module-drop-zone-for-first-module').first();
                        } else {
                              _doubleClickTargetMissingNotif();
                        }

                        if ( $dropTarget && $dropTarget.length > 0 ) {
                              api.czr_sektions.trigger( 'sek-content-dropped', {
                                    drop_target_element : $dropTarget,
                                    location : $dropTarget.closest('[data-sek-level="location"]').data('sek-id'),
                                    before_module : $dropTarget.data('drop-zone-before-module-or-nested-section'),
                                    after_module : $dropTarget.data('drop-zone-after-module-or-nested-section'),
                                    before_section : $dropTarget.data('drop-zone-before-section'),
                                    after_section : $dropTarget.data('drop-zone-after-section'),

                                    content_type : $(this).data('sek-content-type'),
                                    content_id : $(this).data('sek-content-id'),

                                    section_type : $(this).data('sek-section-type'),
                                    is_user_section : "true" === $(this).data('sek-is-user-section')
                              });
                              self.lastClickedTargetInPreview({});
                        } else {
                              _doubleClickTargetMissingNotif();
                              api.errare( 'Double click insertion => the target zone was not found');
                        }
                  };//_onDoubleClick()
                  var _doubleClickTargetMissingNotif = function() {
                        api.notifications.add( new api.Notification( 'missing-injection-target', {
                              type: 'info',
                              message: sektionsLocalizedData.i18n['You first need to click on a target ( with a + icon ) in the preview.'],
                              dismissible: true
                        } ) );
                        _.delay( function() {
                              api.notifications.remove( 'missing-injection-target' );
                        }, 30000 );
                  };
                  $draggableWrapper.find( '[draggable="true"]' ).each( function() {
                        $(this)
                              .on( 'dragstart', function( evt ) { _onStart.call( $(this), evt ); })
                              .on( 'dragend', function( evt ) { _onEnd.call( $(this), evt ); })
                              .dblclick( function( evt ) { _onDoubleClick.call( $(this), evt ); });
                  });
            },//setupNimbleZones()
            setupNimbleDropZones : function() {
                  var self = this;
                  this.$dropZones = this.dnd_getDropZonesElements();
                  this.preDropElement = $( '<div>', {
                        class: sektionsLocalizedData.preDropElementClass,
                        html : ''//will be set dynamically
                  });
                  if ( this.$dropZones.length < 1 ) {
                        throw new Error( '::setupNimbleDropZones => invalid Dom element');
                  }

                  this.$dropZones.each( function() {
                        var $zone = $(this);
                        if ( true === $zone.data('zone-droppable-setup') )
                            return;

                        self.enterOverTimer = null;
                        $zone
                              .on( 'dragenter dragover', sektionsLocalizedData.dropSelectors, function( evt ) {
                                    if ( _.isNull( self.enterOverTimer ) ) {
                                          self.enterOverTimer = true;
                                          _.delay(function() {
                                                if ( self.currentMousePosition && ( ( self.currentMousePosition + '' ) == ( evt.clientY + '' + evt.clientX + '') ) ) {
                                                      self.enterOverTimer = null;
                                                      return;
                                                }
                                                self.currentMousePosition = evt.clientY + '' + evt.clientX + '';
                                                self.dnd_toggleDragApproachClassesToDropZones( evt );
                                          }, 100 );
                                    }

                                    if ( ! self.dnd_canDrop( { targetEl : $(this), evt : evt } ) )
                                      return;

                                    evt.stopPropagation();
                                    self.dnd_OnEnterOver( $(this), evt );
                              })
                              .on( 'dragleave drop', sektionsLocalizedData.dropSelectors, function( evt ) {
                                    switch( evt.type ) {
                                          case 'dragleave' :
                                                if ( ! self.dnd_isOveringDropTarget( $(this), evt  ) ) {
                                                      self.dnd_cleanOnLeaveDrop( $(this), evt );
                                                }
                                          break;
                                          case 'drop' :
                                                this.$cachedDropZoneCandidates = null;//has been declared on enter over

                                                if ( ! self.dnd_canDrop( { targetEl : $(this), evt : evt } ) )
                                                  return;
                                                evt.preventDefault();//@see https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#drop
                                                self.dnd_onDrop( $(this), evt );
                                                self.dnd_cleanOnLeaveDrop( $(this), evt );
                                                api.previewer.send( 'sek-drag-stop' );
                                          break;
                                    }
                              })
                              .data( 'zone-droppable-setup', true );// flag the zone. Will be removed on 'destroy'

                });//this.dropZones.each()
            },//setupNimbleDropZones()



            dnd_isInTarget : function( $el, evt ) {
                  var yPos = evt.clientY,
                      xPos = evt.clientX,
                      dzoneRect = $el[0].getBoundingClientRect(),
                      isInHorizontally = xPos <= dzoneRect.right && dzoneRect.left <= xPos,
                      isInVertically = yPos >= dzoneRect.top && dzoneRect.bottom >= yPos;
                  return isInVertically && isInHorizontally;
            },
            dnd_toggleDragApproachClassesToDropZones : function( evt ) {

                  var self = this,
                      getHypotenuse = function( a, b ) {
                            return(Math.sqrt((a * a) + (b * b)));
                      };

                  this.$dropZones = this.$dropZones || this.dnd_getDropZonesElements();
                  this.$cachedDropZoneCandidates = _.isEmpty( this.$cachedDropZoneCandidates ) ? this.$dropZones.find('.sek-drop-zone') : this.$cachedDropZoneCandidates;// Will be reset on drop

                  this.distanceTable = [];

                  this.$dropZones.find('.sek-drop-zone').each( function() {
                        var yPos = evt.clientY,
                            xPos = evt.clientX,
                            APPROACHING_DIST = 120,
                            CLOSE_DIST = 80,
                            VERY_CLOSE_DIST = 50;//60;

                        var dzoneRect = $(this)[0].getBoundingClientRect(),
                            mouseToYCenter = Math.abs( yPos - ( dzoneRect.bottom - ( dzoneRect.bottom - dzoneRect.top )/2 ) ),
                            mouseToTop = Math.abs( dzoneRect.top - yPos ),
                            mouseToXCenter = Math.abs( xPos - ( dzoneRect.right - ( dzoneRect.right - dzoneRect.left )/2 ) ),
                            mouseToRight = xPos - dzoneRect.right,
                            mouseToLeft = dzoneRect.left - xPos,
                            isVeryCloseVertically = mouseToYCenter < VERY_CLOSE_DIST,
                            isVeryCloseHorizontally =  mouseToXCenter < VERY_CLOSE_DIST,
                            isCloseVertically = mouseToYCenter < CLOSE_DIST,
                            isCloseHorizontally =  mouseToXCenter < CLOSE_DIST,
                            isApproachingVertically = mouseToYCenter < APPROACHING_DIST,
                            isApproachingHorizontally = mouseToXCenter < APPROACHING_DIST,

                            isInHorizontally = xPos <= dzoneRect.right && dzoneRect.left <= xPos,
                            isInVertically = yPos >= dzoneRect.top && dzoneRect.bottom >= yPos;

                        self.distanceTable.push({
                              el : $(this),
                              dist : ( isInVertically && isInHorizontally ) ? 0 : getHypotenuse( mouseToXCenter, mouseToYCenter )
                        });
                        $(this).removeClass( 'sek-drag-is-in');

                        if ( ( isVeryCloseVertically || isInVertically ) && ( isVeryCloseHorizontally || isInHorizontally ) ) {
                              $(this).removeClass( 'sek-drag-is-approaching');
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).addClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-in');
                        } else {
                              $(this).removeClass( 'sek-drag-is-approaching');
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-in');
                        }
                  });//$('.sek-drop-zones').each()


                  var _lowerDist = _.min( _.pluck( self.distanceTable, 'dist') );
                  self.$dropTargetCandidate = null;
                  _.each( self.distanceTable, function( data ) {
                        if ( _.isNull( self.$dropTargetCandidate ) && _lowerDist === data.dist ) {
                              self.$dropTargetCandidate = data.el;
                        }
                  });
                  if ( self.$dropTargetCandidate && self.$dropTargetCandidate.length > 0 && self.dnd_isInTarget( self.$dropTargetCandidate, evt ) ) {
                        self.$dropTargetCandidate.addClass('sek-drag-is-in');
                  }
                  self.enterOverTimer = null;
            },
            dnd_getPreDropElementContent : function( evt ) {
                  var $target = $( evt.currentTarget ),
                      html,
                      preDropContent;

                  switch( this.dndData.content_type ) {
                        case 'module' :
                              html = sektionsLocalizedData.i18n['Insert here'];
                              if ( $target.length > 0 ) {
                                  if ( 'between-sections' === $target.data('sek-location') || 'in-empty-location' === $target.data('sek-location') ) {
                                        html = sektionsLocalizedData.i18n['Insert in a new section'];
                                  }
                              }
                              preDropContent = '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                        break;

                        case 'preset_section' :
                              html = sektionsLocalizedData.i18n['Insert a new section here'];
                              preDropContent = '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                        break;

                        default :
                              api.errare( '::dnd_getPreDropElementContent => invalid content type provided');
                        break;
                  }
                  return preDropContent;
            },
            dnd_getDropZonesElements : function() {
                  return $( api.previewer.targetWindow().document );
            },
            dnd_canDrop : function( params ) {
                  params = _.extend( { targetEl : {}, evt : {} }, params || {} );
                  var self = this, $dropTarget = params.targetEl;
                  if ( ! _.isObject( $dropTarget ) || 1 > $dropTarget.length )
                    return false;

                  var isSectionDropZone   = $dropTarget.hasClass( 'sek-content-preset_section-drop-zone' ),
                      sectionHasNoModule  = $dropTarget.hasClass( 'sek-module-drop-zone-for-first-module' ),
                      isHeaderLocation    = true === $dropTarget.closest('[data-sek-level="location"]').data('sek-is-header-location'),
                      isFooterLocation    = true === $dropTarget.closest('[data-sek-level="location"]').data('sek-is-footer-location'),
                      isContentSectionCandidate = 'preset_section' === self.dndData.content_type && 'content' === self.dndData.section_type,
                      msg;

                  var maybePrintErrorMessage = function( msg ) {
                        if ( $('.sek-no-drop-possible-message', $dropTarget ).length < 1 ) {
                              $dropTarget.append([
                                    '<div class="sek-no-drop-possible-message">',
                                      '<i class="material-icons">not_interested</i>',
                                      msg,
                                    '</div>'
                              ].join(''));
                        }
                  };

                  if ( ( isHeaderLocation || isFooterLocation ) && isContentSectionCandidate ) {
                        msg = isHeaderLocation ? sektionsLocalizedData.i18n['The header location only accepts modules and pre-built header sections'] : sektionsLocalizedData.i18n['The footer location only accepts modules and pre-built footer sections'];
                        maybePrintErrorMessage( msg );
                        return false;
                  }
                  if ( isFooterLocation && 'preset_section' === self.dndData.content_type && 'header' === self.dndData.section_type ) {
                        msg = sektionsLocalizedData.i18n['You can\'t drop a header section in the footer location'];
                        maybePrintErrorMessage( msg );
                        return false;
                  }

                  if ( isHeaderLocation && 'preset_section' === self.dndData.content_type && 'footer' === self.dndData.section_type ) {
                        msg = sektionsLocalizedData.i18n['You can\'t drop a footer section in the header location'];
                        maybePrintErrorMessage( msg );
                        return false;
                  }

                  return $dropTarget.hasClass('sek-drop-zone') && ( ( 'preset_section' === self.dndData.content_type && isSectionDropZone ) || ( 'module' === self.dndData.content_type && ! isSectionDropZone ) || ( 'preset_section' === self.dndData.content_type && sectionHasNoModule ) );
            },
            dnd_OnEnterOver : function( $dropTarget, evt ) {
                  evt.preventDefault();//@see :https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#droptargets
                  if ( true !== $dropTarget.data( 'is-drag-entered' ) ) {
                        $dropTarget.data( 'is-drag-entered', true );
                        $dropTarget.addClass( 'sek-active-drop-zone' );
                        this.$dropZones.addClass( 'sek-is-dragging' );
                  }

                  try { this.dnd_mayBePrintPreDropElement( $dropTarget, evt ); } catch( er ) {
                        api.errare('Error when trying to insert the preDrop content', er );
                  }
            },
            dnd_cleanOnLeaveDrop : function( $dropTarget, evt ) {
                  var self = this;
                  this.$dropZones = this.$dropZones || this.dnd_getDropZonesElements();
                  this.preDropElement.remove();
                  this.$dropZones.removeClass( 'sek-is-dragging' );

                  $( sektionsLocalizedData.dropSelectors, this.$dropZones ).each( function() {
                        self.dnd_cleanSingleDropTarget( $(this) );
                  });
            },
            dnd_cleanSingleDropTarget : function( $dropTarget ) {
                  if ( _.isEmpty( $dropTarget ) || $dropTarget.length < 1 )
                    return;
                  $dropTarget.data( 'is-drag-entered', false );
                  $dropTarget.data( 'preDrop-position', false );
                  $dropTarget.removeClass( 'sek-active-drop-zone' );
                  $dropTarget.find('.sek-drop-zone').removeClass('sek-drag-is-close');
                  $dropTarget.find('.sek-drop-zone').removeClass('sek-drag-is-approaching');

                  $dropTarget.removeClass('sek-feed-me-seymore');

                  $dropTarget.find('.sek-no-drop-possible-message').remove();
            },
            dnd_getPosition : function( $dropTarget, evt ) {
                  var targetRect = $dropTarget[0].getBoundingClientRect(),
                      targetHeight = targetRect.height;
                  if ( 'before' === $dropTarget.data( 'preDrop-position' ) ) {
                        targetHeight = targetHeight + this.preDropElement.outerHeight();
                  } else if ( 'after' === $dropTarget.data( 'preDrop-position' ) ) {
                        targetHeight = targetHeight - this.preDropElement.outerHeight();
                  }

                  return evt.originalEvent.clientY - targetRect.top - ( targetHeight / 2 ) > 0  ? 'after' : 'before';
            },
            dnd_mayBePrintPreDropElement : function( $dropTarget, evt ) {
                  var self = this,
                      previousPosition = $dropTarget.data( 'preDrop-position' ),
                      newPosition = this.dnd_getPosition( $dropTarget, evt  );

                  if ( previousPosition === newPosition )
                    return;

                  if ( true === self.isPrintingPreDrop ) {
                        return;
                  }

                  self.isPrintingPreDrop = true;
                  this.dnd_cleanSingleDropTarget( this.$currentPreDropTarget );
                  var inNewSection = 'between-sections' === $dropTarget.data('sek-location') || 'in-empty-location' === $dropTarget.data('sek-location');
                  $.when( self.preDropElement.remove() ).done( function(){
                        $dropTarget[ 'before' === newPosition ? 'prepend' : 'append' ]( self.preDropElement )
                              .find( '.' + sektionsLocalizedData.preDropElementClass ).html( self.dnd_getPreDropElementContent( evt ) );
                        $dropTarget.find( '.' + sektionsLocalizedData.preDropElementClass ).toggleClass('in-new-sektion', inNewSection );
                        $dropTarget.data( 'preDrop-position', newPosition );

                        $dropTarget.addClass('sek-feed-me-seymore');

                        self.isPrintingPreDrop = false;
                        self.$currentPreDropTarget = $dropTarget;
                  });
            },
            dnd_isOveringDropTarget : function( $dropTarget, evt ) {
                  var targetRect = $dropTarget[0].getBoundingClientRect(),
                      mouseX = evt.clientX,
                      mouseY = evt.clientY,
                      tLeft = targetRect.left,
                      tRight = targetRect.right,
                      tTop = targetRect.top,
                      tBottom = targetRect.bottom,
                      isXin = mouseX >= tLeft && ( tRight - tLeft ) >= ( mouseX - tLeft),
                      isYin = mouseY >= tTop && ( tBottom - tTop ) >= ( mouseY - tTop);
                  return isXin && isYin;
            },
            dnd_onDrop: function( $dropTarget, evt ) {
                  evt.stopPropagation();
                  var _position = 'after' === this.dnd_getPosition( $dropTarget, evt ) ? $dropTarget.index() + 1 : $dropTarget.index();
                  api.czr_sektions.trigger( 'sek-content-dropped', {
                        drop_target_element : $dropTarget,
                        location : $dropTarget.closest('[data-sek-level="location"]').data('sek-id'),
                        before_module : $dropTarget.data('drop-zone-before-module-or-nested-section'),
                        after_module : $dropTarget.data('drop-zone-after-module-or-nested-section'),
                        before_section : $dropTarget.data('drop-zone-before-section'),
                        after_section : $dropTarget.data('drop-zone-after-section'),

                        content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                        content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" ),

                        section_type : evt.originalEvent.dataTransfer.getData( "sek-section-type" ),
                        is_user_section : "true" === evt.originalEvent.dataTransfer.getData( "sek-is-user-section" )
                  });
            },
            reactToDrop : function() {
                  var self = this;
                  var _do_ = function( params ) {
                        if ( ! _.isObject( params ) ) {
                              throw new Error( 'Invalid params provided' );
                        }
                        if ( params.drop_target_element.length < 1 ) {
                              throw new Error( 'Invalid drop_target_element' );
                        }

                        var $dropTarget = params.drop_target_element,
                            dropCase = 'content-in-column';
                        switch( $dropTarget.data('sek-location') ) {
                              case 'between-sections' :
                                    dropCase = 'content-in-a-section-to-create';
                              break;
                              case 'in-empty-location' :
                                    params.is_first_section = true;
                                    params.send_to_preview = false;
                                    dropCase = 'content-in-empty-location';
                              break;
                              case 'between-columns' :
                                    dropCase = 'content-in-new-column';
                              break;
                        }
                        if ( 'preset_section' === params.content_type ) {
                              if ( $dropTarget.hasClass( 'sek-module-drop-zone-for-first-module' ) ) {
                                    var $parentSektion = $dropTarget.closest('div[data-sek-level="section"]');
                                    var colNumber = $parentSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).length;
                                    if ( colNumber > 1 ) {
                                          dropCase = 'preset-section-in-a-nested-section-to-create';
                                          params.is_nested = true;
                                          params.in_column = $dropTarget.closest('[data-sek-level="column"]').data('sek-id');
                                          params.in_sektion = $parentSektion.data('sek-id');
                                    } else {
                                          params.sektion_to_replace = $parentSektion.data('sek-id');
                                          params.after_section = params.sektion_to_replace;
                                          params.in_column = $parentSektion.closest('[data-sek-level="column"]').data('sek-id');
                                          dropCase = 'content-in-a-section-to-replace';
                                    }
                              } else {
                                    if ( 'between-sections' === $dropTarget.data('sek-location') ) {
                                          dropCase = 'content-in-a-section-to-create';
                                    }
                              }



                        }

                        var focusOnAddedContentEditor;
                        switch( dropCase ) {
                              case 'content-in-column' :
                                    var $closestLevelWrapper = $dropTarget.closest('div[data-sek-level]');
                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'No valid level dom element found' );
                                    }
                                    var _level = $closestLevelWrapper.data( 'sek-level' ),
                                        _id = $closestLevelWrapper.data('sek-id');

                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'No valid level id found' );
                                    }

                                    api.previewer.trigger( 'sek-add-module', {
                                          level : _level,
                                          id : _id,
                                          in_column : $dropTarget.closest('div[data-sek-level="column"]').data( 'sek-id'),
                                          in_sektion : $dropTarget.closest('div[data-sek-level="section"]').data( 'sek-id'),

                                          before_module : params.before_module,
                                          after_module : params.after_module,

                                          content_type : params.content_type,
                                          content_id : params.content_id
                                    });
                              break;

                              case 'content-in-a-section-to-create' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;
                              case 'content-in-a-section-to-replace' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;
                              case 'preset-section-in-a-nested-section-to-create' :
                                    api.previewer.trigger( 'sek-add-preset-section-in-new-nested-sektion', params );
                              break;
                              case 'content-in-empty-location' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;

                              default :
                                    api.errare( 'sek control panel => ::reactToDrop => invalid drop case : ' + dropCase );
                              break;
                        }
                  };
                  this.bind( 'sek-content-dropped', function( params ) {
                        try { _do_( params ); } catch( er ) {
                              api.errare( 'error when reactToDrop', er );
                        }
                  });
            }//reactToDrop
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            /* This code is inpired from the plugin customize-posts, GPLv2 or later licensed
                Credits : xwp, westonruter, valendesigns, sayedwp, utkarshpatel.
                Date of original code modification : July 2018
            */
            setupTinyMceEditor: function() {
                  var self = this;
                  api.sekEditorExpanded   = new api.Value( false );
                  api.sekEditorSynchronizedInput = new api.Value();

                  self.editorEventsListenerSetup = false;//this status will help us ensure that we bind the shared tinyMce instance only once
                  var mayBeAwakeTinyMceEditor = function() {
                        api.sekTinyMceEditor = api.sekTinyMceEditor || tinyMCE.get( 'czr-customize-content_editor' );

                        if ( false === self.editorEventsListenerSetup ) {
                              self.attachEventsToEditor();
                              self.editorEventsListenerSetup = true;
                              self.trigger('sek-tiny-mce-editor-bound-and-instantiated');
                        }
                  };
                  self.bind( 'sek-edit-module_done', function( params ) {
                        params = _.isObject( params ) ? params : {};
                        if ( 'tiny_mce_editor' !== params.clicked_input_type && 'czr_tiny_mce_editor_module' !== params.module_type )
                          return;
                        if ( _.isEmpty( params.syncedTinyMceInputId ) )
                          return;

                        var controlId = params.id;
                        if ( true === self.getRegisteredModuleProperty( params.module_type, 'is_father' ) ) {
                              var _childModules_ = self.getRegisteredModuleProperty( params.module_type, 'children' );
                              if ( _.isEmpty( _childModules_ ) ) {
                                    throw new Error('::generateUIforFrontModules => a father module ' + params.module_type + ' is missing children modules ');
                              } else {
                                    _.each( _childModules_, function( mod_type, optionType ){
                                          if ( 'czr_tinymce_child' === mod_type ) {
                                                controlId = controlId + '__' + optionType;//<= as defined when generating the ui in ::generateUIforFrontModules
                                          }
                                    });
                              }
                        }
                        api.sekEditorSynchronizedInput({
                              control_id : controlId,
                              input_id : params.syncedTinyMceInputId
                        });

                        api.sekEditorExpanded( true );
                        api.sekTinyMceEditor.focus();
                  });
                  $('#customize-theme-controls').on('click', '[data-czr-action="open-tinymce-editor"]', function() {
                        var control_id = $(this).data('czr-control-id'),
                            input_id = $(this).data('czr-input-id');
                        if ( _.isEmpty( control_id ) || _.isEmpty( input_id ) ) {
                              api.errare('toggle-tinymce-editor => missing input or control id');
                              return;
                        }
                        var currentEditorSyncData = $.extend( true, {}, api.sekEditorSynchronizedInput() ),
                            newEditorSyncData = _.extend( currentEditorSyncData, {
                                  input_id : input_id,
                                  control_id : control_id
                            });
                        api.sekEditorSynchronizedInput( newEditorSyncData );
                        api.sekEditorExpanded( true );
                        api.sekTinyMceEditor.focus();
                  });
                  api.sekEditorSynchronizedInput.bind( function( to, from ) {
                        mayBeAwakeTinyMceEditor();
                        api( to.control_id, function( _setting_ ) {
                              var _currentModuleValue_ = _setting_(),
                                  _currentInputContent_ = ( _.isObject( _currentModuleValue_ ) && ! _.isEmpty( _currentModuleValue_[ to.input_id ] ) ) ? _currentModuleValue_[ to.input_id ] : '';
                              _currentInputContent_ = _currentInputContent_.replace(/\r?\n/g, '<br/>');
                              try { api.sekTinyMceEditor.setContent( _currentInputContent_ ); } catch( er ) {
                                    api.errare( 'Error when setting the tiny mce editor content in setupTinyMceEditor', er );
                              }
                              api.sekTinyMceEditor.focus();
                        });

                  });//api.sekEditorSynchronizedInput.bind( function( to, from )
                  api.sekEditorExpanded.bind( function ( expanded, from, params ) {
                        mayBeAwakeTinyMceEditor();
                        if ( expanded ) {
                              api.sekTinyMceEditor.focus();
                        }
                        $(document.body).toggleClass( 'czr-customize-content_editor-pane-open', expanded);

                        /*
                        * Ensure only the latest input is bound
                        */

                        $( window )[ expanded ? 'on' : 'off' ]('resize', function() {
                                if ( ! api.sekEditorExpanded() )
                                  return;
                                _.delay( function() {
                                      self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                                }, 50 );

                        });

                        if ( expanded ) {
                              self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                        } else {
                              self.$preview.css( 'bottom', '' );
                              self.$collapseSidebar.css( 'bottom', '' );
                        }
                  });
                  $('#czr-customize-content_editor-pane' ).on('click', '[data-czr-action="close-tinymce-editor"]', function() {
                        api.sekEditorExpanded( false );
                  });
                  $('#customize-controls' ).on('click', function( evt ) {
                        if ( 'open-tinymce-editor' == $( evt.target ).data( 'czr-action') )
                          return;
                        api.sekEditorExpanded( false, { context : "clicked anywhere"} );
                  });
                  $(document).on( 'keydown', _.throttle( function( evt ) {
                        if ( 27 === evt.keyCode ) {
                              api.sekEditorExpanded( false );
                        }
                  }, 50 ));

                  self.bind('sek-tiny-mce-editor-bound-and-instantiated', function() {
                        var iframeDoc = $( api.sekTinyMceEditor.iframeElement ).contents().get(0);
                        $( iframeDoc ).on('keydown', _.throttle( function( evt ) {
                              if ( 27 === evt.keyCode ) {
                                    api.sekEditorExpanded( false );
                              }
                        }, 50 ));
                  });

                  _.each( [
                        'sek-click-on-inactive-zone',
                        'sek-add-section',
                        'sek-add-column',
                        'sek-add-module',
                        'sek-remove',
                        'sek-move',
                        'sek-duplicate',
                        'sek-resize-columns',
                        'sek-add-content-in-new-sektion',
                        'sek-pick-content',
                        'sek-edit-options',
                        'sek-edit-module',
                        'sek-notify'
                  ], function( _evt_ ) {
                        if ( 'sek-edit-module' != _evt_ ) {
                              api.previewer.bind( _evt_, function() { api.sekEditorExpanded( false ); } );
                        } else {
                              api.previewer.bind( _evt_, function( params ) {
                                    api.sekEditorExpanded(  params.module_type === 'czr_tiny_mce_editor_module' );
                              });
                        }
                  });
            },//setupTinyMceEditor




            attachEventsToEditor : function() {
                  var self = this;
                  self.$editorTextArea = $( '#czr-customize-content_editor' );
                  self.$editorPane = $( '#czr-customize-content_editor-pane' );
                  self.$editorDragbar = $( '#czr-customize-content_editor-dragbar' );
                  self.$editorFrame  = $( '#czr-customize-content_editor_ifr' );
                  self.$mceTools     = $( '#wp-czr-customize-content_editor-tools' );
                  self.$mceToolbar   = self.$editorPane.find( '.mce-toolbar-grp' );
                  self.$mceStatusbar = self.$editorPane.find( '.mce-statusbar' );

                  self.$preview = $( '#customize-preview' );
                  self.$collapseSidebar = $( '.collapse-sidebar' );
                  api.sekTinyMceEditor.on( 'input change keyup', function( evt ) {
                        if ( api.control.has( api.sekEditorSynchronizedInput().control_id ) ) {
                              try { api.control( api.sekEditorSynchronizedInput().control_id )
                                    .trigger( 'tinyMceEditorUpdated', {
                                          input_id : api.sekEditorSynchronizedInput().input_id,
                                          html_content : api.sekTinyMceEditor.getContent(),
                                          modified_editor_element : api.sekTinyMceEditor
                                    });
                              } catch( er ) {
                                    api.errare( 'Error when triggering tinyMceEditorUpdated', er );
                              }
                        }
                  });
                  self.$editorTextArea.on( 'input', function( evt ) {
                        try { api.control( api.sekEditorSynchronizedInput().control_id )
                              .trigger( 'tinyMceEditorUpdated', {
                                    input_id : api.sekEditorSynchronizedInput().input_id,
                                    html_content : self.$editorTextArea.val(),
                                    modified_editor_element : self.$editorTextArea
                              });
                        } catch( er ) {
                              api.errare( 'Error when triggering tinyMceEditorUpdated', er );
                        }
                  });
                  $('#czr-customize-content_editor-pane').on( 'mousedown mouseup', function( evt ) {
                        if ( 'mousedown' === evt.type && 'czr-customize-content_editor-dragbar' !== $(evt.target).attr('id') && ! $(evt.target).hasClass('czr-resize-handle') )
                          return;
                        if ( ! api.sekEditorExpanded() )
                          return;
                        switch( evt.type ) {
                              case 'mousedown' :
                                    $( document ).on( 'mousemove.czr-customize-content_editor', function( event ) {
                                          event.preventDefault();
                                          $( document.body ).addClass( 'czr-customize-content_editor-pane-resize' );
                                          self.$editorFrame.css( 'pointer-events', 'none' );
                                          self.czrResizeEditor( event.pageY );
                                    });
                              break;

                              case 'mouseup' :
                                    $( document ).off( 'mousemove.czr-customize-content_editor' );
                                    $( document.body ).removeClass( 'czr-customize-content_editor-pane-resize' );
                                    self.$editorFrame.css( 'pointer-events', '' );
                              break;
                        }
                  });
            },





            czrResizeEditor : function( position ) {
              var self = this,
                  windowHeight = window.innerHeight,
                  windowWidth = window.innerWidth,
                  minScroll = 40,
                  maxScroll = 1,
                  mobileWidth = 782,
                  collapseMinSpacing = 56,
                  collapseBottomOutsideEditor = 8,
                  collapseBottomInsideEditor = 4,
                  args = {},
                  resizeHeight;

              if ( ! api.sekEditorExpanded() ) {
                return;
              }

              if ( ! _.isNaN( position ) ) {
                    resizeHeight = windowHeight - position;
              }

              args.height = resizeHeight;
              args.components = self.$mceTools.outerHeight() + self.$mceToolbar.outerHeight() + self.$mceStatusbar.outerHeight();

              if ( resizeHeight < minScroll ) {
                    args.height = minScroll;
              }

              if ( resizeHeight > windowHeight - maxScroll ) {
                    args.height = windowHeight - maxScroll;
              }

              if ( windowHeight < self.$editorPane.outerHeight() ) {
                    args.height = windowHeight;
              }

              self.$preview.css( 'bottom', args.height );
              self.$editorPane.css( 'height', args.height );
              self.$editorFrame.css( 'height', args.height - args.components );
              self.$collapseSidebar.css(
                    'bottom',
                    collapseMinSpacing > windowHeight - args.height ? self.$mceStatusbar.outerHeight() + collapseBottomInsideEditor : args.height + collapseBottomOutsideEditor
              );
      }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, api.Events );
      var CZR_SeksConstructor   = api.Class.extend( CZRSeksPrototype );
      try { api.czr_sektions = new CZR_SeksConstructor(); } catch( er ) {
            api.errare( 'api.czr_sektions => problem on instantiation', er );
      }
})( wp.customize, jQuery );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      var validateUnit = function( unit ) {
            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                  api.errare( 'error : invalid unit for input ' + this.id, unit );
                  unit = 'px';
            }
            return unit;
          },
          stripUnit = function( value ) {
                return _.isString( value ) ? value.replace(/px|em|%/g,'') : '';
          },
          unitButtonsSetup = function( $wrapper ) {
                var input = this;
                $wrapper.on( 'click', '.sek-ui-button', function( evt, params ) {
                      evt.preventDefault();
                      $wrapper.find('.sek-ui-button').removeClass('is-selected').attr( 'aria-pressed', false );
                      $(this).addClass('is-selected').attr( 'aria-pressed', true );
                      input.css_unit( $(this).data('sek-unit'), params );
                });
                $wrapper.find( '.sek-ui-button[data-sek-unit="'+ ( input.initial_unit || 'px' ) +'"]').addClass('is-selected').attr( 'aria-pressed', true );
          },
          setupResetAction = function( $wrapper, defaultVal ) {
                var input = this;
                $wrapper.on( 'click', '.reset-spacing-wrap', function(evt) {
                      evt.preventDefault();
                      $wrapper.find('input[type="number"]').each( function() {
                            $(this).val('');
                      });

                      input( defaultVal );
                      $('.sek-unit-wrapper', $wrapper ).find('[data-sek-unit="px"]').trigger('click');
                });
          };



      /* ------------------------------------------------------------------------- *
       *  SPACING CLASSIC
      /* ------------------------------------------------------------------------- */
      $.extend( api.czrInputMap, {
            spacing : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-spacing-wrapper', input.container ),
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : [];
                  $wrapper.on( 'input', 'input[type="number"]', function(evt) {
                        var _type_ = $(this).closest('[data-sek-spacing]').data('sek-spacing'),
                            _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                            _rawVal = $(this).val();
                        if ( ( _.isString( _rawVal ) && ! _.isEmpty( _rawVal ) ) || _.isNumber( _rawVal ) ) {
                              _newInputVal[ _type_ ] = _rawVal;
                        } else {
                              _newInputVal = _.omit( _newInputVal, _type_ );
                        }
                        input( _newInputVal );
                  });
                  setupResetAction.call( input, $wrapper, defaultVal );
                  if ( _.isObject( input() ) ) {
                        _.each( input(), function( _val_, _key_ ) {
                              $( '[data-sek-spacing="' + _key_ +'"]', $wrapper ).find( 'input[type="number"]' ).val( _val_ );
                        });
                        var unitToActivate = 'px';
                        $('.sek-unit-wrapper .sek-ui-button', input.container ).each( function() {
                              var unit = $(this).data('sek-unit');
                              if ( ! _.isEmpty( input() ) ) {
                                    if ( ! _.isEmpty( input()[ 'unit' ] ) ) {
                                          if ( unit === input()[ 'unit' ] ) {
                                                unitToActivate = unit;
                                          }
                                    }
                              }
                        });
                        $('.sek-unit-wrapper', input.container ).find('[data-sek-unit="' + validateUnit.call( input, unitToActivate ) + '"]').trigger('click');
                  }
                  var initial_value = input();
                  input.initial_unit = 'px';
                  if ( ! _.isEmpty( initial_value )  ) {
                        input.initial_unit = _.isEmpty( initial_value['unit'] ) ? 'px' : initial_value['unit'];
                  }
                  input.css_unit = new api.Value( validateUnit.call( input, input.initial_unit ) );
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        var _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ 'unit' ] = to;
                        input( _newInputVal );
                  });
                  unitButtonsSetup.call( input, $wrapper );
            }
      });//$.extend( api.czrInputMap, {})















      /* ------------------------------------------------------------------------- *
       *  SPACING WITH DEVICE SWITCHER
      /* ------------------------------------------------------------------------- */
      $.extend( api.czrInputMap, {
            spacingWithDeviceSwitcher : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-spacing-wrapper', input.container ),
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};
                  var getCurrentDeviceActualOrInheritedValue = function( inputValues, currentDevice ) {
                        var deviceHierarchy = [ 'mobile' , 'tablet', 'desktop' ];
                        if ( _.has( inputValues, currentDevice ) ) {
                              return inputValues[ currentDevice ];
                        } else {
                              var deviceIndex = _.findIndex( deviceHierarchy, function( _d_ ) { return currentDevice === _d_; });
                              if ( ! _.isEmpty( currentDevice ) && deviceIndex < deviceHierarchy.length ) {
                                    return getCurrentDeviceActualOrInheritedValue( inputValues, deviceHierarchy[ deviceIndex + 1 ] );
                              } else {
                                    return {};
                              }
                        }
                  };
                  var syncWithPreviewedDevice = function( currentDevice ) {
                        var inputValues = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                            clonedDefault = $.extend( true, {}, defaultVal );
                        inputValues = _.isObject( inputValues ) ? $.extend( clonedDefault, inputValues ) : clonedDefault;
                        var _currentDeviceValues = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice );
                        $( '[data-sek-spacing]', $wrapper ).each( function() {
                              var spacingType = $(this).data('sek-spacing'),
                                  _val_ = '';
                              if ( ! _.isEmpty( _currentDeviceValues ) ) {
                                    if ( ! _.isEmpty( _currentDeviceValues[ spacingType ] ) ) {
                                          _val_ = _currentDeviceValues[ spacingType ];
                                    }
                              }
                              $(this).find( 'input[type="number"]' ).val( _val_ );
                        });
                        var unitToActivate = 'px';
                        $( '.sek-unit-wrapper .sek-ui-button', input.container).each( function() {
                              var unit = $(this).data('sek-unit');
                              if ( ! _.isEmpty( _currentDeviceValues ) ) {
                                    if ( ! _.isEmpty( _currentDeviceValues[ 'unit' ] ) ) {
                                          if ( unit === _currentDeviceValues[ 'unit' ] ) {
                                                unitToActivate = unit;
                                          }
                                    }
                              }
                        });
                        $('.sek-unit-wrapper', input.container ).find('[data-sek-unit="' + validateUnit.call( input, unitToActivate ) + '"]').trigger('click', { previewed_device_switched : true });// We don't want to update the input();
                  };
                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );
                  var initial_value = input();
                  input.initial_unit = 'px';
                  if ( ! _.isEmpty( initial_value ) && ! _.isEmpty( initial_value[ input.previewedDevice() ] ) ) {
                        input.initial_unit = _.isEmpty( initial_value[ input.previewedDevice() ]['unit'] ) ? 'px' : initial_value[ input.previewedDevice() ]['unit'];
                  }
                  input.css_unit = new api.Value( validateUnit.call( input, input.initial_unit ) );
                  $wrapper.on( 'input', 'input[type="number"]', function(evt) {
                        var changedSpacingType    = $(this).closest('[data-sek-spacing]').data('sek-spacing'),
                            changedNumberInputVal = $(this).val(),
                            _newInputVal,
                            previewedDevice = api.previewedDevice() || 'desktop';

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ previewedDevice ] = $.extend( true, {}, _newInputVal[ previewedDevice ] || {} );
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) || _.isNumber( changedNumberInputVal ) ) {
                              _newInputVal[ previewedDevice ][ changedSpacingType ] = changedNumberInputVal;
                        } else {
                              _newInputVal[ previewedDevice ] = _.omit( _newInputVal[ previewedDevice ], changedSpacingType );
                        }

                        input( _newInputVal );
                  });
                  setupResetAction.call( input, $wrapper, defaultVal );
                  input.previewedDevice.bind( function( currentDevice ) {
                        try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                              api.errare('Error when firing syncWithPreviewedDevice for input type spacingWithDeviceSwitcher for input id ' + input.id , er );
                        }
                  });
                  input.css_unit.bind( function( to, from, params ) {
                        if ( _.isObject( params ) && true === params.previewed_device_switched )
                          return;
                        to = _.isEmpty( to ) ? 'px' : to;
                        var _newInputVal,
                            previewedDevice = input.previewedDevice() || 'desktop';

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ previewedDevice ] = $.extend( true, {}, _newInputVal[ previewedDevice ] || {} );
                        _newInputVal[ previewedDevice ][ 'unit' ] = to;
                        input( _newInputVal );
                  });
                  unitButtonsSetup.call( input, $wrapper );
                  try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type spacingWithDeviceSwitcher for input id ' + input.id , er );
                  }
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            /* ------------------------------------------------------------------------- *
             *  BG POSITION SIMPLE
            /* ------------------------------------------------------------------------- */
            bg_position : function( input_options ) {
                  var input = this;
                  $('.sek-bg-pos-wrapper', input.container ).on( 'change', 'input[type="radio"]', function(evt) {
                        input( $(this).val() );
                  });
                  if ( ! _.isEmpty( input() ) ) {
                        input.container.find('input[value="'+ input() +'"]').attr('checked', true).trigger('click');
                  }
            },


            /* ------------------------------------------------------------------------- *
             *  BG POSITION WITH DEVICE SWITCHER
            /* ------------------------------------------------------------------------- */
            bgPositionWithDeviceSwitcher : function( input_options ) {
                  var input = this,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};
                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );

                  var getCurrentDeviceActualOrInheritedValue = function( inputValues, currentDevice ) {
                        var deviceHierarchy = [ 'mobile' , 'tablet', 'desktop' ];
                        if ( _.has( inputValues, currentDevice ) ) {
                              return inputValues[ currentDevice ];
                        } else {
                              var deviceIndex = _.findIndex( deviceHierarchy, function( _d_ ) { return currentDevice === _d_; });
                              if ( ! _.isEmpty( currentDevice ) && deviceIndex < deviceHierarchy.length ) {
                                    return getCurrentDeviceActualOrInheritedValue( inputValues, deviceHierarchy[ deviceIndex + 1 ] );
                              } else {
                                    return {};
                              }
                        }
                  };
                  var syncWithPreviewedDevice = function( currentDevice ) {
                        var inputValues = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                            clonedDefault = $.extend( true, {}, defaultVal );
                        inputValues = _.isObject( inputValues ) ? $.extend( clonedDefault, inputValues ) : clonedDefault;
                        var _currentDeviceValue = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice );

                        input.container.find('input[value="'+ _currentDeviceValue +'"]').attr('checked', true).trigger('click', { previewed_device_switched : true } );
                  };
                  $('.sek-bg-pos-wrapper', input.container ).on( 'change', 'input[type="radio"]', function( evt ) {
                        var changedRadioVal = $(this).val(),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ api.previewedDevice() || 'desktop' ] = changedRadioVal;

                        input( _newInputVal );
                  });
                  input.previewedDevice.bind( function( currentDevice ) {
                        try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                              api.errare('Error when firing syncWithPreviewedDevice for input type spacingWithDeviceSwitcher for input id ' + input.id , er );
                        }
                  });
                  try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type bgPositionWithDeviceSwitcher for input id ' + input.id , er );
                  }
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      var x_or_y_AlignWithDeviceSwitcher = function( params ) {
            var input = this,
                inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {},
                tmplSelector = 'verticalAlignWithDeviceSwitcher' === input.type ? '.sek-v-align-wrapper' : '.sek-h-align-wrapper',// <= because used by 2 different input tmpl
                $wrapper = $( tmplSelector, input.container );
            api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );

            var getCurrentDeviceActualOrInheritedValue = function( inputValues, currentDevice ) {
                  var deviceHierarchy = [ 'mobile' , 'tablet', 'desktop' ];
                  if ( _.has( inputValues, currentDevice ) ) {
                        return inputValues[ currentDevice ];
                  } else {
                        var deviceIndex = _.findIndex( deviceHierarchy, function( _d_ ) { return currentDevice === _d_; });
                        if ( ! _.isEmpty( currentDevice ) && deviceIndex < deviceHierarchy.length ) {
                              return getCurrentDeviceActualOrInheritedValue( inputValues, deviceHierarchy[ deviceIndex + 1 ] );
                        } else {
                              return {};
                        }
                  }
            };
            var syncWithPreviewedDevice = function( currentDevice ) {
                  var inputValues = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                      clonedDefault = $.extend( true, {}, defaultVal );
                  inputValues = _.isObject( inputValues ) ? $.extend( clonedDefault, inputValues ) : clonedDefault;
                  var _currentDeviceValue = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice );
                  $wrapper.find('.selected').removeClass('selected');
                  $wrapper.find( 'div[data-sek-align="' + _currentDeviceValue +'"]' ).addClass('selected');
            };
            $wrapper.on( 'click', '[data-sek-align]', function(evt) {
                  evt.preventDefault();
                  var _newInputVal;

                  _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                  _newInputVal[ api.previewedDevice() || 'desktop' ] = $(this).data('sek-align');

                  $wrapper.find('.selected').removeClass('selected');
                  $.when( $(this).addClass('selected') ).done( function() {
                        input( _newInputVal );
                  });
            });
            input.previewedDevice.bind( function( currentDevice ) {
                  try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type : ' + input.type + ' for input id ' + input.id , er );
                  }
            });
            try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                  api.errare('Error when firing syncWithPreviewedDevice for input type : ' + input.type + ' for input id ' + input.id , er );
            }
      };
      $.extend( api.czrInputMap, {
            horizTextAlignmentWithDeviceSwitcher : x_or_y_AlignWithDeviceSwitcher,
            horizAlignmentWithDeviceSwitcher : x_or_y_AlignWithDeviceSwitcher,
            verticalAlignWithDeviceSwitcher : x_or_y_AlignWithDeviceSwitcher
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            font_size : function( params ) {
                  api.czr_sektions.setupFontSizeAndLineHeightInputs.call(this);
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            line_height : function( params ) {
                  api.czr_sektions.setupFontSizeAndLineHeightInputs.call(this);
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            font_picker : function( input_options ) {
                  var input = this,
                      item = input.input_parent;

                  var _getFontCollections = function() {
                        var dfd = $.Deferred();
                        if ( ! _.isEmpty( api.sek_fontCollections ) ) {
                              dfd.resolve( api.sek_fontCollections );
                        } else {
                              var _ajaxRequest_;
                              if ( ! _.isUndefined( api.sek_fetchingFontCollection ) && 'pending' == api.sek_fetchingFontCollection.state() ) {
                                    _ajaxRequest_ = api.sek_fetchingFontCollection;
                              } else {
                                    _ajaxRequest_ = api.CZR_Helpers.getModuleTmpl( {
                                          tmpl : 'font_list',
                                          module_type: 'font_picker_input',
                                          module_id : input.module.id
                                    } );
                                    api.sek_fetchingFontCollection = _ajaxRequest_;
                              }
                              _ajaxRequest_.done( function( _serverTmpl_ ) {
                                    if ( typeof _serverTmpl_ !== 'string' || _serverTmpl_[0] !== '{' ) {
                                          throw new Error( 'font_picker => server list is not JSON.parse-able');
                                    }
                                    api.sek_fontCollections = JSON.parse( _serverTmpl_ );
                                    dfd.resolve( api.sek_fontCollections );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });

                        }
                        return dfd.promise();
                  };
                  var _preprocessSelect2ForFontFamily = function() {
                        /*
                        * Override czrSelect2 Results Adapter in order to select on highlight
                        * deferred needed cause the selects needs to be instantiated when this override is complete
                        * selec2.amd.require is asynchronous
                        */
                        var selectFocusResults = $.Deferred();
                        if ( 'undefined' !== typeof $.fn.czrSelect2 && 'undefined' !== typeof $.fn.czrSelect2.amd && 'function' === typeof $.fn.czrSelect2.amd.require ) {
                              $.fn.czrSelect2.amd.require(['czrSelect2/results', 'czrSelect2/utils'], function (Result, Utils) {
                                    var ResultsAdapter = function($element, options, dataAdapter) {
                                      ResultsAdapter.__super__.constructor.call(this, $element, options, dataAdapter);
                                    };
                                    Utils.Extend(ResultsAdapter, Result);
                                    ResultsAdapter.prototype.bind = function (container, $container) {
                                      var _self = this;
                                      container.on('results:focus', function (params) {
                                        if ( params.element.attr('aria-selected') != 'true') {
                                          _self.trigger('select', {
                                              data: params.data
                                          });
                                        }
                                      });
                                      ResultsAdapter.__super__.bind.call(this, container, $container);
                                    };
                                    selectFocusResults.resolve( ResultsAdapter );
                              });
                        }
                        else {
                              selectFocusResults.resolve( false );
                        }

                        return selectFocusResults.promise();

                  };//_preprocessSelect2ForFontFamily
                  var _setupSelectForFontFamilySelector = function( customResultsAdapter, fontCollections ) {
                        var _model = item(),
                            _googleFontsFilteredBySubset = function() {
                                  var subset = item.czr_Input('subset')(),
                                      filtered = _.filter( fontCollections.gfonts, function( data ) {
                                            return data.subsets && _.contains( data.subsets, subset );
                                      });

                                  if ( ! _.isUndefined( subset ) && ! _.isNull( subset ) && 'all-subsets' != subset ) {
                                        return filtered;
                                  } else {
                                        return fontCollections.gfonts;
                                  }

                            },
                            $fontSelectElement = $( 'select[data-czrtype="' + input.id + '"]', input.container );
                        var _generateFontOptions = function( fontList, type ) {
                              var _html_ = '';
                              _.each( fontList , function( font_data ) {
                                    var _value = font_data.name,
                                        optionTitle = _.isString( _value ) ? _value.replace(/[+|:]/g, ' ' ) : _value,
                                        _setFontTypePrefix = function( val, type ) {
                                              return _.isString( val ) ? [ '[', type, ']', val ].join('') : '';//<= Example : [gfont]Aclonica:regular
                                        };

                                    _value = _setFontTypePrefix( _value, type );

                                    if ( _value == input() ) {
                                          _html_ += '<option selected="selected" value="' + _value + '">' + optionTitle + '</option>';
                                    } else {
                                          _html_ += '<option value="' + _value + '">' + optionTitle + '</option>';
                                    }
                              });
                              return _html_;
                        };
                        if ( _.isNull( input() ) || _.isEmpty( input() ) ) {
                              $fontSelectElement.append( '<option value="none" selected="selected">' + sektionsLocalizedData.i18n['Select a font family'] + '</option>' );
                        } else {
                              $fontSelectElement.append( '<option value="none">' + sektionsLocalizedData.i18n['Select a font family'] + '</option>' );
                        }
                        _.each( [
                              {
                                    title : sektionsLocalizedData.i18n['Web Safe Fonts'],
                                    type : 'cfont',
                                    list : fontCollections.cfonts
                              },
                              {
                                    title : sektionsLocalizedData.i18n['Google Fonts'],
                                    type : 'gfont',
                                    list : fontCollections.gfonts//_googleFontsFilteredBySubset()
                              }
                        ], function( fontData ) {
                              var $optGroup = $('<optgroup>', { label : fontData.title , html : _generateFontOptions( fontData.list, fontData.type ) });
                              $fontSelectElement.append( $optGroup );
                        });

                        var _fonts_czrSelect2_params = {
                            escapeMarkup: function(m) { return m; },
                        };
                        /*
                        * Maybe use custom adapter
                        */
                        if ( customResultsAdapter ) {
                              $.extend( _fonts_czrSelect2_params, {
                                    resultsAdapter: customResultsAdapter,
                                    closeOnSelect: false,
                              } );
                        }
                        $fontSelectElement.czrSelect2( _fonts_czrSelect2_params );
                        $( '.czrSelect2-selection__rendered', input.container ).css( getInlineFontStyle( input() ) );

                  };//_setupSelectForFontFamilySelector
                  var getInlineFontStyle = function( _fontFamily_ ){
                        if ( ! _.isString( _fontFamily_ ) || _.isEmpty( _fontFamily_ ) )
                          return {};
                        _fontFamily_ = _fontFamily_.replace('[gfont]', '').replace('[cfont]', '');

                        var module = this,
                            split = _fontFamily_.split(':'), font_family, font_weight, font_style;

                        font_family       = getFontFamilyName( _fontFamily_ );

                        font_weight       = split[1] ? split[1].replace( /[^0-9.]+/g , '') : 400; //removes all characters
                        font_weight       = _.isNumber( font_weight ) ? font_weight : 400;
                        font_style        = ( split[1] && -1 != split[1].indexOf('italic') ) ? 'italic' : '';


                        return {
                              'font-family' : 'none' == font_family ? 'inherit' : font_family.replace(/[+|:]/g, ' '),//removes special characters
                              'font-weight' : font_weight || 400,
                              'font-style'  : font_style || 'normal'
                        };
                  };
                  var getFontFamilyName = function( rawFontFamily ) {
                        if ( ! _.isString( rawFontFamily ) || _.isEmpty( rawFontFamily ) )
                            return rawFontFamily;

                        rawFontFamily = rawFontFamily.replace('[gfont]', '').replace('[cfont]', '');
                        var split         = rawFontFamily.split(':');
                        return _.isString( split[0] ) ? split[0].replace(/[+|:]/g, ' ') : '';//replaces special characters ( + ) by space
                  };

                  $.when( _getFontCollections() ).done( function( fontCollections ) {
                        _preprocessSelect2ForFontFamily().done( function( customResultsAdapter ) {
                              _setupSelectForFontFamilySelector( customResultsAdapter, fontCollections );
                        });
                  }).fail( function( _r_ ) {
                        api.errare( 'font_picker => fail response =>', _r_ );
                  });
            }//font_picker()
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            fa_icon_picker : function() {
                  var input           = this,
                      _selected_found = false;
                  var _generateOptions = function( iconCollection ) {
                        _.each( iconCollection , function( iconClass ) {
                              var _attributes = {
                                    value: iconClass,
                                    html: api.CZR_Helpers.capitalize( iconClass.substring( 7 ) )
                              };

                              if ( _attributes.value == input() ) {
                                    $.extend( _attributes, { selected : "selected" } );
                                    _selected_found = true;
                              }
                              $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                        });


                        var addIcon = function ( state ) {
                              if (! state.id) { return state.text; }
                              var  $state = $(
                                '<span class="' + state.element.value + '"></span><span class="social-name">&nbsp;&nbsp;' + state.text + '</span>'
                              );
                              return $state;
                        };
                        var $_placeholder;
                        if ( _selected_found ) {
                              $_placeholder = $('<option>');
                        } else {
                              $_placeholder = $('<option>', { selected: 'selected' } );
                        }
                        $( 'select[data-czrtype]', input.container )
                            .prepend( $_placeholder )
                            .czrSelect2({
                                  templateResult: addIcon,
                                  templateSelection: addIcon,
                                  placeholder: sektionsLocalizedData.i18n['Select an icon'],
                                  allowClear: true
                            });
                  };//_generateOptions


                  var _getIconsCollections = function() {
                        return $.Deferred( function( _dfd_ ) {
                              if ( ! _.isEmpty( input.sek_faIconCollection ) ) {
                                    _dfd_.resolve( input.sek_faIconCollection );
                              } else {
                                    api.CZR_Helpers.getModuleTmpl( {
                                          tmpl : 'icon_list',
                                          module_type: 'fa_icon_picker_input',
                                          module_id : input.module.id
                                    } ).done( function( _serverTmpl_ ) {
                                          if ( typeof _serverTmpl_ !== 'string' || _serverTmpl_[0] !== '[' ) {
                                                throw new Error( 'fa_icon_picker => server list is not JSON.parse-able');
                                          }
                                          input.sek_faIconCollection = JSON.parse( _serverTmpl_ );
                                          _dfd_.resolve( input.sek_faIconCollection );
                                    }).fail( function( _r_ ) {
                                          _dfd_.reject( _r_ );
                                    });
                              }
                        });
                  };//_getIconsCollections
                  var _do_ = function( params ) {
                        if ( true === input.iconCollectionSet )
                          return;
                        $.when( _getIconsCollections() ).done( function( iconCollection ) {
                              _generateOptions( iconCollection );
                              if ( params && true === params.open_on_init ) {
                                    _.delay( function() {
                                          try{ $( 'select[data-czrtype]', input.container ).czrSelect2('open'); }catch(er) {}
                                    }, 100 );
                              }
                        }).fail( function( _r_ ) {
                              api.errare( 'fa_icon_picker => fail response =>', _r_ );
                        });
                        input.iconCollectionSet = true;
                  };
                  input.container.on('click', function() {
                        _do_();
                  });
                  _.delay( function() { _do_( { open_on_init : false } );}, 1000 );

            }
      });//$.extend( api.czrInputMap, {})

})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            code_editor : function( input_options ) {
                  var input          = this,
                      control        = this.module.control,
                      item           = input.input_parent(),
                      editorSettings = false,
                      $textarea      = input.container.find( 'textarea' ),
                      $input_title   = input.container.find( '.customize-control-title' ),
                      editor_params  = $textarea.data( 'editor-params' );
                  if ( wp.codeEditor  && ( _.isUndefined( editor_params ) || false !== editor_params )  ) {
                        editorSettings = editor_params;
                  }

                  input.isReady.done( function() {
                        var _doInstantiate = function( evt ) {
                              var input = this;
                              if ( ! _.isEmpty( input.editor ) )
                                return;
                              if ( _.isEmpty( input.module.control.container.attr('data-sek-expanded') ) || "false" == input.module.control.container.attr('data-sek-expanded') )
                                return;

                              setTimeout( function() {
                                    if ( editorSettings ) {
                                          try { initSyntaxHighlightingEditor( editorSettings ); } catch( er ) {
                                                api.errare( 'error in sek_control => code_editor() input', er );
                                                initPlainTextareaEditor();
                                          }
                                    } else {
                                          initPlainTextareaEditor();
                                    }
                                   $input_title.click();
                              }, 10 );
                        };
                        _doInstantiate.call(input);
                        input.module.control.container.on('sek-accordion-expanded', function() {
                              _doInstantiate.call( input );
                        });
                  });


                  /**
                   * Initialize syntax-highlighting editor.
                   */
                  var initSyntaxHighlightingEditor = function( codeEditorSettings ) {
                        var suspendEditorUpdate = false,
                            settings;

                        settings = _.extend( {}, codeEditorSettings, {
                              onTabNext: CZRSeksPrototype.selectNextTabbableOrFocusable( ':tabbable' ),
                              onTabPrevious: CZRSeksPrototype.selectPrevTabbableOrFocusable( ':tabbable' ),
                              onUpdateErrorNotice: onUpdateErrorNotice
                        });

                        input.editor = wp.codeEditor.initialize( $textarea, settings );
                        $( input.editor.codemirror.display.lineDiv )
                              .attr({
                                    role: 'textbox',
                                    'aria-multiline': 'true',
                                    'aria-label': $input_title.html(),
                                    'aria-describedby': 'editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4'
                              });
                        $input_title.on( 'click', function( evt ) {
                              evt.stopPropagation();
                              input.editor.codemirror.focus();
                        });


                        /*
                         * When the CodeMirror instance changes, mirror to the textarea,
                         * where we have our "true" change event handler bound.
                         */
                        input.editor.codemirror.on( 'change', function( codemirror ) {
                              suspendEditorUpdate = true;
                              $textarea.val( codemirror.getValue() ).trigger( 'change' );
                              suspendEditorUpdate = false;
                        });

                        input.editor.codemirror.setValue( input() );
                        /* TODO: check this */
                        input.bind( input.id + ':changed', function( value ) {
                              if ( ! suspendEditorUpdate ) {
                                    input.editor.codemirror.setValue( value );
                              }
                        });
                        input.editor.codemirror.on( 'keydown', function onKeydown( codemirror, event ) {
                              var escKeyCode = 27;
                              if ( escKeyCode === event.keyCode ) {
                                    event.stopPropagation();
                              }
                        });
                  };



                  /**
                   * Initialize plain-textarea editor when syntax highlighting is disabled.
                   */
                  var initPlainTextareaEditor = function() {
                        var textarea  = $textarea[0];
                        input.editor = textarea;//assign the editor property
                        $textarea.on( 'blur', function onBlur() {
                              $textarea.data( 'next-tab-blurs', false );
                        } );

                        $textarea.on( 'keydown', function onKeydown( event ) {
                              var selectionStart, selectionEnd, value, tabKeyCode = 9, escKeyCode = 27;

                              if ( escKeyCode === event.keyCode ) {
                                    if ( ! $textarea.data( 'next-tab-blurs' ) ) {
                                          $textarea.data( 'next-tab-blurs', true );
                                          event.stopPropagation(); // Prevent collapsing the section.
                                    }
                                    return;
                              }
                              if ( tabKeyCode !== event.keyCode || event.ctrlKey || event.altKey || event.shiftKey ) {
                                    return;
                              }
                              if ( $textarea.data( 'next-tab-blurs' ) ) {
                                    return;
                              }

                              selectionStart = textarea.selectionStart;
                              selectionEnd = textarea.selectionEnd;
                              value = textarea.value;

                              if ( selectionStart >= 0 ) {
                                    textarea.value = value.substring( 0, selectionStart ).concat( '\t', value.substring( selectionEnd ) );
                                    $textarea.selectionStart = textarea.selectionEnd = selectionStart + 1;
                              }

                              event.stopPropagation();
                              event.preventDefault();
                        });
                  },



                  /**
                   * Update error notice.
                   */
                  onUpdateErrorNotice = function( errorAnnotations ) {
                        var message;

                        control.setting.notifications.remove( input.id );
                        if ( 0 !== errorAnnotations.length ) {
                              if ( 1 === errorAnnotations.length ) {
                                    message = sektionsLocalizedData.i18n.codeEditorSingular.replace( '%d', '1' ).replace( '%s', $input_title.html() );
                              } else {
                                    message = sektionsLocalizedData.i18n.codeEditorPlural.replace( '%d', String( errorAnnotations.length ) ).replace( '%s', $input_title.html() );
                              }
                              control.setting.notifications.add( input.id, new api.Notification( input.id, {
                                    message: message,
                                    type: 'warning'
                              } ) );
                        }
                  }
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            range_simple : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]');
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  $numberInput.on('input', function( evt ) {
                        input( $(this).val() );
                        $rangeInput.val( $(this).val() );
                  });
                  $rangeInput.val( $numberInput.val() || 0 );
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            range_with_unit_picker : function( params ) {
                  var input = this,
                  $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                  $numberInput = $wrapper.find( 'input[type="number"]'),
                  $rangeInput = $wrapper.find( 'input[type="range"]'),
                  initial_unit = $wrapper.find('input[data-czrtype]').data('sek-unit'),
                  validateUnit = function( unit ) {
                        if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                              api.errare( 'error : invalid unit for input ' + input.id, unit );
                              unit = 'px';
                        }
                        return unit;
                  };
                  input.css_unit = new api.Value( _.isEmpty( initial_unit ) ? 'px' : validateUnit( initial_unit ) );
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        $wrapper.find( 'input[type="number"]').trigger('input');
                  });
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  $numberInput.on('input', function( evt ) {
                        input( $(this).val() + validateUnit( input.css_unit() ) );
                        $rangeInput.val( $(this).val() );
                  });
                  $rangeInput.val( $numberInput.val() || 0 );
                  $wrapper.on( 'click', '.sek-ui-button', function(evt) {
                        evt.preventDefault();
                        $wrapper.find('.sek-ui-button').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        input.css_unit( $(this).data('sek-unit') );
                  });
                  $wrapper.find( '.sek-ui-button[data-sek-unit="'+ initial_unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            range_with_unit_picker_device_switcher : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'range_with_unit_picker_device_switcher => error : invalid unit for input ' + input.id, unit );
                                  unit = 'px';
                            }
                            return unit;
                      },
                      _extractNumericVal = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? '16' : _rawVal.replace(/px|em|%/g,'');
                      },
                      _extractUnit = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? 'px' : _rawVal.replace(/[0-9]|\.|,/g, '');
                      },
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  var getInitialUnit = function() {
                        return $wrapper.find('input[data-czrtype]').data('sek-unit') || 'px';
                  };
                  var getCurrentDeviceActualOrInheritedValue = function( inputValues, currentDevice ) {
                        var deviceHierarchy = [ 'mobile' , 'tablet', 'desktop' ];
                        if ( _.has( inputValues, currentDevice ) ) {
                              return inputValues[ currentDevice ];
                        } else {
                              var deviceIndex = _.findIndex( deviceHierarchy, function( _d_ ) { return currentDevice === _d_; });
                              if ( ! _.isEmpty( currentDevice ) && deviceIndex < deviceHierarchy.length ) {
                                    return getCurrentDeviceActualOrInheritedValue( inputValues, deviceHierarchy[ deviceIndex + 1 ] );
                              } else {
                                    var clonedDefault = $.extend( true, { desktop : '' }, defaultVal );
                                    return clonedDefault[ 'desktop' ];
                              }
                        }
                  };
                  var syncWithPreviewedDevice = function( currentDevice ) {
                        var inputVal = input(), inputValues = {}, clonedDefault = $.extend( true, {}, defaultVal );
                        inputValues = clonedDefault;
                        if ( _.isObject( inputVal ) ) {
                              inputValues = $.extend( true, {}, inputVal );
                        } else if ( _.isString( inputVal ) && ! _.isEmpty( inputVal ) ) {
                              inputValues = { desktop : inputVal };
                        }
                        var _rawVal = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice ),
                            _unit = _extractUnit( _rawVal ),
                            _numberVal = _extractNumericVal( _rawVal );
                        $('.sek-unit-wrapper', $wrapper).find('[data-sek-unit="' + _unit +'"]').trigger('click', { previewed_device_switched : true });// We don't want to update the input()
                        $wrapper.find( '.sek-ui-button[data-sek-unit="'+ _unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
                        $numberInput.val(  _numberVal  ).trigger('input', { previewed_device_switched : true });// We don't want to update the input()
                  };
                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );
                  input.css_unit = new api.Value( _.isEmpty( getInitialUnit() ) ? 'px' : validateUnit( getInitialUnit() ) );
                  var resetButton = '<button type="button" class="button sek-reset-button sek-float-right">' + sektionsLocalizedData.i18n['Reset'] + '</button>';
                  input.container.find('.customize-control-title').append( resetButton );
                  input.css_unit.bind( function( to, from, params ) {
                        if ( _.isObject( params ) && true === params.previewed_device_switched )
                          return;
                        $numberInput.trigger('input');
                  });
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  $numberInput.on('input', function( evt, params ) {
                        var previewedDevice = api.previewedDevice() || 'desktop',
                            changedNumberInputVal = $(this).val() + validateUnit( input.css_unit() ),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ previewedDevice ] = $.extend( true, {}, _newInputVal[ previewedDevice ] || {} );
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) ) {
                              _newInputVal[ previewedDevice ]= changedNumberInputVal;
                        }
                        if ( _.isEmpty( params ) || ( _.isObject( params ) && true !== params.previewed_device_switched ) ) {
                              input( _newInputVal );
                        }
                        $rangeInput.val( $(this).val() );
                  });
                  $wrapper.on( 'click', '.sek-ui-button', function( evt, params ) {
                        evt.stopPropagation();
                        $wrapper.find('.sek-ui-button').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        input.css_unit( $(this).data('sek-unit'), params );
                  });
                  input.previewedDevice.bind( function( currentDevice ) {
                        try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                              api.errare('Error when firing syncWithPreviewedDevice for input type range_with_unit_picker_device_switcher for input id ' + input.id , er );
                        }
                  });
                  input.container.on( 'click', '.sek-reset-button', function( evt ) {
                        var _currentDevice = api.previewedDevice(),
                            _newVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        if ( !_.isEmpty( _newVal[ _currentDevice ] ) ) {
                              _newVal = _.omit( _newVal, _currentDevice );
                              input( _newVal );
                              syncWithPreviewedDevice( api.previewedDevice() );
                        }
                  });
                  $rangeInput.val( $numberInput.val() || 0 );
                  try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type range_with_unit_picker_device_switcher for input id ' + input.id , er );
                  }
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            borders : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-borders', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]'),
                      $colorInput = $wrapper.find('.sek-alpha-color-input'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'borders => error : invalid unit for input ' + input.id, unit );
                                  unit = 'px';
                            }
                            return unit;
                      },
                      _extractNumericVal = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? '16' : _rawVal.replace(/px|em|%/g,'');
                      },
                      _extractUnit = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? 'px' : _rawVal.replace(/[0-9]|\.|,/g, '');
                      },
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  input.cssBorderTypes = [ 'top', 'left', 'right', 'bottom' ];
                  var getInitialUnit = function() {
                        var inputVal = input(), initial_unit = 'px';
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) && _.isObject( inputVal['_all_'] ) && ! _.isEmpty( inputVal['_all_'][ 'wght'] ) ) {
                              initial_unit = validateUnit( _extractUnit( inputVal['_all_'][ 'wght'] ) );
                        }
                        return initial_unit;
                  };
                  var getInitialWeight = function() {
                        var inputVal = input(), initial_weight = 1;
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) && _.isObject( inputVal['_all_'] ) && ! _.isEmpty( inputVal['_all_'][ 'wght'] ) ) {
                              initial_weight = _extractNumericVal( inputVal['_all_'][ 'wght'] );
                        }
                        initial_weight = parseInt(initial_weight, 10);
                        if ( ! _.isNumber( initial_weight ) || initial_weight < 0 ) {
                              api.errare( 'Error in borders input type for module : ' + input.module.module_type + ' the initial border width is invalid : ' + initial_weight );
                              initial_weight = 1;
                        }
                        return initial_weight;
                  };
                  var getInitialColor = function() {
                        var inputVal = input(), initial_color = '#000000';
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) && _.isObject( inputVal['_all_'] ) && ! _.isEmpty( inputVal['_all_'][ 'col'] ) ) {
                              initial_color = inputVal['_all_'][ 'col'];
                        }
                        return initial_color;
                  };
                  var getCurrentBorderTypeOrAllValue = function( inputValues, borderType ) {
                        var clonedDefaults = $.extend( true, {}, defaultVal ), _all_Value;
                        if ( ! _.has( clonedDefaults, '_all_' ) ) {
                            throw new Error( "Error when firing getCurrentBorderTypeOrAllValue : the default value of the borders input must be php registered as an array formed : array( 'wght' => '1px', 'col' => '#000000' )");
                        }

                        _all_Value =  ( _.isObject( inputValues ) && _.has( inputValues, '_all_' ) ) ? _.extend( clonedDefaults['_all_'], inputValues[ '_all_' ] ) : clonedDefaults['_all_'];
                        if ( _.has( inputValues, borderType ) && _.isObject( inputValues[ borderType ] ) ) {
                              return _.extend( _all_Value, inputValues[ borderType ] );
                        } else {
                              return clonedDefaults['_all_'];
                        }
                  };
                  var syncWithBorderType = function( borderType ) {
                        if ( ! _.contains( _.union( input.cssBorderTypes, [ '_all_' ] ) , borderType ) ) {
                              throw new Error( "Error in syncWithBorderType : the border type must be one of those values '_all_', 'top', 'left', 'right', 'bottom'" );
                        }
                        var inputVal = input(), inputValues = {}, clonedDefault = $.extend( true, {}, defaultVal );
                        if ( _.isObject( inputVal ) ) {
                              inputValues = $.extend( true, {}, inputVal );
                        } else if ( _.isString( inputVal ) ) {
                              inputValues = { _all_ : { wght : inputVal } };
                        }
                        inputValues = $.extend( clonedDefault, inputValues );
                        var _rawVal = getCurrentBorderTypeOrAllValue( inputValues, borderType ), _unit, _numberVal;
                        if ( _.isEmpty( _rawVal ) || ! _.isObject( _rawVal ) || _.isEmpty( _rawVal.wght ) || _.isEmpty( _rawVal.col ) ) {
                              throw new Error( "Error in syncWithBorderType : getCurrentBorderTypeOrAllValue must return an object formed : array( 'wght' => '1px', 'col' => '#000000' )");
                        }

                        _unit = _extractUnit( _rawVal.wght );
                        _numberVal = _extractNumericVal( _rawVal.wght );
                        $('.sek-unit-wrapper', $wrapper).find('[data-sek-unit="' + _unit +'"]').trigger('click', { border_type_switched : true });// We don't want to update the input()
                        $wrapper.find( '.sek-ui-button[data-sek-unit="'+ _unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
                        $numberInput.val( _numberVal ).trigger('input', { border_type_switched : true });// We don't want to update the input()
                        $colorInput.data('border_type_switched', true );
                        $colorInput.val( _rawVal.col ).trigger( 'change' );
                        $colorInput.data('border_type_switched', false );
                  };
                  input.borderColor = new api.Value( _.isEmpty( getInitialColor() ) ? '#000000' : getInitialColor() );
                  input.css_unit = new api.Value( _.isEmpty( getInitialUnit() ) ? 'px' : validateUnit( getInitialUnit() ) );
                  input.borderType = new api.Value( '_all_');
                  $numberInput.val( getInitialWeight() );
                  $colorInput.val( input.borderColor() );
                  $colorInput.wpColorPicker({
                        palettes: true,
                        width: window.innerWidth >= 1440 ? 271 : 251,
                        change : function( evt, o ) {
                              $(this).val( o.color.toString() ).trigger('colorpickerchange');
                              input.borderColor( o.color.toString(), { border_type_switched : true === $(this).data('border_type_switched') } );
                        },
                        clear : function( e, o ) {
                              $(this).val('').trigger('colorpickerchange');
                              input.borderColor('');
                        }
                  });
                  input.css_unit.bind( function( to, from, params ) {
                        if ( _.isObject( params ) && ( true === params.border_type_switched || true === params.initializing_the_unit ) )
                          return;
                        $numberInput.trigger('input', params);
                  });
                  input.borderColor.bind( function( to, from, params ) {
                        if ( _.isObject( params ) && ( true === params.border_type_switched || true === params.initializing_the_color ) )
                          return;
                        $numberInput.trigger('input', params);
                  });
                  input.borderType.bind( function( borderType ) {
                        try { syncWithBorderType( borderType ); } catch( er ) {
                              api.errare('Error when firing syncWithBorderType for input type borders for module type ' + input.module.module_type , er );
                        }
                  });
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  $numberInput.on('input', function( evt, params ) {
                        var currentBorderType = input.borderType() || '_all_',
                            currentColor = input.borderColor(),
                            changedNumberInputVal = $(this).val() + validateUnit( input.css_unit() ),
                            clonedDefaults = $.extend( true, {}, defaultVal ),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : clonedDefaults );
                        _newInputVal[ currentBorderType ] = $.extend( true, {}, _newInputVal[ currentBorderType ] || clonedDefaults[ currentBorderType ] );
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) ) {
                              _newInputVal[ currentBorderType ][ 'wght' ] = changedNumberInputVal;
                        }
                        _newInputVal[ currentBorderType ][ 'col' ] = currentColor;
                        if ( _.isEmpty( params ) || ( _.isObject( params ) && true !== params.border_type_switched ) ) {
                              if ( '_all_' === currentBorderType ) {
                                    _.each( input.cssBorderTypes, function( _type ) {
                                          _newInputVal = _.omit( _newInputVal, _type );
                                    });
                              }
                              input( _newInputVal );
                        }
                        $rangeInput.val( $(this).val() );
                  });
                  $wrapper.on( 'click', '[data-sek-unit]', function( evt, params ) {
                        evt.preventDefault();
                        $wrapper.find('[data-sek-unit]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        input.css_unit( $(this).data('sek-unit'), params );
                  });
                  $wrapper.on( 'click', '[data-sek-border-type]', function( evt, params ) {
                        evt.preventDefault();
                        $wrapper.find('[data-sek-border-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        var border = '_all_';
                        try { border = $(this).data('sek-border-type'); } catch( er ) {
                              api.errare( 'borders input type => error when attaching click event', er );
                        }
                        input.borderType( border, params );
                  });
                  input.container.on( 'click', '.sek-reset-button', function( evt ) {
                        var currentBorderType = input.borderType() || '_all_',
                            _newVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        if ( !_.isEmpty( _newVal[ currentBorderType ] ) ) {
                              _newVal = _.omit( _newVal, currentBorderType );
                              input( _newVal );
                              syncWithBorderType( currentBorderType );
                        }
                  });
                  $rangeInput.val( $numberInput.val() || 0 );
                  try { syncWithBorderType( input.borderType() ); } catch( er ) {
                        api.errare('Error when firing syncWithBorderType for input type borders for module type ' + input.module.module_type , er );
                  }
                  $( '[data-sek-unit="' + input.css_unit() + '"]', $wrapper ).trigger('click', { initializing_the_unit : true } );
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            border_radius : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-borders', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'border_radius => error : invalid unit for input ' + input.id, unit );
                                  unit = 'px';
                            }
                            return unit;
                      },
                      _extractNumericVal = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? '16' : _rawVal.replace(/px|em|%/g,'');
                      },
                      _extractUnit = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? 'px' : _rawVal.replace(/[0-9]|\.|,/g, '');
                      },
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  input.cssRadiusTypes = [ 'top_left','top_right','bottom_right','bottom_left' ];
                  var getInitialUnit = function() {
                        var inputVal = input(), initial_unit = 'px';
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) ) {
                              initial_unit = validateUnit( _extractUnit( inputVal['_all_'] ) );
                        }
                        return initial_unit;
                  };
                  var getInitialRadius = function() {
                        var inputVal = input(), initial_rad = 0;
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) ) {
                              initial_rad = _extractNumericVal( inputVal['_all_'] );
                        }
                        initial_rad = parseInt(initial_rad, 10);
                        if ( ! _.isNumber( initial_rad ) || initial_rad < 0 ) {
                              api.errare( 'Error in border_radius input type for module : ' + input.module.module_type + ' the initial radius is invalid : ' + initial_rad );
                              initial_rad = 0;
                        }
                        return initial_rad;
                  };
                  var getCurrentRadiusTypeOrAllValue = function( inputValues, radiusType ) {
                        var clonedDefaults = $.extend( true, {}, defaultVal ), _all_Value;
                        if ( ! _.has( clonedDefaults, '_all_' ) ) {
                            throw new Error( "Error when firing getCurrentRadiusTypeOrAllValue : the default value of the border_radius input must be php registered as an array");
                        }

                        _all_Value =  ( _.isObject( inputValues ) && _.has( inputValues, '_all_' ) ) ? inputValues[ '_all_' ] : clonedDefaults['_all_'];
                        if ( _.has( inputValues, radiusType ) ) {
                              return inputValues[ radiusType ];
                        } else {
                              return _all_Value;
                        }
                  };
                  var syncWithRadiusType = function( radiusType ) {
                        if ( ! _.contains( [ '_all_', 'top_left', 'top_right', 'bottom_right', 'bottom_left' ], radiusType ) ) {
                              throw new Error( "Error in syncWithRadiusType : the radius type must be one of those values '_all_', 'top_left', 'top_right', 'bottom_right', 'bottom_left', => radius type => " + radiusType );
                        }
                        var inputVal = input(), inputValues = {}, clonedDefault = $.extend( true, {}, defaultVal );
                        if ( _.isObject( inputVal ) ) {
                              inputValues = $.extend( true, {}, inputVal );
                        } else if ( _.isString( inputVal ) ) {
                              inputValues = { _all_ : '0px' };
                        }
                        inputValues = $.extend( clonedDefault, inputValues );
                        var _rawVal = getCurrentRadiusTypeOrAllValue( inputValues, radiusType ), _unit, _numberVal;
                        if ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) {
                              throw new Error( "Error in syncWithRadiusType : getCurrentRadiusTypeOrAllValue must return a string like 3em");
                        }

                        _unit = _extractUnit( _rawVal );
                        _numberVal = _extractNumericVal( _rawVal );
                        $('.sek-unit-wrapper', $wrapper).find('[data-sek-unit="' + _unit +'"]').trigger('click', { radius_type_switched : true });// We don't want to update the input()
                        $wrapper.find( '.sek-ui-button[data-sek-unit="'+ _unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
                        $numberInput.val( _numberVal ).trigger('input', { radius_type_switched : true });// We don't want to update the input()
                  };
                  input.css_unit = new api.Value( _.isEmpty( getInitialUnit() ) ? 'px' : validateUnit( getInitialUnit() ) );
                  input.radiusType = new api.Value('_all_');
                  $numberInput.val( getInitialRadius() );
                  input.css_unit.bind( function( to, from, params ) {
                        if ( _.isObject( params ) && ( true === params.radius_type_switched || true === params.initializing_the_unit ) )
                          return;
                        $numberInput.trigger('input', params);
                  });
                  input.radiusType.bind( function( radiusType ) {
                        try { syncWithRadiusType( radiusType ); } catch( er ) {
                              api.errare('Error when firing syncWithRadiusType for input type border_radius for module type ' + input.module.module_type , er );
                        }
                  });
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  $numberInput.on('input', function( evt, params ) {
                        var currentRadiusType = input.radiusType() || '_all_',
                            changedNumberInputVal = $(this).val() + validateUnit( input.css_unit() ),
                            clonedDefaults = $.extend( true, {}, defaultVal ),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : clonedDefaults );
                        _newInputVal[ currentRadiusType ] = $.extend( true, {}, _newInputVal[ currentRadiusType ] || clonedDefaults[ currentRadiusType ] );
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) ) {
                              _newInputVal[ currentRadiusType ] = changedNumberInputVal;
                        }
                        if ( _.isEmpty( params ) || ( _.isObject( params ) && true !== params.radius_type_switched ) ) {
                              if ( '_all_' === currentRadiusType ) {
                                    _.each( input.cssRadiusTypes, function( _type ) {
                                          _newInputVal = _.omit( _newInputVal, _type );
                                    });
                              }
                              input( _newInputVal );
                        }
                        $rangeInput.val( $(this).val() );
                  });
                  $wrapper.on( 'click', '[data-sek-unit]', function( evt, params ) {
                        evt.preventDefault();
                        $wrapper.find('[data-sek-unit]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        input.css_unit( $(this).data('sek-unit'), params );
                  });
                  $wrapper.on( 'click', '[data-sek-radius-type]', function( evt, params ) {
                        evt.preventDefault();
                        $wrapper.find('[data-sek-radius-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        var border = '_all_';
                        try { border = $(this).data('sek-radius-type'); } catch( er ) {
                              api.errare( 'border_radius input type => error when attaching click event', er );
                        }
                        input.radiusType( border, params );
                  });
                  input.container.on( 'click', '.sek-reset-button', function( evt ) {
                        var currentRadiusType = input.radiusType() || '_all_',
                            _newVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        if ( !_.isEmpty( _newVal[ currentRadiusType ] ) ) {
                              _newVal = _.omit( _newVal, currentRadiusType );
                              input( _newVal );
                              syncWithRadiusType( currentRadiusType );
                        }
                  });
                  $rangeInput.val( $numberInput.val() || 0 );
                  try { syncWithRadiusType( input.radiusType() ); } catch( er ) {
                        api.errare('Error when firing syncWithRadiusType for input type border_radius for module type ' + input.module.module_type , er );
                  }
                  $( '[data-sek-unit="' + input.css_unit() + '"]', $wrapper ).trigger('click', { initializing_the_unit : true } );
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            buttons_choice : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-button-choice-wrapper', input.container ),
                      $mainInput = $wrapper.find( 'input[type="number"]'),
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};
                  $mainInput.val( input() );
                  $wrapper.on( 'click', '[data-sek-choice]', function( evt, params ) {
                        evt.stopPropagation();
                        $wrapper.find('[data-sek-choice]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        var newChoice;
                        try { newChoice = $(this).data('sek-choice'); } catch( er ) {
                              api.errare( 'buttons_choice input type => error when attaching click event', er );
                        }
                        input( newChoice );
                  });
                  $( '[data-sek-choice="' + input() + '"]', $wrapper ).trigger('click', { initializing_the_unit : true } );
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );//global sektionsLocalizedData
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            reset_button : function( params ) {
                  var input = this;
                  input.container.on( 'click', '[data-sek-reset-scope]', function( evt, params ) {
                        evt.stopPropagation();
                        var scope = $(this).data( 'sek-reset-scope' );
                        if ( 'local' === scope ) {
                              try { api.czr_sektions.resetCollectionSetting(); } catch( er ) {
                                    api.errare( 'reset_button => error when firing resetCollectionSetting() on click event', er );
                              }
                        }
                  });
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );//global sektionsLocalizedData, serverControlParams
/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_content_type_switcher_module : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_content_type_switcher_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_content_type_switcher_module' )
                  )
            },
      });

      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            content_type_switcher : function( input_options ) {
                  var input = this,
                      _section_,
                      initial_content_type;

                  if ( ! api.section.has( input.module.control.section() ) ) {
                        throw new Error( 'api.czrInputMap.content_type_switcher => section not registered' );
                  }
                  _section_ = api.section( input.module.control.section() );
                  input.container.on('click', '[data-sek-content-type]', function( evt ) {
                        evt.preventDefault();
                        input.container.find('[data-sek-content-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        api.czr_sektions.currentContentPickerType( $(this).data( 'sek-content-type') );
                  });


                  var _do_ = function( contentType ) {
                        input.container.find( '[data-sek-content-type="' + ( contentType || 'module' ) + '"]').trigger('click');
                        _.each( _section_.controls(), function( _control_ ) {
                              if ( ! _.isUndefined( _control_.content_type ) ) {
                                    _control_.active( contentType === _control_.content_type );
                              }
                        });
                  };
                  api.czr_sektions.currentContentPickerType = api.czr_sektions.currentContentPickerType || new api.Value( input() );
                  _do_( api.czr_sektions.currentContentPickerType() );
                  api.czr_sektions.currentContentPickerType.bind( function( contentType ) {
                        _do_( contentType );
                  });
            }
      });
})( wp.customize , jQuery, _ );





/* ------------------------------------------------------------------------- *
 *  MODULE PICKER MODULE
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_module_picker_module : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_module_picker_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel :  _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_module_picker_module' )
                  )
            },
      });

      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            module_picker : function( input_options ) {
                var input = this;
                api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'module', input_container : input.container } );
            }
      });
})( wp.customize , jQuery, _ );



/* ------------------------------------------------------------------------- *
 *  SECTION PICKER MODULES
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      var section_modules = [
            'sek_intro_sec_picker_module',
            'sek_features_sec_picker_module',
            'sek_contact_sec_picker_module',
            'sek_column_layouts_sec_picker_module'
      ];
      if ( sektionsLocalizedData.isNimbleHeaderFooterEnabled ) {
            section_modules = _.union( section_modules, [ 'sek_header_sec_picker_module','sek_footer_sec_picker_module' ] );
      }
      _.each( section_modules, function( module_type ) {
            api.czrModuleMap[ module_type ] = {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( module_type, 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( module_type )
                  )
            };
      });
})( wp.customize , jQuery, _ );






/* ------------------------------------------------------------------------- *
 *  MY SECTIONS MODULE
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        initialize : function( name, options ) {
                              var input = this;
                              api.CZRInput.prototype.initialize.call( input, name, options );
                              input.isReady.then( function() {
                                    input.renderUserSavedSections();
                                    api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'preset_section', input_container : input.container } );
                              });
                        },


                        renderUserSavedSections : function() {
                              var input = this,
                                  html = '',
                                  $wrapper = input.container.find('.sek-content-type-wrapper'),
                                  creation_date = '',
                                  formatDate = function(date) {
                                      var monthNames = [
                                          "January", "February", "March",
                                          "April", "May", "June", "July",
                                          "August", "September", "October",
                                          "November", "December"
                                      ];

                                      var day = date.getDate(),
                                          monthIndex = date.getMonth(),
                                          year = date.getFullYear(),
                                          hours = date.getHours(),
                                          minutes = date.getMinutes(),
                                          seconds = date.getSeconds();

                                      return [
                                            day,
                                            monthNames[monthIndex],
                                            year
                                      ].join(' ');
                                  };

                              _.each( sektionsLocalizedData.userSavedSektions, function( secData, secKey ) {
                                    try { creation_date = formatDate( new Date( secData.creation_date.replace( /-/g, '/' ) ) ); } catch( er ) {
                                          api.errare( '::renderUserSavedSections => formatDate => error', er );
                                    }
                                    html = [
                                          '<div class="sek-user-section-wrapper">',
                                            '<div class="sek-saved-section-title"><i class="sek-remove-user-section far fa-trash-alt"></i>' + secData.title + '</div>',
                                            '<div draggable="true" data-sek-is-user-section="true" data-sek-section-type="' + secData.type +'" data-sek-content-type="preset_section" data-sek-content-id="' + secKey +'" style="" title="' + secData.title + '">',
                                              '<div class="sek-overlay"></div>',
                                              '<div class="sek-saved-section-description">' + secData.description + '</div>',
                                              ! _.isEmpty( creation_date ) ? ( '<div class="sek-saved-section-date"><i class="far fa-calendar-alt"></i> @missi18n Created : ' + creation_date + '</div>' ) : '',
                                            '</div>',
                                          '</div>'
                                    ].join('');
                                    $wrapper.append( html );
                              });
                        }
                  });
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      if ( sektionsLocalizedData.isSavedSectionEnabled ) {
            $.extend( api.czrModuleMap, {
                  sek_my_sections_sec_picker_module : {
                        mthds : Constructor,
                        crud : false,
                        name : api.czr_sektions.getRegisteredModuleProperty( 'sek_my_sections_sec_picker_module', 'name' ),
                        has_mod_opt : false,
                        ready_on_section_expanded : true,
                        defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_my_sections_sec_picker_module' )
                  },
            });
      }
})( wp.customize , jQuery, _ );







/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            section_picker : function( input_options ) {
                  var input = this;
                  api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'preset_section', input_container : input.container } );
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_anchor_module : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_anchor_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_anchor_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize


            CZRInputMths : {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRInputMths

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'bg-apply-overlay' :
                                          _.each( [ 'bg-color-overlay', 'bg-opacity-overlay' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return ! _.isEmpty( item.czr_Input('bg-image')() + '' ) && api.CZR_Helpers.isChecked( input() );
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_bg_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_bg_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_bg_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize


            CZRInputMths : {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRInputMths

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_border_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_border_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_border_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
            var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'use-custom-breakpoint' :
                                          scheduleVisibilityOfInputId.call( input, 'custom-breakpoint', function() {
                                                return input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_breakpoint_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_breakpoint_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_breakpoint_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRInputMths : {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRInputMths

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'height-type' :
                                          scheduleVisibilityOfInputId.call( input, 'custom-height', function() {
                                                return 'custom' === input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_height_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_height_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_height_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_visibility_module : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_visibility_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_visibility_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRInputMths : {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRInputMths

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'width-type' :
                                          scheduleVisibilityOfInputId.call( input, 'custom-width', function() {
                                                return 'custom' === input();
                                          });
                                          scheduleVisibilityOfInputId.call( input, 'h_alignment', function() {
                                                return 'custom' === input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_width_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_width_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_width_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRInputMths : {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRInputMths

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'use-custom-outer-width' :
                                          scheduleVisibilityOfInputId.call( input, 'outer-section-width', function() {
                                                return input();
                                          });
                                    break;
                                    case 'use-custom-inner-width' :
                                          scheduleVisibilityOfInputId.call( input, 'inner-section-width', function() {
                                                return input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_width_section : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_width_section', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_width_section' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_level_spacing_module : {
                  mthds : '',
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_spacing_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_spacing_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            }//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_local_template : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_local_template', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_local_template' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'use-custom-outer-width' :
                                          scheduleVisibilityOfInputId.call( input, 'outer-section-width', function() {
                                                return input();
                                          });
                                    break;
                                    case 'use-custom-inner-width' :
                                          scheduleVisibilityOfInputId.call( input, 'inner-section-width', function() {
                                                return input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_local_widths : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_local_widths', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_local_widths' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_local_custom_css : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_local_custom_css', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_local_custom_css' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_local_reset : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_local_reset', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_local_reset' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            }//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_local_performances : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_local_performances', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_local_performances' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            }//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_local_header_footer : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_local_header_footer', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_local_header_footer' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'use-custom-breakpoint' :
                                          scheduleVisibilityOfInputId.call( input, 'global-custom-breakpoint', function() {
                                                return input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_global_breakpoint : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_global_breakpoint', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_global_breakpoint' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'use-custom-outer-width' :
                                          scheduleVisibilityOfInputId.call( input, 'outer-section-width', function() {
                                                return input();
                                          });
                                    break;
                                    case 'use-custom-inner-width' :
                                          scheduleVisibilityOfInputId.call( input, 'inner-section-width', function() {
                                                return input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_global_widths : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_global_widths', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_global_widths' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_global_performances : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_global_performances', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_global_performances' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            }//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_global_header_footer : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_global_header_footer', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_global_header_footer' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            sek_global_beta_features : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_global_beta_features', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_global_beta_features' )
                  )
            },
      });
})( wp.customize , jQuery, _ );/* ------------------------------------------------------------------------- *
 *  IMAGE MAIN SETTINGS
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
                  module.bind( 'set_default_content_picker_options', function( params ) {
                        params.defaultContentPickerOption.defaultOption = {
                              'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                              'type'       : '',
                              'type_label' : '',
                              'object'     : '',
                              'id'         : '_custom_',
                              'url'        : ''
                        };
                        return params;
                  });
            },//initialize
            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'img' :
                                          scheduleVisibilityOfInputId.call( input, 'img-size', function() {
                                                return ! _.isEmpty( input()+'' ) && _.isNumber( input() );
                                          });
                                    break;
                                    case 'link-to' :
                                          _.each( [ 'link-pick-url', 'link-custom-url', 'link-target' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'link-custom-url' :
                                                                  bool = 'url' === input() && '_custom_' == item.czr_Input('link-pick-url')().id;
                                                            break;
                                                            case 'link-pick-url' :
                                                                  bool = 'url' === input();
                                                            break;
                                                            case 'link-target' :
                                                                  bool = 'no-link' !== input();
                                                            break;
                                                      }
                                                      return bool;
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'link-pick-url' :
                                          scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                return '_custom_' == input().id && 'url' == item.czr_Input('link-to')();
                                          });
                                    break;
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'use_custom_width' :
                                          _.each( [ 'custom_width' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'use_custom_title_attr' :
                                          _.each( [ 'heading_title' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            },//CZRItemConstructor

      };//Constructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_image_main_settings_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_image_main_settings_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_image_main_settings_child' )
            },
      });
})( wp.customize , jQuery, _ );








/* ------------------------------------------------------------------------- *
 *  IMAGE BORDERS AND BORDER RADIUS
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize
            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            },//CZRItemConstructor

      };//Constructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_image_borders_corners_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_image_borders_corners_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_image_borders_corners_child' )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                    var module = this;
                    module.inputConstructor = api.CZRInput.extend( module.CZRTextEditorInputMths || {} );
                    api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRTextEditorInputMths : {
                    initialize : function( name, options ) {
                          var input = this;
                          if ( 'tiny_mce_editor' == input.type ) {
                                input.isReady.then( function() {
                                      input.container.find('[data-czr-action="open-tinymce-editor"]').trigger('click');
                                });
                          }
                          api.CZRInput.prototype.initialize.call( input, name, options );
                    },

                    setupSelect : function() {
                          api.czr_sektions.setupSelectInput.call( this );
                    }
            },//CZRTextEditorInputMths
      };//Constructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_tinymce_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_tinymce_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_tinymce_child' )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_simple_html_module : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_simple_html_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_html_module' )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var FeaturedPagesConstruct = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend({
                        setupSelect : function() {
                              api.czr_sektions.setupSelectInput.call( this );
                        }
                  });
                  module.itemConstructor = api.CZRItem.extend( module.CZRFPItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize
            CZRFPItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'img-type' :
                                          _.each( [ 'img-id', 'img-size' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'img-id' :
                                                                  bool = 'custom' === input();
                                                            break;
                                                            default :
                                                                  bool = 'none' !== input();
                                                            break;
                                                      }
                                                      return bool;
                                                }); } catch( er ) {
                                                      api.errare( 'Featured pages module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'content-type' :
                                          _.each( [ 'content-custom-text' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'custom' === input();
                                                }); } catch( er ) {
                                                      api.errare( 'Featured pages module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'btn-display' :
                                          _.each( [ 'btn-custom-text' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return input();
                                                }); } catch( er ) {
                                                      api.errare( 'Featured pages module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            },//CZRFPItemConstructor
      };//FeaturedPagesConstruct
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_featured_pages_module : {
                  mthds : FeaturedPagesConstruct,
                  crud : api.czr_sektions.getRegisteredModuleProperty( 'czr_featured_pages_module', 'is_crud' ),
                  hasPreItem : false,//a crud module has a pre item by default
                  refresh_on_add_item : false,// the preview is refreshed on item add
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_featured_pages_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_featured_pages_module' )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRIconItemConstructor || {} );
                      module.bind( 'set_default_content_picker_options', function( params ) {
                            params.defaultContentPickerOption.defaultOption = {
                                  'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                                  'type'       : '',
                                  'type_label' : '',
                                  'object'     : '',
                                  'id'         : '_custom_',
                                  'url'        : ''
                            };
                            return params;
                      });
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize

              /* Helpers */
              CZRIconItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                      case 'link-to' :
                                            _.each( [ 'link-pick-url', 'link-custom-url', 'link-target' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        var bool = false;
                                                        switch( _inputId_ ) {
                                                              case 'link-custom-url' :
                                                                    bool = 'url' == input() && '_custom_' == item.czr_Input('link-pick-url')().id;
                                                              break;
                                                              default :
                                                                    bool = 'url' == input();
                                                              break;
                                                        }
                                                        return bool;
                                                  }); } catch( er ) {
                                                        api.errare( module.module_type + ' => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'link-pick-url' :
                                            scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                  return '_custom_' == input().id && 'url' == item.czr_Input('link-to')();
                                            });
                                      break;
                                      case 'use_custom_color_on_hover' :
                                            _.each( [ 'color_hover' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( module.module_type + ' => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                }
                          });
                    }
              },//CZRIconItemConstructor

      };//Constructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_icon_settings_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_icon_settings_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_icon_settings_child' )
            },
      });
})( wp.customize , jQuery, _ );
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );
              },//initialize

              CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };// Constructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_icon_spacing_border_child: {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_icon_spacing_border_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_icon_spacing_border_child' )
            }
      });
})( wp.customize , jQuery, _ );/* ------------------------------------------------------------------------- *
 *  HEADING MAIN CHILD
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor  = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRHeadingInputMths || {} );
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
                  module.bind( 'set_default_content_picker_options', function( params ) {
                        params.defaultContentPickerOption.defaultOption = {
                              'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                              'type'       : '',
                              'type_label' : '',
                              'object'     : '',
                              'id'         : '_custom_',
                              'url'        : ''
                        };
                        return params;
                  });
            },//initialize

            CZRHeadingInputMths: {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRHeadingsInputMths
            CZRItemConstructor : {
                  ready : function() {
                        var item = this;
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()
                        api.CZRItem.prototype.ready.call( item );
                  },
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'link-to' :
                                          _.each( [ 'link-pick-url', 'link-custom-url', 'link-target' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'link-custom-url' :
                                                                  bool = input() && '_custom_' == item.czr_Input('link-pick-url')().id;
                                                            break;
                                                            case 'link-pick-url' :
                                                                  bool = input();
                                                            break;
                                                            case 'link-target' :
                                                                  bool = input();
                                                            break;
                                                      }
                                                      return bool;
                                                }); } catch( er ) {
                                                      api.errare( 'Heading module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'link-pick-url' :
                                          scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                return '_custom_' == input().id && true === item.czr_Input('link-to')();
                                          });
                                    break;
                              }
                        });
                  }//setInputVisibilityDeps
            },//CZRItemConstructor
      };//Constructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_heading_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_heading_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_heading_child' )
            }
      });
})( wp.customize , jQuery, _ );

/* ------------------------------------------------------------------------- *
 *  HEADING SPACING
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor  = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRHeadingInputMths || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRHeadingInputMths: {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRHeadingsInputMths
      };//Constructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_heading_spacing_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_heading_spacing_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_heading_spacing_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var DividerModuleConstructor = {
            initialize: function( id, options ) {
                  var module = this;
                  module.inputConstructor = api.CZRInput.extend( module.CZRDividerInputMths || {} );
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize


            CZRDividerInputMths: {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRDividerInputMths
      };//DividerModuleConstructor
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_divider_module : {
                  mthds : DividerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_divider_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_divider_module' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_spacer_module : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_spacer_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_spacer_module' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_map_module : {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_map_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_map_module' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
/* ------------------------------------------------------------------------- *
 *  QUOTE DESIGN
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRButtonItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
              CZRButtonItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                      case 'quote_design' :
                                            _.each( [ 'border_width_css', 'border_color_css' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return 'border-before' == input();
                                                  }); } catch( er ) {
                                                        api.errare( 'Quote module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                            _.each( [ 'icon_color_css', 'icon_size_css' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return 'quote-icon-before' == input();
                                                  }); } catch( er ) {
                                                        api.errare( 'Quote module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_quote_design_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_quote_design_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_quote_design_child' )
            }
      });
})( wp.customize , jQuery, _ );










/* ------------------------------------------------------------------------- *
 *  QUOTE CONTENT
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_quote_quote_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_quote_quote_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_quote_quote_child' )
            }
      });
})( wp.customize , jQuery, _ );






/* ------------------------------------------------------------------------- *
 *  CITE CONTENT
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_quote_cite_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_quote_cite_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_quote_cite_child' )
            }
      });
})( wp.customize , jQuery, _ );
/* ------------------------------------------------------------------------- *
 *  BUTTON CONTENT
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRButtonItemConstructor || {} );
                      module.bind( 'set_default_content_picker_options', function( params ) {
                            params.defaultContentPickerOption.defaultOption = {
                                  'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                                  'type'       : '',
                                  'type_label' : '',
                                  'object'     : '',
                                  'id'         : '_custom_',
                                  'url'        : ''
                            };
                            return params;
                      });
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
              CZRButtonItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                      case 'link-to' :
                                            _.each( [ 'link-pick-url', 'link-custom-url', 'link-target' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        var bool = false;
                                                        switch( _inputId_ ) {
                                                              case 'link-custom-url' :
                                                                    bool = 'url' == input() && '_custom_' == item.czr_Input('link-pick-url')().id;
                                                              break;
                                                              default :
                                                                    bool = 'url' == input();
                                                              break;
                                                        }
                                                        return bool;
                                                  }); } catch( er ) {
                                                        api.errare( 'Button module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'link-pick-url' :
                                            scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                  return '_custom_' == input().id && 'url' == item.czr_Input('link-to')();
                                            });
                                      break;
                                      case 'icon' :
                                            scheduleVisibilityOfInputId.call( input, 'icon-side', function() {
                                                  return !_.isEmpty( input() );
                                            });
                                      break;
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_btn_content_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_btn_content_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_btn_content_child' )
            }
      });
})( wp.customize , jQuery, _ );










/* ------------------------------------------------------------------------- *
 *  BUTTON DESIGN
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRButtonItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
              CZRButtonItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                      case 'use_custom_bg_color_on_hover' :
                                            _.each( [ 'bg_color_hover' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( 'Button module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                      break;
                                      case 'use_box_shadow' :
                                            _.each( [ 'push_effect' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( 'Button module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_btn_design_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_btn_design_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_btn_design_child' )
            }
      });
})( wp.customize , jQuery, _ );/* ------------------------------------------------------------------------- *
 *  MENU CONTENT
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRButtonItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );
               },//initialize
              CZRButtonItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_menu_content_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_menu_content_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_menu_content_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
              CZRItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                      case 'show_name_field' :
                                            _.each( [ 'name_field_label', 'name_field_required' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( input.module.module_type + ' => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'show_subject_field' :
                                            _.each( [ 'subject_field_label', 'subject_field_required' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( input.module.module_type + ' => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'show_message_field' :
                                            _.each( [ 'message_field_label', 'message_field_required' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( input.module.module_type + ' => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'link-pick-url' :
                                            try { scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                  return input();
                                            }); } catch( er ) {
                                                  api.errare( input.module.module_type + ' => error in setInputVisibilityDeps', er );
                                            }
                                      break;
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_simple_form_fields_child: {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_simple_form_fields_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_form_fields_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
              CZRItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_simple_form_design_child: {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_simple_form_design_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_form_design_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
              CZRItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                      case 'use_custom_bg_color_on_hover' :
                                            _.each( [ 'bg_color_hover' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( input.module.module_type + ' => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                      break;
                                      case 'use_box_shadow' :
                                            _.each( [ 'push_effect' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( input.module.module_type + ' => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_simple_form_button_child: {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_simple_form_button_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_form_button_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_simple_form_fonts_child: {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_simple_form_fonts_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_form_fonts_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_simple_form_submission_child: {
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_simple_form_submission_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_form_submission_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this );
                            }
                      });
                      module.itemConstructor = api.CZRItem.extend( module.CZRButtonItemConstructor || {} );
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
              CZRButtonItemConstructor : {
                    ready : function() {
                          var item = this;
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()
                          api.CZRItem.prototype.ready.call( item );
                    },
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                                item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                this.bind( function( to ) {
                                      item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                                });
                          };
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                      case 'use_custom_bg_color_on_hover' :
                                            _.each( [ 'bg_color_hover' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( 'Button module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'use_box_shadow' :
                                            _.each( [ 'push_effect' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        return input();
                                                  }); } catch( er ) {
                                                        api.errare( 'Button module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'link-to' :
                                            _.each( [ 'link-pick-url', 'link-custom-url', 'link-target' ] , function( _inputId_ ) {
                                                  try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                        var bool = false;
                                                        switch( _inputId_ ) {
                                                              case 'link-custom-url' :
                                                                    bool = 'url' == input() && '_custom_' == item.czr_Input('link-pick-url')().id;
                                                              break;
                                                              default :
                                                                    bool = 'url' == input();
                                                              break;
                                                        }
                                                        return bool;
                                                  }); } catch( er ) {
                                                        api.errare( 'Button module => error in setInputVisibilityDeps', er );
                                                  }
                                            });
                                      break;
                                      case 'link-pick-url' :
                                            scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                  return '_custom_' == input().id && 'url' == item.czr_Input('link-to')();
                                            });
                                      break;
                                }
                          });
                    }
              }
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_font_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_font_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_font_child' )
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;
                      module.inputConstructor = api.CZRInput.extend({
                            setupSelect : function() {
                                  api.czr_sektions.setupSelectInput.call( this, sektionsLocalizedData.registeredWidgetZones );
                            }
                      });
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
      };
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_widget_area_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_widget_area_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_widget_area_module' )
            }
      });
})( wp.customize , jQuery, _ );