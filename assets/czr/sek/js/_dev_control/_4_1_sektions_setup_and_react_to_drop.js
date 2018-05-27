//global sektionsLocalizedData
/**
 * @https://github.com/StackHive/DragDropInterface
 * @https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API
 * @https://html.spec.whatwg.org/multipage/dnd.html#dnd
 * @https://caniuse.com/#feat=dragndrop
 */
// EVENTS

// drag  => handler : ondrag  Fired when an element or text selection is being dragged.
// dragend => handler : ondragend Fired when a drag operation is being ended (for example, by releasing a mouse button or hitting the escape key). (See Finishing a Drag.)
// dragenter => handler : ondragenter Fired when a dragged element or text selection enters a valid drop target. (See Specifying Drop Targets.)
// dragexit  => handler : ondragexit  Fired when an element is no longer the drag operation's immediate selection target.
// dragleave => handler : ondragleave Fired when a dragged element or text selection leaves a valid drop target.
// dragover  => handler : ondragover  Fired when an element or text selection is being dragged over a valid drop target (every few hundred milliseconds).
// dragstart => handler : ondragstart Fired when the user starts dragging an element or text selection. (See Starting a Drag Operation.)
// drop  => handler : ondrop  Fired when an element or text selection is dropped on a valid drop target. (See Performing a Drop.)

