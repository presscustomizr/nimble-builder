
( function ( api, $, _ ) {
      //SET THE ACTIVE STATE OF THE THEMES SECTION BASED ON WHAT THE SERVER SENT
      api.bind('ready', function() {
            var _do = function() {
                  // the themeServerControlParams global is printed only with Customizr and Hueman
                  if ( _.isUndefined( window.themeServerControlParams ) || _.isUndefined( themeServerControlParams.isThemeSwitchOn ) )
                    return;

                  if ( ! themeServerControlParams.isThemeSwitchOn ) {
                      //reset the callbacks
                      api.panel('themes').active.callbacks = $.Callbacks();
                      api.panel('themes').active( themeServerControlParams.isThemeSwitchOn );
                  }
            };
            if ( api.panel.has( 'themes') ) {
                  _do();
            } else {
                  api.panel.when( 'themes', function( _s ) {
                        _do();
                  });
            }
      });
})( wp.customize , jQuery, _);