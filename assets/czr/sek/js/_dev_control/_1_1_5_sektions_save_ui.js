//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // SAVE DIALOG BLOCK
            // fired in ::initialize()
            setupSaveUI : function() {
                  var self = this;
                  self.saveUIVisible = new api.Value( false );
                  self.saveUIVisible.bind( function( to, from, params ){
                        self.toggleSaveUI( to, params ? params.id : null );
                  });
            },


            // @return void()
            // self.saveUIVisible.bind( function( visible ){
            //       self.toggleSaveUI( visible );
            // });
            toggleSaveUI : function( visible, sectionId ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndSetupSaveUITmpl({}) ).done( function( $_el ) {
                                  self.saveUIContainer = $_el;
                                  //display
                                  _.delay( function() {
                                      self.cachedElements.$body.addClass('nimble-save-ui-visible');
                                  }, 200 );
                                  // set section id input value
                                  $('#sek-saved-section-id').val( sectionId );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            self.cachedElements.$body.removeClass('nimble-save-ui-visible');
                            if ( $( '#nimble-top-save-ui' ).length > 0 ) {
                                  //remove Dom element after slide up
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


            // @return a section model with clean ids
            // also removes the section properties "id" and "level", which are dynamically set when dragging and dropping
            // Example of section model before preprocessing
            // {
            //    collection: [{…}]
            //    id: "" //<= to remove
            //    level: "section" // <= to remove
            //    options: {bg: {…}}
            //    ver_ini: "1.1.8"
            // }
            preProcessSektion : function( sectionModel ) {
                  var self = this, sektionCandidate = self.cleanIds( sectionModel );
                  return _.omit( sektionCandidate, function( val, key ) {
                        return _.contains( ['id', 'level'], key );
                  });
            },


            //@param = { }
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

                  // Attach click events
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
                              // response is {section_post_id: 436}
                              //self.saveUIVisible( false );
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
