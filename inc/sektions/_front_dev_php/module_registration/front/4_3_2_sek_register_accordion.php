<?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ACCORDION MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_accordion_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_module',
        'is_father' => true,
        'children' => array(
            'accord_collec' => 'czr_accordion_collection_child',
            'accord_opts' => 'czr_accordion_opts_child'
        ),
        'name' => __('Accordion', 'text_doma'),
        'starting_value' => array(
            'accord_collec' => array(
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'),
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'),
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.')
            )
        ),
        'sanitize_callback' => '\Nimble\sanitize_cb__czr_accordion_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '[data-sek-accordion-id]' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "accordion_tmpl.php",
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
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sanitize_cb__czr_accordion_module( $value ) {
    if ( !is_array( $value ) )
        return $value;
    if ( !empty($value['accord_collec']) && is_array( $value['accord_collec'] ) ) {
        foreach( $value['accord_collec'] as $key => $data ) {
            if ( array_key_exists( 'text_content', $data ) && is_string( $data['text_content'] ) ) {
                $value['accord_collec'][$key]['text_content'] = sek_maybe_encode_richtext( $data['text_content'] );
            }
            if ( array_key_exists( 'title_text', $data ) && is_string( $data['title_text'] ) ) {
                $value['accord_collec'][$key]['title_text'] = sek_maybe_encode_richtext( $data['title_text'] );
            }
        }
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_accordion_collection_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">toc</i> %1$s', __( 'Item collection', 'text_doma' ) ),
        'starting_value' => array(
            'text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
        ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
            ),
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Title', 'text_doma' ),
                        'inputs' => array(
                            'title_text' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => false,
                                    'includedBtns' => 'basic_btns',
                                    'height' => 50
                                ),
                                'title'              => __( 'Heading text', 'text_doma' ),
                                'default'            => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-inner-accord-title',
                                'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                            ),
                            'title_attr'  => array(
                                'input_type'  => 'text',
                                'default'     => '',
                                'title'       => __('Title on mouse over', 'text_domain_to_be_replaced'),
                                'notice_after' => __('This is the text displayed on mouse over.' )
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Content', 'text_doma' ),
                        'inputs' => array(
                            'text_content' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => true,
                                    'includedBtns' => 'basic_btns_with_lists',
                                ),
                                'title'             => __( 'Text content', 'text_doma' ),
                                'default'           => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-accord-content',
                                'notice_before' => __('You may use some html tags in the "text" tab of the editor.', 'text_domain_to_be_replaced')
                            ),
                            'h_alignment_css' => array(
                                'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                                'title'       => __('Horizontal alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center'),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'css_selectors' => array( '.sek-accord-content' )
                            )
                        )
                    ),
                )//'tabs'
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  ACCORDION OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_accordion_opts_child() {
    $title_content_selector = array( '.sek-accord-item .sek-accord-title *' );
    $main_content_selector = array( '.sek-accord-item .sek-accord-content', '.sek-accord-item .sek-accord-content *' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Accordion options : font style, borders, background, ...', 'text_doma' ) ),
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
        //'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'General', 'text_doma' ),
                        'inputs' => array(
                            'first_expanded' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display first item expanded', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'one_expanded' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display one item expanded at a time', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'border_width_css' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Border weight', 'text_doma' ),
                                'min' => 0,
                                'max' => 80,
                                'default' => '1px',
                                'width-100'   => true,
                                //'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item',
                                'html_before' => '<hr/><h3>' . __('BORDER') .'</h3>'
                            ),
                            'border_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border color', 'text_doma' ),
                                'width-100'   => true,
                                'default'     => '#e3e3e3',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item'
                            ),
                        )//inputs
                    ),
                    array(
                        'title' => __( 'Title style', 'text_doma' ),
                        'inputs' => array(
                            'title_bg_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Backround color', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title',
                                'html_before' => '<h3>' . __('COLOR AND BACKGROUND') .'</h3>'
                            ),
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#565656',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $title_content_selector
                            ),//"#000000",

                            'color_active_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title_width' => 'width-100',
                                'title'       => __( 'Text color when active', 'text_doma' ),
                                'default'     => '#1e261f',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => array( '.sek-accord-item .sek-accord-title:hover *', '[data-sek-expanded="true"] .sek-accord-title *')
                            ),//"#000000",

                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $title_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>',
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100' => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $title_content_selector
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100' => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $title_content_selector
                            ),//24,//"20px",
                            'title_border_w_css' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Border bottom weight', 'text_doma' ),
                                'min' => 0,
                                'max' => 80,
                                'default' => '1px',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title',
                                'html_before' => '<hr/><h3>' . __('BORDER BOTTOM') .'</h3>'
                            ),
                            'title_border_c_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border bottom color', 'text_doma' ),
                                'width-100'   => true,
                                'default'     => '#e3e3e3',
                                //'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title'
                            ),
                            'spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing', 'text_doma' ),
                                'default'     => array('desktop' => array('padding-top' => '15', 'padding-right' => '20', 'padding-left' => '20', 'padding-bottom' => '15', 'unit' => 'px')),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors'      => '.sek-accord-item .sek-accord-title',
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Content style', 'text_doma' ),
                        'inputs' => array(
                            'ct_bg_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Backround color', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#f2f2f2',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => array('.sek-accord-item .sek-accord-content'),
                                'html_before' => '<h3>' . __('COLOR AND BACKGROUND') .'</h3>'
                            ),
                            'ct_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#1e261f',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $main_content_selector
                            ),//"#000000",

                            'ct_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $main_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>',
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'ct_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $main_content_selector
                            ),//16,//"14px",
                            'ct_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $main_content_selector
                            ),//24,//"20px",
                            'ct_spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing', 'text_doma' ),
                                'default'     => array('desktop' => array('padding-top' => '15', 'padding-right' => '20', 'padding-left' => '20', 'padding-bottom' => '15', 'unit' => 'px')),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors'      => '.sek-accord-item .sek-accord-content',
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )//inputs
                    )
                )//tabs
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
//add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_accordion_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_accordion_collection_child', 10, 2 );

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
//     [module_type] => czr_accordion_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_accordion_collection_child( $rules, $params ) {
    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // VERTICAL ALIGNMENT
    // if ( !empty( $item_model[ 'v_alignment' ] ) ) {
    //     if ( !is_array( $item_model[ 'v_alignment' ] ) ) {
    //         sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
    //     }
    //     $v_alignment_value = is_array( $item_model[ 'v_alignment' ] ) ? $item_model[ 'v_alignment' ] : array();
    //     $v_alignment_value = wp_parse_args( $v_alignment_value, array(
    //         'desktop' => 'center',
    //         'tablet' => '',
    //         'mobile' => ''
    //     ));
    //     $mapped_values = array();
    //     foreach ( $v_alignment_value as $device => $align_val ) {
    //         switch ( $align_val ) {
    //             case 'top' :
    //                 $mapped_values[$device] = "flex-start";
    //             break;
    //             case 'center' :
    //                 $mapped_values[$device] = "center";
    //             break;
    //             case 'bottom' :
    //                 $mapped_values[$device] = "flex-end";
    //             break;
    //         }
    //     }
    //     $rules = sek_set_mq_css_rules( array(
    //         'value' => $mapped_values,
    //         'css_property' => 'align-items',
    //         'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] )
    //     ), $rules );
    // }//Vertical alignment


    return $rules;
}




// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_accordion_module', '\Nimble\sek_add_css_rules_for_czr_accordion_module', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_accordion_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $defaults = sek_get_default_module_model( 'czr_accordion_module');
    $accord_defaults = $defaults['accord_opts'];

    $accord_opts = $value['accord_opts'];

    //sek_error_log('sek_get_default_module_model() ?', sek_get_default_module_model( 'czr_accordion_module') );

    // TEXT COLOR ( for the plus / minus icon )
    if ( !empty( $accord_opts[ 'color_css' ] ) && $accord_defaults[ 'color_css' ] != $accord_opts[ 'color_css' ] ) {
        $rules[] = array(
            'selector' => sprintf( '[data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper .sek-accord-item .expander span', $complete_modul_model['id'] ),
            'css_rules' => 'background:'. $accord_opts[ 'color_css' ] .';',
            'mq' =>null
        );
    }
    // ACTIVE / HOVER TEXT COLOR ( for the plus / minus icon )
    if ( !empty( $accord_opts[ 'color_active_css' ] ) && $accord_defaults[ 'color_active_css' ] != $accord_opts[ 'color_active_css' ] ) {
        $rules[] = array(
            'selector' => sprintf( '[data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper [data-sek-expanded="true"] .sek-accord-title .expander span, [data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper .sek-accord-item .sek-accord-title:hover .expander span', $complete_modul_model['id'] ),
            'css_rules' => sprintf('background:%s;', $accord_opts[ 'color_active_css' ] ),
            'mq' =>null
        );
    }

    return $rules;
}


?>