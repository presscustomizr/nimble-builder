//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            ////////////////////////////////////////////////////////
            // TEMPLATE GALLERY
            ////////////////////////////////////////////////////////
            setupTemplateGallery : function() {
                  var self = this;
                  self.templateGalleryExpanded = new api.Value([]);
                  self.templateGalleryExpanded.bind( function( expanded ) {
                        self.cachedElements.$body.toggleClass( 'sek-template-gallery-expanded', expanded );
                        if ( expanded ) {
                              $('#customize-preview iframe').css('z-index', 1);
                              self.renderOrRefreshTempGallery();
                        } else {
                              $('#customize-preview iframe').css('z-index', '');
                        }
                  });

                  // API READY
                  api.previewer.bind('ready', function() {
                        self.templateGalleryExpanded( false );
                  });
            },

            //@params { scope : 'local' or 'global' }
            // print the tree
            renderOrRefreshTempGallery : function() {
                  var self = this,
                      _tmpl;
                  if( $('#nimble-template-gallery').length > 0 )
                    return;

                  // RENDER
                  // try {
                  //       _tmpl =  wp.template( 'nimble-template-gallery' )( {} );
                  // } catch( er ) {
                  //       api.errare( 'Error when parsing nimble-template-gallery template', er );
                  //       return false;
                  // }
                  $( '#customize-preview' ).after( $( '<div/>', {
                        id : 'nimble-template-gallery',
                        html : '<div class="czr-css-loader czr-mr-loader" style="display:block"><div></div><div></div><div></div></div>',
                  }));
                  $('#nimble-template-gallery').append('<div class="sek-tmpl-gallery-inner"></div>');

                  // Wait for the gallery to be fetched and rendered
                  self.getTemplateGalleryHtml().done( function( html ) {
                        $('#nimble-template-gallery').find('.sek-tmpl-gallery-inner').html( html );
                  });

                  // Schedule click event with delegation
                  $('#nimble-template-gallery').on('click', '.sek-tmpl-item', function( evt ) {
                        evt.preventDefault();
                        evt.stopPropagation();
                        console.log('ALORS TEMP ID ?', $(this).data('sek-tmpl-item-id') );
                        //api.czr_sektions.import_nimble_template( $(this).data('sek-tmpl-item-id') );

                        api.czr_sektions.import_nimble_template( 'test_one');// FOR TEST PURPOSES UNTIL THE COLLECTION IS SETUP

                        self.templateGalleryExpanded(false);
                  });
            },

            getTemplateGalleryHtml : function() {
                  var self = this,
                      _html = '';
                  var _templates = {
                        temp_one : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_two : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_three : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_four : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_fsour : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_fosur : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_five : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_six : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_seven : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        },
                        temp_height : {
                            thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                            preview_url : ''
                        }
                  };
                  _.each( _templates, function( _data, _temp_id ) {
                      console.log('SO?', _temp_id );
                      _html += '<div class="sek-tmpl-item" data-sek-tmpl-item-id="' + _temp_id + '">';
                        _html += '<div class="sek-tmpl-thumb"><img src="'+ _data.thumb_url +'"/></div>';
                      _html += '</div>';
                  });

                  return $.Deferred( function() {
                      var dfd = this;
                      _.delay( function() {
                          $('#nimble-template-gallery').find('.czr-css-loader').hide({
                              duration : 300,
                              complete : function() { $(this).remove();}
                          });
                          dfd.resolve( _html );
                      }, 1000);
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );