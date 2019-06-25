<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER FEATURED PAGES MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_social_icons_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_module',
        'is_crud' => true,
        'is_father' => true,
        'children' => array(
            'icons_collection' => 'czr_social_icons_settings_child',
            'icons_style' => 'czr_social_icons_style_child'
        ),
        'name' => __('Social Icons', 'text_doma'),
        // 'starting_value' => array(
        //     'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/social_icons_tmpl.php",
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


/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_social_icons_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_settings_child',
        'name' => __( 'Icon settings', 'text_doma' ),
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
        'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an icon', 'text_doma')
                ),
                'link'  => array(
                    'input_type'  => 'text',
                    'title'       => __('Social link url', 'text_doma'),
                    'notice_after'      => __('Enter the full url of your social profile (must be valid url).', 'text_doma'),
                    'placeholder' => __('http://...,mailto:...,...', 'text_doma')
                )
            ),
            // 'mod-opt' => array(
            //     // 'page-id' => array(
            //     //     'input_type'  => 'content_picker',
            //     //     'title'       => __('Pick a page', 'text_doma')
            //     // ),
            //     'mod_opt_test' => array(
            //         'input_type'  => 'simpleselect',
            //         'title'       => __('Display an image', 'text_doma'),
            //         'default'     => 'featured'
            //     ),
            // ),
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an icon', 'text_doma')
                ),
                'link'  => array(
                    'input_type'  => 'text',
                    'title'       => __('Social link url', 'text_doma'),
                    'notice_after'      => __('Enter the full url of your social profile (must be valid url).', 'text_doma'),
                    'placeholder' => __('http://...,mailto:...,...', 'text_doma')
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  SOCIAL ICONS STYLING
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_social_icons_style_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_style_child',
        'name' => __( 'Social icons options', 'text_doma' ),
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
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Spacing', 'text_doma' ),
                    'default'     => array( 'desktop' => array() ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_doma' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
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
                    // 'css_selectors'=> '.sek-icon i'
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
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 0,
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}
?>