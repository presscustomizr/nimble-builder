//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            cachedElements : {
                $body : $('body'),
                $window : $(window)
            },

            initialize: function() {
                  var self = this;

                  // Set the skope_id
                  try { this.skope_id = _.findWhere( _wpCustomizeSettings.czr_new_skopes, { skope : 'local' }).skope_id; } catch( _er_ ) {
                        this.errare('Preview => error when storing the skope_id', _er_ );
                        return;
                  }

                  // Active UI
                  this.scheduleHighlightActiveLevel();

                  // The loading icon when a level is refreshed
                  self.setupLoader();

                  // DOM READY
                  $( function() {
                        self.setupSortable();
                        self.setupResizable();
                        self.setupUiHoverVisibility();
                        self.scheduleUiClickReactions();

                        self.schedulePanelMsgReactions();
                  });

                  // Make sure we don't force a minimum height to empty columns when a section has at least one module
                  // => allow a better previewing experience and more realistic spacing adjustments
                  // The css class .sek-has-modules is also printed server side
                  // @see php SEK_Front_Render::render()
                  self.cachedElements.$body.on('sek-columns-refreshed sek-modules-refreshed', function( evt, params ) {
                        if ( !_.isUndefined( params ) && !_.isUndefined( params.in_sektion ) && $('[data-sek-id="' + params.in_sektion +'"]').length > 0 ) {
                              var $updatedSektion = $('[data-sek-id="' + params.in_sektion +'"]');
                              $updatedSektion.toggleClass( 'sek-has-modules', $updatedSektion.find('[data-sek-level="module"]').length > 0 );
                        }
                  });

                  // Deactivates the links
                  self.deactivateLinks();

                  self.cachedElements.$body.on([
                        'sek-modules-refreshed',
                        'sek-columns-refreshed',
                        'sek-section-added',
                        'sek-level-refreshed',
                        'sek-edit-module'
                  ].join(' '), function( evt ) {
                        self.deactivateLinks(evt);
                  });


                  // Send the contextually active locations
                  // 1) on init
                  // 2) and when requested by the control panel
                  // introduced for the level tree, https://github.com/presscustomizr/nimble-builder/issues/359
                  var sendActiveLocations = function() {
                        var active_locs = [],
                            active_locs_info = [];// <= introduced for better move up/down of sections https://github.com/presscustomizr/nimble-builder/issues/521
                        $('[data-sek-level="location"]').each( function() {
                              active_locs.push( $(this).data('sek-id') );
                              active_locs_info.push({
                                    id : $(this).data('sek-id'),
                                    is_global : true === $(this).data('sek-is-global-location'),
                                    is_header_footer : true === $(this).data('sek-is-header-location') || true === $(this).data('sek-is-footer-location'),
                                    // added for https://github.com/presscustomizr/nimble-builder-pro/issues/6
                                    is_header : true === $(this).data('sek-is-header-location'),
                                    is_footer : true === $(this).data('sek-is-footer-location')
                              });
                        });
                        api.preview.send('sek-active-locations-in-preview', { active_locations : active_locs, active_locs_info : active_locs_info } );
                  };
                  api.preview.bind('sek-request-active-locations', sendActiveLocations );
                  sendActiveLocations();
            },

            // Fired on initialize()
            // and on user generated events
            deactivateLinks : function( evt ) {
                  evt = evt || {};
                  var _doSafe_ = function() {
                          if ( "yes" === $(this).data('sek-unlinked') )
                            return;
                          // Several cases :
                          // 1- internal link ( <=> api.isLinkPreviewable(... ) = true ) : we allow navigation with shift + click
                          // 2- extenal link => navigation is disabled.
                          // 3- server disabled links, with href attribute set to "javascript:void(0)", this case is checked isJavascriptProtocol
                          var isJavascriptProtocol = _.isString( $(this)[0].protocol ) && -1 !== $(this)[0].protocol.indexOf('javascript');
                          // the check on isJavascriptProtocol fixes issue https://github.com/presscustomizr/nimble-builder/issues/255
                          if ( ! isJavascriptProtocol && api.isLinkPreviewable( $(this)[0] ) ) {
                                $(this).addClass('nimble-shift-clickable');
                                $(this).data('sek-unlinked', "yes").attr('data-nimble-href', $(this).attr('href') ).attr('href', 'javascript:void(0)');
                                // remove target="_blank" if enabled by user
                                // @fixes issue https://github.com/presscustomizr/nimble-builder/issues/542
                                $(this).removeAttr('target');
                                $(this).on('mouseenter', function() {
                                        $(this).attr( 'title', sekPreviewLocalized.i18n['Shift-click to visit the link']);
                                });
                                $(this).on('mouseleave', function() {
                                      $(this).removeAttr( 'title' );
                                });

                                $(this).on('click', function(evt) {
                                      if ( ! evt.shiftKey ) {
                                        return;
                                      }
                                      evt.preventDefault();
                                      window.location.href = $(this).attr('data-nimble-href');
                                });
                          } else {
                                $(this).addClass('nimble-unclickable');
                                $(this).data('sek-unlinked', "yes").attr('data-nimble-href', $(this).attr('href') ).attr('href', 'javascript:void(0)');
                                $(this).on('mouseenter', function() {
                                      $(this).attr( 'title', isJavascriptProtocol ? sekPreviewLocalized.i18n['Link deactivated while previewing'] : sekPreviewLocalized.i18n['External links are disabled when customizing']);
                                }).on('mouseleave', function() {
                                      $(this).removeAttr( 'title' );
                                });
                                $(this).on('click', function(evt) {
                                      evt.preventDefault();
                                });
                          }
                    };
                  this.cachedElements.$body.find('[data-sek-level="module"]').each( function() {
                        $(this).find('a').each( function(){
                              try { _doSafe_.call( $(this) ); } catch(er) { api.errare( '::deactivateLinks => error ', er ); }
                        });
                  });
            },

            // Hightlight the currently level in the preview, corresponding to the active ui in the panel
            //
            // When a new ui is generated, the activeLevelUI is set @see ::schedulePanelMsgReactions()
            // When the level options are modidied ( 'sek-refresh-stylesheet', 'sek-refresh-level' ),
            scheduleHighlightActiveLevel : function() {
                  var self = this;
                  // Stores the currently edited level
                  // aka the one on which the user clicked to edit it
                  this.activeLevelUI = new api.Value('');
                  this.activeLevelEl = new api.Value(null);
                  this.activeUIChangedRecently = new api.Value( false );

                  this.activeLevelUI.bind( function( to, from ) {
                        var $activeLevel = $('[data-sek-id="' + to +'"]'),
                            $previousActiveLevel = $('[data-sek-id="' + from +'"]');

                        if ( $activeLevel.length > 0 ) {
                              $activeLevel.addClass('sek-active-ui sek-highlight-active-ui');
                              // cache $activeLevel
                              self.activeLevelEl( $activeLevel );
                        }
                        if ( $previousActiveLevel.length > 0 ) {
                              $previousActiveLevel.removeClass('sek-active-ui sek-highlight-active-ui');
                        }
                        self.activeUIChangedRecently( Date.now() );
                  });

                  // MAY 2020 : added to focus on the edited element
                  // updated in self.activeLevelUI() and self.schedulePanelMsgReactions()
                  self.activeLevelEl.bind( function($el) {
                        // scroll to focus on the active element
                        // but only if element is offscreen, otherwise clicking on a section edit UI for example, will make it move to the top, which is annoying
                        if ( _.isObject($el) && $el.length > 0 && !nb_.isInScreen( $el[0]) ) {
                              // https://caniuse.com/#search=scrollIntoView
                              try{ $el[0].scrollIntoView(); } catch(er) {
                                    self.errare('activeLevelEl error', er );
                              }
                        }
                  });

                  // apiParams : {
                  //       action : 'sek-refresh-level',
                  //       id : params.uiParams.id,
                  //       level : params.uiParams.level
                  // },
                  // skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),
                  _.each( [ 'sek-refresh-stylesheet', 'sek-refresh-level' ], function( msgId ) {
                        api.preview.bind( msgId, function( params ) {
                              self.activeUIChangedRecently( Date.now() );
                        });
                  });


                  // This api.Value() is updated with Date.now() when something just happened and false after 3000 ms of inactivity
                  // so we can always react to changes, and refresh the timeOut
                  this.activeUIChangedRecently.bind( function( hasChangedRecently ) {
                        var $newActiveLevel = $('[data-sek-id="' + self.activeLevelUI() +'"]');
                        // remove the highlight class if it was previously set to another level
                        if ( $('.sek-highlight-active-ui').length ) {
                              $('.sek-highlight-active-ui').removeClass('sek-highlight-active-ui');
                        }
                        if ( $newActiveLevel.length > 0 ) {
                              $newActiveLevel.toggleClass( 'sek-highlight-active-ui', false !== hasChangedRecently );
                        }

                        clearTimeout( $.data( this, '_ui_change_timer_') );
                        $.data( this, '_ui_change_timer_', setTimeout(function() {
                              self.activeUIChangedRecently( false );
                        }, 3000 ) );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );