<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

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
    if ( !is_array( $rules ) )
      return array();

    $default_data = array( 'wght' => '1px', 'col' => '#000000' );
    if ( array_key_exists('_all_', $border_settings) ) {
        $default_data = wp_parse_args( $border_settings['_all_'] , $default_data );
    }

    $css_rules = array();
    foreach ( $border_settings as $border_dimension => $data ) {
        if ( !is_array( $data ) ) {
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
            if ( !empty( $data[ 'col' ] ) ) {
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
    if ( !is_array( $rules ) )
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

    if ( !empty( $css_rules ) ) {
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
// May 2020 => @todo those media query css rules doesn't take into account the custom breakpoint if set
function sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $spacing_settings, $css_selectors = '', $custom_column_breakpoint = false ) {
    //spacing
    if ( empty( $spacing_settings ) || !is_array( $spacing_settings ) )
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

    // Default breakpoint
    // may be overriden by user defined once
    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];//max-width: 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    // since https://github.com/presscustomizr/nimble-builder/issues/552,
    // we need the parent_level id ( <=> the level on which the CSS rule is applied ) to determine if there's any inherited custom breakpoint to use
    // Exceptions :
    // - when generating media queries for local options, the level_id is set to '_excluded_from_section_custom_breakpoint_', @see sek_add_raw_local_widths_css()
    if ( false !== $custom_column_breakpoint ) {
            $tablet_breakpoint = $custom_column_breakpoint;// default is Sek_Dyn_CSS_Builder::$breakpoints['md'] <=> max-width: 768
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
    if ( !empty( $_pad_marg[ 'desktop' ] ) ) {
        // if ( false !== $custom_column_breakpoint ) {
        //     // added for https://github.com/presscustomizr/nimble-builder/issues/665
        //     $_pad_marg[ 'desktop' ][ 'mq' ] = "(min-width: {$tablet_breakpoint}px)";
        // } else {
        //     $_pad_marg[ 'desktop' ][ 'mq' ] = null;
        // }
        $_pad_marg[ 'desktop' ][ 'mq' ] = null;
    }

    if ( !empty( $_pad_marg[ 'tablet' ] ) ) {
        $_pad_marg[ 'tablet' ][ 'mq' ]  = '(max-width:'. ( $tablet_breakpoint - 1 ) . 'px)'; //max-width: 767
    }

    if ( !empty( $_pad_marg[ 'mobile' ] ) ) {
        $_pad_marg[ 'mobile' ][ 'mq' ]  = '(max-width:'. ( $mobile_breakpoint - 1 ) . 'px)'; //max-width: 575
    }

    foreach( array_filter( $_pad_marg ) as $_spacing_rules ) {
        $css_rules = implode(';',
            array_map( function( $key, $value ) {
                return "$key:{$value};";
            }, array_keys( $_spacing_rules[ 'rules' ] ), array_values( $_spacing_rules[ 'rules' ] )
        ) );
        if ( array_key_exists('mq', $_spacing_rules) ) {
            $rules[] = array(
                'selector' => $css_selectors,//'[data-sek-id="'.$level['id'].'"]',
                'css_rules' => $css_rules,
                'mq' =>$_spacing_rules[ 'mq' ]
            );
        }
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
            $tablet_breakpoint = sek_get_section_or_global_tablet_breakpoint_for_css_rules( $level_id, $for_responsive_columns = false );// default is Sek_Dyn_CSS_Builder::$breakpoints['md'] <=> max-width: 768

            // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
            $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;
        }
    }

    $css_value_by_devices = $params['value'];
    $media_q = array('desktop' => null , 'tablet' => null , 'mobile' => null );

    if ( !empty( $css_value_by_devices ) ) {
          if ( !empty( $css_value_by_devices[ 'desktop' ] ) ) {
              $media_q[ 'desktop' ] = null;
          }

          if ( !empty( $css_value_by_devices[ 'tablet' ] ) ) {
              $media_q[ 'tablet' ]  = '(max-width:'. ( $tablet_breakpoint - 1 ) . 'px)'; // default is max-width: 767
          }

          if ( !empty( $css_value_by_devices[ 'mobile' ] ) ) {
              $media_q[ 'mobile' ]  = '(max-width:'. ( $mobile_breakpoint - 1 ) . 'px)'; // default is max-width: 575
          }

          // $css_value_by_devices looks like
          // array(
          //     'desktop' => '30px',
          //     'tablet' => '',
          //     'mobile' => ''
          // );
          foreach ( $css_value_by_devices as $device => $val ) {
              if ( !in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
                  sek_error_log( __FUNCTION__ . ' => error => unknown device : ' . $device );
                  continue;
              }
              if ( !empty(  $val ) ) {
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
                      'mq' => $media_q[ $device ]
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
            $tablet_breakpoint = sek_get_section_or_global_tablet_breakpoint_for_css_rules( $level_id, $for_responsive_columns = false );// default is Sek_Dyn_CSS_Builder::$breakpoints['md'] <=> max-width: 768

            // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
            $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;
        }
    }

    $css_rules_by_device = $params['css_rules_by_device'];
    $media_q = array('desktop' => null , 'tablet' => null , 'mobile' => null );

    if ( !empty( $css_rules_by_device ) ) {
          if ( !empty( $css_rules_by_device[ 'desktop' ] ) ) {
              $media_q[ 'desktop' ] = null;
          }

          if ( !empty( $css_rules_by_device[ 'tablet' ] ) ) {
              $media_q[ 'tablet' ]  = '(max-width:'. ( $tablet_breakpoint - 1 ) . 'px)'; //max-width: 767
          }

          if ( !empty( $css_rules_by_device[ 'mobile' ] ) ) {
              $media_q[ 'mobile' ]  = '(max-width:'. ( $mobile_breakpoint - 1 ) . 'px)'; //max-width: 575
          }
          foreach ( $css_rules_by_device as $device => $rules_for_device ) {
              $rules[] = array(
                  'selector' => $params['selector'],
                  'css_rules' => $rules_for_device,
                  'mq' => $media_q[ $device ]
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
//
// the 'for_responsive_columns' param has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
// when for columns, we always apply the custom breakpoint defined by the user
// otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
// 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
function sek_get_section_or_global_tablet_breakpoint_for_css_rules( $level_id = '', $for_responsive_columns = false ) {
    //sek_error_log('ALORS CLOSEST PARENT SECTION MODEL ?' . $level_id , sek_get_closest_section_custom_breakpoint( $level_id ) );

    // define a default breakpoint : 768
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints[Sek_Dyn_CSS_Builder::COLS_MOBILE_BREAKPOINT];//COLS_MOBILE_BREAKPOINT = 'md' <=> 768px

    // Is there a custom breakpoint set by a parent section?
    // Order :
    // 1) custom breakpoint set on a nested section
    // 2) custom breakpoint set on a regular section
    //sek_error_log('WE SEARCH FOR => ' . $level_id );
    $closest_section_custom_breakpoint = sek_get_closest_section_custom_breakpoint( array( 'searched_level_id' => $level_id, 'for_responsive_columns' => false ) );
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
    if ( !is_scalar( $value ) )
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

?>