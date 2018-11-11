<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Fire() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$value = $value['main_settings'];

// Utility to print the text content generated with tinyMce
// should be wrapped in a specific selector when customizing,
//  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
if ( ! function_exists( 'Nimble\sek_print_tiny_mce_text_content') ) {
    function sek_print_tiny_mce_text_content( $tiny_mce_content, $input_id, $value ) {
        if ( empty( $tiny_mce_content ) ) {
            echo SEK_Fire()->sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
        } else {
            if ( false === sek_booleanize_checkbox_val( $value['autop'] ) ) {
                remove_filter( 'the_content', 'wpautop');
            }

            // Use our own content filter instead of $content = apply_filters( 'the_content', $tiny_mce_content );
            // because of potential third party plugins corrupting 'the_content' filter. https://github.com/presscustomizr/nimble-builder/issues/233
            $content = apply_filters( 'the_nimble_tinymce_module_content', $tiny_mce_content );
            if ( false === sek_booleanize_checkbox_val( $value['autop'] ) ) {
                add_filter( 'the_content', 'wpautop');
            }
            if ( skp_is_customizing() ) {
                printf('<div title="%3$s" data-sek-input-type="tiny_mce_editor" data-sek-input-id="%1$s">%2$s</div>', $input_id, $content, __( 'Click to edit', 'textdomain_to_be_replaced' ) );
            } else {
                echo apply_filters( 'nimble_parse_for_smart_load', $content );
            }
        }
    }
}
// print the module content if not empty
if ( array_key_exists('content', $value ) ) {
    sek_print_tiny_mce_text_content( $value['content'], 'content', $value );
}