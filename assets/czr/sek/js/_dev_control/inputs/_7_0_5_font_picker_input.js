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
                      item = input.input_parent,
                      $fontSelectElement = $( 'select[data-czrtype="' + input.id + '"]', input.container );

                  var _getFontCollections = function() {
                        var dfd = $.Deferred();
                        if ( ! _.isEmpty( api.sek_fontCollections ) ) {
                              dfd.resolve( api.sek_fontCollections );
                        } else {
                              var _ajaxRequest_;
                              if ( ! _.isUndefined( api.sek_fetchingFontCollection ) && 'pending' == api.sek_fetchingFontCollection.state() ) {
                                    _ajaxRequest_ = api.sek_fetchingFontCollection;
                              } else {
                                    // This utility handles a cached version of the font_list once fetched the first time
                                    // @see api.CZR_Helpers.czr_cachedTmpl
                                    _ajaxRequest_ = api.CZR_Helpers.getModuleTmpl( {
                                          tmpl : 'font_list',
                                          module_type: 'font_picker_input',
                                          module_id : input.module.id
                                    } );
                                    api.sek_fetchingFontCollection = _ajaxRequest_;
                              }
                              _ajaxRequest_.done( function( _serverTmpl_ ) {
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
                        * Override czrSelect2 Results Adapter in order to select on highlight
                        * deferred needed cause the selects needs to be instantiated when this override is complete
                        * selec2.amd.require is asynchronous
                        */
                        var selectFocusResults = $.Deferred();
                        if ( 'undefined' !== typeof $.fn.czrSelect2 && 'undefined' !== typeof $.fn.czrSelect2.amd && 'function' === typeof $.fn.czrSelect2.amd.require ) {
                              $.fn.czrSelect2.amd.require(['czrSelect2/results', 'czrSelect2/utils'], function (Result, Utils) {
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
                  // Instantiates a czrSelect2 select input
                  // http://ivaynberg.github.io/czrSelect2/#documentation
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

                            };

                        // generates the options
                        // @param type = cfont or gfont
                        var _generateFontOptions = function( fontList, type ) {
                              var _html_ = '';
                              _.each( fontList , function( font_data ) {
                                    var _value = _.isString( font_data.name ) ? font_data.name  : 'Undefined Font Family',
                                        optionTitle = _value.replace(/[+|:]/g, ' ' ),
                                        _maybeSetFontTypePrefix = function( val, type ) {
                                              if ( _.isEmpty( type ) )
                                                return val;
                                              return _.isString( val ) ? [ '[', type, ']', val ].join('') : '';//<= Example : [gfont]Aclonica:regular
                                        };

                                    _value = _maybeSetFontTypePrefix( _value, type );
                                    optionTitle = optionTitle.replace('[cfont]', '').replace('[gfont]', '');
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

                        // declare the font list collection : most used, cfont, gfont
                        var _fontCollection = [
                              {
                                    title : sektionsLocalizedData.i18n['Web safe fonts'],
                                    type : 'cfont',
                                    list : fontCollections.cfonts
                              },
                              {
                                    title : sektionsLocalizedData.i18n['Google fonts'],
                                    type : 'gfont',
                                    list : fontCollections.gfonts//_googleFontsFilteredBySubset()
                              }
                        ];

                        // Server fonts are stored as an array of gfonts with duplicates no removed
                        // 0: "[gfont]Raleway:800"
                        // 1: "[gfont]Roboto:regular"
                        // 2: "[gfont]Montserrat:regular"
                        // 3: "[gfont]Exo+2:800italic"
                        // 4: "[gfont]Raleway:800"
                        // 5: "[gfont]Roboto:regular"


                        //
                        // SERVER FONTS is a merge of the uniq gfont array of all skopes. Because each skopes font are stored in the .fonts property of a section setting, each time a new font is used in the customizer.
                        // The resulting SERVER FONTS array can have duplicatesd google fonts, if two skopes use the same font for example.
                        //
                        // How do we increase the weight of locally used gfont for the currently customized skope ?
                        // => AllFontsInApi is a raw list of all fonts, web safe and google fonts, with duplicates not removed
                        // those fonts are the one of the current skope + global sections fonts + global options fonts
                        // Server and api fonts are merged
                        // Since duplicates are not removed from api fonts, a frequently used local font can be quickly positionned on top of the list.
                        var allFontsInApi = api.czr_sektions.sniffAllFonts();
                        var allServerSentFonts = sektionsLocalizedData.alreadyUsedFonts;

                        var _alreadyUsedFonts = [],
                            _allFonts = [];

                        if ( ! _.isEmpty( allServerSentFonts ) && _.isObject( allServerSentFonts ) ) {
                              _.each( allServerSentFonts, function( _font ){
                                    _allFonts.push( _font );
                              });
                        }

                        if ( _.isArray( allFontsInApi ) ) {
                              _.each( allFontsInApi, function( _font ) {
                                    _allFonts.push( _font );
                              });
                        }

                        if ( !_.isEmpty( _allFonts ) ) {
                              // order fonts by number of occurences
                              var _occurencesMap = {},
                                  _fontsOrderedByOccurences = [];
                              // Creates the occurence map
                              _allFonts.forEach(function(i) { _occurencesMap[i] = (_occurencesMap[i]||0) + 1;});

                              // isolate only the occurence number in an array
                              var _occurences =  _.sortBy(_occurencesMap, function(num){ return num; });

                              _.each( _occurences, function( nb ) {
                                    _.each( _occurencesMap, function( nbOccurence, fontName ) {
                                          if ( nb === nbOccurence && !_.contains( _fontsOrderedByOccurences, fontName ) ) {
                                                // unshift because the occurencesMap is in ascending order, and we want the most used fonts at the beginning
                                                _fontsOrderedByOccurences.unshift( fontName );
                                          }
                                    });
                              });

                              // normalizes the most used font collection, like other font collection [{name:'font1'}, {...}, ... ]
                              _.each( _fontsOrderedByOccurences, function( fontName ){
                                    _alreadyUsedFonts.push({name : fontName });
                              });
                              _fontCollection.unshift( {
                                    title : sektionsLocalizedData.i18n['Already used fonts'],
                                    type : null,//already set for Most used fonts
                                    list : _alreadyUsedFonts
                              });
                        }//if ( !_.isEmpty( _allFonts ) )


                        // generate the cfont and gfont html
                        _.each( _fontCollection, function( fontData ) {
                              var $optGroup = $('<optgroup>', { label : fontData.title , html : _generateFontOptions( fontData.list, fontData.type ) });
                              $fontSelectElement.append( $optGroup );
                        });

                        var _fonts_czrSelect2_params = {
                                //minimumResultsForSearch: -1, //no search box needed
                            //templateResult: paintFontOptionElement,
                            //templateSelection: paintFontOptionElement,
                            escapeMarkup: function(m) { return m; },
                        };
                        /*
                        * Maybe use custom adapter
                        */
                        if ( customResultsAdapter ) {
                              $.extend( _fonts_czrSelect2_params, {
                                    resultsAdapter: customResultsAdapter,
                                    closeOnSelect: false,
                              } );
                        }

                        //http://ivaynberg.github.io/czrSelect2/#documentation
                        //FONTS
                        $fontSelectElement.czrSelect2( _fonts_czrSelect2_params );
                        $( '.czrSelect2-selection__rendered', input.container ).css( getInlineFontStyle( input() ) );

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

                  // On load, simply print the current input value
                  // the full list of font ( several thousands !! ) will be rendered on click
                  // March 2021 => to avoid slowing down the UI, the font picker select options are cleaned in cleanRegisteredAndLargeSelectInput()
                  var inputVal = input();
                  $fontSelectElement.append( $('<option>', {
                        value : inputVal,
                        html: inputVal,
                        selected : "selected"
                  }));
                  
                  // Generate options and open select2
                  input.container.on('click', function() {
                        if ( true === $fontSelectElement.data('selectOptionsSet') )
                          return;
                        
                        $fontSelectElement.data('selectOptionsSet', true );
                        // reset previous default html
                        $fontSelectElement.html('');
                        
                        $.when( _getFontCollections() ).done( function( fontCollections ) {
                              _preprocessSelect2ForFontFamily().done( function( customResultsAdapter ) {
                                    _setupSelectForFontFamilySelector( customResultsAdapter, fontCollections );
                                    if ( !_.isUndefined( input.container.find('select[data-czrtype]').data('czrSelect2') ) ) {
                                          input.container.find('select[data-czrtype]').czrSelect2('open');
                                    }
                              });
                        }).fail( function( _r_ ) {
                              api.errare( 'font_picker => fail response =>', _r_ );
                        });
                   });
            }//font_picker()
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );