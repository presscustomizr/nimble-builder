//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            //@return void()
            // clean registered controls, sections, panels
            // only elements that have a true "track" param on registration are populated in the registered() collection
            // if the _id_ param is not specified, all registered controls, sections and panels are removed.
            //
            // preserve the settings => because this is where the customizer changeset of values is persisted before publishing
            // typically fired before updating the ui. @see ::generateUI()
            cleanRegistered : function( _id_ ) {
                  var self = this,
                      registered = $.extend( true, [], self.registered() || [] );

                  registered = _.filter( registered, function( _reg_ ) {
                        if ( 'setting' !== _reg_.what ) {
                              if ( api[ _reg_.what ].has( _reg_.id ) ) {
                                    if ( ! _.isEmpty( _id_ ) && _reg_.id !== _id_ )
                                      return;
                                    // fire an event before removal, can be used to clean some jQuery plugin instance for example
                                    if (  _.isFunction( api[ _reg_.what ]( _reg_.id ).trigger ) ) {//<= Section and Panel constructor are not extended with the Event class, that's why we check if this method exists
                                           self.trigger( 'sek-ui-pre-removal', { what : _reg_.what, id : _reg_.id } );
                                    }
                                    $.when( api[ _reg_.what ]( _reg_.id ).container.remove() ).done( function() {
                                          // remove control, section, panel
                                          api[ _reg_.what ].remove( _reg_.id );
                                          // useful event, used to destroy the $ drop plugin instance for the section / module picker
                                          self.trigger( 'sek-ui-removed', { what : _reg_.what, id : _reg_.id } );
                                    });
                              }
                        }
                        return _reg_.what === 'setting';
                  });
                  self.registered( registered );
            },

            // Keep only the settings for global option, local options, content picker
            // Remove all the other
            cleanRegisteredLevelSettingsAfterHistoryNavigation : function() {
                  var self = this,
                      registered = $.extend( true, [], self.registered() || [] );

                  registered = _.filter( registered, function( _reg_ ) {
                        // We check if the level property is set, so we preserve the permanent options like global options, local options, content picker
                        if ( ! _.isEmpty( _reg_.level ) && 'setting' === _reg_.what ) {
                              if ( api.has( _reg_.id ) ) {
                                    // remove setting from the api
                                    api.remove( _reg_.id );
                              }
                        }
                        return _.isEmpty( _reg_.level ) && 'setting' !== _reg_.what ;
                  });
                  self.registered( registered );
            }

      });//$.extend()
})( wp.customize, jQuery );