//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};
      $.extend( api.czrInputMap, {
            module_option_switcher : function( input_options ) {
                  var input = this,
                      _section_,
                      initial_content_type;

                  if ( ! api.section.has( input.module.control.section() ) ) {
                        throw new Error( input.input_type + ' => section not registered' );
                  }
                  _section_ = api.section( input.module.control.section() );

                  var module_id = '',
                      requested_ui_action,
                      controlRegistrationParams = input.module.control.params.sek_registration_params;

                  if ( _.isUndefined( controlRegistrationParams ) ) {
                        throw new Error( input.input_type + ' => missing registration params' );
                  }
                  if ( controlRegistrationParams && controlRegistrationParams.module_id ) {
                        module_id = controlRegistrationParams.module_id;
                        requested_ui_action = controlRegistrationParams.ui_action;
                  }
                  if ( _.isEmpty( module_id ) ) {
                        throw new Error( input.input_type + ' => missing module id' );
                  }

                  // attach click event on data-sek-option-type buttons
                  input.container.on('click', '[data-sek-option-type]', function( evt ) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        input.container.find('[data-sek-option-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );

                        api.previewer.trigger( 'settings' === $(this).data( 'sek-option-type') ? 'sek-edit-options' : 'sek-edit-module',
                              {
                                    id : module_id,
                                    level : 'module'
                              }
                        );
                  });

                  // handle the is-selected css class toggling
                  var _requestedOptionType = 'sek-generate-level-options-ui' === requested_ui_action ? 'settings' : 'content';

                  input.container
                        .find('[data-sek-option-type]')
                        .removeClass('is-selected')
                        .attr( 'aria-pressed', false );
                  input.container
                        .find('[data-sek-option-type="'+ _requestedOptionType +'"]')
                        .addClass('is-selected')
                        .attr( 'aria-pressed', true );
            }
      });
})( wp.customize, jQuery, _ );