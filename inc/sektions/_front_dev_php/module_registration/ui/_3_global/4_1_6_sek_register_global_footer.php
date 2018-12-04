<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_footer() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_footer',
        'name' => __('Site wide footer', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'global-footer' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select a site wide footer', 'text_domain_to_be_replaced'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'theme_header' => __('Use the active theme\'s footer', 'text_domain' ),
                        'nimble_header' => __('Nimble footer ( beta )', 'text_domain' )
                    ),
                    //'refresh_preview' => true,
                    //'notice_after' => __( '', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
            )
        )//tmpl
    );
}

?>