//BASE CONTROL CLASS
//extends api.Control
//define a set of methods, mostly helpers, to extend the base WP control class
//this will become our base constructor for main complex controls
//EARLY SETUP

var CZRBaseControlMths = CZRBaseControlMths || {};
( function ( api, $, _ ) {
$.extend( CZRBaseControlMths, {
      initialize: function( id, options ) {
            var control = this;
            //add a shortcut to the css properties declared in the php controls
            control.css_attr = _.has( serverControlParams , 'css_attr') ? serverControlParams.css_attr : {};
            api.Control.prototype.initialize.call( control, id, options );

            //When a partial refresh is done we need to send back all postMessage input to the preview
            //=> makes sure that all post message inputs not yet saved in db are properly applied
            control.bind( 'czr-partial-refresh-done', function() {
                  if ( _.has( control, 'czr_moduleCollection' ) ) {
                        _.each( control.czr_moduleCollection(), function( _mod_ ) {
                              if ( ! control.czr_Module( _mod_.id ) )
                                return;

                              control.czr_Module( _mod_.id ).sendModuleInputsToPreview( { isPartialRefresh : true } );
                        });
                  }
            });
      },

      //@return void()
      refreshPreview : function( obj ) {
            this.previewer.refresh();
      }
});//$.extend//CZRBaseControlMths
})( wp.customize , jQuery, _ );