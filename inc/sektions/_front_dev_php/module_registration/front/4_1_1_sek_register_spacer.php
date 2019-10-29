<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SPACER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );


function sek_get_module_params_for_czr_spacer_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_spacer_module',
        'name' => __('Spacer', 'text_doma'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'min'         => 0,
                    'max'         => 100,
                    'step'        => 1,
                    'title'       => __('Space', 'text_doma'),
                    'default'     => array( 'desktop' => '20px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => array( '.sek-spacer' ),
                    'css_identifier' => 'height'
                ),
            )
        ),
        'render_tmpl_path' => "spacer_module_tmpl.php",
    );
}
?>