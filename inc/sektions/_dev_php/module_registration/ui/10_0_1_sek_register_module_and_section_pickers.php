<?php
/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_content_type_switcher_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_content_type_switcher_module',
        'name' => __('Content type', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content_type' => array(
                    'input_type'  => 'content_type_switcher',
                    'title'       => __('Which type of content would you like to insert in your page ?', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )
    );
}


/* ------------------------------------------------------------------------- *
 *  MODULE PICKER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_module_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_module_picker_module',
        'name' => __('Content Picker', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'module_id' => array(
                    'input_type'  => 'module_picker',
                    'title'       => __('Drag and drop modules in the previewed page', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )
    );
}

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
                    'title'       => __('Drag and drop sections in the previewed page', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )
    );
}
?>