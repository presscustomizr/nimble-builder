<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}


if ( !function_exists( 'Nimble\sek_print_gallery_mod' ) ) {
    function sek_print_gallery_mod( $gallery_opts, $model, $gallery_collec = array() ) {
        $gallery_collec = is_array( $gallery_collec ) ? $gallery_collec : array();
  
        $is_accordion_multi_item = count( $gallery_collec ) > 1;

        echo 'THE GALLERY !!';

        // wrapper should be data-sek-gallery-id, used as 'css_selectors' on registration

    }
}


$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$gallery_collec = !empty($value['gallery_collec']) ? $value['gallery_collec'] : array();
$gallery_opts = !empty($value['gallery_opts']) ? $value['gallery_opts'] : array();

if ( !empty( $gallery_collec ) ) {
    sek_print_gallery_mod( $gallery_opts, $model, $gallery_collec );
    sek_emit_js_event('nb-needs-gallery');
} else {
    if ( skp_is_customizing() ) {
        printf( '<div class="sek-mod-preview-placeholder"><div class="sek-preview-ph-text" style="%2$s"><p>%1$s</p></div></div>',
            __('Click to start adding images.', 'text_doma'),
            'background: url(' . NIMBLE_MODULE_ICON_PATH . 'Nimble_gallery_icon.svg) no-repeat 50% 75%;background-size: 170px;'
        );
    }
}