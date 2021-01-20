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
                  self.tmplImportDialogVisible = new api.Value(false);// Hidden by default
                  if ( !sektionsLocalizedData.isTemplateGalleryEnabled )
                    return;

                  self.templateGalleryExpanded.bind( function( expanded ) {
                        self.cachedElements.$body.toggleClass( 'sek-template-gallery-expanded', expanded );
                        if ( expanded ) {
                              // close template saver
                              // close level tree
                              self.tmplDialogVisible(false);
                              self.levelTreeExpanded(false);
                              self.tmplImportDialogVisible(false);
                              $('#customize-preview iframe').css('z-index', 1);
                              self.renderOrRefreshTempGallery( { what:'api_tmpl' });
                        } else {
                              $('#customize-preview iframe').css('z-index', '');
                              api.trigger('nb-template-gallery-closed');
                        }
                  });

                  self.tmplImportDialogVisible.bind( function( expanded ) {
                        self.cachedElements.$body.toggleClass( 'sek-tmpl-dialog-expanded', expanded );
                        if ( expanded ) {
                              // close template saver
                              // close level tree
                              self.tmplDialogVisible(false);
                              self.levelTreeExpanded(false);
                              $('#customize-preview iframe').css('z-index', 1);
                        }
                  });

                  // API READY
                  api.previewer.bind('ready', function() {
                        self.templateGalleryExpanded( false );
                  });
            },

            renderTmplGalleryUI : function() {
                  if ( $('#nimble-tmpl-gallery').length > 0 )
                    return $('#nimble-tmpl-gallery');

                  var self = this;

                  try {
                        _tmpl =  wp.template( 'nimble-top-tmpl-gallery' )( {} );
                  } catch( er ) {
                        api.errare( 'Error when parsing nimble-top-tmpl-gallery template', er );
                        return false;
                  }
                  $('#customize-preview').after( $( _tmpl ) );
                  return $('#nimble-tmpl-gallery');
            },

            // print and schedule dom events
            renderOrRefreshTempGallery : function( params ) {
                  params = $.extend( {what:'api_tmpl'}, params || {} );
                  var self = this,
                      _tmpl,
                      $tmplGalWrapper;
                  if( $('#nimble-tmpl-gallery').length < 1 ) {
                        $.when( self.renderTmplGalleryUI({}) ).done( function() {
                              self.setupTmplGalleryDOMEvents();
                        });
                  }

                  // Clean previous html
                  $('#nimble-tmpl-gallery').find('.sek-tmpl-gallery-inner').html('');

                  var _doPrintTmplGalleryHtml = function(params) {
                        self.getTemplateGalleryHtml( params ).done( function( html ) {
                              $tmplGalWrapper = $('#nimble-tmpl-gallery');
                              $tmplGalWrapper.find('.sek-tmpl-gallery-inner').html( html );
                        });
                        return self.getTemplateGalleryHtml( params );
                  };
                  // Wait for the gallery to be fetched and rendered
                  _doPrintTmplGalleryHtml( params ).done( function( html ) {
                        if ( _.isEmpty( html ) && 'api_tmpl' === params.what ) {
                              if ( typeof window.console.log == 'function' ) {
                                    console.log('Nimble Builder API problem => could not fetch templates');
                              }
                              _doPrintTmplGalleryHtml( {what:'user_tmpl'} );
                        } else {
                              $tmplGalWrapper = $('#nimble-tmpl-gallery');
                              $tmplGalWrapper.find('#sek-tmpl-source-switcher').show();
                              // Reset template source switcher buttons
                              $tmplGalWrapper.find('#sek-tmpl-source-switcher button').attr('aria-pressed', "false").removeClass('is-selected');
                              $tmplGalWrapper.find('[data-sek-tmpl-type="'+ params.what +'"]').attr('aria-pressed', "true").addClass('is-selected');
                        }
                  });
            },

            // @return html
            getTemplateGalleryHtml : function( params ) {
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
                  // };

                  // _.each( _templates, function( _data, _temp_id ) {
                  //     console.log('SO?', _temp_id );
                  //     _html += '<div class="sek-tmpl-item" data-sek-tmpl-item-id="' + _temp_id + '">';
                  //       _html += '<div class="sek-tmpl-thumb"><img src="'+ _data.thumb_url +'"/></div>';
                  //     _html += '</div>';
                  // });
                  var _defaultThumbUrl = [ sektionsLocalizedData.baseUrl , '/assets/admin/img/wire_frame.png',  '?ver=' , sektionsLocalizedData.nimbleVersion ].join(''),
                      _dfd_ = $.Deferred(),
                      _titleAttr,
                      _thumbUrl,
                      $cssLoader = $('#nimble-tmpl-gallery').find('.czr-css-loader');

                  $cssLoader.show();

                  var _doRender = function( tmpl_collection ) {
                        if ( _.isEmpty( tmpl_collection ) && 'user_tmpl' === params.what ) {
                              var _placeholdImgUrl = [ sektionsLocalizedData.baseUrl , '/assets/admin/img/empty_tmpl_collection_notice.jpg',  '?ver=' , sektionsLocalizedData.nimbleVersion ].join(''),
                                    doc_url = 'https://docs.presscustomizr.com/article/417-how-to-save-and-reuse-sections-with-nimble-builder';

                              _html += '<div class="sek-tmpl-empty-collection">';
                                    _html += '<p>' + sektionsLocalizedData.i18n['You did not save any template yet.'] + '</p>';
                                    _html += '<img src="'+ _placeholdImgUrl +'" />';
                                    //_html += '<br/><a href="'+ doc_url +'" target="_blank" rel="noreferrer nofollow">'+ doc_url +'</a>';
                              _html += '</div>';
                        } else {
                              _.each( tmpl_collection, function( _data, _temp_id ) {
                                    if( !_.isEmpty( _data.description ) ) {
                                        _titleAttr = [ _data.title, _data.last_modified_date, _data.description ].join(' | ');
                                    } else {
                                        _titleAttr = [ _data.title, _data.last_modified_date ].join(' | ');
                                    }
      
                                    _thumbUrl = !_.isEmpty( _data.thumb_url ) ? _data.thumb_url : _defaultThumbUrl;                        
      
                                    _html += '<div class="sek-tmpl-item" data-sek-tmpl-item-id="' + _temp_id + '">';
                                      //_html += '<div class="sek-tmpl-thumb"><img src="'+ _thumbUrl +'"/></div>';
                                      _html += '<div class="sek-tmpl-thumb" style="background-image:url('+ _thumbUrl +')"></div>';
                                      _html += '<div class="sek-tmpl-info" title="'+ _titleAttr +'">';
                                        _html += '<h3 class="tmpl-title">' + _data.title + '</h3>';
                                        _html += '<p class="tmpl-date"><i>' + [ sektionsLocalizedData.i18n['Last modified'], ' : ', _data.last_modified_date ].join(' ') + '</i></p>';
                                        _html += '<p class="tmpl-desc">' + _data.description + '</p>';
                                        _html += '<i class="material-icons use-tmpl" title="'+ sektionsLocalizedData.i18n['Use this template'] +'">add_circle_outline</i>';
                                        if ( 'user_tmpl' === params.what ) {
                                          _html += '<i class="material-icons edit-tmpl" title="'+ sektionsLocalizedData.i18n['Edit this template'] +'">edit</i>';
                                          _html += '<i class="material-icons remove-tmpl" title="'+ sektionsLocalizedData.i18n['Remove this template'] +'">delete_forever</i>';
                                        }
                                        if ( "true" == _data.is_pro_tmpl ) {
                                          _html += '<div class="sek-is-pro"><img src="' + sektionsLocalizedData.czrAssetsPath + 'sek/img/pro_orange.svg" alt="Pro feature"/></div>';
                                        }
                                      _html += '</div>';
                                    _html += '</div>';
                              });
                        }
                        
                        
                        if ( $cssLoader.length > 0 ) {
                              $cssLoader.hide({
                                    duration : 100,
                                    complete : function() {
                                          //$(this).remove();
                                          _dfd_.resolve( _html );
                                    }
                              });
                        } else {
                              _dfd_.resolve( _html );
                        }
                  };//_doRender

                  var _tmpl_collection_promise = 'user_tmpl' === params.what ? self.setSavedTmplCollection : self.getApiTmplCollection;
                  _tmpl_collection_promise.call(self)
                        .done( function(tmpl_collection) { 
                              setTimeout( function() { 
                                    _doRender(tmpl_collection);
                              }, 0 );
                        })
                        .fail( function() {
                              console.log('tmpl collection promise failed', params );
                              _dfd_.resolve('');
                        });
                  return _dfd_.promise();
            },



            // @return void()
            setupTmplGalleryDOMEvents : function() {
                  var $galWrapper = $('#nimble-tmpl-gallery');
                  var self = this;
                  $galWrapper
                        // Schedule click event with delegation
                        // PICK A TEMPLATE
                        .on('click', '.sek-tmpl-item .use-tmpl', function( evt ) {
                              evt.preventDefault();
                              evt.stopPropagation();
                              var tmpl_id = $(this).closest('.sek-tmpl-item').data('sek-tmpl-item-id');
                              if ( _.isEmpty(tmpl_id) ) {
                                    api.errare('::setupTmplGalleryDOMEvents => error => invalid template id');
                                    return;
                              }
      
                              // if current page has NB sections, display an import dialog, otherwise import now
                              if ( self.hasCurrentPageNBSectionsNotHeaderFooter() ) {
                                    self._tmplNameWhileImportDialog = tmpl_id;
                                    self.tmplImportDialogVisible(true);
                              } else {
                                    //api.czr_sektions.get_gallery_tmpl_json_and_inject( $(this).data('sek-tmpl-item-id') );
                                    //api.czr_sektions.get_gallery_tmpl_json_and_inject( {template_name : 'test_one', from: 'nimble_api'});// FOR TEST PURPOSES UNTIL THE COLLECTION IS SETUP
                                    api.czr_sektions.get_gallery_tmpl_json_and_inject( {template_name : tmpl_id, from: 'user'});
                                    self.templateGalleryExpanded(false);
                              }
                        })
                        // PICK AN IMPORT MODE WHEN PAGE HAS SECTIONS ALREADY
                        .on('click', '.sek-tmpl-gal-import-dialog .sek-ui-button', function( evt ) {
                              evt.preventDefault();
                              evt.stopPropagation();
                              // 3 possible import modes : replace, before, after
                              var tmpl_inject_mode = $(this).data('sek-tmpl-inject-mode');
                              if ( !_.contains(['replace', 'before', 'after'], tmpl_inject_mode ) ) {
                                    api.errare('::setupTmplGalleryDOMEvents => error => invalid import mode');
                                    return;
                              }
                              api.czr_sektions.get_gallery_tmpl_json_and_inject({
                                    template_name : self._tmplNameWhileImportDialog,
                                    from: 'user',
                                    tmpl_inject_mode: tmpl_inject_mode
                              });
                              // api.czr_sektions.get_gallery_tmpl_json_and_inject({
                              //       template_name : 'test_one',
                              //       from: 'nimble_api',
                              //       tmpl_inject_mode: tmpl_inject_mode
                              // });
                              self.templateGalleryExpanded(false);
                        })
                        // SEARCH ACTIONS
                        .on('propertychange change click keyup input paste', '.sek-filter-tmpl', _.debounce( function(evt) {
                              evt.preventDefault();
                              var _s = $(this).val();
                              //console.log('searched string ??', _s );
                              var _reset = function() {
                                    $galWrapper.removeClass('search-active');
                                    $galWrapper.find('.sek-tmpl-item').each( function() {
                                          $(this).removeClass('search-match');
                                    });
                              };
                              if ( !_.isString(_s) ) {
                                    _reset();
                                    return;
                              }
                              _s = _s.trim().toLowerCase();
                              if ( _.isEmpty( _s.replace(/\s/g, '') ) ) {
                                    _reset();
                              } else {
                                    $galWrapper.addClass('search-active');
                                    var title,desc,date,titleMatch, descMatch,dateMatch;
                                    $galWrapper.find('.sek-tmpl-item').each( function() {
                                          title = ( $(this).find('.tmpl-title').html() + '' ).toLowerCase();
                                          desc = ( $(this).find('.tmpl-desc').html() + '' ).toLowerCase();
                                          date = ( $(this).find('.tmpl-date').html() + '' ).toLowerCase();
                                          titleMatch = -1 != title.indexOf(_s);
                                          descMatch = -1 != desc.indexOf(_s);
                                          dateMatch = -1 != date.indexOf(_s);
                                          $(this).toggleClass( 'search-match', titleMatch || descMatch || dateMatch );
                                    });
                              }
      
                        }, 100 ) )
                        // EDIT
                        .on( 'click', '.sek-tmpl-info .edit-tmpl', function(evt) {
                              evt.preventDefault();
                              var _focusOnEditCandidate = function( mode ) {
                                    self.tmplDialogMode( 'edit' );
                                    // self unbind
                                    self.tmplDialogMode.unbind( _focusOnEditCandidate );
                              };
                              self.tmplToEdit = $(this).closest("[data-sek-tmpl-item-id]").data('sek-tmpl-item-id');
                              self.tmplDialogMode.bind( _focusOnEditCandidate );
                              self.tmplDialogVisible(true);
                        })
                        // REMOVE
                        .on( 'click', '.sek-tmpl-info .remove-tmpl', function(evt) {
                              evt.preventDefault();
                              var _focusOnRemoveCandidate = function( mode ) {
                                    self.tmplDialogMode( 'remove' );
                                    // self unbind
                                    self.tmplDialogMode.unbind( _focusOnRemoveCandidate );
                              };
                              self.tmplToRemove = $(this).closest("[data-sek-tmpl-item-id]").data('sek-tmpl-item-id');
                              self.tmplDialogMode.bind( _focusOnRemoveCandidate );
                              self.tmplDialogVisible(true);
                        })
                        .on( 'click', '.sek-close-dialog', function(evt) {
                                    evt.preventDefault();
                                    self.templateGalleryExpanded( false );
                        })
                        .on( 'click', '#sek-tmpl-source-switcher button', function( evt ) {
                              //console.log('SO CLICK ?', $(this).data('sek-tmpl-type' ) );
                              evt.preventDefault();
                              $('#sek-tmpl-source-switcher button').removeClass('is-selected').attr('aria-pressed', "false");
                              $(this).addClass('is-selected').attr('aria-pressed', "true");
                              self.renderOrRefreshTempGallery( { what : $(this).data('sek-tmpl-type') } );
                        });
              },
      });//$.extend()
})( wp.customize, jQuery );