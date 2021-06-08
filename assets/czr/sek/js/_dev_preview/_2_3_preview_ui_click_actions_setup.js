//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            //Fired on Dom ready in initialize()
            scheduleUiClickReactions : function() {
                  var self = this;

                  var _reactOnClick = function( evt ) {
                        // First clean any currently highlighted target drop zone
                        // implemented for double-click insertion https://github.com/presscustomizr/nimble-builder/issues/317
                        api.preview.send( 'sek-clean-target-drop-zone' );

                        var clickedOn = 'inactiveZone',
                              $el = $(evt.target),
                              $hookLocation = $el.closest('[data-sek-level="location"][data-sek-preview-level-guid="' + sekPreviewLocalized.previewLevelGuid +'"]'),
                              $closestLevelWrapper = $el.closest('[data-sek-preview-level-guid="' + sekPreviewLocalized.previewLevelGuid +'"]'),
                              $closestActionIcon = $el.closest('[data-sek-click-on]'),
                              _action,
                              _location_id = $hookLocation.data('sek-id'),
                              _level = $closestLevelWrapper.data('sek-level'),
                              _id = $closestLevelWrapper.data('sek-id');

                        if ( 'add-content' == $el.data('sek-click-on') || ( $el.closest('[data-sek-click-on]').length > 0 && 'add-content' == $el.closest('[data-sek-click-on]').data('sek-click-on') ) ) {
                              clickedOn = 'addContentButton';
                        } else if ( ! _.isEmpty( $el.data( 'sek-click-on' ) ) || $closestActionIcon.length > 0 ) {
                              clickedOn = 'UIIcon';
                        } else if ( 'module' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'moduleWrapper';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') && true === $closestLevelWrapper.data('sek-no-modules') ) {
                              clickedOn = 'noModulesColumn';
                        } else if ( $el.hasClass('sek-to-json') ) {
                              clickedOn = 'sekToJson';
                        } else if ( 'column' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'columnOutsideModules';
                        } else if ( 'section' == $closestLevelWrapper.data('sek-level') ) {
                              clickedOn = 'sectionOutsideColumns';
                        } else if ( ! _.isEmpty( $el.data( 'sek-add' ) ) ) {
                              clickedOn = 'addSektion';
                        } else if ( $el.hasClass('sek-wp-content-wrapper') || $el.hasClass( 'sek-wp-content-dyn-ui') ) {
                              clickedOn = 'wpContent';
                        } else if ( $el.hasClass('sek-edit-wp-content') ) {
                              clickedOn = 'editWpContent';
                        } else {
                              clickedOn = 'inactiveZone';
                        }

                        if ( $hookLocation.length > 0 && _.isEmpty( _location_id ) ) {
                              self.errare( '::scheduleUiClickReactions => error location id can not be empty' );
                        }

                        //console.log('ALORS CLICKED ?', clickedOn, $el );

                        switch( clickedOn ) {
                              case 'addContentButton' :
                                    var is_first_section = true === $el.closest('[data-sek-is-first-section]').data('sek-is-first-section');

                                    api.preview.send( 'sek-add-section', {
                                          location : _location_id,
                                          level : 'section',
                                          before_section : $el.closest('[data-sek-before-section]').data('sek-before-section'),
                                          after_section : $el.closest('[data-sek-after-section]').data('sek-after-section'),
                                          is_first_section : is_first_section,
                                          send_to_preview : ! is_first_section
                                    });
                              break;
                              case 'UIIcon' :
                                    if ( 1 > $closestLevelWrapper.length ) {
                                          throw new Error( 'ERROR => sek-front-preview => No valid level dom element found' );
                                    }
                                    _action = $el.closest('[data-sek-click-on]').data('sek-click-on');

                                    if ( _.isEmpty( _action ) ) {
                                          throw new Error( 'Invalid action' );
                                    }
                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                          throw new Error( 'ERROR => sek-front-preview => No valid level id found' );
                                    }
                                    self._send_( $el, {
                                          action : _action,
                                          location : _location_id,
                                          level : _level,
                                          id : _id,
                                          was_triggered : false //<= indicates that the user clicked.
                                    });
                              break;
                              case 'moduleWrapper' :
                                    // stop here if the ui icons block was clicked
                                    if ( $el.parent('.sek-dyn-ui-icons').length > 0 )
                                          return;

                                    self._send_( $el, {
                                          action : 'edit-module',
                                          level : _level,
                                          id : _id
                                    });
                              break;
                              case 'noModulesColumn' :
                                    // stop here if the ui icons block was clicked
                                    if ( $el.parent('.sek-dyn-ui-icons').length > 0 )
                                          return;

                                    self._send_( $el, {
                                          action : 'edit-options',
                                          location : _location_id,
                                          level : _level,
                                          id : _id
                                    });
                                    //self._send_( $el, { action : 'pick-content', content_type : 'module', level : _level , id : _id } );
                              break;
                              case 'columnOutsideModules' :
                              case 'sectionOutsideColumns' :
                                    self._send_( $el, {
                                          action : 'edit-options',
                                          location : _location_id,
                                          level : _level,
                                          id : _id
                                    });
                              break;
                              case 'addSektion' :
                                    api.preview.send( 'sek-add-section', {
                                          location : _location_id,
                                          level : $el.data('sek-add')
                                    });
                              break;
                              case 'sekToJson' :
                                    api.preview.send( 'sek-to-json', { id : _id } );
                              break;
                              case 'wpContent' :
                                    api.preview.send( 'sek-notify', {
                                          type : 'info',
                                          duration : 8000,
                                          message : sekPreviewLocalized.i18n['This content has been created with the WordPress editor.']
                                    });
                              break;
                              case 'editWpContent' :
                                    // note : the edit url is printed as a data attribute to prevent being automatically parsed by wp when customizing and turned into a changeset url
                                    var edit_url = $el.closest('[data-sek-wp-edit-link]').data('sek-wp-edit-link');
                                    if ( ! _.isEmpty( edit_url ) ) {
                                          window.open( edit_url,'_blank' );
                                    }

                              break;
                              case 'inactiveZone' :
                                    api.preview.send( 'sek-click-on-inactive-zone');//<= for example, collapses the tinyMce editor if expanded
                                    //self._send_( $el, { action : 'pick-content' } );
                              break;
                        }
                  };//_reactOnClick()

                  self.cachedElements.$body.on('click',_reactOnClick );//$('body').on('click', function( evt ) {}

            },//scheduleUserReactions()


            _send_ : function( $el, params ) {
                  //console.log('IN _send_', $el, params );
                  var clonedParams = $.extend( true, {}, params ),
                      syncedTinyMceInputId = '',
                      $moduleWrapper = $el.closest('div[data-sek-level="module"]'),
                      _module_type_ = 'module' === params.level ? $moduleWrapper.data( 'sek-module-type') : '';

                  if ( 'module' === params.level ) {
                        if ( 'czr_tiny_mce_editor_module' === _module_type_ ) {
                              syncedTinyMceInputId = $moduleWrapper.find('div[data-sek-input-id]').length > 0 ? $moduleWrapper.find('div[data-sek-input-id]').data('sek-input-id') : '';
                        }
                  }
                  api.preview.send( 'sek-' + params.action, _.extend( {
                        location : params.location,
                        level : params.level,
                        id : params.id,
                        content_type : $el.data( 'sek-content-type'),
                        module_type : _module_type_,
                        in_column : $el.closest('div[data-sek-level="column"]').length > 0 ? $el.closest('div[data-sek-level="column"]').data( 'sek-id') : '',
                        in_sektion : $el.closest('div[data-sek-level="section"]').length > 0 ? $el.closest('div[data-sek-level="section"]').data( 'sek-id') : '',
                        clicked_input_type : $el.closest('div[data-sek-input-type]').length > 0 ? $el.closest('div[data-sek-input-type]').data('sek-input-type') : '',
                        clicked_input_id : $el.closest('div[data-sek-input-id]').length > 0 ? $el.closest('div[data-sek-input-id]').data('sek-input-id') : '',
                        was_triggered : params.was_triggered,
                        // the syncedTinyMceInputId is used in the control::setupTinyMceEditor()
                        // Fixes : https://github.com/presscustomizr/nimble-builder/issues/251
                        syncedTinyMceInputId : syncedTinyMceInputId
                  }, clonedParams ) );
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
