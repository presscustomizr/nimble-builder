<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function sek_is_debug_mode() {
  return isset( $_GET['nimble_debug'] );
}
// @return array
function sek_is_dev_mode() {
  return ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || sek_is_debug_mode();
}

if ( !defined( 'NIMBLE_CPT' ) ) { define( 'NIMBLE_CPT' , 'nimble_post_type' ); }
if ( !defined( 'NIMBLE_TEMPLATE_CPT' ) ) { define( 'NIMBLE_TEMPLATE_CPT' , 'nimble_template' ); }
if ( !defined( 'NIMBLE_CSS_FOLDER_NAME' ) ) { define( 'NIMBLE_CSS_FOLDER_NAME' , 'sek_css' ); }
if ( !defined( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'nimble___' ); }
if ( !defined( 'NIMBLE_GLOBAL_SKOPE_ID' ) ) { define( 'NIMBLE_GLOBAL_SKOPE_ID' , 'skp__global' ); }

if ( !defined( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' , '__nimble_options__' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' , 'nimble_saved_sektions' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS' , 'nimble_most_used_fonts' ); }

if ( !defined( 'NIMBLE_PREFIX_FOR_SAVED_TMPL' ) ) { define( 'NIMBLE_PREFIX_FOR_SAVED_TMPL' , 'nimble_tmpl_' ); }

if ( !defined( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' , '__nimble__' ); }
if ( !defined( 'NIMBLE_WIDGET_PREFIX' ) ) { define( 'NIMBLE_WIDGET_PREFIX' , 'nimble-widget-area-' ); }
if ( !defined( 'NIMBLE_ASSETS_VERSION' ) ) { define( 'NIMBLE_ASSETS_VERSION', sek_is_dev_mode() ? time() : NIMBLE_VERSION ); }
if ( !defined( 'NIMBLE_MODULE_ICON_PATH' ) ) { define( 'NIMBLE_MODULE_ICON_PATH' , NIMBLE_BASE_URL . '/assets/czr/sek/icons/modules/' ); }
if ( !defined( 'NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID') ) { define( 'NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID' , 'czr-customize-content_editor' ); }

if ( !defined( 'NIMBLE_WELCOME_NOTICE_ID' ) ) { define ( 'NIMBLE_WELCOME_NOTICE_ID', 'nimble-welcome-notice-12-2018' ); }
//mt_rand(0, 65535) . 'test-nimble-feedback-notice-04-2019'
if ( !defined( 'NIMBLE_FEEDBACK_NOTICE_ID' ) ) { define ( 'NIMBLE_FEEDBACK_NOTICE_ID', 'nimble-feedback-notice-04-2019' ); }

if ( !defined( 'NIMBLE_JQUERY_ID' ) ) { define ( 'NIMBLE_JQUERY_ID', 'nb-jquery' ); }
if ( !defined( 'NIMBLE_JQUERY_LATEST_CDN_URL' ) ) { define ( 'NIMBLE_JQUERY_LATEST_CDN_URL', 'https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js' ); }
if ( !defined( 'NIMBLE_JQUERY_MIGRATE_URL' ) ) { define ( 'NIMBLE_JQUERY_MIGRATE_URL', site_url() . '/wp-includes/js/jquery/jquery-migrate.min.js' ); }

?><?php
/* ------------------------------------------------------------------------- *
 *  LOCATIONS UTILITIES
/* ------------------------------------------------------------------------- */
// @return array
function sek_get_locations() {
    if ( ! is_array( Nimble_Manager()->registered_locations ) ) {
        sek_error_log( __FUNCTION__ . ' error => the registered locations must be an array');
        return Nimble_Manager()->default_locations;
    }
    //sek_error_log( __FUNCTION__ .' => locations ?',  array_merge( Nimble_Manager()->default_locations, Nimble_Manager()->registered_locations ) );
    return apply_filters( 'sek_get_locations', Nimble_Manager()->registered_locations );
}

// @return array of "local" content locations => locations with the following characterictics :
// - sections in this location are specific to a given skope id
// - header and footer locations are excluded
function sek_get_local_content_locations() {
    $locations = array();
    $all_locations = sek_get_locations();
    if ( is_array( $all_locations ) ) {
        foreach ( $all_locations as $loc_id => $loc_data) {
            // Normalizes with the default model used to register a location
            // public $default_registered_location_model = [
            //   'priority' => 10,
            //   'is_global_location' => false,
            //   'is_header_location' => false,
            //   'is_footer_location' => false
            // ];
            $loc_data = wp_parse_args( $loc_data, Nimble_Manager()->default_registered_location_model );
            if ( true === $loc_data['is_header_location'] || true === $loc_data['is_footer_location'] )
              continue;

            if ( ! sek_is_global_location( $loc_id ) ) {
                $locations[$loc_id] = $loc_data;
            }
        }
    }
    return $locations;
}

// DEPRECATED IN V1.4.0.
// Kept for retro compatibility
function sek_get_local_locations() {
    return sek_get_local_content_locations();
}

// @return an array of "global" locations => in which the sections are displayed site wide
function sek_get_global_locations() {
    $locations = array();
    $all_locations = sek_get_locations();
    if ( is_array( $all_locations ) ) {
        foreach ( $all_locations as $loc_id => $loc_data) {
            if ( sek_is_global_location( $loc_id ) ) {
                $locations[$loc_id] = $loc_data;
            }
        }
    }
    return $locations;
}


// @param location_id (string)
function sek_get_registered_location_property( $location_id, $property_name = '' ) {
    $all_locations = sek_get_locations();
    $default_property_val = 'not_set';
    //sek_error_log( __FUNCTION__ .' => locations ?',  $all_locations );
    if ( ! isset( $all_locations[$location_id] ) || ! is_array( $all_locations[$location_id] ) ) {
        sek_error_log( __FUNCTION__ . ' error => the location ' . $location_id . ' is invalid or not registered.');
        return $default_property_val;
    }

    if ( empty( $property_name ) || ! is_string( $property_name ) ) {
        sek_error_log( __FUNCTION__ . ' error => the requested property for location ' . $location_id . ' is invalid');
        return $default_property_val;
    }

    $location_params = wp_parse_args( $all_locations[$location_id], Nimble_Manager()->default_registered_location_model );
    return ! empty( $location_params[$property_name] ) ? $location_params[$property_name] : $default_property_val;
}

// @return bool
function sek_is_global_location( $location_id ) {
    if ( ! is_string( $location_id ) || empty( $location_id ) ) {
        sek_error_log( __FUNCTION__ . ' error => missing or invalid location_id param' );
        return false;
    }
    $is_global_location = sek_get_registered_location_property( $location_id, 'is_global_location' );
    return 'not_set' === $is_global_location ? false : true === $is_global_location;
}

// @param $location_id ( string ). Example '__after_header'
function register_location( $location_id, $params = array() ) {
    $params = is_array( $params ) ? $params : array();
    $params = wp_parse_args( $params, Nimble_Manager()->default_registered_location_model );
    $registered_locations = Nimble_Manager()->registered_locations;
    if ( is_array( $registered_locations ) ) {
        $registered_locations[$location_id] = $params;
    }
    Nimble_Manager()->registered_locations = $registered_locations;
    //sek_error_log( __FUNCTION__ .' => Nimble_Manager()->registered_locations', Nimble_Manager()->registered_locations );
}


// @return array
// @used when populating the customizer localized params
// @param $skope_id optional. Specified when we need to differentiate the local and global locations
function sek_get_default_location_model( $skope_id = null ) {
    $is_global_skope = NIMBLE_GLOBAL_SKOPE_ID === $skope_id;
    if ( $is_global_skope ) {
        $defaut_sektions_value = [ 'collection' => [], 'fonts' => [] ];//global_options are saved in a specific option => NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS
    } else {
        $defaut_sektions_value = [ 'collection' => [], 'local_options' => [], 'fonts' => [] ];
    }
    foreach( sek_get_locations() as $location_id => $params ) {
        $is_global_location = sek_is_global_location( $location_id );
        if ( $is_global_skope && ! $is_global_location )
          continue;
        if ( ! $is_global_skope && $is_global_location )
          continue;

        $location_model = wp_parse_args( [ 'id' => $location_id ], Nimble_Manager()->default_location_model );
        if ( $is_global_location ) {
            $location_model[ 'is_global_location' ] = true;
        }

        $defaut_sektions_value['collection'][] = $location_model;
    }
    return $defaut_sektions_value;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  FRONT ASSET SNIFFERS
/* ------------------------------------------------------------------------- */

// @return bool
// some modules uses font awesome :
// Fired in 'wp_enqueue_scripts' to check if font awesome is needed
function sek_front_needs_font_awesome( $bool = false, $recursive_data = null ) {
    $contextually_active_modules = sek_get_collection_of_contextually_active_modules();
    $font_awesome_dependant_modules = Nimble_Manager()->modules_dependant_of_font_awesome;//'czr_button_module', 'czr_icon_module', 'czr_social_icons_module'
    foreach ( $font_awesome_dependant_modules as $module_type ) {
      if ( array_key_exists($module_type , $contextually_active_modules) )
        $bool = true;
    }
    return $bool;
}

// @return bool
// Fired in 'wp_enqueue_scripts'
// Recursively sniff the local and global sections to find a 'img-lightbox' string
// @see sek_get_module_params_for_czr_image_main_settings_child
function sek_front_needs_magnific_popup( $bool = false, $recursive_data = null ) {
    if ( !$bool ) {
        if ( is_null( $recursive_data ) ) {
            $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
            $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

            $recursive_data = array_merge( $local_collection, $global_collection );
        }

        foreach ($recursive_data as $key => $value) {
            // @see sek_get_module_params_for_czr_image_main_settings_child
            if ( is_string( $value ) && 'img-lightbox' === $value ) {
                $bool = true;
                break;
            }
            if ( is_array( $value ) ) {
                $bool = sek_front_needs_magnific_popup( $bool, $value );
            }
        }
    }
    return true === $bool;
}

// @return bool
// Fired in 'wp_enqueue_scripts'
function sek_front_needs_parallax_bg( $bool = false, $recursive_data = null ) {
    if ( !$bool ) {
        if ( is_null( $recursive_data ) ) {
            $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
            $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

            $recursive_data = array_merge( $local_collection, $global_collection );
        }

        foreach ($recursive_data as $key => $value) {
            // @see sek_get_module_params_for_czr_image_main_settings_child
            if ( 'bg-parallax' === $key && sek_booleanize_checkbox_val($value) ) {
                $bool = true;
                break;
            }
            if ( is_array( $value ) ) {
                $bool = sek_front_needs_parallax_bg( $bool, $value );
            }
        }
    }
    return true === $bool;
}

// @return bool
// Fired in 'wp_enqueue_scripts'
function sek_front_needs_video_bg( $bool = false, $recursive_data = null ) {
    if ( !$bool ) {
        if ( is_null( $recursive_data ) ) {
            $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
            $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

            $recursive_data = array_merge( $local_collection, $global_collection );
        }

        foreach ($recursive_data as $key => $value) {
            // @see sek_get_module_params_for_czr_image_main_settings_child
            if ( 'bg-video' === $key && !empty($value) ) {
                $bool = true;
                break;
            }
            if ( is_array( $value ) ) {
                $bool = sek_front_needs_video_bg( $bool, $value );
            }
        }
    }
    return true === $bool;
}


// @return bool
// march 2020 introduced https://github.com/presscustomizr/nimble-builder/issues/632
function sek_is_jquery_replaced() {
    if ( skp_is_customizing() )
      return;
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['use_latest_version_jquery'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['use_latest_version_jquery'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_load_jquery_async() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['load_jquery_async'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['load_jquery_async'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/626
function sek_load_front_assets_on_scroll() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['load_front_assets_in_ajax'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['load_front_assets_in_ajax'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/626
function sek_preload_font_awesome() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['preload_font_awesome'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['preload_font_awesome'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/612
// function sek_inline_module_stylesheets_on_front() {
//     $glob_perf = sek_get_global_option_value( 'performances' );
//     if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['print_partial_module_stylesheets_inline'] ) ) {
//         return sek_booleanize_checkbox_val( $glob_perf['print_partial_module_stylesheets_inline'] );
//     }
//     return false;
// }

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_inline_dynamic_stylesheets_on_front() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['print_dyn_stylesheets_inline'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['print_dyn_stylesheets_inline'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_use_split_stylesheets_on_front() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['use_partial_module_stylesheets'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['use_partial_module_stylesheets'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/629
// Firefox doesn not support preload
// IE is supposed to support it, but tests show that google fonts may not be loaded on each page refresh
function sek_preload_google_fonts_on_front() {
    // When preload is active, browser support is checked with javascript
    // with a fallback on regular style fetching
    // if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) // 'Internet explorer'
    //   return;
    // elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== FALSE) // 'Mozilla Firefox'
    //   return;
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['preload_google_fonts'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['preload_google_fonts'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/635
function sek_load_front_assets_in_ajax() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['load_assets_in_ajax'] ) ) {
        return !skp_is_customizing() && sek_booleanize_checkbox_val( $glob_perf['load_assets_in_ajax'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/635
function sek_preload_some_scripts_and_styles() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['preload_front_scripts'] ) ) {
        return !skp_is_customizing() && sek_booleanize_checkbox_val( $glob_perf['preload_front_scripts'] );
    }
    return false;
}

// Adds defer attribute to enqueued / registered scripts.
// fired @wp_enqueue_scripts
function sek_defer_script($handle) {
    // Adds defer attribute to enqueued / registered scripts.
    wp_script_add_data( $handle, 'defer', true );
}
?><?php

/* ------------------------------------------------------------------------- *
 *  IMAGE HELPER
/* ------------------------------------------------------------------------- */
// @see https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
// used in sek_get_select_options_for_input_id()
function sek_get_img_sizes() {
    global $_wp_additional_image_sizes;

    $sizes = array();
    $to_return = array(
        'original' => __('Original image dimensions', 'text_doma')
    );

    foreach ( get_intermediate_image_sizes() as $_size ) {

        $first_to_upper_size = ucfirst(strtolower($_size));
        $first_to_upper_size = preg_replace_callback( '/[.!?].*?\w/', '\Nimble\sek_img_sizes_preg_replace_callback', $first_to_upper_size );

        if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
            $sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
            $sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
            $sizes[ $_size ]['title'] =  $first_to_upper_size;
            //$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
        } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
            $sizes[ $_size ] = array(
                'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                'title' =>  $first_to_upper_size
                //'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
            );
        }
    }
    foreach ( $sizes as $_size => $data ) {
        $to_return[ $_size ] = $data['title'] . ' - ' . $data['width'] . ' x ' . $data['height'];
    }

    return $to_return;
}

function sek_img_sizes_preg_replace_callback( $matches ) {
    return strtoupper( $matches[0] );
}





/* ------------------------------------------------------------------------- *
 *  SMART LOAD HELPER FOR IMAGES AND VIDEOS
/* ------------------------------------------------------------------------- */
/**
* callback of preg_replace_callback in SEK_Front_Render::sek_maybe_process_img_for_js_smart_load
* @return string
*/
function nimble_regex_callback( $matches ) {
    // bail if the img has already been parsed for swiper slider lazyloading ( https://github.com/presscustomizr/nimble-builder/issues/596 )
    if ( false !== strpos( $matches[0], 'data-srcset' ) || false !== strpos( $matches[0], 'data-src' ) ) {
      return $matches[0];
    // bail if already parsed by this regex or if smartload is disabled
    } else if ( false !== strpos( $matches[0], 'data-sek-src' ) || preg_match('/ data-sek-smartload *= *"false" */', $matches[0]) ) {
      return $matches[0];
    // otherwise go ahead and parse
    } else {
      return apply_filters( 'nimble_img_smartloaded',
        str_replace( array('srcset=', 'sizes='), array('data-sek-srcset=', 'data-sek-sizes='),
            sprintf('<img %1$s src="%2$s" data-sek-src="%3$s" %4$s>',
                $matches[1],
                'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
                $matches[2],
                $matches[3]
            )
        )
      );
    }
}


// @return boolean
// img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
// the local option wins
// if local is set to inherit, return the global option
// This option is cached
// deactivated when customizing
function sek_is_img_smartload_enabled() {
    if ( 'not_cached' !== Nimble_Manager()->img_smartload_enabled ) {
        return Nimble_Manager()->img_smartload_enabled;
    }
    $is_img_smartload_enabled = false;
    // LOCAL OPTION
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $local_performances_data = sek_get_local_option_value( 'local_performances' );
    $local_smartload = 'inherit';
    if ( !is_null( $local_performances_data ) && is_array( $local_performances_data ) ) {
        if ( ! empty( $local_performances_data['local-img-smart-load'] ) && 'inherit' !== $local_performances_data['local-img-smart-load'] ) {
              $local_smartload = 'yes' === $local_performances_data['local-img-smart-load'];
        }
    }

    if ( 'inherit' !== $local_smartload ) {
        $is_img_smartload_enabled = $local_smartload;
    } else {
        // GLOBAL OPTION
        $glob_performances_data = sek_get_global_option_value( 'performances' );
        if ( !is_null( $glob_performances_data ) && is_array( $glob_performances_data ) && !empty( $glob_performances_data['global-img-smart-load'] ) ) {
            $is_img_smartload_enabled = sek_booleanize_checkbox_val( $glob_performances_data['global-img-smart-load'] );
        }
    }

    // CACHE THE OPTION
    Nimble_Manager()->img_smartload_enabled = $is_img_smartload_enabled;

    return Nimble_Manager()->img_smartload_enabled;
}


// @return boolean
// video background lazy load can be set globally with 'global-bg-video-lazy-load'
// implemented in nov 2019 for https://github.com/presscustomizr/nimble-builder/issues/287
// This option is cached
function sek_is_video_bg_lazyload_enabled() {
    // if ( skp_is_customizing() )
    //   return false;
    if ( 'not_cached' !== Nimble_Manager()->video_bg_lazyload_enabled ) {
        return Nimble_Manager()->video_bg_lazyload_enabled;
    }
    $is_video_bg_lazyload_enabled = false;
    $glob_performances_data = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_performances_data ) && is_array( $glob_performances_data ) && !empty( $glob_performances_data['global-bg-video-lazy-load'] ) ) {
        $is_video_bg_lazyload_enabled = sek_booleanize_checkbox_val( $glob_performances_data['global-bg-video-lazy-load'] );
    }

    // CACHE THE OPTION
    Nimble_Manager()->video_bg_lazyload_enabled = $is_video_bg_lazyload_enabled;

    return Nimble_Manager()->video_bg_lazyload_enabled;
}



// /* ------------------------------------------------------------------------- *
// *  Adaptation of wp_get_attachment_image() for preprocessing lazy loading carousel images
//  added in dec 2019 for https://github.com/presscustomizr/nimble-builder/issues/570
//  used in tmpl/modules/img_slider_tmpl.php
// /* ------------------------------------------------------------------------- */
function sek_get_attachment_image_for_lazyloading_images_in_swiper_carousel( $attachment_id, $size = 'thumbnail', $is_first_img ) {
    $html  = '';
    $image = wp_get_attachment_image_src( $attachment_id, $size, $icon = false );
    if ( $image ) {
        list($src, $width, $height) = $image;
        $hwstring                   = image_hwstring( $width, $height );
        $size_class                 = $size;
        if ( is_array( $size_class ) ) {
            $size_class = join( 'x', $size_class );
        }
        $attachment   = get_post( $attachment_id );
        $default_attr = array(
            'src'   => $src,
            'class' => "attachment-$size_class size-$size_class swiper-lazy",// add swiper class for lazyloading @see https://swiperjs.com/api/#lazy
            'alt'   => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
        );

        $attr = $default_attr;

        // Generate 'srcset' and 'sizes' if not already present.
        if ( empty( $attr['srcset'] ) ) {
            $image_meta = wp_get_attachment_metadata( $attachment_id );

            if ( is_array( $image_meta ) ) {
                $size_array = array( absint( $width ), absint( $height ) );
                $srcset     = wp_calculate_image_srcset( $size_array, $src, $image_meta, $attachment_id );
                $sizes      = wp_calculate_image_sizes( $size_array, $src, $image_meta, $attachment_id );

                if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
                    $attr['srcset'] = $srcset;

                    if ( empty( $attr['sizes'] ) ) {
                        $attr['sizes'] = $sizes;
                    }
                }
            }
        }

        /**
         * Filters the list of attachment image attributes.
         *
         * @since 2.8.0
         *
         * @param array        $attr       Attributes for the image markup.
         * @param WP_Post      $attachment Image attachment post.
         * @param string|array $size       Requested size. Image size or array of width and height values
         *                                 (in that order). Default 'thumbnail'.
         */
        $attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $size );

        // add swiper data-* stuffs for lazyloading now, after all filters
        // @see https://swiperjs.com/api/#lazy
        if ( !empty( $attr['srcset'] ) ) {
            $attr['data-srcset'] = $attr['srcset'];
            unset( $attr['srcset'] );
        }

        if ( !empty( $attr['src'] ) ) {
            $attr['data-src'] = $attr['src'];
            $attr['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            //unset( $attr['src'] );
        }
        if ( !empty( $attr['sizes'] ) ) {
            $attr['data-sek-img-sizes'] = $attr['sizes'];
            unset( $attr['sizes'] );
        }
        // when lazy load is active, we want to lazy load the first image of the slider if offscreen
        if ( $is_first_img && sek_is_img_smartload_enabled() ) {
            $attr['data-sek-src'] = $attr['src'];
        }

        $attr = array_map( 'esc_attr', $attr );
        $html = rtrim( "<img $hwstring" );
        foreach ( $attr as $name => $value ) {
            $html .= " $name=" . '"' . $value . '"';
        }
        $html .= ' />';
    }

    return $html;
}


// /* ------------------------------------------------------------------------- *
// *  IMPORT IMAGE IF NOT ALREADY IN MEDIA LIB
// /* ------------------------------------------------------------------------- */
// @return attachment id or WP_Error
// this method uses download_url()
// it first checks if the media already exists in the media library
function sek_sideload_img_and_return_attachment_id( $img_url ) {
    // Set variables for storage, fix file filename for query strings.
    preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $img_url, $matches );
    $filename = basename( $matches[0] );
    // prefix with nimble_asset_ if not done yet
    // for example, when importing a file, the img might already have the nimble_asset_ prefix if it's been uploaded by Nimble
    if ( 'nimble_asset_' !== substr($filename, 0, strlen('nimble_asset_') ) ) {
        $filename = 'nimble_asset_' . $filename;
    }

    // remove the extension
    $img_title = preg_replace( '/\.[^.]+$/', '', trim( $filename ) );

    //sek_error_log( __FUNCTION__ . ' ALORS img_title?', preg_replace( '/\.[^.]+$/', '', trim( $img_title ) ) );

    // Make sure this img has not already been uploaded
    // Meta query on the alt property, better than the title
    // because of https://github.com/presscustomizr/nimble-builder/issues/435
    $args = array(
        'posts_per_page' => 1,
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        //'name' => $img_title,
        'meta_query' => array(
          array(
            'key'     => '_wp_attachment_image_alt',
            'value'   => $img_title,
            'compare' => '='
          ),
        ),
    );
    $get_attachment = new \WP_Query( $args );

    //error_log( print_r( $get_attachment->posts, true ) );
    if ( is_array( $get_attachment->posts ) && array_key_exists(0, $get_attachment->posts) ) {
        //wp_send_json_error( __CLASS__ . '::' . __CLASS__ . '::' . __FUNCTION__ . ' => file already uploaded : ' . $relative_path );
        $img_id_already_uploaded = $get_attachment->posts[0]->ID;
    }
    // stop now and return the id if the attachment was already uploaded
    if ( isset($img_id_already_uploaded) ) {
        //sek_error_log( __FUNCTION__ . ' ALREADY UPLOADED ?', $img_id_already_uploaded );
        return $img_id_already_uploaded;
    }

    // Insert the media
    // Prepare the file_array that we will pass to media_handle_sideload()
    $file_array = array();
    $file_array['name'] = $filename;

    // Download file to temp location.
    $file_array['tmp_name'] = download_url( $img_url );

    // If error storing temporarily, return the error.
    if ( is_wp_error( $file_array['tmp_name'] ) ) {
        return $file_array['tmp_name'];
    }

    // Do the validation and storage stuff.
    $id = media_handle_sideload( $file_array, 0 );

    // If error storing permanently, unlink.
    if ( is_wp_error( $id ) ) {
        @unlink( $file_array['tmp_name'] );
    } else {
        // Store the title as image alt property
        // so we can identify it uniquely next time when checking if already uploaded
        // of course, if the alt property has been manually modified meanwhile, the image will be loaded again
        // fixes https://github.com/presscustomizr/nimble-builder/issues/435
        add_post_meta( $id, '_wp_attachment_image_alt', $img_title, true );
    }

    return $id;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  Page Menu for menu module
/* ------------------------------------------------------------------------- */
/**
 * Display or retrieve list of pages with optional home link.
 * Modified copy of wp_page_menu()
 * @return string html menu
 */
function sek_page_menu_fallback( $args = array() ) {
    $defaults = array('show_home' => true, 'sort_column' => 'menu_order, post_title', 'menu_class' => 'menu', 'echo' => true, 'link_before' => '', 'link_after' => '');
    $args = wp_parse_args( $args, $defaults );
    $args = apply_filters( 'wp_page_menu_args', $args );
    $menu = '';
    $list_args = $args;

    // Show Home in the menu
    if ( ! empty($args['show_home']) ) {
        if ( true === $args['show_home'] || '1' === $args['show_home'] || 1 === $args['show_home'] ) {
            $text = __('Home' , 'text_domain_to_replace');
        } else {
            $text = $args['show_home'];
        }
        $class = '';
        if ( is_front_page() && !is_paged() ) {
            $class = 'class="current_page_item"';
        }
        $menu .= '<li ' . $class . '><a href="' . home_url( '/' ) . '">' . $args['link_before'] . $text . $args['link_after'] . '</a></li>';
        // If the front page is a page, add it to the exclude list
        if (get_option('show_on_front') == 'page') {
            if ( !empty( $list_args['exclude'] ) ) {
                $list_args['exclude'] .= ',';
            } else {
                $list_args['exclude'] = '';
            }
            $list_args['exclude'] .= get_option('page_on_front');
        }
    }

    $list_args['echo'] = false;
    $list_args['title_li'] = '';

    // limit the number of pages displayed, home excluded from the count if included
    $list_args['number'] = 4;

    $menu .= str_replace( array( "\r", "\n", "\t" ), '', sek_list_pages( $list_args ) );
     // if ( $menu )
    //   $menu = '<ul>' . $menu . '</ul>';
     //$menu = '<div class="' . esc_attr($args['menu_class']) . '">' . $menu . "</div>\n";
    if ( $menu ) {
        $menu = '<ul class="' . esc_attr( $args['menu_class'] ) . '">' . $menu . '</ul>';
    }

    //$menu = apply_filters( 'wp_page_menu', $menu, $args );
    if ( $args['echo'] )
      echo $menu;
    else
      return $menu;
}
 /**
 * Retrieve or display list of pages in list (li) format.
 * Modified copy of wp_list_pages
 * @return string HTML list of pages.
 */
function sek_list_pages( $args = '' ) {
    $defaults = array(
        'depth' => 0,
        'show_date' => '',
        'date_format' => get_option( 'date_format' ),
        'child_of' => 0,
        'exclude' => '',
        'title_li' => __( 'Pages', 'text_domain_to_replace' ),
        'echo' => 1,
        'authors' => '',
        'sort_column' => 'menu_order, post_title',
        'link_before' => '',
        'link_after' => '',
        'walker' => ''
    );
    $r = wp_parse_args( $args, $defaults );
    $output = '';
    $current_page = 0;
     // sanitize, mostly to keep spaces out
    $r['exclude'] = preg_replace( '/[^0-9,]/', '', $r['exclude'] );
     // Allow plugins to filter an array of excluded pages (but don't put a nullstring into the array)
    $exclude_array = ( $r['exclude'] ) ? explode( ',', $r['exclude'] ) : array();
    $r['exclude'] = implode( ',', apply_filters( 'wp_list_pages_excludes', $exclude_array ) );
     // Query pages.
    $r['hierarchical'] = 0;
    $pages = get_pages( $r );
    if ( ! empty( $pages ) ) {
      if ( $r['title_li'] ) {
        $output .= '<li class="pagenav">' . $r['title_li'] . '<ul>';
      }
      global $wp_query;
      if ( is_page() || is_attachment() || $wp_query->is_posts_page ) {
        $current_page = get_queried_object_id();
      } elseif ( is_singular() ) {
        $queried_object = get_queried_object();
        if ( is_post_type_hierarchical( $queried_object->post_type ) ) {
          $current_page = $queried_object->ID;
        }
      }
      $output .= sek_walk_page_tree( $pages, $r['depth'], $current_page, $r );
      if ( $r['title_li'] ) {
          $output .= '</ul></li>';
      }
    }
    $html = apply_filters( 'wp_list_pages', $output, $r );
    if ( $r['echo'] ) {
        echo $html;
    } else {
        return $html;
    }
}


/**
 * Retrieve HTML list content for page list.
 *
 * @uses Walker_Page to create HTML list content.
 * @since 2.1.0
 * @see Walker_Page::walk() for parameters and return description.
*/
function sek_walk_page_tree( $pages, $depth, $current_page, $r ) {
  // if ( empty($r['walker']) )
  //   $walker = new Walker_Page;
  // else
  //   $walker = $r['walker'];
  $walker = new \Walker_Page;
  foreach ( (array) $pages as $page ) {
      if ( $page->post_parent ) {
          $r['pages_with_children'][ $page->post_parent ] = true;
      }
  }
  $args = array( $pages, $depth, $r, $current_page );
  return call_user_func_array(array($walker, 'walk'), $args);
}

function sek_get_user_created_menus() {
    // if ( ! skp_is_customizing() )
    //   return array();
    $all_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
    $user_menus = array();
    foreach ( $all_menus as $menu_obj ) {
        if ( is_string( $menu_obj->slug ) && !empty( $menu_obj->slug ) && !empty( $menu_obj->name ) ) {
            $user_menus[ $menu_obj->slug ] = $menu_obj->name;
        }
    }
    // sek_error_log( 'sek_get_user_created_menus', array_merge(
    //     array( 'nimble_page_menu' => __('Default page menu', 'text_domain_to_replace') )
    // , $user_menus ) );
    return array_merge(
        array( 'nimble_page_menu' => __('Default page menu', 'text_domain_to_replace') )
    , $user_menus );
}

?><?php

/* ------------------------------------------------------------------------- *
 *  MODULES COLLECTION
/* ------------------------------------------------------------------------- */
// introduced when implementing the level tree #359
function sek_get_module_collection() {
    return apply_filters( 'sek_get_module_collection', array(
        array(
          'content-type' => 'preset_section',
          'content-id' => 'two_columns',
          'title' => __( 'Two Columns', 'text_doma' ),
          'icon' => 'Nimble_2-columns_icon.svg'
        ),
        array(
          'content-type' => 'preset_section',
          'content-id' => 'three_columns',
          'title' => __( 'Three Columns', 'text_doma' ),
          'icon' => 'Nimble_3-columns_icon.svg'
        ),
        array(
          'content-type' => 'preset_section',
          'content-id' => 'four_columns',
          'title' => __( 'Four Columns', 'text_doma' ),
          'icon' => 'Nimble_4-columns_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_tiny_mce_editor_module',
          'title' => __( 'WordPress Editor', 'text_doma' ),
          'icon' => 'Nimble_rich-text-editor_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_image_module',
          'title' => __( 'Image', 'text_doma' ),
          'icon' => 'Nimble__image_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_heading_module',
          'title' => __( 'Heading', 'text_doma' ),
          'icon' => 'Nimble__heading_icon.svg'
        ),

        array(
          'content-type' => 'module',
          'content-id' => 'czr_icon_module',
          'title' => __( 'Icon', 'text_doma' ),
          'icon' => 'Nimble__icon_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_button_module',
          'title' => __( 'Button', 'text_doma' ),
          'icon' => 'Nimble_button_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_img_slider_module',
          'title' => __( 'Image & Text Carousel', 'text_doma' ),
          'icon' => 'Nimble_slideshow_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_accordion_module',
          'title' => __( 'Accordion', 'text_doma' ),
          'icon' => 'Nimble_accordion_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_simple_html_module',
          'title' => __( 'Html Content', 'text_doma' ),
          'icon' => 'Nimble_html_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_post_grid_module',
          'title' => __( 'Post Grid', 'text_doma' ),
          'icon' => 'Nimble_posts-list_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_quote_module',
          'title' => __( 'Quote', 'text_doma' ),
          'icon' => 'Nimble_quote_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_shortcode_module',
          'title' => __( 'Shortcode', 'text_doma' ),
          'icon' => 'Nimble_shortcode_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_spacer_module',
          'title' => __( 'Spacer', 'text_doma' ),
          'icon' => 'Nimble__spacer_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_divider_module',
          'title' => __( 'Divider', 'text_doma' ),
          'icon' => 'Nimble__divider_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_map_module',
          'title' => __( 'Map', 'text_doma' ),
          'icon' => 'Nimble_map_icon.svg'
        ),

        array(
          'content-type' => 'module',
          'content-id' => 'czr_widget_area_module',
          'title' => __( 'WordPress widget area', 'text_doma' ),
          'font_icon' => '<i class="fab fa-wordpress-simple"></i>'
          //'active' => sek_are_beta_features_enabled()
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_social_icons_module',
          'title' => __( 'Social Profiles', 'text_doma' ),
          'icon' => 'Nimble_social_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_simple_form_module',
          'title' => __( 'Simple Contact Form', 'text_doma' ),
          'icon' => 'Nimble_contact-form_icon.svg'
        ),

        array(
          'content-type' => 'module',
          'content-id' => 'czr_menu_module',
          'title' => __( 'Menu', 'text_doma' ),
          'font_icon' => '<i class="material-icons">menu</i>'
          //'active' => sek_are_beta_features_enabled()
        )
        // array(
        //   'content-type' => 'module',
        //   'content-id' => 'czr_special_img_module',
        //   'title' => __( 'Nimble Image', 'text_doma' ),
        //   'font_icon' => '<i class="material-icons">all_out</i>',
        //   'active' => sek_is_pro()
        // )
        // array(
        //   'content-type' => 'module',
        //   'content-id' => 'czr_featured_pages_module',
        //   'title' => __( 'Featured pages',  'text_doma' ),
        //   'icon' => 'Nimble__featured_icon.svg'
        // ),


    ));
}




// @return void()
// Fired in 'wp_enqueue_scripts'
// Recursively sniff the local and global sections to populate Nimble_Manager()->contextually_active_modules
// introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_populate_collection_of_contextually_active_modules( $recursive_data = null, $module_collection = null ) {
    if ( is_null( $recursive_data ) ) {
        $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
        $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
        $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
        $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

        $recursive_data = array_merge( $local_collection, $global_collection );
    }
    if ( is_null( $module_collection ) ) {
        // make sure Nimble_Manager()->contextually_active_modules is initialized as an array before starting populating it.
        $module_collection = 'not_set' === Nimble_Manager()->contextually_active_modules ? [] : Nimble_Manager()->contextually_active_modules;
    }

    foreach ($recursive_data as $key => $value) {
        if ( is_array( $value ) && array_key_exists('module_type', $value) ) {
            $module_type = $value['module_type'];
            if ( !array_key_exists($module_type, $module_collection) ) {
                $module_collection[$module_type] = [];
            }
            if ( !in_array( $value['id'], $module_collection[$module_type] ) ) {
                $module_collection[$module_type][] = $value['id'];
            }
        } else if ( is_array( $value ) ) {
            $module_collection = sek_populate_collection_of_contextually_active_modules( $value, $module_collection );
        }
    }
    Nimble_Manager()->contextually_active_modules = $module_collection;
    return Nimble_Manager()->contextually_active_modules;
}

// return the cached collection or build it when needed
function sek_get_collection_of_contextually_active_modules( $recursive_data = null, $module_collection = null ) {
    if ( 'not_set' === Nimble_Manager()->contextually_active_modules ) {
        return sek_populate_collection_of_contextually_active_modules();
    }
    return Nimble_Manager()->contextually_active_modules;
}



/* ------------------------------------------------------------------------- *
 *  REGISTERED MODULES => GET PROPERTY
/* ------------------------------------------------------------------------- */
// Helper
function sek_get_registered_module_type_property( $module_type, $property = '' ) {
    // check introduced since https://github.com/presscustomizr/nimble-builder/issues/432
    // may not be mandatory
    if ( !class_exists('\Nimble\CZR_Fmk_Base') ) {
        sek_error_log( __FUNCTION__ . ' => error => CZR_Fmk_Base not loaded' );
        return;
    }
    // registered modules
    $registered_modules = CZR_Fmk_Base()->registered_modules;
    if ( ! array_key_exists( $module_type, $registered_modules ) ) {
        sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' not registered.' );
        return;
    }
    if ( array_key_exists( $property , $registered_modules[ $module_type ] ) ) {
        return $registered_modules[ $module_type ][$property];
    }
    return;
}




/* ------------------------------------------------------------------------- *
 *  GET THE INPUT VALUE OF A GIVEN MODULE MODEL
/* ------------------------------------------------------------------------- */
// Recursive helper
// Handles simple model and multidimensional module model ( father - children ), like
// Array
// (
//     [quote_content] => Array
//         (
//             [quote_text] => Hey, careful, man, there's a beverage here!
//             [quote_font_size_css] => Array
//                 (
//                     [desktop] => 29px
//                     [mobile] => 12px
//                 )

//             [quote_letter_spacing_css] => 7
//             [quote___flag_important] => 1
//         )

//     [cite_content] => Array
//         (
//             [cite_text] => The Dude in <a href="https://www.imdb.com/title/tt0118715/quotes/qt0464770" rel="nofollow noopener noreferrer" target="_blank">The Big Lebowski</a>
//             [cite_font_style_css] => italic
//         )

//     [design] => Array
//         (
//             [quote_design] => border-before
//         )
// )
// Helper
// @param $input_id ( string )
// @param $module_model ( array )
function sek_get_input_value_in_module_model( $input_id, $module_model ) {
    if ( ! is_string( $input_id ) ) {
        sek_error_log( __FUNCTION__ . ' => error => the $input_id param should be a string', $module_model);
        return;
    }
    if ( ! is_array( $module_model ) ) {
        sek_error_log( __FUNCTION__ . ' => error => the $module_model param should be an array', $module_model );
        return;
    }
    $input_value = '_not_set_';
    foreach ( $module_model as $key => $data ) {
        if ( $input_value !== '_not_set_' )
          break;
        if ( $input_id === $key ) {
            $input_value = $data;
            break;
        } else {
            if ( is_array( $data ) ) {
                $input_value = sek_get_input_value_in_module_model( $input_id, $data );
            }
        }
    }
    return $input_value;
}





/* ------------------------------------------------------------------------- *
 *  REGISTERED MODULES => DEFAULT MODULE MODEL
/* ------------------------------------------------------------------------- */
// @param (string) module_type
// Walk the registered modules tree and generates the module default if not already cached
// used :
// - in sek_normalize_module_value_with_defaults(), when preprocessing the module model before printing the module template. @see SEK_Front::render()
// - when setting the css of a level option. @see for example : sek_add_css_rules_for_bg_border_background()
// @return array()
function sek_get_default_module_model( $module_type = '' ) {
    $default = array();
    if ( empty( $module_type ) || is_null( $module_type ) )
      return $default;

    // check introduced since https://github.com/presscustomizr/nimble-builder/issues/432
    // may not be mandatory
    if ( !class_exists('\Nimble\CZR_Fmk_Base') ) {
        sek_error_log( __FUNCTION__ . ' => error => CZR_Fmk_Base not loaded' );
        return $default;
    }

    // Did we already cache it ?
    $default_models = Nimble_Manager()->default_models;
    if ( ! empty( $default_models[ $module_type ] ) ) {
        $default = $default_models[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base()->registered_modules;
        if ( ! array( $registered_modules ) || !CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $default;
        }

        // Is this module a father ?
        if ( !empty( $registered_modules[ $module_type ]['is_father'] ) && true === $registered_modules[ $module_type ]['is_father'] ) {
            if ( empty( $registered_modules[ $module_type ][ 'children' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
                return $default;
            }
            if ( ! is_array( $registered_modules[ $module_type ][ 'children' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' children modules should be an array' );
                return $default;
            }

            foreach ( $registered_modules[ $module_type ][ 'children' ] as $opt_group => $child_mod_type ) {
                if ( empty( $registered_modules[ $child_mod_type ][ 'tmpl' ] ) ) {
                    sek_error_log( __FUNCTION__ . ' => ' . $child_mod_type . ' => missing "tmpl" property => impossible to build the father default model.' );
                    continue;
                }
                $default[$opt_group] = _sek_build_default_model( $registered_modules[ $child_mod_type ][ 'tmpl' ] );
            }
        }
        // Not father module case
        else {
            if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the default model.' );
                return $default;
            }
            // Build
            $default = _sek_build_default_model( $registered_modules[ $module_type ][ 'tmpl' ] );
        }

        // Cache
        $default_models[ $module_type ] = $default;
        Nimble_Manager()->default_models = $default_models;
        //sek_error_log( __FUNCTION__ . ' => $default_models', $default_models );
    }
    return $default;
}

// @return array() default model
// Walk recursively the 'tmpl' property of the module
// 'tmpl' => array(
//     'pre-item' => array(
//         'social-icon' => array(
//             'input_type'  => 'select',
//             'title'       => __('Select an icon', 'text_doma')
//         ),
//     ),
//     'mod-opt' => array(
//         'social-size' => array(
//             'input_type'  => 'number',
//             'title'       => __('Size in px', 'text_doma'),
//             'step'        => 1,
//             'min'         => 5,
//             'transport' => 'postMessage'
//         )
//     ),
//     'item-inputs' => array(
//         'item-inputs' => array(
                // 'tabs' => array(
                //     array(
                //         'title' => __('Content', 'text_doma'),
                //         //'attributes' => 'data-sek-device="desktop"',
                //         'inputs' => array(
                //             'content' => array(
                //                 'input_type'  => 'detached_tinymce_editor',
                //                 'title'       => __('Content', 'text_doma')
                //             ),
                //             'h_alignment_css' => array(
                //                 'input_type'  => 'h_text_alignment',
                //                 'title'       => __('Alignment', 'text_doma'),
                //                 'default'     => is_rtl() ? 'right' : 'left',
                //                 'refresh_markup' => false,
                //                 'refresh_stylesheet' => true
                //             )
                //         )
//         )
//     )
// )
function _sek_build_default_model( $module_tmpl_data, $default_model = null ) {
    $default_model = is_array( $default_model ) ? $default_model : array();
    //error_log( print_r(  $module_tmpl_data , true ) );
    foreach( $module_tmpl_data as $key => $data ) {
        if ( 'pre-item' === $key )
          continue;
        if ( is_array( $data ) && array_key_exists( 'input_type', $data ) ) {
            $default_model[ $key ] = array_key_exists( 'default', $data ) ? $data[ 'default' ] : '';
        }
        if ( is_array( $data ) ) {
            $default_model = _sek_build_default_model( $data, $default_model );
        }
    }

    return $default_model;
}











/* ------------------------------------------------------------------------- *
 *  REGISTERED MODULES => INPUT LIST
/* ------------------------------------------------------------------------- */
// @param (string) module_type
// Walk the registered modules tree and generates the module input list if not already cached
// used :
// - when filtering 'sek_add_css_rules_for_input_id' @see Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker()
// @return array()
function sek_get_registered_module_input_list( $module_type = '' ) {
    $input_list = array();
    if ( empty( $module_type ) || is_null( $module_type ) )
      return $input_list;

    // check introduced since https://github.com/presscustomizr/nimble-builder/issues/432
    // may not be mandatory
    if ( !class_exists('\Nimble\CZR_Fmk_Base') ) {
        sek_error_log( __FUNCTION__ . ' => error => CZR_Fmk_Base not loaded' );
        return $input_list;
    }

    // Did we already cache it ?
    $cached_input_lists = Nimble_Manager()->cached_input_lists;
    if ( ! empty( $cached_input_lists[ $module_type ] ) ) {
        $input_list = $cached_input_lists[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base()->registered_modules;
        // sek_error_log( __FUNCTION__ . ' => registered_modules', $registered_modules );
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $input_list;
        }


        // Is this module a father ?
        if ( !empty( $registered_modules[ $module_type ]['is_father'] ) && true === $registered_modules[ $module_type ]['is_father'] ) {
            if ( empty( $registered_modules[ $module_type ][ 'children' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
                return $input_list;
            }
            if ( ! is_array( $registered_modules[ $module_type ][ 'children' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' children modules should be an array' );
                return $input_list;
            }
            $temp = array();
            foreach ( $registered_modules[ $module_type ][ 'children' ] as $opt_group => $child_mod_type ) {
                if ( empty( $registered_modules[ $child_mod_type ][ 'tmpl' ] ) ) {
                    sek_error_log( __FUNCTION__ . ' => ' . $child_mod_type . ' => missing "tmpl" property => impossible to build the master input_list.' );
                    continue;
                }
                // $temp[$opt_group] = _sek_build_input_list( $registered_modules[ $child_mod_type ][ 'tmpl' ] );
                // $input_list = array_merge( $input_list, $temp[$opt_group] );

                $input_list[$opt_group] = _sek_build_input_list( $registered_modules[ $child_mod_type ][ 'tmpl' ] );
            }
        } else {
            if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the input_list.' );
                return $input_list;
            }
            // Build
            $input_list = _sek_build_input_list( $registered_modules[ $module_type ][ 'tmpl' ] );
        }




        // if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
        //     sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the input_list.' );
        //     return $input_list;
        // }

        // // Build
        // $input_list = _sek_build_input_list( $registered_modules[ $module_type ][ 'tmpl' ] );

        // Cache
        $cached_input_lists[ $module_type ] = $input_list;
        Nimble_Manager()->cached_input_lists = $cached_input_lists;
        // sek_error_log( __FUNCTION__ . ' => $cached_input_lists', $cached_input_lists );
    }
    return $input_list;
}

// @return array() default model
// Walk recursively the 'tmpl' property of the module
// 'tmpl' => array(
//     'pre-item' => array(
//         'social-icon' => array(
//             'input_type'  => 'select',
//             'title'       => __('Select an icon', 'text_doma')
//         ),
//     ),
//     'mod-opt' => array(
//         'social-size' => array(
//             'input_type'  => 'number',
//             'title'       => __('Size in px', 'text_doma'),
//             'step'        => 1,
//             'min'         => 5,
//             'transport' => 'postMessage'
//         )
//     ),
//     'item-inputs' => array(
//         'item-inputs' => array(
                // 'tabs' => array(
                //     array(
                //         'title' => __('Content', 'text_doma'),
                //         //'attributes' => 'data-sek-device="desktop"',
                //         'inputs' => array(
                //             'content' => array(
                //                 'input_type'  => 'detached_tinymce_editor',
                //                 'title'       => __('Content', 'text_doma')
                //             ),
                //             'h_alignment_css' => array(
                //                 'input_type'  => 'h_text_alignment',
                //                 'title'       => __('Alignment', 'text_doma'),
                //                 'default'     => is_rtl() ? 'right' : 'left',
                //                 'refresh_markup' => false,
                //                 'refresh_stylesheet' => true
                //             )
                //         )
//         )
//     )
// )
// Build the input list from item-inputs and modop-inputs
function _sek_build_input_list( $module_tmpl_data, $input_list = null ) {
    $input_list = is_array( $input_list ) ? $input_list : array();
    //sek_error_log( '_sek_build_input_list', print_r(  $module_tmpl_data , true ) );
    foreach( $module_tmpl_data as $key => $data ) {
        if ( 'pre-item' === $key )
          continue;
        if ( is_array( $data ) && array_key_exists( 'input_type', $data ) ) {
            // each input_id of a module should be unique
            if ( array_key_exists( $key, $input_list ) ) {
                sek_error_log( __FUNCTION__ . ' => error => duplicated input_id found => ' . $key );
            } else {
                $input_list[ $key ] = $data;
            }
        } else if ( is_array( $data ) ) {
            $input_list = _sek_build_input_list( $data, $input_list );
        }
    }

    return $input_list;
}








/* ------------------------------------------------------------------------- *
 *  NORMALIZE MODULE VALUE WITH DEFAULT
 *  preprocessing the module model before printing the module template.
 *  used before rendering or generating css
/* ------------------------------------------------------------------------- */
// @return array() $normalized_model
function sek_normalize_module_value_with_defaults( $raw_module_model ) {
    $normalized_model = $raw_module_model;
    if ( empty( $normalized_model['module_type'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing module type', $normalized_model );
    }
    $module_type = $normalized_model['module_type'];
    $is_father = sek_get_registered_module_type_property( $module_type, 'is_father' );

    $raw_module_value = ( ! empty( $raw_module_model['value'] ) && is_array( $raw_module_model['value'] ) ) ? $raw_module_model['value'] : array();

    // reset the model value and rewrite it normalized with the defaults
    $normalized_model['value'] = array();
    if ( $is_father ) {
        $children = sek_get_registered_module_type_property( $module_type, 'children' );
        if ( empty( $children ) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
            return $default;
        }
        if ( ! is_array( $children ) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' children modules should be an array' );
            return $default;
        }
        foreach ( $children as $opt_group => $child_mod_type ) {
            $children_value = ( ! empty( $raw_module_value[$opt_group] ) && is_array( $raw_module_value[$opt_group] ) ) ? $raw_module_value[$opt_group] : array();
            $normalized_model['value'][ $opt_group ] = _sek_normalize_single_module_values( $children_value, $child_mod_type );
        }
    } else {
        $normalized_model['value'] = _sek_normalize_single_module_values( $raw_module_value, $module_type );
    }
    //sek_error_log('sek_normalize_single_module_values for module type ' . $module_type , $normalized_model );
    return $normalized_model;
}

// @return array()
function _sek_normalize_single_module_values( $raw_module_value, $module_type ) {
    $default_value_model  = sek_get_default_module_model( $module_type );//<= walk the registered modules tree and generates the module default if not already cached

    // reset the model value and rewrite it normalized with the defaults
    $module_values = array();
    if ( czr_is_multi_item_module( $module_type ) ) {
        foreach ( $raw_module_value as $item ) {
            $module_values[] = wp_parse_args( $item, $default_value_model );
        }
    } else {
        $module_values = wp_parse_args( $raw_module_value, $default_value_model );
    }

    return $module_values;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  BREAKPOINTS HELPER
/* ------------------------------------------------------------------------- */
function sek_get_global_custom_breakpoint() {
    $global_breakpoint_data = sek_get_global_option_value('breakpoint');
    if ( is_null( $global_breakpoint_data ) || empty( $global_breakpoint_data['global-custom-breakpoint'] ) )
      return;

    if ( empty( $global_breakpoint_data[ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $global_breakpoint_data[ 'use-custom-breakpoint'] ) )
      return;

    return intval( $global_breakpoint_data['global-custom-breakpoint'] );
}


// @return bool
// introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// Let us know if we need to apply the user defined custom breakpoint to all by-device customizations, like alignment
// false by default.
function sek_is_global_custom_breakpoint_applied_to_all_customizations_by_device() {
    $global_breakpoint_data = sek_get_global_option_value('breakpoint');
    if ( is_null( $global_breakpoint_data ) || empty( $global_breakpoint_data['global-custom-breakpoint'] ) )
      return false;

    if ( empty( $global_breakpoint_data[ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $global_breakpoint_data[ 'use-custom-breakpoint'] ) )
      return false;

    // We need a custom breakpoint > 1
    if ( intval( $global_breakpoint_data['global-custom-breakpoint'] ) <= 1 )
      return;

    // apply-to-all option is unchecked by default
    // returns true when user has checked the apply to all option
    return array_key_exists('apply-to-all', $global_breakpoint_data ) && sek_booleanize_checkbox_val( $global_breakpoint_data[ 'apply-to-all' ] ) ;
}


// invoked when filtering 'sek_add_css_rules_for__section__options'
// param 'for_responsive_columns' has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
// when for columns, we always apply the custom breakpoint defined by the user
// otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
// 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
// @param params array(
//  'section_model' => array(),
//  'for_responsive_columns' => bool
// )
function sek_get_section_custom_breakpoint( $params ) {
    if ( !is_array( $params ) )
      return;

    $params = wp_parse_args( $params, array(
        'section_model' => array(),
        'for_responsive_columns' => false
    ));

    $section = $params['section_model'];

    if ( ! is_array( $section ) )
      return;

    if ( empty($section['id']) )
      return;

    $options = empty( $section[ 'options' ] ) ? array() : $section['options'];
    if ( empty( $options[ 'breakpoint' ] ) )
      return;

    if ( empty( $options[ 'breakpoint' ][ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $options[ 'breakpoint' ][ 'use-custom-breakpoint'] ) )
      return;

    // assign default value if use-custom-breakpoint is checked but there's no breakpoint set.
    // this can also occur if the custom breakpoint is left to default in the customizer ( default values are not considered when saving )
    if ( empty( $options[ 'breakpoint' ][ 'custom-breakpoint' ] ) ) {
        if ( array_key_exists('custom-breakpoint', $options[ 'breakpoint' ] ) ) {
            // this is the case when user has emptied the setting
            $custom_breakpoint = 1;// added when fixing https://github.com/presscustomizr/nimble-builder/issues/623
        } else {
            $custom_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];//768
        }
    } else {
        $custom_breakpoint = intval( $options[ 'breakpoint' ][ 'custom-breakpoint' ] );
    }

    if ( $custom_breakpoint <= 0 )
      return 1;

    // 1) When the breakpoint is requested for responsive columns, we always return the custom value
    if ( $params['for_responsive_columns'] )
      return $custom_breakpoint;

    // 2) Otherwise ( other CSS rules generation case, like alignment ) we make sure that user want to apply the custom breakpoint also to other by-device customizations
    return sek_is_section_custom_breakpoint_applied_to_all_customizations_by_device( $options[ 'breakpoint' ] ) ? $custom_breakpoint : null;
}


// @return bool
// introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// Let us know if we need to apply the user defined custom breakpoint to all by-device customizations, like alignment
// false by default.
// @param $section_breakpoint_options = array(
//    'use-custom-breakpoint' => bool
//    'custom-breakpoint' => int
//    'apply-to-all' => bool
// )
function sek_is_section_custom_breakpoint_applied_to_all_customizations_by_device( $section_breakpoint_options ) {
    if ( ! is_array( $section_breakpoint_options ) || empty( $section_breakpoint_options ) )
      return;

    if ( empty( $section_breakpoint_options[ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $section_breakpoint_options[ 'use-custom-breakpoint'] ) )
      return;

    // We need a custom breakpoint > 1
    // Make sure the custom breakpoint has not been emptied, otherwise assign a minimal value of 1px
    // fixes : https://github.com/presscustomizr/nimble-builder/issues/623
    $custom_breakpoint = empty( $section_breakpoint_options['custom-breakpoint'] ) ? 1 : $section_breakpoint_options['custom-breakpoint'];
    if ( intval( $custom_breakpoint ) <= 1 )
      return;

    // apply-to-all option is unchecked by default
    // returns true when user has checked the apply to all option
    return array_key_exists('apply-to-all', $section_breakpoint_options ) && sek_booleanize_checkbox_val( $section_breakpoint_options[ 'apply-to-all' ] );
}


// Recursive helper
// Is also used when building the dyn_css or when firing sek_add_css_rules_for_spacing()
// @param id : mandatory
// @param collection : optional <= that's why if missing we must walk all collections : local and global
function sek_get_closest_section_custom_breakpoint( $params ) {
    $params = wp_parse_args( $params, array(
        'searched_level_id' => '',
        'collection' => 'not_set',
        'skope_id' => '',

        'last_section_breakpoint_found' => 0,
        'last_regular_section_breakpoint_found' => 0,
        'last_nested_section_breakpoint_found' => 0,

        'searched_level_id_found' => false,

        // the 'for_responsive_columns' param has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
        // so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
        // when for columns, we always apply the custom breakpoint defined by the user
        // otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
        // 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
        'for_responsive_columns' => false
    ) );

    extract( $params, EXTR_OVERWRITE );

    if ( ! is_string( $searched_level_id ) || empty( $searched_level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $last_section_breakpoint_found;;
    }
    if ( $searched_level_id_found ) {
        return $last_section_breakpoint_found;
    }

    // When no collection is provided, we must walk all collections, local and global.
    if ( 'not_set' === $collection  ) {
        if ( empty( $skope_id ) ) {
            if ( is_array( $_POST ) && ! empty( $_POST['location_skope_id'] ) ) {
                $skope_id = $_POST['location_skope_id'];
            } else {
                // When fired during an ajax 'customize_save' action, the skp_get_skope_id() is determined with $_POST['local_skope_id']
                // @see add_filter( 'skp_get_skope_id', '\Nimble\sek_filter_skp_get_skope_id', 10, 2 );
                $skope_id = skp_get_skope_id();
            }
        }
        if ( empty( $skope_id ) || '_skope_not_set_' === $skope_id ) {
            sek_error_log( __FUNCTION__ . ' => the skope_id should not be empty.');
        }
        $local_skope_settings = sek_get_skoped_seks( $skope_id );
        $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
        $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
        $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

        $collection = array_merge( $local_collection, $global_collection );
    }

    // Loop collections
    foreach ( $collection as $level_data ) {
        //sek_error_log($last_section_breakpoint_found . ' MATCH ?  => LEVEL ID AND TYPE => ' . $level_data['level'] . ' | ' . $level_data['id'] );
        // stop here and return if a match was recursively found
        if ( $searched_level_id_found )
          break;

        if ( 'section' == $level_data['level'] ) {
            $section_maybe_custom_breakpoint = intval( sek_get_section_custom_breakpoint( array( 'section_model' => $level_data, 'for_responsive_columns' => $for_responsive_columns ) ) );

            if ( !empty( $level_data['is_nested'] ) && $level_data['is_nested'] ) {
                $last_nested_section_breakpoint_found = $section_maybe_custom_breakpoint;
            } else {
                $last_nested_section_breakpoint_found = 0;//reset last nested breakpoint
                $last_regular_section_breakpoint_found = $section_maybe_custom_breakpoint;
            }

           //sek_error_log('SECTION ID AND BREAKPOINT ' . $level_data['level'] . ' | ' . $level_data['id'] , $last_section_breakpoint_found );

            // sek_error_log('ALORS ???', compact(
            //     'searched_level_id_found',
            //     'last_section_breakpoint_found',
            //     'last_regular_section_breakpoint_found',
            //     'last_nested_section_breakpoint_found'
            // ) );
        }

        if ( array_key_exists( 'id', $level_data ) && $searched_level_id == $level_data['id'] ) {
            //match found, break this loop
            // sek_error_log('MATCH FOUND! => ' . $last_section_breakpoint_found );
            // sek_error_log('MATCH FOUND => ALORS ???', compact(
            //     'searched_level_id_found',
            //     'last_section_breakpoint_found',
            //     'last_regular_section_breakpoint_found',
            //     'last_nested_section_breakpoint_found'
            // ) );
            if ( $last_nested_section_breakpoint_found >= 1 ) {
                $last_section_breakpoint_found = $last_nested_section_breakpoint_found;
            } else if ( $last_regular_section_breakpoint_found >= 1 ) {
                $last_section_breakpoint_found = $last_regular_section_breakpoint_found;
            } else {
                $last_section_breakpoint_found = 0;
            }

            $searched_level_id_found = true;
            break;
        }
        if ( !$searched_level_id_found && array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            $collection = $level_data['collection'];

            $recursive_params = compact(
                'searched_level_id',
                'collection',
                'skope_id',
                'last_section_breakpoint_found',
                'last_regular_section_breakpoint_found',
                'last_nested_section_breakpoint_found',
                'searched_level_id_found',
                'for_responsive_columns'
            );
            $recursive_values = sek_get_closest_section_custom_breakpoint( $recursive_params );

            if ( is_array($recursive_values) ) {
                extract( $recursive_values );
            } else {
                $last_section_breakpoint_found = $recursive_values;
                $searched_level_id_found = true;
                break;
            }
        }
    }

    // Returns a breakpoint int if found or an array
    // => this way we can determine if we continue or not to walk recursively
    return $searched_level_id_found ? $last_section_breakpoint_found : compact(
        'searched_level_id_found',
        'last_section_breakpoint_found',
        'last_regular_section_breakpoint_found',
        'last_nested_section_breakpoint_found'
    );
}

?><?php
/* ------------------------------------------------------------------------- *
 *   LOCAL OPTIONS HELPERS
/* ------------------------------------------------------------------------- */
// @param $option_name = string
// 'nimble_front_classes_ready' is fired when Nimble_Manager() is instanciated
function sek_get_local_option_value( $option_name = '', $skope_id = null ) {
    if ( empty($option_name) ) {
        sek_error_log( __FUNCTION__ . ' => invalid option name' );
        return array();
    }
    if ( !skp_is_customizing() && did_action('nimble_front_classes_ready') && '_not_cached_yet_' !== Nimble_Manager()->local_options ) {
        $local_options = Nimble_Manager()->local_options;
    } else {
        // use the provided skope_id if in the signature
        $skope_id = ( !empty( $skope_id ) && is_string( $skope_id ))? $skope_id : skp_get_skope_id();
        $localSkopeNimble = sek_get_skoped_seks( skp_get_skope_id() );
        $local_options = ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['local_options'] ) && is_array( $localSkopeNimble['local_options'] ) ) ? $localSkopeNimble['local_options'] : array();
        // Cache only after 'wp' && 'nimble_front_classes_ready'
        // never cache when doing ajax
        if ( did_action('nimble_front_classes_ready') && did_action('wp') && !defined('DOING_AJAX') )  {
            Nimble_Manager()->local_options = $local_options;
        }
    }
    // maybe normalizes with default values
    $values = ( ! empty( $local_options ) && ! empty( $local_options[ $option_name ] ) ) ? $local_options[ $option_name ] : null;
    if ( did_action('nimble_front_classes_ready') ) {
        $values = sek_normalize_local_options_with_defaults( $option_name, $values );
    }
    return $values;
}


// @return array() $normalized_values
// @see _1_6_4_sektions_generate_UI_local_skope_options.js
function sek_normalize_local_options_with_defaults( $option_name, $raw_module_values ) {
    if ( empty($option_name) ) {
        sek_error_log( __FUNCTION__ . ' => invalid option name' );
        return array();
    }
    $normalized_values = ( !empty($raw_module_values) && is_array( $raw_module_values ) ) ? $raw_module_values : array();
    // map the option key as saved in db ( @see _1_6_4_sektions_generate_UI_local_skope_options.js ) and the module type
    $local_option_map = SEK_Front_Construct::$local_options_map;

    if ( !array_key_exists($option_name, $local_option_map) ) {
        sek_error_log( __FUNCTION__ . ' => invalid option name', $option_name );
        return $raw_module_values;
    } else {
        $module_type = $local_option_map[$option_name];
    }

    // normalize with the defaults
    // class_exists check introduced since https://github.com/presscustomizr/nimble-builder/issues/432
    // may not be mandatory
    if ( class_exists('\Nimble\CZR_Fmk_Base') ) {
        if( CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
            $normalized_values = _sek_normalize_single_module_values( $normalized_values, $module_type );
        }
    }
    return $normalized_values;
}





/* ------------------------------------------------------------------------- *
 *  GLOBAL OPTIONS HELPERS
/* ------------------------------------------------------------------------- */
// @param $option_name = string
// 'nimble_front_classes_ready' is fired when Nimble_Manager() is instanciated
function sek_get_global_option_value( $option_name = '' ) {
    if ( empty($option_name) ) {
        sek_error_log( __FUNCTION__ . ' => invalid option name' );
        return array();
    }
    if ( !skp_is_customizing() && did_action('nimble_front_classes_ready') && '_not_cached_yet_' !== Nimble_Manager()->global_nimble_options ) {
        $global_nimble_options = Nimble_Manager()->global_nimble_options;
    } else {
        $global_nimble_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
        //sek_error_log(' SOOO OPTIONS ?', $global_nimble_options );
        // cache when nimble is ready
        // this hook is fired when Nimble_Manager() is instanciated
        // never cache when doing ajax
        if ( did_action('nimble_front_classes_ready') && !defined('DOING_AJAX') ) {
            Nimble_Manager()->global_nimble_options = $global_nimble_options;
        }
    }
    // maybe normalizes with default values
    $values = ( is_array( $global_nimble_options ) && !empty( $global_nimble_options[ $option_name ] ) ) ? $global_nimble_options[ $option_name ] : null;
    if ( did_action('nimble_front_classes_ready') ) {
        $values = sek_normalize_global_options_with_defaults( $option_name, $values );
    }
    return $values;
}



// @see _1_6_5_sektions_generate_UI_global_options.js
// @return array() $normalized_values
function sek_normalize_global_options_with_defaults( $option_name, $raw_module_values ) {
    if ( empty($option_name) ) {
        sek_error_log( __FUNCTION__ . ' => invalid option name' );
        return array();
    }
    $normalized_values = ( !empty($raw_module_values) && is_array( $raw_module_values ) ) ? $raw_module_values : array();
    // map the option key as saved in db ( @see _1_6_5_sektions_generate_UI_global_options.js ) and the module type
    $global_option_map = SEK_Front_Construct::$global_options_map;

    //sek_error_log('SEK_Front_Construct::$global_options_map', SEK_Front_Construct::$global_options_map );

    if ( !array_key_exists($option_name, $global_option_map) ) {
        sek_error_log( __FUNCTION__ . ' => invalid option name', $option_name );
        return $raw_module_values;
    } else {
        $module_type = $global_option_map[$option_name];
    }

    // normalize with the defaults
    // class_exists check introduced since https://github.com/presscustomizr/nimble-builder/issues/432
    // may not be mandatory
    if ( class_exists('\Nimble\CZR_Fmk_Base') ) {
        if( CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
            $normalized_values = _sek_normalize_single_module_values( $normalized_values, $module_type );
        }
    } else {
        sek_error_log( __FUNCTION__ . ' => error => CZR_Fmk_Base not loaded' );
    }
    return $normalized_values;
}

?><?php


/* ------------------------------------------------------------------------- *
 *  Dynamic variables parsing
/* ------------------------------------------------------------------------- */
function sek_find_pattern_match($matches) {
    $replace_values = apply_filters( 'sek_template_tags', array(
      'home_url' => 'home_url',
      'the_title' => 'sek_get_the_title',
      'the_content' => 'sek_get_the_content'
    ));

    if ( array_key_exists( $matches[1], $replace_values ) ) {
      $dyn_content = $replace_values[$matches[1]];
      $fn_name = $dyn_content;// <= typically not namespaced if WP core function, or function added with a filter from a child theme for example
      $namespaced_fn_name = __NAMESPACE__ . '\\' . $dyn_content; // <= namespaced if Nimble Builder function, introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
      if ( function_exists( $namespaced_fn_name ) ) {
        return $namespaced_fn_name();//<= @TODO use call_user_func() here + handle the case when the callback is a method
      } else if ( function_exists( $fn_name ) ) {
        return $fn_name();//<= @TODO use call_user_func() here + handle the case when the callback is a method
      } else if ( is_string($dyn_content) ) {
        return $dyn_content;
      } else {
        return null;
      }
    }
    return null;
}
// fired @filter 'nimble_parse_template_tags'
function sek_parse_template_tags( $val ) {
    //the pattern could also be '!\{\{(\w+)\}\}!', but adding \s? allows us to allow spaces around the term inside curly braces
    //see https://stackoverflow.com/questions/959017/php-regex-templating-find-all-occurrences-of-var#comment71815465_959026
    return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(\w+)\s?\}\}!', '\Nimble\sek_find_pattern_match', $val) : $val;
}
add_filter( 'nimble_parse_template_tags', '\Nimble\sek_parse_template_tags' );

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_title() {
  if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      $post_id = sek_get_posted_query_param_when_customizing( 'post_id' );
      return is_int($post_id) ? get_the_title($post_id) : null;
  } else {
      return get_the_title();
  }
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_content() {
  if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      $post_id = sek_get_posted_query_param_when_customizing( 'post_id' );
      $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
      if ( $is_singular && is_int($post_id) ) {
          $post_object = get_post( $post_id );
          return ! empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
      }
  } else {
      if( is_singular() ) {
        $post_object = get_post();
        return ! empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
      }
  }
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
// Possible params as of October 2019
// @see inc/czr-skope/_dev/1_1_0_skop_customizer_preview_load_assets.php::
// 'is_singular' => $wp_query->is_singular,
// 'post_id' => get_the_ID()
function sek_get_posted_query_param_when_customizing( $param ) {
  if ( isset( $_POST['czr_query_params'] ) ) {
      $query_params = json_decode( wp_unslash( $_POST['czr_query_params'] ), true );
      if ( array_key_exists( $param, $query_params ) ) {
          return $query_params[$param];
      } else {
          sek_error_log( __FUNCTION__ . ' => invalid param requested');
          return null;
      }
  }
  return null;
}

?><?php
/* ------------------------------------------------------------------------- *
 *   TEMPLATE OVERRIDE HELPERS
/* ------------------------------------------------------------------------- */
// TEMPLATES PATH
// added for #532, october 2019
/**
 * Returns the path to the NIMBLE templates directory
 * inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
 */
function sek_get_templates_dir() {
  return NIMBLE_BASE_PATH . "/tmpl";
}

// added for #532, october 2019
/* Returns the template directory name.
 * inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
*/
function sek_get_theme_template_dir_name() {
  return trailingslashit( apply_filters( 'nimble_templates_dir', 'nimble_templates' ) );
}


// added for #532, october 2019
/**
 * Returns a list of paths to check for template locations
 * inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
 */
function sek_get_theme_template_base_paths() {

  $template_dir = sek_get_theme_template_dir_name();

  $file_paths = array(
    1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
    10 => trailingslashit( get_template_directory() ) . $template_dir
  );

  $file_paths = apply_filters( 'nimble_template_paths', $file_paths );

  // sort the file paths based on priority
  ksort( $file_paths, SORT_NUMERIC );

  return array_map( 'trailingslashit', $file_paths );
}


// @return path string
// added for #400
// @param params = array(
//  'file_name' string 'nimble_template.php',
//  'folder' =>  string 'page-templates', 'header', 'footer'
// )
// @param
function sek_maybe_get_overriden_local_template_path( $params = array() ) {
    if ( empty( $params ) || ! is_array( $params ))
      return;
    $params = wp_parse_args( $params, array( 'file_name' => '', 'folder' => 'page-templates' ) );

    if ( ! in_array( $params['folder'] , array( 'page-templates', 'header', 'footer' ) ) )
      return;

    $overriden_template_path = '';
    // try locating this template file by looping through the template paths
    // inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
    foreach( sek_get_theme_template_base_paths() as $path_candidate ) {
      if( file_exists( $path_candidate . $params['folder'] . '/' . $params['file_name'] ) ) {
        $overriden_template_path = $path_candidate . $params['folder'] . '/' . $params['file_name'];
        break;
      }
    }
    return $overriden_template_path;
}



// @return mixed null || string
function sek_get_locale_template(){
    $template_path = null;
    $local_template_data = sek_get_local_option_value( 'template' );
    if ( ! empty( $local_template_data ) && ! empty( $local_template_data['local_template'] ) && 'default' !== $local_template_data['local_template'] ) {
        $template_file_name = $local_template_data['local_template'];
        $template_file_name_with_php_extension = $template_file_name . '.php';

        // Set the default template_path first
        $template_path = sek_get_templates_dir() . "/page-templates/{$template_file_name_with_php_extension}";
        // Make this filtrable
        // (this filter is used in Hueman theme to assign a specific template)
        $template_path = apply_filters( 'nimble_get_locale_template_path', $template_path, $template_file_name );

        // Use an override if any
        // Default page tmpl path looks like : NIMBLE_BASE_PATH . "/tmpl/page-template/nimble_template.php",
        $overriden_template_path = sek_maybe_get_overriden_local_template_path( array( 'file_name' => $template_file_name_with_php_extension, 'folder' => 'page-templates' ) );
        if ( !empty( $overriden_template_path ) ) {
            $template_path = $overriden_template_path;
        }

        if ( ! file_exists( $template_path ) ) {
            sek_error_log( __FUNCTION__ .' the custom template does not exist', $template_path );
            $template_path = null;
        }
    }
    return $template_path;
}



/* ------------------------------------------------------------------------- *
 *  HEADER FOOTER
/* ------------------------------------------------------------------------- */
// fired by sek_maybe_set_local_nimble_footer() @get_footer()
// fired by sek_maybe_set_local_nimble_header() @get_header()
function sek_page_uses_nimble_header_footer() {
    // cache the properties if not done yet
    Nimble_Manager()->sek_maybe_set_nimble_header_footer();
    return true === Nimble_Manager()->has_local_header_footer || true === Nimble_Manager()->has_global_header_footer;
}


// DEPRECATED SINCE Nimble v1.3.0, november 2018
// was used in the Hueman theme before version 3.4.9
function render_content_sections_for_nimble_template() {
    Nimble_Manager()->render_nimble_locations(
        array_keys( Nimble_Manager()->default_locations ),//array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
        array( 'fallback_location' => 'loop_start' )
    );
}

?><?php
// Recursively walk the level tree until a match is found
// @param id = the id of the level for which the model shall be returned
// @param $collection = sek_get_skoped_seks( $skope_id )['collection']; <= the root collection must always be provided, so we are sure it's
function sek_get_level_model( $id, $collection = array() ) {
    $_data = 'no_match';
    if ( ! is_array( $collection ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid collection param when getting model for id : ' . $id );
        return $_data;
    }
    foreach ( $collection as $level_data ) {
        // stop here and return if a match was recursively found
        if ( 'no_match' != $_data )
          break;
        if ( array_key_exists( 'id', $level_data ) && $id === $level_data['id'] ) {
            $_data = $level_data;
        } else {
            if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
                $_data = sek_get_level_model( $id, $level_data['collection'] );
            }
        }
    }
    return $_data;
}

// Recursive helper
// Typically used when ajaxing
// Is also used when building the dyn_css or when firing sek_add_css_rules_for_spacing()
// @param id : mandatory
// @param collection : optional <= that's why if missing we must walk all collections : local and global
function sek_get_parent_level_model( $child_level_id = '', $collection = array(), $skope_id = '' ) {
    $_parent_level_data = 'no_match';
    if ( ! is_string( $child_level_id ) || empty( $child_level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $_parent_level_data;
    }

    // When no collection is provided, we must walk all collections, local and global.
    if ( empty( $collection ) ) {
        if ( empty( $skope_id ) ) {
            if ( is_array( $_POST ) && ! empty( $_POST['location_skope_id'] ) ) {
                $skope_id = $_POST['location_skope_id'];
            } else {
                // When fired during an ajax 'customize_save' action, the skp_get_skope_id() is determined with $_POST['local_skope_id']
                // @see add_filter( 'skp_get_skope_id', '\Nimble\sek_filter_skp_get_skope_id', 10, 2 );
                $skope_id = skp_get_skope_id();
            }
        }
        if ( empty( $skope_id ) || '_skope_not_set_' === $skope_id ) {
            sek_error_log( __FUNCTION__ . ' => the skope_id should not be empty.');
        }
        $local_skope_settings = sek_get_skoped_seks( $skope_id );
        $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
        $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
        $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

        $collection = array_merge( $local_collection, $global_collection );
    }

    foreach ( $collection as $level_data ) {
        // stop here and return if a match was recursively found
        if ( 'no_match' !== $_parent_level_data )
          break;
        if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            foreach ( $level_data['collection'] as $child_level_data ) {
                if ( array_key_exists( 'id', $child_level_data ) && $child_level_id == $child_level_data['id'] ) {
                    $_parent_level_data = $level_data;
                    //match found, break this loop
                    break;
                } else {
                    $_parent_level_data = sek_get_parent_level_model( $child_level_id, $level_data['collection'], $skope_id );
                }
            }
        }
    }
    return $_parent_level_data;
}




// Return the skope id in which a level will be rendered
// For that, walk the collections local and global to see if there's a match
// Fallback skope is local.
// used for example in the simple form module to print the hidden skope id, needed on submission.
// Recursive helper
// @param id : mandatory
// @param collection : optional <= that's why if missing we must walk all collections : local and global
function sek_get_level_skope_id( $level_id = '' ) {
    $level_skope_id = skp_get_skope_id();
    if ( ! is_string( $level_id ) || empty( $level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $level_skope_id;
    }

    $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
    $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
    // if the level id has not been found in the local sections, we know it's a global level.
    // In dev mode, always make sure that the level id is found in the global locations.
    if ( 'no_match' === sek_get_level_model( $level_id, $local_collection ) ) {
        $level_skope_id = NIMBLE_GLOBAL_SKOPE_ID;
        if ( sek_is_dev_mode() ) {
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();
            if ( 'no_match' === sek_get_level_model( $level_id, $global_collection ) ) {
                sek_error_log( __FUNCTION__ . ' => warning, a level id ( ' . $level_id .' ) was not found in local and global sections.');
            }
        }
    }

    return $level_skope_id;
}

?><?php
// return bool
// count the number of global section created, no matter if they are header footer or other global locations
// can be used to determine if we need to render Nimble Builder assets on front. See ::sek_enqueue_front_assets()
function sek_has_global_sections() {
    if ( skp_is_customizing() )
      return true;
    $maybe_global_sek_post = sek_get_seks_post( NIMBLE_GLOBAL_SKOPE_ID, 'global' );
    $nb_section_created = 0;
    if ( is_object($maybe_global_sek_post) ) {
        $seks_data = maybe_unserialize($maybe_global_sek_post->post_content);
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        $nb_section_created = sek_count_not_empty_sections_in_page( $seks_data );
    }
    return $nb_section_created > 0;
}


// @return bool
// added for https://github.com/presscustomizr/nimble-builder/issues/436
// initially used to determine if a post or a page has been customized with Nimble Builder => if so, we add an edit link in the post/page list
// when used in admin, the skope_id must be provided
// can be used to determine if we need to render Nimble Builder assets on front. See ::sek_enqueue_front_assets()
function sek_local_skope_has_nimble_sections( $skope_id = '' ) {
    if ( empty( $skope_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing skope id' );
        return false;
    }
    $maybe_local_sek_post = sek_get_seks_post( $skope_id, 'local' );
    $nb_section_created = 0;
    if ( is_object($maybe_local_sek_post) ) {
        $seks_data = maybe_unserialize($maybe_local_sek_post->post_content);
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        $nb_section_created = sek_count_not_empty_sections_in_page( $seks_data );
    }
    return $nb_section_created > 0;
}





// @return boolean
// Indicates if a section level contains at least on module
// Used in SEK_Front_Render::render() to maybe print a css class on the section level
function sek_section_has_modules( $model, $has_module = null ) {
    $has_module = is_null( $has_module ) ? false : (bool)$has_module;
    foreach ( $model as $level_data ) {
        // stop here and return if a match was recursively found
        if ( true === $has_module )
          break;
        if ( is_array( $level_data ) && array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            foreach ( $level_data['collection'] as $child_level_data ) {
                if ( 'module'== $child_level_data['level'] ) {
                    $has_module = true;
                    //match found, break this loop
                    break;
                } else {
                    $has_module = sek_section_has_modules( $child_level_data, $has_module );
                }
            }
        }
    }
    return $has_module;
}



/* ------------------------------------------------------------------------- *
 *  HAS USER STARTED CREATING SECTIONS ?
/* ------------------------------------------------------------------------- */
// @return a boolean
// Used to check if we should render the welcome notice in sek_render_welcome_notice()
function sek_site_has_nimble_sections_created() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    //sek_error_log('DO WE HAVE SECTIONS ?', $query );
    return is_array( $query->posts ) && ! empty( $query->posts );
}




// recursive helper to count the number of sections in a given set of sections data
function sek_count_not_empty_sections_in_page( $seks_data, $count = 0 ) {
    if ( ! is_array( $seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid seks_data param');
        return $count;
    }
    foreach ( $seks_data as $key => $data ) {
        if ( is_array( $data ) ) {
            if ( !empty( $data['level'] ) && 'section' === $data['level'] ) {
                if ( !empty( $data['collection'] ) ) {
                    $count++;
                }
            } else {
                $count = sek_count_not_empty_sections_in_page( $data, $count );
            }
        }
    }
    return $count;
}

?><?php
// /* ------------------------------------------------------------------------- *
// *  FEEDBACK NOTIF
// /* ------------------------------------------------------------------------- */
// Invoked when generating the customizer localized js params 'sektionsLocalizedData'
function sek_get_feedback_notif_status() {
    if ( sek_feedback_notice_is_dismissed() )
      return;
    if ( sek_feedback_notice_is_postponed() )
      return;

    // Did we set the status already ?
    if ( 'not_set' !== Nimble_Manager()->feedback_notif_status )
      return Nimble_Manager()->feedback_notif_status;

    // If not let's set it

    $start_version = get_option( 'nimble_started_with_version', NIMBLE_VERSION );
    //sek_error_log('START VERSION ?' . $start_version, version_compare( $start_version, '1.6.0', '<=' ) );

    // Bail if user did not start before v1.10.10, February 15th 2020 ( set on March 3rd 2020 )
    if ( ! version_compare( $start_version, '1.10.10', '<=' ) )
      return;

    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( ! is_array( $query->posts ) || empty( $query->posts ) )
      return;

    $customized_pages = 0;
    $nb_section_created = 0;
    // the global var is easier to handle for array when populated recursively
    global $modules_used;
    $module_used = array();

    foreach ( $query->posts as $post_object ) {
        $seks_data = maybe_unserialize($post_object->post_content);
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        $nb_section_created += sek_count_not_empty_sections_in_page( $seks_data );
        sek_populate_list_of_modules_used( $seks_data );
        $customized_pages++;
    }

    if ( !is_array( $modules_used ) || !is_numeric( $nb_section_created ) || !is_numeric($customized_pages) )
      return;

    $modules_used = array_unique($modules_used);

    // sek_error_log('$section_created ??', $nb_section_created );
    // sek_error_log('$modules_used ?? ' . count($modules_used), $modules_used );
    // sek_error_log('$customized_pages ??', $customized_pages );
    //version_compare( $this->wp_version, '4.1', '>=' )
    Nimble_Manager()->feedback_notif_status = $customized_pages > 0 && $nb_section_created > 2 && count($modules_used) > 2;
    return Nimble_Manager()->feedback_notif_status;
}


// recursive helper to generate a list of module used in a given set of sections data
function sek_populate_list_of_modules_used( $seks_data ) {
    global $modules_used;
    if ( ! is_array( $seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid seks_data param');
        return $count;
    }
    foreach ( $seks_data as $key => $data ) {
        if ( is_array( $data ) ) {
            if ( !empty( $data['level'] ) && 'module' === $data['level'] && !empty( $data['module_type'] ) ) {
                $modules_used[] = $data['module_type'];
            } else {
                //$modules_used = array_merge( $modules_used, sek_populate_list_of_modules_used( $data, $modules_used ) );
                sek_populate_list_of_modules_used( $data, $modules_used );
            }
        }
    }
}


function sek_feedback_notice_is_dismissed() {
    $dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
    $dismissed_array = array_filter( explode( ',', (string) $dismissed ) );
    return in_array( NIMBLE_FEEDBACK_NOTICE_ID, $dismissed_array );
}

// @uses get_user_meta( get_current_user_id(), 'nimble_user_transients', true );
// populated in ajax class
function sek_feedback_notice_is_postponed() {
    return 'maybe_later' === get_transient( NIMBLE_FEEDBACK_NOTICE_ID );
}

?><?php
/* ------------------------------------------------------------------------- *
 *  reCAPTCHA HELPER
/* ------------------------------------------------------------------------- */
// @return boolean
// reCaptcha is enabled globally
// deactivated when customizing
function sek_is_recaptcha_globally_enabled() {
    if ( did_action('nimble_front_classes_ready') && '_not_cached_yet_' !== Nimble_Manager()->recaptcha_enabled ) {
        return Nimble_Manager()->recaptcha_enabled;
    }
    $recaptcha_enabled = false;

    $glob_recaptcha_opts = sek_get_global_option_value( 'recaptcha' );

    if ( !is_null( $glob_recaptcha_opts ) && is_array( $glob_recaptcha_opts ) && !empty( $glob_recaptcha_opts['enable'] ) ) {
        $recaptcha_enabled = sek_booleanize_checkbox_val( $glob_recaptcha_opts['enable'] ) && !empty( $glob_recaptcha_opts['public_key'] ) && !empty($glob_recaptcha_opts['private_key'] );
    }

    // CACHE when not doing ajax
    if ( !defined( 'DOING_AJAX') || true !== DOING_AJAX ) {
        Nimble_Manager()->recaptcha_enabled = $recaptcha_enabled;
    }

    return $recaptcha_enabled;
}

// @return boolean
// reCaptcha is enabled globally
// deactivated when customizing
function sek_is_recaptcha_badge_globally_displayed() {
    if ( did_action('nimble_front_classes_ready') && '_not_cached_yet_' !== Nimble_Manager()->recaptcha_badge_displayed ) {
        return Nimble_Manager()->recaptcha_badge_displayed;
    }
    $display_badge = false;//disabled by default @see sek_get_module_params_for_sek_global_recaptcha()

    $glob_recaptcha_opts = sek_get_global_option_value( 'recaptcha' );

    if ( !is_null( $glob_recaptcha_opts ) && is_array( $glob_recaptcha_opts ) && !empty( $glob_recaptcha_opts['badge'] ) ) {
        $display_badge = sek_booleanize_checkbox_val( $glob_recaptcha_opts['badge'] ) && sek_is_recaptcha_globally_enabled();
    }

    // CACHE when not doing ajax
    if ( !defined( 'DOING_AJAX') || true !== DOING_AJAX ) {
        Nimble_Manager()->recaptcha_badge_displayed = $display_badge;
    }

    return $display_badge;
}



// @return bool
// used to print reCaptcha js for the form module
function sek_front_sections_include_a_form( $bool = false, $recursive_data = null ) {
    if ( !$bool ) {
        if ( is_null( $recursive_data ) ) {
            $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
            $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

            $recursive_data = array_merge( $local_collection, $global_collection );
        }

        foreach ($recursive_data as $key => $value) {
            if ( is_array( $value ) && array_key_exists('module_type', $value) && 'czr_simple_form_module' === $value['module_type'] ) {
                $bool = true;
                break;
            } else if ( is_array( $value ) ) {
                $bool = sek_front_sections_include_a_form( $bool, $value );
            }
        }
    }
    return $bool;
}

?><?php

// Filter the local skope id when invoking skp_get_skope_id in a customize_save ajax action
add_filter( 'skp_get_skope_id', '\Nimble\sek_filter_skp_get_skope_id', 10, 2 );
function sek_filter_skp_get_skope_id( $skope_id, $level ) {
    // When ajaxing, @see the js callback on 'save-request-params', core hooks for the save query
    // api.bind('save-request-params', function( query ) {
    //       $.extend( query, { local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ) } );
    // });
    // implemented to fix : https://github.com/presscustomizr/nimble-builder/issues/242
    if ( 'local' === $level && is_array( $_POST ) && ! empty( $_POST['local_skope_id'] ) && 'customize_save' === $_POST['action'] ) {
        $skope_id = $_POST['local_skope_id'];
    }
    return $skope_id;
}

//@return string
function sek_get_seks_setting_id( $skope_id = '' ) {
  if ( empty( $skope_id ) ) {
      sek_error_log( __FUNCTION__ . ' => empty skope id or location => collection setting id impossible to build' );
  }
  return NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . "[{$skope_id}]";
}



// @return void()
/*function sek_get_module_placeholder( $placeholder_icon = 'short_text' ) {
  $placeholder_icon = empty( $placeholder_icon ) ? 'not_interested' : $placeholder_icon;
  ?>
    <div class="sek-module-placeholder">
      <i class="material-icons"><?php echo $placeholder_icon; ?></i>
    </div>
  <?php
}*/


/* ------------------------------------------------------------------------- *
 *  HELPER FOR CHECKBOX OPTIONS
/* ------------------------------------------------------------------------- */
function sek_is_checked( $val ) {
    //cast to string if array
    $val = is_array($val) ? $val[0] : $val;
    return sek_booleanize_checkbox_val( $val );
}

function sek_booleanize_checkbox_val( $val ) {
    if ( ! $val || is_array( $val ) ) {
      return false;
    }
    if ( is_bool( $val ) && $val )
      return true;
    switch ( (string) $val ) {
      case 'off':
      case '' :
      case 'false' :
        return false;
      case 'on':
      case '1' :
      case 'true' :
        return true;
      default : return false;
    }
}




/* ------------------------------------------------------------------------- *
 *  Nimble Widgets Areas
/* ------------------------------------------------------------------------- */
// @return the list of Nimble registered widget areas
function sek_get_registered_widget_areas() {
    global $wp_registered_sidebars;
    $widget_areas = array();
    if ( is_array( $wp_registered_sidebars ) && ! empty( $wp_registered_sidebars ) ) {
        foreach ( $wp_registered_sidebars as $registered_sb ) {
            $id = $registered_sb['id'];
            if ( ! sek_is_nimble_widget_id( $id ) )
              continue;
            $widget_areas[ $id ] = $registered_sb['name'];
        }
    }
    return $widget_areas;
}
// @return bool
// @ param $id string
function sek_is_nimble_widget_id( $id ) {
    // NIMBLE_WIDGET_PREFIX = nimble-widget-area-
    return NIMBLE_WIDGET_PREFIX === substr( $id, 0, strlen( NIMBLE_WIDGET_PREFIX ) );
}





/* ------------------------------------------------------------------------- *
 *  Beta Features
/* ------------------------------------------------------------------------- */
// December 2018 => preparation of the header / footer feature
// The beta features can be control by a constant
// and by a global option
function sek_are_beta_features_enabled() {
    $global_beta_feature = sek_get_global_option_value( 'beta_features');
    if ( is_array( $global_beta_feature ) && array_key_exists('beta-enabled', $global_beta_feature ) ) {
          return (bool)$global_beta_feature['beta-enabled'];
    }
    return NIMBLE_BETA_FEATURES_ENABLED;
}

/* ------------------------------------------------------------------------- *
 *  PRO
/* ------------------------------------------------------------------------- */
function sek_is_pro() {
    return sek_is_dev_mode();
}



/* ------------------------------------------------------------------------- *
 *  VERSION HELPERS
/* ------------------------------------------------------------------------- */
/**
* Returns a boolean
* check if user started to use the plugin before ( strictly < ) the requested version
* @param $_ver : string free version
*/
function sek_user_started_before_version( $requested_version ) {
    $started_with = get_option( 'nimble_started_with_version' );
    //the transient is set in HU_utils::hu_init_properties()
    if ( ! $started_with )
      return false;

    if ( ! is_string( $requested_version ) )
      return false;

    return version_compare( $started_with , $requested_version, '<' );
}



/* ------------------------------------------------------------------------- *
 *   VARIOUS HELPERS
/* ------------------------------------------------------------------------- */
function sek_text_truncate( $text, $max_text_length, $more, $strip_tags = true ) {
    if ( ! $text )
        return '';

    if ( $strip_tags )
        $text       = strip_tags( $text );

    if ( ! $max_text_length )
        return $text;

    $end_substr = $text_length = strlen( $text );
    if ( $text_length > $max_text_length ) {
        $text      .= ' ';
        $end_substr = strpos( $text, ' ' , $max_text_length);
        $end_substr = ( FALSE !== $end_substr ) ? $end_substr : $max_text_length;
        $text       = trim( substr( $text , 0 , $end_substr ) );
    }

    if ( $more && $end_substr < $text_length )
        return $text . ' ' .$more;

    return $text;
}


// @return a bool
// typically when
// previewing a changeset on front with a link generated in the publish menu of the customizer
// looking like : mysite.com/?customize_changeset_uuid=67862e7f-427c-4183-b3f7-62eb86f79899
// in this case the $_REQUEST super global, doesn't include a customize_messenger_channel paral
// added when fixing https://github.com/presscustomizr/nimble-builder/issues/351
function sek_is_customize_previewing_a_changeset_post() {
    return !( defined('DOING_AJAX') && DOING_AJAX ) && is_customize_preview() && !isset( $_REQUEST['customize_messenger_channel']);
}




// @return string theme name
// always return the parent theme name
function sek_get_parent_theme_slug() {
    $theme_slug = get_option( 'stylesheet' );
    // $_REQUEST['theme'] is set both in live preview and when we're customizing a non active theme
    $theme_slug = isset($_REQUEST['theme']) ? $_REQUEST['theme'] : $theme_slug; //old wp versions
    $theme_slug = isset($_REQUEST['customize_theme']) ? $_REQUEST['customize_theme'] : $theme_slug;

    //gets the theme name (or parent if child)
    $theme_data = wp_get_theme( $theme_slug );
    if ( $theme_data->parent() ) {
        $theme_slug = $theme_data->parent()->Name;
    }

    return sanitize_file_name( strtolower( $theme_slug ) );
}




function sek_error_log( $title, $content = null ) {
    if ( ! sek_is_dev_mode() )
      return;
    if ( is_null( $content ) ) {
        error_log( '<' . $title . '>' );
    } else {
        error_log( '<' . $title . '>' );
        error_log( print_r( $content, true ) );
        error_log( '</' . $title . '>' );
    }
}




// /* ------------------------------------------------------------------------- *
// *  HELPERS FOR ADMIN AND API TO DETERMINE / CHECK CURRENT THEME NAME
// /* ------------------------------------------------------------------------- */
// @return bool
function sek_is_presscustomizr_theme( $theme_name ) {
  $bool = false;
  if ( is_string( $theme_name ) ) {
    foreach ( ['customizr', 'hueman'] as $pc_theme ) {
      // handle the case when the theme name looks like customizr-4.1.29
      if ( !$bool && $pc_theme === substr( $theme_name, 0, strlen($pc_theme) ) ) {
          $bool = true;
      }
    }
  }
  return $bool;
}

// @return the theme name string, exact if customizr or hueman
function sek_maybe_get_presscustomizr_theme_name( $theme_name ) {
  if ( is_string( $theme_name ) ) {
    foreach ( ['customizr', 'hueman'] as $pc_theme ) {
      // handle the case when the theme name looks like customizr-4.1.29
      if ( $pc_theme === substr( $theme_name, 0, strlen($pc_theme) ) ) {
          $theme_name = $pc_theme;
      }
    }
  }
  return $theme_name;
}

// @return a string
function sek_get_th_start_ver( $theme_name ) {
  if ( !in_array( $theme_name, ['customizr', 'hueman'] ) )
    return '';
  $start_ver = '';
  switch( $theme_name ) {
      case 'customizr' :
          $start_ver = defined( 'CZR_USER_STARTED_USING_FREE_THEME' ) ? CZR_USER_STARTED_USING_FREE_THEME : '';
      break;
      case 'hueman' :
          $start_ver = get_transient( 'started_using_hueman' );
      break;
  }
  return $start_ver;
}

?><?php
// /* ------------------------------------------------------------------------- *
// *  NIMBLE API
// /* ------------------------------------------------------------------------- */
if ( !defined( "NIMBLE_SECTIONS_LIBRARY_OPT_NAME" ) ) { define( "NIMBLE_SECTIONS_LIBRARY_OPT_NAME", 'nimble_api_prebuilt_sections_data' ); }
if ( !defined( "NIMBLE_NEWS_OPT_NAME" ) ) { define( "NIMBLE_NEWS_OPT_NAME", 'nimble_api_news_data' ); }
// NIMBLE_DATA_API_URL_V2 SINCE MAY 21ST 2019
// after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
// DOES NOT RETURN THE DATA FOR PRESET SECTIONS
// if ( !defined( "NIMBLE_DATA_API_URL" ) ) { define( "NIMBLE_DATA_API_URL", 'https://api.nimblebuilder.com/wp-json/nimble/v1/cravan' ); }
if ( !defined( "NIMBLE_DATA_API_URL_V2" ) ) { define( "NIMBLE_DATA_API_URL_V2", 'https://api.nimblebuilder.com/wp-json/nimble/v2/cravan' ); }

// Nimble api returns a set of value structured as follow
// return array(
//     'timestamp' => time(),
//     'upgrade_notice' => array(),
//     'library' => array(
//         'sections' => array(
//             'registration_params' => sek_get_sections_registration_params(),
//             'json_collection' => sek_get_json_collection()
//         ),
//         'templates' => array()
//     ),
//     'latest_posts' => $post_data,
//     'cta' => array( 'started_before' => $go_pro_if_started_before, 'html' => $go_pro_html )
//     // 'testtest' => $_GET,
//     // 'testreferer' => $_SERVER => to get the
// );
// @return array|false Info data, or false.
function sek_get_nimble_api_data( $force_update = false ) {
    $api_data_transient_name = 'nimble_api_data_' . NIMBLE_VERSION;
    $info_data = get_transient( $api_data_transient_name );
    $theme_slug = sek_get_parent_theme_slug();
    $pc_theme_name = sek_maybe_get_presscustomizr_theme_name( $theme_slug );
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;
    if ( true === $force_update && sek_is_dev_mode() ) {
          sek_error_log('API is in force update mode');
    }

    // Refresh every 12 hours, unless force_update set to true
    if ( $force_update || false === $info_data ) {
        $timeout = ( $force_update ) ? 25 : 8;
        $response = wp_remote_get( NIMBLE_DATA_API_URL_V2, array(
          'timeout' => $timeout,
          'body' => [
            'api_version' => NIMBLE_VERSION,
            'site_lang' => get_bloginfo( 'language' ),
            'theme_name' => $pc_theme_name,
            'start_ver' => sek_get_th_start_ver( $pc_theme_name )
          ],
        ) );

        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            // HOUR_IN_SECONDS is a default WP constant
            set_transient( $api_data_transient_name, [], 2 * HOUR_IN_SECONDS );
            return false;
        }

        $info_data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $info_data ) || ! is_array( $info_data ) ) {
            set_transient( $api_data_transient_name, [], 2 * HOUR_IN_SECONDS );
            return false;
        }

        // on May 21st 2019 => back to the local data for preset sections
        // after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
        // if ( !empty( $info_data['library'] ) ) {
        //     if ( !empty( $info_data['library']['sections'] ) ) {
        //         update_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME, $info_data['library']['sections'], 'no' );
        //     }
        //     unset( $info_data['library'] );
        // }

        if ( isset( $info_data['latest_posts'] ) ) {
            update_option( NIMBLE_NEWS_OPT_NAME, $info_data['latest_posts'], 'no' );
            unset( $info_data['latest_posts'] );
        }

        set_transient( $api_data_transient_name, $info_data, 12 * HOUR_IN_SECONDS );
    }//if ( $force_update || false === $info_data ) {

    return $info_data;
}


//////////////////////////////////////////////////
/// SECTIONS DATA
function sek_get_sections_registration_params_api_data( $force_update = false ) {
    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update, theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['registration_params'] ) ) {
        sek_get_nimble_api_data( true );//<= true for "force_update"
        $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    }

    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['registration_params'] ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no section registration params' );
        return array();
    }
    return $sections_data['registration_params'];
}

function sek_get_preset_sections_api_data( $force_update = false ) {
    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update( upgrader_process_complete ), theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['json_collection'] ) ) {
        sek_get_nimble_api_data( true );//<= true for "force_update"
        $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    }

    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['json_collection'] ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no json_collection' );
        return array();
    }
    return $sections_data['json_collection'];
}


//////////////////////////////////////////////////
/// LATESTS POSTS
// @return array of posts
function sek_get_latest_posts_api_data( $force_update = false ) {
    sek_get_nimble_api_data( $force_update );
    $latest_posts = get_option( NIMBLE_NEWS_OPT_NAME );
    if ( empty( $latest_posts ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no latest_posts' );
        return array();
    }
    return $latest_posts;
}

// @return html string
function sek_start_msg_from_api( $theme_name, $force_update = false ) {
    $info_data = sek_get_nimble_api_data( $force_update );
    if ( !sek_is_presscustomizr_theme( $theme_name ) || !is_array( $info_data ) ) {
        return '';
    }
    $msg = '';
    $api_msg = isset( $info_data['start_msg'] ) ? $info_data['start_msg'] : null;

    if ( !is_null($api_msg) && is_string($api_msg) ) {
        $msg = $api_msg;
    }
    return $msg;
}

// Refresh the api data on plugin update and theme switch
add_action( 'after_switch_theme', '\Nimble\sek_refresh_nimble_api_data');
add_action( 'upgrader_process_complete', '\Nimble\sek_refresh_nimble_api_data');
function sek_refresh_nimble_api_data() {
    // Refresh data on theme switch
    // => so the posts and message are up to date
    sek_get_nimble_api_data(true);
}

?><?php
// This file has been introduced on May 21st 2019 => back to the local data
// after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445

/////////////////////////////////////////////////////////////
// REGISTRATION PARAMS FOR PRESET SECTIONS
// Store the params in transient, refreshed every hour
// @return array()
function sek_get_sections_registration_params( $force_update = false ) {
    $section_params_transient_name = 'section_params_transient_' . NIMBLE_VERSION;
    $registration_params = get_transient( $section_params_transient_name );
    // Refresh every 30 days, unless force_update set to true
    if ( $force_update || false === $registration_params ) {
        $registration_params = sek_get_raw_registration_params();
        set_transient( $section_params_transient_name, $registration_params, 30 * DAY_IN_SECONDS );
    }
    return $registration_params;
}

function sek_get_raw_registration_params() {
    return [
        'sek_intro_sec_picker_module' => [
            'module_title' => __('Sections for an introduction', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'intro_three',
                    'title' => __('1 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_three.jpg'
                ),
                array(
                    'content-id' => 'intro_one',
                    'title' => __('1 column, full-width background', 'text-domain' ),
                    'thumb' => 'intro_one.jpg'
                ),
                array(
                    'content-id' => 'intro_two',
                    'title' => __('2 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_two.jpg'
                )
            )
        ],
        'sek_features_sec_picker_module' => [
            'module_title' => __('Sections for services and features', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'features_one',
                    'title' => __('3 columns with icon and call to action', 'text-domain' ),
                    'thumb' => 'features_one.jpg',
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'features_two',
                    'title' => __('3 columns with icon', 'text-domain' ),
                    'thumb' => 'features_two.jpg',
                    //'height' => '188px'
                )
            )
        ],
        'sek_about_sec_picker_module' => [
            'module_title' => __('Contact-us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'about_one',
                    'title' => __('A simple about us section with 2 columns', 'text-domain' ),
                    'thumb' => 'about_one.jpg',
                    //'height' => '188px'
                )
            )
        ],
        'sek_contact_sec_picker_module' => [
            'module_title' => __('Contact-us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'contact_one',
                    'title' => __('A contact form and a Google map', 'text-domain' ),
                    'thumb' => 'contact_one.jpg',
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'contact_two',
                    'title' => __('A contact form with an image background', 'text-domain' ),
                    'thumb' => 'contact_two.jpg',
                    //'height' => '188px'
                )
            )
        ],
        'sek_column_layouts_sec_picker_module' => [
            'module_title' => __('Empty sections with columns layout', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'two_columns',
                    'title' => __('two columns layout', 'text-domain' ),
                    'thumb' => 'two_columns.jpg'
                ),
                array(
                    'content-id' => 'three_columns',
                    'title' => __('three columns layout', 'text-domain' ),
                    'thumb' => 'three_columns.jpg'
                ),
                array(
                    'content-id' => 'four_columns',
                    'title' => __('four columns layout', 'text-domain' ),
                    'thumb' => 'four_columns.jpg'
                ),
            )
        ],
        // pre-built sections for header and footer
        'sek_header_sec_picker_module' => [
            'module_title' => __('Header sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'header_one',
                    'title' => __('simple header with a logo on the left and a menu on the right', 'text-domain' ),
                    'thumb' => 'header_one.jpg',
                    'height' => '33px',
                    'section_type' => 'header'
                ),
                array(
                    'content-id' => 'header_two',
                    'title' => __('simple header with a logo on the right and a menu on the left', 'text-domain' ),
                    'thumb' => 'header_two.jpg',
                    'height' => '33px',
                    'section_type' => 'header'
                )
            )
        ],
        'sek_footer_sec_picker_module' => [
            'module_title' => __('Footer sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'footer_one',
                    'title' => __('simple footer with 3 columns and large bottom zone', 'text-domain' ),
                    'thumb' => 'footer_one.jpg',
                    'section_type' => 'footer'
                )
            )
        ]
    ];
}

/////////////////////////////////////////////////////////////
// JSON FOR PRESET SECTIONS
function sek_get_preset_section_collection_from_json( $force_update = false ) {
    $section_json_transient_name = 'section_json_transient_' . NIMBLE_VERSION;
    $json_collection = get_transient( $section_json_transient_name );
    // Refresh every 30 days, unless force_update set to true
    if ( $force_update || false === $json_collection ) {
        $json_raw = @file_get_contents( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        if ( $json_raw === false ) {
            $json_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        }

        $json_collection = json_decode( $json_raw, true );
        set_transient( $section_json_transient_name, $json_collection, 30 * DAY_IN_SECONDS );
    }
    return $json_collection;
}


// Maybe refresh data on
// - theme switch
// - nimble upgrade
// - nimble is loaded ( only when is_admin() ) <= This makes the loading of the customizer faster on the first load, because the transient is ready.
add_action( 'nimble_front_classes_ready', '\Nimble\sek_refresh_preset_sections_data');
add_action( 'after_switch_theme', '\Nimble\sek_refresh_preset_sections_data');
add_action( 'upgrader_process_complete', '\Nimble\sek_refresh_preset_sections_data');
function sek_refresh_preset_sections_data() {
    if ( 'nimble_front_classes_ready' === current_filter() && !is_admin() )
      return;
    if ( 'nimble_front_classes_ready' === current_filter() && defined( 'DOING_AJAX') && DOING_AJAX )
      return;

    // => so the posts and message are up to date
    sek_get_preset_section_collection_from_json(true);
    sek_get_sections_registration_params(true);
}

?><?php
add_action( 'admin_bar_menu', '\Nimble\sek_add_customize_link', 1000 );
function sek_add_customize_link() {
    global $wp_admin_bar;
    // Don't show for users who can't access the customizer
    if ( ! current_user_can( 'customize' ) )
      return;

    $return_customize_url = '';
    $customize_url = '';
    if ( is_admin() ) {
        if ( !is_admin_bar_showing() )
            return;

        $customize_url = sek_get_customize_url_when_is_admin();
    } else {
        global $wp_customize;
        // Don't show if the user cannot edit a given customize_changeset post currently being previewed.
        if ( is_customize_preview() && $wp_customize->changeset_post_id() && ! current_user_can( get_post_type_object( 'customize_changeset' )->cap->edit_post, $wp_customize->changeset_post_id() ) ) {
          return;
        }

        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if ( is_customize_preview() && $wp_customize->changeset_uuid() ) {
            $current_url = remove_query_arg( 'customize_changeset_uuid', $current_url );
        }

        $customize_url = add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() );
        if ( is_customize_preview() ) {
            $customize_url = add_query_arg( array( 'changeset_uuid' => $wp_customize->changeset_uuid() ), $customize_url );
        }
    }

    if ( empty( $customize_url ) )
      return;
    $customize_url = add_query_arg(
        array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
        $customize_url
    );

    $wp_admin_bar->add_menu( array(
      'id'     => 'nimble_customize',
      'title'  => sprintf( '<span class="sek-nimble-icon" title="%3$s"><img src="%1$s" alt="%2$s"/><span class="sek-nimble-admin-bar-title">%4$s</span></span>',
          NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION,
          __('Nimble Builder','text_domain_to_replace'),
          __('Add sections in live preview with Nimble Builder', 'text_domain'),
          __( 'Build with Nimble Builder', 'text_domain' )
      ),
      'href'   => $customize_url,
      'meta'   => array(
        'class' => 'hide-if-no-customize',
      ),
    ) );
}//sek_add_customize_link

// returns a customize link when is_admin() for posts and terms
// inspired from wp-includes/admin-bar.php#wp_admin_bar_edit_menu()
// @param $post is a post object
function sek_get_customize_url_when_is_admin( $post = null ) {
    global $tag, $user_id;
    $customize_url = '';
    $current_screen = get_current_screen();
    $post = is_null( $post ) ? get_post() : $post;

    // July 2019 => Don't display the admin button in post and pages, where we already have the edit button next to the post title
    // if ( 'post' == $current_screen->base
    //     && 'add' != $current_screen->action
    //     && ( $post_type_object = get_post_type_object( $post->post_type ) )
    //     && current_user_can( 'read_post', $post->ID )
    //     && ( $post_type_object->public )
    //     && ( $post_type_object->show_in_admin_bar ) )
    // {
    //     if ( 'draft' == $post->post_status ) {
    //         $preview_link = get_preview_post_link( $post );
    //         $customize_url = esc_url( $preview_link );
    //     } else {
    //         $customize_url = get_permalink( $post->ID );
    //     }
    // } else

    if ( 'edit' == $current_screen->base
        && ( $post_type_object = get_post_type_object( $current_screen->post_type ) )
        && ( $post_type_object->public )
        && ( $post_type_object->show_in_admin_bar )
        && ( get_post_type_archive_link( $post_type_object->name ) )
        && ! ( 'post' === $post_type_object->name && 'posts' === get_option( 'show_on_front' ) ) )
    {
        $customize_url = get_post_type_archive_link( $current_screen->post_type );
    } elseif ( 'term' == $current_screen->base
        && isset( $tag ) && is_object( $tag ) && ! is_wp_error( $tag )
        && ( $tax = get_taxonomy( $tag->taxonomy ) )
        && $tax->public )
    {
        $customize_url = get_term_link( $tag );
    } elseif ( 'user-edit' == $current_screen->base
        && isset( $user_id )
        && ( $user_object = get_userdata( $user_id ) )
        && $user_object->exists()
        && $view_link = get_author_posts_url( $user_object->ID ) )
    {
        $customize_url = $view_link;
    }

    if ( ! empty( $customize_url ) ) {
        $return_customize_url = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), wp_customize_url() );
        $customize_url = add_query_arg( 'url', urlencode( $customize_url ), $return_customize_url );
    }
    return $customize_url;
}

// introduced for https://github.com/presscustomizr/nimble-builder/issues/436
function sek_get_customize_url_for_post_id( $post_id, $return_url = '' ) {
    // Build customize_url
    // @see function sek_get_customize_url_when_is_admin()
    $customize_url = get_permalink( $post_id );
    $return_url = empty( $return_url ) ? $customize_url : $return_url;
    $return_customize_url = add_query_arg(
        'return',
        urlencode(
            remove_query_arg( wp_removable_query_args(), wp_unslash( $return_url ) )
        ),
        wp_customize_url()
    );
    $customize_url = add_query_arg( 'url', urlencode( $customize_url ), $return_customize_url );
    $customize_url = add_query_arg(
        array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
        $customize_url
    );

    return $customize_url;
}

?><?php
// fired @wp_loaded
// Note : if fired @plugins_loaded, invoking wp_update_post() generates php notices
function sek_maybe_do_version_mapping() {
    if ( ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) )
      return;
    //delete_option(NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS);
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    $global_options = is_array( $global_options ) ? $global_options : array();
    $global_options['retro_compat_mappings'] = isset( $global_options['retro_compat_mappings'] ) ? $global_options['retro_compat_mappings'] : array();

    // To 1_0_4 was introduced in december 2018
    // It's related to a modification of the skope_id when home is a static page
    if ( ! array_key_exists( 'to_1_4_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['to_1_4_0'] ) {
        $status_to_1_4_0 = sek_do_compat_to_1_4_0();
        //sek_error_log('$status_1_0_4_to_1_1_0 ' . $status_1_0_4_to_1_1_0, $global_options );
        $global_options['retro_compat_mappings']['to_1_4_0'] = 'done';
    }

    // 1_0_4_to_1_1_0 introduced in October 2018
    if ( ! array_key_exists( '1_0_4_to_1_1_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] ) {
        $status_1_0_4_to_1_1_0 = sek_do_compat_1_0_4_to_1_1_0();
        //sek_error_log('$status_1_0_4_to_1_1_0 ' . $status_1_0_4_to_1_1_0, $global_options );
        $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] = 'done';
    }
    update_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS, $global_options );
}

////////////////////////////////////////////////////////////////
// RETRO COMPAT => to 1.4.0
// It's related to a modification of the skope_id when home is a static page
// Was skp__post_page_home
// Now is skp__post_page_{$static_home_page_id}
// This was introduced to facilitate the compatibility of Nimble Builder with multilanguage plugins like polylang
// => Allows user to create a different home page for each languages
//
// If the current home page is not a static page, we don't have to do anything
// If not, the sections currently saved for skope skp__post_page_home, must be moved to skope skp__post_page_{$static_home_page_id}
// => this means that we need to update the post_id saved for option NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . 'skp__post_page_{$static_home_page_id}';
// to the value of the one saved for option NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . 'skp__post_page_home';
function sek_do_compat_to_1_4_0() {
    if ( 'page' === get_option( 'show_on_front' ) ) {
        $home_page_id = (int)get_option( 'page_on_front' );
        if ( 0 < $home_page_id ) {
            // get the post id storing the current sections on home
            // @see sek_get_seks_post()
            $current_option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . 'skp__post_page_home';
            $post_id_storing_home_page_sections = (int)get_option( $current_option_name );
            if ( $post_id_storing_home_page_sections > 0 ) {
                $new_option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . "skp__post_page_{$home_page_id}";
                update_option( $new_option_name, $post_id_storing_home_page_sections );
            }
        }
    }
}


////////////////////////////////////////////////////////////////
// RETRO COMPAT 1.0.4 to 1.1.0
// Introduced when upgrading from version 1.0.4 to version 1.1 +. October 2018.
// 1) Retro compat for image and tinymce module, turned multidimensional ( father - child logic ) since 1.1+
// 2) Ensure each level has a "ver_ini" property set to 1.0.4
function sek_do_compat_1_0_4_to_1_1_0() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( ! is_array( $query->posts ) || empty( $query->posts ) )
      return;

    $status = 'success';
    foreach ($query->posts as $post_object ) {
        if ( $post_object ) {
            $seks_data = maybe_unserialize( $post_object->post_content );
        }

        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        if ( empty( $seks_data ) )
          continue;
        $seks_data = sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $seks_data );
        $new_post_data = array(
            'ID'          => $post_object->ID,
            'post_title'  => $post_object->post_title,
            'post_name'   => sanitize_title( $post_object->post_title ),
            'post_type'   => NIMBLE_CPT,
            'post_status' => 'publish',
            'post_content' => maybe_serialize( $seks_data )
        );
        //sek_error_log('$new_post_data ??', $seks_data );
        $r = wp_update_post( wp_slash( $new_post_data ), true );
        if ( is_wp_error( $r ) ) {
            $status = 'error';
            sek_error_log( __FUNCTION__ . ' => error', $r );
        }
    }//foreach
    return $status;
}



// Recursive helper
// Sniff the modules that need a compatibility mapping
// do the mapping
// @return an updated sektions collection
function sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $seks_data ) {
    $new_seks_data = array();
    foreach ( $seks_data as $key => $value ) {
        // Set level ver_ini
        // If the ver_ini property is not set, it means the level has been created with the previous version of Nimble ( v1.0.4 )
        // Let's add it
        if ( is_array($value) && array_key_exists('level', $value) && ! array_key_exists('ver_ini', $value) ) {
            $value['ver_ini'] = '1.0.4';
        }
        $new_seks_data[$key] = $value;
        // LEVEL OPTIONS mapping
        // remove spacing
        // remove layout
        // copy all background related options ( bg-* ) from "bg_border" to "bg"
        // options => array(
        //    spacing => array(),
        //    height => array(),
        //    bg_border => array()
        // )
        if ( ! empty( $value ) && is_array( $value ) && 'options' === $key ) {
            // bail if the mapping has already been done
            if ( array_key_exists( 'bg', $value ) )
              continue;
            $new_seks_data[$key] = array();
            foreach( $value as $_opt_group => $_opt_group_data ) {
                if ( 'layout' === $_opt_group )
                  continue;
                if ( 'bg_border' === $_opt_group ) {
                    foreach ( $_opt_group_data as $input_id => $val ) {
                        if ( false !== strpos( $input_id , 'bg-' ) ) {
                            $new_seks_data[$key]['bg'][$input_id] = $val;
                        }
                    }
                }
                if ( 'spacing' === $_opt_group ) {
                    $new_seks_data[$key]['spacing'] = array( 'pad_marg' => sek_map_compat_1_0_4_to_1_1_0_do_level_spacing_mapping( $_opt_group_data ) );
                }
            }
        } // end of Level mapping
        // MODULE mapping
        else if ( is_array( $value ) && array_key_exists('module_type', $value ) ) {
            $new_seks_data[$key] = $value;
            // Assign a default value to the new_value in case we have no matching case
            $new_value = $value['value'];

            switch ( $value['module_type'] ) {
                case 'czr_image_module':
                    if ( is_array( $value['value'] ) ) {
                        // make sure we don't map twice
                        if ( array_key_exists( 'main_settings', $value['value'] ) || array_key_exists( 'borders_corners', $value['value'] ) )
                          break;
                        $new_value = array( 'main_settings' => array(), 'borders_corners' => array() );
                        foreach ( $value['value'] as $input_id => $input_data ) {
                            // make sure we don't map twice
                            if ( in_array( $input_id, array( 'main_settings', 'borders_corners' ) ) )
                              break;
                            switch ($input_id) {
                                case 'border-type':
                                case 'borders':
                                case 'border_radius_css':
                                    $new_value['borders_corners'][$input_id] = $input_data;
                                break;

                                default:
                                    $new_value['main_settings'][$input_id] = $input_data;
                                break;
                            }
                        }
                    }
                break;

                case 'czr_tiny_mce_editor_module':
                    if ( is_array( $value['value'] ) ) {
                        // make sure we don't map twice
                        if ( array_key_exists( 'main_settings', $value['value'] ) || array_key_exists( 'font_settings', $value['value'] ) )
                          break;
                        $new_value = array( 'main_settings' => array(), 'font_settings' => array() );
                        foreach ( $value['value'] as $input_id => $input_data ) {
                            // make sure we don't map twice
                            if ( in_array( $input_id, array( 'main_settings', 'font_settings' ) ) )
                              break;
                            switch ($input_id) {
                                case 'content':
                                case 'h_alignment_css':
                                    $new_value['main_settings'][$input_id] = $input_data;
                                break;

                                default:
                                    $new_value['font_settings'][$input_id] = $input_data;
                                break;
                            }
                        }
                    }
                break;
                default :
                    $new_value = $value['value'];
                break;
            }
            $new_seks_data[$key]['value'] = $new_value;
        } // End of module mapping
        // go recursive if possible
        else if ( is_array($value) ) {
            $new_seks_data[$key] = sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $value );
        }
    }
    return $new_seks_data;
}

// mapping from
// [spacing] => Array
// (
//     [desktop_pad_marg] => Array
//         (
//             [padding-top] => 20
//             [padding-bottom] => 20
//         )

//     [desktop_unit] => em
//     [tablet_pad_marg] => Array
//         (
//             [padding-left] => 30
//             [padding-right] => 30
//         )

//     [tablet_unit] => percent
// )
// to
// [spacing] => Array
// (
//     [pad_marg] => Array
//         (
//             [desktop] => Array
//                 (
//                     [padding-top] => 20
//                     [padding-bottom] => 20
//                     [unit] => em
//                 )

//             [tablet] => Array
//                 (
//                     [padding-right] => 30
//                     [padding-left] => 30
//                     [unit] => %
//                 )

//         )

// )
function sek_map_compat_1_0_4_to_1_1_0_do_level_spacing_mapping( $old_user_data ) {
    $old_data_structure = array(
        'desktop_pad_marg',
        'desktop_unit',
        'tablet_pad_marg',
        'tablet_unit',
        'mobile_pad_marg',
        'mobile_unit'
    );
    //sek_error_log('$old_user_data', $old_user_data);
    $mapped_data = array();
    foreach ( $old_data_structure as $old_key ) {
        if ( false !== strpos( $old_key , 'pad_marg' ) ) {
            $device = str_replace('_pad_marg', '', $old_key );
            if ( array_key_exists( $old_key, $old_user_data ) ) {
                $mapped_data[$device] = $old_user_data[$old_key];
            }
        }
        if ( false !== strpos( $old_key , 'unit' ) ) {
            $device = str_replace('_unit', '', $old_key );
            if ( array_key_exists( $old_key, $old_user_data ) ) {
                $mapped_data[$device] = is_array( $mapped_data[$device] ) ? $mapped_data[$device] : array();
                $mapped_data[$device]['unit'] = 'percent' === $old_user_data[$old_key] ? '%' : $old_user_data[$old_key];
            }
        }
    }
    return $mapped_data;
}
?><?php
// SEKTION POST
register_post_type( NIMBLE_CPT , array(
    'labels' => array(
      'name'          => __( 'Nimble sections', 'text_doma' ),
      'singular_name' => __( 'Nimble sections', 'text_doma' ),
    ),
    'public'           => false,
    'hierarchical'     => false,
    'rewrite'          => false,
    'query_var'        => false,
    'delete_with_user' => false,
    'can_export'       => true,
    '_builtin'         => true, /* internal use only. don't use this when registering your own post type. */
    'supports'         => array( 'title', 'revisions' ),
    'capabilities'     => array(
        'delete_posts'           => 'edit_theme_options',
        'delete_post'            => 'edit_theme_options',
        'delete_published_posts' => 'edit_theme_options',
        'delete_private_posts'   => 'edit_theme_options',
        'delete_others_posts'    => 'edit_theme_options',
        'edit_post'              => 'edit_theme_options',
        'edit_posts'             => 'edit_theme_options',
        'edit_others_posts'      => 'edit_theme_options',
        'edit_published_posts'   => 'edit_theme_options',
        'read_post'              => 'read',
        'read_private_posts'     => 'read',
        'publish_posts'          => 'edit_theme_options',
    )
));





/**
 * Fetch the `nimble_post_type` post for a given {skope_id}
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A theme object stylesheet name. Defaults to the current theme.
 * @return WP_Post|null The skope post or null if none exists.
 */
function sek_get_seks_post( $skope_id = '', $skope_level = 'local' ) {
    //sek_error_log('skope_id in sek_get_seks_post => ' . $skope_id );
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }

    $cached_seks_posts = Nimble_Manager()->seks_posts;
    if ( !is_array($cached_seks_posts) ) {
        sek_error_log( __FUNCTION__ .' => error => $cached_seks_posts must be an array' );
        $cached_seks_posts = array();
    }

    if ( !skp_is_customizing() && array_key_exists( $skope_id, $cached_seks_posts ) && !empty( $cached_seks_posts[$skope_id] ) ) {
        return $cached_seks_posts[$skope_id];
    }
    //sek_error_log('sek_get_seks_post => ' . $skope_id . ' skope level : ' . $skope_level );

    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );

    $post = null;

    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;

    $post_id = (int)get_option( $option_name );
    // if the options has not been set yet, it will return (int) 0
    // id #1 is already taken by the 'Hello World' post.
    if ( 1 > $post_id ) {
        //error_log( 'sek_get_seks_post => post_id is not valid for options => ' . $option_name );
        return;
    }

    if ( ! is_int( $post_id ) ) {
        error_log( 'sek_get_seks_post => post_id ! is_int() for options => ' . $option_name );
    }

    if ( is_int( $post_id ) && $post_id > 0 && get_post( $post_id ) ) {
        $post = get_post( $post_id );
    }

    // `-1` indicates no post exists; no query necessary.
    if ( ! $post && -1 !== $post_id ) {
        $query = new \WP_Query( $sek_post_query_vars );
        $post = $query->post;
        $post_id = $post ? $post->ID : -1;
        /*
         * Cache the lookup. See sek_update_sek_post().
         * @todo This should get cleared if a skope post is added/removed.
         */
        update_option( $option_name, (int)$post_id );
    }
    if ( !skp_is_customizing() ) {
        $cached_seks_posts[$skope_id] = $post;
        Nimble_Manager()->seks_posts = $cached_seks_posts;
        return $cached_seks_posts[$skope_id];
    } else {
        return $post;
    }
}


/**
 * Fetch the saved collection of sektion for a given skope_id / location
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A theme object stylesheet name. Defaults to the current theme.
 * @return array => the skope setting items
 */
function sek_get_skoped_seks( $skope_id = '', $location_id = '', $skope_level = 'local' ) {
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }
    $is_global_skope = NIMBLE_GLOBAL_SKOPE_ID === $skope_id;
    $is_cached = false;

    // use the cached value when available ( after did_action('wp') )
    if ( did_action('wp') ) {
        if ( !$is_global_skope && 'not_cached' != Nimble_Manager()->local_seks ) {
            $is_cached = true;
            $seks_data = Nimble_Manager()->local_seks;
        }
        if ( $is_global_skope && 'not_cached' != Nimble_Manager()->global_seks ) {
            $is_cached = true;
            $seks_data = Nimble_Manager()->global_seks;
        }
    }

    if ( ! $is_cached ) {
        $seks_data = array();
        $post = sek_get_seks_post( $skope_id, $is_global_skope ? 'global' : 'local' );
        if ( $post ) {
            $seks_data = maybe_unserialize( $post->post_content );
        }
        $seks_data = is_array( $seks_data ) ? $seks_data : array();

        // normalizes
        // [ 'collection' => [], 'local_options' => [] ];
        $default_collection = sek_get_default_location_model( $skope_id );
        $seks_data = wp_parse_args( $seks_data, $default_collection );

        // Maybe add missing registered locations
        $maybe_incomplete_locations = [];
        foreach( $seks_data['collection'] as $location_data ) {
            if ( !empty( $location_data['id'] ) ) {
                $maybe_incomplete_locations[] = $location_data['id'];
            }
        }

        foreach( sek_get_locations() as $loc_id => $params ) {
            if ( !in_array( $loc_id, $maybe_incomplete_locations ) ) {
                if ( ( sek_is_global_location( $loc_id ) && $is_global_skope ) || ( ! sek_is_global_location( $loc_id ) && ! $is_global_skope  ) ) {
                    $seks_data['collection'][] = wp_parse_args( [ 'id' => $loc_id ], Nimble_Manager()->default_location_model );
                }
            }
        }
        // cache now
        if ( $is_global_skope ) {
            Nimble_Manager()->global_seks = $seks_data;
        } else {
            Nimble_Manager()->local_seks = $seks_data;
        }

    }//end if

    // when customizing, let us filter the value with the 'customized' ones
    $seks_data = apply_filters(
        'sek_get_skoped_seks',
        $seks_data,
        $skope_id,
        $location_id
    );

    // sek_error_log( '<sek_get_skoped_seks() location => ' . $location .  array_key_exists( 'collection', $seks_data ), $seks_data );
    // if a location is specified, return specifically the sections of this location
    if ( array_key_exists( 'collection', $seks_data ) && ! empty( $location_id ) ) {
        if ( ! array_key_exists( $location_id, sek_get_locations() ) ) {
            error_log( __FUNCTION__ . ' Error => location ' . $location_id . ' is not registered in the available locations' );
        } else {
            $seks_data = sek_get_level_model( $location_id, $seks_data['collection'] );
        }
    }

    return 'no_match' === $seks_data ? Nimble_Manager()->default_location_model : $seks_data;
}



/**
 * Update the `nimble_post_type` post for a given "{$skope_id}"
 * Inserts a `nimble_post_type` post when one doesn't yet exist.
 *
 * @since 4.7.0
 *
 * }
 * @return WP_Post|WP_Error Post on success, error on failure.
 */
function sek_update_sek_post( $seks_data, $args = array() ) {
    $args = wp_parse_args( $args, array(
        'skope_id' => ''
    ) );

    if ( ! is_array( $seks_data ) ) {
        error_log( 'sek_update_sek_post => $seks_data is not an array' );
        return new \WP_Error( 'sek_update_sek_post => $seks_data is not an array');
    }

    $skope_id = $args['skope_id'];
    if ( empty( $skope_id ) ) {
        error_log( 'sek_update_sek_post => empty skope_id' );
        return new \WP_Error( 'sek_update_sek_post => empty skope_id');
    }

    $post_title = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;

    $post_data = array(
        'post_title' => $post_title,
        'post_name' => sanitize_title( $post_title ),
        'post_type' => NIMBLE_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $seks_data )
    );

    // Update post if it already exists, otherwise create a new one.
    $post = sek_get_seks_post( $skope_id, NIMBLE_GLOBAL_SKOPE_ID !== $skope_id ? 'local' : 'global' );

    if ( $post ) {
        $post_data['ID'] = $post->ID;
        $r = wp_update_post( wp_slash( $post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $post_data ), true );
        if ( ! is_wp_error( $r ) ) {
            $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
            $post_id = $r;//$r is the post ID

            update_option( $option_name, (int)$post_id );

            // Trigger creation of a revision. This should be removed once #30854 is resolved.
            if ( 0 === count( wp_get_post_revisions( $r ) ) ) {
                wp_save_post_revision( $r );
            }
        }
    }

    if ( is_wp_error( $r ) ) {
        return $r;
    }
    return get_post( $r );
}

?><?php
/* ------------------------------------------------------------------------- *
 *  SAVED TEMPLATES
/* ------------------------------------------------------------------------- */
// SAVED TEMPLATES POST TYPE
// CPT for template : 'nimble_template'
register_post_type( NIMBLE_TEMPLATE_CPT , array(
    'labels' => array(
      'name'          => __( 'Nimble templates', 'text_doma' ),
      'singular_name' => __( 'Nimble templates', 'text_doma' ),
    ),
    'public'           => false,
    'hierarchical'     => false,
    'rewrite'          => false,
    'query_var'        => false,
    'delete_with_user' => false,
    'can_export'       => true,
    '_builtin'         => true, /* internal use only. don't use this when registering your own post type. */
    'supports'         => array( 'title', 'revisions' ),
    'capabilities'     => array(
        'delete_posts'           => 'edit_theme_options',
        'delete_post'            => 'edit_theme_options',
        'delete_published_posts' => 'edit_theme_options',
        'delete_private_posts'   => 'edit_theme_options',
        'delete_others_posts'    => 'edit_theme_options',
        'edit_post'              => 'edit_theme_options',
        'edit_posts'             => 'edit_theme_options',
        'edit_others_posts'      => 'edit_theme_options',
        'edit_published_posts'   => 'edit_theme_options',
        'read_post'              => 'read',
        'read_private_posts'     => 'read',
        'publish_posts'          => 'edit_theme_options',
    )
));





/**
 * Fetch the 'nimble_template' post for a given post_name
 *
 * @return WP_Post|null
 */
function sek_get_saved_tmpl_post( $tmpl_post_name ) {
    // $sek_post_query_vars = array(
    //     'post_type'              => NIMBLE_CPT,
    //     'post_status'            => get_post_stati(),
    //     'name'                   => sanitize_title( $tmpl_post_name ),
    //     'posts_per_page'         => 1,
    //     'no_found_rows'          => true,
    //     'cache_results'          => true,
    //     'update_post_meta_cache' => false,
    //     'update_post_term_cache' => false,
    //     'lazy_load_term_meta'    => false,
    // );

    $post = null;
    $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
    $tmpl_data = array_key_exists( $saved_tmpl_id, $all_saved_seks ) ? $all_saved_seks[$saved_tmpl_id] : array();
    $post_id = array_key_exists( 'post_id', $tmpl_data ) ? $tmpl_data['post_id'] : -1;

    // if the options has not been set yet, it will return (int) 0
    if ( 0 > $post_id ) {
        //error_log( 'sek_get_seks_post => post_id is not valid for options => ' . $saved_tmpl_id );
        return;
    }

    if ( ! is_int( $post_id ) ) {
        error_log( __FUNCTION__ .' => post_id ! is_int() for options => ' . $saved_tmpl_id );
    }

    if ( is_int( $post_id ) && $post_id > 0 && get_post( $post_id ) ) {
        $post = get_post( $post_id );
    }

    // // `-1` indicates no post exists; no query necessary.
    // if ( ! $post && -1 !== $post_id ) {
    //     $query = new WP_Query( $sek_post_query_vars );
    //     $post = $query->post;
    //     $post_id = $post ? $post->ID : -1;
    //     /*
    //      * Cache the lookup. See sek_update_sek_post().
    //      * @todo This should get cleared if a skope post is added/removed.
    //      */
    //     update_option( $option_name, (int)$post_id );
    // }

    return $post;
}



// @return the saved template data collection
function sek_get_saved_template_data( $tmpl_post_name ) {
    $sek_post = sek_get_saved_template_post( $tmpl_post_name );
    $tmpl_data = array();
    if ( $sek_post ) {
        $tmpl_data_decoded = maybe_unserialize( $sek_post->post_content );
        // The section data are described as an array
        // array(
        //     'title' => '',
        //     'description' => '',
        //     'id' => '',
        //     'type' => 'content',//in the future will be used to differentiate header, content and footer sections
        //     'creation_date' => date("Y-m-d H:i:s"),
        //     'update_date' => '',
        //     'data' => array(),<= this is where we describe the columns and options
        //     'nimble_version' => NIMBLE_VERSION
        // )
        if ( is_array( $tmpl_data_decoded ) && ! empty( $tmpl_data_decoded['data'] ) && is_string( $tmpl_data_decoded['data'] ) ) {
            $tmpl_data = json_decode( wp_unslash( $tmpl_data_decoded['data'], true ) );
        }
    }
    return $tmpl_data;
}


// invoked on 'wp_ajax_sek_get_user_saved_templates'
// @return an unserialized array of all templates saved by user
function sek_get_all_saved_templates() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_TEMPLATE_CPT,
        'post_status'            => 'publish',
        //'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,

        'orderby' => 'modified',
        'order' => 'DESC'
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( ! is_array( $query->posts ) || empty( $query->posts ) )
      return;

    $collection = array();

    sek_error_log('QUERY ??', $query );

    foreach ( $query->posts as $post_object ) {
        $content = maybe_unserialize( $post_object->post_content );
        if ( !is_array($content) ) {
            continue;
        }
        // When updating a template, we only need to return title and description
        $collection[$post_object->post_name] = array(
            'title' => !empty($content['title']) ? $content['title'] : '',
            'description' => !empty($content['description']) ? $content['description'] : ''
        );
    }

    return $collection;
}



 // Update the 'nimble_template' post
 // Inserts a 'nimble_template' post when one doesn't yet exist.
 // $tmpl_data = array(
  //     'title' => $_POST['sek_tmpl_title'],
  //     'description' => $_POST['sek_tmpl_description'],
  //     'data' => $_POST['sek_tmpl_data']//<= json stringified
  // );
// @return WP_Post|WP_Error Post on success, error on failure.
function sek_update_saved_tmpl_post( $tmpl_data, $post_name_to_update = '' ) {
    if ( ! is_array( $tmpl_data ) ) {
        sek_error_log( __FUNCTION__ . ' => $tmpl_data is not an array' );
        return new \WP_Error( __FUNCTION__ . ' => $tmpl_data is not an array');
    }

    $tmpl_data = wp_parse_args( $tmpl_data, array(
        'title' => '',
        'description' => '',
        'data' => array(),
        'nimble_version' => NIMBLE_VERSION
    ));

    // $post_name_to_update will be used when user updates an existing template
    if ( !empty($post_name_to_update) ) {
        $tmpl_post_name = $post_name_to_update;
    } else {
        $tmpl_post_name = NIMBLE_PREFIX_FOR_SAVED_TMPL .  sanitize_title( $tmpl_data['title'] );
    }

    $post_data = array(
        'post_title' => esc_attr( $tmpl_data['title'] ),
        'post_name' => $tmpl_post_name,
        'post_type' => NIMBLE_TEMPLATE_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $tmpl_data )
    );

    // Update post if it already exists, otherwise create a new one.
    $post = sek_get_saved_tmpl_post( $tmpl_post_name );

    sek_error_log( __FUNCTION__ . ' => so $tmpl_data for skope ' . $tmpl_post_name, $tmpl_data );

    if ( $post ) {
        $post_data['ID'] = $post->ID;
        $r = wp_update_post( wp_slash( $post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $post_data ), true );
        if ( ! is_wp_error( $r ) ) {
            $post_id = $r;//$r is the post ID
            // Trigger creation of a revision. This should be removed once #30854 is resolved.
            if ( 0 === count( wp_get_post_revisions( $r ) ) ) {
                wp_save_post_revision( $r );
            }
        }
    }

    if ( is_wp_error( $r ) ) {
        return $r;
    }
    return get_post( $r );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  REVISION HELPERS
