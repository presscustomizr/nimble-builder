<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_performances() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_performances',
        'name' => __('Site wide performance options', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'global-img-smart-load' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Load images on scroll', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __( 'Check this option to delay the loading of non visible images. Images below the viewport will be loaded dynamically on scroll. This can boost performances by reducing the weight of long web pages designed with several images.', 'text_domain_to_be_replaced')
                ),
            )
        )//tmpl
    );
}

?>