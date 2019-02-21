<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_recaptcha() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_recaptcha',
        'name' => __('Protect your contact forms with Google reCAPTCHA', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'enable' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Activate Google reCAPTCHA on your forms', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf( __('The Nimble Builder can setup the %1$s service to protect your forms against spam. You need to %2$s'),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://developers.google.com/recaptcha/', __('Google reCAPTCHA', 'text_doma') ),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://www.google.com/recaptcha/admin#list', __('get your domain API keys from Google', 'text_doma') )
                    )
                ),
                'public_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Public reCAPTCHA key', 'text_doma'),
                    'default'     => ''
                ),
                'private_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Private reCAPTCHA key', 'text_doma'),
                    'default'     => ''
                ),
            )
        )//tmpl
    );
}

?>