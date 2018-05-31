//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            initialize: function() {
                  var self = this;

                  // Set the skope_id
                  try { this.skope_id = _.findWhere( _wpCustomizeSettings.czr_new_skopes, { skope : 'local' }).skope_id; } catch( _er_ ) {
                        this.errare('Preview => error when storing the skope_id', _er_ );
                        return;
                  }

                  // Active UI
                  this.scheduleHighlightActiveLevel();

                  // DOM READY
                  $( function() {
                        self.setupSortable();
                        self.setupResizable();
                        self.setupUiHoverVisibility();
                        self.scheduleUiClickReactions();

                        self.schedulePanelMsgReactions();
                  });
            },

            // Hightlight the currently level in the preview, corresponding to the active ui in the panel
            //
            // When a new ui is generated, the activeLevelUI is set @see ::schedulePanelMsgReactions()
            // When the level options are modidied ( 'sek-refresh-stylesheet', 'sek-refresh-level' ),
            scheduleHighlightActiveLevel : function() {
                  var self = this;
                  // Stores the currently edited level
                  this.activeLevelUI = new api.Value('');
                  this.activeUIChangedRecently = new api.Value( false );

                  this.activeLevelUI.bind( function( to, from ) {
                        var $activeLevel = $('[data-sek-id="' + to +'"]'),
                            $previousActiveLevel = $('[data-sek-id="' + from +'"]');
                        if ( $activeLevel.length > 0 ) {
                              $activeLevel.addClass('sek-active-ui sek-highlight-active-ui');
                        }
                        if ( $previousActiveLevel.length > 0 ) {
                              $previousActiveLevel.removeClass('sek-active-ui sek-highlight-active-ui');
                        }
                        self.activeUIChangedRecently( Date.now() );
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