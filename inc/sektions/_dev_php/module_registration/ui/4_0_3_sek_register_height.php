<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_height_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_height_module',
        'name' => __('Height options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Height : auto or custom', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'height-type' )
                ),
                'custom-height' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Custom height', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => '50%',
                    'width-100'   => true
                ),
                'v_alignment' => array(
                    'input_type'  => 'v_alignment',
                    'title'       => __('Inner vertical alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'v_alignment'
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

    if ( ! empty( $options[ 'height' ][ 'v_alignment' ] ) ) {
        $v_alignment_value = $options[ 'height' ][ 'v_alignment' ];
        switch ( $v_alignment_value ) {
            case 'top' :
                $v_align_value = "flex-start";
            break;
            case 'center' :
                $v_align_value = "center";
            break;
            case 'bottom' :
                $v_align_value = "flex-end";
            break;
            default :
                $v_align_value = "center";
            break;
        }
        $css_rules = '';
        if ( isset( $v_align_value ) ) {
            $css_rules .= 'align-items:' . $v_align_value;
        }

        if ( !empty( $css_rules ) ) {
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => $css_rules,
                    'mq' =>null
            );
        }
    }
    if ( ! empty( $options[ 'height' ][ 'height-type' ] ) ) {
        if ( 'custom' == $options[ 'height' ][ 'height-type' ] && array_key_exists( 'custom-height', $options[ 'height' ] ) ) {
            $height = $options[ 'height' ][ 'custom-height' ];
            $css_rules = '';
            if ( isset( $height ) && FALSE !== $height ) {
                $numeric = sek_extract_numeric_value( $height );
                if ( !empty( $numeric ) ) {
                    $unit = sek_extract_unit( $height );
                    $unit = '%' === $unit ? 'vh' : $unit;
                    $css_rules .= 'height:' . $numeric . $unit . ';';
                }
            }
            if ( !empty( $css_rules ) ) {
                $rules[]     = array(
                        'selector' => '[data-sek-id="'.$level['id'].'"]',
                        'css_rules' => $css_rules,
                        'mq' =>null
                );
            }
        }
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?>