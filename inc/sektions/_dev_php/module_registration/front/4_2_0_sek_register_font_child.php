<?php
/* ------------------------------------------------------------------------- *
 *  GENERIC FONT CHILD MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_font_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_font_child',
        'name' => __( 'Font settings', 'text_domain_to_be_replaced' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_domain_to_be_replaced'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => '',
        'tmpl' => array(
            'item-inputs' => array(
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
                    'default'     => array( 'desktop' => '16px' ),
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
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'fonts___flag_important'  => array(
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
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}
?>