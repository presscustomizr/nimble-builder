<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_header_footer() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('sticky header, header over content, sticky footer, search icon, hamburger color, ...', 'nimble-builder') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_header_footer',
        //'name' => __('Page header', 'nimble-builder'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'nimble-builder' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'header-footer' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a header and a footer for this page', 'nimble-builder'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the site wide option', 'nimble-builder' ),
                        'theme' => __('Use the active theme\'s header and footer', 'nimble-builder' ),
                        'nimble_global' => __('Nimble site wide header and footer', 'nimble-builder' ),
                        'nimble_local' => __('Nimble specific header and footer for this page', 'nimble-builder' )
                    ),
                    'refresh_preview' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => sprintf( __( 'This option overrides the site wide header and footer options set in the %1$s for this page only.', 'nimble-builder'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })",
                            __('site wide options', 'nimble-builder')
                        )
                    ),
                    'html_after' => $pro_text
                )
            )
        )//tmpl
    );
}
?>