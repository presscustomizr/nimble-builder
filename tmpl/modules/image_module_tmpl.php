<?php
// this.defaultItemModel = {
//     img : '',
//     'img-size' : 'large',
//     'alignment' : '',
//     'link-to' : '',
//     'link-pick-url' : '',
//     'link-custom-url' : '',
//     'link-target' : '',
//     'lightbox' : true
// };
$model = SEK_Front() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
if ( ! function_exists( 'sek_get_img_module_img_html') ) {
    function sek_get_img_module_img_html( $value ) {
        $html = '';
        if ( is_int( $value['img'] ) ) {
            $html = wp_get_attachment_image( $value['img'], empty( $value['img-size'] ) ? 'large' : $value['img-size']);
        } else if ( array_key_exists('img', $value ) && is_string( $value['img'] ) ) {
            $html = sprintf( '<img alt="default img" src="%1$s"/>', $value['img'] );
        } else {
            //falls back on an icon if previewing
            if ( skp_is_customizing() ) {
                $html = SEK_Front() -> sek_get_input_placeholder_content( 'upload' );
            }
        }
        return $html;
    }
}

if ( ! function_exists( 'sek_get_img_module_img_link' ) ) {
    function sek_get_img_module_img_link( $value ) {
        $link = 'javascript:void()';
        if ( skp_is_customizing() ) {
            return $link;
        }
        switch( $value['link-to'] ) {
            case 'url' :
                if ( ! empty( $value['link-pick-url'] ) && ! empty( $value['link-pick-url']['id'] ) ) {
                    if ( '_custom_' == $value['link-pick-url']['id']  && ! empty( $value['link-custom-url'] ) ) {
                        $link = $value['link-custom-url'];
                    } else if ( ! empty( $value['link-pick-url']['url'] ) ) {
                        $link = $value['link-pick-url']['url'];
                    }
                }
            break;
            case 'img-file' :
                if ( is_int( $value['img'] ) ) {
                    $link = wp_get_attachment_url( $value['img'] );
                }
            break;
            case 'img-page' :
                if ( is_int( $value['img'] ) ) {
                    $link = get_attachment_link( $value['img'] );
                }
            break;
        }
        return $link;
    }
}

// Print
if ( 'no-link' === $value['link-to'] ) {
    echo sek_get_img_module_img_html( $value );
} else {
    printf('<a href="%1$s" %2$s>%3$s</a>',
        sek_get_img_module_img_link( $value ),
        true === (bool)$value['link-target'] ? 'target="_blank"' : '',
        sek_get_img_module_img_html( $value )
    );
}