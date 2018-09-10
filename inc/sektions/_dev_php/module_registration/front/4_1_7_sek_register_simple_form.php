<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER BUTTON MODULE
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
        'name' => __( 'Simple Form', 'text_domain_to_be_replaced' ),
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
        'css_selectors' => array( '.sek-module-inner > .sek-button' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_name_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Show name field', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_subject_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Show subject field', 'text_domain_to_be_replaced'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_message_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Show message field', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_form_module_tmpl.php",
    );
}


// function sanitize_callback__czr_simple_form_module( $value ) {
//     $value[ 'button_text' ] = sanitize_text_field( $value[ 'button_text' ] );
//     return $value;
// }

?>
