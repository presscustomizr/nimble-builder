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
        'sanitize_callback' => '\Nimble\sek_sanitize_czr_shortcode_module',
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
                'lazyload' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Enable image lazy-loading', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                // flex-box should be enabled by user and not active by default.
                // It's been implemented primarily to ease centering ( see https://github.com/presscustomizr/nimble-builder/issues/565 )
                // When enabled, it can create layout issues like : https://github.com/presscustomizr/nimble-builder/issues/576
                'use_flex' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a flex-box wrapper', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('Flex-box is a CSS standard used to specify the layout of HTML pages. Using flex-box can make it easier to center the content of shortcodes.', 'text_doma')
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

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sek_sanitize_czr_shortcode_module( $content ) {
    if ( is_array($content) && !empty($content['text_content']) ) {
        $content['text_content'] = sek_maybe_encode_richtext($content['text_content']);
    }
    return $content;
}
?>