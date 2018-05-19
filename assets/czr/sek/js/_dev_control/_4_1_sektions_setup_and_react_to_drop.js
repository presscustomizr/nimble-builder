//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::initialize, on 'sek-refresh-sekdrop' AND on previewer('ready') each time the previewer is refreshed
            // 'sek-refresh-sekdrop' is emitted by the section and the module picker modules with param { type : 'section_picker' || 'module_picker'}
            // @param type 'section_picker' || 'module_picker'
            // @param $el = $( api.previewer.targetWindow().document ).find( '.sektion-wrapper');
            setupSekDrop : function( type, $el ) {
                  if ( $el.length < 1 ) {
                        throw new Error( 'setupSekDrop => invalid Dom element');
                  }

                  // this is the jQuery element instance on which sekDrop shall be fired
                  var instantiateSekDrop = function() {
                        if ( $(this).length < 1 ) {
                              throw new Error( 'instantiateSekDrop => invalid Dom element');
                        }
                        //console.log('instantiateSekDrop', type, $el );
                        var baseOptions = {
                              axis: [ 'vertical' ],
                              isDroppingAllowed: function() { return true; }, //self.isDroppingAllowed.bind( self ),
                              placeholderClass: 'sortable-placeholder',
                              onDragEnter : function( side, event) {
                                 // console.log('On drag enter', event, side , $(event.currentTarget));
                                  //$(event.currentTarget).closest('div[data-sek-level="section"]').trigger('mouseenter');
                                  //console.log('closest column id ?', $(event.currentTarget).closest('div[data-sek-level="column"]').data('sek-id') );
                              },
                              // onDragLeave : function( event, ui) {
                              //     console.log('On drag enter', event, ui );
                              //     $(event.currentTarget).find('[data-sek-action="pick-module"]').show();
                              // },
                              //onDragOver : function( side, event) {},
                              onDropping: function( side, event ) {
                                    event.stopPropagation();
                                    var _position = 'bottom' === side ? $(this).index() + 1 : $(this).index();
                                    //console.log('ON DROPPING', event.originalEvent.dataTransfer.getData( "module-params" ), $(self) );

                                    // console.log('onDropping params', side, event );
                                    // console.log('onDropping element => ', $(self) );
                                    api.czr_sektions.trigger( 'sek-content-dropped', {
                                          drop_target_element : $(this),
                                          location : $(this).closest('[data-sek-level="location"]').data('sek-id'),
                                          position : _position,
                                          before_section : $(this).data('sek-before-section'),
                                          after_section : $(this).data('sek-after-section'),
                                          content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                                          content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                                    });
                              }
                        };

                        var options = {};
                        switch ( type ) {
                              case 'module_picker' :
                                    options = {
                                          items: [
                                                '.sek-module-drop-zone-for-first-module',//the drop zone when there's no module or nested sektion in the column
                                                '.sek-module',// the drop zone when there is at least one module
                                                '.sek-column > .sek-module-wrapper sek-section',// the drop zone when there is at least one nested section
                                                '.sek-content-drop-zone'//between sections
                                          ].join(','),
                                          placeholderContent : function( evt ) {
                                                var $target = $( evt.currentTarget ),
                                                    html = '@missi18n Insert Here';

                                                if ( $target.length > 0 ) {
                                                    if ( 'between-sections' == $target.data('sek-location') ) {
                                                          html = '@missi18n Insert in a new section';
                                                    }
                                                }
                                                return '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                                          },

                                    };
                              break;

                              case 'section_picker' :
                                    options = {
                                          items: [
                                                '.sek-content-drop-zone'//between sections
                                          ].join(','),
                                          placeholderContent : function( evt ) {
                                                $target = $( evt.currentTarget );
                                                var html = '@missi18n Insert a new section here';
                                                return '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                                          },
                                    };
                              break;

                              default :
                                    api.errare( '::setupSekDrop => missing picker type' );
                              break;
                        }

                        var _opts_ = $.extend( true, {}, baseOptions );
                        options = _.extend( _opts_, options );
                        $(this).sekDrop( options ).attr('data-sek-droppable-type', type );
                  };//instantiateSekDrop()

                  //console.log("$( api.previewer.targetWindow().document ).find( '.sektion-wrapper')", $( api.previewer.targetWindow().document ).find( '.sektion-wrapper') );

                  if ( ! _.isUndefined( $el.data('sekDrop') ) ) {
                        $el.sekDrop( 'destroy' );
                  }

                  try {
                        instantiateSekDrop.call( $el );
                  } catch( er ) {
                        api.errare( '::setupSekDrop => Error when firing instantiateSekDrop', er );
                  }
            },//setupSekDrop()


            // invoked on api('ready') from self::initialize()
            reactToDrop : function() {
                  var self = this;
                  // @param {
                  //    drop_target_element : $(el) in which the content has been dropped
                  //    position : 'bottom' or 'top' compared to the drop-zone
                  //    content_type : single module, empty layout, preset module template
                  // }
                  var _do_ = function( params ) {
                        if ( ! _.isObject( params ) ) {
                              throw new Error( 'Invalid params provided' );
                        }
                        if ( params.drop_target_element.length < 1 ) {
                              throw new Error( 'Invalid drop_target_element' );
                        }

                        var dropCase = 'content-in-column';
                        if ( 'between-sections' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-new-section';
                        }
                        if ( 'between-columns' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-new-column';
                        }
                        var focusOnAddedContentEditor;
                        switch( dropCase ) {
                              case 'content-in-column' :
                                    //console.log('PPPPPPPPoooorrams', params );
                                    var $closestLevelWrapper = params.drop_target_element.closest('div[data-sek-level]');
                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'No valid level dom element found' );
                                    }
                                    var _level = $closestLevelWrapper.data( 'sek-level' ),
                                        _id = $closestLevelWrapper.data('sek-id');

                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'No valid level id found' );
                                    }
                                    console.log('drop content-in-column', params );
                                    api.previewer.trigger( 'sek-add-module', {
                                          level : _level,
                                          id : _id,
                                          in_column : params.drop_target_element.closest('div[data-sek-level="column"]').data( 'sek-id'),
                                          in_sektion : params.drop_target_element.closest('div[data-sek-level="section"]').data( 'sek-id'),
                                          position : params.position,
                                          content_type : params.content_type,
                                          content_id : params.content_id
                                    });
                              break;

                              case 'content-in-new-section' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;

                              case 'content-in-new-column' :

                              break;
                        }
                  };

                  // @see module picker or section picker modules
                  // api.czr_sektions.trigger( 'sek-content-dropped', {
                  //       drop_target_element : $(this),
                  //       position : _position,
                  //       before_section : $(this).data('sek-before-section'),
                  //       after_section : $(this).data('sek-after-section'),
                  //       content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                  //       content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                  // });
                  this.bind( 'sek-content-dropped', function( params ) {
                        console.log('sek-content-dropped', params );
                        try { _do_( params ); } catch( er ) {
                              api.errare( 'error when reactToDrop', er );
                        }
                  });
            }//reactToDrop
      });//$.extend()
})( wp.customize, jQuery );