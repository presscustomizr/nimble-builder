<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_section_layout_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_section_layout_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'boxed-wide' => array(
                    'input_type'  => 'select',
                    'title'       => __('Boxed or full width', 'text_domain_to_be_replaced'),
                    'refresh-markup' => true,
                    'refresh-stylesheet' => false,
                    'default'     => 'fullwidth'
                ),

                /* suspended, needs more thoughts
                'boxed-width' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __('Custom boxed width', 'text_domain_to_be_replaced'),
                    'orientation' => 'horizontal',
                    'min' => 500,
                    'max' => 1600,
                    'unit' => 'px'
                ),*/
            )
        )//tmpl
    );
}
?>