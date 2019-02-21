<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_anchor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_anchor_module',
        'name' => __('Set a custom anchor', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'custom_anchor' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom anchor', 'text_doma'),
                    'default'     => '',
                    'notice_after' => __('Note : white spaces, numbers and special characters are not allowed when setting an anchor.')
                ),
            )
        )//tmpl
    );
}

?>