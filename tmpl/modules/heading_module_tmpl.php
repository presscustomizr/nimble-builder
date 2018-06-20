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
if ( ! function_exists( '\Nimble\sek_print_tiny_mce_text_heading_content') ) {
    function sek_print_tiny_mce_text_heading_content( $tiny_mce_content, $input_id, $module_model, $args = array() ) {
        $defaults = array(
            'strip_tags'   => true,
            'allowed_tags' => '<br><a><span><img><strong><em><del>',
            'echo'         => false
        );
        $args = wp_parse_args( $args, $defaults );

        if ( empty( $tiny_mce_content ) ) {
            $to_print = SEK_Front()->sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
        } else {
            $content = apply_filters( 'the_content', $tiny_mce_content );
            if ( $args[ 'strip_tags' ] ) {
                $content = strip_tags( $content, $args[ 'allowed_tags' ] );
            }
            if ( skp_is_customizing() ) {
                $to_print = sprintf('<div title="%3$s" data-sek-input-type="tiny_mce_editor" data-sek-input-id="%1$s">%2$s</div>', $input_id, $content, __('Click to edit', 'here') );
            } else {
                $to_print = $content;
            }
        }
        if ( $args[ 'echo' ] ) {
            echo $to_print;
        } else {
            return $to_print;
        }
    }
}
// print the module content if not empty
if ( array_key_exists('heading_text', $value ) ) {
    $tag = empty( $value[ 'heading_tag' ] ) ? 'h1' : $value[ 'heading_tag' ];

    printf( '<%1$s>%2$s</%1$s>',
        $tag,
        sek_print_tiny_mce_text_heading_content( $value['heading_text'], 'heading_text', $model )
    );
}