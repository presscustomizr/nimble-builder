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
if ( !function_exists( 'Nimble\sek_get_text_heading_content' ) ) {
    function sek_get_text_heading_content( $heading_content, $input_id, $module_model ) {
        if ( empty( $heading_content ) ) {
            $to_print = Nimble_Manager()->sek_get_input_placeholder_content( 'text', $input_id );
        } else {
            // filter added since text editor implementation https://github.com/presscustomizr/nimble-builder/issues/403
            // Use our own content filter instead of $content = apply_filters( 'the_content', $tiny_mce_content );
            // because of potential third party plugins corrupting 'the_content' filter. https://github.com/presscustomizr/nimble-builder/issues/233
            remove_filter( 'the_nimble_tinymce_module_content', 'wpautop');
            
            // Feb 2021 : now saved as a json to fix emojis issues
            // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
            // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
            $heading_content = sek_maybe_decode_richtext($heading_content);

            $heading_content = apply_filters( 'the_nimble_tinymce_module_content', $heading_content );
            $heading_content = sek_strip_script_tags($heading_content);
            add_filter( 'the_nimble_tinymce_module_content', 'wpautop');
            if ( skp_is_customizing() ) {
                $to_print = sprintf('<div title="%3$s" data-sek-input-type="textarea" data-sek-input-id="%1$s">%2$s</div>',
                    esc_attr($input_id),
                    $heading_content,
                    __( 'Click to edit', 'textdomain_to_be_replaced' )
                );
            } else {
                $to_print = $heading_content;
            }
        }
        // Make sure to strip possible heading tags added as html content
        if ( is_string($to_print) ) {
            foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
                $to_print = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/",'', $to_print );
            }
        }
        return $to_print;
    }
}

if ( !function_exists( 'Nimble\sek_get_heading_module_link') ) {
    function sek_get_heading_module_link( $value ) {
        $link = 'javascript:void(0);';
        // if ( skp_is_customizing() ) {
        //     return $link;
        // }
        if ( true === sek_booleanize_checkbox_val( $value['link-to'] ) ) {
            if ( !empty( $value['link-pick-url'] ) && !empty( $value['link-pick-url']['id'] ) ) {
                if ( '_custom_' == $value['link-pick-url']['id']  && !empty( $value['link-custom-url'] ) ) {
                    $custom_url = apply_filters( 'nimble_parse_template_tags', $value['link-custom-url'] );
                    $link = $custom_url;
                } else if ( !empty( $value['link-pick-url']['url'] ) ) {
                    $link = $value['link-pick-url']['url'];
                }
            }
        }
        return $link;
    }
}

// print the module content if not empty
if ( array_key_exists('heading_text', $value ) ) {
    $tag = empty( $value[ 'heading_tag' ] ) ? 'h1' : $value[ 'heading_tag' ];
    // Feb 2021 : now saved as a json to fix emojis issues
    // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
    // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
    $heading_title = sek_maybe_decode_richtext( empty( $value['heading_title'] ) ? '' : $value['heading_title'] );
    if ( false === sek_booleanize_checkbox_val( $value['link-to'] ) ) {
        printf( '<%1$s %3$s class="sek-heading">%2$s</%1$s>',
            esc_attr($tag),
            wp_kses_post(sek_get_text_heading_content( $value['heading_text'], 'heading_text', $model )),
            !empty( $heading_title ) ? 'title="' . esc_html( $heading_title ) . '"' : ''
        );
    } else {
        printf( '<%1$s %3$s class="sek-heading">%2$s</%1$s>',
            esc_attr($tag),
            sprintf('<a href="%1$s" %2$s>%3$s</a>',
                esc_url(sek_get_heading_module_link( $value )),
                true === sek_booleanize_checkbox_val( $value['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
                wp_kses_post(sek_get_text_heading_content( $value['heading_text'], 'heading_text', $model ))
            ),
            !empty( $heading_title ) ? 'title="' . esc_html( $heading_title ) . '"' : ''
        );
    }

}