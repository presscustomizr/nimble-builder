<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
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


if ( !function_exists( 'Nimble\remove_attachment_image_style_attr' ) ) {
    function remove_attachment_image_style_attr( $attr ) {
        if ( is_array($attr) && isset($attr['style']) ) {
            unset($attr['style']);
        }
        return $attr;
    }
}

if ( !function_exists( 'Nimble\sek_get_img_module_img_html') ) {
    function sek_get_img_module_img_html( $value, $for_mobile = false, $img = null, $img_size = null ) {
        $img = !is_null($img) ? $img : $value['img'];
        $img_size = !is_null($img_size) ? $img_size : $value['img-size'];
        $use_post_thumbnail = !empty( $value['use-post-thumb'] ) && sek_is_checked( $value['use-post-thumb'] );

        if ( $use_post_thumbnail ) {
            $current_post_id = sek_get_post_id_on_front_and_when_customizing();
            $is_attachment = is_attachment();
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
                $is_attachment = sek_get_posted_query_param_when_customizing( 'is_attachment' );
            }
            if ( $is_attachment ) {
                $img = $current_post_id;
            } else {
                $img = ( has_post_thumbnail( $current_post_id ) ) ? get_post_thumbnail_id( $current_post_id ) : $img;
            }
        }

        $img_figure_classes = '';
        //visual effect classes
        if ( true === sek_booleanize_checkbox_val( $value['use_box_shadow'] ) ) {
            $img_figure_classes = ' box-shadow';
        }
        if ( 'none' !== $value['img_hover_effect']) {
            $img_figure_classes .= " sek-hover-effect-" . $value['img_hover_effect'];
        }

        $img_figure_classes .= $for_mobile ? " sek-is-mobile-logo" : " sek-img";

        if ( true === sek_booleanize_checkbox_val( $value['use_custom_height'] ) ) {
            $img_figure_classes .= " has-custom-height";
        }

        $html = '';
        if ( is_int( $img ) ) {
            // Nov 2020 : removes any additional styles added by a theme ( Twenty Twenty one ) or a plugin to the image
            add_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );
            $html = wp_get_attachment_image( $img, empty( $img_size ) ? 'large' : $img_size);
            remove_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );
        } else if ( !empty( $img ) && is_string( $img ) ) {
            // the default img is excluded from the smart loading parsing @see nimble_regex_callback()
            // => this is needed because this image has no specific dimensions set. And therefore can create false javascript computations of other element's distance to top on page load.
            // in particular when calculting if is_visible() to decide if we smart load.
            if ( false !== wp_http_validate_url( $img ) ) {
                $html = sprintf( '<img alt="default img" data-skip-lazyload="true" src="%1$s"/>', esc_url( $img )  );
            }
        } else {
            //falls back on an icon if previewing
            if ( skp_is_customizing() ) {
                $html = sprintf('<div style="min-height:50px">%1$s</div>', wp_kses_post(Nimble_Manager()->sek_get_input_placeholder_content( 'upload' )));
            }
        }

        // Do we have something ? If not print the placeholder
        if ( empty($html) && skp_is_customizing() ) {
            $html = sprintf('<div style="min-height:50px">%1$s</div>', wp_kses_post(Nimble_Manager()->sek_get_input_placeholder_content( 'upload' )));
        }

        $title = '';
        if ( false !== sek_booleanize_checkbox_val( $value['use_custom_title_attr']) ) {
            $title = strip_tags( $value['heading_title'] );
            // convert into a json to prevent emoji breaking global json data structure
            // fix for https://github.com/presscustomizr/nimble-builder/issues/544
            $title = sek_maybe_decode_richtext($title);
        } else {
            //   'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
            //   'caption' => $attachment->post_excerpt,
            //   'description' => $attachment->post_content,
            //   'href' => get_permalink( $attachment->ID ),
            //   'src' => $attachment->guid,
            //   'title' => $attachment->post_title
            if ( is_int( $img ) ) {
                $img_post = get_post( $img );
                if ( !is_wp_error( $img_post ) && is_object( $img_post ) && 'attachment' === $img_post->post_type ) {
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
            }
        }
        if ( !skp_is_customizing() && false !== strpos($html, 'data-sek-src="http') ) {
            $html = $html.Nimble_Manager()->css_loader_html;
        }
        return sprintf('<figure class="%1$s" title="%3$s">%2$s</figure>',
            esc_attr($img_figure_classes),
            $html,
            esc_html( $title )
        );
    }
}

if ( !function_exists( 'Nimble\sek_get_img_module_img_link' ) ) {
    function sek_get_img_module_img_link( $value ) {
        $link = 'javascript:void(0);';
        // if ( skp_is_customizing() ) {
        //     return $link;
        // }
        switch( $value['link-to'] ) {
            case 'url' :
                if ( !empty( $value['link-pick-url'] ) && !empty( $value['link-pick-url']['id'] ) ) {
                    if ( '_custom_' == $value['link-pick-url']['id']  && !empty( $value['link-custom-url'] ) ) {
                        $custom_url = apply_filters( 'nimble_parse_template_tags', $value['link-custom-url'] );
                        $link = $custom_url;
                    } else if ( !empty( $value['link-pick-url']['url'] ) ) {
                        $link = $value['link-pick-url']['url'];
                    }
                }
            break;
            case 'img-file' :
            case 'img-lightbox' :
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
    $to_render = apply_filters('nb_img_module_html', sek_get_img_module_img_html( $main_settings ), $main_settings );
    echo apply_filters( 'nimble_parse_for_smart_load', wp_kses_post($to_render));
} else {
    $link = sek_get_img_module_img_link( $main_settings );
    $to_render = sprintf('<a class="%4$s %5$s" href="%1$s" %2$s>%3$s</a>',
        esc_url($link),
        true === sek_booleanize_checkbox_val( $main_settings['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
        apply_filters('nb_img_module_html', sek_get_img_module_img_html( $main_settings ), $main_settings ),
        'sek-link-to-'.esc_attr($main_settings['link-to']), // sek-link-to-img-lightbox
        false === strpos($link,'http') ? 'sek-no-img-link' : ''
    );
    echo apply_filters( 'nimble_parse_for_smart_load', wp_kses_post($to_render));
}
if ( 'img-lightbox' === $main_settings['link-to'] ) {
    sek_emit_js_event('nb-needs-swipebox');
}