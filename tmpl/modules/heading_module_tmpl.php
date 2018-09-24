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
if ( ! function_exists( __NAMESPACE__ . '\sek_print_text_heading_content' ) ) {
    function sek_print_text_heading_content( $heading_content, $input_id, $module_model, $echo = false ) {
        if ( empty( $heading_content ) ) {
            $to_print = SEK_Front()->sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
        } else {
            if ( skp_is_customizing() ) {
                $to_print = sprintf('<div title="%3$s" data-sek-input-type="textarea" data-sek-input-id="%1$s">%2$s</div>', $input_id, $heading_content, __( 'Click to edit', 'textdomain_to_be_replaced' ) );
            } else {
                $to_print = $heading_content;
            }
        }
        if ( $echo ) {
            echo $to_print;
        } else {
            return $to_print;
        }

    }
}

// print the module content if not empty
if ( array_key_exists('heading_text', $value ) ) {
    $tag = empty( $value[ 'heading_tag' ] ) ? 'h1' : $value[ 'heading_tag' ];

    printf( '<%1$s class="sek-heading">%2$s</%1$s>',
        $tag,
        sek_print_text_heading_content( $value['heading_text'], 'heading_text', $model )
    );
}