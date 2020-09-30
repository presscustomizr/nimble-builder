<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_mod_option_switcher_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_mod_option_switcher_module',
        //'name' => __('Option switcher', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content_type' => array(
                    'input_type'  => 'module_option_switcher',
                    'title'       => '',//__('Which type of content would you like to drop in your page ?', 'text_doma'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                )
            )
        )
    );
}

?>