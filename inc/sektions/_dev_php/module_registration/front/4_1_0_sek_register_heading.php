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
        'name' => __('Heading', 'text_domain_to_be_replaced'),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_heading_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'heading_text' => 'This is a heading.'
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'heading_text' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Heading text', 'text_domain'),
                    'default'     => ''
                ),
                'heading_tag' => array(
                    'input_type'  => 'select',
                    'title'       => __('Heading tag', 'text_domain'),
                    'default'     => 'h1'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/heading_module_tmpl.php",
        //'placeholder_icon' => 'code'
    );
}


function sanitize_callback__czr_heading_module( $setting_value ) {
    //sek_error_log('Sanitizing the heading odule', $setting_value );
    return $setting_value;
}
?>