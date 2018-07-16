<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = SEK_Front() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();


if ( ! function_exists( 'Nimble\sek_get_button_module_link' ) ) {
    function sek_get_button_module_link( $value ) {
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

if ( ! function_exists( 'Nimble\sek_get_button_module_icon' ) ) {
    function sek_get_button_module_icon( $value ) {
        return ! empty( $value[ 'icon' ] ) ? sprintf( '<i class="%1$s"></i>', $value[ 'icon' ] ) : '';
    }
}

//icon
if ( ! empty( $value[ 'icon' ] ) ) {
    $icon = sprintf( '<i class="%1$s"></i>', $value[ 'icon' ] );
}

// Print
if ( 'no-link' === $value['link-to'] ) :
    printf('<button class="sek-btn">%1$s<span class="sek-btn-text">%2$s</span></button>',
        sek_get_button_module_icon( $value ),
        strip_tags( $value[ 'button_text' ] )
    );
else :
    printf('<a class="sek-btn" href="%1$s" %2$s><span class="sek-btn-inner">%3$s<span class="sek-btn-text">%4$s</span></span></a>',
        sek_get_button_module_link( $value ),
        true === sek_booleanize_checkbox_val( $value['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
        sek_get_button_module_icon( $value ),
        strip_tags( $value['button_text'] )
    );
endif;