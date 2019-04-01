//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            range_simple_device_switcher : function( params ) {
                  var input = this,
                      $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                      $numberInput = $wrapper.find( 'input[type="number"]'),
                      $rangeInput = $wrapper.find( 'input[type="range"]'),
                      // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
                      _extractNumericVal = function( _rawVal ) {
                            return ( _.isEmpty( _rawVal ) || ! _.isString( _rawVal ) ) ? '16' : _rawVal.replace(/px|em|%/g,'');
                      },
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

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
                              if ( ! _.isEmpty( currentDevice ) && deviceIndex < deviceHierarchy.length ) {
                                    return getCurrentDeviceActualOrInheritedValue( inputValues, deviceHierarchy[ deviceIndex + 1 ] );
                              } else {
                                    var clonedDefault = $.extend( true, { desktop : '' }, defaultVal );
                                    return clonedDefault[ 'desktop' ];
                              }
                        }
                  };

                  // Synchronizes on init + refresh on previewed device changes
                  var syncWithPreviewedDevice = function( currentDevice ) {
                        // initialize the number input with the current input val
                        // for retro-compatibility, we must handle the case when the initial input val is a string instead of an array
                        // in this case, the string value is assigned to the desktop device.
                        var inputVal = input(), inputValues = {}, clonedDefault = $.extend( true, {}, defaultVal );
                        inputValues = clonedDefault;
                        if ( _.isObject( inputVal ) ) {
                              inputValues = $.extend( true, {}, inputVal );
                        } else if ( _.isString( inputVal ) && ! _.isEmpty( inputVal ) ) {
                              inputValues = { desktop : inputVal };
                        }
                        //inputValues = _.extend( inputValues, clonedDefault );
                        // do we have a val for the current device ?
                        var _rawVal = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice ),
                            _numberVal = _extractNumericVal( _rawVal );

                        // update the numeric val
                        $numberInput.val(  _numberVal  ).trigger('input', { previewed_device_switched : true });// We don't want to update the input()
                  };

                  // SETUP
                  // setup the device switcher
                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );

                  // Append a reset button
                  // var resetButton = '<button type="button" class="button sek-reset-button sek-float-right">' + sektionsLocalizedData.i18n['Reset'] + '</button>';
                  // input.container.find('.customize-control-title').append( resetButton );

                  // SCHEDULE REACTIONS
                  // synchronizes range input and number input
                  // number is the master => sets the input() val
                  $rangeInput.on('input', function( evt ) {
                        $numberInput.val( $(this).val() ).trigger('input');
                  });

                  // Set the input val
                  $numberInput.on('input', function( evt, params ) {
                        var previewedDevice = api.previewedDevice() || 'desktop',
                            changedNumberInputVal = $(this).val(),
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

                  // react to previewed device changes
                  // input.previewedDevice is updated in api.czr_sektions.maybeSetupDeviceSwitcherForInput()
                  input.previewedDevice.bind( function( currentDevice ) {
                        try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                              api.errare('Error when firing syncWithPreviewedDevice for input type ' + input.type + ' for input id ' + input.id , er );
                        }
                  });

                  // // Schedule the reset of the value for the currently previewed device
                  // input.container.on( 'click', '.sek-reset-button', function( evt ) {
                  //       var _currentDevice = api.previewedDevice(),
                  //           _newVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                  //       if ( !_.isEmpty( _newVal[ _currentDevice ] ) ) {
                  //             _newVal = _.omit( _newVal, _currentDevice );
                  //             input( _newVal );
                  //             syncWithPreviewedDevice( api.previewedDevice() );
                  //       }
                  // });

                  // trigger a change on init to sync the range input
                  $rangeInput.val( $numberInput.val() || 0 );
                  try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type ' + input.type + ' for input id ' + input.id , er );
                  }
            },

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );