<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_header_footer() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_header_footer',
        'name' => __('Page header', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_domain_to_be_replaced' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'header-footer' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select a header and a footer for this page', 'text_domain_to_be_replaced'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the site wide option', 'text_domain' ),
                        'theme' => __('Use the active theme\'s header and footer', 'text_domain' ),
                        'nimble_local' => __('Nimble specific header and footer for this page ( beta )', 'text_domain' ),
                        'nimble_global' => __('Nimble site wide header and footer ( beta )', 'text_domain' )
                    ),
                    'refresh_preview' => true,
                    //'notice_after' => __( '', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )//tmpl
    );
}
?>