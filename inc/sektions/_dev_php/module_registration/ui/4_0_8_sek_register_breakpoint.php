<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_breakpoint_module() {
    $global_custom_breakpoint = sek_get_global_custom_breakpoint();
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_breakpoint_module',
        'name' => __('Set a custom breakpoint', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                  'use-custom-breakpoint' => array(
                      'input_type'  => 'gutencheck',
                      'title'       => __('Use a custom breakpoint for the vertical reorganization of columns', 'text_domain_to_be_replaced'),
                      'default'     => 0,
                      'title_width' => 'width-80',
                      'input_width' => 'width-20',
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true
                  ),
                  'custom-breakpoint'  => array(
                      'input_type'  => 'range_simple',
                      'title'       => __( 'Define a custom breakpoint in pixels', 'text_domain_to_be_replaced' ),
                      'default'     => $global_custom_breakpoint > 0 ? $global_custom_breakpoint : 768,
                      'min'         => 1,
                      'max'         => 2000,
                      'step'        => 1,
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true,
                      //'css_identifier' => 'letter_spacing',
                      'width-100'   => true,
                      'title_width' => 'width-100',
                      'notice_after' => __( 'This is the breakpoint under which columns are reorganized vertically. The default breakpoint is 768px.', 'text_domain_to_be_replaced')
                  )//0,
            )
        )//tmpl
    );
}
/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_sections_breakpoint', 10, 3 );
function sek_add_css_rules_for_sections_breakpoint( $rules, $section ) {
    if ( ! is_array( $section ) )
      return $rules;
    // this filter is fired for all level types. Make sure we filter only the sections.
    if ( empty( $section['level'] ) || 'section' !== $section['level'] )
      return $rules;

    $custom_breakpoint = intval( sek_get_section_custom_breakpoint( $section ) );

    if ( $custom_breakpoint < 1 )
      return $rules;

    $col_number = ( array_key_exists( 'collection', $section ) && is_array( $section['collection'] ) ) ? count( $section['collection'] ) : 1;
    $col_number = 12 < $col_number ? 12 : $col_number;
    $col_width_in_percent = 100/$col_number;
    $col_suffix = floor( $col_width_in_percent );

    $responsive_css_rules = "flex: 0 0 {$col_suffix}%;max-width: {$col_suffix}%;";
    $rules[] = array(
        'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner > .sek-section-custom-breakpoint-col-'.$col_suffix,
        'css_rules' => $responsive_css_rules,
        'mq' => "(min-width: {$custom_breakpoint}px)"
    );
    return $rules;
}

?>