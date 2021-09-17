<?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER GALLERY MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_gallery_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_gallery_module',
        'is_father' => true,
        'children' => array(
            'gallery_collec' => 'czr_gallery_collection_child',
            'gallery_opts' => 'czr_gallery_opts_child'
        ),
        'name' => __('Gallery', 'text_doma'),
        'starting_value' => array(
            'gallery_collec' => array(
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' )
            )
        ),
        'sanitize_callback' => '\Nimble\sanitize_cb__czr_gallery_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-gal-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "gallery_tmpl.php",
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
function sanitize_cb__czr_gallery_module( $value ) {
    if ( !is_array( $value ) )
        return $value;
    if ( !empty($value['gallery_collec']) && is_array( $value['gallery_collec'] ) ) {
        foreach( $value['gallery_collec'] as $key => $data ) {
            if ( array_key_exists( 'custom_caption', $data ) && is_string( $data['custom_caption'] ) ) {
                $value['gallery_collec'][$key]['custom_caption'] = sek_maybe_encode_richtext( $data['custom_caption'] );
            }
            // if ( array_key_exists( 'title_text', $data ) && is_string( $data['title_text'] ) ) {
            //     $value['gallery_collec'][$key]['title_text'] = sek_maybe_encode_richtext( $data['title_text'] );
            // }
        }
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_gallery_collection_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_gallery_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">toc</i> %1$s', __( 'Image collection', 'text_doma' ) ),
        // 'starting_value' => array(
        //     'custom_caption' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
        // ),
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
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'custom_caption' => array(
                    'input_type'         => 'text',
                    'title' => __('Image title displayed as tooltip and in the lightbox popup', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'notice_after' => __('When left empty, Nimble Builder will use by order of priority the image caption, description, and image title. Those properties can be edited for each image in the media library.')
                ),
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  GALLERY OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_gallery_opts_child() {
    $title_content_selector = array( '.sek-accord-item .sek-accord-title *' );
    $main_content_selector = array( '.sek-accord-item .sek-accord-content', '.sek-accord-item .sek-accord-content *' );
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sprintf( __( '%1$s + cool additional options', 'text-doma'),
            sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" style="text-decoration:underline">%2$s</a>',
                'https://nimblebuilder.com/gallery-examples/#masonry',
                __('masonry galleries', 'text-doma')
            )
        );
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_gallery_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Gallery options', 'text_doma' ) ),
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
                'img_size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('Select a size for this image among those generated by WordPress.', 'text_doma' ),
                    'html_after' => '<hr/>'
                ),
                'columns'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Number of columns', 'text_doma' ),
                    'default'     => array( 'desktop' => '3', 'tablet' => '2', 'mobile' => '1' ),
                    'min'         => 1,
                    'max'         => 24,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_stylesheet' => true, //<= some CSS rules are layout dependant
                    'html_after' => $pro_text
                ),//null,

                'custom-rows-columns' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define custom row and column dimensions', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    //'refresh_markup' => true,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                    'html_before' => '<hr/>'
                    //'notice_after' => __('When enabled and possible, Nimble will use the post thumbnail.', 'text_doma'),
                ),

                'column_width'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Custom column width', 'text_doma' ),
                    'min' => 0,
                    'max' => 1000,
                    'default'     => array( 'desktop' => '200px', 'tablet' => '150px', 'mobile' => '100px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'raw_height'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Custom raw height', 'text_doma' ),
                    'min' => 0,
                    'max' => 1000,
                    'default'     => array( 'desktop' => '200px', 'tablet' => '150px', 'mobile' => '100px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'column_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between columns', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '5px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr/>'
                ),//null,

                'row_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between rows', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '5px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Schedule an action on click or tap', 'text_doma'),
                    'default'     => 'img-lightbox',
                    'choices'     => array(
                        'no-link' => __('No click action', 'text_doma' ),
                        'img-lightbox' =>__('Lightbox : enlarge the image, and dim out the rest of the content', 'text_doma' ),
                        'img-file' => __('Link to image file', 'text_doma' ),
                        'img-page' =>__('Link to image page', 'text_doma' )
                    ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'notice_after' => __('Note that some click actions are disabled during customization.', 'text_doma' ),
                    'html_before' => '<hr/>'
                ),

                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_gallery_module', '\Nimble\sek_add_css_rules_for_czr_gallery_module', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_gallery_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $main_settings = $value['gallery_opts'];

    //sek_error_log('sek_get_default_module_model() ?', sek_get_default_module_model( 'czr_gallery_module') );

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

    // BASE CSS RULES
    // .sek-gal-items.sek-all-col-1 {
    //   -ms-grid-columns: minmax(0,1fr);
    //   grid-template-columns: repeat(1, minmax(0,1fr));
    // }
    // .sek-gal-items.sek-all-col-2 {
    //   -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
    //   grid-template-columns: repeat(2, minmax(0,1fr));
    //   grid-column-gap: 20px;
    //   grid-row-gap: 20px;
    // }
    $col_nb_gap_map = [
        'col-1' => null,
        'col-2' => '10px',
        'col-3' => '10px',
        'col-4' => '10px',
        'col-5' => '10px',
        'col-6' => '10px',
        'col-7' => '10px',
        'col-8' => '10px',
        'col-9' => '10px',
        'col-10' => '5px',
        'col-11' => '5px',
        'col-12' => '5px',
        'col-13' => '5px',
        'col-14' => '5px',
        'col-15' => '5px',
        'col-16' => '5px',
        'col-17' => '5px',
        'col-18' => '5px',
        'col-19' => '5px',
        'col-20' => '5px',
        'col-21' => '5px',
        'col-22' => '5px',
        'col-23' => '5px',
        'col-24' => '5px'
    ];

    if ( !isset(Nimble_Manager()->generic_gallery_grid_css_rules_written) ) {
        foreach ($col_nb_gap_map as $col_nb_index => $col_gap) {
            $col_nb = intval( str_replace('col-', '', $col_nb_index ) );
            $ms_grid_columns = [];
            // Up to 24 columns
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
                'selector' => '.sek-gal-wrapper .sek-gal-items.sek-all-col-'.$col_nb,
                'css_rules' => implode(';', $col_css_rules),
                'mq' =>null
            );
        }
        Nimble_Manager()->generic_gallery_grid_css_rules_written = true;
    }

    // MEDIA QUERIES
    $main_settings['columns'] = is_array($main_settings['columns']) ? $main_settings['columns'] : [];
    $cols_by_device = wp_parse_args(
        $main_settings['columns'],
        [ 'desktop' => '3', 'tablet' => '2', 'mobile' => '1' ]// as per registration params
    );
    if ( sek_is_pro() && array_key_exists('min_column_width', $main_settings ) ) {
        $min_column_width_by_device = wp_parse_args(
            $main_settings['min_column_width'],
            [ 'desktop' => '250', 'tablet' => '250', 'mobile' => '250' ]
        );
    }

    // Normalize column and row dimensions
    // will be used later on
    $col_width_by_device = $main_settings['column_width'];
    $col_width_by_device = is_array( $col_width_by_device ) ? $col_width_by_device : [];
    $col_width_by_device = wp_parse_args(
        $main_settings['column_width'],
        [ 'desktop' => '200px', 'tablet' => '200px', 'mobile' => '200px' ]
    );

    // replace % by vh when needed
    $col_width_by_device_with_unit = $col_width_by_device;
    foreach ($col_width_by_device as $device => $num_unit ) {
        $numeric = sek_extract_numeric_value( $num_unit );
        $numeric = $numeric < 0 ? '0' : $numeric;
        $col_width_by_device_with_unit[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
        if ( !empty( $num_unit ) ) {
            $unit = sek_extract_unit( $num_unit );
            $col_width_by_device_with_unit[$device] = $numeric . $unit;
        }
    }


    $raw_height_by_device = wp_parse_args(
        $main_settings['raw_height'],
        [ 'desktop' => '200px', 'tablet' => '200px', 'mobile' => '200px' ]
    );
     // replace % by vh when needed
    $raw_height_by_device_with_unit = $raw_height_by_device;
    foreach ($raw_height_by_device as $device => $num_unit ) {
         $numeric = sek_extract_numeric_value( $num_unit );
         $numeric = $numeric < 0 ? '0' : $numeric;
         $raw_height_by_device_with_unit[$device] = '';
         // Leave the device value empty if === to default
         // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
         // fixes https://github.com/presscustomizr/nimble-builder/issues/419
         //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
         if ( !empty( $num_unit ) ) {
             $unit = sek_extract_unit( $num_unit );
             $raw_height_by_device_with_unit[$device] = $numeric . $unit;
         }
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
        $selector = sprintf('[data-sek-id="%1$s"] .sek-gal-wrapper .sek-gal-items.sek-%2$s-col-%3$s',
            $complete_modul_model['id'],
            $device,
            $col_nb
        );

        $has_custom_row_and_column_dimensions = sek_booleanize_checkbox_val( $main_settings['custom-rows-columns'] );

        // Custom row and column dimension is not compatible with masonry
        if ( array_key_exists('masonry_on', $main_settings ) ) {
            $has_custom_row_and_column_dimensions = $has_custom_row_and_column_dimensions && !sek_booleanize_checkbox_val( $main_settings['masonry_on'] );
        }
        $has_auto_fill_column = array_key_exists('auto_fill', $main_settings ) && sek_booleanize_checkbox_val( $main_settings['auto_fill'] );//<= pro
        // CSS RULES
        //     .sek-gal-items.sek-desktop-col-1 {
        //       -ms-grid-columns: minmax(0,1fr);
        //       grid-template-columns: repeat(1, minmax(0,1fr));
        //     }
        //     .sek-gal-items.sek-desktop-col-2 {
        //       -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
        //       grid-template-columns: repeat(2, minmax(0,1fr));
        //       grid-column-gap: 20px;
        //       grid-row-gap: 20px;
        //     }
        // July 2021 : introduction of the auto-fill rule in pro
        if ( sek_is_pro() && $has_auto_fill_column ) {
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
            // Up to 24 columns
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



        // Column width and row height
        if ( $has_custom_row_and_column_dimensions ) {
            if ( !$has_auto_fill_column ) {
                $norm_col_nb = $col_nb > 0 ? $col_nb : 1;

                if ( array_key_exists($device, $col_width_by_device_with_unit ) ) {
                    $col_width = $col_width_by_device_with_unit[$device];
                }
                if ( !empty($col_width) ) {
                    $grid_template_columns = "repeat({$norm_col_nb}, {$col_width});";
                    $col_css_rules[] = 'grid-template-columns:' . $grid_template_columns;
                }
            }

            if ( array_key_exists($device, $raw_height_by_device_with_unit ) ) {
                $raw_height = $raw_height_by_device_with_unit[$device];
            }
            if ( !empty($raw_height) ) {
                $col_css_rules[] = 'grid-auto-rows:' . $raw_height;
                $col_css_rules[] = '-ms-grid-rows:' . $raw_height;
                $col_css_rules[] = 'grid-template-rows:' . $raw_height;
            }
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
    // END OF GRID LAYOUT




    // COLUMN AND ROW GAP
    // Horizontal Gap
    $gap = $main_settings['column_gap'];
    $gap = is_array( $gap ) ? $gap : array();
    $defaults = array(
        'desktop' => '10px',// <= this value matches the static CSS rule and the input default for the module
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
            '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-gal-wrapper .sek-gal-items'
        ] ),
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );

    // Vertical Gap => common to list and grid layout
    $v_gap = $main_settings['row_gap'];
    $v_gap = is_array( $v_gap ) ? $v_gap : array();
    $defaults = array(
        'desktop' => '10px',// <= this value matches the static CSS rule and the input default for the module
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
        'selector' => '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-gal-wrapper .sek-gal-items',
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );
    // END OF COLUMN AND ROW GAP



    return $rules;
}












// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
//add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_gallery_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_gallery_collection_child', 10, 2 );

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
//     [module_type] => czr_gallery_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_gallery_collection_child( $rules, $params ) {
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


?>