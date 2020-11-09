<?php
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

?>