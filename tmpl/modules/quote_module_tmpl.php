<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Fire() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$quote_content_settings = $value['quote_content'];
$cite_content_settings = $value['cite_content'];
$design_settings = $value['design'];

// Utility to print the text content generated with tinyMce
// should be wrapped in a specific selector when customizing,
//  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
if ( ! function_exists( __NAMESPACE__ . '\sek_print_quote_content' ) ) {
    function sek_print_quote_content( $quote_content, $input_id, $module_model, $echo = false ) {
        if ( skp_is_customizing() ) {
            $to_print = sprintf('<div title="%3$s" data-sek-input-type="textarea" data-sek-input-id="%1$s">%2$s</div>', $input_id, $quote_content, __( 'Click to edit', 'textdomain_to_be_replaced' ) );
        } else {
            $to_print = $quote_content;
        }

        if ( $echo ) {
            echo $to_print;
        } else {
            return $to_print;
        }

    }
}

// print the module content if not empty
if ( ! empty( $quote_content_settings['quote_text'] ) ) {
    sek_print_quote_content(
        sprintf( '<blockquote class="sek-quote%3$s"><div class="sek-quote-inner"><div class="sek-quote-content">%1$s</div>%2$s</div></blockquote>',
            wpautop( $quote_content_settings['quote_text'] ),
            ! empty( $cite_content_settings['cite_text'] ) ? sprintf( '<footer class="sek-quote-footer"><cite class="sek-cite">%1$s</cite></footer>', $cite_content_settings['cite_text'] ) : '',
            empty( $design_settings['quote_design'] ) || 'none' == $design_settings['quote_design'] ? '' : " sek-quote-design sek-{$design_settings['quote_design']}"
        ),
        'quote_text',
        $model,
        $echo = true
    );
}