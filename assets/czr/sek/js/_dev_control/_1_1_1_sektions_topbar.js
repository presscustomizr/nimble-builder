//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::initialize(), at api.bind( 'ready', function() {})
            setupTopBar : function() {
                  var self = this;
                  self.topBarId = '#nimble-top-bar';
                  self.topBarVisible = new api.Value( false );
                  self.topBarVisible.bind( function( visible ){
                        if ( ! self.levelTreeExpanded() ) {
                              self.toggleTopBar( visible );
                        }
                  });

                  self.mouseMovedRecently = new api.Value( {} );
                  self.mouseMovedRecently.bind( function( position ) {
                        self.topBarVisible( ! _.isEmpty( position )  );
                  });

                  var trackMouseMovements = function( evt ) {
                        self.mouseMovedRecently( { x : evt.clientX, y : evt.clientY } );
                        clearTimeout( self.cachedElements.$window.data('_scroll_move_timer_') );
                        self.cachedElements.$window.data('_scroll_move_timer_', setTimeout(function() {
                              self.mouseMovedRecently.set( {} );
                        }, 4000 ) );
                  };
                  self.cachedElements.$window.on( 'mousemove scroll,', _.throttle( trackMouseMovements , 50 ) );
                  api.previewer.bind('ready', function() {
                        $(api.previewer.targetWindow().document ).on( 'mousemove scroll,', _.throttle( trackMouseMovements , 50 ) );
                  });

                  // LEVEL TREE
                  self.setupLevelTree();
            },


            // @return void()
            // self.topBarVisible.bind( function( visible ){
            //       self.toggleTopBar( visible );
            // });
            toggleTopBar : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndSetupTopBarTmpl({}) ).done( function( $_el ) {
                                  self.topBarContainer = $_el;
                                  //display
                                  _.delay( function() {
                                      self.cachedElements.$body.addClass('nimble-top-bar-visible');
                                  }, 200 );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            self.cachedElements.$body.removeClass('nimble-top-bar-visible');
                            if ( self.topBarContainer && self.topBarContainer.length ) {
                                  //remove Dom element after slide up
                                  _.delay( function() {
                                        //self.topBarContainer.remove();
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


            //@param = { }
            renderAndSetupTopBarTmpl : function( params ) {
                  var self = this,
                      _tmpl;

                  // CHECK IF ALREADY RENDERED
                  if ( $( self.topBarId ).length > 0 )
                    return $( self.topBarId );

                  // RENDER
                  try {
                        _tmpl =  wp.template( 'nimble-top-bar' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing the the top note template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );
                  $('#customize-preview').trigger('nimble-top-bar-rendered');

                  // UNDO / REDO ON CTRL + Z / CTRL + Y EVENTS
                  $(document).keydown( function( evt ) {
                        if ( evt.ctrlKey && _.contains( [89, 90], evt.keyCode ) ) {
                              try { self.navigateHistory( 90 === evt.keyCode ? 'undo' : 'redo'); } catch( er ) {
                                    api.errare( 'Error when firing self.navigateHistory', er );
                              }
                        }
                  });


                  // CLICK EVENTS
                  // Attach click events
                  $('.sek-add-content', self.topBarId).on( 'click', function(evt) {
                        evt.preventDefault();
                        api.previewer.trigger( 'sek-pick-content', { content_type : 'module' });
                  });
                  $('.sek-level-tree', self.topBarId).on( 'click', function(evt) {
                        evt.preventDefault();
                        self.levelTreeExpanded(!self.levelTreeExpanded());
                  });
                  $('[data-nimble-history]', self.topBarId).on( 'click', function(evt) {
                        try { self.navigateHistory( $(this).data( 'nimble-history') ); } catch( er ) {
                              api.errare( 'Error when firing self.navigateHistory', er );
                        }
                  });
                  $('.sek-settings', self.topBarId).on( 'click', function(evt) {
                        // Focus on the Nimble panel
                        api.panel( sektionsLocalizedData.sektionsPanelId, function( _panel_ ) {
                              self.rootPanelFocus();
                              _panel_.focus();
                        });
                        // // Generate UI for the local skope options
                        // self.generateUI({ action : 'sek-generate-local-skope-options-ui'}).done( function() {
                        //       api.control( self.getLocalSkopeOptionId(), function( _control_ ) {
                        //             _control_.focus();
                        //       });
                        // });
                  });

                  $('.sek-nimble-doc, .sek-notifications', self.topBarId).on( 'click', function(evt) {
                        evt.preventDefault();
                        if ( $(this).data('doc-href') ) {
                              window.open($(this).data('doc-href'), '_blank');
                        }
                  });

                  $('.sek-tmpl-saving', self.topBarId ).on( 'click', function(evt) {
                        // Focus on the Nimble panel
                        // api.panel( sektionsLocalizedData.sektionsPanelId, function( _panel_ ) {
                        //       self.rootPanelFocus();
                        //       _panel_.focus();
                        // });
                        evt.preventDefault();
                        self.tmplDialogVisible(!self.tmplDialogVisible());// self.tmplDialogVisible() is initialized false
                  });

                  $( self.topBarId ).on( 'click', '.sek-reset-local-sektions', function(evt) {
                        // Focus on the Nimble panel
                        // api.panel( sektionsLocalizedData.sektionsPanelId, function( _panel_ ) {
                        //       self.rootPanelFocus();
                        //       _panel_.focus();
                        // });
                        // api.control( self.getLocalSkopeOptionId() + '__local_reset', function( _control_ ) {
                        //       _control_.focus();
                        //       _control_.container.find('.customize-control-title').trigger('click');
                        // });
                        // evt.preventDefault();
                        // Focus on the Nimble panel
                        api.panel( sektionsLocalizedData.sektionsPanelId, function( _panel_ ) {
                              self.rootPanelFocus();
                              _panel_.focus();
                              api.section( self.SECTION_ID_FOR_LOCAL_OPTIONS, function( _section_ ) {
                                    _section_.focus();
                                    setTimeout( function() {
                                          api.control( self.getLocalSkopeOptionId() + '__local_reset', function( _control_ ) {
                                                _control_.focus();
                                                _control_.container.find('.customize-control-title').trigger('click');
                                                _control_.container.addClass('button-see-me');
                                                _.delay( function() {
                                                      _control_.container.removeClass('button-see-me');
                                                }, 800 );
                                          });
                                    }, 500 );
                              });
                        });
                  });

                  // NOTIFICATION WHEN USING CUSTOM TEMPLATE
                  // implemented for https://github.com/presscustomizr/nimble-builder/issues/304
                  var printSektionsSkopeStatus = function( args ) {
                        if ( $(self.topBarId).length < 1 || sektionsLocalizedData.isDebugMode )
                              return;

                        if ( !sektionsLocalizedData.isSiteTemplateEnabled )
                              return;
                        var _hasLocalSektions = false;
                        if ( args && args.on_init ) {
                              //console.log('ON INIT : ', api.czr_skopeBase.getSkopeProperty( 'skope_id', 'group' ) );
                              _hasLocalSektions = api.czr_skopeBase.getSkopeProperty( 'has_local_sektions', 'local' );
                        } else if ( args && args.after_reset ) {
                              //console.log('AFTER RESET');
                              _hasLocalSektions = false;
                        } else {
                              _hasLocalSektions = self.hasLocalSektions();
                        }
                        //console.log('GROUP SKOPE FOR SITE TEMPL', api.czr_skopeBase.getSkopeProperty( 'has_local_sektions', 'local' ) );
                        //console.log('GLOBAL OPTIONS ', api(sektionsLocalizedData.optNameForGlobalOptions)() );
                        var _groupSkope = api.czr_skopeBase.getSkopeProperty( 'skope_id', 'group' ),
                              _hasSiteTemplateSet = false,
                              _inheritsSiteTemplate = false,
                              _globOptions = api(sektionsLocalizedData.optNameForGlobalOptions)();

                        if ( _.isObject(_globOptions) && _globOptions.site_templates && _.isObject(_globOptions.site_templates ) ) {
                              _.each( _globOptions.site_templates, function( tmpl, siteTmplSkope ) {
                                    if ( !_hasSiteTemplateSet && _groupSkope === siteTmplSkope.substring(0,_groupSkope.length) ) {
                                          _hasSiteTemplateSet = true;
                                    }
                              } );
                        }

                        //console.log('HAS SITE TMPL SET ?', _hasSiteTemplateSet );
                        _inheritsSiteTemplate = _hasSiteTemplateSet && !_hasLocalSektions;
                        //console.log('ALORS ?', _inheritsSiteTemplate, _hasLocalSektions );
                        var _msg = sektionsLocalizedData.i18n['This page has no NB sections'];
                        if ( _inheritsSiteTemplate ) {
                              _msg = sektionsLocalizedData.i18n['This page inherits a NB site template'];
                        } else if ( _hasLocalSektions ) {
                              _msg = sektionsLocalizedData.i18n['This page has NB sections'];
                              _msg += '<button type="button" class="far fa-trash-alt sek-reset-local-sektions" title="Remove sektions" data-nimble-state="enabled"><span class="screen-reader-text">Remove sektions</span></button>';
                        }

                        $(self.topBarId).find('.sek-notifications')
                              .html([
                                    '<span class="fas fa-info-circle"></span>',
                                    _msg
                                    //sektionsLocalizedData.i18n['This page uses Nimble Builder template.']
                              ].join(' '));
                              //.attr('data-doc-href', 'https://docs.presscustomizr.com/article/339-changing-the-page-template');
                        
                              // if ( _.isObject( templateSettingValue ) && templateSettingValue.local_template && 'default' !== templateSettingValue.local_template ) {
                              if ( _inheritsSiteTemplate ) {
                                    $(self.topBarId).find('.sek-notifications').addClass('is-linked').data('doc-href', 'https://docs.presscustomizr.com/article/428-how-to-use-site-templates-with-nimble-builder');
                              } else {
                                    $(self.topBarId).find('.sek-notifications').removeClass('is-linked').data('doc-href','');
                              }
                        // } else {
                        //       $(self.topBarId).find('.sek-notifications').html('');
                        // }
                  };

                  api.bind('nimble-update-topbar-skope-status', printSektionsSkopeStatus );

                  var initOnSkopeReady = function() {
                        // Schedule notification rendering on init
                        // @see ::generateUIforLocalSkopeOptions()
                        api( self.localSectionsSettingId(), function( _localSectionsSetting_ ) {
                              // var localSectionsValue = _localSectionsSetting_(),
                              //     initialLocalTemplateValue = ( _.isObject( localSectionsValue ) && localSectionsValue.local_options && localSectionsValue.local_options.template ) ? localSectionsValue.local_options.template : null;
                              // // on init
                              // printSektionsSkopeStatus( initialLocalTemplateValue );
                              printSektionsSkopeStatus({on_init : true});
                        });

                        // React to template changes
                        // @see ::generateUIforLocalSkopeOptions() for the declaration of self.getLocalSkopeOptionId() + '__template'
                        // api( self.getLocalSkopeOptionId() + '__template', function( _set_ ) {
                        //       _set_.bind( function( to, from ) {
                        //             printSektionsSkopeStatus( to );
                        //       });
                        // });
                  };

                  // fire now
                  initOnSkopeReady();
                  // and on skope change, when user navigates through the previewed pages
                  // 'nimble-ready-for-current-skope' declared in ::initialize()
                  api.bind('nimble-ready-for-current-skope', function() {
                        initOnSkopeReady();
                  });

                  return $( self.topBarId );
            }
      });//$.extend()
})( wp.customize, jQuery );
