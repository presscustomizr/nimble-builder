<?php
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

?>