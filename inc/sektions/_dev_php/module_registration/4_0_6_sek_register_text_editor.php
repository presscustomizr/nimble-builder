<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER THE TEXT EDITOR MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Content', 'text_domain_to_be_replaced'),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'content' => array(
                                'input_type'  => 'tiny_mce_editor',
                                'title'       => __('Content', 'text_domain_to_be_replaced')
                            ),
                            'alignment' => array(
                                'input_type'  => 'h_text_alignment',
                                'title'       => __('Alignment', 'text_domain_to_be_replaced')
                            )
                        )
                    ),
                    array(
                        'title' => __('Font style', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-google-font-tab="true"',
                        'inputs' => array(
                            'font-family' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __('Font family', 'text_domain_to_be_replaced')
                            ),
                            'font-size'       => array(
                                'input_type'  => 'font_size',
                                'title'       => __('Font size', 'text_domain_to_be_replaced')
                            ),//16,//"14px",
                            'line-height'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __('Line height', 'text_domain_to_be_replaced')
                            ),//24,//"20px",
                            'font-weight'     => array(
                                'input_type'  => 'select',
                                'title'       => __('Font weight', 'text_domain_to_be_replaced')
                            ),//null,
                            'font-style'      => array(
                                'input_type'  => 'select',
                                'title'       => __('Font style', 'text_domain_to_be_replaced')
                            ),//null,
                            'text-decoration' => array(
                                'input_type'  => 'select',
                                'title'       => __('Text decoration', 'text_domain_to_be_replaced')
                            ),//null,
                            'text-transform'  => array(
                                'input_type'  => 'select',
                                'title'       => __('Text transform', 'text_domain_to_be_replaced')
                            ),//null,

                            'letter-spacing'  => array(
                                'input_type'  => 'number',
                                'title'       => __('Letter spacing', 'text_domain_to_be_replaced')
                            ),//0,
                            'color'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Text color', 'text_domain_to_be_replaced')
                            ),//"#000000",
                            'color-hover'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Text color on mouse over', 'text_domain_to_be_replaced')
                            ),//"#000000",
                            'important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Make those style options win if other rules are applied.', 'text_domain_to_be_replaced')
                            ),//false
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}

?>