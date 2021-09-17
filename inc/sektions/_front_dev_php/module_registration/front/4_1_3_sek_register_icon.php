<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ICON MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_icon_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_module',
        'is_father' => true,
        'children' => array(
            'icon_settings' => 'czr_icon_settings_child',
            'spacing_border' => 'czr_icon_spacing_border_child'
        ),
        'name' => __('Icon', 'text_doma'),
        'starting_value' => array(
            'icon_settings' => array(
                'icon' =>  'far fa-star',
                'font_size_css' => '40px',
                'color_css' => '#707070',
                'color_hover' => '#969696'
            )
        ),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_icon_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "icon_module_tmpl.php",
        // Nimble will "sniff" if we need font awesome
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}





/* ------------------------------------------------------------------------- *
 *  MAIN ICON SETTINGS : ICON, SIZE, COLOR, LINK
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_settings_child',
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
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an Icon', 'text_doma'),
                    //'default'     => 'no-link'
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
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'font_size_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Size', 'text_doma'),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'       => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors' => '.sek-icon',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'use_custom_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom icon color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#969696',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'color_hover'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  ICON SPACING BORDER SHADOW
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_spacing_border_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_spacing_border_child',
        'name' => __( 'Icon options for background, spacing, border, shadow', 'text_doma' ),
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











/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_icon_module', '\Nimble\sek_add_css_rules_for_icon_front_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_icon_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];

    $icon_settings = $value['icon_settings'];

    // COLOR ON HOVER
    $icon_color = $icon_settings['color_css'];
    if ( sek_booleanize_checkbox_val( $icon_settings['use_custom_color_on_hover'] ) ) {
        $color_hover = $icon_settings['color_hover'];
        $rules[] = array(
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon i:hover',
            'css_rules' => 'color:' . $color_hover . ';',
            'mq' =>null
        );
    }

    // BORDERS
    $border_settings = $value[ 'spacing_border' ][ 'borders' ];
    $border_type = $value[ 'spacing_border' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon-wrapper'
        );
    }

    return $rules;
}
?>