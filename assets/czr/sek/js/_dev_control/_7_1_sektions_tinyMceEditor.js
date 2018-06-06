//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired from ::initialize()
            setupTinyMceEditor: function() {
                  var self = this;
                  // OBSERVABLE VALUES
                  api.sekEditorExpanded   = new api.Value( false );
                  api.sekEditorSynchronizedInput = new api.Value();

                  self.editorEventsListenerSetup = false;//this status will help us ensure that we bind the shared tinyMce instance only once

                  // Cache the instance and attach
                  var mayBeAwakeTinyMceEditor = function() {
                        api.sekTinyMceEditor = api.sekTinyMceEditor || tinyMCE.get( 'czr-customize-content_editor' );

                        if ( false === self.editorEventsListenerSetup ) {
                              self.attachEventsToEditor();
                              self.editorEventsListenerSetup = true;
                              self.trigger('sek-tiny-mce-editor-bound-and-instantiated');
                        }
                  };


                  // SET THE SYNCHRONIZED INPUT
                  // CASE 1) When user has clicked on a tiny-mce editable content block
                  // CASE 2) when user click on the edit button in the module ui
                  // @see reactToPreviewMsg
                  // Each time a message is received from the preview, the corresponding action are executed
                  // and an event {msgId}_done is triggered on the current instance
                  // This is how we can listen here to 'sek-edit-module_done'
                  // The sek-edit-module is fired when clicking on a .sek-module wrapper @see ::scheduleUiClickReactions
                  self.bind( 'sek-edit-module_done', function( params ) {
                        if ( 'tiny_mce_editor' != params.clicked_input_type )
                          return;

                        // Set a new sync input
                        api.sekEditorSynchronizedInput({
                              control_id : params.id,
                              input_id : params.clicked_input_id
                        });

                        api.sekEditorExpanded( true );
                        api.sekTinyMceEditor.focus();
                  });

                  // CASE 1)
                  // Toggle the editor visibility
                  // Change the button text
                  // set the clicked input id as the new one
                  $('#customize-theme-controls').on('click', '[data-czr-action="open-tinymce-editor"]', function() {
                        //console.log( '[data-czr-action="toggle-tinymce-editor"]', $(this) , api.sekEditorSynchronizedInput() );
                        // Get the control and the input id from the clicked element
                        // => then updated the synchronized input with them
                        var control_id = $(this).data('czr-control-id'),
                            input_id = $(this).data('czr-input-id');
                        if ( _.isEmpty( control_id ) || _.isEmpty( input_id ) ) {
                              api.errare('toggle-tinymce-editor => missing input or control id');
                              return;
                        }
                        var currentEditorSyncData = $.extend( true, {}, api.sekEditorSynchronizedInput() ),
                            newEditorSyncData = _.extend( currentEditorSyncData, {
                                  input_id : input_id,
                                  control_id : control_id
                            });
                        api.sekEditorSynchronizedInput( newEditorSyncData );
                        api.sekEditorExpanded( true );
                        api.sekTinyMceEditor.focus();
                  });


                  // CASE 2)
                  // when the synchronized input gets changed by the user
                  // 1) make sure the editor is expanded
                  // 2) refresh the editor content with the input() one
                  api.sekEditorSynchronizedInput.bind( function( to, from ) {
                        mayBeAwakeTinyMceEditor();
                        //api.sekEditorExpanded( true );
                        //console.log('MODULE VALUE ?', self.getLevelProperty( { property : 'value', id : to.control_id } ) );
                        // When initializing the module, its customized value might not be set yet
                        var _currentModuleValue_ = self.getLevelProperty( { property : 'value', id : to.control_id } ),
                            _currentInputContent_ = ( _.isObject( _currentModuleValue_ ) && ! _.isEmpty( _currentModuleValue_[ to.input_id ] ) ) ? _currentModuleValue_[ to.input_id ] : '';

                        try { api.sekTinyMceEditor.setContent( _currentInputContent_ ); } catch( er ) {
                              api.errare( 'Error when setting the tiny mce editor content in setupTinyMceEditor', er );
                        }
                        api.sekTinyMceEditor.focus();
                  });//api.sekEditorSynchronizedInput.bind( function( to, from )








                  // REACT TO EDITOR VISIBILITY
                  api.sekEditorExpanded.bind( function ( expanded ) {
                        mayBeAwakeTinyMceEditor();
                        //api.infoLog('in api.sekEditorExpanded', expanded );
                        if ( expanded ) {
                            api.sekTinyMceEditor.focus();
                        }
                        $(document.body).toggleClass( 'czr-customize-content_editor-pane-open', expanded);

                        /*
                        * Ensure only the latest input is bound
                        */
                        // if ( api.sekTinyMceEditor.locker && api.sekTinyMceEditor.locker !== input ) {
                        //       //api.sekEditorExpanded.set( false );
                        //       api.sekTinyMceEditor.locker = null;
                        // } if ( ! api.sekTinyMceEditor.locker || api.sekTinyMceEditor.locker === input ) {
                        //       $(document.body).toggleClass('czr-customize-content_editor-pane-open', expanded);
                        //       api.sekTinyMceEditor.locker = input;
                        // }

                        $( window )[ expanded ? 'on' : 'off' ]('resize', function() {
                                if ( ! api.sekEditorExpanded() )
                                  return;
                                _.delay( function() {
                                      self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                                }, 50 );

                        });

                        if ( expanded ) {
                              self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                        } else {
                              //resize reset
                              //self.container.closest( 'ul.accordion-section-content' ).css( 'padding-bottom', '' );
                              self.$preview.css( 'bottom', '' );
                              self.$collapseSidebar.css( 'bottom', '' );
                        }
                  });




                  // COLLAPSING THE EDITOR
                  // or on click on the icon located on top of the editor
                  $('#czr-customize-content_editor-pane' ).on('click', '[data-czr-action="close-tinymce-editor"]', function() {
                        api.sekEditorExpanded( false );
                  });

                  // on click anywhere but on the 'Edit' ( 'open-tinymce-editor' action ) button
                  $('#customize-controls' ).on('click', function( evt ) {
                        if ( 'open-tinymce-editor' == $( evt.target ).data( 'czr-action') )
                          return;

                        api.sekEditorExpanded( false );
                  });

                  // Pressing the escape key collapses the editor
                  // both in the customizer panel and the editor frame
                  $(document).on( 'keydown', _.throttle( function( evt ) {
                        if ( 27 === evt.keyCode ) {
                              api.sekEditorExpanded( false );
                        }
                  }, 50 ));

                  self.bind('sek-tiny-mce-editor-bound-and-instantiated', function() {
                        var iframeDoc = $( api.sekTinyMceEditor.iframeElement ).contents().get(0);
                        $( iframeDoc ).on('keydown', _.throttle( function( evt ) {
                              if ( 27 === evt.keyCode ) {
                                    api.sekEditorExpanded( false );
                              }
                        }, 50 ));
                  });

                  _.each( [
                        'sek-click-on-inactive-zone',
                        'sek-add-section',
                        'sek-add-column',
                        'sek-add-module',
                        'sek-remove',
                        'sek-move',
                        'sek-duplicate',
                        'sek-resize-columns',
                        'sek-add-content-in-new-sektion',
                        'sek-pick-module',
                        'sek-edit-options',
                        'sek-edit-module'
                  ], function( _evt_ ) {
                        if ( 'sek-edit-module' != _evt_ ) {
                              api.previewer.bind( _evt_, function() { api.sekEditorExpanded( false ); } );
                        } else {
                              api.previewer.bind( _evt_, function( params ) {
                                    api.sekEditorExpanded(  params.module_type === 'czr_tiny_mce_editor_module' );
                              });
                        }
                  });
            },//setupTinyMceEditor




            attachEventsToEditor : function() {
                  var self = this;
                  // Cache some dom elements
                  self.$editorTextArea = $( '#czr-customize-content_editor' );
                  self.$editorPane = $( '#czr-customize-content_editor-pane' );
                  self.$editorDragbar = $( '#czr-customize-content_editor-dragbar' );
                  self.$editorFrame  = $( '#czr-customize-content_editor_ifr' );
                  self.$mceTools     = $( '#wp-czr-customize-content_editor-tools' );
                  self.$mceToolbar   = self.$editorPane.find( '.mce-toolbar-grp' );
                  self.$mceStatusbar = self.$editorPane.find( '.mce-statusbar' );

                  self.$preview = $( '#customize-preview' );
                  self.$collapseSidebar = $( '.collapse-sidebar' );

                  // REACT TO EDITOR CHANGES
                  // bind on / off event actions
                  // Problem to solve : we need to attach event to both the visual and the text editor tab ( html editor ), which have different selectors
                  // If we bind only the visual editor, changes made to the simple textual html editor won't be taken into account
                  // VISUAL EDITOR
                  api.sekTinyMceEditor.on( 'input change keyup', function( evt ) {
                        //console.log('api.sekTinyMceEditor on input change keyup', evt.type, api.sekTinyMceEditor.getContent() );
                        // set the input value
                        if ( api.control.has( api.sekEditorSynchronizedInput().control_id ) ) {
                              try { api.control( api.sekEditorSynchronizedInput().control_id )
                                    .trigger( 'tinyMceEditorUpdated', {
                                          input_id : api.sekEditorSynchronizedInput().input_id,
                                          html_content : api.sekTinyMceEditor.getContent(),
                                          modified_editor_element : api.sekTinyMceEditor
                                    });
                              } catch( er ) {
                                    api.errare( 'Error when triggering tinyMceEditorUpdated', er );
                              }
                        }
                  });

                  // TEXT EDITOR
                  self.$editorTextArea.on( 'change keyup', function( evt ) {
                        //console.log('self.$editorTextArea on change keyup', evt.type, self.$editorTextArea.val() );
                        try { api.control( api.sekEditorSynchronizedInput().control_id )
                              .trigger( 'tinyMceEditorUpdated', {
                                    input_id : api.sekEditorSynchronizedInput().input_id,
                                    html_content : self.$editorTextArea.val(),
                                    modified_editor_element : self.$editorTextArea
                              });
                        } catch( er ) {
                              api.errare( 'Error when triggering tinyMceEditorUpdated', er );
                        }
                  });



                  // LISTEN TO USER DRAG ACTIONS => RESIZE EDITOR
                  // self.$editorDragbar.on( 'mousedown mouseup', function( evt ) {
                  //       if ( ! api.sekEditorExpanded() )
                  //         return;
                  //       switch( evt.type ) {
                  //             case 'mousedown' :
                  //                   $( document ).on( 'mousemove.czr-customize-content_editor', function( event ) {
                  //                         event.preventDefault();
                  //                         $( document.body ).addClass( 'czr-customize-content_editor-pane-resize' );
                  //                         self.$editorFrame.css( 'pointer-events', 'none' );
                  //                         self.czrResizeEditor( event.pageY );
                  //                   });
                  //             break;

                  //             case 'mouseup' :
                  //                   $( document ).off( 'mousemove.czr-customize-content_editor' );
                  //                   $( document.body ).removeClass( 'czr-customize-content_editor-pane-resize' );
                  //                   self.$editorFrame.css( 'pointer-events', '' );
                  //             break;
                  //       }
                  // });
            },





            czrResizeEditor : function( position ) {
              var self = this,
                  //$sectionContent = input.container.closest( 'ul.accordion-section-content' ),
                  windowHeight = window.innerHeight,
                  windowWidth = window.innerWidth,
                  minScroll = 40,
                  maxScroll = 1,
                  mobileWidth = 782,
                  collapseMinSpacing = 56,
                  collapseBottomOutsideEditor = 8,
                  collapseBottomInsideEditor = 4,
                  args = {},
                  resizeHeight;

              if ( ! api.sekEditorExpanded() ) {
                return;
              }

              if ( ! _.isNaN( position ) ) {
                    resizeHeight = windowHeight - position;
              }

              args.height = resizeHeight;
              args.components = self.$mceTools.outerHeight() + self.$mceToolbar.outerHeight() + self.$mceStatusbar.outerHeight();

              if ( resizeHeight < minScroll ) {
                    args.height = minScroll;
              }

              if ( resizeHeight > windowHeight - maxScroll ) {
                    args.height = windowHeight - maxScroll;
              }

              if ( windowHeight < self.$editorPane.outerHeight() ) {
                    args.height = windowHeight;
              }

              self.$preview.css( 'bottom', args.height );
              self.$editorPane.css( 'height', args.height );
              self.$editorFrame.css( 'height', args.height - args.components );
              self.$collapseSidebar.css(
                    'bottom',
                    collapseMinSpacing > windowHeight - args.height ? self.$mceStatusbar.outerHeight() + collapseBottomInsideEditor : args.height + collapseBottomOutsideEditor
              );

              //$sectionContent.css( 'padding-bottom',  windowWidth <= mobileWidth ? args.height : '' );
      }
      });//$.extend()
})( wp.customize, jQuery );