//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            // FONT PICKER
            font_picker : function( input_options ) {
                  var input = this,
                      item = input.input_parent;

                  var _getFontCollections = function() {
                        var dfd = $.Deferred();
                        if ( ! _.isEmpty( api.sek_fontCollections ) ) {
                              dfd.resolve( api.sek_fontCollections );
                        } else {
                              // This utility handles a cached version of the font_list once fetched the first time
                              // @see api.CZR_Helpers.czr_cachedTmpl
                              api.CZR_Helpers.getModuleTmpl( {
                                    tmpl : 'font_list',
                                    module_type: 'font_picker_input',
                                    module_id : input.module.id
                              } ).done( function( _serverTmpl_ ) {
                                    // Ensure we have a string that's JSON.parse-able
                                    if ( typeof _serverTmpl_ !== 'string' || _serverTmpl_[0] !== '{' ) {
                                          throw new Error( 'font_picker => server list is not JSON.parse-able');
                                    }
                                    api.sek_fontCollections = JSON.parse( _serverTmpl_ );
                                    dfd.resolve( api.sek_fontCollections );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });
                        }
                        return dfd.promise();
                  };
                  var _preprocessSelect2ForFontFamily = function() {
                        /*
                        * Override select2 Results Adapter in order to select on highlight
                        * deferred needed cause the selects needs to be instantiated when this override is complete
                        * selec2.amd.require is asynchronous
                        */
                        var selectFocusResults = $.Deferred();
                        if ( 'undefined' !== typeof $.fn.select2 && 'undefined' !== typeof $.fn.select2.amd && 'function' === typeof $.fn.select2.amd.require ) {
                              $.fn.select2.amd.require(['select2/results', 'select2/utils'], function (Result, Utils) {
                                    var ResultsAdapter = function($element, options, dataAdapter) {
                                      ResultsAdapter.__super__.constructor.call(this, $element, options, dataAdapter);
                                    };
                                    Utils.Extend(ResultsAdapter, Result);
                                    ResultsAdapter.prototype.bind = function (container, $container) {
                                      var _self = this;
                                      container.on('results:focus', function (params) {
                                        if ( params.element.attr('aria-selected') != 'true') {
                                          _self.trigger('select', {
                                              data: params.data
                                          });
                                        }
                                      });
                                      ResultsAdapter.__super__.bind.call(this, container, $container);
                                    };
                                    selectFocusResults.resolve( ResultsAdapter );
                              });
                        }
                        else {
                              selectFocusResults.resolve( false );
                        }

                        return selectFocusResults.promise();

                  };//_preprocessSelect2ForFontFamily

                  // @return void();
                  // Instantiates a select2 select input
                  // http://ivaynberg.github.io/select2/#documentation
                  var _setupSelectForFontFamilySelector = function( customResultsAdapter, fontCollections ) {
                        var _model = item(),
                            _googleFontsFilteredBySubset = function() {
                                  var subset = item.czr_Input('subset')(),
                                      filtered = _.filter( fontCollections.gfonts, function( data ) {
                                            return data.subsets && _.contains( data.subsets, subset );
                                      });

                                  if ( ! _.isUndefined( subset ) && ! _.isNull( subset ) && 'all-subsets' != subset ) {
                                        return filtered;
                                  } else {
                                        return fontCollections.gfonts;
                                  }

                            },
                            $fontSelectElement = $( 'select[data-czrtype="' + input.id + '"]', input.container );

                        // generates the options
                        // @param type = cfont or gfont
                        var _generateFontOptions = function( fontList, type ) {
                              var _html_ = '';
                              _.each( fontList , function( font_data ) {
                                    var _value = font_data.name,
                                        optionTitle = _.isString( _value ) ? _value.replace(/[+|:]/g, ' ' ) : _value,
                                        _setFontTypePrefix = function( val, type ) {
                                              return _.isString( val ) ? [ '[', type, ']', val ].join('') : '';//<= Example : [gfont]Aclonica:regular
                                        };

                                    _value = _setFontTypePrefix( _value, type );

                                    if ( _value == input() ) {
                                          _html_ += '<option selected="selected" value="' + _value + '">' + optionTitle + '</option>';
                                    } else {
                                          _html_ += '<option value="' + _value + '">' + optionTitle + '</option>';
                                    }
                              });
                              return _html_;
                        };

                        //add the first option
                        if ( _.isNull( input() ) || _.isEmpty( input() ) ) {
                              $fontSelectElement.append( '<option value="none" selected="selected">' + sektionsLocalizedData.i18n['Select a font family'] + '</option>' );
                        } else {
                              $fontSelectElement.append( '<option value="none">' + sektionsLocalizedData.i18n['Select a font family'] + '</option>' );
                        }


                        // generate the cfont and gfont html
                        _.each( [
                              {
                                    title : sektionsLocalizedData.i18n['Web Safe Fonts'],
                                    type : 'cfont',
                                    list : fontCollections.cfonts
                              },
                              {
                                    title : sektionsLocalizedData.i18n['Google Fonts'],
                                    type : 'gfont',
                                    list : fontCollections.gfonts//_googleFontsFilteredBySubset()
                              }
                        ], function( fontData ) {
                              var $optGroup = $('<optgroup>', { label : fontData.title , html : _generateFontOptions( fontData.list, fontData.type ) });
                              $fontSelectElement.append( $optGroup );
                        });

                        var _fonts_select2_params = {
                                //minimumResultsForSearch: -1, //no search box needed
                            //templateResult: paintFontOptionElement,
                            //templateSelection: paintFontOptionElement,
                            escapeMarkup: function(m) { return m; },
                        };
                        /*
                        * Maybe use custom adapter
                        */
                        if ( customResultsAdapter ) {
                              $.extend( _fonts_select2_params, {
                                    resultsAdapter: customResultsAdapter,
                                    closeOnSelect: false,
                              } );
                        }

                        //http://ivaynberg.github.io/select2/#documentation
                        //FONTS
                        $fontSelectElement.select2( _fonts_select2_params );
                        $( '.select2-selection__rendered', input.container ).css( getInlineFontStyle( input() ) );

                  };//_setupSelectForFontFamilySelector

                  // @return {} used to set $.css()
                  // @param font {string}.
                  // Example : Aclonica:regular
                  // Example : Helvetica Neue, Helvetica, Arial, sans-serif
                  var getInlineFontStyle = function( _fontFamily_ ){
                        // the font is set to 'none' when "Select a font family" option is picked
                        if ( ! _.isString( _fontFamily_ ) || _.isEmpty( _fontFamily_ ) )
                          return {};

                        //always make sure we remove the prefix.
                        _fontFamily_ = _fontFamily_.replace('[gfont]', '').replace('[cfont]', '');

                        var module = this,
                            split = _fontFamily_.split(':'), font_family, font_weight, font_style;

                        font_family       = getFontFamilyName( _fontFamily_ );

                        font_weight       = split[1] ? split[1].replace( /[^0-9.]+/g , '') : 400; //removes all characters
                        font_weight       = _.isNumber( font_weight ) ? font_weight : 400;
                        font_style        = ( split[1] && -1 != split[1].indexOf('italic') ) ? 'italic' : '';


                        return {
                              'font-family' : 'none' == font_family ? 'inherit' : font_family.replace(/[+|:]/g, ' '),//removes special characters
                              'font-weight' : font_weight || 400,
                              'font-style'  : font_style || 'normal'
                        };
                  };

                  // @return the font family name only from a pre Google formated
                  // Example : input is Inknut+Antiqua:regular
                  // Should return Inknut Antiqua
                  var getFontFamilyName = function( rawFontFamily ) {
                        if ( ! _.isString( rawFontFamily ) || _.isEmpty( rawFontFamily ) )
                            return rawFontFamily;

                        rawFontFamily = rawFontFamily.replace('[gfont]', '').replace('[cfont]', '');
                        var split         = rawFontFamily.split(':');
                        return _.isString( split[0] ) ? split[0].replace(/[+|:]/g, ' ') : '';//replaces special characters ( + ) by space
                  };

                  $.when( _getFontCollections() ).done( function( fontCollections ) {
                        _preprocessSelect2ForFontFamily().done( function( customResultsAdapter ) {
                              _setupSelectForFontFamilySelector( customResultsAdapter, fontCollections );
                        });
                  }).fail( function( _r_ ) {
                        api.errare( 'font_picker => fail response =>', _r_ );
                  });
            }//font_picker()
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );