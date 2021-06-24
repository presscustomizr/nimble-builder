
(function (api, $, _) {
  /*****************************************************************************
  * CAPTURE PREVIEW INFORMATIONS ON REFRESH + REACT TO THEM
  *****************************************************************************/
  //This promise will let us know when we have the first set of preview query ready to use
  //This is needed for modules contextually dependant
  //For example, the slider module will initialize the module model based on the contextual informations, if no items have been set yet.
  api.czr_wpQueryDataReady = api.czr_wpQueryDataReady || $.Deferred();
  api.czr_wpQueryInfos = api.czr_wpQueryInfos || new api.Value();

  //Data are sent by the preview frame when the panel has sent the 'sync' or even better 'active' event
  api.bind( 'ready', function() {
        /* WP CONDITIONAL TAGS => stores and observes the WP conditional tags sent by the preview */
        api.previewer.bind( 'czr-query-data-ready', function( data ) {

              api.czr_wpQueryInfos( data );

              if ( 'pending' == api.czr_wpQueryDataReady.state() ) {
                    api.czr_wpQueryDataReady.resolve( data );
              }
        });

        //PARTIAL REFRESHS => stores and observes the partials data sent by the preview
        //=> this is used in api.CZR_Helpers.hasPartRefresh( control_id )
        //=> as of WP4.7.5, there's no way to get the list of control with partial refresh in the customize-control api
        api.previewer.bind( 'czr-partial-refresh-data', function( data ) {
              api.czr_partials = api.czr_partials || new api.Value();
              api.czr_partials.set( data );
        });

        //PARTIAL REFRESHS : React on partial refresh done
        // @data : { set_id : api setting id }
        api.previewer.bind( 'czr-partial-refresh-done', function( data ) {
              if ( ! _.has( data, 'set_id' ) )
                return;
              var setId = api.CZR_Helpers.build_setId( data.set_id );
              if ( ! api.has( setId ) )
                return;
              //inform the control
              var ctrlId = api.CZR_Helpers.getControlSettingId( setId );
              if ( ! api.control.has( ctrlId ) )
                return;
              api.control( ctrlId ).trigger( 'czr-partial-refresh-done' );
        });
  });//api.bind('ready')
})( wp.customize , jQuery, _ );