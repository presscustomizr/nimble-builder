<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
if ( !function_exists( 'Nimble\sek_print_shortcode_module_content' ) ) {
  function sek_print_shortcode_module_content( $value = array() ) {
    // Feb 2021 : now saved as a json to fix emojis issues
    // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
    // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
    $shortcode_mod_html_content = sek_maybe_decode_richtext( $value['text_content'] );

    $shortcode_mod_html_content = sek_strip_script_tags( $shortcode_mod_html_content );
    
    // Use our own content filter instead of $content = apply_filters( 'the_content', $tiny_mce_content );
    // because of potential third party plugins corrupting 'the_content' filter. https://github.com/presscustomizr/nimble-builder/issues/233
    printf( '<div class="sek-shortcode-content" data-sek-use-flexbox="%2$s">%1$s</div>',
        true === sek_booleanize_checkbox_val( $value['lazyload'] ) ? apply_filters( 'nimble_parse_for_smart_load', apply_filters( 'the_nimble_tinymce_module_content', wp_kses_post($shortcode_mod_html_content) ) ) : apply_filters( 'the_nimble_tinymce_module_content', wp_kses_post($shortcode_mod_html_content) ),
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
            'background: url(' . esc_url(NIMBLE_MODULE_ICON_PATH) . 'Nimble_shortcode_icon.svg) no-repeat 50% 75%;background-size: 170px;'
        );
    }
}

