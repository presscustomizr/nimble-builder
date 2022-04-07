<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'Nimble\sek_get_gal_img_item_html') ) {
    function sek_get_gal_img_item_html( $item, $gallery_opts ) {
        $item = is_array( $item ) ? $item : [];
        $gallery_opts = is_array( $gallery_opts ) ? $gallery_opts : [];
        $img = $item['img'];
        $img_size = array_key_exists('img_size', $gallery_opts ) ? $gallery_opts['img_size'] : 'large';
        $img_size = ( !is_null($img_size) && is_string( $img_size ) ) ? $img_size : '';
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
                $html = sprintf( '<img alt="default img" data-skip-lazyload="true" src="%1$s" title="%2$s"/>',
                    esc_url( $img ),
                    esc_attr(sek_get_gal_img_title( $item, $gallery_opts ))
                );
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
        return $html;
    }
}

if ( !function_exists( 'Nimble\sek_get_gal_img_link' ) ) {
    function sek_get_gal_img_link( $item, $opts ) {
        $link = 'javascript:void(0);';
        // if ( skp_is_customizing() ) {
        //     return $link;
        // }
        switch( $opts['link-to'] ) {
            case 'url' :
                if ( !empty( $opts['link-pick-url'] ) && !empty( $opts['link-pick-url']['id'] ) ) {
                    if ( '_custom_' == $opts['link-pick-url']['id']  && !empty( $opts['link-custom-url'] ) ) {
                        $custom_url = apply_filters( 'nimble_parse_template_tags', $opts['link-custom-url'] );
                        $link = $custom_url;
                    } else if ( !empty( $opts['link-pick-url']['url'] ) ) {
                        $link = esc_url( $opts['link-pick-url']['url'] );
                    }
                }
            break;
            case 'img-file' :
            case 'img-lightbox' :
                if ( is_int( $item['img'] ) ) {
                    $link = wp_get_attachment_url( $item['img'] );
                }
            break;
            case 'img-page' :
                if ( is_int( $item['img'] ) ) {
                    $link = get_attachment_link( $item['img'] );
                }
            break;
        }
        return $link;
    }
}


if ( !function_exists( 'Nimble\sek_get_gal_img_title' ) ) {
    function sek_get_gal_img_title( $item, $opts ) {
        $title = '';
        $img = $item['img'];
        if ( !empty($item['custom_caption']) ) {
            $title = strip_tags( $item['custom_caption'] );
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
        return $title;
    }
}



if ( !function_exists( 'Nimble\sek_print_gallery_mod' ) ) {
    function sek_print_gallery_mod( $model, $gallery_opts, $gallery_collec = array() ) {
        $value = array_key_exists( 'value', $model ) ? $model['value'] : array();
        $gallery_collec = is_array( $gallery_collec ) ? $gallery_collec : array();
        $gallery_opts = is_array( $gallery_opts ) ? $gallery_opts : array();
  
        $is_gallery_multi_item = count( $gallery_collec ) > 1;

        $columns_by_device = $gallery_opts['columns'];

        $columns_by_device = is_array( $columns_by_device ) ? $columns_by_device : array();
        $columns_by_device = wp_parse_args( $columns_by_device, array(
            'desktop' => 2,
            'tablet' => '',
            'mobile' => ''
        ));

        $gal_wrapper_classes = [];
        $gal_wrapper_classes = apply_filters('nb_gal_wrapper_classes', $gal_wrapper_classes, $value );
        $gal_wrapper_classes = implode(' ', $gal_wrapper_classes );

        $normalized_columns_by_device = array();

        // normalizes
        foreach ( $columns_by_device as $device => $column_nb ) {
            $column_nb = (int)$column_nb;
            if ( !empty( $column_nb ) ) {
              $column_nb = $column_nb > 24 ? 24 : $column_nb;
              $column_nb = $column_nb < 1 ? 1 : $column_nb;
            }
            $normalized_columns_by_device[$device] = $column_nb;
        }
        $gal_items_classes = 'img-lightbox' === $gallery_opts['link-to'] ? ['sek-gallery-lightbox'] : [];
        foreach ( $normalized_columns_by_device as $device => $column_nb ) {
            if ( empty( $column_nb ) )
              continue;
            $gal_items_classes[] = "sek-{$device}-col-{$column_nb}";
            if ( 'desktop' === $device ) {
              $gal_items_classes[] = "sek-all-col-{$column_nb}";
            }
        }

        $gal_items_classes = apply_filters('nb_gal_item_classes', $gal_items_classes, $value );
        $gal_items_classes = implode(' ', $gal_items_classes );

        if ( 'img-lightbox' === $gallery_opts['link-to'] ) {
            sek_emit_js_event('nb-needs-swipebox');
        }
        // wrapper should be data-sek-gallery-id, used as 'css_selectors' on registration
        do_action( 'nb_before_post_gal_wrapper' );
        ?>
        
        <div class="sek-gal-wrapper <?php echo esc_attr($gal_wrapper_classes); ?>" id="<?php echo esc_attr($model['id']); ?>">
            <div class="sek-gal-items <?php echo esc_attr($gal_items_classes); ?>">
                <?php foreach ( $gallery_collec as $index => $item ) : ?>
                    <figure class="sek-img-gal-item" data-sek-item-id="<?php echo esc_attr($item['id']); ?>">
                        <?php
                            if ( 'no-link' === $gallery_opts['link-to'] ) {
                                $html = sek_get_gal_img_item_html( $item, $gallery_opts );
                                echo apply_filters( 'nimble_parse_for_smart_load', wp_kses_post($html) );
                                if ( !skp_is_customizing() && false !== strpos($html, 'data-sek-src="http') ) {
                                    echo '<div class="sek-css-loader sek-mr-loader"><div></div><div></div><div></div></div>';
                                }
                            } else {
                                $link = sek_get_gal_img_link( $item, $gallery_opts );
                                $html = sek_get_gal_img_item_html( $item, $gallery_opts );

                                printf('<a class="%4$s %5$s" href="%1$s" %2$s title="%6$s">%3$s</a>',
                                    esc_url($link),
                                    true === sek_booleanize_checkbox_val( $gallery_opts['link-target'] ) ? 'target="_blank" rel="noopener noreferrer"' : '',
                                    apply_filters( 'nimble_parse_for_smart_load', wp_kses_post($html) ),
                                    esc_attr( 'sek-gal-link-to-'.$gallery_opts['link-to'] ), // sek-gal-link-to-img-lightbox
                                    false === strpos($link,'http') ? 'sek-no-img-link' : 'sek-gal-img-has-link',
                                    esc_attr(sek_get_gal_img_title( $item, $gallery_opts ))
                                );
                            }
                        ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}


$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$gallery_collec = !empty($value['gallery_collec']) ? $value['gallery_collec'] : array();
$gallery_opts = !empty($value['gallery_opts']) ? $value['gallery_opts'] : array();

if ( !empty( $gallery_collec ) ) {
    sek_emit_js_event('nb-needs-gallery');
    sek_print_gallery_mod( $model, $gallery_opts, $gallery_collec );
} else {
    if ( skp_is_customizing() ) {
        printf( '<div class="sek-mod-preview-placeholder"><div class="sek-preview-ph-text" style="%2$s"><p>%1$s</p></div></div>',
            __('Click to start adding images.', 'text_doma'),
            'background: url(' . esc_url(NIMBLE_MODULE_ICON_PATH) . 'Nimble_gallery_icon.svg) no-repeat 50% 75%;background-size: 170px;'
        );
    }
}