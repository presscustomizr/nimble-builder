<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_widths',
        'name' => __('Site wide width options', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom outer width for the sections site wide', 'text_domain_to_be_replaced'),
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
                    'notice_after' => __('This option will be inherited by all Nimble sections of your site, unless for pages or sections with specific width options.')
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom inner width for the sections site wide', 'text_domain_to_be_replaced'),
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
                    'notice_after' => __('This option will be inherited by all Nimble sections of your site, unless for pages or sections with specific width options.')
                )
            )
        )//tmpl
    );
}


add_action('wp_head', '\Nimble\sek_write_global_custom_section_widths', 1000 );
function sek_write_global_custom_section_widths() {
    $css = '';

    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    if ( is_array( $global_options ) && ! empty( $global_options['widths'] ) ) {
        $width_options = $global_options['widths'];
        if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
            if ( ! empty( $width_options[ 'outer-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $width_options[ 'outer-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $width_options[ 'outer-section-width'] );
                      $css .= sprintf( '[data-sek-level="section"]{max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
        }
        if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
            if ( ! empty( $width_options[ 'inner-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $width_options[ 'inner-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $width_options[ 'inner-section-width'] );
                      $css .= sprintf( '[data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner {max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
        }
    }
    printf('<style type="text/css" id="nimble-global-options">%1$s</style>', $css );
}
?>