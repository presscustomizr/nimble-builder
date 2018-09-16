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
        if ( !empty( $numeric ) ) {
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

?>