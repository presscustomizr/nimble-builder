<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

// Utility to print the html content
// should be wrapped in a specific selector when customizing,
if ( !function_exists( 'Nimble\sek_print_html_content') ) {
    function sek_print_html_content( $html_content, $input_id ) {
        if ( empty( $html_content ) ) {
            echo wp_kses_post(Nimble_Manager()->sek_get_input_placeholder_content( 'text', 'html_content' ));
        } else {
            // Feb 2021 : now saved as a json to fix emojis issues
            // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
            // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
            $html_content = sek_maybe_decode_richtext( $html_content );

            // added September 2019 when revamping presscustomizr.com
            $html_content = sek_parse_template_tags( $html_content );

            // added may 2020 related to https://github.com/presscustomizr/nimble-builder/issues/688
            $html_content = sek_strip_script_tags_and_print_js_inline( $html_content, Nimble_Manager()->model );

            //TODO: move add_filter 'sek_html_content' somewhere else so it's called once
            //and we won't need to remove it
            //Also consider to add several other filter callbacks e.g. wptexturize, wpemoji... : see default-filters for 'the_content' in wp-incudes/default-filters.php
            //The html widget for sure doesn't conver emojis
            add_filter( 'sek_html_content', 'do_shortcode' );
            if ( !skp_is_customizing() ) {
                echo apply_filters( 'nimble_parse_for_smart_load',apply_filters( 'sek_html_content',  htmlspecialchars_decode(esc_html($html_content) )) );
            } else {
                add_filter( 'safe_style_css', 'nimble_allow_display_attribute' );
                echo wp_kses_post($html_content);
                remove_filter( 'safe_style_css', 'nimble_allow_display_attribute' );
            }
            remove_filter( 'sek_html_content', 'do_shortcode' );
        }
    }
}
// print the module content
if ( array_key_exists( 'html_content', $value ) ) {
    sek_print_html_content( $value['html_content'], 'html_content' );
}