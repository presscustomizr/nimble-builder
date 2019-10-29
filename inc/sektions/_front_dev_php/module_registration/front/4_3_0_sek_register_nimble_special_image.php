<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER NIMBLE IMAGE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_special_img_module() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_special_img_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_special_img_main_settings_child',
            // ??
        ),
        'name' => __('Nimble Image', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
            )
        ),
        // 'sanitize_callback' => '\Nimble\czr_special_img_module_sanitize_validate',
        // 'validate_callback' => '\Nimble\czr_special_img_module_sanitize_validate',
        'render_tmpl_path' => "special_img_module_tmpl.php"
    );
}




/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_special_img_main_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_special_img_main_settings_child',
        'name' => __( 'Image main settings', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'img-size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' )
                ),
                'link-to' => array(
                    'input_type'  => 'simpleselect',
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
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new page', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                // 'h_alignment_css' => array(
                //     'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                //     'title'       => __('Alignment', 'text_doma'),
                //     'default'     => array( 'desktop' => 'center' ),
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true,
                //     'css_identifier' => 'h_alignment',
                //     'title_width' => 'width-100',
                //     'width-100'   => true,
                // ),
                'use_custom_title_attr' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Set the text displayed when the mouse is held over', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('If not specified, Nimble will use by order of priority the caption, the description, and the image title. Those properties can be edited for each image in the media library.')
                ),
                'heading_title' => array(
                    'input_type'         => 'text',
                    'title' => __('Custom text displayed on mouse hover', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true
                ),
                // 'use_custom_width' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __( 'Custom image width', 'text_doma' ),
                //     'default'     => 0,
                //     'refresh_stylesheet' => true
                // ),
                // 'custom_width' => array(
                //     'input_type'  => 'range_with_unit_picker_device_switcher',
                //     'title'       => __('Width', 'text_doma'),
                //     'min' => 1,
                //     'max' => 100,
                //     //'unit' => '%',
                //     'default'     => array( 'desktop' => '100%' ),
                //     'max'     => 500,
                //     'width-100'   => true,
                //     'title_width' => 'width-100',
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true
                // ),
                // 'use_box_shadow' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __( 'Apply a shadow', 'text_doma' ),
                //     'default'     => 0,
                // ),
                // 'img_hover_effect' => array(
                //     'input_type'  => 'simpleselect',
                //     'title'       => __('Mouse over effect', 'text_doma'),
                //     'default'     => 'none',
                //     'choices'     => sek_get_select_options_for_input_id( 'img_hover_effect' )
                // )
            )
        ),
        'render_tmpl_path' => '',
    );
}
?>