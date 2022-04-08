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

?><?php
// The base fmk is loaded @after_setup_theme:10
add_action( 'after_setup_theme', '\Nimble\sek_schedule_module_registration', 50 );

// On front we register only the necessary modules
// When customizing, we register all of them
// On admin, we register none
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
        // June 2020 : for https://github.com/presscustomizr/nimble-builder/issues/520
        sek_register_user_sections_module();
    } else {
        // Condition !is_admin() added in april 2020
        // fixes https://github.com/presscustomizr/nimble-builder/issues/658
        if ( !is_admin() ) {
            add_action( 'wp', '\Nimble\sek_register_active_modules_on_front', PHP_INT_MAX );
        }
    }
}

// @return void();
// @hook 'after_setup_theme'
function sek_register_modules_when_customizing_or_ajaxing() {
    $modules = array_merge(
        SEK_Front_Construct::$ui_picker_modules,
        // June 2020 filter added for https://github.com/presscustomizr/nimble-builder-pro/issues/6
        apply_filters( 'nb_level_module_collection', SEK_Front_Construct::$ui_level_modules ),
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
function sek_register_active_modules_on_front() {
    sek_register_modules_when_not_customizing_and_not_ajaxing();
}


// @param $skope_id added in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/657
function sek_register_modules_when_not_customizing_and_not_ajaxing( $skope_id = '' ) {
    $contextually_actives_raw = sek_get_collection_of_contextually_active_modules( $skope_id );
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
        apply_filters( 'nb_level_module_collection', SEK_Front_Construct::$ui_level_modules ),
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
            $params = apply_filters( "nimble_module_params_for_{$module_name}", $fn() );
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
            'name' => '',
            'section_collection' => array()
        ));

        // normalize the module params
        $normalized_params = $default_module_params;
        $normalized_params['module_type'] = $module_type;
        $normalized_params['name'] = $module_params['name'];
        $normalized_params['tmpl']['item-inputs']['sections']['section_collection'] = $module_params['section_collection'];
        CZR_Fmk_Base()->czr_pre_register_dynamic_module( $normalized_params );
    }

}

