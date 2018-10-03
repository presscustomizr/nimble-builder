<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_widths',
        'name' => __('Width settings of the sections in the current page', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(
        //     'outer-section-width' => '100%',
        //     'inner-section-width' => '100%'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom outer width for the sections of this page', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('This option will be inherited by all Nimble sections of the currently previewed page, unless for sections with a specific width option.')
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom inner width for the sections of this page', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('This option will be inherited by all Nimble sections of the currently previewed page, unless for sections with a specific width option.')
                )
            )
        )//tmpl
    );
}


// add user local custom css
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_local_widths_css' );
//@filter 'nimble_get_dynamic_stylesheet'
function sek_add_raw_local_widths_css( $css ) {
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $local_options = sek_get_skoped_seks( !empty( $_POST['skope_id'] ) ? $_POST['skope_id'] : skp_build_skope_id() );
    if ( is_array( $local_options ) && !empty( $local_options['options']) && ! empty( $local_options['options']['widths'] ) ) {
        $width_options = $local_options['options']['widths'];

        if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
            if ( ! empty( $width_options['outer-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $width_options['outer-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $width_options['outer-section-width'] );
                      $css .= sprintf( '.sektion-wrapper [data-sek-level="section"]{max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
        }
        if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
            if ( ! empty( $width_options['inner-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $width_options[ 'inner-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $width_options[ 'inner-section-width'] );
                      $css .= sprintf( '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner {max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
        }
    }
    return $css;
}
?>