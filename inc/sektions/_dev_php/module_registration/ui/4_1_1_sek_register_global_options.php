<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_options_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_options_module',
        'name' => __('Site wide options', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'global-custom-breakpoint'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Define a custom breakpoint in pixels', 'text_domain_to_be_replaced' ),
                    'default'     => 768,
                    'min'         => 0,
                    'max'         => 2000,
                    'step'        => 1,
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __( 'This is the breakpoint under which columns are reorganized vertically. The default breakpoint is 768px.', 'text_domain_to_be_replaced')
                ),//0,
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Outer sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true,
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Inner sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true,
                )
            )
        )//tmpl
    );
}


add_action('wp_head', '\Nimble\sek_write_global_custom_breakpoint_rules', 1000 );
function sek_write_global_custom_breakpoint_rules() {
    // delete_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    $custom_breakpoint = sek_get_global_custom_breakpoint();
    if ( $custom_breakpoint < 0 )
      return;
      ?>
      <style type="text/css">
          @media (min-width:<?php echo $custom_breakpoint; ?>px) {.sek-custom-global-col-8 {-ms-flex: 0 0 8.333%;flex: 0 0 8.333%;max-width: 8.333%;}.sek-custom-global-col-9 {-ms-flex: 0 0 9.090909%;flex: 0 0 9.090909%;max-width: 9.090909%;}.sek-custom-global-col-10 {-ms-flex: 0 0 10%;flex: 0 0 10%;max-width: 10%;}.sek-custom-global-col-11 {-ms-flex: 0 0 11.111%;flex: 0 0 11.111%;max-width: 11.111%;}.sek-custom-global-col-12 {-ms-flex: 0 0 12.5%;flex: 0 0 12.5%;max-width: 12.5%;}.sek-custom-global-col-14 {-ms-flex: 0 0 14.285%;flex: 0 0 14.285%;max-width: 14.285%;}.sek-custom-global-col-16 {-ms-flex: 0 0 16.666%;flex: 0 0 16.666%;max-width: 16.666%;}.sek-custom-global-col-20 {-ms-flex: 0 0 20%;flex: 0 0 20%;max-width: 20%;}.sek-custom-global-col-25 {-ms-flex: 0 0 25%;flex: 0 0 25%;max-width: 25%;}.sek-custom-global-col-30 {-ms-flex: 0 0 30%;flex: 0 0 30%;max-width: 30%;}.sek-custom-global-col-33 {-ms-flex: 0 0 33.333%;flex: 0 0 33.333%;max-width: 33.333%;}.sek-custom-global-col-40 {-ms-flex: 0 0 40%;flex: 0 0 40%;max-width: 40%;}.sek-custom-global-col-50 {-ms-flex: 0 0 50%;flex: 0 0 50%;max-width: 50%;}.sek-custom-global-col-60 {-ms-flex: 0 0 60%;flex: 0 0 60%;max-width: 60%;}.sek-custom-global-col-66 {-ms-flex: 0 0 66.666%;flex: 0 0 66.666%;max-width: 66.666%;}.sek-custom-global-col-70 {-ms-flex: 0 0 70%;flex: 0 0 70%;max-width: 70%;}.sek-custom-global-col-75 {-ms-flex: 0 0 75%;flex: 0 0 75%;max-width: 75%;}.sek-custom-global-col-80 {-ms-flex: 0 0 80%;flex: 0 0 80%;max-width: 80%;}.sek-custom-global-col-83 {-ms-flex: 0 0 83.333%;flex: 0 0 83.333%;max-width: 83.333%;}.sek-custom-global-col-90 {-ms-flex: 0 0 90%;flex: 0 0 90%;max-width: 90%;}.sek-custom-global-col-100 {-ms-flex: 0 0 100%;flex: 0 0 100%;max-width: 100%;}}
      </style>
      <?php
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// add_filter( 'nimble_css_rules_collection_before_printing_stylesheet', '\Nimble\sek_maybe_add_global_custom_section_width' );
// //@filter 'nimble_get_dynamic_stylesheet'
// function sek_maybe_add_global_custom_section_width( $rules ) {
//     $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
//     if ( ! is_array( $global_options ) || empty( $global_options['general'] ) || empty( $global_options['general']['global-custom-breakpoint'] ) )
//       return $rules;

//     $custom_breakpoint = intval( $global_options['general']['global-custom-breakpoint'] );
//     if ( $custom_breakpoint < 0 )
//       return $rules;

//     return $rules;
// }

// add user local custom css
// add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_global_custom_breakpoint_css' );
// //@filter 'nimble_get_dynamic_stylesheet'
// function sek_add_raw_global_custom_breakpoint_css( $css ) {
//     $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
//     if ( ! is_array( $global_options ) || empty( $global_options['general'] ) || empty( $global_options['general']['global-custom-breakpoint'] ) )
//       return $css;

//     $custom_breakpoint = intval( $global_options['general']['global-custom-breakpoint'] );
//     if ( $custom_breakpoint < 0 )
//       return $css;

//     $responsive_css_rules = "@media (min-width: {$custom_breakpoint}px) {.sek-custom-global-col-8 {-ms-flex: 0 0 8.333%;flex: 0 0 8.333%;max-width: 8.333%;}.sek-custom-global-col-9 {-ms-flex: 0 0 9.090909%;flex: 0 0 9.090909%;max-width: 9.090909%;}.sek-custom-global-col-10 {-ms-flex: 0 0 10%;flex: 0 0 10%;max-width: 10%;}.sek-custom-global-col-11 {-ms-flex: 0 0 11.111%;flex: 0 0 11.111%;max-width: 11.111%;}.sek-custom-global-col-12 {-ms-flex: 0 0 12.5%;flex: 0 0 12.5%;max-width: 12.5%;}.sek-custom-global-col-14 {-ms-flex: 0 0 14.285%;flex: 0 0 14.285%;max-width: 14.285%;}.sek-custom-global-col-16 {-ms-flex: 0 0 16.666%;flex: 0 0 16.666%;max-width: 16.666%;}.sek-custom-global-col-20 {-ms-flex: 0 0 20%;flex: 0 0 20%;max-width: 20%;}.sek-custom-global-col-25 {-ms-flex: 0 0 25%;flex: 0 0 25%;max-width: 25%;}.sek-custom-global-col-30 {-ms-flex: 0 0 30%;flex: 0 0 30%;max-width: 30%;}.sek-custom-global-col-33 {-ms-flex: 0 0 33.333%;flex: 0 0 33.333%;max-width: 33.333%;}.sek-custom-global-col-40 {-ms-flex: 0 0 40%;flex: 0 0 40%;max-width: 40%;}.sek-custom-global-col-50 {-ms-flex: 0 0 50%;flex: 0 0 50%;max-width: 50%;}.sek-custom-global-col-60 {-ms-flex: 0 0 60%;flex: 0 0 60%;max-width: 60%;}.sek-custom-global-col-66 {-ms-flex: 0 0 66.666%;flex: 0 0 66.666%;max-width: 66.666%;}.sek-custom-global-col-70 {-ms-flex: 0 0 70%;flex: 0 0 70%;max-width: 70%;}.sek-custom-global-col-75 {-ms-flex: 0 0 75%;flex: 0 0 75%;max-width: 75%;}.sek-custom-global-col-80 {-ms-flex: 0 0 80%;flex: 0 0 80%;max-width: 80%;}.sek-custom-global-col-83 {-ms-flex: 0 0 83.333%;flex: 0 0 83.333%;max-width: 83.333%;}.sek-custom-global-col-90 {-ms-flex: 0 0 90%;flex: 0 0 90%;max-width: 90%;}.sek-custom-global-col-100 {-ms-flex: 0 0 100%;flex: 0 0 100%;max-width: 100%;}}";

//     return $css . $responsive_css_rules;
// }


// add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_global_custom_breakpoint', 10, 3 );
// function sek_add_css_rules_for_global_custom_breakpoint( $rules, $section ) {
//     if ( ! is_array( $section ) )
//       return $rules;
//     // this filter is fired for all level types. Make sure we filter only the sections.
//     if ( empty( $section['level'] ) || 'section' !== $section['level'] )
//       return $rules;

//     $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
//     if ( ! is_array( $global_options ) || empty( $global_options['general'] ) || empty( $global_options['general']['global-custom-breakpoint'] ) )
//       return $rules;

//     $global_custom_breakpoint = $global_options['general']['global-custom-breakpoint'];

//     $options = empty( $section[ 'options' ] ) ? array() : $section['options'];
//     if ( empty( $options[ 'breakpoint' ] ) )
//       return $rules;

//     if ( empty( $options[ 'breakpoint' ][ 'custom-breakpoint' ] ) )
//       return $rules;

//     if ( empty($section['id']) )
//       return $rules;


//     $custom_breakpoint = intval( $options[ 'breakpoint' ][ 'custom-breakpoint' ] );
//     if ( $custom_breakpoint < 0 )
//       return $rules;

//     $col_number = ( array_key_exists( 'collection', $section ) && is_array( $section['collection'] ) ) ? count( $section['collection'] ) : 1;
//     $col_number = 12 < $col_number ? 12 : $col_number;
//     $col_width_in_percent = 100/$col_number;
//     $col_suffix = floor( $col_width_in_percent );

//     $css_rules = 'flex: 0 0 100%;max-width: 100%;';
//     $rules[] = array(
//         'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner > .sek-custom-level-col-'.$col_suffix,
//         'css_rules' => $css_rules,
//         'mq' =>null
//     );

//     $responsive_css_rules = "flex: 0 0 {$col_suffix}%;max-width: {$col_suffix}%;";
//     $rules[] = array(
//         'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner > .sek-custom-level-col-'.$col_suffix,
//         'css_rules' => $responsive_css_rules,
//         'mq' => "(min-width: {$custom_breakpoint}px)"
//     );
//     return $rules;
// }
?>