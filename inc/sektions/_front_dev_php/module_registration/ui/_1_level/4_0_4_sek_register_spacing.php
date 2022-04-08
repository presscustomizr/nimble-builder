<?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_spacing_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_spacing_module',
        //'name' => __('Spacing options', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',

        'tmpl' => array(
            'item-inputs' => array(
                'pad_marg' => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __('Set padding and margin', 'text_doma'),
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
    if ( !is_array( $pad_marg_options ) )
      return $rules;

    // GENERATE SPACING RULES BY DEVICE
    // SPECIFIC CASE FOR COLUMNS see https://github.com/presscustomizr/nimble-builder/issues/665
    if ( 'column' === $level['level'] ) {
        // Get the parent section level model
        $parent_column_section = sek_get_parent_level_model( $level['id'] );
        if ( 'no_match' === $parent_column_section ) {
            sek_error_log( __FUNCTION__ . ' => $parent_column_section not found for level id : ' . $level['id'] );
            return $rules;
        }

        // COLUMN BREAKPOINT
        // define a default breakpoint : 768
        $column_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints[Sek_Dyn_CSS_Builder::COLS_MOBILE_BREAKPOINT];//COLS_MOBILE_BREAKPOINT = 'md' <=> 768

        // Is there a global custom breakpoint set ?
        $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
        $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;

        // Does the parent section have a custom breakpoint set ?
        $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( array( 'section_model' => $parent_column_section, 'for_responsive_columns' => true ) ) );
        $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;
        if ( $has_section_custom_breakpoint ) {
            $column_breakpoint = $section_custom_breakpoint;
        } else if ( $has_global_custom_breakpoint ) {
            $column_breakpoint = $global_custom_breakpoint;
        }

        // PADDING / MARGIN RULES
        $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $pad_marg_options, '[data-sek-id="'.$level['id'].'"]', $custom_column_breakpoint = $column_breakpoint );

        // ADAPT COLUMN WIDTH IF A MARGIN IS SET, BY DEVICE
        // april 2020 : fixes https://github.com/presscustomizr/nimble-builder/issues/665
        // if the column has a positive ( > 0 ) margin-right and / or a margin-left set , let's adapt the column widths so we fit the 100%
        foreach (['desktop', 'tablet', 'mobile'] as $_device ) {
            $pad_marg_options[$_device] = !empty($pad_marg_options[$_device]) ? $pad_marg_options[$_device] : array();
            $rules = sek_process_column_width_for_device( array(
                'device' => $_device,
                'rules' => $rules,
                'pad_marg_options' => $pad_marg_options,
                'level' => $level,
                'parent_section' => $parent_column_section,
                'column_breakpoint' => $column_breakpoint,
                'has_global_custom_breakpoint' => $has_global_custom_breakpoint,
                'has_section_custom_breakpoint' => $has_section_custom_breakpoint
            ));
        }
    } else {
        // OTHER LEVEL CASES : SECTION, MODULE
        $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $pad_marg_options, '[data-sek-id="'.$level['id'].'"]' );
    }

    return $rules;
}




// @return array of $rules
function sek_process_column_width_for_device( $params ) {
    $_device = $params['device'];
    $rules = $params['rules'];
    $pad_marg_options = $params['pad_marg_options'];
    $level = $params['level'];
    $parent_section = $params['parent_section'];
    $column_breakpoint = $params['column_breakpoint'];
    $has_global_custom_breakpoint = $params['has_global_custom_breakpoint'];
    $has_section_custom_breakpoint = $params['has_section_custom_breakpoint'];

    // RECURSIVELY CALCULATE TOTAL HORIZONTAL MARGIN FOR THE DEVICE, OR THE ONE INHERITED FROM WIDER ONES
    // implemented for https://github.com/presscustomizr/nimble-builder/issues/665
    $total_horizontal_margin_with_unit = sek_get_maybe_inherited_total_horizontal_margins( $_device, $pad_marg_options );
    //sek_error_log('soo ?$total_horizontal_margin_with_unit ' .$_device . $level['id'] . ' | ' . $total_horizontal_margin_with_unit );
    // When no horizontal margin, no need to add a custom css rule for column width
    // + it can break rendering on Edge see https://github.com/presscustomizr/nimble-builder/issues/690
    $margin_without_unit = preg_replace("/[^0-9]/", "", $total_horizontal_margin_with_unit );

    // WRITE RULES
    switch ( $_device ) {
        case 'desktop':
            // no valid parent section ? stop here
            if ( !is_array( $parent_section ) || empty( $parent_section) )
              return $rules;

            $col_number = ( array_key_exists( 'collection', $parent_section ) && is_array( $parent_section['collection'] ) ) ? count( $parent_section['collection'] ) : 1;
            $col_number = 12 < $col_number ? 12 : $col_number;

            $col_width_in_percent = 100/$col_number;
            $col_suffix = floor( $col_width_in_percent );
            //sek_error_log('$parent_section', $parent_section );

            // DO WE HAVE A COLUMN WIDTH FOR THE COLUMN ?
            // if not, let's get the col suffix from the parent section
            // First try to find a width value in options, then look in the previous width property for backward compatibility
            // After implementing https://github.com/presscustomizr/nimble-builder/issues/279
            $column_options = isset( $level['options'] ) ? $level['options'] : array();
            $custom_width = null;
            if ( !empty( $column_options['width'] ) && !empty( $column_options['width']['custom-width'] ) ) {
                $width_candidate = (float)$column_options['width']['custom-width'];
                if ( $width_candidate < 0 || $width_candidate > 100 ) {
                    sek_error_log( __FUNCTION__ . ' => invalid width value for column id : ' . $level['id'] );
                } else {
                    $custom_width = $width_candidate;
                }
            } else {
                // Backward compat since June 2019
                // After implementing https://github.com/presscustomizr/nimble-builder/issues/279
                $custom_width   = ( !empty( $level[ 'width' ] ) && is_numeric( $level[ 'width' ] ) ) ? $level['width'] : null;
            }

            if ( !is_null( $custom_width ) ) {
                $col_width_in_percent = $custom_width;
            }

            // bail here if we messed up the column width
            if ( $col_suffix < 1 )
              return $rules;

            // define a default selector
            // will be more specific depending on the fact that a local or global custom breakpoint is set
            $selector = sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );


            // SELECTOR DEPENDING ON THE CUSTOM BREAKPOINT
            if ( $has_section_custom_breakpoint ) {
                // In this case, we need to use ".sek-section-custom-breakpoint-col-{}"
                // @see sek_add_css_rules_for_sections_breakpoint
                $selector =  sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-section-custom-breakpoint-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );
            } else if ( $has_global_custom_breakpoint ) {
                // In this case, we need to use ".sek-global-custom-breakpoint-col-{}"
                // @see sek_add_css_rules_for_sections_breakpoint
                $selector =  sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-global-custom-breakpoint-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );
            }

            // Format width in percent with 3 digits after decimal
            $col_width_in_percent = number_format( $col_width_in_percent, 3 );
            // When no horizontal margin, no need to add a custom css rule for column width
            // + it can break rendering on Edge see https://github.com/presscustomizr/nimble-builder/issues/690
            if ( 0 === (int)$margin_without_unit ) {
                $responsive_css_rules_for_desktop = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $col_width_in_percent );
            } else {
                $responsive_css_rules_for_desktop = sprintf( '-ms-flex: 0 0 calc(%1$s%% - %2$s) ;flex: 0 0 calc(%1$s%% - %2$s);max-width: calc(%1$s%% - %2$s)', $col_width_in_percent, $total_horizontal_margin_with_unit );
            }

            // we need to override the rule defined in : Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
            // that's why we use a long specific selector here
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => $responsive_css_rules_for_desktop,
                'mq' => "(min-width: {$column_breakpoint}px)"
            );
        break;

        case 'tablet':
            // the horizontal margin should be subtracted also to the column width of 100%, below the mobile breakpoint: basically the margin should be always subtracted to the column width for each viewport it is set
            // @see https://github.com/presscustomizr/nimble-builder/issues/217
            // When no horizontal margin, no need to add a custom css rule for column width
            // + it can break rendering on Edge see https://github.com/presscustomizr/nimble-builder/issues/690
            if ( 0 === (int)$margin_without_unit ) {
                $responsive_css_rules_for_100_percent_width = '-ms-flex: 0 0 100%;flex: 0 0 100%;max-width:100%';
            } else {
                $responsive_css_rules_for_100_percent_width = sprintf( '-ms-flex: 0 0 calc(100%% - %1$s) ;flex: 0 0 calc(100%% - %1$s);max-width: calc(100%% - %1$s)', $total_horizontal_margin_with_unit );
            }
            //sek_error_log('FOR TABLET =>'. $responsive_css_rules_for_100_percent_width );
            $rules[] = array(
                'selector' => sprintf('.sek-sektion-inner > [data-sek-id="%1$s"]', $level['id'] ),
                'css_rules' => $responsive_css_rules_for_100_percent_width,
                'mq' => "(max-width: {$column_breakpoint}px)"
            );
        break;

        // If user define column breakpoint ( the tablet one ) is < to $mobile_breakpoint, make sure $mobile_breakpoint inherit tablet ones
        case 'mobile':
            $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];//max-width: 576
            //$mobile_breakpoint = $mobile_breakpoint >= $column_breakpoint ? $column_breakpoint : $mobile_breakpoint;
            //if ( $mobile_breakpoint < $column_breakpoint ) {
            // the horizontal margin should be subtracted also to the column width of 100%, below the mobile breakpoint: basically the margin should be always subtracted to the column width for each viewport it is set
            // @see https://github.com/presscustomizr/nimble-builder/issues/217
            // When no horizontal margin, no need to add a custom css rule for column width
            // + it can break rendering on Edge see https://github.com/presscustomizr/nimble-builder/issues/690
            if ( 0 === (int)$margin_without_unit ) {
                $responsive_css_rules_for_100_percent_width = '-ms-flex: 0 0 100%;flex: 0 0 100%;max-width:100%';
            } else {
                $responsive_css_rules_for_100_percent_width = sprintf( '-ms-flex: 0 0 calc(%1$s%% - %2$s) ;flex: 0 0 calc(%1$s%% - %2$s);max-width: calc(%1$s%% - %2$s)', 100, $total_horizontal_margin_with_unit );
            }

            $rules[] = array(
                'selector' => sprintf('.sek-sektion-inner > [data-sek-id="%1$s"]', $level['id'] ),
                'css_rules' => $responsive_css_rules_for_100_percent_width,
                'mq' => "(max-width: {$mobile_breakpoint}px)"
            );
            //}
        break;
    }//switch device

    //sek_error_log('padding margin', $rules );
    return $rules;
}



