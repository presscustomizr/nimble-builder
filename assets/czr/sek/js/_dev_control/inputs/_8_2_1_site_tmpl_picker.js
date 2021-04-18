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
                        _defaultData = {
                              site_tmpl_id : '_no_site_tmpl_',
                              site_tmpl_source : 'user_tmpl',
                              site_tmpl_title : ''
                        },
                        siteTmplData,
                        siteTmplDataCandidate,
                        site_tmpl_id, site_tmpl_source, site_tmpl_title;

                  // When a user template is being modified or removed, NB refreshes the site template input
                  input.container.one('site-tmpl-input-rendered', function() {
                        api.czr_sektions.allSavedTemplates.bind( function( userTmplates ) {
                              var raw_input = input();
                              siteTmplDataCandidate = $.extend( true, {}, _.isObject(raw_input) ? raw_input : {} );
                              if ( !_.isObject( siteTmplDataCandidate ) || _.isArray( siteTmplDataCandidate ) ) {
                                    siteTmplDataCandidate = $.extend( true, {}, _defaultData );
                              }
                              //siteTmplDataCandidate = $.extend( siteTmplDataCandidate, _defaultData );
                              site_tmpl_id = siteTmplDataCandidate.site_tmpl_id;
                              site_tmpl_source = siteTmplDataCandidate.site_tmpl_source;
                              site_tmpl_title = siteTmplDataCandidate.site_tmpl_title;

                              if ( _.isEmpty(site_tmpl_id) || !_.isString(site_tmpl_id) || !_.isObject(userTmplates) )
                                    return;
                              // Stop here if the template is not a user_tmpl. ( we don't need to reset title if _no_site_tmpl_ and api_tmpl are not editable )
                              if ( "user_tmpl" != site_tmpl_source )
                                    return;

                              if ( userTmplates[site_tmpl_id] ) {
                                    try{ printCurrentTemplateName(); } catch(er) { api.errare('Error when printing template val', er ); }
                              } else {
                                    // If the template has been removed, trigger a reset
                                    $hidInputEl.trigger('nb-set-site-tmpl', _defaultData );
                              }
                        });
                  });

                  // printParams : { see_me : true }
                  var printCurrentTemplateName = function( printParams ) {
                        printParams = $.extend( { see_me : false }, printParams || {} );

                        var _doRender = function( site_tmpl_id, site_tmpl_title ) {
                              _html = '<span class="sek-current-site-tmpl">';
                                    if ( '_no_site_tmpl_' === site_tmpl_id || _.isEmpty( site_tmpl_id ) ) {
                                          _html += sektionsLocalizedData.i18n['No template set.'];
                                          input.container.removeClass('sek-has-site-tmpl');
                                          input.container.removeClass('sek-site-tmpl-not-found');
                                    // Case of a user template not found. NOT POSSIBLE WHEN API TEMPLATE SOURCE
                                    } else if ( '_tmpl_not_found_' === site_tmpl_id || _.isEmpty( site_tmpl_id ) ) {
                                          _html += sektionsLocalizedData.i18n['Template not found : reset or pick another one.'];
                                          input.container.removeClass('sek-has-site-tmpl');
                                          input.container.addClass('sek-site-tmpl-not-found');
                                    } else {
                                          _html += sektionsLocalizedData.i18n['Active template : '] +  ( _.isEmpty(site_tmpl_title) ? site_tmpl_id : site_tmpl_title );
                                          input.container.addClass('sek-has-site-tmpl');
                                          input.container.removeClass('sek-site-tmpl-not-found');
                                    }
                              _html += '</span>';
                              input.container.find('.sek-current-site-tmpl').remove();
                              input.container.find('.czr-input').prepend(_html);

                              // Catch user's eye by animating the site template input
                              if ( printParams.see_me && '_no_site_tmpl_' != site_tmpl_id ) {
                                    input.container.addClass('button-see-me');
                                    _.delay( function() {
                                          input.container.removeClass('button-see-me');
                                    }, 800 );
                              }

                              input.container.trigger('site-tmpl-input-rendered');
                        };//_doRender


                        //{
                        //       site_tmpl_id : _tmpl_id,
                        //       site_tmpl_source : _tmpl_source,
                        //       site_tmpl_title : _tmpl_title
                        // }
                        var raw_input = input();
                        siteTmplDataCandidate = $.extend( true, {}, _.isObject(raw_input) ? raw_input : {} );
                        if ( !_.isObject( siteTmplDataCandidate ) || _.isArray( siteTmplDataCandidate ) ) {
                              siteTmplDataCandidate = $.extend( true, {}, _defaultData );
                        }
                        //siteTmplDataCandidate = $.extend( siteTmplDataCandidate, _defaultData );

                        site_tmpl_id = siteTmplDataCandidate.site_tmpl_id;
                        site_tmpl_source = siteTmplDataCandidate.site_tmpl_source;
                        site_tmpl_title = siteTmplDataCandidate.site_tmpl_title;

                        //site_tmpl_id = input();
                        if ( !_.isString(site_tmpl_id) || _.isEmpty(site_tmpl_id) ) {
                              api.errare('printCurrentTemplateName : Error => site template must be a string');
                              site_tmpl_id = '_no_site_tmpl_';
                        }
                        // Get the title
                        if ( '_no_site_tmpl_' === site_tmpl_id ) {
                              _doRender(siteTmplDataCandidate.site_tmpl_id, site_tmpl_title);
                        } else {
                              _tmpl_collection_promise = 'user_tmpl' === site_tmpl_source ? api.czr_sektions.setSavedTmplCollection : api.czr_sektions.getApiTmplCollection;
                              _tmpl_collection_promise.call(api.czr_sektions)
                              .done( function(tmpl_collection) {
                                    // if the tmpl_id is found in the collection, update the site_tmpl_title with its latest value
                                    if ( _.isObject(tmpl_collection) && tmpl_collection[site_tmpl_id] && tmpl_collection[site_tmpl_id].title ) {
                                          site_tmpl_title = tmpl_collection[site_tmpl_id].title;
                                    } else if ( 'user_tmpl' === site_tmpl_source ) {
                                          // If an api template is not found, NB doesn't print _tmpl_not_found_ associated message
                                          // => because it may happen that the api is unreachable or that an api template previously selected has been removed.
                                          //
                                          // For user template source, if tmpl id was not found in the current collection, it's been probably previously removed
                                          // so render as a '_tmpl_not_found_'
                                          api.errare('::printCurrentTemplateName => site template not found in collection => previously removed => id : ' + site_tmpl_id + ' | source : ' + site_tmpl_source  );
                                          site_tmpl_id = '_tmpl_not_found_';
                                    }
                                    _doRender(site_tmpl_id, site_tmpl_title);
                              })
                              .fail( function() {
                                    api.errare('printCurrentTemplateName error when getting collection promise failed', params );
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

                  // @args {
                  //       site_tmpl_id : _tmpl_id,
                  //       site_tmpl_source : _tmpl_source,
                  //       site_tmpl_title : _tmpl_title
                  // }
                  $hidInputEl.on('nb-set-site-tmpl', function( evt, args ) {
                        if ( !_.isObject(args) ) {
                              api.errare('site_tmpl_picker => error => wrong args on tmpl pick', args );
                              return;
                        }
                        if ( _.isUndefined(args.site_tmpl_id) || _.isUndefined(args.site_tmpl_source) || _.isUndefined(args.site_tmpl_title) ) {
                              api.errare('site_tmpl_picker => error => invalid args passed on tmpl pick', args );
                              return;
                        }
                        // _defaultData = { site_tmpl_id : '_no_site_tmpl_', site_tmpl_source : 'user_tmpl' }
                        siteTmplData = $.extend( true, {}, _defaultData );
                        siteTmplData = $.extend( siteTmplData, args );
                        input( siteTmplData );


                        try{ printCurrentTemplateName({ see_me : true }); } catch(er) { api.errare('Error when printing template val', er ); }

                        // Close gallery unless a reset has been triggered ( If a template has been removed )
                        if ( '_no_site_tmpl_' !== siteTmplData.site_tmpl_id ) { 
                              api.czr_sektions.templateGalleryExpanded( false );
                              $('[data-input-type="site_tmpl_picker"]').removeClass('sek-site-tmpl-picking-active');
                        }

                  });

                  try{ printCurrentTemplateName(); } catch(er) { api.errare('Error when printing template val', er ); }
            }
            
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );