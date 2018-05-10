//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            //Fired on Dom ready in initialize()
            scheduleUiClickReactions : function() {
                  var self = this;

                  $('body').on('click', function( evt ) {

                        var clickedOn = '',
                            $el = $(evt.target),
                            $hook_location = $el.closest('[data-sek-level="location"]'),
                            $closestLevelWrapper = $el.closest('[data-sek-level]'),
                            $closestActionIcon = $el.closest('[data-sek-action]'),
                            _action,
                            _location = $hook_location.data('sek-id'),
                            _level = $closestLevelWrapper.data('sek-level'),
                            _id = $closestLevelWrapper.data('sek-id');
                        if ( 'add-content' == $el.data('sek-action') || ( $el.closest('[data-sek-action]').length > 0 && 'add-content' == $el.closest('[data-sek-action]').data('sek-action') ) ) {
                              clickedOn = 'addContentButton';
                        } else if ( ! _.isEmpty( $el.data( 'sek-action' ) ) || $closestActionIcon.length > 0 ) {
                              clickedOn = 'overlayUiIcon';
                        } else if ( 'module' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'moduleWrapper';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') && true === $closestLevelWrapper.data('sek-no-modules') ) {
                              clickedOn = 'noModulesColumn';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'columnOutsideModules';
                        } else if ( 'section' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'sectionOutsideColumns';
                        } else if ( ! _.isEmpty( $el.data( 'sek-add' ) ) ) {
                              clickedOn = 'addSektion';
                        } else if ( $el.hasClass('sek-to-json') ) {
                              clickedOn = 'sekToJson';
                        } else {
                              clickedOn = 'inactiveZone';
                        }

                        //console.log('CLICKED', $(evt.target), clickedOn );

                        switch( clickedOn ) {
                              case 'addContentButton' :
                                    self._send_( $el, { action : 'pick-section' } );
                              break;
                              case 'overlayUiIcon' :

                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'ERROR => sek-front-preview => No valid level dom element found' );
                                    }
                                    _action = $el.data('sek-action');

                                    if ( _.isEmpty( _action ) ) {
                                        throw new Error( 'Invalid action' );
                                    }
                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'ERROR => sek-front-preview => No valid level id found' );
                                    }
                                    self._send_( $el, {
                                        action : _action,
                                        location : _location,
                                        level : _level,
                                        id : _id
                                    });
                              break;
                              case 'moduleWrapper' :
                                    // stop here if the ui overlay actions block was clicked
                                    if ( $el.parent('.sek-block-overlay-actions').length > 0 )
                                      return;
                                    self._send_( $el, { action : 'edit-module', level : _level , id : _id } );
                              break;
                              case 'noModulesColumn' :
                                    // stop here if the ui overlay actions block was clicked
                                    if ( $el.parent('.sek-block-overlay-actions').length > 0 )
                                      return;

                                    self._send_( $el, { action : 'pick-module', level : _level , id : _id } );
                              break;
                              case 'columnOutsideModules' :
                              case 'sectionOutsideColumns' :
                                    self._send_( $el, {
                                        action : 'edit-options',
                                        location : _location,
                                        level : _level,
                                        id : _id
                                    });
                              break;
                              case 'addSektion' :
                                    api.preview.send( 'sek-add-section', {
                                          location : _location,
                                          level : $el.data('sek-add')
                                    });
                              break;
                              case 'sekToJson' :
                                    api.preview.send( 'sek-to-json', { id : _id } );
                              break;
                              case 'inactiveZone' :
                                    api.preview.send( 'sek-click-on-inactive-zone');
                                    self._send_( $el, { action : 'pick-module' } );
                              break;
                        }
                  });
            },//scheduleUserReactions()


            _send_ : function( $el, params ) {
                  api.preview.send( 'sek-' + params.action, {
                        location : params.location,
                        level : params.level,
                        id : params.id,
                        in_column : $el.closest('div[data-sek-level="column"]').length > 0 ? $el.closest('div[data-sek-level="column"]').data( 'sek-id') : '',
                        in_sektion : $el.closest('div[data-sek-level="section"]').length > 0 ? $el.closest('div[data-sek-level="section"]').data( 'sek-id') : '',
                        clicked_input_type : $el.closest('div[data-sek-input-type]').length > 0 ? $el.closest('div[data-sek-input-type]').data('sek-input-type') : '',
                        clicked_input_id : $el.closest('div[data-sek-input-id]').length > 0 ? $el.closest('div[data-sek-input-id]').data('sek-input-id') : ''
                  });
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
