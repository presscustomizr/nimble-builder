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
            //
            // March 2021 => also clean large select options like fontPicker which generates thousands of lines and slow down the UI dramatically if kept            
            cleanRegisteredAndLargeSelectInput : function( _id_ ) {
                  var self = this,
                      registered = $.extend( true, [], self.registered() || [] );

                  // added for https://github.com/presscustomizr/nimble-builder/issues/403
                  // in order to remove all instantiations of WP editor
                  // @see ::initialize()
                  api.trigger('sek-before-clean-registered');

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

                  // March 2021
                  // clean font picker markup, which generates thousands of select options lines and slow down the entire UI when kept
                  // This concerns the global options and local options for which controls are not cleaned like the one of the levels UI
                  self.cachedElements.$body.find('[data-input-type="font_picker"]').each( function() {
                        var currentInputVal = $(this).find('select[data-czrtype]').val();
                        // clean select 2 instance + all select options
                        if ( !_.isUndefined( $(this).find('select[data-czrtype]').data('czrSelect2') ) ) {
                              $(this).find('select[data-czrtype]').czrSelect2('destroy');
                        }
                        $(this).find('select[data-czrtype]').html('');

                        // append the current input val
                        $(this).find('select[data-czrtype]').html('').append( $('<option>', {
                              value : currentInputVal,
                              html: currentInputVal,
                              selected : "selected"
                        }));

                        $(this).find('select[data-czrtype]').data('selectOptionsSet', false );
                  });

            },


            // This action can be fired after an import, to update the local settings with the imported values
            cleanRegisteredLocalOptionSettingsAndControls : function() {
                  var self = this,
                      localOptionPrefix = self.getLocalSkopeOptionId(),
                      registered = $.extend( true, [], self.registered() || [] );

                  registered = _.filter( registered, function( _reg_ ) {
                        // Remove the local setting
                        if ( _reg_.id && -1 !== _reg_.id.indexOf( localOptionPrefix ) && api.has( _reg_.id ) ) {
                               api.remove( _reg_.id );
                        }
                        // Remove the local control
                        if ( _reg_.id && -1 !== _reg_.id.indexOf( localOptionPrefix ) && api.control.has( _reg_.id ) ) {
                              $.when( api.control( _reg_.id ).container.remove() ).done( function() {
                                    // remove control, section, panel
                                    api.control.remove( _reg_.id );
                              });
                        }
                        // keep only the setting not local
                        return _reg_.id && -1 === _reg_.id.indexOf( localOptionPrefix );
                  });
                  self.registered( registered );
            },


            // Keep only the settings for global option, local options, content picker
            // Remove all the other
            // The level ( section, column module ) settings can be identified because they are registered with a level property
            cleanRegisteredLevelSettings : function() {
                  var self = this,
                      registered = $.extend( true, [], self.registered() || [] );

                  registered = _.filter( registered, function( _reg_ ) {
                        // We check if the level property is empty
                        // if not empty, we can remove the setting from the api.
                        if ( ! _.isEmpty( _reg_.level ) && 'setting' === _reg_.what && api.has( _reg_.id ) ) {
                              // remove setting from the api
                              api.remove( _reg_.id );
                        }
                        // we keep only the setting with
                        // so we preserve the permanent options like global options, local options, content picker
                        return _.isEmpty( _reg_.level ) && 'setting' === _reg_.what ;
                  });
                  self.registered( registered );
            }

      });//$.extend()
})( wp.customize, jQuery );