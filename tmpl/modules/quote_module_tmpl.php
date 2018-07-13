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
if ( ! function_exists( __NAMESPACE__ . '\sek_print_quote_content' ) ) {
    function sek_print_quote_content( $quote_content, $input_id, $module_model, $echo = false ) {
        if ( empty( $quote_content ) ) {
            $to_print = SEK_Front()->sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
        } else {
            if ( skp_is_customizing() ) {
                $to_print = sprintf('<div title="%3$s" data-sek-input-type="textarea" data-sek-input-id="%1$s">%2$s</div>', $input_id, $quote_content, __( 'Click to edit', 'textdomain_to_be_replaced' ) );
            } else {
                $to_print = $quote_content;
            }
        }
        if ( $echo ) {
            echo $to_print;
        } else {
            return $to_print;
        }

    }
}

//We might skip this, sanitization on save should be enough
$value = sanitize_callback__czr_quote_module( $value );

// print the module content if not empty
// blockquote specifications:
// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/blockquote
// https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#Flow_content
if ( ! empty( $value['quote_text'] ) ) {
    sek_print_quote_content(
        sprintf( '<blockquote class="sek-quote%3$s"><div class="sek-quote-content">%1$s</div>%2$s</blockquote>',
            wpautop( $value['quote_text'] ),
            ! empty( $value['cite_text'] ) ? sprintf( '<footer><cite class="sek-cite">%1$s</cite></footer>', $value['cite_text'] ) : '',
            empty( $value['quote_design'] ) || 'none' == $value['quote_design'] ? '' : " sek-quote-design sek-{$value['quote_design']}"
        ),
        'quote_text',
        $model,
        $echo = true
    );
}