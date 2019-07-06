<?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMG SLIDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_img_slider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_module',
        'is_father' => true,
        'children' => array(
            'img_collection' => 'czr_img_slider_collection_child',
            'slider_options' => 'czr_img_slider_opts_child'
        ),
        'name' => __('Image Carousel', 'text_doma'),
        // 'starting_value' => array(
        //     'img_collection' => array(
        //         'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        //     )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/img_slider_tmpl.php",
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
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_collection_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_collection_child',
        'is_crud' => true,
        'name' => __( 'Icon collection', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => NIMBLE_BASE_URL . '/assets/img/default-img.png'
                ),
            ),
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'title_attr'  => array(
                    'input_type'  => 'text',
                    'default'     => '',
                    'title'       => __('Title', 'text_domain_to_be_replaced'),
                    'notice_after'      => __('This is the text displayed on mouse over.', 'text_domain_to_be_replaced'),
                ),
                // 'link_target' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Open link in a new browser tab', 'text_doma'),
                //     'default'     => false,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                // ),
                // 'color_css' => array(
                //     'input_type'  => 'wp_color_alpha',
                //     'title'       => __('Color', 'text_doma'),
                //     'width-100'   => true,
                //     'default'    => '#707070',
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true,
                //     'css_identifier' => 'color'
                // ),
                // 'use_custom_color_on_hover' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __( 'Set a custom icon color on mouse hover', 'text_doma' ),
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true,
                //     'default'     => false,
                // ),
                // 'social_color_hover' => array(
                //     'input_type'  => 'wp_color_alpha',
                //     'title'       => __('Hover color', 'text_doma'),
                //     'width-100'   => true,
                //     'default'    => '#969696',
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => true,
                //     //'css_identifier' => 'color_hover'
                // )
            )
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  SOCIAL ICONS STYLING
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_opts_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_opts_child',
        'name' => __( 'Design options : size, spacing, alignment,...', 'text_doma' ),
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
            'item-inputs' => array(

            )
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
//add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_img_slider_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_img_slider_collection_child', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_img_slider_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_img_slider_collection_child( $rules, $params ) {
    //sek_error_log('SOCIAL ITEMS PARAMS?', $params );

    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // COLOR ON HOVER
    $icon_color = $item_model['color_css'];
    if ( sek_booleanize_checkbox_val( $item_model['use_custom_color_on_hover'] ) ) {
        $color_hover = $item_model['social_color_hover'];
    } else {
        // Build the lighter rgb from the user picked bg color
        if ( 0 === strpos( $icon_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $icon_color );
            $color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $color_hover      = sek_rgb2rgba( $color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $icon_color, 'rgb' ) ) {
            $color_hover      = sek_lighten_rgb( $icon_color, $percent=15 );
        } else {
            $color_hover      = sek_lighten_hex( $icon_color, $percent=15 );
        }
    }
    $color_hover_selector = sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-social-icon:hover', $params['parent_module_id'], $item_model['id'] );
    $rules[] = array(
        'selector' => $color_hover_selector,
        'css_rules' => 'color:' . $color_hover . ';',
        'mq' =>null
    );
    return $rules;
}

// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
//add_filter( 'sek_add_css_rules_for_module_type___czr_img_slider_module', '\Nimble\sek_add_css_rules_for_czr_img_slider_module', 10, 2 );


// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_img_slider_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $icons_style = $value['icons_style'];

    // HORIZONTAL SPACE BETWEEN ICONS
    $padding_right = $icons_style['space_between_icons'];
    $padding_right = is_array( $padding_right ) ? $padding_right : array();
    $defaults = array(
        'desktop' => '15px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $padding_right = wp_parse_args( $padding_right, $defaults );
    $padding_right_ready_val = $padding_right;
    foreach ($padding_right as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $padding_right_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( ! empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $padding_right_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $padding_right_ready_val,
        'css_property' => 'padding-right',
        'selector' => implode(',', array(
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-social-icons-wrapper > *:not(:last-child)',
        )),
        'is_important' => false
    ), $rules );

    return $rules;
}
?>