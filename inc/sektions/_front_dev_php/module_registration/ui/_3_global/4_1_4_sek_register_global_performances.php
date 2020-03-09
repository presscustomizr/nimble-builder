<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_performances() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_performances',
        'name' => __('Site wide performance options', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'global-img-smart-load' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading images off screen', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf('<strong>%1$s</strong>',
                        __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    )
                ),
                'global-bg-video-lazy-load' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading video backgrounds', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // 'notice_after' => sprintf('%1$s <br/><strong>%2$s</strong>',
                    //     __( 'Load video backgrounds when', 'text_dom'),
                    //     __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    // )
                ),
                'use_partial_module_stylesheets' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use partial CSS stylesheets for modules', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'print_partial_module_stylesheets_inline' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Print modules stylesheets inline', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'print_dyn_stylesheets_inline' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Print generated stylesheets inline', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'load_front_assets_in_ajax' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading module assets when modules are offscreen', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'load_font_awesome_in_ajax' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Defer loading Font Awesome icons', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'load_js_async' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Load javascript files asynchronously', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'preload_google_fonts' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Preload Google fonts', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'preload_jquery' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Preload jQuery library on front-end', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('Use with caution, preloading Jquery can break third party plugins and themes.')
                ),
            )
        )//tmpl
    );
}

?>