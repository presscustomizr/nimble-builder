<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'Nimble\sek_get_gal_img_item_html') ) {
    function sek_get_gal_img_item_html( $item, $img_size = null ) {
        $img = $item['img'];
        $img_size = !is_null($img_size) ? $img_size : '';
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
                $html = sprintf('<div style="min-height:50px">%1$s</div>', Nimble_Manager()->sek_get_input_placeholder_content( 'upload' ));
            }
        }

        // Do we have something ? If not print the placeholder
        if ( empty($html) && skp_is_customizing() ) {
            $html = sprintf('<div style="min-height:50px">%1$s</div>', Nimble_Manager()->sek_get_input_placeholder_content( 'upload' ));
        }

        $html = apply_filters( 'nimble_parse_for_smart_load', $html );
        if ( !skp_is_customizing() && false !== strpos($html, 'data-sek-src="http') ) {
            $html = $html.Nimble_Manager()->css_loader_html;
        }
        return $html;
    }
}


if ( !function_exists( 'Nimble\sek_print_gallery_mod' ) ) {
    function sek_print_gallery_mod( $model, $gallery_opts, $gallery_collec = array() ) {
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
        $normalized_columns_by_device = array();
        // normalizes
        foreach ( $columns_by_device as $device => $column_nb ) {
            $column_nb = (int)$column_nb;
            if ( !empty( $column_nb ) ) {
              $column_nb = $column_nb > 12 ? 12 : $column_nb;
              $column_nb = $column_nb < 1 ? 1 : $column_nb;
            }
            $normalized_columns_by_device[$device] = $column_nb;
        }
        $gal_wrapper_classes = [];
        $gal_wrapper_classes = apply_filters('nb_gal_wrapper_classes', $gal_wrapper_classes, $model );
        $gal_wrapper_classes = implode(' ', $gal_wrapper_classes );

        $gal_items_classes = [];
        $gal_items_classes = apply_filters('nb_gal_item_classes', $gal_items_classes, $model );
        $gal_items_classes = implode(' ', $gal_items_classes );
        // wrapper should be data-sek-gallery-id, used as 'css_selectors' on registration
        ?>
        
        <div class="sek-gal-wrapper <?php echo $gal_wrapper_classes; ?>" id="<?php echo $model['id']; ?>">
            <div class="sek-gal-items <?php echo $gal_items_classes; ?>">
                <?php foreach ( $gallery_collec as $index => $item ) : ?>
                    <figure class="sek-img-gal-item" data-sek-item-id="<?php echo $item['id']; ?>">
                        <?php echo sek_get_gal_img_item_html( $item ); ?>
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
            'background: url(' . NIMBLE_MODULE_ICON_PATH . 'Nimble_gallery_icon.svg) no-repeat 50% 75%;background-size: 170px;'
        );
    }
}