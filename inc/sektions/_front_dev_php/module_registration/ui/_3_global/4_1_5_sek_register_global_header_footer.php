<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_header_footer() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_header_footer',
        'name' => __('Site wide header', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'header-footer' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select a site wide header and footer', 'text_domain_to_be_replaced'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'theme' => __('Use the active theme\'s header and footer', 'text_domain' ),
                        'nimble_global' => __('Nimble site wide header and footer ( beta )', 'text_domain' )
                    ),
                    //'refresh_preview' => true,
                    'notice_before_title' => sprintf( __( 'The Nimble Builder allows you to build your own header and footer, or to use your theme\'s ones. This option can be overriden in the %1$s.', 'text_domain_to_be_replaced'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__localOptionsSection', function( _s_ ){_s_.container.find('.accordion-section-title').first().trigger('click');})",
                            __('Current page options', 'text_domain_to_be_replaced')
                        )
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
            )
        )//tmpl
    );
}

?>