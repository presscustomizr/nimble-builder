<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_template() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_template',
        'name' => __('Template for the current page', 'text_domain_to_be_replaced'),
        'starting_value' => array(),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_template' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select a template', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'width-100'   => true,
                    'choices'     => sek_get_select_options_for_input_id( 'local_template' ),
                    'refresh_preview' => true
                )
            )
        )//tmpl
    );
}
?>