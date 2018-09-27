<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER DIVIDER MODULE
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
function sek_get_module_params_for_czr_divider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_divider_module',
        'name' => __('Divider', 'text_domain_to_be_replaced'),
        'css_selectors' => array( '.sek-divider' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border_top_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Weight', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 50,
                    //'unit' => 'px',
                    'default' => '1px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_width'
                ),
                'border_top_style_css' => array(
                    'input_type'  => 'select',
                    'title'       => __('Style', 'text_domain_to_be_replaced'),
                    'default' => 'solid',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_style',
                    'choices'    => sek_get_select_options_for_input_id( 'border-type' )
                ),
                'border_top_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_color'
                ),
                'width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Width', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => '%',
                    'default' => '100%',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'width'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius'
                    //'css_selectors'=> ''
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => '.sek-module-inner',
                    'css_identifier' => 'h_alignment'
                ),
                'v_spacing_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space before and after', 'text_domain_to_be_replaced'),
                    'min'         => 1,
                    'max'         => 100,
                    'default'     => array( 'desktop' => '15px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'v_spacing'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/divider_module_tmpl.php",
    );
}
?>