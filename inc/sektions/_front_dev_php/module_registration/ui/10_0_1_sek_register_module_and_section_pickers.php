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
                    'title'       => '',//__('Which type of content would you like to drop in your page ?', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => sprintf(
                        __('Note : you can %1$s to replace your default theme template. Or design your own %2$s.', 'nimble-builder'),
                        sprintf('<a href="#" onclick="%2$s" title="%1$s">%1$s</a>',
                            __('use the Nimble page template', 'nimble-builder'),
                            "javascript:wp.customize.section('__localOptionsSection', function( _s_ ){_s_.container.find('.accordion-section-title').first().trigger('click');})"
                        ),
                        // Header and footer have been introduced in v1.4.0 but not enabled by default
                        sek_is_header_footer_enabled() ? sprintf('<a href="#" onclick="%2$s" title="%1$s">%1$s</a>',
                            __('header and footer', 'nimble-builder'),
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })"
                        ) : __('header and footer', 'nimble-builder')
                    )
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
                    'title'       => __('Drag and drop or double-click on a module to insert it in your chosen target element in the previewed page', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )
    );
}


/* ------------------------------------------------------------------------- *
 *  SEKTION PICKER MODULES
/* ------------------------------------------------------------------------- */
function sek_get_default_section_input_params() {
    return array(
        'input_type'  => 'section_picker',
        'title'       => __('Drag and drop or double-click on a section to insert it in your chosen target element in the previewed page', 'text_domain_to_be_replaced'),
        'width-100'   => true,
        'title_width' => 'width-100'
    );

}


//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_intro_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_intro_sec_picker_module',
        'name' => __('Sections for an introduction', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'intro_sections' => sek_get_default_section_input_params()
            )
        )
    );
}
function sek_get_module_params_for_sek_features_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_features_sec_picker_module',
        'name' => __('Sections for services and features', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'features_sections' => sek_get_default_section_input_params()
            )
        )
    );
}

function sek_get_module_params_for_sek_contact_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_contact_sec_picker_module',
        'name' => __('Contact-us sections', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'contact_sections' => sek_get_default_section_input_params()
            )
        )
    );
}

function sek_get_module_params_for_sek_column_layouts_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_column_layouts_sec_picker_module',
        'name' => __('Empty sections with columns layout', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'layout_sections' => sek_get_default_section_input_params()
            )
        )
    );
}

function sek_get_module_params_for_sek_header_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_header_sec_picker_module',
        'name' => __('Header sections', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'header_sections' => sek_get_default_section_input_params()
            )
        )
    );
}
function sek_get_module_params_for_sek_footer_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_footer_sec_picker_module',
        'name' => __('Footer sections', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'footer_sections' => sek_get_default_section_input_params()
            )
        )
    );
}






function sek_get_module_params_for_sek_my_sections_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_my_sections_sec_picker_module',
        'name' => __('My sections', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'my_sections' => sek_get_default_section_input_params()
            )
        )
    );
}
?>