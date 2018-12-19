<?php
// @return array() for css rules
// $rules[]     = array(
//     'selector' => '[data-sek-id="'.$level['id'].'"]',
//     'css_rules' => 'border-radius:'.$numeric . $unit.';',
//     'mq' =>null
// );
//
// @param $border_options is an array looking like :
// [borders] => Array
//         (
//             [_all_] => Array
//                 (
//                     [wght] => 55px
//                     [col] => #359615
//                 )

//             [top] => Array
//                 (
//                     [wght] => 6em
//                     [col] => #dd3333
//                 )

//             [bottom] => Array
//                 (
//                     [wght] => 76%
//                     [col] => #eeee22
//                 )
// @param $border_type is a string. solid, dashed, ...
function sek_generate_css_rules_for_multidimensional_border_options( $rules, $border_settings, $border_type, $css_selectors = '' ) {
    if ( ! is_array( $rules ) )
      return array();

    $default_data = array( 'wght' => '1px', 'col' => '#000000' );
    if ( array_key_exists('_all_', $border_settings) ) {
        $default_data = wp_parse_args( $border_settings['_all_'] , $default_data );
    }

    $css_rules = array();
    foreach ( $border_settings as $border_dimension => $data ) {
        if ( ! is_array( $data ) ) {
            sek_error_log( __FUNCTION__ . " => ERROR, the border setting should be an array formed like : array( 'wght' => '1px', 'col' => '#000000' )");
        }
        $data = wp_parse_args( $data, $default_data );

        $border_properties = array();
        // border width
        $numeric = sek_extract_numeric_value( $data['wght'] );
        if ( is_numeric( $numeric ) ) {
            $unit = sek_extract_unit( $data['wght'] );
            // $unit = '%' === $unit ? 'vw' : $unit;
            $border_properties[] = $numeric . $unit;
            //border type
            $border_properties[] = $border_type;
            //border color
            //(needs validation: we need a sanitize hex or rgba color)
            if ( ! empty( $data[ 'col' ] ) ) {
                $border_properties[] = $data[ 'col' ];
            }

            $css_property = 'border';
            if ( '_all_' !== $border_dimension ) {
                $css_property = 'border-' . $border_dimension;
            }

            $css_rules[] = "{$css_property}:" . implode( ' ', array_filter( $border_properties ) );
            //sek_error_log('CSS RULES FOR BORDERS', implode( ';', array_filter( $css_rules ) ));
        }//if ( !empty( $numeric ) )
    }//foreach

    //append border rules
    $rules[]     = array(
        'selector' => $css_selectors,
        'css_rules' => implode( ';', array_filter( $css_rules ) ),//"border:" . implode( ' ', array_filter( $border_properties ) ),
        'mq' =>null
    );
    return $rules;
}



// @return array() for css rules
// $rules[]     = array(
//     'selector' => '[data-sek-id="'.$level['id'].'"]',
//     'css_rules' => 'border-radius:'.$numeric . $unit.';',
//     'mq' =>null
// );
//
// @param border_radius_settings is an array looking like :
// border-radius] => Array
// (
//     [_all_] => 207px
//     [bottom_right] => 360px
//     [top_left] => 448px
//     [bottom_left] => 413px
// )
function sek_generate_css_rules_for_border_radius_options( $rules, $border_radius_settings, $css_selectors = '' ) {
    if ( ! is_array( $rules ) )
      return array();

    if ( empty( $border_radius_settings ) )
      return $rules;

    $default_radius = '0px';
    if ( array_key_exists('_all_', $border_radius_settings ) ) {
        $default_val = sek_extract_numeric_value( $border_radius_settings['_all_'] );
        if ( is_numeric( $default_val ) ) {
            $unit = sek_extract_unit( $border_radius_settings['_all_'] );
            $default_radius = $default_val . $unit;
        }
    }

    // Make sure that the $border_radius_setting is normalize so we can generate a proper border-radius value
    // For example a user value of  ( '_all_' => '3px', 'top_right' => '6px', 'bottom_left' => '3px' )
    // should be normalized to      ( '_all_' => '3px', 'top_left' => '3px', 'top_right' => '6px', 'bottom_right' => '3px', 'bottom_left' => '3px' )
    $radius_dimensions_default = array(
        'top_left' => $default_radius,
        'top_right' => $default_radius,
        'bottom_right' => $default_radius,
        'bottom_left' => $default_radius
    );

    $css_rules = '';
    if ( array_key_exists( '_all_', $border_radius_settings ) && 1 === count( $border_radius_settings ) ) {
        $css_rules = "border-radius:" . $default_radius.';';
    } else {
        // Build the normalized array for multidimensional css properties
        $normalized_border_radius_values = array();
        foreach ( $radius_dimensions_default as $dim => $default_radius) {
            if ( array_key_exists( $dim, $border_radius_settings ) ) {
                $numeric = sek_extract_numeric_value( $border_radius_settings[$dim] );
                if ( is_numeric( $numeric ) ) {
                    $unit = sek_extract_unit( $border_radius_settings[$dim] );
                    $normalized_border_radius_values[] = $numeric . $unit;
                } else {
                    $normalized_border_radius_values[] = $default_radius;
                }
            } else {
                $normalized_border_radius_values[] = $default_radius;
            }
        }
        $css_rules = "border-radius:" . implode( ' ', array_filter( $normalized_border_radius_values ) ).';';
    }

    if ( ! empty( $css_rules ) ) {
        //append border radius rules
        $rules[]     = array(
            'selector' => $css_selectors,
            'css_rules' => $css_rules,
            'mq' => null
        );
    }

    return $rules;
}




