
( function ( api, $, _ ) {
      /*****************************************************************************
      * A "CONTEXT AWARE" SET METHD
      *****************************************************************************/
      /**
      * OVERRIDES BASE api.Value set method
      * => adds the o {} param, allowing to pass additional contextual informations.
      *
      * Set the value and trigger all bound callbacks.
      *
      * @param {object} to New value.
      */

      // set: function( to ) {
      //   var from = this._value;

      //   to = this._setter.apply( this, arguments );
      //   to = this.validate( to );

      //   // Bail if the sanitized value is null or unchanged.
      //   if ( null === to || _.isEqual( from, to ) ) {
      //     return this;
      //   }

      //   this._value = to;
      //   this._dirty = true;

      //   this.callbacks.fireWith( this, [ to, from ] );

      //   return this;
      // },
      api.Value.prototype.set = function( to, o ) {
            var from = this._value, dfd = $.Deferred(), self = this, _promises = [];

            to = this._setter.apply( this, arguments );
            to = this.validate( to );
            args = _.extend( { silent : false }, _.isObject( o ) ? o : {} );

            // Bail if the sanitized value is null or unchanged.
            if ( null === to || _.isEqual( from, to ) ) {
                  return dfd.resolveWith( self, [ to, from, o ] ).promise();
            }

            this._value = to;
            this._dirty = true;
            if ( true === args.silent ) {
                  return dfd.resolveWith( self, [ to, from, o ] ).promise();
            }

            if ( this._deferreds ) {
                  _.each( self._deferreds, function( _prom ) {
                        _promises.push( _prom.apply( null, [ to, from, o ] ) );
                  });

                  $.when.apply( null, _promises )
                        .fail( function() { api.errorLog( 'A deferred callback failed in api.Value::set()'); })
                        .then( function() {
                              self.callbacks.fireWith( self, [ to, from, o ] );
                              dfd.resolveWith( self, [ to, from, o ] );
                        });
            } else {
                  this.callbacks.fireWith( this, [ to, from, o ] );
                  return dfd.resolveWith( self, [ to, from, o ] ).promise( self );
            }
            return dfd.promise( self );
      };

      //allows us to specify a list of callbacks + a { deferred : true } param
      //if deferred is found and true, then the callback(s) are added in a list of deferred
      //@see how this deferred list is used in api.Value.prototype.set()
      api.Value.prototype.bind = function() {
          //find an object in the argument
          var self = this,
              _isDeferred = false,
              _cbs = [];

          $.each( arguments, function( _key, _arg ) {
                if ( ! _isDeferred )
                  _isDeferred = _.isObject( _arg  ) && _arg.deferred;
                if ( _.isFunction( _arg ) )
                  _cbs.push( _arg );
          });

          if ( _isDeferred ) {
                self._deferreds = self._deferreds || [];
                _.each( _cbs, function( _cb ) {
                      if ( ! _.contains( _cb, self._deferreds ) )
                        self._deferreds.push( _cb );
                });
          } else {
                //original method
                self.callbacks.add.apply( self.callbacks, arguments );
          }
          return this;
      };

      /*****************************************************************************
      * A SILENT SET METHOD :
      * => keep the dirtyness param unchanged
      * => stores the api state before callback calls, and reset it after
      * => add an object param to the callback to inform that this is a silent process
      * , this is typically used in the overridden api.Setting.preview method
      *****************************************************************************/
      //@param to : the new value to set
      //@param dirtyness : the current dirtyness status of this setting
      //
      api.Setting.prototype.silent_set =function( to, dirtyness ) {
            var from = this._value,
                _save_state = api.state('saved')();

            to = this._setter.apply( this, arguments );
            to = this.validate( to );

            // Bail if the sanitized value is null or unchanged.
            if ( null === to || _.isEqual( from, to ) ) {
              return this;
            }

            this._value = to;
            this._dirty = ( _.isUndefined( dirtyness ) || ! _.isBoolean( dirtyness ) ) ? this._dirty : dirtyness;

            this.callbacks.fireWith( this, [ to, from, { silent : true } ] );
            //reset the api state to its value before the callback call
            api.state('saved')( _save_state );
            return this;
      };
})( wp.customize , jQuery, _ );