//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // the input id determine if we fetch the revision history of the local or global setting
      $.extend( api.czrInputMap, {
            revision_history : function( params ) {
                  var input = this;
                  _selected_found = false;
                  //generates the options
                  var _generateOptions = function( revisionHistory ) {
                        if ( input.container.find('.sek-revision-history').length > 0 )
                          return;
                        if ( _.isEmpty( revisionHistory ) ) {
                              input.container.append( [ '<i>', sektionsLocalizedData.i18n['No revision history available for the moment.'], '</i>' ].join('') );
                              return;
                        }
                        input.container.append( $('<select/>', {
                              class : 'sek-revision-history',
                              html : [ '<option value="_select_">', ' -', sektionsLocalizedData.i18n['Select'], '- ', '</option>'].join('')
                        }));

                        // The revisions are listed by ascending date
                        // => let's reverse the order so that we see the latest first
                        var optionsNodes = [];
                        _.each( revisionHistory , function( _date, _post_id ) {
                              var _attributes = {
                                    value: _post_id,
                                    html: _date
                              };

                              if ( _attributes.value == input() ) {
                                    $.extend( _attributes, { selected : "selected" } );
                                    _selected_found = true;
                              }
                              optionsNodes.unshift( $('<option>', _attributes) );
                        });

                        // Add the 'published' note to the first node
                        optionsNodes[0].html( [ optionsNodes[0].html(), sektionsLocalizedData.i18n['(currently published version)'] ].join(' ') );
                        _.each( optionsNodes, function( nod ) {
                              $( 'select.sek-revision-history', input.container ).append( nod );
                        });

                        // Initialize selecter
                        $( 'select.sek-revision-history', input.container ).selecter();
                  };//_generateOptions


                  var _getRevisionHistory = function() {
                        return $.Deferred( function( _dfd_ ) {
                              if ( ! _.isEmpty( input.sek_revisionHistory ) ) {
                                    _dfd_.resolve( input.sek_revisionHistory );
                              } else {
                                    // The revision history sent by the server is an object
                                    // {
                                    //  post_id : date,
                                    //  post_id : date,
                                    //  ...
                                    // }
                                    api.czr_sektions.getRevisionHistory( { is_local : 'local_revisions' === input.id } ).done( function( revisionHistory ) {
                                          // Ensure we have a string that's JSON.parse-able
                                          if ( !_.isObject(revisionHistory) ) {
                                                throw new Error( '_getRevisionHistory => server list is not a object');
                                          }
                                          input.sek_revisionHistory = revisionHistory;
                                          _dfd_.resolve( input.sek_revisionHistory );
                                    }).fail( function( _r_ ) {
                                          _dfd_.reject( _r_ );
                                    });
                              }
                              //return dfd.promise();
                        });
                  };//_getRevisionHistory

                  // do
                  var _do_ = function( params ) {
                        if ( true === input.revisionHistorySet )
                          return;
                        $.when( _getRevisionHistory() ).done( function( revisionHistory ) {
                              _generateOptions( revisionHistory );
                              if ( params && true === params.open_on_init ) {
                                    // let's open select2 after a delay ( because there's no 'ready' event with select2 )
                                    _.delay( function() {
                                          try{ $( 'select[data-czrtype]', input.container ).czrSelect2('open'); }catch(er) {}
                                    }, 100 );
                              }
                        }).fail( function( _r_ ) {
                              api.errare( '_getRevisionHistory => fail response =>', _r_ );
                        });
                        input.revisionHistorySet = true;
                  };

                  // Generate options and open select2
                  input.container.on('change', '.sek-revision-history', function() {
                        var _val = $(this).val();
                        if ( '_select_' !== _val ) {
                              api.czr_sektions.setSingleRevision( { revision_post_id : _val, is_local : 'local_revisions' === input.id } );
                        }
                  });

                  // schedule the revisionHistorySet after a delay
                  _.delay( function() { _do_( { open_on_init : false } );}, 1000 );

            }//revision_history
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );