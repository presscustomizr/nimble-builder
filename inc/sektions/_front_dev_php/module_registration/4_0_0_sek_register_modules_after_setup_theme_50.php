<?php
// The base fmk is loaded @after_setup_theme:10
add_action( 'after_setup_theme', '\Nimble\sek_schedule_module_registration', 50 );

// When not customizing, we don't need to register all modules
function sek_schedule_module_registration() {
    if ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
        sek_register_modules();
    } else {
        add_action( 'wp', '\Nimble\sek_register_modules', PHP_INT_MAX );
    }
}



function sek_register_modules() {
    $modules = apply_filters( 'nimble_pre_module_registration', array(), current_filter() );
    $module_candidates = array();

    // flatten the array
    // because can be formed this way after filter when including child
    // [0] => Array
    //     (
    //         [0] => czr_post_grid_module
    //         [1] => czr_post_grid_main_child
    //         [2] => czr_post_grid_thumb_child
    //         [3] => czr_post_grid_metas_child
    //         [4] => czr_post_grid_fonts_child
    //     )

    // [1] => sek_level_bg_module
    // [2] => sek_level_border_module
    foreach ($modules as $key => $value) {
      if ( is_array( $value ) ) {
          $module_candidates = array_merge( $module_candidates, $value );
      } else {
          $module_candidates[] = $value;
      }
    }

    // remove duplicated modules, typically 'czr_font_child'
    $module_candidates = array_unique( $module_candidates );

    foreach ( $module_candidates as $module_name ) {
        // Was previously written "\Nimble\sek_get_module_params_for_{$module_name}";
        // But this syntax can lead to function_exists() return false even if the function exists
        // Probably due to a php version issue. Bug detected with php version 5.6.38
        // bug report detailed here https://github.com/presscustomizr/nimble-builder/issues/234
        $fn = "Nimble\sek_get_module_params_for_{$module_name}";
        if ( function_exists( $fn ) ) {
            $params = $fn();
            if ( is_array( $params ) ) {
                CZR_Fmk_Base()->czr_pre_register_dynamic_module( $params );
            } else {
                error_log( __FUNCTION__ . ' Module registration params should be an array');
            }
        } else {
            error_log( __FUNCTION__ . ' missing params callback fn for module ' . $module_name );
        }
    }
}//sek_register_modules()


add_filter( 'nimble_pre_module_registration', '\Nimble\sek_filter_modules_to_register', 10, 2 );
function sek_filter_modules_to_register( $modules, $hook ) {
    if ( 'wp' !== $hook ) {
        $modules = array_merge(
            $modules,
            SEK_Front_Construct::$ui_picker_modules,
            SEK_Front_Construct::$ui_level_modules,
            SEK_Front_Construct::$ui_local_global_options_modules,
            SEK_Front_Construct::$ui_front_modules
        );

        // Header and footer have been introduced in v1.4.0 but not enabled by default
        // The module menu and the widget area module are on hold until "header and footer" feature is released.
        if ( sek_is_header_footer_enabled() ) {
            $modules = array_merge( $modules, SEK_Front_Construct::$ui_front_beta_modules );
        }
        // if ( sek_is_pro() ) {
        //     $modules = array_merge( $modules, [
        //         'czr_special_img_module',
        //         'czr_special_img_main_settings_child'
        //     ]);
        // }
    } else {
        //sniff the list of active modules in local and global location
        sek_populate_contextually_active_module_list();

        $contextually_actives = array();
        $front_modules = SEK_Front_Construct::$ui_front_modules;
        // we need to get all children when the module is a father.
        // This will be flatenized afterwards
        foreach ( Nimble_Manager()->contextually_active_modules as $module_name ) {
            if ( array_key_exists( $module_name, $front_modules ) ) {
                $contextually_actives[] = $front_modules[ $module_name ];
            }
        }

        $modules = array_merge(
            $modules,
            $contextually_actives,
            SEK_Front_Construct::$ui_level_modules,
            SEK_Front_Construct::$ui_local_global_options_modules
        );
    }
    return $modules;
}

// @return void()
// recursive helper => populates Nimble_Manager()->contextually_active_modules
function sek_populate_contextually_active_module_list( $recursive_data = null ) {
    if ( is_null( $recursive_data ) ) {
        $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
        $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
        $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
        $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();
        $recursive_data = array_merge( $local_collection, $global_collection );
    }
    foreach ( $recursive_data as $key => $value ) {
        if ( is_array( $value ) ) {
            sek_populate_contextually_active_module_list( $value );
        }
        // @see sek_get_module_params_for_czr_image_main_settings_child
        if ( is_string( $key ) && 'module_type' === $key ) {
            $module_collection = Nimble_Manager()->contextually_active_modules;
            $module_collection[] = $value;
            Nimble_Manager()->contextually_active_modules = array_unique( $module_collection );
        }
    }
}


