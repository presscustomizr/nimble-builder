
( function ( api, $, _ ) {
      //PREPARE THE SKOPE AWARE PREVIEWER

      //@return void()
      //Changed the core to specify that the setting preview is actually a deferred callback
      //=> allows us to use syntax like :
      //api( setId ).set( new_value ).done( function() { execute actions when all the setting callbacks have been done })
      // api.Setting.prototype.initialize = function( id, value, options ) {
      //       var setting = this;
      //       api.Value.prototype.initialize.call( setting, value, options );

      //       setting.id = id;
      //       setting.transport = setting.transport || 'refresh';
      //       setting._dirty = options.dirty || false;
      //       setting.notifications = new api.Values({ defaultConstructor: api.Notification });

      //       // Whenever the setting's value changes, refresh the preview.
      //       setting.bind( setting.preview );

      //       // the deferred can be used in moduleCollectionReact to execute actions after the module has been set.
      //       // setting.bind( function( to, from , data ) {
      //       //       return setting.preview( to, from , data );
      //       // }, { deferred : true } );
      // };


      //var _old_preview = api.Setting.prototype.preview;
      //@return a deferred promise
      api.Setting.prototype.preview = function( to, from , data ) {
            var setting = this, transport, dfd = $.Deferred();

            transport = setting.transport;

            //as soon as the previewer is setup, let's behave as usual
            //=> but don't refresh when silently updating

            //Each input instantiated in an item or a modOpt can have a specific transport set.
            //the input transport is hard coded in the module js template, with the attribute : data-transport="postMessage" or "refresh"
            //=> this is optional, if not set, then the transport will be inherited from the the module, which inherits from the control.
            //
            //If the input transport is specifically set to postMessage, then we don't want to send the 'setting' event to the preview
            //=> this will prevent any partial refresh to be triggered if the input control parent is defined has a partial refresh one.
            //=> the input will be sent to preview with api.previewer.send( 'czr_input', {...} )
            //
            //One exception : if the input transport is set to postMessage but the setting has not been set yet in the api (from is undefined, null, or empty) , we usually need to make an initial refresh
            //=> typically, the initial refresh can be needed to set the relevant module css id selector that will be used afterwards for the postMessage input preview

            //If we are in an input postMessage situation, the not_preview_sent param has been set in the czr_Input.inputReact method
            //=> 1) We bail here
            //=> 2) and we will send a custom event to the preview looking like :
            //api.previewer.send( 'czr_input', {
            //       set_id        : module.control.id,
            //       module        : { items : $.extend( true, {}, module().items) , modOpt : module.hasModOpt() ?  $.extend( true, {}, module().modOpt ): {} },
            //       module_id     : module.id,//<= will allow us to target the right dom element on front end
            //       input_id      : input.id,
            //       input_parent_id : input.input_parent.id,//<= can be the mod opt or the item
            //       value         : to
            // });

            //=> if no from (setting not set yet => fall back on defaut transport)
            if ( ! _.isUndefined( from ) && ! _.isEmpty( from ) && ! _.isNull( from ) ) {
                  if ( _.isObject( data ) && true === data.not_preview_sent ) {
                        return dfd.resolve( arguments ).promise();
                  }
            }

            //Don't do anything id we are silent
            if ( _.has( data, 'silent' ) && false !== data.silent )
              return dfd.resolve( arguments ).promise();


            //CORE PREVIEW AS OF WP 4.7+
            if ( 'postMessage' === transport && ! api.state( 'previewerAlive' ).get() ) {
                  transport = 'refresh';
            }

            if ( 'postMessage' === transport ) {
                  //Pre setting event with a richer object passed
                  //=> can be used in a partial refresh scenario to execute actions prior to the actual selective refresh which is triggered on 'setting', just after
                  setting.previewer.send( 'pre_setting', {
                        set_id : setting.id,
                        data   : data,//<= { module_id : 'string', module : {} } which typically includes the module_id and the module model ( items, mod options )
                        value  : to
                  });

                  //WP Default
                  //=> the 'setting' event is used for normal and partial refresh post message actions
                  //=> the partial refresh is fired on the preview if a partial has been registered for this setting in the php customize API
                  //=> When a partial has been registered, the "normal" ( => the not partial refresh ones ) postMessage callbacks will be fired before the ajax ones

                  // Nimble Builder => the 'setting' event triggers a refresh of the previewer dirty values
                  // The dirties are then used to populate $_POST['customized'] params via $.ajaxPrefilter()
                  // @see core customize-preview.js
                  // This is how NB can :
                  // - dynamically register settings server side in PHP customize manager while doing ajax actions
                  // - get the customized sektion collection. @see sek_get_skoped_seks() and Nimble_Collection_Setting::filter_previewed_sek_get_skoped_seks
                  setting.previewer.send( 'setting', [ setting.id, setting() ] );

                  dfd.resolve( arguments );

            } else if ( 'refresh' === transport ) {
                  setting.previewer.refresh();
                  dfd.resolve( arguments );
            }

            return dfd.promise();
      };//api.Setting.prototype.preview
})( wp.customize , jQuery, _ );