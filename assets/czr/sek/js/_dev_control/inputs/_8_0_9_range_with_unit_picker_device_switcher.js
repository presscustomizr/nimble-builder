//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            range_with_unit_picker_device_switcher : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'range_with_unit_picker_device_switcher => error : invalid unit for input ' + input.id, unit );
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

                  var getInitialUnit = function() {
                        return $wrapper.find('input[data-czrtype]').data('sek-unit') || 'px';
                  };

                  // Recursive helper
                  // return the value set for the currently previewed device if exists
                  // OR
                  // return the inherited value from the first parent device for which the value is set
                  // OR
                  // falls back on the module default
                  var getCurrentDeviceActualOrInheritedValue = function( inputValues, currentDevice ) {
                        var deviceHierarchy = [ 'mobile' , 'tablet', 'desktop' ];
                        if ( _.has( inputValues, currentDevice ) ) {
                              return inputValues[ currentDevice ];
                        } else {
                              var deviceIndex = _.findIndex( deviceHierarchy, function( _d_ ) { return currentDevice === _d_; });
                              if ( deviceIndex < deviceHierarchy.length ) {
                                    return getCurrentDeviceActualOrInheritedValue( inputValues, deviceHierarchy[ deviceIndex + 1 ] );
                              } else {
                                    return '16px';
                              }
                        }
                  };

                  // Synchronizes on init + refresh on previewed device changes
                  var syncWithPreviewedDevice = function( currentDevice ) {
                        // initialize the number input with the current input val
                        // for retro-compatibility, we must handle the case when the initial input val is a string instead of an array
                        // in this case, the string value is assigned to the desktop device.
                        var inputVal = input(), inputValues = {}, clonedDefault = $.extend( true, {}, defaultVal );
                        if ( _.isObject( inputVal ) ) {
                              inputValues = $.extend( true, {}, inputVal );
                        } else if ( _.isString( inputVal ) ) {
                              inputValues = { desktop : inputVal };
                        }
                        inputValues = $.extend( clonedDefault, inputValues );

                        // do we have a val for the current device ?
                        var _rawVal = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice ),
                            _unit = _extractUnit( _rawVal ),
                            _numberVal = _extractNumericVal( _rawVal );

                        // update the unit
                        $('.sek-unit-wrapper', $wrapper).find('[data-sek-unit="' + _unit +'"]').trigger('click', { previewed_device_switched : true });// We don't want to update the input()
                        // add is-selected button on init to the relevant unit button
                        $wrapper.find( '.sek-ui-button[data-sek-unit="'+ _unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );

                        // update the numeric val
                        $numberInput.val(  _numberVal  ).trigger('input', { previewed_device_switched : true });// We don't want to update the input()
                  };



                  // SETUP
                  // setup the device switcher
                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );

                  // initialize the unit with the value provided in the dom
                  input.css_unit = new api.Value( _.isEmpty( getInitialUnit() ) ? 'px' : validateUnit( getInitialUnit() ) );

                  // Append a reset button
                  var resetButton = '<button type="button" class="button sek-reset-button sek-float-right">@missi18n Reset</button>';
                  input.container.find('.customize-control-title').append( resetButton );






                  // SCHEDULE REACTIONS
                  // React to a unit change => trigger a number input change
                  // Don't move when switching the device
                  // @param params can be { previewed_device_switched : true }
                  input.css_unit.bind( function( to, from, params ) {
                        if ( _.isObject( params ) && true === params.previewed_device_switched )
                          return;
                        $numberInput.trigger('input');
                  });

                  // synchronizes range input and number input
                  // number is the master => sets the input() val
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });
                  // Set the input val
                  $numberInput.on('input', function( evt, params ) {
                        var previewedDevice = api.previewedDevice() || 'desktop',
                            changedNumberInputVal = $(this).val() + validateUnit( input.css_unit() ),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ previewedDevice ] = $.extend( true, {}, _newInputVal[ previewedDevice ] || {} );

                        // Validates
                        if ( ( _.isString( changedNumberInputVal ) && ! _.isEmpty( changedNumberInputVal ) ) ) {
                              _newInputVal[ previewedDevice ]= changedNumberInputVal;
                        }

                        // update input if not previewed_device_switched
                        if ( _.isEmpty( params ) || ( _.isObject( params ) && true !== params.previewed_device_switched ) ) {
                              input( _newInputVal );
                        }
                        $rangeInput.val( $(this).val() );
                  });

                  // Schedule unit changes on button click
                  $wrapper.on( 'click', '.sek-ui-button', function( evt, params ) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        $wrapper.find('.sek-ui-button').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        // update the initial unit ( not mandatory)
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        // set the current unit Value
                        input.css_unit( $(this).data('sek-unit'), params );
                  });

                  // react to previewed device changes
                  // input.previewedDevice is updated in api.czr_sektions.maybeSetupDeviceSwitcherForInput()
                  input.previewedDevice.bind( function( currentDevice ) {
                        try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                              api.errare('Error when firing syncWithPreviewedDevice for input type range_with_unit_picker_device_switcher for input id ' + input.id , er );
                        }
                  });

                  // Schedule the reset of the value for the currently previewed device
                  input.container.on( 'click', '.sek-reset-button', function( evt ) {
                        var _currentDevice = api.previewedDevice(),
                            _newVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        if ( !_.isEmpty( _newVal[ _currentDevice ] ) ) {
                              _newVal = _.omit( _newVal, _currentDevice );
                              input( _newVal );
                              syncWithPreviewedDevice( api.previewedDevice() );
                        }
                  });


                  // INITIALIZES
                  // trigger a change on init to sync the range input
                  $rangeInput.val( $numberInput.val() || 0 );
                  try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type range_with_unit_picker_device_switcher for input id ' + input.id , er );
                  }
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );