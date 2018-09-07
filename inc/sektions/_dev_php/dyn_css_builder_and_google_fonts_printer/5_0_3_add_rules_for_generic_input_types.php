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
            sek_error_log('in font_size', $value );
            if ( is_string( $value ) ) {
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $properties_to_render['font-size'] = $value;
                  }
            } else {
                  $important = sek_is_flagged_important( $input_id, $parent_level, $registered_input_list );
                  $rules = sek_set_mq_font_size_rules( $value, $selector, $important, $rules );
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
        case 'h_alignment' :
            $properties_to_render['text-align'] = $value;
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
            // Preprocess the selected font family
            //font: [font-stretch] [font-style] [font-variant] [font-weight] [font-size]/[line-height] [font-family];
            //special treatment for font-family
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
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $unit = '%' === $unit ? 'vh' : $unit;
                $properties_to_render['height'] = $numeric . $unit;
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
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $properties_to_render['border-radius'] = $numeric . $unit;
            }
        break;
        case 'width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                //$unit = '%' === $unit ? 'vw' : $unit;

                $properties_to_render['width'] = $numeric . $unit;
                // sek_error_log(' WIDTH ? for '. $input_id, $properties_to_render );
                // sek_error_log('$parent_level', $parent_level );
                //$properties_to_render['width'] = in_array( $value, range( 1, 100 ) ) ? $value . '%' : 100 . '%';
            }
        break;
        case 'v_spacing' :
            //$value = in_array( $value, range( 1, 100 ) ) ? $value . 'px' : '15px' ;
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $unit = '%' === $unit ? 'vh' : $unit;

                $properties_to_render = array(
                    'margin-top'  => $numeric . $unit,
                    'margin-bottom' => $numeric . $unit
                );
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

            $filtered_rules_candidates = array_filter( $rules_candidates, function( $k ) {
                return 'unit' !== $k;
            }, ARRAY_FILTER_USE_KEY );

            $properties_to_render = $filtered_rules_candidates;

            array_walk( $properties_to_render,
                function( &$val, $key, $unit ) {
                    //make sure paddings are positive values
                    if ( FALSE !== strpos( 'padding', $key ) ) {
                        $val = abs( $val );
                    }

                    $val .= $unit;
            }, $unit );
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
        $important = sek_is_flagged_important( $input_id, $parent_level, $registered_input_list );
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
function sek_is_flagged_important( $input_id, $parent_level, $registered_input_list ) {
    // is the important flag on ?
    $important = false;
    if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
        // loop on the module input values, and find _flag_important.
        // then check if the current input_id, is in the list of important_input_list
        foreach( $parent_level['value'] as $id => $input_value ) {
            if ( false !== strpos( $id, '_flag_important' ) ) {
                if ( is_array( $registered_input_list ) && array_key_exists( $id, $registered_input_list ) ) {
                    if ( empty( $registered_input_list[ $id ][ 'important_input_list' ] ) ) {
                        sek_error_log( __FUNCTION__ . ' => missing important_input_list for input id ' . $id );
                    } else {
                        $important_list_candidate = $registered_input_list[ $id ][ 'important_input_list' ];
                        if ( in_array( $input_id, $important_list_candidate ) ) {
                            $important = (bool)sek_is_checked( $input_value );
                        }
                    }
                }
            }
        }
    }
    return $important;
}

//HELPERS
/**
* SASS COLOR DARKEN/LIGHTEN UTILS
*/

/**
 *  Darken hex color
 */
function sek_darken_hex( $hex, $percent, $make_prop_value = true ) {
    $hsl        = sek_hex2hsl( $hex );

    $dark_hsl   = sek_darken_hsl( $hsl, $percent );
    return sek_hsl2hex( $dark_hsl, $make_prop_value );
}



/**
 *Lighten hex color
 */
function sek_lighten_hex($hex, $percent, $make_prop_value = true) {
    $hsl         = sek_hex2hsl( $hex );

    $light_hsl   = sek_lighten_hsl( $hsl, $percent );
    return sek_hsl2hex( $light_hsl, $make_prop_value );
}


/**
 * Darken rgb color
 */
function sek_darken_rgb( $rgb, $percent, $array = false, $make_prop_value = false ) {
    $hsl      = sek_rgb2hsl( $rgb, true );
    $dark_hsl   = sek_darken_hsl( $hsl, $percent );

    return sek_hsl2rgb( $dark_hsl, $array, $make_prop_value );
}


/**
 * Lighten rgb color
 */
function sek_lighten_rgb($rgb, $percent, $array = false, $make_prop_value = false ) {
    $hsl      = sek_rgb2hsl( $rgb, true );

    $light_hsl = sek_lighten_hsl( $light_hsl, $percent );

    return sek_hsl2rgb( $light_hsl, $array, $make_prop_value );
}



/**
 * Darken/Lighten hsl
 */
function sek_darken_hsl( $hsl, $percentage, $array = true ) {
    $percentage = trim( $percentage, '% ' );

    $hsl[2] = ( $hsl[2] * 100 ) - $percentage;
    $hsl[2] = ( $hsl[2] < 0 ) ? 0: $hsl[2]/100;

    if ( !$array ) {
        $hsl = implode( ",", $hsl );
    }

    return $hsl;
}


/**
 * Lighten hsl
 */
function sek_lighten_hsl( $hsl, $percentage, $array = true  ) {
    $percentage = trim( $percentage, '% ' );

    $hsl[2] = ( $hsl[2] * 100 ) + $percentage;
    $hsl[2] = ( $hsl[2] > 100 ) ? 1 : $hsl[2]/100;

    if ( !$array ) {
        $hsl = implode( ",", $hsl );
    }
    return $hsl;
}



/**
 *  Convert hexadecimal to rgb
 */
function sek_hex2rgb( $hex, $array = false, $make_prop_value = false ) {
    $hex = trim( $hex, '# ' );

    if ( 3 == strlen( $hex ) ) {
        $r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
        $g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
        $b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );

    }
    else {
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );

    }

    $rgb = array( $r, $g, $b );

    if ( !$array ) {
        $rgb = implode( ",", $rgb );
        $rgb = $make_prop_value ? "rgb($rgb)" : $rgb;
    }

    return $rgb;
}


/**
 *  Convert hexadecimal to rgba
 */
 function sek_hex2rgba( $hex, $alpha = 0.7, $array = false, $make_prop_value = false ) {
    $rgb = $rgba = sek_hex2rgb( $hex, $_array = true );

    $rgba[]     = $alpha;

    if ( !$array ) {
       $rgba = implode( ",", $rgba );
       $rgba = $make_prop_value ? "rgba($rgba)" : $rgba;
    }

    return $rgba;
}



/**
 *  Convert rgb to rgba
 */
function sek_rgb2rgba( $rgb, $alpha = 0.7, $array = false, $make_prop_value = false ) {
    $rgb   = is_array( $rgb ) ? $rgb : explode( ',', $rgb );
    $rgb   = is_array( $rgb) ? $rgb : array( $rgb );
    $rgb   = count( $rgb ) < 3 ? array_pad( $rgb, 3, 255 ) : $rgb;

    $rgba[] = $alpha;

    if ( !$array ) {
        $rgba = implode( ",", $rgba );
        $rgba = $make_prop_value ? "rgba($rgba)" : $rgba;
    }

    return $rgba;
}


/*
* Following heavily based on
* https://github.com/mexitek/phpColors
* MIT License
*/
/**
 *  Convert rgb to hexadecimal
 */
function sek_rgb2hex( $rgb, $make_prop_value = false ) {
    $rgb = is_array( $rgb ) ? $rgb : explode( ',', $rgb );
    $rgb = is_array( $rgb) ? $rgb : array( $rgb );
    $rgb = count( $rgb ) < 3 ? array_pad( $rgb, 3, 255 ) : $rgb;

    // Convert RGB to HEX
    $hex[0] = str_pad( dechex( $rgb[0] ), 2, '0', STR_PAD_LEFT );
    $hex[1] = str_pad( dechex( $rgb[1] ), 2, '0', STR_PAD_LEFT );
    $hex[2] = str_pad( dechex( $rgb[2] ), 2, '0', STR_PAD_LEFT );

    $hex = implode( '', $hex );

    return $make_prop_value ? "#{$hex}" : $hex;
}


/**
 *  Convert rgba to rgb array + alpha
 */
function sek_rgba2rgb_a( $rgba ) {
    $rgba = is_array( $rgba ) ? $rgba : explode( ',', $rgba );
    $rgba = is_array( $rgba) ? $rgba : array( $rgba );
    return array(
        array_slice( $rgba, 0, 3 ),
        $rgba[4]
    );
}
/*
* heavily based on
* phpColors
*/
/**
 *  Convert rgb to hsl
 */
function sek_rgb2hsl( $rgb, $array = false ) {
    $rgb       = is_array( $rgb ) ? $rgb : explode( ',', $rgb );
    $rgb       = is_array( $rgb) ? $rgb : array( $rgb );
    $rgb       = count( $rgb ) < 3 ? array_pad( $rgb, 3, 255 ) : $rgb;

    $deltas    = array();

    $RGB       = array(
        'R'   => ( $rgb[0] / 255 ),
        'G'   => ( $rgb[1] / 255 ),
        'B'   => ( $rgb[2] / 255 )
    );


    $min       = min( array_values( $RGB ) );
    $max       = max( array_values( $RGB ) );
    $span      = $max - $min;

    $H = $S    = 0;
    $L         = ($max + $min)/2;

    if ( 0 != $span ) {

        if ( $L < 0.5 ) {
            $S = $span / ( $max + $min );
        }
        else {
            $S = $span / ( 2 - $max - $min );
        }

        foreach ( array( 'R', 'G', 'B' ) as $var ) {
            $deltas[$var] = ( ( ( $max - $RGB[$var] ) / 6 ) + ( $span / 2 ) ) / $span;
        }

        if ( $max == $RGB['R'] ) {
            $H = $deltas['B'] - $deltas['G'];
        }
        else if ( $max == $RGB['G'] ) {
            $H = ( 1 / 3 ) + $deltas['R'] - $deltas['B'];
        }
        else if ( $max == $RGB['B'] ) {
            $H = ( 2 / 3 ) + $deltas['G'] - $deltas['R'];
        }

        if ( $H<0 ) {
            $H++;
        }

        if ( $H>1 ) {
            $H--;
        }
    }

    $hsl = array( $H*360, $S, $L );


    if ( !$array ) {
        $hsl = implode( ",", $hsl );
    }

    return $hsl;
}


/**
 * Convert hsl to rgb
*/
function sek_hsl2rgb( $hsl, $array=false, $make_prop_value = false ) {
    list($H,$S,$L) = array( $hsl[0]/360, $hsl[1], $hsl[2] );

    $rgb           = array_fill( 0, 3, $L * 255 );

    if ( 0 !=$S ) {
        if ($L < 0.5 ) {
            $var_2 = $L * ( 1 + $S );
        } else {
            $var_2 = ( $L + $S ) - ( $S * $L );
        }

        $var_1  = 2 * $L - $var_2;

        $rgb[0] = sek_hue2rgbtone( $var_1, $var_2, $H + ( 1/3 ) );
        $rgb[1] = sek_hue2rgbtone( $var_1, $var_2, $H );
        $rgb[2] = sek_hue2rgbtone( $var_1, $var_2, $H - ( 1/3 ) );
    }

    if ( !$array ) {
        $rgb = implode(",", $rgb);
        $rgb = $make_prop_value ? "rgb($rgb)" : $rgb;
    }

    return $rgb;
}


/**
 * Convert hsl to hex
 */
function sek_hsl2hex( $hsl = array(), $make_prop_value = false ) {
    $rgb = sek_hsl2rgb( $hsl, $array = true );
    return sek_rgb2hex( $rgb, $make_prop_value );
}


/**
 * Convert hex to hsl
 */
