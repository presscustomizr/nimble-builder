<?php
// The base fmk is loaded @after_setup_theme:10
add_action( 'after_setup_theme', '\Nimble\sek_register_modules', 50 );
function sek_register_modules() {
    foreach( [
        'sek_module_picker_module',
        //'sek_section_picker_module',
        'sek_level_bg_border_module',
        'sek_level_section_layout_module',
        'sek_level_height_module',
        'sek_spacing_module',
        'czr_simple_html_module',
        'czr_tiny_mce_editor_module',
        'czr_image_module',
        'czr_featured_pages_module',
        'czr_heading_module',
        'czr_spacer_module',
        'czr_divider_module',
        'czr_icon_module',
        'czr_map_module'
    ] as $module_name ) {
        $fn = "\Nimble\sek_get_module_params_for_{$module_name}";
        if ( function_exists( $fn ) ) {
            $params = $fn();
            if ( is_array( $params ) ) {
                CZR_Fmk_Base()->czr_pre_register_dynamic_module( $params );
            } else {
                error_log( __FUNCTION__ . ' Module registration params should be an array');
            }
        } else {
            error_log( __FUNCTION__ . ' missing params callback fn for module ' . $module_name );
        }
    }

}//sek_register_modules()

?>