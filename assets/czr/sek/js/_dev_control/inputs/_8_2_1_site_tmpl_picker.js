//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            site_tmpl_picker : function( params ) {
                  var input = this,
                        _html,
                        $hidInputEl = $( '[data-czrtype]', input.container ),
                        _defaultData = { site_tmpl_id : '_no_site_tmpl_', site_tmpl_source : 'user_tmpl' },
                        siteTmplData,
                        site_tmpl_id, site_tmpl_source,
                        tmplTitle = '';

                  var printCurrentTemplateVal = function() {
                        var _doRender = function( site_tmpl_id, tmplTitle ) {
                              _html = '<span class="sek-current-site-tmpl">';
                                    if ( '_no_site_tmpl_' === site_tmpl_id || _.isEmpty( site_tmpl_id ) ) {
                                          _html += '@missi18n => No template';
                                          input.container.removeClass('sek-has-site-tmpl');
                                    } else {
                                          _html += '@missi18n => Active template : ' +  ( _.isEmpty(tmplTitle) ? site_tmpl_id : tmplTitle );
                                          input.container.addClass('sek-has-site-tmpl');
                                    }
                              _html += '</span>';
                              input.container.find('.sek-current-site-tmpl').remove();
                              input.container.find('.czr-input').prepend(_html);
                        };

                        site_tmpl_id = input();
                        if ( !_.isString(site_tmpl_id) || _.isEmpty(site_tmpl_id) ) {
                              site_tmpl_id = '_no_site_tmpl_';
                        }

                        // Get the title
                        if ( '_no_site_tmpl_' === site_tmpl_id ) {
                              _doRender(site_tmpl_id, tmplTitle);
                        } else {
                              // NB stores the site template id as a concatenation of template source + '___' + template name
                              // Ex : user_tmpl___landing-page-for-services
                              if ( _.isString( site_tmpl_id ) ) {
                                    if ( 'user_tmpl' === site_tmpl_id.substring(0,9) ) {
                                          site_tmpl_source = 'user_tmpl';
                                          site_tmpl_id = site_tmpl_id.replace('user_tmpl___','');
                                    } else if ( 'api_tmpl' === site_tmpl_id.substring(0,8) ) {
                                          site_tmpl_source = 'api_tmpl';
                                          site_tmpl_id = site_tmpl_id.replace('api_tmpl___','');
                                    } else {
                                          api.errare('Error => invalid site template source');
                                          return;
                                    }
                              } else {
                                    api.errare('Error => site template must be a string');
                                    return;
                              }
                              _tmpl_collection_promise = 'user_tmpl' === site_tmpl_source ? api.czr_sektions.setSavedTmplCollection : api.czr_sektions.getApiTmplCollection;
                              _tmpl_collection_promise.call(api.czr_sektions)
                              .done( function(tmpl_collection) {
                                    if ( _.isObject(tmpl_collection) && tmpl_collection[site_tmpl_id] && tmpl_collection[site_tmpl_id].title ) {
                                          tmplTitle = tmpl_collection[site_tmpl_id].title;
                                    } else {
                                          api.errare('Error => site template not found in collection ' + site_tmpl_source );
                                    }
                                    _doRender(site_tmpl_id, tmplTitle);
                              })
                              .fail( function() {
                                    api.errare('printCurrentTemplateVal error when getting collection promise failed', params );
                                    _dfd_.resolve('');
                              });
                        }
                  };

                  // Schedule events
                  input.container.on( 'click', '[data-sek-group-scope]', function( evt, args ) {
                        evt.stopPropagation();
                        var scope = $(this).data( 'sek-group-scope' );

                        if ( _.isEmpty( scope ) ) {
                              api.errare( 'site_tmpl_picker input => invalid scope provided.', scope );
                              return;
                        }
                        // Close picking when re-click on the button while the template gallery is already displayed
                        if ( input.container.hasClass('sek-site-tmpl-picking-active') ) {
                              api.czr_sektions._site_tmpl_scope = null;
                              api.czr_sektions.templateGalleryExpanded( false );
                              $('[data-input-type="site_tmpl_picker"]').removeClass('sek-site-tmpl-picking-active');
                        } else {
                              api.czr_sektions._site_tmpl_scope = input.id;
                              api.czr_sektions.templateGalleryExpanded( true );
                              $('[data-input-type="site_tmpl_picker"]').removeClass('sek-site-tmpl-picking-active');
                              input.container.addClass('sek-site-tmpl-picking-active');
                        }
                  });//on('click')
                  input.container.on( 'click', '.sek-remove-site-tmpl', function( evt, args ) {
                        evt.stopPropagation();
                        $hidInputEl.trigger('nb-set-site-tmpl', _defaultData );
                  });//on('click')

                  $hidInputEl.on('nb-set-site-tmpl', function( evt, args ) {
                        if ( !_.isObject(args) ) {
                              api.errare('site_tmpl_picker => error => wrong args on tmpl pick', args );
                              return;
                        }

                        // _defaultData = { site_tmpl_id : '_no_site_tmpl_', site_tmpl_source : 'user_tmpl' }
                        siteTmplData = $.extend( true, {}, _defaultData );
                        siteTmplData = $.extend( siteTmplData, args );
                        // Set input value and try to print title
                        if ( '_no_site_tmpl_' === siteTmplData.site_tmpl_id ) {
                              input( siteTmplData.site_tmpl_id );
                        } else {
                              // NB stores the site template id as a concatenation of template source + '___' + template name
                              // Ex : user_tmpl___landing-page-for-services
                              input( siteTmplData.site_tmpl_source + '___' + siteTmplData.site_tmpl_id );
                        }

                        try{ printCurrentTemplateVal(); } catch(er) { api.errare('Error when printing template val', er ); }
                        api.czr_sektions.templateGalleryExpanded( false );
                        $('[data-input-type="site_tmpl_picker"]').removeClass('sek-site-tmpl-picking-active');
                  });

                  try{ printCurrentTemplateVal(); } catch(er) { api.errare('Error when printing template val', er ); }
            }
            
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );