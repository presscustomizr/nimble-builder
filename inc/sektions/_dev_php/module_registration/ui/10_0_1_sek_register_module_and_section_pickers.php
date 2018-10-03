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
                        __('Note : you can %1$s to replace your default theme template. Depending on your theme structure, it can allow you to display your sections in full-width mode.'),
                        sprintf('<a href="%2$s" title="%1$s">%1$s</a>',
                            __('select the Nimble page template', 'text-domain'),
                            "javascript:if ( sektionsLocalizedData && sektionsLocalizedData.sektionsPanelId ) { wp.customize.panel(sektionsLocalizedData.sektionsPanelId, function( _panel_ ) { try{wp.customize.czr_sektions.rootPanelFocus(); _panel_.focus();}catch(er){} } ) }"
                        )
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
                    'title'       => __('Drag and drop modules in the previewed page', 'text_domain_to_be_replaced'),
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
        'title'       => __('Drag and drop sections in the previewed page', 'text_domain_to_be_replaced'),
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
?>