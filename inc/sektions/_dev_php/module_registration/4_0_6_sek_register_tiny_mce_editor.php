<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER THE TEXT EDITOR MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',
        'starting_value' => array(
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.',
            'font_family_css' => '[gfont]Ribeye:regular'
        ),
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
                                'title'       => __('Content', 'text_domain_to_be_replaced'),
                                'default'     => ''
                            ),
                            'h_alignment_css' => array(
                                'input_type'  => 'h_text_alignment',
                                'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                                'default'     => is_rtl() ? 'right' : 'left',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            )
                        )
                    ),
                    array(
                        'title' => __('Font style', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-google-font-tab="true"',
                        'inputs' => array(
                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __('Font family', 'text_domain_to_be_replaced'),
                                'default'     => '',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                                'refresh-fonts' => true,
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'font_size',
                                'title'       => __('Font size in pixels', 'text_domain_to_be_replaced'),
                                'default'     => '16px',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __('Line height in pixels', 'text_domain_to_be_replaced'),
                                'default'     => '24px',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//24,//"20px",
                            'font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __('Font weight', 'text_domain_to_be_replaced'),
                                'default'     => 400,
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//null,
                            'font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __('Font style', 'text_domain_to_be_replaced'),
                                'default'     => 'inherit',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                            ),//null,
                            'text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __('Text decoration', 'text_domain_to_be_replaced'),
                                'default'     => 'none',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//null,
                            'text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __('Text transform', 'text_domain_to_be_replaced'),
                                'default'     => 'none',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//null,

                            'letter_spacing_css'  => array(
                                'input_type'  => 'number',
                                'title'       => __('Letter spacing', 'text_domain_to_be_replaced'),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//0,
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Text color', 'text_domain_to_be_replaced'),
                                'default'     => '',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                                'width-100'   => true
                            ),//"#000000",
                            'color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Text color on mouse over', 'text_domain_to_be_replaced'),
                                'default'     => '',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                                'width-100'   => true
                            ),//"#000000",
                            'important_css'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Make those style options win if other rules are applied.', 'text_domain_to_be_replaced'),
                                'default'     => 0,
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
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