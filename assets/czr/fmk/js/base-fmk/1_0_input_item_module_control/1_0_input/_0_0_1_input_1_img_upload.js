var CZRInputMths = CZRInputMths || {};
( function ( api, $, _ ) {
$.extend( CZRInputMths , {
    // callback for data-input-type="upload"
    setupImageUploaderSaveAsId : function() {
          this.setupImageUploader();
    },

    // callback for data-input-type="upload_url"
    setupImageUploaderSaveAsUrl : function() {
          this.setupImageUploader( { save_as_url : true } );
    },

    //@param args { save_as_url : false }
    setupImageUploader : function( args ) {
          var input        = this,
              _model       = input();

          args = _.extend( { save_as_url : false }, args || {} );
          input.save_as_url = args.save_as_url;

          //an instance field where we'll store the current attachment
          input.attachment   = {};

          //do we have an html template and a input container?
          if ( ! input.container )
            return this;

          input.tmplRendered = $.Deferred();
          input.setupContentRendering( _model, {} );

          //valid just in the init
          input.tmplRendered
                .done( function(){
                      input.czrImgUploaderBinding();
                })
                .fail( function() {
                      api.errorLog( 'setupImageUploader => failed to fetch the template.');
                });
  },

  setupContentRendering : function( to, from ) {
        var input = this, _attachment;
        //retrieve new image if 'to' is different from the saved one
        //NEED A BETTER WAY?
        if ( ( input.attachment.id != to ) && from !== to ) {
              if ( _.isEmpty( to ) ) {
                    input.attachment = {};
                    input.renderImageUploaderTemplate();
              // handles the case when a url is provided
              // Occurs for example when contextualizing the header_image with skope
              } else if ( ! _.isNumber( to ) ) {
                    input.renderImageUploaderTemplate( { fromUrl : true, url : to });
              }
              //Has this image already been fetched ?
              _attachment = wp.media.attachment( to );
              if ( _.isObject( _attachment ) && _.has( _attachment, 'attributes' ) && _.has( _attachment.attributes, 'sizes' ) ) {
                    input.attachment       = _attachment.attributes;
                    input.renderImageUploaderTemplate();
              }
              // If not, try to fetch it but only if the candidate "to" is a number
              else {
                    if ( _.isNumber( to ) ) {
                          wp.media.attachment( to ).fetch().done( function() {
                                input.attachment       = this.attributes;
                                input.renderImageUploaderTemplate();
                          }).fail( function() {
                                api.errorLog('renderImageUploaderTemplate => failed attempt to fetch an img with id : ' + to );
                                // input.attachment = {};
                                // input.renderImageUploaderTemplate();
                          });
                     }
              }
        }//Standard reaction, the image has been updated by the user or init
        else if (  ! input.attachment.id || input.attachment.id === to ) {
              input.renderImageUploaderTemplate();
        }
  },

  czrImgUploaderBinding : function() {
        var input = this;
        //Bind events
        // Shortcut so that we don't have to use _.bind every time we add a callback.
        _.bindAll( input, 'czrImgUploadRemoveFile', 'czrImgUploadOpenFrame', 'czrImgUploadSelect');

        // Bind events, with delegation to facilitate re-rendering.
        input.container.on( 'click keydown', '.upload-button', input.czrImgUploadOpenFrame );
        input.container.on( 'click keydown', '.thumbnail-image img', input.czrImgUploadOpenFrame );
        input.container.on( 'click keydown', '.remove-button', input.czrImgUploadRemoveFile );

        input.bind( input.id + ':changed', function( to, from ){
              input.tmplRendered = $.Deferred();
              input.setupContentRendering( to, from );
        });
  },
  /**
  * Open the media modal.
  */
  czrImgUploadOpenFrame: function( event ) {
        if ( api.utils.isKeydownButNotEnterEvent( event ) ) {
          return;
        }

        event.preventDefault();

        if ( ! this.frame ) {
          this.czrImgUploadInitFrame();
        }

        this.frame.open();
  },

  /**
  * Create a media modal select frame, and store it so the instance can be reused when needed.
  */
  czrImgUploadInitFrame: function() {
        var input = this,
            button_labels = this.getUploaderLabels();

        input.frame = wp.media({
                button: {
                      text: button_labels.frame_button
                },
                states: [
                       new wp.media.controller.Library({
                              title:     button_labels.frame_title,
                              library:   wp.media.query({ type: 'image' }),
                              multiple:  false,
                              date:      false
                       })
                ]
        });
        // When a file is selected, run a callback.
        input.frame.on( 'select', input.czrImgUploadSelect );
  },

  /**
  * Called when the "Remove" link is clicked. Empties the setting.
  *
  * @param {object} event jQuery Event object
  */
  czrImgUploadRemoveFile: function( event ) {
        var input = this;

        if ( api.utils.isKeydownButNotEnterEvent( event ) ) {
          return;
        }
        event.preventDefault();
        //reset the attachment class field
        input.attachment = {};
        //set the model
        input.set('');
  },


  /**
  * Callback handler for when an attachment is selected in the media modal.
  * Gets the selected image information, and sets it within the input.
  */
  czrImgUploadSelect: function() {
        var node,
            input = this,
            attachment   = input.frame.state().get( 'selection' ).first().toJSON(),  // Get the attachment from the modal frame.
            mejsSettings = window._wpmejsSettings || {};
        //save the attachment in a class field
        input.attachment = attachment;
        //set the model
        input.set( input.save_as_url ? attachment.url : attachment.id );
  },




  //////////////////////////////////////////////////
  /// HELPERS
  //////////////////////////////////////////////////
  // @param args = { fromUrl : true, url : to } || null
  renderImageUploaderTemplate: function( args ) {
        var input  = this;
        args = _.extend( { fromUrl : false, url : '' }, args || {} );

        // //do we have view template script?
        // if ( 0 === $( '#tmpl-czr-input-img-uploader-view-content' ).length ) {
        //       throw new Error('renderImageUploaderTemplate => Missing template for input ' + input.id );
        // }


        // var view_template = wp.template('czr-input-img-uploader-view-content');

        // //  do we have an html template and a module container?
        // if ( ! view_template  || ! input.container )
        //  return;

        var $_view_el    = input.container.find('.' + input.module.control.css_attr.img_upload_container );

        if ( ! $_view_el.length || 1 > input.container.length )
          return;

        var _template_params = {
              button_labels : input.getUploaderLabels(),
              settings      : input.id,
              attachment    : input.attachment,
              fromUrl       : args.url,
              canUpload     : true
        };

        if ( $('#tmpl-czr-img-uploader').length > 0 ) {
              $_view_el.html( wp.template( 'czr-img-uploader' )( _template_params ) );
              input.tmplRendered.resolve();
              input.container.trigger( input.id + ':content_rendered' );
        } else {
              api.CZR_Helpers.getModuleTmpl( {
                    tmpl : 'img-uploader',
                    module_type: 'all_modules',
                    module_id : input.module.id
              } ).done( function( _serverTmpl_ ) {
                    //console.log( 'renderModuleParts => success response =>', input.module.id, _serverTmpl_);
                    $_view_el.html( api.CZR_Helpers.parseTemplate( _serverTmpl_ )( _template_params ) );
                    input.tmplRendered.resolve();
                    input.container.trigger( input.id + ':content_rendered' );
              }).fail( function( _r_ ) {
                    //console.log( 'renderModuleParts => fail response =>', _r_);
                    input.tmplRendered.reject( 'renderImageUploaderTemplate => Problem when fetching the tmpl from server for module : '+ input.module.id );
              });
        }


        return true;
  },

  getUploaderLabels : function() {
        var _ts = serverControlParams.i18n,
            input = this,
            _map = {
            'select'      : _ts.select_image,
            'change'      : _ts.change_image,
            'remove'      : _ts.remove_image,
            'default'     : _ts.default_image,
            'placeholder' : _ts.placeholder_image,
            'frame_title' : _ts.frame_title_image,
            'frame_button': _ts.frame_button_image
        };

        //are we fine ?
        var _fallbackmap = {};
        _.each( _map, function( ts_string, key ) {
              if ( _.isEmpty( ts_string ) ) {
                    api.errorLog( 'A translated string is missing ( ' + key + ' ) for the image uploader input in module : ' + input.module.id );
                    _fallbackmap[ key ] = key;
                    return;
              } else {
                _fallbackmap[ key ] = ts_string;
              }
        });

        return _fallbackmap;
  }
});//$.extend
})( wp.customize , jQuery, _ );