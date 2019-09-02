<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
/* $value looks like
Array
(
    [icon] => fab fa-adversal
    [link-to] => url
    [link-pick-url] => Array
        (
            [id] => 3126
            [type_label] => Page
            [title] => Test foogallery
            [object_type] => page
            [url] => http://customizr-tests.wordpress.test/test-foogallery/
        )

    [link-custom-url] =>
    [link-target] => 1
    [font_size_css] => 15
    [h_alignment_css] => right
    [color_css] => #590505
    [color_hover_css] => #590606
)
*/
if ( ! function_exists( 'Nimble\sek_get_icon_module_icon_html') ) {
    function sek_get_icon_module_icon_html( $value ) {
        $html = '';
        $icon_settings = $value['icon_settings'];
        $spacing_border = $value['spacing_border'];

        if ( ! empty( $icon_settings['icon'] ) ) {
            $html = sprintf( '<div class="sek-icon-wrapper"><i class="%1$s"></i></div>', $icon_settings[ 'icon' ] );
        } else {
            //falls back on an icon if previewing
            if ( skp_is_customizing() ) {
                $html = Nimble_Manager()->sek_get_input_placeholder_content( 'icon' );
            }
        }
        return $html;
    }
}


if ( ! function_exists( 'Nimble\sek_get_icon_module_icon_link' ) ) {
    function sek_get_icon_module_icon_link( $icon_settings ) {
        $link = 'javascript:void(0);';
        // if ( skp_is_customizing() ) {
        //     return $link;
        // }
        if ( 'url' == $icon_settings['link-to'] ) {
            if ( ! empty( $icon_settings['link-pick-url'] ) && ! empty( $icon_settings['link-pick-url']['id'] ) ) {
                if ( '_custom_' == $icon_settings['link-pick-url']['id']  && ! empty( $icon_settings['link-custom-url'] ) ) {
                    $link = esc_url( $icon_settings['link-custom-url'] );
                } else if ( ! empty( $icon_settings['link-pick-url']['url'] ) ) {
                    $link = esc_url( $icon_settings['link-pick-url']['url'] );
                }
            }
        }
        return $link;
    }
}

$icon_settings = $value['icon_settings'];
$spacing_border = $value['spacing_border'];

$visual_effect_class = '';
//visual effect classes
if ( isset( $spacing_border['use_box_shadow'] ) && true === sek_booleanize_checkbox_val( $spacing_border['use_box_shadow'] ) ) {
    $visual_effect_class = 'box-shadow';
}

// Print
if ( 'no-link' === $icon_settings['link-to'] ) :
    printf('<div class="sek-icon %2$s">%1$s</div>',
        sek_get_icon_module_icon_html( $value ),
        $visual_effect_class
    );
else :
    printf('<a class="sek-icon %4$s" href="%1$s" %2$s>%3$s</a>',
        sek_get_icon_module_icon_link( $icon_settings ),
        true === sek_booleanize_checkbox_val( $icon_settings['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
        sek_get_icon_module_icon_html( $value ),
        $visual_effect_class
    );
endif;
