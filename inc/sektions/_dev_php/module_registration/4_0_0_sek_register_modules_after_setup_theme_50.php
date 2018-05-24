<?php
// The base fmk is loaded on after_setup_theme before 50
add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_register_modules() {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }

    foreach( [
        'sek_module_picker_module',
        //'sek_section_picker_module',
        'sek_level_bg_border_module',
        'sek_level_section_layout_height_module',
        'sek_spacing_module',
        //'czr_simple_html_module',
        'czr_tiny_mce_editor_module',
        'czr_image_module',
        //'czr_featured_pages_module'
    ] as $module_name ) {
        $fn = "sek_get_module_params_for_{$module_name}";
        if ( function_exists( $fn ) ) {
            $params = $fn();
            if ( is_array( $params ) ) {
                $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( $params );
            } else {
                error_log( __FUNCTION__ . ' Module registration params should be an array');
            }
        } else {
            error_log( __FUNCTION__ . ' missing params callback fn for module ' . $module_name );
        }
    }

}//sek_register_modules()

?>