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
                  var input   = this,
                      control = this.module.control,
                      item    = input.input_parent(),
                      editorSettings = false,
                      $textarea = input.container.find( 'textarea' ),
                      $input_title = input.container.find( '.customize-control-title' ),
                      editor_params = $textarea.data( 'editor-params' );


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

                  // Obtain editorSettings for instantiation.
                  if ( wp.codeEditor  && ( _.isUndefined( editor_params ) || false !== editor_params )  ) {
                        // Obtain default editor settings.
                        editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
                        editorSettings.codemirror = _.extend (
                              {},
                              editorSettings.codemirror,
                              {
                                    indentUnit: 2,
                                    tabSize: 2
                              }
                        );

                        // Merge editor_settings param on top of defaults.
                        if ( _.isObject( editor_params ) ) {
                              _.each( editor_params, function( value, key ) {
                                    if ( _.isObject( value ) ) {
                                          editorSettings[ key ] = _.extend(
                                                {},
                                                editorSettings[ key ],
                                                value
                                          );
                                    }
                              } );
                        }
                  }

                  input.isReady.done( function() {
                        // the input should be visible otherwise the code mirror initializes wrongly:
                        // e.g. bad ui (bad inline CSS maths), not visible content until click
                        setTimeout( function() {
                              if ( editorSettings ) {
                                    initSyntaxHighlightingEditor( editorSettings );
                              } else {
                                    initPlainTextareaEditor();
                              }
                              //focus the editor
                             $input_title.click();
                        }, 10 );
                  });


                  /**
                   * Initialize syntax-highlighting editor.
                   */
                  var initSyntaxHighlightingEditor = function( codeEditorSettings ) {
                        var suspendEditorUpdate = false,
                            settings;

                        settings = _.extend( {}, codeEditorSettings, {
                              onTabNext: onTabNext,
                              onTabPrevious: onTabPrevious,
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
                        $input_title.on( 'click', function() {
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

                        input.editor.codemirror.setValue( input.input_parent().html_content );

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
                  },



                  /**
                   * Handle tabbing to the tabbable element before the editor.
                   */
                  onTabNext = function() {
                        CZRSeksPrototype.selectNextTabbableOrFocusable( ':tabbable' );
                  },



                  /**
                   * Handle tabbing to the tabbable element after the editor.
                   */
                  onTabPrevious = function() {
                        CZRSeksPrototype.selectPrevTabbableOrFocusable( ':tabbable' );
                  }
            }
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );