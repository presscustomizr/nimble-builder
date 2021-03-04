<?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_tinymce_child',
            'font_settings' => 'czr_font_child'
        ),
        'name' => __('Text Editor', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
            )
        ),
        'sanitize_callback' => '\Nimble\sek_sanitize_czr_tiny_mce_editor_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array(
            // this list is limited to the most commonly used tags in the editor.
            // note that Hx headings have a default style set in _heading.scss
            '.sek-module-inner',
            '.sek-module-inner p',
            '.sek-module-inner a',
            '.sek-module-inner li'
        ),
        'render_tmpl_path' => "tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sek_sanitize_czr_tiny_mce_editor_module( $content ) {
    if ( is_array($content) && !empty($content['main_settings']) && is_array($content['main_settings']) ) {
        $editor_content = !empty($content['main_settings']['content']) ? $content['main_settings']['content'] : '';
        $content['main_settings']['content'] = sek_maybe_encode_richtext($editor_content);
    }
    //sek_error_log( 'ALORS MODULE CONTENT ?', $content );
    return $content;
}

/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tinymce_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tinymce_child',
        'name' => __('Content', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content' => array(
                    'input_type'  => 'detached_tinymce_editor',
                    'title'       => __('Content', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => '.sek-module-inner [data-sek-input-type="detached_tinymce_editor"]',
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'autop' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Automatically convert text into paragraph', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('WordPress wraps the editor text inside "p" tags by default. You can disable this behaviour by unchecking this option.', 'text-domain')
                ),
            )
        ),
        'render_tmpl_path' =>'',
    );
}


?>