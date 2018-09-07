//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};


      // HELPERS USED IN ALL SPACING INPUT TYPES
      // "this" is input
      var validateUnit = function( unit ) {
            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                  api.errare( 'error : invalid unit for input ' + this.id, unit );
                  unit = 'px';
            }
            return unit;
          },
          stripUnit = function( value ) {
                return _.isString( value ) ? value.replace(/px|em|%/g,'') : '';
          },
          unitButtonsSetup = function( $wrapper ) {
                var input = this;
                // Schedule unit changes on button click
                $wrapper.on( 'click', '.sek-ui-button', function(evt) {
                      evt.preventDefault();
                      // handle the is-selected css class toggling
                      $wrapper.find('.sek-ui-button').removeClass('is-selected').attr( 'aria-pressed', false );
                      $(this).addClass('is-selected').attr( 'aria-pressed', true );
                      // set the current unit Value
                      input.css_unit( $(this).data('sek-unit') );
                });

                // add is-selected button on init to the relevant unit button
                $wrapper.find( '.sek-ui-button[data-sek-unit="'+ ( input.initial_unit || 'px' ) +'"]').addClass('is-selected').attr( 'aria-pressed', true );
          },
          setupResetAction = function( $wrapper, defaultVal ) {
                var input = this;
                $wrapper.on( 'click', '.reset-spacing-wrap', function(evt) {
                      evt.preventDefault();
                      $wrapper.find('input[type="number"]').each( function() {
                            $(this).val('');
                      });

                      input( defaultVal );
                      // Reset unit to pixels
                      $('.sek-unit-wrapper', $wrapper ).find('[data-sek-unit="px"]').trigger('click');
                });
          };



      /* ------------------------------------------------------------------------- *
       *  SPACING CLASSIC
      /* ------------------------------------------------------------------------- */
      $.extend( api.czrInputMap, {
            spacing : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-spacing-wrapper', input.container ),
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : [];

                  // Listen to user actions on the inputs and set the input value
                  $wrapper.on( 'input', 'input[type="number"]', function(evt) {
                        var _type_ = $(this).closest('[data-sek-spacing]').data('sek-spacing'),
                            _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                            _rawVal = $(this).val();

                        // Validates
                        // @fixes https://github.com/presscustomizr/nimble-builder/issues/26
                        if ( ( _.isString( _rawVal ) && ! _.isEmpty( _rawVal ) ) || _.isNumber( _rawVal ) ) {
                              _newInputVal[ _type_ ] = _rawVal;
                        } else {
                              // this allow users to reset a given padding / margin instead of reseting them all at once with the "reset all spacing" option
                              _newInputVal = _.omit( _newInputVal, _type_ );
                        }
                        input( _newInputVal );
                  });
                  // Schedule a reset action
                  setupResetAction.call( input, $wrapper, defaultVal );

                  // Synchronize on init
                  if ( _.isObject( input() ) ) {
                        _.each( input(), function( _val_, _key_ ) {
                              $( '[data-sek-spacing="' + _key_ +'"]', $wrapper ).find( 'input[type="number"]' ).val( _val_ );
                        });
                        // loop on the unit buttons and check which one should be clicked
                        var unitToActivate = 'px';
                        $('.sek-unit-wrapper .sek-ui-button', input.container ).each( function() {
                              var unit = $(this).data('sek-unit');
                              // do we have a unit for the current device ?
                              if ( ! _.isEmpty( input() ) ) {
                                    if ( ! _.isEmpty( input()[ 'unit' ] ) ) {
                                          if ( unit === input()[ 'unit' ] ) {
                                                unitToActivate = unit;
                                          }
                                    }
                              }
                        });
                        $('.sek-unit-wrapper', input.container ).find('[data-sek-unit="' + validateUnit.call( input, unitToActivate ) + '"]').trigger('click');
                  }

                  // Set the initial unit
                  var initial_value = input();
                  input.initial_unit = 'px';
                  if ( ! _.isEmpty( initial_value )  ) {
                        input.initial_unit = _.isEmpty( initial_value['unit'] ) ? 'px' : initial_value['unit'];
                  }

                  // initialize the unit with the value provided in the dom
                  input.css_unit = new api.Value( validateUnit.call( input, input.initial_unit ) );

                  // React to a unit change
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        var _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ 'unit' ] = to;
                        input( _newInputVal );
                  });

                  // Schedule unit changes on button click
                  // add is-selected button on init to the relevant unit button
                  unitButtonsSetup.call( input, $wrapper );
            }
      });//$.extend( api.czrInputMap, {})















      /* ------------------------------------------------------------------------- *
       *  SPACING WITH DEVICE SWITCHER
      /* ------------------------------------------------------------------------- */
      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            spacingWithDeviceSwitcher : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-spacing-wrapper', input.container ),
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );

                  // Listen to user actions on the inputs and set the input value
                  $wrapper.on( 'input', 'input[type="number"]', function(evt) {
                        var changedSpacingType    = $(this).closest('[data-sek-spacing]').data('sek-spacing'),
                            changedNumberInputVal = $(this).val(),
                            _newInputVal,
                            previewedDevice = api.previewedDevice() || 'desktop';

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ previewedDevice ] = $.extend( true, {}, _newInputVal[ previewedDevice ] || {} );
                        // Validates
                        // @fixes https://github.com/presscustomizr/nimble-builder/issues/26
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) || _.isNumber( changedNumberInputVal ) ) {
                              _newInputVal[ previewedDevice ][ changedSpacingType ] = changedNumberInputVal;
                        } else {
                              // this allow users to reset a given padding / margin instead of reseting them all at once with the "reset all spacing" option
                              _newInputVal[ previewedDevice ] = _.omit( _newInputVal[ previewedDevice ], changedSpacingType );
                        }

                        input( _newInputVal );
                  });

                  // Schedule a reset action
                  setupResetAction.call( input, $wrapper, defaultVal );

                  // Synchronizes on init + refresh on previewed device changes
                  var syncWithPreviewedDevice = function( currentDevice ) {
                        var inputValues = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                            clonedDefault = $.extend( true, {}, defaultVal );
                        inputValues = _.isObject( inputValues ) ? $.extend( clonedDefault, inputValues ) : clonedDefault;

                        // loop on each sek spacing and check if we find a value to write for this device
                        $( '[data-sek-spacing]', $wrapper ).each( function() {
                              var spacingType = $(this).data('sek-spacing'),
                                  _val_ = '';
                              // do we have a val for the current device ?
                              if ( ! _.isEmpty( inputValues[ currentDevice ] ) ) {
                                    if ( ! _.isEmpty( inputValues[ currentDevice ][ spacingType ] ) ) {
                                          _val_ = inputValues[ currentDevice ][ spacingType ];
                                    }
                              }
                              $(this).find( 'input[type="number"]' ).val( _val_ );
                        });

                        // loop on the unit button and check which one should be clicked
                        var unitToActivate = 'px';
                        $( '.sek-unit-wrapper .sek-ui-button', input.container).each( function() {
                              var unit = $(this).data('sek-unit');
                              // do we have a unit for the current device ?
                              if ( ! _.isEmpty( inputValues[ currentDevice ] ) ) {
                                    if ( ! _.isEmpty( inputValues[ currentDevice ][ 'unit' ] ) ) {
                                          if ( unit === inputValues[ currentDevice ][ 'unit' ] ) {
                                                unitToActivate = unit;
                                          }
                                    }
                              }
                        });
                        $('.sek-unit-wrapper', input.container ).find('[data-sek-unit="' + validateUnit.call( input, unitToActivate ) + '"]').trigger('click');
                  };

                  syncWithPreviewedDevice( api.previewedDevice() );

                  // react to previewed device changes
                  // input.previewedDevice is updated in api.czr_sektions.maybeSetupDeviceSwitcherForInput()
                  input.previewedDevice.bind( syncWithPreviewedDevice );

                  // Set the initial unit
                  var initial_value = input();
                  input.initial_unit = 'px';
                  if ( ! _.isEmpty( initial_value ) && ! _.isEmpty( initial_value[ input.previewedDevice() ] ) ) {
                        input.initial_unit = _.isEmpty( initial_value[ input.previewedDevice() ]['unit'] ) ? 'px' : initial_value[ input.previewedDevice() ]['unit'];
                  }

                  // initialize the unit with the value provided in the dom
                  input.css_unit = new api.Value( validateUnit.call( input, input.initial_unit ) );

                  // React to a unit change
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        var _newInputVal,
                            previewedDevice = input.previewedDevice() || 'desktop';

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ previewedDevice ] = $.extend( true, {}, _newInputVal[ previewedDevice ] || {} );
                        _newInputVal[ previewedDevice ][ 'unit' ] = to;
                        input( _newInputVal );
                  });

                  // Schedule unit changes on button click
                  // add is-selected button on init to the relevant unit button
                  unitButtonsSetup.call( input, $wrapper );
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );