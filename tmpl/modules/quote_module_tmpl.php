<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$quote_content_settings = $value['quote_content'];
$cite_content_settings = $value['cite_content'];
$design_settings = $value['design'];

// Utility to print the text content generated with tinyMce
// should be wrapped in a specific selector when customizing,
//  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
if ( !function_exists( __NAMESPACE__ . '\sek_print_quote_content' ) ) {
    function sek_print_quote_content( $quote_content, $input_id, $module_model ) {
        // added september 2020 related to https://github.com/presscustomizr/nimble-builder/issues/688
        $quote_content = sek_strip_script_tags( $quote_content );

        // filter added since text editor implementation https://github.com/presscustomizr/nimble-builder/issues/403
        // Use our own content filter instead of $content = apply_filters( 'the_content', $tiny_mce_content );
        // because of potential third party plugins corrupting 'the_content' filter. https://github.com/presscustomizr/nimble-builder/issues/233
        //$quote_content = apply_filters( 'the_nimble_tinymce_module_content', $quote_content );

        if ( skp_is_customizing() ) {
            $to_print = sprintf('<div title="%3$s" data-sek-input-type="textarea" data-sek-input-id="%1$s">%2$s</div>', esc_attr($input_id), $quote_content, __( 'Click to edit', 'textdomain_to_be_replaced' ) );
        } else {
            $to_print = $quote_content;
        }

        echo apply_filters( 'the_nimble_tinymce_module_content', wp_kses_post($to_print) );
    }
}

// print the module content if not empty
if ( !empty( $quote_content_settings['quote_text'] ) ) {
    $cite_text = '';
    if ( !empty( $cite_content_settings['cite_text'] ) ) {
        // Feb 2021 : now saved as a json to fix emojis issues
        // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
        // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
        $cite_text = sek_maybe_decode_richtext( $cite_content_settings['cite_text'] );
    }

    sek_print_quote_content(
        sprintf( '<blockquote class="sek-quote%3$s" data-sek-quote-design="%4$s"><div class="sek-quote-inner"><div class="sek-quote-content">%1$s</div>%2$s</div></blockquote>',
            // Feb 2021 : now saved as a json to fix emojis issues
            // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
            // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
            wp_kses_post(sek_maybe_decode_richtext( $quote_content_settings['quote_text'] )),
            !empty( $cite_text ) ? sprintf( '<footer class="sek-quote-footer"><cite class="sek-cite">%1$s</cite></footer>', sek_strip_script_tags( $cite_text ) ) : '',
            empty( $design_settings['quote_design'] ) || 'none' == $design_settings['quote_design'] ? '' : " sek-quote-design sek-{$design_settings['quote_design']}",
            $design_settings['quote_design']
        ),
        'quote_text',
        $model
    );
}