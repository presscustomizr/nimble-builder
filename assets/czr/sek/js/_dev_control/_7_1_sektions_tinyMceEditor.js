//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            /* This code is inpired from the plugin customize-posts, GPLv2 or later licensed
                Credits : xwp, westonruter, valendesigns, sayedwp, utkarshpatel.
                Date of original code modification : July 2018
            */
            // fired from ::initialize()
            setupTinyMceEditor: function() {
                  var self = this;
                  // OBSERVABLE VALUES
                  api.sekEditorExpanded   = new api.Value( false );
                  //api.sekEditorSynchronizedInput = new api.Value();

                  self.editorEventsListenerSetup = false;//this status will help us ensure that we bind the shared tinyMce instance only once

                  // Cache some dom elements
                  self.$editorPane = $( '#czr-customize-content_editor-pane' );
                  self.$editorDragbar = $( '#czr-customize-content_editor-dragbar' );
                  self.$preview = $( '#customize-preview' );
                  self.$collapseSidebar = $( '.collapse-sidebar' );

                  self.attachResizeEventsToEditor();

                  // Cache the instance and attach
                  var mayBeAwakeTinyMceEditor = function() {
                        api.sekTinyMceEditor = tinyMCE.get( sektionsLocalizedData.idOfDetachedTinyMceTextArea );
                        var _do = function() {
                              if ( false === self.editorEventsListenerSetup ) {
                                    self.editorEventsListenerSetup = true;
                                    self.trigger('sek-tiny-mce-editor-bound-and-instantiated');
                              }
                        };
                        if ( api.sekTinyMceEditor ) {
                              if ( api.sekTinyMceEditor.initialized ) {
                                    _do();
                              } else {
                                    api.sekTinyMceEditor.on( 'init',function() {
                                        _do();
                                    } );
                              }
                        }
                  };

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
                        // var currentEditorSyncData = $.extend( true, {}, api.sekEditorSynchronizedInput() ),
                        //     newEditorSyncData = _.extend( currentEditorSyncData, {
                        //           input_id : input_id,
                        //           control_id : control_id
                        //     });
                        //api.sekEditorSynchronizedInput( newEditorSyncData );
                        api.sekEditorExpanded( true );
                        //api.sekTinyMceEditor.focus();
                  });



                  // REACT TO EDITOR VISIBILITY
                  api.sekEditorExpanded.bind( function ( expanded, from, params ) {
                        try{ mayBeAwakeTinyMceEditor(); } catch(er) {
                              if ( window.console ) {
                                    console.log('Error in mayBeAwakeTinyMceEditor ', er );
                              }
                        }
                        //api.infoLog('in api.sekEditorExpanded', expanded );
                        if ( expanded && api.sekTinyMceEditor ) {
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

                        self.cachedElements.$window[ expanded ? 'on' : 'off' ]('resize', function() {
                                if ( ! api.sekEditorExpanded() )
                                  return;
                                _.delay( function() {
                                      self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                                }, 50 );

                        });

                        if ( expanded ) {
                              self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                              // fix wrong height on init https://github.com/presscustomizr/nimble-builder/issues/409
                              // there's probably a smarter way to get the right height on init. But let's be lazy.
                              _.delay( function() {
                                    self.cachedElements.$window.trigger('resize');
                              }, 100 );
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
                        api.sekEditorExpanded( false, { context : "clicked anywhere"} );
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
                        //'sek-click-on-inactive-zone', //<=commented to fix #856
                        'sek-add-section',
                        'sek-add-column',
                        'sek-add-module',
                        'sek-remove',
                        'sek-move',
                        'sek-duplicate',
                        'sek-resize-columns',
                        'sek-add-content-in-new-sektion',
                        'sek-pick-content',
                        'sek-edit-options',
                        'sek-edit-module',
                        'sek-notify'
                  ], function( _evt_ ) {
                        if ( 'sek-edit-module' != _evt_ ) {
                              api.previewer.bind( _evt_, function() { api.sekEditorExpanded( false ); } );
                        } else {
                              api.previewer.bind( _evt_, function( params ) {
                                    if ( params && params.module_type ) {
                                          api.sekEditorExpanded(  params.module_type === 'czr_tiny_mce_editor_module' );
                                    }
                              });
                        }
                  });
            },//setupTinyMceEditor




            attachResizeEventsToEditor : function() {
                  var self = this;
                  // LISTEN TO USER DRAG ACTIONS => RESIZE EDITOR
                  // Note : attaching event to the dragbar element was broken => the mouseup event could not be triggered for some reason, probably because adding the class "czr-customize-content_editor-pane-resize", makes us lose access to the dragbar element
                  // => that's why we listen for the mouse events when they have bubbled up to the parent wrapper, and then check if the target is our candidate.
                  $('#czr-customize-content_editor-pane').on( 'mousedown mouseup', function( evt ) {
                        if ( 'mousedown' === evt.type && 'czr-customize-content_editor-dragbar' !== $(evt.target).attr('id') && ! $(evt.target).hasClass('czr-resize-handle') )
                          return;
                        if ( ! api.sekEditorExpanded() )
                          return;
                        switch( evt.type ) {
                              case 'mousedown' :
                                    $( document ).on( 'mousemove.' + sektionsLocalizedData.idOfDetachedTinyMceTextArea, function( event ) {
                                          event.preventDefault();
                                          $( document.body ).addClass( 'czr-customize-content_editor-pane-resize' );
                                          $( '#czr-customize-content_editor_ifr' ).css( 'pointer-events', 'none' );
                                          self.czrResizeEditor( event.pageY );
                                    });
                              break;

                              case 'mouseup' :
                                    $( document ).off( 'mousemove.' + sektionsLocalizedData.idOfDetachedTinyMceTextArea );
                                    $( document.body ).removeClass( 'czr-customize-content_editor-pane-resize' );
                                    $( '#czr-customize-content_editor_ifr' ).css( 'pointer-events', '' );
                              break;
                        }
                  });
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

              var $editorFrame  = $( '#czr-customize-content_editor_ifr' ),
                  $mceTools     = $( '#wp-czr-customize-content_editor-tools' ),
                  $mceToolbar   = self.$editorPane.find( '.mce-toolbar-grp' ),
                  $mceStatusbar = self.$editorPane.find( '.mce-statusbar' );


              if ( ! api.sekEditorExpanded() ) {
                return;
              }

              if ( ! _.isNaN( position ) ) {
                    resizeHeight = windowHeight - position;
              }

              args.height = resizeHeight;
              args.components = $mceTools.outerHeight() + $mceToolbar.outerHeight() + $mceStatusbar.outerHeight();

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
              $editorFrame.css( 'height', args.height - args.components );

              // the code hereafter is not needed.
              // don't remember why it was included from the beginning...
              // self.$collapseSidebar.css(
              //       'bottom',
              //       collapseMinSpacing > windowHeight - args.height ? $mceStatusbar.outerHeight() + collapseBottomInsideEditor : args.height + collapseBottomOutsideEditor
              // );

              //$sectionContent.css( 'padding-bottom',  windowWidth <= mobileWidth ? args.height : '' );
          }
      });//$.extend()
})( wp.customize, jQuery );