// HELPERS
// Used when registering a select input in a module
// @return an array of options that will be used to populate the select input in js
function sek_get_select_options_for_input_id( $input_id ) {
    $options = array();
    switch( $input_id ) {
        case 'img_hover_effect' :
            $options = array(
                'none' => __('No effect', 'text_doma' ),
                'opacity' => __('Opacity', 'text_doma' ),
                'zoom-out' => __('Zoom out', 'text_doma' ),
                'zoom-in' => __('Zoom in', 'text_doma' ),
                'move-up' =>__('Move up', 'text_doma' ),
                'move-down' =>__('Move down', 'text_doma' ),
                'blur' =>__('Blur', 'text_doma' ),
                'grayscale' =>__('Grayscale', 'text_doma' ),
                'reverse-grayscale' =>__('Reverse grayscale', 'text_doma' )
            );
        break;
        case 'img-size' :
            $options = sek_get_img_sizes();
        break;

        // ALL MODULES
        case 'link-to' :
            $options = array(
                'no-link' => __('No link', 'text_doma' ),
                'url' => __('Site content or custom url', 'text_doma' ),
            );
        break;

        // FEATURED PAGE MODULE
        case 'img-type' :
            $options = array(
                'none' => __( 'No image', 'text_doma' ),
                'featured' => __( 'Use the page featured image', 'text_doma' ),
                'custom' => __( 'Use a custom image', 'text_doma' ),
            );
        break;
        case 'content-type' :
            $options = array(
                'none' => __( 'No text', 'text_doma' ),
                'page-excerpt' => __( 'Use the page excerpt', 'text_doma' ),
                'custom' => __( 'Use a custom text', 'text_doma' ),
            );
        break;

        // HEADING MODULE
        case 'heading_tag':
            $options = array(
                /* Not totally sure these should be localized as they strictly refer to html tags */
                'h1' => __('H1', 'text_doma' ),
                'h2' => __('H2', 'text_doma' ),
                'h3' => __('H3', 'text_doma' ),
                'h4' => __('H4', 'text_doma' ),
                'h5' => __('H5', 'text_doma' ),
                'h6' => __('H6', 'text_doma' ),
            );
        break;

        // CSS MODIFIERS INPUT ID
        case 'font_weight_css' :
            $options = array(
                'normal'  => __( 'normal', 'text_doma' ),
                'bold'    => __( 'bold', 'text_doma' ),
                'bolder'  => __( 'bolder', 'text_doma' ),
                'lighter'   => __( 'lighter', 'text_doma' ),
                100     => 100,
                200     => 200,
                300     => 300,
                400     => 400,
                500     => 500,
                600     => 600,
                700     => 700,
                800     => 800,
                900     => 900
            );
        break;
        case 'font_style_css' :
            $options = array(
                'inherit'   => __( 'inherit', 'text_doma' ),
                'italic'  => __( 'italic', 'text_doma' ),
                'normal'  => __( 'normal', 'text_doma' ),
                'oblique' => __( 'oblique', 'text_doma' )
            );
        break;
        case 'text_decoration_css'  :
            $options = array(
                'none'      => __( 'none', 'text_doma' ),
                'inherit'   => __( 'inherit', 'text_doma' ),
                'line-through' => __( 'line-through', 'text_doma' ),
                'overline'    => __( 'overline', 'text_doma' ),
                'underline'   => __( 'underline', 'text_doma' )
            );
        break;
        case 'text_transform_css' :
            $options = array(
                'none'      => __( 'none', 'text_doma' ),
                'inherit'   => __( 'inherit', 'text_doma' ),
                'capitalize'  => __( 'capitalize', 'text_doma' ),
                'uppercase'   => __( 'uppercase', 'text_doma' ),
                'lowercase'   => __( 'lowercase', 'text_doma' )
            );
        break;

        // SPACING MODULE
        case 'css_unit' :
            $options = array(
                'px' => __('Pixels', 'text_doma' ),
                'em' => __('Em', 'text_doma'),
                'percent' => __('Percents', 'text_doma' )
            );
        break;

        //QUOTE MODULE
        case 'quote_design' :
            $options = array(
                'none' => __( 'Text only', 'text_doma' ),
                'border-before' => __( 'Side Border', 'text_doma' ),
                'quote-icon-before' => __( 'Quote Icon', 'text_doma' ),
            );
        break;

        // LEVELS UI : LAYOUT BACKGROUND BORDER HEIGHT WIDTH
        case 'boxed-wide' :
            $options = array(
                'boxed' => __('Boxed', 'text_doma'),
                'fullwidth' => __('Full Width', 'text_doma')
            );
        break;
        case 'height-type' :
            $options = array(
                'auto' => __('Adapt to content', 'text_doma'),
                'custom' => __('Custom', 'text_doma' )
            );
        break;
        case 'width-type' :
            $options = array(
                'default' => __('Default', 'text_doma'),
                'custom' => __('Custom', 'text_doma' )
            );
        break;
        case 'bg-scale' :
            $options = array(
                'default' => __('Default', 'text_doma'),
                'auto' => __('Automatic', 'text_doma'),
                'cover' => __('Scale to fill', 'text_doma'),
                'contain' => __('Fit', 'text_doma'),
            );
        break;
        case 'bg-position' :
            $options = array(
                'default' => __('default', 'text_doma'),
            );
        break;
        case 'border-type' :
            $options = array(
                'none' => __('none', 'text_doma'),
                'solid' => __('solid', 'text_doma'),
                'double' => __('double', 'text_doma'),
                'dotted' => __('dotted', 'text_doma'),
                'dashed' => __('dashed', 'text_doma')
            );
        break;

        default :
            sek_error_log( __FUNCTION__ . ' => no case set for input id : '. $input_id );
        break;
    }
    return $options;
}

?>