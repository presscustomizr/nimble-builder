<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_beta_features() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_beta_features',
        //'name' => __('Beta features', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'beta-enabled' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Enable beta features', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_before_title' => sprintf( '%1$s <strong>%2$s</strong>',
                        __( 'Check this option to try the upcoming features of Nimble Builder.', 'text_doma') ,
                        __('There are currently no available beta features to test.', 'text_doma')
                    ),
                    'notice_after' => __( 'Be sure to refresh the customizer before you start using the beta features.', 'text_doma')
                ),
            )
        )//tmpl
    );
}

?>