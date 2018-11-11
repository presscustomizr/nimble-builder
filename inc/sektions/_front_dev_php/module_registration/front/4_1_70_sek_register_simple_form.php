<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE FORM MODULES
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_simple_form_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_module',
        'is_father' => true,
        'children' => array(
            'form_fields'   => 'czr_simple_form_fields_child',
            'fields_design' => 'czr_simple_form_design_child',
            'form_button'   => 'czr_simple_form_button_child',
            'form_fonts'    => 'czr_simple_form_fonts_child',
            'form_submission'    => 'czr_simple_form_submission_child'
        ),
        'name' => __( 'Simple Form', 'text_domain_to_be_replaced' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        'starting_value' => array(
            'fields_design' => array(
                'border' => array(
                    '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                )
            ),
            'form_button' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => is_rtl() ? 'right' : 'left',
                'use_box_shadow' => 1,
                'push_effect' => 1
            ),
            'form_fonts' => array(
                'fl_font_family_css' => '[cfont]Lucida Console,Monaco,monospace',
                'btn_color_css' => '#ffffff'
            ),
            'form_submission' => array(
                'email_footer' => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_domain_to_be_replaced' ),
                    get_bloginfo( 'name' ),
                    get_site_url( 'url' )
                )
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_form_module_tmpl.php",
    );
}











