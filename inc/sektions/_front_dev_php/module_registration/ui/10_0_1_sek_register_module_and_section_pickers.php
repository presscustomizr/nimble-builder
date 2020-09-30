<?php
/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_content_type_switcher_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_content_type_switcher_module',
        'name' => __('Select a content type', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content_type' => array(
                    'input_type'  => 'content_type_switcher',
                    'title'       => '',//__('Which type of content would you like to drop in your page ?', 'text_doma'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => sprintf(
                        __('Note : you can %1$s to replace your default theme template. Or design your own %2$s.', 'nimble-builder'),
                        sprintf('<a href="#" onclick="%2$s" title="%1$s">%1$s</a>',
                            __('use the Nimble page template', 'nimble-builder'),
                            "javascript:wp.customize.section('__localOptionsSection', function( _s_ ){_s_.container.find('.accordion-section-title').first().trigger('click');})"
                        ),
                        sprintf('<a href="#" onclick="%2$s" title="%1$s">%1$s</a>',
                            __('header and footer', 'nimble-builder'),
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })"
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
        'name' => __('Pick a module', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'module_id' => array(
                    'input_type'  => 'module_picker',
                    'title'       => __('Drag-and-drop or double-click a module to insert it into a drop zone of the preview page.', 'text_doma'),
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
// now registered with sek_register_prebuilt_section_modules() in add_action( 'after_setup_theme', '\Nimble\sek_schedule_module_registration', 50 );
// and then populated in AJAX from a local JSON since https://github.com/presscustomizr/nimble-builder/issues/431

// FOR SAVED SECTIONS
// function sek_get_module_params_for_sek_my_sections_sec_picker_module() {
//     return array(
//         'dynamic_registration' => true,
//         'module_type' => 'sek_my_sections_sec_picker_module',
//         'name' => __('My sections', 'text_doma'),
//         'tmpl' => array(
//             'item-inputs' => array(
//                 'my_sections' => array(
//                     'input_type'  => 'section_picker',
//                     'title'       => __('Drag-and-drop or double-click a section to insert it into a drop zone of the preview page.', 'text_doma'),
//                     'width-100'   => true,
//                     'title_width' => 'width-100'
//                 )
//             )
//         )
//     );
// }
?>