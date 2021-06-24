
//DOM READY :
//1) FIRE SPECIFIC INPUT PLUGINS
//2) ADD SOME COOL STUFFS
//3) SPECIFIC CONTROLS ACTIONS
( function ( wp, $ ) {
      $( function($) {
            var api = wp.customize || api;

            //WHAT IS HAPPENING IN THE MESSENGER
            // $(window.parent).on( 'message', function(e, o) {
            //   api.consoleLog('SENT STUFFS', JSON.parse( e.originalEvent.data), e );
            // });
            // $( window ).on( 'message', function(e, o) {
            //   api.consoleLog('INCOMING MESSAGE', JSON.parse( e.originalEvent.data), e );
            // });
            // $(window.document).bind("ajaxSend", function(e, o){
            //    api.consoleLog('AJAX SEND', e, arguments );
            // }).bind("ajaxComplete", function(e, o){
            //    api.consoleLog('AJAX COMPLETE', e, o);
            // });

            var fireHeaderButtons = function() {
                  var $header_button;

                  // Deactivated for the moment.
                  // The + button has been moved in the Nimble top bar
                  // if ( api.czr_sektions ) {
                  //       var _title_ = ( window.sektionsLocalizedData && sektionsLocalizedData.i18n && sektionsLocalizedData.i18n['Drag and drop content'] ) ? sektionsLocalizedData.i18n['Drag and drop content'] : '';
                  //       $header_button = $('<span/>', {
                  //             class:'customize-controls-home-or-add',
                  //             html:'<span class="screen-reader-text">Home</span><span class="material-icons" title="' + _title_ +'">add_circle_outline</span>'
                  //       });
                  // } else {
                  //       $header_button = $('<span/>', {
                  //             class:'customize-controls-home-or-add fas fa-home',
                  //             html:'<span class="screen-reader-text">Home</span>'
                  //       });
                  // }

                  $header_button = $('<span/>', {
                        class:'customize-controls-home-or-add fas fa-home',
                        html:'<span class="screen-reader-text">Home</span>'
                  });

                  $.when( $('#customize-header-actions').append( $header_button ) )
                        .done( function() {
                              $('body').addClass('czr-has-home-btn');
                              $header_button
                                    .keydown( function( event ) {
                                          if ( 9 === event.which ) // tab
                                            return;
                                          if ( 13 === event.which ) // enter
                                            this.trigger('click');
                                          event.preventDefault();
                                    })
                                    .on( 'click.customize-controls-home-or-add', function() {
                                          // if ( api.czr_sektions ) {
                                          //       api.previewer.trigger( 'sek-pick-content', {});
                                          // }
                                          //event.preventDefault();
                                          //close everything
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
                                    });
                              // animate on init
                              // @use button-see-mee css class declared in core in /wp-admin/css/customize-controls.css
                              _.delay( function() {
                                    if ( $header_button.hasClass( 'button-see-me') )
                                      return;
                                    var _seeMe = function() {
                                              return $.Deferred(function(){
                                                    var dfd = this;
                                                    $header_button.addClass('button-see-me');
                                                    _.delay( function() {
                                                          $header_button.removeClass('button-see-me');
                                                          dfd.resolve();
                                                    }, 800 );
                                              });
                                        },
                                        i = 0,
                                        _seeMeLoop = function() {
                                              _seeMe().done( function() {
                                                    i--;
                                                    if ( i >= 0 ) {
                                                          _.delay( function() {
                                                                _seeMeLoop();
                                                          }, 50 );
                                                    }
                                              });
                                        };
                                    _seeMeLoop();
                              }, 2000 );
                        });// done()
            };

            // Nov 2020 => remove home button for users of blocksy theme
            // https://github.com/presscustomizr/themes-customizer-fmk/issues/53
            if ( !_.contains(['blocksy'], serverControlParams.activeTheme ) ) {
                fireHeaderButtons();
            }

      });//end of $( function($) ) dom ready
})( wp, jQuery );