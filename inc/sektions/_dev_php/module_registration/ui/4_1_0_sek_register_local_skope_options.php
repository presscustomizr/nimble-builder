<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_skope_options_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_skope_options_module',
        'name' => __('Options for the sections of the current page', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_domain_to_be_replaced' ) ),
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_template' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select a template', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'local_template' ),
                    'refresh_preview' => true
                ),
                'local_custom_css' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Custom css' , 'text_domain_to_be_replaced' ),
                    'code_type' => 'text/css',// 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
                    'notice_after' => __('This CSS code will be restricted to the currently previewed page only, and not applied site wide.', 'text_domain_to_be_replaced'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                )
            )
        )//tmpl
    );
}


// add user local custom css
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_local_custom_css' );
//@filter 'nimble_get_dynamic_stylesheet'
function sek_add_raw_local_custom_css( $css ) {
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $localSkopeNimble = sek_get_skoped_seks( !empty( $_POST['skope_id'] ) ? $_POST['skope_id'] : skp_build_skope_id()  );
    if ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['options']) && ! empty( $localSkopeNimble['options']['general'] ) && ! empty( $localSkopeNimble['options']['general']['local_custom_css'] ) ) {
        $css .= $localSkopeNimble['options']['general']['local_custom_css'];
    }
    return $css;
}
?>