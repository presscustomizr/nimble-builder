//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};
      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            // FONT AWESOME ICON PICKER
            // FONT AWESOME ICON PICKER
            fa_icon_picker : function() {
                  var input           = this,
                      item            = input.input_parent,
                      _selected_found = false;

                  //generates the options
                  var _generateOptions = function( iconCollection ) {
                        _.each( iconCollection , function( iconClass ) {
                              var _attributes = {
                                    value: iconClass,
                                    //iconClass is in the form "fa(s|b|r) fa-{$name}" so the name starts at position 7
                                    html: api.CZR_Helpers.capitalize( iconClass.substring( 7 ) )
                              };

                              if ( _attributes.value == item().icon ) {
                                    $.extend( _attributes, { selected : "selected" } );
                                    _selected_found = true;
                              }
                              $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                        });


                        var addIcon = function ( state ) {
                              if (! state.id) { return state.text; }

                              //two spans here because we cannot wrap the text into the icon span as the solid FA5 font-weight is bold
                              var  $state = $(
                                '<span class="' + state.element.value + '"></span><span class="social-name">&nbsp;&nbsp;' + state.text + '</span>'
                              );
                              return $state;
                        };

                        //blank option to allow placeholders
                        var $_placeholder;
                        if ( _selected_found ) {
                              $_placeholder = $('<option>');
                        } else {
                              $_placeholder = $('<option>', { selected: 'selected' } );
                        }
                        //Initialize select2
                        $( 'select[data-czrtype]', input.container )
                          .prepend( $_placeholder )
                          .select2({
                                templateResult: addIcon,
                                templateSelection: addIcon,
                                placeholder: sektionsLocalizedData.i18n['Select an icon'],
                                allowClear: true
                        });
                  };//_generateOptions

                  var _getIconsCollections = function() {
                        var dfd = $.Deferred();
                        if ( ! _.isEmpty( input.sek_faIconCollection ) ) {
                              dfd.resolve( input.sek_faIconCollection );
                        } else {
                              // This utility handles a cached version of the font_list once fetched the first time
                              // @see api.CZR_Helpers.czr_cachedTmpl
                              api.CZR_Helpers.getModuleTmpl( {
                                    tmpl : 'icon_list',
                                    module_type: 'fa_icon_picker_input',
                                    module_id : input.module.id
                              } ).done( function( _serverTmpl_ ) {
                                    // Ensure we have a string that's JSON.parse-able
                                    if ( typeof _serverTmpl_ !== 'string' || _serverTmpl_[0] !== '[' ) {
                                          throw new Error( 'fa_icon_picker => server list is not JSON.parse-able');
                                    }
                                    input.sek_faIconCollection = JSON.parse( _serverTmpl_ );
                                    dfd.resolve( input.sek_faIconCollection );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });
                        }
                        return dfd.promise();
                  };//_getIconsCollections

                  // do
                  $.when( _getIconsCollections() ).done( function( iconCollection ) {
                        _generateOptions( iconCollection );
                  }).fail( function( _r_ ) {
                        api.errare( 'fa_icon_picker => fail response =>', _r_ );
                  });

            }
      });//$.extend( api.czrInputMap, {})

})( wp.customize, jQuery, _ );