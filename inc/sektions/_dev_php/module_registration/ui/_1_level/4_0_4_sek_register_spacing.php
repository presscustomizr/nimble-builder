<?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_spacing_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_spacing_module',
        'name' => __('Spacing options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',

        'tmpl' => array(
            'item-inputs' => array(
                'pad_marg' => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __('Set padding and margin', 'text_domain_to_be_replaced'),
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
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_spacing', 10, 3 );
// hook : sek_dyn_css_builder_rules
// @return array() of css rules
function sek_add_css_rules_for_spacing( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    //spacing
    if ( empty( $options[ 'spacing' ] ) || empty( $options[ 'spacing' ][ 'pad_marg' ] ) )
      return $rules;

    $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $options[ 'spacing' ][ 'pad_marg' ], '[data-sek-id="'.$level['id'].'"]' );
    return $rules;
}

?>