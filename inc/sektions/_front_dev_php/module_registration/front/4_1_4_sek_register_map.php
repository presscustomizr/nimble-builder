<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MAP MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_map_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_map_module',
        'name' => __('Map', 'text_doma'),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_gmap_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        //'css_selectors' => array( '.sek-module-inner' ),
        'starting_value' => array(
            'address'       => 'Nice, France',
            'zoom'          => 10,
            'height_css'    => '200px'
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'address' => array(
                    'input_type'  => 'text',
                    'title'       => __( 'Address', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '',
                ),
                'zoom' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Zoom', 'text_doma' ),
                    'min' => 1,
                    'max' => 20,
                    'unit' => '',
                    'default' => 10,
                    'width-100'   => true
                ),
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Height', 'text_doma' ),
                    'min' => 1,
                    'max' => 600,
                    'default'     => array( 'desktop' => '200px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors' => array( '.sek-embed::before' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'height'
                ),
                'lazyload' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Lazy load', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf('%1$s <br/><strong>%2$s</strong>',
                        __( 'With the lazy load option enabled, Nimble loads the map when it becomes visible while scrolling. This improves your page load performances.', 'text_dom'),
                        __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    ),
                )
            )
        ),
        'render_tmpl_path' => "map_module_tmpl.php",
    );
}
?>