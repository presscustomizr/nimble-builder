<?php
/* ------------------------------------------------------------------------- *
 *  SEKTION PICKER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_section_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_section_picker_module',
        'name' => __('Section Picker', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'section_id' => array(
                    'input_type'  => 'section_picker',
                    'title'       => __('Pick a section', 'text_domain_to_be_replaced'),
                    'width-100'   => true
                )
            )
        )
    );
}
?>