//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::initialize(),
            // at api.bind( 'ready', function() {})
            // at _mainPanel_.expanded.bind( ... )
            setupFeedBackUI : function() {
                  var self = this;
                  self.feedbackLastUserAction = 'none';//<= store the last click action
                  self.feedbackUIId = '#nimble-feedback';
                  self.feedbackUIVisible = new api.Value( false );
                  self.feedbackUIVisible.bind( function( visible ){
                        if ( ! self.levelTreeExpanded() ) {
                              self.toggleFeddBackUI( visible );
                        }
                        // Schedule a self closing of the feedback UI
                        if ( visible ) {
                              self.refreshSelfClosingTimer();
                        }
                  });
                  self.feedbackUIVisible( true );
            },

            // self close the feedback ui when conditions are met
            refreshSelfClosingTimer : function() {
                  var self = this;
                  clearTimeout( $(self.feedbackUIId).data('_feedback_user_action_timer_') );
                  $(self.feedbackUIId).data('_feedback_user_action_timer_', setTimeout(function() {
                        // => 'maybe_later', 'already_did', 'dismiss' are hiding the feedback ui, no worries
                        // => 'go_review', 'reporting_problem' => user should click on "already did" to dismiss
                        // all other states are intermediate and can trigger a self close
                        if ( ! _.contains( [ 'go_review', 'reporting_problem' ] , self.feedbackLastUserAction) ) {
                              self.feedbackUIVisible( false );
                        }
                  }, 60000 ) );
            },

            // @return void()
            // self.feedbackUIVisible.bind( function( visible ){
            //       self.toggleFeddBackUI( visible );
            // });
            toggleFeddBackUI : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndSetupFeedbackTmpl({}) ).done( function( $_el ) {
                                  //display
                                  _.delay( function() {
                                      self.cachedElements.$body.addClass('nimble-feedback-ui-visible');
                                  }, 200 );
                            });
                      },
                      _hideAndRemove = function() {
                            var dfd = $.Deferred();
                            self.cachedElements.$body.removeClass('nimble-feedback-ui-visible');
                            if ( $( self.feedbackUIId ).length > 0 ) {
                                  //remove Dom element after slide up
                                  _.delay( function() {
                                        $( self.feedbackUIId ).remove();
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
                        _hideAndRemove().done( function() {
                              self.feedbackUIVisible( false );//should be already false
                        });
                  }
            },


            //@param = { }
            renderAndSetupFeedbackTmpl : function( params ) {
                  var self = this,
                      _tmpl;

                  // CHECK IF ALREADY RENDERED
                  if ( $( self.feedbackUIId ).length > 0 )
                    return $( self.feedbackUIId );

                  // RENDER
                  try {
                        _tmpl =  wp.template( 'nimble-feedback-ui' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing the the feedback template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );

                  // SCHEDULE EVENTS
                  if ( self.feedbackEventsScheduled )
                    return;

                  // @see PHP constant NIMBLE_FEEDBACK_NOTICE_ID
                  var _feedbackNoticeId = $(self.feedbackUIId).data('sek-dismiss-pointer');

                  // @return $.Deferred
                  var doAjaxDismiss = function() {
                      // On dismissing the notice, make a POST request to store this notice with the dismissed WP pointers so it doesn't display again.
                      // @uses 'dismiss-wp-pointer' <= core action to store the dismissed admin notification in the wp_usermeta DB table
                      // WP already has the PHP callback for that in  wp-admin/includes/ajax-actions.php
                      // the array of dismissed pointers can be accessed server side with get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
                      wp.ajax.post( 'dismiss-wp-pointer', {
                            pointer: _feedbackNoticeId
                      }).fail( function( resp ) {
                            api.errare( 'ajax dismiss failure', resp );
                      });
                  };

                  // Attach event with delegation
                  self.cachedElements.$body.on('click', '[data-sek-feedback-action]', function(evt) {
                        evt.preventDefault();

                        // On each click action, reset the timer
                        self.refreshSelfClosingTimer();

                        var _action = $(this).data('sek-feedback-action');

                        // store it
                        self.feedbackLastUserAction = _action;

                        switch( _action ) {
                              // Step one
                              case 'not_enjoying' :
                                    $(self.feedbackUIId).find('.sek-feedback-step-one').hide();
                                    $(self.feedbackUIId).find('.sek-feedback-step-two-not-enjoying').show();
                              break;
                              case 'enjoying' :
                                    $(self.feedbackUIId).find('.sek-feedback-step-one').hide();
                                    $(self.feedbackUIId).find('.sek-feedback-step-two-enjoying').show();
                              break;

                              // Step two negative
                              case 'reporting_problem' :
                                    window.open($(this).data('problem-href'), '_blank');
                                    //self.feedbackUIVisible( false );
                              break;

                              // Step two positive
                              case 'go_review' :
                                    window.open('https://wordpress.org/support/plugin/nimble-builder/reviews/?filter=5/#new-post', '_blank');
                              break;

                              // Can be clicked in all cases
                              case 'maybe_later' :
                                    self.feedbackUIVisible( false );
                                    wp.ajax.post( 'sek_postpone_feedback', {
                                          nonce: api.settings.nonce.save,
                                          transient_duration_in_days : 30
                                    }).fail( function( resp ) {
                                          api.errare( 'ajax dismiss failure', resp );
                                    });
                              break;

                              // Ajax dismiss action
                              case 'already_did' :
                                    $(self.feedbackUIId).find('.sek-feedback-step-two-not-enjoying').hide();
                                    $(self.feedbackUIId).find('.sek-feedback-step-two-enjoying').hide();
                                    $(self.feedbackUIId).find('.sek-feedback-step-three-thanks').show();
                                    _.delay( function() {
                                          self.feedbackUIVisible( false );
                                    }, 3000 );
                                    doAjaxDismiss();
                              break;
                              case 'dismiss' :
                                    self.feedbackUIVisible( false );
                                    doAjaxDismiss();
                              break;
                              default :
                                    api.errare('::renderAndSetupFeedbackTmpl => invalid action');
                              break;
                        }
                        //window.open($(this).data('doc-href'), '_blank');
                  });

                  // so we bind event only once
                  self.feedbackEventsScheduled = true;
                  return $( self.feedbackUIId );
            }//renderAndSetupFeedbackTmpl
      });//$.extend()
})( wp.customize, jQuery );
