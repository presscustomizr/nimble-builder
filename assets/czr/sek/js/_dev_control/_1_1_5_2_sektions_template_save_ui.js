//global sektionsLocalizedData
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // SAVE TMPL DIALOG BLOCK
            // fired in ::initialize()
            setupSaveTmplUI : function() {
                  var self = this;
                  self.saveTmplUIVisible = new api.Value( false );
                  self.saveTmplUIVisible.bind( function( to ){
                        self.toggleSaveTmplUI( to );
                  });
            },


            // @return void()
            // self.saveTmplUIVisible.bind( function( visible ){
            //       self.toggleSaveTmplUI( visible );
            // });
            toggleSaveTmplUI : function( visible ) {
                  visible = _.isUndefined( visible ) ? true : visible;
                  console.log('SIO?', visible );
                  var self = this,
                      _renderAndSetup = function() {
                            $.when( self.renderAndsetupSaveTmplUITmpl({}) ).done( function( $_el ) {
                                  self.saveUIContainer = $_el;
                                  //display
                                  _.delay( function() {
                                      self.cachedElements.$body.addClass('sek-save-tmpl-ui-visible');
                                  }, 200 );
                                  // set tmpl id input value
                                  //$('#sek-saved-tmpl-id').val( tmplId );
                            });
                      },
                      _hide = function() {
                            var dfd = $.Deferred();
                            self.cachedElements.$body.removeClass('sek-save-tmpl-ui-visible');
                            if ( $( '#nimble-top-tmpl-save-ui' ).length > 0 ) {
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
                              self.saveTmplUIVisible( false );//should be already false
                        });
                  }
            },



            //@param = { }
            renderAndsetupSaveTmplUITmpl : function( params ) {
                  if ( $( '#nimble-top-tmpl-save-ui' ).length > 0 )
                    return $( '#nimble-top-tmpl-save-ui' );

                  var self = this;

                  try {
                        _tmpl =  wp.template( 'nimble-top-tmpl-save-ui' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing nimble-top-tmpl-save-ui template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );

                  // Attach click events
                  $('#nimble-top-tmpl-save-ui').on( 'click', '.sek-do-save-tmpl', function(evt) {
                        evt.preventDefault();
                        var $_title = $('#sek-saved-tmpl-title'),
                            tmpl_title = $_title.val(),
                            tmpl_description = $('#sek-saved-tmpl-description').val(),
                            collectionSettingId = self.localSectionsSettingId(),
                            currentLocalSettingValue = self.preProcessTmpl( api( collectionSettingId )() );

                        if ( _.isEmpty( tmpl_title ) ) {
                            $_title.addClass('error');
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

                        $('#sek-saved-tmpl-title').removeClass('error');

                        wp.ajax.post( 'sek_save_user_template', {
                              nonce: api.settings.nonce.save,
                              tmpl_title: tmpl_title,
                              tmpl_description: tmpl_description,
                              tmpl_data: JSON.stringify( currentLocalSettingValue ),
                              //skope_id: api.czr_skopeBase.getSkopeProperty( 'skope_id' )
                        })
                        .done( function( response ) {
                              console.log('ALORS SERVER RESP FOR SAVED TEMPLATE ?', response );
                              // response is {tmpl_post_id: 436}
                              //self.saveTmplUIVisible( false );
                              api.previewer.trigger('sek-notify', {
                                  type : 'success',
                                  duration : 10000,
                                  message : [
                                        '<span style="font-size:0.95em">',
                                          '<strong>@missi18n Your template has been saved.</strong>',
                                        '</span>'
                                  ].join('')
                              });
                        })
                        .fail( function( er ) {
                              console.log('ER ??', er );
                              api.errorLog( 'ajax sek_save_template => error', er );
                              api.previewer.trigger('sek-notify', {
                                  type : 'error',
                                  duration : 10000,
                                  message : [
                                        '<span style="font-size:0.95em">',
                                          '<strong>@missi18n error when saving template</strong>',
                                        '</span>'
                                  ].join('')
                              });
                        });
                  });//on click

                  $('.sek-cancel-save', '#nimble-top-tmpl-save-ui').on( 'click', function(evt) {
                        evt.preventDefault();
                        self.saveTmplUIVisible(false);
                  });

                  return $( '#nimble-top-tmpl-save-ui' );
            },



            // @return a tmpl model with clean ids
            // also removes the tmpl properties "id" and "level", which are dynamically set when dragging and dropping
            // Example of tmpl model before preprocessing
            // {
            //    collection: [{…}]
            //    id: "" //<= to remove
            //    level: "tmpl" // <= to remove
            //    options: {bg: {…}}
            //    ver_ini: "1.1.8"
            // }
            preProcessTmpl : function( tmpl_data ) {
                  console.log('TO DO => make sure template is ok to be saved');
                  return tmpl_data;
            },
      });//$.extend()
})( wp.customize, jQuery );
