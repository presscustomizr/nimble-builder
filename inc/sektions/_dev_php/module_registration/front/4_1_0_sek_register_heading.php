<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER HEADING MODULE
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
function sek_get_module_params_for_czr_heading_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_module',
        'name' => __( 'Heading', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_heading_module',
        'validate_callback' => '\Nimble\validate_callback__czr_heading_module',
        'starting_value' => array(
            'heading_text' => 'This is a heading.',
            'h_alignment_css' => 'center'
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'General design', 'text_domain_to_be_replaced' ),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'heading_text' => array(
                                'input_type'         => 'text',
                                'title'              => __( 'Heading text', 'text_domain_to_be_replaced' ),
                                'default'            => '',
                                'width-100'         => true,
                                'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),

                            ),
                            'heading_tag' => array(
                                'input_type'         => 'select',
                                'title'              => __( 'Heading tag', 'text_domain_to_be_replaced' ),
                                'default'            => 'h1',
                                'choices'            => sek_get_select_options_for_input_id( 'heading_tag' )
                            ),
                            'h_alignment_css'        => array(
                                'input_type'         => 'h_text_alignment',
                                'title'              => __( 'Alignment', 'text_domain_to_be_replaced' ),
                                'default'            => is_rtl() ? 'right' : 'left',
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment'
                            ),
                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family'
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'min' => 0,
                                'max' => 100,
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
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color'
                            ),//"#000000",
                            'color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover'
                            ),//"#000000",
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'heading___flag_important'       => array(
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
                        'title' => __( 'Other font settings', 'text_domain_to_be_replaced' ),
                        'attributes' => 'data-sek-google-font-tab="true"',
                        'inputs' => array(

                            'font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
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
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/heading_module_tmpl.php",
    );
}


function sanitize_callback__czr_heading_module( $value ) {
    if (  !current_user_can( 'unfiltered_html' ) && array_key_exists('heading_text', $value ) ) {
        //sanitize heading_text
        $value[ 'heading_text' ] = czr_heading_module_kses_text( $value[ 'heading_text' ] );
    }
    return $value;
    //return new \WP_Error('required' ,'heading did not pass sanitization');
}

// @see SEK_CZR_Dyn_Register::set_dyn_setting_args
// Only the boolean true or a WP_error object will be valid returned value considered when validating
function validate_callback__czr_heading_module( $value ) {
    //return new \WP_Error('required' ,'heading did not pass ');
    return true;
}


?>
