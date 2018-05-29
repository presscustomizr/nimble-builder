<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_height_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_height_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Height : fit to screen or custom', 'text_domain_to_be_replaced'),
                    'default'     => 'default'
                ),
                'custom-height' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __('Custom height', 'text_domain_to_be_replaced'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    'unit' => '%',
                    'default' => 50
                ),
                'v_alignment_css' => array(
                    'input_type'  => 'v_alignment',
                    'title'       => __('Vertical alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', 'sek_add_css_rules_for_level_height', 10, 3 );
function sek_add_css_rules_for_level_height( array $rules, array $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'height' ] ) )
      return $rules;

    if ( empty( $options[ 'height' ][ 'height-type' ] ) )
      return $rules;

    if ( 'fit-to-screen' == $options[ 'height' ][ 'height-type' ] ) {
        $height = '100';
    }
    elseif ( 'custom' == $options[ 'height' ][ 'height-type' ] && array_key_exists( 'custom-height', $options[ 'height' ] ) && FALSE !== $height_value = filter_var( $options[ 'height' ][ 'custom-height' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) ) ) ) {
        $height = $height_value;
    }
    $css_rules = '';
    if ( isset( $height ) && FALSE !== $height ) {
        $css_rules .= 'height:' . $height . 'vh;';
    }

    if ( !empty( $css_rules ) ) {
        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'css_rules' => $css_rules,
                'mq' =>null
        );
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?>