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
function sek_get_module_params_for_czr_menu_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_module',
        'is_father' => true,
        'children' => array(
            'content' => 'czr_menu_content_child',
            //'design' => 'czr_menu_design_child',
            'font' => 'czr_font_child',
            'mobile_options' => 'czr_menu_mobile_options'

        ),
        'name' => __( 'Menu', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            // 'content' => array(
            //     'button_text' => __('Click me','text_doma'),
            // ),
            // 'design' => array(
            //     'bg_color_css' => '#020202',
            //     'bg_color_hover' => '#151515', //lighten 15%,
            //     'use_custom_bg_color_on_hover' => 0,
            //     'border_radius_css' => '2',
            //     'h_alignment_css' => 'center',
            //     'use_box_shadow' => 1,
            //     'push_effect' => 1,
            // ),
            // 'font' => array(
            //     'color_css'  => '#ffffff',
            // )
        ),
        'css_selectors' => array( '.sek-menu-module > li' ),//<=@see tmpl/modules/menu_module_tmpl.php
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/menu_module_tmpl.php"
    );
}

/* ------------------------------------------------------------------------- *
 *  MENU CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_content_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_content_child',
        'name' => __( 'Menu content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'menu-id' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select a menu', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_user_created_menus(),
                    'notice_after' => sprintf( __( 'You can create and edit menus in the %1$s. If you just created a new menu, publish and refresh the customizer to see in the dropdown list.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.panel('nav_menus', function( _p_ ){ _p_.focus(); })",
                            __('menu panel', 'text_doma')
                        )
                    ),
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_flex_alignment',
                    'css_selectors' => array( '.sek-nav-collapse', '.sek-nav-wrap' ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
            ),
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 * MOBILE OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_mobile_options() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_mobile_options',
        'name' => __( 'Settings for mobile devices', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'expand_below' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => sprintf('%s %s', '<i class="material-icons sek-level-option-icon">devices</i>', __('On mobile devices, expand the menu in full width below the menu hamburger icon', 'text_doma') ),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
            ),
        ),
        'render_tmpl_path' => '',
    );
}
?>