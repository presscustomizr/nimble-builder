//global sektionsLocalizedData
( function ( api, $, _ ) {

      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            simpleselect : function( selectOptions ) {
                  api.czr_sektions.setupSelectInput.call( this, selectOptions );
            },
            multiselect : function( selectOptions ) {
                  api.czr_sektions.setupSelectInput.call( this, selectOptions );
            },

            simpleselectWithDeviceSwitcher : function( selectOptions ) {
                  var input  = this,
                      item   = input.input_parent,
                      module = input.module,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      $select = $( 'select', input.container ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  // use the provided selectOptions if any
                  selectOptions = _.isEmpty( selectOptions ) ? inputRegistrationParams.choices : selectOptions;

                  // allow selectOptions to be filtrable remotely when the options are not passed on registration for example
                  // @see widget are module in initialize() for example
                  var filtrable = { params : selectOptions };
                  input.module.trigger( 'nimble-set-select-input-options', filtrable );
                  selectOptions = filtrable.params;

                  if ( _.isEmpty( selectOptions ) || ! _.isObject( selectOptions ) ) {
                        api.errare( 'api.czr_sektions.setupSelectInput => missing select options for input id => ' + input.id + ' in module ' + input.module.module_type );
                        return;
                  }

                  //generates the options
                  _.each( selectOptions , function( title, value ) {
                        var _attributes = {
                                  value : value,
                                  html: title
                            };
                        if ( value == input() ) {
                              $.extend( _attributes, { selected : "selected" } );
                        } else if ( 'px' === value ) {
                              $.extend( _attributes, { selected : "selected" } );
                        }
                        $select.append( $('<option>', _attributes) );
                  });

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
                        var _rawVal = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice );

                        // update the select val
                        //$select.selecter('destroy');
                        $select.val(  _rawVal  ).trigger('change', { previewed_device_switched : true });// We don't want to update the input()
                  };

                  // SETUP
                  // setup the device switcher
                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );

                  // Set the input val
                  $select.on('change', function( evt, params ) {
                        var previewedDevice = api.previewedDevice() || 'desktop',
                            changedSelectVal = $(this).val(),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ previewedDevice ] = $.extend( true, {}, _newInputVal[ previewedDevice ] || {} );

                        // Validates
                        if ( ( _.isString( changedSelectVal ) && ! _.isEmpty( changedSelectVal ) ) ) {
                              _newInputVal[ previewedDevice ]= changedSelectVal;
                        }

                        // update input if not previewed_device_switched
                        if ( _.isEmpty( params ) || ( _.isObject( params ) && true !== params.previewed_device_switched ) ) {
                              input( _newInputVal );
                        }
                  });

                  // react to previewed device changes
                  // input.previewedDevice is updated in api.czr_sektions.maybeSetupDeviceSwitcherForInput()
                  input.previewedDevice.bind( function( currentDevice ) {
                        try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                              api.errare('Error when firing syncWithPreviewedDevice for input type ' + input.type + ' for input id ' + input.id , er );
                        }
                  });

                  //$select.selecter();
            }

      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );