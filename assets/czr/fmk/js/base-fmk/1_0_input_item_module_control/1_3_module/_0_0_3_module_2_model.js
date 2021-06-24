//MULTI CONTROL CLASS
//extends api.CZRBaseControl
//
//Setup the collection of items
//renders the module view
//Listen to items collection changes and update the control setting

var CZRModuleMths = CZRModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRModuleMths, {
      //Returns the default item defined in initialize
      //Each chid class can override the default item and the following method
      getDefaultItemModel : function( id ) {
              var module = this;
              return $.extend( _.clone( module.defaultItemModel ), { id : id || '' } );
      },

      //////////////////////////////////
      ///MODEL HELPERS
      //////////////////////////////////
      //the job of this function is to return a new item ready to be added to the collection
      //the new item shall have a unique id
      //!!recursive
      _initNewItem : function( _item , _next_key ) {
              var module = this,
                  _new_item = { id : '' },
                  _id;

              //get the next available key of the collection
              _next_key = 'undefined' != typeof(_next_key) ? _next_key : _.size( module.itemCollection() );

              if ( _.isNumber(_next_key) ) {
                _id = module.module_type + '_' + _next_key;
              }
              else {
                _id = _next_key;
                //reset next key to 0 in case a recursive loop is needed later
                _next_key = 0;
              }

              if ( _item && ! _.isEmpty( _item) )
                _new_item = $.extend( _item, { id : _id } );
              else
                _new_item = this.getDefaultItemModel( _id );

              //check the id existence, and its unicity
              if ( _.has(_new_item, 'id') && module._isItemIdPossible(_id) ) {
                    //make sure that the provided item has all the default properties set
                    _.map( module.getDefaultItemModel() , function( value, property ){
                          if ( ! _.has(_new_item, property) )
                            _new_item[property] = value;
                    });

                return _new_item;
              }

              //if id already exists, then test a new one
              return module._initNewItem( _new_item, _next_key + 1);
      }
});//$.extend
})( wp.customize , jQuery, _ );