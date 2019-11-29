//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired on Dom Ready, in ::initialize()
            setupResizable : function() {
                  var self = this;
                  $('.sektion-wrapper').find( 'div[data-sek-level="section"]' ).each( function() {
                        self.maybeMakeColumnResizableInSektion.call( this );
                  });
                  // Delegate instantiation when a level markup is refreshed
                  // Let the event bubble up to the location, and then visit all children section to maybe re-instantiate resizable
                  // @fixes https://github.com/presscustomizr/nimble-builder/issues/165
                  self.cachedElements.$body.on(
                        'sek-level-refreshed sek-modules-refreshed sek-columns-refreshed sek-section-added sek-location-refreshed',
                        '[data-sek-level="location"]',
                        function( evt ) {
                              $(this).find('[data-sek-level="section"]').each( function() {
                                    self.maybeMakeColumnResizableInSektion.call( this );
                              });
                        }
                  );
                  return this;
            },//setupResizable()

            // this is the parent section jQuery object
            maybeMakeColumnResizableInSektion : function() {
                  var self = this,
                      $parentSektion,
                      parentSektionId,
                      parentSektionWidth,
                      $resizedColumn,
                      resizedColumnWidthInPercent,
                      //calculate the number of column in this section, excluding the columns inside nested sections if any
                      colNumber,
                      $sisterColumn,
                      isLastColumn;

                  // We won't fire resizable for single column sektions
                  var $directColumnChildren = $(this).find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' );
                  if ( 2 > $directColumnChildren.length )
                    return;

                  var $lastCol = $(this).find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).last();

                  $directColumnChildren.not($lastCol).each( function() {
                        $(this).resizable({
                                // handles: { 'e': '.ui-resizable-e', 'w': '.ui-resizable-w' },
                                resize : function( event, ui ) {
                                      $('.sektion-wrapper').data('sek-resizing-columns', true );
                                },
                                start : function( event, ui ) {
                                      $parentSektion = ui.element.closest('div[data-sek-level="section"]');
                                      parentSektionId = $parentSektion.data('sek-id');
                                      parentSektionWidth = $parentSektion.find('.sek-sektion-inner')[0].getBoundingClientRect().width;
                                      //calculate the number of column in this section, excluding the columns inside nested sections if any
                                      colNumber = $parentSektion.find('.sek-sektion-inner').first().children( 'div[data-sek-level="column"]' ).length;

                                      // Resizable should not have been instantiated anyway
                                      if ( 2 > colNumber )
                                        return;

                                      $resizedColumn = ui.element.closest('div[data-sek-level="column"]');
                                      if ( 1 > $resizedColumn.length ) {
                                          throw new Error( 'ERROR => resizable => No valid level dom element found' );
                                      }

                                      isLastColumn = $resizedColumn.index() + 1 == colNumber;

                                      // Assomption : RTL user. LTR should be implemented also.
                                      // If parent section has at least 2 columns, the sister column is the one on the right if not in last position. On the left if last.
                                      $sisterColumn = isLastColumn ? $resizedColumn.prev() : $resizedColumn.next();

                                      // Implement a global state value()
                                      $('.sektion-wrapper').data('sek-resizing-columns', true );

                                      // auto set to false after a moment.
                                      _.delay( function() {
                                            $('.sektion-wrapper').data('sek-resizing-columns', false );
                                      }, 3000 );
                                },
                                stop : function( event, ui ) {
                                      // console.log('ON RESIZE STOP', event, ui, $resizedColumn );
                                      // console.log('colNumber', colNumber, '[data-sek-id="' + parentSektionId +'"] > .sek-sektion-inner' );
                                      // Skip the case when there's only one column
                                      // Resizable should not have been instantiated anyway
                                      if ( 2 > colNumber )
                                        return;

                                      if ( 1 > $resizedColumn.length ) {
                                          throw new Error( 'ERROR => resizable => No valid level dom element found' );
                                      }

                                      // Reset the automatic inline style
                                      $resizedColumn.css({
                                            width : '',
                                            height: ''
                                      });

                                      resizedColumnWidthInPercent = ( ( parseFloat( ui.size.width ) / parseFloat( parentSektionWidth ) ) * 100 ).toFixed(3);

                                      api.preview.send( 'sek-resize-columns', {
                                            action : 'sek-resize-columns',
                                            level : $resizedColumn.data( 'sek-level' ),
                                            in_sektion : $parentSektion.data('sek-id'),
                                            id : $resizedColumn.data('sek-id'),

                                            resized_column : $resizedColumn.data('sek-id'),
                                            sister_column : $sisterColumn.data('sek-id'),

                                            resizedColumnWidthInPercent : resizedColumnWidthInPercent,

                                            col_number : colNumber
                                      });

                                      $('.sektion-wrapper').data('sek-resizing-columns', false );
                                },
                                helper: "ui-resizable-helper",
                                handles :'e'
                        });//$(this).resizable({})

                        // Add a resizable icon in the handle
                        // revealed on section hovering @see sek-preview.css
                        var $column = $(this);
                        _.delay( function() {
                              var $resizableHandle = $column.find('.ui-resizable-handle');
                              if ( $resizableHandle.find('.fa-arrows-alt-h').length < 1 ) {
                                    $column.find('.ui-resizable-handle').append('<i class="fas fa-arrows-alt-h"></i>');
                              }
                        }, 500 );

                  });//$directColumnChildren.each()
            }

      });//$.extend()
})( wp.customize, jQuery, _ );
