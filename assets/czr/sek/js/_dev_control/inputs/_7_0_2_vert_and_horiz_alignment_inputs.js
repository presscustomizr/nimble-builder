//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // Generic method to instantiate the following input types :
      // horizTextAlignmentWithDeviceSwitcher and horizAlignmentWithDeviceSwitcher => tmpl => 3_0_5_sek_input_tmpl_horizontal_alignment.php
      // verticalAlignWithDeviceSwitcher => tmpl => 3_0_6_sek_input_tmpl_vertical_alignment.php
      var x_or_y_AlignWithDeviceSwitcher = function( params ) {
            var input = this,
                inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                defaultVal = ( ! _.isEmpty( inputRegistrationParams ) && ! _.isEmpty( inputRegistrationParams.default ) ) ? inputRegistrationParams.default : {},
                tmplSelector = 'verticalAlignWithDeviceSwitcher' === input.type ? '.sek-v-align-wrapper' : '.sek-h-align-wrapper',// <= because used by 2 different input tmpl
                $wrapper = $( tmplSelector, input.container );

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

                  //input.container.find('input[value="'+ _currentDeviceValue +'"]').attr('checked', true).trigger('click', { previewed_device_switched : true } );
                  $wrapper.find('.selected').removeClass('selected');
                  $wrapper.find( 'div[data-sek-align="' + _currentDeviceValue +'"]' ).addClass('selected');
            };

            // on click
            $wrapper.on( 'click', '[data-sek-align]', function(evt) {
                  evt.preventDefault();
                  var _newInputVal;

                  _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                  _newInputVal[ api.previewedDevice() || 'desktop' ] = $(this).data('sek-align');

                  $wrapper.find('.selected').removeClass('selected');
                  $.when( $(this).addClass('selected') ).done( function() {
                        input( _newInputVal );
                  });
            });

            // react to previewed device changes
            // input.previewedDevice is updated in api.czr_sektions.maybeSetupDeviceSwitcherForInput()
            input.previewedDevice.bind( function( currentDevice ) {
                  try { syncWithPreviewedDevice( currentDevice ); } catch( er ) {
                        api.errare('Error when firing syncWithPreviewedDevice for input type : ' + input.type + ' for input id ' + input.id , er );
                  }
            });

            // INITIALIZES
            try { syncWithPreviewedDevice( api.previewedDevice() ); } catch( er ) {
                  api.errare('Error when firing syncWithPreviewedDevice for input type : ' + input.type + ' for input id ' + input.id , er );
            }
      };


      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            horizTextAlignmentWithDeviceSwitcher : x_or_y_AlignWithDeviceSwitcher,
            horizAlignmentWithDeviceSwitcher : x_or_y_AlignWithDeviceSwitcher,
            verticalAlignWithDeviceSwitcher : x_or_y_AlignWithDeviceSwitcher
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );