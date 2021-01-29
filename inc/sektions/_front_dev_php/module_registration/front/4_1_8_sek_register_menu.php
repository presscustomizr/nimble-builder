<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MENU MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

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
        'css_selectors' => array( '.sek-menu-module li > a', '.nb-search-expand-inner input', '[data-sek-is-mobile-vertical-menu="yes"] .nb-mobile-search input', '.nb-arrow-for-mobile-menu' ),//<=@see tmpl/modules/menu_module_tmpl.php
        'render_tmpl_path' => "menu_module_tmpl.php"
    );
}

/* ------------------------------------------------------------------------- *
 *  MENU CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_content_child() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('search icon next to the menu, sticky header, hamburger color, ...', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_content_child',
        'name' => __( 'Menu content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'menu-id' => array(
                    'input_type'  => 'simpleselect',
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
                // alignment of items on desktops devices ( when items are horizontal ), is controled with selector .sek-nav-collapse
                // janv 2021 : alignement of menu items in the vertical mobile mnenu with '[data-sek-is-mobile-vertical-menu="yes"] .sek-nav li a'
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Menu items alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'right', 'tablet' => 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_flex_alignment',
                    'css_selectors' => array( '.sek-nav-collapse', '[data-sek-is-mobile-vertical-menu="yes"] .sek-nav li a' ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'hamb_h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Hamburger button alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_flex_alignment',
                    'css_selectors' => array( '.sek-nav-wrap' ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_after' => $pro_text
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
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('%s %s', '<i class="material-icons sek-level-option-icon">devices</i>', __('On mobile devices, expand the menu in full width below the menu hamburger icon.', 'text_doma') ),
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