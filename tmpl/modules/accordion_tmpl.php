<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'Nimble\sek_print_accordion' ) ) {
  function sek_print_accordion( $accord_opts, $model, $accord_collec = array() ) {
      $accord_collec = is_array( $accord_collec ) ? $accord_collec : array();

      $is_accordion_multi_item = count( $accord_collec ) > 1;
      $first_expanded = true === sek_booleanize_checkbox_val( $accord_opts['first_expanded'] ) ? "true" : "false";

      //sek_error_log('$accord_opts??' . $first_expanded, $accord_opts );

      $global_border_width = sek_extract_numeric_value( $accord_opts['border_width_css']);
      $title_border_width = sek_extract_numeric_value( $accord_opts['title_border_w_css']);

      ?>
        <?php printf('<div class="sek-accord-wrapper" data-sek-accord-id="%1$s" data-sek-is-multi-item="%2$s" data-sek-first-expanded="%3$s" data-sek-one-expanded="%4$s" data-sek-has-global-border="%5$s" data-sek-has-title-border="%6$s" role="tablist">',
            esc_attr($model['id']),
            $is_accordion_multi_item ? "true" : "false",
            esc_attr($first_expanded),
            true === sek_booleanize_checkbox_val( $accord_opts['one_expanded'] ) ? "true" : "false",
            $global_border_width > 0 ? "true" : "false",
            $title_border_width > 0 ? "true" : "false"
          ); ?>
          <?php if ( is_array( $accord_collec ) && count( $accord_collec ) > 0 ) : ?>
            <?php
                $ind = 1;
                foreach( $accord_collec as $key => $item ) {
                    $title = !empty( $item['title_text'] ) ? $item['title_text'] : sprintf( '%s %s', __('Accordion title', 'text_dom'), '#' . $ind );
                    $item_html_content = $item['text_content'];

                    $item_html_content = sek_maybe_decode_richtext($item_html_content);
                    // added may 2020 related to https://github.com/presscustomizr/nimble-builder/issues/688
                    $item_html_content = sek_strip_script_tags( $item_html_content );

                    // Use our own content filter instead of $content = apply_filters( 'the_content', $tiny_mce_content );
                    // because of potential third party plugins corrupting 'the_content' filter. https://github.com/presscustomizr/nimble-builder/issues/233
                    // added may 2020 for #699
                    // 'the_nimble_tinymce_module_content' includes parsing template tags
                    // Put them together
                    $title_attr = esc_attr( $item['title_attr'] );
                    printf( '<div class="sek-accord-item" %1$s data-sek-item-id="%2$s" data-sek-expanded="%5$s"><div id="sek-tab-title-%2$s" class="sek-accord-title" role="tab" aria-controls="sek-tab-content-%2$s"><span class="sek-inner-accord-title">%3$s</span><div class="expander"><span></span><span></span></div></div><div id="sek-tab-content-%2$s" class="sek-accord-content" role="tabpanel" aria-labelledby="sek-tab-title-%2$s">%4$s</div></div>',
                        empty($title_attr) ? '' : 'title="'. esc_html($title_attr) . '"',
                        esc_attr($item['id']),
                        wp_kses_post(sek_maybe_decode_richtext($title)),// convert into a json to prevent emoji breaking global json data structure
                        !skp_is_customizing() ? apply_filters( 'nimble_parse_for_smart_load', apply_filters( 'the_nimble_tinymce_module_content', wp_kses_post($item_html_content)  )) : apply_filters( 'the_nimble_tinymce_module_content', wp_kses_post($item_html_content) ),
                        ( 'true' === $first_expanded && 1 === $ind ) ? "true" : "false"
                    );
                    $ind++;
                }//foreach
            ?>
          <?php endif; ?>
        </div><?php //.sek-accord-wrapper ?>

      <?php
  }
}

$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$accord_collec = !empty($value['accord_collec']) ? $value['accord_collec'] : array();
$accord_opts = !empty($value['accord_opts']) ? $value['accord_opts'] : array();

if ( !empty( $accord_collec ) ) {
    sek_print_accordion( $accord_opts, $model, $accord_collec );
    sek_emit_js_event('nb-needs-accordion');
} else {
    if ( skp_is_customizing() ) {
        printf( '<div class="sek-mod-preview-placeholder"><div class="sek-preview-ph-text" style="%2$s"><p>%1$s</p></div></div>',
            __('Click to start adding items.', 'text_doma'),
            'background: url(' . esc_url(NIMBLE_MODULE_ICON_PATH) . 'Nimble_accordion_icon.svg) no-repeat 50% 75%;background-size: 170px;'
        );
    }
}
