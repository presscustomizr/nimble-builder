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
                        // Detecting HTML5 Drag And Drop support in javascript
                        // https://stackoverflow.com/questions/2856262/detecting-html5-drag-and-drop-support-in-javascript#2856275
                        if ( 'draggable' in document.createElement('span') ) {
                              self.setupNimbleDragZones( params.input_container );//<= module or section picker
                        } else {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  sektionsLocalizedData.i18n['This browser does not support drag and drop. You might need to update your browser or use another one.'],
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
                        //   { what: "control", id: "__nimble___sek_draggable_sections_ui", label: "Section Picker", type: "czr_module", module_type: "sek_intro_sec_picker_module", …}
                        //   { what: "setting", id: "__nimble___sek_draggable_sections_ui", dirty: false, value: "", transport: "postMessage", … }
                        //   { what: "section", id: "__nimble___sek_draggable_sections_ui", title: "Section Picker", panel: "__sektions__", priority: 30}
                        // ]
                        if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_intro_sec_picker_module' } ) ) ) {
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
                  //api.infoLog('instantiate', type );
                  // $(this) is the dragged element
                  var _onStart = function( evt ) {
                        evt.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                        evt.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                        evt.originalEvent.dataTransfer.setData( "sek-section-type", $(this).data('sek-section-type') );
                        evt.originalEvent.dataTransfer.setData( "sek-is-user-section", $(this).data('sek-is-user-section') );
                        // evt.originalEvent.dataTransfer.effectAllowed = "move";
                        // evt.originalEvent.dataTransfer.dropEffect = "move";
                        // Notify if not supported : https://caniuse.com/#feat=dragndrop
                        try {
                              evt.originalEvent.dataTransfer.setData( 'browserSupport', 'browserSupport' );
                              evt.originalEvent.dataTransfer.clearData( 'browserSupport' );
                        } catch ( er ) {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  sektionsLocalizedData.i18n['This browser does not support drag and drop. You might need to update your browser or use another one.'],
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
                        $(this).addClass('sek-dragged');
                        $('body').addClass('sek-dragging');
                        api.previewer.send( 'sek-drag-start', { type : self.dnd_draggedType } );//fires the rendering of the dropzones
                  };

                  var _onEnd = function( evt ) {
                        $('body').removeClass('sek-dragging');
                        $(this).removeClass('sek-dragged');
                        api.previewer.send( 'sek-drag-stop' );
                  };

                  // Schedule
                  $draggableWrapper.find( '[draggable]' ).each( function() {
                        $(this).on( 'dragstart', function( evt ) { _onStart.call( $(this), evt ); })
                              .on( 'dragend', function( evt ) { _onEnd.call( $(this), evt ); });
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
                                    //api.infoLog( self.enterOverTimer, self.dnd_canDrop( $(this) ) );
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
                            APPROACHING_DIST = 120,
                            CLOSE_DIST = 80,
                            VERY_CLOSE_DIST = 60;

                        var dzoneRect = $(this)[0].getBoundingClientRect(),
                            mouseToYCenter = Math.abs( yPos - ( dzoneRect.bottom - ( dzoneRect.bottom - dzoneRect.top )/2 ) ),
                            mouseToTop = Math.abs( dzoneRect.top - yPos ),
                            mouseToXCenter = Math.abs( xPos - ( dzoneRect.right - ( dzoneRect.right - dzoneRect.left )/2 ) ),
                            mouseToRight = xPos - dzoneRect.right,
                            mouseToLeft = dzoneRect.left - xPos,
                            isVeryCloseVertically = mouseToYCenter < VERY_CLOSE_DIST,
                            isVeryCloseHorizontally =  mouseToXCenter < VERY_CLOSE_DIST,
                            isCloseVertically = mouseToYCenter < CLOSE_DIST,
                            isCloseHorizontally =  mouseToXCenter < CLOSE_DIST,
                            isApproachingVertically = mouseToYCenter < APPROACHING_DIST,
                            isApproachingHorizontally = mouseToXCenter < APPROACHING_DIST,

                            isInHorizontally = xPos <= dzoneRect.right && dzoneRect.left <= xPos,
                            isInVertically = yPos >= dzoneRect.top && dzoneRect.bottom >= yPos;

                        // var html = "isApproachingHorizontally : " + isApproachingHorizontally + ' | isCloseHorizontally : ' + isCloseHorizontally + ' | isInHorizontally : ' + isInHorizontally;
                        // html += ' | xPos : ' + xPos + ' | zoneRect.right : ' + dzoneRect.right;
                        // html += "isApproachingVertically : " + isApproachingVertically + ' | isCloseVertically : ' + isCloseVertically + ' | isInVertically : ' + isInVertically;
                        // html += ' | yPos : ' + yPos + ' | zoneRect.top : ' + dzoneRect.top;
                        // $(this).html( '<span style="font-size:10px">' + html + '</span>');

                        // var html = '';
                        // html += ' | mouseToBottom : ' + mouseToBottom + ' | mouseToTop : ' + mouseToTop;
                        // html += "isApproachingVertically : " + isApproachingVertically + ' | isCloseVertically : ' + isCloseVertically + ' | isInVertically : ' + isInVertically;
                        // $(this).html( '<span style="font-size:12px">' + html + '</span>');

                        // var html = ' | xPos : ' + xPos + ' | zoneRect.right : ' + dzoneRect.right + ' | zoneRect.left : ' + dzoneRect.left;
                        // html += "mouseToYCenter : " + mouseToYCenter + ' | mouseToXCenter : ' + mouseToXCenter;
                        // html += ' | yPos : ' + yPos + ' | zoneRect.top : ' + dzoneRect.top + ' | zoneRect.bottom : ' + dzoneRect.bottom;
                        // $(this).html( '<span style="font-size:10px">' + html + '</span>');

                        //var html = '';

                        if ( isInVertically && isInHorizontally ) {
                              $(this).removeClass( 'sek-drag-is-approaching');
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).addClass( 'sek-drag-is-in');
                              //html += 'is IN';
                        } else if ( ( isVeryCloseVertically || isInVertically ) && ( isVeryCloseHorizontally || isInHorizontally ) ) {
                              $(this).removeClass( 'sek-drag-is-approaching');
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).addClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-in');
                              //html += 'is very close';
                        } else if ( ( isCloseVertically || isInVertically ) && ( isCloseHorizontally || isInHorizontally ) ) {
                              $(this).removeClass( 'sek-drag-is-approaching');
                              $(this).addClass( 'sek-drag-is-close' );
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-in');
                              //html += 'is close';
                        } else if ( ( isApproachingVertically || isInVertically ) && ( isApproachingHorizontally || isInHorizontally ) ) {
                              $(this).addClass( 'sek-drag-is-approaching');
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-in');
                              //html += 'is approaching';
                        } else {
                              $(this).removeClass( 'sek-drag-is-approaching');
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-in');
                        }


                        //$(this).html( '<span style="font-size:10px">' + html + '</span>');
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
                              html = sektionsLocalizedData.i18n['Insert here'];
                              if ( $target.length > 0 ) {
                                  if ( 'between-sections' === $target.data('sek-location') || 'in-empty-location' === $target.data('sek-location') ) {
                                        html = sektionsLocalizedData.i18n['Insert in a new section'];
                                  }
                              }
                              preDropContent = '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                        break;

                        case 'preset_section' :
                              html = sektionsLocalizedData.i18n['Insert a new section here'];
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
                  //api.infoLog("$dropTarget.hasClass('sek-drop-zone') ?", $dropTarget, $dropTarget.hasClass('sek-drop-zone') );
                  var isSectionDropZone = $dropTarget && $dropTarget.length > 0 && $dropTarget.hasClass( 'sek-content-preset_section-drop-zone' ),
                      sectionHasNoModule = $dropTarget && $dropTarget.length > 0 && $dropTarget.hasClass( 'sek-module-drop-zone-for-first-module' );
                  return $dropTarget.hasClass('sek-drop-zone') && ( ( 'preset_section' === this.dnd_draggedType && isSectionDropZone ) || ( 'module' === this.dnd_draggedType && ! isSectionDropZone ) || ( 'preset_section' === this.dnd_draggedType && sectionHasNoModule ) );
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

                  $dropTarget.removeClass('sek-feed-me-seymore');
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
                  var inNewSection = 'between-sections' === $dropTarget.data('sek-location') || 'in-empty-location' === $dropTarget.data('sek-location');
                  $.when( self.preDropElement.remove() ).done( function(){
                        $dropTarget[ 'before' === newPosition ? 'prepend' : 'append' ]( self.preDropElement )
                              .find( '.' + sektionsLocalizedData.preDropElementClass ).html( self.dnd_getPreDropElementContent( evt ) );
                        // Flag the preDrop element with class to apply a specific style if inserted in a new sektion of in a column
                        $dropTarget.find( '.' + sektionsLocalizedData.preDropElementClass ).toggleClass('in-new-sektion', inNewSection );
                        $dropTarget.data( 'preDrop-position', newPosition );

                        $dropTarget.addClass('sek-feed-me-seymore');

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
                  // api.infoLog('onDropping params', position, evt );
                  // api.infoLog('onDropping element => ', $dropTarget.data('drop-zone-before-section'), $dropTarget );
                  api.czr_sektions.trigger( 'sek-content-dropped', {
                        drop_target_element : $dropTarget,
                        location : $dropTarget.closest('[data-sek-level="location"]').data('sek-id'),
                        // when inserted between modules
                        before_module : $dropTarget.data('drop-zone-before-module-or-nested-section'),
                        after_module : $dropTarget.data('drop-zone-after-module-or-nested-section'),

                        // When inserted between sections
                        before_section : $dropTarget.data('drop-zone-before-section'),
                        after_section : $dropTarget.data('drop-zone-after-section'),

                        content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                        content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" ),

                        section_type : evt.originalEvent.dataTransfer.getData( "sek-section-type" ),
                        // Saved sections
                        is_user_section : "true" === evt.originalEvent.dataTransfer.getData( "sek-is-user-section" )
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
                  //    before_section : $(this).data('drop-zone-before-section'),
                  //    after_section : $(this).data('drop-zone-after-section'),
                  //    content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                  //    content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" ),
                  //    section_type : evt.originalEvent.dataTransfer.getData( "sek-section-type" ),//<= content, header, footer
                  //    is_user_section : true === evt.originalEvent.dataTransfer.getData( "sek-is-user-section" ),
                  // }
                  var _do_ = function( params ) {
                        if ( ! _.isObject( params ) ) {
                              throw new Error( 'Invalid params provided' );
                        }
                        if ( params.drop_target_element.length < 1 ) {
                              throw new Error( 'Invalid drop_target_element' );
                        }

                        var $dropTarget = params.drop_target_element,
                            dropCase = 'content-in-column';

                        // If the data('sek-location') is available, let's use it
                        switch( $dropTarget.data('sek-location') ) {
                              case 'between-sections' :
                                    dropCase = 'content-in-a-section-to-create';
                              break;
                              case 'in-empty-location' :
                                    params.is_first_section = true;
                                    params.send_to_preview = false;
                                    dropCase = 'content-in-empty-location';
                              break;
                              case 'between-columns' :
                                    dropCase = 'content-in-new-column';
                              break;
                        }

                        // case of a preset_section content_type being added to an existing but empty section
                        if ( 'preset_section' === params.content_type ) {
                              if ( $dropTarget.hasClass( 'sek-module-drop-zone-for-first-module' ) ) {
                                    var $parentSektion = $dropTarget.closest('div[data-sek-level="section"]');
                                    //calculate the number of column in this section, excluding the columns inside nested sections if any
                                    var colNumber = $parentSektion.find('.sek-sektion-inner').first().children( '[data-sek-level="column"]' ).length;
                                    // if the parent section has more than 1 column, we will need to inject the preset_section inside a nested_section
                                    if ( colNumber > 1 ) {
                                          dropCase = 'preset-section-in-a-nested-section-to-create';
                                          params.is_nested = true;
                                          params.in_column = $dropTarget.closest('[data-sek-level="column"]').data('sek-id');
                                          params.in_sektion = $parentSektion.data('sek-id');
                                          //params.after_section = params.sektion_to_replace;
                                    } else {
                                          params.sektion_to_replace = $parentSektion.data('sek-id');
                                          params.after_section = params.sektion_to_replace;
                                          // if the sektion to replace is nested, we will append the new sektion to the parent column of the nested section
                                          params.in_column = $parentSektion.closest('[data-sek-level="column"]').data('sek-id');
                                          dropCase = 'content-in-a-section-to-replace';
                                    }
                              } else {
                                    if ( 'between-sections' === $dropTarget.data('sek-location') ) {
                                          dropCase = 'content-in-a-section-to-create';
                                    }
                              }



                        }

                        var focusOnAddedContentEditor;
                        switch( dropCase ) {
                              case 'content-in-column' :
                                    var $closestLevelWrapper = $dropTarget.closest('div[data-sek-level]');
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
                                          in_column : $dropTarget.closest('div[data-sek-level="column"]').data( 'sek-id'),
                                          in_sektion : $dropTarget.closest('div[data-sek-level="section"]').data( 'sek-id'),

                                          before_module : params.before_module,
                                          after_module : params.after_module,

                                          content_type : params.content_type,
                                          content_id : params.content_id
                                    });
                              break;

                              case 'content-in-a-section-to-create' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;
                              // this case fixes https://github.com/presscustomizr/nimble-builder/issues/139
                              case 'content-in-a-section-to-replace' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;
                              case 'preset-section-in-a-nested-section-to-create' :
                                    api.previewer.trigger( 'sek-add-preset-section-in-new-nested-sektion', params );
                              break;
                              case 'content-in-empty-location' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;

                              default :
                                    api.errare( 'sek control panel => ::reactToDrop => invalid drop case : ' + dropCase );
                              break;
                              // case 'content-in-new-column' :

                              // break;
                        }
                  };

                  // @see module picker or section picker modules
                  // api.czr_sektions.trigger( 'sek-content-dropped', {
                  //       drop_target_element : $(this),
                  //       position : _position,
                  //       before_section : $(this).data('drop-zone-before-section'),
                  //       after_section : $(this).data('drop-zone-after-section'),
                  //       content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                  //       content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" ),
                  //       is_user_section : true === evt.originalEvent.dataTransfer.getData( "sek-is-user-section" ),
                  // });
                  this.bind( 'sek-content-dropped', function( params ) {
                        //api.infoLog('sek-content-dropped', params );
                        try { _do_( params ); } catch( er ) {
                              api.errare( 'error when reactToDrop', er );
                        }
                  });
            }//reactToDrop
      });//$.extend()
})( wp.customize, jQuery );