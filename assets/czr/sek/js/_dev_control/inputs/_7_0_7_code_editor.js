//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            code_editor : function( input_options ) {
                  var input          = this,
                      control        = this.module.control,
                      item           = input.input_parent(),
                      editorSettings = false,
                      $textarea      = input.container.find( 'textarea' ),
                      $input_title   = input.container.find( '.customize-control-title' ),
                      initial_content;
                      //editor_params  = $textarea.data( 'editor-params' );

                  // // When using blocking notifications (type: error) the following block will append a checkbox to the
                  // // notification message block that once checked will allow to save and publish anyways

                  // // Note that rendering is debounced so the props will be used when rendering happens after add event.
                  // control.notifications.bind( 'add', function( notification ) {
                  //       // Skip if control notification is not from setting csslint_error notification.
                  //       if ( notification.code !== control.setting.id + ':' + input.id ) {
                  //             return;
                  //       }

                  //       // Customize the template and behavior of csslint_error notifications.
                  //       notification.templateId = 'customize-code-editor-lint-error-notification';
                  //       notification.render = (function( render ) {
                  //             return function() {
                  //                   var li = render.call( this );
                  //                   li.find( 'input[type=checkbox]' ).on( 'click', function() {
                  //                         control.setting.notifications.remove( input.id );
                  //                   } );
                  //                   return li;
                  //             };
                  //       })( notification.render );
                  // } );
                  var _getEditorParams = function() {
                        return $.Deferred( function( _dfd_ ) {
                              var code_type = _.isEmpty( $textarea.data('editor-code-type') ) ? 'text/html' : $textarea.data('editor-code-type');
                              if ( api.czr_sektions.code_editor_params && api.czr_sektions.code_editor_params[ code_type ] ) {
                                    _dfd_.resolve( api.czr_sektions.code_editor_params[ code_type ] );
                              } else {
                                    wp.ajax.post( 'sek_get_code_editor_params', {
                                          nonce: api.settings.nonce.save,
                                          code_type : code_type
                                    }).done( function( code_editor_params ) {
                                          if ( !_.isObject( code_editor_params ) ) {
                                                api.errare( input.id + ' => error => invalid code editor params sent by server', code_editor_params );
                                          }
                                          api.czr_sektions.code_editor_params = {} || api.czr_sektions.code_editor_params;
                                          api.czr_sektions.code_editor_params[ code_type ] = code_editor_params;
                                          _dfd_.resolve( api.czr_sektions.code_editor_params[ code_type ] );
                                    }).fail( function( _r_ ) {
                                          _dfd_.reject( _r_ );
                                    });
                              }
                        });
                  };

                  // do
                  var _fetchEditorParamsAndInstantiate = function( params ) {
                        if ( true === input.catCollectionSet )
                          return;
                        $.when( _getEditorParams() ).done( function( editorParams ) {
                              _generateOptionsAndInstantiateSelect2(editorParams);
                              if ( params && true === params.open_on_init ) {
                                    // let's open select2 after a delay ( because there's no 'ready' event with select2 )
                                    _.delay( function() {
                                          try{ $selectEl.czrSelect2('open'); } catch(er) {}
                                    }, 100 );
                              }
                        }).fail( function( _r_ ) {
                              api.errare( input.id + ' => fail response when _getEditorParams()', _r_ );
                        });
                        input.catCollectionSet = true;
                  };


                  input.isReady.done( function() {
                        var _doInstantiate = function( evt ) {
                              var input = this;
                              // Bail if we have an instance
                              if ( ! _.isEmpty( input.editor ) )
                                return;
                              // Bail if the control is not expanded yet
                              if ( _.isEmpty( input.module.control.container.attr('data-sek-expanded') ) || "false" == input.module.control.container.attr('data-sek-expanded') )
                                return;

                              setTimeout( function() {
                                    if ( editorSettings ) {
                                          try { initSyntaxHighlightingEditor( editorSettings ); } catch( er ) {
                                                api.errare( 'error in sek_control => code_editor() input', er );
                                                initPlainTextareaEditor();
                                          }
                                    } else {
                                          initPlainTextareaEditor();
                                    }
                                    //focus the editor
                                   $input_title.trigger('click');
                              }, 10 );
                        };
                        // Feb 2021 : modules using this input will now be saved as a json to fix emojis issues
                        // we've started to implement the json saved for the heading module, but all modules will progressively transition to this new format
                        // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
                        // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
                        initial_content = input();
                        if ( api.czr_sektions.isJsonString(initial_content) ) {
                              initial_content = JSON.parse( initial_content );
                        }

                        // inject the content in the code editor now
                        // @fixes the problem of {{...}} syntax being parsed by _. templating system
                        $textarea.html( initial_content );

                        $.when( _getEditorParams() ).done( function( editorParams ) {
                              //$textarea.attr( 'data-editor-params', editorParams );
                              // Obtain editorSettings for instantiation.
                              if ( wp.codeEditor  && ( _.isUndefined( editorParams ) || false !== editorParams )  ) {
                                    // Obtain this input editor settings (we don't have defaults).
                                    editorSettings = editorParams;
                              }

                              // Try to instantiate now
                              _doInstantiate.call(input);

                              // the input should be visible otherwise the code mirror initializes wrongly:
                              // e.g. bad ui (bad inline CSS maths), not visible content until click.
                              // When the code_editor input is rendered in an accordion control ( @see CZRSeksPrototype.scheduleModuleAccordion ), we need to defer the instantiation when the control has been expanded.
                              // fixes @see https://github.com/presscustomizr/nimble-builder/issues/176
                              input.module.control.container.first().one('sek-accordion-expanded', function() {
                                    _doInstantiate.call( input );
                              });
                        }).fail( function(er) {
                              api.errare( input.id + ' => error when getting the editor params from server');
                        });
                  });


                  /**
                   * Initialize syntax-highlighting editor.
                   */
                  var initSyntaxHighlightingEditor = function( codeEditorSettings ) {
                        var suspendEditorUpdate = false,
                            settings;

                        settings = _.extend( {}, codeEditorSettings, {
                              onTabNext: CZRSeksPrototype.selectNextTabbableOrFocusable( ':tabbable' ),
                              onTabPrevious: CZRSeksPrototype.selectPrevTabbableOrFocusable( ':tabbable' ),
                              onUpdateErrorNotice: onUpdateErrorNotice
                        });

                        input.editor = wp.codeEditor.initialize( $textarea, settings );


                        // Improve the editor accessibility.
                        $( input.editor.codemirror.display.lineDiv )
                              .attr({
                                    role: 'textbox',
                                    'aria-multiline': 'true',
                                    'aria-label': $input_title.html(),
                                    'aria-describedby': 'editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4'
                              });

                        // Focus the editor when clicking on its title.
                        $input_title.on( 'click', function( evt ) {
                              evt.stopPropagation();
                              input.editor.codemirror.focus();
                        });


                        /*
                         * When the CodeMirror instance changes, mirror to the textarea,
                         * where we have our "true" change event handler bound.
                         */
                        input.editor.codemirror.on( 'change', function( codemirror ) {
                              suspendEditorUpdate = true;
                              $textarea.val( codemirror.getValue() ).trigger( 'change' );
                              suspendEditorUpdate = false;
                        });

                        // Feb 2021 : modules using this input will now be saved as a json to fix emojis issues
                        // we've started to implement the json saved for the heading module, but all modules will progressively transition to this new format
                        // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
                        // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
                        initial_content = input();
                        if ( api.czr_sektions.isJsonString(initial_content) ) {
                              initial_content = JSON.parse( initial_content );
                        }
                        input.editor.codemirror.setValue( initial_content );

                        // Update CodeMirror when the setting is changed by another plugin.
                        /* TODO: check this */
                        input.bind( input.id + ':changed', function( value ) {
                              if ( ! suspendEditorUpdate ) {
                                    input.editor.codemirror.setValue( value );
                              }
                        });

                        // Prevent collapsing section when hitting Esc to tab out of editor.
                        input.editor.codemirror.on( 'keydown', function onKeydown( codemirror, event ) {
                              var escKeyCode = 27;
                              if ( escKeyCode === event.keyCode ) {
                                    event.stopPropagation();
                              }
                        });
                  };



                  /**
                   * Initialize plain-textarea editor when syntax highlighting is disabled.
                   */
                  var initPlainTextareaEditor = function() {
                        var textarea  = $textarea[0];
                        input.editor = textarea;//assign the editor property
                        $textarea.on( 'blur', function onBlur() {
                              $textarea.data( 'next-tab-blurs', false );
                        } );

                        $textarea.on( 'keydown', function onKeydown( event ) {
                              var selectionStart, selectionEnd, value, tabKeyCode = 9, escKeyCode = 27;

                              if ( escKeyCode === event.keyCode ) {
                                    if ( ! $textarea.data( 'next-tab-blurs' ) ) {
                                          $textarea.data( 'next-tab-blurs', true );
                                          event.stopPropagation(); // Prevent collapsing the section.
                                    }
                                    return;
                              }

                              // Short-circuit if tab key is not being pressed or if a modifier key *is* being pressed.
                              if ( tabKeyCode !== event.keyCode || event.ctrlKey || event.altKey || event.shiftKey ) {
                                    return;
                              }

                              // Prevent capturing Tab characters if Esc was pressed.
                              if ( $textarea.data( 'next-tab-blurs' ) ) {
                                    return;
                              }

                              selectionStart = textarea.selectionStart;
                              selectionEnd = textarea.selectionEnd;
                              value = textarea.value;

                              if ( selectionStart >= 0 ) {
                                    textarea.value = value.substring( 0, selectionStart ).concat( '\t', value.substring( selectionEnd ) );
                                    $textarea.selectionStart = textarea.selectionEnd = selectionStart + 1;
                              }

                              event.stopPropagation();
                              event.preventDefault();
                        });
                  },



                  /**
                   * Update error notice.
                   */
                  onUpdateErrorNotice = function( errorAnnotations ) {
                        var message;

                        control.setting.notifications.remove( input.id );
                        if ( 0 !== errorAnnotations.length ) {
                              if ( 1 === errorAnnotations.length ) {
                                    message = sektionsLocalizedData.i18n.codeEditorSingular.replace( '%d', '1' ).replace( '%s', $input_title.html() );
                              } else {
                                    message = sektionsLocalizedData.i18n.codeEditorPlural.replace( '%d', String( errorAnnotations.length ) ).replace( '%s', $input_title.html() );
                              }
                              control.setting.notifications.add( input.id, new api.Notification( input.id, {
                                    message: message,
                                    type: 'warning'
                              } ) );
                        }
                  }
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );