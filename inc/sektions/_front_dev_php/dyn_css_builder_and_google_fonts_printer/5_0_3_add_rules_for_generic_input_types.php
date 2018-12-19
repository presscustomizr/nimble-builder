<?php
// filter declared in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker()
// $rules = apply_filters( "sek_add_css_rules_for_input_id", $rules, $key, $entry, $this -> parent_level );
// the rules are filtered if ( false !== strpos( $input_id_candidate, '_css') )
// Example of input id candidate filtered : 'h_alignment_css'
add_filter( "sek_add_css_rules_for_input_id", '\Nimble\sek_add_css_rules_for_css_sniffed_input_id', 10, 6 );
function sek_add_css_rules_for_css_sniffed_input_id( $rules, $value, $input_id, $registered_input_list, $parent_level, $module_level_css_selectors ) {

    if ( ! is_string( $input_id ) || empty( $input_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing input_id', $parent_level);
        return $rules;
    }
    if ( ! is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
        sek_error_log( __FUNCTION__ . ' => missing input_list', $parent_level);
        return $rules;
    }
    $input_registration_params = $registered_input_list[ $input_id ];
    if ( ! is_string( $input_registration_params['css_identifier'] ) || empty( $input_registration_params['css_identifier'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing css_identifier', $parent_level );
        return $rules;
    }

    $selector = '[data-sek-id="'.$parent_level['id'].'"]';
    $css_identifier = $input_registration_params['css_identifier'];

    // SPECIFIC CSS SELECTOR AT MODULE LEVEL
    // are there more specific css selectors specified on module registration ?
    if ( !is_null( $module_level_css_selectors ) && !empty( $module_level_css_selectors ) ) {
        if ( is_array( $module_level_css_selectors ) ) {
            $new_selectors = array();
            foreach ( $module_level_css_selectors as $spec_selector ) {
                $new_selectors[] = $selector . ' ' . $spec_selector;
            }
            $new_selectors = implode(',', $new_selectors );
            $selector = $new_selectors;
        } else if ( is_string( $module_level_css_selectors ) ) {
            $selector .= ' ' . $module_level_css_selectors;
        }
    }
    // for a module level, increase the default specifity to the .sek-module-inner container by default
    // @fixes https://github.com/presscustomizr/nimble-builder/issues/85
    else if ( 'module' === $parent_level['level'] ) {
        $selector .= ' .sek-module-inner';
    }


    // SPECIFIC CSS SELECTOR AT INPUT LEVEL
    // => Overrides the module level specific selector, if it was set.
    if ( 'module' === $parent_level['level'] ) {
        //$start = microtime(true) * 1000;
        if ( ! is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input list' );
        } else if ( is_array( $registered_input_list ) && empty( $registered_input_list[ $input_id ] ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input id ' . $input_id . ' in input list for module type ' . $parent_level['module_type'] );
        }
        if ( is_array( $registered_input_list ) && ! empty( $registered_input_list[ $input_id ] ) && ! empty( $registered_input_list[ $input_id ]['css_selectors'] ) ) {
            // reset the selector to the level id selector, in case it was previously set spcifically at the module level
            $selector = '[data-sek-id="'.$parent_level['id'].'"]';
            $input_level_css_selectors = $registered_input_list[ $input_id ]['css_selectors'];
            $new_selectors = array();
            if ( is_array( $input_level_css_selectors ) ) {
                foreach ( $input_level_css_selectors as $spec_selector ) {
                    $new_selectors[] = $selector . ' ' . $spec_selector;
                }
            } else if ( is_string( $input_level_css_selectors ) ) {
                $new_selectors[] = $selector . ' ' . $input_level_css_selectors;
            }

            $new_selectors = implode(',', $new_selectors );
            $selector = $new_selectors;
            //sek_error_log( '$input_level_css_selectors', $selector );
        }
        // sek_error_log( 'input_id', $input_id );
        // sek_error_log( '$registered_input_list', $registered_input_list );

        // $end = microtime(true) * 1000;
        // $time_elapsed_secs = $end - $start;
        // sek_error_log('$time_elapsed_secs to get module params', $time_elapsed_secs );
    }


    $mq = null;
    $properties_to_render = array();

    switch ( $css_identifier ) {
        // 2 cases for the font_size
        // 1) we save it as a string : '16px'
        // 2) we save it as an array of strings by devices : [ 'desktop' => '55px', 'tablet' => '40px', 'mobile' => '36px']
        case 'font_size' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $properties_to_render['font-size'] = $value;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '16px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $rules = sek_set_mq_css_rules( array(
                      'value' => $value,
                      'css_property' => 'font-size',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        case 'line_height' :
            $properties_to_render['line-height'] = $value;
        break;
        case 'font_weight' :
            $properties_to_render['font-weight'] = $value;
        break;
        case 'font_style' :
            $properties_to_render['font-style'] = $value;
        break;
        case 'text_decoration' :
            $properties_to_render['text-decoration'] = $value;
        break;
        case 'text_transform' :
            $properties_to_render['text-transform'] = $value;
        break;
        case 'letter_spacing' :
            $properties_to_render['letter-spacing'] = $value . 'px';
        break;
        case 'color' :
            $properties_to_render['color'] = $value;
        break;
        case 'color_hover' :
            //$selector = '[data-sek-id="'.$parent_level['id'].'"]:hover';
            // Add ':hover to each selectors'
            $new_selectors = array();
            $exploded = explode(',', $selector);
            foreach ( $exploded as $sel ) {
                $new_selectors[] = $sel.':hover';
            }

            $selector = implode(',', $new_selectors);
            $properties_to_render['color'] = $value;
        break;
        case 'background_color' :
            $properties_to_render['background-color'] = $value;
        break;
        case 'h_flex_alignment' :
            $important = false;
            if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
            }
            // convert to flex
            $flex_ready_value = array();
            foreach( $value as $device => $val ) {
                switch ( $val ) {
                    case 'left' :
                        $h_align_value = "flex-start";
                    break;
                    case 'center' :
                        $h_align_value = "center";
                    break;
                    case 'right' :
                        $h_align_value = "flex-end";
                    break;
                    default :
                        $h_align_value = "center";
                    break;
                }
                $flex_ready_value[$device] = $h_align_value;
            }
            $flex_ready_value = wp_parse_args( $flex_ready_value, array(
                'desktop' => '',
                'tablet' => '',
                'mobile' => ''
            ));

            $rules = sek_set_mq_css_rules( array(
                'value' => $flex_ready_value,
                'css_property' => 'justify-content',
                'selector' => $selector,
                'is_important' => $important,
            ), $rules );
        break;
        // handles simple or by device option
        case 'h_alignment' :
            if ( is_string( $value ) ) {// <= simple
                $properties_to_render['text-align'] = $value;
            } else if ( is_array( $value ) ) {// <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $rules = sek_set_mq_css_rules( array(
                      'value' => $value,
                      'css_property' => 'text-align',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        case 'v_alignment' :
            switch ( $value ) {
                case 'top' :
                    $v_align_value = "flex-start";
                break;
                case 'center' :
                    $v_align_value = "center";
                break;
                case 'bottom' :
                    $v_align_value = "flex-end";
                break;
                default :
                    $v_align_value = "center";
                break;
            }
            $properties_to_render['align-items'] = $v_align_value;
        break;
        case 'font_family' :
            $family = $value;
            //sek_error_log( __FUNCTION__ . ' font-family', $value );
            // Preprocess the selected font family
            // font: [font-stretch] [font-style] [font-variant] [font-weight] [font-size]/[line-height] [font-family];
            // special treatment for font-family
            if ( false != strstr( $value, '[gfont]') ) {
                $split = explode(":", $family);
                $family = $split[0];
                //only numbers for font-weight. 400 is default
                $properties_to_render['font-weight']    = $split[1] ? preg_replace('/\D/', '', $split[1]) : '';
                $properties_to_render['font-weight']    = empty($properties_to_render['font-weight']) ? 400 : $properties_to_render['font-weight'];
                $properties_to_render['font-style']     = ( $split[1] && strstr($split[1], 'italic') ) ? 'italic' : 'normal';
            }

            $family = str_replace( array( '[gfont]', '[cfont]') , '' , $family );
            $properties_to_render['font-family'] = false != strstr( $value, '[cfont]') ? $family : "'" . str_replace( '+' , ' ' , $family ) . "'";
        break;

        /* Spacer */
        // The unit should be included in the $value
        case 'height' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $unit = '%' === $unit ? 'vh' : $unit;
                      $properties_to_render['height'] = $numeric . $unit;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '20px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $unit = '%' === $unit ? 'vh' : $unit;
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'height',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        /* Quote border */
        case 'border_width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $properties_to_render['border-width'] = $numeric . $unit;
            }
        break;
        case 'border_color' :
            $properties_to_render['border-color'] = $value ? $value : '';
        break;
        /* Divider */
        case 'border_top_width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $unit = '%' === $unit ? 'vh' : $unit;
                $properties_to_render['border-top-width'] = $numeric . $unit;
                //$properties_to_render['border-top-width'] = $value > 0 ? $value . 'px' : '1px';
            }
        break;
        case 'border_top_style' :
            $properties_to_render['border-top-style'] = $value ? $value : 'solid';
        break;
        case 'border_top_color' :
            $properties_to_render['border-top-color'] = $value ? $value : '#5a5a5a';
        break;
        case 'border_radius' :
            if ( is_string( $value ) ) {
                $numeric = sek_extract_numeric_value( $value );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $value );
                    $properties_to_render['border-radius'] = $numeric . $unit;
                }
            } else if ( is_array( $value ) ) {
                $rules = sek_generate_css_rules_for_border_radius_options( $rules, $value, $selector );
            }
        break;

        case 'width' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $properties_to_render['width'] = $numeric . $unit;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '100%',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'width',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;

        case 'v_spacing' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $unit = '%' === $unit ? 'vh' : $unit;
                      $properties_to_render = array(
                          'margin-top'  => $numeric . $unit,
                          'margin-bottom' => $numeric . $unit
                      );
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '15px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $unit = '%' === $unit ? 'vh' : $unit;
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'margin-top',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'margin-bottom',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        //not used at the moment, but it might if we want to display the divider as block (e.g. a div instead of a span)
        case 'h_alignment_block' :
            switch ( $value ) {
                case 'right' :
                    $properties_to_render = array(
                        'margin-right'  => '0'
                    );
                break;
                case 'left' :
                    $properties_to_render = array(
                        'margin-left'  => '0'
                    );
                break;
                default :
                    $properties_to_render = array(
                        'margin-left'  => 'auto',
                        'margin-right' => 'auto'
                    );
            }
        break;
        case 'padding_margin_spacing' :
            $default_unit = 'px';
            $rules_candidates = $value;
            //add unit and sanitize padding (cannot have negative padding)
            $unit                 = !empty( $rules_candidates['unit'] ) ? $rules_candidates['unit'] : $default_unit;
            $unit                 = 'percent' == $unit ? '%' : $unit;

            $new_filtered_rules = array();
            foreach ( $rules_candidates as $k => $v) {
                if ( 'unit' !== $k ) {
                    $new_filtered_rules[ $k ] = $v;
                }
            }

            $properties_to_render = $new_filtered_rules;

            array_walk( $properties_to_render,
                function( &$val, $key, $unit ) {
                    //make sure paddings are positive values
                    if ( FALSE !== strpos( 'padding', $key ) ) {
                        $val = abs( $val );
                    }

                    $val .= $unit;
            }, $unit );
        break;
        case 'spacing_with_device_switcher' :
            if ( ! empty( $value ) && is_array( $value ) ) {
                $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $value, $selector );
            }
        break;

        // The default is simply there to let us know if a css_identifier is missing
        default :
            sek_error_log( __FUNCTION__ . ' => the css_identifier : ' . $css_identifier . ' has no css rules defined for input id ' . $input_id );
        break;
    }//switch

    // when the module has an '*_flag_important' input,
    // => check if the input_id belongs to the list of "important_input_list"
    // => and maybe flag the css rules with !important
    if ( ! empty( $properties_to_render ) ) {
        $important = false;
        if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
            $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
        }
        $css_rules = '';
        foreach ( $properties_to_render as $prop => $prop_val ) {
            $css_rules .= sprintf( '%1$s:%2$s%3$s;', $prop, $prop_val, $important ? '!important' : '' );
        }//end foreach

        $rules[] = array(
            'selector'    => $selector,
            'css_rules'   => $css_rules,
            'mq'          => $mq
        );
    }
    return $rules;
}


