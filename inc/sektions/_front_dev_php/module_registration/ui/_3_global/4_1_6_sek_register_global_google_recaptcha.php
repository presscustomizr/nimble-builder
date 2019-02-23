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
                    'title'       => sprintf( '<img height="20" width="20" src="%1$s"/> %2$s', NIMBLE_BASE_URL . '/assets/img/recaptcha_32.png', __('Activate Google reCAPTCHA on your forms', 'text_doma') ),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf( __('The Nimble Builder can activate the %1$s service to protect your forms against spam. You need to %2$s.'),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://docs.presscustomizr.com/article/385-how-to-enable-recaptcha-protection-against-spam-in-your-forms-with-the-nimble-builder/?utm_source=usersite&utm_medium=link&utm_campaign=nimble-form-module', __('Google reCAPTCHA', 'text_doma') ),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://www.google.com/recaptcha/admin#list', __('get your domain API keys from Google', 'text_doma') )
                    )
                ),
                'public_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Public key', 'text_doma'),
                    'default'     => '',
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),
                'private_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Private key', 'text_doma'),
                    'default'     => '',
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),
                'badge' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Show the reCAPTCHA badge at the bottom of your page', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after'       => __( 'The badge is not previewable when customizing.', 'text_doma')
                ),
                'failure_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Failure message' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Google ReCaptcha validation failed. This form only accepts messages from humans.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
            )
        )//tmpl
    );
}

?>