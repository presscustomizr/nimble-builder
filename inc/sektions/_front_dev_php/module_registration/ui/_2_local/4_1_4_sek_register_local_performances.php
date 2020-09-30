<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_performances() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_performances',
        //'name' => __('Performance optimizations', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local-img-smart-load' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select how you want to load the images in the sections of this page.', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the site wide option', 'text_domain' ),
                        'yes' => __('Load images on scroll ( optimized )', 'text_domain' ),
                        'no'  => __('Load all images on page load ( not optimized )', 'text_domain' )
                    ),
                    //'refresh_preview' => true,
                    'notice_after' => sprintf('%1$s <br/><strong>%2$s</strong>',
                        __( 'When you select "Load images on scroll", images below the window are loaded dynamically when scrolling. This can improve performance by reducing the weight of long web pages including multiple images.', 'text_dom'),
                        __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )//tmpl
    );
}
?>