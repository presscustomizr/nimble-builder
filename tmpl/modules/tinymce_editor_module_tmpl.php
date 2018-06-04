<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Front() -> model;
$module_type = $model['module_type'];
// print the module content if not empty
if ( ! array_key_exists( 'value', $model ) ) {
    // print the placeholder for each input_types
    $tmpl_data = sek_get_registered_module_type_property( $module_type, 'tmpl' );
    // foreach( $tmpl_data['item-inputs'] as $input_id => $input_data ) {
    //     echo SEK_Front() -> sek_get_input_placeholder_content( $input_data['input_type'], $input_id );
    // }
} else {
    if ( array_key_exists('content', $model['value'] ) ) {
        SEK_Front() -> sek_print_tiny_mce_text_content( $model['value']['content'], 'content', $model );
    }
}