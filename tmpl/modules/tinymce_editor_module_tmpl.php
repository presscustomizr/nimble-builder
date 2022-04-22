<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$value = $value['main_settings'];

// Utility to print the text content generated with tinyMce
// should be wrapped in a specific selector when customizing,
//  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
if ( !function_exists( 'Nimble\sek_print_tiny_mce_text_content') ) {
    function sek_print_tiny_mce_text_content( $tiny_mce_content, $input_id, $value ) {
        if ( empty( $tiny_mce_content ) ) {
            echo wp_kses_post(Nimble_Manager()->sek_get_input_placeholder_content( 'detached_tinymce_editor', $input_id ));
        } else {
            if ( false === sek_booleanize_checkbox_val( $value['autop'] ) ) {
                remove_filter( 'the_nimble_tinymce_module_content', 'wpautop');
            }

            // Feb 2021 : now saved as a json to fix emojis issues
            // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
            // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
            $content = sek_maybe_decode_richtext( $tiny_mce_content );
            $content = sek_strip_script_tags_and_print_js_inline( $content, Nimble_Manager()->model );
            // Use our own content filter instead of $content = apply_filters( 'the_content', $tiny_mce_content );
            // because of potential third party plugins corrupting 'the_content' filter. https://github.com/presscustomizr/nimble-builder/issues/233
            if ( false === sek_booleanize_checkbox_val( $value['autop'] ) ) {
                add_filter( 'the_nimble_tinymce_module_content', 'wpautop');
            }
            if ( skp_is_customizing() ) {
                printf('<div title="%3$s" data-sek-input-type="detached_tinymce_editor" data-sek-input-id="%1$s">%2$s</div>', esc_attr($input_id), apply_filters( 'the_nimble_tinymce_module_content', wp_kses_post($content)), __( 'Click to edit', 'textdomain_to_be_replaced' ) );
            } else {
                echo apply_filters( 'nimble_parse_for_smart_load', apply_filters( 'the_nimble_tinymce_module_content', htmlspecialchars_decode(esc_html($content))) );
            }
        }
    }
}
// print the module content if not empty
if ( array_key_exists('content', $value ) ) {
    sek_print_tiny_mce_text_content( $value['content'], 'content', $value );
}