<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_header_footer() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('sticky header, header over content, sticky footer, search icon, hamburger color, ...', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_header_footer',
        //'name' => __('Site wide header', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'header-footer' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a site wide header and footer', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'theme' => __('Use the active theme\'s header and footer', 'text_domain' ),
                        'nimble_global' => __('Nimble site wide header and footer', 'text_domain' )
                    ),
                    //'refresh_preview' => true,
                    'notice_before_title' => sprintf( __( 'Nimble Builder allows you to build your own header and footer, or to use your theme\'s ones. This option can be overriden in the %1$s.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__localOptionsSection', function( _s_ ){_s_.container.find('.accordion-section-title').first().trigger('click');})",
                            __('current page options', 'text_doma')
                        )
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_after' => $pro_text
                ),
            )
        )//tmpl
    );
}

?>