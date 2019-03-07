//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            revision_history : function( params ) {
                  var input = this;

                  _selected_found = false;
                  //generates the options
                  var _generateOptions = function( revisionHistory ) {
                        if ( input.container.find('.sek-revision-history').length > 0 )
                          return;
                        input.container.append( $('<select/>', { class : 'sek-revision-history', html : '<option value="_select_">@missi18n Select</option>' } ) );

                        _.each( revisionHistory , function( _date, _post_id ) {
                              var _attributes = {
                                    value: _post_id,
                                    //iconClass is in the form "fa(s|b|r) fa-{$name}" so the name starts at position 7
                                    html: _date //api.CZR_Helpers.capitalize( iconClass.substring( 7 ) )
                              };

                              if ( _attributes.value == input() ) {
                                    $.extend( _attributes, { selected : "selected" } );
                                    _selected_found = true;
                              }
                              $( 'select.sek-revision-history', input.container ).append( $('<option>', _attributes) );
                        });

                        //Initialize selecter
                        $( 'select.sek-revision-history', input.container ).selecter();
                  };//_generateOptions


                  var _getRevisionHistory = function() {
                        return $.Deferred( function( _dfd_ ) {
                              if ( ! _.isEmpty( input.sek_revisionHistory ) ) {
                                    _dfd_.resolve( input.sek_revisionHistory );
                              } else {
                                    api.czr_sektions.getLocalRevisionList().done( function( revisionHistory ) {
                                          // Ensure we have a string that's JSON.parse-able
                                          if ( !_.isObject(revisionHistory) ) {
                                                throw new Error( 'fa_icon_picker => server list is not JSON.parse-able');
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
                              api.errare( 'fa_icon_picker => fail response =>', _r_ );
                        });
                        input.revisionHistorySet = true;
                  };

                  // Generate options and open select2
                  input.container.on('change', '.sek-revision-history', function() {
                        var _val = $(this).val();
                        if ( '_select_' !== _val ) {
                              api.czr_sektions.setSingleRevision( _val );
                        }
                  });

                  // schedule the revisionHistorySet after a delay
                  _.delay( function() { _do_( { open_on_init : false } );}, 1000 );

            }//revision_history
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );