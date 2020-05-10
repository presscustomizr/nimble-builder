//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            ////////////////////////////////////////////////////////
            // TEMPLATE GALLERY
            ////////////////////////////////////////////////////////
            // APRIL 2020 : for https://github.com/presscustomizr/nimble-builder/issues/651
            setupTemplateGallery : function() {


                  var self = this;
                  self.templateGalleryExpanded = new api.Value(false);

                  if ( !sektionsLocalizedData.isTemplateGalleryEnabled )
                    return;

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
                  if( $('#nimble-template-gallery').length < 1 ) {

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
                      $('#nimble-template-gallery')
                          .append('<div class="sek-tmpl-gallery-inner"></div>')
                          // Schedule click event with delegation
                          .on('click', '.sek-tmpl-item', function( evt ) {
                                evt.preventDefault();
                                evt.stopPropagation();
                                var tmpl_id = $(this).data('sek-tmpl-item-id');
                                console.log('ALORS TEMP ID ?', tmpl_id );
                                if ( _.isEmpty(tmpl_id) ) {
                                    api.errare('::renderOrRefreshTempGallery => error => invalid template id');
                                }
                                //api.czr_sektions.import_nimble_template( $(this).data('sek-tmpl-item-id') );
                                //api.czr_sektions.import_nimble_template( {template_name : 'test_one', from: 'nimble_api'});// FOR TEST PURPOSES UNTIL THE COLLECTION IS SETUP
                                api.czr_sektions.import_nimble_template( {template_name : tmpl_id, from: 'user'});

                                self.templateGalleryExpanded(false);
                          });
                  }

                  // Clean previous html
                  var $galleryInner = $('#nimble-template-gallery').find('.sek-tmpl-gallery-inner');
                  $galleryInner.html('');
                  // Wait for the gallery to be fetched and rendered
                  self.getTemplateGalleryHtml().done( function( html ) {
                        console.log('HTML? ', html );
                        $galleryInner.html( html );
                  });
            },



            getTemplateGalleryHtml : function() {
                  var self = this,
                      _html = '';
                  // var _templates = {
                  //       temp_one : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_two : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_three : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_four : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_fsour : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_fosur : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_five : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_six : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_seven : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       },
                  //       temp_height : {
                  //           thumb_url : 'https://nimblebuilder.com/wp-content/uploads/2020/04/2020-04-06_16-36-12.jpg',
                  //           preview_url : ''
                  //       }
                  // };

                  // _.each( _templates, function( _data, _temp_id ) {
                  //     console.log('SO?', _temp_id );
                  //     _html += '<div class="sek-tmpl-item" data-sek-tmpl-item-id="' + _temp_id + '">';
                  //       _html += '<div class="sek-tmpl-thumb"><img src="'+ _data.thumb_url +'"/></div>';
                  //     _html += '</div>';
                  // });
                  var _thumbUrl = sektionsLocalizedData.baseUrl + '/assets/admin/img/wire_frame.png';
                  var _dfd_ = $.Deferred();

                  self.getSavedTmplCollection().done( function( tmpl_collection ) {
                        _.each( tmpl_collection, function( _data, _temp_id ) {
                              console.log('SO?', _data );
                              _html += '<div class="sek-tmpl-item" data-sek-tmpl-item-id="' + _temp_id + '">';
                                _html += '<div class="sek-tmpl-thumb"><img src="'+ _thumbUrl +'"/></div>';
                                _html += '<div class="sek-tmpl-info">';
                                  _html += '<h3>' + _data.title + '</h3>';
                                  _html += '<p><i>' + '@missi18n Last modified : ' + _data.last_modified_date + '</i></p>';
                                  _html += '<p>' + _data.description + '</p>';
                                  _html += '<i class="material-icons" title="@missi18n use this template">add_circle_outline</i>';
                                _html += '</div>';
                              _html += '</div>';
                        });

                        var $cssLoader = $('#nimble-template-gallery').find('.czr-css-loader');
                        if ( $cssLoader.length > 0 ) {
                              $cssLoader.hide({
                                    duration : 300,
                                    complete : function() {
                                          $(this).remove();
                                          _dfd_.resolve( _html );
                                    }
                              });
                        } else {
                              _dfd_.resolve( _html );
                        }
                  });

                  return _dfd_.promise();
            }
      });//$.extend()
})( wp.customize, jQuery );