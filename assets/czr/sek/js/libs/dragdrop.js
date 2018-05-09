/**
 * @https://github.com/StackHive/DragDropInterface
 */
;(function( $ ) {

  var hasFullDataTransferSupport = function( evt ) {
    try {
      evt.originalEvent.dataTransfer.setData( 'test', 'test' );

      evt.originalEvent.dataTransfer.clearData( 'test' );

      return true;
    } catch ( e ) {
      return false;
    }
  };

  var DragConstruct = function( userSettings ) {
    var self = this,
      settings = {},
      elementsCache = {},
      defaultSettings = {
        element: '',
        groups: null,
        onDragStart: null,
        onDragEnd: null
      };

    var initSettings = function() {
      $.extend( true, settings, defaultSettings, userSettings );
    };

    var initElementsCache = function() {
      elementsCache.$element = $( settings.element );
    };

    var buildElements = function() {
      elementsCache.$element.attr( 'draggable', true );
    };

    var onDragEnd = function( evt ) {
      if ( $.isFunction( settings.onDragEnd ) ) {
        settings.onDragEnd.call( elementsCache.$element, evt, self );
      }
    };

    var onDragStart = function( evt ) {
      var groups = settings.groups || [],
        dataContainer = {
          groups: groups
        };

      if ( hasFullDataTransferSupport( evt ) ) {
        evt.originalEvent.dataTransfer.setData( JSON.stringify( dataContainer ), true );
      }

      if ( $.isFunction( settings.onDragStart ) ) {
        settings.onDragStart.call( elementsCache.$element, evt, self );
      }
    };

    var attachEvents = function() {
      elementsCache.$element
        .on( 'dragstart', onDragStart )
        .on( 'dragend', onDragEnd );
    };

    var init = function() {
      initSettings();

      initElementsCache();

      buildElements();

      attachEvents();
    };

    this.destroy = function() {
      elementsCache.$element.off( 'dragstart', onDragStart );

      elementsCache.$element.removeAttr( 'draggable' );
    };

    init();
  };
























  var DropConstruct = function( userSettings ) {
    var self = this,
      settings = {},
      elementsCache = {},
      currentElement,
      currentSide,
      defaultSettings = {
        element: '',
        items: '>',
        horizontalSensitivity: '10%',
        axis: [ 'vertical', 'horizontal' ],
        placeholder: true,
        currentElementClass: 'sekdnd-current-element',
        placeholderClass: 'sekdnd-placeholder',
        placeholderContent : '',
        hasDraggingOnChildClass: 'sekdnd-has-dragging-on-child',
        groups: null,
        isDroppingAllowed: null,
        onDragEnter: null,
        onDragging: null,
        onDropping: null,
        onDragLeave: null
      };

    var initSettings = function() {
      $.extend( settings, defaultSettings, userSettings );
    };

    var initElementsCache = function() {
      elementsCache.$element = $( settings.element );

      elementsCache.$placeholder = $( '<div>', {
          class: settings.placeholderClass
      });
    };

    var hasHorizontalDetection = function() {
      return -1 !== settings.axis.indexOf( 'horizontal' );
    };

    var hasVerticalDetection = function() {
      return -1 !== settings.axis.indexOf( 'vertical' );
    };

    var checkHorizontal = function( offsetX, elementWidth ) {
      var isPercentValue,
        sensitivity;

      if ( ! hasHorizontalDetection() ) {
        return false;
      }

      if ( ! hasVerticalDetection() ) {
        return offsetX > elementWidth / 2 ? 'right' : 'left';
      }

      sensitivity = settings.horizontalSensitivity.match( /\d+/ );

      if ( ! sensitivity ) {
        return false;
      }

      sensitivity = sensitivity[0];

      isPercentValue = /%$/.test( settings.horizontalSensitivity );

      if ( isPercentValue ) {
        sensitivity = elementWidth / sensitivity;
      }

      if ( offsetX > elementWidth - sensitivity ) {
        return 'right';
      } else if ( offsetX < sensitivity ) {
        return 'left';
      }

      return false;
    };

    var setSide = function( evt ) {
      var $element = $( currentElement ),
        elementHeight = $element.outerHeight() - elementsCache.$placeholder.outerHeight(),
        elementWidth = $element.outerWidth();

      evt = evt.originalEvent;

      if ( currentSide = checkHorizontal( evt.offsetX, elementWidth ) ) {
        return;
      }

      if ( ! hasVerticalDetection() ) {
        currentSide = null;

        return;
      }

      var elementPosition = currentElement.getBoundingClientRect();

      currentSide = evt.clientY > elementPosition.top + elementHeight / 2 ? 'bottom' : 'top';
    };

    var insertPlaceholder = function( evt ) {
      if ( ! settings.placeholder ) {
        return;
      }

      var insertMethod = 'top' === currentSide ? 'prependTo' : 'appendTo';

      try { elementsCache.$placeholder[ insertMethod ]( currentElement ).html( settings.placeholderContent( evt ) ); } catch( er ) {
            api.errare('Error when trying to insert the placeholder content', er );
      }
    };

    var isDroppingAllowed = function( evt ) {
      var dataTransferTypes,
        draggableGroups,
        isGroupMatch,
        isDroppingAllowed;

      if ( settings.groups && hasFullDataTransferSupport( evt ) ) {
        dataTransferTypes = evt.originalEvent.dataTransfer.types;

        isGroupMatch = false;

        dataTransferTypes = Array.prototype.slice.apply( dataTransferTypes ); // Convert to array, since Firefox hold it as DOMStringList

        dataTransferTypes.forEach( function( type ) {
          try {
            draggableGroups = JSON.parse( type );

            if ( ! draggableGroups.groups.slice ) {
              return;
            }

            settings.groups.forEach( function( groupName ) {

              if ( -1 !== draggableGroups.groups.indexOf( groupName ) ) {
                isGroupMatch = true;

                return false; // stops the forEach from extra loops
              }
            } );
          } catch ( e ) {
          }
        } );

        if ( ! isGroupMatch ) {
          return false;
        }
      }

      if ( $.isFunction( settings.isDroppingAllowed ) ) {

        isDroppingAllowed = settings.isDroppingAllowed.call( currentElement, currentSide, evt, self );

        if ( ! isDroppingAllowed ) {
          return false;
        }
      }

      return true;
    };

    var onDragEnter = function( evt ) {
      evt.stopPropagation();

      if ( currentElement ) {
        return;
      }

      currentElement = this;

      elementsCache.$element.parents().each( function() {
        var droppableInstance = $( this ).data( 'sekDrop' );

        if ( ! droppableInstance ) {
          return;
        }

        droppableInstance.doDragLeave();
      } );

      setSide( evt );

      if ( ! isDroppingAllowed( evt ) ) {
        return;
      }

      insertPlaceholder( evt );

      elementsCache.$element.addClass( settings.hasDraggingOnChildClass );

      $( currentElement ).addClass( settings.currentElementClass );

      if ( $.isFunction( settings.onDragEnter ) ) {
        settings.onDragEnter.call( currentElement, currentSide, evt, self );
      }
    };

    var onDragOver = function( evt ) {
      evt.stopPropagation();

      if ( ! currentElement ) {
        onDragEnter.call( this, evt );
      }

      var oldSide = currentSide;

      setSide( evt );

      if ( ! isDroppingAllowed( evt ) ) {
        return;
      }

      evt.preventDefault();

      if ( oldSide !== currentSide ) {
        insertPlaceholder( evt );
      }

      if ( $.isFunction( settings.onDragging ) ) {
        settings.onDragging.call( this, currentSide, evt, self );
      }
    };

    var onDragLeave = function( evt ) {
      var elementPosition = this.getBoundingClientRect();

      if ( 'dragleave' === evt.type && ! (
        evt.clientX < elementPosition.left ||
        evt.clientX >= elementPosition.right ||
        evt.clientY < elementPosition.top ||
        evt.clientY >= elementPosition.bottom
      ) ) {
        return;
      }

      $( currentElement ).removeClass( settings.currentElementClass );

      self.doDragLeave();
    };

    var onDrop = function( evt ) {
      setSide( evt );

      if ( ! isDroppingAllowed( evt ) ) {
        return;
      }

      evt.preventDefault();

      if ( $.isFunction( settings.onDropping ) ) {
        settings.onDropping.call( this, currentSide, evt, self );
      }
    };

    var attachEvents = function() {
      elementsCache.$element
        .on( 'dragenter', settings.items, onDragEnter )
        .on( 'dragover', settings.items, onDragOver )
        .on( 'drop', settings.items, onDrop )
        .on( 'dragleave drop', settings.items, onDragLeave );
    };

    var init = function() {
      initSettings();

      initElementsCache();

      attachEvents();
    };

    this.doDragLeave = function() {
      if ( settings.placeholder ) {
        elementsCache.$placeholder.remove();
      }

      elementsCache.$element.removeClass( settings.hasDraggingOnChildClass );

      if ( $.isFunction( settings.onDragLeave ) ) {
        settings.onDragLeave.call( currentElement, evt, self );
      }

      currentElement = currentSide = null;
    };

    this.destroy = function() {
      elementsCache.$element
        .off( 'dragenter', settings.items, onDragEnter )
        .off( 'dragover', settings.items, onDragOver )
        .off( 'drop', settings.items, onDrop )
        .off( 'dragleave drop', settings.items, onDragLeave );
    };

    init();
  };



  // Instantiate
  var plugins = {
    sekDrag: DragConstruct,
    sekDrop: DropConstruct
  };

  $.each( plugins, function( pluginName, Plugin ) {
    $.fn[ pluginName ] = function( options ) {
      options = options || {};

      this.each( function() {
        var instance = $.data( this, pluginName ),
          hasInstance = instance instanceof Plugin;

        if ( hasInstance ) {

          if ( 'destroy' === options ) {

            instance.destroy();

            $.removeData( this, pluginName );
          }

          return;
        }

        options.element = this;

        $.data( this, pluginName, new Plugin( options ) );
      } );

      return this;
    };
  } );
})( jQuery );
