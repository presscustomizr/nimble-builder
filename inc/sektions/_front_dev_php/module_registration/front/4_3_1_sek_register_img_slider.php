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
        'name' => __('Image & Text Carousel', 'text_doma'),
        'starting_value' => array(
            'img_collection' => array(
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' )
            )
        ),
        'sanitize_callback' => '\Nimble\sanitize_cb__czr_img_slider_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '[data-sek-swiper-id]' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "img_slider_tmpl.php",
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
function sanitize_cb__czr_img_slider_module( $value ) {
    if ( !is_array( $value ) )
        return $value;

    if ( !empty($value['img_collection']) && is_array( $value['img_collection'] ) ) {
        foreach( $value['img_collection'] as $key => $data ) {
            if ( array_key_exists( 'text_content', $data ) && is_string( $data['text_content'] ) ) {
                $value['img_collection'][$key]['text_content'] = sek_maybe_encode_richtext( $data['text_content'] );
            }
        }
    }
    return $value;
}


/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_collection_child() {
    $text_content_selector = array( '.sek-slider-text-content', '.sek-slider-text-content *' );
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = __( 'add transition options with effects like fade or flip, link slides individually to any content.', 'text-doma');
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">photo_library</i> %1$s', __( 'Slide collection', 'text_doma' ) ),
        'starting_value' => array(
            'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
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
                        'title' => __( 'Image', 'text_doma' ),
                        'inputs' => array(
                            'img' => array(
                                'input_type'  => 'upload',
                                'title'       => __('Pick an image', 'text_doma'),
                                'default'     => ''
                            ),
                            'img-size' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Select the image size', 'text_doma'),
                                'default'     => 'large',
                                'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                                'notice_before' => __('Select a size for this image among those generated by WordPress.', 'text_doma' )
                            ),
                            'title_attr'  => array(
                                'input_type'  => 'text',
                                'default'     => '',
                                'title'       => __('Title', 'text_domain_to_be_replaced'),
                                'notice_after' => sprintf( __('This is the text displayed on mouse over. You can use the following template tags referring to the image attributes : %1$s', 'text_domain_to_be_replaced'), '&#123;&#123;title&#125;&#125;, &#123;&#123;caption&#125;&#125;, &#123;&#123;description&#125;&#125;' ),
                                'html_after' => $pro_text
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
                                'notice_after' => __('Note : you can adjust the text color and / or use a color overlay to improve accessibility of your text content.', 'text_doma')
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
                                'refresh_markup'    => '.sek-slider-text-content',
                                'notice_before' => sprintf( __('You may use some html tags in the "text" tab of the editor. You can also use the following template tags referring to the image attributes : %1$s', 'text_domain_to_be_replaced'), '&#123;&#123;title&#125;&#125;, &#123;&#123;caption&#125;&#125;, &#123;&#123;description&#125;&#125;' )
                            ),

                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#e2e2e2',// why this light grey ? => if set to white ( #fff ), the text is not visible when no image is picked, which might be difficult to understand for users
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $text_content_selector,
                            ),//"#000000",

                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $text_content_selector,
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
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $text_content_selector,
                            ),//16,//"14px",
                            'line_height_css'     => array(
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
                                'css_selectors' => $text_content_selector,
                            ),//24,//"20px",

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
                                'html_before' => '<hr/><h3>' . __('ALIGNMENTS') .'</h3>'
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
                                'css_selectors' => array( '.sek-slider-text-content' ),
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Color overlay', 'text_doma' ),
                        'inputs' => array(
                            'apply-overlay' => array(
                                'input_type'  => 'nimblecheck',
                                'notice_after' => __('A color overlay is usually recommended when displaying text content on top of the image. You can customize the color and transparency in the global design settings of the carousel.', 'text_doma' ),
                                'title'       => __('Apply a color overlay', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'html_before' => '<hr/><h3>' . __('COLOR OVERLAY') .'</h3>'
                            ),
                            'color-overlay' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Overlay Color', 'text_doma'),
                                'width-100'   => true,
                                'default'     => '#000000',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true
                            ),
                            'opacity-overlay' => array(
                                'input_type'  => 'range_simple',
                                'title'       => __('Opacity (in percents)', 'text_doma'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                // 'unit' => '%',
                                'default'  => '30',
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true
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
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = __( 'add transition options with effects like fade and flip, link slides individually to any content.', 'text-doma');
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Slider options : height, autoplay, navigation...', 'text_doma' ) ),
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
                            'image-layout' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Image layout', 'text_doma'),
                                'default'     => 'width-100',
                                'choices'     => array(
                                    'nimble-wizard' => __('Nimble wizard', 'text_doma' ),
                                    'cover' => __('Images fill space and are centered without being stretched', 'text_doma'),
                                    'width-100' => __('Adapt images to carousel\'s width', 'text_doma' ),
                                    'height-100' => __('Adapt images to carousel\'s height', 'text_doma' ),
                                ),
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'notice_before' => __('Nimble wizard ensures that the images fill all available space of the carousel in any devices, without blank spaces on the edges, and without stretching the images.', 'text_doma' ),
                            ),
                            'autoplay' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Autoplay', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Note that the autoplay is disabled during customization.', 'text_doma' ),
                            ),
                            'autoplay_delay' => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Delay between each slide in milliseconds (ms)', 'text_doma' ),
                                'min' => 1,
                                'max' => 30000,
                                'step' => 500,
                                'unit' => '',
                                'default' => 3000,
                                'width-100'   => true,
                                'title_width' => 'width-100'
                            ),
                            'pause_on_hover' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Pause autoplay on mouse over', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'infinite_loop' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Infinite loop', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            // added dec 2019 for https://github.com/presscustomizr/nimble-builder/issues/570
                            'lazy_load' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Lazy load images', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Lazy loading images improves page load performances.', 'text_doma' ),
                                'html_before' => $pro_text
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
                                'css_selectors'=> '.swiper-slide'
                            )
                        )//inputs
                    ),
                    array(
                        'title' => __( 'Height', 'text_doma' ),
                        'inputs' => array(
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
                                'max' => 1000,
                                'default'     => array( 'desktop' => '400px', 'mobile' => '200px' ),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Navigation', 'text_doma' ),
                        'inputs' => array(
                            'nav_type' => array(
                                'input_type'  => 'simpleselect',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'default' => 'arrows',
                                'choices'     => array(
                                    'arrows_dots' => __('Arrows and bullets', 'text_doma'),
                                    'arrows' => __('Arrows only', 'text_doma'),
                                    'dots' => __('Bullets only', 'text_doma'),
                                    'none' => __('None', 'text_doma')
                                ),
                                'html_before' => '<hr/><h3>' . __('NAVIGATION') .'</h3>'
                            ),
                            'hide_nav_on_mobiles' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Hide arrows and bullets on mobiles', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            // 'arrows_size'  => array(
                            //     'input_type'  => 'range_simple_device_switcher',
                            //     'title'       => __( 'Size of the arrows', 'text_doma' ),
                            //     'default'     => array( 'desktop' => '18'),
                            //     'min'         => 1,
                            //     'max'         => 50,
                            //     'step'        => 1,
                            //     'width-100'   => true,
                            //     'title_width' => 'width-100'
                            // ),//null,
                            'arrows_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Color of the navigation arrows', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => array('.sek-swiper-nav .sek-swiper-arrows .sek-chevron')
                            ),
                            // 'dots_size'  => array(
                            //     'input_type'  => 'range_simple_device_switcher',
                            //     'title'       => __( 'Size of the dots', 'text_doma' ),
                            //     'default'     => array( 'desktop' => '16'),
                            //     'min'         => 1,
                            //     'max'         => 50,
                            //     'step'        => 1,
                            //     'width-100'   => true,
                            //     'title_width' => 'width-100'
                            // ),//null,
                            'dots_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Color of the active pagination bullet', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => array('.swiper-pagination-bullet-active')
                            ),
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
    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();
    $all_defaults = sek_get_default_module_model( 'czr_img_slider_collection_child');
    // Default :
    // [v_alignment] => Array
    // (
    //     [desktop] => center
    // )
    // VERTICAL ALIGNMENT
    if ( !empty( $item_model[ 'v_alignment' ] ) && $all_defaults['v_alignment'] != $item_model[ 'v_alignment' ] ) {
        if ( !is_array( $item_model[ 'v_alignment' ] ) ) {
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
                    $mapped_values[$device] = "align-items:flex-start;-webkit-box-align:start;-ms-flex-align:start;";
                break;
                case 'center' :
                    $mapped_values[$device] = "align-items:center;-webkit-box-align:center;-ms-flex-align:center;";
                break;
                case 'bottom' :
                    $mapped_values[$device] = "align-items:flex-end;-webkit-box-align:end;-ms-flex-align:end";
                break;
            }
        }
        $rules = sek_set_mq_css_rules_supporting_vendor_prefixes( array(
            'css_rules_by_device' => $mapped_values,
            'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] ),
            'level_id' => $params['parent_module_id']
        ), $rules );
    }//Vertical alignment

    //Background overlay?
    // 1) a background image should be set
    // 2) the option should be checked
    if ( sek_is_checked( $item_model[ 'apply-overlay'] ) ) {
        //(needs validation: we need a sanitize hex or rgba color)
        $bg_color_overlay = isset( $item_model[ 'color-overlay' ] ) ? $item_model[ 'color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            //overlay pseudo element
            $bg_overlay_css_rules = 'background-color:'.$bg_color_overlay;

            //opacity
            //validate/sanitize
            $bg_overlay_opacity     = isset( $item_model[ 'opacity-overlay' ] ) ? filter_var( $item_model[ 'opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_css_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_css_rules . ';opacity:' . $bg_overlay_opacity . ';' : $bg_overlay_css_rules;

            $rules[]     = array(
                'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"][data-sek-has-overlay="true"] .sek-carousel-img::after', $params['parent_module_id'], $item_model['id'] ),
                'css_rules' => $bg_overlay_css_rules,
                'mq' =>null
            );
        }
    }// BG Overlay

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

    $selector = '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .swiper .swiper-wrapper';


    // CUSTOM HEIGHT BY DEVICE
    if ( !empty( $slider_options[ 'height-type' ] ) ) {
        if ( 'custom' === $slider_options[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $slider_options ) ? $slider_options[ 'custom-height' ] : array();

            if ( !is_array( $custom_user_height ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the height option should be an array( {device} => {number}{unit} )', $custom_user_height);
            }
            $custom_user_height = is_array( $custom_user_height ) ? $custom_user_height : array();

            // DEFAULTS :
            // array(
            //     'desktop' => '400px',
            //     'tablet' => '',
            //     'mobile' => '200px'
            // );
            $all_defaults = sek_get_default_module_model( 'czr_img_slider_module');
            $slider_defaults = $all_defaults['slider_options'];
            $defaults = $slider_defaults['custom-height'];

            $custom_user_height = wp_parse_args( $custom_user_height, $defaults );

            if ( $defaults != $custom_user_height ) {
                $height_value = $custom_user_height;
                foreach ( $custom_user_height as $device => $num_unit ) {
                    $numeric = sek_extract_numeric_value( $num_unit );
                    if ( !empty( $numeric ) ) {
                        $unit = sek_extract_unit( $num_unit );
                        $unit = '%' === $unit ? 'vh' : $unit;
                        $height_value[$device] = $numeric . $unit;
                    }
                }

                $rules = sek_set_mq_css_rules(array(
                    'value' => $height_value,
                    'css_property' => 'height',
                    'selector' => $selector,
                    'level_id' => $complete_modul_model['id']
                ), $rules );
            }
        }// if custom height
        else {
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => 'height:auto;',
                'mq' =>null
            );
        }
    }// Custom height rules

    return $rules;
}


?>