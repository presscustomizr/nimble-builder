<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_layout_bg_module() {
    return array(
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
                                'input_type'  => 'wp_color_alpha',
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
                                'input_type'  => 'wp_color_alpha',
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
                                'title'       => __('Boxed or full width', 'text_domain_to_be_replaced'),
                                'refresh-markup' => true,
                                'refresh-stylesheet' => false
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
                                'input_type'  => 'wp_color_alpha',
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
    );
}
?>