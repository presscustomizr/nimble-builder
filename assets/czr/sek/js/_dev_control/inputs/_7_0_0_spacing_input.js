//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            spacing : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-spacing-wrapper', input.container );
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
                              _newInputVal = _.omit( _type_, _newInputVal );
                        }

                        input( _newInputVal );
                  });
                  // Schedule a reset action
                  // Note : this has to be done by device
                  $wrapper.on( 'click', '.reset-spacing-wrap', function(evt) {
                        evt.preventDefault();
                        $wrapper.find('input[type="number"]').each( function() {
                              $(this).val('');
                        });
                        // [] is the default value
                        // we could have get it with api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_spacing_module' )
                        // @see php spacing module registration
                        input( [] );
                  });

                  // Synchronize on init
                  if ( _.isObject( input() ) ) {
                        _.each( input(), function( _val_, _key_ ) {
                              $( '[data-sek-spacing="' + _key_ +'"]', $wrapper ).find( 'input[type="number"]' ).val( _val_ );
                        });
                  }
            }
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );