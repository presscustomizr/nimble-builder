<?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_spacing_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_spacing_module',
        'name' => __('Spacing options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',

        'tmpl' => array(
            'item-inputs' => array(
                'pad_marg' => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __('Set padding and margin', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'default'     => array( 'desktop' => array() ),
                    'has_device_switcher' => true
                )
            )
        )
    );
}





/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_spacing', 10, 2 );
// hook : sek_dyn_css_builder_rules
// @return array() of css rules
function sek_add_css_rules_for_spacing( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    //spacing
    if ( empty( $options[ 'spacing' ] ) || empty( $options[ 'spacing' ][ 'pad_marg' ] ) )
      return $rules;
    $pad_marg_options = $options[ 'spacing' ][ 'pad_marg' ];
    // array( desktop => array( margin-right => 10, padding-top => 5, unit => 'px' ) )
    if ( ! is_array( $pad_marg_options ) )
      return $rules;

    $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $pad_marg_options, '[data-sek-id="'.$level['id'].'"]' );

    // if the column has a positive ( > 0 ) margin-right and / or a margin-left set , let's adapt the column widths so we fit the 100%
    if ( 'column' === $level['level'] && ! empty( $pad_marg_options['desktop'] ) ) {
        $margin_left = array_key_exists('margin-left', $pad_marg_options['desktop'] ) ? $pad_marg_options['desktop']['margin-left'] : 0;
        $margin_right = array_key_exists('margin-right', $pad_marg_options['desktop'] ) ? $pad_marg_options['desktop']['margin-right'] : 0;
        $device_unit = array_key_exists('unit', $pad_marg_options['desktop'] ) ? $pad_marg_options['desktop']['unit'] : 'px';

        $total_horizontal_margin = (int)$margin_left + (int)$margin_right;

        $parent_section = sek_get_parent_level_model( $level['id'] );

        if ( $total_horizontal_margin > 0 && !empty( $parent_section ) ) {
            $total_horizontal_margin_with_unit = $total_horizontal_margin . $device_unit;//20px


            $col_number = ( array_key_exists( 'collection', $parent_section ) && is_array( $parent_section['collection'] ) ) ? count( $parent_section['collection'] ) : 1;
            $col_number = 12 < $col_number ? 12 : $col_number;

            $col_width_in_percent = 100/$col_number;
            $col_suffix = floor( $col_width_in_percent );
            //sek_error_log('$parent_section', $parent_section );

            // Do we have a custom width for the column ?
            // if not, let's get the col suffix from the parent section
            $custom_width   = ( ! empty( $level[ 'width' ] ) && is_numeric( $level[ 'width' ] ) ) ? $level['width'] : null;
            if ( ! is_null( $custom_width ) ) {
                $col_width_in_percent = $custom_width;
            }

            // bail here if we messed up the column width
            if ( $col_suffix < 1 )
              return $rules;

            // define a default breakpoint : 768
            $breakpoint = Sek_Dyn_CSS_Builder::$breakpoints[ Sek_Dyn_CSS_Builder::COLS_MOBILE_BREAKPOINT ];//COLS_MOBILE_BREAKPOINT = 'md' <=> 768px

            // Is there a global custom breakpoint set ?
            $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
            $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;

            // Does the parent section have a custom breakpoint set ?
            $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( $parent_section ) );
            $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

            if ( $has_section_custom_breakpoint ) {
                $breakpoint = $section_custom_breakpoint;
            } else if ( $has_global_custom_breakpoint ) {
                $breakpoint = $global_custom_breakpoint;
            }

            $responsive_css_rules = sprintf( '-ms-flex: 0 0 calc(%1$s%% - %2$s) ;flex: 0 0 calc(%1$s%% - %2$s);max-width: calc(%1$s%% - %2$s)', $col_width_in_percent, $total_horizontal_margin_with_unit );

            // we need to override the rule defined in : Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
            // that's why we use a long specific selector here
            $rules[] = array(
                'selector' => sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] ),
                'css_rules' => $responsive_css_rules,
                'mq' => "(min-width: {$breakpoint}px)"
            );
            //sek_error_log('padding margin', $rules );
        }//if ( $total_horizontal_margin > 0 && !empty( $parent_section ) ) {
    }// if column

    return $rules;
}

?>