<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_text_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_text_module',
        //'name' => __('Text', 'text_doma'),
        // 'starting_value' => array(
        //     'bg-color-overlay'  => '#000000',
        //     'bg-opacity-overlay' => '40'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '', // <= this first empty selector generates a selector looking like [data-sek-id="__nimble__27f2dc680c0c"], which allows us to style text not wrapped in any specific html tags
          'p', 'a', '.sek-btn', 'button', 'input', 'select', 'optgroup', 'textarea' ),
        'tmpl' => array(
            'item-inputs' => array(
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_before' => sprintf( '<span class="czr-notice">%1$s<br/>%2$s</span><hr>',
                        __('Note : some modules have text settings in their module content tab. Those settings are applied first, before those below.'),
                        __('Text styling is always inherited in this order : section > column > module settings > module content')
                    )
                ),
                'font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
                ),
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
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
                    'title'       => __( 'Line height', 'text_doma' ),
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
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color'
                ),//"#000000",
                'color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color on mouse over', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover'
                ),//"#000000",
                'font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font weight', 'text_doma'),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font style', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text decoration', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text transform', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,

                'letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                )//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                // 'fonts___flag_important'  => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Apply the style options in priority (uses !important).', 'text_doma'),
                //     'default'     => 0,
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     // declare the list of input_id that will be flagged with !important when the option is checked
                //     // @see sek_add_css_rules_for_css_sniffed_input_id
                //     // @see Nsek_is_flagged_important
                //     'important_input_list' => array(
                //         'font_family_css',
                //         'font_size_css',
                //         'line_height_css',
                //         'font_weight_css',
                //         'font_style_css',
                //         'text_decoration_css',
                //         'text_transform_css',
                //         'letter_spacing_css',
                //         'color_css',
                //         'color_hover_css'
                //     )
                // )
            )//item-inputs
        )//tmpl
    );
}


?>