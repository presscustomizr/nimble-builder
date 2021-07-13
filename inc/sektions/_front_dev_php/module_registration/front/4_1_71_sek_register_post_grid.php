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
        'render_tmpl_path' => "post_grid_module_tmpl.php"
    );
}


/* ------------------------------------------------------------------------- *
 *  CHILD MAIN GRID SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_main_child() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sprintf( __( '%1$s + more controls on grid items content, like shadow, background, spacing...', 'text-doma'),
            sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" style="text-decoration:underline">%2$s</a>',
                'https://nimblebuilder.com/post-grid-sections/#pro-grid-one',
                __('masonry grid', 'text-doma')
            )
        );
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_main_child',
        'name' => __( 'Main grid settings : layout, number of posts, columns,...', 'text_doma' ),
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
                'use_current_query' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use contextual WordPress post query', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('This option allows you to use the posts normally displayed by WordPress on this page.', 'text_doma')
                    //'html_before' => '<hr>'
                ),
                'replace_query' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Add custom parameters to the contextual WordPress post query', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'display_pagination' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display pagination links', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'post_number'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Number of posts', 'text_doma' ),
                    'default'     => 3,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true
                ),//0,
                'posts_per_page'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Posts per page', 'text_doma' ),
                    'default'     => 10,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),//0,
                'order_by'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Order posts by', 'text_doma' ),
                    'default'     => 'date_desc',
                    'choices'      => array(
                        'date_desc' => __('Newest to oldest', 'text_doma'),
                        'date_asc' => __('Oldest to newest', 'text_doma'),
                        'title_asc' => __('A &rarr; Z', 'text_doma'),
                        'title_desc' => __('Z &rarr; A', 'text_doma')
                    )
                ),//null,
                'include_sticky' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Include "sticky" posts', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'categories'  => array(
                    'input_type'  => 'category_picker',
                    'title'       => __( 'Filter posts by category', 'text_doma' ),
                    'default'     => array(),
                    'choices'      => array(),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'notice_before' => __('Display posts that have these categories. Multiple categories allowed.', 'text_doma')
                ),//null,
                'must_have_all_cats' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display posts that have "all" of these categories', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'layout'  => array(
                    'input_type'  => 'grid_layout',
                    'title'       => __( 'Posts layout : list or grid', 'text_doma' ),
                    'default'     => 'list',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_before' => '<hr>',
                    'refresh_stylesheet' => true, //<= some CSS rules are layout dependant
                    'html_before' => $pro_text
                ),//null,
                'columns'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Number of columns', 'text_doma' ),
                    'default'     => array( 'desktop' => '2', 'tablet' => '2', 'mobile' => '1' ),
                    'min'         => 1,
                    'max'         => 12,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_stylesheet' => true //<= some CSS rules are layout dependant
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
                'has_tablet_breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => '<i class="material-icons sek-input-title-icon">tablet_mac</i>' . __('Reorganize image and content vertically on tablet devices', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true
                    //'html_before' => '<hr>'
                ),
                'has_mobile_breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => '<i class="material-icons sek-input-title-icon">phone_iphone</i>' . __('Reorganize image and content vertically on smartphones devices', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true
                ),

                'show_title' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display the post title', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr>'
                ),
                'show_excerpt' => array(
                    'input_type'  => 'nimblecheck',
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
                'space_between_el' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space between text blocks', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '10px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),

                'pg_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Text blocks alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'css_selectors' => array( '.sek-post-grid-wrapper .sek-pg-content' ),
                    'html_before' => '<hr>'
                ),

                'content_padding' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Content blocks padding', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '0px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),

                'custom_grid_spaces' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define custom spaces between columns and rows', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr>'
                ),
                'column_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between columns', 'text_doma' ),
                    'min' => 0,
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
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '25px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,
                'apply_shadow_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a shadow effect when hovering with the cursor', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr/>'
                )
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
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display post thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'notice_after' => __('The post thumbnail can be set as "Featured image" when creating a post.', 'text_doma')
                ),
                'img_size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the source image size of the thumbnail', 'text_doma'),
                    'title_width' => 'width-100',
                    'default'     => 'medium_large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('This allows you to select a preferred image size among those generated by WordPress.', 'text_doma' ),
                    'notice_after' => __('Note that Nimble Builder will let browsers choose the most appropriate size for better performances.', 'text_doma' )
                ),
                'img_has_custom_height' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a custom height to the thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr>'
                ),
                'img_height' => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Thumbnail height', 'text_doma' ),
                    'default'     =>  array( 'desktop' => '65' ),
                    'min'         => 1,
                    'max'         => 300,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_before' => __('Tip : the height is in percent of the image container\'s width. Applying a height of 100% makes the image square.')
                ),//null,
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '4px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> '.sek-pg-thumbnail img'
                ),
                'use_post_thumb_placeholder' => array(
                    'input_type'  => 'nimblecheck',
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
        'name' => __( 'Post metas : author, date, category, ...', 'text_doma' ),
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
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display categories', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_author' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display author', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_date' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display date', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_comments' => array(
                    'input_type'  => 'nimblecheck',
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
    $pt_font_selectors = array( '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title a', '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title' );
    $pe_font_selectors = array( '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt', '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt *' );
    $cat_font_selectors = array( '.sek-module-inner .sek-pg-category', '.sek-module-inner .sek-pg-category a' );
    $metas_font_selectors = array( '.sek-module-inner .sek-pg-metas', '.sek-module-inner .sek-pg-metas span', '.sek-module-inner .sek-pg-metas a');
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
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'pt_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '28px', 'tablet' => '22px', 'mobile' => '20px' ),
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
                                'default'     => '#121212',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $pt_font_selectors,
                            ),//"#000000",
                            'pt_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '#666',
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
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'pe_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '14px' ),
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
                                'default'     => '1.3em',
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
                                'default'     => '#555',
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
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'cat_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '13px' ),
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
                                'default'     => '1.2em',
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
                                'default'     => '#767676',
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
                                'default'     => 'uppercase',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $cat_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Metas', 'text_doma' ),
                        'inputs' => array(
                            'met_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $metas_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'met_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '13px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $metas_font_selectors,
                            ),//16,//"14px",
                            'met_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.2em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $metas_font_selectors,
                            ),//24,//"20px",
                            'met_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#767676',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $metas_font_selectors,
                            ),//"#000000",
                            'met_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $metas_font_selectors,
                            ),//"#000000",
                            'met_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $metas_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'met_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $metas_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'met_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'uppercase',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $metas_font_selectors,
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

    $value = $complete_modul_model['value'];
    $main_settings = $value['grid_main'];
    $thumb_settings = $value['grid_thumb'];


    // SPACE BETWEEN CONTENT ELEMENTS
    $margin_bottom = $main_settings['space_between_el'];
    $margin_bottom = is_array( $margin_bottom ) ? $margin_bottom : array();
    $defaults = array(
        'desktop' => '10px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $margin_bottom = wp_parse_args( $margin_bottom, $defaults );
    $margin_bottom_ready_val = $margin_bottom;
    foreach ($margin_bottom as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $margin_bottom_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( !empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $margin_bottom_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $margin_bottom_ready_val,
        'css_property' => 'margin-bottom',
        'selector' => implode(',', array(
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items article .sek-pg-content > *:not(:last-child)',
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items.sek-list-layout article > *:not(:last-child):not(.sek-pg-thumbnail)',
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items.sek-grid-layout article > *:not(:last-child)'
        )),
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );



    // CONTENT BLOCKS PADDING
    $content_padding = $main_settings['content_padding'];
    $content_padding = is_array( $content_padding ) ? $content_padding : array();
    $defaults = array(
        'desktop' => '0px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $content_padding = wp_parse_args( $content_padding, $defaults );
    $content_padding_ready_val = $content_padding;
    foreach ($content_padding as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $content_padding_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( !empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $content_padding_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $content_padding_ready_val,
        'css_property' => 'padding',
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items article .sek-pg-content',
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );



    // IMG COLUMN WIDTH IN LIST
    // - only relevant when the thumbnail is displayed
    // - default value is array( 'desktop' => '30' )
    // - default css is .sek-list-layout article.sek-has-thumb { grid-template-columns: 30% minmax(0,1fr); }
    if ( 'list' === $main_settings['layout'] && true === sek_booleanize_checkbox_val( $thumb_settings['show_thumb'] ) ) {
        $img_column_width = $main_settings['img_column_width'];
        $img_column_width = is_array( $img_column_width ) ? $img_column_width : array();
        $defaults = array(
            'desktop' => '30%',// <= this value matches the static CSS rule and the input default for the module
            'tablet' => '',
            'mobile' => ''
        );
        $img_column_width = wp_parse_args( $img_column_width, $defaults );
        $img_column_width_ready_value = $img_column_width;
        foreach ($img_column_width as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            $img_column_width_ready_value[$device] = '';
            // Leave the device value empty if === to default
            // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
            // fixes https://github.com/presscustomizr/nimble-builder/issues/419
            if ( !empty( $num_val ) && $num_val.'%' !== $defaults[$device].'' ) {
                $num_val = $num_val > 100 ? 100 : $num_val;
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_column_width_ready_value[$device] = sprintf('%s minmax(0,1fr);', $num_val . '%');
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $img_column_width_ready_value,
            'css_property' => array( 'grid-template-columns', '-ms-grid-columns' ),
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-list-layout article.sek-has-thumb',
            'is_important' => false,
            'level_id' => $complete_modul_model['id']
        ), $rules );
    }

    // IMG HEIGHT
    // we set the height of the image container ( <a> tag ), with the padding property
    // because padding and margin are relative to the width in CSS
    // @see https://www.w3.org/TR/2011/REC-CSS2-20110607/box.html#padding-properties
    if ( true === sek_booleanize_checkbox_val( $thumb_settings['img_has_custom_height'] ) ) {
        $img_height = $thumb_settings['img_height'];
        $img_height = is_array( $img_height ) ? $img_height : array();
        $defaults = array(
            'desktop' => '65%',// <= this value matches the static CSS rule and the input default for the module
            'tablet' => '',
            'mobile' => ''
        );
        $img_height = wp_parse_args( $img_height, $defaults );

        $img_height_ready_value = $img_height;
        foreach ( $img_height as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            $img_height_ready_value[$device] = '';
            // Leave the device value empty if === to default
            // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
            // fixes https://github.com/presscustomizr/nimble-builder/issues/419
            if ( !empty( $num_val ) && $num_val.'%' !== $defaults[$device].'' ) {
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_height_ready_value[$device] = sprintf('%s;', $num_val .'%');
            }
        }
        $rules = sek_set_mq_css_rules(array(
            'value' => $img_height_ready_value,
            'css_property' => 'padding-top',
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-thumb-custom-height figure a',
            'is_important' => false,
            'level_id' => $complete_modul_model['id']
        ), $rules );
    }


    // COLUMN AND ROW GAP
    if ( true === sek_booleanize_checkbox_val( $main_settings['custom_grid_spaces'] ) ) {
          // Horizontal Gap
          $gap = $main_settings['column_gap'];
          $gap = is_array( $gap ) ? $gap : array();
          $defaults = array(
              'desktop' => '20px',// <= this value matches the static CSS rule and the input default for the module
              'tablet' => '',
              'mobile' => ''
          );
          $gap = wp_parse_args( $gap, $defaults );
          // replace % by vh when needed
          $gap_ready_value = $gap;
          foreach ($gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              $numeric = $numeric < 0 ? '0' : $numeric;
              $gap_ready_value[$device] = '';
              // Leave the device value empty if === to default
              // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
              // fixes https://github.com/presscustomizr/nimble-builder/issues/419
              //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
              if ( !empty( $num_unit ) ) {
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
                  '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-post-grid-wrapper .sek-grid-layout',
                  '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-post-grid-wrapper .sek-list-layout article.sek-has-thumb'
              ] ),
              'is_important' => false,
              'level_id' => $complete_modul_model['id']
          ), $rules );

          // Vertical Gap => common to list and grid layout
          $v_gap = $main_settings['row_gap'];
          $v_gap = is_array( $v_gap ) ? $v_gap : array();
          $defaults = array(
              'desktop' => '25px',// <= this value matches the static CSS rule and the input default for the module
              'tablet' => '',
              'mobile' => ''
          );
          $v_gap = wp_parse_args( $v_gap, $defaults );
          // replace % by vh when needed
          $v_gap_ready_value = $v_gap;
          foreach ($v_gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              $numeric = $numeric < 0 ? 0 : $numeric;
              $v_gap_ready_value[$device] = '';
              // Leave the device value empty if === to default
              // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
              // fixes https://github.com/presscustomizr/nimble-builder/issues/419
              //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
              if ( !empty( $num_unit ) ) {
                  $unit = sek_extract_unit( $num_unit );
                  $v_gap_ready_value[$device] = $numeric . $unit;
              }
          }

          $rules = sek_set_mq_css_rules(array(
              'value' => $v_gap_ready_value,
              'css_property' => 'grid-row-gap',
              'selector' => '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-post-grid-wrapper .sek-grid-items',
              'is_important' => false,
              'level_id' => $complete_modul_model['id']
          ), $rules );
    }


    // TABLET AND MOBILE BREAKPOINT SETUP
    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];// 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    $custom_tablet_breakpoint = $tablet_breakpoint;

    // Is there a global custom breakpoint set ?
    $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
    $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;
    // Does the parent section have a custom breakpoint set ?
    $section_custom_breakpoint = intval( sek_get_closest_section_custom_breakpoint( array( 'searched_level_id' => $complete_modul_model['id'] ) ) );
    $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

    // Use section breakpoint in priority, then global one
    if ( $has_section_custom_breakpoint ) {
        $custom_tablet_breakpoint = $section_custom_breakpoint;
    } else if ( $has_global_custom_breakpoint ) {
        $custom_tablet_breakpoint = $global_custom_breakpoint;
    }

    $tablet_breakpoint = $custom_tablet_breakpoint;
    // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
    $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;

    $tab_bp_val = $tablet_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551
    $mob_bp_val = $mobile_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551


    // GRID LAYOUT
    // NUMBER OF COLUMNS BY DEVICE IN CASE OF A CUSTOM BREAKPOINT, GLOBAL OR FOR THE SECTION
    // Get the default breakpoint values
    if ( 'grid' === $main_settings['layout'] ) {
        // BASE CSS RULES
        // .sek-grid-layout.sek-all-col-1 {
        //   -ms-grid-columns: minmax(0,1fr);
        //   grid-template-columns: repeat(1, minmax(0,1fr));
        // }
        // .sek-grid-layout.sek-all-col-2 {
        //   -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
        //   grid-template-columns: repeat(2, minmax(0,1fr));
        //   grid-column-gap: 20px;
        //   grid-row-gap: 20px;
        // }
        $col_nb_gap_map = [
            'col-1' => null,
            'col-2' => '20px',
            'col-3' => '15px',
            'col-4' => '15px',
            'col-5' => '10px',
            'col-6' => '10px',
            'col-7' => '10px',
            'col-8' => '10px',
            'col-9' => '10px',
            'col-10' => '5px',
            'col-11' => '5px',
            'col-12' => '5px'
        ];
        if ( !isset(Nimble_Manager()->generic_post_grid_css_rules_written) ) {
            foreach ($col_nb_gap_map as $col_nb_index => $col_gap) {
                $col_nb = intval( str_replace('col-', '', $col_nb_index ) );
                $ms_grid_columns = [];
                // Up to 12 columns
                for ($j=1; $j <= $col_nb; $j++) {
                    if ( $j > 1 ) {
                        $ms_grid_columns[] = $col_gap;
                    }
                    $ms_grid_columns[] = 'minmax(0,1fr)';
                }
                $ms_grid_columns = implode(' ', $ms_grid_columns);

                $grid_template_columns = "repeat({$col_nb}, minmax(0,1fr))";

                $col_css_rules = [
                    '-ms-grid-columns:' . $ms_grid_columns,
                    'grid-template-columns:' . $grid_template_columns
                ];
                if ( $col_nb > 1 ) {
                    $col_css_rules[] = 'grid-column-gap:'.$col_gap;
                    $col_css_rules[] = 'grid-row-gap:'.$col_gap;
                }
                $rules[] = array(
                    'selector' => '.sek-post-grid-wrapper .sek-grid-layout.sek-all-col-'.$col_nb,
                    'css_rules' => implode(';', $col_css_rules),
                    'mq' =>null
                );
            }
            Nimble_Manager()->generic_post_grid_css_rules_written = true;
        }


        // MEDIA QUERIES
        $main_settings['columns'] = is_array($main_settings['columns']) ? $main_settings['columns'] : [];
        $cols_by_device = wp_parse_args(
            $main_settings['columns'],
            [ 'desktop' => '2', 'tablet' => '2', 'mobile' => '1' ]
        );
        if ( sek_is_pro() && array_key_exists('min_column_width', $main_settings ) ) {
            $min_column_width_by_device = wp_parse_args(
                $main_settings['min_column_width'],
                [ 'desktop' => '250', 'tablet' => '250', 'mobile' => '250' ]
            );
        }

        $col_css_rules = '';
        foreach ( $cols_by_device as $device => $col_nb ) {
            $col_nb = intval($col_nb);
            // First define the media queries using custom user breakpoints
            switch( $device ) {
                case 'desktop' :
                    $media_qu = "(min-width:{$tablet_breakpoint}px)";
                break;
                case 'tablet' :
                    if ( $mobile_breakpoint >= ( $tab_bp_val ) ) {
                        $media_qu = "(max-width:{$tab_bp_val}px)";
                    } else {
                        $media_qu = "(min-width:{$mob_bp_val}px) and (max-width:{$tab_bp_val}px)";
                    }
                break;
                case 'mobile' :
                    $media_qu = "(max-width:{$mob_bp_val}px)";
                break;
            }


            // Then define the selector + css rules by device
            // SELECTOR
            $selector = sprintf('[data-sek-id="%1$s"] .sek-post-grid-wrapper .sek-grid-layout.sek-%2$s-col-%3$s',
                $complete_modul_model['id'],
                $device,
                $col_nb
            );

            // CSS RULES
            //     .sek-grid-layout.sek-desktop-col-1 {
            //       -ms-grid-columns: minmax(0,1fr);
            //       grid-template-columns: repeat(1, minmax(0,1fr));
            //     }
            //     .sek-grid-layout.sek-desktop-col-2 {
            //       -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
            //       grid-template-columns: repeat(2, minmax(0,1fr));
            //       grid-column-gap: 20px;
            //       grid-row-gap: 20px;
            //     }
            // July 2021 : introduction of the auto-fill rule in pro
            if ( sek_is_pro() && array_key_exists('auto_fill', $main_settings) && sek_booleanize_checkbox_val($main_settings['auto_fill']) ) {
                $min_col_width = 250;
                if ( array_key_exists($device, $min_column_width_by_device ) ) {
                    $min_col_width = intval( $min_column_width_by_device[$device] );
                }
                $grid_template_columns = "repeat(auto-fill, minmax({$min_col_width}px,1fr));";
                // in this case, no need to add '-ms-grid-columns' rule
                $col_css_rules = [
                    'grid-template-columns:' . $grid_template_columns
                ];
            } else {
                $ms_grid_columns = [];
                // Up to 12 columns
                for ($i=1; $i <= $col_nb; $i++) {
                    if ( $i > 1 ) {
                        $col_gap = array_key_exists('col-'.$col_nb, $col_nb_gap_map ) ? $col_nb_gap_map['col-'.$col_nb] : '5px';
                        $ms_grid_columns[] = $col_gap;
                    }
                    $ms_grid_columns[] = 'minmax(0,1fr)';
                }

                $ms_grid_columns = implode(' ', $ms_grid_columns);

                $grid_template_columns = "repeat({$col_nb}, minmax(0,1fr))";
                $col_css_rules = [
                    '-ms-grid-columns:' . $ms_grid_columns,
                    'grid-template-columns:' . $grid_template_columns
                ];
            }

            if ( $col_nb > 1 ) {
                $col_gap = array_key_exists('col-'.$col_nb, $col_nb_gap_map ) ? $col_nb_gap_map['col-'.$col_nb] : '5px';
                $col_css_rules[] = 'grid-column-gap:'.$col_gap;
                $col_css_rules[] = 'grid-row-gap:'.$col_gap;
            }

            $col_css_rules_ready = [];
            if ( 'desktop' != $device ) {
                foreach ($col_css_rules as $col_rule) {
                    $col_css_rules_ready[] = $col_rule .= '';//!important';
                }
            } else {
                $col_css_rules_ready = $col_css_rules;
            }
            $col_css_rules_ready = implode(';', $col_css_rules_ready);

            $rules[] = array(
                'selector' => $selector,
                'css_rules' => $col_css_rules_ready,
                'mq' => $media_qu
            );
        }// end foreach
    }// END OF GRID LAYOUT RULES


    // LIST LAYOUT RULES
    if ( 'list' === $main_settings['layout'] ) {
        $css_rules = [
            '-ms-grid-columns: minmax(0,1fr)!important;',
            'grid-template-columns: minmax(0,1fr)!important;',//<= important because this property can be customized by users for desktop
            'grid-gap: 0;',
        ];
        // TABLET RULES
        if ( $mobile_breakpoint >= ( $tab_bp_val ) ) {
            $media_qu = "(max-width:{$tab_bp_val}px)";
        } else {
            $media_qu = "(min-width:{$mob_bp_val}px) and (max-width:{$tab_bp_val}px)";
        }
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-tablet-breakpoint .sek-list-layout article',
            'css_rules' => implode('', $css_rules),
            'mq' => $media_qu
        );
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-tablet-breakpoint .sek-list-layout article .sek-pg-thumbnail',
            'css_rules' => 'margin-bottom:10px;',
            'mq' => $media_qu
        );

        // MOBILE RULES
        $media_qu = "(max-width:{$mob_bp_val}px)";
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-mobile-breakpoint .sek-list-layout article',
            'css_rules' => implode('', $css_rules),
            'mq' => $media_qu
        );
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-mobile-breakpoint .sek-list-layout article .sek-pg-thumbnail',
            'css_rules' => 'margin-bottom:10px;',
            'mq' => $media_qu
        );
    }
    return $rules;
}
?>