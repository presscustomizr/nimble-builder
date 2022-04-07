<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$content_settings = $value['content'];
$design_settings = $value['design'];

if ( !function_exists( 'Nimble\sek_get_button_module_link' ) ) {
    function sek_get_button_module_link( $content_settings ) {
        $link = 'javascript:void(0);';
        // if ( skp_is_customizing() ) {
        //     return $link;
        // }
        if ( 'url' == $content_settings['link-to'] ) {
            if ( !empty( $content_settings['link-pick-url'] ) && !empty( $content_settings['link-pick-url']['id'] ) ) {
                if ( '_custom_' == $content_settings['link-pick-url']['id']  && !empty( $content_settings['link-custom-url'] ) ) {
                    $link = $content_settings['link-custom-url'];
                } else if ( !empty( $content_settings['link-pick-url']['url'] ) ) {
                    $link = $content_settings['link-pick-url']['url'];
                }
            }
        }
        return $link;
    }
}

if ( !function_exists( 'Nimble\sek_get_button_module_icon' ) ) {
    function sek_get_button_module_icon( $content_settings ) {
        return !empty( $content_settings[ 'icon' ] ) ? sprintf( '<i class="%1$s"></i>', esc_attr($content_settings[ 'icon' ]) ) : '';
    }
}

$visual_effect_class = '';
//visual effect classes
if ( isset( $design_settings['use_box_shadow'] ) && true === sek_booleanize_checkbox_val( $design_settings['use_box_shadow'] ) ) {
    $visual_effect_class = ' box-shadow';
    if ( isset( $design_settings['push_effect'] ) && true === sek_booleanize_checkbox_val( $design_settings['push_effect'] ) ) {
        $visual_effect_class .= ' push-effect';
    }
}

// Print
// Feb 2021 : now saved as a json to fix emojis issues
// see fix for https://github.com/presscustomizr/nimble-builder/issues/544
// to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
$btn_text = sek_maybe_decode_richtext( $content_settings[ 'button_text'] );
$btb_text = sek_strip_script_tags($btn_text);
$icon_side = empty($content_settings['icon-side']) ? 'left' : $content_settings['icon-side'];

if ( !isset( $content_settings['link-to'] ) || isset( $content_settings['link-to'] ) && 'no-link' === $content_settings['link-to'] )  {
    printf('<button %5$s class="sek-btn%3$s"><span class="sek-btn-inner">%1$s<span class="sek-btn-text">%2$s</span>%4$s</span></button>',
        ( 'left' === $icon_side && !empty( $content_settings[ 'icon' ] ) ) ? sprintf( '<i class="%1$s"></i>', esc_attr($content_settings[ 'icon' ]) ) : '',
        convert_smilies( wp_kses_post($btb_text) ),
        esc_attr($visual_effect_class),
        ( 'right' === $icon_side && !empty( $content_settings[ 'icon' ] ) ) ? sprintf( '<i class="%1$s"></i>', esc_attr($content_settings[ 'icon' ]) ) : '',
        !empty($content_settings['btn_text_on_hover']) ? 'title="' . esc_html( $content_settings['btn_text_on_hover'] ) . '"' : ''
    );
} else {
    printf('<a %7$s class="sek-btn%5$s" href="%1$s" %2$s><span class="sek-btn-inner">%3$s<span class="sek-btn-text">%4$s</span>%6$s</span></a>',
        esc_url( sek_get_button_module_link( $content_settings ) ),
        true === sek_booleanize_checkbox_val( $content_settings['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
        ( 'left' === $icon_side && !empty( $content_settings[ 'icon' ] ) ) ? sprintf( '<i class="%1$s"></i>', esc_attr($content_settings[ 'icon' ]) ) : '',
        convert_smilies( wp_kses_post($btb_text) ),
        esc_attr($visual_effect_class),
        ( 'right' === $icon_side && !empty( $content_settings[ 'icon' ] ) ) ? sprintf( '<i class="%1$s"></i>', esc_attr($content_settings[ 'icon' ]) ) : '',
        !empty($content_settings['btn_text_on_hover']) ? 'title="' . esc_html( $content_settings['btn_text_on_hover'] ) . '"' : ''
    );
}
if ( !empty(sek_get_button_module_icon( $content_settings )) ) {
    sek_emit_js_event('nb-needs-fa');
}