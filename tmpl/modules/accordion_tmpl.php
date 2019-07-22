<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




if ( ! function_exists( 'Nimble\sek_print_accordion' ) ) {
  function sek_print_accordion( $accord_collec = array(), $accord_opts, $model ) {
      $accord_collec = is_array( $accord_collec ) ? $accord_collec : array();
      $is_accordion_multi_item = count( $accord_collec ) > 1;
      //$hide_nav_on_mobiles = true === sek_booleanize_checkbox_val( $accord_opts['hide_nav_on_mobiles'] );
      ?>
        <?php printf('<div class="sek-accord-wrapper data-sek-accord-id="%1$s" data-sek-is-multi-item="%2$s" role="tablist">',
            $model['id'],
            $is_accordion_multi_item ? 'true' : 'false'
          ); ?>
          <?php if ( is_array( $accord_collec ) && count( $accord_collec ) > 0 ) : ?>
              <?php
              $ind = 1;
              foreach( $accord_collec as $key => $item ) {
                  $title = !empty( $item['title_text'] ) ? $item['title_text'] : sprintf( '%s %s', __('Accordion title', 'text_dom'), '#' . $ind );
                  // Put them together
                  printf( '<div class="sek-accord-item" title="%1$s" data-sek-item-id="%2$s" data-sek-expanded="%5$s"><div class="sek-accord-title" role="tab" aria-controls="sek-tab-content-%2$s"><span class="sek-inner-accord-title">%3$s</span><button>
  <span></span><span></span></button></div><div class="sek-accord-content" role="tabpanel" aria-labelledby="sek-tab-content-%2$s">%4$s</div></div>',
                      esc_html( esc_attr( $item['title_attr'] ) ),
                      $item['id'],
                      $title,
                      $item['text_content'],
                      'false'
                  );
                  $ind++;
              }//foreach
              ?>
          <?php endif; ?>
        </div><?php //.sek-accord-wrapper ?>

      <?php
  }
}

$model = Nimble_Manager() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$accord_collec = !empty($value['accord_collec']) ? $value['accord_collec'] : array();
$accord_opts = !empty($value['accord_opts']) ? $value['accord_opts'] : array();

if ( !empty( $accord_collec ) ) {
    sek_print_accordion( $accord_collec, $accord_opts, $model );
} else {
    if ( skp_is_customizing() ) {
        printf( '<div class="sek-accordion-placeholder"><div class="sek-accordion-ph-text" style="%2$s"><p>%1$s</p></div></div>',
            __('Click to start adding items.', 'text_doma'),
            'background: url(' . NIMBLE_MODULE_ICON_PATH . 'Nimble_accordion_icon.svg) no-repeat 50% 75%;background-size: 200px;'
        );
    }
}
