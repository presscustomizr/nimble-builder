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
                'use-custom-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define custom outer and inner widths for the sections site wide', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('Those width options will be applied by default to all Nimble sections of your site, unless a page or a section has specific width options.')
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Outer sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Inner sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true
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
        if ( ! empty( $global_options['widths'][ 'use-custom-width' ] ) && true === sek_booleanize_checkbox_val( $global_options['widths'][ 'use-custom-width' ] ) ) {
            if ( ! empty( $global_options['widths'][ 'outer-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $global_options['widths'][ 'outer-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $global_options['widths'][ 'outer-section-width'] );
                      $css .= sprintf( '[data-sek-level="section"]{max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
            if ( ! empty( $global_options['widths'][ 'inner-section-width'] ) ) {
                  $numeric = sek_extract_numeric_value( $global_options['widths'][ 'inner-section-width'] );
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $global_options['widths'][ 'inner-section-width'] );
                      $css .= sprintf( '[data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner {max-width:%1$s%2$s;margin: 0 auto;}', $numeric, $unit );
                  }
            }
        }
    }
    printf('<style type="text/css" id="nimble-global-options">%1$s</style>', $css );
}
?>