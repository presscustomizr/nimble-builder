//extends api.CZRBaseControl

var CZRItemMths = CZRItemMths || {};
( function ( api, $, _ ) {
$.extend( CZRItemMths , {
      //The idea is to send only the currently modified item instead of the entire collection
      //the entire collection is sent anyway on api(setId).set( value ), and accessible in the preview via api(setId).bind( fn( to) )
      _sendItem : function( to, from ) {
            var item = this,
                module = item.module,
                _changed_props = [];

            //which property(ies) has(ve) changed ?
            _.each( from, function( _val, _key ) {
                  if ( _val != to[_key] )
                    _changed_props.push(_key);
            });

            _.each( _changed_props, function( _prop ) {
                  api.previewer.send( 'sub_setting', {
                        set_id : module.control.id,
                        id : to.id,
                        changed_prop : _prop,
                        value : to[_prop]
                  });

                  //add a hook here
                  module.trigger('item_sent', { item : to , dom_el: item.container, changed_prop : _prop } );
            });
      },

      // fired on click event
      // @see initialize()
      toggleRemoveAlert : function() {
            var _isVisible = this.removeDialogVisible();
            this.module.closeRemoveDialogs();
            this.removeDialogVisible( ! _isVisible );
      },

      //fired on click dom event
      //for dynamic multi input modules
      //@return void()
      //@param params : { dom_el : {}, dom_event : {}, event : {}, model {} }
      removeItem : function( params ) {
            params = params || {};
            var item = this,
                module = this.module,
                _new_collection = _.clone( module.itemCollection() );

            //hook here
            module.trigger('pre_item_dom_remove', item() );

            //destroy the Item DOM el
            item._destroyView();

            //new collection
            //say it
            _new_collection = _.without( _new_collection, _.findWhere( _new_collection, {id: item.id }) );
            module.itemCollection.set( _new_collection );
            //hook here
            module.trigger('pre_item_api_remove', item() );

            var _item_ = $.extend( true, {}, item() );

            // <REMOVE THE ITEM FROM THE COLLECTION>
            module.czr_Item.remove( item.id );
            // </REMOVE THE ITEM FROM THE COLLECTION>

            //refresh the preview frame (only needed if transport is postMessage && has no partial refresh set )
            //must be a dom event not triggered
            //otherwise we are in the init collection case where the items are fetched and added from the setting in initialize
            if ( 'postMessage' == api(module.control.id).transport && _.has( params, 'dom_event') && ! _.has( params.dom_event, 'isTrigger' ) && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                  // api.previewer.refresh().done( function() {
                  //       _dfd_.resolve();
                  // });
                  // It would be better to wait for the refresh promise
                  // The following approach to bind and unbind when refreshing the preview is similar to the one coded in module::addItem()
                  var triggerEventWhenPreviewerReady = function() {
                        api.previewer.unbind( 'ready', triggerEventWhenPreviewerReady );
                        module.trigger( 'item-removed', _item_ );
                  };
                  api.previewer.bind( 'ready', triggerEventWhenPreviewerReady );
                  api.previewer.refresh();
            } else {
                  module.trigger( 'item-removed', _item_ );
                  module.control.trigger( 'item-removed', _item_ );
            }

      },

      //@return the item {...} from the collection
      //takes a item unique id as param
      getModel : function(id) {
            return this();
      }

});//$.extend
})( wp.customize , jQuery, _ );