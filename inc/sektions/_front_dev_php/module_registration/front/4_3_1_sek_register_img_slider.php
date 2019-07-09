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
            'slider_options' => 'czr_img_slider_opts_child',
            'font_options' => 'czr_img_slider_fonts_child'
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
        'name' => __( 'Slides collection', 'text_doma' ),
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
                    'default'     => NIMBLE_BASE_URL . '/assets/img/default-img.png'
                ),
            ),
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Image', 'text_doma' ),
                        'inputs' => array(
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
                            )
                        )
                    ),

                    array(
                        'title' => __( 'Text', 'text_doma' ),
                        'inputs' => array(
                            'enable_text' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Add text content', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'html_before' => '<hr/><h3>' . __('TEXT CONTENT') .'</h3>'
                            ),
                            'text_content' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => false,
                                    'includedBtns' => 'basic_btns',
                                ),
                                'title'             => __( 'Text content', 'text_doma' ),
                                'default'           => '',
                                'width-100'         => true,
                                //'notice_before'     => __( 'You may use some html tags like a, br,p, div, span with attributes like style, id, class ...', 'text_doma'),
                                'refresh_markup'    => '.sek-slider-text-content'
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
                                'css_selectors' => array( '.sek-slider-text-content' ),
                            ),
                            'v_alignment' => array(
                                'input_type'  => 'verticalAlignWithDeviceSwitcher',
                                'title'       => __('Vertical alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center' ),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                //'css_identifier' => 'v_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                            ),
                            'spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing of the text content', 'text_doma' ),
                                'default'     => array('desktop' => array(
                                    'padding-bottom' => '5',
                                    'padding-top' => '5',
                                    'padding-right' => '5',
                                    'padding-left' => '5',
                                    'unit' => '%')
                                ),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors' => array( '.sek-slider-text-content' )
                            ),
                            'apply_overlay' => array(
                                'input_type'  => 'nimblecheck',
                                'notice_after' => __('A color overlay is usually recommended when displaying text content on top of the image. You can customize the color and transparency in the global design settings of the carousel.', 'text_doma' ),
                                'title'       => __('Apply a color overlay', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                            )
                        )
                    ),


                    array(
                        'title' => __( 'Click action', 'text_doma' ),
                        'inputs' => array(
                            'link-to' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Schedule an action on click or tap', 'text_doma'),
                                'default'     => 'no-link',
                                'choices'     => array(
                                    'no-link' => __('No click action', 'text_doma' ),
                                    'url' => __('Link to site content or custom url', 'text_doma' ),
                                    'img-file' => __('Link to image file', 'text_doma' ),
                                    'img-page' =>__('Link to image page', 'text_doma' )
                                ),
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'html_before' => '<hr/><h3>' . __('ACTION ON CLICK') .'</h3>',
                                'notice_after' => __('Note that some click actions are disabled during customization.', 'text_doma' ),
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
                            )
                        )
                    )
                )//'tabs'
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  SLIDER OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_opts_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_opts_child',
        'name' => __( 'Slider options : autoplay, ', 'text_doma' ),
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
                'pause_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Pause on mouse hover', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'autoplay' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Autoplay', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'autoplay_delay' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Delay between each slide in milliseconds (ms)', 'text_doma' ),
                    'min' => 1,
                    'max' => 10000,
                    'unit' => '',
                    'default' => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),
                'infinite_loop' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Infinite loop', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'height-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Height : auto or custom', 'text_doma'),
                    'default'     => 'custom',
                    'choices'     => sek_get_select_options_for_input_id( 'height-type' ),// auto, custom
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr/><h3>' . __('SLIDER HEIGHT') .'</h3>'
                ),
                'custom-height' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom height', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '350px', 'mobile' => '200px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                ),
                // 'apply-overlay' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Apply an overlay', 'text_doma'),
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'default'     => 0,
                //     'html_before' => '<hr/><h3>' . __('OVERLAY COLOR') .'</h3>'
                // ),
                'color-overlay' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Overlay Color', 'text_doma'),
                    'width-100'   => true,
                    'default'     => '#000000'
                ),
                'opacity-overlay' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Opacity (in percents)', 'text_doma'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    // 'unit' => '%',
                    'default'  => '40',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'nav_type' => array(
                    'input_type'  => 'simpleselect',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'default' => 'arrows_dots',
                    'choices'     => array(
                        'arrows_dots' => __('Arrows and dots', 'text_doma'),
                        'arrows' => __('Arrows', 'text_doma'),
                        'dots' => __('Dots', 'text_doma'),
                        'none' => __('None', 'text_doma')
                    ),
                    'html_before' => '<hr/><h3>' . __('NAVIGATION') .'</h3>'
                ),
                'arrows_size'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Size of the arrows', 'text_doma' ),
                    'default'     => array( 'desktop' => '16'),
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),//null,
                'arrows_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Arrows color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'dots_size'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Size of the dots', 'text_doma' ),
                    'default'     => array( 'desktop' => '16'),
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),//null,
                'dots_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Dots color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  DESIGN OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_fonts_child() {
    $pt_font_selectors = array( '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title a', '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title' );
    $pe_font_selectors = array( '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt', '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt *' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_fonts_child',
        'name' => __( 'Slider font options', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        // ),
        //'css_selectors' => array( '.sek-module-inner .sek-post-grid-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Text content', 'text_doma' ),
                        'inputs' => array(
                            'pe_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $pe_font_selectors,
                            ),
                            'pe_font_size_css'       => array(
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
                                'css_selectors' => $pe_font_selectors,
                            ),//16,//"14px",
                            'pe_line_height_css'     => array(
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
                                'css_selectors' => $pe_font_selectors,
                            ),//24,//"20px",
                            'pe_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#494949',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $pe_font_selectors,
                            ),//"#000000",
                            'pe_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $pe_font_selectors,
                            ),//"#000000",
                            'pe_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $pe_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'pe_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $pe_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'pe_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $pe_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Caption', 'text_doma' ),
                        'inputs' => array(
                            'pt_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $pt_font_selectors,
                            ),
                            'pt_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '28px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $pt_font_selectors,
                            ),//16,//"14px",
                            'pt_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.3em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $pt_font_selectors,
                            ),//24,//"20px",
                            'pt_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#444',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $pt_font_selectors,
                            ),//"#000000",
                            'pt_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $pt_font_selectors,
                            ),//"#000000",
                            'pt_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $pt_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'pt_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $pt_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'pt_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $pt_font_selectors,
                                'choices'    => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        )
                    )
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_img_slider_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_img_slider_collection_child', 10, 2 );

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
    //sek_error_log('SLIDER ITEMS PARAMS?', $params );

    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // VERTICAL ALIGNMENT
    if ( ! empty( $item_model[ 'v_alignment' ] ) ) {
        if ( ! is_array( $item_model[ 'v_alignment' ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
        }
        $v_alignment_value = is_array( $item_model[ 'v_alignment' ] ) ? $item_model[ 'v_alignment' ] : array();
        $v_alignment_value = wp_parse_args( $v_alignment_value, array(
            'desktop' => 'center',
            'tablet' => '',
            'mobile' => ''
        ));
        $mapped_values = array();
        foreach ( $v_alignment_value as $device => $align_val ) {
            switch ( $align_val ) {
                case 'top' :
                    $mapped_values[$device] = "flex-start";
                break;
                case 'center' :
                    $mapped_values[$device] = "center";
                break;
                case 'bottom' :
                    $mapped_values[$device] = "flex-end";
                break;
            }
        }
        $rules = sek_set_mq_css_rules( array(
            'value' => $mapped_values,
            'css_property' => 'align-items',
            'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] )
        ), $rules );
    }

    return $rules;
}


// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_img_slider_module', '\Nimble\sek_add_css_rules_for_czr_img_slider_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_img_slider_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $slider_options = $value['slider_options'];

    $selector = '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .swiper-container .swiper-wrapper';

    // CUSTOM HEIGHT BY DEVICE
    if ( ! empty( $slider_options[ 'height-type' ] ) ) {
        if ( 'custom' === $slider_options[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $slider_options ) ? $slider_options[ 'custom-height' ] : array();

            if ( ! is_array( $custom_user_height ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the height option should be an array( {device} => {number}{unit} )', $custom_user_height);
            }
            $custom_user_height = is_array( $custom_user_height ) ? $custom_user_height : array();
            $custom_user_height = wp_parse_args( $custom_user_height, array(
                'desktop' => '350px',
                'tablet' => '',
                'mobile' => '200px'
            ));
            $height_value = $custom_user_height;
            foreach ( $custom_user_height as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $unit = '%' === $unit ? 'vh' : $unit;
                    $height_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $height_value,
                'css_property' => 'height',
                'selector' => $selector
            ), $rules );
        }// if custom height
        else {
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => 'height:auto;',
                'mq' =>null
            );
        }
    }

    return $rules;
}


?>