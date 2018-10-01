//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            /* ------------------------------------------------------------------------- *
             *  BG POSITION SIMPLE
            /* ------------------------------------------------------------------------- */
            bg_position : function( input_options ) {
                  var input = this;
                  // Listen to user actions on the inputs and set the input value
                  $('.sek-bg-pos-wrapper', input.container ).on( 'change', 'input[type="radio"]', function(evt) {
                        input( $(this).val() );
                  });

                  // Synchronize on init
                  if ( ! _.isEmpty( input() ) ) {
                        input.container.find('input[value="'+ input() +'"]').attr('checked', true).trigger('click');
                  }
            },


            /* ------------------------------------------------------------------------- *
             *  BG POSITION WITH DEVICE SWITCHER
            /* ------------------------------------------------------------------------- */
            bgPositionWithDeviceSwitcher : function( input_options ) {
                  var input = this,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {};

                  // SETUP
                  api.czr_sektions.maybeSetupDeviceSwitcherForInput.call( input );

                  var getCurrentDeviceActualOrInheritedValue = function( inputValues, currentDevice ) {
                        var deviceHierarchy = [ 'mobile' , 'tablet', 'desktop' ];
                        if ( _.has( inputValues, currentDevice ) ) {
                              return inputValues[ currentDevice ];
                        } else {
                              var deviceIndex = _.findIndex( deviceHierarchy, function( _d_ ) { return currentDevice === _d_; });
                              if ( ! _.isEmpty( currentDevice ) && deviceIndex < deviceHierarchy.length ) {
                                    return getCurrentDeviceActualOrInheritedValue( inputValues, deviceHierarchy[ deviceIndex + 1 ] );
                              } else {
                                    return {};
                              }
                        }
                  };

                  // Synchronizes on init + refresh on previewed device changes
                  var syncWithPreviewedDevice = function( currentDevice ) {
                        var inputValues = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                            clonedDefault = $.extend( true, {}, defaultVal );
                        inputValues = _.isObject( inputValues ) ? $.extend( clonedDefault, inputValues ) : clonedDefault;
                        var _currentDeviceValue = getCurrentDeviceActualOrInheritedValue( inputValues, currentDevice );

                        input.container.find('input[value="'+ _currentDeviceValue +'"]').attr('checked', true).trigger('click', { previewed_device_switched : true } );
                  };



                  // Listen to user actions on the inputs and set the input value
                  $('.sek-bg-pos-wrapper', input.container ).on( 'change', 'input[type="radio"]', function( evt ) {
                        var changedRadioVal = $(this).val(),
                            _newInputVal;

                        _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ api.previewedDevice() || 'desktop' ] = changedRadioVal;

                        input( _newInputVal );
                  });


                  // react to previewed device changes
                  // input.previewedDevice is updated in api.czr_sektions.maybeSetupDeviceSwitcherForInput()
                  input.previewedDevice.bind( function( currentDevice ) {
                        try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                              api.errare('Error when firing syncWithPreviewedDevice for input type spacingWithDeviceSwitcher for input id ' + input.id , er );
                        }
                  });

                  // INITIALIZES
                  try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type bgPositionWithDeviceSwitcher for input id ' + input.id , er );
                  }
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );