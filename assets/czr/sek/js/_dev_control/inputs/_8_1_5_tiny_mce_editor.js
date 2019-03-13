//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            nimble_tinymce_editor : function() {
                  var input = this,
                      $textarea = input.container.find('textarea').first(),
                      _id = $textarea.length > 0 ? $textarea.attr('id') : null,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      // see how those buttons can be set in php class _NIMBLE_Editors
                      // @see the global js var nimbleTinyMCEPreInit includes all params
                      defaultToolbarBtns = "formatselect,forecolor,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,strikethrough,hr,pastetext,removeformat,charmap,outdent,indent,undo,redo",
                      defaultQuickTagBtns = "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close";

                  if ( _.isNull( _id ) ) {
                        throw new Error( 'setupTinyMceEditor => missing textarea for module :' + input.module.id );
                  }
                  if ( tinyMCE.get( _id ) ) {
                        throw new Error( 'setupTinyMceEditor => duplicate editor id.');
                  }
                  var getToolbarBtns = function() {
                        var toolBarBtn = defaultToolbarBtns.split(',');
                        if ( inputRegistrationParams.editor_params && _.isArray( inputRegistrationParams.editor_params.excludedBtns ) ) {
                            var excluded = inputRegistrationParams.editor_params.excludedBtns;
                            toolBarBtn = _.filter( toolBarBtn, function( _btn ) {
                                  return !_.contains( excluded, _btn );
                            });
                       }
                       return toolBarBtn.join(',');
                  };
                  // Set a height for the textarea before instanciation
                  $textarea.css( 'height', self.TINYMCE_EDITOR_HEIGHT );

                  // the plugins like colorpicker have been loaded when instantiating the detached tinymce editor
                  // @see php class _NIMBLE_Editors
                  // if not specified, wp.editor falls back on the ones of wp.editor.getDefaultSettings()
                  // we can use them here without the need to specify them in the tinymce {} params
                  // @see the tinyMCE params with this global var : nimbleTinyMCEPreInit.mceInit["czr-customize-content_editor"]
                  wp.editor.initialize( _id, {
                        //tinymce: nimbleTinyMCEPreInit.mceInit["czr-customize-content_editor"],
                        tinymce: {
                            //plugins:"charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,wordpress,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
                            toolbar1:getToolbarBtns(),
                            //toolbar2:"",
                            content_css:( function() {
                                  var default_settings = wp.editor.getDefaultSettings(),
                                      stylesheets = [ sektionsLocalizedData.tinyMceNimbleEditorStylesheetUrl ];
                                  if ( default_settings && default_settings.tinymce && default_settings.tinymce.content_css ) {
                                        stylesheets = _.union( default_settings.tinymce.content_css.split(','), stylesheets );
                                  }
                                  return stylesheets.join(',');
                            })()
                        },
                        quicktags : defaultQuickTagBtns,
                        mediaButtons: ( inputRegistrationParams.editor_params && false === inputRegistrationParams.editor_params.media_button ) ? false : true
                  });

                  // Note that an easy way to instantiate a basic editor would be to use :
                  //wp.editor.initialize( _id, { tinymce : { wpautop: false }, quicktags : true });

                  var _editor = tinyMCE.get( _id );
                  if ( ! _editor ) {
                        throw new Error( 'setupTinyMceEditor => missing editor instance for module :' + input.module.id );
                  }

                  // Store the id of each instantiated tinyMceEditor
                  // used in api.czrSektion::cleanRegistered
                  api.czrActiveWPEditors = api.czrActiveWPEditors || [];
                  var currentEditors = $.extend( true, [], api.czrActiveWPEditors );
                  currentEditors.push(_id);
                  api.czrActiveWPEditors = currentEditors;

                  // Let's set the input() value when the editor is ready
                  // Because when we instantiate it, the textarea might not reflect the input value because too early
                  var _doOnInit = function() {
                        _editor.setContent( input() );
                  };
                  if ( _editor.initialized ) {
                        _doOnInit();
                  } else {
                        _editor.on( 'init',_doOnInit );
                  }

                  // bind events
                  _editor.on( 'input change keyup', function( evt ) {
                        input( _editor.getContent() );
                  } );
            },




            // this input setup works in collaboration with ::setupTinyMceEditor()
            // for api.sekEditorExpanded() and resizing of the editor.
            detached_tinymce_editor : function() {
                  var input = this,
                      $textarea = $('textarea#' + sektionsLocalizedData.idOfDetachedTinyMceTextArea ), //$('textarea#czr-customize-content_editor'),
                      _id;
                  if ( $textarea.length > 0  ) {
                        _id = $textarea.attr('id');
                  } else {
                        throw new Error( 'api.czrInputMap::detached_tinymce_editor => missing textarea element');
                  }

                  // if ( _.isNull( _id ) ) {
                  //       throw new Error( 'setupDetachedTinyMceEditor => missing textarea for module :' + input.module.id );
                  // }
                  // See wp.editor.initialize() in wp-admin/js/editor.js for initialization options.
                   // **
                   // * Initialize TinyMCE and/or Quicktags. For use with wp_enqueue_editor() (PHP).
                   // *
                   // * Intended for use with an existing textarea that will become the Text editor tab.
                   // * The editor width will be the width of the textarea container, height will be adjustable.
                   // *
                   // * Settings for both TinyMCE and Quicktags can be passed on initialization, and are "filtered"
                   // * with custom jQuery events on the document element, wp-before-tinymce-init and wp-before-quicktags-init.
                   // *
                   // * @since 4.8.0
                   // *
                   // * @param {string} id The HTML id of the textarea that is used for the editor.
                   // *                    Has to be jQuery compliant. No brackets, special chars, etc.
                   // * @param {object} settings Example:
                   // * settings = {
                   // *    // See https://www.tinymce.com/docs/configure/integration-and-setup/.
                   // *    // Alternatively set to `true` to use the defaults.
                   // *    tinymce: {
                   // *        setup: function( editor ) {
                   // *            console.log( 'Editor initialized', editor );
                   // *        }
                   // *    }
                   // *
                   // *    // Alternatively set to `true` to use the defaults.
                   // *    quicktags: {
                   // *        buttons: 'strong,em,link'
                   // *    }
                   // * }
                   // */

                  // Remove now
                  // the initial instance has been created with php inline js generated by sek_setup_nimble_editor()
                  // IMPORTANT !! => don't use wp.editor.remove() @see wp-admin/js/editor.js, because it can remove the stylesheet editor.css
                  // and break the style of all editors
                  if ( window.tinymce ) {
                    mceInstance = window.tinymce.get( _id );

                    if ( mceInstance ) {
                      // if ( ! mceInstance.isHidden() ) {
                      //   mceInstance.save();
                      // }
                      mceInstance.remove();
                    }
                  }
                  // if ( window.quicktags ) {
                  //   qtInstance = window.QTags.getInstance( id );

                  //   if ( qtInstance ) {
                  //     qtInstance.remove();
                  //   }
                  // }

                  // Instantiate a new one
                  // see in wp-admin/js/editor.js
                  // the nimbleTinyMCEPreInit are set in php class _NIMBLE_Editors
                  if ( !window.nimbleTinyMCEPreInit || !window.nimbleTinyMCEPreInit.mceInit || !window.nimbleTinyMCEPreInit.mceInit[ _id ] ) {
                        throw new Error('setupDetachedTinyMceEditor => invalid nimbleTinyMCEPreInit global var');
                  }

                  var init_settings = nimbleTinyMCEPreInit.mceInit[ _id ];
                  init_settings.content_css = ( function() {
                        var default_settings = wp.editor.getDefaultSettings(),
                            stylesheets = [ sektionsLocalizedData.tinyMceNimbleEditorStylesheetUrl ];
                        if ( default_settings && default_settings.tinymce && default_settings.tinymce.content_css ) {
                              stylesheets = _.union( default_settings.tinymce.content_css.split(','), stylesheets );
                        }
                        return stylesheets.join(',');
                  })();

                  window.tinymce.init( init_settings );
                  window.QTags.getInstance( _id );
                  // wp.editor.initialize( _id, {
                  //       //tinymce : true,
                  //       tinymce: nimbleTinyMCEPreInit.mceInit[_id],
                  //       quicktags : nimbleTinyMCEPreInit.qtInit[_id],
                  //       mediaButtons: true
                  // });

                  var _editor = tinyMCE.get( _id );
                  if ( ! _editor ) {
                        throw new Error( 'setupDetachedTinyMceEditor => missing editor instance for module :' + input.module.id );
                  }

                  // Let's set the input() value when the editor is ready
                  // Because when we instantiate it, the textarea might not reflect the input value because too early
                  var _doOnInit = function() {
                        _editor.setContent( input() );
                        api.sekEditorExpanded( true );
                  };
                  if ( _editor.initialized ) {
                        _doOnInit();
                  } else {
                        _editor.on( 'init', _doOnInit );
                  }

                  // bind events
                  _editor.on( 'input change keyup keydown click SetContent BeforeSetContent', function( evt ) {
                        //$textarea.trigger( 'change', {current_input : input} );
                        input( _editor.getContent() );
                  } );

                  // store the current input now, so we'll always get the right one when textarea changes
                  api.sekCurrentDetachedTinyMceInput = input;

                  // TEXT EDITOR => This is the original textarea element => needs to be bound separatelyn because not considered part of the tinyMce editor.
                  // Bound only once
                  if ( !$textarea.data('czr-bound-for-detached-editor') ) {
                        $textarea.on( 'input', function( evt, params ) {
                              api.sekCurrentDetachedTinyMceInput( $(this).val() );
                        });
                        $textarea.data('czr-bound-for-detached-editor', true );
                  }

            },//setupDetachedTinyMceEditor
      });//$.extend( api.czrInputMap, {})
})( wp.customize, jQuery, _ );