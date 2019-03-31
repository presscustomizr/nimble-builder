<?php
/* ------------------------------------------------------------------------- *
 *  POST GRID MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_module',
        'name' => __('Post Grid', 'text_doma'),
        'is_father' => true,
        'children' => array(
            'grid_main'   => 'czr_post_grid_main_child',
            'grid_thumb'  => 'czr_post_grid_thumb_child',
            'grid_metas'  => 'czr_post_grid_metas_child',
            'grid_fonts'  => 'czr_post_grid_fonts_child',
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/post_grid_module_tmpl.php"
    );
}


/* ------------------------------------------------------------------------- *
 *  CHILD MAIN GRID SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_main_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_main_child',
        'name' => __( 'Main grid settings', 'text_doma' ),
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
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'layout'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Layout', 'text_doma' ),
                    'default'     => 'list',
                    'choices'      => array('list' => __('List', 'text_doma'), 'grid' => __('Grid', 'text_doma') )
                ),//null,
                'columns'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Number of columns', 'text_doma' ),
                    'default'     => array( 'desktop' => '2', 'tablet' => '1', 'mobile' => '1' ),
                    'min'         => 1,
                    'max'         => 4,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),//null,
                'img_column_width' => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Width of the image\'s column (in percent)', 'text_doma' ),
                    'default'     => array( 'desktop' => '30' ),
                    'min'         => 1,
                    'max'         => 100,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,
                'post_number'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Number of posts', 'text_doma' ),
                    'default'     => 3,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                ),//0,
                'categories'  => array(
                    'input_type'  => 'category_picker',
                    'title'       => __( 'Filter by category', 'text_doma' ),
                    'default'     => array(),
                    'choices'      => array(),
                    'width-100'   => true,
                    'notice_before' => __('Use this control to filter posts by category. Multiple categories allowed.', 'text_doma')
                ),//null,
                'order_by'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Order by', 'text_doma' ),
                    'default'     => 'date_desc',
                    'choices'      => array(
                        'date_desc' => __('Newest to oldest', 'text_doma'),
                        'date_asc' => __('Oldest to newest', 'text_doma'),
                        'title_asc' => __('A &rarr; Z', 'text_doma'),
                        'title_desc' => __('Z &rarr; A', 'text_doma')
                    )
                ),//null,
                'show_title' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display the post title', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_excerpt' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display the post excerpt', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'excerpt_length'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Excerpt length in words', 'text_doma' ),
                    'default'     => 20,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),//0,


                'apply_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Apply a shadow', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'has_tablet_breakpoint' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => '<i class="material-icons sek-level-option-icon">tablet_mac</i>' . __('Reorganize image and content vertically on tablet devices', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'has_mobile_breakpoint' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => '<i class="material-icons sek-level-option-icon">phone_iphone</i>' . __('Reorganize image and content vertically on smartphones devices', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'custom_grid_spaces' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define custom spaces between columns and rows', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true
                ),
                'column_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between columns', 'text_doma' ),
                    'min' => 1,
                    'max' => 100,
                    'default'     => array( 'desktop' => '20px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'row_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between rows', 'text_doma' ),
                    'min' => 1,
                    'max' => 100,
                    'default'     => array( 'desktop' => '25px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                )//null,
            )
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 *  CHILD IMG SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_thumb_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_thumb_child',
        'name' => __( 'Post thumbnail settings : size, design...', 'text_doma' ),
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
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                // IMAGE
                'show_thumb' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display post thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'notice_after' => __('This image can be set as "Featured image" when creating a post.', 'text_doma')
                ),
                'img_size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'medium',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('Select a size for this image among those generated by WordPress.', 'text_doma' )
                ),
                'img_has_custom_height' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Apply a custom height to the thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true
                ),
                'img_height' => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Thumbnail height (in percent of the image container\'s width)', 'text_doma' ),
                    'default'     =>  array( 'desktop' => '50' ),
                    'min'         => 1,
                    'max'         => 300,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('Tip : applying a height of 100% makes the image square.')
                ),//null,

                'use_post_thumb_placeholder' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Use a placeholder image when no post thumbnail is set', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  CHILD POST METAS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_metas_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_metas_child',
        'name' => __( 'Post metas : author, date, category, tags,...', 'text_doma' ),
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
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_cats' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display categories', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_author' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display author', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_date' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display date', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_comments' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display comment number', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  FONTS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_fonts_child() {
    $pt_font_selectors = array( '.sek-post-title' );
    $pe_font_selectors = array( '.sek-post-excerpt' );
    $cat_font_selectors = array( '.sek-pg-category' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_fonts_child',
        'name' => __( 'Grid text settings : fonts, colors, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-post-grid-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Post titles', 'text_doma' ),
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
                                'default'     => array( 'desktop' => '16px' ),
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
                                'default'     => '1.5em',
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
                                'default'     => '',
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
                    ),
                    array(
                        'title' => __( 'Excerpt', 'text_doma' ),
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
                                'default'     => '',
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
                        'title' => __( 'Categories', 'text_doma' ),
                        'inputs' => array(
                            'cat_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $cat_font_selectors,
                            ),
                            'cat_font_size_css'       => array(
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
                                'css_selectors' => $cat_font_selectors,
                            ),//16,//"14px",
                            'cat_line_height_css'     => array(
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
                                'css_selectors' => $cat_font_selectors,
                            ),//24,//"20px",
                            'cat_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $cat_font_selectors,
                            ),//"#000000",
                            'cat_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $cat_font_selectors,
                            ),//"#000000",
                            'cat_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $cat_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'cat_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $cat_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'cat_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $cat_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    )//tab
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_post_grid_module', '\Nimble\sek_add_css_rules_for_czr_post_grid_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_post_grid_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    //sek_error_log( 'ALORS ????', $complete_modul_model );
    $value = $complete_modul_model['value'];

    $main_settings = $value['grid_main'];
    $thumb_settings = $value['grid_thumb'];

    // IMG COLUMN WIDTH IN LIST
    // => only relevant when the thumbnail is displayed
    if ( 'list' === $main_settings['layout'] && true === sek_booleanize_checkbox_val( $thumb_settings['show_thumb'] ) ) {
        $img_column_width = $main_settings['img_column_width'];
        $img_column_width = is_array( $img_column_width ) ? $img_column_width : array();
        $img_column_width = wp_parse_args( $img_column_width, array(
            'desktop' => '30%',
            'tablet' => '',
            'mobile' => ''
        ));

        $img_column_width_ready_value = $img_column_width;
        foreach ($img_column_width as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            if ( ! empty( $num_val ) ) {
                $num_val = $num_val > 100 ? 100 : $num_val;
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_column_width_ready_value[$device] = sprintf('%s 1fr;', $num_val . '%');
            }
        }
        $rules = sek_set_mq_css_rules(array(
            'value' => $img_column_width_ready_value,
            'css_property' => 'grid-template-columns',
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-list-layout.sek-has-thumb article',
            'is_important' => false
        ), $rules );


        // $img_column_width = $img_column_width > 100 ? 100 : $img_column_width;
        // $img_column_width = $img_column_width < 1 ? 1 : $img_column_width;
        // $rules[] = array(
        //     'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-list-layout.sek-has-thumb article',
        //     'css_rules' => 'grid-template-columns:' . sprintf('%s 1fr;', $img_column_width . '%'),
        //     'mq' =>null
        // );
    }

    // IMG HEIGHT
    // we set the height of the image container ( <a> tag ), with the padding property
    // because padding and margin are relative to the width in CSS
    // @see https://www.w3.org/TR/2011/REC-CSS2-20110607/box.html#padding-properties
    if ( true === sek_booleanize_checkbox_val( $thumb_settings['img_has_custom_height'] ) ) {
        $img_height = $thumb_settings['img_height'];
        $img_height = is_array( $img_height ) ? $img_height : array();
        $img_height = wp_parse_args( $img_height, array(
            'desktop' => '50%',
            'tablet' => '',
            'mobile' => ''
        ));

        $img_height_ready_value = $img_height;
        foreach ($img_height as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            if ( ! empty( $num_val ) ) {
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_height_ready_value[$device] = sprintf('%s;', $num_val .'%');
            }
        }
        $rules = sek_set_mq_css_rules(array(
            'value' => $img_height_ready_value,
            'css_property' => 'padding-top',
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-thumb-custom-height figure a',
            'is_important' => false
        ), $rules );
    }

    // COLUMN AND ROW GAP
    if ( true === sek_booleanize_checkbox_val( $main_settings['custom_grid_spaces'] ) ) {
          // Horizontal Gap
          $gap = $main_settings['column_gap'];
          $gap = is_array( $gap ) ? $gap : array();
          $gap = wp_parse_args( $gap, array(
              'desktop' => '20px',
              'tablet' => '',
              'mobile' => ''
          ));
          // replace % by vh when needed
          $gap_ready_value = $gap;
          foreach ($gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              if ( ! empty( $numeric ) ) {
                  $unit = sek_extract_unit( $num_unit );
                  $gap_ready_value[$device] = $numeric . $unit;
              }
          }

          // for grid layout => gap between columns
          // for list layout => gap between image and content
          $rules = sek_set_mq_css_rules(array(
              'value' => $gap_ready_value,
              'css_property' => 'grid-column-gap',
              'selector' => implode( ',', [
                  '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-layout',
                  '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-list-layout.sek-has-thumb article'
              ] ),
              'is_important' => false
          ), $rules );

          // Vertical Gap => common to list and grid layout
          $v_gap = $main_settings['row_gap'];
          $v_gap = is_array( $v_gap ) ? $v_gap : array();
          $v_gap = wp_parse_args( $v_gap, array(
              'desktop' => '25px',
              'tablet' => '',
              'mobile' => ''
          ));
          // replace % by vh when needed
          $v_gap_ready_value = $v_gap;
          foreach ($v_gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              if ( ! empty( $numeric ) ) {
                  $unit = sek_extract_unit( $num_unit );
                  $v_gap_ready_value[$device] = $numeric . $unit;
              }
          }

          $rules = sek_set_mq_css_rules(array(
              'value' => $v_gap_ready_value,
              'css_property' => 'grid-row-gap',
              'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items',
              'is_important' => false
          ), $rules );
    }


    // BORDERS
    // $border_settings = $value[ 'spacing_border' ][ 'borders' ];
    // $border_type = $value[ 'spacing_border' ][ 'border-type' ];
    // $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    // //border width + type + color
    // if ( $has_border_settings ) {
    //     $rules = sek_generate_css_rules_for_multidimensional_border_options(
    //         $rules,
    //         $border_settings,
    //         $border_type,
    //         '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon-wrapper'
    //     );
    // }

    return $rules;
}
?>