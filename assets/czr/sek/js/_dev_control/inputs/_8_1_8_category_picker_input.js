//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            category_picker : function( params ) {
                  var selectOptions,
                      input = this,
                      $selectEl = $( 'select[data-czrtype]', input.container );

                  var getInputValue = function() {
                        var inputValue = input();
                        // when select is multiple, the value is an array
                        inputValue = _.isString( inputValue ) ? [ inputValue ] : inputValue;
                        return !_.isArray( inputValue ) ? [] : inputValue;
                  };


                  var _getCategoryCollection = function() {
                        return $.Deferred( function( _dfd_ ) {
                              if ( ! _.isEmpty( api.czr_sektions.post_categories ) ) {
                                    _dfd_.resolve( api.czr_sektions.post_categories );
                              } else {
                                    wp.ajax.post( 'sek_get_post_categories', {
                                          nonce: api.settings.nonce.save,
                                    }).done( function( raw_cat_collection ) {
                                          if ( !_.isArray( raw_cat_collection ) ) {
                                                api.errare( input.id + ' => error => invalid category collection sent by server');
                                          }
                                          var catCollection = {};
                                          // server sends
                                          // [
                                          //  0: {id: 2, slug:'my-category', name: "My category"}
                                          //  1: {id: 11, slug:'my-category', name: "cat10"}
                                          //  ...
                                          // ]
                                          _.each( raw_cat_collection, function( cat_data ) {
                                                if ( _.isEmpty( cat_data.slug ) || _.isEmpty( cat_data.name ) ) {
                                                      _dfd_.reject( 'missing slug or name for at least one category' );
                                                } else {
                                                      catCollection[ cat_data.slug ] = cat_data.name;
                                                }

                                          });
                                          api.czr_sektions.post_categories = catCollection;
                                          _dfd_.resolve( api.czr_sektions.post_categories );
                                    }).fail( function( _r_ ) {
                                          _dfd_.reject( _r_ );
                                    });
                              }
                        });
                  };

                  // do
                  var _fetchServerCatsAndInstantiateSelect2 = function( params ) {
                        if ( true === input.catCollectionSet )
                          return;
                        $.when( _getCategoryCollection() ).done( function( _catCollection ) {
                              _generateOptionsAndInstantiateSelect2(_catCollection);
                              if ( params && true === params.open_on_init ) {
                                    // let's open select2 after a delay ( because there's no 'ready' event with select2 )
                                    _.delay( function() {
                                          try{ $selectEl.czrSelect2('open'); } catch(er) {}
                                    }, 100 );
                              }
                        }).fail( function( _r_ ) {
                              api.errare( input.id + ' => fail response when _getCategoryCollection()', _r_ );
                        });
                        input.catCollectionSet = true;
                  };

                  var _generateOptionsAndInstantiateSelect2 = function( selectOptions ) {
                        //generates the options
                        _.each( selectOptions , function( title, value ) {
                              var _attributes = {
                                        value : value,
                                        html: title
                                  };
                              if ( _.contains( getInputValue(), value ) ) {
                                    $.extend( _attributes, { selected : "selected" } );
                              }
                              $selectEl.append( $('<option>', _attributes) );
                        });
                        // see how the tmpl is rendered server side in PHP with ::ac_set_input_tmpl_content()
                        $selectEl.czrSelect2({
                              closeOnSelect: true,
                              templateSelection: function czrEscapeMarkup(obj) {
                                    //trim dashes
                                    return obj.text.replace(/\u2013|\u2014/g, "");
                              }
                        });

                        //handle case when all choices become unselected
                        $selectEl.on('change', function(){
                              if ( 0 === $(this).find("option:selected").length ) {
                                    input([]);
                              }
                        });
                  };// _generateOptionsAnd...()
                  // schedule the catCollectionSet after a delay
                  //_.delay( function() { _fetchServerCatsAndInstantiateSelect2( { open_on_init : false } );}, 1000 );

                  // on init, instantiate select2 with the input() values only
                  var selectOptionsOnInit = {};
                  _.each( getInputValue(), function( _val ) {
                        selectOptionsOnInit[ _val ] = ( _val + '' ).replace( /-/g, ' ');
                  });
                  _generateOptionsAndInstantiateSelect2( selectOptionsOnInit );

                  // re-generate select2 on click with the server collection
                  input.container.on('click', function() {
                        if ( true === input.catCollectionSet )
                          return;
                        // destroy the temporary instance
                        $selectEl.czrSelect2('destroy');
                        // destroy the temporary options
                        $.when( $selectEl.find('option').remove() ).done( function() {
                              _fetchServerCatsAndInstantiateSelect2( { open_on_init : true } );
                        });
                  });

            }//category_picker()
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );