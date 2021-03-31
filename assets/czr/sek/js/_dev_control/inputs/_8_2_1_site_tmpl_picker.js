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
                        _defaultVal = { site_tmpl_id : '_no_site_tmpl_', site_tmpl_source : 'user_tmpl', site_tmpl_title : '@missi18n No Template'},
                        inputVal,
                        newInputVal,
                        tmplTitle;

                  var printCurrentTemplateVal = function() {
                        console.log('PRINT CURRENT TEMPLATE VAL', _defaultVal );
                        inputVal = input();
                        inputVal = _.isObject(inputVal) ? inputVal : _defaultVal;
                        newInputVal = $.extend( true, {}, _defaultVal );
                        newInputVal = $.extend( _defaultVal, inputVal );

                        var _doRender = function( tmplId, tmplTitle ) {
                              console.log('tmplTitle ???', tmplTitle);
                              _html = '<span class="sek-current-site-tmpl">';
                              if ( '_no_site_tmpl_' === tmplId || _.isEmpty( tmplId ) ) {
                                    _html += '@missi18n => No template';
                              } else {
                                    _html += '@missi18n => Current Template : ' +  ( _.isEmpty(tmplTitle) ? tmplId : tmplTitle );
                              }
                              _html += '</span>';
                              input.container.find('.sek-current-site-tmpl').remove();
                              input.container.find('.czr-input').prepend(_html);
                        };

                        // Get the title
                        _tmpl_collection_promise = 'user_tmpl' === inputVal.site_tmpl_source ? api.czr_sektions.setSavedTmplCollection : api.czr_sektions.getApiTmplCollection;
                        _tmpl_collection_promise.call(api.czr_sektions)
                              .done( function(tmpl_collection) {
                                    if ( '_no_site_tmpl_' != inputVal.site_tmpl_id ) {
                                          if ( _.isObject(tmpl_collection) && tmpl_collection[inputVal.site_tmpl_id] && tmpl_collection[inputVal.site_tmpl_id].title ) {
                                                tmplTitle = tmpl_collection[inputVal.site_tmpl_id].title;
                                          }
                                    }
                                    _doRender(inputVal.site_tmpl_id, tmplTitle);
                                    console.log('alors COLLECTION? ', tmpl_collection );
                              })
                              .fail( function() {
                                    console.log('printCurrentTemplateVal error when getting collection promise failed', params );
                                    _dfd_.resolve('');
                              });

                        // default val = '_no_site_tmpl_'
                        
                  };

                  // Schedule events
                  input.container.on( 'click', '[data-sek-group-scope]', function( evt, args ) {
                        evt.stopPropagation();
                        var scope = $(this).data( 'sek-group-scope' );

                        if ( _.isEmpty( scope ) ) {
                              api.errare( 'site_tmpl_picker input => invalid scope provided.', scope );
                              return;
                        }
                        
                        api.czr_sektions._site_tmpl_scope = input.id;
                        api.czr_sektions.templateGalleryExpanded( !api.czr_sektions.templateGalleryExpanded() );
                  
                  });//on('click')
                  input.container.on( 'click', '.sek-remove-site-tmpl', function( evt, args ) {
                        evt.stopPropagation();
                        $hidInputEl.trigger('nb-set-site-tmpl', _defaultVal );
                  });//on('click')

                  
                  
                  $hidInputEl.on('nb-set-site-tmpl', function( evt, args ) {
                        if ( !_.isObject(args) ) {
                              api.errare('site_tmpl_picker => error => wrong args on tmpl pick', args );
                              return;
                        }
                        newInputVal = $.extend( true, {}, _defaultVal );
                        newInputVal = $.extend( newInputVal, args );

                        input( newInputVal );
                        try{ printCurrentTemplateVal(); } catch(er) { api.errare('Error when printing template val', er ); }
                  });

                  try{ printCurrentTemplateVal(); } catch(er) { api.errare('Error when printing template val', er ); }
            }
            
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );