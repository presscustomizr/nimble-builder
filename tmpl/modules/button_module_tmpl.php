<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = SEK_Front() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$content_settings = $value['content'];
$design_settings = $value['design'];

if ( ! function_exists( 'Nimble\sek_get_button_module_link' ) ) {
    function sek_get_button_module_link( $content_settings ) {
        $link = 'javascript:void(0);';
        if ( skp_is_customizing() ) {
            return $link;
        }
        if ( 'url' == $content_settings['link-to'] ) {
            if ( ! empty( $content_settings['link-pick-url'] ) && ! empty( $content_settings['link-pick-url']['id'] ) ) {
                if ( '_custom_' == $content_settings['link-pick-url']['id']  && ! empty( $content_settings['link-custom-url'] ) ) {
                    $link = esc_url( $content_settings['link-custom-url'] );
                } else if ( ! empty( $content_settings['link-pick-url']['url'] ) ) {
                    $link = esc_url( $content_settings['link-pick-url']['url'] );
                }
            }
        }
        return $link;
    }
}

if ( ! function_exists( 'Nimble\sek_get_button_module_icon' ) ) {
    function sek_get_button_module_icon( $content_settings ) {
        return ! empty( $content_settings[ 'icon' ] ) ? sprintf( '<i class="%1$s"></i>', $content_settings[ 'icon' ] ) : '';
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
if ( !isset( $content_settings['link-to'] ) || isset( $content_settings['link-to'] ) && 'no-link' === $content_settings['link-to'] ) :
    printf('<button class="sek-btn%3$s"><span class="sek-btn-inner">%1$s<span class="sek-btn-text">%2$s</span></span></button>',
        sek_get_button_module_icon( $content_settings ),
        strip_tags( $content_settings[ 'button_text' ] ),
        $visual_effect_class
    );
else :
    printf('<a class="sek-btn%5$s" href="%1$s" %2$s><span class="sek-btn-inner">%3$s<span class="sek-btn-text">%4$s</span></span></a>',
        sek_get_button_module_link( $content_settings ),
        true === sek_booleanize_checkbox_val( $content_settings['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
        sek_get_button_module_icon( $content_settings ),
        strip_tags( $content_settings['button_text'] ),
        $visual_effect_class
    );
endif;