<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Front() -> model;
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
if ( ! function_exists( '\Nimble\sek_get_icon_module_icon_html') ) {
    function sek_get_icon_module_icon_html( $value ) {
        $html = '';
        if ( ! empty( $value['icon'] ) ) {
            $html = sprintf( '<i class="%1$s"></i>', $value[ 'icon' ] );
        } else {
            //falls back on an icon if previewing
            if ( skp_is_customizing() ) {
                $html = SEK_Front() -> sek_get_input_placeholder_content( 'icon' );
            }
        }
        return $html;
    }
}


if ( ! function_exists( 'Nimble\sek_get_icon_module_icon_link' ) ) {
    function sek_get_icon_module_icon_link( $value ) {
        $link = 'javascript:void(0);';
        if ( skp_is_customizing() ) {
            return $link;
        }
        if ( 'url' == $value['link-to'] ) {
            if ( ! empty( $value['link-pick-url'] ) && ! empty( $value['link-pick-url']['id'] ) ) {
                if ( '_custom_' == $value['link-pick-url']['id']  && ! empty( $value['link-custom-url'] ) ) {
                    $link = esc_url( $value['link-custom-url'] );
                } else if ( ! empty( $value['link-pick-url']['url'] ) ) {
                    $link = esc_url( $value['link-pick-url']['url'] );
                }
            }
        }
        return $link;
    }
}
// Print
if ( 'no-link' === $value['link-to'] ) :
    printf('<div class="sek-icon">%1$s</div>',
        sek_get_icon_module_icon_html( $value )
    );
else :
    printf('<a class="sek-icon" href="%1$s" %2$s>%3$s</a>',
        sek_get_icon_module_icon_link( $value ),
        true === sek_booleanize_checkbox_val( $value['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
        sek_get_icon_module_icon_html( $value )
    );
endif;
