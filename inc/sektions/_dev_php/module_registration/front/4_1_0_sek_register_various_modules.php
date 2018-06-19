<?php
/* ------------------------------------------------------------------------- *
 *  A FILE TO REGISTER SEVERAL MODULES, SO WE DON'T HAVE TO CONCATENATE IT WITH GRUNT
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
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
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

function sek_get_module_params_for_czr_spacer_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_spacer_module',
        'name' => __('Spacer', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        /*'starting_value' => array(
            'heading_text' => 'This is a heading.'
        ),*/
        'tmpl' => array(
            'item-inputs' => array(
                'height_css' => array(
                    'input_type'  => 'number',
                    'min'         => 1,
                    'title'       => __('Space', 'text_domain_to_be_replaced'),
                    'default'     => 20,
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/spacer_module_tmpl.php",
    );
}

function sek_get_module_params_for_czr_divider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_divider_module',
        'name' => __('Divider', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'border_top_width_css' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __('Weight', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 50,
                    'unit' => 'px',
                    'default' => 1,
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                ),
                'border_top_style_css' => array(
                    'input_type'  => 'select',
                    'title'       => __('Style', 'text_domain_to_be_replaced'),
                    'default' => 'solid',
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                ),
                'border_top_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                ),
                'width_css' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __('Width', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 100,
                    'unit' => '%',
                    'default' => 100,
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                ),
                'v_spacing_css' => array(
                    'input_type'  => 'number',
                    'title'       => __('Space before and after', 'text_domain_to_be_replaced'),
                    'min'         => 1,
                    'max'         => 100,
                    'default'     => 15,
                    'refresh-markup' => false,
                    'refresh-stylesheet' => true
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/divider_module_tmpl.php",
    );
}

?>