// @return array() for css rules
// $rules[]     = array(
//     'selector' => '[data-sek-id="'.$level['id'].'"]',
//     'css_rules' => '',
//     'mq' =>null
// );
//
// @param $spacing_settings is an array looking like :
// Array
// (
//     [desktop] => Array
//         (
//             [padding-top] => 2
//         )

// )
function sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $spacing_settings, $css_selectors = '' ) {
    //spacing
    if ( empty( $spacing_settings ) || ! is_array( $spacing_settings ) )
      return $rules;


    $default_unit = 'px';

    //not mobile first
    $_desktop_rules = $_mobile_rules = $_tablet_rules = null;

    if ( !empty( $spacing_settings['desktop'] ) ) {
         $_desktop_rules = array( 'rules' => $spacing_settings['desktop'] );
    }

    // POPULATES AN ARRAY FROM THE RAW SAVED OPTIONS
    $_pad_marg = array(
        'desktop' => array(),
        'tablet' => array(),
        'mobile' => array()
    );

    foreach( array_keys( $_pad_marg ) as $device  ) {
        if ( !empty( $spacing_settings[ $device ] ) ) {
            $rules_candidates = $spacing_settings[ $device ];
            //add unit and sanitize padding (cannot have negative padding)
            $unit                 = !empty( $rules_candidates['unit'] ) ? $rules_candidates['unit'] : $default_unit;
            $unit                 = 'percent' == $unit ? '%' : $unit;

            $new_filtered_rules = array();
            foreach ( $rules_candidates as $k => $v) {
                if ( 'unit' !== $k ) {
                    $new_filtered_rules[ $k ] = $v;
                }
            }

            $_pad_marg[ $device ] = array( 'rules' => $new_filtered_rules );

            array_walk( $_pad_marg[ $device ][ 'rules' ],
                function( &$val, $key, $unit ) {
                    //make sure paddings are positive values
                    if ( FALSE !== strpos( 'padding', $key ) ) {
                        $val = abs( $val );
                    }

                    $val .= $unit;
            }, $unit );
        }
    }


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
    if ( ! empty( $_pad_marg[ 'desktop' ] ) ) {
        $_pad_marg[ 'desktop' ][ 'mq' ] = null;
    }

    if ( ! empty( $_pad_marg[ 'tablet' ] ) ) {
        $_pad_marg[ 'tablet' ][ 'mq' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['md'] - 1 ) . 'px)'; //max-width: 767
    }

    if ( ! empty( $_pad_marg[ 'mobile' ] ) ) {
        $_pad_marg[ 'mobile' ][ 'mq' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['sm'] - 1 ) . 'px)'; //max-width: 575
    }

    foreach( array_filter( $_pad_marg ) as $_spacing_rules ) {
        $css_rules = implode(';',
            array_map( function( $key, $value ) {
                return "$key:{$value};";
            }, array_keys( $_spacing_rules[ 'rules' ] ), array_values( $_spacing_rules[ 'rules' ] )
        ) );

        $rules[] = array(
            'selector' => $css_selectors,//'[data-sek-id="'.$level['id'].'"]',
            'css_rules' => $css_rules,
            'mq' =>$_spacing_rules[ 'mq' ]
        );
    }
    return $rules;
}



// This function is invoked when sniffing the input rules.
// It's a generic helper to generate media query css rule
// @return an array of css rules looking like
// $rules[] = array(
//     'selector'    => $selector,
//     'css_rules'   => $css_rules,
//     'mq'          => $mq
// );
// @params params(array). Example
// array(
//     'value' => $ready_value,(array)
//     'css_property' => 'height',(string)
//     'selector' => $selector,(string)
//     'is_important' => $important,(bool)
// )
// params['value'] = Array
// (
//     [desktop] => 5em
//     [tablet] => 4em
//     [mobile] => 25px
// )
function sek_set_mq_css_rules( $params, $rules ) {
    // TABLETS AND MOBILES WILL INHERIT UPPER MQ LEVELS IF NOT OTHERWISE SPECIFIED
    // Sek_Dyn_CSS_Builder::$breakpoints = [
    //     'xs' => 0,
    //     'sm' => 576,
    //     'md' => 768,
    //     'lg' => 992,
    //     'xl' => 1200
    // ];
    $params = wp_parse_args( $params, array(
        'value' => array(),
        'css_property' => '',
        'selector' => '',
        'is_important' => false
    ));
    if ( ! empty( $params['value'][ 'desktop' ] ) ) {
        $_font_size_mq[ 'desktop' ] = null;
    }

    if ( ! empty( $params['value'][ 'tablet' ] ) ) {
        $_font_size_mq[ 'tablet' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['md'] - 1 ) . 'px)'; //max-width: 767
    }

    if ( ! empty( $params['value'][ 'mobile' ] ) ) {
        $_font_size_mq[ 'mobile' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['sm'] - 1 ) . 'px)'; //max-width: 575
    }
    foreach ( $params['value'] as $device => $val ) {
        if ( ! in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
            sek_error_log( __FUNCTION__ . ' => error => unknown device : ' . $device );
            continue;
        }
        if ( ! empty(  $val ) ) {
            $rules[] = array(
                'selector' => $params['selector'],
                'css_rules' => sprintf( '%1$s:%2$s%3$s;', $params['css_property'], $val, $params['is_important'] ? '!important' : '' ),
                'mq' => $_font_size_mq[ $device ]
            );
        }
    }
    return $rules;
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
    return is_numeric( $numeric ) ? $numeric : null;
}


?>