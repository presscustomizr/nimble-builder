<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Front() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

// Utility to print the text content generated with tinyMce
// should be wrapped in a specific selector when customizing,
//  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
if ( ! function_exists( '\Nimble\sek_print_tiny_mce_text_content') ) {
    function sek_print_tiny_mce_text_content( $tiny_mce_content, $input_id, $module_model, $strip_tags = false, $allowed_tags = '' ) {
        if ( empty( $tiny_mce_content ) ) {
            echo SEK_Front()->sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
        } else {
            $content = apply_filters( 'the_content', $tiny_mce_content );
            if ( $strip_tags ) {
                $content = strip_tags( $content, $allowed_tags );
            }
            if ( skp_is_customizing() ) {
                printf('<div title="%3$s" data-sek-input-type="tiny_mce_editor" data-sek-input-id="%1$s">%2$s</div>', $input_id, $content, __('Click to edit', 'here') );
            } else {
                echo $content;
            }
        }
    }
}
// print the module content if not empty
if ( array_key_exists('heading_text', $value ) ) {
    $tag = empty( $value[ 'heading_tag' ] ) ? 'h1' : $value[ 'heading_tag' ];

    echo "<$tag>";
    sek_print_tiny_mce_text_content( $value['heading_text'], 'heading_text', $model, $strip_tags = true, $allowed_tags = '<br><a><span><img><strong><em>' );
    echo "</$tag>";
}