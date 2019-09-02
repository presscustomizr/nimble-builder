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
$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$main_settings = $value['main_settings'];
//$borders_corners_settings = $value['borders_corners'];

if ( ! function_exists( 'Nimble\sek_get_img_module_img_html') ) {
    function sek_get_img_module_img_html( $value ) {
        // 'round' => rounded shape
        // 'expanded' => already expaded
        $visual_effect_class = array( 'round' );

        $html = '';
        if ( is_int( $value['img'] ) ) {
            $img_src = wp_get_attachment_image_src( $value['img'], empty( $value['img-size'] ) ? 'large' : $value['img-size']);
        }
        if ( ! empty( $img_src ) && isset( $img_src[0] ) ) {
            $img_url = $img_src[0];
        } else {
            $img_url = $value['img'];
        }

        if ( ! empty( $img_url ) && is_string( $img_url ) ) {
            $html = sprintf( '<div class="sek-nimble-image" style="background-image:url(%1$s)"></div>', esc_url($img_url ) );
        } else {
            //falls back on an icon if previewing
            if ( skp_is_customizing() ) {
                $html = Nimble_Manager()->sek_get_input_placeholder_content( 'upload' );
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
        if ( is_int( $value['img'] ) ) {
            $img_post = get_post( $value['img'] );
            if ( !is_wp_error( $img_post ) && is_object( $img_post ) && 'attachment' === $img_post->post_type ) {
                $caption = $img_post->post_excerpt;
                $description = $img_post->post_content;
                $img_title = $img_post->post_title;
                if ( false !== sek_booleanize_checkbox_val( $value['use_custom_title_attr']) ) {
                    $title = esc_html( $value['heading_title'] );
                } elseif ( !empty( $caption ) ) {
                    $title = $caption;
                } else if ( !empty( $description ) ) {
                    $title = $description;
                } else if ( !empty( $img_title ) ) {
                    $title = $img_title;
                }
            }
        }

        if ( 'no-link' === $value['link-to'] ) {
            $html = sprintf('<div class="sek-nimble-image-mask"></div>%1$s',
                $html
            );
        } else {
            $html = sprintf('<a href="%1$s" class="sek-nimble-image-mask" %2$s></a>%3$s',
                sek_get_img_module_img_link( $value ),
                true === sek_booleanize_checkbox_val( $value['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
                $html
            );
        }
        // Would be great if this would be able to parse also the background-url inline style and move it into the data-sek-lazy-bg attribute
        return apply_filters( 'nimble_parse_for_smart_load', sprintf('<figure class="sek-nimble-image-wrapper %1$s" title="%3$s">%2$s</figure>', implode( ' ', $visual_effect_class), $html, esc_html( $title ) ) );
    }
}

if ( ! function_exists( 'Nimble\sek_get_img_module_img_link' ) ) {
    function sek_get_img_module_img_link( $value ) {
        $link = 'javascript:void(0);';
        // if ( skp_is_customizing() ) {
        //     return $link;
        // }
        switch( $value['link-to'] ) {
            case 'url' :
                if ( ! empty( $value['link-pick-url'] ) && ! empty( $value['link-pick-url']['id'] ) ) {
                    if ( '_custom_' == $value['link-pick-url']['id']  && ! empty( $value['link-custom-url'] ) ) {
                        $custom_url = apply_filters( 'nimble_parse_template_tags', $value['link-custom-url'] );
                        $link = esc_url( $custom_url );
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
echo sek_get_img_module_img_html( $main_settings );