function sek_hex2hsl( $hex ) {
    $rgb = sek_hex2rgb( $hex, true );
    return sek_rgb2hsl( $rgb, true );
}

/**
 * Convert hue to rgb
 */
function sek_hue2rgbtone( $v1, $v2, $vH ) {
    $_to_return = $v1;

    if( $vH < 0 ) {
        $vH += 1;
    }
    if( $vH > 1 ) {
        $vH -= 1;
    }

    if ( (6*$vH) < 1 ) {
        $_to_return = ($v1 + ($v2 - $v1) * 6 * $vH);
    }
    elseif ( (2*$vH) < 1 ) {
        $_to_return = $v2;
    }
    elseif ( (3*$vH) < 2 ) {
        $_to_return = ($v1 + ($v2-$v1) * ( (2/3)-$vH ) * 6);
    }

    return round( 255 * $_to_return );
}



/*
 *  Returns the complementary hsl color
 */
function sek_rgb_invert( $rgb )  {
    // Adjust Hue 180 degrees
    // $hsl[0] += ($hsl[0]>180) ? -180:180;
    $rgb_inverted =  array(
        255 - $rgb[0],
        255 - $rgb[1],
        255 - $rgb[2]
    );

    return $rgb_inverted;
}


/**
 * Returns the complementary hsl color
 */
function sek_hex_invert( $hex, $make_prop_value = true )  {
    $rgb           = sek_hex2rgb( $hex, $array = true );
    $rgb_inverted  = sek_rgb_invert( $rgb );

    return sek_rgb2hex( $rgb_inverted, $make_prop_value );
}

// 1.5em => em
function sek_extract_unit( $value ) {
    $unit = preg_replace('/[0-9]|\.|,/', '', $value );
    return  0 === preg_match( "/(px|em|%)/i", $unit ) ? 'px' : $unit;
}

// 1.5em => 1.5
// note : using preg_replace('/[^0-9]/', '', $data); would remove the dots or comma.
function sek_extract_numeric_value( $value ) {
    if ( ! is_scalar( $value ) )
      return null;
    $numeric = preg_replace('/px|em|%/', '', $value);
    return ( !empty( $numeric ) && is_int( (int)$numeric ) && $numeric > 0 )? $numeric : null;
}




// This function is invoked when sniffing the font_size_css input rules.
// @param $value = Array
// (
//     [desktop] => 5em
//     [mobile] => 25px
// )
// @param $rules = array() of css rules to populate
// @return an array of css rules looking like
// $rules[] = array(
//     'selector'    => $selector,
//     'css_rules'   => $css_rules,
//     'mq'          => $mq
// );
function sek_set_mq_font_size_rules( $value, $selector, $important, $rules = array() ) {
    $default_unit = 'px';
    $value = wp_parse_args( $value, array(
        'desktop' => '16px',
        'tablet' => '',
        'mobile' => ''
    ));


    /*
    * TABLETS AND MOBILES WILL INHERIT UPPER MQ LEVELS IF NOT OTHERWISE SPECIFIED
    */
    // Sek_Dyn_CSS_Builder::$breakpoints = [
    //     'xs' => 0,
    //     'sm' => 576,
    //     'md' => 768,
    //     'lg' => 992,
    //     'xl' => 1200
    // ];
    if ( ! empty( $value[ 'desktop' ] ) ) {
        $_font_size_mq[ 'desktop' ] = null;
    }

    if ( ! empty( $value[ 'tablet' ] ) ) {
        $_font_size_mq[ 'tablet' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['md'] - 1 ) . 'px)'; //max-width: 767
    }

    if ( ! empty( $value[ 'mobile' ] ) ) {
        $_font_size_mq[ 'mobile' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['sm'] - 1 ) . 'px)'; //max-width: 575
    }


    foreach ( $value as $device => $val ) {
        if ( ! empty(  $val ) ) {
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => sprintf( '%1$s:%2$s%3$s;', 'font-size', $val, $important ? '!important' : '' ),
                'mq' => $_font_size_mq[ $device ]
            );
        }
    }

    return $rules;
}

?>