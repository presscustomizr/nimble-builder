<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_widths',
        'name' => __('Site wide width options', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom outer width for the sections site wide', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __('This option will be inherited by all Nimble sections of your site, unless for pages or sections with specific width options.')
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom inner width for the sections site wide', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner sections width', 'text_domain_to_be_replaced'),
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
            'selector' => $selector
        ), $rules );

        // when customizing the inner section width, we need to reset the default padding rules for .sek-container-fluid {padding-right:10px; padding-left:10px}
        // @see assets/front/scss/_grid.scss
        if ( 'inner-section-width' === $width_opt_name ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-left',
                'selector' => '[data-sek-level="section"] > .sek-container-fluid'
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => '[data-sek-level="section"] > .sek-container-fluid'
            ), $rules );
        }

        if ( ! empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector
            ), $rules );
        }
    }//foreach

    $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    if ( is_string( $width_options_css ) && ! empty( $width_options_css ) ) {
        printf('<style type="text/css" id="nimble-global-options">%1$s</style>', $width_options_css );
    }
}
?>