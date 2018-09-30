<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_width_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_module',
        'name' => __('Width options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'width-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Width : 100% or custom', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'width-type' )
                ),
                'custom-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),
                'h_alignment' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Horizontal alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment'
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
    if ( empty( $options[ 'width' ] ) )
      return $rules;

    if ( ! empty( $options[ 'width' ][ 'h_alignment' ] ) ) {
        $h_alignment_value = $options[ 'width' ][ 'h_alignment' ];
        switch ( $h_alignment_value ) {
            case 'left' :
                $h_align_value = "flex-start";
            break;
            case 'center' :
                $h_align_value = "center";
            break;
            case 'right' :
                $h_align_value = "flex-end";
            break;
            default :
                $h_align_value = "center";
            break;
        }
        $css_rules = '';
        if ( isset( $h_align_value ) ) {
            $css_rules .= 'align-self:' . $h_align_value;
        }

        if ( !empty( $css_rules ) ) {
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$module['id'].'"]',
                    'css_rules' => $css_rules,
                    'mq' =>null
            );
        }
    }

    if ( ! empty( $options[ 'width' ][ 'width-type' ] ) ) {
        if ( 'custom' == $options[ 'width' ][ 'width-type' ] && array_key_exists( 'custom-width', $options[ 'width' ] ) ) {
            $user_custom_width_value = $options[ 'width' ][ 'custom-width' ];
            $selector = '[data-sek-id="'.$module['id'].'"]';

            if ( is_string( $user_custom_width_value ) ) {
                $numeric = sek_extract_numeric_value( $user_custom_width_value );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $user_custom_width_value );
                    $rules[]     = array(
                            'selector' => $selector,// we need to use the specificity body .sektion-wrapper, in order to override any local skope or global inner / outer setting
                            'css_rules' => sprintf( 'max-width:%1$s%2$s;margin: 0 auto;', $numeric, $unit ),
                            'mq' =>null
                    );
                }
            } else if ( is_array( $user_custom_width_value ) ) {
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
                    'selector' => $selector
                ), $rules );
            }
        }
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?>