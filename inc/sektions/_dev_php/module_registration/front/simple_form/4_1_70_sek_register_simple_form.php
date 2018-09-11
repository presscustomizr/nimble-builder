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
        'is_father' => true,
        'children' => array(
            'form_fields' => 'czr_simple_form_fields_module',
            'design' => 'czr_simple_form_design_module'
        ),
        'name' => __( 'Simple Form', 'text_domain_to_be_replaced' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        'starting_value' => array(
            'design' => array(
                'border_width_css' => '4px',
                'border_color_css' => '#ff0000'
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_form_module_tmpl.php",
    );
}


// function sanitize_callback__czr_simple_form_module( $value ) {
//     $value[ 'button_text' ] = sanitize_text_field( $value[ 'button_text' ] );
//     return $value;
// }

?>
