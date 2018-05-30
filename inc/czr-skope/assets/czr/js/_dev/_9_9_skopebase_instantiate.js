//@global serverControlParams
var CZRSkopeBaseMths = CZRSkopeBaseMths || {};
(function ( api, $ ) {
      // Skope
      $.extend( CZRSkopeBaseMths, api.Events );
      var CZR_SkopeBase   = api.Class.extend( CZRSkopeBaseMths );

      // Schedule skope instantiation on api ready
      // api.bind( 'ready' , function() {
      //       api.czr_skopeBase   = new api.CZR_SkopeBase();
      // });
      api.czr_skopeBase   = new CZR_SkopeBase();
})( wp.customize, jQuery );