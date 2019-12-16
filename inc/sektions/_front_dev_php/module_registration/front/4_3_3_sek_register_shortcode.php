<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SHORTCODE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_shortcode_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_shortcode_module',
        'name' => __('Shortcode', 'text_doma'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'text_content' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => true,
                        'includedBtns' => 'basic_btns_with_lists',
                    ),
                    'title'             => __( 'Write the shortcode(s) in the text editor', 'text_doma' ),
                    'default'           => '',
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup'    => '.sek-shortcode-content',
                    'notice_before' => __('A shortcode is a WordPress-specific code that lets you display predefined items. For example a trivial shortcode for a gallery looks like this [gallery].') . '<br/><br/>',
                    'notice_after' => __('You may use some html tags in the "text" tab of the editor.', 'text_domain_to_be_replaced')
                ),
                'refresh_button' => array(
                    'input_type'  => 'refresh_preview_button',
                    'title'       => __( '' , 'text_doma' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Horizontal alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_flex_alignment',
                    'css_selectors'      => '.sek-module-inner > .sek-shortcode-content',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_before' => '<hr/><h3>' . __('ALIGNMENT') .'</h3>'
                )
            )
        ),
        'render_tmpl_path' => "shortcode_module_tmpl.php",
    );
}
?>