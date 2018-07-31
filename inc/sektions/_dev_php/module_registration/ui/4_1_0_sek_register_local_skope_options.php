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
                'use-custom-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define custom outer and inner widths for the sections of this page', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Outer sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Inner sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
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
    $local_options = sek_get_skoped_seks( !empty( $_POST['skope_id'] ) ? $_POST['skope_id'] : skp_build_skope_id() );
    if ( is_array( $local_options ) && !empty( $local_options['options']) && ! empty( $local_options['options']['general'] ) ) {
        $general_options = $local_options['options']['general'];
        if ( ! empty( $general_options['local_custom_css'] ) ) {
            $css .= $general_options['local_custom_css'];
        }

        if ( ! empty( $general_options[ 'use-custom-width' ] ) && true === sek_booleanize_checkbox_val( $general_options[ 'use-custom-width' ] ) ) {
            if ( ! empty( $general_options['outer-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $general_options['outer-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $general_options['outer-section-width'] );
                      $css .= sprintf( '.sektion-wrapper [data-sek-level="section"]{max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
            if ( ! empty( $general_options[ 'inner-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $general_options[ 'inner-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $general_options[ 'inner-section-width'] );
                      $css .= sprintf( '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner {max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
        }
    }
    return $css;
}
?>