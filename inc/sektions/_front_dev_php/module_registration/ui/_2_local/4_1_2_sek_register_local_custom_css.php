<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_custom_css() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_custom_css',
        //'name' => __('Custom CSS for the sections of the current page', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_custom_css' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Custom css' , 'text_doma' ),
                    'default'     => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) ),
                    'code_type' => 'text/css',// 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
                    'notice_before_title' => __('The CSS code added below will only be applied to the currently previewed page, not site wide.', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => false,
                    'refresh_css_via_post_message' => true
                )
            )
        )//tmpl
    );
}


// add user local custom css
// this filter is declared in Sek_Dyn_CSS_Builder::get_stylesheet() with 2 parameters
// apply_filters( 'nimble_get_dynamic_stylesheet', $css, $this->is_global_stylesheet );
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_local_custom_css', 10, 2 );
//@filter 'nimble_get_dynamic_stylesheet'
function sek_add_raw_local_custom_css( $css, $is_global_stylesheet ) {
    // the local custom css must be restricted to the local stylesheet
    if ( $is_global_stylesheet )
      return $css;
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? sanitize_text_field($_POST['local_skope_id']) : skp_build_skope_id() );
    if ( is_array( $local_options ) && !empty( $local_options['local_options']) && !empty( $local_options['local_options']['custom_css'] ) ) {
        $options = $local_options['local_options']['custom_css'];
        if ( !empty( $options['local_custom_css'] ) ) {
            $css .= $options['local_custom_css'];
        }
    }
    return $css;
}
?>