//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::initialize(), at api.bind( 'ready', function() {})
            setupFeedBackUI : function() {
                  var self = this;
                  self.feedbackUIId = '#nimble-feedback';
                  self.feedbackUIVisible = new api.Value( false );
                  self.feedbackUIVisible.bind( function( visible ){
                        if ( ! self.levelTreeExpanded() ) {
                              self.toggleFeddBackUI( visible );
                        }
                  });

                  self.feedbackUIVisible( true );
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
                                      $('body').addClass('nimble-feedback-ui-visible');
                                  }, 200 );
                            });
                      },
                      _hideAndRemove = function() {
                            var dfd = $.Deferred();
                            $('body').removeClass('nimble-feedback-ui-visible');
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

                  // CLICK EVENTS
                  // Attach click events
                  // $('.sek-add-content', self.feedbackUIId).on( 'click', function(evt) {
                  //       evt.preventDefault();
                  //       api.previewer.trigger( 'sek-pick-content', { content_type : 'module' });
                  // });
                  // $('.sek-level-tree', self.feedbackUIId).on( 'click', function(evt) {
                  //       evt.preventDefault();
                  //       self.levelTreeExpanded(!self.levelTreeExpanded());
                  // });


                  // $('.sek-nimble-doc', self.feedbackUIId).on( 'click', function(evt) {
                  //       evt.preventDefault();
                  //       window.open($(this).data('doc-href'), '_blank');
                  // });

                  // Attach event with delegation
                  $('body').on('click', '[data-sek-feedback-action]', function(evt) {
                        evt.preventDefault();
                        var _action = $(this).data('sek-feedback-action');
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
                              case 'not_reporting_problem' :
                                    self.feedbackUIVisible( false );
                              break;
                              case 'reporting_problem' :
                                    window.open($(this).data('problem-href'), '_blank');
                                    self.feedbackUIVisible( false );
                              break;

                              // Step two positive
                              case 'maybe_later' :
                                    self.feedbackUIVisible( false );
                              break;
                              case 'go_review' :
                                    window.open('https://wordpress.org/support/plugin/nimble-builder/reviews/#new-post', '_blank');
                              break;
                              case 'already_reviewed' :
                                    $(self.feedbackUIId).find('.sek-feedback-step-two-enjoying').hide();
                                    $(self.feedbackUIId).find('.sek-feedback-step-three-thanks').show();
                                    _.delay( function() {
                                          self.feedbackUIVisible( false );
                                    }, 2000 );
                              break;

                              default :
                                    api.errare('::renderAndSetupFeedbackTmpl => invalid action');
                              break;
                        }
                        //window.open($(this).data('doc-href'), '_blank');
                  });


                  // Close
                  $('body').on('click', '.sek-close-feedback-ui' , function(evt) {
                        evt.preventDefault();
                        self.feedbackUIVisible( false );
                  });

                  // so we bind event only once
                  self.feedbackEventsScheduled = true;
                  return $( self.feedbackUIId );
            }
      });//$.extend()
})( wp.customize, jQuery );
