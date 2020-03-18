<?php
namespace Nimble;
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










/****************************************************
* BREAKPOINT / CSS GENERATION WITH MEDIA QUERIES
****************************************************/

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
//     'css_property' => 'height',(string or array of properties)
//     'selector' => $selector,(string)
//     'is_important' => $important,(bool),
//     'level_id' => ''
// )
//
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
        'is_important' => false,
        'level_id' => '' //<= added for https://github.com/presscustomizr/nimble-builder/issues/552
    ));

    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];//max-width: 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    // since https://github.com/presscustomizr/nimble-builder/issues/552,
    // we need the parent_level id ( <=> the level on which the CSS rule is applied ) to determine if there's any inherited custom breakpoint to use
    // Exceptions :
    // - when generating media queries for local options, the level_id is set to '_excluded_from_section_custom_breakpoint_', @see sek_add_raw_local_widths_css()
    if ( '_excluded_from_section_custom_breakpoint_' !== $params['level_id'] ) {
        if ( empty( $params['level_id'] ) ) {
            sek_error_log( __FUNCTION__ . ' => missing level id, needed to determine if there is a custom breakpoint to use', $params );
        } else {
            $level_id = $params['level_id'];
            $tablet_breakpoint = sek_get_user_defined_tablet_breakpoint_for_css_rules( $level_id );// default is Sek_Dyn_CSS_Builder::$breakpoints['md'] <=> max-width: 768

            // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
            $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;
        }
    }

    $css_value_by_devices = $params['value'];
    $_font_size_mq = array('desktop' => null , 'tablet' => null , 'mobile' => null );

    if ( !empty( $css_value_by_devices ) ) {
          if ( ! empty( $css_value_by_devices[ 'desktop' ] ) ) {
              $_font_size_mq[ 'desktop' ] = null;
          }

          if ( ! empty( $css_value_by_devices[ 'tablet' ] ) ) {
              $_font_size_mq[ 'tablet' ]  = '(max-width:'. ( $tablet_breakpoint - 1 ) . 'px)'; // default is max-width: 767
          }

          if ( ! empty( $css_value_by_devices[ 'mobile' ] ) ) {
              $_font_size_mq[ 'mobile' ]  = '(max-width:'. ( $mobile_breakpoint - 1 ) . 'px)'; // default is max-width: 575
          }

          // $css_value_by_devices looks like
          // array(
          //     'desktop' => '30px',
          //     'tablet' => '',
          //     'mobile' => ''
          // );
          foreach ( $css_value_by_devices as $device => $val ) {
              if ( ! in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
                  sek_error_log( __FUNCTION__ . ' => error => unknown device : ' . $device );
                  continue;
              }
              if ( ! empty(  $val ) ) {
                  // the css_property can be an array
                  // this is needed for example to write properties supporting several vendor prefixes
                  $css_property = $params['css_property'];
                  if ( is_array( $css_property ) ) {
                      $css_rules_array = array();
                      foreach ( $css_property as $property ) {
                          $css_rules_array[] = sprintf( '%1$s:%2$s%3$s;', $property, $val, $params['is_important'] ? '!important' : '' );
                      }
                      $css_rules = implode( '', $css_rules_array );
                  } else {
                      $css_rules = sprintf( '%1$s:%2$s%3$s;', $css_property, $val, $params['is_important'] ? '!important' : '' );
                  }
                  $rules[] = array(
                      'selector' => $params['selector'],
                      'css_rules' => $css_rules,
                      'mq' => $_font_size_mq[ $device ]
                  );
              }
          }
    } else {
        sek_error_log( __FUNCTION__ . ' Error => missing css rules ');
    }

    return $rules;
}





// Additional version of sek_set_mq_css_rules() created in July 2019
// It does not replace the old, but allow another type of rule generation by device
// => this version uses a param "css_rules_by_device" which describe the complete rule ( like padding-top:5em; ) for each device, instead of spliting value and property like the previous one
// => it fixes the problem of vendor prefixes for which the value is not written the same.
// For example, a top alignment in flex is written this way :
// -webkit-box-align:start;
// -ms-flex-align:start;
//     align-items:flex-start;
//
// In this case, the param css_rules_by_device will be : "align-items:flex-start;-webkit-box-align:start;-ms-flex-align:start;";
//
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
//     'css_rules_by_device' => array of css rules by devices
//     'selector' => $selector,(string)
//     'level_id' => ''
// )
// )
// params['value'] = Array
// (
//     [desktop] => padding-top:5em;
//     [tablet] => padding-top:4em
//     [mobile] => padding-top:25px
// )
function sek_set_mq_css_rules_supporting_vendor_prefixes( $params, $rules ) {
    // TABLETS AND MOBILES WILL INHERIT UPPER MQ LEVELS IF NOT OTHERWISE SPECIFIED
    // Sek_Dyn_CSS_Builder::$breakpoints = [
    //     'xs' => 0,
    //     'sm' => 576,
    //     'md' => 768,
    //     'lg' => 992,
    //     'xl' => 1200
    // ];
    $params = wp_parse_args( $params, array(
        'css_rules_by_device' => array(),
        'selector' => '',
        'level_id' => array() //<= added for https://github.com/presscustomizr/nimble-builder/issues/552
    ));

    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];//max-width: 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    // since https://github.com/presscustomizr/nimble-builder/issues/552,
    // we need the parent_level id ( <=> the level on which the CSS rule is applied ) to determine if there's any inherited custom breakpoint to use
    // Exceptions :
    // - when generating media queries for local options, the level_id is set to '_excluded_from_section_custom_breakpoint_', @see sek_add_raw_local_widths_css()
    if ( '_excluded_from_section_custom_breakpoint_' !== $params['level_id'] ) {
        if ( empty( $params['level_id'] ) ) {
            sek_error_log( __FUNCTION__ . ' => missing level id, needed to determine if there is a custom breakpoint to use', $params );
        } else {
            $level_id = $params['level_id'];
            $tablet_breakpoint = sek_get_user_defined_tablet_breakpoint_for_css_rules( $level_id );// default is Sek_Dyn_CSS_Builder::$breakpoints['md'] <=> max-width: 768

            // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
            $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;
        }
    }

    $css_rules_by_device = $params['css_rules_by_device'];
    $_font_size_mq = array('desktop' => null , 'tablet' => null , 'mobile' => null );

    if ( !empty( $css_rules_by_device ) ) {
          if ( ! empty( $css_rules_by_device[ 'desktop' ] ) ) {
              $_font_size_mq[ 'desktop' ] = null;
          }

          if ( ! empty( $css_rules_by_device[ 'tablet' ] ) ) {
              $_font_size_mq[ 'tablet' ]  = '(max-width:'. ( $tablet_breakpoint - 1 ) . 'px)'; //max-width: 767
          }

          if ( ! empty( $css_rules_by_device[ 'mobile' ] ) ) {
              $_font_size_mq[ 'mobile' ]  = '(max-width:'. ( $mobile_breakpoint - 1 ) . 'px)'; //max-width: 575
          }
          foreach ( $css_rules_by_device as $device => $rules_for_device ) {
              $rules[] = array(
                  'selector' => $params['selector'],
                  'css_rules' => $rules_for_device,
                  'mq' => $_font_size_mq[ $device ]
              );
          }
    } else {
        sek_error_log( __FUNCTION__ . ' Error => missing css rules ');
    }

    return $rules;
}






// BREAKPOINT HELPER
// A custom breakpoint can be set globally or by section
// It replaces the default tablet breakpoint ( 768 px )
function sek_get_user_defined_tablet_breakpoint_for_css_rules( $level_id = '' ) {
    //sek_error_log('ALORS CLOSEST PARENT SECTION MODEL ?' . $level_id , sek_get_closest_section_custom_breakpoint( $level_id ) );

    // define a default breakpoint : 768
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];

    // Is there a custom breakpoint set by a parent section?
    // Order :
    // 1) custom breakpoint set on a nested section
    // 2) custom breakpoint set on a regular section
    //sek_error_log('WE SEARCH FOR => ' . $level_id );
    $closest_section_custom_breakpoint = sek_get_closest_section_custom_breakpoint( array( 'searched_level_id' => $level_id ) );
    //sek_error_log('WE FOUND A BREAKPOINT ', $closest_section_custom_breakpoint  );

    if ( is_array( $closest_section_custom_breakpoint ) ) {
        // we do this check because sek_get_closest_section_custom_breakpoint() uses an array when recursively looping
        // but returns number when a match is found
        $closest_section_custom_breakpoint = 0;
    } else {
        $closest_section_custom_breakpoint = intval( $closest_section_custom_breakpoint );
    }


    if ( $closest_section_custom_breakpoint >= 1 ) {
        $tablet_breakpoint = $closest_section_custom_breakpoint;
    } else {
        // 1) Is there a global custom breakpoint set ?
        // 2) shall we apply this global custom breakpoint to all customizations or only to responsive columns ? https://github.com/presscustomizr/nimble-builder/issues/564
        if ( sek_is_global_custom_breakpoint_applied_to_all_customizations_by_device() ) {
            $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
            if ( $global_custom_breakpoint >= 1 ) {
                $tablet_breakpoint = $global_custom_breakpoint;
            }
        }
    }
    return intval( $tablet_breakpoint );
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
    $light_hsl = sek_lighten_hsl( $hsl, $percent );

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
    $rgb   = $rgba = count( $rgb ) < 3 ? array_pad( $rgb, 3, 255 ) : $rgb;

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
 *  @param $rgba string example rgba(221,51,51,0.72)
 *  @return array()
 */
function sek_rgba2rgb_a( $rgba ) {
    $rgba = is_array( $rgba ) ? $rgba : explode( ',', $rgba );
    $rgba = is_array( $rgba) ? $rgba : array( $rgba );
    // make sure we remove all parenthesis remaining
    $rgba = array_map( function( $val ) {
        return str_replace(array('(', ')' ), '', $val);
    }, array_values( $rgba ) );
    $rgb =  array_slice( $rgba, 0, 3 );
    // remove everything but numbers
    $rgb = array_map( function( $_val ) {
        return preg_replace('/[^0-9]/', '', $_val);
    }, array_values( $rgb ) );

    return array(
        $rgb,
        // https://github.com/presscustomizr/nimble-builder/issues/303
        isset( $rgba[3] ) ? $rgba[3] : 1
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

    // check if numeric following https://github.com/presscustomizr/nimble-builder/issues/303
    $RGB       = array(
        'R'   => is_numeric($rgb[0]) ? ( $rgb[0] / 255 ) : 1,
        'G'   => is_numeric($rgb[1]) ? ( $rgb[1] / 255 ) : 1,
        'B'   => is_numeric($rgb[2]) ? ( $rgb[2] / 255 ) : 1,
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
// -2px => px
function sek_extract_unit( $value ) {
    // remove numbers, dot, comma
    $unit = preg_replace('/[0-9]|\.|,/', '', $value );
    // remove hyphens
    $unit = str_replace('-', '', $unit );
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

// Return a font family string, usable in a css stylesheet
// [cfont]Helvetica Neue, Helvetica, Arial, sans-serif =>  Helvetica Neue, Helvetica, Arial, sans-serif
// [gfont]Assistant:regular => Assistant
function sek_extract_css_font_family_from_customizer_option( $family ) {
    //sek_error_log( __FUNCTION__ . ' font-family', $value );
    // Preprocess the selected font family
    // font: [font-stretch] [font-style] [font-variant] [font-weight] [font-size]/[line-height] [font-family];
    // special treatment for font-family
    if ( false != strstr( $family, '[gfont]') ) {
        $split = explode(":", $family);
        $family = $split[0];
        //only numbers for font-weight. 400 is default
        $properties_to_render['font-weight']    = $split[1] ? preg_replace('/\D/', '', $split[1]) : '';
        $properties_to_render['font-weight']    = empty($properties_to_render['font-weight']) ? 400 : $properties_to_render['font-weight'];
        $properties_to_render['font-style']     = ( $split[1] && strstr($split[1], 'italic') ) ? 'italic' : 'normal';
    }
    if ( 'none' === $family ) {
        $family = '';
    } else {
        $family = false != strstr( $family, '[cfont]') ? $family : "'" . str_replace( '+' , ' ' , $family ) . "'";
        $family = str_replace( array( '[gfont]', '[cfont]') , '' , $family );
    }

    return $family;
}

?><?php
// The base fmk is loaded @after_setup_theme:10
add_action( 'after_setup_theme', '\Nimble\sek_schedule_module_registration', 50 );

// When not customizing, we don't need to register all modules
function sek_schedule_module_registration() {
    // we load all modules when :
    // 1) customizing
    // 2) doing ajax <=> customizing
    // 3) isset( $_POST['nimble_simple_cf'] ) <= when a contact form is submitted.
    // Note about 3) => We should in fact load the necessary modules that we can determined with the posted skope_id. To be improved.
    // 3 fixes https://github.com/presscustomizr/nimble-builder/issues/433
    if ( isset( $_POST['nimble_simple_cf'] ) ) {
        sek_register_modules_when_customizing_or_ajaxing();
    } else if ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
        sek_register_modules_when_customizing_or_ajaxing();
        // prebuilt sections are registered from a JSON since https://github.com/presscustomizr/nimble-builder/issues/431
        sek_register_prebuilt_section_modules();
    } else {
        add_action( 'wp', '\Nimble\sek_register_modules_when_not_customizing_and_not_ajaxing', PHP_INT_MAX );
    }
}

// @return void();
// @hook 'after_setup_theme'
function sek_register_modules_when_customizing_or_ajaxing() {
    $modules = array_merge(
        SEK_Front_Construct::$ui_picker_modules,
        SEK_Front_Construct::$ui_level_modules,
        SEK_Front_Construct::$ui_local_global_options_modules,
        SEK_Front_Construct::sek_get_front_module_collection()
    );

    // widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
    if ( sek_are_beta_features_enabled() ) {
        $modules = array_merge( $modules, SEK_Front_Construct::$ui_front_beta_modules );
    }
    sek_do_register_module_collection( $modules );
}



// @return void();
// @hook 'wp'@PHP_INT_MAX
function sek_register_modules_when_not_customizing_and_not_ajaxing() {
    $contextually_actives_raw = sek_get_collection_of_contextually_active_modules();
    $contextually_actives_raw = array_keys( $contextually_actives_raw );

    $contextually_actives_candidates = array();
    $front_modules = array_merge( SEK_Front_Construct::sek_get_front_module_collection(), SEK_Front_Construct::$ui_front_beta_modules );

    // we need to get all children when the module is a father.
    // This will be flatenized afterwards
    foreach ( $contextually_actives_raw as $module_name ) {

        // Parent module with children
        if ( array_key_exists( $module_name, $front_modules ) ) {
            // get the list of childrent, includes the parent too.
            // @see ::sek_get_front_module_collection()
            $contextually_actives_candidates[] = $front_modules[ $module_name ];
        }
        // Simple module with no children
        if ( in_array( $module_name, $front_modules ) ) {
            $contextually_actives_candidates[] = $module_name;
        }
    }

    $modules = array_merge(
        $contextually_actives_candidates,
        SEK_Front_Construct::$ui_level_modules,
        SEK_Front_Construct::$ui_local_global_options_modules
    );
    sek_do_register_module_collection( $modules );
}


// @return void();
function sek_do_register_module_collection( $modules ) {
    $module_candidates = array();
    // flatten the array
    // because can be formed this way after filter when including child
    // [0] => Array
    //     (
    //         [0] => czr_post_grid_module
    //         [1] => czr_post_grid_main_child
    //         [2] => czr_post_grid_thumb_child
    //         [3] => czr_post_grid_metas_child
    //         [4] => czr_post_grid_fonts_child
    //     )

    // [1] => sek_level_bg_module
    // [2] => sek_level_border_module
    foreach ($modules as $key => $value) {
      if ( is_array( $value ) ) {
          $module_candidates = array_merge( $module_candidates, $value );
      } else {
          $module_candidates[] = $value;
      }
    }

    // remove duplicated modules, typically 'czr_font_child'
    $module_candidates = array_unique( $module_candidates );
    foreach ( $module_candidates as $module_name ) {
        // Was previously written "\Nimble\sek_get_module_params_for_{$module_name}";
        // But this syntax can lead to function_exists() return false even if the function exists
        // Probably due to a php version issue. Bug detected with php version 5.6.38
        // bug report detailed here https://github.com/presscustomizr/nimble-builder/issues/234
        $fn = "Nimble\sek_get_module_params_for_{$module_name}";
        if ( function_exists( $fn ) ) {
            $params = $fn();
            if ( is_array( $params ) ) {
                CZR_Fmk_Base()->czr_pre_register_dynamic_module( $params );
            } else {
                error_log( __FUNCTION__ . ' Module registration params should be an array');
            }
        } else {
            error_log( __FUNCTION__ . ' missing params callback fn for module ' . $module_name );
        }
    }
}






// SINGLE MODULE PARAMS STUCTURE
// 'dynamic_registration' => true,
// 'module_type' => 'sek_column_layouts_sec_picker_module',
// 'name' => __('Empty sections with columns layout', 'text_doma'),
// 'tmpl' => array(
//     'item-inputs' => array(
//         'sections' => array(
//             'input_type'  => 'section_picker',
//             'title'       => __('Drag-and-drop or double-click a section to insert it into a drop zone of the preview page.', 'text_doma'),
//             'width-100'   => true,
//             'title_width' => 'width-100',
//             'section_collection' => array(
//                 array(
//                     'content-id' => 'two_columns',
//                     'title' => __('two columns layout', 'text-domain' ),
//                     'thumb' => 'two_columns.jpg'
//                 ),
//                 array(
//                     'content-id' => 'three_columns',
//                     'title' => __('three columns layout', 'text-domain' ),
//                     'thumb' => 'three_columns.jpg'
//                 ),
//                 array(
//                     'content-id' => 'four_columns',
//                     'title' => __('four columns layout', 'text-domain' ),
//                     'thumb' => 'four_columns.jpg'
//                 ),
//             )
//         )
//     )
// )
// @return void();
// @hook 'after_setup_theme'
function sek_register_prebuilt_section_modules() {
    $registration_params = sek_get_sections_registration_params();
    $default_module_params = array(
        'dynamic_registration' => true,
        'module_type' => '',
        'name' => '',
        'tmpl' => array(
            'item-inputs' => array(
                'sections' => array(
                    'input_type'  => 'section_picker',
                    'title'       => __('Drag-and-drop or double-click a section to insert it into a drop zone of the preview page.', 'text_doma'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'section_collection' => array()
                )
            )
        )
    );

    foreach ( $registration_params as $module_type => $module_params ) {
        $module_params = wp_parse_args( $module_params, array(
            'module_title' => '',
            'section_collection' => array()
        ));

        // normalize the module params
        $normalized_params = $default_module_params;
        $normalized_params['module_type'] = $module_type;
        $normalized_params['name'] = $module_params['module_title'];
        $normalized_params['tmpl']['item-inputs']['sections']['section_collection'] = $module_params['section_collection'];
        CZR_Fmk_Base()->czr_pre_register_dynamic_module( $normalized_params );
    }

}






// HELPERS
// Used when registering a select input in a module
// @return an array of options that will be used to populate the select input in js
function sek_get_select_options_for_input_id( $input_id ) {
    $options = array();
    switch( $input_id ) {
        case 'img_hover_effect' :
            $options = array(
                'none' => __('No effect', 'text_doma' ),
                'opacity' => __('Opacity', 'text_doma' ),
                'zoom-out' => __('Zoom out', 'text_doma' ),
                'zoom-in' => __('Zoom in', 'text_doma' ),
                'move-up' =>__('Move up', 'text_doma' ),
                'move-down' =>__('Move down', 'text_doma' ),
                'blur' =>__('Blur', 'text_doma' ),
                'grayscale' =>__('Grayscale', 'text_doma' ),
                'reverse-grayscale' =>__('Reverse grayscale', 'text_doma' )
            );
        break;
        case 'img-size' :
            $options = sek_get_img_sizes();
        break;

        // ALL MODULES
        case 'link-to' :
            $options = array(
                'no-link' => __('No link', 'text_doma' ),
                'url' => __('Site content or custom url', 'text_doma' ),
            );
        break;

        // FEATURED PAGE MODULE
        case 'img-type' :
            $options = array(
                'none' => __( 'No image', 'text_doma' ),
                'featured' => __( 'Use the page featured image', 'text_doma' ),
                'custom' => __( 'Use a custom image', 'text_doma' ),
            );
        break;
        case 'content-type' :
            $options = array(
                'none' => __( 'No text', 'text_doma' ),
                'page-excerpt' => __( 'Use the page excerpt', 'text_doma' ),
                'custom' => __( 'Use a custom text', 'text_doma' ),
            );
        break;

        // HEADING MODULE
        case 'heading_tag':
            $options = array(
                /* Not totally sure these should be localized as they strictly refer to html tags */
                'h1' => __('H1', 'text_doma' ),
                'h2' => __('H2', 'text_doma' ),
                'h3' => __('H3', 'text_doma' ),
                'h4' => __('H4', 'text_doma' ),
                'h5' => __('H5', 'text_doma' ),
                'h6' => __('H6', 'text_doma' ),
            );
        break;

        // CSS MODIFIERS INPUT ID
        case 'font_weight_css' :
            $options = array(
                'normal'  => __( 'normal', 'text_doma' ),
                'bold'    => __( 'bold', 'text_doma' ),
                'bolder'  => __( 'bolder', 'text_doma' ),
                'lighter'   => __( 'lighter', 'text_doma' ),
                100     => 100,
                200     => 200,
                300     => 300,
                400     => 400,
                500     => 500,
                600     => 600,
                700     => 700,
                800     => 800,
                900     => 900
            );
        break;
        case 'font_style_css' :
            $options = array(
                'inherit'   => __( 'inherit', 'text_doma' ),
                'italic'  => __( 'italic', 'text_doma' ),
                'normal'  => __( 'normal', 'text_doma' ),
                'oblique' => __( 'oblique', 'text_doma' )
            );
        break;
        case 'text_decoration_css'  :
            $options = array(
                'none'      => __( 'none', 'text_doma' ),
                'inherit'   => __( 'inherit', 'text_doma' ),
                'line-through' => __( 'line-through', 'text_doma' ),
                'overline'    => __( 'overline', 'text_doma' ),
                'underline'   => __( 'underline', 'text_doma' )
            );
        break;
        case 'text_transform_css' :
            $options = array(
                'none'      => __( 'none', 'text_doma' ),
                'inherit'   => __( 'inherit', 'text_doma' ),
                'capitalize'  => __( 'capitalize', 'text_doma' ),
                'uppercase'   => __( 'uppercase', 'text_doma' ),
                'lowercase'   => __( 'lowercase', 'text_doma' )
            );
        break;

        // SPACING MODULE
        case 'css_unit' :
            $options = array(
                'px' => __('Pixels', 'text_doma' ),
                'em' => __('Em', 'text_doma'),
                'percent' => __('Percents', 'text_doma' )
            );
        break;

        //QUOTE MODULE
        case 'quote_design' :
            $options = array(
                'none' => __( 'Text only', 'text_doma' ),
                'border-before' => __( 'Side Border', 'text_doma' ),
                'quote-icon-before' => __( 'Quote Icon', 'text_doma' ),
            );
        break;

        // LEVELS UI : LAYOUT BACKGROUND BORDER HEIGHT WIDTH
        case 'boxed-wide' :
            $options = array(
                'boxed' => __('Boxed', 'text_doma'),
                'fullwidth' => __('Full Width', 'text_doma')
            );
        break;
        case 'height-type' :
            $options = array(
                'auto' => __('Adapt to content', 'text_doma'),
                'custom' => __('Custom', 'text_doma' )
            );
        break;
        case 'width-type' :
            $options = array(
                'default' => __('Default', 'text_doma'),
                'custom' => __('Custom', 'text_doma' )
            );
        break;
        case 'bg-scale' :
            $options = array(
                'default' => __('Default', 'text_doma'),
                'auto' => __('Automatic', 'text_doma'),
                'cover' => __('Scale to fill', 'text_doma'),
                'contain' => __('Fit', 'text_doma'),
            );
        break;
        case 'bg-position' :
            $options = array(
                'default' => __('default', 'text_doma'),
            );
        break;
        case 'border-type' :
            $options = array(
                'none' => __('none', 'text_doma'),
                'solid' => __('solid', 'text_doma'),
                'double' => __('double', 'text_doma'),
                'dotted' => __('dotted', 'text_doma'),
                'dashed' => __('dashed', 'text_doma')
            );
        break;

        default :
            sek_error_log( __FUNCTION__ . ' => no case set for input id : '. $input_id );
        break;
    }
    return $options;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_content_type_switcher_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_content_type_switcher_module',
        'name' => __('Content type', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content_type' => array(
                    'input_type'  => 'content_type_switcher',
                    'title'       => '',//__('Which type of content would you like to drop in your page ?', 'text_doma'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => sprintf(
                        __('Note : you can %1$s to replace your default theme template. Or design your own %2$s.', 'nimble-builder'),
                        sprintf('<a href="#" onclick="%2$s" title="%1$s">%1$s</a>',
                            __('use the Nimble page template', 'nimble-builder'),
                            "javascript:wp.customize.section('__localOptionsSection', function( _s_ ){_s_.container.find('.accordion-section-title').first().trigger('click');})"
                        ),
                        sprintf('<a href="#" onclick="%2$s" title="%1$s">%1$s</a>',
                            __('header and footer', 'nimble-builder'),
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })"
                        )
                    )
                )
            )
        )
    );
}


/* ------------------------------------------------------------------------- *
 *  MODULE PICKER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_module_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_module_picker_module',
        'name' => __('Content Picker', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'module_id' => array(
                    'input_type'  => 'module_picker',
                    'title'       => __('Drag-and-drop or double-click a module to insert it into a drop zone of the preview page.', 'text_doma'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )
    );
}


/* ------------------------------------------------------------------------- *
 *  SEKTION PICKER MODULES
/* ------------------------------------------------------------------------- */
// registered from a remote JSON since https://github.com/presscustomizr/nimble-builder/issues/431

// FOR SAVED SECTIONS
// function sek_get_module_params_for_sek_my_sections_sec_picker_module() {
//     return array(
//         'dynamic_registration' => true,
//         'module_type' => 'sek_my_sections_sec_picker_module',
//         'name' => __('My sections', 'text_doma'),
//         'tmpl' => array(
//             'item-inputs' => array(
//                 'my_sections' => array(
//                     'input_type'  => 'section_picker',
//                     'title'       => __('Drag-and-drop or double-click a section to insert it into a drop zone of the preview page.', 'text_doma'),
//                     'width-100'   => true,
//                     'title_width' => 'width-100'
//                 )
//             )
//         )
//     );
// }
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_mod_option_switcher_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_mod_option_switcher_module',
        'name' => __('Option switcher', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content_type' => array(
                    'input_type'  => 'module_option_switcher',
                    'title'       => '',//__('Which type of content would you like to drop in your page ?', 'text_doma'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                )
            )
        )
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_bg_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_bg_module',
        'name' => __('Background', 'text_doma'),
        // 'starting_value' => array(
        //     'bg-color-overlay'  => '#000000',
        //     'bg-opacity-overlay' => '40'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'bg-color' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Background color', 'text_doma'),
                    'width-100'   => true,
                    'default'     => '',
                ),
                'bg-image' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Image', 'text_doma'),
                    'default'     => '',
                    'notice_after' => sprintf( __('To ensure better performances, use optimized images for your backgrounds. You can also enable the lazy loading option in the %1$s.', 'text_doma'),
                      sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                          "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })",
                          __('site wide options', 'text_doma')
                      )
                    ),
                    'refresh_markup' => true,
                    'html_before' => '<hr/><h3>' . __('Image background', 'text-doma') .'</h3>'
                ),

                'bg-position' => array(
                    'input_type'  => 'bgPositionWithDeviceSwitcher',
                    'title'       => __('Image position', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'title_width' => 'width-100',
                ),
                // 'bg-parallax' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Parallax scrolling', 'text_doma')
                // ),
                'bg-attachment' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Fixed background', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'default'     => 0
                ),
                'bg-parallax' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Parallax effect on scroll', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    'notice_after' => __('When enabled, the background image moves slower than the page elements on scroll. This effect is not enabled on mobile devices.', 'text_doma'),
                    'refresh_markup' => true,
                ),
                'bg-parallax-force' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Parallax force (in percents)', 'text_doma'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    // 'unit' => '%',
                    'default'  => '60',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __('Customize the magnitude of the visual effect when scrolling.', 'text_doma'),
                    'refresh_markup' => true
                ),
                'bg-scale' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Scale', 'text_doma'),
                    'default'     => 'cover',
                    'choices'     => sek_get_select_options_for_input_id( 'bg-scale' )
                ),
                'bg-repeat' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Repeat', 'text_doma'),
                    'default'     => 'no-repeat',
                    'choices'     => array(
                        'default' => __('Default', 'text_dom'),
                        'no-repeat' => __('No repeat', 'text_dom'),
                        'repeat' => __('Repeat', 'text_dom'),
                        'repeat-x' => __('Repeat x', 'text_dom'),
                        'repeat-y' => __('Repeat y', 'text_dom'),
                        'round' => __('Round', 'text_dom'),
                        'space' => __('Space', 'text_dom'),
                    )
                ),
                'bg-use-video' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a video background', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    //'notice_after' => __('', 'text_doma'),
                    'refresh_markup' => true,
                    'html_before' => '<hr/><h3>' . __('Video background', 'text-doma') .'</h3>'
                ),
                'bg-video' => array(
                    'input_type'  => 'text',
                    'title'       => __('Video link', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true,
                    'notice_after' => __('Video link from YouTube, Vimeo, or a self-hosted file ( mp4 format is recommended )', 'text_doma'),
                ),
                'bg-video-loop' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Loop infinitely', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 1,
                    //'notice_after' => __('', 'text_doma'),
                    'refresh_markup' => true,
                ),
                'bg-video-delay-start' => array(
                    'input_type'  => 'number_simple',
                    'title'       => __('Play after a delay', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true,
                    'notice_after' => __('Set an optional delay in seconds before playing the video', 'text-doma')
                ),
                'bg-video-on-mobile' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Play on mobile devices', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    'notice_after' => __('Not recommended if you don\'t use a self-hosted video file', 'text_doma'),
                    'refresh_markup' => true,
                ),
                'bg-video-start-time' => array(
                    'input_type'  => 'number_simple',
                    'title'       => __('Start time', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true
                ),
                'bg-video-end-time' => array(
                    'input_type'  => 'number_simple',
                    'title'       => __('End time', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true,
                    'notice_after' => __('Set an optional start and end time in seconds', 'text-doma')
                ),
                'bg-apply-overlay' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a background overlay', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    'html_before' => '<hr/><h3>' . __('Overlay color', 'text-doma') .'</h3>'
                ),
                'bg-color-overlay' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Overlay Color', 'text_doma'),
                    'width-100'   => true,
                    'default'     => '#000000'
                ),
                'bg-opacity-overlay' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Opacity (in percents)', 'text_doma'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    // 'unit' => '%',
                    'default'  => '40',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_background', 10, 3 );

function sek_add_css_rules_for_level_background( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

    // $default_value_model = Array
    // (
    //     [bg-color] =>
    //     [bg-image] =>
    //     [bg-position] => center
    //     [bg-attachment] => 0
    //     [bg-scale] => default
    //     [bg-apply-overlay] => 0
    //     [bg-color-overlay] =>
    //     [bg-opacity-overlay] => 50
    //     [border-width] => 1
    //     [border-type] => none
    //     [border-color] =>
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_bg_module' );
    $bg_options = ( ! empty( $options[ 'bg' ] ) && is_array( $options[ 'bg' ] ) ) ? $options[ 'bg' ] : array();
    $bg_options = wp_parse_args( $bg_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $bg_options ) )
      return $rules;

    $background_properties = array();
    $bg_property_selector = '[data-sek-id="'.$level['id'].'"]';

    /* The general syntax of the background property is:
    * https://www.webpagefx.com/blog/web-design/background-css-shorthand/
    * background: [background-image] [background-position] / [background-size] [background-repeat] [background-attachment] [background-origin] [background-clip] [background-color];
    */
    // Img background
    if ( ! empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
        // deactivated when customizing @see function sek_is_img_smartload_enabled()

        //$background_properties[ 'background-image' ] = 'url("'. wp_get_attachment_url( $bg_options[ 'bg-image'] ) .'")';

        // Img Bg Position
        // 'center' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( ! empty( $bg_options[ 'bg-position'] ) && 'center' != $bg_options[ 'bg-position'] ) {
            $pos_map = array(
                'top_left'    => '0% 0%',
                'top'         => '50% 0%',
                'top_right'   => '100% 0%',
                'left'        => '0% 50%',
                'center'      => '50% 50%',
                'right'       => '100% 50%',
                'bottom_left' => '0% 100%',
                'bottom'      => '50% 100%',
                'bottom_right'=> '100% 100%'
            );
            // Retro-compat for old bg-position option without device switcher
            if ( is_string( $bg_options[ 'bg-position'] ) ) {
                $raw_pos = $bg_options[ 'bg-position'];
                $background_properties[ 'background-position' ] = array_key_exists($raw_pos, $pos_map) ? $pos_map[ $raw_pos ] : $pos_map[ 'center' ];
            } else if ( is_array( $bg_options[ 'bg-position'] ) ) {
                $mapped_bg_options = array();
                // map option with css value
                foreach ($bg_options[ 'bg-position'] as $device => $user_val ) {
                    if ( ! in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
                        sek_error_log( __FUNCTION__ . ' => error => unknown device : ' . $device );
                        continue;
                    }
                    $mapped_bg_options[$device] = array_key_exists($user_val, $pos_map) ? $pos_map[ $user_val ] : $pos_map[ 'center' ];
                }

                $rules = sek_set_mq_css_rules(array(
                    'value' => $mapped_bg_options,
                    'css_property' => 'background-position',
                    'selector' => $bg_property_selector,
                    'level_id' => $level['id']
                ), $rules );
            }
        }

        // background size
        // 'cover' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( ! empty( $bg_options['bg-scale'] ) && 'default' != $bg_options['bg-scale'] && 'cover' != $bg_options['bg-scale'] ) {
            //When specifying a background-size value, it must immediately follow the background-position value.
            $background_properties['background-size'] = $bg_options['bg-scale'];
        }

        // add no-repeat by default?
        // 'no-repeat' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( ! empty( $bg_options['bg-repeat'] ) && 'default' != $bg_options['bg-repeat'] ) {
            $background_properties['background-repeat'] = $bg_options['bg-repeat'];
        }

        // write the bg-attachment rule only if true <=> set to "fixed"
        if ( ! empty( $bg_options['bg-attachment'] ) && sek_is_checked( $bg_options['bg-attachment'] ) ) {
            $background_properties['background-attachment'] = 'fixed';
        }

    }

    //background color (needs validation: we need a sanitize hex or rgba color)
    if ( ! empty( $bg_options['bg-color'] ) ) {
        $background_properties['background-color'] = $bg_options[ 'bg-color' ];
    }


    //build background rule
    if ( ! empty( $background_properties ) ) {
        $background_css_rules = '';
        foreach ($background_properties as $bg_prop => $bg_css_val ) {
            $background_css_rules .= sprintf('%1$s:%2$s;', $bg_prop, $bg_css_val );
        }
        $rules[] = array(
            'selector' => $bg_property_selector,
            'css_rules' => $background_css_rules,
            'mq' =>null
        );
    }

    //Background overlay?
    // 1) a background image or video should be set
    // 2) the option should be checked
    if ( ( !empty( $bg_options['bg-image']) || ( sek_is_checked( $bg_options['bg-use-video'] ) && !empty( $bg_options['bg-video'] ) ) ) && !empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {
        //(needs validation: we need a sanitize hex or rgba color)
        $bg_color_overlay = isset( $bg_options[ 'bg-color-overlay' ] ) ? $bg_options[ 'bg-color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            //overlay pseudo element
            $bg_overlay_css_rules = 'content:"";display:block;position:absolute;top:0;left:0;right:0;bottom:0;background-color:'.$bg_color_overlay;

            //opacity
            //validate/sanitize
            $bg_overlay_opacity     = isset( $bg_options[ 'bg-opacity-overlay' ] ) ? filter_var( $bg_options[ 'bg-opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_css_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_css_rules . ';opacity:' . $bg_overlay_opacity : $bg_overlay_css_rules;

            // nov 2019 : added new selector '> .sek-bg-video-wrapper' for https://github.com/presscustomizr/nimble-builder/issues/287
            $rules[]     = array(
                    'selector' => implode(',', array( '[data-sek-id="'.$level['id'].'"]::before', '[data-sek-id="'.$level['id'].'"] > .sek-bg-video-wrapper::after' ) ),
                    'css_rules' => $bg_overlay_css_rules,
                    'mq' =>null
            );
            //we have to also:
            // 1) make '[data-sek-id="'.$level['id'].'"] to be relative positioned (to make the overlay absolute element referring to it)
            // 2) make any '[data-sek-id="'.$level['id'].'"] first child to be relative (not to the resizable handle div)
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => 'position:relative',
                    'mq' => null
            );

            $first_child_selector = '[data-sek-id="'.$level['id'].'"]>*';
            //in the preview we still want some elements to be absoluted positioned
            //1) the .ui-resizable-handle (jquery-ui)
            //2) the block overlay
            //3) the add content button
            if ( is_customize_preview() ) {
                $first_child_selector .= ':not(.ui-resizable-handle):not(.sek-dyn-ui-wrapper):not(.sek-add-content-button)';
            }
            $rules[]     = array(
                'selector' => $first_child_selector,
                'css_rules' => 'position:relative',
                'mq' =>null
            );
        }
    }//if ( ! empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {}

    return $rules;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_text_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_text_module',
        'name' => __('Text', 'text_doma'),
        // 'starting_value' => array(
        //     'bg-color-overlay'  => '#000000',
        //     'bg-opacity-overlay' => '40'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '', // <= this first empty selector generates a selector looking like [data-sek-id="__nimble__27f2dc680c0c"], which allows us to style text not wrapped in any specific html tags
          'p', 'a', '.sek-btn', 'button', 'input', 'select', 'optgroup', 'textarea' ),
        'tmpl' => array(
            'item-inputs' => array(
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_before' => sprintf( '<span class="czr-notice">%1$s<br/>%2$s</span><hr>',
                        __('Note : some modules have text settings in their module content tab. Those settings are applied first, before those below.'),
                        __('Text styling is always inherited in this order : section > column > module settings > module content')
                    )
                ),
                'font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family'
                ),
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height'
                ),//24,//"20px",
                'color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color'
                ),//"#000000",
                'color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color on mouse over', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover'
                ),//"#000000",
                'font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font weight', 'text_doma'),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font style', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text decoration', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text transform', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,

                'letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                )//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                // 'fonts___flag_important'  => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Apply the style options in priority (uses !important).', 'text_doma'),
                //     'default'     => 0,
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     // declare the list of input_id that will be flagged with !important when the option is checked
                //     // @see sek_add_css_rules_for_css_sniffed_input_id
                //     // @see Nsek_is_flagged_important
                //     'important_input_list' => array(
                //         'font_family_css',
                //         'font_size_css',
                //         'line_height_css',
                //         'font_weight_css',
                //         'font_style_css',
                //         'text_decoration_css',
                //         'text_transform_css',
                //         'letter_spacing_css',
                //         'color_css',
                //         'color_hover_css'
                //     )
                // )
            )//item-inputs
        )//tmpl
    );
}


?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_border_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_border_module',
        'name' => __('Borders', 'text_doma'),
        'starting_value' => array(
            'borders' => array(
                '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
            )
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border shape', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' )
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'border-radius'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    //'refresh_markup' => false,
                    //'refresh_stylesheet' => true,
                    //'css_identifier' => 'border_radius',
                    //'css_selectors'=> $css_selectors
                ),
                'shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a shadow', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default' => 0
                )
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_border', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_boxshadow', 10, 3 );


function sek_add_css_rules_for_border( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    // $default_value_model = Array
    // (
    //     [bg-color] =>
    //     [bg-image] =>
    //     [bg-position] => center
    //     [bg-attachment] => 0
    //     [bg-scale] => default
    //     [bg-apply-overlay] => 0
    //     [bg-color-overlay] =>
    //     [bg-opacity-overlay] => 50
    //     [border-type] => 'solid'
    //     [borders] => Array
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
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_border_module' );
    $normalized_border_options = ( ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) ) ? $options[ 'border' ] : array();
    $normalized_border_options = wp_parse_args( $normalized_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $normalized_border_options ) )
      return $rules;

    $border_settings = ! empty( $normalized_border_options[ 'borders' ] ) ? $normalized_border_options[ 'borders' ] : FALSE;
    $border_type = $normalized_border_options[ 'border-type' ];
    $has_border_settings  = FALSE !== $border_settings && is_array( $border_settings ) && ! empty( $border_type ) && 'none' != $border_type;

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options( $rules, $border_settings, $border_type, '[data-sek-id="'.$level['id'].'"]'  );
    }

    $has_border_radius = ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) && !empty( $options[ 'border' ]['border-radius'] );
    if ( $has_border_radius ) {
        $radius_settings = $normalized_border_options['border-radius'];
        $rules = sek_generate_css_rules_for_border_radius_options( $rules, $normalized_border_options['border-radius'], '[data-sek-id="'.$level['id'].'"]' );
    }

    return $rules;
}



function sek_add_css_rules_for_boxshadow( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    // $default_value_model = Array
    // (
    //     [bg-color] =>
    //     [bg-image] =>
    //     [bg-position] => center
    //     [bg-attachment] => 0
    //     [bg-scale] => default
    //     [bg-apply-overlay] => 0
    //     [bg-color-overlay] =>
    //     [bg-opacity-overlay] => 50
    //     [border-width] => 1
    //     [border-type] => none
    //     [border-color] =>
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_border_module' );
    $normalized_border_options = ( ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) ) ? $options[ 'border' ] : array();
    $normalized_border_options = wp_parse_args( $normalized_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $normalized_border_options) )
      return $rules;

    if ( !empty( $normalized_border_options[ 'shadow' ] ) &&  sek_is_checked( $normalized_border_options[ 'shadow'] ) ) {
        // when customizing set to !important, to override the sek-highlight-active-ui effect
        $rules[]     = array(
                'selector' => '.customizer-preview [data-sek-id="'.$level['id'].'"]',
                'css_rules' => '-webkit-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;-moz-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;',
                'mq' =>null
        );
        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'css_rules' => '-webkit-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;-moz-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;',
                'mq' =>null
        );
    }
    return $rules;
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_height_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_height_module',
        'name' => __('Height options', 'text_doma'),
        'starting_value' => array(
            'custom-height'  => array( 'desktop' => '50%' ),
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Height : auto or custom', 'text_doma'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'height-type' )
                ),
                'custom-height' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom height', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '50%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_before' => 'Note that when using a custom height, the inner content can be larger than the parent container, in particular on mobile devices. To prevent this problem, preview your page with the device switcher icons. You can also activate the overflow hidden option below.'
                ),
                // implemented to fix https://github.com/presscustomizr/nimble-builder/issues/365
                'overflow_hidden' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Overflow hidden', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('Hide the content when it is too big to fit in its parent container.', 'text_doma')
                ),
                'v_alignment' => array(
                    'input_type'  => 'verticalAlignWithDeviceSwitcher',
                    'title'       => __('Inner vertical alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'v_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_height', 10, 3 );
function sek_add_css_rules_for_level_height( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'height' ] ) )
      return $rules;

    $height_options = is_array( $options[ 'height' ] ) ? $options[ 'height' ] : array();

    // VERTICAL ALIGNMENT
    if ( ! empty( $height_options[ 'v_alignment' ] ) ) {
        if ( ! is_array( $height_options[ 'v_alignment' ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
        }
        $v_alignment_value = is_array( $height_options[ 'v_alignment' ] ) ? $height_options[ 'v_alignment' ] : array();
        $v_alignment_value = wp_parse_args( $v_alignment_value, array(
            'desktop' => 'center',
            'tablet' => '',
            'mobile' => ''
        ));
        $mapped_values = array();
        foreach ( $v_alignment_value as $device => $align_val ) {
            switch ( $align_val ) {
                case 'top' :
                    $mapped_values[$device] = "align-items:flex-start;-webkit-box-align:start;-ms-flex-align:start;";
                break;
                case 'center' :
                    $mapped_values[$device] = "align-items:center;-webkit-box-align:center;-ms-flex-align:center;";
                break;
                case 'bottom' :
                    $mapped_values[$device] = "align-items:flex-end;-webkit-box-align:end;-ms-flex-align:end";
                break;
            }
        }
        $rules = sek_set_mq_css_rules_supporting_vendor_prefixes( array(
            'css_rules_by_device' => $mapped_values,
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'level_id' => $level['id']
        ), $rules );
    }

    // CUSTOM HEIGHT BY DEVICE
    if ( ! empty( $height_options[ 'height-type' ] ) ) {
        if ( 'custom' === $height_options[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $height_options ) ? $height_options[ 'custom-height' ] : array();
            $selector = '[data-sek-id="'.$level['id'].'"]';
            if ( ! is_array( $custom_user_height ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the height option should be an array( {device} => {number}{unit} )', $custom_user_height);
            }
            $custom_user_height = is_array( $custom_user_height ) ? $custom_user_height : array();
            $custom_user_height = wp_parse_args( $custom_user_height, array(
                'desktop' => '50%',
                'tablet' => '',
                'mobile' => ''
            ));
            $height_value = $custom_user_height;
            foreach ( $custom_user_height as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $unit = '%' === $unit ? 'vh' : $unit;
                    $height_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $height_value,
                'css_property' => 'height',
                'selector' => $selector,
                'level_id' => $level['id']
            ), $rules );
        }
    }

    // OVERFLOW HIDDEN
    // implemented to fix https://github.com/presscustomizr/nimble-builder/issues/365
    if ( ! empty( $height_options[ 'overflow_hidden' ] ) && sek_booleanize_checkbox_val( $height_options[ 'overflow_hidden' ] ) ) {
        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'css_rules' => 'overflow:hidden',
            'mq' =>null
        );
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_spacing_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_spacing_module',
        'name' => __('Spacing options', 'text_doma'),
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
        if ( 'no_match' === $parent_section ) {
            sek_error_log( __FUNCTION__ . ' => $parent_section not found for level id : ' . $level['id'] );
            return $rules;
        }

        if ( $total_horizontal_margin > 0 && is_array( $parent_section ) && !empty( $parent_section ) ) {
            $total_horizontal_margin_with_unit = $total_horizontal_margin . $device_unit;//20px

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
            if ( !empty( $column_options['width'] ) && !empty( $column_options['width']['custom-width'] ) ) {
                $width_candidate = (float)$column_options['width']['custom-width'];
                if ( $width_candidate < 0 || $width_candidate > 100 ) {
                    sek_error_log( __FUNCTION__ . ' => invalid width value for column id : ' . $column['id'] );
                } else {
                    $custom_width = $width_candidate;
                }
            } else {
                // Backward compat since June 2019
                // After implementing https://github.com/presscustomizr/nimble-builder/issues/279
                $custom_width   = ( ! empty( $level[ 'width' ] ) && is_numeric( $level[ 'width' ] ) ) ? $level['width'] : null;
            }

            if ( ! is_null( $custom_width ) ) {
                $col_width_in_percent = $custom_width;
            }

            // bail here if we messed up the column width
            if ( $col_suffix < 1 )
              return $rules;

            // define a default breakpoint : 768
            $breakpoint = Sek_Dyn_CSS_Builder::$breakpoints[ Sek_Dyn_CSS_Builder::COLS_MOBILE_BREAKPOINT ];//COLS_MOBILE_BREAKPOINT = 'md' <=> 768px

            // define a default selector
            $selector = sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );

            // Is there a global custom breakpoint set ?
            $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
            $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;

            // Does the parent section have a custom breakpoint set ?
            $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( array( 'section_model' => $parent_section, 'for_responsive_columns' => true ) ) );
            $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

            if ( $has_section_custom_breakpoint ) {
                $breakpoint = $section_custom_breakpoint;
                // In this case, we need to use ".sek-section-custom-breakpoint-col-{}"
                // @see sek_add_css_rules_for_sections_breakpoint
                $selector =  sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-section-custom-breakpoint-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );
            } else if ( $has_global_custom_breakpoint ) {
                // In this case, we need to use ".sek-global-custom-breakpoint-col-{}"
                // @see sek_add_css_rules_for_sections_breakpoint
                $selector =  sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-global-custom-breakpoint-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );
                $breakpoint = $global_custom_breakpoint;
            }

            // Format width in percent with 3 digits after decimal
            $col_width_in_percent = number_format( $col_width_in_percent, 3 );
            $responsive_css_rules = sprintf( '-ms-flex: 0 0 calc(%1$s%% - %2$s) ;flex: 0 0 calc(%1$s%% - %2$s);max-width: calc(%1$s%% - %2$s)', $col_width_in_percent, $total_horizontal_margin_with_unit );

            // we need to override the rule defined in : Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
            // that's why we use a long specific selector here
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => $responsive_css_rules,
                'mq' => "(min-width: {$breakpoint}px)"
            );

            // the horizontal margin should be subtracted also to the column width of 100%, below the mobile breakpoint: basically the margin should be always subtracted to the column width for each viewport it is set
            // @see https://github.com/presscustomizr/nimble-builder/issues/217
            $responsive_css_rules_for_100_percent_width = sprintf( '-ms-flex: 0 0 calc(%1$s%% - %2$s) ;flex: 0 0 calc(%1$s%% - %2$s);max-width: calc(%1$s%% - %2$s)', 100, $total_horizontal_margin_with_unit );
            $rules[] = array(
                'selector' => sprintf('.sek-sektion-inner > [data-sek-id="%1$s"]', $level['id'] ),
                'css_rules' => $responsive_css_rules_for_100_percent_width,
                //$breakpoint_for_100_percent_width = $breakpoint-1;
                'mq' => null,// "(max-width: {$breakpoint_for_100_percent_width}px)"
            );
            //sek_error_log('padding margin', $rules );
        }//if ( $total_horizontal_margin > 0 && !empty( $parent_section ) ) {
    }// if column

    return $rules;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_width_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_module',
        'name' => __('Width options', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'width-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Width : 100% or custom', 'text_doma'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'width-type' )
                ),
                'custom-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),
                'h_alignment' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Horizontal alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for__module__options', '\Nimble\sek_add_css_rules_for_module_width', 10, 3 );
function sek_add_css_rules_for_module_width( $rules, $module ) {
    $options = empty( $module[ 'options' ] ) ? array() : $module['options'];
    if ( empty( $options[ 'width' ] ) || ! is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = is_array( $options[ 'width' ] ) ? $options[ 'width' ] : array();

    // ALIGNMENT BY DEVICE
    if ( ! empty( $width_options[ 'h_alignment' ] ) ) {
        if ( ! is_array( $width_options[ 'h_alignment' ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the h_alignment option should be an array( {device} => {alignment} )');
        }
        $h_alignment_value = is_array( $width_options[ 'h_alignment' ] ) ? $width_options[ 'h_alignment' ] : array();
        $h_alignment_value = wp_parse_args( $h_alignment_value, array(
            'desktop' => '',
            'tablet' => '',
            'mobile' => ''
        ));
        $mapped_values = array();
        foreach ( $h_alignment_value as $device => $align_val ) {
            switch ( $align_val ) {
                case 'left' :
                    $mapped_values[$device] = "align-self:flex-start;-ms-grid-row-align:start;-ms-flex-item-align:start;";
                break;
                case 'center' :
                    $mapped_values[$device] = "align-self:center;-ms-grid-row-align:center;-ms-flex-item-align:center;";
                break;
                case 'right' :
                    $mapped_values[$device] = "align-self:flex-end;-ms-grid-row-align:end;-ms-flex-item-align:end;";
                break;
            }
        }

        $rules = sek_set_mq_css_rules_supporting_vendor_prefixes( array(
            'css_rules_by_device' => $mapped_values,
            'selector' => '[data-sek-id="'.$module['id'].'"]',
            'level_id' => $module['id']
        ), $rules );
    }


    // CUSTOM WIDTH BY DEVICE
    if ( ! empty( $width_options[ 'width-type' ] ) ) {
        if ( 'custom' == $width_options[ 'width-type' ] && array_key_exists( 'custom-width', $width_options ) ) {
            $user_custom_width_value = $width_options[ 'custom-width' ];
            $selector = '[data-sek-id="'.$module['id'].'"]';

            if ( ! empty( $user_custom_width_value ) && ! is_array( $user_custom_width_value ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
            }
            $user_custom_width_value = is_array( $user_custom_width_value ) ? $user_custom_width_value : array();
            $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
                'desktop' => '100%',
                'tablet' => '',
                'mobile' => ''
            ));
            $width_value = $user_custom_width_value;
            foreach ( $user_custom_width_value as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $width_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $width_value,
                'css_property' => 'width',
                'selector' => $selector,
                'level_id' => $module['id']
            ), $rules );
        }
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_width_column() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_column',
        'name' => __('Column width', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'custom-width' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Column width in percent', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default'     => '_not_set_',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_stylesheet' => true
                )
                // 'h_alignment' => array(
                //     'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                //     'title'       => __('Horizontal alignment', 'text_doma'),
                //     'default'     => array( 'desktop' => 'center' ),
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true,
                //     'css_identifier' => 'h_alignment',
                //     'title_width' => 'width-100',
                //     'width-100'   => true,
                // )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for__column__options', '\Nimble\sek_add_css_rules_for_column_width', 10, 3 );
function sek_add_css_rules_for_column_width( $rules, $column ) {
    $options = empty( $column[ 'options' ] ) ? array() : $column['options'];
    if ( empty( $options[ 'width' ] ) || ! is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = is_array( $options[ 'width' ] ) ? $options[ 'width' ] : array();

    // ALIGNMENT BY DEVICE
    // if ( ! empty( $width_options[ 'h_alignment' ] ) ) {
    //     if ( ! is_array( $width_options[ 'h_alignment' ] ) ) {
    //         sek_error_log( __FUNCTION__ . ' => error => the h_alignment option should be an array( {device} => {alignment} )');
    //     }
    //     $h_alignment_value = is_array( $width_options[ 'h_alignment' ] ) ? $width_options[ 'h_alignment' ] : array();
    //     $h_alignment_value = wp_parse_args( $h_alignment_value, array(
    //         'desktop' => '',
    //         'tablet' => '',
    //         'mobile' => ''
    //     ));
    //     $mapped_values = array();
    //     foreach ( $h_alignment_value as $device => $align_val ) {
    //         switch ( $align_val ) {
    //             case 'left' :
    //                 $mapped_values[$device] = "flex-start";
    //             break;
    //             case 'center' :
    //                 $mapped_values[$device] = "center";
    //             break;
    //             case 'right' :
    //                 $mapped_values[$device] = "flex-end";
    //             break;
    //         }
    //     }

    //     $rules = sek_set_mq_css_rules( array(
    //         'value' => $mapped_values,
    //         'css_property' => 'align-self',
    //         'selector' => '[data-sek-id="'.$column['id'].'"]'
    //     ), $rules );
    // }


    // CUSTOM WIDTH
    if ( ! empty( $width_options[ 'width-type' ] ) ) {
        if ( 'custom' == $width_options[ 'width-type' ] && array_key_exists( 'custom-width', $width_options ) ) {
            $user_custom_width_value = $width_options[ 'custom-width' ];
            $selector = '[data-sek-id="'.$column['id'].'"]';

            if ( ! empty( $user_custom_width_value ) && ! is_array( $user_custom_width_value ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
            }
            $user_custom_width_value = is_array( $user_custom_width_value ) ? $user_custom_width_value : array();
            $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
                'desktop' => '100%',
                'tablet' => '',
                'mobile' => ''
            ));
            $width_value = $user_custom_width_value;
            foreach ( $user_custom_width_value as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $width_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $width_value,
                'css_property' => 'width',
                'selector' => $selector,
                'level_id' => $column['id']
            ), $rules );
        }
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_width_section() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_section',
        'name' => __('Width options', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        // 'starting_value' => array(
        //     'outer-section-width' => '100%',
        //     'inner-section-width' => '100%'
        // ),
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define a custom outer width for this section', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer section width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define a custom inner width for this section', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner section width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// Data structure since v1.1.0. Oct 2018
// [width] => Array
// (
//   [use-custom-outer-width] => 1
//   [outer-section-width] => Array
//       (
//           [desktop] => 99%
//           [mobile] => 66%
//           [tablet] => 93%
//       )

//   [use-custom-inner-width] => 1
//   [inner-section-width] => Array
//       (
//           [desktop] => 98em
//           [tablet] => 11em
//           [mobile] => 8em
//       )
// )
// The inner and outer widths can be set at 3 levels :
// 1) global
// 2) skope ( local )
// 3) section
// And for 3 different types of devices : desktop, tablet, mobiles.
//
// Nimble implements an inheritance for both logic, determined by the css selectors, and the media query rules.
// For example, an inner width of 85% applied for skope will win against the global one, but can be overriden by a specific inner width set at a section level.
add_filter( 'sek_add_css_rules_for__section__options', '\Nimble\sek_add_css_rules_for_section_width', 10, 3 );
function sek_add_css_rules_for_section_width( $rules, $section ) {
    $options = empty( $section[ 'options' ] ) ? array() : $section['options'];
    if ( empty( $options[ 'width' ] ) || ! is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = $options[ 'width' ];
    $user_defined_widths = array();

    if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"]';
    }
    if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"] > .sek-container-fluid > .sek-sektion-inner';
    }

    if ( empty( $user_defined_widths ) )
      return $rules;

    // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
        // If not set in the width options , it means that it is equal to default
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
                $padding_of_the_parent_container[$device] = 'inherit';
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $max_width_value,
            'css_property' => 'max-width',
            'selector' => $selector,
            'level_id' => $section['id']
        ), $rules );

        // when customizing the inner section width, we need to reset the default padding rules for .sek-container-fluid {padding-right:10px; padding-left:10px}
        // @see assets/front/scss/_grid.scss
        if ( 'inner-section-width' === $width_opt_name ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-left',
                'selector' => 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"] > .sek-container-fluid',
                'level_id' => $section['id']
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"] > .sek-container-fluid',
                'level_id' => $section['id']
            ), $rules );
        }

        if ( ! empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector,
                'level_id' => $section['id']
            ), $rules );
        }
    }//foreach

    return $rules;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_anchor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_anchor_module',
        'name' => __('Set a custom anchor', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'custom_anchor' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom anchor', 'text_doma'),
                    'default'     => '',
                    'notice_after' => __('Note : white spaces, numbers and special characters are not allowed when setting a CSS ID.'),
                    'refresh_markup' => true
                ),
                'custom_css_classes' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom CSS classes', 'text_doma'),
                    'default'     => '',
                    'notice_after' => __('Note : you can add several custom CSS classes separated by a white space.'),
                    'refresh_markup' => true
                )
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_visibility_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_visibility_module',
        'name' => __('Set visibility on devices', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'desktops' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">desktop_mac</i> %1$s', __('Visible on desktop devices', 'text_doma') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'tablets' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">tablet_mac</i> %1$s', __('Visible on tablet devices', 'text_doma') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'mobiles' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">phone_iphone</i> %1$s', __('Visible on mobile devices', 'text_doma') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('Note that those options are not applied during the live customization of your site, but only when the changes are published.', 'text_domain')
                ),
            )
        )//tmpl
    );
}


/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// levels are visible by default
// the default CSS rule should be :
// @media (min-width:768px){
//   [data-sek-level="location"] .sek-hidden-on-desktops { display: none; }
// }
// @media (min-width:575px) and (max-width:767px){
//   [data-sek-level="location"] .sek-hidden-on-tablets { display: none; }
// }
// @media (max-width:575px){
//   [data-sek-level="location"] .sek-hidden-on-mobiles { display: none; }
// }
//
// Dec 2019 : since issue https://github.com/presscustomizr/nimble-builder/issues/555, we use a dynamic CSS rule generation instead of static CSS
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_visibility', 10, 3 );
function sek_add_css_rules_for_level_visibility( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'visibility' ] ) )
      return $rules;

    $visibility_options = is_array( $options[ 'visibility' ] ) ? $options[ 'visibility' ] : array();

    // Get the default breakpoint values
    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];// 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    // nested section should inherit the custom breakpoint of the parent
    // @fixes https://github.com/presscustomizr/nimble-builder/issues/554
    $custom_tablet_breakpoint =  intval( sek_get_closest_section_custom_breakpoint( array( 'searched_level_id' => $level['id'] ) ) );

    if ( $custom_tablet_breakpoint >= 1 ) {
        $tablet_breakpoint = $custom_tablet_breakpoint;
    }

    // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
    $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;

    $visibility_value =  array(
        'desktop' => ( array_key_exists('desktops', $visibility_options ) && true !== sek_booleanize_checkbox_val( $visibility_options['desktops'] ) ) ? 'hide' : '',
        'tablet' => ( array_key_exists('tablets', $visibility_options ) && true !== sek_booleanize_checkbox_val( $visibility_options['tablets'] ) ) ? 'hide' : '',
        'mobile' => ( array_key_exists('mobiles', $visibility_options ) && true !== sek_booleanize_checkbox_val( $visibility_options['mobiles'] ) ) ? 'hide' : ''
    );

    $mob_bp_val = $mobile_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551
    foreach ( $visibility_value as $device => $visibility_val ) {
        if ( 'hide' !== $visibility_val )
          continue;

        switch( $device ) {
            case 'desktop' :
                $media_qu = "(min-width:{$tablet_breakpoint}px)";
            break;
            case 'tablet' :
                $tab_bp_val = $tablet_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551
                if ( $mobile_breakpoint >= ( $tab_bp_val ) ) {
                    $media_qu = "(max-width:{$tab_bp_val}px)";
                } else {
                    $media_qu = "(min-width:{$mob_bp_val}px) and (max-width:{$tab_bp_val}px)";
                }
            break;
            case 'mobile' :
                $media_qu = "(max-width:{$mob_bp_val}px)";
            break;
        }
        /* WHEN CUSTOMIZING MAKE SURE WE CAN SEE THE LEVELS, EVEN IF SETUP TO BE HIDDEN WITH THE CURRENT PREVIEWED DEVICE */
        $rules[] = array(
            'selector' => '.customizer-preview [data-sek-level="location"] [data-sek-id="'.$level['id'].'"]',
            'css_rules' => 'display: -ms-flexbox;display: -webkit-box;display: flex;-webkit-filter: grayscale(50%);filter: grayscale(50%);-webkit-filter: gray;filter: gray;opacity: 0.7;',
            'mq' => $media_qu
        );
        $rules[] = array(
            'selector' => '[data-sek-level="location"] [data-sek-id="'.$level['id'].'"]',
            'css_rules' => 'display:none',
            'mq' => $media_qu
        );
    }
    return $rules;
}

?><?php
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
    $custom_breakpoint = sek_get_closest_section_custom_breakpoint( array(
        'searched_level_id' => $section['id'],
        'for_responsive_columns' => true
    ));
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
        $breakpoint = $breakpoint - 1;//fixes https://github.com/presscustomizr/nimble-builder/issues/559

        $rules[] = array(
            'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner',
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

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_template() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_template',
        'name' => __('Template for the current page', 'text_doma'),
        'starting_value' => array(),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_template' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a template', 'text_doma'),
                    'default'     => 'default',
                    'width-100'   => true,
                    'choices'     => array(
                        'default' => __('Default theme template','text_doma'),
                        'nimble_template' => __('Nimble Builder template','text_doma')
                    ),
                    'refresh_preview' => true,
                    'notice_before_title' => __('Use Nimble Builder\'s template to display content created only with Nimble Builder on this page. Your theme\'s default template will be overriden','text_doma')
                    //'notice_after' => __('When you select Nimble Builder\'s template, only the Nimble sections are displayed.')
                )
            )
        )//tmpl
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_widths',
        'name' => __('Width settings of the sections in the current page', 'text_doma'),
        // 'starting_value' => array(
        //     'outer-section-width' => '100%',
        //     'inner-section-width' => '100%'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define a custom outer width for the sections of this page', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_preview' => true,
                    'notice_before_title' => sprintf( __( 'The inner and outer widths of the sections displayed in this page can be set here. It will override in the %1$s. You can also set a custom inner and outer width for each single sections.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })",
                            __('site wide options', 'text_doma')
                        )
                    ),
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer sections width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('This option will be inherited by all Nimble sections of the currently previewed page, unless for sections with a specific width option.')
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define a custom inner width for the sections of this page', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner sections width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('This option will be inherited by all Nimble sections of the currently previewed page, unless for sections with a specific width option.')
                )
            )
        )//tmpl
    );
}


// Add user local custom inner and outer widths for the sections
// Data structure since v1.1.0. Oct 2018
// [width] => Array
// (
//   [use-custom-outer-width] => 1
//   [outer-section-width] => Array
//       (
//           [desktop] => 99%
//           [mobile] => 66%
//           [tablet] => 93%
//       )

//   [use-custom-inner-width] => 1
//   [inner-section-width] => Array
//       (
//           [desktop] => 98em
//           [tablet] => 11em
//           [mobile] => 8em
//       )
// )
// The inner and outer widths can be set at 3 levels :
// 1) global
// 2) skope ( local )
// 3) section
// And for 3 different types of devices : desktop, tablet, mobiles.
//
// Nimble implements an inheritance for both logic, determined by the css selectors, and the media query rules.
// For example, an inner width of 85% applied for skope will win against the global one, but can be overriden by a specific inner width set at a section level.
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_local_widths_css', 10, 2 );
// @filter 'nimble_get_dynamic_stylesheet'
// this filter is declared in Sek_Dyn_CSS_Builder::get_stylesheet() with 2 parameters
// apply_filters( 'nimble_get_dynamic_stylesheet', $css, $this->is_global_stylesheet );
function sek_add_raw_local_widths_css( $css, $is_global_stylesheet ) {
    // the local width rules must be restricted to the local stylesheet
    if ( $is_global_stylesheet )
      return $css;

    $css = is_string( $css ) ? $css : '';
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? $_POST['local_skope_id'] : skp_build_skope_id() );

    if ( ! is_array( $local_options ) || empty( $local_options['local_options']) || empty( $local_options['local_options']['widths'] ) )
      return $css;

    $width_options = $local_options['local_options']['widths'];
    $user_defined_widths = array();

    if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = '.sektion-wrapper [data-sek-level="section"]';
    }
    if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner';
    }

    $rules = array();

    // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
        // If not set in the width options , it means that it is equal to default
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
                $padding_of_the_parent_container[$device] = 'inherit';
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $max_width_value,
            'css_property' => 'max-width',
            'selector' => $selector,
            'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
        ), $rules );

        // when customizing the inner section width, we need to reset the default padding rules for .sek-container-fluid {padding-right:10px; padding-left:10px}
        // @see assets/front/scss/_grid.scss
        if ( 'inner-section-width' === $width_opt_name ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-left',
                'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid',
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid',
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
        }

        if ( ! empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector,
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
        }
    }//foreach

    $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    return is_string( $width_options_css ) ? $css . $width_options_css : $css;
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_custom_css() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_custom_css',
        'name' => __('Custom CSS for the sections of the current page', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_custom_css' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Custom css' , 'text_doma' ),
                    'default'     => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) ),
                    'code_type' => 'text/css',// 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
                    'notice_before_title' => __('The CSS code added below will only be applied to the currently previewed page, not site wide.', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                )
            )
        )//tmpl
    );
}


// add user local custom css
// this filter is declared in Sek_Dyn_CSS_Builder::get_stylesheet() with 2 parameters
// apply_filters( 'nimble_get_dynamic_stylesheet', $css, $this->is_global_stylesheet );
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_local_custom_css', 10, 2 );
//@filter 'nimble_get_dynamic_stylesheet'
function sek_add_raw_local_custom_css( $css, $is_global_stylesheet ) {
    // the local custom css must be restricted to the local stylesheet
    if ( $is_global_stylesheet )
      return $css;
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? $_POST['local_skope_id'] : skp_build_skope_id() );
    if ( is_array( $local_options ) && !empty( $local_options['local_options']) && ! empty( $local_options['local_options']['custom_css'] ) ) {
        $options = $local_options['local_options']['custom_css'];
        if ( ! empty( $options['local_custom_css'] ) ) {
            $css .= $options['local_custom_css'];
        }
    }
    return $css;
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_reset() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_reset',
        'name' => __('Reset the sections of the current page', 'text_doma'),
        'tmpl' => array(
            'item-inputs' => array(
                'reset_local' => array(
                    'input_type'  => 'reset_button',
                    'title'       => __( 'Remove the Nimble sections in the current page' , 'text_doma' ),
                    'scope'       => 'local',
                    'notice_after' => __('This will reset the sections created for the currently previewed page only. All other sections in other contexts will be preserved.', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                )
            )
        )//tmpl
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_performances() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_performances',
        'name' => __('Performance optimizations', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local-img-smart-load' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select how you want to load the images in the sections of this page.', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the site wide option', 'text_domain' ),
                        'yes' => __('Load images on scroll ( optimized )', 'text_domain' ),
                        'no'  => __('Load all images on page load ( not optimized )', 'text_domain' )
                    ),
                    //'refresh_preview' => true,
                    'notice_after' => sprintf('%1$s <br/><strong>%2$s</strong>',
                        __( 'When you select "Load images on scroll", images below the window are loaded dynamically when scrolling. This can improve performance by reducing the weight of long web pages including multiple images.', 'text_dom'),
                        __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )//tmpl
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_header_footer() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_header_footer',
        'name' => __('Page header', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'header-footer' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a header and a footer for this page', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the site wide option', 'text_domain' ),
                        'theme' => __('Use the active theme\'s header and footer', 'text_domain' ),
                        'nimble_global' => __('Nimble site wide header and footer', 'text_domain' ),
                        'nimble_local' => __('Nimble specific header and footer for this page', 'text_domain' )
                    ),
                    'refresh_preview' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => sprintf( __( 'This option overrides the site wide header and footer options set in the %1$s for this page only.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })",
                            __('site wide options', 'text_doma')
                        )
                    ),
                )
            )
        )//tmpl
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_revisions() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_revisions',
        'name' => __('Revision history', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_revisions' => array(
                    'input_type'  => 'revision_history',
                    'title'       => __('Browse your revision history', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_before' => __('This is the revision history of the sections of the currently customized page.', 'text_doma'),
                    'notice_after' => __('Select a revision from the drop-down list to preview it. You can then restore it by clicking the Publish button at the top of the page.', 'text_doma')
                )
            )
        )//tmpl
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_imp_exp() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_imp_exp',
        'name' => __('Export / Import', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'import_export' => array(
                    'input_type'  => 'import_export',
                    'scope' => 'local',
                    'title'       => __('EXPORT', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    //'notice_before' => __('Make sure you import a file generated with Nimble Builder export system.', 'text_doma'),
                    // 'notice_after' => __('Select a revision from the drop-down list to preview it. You can then restore it by clicking the Publish button at the top of the page.', 'text_doma')
                ),
                'keep_existing_sections' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Combine the imported sections with the current ones.', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'notice_after' => __( 'Check this option if you want to keep the existing sections of this page, and combine them with the imported ones.', 'text_doma'),
                )
            )
        )//tmpl
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_text() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_text',
        'name' => __('Global text', 'text_doma'),
        // 'starting_value' => array(
        //     'global_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'default_font_family' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'refresh_preview' => false,
                    'html_before' => '<h3>' . __('GLOBAL TEXT STYLE') .'</h3>'
                ),
                'default_font_size'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                ),//16,//"14px",
                'default_line_height'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                ),//24,//"20px",
                'default_color'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'width-100'   => true,
                    'notice_before' => __('Inherits your active theme\'s option when not set.', 'text_doma')
                ),//"#000000",

                'links_color'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Links color', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'width-100'   => true,
                    'notice_before' => __('Inherits your active theme\'s option when not set.', 'text_doma'),
                    'html_before' => '<hr/><h3>' . __('GLOBAL STYLE OPTIONS FOR LINKS') .'</h3>'
                ),//"#000000",
                'links_color_hover'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Links color on mouse hover', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'width-100'   => true,
                    'notice_before' => __('Inherits your active theme\'s option when not set.', 'text_doma'),
                    'title_width' => 'width-100'
                ),//"#000000",
                'links_underlining'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link underlining', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'choices'            => array(
                        'inherit' => __('Default', 'text_doma'),
                        'underlined' => __( 'Underlined', 'text_doma'),
                        'not_underlined' => __( 'Not underlined', 'text_doma'),
                    )
                ),//null,
                'links_underlining_hover'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link underlining on mouse hover', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'choices'            => array(
                        'inherit' => __('Default', 'text_doma'),
                        'underlined' => __( 'Underlined', 'text_doma'),
                        'not_underlined' => __( 'Not underlined', 'text_doma'),
                    )
                ),//null,

                'headings_font_family' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'refresh_preview' => false,
                    'html_before' => '<hr/><h3>' . __('GLOBAL STYLE OPTIONS FOR HEADINGS') .'</h3>'
                ),
            )
        )//tmpl
    );
}



// Nimble implements an inheritance for both logic, determined by the css selectors, and the media query rules.
// For example, an inner width of 85% applied for skope will win against the global one, but can be overriden by a specific inner width set at a section level.
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_global_text_css', 10, 2 );
// @filter 'nimble_get_dynamic_stylesheet'
// this filter is declared in Sek_Dyn_CSS_Builder::get_stylesheet() with 2 parameters
// apply_filters( 'nimble_get_dynamic_stylesheet', $css, $this->is_global_stylesheet );
function sek_add_raw_global_text_css( $css, $is_global_stylesheet ) {
    // the global text rules must be restricted to the local stylesheet
    if ( !$is_global_stylesheet )
      return $css;

    $css = is_string( $css ) ? $css : '';

    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    if ( ! is_array( $global_options ) || empty( $global_options['global_text'] ) || !is_array( $global_options['global_text'] ) )
      return $css;

    $text_options = $global_options['global_text'];
    if ( ! is_array( $text_options  ) )
      return $css;

    $rules = array();
    // SELECTORS
    $default_text_selector = '.sektion-wrapper [data-sek-level], [data-sek-level] p, [data-sek-level] .sek-btn, [data-sek-level] button, [data-sek-level] input, [data-sek-level] select, [data-sek-level] optgroup, [data-sek-level] textarea';
    $links_selector = '.sektion-wrapper [data-sek-level] a';
    $links_hover_selector = '.sektion-wrapper [data-sek-level] a:hover';
    $headings_selector = '.sektion-wrapper [data-sek-level] h1, .sektion-wrapper [data-sek-level] h2, .sektion-wrapper [data-sek-level] h3, .sektion-wrapper [data-sek-level] h4, .sektion-wrapper [data-sek-level] h5, .sektion-wrapper [data-sek-level] h6';

    // DEFAULT TEXT OPTIONS
    // Font Family
    if ( !empty( $text_options['default_font_family'] ) && 'none' !== $text_options['default_font_family'] ) {
        $rules[] = array(
            'selector'    => $default_text_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'font-family', sek_extract_css_font_family_from_customizer_option( $text_options['default_font_family'] ) ),
            'mq'          => null
        );
    }
    // Font size by devices
    // @see sek_add_css_rules_for_css_sniffed_input_id()
    if ( !empty( $text_options['default_font_size'] ) ) {
        $default_font_size = $text_options['default_font_size'];
        $default_font_size = !is_array($default_font_size) ? array() : $default_font_size;
        $default_font_size = wp_parse_args( $default_font_size, array(
            'desktop' => '16px',
            'tablet' => '',
            'mobile' => ''
        ));
        $rules = sek_set_mq_css_rules( array(
            'value' => $default_font_size,
            'css_property' => 'font-size',
            'selector' => $default_text_selector,
            'is_important' => false,
            'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
        ), $rules );
    }
    // Line height
    if ( !empty( $text_options['default_line_height'] ) ) {
        $rules[] = array(
            'selector'    => $default_text_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'line-height', $text_options['default_line_height'] ),
            'mq'          => null
        );
    }
    // Color
    if ( !empty( $text_options['default_color'] ) ) {
        $rules[] = array(
            'selector'    => $default_text_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'color', $text_options['default_color'] ),
            'mq'          => null
        );
    }

    // LINKS OPTIONS
    // Color
    if ( !empty( $text_options['links_color'] ) ) {
        $rules[] = array(
            'selector'    => $links_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'color', $text_options['links_color'] ),
            'mq'          => null
        );
    }
    // Color on hover
    if ( !empty( $text_options['links_color_hover'] ) ) {
        $rules[] = array(
            'selector'    => $links_hover_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'color', $text_options['links_color_hover'] ),
            'mq'          => null
        );
    }
    // Underline
    if ( !empty( $text_options['links_underlining'] ) && 'inherit' !== $text_options['links_underlining'] ) {
        $rules[] = array(
            'selector'    => $links_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'text-decoration', 'underlined' === $text_options['links_underlining'] ? 'underline' : 'solid' ),
            'mq'          => null
        );
    }
    // Underline on hover
    if ( !empty( $text_options['links_underlining_hover'] ) && 'inherit' !== $text_options['links_underlining_hover'] ) {
        $rules[] = array(
            'selector'    => $links_hover_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'text-decoration', 'underlined' === $text_options['links_underlining_hover'] ? 'underline' : 'solid' ),
            'mq'          => null
        );
    }

    // HEADINGS OPTIONS
    // Font Family
    if ( !empty( $text_options['headings_font_family'] ) && 'none' !== $text_options['headings_font_family'] ) {
        $rules[] = array(
            'selector'    => $headings_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'font-family', sek_extract_css_font_family_from_customizer_option( $text_options['headings_font_family'] ) ),
            'mq'          => null
        );
    }

    // sek_error_log('ALORS text_options ?', $text_options);


    $global_text_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    return is_string( $global_text_options_css ) ? $css . $global_text_options_css : $css;

    // // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    // foreach ( $user_defined_widths as $width_opt_name => $selector ) {
    //     if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
    //         sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
    //     }
    //     // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
    //     // If not set in the width options , it means that it is equal to default
    //     $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
    //     $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
    //         'desktop' => '100%',
    //         'tablet' => '',
    //         'mobile' => ''
    //     ));
    //     $max_width_value = $user_custom_width_value;
    //     $margin_value = array();

    //     foreach ( $user_custom_width_value as $device => $num_unit ) {
    //         $numeric = sek_extract_numeric_value( $num_unit );
    //         if ( ! empty( $numeric ) ) {
    //             $unit = sek_extract_unit( $num_unit );
    //             $max_width_value[$device] = $numeric . $unit;
    //             $margin_value[$device] = '0 auto';
    //             $padding_of_the_parent_container[$device] = 'inherit';
    //         }
    //     }

    //     $rules = sek_set_mq_css_rules(array(
    //         'value' => $max_width_value,
    //         'css_property' => 'max-width',
    //         'selector' => $selector
    //     ), $rules );

    //     // when customizing the inner section width, we need to reset the default padding rules for .sek-container-fluid {padding-right:10px; padding-left:10px}
    //     // @see assets/front/scss/_grid.scss
    //     if ( 'inner-section-width' === $width_opt_name ) {
    //         $rules = sek_set_mq_css_rules(array(
    //             'value' => $padding_of_the_parent_container,
    //             'css_property' => 'padding-left',
    //             'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid'
    //         ), $rules );
    //         $rules = sek_set_mq_css_rules(array(
    //             'value' => $padding_of_the_parent_container,
    //             'css_property' => 'padding-right',
    //             'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid'
    //         ), $rules );
    //     }

    //     if ( ! empty( $margin_value ) ) {
    //         $rules = sek_set_mq_css_rules(array(
    //             'value' => $margin_value,
    //             'css_property' => 'margin',
    //             'selector' => $selector
    //         ), $rules );
    //     }
    // }//foreach

    // $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    // return is_string( $width_options_css ) ? $css . $width_options_css : $css;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_breakpoint() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_breakpoint',
        'name' => __('Site wide breakpoint options', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a global custom breakpoint for responsive columns', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_before_title' => __( 'This is the viewport width from which columns are rearranged vertically. The default global breakpoint is 768px. A custom breakpoint can also be set for each section.', 'text_doma')
                ),
                'global-custom-breakpoint'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Define a custom breakpoint in pixels', 'text_doma' ),
                    'default'     => 768,
                    'min'         => 1,
                    'max'         => 2000,
                    'step'        => 1,
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
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
            )
        )//tmpl
    );
}


add_action('wp_head', '\Nimble\sek_write_global_custom_breakpoint', 1000 );
function sek_write_global_custom_breakpoint() {
    $css = '';
    // delete_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    $custom_breakpoint = sek_get_global_custom_breakpoint();
    if ( $custom_breakpoint >= 1 ) {
        $css .= '@media (min-width:' . $custom_breakpoint . 'px) {.sek-global-custom-breakpoint-col-8 {-ms-flex: 0 0 8.333%;flex: 0 0 8.333%;max-width: 8.333%;}.sek-global-custom-breakpoint-col-9 {-ms-flex: 0 0 9.090909%;flex: 0 0 9.090909%;max-width: 9.090909%;}.sek-global-custom-breakpoint-col-10 {-ms-flex: 0 0 10%;flex: 0 0 10%;max-width: 10%;}.sek-global-custom-breakpoint-col-11 {-ms-flex: 0 0 11.111%;flex: 0 0 11.111%;max-width: 11.111%;}.sek-global-custom-breakpoint-col-12 {-ms-flex: 0 0 12.5%;flex: 0 0 12.5%;max-width: 12.5%;}.sek-global-custom-breakpoint-col-14 {-ms-flex: 0 0 14.285%;flex: 0 0 14.285%;max-width: 14.285%;}.sek-global-custom-breakpoint-col-16 {-ms-flex: 0 0 16.666%;flex: 0 0 16.666%;max-width: 16.666%;}.sek-global-custom-breakpoint-col-20 {-ms-flex: 0 0 20%;flex: 0 0 20%;max-width: 20%;}.sek-global-custom-breakpoint-col-25 {-ms-flex: 0 0 25%;flex: 0 0 25%;max-width: 25%;}.sek-global-custom-breakpoint-col-30 {-ms-flex: 0 0 30%;flex: 0 0 30%;max-width: 30%;}.sek-global-custom-breakpoint-col-33 {-ms-flex: 0 0 33.333%;flex: 0 0 33.333%;max-width: 33.333%;}.sek-global-custom-breakpoint-col-40 {-ms-flex: 0 0 40%;flex: 0 0 40%;max-width: 40%;}.sek-global-custom-breakpoint-col-50 {-ms-flex: 0 0 50%;flex: 0 0 50%;max-width: 50%;}.sek-global-custom-breakpoint-col-60 {-ms-flex: 0 0 60%;flex: 0 0 60%;max-width: 60%;}.sek-global-custom-breakpoint-col-66 {-ms-flex: 0 0 66.666%;flex: 0 0 66.666%;max-width: 66.666%;}.sek-global-custom-breakpoint-col-70 {-ms-flex: 0 0 70%;flex: 0 0 70%;max-width: 70%;}.sek-global-custom-breakpoint-col-75 {-ms-flex: 0 0 75%;flex: 0 0 75%;max-width: 75%;}.sek-global-custom-breakpoint-col-80 {-ms-flex: 0 0 80%;flex: 0 0 80%;max-width: 80%;}.sek-global-custom-breakpoint-col-83 {-ms-flex: 0 0 83.333%;flex: 0 0 83.333%;max-width: 83.333%;}.sek-global-custom-breakpoint-col-90 {-ms-flex: 0 0 90%;flex: 0 0 90%;max-width: 90%;}.sek-global-custom-breakpoint-col-100 {-ms-flex: 0 0 100%;flex: 0 0 100%;max-width: 100%;}}';
        printf('<style type="text/css" id="nimble-global-breakpoint-options">%1$s</style>', $css );
    }
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_widths',
        'name' => __('Site wide width options', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define a custom outer width for the sections site wide', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_preview' => true,
                    'notice_before_title' => sprintf( __( 'The inner and outer widths of your sections can be set globally here, but also overriden in the %1$s, and for each sections.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__localOptionsSection', function( _s_ ){_s_.container.find('.accordion-section-title').first().trigger('click');})",
                            __('current page options', 'text_doma')
                        )
                    ),
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer sections width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __('This option will be inherited by all Nimble sections of your site, unless for pages or sections with specific width options.')
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define a custom inner width for the sections site wide', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner sections width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __('This option will be inherited by all Nimble sections of your site, unless for pages or sections with specific width options.')
                )
            )
        )//tmpl
    );
}


// Add user site wide custom inner and outer widths for the sections
// Data structure since v1.1.0. Oct 2018
// [width] => Array
// (
//   [use-custom-outer-width] => 1
//   [outer-section-width] => Array
//       (
//           [desktop] => 99%
//           [mobile] => 66%
//           [tablet] => 93%
//       )

//   [use-custom-inner-width] => 1
//   [inner-section-width] => Array
//       (
//           [desktop] => 98em
//           [tablet] => 11em
//           [mobile] => 8em
//       )
// )
// The inner and outer widths can be set at 3 levels :
// 1) global
// 2) skope ( local )
// 3) section
// And for 3 different types of devices : desktop, tablet, mobiles.
//
// Nimble implements an inheritance for both logic, determined by the css selectors, and the media query rules.
// For example, an inner width of 85% applied for skope will win against the global one, but can be overriden by a specific inner width set at a section level.
add_action('wp_head', '\Nimble\sek_write_global_custom_section_widths', 1000 );
function sek_write_global_custom_section_widths() {
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );

    if ( ! is_array( $global_options ) || empty( $global_options['widths'] ) || !is_array( $global_options['widths'] ) )
      return;

    $width_options = $global_options['widths'];
    $user_defined_widths = array();

    if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = '[data-sek-level="section"]';
    }
    if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = '[data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner';
    }

    $rules = array();

    // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
        // If not set in the width options , it means that it is equal to default
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
                $padding_of_the_parent_container[$device] = 'inherit';
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $max_width_value,
            'css_property' => 'max-width',
            'selector' => $selector,
            'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
        ), $rules );

        // when customizing the inner section width, we need to reset the default padding rules for .sek-container-fluid {padding-right:10px; padding-left:10px}
        // @see assets/front/scss/_grid.scss
        if ( 'inner-section-width' === $width_opt_name ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-left',
                'selector' => '[data-sek-level="section"] > .sek-container-fluid',
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => '[data-sek-level="section"] > .sek-container-fluid',
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
        }

        if ( ! empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector,
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
        }
    }//foreach

    $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    if ( is_string( $width_options_css ) && ! empty( $width_options_css ) ) {
        printf('<style type="text/css" id="nimble-global-widths-options">%1$s</style>', $width_options_css );
    }
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_reset() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_reset',
        'name' => __('Reset global scope sections', 'text_doma'),
        'tmpl' => array(
            'item-inputs' => array(
                'reset_global' => array(
                    'input_type'  => 'reset_button',
                    'title'       => __( 'Remove the sections displayed globally' , 'text_doma' ),
                    'scope'       => 'global',
                    'notice_after' => __('This will remove the sections displayed on global scope locations. Local scope sections will not be impacted.', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                )
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_performances() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_performances',
        'name' => __('Site wide performance options', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'global-img-smart-load' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading off screen images', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf('<strong>%1$s</strong>',
                        __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    ),
                    'html_before' => '<hr/><h3>' . __('LAZY LOADING') .'</h3>'
                ),
                'global-bg-video-lazy-load' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading video backgrounds', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // 'notice_after' => sprintf('%1$s <br/><strong>%2$s</strong>',
                    //     __( 'Load video backgrounds when', 'text_dom'),
                    //     __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    // )
                ),

                'use_partial_module_stylesheets' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use partial CSS stylesheets for modules', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr/><h3>' . __('STYLESHEETS') .'</h3>'
                ),
                // 'print_partial_module_stylesheets_inline' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Print modules stylesheets inline', 'text_doma'),
                //     'default'     => 0,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                // ),
                'print_dyn_stylesheets_inline' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Print generated stylesheets inline', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'preload_google_fonts' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Preload Google fonts', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'preload_font_awesome' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading Font Awesome icons', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),



                'preload_front_scripts' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading and parsing of javascript files to avoid render blocking issues', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr/><h3>' . __('SCRIPTS') .'</h3>'
                ),
                'load_assets_in_ajax' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => '[Beta] ' . __('Inject non priority assets dynamically in the page only when needed.', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    //'notice_after' => __('Beta feature'),
                ),


                'load_jquery_async' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Load jQuery library asynchronously', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('Use with caution, can break third party plugins and themes.'),
                    'html_before' => '<hr/><h3>' . __('JQUERY') .'</h3>'
                ),
                'use_latest_version_jquery' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use the latest version of jQuery library on front-end, without jquery-migrate', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('Use with caution, can break third party plugins and themes.')
                ),
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_header_footer() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_header_footer',
        'name' => __('Site wide header', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'header-footer' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a site wide header and footer', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'theme' => __('Use the active theme\'s header and footer', 'text_domain' ),
                        'nimble_global' => __('Nimble site wide header and footer', 'text_domain' )
                    ),
                    //'refresh_preview' => true,
                    'notice_before_title' => sprintf( __( 'Nimble Builder allows you to build your own header and footer, or to use your theme\'s ones. This option can be overriden in the %1$s.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__localOptionsSection', function( _s_ ){_s_.container.find('.accordion-section-title').first().trigger('click');})",
                            __('current page options', 'text_doma')
                        )
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_recaptcha() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_recaptcha',
        'name' => __('Protect your contact forms with Google reCAPTCHA', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'enable' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf( '<img height="20" width="20" src="%1$s"/> %2$s', NIMBLE_BASE_URL . '/assets/img/recaptcha_32.png', __('Activate Google reCAPTCHA on your forms', 'text_doma') ),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf( __('Nimble Builder can activate the %1$s service to protect your forms against spambots. You need to %2$s.'),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://docs.presscustomizr.com/article/385-how-to-enable-recaptcha-protection-against-spam-in-your-forms-with-the-nimble-builder/?utm_source=usersite&utm_medium=link&utm_campaign=nimble-form-module', __('Google reCAPTCHA', 'text_doma') ),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://www.google.com/recaptcha/admin#list', __('get your domain API keys from Google', 'text_doma') )
                    )
                ),
                'public_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Site key', 'text_doma'),
                    'default'     => '',
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),
                'private_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Secret key', 'text_doma'),
                    'default'     => '',
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),
                'score'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Score threshold', 'text_doma' ),
                    'default'     => 0.5,
                    'min'         => 0,
                    'max'         => 1,
                    'step'        => 0.05,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'notice_after'  => __( 'reCAPTCHA returns a score from 0 to 1 on each submission. 1 is very likely a good interaction, 0 is very likely a bot. A form submission that scores lower than your threshold will be considered as done by a robot, and aborted.', 'text_doma'),
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),//0,
                'show_failure_message' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Show a failure message', 'text_doma' ),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'failure_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Failure message' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Google ReCAPTCHA validation failed. This form only accepts messages from humans.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'badge' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Show the reCAPTCHA badge at the bottom of your page', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after'       => __( 'The badge is not previewable when customizing.', 'text_doma')
                )
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_imp_exp() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_imp_exp',
        'name' => __('Export / Import global sections', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'import_export' => array(
                    'input_type'  => 'import_export',
                    'scope' => 'global',
                    'title'       => __('EXPORT', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_before' => sprintf('<span class="czr-notice">%1$s</span><br/>',__('These options allows you to export and import global sections like a global header-footer.', 'text_doma') )
                    // 'notice_after' => __('Select a revision from the drop-down list to preview it. You can then restore it by clicking the Publish button at the top of the page.', 'text_doma')
                ),
                // 'keep_existing_sections' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Combine the imported sections with the current ones.', 'text_doma'),
                //     'default'     => 0,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => false,
                //     'refresh_preview' => true,
                //     'notice_after' => __( 'Check this option if you want to keep the existing sections of this page, and combine them with the imported ones.', 'text_doma'),
                // )
            )
        )//tmpl
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_revisions() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_revisions',
        'name' => __('Revision history', 'text_doma'),
        // 'starting_value' => array(
        //     'global_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'global_revisions' => array(
                    'input_type'  => 'revision_history',
                    'title'       => __('Browse your revision history', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_before' => __('This is the revision history of the global sections displayed site wide.', 'text_doma'),
                    'notice_after' => __('Select a revision from the drop-down list to preview it. You can then restore it by clicking the Publish button at the top of the page.', 'text_doma')
                )
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_beta_features() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_beta_features',
        'name' => __('Beta features', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'beta-enabled' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Enable beta features', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_before_title' => sprintf( '%1$s <strong>%2$s</strong>',
                        __( 'Check this option to try the upcoming features of Nimble Builder.', 'text_doma') ,
                        __('There are currently no available beta features to test.', 'text_doma')
                    ),
                    'notice_after' => __( 'Be sure to refresh the customizer before you start using the beta features.', 'text_doma')
                ),
            )
        )//tmpl
    );
}

?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE HTML MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_simple_html_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_html_module',
        'name' => __( 'Html Content', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_html_module',
        'tmpl' => array(
            'item-inputs' => array(
                'html_content' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'HTML Content' , 'text_doma' ),
                    'refresh_markup' => '.sek-module-inner'
                    //'code_type' => 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Horizontal text alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'left' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    //'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_before' => '<hr/><h3>' . __('ALIGNMENT') .'</h3>'
                ),
                'font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'html_before' => '<hr/><h3>' . __('TEXT OPTIONS') .'</h3>'
                ),
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height'
                ),//24,//"20px",
                'color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color'
                ),//"#000000",
                'color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color on mouse over', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover'
                ),//"#000000",
                'font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font weight', 'text_doma'),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font style', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text decoration', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text transform', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,

                'letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'fonts___flag_important'  => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply the style options in priority (uses !important).', 'text_doma'),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see Nsek_is_flagged_important
                    'important_input_list' => array(
                        'font_family_css',
                        'font_size_css',
                        'line_height_css',
                        'font_weight_css',
                        'font_style_css',
                        'text_decoration_css',
                        'text_transform_css',
                        'letter_spacing_css',
                        'color_css',
                        'color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => "simple_html_module_tmpl.php",
        'placeholder_icon' => 'code'
    );
}

function sanitize_callback__czr_simple_html_module( $value ) {
    if ( array_key_exists( 'html_content', $value ) ) {
        if ( !current_user_can( 'unfiltered_html' ) ) {
            $value[ 'html_content' ] = wp_kses_post( $value[ 'html_content' ] );
        }
    }
    return $value;
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_tinymce_child',
            'font_settings' => 'czr_font_child'
        ),
        'name' => __('Text Editor', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
            )
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array(
            // this list is limited to the most commonly used tags in the editor.
            // note that Hx headings have a default style set in _heading.scss
            '.sek-module-inner',
            '.sek-module-inner p',
            '.sek-module-inner a',
            '.sek-module-inner li'
        ),
        'render_tmpl_path' => "tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}



/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tinymce_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tinymce_child',
        'name' => __('Content', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content' => array(
                    'input_type'  => 'detached_tinymce_editor',
                    'title'       => __('Content', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => '.sek-module-inner [data-sek-input-type="detached_tinymce_editor"]',
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'autop' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Automatically convert text into paragraph', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('WordPress wraps the editor text inside "p" tags by default. You can disable this behaviour by unchecking this option.', 'text-domain')
                ),
            )
        ),
        'render_tmpl_path' =>'',
    );
}


?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMAGE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_image_module() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_image_main_settings_child',
            'borders_corners' => 'czr_image_borders_corners_child'
        ),
        'name' => __('Image', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png',
                'custom_width' => ''
            )
        ),
        // 'sanitize_callback' => '\Nimble\czr_image_module_sanitize_validate',
        // 'validate_callback' => '\Nimble\czr_image_module_sanitize_validate',
        'render_tmpl_path' => "image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}




/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_image_main_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_main_settings_child',
        'name' => __( 'Image main settings', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'img-size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('Select a size for this image among those generated by WordPress.', 'text_doma' )
                ),
                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Schedule an action on click or tap', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => array(
                        'no-link' => __('No click action', 'text_doma' ),
                        'img-lightbox' =>__('Lightbox : enlarge the image, and dim out the rest of the content', 'text_doma' ),
                        'url' => __('Link to site content or custom url', 'text_doma' ),
                        'img-file' => __('Link to image file', 'text_doma' ),
                        'img-page' =>__('Link to image page', 'text_doma' )
                    ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'notice_after' => __('Note that some click actions are disabled during customization.', 'text_doma' ),
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new page', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'css_selectors'=> 'figure'
                ),
                'use_custom_title_attr' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Set the text displayed when the mouse is held over', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('If not specified, Nimble will use by order of priority the caption, the description, and the image title. Those properties can be edited for each image in the media library.')
                ),
                'heading_title' => array(
                    'input_type'         => 'text',
                    'title' => __('Custom text displayed on mouse hover', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true
                ),
                'use_custom_width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Custom image width', 'text_doma' ),
                    'default'     => 0,
                    'refresh_stylesheet' => true
                ),
                'custom_width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Width', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => '%',
                    'default'     => array( 'desktop' => '100%' ),
                    'max'     => 500,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 0,
                ),
                'img_hover_effect' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Mouse over effect', 'text_doma'),
                    'default'     => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'img_hover_effect' )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}






/* ------------------------------------------------------------------------- *
 *  IMAGE BORDERS AND BORDER RADIUS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_image_borders_corners_child() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_borders_corners_child',
        'name' => __( 'Borders and corners', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> $css_selectors
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_image_module', '\Nimble\sek_add_css_rules_for_czr_image_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_image_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $main_settings = $complete_modul_model['value']['main_settings'];
    $borders_corners_settings = $complete_modul_model['value']['borders_corners'];

    // WIDTH
    if ( sek_booleanize_checkbox_val( $main_settings['use_custom_width'] ) ) {
        $width = $main_settings[ 'custom_width' ];
        $css_rules = '';
        if ( isset( $width ) && FALSE !== $width ) {
            $numeric = sek_extract_numeric_value( $width );
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $width );
                $css_rules .= 'width:' . $numeric . $unit . ';';
            }
            // same treatment as in sek_add_css_rules_for_css_sniffed_input_id() => 'width'
            if ( is_string( $width ) ) {
                  $numeric = sek_extract_numeric_value($width);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $width );
                      $css_rules .= 'width:' . $numeric . $unit . ';';
                  }
            } else if ( is_array( $width ) ) {
                  $width = wp_parse_args( $width, array(
                      'desktop' => '100%',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $width;
                  foreach ($width as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'width',
                      'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                      'is_important' => false,
                      'level_id' => $complete_modul_model['id']
                  ), $rules );
            }
        }//if


        if ( !empty( $css_rules ) ) {
            $rules[] = array(
                'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                'css_rules' => $css_rules,
                'mq' =>null
            );
        }
    }

    // BORDERS
    $border_settings = $borders_corners_settings[ 'borders' ];
    $border_type = $borders_corners_settings[ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img'
        );
    }

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER FEATURED PAGES MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_featured_pages_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_featured_pages_module',
        'is_crud' => true,
        'name' => __('Featured Pages', 'text_doma'),
        // 'starting_value' => array(
        //     'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Display an image', 'text_doma'),
                    'default'     => 'featured',
                    'choices'     => sek_get_select_options_for_input_id( 'img-type' )
                ),
            ),
            // 'mod-opt' => array(
            //     // 'page-id' => array(
            //     //     'input_type'  => 'content_picker',
            //     //     'title'       => __('Pick a page', 'text_doma')
            //     // ),
            //     'mod_opt_test' => array(
            //         'input_type'  => 'simpleselect',
            //         'title'       => __('Display an image', 'text_doma'),
            //         'default'     => 'featured'
            //     ),
            // ),
            'item-inputs' => array(
                'page-id' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Pick a page', 'text_doma'),
                    'default'     => ''
                ),
                'img-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Display an image', 'text_doma'),
                    'default'     => 'featured',
                    'choices'     => sek_get_select_options_for_input_id( 'img-type' )
                ),
                'img-id' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'img-size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' )
                ),
                'content-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Display a text', 'text_doma'),
                    'default'     => 'page-excerpt',
                    'choices'     => sek_get_select_options_for_input_id( 'content-type' )
                ),
                'content-custom-text' => array(
                    'input_type'  => 'nimble_tinymce_editor',
                    'title'       => __('Custom text content', 'text_doma'),
                    'default'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
                ),
                'btn-display' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display a call to action button', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'btn-custom-text' => array(
                    'input_type'  => 'nimble_tinymce_editor',
                    'title'       => __('Custom button text', 'text_doma'),
                    'default'     => __('Read More', 'text_doma'),
                )
            )
        ),
        'render_tmpl_path' => "featured_pages_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}

?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SOCIAL ICONS MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_social_icons_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_module',
        'is_father' => true,
        'children' => array(
            'icons_collection' => 'czr_social_icons_settings_child',
            'icons_style' => 'czr_social_icons_style_child'
        ),
        'name' => __('Social Icons', 'text_doma'),
        'starting_value' => array(
            'icons_collection' => array(
                array( 'icon' => 'fab fa-facebook', 'color_css' => '#3b5998' ),
                array( 'icon' => 'fab fa-twitter', 'color_css' => '#1da1f2' ),
                array( 'icon' => 'fab fa-instagram', 'color_css' => '#262626' )
            )
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "social_icons_tmpl.php",
        // Nimble will "sniff" if we need font awesome
        // No need to enqueue font awesome here
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}


/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_social_icons_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_settings_child',
        'is_crud' => true,
        'name' => __( 'Icon collection', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an icon', 'text_doma')
                ),
                'link'  => array(
                    'input_type'  => 'text',
                    'title'       => __('Social link url', 'text_doma'),
                    'notice_after'      => __('Enter the full url of your social profile (must be valid url).', 'text_doma'),
                    'placeholder' => __('http://...,mailto:...,...', 'text_doma')
                )
            ),
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an icon', 'text_doma')
                ),
                'link'  => array(
                    'input_type'  => 'text',
                    'default'     => '',
                    'title'       => __('Social link url', 'text_doma'),
                    'notice_after'      => __('Enter the full url of your social profile (must be valid url).', 'text_doma'),
                    'placeholder' => __('http://...,mailto:...,...', 'text_doma')
                ),
                'title_attr'  => array(
                    'input_type'  => 'text',
                    'default'     => '',
                    'title'       => __('Title', 'text_domain_to_be_replaced'),
                    'notice_after'      => __('This is the text displayed on mouse over.', 'text_domain_to_be_replaced'),
                ),
                'link_target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'use_custom_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom icon color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => false,
                ),
                'social_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#969696',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'color_hover'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  SOCIAL ICONS STYLING
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_social_icons_style_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_style_child',
        'name' => __( 'Design options : size, spacing, alignment,...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Icons size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors'      => '.sek-module-inner .sek-social-icons-wrapper > li .sek-social-icon',
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors'      => '.sek-module-inner .sek-social-icons-wrapper > li .sek-social-icon',
                ),//24,//"20px",
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Horizontal alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),//consistent with SCSS
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    //'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'space_between_icons' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space between icons', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '8px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Spacing of the icons wrapper', 'text_doma' ),
                    'default'     => array('desktop' => array('margin-bottom' => '10', 'margin-top' => '10', 'unit' => 'px')),//consistent with SCSS
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    'css_selectors'      => '.sek-module-inner .sek-social-icons-wrapper',
                    // 'css_selectors'=> '.sek-icon i'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_social_icons_settings_child', '\Nimble\sek_add_css_rules_for_items_in_czr_social_icons_settings_child', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_social_icons_settings_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_social_icons_settings_child( $rules, $params ) {
    //sek_error_log('SOCIAL ITEMS PARAMS?', $params );

    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // COLOR ON HOVER
    $icon_color = $item_model['color_css'];
    if ( sek_booleanize_checkbox_val( $item_model['use_custom_color_on_hover'] ) ) {
        $color_hover = $item_model['social_color_hover'];
    } else {
        // Build the lighter rgb from the user picked bg color
        if ( 0 === strpos( $icon_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $icon_color );
            $color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $color_hover      = sek_rgb2rgba( $color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $icon_color, 'rgb' ) ) {
            $color_hover      = sek_lighten_rgb( $icon_color, $percent=15 );
        } else {
            $color_hover      = sek_lighten_hex( $icon_color, $percent=15 );
        }
    }
    $color_hover_selector = sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-social-icon:hover', $params['parent_module_id'], $item_model['id'] );
    $rules[] = array(
        'selector' => $color_hover_selector,
        'css_rules' => 'color:' . $color_hover . ';',
        'mq' =>null
    );
    return $rules;
}

// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_social_icons_module', '\Nimble\sek_add_css_rules_for_czr_social_icons_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_social_icons_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $icons_style = $value['icons_style'];

    // HORIZONTAL SPACE BETWEEN ICONS
    $padding_right = $icons_style['space_between_icons'];
    $padding_right = is_array( $padding_right ) ? $padding_right : array();
    $defaults = array(
        'desktop' => '15px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $padding_right = wp_parse_args( $padding_right, $defaults );
    $padding_right_ready_val = $padding_right;
    foreach ($padding_right as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $padding_right_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( ! empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $padding_right_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $padding_right_ready_val,
        'css_property' => 'padding-right',
        'selector' => implode(',', array(
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-social-icons-wrapper > *:not(:last-child)',
        )),
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_heading_child',
            'font_settings' => 'czr_font_child',
            'spacing' => 'czr_heading_spacing_child'
        ),
        'name' => __('Heading', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'heading_text' => 'This is a heading.'
            )
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'render_tmpl_path' => "heading_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}



/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_child',
        'name' => __('Content', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'heading_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                        'height' => 50
                    ),
                    'title'              => __( 'Heading text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'refresh_markup'    => '.sek-heading [data-sek-input-type="textarea"]'
                    //'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                ),
                'heading_tag' => array(
                    'input_type'         => 'simpleselect',
                    'title'              => __( 'Heading tag', 'text_doma' ),
                    'default'            => 'h1',
                    'choices'            => sek_get_select_options_for_input_id( 'heading_tag' )
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'heading_title' => array(
                    'input_type'         => 'text',
                    'title' => __('Display a tooltip text when the mouse is held over', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'notice_after' => __('Not previewable during customization', 'text_domain_to')
                ),
                'link-to' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Turn into a link', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new page', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' =>'',
    );
}


/* ------------------------------------------------------------------------- *
 *  HEADING SPACING CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_spacing_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_spacing_child',
        'name' => __('Spacing', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Margin and padding', 'text_doma' ),
                    'default'     => array('desktop' => array('margin-bottom' => '0.6', 'margin-top' => '0.6', 'unit' => 'em')),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    //'css_selectors'=> ''
                )
            )
        ),
        'render_tmpl_path' =>'',
    );
}




function sanitize_callback__czr_heading_module( $value ) {
    if (  !current_user_can( 'unfiltered_html' ) && array_key_exists('main_settings', $value ) && is_array( $value['main_settings'] ) && array_key_exists('heading_text', $value['main_settings'] ) ) {
        //sanitize heading_text
        if ( function_exists( 'czr_heading_module_kses_text' ) ) {
            $value['main_settings'][ 'heading_text' ] = czr_heading_module_kses_text( $value['main_settings'][ 'heading_text' ] );
        }
    }
    return $value;
    //return new \WP_Error('required' ,'heading did not pass sanitization');
}

// @see SEK_CZR_Dyn_Register::set_dyn_setting_args
// Only the boolean true or a WP_error object will be valid returned value considered when validating
function validate_callback__czr_heading_module( $value ) {
    //return new \WP_Error('required' ,'heading did not pass ');
    return true;
}


?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SPACER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );


function sek_get_module_params_for_czr_spacer_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_spacer_module',
        'name' => __('Spacer', 'text_doma'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'min'         => 0,
                    'max'         => 100,
                    'step'        => 1,
                    'title'       => __('Space', 'text_doma'),
                    'default'     => array( 'desktop' => '20px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => array( '.sek-spacer' ),
                    'css_identifier' => 'height'
                ),
            )
        ),
        'render_tmpl_path' => "spacer_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER DIVIDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_divider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_divider_module',
        'name' => __('Divider', 'text_doma'),
        'css_selectors' => array( '.sek-divider' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border_top_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Weight', 'text_doma'),
                    'min' => 1,
                    'max' => 50,
                    //'unit' => 'px',
                    'default' => '1px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_width'
                ),
                'border_top_style_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Style', 'text_doma'),
                    'default' => 'solid',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_style',
                    'choices'    => sek_get_select_options_for_input_id( 'border-type' )
                ),
                'border_top_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_color'
                ),
                'width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Width', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => '%',
                    'default' => '100%',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'width'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius'
                    //'css_selectors'=> ''
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => '.sek-module-inner',
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'v_spacing_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space before and after', 'text_doma'),
                    'min'         => 1,
                    'max'         => 100,
                    'default'     => array( 'desktop' => '15px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'v_spacing'
                ),
            )
        ),
        'render_tmpl_path' => "divider_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ICON MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_icon_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_module',
        'is_father' => true,
        'children' => array(
            'icon_settings' => 'czr_icon_settings_child',
            'spacing_border' => 'czr_icon_spacing_border_child'
        ),
        'name' => __('Icon', 'text_doma'),
        'starting_value' => array(
            'icon_settings' => array(
                'icon' =>  'far fa-star',
                'font_size_css' => '40px',
                'color_css' => '#707070',
                'color_hover' => '#969696'
            )
        ),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_icon_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "icon_module_tmpl.php",
        // Nimble will "sniff" if we need font awesome
        // see ::sek_front_needs_font_awesome()
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}





/* ------------------------------------------------------------------------- *
 *  MAIN ICON SETTINGS : ICON, SIZE, COLOR, LINK
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_settings_child',
        'name' => __( 'Icon settings', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an Icon', 'text_doma'),
                    //'default'     => 'no-link'
                ),
                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link to', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new page', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'font_size_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Size', 'text_doma'),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'       => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors' => '.sek-icon',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'use_custom_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom icon color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#969696',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'color_hover'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  ICON SPACING BORDER SHADOW
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_spacing_border_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_spacing_border_child',
        'name' => __( 'Icon options for background, spacing, border, shadow', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Spacing', 'text_doma' ),
                    'default'     => array( 'desktop' => array() ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_doma' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 0,
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}











/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_icon_module', '\Nimble\sek_add_css_rules_for_icon_front_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_icon_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];

    $icon_settings = $value['icon_settings'];

    // COLOR ON HOVER
    $icon_color = $icon_settings['color_css'];
    if ( sek_booleanize_checkbox_val( $icon_settings['use_custom_color_on_hover'] ) ) {
        $color_hover = $icon_settings['color_hover'];
    } else {
        // Build the lighter rgb from the user picked bg color
        if ( 0 === strpos( $icon_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $icon_color );
            $color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $color_hover      = sek_rgb2rgba( $color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $icon_color, 'rgb' ) ) {
            $color_hover      = sek_lighten_rgb( $icon_color, $percent=15 );
        } else {
            $color_hover      = sek_lighten_hex( $icon_color, $percent=15 );
        }
    }
    $rules[] = array(
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon i:hover',
        'css_rules' => 'color:' . $color_hover . ';',
        'mq' =>null
    );

    // BORDERS
    $border_settings = $value[ 'spacing_border' ][ 'borders' ];
    $border_type = $value[ 'spacing_border' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon-wrapper'
        );
    }

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MAP MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_map_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_map_module',
        'name' => __('Map', 'text_doma'),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_gmap_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        //'css_selectors' => array( '.sek-module-inner' ),
        'starting_value' => array(
            'address'       => 'Nice, France',
            'zoom'          => 10,
            'height_css'    => '200px'
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'address' => array(
                    'input_type'  => 'text',
                    'title'       => __( 'Address', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '',
                ),
                'zoom' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Zoom', 'text_doma' ),
                    'min' => 1,
                    'max' => 20,
                    'unit' => '',
                    'default' => 10,
                    'width-100'   => true
                ),
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Height', 'text_doma' ),
                    'min' => 1,
                    'max' => 600,
                    'default'     => array( 'desktop' => '200px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors' => array( '.sek-embed::before' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'height'
                ),
                'lazyload' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Lazy load', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf('%1$s <br/><strong>%2$s</strong>',
                        __( 'With the lazy load option enabled, Nimble loads the map when it becomes visible while scrolling. This improves your page load performances.', 'text_dom'),
                        __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    ),
                )
            )
        ),
        'render_tmpl_path' => "map_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER QUOTE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_quote_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_module',
        'is_father' => true,
        'children' => array(
            'quote_content' => 'czr_quote_quote_child',
            'cite_content' => 'czr_quote_cite_child',
            'design' => 'czr_quote_design_child'
        ),
        'name' => __('Quote', 'text_doma' ),
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_callback__czr_quote_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'quote_content' => array(
                'quote_text'  => __('Hey, careful, man, there\'s a beverage here!','text_doma'),
            ),
            'cite_content' => array(
                'cite_text'   => sprintf( __('The Dude in %1s', 'text_doma'), '<a href="https://www.imdb.com/title/tt0118715/quotes/qt0464770" rel="nofollow noopener noreferrer" target="_blank">The Big Lebowski</a>' ),
                'cite_font_style_css' => 'italic',
            ),
            'design' => array(
                'quote_design' => 'border-before'
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => "quote_module_tmpl.php",
        // Nimble will "sniff" if we need font awesome
        // No need to enqueue font awesome here
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //       )
        // )
    );
}








/* ------------------------------------------------------------------------- *
 *  QUOTE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_quote_child() {
    $quote_font_selectors = array( '.sek-quote .sek-quote-content', '.sek-quote .sek-quote-content *');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_quote_child',
        'name' => __( 'Quote content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'quote_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                    ),
                    'title'             => __( 'Main quote content', 'text_doma' ),
                    'default'           => '',
                    'width-100'         => true,
                    //'notice_before'     => __( 'You may use some html tags like a, br,p, div, span with attributes like style, id, class ...', 'text_doma'),
                    'refresh_markup'    => '.sek-quote-content'
                ),
                'quote_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $quote_font_selectors,
                ),
                'quote_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $quote_font_selectors,
                ),//16,//"14px",
                'quote_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $quote_font_selectors,
                ),//24,//"20px",
                'quote_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font weight', 'text_doma' ),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'quote_font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font style', 'text_doma' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'quote_text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text decoration', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'quote_text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text transform', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'quote_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $quote_font_selectors,
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'quote___flag_important'       => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see sek_is_flagged_important
                    'important_input_list' => array(
                        'quote_font_family_css',
                        'quote_font_size_css',
                        'quote_line_height_css',
                        'quote_font_weight_css',
                        'quote_font_style_css',
                        'quote_text_decoration_css',
                        'quote_text_transform_css',
                        'quote_letter_spacing_css',
                        'quote_color_css',
                        'quote_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}





/* ------------------------------------------------------------------------- *
 *  CITE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_cite_child() {
    $cite_font_selectors  = array( '.sek-cite', '.sek-cite *');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_cite_child',
        'name' => __( 'Cite content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'cite_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                        'height' => 50
                    ),
                    'refresh_markup' => '.sek-cite',
                    'title'              => __( 'Cite text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    //'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                ),
                'cite_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $cite_font_selectors,
                ),
                'cite_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    'default'     => array( 'desktop' => '14px' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $cite_font_selectors,
                ),//16,//"14px",
                'cite_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $cite_font_selectors,
                ),//24,//"20px",
                'cite_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font weight', 'text_doma' ),
                    'default'     => 'normal',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'cite_font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font style', 'text_doma' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $cite_font_selectors,
                    'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'cite_text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text decoration', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'cite_text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text transform', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'cite_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $cite_font_selectors,
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'cite___flag_important'       => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see Nsek_is_flagged_important
                    'important_input_list' => array(
                        'cite_font_family_css',
                        'cite_font_size_css',
                        'cite_line_height_css',
                        'cite_font_weight_css',
                        'cite_font_style_css',
                        'cite_text_decoration_css',
                        'cite_text_transform_css',
                        'cite_letter_spacing_css',
                        'cite_color_css',
                        'cite_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}










/* ------------------------------------------------------------------------- *
 *  DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_design_child() {
    $cite_font_selectors  = array( '.sek-quote-design .sek-cite', '.sek-quote-design .sek-cite a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_design_child',
        'name' => __( 'Design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'quote_design' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Design', 'text_doma' ),
                    'default'     => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'quote_design' )
                ),
                'border_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Border weight', 'text_doma' ),
                    'min' => 1,
                    'max' => 80,
                    'default' => '5px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_width',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'border_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Border Color', 'text_doma' ),
                    'width-100'   => true,
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'icon_size_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Icon Size', 'text_doma' ),
                    'default'     => '50px',
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => array( '.sek-quote.sek-quote-design.sek-quote-icon-before::before', '.sek-quote.sek-quote-design.sek-quote-icon-before' )
                ),
                'icon_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Icon Color', 'text_doma' ),
                    'width-100'   => true,
                    'default'     => '#ccc',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-quote-icon-before::before'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}










function sanitize_callback__czr_quote_module( $value ) {
    if ( !current_user_can( 'unfiltered_html' ) ) {
        if ( array_key_exists( 'quote_text', $value ) ) {
            //sanitize quote_text
            $value[ 'quote_text' ] = wp_kses_post( $value[ 'quote_text' ] );
        }
        if ( array_key_exists( 'cite_text', $value ) ) {
            //sanitize cite_text
            $value[ 'cite_text' ] = wp_kses_post( $value[ 'cite_text' ] );
        }
    }
    return $value;
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER BUTTON MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_button_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_button_module',
        'is_father' => true,
        'children' => array(
            'content' => 'czr_btn_content_child',
            'design' => 'czr_btn_design_child',
            'font' => 'czr_font_child'
        ),
        'name' => __( 'Button', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            'content' => array(
                'button_text' => __('Click me','text_doma'),
            ),
            'design' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => 'center',
                'use_box_shadow' => 1,
                'push_effect' => 1,
            ),
            'font' => array(
                'color_css'  => '#ffffff',
            )
        ),
        'css_selectors' => array( '.sek-module-inner .sek-btn' ),
        'render_tmpl_path' => "button_module_tmpl.php"
    );
}



/* ------------------------------------------------------------------------- *
 *  BUTTON CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_content_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_content_child',
        'name' => __( 'Button content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'button_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns_nolink',
                        'height' => 45
                    ),
                    'title'              => __( 'Button text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'refresh_markup'    => '.sek-btn-text'
                ),
                'btn_text_on_hover' => array(
                    'input_type'         => 'text',
                    'title'              => __( 'Tooltip text on mouse hover', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'notice_after'       => __( 'Not previewable when customizing.', 'text_doma')
                ),
                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link to', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new page', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __( 'Icon next to the button text', 'text_doma' ),
                    //'default'     => 'no-link'
                ),
                'icon-side' => array(
                    'input_type'  => 'buttons_choice',
                    'title'       => __("Icon's position", 'text_doma'),
                    'default'     => 'left',
                    'choices'     => array( 'left' => __('Left', 'text-domain'), 'right' => __('Right', 'text-domain') )
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_design_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_design_child',
        'name' => __( 'Button design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '#020202',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    //'css_selectors'=> $css_selectors
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __( 'You can also customize the text color on mouseover in the group of text settings below.', 'text_doma')
                    //'css_identifier' => 'background_color_hover',
                    //'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    //'css_selectors'=> $css_selectors
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Button alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_doma' ),
                    'default'            => array(
                        'padding-top'    => .5,
                        'padding-bottom' => .5,
                        'padding-right'  => 1,
                        'padding-left'   => 1,
                        'unit' => 'em'
                    ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'padding_margin_spacing',
                    'css_selectors'=> '.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Push visual effect', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}
















function sanitize_callback__czr_button_module( $value ) {
    if ( is_array( $value ) && is_array( $value['content'] ) && array_key_exists( 'button_text', $value['content'] ) ) {
        $value['content'][ 'button_text' ] = sanitize_text_field( $value['content'][ 'button_text' ] );
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_button_module', '\Nimble\sek_add_css_rules_for_button_front_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_button_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    // BACKGROUND
    $value = $complete_modul_model['value'];
    $design_settings = $value['design'];
    $bg_color = $design_settings['bg_color_css'];
    if ( sek_booleanize_checkbox_val( $design_settings['use_custom_bg_color_on_hover'] ) ) {
        $bg_color_hover = $design_settings['bg_color_hover'];
    } else {
        // Build the lighter rgb from the user picked bg color
        if ( 0 === strpos( $bg_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
            $bg_color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $bg_color_hover      = sek_rgb2rgba( $bg_color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
            $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=15 );
        } else {
            $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=15 );
        }
    }
    $rules[] = array(
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-btn:hover',
        'css_rules' => 'background-color:' . $bg_color_hover . ';',
        'mq' =>null
    );

    // BORDERS
    $border_settings = $design_settings[ 'borders' ];
    $border_type = $design_settings[ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-btn'
        );
    }
    return $rules;
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE FORM MODULES
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_simple_form_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_module',
        'is_father' => true,
        'children' => array(
            'form_fields'   => 'czr_simple_form_fields_child',
            'fields_design' => 'czr_simple_form_design_child',
            'form_button'   => 'czr_simple_form_button_child',
            'form_fonts'    => 'czr_simple_form_fonts_child',
            'form_submission'    => 'czr_simple_form_submission_child'
        ),
        'name' => __( 'Simple Form', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        'starting_value' => array(
            'fields_design' => array(
                'border' => array(
                    '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                )
            ),
            'form_button' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => is_rtl() ? 'right' : 'left',
                'use_box_shadow' => 1,
                'push_effect' => 1
            ),
            'form_fonts' => array(
                // 'fl_font_family_css' => '[cfont]Lucida Console,Monaco,monospace',
                'fl_font_weight_css' => 'bold',
                'btn_color_css' => '#ffffff'
            ),
            'form_submission' => array(
                'email_footer' => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                    get_bloginfo( 'name' ),
                    get_site_url( 'url' )
                )
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => "simple_form_module_tmpl.php",
    );
}











/* ------------------------------------------------------------------------- *
 *  FIELDS VISIBILITY AND REQUIRED
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fields_child() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fields_child',
        'name' => __( 'Form fields and button labels', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_name_field' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display name field', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'name_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Name field label', 'text_doma'),
                    'default'     => __('Name', 'translate')
                ),
                'name_field_required' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Name field is required', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'show_subject_field' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display subject field', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'subject_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Subject field label', 'text_doma'),
                    'default'     => __('Subject', 'translate')
                ),
                'subject_field_required' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Subject field is required', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'show_message_field' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display message field', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'message_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Message field label', 'text_doma'),
                    'default'     => __('Message', 'translate')
                ),
                'message_field_required' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Message field is required', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'email_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email field label', 'text_doma'),
                    'default'     => __('Email', 'translate')
                ),

                'button_text' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Button text', 'text_doma'),
                    'default'     => __('Submit', 'translate')
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_design_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_design_child',
        'name' => __( 'Form fields design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Fields background color', 'text_doma' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Fields border shape', 'text_doma'),
                    'default' => 'solid',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders options', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> $css_selectors
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Fields rounded corners', 'text_doma' ),
                    'default'     => '3px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'use_inset_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply an inset shadow', 'text_doma' ),
                    'default'     => 1,
                ),
                'use_outset_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply an outset shadow', 'text_doma' ),
                    'default'     => 0,
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SUBMIT BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_button_child() {
    $css_selectors = 'form input[type="submit"]';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_button_child',
        'name' => __( 'Form button design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                      'input_type'  => 'wp_color_alpha',
                      'title'       => __( 'Background color', 'text_doma' ),
                      'width-100'   => true,
                      'default'    => '',
                      'refresh_markup' => false,
                      'refresh_stylesheet' => true,
                      'css_identifier' => 'background_color',
                      'css_selectors'=> $css_selectors
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'background_color_hover',
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default'     => '2px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __( 'Button alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors'=> '.sek-form-btn-wrapper',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_doma' ),
                    'default'            => array(
                        'margin-top'     => .5,
                        'padding-top'    => .5,
                        'padding-bottom' => .5,
                        'padding-right'  => 1,
                        'padding-left'   => 1,
                        'unit' => 'em'
                    ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'padding_margin_spacing',
                    'css_selectors'=> $css_selectors,//'.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Push visual effect', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}












/* ------------------------------------------------------------------------- *
 *  FONTS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fonts_child() {
    $fl_font_selectors = array( 'form label', '.sek-form-message' ); //<= .sek-form-message is the wrapper of the form status message : Thanks, etc...
    $ft_font_selectors = array( 'form input[type="text"]', 'form input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    $btn_font_selectors = array( 'form input[type="submit"]' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fonts_child',
        'name' => __( 'Form texts options : fonts, colors, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Fields labels', 'text_doma' ),
                        'inputs' => array(
                            'fl_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $fl_font_selectors,
                            ),
                            'fl_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $fl_font_selectors,
                            ),//16,//"14px",
                            'fl_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $fl_font_selectors,
                            ),//24,//"20px",
                            'fl_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'bold',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'fl_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'fl_text_decoration_css' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text decoration', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'fl_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'fl_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_doma' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $fl_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'fl___flag_important'       => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'fl_font_family_css',
                                    'fl_font_size_css',
                                    'fl_line_height_css',
                                    'fl_font_weight_css',
                                    'fl_font_style_css',
                                    'fl_text_decoration_css',
                                    'fl_text_transform_css',
                                    'fl_letter_spacing_css',
                                    'fl_color_css',
                                    'fl_color_hover_css'
                                )
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Field Text', 'text_doma' ),
                        'inputs' => array(
                            'ft_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $ft_font_selectors,
                            ),
                            'ft_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $ft_font_selectors,
                            ),//16,//"14px",
                            'ft_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $ft_font_selectors,
                            ),//24,//"20px",
                            'ft_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'ft_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $ft_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'ft_text_decoration_css' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text decoration', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'ft_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'ft_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_doma' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $ft_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'ft___flag_important'       => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'ft_font_family_css',
                                    'ft_font_size_css',
                                    'ft_line_height_css',
                                    'ft_font_weight_css',
                                    'ft_font_style_css',
                                    'ft_text_decoration_css',
                                    'ft_text_transform_css',
                                    'ft_letter_spacing_css',
                                    'ft_color_css',
                                    'ft_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Button', 'text_doma' ),
                        'inputs' => array(
                            'btn_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $btn_font_selectors,
                            ),
                            'btn_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $btn_font_selectors,
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $btn_font_selectors,
                            ),//24,//"20px",
                            'btn_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'btn_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $btn_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'btn_text_decoration_css' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text decoration', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'btn_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'btn_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_doma' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $btn_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'btn___flag_important'       => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'btn_font_family_css',
                                    'btn_font_size_css',
                                    'btn_line_height_css',
                                    'btn_font_weight_css',
                                    'btn_font_style_css',
                                    'btn_text_decoration_css',
                                    'btn_text_transform_css',
                                    'btn_letter_spacing_css',
                                    'btn_color_css',
                                    'btn_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}

// function sanitize_callback__czr_simple_form_module( $value ) {
//     $value[ 'button_text' ] = sanitize_text_field( $value[ 'button_text' ] );
//     return $value;
// }



/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_submission_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_submission_child',
        'name' => __( 'Form submission options', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'recipients' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email recipient', 'text_doma'),
                    'default'     => get_option( 'admin_email' ),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'success_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Success message on submission' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Thanks! Your message has been sent.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false,
                    'notice_before' => __('Tip : replace the default messages with a blank space to not show anything.')
                ),
                'error_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Error message on submission' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Invalid form submission : some fields have not been entered properly.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'failure_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Failure message on submission' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Your message was not sent. Try Again.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'email_footer' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Email footer' , 'text_doma' ),
                    'notice_before' => __('Html code is allowed', 'text-domain'),
                    'default'     => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                        get_bloginfo( 'name' ),
                        get_site_url( 'url' )
                    ),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'recaptcha_enabled' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => sprintf( '%s %s',
                        '<i class="material-icons">security</i>',
                        __('Spam protection with Google reCAPTCHA', 'text_doma')
                    ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'default' => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the global option', 'text_doma'),
                        'disabled' => __('Disable', 'text_doma')
                    ),
                    'refresh_preview'  => false,
                    'refresh_markup' => false,
                    'notice_after' => sprintf( __('Nimble Builder can activate the %1$s service to protect your forms against spam. You need to %2$s.'),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://docs.presscustomizr.com/article/385-how-to-enable-recaptcha-protection-against-spam-in-your-forms-with-the-nimble-builder/?utm_source=usersite&utm_medium=link&utm_campaign=nimble-form-module', __('Google reCAPTCHA', 'text_doma') ),
                        sprintf('<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })",
                            __('activate it in the global settings', 'text_doma')
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING FOR THE FORM MODULE
/* ------------------------------------------------------------------------- */
// FORM MODULE CHILDREN
// 'children' => array(
//       'form_fields'   => 'czr_simple_form_fields_child',
//       'fields_design' => 'czr_simple_form_design_child',
//       'form_button'   => 'czr_simple_form_button_child',
//       'form_fonts'    => 'czr_simple_form_fonts_child'
//   ),
add_filter( 'sek_add_css_rules_for_module_type___czr_simple_form_module', '\Nimble\sek_add_css_rules_for_czr_simple_form_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_simple_form_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];

    // BUTTON
    if ( ! empty( $value['form_button'] ) && is_array( $value['form_button'] ) ) {
        $form_button_options = $value['form_button'];
        $bg_color = $form_button_options['bg_color_css'];
        if ( sek_booleanize_checkbox_val( $form_button_options['use_custom_bg_color_on_hover'] ) ) {
            $bg_color_hover = $form_button_options['bg_color_hover'];
        } else {
            // Build the lighter rgb from the user picked bg color
            if ( 0 === strpos( $bg_color, 'rgba' ) ) {
                list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
                $bg_color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
                $bg_color_hover      = sek_rgb2rgba( $bg_color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
            } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
                $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=15 );
            } else {
                $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=15 );
            }
        }

        $rules[] = array(
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] input[type="submit"]:hover',
            'css_rules' => 'background-color:' . $bg_color_hover . ';',
            'mq' =>null
        );

        // BUTTON BORDERS
        $border_settings = $form_button_options[ 'borders' ];
        $border_type = $form_button_options[ 'border-type' ];
        $has_border_settings  = 'none' != $border_type && !empty( $border_type );

        //border width + type + color
        if ( $has_border_settings ) {
            $rules = sek_generate_css_rules_for_multidimensional_border_options(
                $rules,
                $border_settings,
                $border_type,
                '[data-sek-id="'.$complete_modul_model['id'].'"] input[type="submit"]'
            );
        }
    }


    // FIELDS BORDERS
    $border_settings = $value[ 'fields_design' ][ 'borders' ];
    $border_type = $value[ 'fields_design' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $selector_list = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
        $css_selectors = array();
        foreach( $selector_list as $selector ) {
            $css_selectors[] = '[data-sek-id="'.$complete_modul_model['id'].'"]' . ' ' . $selector;
        }
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            implode( ', ', $css_selectors )
        );
    }
    return $rules;
}



?>
<?php
/* ------------------------------------------------------------------------- *
 *  POST GRID MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_module',
        'name' => __('Post Grid', 'text_doma'),
        'is_father' => true,
        'children' => array(
            'grid_main'   => 'czr_post_grid_main_child',
            'grid_thumb'  => 'czr_post_grid_thumb_child',
            'grid_metas'  => 'czr_post_grid_metas_child',
            'grid_fonts'  => 'czr_post_grid_fonts_child',
        ),
        'render_tmpl_path' => "post_grid_module_tmpl.php"
    );
}


/* ------------------------------------------------------------------------- *
 *  CHILD MAIN GRID SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_main_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_main_child',
        'name' => __( 'Main grid settings : layout, number of posts, columns,...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'post_number'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Number of posts', 'text_doma' ),
                    'default'     => 3,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true
                ),//0,
                'display_pagination' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display pagination links', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'posts_per_page'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Posts per page', 'text_doma' ),
                    'default'     => 10,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),//0,
                'categories'  => array(
                    'input_type'  => 'category_picker',
                    'title'       => __( 'Filter posts by category', 'text_doma' ),
                    'default'     => array(),
                    'choices'      => array(),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'notice_before' => __('Display posts that have these categories. Multiple categories allowed.', 'text_doma')
                ),//null,
                'must_have_all_cats' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display posts that have "all" of these categories', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'order_by'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Order posts by', 'text_doma' ),
                    'default'     => 'date_desc',
                    'choices'      => array(
                        'date_desc' => __('Newest to oldest', 'text_doma'),
                        'date_asc' => __('Oldest to newest', 'text_doma'),
                        'title_asc' => __('A &rarr; Z', 'text_doma'),
                        'title_desc' => __('Z &rarr; A', 'text_doma')
                    )
                ),//null,
                'layout'  => array(
                    'input_type'  => 'grid_layout',
                    'title'       => __( 'Posts layout : list or grid', 'text_doma' ),
                    'default'     => 'list',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_before' => '<hr>',
                    'refresh_stylesheet' => true //<= some CSS rules are layout dependant
                ),//null,
                'columns'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Number of columns', 'text_doma' ),
                    'default'     => array( 'desktop' => '2', 'tablet' => '2', 'mobile' => '1' ),
                    'min'         => 1,
                    'max'         => 4,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),//null,
                'img_column_width' => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Width of the image\'s column (in percent)', 'text_doma' ),
                    'default'     => array( 'desktop' => '30' ),
                    'min'         => 1,
                    'max'         => 100,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,
                'has_tablet_breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => '<i class="material-icons sek-input-title-icon">tablet_mac</i>' . __('Reorganize image and content vertically on tablet devices', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'has_mobile_breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => '<i class="material-icons sek-input-title-icon">phone_iphone</i>' . __('Reorganize image and content vertically on smartphones devices', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'show_title' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display the post title', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr>'
                ),
                'show_excerpt' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display the post excerpt', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'excerpt_length'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Excerpt length in words', 'text_doma' ),
                    'default'     => 20,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),//0,
                'space_between_el' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space between text blocks', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '15px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),

                'pg_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Text blocks alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'css_selectors' => array( '.sek-post-grid-wrapper .sek-pg-content' ),
                    'html_before' => '<hr>'
                ),

                'apply_shadow_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a shadow effect when hovering with the cursor', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'content_padding' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Content blocks padding', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '0px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),

                'custom_grid_spaces' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define custom spaces between columns and rows', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr>'
                ),
                'column_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between columns', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '20px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'row_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between rows', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '25px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                )//null,
            )
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 *  CHILD IMG SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_thumb_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_thumb_child',
        'name' => __( 'Post thumbnail settings : size, design...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                // IMAGE
                'show_thumb' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display post thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'notice_after' => __('The post thumbnail can be set as "Featured image" when creating a post.', 'text_doma')
                ),
                'img_size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the source image size of the thumbnail', 'text_doma'),
                    'title_width' => 'width-100',
                    'default'     => 'medium_large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('This allows you to select a preferred image size among those generated by WordPress.', 'text_doma' ),
                    'notice_after' => __('Note that Nimble Builder will let browsers choose the most appropriate size for better performances.', 'text_doma' )
                ),
                'img_has_custom_height' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a custom height to the thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr>'
                ),
                'img_height' => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Thumbnail height', 'text_doma' ),
                    'default'     =>  array( 'desktop' => '65' ),
                    'min'         => 1,
                    'max'         => 300,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_before' => __('Tip : the height is in percent of the image container\'s width. Applying a height of 100% makes the image square.')
                ),//null,
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> '.sek-pg-thumbnail'
                ),
                'use_post_thumb_placeholder' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a placeholder image when no post thumbnail is set', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  CHILD POST METAS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_metas_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_metas_child',
        'name' => __( 'Post metas : author, date, category, tags,...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_cats' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display categories', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_author' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display author', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_date' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display date', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_comments' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display comment number', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  FONTS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_fonts_child() {
    $pt_font_selectors = array( '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title a', '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title' );
    $pe_font_selectors = array( '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt', '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt *' );
    $cat_font_selectors = array( '.sek-module-inner .sek-pg-category a' );
    $metas_font_selectors = array( '.sek-module-inner .sek-pg-metas span', '.sek-module-inner .sek-pg-metas a');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_fonts_child',
        'name' => __( 'Grid text settings : fonts, colors, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-post-grid-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Post titles', 'text_doma' ),
                        'inputs' => array(
                            'pt_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $pt_font_selectors,
                            ),
                            'pt_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '28px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $pt_font_selectors,
                            ),//16,//"14px",
                            'pt_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.3em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $pt_font_selectors,
                            ),//24,//"20px",
                            'pt_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#444',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $pt_font_selectors,
                            ),//"#000000",
                            'pt_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $pt_font_selectors,
                            ),//"#000000",
                            'pt_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $pt_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'pt_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $pt_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'pt_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $pt_font_selectors,
                                'choices'    => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Excerpt', 'text_doma' ),
                        'inputs' => array(
                            'pe_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $pe_font_selectors,
                            ),
                            'pe_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $pe_font_selectors,
                            ),//16,//"14px",
                            'pe_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $pe_font_selectors,
                            ),//24,//"20px",
                            'pe_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#494949',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $pe_font_selectors,
                            ),//"#000000",
                            'pe_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $pe_font_selectors,
                            ),//"#000000",
                            'pe_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $pe_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'pe_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $pe_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'pe_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $pe_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Categories', 'text_doma' ),
                        'inputs' => array(
                            'cat_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $cat_font_selectors,
                            ),
                            'cat_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '14px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $cat_font_selectors,
                            ),//16,//"14px",
                            'cat_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $cat_font_selectors,
                            ),//24,//"20px",
                            'cat_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#767676',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $cat_font_selectors,
                            ),//"#000000",
                            'cat_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $cat_font_selectors,
                            ),//"#000000",
                            'cat_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $cat_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'cat_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $cat_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'cat_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'uppercase',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $cat_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Metas', 'text_doma' ),
                        'inputs' => array(
                            'met_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $metas_font_selectors,
                            ),
                            'met_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '14px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $metas_font_selectors,
                            ),//16,//"14px",
                            'met_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $metas_font_selectors,
                            ),//24,//"20px",
                            'met_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#767676',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $metas_font_selectors,
                            ),//"#000000",
                            'met_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $metas_font_selectors,
                            ),//"#000000",
                            'met_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $metas_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'met_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $metas_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'met_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'uppercase',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $metas_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    )//tab
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_post_grid_module', '\Nimble\sek_add_css_rules_for_czr_post_grid_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_post_grid_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $main_settings = $value['grid_main'];
    $thumb_settings = $value['grid_thumb'];


    // SPACE BETWEEN CONTENT ELEMENTS
    $margin_bottom = $main_settings['space_between_el'];
    $margin_bottom = is_array( $margin_bottom ) ? $margin_bottom : array();
    $defaults = array(
        'desktop' => '15px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $margin_bottom = wp_parse_args( $margin_bottom, $defaults );
    $margin_bottom_ready_val = $margin_bottom;
    foreach ($margin_bottom as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $margin_bottom_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( ! empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $margin_bottom_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $margin_bottom_ready_val,
        'css_property' => 'margin-bottom',
        'selector' => implode(',', array(
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items article .sek-pg-content > *:not(:last-child)',
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items.sek-list-layout article > *:not(:last-child):not(.sek-pg-thumbnail)',
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items.sek-grid-layout article > *:not(:last-child)'
        )),
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );



    // CONTENT BLOCKS PADDING
    $content_padding = $main_settings['content_padding'];
    $content_padding = is_array( $content_padding ) ? $content_padding : array();
    $defaults = array(
        'desktop' => '0px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $content_padding = wp_parse_args( $content_padding, $defaults );
    $content_padding_ready_val = $content_padding;
    foreach ($content_padding as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $content_padding_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( ! empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $content_padding_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $content_padding_ready_val,
        'css_property' => 'padding',
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items article .sek-pg-content',
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );



    // IMG COLUMN WIDTH IN LIST
    // - only relevant when the thumbnail is displayed
    // - default value is array( 'desktop' => '30' )
    // - default css is .sek-list-layout article.sek-has-thumb { grid-template-columns: 30% minmax(0,1fr); }
    if ( 'list' === $main_settings['layout'] && true === sek_booleanize_checkbox_val( $thumb_settings['show_thumb'] ) ) {
        $img_column_width = $main_settings['img_column_width'];
        $img_column_width = is_array( $img_column_width ) ? $img_column_width : array();
        $defaults = array(
            'desktop' => '30%',// <= this value matches the static CSS rule and the input default for the module
            'tablet' => '',
            'mobile' => ''
        );
        $img_column_width = wp_parse_args( $img_column_width, $defaults );
        $img_column_width_ready_value = $img_column_width;
        foreach ($img_column_width as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            $img_column_width_ready_value[$device] = '';
            // Leave the device value empty if === to default
            // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
            // fixes https://github.com/presscustomizr/nimble-builder/issues/419
            if ( ! empty( $num_val ) && $num_val.'%' !== $defaults[$device].'' ) {
                $num_val = $num_val > 100 ? 100 : $num_val;
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_column_width_ready_value[$device] = sprintf('%s minmax(0,1fr);', $num_val . '%');
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $img_column_width_ready_value,
            'css_property' => array( 'grid-template-columns', '-ms-grid-columns' ),
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-list-layout article.sek-has-thumb',
            'is_important' => false,
            'level_id' => $complete_modul_model['id']
        ), $rules );
    }

    // IMG HEIGHT
    // we set the height of the image container ( <a> tag ), with the padding property
    // because padding and margin are relative to the width in CSS
    // @see https://www.w3.org/TR/2011/REC-CSS2-20110607/box.html#padding-properties
    if ( true === sek_booleanize_checkbox_val( $thumb_settings['img_has_custom_height'] ) ) {
        $img_height = $thumb_settings['img_height'];
        $img_height = is_array( $img_height ) ? $img_height : array();
        $defaults = array(
            'desktop' => '65%',// <= this value matches the static CSS rule and the input default for the module
            'tablet' => '',
            'mobile' => ''
        );
        $img_height = wp_parse_args( $img_height, $defaults );

        $img_height_ready_value = $img_height;
        foreach ( $img_height as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            $img_height_ready_value[$device] = '';
            // Leave the device value empty if === to default
            // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
            // fixes https://github.com/presscustomizr/nimble-builder/issues/419
            if ( ! empty( $num_val ) && $num_val.'%' !== $defaults[$device].'' ) {
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_height_ready_value[$device] = sprintf('%s;', $num_val .'%');
            }
        }
        $rules = sek_set_mq_css_rules(array(
            'value' => $img_height_ready_value,
            'css_property' => 'padding-top',
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-thumb-custom-height figure a',
            'is_important' => false,
            'level_id' => $complete_modul_model['id']
        ), $rules );
    }


    // COLUMN AND ROW GAP
    if ( true === sek_booleanize_checkbox_val( $main_settings['custom_grid_spaces'] ) ) {
          // Horizontal Gap
          $gap = $main_settings['column_gap'];
          $gap = is_array( $gap ) ? $gap : array();
          $defaults = array(
              'desktop' => '20px',// <= this value matches the static CSS rule and the input default for the module
              'tablet' => '',
              'mobile' => ''
          );
          $gap = wp_parse_args( $gap, $defaults );
          // replace % by vh when needed
          $gap_ready_value = $gap;
          foreach ($gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              $numeric = $numeric < 0 ? '0' : $numeric;
              $gap_ready_value[$device] = '';
              // Leave the device value empty if === to default
              // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
              // fixes https://github.com/presscustomizr/nimble-builder/issues/419
              if ( ! empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
                  $unit = sek_extract_unit( $num_unit );
                  $gap_ready_value[$device] = $numeric . $unit;
              }
          }

          // for grid layout => gap between columns
          // for list layout => gap between image and content
          $rules = sek_set_mq_css_rules(array(
              'value' => $gap_ready_value,
              'css_property' => 'grid-column-gap',
              'selector' => implode( ',', [
                  '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-layout',
                  '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-list-layout article.sek-has-thumb'
              ] ),
              'is_important' => false,
              'level_id' => $complete_modul_model['id']
          ), $rules );

          // Vertical Gap => common to list and grid layout
          $v_gap = $main_settings['row_gap'];
          $v_gap = is_array( $v_gap ) ? $v_gap : array();
          $defaults = array(
              'desktop' => '25px',// <= this value matches the static CSS rule and the input default for the module
              'tablet' => '',
              'mobile' => ''
          );
          $v_gap = wp_parse_args( $v_gap, $defaults );
          // replace % by vh when needed
          $v_gap_ready_value = $v_gap;
          foreach ($v_gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              $numeric = $numeric < 0 ? 0 : $numeric;
              $v_gap_ready_value[$device] = '';
              // Leave the device value empty if === to default
              // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
              // fixes https://github.com/presscustomizr/nimble-builder/issues/419
              if ( ! empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
                  $unit = sek_extract_unit( $num_unit );
                  $v_gap_ready_value[$device] = $numeric . $unit;
              }
          }

          $rules = sek_set_mq_css_rules(array(
              'value' => $v_gap_ready_value,
              'css_property' => 'grid-row-gap',
              'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items',
              'is_important' => false,
              'level_id' => $complete_modul_model['id']
          ), $rules );
    }
    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER BUTTON MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_menu_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_module',
        'is_father' => true,
        'children' => array(
            'content' => 'czr_menu_content_child',
            //'design' => 'czr_menu_design_child',
            'font' => 'czr_font_child',
            'mobile_options' => 'czr_menu_mobile_options'

        ),
        'name' => __( 'Menu', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            // 'content' => array(
            //     'button_text' => __('Click me','text_doma'),
            // ),
            // 'design' => array(
            //     'bg_color_css' => '#020202',
            //     'bg_color_hover' => '#151515', //lighten 15%,
            //     'use_custom_bg_color_on_hover' => 0,
            //     'border_radius_css' => '2',
            //     'h_alignment_css' => 'center',
            //     'use_box_shadow' => 1,
            //     'push_effect' => 1,
            // ),
            // 'font' => array(
            //     'color_css'  => '#ffffff',
            // )
        ),
        'css_selectors' => array( '.sek-menu-module > li > a' ),//<=@see tmpl/modules/menu_module_tmpl.php
        'render_tmpl_path' => "menu_module_tmpl.php"
    );
}

/* ------------------------------------------------------------------------- *
 *  MENU CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_content_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_content_child',
        'name' => __( 'Menu content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'menu-id' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a menu', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_user_created_menus(),
                    'notice_after' => sprintf( __( 'You can create and edit menus in the %1$s. If you just created a new menu, publish and refresh the customizer to see in the dropdown list.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.panel('nav_menus', function( _p_ ){ _p_.focus(); })",
                            __('menu panel', 'text_doma')
                        )
                    ),
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Menu items alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_flex_alignment',
                    'css_selectors' => array( '.sek-nav-collapse', '[data-sek-is-mobile-menu="yes"] .sek-nav li a' ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'hamb_h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Hamburger button alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_flex_alignment',
                    'css_selectors' => array( '.sek-nav-wrap' ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
            ),
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 * MOBILE OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_mobile_options() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_mobile_options',
        'name' => __( 'Settings for mobile devices', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'expand_below' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('%s %s', '<i class="material-icons sek-level-option-icon">devices</i>', __('On mobile devices, expand the menu in full width below the menu hamburger icon.', 'text_doma') ),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
            ),
        ),
        'render_tmpl_path' => '',
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  GENERIC FONT CHILD MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_font_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_font_child',
        'name' => __( 'Text settings : font, color, size, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => '',
        'tmpl' => array(
            'item-inputs' => array(
                'font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family'
                ),
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height'
                ),//24,//"20px",
                'color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color'
                ),//"#000000",
                'color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color on mouse over', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover'
                ),//"#000000",
                'font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font weight', 'text_doma'),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font style', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text decoration', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text transform', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,

                'letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'fonts___flag_important'  => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply the style options in priority (uses !important).', 'text_doma'),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see Nsek_is_flagged_important
                    'important_input_list' => array(
                        'font_family_css',
                        'font_size_css',
                        'line_height_css',
                        'font_weight_css',
                        'font_style_css',
                        'text_decoration_css',
                        'text_transform_css',
                        'letter_spacing_css',
                        'color_css',
                        'color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER WIDGET ZONE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_widget_area_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_widget_area_module',
        'name' => __('Widget Zone', 'text_doma'),
        //'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'widget-area-id' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a widget area', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => array(),
                    'refresh_preview' => true,// <= so that the partial refresh links are displayed
                    'html_before' => '<span class="czr-notice">' . __('This module allows you to embed any WordPress widgets in your Nimble sections.', 'text_doma') . '<br/>' . __('1) Select a widget area in the dropdown list,', 'text_doma') . '<br/>' . sprintf( __( '2) once selected an area, you can add and edit the WordPress widgets in it in the %1$s.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s"><strong>%2$s</strong></a>',
                            "javascript:wp.customize.panel('widgets', function( _p_ ){ _p_.focus(); })",
                            __('widget panel', 'text_doma')
                        )
                    ) . '</span><br/>'
                )
            )
        ),
        'render_tmpl_path' => "widget_area_module_tmpl.php",
    );
}

?><?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMG SLIDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_img_slider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_module',
        'is_father' => true,
        'children' => array(
            'img_collection' => 'czr_img_slider_collection_child',
            'slider_options' => 'czr_img_slider_opts_child'
        ),
        'name' => __('Image & Text Carousel', 'text_doma'),
        'starting_value' => array(
            'img_collection' => array(
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' )
            )
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '[data-sek-swiper-id]' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "img_slider_tmpl.php",
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}


/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_collection_child() {
    $text_content_selector = array( '.sek-slider-text-content', '.sek-slider-text-content *' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">photo_library</i> %1$s', __( 'Slide collection', 'text_doma' ) ),
        'starting_value' => array(
            'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
            ),
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Image', 'text_doma' ),
                        'inputs' => array(
                            'img' => array(
                                'input_type'  => 'upload',
                                'title'       => __('Pick an image', 'text_doma'),
                                'default'     => ''
                            ),
                            'title_attr'  => array(
                                'input_type'  => 'text',
                                'default'     => '',
                                'title'       => __('Title', 'text_domain_to_be_replaced'),
                                'notice_after' => sprintf( __('This is the text displayed on mouse over. You can use the following template tags referring to the image attributes : %1$s', 'text_domain_to_be_replaced'), '&#123;&#123;title&#125;&#125;, &#123;&#123;caption&#125;&#125;, &#123;&#123;description&#125;&#125;' )
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Text', 'text_doma' ),
                        'inputs' => array(
                            'enable_text' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Add text content', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Note : you can adjust the text color and / or use a color overlay to improve accessibility of your text content.', 'text_doma')
                            ),
                            'text_content' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => false,
                                    'includedBtns' => 'basic_btns',
                                ),
                                'title'             => __( 'Text content', 'text_doma' ),
                                'default'           => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-slider-text-content',
                                'notice_before' => sprintf( __('You may use some html tags in the "text" tab of the editor. You can also use the following template tags referring to the image attributes : %1$s', 'text_domain_to_be_replaced'), '&#123;&#123;title&#125;&#125;, &#123;&#123;caption&#125;&#125;, &#123;&#123;description&#125;&#125;' )
                            ),

                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#e2e2e2',// why this light grey ? => if set to white ( #fff ), the text is not visible when no image is picked, which might be difficult to understand for users
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $text_content_selector,
                            ),//"#000000",

                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $text_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>'
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $text_content_selector,
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $text_content_selector,
                            ),//24,//"20px",

                            'h_alignment_css' => array(
                                'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                                'title'       => __('Horizontal alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center'),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'css_selectors' => array( '.sek-slider-text-content' ),
                                'html_before' => '<hr/><h3>' . __('ALIGNMENTS') .'</h3>'
                            ),
                            'v_alignment' => array(
                                'input_type'  => 'verticalAlignWithDeviceSwitcher',
                                'title'       => __('Vertical alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center' ),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                //'css_identifier' => 'v_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                            ),
                            'spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing of the text content', 'text_doma' ),
                                'default'     => array('desktop' => array(
                                    'padding-bottom' => '5',
                                    'padding-top' => '5',
                                    'padding-right' => '5',
                                    'padding-left' => '5',
                                    'unit' => '%')
                                ),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors' => array( '.sek-slider-text-content' ),
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Color overlay', 'text_doma' ),
                        'inputs' => array(
                            'apply-overlay' => array(
                                'input_type'  => 'nimblecheck',
                                'notice_after' => __('A color overlay is usually recommended when displaying text content on top of the image. You can customize the color and transparency in the global design settings of the carousel.', 'text_doma' ),
                                'title'       => __('Apply a color overlay', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'html_before' => '<hr/><h3>' . __('COLOR OVERLAY') .'</h3>'
                            ),
                            'color-overlay' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Overlay Color', 'text_doma'),
                                'width-100'   => true,
                                'default'     => '#000000',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true
                            ),
                            'opacity-overlay' => array(
                                'input_type'  => 'range_simple',
                                'title'       => __('Opacity (in percents)', 'text_doma'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                // 'unit' => '%',
                                'default'  => '30',
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true
                            )
                        )
                    )
                )//'tabs'
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  SLIDER OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_opts_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Slider options : height, autoplay, navigation...', 'text_doma' ) ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'General', 'text_doma' ),
                        'inputs' => array(
                            'image-layout' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Image layout', 'text_doma'),
                                'default'     => 'width-100',
                                'choices'     => array(
                                    'nimble-wizard' => __('Nimble wizard', 'text_doma' ),
                                    'width-100' => __('Adapt images to carousel\'s width', 'text_doma' ),
                                    'height-100' => __('Adapt images to carousel\'s height', 'text_doma' ),
                                ),
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'notice_before' => __('Nimble wizard ensures that the images fill all available space of the carousel in any devices, without blank spaces on the edges, and without stretching the images.', 'text_doma' ),
                            ),
                            'autoplay' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Autoplay', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Note that the autoplay is disabled during customization.', 'text_doma' ),
                            ),
                            'autoplay_delay' => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Delay between each slide in milliseconds (ms)', 'text_doma' ),
                                'min' => 1,
                                'max' => 30000,
                                'step' => 500,
                                'unit' => '',
                                'default' => 3000,
                                'width-100'   => true,
                                'title_width' => 'width-100'
                            ),
                            'pause_on_hover' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Pause autoplay on mouse over', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'infinite_loop' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Infinite loop', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            // added dec 2019 for https://github.com/presscustomizr/nimble-builder/issues/570
                            'lazy_load' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Lazy load images', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Lazy loading images improves page load performances.', 'text_doma' ),
                            ),
                        )//inputs
                    ),
                    array(
                        'title' => __( 'Height', 'text_doma' ),
                        'inputs' => array(
                            'height-type' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Height : auto or custom', 'text_doma'),
                                'default'     => 'custom',
                                'choices'     => sek_get_select_options_for_input_id( 'height-type' ),// auto, custom
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'html_before' => '<hr/><h3>' . __('SLIDER HEIGHT') .'</h3>'
                            ),
                            'custom-height' => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'title'       => __('Custom height', 'text_doma'),
                                'min' => 0,
                                'max' => 1000,
                                'default'     => array( 'desktop' => '400px', 'mobile' => '200px' ),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Navigation', 'text_doma' ),
                        'inputs' => array(
                            'nav_type' => array(
                                'input_type'  => 'simpleselect',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'default' => 'arrows',
                                'choices'     => array(
                                    'arrows_dots' => __('Arrows and bullets', 'text_doma'),
                                    'arrows' => __('Arrows only', 'text_doma'),
                                    'dots' => __('Bullets only', 'text_doma'),
                                    'none' => __('None', 'text_doma')
                                ),
                                'html_before' => '<hr/><h3>' . __('NAVIGATION') .'</h3>'
                            ),
                            'hide_nav_on_mobiles' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Hide arrows and bullets on mobiles', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            // 'arrows_size'  => array(
                            //     'input_type'  => 'range_simple_device_switcher',
                            //     'title'       => __( 'Size of the arrows', 'text_doma' ),
                            //     'default'     => array( 'desktop' => '18'),
                            //     'min'         => 1,
                            //     'max'         => 50,
                            //     'step'        => 1,
                            //     'width-100'   => true,
                            //     'title_width' => 'width-100'
                            // ),//null,
                            'arrows_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Color of the navigation arrows', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => array('.sek-swiper-nav .sek-swiper-arrows .sek-chevron')
                            ),
                            // 'dots_size'  => array(
                            //     'input_type'  => 'range_simple_device_switcher',
                            //     'title'       => __( 'Size of the dots', 'text_doma' ),
                            //     'default'     => array( 'desktop' => '16'),
                            //     'min'         => 1,
                            //     'max'         => 50,
                            //     'step'        => 1,
                            //     'width-100'   => true,
                            //     'title_width' => 'width-100'
                            // ),//null,
                            'dots_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Color of the active pagination bullet', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => array('.swiper-pagination-bullet-active')
                            ),
                        )//inputs
                    )
                )//tabs
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_img_slider_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_img_slider_collection_child', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_img_slider_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_img_slider_collection_child( $rules, $params ) {
    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();
    $all_defaults = sek_get_default_module_model( 'czr_img_slider_collection_child');
    // Default :
    // [v_alignment] => Array
    // (
    //     [desktop] => center
    // )
    // VERTICAL ALIGNMENT
    if ( ! empty( $item_model[ 'v_alignment' ] ) && $all_defaults['v_alignment'] != $item_model[ 'v_alignment' ] ) {
        if ( ! is_array( $item_model[ 'v_alignment' ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
        }
        $v_alignment_value = is_array( $item_model[ 'v_alignment' ] ) ? $item_model[ 'v_alignment' ] : array();
        $v_alignment_value = wp_parse_args( $v_alignment_value, array(
            'desktop' => 'center',
            'tablet' => '',
            'mobile' => ''
        ));
        $mapped_values = array();
        foreach ( $v_alignment_value as $device => $align_val ) {
            switch ( $align_val ) {
                case 'top' :
                    $mapped_values[$device] = "align-items:flex-start;-webkit-box-align:start;-ms-flex-align:start;";
                break;
                case 'center' :
                    $mapped_values[$device] = "align-items:center;-webkit-box-align:center;-ms-flex-align:center;";
                break;
                case 'bottom' :
                    $mapped_values[$device] = "align-items:flex-end;-webkit-box-align:end;-ms-flex-align:end";
                break;
            }
        }
        $rules = sek_set_mq_css_rules_supporting_vendor_prefixes( array(
            'css_rules_by_device' => $mapped_values,
            'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] ),
            'level_id' => $params['parent_module_id']
        ), $rules );
    }//Vertical alignment

    //Background overlay?
    // 1) a background image should be set
    // 2) the option should be checked
    if ( sek_is_checked( $item_model[ 'apply-overlay'] ) ) {
        //(needs validation: we need a sanitize hex or rgba color)
        $bg_color_overlay = isset( $item_model[ 'color-overlay' ] ) ? $item_model[ 'color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            //overlay pseudo element
            $bg_overlay_css_rules = 'background-color:'.$bg_color_overlay;

            //opacity
            //validate/sanitize
            $bg_overlay_opacity     = isset( $item_model[ 'opacity-overlay' ] ) ? filter_var( $item_model[ 'opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_css_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_css_rules . ';opacity:' . $bg_overlay_opacity . ';' : $bg_overlay_css_rules;

            $rules[]     = array(
                'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"][data-sek-has-overlay="true"] .sek-carousel-img::after', $params['parent_module_id'], $item_model['id'] ),
                'css_rules' => $bg_overlay_css_rules,
                'mq' =>null
            );
        }
    }// BG Overlay

    return $rules;
}




// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_img_slider_module', '\Nimble\sek_add_css_rules_for_czr_img_slider_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_img_slider_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $slider_options = $value['slider_options'];

    $selector = '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .swiper-container .swiper-wrapper';


    // CUSTOM HEIGHT BY DEVICE
    if ( ! empty( $slider_options[ 'height-type' ] ) ) {
        if ( 'custom' === $slider_options[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $slider_options ) ? $slider_options[ 'custom-height' ] : array();

            if ( ! is_array( $custom_user_height ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the height option should be an array( {device} => {number}{unit} )', $custom_user_height);
            }
            $custom_user_height = is_array( $custom_user_height ) ? $custom_user_height : array();

            // DEFAULTS :
            // array(
            //     'desktop' => '400px',
            //     'tablet' => '',
            //     'mobile' => '200px'
            // );
            $all_defaults = sek_get_default_module_model( 'czr_img_slider_module');
            $slider_defaults = $all_defaults['slider_options'];
            $defaults = $slider_defaults['custom-height'];

            $custom_user_height = wp_parse_args( $custom_user_height, $defaults );

            if ( $defaults != $custom_user_height ) {
                $height_value = $custom_user_height;
                foreach ( $custom_user_height as $device => $num_unit ) {
                    $numeric = sek_extract_numeric_value( $num_unit );
                    if ( ! empty( $numeric ) ) {
                        $unit = sek_extract_unit( $num_unit );
                        $unit = '%' === $unit ? 'vh' : $unit;
                        $height_value[$device] = $numeric . $unit;
                    }
                }

                $rules = sek_set_mq_css_rules(array(
                    'value' => $height_value,
                    'css_property' => 'height',
                    'selector' => $selector,
                    'level_id' => $complete_modul_model['id']
                ), $rules );
            }
        }// if custom height
        else {
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => 'height:auto;',
                'mq' =>null
            );
        }
    }// Custom height rules

    return $rules;
}


?><?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ACCORDION MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_accordion_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_module',
        'is_father' => true,
        'children' => array(
            'accord_collec' => 'czr_accordion_collection_child',
            'accord_opts' => 'czr_accordion_opts_child'
        ),
        'name' => __('Accordion', 'text_doma'),
        'starting_value' => array(
            'accord_collec' => array(
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'),
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'),
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.')
            )
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '[data-sek-accordion-id]' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "accordion_tmpl.php",
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}


/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_accordion_collection_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">toc</i> %1$s', __( 'Item collection', 'text_doma' ) ),
        'starting_value' => array(
            'text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
        ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
            ),
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Title', 'text_doma' ),
                        'inputs' => array(
                            'title_text' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => false,
                                    'includedBtns' => 'basic_btns',
                                    'height' => 50
                                ),
                                'title'              => __( 'Heading text', 'text_doma' ),
                                'default'            => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-inner-accord-title',
                                'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                            ),
                            'title_attr'  => array(
                                'input_type'  => 'text',
                                'default'     => '',
                                'title'       => __('Title on mouse over', 'text_domain_to_be_replaced'),
                                'notice_after' => __('This is the text displayed on mouse over.' )
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Content', 'text_doma' ),
                        'inputs' => array(
                            'text_content' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => true,
                                    'includedBtns' => 'basic_btns_with_lists',
                                ),
                                'title'             => __( 'Text content', 'text_doma' ),
                                'default'           => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-accord-content',
                                'notice_before' => __('You may use some html tags in the "text" tab of the editor.', 'text_domain_to_be_replaced')
                            ),
                            'h_alignment_css' => array(
                                'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                                'title'       => __('Horizontal alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center'),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'css_selectors' => array( '.sek-accord-content' )
                            )
                        )
                    ),
                )//'tabs'
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  ACCORDION OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_accordion_opts_child() {
    $title_content_selector = array( '.sek-accord-item .sek-accord-title *' );
    $main_content_selector = array( '.sek-accord-item .sek-accord-content', '.sek-accord-item .sek-accord-content *' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Accordion options : font style, borders, background, ...', 'text_doma' ) ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'General', 'text_doma' ),
                        'inputs' => array(
                            'first_expanded' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display first item expanded', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'one_expanded' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display one item expanded at a time', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'border_width_css' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Border weight', 'text_doma' ),
                                'min' => 0,
                                'max' => 80,
                                'default' => '1px',
                                'width-100'   => true,
                                //'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item',
                                'html_before' => '<hr/><h3>' . __('BORDER') .'</h3>'
                            ),
                            'border_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border color', 'text_doma' ),
                                'width-100'   => true,
                                'default'     => '#e3e3e3',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item'
                            ),
                        )//inputs
                    ),
                    array(
                        'title' => __( 'Title style', 'text_doma' ),
                        'inputs' => array(
                            'title_bg_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Backround color', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title',
                                'html_before' => '<h3>' . __('COLOR AND BACKGROUND') .'</h3>'
                            ),
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#565656',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $title_content_selector
                            ),//"#000000",

                            'color_active_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title_width' => 'width-100',
                                'title'       => __( 'Text color when active', 'text_doma' ),
                                'default'     => '#1e261f',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => array( '.sek-accord-item .sek-accord-title:hover *', '[data-sek-expanded="true"] .sek-accord-title *')
                            ),//"#000000",

                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $title_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>'
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100' => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $title_content_selector
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100' => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $title_content_selector
                            ),//24,//"20px",
                            'title_border_w_css' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Border bottom weight', 'text_doma' ),
                                'min' => 0,
                                'max' => 80,
                                'default' => '1px',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title',
                                'html_before' => '<hr/><h3>' . __('BORDER BOTTOM') .'</h3>'
                            ),
                            'title_border_c_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border bottom color', 'text_doma' ),
                                'width-100'   => true,
                                'default'     => '#e3e3e3',
                                //'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title'
                            ),
                            'spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing', 'text_doma' ),
                                'default'     => array('desktop' => array('padding-top' => '15', 'padding-right' => '20', 'padding-left' => '20', 'padding-bottom' => '15', 'unit' => 'px')),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors'      => '.sek-accord-item .sek-accord-title',
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Content style', 'text_doma' ),
                        'inputs' => array(
                            'ct_bg_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Backround color', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#f2f2f2',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => array('.sek-accord-item .sek-accord-content'),
                                'html_before' => '<h3>' . __('COLOR AND BACKGROUND') .'</h3>'
                            ),
                            'ct_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#1e261f',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $main_content_selector
                            ),//"#000000",

                            'ct_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $main_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>'
                            ),
                            'ct_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $main_content_selector
                            ),//16,//"14px",
                            'ct_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $main_content_selector
                            ),//24,//"20px",
                            'ct_spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing', 'text_doma' ),
                                'default'     => array('desktop' => array('padding-top' => '15', 'padding-right' => '20', 'padding-left' => '20', 'padding-bottom' => '15', 'unit' => 'px')),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors'      => '.sek-accord-item .sek-accord-content',
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )//inputs
                    )
                )//tabs
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
//add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_accordion_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_accordion_collection_child', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_accordion_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_accordion_collection_child( $rules, $params ) {
    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // VERTICAL ALIGNMENT
    // if ( ! empty( $item_model[ 'v_alignment' ] ) ) {
    //     if ( ! is_array( $item_model[ 'v_alignment' ] ) ) {
    //         sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
    //     }
    //     $v_alignment_value = is_array( $item_model[ 'v_alignment' ] ) ? $item_model[ 'v_alignment' ] : array();
    //     $v_alignment_value = wp_parse_args( $v_alignment_value, array(
    //         'desktop' => 'center',
    //         'tablet' => '',
    //         'mobile' => ''
    //     ));
    //     $mapped_values = array();
    //     foreach ( $v_alignment_value as $device => $align_val ) {
    //         switch ( $align_val ) {
    //             case 'top' :
    //                 $mapped_values[$device] = "flex-start";
    //             break;
    //             case 'center' :
    //                 $mapped_values[$device] = "center";
    //             break;
    //             case 'bottom' :
    //                 $mapped_values[$device] = "flex-end";
    //             break;
    //         }
    //     }
    //     $rules = sek_set_mq_css_rules( array(
    //         'value' => $mapped_values,
    //         'css_property' => 'align-items',
    //         'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] )
    //     ), $rules );
    // }//Vertical alignment


    return $rules;
}




// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_accordion_module', '\Nimble\sek_add_css_rules_for_czr_accordion_module', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_accordion_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $defaults = sek_get_default_module_model( 'czr_accordion_module');
    $accord_defaults = $defaults['accord_opts'];

    $accord_opts = $value['accord_opts'];

    //sek_error_log('sek_get_default_module_model() ?', sek_get_default_module_model( 'czr_accordion_module') );

    // TEXT COLOR ( for the plus / minus icon )
    if ( ! empty( $accord_opts[ 'color_css' ] ) && $accord_defaults[ 'color_css' ] != $accord_opts[ 'color_css' ] ) {
        $rules[] = array(
            'selector' => sprintf( '[data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper .sek-accord-item .expander span', $complete_modul_model['id'] ),
            'css_rules' => 'background:'. $accord_opts[ 'color_css' ] .';',
            'mq' =>null
        );
    }
    // ACTIVE / HOVER TEXT COLOR ( for the plus / minus icon )
    if ( ! empty( $accord_opts[ 'color_active_css' ] ) && $accord_defaults[ 'color_active_css' ] != $accord_opts[ 'color_active_css' ] ) {
        $rules[] = array(
            'selector' => sprintf( '[data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper [data-sek-expanded="true"] .sek-accord-title .expander span, [data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper .sek-accord-item .sek-accord-title:hover .expander span', $complete_modul_model['id'] ),
            'css_rules' => sprintf('background:%s;', $accord_opts[ 'color_active_css' ] ),
            'mq' =>null
        );
    }

    return $rules;
}


?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SHORTCODE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_shortcode_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_shortcode_module',
        'name' => __('Shortcode', 'text_doma'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'text_content' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => true,
                        'includedBtns' => 'basic_btns_with_lists',
                    ),
                    'title'             => __( 'Write the shortcode(s) in the text editor', 'text_doma' ),
                    'default'           => '',
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup'    => '.sek-shortcode-content',
                    'notice_before' => __('A shortcode is a WordPress-specific code that lets you display predefined items. For example a trivial shortcode for a gallery looks like this [gallery].') . '<br/><br/>',
                    'notice_after' => __('You may use some html tags in the "text" tab of the editor.', 'text_domain_to_be_replaced')
                ),
                'refresh_button' => array(
                    'input_type'  => 'refresh_preview_button',
                    'title'       => __( '' , 'text_doma' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                ),
                // flex-box should be enabled by user and not active by default.
                // It's been implemented primarily to ease centering ( see https://github.com/presscustomizr/nimble-builder/issues/565 )
                // When enabled, it can create layout issues like : https://github.com/presscustomizr/nimble-builder/issues/576
                'use_flex' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a flex-box wrapper', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('Flex-box is a CSS standard used to specify the layout of HTML pages. Using flex-box can make it easier to center the content of shortcodes.', 'text_doma')
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Horizontal alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_flex_alignment',
                    'css_selectors'      => '.sek-module-inner > .sek-shortcode-content',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_before' => '<hr/><h3>' . __('ALIGNMENT') .'</h3>'
                )
            )
        ),
        'render_tmpl_path' => "shortcode_module_tmpl.php",
    );
}
?><?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 *  Sek Dyn CSS Builder: class responsible for building Stylesheet from a sek model
 */
class Sek_Dyn_CSS_Builder {

    /*min widths, considering CSS min widths BP:
    $grid-breakpoints: (
        xs: 0,
        sm: 576px,
        md: 768px,
        lg: 992px,
        xl: 1200px
    )

    we could have a constant array since php 5.6
    */
    public static $breakpoints = [
        'xs' => 0,
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
        'xl' => 1200
    ];

    const COLS_MOBILE_BREAKPOINT  = 'md';

    private $collection;//the collection of css rules
    private $sek_model;
    // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
    private $is_global_stylesheet;
    private $parent_level_model = array();

    public function __construct( $sek_model = array(), $is_global_stylesheet = false ) {
        $this->sek_model  = $sek_model;
        $this->is_global_stylesheet = $is_global_stylesheet;
        // set the css rules for columns
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        // filter fired in sek_css_rules_sniffer_walker()
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

        //sek_error_log('FIRING THE CSS BUILDER');

        $this->sek_css_rules_sniffer_walker();
    }












    // Fired in the constructor
    // Walk the level tree and build rules when needed
    // The rules are filtered when some conditions are met.
    // This allows us to schedule the css rules addition remotely :
    // - from the module registration php file
    // - from the generic input types ( @see sek_add_css_rules_for_generic_css_input_types() )
    public function sek_css_rules_sniffer_walker( $level = null, $parent_level = array() ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        // The parent level is set when the function is invoked recursively, from a level where we actually have a 'level' property
        if ( ! empty( $parent_level ) ) {
            $this->parent_level_model = $parent_level;
        }

        foreach ( $level as $key => $entry ) {
            $rules = array();

            // INPUT CSS RULES <= used in front modules only
            // When we are inside the associative arrays of
            // - the module 'value'
            // - or the level 'options' entries <= NOT ANYMORE
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( is_string( $key ) && 1 < strlen( $key ) ) {
                // we need to have a level model set
                if ( !empty( $parent_level ) && is_array( $parent_level ) && !empty( $parent_level['module_type'] ) ) {
                     // If the current level is a module, check if the module has generic css ( *_css suffixed ) selectors specified on registration
                    // $module_level_css_selectors = null;
                    // $registered_input_list = null;
                    $module_level_css_selectors = sek_get_registered_module_type_property( $parent_level['module_type'], 'css_selectors' );

                    $registered_input_list = sek_get_registered_module_input_list( $parent_level['module_type'] );
                    if ( 'value' === $key && is_array( $entry ) ) {
                          $is_father = sek_get_registered_module_type_property( $parent_level['module_type'], 'is_father' );
                          $father_mod_type = $parent_level['module_type'];
                          // If the module has children ( the module is_father ), let's loop on each option group
                          if ( $is_father ) {
                              $children = sek_get_registered_module_type_property( $father_mod_type, 'children' );
                              // Loop on the children
                              foreach ( $entry as $opt_group_type => $input_candidates ) {
                                  if ( ! is_array( $children ) ) {
                                      sek_error_log( 'Father module ' . $father_mod_type . ' has invalid children');
                                      continue;
                                  }
                                  if ( empty( $children[$opt_group_type] ) ) {
                                      sek_error_log( 'Father module ' . $father_mod_type . ' has a invalid child for option group : '. $opt_group_type);
                                      continue;
                                  }
                                  // The module type of the currently looped child
                                  $child_mod_type = $children[ $opt_group_type ];

                                  // If the child module has no css_selectors set, we fallback on the father css_selector
                                  $child_css_selector = sek_get_registered_module_type_property( $child_mod_type, 'css_selectors' );
                                  $child_css_selector = empty( $child_css_selector ) ? $module_level_css_selectors : $child_css_selector;

                                  // Is is a multi-item module ?
                                  $is_multi_items_module = true === sek_get_registered_module_type_property( $child_mod_type, 'is_crud' );

                                  if ( $is_multi_items_module ) {
                                      foreach ( $input_candidates as $item_input_list ) {
                                          $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                              'input_list' => $item_input_list,
                                              'registered_input_list' => $registered_input_list[ $opt_group_type ],// <= the full list of input for the module
                                              'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                              'module_css_selector' => $child_css_selector, //a default set of css_se
                                              'is_multi_items' => true
                                          ) );

                                          $rules = apply_filters( "sek_add_css_rules_for_single_item_in_module_type___{$child_mod_type}", $rules, array(
                                              'input_list' => wp_parse_args( $item_input_list, sek_get_default_module_model( $child_mod_type ) ),
                                              'parent_module_type' => $child_mod_type,// 'registered_input_list' => $registered_input_list[ $opt_group_type ],// <= the full list of input for the module
                                              'parent_module_id' => $parent_level['id'],// <= the parent module level id, used to increase the CSS specificity
                                              'module_css_selector' => $child_css_selector //a default set of css_se
                                          ) );
                                      }
                                  } else {
                                      $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                          'input_list' => $input_candidates,
                                          'registered_input_list' => $registered_input_list[ $opt_group_type ],// <= the full list of input for the module
                                          'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                          'module_css_selector' => $child_css_selector //a default set of css_selectors might have been specified on module registration
                                      ));
                                  }
                              }//foreach
                          } //if ( $is_father )
                          else {
                              // Is is a multi-item module ?
                              $is_multi_items_module = true === sek_get_registered_module_type_property( $father_mod_type, 'is_crud' );

                              if ( $is_multi_items_module ) {
                                  foreach ( $entry as $item_input_list ) {
                                      $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                          'input_list' => $item_input_list,
                                          'registered_input_list' => $registered_input_list,// <= the full list of input for the module
                                          'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                          'module_css_selector' => $module_level_css_selectors, //a default set of css_se
                                          'is_multi_items' => true
                                      ) );

                                      $rules = apply_filters( "sek_add_css_rules_for_multi_item_module_type___{$father_mod_type}", $rules, array(
                                          'input_list' => wp_parse_args( $item_input_list, sek_get_default_module_model( $father_mod_type ) ),
                                          'parent_module_type' => $father_mod_type,// <= the full list of input for the module
                                          'parent_module_id' => $parent_level['id'],// <= the parent module level id, used to increase the CSS specificity
                                          'module_css_selector' => $module_level_css_selectors, //a default set of css_se
                                      ) );
                                  }
                              } else {
                                  $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                      'input_list' => $entry,
                                      'registered_input_list' => $registered_input_list,// <= the full list of input for the module
                                      'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                      'module_css_selector' => $module_level_css_selectors //a default set of css_selectors might have been specified on module registration
                                  ));
                              }
                          }//if is_father
                    }//if
                }//if
            }//if


            // INPUT TEXT LEVEL CSS RULES
            // @added in sept 2019 for https://github.com/presscustomizr/nimble-builder/issues/499
            // When we are inside the associative arrays of the level 'options'
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( is_string( $key ) && 1 < strlen( $key ) && 'options' === $key ) {
                // we need to have a level model set
                if ( !empty( $parent_level ) && is_array( $parent_level ) ) {
                    if ( is_array( $entry ) ) {

                        // Level options are structured as an associative array of option groups
                        // $entry = array(
                        //    'text' => array(
                        //        font_size_css => ...
                        //        color_css => ...
                        //    ),
                        //    'bg' => array(),
                        //    ...
                        // )
                        foreach ( $entry as $opt_group_type => $input_candidates ) {
                            if ( 'level_text' !== $opt_group_type )
                              continue;

                            $level_text_registered_input_list = sek_get_registered_module_input_list( 'sek_level_text_module' );
                            $level_text_css_selectors = sek_get_registered_module_type_property( 'sek_level_text_module', 'css_selectors' );

                            $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                'input_list' => $input_candidates,
                                'registered_input_list' => $level_text_registered_input_list,// <= the full list of input for the module
                                'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                'module_css_selector' => $level_text_css_selectors //a default set of css_selectors might have been specified on module registration
                            ));
                        }
                    }//if
                }//if
            }//if


            // LEVEL CSS RULES
            if ( is_array( $entry ) ) {
                // Populate rules for sections / columns / modules
                // Location level are excluded
                if ( !empty( $entry[ 'level' ] ) && 'location' != $entry[ 'level' ] ) {
                    $level_type = $entry[ 'level' ];
                    $rules = apply_filters( "sek_add_css_rules_for__{$level_type}__options", $rules, $entry );
                    // build rules for level options => section / column / module
                    $rules = apply_filters( 'sek_add_css_rules_for_level_options', $rules, $entry );
                }

                // populate rules for modules values
                if ( !empty( $entry[ 'level' ] ) && 'module' === $entry['level'] ) {
                    if ( ! empty( $entry['module_type'] ) ) {
                        $module_type = $entry['module_type'];
                        // build rules for modules
                        // applying sek_normalize_module_value_with_defaults() allows us to access all the value properties of the module without needing to check their existence
                        $rules = apply_filters( "sek_add_css_rules_for_module_type___{$module_type}", $rules, sek_normalize_module_value_with_defaults( $entry ) );
                    }
                }
            } // if ( is_array( $entry ) ) {


            // POPULATE THE CSS RULES COLLECTION
            if ( !empty( $rules ) ) {
                //@TODO: MAKE SURE RULE ARE NORMALIZED
                foreach( $rules as $rule ) {
                    if ( ! is_array( $rule ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                        continue;
                    }
                    if ( empty( $rule['selector']) ) {
                        sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
                        continue;
                    }
                    $this->sek_populate(
                        $rule[ 'selector' ],
                        $rule[ 'css_rules' ],
                        $rule[ 'mq' ]
                    );
                }//foreach
            }

            // keep walking if the current $entry is an array
            // make sure that the parent_level_model is set right before jumping down to the next level
            if ( is_array( $entry ) ) {
                // Can we set a parent level ?
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $parent_level = $entry;
                }
                // Let's go recursive
                $this->sek_css_rules_sniffer_walker( $entry, $parent_level );
            }

            // Reset the parent level model because it might have been modified after walking the sublevels
            if ( ! empty( $parent_level ) ) {
                $this->parent_level_model = $parent_level;
            }

        }//foreach
    }//sek_css_rules_sniffer_walker()




    // @param $rules // <= the in-progress global array of css rules to be populated
    // @param $params= array()
    // @return array of css rules*
    // The input ids prefixed with '_css' are eligible for automaric CSS rules generation.
    // @see add_filter( "sek_add_css_rules_for_input_id", '\Nimble\sek_add_css_rules_for_css_sniffed_input_id', 10, 1 );
    function sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, $params ) {
        // normalize params
        $default_params = array(
            'input_list' => array(),
            'registered_input_list' => array(),// <= the full list of input for the module
            'parent_module_level' => array(),// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
            'module_css_selector' => '',//a default set of css_selectors might have been specified on module registration
            'is_multi_items' => false
        );
        $params = wp_parse_args( $params, $default_params );

        // FOR MULTI-ITEM MODULES=> add the item-id
        // a multi-item module has a unique id for each item
        // An item looks like :
        // Array
        // (
        //     [id] => 34913f6eef98
        //     [icon] => fab fa-accusoft
        //     [color_css] => #dd9933
        // )
        $item_id = null;
        if ( $params['is_multi_items'] ) {
            if ( !is_array( $params['input_list'] ) || !isset($params['input_list']['id']) ) {
                sek_error_log( __FUNCTION__ . ' => Error => each item of a multi-item module must have an id', $params );
            } else {
                $item_id = $params['input_list']['id'];
            }
        }

        foreach( $params['input_list'] as $input_id_candidate => $_input_val ) {
              if ( false !== strpos( $input_id_candidate, '_css') ) {
                  $rules = apply_filters( 'sek_add_css_rules_for_input_id', $rules, array(
                      'css_val' => $_input_val,//string or array(), //<= the css property value
                      'input_id' => $input_id_candidate,//string// <= the unique input_id as it as been declared on module registration
                      'registered_input_list' => $params['registered_input_list'],// <= the full list of input for the module
                      'parent_module_level' => $params['parent_module_level'],// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                      'module_css_selector' => $params['module_css_selector'],// <= a default set of css_selectors might have been specified on module registration
                      'is_multi_items' => $params['is_multi_items'],// <= for multi-item modules, the input selectors will be made specific for each item-id. In module templates, we'll use data-sek-item-id="%5$s"
                      // implemented to allow CSS rules to be generated on a per-item basis
                      // for https://github.com/presscustomizr/nimble-builder/issues/78
                      'item_id' => $item_id // <= a multi-item module has a unique id for each item
                  ));
              }
        }
        return $rules;
    }











    // @return void()
    // populates the css rules ::collection property, organized by media queries
    public function sek_populate( $selector, $css_rules, $mq = '' ) {
        if ( ! is_string( $selector ) )
            return;
        if ( ! is_string( $css_rules ) )
            return;

        // Assign a default media device
        //TODO: allowed media query?
        $mq_device = 'all_devices';

        // If a media query is requested, build it
        if ( !empty( $mq ) ) {
            if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
            } else {
                $mq_device = $mq;
            }
        }

        // if the media query for this device is not yet added, add it
        if ( !isset( $this->collection[ $mq_device ] ) ) {
            $this->collection[ $mq_device ] = array();
        }

        if ( !isset( $this->collection[ $mq_device ][ $selector ] ) ) {
            $this->collection[ $mq_device ][ $selector ] = array();
        }

        $this->collection[ $mq_device ][ $selector ][] = $css_rules;
    }//sek_populate



    // @return string
    public static function sek_maybe_wrap_in_media_query( $css,  $mq_device = 'all_devices' ) {
        if ( 'all_devices' === $mq_device ) {
            return $css;
        }
        if ( false === strpos($mq_device, '(') || false === strpos($mq_device, ')') ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing parenthesis in the media queries', $mq_device );
            return $css;
        }
        return sprintf( '@media%1$s{%2$s}', $mq_device, $css);
    }


    // sorts the media queries from all_devices to the smallest width
    // This doesn't make the difference between max-width and min-width
    // @return integer
    public static function user_defined_array_key_sort_fn($a, $b) {
        if ( 'all_devices' === $a ) {
            return -1;
        }
        if ( 'all_devices' === $b ) {
            return 1;
        }
        $a_int = (int)preg_replace('/[^0-9]/', '', $a) * 1;
        $b_int = (int)preg_replace('/[^0-9]/', '', $b) * 1;

        return $b_int - $a_int;
    }

    //@returns a stringified stylesheet, ready to be printed on the page or in a file
    public function get_stylesheet() {
        $css = '';
        $collection = apply_filters( 'nimble_css_rules_collection_before_printing_stylesheet', $this->collection );
        if ( is_array( $collection ) && !empty( $collection ) ) {
            // Sort the collection by media queries
            uksort( $collection, array( get_called_class(), 'user_defined_array_key_sort_fn' ) );

            // process
            foreach ( $collection as $mq_device => $selectors ) {
                $_css = '';
                foreach ( $selectors as $selector => $css_rules ) {
                    $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                    $_css .=  $selector . '{' . $css_rules . '}';
                    $_css =  str_replace(';;', ';', $_css);//@fixes https://github.com/presscustomizr/nimble-builder/issues/137
                }
                $_css = self::sek_maybe_wrap_in_media_query( $_css, $mq_device );
                $css .= $_css;
            }
        }
        return apply_filters( 'nimble_get_dynamic_stylesheet', $css, $this->is_global_stylesheet );
    }







    // Helper
    // @return css string including media queries
    // @used for example when generating the rules for used defined section widths locally and globally
    public static function sek_generate_css_stylesheet_for_a_set_of_rules( $rules ) {
        $rules_collection = array();
        $css = '';

        if ( empty( $rules ) || ! is_array( $rules ) )
          return $css;

        // POPULATE THE CSS RULES COLLECTION
        foreach( $rules as $rule ) {
            if ( ! is_array( $rule ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                continue;
            }
            if ( empty($rule['selector']) || ! is_string( $rule['selector'] ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
                continue;
            }

            $selector = $rule[ 'selector' ];
            $css_rules = $rule[ 'css_rules' ];
            $mq = $rule[ 'mq' ];

            if ( ! is_string( $css_rules ) )
              continue;

            // Assign a default media device
            //TODO: allowed media query?
            $mq_device = 'all_devices';

            // If a media query is requested, build it
            if ( !empty( $mq ) ) {
                if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                    error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
                } else {
                    $mq_device = $mq;
                }
            }

            // if the media query for this device is not yet added, add it
            if ( !isset( $rules_collection[ $mq_device ] ) ) {
                $rules_collection[ $mq_device ] = array();
            }

            if ( !isset( $rules_collection[ $mq_device ][ $selector ] ) ) {
                $rules_collection[ $mq_device ][ $selector ] = array();
            }

            $rules_collection[ $mq_device ][ $selector ][] = $css_rules;
        }//foreach

        // GENERATE CSS
        if ( is_array( $rules_collection ) && !empty( $rules_collection ) ) {
            // Sort the collection by media queries
            // get_called_class() is supported by php >= 5.3.0. Nimble needs 5.4
            // @see https://developer.wordpress.org/reference/functions/add_action/
            uksort( $rules_collection, array( get_called_class(), 'user_defined_array_key_sort_fn' ) );

            // process
            foreach ( $rules_collection as $mq_device => $selectors ) {
                $_css = '';
                foreach ( $selectors as $selector => $css_rules ) {
                    $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                    $_css .=  $selector . '{' . $css_rules . '}';
                    $_css =  str_replace(';;', ';', $_css);//@fixes https://github.com/presscustomizr/nimble-builder/issues/137
                }
                $_css = self::sek_maybe_wrap_in_media_query( $_css, $mq_device );
                $css .= $_css;
            }
        }

        return $css;
    }//sek_generate_css_stylesheet_for_a_set_of_rules()









    // hook : sek_add_css_rules_for_level_options
    // fired this class constructor
    public function sek_add_rules_for_column_width( $rules, $column ) {
        if ( ! is_array( $column ) )
          return $rules;

        if ( empty( $column['level'] ) || 'column' !== $column['level'] || empty( $column['id'] ) )
          return $rules;

        $width = null;
        // First try to find a width value in options, then look in the previous width property for backward compatibility
        // After implementing https://github.com/presscustomizr/nimble-builder/issues/279
        $column_options = isset( $column['options'] ) ? $column['options'] : array();
        //sek_error_log( 'COLUMN MODEL WHEN ADDING RULES ?', $column_options );

        if ( !empty( $column_options['width'] ) && !empty( $column_options['width']['custom-width'] ) ) {
            $width_candidate = (float)$column_options['width']['custom-width'];
            if ( $width_candidate < 0 || $width_candidate > 100 ) {
                sek_error_log( __FUNCTION__ . ' => invalid width value for column id : ' . $column['id'] );
            } else {
                $width = $width_candidate;
            }
        } else {
            // Backward compat since June 2019
            // After implementing https://github.com/presscustomizr/nimble-builder/issues/279
            $width = empty( $column[ 'width' ] ) || !is_numeric( $column[ 'width' ] ) ? '' : $column['width'];
        }

        // width
        if ( empty( $width ) )
          return $rules;

        // define a default breakpoint : 768
        $breakpoint = self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ];

        // Does the parent section have a custom breakpoint set ?
        $parent_section = sek_get_parent_level_model( $column['id'] );
        if ( 'no_match' === $parent_section ) {
            sek_error_log( __FUNCTION__ . ' => $parent_section not found for column id : ' . $column['id'] );
            return $rules;
        }
        $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( array( 'section_model' => $parent_section, 'for_responsive_columns' => true ) ) );
        if ( $section_custom_breakpoint >= 1 ) {
            $breakpoint = $section_custom_breakpoint;
        } else {
            // Is there a global custom breakpoint set ?
            $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
            if ( $global_custom_breakpoint >= 1 ) {
                $breakpoint = $global_custom_breakpoint;
            }
        }

        // Note : the css selector must be specific enough to override the possible parent section ( or global ) custom breakpoint one.
        // @see sek_add_css_rules_for_level_breakpoint()
        $rules[] = array(
            'selector'      => sprintf( '[data-sek-id="%1$s"] .sek-sektion-inner > .sek-column[data-sek-id="%2$s"]', $parent_section['id'], $column['id'] ),
            'css_rules'     => sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width ),
            'mq'            => "(min-width:{$breakpoint}px)"
        );
        return $rules;
    }
}//end class

?><?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 *  Sek Dyn CSS Handler: class responsible for enqueuing/writing CSS file or enqueuing/printing inline CSS
 */
class Sek_Dyn_CSS_Handler {

    /**
     * CSS files base dir constant
     * Relative dir in the WordPress uploads dir
     *
     * @access public
     */
    const CSS_BASE_DIR = NIMBLE_CSS_FOLDER_NAME;

    /**
     * Functioning mode constant
     *
     * @access public
     */
    const MODE_INLINE  = 'inline';

    /**
     * Functioning mode constant
     *
     * @access public
     */
    const MODE_FILE    = 'file';

    /**
     * CSS resource ID
     *
     * Holds the CSS resource ID
     * Will be used to generate both the file name and the CSS handle when enqueued_or_printed
     * Usually set to skope_id
     *
     * @access private
     * @var string
     */
    private $id;



    /**
     * Requested skope_id
     *
     * Will be used as id
     * Must be provided
     *
     * @access private
     * @var string
     */
    private $skope_id;

    // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
    private $is_global_stylesheet;

    /**
     * the CSS
     *
     * Holds the CSS string: whether to inline print or to write in the proper file
     *
     * @access private
     * @var string
     */
    private $css_string_to_enqueue_or_print = '';


    /**
     * CSS enqueuing / inline printing status
     *
     * Hold the enqueuing status
     *
     * @access private
     * @var bool
     */
    private $enqueued_or_printed = false;



    /**
     * Enqueuing hook
     *
     * Holds the wp action hook name at whose occurrence the CSS will be enqueued_or_printed
     *
     * @access private
     * @var string
     */
    private $hook;


    /**
     * Enqueuing hook priority
     *
     * Holds the wp action hook priority at whose occurrence the CSS will be enqueued
     * (see the $hook param)
     *
     * @var int
     */
    private $priority = 10;



    /**
     * Enqueuing dependencies
     *
     * Holds the style dependencies for this CSS
     *
     * @access private
     * @var array
     */
    private $dep = array();



    /**
     * Functioning mode
     *
     * Holds the object functioning mode: MODE_FILE or MODE_INLINE
     *
     * @access private
     * @var string
     */
    private $mode;

    /**
     * File writing flag
     *
     * Indicates if we need to only write, not print or enqueuing
     * This is used when saving the customizer options + writing the css file.
     *
     * @access private
     * @var bool
     */
    private $customizer_save = false;


    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing if the file doesn't exist
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_write = false;



    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing even if the file exists
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_rewrite = false;


    /**
     * File status
     *
     * Holds the file existence status (true|false)
     *
     * @access private
     * @var bool
     */
    private $file_exists = false;



    /**
     * CSS file base PATH
     *
     * Holds the CSS relative base path
     * This is simply CSS_BASE_DIR in single sites, while its structure takes in account network and site id in multisites
     *
     * @access private
     * @var string
     */
    private $relative_base_path;



    /**
     * CSS file base URI
     *
     * Holds the CSS folder URI
     *
     * @access private
     * @var string
     */
    private $base_uri;


    /**
     * CSS file base URL
     *
     * Holds the CSS folder URL
     *
     * @access private
     * @var string
     */
    private $base_url;



    /**
     * CSS file URL
     *
     * Holds the CSS file URL
     *
     * @access private
     * @var string
     */
    private $url;




    /**
     * CSS file URI
     *
     * Holds the CSS file URI
     *
     * @access private
     * @var string
     */
    private $uri;

    private $builder;//will hold the Sek_Dyn_CSS_Builder instance

    public $sek_model = 'no_set';


    /**
     * Sek Dyn CSS Handler constructor.
     *
     * Initializing the object.
     *
     * @access public
     * @param array $args Optional.
     *
     */
    public function __construct( $args = array() ) {

        $defaults = array(
            'id'                              => 'sek-'.rand(),
            'skope_id'                        => '',
            // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
            'is_global_stylesheet'            => false,
            'mode'                            => self::MODE_FILE,
            'css_string_to_enqueue_or_print'  => $this->css_string_to_enqueue_or_print,
            'dep'                             => $this->dep,
            'hook'                            => '',
            'priority'                        => $this->priority,
            'customizer_save'                 => false,//<= used when saving the customizer settins => we want to write the css file on Nimble_Customizer_Setting::update()
            'force_write'                     => $this->force_write,
            'force_rewrite'                   => $this->force_rewrite
        );

        $args = wp_parse_args( $args, $defaults );

        //normalize some parameters
        $args[ 'dep' ]          = is_array( $args[ 'dep' ] ) ? $args[ 'dep' ]  : array();
        $args[ 'priority']      = is_numeric( $args[ 'priority' ] ) ? $args[ 'priority' ] : $this->priority;

        //turn $args into object properties
        foreach ( $args as $key => $value ) {
            if ( property_exists( $this, $key ) && array_key_exists( $key, $defaults) ) {
                    $this->$key = $value;
            }
        }

        if ( empty( $this->skope_id ) ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ .' => __construct => skope_id not provided' );
            return;
        }

        //build no parameterized properties
        $this->_sek_dyn_css_set_properties();

        // Possible scenarios :
        // 1) customizing :
        //    the css is always printed inline. If there's already an existing css file for this skope_id, it's not enqueued.
        // 2) saving in the customizer :
        //    the css file is written in a "force_rewrite" mode, meaning that any existing css file gets re-written.
        //    There's no enqueing scheduled, 'customizer_save' mode.
        // 3) front, user logged in + 'customize' capabilities :
        //    the css file is re-written on each page load + enqueued. If writing a css file is not possible, we fallback on inline printing.
        // 4) front, user not logged in :
        //    the normal behaviour is that the css file is enqueued.
        //    It should have been written when saving in the customizer. If no file available, we try to write it. If writing a css file is not possible, we fallback on inline printing.
        if ( is_customize_preview() || ! $this->_sek_dyn_css_file_exists() || $this->force_rewrite || $this->customizer_save ) {
            $this->sek_model = sek_get_skoped_seks( $this->skope_id );

            //  on front, when no stylesheet is available, the fallback hook must be set to wp_head, because the hook property might be empty
            // fixes https://github.com/presscustomizr/nimble-builder/issues/328
            if ( !is_customize_preview() && !$this->_sek_dyn_css_file_exists() ) {
                $this->hook = 'wp_head';
            }

            //build stylesheet
            $this->builder = new Sek_Dyn_CSS_Builder( $this->sek_model, $this->is_global_stylesheet );

            // now that the stylesheet is ready let's cache it
            $this->css_string_to_enqueue_or_print = (string)$this->builder->get_stylesheet();
        }

        // Do we have any rules to print / enqueue ?
        // If yes, print in the dom or enqueue depending on the current context ( customization or front )
        // If not, delete any previouly created stylesheet

        //hook setup for printing or enqueuing
        //bail if "customizer_save" == true, typically when saving the customizer settings @see Nimble_Customizer_Setting::update()
        if ( ! $this->customizer_save ) {
            $this->_schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook();
        } else {
            //sek_error_log( __CLASS__ . '::' . __FUNCTION__ .' ?? => $this->css_string_to_enqueue_or_print => ', $this->css_string_to_enqueue_or_print );
            if ( $this->css_string_to_enqueue_or_print ) {
                $this->sek_dyn_css_maybe_write_css_file();
            } else {
                $this->sek_dyn_css_maybe_delete_file();
            }
        }
    }//__construct





    /*
    * Private methods
    */

    /**
     *
     * Build these instance properties based on the params passed on instantiation
     * called in the constructor
     *
     * @access private
     *
     */
    private function _sek_dyn_css_set_properties() {
        $this->_sek_dyn_css_require_wp_filesystem();

        $this->relative_base_path   = $this->_sek_dyn_css_build_relative_base_path();

        $this->base_uri             = $this->_sek_dyn_css_build_base_uri();
        $this->base_url             = $this->_sek_dyn_css_build_base_url();

        $this->uri                  = $this->_sek_dyn_css_build_uri();
        $this->url                  = $this->_ssl_maybe_fix_url( $this->_sek_dyn_css_build_url() );

        $this->file_exists          = $this->_sek_dyn_css_file_exists();

        if ( self::MODE_FILE == $this->mode ) {
            if ( ! $this->_sek_dyn_css_write_file_is_possible() ) {
                $this->mode = self::MODE_INLINE;
            }
        }
    }


    /**
    * replace http: URL with https: URL
    * @fix https://github.com/presscustomizr/nimble-builder/issues/188
    * @param string $url
    * @return string
    */
    private function _ssl_maybe_fix_url($url) {
      // only fix if source URL starts with http://
      if ( is_ssl() && is_string($url) && stripos($url, 'http://') === 0 ) {
        $url = 'https' . substr($url, 4);
      }

      return $url;
    }


    /**
     *
     * Maybe setup hooks
     * called in the constructor
     *
     * @access private
     *
     */
    private function _schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook() {
        if ( $this->hook ) {
            add_action( $this->hook, array( $this, 'sek_dyn_css_enqueue_or_print_and_google_fonts_print' ), $this->priority );
        } else {
            //enqueue or print
            $this->sek_dyn_css_enqueue_or_print_and_google_fonts_print();
        }
    }




    /**
     * Enqueue CSS.
     *
     * Either enqueue the CSS file or add inline style, depending on the object mode property.
     * The inline enqueuing is also the fall-back if anything goes wrong while trying to enqueuing the file.
     *
     * This method can also write the file under some circumstances (see when the object force_write || force_rewrite are enabled)
     *
     * @access public
     * @return void()
     */
    public function sek_dyn_css_enqueue_or_print_and_google_fonts_print() {
        //sek_error_log( __CLASS__ . ' | ' . __FUNCTION__ . ' => ' . $this->id );
        // CSS FILE
        //case enqueue file : front end + user with customize caps not logged in
        if ( self::MODE_FILE == $this->mode ) {
            //in case we need to write the file before enqueuing
            //1) $this->css_string_to_enqueue_or_print must exists
            //2) we might need to force the rewrite even if the file exists or to write it if the file doesn't exist
            if ( $this->css_string_to_enqueue_or_print ) {
                if ( $this->force_rewrite || ( !$this->file_exists && $this->force_write ) ) {
                    $this->file_exists = $this->sek_dyn_css_maybe_write_css_file();
                }
            }

            //if the file exists
            if ( $this->file_exists ) {
                //print the needed html to enqueue a style only if we're in wp_footer or wp_head
                if ( in_array( current_filter(), array( 'wp_footer', 'wp_head' ) ) ) {
                    /*
                    * TODO: make sure all the deps are enqueued
                    */
                    printf( '<link rel="stylesheet" id="sek-dyn-%1$s-css" href="%2$s" type="text/css" media="all" />',
                        $this->id,
                        //this resource version is built upon the file last modification time
                        add_query_arg( array( 'ver' => filemtime($this->uri) ), $this->url )
                    );
                } else {
                    //this resource version is built upon the file last modification time
                    wp_enqueue_style( "sek-dyn-{$this->id}", $this->url, $this->dep, filemtime($this->uri) );
                }

                $this->enqueued_or_printed = true;
            }
        }// if ( self::MODE_FILE )
        // case when sek_inline_dynamic_stylesheets_on_front()
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/612
        else if ( !is_customize_preview() && self::MODE_INLINE == $this->mode ) {
            global $wp_filesystem;
            if ( $wp_filesystem->exists($this->uri) && $wp_filesystem->is_readable($this->uri) ) {
                $file_content = $wp_filesystem->get_contents($this->uri);
                printf( '<style id="sek-dyn-%1$s-css" media="all">%2$s</style>', $this->id, $file_content );
                $this->enqueued_or_printed = true;
            }
        }


        //if $this->mode != 'file' or the file enqueuing didn't go through (fall back)
        //print inline style
        if ( $this->css_string_to_enqueue_or_print && ! $this->enqueued_or_printed ) {
            $dep =  array_pop( $this->dep );

            if ( !$dep || wp_style_is( $dep, 'done' ) || !wp_style_is( $dep, 'done' ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                printf( '<style id="sek-%1$s" type="text/css" media="all">%2$s</style>', $this->id, $this->css_string_to_enqueue_or_print );
            } else {
                //not sure
                wp_add_inline_style( $dep , $this->css_string_to_enqueue_or_print );
            }

            $this->mode     = self::MODE_INLINE;
            $this->enqueued_or_printed = true;
        }
    }


    /*
    * Public 'actions'
    */
    /**
     *
     * Write the CSS to the disk, if we can
     *
     * @access public
     *
     * @return bool TRUE if the CSS file has been written, FALSE otherwise
     */
    public function sek_dyn_css_maybe_write_css_file() {
        global $wp_filesystem;

        $error = false;

        $base_uri = $this->base_uri;

        // Can we create the folder?
        if ( ! $wp_filesystem->is_dir( $base_uri ) ) {
            $error = !wp_mkdir_p( $base_uri );
        }

        if ( $error ) {
            return false;
        }

        if ( ! file_exists( $index_path = wp_normalize_path( trailingslashit( $base_uri ) . 'index.php' ) ) ) {
            // predefined mode settings for WP files
            $wp_filesystem->put_contents( $index_path, "<?php\n// Silence is golden.\n", FS_CHMOD_FILE );
        }


        if ( ! wp_is_writable( $base_uri ) ) {
            return false;
        }

        //actual write try and update the file_exists status
        $this->file_exists = $wp_filesystem->put_contents(
            $this->uri,
            $this->css_string_to_enqueue_or_print,
            // predefined mode settings for WP files
            FS_CHMOD_FILE
        );

        //return whether or not the writing succeeded
        return $this->file_exists;
    }



    /**
     *
     * Remove the CSS file from the disk, if it exists
     *
     * @access public
     *
     * @return bool TRUE if the CSS file has been deleted (or didn't exist already), FALSE otherwise
     */
    public function sek_dyn_css_maybe_delete_file() {
        if ( $this->file_exists ) {
            global $wp_filesystem;
            $this->file_exists != $wp_filesystem->delete( $this->uri );
            return !$this->file_exists;
        }
        return !$this->file_exists;
    }




    /*
    * Private helpers
    */

    /**
     *
     * Retrieve the actual CSS file existence on the file system
     *
     * @access private
     *
     * @return bool TRUE if the CSS file exists, FALSE otherwise
     */
    private function _sek_dyn_css_file_exists() {
        global $wp_filesystem;
        //sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' SOOO ? => ' . $this->uri . $wp_filesystem->exists( $this->uri ), empty( $file_content ) );
        if ( $wp_filesystem->exists( $this->uri ) ) {
            $file_content = $wp_filesystem->get_contents( $this->uri );
            return $wp_filesystem->is_readable( $this->uri ) && !empty( $file_content );
        } else {
            return false;
        }

    }



    /**
     *
     * Build normalized URI of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URI
     */
    private function _sek_dyn_css_build_uri() {
        if ( ! isset( $this->base_uri ) ) {
            $this->_sek_dyn_css_build_base_uri();
        }
        return wp_normalize_path( trailingslashit( $this->base_uri ) . "{$this->id}.css" );
    }




    /**
     *
     * Build the URL of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URL
     */
    private function _sek_dyn_css_build_url() {
        if ( ! isset( $this->base_url ) ) {
            $this->_sek_dyn_css_build_base_url();
        }
        return trailingslashit( $this->base_url ) . "{$this->id}.css";
    }




    /**
     *
     * Build the URI of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URI
     */
    private function _sek_dyn_css_build_base_uri() {
        //since 4.5.0
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $relative_base_path );
    }




    /**
     *
     * Build the URL of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URL
     */
    private function _sek_dyn_css_build_base_url() {
        //since 4.5.0
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return set_url_scheme( trailingslashit( $upload_dir['baseurl'] ) . $relative_base_path );
    }




    /**
     *
     * Retrieve the relative path (to the 'uploads' dir ) of the CSS base directory
     *
     * @access private
     *
     * @return string The relative path (to the 'uploads' dir) of the CSS base directory
     */
    private function _sek_dyn_css_build_relative_base_path() {
        $css_base_dir     = self::CSS_BASE_DIR;

        if ( is_multisite() ) {
            $site        = get_site();
            $network_id  = $site->site_id;
            $site_id     = $site->blog_id;
            $css_dir     = trailingslashit( $css_base_dir ) . trailingslashit( $network_id ) . $site_id;
        }

        return $css_base_dir;
    }




    /**
     *
     * Checks whether or not we can write to the disk
     *
     * @access private
     *
     * @return bool Whether or not we have filesystem credentials
     */
    //TODO: try to extend this to other methods e.g. FTP when FTP credentials are already defined
    private function _sek_dyn_css_write_file_is_possible() {
        $upload_dir      = wp_get_upload_dir();
        //Note: if the 'uploads' dir has not been created, this check will not pass, hence no file will never be created
        //unless something else creates the 'uploads' dir
        if ( 'direct' === get_filesystem_method( array(), $upload_dir['basedir'] ) ) {
            $creds = request_filesystem_credentials( '', '', false, false, array() );

            /* initialize the API */
            if ( ! WP_Filesystem($creds) ) {
                /* any problems and we exit */
                return false;
            }
            return true;
        }

        return false;
    }



    /**
     *
     * Simple helper to require the WordPress filesystem relevant file
     *
     * @access private
     */
    private function _sek_dyn_css_require_wp_filesystem() {
        global $wp_filesystem;

        // Initialize the WordPress filesystem.
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }
    }

}

?><?php
// filter declared in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker()
// $rules = apply_filters( "sek_add_css_rules_for_input_id", $rules, $key, $entry, $this->parent_level );
// the rules are filtered if ( false !== strpos( $input_id_candidate, '_css') )
// Example of input id candidate filtered : 'h_alignment_css'
// @see function sek_loop_on_input_candidates_and_maybe_generate_css_rules( $params ) {}
add_filter( "sek_add_css_rules_for_input_id", '\Nimble\sek_add_css_rules_for_css_sniffed_input_id', 10, 2 );

//@param $params = array()
//@param $rules <= the in-progress global array of css rules to be populated
function sek_add_css_rules_for_css_sniffed_input_id( $rules, $params ) {
    // normalize params
    $default_params = array(
        'css_val' => '',//string or array(), //<= the css property value
        'input_id' => '',//string// <= the unique input_id as it as been declared on module registration
        'registered_input_list' => array(),// <= the full list of input for the module
        'parent_module_level' => array(),// <= the parent level. name is misleading because can be module but also other levels array( 'location', 'section', 'column', 'module' )
        'module_css_selector' => '',//<= a default set of css_selectors might have been specified on module registration
        'is_multi_items' => false,// <= for multi-item modules, the input selectors will be made specific for each item-id. In module templates, we'll use data-sek-item-id="%5$s"
        'item_id' => '' // <= a multi-item module has a unique id for each item
    );

    $params = wp_parse_args( $params, $default_params );

    // map variables
    $value = $params['css_val'];
    $input_id = $params['input_id'];
    $registered_input_list = $params['registered_input_list'];
    $parent_level = $params['parent_module_level'];
    $module_level_css_selectors = $params['module_css_selector'];
    $is_multi_items = $params['is_multi_items'];
    $item_id = $params['item_id'];

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
        sek_error_log( __FUNCTION__ . ' => missing css_identifier for parent level', $parent_level );
        sek_error_log('$registered_input_list', $registered_input_list );
        return $rules;
    }

    $selector = sprintf( '[data-sek-id="%1$s"]', $parent_level['id'] );
    // for multi-items module, each item has a unique id allowing us to identify it
    // implemented to allow CSS rules to be generated on a per-item basis
    // for https://github.com/presscustomizr/nimble-builder/issues/78
    if ( $is_multi_items ) {
        $selector = sprintf( '[data-sek-id="%1$s"] [data-sek-item-id="%2$s"]', $parent_level['id'], $item_id );
    }
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
            if ( $is_multi_items ) {
                $selector = sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"]', $parent_level['id'], $item_id );
            }
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
                      'level_id' => $parent_level['id']
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
                        $h_align_value = sprintf('justify-content:flex-start%1$s;-webkit-box-pack:start%1$s;-ms-flex-pack:start%1$s;', $important ? '!important' : '' );
                    break;
                    case 'center' :
                        $h_align_value = sprintf('justify-content:center%1$s;-webkit-box-pack:center%1$s;-ms-flex-pack:center%1$s;', $important ? '!important' : '' );
                    break;
                    case 'right' :
                        $h_align_value = sprintf('justify-content:flex-end%1$s;-webkit-box-pack:end%1$s;-ms-flex-pack:end%1$s;', $important ? '!important' : '' );
                    break;
                    default :
                        $h_align_value = sprintf('justify-content:center%1$s;-webkit-box-pack:center%1$s;-ms-flex-pack:center%1$s;', $important ? '!important' : '' );
                    break;
                }
                $flex_ready_value[$device] = $h_align_value;
            }
            $flex_ready_value = wp_parse_args( $flex_ready_value, array(
                'desktop' => '',
                'tablet' => '',
                'mobile' => ''
            ));

            $rules = sek_set_mq_css_rules_supporting_vendor_prefixes( array(
                'css_rules_by_device' => $flex_ready_value,
                'selector' => $selector,
                'level_id' => $parent_level['id']
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
                      'level_id' => $parent_level['id'],
                  ), $rules );
            }
        break;

        // -webkit-box-align:end;
        // -ms-flex-align:end;
        // align-items:flex-end;
        case 'v_alignment' :
            switch ( $value ) {
                case 'top' :
                    $v_align_value = "flex-start";
                    $v_vendor_value = "start";
                break;
                case 'center' :
                    $v_align_value = "center";
                    $v_vendor_value = "center";
                break;
                case 'bottom' :
                    $v_align_value = "flex-end";
                    $v_vendor_value = "end";
                break;
                default :
                    $v_align_value = "center";
                    $v_vendor_value = "center";
                break;
            }
            $properties_to_render['align-items'] = $v_align_value;
            $properties_to_render['-webkit-box-align'] = $v_vendor_value;
            $properties_to_render['-ms-flex-align'] = $v_vendor_value;
        break;
        case 'font_family' :
            $ffamily = sek_extract_css_font_family_from_customizer_option( $value );
            if ( !empty( $ffamily ) ) {
                $properties_to_render['font-family'] = $ffamily;
            }
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
                      'level_id' => $parent_level['id']
                  ), $rules );
            }
        break;
        /* Quote border */
        case 'border_width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( 0 === intval($numeric) || ! empty( $numeric ) ) {
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
                      'level_id' => $parent_level['id']
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
                      'level_id' => $parent_level['id']
                  ), $rules );
                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'margin-bottom',
                      'selector' => $selector,
                      'is_important' => $important,
                      'level_id' => $parent_level['id']
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
}//sek_add_css_rules_for_css_sniffed_input_id






// @return boolean
// Recursive
// Check if a *_flag_important input id is part of the registered input list of the module
// then verify is the provided input_id is part of the list of input that should be set to important => 'important_input_list'
// Example of a *_flag_important input:
// 'quote___flag_important'       => array(
//     'input_type'  => 'nimblecheck',
//     'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
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
?><?php
////////////////////////////////////////////////////////////////
// SEK Front Class
if ( ! class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $local_seks = 'not_cached';// <= used to cache the sektions for the local skope_id
        public $global_seks = 'not_cached';// <= used to cache the sektions for the global skope_id
        public $seks_posts = [];// <= march 2020 : used to cache the current local and global sektion posts
        public $model = array();//<= when rendering, the current level model
        public $parent_model = array();//<= when rendering, the current parent model
        public $default_models = array();// <= will be populated to cache the default models when invoking sek_get_default_module_model
        public $cached_input_lists = array(); // <= will be populated to cache the input_list of each registered module. Useful when we need to get info like css_selector for a particular input type or id.
        public $ajax_action_map = array();
        public $default_locations = [
            'loop_start' => array( 'priority' => 10 ),
            'before_content' => array(),
            'after_content' => array(),
            'loop_end' => array( 'priority' => 10 ),
        ];
        public $registered_locations = [];
        // the model used to register a location
        public $default_registered_location_model = [
          'priority' => 10,
          'is_global_location' => false,
          'is_header_location' => false,
          'is_footer_location' => false
        ];
        // the model used when saving a location in db
        public $default_location_model = [
            'id' => '',
            'level' => 'location',
            'collection' => [],
            'options' => [],
            'ver_ini' => NIMBLE_VERSION
        ];
        public $rendered_levels = [];//<= stores the ids of the level rendered with ::render()

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sek_Nimble_Manager ) ) {
                self::$instance = new Sek_Nimble_Manager( $params );

                // this hook is used to add_action( 'nimble_front_classes_ready', array( $this, 'sek_register_nimble_global_locations') );
                do_action( 'nimble_front_classes_ready', self::$instance );
            }
            return self::$instance;
        }

        // store the local and global options
        public $local_options = '_not_cached_yet_';
        public $global_nimble_options = '_not_cached_yet_';

        public $img_smartload_enabled = 'not_cached';
        public $video_bg_lazyload_enabled = 'not_cached';//<= for https://github.com/presscustomizr/nimble-builder/issues/287

        public $has_local_header_footer = '_not_cached_yet_';//used in sek_maybe_set_local_nimble_header() and sek_maybe_set_local_nimble_footer()
        public $has_global_header_footer = '_not_cached_yet_';//used in sek_maybe_set_local_nimble_header() and sek_maybe_set_local_nimble_footer()

        public $recaptcha_enabled = '_not_cached_yet_';//enabled in the global options
        public $recaptcha_badge_displayed = '_not_cached_yet_';//enabled in the global options

        // option key as saved in db => module_type
        // is used in _1_6_5_sektions_generate_UI_global_options.js and when normalizing the global option in sek_normalize_global_options_with_defaults()
        public static $global_options_map = [
            'global_text' => 'sek_global_text',
            'widths' => 'sek_global_widths',
            'breakpoint' => 'sek_global_breakpoint',
            'global_header_footer' => 'sek_global_header_footer',
            'performances' => 'sek_global_performances',
            'recaptcha' => 'sek_global_recaptcha',
            'global_revisions' => 'sek_global_revisions',
            'global_reset' => 'sek_global_reset',
            'global_imp_exp' => 'sek_global_imp_exp',
            'beta_features' => 'sek_global_beta_features'
        ];
        // option key as saved in db => module_type
        // is used in _1_6_4_sektions_generate_UI_local_skope_options.js and when normalizing the global option in sek_normalize_global_options_with_defaults()
        public static $local_options_map = [
            'template' => 'sek_local_template',
            'local_header_footer' => 'sek_local_header_footer',
            'widths' => 'sek_local_widths',
            'custom_css' => 'sek_local_custom_css',
            'local_performances' => 'sek_local_performances',
            'local_reset' => 'sek_local_reset',
            'import_export' => 'sek_local_imp_exp',
            'local_revisions' => 'sek_local_revisions'
        ];
        // introduced when implementing import/export feature
        // @see https://github.com/presscustomizr/nimble-builder/issues/411
        public $img_import_errors = [];

        // stores the active module collection
        // @see populated in sek_populate_collection_of_contextually_active_modules()
        // list of modules displayed on local + global sektions for a givent page.
        // populated 'wp'@PHP_INT_MAX and used to
        // 1) determine which module should be registered when not customizing or ajaxing. See sek_register_modules_when_not_customizing_and_not_ajaxing()
        // 2) determine which assets ( css / js ) is needed for this context. see ::sek_enqueue_front_assets
        //
        // updated for https://github.com/presscustomizr/nimble-builder/issues/612
        public $contextually_active_modules = 'not_set';

        public static $ui_picker_modules = [
          // UI CONTENT PICKER
          'sek_content_type_switcher_module',
          'sek_module_picker_module'
        ];

        public static $ui_level_modules = [
          // UI LEVEL MODULES
          'sek_mod_option_switcher_module',
          'sek_level_bg_module',
          'sek_level_text_module',
          'sek_level_border_module',
          //'sek_level_section_layout_module',<// deactivated for now. Replaced by sek_level_width_section
          'sek_level_height_module',
          'sek_level_spacing_module',
          'sek_level_width_module',
          'sek_level_width_column',
          'sek_level_width_section',
          'sek_level_anchor_module',
          'sek_level_visibility_module',
          'sek_level_breakpoint_module'
        ];

        public static $ui_local_global_options_modules = [
          // local skope options modules
          'sek_local_template',
          'sek_local_widths',
          'sek_local_custom_css',
          'sek_local_reset',
          'sek_local_performances',
          'sek_local_header_footer',
          'sek_local_revisions',
          'sek_local_imp_exp',

          // global options modules
          'sek_global_text',
          'sek_global_widths',
          'sek_global_breakpoint',
          'sek_global_header_footer',
          'sek_global_performances',
          'sek_global_recaptcha',
          'sek_global_revisions',
          'sek_global_reset',
          'sek_global_imp_exp',
          'sek_global_beta_features'
        ];

        // Is merged with front module when sek_is_header_footer_enabled() === true
        // @see sek_register_modules_when_customizing_or_ajaxing
        // and sek_register_modules_when_not_customizing_and_not_ajaxing
        public static $ui_front_beta_modules = [];

        // introduced for https://github.com/presscustomizr/nimble-builder/issues/456
        public $global_sections_rendered = false;

        // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
        // september 2019
        // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
        // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
        // @see ::render() method
        // otherwise the preview UI can be broken
        public $preview_level_guid = '_preview_level_guid_not_set_';

        // March 2020 : feedback_notif_status is used to store the feedback notif ( used to display a request for review in the customizer )
        // so that we don't fire the heavy requests multiple times in case the function sek_get_feedback_notif_status() is invoked several times
        public $feedback_notif_status = 'not_set';

        // March 2020 : introduction of split stylesheet for some modules
        public $big_module_stylesheet_map = [
            'czr_quote_module' => 'quote-module',
            'czr_icon_module' => 'icon-module',
            'czr_img_slider_module' => 'img-slider-module-with-swiper',
            'czr_accordion_module' => 'accordion-module',
            'czr_menu_module' => 'menu-module',
            'czr_post_grid_module' => 'post-grid-module',
            'czr_simple_form_module' => 'simple-form-module'
        ];

        // March 2020
        public $modules_dependant_of_font_awesome = [ 'czr_button_module', 'czr_icon_module', 'czr_social_icons_module' ];

        // March 2020, for https://github.com/presscustomizr/nimble-builder/issues/629
        public $google_fonts_print_candidates = 'not_set';// will cache the google font candidates to print in ::_setup_hook_for_front_css_printing_or_enqueuing()

        public $css_loader_html = '<div class="sek-css-loader sek-mr-loader"><div></div><div></div><div></div></div>';

        /////////////////////////////////////////////////////////////////
        // <CONSTRUCTOR>
        function __construct( $params = array() ) {
            if ( did_action('nimble_manager_ready') )
              return;
            // INITIALIZE THE REGISTERED LOCATIONS WITH THE DEFAULT LOCATIONS
            $this->registered_locations = $this->default_locations;

            // AJAX
            $this->_schedule_front_ajax_actions();
            $this->_schedule_img_import_ajax_actions();
            if ( defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) && NIMBLE_SAVED_SECTIONS_ENABLED ) {
                $this->_schedule_section_saving_ajax_actions();
            }
            // FRONT ASSETS
            $this->_schedule_front_assets_printing();
            // CUSTOOMIZER PREVIEW ASSETS
            $this->_schedule_preview_assets_printing();
            // RENDERING
            $this->_schedule_front_rendering();
            // RENDERING
            $this->_setup_hook_for_front_css_printing_or_enqueuing();
            // LOADS SIMPLE FORM
            $this->_setup_simple_forms();
            // REGISTER NIMBLE WIDGET ZONES
            add_action( 'widgets_init', array( $this, 'sek_nimble_widgets_init' ) );
            do_action('nimble_manager_ready');
        }//__construct

        // @fired @hook 'widgets_init'
        // Creates 10 widget zones
        public function sek_nimble_widgets_init() {
            // Header/footer, widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
            $defaults = array(
                'name'          => '',
                'id'            => '',
                'description'   => '',
                'class'         => '',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget'  => '</aside>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            );
            for ( $i=1; $i < 11; $i++ ) {
                $args['id'] = NIMBLE_WIDGET_PREFIX . $i;//'nimble-widget-area-'
                $args['name'] = sprintf( __('Nimble widget area #%1$s', 'text_domain_to_replace' ), $i );
                $args['description'] = $args['name'];
                $args = wp_parse_args( $args, $defaults );
                register_sidebar( $args );
            }
        }

        // Invoked @'after_setup_theme'
        static function sek_get_front_module_collection() {
            return apply_filters( 'sek_get_front_module_collection', [
              // FRONT MODULES
              'czr_simple_html_module',

              'czr_tiny_mce_editor_module' => array(
                'czr_tiny_mce_editor_module',
                'czr_tinymce_child',
                'czr_font_child'
              ),

              'czr_image_module' => array(
                'czr_image_module',
                'czr_image_main_settings_child',
                'czr_image_borders_corners_child'
              ),

              //'czr_featured_pages_module',
              'czr_heading_module'  => array(
                'czr_heading_module',
                'czr_heading_child',
                'czr_heading_spacing_child',
                'czr_font_child'
              ),

              'czr_spacer_module',
              'czr_divider_module',

              'czr_icon_module' => array(
                'czr_icon_module',
                'czr_icon_settings_child',
                'czr_icon_spacing_border_child',
              ),


              'czr_map_module',

              'czr_quote_module' => array(
                'czr_quote_module',
                'czr_quote_quote_child',
                'czr_quote_cite_child',
                'czr_quote_design_child',
              ),

              'czr_button_module' => array(
                'czr_button_module',
                'czr_btn_content_child',
                'czr_btn_design_child',
                'czr_font_child'
              ),

              // simple form father + children
              'czr_simple_form_module' => array(
                'czr_simple_form_module',
                'czr_simple_form_fields_child',
                'czr_simple_form_button_child',
                'czr_simple_form_design_child',
                'czr_simple_form_fonts_child',
                'czr_simple_form_submission_child'
              ),

              'czr_post_grid_module' => array(
                'czr_post_grid_module',
                'czr_post_grid_main_child',
                'czr_post_grid_thumb_child',
                'czr_post_grid_metas_child',
                'czr_post_grid_fonts_child'
              ),

              // widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
              'czr_menu_module' => array(
                'czr_menu_module',
                'czr_menu_content_child',
                'czr_menu_mobile_options',
                'czr_font_child'
              ),
              //'czr_menu_design_child',

              'czr_widget_area_module',

              'czr_social_icons_module' => array(
                'czr_social_icons_module',
                'czr_social_icons_settings_child',
                'czr_social_icons_style_child'
              ),

              'czr_img_slider_module' => array(
                'czr_img_slider_module',
                'czr_img_slider_collection_child',
                'czr_img_slider_opts_child'
              ),

              'czr_accordion_module' => array(
                'czr_accordion_module',
                'czr_accordion_collection_child',
                'czr_accordion_opts_child'
              ),

              'czr_shortcode_module',
            ]);
        }

    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Ajax' ) ) :
    class SEK_Front_Ajax extends SEK_Front_Construct {
        // Fired in __construct()
        function _schedule_front_ajax_actions() {
            add_action( 'wp_ajax_sek_get_content', array( $this, 'sek_get_level_content_for_injection' ) );
            //add_action( 'wp_ajax_sek_get_preview_ui_element', array( $this, 'sek_get_ui_content_for_injection' ) );
            // Fetches the preset_sections
            add_action( 'wp_ajax_sek_get_preset_sections', array( $this, 'sek_get_preset_sektions' ) );
            // Fetches the list of revision for a given skope_id
            add_action( 'wp_ajax_sek_get_revision_history', array( $this, 'sek_get_revision_history' ) );
            // Fetches the revision for a given post id
            add_action( 'wp_ajax_sek_get_single_revision', array( $this, 'sek_get_single_revision' ) );
            // Fetches the category collection to generate the options for a select input
            // @see api.czrInputMap.category_picker
            add_action( 'wp_ajax_sek_get_post_categories', array( $this, 'sek_get_post_categories' ) );

            // Fetches the code editor params to generate the options for a textarea input
            // @see api.czrInputMap.code_editor
            add_action( 'wp_ajax_sek_get_code_editor_params', array( $this, 'sek_get_code_editor_params' ) );

            add_action( 'wp_ajax_sek_postpone_feedback', array( $this, 'sek_postpone_feedback_notification' ) );

            // <AJAX TO FETCH INPUT COMPONENTS>
            // this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
            // It allows us to populate the server response with the relevant module html template
            // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
            add_filter( "ac_set_ajax_czr_tmpl___fa_icon_picker_input", array( $this, 'sek_get_fa_icon_list_tmpl' ), 10, 3 );

            // this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
            // It allows us to populate the server response with the relevant module html template
            // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
            add_filter( "ac_set_ajax_czr_tmpl___font_picker_input", array( $this, 'sek_get_font_list_tmpl' ), 10, 3 );
            // </AJAX TO FETCH INPUT COMPONENTS>

            // Returns the customize url for the edit button when using Gutenberg editor
            // implemented for https://github.com/presscustomizr/nimble-builder/issues/449
            // @see assets/admin/js/nimble-gutenberg.js
            add_action( 'wp_ajax_sek_get_customize_url_for_nimble_edit_button', array( $this, 'sek_get_customize_url_for_nimble_edit_button' ) );


            // This is the list of accepted actions
            $this->ajax_action_map = array(
                  'sek-add-section',
                  'sek-remove-section',
                  'sek-duplicate-section',

                  // fired when dropping a module or a preset_section
                  'sek-add-content-in-new-nested-sektion',
                  'sek-add-content-in-new-sektion',

                  // add, duplicate, remove column is a re-rendering of the parent sektion collection
                  'sek-add-column',
                  'sek-remove-column',
                  'sek-duplicate-column',
                  'sek-resize-columns',
                  'sek-refresh-columns-in-sektion',

                  'sek-add-module',
                  'sek-remove-module',
                  'sek-duplicate-module',
                  'sek-refresh-modules-in-column',

                  'sek-refresh-stylesheet',

                  'sek-refresh-level'
            );
        }

        ////////////////////////////////////////////////////////////////
        // GENERIC HELPER FIRED IN ALL AJAX CALLBACKS
        // @param $params = array('check_nonce' => true )
        function sek_do_ajax_pre_checks( $params = array() ) {
            $params = wp_parse_args( $params, array( 'check_nonce' => true ) );
            if ( $params['check_nonce'] ) {
                $action = 'save-customize_' . get_stylesheet();
                if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                     wp_send_json_error( array(
                        'code' => 'invalid_nonce',
                        'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' => check_ajax_referer() failed.' ),
                    ) );
                }
            }

            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }
        }//sek_do_ajax_pre_checks()


        ////////////////////////////////////////////////////////////////
        // IMPORT IMG
        // Fired in __construct()
        function _schedule_img_import_ajax_actions() {
            add_action( 'wp_ajax_sek_import_attachment', array( $this, 'sek_ajax_import_attachment' ) );
        }

        ////////////////////////////////////////////////////////////////
        // SECTION SAVING
        // Fired in __construct()
        function _schedule_section_saving_ajax_actions() {
            // Writes the saved section in a CPT + update the saved section option
            add_action( 'wp_ajax_sek_save_section', array( $this, 'sek_ajax_save_section' ) );
            // Fetches the user_saved sections
            add_action( 'wp_ajax_sek_get_user_saved_sections', array( $this, 'sek_sek_get_user_saved_sections' ) );
        }

        ////////////////////////////////////////////////////////////////
        // PRESET SECTIONS
        // Fired in __construct()
        // hook : 'wp_ajax_sek_get_preset_sektions'
        function sek_get_preset_sektions() {
            $this->sek_do_ajax_pre_checks();
            // May 21st => back to the local data
            // after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
            //$preset_sections = sek_get_preset_sections_api_data();
            $preset_sections = sek_get_preset_section_collection_from_json();
            if ( empty( $preset_sections ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no preset_sections when running sek_get_preset_sections_api_data()' );
            }
            wp_send_json_success( $preset_sections );
        }



        // hook : 'wp_ajax_sek_get_html_for_injection'
        function sek_get_level_content_for_injection( $params ) {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( ! isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }

            // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
            // september 2019
            // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
            // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
            // otherwise the preview UI can be broken
            if ( ! isset( $_POST['preview-level-guid'] ) || empty( $_POST['preview-level-guid'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing preview-level-guid' );
            }

            if ( ! isset( $_POST['sek_action'] ) || empty( $_POST['sek_action'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing sek_action' );
            }
            $sek_action = $_POST['sek_action'];

            $exported_setting_validities = array();

            // CHECK THE SETTING VALIDITIES BEFORE RENDERING
            // When a module has been registered with a sanitize_callback, we can collect the possible problems here before sending the response.
            // Then, on ajax.done(), in SekPreviewPrototype::schedulePanelMsgReactions, we will send the setting validities object to the panel
            if ( is_customize_preview() ) {
                global $wp_customize;
                // prepare the setting validities so we can pass them when sending the ajax response
                $setting_validities = $wp_customize->validate_setting_values( $wp_customize->unsanitized_post_values() );
                $raw_exported_setting_validities = array_map( array( $wp_customize, 'prepare_setting_validity_for_js' ), $setting_validities );

                // filter the setting validity to only keep the __nimble__ prefixed ui settings
                $exported_setting_validities = array();
                foreach( $raw_exported_setting_validities as $setting_id => $validity ) {
                    // don't consider the not Nimble UI settings, not starting with __nimble__
                    if ( false === strpos( $setting_id , NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) )
                      continue;
                    $exported_setting_validities[ $setting_id ] = $validity;
                }
            }

            $html = '';
            // is this action possible ?
            if ( in_array( $sek_action, $this->ajax_action_map ) ) {
                $content_type = null;
                if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                    $content_type = $_POST['content_type'];
                }

                // This 'preset_section' === $content_type statement has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                if ( 'preset_section' === $content_type ) {
                    $collection_of_preset_section_id = null;
                    if ( array_key_exists( 'collection_of_preset_section_id', $_POST ) && is_array( $_POST['collection_of_preset_section_id'] ) ) {
                        $collection_of_preset_section_id = $_POST['collection_of_preset_section_id'];
                    }

                    switch ( $sek_action ) {
                        // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                        case 'sek-add-content-in-new-sektion' :
                        case 'sek-add-content-in-new-nested-sektion' :
                            if ( 'preset_section' === $content_type ) {
                                if ( !is_array( $collection_of_preset_section_id ) || empty( $collection_of_preset_section_id ) ) {
                                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                                    break;
                                }
                                foreach ( $_POST['collection_of_preset_section_id'] as $preset_section_id ) {
                                    $html .= $this->sek_ajax_fetch_content( $sek_action, $preset_section_id );
                                }
                            // 'module' === $content_type
                            } else {
                                $html = $this->sek_ajax_fetch_content( $sek_action );
                            }

                        break;

                        default :
                            $html = $this->sek_ajax_fetch_content( $sek_action );
                        break;
                    }
                } else {
                      $html = $this->sek_ajax_fetch_content( $sek_action );
                }

                //sek_error_log(__CLASS__ . '::' . __FUNCTION__ , $html );
                if ( is_wp_error( $html ) ) {
                    wp_send_json_error( $html );
                } else {
                    $response = array(
                        'contents' => $html,
                        'setting_validities' => $exported_setting_validities
                    );
                    wp_send_json_success( apply_filters( 'sek_content_results', $response, $sek_action ) );
                }
            } else {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => this ajax action ( ' . $sek_action . ' ) is not listed in the map ' );
            }


        }//sek_get_content_for_injection()


        // hook : add_filter( "sek_set_ajax_content___{$action}", array( $this, 'sek_ajax_fetch_content' ) );
        // $_POST looks like Array
        // (
        //     [action] => sek_get_content
        //     [withNonce] => false
        //     [id] => __nimble__0b7c85561448ab4eb8adb978
        //     [skope_id] => skp__post_page_home
        //     [sek_action] => sek-add-section
        //     [SEKFrontNonce] => 3713b8ac5c
        //     [customized] => {\"nimble___loop_start[skp__post_page_home]\":{...}}
        // )
        // @return string
        // @param $sek_action is $_POST['sek_action']
        // @param $maybe_preset_section_id is used when injecting a collection of preset sections
        private function sek_ajax_fetch_content( $sek_action = '', $maybe_preset_section_id = '' ) {
            //sek_error_log( __CLASS__ . '::' . __FUNCTION__ , $_POST );
            // the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // since wp has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
            if ( ! is_array( $sektionSettingValue ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue => it should be an array().' );
                return;
            }
            if ( empty( $sek_action ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => invalid sek_action param' );
                return;
            }
            $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
            if ( ! is_array( $sektion_collection ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektion_collection => it should be an array().' );
                return;
            }

            $candidate_id = '';
            $collection = array();
            $level_model = array();

            $is_stylesheet = false;

            switch ( $sek_action ) {
                case 'sek-add-section' :
                case 'sek-duplicate-section' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        //$level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;

                // This $content_type var has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                case 'sek-add-content-in-new-sektion' :
                case 'sek-add-content-in-new-nested-sektion' :
                    $content_type = null;
                    if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                        $content_type = $_POST['content_type'];
                    }
                    if ( 'preset_section' === $content_type ) {
                        if ( ! array_key_exists( 'collection_of_preset_section_id', $_POST ) || ! is_array( $_POST['collection_of_preset_section_id'] ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                            break;
                        }
                        if ( ! is_string( $maybe_preset_section_id ) || empty( $maybe_preset_section_id ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => inavalid preset section id' );
                            break;
                        }
                        $level_id = $maybe_preset_section_id;
                    // module content type case.
                    // the level id has been passed the regular way
                    } else {
                        $level_id = $_POST[ 'id' ];
                    }

                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        //$level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                        $level_model = sek_get_level_model( $level_id, $sektion_collection );
                    }
                break;

                //only used for nested section
                case 'sek-remove-section' :
                    if ( ! array_key_exists( 'is_nested', $_POST ) || true !== json_decode( $_POST['is_nested'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => the section must be nested in this ajax action' );
                        break;
                    } else {
                        // we need to set the parent_model here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    }
                break;

                // We re-render the entire parent sektion collection in all cases
                case 'sek-add-column' :
                case 'sek-remove-column' :
                case 'sek-duplicate-column' :
                case 'sek-refresh-columns-in-sektion' :
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST['in_sektion'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_sektion param' );
                        break;
                    }
                    // sek_error_log('sektion_collection', $sektion_collection );
                    $level_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                break;

                // We re-render the entire parent column collection
                case 'sek-add-module' :
                case 'sek-remove-module' :
                case 'sek-refresh-modules-in-column' :
                case 'sek-duplicate-module' :
                    if ( ! array_key_exists( 'in_column', $_POST ) || empty( $_POST['in_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_column param' );
                        break;
                    }
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST[ 'in_sektion' ] ) ) {
                        $this->parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $this->parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                break;

                case 'sek-resize-columns' :
                    if ( ! array_key_exists( 'resized_column', $_POST ) || empty( $_POST['resized_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing resized_column' );
                        break;
                    }
                    $is_stylesheet = true;
                break;

                case 'sek-refresh-stylesheet' :
                    $is_stylesheet = true;
                break;

                 case 'sek-refresh-level' :
                    if ( ! array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing level id' );
                        break;
                    }
                    if ( !empty( $_POST['level'] ) && 'column' === $_POST['level'] ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_parent_level_model( $_POST['id'], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                break;
            }//Switch sek_action

            // sek_error_log('LEVEL MODEL WHEN AJAXING', $level_model );

            ob_start();

            if ( $is_stylesheet ) {
                $r = $this->print_or_enqueue_seks_style( $_POST['location_skope_id'] );
            } else {
                if ( 'no_match' == $level_model ) {
                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action . ' => missing level model' );
                    ob_end_clean();
                    return;
                }
                if ( empty( $level_model ) || ! is_array( $level_model ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => empty or invalid $level_model' );
                    ob_end_clean();
                    return;
                }
                // note that in the case of a sektion nested inside a column, the parent_model has been set in the switch{ case : ... } above ,so we can access it in the ::render method to calculate the column width.
                $r = $this->render( $level_model );
            }
            $html = ob_get_clean();
            if ( is_wp_error( $r ) ) {
                return $r;
            } else {
                // the $html content should not be empty when ajaxing a template
                // it can be empty when ajaxing a stylesheet
                if ( ! $is_stylesheet && empty( $html ) ) {
                      // return a new WP_Error that will be intercepted in sek_get_level_content_for_injection
                      $html = new \WP_Error( 'ajax_fetch_content_error', __CLASS__ . '::' . __FUNCTION__ . ' => no content returned for sek_action : ' . $sek_action );
                }
                return apply_filters( "sek_set_ajax_content", $html, $sek_action );// this is sent with wp_send_json_success( apply_filters( 'sek_content_results', $html, $sek_action ) );
            }
        }











        /////////////////////////////////////////////////////////////////
        // hook : wp_ajax_sek_import_attachment
        function sek_ajax_import_attachment() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( !isset( $_POST['img_url'] ) || !is_string($_POST['img_url']) ) {
                wp_send_json_error( 'missing_or_invalid_img_url_when_importing_image');
            }

            $id = sek_sideload_img_and_return_attachment_id( $_POST['img_url'] );
            if ( is_wp_error( $id ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => problem when trying to wp_insert_attachment() for img : ' . $_POST['img_url'] . ' | SERVER ERROR => ' . json_encode( $id ) );
            } else {
                wp_send_json_success([
                  'id' => $id,
                  'url' => wp_get_attachment_url( $id )
                ]);
            }
        }
















        /////////////////////////////////////////////////////////////////
        // hook : wp_ajax_sek_save_section
        function sek_ajax_save_section() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            // We must have a title and a section_id and sektion data
            if ( empty( $_POST['sek_title']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing title' );
            }
            if ( empty( $_POST['sek_id']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing sektion_id' );
            }
            if ( empty( $_POST['sek_data']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing sektion data' );
            }
            if ( ! is_string( $_POST['sek_data'] ) ) {
                wp_send_json_error( __FUNCTION__ . ' => the sektion data must be a json stringified' );
            }
            // sek_error_log('SEKS DATA ?', $_POST['sek_data'] );
            // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
            $sektion_to_save = array(
                'title' => $_POST['sek_title'],
                'description' => $_POST['sek_description'],
                'id' => $_POST['sek_id'],
                'type' => 'content',//in the future will be used to differentiate header, content and footer sections
                'creation_date' => date("Y-m-d H:i:s"),
                'update_date' => '',
                'data' => $_POST['sek_data']//<= json stringified
            );

            $saved_section_post = sek_update_saved_seks_post( $sektion_to_save );
            if ( is_wp_error( $saved_section_post ) ) {
                wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_saved_seks_post()' );
            } else {
                // sek_error_log( 'ALORS CE POST?', $saved_section_post );
                wp_send_json_success( [ 'section_post_id' => $saved_section_post->ID ] );
            }

            //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
        }


        // @hook wp_ajax_sek_sek_get_user_saved_sections
        function sek_sek_get_user_saved_sections() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            // We must have a section_id provided
            if ( empty( $_POST['preset_section_id']) || ! is_string( $_POST['preset_section_id'] ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing or invalid preset_section_id' );
            }
            $section_id = $_POST['preset_section_id'];

            $section_data_decoded_from_custom_post_type = sek_get_saved_sektion_data( $section_id );
            if ( ! empty( $section_data_decoded_from_custom_post_type ) ) {
                wp_send_json_success( $section_data_decoded_from_custom_post_type );
            } else {
                $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
                if ( ! is_array( $all_saved_seks ) || empty( $all_saved_seks[$section_id]) ) {
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing section data in get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS )' );
                }
                // $section_infos is an array
                // Array
                // (
                //     [post_id] => 399
                //     [title] => My section one
                //     [description] =>
                //     [creation_date] => 2018-10-29 13:52:54
                //     [type] => content
                // )
                $section_infos = $all_saved_seks[$section_id];
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing post data for section title ' . $section_infos['title'] );
            }
        }






        ////////////////////////////////////////////////////////////////
        // REVISIONS
        // Fired in __construct()
        function sek_get_revision_history() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            if ( ! isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }
            $rev_list = sek_get_revision_history_from_posts( $_POST['skope_id'] );
            wp_send_json_success( $rev_list );
        }


        function sek_get_single_revision() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            if ( ! isset( $_POST['revision_post_id'] ) || empty( $_POST['revision_post_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing revision_post_id' );
            }
            $revision = sek_get_single_post_revision( $_POST['revision_post_id'] );
            wp_send_json_success( $revision );
        }



        ////////////////////////////////////////////////////////////////
        // POST CATEGORIES => to be used in the category picker select input
        // Fired in __construct()
        function sek_get_post_categories() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
            $raw_cats = get_categories();
            $raw_cats = is_array( $raw_cats ) ? $raw_cats : array();
            $cat_collection = array();
            foreach( $raw_cats as $cat ) {
                $cat_collection[] = array(
                    'id' => $cat->term_id,
                    'slug' => $cat->slug,
                    'name' => sprintf( '%s (%s %s)', $cat->cat_name, $cat->count, __('posts', 'text_doma') )
                );
            }
            wp_send_json_success( $cat_collection );
        }

        ////////////////////////////////////////////////////////////////
        // CODE EDITOR PARAMS => to be used in the code editor input
        // Fired in __construct()
        function sek_get_code_editor_params() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
            $code_type = isset( $_POST['code_type'] ) ? $_POST['code_type'] : 'text/html';
            $editor_params = nimble_get_code_editor_settings( array(
                'type' => $code_type
            ));
            wp_send_json_success( $editor_params );
        }

        ////////////////////////////////////////////////////////////////
        // POSTPONE FEEDBACK NOTIFICATION IN CUSTOMIZER
        // INSPIRED FROM CORE DISMISS POINTER MECHANISM
        // @see wp-admin/includes/ajax-actions.php
        function sek_postpone_feedback_notification() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            if ( !isset( $_POST['transient_duration_in_days'] ) ||!is_numeric( $_POST['transient_duration_in_days'] ) ) {
                $transient_duration = 7 * DAY_IN_SECONDS;
            } else {
                $transient_duration = $_POST['transient_duration_in_days'] * DAY_IN_SECONDS;
            }
            set_transient( NIMBLE_FEEDBACK_NOTICE_ID, 'maybe_later', $transient_duration );
            wp_die( 1 );
        }


        ////////////////////////////////////////////////////////////////
        // USED TO PRINT THE BUTTON EDIT WITH NIMBLE ON POSTS AND PAGES
        // when using Gutenberg editor
        // implemented for https://github.com/presscustomizr/nimble-builder/issues/449
        function sek_get_customize_url_for_nimble_edit_button() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( ! isset( $_POST['nimble_edit_post_id'] ) || empty( $_POST['nimble_edit_post_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing post_id' );
            }

            $post_id = $_POST['nimble_edit_post_id'];

            // Build customize_url
            // @see function sek_get_customize_url_when_is_admin()
            $return_url_after_customization = '';//"/wp-admin/post.php?post={$post_id}&action=edit";
            $customize_url = sek_get_customize_url_for_post_id( $post_id, $return_url_after_customization );
            wp_send_json_success( $customize_url );
        }


        ////////////////////////////////////////////////////////////////
        // FETCH FONT AWESOME ICONS
        // hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
        // this dynamic filter is declared on wp_ajax_ac_get_template
        // It allows us to populate the server response with the relevant module html template
        // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
        //
        // For czr_tiny_mce_editor_module, we request the font_list tmpl
        function sek_get_fa_icon_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
            if ( empty( $requested_tmpl ) ) {
                wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
            }
            return wp_json_encode(
                $this->sek_retrieve_decoded_font_awesome_icons()
            );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }



        //retrieves faicons:
        // 1) from faicons.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
        // otherwise
        // 2) from the transient set if it exists
        function sek_retrieve_decoded_font_awesome_icons() {
            // this file must be generated with: https://github.com/presscustomizr/nimble-builder/issues/57
            $faicons_json_path      = NIMBLE_BASE_PATH . '/assets/faicons.json';
            $faicons_transient_name = 'sek_font_awesome_february_2020';
            if ( false == get_transient( $faicons_transient_name ) ) {
                if ( file_exists( $faicons_json_path ) ) {
                    $faicons_raw      = @file_get_contents( $faicons_json_path );

                    if ( false === $faicons_raw ) {
                        $faicons_raw = wp_remote_fopen( $faicons_json_path );
                    }

                    $faicons_decoded   = json_decode( $faicons_raw, true );
                    set_transient( $faicons_transient_name , $faicons_decoded , 60*60*24*3000 );
                } else {
                    wp_send_json_error( __FUNCTION__ . ' => the file faicons.json is missing' );
                }
            }
            else {
                $faicons_decoded = get_transient( $faicons_transient_name );
            }

            return $faicons_decoded;
        }








        ////////////////////////////////////////////////////////////////
        // FETCH FONT LISTS
        // hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
        // For czr_tiny_mce_editor_module, we request the font_list tmpl
        function sek_get_font_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
            if ( empty( $requested_tmpl ) ) {
                wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
            }

            return wp_json_encode( array(
                'cfonts' => $this->sek_get_cfonts(),
                'gfonts' => $this->sek_get_gfonts(),
            ) );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }


        function sek_get_cfonts() {
            $cfonts = array();
            $raw_cfonts = array(
                'Arial Black,Arial Black,Gadget,sans-serif',
                'Century Gothic',
                'Comic Sans MS,Comic Sans MS,cursive',
                'Courier New,Courier New,Courier,monospace',
                'Georgia,Georgia,serif',
                'Helvetica Neue, Helvetica, Arial, sans-serif',
                'Impact,Charcoal,sans-serif',
                'Lucida Console,Monaco,monospace',
                'Lucida Sans Unicode,Lucida Grande,sans-serif',
                'Palatino Linotype,Book Antiqua,Palatino,serif',
                'Tahoma,Geneva,sans-serif',
                'Times New Roman,Times,serif',
                'Trebuchet MS,Helvetica,sans-serif',
                'Verdana,Geneva,sans-serif',
            );
            foreach ( $raw_cfonts as $font ) {
              //no subsets for cfonts => epty array()
              $cfonts[] = array(
                  'name'    => $font ,
                  'subsets'   => array()
              );
            }
            return apply_filters( 'sek_font_picker_cfonts', $cfonts );
        }


        //retrieves gfonts:
        // 1) from webfonts.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
        // otherwise
        // 2) from the transiet set if it exists
        //
        // => Until June 2017, the webfonts have been stored in 'tc_gfonts' transient
        // => In June 2017, the Google Fonts have been updated with a new webfonts.json
        // generated from : https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBID8gp8nBOpWyH5MrsF7doP4fczXGaHdA
        //
        // => The transient name is now : czr_gfonts_june_2017
        function sek_retrieve_decoded_gfonts() {
            if ( false == get_transient( 'sek_gfonts_march_2020' ) ) {
                $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

                if ( $gfont_raw === false ) {
                  $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
                }

                $gfonts_decoded   = json_decode( $gfont_raw, true );
                set_transient( 'sek_gfonts_march_2020' , $gfonts_decoded , 60*60*24*3000 );
            }
            else {
              $gfonts_decoded = get_transient( 'sek_gfonts_march_2020' );
            }

            return $gfonts_decoded;
        }



        //@return the google fonts
        function sek_get_gfonts( $what = null ) {
          //checks if transient exists or has expired

          $gfonts_decoded = $this->sek_retrieve_decoded_gfonts();
          $gfonts = array();
          //$subsets = array();

          // $subsets['all-subsets'] = sprintf( '%1$s ( %2$s %3$s )',
          //   __( 'All languages' , 'text_doma' ),
          //   count($gfonts_decoded['items']) + count( $this->get_cfonts() ),
          //   __('fonts' , 'text_doma' )
          // );

          foreach ( $gfonts_decoded['items'] as $font ) {
            foreach ( $font['variants'] as $variant ) {
              $name     = str_replace( ' ', '+', $font['family'] );
              $gfonts[]   = array(
                  'name'    => $name . ':' .$variant
                  //'subsets'   => $font['subsets']
              );
            }
            //generates subset list : subset => font number
            // foreach ( $font['subsets'] as $sub ) {
            //   $subsets[$sub] = isset($subsets[$sub]) ? $subsets[$sub]+1 : 1;
            // }
          }

          //finalizes the subset array
          // foreach ( $subsets as $subset => $font_number ) {
          //   if ( 'all-subsets' == $subset )
          //     continue;
          //   $subsets[$subset] = sprintf('%1$s ( %2$s %3$s )',
          //     $subset,
          //     $font_number,
          //     __('fonts' , 'text_doma' )
          //   );
          // }

          return ('subsets' == $what) ? apply_filters( 'sek_font_picker_gfonts_subsets ', $subsets ) : apply_filters( 'sek_font_picker_gfonts', $gfonts )  ;
        }











        // hook : 'wp_ajax_sek_get_preview_ui_element'
        /*function sek_get_ui_content_for_injection( $params ) {
            // error_log( print_r( $_POST, true ) );
            // error_log( print_r( sek_get_skoped_seks( "skp__post_page_home", 'loop_start' ), true ) );
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
                return;
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
                return;
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
                return;
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
                return;
            }

            if ( ! isset( $_POST['level'] ) || empty( $_POST['level'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing level' );
                return;
            }
            if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing level id' );
                return;
            }
            if ( ! isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
                return;
            }


            // the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // since wp has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
            if ( ! is_array( $sektionSettingValue ) || ! array_key_exists( 'collection', $sektionSettingValue ) || ! is_array( $sektionSettingValue['collection'] ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue' );
                return;
            }
            // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
            $this->parent_model = sek_get_parent_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );
            $this->model = sek_get_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );

            $level = $_POST['level'];

            $html = '';
            ob_start();
                load_template( dirname( __FILE__ ) . "/tmpl/ui/block-overlay-{$level}.php", false );
            $html = ob_get_clean();

            if ( empty( $html ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no content returned' );
            } else {
                wp_send_json_success( apply_filters( 'sek_ui_content_results', $html ) );
            }
        }//sek_get_content_for_injection()*/

    }//class
endif;
?><?php
if ( !class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_assets_printing() {
            // Maybe adds `defer` support for scripts registered or enqueued
            // and for which we've added an attribute with sek_defer_script( $_hand, 'defer', true );
            // inspired from Twentytwenty WP theme
            // @see https://core.trac.wordpress.org/ticket/12009
            add_filter( 'script_loader_tag', array( $this, 'sek_filter_script_loader_tag' ), 10, 2 );

            // Load Front CSS
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_css_assets' ) );

            // Load Front JS
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_js_assets' ) );

            // added March 2020 when experimenting for https://github.com/presscustomizr/nimble-builder/issues/626
            add_action( 'wp_default_scripts', array( $this, 'sek_maybe_dequeue_jquery_and_schedule_jquery_migrate' ) );

            // replace wp_localize because we don't need to indicate a dependency to any scripts for local data
            add_action( 'wp_head', array( $this, 'sek_add_local_script_data' ), 0  );

            // Maybe print split module stylesheet inline
            // introduced in march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            //add_action( 'wp_head', array( $this, 'sek_maybe_print_inline_split_module_stylesheets' ), PHP_INT_MAX  );

            // initialize Nimble front js app
            add_action( 'wp_head', array( $this, 'sek_initialize_front_js_app' ), 0  );

            // Inform Nimble app that jQuery is loaded
            add_action( 'wp_head', array( $this, 'sek_detect_jquery' ), PHP_INT_MAX );

            // Maybe print a CSS loader for img and background lazy loaded
            add_action( 'wp_head', array( $this, 'sek_print_style_for_css_loader' ), PHP_INT_MAX );

            // Emit an event when jQuery is detected. 'nb-jquery-loaded'
            // maybe fetch jQuery from a CDN when dequeued
            add_action( 'wp_footer', array( $this, 'sek_preload_jquery_from_dns' ));

            // Maybe preload Font Awesome icons when really needed ( sniff first ) + nb_.listenTo('nb-needs-fa')
            add_action( 'wp_footer', array( $this, 'sek_maybe_preload_front_scripts_and_styles' ), PHP_INT_MAX );

            // Maybe load scripts on scroll
            add_action( 'wp_footer', array( $this, 'sek_maybe_load_scripts_in_ajax' ), PHP_INT_MAX );
        }//_schedule_front_and_preview_assets_printing



        // hook : 'wp_enqueue_scripts'
        function sek_enqueue_front_css_assets() {
            /* ------------------------------------------------------------------------- *
             *  MAIN STYLESHEET
            /* ------------------------------------------------------------------------- */
            // do we have local or global sections to render in this page ?
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            // we know the skope_id because 'wp' has been fired
            $has_local_sections = sek_local_skope_has_nimble_sections( skp_get_skope_id() );
            $has_global_sections = sek_has_global_sections();

            // the light split stylesheet is never used when customizing
            $is_stylesheet_split_for_performance = !skp_is_customizing() && sek_use_split_stylesheets_on_front();

            $main_stylesheet_name = $is_stylesheet_split_for_performance ? 'sek-base-light' : 'sek-base';

            // Always load the base Nimble style when user logged in so we can display properly the button in the top admin bar.
            if ( is_user_logged_in() || $has_local_sections || $has_global_sections ) {
                $rtl_suffix = is_rtl() ? '-rtl' : '';

                //wp_enqueue_style( 'google-material-icons', '//fonts.googleapis.com/icon?family=Material+Icons', array(), null, 'all' );
                //base custom CSS bootstrap inspired
                wp_enqueue_style(
                    $main_stylesheet_name,
                    sprintf(
                        '%1$s/assets/front/css/%2$s' ,
                        NIMBLE_BASE_URL,
                        sek_is_dev_mode() ? "{$main_stylesheet_name}{$rtl_suffix}.css" : "{$main_stylesheet_name}{$rtl_suffix}.min.css"
                    ),
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    'all'
                );
            }


            /* ------------------------------------------------------------------------- *
             *  STOP HERE IF NOTHING TO PRINT
            /* ------------------------------------------------------------------------- */
            // We don't need Nimble Builder assets when no local or global sections have been created
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            if ( !$has_local_sections && !$has_global_sections )
              return;


            /* ------------------------------------------------------------------------- *
             *  MODULE PARTIAL STYLESHEETS
            /* ------------------------------------------------------------------------- */
            // populate the collection of module displayed in current context : local + global
            // introduced march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            // formed like :
            // [czr_heading_module] => Array
            //     (
            //         [0] => __nimble__9a02775e86ec
            //         [1] => __nimble__01f1e8d56415
            //         [2] => __nimble__8fc8dac22299
            //         [3] => __nimble__b71c69fd674d
            //         [4] => __nimble__b74a63e1dc57
            //         [5] => __nimble__ca13a73ca586
            //         [6] => __nimble__e66b407f0f2b
            //         [7] => __nimble__7d6526ab1812
            //     )

            // [czr_img_slider_module] => Array
            //     (
            //         [0] => __nimble__3a38fe3587b2
            //     )

            // [czr_accordion_module] => Array
            //     (
            //         [0] => __nimble__ec3d7956fe17
            //     )

            // [czr_social_icons_module] => Array
            //     (
            //         [0] => __nimble__c1526193134e
            //     )
            $contextually_active_modules = sek_get_collection_of_contextually_active_modules();

            //sek_error_log('$contextually_active_modules ?', $contextually_active_modules );


            // public $big_module_stylesheet_map = [
            //     'czr_quote_module' => 'quote-module',
            //     'czr_icon_module' => 'icon-module',
            //     'czr_img_slider_module' => 'img-slider-module-with-swiper',
            //     'czr_accordion_module' => 'accordion-module',
            //     'czr_menu_module' => 'menu-module',
            //     'czr_post_grid_module' => 'post-grid-module',
            //     'czr_simple_form_module' => 'simple-form-module'
            // ];
            // SPLIT STYLESHEETS
            // introduced march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            // if the module stylesheets are inline, see wp_head action
            if ( !skp_is_customizing() && $is_stylesheet_split_for_performance ) {
                // loop on the map module type (candidates for split) => stylesheet file name
                foreach (Nimble_Manager()->big_module_stylesheet_map as $module_type => $stylesheet_name ) {
                    if ( !array_key_exists($module_type , $contextually_active_modules ) )
                      continue;

                    wp_enqueue_style(
                        $module_type,
                        sprintf( '%1$s%2$s%3$s',
                            NIMBLE_BASE_URL . '/assets/front/css/modules/',
                            $stylesheet_name,
                            sek_is_dev_mode() ? '.css' : '.min.css'
                        ),
                        array( $main_stylesheet_name ),
                        NIMBLE_ASSETS_VERSION,
                        $media = 'all'
                    );
                }
            }


            /* ------------------------------------------------------------------------- *
             *  LIGHT BOX WITH MAGNIFIC POPUP
            /* ------------------------------------------------------------------------- */
            // when sek_preload_front_scripts(), the stylesheet is preloaded
            if ( sek_front_needs_magnific_popup() && !sek_preload_front_scripts() && !sek_load_front_assets_in_ajax() ) {
                wp_enqueue_style(
                    'nb-magnific-popup',
                    NIMBLE_BASE_URL . '/assets/front/css/libs/magnific-popup.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }

            /* ------------------------------------------------------------------------- *
             *  SWIPER FOR SLIDERS
            /* ------------------------------------------------------------------------- */
            if ( array_key_exists('czr_img_slider_module' , $contextually_active_modules) ) {
                // march 2020 : when using split stylesheet, swiper css is already included in assets/front/css/modules/img-slider-module-with-swiper.css
                // when loading assets in ajax, swiper stylesheet is loaded dynamically
                // so we don't need to enqueue it
                // added for https://github.com/presscustomizr/nimble-builder/issues/612
                // added for https://github.com/presscustomizr/nimble-builder/issues/635
                if ( skp_is_customizing() || !sek_load_front_assets_in_ajax() ) {
                      wp_enqueue_style(
                          'nb-swiper',
                          sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/css/libs/swiper.css' : NIMBLE_BASE_URL . '/assets/front/css/libs/swiper.min.css',
                          array(),
                          NIMBLE_ASSETS_VERSION,
                          $media = 'all'
                      );
                }
            }

            /* ------------------------------------------------------------------------- *
             *  FONT AWESOME STYLESHEET
            /* ------------------------------------------------------------------------- */
            if ( sek_front_needs_font_awesome() && !sek_preload_font_awesome() && !sek_load_front_assets_in_ajax() ) {
                wp_enqueue_style(
                    'nb-font-awesome',
                    NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }

        }//sek_enqueue_front_assets









        //@'wp_enqueue_scripts'
        function sek_enqueue_front_js_assets() {

            /* ------------------------------------------------------------------------- *
             *  MAIN SCRIPT
             /* ------------------------------------------------------------------------- */
            // wp_register_script(
            //     'sek-front-fmk-js',
            //     NIMBLE_BASE_URL . '/assets/front/js/_front_js_fmk.js',
            //     array( 'jquery', 'underscore'),
            //     time(),
            //     true
            // );
            if ( !sek_load_front_assets_in_ajax() ) {
                wp_enqueue_script(
                    'nb-main-js',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.js' : NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.min.js',
                    //array( 'jquery', 'underscore'),
                    // october 2018 => underscore is concatenated in the main front js file.
                    ( !skp_is_customizing() && sek_is_jquery_replaced() ) ? array() : array( 'jquery'),
                    NIMBLE_ASSETS_VERSION,
                    false
                );
                // added for https://github.com/presscustomizr/nimble-builder/issues/583
                sek_defer_script('nb-main-js');
            }


            // when front scripts are preloaded, jquery is not declared as dependency
            // we need to make sure its enqueued
            if ( sek_preload_front_scripts() || sek_load_front_assets_in_ajax() ) {
                if ( ! sek_is_jquery_replaced() ) {
                    wp_enqueue_script('jquery');
                }
            }

            // Google reCAPTCHA
            $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
            $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();


            $contextually_active_modules = sek_get_collection_of_contextually_active_modules();

            // public $big_module_stylesheet_map = [
            //     'czr_quote_module' => 'quote-module',
            //     'czr_icon_module' => 'icon-module',
            //     'czr_img_slider_module' => 'img-slider-module-with-swiper',
            //     'czr_accordion_module' => 'accordion-module',
            //     'czr_menu_module' => 'menu-module',
            //     'czr_post_grid_module' => 'post-grid-module',
            //     'czr_simple_form_module' => 'simple-form-module'
            // ];


            // The following js assets are loaded defer if customizing or if preload/ajax not enabled
            if ( ( !sek_preload_front_scripts() && !sek_load_front_assets_in_ajax() ) || skp_is_customizing() ) {
                /* ------------------------------------------------------------------------- *
                 *  LIGHT BOX WITH MAGNIFIC POPUP
                /* ------------------------------------------------------------------------- */
                // Magnific Popup is loaded when needed only
                // front : Load if js not loaded dynamically + we detect the need for the script
                // customizing : load if not loaded dynamically
                if ( sek_front_needs_magnific_popup() || skp_is_customizing() ) {
                    wp_enqueue_script(
                        'nb-magnific-popups',
                        sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-magnific-popup.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-magnific-popup.min.js',
                        array(), //( !skp_is_customizing() && sek_is_jquery_replaced() ) ? array() : array( 'jquery'),
                        NIMBLE_ASSETS_VERSION,
                        false
                    );
                    sek_defer_script('nb-magnific-popups');
                }


                /* ------------------------------------------------------------------------- *
                 *  SWIPER FOR SLIDERS
                 /* ------------------------------------------------------------------------- */
                // SWIPER JS LIB + MODULE SCRIPT
                // Swiper js is needed for the czr_img_slider_module
                // front : Load if js not loaded dynamically + we detect the need for the script
                // customizing : load if not loaded dynamically
                if ( array_key_exists('czr_img_slider_module' , $contextually_active_modules) || skp_is_customizing() ) {
                    wp_enqueue_script(
                      'nb-swiper',
                      sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/swiper.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/swiper.min.js',
                      array(),
                      NIMBLE_ASSETS_VERSION,
                      false
                    );
                    // not added when customizing
                    sek_defer_script('nb-swiper');
                }


                /* ------------------------------------------------------------------------- *
                 *  VIDEO BG
                 /* ------------------------------------------------------------------------- */
                // front : Load if js not loaded dynamically + we detect the need for the script
                // customizing : load if not loaded dynamically
                if ( ( sek_front_needs_video_bg() ) || skp_is_customizing() ) {
                    wp_enqueue_script(
                        'nb-video-bg-plugin',
                        sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-video-bg.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-video-bg.min.js',
                        array(),
                        NIMBLE_ASSETS_VERSION,
                        false
                    );
                    // not added when customizing
                    sek_defer_script('nb-video-bg-plugin');
                }
            }//if ( ( !sek_preload_front_scripts() && !sek_load_front_assets_in_ajax() ) || skp_is_customizing() ) {
        }//sek_enqueue_front_js_assets




        // @'wp_default_scripts'
        // see https://wordpress.stackexchange.com/questions/291700/how-to-stop-jquery-migrate-manually
        // https://stackoverflow.com/questions/18421404/how-do-i-stop-wordpress-loading-jquery-and-jquery-migrate#25977181
        function sek_maybe_dequeue_jquery_and_schedule_jquery_migrate( &$scripts ) {
            if ( !skp_is_customizing() && !is_admin() && sek_is_jquery_replaced() && !empty( $scripts->registered['jquery'] ) ) {
                $scripts->registered['jquery']->deps = array_diff(
                    $scripts->registered['jquery']->deps,
                    [ 'jquery-migrate' ]
                );
                $scripts->remove( 'jquery');
                //$scripts->add( 'jquery', false, array( 'jquery-core' ), '1.2.1' );
            }
            // when jquery is loaded async and not replaced we need to dequeue jquery-migrate and load it dynamically on 'nb-jquery-loaded'
            if ( !skp_is_customizing() && sek_load_jquery_async() && !sek_is_jquery_replaced() ) {
                $scripts->registered['jquery']->deps = array_diff(
                    $scripts->registered['jquery']->deps,
                    [ 'jquery-migrate' ]
                );
                $scripts->remove('jquery-migrate');
                // Inform Nimble app that jQuery is loaded
                add_action( 'wp_head', array( $this, 'sek_maybe_inject_jquery_migrate' ), 10 );
            }
        }




        /**
         * Fired @'script_loader_tag'
         * Adds async/defer attributes to enqueued / registered scripts.
         * works with sek_defer_script()
         * see https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
         * based on a solution found in Twentytwenty
         * and for which we've added an attribute with sek_defer_script( $_hand, 'defer', true );
         * If #12009 lands in WordPress, this function can no-op since it would be handled in core.
         *
         * @param string $tag    The script tag.
         * @param string $handle The script handle.
         * @return string Script HTML string.
        */
        public function sek_filter_script_loader_tag( $tag, $handle ) {
            // adds an id to jquery core so we can detect when it's loaded
            if ( 'jquery-core' === $handle ) {
                // tag is a string and looks like <script src='http://customizr-dev.test/wp-includes/js/jquery/jquery-migrate.js?ver=1.4.1'></script>
                $tag = str_replace('src=', 'id="'.NIMBLE_JQUERY_ID.'" src=', $tag);
                if ( sek_load_jquery_async() && !skp_is_customizing() ) {
                    $tag = str_replace('src=', 'async src=', $tag);
                }
            }

            // if ( skp_is_customizing() )
            //   return $tag;

            foreach ( [ 'async', 'defer' ] as $attr ) {
              if ( !wp_scripts()->get_data( $handle, $attr ) ) {
                continue;
              }
              // Prevent adding attribute when already added in #12009.
              if ( !preg_match( ":\s$attr(=|>|\s):", $tag ) ) {
                $tag = preg_replace( ':(?=></script>):', " $attr", $tag, 1 );
              }
              // Only allow async or defer, not both.
              break;
            }
            return $tag;
        }


        // @wp_head0
        function sek_add_local_script_data() {
            $l10n = array(
                'isDevMode' => sek_is_dev_mode(),
                //'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
                'localSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks() ) : '',
                'globalSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID ) ) : '',
                'skope_id' => skp_get_skope_id(), //added for debugging purposes
                'recaptcha_public_key' => !empty ( $global_recaptcha_opts['public_key'] ) ? $global_recaptcha_opts['public_key'] : '',

                'lazyload_enabled' => sek_is_img_smartload_enabled(),
                'video_bg_lazyload_enabled' => sek_is_video_bg_lazyload_enabled(),
                'load_front_assets_on_scroll' => sek_load_front_assets_in_ajax(),

                'assetVersion' => NIMBLE_ASSETS_VERSION,
                'frontAssetsPath' => NIMBLE_BASE_URL . '/assets/front/',
                'contextuallyActiveModules' => sek_get_collection_of_contextually_active_modules(),
                'fontAwesomeAlreadyEnqueued' => wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued')
            );
            foreach ( (array) $l10n as $key => $value ) {
                if ( ! is_scalar( $value ) ) {
                  continue;
                }
                $l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
            }

            printf('<script>%1$s</script>', "var sekFrontLocalized = " . wp_json_encode( $l10n ) . ';' );
        }




        // @'wp_head'0
        // Loading sequence :
        // 1) window.nb_ utils starts being populated
        // 2) 'nb-jquery-loaded' => fired in footer when jQuery is defined <= window.nb_ utils is completed with jQuery dependant helper properties and methods
        // 3) 'nb-app-ready' => fired in footer on 'nb-jquery-loaded' <= all module scripts are fired on this event
        // 4) 'nb-jmp-parsed', ... are emitted in each script files
        function sek_initialize_front_js_app() {
            ?>
            <script id="nimble-app-init">window.nb_={},function(e,n){if(window.nb_={isArray:function(e){return Array.isArray(e)||"[object Array]"===toString.call(e)},inArray:function(e,n){return!(!nb_.isArray(e)||nb_.isUndefined(n))&&e.indexOf(n)>-1},isUndefined:function(e){return void 0===e},isObject:function(e){var n=typeof e;return"function"===n||"object"===n&&!!e},errorLog:function(){nb_.isUndefined(console)||"function"!=typeof window.console.log||console.log.apply(console,arguments)},hasPreloadSupport:function(e){var n=document.createElement("link").relList;return!(!n||!n.supports)&&n.supports("preload")},listenTo:function(e,n){var t={"nb-jquery-loaded":function(){return"undefined"!=typeof jQuery},"nb-app-ready":function(){return void 0!==window.nb_&&nb_.wasListenedTo("nb-jquery-loaded")},"nb-jmp-parsed":function(){return"undefined"!=typeof jQuery&&void 0!==jQuery.fn.magnificPopup},"nb-main-swiper-parsed":function(){return void 0!==window.Swiper}},o=function(o){nb_.isUndefined(t[e])||!1!==t[e]()?(n(),nb_.eventsListenedTo.push(e)):nb_.errorLog("Nimble error => an event callback could not be fired because conditions not met => ",e,nb_.eventsListenedTo)};"function"==typeof n?nb_.wasEmitted(e)?o():document.addEventListener(e,o):nb_.errorLog("Nimble error => listenTo func param is not a function for event => ",e)},eventsEmitted:[],eventsListenedTo:[],emit:function(e){if(!nb_.wasEmitted(e)){var n=document.createEvent("Event");n.initEvent(e,!0,!0),document.dispatchEvent(n),nb_.eventsEmitted.push(e)}},wasListenedTo:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsListenedTo,e)},wasEmitted:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsEmitted,e)},isInScreen:function(e,n){var t=window.pageYOffset||document.documentElement.scrollTop,o=t+window.innerHeight,r=e.offsetTop,i=n||0;return r+e.clientHeight>=t-i&&r<=o+i},isCustomizing:function(){return!1},isLazyLoadEnabled:function(){return!nb_.isCustomizing()&&!1},preloadAsset:function(e){if(e=e||{},nb_.preloadedAssets=nb_.preloadedAssets||[],!nb_.inArray(nb_.preloadedAssets,e.id)){var n=document.getElementsByTagName("head")[0],t=document.createElement("link"),o=(e.as,function(){if("style"===e.as)this.setAttribute("rel","stylesheet");else{var t=document.createElement("script");t.setAttribute("src",e.href),t.setAttribute("id",e.id),nb_.hasPreloadSupport()||"script"!==e.as||t.setAttribute("defer","defer"),n.appendChild(t),this&&this.parentNode&&this.parentNode.removeChild(this)}});nb_.hasPreloadSupport()||"script"!==e.as?(t.setAttribute("href",e.href),t.setAttribute("rel",nb_.hasPreloadSupport()?"preload":"stylesheet"),t.setAttribute("id",e.id),t.setAttribute("as",e.as),t.onload=function(){this.onload=null,e.onEvent?nb_.listenTo(e.onEvent,function(){o.call(t)}):o.call(t)},t.onerror=function(){nb_.errorLog("Nimble preloadAsset error",er,e)}):e.onEvent?nb_.listenTo(e.onEvent,function(){o.call(t)}):o.call(t),n.appendChild(t),nb_.preloadedAssets.push(e.id),e.scriptEl&&e.scriptEl.parentNode&&e.scriptEl.parentNode.removeChild(e.scriptEl)}},revealBG:function(){this.getAttribute("data-sek-src")&&(this.setAttribute("style",'background-image:url("'+this.getAttribute("data-sek-src")+'")'),this.className+=" smartload-skip",this.querySelectorAll(".sek-css-loader").forEach(function(e){nb_.isObject(e)&&e.parentNode.removeChild(e)}))}},window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(e,n){n=n||window;for(var t=0;t<this.length;t++)e.call(n,this[t],t,this)}),nb_.listenTo("nb-docready",function(){var e=document.querySelectorAll("div.sek-has-bg");!nb_.isObject(e)||e.length<1||e.forEach(function(e){nb_.isObject(e)&&(!nb_.isLazyLoadEnabled()||nb_.isInScreen(e)&&nb_.isLazyLoadEnabled())&&nb_.revealBG.call(e)})}),"complete"===document.readyState||"loading"!==document.readyState&&!document.documentElement.doScroll)nb_.emit("nb-docready");else{var t=function(){nb_.wasEmitted("nb-docready")||nb_.emit("nb-docready")};document.addEventListener("DOMContentLoaded",t),window.addEventListener("load",t)}}(window,document);</script>
            <?php
        }




        //@'wp_head'10  when sek_load_jquery_async() && !skp_is_customizing()
        function sek_maybe_inject_jquery_migrate() {
            global $wp_scripts;
            if ( isset($wp_scripts->registered['jquery-migrate']) )
              return;
            ?>
            <script id="nb-load-jquery-migrate">!function(){var e=function(){var e=document.createElement("script");e.setAttribute("src","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),e.setAttribute("id","nb-query-migrate"),e.setAttribute("defer","defer"),document.getElementsByTagName("head")[0].appendChild(e);var t=document.getElementById("nb-load-jquery-migrate");t.parentNode.removeChild(t)};nb_.listenTo("nb-jquery-loaded",function(){if(nb_.hasPreloadSupport()){var t=document.createElement("link");t.setAttribute("href","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),t.setAttribute("rel","preload"),t.setAttribute("id","nb-query-migrate"),t.setAttribute("as","script"),t.onload=function(){this.onload=null,this.rel="script",e()},document.getElementsByTagName("head")[0].appendChild(t)}else e()})}();</script>
            <?php
        }



        //@'wp_head'PHP_INT_MAX
        function sek_print_style_for_css_loader() {
          // if ( !sek_is_img_smartload_enabled() || skp_is_customizing() )
          //   return;
          ?>
          <style id="nb-lazyload-css-loader">@-webkit-keyframes sek-mr-loader{0%{-webkit-transform:scale(.1);transform:scale(.1);opacity:1}70%{-webkit-transform:scale(1);transform:scale(1);opacity:.7}100%{opacity:0}}@keyframes sek-mr-loader{0%{-webkit-transform:scale(.1);transform:scale(.1);opacity:1}70%{-webkit-transform:scale(1);transform:scale(1);opacity:.7}100%{opacity:0}}.sek-css-loader{width:50px;height:50px;position:absolute;-webkit-transform:translate3d(-50%,-50%,0);transform:translate3d(-50%,-50%,0);top:50%;left:50%}.csstransforms3d .sek-css-loader{display:block}.sek-mr-loader>div:nth-child(0){-webkit-animation-delay:-.8s;animation-delay:-.8s}.sek-mr-loader>div:nth-child(1){-webkit-animation-delay:-.6s;animation-delay:-.6s}.sek-mr-loader>div:nth-child(2){-webkit-animation-delay:-.4s;animation-delay:-.4s}.sek-mr-loader>div:nth-child(3){-webkit-animation-delay:-.2s;animation-delay:-.2s}.sek-mr-loader>div{-webkit-animation-fill-mode:both;animation-fill-mode:both;position:absolute;top:0;left:0;width:100%;height:100%;border-radius:100%;border:2px solid #777;-webkit-animation:sek-mr-loader 1.25s 0s infinite cubic-bezier(.21,.53,.56,.8);animation:sek-mr-loader 1.25s 0s infinite cubic-bezier(.21,.53,.56,.8)}.white-loader>.sek-mr-loader>div{border:2px solid #fff}</style>
          <?php
        }


        // @'wp_head'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        // jQuery can potentially be loaded async, so let's react to its load or the presence of window.jQuery
        function sek_detect_jquery() {
            ?>
            <script id="nimble-detect-jquery">!function(){var e=function(){var e="nb-jquery-loaded";nb_.wasEmitted(e)||nb_.emit(e)},n=function(t){t=t||0,void 0!==window.jQuery?e():t<30?setTimeout(function(){n(++t)},200):alert("Nimble Builder problem : jQuery.js was not detected on your website")},t=document.getElementById("<?php echo NIMBLE_JQUERY_ID; ?>");t&&t.addEventListener("load",function(){e()}),n()}();</script>
            <?php
        }



        // // hook : wp_head@PHP_INT_MAX
        // // introduced in march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
        // function sek_maybe_print_inline_split_module_stylesheets() {
        //     $is_stylesheet_split_for_performance = !skp_is_customizing() && sek_use_split_stylesheets_on_front();
        //     $is_inline_stylesheets_for_performance= !skp_is_customizing() && $is_stylesheet_split_for_performance && sek_inline_module_stylesheets_on_front();
        //     if ( !$is_inline_stylesheets_for_performance)
        //       return;
        //     // css assets are always enqueued when customizing
        //     global $wp_filesystem;
        //     $contextually_active_modules = sek_get_collection_of_contextually_active_modules();
        //     // loop on the map module type (candidates for split) => stylesheet file name
        //     foreach (Nimble_Manager()->big_module_stylesheet_map as $module_type => $stylesheet_name ) {
        //         if ( !array_key_exists($module_type , $contextually_active_modules) )
        //           continue;
        //         $uri = NIMBLE_BASE_PATH . '/assets/front/css/modules/' . $stylesheet_name .'.min.css';
        //         //sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' SOOO ? => ' . $this->uri . $wp_filesystem->exists( $this->uri ), empty( $file_content ) );
        //         if ( $wp_filesystem->exists( $uri ) && $wp_filesystem->is_readable( $uri ) ) {
        //             $file_content = $wp_filesystem->get_contents( $uri );
        //             printf( '<style id="%1$s-stylesheet" type="text/css" media="all">%2$s</style>', $stylesheet_name, $file_content );
        //         }
        //     }
        // }




        //@wp_footer
        function sek_maybe_preload_front_scripts_and_styles() {
            if ( sek_load_front_assets_in_ajax() )
              return;
            /* ------------------------------------------------------------------------- *
             *  PRELOAD FRONT SCRIPT
            /* ------------------------------------------------------------------------- */
            $assets_urls = [
                'nb-magnific-popup' => sek_is_dev_mode() ? '/assets/front/js/libs/jquery-magnific-popup.js' : '/assets/front/js/libs/jquery-magnific-popup.min.js',
                'nb-swiper' => sek_is_dev_mode() ? '/assets/front/js/libs/swiper.js' : '/assets/front/js/libs/swiper.min.js',
                'nb-video-bg-plugin' => sek_is_dev_mode() ? '/assets/front/js/libs/nimble-video-bg.js' : '/assets/front/js/libs/nimble-video-bg.min.js',

                'nb-font-awesome' => '/assets/front/fonts/css/fontawesome-all.min.css',
                'nb-magnific-popup-style' => '/assets/front/css/libs/magnific-popup.min.css'
            ];

            // add version
            foreach( $assets_urls as $k => $path ) {
                $assets_urls[$k] = NIMBLE_BASE_URL .$path .'?'.NIMBLE_ASSETS_VERSION;
            }

            // when not customizing, sek_front_needs_font_awesome() sniffs if the collection include a module using an icon
            if ( sek_preload_front_scripts() ) {
                if ( sek_is_img_smartload_enabled() ) {
                  ?><script>nb_.emit('nb-needs-lazyload');</script><?php
                }

                ?>
                <script id="nb-load-front-script-and-styles">nb_.listenTo("nb-needs-magnific-popup",function(){nb_.preloadAsset({id:"nb-magnific-popup",as:"script",href:"<?php echo $assets_urls['nb-magnific-popup']; ?>",onEvent:"nb-docready"}),nb_.preloadAsset({id:"nb-magnific-popup-style",as:"style",href:"<?php echo $assets_urls['nb-magnific-popup-style']; ?>",onEvent:"nb-docready"})}),nb_.listenTo("nb-needs-swiper",function(){nb_.preloadAsset({id:"nb-swiper",as:"script",href:"<?php echo $assets_urls['nb-swiper']; ?>",onEvent:"nb-docready"})}),nb_.listenTo("nb-needs-videobg-js",function(){nb_.preloadAsset({id:"nb-video-bg-plugin",as:"script",href:"<?php echo $assets_urls['nb-video-bg-plugin']; ?>",onEvent:"nb-docready"})});</script>
                <?php
            }//sek_preload_front_scripts()

            // if active theme is Hueman or Customizr, Font Awesome may already been enqueued.
            // asset handle for Customizr => 'customizr-fa'
            // asset handle for Hueman => 'hueman-font-awesome'
            if ( sek_preload_font_awesome() && !wp_style_is('customizr-fa', 'enqueued') && !wp_style_is('hueman-font-awesome', 'enqueued') ) {
                // Font awesome is always loaded when customizing
                // when not customizing, sek_front_needs_font_awesome() sniffs if the collection include a module using an icon
                ?>
                <script id="nb-load-fa">
                  nb_.listenTo('nb-needs-fa', function() {
                      nb_.preloadAsset( {
                        id : 'nb-font-awesome',
                        as : 'style',
                        href : "<?php echo $assets_urls['nb-font-awesome']; ?>",
                        onEvent : 'nb-docready',
                        scriptEl : document.currentScript
                      });
                  });
                </script>
                <?php
            }
        }


        // @'wp_footer'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        function sek_preload_jquery_from_dns() {
            if( sek_is_jquery_replaced() && !skp_is_customizing() ) {
            ?>
            <script id="nb-load-jquery">setTimeout( function() {
                nb_.preloadAsset( {
                    id : '<?php echo NIMBLE_JQUERY_ID; ?>',
                    as : 'script',
                    href : '<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>',
                    scriptEl : document.currentScript
                });
            }, 0 );
            </script>
            <?php
            }
        }//sek_preload_jquery_from_dns()


        //@'wp_footer'PHP_INT_MAX
        function sek_maybe_load_scripts_in_ajax() {
            if ( ! sek_load_front_assets_in_ajax() )
              return;
            ?>
            <script id="nb-load-assets-dynamically">window,document,nb_.listenTo("nb-jquery-loaded",function(){nb_.scriptsLoadingStatus={},nb_.ajaxLoadScript=function(t){jQuery(function(a){t=a.extend({path:"",complete:"",loadcheck:!1},t),nb_.scriptsLoadingStatus[t.path]&&"pending"===nb_.scriptsLoadingStatus[t.path].state()||(nb_.scriptsLoadingStatus[t.path]=nb_.scriptsLoadingStatus[t.path]||a.Deferred(),jQuery.ajax({url:sekFrontLocalized.frontAssetsPath+t.path+"?"+sekFrontLocalized.assetVersion,cache:!0,dataType:"script"}).done(function(){"function"!=typeof t.loadcheck||t.loadcheck()?"function"==typeof t.complete&&t.complete():nb_.errorLog("ajaxLoadScript success but loadcheck failed for => "+t.path)}).fail(function(){nb_.errorLog("ajaxLoadScript failed for => "+t.path)}))})},jQuery(function(t){sekFrontLocalized.load_front_assets_on_scroll&&nb_.ajaxLoadScript({path:sekFrontLocalized.isDevMode?"js/ccat-nimble-front.js":"js/ccat-nimble-front.min.js"})})});</script>
            <?php
        }
    }//class
endif;
?><?php
if ( !class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets_Customizer_Preview extends SEK_Front_Assets {
        // Fired in __construct()
        function _schedule_preview_assets_printing() {
            add_action( 'wp_footer', array( $this, 'sek_customizr_js_stuff' ), PHP_INT_MAX  );

            // Load customize preview js
            add_action( 'customize_preview_init' , array( $this, 'sek_schedule_customize_preview_assets' ) );
        }//_schedule_preview_assets_printing

        // @'wp_footer'
        function sek_customizr_js_stuff() {
            if( !skp_is_customizing() )
              return;
            ?>
            <script id="nb-customizer-previewed-device-handler">(function(w, d){
      nb_.listenTo( 'nb-app-ready', function() {
          //PREVIEWED DEVICE ?
          //Listen to the customizer previewed device
          var _setPreviewedDevice = function() {
                wp.customize.preview.bind( 'previewed-device', function( device ) {
                      nb_.previewedDevice = device;// desktop, tablet, mobile
                });
          };
          if ( wp.customize.preview ) {
              _setPreviewedDevice();
          } else {
                wp.customize.bind( 'preview-ready', function() {
                      _setPreviewedDevice();
                });
          }
          // REVEAL BG IMAGE ON CHANGE ?
          jQuery( function($) {
              $('body').on( 'sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
                    var matches = document.querySelectorAll('div.sek-has-bg');
                    if ( !nb_.isObject( matches ) || matches.length < 1 )
                      return;

                    var imgSrc;
                    matches.forEach( function(el) {
                        if ( !nb_.isObject(el) )
                          return;

                        if ( nb_.isCustomizing() ) {
                            nb_.revealBG.call(el);
                        }
                    });
              });
          });
      });
}(window, document));</script>
            <?php
        }

        // enqueue / print customize preview assets
        // hook : 'customize_preview_init'
        function sek_schedule_customize_preview_assets() {
            // we don't need those assets when previewing a customize changeset
            // added when fixing https://github.com/presscustomizr/nimble-builder/issues/351
            if ( sek_is_customize_previewing_a_changeset_post() )
              return;

            // Load preview ui js tmpl
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                sprintf(
                    '%1$s/assets/czr/sek/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'sek-preview.css' : 'sek-preview.min.css'
                ),
                array( 'sek-base' ),
                NIMBLE_ASSETS_VERSION,
                'all'
            );

            // Communication between preview and customizer panel
            wp_enqueue_script(
                'sek-customize-preview',
                sprintf(
                    '%1$s/assets/czr/sek/js/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'ccat-sek-preview.js' : 'ccat-sek-preview.min.js'
                ),
                array( 'customize-preview', 'underscore'),
                NIMBLE_ASSETS_VERSION,
                true
            );

            wp_localize_script(
                'sek-customize-preview',
                'sekPreviewLocalized',
                array(
                    'i18n' => array(
                        "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_doma'),
                        "Moving elements between global and local sections is not allowed." => __( "Moving elements between global and local sections is not allowed.", 'text_doma'),
                        'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_doma'),
                        'Insert here' => __('Insert here', 'text_doma'),
                        'This content has been created with the WordPress editor.' => __('This content has been created with the WordPress editor.', 'text_domain' ),

                        'Insert a new section' => __('Insert a new section', 'text_doma' ),
                        '@location' => __('@location', 'text_domain_to_be'),
                        'Insert a new global section' => __('Insert a new global section', 'text_doma' ),

                        'section' => __('section', 'text_doma'),
                        'header section' => __('header section', 'text_doma'),
                        'footer section' => __('footer section', 'text_doma'),
                        '(global)' => __('(global)', 'text_doma'),
                        'nested section' => __('nested section', 'text_doma'),

                        'Shift-click to visit the link' => __('Shift-click to visit the link', 'text_doma'),
                        'External links are disabled when customizing' => __('External links are disabled when customizing', 'text_doma'),
                        'Link deactivated while previewing' => __('Link deactivated while previewing', 'text_doma')
                    ),
                    'isDevMode' => sek_is_dev_mode(),
                    'isPreviewUIDebugMode' => isset( $_GET['preview_ui_debug'] ) || NIMBLE_IS_PREVIEW_UI_DEBUG_MODE,
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),

                    'registeredModules' => CZR_Fmk_Base()->registered_modules,

                    // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
                    // september 2019
                    // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
                    // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
                    // when generating the ui, we check if the localized guid matches the one rendered server side
                    // otherwise the preview UI can be broken
                    'previewLevelGuid' => $this->sek_get_preview_level_guid()
                )
            );

            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_style(
                'ui-sortable',
                '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
                array(),
                null,//time(),
                $media = 'all'
            );
            wp_enqueue_script( 'jquery-ui-resizable' );

            // March 2020
            if ( sek_get_feedback_notif_status() ) {
                wp_enqueue_script(
                  'sek-confettis',
                  sprintf( '%1$s/assets/front/css/libs/confetti.browser.min.js', NIMBLE_BASE_URL ),
                  array(),
                  NIMBLE_ASSETS_VERSION,
                  true
                );
            }
        }


        //'wp_footer' in the preview frame
        function sek_print_ui_tmpl() {
            ?>
              <script type="text/html" id="sek-tmpl-add-content-button">
                  <# //console.log( 'data', data ); #>
                  <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                    <div class="sek-add-content-button-wrapper">
                     <# var hook_location = '', btn_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['Insert a new section'] : sekPreviewLocalized.i18n['Insert a new global section'], addContentBtnWidth = true !== data.is_global_location ? '83px' : '113px' #>
                      <# if ( data.location ) {
                          hook_location = ['(' , sekPreviewLocalized.i18n['@location'] , ':"',data.location , '")'].join('');
                      } #>
                      <button title="{{btn_title}} {{hook_location}}" data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:{{addContentBtnWidth}};">
                        <span class="sek-click-on-button-icon sek-click-on">+</span><span class="action-button-text">{{btn_title}}</span>
                      </button>
                    </div>
                  </div>
              </script>

              <?php
                  $icon_right_side_class = is_rtl() ? 'sek-dyn-left-icons' : 'sek-dyn-right-icons';
                  $icon_left_side_class = is_rtl() ? 'sek-dyn-right-icons' : 'sek-dyn-left-icons';
              ?>

              <script type="text/html" id="sek-dyn-ui-tmpl-section">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-dyn-ui-wrapper sek-section-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">

                        <?php if ( sek_is_dev_mode() ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <?php
                          // Code before implementing https://github.com/presscustomizr/nimble-builder/issues/521 :
                          /* <# if ( true !== data.is_first_section_in_parent ) { #>
                          <i data-sek-click-on="move-section-up" class="material-icons sek-click-on" title="<?php _e( 'Move section up', 'text_domain' ); ?>">keyboard_arrow_up</i>
                        <# } #>
                        <# if ( true !== data.is_last_section_in_parent ) { #>
                          <i data-sek-click-on="move-section-down" class="material-icons sek-click-on" title="<?php _e( 'Move section down', 'text_domain' ); ?>">keyboard_arrow_down</i>
                        <# } #>*/
                        ?>
                        <i data-sek-click-on="move-section-up" class="material-icons sek-click-on" title="<?php _e( 'Move section up', 'text_domain' ); ?>">keyboard_arrow_up</i>
                        <i data-sek-click-on="move-section-down" class="material-icons sek-click-on" title="<?php _e( 'Move section down', 'text_domain' ); ?>">keyboard_arrow_down</i>


                        <?php // if this is a nested section, it has the is_nested property set to true. We don't want to make it draggable for the moment. @todo ?>
                        <# if ( !data.is_nested ) { #>
                          <# if ( true !== data.is_global_location ) { #>
                            <i class="fas fa-arrows-alt sek-move-section" title="<?php _e( 'Drag section', 'text_domain' ); ?>"></i>
                           <# } #>
                        <# } #>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">tune</i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-click-on="add-column" class="material-icons sek-click-on" title="<?php _e( 'Add a column', 'text_domain' ); ?>">view_column</i>
                        <# } #>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate section', 'text_domain' ); ?>">filter_none</i>
                        <?php if ( defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) && NIMBLE_SAVED_SECTIONS_ENABLED ) : ?>
                          <i data-sek-click-on="toggle-save-section-ui" class="sek-save far fa-save" title="<?php _e( 'Save this section', 'text_domain' ); ?>"></i>
                        <?php endif; ?>
                        <i data-sek-click-on="pick-content" data-sek-content-type="module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove section', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <#
                          var section_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['section'] : sekPreviewLocalized.i18n['section (global)'];
                          var section_title = !data.is_nested ? sekPreviewLocalized.i18n['section'] : sekPreviewLocalized.i18n['nested section'];
                          if ( true === data.is_header_location && !data.is_nested ) {
                                section_title = sekPreviewLocalized.i18n['header section'];
                          } else if ( true === data.is_footer_location && !data.is_nested ) {
                                section_title = sekPreviewLocalized.i18n['footer section'];
                          }

                          section_title = true !== data.is_global_location ? section_title : [ section_title, sekPreviewLocalized.i18n['(global)'] ].join(' ');
                        #>
                        <div class="sek-dyn-ui-level-type">{{section_title}}</div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <?php
                    // when a column has nested section(s), its ui might be hidden by deeper columns.
                    // that's why a CSS class is added to position it on the top right corner, instead of bottom right
                    // @see https://github.com/presscustomizr/nimble-builder/issues/488
                  ?>
                  <# var has_nested_section_class = true === data.has_nested_section ? 'sek-col-has-nested-section' : ''; #>
                  <div class="sek-dyn-ui-wrapper sek-column-dyn-ui {{has_nested_section_class}}">
                    <div class="sek-dyn-ui-inner <?php echo $icon_right_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-column" title="<?php _e( 'Move column', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">tune</i>
                        <# if ( !data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-click-on="add-section" class="material-icons sek-click-on" title="<?php _e( 'Add a nested section', 'text_domain' ); ?>">account_balance_wallet</i>
                        <# } #>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate column', 'text_domain' ); ?>">filter_none</i>
                        <# } #>

                        <i data-sek-click-on="pick-content" data-sek-content-type="module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <# if ( !data.parent_is_single_column ) { #>
                          <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove column', 'text_domain' ); ?>">delete_forever</i>
                        <# } #>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type"><?php _e( 'column', 'text_domain' ); ?></div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-module">
                  <div class="sek-dyn-ui-wrapper sek-module-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-module" title="<?php _e( 'Move module', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-module" class="fas fa-pencil-alt sek-tip sek-click-on" title="<?php _e( 'Edit module content', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">tune</i>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate module', 'text_domain' ); ?>">filter_none</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove module', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <#
                      var module_name = !_.isEmpty( data.module_name ) ? data.module_name + ' ' + '<?php _e("module", "text_domain"); ?>' : '<?php _e("module", "text_domain"); ?>';
                    #>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-module" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type">{{module_name}}</div>
                      </div>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-wp-content">
                  <div class="sek-dyn-ui-wrapper sek-wp-content-dyn-ui">
                    <div class="sek-dyn-ui-inner">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-pencil-alt sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"></i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <span class="sek-dyn-ui-location-type" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <i class="fab fa-wordpress sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"> <?php _e( 'WordPress content', 'text_domain'); ?></i>
                    </span>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>
            <?php
        }
    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Render' ) ) :
    class SEK_Front_Render extends SEK_Front_Assets_Customizer_Preview {
        // Fired in __construct()
        function _schedule_front_rendering() {
            if ( !defined( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY", PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY", PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY" ) ) { define( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY", - PHP_INT_MAX ); }

            // Fires after 'wp' and before the 'get_header' template file is loaded.
            add_action( 'template_redirect', array( $this, 'sek_schedule_rendering_hooks') );

            // Encapsulate the singular post / page content so we can generate a dynamic ui around it when customizing
            add_filter( 'the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

            // SCHEDULE THE ASSETS ENQUEUING
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_the_printed_module_assets') );

            // SMART LOAD
            add_filter( 'nimble_parse_for_smart_load', array( $this, 'sek_maybe_process_img_for_js_smart_load') );

            // SETUP OUR the_content FILTER for the Tiny MCE module
            $this->sek_setup_tiny_mce_content_filters();

            // REGISTER HEADER AND FOOTER GLOBAL LOCATIONS
            add_action( 'nimble_front_classes_ready', array( $this, 'sek_register_nimble_global_locations') );

            // CONTENT : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
            add_filter( 'template_include', array( $this, 'sek_maybe_set_local_nimble_template' ) );

            // HEADER FOOTER
            // Header/footer, widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
            add_action( 'template_redirect', array( $this, 'sek_maybe_set_nimble_header_footer' ) );
            // HEADER : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
            add_filter( 'get_header', array( $this, 'sek_maybe_set_local_nimble_header') );
            // FOOTER : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
            add_filter( 'get_footer', array( $this, 'sek_maybe_set_local_nimble_footer') );

            // INCLUDE NIMBLE CONTENT IN SEARCH RESULTS
            add_action( 'wp_head', array( $this, 'sek_maybe_include_nimble_content_in_search_results' ) );
        }//_schedule_front_rendering()



        // Encapsulate the singular post / page content so we can generate a dynamic ui around it when customizing
        // @filter the_content::NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY
        function sek_wrap_wp_content( $html ) {
            if ( ! skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) )
              return $html;
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                global $post;
                // note : the edit url is printed as a data attribute to prevent being automatically parsed by wp when customizing and turned into a changeset url
                $html = sprintf( '<div class="sek-wp-content-wrapper" data-sek-wp-post-id="%1$s" data-sek-wp-edit-link="%2$s" title="%3$s">%4$s</div>',
                      $post->ID,
                      // we can't rely on the get_edit_post_link() function when customizing because emptied by wp core
                      $this->get_unfiltered_edit_post_link( $post->ID ),
                      __( 'WordPress content', 'text_domain'),
                      wpautop( $html )
                );
            }
            return $html;
        }


        // Fired in the constructor
        function sek_register_nimble_global_locations() {
            register_location('nimble_local_header', array( 'is_header_location' => true ) );
            register_location('nimble_local_footer', array( 'is_footer_location' => true ) );
            register_location('nimble_global_header', array( 'is_global_location' => true, 'is_header_location' => true ) );
            register_location('nimble_global_footer', array( 'is_global_location' => true, 'is_footer_location' => true ) );
        }

        // @template_redirect
        // When using the default theme template, let's schedule the default hooks rendering
        // When using the Nimble template, this is done with render_content_sections_for_nimble_template();
        function sek_schedule_rendering_hooks() {
            $locale_template = sek_get_locale_template();
            // cache all locations now
            $all_locations = sek_get_locations();

            // $default_locations = [
            //     'loop_start' => array( 'priority' => 10 ),
            //     'before_content' => array(),
            //     'after_content' => array(),
            //     'loop_end' => array( 'priority' => 10 ),
            // ]
            // SCHEDULE THE ACTIONS ON HOOKS AND CONTENT FILTERS
            foreach( $all_locations as $location_id => $params ) {
                $params = is_array( $params ) ? $params : array();
                $params = wp_parse_args( $params, array( 'priority' => 10 ) );

                // When a local template is used, the default locations are rendered with :
                // render_nimble_locations(
                //     array_keys( Nimble_Manager()->default_locations ),//array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
                // );
                // @see nimble tmpl/ template files
                // That's why we don't need to add the rendering actions for the default locations. We only need to add action for the possible locations registered on the theme hooks
                if ( !empty( $locale_template ) && !array_key_exists( $location_id, Nimble_Manager()->default_locations ) ) {
                    add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                } else {
                    switch ( $location_id ) {
                        case 'loop_start' :
                        case 'loop_end' :
                            // Do not add loop_start, loop_end action hooks when in a jetpack's like "infinite scroll" query
                            // see: https://github.com/presscustomizr/nimble-builder/issues/228
                            // the filter 'infinite_scroll_got_infinity' is documented both in jetpack's infinite module
                            // and in Customizr-Pro/Hueman-Pro infinite scroll code. They both use the same $_GET var too.
                            // Actually this is not needed anymore for our themes, see:
                            // https://github.com/presscustomizr/nimble-builder/issues/228#issuecomment-449362111
                            if ( ! ( apply_filters( 'infinite_scroll_got_infinity', isset( $_GET[ 'infinity' ] ) ) ) ) {
                                add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                            }
                        break;
                        case 'before_content' :
                            add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                        break;
                        case 'after_content' :
                            add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );
                        break;
                        // Default is typically used for custom locations
                        default :
                            add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                        break;
                    }
                }

            }
        }



        // hook : loop_start, loop_end, and all custom locations like __before_main_wrapper, __after_header or __before_footer in the Customizr theme.
        // @return void()
        function sek_schedule_sektions_rendering( $query = null ) {
            // Check if the passed query is the main_query, bail if not
            // fixes: https://github.com/presscustomizr/nimble-builder/issues/154 2.
            // Note: a check using $query instanceof WP_Query would return false here, probably because the
            // query object is passed by reference
            // accidentally this would also fix the same point 1. of the same issue if the 'sek_schedule_rendering_hooks' method will be fired
            // with an early hook (earlier than wp_head).
            if ( is_object( $query ) && is_a( $query, 'WP_Query' ) && ! $query->is_main_query() ) {
                return;
            }

            $location_id = current_filter();
            // why check if did_action( ... ) ?
            //  => A location can be rendered only once
            // => for loop_start and loop_end, checking with is_main_query() is not enough because the main loop might be used 2 times in the same page
            // => for a custom location, it can be rendered by do_action() somewhere, and be rendered also with render_nimble_locations()
            // @see issue with Twenty Seventeen here : https://github.com/presscustomizr/nimble-builder/issues/14
            if ( did_action( "sek_before_location_{$location_id}" ) )
              return;

            do_action( "sek_before_location_{$location_id}" );
            $this->_render_seks_for_location( $location_id );
            do_action( "sek_after_location_{$location_id}" );
        }

        // hook : 'the_content'::-9999
        function sek_schedule_sektion_rendering_before_content( $html ) {
            // Disable because https://github.com/presscustomizr/nimble-builder/issues/380
            // No regression ?

            // if ( did_action( 'sek_before_location_before_content' ) )
            //   return $html;

            do_action( 'sek_before_location_before_content' );
            return $this->_filter_the_content( $html, 'before_content' );
        }

        // hook : 'the_content'::9999
        function sek_schedule_sektion_rendering_after_content( $html ) {
            // Disable because https://github.com/presscustomizr/nimble-builder/issues/380
            // No regression ?

            // if ( did_action( 'sek_before_location_after_content' ) )
            //   return $html;

            do_action( 'sek_before_location_after_content' );
            return $this->_filter_the_content( $html, 'after_content' );
        }

        private function _filter_the_content( $html, $where ) {
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                ob_start();
                $this->_render_seks_for_location( $where );
                $html = 'before_content' == $where ? ob_get_clean() . $html : $html . ob_get_clean();
                // Collapse line breaks before and after <div> elements so they don't get autop'd.
                // @see function wpautop() in wp-includes\formatting.php
                // @fixes https://github.com/presscustomizr/nimble-builder/issues/32
                if ( strpos( $html, '<div' ) !== false ) {
                  $html = preg_replace( '|\s*<div|', '<div', $html );
                  $html = preg_replace( '|</div>\s*|', '</div>', $html );
                }
            }

            return $html;
        }

        // the $location_data can be provided. Typically when using the function render_content_sections_for_nimble_template in the Nimble page template.
        public function _render_seks_for_location( $location_id = '', $location_data = array() ) {
            $all_locations = sek_get_locations();

            if ( ! array_key_exists( $location_id, $all_locations ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location_id . ' is not registered in sek_get_locations()');
                return;
            }
            $locationSettingValue = array();
            $is_global_location = sek_is_global_location( $location_id );
            if ( empty( $location_data ) ) {
                $skope_id = $is_global_location ? NIMBLE_GLOBAL_SKOPE_ID : skp_build_skope_id();
                $locationSettingValue = sek_get_skoped_seks( $skope_id, $location_id );
            } else {
                $locationSettingValue = $location_data;
            }
            if ( is_array( $locationSettingValue ) ) {

                remove_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );
                // sek_error_log( 'LEVEL MODEL IN ::sek_schedule_sektions_rendering()', $locationSettingValue);
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                $this->render( $locationSettingValue, $location_id );

                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ),NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                add_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

                // inform Nimble Builder that a global section has been rendered
                // introduced for https://github.com/presscustomizr/nimble-builder/issues/456
                if ( $is_global_location ) {
                    Nimble_Manager()->global_sections_rendered = true;
                }

            } else {
                error_log( __CLASS__ . ' :: ' . __FUNCTION__ .' => sek_get_skoped_seks() should always return an array().');
            }
        }






        /* ------------------------------------------------------------------------- *
         * RENDERING UTILITIES USED IN NIMBLE TEMPLATES
        /* ------------------------------------------------------------------------- */
        // @return void()
        // @param $locations. mixed type
        // @param $options (array)
        // Note that a location can be rendered only once in a given page.
        // That's why we need to check if did_action(''), like in ::sek_schedule_sektions_rendering
        function render_nimble_locations( $locations, $options = array() ) {
            if ( is_string( $locations ) && ! empty( $locations ) ) {
                $locations = array( $locations );
            }
            if ( ! is_array( $locations ) ) {
                sek_error_log( __FUNCTION__ . ' error => missing or invalid locations provided');
                return;
            }

            // Normalize the $options
            $options = ! is_array( $options ) ? array() : $options;
            $options = wp_parse_args( $options, array(
                // fallback_location => the location rendered even if empty.
                // This way, the user starts customizing with only one location for the content instead of four
                // But if the other locations were already customized, they will be printed.
                'fallback_location' => null, // Typically set as 'loop_start' in the nimble templates
            ));

            //$is_global = sek_is_global_location( $location_id );
            // $skope_id = skp_get_skope_id();
            // $skopeLocationCollection = array();
            // $skopeSettingValue = sek_get_skoped_seks( $skope_id );
            // if ( is_array( ) && array_key_exists('collection', search) ) {
            //     $skopeLocationCollection = $skopeSettingValue['collection'];
            // }

            //sek_error_log( __FUNCTION__ . ' sek_get_skoped_seks(  ', sek_get_skoped_seks() );

            foreach( $locations as $location_id ) {
                if ( ! is_string( $location_id ) || empty( $location_id ) ) {
                    sek_error_log( __FUNCTION__ . ' => error => a location_id is not valid in the provided locations', $locations );
                    continue;
                }

                // why check if did_action( ... ) ?
                // => A location can be rendered only once
                // => for loop_start and loop_end, checking with is_main_query() is not enough because the main loop might be used 2 times in the same page
                // => for a custom location, it can be rendered by do_action() somewhere, and be rendered also with render_nimble_locations()
                // @see issue with Twenty Seventeen here : https://github.com/presscustomizr/nimble-builder/issues/14
                if ( did_action( "sek_before_location_{$location_id}" ) )
                  continue;

                $is_global = sek_is_global_location( $location_id );
                $skope_id = $is_global ? NIMBLE_GLOBAL_SKOPE_ID : skp_get_skope_id();
                $locationSettingValue = sek_get_skoped_seks( $skope_id, $location_id );
                //sek_error_log('$locationSettingValue ??? => ' . $location_id, $locationSettingValue );
                if ( ! is_null( $options[ 'fallback_location' ]) ) {
                    // We don't need to render the locations with no sections
                    // But we need at least one location : let's always render loop_start.
                    // => so if the user switches from the nimble_template to the default theme one, the loop_start section will always be rendered.
                    if ( $options[ 'fallback_location' ] === $location_id || ( is_array( $locationSettingValue ) && ! empty( $locationSettingValue['collection'] ) ) ) {
                        do_action( "sek_before_location_{$location_id}" );
                        Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                        do_action( "sek_after_location_{$location_id}" );
                    }
                } else {
                    do_action( "sek_before_location_{$location_id}" );
                    Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                    do_action( "sek_after_location_{$location_id}" );
                }

            }//render_nimble_locations()
        }







        /* ------------------------------------------------------------------------- *
         *  MAIN RENDERING METHOD
        /* ------------------------------------------------------------------------- */
        // Walk a model tree recursively and render each level with a specific template
        function render( $model = array(), $location = 'loop_start' ) {
            //sek_error_log('LOCATIONS IN ::render()', sek_get_locations() );
            //sek_error_log('LEVEL MODEL IN ::RENDER()', $model );
            // Is it the root level ?
            // The root level has no id and no level entry
            if ( ! is_array( $model ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a model must be an array', $model );
                return;
            }
            if ( ! array_key_exists( 'level', $model ) || ! array_key_exists( 'id', $model ) ) {
                error_log( '::render() => a level model is missing the level or the id property' );
                return;
            }
            // The level "id" is a string not empty
            $id = $model['id'];
            if ( ! is_string( $id ) || empty( $id ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a level id must be a string not empty', $model );
                return;
            }

            // The level "level" can take 4 values : location, section, column, module
            $level_type = $model['level'];
            if ( ! is_string( $level_type ) || empty( $level_type ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a level type must be a string not empty', $model );
                return;
            }

            // A level id can be rendered only once by the recursive ::render method
            if ( in_array( $id, Nimble_Manager()->rendered_levels ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a ' . $level_type . ' level id has already been rendered : ' . $id );
                return;
            }
            // Record the rendered id now
            Nimble_Manager()->rendered_levels[] = $id;

            // Cache the parent model
            // => used when calculating the width of the column to be added
            $parent_model = $this->parent_model;
            $this->model = $model;

            $collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();

            //sek_error_log( __FUNCTION__ . ' WHAT ARE WE RENDERING? ' . $id, current_filter() . ' | ' . current_action() );
            $custom_anchor = null;
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                    $custom_anchor = esc_attr( $model[ 'options' ][ 'anchor' ]['custom_anchor'] );
                }
            }
            $custom_css_classes = null;
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] ) ) {
                if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] ) ) {
                    $custom_css_classes = esc_attr( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] );
                    //$custom_css_classes = preg_replace("/[^0-9a-zA-Z]/","", $custom_css_classes);
                }
            }

            switch ( $level_type ) {
                /********************************************************
                 LOCATIONS
                ********************************************************/
                case 'location' :
                    //sek_error_log( __FUNCTION__ . ' WHAT ARE WE RENDERING? ' . $id , $collection );
                    //empty sektions wrapper are only printed when customizing
                    ?>
                      <?php if ( skp_is_customizing() || ( ! skp_is_customizing() && ! empty( $collection ) ) ) : ?>
                            <?php
                              $is_header_location = true === sek_get_registered_location_property( $id, 'is_header_location' );
                              $is_footer_location = true === sek_get_registered_location_property( $id, 'is_footer_location' );
                              printf( '<div class="sektion-wrapper" data-sek-level="location" data-sek-id="%1$s" %2$s %3$s %4$s %5$s>',
                                  $id,
                                  sprintf('data-sek-is-global-location="%1$s"', sek_is_global_location( $id ) ? 'true' : 'false'),
                                  $is_header_location ? 'data-sek-is-header-location="true"' : '',
                                  $is_footer_location ? 'data-sek-is-footer-location="true"' : '',
                                  $this->sek_maybe_print_preview_level_guid_html()//<= added for #494
                              );
                            ?>
                            <?php
                              $this->parent_model = $model;
                              foreach ( $collection as $_key => $sec_model ) { $this->render( $sec_model ); }
                            ?>
                            <?php
                              // empty global locations placeholders are only printed when customizing But not previewing a changeset post
                              // since https://github.com/presscustomizr/nimble-builder/issues/351
                            ?>
                            <?php if ( empty( $collection ) && !sek_is_customize_previewing_a_changeset_post() ) : ?>
                                <div class="sek-empty-location-placeholder">
                                  <?php
                                    if ( $is_header_location || $is_footer_location ) {
                                        printf('<span class="sek-header-footer-location-placeholder">%1$s %2$s</span>',
                                            sprintf( '<span class="sek-nimble-icon"><img src="%1$s"/></span>',
                                                NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION
                                            ),
                                            $is_header_location ? __('Start designing the header', 'text_doma') : __('Start designing the footer', 'text_doma')
                                        );
                                    }
                                  ?>
                                </div>
                            <?php endif; ?>
                          </div><?php //class="sektion-wrapper" ?>
                      <?php endif; ?>
                    <?php
                break;


                /********************************************************
                 SECTIONS
                ********************************************************/
                case 'section' :
                    $is_nested = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
                    $has_at_least_one_module = sek_section_has_modules( $collection );
                    $column_container_class = 'sek-container-fluid';
                    //when boxed use proper container class
                    if ( !empty( $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) {
                        $column_container_class = 'sek-container';
                    }
                    // if there's a video background or a parallax bg we need to inform js api
                    $bg_attributes = $this->sek_maybe_add_bg_attributes( $model );

                    // if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
                    $has_bg_img = false;
                    if ( false !== strpos( $bg_attributes, 'data-sek-src="http') ) {
                        $has_bg_img = true;
                    }
                    printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section %3$s %4$s %5$s %6$s" %7$s %8$s %9$s>%10$s',
                        $id,
                        $is_nested ? 'data-sek-is-nested="true"' : '',
                        $has_at_least_one_module ? 'sek-has-modules' : '',
                        $this->get_level_visibility_css_class( $model ),
                        $has_bg_img ? 'sek-has-bg' : '',
                        is_null( $custom_css_classes ) ? '' : $custom_css_classes,

                        is_null( $custom_anchor ) ? '' : 'id="' . ltrim( $custom_anchor , '#' ) . '"',// make sure we clean the hash if user left it
                        // add smartload + parallax attributes
                        $bg_attributes,

                        $this->sek_maybe_print_preview_level_guid_html(),//<= added for #494
                        ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? Nimble_Manager()->css_loader_html : ''
                    );
                    if ( false !== strpos($bg_attributes, 'data-sek-video-bg-src') ) {
                      ?><script>nb_.emit('nb-needs-videobg-js');</script><?php
                    }
                    if ( false !== strpos($bg_attributes, 'data-sek-bg-parallax="true"') ) {
                      ?><script>nb_.emit('nb-needs-parallax');</script><?php
                    }
                    ?>

                          <div class="<?php echo $column_container_class; ?>">
                            <div class="sek-row sek-sektion-inner">
                                <?php
                                  // Set the parent model now
                                  $this->parent_model = $model;
                                  foreach ( $collection as $col_model ) {$this->render( $col_model ); }
                                ?>
                            </div>
                          </div>
                      </div><?php //data-sek-level="section" ?>
                    <?php
                break;


                /********************************************************
                 COLUMNS
                ********************************************************/
                case 'column' :
                    // if ( defined('DOING_AJAX') && DOING_AJAX ) {
                    //     error_log( print_r( $parent_model, true ) );
                    // }
                    // sek_error_log( 'PARENT MODEL WHEN RENDERING', $parent_model );

                    // SETUP THE DEFAULT CSS CLASS
                    // Note : the css rules for custom width are generated in Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
                    $col_number = ( array_key_exists( 'collection', $parent_model ) && is_array( $parent_model['collection'] ) ) ? count( $parent_model['collection'] ) : 1;
                    $col_number = 12 < $col_number ? 12 : $col_number;
                    $col_width_in_percent = 100/$col_number;

                    //@note : we use the same logic in the customizer preview js to compute the column css classes when dragging them
                    //@see sek_preview::makeColumnsSortableInSektion
                    //TODO, we might want to be sure the $col_suffix is related to an allowed size
                    $col_suffix = floor( $col_width_in_percent );

                    // SETUP THE GLOBAL CUSTOM BREAKPOINT CSS CLASS
                    $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );

                    // SETUP THE LEVEL CUSTOM BREAKPOINT CSS CLASS
                    // nested section should inherit the custom breakpoint of the parent
                    // @fixes https://github.com/presscustomizr/nimble-builder/issues/554

                    // the 'for_responsive_columns' param has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
                    // so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
                    // when for columns, we always apply the custom breakpoint defined by the user
                    // otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
                    // 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
                    $section_custom_breakpoint =  sek_get_closest_section_custom_breakpoint( array(
                        'searched_level_id' => $parent_model['id'],
                        'for_responsive_columns' => true
                    ));

                    $grid_column_class = "sek-col-{$col_suffix}";
                    if ( $section_custom_breakpoint >= 1 ) {
                        $grid_column_class = "sek-section-custom-breakpoint-col-{$col_suffix}";
                    } else if ( $global_custom_breakpoint >= 1 ) {
                        $grid_column_class = "sek-global-custom-breakpoint-col-{$col_suffix}";
                    }
                    $bg_attributes = $this->sek_maybe_add_bg_attributes( $model );

                    // if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
                    $has_bg_img = false;
                    if ( false !== strpos( $bg_attributes, 'data-sek-src="http') ) {
                        $has_bg_img = true;
                    }
                    printf('<div data-sek-level="column" data-sek-id="%1$s" class="sek-column sek-col-base %2$s %3$s %4$s %5$s" %6$s %7$s %8$s %9$s>%10$s',
                        $id,
                        $grid_column_class,
                        $this->get_level_visibility_css_class( $model ),
                        $has_bg_img ? 'sek-has-bg' : '',
                        is_null( $custom_css_classes ) ? '' : $custom_css_classes,

                        empty( $collection ) ? 'data-sek-no-modules="true"' : '',
                        // add smartload + parallax attributes
                        $bg_attributes,
                        is_null( $custom_anchor ) ? '' : 'id="' . $custom_anchor . '"',

                        $this->sek_maybe_print_preview_level_guid_html(),//<= added for #494
                        ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? Nimble_Manager()->css_loader_html : ''
                    );
                    if ( false !== strpos($bg_attributes, 'data-sek-video-bg-src') ) {
                      ?><script>nb_.emit('nb-needs-videobg-js');</script><?php
                    }
                    if ( false !== strpos($bg_attributes, 'data-sek-bg-parallax="true"') ) {
                      ?><script>nb_.emit('nb-needs-parallax');</script><?php
                    }
                      ?>
                        <?php
                        // Drop zone : if no modules, the drop zone is wrapped in sek-no-modules-columns
                        // if at least one module, the sek-drop-zone is the .sek-column-inner wrapper
                        ?>
                        <div class="sek-column-inner <?php echo empty( $collection ) ? 'sek-empty-col' : ''; ?>">
                            <?php
                              // the drop zone is inserted when customizing but not when previewing a changeset post
                              // since https://github.com/presscustomizr/nimble-builder/issues/351
                              if ( skp_is_customizing() && !sek_is_customize_previewing_a_changeset_post() && empty( $collection ) ) {
                                  //$content_type = 1 === $col_number ? 'section' : 'module';
                                  $content_type = 'module';
                                  $title = 'section' === $content_type ? __('Drag and drop a section or a module here', 'text_doma' ) : __('Drag and drop a block of content here', 'text_doma' );
                                  ?>
                                  <div class="sek-no-modules-column">
                                    <div class="sek-module-drop-zone-for-first-module sek-content-module-drop-zone sek-drop-zone">
                                      <i data-sek-click-on="pick-content" data-sek-content-type="<?php echo $content_type; ?>" class="material-icons sek-click-on" title="<?php echo $title; ?>">add_circle_outline</i>
                                      <span class="sek-injection-instructions"><?php _e('Drag and drop or double-click the content that you want to insert here.', 'text_domain_to_rep'); ?></span>
                                    </div>
                                  </div>
                                  <?php
                              } else {
                                  // Set the parent model now
                                  $this->parent_model = $model;
                                  foreach ( $collection as $module_or_nested_section_model ) {
                                      ?>
                                      <?php
                                      $this->render( $module_or_nested_section_model );
                                  }
                                  ?>
                                  <?php
                              }
                            ?>
                        </div>
                      </div><?php //data-sek-level="column" ?>
                    <?php
                break;


                /********************************************************
                 MODULES
                ********************************************************/
                case 'module' :
                    if ( empty( $model['module_type'] ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing module_type for a module', $model );
                        break;
                    }

                    $module_type = $model['module_type'];

                    if ( ! CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => module_type not registered', $module_type );
                        break;
                    }

                    $model = sek_normalize_module_value_with_defaults( $model );
                    // update the current cached model
                    $this->model = $model;
                    $title_attribute = '';
                    if ( skp_is_customizing() ) {
                        $title_attribute = __('Edit module settings', 'text-domain');
                        $title_attribute = 'title="'.$title_attribute.'"';
                    }

                    // SETUP MODULE TEMPLATE PATH
                    // introduced for #532, october 2019
                    // Default tmpl path looks like : NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
                    //
                    // Important note :
                    // @fixes https://github.com/presscustomizr/nimble-builder/issues/537
                    // since #532, module registered in Nimble Builder core have a render_tmpl_path property looking like 'render_tmpl_path' => "simple_html_module_tmpl.php",
                    // But if a developer wants to register a custom module with a specific template path, it is still possible by using a full path
                    // 1) We first check if the file exists, if it is a full path this will return TRUE and the render tmpl path will be set this way
                    // , for example, we use a custom gif module on presscustomizr.com, for which the render_tmpl_path is a full path:
                    // 'render_tmpl_path' => TC_BASE_CHILD . "inc/nimble-modules/modules-registration/tmpl/modules/gif_image_module_tmpl.php",
                    // 2) then we check if there's an override
                    // 3) finally we use the default Nimble Builder path

                    // render_tmpl_path can be
                    // 1) simple_html_module_tmpl.php <= most common case, the module is registered by Nimble Builder
                    // 2) srv/www/pc-dev/htdocs/wp-content/themes/tc/inc/nimble-modules/modules-registration/tmpl/modules/gif_image_module_tmpl.php <= case of a custom module
                    $template_name_or_path = sek_get_registered_module_type_property( $module_type, 'render_tmpl_path' );

                    $template_name = basename( $template_name_or_path );
                    $template_name = ltrim( $template_name_or_path, '/' );

                    if ( file_exists( $template_name_or_path ) ) {
                        $template_path = $template_name_or_path;
                    } else {
                        $template_path = sek_get_templates_dir() . "/modules/{$template_name}";
                    }

                    // make this filtrable
                    $render_tmpl_path = apply_filters( 'nimble_module_tmpl_path', $template_path, $module_type );

                    // Then check if there's an override
                    $overriden_template_path = $this->sek_maybe_get_overriden_template_path_for_module( $template_name );

                    $is_module_template_overriden = false;
                    if ( !empty( $overriden_template_path ) ) {
                        $render_tmpl_path = $overriden_template_path;
                        $is_module_template_overriden = true;
                    }
                    // if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
                    $bg_attributes = $this->sek_maybe_add_bg_attributes( $model );
                    $has_bg_img = false;
                    if ( false !== strpos( $bg_attributes, 'data-sek-src="http') ) {
                        $has_bg_img = true;
                    }
                    if ( false !== strpos($bg_attributes, 'data-sek-bg-parallax="true"') ) {
                      ?><script>nb_.emit('nb-needs-parallax');</script><?php
                    }

                    printf('<div data-sek-level="module" data-sek-id="%1$s" data-sek-module-type="%2$s" class="sek-module %3$s %4$s" %5$s %6$s %7$s %8$s %9$s %10$s>%11$s',
                        $id,
                        $module_type,
                        $this->get_level_visibility_css_class( $model ),
                        $has_bg_img ? 'sek-has-bg' : '',
                        is_null( $custom_css_classes ) ? '' : $custom_css_classes,

                        $title_attribute,
                        // add smartload + parallax attributes
                        $bg_attributes,
                        is_null( $custom_anchor ) ? '' : 'id="' . $custom_anchor . '"',

                        $this->sek_maybe_print_preview_level_guid_html(), //<= added for #494
                        $is_module_template_overriden ? 'data-sek-module-template-overriden="true"': '',// <= added for #532
                        ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? Nimble_Manager()->css_loader_html : ''
                    );
                      ?>
                        <div class="sek-module-inner">
                          <?php
                            if ( !empty( $render_tmpl_path ) && file_exists( $render_tmpl_path ) ) {
                                load_template( $render_tmpl_path, false );
                            } else {
                                error_log( __FUNCTION__ . ' => no template found for module type ' . $module_type  );
                            }
                          ?>
                        </div>
                    </div><?php //data-sek-level="module" ?>
                    <?php
                break;

                default :
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' error => a level is invalid : ' . $level_type  );
                break;
            }

            $this->parent_model = $parent_model;
        }//render









        /* ------------------------------------------------------------------------- *
         * VARIOUS HELPERS
        /* ------------------------------------------------------------------------- */
        /* HELPER TO PRINT THE VISIBILITY CSS CLASS IN THE LEVEL CONTAINER */
        // Dec 2019 : since issue https://github.com/presscustomizr/nimble-builder/issues/555, we use a dynamic CSS rule generation instead of static CSS
        // The CSS class are kept only for information when inspecting the markup
        // @see sek_add_css_rules_for_level_visibility()
        // @return string
        private function get_level_visibility_css_class( $model ) {
            if ( ! is_array( $model ) ) {
                error_log( __FUNCTION__ . ' => $model param should be an array' );
                return;
            }
            $visibility_class = '';
            //when boxed use proper container class
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'visibility' ] ) ) {
                if ( is_array( $model[ 'options' ][ 'visibility' ] ) ) {
                    foreach ( $model[ 'options' ][ 'visibility' ] as $device_type => $device_visibility_bool ) {
                        if ( true !== sek_booleanize_checkbox_val( $device_visibility_bool ) ) {
                            $visibility_class .= " sek-hidden-on-{$device_type}";
                        }
                    }
                }
            }
            return $visibility_class;
        }





        /* MODULE AND PLACEHOLDER */
        // module templates can be overriden from a child theme when located in nimble_templates/modules/{template_name}.php
        // for example /wp-content/themes/twenty-nineteen-child/nimble_templates/modules/image_module_tmpl.php
        // added for #532, october 2019
        private function sek_maybe_get_overriden_template_path_for_module( $template_name = '') {
            if ( empty( $template_name ) )
              return;
            $overriden_template_path = '';
            // try locating this template file by looping through the template paths
            // inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
            foreach( sek_get_theme_template_base_paths() as $path_candidate ) {
              if( file_exists( $path_candidate . 'modules/' . $template_name ) ) {
                $overriden_template_path = $path_candidate . 'modules/' . $template_name;
                break;
              }
            }

            return $overriden_template_path;
        }


        // march 2020 : not used anymore
        function sek_get_input_placeholder_content( $input_type = '', $input_id = '' ) {
            $ph = '<i class="material-icons">pan_tool</i>';
            switch( $input_type ) {
                case 'detached_tinymce_editor' :
                case 'nimble_tinymce_editor' :
                case 'text' :
                  $ph = skp_is_customizing() ? '<div class="sek-tiny-mce-module-placeholder-text">' . __('Click to edit', 'here') .'</div>' : '';
                break;
                case 'upload' :
                  $ph = '<i class="material-icons">image</i>';
                break;
            }
            switch( $input_id ) {
                case 'html_content' :
                  $ph = skp_is_customizing() ? sprintf('<pre>%1$s<br/>%2$s</pre>', __('Html code goes here', 'text-domain'), __('Click to edit', 'here') ) : '';
                break;
            }
            if ( skp_is_customizing() ) {
                return sprintf('<div class="sek-module-placeholder" title="%4$s" data-sek-input-type="%1$s" data-sek-input-id="%2$s">%3$s</div>', $input_type, $input_id, $ph, __('Click to edit', 'here') );
            } else {
                return $ph;
            }
        }



        /**
         * unfiltered version of get_edit_post_link() located in wp-includes/link-template.php
         * ( filtered by wp core when invoked in customize-preview )
         */
        function get_unfiltered_edit_post_link( $id = 0, $context = 'display' ) {
            if ( ! $post = get_post( $id ) )
              return;

            if ( 'revision' === $post->post_type )
              $action = '';
            elseif ( 'display' == $context )
              $action = '&amp;action=edit';
            else
              $action = '&action=edit';

            $post_type_object = get_post_type_object( $post->post_type );
            if ( !$post_type_object )
              return;

            if ( !current_user_can( 'edit_post', $post->ID ) )
              return;

            if ( $post_type_object->_edit_link ) {
              $link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
            } else {
              $link = '';
            }
            return $link;
        }



        // @hook wp_enqueue_scripts
        function sek_enqueue_the_printed_module_assets() {
            $skope_id = skp_get_skope_id();
            $skoped_seks = sek_get_skoped_seks( $skope_id );

            if ( ! is_array( $skoped_seks ) || empty( $skoped_seks['collection'] ) )
              return;

            $enqueueing_candidates = $this->sek_sniff_assets_to_enqueue( $skoped_seks['collection'] );

            foreach ( $enqueueing_candidates as $handle => $asset_params ) {
                if ( empty( $asset_params['type'] ) ) {
                    sek_error_log( __FUNCTION__ . ' => missing asset type', $asset_params );
                    continue;
                }
                switch ( $asset_params['type'] ) {
                    case 'css' :
                        wp_enqueue_style(
                            $handle,
                            array_key_exists( 'src', $asset_params ) ? $asset_params['src'] : null,
                            array_key_exists( 'deps', $asset_params ) ? $asset_params['deps'] : array(),
                            NIMBLE_ASSETS_VERSION,
                            'all'
                        );
                    break;
                    case 'js' :
                        wp_enqueue_script(
                            $handle,
                            array_key_exists( 'src', $asset_params ) ? $asset_params['src'] : null,
                            array_key_exists( 'deps', $asset_params ) ? $asset_params['deps'] : null,
                            array_key_exists( 'ver', $asset_params ) ? $asset_params['ver'] : null,
                            array_key_exists( 'in_footer', $asset_params ) ? $asset_params['in_footer'] : false
                        );
                    break;
                }
            }
        }//sek_enqueue_the_printed_module_assets()

        // @hook sek_sniff_assets_to_enqueue
        function sek_sniff_assets_to_enqueue( $collection, $enqueuing_candidates = array() ) {
            foreach ( $collection as $level_data ) {
                if ( array_key_exists( 'level', $level_data ) && 'module' === $level_data['level'] && ! empty( $level_data['module_type'] ) ) {
                    $front_assets = sek_get_registered_module_type_property( $level_data['module_type'], 'front_assets' );
                    if ( is_array( $front_assets ) ) {
                        foreach ( $front_assets as $handle => $asset_params ) {
                            if ( is_string( $handle ) && ! array_key_exists( $handle, $enqueuing_candidates ) ) {
                                $enqueuing_candidates[ $handle ] = $asset_params;
                            }
                        }
                    }
                } else {
                    if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
                        $enqueuing_candidates = $this->sek_sniff_assets_to_enqueue( $level_data['collection'], $enqueuing_candidates );
                    }
                }
            }//foreach
            return $enqueuing_candidates;
        }

        /* ------------------------------------------------------------------------- *
         *  SMART LOAD.
        /* ------------------------------------------------------------------------- */
        // @return string
        // adds the lazy load data attributes when sek_is_img_smartload_enabled()
        // adds the parallax attributes
        // img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
        // the local option wins
        // deactivated when customizing @see function sek_is_img_smartload_enabled()
        function sek_maybe_add_bg_attributes( $model ) {
            $new_attributes = [];
            $bg_img_url = '';
            $parallax_enabled = false;
            $fixed_bg_enabled = false;
            $width = '';
            $height = '';
            $level_type = array_key_exists( 'level', $model ) ? $model['level'] : 'section';

            // will be used for sections (not columns and modules ) that have a video background
            // implemented for video bg https://github.com/presscustomizr/nimble-builder/issues/287
            $video_bg_url = '';
            $video_bg_loop = true;
            $video_bg_delay_before_start = null;
            $video_bg_on_mobile = false;
            $video_bg_start_time = null;
            $video_bg_end_time = null;


            if ( !empty( $model[ 'options' ] ) && is_array( $model['options'] ) ) {
                $bg_options = ( ! empty( $model[ 'options' ][ 'bg' ] ) && is_array( $model[ 'options' ][ 'bg' ] ) ) ? $model[ 'options' ][ 'bg' ] : array();
                if ( !empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
                    $new_attributes[] = 'data-sek-has-bg="true"';
                    $bg_img_url = wp_get_attachment_url( $bg_options[ 'bg-image'] );
                    // When the fixed background is ckecked, it wins against parallax
                    $fixed_bg_enabled = !empty( $bg_options['bg-attachment'] ) && sek_booleanize_checkbox_val( $bg_options['bg-attachment'] );
                    $parallax_enabled = !$fixed_bg_enabled && !empty( $bg_options['bg-parallax'] ) && sek_booleanize_checkbox_val( $bg_options['bg-parallax'] );
                    if ( $parallax_enabled ) {
                        $image = wp_get_attachment_image_src( $bg_options[ 'bg-image'], 'full' );
                        if ( $image ) {
                            list( $src, $width, $height ) = $image;
                        }
                    }
                }

                // Nov 2019, for video background https://github.com/presscustomizr/nimble-builder/issues/287
                // should be added for sections and columns only
                if ( in_array( $level_type, array( 'section', 'column') ) && !empty( $bg_options[ 'bg-use-video'] ) && sek_booleanize_checkbox_val( $bg_options[ 'bg-use-video'] ) ) {
                    if ( !empty( $bg_options[ 'bg-video' ] ) ) {
                        $video_bg_url = $bg_options[ 'bg-video' ];
                        // replace http by https if needed for mp4 video url
                        // fixes https://github.com/presscustomizr/nimble-builder/issues/550
                        if ( is_ssl() && is_string($video_bg_url) && stripos($video_bg_url, 'http://') === 0 ) {
                            $video_bg_url = 'https' . substr($video_bg_url, 4);
                        }
                    }
                    if ( array_key_exists( 'bg-video-loop', $bg_options ) ) {
                        $video_bg_loop = sek_booleanize_checkbox_val( $bg_options[ 'bg-video-loop' ] );
                    }
                    if ( !empty( $bg_options[ 'bg-video-delay-start' ] ) ) {
                        $video_bg_delay_before_start = abs( (int)$bg_options[ 'bg-video-delay-start' ] );
                    }

                    if ( array_key_exists( 'bg-video-on-mobile', $bg_options ) ) {
                        $video_bg_on_mobile = sek_booleanize_checkbox_val( $bg_options[ 'bg-video-on-mobile' ] );
                    }
                    if ( !empty( $bg_options[ 'bg-video-start-time' ] ) ) {
                        $video_bg_start_time = abs( (int)$bg_options[ 'bg-video-start-time' ] );
                    }
                    if ( !empty( $bg_options[ 'bg-video-end-time' ] ) ) {
                        $video_bg_end_time = abs( (int)$bg_options[ 'bg-video-end-time' ] );
                    }
                }
            }

            if ( !empty( $bg_img_url ) ) {
                $new_attributes[] = sprintf( 'data-sek-src="%1$s"', $bg_img_url );
            }
            if ( sek_is_img_smartload_enabled() ) {
                $new_attributes[] = sprintf( 'data-sek-lazy-bg="true"' );
            }
            // data-sek-bg-fixed attribute has been added for https://github.com/presscustomizr/nimble-builder/issues/414
            // @see css rules related
            // we can't have both fixed and parallax option together
            // when the fixed background is ckecked, it wins against parallax
            if ( $fixed_bg_enabled ) {
                $new_attributes[] = 'data-sek-bg-fixed="true"';
            } else if ( $parallax_enabled ) {
                $new_attributes[] = sprintf('data-sek-bg-parallax="true" data-bg-width="%1$s" data-bg-height="%2$s" data-sek-parallax-force="%3$s"',
                    $width,
                    $height,
                    array_key_exists('bg-parallax-force', $bg_options) ? $bg_options['bg-parallax-force'] : '40'
                    //!empty( $bg_options['bg-parallax-force'] ) ? $bg_options['bg-parallax-force'] : '40'
                );
            }

            // video background insertion can only be done for sections and columns
            if ( in_array( $level_type, array( 'section', 'column') ) ) {
                if ( !empty( $video_bg_url ) && is_string( $video_bg_url ) ) {
                    $new_attributes[] = sprintf('data-sek-video-bg-src="%1$s"', $video_bg_url );
                    $new_attributes[] = sprintf('data-sek-video-bg-loop="%1$s"', $video_bg_loop ? 'true' : 'false' );
                    if ( !is_null( $video_bg_delay_before_start ) && $video_bg_delay_before_start >= 0 ) {
                        $new_attributes[] = sprintf('data-sek-video-delay-before="%1$s"', $video_bg_delay_before_start );
                    }
                    $new_attributes[] = sprintf('data-sek-video-bg-on-mobile="%1$s"', $video_bg_on_mobile ? 'true' : 'false' );
                    if ( !is_null( $video_bg_start_time ) && $video_bg_start_time >= 0 ) {
                        $new_attributes[] = sprintf('data-sek-video-start-at="%1$s"', $video_bg_start_time );
                    }
                    if ( !is_null( $video_bg_end_time ) && $video_bg_end_time >= 0 ) {
                        $new_attributes[] = sprintf('data-sek-video-end-at="%1$s"', $video_bg_end_time );
                    }
                }
            }
            return implode( ' ', $new_attributes );
        }


        // @filter nimble_parse_for_smart_load
        // this filter is used in several modules : tiny_mce_editor, image module, post grid
        // img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
        // deactivated when customizing @see function sek_is_img_smartload_enabled()
        // @return html string
        function sek_maybe_process_img_for_js_smart_load( $html ) {
            // if ( skp_is_customizing() || !sek_is_img_smartload_enabled() )
            //   return $html;
            if ( !sek_is_img_smartload_enabled() )
              return $html;
            if ( ! is_string( $html ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => provided html is not a string', $html );
                return $html;
            }
            if ( is_feed() || is_preview() )
                return $html;

            $allowed_image_extensions = apply_filters( 'nimble_smartload_allowed_img_extensions', array(
                'bmp',
                'gif',
                'jpeg',
                'jpg',
                'jpe',
                'tif',
                'tiff',
                'ico',
                'png',
                'svg',
                'svgz'
            ) );

            if ( empty( $allowed_image_extensions ) || ! is_array( $allowed_image_extensions ) ) {
              return $html;
            }

            $img_extensions_pattern = sprintf( "(?:%s)", implode( '|', $allowed_image_extensions ) );
            $pattern = '#<img([^>]+?)src=[\'"]?([^\'"\s>]+\.'.$img_extensions_pattern.'[^\'"\s>]*)[\'"]?([^>]*)>#i';

            return preg_replace_callback( $pattern, '\Nimble\nimble_regex_callback', $html);
        }


        ////////////////////////////////////////////////////////////////
        // SETUP CONTENT FILTERS FOR TINYMCE MODULE
        // Fired in the constructor
        private function sek_setup_tiny_mce_content_filters() {
            // @see filters in wp-includes/default-filters.php
            // always check if 'do_blocks' exists for retrocompatibility with WP < 5.0. @see https://github.com/presscustomizr/nimble-builder/issues/237
            if ( function_exists( 'do_blocks' ) ) {
                add_filter( 'the_nimble_tinymce_module_content', 'do_blocks', 9 );
            }
            add_filter( 'the_nimble_tinymce_module_content', 'wptexturize' );
            add_filter( 'the_nimble_tinymce_module_content', 'convert_smilies', 20 );
            add_filter( 'the_nimble_tinymce_module_content', 'wpautop' );
            add_filter( 'the_nimble_tinymce_module_content', 'shortcode_unautop' );
            add_filter( 'the_nimble_tinymce_module_content', 'prepend_attachment' );
            add_filter( 'the_nimble_tinymce_module_content', 'wp_make_content_images_responsive' );
            add_filter( 'the_nimble_tinymce_module_content', 'do_shortcode', 11 ); // AFTER wpautop()
            add_filter( 'the_nimble_tinymce_module_content', 'capital_P_dangit', 9 );
            add_filter( 'the_nimble_tinymce_module_content', '\Nimble\sek_parse_template_tags', 21 );

            // Hack to get the [embed] shortcode to run before wpautop()
            // fixes Video Embed not showing when using Add Media > Insert from Url
            // @see https://github.com/presscustomizr/nimble-builder/issues/250
            // @see wp-includes/class-wp-embed.php
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_run_shortcode' ), 8 );

            // @see filters in wp-includes/class-wp-embed.php
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_parse_content_for_video_embed') , 8 );
        }

         // fired @filter the_nimble_tinymce_module_content
        function sek_run_shortcode( $content ) {
            if ( array_key_exists( 'wp_embed', $GLOBALS ) && $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
                $content = $GLOBALS['wp_embed']->run_shortcode( $content );
            }
            return $content;
        }

        // fired @filter the_nimble_tinymce_module_content
        function sek_parse_content_for_video_embed( $content ) {
            if ( array_key_exists( 'wp_embed', $GLOBALS ) && $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
                $content = $GLOBALS['wp_embed']->autoembed( $content );
            }
            return $content;
        }





        /* ------------------------------------------------------------------------- *
         *  CONTENT, HEADER, FOOTER
        /* ------------------------------------------------------------------------- */
        // fired @hook 'template_include'
        // @return template path
        function sek_maybe_set_local_nimble_template( $template ) {
            //sek_error_log(' SOO ?? sek_get_skoped_seks( skp_get_skope_id() ) ' . skp_get_skope_id(), sek_get_skoped_seks( skp_get_skope_id() ) );
            $locale_template = sek_get_locale_template();
            if ( !empty( $locale_template ) ) {
                $template = $locale_template;
            }
            //sek_error_log( 'TEMPLATE ? => ' . did_action('wp'), $template );
            return $template;
        }


        // fired @hook 'template_redirect'
        // fired by sek_maybe_set_local_nimble_footer() @get_footer()
        // fired by sek_maybe_set_local_nimble_header() @get_header()
        // @return void()
        // set the value of the properties
        // has_local_header_footer
        // has_global_header_footer
        function sek_maybe_set_nimble_header_footer() {
            if ( !did_action('nimble_front_classes_ready') || !did_action('wp') ) {
                sek_error_log( __FUNCTION__ . ' has been invoked too early at hook ' . current_filter() );
                return;
            }
            if ( '_not_cached_yet_' === $this->has_local_header_footer || '_not_cached_yet_' === $this->has_global_header_footer ) {
                //sek_error_log(' SOO ?? sek_get_skoped_seks( skp_get_skope_id() ) ' . skp_get_skope_id(), sek_get_skoped_seks( skp_get_skope_id() ) );
                $local_header_footer_data = sek_get_local_option_value('local_header_footer');
                $global_header_footer_data = sek_get_global_option_value('global_header_footer');

                $apply_local_option = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data ) && 'inherit' !== $local_header_footer_data['header-footer'];

                $this->has_global_header_footer = !is_null( $global_header_footer_data ) && is_array( $global_header_footer_data ) && !empty( $global_header_footer_data['header-footer'] ) && 'nimble_global' === $global_header_footer_data['header-footer'];

                if ( $apply_local_option ) {
                    $this->has_local_header_footer = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data['header-footer'] ) && 'nimble_local' === $local_header_footer_data['header-footer'];
                    $this->has_global_header_footer = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data['header-footer'] ) && 'nimble_global' === $local_header_footer_data['header-footer'];
                }
            }
        }



        // fired @filter get_header()
        // Nimble will use an overridable template if a local or global header/footer is used
        // template located in /tmpl/header/ or /tmpl/footer
        // developers can override this template from a theme with a file that has this path : 'nimble_templates/header/nimble_header_tmpl.php
        function sek_maybe_set_local_nimble_header( $header_name ) {
            // if Nimble_Manager()->has_local_header_footer || Nimble_Manager()->has_global_header_footer
            if ( sek_page_uses_nimble_header_footer() ) {
                // load the Nimble template which includes a call to wp_head()
                $template_file_name_with_php_extension = 'nimble_header_tmpl.php';
                $template_path = apply_filters( 'nimble_set_header_template_path', NIMBLE_BASE_PATH . "/tmpl/header/{$template_file_name_with_php_extension}", $template_file_name_with_php_extension );

                // dec 2019 : can be overriden from a child theme
                // see https://github.com/presscustomizr/nimble-builder/issues/568
                $overriden_template_path = sek_maybe_get_overriden_local_template_path( array( 'file_name' => $template_file_name_with_php_extension, 'folder' => 'header' ) );
                if ( !empty( $overriden_template_path ) ) {
                    $template_path = $overriden_template_path;
                }

                load_template( $template_path, false );

                // do like in wp core get_header()
                $templates = array();
                $header_name = (string) $header_name;
                if ( '' !== $header_name ) {
                  $templates[] = "header-{$header_name}.php";
                }

                $templates[] = 'header.php';

                // don't run wp_head a second time
                remove_all_actions( 'wp_head' );
                // capture the print and clean it.
                ob_start();
                // won't be re-loaded by the second call performed by WP
                // see https://developer.wordpress.org/reference/functions/locate_template/
                // and https://developer.wordpress.org/reference/functions/load_template/
                locate_template( $templates, true );
                ob_get_clean();
            }
        }

        // fired @filter get_footer()
        // Nimble will use an overridable template if a local or global header/footer is used
        // template located in /tmpl/header/ or /tmpl/footer
        // developers can override this template from a theme with a file that has this path : 'nimble_templates/footer/nimble_footer_tmpl.php
        function sek_maybe_set_local_nimble_footer( $footer_name ) {
            // if Nimble_Manager()->has_local_header_footer || Nimble_Manager()->has_global_header_footer
            if ( sek_page_uses_nimble_header_footer() ) {
                // load the Nimble template which includes a call to wp_footer()
                $template_file_name_with_php_extension = 'nimble_footer_tmpl.php';
                $template_path = apply_filters( 'nimble_set_header_template_path', NIMBLE_BASE_PATH . "/tmpl/footer/{$template_file_name_with_php_extension}", $template_file_name_with_php_extension );

                // dec 2019 : can be overriden from a child theme
                // see https://github.com/presscustomizr/nimble-builder/issues/568
                $overriden_template_path = sek_maybe_get_overriden_local_template_path( array( 'file_name' => $template_file_name_with_php_extension, 'folder' => 'footer' ) );
                if ( !empty( $overriden_template_path ) ) {
                    $template_path = $overriden_template_path;
                }

                load_template( $template_path, false );

                // do like in wp core get_footer()
                $templates = array();
                $name = (string) $footer_name;
                if ( '' !== $footer_name ) {
                    $templates[] = "footer-{$footer_name}.php";
                }

                $templates[]    = 'footer.php';

                // don't run wp_footer a second time
                remove_all_actions( 'wp_footer' );
                // capture the print and clean it.
                ob_start();
                // won't be re-loaded by the second call performed by WP
                // see https://developer.wordpress.org/reference/functions/locate_template/
                // and https://developer.wordpress.org/reference/functions/load_template/
                locate_template( $templates, true );
                ob_get_clean();
            }
        }//sek_maybe_set_local_nimble_footer


        // @hook wp_head
        // Elements of decisions for this implementation :
        // The problem to solve here is to add the post ( or pages ) where user has created Nimble sections for which the content matches the search term.
        // 1) we need a way to find the matches
        // 2) then to "map" the Nimble post to its related post or page
        // 3) then include the related post / page to the list of search result.
        // This can't be done by filtering the WP core query params, because Nimble sections are saved as separate posts, not post metas.
        // That's why the posts are added to the array of posts of the main query.
        //
        // fixes https://github.com/presscustomizr/nimble-builder/issues/439
        //
        // May 2019 => note that this implementation won't include Nimble sections created in other contexts than page or post.
        // This could be added in the future.
        //
        // partially inspired by https://stackoverflow.com/questions/24195818/add-results-into-wordpress-search-results
        function sek_maybe_include_nimble_content_in_search_results(){
            if ( !is_search() )
              return;
            global $wp_query;

            $query_vars = $wp_query->query_vars;
            if ( ! is_array( $query_vars ) || empty( $query_vars['s'] ) )
              return;

            // Search query on Nimble CPT
            $sek_post_query_vars = array(
                'post_type'              => NIMBLE_CPT,
                'post_status'            => get_post_stati(),
                'posts_per_page'         => -1,
                'no_found_rows'          => true,
                'cache_results'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'lazy_load_term_meta'    => false,
                's' => $query_vars['s']
            );
            $query = new \WP_Query( $sek_post_query_vars );

            // The search string has been found in a set of Nimble posts
            if ( is_array( $query->posts ) ) {
                foreach ( $query->posts as $post_object ) {
                    // The related WP object ( == skope ) is written in the title of Nimble CPT
                    // ex : nimble___skp__post_post_114
                    if ( preg_match('(post_page|post_post)', $post_object->post_title ) ) {
                        $post_number = preg_replace('/[^0-9]/', '', $post_object->post_title );
                        $post_number = intval($post_number);

                        $post_candidate = get_post( $post_number );

                        if ( is_object( $post_candidate ) ) {
                            // Merge Nimble posts to WP posts
                            array_push( $wp_query->posts, $post_candidate );
                        }
                    }
                }
            }

            // Maybe clean duplicated posts
            $maybe_includes_duplicated = $wp_query->posts;
            $without_duplicated = array();
            $post_ids = array();

            foreach ( $maybe_includes_duplicated as $post_obj ) {
                if ( in_array( $post_obj->ID, $post_ids ) )
                  continue;
                $post_ids[] = $post_obj->ID;
                $without_duplicated[] = $post_obj;
            }
            $wp_query->posts = $without_duplicated;

            // Make sure the post_count and found_posts are updated
            $wp_query->post_count = count($wp_query->posts);
            $wp_query->found_posts = $wp_query->post_count;
        }// sek_maybe_include_nimble_content_in_search_results


        // @return html string
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
        function sek_maybe_print_preview_level_guid_html() {
              if ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
                  return sprintf( 'data-sek-preview-level-guid="%1$s"', $this->sek_get_preview_level_guid() );
              }
              return '';
        }

        // @return unique guid()
        // inspired from https://stackoverflow.com/questions/21671179/how-to-generate-a-new-guid#26163679
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
        function sek_get_preview_level_guid() {
              if ( '_preview_level_guid_not_set_' === $this->preview_level_guid ) {
                  // When ajaxing, typically creating content, we need to make sure that we use the initial guid generated last time the preview was refreshed
                  // @see preview::doAjax()
                  if ( isset( $_POST['preview-level-guid'] ) ) {
                      if ( empty( $_POST['preview-level-guid'] ) ) {
                            sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => error, preview-level-guid can not be empty' );
                      }
                      $this->preview_level_guid = $_POST['preview-level-guid'];
                  } else {
                      $this->preview_level_guid = sprintf('%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) );
                  }

              }
              return $this->preview_level_guid;
        }
    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Render_Css' ) ) :
    class SEK_Front_Render_Css extends SEK_Front_Render {
        // Fired in __construct()
        function _setup_hook_for_front_css_printing_or_enqueuing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'print_or_enqueue_seks_style'), PHP_INT_MAX );
        }

        // Can be fired :
        // 1) on wp_enqueue_scripts or wp_head
        // 2) when ajaxing, for actions 'sek-resize-columns', 'sek-refresh-stylesheet'
        function print_or_enqueue_seks_style( $skope_id = null ) {
            // when this method is fired in a customize preview context :
            //    - the skope_id has to be built. Since we are after 'wp', this is not a problem.
            //    - the css rules are printed inline in the <head>
            //    - we set to hook to wp_head
            //
            // when the method is fired in an ajax refresh scenario, like 'sek-refresh-stylesheet'
            //    - the skope_id must be passed as param
            //    - the css rules are printed inline in the <head>
            //    - we set the hook to ''

            // AJAX REQUESTED STYLESHEET
            if ( ( ! is_null( $skope_id ) && ! empty( $skope_id ) ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                if ( ! isset($_POST['local_skope_id']) ) {
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => error missing local_skope_id');
                    return;
                }
                $local_skope_id = $_POST['local_skope_id'];
                $css_handler_instance = $this->_instantiate_css_handler( array( 'skope_id' => $skope_id, 'is_global_stylesheet' => NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) );
            }
            // in a front normal context, the css is enqueued from the already written file.
            else {
                $local_skope_id = skp_build_skope_id();
                // LOCAL SECTIONS STYLESHEET
                $this->_instantiate_css_handler( array( 'skope_id' => skp_build_skope_id() ) );
                // GLOBAL SECTIONS STYLESHEET
                // Can hold rules for global sections and global styling
                $this->_instantiate_css_handler( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) );
            }
            $google_fonts_print_candidates = $this->sek_get_gfont_print_candidates( $local_skope_id );
            // GOOGLE FONTS
            if ( !empty( $google_fonts_print_candidates ) ) {
                // When customizing we get the google font content
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    $this->sek_gfont_print( $google_fonts_print_candidates );
                } else {
                    if ( in_array( current_filter(), array( 'wp_footer', 'wp_head' ) ) ) {
                        $this->sek_gfont_print( $google_fonts_print_candidates );
                    } else {

                        // preload implemented for https://github.com/presscustomizr/nimble-builder/issues/629
                        if ( !skp_is_customizing() && sek_preload_google_fonts_on_front() ) {
                            add_action( 'wp_head', array( $this, 'sek_gfont_print_with_preload') );
                        } else {
                            wp_enqueue_style(
                                'sek-gfonts-local-and-global',
                                sprintf( '//fonts.googleapis.com/css?family=%s', $google_fonts_print_candidates ),
                                array(),
                                null,
                                'all'
                            );
                        }
                    }
                }
            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX && empty( $skope_id ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . ' =>the skope_id should not be empty' );
            }
        }//print_or_enqueue_seks_style

        // hook : wp_head
        // or fired directly when ajaxing
        // When ajaxing, the link#sek-gfonts-{$this->id} gets removed from the dom and replaced by this string
        function sek_gfont_print( $print_candidates ) {
            if ( ! empty( $print_candidates ) ) {
                printf('<link rel="stylesheet" id="%1$s" href="%2$s">',
                    'sek-gfonts-local-and-global',
                    "//fonts.googleapis.com/css?family={$print_candidates}"
                );
            }
        }

        // hook : wp_head
        // fired on front only when not customizing
        // March 2020 preload implemented for https://github.com/presscustomizr/nimble-builder/issues/629
        function sek_gfont_print_with_preload( $print_candidates = '' ) {
            // print candidates must be fetched when sek_preload_google_fonts_on_front()
            $print_candidates = $this->sek_get_gfont_print_candidates();

            if ( ! empty( $print_candidates ) ) {
                ?>
                <script id="nimble-preload-gfonts">nb_.preloadAsset( { id : 'sek-gfonts-local-and-global', as : 'style', href : '//fonts.googleapis.com/css?family=<?php echo $print_candidates; ?>', scriptEl : document.currentScript } );</script>
                <?php
            }
        }

        //@return string
        // sek_model is passed when customizing in SEK_Front_Render_Css::print_or_enqueue_seks_style()
        function sek_get_gfont_print_candidates( $local_skope_id = null ) {
            // return the cache version if already set
            if ( 'not_set' !== Nimble_Manager()->google_fonts_print_candidates )
              return Nimble_Manager()->google_fonts_print_candidates;

            $local_skope_id = is_null( $local_skope_id ) ? skp_build_skope_id() : $local_skope_id;
            // local sections
            $local_seks = sek_get_skoped_seks( $local_skope_id );
            // global sections
            $global_seks = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            // global options
            $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );

            $print_candidates = '';
            $ffamilies = array();

            // Let's build the collection of google fonts from local sections, global sections, global options
            if ( is_array( $local_seks ) && !empty( $local_seks['fonts'] ) && is_array( $local_seks['fonts'] ) ) {
                $ffamilies = $local_seks['fonts'];
            }
            if ( is_array( $global_seks ) && !empty( $global_seks['fonts'] ) && is_array( $global_seks['fonts'] ) ) {
                $ffamilies = array_merge( $ffamilies, $global_seks['fonts'] );
            }
            if ( is_array( $global_options ) && !empty( $global_options['fonts'] ) && is_array( $global_options['fonts'] ) ) {
                $ffamilies = array_merge( $ffamilies, $global_options['fonts'] );
            }

            // remove duplicate if any
            $ffamilies = array_unique( $ffamilies );

            if ( ! empty( $ffamilies ) ) {
                $ffamilies = implode( "|", $ffamilies );
                $print_candidates = str_replace( '|', '%7C', $ffamilies );
                $print_candidates = str_replace( '[gfont]', '' , $print_candidates );
            }
            // cache now
            Nimble_Manager()->google_fonts_print_candidates = $print_candidates;
            return Nimble_Manager()->google_fonts_print_candidates;
        }

        // @param params = array( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) )
        // fired @'wp_enqueue_scripts'
        private function _instantiate_css_handler( $params = array() ) {
            $params = wp_parse_args( $params, array( 'skope_id' => '', 'is_global_stylesheet' => false ) );

            // Print inline or enqueue ?
            $print_mode = Sek_Dyn_CSS_Handler::MODE_FILE;
            if ( is_customize_preview() ) {
              $print_mode = Sek_Dyn_CSS_Handler::MODE_INLINE;
            } else if ( sek_inline_dynamic_stylesheets_on_front() ) {
              $print_mode = Sek_Dyn_CSS_Handler::MODE_INLINE;
            }
            // Which hook ?
            $fire_at_hook = '';
            if ( !defined( 'DOING_AJAX' ) && is_customize_preview() ) {
              $fire_at_hook = 'wp_head';
            }
            // introduced for https://github.com/presscustomizr/nimble-builder/issues/612
            else if ( !defined( 'DOING_AJAX' ) && !is_customize_preview() && sek_inline_dynamic_stylesheets_on_front() ) {
              $fire_at_hook = 'wp_head';
            }

            $css_handler_instance = new Sek_Dyn_CSS_Handler( array(
                'id'             => $params['skope_id'],
                'skope_id'       => $params['skope_id'],
                // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
                'is_global_stylesheet' => $params['is_global_stylesheet'],
                'mode'           => $print_mode,
                //these are taken in account only when 'mode' is 'file'
                'force_write'    => true, //<- write if the file doesn't exist
                'force_rewrite'  => is_user_logged_in() && current_user_can( 'customize' ), //<- write even if the file exists
                'hook'           => $fire_at_hook
            ));
            return $css_handler_instance;
        }

    }//class
endif;

?><?php
if ( ! class_exists( '\Nimble\Sek_Simple_Form' ) ) :
class Sek_Simple_Form extends SEK_Front_Render_Css {

    private $form;
    private $fields;
    private $mailer;

    private $form_composition;

    function _setup_simple_forms() {
        //Hooks
        add_action( 'parse_request', array( $this, 'simple_form_parse_request' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_recaptcha_scripts' ), 0 );
        add_action( 'body_class', array( $this, 'set_the_recaptcha_badge_visibility_class') );

        // Note : form input need to be prefixed to avoid a collision with reserved WordPress input
        // @see : https://stackoverflow.com/questions/15685020/wordpress-form-submission-and-the-404-error-page#16636051
        $this->form_composition = array(
            'nimble_simple_cf'              => array(
                'type'            => 'hidden',
                'value'           => 'nimble_simple_cf'
            ),
            'nimble_recaptcha_resp'   => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_skope_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_level_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_name' => array(
                'label'            => __( 'Name', 'text_doma' ),
                'required'         => true,
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_email' => array(
                'label'            => __( 'Email', 'text_doma' ),
                'required'         => true,
                'type'             => 'email',
                'wrapper_tag'      => 'div'
            ),
            'nimble_subject' => array(
                'label'            => __( 'Subject', 'text_doma' ),
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_message' => array(
                'label'            => __( 'Message', 'text_doma' ),
                'required'         => true,
                'additional_attrs' => array( 'rows' => "10", 'cols' => "50" ),
                'type'             => 'textarea',
                'wrapper_tag'      => 'div'
            ),
            'nimble_submit' => array(
                'type'             => 'submit',
                'value'            => __( 'Submit', 'text_doma' ),
                'additional_attrs' => array( 'class' => 'sek-btn' ),
                'wrapper_tag'      => 'div',
                'wrapper_class'    => array( 'sek-form-field', 'sek-form-btn-wrapper' )
            )
        );
    }//_setup_simple_forms


    //@hook: parse_request
    function simple_form_parse_request() {
        if ( ! isset( $_POST['nimble_simple_cf'] ) )
          return;

        // get the module options
        // we are before 'wp', so let's use the posted skope_id and level_id to get our $module_user_values
        $module_model = array();
        if ( isset( $_POST['nimble_skope_id'] ) && '_skope_not_set_' !== $_POST['nimble_skope_id'] ) {
            $local_sektions = sek_get_skoped_seks( $_POST['nimble_skope_id'] );
            if ( is_array( $local_sektions ) && !empty( $local_sektions ) ) {
            $sektion_collection = array_key_exists('collection', $local_sektions) ? $local_sektions['collection'] : array();
            }
            if ( is_array($sektion_collection) && ! empty( $sektion_collection ) && isset( $_POST['nimble_level_id'] ) ) {
                $module_model = sek_get_level_model($_POST['nimble_level_id'], $sektion_collection );
                $module_model = sek_normalize_module_value_with_defaults( $module_model );
            }
        } else {
            sek_error_log( __FUNCTION__ . ' => skope_id problem');
            return;
        }

        if ( empty( $module_model ) ) {
            sek_error_log( __FUNCTION__ . ' => invalid module model');
            return;
        }

        //update the form with the posted values
        foreach ( $this->form_composition as $name => $field ) {
            $form_composition[ $name ]                = $field;
            if ( isset( $_POST[ $name ] ) ) {
                $form_composition[ $name ][ 'value' ] = $_POST[ $name ];
            }
        }
        //set the form composition according to the user's options
        $form_composition = $this->_set_form_composition( $form_composition, $module_model );
        //generate fields
        $this->fields = $this->simple_form_generate_fields( $form_composition );
        //generate form
        $this->form = $this->simple_form_generate_form( $this->fields, $module_model );

        //mailer
        $this->mailer = new Sek_Mailer( $this->form );
        $this->mailer->maybe_send( $form_composition, $module_model );
    }

    // Fired @hook wp_enqueue_scripts
    // @return void()
    function maybe_enqueue_recaptcha_scripts() {
        // enabled if
        // - not customizing
        // - global 'recaptcha' options has the following values
        //    - enabled === true
        //    - public_key entered
        //    - private_key entered
        // - the current page does not include a form in a local or global location
        if ( skp_is_customizing() || !sek_is_recaptcha_globally_enabled() || !sek_front_sections_include_a_form() )
          return;

        // @todo, we don't handle the case when reCaptcha is globally enabled but disabled for a particular form.

        $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
        $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();

        $url = add_query_arg(
            array( 'render' => esc_attr( $global_recaptcha_opts['public_key'] ) ),
            'https://www.google.com/recaptcha/api.js'
        );

        wp_enqueue_script( 'google-recaptcha', $url, array(), '3.0', true );
        add_action('wp_footer', array( $this, 'print_recaptcha_inline_js'), 100 );
    }

    // @hook wp_footer
    // printed only when sek_is_recaptcha_globally_enabled()
    // AND
    // sek_front_sections_include_a_form()
    function print_recaptcha_inline_js() {
        ?>
            <script id="nimble-inline-recaptcha">
              !( function( grecaptcha, sitekey ) {
                  var recaptcha = {
                      execute: function() {
                          grecaptcha.execute(
                              sitekey,
                              // see https://developers.google.com/recaptcha/docs/v3#actions
                              { action: ( window.sekFrontLocalized && sekFrontLocalized.skope_id ) ? sekFrontLocalized.skope_id.replace( 'skp__' , 'nimble_form__' ) : 'nimble_builder_form' }
                          ).then( function( token ) {
                              var forms = document.getElementsByTagName( 'form' );
                              for ( var i = 0; i < forms.length; i++ ) {
                                  var fields = forms[ i ].getElementsByTagName( 'input' );
                                  for ( var j = 0; j < fields.length; j++ ) {
                                      var field = fields[ j ];
                                      if ( 'nimble_recaptcha_resp' === field.getAttribute( 'name' ) ) {
                                          field.setAttribute( 'value', token );
                                          break;
                                      }
                                  }
                              }
                          } );
                      }
                  };
                  grecaptcha.ready( recaptcha.execute );
              })( grecaptcha, sekFrontLocalized.recaptcha_public_key );
            </script>
        <?php
    }

    // @hook body_class
    public function set_the_recaptcha_badge_visibility_class( $classes ) {
        // Shall we print the badge ?
        // @todo : we don't handle the case when recaptcha badge is globally displayed but
        // the current page has disabled recaptcha
        if ( ! sek_is_recaptcha_badge_globally_displayed() ) {
            $classes[] = 'sek-hide-rc-badge';
        }
        return $classes;
    }


    // Rendering
    // Invoked from the tmpl
    // @return string
    // @param module_options is the module level "value" property. @see tmpl/modules/simple_form_module_tmpl.php
    function get_simple_form_html( $module_model ) {
        // sek_error_log('$module_model ?', $module_model );
        // sek_error_log('$this->fields ?', $this->fields );
        // sek_error_log('$this->form ?', $this->form );
        // sek_error_log('$this->mailer ?', $this->mailer );
        // sek_error_log('$_POST ?', $_POST );
        $html         = '';
        //set the form composition according to the user's options
        $form_composition = $this->_set_form_composition( $this->form_composition, $module_model );
        //generate fields
        $fields       = isset( $this->fields ) ? $this->fields : $this->simple_form_generate_fields( $form_composition );
        //generate form
        $form         = isset( $this->form ) ? $this->form : $this->simple_form_generate_form( $fields, $module_model );

        $module_id = is_array( $module_model ) && array_key_exists('id', $module_model ) ? $module_model['id'] : '';
        ob_start();
        ?>
        <div id="sek-form-respond">
          <?php
            $echo_form = true;
            // When loading the page after a send attempt, focus on the module html element with a javascript animation
            // In this case, don't echo the form, but only the user defined message which should be displayed after submitting the form
            if ( ! is_null( $this->mailer ) ) {
                // Make sure we target the right form if several forms are displayed in a page
                $current_form_has_been_submitted = isset( $_POST['nimble_level_id'] ) && $_POST['nimble_level_id'] === $module_id;

                if ( 'sent' == $this->mailer->get_status() && $current_form_has_been_submitted ) {
                    $echo_form = false;
                }
            }

            if ( !$echo_form ) {
                ?>
                  <script type="text/javascript">
                      jQuery( function($) {
                          var $elToFocusOn = $('div[data-sek-id="<?php echo $module_id; ?>"]' );
                          if ( $elToFocusOn.length > 0 ) {
                                var _do = function() {
                                    $('html, body').animate({
                                        scrollTop : $elToFocusOn.offset().top - ( $(window).height() / 2 ) + ( $elToFocusOn.outerHeight() / 2 )
                                    }, 'slow');
                                };
                                try { _do(); } catch(er) {}
                          }
                      });
                  </script>
                <?php

                $message = $this->mailer->get_message( $this->mailer->get_status(), $module_model );
                if ( !empty($message) ) {
                    $class = 'sek-mail-failure';
                    switch( $this->mailer->get_status() ) {
                          case 'sent' :
                              $class = 'sek-mail-success';
                          break;
                          case 'not_sent' :
                              $class = '';
                          break;
                          case 'aborted' :
                              $class = 'sek-mail-aborted';
                          break;
                    }
                    printf( '<div class="sek-form-message %1$s">%2$s</div>', $class, $message );
                }
            } else {
                // If we're in the regular case ( not after submission ), echo the form
                echo $form;
            }
          ?>
        </div>
        <?php
        return ob_get_clean();
    }

    //set the fields to render
    private function _set_form_composition( $form_composition, $module_model = array() ) {

        $user_form_composition = array();
        if ( ! is_array( $module_model ) ) {
              sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => ERROR : invalid module options array');
              return $user_form_composition;
        }
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $form_fields_options = empty( $module_user_values['form_fields'] ) ? array() : $module_user_values['form_fields'];
        $form_button_options = empty( $module_user_values['form_button'] ) ? array() : $module_user_values['form_button'];
        $form_submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        foreach ( $form_composition as $field_id => $field_data ) {
            //sek_error_log( '$field_data', $field_data );
            switch ( $field_id ) {
                case 'nimble_name':
                    if ( ! empty( $form_fields_options['show_name_field'] ) && sek_is_checked( $form_fields_options['show_name_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['name_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['name_field_label'] );
                    }
                break;
                case 'nimble_subject':
                    if ( ! empty( $form_fields_options['show_subject_field'] ) && sek_is_checked( $form_fields_options['show_subject_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['subject_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['subject_field_label'] );
                    }
                break;
                case 'nimble_message':
                    if ( ! empty( $form_fields_options['show_message_field'] ) && sek_is_checked( $form_fields_options['show_message_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['message_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['message_field_label'] );
                    }
                break;
                case 'nimble_email':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['email_field_label'] );
                break;
                //'additional_attrs' => array( 'class' => 'sek-btn' ),
                case 'nimble_submit':
                    $user_form_composition[$field_id] = $field_data;
                    $visual_effect_class = '';
                    //visual effect classes
                    if ( array_key_exists( 'use_box_shadow', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['use_box_shadow'] ) ) {
                        $visual_effect_class = ' box-shadow';
                        if ( array_key_exists( 'push_effect', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['push_effect'] ) ) {
                            $visual_effect_class .= ' push-effect';
                        }
                    }
                    $user_form_composition[$field_id]['additional_attrs']['class'] = 'sek-btn' . $visual_effect_class;
                    $user_form_composition[$field_id]['value'] = esc_attr( $form_fields_options['button_text'] );
                break;
                case 'nimble_skope_id':
                    $user_form_composition[$field_id] = $field_data;
                    // When the form is submitted, we grab the skope_id from the posted value, because it is too early to build it.
                    // of course we don't need to set this input value when customizing.
                    $skope_id = '';
                    if ( ! skp_is_customizing() ) {
                        $skope_id = isset( $_POST['nimble_skope_id'] ) ? $_POST['nimble_skope_id'] : sek_get_level_skope_id( $module_model['id'] );
                    }

                    // always use the posted skope_id
                    // => in a scenario in which we post the form several times, the skp_get_skope_id() won't be available after the first submit action
                    $user_form_composition[$field_id]['value'] = $skope_id;
                break;
                case 'nimble_level_id':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['value'] = $module_model['id'];
                break;
                // print the recaptcha input field if
                // 1) reCAPTCHA enabled in the global options AND properly setup with non empty keys
                // 2) reCAPTCHA enabled for this particular form
                case 'nimble_recaptcha_resp' :
                    if ( ! skp_is_customizing() && sek_is_recaptcha_globally_enabled() && 'disabled' !== $form_submission_options['recaptcha_enabled'] ) {
                        $user_form_composition[$field_id] = $field_data;
                    }
                break;
                default:
                    $user_form_composition[$field_id] = $field_data;
                break;
            }

        }
        return $user_form_composition;
    }


    //generate the fields
    function simple_form_generate_fields( $form_composition = array() ) {
        $form_composition = empty( $form_composition ) ? $this->form_composition : $form_composition;
        $fields_ = array();
        $id_suffix = rand();
        foreach ( $form_composition as $name => $field ) {
            $field = wp_parse_args( $field, array( 'type' => 'text' ) );

            if ( class_exists( $class = '\Nimble\Sek_Input_' . ucfirst( $field['type'] ) ) ) {
                $fields_ [] = new Sek_Field (
                    new $class( array_merge( array( 'id_suffix'=> $id_suffix ), $field, array( 'name' => $name ) ) ),
                    $field
                );
            }
        }

        return $fields_;
    }


    //generate the fields
    function simple_form_generate_form( $fields, $module_model ) {
        $form   = new Sek_Form( [
            'action' => is_array( $module_model ) && ! empty( $module_model['id']) ? '#' . $module_model['id'] :'#',
            'method' => 'post'
        ] );
        $form->add_fields( $fields );

        return $form;
    }
}//Sek_Simple_Form
endif;

?><?php
/*
*&
*
*/
if ( ! class_exists( '\Nimble\Sek_Form' ) ) :
class Sek_Form {
    private $fields;
    private $attributes;

    // Sek_Form is instantiated from Sek_Simple_Form::simple_form_generate_form
    //$form   = new Sek_Form( [
    //     'action' => is_array( $module_model ) && ! empty( $module_model['id']) ? $module_model['id'] :'#',
    //     'method' => 'post'
    // ] );
    public function __construct( $args = array() ) {
        $this->fields        = array();
        $this->attributes    = wp_parse_args( $args, array(
            'action' => '#',// the action attribute doesn't have to be specified when form data is sent to the same page
            // see https://developer.mozilla.org/en-US/docs/Learn/Forms/Sending_and_retrieving_form_data,
            // for the moment we use the parent module id, example : #__nimble__cd4d5b307a3b
            'method' => 'post'
            //TODO: add html callback
        ) );
    }

    public function add_field( Sek_Field $field ) {
        $this->fields[ sanitize_key( $field->get_input()->get_data('name') ) ] = $field;
    }

    public function add_fields( array $fields ) {
        foreach ($fields as $field) {
            $this->add_field( $field );
        }
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_field( $field_name ) {
        if ( is_array( $this->fields ) && array_key_exists(sanitize_key( $field_name ), $this->fields) ) {
            return $this->fields[ sanitize_key( $field_name ) ];
        }
        return null;
    }

    //make sure fields are well formed
    public function has_invalid_field() {
        $has_invalid_field = false;

        foreach ( $this->fields as $form_field ) {
            if ( false !== $has_invalid_field )
              continue;
            $input        = $form_field->get_input();
            $value        = $input->get_value();
            $filter       = $input->get_data( 'filter' );
            $can_be_empty = true !== $input->get_data( 'required' );

            if ( $can_be_empty && ! $value ) {
                continue;
            }
            if ( $filter && ! filter_var( $value, $filter ) ) {
                $has_invalid_field = $input->get_data('label');
                break;
            }
        }

        return $has_invalid_field;
    }

    public function get_attributes_html() {
        return implode( ' ', array_map(
            function ($k, $v) {
                return sanitize_key( $k ) .'="'. esc_attr( $v ) .'"';
            },
            array_keys( $this->attributes ), $this->attributes
        ) );
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $fields = '';

        foreach ($this->fields as $name => $field) {
            $fields .= $field;
        }

        return sprintf('<form %1$s>%2$s</form>',
            $this->get_attributes_html(),
            $fields
        );
    }
}//Sek_Form
endif;











/*
* Field class definition
*
* label and/or wrapper + input field
*/
if ( ! class_exists( '\Nimble\Sek_Field' ) ) :
class Sek_Field {
    private $input;
    private $data;

    public function __construct( Sek_Input_Interface $input, $args = array() ) {
        $this->input = $input;

        $this->data  = wp_parse_args( $args, [
            'wrapper_tag'         => '',
            'wrapper_class'       => array( 'sek-form-field' ),
            'label'               => '',
            //TODO: allow callbacks
            'before_label'        => '',
            'after_label'         => '',
            'before_input'        => '',
            'after_input'         => '',
        ]);
    }

    public function get_input() {
        return $this->input;
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $label = $this->data[ 'label' ];

        //label stuff
        if ( $label ) {
            if ( true == $this->input->get_data( 'required' ) ) {
                $label .= ' *';
                //$label .= ' ' . esc_html__( '(required)', 'text_doma' );
            }
            $label = sprintf( '%1$s<label for="%2$s">%3$s</label>%4$s',
                $this->data[ 'before_label' ],
                esc_attr( $this->input->get_data( 'id' ) ),
                esc_html($label),
                $this->data[ 'after_label' ]
            );
        }

        //the input
        $html = sprintf( '%s%s%s%s',
            $label,
            $this->data[ 'before_input' ],
            $this->input,
            $this->data[ 'after_input' ]
        );

        //any wrapper?
        if ( $this->data[ 'wrapper_tag' ] ) {
            $wrapper_tag   = tag_escape( $this->data[ 'wrapper_tag' ] );
            $wrapper_class = $this->data[ 'wrapper_class' ] ? ' class="'. implode( ' ', array_map('sanitize_html_class', $this->data[ 'wrapper_class' ] ) ) .'"' : '';

            $html = sprintf( '<%1$s%2$s>%3$s</%1$s>',
                $wrapper_tag,
                $wrapper_class,
                $html
            );
        }

        return $html;
    }
}
endif;

?><?php
/*
*
* Input objects definition
*
*/
interface Sek_Input_Interface {
    public function sanitize( $value );
    public function escape( $value );
    public function get_value();
    public function set_value( $value );
    public function get_data( $data_key );
    public function html();
}

abstract class Sek_Input_Abstract implements Sek_Input_Interface {
    private $data;
    protected $attributes = array( 'id', 'name', 'required' );

    public function __construct( $args ) {
        //no name no party
        //TODO: raise exception
        if ( ! isset( $args['name'] ) ) {
            error_log( __FUNCTION__ . ' => contact form input name not set' );
            return;
        }

        $defaults = array(
            'name'               => '',
            'id'                 => '',
            'id_suffix'          => null,
            'additional_attrs'   => array(),
            'sanitize_cb'        => array( $this, 'sanitize' ),
            'escape_cb'          => array( $this, 'escape' ),
            'required'           => false,
            'filter'             => '',
            'value'              => ''
        );

        $data = wp_parse_args( $args, $defaults );


        $data[ 'id_suffix' ]        = is_null( $data[ 'id_suffix' ] ) ? rand() : $data[ 'id_suffix' ];
        $data[ 'id' ]               = empty( $data[ 'id' ] ) ? $data[ 'name' ] : $data[ 'id' ];
        $data[ 'id' ]               = $data[ 'id' ] . $data[ 'id_suffix' ];
        $data[ 'additional_attrs' ] = is_array( $data[ 'additional_attrs' ] ) ? $data[ 'additional_attrs' ] : array();

        $this->data = $data;

        if ( $data[ 'value' ] ) {
            $this->set_value( $data[ 'value' ]  );
        }

    }

    public function sanitize( $value ) {
        return $value;
    }

    public function escape( $value ) {
        return esc_attr( $value );
    }


    public function get_value() {
        $data = (array)$this->data;
        $value = $this->data['escape_cb']( $data['value'] );
        if ( skp_is_customizing() ) {
            $field_name = $this->get_data('name');
            switch( $field_name ) {
                case 'nimble_name' :
                    $value = '';
                break;
                case 'nimble_email' :
                    $value = '';
                break;
                case 'nimble_subject' :
                    $value = '';
                break;
                // case 'nimble_message' :
                //     $value = __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', 'text-domain');
                // break;
            }
        }
        return $value;
    }


    public function set_value( $value ) {
        $this->data['value'] = $this->data['sanitize_cb']( $value );
    }

    public function get_data( $data_key ){
        return isset( $this->data[ $data_key ] ) ? $this->data[ $data_key ] : null;
    }

    public function get_attributes_html() {
        $attributes = array_merge(
            array_intersect_key(
                array_filter( $this->data ),
                array_flip( $this->attributes )
            ),
            $this->data[ 'additional_attrs' ]
        );
        if ( skp_is_customizing() ) {
            $attributes['value'] = array_key_exists('value', $attributes ) ? $attributes['value'] : '';
        }
        $attributes = array_map(
            function ($k, $v) {
                switch ( $k ) {
                  case 'value':
                      $v = $this->get_value();
                  break;
                  default:
                      $v = esc_attr( $v );
                  break;
                }
                // 'required' attribute doesn't need a value : <input name="nimble_email" id="nimble_email1163989492" type="text" required/>
                return 'required' === $k ? 'required' : sanitize_key( $k ) .'="'. $v .'"';
            },
            array_keys($attributes), $attributes
        );

        return implode( ' ', $attributes );
    }


    public function __toString() {
        return $this->html();
    }

}//end abstract class









if ( ! class_exists( '\Nimble\Sek_Input_Basic' ) ) :
class Sek_Input_Basic extends Sek_Input_Abstract {

    public function __construct( $args ) {
        $this->attributes   = array_merge( $this->attributes, array( 'value', 'type' ) );
        parent::__construct( $args );
    }

    public function html() {
        return sprintf( '<input %s/>',
            $this->get_attributes_html()
        );
    }
}
endif;

if ( ! class_exists( '\Nimble\Sek_Input_Hidden' ) ) :
class Sek_Input_Hidden extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args[ 'type' ]     = 'hidden';
        parent::__construct( $args );
    }
}
endif;

if ( ! class_exists( '\Nimble\Sek_Input_Text' ) ) :
class Sek_Input_Text extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args               = is_array( $args ) ? $args : array();
        $args[ 'type' ]     = 'text';
        $args[ 'filter' ]   = FILTER_SANITIZE_STRING;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return sanitize_text_field( $value );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_Email' ) ) :
class Sek_Input_Email extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'text';
        $args[ 'filter' ] = FILTER_SANITIZE_EMAIL;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        if ( ! is_email( $value ) ) {
            return '';
        }
        return sanitize_email($value);
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_URL' ) ) :
class Sek_Input_URL extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'url';
        $args[ 'filter' ] = FILTER_SANITIZE_URL;

        parent::__construct( $args );
    }

    public function sanitize($value) {
        return esc_url_raw( $value );
    }

    public function escape( $value ){
        return esc_url( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_Submit' ) ) :
class Sek_Input_Submit extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'submit';
        $args             = wp_parse_args($args, [
            'value' => esc_html__( 'Contact', 'text_doma' ),
        ]);

        parent::__construct( $args );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;



if ( ! class_exists( '\Nimble\Sek_Input_Textarea' ) ) :
class Sek_Input_Textarea extends Sek_Input_Abstract {

    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'textarea';

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return wp_kses_post($value);
    }

    public function escape( $value ) {
        return $this->sanitize( $value );
    }


    public function html() {
        return sprintf( '<textarea %1$s>%2$s</textarea>',
            $this->get_attributes_html(),
            $this->get_value()
        );
    }
}
endif;
?><?php
/*
*
* Mailer class definition
*
*/
if ( ! class_exists( '\Nimble\Sek_Mailer' ) ) :
class Sek_Mailer {
    private $form;
    private $status;
    private $messages;
    private $invalid_field = false;
    public $recaptcha_errors = '_no_error_';//will store array( 'endpoint' => $endpoint, 'request' => $request, 'response' => '' );

    public function __construct( Sek_Form $form ) {
        $this-> form = $form;

        $this->messages = array(
            //status          => message
            //'not_sent'        => __( 'Message was not sent. Try Again.', 'text_doma'),
            //'sent'            => __( 'Thanks! Your message has been sent.', 'text_doma'),
            'aborted'         => __( 'Please supply correct information.', 'text_doma') //<-todo too much generic
        );
        $this->status = 'init';

        // Validate reCAPTCHA if submitted
        // When sek_is_recaptcha_globally_enabled(), the hidden input 'nimble_recaptcha_resp' is rendered with a value set to a token remotely fetched with a js script
        // @see print_recaptcha_inline_js
        // on submission, we get the posted token value, and validate it with a remote http request to the google api
        if ( isset( $_POST['nimble_recaptcha_resp'] ) ) {
            if ( !$this->validate_recaptcha( $_POST['nimble_recaptcha_resp'] ) ) {
                $this->status = 'recaptcha_fail';
                if ( sek_is_dev_mode() ) {
                    sek_error_log('reCAPTCHA failure', $this->recaptcha_errors );
                }
            }
        }
    }


    //@return bool
    private function validate_recaptcha( $recaptcha_token ) {
        $is_valid = false;
        $endpoint = 'https://www.google.com/recaptcha/api/siteverify';
        $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
        $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();
        // the user did not enter the key yet.
        // let's validate
        if ( empty($global_recaptcha_opts['private_key']) )
          return true;

        //$public = $global_recaptcha_opts['public_key'];
        $request = array(
            'body' => array(
                'secret' => $global_recaptcha_opts['private_key'],
                'response' => $recaptcha_token
            ),
        );

        // cache the recaptcha_errors
        $response = wp_remote_post( esc_url_raw( $endpoint ), $request );
        if ( is_array( $response ) ) {
            $maybe_recaptcha_errors = wp_remote_retrieve_body( $response );
            $maybe_recaptcha_errors = json_decode( $maybe_recaptcha_errors );
            $maybe_recaptcha_errors = is_object($maybe_recaptcha_errors) ? (array)$maybe_recaptcha_errors : $maybe_recaptcha_errors;
            if ( is_array( $maybe_recaptcha_errors ) && isset( $maybe_recaptcha_errors['error-codes'] ) && is_array( $maybe_recaptcha_errors['error-codes'] ) ) {
                $this->recaptcha_errors = implode(', ', $maybe_recaptcha_errors['error-codes'] );
            }

        }

        //sek_error_log('reCAPTCHA response ?', $response );
        // There
        if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
            $this->recaptcha_errors = sprintf( __('There was a problem when performing the reCAPTCHA http request.') );
            return $is_valid;
        }

        // At this point, we can check the score if there not already an error messages, like a re-submission problem for example
        if ( '_no_error_' === $this->recaptcha_errors ) {
            $response_body = wp_remote_retrieve_body( $response );
            $response_body = json_decode( $response_body, true );

            // see https://developers.google.com/recaptcha/docs/v3#score
            $score = isset( $response_body['score'] ) ? $response_body['score'] : 0;

            // get the user defined threshold
            // must be normalized to be 0 >= threshold >= 1
            $user_score_threshold = array_key_exists('score', $global_recaptcha_opts) ? $global_recaptcha_opts['score'] : 0.5;
            $user_score_threshold = !is_numeric( $user_score_threshold ) ? 0.5 : $user_score_threshold;
            $user_score_threshold = $user_score_threshold > 1 ? 1 : $user_score_threshold;
            $user_score_threshold = $user_score_threshold < 0 ? 0 : $user_score_threshold;
            $user_score_threshold = apply_filters( 'nimble_recaptcha_score_treshold', $user_score_threshold );

            $is_valid = $is_human = $user_score_threshold < $score;
            if ( !$is_valid ) {
                $this->recaptcha_errors = sprintf( __('Google reCAPTCHA returned a score of %s, which is lower than your threshold of %s.', 'text_dom' ), $score, $user_score_threshold );
            }
        }

        return $is_valid;
    }


    // Depending on the user options, some fields might exists in the $form object
    // We need to check their existence ( @see https://github.com/presscustomizr/nimble-builder/issues/399 )
    public function maybe_send( $form_composition, $module_model ) {
        // the captcha validation has been made on Sek_Mailer instantiation
        if ( 'recaptcha_fail' === $this->status ) {
            return;
        }

        //sek_error_log('$form_composition ??', $form_composition );
        //sek_error_log('$module_model ??', $module_model );
        //sek_error_log('$this->form ??', $form_composition , $this->form );

        $invalid_field = $this->form->has_invalid_field();
        if ( false !== $invalid_field ) {
            $this->status = 'aborted';
            $this->invalid_field = $invalid_field;
            return;
        }

        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        //<-allow html?->TODO: turn into option
        $allow_html     = true;

        $sender_email   = $this->form->get_field('nimble_email')->get_input()->get_value();

        // Define a default sender name + make sure the field exists
        // fixes https://github.com/presscustomizr/nimble-builder/issues/513
        $sender_name    = __('Someone', 'text_doma');
        $sender_name_is_set = false;
        if ( is_array( $form_composition ) && array_key_exists( 'nimble_name', $form_composition ) ) {
            $sender_name_candidate  = sprintf( '%1$s', $this->form->get_field('nimble_name')->get_input()->get_value() );
            if ( !empty( $sender_name_candidate ) ) {
                $sender_name = $sender_name_candidate;
                $sender_name_is_set = true;
            }
        }

        $sender_body_message = null === $this->form->get_field('nimble_message') ? '' : $this->form->get_field('nimble_message')->get_input()->get_value();

        if ( array_key_exists( 'recipients', $submission_options ) ) {
            $recipient      = $submission_options['recipients'];
        } else {
            $recipient      = get_option( 'admin_email' );
        }

        if ( array_key_exists( 'nimble_subject' , $form_composition ) ) {
            $subject = $this->form->get_field('nimble_subject')->get_input()->get_value();
        } else if ( $sender_name_is_set ) {
            $subject = sprintf( __( '%1$s sent a message from %2$s', 'text_doma' ), $sender_name, get_bloginfo( 'name' ) );
        } else {
            $subject = sprintf( __( 'Someone sent a message from %1$s', 'text_doma' ), get_bloginfo( 'name' ) );
        }



        // $sender_website = sprintf( __( 'Website: %1$s %2$s', 'text_doma' ),
        //     $this->form->get_field('website')->get_input()->get_value(),
        //     $allow_html ? '<br><br><br>': "\r\n\r\n\r\n"
        // );

        // the sender's email is written in the email's header reply-to field.
        // But it is also written inside the message body following this issue, https://github.com/presscustomizr/nimble-builder/issues/218
        $before_message = sprintf( '%1$s: %2$s &lt;%3$s&gt;', __('From', 'text_doma'), $sender_name, $sender_email );//$sender_website;
        $before_message .= sprintf( '<br>%1$s: %2$s', __('Subject', 'text_doma'), $subject );
        $after_message  = '';

        if ( array_key_exists( 'email_footer', $submission_options ) ) {
            $email_footer = $submission_options['email_footer'];
        } else {
            $email_footer = sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                get_bloginfo( 'name' ),
                get_site_url( 'url' )
            );
        }

        if ( !empty( $sender_body_message ) ) {
            $sender_body_message = sprintf( '<br><br>%1$s: <br>%2$s',
                __('Message body', 'text_doma'),
                //$allow_html ? '<br><br>': "\r\n\r\n",
                $sender_body_message
            );
        }

        $body = sprintf( '%1$s%2$s%3$s%4$s%5$s',
            $before_message,
            $sender_body_message,
            $after_message,
            $allow_html ? '<br><br>--<br>': "\r\n\r\n--\r\n",
            $email_footer
        );

        $headers        = array();
        $headers[]      = $allow_html ? 'Content-Type: text/html' : '';
        $headers[]      = 'charset=UTF-8'; //TODO: maybe turn into option

        $headers[]      = sprintf( 'From: %1$s <%2$s>', $sender_name, $this->get_from_email() );
        $headers[]      = sprintf( 'Reply-To: %1$s <%2$s>', $sender_name, $sender_email );

        $this->status   = wp_mail( $recipient, $subject, $body, $headers ) ? 'sent' : 'not_sent';
    }



    public function get_status() {
        return $this->status;
    }


    public function get_message( $status, $module_model ) {
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        $submission_message = isset( $this->messages[ $status ] ) ? $this->messages[ $status ] : '';

        // the check with strlen( preg_replace('/\s+/' ... ) allow user to "hack" the custom submission message with a blank space
        // because if the field is empty it will fallback on the default value
        switch( $status ) {
            case 'not_sent' :
                if ( array_key_exists( 'failure_message', $submission_options ) && !empty( $submission_options['failure_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['failure_message'] ) ) ) {
                    $submission_message = $submission_options['failure_message'];
                }
            break;
            case 'sent' :
                if ( array_key_exists( 'success_message', $submission_options ) && !empty( $submission_options['success_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['success_message'] ) ) ) {
                    $submission_message = $submission_options['success_message'];
                }
            break;
            case 'aborted' :
                if ( array_key_exists( 'error_message', $submission_options ) && !empty( $submission_options['error_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['error_message'] ) ) ) {
                    $submission_message = $submission_options['error_message'];
                }
                if ( false !== $this->invalid_field ) {
                    $submission_message = sprintf( __( '%1$s : <strong>%2$s</strong>.', 'text-domain' ), $submission_message, $this->invalid_field );
                }
            break;
            case 'recaptcha_fail' :
                $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
                $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();
                if ( true === sek_booleanize_checkbox_val($global_recaptcha_opts['show_failure_message']) ) {
                    $submission_message = !empty($global_recaptcha_opts['failure_message']) ? $global_recaptcha_opts['failure_message'] : '';
                }
            break;
        }

        if ( '_no_error_' !== $this->recaptcha_errors && current_user_can( 'customize' ) ) {
              $submission_message .= sprintf( '<br/>%s : <i>%s</i>', __('reCAPTCHA problem (only visible by a logged in administrator )', 'text_doma'), $this->recaptcha_errors );
        }
        return $submission_message;
    }




    // inspired from wpcf7
    private function get_from_email() {
        $admin_email = get_option( 'admin_email' );
        $sitename    = strtolower( $_SERVER['SERVER_NAME'] );

        if ( in_array( $sitename, array( 'localhost', '127.0.0.1' ) ) ) {
            return $admin_email;
        }

        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }

        if ( strpbrk( $admin_email, '@' ) == '@' . $sitename ) {
            return $admin_email;
        }

        return 'wordpress@' . $sitename;
    }
}//Sek_Mailer
endif;












// inspired by wpcf7
function sek_simple_form_mail_template() {
    $template = array(
        'subject' =>
            sprintf( __( '%1$s: new contact request', 'text_doma' ),
                get_bloginfo( 'name' )
            ),
        'sender' => sprintf( '[your-name] <%s>', simple_form_from_email() ),
        'body' =>
            /* translators: %s: [your-name] <[your-email]> */
            sprintf( __( 'From: %s', 'text_doma' ),
                '[your-name] <[your-email]>' ) . "\n"
            /* translators: %s: [your-subject] */
            . sprintf( __( 'Subject: %s', 'text_doma' ),
                '[your-subject]' ) . "\n\n"
            . __( 'Message Body:', 'text_doma' )
                . "\n" . '[your-message]' . "\n\n"
            . '-- ' . "\n"
            /* translators: 1: blog name, 2: blog URL */
            . sprintf(
                __( 'This e-mail was sent from a contact form on %1$s (%2$s)', 'text_doma' ),
                get_bloginfo( 'name' ),
                get_bloginfo( 'url' ) ),
        'recipient' => get_option( 'admin_email' ),
        'additional_headers' => 'Reply-To: [your-email]',
        'attachments' => '',
        'use_html' => 0,
        'exclude_blank' => 0,
    );

    return $template;
}//simple_form_mail_template


?><?php

if ( ! class_exists( '\Nimble\Sek_Nimble_Manager' ) ) :
  final class Sek_Nimble_Manager extends Sek_Simple_Form {}
endif;

function Nimble_Manager( $params = array() ) {
    return Sek_Nimble_Manager::get_instance( $params );
}

Nimble_Manager();
?>