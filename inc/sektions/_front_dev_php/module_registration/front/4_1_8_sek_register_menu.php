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
            'font' => 'czr_font_child'
        ),
        'name' => __( 'Menu', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            // 'content' => array(
            //     'button_text' => __('Click me','text_domain_to_be_replaced'),
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
        'css_selectors' => array( '.sek-menu-module .menu-item' ),//<=@see tmpl/modules/menu_module_tmpl.php
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/menu_module_tmpl.php",
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
 *  BUTTON CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_content_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_content_child',
        'name' => __( 'Menu content', 'text_domain_to_be_replaced' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'menu-id' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_user_created_menus()
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}
?>