// Recursive helper to get the total horizontal margin of the current device
// or the one inherited from the parent device
// implemented for https://github.com/presscustomizr/nimble-builder/issues/665
function sek_get_maybe_inherited_total_horizontal_margins( $_device, $pad_marg_options ) {
      $total_horizontal_margin_with_unit = '0px';
      if ( array_key_exists('margin-left', $pad_marg_options[$_device] ) || array_key_exists('margin-right', $pad_marg_options[$_device] ) ) {
          $margin_left = array_key_exists('margin-left', $pad_marg_options[$_device] ) ? $pad_marg_options[$_device]['margin-left'] : 0;
          $margin_right = array_key_exists('margin-right', $pad_marg_options[$_device] ) ? $pad_marg_options[$_device]['margin-right'] : 0;
          $device_unit = array_key_exists('unit', $pad_marg_options[$_device] ) ? $pad_marg_options[$_device]['unit'] : 'px';
          $total_horizontal_margin = (int)$margin_left + (int)$margin_right;

          // IF NO HORIZONTAL MARGIN, LET'S STOP HERE
          if ( $total_horizontal_margin <= 0 ) {
              return $total_horizontal_margin_with_unit;
          }

          return $total_horizontal_margin . $device_unit;//example : 20px
      } else {
          $device_hierarchy = array( 'mobile' , 'tablet', 'desktop' );
          $device_index = array_search( $_device, $device_hierarchy );
          if ( $device_index < ( count( $device_hierarchy ) - 1 ) ) {
              $next_device = $device_hierarchy[ $device_index + 1 ];
              return sek_get_maybe_inherited_total_horizontal_margins( $next_device, $pad_marg_options );
          }
      }
      return $total_horizontal_margin_with_unit;
}
?>