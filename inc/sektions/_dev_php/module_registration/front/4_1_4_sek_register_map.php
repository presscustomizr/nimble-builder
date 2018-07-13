<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MAP MODULE
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
function sek_get_module_params_for_czr_map_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_map_module',
        'name' => __('Map', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_gmap_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        //'css_selectors' => array( '.sek-module-inner' ),
        'starting_value' => array(
            'address'       => 'Nice, France',
            'zoom'          => 10,
            'height_css'    => 200
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'address' => array(
                    'input_type'  => 'text',
                    'title'       => __( 'Address', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '',
                ),
                'zoom' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __( 'Zoom', 'text_domain_to_be_replaced' ),
                    'min' => 1,
                    'max' => 20,
                    'unit' => '',
                    'default' => 10,
                ),
                'height_css' => array(
                    'input_type'  => 'number',
                    'title'       => __( 'Height in pixels', 'text_domain_to_be_replaced' ),
                    'min' => 1,
                    'default' => 200,
                    'css_selectors' => array( '.sek-embed::before' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'height'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/map_module_tmpl.php",
    );
}
?>