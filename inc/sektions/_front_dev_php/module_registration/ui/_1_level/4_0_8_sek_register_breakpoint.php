<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_breakpoint_module() {
    $global_custom_breakpoint = sek_get_global_custom_breakpoint();
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_breakpoint_module',
        //'name' => __('Set a custom breakpoint', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                  'use-custom-breakpoint' => array(
                      'input_type'  => 'nimblecheck',
                      'title'       => __('Use a custom breakpoint for responsive columns', 'text_doma'),
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
                      'notice_after' => __( 'This is the viewport width from which columns are rearranged vertically. The default breakpoint is 768px.', 'text_doma')
                  ),//0,
                  'apply-to-all' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply this breakpoint to all by-device customizations', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf(
                        __( '%s When enabled, this custom breakpoint is applied not only to responsive columns but also to all by-device customizations, like alignment for example.', 'text_doma'),
                        '<span class="sek-mobile-device-icons"><i class="sek-switcher preview-desktop"></i>&nbsp;<i class="sek-switcher preview-tablet"></i>&nbsp;<i class="sek-switcher preview-mobile"></i></span>'
                    )
                  ),
                  'reverse-col-at-breakpoint' => array(
                      'input_type'  => 'nimblecheck',
                      'title'       => __('Reverse the columns direction on devices smaller than the breakpoint.', 'text_doma'),
                      'default'     => 0,
                      'title_width' => 'width-80',
                      'input_width' => 'width-20',
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true
                  )
            )
        )//tmpl
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for__section__options', '\Nimble\sek_add_css_rules_for_sections_breakpoint', 10, 3 );
function sek_add_css_rules_for_sections_breakpoint( $rules, $section ) {
    // nested section should inherit the custom breakpoint of the parent
    // @fixes https://github.com/presscustomizr/nimble-builder/issues/554
    // Is there a custom breakpoint set by a parent section?
    // Order :
    // 1) custom breakpoint set on a nested section
    // 2) custom breakpoint set on a regular section

    // the 'for_responsive_columns' param has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
    // so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
    // when for columns, we always apply the custom breakpoint defined by the user
    // otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
    // 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
    $custom_breakpoint = intval( sek_get_closest_section_custom_breakpoint( array(
        'searched_level_id' => $section['id'],
        'for_responsive_columns' => true
    )));
    // sek_error_log('SECTION ??', $section );
    // sek_error_log('$custom_breakpoint??', is_int($custom_breakpoint) );

    if ( is_int($custom_breakpoint) && $custom_breakpoint > 0 ) {
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
        $breakpoint = $breakpoint - 1;//fixes https://github.com/presscustomizr/nimble-builder/issues/559

        // selector uses ">" syntax to make sure the reverse-column rule is not inherited by a nested section
        $rules[] = array(
            'selector' => '[data-sek-id="'.$section['id'].'"] > .sek-container-fluid > .sek-sektion-inner',
            'css_rules' => "-ms-flex-direction: column-reverse;flex-direction: column-reverse;",
            'mq' => "(max-width: {$breakpoint}px)"
        );

        // when using column-reverse for the parent section we need to set the flex:auto to children column
        // otherwise, columns may lose their height. See https://github.com/presscustomizr/nimble-builder/issues/622
        $rules[] = array(
            'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner > [data-sek-level="column"]',
            'css_rules' => '-webkit-box-flex: 1;-ms-flex: auto;flex: auto;',
            'mq' => "(max-width: {$breakpoint}px)"
        );
    }


    return $rules;
}

?>