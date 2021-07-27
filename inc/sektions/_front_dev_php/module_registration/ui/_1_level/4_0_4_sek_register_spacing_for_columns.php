<?php
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
?>