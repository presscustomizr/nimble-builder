
(function (api, $, _) {
api.CZR_Helpers = api.CZR_Helpers || {};
//////////////////////////////////////////////////
/// ACTIONS AND DOM LISTENERS
//////////////////////////////////////////////////
//adds action to an existing event map
//@event map = [ {event1}, {event2}, ... ]
//@new_event = {  trigger   : event name , actions   : [ 'cb1', 'cb2', ... ] }
api.CZR_Helpers = $.extend( api.CZR_Helpers, {

      // Fetches a module tmpl from the server if not yet cached
      // {
      //   tmpl : 'item-inputs',
      //   module_type: module.module_type || 'all_modules',
      //   module_id : ''
      //   ... <= other custom args can be added dynamically here. Like the item_model when fetching the item content template
      // }
      // @return a promise()
      getModuleTmpl : function( args ) {
            var dfd = $.Deferred();
            args = _.extend( {
                  tmpl : '',
                  module_type: '',
                  module_id : '',
                  cache : true,//<= shall we cache the tmpl or not. Should be true in almost all cases.
                  nonce: api.settings.nonce.save//<= do we need to set a specific nonce to fetch the tmpls ?
            }, args );

            // are we good to go ?
            if ( _.isEmpty( args.tmpl ) || _.isEmpty( args.module_type ) ) {
                  dfd.reject( 'api.CZR_Helpers.getModuleTmpl => missing tmpl or module_type param' );
            }

            // This will be used to store the previously fetched template
            // 1) the generic templates used for all_modules
            // 2) each module templates : pre-item inputs, item inputs and mod options
            api.CZR_Helpers.czr_cachedTmpl = api.CZR_Helpers.czr_cachedTmpl || {};
            api.CZR_Helpers.czr_cachedTmpl[ args.module_type ] = api.CZR_Helpers.czr_cachedTmpl[ args.module_type ] || {};

            // when cache is on, use the cached template
            // Example of cache set to off => the skoped items templates are all different because based on the control type => we can't cache them
            if ( true === args.cache && ! _.isEmpty( api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ] ) && _.isString( api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ] ) ) {
                  //console.log('Cached => ', args.tmpl );
                  dfd.resolve( api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ] );
            } else {
                  // if the tmpl is currently being fetched, return the temporary promise()
                  // this can occurs when rendering a multi-item module for the first time
                  // assigning the tmpl ajax request to the future cache entry allows us to fetch only once
                  if ( _.isObject( api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ] ) && 'pending' == api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ].state() ) {
                        return api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ];//<= this is a $.promise()
                  } else {
                        //console.log('Needs to be fetched => ', args.tmpl );
                        // First time fetch
                        api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ] = wp.ajax.post( 'ac_get_template', args )
                              .done( function( _serverTmpl_ ) {
                                    // resolve and cache
                                    dfd.resolve( _serverTmpl_ );
                                    api.CZR_Helpers.czr_cachedTmpl[ args.module_type ][ args.tmpl ] = _serverTmpl_;
                              }).fail( function( _r_ ) {
                                    //console.log( 'api.CZR_Helpers.getModuleTmpl => ', _r_ );
                                    api.errare( 'api.CZR_Helpers.getModuleTmpl => Problem when fetching the ' + args.tmpl + ' tmpl from server for module : ' + args.module_id + ' ' + args.module_type, _r_);
                                    dfd.reject( _r_ );
                                    // Nimble => display an error notification when
                                    // - session has expired
                                    // - when statusText is "Bad Request"
                                    if ( _.isObject( _r_ ) ) {
                                          if ( 'invalid_nonce' === _r_.code || 'Bad Request' === _r_.statusText ) {
                                                if ( window.sektionsLocalizedData && sektionsLocalizedData.i18n ) {
                                                      api.previewer.trigger( 'sek-notify', { type : 'error', duration : 30000, message : sektionsLocalizedData.i18n['Something went wrong, please refresh this page.'] });
                                                }
                                          }
                                    }
                              });
                  }
            }
            return dfd.promise();
      }

});//$.extend
  // $( window ).on( 'message', function( e, o) {
  //   api.consoleLog('WHAT ARE WE LISTENING TO?', e, o );
  // });
})( wp.customize , jQuery, _);