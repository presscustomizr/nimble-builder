//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            borders : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-borders', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]'),
                      $colorInput = $wrapper.find('.sek-alpha-color-input'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'borders => error : invalid unit for input ' + input.id, unit );
                                  unit = 'px';
                            }
                            return unit;
                      },
                      // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
                      _extractNumericVal = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? '16' : _rawVal.replace(/px|em|%/g,'');
                      },
                      _extractUnit = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? 'px' : _rawVal.replace(/[0-9]|\.|,/g, '');
                      },
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  input.cssBorderTypes = [ 'top', 'left', 'right', 'bottom' ];

                  // Return the unit of the _all_ border type
                  var getInitialUnit = function() {
                        var inputVal = input(), initial_unit = 'px';
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) && _.isObject( inputVal['_all_'] ) && ! _.isEmpty( inputVal['_all_'][ 'wght'] ) ) {
                              initial_unit = validateUnit( _extractUnit( inputVal['_all_'][ 'wght'] ) );
                        }
                        return initial_unit;
                  };
                  // Return the number value of the _all_ border type
                  var getInitialWeight = function() {
                        var inputVal = input(), initial_weight = 1;
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) && _.isObject( inputVal['_all_'] ) && ! _.isEmpty( inputVal['_all_'][ 'wght'] ) ) {
                              initial_weight = _extractNumericVal( inputVal['_all_'][ 'wght'] );
                        }
                        initial_weight = parseInt(initial_weight, 10);
                        if ( ! _.isNumber( initial_weight ) || initial_weight < 0 ) {
                              api.errare( 'Error in borders input type for module : ' + input.module.module_type + ' the initial border width is invalid : ' + initial_weight );
                              initial_weight = 1;
                        }
                        return initial_weight;
                  };
                  // Return the color of the _all_ border type
                  var getInitialColor = function() {
                        var inputVal = input(), initial_color = '#000000';
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) && _.isObject( inputVal['_all_'] ) && ! _.isEmpty( inputVal['_all_'][ 'col'] ) ) {
                              initial_color = inputVal['_all_'][ 'col'];
                        }
                        return initial_color;
                  };

                  // Recursive helper
                  // _all_ : { wght : 1px, col : #000000 }
                  // falls back on {}
                  var getCurrentBorderTypeOrAllValue = function( inputValues, borderType ) {
                        var clonedDefaults = $.extend( true, {}, defaultVal ), _all_Value;
                        if ( ! _.has( clonedDefaults, '_all_' ) ) {
                            throw new Error( "Error when firing getCurrentBorderTypeOrAllValue : the default value of the borders input must be php registered as an array formed : array( 'wght' => '1px', 'col' => '#000000' )");
                        }

                        _all_Value =  ( _.isObject( inputValues ) && _.has( inputValues, '_all_' ) ) ? _.extend( clonedDefaults['_all_'], inputValues[ '_all_' ] ) : clonedDefaults['_all_'];
                        if ( _.has( inputValues, borderType ) && _.isObject( inputValues[ borderType ] ) ) {
                              return _.extend( _all_Value, inputValues[ borderType ] );
                        } else {
                              return clonedDefaults['_all_'];
                        }
                  };

                  // Synchronizes on init + refresh on border type change
                  var syncWithBorderType = function( borderType ) {
                        if ( ! _.contains( _.union( input.cssBorderTypes, [ '_all_' ] ) , borderType ) ) {
                              throw new Error( "Error in syncWithBorderType : the border type must be one of those values '_all_', 'top', 'left', 'right', 'bottom'" );
                        }

                        // initialize the number input with the current input val
                        // for retro-compatibility, we must handle the case when the initial input val is a string instead of an array
                        // in this case, the string value is assigned to the desktop device.
                        var inputVal = input(), inputValues = {}, clonedDefault = $.extend( true, {}, defaultVal );
                        if ( _.isObject( inputVal ) ) {
                              inputValues = $.extend( true, {}, inputVal );
                        } else if ( _.isString( inputVal ) ) {
                              inputValues = { _all_ : { wght : inputVal } };
                        }
                        inputValues = $.extend( clonedDefault, inputValues );

                        // do we have a val for the current border type ?
                        var _rawVal = getCurrentBorderTypeOrAllValue( inputValues, borderType ), _unit, _numberVal;
                        if ( _.isEmpty( _rawVal ) || ! _.isObject( _rawVal ) || _.isEmpty( _rawVal.wght ) || _.isEmpty( _rawVal.col ) ) {
                              throw new Error( "Error in syncWithBorderType : getCurrentBorderTypeOrAllValue must return an object formed : array( 'wght' => '1px', 'col' => '#000000' )");
                        }

                        _unit = _extractUnit( _rawVal.wght );
                        _numberVal = _extractNumericVal( _rawVal.wght );

                        // update the unit
                        $('.sek-unit-wrapper', $wrapper).find('[data-sek-unit="' + _unit +'"]').trigger('click', { border_type_switched : true });// We don't want to update the input()
                        // add is-selected button on init to the relevant unit button
                        $wrapper.find( '.sek-ui-button[data-sek-unit="'+ _unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
                        // update the numeric val
                        $numberInput.val( _numberVal ).trigger('input', { border_type_switched : true });// We don't want to update the input()
                        // update the color
                        // trigger the change between "border_type_switched" data flags, so we know the api setting don't have to be refreshed
                        // ( there's no easy other way to pass a param when triggering )
                        $colorInput.data('border_type_switched', true );
                        $colorInput.val( _rawVal.col ).trigger( 'change' );
                        $colorInput.data('border_type_switched', false );
                  };





                  // SETUP
                  input.borderColor = new api.Value( _.isEmpty( getInitialColor() ) ? '#000000' : getInitialColor() );
                  // initialize the unit
                  input.css_unit = new api.Value( _.isEmpty( getInitialUnit() ) ? 'px' : validateUnit( getInitialUnit() ) );
                  // setup the border type switcher. Initialized with all.
                  input.borderType = new api.Value( '_all_');
                  // Setup the initial state of the number input
                  $numberInput.val( getInitialWeight() );
                  // Setup the color input
                  $colorInput.val( input.borderColor() );
                  $colorInput.wpColorPicker({
                        palettes: true,
                        //hide:false,
                        width: window.innerWidth >= 1440 ? 271 : 251,
                        change : function( evt, o ) {
                              //if the input val is not updated here, it's not detected right away.
                              //weird
                              //is there a "change complete" kind of event for iris ?
                              //$(this).val($(this).wpColorPicker('color'));
                              //input.container.find('[data-czrtype]').trigger('colorpickerchange');

                              //synchronizes with the original input
                              //OLD => $(this).val( $(this).wpColorPicker('color') ).trigger('colorpickerchange').trigger('change');
                              $(this).val( o.color.toString() ).trigger('colorpickerchange');
                              input.borderColor( o.color.toString(), { border_type_switched : true === $(this).data('border_type_switched') } );
                              //input.borderColor( o.color.toString() );
                              // if ( evt.originalEvent && evt.originalEvent.type && 'external' === evt.originalEvent.type ) {
                              //       input.borderColor( o.color.toString(), { border_type_switched : true } );
                              // } else {
                              //       input.borderColor( o.color.toString() );
                              // }
                        },
                        clear : function( e, o ) {
                              $(this).val('').trigger('colorpickerchange');
                              input.borderColor('');
                        }
                  });






                  // SCHEDULE REACTIONS
                  // React to a unit change => trigger a number input change
                  // Don't move when switching the border type or initializing unit
                  // @param params can be { border_type_switched : true }
                  input.css_unit.bind( function( to, from, params ) {
                        // don't update the main input when switching border types or initializing the unit value
                        if ( _.isObject( params ) && ( true === params.border_type_switched || true === params.initializing_the_unit ) )
                          return;
                        $numberInput.trigger('input', params);
                  });

                  // React to a color change => trigger a number input change
                  // Don't move when switching the border type or initializing the color
                  // @param params can be { border_type_switched : true }
                  input.borderColor.bind( function( to, from, params ) {
                        // don't update the main input when switching border types or initializing the unit value
                        if ( _.isObject( params ) && ( true === params.border_type_switched || true === params.initializing_the_color ) )
                          return;
                        $numberInput.trigger('input', params);
                  });

                  // react to border type changes
                  input.borderType.bind( function( borderType ) {
                        try { syncWithBorderType( borderType ); } catch( er ) {
                              api.errare('Error when firing syncWithBorderType for input type borders for module type ' + input.module.module_type , er );
                        }
                  });

                  // synchronizes range input and number input
                  // number is the master => sets the input() val
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });

                  // Set the input val
                  $numberInput.on('input', function( evt, params ) {
                        var currentBorderType = input.borderType() || '_all_',
                            currentColor = input.borderColor(),
                            changedNumberInputVal = $(this).val() + validateUnit( input.css_unit() ),
                            clonedDefaults = $.extend( true, {}, defaultVal ),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : clonedDefaults );
                        _newInputVal[ currentBorderType ] = $.extend( true, {}, _newInputVal[ currentBorderType ] || clonedDefaults[ currentBorderType ] );

                        // populate the border weight value
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) ) {
                              _newInputVal[ currentBorderType ][ 'wght' ] = changedNumberInputVal;
                        }
                        // populate the color value
                        _newInputVal[ currentBorderType ][ 'col' ] = currentColor;

                        // update input if not border_type_switched
                        // if _all_ is changed, removed all other types
                        if ( _.isEmpty( params ) || ( _.isObject( params ) && true !== params.border_type_switched ) ) {
                              if ( '_all_' === currentBorderType ) {
                                    _.each( input.cssBorderTypes, function( _type ) {
                                          _newInputVal = _.omit( _newInputVal, _type );
                                    });
                              }
                              input( _newInputVal );
                        }
                        // refresh the range slider
                        $rangeInput.val( $(this).val() );
                  });


                  // Schedule unit changes on button click
                  $wrapper.on( 'click', '[data-sek-unit]', function( evt, params ) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        $wrapper.find('[data-sek-unit]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        // update the initial unit ( not mandatory)
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        // set the current unit Value
                        input.css_unit( $(this).data('sek-unit'), params );
                  });

                  // Schedule border type changes on button click
                  $wrapper.on( 'click', '[data-sek-border-type]', function( evt, params ) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        $wrapper.find('[data-sek-border-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        var border = '_all_';
                        try { border = $(this).data('sek-border-type'); } catch( er ) {
                              api.errare( 'borders input type => error when attaching click event', er );
                        }
                        input.borderType( border, params );
                  });

                  // Schedule the reset of the value for the currently previewed device
                  input.container.on( 'click', '.sek-reset-button', function( evt ) {
                        var currentBorderType = input.borderType() || '_all_',
                            _newVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        if ( !_.isEmpty( _newVal[ currentBorderType ] ) ) {
                              _newVal = _.omit( _newVal, currentBorderType );
                              input( _newVal );
                              syncWithBorderType( currentBorderType );
                        }
                  });








                  // INITIALIZES
                  // trigger a change on init to sync the range input
                  $rangeInput.val( $numberInput.val() || 0 );
                  try { syncWithBorderType( input.borderType() ); } catch( er ) {
                        api.errare('Error when firing syncWithBorderType for input type borders for module type ' + input.module.module_type , er );
                  }
                  // trigger a click on the initial unit
                  // => the initial unit could be set when fetching the server template but it's more convenient to handle it once the template is rendered
                  $( '[data-sek-unit="' + input.css_unit() + '"]', $wrapper ).trigger('click', { initializing_the_unit : true } );
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );