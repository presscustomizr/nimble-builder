<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER THE TEXT EDITOR MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',
        'name' => __('Text Editor', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'General design', 'text_domain_to_be_replaced' ),
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
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment'
                            ),
                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __('Font family', 'text_domain_to_be_replaced'),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family'
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'default'     => array( 'desktop' => array() ),
                                'min' => 0,
                                'max' => 100,
                                'title_width' => 'width-100',
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size'
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height'
                            ),//24,//"20px",
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Text color', 'text_domain_to_be_replaced'),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color'
                            ),//"#000000",
                            'color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Text color on mouse over', 'text_domain_to_be_replaced'),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover'
                            ),//"#000000",
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'tiny_mce___flag_important'  => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply the style options in priority (uses !important).', 'text_domain_to_be_replaced'),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'font_family_css',
                                    'font_size_css',
                                    'line_height_css',
                                    'font_weight_css',
                                    'font_style_css',
                                    'text_decoration_css',
                                    'text_transform_css',
                                    'letter_spacing_css',
                                    'color_css',
                                    'color_hover_css'
                                )
                            ),//false
                        )
                    ),
                    array(
                        'title' => __('Other font settings', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-google-font-tab="true"',
                        'inputs' => array(
                            'font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __('Font weight', 'text_domain_to_be_replaced'),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __('Font style', 'text_domain_to_be_replaced'),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __('Text decoration', 'text_domain_to_be_replaced'),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __('Text transform', 'text_domain_to_be_replaced'),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,

                            'letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'width-100'   => true,
                            )//0,
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