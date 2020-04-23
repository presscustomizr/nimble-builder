<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

if ( ! function_exists( 'Nimble\sek_print_shortcode_module_content' ) ) {
  function sek_print_shortcode_module_content( $value = array() ) {
    $shortcode_mod_html_content = $value['text_content'];
    // Use our own content filter instead of $content = apply_filters( 'the_content', $tiny_mce_content );
    // because of potential third party plugins corrupting 'the_content' filter. https://github.com/presscustomizr/nimble-builder/issues/233
    $content = apply_filters( 'the_nimble_tinymce_module_content', $shortcode_mod_html_content );
    // april 2020 : parse for lazy load added for https://github.com/presscustomizr/nimble-builder/issues/669
    $content = apply_filters( 'nimble_parse_for_smart_load', $content );
    printf( '<div class="sek-shortcode-content" data-sek-use-flexbox="%2$s">%1$s</div>',
      $content,
      ( array_key_exists( 'use_flex', $value ) && true === sek_booleanize_checkbox_val( $value['use_flex'] ) ) ? "true" : "false"
    );
  }
}

if ( !empty( $value['text_content'] ) ) {
    sek_print_shortcode_module_content( $value );
} else {
    if ( skp_is_customizing() ) {
        printf( '<div class="sek-mod-preview-placeholder"><div class="sek-preview-ph-text" style="%2$s"><p>%1$s</p></div></div>',
            __('Click to edit the shortcode module.', 'text_doma'),
            'background: url(' . NIMBLE_MODULE_ICON_PATH . 'Nimble_shortcode_icon.svg) no-repeat 50% 75%;background-size: 170px;'
        );
    }
}

