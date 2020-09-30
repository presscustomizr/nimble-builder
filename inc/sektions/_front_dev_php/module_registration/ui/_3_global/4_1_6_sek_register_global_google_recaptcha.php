<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_recaptcha() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_recaptcha',
        //'name' => __('Protect your contact forms with Google reCAPTCHA', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'enable' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf( '<img height="20" width="20" src="%1$s"/> %2$s', NIMBLE_BASE_URL . '/assets/img/recaptcha_32.png', __('Activate Google reCAPTCHA on your forms', 'text_doma') ),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf( __('Nimble Builder can activate the %1$s service to protect your forms against spambots. You need to %2$s.'),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://docs.presscustomizr.com/article/385-how-to-enable-recaptcha-protection-against-spam-in-your-forms-with-the-nimble-builder/?utm_source=usersite&utm_medium=link&utm_campaign=nimble-form-module', __('Google reCAPTCHA', 'text_doma') ),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://www.google.com/recaptcha/admin#list', __('get your domain API keys from Google', 'text_doma') )
                    )
                ),
                'public_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Site key', 'text_doma'),
                    'default'     => '',
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),
                'private_key' => array(
                    'input_type'  => 'text',
                    'title'       => __('Secret key', 'text_doma'),
                    'default'     => '',
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),
                'score'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Score threshold', 'text_doma' ),
                    'default'     => 0.5,
                    'min'         => 0,
                    'max'         => 1,
                    'step'        => 0.05,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'notice_after'  => __( 'reCAPTCHA returns a score from 0 to 1 on each submission. 1 is very likely a good interaction, 0 is very likely a bot. A form submission that scores lower than your threshold will be considered as done by a robot, and aborted.', 'text_doma'),
                    'refresh_preview' => false,
                    'refresh_markup' => false
                ),//0,
                'show_failure_message' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Show a failure message', 'text_doma' ),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'failure_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Failure message' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Google ReCAPTCHA validation failed. This form only accepts messages from humans.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'badge' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Show the reCAPTCHA badge at the bottom of your page', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after'       => __( 'The badge is not previewable when customizing.', 'text_doma')
                )
            )
        )//tmpl
    );
}

?>