// Drop targets can be rendered statically when the preview is rendered or dynamically on dragstart ( sent to preview with 'sek-drag-start')
// Typically, an empty column will be populated with a zek-drop-zone element statically in the preview.
// The other drop zones are rendered dynamically in ::schedulePanelMsgReactions case 'sek-drag-start'
//
// droppable targets are defined server side in sektionsLocalizedData.dropSelectors :
// '.sek-drop-zone' <= to pass the ::dnd_canDrop() test, a droppable target should have this css class
// 'body' <= body will not be eligible for drop, but setting the body as drop zone allows us to fire dragenter / dragover actions, like toggling the "approaching" or "close" css class to real drop zone
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            //-------------------------------------------------------------------------------------------------
            //-- SETUP DnD
            //-------------------------------------------------------------------------------------------------
            //Fired in ::initialize()
            // INSTANTIATE Dnd ZONES IF SUPPORTED BY THE BROWSER
            // + SCHEDULE DROP ZONES RE-INSTANTIATION ON PREVIEW REFRESH
            // + SCHEDULE API REACTION TO *drop event
            // setup $.sekDrop for $( api.previewer.targetWindow().document ).find( '.sektion-wrapper')
            setupDnd : function() {
                  var self = this;
                  // emitted by the module_picker or the section_picker module
                  // @params { type : 'section' || 'module', input_container : input.container }
                  self.bind( 'sek-refresh-dragzones', function( params ) {
                        if ( 'draggable' in document.createElement('span') ) {
                              self.setupNimbleDragZones( params.input_container );//<= module or section picker
                        } else {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  '@missi18n => your browser does not support the drag and drop technology. You might want to customize your site using another browser.',
                                          dismissible: true
                                    } ) );

                                    // Removed if not dismissed after 5 seconds
                                    _.delay( function() {
                                          api.notifications.remove( 'drag-drop-support' );
                                    }, 10000 );
                              });
                        }
                  });

                  // on previewer refresh
                  api.previewer.bind( 'ready', function() {
                        try { self.setupNimbleDropZones();//<= module or section picker
                        } catch( er ) {
                              api.errare( '::setupDnd => error on self.setupNimbleDropZones()', er );
                        }
                        // if the module_picker or the section_picker is currently a registered ui control,
                        // => re-instantiate sekDrop on the new preview frame
                        // the registered() ui levels look like :
                        // [
                        //   { what: "control", id: "__sek___sek_draggable_sections_ui", label: "@missi18n Section Picker", type: "czr_module", module_type: "sek_section_picker_module", …}
                        //   { what: "setting", id: "__sek___sek_draggable_sections_ui", dirty: false, value: "", transport: "postMessage", … }
                        //   { what: "section", id: "__sek___sek_draggable_sections_ui", title: "@missi18n Section Picker", panel: "__sektions__", priority: 30}
                        // ]
                        if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_section_picker_module' } ) ) ) {
                              self.rootPanelFocus();
                        } else if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_module_picker_module' } ) ) ) {
                              self.rootPanelFocus();
                        }
                  });

                  // React to the *-droped event
                  self.reactToDrop();
            },

            //-------------------------------------------------------------------------------------------------
            //--DRAG ZONES SETUP
            //-------------------------------------------------------------------------------------------------
            // fired in ::initialize, on 'sek-refresh-nimbleDragDropZones
            // 'sek-refresh-nimbleDragDropZones' is emitted by the section and the module picker modules with param { type : 'section_picker' || 'module_picker'}
            setupNimbleDragZones : function( $draggableWrapper ) {
                  var self = this;
                  //console.log('instantiate', type );
                  // $(this) is the dragged element
                  var _onStart = function( evt ) {
                        evt.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                        evt.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                        // evt.originalEvent.dataTransfer.effectAllowed = "move";
                        // evt.originalEvent.dataTransfer.dropEffect = "move";
                        // Notify if not supported : https://caniuse.com/#feat=dragndrop
                        try {
                              evt.originalEvent.dataTransfer.setData( 'browserSupport', 'browserSupport' );
                              evt.originalEvent.dataTransfer.setData( 'browserSupport', 'browserSupport' );
                              evt.originalEvent.dataTransfer.clearData( 'browserSupport' );
                        } catch ( er ) {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  '@missi18n => your browser does not support the drag and drop technology. You might want to customize your site using another browser.',
                                          dismissible: true
                                    } ) );

                                    // Removed if not dismissed after 5 seconds
                                    _.delay( function() {
                                          api.notifications.remove( 'drag-drop-support' );
                                    }, 10000 );
                              });
                        }
                        // Set the dragged type property now : module or preset_section
                        self.dnd_draggedType = $(this).data('sek-content-type');

                        api.previewer.send( 'sek-drag-start', { type : self.dnd_draggedType } );//fires the rendering of the dropzones
                        $(evt.currentTarget).addClass('sek-grabbing');
                  };

                  var _onEnd = function( evt ) {
                        api.previewer.send( 'sek-drag-stop' );
                        // make sure that the sek-grabbing class ( -webkit-grabbing ) gets reset on dragEnd
                        $(evt.currentTarget).removeClass('sek-grabbing');
                  };

                  // Schedule
                  $draggableWrapper.find( '[draggable]' ).each( function() {
                        $(this).on( 'dragstart', function( evt ) {
                                    _onStart.call( $(this), evt );
                              })
                              .on( 'dragend', function( evt ) {
                                    _onEnd.call( $(this), evt );
                              });
                  });
            },//setupNimbleZones()












            //-------------------------------------------------------------------------------------------------
            //--DRAG ZONES SETUP
            //-------------------------------------------------------------------------------------------------
            // Scheduled on previewer('ready') each time the previewer is refreshed
            setupNimbleDropZones : function() {
                  var self = this;
                  this.$dropZones = this.dnd_getDropZonesElements();
                  this.preDropElement = $( '<div>', {
                        class: sektionsLocalizedData.preDropElementClass,
                        html : ''//will be set dynamically
                  });
                  if ( this.$dropZones.length < 1 ) {
                        throw new Error( '::setupNimbleDropZones => invalid Dom element');
                  }

                  this.$dropZones.each( function() {
                        var $zone = $(this);
                        // Make sure we don't delegate an event twice for a given element
                        if ( true === $zone.data('zone-droppable-setup') )
                            return;

                        self.enterOverTimer = null;
                        // Delegated to allow reactions on future modules / sections
                        $zone
                              //.on( 'dragenter dragover', sektionsLocalizedData.dropSelectors,  )
                              .on( 'dragenter dragover', sektionsLocalizedData.dropSelectors, function( evt ) {
                                    //console.log( self.enterOverTimer, self.dnd_canDrop( $(this) ) );
                                    if ( _.isNull( self.enterOverTimer ) ) {
                                          self.enterOverTimer = true;
                                          _.delay(function() {
                                                // If the mouse did not move, reset the time and do nothing
                                                // this will prevent a drop zone to "dance", aka expand collapse, when stoping the mouse close to it
                                                if ( self.currentMousePosition && ( ( self.currentMousePosition + '' ) == ( evt.clientY + '' + evt.clientX + '') ) ) {
                                                      self.enterOverTimer = null;
                                                      return;
                                                }
                                                self.currentMousePosition = evt.clientY + '' + evt.clientX + '';
                                                self.dnd_toggleDragApproachClassesToDropZones( evt );
                                          }, 100 );
                                    }

                                    if ( ! self.dnd_canDrop( $(this) ) )
                                      return;

                                    evt.stopPropagation();
                                    self.dnd_OnEnterOver( $(this), evt );
                              })
                              .on( 'dragleave drop', sektionsLocalizedData.dropSelectors, function( evt ) {
                                    switch( evt.type ) {
                                          case 'dragleave' :
                                                if ( ! self.dnd_isOveringDropTarget( $(this), evt  ) ) {
                                                      self.dnd_cleanOnLeaveDrop( $(this), evt );
                                                }
                                          break;
                                          case 'drop' :
                                                // Reset the this.$cachedDropZoneCandidates now
                                                this.$cachedDropZoneCandidates = null;//has been declared on enter over

                                                if ( ! self.dnd_canDrop( $(this) ) )
                                                  return;
                                                evt.preventDefault();//@see https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#drop
                                                self.dnd_onDrop( $(this), evt );
                                                self.dnd_cleanOnLeaveDrop( $(this), evt );
                                                // this event will fire another cleaner
                                                // also sent on dragend
                                                api.previewer.send( 'sek-drag-stop' );
                                          break;
                                    }
                              })
                              .data( 'zone-droppable-setup', true );// flag the zone. Will be removed on 'destroy'

                });//this.dropZones.each()
            },//setupNimbleDropZones()




            //-------------------------------------------------------------------------------------------------
            //-- DnD Helpers
            //-------------------------------------------------------------------------------------------------
            // Fired on 'dragenter dragover'
            // toggles the "approaching" and "close" css classes when conditions are met.
            //
            // Because this function can be potentially heavy if there are a lot of drop zones, this is fired with a timer
            //
            // Note : this is fired before checking if the target is eligible for drop. This way we can calculate an approach, as soon as we start hovering the 'body' ( which is part the drop selector list )
            dnd_toggleDragApproachClassesToDropZones : function( evt ) {
                  var self = this;
                  this.$dropZones = this.$dropZones || this.dnd_getDropZonesElements();
                  this.$cachedDropZoneCandidates = _.isEmpty( this.$cachedDropZoneCandidates ) ? this.$dropZones.find('.sek-drop-zone') : this.$cachedDropZoneCandidates;// Will be reset on drop

                  this.$dropZones.find('.sek-drop-zone').each( function() {
                        var yPos = evt.clientY,
                            xPos = evt.clientX,
                            isApproachingThreshold = 80;
                            isCloseThreshold = 30;

                        var dzoneRect = $(this)[0].getBoundingClientRect(),
                            mouseToBottom = yPos - dzoneRect.bottom,
                            mouseToTop = dzoneRect.top - yPos,
                            mouseToRight = xPos - dzoneRect.right,
                            mouseToLeft = dzoneRect.left - xPos,
                            isCloseVertically = ( mouseToBottom > 0 && mouseToBottom < isCloseThreshold ) || ( mouseToTop > 0 && mouseToTop < isCloseThreshold ),
                            isCloseHorizontally =  ( mouseToRight > 0 && mouseToRight < isCloseThreshold ) || ( mouseToLeft > 0 && mouseToLeft < isCloseThreshold ),
                            isInHorizontally = xPos <= dzoneRect.right && dzoneRect.left <= xPos,
                            isInVertically = yPos <= dzoneRect.top && dzoneRect.bottom <= yPos,
                            isApproachingVertically = ( mouseToBottom > 0 && mouseToBottom < isApproachingThreshold ) || ( mouseToTop > 0 && mouseToTop < isApproachingThreshold ),
                            isApproachingHorizontally = ( mouseToRight > 0 && mouseToRight < isApproachingThreshold ) || ( mouseToLeft > 0 && mouseToLeft < isApproachingThreshold );

                        // var html = "isApproachingHorizontally : " + isApproachingHorizontally + ' | isCloseHorizontally : ' + isCloseHorizontally + ' | isInHorizontally : ' + isInHorizontally;
                        // html += ' | xPos : ' + xPos + ' | zoneRect.right : ' + dzoneRect.right;
                        // html += "isApproachingVertically : " + isApproachingVertically + ' | isCloseVertically : ' + ' | isInVertically : ' + isInVertically;
                        // html += ' | yPos : ' + yPos + ' | zoneRect.top : ' + dzoneRect.top;
                        // $(this).html(html);

                        if ( ( isCloseVertically || isInVertically ) && ( isCloseHorizontally || isInHorizontally ) ) {
                              $(this).addClass( 'sek-drag-is-close');
                              $(this).removeClass( 'sek-drag-is-approaching' );
                        } else if ( ( isApproachingVertically || isInVertically ) && ( isApproachingHorizontally || isInHorizontally ) ) {
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).addClass( 'sek-drag-is-approaching' );
                        } else {
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).removeClass( 'sek-drag-is-approaching' );
                        }
                  });//$('.sek-drop-zones').each()

                  // Reset the timer
                  self.enterOverTimer = null;
            },

            // @return string
            dnd_getPreDropElementContent : function( evt ) {
                  var $target = $( evt.currentTarget ),
                      html,
                      preDropContent;

                  switch( this.dnd_draggedType ) {
                        case 'module' :
                              html = '@missi18n Insert Here';
                              if ( $target.length > 0 ) {
                                  if ( 'between-sections' === $target.data('sek-location') || 'in-empty-location' === $target.data('sek-location') ) {
                                        html = '@missi18n Insert in a new section';
                                  }
                              }
                              preDropContent = '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                        break;

                        case 'preset_section' :
                              html = '@missi18n Insert a new section here';
                              preDropContent = '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                        break;

                        default :
                              api.errare( '::dnd_getPreDropElementContent => invalid content type provided');
                        break;
                  }
                  return preDropContent;
            },

            // Scheduled on previewer('ready') each time the previewer is refreshed
            dnd_getDropZonesElements : function() {
                  return $( api.previewer.targetWindow().document );
            },

            // @return boolean
            // Note : the class "sek-content-preset_section-drop-zone" is dynamically generated in preview::schedulePanelMsgReactions() sek-drag-start case
            dnd_canDrop : function( $dropTarget ) {
                  //console.log("$dropTarget.hasClass('sek-drop-zone') ?", $dropTarget, $dropTarget.hasClass('sek-drop-zone') );
                  var isSectionDropZone = $dropTarget && $dropTarget.length > 0 && $dropTarget.hasClass( 'sek-content-preset_section-drop-zone' );
                  return $dropTarget.hasClass('sek-drop-zone') && ( ( 'preset_section' === this.dnd_draggedType && isSectionDropZone ) || ( 'module' === this.dnd_draggedType && ! isSectionDropZone ) );
            },

            // @return void()
            dnd_OnEnterOver : function( $dropTarget, evt ) {
                  evt.preventDefault();//@see :https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#droptargets
                  // Bail here if we are in the currently drag entered element
                  if ( true !== $dropTarget.data( 'is-drag-entered' ) ) {
                        // Flag now
                        $dropTarget.data( 'is-drag-entered', true );
                        $dropTarget.addClass( 'sek-active-drop-zone' );
                        // Flag the dropEl parent element
                        this.$dropZones.addClass( 'sek-is-dragging' );
                  }

                  try { this.dnd_mayBePrintPreDropElement( $dropTarget, evt ); } catch( er ) {
                        api.errare('Error when trying to insert the preDrop content', er );
                  }
            },

            // @return void()
            dnd_cleanOnLeaveDrop : function( $dropTarget, evt ) {
                  var self = this;
                  this.$dropZones = this.$dropZones || this.dnd_getDropZonesElements();
                  this.preDropElement.remove();
                  this.$dropZones.removeClass( 'sek-is-dragging' );

                  $( sektionsLocalizedData.dropSelectors, this.$dropZones ).each( function() {
                        self.dnd_cleanSingleDropTarget( $(this) );
                  });
            },

            // @return void()
            dnd_cleanSingleDropTarget : function( $dropTarget ) {
                  if ( _.isEmpty( $dropTarget ) || $dropTarget.length < 1 )
                    return;
                  $dropTarget.data( 'is-drag-entered', false );
                  $dropTarget.data( 'preDrop-position', false );
                  $dropTarget.removeClass( 'sek-active-drop-zone' );
                  $dropTarget.find('.sek-drop-zone').removeClass('sek-drag-is-close');
                  $dropTarget.find('.sek-drop-zone').removeClass('sek-drag-is-approaching');
            },


            // @return string after or before
            dnd_getPosition : function( $dropTarget, evt ) {
                  var targetRect = $dropTarget[0].getBoundingClientRect(),
                      targetHeight = targetRect.height;

                  // if the preDrop is already printed, we have to take it into account when calc. the target height
                  if ( 'before' === $dropTarget.data( 'preDrop-position' ) ) {
                        targetHeight = targetHeight + this.preDropElement.outerHeight();
                  } else if ( 'after' === $dropTarget.data( 'preDrop-position' ) ) {
                        targetHeight = targetHeight - this.preDropElement.outerHeight();
                  }

                  return evt.originalEvent.clientY - targetRect.top - ( targetHeight / 2 ) > 0  ? 'after' : 'before';
            },

            // @return void()
            dnd_mayBePrintPreDropElement : function( $dropTarget, evt ) {
                  var self = this,
                      previousPosition = $dropTarget.data( 'preDrop-position' ),
                      newPosition = this.dnd_getPosition( $dropTarget, evt  );

                  if ( previousPosition === newPosition )
                    return;

                  if ( true === self.isPrintingPreDrop ) {
                        return;
                  }

                  self.isPrintingPreDrop = true;

                  // make sure we clean the previous wrapper of the pre drop element
                  this.dnd_cleanSingleDropTarget( this.$currentPreDropTarget );

                  $.when( self.preDropElement.remove() ).done( function(){
                        $dropTarget[ 'before' === newPosition ? 'prepend' : 'append' ]( self.preDropElement )
                              .find( '.' + sektionsLocalizedData.preDropElementClass ).html( self.dnd_getPreDropElementContent( evt ) );
                        $dropTarget.data( 'preDrop-position', newPosition );

                        self.isPrintingPreDrop = false;
                        self.$currentPreDropTarget = $dropTarget;
                  });
            },

            //@return void()
            dnd_isOveringDropTarget : function( $dropTarget, evt ) {
                  var targetRect = $dropTarget[0].getBoundingClientRect(),
                      mouseX = evt.clientX,
                      mouseY = evt.clientY,
                      tLeft = targetRect.left,
                      tRight = targetRect.right,
                      tTop = targetRect.top,
                      tBottom = targetRect.bottom,
                      isXin = mouseX >= tLeft && ( tRight - tLeft ) >= ( mouseX - tLeft),
                      isYin = mouseY >= tTop && ( tBottom - tTop ) >= ( mouseY - tTop);
                  return isXin && isYin;
            },

            //@return void()
            dnd_onDrop: function( $dropTarget, evt ) {
                  evt.stopPropagation();
                  var _position = 'after' === this.dnd_getPosition( $dropTarget, evt ) ? $dropTarget.index() + 1 : $dropTarget.index();
                  // console.log('onDropping params', position, evt );
                  // console.log('onDropping element => ', $dropTarget.data('drop-zone-before-section'), $dropTarget );


                  api.czr_sektions.trigger( 'sek-content-dropped', {
                        drop_target_element : $dropTarget,
                        location : $dropTarget.closest('[data-sek-level="location"]').data('sek-id'),
                        position : _position,
                        before_section : $dropTarget.data('drop-zone-before-section'),
                        after_section : $dropTarget.data('drop-zone-after-section'),
                        content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                        content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" )
                  });
            },



            //-------------------------------------------------------------------------------------------------
            //-- SCHEDULE REACTIONS TO 'sek-content-dropped'
            //-------------------------------------------------------------------------------------------------
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
                        if ( 'in-empty-location' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-empty-location';
                        }
                        if ( 'between-columns' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-new-column';
                        }
                        var focusOnAddedContentEditor;
                        switch( dropCase ) {
                              case 'content-in-column' :
                                    var $closestLevelWrapper = params.drop_target_element.closest('div[data-sek-level]');
                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'No valid level dom element found' );
                                    }
                                    var _level = $closestLevelWrapper.data( 'sek-level' ),
                                        _id = $closestLevelWrapper.data('sek-id');

                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'No valid level id found' );
                                    }
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

                              case 'content-in-empty-location' :
                                    params.is_first_section = true;
                                    params.send_to_preview = false;
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
                  //       before_section : $(this).data('drop-zone-before-section'),
                  //       after_section : $(this).data('drop-zone-after-section'),
                  //       content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                  //       content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" )
                  // });
                  this.bind( 'sek-content-dropped', function( params ) {
                        //console.log('sek-content-dropped', params );
                        try { _do_( params ); } catch( er ) {
                              api.errare( 'error when reactToDrop', er );
                        }
                  });
            }//reactToDrop
      });//$.extend()
})( wp.customize, jQuery );