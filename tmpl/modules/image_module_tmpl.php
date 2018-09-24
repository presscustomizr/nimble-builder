<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
$main_settings = $value['main_settings'];
//$borders_corners_settings = $value['borders_corners'];

if ( ! function_exists( '\Nimble\sek_get_img_module_img_html') ) {
    function sek_get_img_module_img_html( $value ) {
        $visual_effect_class = '';
        //visual effect classes
        if ( true === sek_booleanize_checkbox_val( $value['use_box_shadow'] ) ) {
            $visual_effect_class = ' box-shadow';
        }
        if ( 'none' !== $value['img_hover_effect']) {
            $visual_effect_class .= " sek-hover-effect-" . $value['img_hover_effect'];
        }

        $html = '';
        if ( is_int( $value['img'] ) ) {
            $html = wp_get_attachment_image( $value['img'], empty( $value['img-size'] ) ? 'large' : $value['img-size']);
        } else if ( ! empty( $value['img'] ) && is_string( $value['img'] ) ) {
            $html = sprintf( '<img alt="default img" src="%1$s"/>', esc_url(  $value['img'] )  );
        } else {
            //falls back on an icon if previewing
            if ( skp_is_customizing() ) {
                $html = SEK_Front() -> sek_get_input_placeholder_content( 'upload' );
            }
        }

        $title = '';
        //   'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
        //   'caption' => $attachment->post_excerpt,
        //   'description' => $attachment->post_content,
        //   'href' => get_permalink( $attachment->ID ),
        //   'src' => $attachment->guid,
        //   'title' => $attachment->post_title
        $img_post = get_post( $value['img'] );
        if ( !is_wp_error( $img_post ) && is_object( $img_post ) ) {
            $caption = $img_post->post_excerpt;
            $description = $img_post->post_content;
            $img_title = $img_post->post_title;

            if ( !empty( $caption ) ) {
                $title = $caption;
            } else if ( !empty( $description ) ) {
                $title = $description;
            } else if ( !empty( $img_title ) ) {
                $title = $img_title;
            }
        }
        return sprintf('<figure class="%1$s" title="%3$s">%2$s</figure>', $visual_effect_class, $html, $title );
    }
}

if ( ! function_exists( '\Nimble\sek_get_img_module_img_link' ) ) {
    function sek_get_img_module_img_link( $value ) {
        $link = 'javascript:void(0);';
        if ( skp_is_customizing() ) {
            return $link;
        }
        switch( $value['link-to'] ) {
            case 'url' :
                if ( ! empty( $value['link-pick-url'] ) && ! empty( $value['link-pick-url']['id'] ) ) {
                    if ( '_custom_' == $value['link-pick-url']['id']  && ! empty( $value['link-custom-url'] ) ) {
                        $link = esc_url( $value['link-custom-url'] );
                    } else if ( ! empty( $value['link-pick-url']['url'] ) ) {
                        $link = esc_url( $value['link-pick-url']['url'] );
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
if ( 'no-link' === $main_settings['link-to'] ) {
    echo sek_get_img_module_img_html( $main_settings );
} else {
    printf('<a href="%1$s" %2$s>%3$s</a>',
        sek_get_img_module_img_link( $main_settings ),
        true === sek_booleanize_checkbox_val( $main_settings['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
        sek_get_img_module_img_html( $main_settings )
    );
}