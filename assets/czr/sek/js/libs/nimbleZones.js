/**
 * @https://github.com/StackHive/DragDropInterface
 * @https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API
 * @https://html.spec.whatwg.org/multipage/dnd.html#dnd
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

;(function( $, _ ) {
    var Plugin = function( element, options ) {

          var self = this;
          this.element = element;
          this.defaults = {
                dropZones:'',
                onStart: function() { api.errare( 'drag and drop => missing onStart callback.'); },
                onEnd: function() { api.errare( 'drag and drop => missing onEnd callback.'); },
                onDrop: function() { api.errare( 'drag and drop => missing missing onDrop callback.'); },
                dropSelectors: '',
                activeDropZoneClass: 'sek-active-drop-zone',
                placeholderClass: '',
                placeholderContent : function() { api.errare( 'drag and drop => missing placeholderContent callback.'); },
          };

          this.options = $.extend( {}, self.defaults, options);
          this.dragEl = $( element );
          this.dropZones = $( options.dropZones );
          this.placeholderEl = $( '<div>', {
                class: options.placeholderClass
          });
          this.draggingActiveClass = 'sek-is-dragging';
          this.initialize( this.element, options );
    };//Plugin()

    $.extend( Plugin.prototype, {
          initialize : function( element, options ) {
                var self = this;
                // EVENTS
                if ( true !== this.dragEl.data('nimble-draggable') ) {
                      this.dragEl
                            .on( 'dragstart', function( evt ) {
                                  self.options.onStart.call( self.dragEl, evt, self );
                            })
                            .on( 'dragend', function( evt ) {
                                  self.options.onEnd.call( self.dragEl, evt, self );
                            })
                            .attr('data-nimble-draggable', true );
                }

                if ( true !== this.dropZones.data('nimble-droppable') ) {
                      this.dropZones
                            .on( 'dragenter dragover', self.options.dropSelectors, function( evt ) {
                                  evt.stopPropagation();
                                  switch( evt.type ) {
                                        case 'dragover' :
                                              evt.preventDefault();//@see :https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#droptargets
                                              self.onEnterOver( $(this), evt );
                                        break;
                                        case 'dragenter' :
                                              self.onEnterOver( $(this), evt );
                                        break;
                                  }

                            })
                            .on( 'dragleave drop', self.options.dropSelectors, function( evt ) {
                                  switch( evt.type ) {
                                        case 'dragleave' :
                                              if ( ! self.isOveringDropTarget( $(this), evt  ) ) {
                                                    self.onLeaveDrop( $(this), evt );
                                              }
                                        break;
                                        case 'drop' :
                                              evt.preventDefault();//@see https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#drop
                                              self.options.onDrop.call( $(this), self.getPosition( $(this), evt ) , evt, self );
                                              self.onLeaveDrop( $(this), evt );
                                        break;
                                  }

                            })
                            .attr( 'data-nimble-droppable', true );
                }
          },//initialize

          // @return void()
          onEnterOver : function( $dropTarget, evt ) {
                evt.stopPropagation();
                // Bail here if we are in the currently drag entered element
                if ( true === $dropTarget.data( 'is-drag-entered' ) )
                  return;

                // Flag now
                $dropTarget.data( 'is-drag-entered', true );
                $dropTarget.addClass( this.options.activeDropZoneClass );

                // Flag the dropEl parent element
                this.dropZones.addClass( this.draggingActiveClass );

                try { this.printPlaceholder( $dropTarget, evt ); } catch( er ) {
                      api.errare('Error when trying to insert the placeholder content', er );
                }
          },

          // @return void()
          onLeaveDrop : function( $dropTarget, evt ) {
                // Clean up
                if ( $dropTarget && $dropTarget.length > 0 ) {
                      $dropTarget.removeClass( this.options.activeDropZoneClass );
                }
                this.placeholderEl.remove();
                this.dropZones.removeClass( this.draggingActiveClass );
                $( this.options.dropSelectors, this.dropZones ).each( function() {
                      $(this).data( 'is-drag-entered', false );
                });
          },


          // @return string after or before
          getPosition : function( $dropTarget, evt ) {
                evt = evt.originalEvent;
                var _height = $dropTarget.outerHeight() - this.placeholderEl.outerHeight(),
                    _position = $dropTarget[0].getBoundingClientRect();
                return evt.clientY > ( _position.top + _height / 2 ) ? 'after' : 'before';
          },

          // @return voi()
          printPlaceholder : function( $dropTarget, evt ) {
                $dropTarget[ 'before' === this.getPosition( $dropTarget, evt ) ? 'prepend' : 'append' ]( this.placeholderEl );
                $dropTarget.find( '.' + this.options.placeholderClass ).html( this.options.placeholderContent( evt ) );
          },

          //@return void()
          isOveringDropTarget : function( $dropTarget, evt ) {
                var targetPos = $dropTarget[0].getBoundingClientRect(),
                    mouseX = evt.clientX,
                    mouseY = evt.clientY,
                    tLeft = targetPos.left,
                    tRight = targetPos.right,
                    tTop = targetPos.top,
                    tBottom = targetPos.bottom,
                    isXin = mouseX >= tLeft && ( tRight - tLeft ) >= ( mouseX - tLeft),
                    isYin = mouseY >= tTop && ( tBottom - tTop ) >= ( mouseY - tTop);
                return isXin && isYin;
          },

          // @return void()
          destroy : function() {
                this.dragEl
                      .off( 'dragstart' )
                      .removeAttr( 'data-nimble-draggable' );

                this.dropZones
                      .off( 'dragenter dragover drop dragleave' )
                      .removeAttr( 'data-nimble-droppable' );
          }

    });//Constructor

    // Prevents against multiple instantiations
    var pluginName = 'nimbleZones';
    $.fn[pluginName] = function ( options ) {
          return this.each(function () {
                var plugInst = $.data(this, 'plugin_' + pluginName );
                if ( !plugInst ) {
                      $.data( this, 'plugin_' + pluginName, new Plugin( this, options ) );
                } else {
                      if ( 'destroy' === options ) {
                            plugInst.destroy();
                            $.removeData( this, 'plugin_' + pluginName );
                      }
                }
          });
    };

})( jQuery, _ );//(function( $ ) {}