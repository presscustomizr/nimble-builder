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
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : null;
// print the module content if not empty
if ( is_null( $value ) || ! array_key_exists('img', $value ) ) {
    // print the placeholder for each input_types
    $tmpl_data = sek_get_registered_module_type_property( $module_type, 'tmpl' );
    echo SEK_Front() -> sek_get_input_placeholder_content( 'upload' );
} else {
    if ( array_key_exists('img', $value ) ) {
        echo wp_get_attachment_image( $value['img'], $value['img-size']);
    }
}

