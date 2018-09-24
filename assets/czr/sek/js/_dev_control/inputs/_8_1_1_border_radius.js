//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            border_radius : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-borders', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'border_radius => error : invalid unit for input ' + input.id, unit );
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

                  input.cssRadiusTypes = [ 'top_left','top_right','bottom_right','bottom_left' ];

                  // Return the unit of the _all_ border type
                  var getInitialUnit = function() {
                        var inputVal = input(), initial_unit = 'px';
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) ) {
                              initial_unit = validateUnit( _extractUnit( inputVal['_all_'] ) );
                        }
                        return initial_unit;
                  };
                  // Return the number value of the _all_ border type
                  var getInitialRadius = function() {
                        var inputVal = input(), initial_rad = 0;
                        if ( _.isObject( inputVal ) && _.has( inputVal, '_all_' ) ) {
                              initial_rad = _extractNumericVal( inputVal['_all_'] );
                        }
                        initial_rad = parseInt(initial_rad, 10);
                        if ( ! _.isNumber( initial_rad ) || initial_rad < 0 ) {
                              api.errare( 'Error in border_radius input type for module : ' + input.module.module_type + ' the initial radius is invalid : ' + initial_rad );
                              initial_rad = 0;
                        }
                        return initial_rad;
                  };


                  // Recursive helper
                  // _all_ : 3px
                  // falls back on {}
                  var getCurrentRadiusTypeOrAllValue = function( inputValues, radiusType ) {
                        var clonedDefaults = $.extend( true, {}, defaultVal ), _all_Value;
                        if ( ! _.has( clonedDefaults, '_all_' ) ) {
                            throw new Error( "Error when firing getCurrentRadiusTypeOrAllValue : the default value of the border_radius input must be php registered as an array");
                        }

                        _all_Value =  ( _.isObject( inputValues ) && _.has( inputValues, '_all_' ) ) ? inputValues[ '_all_' ] : clonedDefaults['_all_'];
                        if ( _.has( inputValues, radiusType ) ) {
                              return inputValues[ radiusType ];
                        } else {
                              return _all_Value;
                        }
                  };

                  // Synchronizes on init + refresh on border radius change
                  var syncWithRadiusType = function( radiusType ) {
                        if ( ! _.contains( [ '_all_', 'top_left', 'top_right', 'bottom_right', 'bottom_left' ], radiusType ) ) {
                              throw new Error( "Error in syncWithRadiusType : the radius type must be one of those values '_all_', 'top_left', 'top_right', 'bottom_right', 'bottom_left', => radius type => " + radiusType );
                        }

                        // initialize the number input with the current input val
                        // for retro-compatibility, we must handle the case when the initial input val is a string instead of an array
                        // in this case, the string value is assigned to the desktop device.
                        var inputVal = input(), inputValues = {}, clonedDefault = $.extend( true, {}, defaultVal );
                        if ( _.isObject( inputVal ) ) {
                              inputValues = $.extend( true, {}, inputVal );
                        } else if ( _.isString( inputVal ) ) {
                              inputValues = { _all_ : '0px' };
                        }
                        inputValues = $.extend( clonedDefault, inputValues );

                        // do we have a val for the current type ?
                        var _rawVal = getCurrentRadiusTypeOrAllValue( inputValues, radiusType ), _unit, _numberVal;
                        if ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) {
                              throw new Error( "Error in syncWithRadiusType : getCurrentRadiusTypeOrAllValue must return a string like 3em");
                        }

                        _unit = _extractUnit( _rawVal );
                        _numberVal = _extractNumericVal( _rawVal );

                        // update the unit
                        $('.sek-unit-wrapper', $wrapper).find('[data-sek-unit="' + _unit +'"]').trigger('click', { radius_type_switched : true });// We don't want to update the input()
                        // add is-selected button on init to the relevant unit button
                        $wrapper.find( '.sek-ui-button[data-sek-unit="'+ _unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
                        // update the numeric val
                        $numberInput.val( _numberVal ).trigger('input', { radius_type_switched : true });// We don't want to update the input()
                  };







                  // SETUP
                  // initialize the unit
                  input.css_unit = new api.Value( _.isEmpty( getInitialUnit() ) ? 'px' : validateUnit( getInitialUnit() ) );
                  // setup the border type switcher. Initialized with all.
                  input.radiusType = new api.Value('_all_');
                  // Setup the initial state of the number input
                  $numberInput.val( getInitialRadius() );








                  // SCHEDULE REACTIONS
                  // React to a unit change => trigger a number input change
                  // Don't move when switching the border type or initializing unit
                  // @param params can be { radius_type_switched : true }
                  input.css_unit.bind( function( to, from, params ) {
                        // don't update the main input when switching border types or initializing the unit value
                        if ( _.isObject( params ) && ( true === params.radius_type_switched || true === params.initializing_the_unit ) )
                          return;
                        $numberInput.trigger('input', params);
                  });

                  // react to border type changes
                  input.radiusType.bind( function( radiusType ) {
                        try { syncWithRadiusType( radiusType ); } catch( er ) {
                              api.errare('Error when firing syncWithRadiusType for input type border_radius for module type ' + input.module.module_type , er );
                        }
                  });

                  // synchronizes range input and number input
                  // number is the master => sets the input() val
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });

                  // Set the input val
                  $numberInput.on('input', function( evt, params ) {
                        var currentRadiusType = input.radiusType() || '_all_',
                            changedNumberInputVal = $(this).val() + validateUnit( input.css_unit() ),
                            clonedDefaults = $.extend( true, {}, defaultVal ),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : clonedDefaults );
                        _newInputVal[ currentRadiusType ] = $.extend( true, {}, _newInputVal[ currentRadiusType ] || clonedDefaults[ currentRadiusType ] );

                        // populate the border weight value
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) ) {
                              _newInputVal[ currentRadiusType ] = changedNumberInputVal;
                        }
                        // update input if not radius_type_switched
                        // if _all_ is changed, removed all other types
                        if ( _.isEmpty( params ) || ( _.isObject( params ) && true !== params.radius_type_switched ) ) {
                              if ( '_all_' === currentRadiusType ) {
                                    _.each( input.cssRadiusTypes, function( _type ) {
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
                  $wrapper.on( 'click', '[data-sek-radius-type]', function( evt, params ) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        $wrapper.find('[data-sek-radius-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        var border = '_all_';
                        try { border = $(this).data('sek-radius-type'); } catch( er ) {
                              api.errare( 'border_radius input type => error when attaching click event', er );
                        }
                        input.radiusType( border, params );
                  });

                  // Schedule the reset of the value for the currently previewed device
                  input.container.on( 'click', '.sek-reset-button', function( evt ) {
                        var currentRadiusType = input.radiusType() || '_all_',
                            _newVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        if ( !_.isEmpty( _newVal[ currentRadiusType ] ) ) {
                              _newVal = _.omit( _newVal, currentRadiusType );
                              input( _newVal );
                              syncWithRadiusType( currentRadiusType );
                        }
                  });








                  // INITIALIZES
                  // trigger a change on init to sync the range input
                  $rangeInput.val( $numberInput.val() || 0 );
                  try { syncWithRadiusType( input.radiusType() ); } catch( er ) {
                        api.errare('Error when firing syncWithRadiusType for input type border_radius for module type ' + input.module.module_type , er );
                  }
                  // trigger a click on the initial unit
                  // => the initial unit could be set when fetching the server template but it's more convenient to handle it once the template is rendered
                  $( '[data-sek-unit="' + input.css_unit() + '"]', $wrapper ).trigger('click', { initializing_the_unit : true } );
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );