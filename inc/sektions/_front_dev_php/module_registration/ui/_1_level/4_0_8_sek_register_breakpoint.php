<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_breakpoint_module() {
    $global_custom_breakpoint = sek_get_global_custom_breakpoint();
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_breakpoint_module',
        'name' => __('Set a custom breakpoint', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                  'use-custom-breakpoint' => array(
                      'input_type'  => 'nimblecheck',
                      'title'       => __('Use a custom breakpoint for the vertical reorganization of columns', 'text_doma'),
                      'default'     => 0,
                      'title_width' => 'width-80',
                      'input_width' => 'width-20',
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true
                  ),
                  'custom-breakpoint'  => array(
                      'input_type'  => 'range_simple',
                      'title'       => __( 'Define a custom breakpoint in pixels', 'text_doma' ),
                      'default'     => $global_custom_breakpoint > 0 ? $global_custom_breakpoint : 768,
                      'min'         => 1,
                      'max'         => 2000,
                      'step'        => 1,
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true,
                      //'css_identifier' => 'letter_spacing',
                      'width-100'   => true,
                      'title_width' => 'width-100',
                      'notice_after' => __( 'This is the breakpoint under which columns are reorganized vertically. The default breakpoint is 768px.', 'text_doma')
                  ),//0,
                  'reverse-col-at-breakpoint' => array(
                      'input_type'  => 'nimblecheck',
                      'title'       => __('Reverse the columns direction on devices smaller than the breakpoint.', 'text_doma'),
                      'default'     => 0,
                      'title_width' => 'width-80',
                      'input_width' => 'width-20',
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true
                  ),
            )
        )//tmpl
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for__section__options', '\Nimble\sek_add_css_rules_for_sections_breakpoint', 10, 3 );
function sek_add_css_rules_for_sections_breakpoint( $rules, $section ) {
    $custom_breakpoint = intval( sek_get_section_custom_breakpoint( $section ) );
    if ( $custom_breakpoint > 0 ) {
        $col_number = ( array_key_exists( 'collection', $section ) && is_array( $section['collection'] ) ) ? count( $section['collection'] ) : 1;
        $col_number = 12 < $col_number ? 12 : $col_number;
        $col_width_in_percent = 100/$col_number;
        $col_suffix = floor( $col_width_in_percent );

        $responsive_css_rules = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $col_suffix );
        $rules[] = array(
            'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner > .sek-section-custom-breakpoint-col-'.$col_suffix,
            'css_rules' => $responsive_css_rules,
            'mq' => "(min-width: {$custom_breakpoint}px)"
        );
    }

    // maybe set the reverse column order on mobile devices ( smaller than the breakpoint )
    if ( isset( $section[ 'options' ] ) && isset( $section[ 'options' ]['breakpoint'] ) && array_key_exists( 'reverse-col-at-breakpoint', $section[ 'options' ]['breakpoint'] ) ) {
        $default_md_breakpoint = '768';
        if ( class_exists('\Nimble\Sek_Dyn_CSS_Builder') ) {
            $default_md_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];
        }
        $breakpoint = $custom_breakpoint > 0 ? $custom_breakpoint : $default_md_breakpoint;
        $responsive_css_rules = "-ms-flex-direction: column-reverse;flex-direction: column-reverse;";
        $rules[] = array(
            'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner',
            'css_rules' => $responsive_css_rules,
            'mq' => "(max-width: {$breakpoint}px)"
        );
    }


    return $rules;
}

?>