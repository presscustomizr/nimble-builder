<?php
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

?>