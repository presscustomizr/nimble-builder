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
function sek_get_module_params_for_czr_button_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_button_module',
        'is_father' => true,
        'children' => array(
            'content' => 'czr_btn_content_child',
            'design' => 'czr_btn_design_child',
            'font' => 'czr_font_child'
        ),
        'name' => __( 'Button', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            'content' => array(
                'button_text' => __('Click me','text_doma'),
            ),
            'design' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => 'center',
                'use_box_shadow' => 1,
                'push_effect' => 1,
            ),
            'font' => array(
                'color_css'  => '#ffffff',
            )
        ),
        'css_selectors' => array( '.sek-btn' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/button_module_tmpl.php"
    );
}



/* ------------------------------------------------------------------------- *
 *  BUTTON CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_content_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_content_child',
        'name' => __( 'Button content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'button_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns_nolink',
                        'height' => 45
                    ),
                    'title'              => __( 'Button text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'refresh_markup'    => '.sek-btn-text'
                ),
                'btn_text_on_hover' => array(
                    'input_type'         => 'text',
                    'title'              => __( 'Tooltip text on mouse hover', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'notice_after'       => __( 'Not previewable when customizing.', 'text_doma')
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __( 'Icon next to the button text', 'text_doma' ),
                    //'default'     => 'no-link'
                ),
                'icon-side' => array(
                    'input_type'  => 'buttons_choice',
                    'title'       => __("Icon's position", 'text_doma'),
                    'default'     => 'left',
                    'choices'     => array( 'left' => __('Left', 'text-domain'), 'right' => __('Right', 'text-domain') )
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_design_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_design_child',
        'name' => __( 'Button design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '#020202',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    //'css_selectors'=> $css_selectors
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __( 'You can also customize the text color on mouseover in the group of text settings below.', 'text_doma')
                    //'css_identifier' => 'background_color_hover',
                    //'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
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
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    //'css_selectors'=> $css_selectors
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Button alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_doma' ),
                    'default'            => array(
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
                    'css_selectors'=> '.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Push visual effect', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}
















function sanitize_callback__czr_button_module( $value ) {
    if ( is_array( $value ) && is_array( $value['content'] ) && array_key_exists( 'button_text', $value['content'] ) ) {
        $value['content'][ 'button_text' ] = sanitize_text_field( $value['content'][ 'button_text' ] );
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_button_module', '\Nimble\sek_add_css_rules_for_button_front_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_button_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    // BACKGROUND
    $value = $complete_modul_model['value'];
    $design_settings = $value['design'];
    $bg_color = $design_settings['bg_color_css'];
    if ( sek_booleanize_checkbox_val( $design_settings['use_custom_bg_color_on_hover'] ) ) {
        $bg_color_hover = $design_settings['bg_color_hover'];
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
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-btn:hover',
        'css_rules' => 'background-color:' . $bg_color_hover . ';',
        'mq' =>null
    );

    // BORDERS
    $border_settings = $design_settings[ 'borders' ];
    $border_type = $design_settings[ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-btn'
        );
    }
    return $rules;
}

?>
