<?php
// The base fmk is loaded on after_setup_theme before 50


add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_register_modules() {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }



    /* ------------------------------------------------------------------------- *
     *  MODULE PICKER MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'sek_module_picker_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'module_id' => array(
                    'input_type'  => 'module_picker',
                    'title'       => __('Pick a module', 'text_domain_to_be_replaced')
                )
            )
        )
    ));




    /* ------------------------------------------------------------------------- *
     *  SECTION PICKER MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'sek_section_picker_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'section_id' => array(
                    'input_type'  => 'section_picker',
                    'title'       => __('Pick a section', 'text_domain_to_be_replaced')
                )
            )
        )
    ));





    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_layout_bg_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Background', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'bg-color' => array(
                                'input_type'  => 'wp_color_apha',
                                'title'       => __('Background color', 'text_domain_to_be_replaced'),
                                'width-100'   => true
                            ),
                            'bg-image' => array(
                                'input_type'  => 'upload',
                                'title'       => __('Image', 'text_domain_to_be_replaced')
                            ),
                            'bg-position' => array(
                                'input_type'  => 'bg_position',
                                'title'       => __('Image position', 'text_domain_to_be_replaced'),
                                'default'     => 'center'
                            ),
                            // 'bg-parallax' => array(
                            //     'input_type'  => 'gutencheck',
                            //     'title'       => __('Parallax scrolling', 'text_domain_to_be_replaced')
                            // ),
                            'bg-attachment' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Fixed background', 'text_domain_to_be_replaced')
                            ),
                            // 'bg-repeat' => array(
                            //     'input_type'  => 'select',
                            //     'title'       => __('repeat', 'text_domain_to_be_replaced')
                            // ),
                            'bg-scale' => array(
                                'input_type'  => 'select',
                                'title'       => __('scale', 'text_domain_to_be_replaced')
                            ),
                            'bg-video' => array(
                                'input_type'  => 'text',
                                'title'       => __('Video', 'text_domain_to_be_replaced')
                            ),
                            'bg-apply-overlay' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply a background overlay', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'bg-color-overlay' => array(
                                'input_type'  => 'wp_color_apha',
                                'title'       => __('Overlay Color', 'text_domain_to_be_replaced'),
                                'width-100'   => true
                            ),
                            'bg-opacity-overlay' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Opacity', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                'unit' => '%'
                            )
                        )
                    ),
                    array(
                        'title' => __('Layout', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'boxed-wide' => array(
                                'input_type'  => 'select',
                                'title'       => __('Boxed or full width', 'text_domain_to_be_replaced')
                            ),

                            /* suspended, needs more thoughts
                            'boxed-width' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Custom boxed width', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 500,
                                'max' => 1600,
                                'unit' => 'px'
                            ),*/
                            'height-type' => array(
                                'input_type'  => 'select',
                                'title'       => __('Height : fit to screen or custom', 'text_domain_to_be_replaced')
                            ),
                            'custom-height' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Custom height', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                'unit' => '%'
                            ),
                            'v-alignment' => array(
                                'input_type'  => 'v_alignment',
                                'title'       => __('Vertical alignment', 'text_domain_to_be_replaced'),
                                'default'     => 'center'
                            ),
                        )
                    ),
                    array(
                        'title' => __('Border', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'border-width' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Border width', 'text_domain_to_be_replaced'),
                                'min' => 0,
                                'max' => 100,
                                'unit' => 'px'
                            ),
                            'border-type' => array(
                                'input_type'  => 'select',
                                'title'       => __('Border shape', 'text_domain_to_be_replaced')
                            ),
                            'border-color' => array(
                                'input_type'  => 'wp_color_apha',
                                'title'       => __('Border color', 'text_domain_to_be_replaced'),
                                'width-100'   => true,
                            ),
                            'shadow' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply a shadow', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            )
                        )
                    ),
                )//tabs
            )//item-inputs
        )//tmpl
    ));





    /* ------------------------------------------------------------------------- *
     *  SPACING MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'sek_spacing_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',

        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Desktop', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'desktop_pad_marg' => array(
                                'input_type'  => 'spacing',
                                'title'       => __('Set padding and margin for Desktop', 'text_domain_to_be_replaced')
                            ),
                            'desktop_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced')
                            )
                        )
                    ),
                    array(
                        'title' => __('Tablet', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-device="tablet"',
                        'inputs' => array(
                            'tablet_pad_marg' => array(
                                'input_type'  => 'spacing',
                                'title'       => __('Set padding and margin for tablet devices', 'text_domain_to_be_replaced')
                            ),
                            'tablet_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced')
                            )
                        )
                    ),
                    array(
                        'title' => __('Mobile', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-device="mobile"',
                        'inputs' => array(
                            'mobile_pad_marg' => array(
                                'input_type'  => 'spacing',
                                'title'       => __('Set padding and margin for mobile devices', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-100',
                            ),
                            'mobile_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced')
                            )
                        )
                    )

                )
            )
        )
    ));




    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER THE SIMPLE HTML MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_html_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'html_content' => array(
                    'input_type'  => 'textarea',
                    'title'       => __('HTML Content', 'text_domain_to_be_replaced')
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_html_module_tmpl.php",
        'placeholder_icon' => 'code'
    ));




    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER THE TEXT EDITOR MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Content', 'text_domain_to_be_replaced')
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    ));




    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER IMAGE MODULE
    /* ------------------------------------------------------------------------- */
        // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_domain_to_be_replaced')
                ),
                'img-size' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                    'default'     => 'large'
                ),
                'alignment' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center'
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced')
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_domain_to_be_replaced')
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Link url', 'text_domain_to_be_replaced')
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_domain_to_be_replaced')
                ),
                'lightbox' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Activate a lightbox on click', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 'center'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    ));






    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER FEATURED PAGES MODULE
    /* ------------------------------------------------------------------------- */
        // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_featured_pages_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_domain_to_be_replaced')
                // ),
                'img-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display an image', 'text_domain_to_be_replaced'),
                    'default'     => 'featured'
                ),
            ),
            'item-inputs' => array(
                'page-id' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Pick a page', 'text_domain_to_be_replaced')
                ),
                'img-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display an image', 'text_domain_to_be_replaced'),
                    'default'     => 'featured'
                ),
                'img-id' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_domain_to_be_replaced')
                ),
                'img-size' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                    'default'     => 'large'
                ),
                'content-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display a text', 'text_domain_to_be_replaced'),
                    'default'     => 'page-excerpt'
                ),
                'content-custom-text' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Custom text content', 'text_domain_to_be_replaced'),
                    'default'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
                ),
                'btn-display' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display a call to action button', 'text_domain_to_be_replaced'),
                    'default'     => true
                ),
                'btn-custom-text' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Custom button text', 'text_domain_to_be_replaced'),
                    'default'     => __('Read More', 'text_domain_to_be_replaced'),
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/featured_pages_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    ));
}//sek_register_modules()
?>