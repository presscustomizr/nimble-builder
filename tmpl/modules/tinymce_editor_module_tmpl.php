<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Front() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$value = $value['main_settings'];

// Utility to print the text content generated with tinyMce
// should be wrapped in a specific selector when customizing,
//  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
if ( ! function_exists( '\Nimble\sek_print_tiny_mce_text_content') ) {
    function sek_print_tiny_mce_text_content( $tiny_mce_content, $input_id, $module_model ) {
        if ( empty( $tiny_mce_content ) ) {
            echo SEK_Front()->sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
        } else {
            $content = apply_filters( 'the_content', $tiny_mce_content );
            if ( skp_is_customizing() ) {
                printf('<div title="%3$s" data-sek-input-type="tiny_mce_editor" data-sek-input-id="%1$s">%2$s</div>', $input_id, $content, __( 'Click to edit', 'textdomain_to_be_replaced' ) );
            } else {
                echo $content;
            }
        }
    }
}
// print the module content if not empty
if ( array_key_exists('content', $value ) ) {
    sek_print_tiny_mce_text_content( $value['content'], 'content', $model );
}