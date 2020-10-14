// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  ACCORDION MODULE
/* ------------------------------------------------------------------------- */
(function(w, d){
      var callbackFunc = function() {
          jQuery( function($){
              $( 'body' ).on( 'click sek-expand-accord-item', '.sek-accord-item > .sek-accord-title', function( evt ) {
                  //evt.preventDefault();
                  //evt.stopPropagation();
                  var $item = $(this).closest( '.sek-accord-item'),
                      $accordion = $(this).closest( '.sek-accord-wrapper');

                  // Note : cast the boolean to a string by adding +''
                  if ( "true" == $accordion.data('sek-one-expanded')+'' ) {
                      $accordion.find('.sek-accord-item').not( $item ).each( function() {
                            var $current_item = $(this);
                            $current_item.find('.sek-accord-content').stop( true, true ).slideUp( {
                                  duration : 200,
                                  start : function() {
                                        // If already expanded, make sure inline style display:block is set
                                        // otherwise, the CSS style display:none will apply first, making the transition brutal.
                                        if ( "true" == $current_item.attr('data-sek-expanded')+'' ) {
                                              $current_item.find('.sek-accord-content').css('display', 'block');
                                        }
                                        $current_item.attr('data-sek-expanded', "false" );
                                  }
                            });
                      });
                  }
                  if ( 'sek-expand-accord-item' === evt.type && "true" == $item.attr('data-sek-expanded')+'' ) {
                      return;
                  } else {
                      $item.find('.sek-accord-content').stop( true, true ).slideToggle({
                            duration : 200,
                            start : function() {
                                  // If already expanded, make sure inline style display:block is set
                                  // otherwise, the CSS style display:none will apply first, making the transition brutal.
                                  if ( "true" == $item.attr('data-sek-expanded')+'' ) {
                                        $item.find('.sek-accord-content').css('display', 'block');
                                  }
                                  $item.attr('data-sek-expanded', "false" == $item.attr('data-sek-expanded')+'' ? "true" : "false" );
                                  $item.trigger( "true" == $item.attr('data-sek-expanded') ? 'sek-accordion-expanded' : 'sek-accordion-collapsed' );
                            }
                      });
                  }

              });// on 'click'

              // When customizing, expand the currently edited item
              // @see CZRItemConstructor in api.czrModuleMap.czr_img_slider_collection_child
              if ( window.wp && ! nb_.isUndefined( wp.customize ) ) {
                    wp.customize.preview.bind('sek-item-focus', function( params ) {

                          var $itemEl = $('[data-sek-item-id="' + params.item_id +'"]', '.sek-accord-wrapper').first();
                          if ( 1 > $itemEl.length )
                            return;

                          $itemEl.find('.sek-accord-title').trigger('sek-expand-accord-item');
                    });
              }
          });//jQuery()

      };/////////////// callbackFunc
      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', callbackFunc );
}(window, document));