/* ------------------------------------------------------------------------- *
 *  FIELDS VISIBILITY AND REQUIRED
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fields_child() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fields_child',
        'name' => __( 'Form fields and button labels', 'text_domain_to_be_replaced' ),
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
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_name_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display name field', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'name_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Name field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Name', 'translate')
                ),
                'name_field_required' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Name field is required', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'show_subject_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display subject field', 'text_domain_to_be_replaced'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'subject_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Subject field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Subject', 'translate')
                ),
                'subject_field_required' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Subject field is required', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'show_message_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display message field', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'message_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Message field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Message', 'translate')
                ),
                'message_field_required' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Message field is required', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'email_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Email', 'translate')
                ),

                'button_text' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Button text', 'text_domain_to_be_replaced'),
                    'default'     => __('Submit', 'translate')
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_design_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_design_child',
        'name' => __( 'Form fields design', 'text_domain_to_be_replaced' ),
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
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Fields background color', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Fields border shape', 'text_domain_to_be_replaced'),
                    'default' => 'solid',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders options', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> $css_selectors
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Fields rounded corners', 'text_domain_to_be_replaced' ),
                    'default'     => '3px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'use_inset_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply an inset shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                ),
                'use_outset_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply an outset shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SUBMIT BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_button_child() {
    $css_selectors = 'form input[type="submit"]';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_button_child',
        'name' => __( 'Form button design', 'text_domain_to_be_replaced' ),
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
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                      'input_type'  => 'wp_color_alpha',
                      'title'       => __( 'Background color', 'text_domain_to_be_replaced' ),
                      'width-100'   => true,
                      'default'    => '',
                      'refresh_markup' => false,
                      'refresh_stylesheet' => true,
                      'css_identifier' => 'background_color',
                      'css_selectors'=> $css_selectors
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'background_color_hover',
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border', 'text_domain_to_be_replaced'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default'     => '2px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __( 'Button alignment', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors'=> '.sek-form-btn-wrapper',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_domain_to_be_replaced' ),
                    'default'            => array(
                        'margin-top'     => .5,
                        'padding-top'    => .5,
                        'padding-bottom' => .5,
                        'padding-right'  => 1,
                        'padding-left'   => 1,
                        'unit' => 'em'
                    ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'padding_margin_spacing',
                    'css_selectors'=> $css_selectors,//'.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply a shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Push visual effect', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}












/* ------------------------------------------------------------------------- *
 *  FONTS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fonts_child() {
    $fl_font_selectors = array( 'form label', '.sek-form-message' ); //<= .sek-form-message is the wrapper of the form status message : Thanks, etc...
    $ft_font_selectors = array( 'form input[type="text"]', 'form input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    $btn_font_selectors = array( 'form input[type="submit"]' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fonts_child',
        'name' => __( 'Form texts options : fonts, colors, ...', 'text_domain_to_be_replaced' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Fields labels', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'fl_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $fl_font_selectors,
                            ),
                            'fl_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $fl_font_selectors,
                            ),//16,//"14px",
                            'fl_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $fl_font_selectors,
                            ),//24,//"20px",
                            'fl_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'fl_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'fl_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'fl_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'fl_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $fl_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'fl___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'fl_font_family_css',
                                    'fl_font_size_css',
                                    'fl_line_height_css',
                                    'fl_font_weight_css',
                                    'fl_font_style_css',
                                    'fl_text_decoration_css',
                                    'fl_text_transform_css',
                                    'fl_letter_spacing_css',
                                    'fl_color_css',
                                    'fl_color_hover_css'
                                )
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Field Text', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'ft_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $ft_font_selectors,
                            ),
                            'ft_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $ft_font_selectors,
                            ),//16,//"14px",
                            'ft_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $ft_font_selectors,
                            ),//24,//"20px",
                            'ft_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'ft_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $ft_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'ft_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'ft_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'ft_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $ft_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'ft___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'ft_font_family_css',
                                    'ft_font_size_css',
                                    'ft_line_height_css',
                                    'ft_font_weight_css',
                                    'ft_font_style_css',
                                    'ft_text_decoration_css',
                                    'ft_text_transform_css',
                                    'ft_letter_spacing_css',
                                    'ft_color_css',
                                    'ft_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Button', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'btn_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $btn_font_selectors,
                            ),
                            'btn_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $btn_font_selectors,
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
                                'css_identifier' => 'line_height',
                                'css_selectors' => $btn_font_selectors,
                            ),//24,//"20px",
                            'btn_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'btn_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $btn_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'btn_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'btn_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'btn_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $btn_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'btn___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'btn_font_family_css',
                                    'btn_font_size_css',
                                    'btn_line_height_css',
                                    'btn_font_weight_css',
                                    'btn_font_style_css',
                                    'btn_text_decoration_css',
                                    'btn_text_transform_css',
                                    'btn_letter_spacing_css',
                                    'btn_color_css',
                                    'btn_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}

// function sanitize_callback__czr_simple_form_module( $value ) {
//     $value[ 'button_text' ] = sanitize_text_field( $value[ 'button_text' ] );
//     return $value;
// }



/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_submission_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_submission_child',
        'name' => __( 'Form submission options', 'text_domain_to_be_replaced' ),
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
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'recipients' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email recipient', 'text_domain_to_be_replaced'),
                    'default'     => get_option( 'admin_email' ),
                    'refresh_preview'  => false
                ),
                'success_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Success message on submission' , 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Thanks! Your message has been sent.', 'text_domain_to_be_replaced'),
                    'refresh_preview'  => false
                ),
                'error_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Error message on submission' , 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Invalid form submission : some fields have not been entered properly.', 'text_domain_to_be_replaced'),
                    'refresh_preview'  => false
                ),
                'failure_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Failure message on submission' , 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Your message was not sent. Try Again.', 'text_domain_to_be_replaced'),
                    'refresh_preview'  => false
                ),
                'email_footer' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Email footer' , 'text_domain_to_be_replaced' ),
                    'notice_before' => __('Html code is allowed', 'text-domain'),
                    'default'     => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_domain_to_be_replaced' ),
                        get_bloginfo( 'name' ),
                        get_site_url( 'url' )
                    ),
                    'refresh_preview'  => false
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING FOR THE FORM MODULE
/* ------------------------------------------------------------------------- */
// FORM MODULE CHILDREN
// 'children' => array(
//       'form_fields'   => 'czr_simple_form_fields_child',
//       'fields_design' => 'czr_simple_form_design_child',
//       'form_button'   => 'czr_simple_form_button_child',
//       'form_fonts'    => 'czr_simple_form_fonts_child'
//   ),
add_filter( 'sek_add_css_rules_for_module_type___czr_simple_form_module', '\Nimble\sek_add_css_rules_for_czr_simple_form_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_simple_form_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];

    // BUTTON
    if ( ! empty( $value['form_button'] ) && is_array( $value['form_button'] ) ) {
        $form_button_options = $value['form_button'];
        $bg_color = $form_button_options['bg_color_css'];
        if ( sek_booleanize_checkbox_val( $form_button_options['use_custom_bg_color_on_hover'] ) ) {
            $bg_color_hover = $form_button_options['bg_color_hover'];
        } else {
            // Build the lighter rgb from the user picked bg color
            if ( 0 === strpos( $bg_color, 'rgba' ) ) {
                list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
                $bg_color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
                $bg_color_hover      = sek_rgb2rgba( $bg_color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
            } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
                $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=15 );
            } else {
                $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=15 );
            }
        }

        $rules[] = array(
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] input[type="submit"]:hover',
            'css_rules' => 'background-color:' . $bg_color_hover . ';',
            'mq' =>null
        );

        // BUTTON BORDERS
        $border_settings = $form_button_options[ 'borders' ];
        $border_type = $form_button_options[ 'border-type' ];
        $has_border_settings  = 'none' != $border_type && !empty( $border_type );

        //border width + type + color
        if ( $has_border_settings ) {
            $rules = sek_generate_css_rules_for_multidimensional_border_options(
                $rules,
                $border_settings,
                $border_type,
                '[data-sek-id="'.$complete_modul_model['id'].'"] input[type="submit"]'
            );
        }
    }


    // FIELDS BORDERS
    $border_settings = $value[ 'fields_design' ][ 'borders' ];
    $border_type = $value[ 'fields_design' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $selector_list = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
        $css_selectors = array();
        foreach( $selector_list as $selector ) {
            $css_selectors[] = '[data-sek-id="'.$complete_modul_model['id'].'"]' . ' ' . $selector;
        }
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            implode( ', ', $css_selectors )
        );
    }
    return $rules;
}



?>