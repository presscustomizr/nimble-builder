<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

// Utility to print the html content
// should be wrapped in a specific selector when customizing,
if ( ! function_exists( 'Nimble\sek_print_html_content') ) {
    function sek_print_html_content( $html_content, $input_id ) {
        if ( empty( $html_content ) ) {
            echo Nimble_Manager()->sek_get_input_placeholder_content( 'text', 'html_content' );
        } else {
            // added September 2019 when revamping presscustomizr.com
            $html_content = sek_parse_template_tags( $html_content );

            //TODO: move add_filter 'sek_html_content' somewhere else so it's called once
            //and we won't need to remove it
            //Also consider to add several other filter callbacks e.g. wptexturize, wpemoji... : see default-filters for 'the_content' in wp-incudes/default-filters.php
            //The html widget for sure doesn't conver emojis
            add_filter( 'sek_html_content', 'do_shortcode' );
            echo apply_filters( 'sek_html_content', $html_content );
            remove_filter( 'sek_html_content', 'do_shortcode' );
        }
    }
}
// print the module content
if ( array_key_exists( 'html_content', $value ) ) {
    sek_print_html_content( $value['html_content'], 'html_content' );
}