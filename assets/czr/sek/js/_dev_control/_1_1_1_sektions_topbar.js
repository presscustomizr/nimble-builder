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

                  $('.sek-nimble-doc', self.topBarId).on( 'click', function(evt) {
                        evt.preventDefault();
                        window.open($(this).data('doc-href'), '_blank');
                  });

                  $('.sek-tmpl-saving', self.topBarId ).on( 'click', function(evt) {
                        // Focus on the Nimble panel
                        // api.panel( sektionsLocalizedData.sektionsPanelId, function( _panel_ ) {
                        //       self.rootPanelFocus();
                        //       _panel_.focus();
                        // });
                        evt.preventDefault();
                        self.saveTmplUIVisible(!self.saveTmplUIVisible());// self.saveTmplUIVisible() is initialized false
                  });


                  // NOTIFICATION WHEN USING CUSTOM TEMPLATE
                  // implemented for https://github.com/presscustomizr/nimble-builder/issues/304
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
                        // Schedule notification rendering on init
                        // @see ::generateUIforLocalSkopeOptions()
                        api( self.localSectionsSettingId(), function( _localSectionsSetting_ ) {
                              var localSectionsValue = _localSectionsSetting_(),
                                  initialLocalTemplateValue = ( _.isObject( localSectionsValue ) && localSectionsValue.local_options && localSectionsValue.local_options.template ) ? localSectionsValue.local_options.template : null;
                              // on init
                              maybePrintNotificationForUsageOfNimbleTemplate( initialLocalTemplateValue );
                        });

                        // React to template changes
                        // @see ::generateUIforLocalSkopeOptions() for the declaration of self.getLocalSkopeOptionId() + '__template'
                        api( self.getLocalSkopeOptionId() + '__template', function( _set_ ) {
                              _set_.bind( function( to, from ) {
                                    maybePrintNotificationForUsageOfNimbleTemplate( to );
                              });
                        });
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
