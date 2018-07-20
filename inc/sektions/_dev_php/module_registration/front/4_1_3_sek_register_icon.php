<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ICON MODULE
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
function sek_get_module_params_for_czr_icon_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_module',
        'name' => __('Icon', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'icon' =>  'far fa-star',
            'font_size_css' => '40px',
            'color_css' => '#707070'
        ),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_icon_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an Icon', 'text_domain_to_be_replaced'),
                    //'default'     => 'no-link'
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_domain_to_be_replaced'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_domain_to_be_replaced'),
                    'default'     => false
                ),
                'font_size_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Size', 'text_domain_to_be_replaced'),
                    'default'     => '16px',
                    'min' => 0,
                    'max' => 100,
                    'width-100'       => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors' => '.sek-icon'
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'color_hover_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color_hover'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/icon_module_tmpl.php",
        'front_assets' => array(
              'czr-font-awesome' => array(
                  'type' => 'css',
                  //'handle' => 'czr-font-awesome',
                  'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
                  //'deps' => array()
              )
        )
    );
}
?>