// @return boolean
// Recursive
// Check if a *_flag_important input id is part of the registered input list of the module
// then verify is the provided input_id is part of the list of input that should be set to important => 'important_input_list'
// Example of a *_flag_important input:
// 'quote___flag_important'       => array(
//     'input_type'  => 'gutencheck',
//     'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
//     'default'     => 0,
//     'refresh_markup' => false,
//     'refresh_stylesheet' => true,
//     'title_width' => 'width-80',
//     'input_width' => 'width-20',
//     // declare the list of input_id that will be flagged with !important when the option is checked
//     // @see sek_add_css_rules_for_css_sniffed_input_id
//     // @see sek_is_flagged_important
//     'important_input_list' => array(
//         'quote_font_family_css',
//         'quote_font_size_css',
//         'quote_line_height_css',
//         'quote_font_weight_css',
//         'quote_font_style_css',
//         'quote_text_decoration_css',
//         'quote_text_transform_css',
//         'quote_letter_spacing_css',
//         'quote_color_css',
//         'quote_color_hover_css'
//     )
// )
function sek_is_flagged_important( $input_id, $module_value, $registered_input_list ) {
    $important = false;

    if ( ! is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
        sek_error_log( __FUNCTION__ . ' => error => the $registered_input_list param should be an array not empty');
        return $important;
    }

    // loop on the registered input list and try to find a *_flag_important input id
    foreach ( $registered_input_list as $_id => $input_data ) {
        if ( is_string( $_id ) && false !== strpos( $_id, '_flag_important' ) ) {
            if ( empty( $input_data[ 'important_input_list' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => error => missing important_input_list for input id ' . $_id );
            } else {
                $important_list_candidate = $input_data[ 'important_input_list' ];
                if ( in_array( $input_id, $important_list_candidate ) ) {
                    $important = sek_booleanize_checkbox_val( sek_get_input_value_in_module_model( $_id, $module_value ) );
                }
            }
        }
    }
    return $important;
}
?>