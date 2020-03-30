//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      // Skope
      $.extend( CZRSeksPrototype, api.Events );
      var CZR_SeksConstructor   = api.Class.extend( CZRSeksPrototype );

      // Schedule skope instantiation on api ready
      // api.bind( 'ready' , function() {
      //       api.czr_skopeBase   = new api.CZR_SeksConstructor();
      // });
      try { api.czr_sektions = new CZR_SeksConstructor(); } catch( er ) {
            api.errare( 'api.czr_sektions => problem on instantiation', er );
      }
})( wp.customize, jQuery );