/* ------------------------------------------------------------------------- */
/**
 * Fetch the revisions of the `nimble_post_type` post for a given {skope_id}
 * @param string $skope_id optional
 * @return string $skope_level optional
 */
function sek_get_revision_history_from_posts( $skope_id = '', $skope_level = 'local' ) {
    //sek_error_log('skope_id in sek_get_seks_post => ' . $skope_id );
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }
    // We need a valid skope_id
    if ( defined('DOING_AJAX') && DOING_AJAX && '_skope_not_set_' === $skope_id ) {
          wp_send_json_error( __FUNCTION__ . ' => invalid skope id' );
    }
    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    $post_id = (int)get_option( $option_name );
    $raw_revision_history = array();
    if ( -1 !== $post_id ) {
        $args = array(
            'post_parent' => $post_id, // id
            'post_type' => 'revision',
            'post_status' => 'inherit'
        );
        $raw_revision_history = get_children($args);
    }
    $revision_history = array();
    if ( is_array( $raw_revision_history ) ) {
        foreach ($raw_revision_history as $post_id => $post_object ) {
            $revision_history[$post_id] = $post_object->post_date;
        }
    }
    return $revision_history;
}


/**
 * Fetch the revisions of the `nimble_post_type` post for a given revision post id
 * @param string $skope_id optional
 * @return string $skope_level optional
 */
function sek_get_single_post_revision( $post_id = null ) {

    // We need a valid post_id
    if ( defined('DOING_AJAX') && DOING_AJAX && ( is_null( $post_id ) || !is_numeric( (int)$post_id ) ) ) {
          wp_send_json_error( __FUNCTION__ . ' => invalid post id' );
    }
    $post = get_post( (int)$post_id );
    if ( is_wp_error( $post ) ) {
        wp_send_json_error( __FUNCTION__ . ' => post does not exist' );
        return;
    }
    return maybe_unserialize( $post->post_content );
}

?>