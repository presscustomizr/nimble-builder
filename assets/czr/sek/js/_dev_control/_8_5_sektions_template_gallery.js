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
                  self.tmplInjectDialogVisible = new api.Value(false);// Hidden by default
                  if ( !sektionsLocalizedData.isTemplateGalleryEnabled )
                    return;

                  self.tmplSearchFieldVisible = new api.Value(false);// Hidden by default
                  self.tmplSearchFieldVisible.bind( function( visible ) {
                        var $tmplSearchWrap = self.cachedElements.$body.find('.sek-tmpl-filter-wrapper');
                        if ( visible ) {
                              $tmplSearchWrap.fadeIn('fast');
                        } else {
                              $tmplSearchWrap.fadeOut('fast');
                        }
                  });
                  
                  self.templateGalleryExpanded.bind( function( expanded ) {
                        self.cachedElements.$body.toggleClass( 'sek-template-gallery-expanded', expanded );
                        if ( expanded ) {
                              // close template saver
                              // close level tree
                              self.tmplDialogVisible(false);
                              self.levelTreeExpanded(false);
                              self.tmplInjectDialogVisible(false);
                              $('#customize-preview iframe').css('z-index', 1);
                              self.renderOrRefreshTempGallery( { tmpl_source:'api_tmpl' } );
                        } else {
                              $('#customize-preview iframe').css('z-index', '');
                              api.trigger('nb-template-gallery-closed');
                              // SITE TEMPLATE PICKING
                              // When closing template gallery, make sure NB reset the possible previous tmpl scope used in a site template picking scenario
                              self._site_tmpl_scope = null;
                              // If template gallery was closed during a site template picking scenario, make sure input state is reset
                              $('[data-input-type="site_tmpl_picker"]').removeClass('sek-site-tmpl-picking-active');
                        }
                  });

                  self.tmplInjectDialogVisible.bind( function( expanded ) {
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
            // @params : { tmpl_source:'api_tmpl'}
            renderOrRefreshTempGallery : function( params ) {
                  params = $.extend( {tmpl_source:'api_tmpl'}, params || {} );
                  var self = this,
                      $tmplGalWrapper;
                  if( $('#nimble-tmpl-gallery').length < 1 ) {
                        $.when( self.renderTmplGalleryUI({}) ).done( function() {
                              self.setupTmplGalleryDOMEvents();
                        });
                  }

                  // Clean previous html
                  $('#nimble-tmpl-gallery').find('.sek-tmpl-gallery-inner').html('');

                  var _doPrintTmplGalleryHtml = function(params) {
                        return self.getTemplateGalleryHtml( params ).done( function( html ) {
                              $tmplGalWrapper = $('#nimble-tmpl-gallery');
                              $tmplGalWrapper.find('.sek-tmpl-gallery-inner').html( html );
                              $tmplGalWrapper.removeClass('sek-is-site-tmpl-mode');
                              // Site template picking mode => add a class in order to display only api site templates
                              if ( 'api_tmpl' === params.tmpl_source && self._site_tmpl_scope && !_.isEmpty( self._site_tmpl_scope ) ) {
                                    $tmplGalWrapper.addClass('sek-is-site-tmpl-mode');
                              }
                        });
                  };
                  // Wait for the gallery to be fetched and rendered
                  _doPrintTmplGalleryHtml( params ).done( function( html ) {
                        if ( _.isEmpty( html ) && 'api_tmpl' === params.tmpl_source ) {
                              if ( typeof window.console.log == 'function' ) {
                                    console.log('Nimble Builder API problem => could not fetch templates');
                              }
                              _doPrintTmplGalleryHtml( {tmpl_source:'user_tmpl' } );
                        } else {
                              $tmplGalWrapper = $('#nimble-tmpl-gallery');
                              $tmplGalWrapper.find('#sek-tmpl-source-switcher').show();
                              // Reset template source switcher buttons
                              $tmplGalWrapper.find('#sek-tmpl-source-switcher button').attr('aria-pressed', "false").removeClass('is-selected');
                              $tmplGalWrapper.find('[data-sek-tmpl-source="'+ params.tmpl_source +'"]').attr('aria-pressed', "true").addClass('is-selected');
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
                        if ( _.isEmpty( tmpl_collection ) && 'user_tmpl' === params.tmpl_source ) {
                              var _placeholdImgUrl = [ sektionsLocalizedData.baseUrl , '/assets/admin/img/empty_tmpl_collection_notice.jpg',  '?ver=' , sektionsLocalizedData.nimbleVersion ].join(''),
                                    doc_url = 'https://docs.presscustomizr.com/article/426-how-to-save-and-reuse-templates-with-nimble-builder';

                              _html += '<div class="sek-tmpl-empty-collection">';
                                    _html += '<p>' + sektionsLocalizedData.i18n['You did not save any templates yet.'] + '</p>';
                                    _html += '<img src="'+ _placeholdImgUrl +'" />';
                                    _html += '<br/><a href="'+ doc_url +'" target="_blank" rel="noreferrer nofollow">'+ doc_url +'</a>';
                              _html += '</div>';
                        } else {
                              _.each( tmpl_collection, function( _data, _temp_id ) {
                                    if( !_.isEmpty( _data.description ) ) {
                                        _titleAttr = [ _data.title, _data.last_modified_date, _data.description ].join(' | ');
                                    } else {
                                        _titleAttr = [ _data.title, _data.last_modified_date ].join(' | ');
                                    }
      
                                    _thumbUrl = !_.isEmpty( _data.thumb_url ) ? _data.thumb_url : _defaultThumbUrl;

                                    _html += '<div class="sek-tmpl-item" data-sek-tmpl-item-id="' + _temp_id + '" data-sek-tmpl-item-source="'+ params.tmpl_source +'" data-sek-api-site-tmpl="' + (_data.is_site_tmpl ? "true" : "false") +'" data-sek-is-pro-tmpl="' + (_data.is_pro_tmpl ? "yes" : "no") + '">';
                                          //_html += '<div class="sek-tmpl-thumb"><img src="'+ _thumbUrl +'"/></div>';
                                          _html += '<div class="tmpl-top-title"><h3>' + _data.title + '</h3></div>';
                                                _html += '<div class="tmpl-thumb-and-info-wrap">';
                                                      _html += '<div class="sek-tmpl-thumb" style="background-image:url('+ _thumbUrl +')"></div>';
                                                      _html += '<div class="sek-tmpl-info" title="'+ _titleAttr +'">';
                                                      // _html += '<h3 class="tmpl-title tmpl-api-hide">' + _data.title + '</h3>';
                                                      _html += '<p class="tmpl-desc tmpl-api-hide">' + _data.description + '</p>';
                                                      _html += '<p class="tmpl-date tmpl-api-hide"><i>' + [ sektionsLocalizedData.i18n['Last modified'], ' : ', _data.last_modified_date ].join(' ') + '</i></p>';
                                                      _html += '<i class="material-icons use-tmpl" title="'+ sektionsLocalizedData.i18n['Use this template'] +'">add_circle_outline</i>';
                                                      if ( 'user_tmpl' === params.tmpl_source ) {
                                                            _html += '<i class="material-icons edit-tmpl" title="'+ sektionsLocalizedData.i18n['Edit this template'] +'">edit</i>';
                                                            _html += '<i class="material-icons remove-tmpl" title="'+ sektionsLocalizedData.i18n['Remove this template'] +'">delete_forever</i>';
                                                      }
                                                      if ( _data.is_pro_tmpl ) {
                                                            _html += '<div class="sek-is-pro-template"><img src="' + sektionsLocalizedData.czrAssetsPath + 'sek/img/pro_orange.svg" alt="Pro feature"/></div>';
                                                      }

                                                      if ( 'api_tmpl' === params.tmpl_source ) {
                                                            if ( _data.demo_url && -1 != _data.demo_url.indexOf('http') ) {
                                                                  _html += '<div class="sek-tmpl-demo-link tmpl-api-hide"><a href="' + _data.demo_url + '?utm_source=usersite&amp;utm_medium=link&amp;utm_campaign=tmpl_demos" target="_blank" rel="noopener noreferrer">' + sektionsLocalizedData.i18n['Live demo'] + ' <i class="fas fa-external-link-alt"></i></a></div>';
                                                            }
                                                            if ( _data.is_site_tmpl ) {
                                                                  _html += '<div class="sek-is-site-template" title="Site templates include dynamic template tags.">Site Template</div>';
                                                            }
                                                      }
                                                _html += '</div>';
                                          _html += '</div>';
                                    _html += '</div>';
                              });
                              if ( 'api_tmpl' === params.tmpl_source && !_.isEmpty(_html) ) {
                                    _html += '<div class="sek-tmpl-coming-soon">';
                                          _html += '<p>' + sektionsLocalizedData.i18n['🍥 More templates coming...'] + '</p>';
                                    _html += '</div>';
                              }
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

                  var _tmpl_collection_promise = 'user_tmpl' === params.tmpl_source ? self.setSavedTmplCollection : self.getApiTmplCollection;
                  _tmpl_collection_promise.call(self)
                        .done( function(tmpl_collection) { 
                              setTimeout( function() { 
                                    _doRender(tmpl_collection);
                                    self.tmplSearchFieldVisible( !_.isEmpty( tmpl_collection ) );
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
                  var $galWrapper = $('#nimble-tmpl-gallery'),
                        self = this;

                  $galWrapper
                        // Schedule click event with delegation
                        // PICK A TEMPLATE
                        .on('click', '.sek-tmpl-item .use-tmpl', function( evt ) {
                              evt.preventDefault();
                              evt.stopPropagation();
                              var _tmpl_id = $(this).closest('.sek-tmpl-item').data('sek-tmpl-item-id'),
                                    _tmpl_source = $(this).closest('.sek-tmpl-item').data('sek-tmpl-item-source'),
                                    _tmpl_title = $(this).closest('.sek-tmpl-item').find('.tmpl-top-title h3').html(),
                                    _tmpl_is_pro = 'yes' === $(this).closest('.sek-tmpl-item').data('sek-is-pro-tmpl');

                              if ( _.isEmpty(_tmpl_id) ) {
                                    api.errare('::setupTmplGalleryDOMEvents => error => invalid template id');
                                    return;
                              }

                              if ( _tmpl_is_pro ) {
                                    var _problemMsg;
                                    if ( sektionsLocalizedData.isPro ) {
                                          // Check if :
                                          // 1) the license key has been entered
                                          // 2) the status is 'valid'
                                          if ( _.isEmpty( sektionsLocalizedData.pro_license_key ) ) {
                                                _problemMsg = sektionsLocalizedData.i18n['Missing license key'];
                                          } else if ( 'valid' !== sektionsLocalizedData.pro_license_status ) {
                                                _problemMsg = sektionsLocalizedData.i18n['Pro license problem'];
                                          }
                                          // If we have a problem msg let's print it and bail now
                                          if ( !_.isEmpty( _problemMsg ) ) {
                                                api.previewer.trigger('sek-notify', {
                                                      type : 'error',
                                                      duration : 60000,
                                                      is_pro_notif : true,
                                                      notif_id : 'pro_tmpl_error',
                                                      message : [
                                                            '<span style="font-size:0.95em">',
                                                            '<strong>'+ _problemMsg + '</strong>',
                                                            '</span>'
                                                      ].join('')
                                                });
                                                return;
                                          }

                                    } else {
                                          api.previewer.trigger('sek-notify', {
                                                type : 'info',
                                                duration : 60000,
                                                //is_pro_notif : true,
                                                notif_id : 'go_pro',
                                                message : [
                                                      '<span style="font-size:0.95em">',
                                                      '<strong>'+ sektionsLocalizedData.i18n['Go pro link when click on pro tmpl or section'] + '</strong>',
                                                      '</span>'
                                                ].join('')
                                          });
                                          return;
                                    }
                              }//if tmpl is pro

                              // Site template mode ?
                              if ( self._site_tmpl_scope && !_.isEmpty( self._site_tmpl_scope ) ) {
                                    var $siteTmplInput = $( '[data-czrtype="' + self._site_tmpl_scope +'"]' );
                                    if ( $siteTmplInput.length > 0 ) {
                                          if ( !_.contains(['user_tmpl', 'api_tmpl'], _tmpl_source ) ) {
                                                api.errare('Error when picking site template => invalid tmpl source');
                                                return;
                                          }

                                          $siteTmplInput.trigger('nb-set-site-tmpl', {
                                                site_tmpl_id : _tmpl_id,
                                                site_tmpl_source : _tmpl_source,
                                                site_tmpl_title : _tmpl_title
                                          });
                                    }
                                    return;
                              }

                              // if current page has NB sections, display an import dialog, otherwise import now
                              if ( self.hasCurrentPageNBSectionsNotHeaderFooter() ) {
                                    self._tmplNameWhileImportDialog = _tmpl_id;
                                    self._tmplSourceWhileImportDialog = _tmpl_source;
                                    self._tmplIsProWhileImportDialog = _tmpl_is_pro;
                                    self.tmplInjectDialogVisible(true);
                              } else {
                                    api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true, duration : 30000 });
                                    //api.czr_sektions.get_gallery_tmpl_json_and_inject( $(this).data('sek-tmpl-item-id') );
                                    //api.czr_sektions.get_gallery_tmpl_json_and_inject( {tmpl_name : 'test_one', tmpl_source: 'api_tmpl'});// FOR TEST PURPOSES UNTIL THE COLLECTION IS SETUP
                                    api.czr_sektions.get_gallery_tmpl_json_and_inject( {
                                          tmpl_name : _tmpl_id,
                                          tmpl_source: _tmpl_source,
                                          tmpl_is_pro: _tmpl_is_pro
                                    }).always( function() {
                                          api.previewer.send( 'sek-clean-loader');
                                    });
                                    self.templateGalleryExpanded(false);
                              }
                        })
                        // PICK AN IMPORT MODE WHEN PAGE HAS SECTIONS ALREADY
                        .on('click', '.sek-tmpl-gal-inject-dialog .sek-ui-button', function( evt ) {
                              evt.preventDefault();
                              evt.stopPropagation();
                              var tmpl_inject_mode = $(this).data('sek-tmpl-inject-mode');

                              // Did user cancel tmpl injection ?
                              if ( 'cancel' === tmpl_inject_mode ) {
                                    self.tmplInjectDialogVisible(false);
                                    return;
                              }

                              // 3 possible import modes : replace, before, after
                              if ( !_.contains(['replace', 'before', 'after'], tmpl_inject_mode ) ) {
                                    api.errare('::setupTmplGalleryDOMEvents => error => invalid import mode');
                                    return;
                              }
                              api.previewer.send( 'sek-maybe-print-loader', { fullPageLoader : true, duration : 30000 });
                              api.czr_sektions.get_gallery_tmpl_json_and_inject({
                                    tmpl_name : self._tmplNameWhileImportDialog,
                                    tmpl_source: self._tmplSourceWhileImportDialog,
                                    tmpl_is_pro: self._tmplIsProWhileImportDialog,
                                    tmpl_inject_mode: tmpl_inject_mode
                              }).always( function() {
                                    api.previewer.send( 'sek-clean-loader', { cleanFullPageLoader : true });
                              });
                              // api.czr_sektions.get_gallery_tmpl_json_and_inject({
                              //       tmpl_name : 'test_one',
                              //       tmpl_source: 'nimble_api',
                              //       tmpl_inject_mode: tmpl_inject_mode
                              // });
                              self.templateGalleryExpanded(false);
                        })
                        // SEARCH ACTIONS
                        .on('propertychange change click keyup input paste', '.sek-filter-tmpl', _.debounce( function(evt) {
                              evt.preventDefault();
                              var _s = $(this).val();
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
                              evt.preventDefault();
                              $('#sek-tmpl-source-switcher button').removeClass('is-selected').attr('aria-pressed', "false");
                              $(this).addClass('is-selected').attr('aria-pressed', "true");
                              self.renderOrRefreshTempGallery( { tmpl_source: $(this).data('sek-tmpl-source') } );
                        });
              },
      });//$.extend()
})( wp.customize, jQuery );