// @return void();
// @hook 'after_setup_theme'
// June 2020 for https://github.com/presscustomizr/nimble-builder/issues/520
function sek_register_user_sections_module() {
    $normalized_params = array(
        'dynamic_registration' => true,
        'module_type' => 'sek_my_sections_sec_picker_module',
        'name' => __('My sections', 'text-doma'),
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

    CZR_Fmk_Base()->czr_pre_register_dynamic_module( $normalized_params );
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
        'name' => __('Select a content type', 'text_doma'),
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
        'name' => __('Pick a module', 'text_doma'),
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
// now registered with sek_register_prebuilt_section_modules() in add_action( 'after_setup_theme', '\Nimble\sek_schedule_module_registration', 50 );
// and then populated in AJAX from a local JSON since https://github.com/presscustomizr/nimble-builder/issues/431

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
        //'name' => __('Option switcher', 'text_doma'),
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
        //'name' => __('Background', 'text_doma'),
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
                'bg-use-post-thumb' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use the contextual post thumbnail', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'default'     => 0,
                    'notice_after' => __('When enabled and possible, Nimble will use the post thumbnail.', 'text_doma'),
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
    $bg_options = ( !empty( $options[ 'bg' ] ) && is_array( $options[ 'bg' ] ) ) ? $options[ 'bg' ] : array();
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
    if ( !empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
        // deactivated when customizing @see function sek_is_img_smartload_enabled()

        //$background_properties[ 'background-image' ] = 'url("'. wp_get_attachment_url( $bg_options[ 'bg-image'] ) .'")';

        // Img Bg Position
        // 'center' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( !empty( $bg_options[ 'bg-position'] ) && 'center' != $bg_options[ 'bg-position'] ) {
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
                    if ( !in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
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
        if ( !empty( $bg_options['bg-scale'] ) && 'default' != $bg_options['bg-scale'] && 'cover' != $bg_options['bg-scale'] ) {
            //When specifying a background-size value, it must immediately follow the background-position value.
            $background_properties['background-size'] = $bg_options['bg-scale'];
        }

        // add no-repeat by default?
        // 'no-repeat' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( !empty( $bg_options['bg-repeat'] ) && 'default' != $bg_options['bg-repeat'] ) {
            $background_properties['background-repeat'] = $bg_options['bg-repeat'];
        }

        // write the bg-attachment rule only if true <=> set to "fixed"
        if ( !empty( $bg_options['bg-attachment'] ) && sek_is_checked( $bg_options['bg-attachment'] ) ) {
            $background_properties['background-attachment'] = 'fixed';
        }

    }

    //background color (needs validation: we need a sanitize hex or rgba color)
    if ( !empty( $bg_options['bg-color'] ) ) {
        $background_properties['background-color'] = $bg_options[ 'bg-color' ];
    }


    //build background rule
    if ( !empty( $background_properties ) ) {
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
    if ( ( !empty( $bg_options['bg-image']) || sek_is_checked( $bg_options['bg-use-post-thumb'] ) || ( sek_is_checked( $bg_options['bg-use-video'] ) && !empty( $bg_options['bg-video'] ) ) ) && !empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {
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
    }//if ( !empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {}

    return $rules;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_text_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_text_module',
        //'name' => __('Text', 'text_doma'),
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
                    'css_identifier' => 'font_family',
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
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
        //'name' => __('Borders', 'text_doma'),
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
                    'default' => 0,
                    'refresh_markup' => true
                )
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_border', 10, 3 );
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
    $normalized_border_options = ( !empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) ) ? $options[ 'border' ] : array();
    $normalized_border_options = wp_parse_args( $normalized_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $normalized_border_options ) )
      return $rules;

    $border_settings = !empty( $normalized_border_options[ 'borders' ] ) ? $normalized_border_options[ 'borders' ] : FALSE;
    $border_type = $normalized_border_options[ 'border-type' ];
    $has_border_settings  = FALSE !== $border_settings && is_array( $border_settings ) && !empty( $border_type ) && 'none' != $border_type;

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options( $rules, $border_settings, $border_type, '[data-sek-id="'.$level['id'].'"]'  );
    }

    $has_border_radius = !empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) && !empty( $options[ 'border' ]['border-radius'] );
    if ( $has_border_radius ) {
        $radius_settings = $normalized_border_options['border-radius'];
        $rules = sek_generate_css_rules_for_border_radius_options( $rules, $normalized_border_options['border-radius'], '[data-sek-id="'.$level['id'].'"]' );
    }

    return $rules;
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_height_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_height_module',
        //'name' => __('Height options', 'text_doma'),
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
                ),
                'zindex' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('z-index', 'text_doma'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    // 'unit' => '%',
                    'default'  => '0',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
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
    if ( !empty( $height_options[ 'v_alignment' ] ) ) {
        if ( !is_array( $height_options[ 'v_alignment' ] ) ) {
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
    if ( !empty( $height_options[ 'height-type' ] ) ) {
        if ( 'custom' === $height_options[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $height_options ) ? $height_options[ 'custom-height' ] : array();
            $selector = '[data-sek-id="'.$level['id'].'"]';
            if ( !is_array( $custom_user_height ) ) {
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
                if ( !empty( $numeric ) ) {
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
    if ( !empty( $height_options[ 'overflow_hidden' ] ) && sek_booleanize_checkbox_val( $height_options[ 'overflow_hidden' ] ) ) {
        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'css_rules' => 'overflow:hidden',
            'mq' =>null
        );
    }

    // Z-INDEX
    // implemented to fix https://github.com/presscustomizr/nimble-builder/issues/365
    if ( !empty( $height_options[ 'zindex' ] ) ) {
        $numeric = sek_extract_numeric_value( $height_options[ 'zindex' ] );
        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'css_rules' => 'position:relative;z-index:' . $numeric,
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
?><?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE ( SPECIFIC FOR COLUMNS ). See https://github.com/presscustomizr/nimble-builder/issues/868
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_spacing_module_for_columns() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_spacing_module_for_columns',
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
                    'default'     => array( 'desktop' => array('padding-left' => '10', 'padding-right' => '10') ),
                    'has_device_switcher' => true
                )
            )
        )
    );
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_width_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_module',
        //'name' => __('Width options', 'text_doma'),
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
                    'notice_after' => __('Horizontal alignment can only be applied with a custom module width < to the parent column\'s width'),
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
    if ( empty( $options[ 'width' ] ) || !is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = is_array( $options[ 'width' ] ) ? $options[ 'width' ] : array();

    // ALIGNMENT BY DEVICE
    if ( !empty( $width_options[ 'h_alignment' ] ) ) {
        if ( !is_array( $width_options[ 'h_alignment' ] ) ) {
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
    if ( !empty( $width_options[ 'width-type' ] ) ) {
        if ( 'custom' == $width_options[ 'width-type' ] && array_key_exists( 'custom-width', $width_options ) ) {
            $user_custom_width_value = $width_options[ 'custom-width' ];
            $selector = '[data-sek-id="'.$module['id'].'"]';

            if ( !empty( $user_custom_width_value ) && !is_array( $user_custom_width_value ) ) {
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
                if ( !empty( $numeric ) ) {
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
        //'name' => __('Column width', 'text_doma'),
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
    if ( empty( $options[ 'width' ] ) || !is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = is_array( $options[ 'width' ] ) ? $options[ 'width' ] : array();

    // ALIGNMENT BY DEVICE
    // if ( !empty( $width_options[ 'h_alignment' ] ) ) {
    //     if ( !is_array( $width_options[ 'h_alignment' ] ) ) {
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
    if ( !empty( $width_options[ 'width-type' ] ) ) {
        if ( 'custom' == $width_options[ 'width-type' ] && array_key_exists( 'custom-width', $width_options ) ) {
            $user_custom_width_value = $width_options[ 'custom-width' ];
            $selector = '[data-sek-id="'.$column['id'].'"]';

            if ( !empty( $user_custom_width_value ) && !is_array( $user_custom_width_value ) ) {
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
                if ( !empty( $numeric ) ) {
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
        //'name' => __('Width options', 'text_doma'),
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
                    'max' => 1500,
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
                    'max' => 1500,
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
    if ( empty( $options[ 'width' ] ) || !is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = $options[ 'width' ];
    $user_defined_widths = array();

    if ( !empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = 'body .nb-loc [data-sek-id="'.$section['id'].'"]';
    }
    if ( !empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = 'body .nb-loc [data-sek-id="'.$section['id'].'"] > .sek-container-fluid > .sek-sektion-inner';
    }

    if ( empty( $user_defined_widths ) )
      return $rules;

    // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( !empty( $width_options[ $width_opt_name ] ) && !is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
        // If not set in the width options , it means that it is equal to default
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || !is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();
        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $padding_of_the_parent_container[$device] = 'inherit';
            $numeric = sek_extract_numeric_value( $num_unit );
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
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
                'selector' => 'body .nb-loc [data-sek-id="'.$section['id'].'"] > .sek-container-fluid',
                'level_id' => $section['id']
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => 'body .nb-loc [data-sek-id="'.$section['id'].'"] > .sek-container-fluid',
                'level_id' => $section['id']
            ), $rules );
        }

        if ( !empty( $margin_value ) ) {
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
        //'name' => __('Set a custom anchor', 'text_doma'),
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
        //'name' => __('Set visibility on devices', 'text_doma'),
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

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_cust_css_level() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('custom CSS on a per level basis (section, column, module ).', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_cust_css_level',
        //'name' => __('Width options', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        // 'starting_value' => array(
        //     'outer-section-width' => '100%',
        //     'inner-section-width' => '100%'
        // ),
        'tmpl' => array(
            'item-inputs' => array(
                'custom_css' => array(
                    'input_type'  => 'inactive',
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'html_before' => $pro_text . '<hr/>'
                )
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_animation_module() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('add animations to anything', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_animation_module',
        //'name' => __('Width options', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        // 'starting_value' => array(
        //     'outer-section-width' => '100%',
        //     'inner-section-width' => '100%'
        // ),
        'tmpl' => array(
            'item-inputs' => array(
                'anim_type' => array(
                    'input_type'  => 'inactive',
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'html_before' => $pro_text . '<hr/>'
                )
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_template() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_template',
        //'name' => __('Template for the current page', 'text_doma'),
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
        //'name' => __('Width settings of the sections in the current page', 'text_doma'),
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
                    'max' => 1500,
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
                    'max' => 1500,
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
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? sanitize_text_field($_POST['local_skope_id']) : skp_build_skope_id() );

    if ( !is_array( $local_options ) || empty( $local_options['local_options']) || empty( $local_options['local_options']['widths'] ) )
      return $css;

    $width_options = $local_options['local_options']['widths'];
    $user_defined_widths = array();

    if ( !empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = '.nb-loc [data-sek-level="section"]:not([data-sek-is-nested="true"])';
    }
    if ( !empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = '.nb-loc [data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner';
    }

    $rules = array();

    // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( !empty( $width_options[ $width_opt_name ] ) && !is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
        // If not set in the width options , it means that it is equal to default
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || !is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            $padding_of_the_parent_container[$device] = 'inherit';
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
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
                'selector' => '.nb-loc [data-sek-level="section"] > .sek-container-fluid',
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => '.nb-loc [data-sek-level="section"] > .sek-container-fluid',
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
        }

        if ( !empty( $margin_value ) ) {
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
        //'name' => __('Custom CSS for the sections of the current page', 'text_doma'),
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
                    'refresh_stylesheet' => false,
                    'refresh_preview' => false,
                    'refresh_css_via_post_message' => true
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
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? sanitize_text_field($_POST['local_skope_id']) : skp_build_skope_id() );
    if ( is_array( $local_options ) && !empty( $local_options['local_options']) && !empty( $local_options['local_options']['custom_css'] ) ) {
        $options = $local_options['local_options']['custom_css'];
        if ( !empty( $options['local_custom_css'] ) ) {
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
        //'name' => __('Reset the sections of the current page', 'text_doma'),
        'tmpl' => array(
            'item-inputs' => array(
                // Added April 2021 for #478
                // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
                // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
                // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
                // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
                'inherit_group_scope' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('After removal : inherit the site template if specified', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> <a href="%2$s" target="_blank" rel="noopener noreferrer">%1$s</a></span>',
                        __('How to use site templates with Nimble Builder ?'),
                        'https://docs.presscustomizr.com/article/428-how-to-use-site-templates-with-nimble-builder'
                    ),
                    'notice_after' => __('If a site template is defined for this context, this page will inherit the site template by default, unless this option is unchecked.', 'text_doma'),
                    //'notice_after' => __( 'Check this option if you want to keep the existing sections of this page, and combine them with the imported ones.', 'text_doma'),
                ),
                'reset_local' => array(
                    'input_type'  => 'reset_button',
                    'title'       => __( 'Remove all sections and Nimble Builder options of this page' , 'text_doma' ),
                    'scope'       => 'local',
                    'html_before' => '<hr/>',
                    'notice_after' => __('This will remove the options and sections created for the currently previewed page only. All other sections and options in other contexts will be preserved.', 'text_doma'),
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
        //'name' => __('Performance optimizations', 'text_doma'),
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
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('sticky header, header over content, sticky footer, search icon, hamburger color, ...', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_header_footer',
        //'name' => __('Page header', 'text_doma'),
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
                    'html_after' => $pro_text
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
        //'name' => __('Revision history', 'text_doma'),
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
        //'name' => __('Export / Import', 'text_doma'),
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
                ),
                // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                'import_img' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Import images in your media library.', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'notice_after' => __( 'When this option is unchecked, Nimble Builder will not import images and use instead the url of the original images.', 'text_doma'),
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
        //'name' => __('Global text', 'text_doma'),
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
                    'html_before' => '<h3>' . __('GLOBAL TEXT STYLE') .'</h3>',
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
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
                    'html_before' => '<hr/><h3>' . __('GLOBAL STYLE OPTIONS FOR HEADINGS') .'</h3>',
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
                ),
            )
        )//tmpl
    );
}


// Nimble implements an inheritance for both logic, determined by the css selectors, and the media query rules.
// For example, an inner width of 85% applied for skope will win against the global one, but can be overriden by a specific inner width set at a section level.
// October 2020 => it's better to write this global style inline than to hook in filter 'nimble_get_dynamic_stylesheet', as we do for local width for example, implying that we may create a small useless global stylesheet.
// Because :
// 1) if user doesn't use any global header / footer, which is the most common case, we save an http request for a global stylesheet
// 2) the css rules generated for global text are very short and do not justify a new stylesheet
add_filter( 'nimble_set_global_inline_style', '\Nimble\sek_add_raw_global_text_css' );
// @hook 'wp_head'
function sek_add_raw_global_text_css( $global_css = '') {
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    if ( !is_array( $global_options ) || empty( $global_options['global_text'] ) || !is_array( $global_options['global_text'] ) )
      return $global_css;

    $text_options = $global_options['global_text'];
    if ( !is_array( $text_options  ) )
      return $global_css;

    $rules = array();
    // SELECTORS
    $default_text_selector = '.nb-loc [data-sek-level], .nb-loc [data-sek-level] p, .nb-loc [data-sek-level] .sek-btn, .nb-loc [data-sek-level] button, .nb-loc [data-sek-level] input, .nb-loc [data-sek-level] select, .nb-loc [data-sek-level] optgroup, .nb-loc [data-sek-level] textarea, .nb-loc [data-sek-level] ul, .nb-loc [data-sek-level] ol, .nb-loc [data-sek-level] li';
    $links_selector = '.nb-loc [data-sek-level] .sek-module-inner a';
    $links_hover_selector = '.nb-loc [data-sek-level] .sek-module-inner a:hover';
    $headings_selector = '.nb-loc [data-sek-level] h1, .nb-loc [data-sek-level] h2, .nb-loc [data-sek-level] h3, .nb-loc [data-sek-level] h4, .nb-loc [data-sek-level] h5, .nb-loc [data-sek-level] h6';

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

    $global_css = is_string($global_css) ? $global_css : '';
    $global_text_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );
    if ( is_string( $global_text_options_css ) && !empty( $global_text_options_css ) ) {
        $global_css .= $global_text_options_css;
    }
    return $global_css;
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_breakpoint() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_breakpoint',
        //'name' => __('Site wide breakpoint options', 'text_doma'),
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


add_filter('nimble_set_global_inline_style', '\Nimble\sek_write_global_custom_breakpoint' );
function sek_write_global_custom_breakpoint($global_css = '') {
    $custom_breakpoint = sek_get_global_custom_breakpoint();
    if ( $custom_breakpoint >= 1 ) {
        $global_css = is_string($global_css) ? $global_css : '';
        $css = '@media (min-width:' . $custom_breakpoint . 'px) {.sek-global-custom-breakpoint-col-8 {-ms-flex: 0 0 8.333%;flex: 0 0 8.333%;max-width: 8.333%;}.sek-global-custom-breakpoint-col-9 {-ms-flex: 0 0 9.090909%;flex: 0 0 9.090909%;max-width: 9.090909%;}.sek-global-custom-breakpoint-col-10 {-ms-flex: 0 0 10%;flex: 0 0 10%;max-width: 10%;}.sek-global-custom-breakpoint-col-11 {-ms-flex: 0 0 11.111%;flex: 0 0 11.111%;max-width: 11.111%;}.sek-global-custom-breakpoint-col-12 {-ms-flex: 0 0 12.5%;flex: 0 0 12.5%;max-width: 12.5%;}.sek-global-custom-breakpoint-col-14 {-ms-flex: 0 0 14.285%;flex: 0 0 14.285%;max-width: 14.285%;}.sek-global-custom-breakpoint-col-16 {-ms-flex: 0 0 16.666%;flex: 0 0 16.666%;max-width: 16.666%;}.sek-global-custom-breakpoint-col-20 {-ms-flex: 0 0 20%;flex: 0 0 20%;max-width: 20%;}.sek-global-custom-breakpoint-col-25 {-ms-flex: 0 0 25%;flex: 0 0 25%;max-width: 25%;}.sek-global-custom-breakpoint-col-30 {-ms-flex: 0 0 30%;flex: 0 0 30%;max-width: 30%;}.sek-global-custom-breakpoint-col-33 {-ms-flex: 0 0 33.333%;flex: 0 0 33.333%;max-width: 33.333%;}.sek-global-custom-breakpoint-col-40 {-ms-flex: 0 0 40%;flex: 0 0 40%;max-width: 40%;}.sek-global-custom-breakpoint-col-50 {-ms-flex: 0 0 50%;flex: 0 0 50%;max-width: 50%;}.sek-global-custom-breakpoint-col-60 {-ms-flex: 0 0 60%;flex: 0 0 60%;max-width: 60%;}.sek-global-custom-breakpoint-col-66 {-ms-flex: 0 0 66.666%;flex: 0 0 66.666%;max-width: 66.666%;}.sek-global-custom-breakpoint-col-70 {-ms-flex: 0 0 70%;flex: 0 0 70%;max-width: 70%;}.sek-global-custom-breakpoint-col-75 {-ms-flex: 0 0 75%;flex: 0 0 75%;max-width: 75%;}.sek-global-custom-breakpoint-col-80 {-ms-flex: 0 0 80%;flex: 0 0 80%;max-width: 80%;}.sek-global-custom-breakpoint-col-83 {-ms-flex: 0 0 83.333%;flex: 0 0 83.333%;max-width: 83.333%;}.sek-global-custom-breakpoint-col-90 {-ms-flex: 0 0 90%;flex: 0 0 90%;max-width: 90%;}.sek-global-custom-breakpoint-col-100 {-ms-flex: 0 0 100%;flex: 0 0 100%;max-width: 100%;}}';
          $global_css .= $css;
    }
    return $global_css;
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_widths',
        //'name' => __('Site wide width options', 'text_doma'),
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
                    'max' => 1500,
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
                    'max' => 1500,
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
// October 2020 => it's better to write this global style inline than to hook in filter 'nimble_get_dynamic_stylesheet', as we do for local width for example, implying that we may create a global stylesheet.
// Because :
// 1) if user doesn't use any global header / footer, which is the most common case, we save an http request for a global stylesheet
// 2) the css rules generated for custom section widths are very short and do not justify a new stylesheet
add_filter('nimble_set_global_inline_style', '\Nimble\sek_write_global_custom_section_widths', 1000 );
function sek_write_global_custom_section_widths($global_css = '') {
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );

    if ( !is_array( $global_options ) || empty( $global_options['widths'] ) || !is_array( $global_options['widths'] ) )
      return $global_css;

    $width_options = $global_options['widths'];
    $user_defined_widths = array();

    if ( !empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = '[data-sek-level="section"]';
    }
    if ( !empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = '[data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner';
    }

    $rules = array();

    // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( !empty( $width_options[ $width_opt_name ] ) && !is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
        // If not set in the width options , it means that it is equal to default
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || !is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            $padding_of_the_parent_container[$device] = 'inherit';
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
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

        if ( !empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector,
                'level_id' => '_excluded_from_section_custom_breakpoint_' //<= introduced in dec 2019 : https://github.com/presscustomizr/nimble-builder/issues/564
            ), $rules );
        }
    }//foreach

    $global_css = is_string($global_css) ? $global_css : '';
    $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );
    if ( is_string( $width_options_css ) && !empty( $width_options_css ) ) {
        $global_css .= $width_options_css;
    }
    return $global_css;
}
?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_reset() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_reset',
        //'name' => __('Reset global scope sections', 'text_doma'),
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
        //'name' => __('Site wide performance options', 'text_doma'),
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

                // 'use_partial_module_stylesheets' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Use partial CSS stylesheets for modules', 'text_doma'),
                //     'default'     => 0,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'html_before' => '<hr/><h3>' . __('STYLESHEETS') .'</h3>'
                // ),
                // 'print_partial_module_stylesheets_inline' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Print modules stylesheets inline', 'text_doma'),
                //     'default'     => 0,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                // ),
                // 'print_dyn_stylesheets_inline' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Print generated stylesheets inline', 'text_doma'),
                //     'default'     => 1,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'html_before' => '<hr/><h3>' . __('STYLESHEETS') .'</h3>'
                // ),
                'preload_google_fonts' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Preload Google fonts', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                // 'preload_font_awesome' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Preload Font Awesome icons', 'text_doma'),
                //     'default'     => 1,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20'
                // ),
                'load_assets_in_ajax' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Inject non priority assets dynamically in the page only when needed.', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr/><h3>' . __('SCRIPTS') .'</h3>'
                    //'notice_after' => __('Beta feature'),
                )
            )
        )//tmpl
    );
}

?><?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_header_footer() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('sticky header, header over content, sticky footer, search icon, hamburger color, ...', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_header_footer',
        //'name' => __('Site wide header', 'text_doma'),
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
                    'title_width' => 'width-100',
                    'html_after' => $pro_text
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
        //'name' => __('Protect your contact forms with Google reCAPTCHA', 'text_doma'),
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
        //'name' => __('Export / Import global sections', 'text_doma'),
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
                // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                // https://github.com/presscustomizr/nimble-builder/issues/676
                'import_img' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Import images in your media library.', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'notice_after' => __( 'When this option is unchecked, Nimble Builder will not import images and use instead the url of the original images.', 'text_doma'),
                )
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
        //'name' => __('Revision history', 'text_doma'),
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
                    'notice_before' => __('This is the revision history of the global sections displayed site wide.', 'text_doma') . ' ' . __('Like the global header and footer for example.', 'text_doma'),
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
        //'name' => __('Beta features', 'text_doma'),
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
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_site_tmpl_pickers() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('templates for custom post types, custom taxonomies, ....', 'text-doma') );
    }
    $default_params = [ 'site_tmpl_id' => '_no_site_tmpl_', 'site_tmpl_source' => 'user_tmpl', 'site_tmpl_title' => '' ];
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_site_tmpl_pickers',
        //'name' => __('Site wide header', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                // 'skp__home' => array(
                //     'input_type'  => 'site_tmpl_picker',
                //     'title'       => __('Template for home', 'text_doma'),
                //     'default'     => $default_params,
                //     //'refresh_preview' => true,
                //     'notice_before_title' => '',
                //     'width-100'   => true,
                //     'title_width' => 'width-100'
                // ),
                'skp__all_page' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for single pages', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false,
                    'html_before' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> <a href="%2$s" target="_blank" rel="noopener noreferrer">%1$s</a></span><hr/>',
                        __('How to use site templates with Nimble Builder ?'),
                        'https://docs.presscustomizr.com/article/428-how-to-use-site-templates-with-nimble-builder'
                    ),
                ),
                'skp__all_post' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for single posts', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_category' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for categories', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_post_tag' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for tags', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_author' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for authors', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_attachment'  => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for attachment pages', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                // this skope has no group skope => this is why we need to add the suffix '_for_site_tmpl' to differentiate with local sektion skope
                // @ see skp_get_no_group_skope_list()
                'skp__search_for_site_tmpl' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for search page', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                // this skope has no group skope => this is why we need to add the suffix '_for_site_tmpl' to differentiate with local sektion skope
                // @ see skp_get_no_group_skope_list()
                'skp__404_for_site_tmpl' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for 404 error page', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_after' => $pro_text,
                    'refresh_preview' => false
                ),
                // this skope has no group skope => this is why we need to add the suffix '_for_site_tmpl' to differentiate with local sektion skope
                // @ see skp_get_no_group_skope_list()
                'skp__date_for_site_tmpl' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for date pages', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_after' => $pro_text,
                    'refresh_preview' => false
                )
            )
        )//tmpl
    );
}

?>