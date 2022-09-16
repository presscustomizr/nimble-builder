<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// @return bool
function sek_is_dev_mode() {
  return ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
}
// @return bool
// Nov 2020 : helper used to display NB CPT in admin
function sek_is_cpt_debug_mode() {
  return isset( $_GET['nimble_cpt_debug'] ) || (defined('NIMBLE_CPT_DEBUG_MODE') && NIMBLE_CPT_DEBUG_MODE);
}

if ( !defined( 'NIMBLE_CPT' ) ) { define( 'NIMBLE_CPT' , 'nimble_post_type' ); }
if ( !defined( 'NIMBLE_TEMPLATE_CPT' ) ) { define( 'NIMBLE_TEMPLATE_CPT' , 'nimble_template' ); }
if ( !defined( 'NIMBLE_SECTION_CPT' ) ) { define( 'NIMBLE_SECTION_CPT' , 'nimble_section' ); }

if ( !defined( 'NIMBLE_PREFIX_FOR_SAVED_TMPL' ) ) { define( 'NIMBLE_PREFIX_FOR_SAVED_TMPL' , 'nb_tmpl_' ); }
if ( !defined( 'NIMBLE_PREFIX_FOR_SAVED_SECTION' ) ) { define( 'NIMBLE_PREFIX_FOR_SAVED_SECTION' , 'nb_section_' ); }

if ( !defined( 'NIMBLE_DEPREC_ONE_CSS_FOLDER_NAME' ) ) { define( 'NIMBLE_DEPREC_ONE_CSS_FOLDER_NAME' , 'sek_css' ); }//<= folder name deprecated in july 2020
if ( !defined( 'NIMBLE_DEPREC_TWO_CSS_FOLDER_NAME' ) ) { define( 'NIMBLE_DEPREC_TWO_CSS_FOLDER_NAME' , 'nb_css' ); }//<= folder name deprecated in october 2020
if ( !defined( 'NIMBLE_CSS_FOLDER_NAME' ) ) { define( 'NIMBLE_CSS_FOLDER_NAME' , 'nimble_css' ); }
if ( !defined( 'NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS' ) ) { define( 'NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS' , 'nimble_module_css_read_status' ); }

if ( !defined( 'NIMBLE_OPT_SEKTION_POST_INDEX' ) ) { define( 'NIMBLE_OPT_SEKTION_POST_INDEX' , 'nimble_posts_index' ); }
if ( !defined( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'nimble___' ); }
if ( !defined( 'NIMBLE_GLOBAL_SKOPE_ID' ) ) { define( 'NIMBLE_GLOBAL_SKOPE_ID' , 'skp__global' ); }

if ( !defined( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' , 'nimble_global_opts' ); }// <= name updated in march 2021 was __nimble_options__

//if ( !defined( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' , 'nimble_saved_sektions' ); } //<= June 2020 to be removed
if ( !defined( 'NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS' , 'nimble_most_used_fonts' ); }
if ( !defined( 'NIMBLE_OPT_FOR_GLOBAL_CSS' ) ) { define( 'NIMBLE_OPT_FOR_GLOBAL_CSS' , 'nimble_global_css' ); }

if ( !defined( 'NIMBLE_OPT_NAME_FOR_SECTION_JSON' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SECTION_JSON' , 'nimble_prebuild_sections' ); }// <= name updated in march 2021, was nb_prebuild_section_json

if ( !defined( 'NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES' ) ) { define( 'NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES' , 'nb_backward_fixes' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING' , 'nb_shortcodes_parsed_in_czr' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE' ) ) { define( 'NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE' , 'nb_widgets_disabled_in_czr' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_DEBUG_MODE' ) ) { define( 'NIMBLE_OPT_NAME_FOR_DEBUG_MODE' , 'nb_debug_mode_active' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS' , 'nb_google_font_disable' ); }

if ( !defined( 'NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED' ) ) { define( 'NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED' , '__nimble__' ); }
if ( !defined( 'NIMBLE_WIDGET_PREFIX' ) ) { define( 'NIMBLE_WIDGET_PREFIX' , 'nimble-widget-area-' ); }
if ( !defined( 'NIMBLE_ASSETS_VERSION' ) ) { define( 'NIMBLE_ASSETS_VERSION', sek_is_dev_mode() ? time() : NIMBLE_VERSION ); }
if ( !defined( 'NIMBLE_MODULE_ICON_PATH' ) ) { define( 'NIMBLE_MODULE_ICON_PATH' , NIMBLE_BASE_URL . '/assets/czr/sek/icons/modules/' ); }
if ( !defined( 'NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID') ) { define( 'NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID' , 'czr-customize-content_editor' ); }

// TRANSIENTS ID
if ( !defined( 'NIMBLE_WELCOME_NOTICE_ID' ) ) { define ( 'NIMBLE_WELCOME_NOTICE_ID', 'nimble-welcome-notice-12-2018' ); }
//mt_rand(0, 65535) . 'test-nimble-feedback-notice-04-2019'
if ( !defined( 'NIMBLE_FEEDBACK_NOTICE_ID' ) ) { define ( 'NIMBLE_FEEDBACK_NOTICE_ID', 'nimble-feedback-notice-01-2022' ); }
if ( !defined( 'NIMBLE_FAWESOME_TRANSIENT_ID' ) ) { define ( 'NIMBLE_FAWESOME_TRANSIENT_ID', 'sek_font_awesome_october_2021' ); }
if ( !defined( 'NIMBLE_GFONTS_TRANSIENT_ID' ) ) { define ( 'NIMBLE_GFONTS_TRANSIENT_ID', 'sek_gfonts_march_2020' ); }
if ( !defined( 'NIMBLE_FEEDBACK_STATUS_TRANSIENT_ID' ) ) { define ( 'NIMBLE_FEEDBACK_STATUS_TRANSIENT_ID', 'nimble_feedback_status' ); }
if ( !defined( 'NIMBLE_API_CHECK_TRANSIENT_ID' ) ) { define ( 'NIMBLE_API_CHECK_TRANSIENT_ID', 'nimble_version_check_for_api' ); }


if ( !defined( 'NIMBLE_GOOGLE_FONTS_STYLESHEET_ID' ) ) { define ( 'NIMBLE_GOOGLE_FONTS_STYLESHEET_ID', 'sek-gfonts-local-and-global' ); }
if ( !defined( 'NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID' ) ) { define ( 'NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID', 'nimble-global-inline-style' ); }

if ( !defined( "NIMBLE_DATA_API_URL_V2" ) ) { define( "NIMBLE_DATA_API_URL_V2",
  ( defined('NIMBLE_FETCH_API_LOCALLY') && NIMBLE_FETCH_API_LOCALLY && defined('NIMBLE_LOCAL_API_URL') ) ? NIMBLE_LOCAL_API_URL : 'https://api.nimblebuilder.com/wp-json/nimble/v2/cravan'
); }
if ( !defined( 'NIMBLE_PRO_URL' ) ) { define ( 'NIMBLE_PRO_URL', 'https://presscustomizr.com/nimble-builder-pro' ); }

// @return bool
function sek_is_debug_mode() {
  return isset( $_GET['nimble_debug'] ) || sek_booleanize_checkbox_val( get_option( NIMBLE_OPT_NAME_FOR_DEBUG_MODE ) );
}

?><?php
/* ------------------------------------------------------------------------- *
 *  LOCATIONS UTILITIES
/* ------------------------------------------------------------------------- */
// @return array
function sek_get_locations() {
    if ( !is_array( Nimble_Manager()->registered_locations ) ) {
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

            if ( !sek_is_global_location( $loc_id ) ) {
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
    if ( !isset( $all_locations[$location_id] ) || !is_array( $all_locations[$location_id] ) ) {
        sek_error_log( __FUNCTION__ . ' error => the location ' . $location_id . ' is invalid or not registered.');
        return $default_property_val;
    }

    if ( empty( $property_name ) || !is_string( $property_name ) ) {
        sek_error_log( __FUNCTION__ . ' error => the requested property for location ' . $location_id . ' is invalid');
        return $default_property_val;
    }

    $location_params = wp_parse_args( $all_locations[$location_id], Nimble_Manager()->default_registered_location_model );
    return array_key_exists($property_name, $location_params) ? $location_params[$property_name] : $default_property_val;
}

// @return bool
function sek_is_global_location( $location_id ) {
    if ( !is_string( $location_id ) || empty( $location_id ) ) {
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
        // March 2021 property '__inherits_group_skope_tmpl_when_exists__' added for site templates #478
        $defaut_sektions_value = [ 'collection' => [], 'local_options' => [], 'fonts' => [], '__inherits_group_skope_tmpl_when_exists__' => true ];
    }
    foreach( sek_get_locations() as $location_id => $params ) {
        $is_global_location = sek_is_global_location( $location_id );
        if ( $is_global_skope && !$is_global_location )
          continue;
        if ( !$is_global_skope && $is_global_location )
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
function sek_load_front_assets_dynamically() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['load_assets_in_ajax'] ) ) {
        return !skp_is_customizing() && sek_booleanize_checkbox_val( $glob_perf['load_assets_in_ajax'] );
    }
    return false;
}


// Adds defer attribute to enqueued / registered scripts.
// fired @wp_enqueue_scripts
function sek_defer_script($handle) {
    // Adds defer attribute to enqueued / registered scripts.
    wp_script_add_data( $handle, 'defer', true );
}

// oct 2020 => introduction of a normalized way to emit a js event to NB front api
// in particular to make sure NB doesn't print a <script> twice to emit the same event
function sek_emit_js_event( $event = '', $echo = true ) {
    $emitted = Nimble_Manager()->emitted_js_event;
    if ( !is_string($event) || in_array($event, $emitted) )
      return;
    $emitted[] = $event;
    Nimble_Manager()->emitted_js_event = $emitted;
    
    if ( $echo ) {
        $html = sprintf('(function(){if(window.nb_){nb_.emit("%1$s");}})();', $event );
        wp_register_script( 'nb_emit_' . $event, '');
        wp_enqueue_script( 'nb_emit_' . $event );
        wp_add_inline_script( 'nb_emit_' . $event, $html );
    } else {
        $html = sprintf('<script>(function(){if(window.nb_){nb_.emit("%1$s");}})();</script>', $event );
        return $html;
    }
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
    } else if ( false !== strpos( $matches[0], 'data-sek-src' ) || preg_match('/ data-skip-lazyload *= *"true" */', $matches[0]) ) {
      return $matches[0];
    // otherwise go ahead and parse
    } else {
      return apply_filters( 'nimble_img_smartloaded',
        // june 2020 : the spaces before strings to replace ensure we don't replace attributes that include "srcset" or "sizes"
        // which can happen if another plugin has already added lazy load attributes
        // see https://github.com/presscustomizr/nimble-builder/issues/723
        str_replace( array(' srcset=', ' sizes='), array(' data-sek-srcset=', ' data-sek-sizes='),
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
    // condition added in april 2020 when implementing yoast compat https://github.com/presscustomizr/nimble-builder/issues/657
    if ( is_admin() && !skp_is_customizing() )
      return false;

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
        if ( !empty( $local_performances_data['local-img-smart-load'] ) && 'inherit' !== $local_performances_data['local-img-smart-load'] ) {
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

/* ------------------------------------------------------------------------- *
 *  DISABLE SUPPORT FOR BROWSER NATIVE LAZY LOADING
/* ------------------------------------------------------------------------- */
// Disabled when rendering NB content only
// attribute loading="lazy" was introduced in WP 5.5, Oct 2020
// => see why NB disable it here :https://github.com/presscustomizr/nimble-builder/issues/747
add_filter( 'wp_lazy_loading_enabled', function($default) {
    return Nimble_Manager()->rendering ? false : $default;
});




/* ------------------------------------------------------------------------- *
*  IMPORT IMAGE IF NOT ALREADY IN MEDIA LIB
/* ------------------------------------------------------------------------- */
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
        sek_error_log( __FUNCTION__ . ' error when firing download_url() for image : ' . $img_url );
        return $file_array['tmp_name'];
    }

    // Do the validation and storage stuff.
    $id = media_handle_sideload( $file_array, 0 );

    // If error storing permanently, unlink.
    if ( is_wp_error( $id ) ) {
        sek_error_log( __FUNCTION__ . ' error when firing media_handle_sideload() for image : ' . $img_url );
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




// IMPORT IMG HELPER
// recursive
//add_filter( 'nimble_pre_import', '\Nimble\sek_maybe_import_imgs' );
function sek_maybe_import_imgs( $seks_data, $do_import_images = true ) {
    $new_seks_data = array();
    // Reset img_import_errors
    Nimble_Manager()->img_import_errors = [];
    foreach ( $seks_data as $key => $value ) {
        if ( is_array($value) ) {
            $new_seks_data[$key] = sek_maybe_import_imgs( $value, $do_import_images );
        } else {
            if ( is_string( $value ) && false !== strpos( $value, '__img_url__' ) && sek_is_img_url( $value ) ) {
                $url = str_replace( '__img_url__', '', $value );
                // april 2020 : new option to skip importing images
                // introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                if ( !$do_import_images ) {
                    $value = $url;
                } else {
                    //sek_error_log( __FUNCTION__ . ' URL?', $url );
                    $id = sek_sideload_img_and_return_attachment_id( $url );
                    if ( is_wp_error( $id ) ) {
                        $value = null;
                        $img_errors = Nimble_Manager()->img_import_errors;
                        $img_errors[] = $url;
                        Nimble_Manager()->img_import_errors = $img_errors;
                    } else {
                        $value = $id;
                    }
                }
            } else if ( is_string( $value ) && false !== strpos( $value, '__default_img_medium__' ) ) {
                $value = NIMBLE_BASE_URL . '/assets/img/default-img.png';
            }
            $new_seks_data[$key] = $value;
        }
    }
    return $new_seks_data;
}

// @return bool
function sek_is_img_url( $url = '' ) {
    if ( is_string( $url ) ) {
      if ( preg_match( '/\.(jpg|jpeg|png|gif)/i', $url ) ) {
        return true;
      }
    }
    return false;
}








/* ------------------------------------------------------------------------- *
*  REMOVE IMAGE STYLE ATTRIBUTE
*  Used in image module, slider module, special image module
/* ------------------------------------------------------------------------- */
// Nov 2020 : removes any additional styles added by a theme ( Twenty Twenty one ) or a plugin to the image
// because otherwise it overrides NB builder custom width, height, and custom width when scrolling ( NB Pro )
// hook : 'wp_get_attachment_image_attributes'
function sek_remove_image_style_attr( $attr ) {
    if ( is_array($attr) && isset($attr['style']) ) {
        unset($attr['style']);
    }
    return $attr;
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
    if ( !empty($args['show_home']) ) {
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
      echo wp_kses_post($menu);
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
    if ( !empty( $pages ) ) {
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
        echo wp_kses_post($html);
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
    // if ( !skp_is_customizing() )
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
          'title' => __( 'Rich Text Editor', 'text_doma' ),
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
            'content-id' => 'czr_gallery_module',
            'title' => __( 'Image gallery', 'text_doma' ),
            'icon' => 'Nimble_gallery_icon.svg'
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
          'content-id' => 'czr_special_img_module',
          'title' => __( 'Nimble Image', 'text_doma' ),
          'icon' => 'Nimble_img_icon.svg',
          'is_pro' => !sek_is_pro(),
          'active' => sek_is_pro()
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
            'content-id' => 'czr_advanced_list_module',
            'title' => __( 'Advanced List', 'text_doma' ),
            'icon' => 'Nimble__advanced_list_icon.svg',
            'is_pro' => !sek_is_pro(),
            'active' => sek_is_pro()
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
          'font_icon' => '<i class="fab fa-wordpress-simple"></i>',
          'active' => !sek_is_widget_module_disabled()
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
    ));
}




// September 2020 : filter the collection of modules
// Removes pro upsell modules if sek_is_upsell_enabled() is false
// filter declared in inc/sektions/_front_dev_php/_constants_and_helper_functions/0_0_5_modules_helpers.php
add_filter('sek_get_module_collection', function( $collection ) {
    if ( sek_is_upsell_enabled() )
      return $collection;

    $filtered = [];
    foreach ($collection as $mod => $mod_data) {
        if ( array_key_exists('is_pro', $mod_data) && $mod_data['is_pro'] )
          continue;
        $filtered[] = $mod_data;
    }
    return $filtered;
});


// @return void()
// Fired in 'wp_enqueue_scripts'
// Recursively sniff the local and global sections to populate Nimble_Manager()->contextually_active_modules
// introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_populate_collection_of_contextually_active_modules( $skope_id = '', $recursive_data = null, $module_collection = null ) {

    $skope_id = empty( $skope_id ) ? skp_get_skope_id() : $skope_id;

    if ( is_null( $recursive_data ) ) {
        $local_skope_settings = sek_get_skoped_seks( $skope_id );
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
            $module_collection = sek_populate_collection_of_contextually_active_modules( $skope_id, $value, $module_collection);
        }
    }
    Nimble_Manager()->contextually_active_modules = $module_collection;
    return Nimble_Manager()->contextually_active_modules;
}

// return the cached collection or build it when needed
function sek_get_collection_of_contextually_active_modules( $skope_id = '' ) {
    $skope_id = empty( $skope_id ) ? skp_get_skope_id() : $skope_id;
    if ( 'not_set' === Nimble_Manager()->contextually_active_modules ) {
        return sek_populate_collection_of_contextually_active_modules( $skope_id );
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
    if ( !array_key_exists( $module_type, $registered_modules ) ) {
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
    if ( !is_string( $input_id ) ) {
        sek_error_log( __FUNCTION__ . ' => error => the $input_id param should be a string', $module_model);
        return;
    }
    if ( !is_array( $module_model ) ) {
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
    if ( !empty( $default_models[ $module_type ] ) ) {
        $default = $default_models[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base()->registered_modules;
        if ( !array( $registered_modules ) || !CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $default;
        }

        // Is this module a father ?
        if ( !empty( $registered_modules[ $module_type ]['is_father'] ) && true === $registered_modules[ $module_type ]['is_father'] ) {
            if ( empty( $registered_modules[ $module_type ][ 'children' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
                return $default;
            }
            if ( !is_array( $registered_modules[ $module_type ][ 'children' ] ) ) {
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
    if ( !empty( $cached_input_lists[ $module_type ] ) ) {
        $input_list = $cached_input_lists[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base()->registered_modules;
        // sek_error_log( __FUNCTION__ . ' => registered_modules', $registered_modules );
        if ( sek_is_dev_mode() && !array( $registered_modules ) || !array_key_exists( $module_type, $registered_modules ) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $input_list;
        }


        // Is this module a father ?
        if ( !empty( $registered_modules[ $module_type ]['is_father'] ) && true === $registered_modules[ $module_type ]['is_father'] ) {
            if ( empty( $registered_modules[ $module_type ][ 'children' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
                return $input_list;
            }
            if ( !is_array( $registered_modules[ $module_type ][ 'children' ] ) ) {
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

    $raw_module_value = ( !empty( $raw_module_model['value'] ) && is_array( $raw_module_model['value'] ) ) ? $raw_module_model['value'] : array();

    // reset the model value and rewrite it normalized with the defaults
    $normalized_model['value'] = array();
    if ( $is_father ) {
        $children = sek_get_registered_module_type_property( $module_type, 'children' );
        if ( empty( $children ) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
            return $default;
        }
        if ( !is_array( $children ) ) {
            sek_error_log( __FUNCTION__ . ' => ' . $module_type . ' children modules should be an array' );
            return $default;
        }
        foreach ( $children as $opt_group => $child_mod_type ) {
            $children_value = ( !empty( $raw_module_value[$opt_group] ) && is_array( $raw_module_value[$opt_group] ) ) ? $raw_module_value[$opt_group] : array();
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


// Returns an array of allowed HTML tags and attributes when securing form echoed with wp_kses()
function sek_get_allowed_html_in_forms() {
    $allowed = array(
        'div' => array(),
        'span' => array(),
        'form' => array(
            'action' => true,
            'method' => true,
            'post' => true
        ),
        'button' => array(
            'disabled' => true,
            'name' => true,
            'type' => true,
            'value' => true,
        ),
        'input' => array(
            'alt' => true,
            'capture' => true,
            'checked' => true,
            'disabled' => true,
            'list' => true,
            'name' => true,
            'placeholder' => true,
            'readonly' => true,
            'type' => true,
            'value' => true,
        ),
        'label' => array(
            'for' => true,
        ),
        'textarea' => array(
            'cols' => true,
            'disabled' => true,
            'maxlength' => true,
            'minlength' => true,
            'name' => true,
            'placeholder' => true,
            'readonly' => true,
            'rows' => true,
            'spellcheck' => true,
            'wrap' => true,
        ),
    );

    $allowed = array_map(
        function ($to_map) {
            $attr = array(
                'aria-checked' => true,
                'aria-describedby' => true,
                'aria-details' => true,
                'aria-disabled' => true,
                'aria-hidden' => true,
                'aria-invalid' => true,
                'aria-label' => true,
                'aria-labelledby' => true,
                'aria-live' => true,
                'aria-relevant' => true,
                'aria-required' => true,
                'aria-selected' => true,
                'class' => true,
                'data-*' => true,
                'id' => true,
                'inputmode' => true,
                'role' => true,
                'style' => true,
                'tabindex' => true,
                'title' => true,
            );

            return array_merge( $attr, (array) $to_map );
        },
        $allowed
    );
    return $allowed;
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

    if ( !is_array( $section ) )
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
    if ( !is_array( $section_breakpoint_options ) || empty( $section_breakpoint_options ) )
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

    if ( !is_string( $searched_level_id ) || empty( $searched_level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $last_section_breakpoint_found;;
    }
    if ( $searched_level_id_found ) {
        return $last_section_breakpoint_found;
    }

    // When no collection is provided, we must walk all collections, local and global.
    if ( 'not_set' === $collection  ) {
        if ( empty( $skope_id ) ) {
            if ( is_array( $_POST ) && !empty( $_POST['location_skope_id'] ) ) {
                $skope_id = sanitize_text_field($_POST['location_skope_id']);
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
        }

        if ( array_key_exists( 'id', $level_data ) && $searched_level_id == $level_data['id'] ) {
            //match found, break this loop
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
        $localSkopeNimble = sek_get_skoped_seks( $skope_id );
        $local_options = ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['local_options'] ) && is_array( $localSkopeNimble['local_options'] ) ) ? $localSkopeNimble['local_options'] : array();
        // Cache only after 'wp' && 'nimble_front_classes_ready'
        // never cache when doing ajax
        if ( did_action('nimble_front_classes_ready') && did_action('wp') && !defined('DOING_AJAX') )  {
            Nimble_Manager()->local_options = $local_options;
        }
    }
    // maybe normalizes with default values
    $values = ( !empty( $local_options ) && !empty( $local_options[ $option_name ] ) ) ? $local_options[ $option_name ] : null;
    if ( did_action('nimble_front_classes_ready') ) {
        $values = sek_normalize_local_options_with_defaults( $option_name, $values );
    }
    return $values;
}

// Introduced for site templates, when using function sek_is_inheritance_locally_disabled()
// needed because we can't rely on sek_get_skoped_seks() to get current local sections data, because this function returns the inherited data
// @param $option_name = string
// 'nimble_front_classes_ready' is fired when Nimble_Manager() is instanciated
function sek_get_local_option_value_without_inheritance( $option_name = '', $skope_id = null ) {
    if ( empty($option_name) ) {
        sek_error_log( __FUNCTION__ . ' => invalid option name' );
        return array();
    }
    if ( !skp_is_customizing() && did_action('nimble_front_classes_ready') && '_not_cached_yet_' !== Nimble_Manager()->local_options_without_tmpl_inheritance ) {
        $local_options_without_tmpl_inheritance = Nimble_Manager()->local_options_without_tmpl_inheritance;
    } else {
        // use the provided skope_id if in the signature
        $skope_id = ( !empty( $skope_id ) && is_string( $skope_id ))? $skope_id : skp_get_skope_id();
        $localSkopeNimble = sek_get_seks_without_group_inheritance( $skope_id );
        if ( skp_is_customizing() && NIMBLE_GLOBAL_SKOPE_ID != $skope_id ) {
            // when customizing, let us filter the value with the 'customized' ones
            $localSkopeNimble = apply_filters(
                'sek_get_skoped_seks',
                $localSkopeNimble,
                $skope_id,
                ''
            );
        }

        $local_options_without_tmpl_inheritance = ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['local_options'] ) && is_array( $localSkopeNimble['local_options'] ) ) ? $localSkopeNimble['local_options'] : array();
        // Cache only after 'wp' && 'nimble_front_classes_ready'
        // never cache when doing ajax
        if ( did_action('nimble_front_classes_ready') && did_action('wp') && !defined('DOING_AJAX') )  {
            Nimble_Manager()->local_options_without_tmpl_inheritance = $local_options_without_tmpl_inheritance;
        }
    }
    // maybe normalizes with default values
    $values = ( !empty( $local_options_without_tmpl_inheritance ) && !empty( $local_options_without_tmpl_inheritance[ $option_name ] ) ) ? $local_options_without_tmpl_inheritance[ $option_name ] : null;
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








// // @see _1_6_5_sektions_generate_UI_site_tmpl_options.js
// // @return array() $normalized_values
// function sek_normalize_site_tmpl_options_with_defaults( $option_name, $raw_module_values ) {
//     if ( empty($option_name) ) {
//         sek_error_log( __FUNCTION__ . ' => invalid option name' );
//         return array();
//     }
//     $normalized_values = ( !empty($raw_module_values) && is_array( $raw_module_values ) ) ? $raw_module_values : array();
//     // map the option key as saved in db ( @see _1_6_5_sektions_generate_UI_global_options.js ) and the module type
//     $site_tmpl_options_map = SEK_Front_Construct::$site_tmpl_options_map;

//     //sek_error_log('SEK_Front_Construct::$global_options_map', SEK_Front_Construct::$global_options_map );

//     if ( !array_key_exists($option_name, $site_tmpl_options_map) ) {
//         sek_error_log( __FUNCTION__ . ' => invalid option name', $option_name );
//         return $raw_module_values;
//     } else {
//         $module_type = $site_tmpl_options_map[$option_name];
//     }

//     // normalize with the defaults
//     // class_exists check introduced since https://github.com/presscustomizr/nimble-builder/issues/432
//     // may not be mandatory
//     if ( class_exists('\Nimble\CZR_Fmk_Base') ) {
//         if( CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
//             $normalized_values = _sek_normalize_single_module_values( $normalized_values, $module_type );
//         }
//     } else {
//         sek_error_log( __FUNCTION__ . ' => error => CZR_Fmk_Base not loaded' );
//     }
//     return $normalized_values;
// }


?><?php


/* ------------------------------------------------------------------------- *
 *  Dynamic variables parsing
/* ------------------------------------------------------------------------- */
function sek_find_pattern_match($matches) {
    $replace_values = apply_filters( 'sek_template_tags', array(
      'home_url' => 'home_url',
      'year_now' => date("Y"),
      'site_title' => 'get_bloginfo',
      'the_title' => 'sek_get_the_title',
      'the_archive_title' => 'sek_get_the_archive_title',// works for authors, CPT, taxonomies
      'the_archive_description' => 'sek_get_the_archive_description',// works for authors, CPT, taxonomies
      'the_content' => 'sek_get_the_content',
      'the_tags' => 'sek_get_the_tags',
      'the_categories' => 'sek_get_the_categories',

      'the_author_link' => 'sek_get_the_author_link',
      'the_author_name' => 'sek_get_the_author_name',
      'the_author_avatar' => 'sek_get_the_author_avatar',
      'the_author_bio' => 'sek_get_the_author_bio',

      'the_published_date' => 'sek_get_the_published_date',
      'the_modified_date' => 'sek_get_the_modified_date',
      'the_comments' => 'sek_get_the_comments',
      'the_previous_post_link' => 'sek_get_previous_post_link',
      'the_next_post_link' => 'sek_get_next_post_link',
      'the_comment_number' => 'sek_get_the_comment_number',

      'the_search_query' => 'sek_get_search_query',
      'the_search_results_number' => 'sek_get_search_results_nb'
    ));

    // Are we good after the filter ?
    if ( !is_array($replace_values) )
      return;

    //sek_error_log('$matches ??', $matches );
    if ( !is_array($matches) || empty($matches[1]) )
      return;

    //$data = html_entity_decode($matches[1], ENT_QUOTES, get_bloginfo( 'charset' ) );
    $data = explode(' ', $matches[1] );

    // Filter so that {{the_categories sep="/"}} becomes array('the_categories', 'sep="/"' ) with no empty entries
    // => the first entry is the template tag name, the other entries are the callback arguments ( to implement April 2021 )
    $new_data = array_filter($data, function($value) {
        if ( !is_string($value) )
          return false;
      $value = ltrim($value);
      return !is_null($value) && !empty($value) && preg_match("/[a-z]/i", $value) ;
    });

    if ( isset($new_data[0]) && array_key_exists( $new_data[0], $replace_values ) ) {
      // @todo => authorize arguments passed as an array
      $dyn_content = $replace_values[$new_data[0]];
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
    //return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(\w+)\s?\}\}!', '\Nimble\sek_find_pattern_match', $val) : $val;
    return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(.*?)\s?\}\}!', '\Nimble\sek_find_pattern_match', $val) : $val;
}
add_filter( 'nimble_parse_template_tags', '\Nimble\sek_parse_template_tags' );




// CALLBACKS WHEN IS_ARCHIVE()
function sek_get_the_archive_title() {
  $is_archive = is_archive();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_archive = sek_get_posted_query_param_when_customizing( 'is_archive' );
  }
  if ( !$is_archive ) {
    return sek_get_tmpl_tag_error( $tag = 'the_archive_title', $msg = __('It can be used in archive pages only.', 'text_doma') );
  }

  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_archive_title' );
  } else {
    add_filter('get_the_archive_title_prefix', '__return_false');
    $title = get_the_archive_title();
    remove_filter('get_the_archive_title_prefix', '__return_false');
  }
  return $title;
}

function sek_get_the_archive_description() {
  $is_archive = is_archive();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_archive = sek_get_posted_query_param_when_customizing( 'is_archive' );
  }
  if ( !$is_archive ) {
    return sek_get_tmpl_tag_error( $tag = 'the_archive_description', $msg = __('It can be used in archive pages only.', 'text_doma') );
  }

  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_archive_description' );
  } else {
    $title = get_the_archive_description();
  }
  return $title;
}


// CALLBACKS WHEN IS_SINGULAR()
function sek_get_next_post_link() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_next_post_link', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_next_post_link' );
  } else {
    $title = get_next_post_link( $format = '%link' );
  }
  if ( empty( $title ) ) {
    return '';
  } else {
    return sprintf( '<span class="sek-next-post-link">%1$s</span>', $title );
  }
}

function sek_get_previous_post_link() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_previous_post_link', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_previous_post_link' );
  } else {
    $title = get_previous_post_link( $format = '%link' );
  }
  if ( empty( $title ) ) {
    return '';
  } else {
    return sprintf( '<span class="sek-previous-post-link">%1$s</span>', $title );
  }
}

function sek_get_the_comments() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_comments', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    return sprintf('<div class="nimble-notice-in-preview"><i class="fas fa-info-circle"></i>&nbsp;%1$s</div>',
      __('Comment template can not be refreshed while customizing', 'text_doma')
    );
  }

  ob_start();
  //load_template( $tmpl_path, false );
  if ( comments_open() || get_comments_number() ) {
    add_filter('comments_template', '\Nimble\sek_set_nb_comments_template_path');
    comments_template();
    remove_filter('comments_template', '\Nimble\sek_set_nb_comments_template_path');
  }
  return ob_get_clean();
}

//@filter 'comments_template'
function sek_set_nb_comments_template_path( $original_path ) {
  //@to do => make this path overridable
  $nb_path = sek_get_templates_dir() . "/wp/comments-template.php";
  if ( file_exists( $nb_path ) ) {
    return $nb_path;
  }
  return $original_path;
}

function sek_get_the_published_date() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_published_date', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  $post_id = sek_get_post_id_on_front_and_when_customizing();
  $published_date = get_the_date( get_option('date_format'), $post_id);
  $machine_readable_published_date = esc_attr( get_the_date( 'c' , $post_id ) );
  return sprintf( '<time class="sek-published-date" datetime="%1$s">%2$s</time>',
    $machine_readable_published_date,
    $published_date
  );
}

function sek_get_the_modified_date() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_modified_date', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  $post_id = sek_get_post_id_on_front_and_when_customizing();
  $modified_date = get_the_modified_date( get_option('date_format'), $post_id );
  $machine_readable_modified_date = esc_attr( get_the_modified_date( 'c' ), $post_id );
  return sprintf( '<time class="sek-modified-date" datetime="%1$s">%2$s</time>',
    $machine_readable_modified_date ,
    $modified_date
  );
}

function sek_get_the_tags( $separator = ' &middot; ') {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_tags', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return sprintf( '<span class="sek-post-tags">%1$s</span>', get_the_tag_list( $before = '', $sep = $separator, $after = '', $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}


function sek_get_the_categories( $separator = ' / ') {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_categories', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return sprintf( '<span class="sek-post-category">%1$s</span>', get_the_category_list( $separator, '', $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}

function sek_get_the_comment_number() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_comment_number', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return sprintf( '<span class="sek-post-comment-number">%1$s</span>', get_comments_number_text( $zero = false, $one = false, $more = false, $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}


// AUTHOR DATA
// 2 CASES : singular or author archives
function sek_get_the_author_link() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    $display_name = get_the_author_meta( 'display_name', $author_id );
    return sprintf(
      '<a href="%1$s" title="%2$s" class="sek-author-link" rel="author">%3$s</a>',
      esc_url( get_author_posts_url( $author_id, get_the_author_meta( 'user_nicename', $author_id ) ) ),
      /* translators: %s: Author's display name. */
      esc_attr( sprintf( __( 'Posts by %s' ), $display_name ) ),
      $display_name
    );
  }
  return null;
}

function sek_get_the_author_name() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    return sprintf( '<span class="sek-author-name">%1$s</span>', get_the_author_meta( 'display_name', $author_id ) );
  }
  return null;
}

function sek_get_the_author_avatar() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    return get_avatar( get_the_author_meta( 'ID', $author_id ), '85' );
  }
  return null;
}

function sek_get_the_author_bio() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    return sprintf( '<span class="sek-author-description">%1$s</span>', get_the_author_meta( 'description', $author_id ) );
  }
  return null;
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_title() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_title', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return get_the_title( sek_get_post_id_on_front_and_when_customizing() );
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_content() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_content', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
      $post_id = (int)sek_get_posted_query_param_when_customizing( 'post_id' );
      if ( is_int($post_id) ) {
          $post_object = get_post( $post_id );
          return !empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
      }
  } else {
      $post_object = get_post();
      return !empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
  }
  return null;
}

// CALLBACKS WHEN IS_SEARCH()
function sek_get_search_query() {
  $is_search = is_search();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_search = sek_get_posted_query_param_when_customizing( 'is_search' );
    $search_query = sek_get_posted_query_param_when_customizing( 'the_search_query' );
  } else {
    $search_query = get_search_query();
  }
  if ( !$is_search ) {
    return sek_get_tmpl_tag_error( $tag = 'the_search_query', $msg = __('It can only be used in search results page.', 'text_doma') );
  }
  return sprintf( '<span class="sek-search-query">%1$s</span>', esc_html( $search_query ) );
}

function sek_get_search_results_nb() {
  $is_search = is_search();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_search = sek_get_posted_query_param_when_customizing( 'is_search' );
    $search_res_nb = sek_get_posted_query_param_when_customizing( 'the_search_results_nb' );
  } else {
    global $wp_query;
    $search_res_nb = (int)$wp_query->found_posts;
  }
  if ( !$is_search ) {
    return sek_get_tmpl_tag_error( $tag = 'the_search_results_number', $msg = __('It can only be used in search results page.', 'text_doma') );
  }
  return sprintf( '<span class="sek-search-results-number">%1$s</span>', esc_html( $search_res_nb ) );
}

//////////////////////////////////////////////////
///// HELPERS
/////////////////////////////////////////////////
function sek_get_author_id_on_front_and_when_customizing() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( $is_singular ) {
    $post_id = sek_get_post_id_on_front_and_when_customizing();
    $post_object = get_post( $post_id );
    if ( empty( $post_object ) || !is_object( $post_object ) ) {
      $author_id = null;
    }
    $author_id = $post_object->post_author;
  } else {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
      $author_id = sek_get_posted_query_param_when_customizing( 'the_author_id' );
    } else {
      global $authordata;
      $author_id = isset( $authordata->ID ) ? $authordata->ID : 0;
    }
  }
  return $author_id;
}

// @return the post id in all cases
// when performing ajax action, we need the posted query params made available from the ajax params
function sek_get_post_id_on_front_and_when_customizing() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
        $post_id = (int)sek_get_posted_query_param_when_customizing( 'post_id' );
    } else {
        $post_id = get_the_ID();
    }
    return is_int($post_id) ? $post_id : null;
}

// recursively sanitize an array of posted ($_POST) query_params to be used when customzing
// @param params (array)
function sek_sanitize_query_params_array( $params = array()) {
  foreach ($params as $prm => $val) {
    if ( is_array($val) ) {
      if ( empty($val) ) {
        $sanitized_query_params[$prm] = [];
      } else {
        $sanitized_query_params[$prm] = sek_sanitize_query_params_array($params);
      }
    } else {
      $sanitized_query_params[$prm] = sanitize_text_field($val);
    }
  }
  return $sanitized_query_params;
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
          if ( !is_array($query_params[$param]) ) {
            return sanitize_text_field($query_params[$param]);
          } else {
            return sek_sanitize_query_params_array($query_params[$param]);
          }
      } else {
          sek_error_log( __FUNCTION__ . ' => invalid param requested');
          return null;
      }
  }
  return null;
}

function sek_get_tmpl_tag_error( $tag, $msg ) {
  if ( !skp_is_customizing() )
    return;
  return sprintf('<div class="nimble-notice-in-preview nimble-inline-notice-in-preview"><i class="fas fa-info-circle"></i> %1$s %2$s</div>',
    '{{' . $tag . '}} ' . __('could not be printed.', 'text_doma'),
    $msg
  );
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
    if ( empty( $params ) || !is_array( $params ))
      return;
    $params = wp_parse_args( $params, array( 'file_name' => '', 'folder' => 'page-templates' ) );

    if ( !in_array( $params['folder'] , array( 'page-templates', 'header', 'footer' ) ) )
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
    if ( !empty( $local_template_data ) && !empty( $local_template_data['local_template'] ) && 'default' !== $local_template_data['local_template'] ) {
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

        if ( !file_exists( $template_path ) ) {
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
    if ( !is_array( $collection ) ) {
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
    if ( !is_string( $child_level_id ) || empty( $child_level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $_parent_level_data;
    }

    // When no collection is provided, we must walk all collections, local and global.
    if ( empty( $collection ) ) {
        if ( empty( $skope_id ) ) {
            if ( is_array( $_POST ) && !empty( $_POST['location_skope_id'] ) ) {
                $skope_id = sanitize_text_field($_POST['location_skope_id']);
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
    if ( !is_string( $level_id ) || empty( $level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $level_skope_id;
    }

    $local_skope_settings = sek_get_skoped_seks( $level_skope_id );
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
    if ( 'not_set' !== Nimble_Manager()->page_has_global_sections )
        return Nimble_Manager()->page_has_global_sections;

    $maybe_global_sek_post = sek_get_seks_post( NIMBLE_GLOBAL_SKOPE_ID, 'global' );
    $nb_section_created = 0;
    if ( is_object($maybe_global_sek_post) ) {
        $seks_data = maybe_unserialize($maybe_global_sek_post->post_content);
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        $nb_section_created = sek_count_not_empty_sections_in_page( $seks_data );
    }
    // cache now
    Nimble_Manager()->page_has_global_sections = $nb_section_created > 0;
    return Nimble_Manager()->page_has_global_sections;
}


// @return bool
// added for https://github.com/presscustomizr/nimble-builder/issues/436
// initially used to determine if a post or a page has been customized with Nimble Builder => if so, we add an edit link in the post/page list
// when used in admin, the skope_id must be provided
// can be used to determine if we need to render Nimble Builder assets on front. See ::sek_enqueue_front_assets()
// March 2021 => fixed : function sek_local_skope_has_nimble_sections() => when customzing => seks_data param should be provided after being filtered with customized values
function sek_local_skope_has_nimble_sections( $skope_id = '', $seks_data = null ) {
    $skope_id = empty( $skope_id ) ? skp_get_skope_id() : $skope_id;

    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return false;
    }

    if ( 'not_set' !== Nimble_Manager()->page_has_local_sections )
        return Nimble_Manager()->page_has_local_sections;

    $nb_section_created = 0;
    
    // When the collection is provided use it otherwise get it
    if ( is_null($seks_data) || !is_array($seks_data) ) {
        $seks_data = sek_get_skoped_seks( $skope_id );
    }
    if ( is_array( $seks_data ) ) {
        $nb_section_created = sek_count_not_empty_sections_in_page( $seks_data );
    }
    // cache now
    Nimble_Manager()->page_has_local_sections = $nb_section_created > 0;
    return Nimble_Manager()->page_has_local_sections;
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
        //'name'                   => sanitize_title(),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    //sek_error_log('DO WE HAVE SECTIONS ?', $query );
    return is_array( $query->posts ) && !empty( $query->posts );
}




// recursive helper to count the number of sections in a given set of sections data
function sek_count_not_empty_sections_in_page( $seks_data, $count = 0 ) {
    if ( !is_array( $seks_data ) ) {
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
// Invoked when printing the review note in the plugin table, in the 'plugin_row_meta'
// Since this is a quite heavy check, NB stores it in a 7 days long transient
function sek_get_feedback_notif_status() {
    if ( sek_feedback_notice_is_dismissed() )
      return;

    // Check if we already stored the status in a transient first
    $transient_name = NIMBLE_FEEDBACK_STATUS_TRANSIENT_ID;
    $transient_value = get_transient( $transient_name );
    if ( false != $transient_value ) {
        return $transient_value;
    }

    // If transient not set or expired, let's set it and return the feedback status
    // $start_version = get_option( 'nimble_started_with_version', NIMBLE_VERSION );

    // Bail if user started after v2.1.20, October 22nd 2020 ( set on November 23th 2020 )
    // if ( !version_compare( $start_version, '3.1.12', '<=' ) )
    //   return;

    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title(),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( !is_array( $query->posts ) || empty( $query->posts ) )
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

    $transient_value = 'not_eligible';
    // sek_error_log('$section_created ??', $nb_section_created );
    // sek_error_log('$modules_used ?? ' . count($modules_used), $modules_used );
    // sek_error_log('$customized_pages ??', $customized_pages );
    //version_compare( $this->wp_version, '4.1', '>=' )
    if ( $customized_pages > 1 && $nb_section_created > 1 && count($modules_used) > 1 ) {
        $transient_value = 'eligible';
    }
    set_transient( $transient_name, $transient_value, 7 * DAY_IN_SECONDS );
    return $transient_value;
}


// recursive helper to generate a list of module used in a given set of sections data
function sek_populate_list_of_modules_used( $seks_data ) {
    global $modules_used;
    if ( !is_array( $seks_data ) ) {
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
    if ( 'local' === $level && is_array( $_POST ) && !empty( $_POST['local_skope_id'] ) && 'customize_save' === sanitize_text_field($_POST['action']) ) {
        $skope_id = sanitize_text_field($_POST['local_skope_id']);
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


/* ------------------------------------------------------------------------- *
 *  HELPER FOR CHECKBOX OPTIONS
/* ------------------------------------------------------------------------- */
function sek_is_checked( $val ) {
    //cast to string if array
    $val = is_array($val) ? $val[0] : $val;
    return sek_booleanize_checkbox_val( $val );
}

function sek_booleanize_checkbox_val( $val ) {
    if ( !$val || is_array( $val ) ) {
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
    if ( is_array( $wp_registered_sidebars ) && !empty( $wp_registered_sidebars ) ) {
        foreach ( $wp_registered_sidebars as $registered_sb ) {
            $id = $registered_sb['id'];
            if ( !sek_is_nimble_widget_id( $id ) )
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

// @return bool
// introduced for #883
function sek_is_widget_module_disabled() {
    return sek_booleanize_checkbox_val( get_option( NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE ) );
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
    return defined('NB_PRO_VERSION');
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
    if ( !$started_with )
      return false;

    if ( !is_string( $requested_version ) )
      return false;

    return version_compare( $started_with , $requested_version, '<' );
}



/* ------------------------------------------------------------------------- *
 *   VARIOUS HELPERS
/* ------------------------------------------------------------------------- */
function sek_text_truncate( $text, $max_text_length, $more, $strip_tags = true ) {
    if ( !$text )
        return '';

    if ( $strip_tags )
        $text       = strip_tags( $text );

    if ( !$max_text_length )
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
    $theme_slug = sanitize_text_field( isset($_REQUEST['theme']) ? $_REQUEST['theme'] : $theme_slug ); //old wp versions
    $theme_slug = sanitize_text_field( isset($_REQUEST['customize_theme']) ? $_REQUEST['customize_theme'] : $theme_slug );

    //gets the theme name (or parent if child)
    $theme_data = wp_get_theme( $theme_slug );
    if ( $theme_data->parent() ) {
        $theme_slug = $theme_data->parent()->Name;
    }

    return sanitize_file_name( strtolower( $theme_slug ) );
}




function sek_error_log( $title, $content = null ) {
    // Know in which function sek_error_log() was called
    // debug_backtrace()[1]['function'];
    $backtrace = debug_backtrace();
    //error_log( print_r( $backtrace, true ) );
    $content = is_null( $content ) ? '' : $content;
    if ( !sek_is_dev_mode() )
      return;

    $btrace = '';
    if ( is_array($backtrace) && isset($backtrace[1]) ) {
        if ( !empty( $backtrace[1]['file'] ) && !empty( $backtrace[1]['line'] ) ) {
            $btrace = $btrace . "\n ====> " . $backtrace[1]['file'] . '#' . $backtrace[1]['line'];
        }
        if ( !empty( $backtrace[1]['class'] ) && !empty( $backtrace[1]['function'] ) ) {
            $btrace = $btrace . "\n ====> " . $backtrace[1]['class'] . '::' . $backtrace[1]['function'];
        } else if ( !empty( $backtrace[1]['function']) ) {
            $btrace = $btrace . "\n ====> " . $backtrace[1]['function'];
        }
    }
    if ( is_null( $content ) ) {
        error_log( '<' . $title . '>' );
        if ( !empty($btrace) ) {
          error_log( '// backtrace :' . $btrace . '//' );
        }
    } else {
        error_log( '<' . $title . '>' );
        error_log( print_r( $content, true ) );
        if ( !empty($btrace) ) {
          error_log( '// backtrace :' . $btrace . '//' );
        }
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




/* ------------------------------------------------------------------------- *
 *  STRIP SCRIPT TAG WHEN CUSTOMIZING
 *  to prevent customizer breakages. See https://github.com/presscustomizr/nimble-builder/issues/688
/* ------------------------------------------------------------------------- */
function sek_strip_script_tags_and_print_js_inline( $html, $model ) {
    if ( !is_string( $html ) )
        return $html;
    if ( skp_is_customizing() ) {
        // June 2020 => added a notice for https://github.com/presscustomizr/nimble-builder/issues/710
        $script_notice = sprintf('<div class="nimble-notice-in-preview"><i class="fas fa-info-circle"></i>&nbsp;%1$s</div>',
        __('Custom javascript code is not executed when customizing.', 'text-doma')
        );
        return preg_replace('#<script(.*?)>(.*?)</script>#is', $script_notice, $html);
    } else {
        $js = "";
        // Get the script tags content
        preg_match_all('/<script>(.*?)<\/script>/s', $html, $matches);
        foreach ($matches[1] as $value) {
            if (!empty($value)) {
                $js .= $value .";\n";
            }
        }
        
        // Remove the scripts from the content
        //$html = preg_replace('#<script(.*?)</script>#is', '', $html);
        
        // Print scripts inline safely
        if ( !empty($js) ) {
            wp_register_script( 'nb_module_custom_js_' . $model['id'], '');
            wp_enqueue_script( 'nb_module_custom_js_' . $model['id'] );
            wp_add_inline_script( 'nb_module_custom_js_' . $model['id'], $js );
        }
        return $html;
    }
}

function sek_strip_script_tags( $html = '' ) {
      if (!is_string( $html ) ) {
          return $html;
      }
      return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
}

// @return bool
// Introduced May 2020
function sek_current_user_can_access_nb_ui() {
    return apply_filters('nb_user_has_access', true );
}




/* ------------------------------------------------------------------------- *
 *  TRANSIENTS
/* ------------------------------------------------------------------------- */
// July 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/730
function sek_clean_transients_like( $transient_string ) {
    global $wpdb;
    $where_like = '%'.$transient_string.'%';
    $sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
            FROM  $wpdb->options
            WHERE `option_name` LIKE '$where_like'
            ORDER BY `option_name`";

    $results = $wpdb->get_results( $sql );
    $transients = array();

    // TRANSIENTS
    foreach ( $results as $result ) {
        if ( 0 === strpos( $result->name, '_transient' ) ) {
            if ( 0 === strpos( $result->name, '_transient_timeout_') ) {
                $transients['transient_timeout'][ $result->name ] = $result->value;
            } else {
                $transients['transient'][ $result->name ] = maybe_unserialize( $result->value );
            }
        }
    }

    //sek_error_log('CLEAN PAST TRANSIENTS ' . $transient_string, $transients );

    // Clean the transients found
    // transient name looks like _transient_section_json_transient_2.1.5
    // when deleted, it also removes the associated _transient_timeout_... option
    foreach ($transients as $group => $list) {
        if ( 'transient' === $group && is_array($list) ) {
            foreach ($list as $name => $val) {
              // the transient name does not include the "_transient_" prefix
              $trans_name = substr($name, strlen('_transient_') );
              delete_transient( $trans_name );
            }
        }
    }
}





/* ------------------------------------------------------------------------- *
 *  OPTIONS CLEANING
/* ------------------------------------------------------------------------- */
//  introduced for https://github.com/presscustomizr/nimble-builder/issues/826
function sek_clean_options_starting_like( $opt_string ) {
    global $wpdb;
    $where_like = '%'.$opt_string.'%';
    $sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
            FROM  $wpdb->options
            WHERE `option_name` LIKE '$where_like'
            ORDER BY `option_name`";

    $results = $wpdb->get_results( $sql );
    if ( !is_array( $results ) )
      return;

    foreach ( $results as $result ) {
        if ( 0 === strpos( $result->name, $opt_string ) ) {
            delete_option( $result->name );
        }
    }
}






// July 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/720
// @param $features (string) list of features
function sek_get_pro_notice_for_czr_input( $features = '' ) {
  if ( !sek_is_upsell_enabled() )
    return '';
  return sprintf( '<hr/><p class="sek-pro-notice"><img class="sek-pro-icon" src="%1$s"/><span class="sek-pro-notice-icon-bef-text"><img src="%2$s"/></span><span class="sek-pro-notice-text">%3$s : %4$s<br/><br/>%5$s</span><p>',
      NIMBLE_BASE_URL.'/assets/czr/sek/img/pro_white.svg?ver='.NIMBLE_VERSION,
      NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION,
      __('Unlock more features with Nimble Builder Pro', 'text-doma'),
      $features,
      sprintf('<a href="%1$s" rel="noopener noreferrer" title="%2$s" target="_blank">%2$s <i class="fas fa-external-link-alt"></i></a>',
          'https://presscustomizr.com/nimble-builder-pro/?utm_source=usersite&utm_medium=link&utm_campaign=czr',
          __('Go Pro', 'text-doma')
      )
  );
}


// September 2020 : filter the collection of pre-built sections
// Removes pro upsell modules if sek_is_upsell_enabled() is false
// filter declared in _front_dev_php/_constants_and_helper_functions/0_5_2_sektions_local_sektion_data.php
add_filter('sek_get_raw_section_registration_params', function( $collection ) {
    if ( sek_is_pro() )
      return $collection;
    if ( sek_is_upsell_enabled() )
      return $collection;

    $filtered = [];
    foreach ($collection as $section_group_name => $group_data) {
        $filtered[$section_group_name] = $group_data;
        foreach ( $group_data['section_collection'] as $sec_key => $sec_data) {
            if ( array_key_exists('is_pro', $sec_data) && $sec_data['is_pro'] ) {
                unset($filtered[$section_group_name]['section_collection'][$sec_key]);
            }
        }
    }
    return $filtered;
});

// inspired from https://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string-for-use-in-a-secret-l
// equivalent of js customizer method CZRSeksPrototype::guid()
// introduced feb 2021 for https://github.com/presscustomizr/nimble-builder/issues/478
// used when generating id server side for a site template
function sek_generate_level_guid() {
    return NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED . substr( strval( md5( uniqid( rand(), true) ) ),0, 12 );//__nimble__4cdf8be5ce8f
}


function sek_is_upsell_enabled() {
  return !sek_is_pro();
}

?><?php
// /* ------------------------------------------------------------------------- *
// *  NIMBLE API
// /* ------------------------------------------------------------------------- */

// Nimble api returns a set of value structured as follow
// return array(
//     'timestamp' => time(),
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
// api data is refreshed on plugin update and theme switch
// @$what param can be 'latest_posts_and_start_msg', 'templates', 'single_section'
function sek_get_nimble_api_data( $params ) {
    $params = is_array($params) ? $params : [];
    $params = wp_parse_args( $params, [
        'what' => '',
        'tmpl_name' => '',
        'section_id' => '',
        'force_update' => false
    ]);
    $what = $params['what'];
    $tmpl_name = $params['tmpl_name'];
    $section_id =  $params['section_id'];
    $force_update = $params['force_update'];
    $wp_cache_key = 'nimble_api_data_'. $what . $tmpl_name . $section_id;
    
    // We must have a "what"
    if ( is_null($what) || !is_string($what) ) {
        sek_error_log( __FUNCTION__ . ' => error => $what param not set');
        return false;
    }

    // If a single template is requested, a valid template name must be provided
    if ( 'single_tmpl' === $what && ( empty($tmpl_name) || !is_string($tmpl_name) ) ) {
        sek_error_log( __FUNCTION__ . ' => error => invalid $tmpl_name param');
        return false;
    }

    // If a single section is requested, a valid section id must be provided
    if ( 'single_section' === $what && ( empty($section_id) || !is_string($section_id) ) ) {
        sek_error_log( __FUNCTION__ . ' => error => invalid $section_id param');
        return false;
    }

    $cached_api_data = wp_cache_get( $wp_cache_key  );

    if ( $cached_api_data && is_array($cached_api_data) && !empty($cached_api_data) ) {
        return $cached_api_data;
    }

    $transient_name = '';
    $transient_duration = 7 * DAY_IN_SECONDS;

    switch ( $what ) {
        case 'latest_posts_and_start_msg':
            $transient_name = 'nimble_api_posts';
            $transient_duration = 7 * DAY_IN_SECONDS;
        break;
        case 'all_tmpl':
            $transient_name = 'nimble_api_all_tmpl';
            $transient_duration = 5 * DAY_IN_SECONDS;
        break;
        case 'single_tmpl':
            $transient_name = 'nimble_api_tmpl_' . $tmpl_name;
            $transient_duration = 2 * DAY_IN_SECONDS;
        break;
        case 'single_section':
            $transient_name = 'nimble_api_section_' . $section_id;
        break;
        default:
            sek_error_log( __FUNCTION__ . ' => error => invalid $what param => ' . $what );
        break;
    }

    if ( empty( $transient_name ) ) {
        return false;
    }

    $theme_slug = sek_get_parent_theme_slug();
    $version_transient_value = get_transient( NIMBLE_API_CHECK_TRANSIENT_ID );
    $expected_version_transient_value = NIMBLE_VERSION . '_' . $theme_slug;
    $api_needs_update = $version_transient_value != $expected_version_transient_value;

    $api_transient_data = maybe_unserialize( get_transient( $transient_name ) );

    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;
    if ( true === $force_update ) {
          sek_error_log( __FUNCTION__ . ' API is in force update mode. API data requested => ' . $transient_name );
    }

    $api_data = $api_transient_data;
    $invalid_transient_data = false;

    // When requesting a single_section with sek_api_get_single_section_data, the expected returned data are formed like
    // [
    //     [timestamp] => 1621256718
    //     [single_section] => []
    // ]
        // When requesting a single_tmpl with sek_get_single_tmpl_api_data, the expected returned data are formed like
    // [
    //     [timestamp] => 1621256718
    //     [single_tmpl] => []
    // ]
    // If a problem occured when getting a pro section or template, single_section or single_tmpl is a string, not an array
    // in this case, we need to re-connect to the api
    // see https://github.com/presscustomizr/nimble-builder-pro/issues/193
    if ( 'single_section' === $what && is_array( $api_data ) && array_key_exists('single_section', $api_data ) && !is_array($api_data['single_section'] ) ) {
        $invalid_transient_data = true;
    }
    if ( 'single_tmpl' === $what && is_array( $api_data ) && array_key_exists('single_tmpl', $api_data ) && !is_array($api_data['single_tmpl'] ) ) {
        $invalid_transient_data = true;
    }

    // Connect to remote NB api when :
    // 1) api data transient is not set or has expired ( false === $api_transient_data )
    // 2) force_update param is true
    // 3) NB has been updated to a new version ( $api_needs_update case )
    // 4) Theme has been changed ( $api_needs_update case )
    // 5) API DATA is not an array ( for https://github.com/presscustomizr/nimble-builder-pro/issues/193 )
    // 6) Invalid transient data ( for https://github.com/presscustomizr/nimble-builder-pro/issues/193 )
    if ( $force_update || false === $api_data || !is_array($api_data) || $api_needs_update || $invalid_transient_data ) {
        $query_params = apply_filters( 'nimble_api_query_params', [
            'timeout' => ( $force_update ) ? 25 : 8,
            'body' => [
                'api_version' => NIMBLE_VERSION,
                'site_lang' => get_bloginfo( 'language' ),
                'what' => $what,// 'single_tmpl', 'all_tmpl', 'latest_posts_and_start_msg', 'single_section'
                'tmpl_name' => $tmpl_name,
                'section_id' => $section_id
            ]
        ] );

        //sek_error_log('CALL TO REMOTE API NOW FOR DATA => ' . $transient_name . ' | ' . $force_update . ' | ' . $api_needs_update, $query_params );

        $response = wp_remote_get( NIMBLE_DATA_API_URL_V2, $query_params );

        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            // set the transient to '_api_error_', so that we don't hammer the api if not reachable. next call will be done after transient expiration
            $api_data = '_api_error_';
            sek_error_log( __FUNCTION__ . ' error with api response');
        }

        $api_data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $api_data ) || !is_array( $api_data ) ) {
            sek_error_log( __FUNCTION__ . ' invalid api data after json decode', $api_data );
            // set the transient to '_api_error_', so that we don't hammer the api if not reachable. next call will be done after transient expiration
            $api_data = '_api_error_';
        }
        // When requesting a single_section with sek_api_get_single_section_data, the expected returned data are formed like
        // [
        //     [timestamp] => 1621256718
        //     [single_section] => []
        // ]
         // When requesting a single_tmpl with sek_get_single_tmpl_api_data, the expected returned data are formed like
        // [
        //     [timestamp] => 1621256718
        //     [single_tmpl] => []
        // ]
        // If a problem occured when getting a pro section or template, single_section or single_tmpl is a string, not an array
        // in this case, we don't want to sage the api data like this as transient because user will need the transient to expire before getting the correct data ( see https://github.com/presscustomizr/nimble-builder-pro/issues/193 )
        if ( 'single_section' === $what && array_key_exists('single_section', $api_data ) && !is_array($api_data['single_section'] ) ) {
            sek_error_log( __FUNCTION__ . ' invalid single section api data', $api_data);
            $api_data = '_api_error_';
        }
        if ( 'single_tmpl' === $what && array_key_exists('single_tmpl', $api_data ) && !is_array($api_data['single_tmpl'] ) ) {
            sek_error_log( __FUNCTION__ . ' invalid single tmpl api data', $api_data);
            $api_data = '_api_error_';
        }

        // if the api could not be reached, let's retry in 2 minutes with a short transient duration
        set_transient( $transient_name, $api_data, '_api_error_' === $api_data ? 2 * MINUTE_IN_SECONDS : $transient_duration );
        // The api data will be refreshed on next plugin update, or next theme switch. Or if $transient_name has expired.
        // $expected_version_transient_value = NIMBLE_VERSION . '_' . $theme_slug;
        set_transient( NIMBLE_API_CHECK_TRANSIENT_ID, $expected_version_transient_value, 100 * DAY_IN_SECONDS );
    }//if ( $force_update || false === $api_data )
    
    // if api_error a new api call will be done when the relevant transient will expire
    if ( '_api_error_' === $api_data ) {
        sek_error_log( __FUNCTION__ . ' API data value is _api_error_ for transient data : ' . $transient_name );
    }

    $api_data = '_api_error_' === $api_data ? null : $api_data;
    wp_cache_set( $wp_cache_key, $api_data );

    //sek_error_log('API DATA for ' . $transient_name, $api_data );

    return $api_data;
}


//////////////////////////////////////////////////
/// TEMPLATE DATA
function sek_get_all_tmpl_api_data( $force_update = false ) {
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;

    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update( upgrader_process_complete ), theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $api_data = sek_get_nimble_api_data([
        'what' => 'all_tmpl',
        'force_update' => $force_update
    ]);

    $api_data = is_array( $api_data ) ? $api_data : [];

    //sek_error_log('TMPL DATA ?', $tmpl_data);
    if ( empty($api_data) || !array_key_exists('lib', $api_data) || !is_array($api_data['lib']) || empty($api_data['lib']['templates']) || !is_array($api_data['lib']['templates']) ) {
        sek_error_log( __FUNCTION__ . ' => error => no json_collection' );
        return array();
    }
   
    //return [];
    return maybe_unserialize( $api_data['lib']['templates'] );
}


function sek_get_single_tmpl_api_data( $tmpl_name, $is_pro_tmpl = false, $force_update = false ) {
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;

    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update( upgrader_process_complete ), theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $api_data = sek_get_nimble_api_data([
        'what' => 'single_tmpl',
        'tmpl_name' => $tmpl_name,
        'force_update' => $force_update
    ]);

    // The api should return an array
    if ( !is_array( $api_data ) || !array_key_exists( 'single_tmpl', $api_data ) ) {
        return __('Problem when fetching template');
    }

    // If the api returned a pro license key problem, bail now and return the api string message
    if ( $is_pro_tmpl && is_string( $api_data['single_tmpl'] ) ) {
        return $api_data['single_tmpl'];
    }

    $api_data = wp_parse_args( $api_data, [
        'timestamp' => '',
        'single_tmpl' => null
    ]);
    //sek_error_log('TMPL DATA ?', $tmpl_data);
    if ( empty($api_data['single_tmpl']) ) {
        sek_error_log( __FUNCTION__ . ' => error => empty template for ' . $tmpl_name );
        return array();
    }
    if ( !is_array( $api_data['single_tmpl'] ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid template for ' . $tmpl_name );
        return array();
    }

    if ( !array_key_exists( 'data', $api_data['single_tmpl'] ) || !array_key_exists( 'metas',$api_data['single_tmpl'] ) ) {
        sek_error_log( __FUNCTION__ . ' => error => invalid template data for ' . $tmpl_name );
        return array();
    }
    //return [];
    return maybe_unserialize( $api_data['single_tmpl'] );
}



//////////////////////////////////////////////////
/// SINGLE PRESET SECTION DATA
function sek_api_get_single_section_data( $api_section_id, $force_update = false ) {
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;

    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update( upgrader_process_complete ), theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $api_data = sek_get_nimble_api_data([
        'what' => 'single_section',
        'section_id' => $api_section_id,
        'force_update' => $force_update
    ]);

    $api_data = is_array( $api_data ) ? $api_data : [];
    $api_data = wp_parse_args( $api_data, [
        'timestamp' => '',
        'single_section' => null
    ]);
    //sek_error_log('SECTION DATA ?', $api_data);
    if ( empty($api_data['single_section']) ) {
        sek_error_log( __FUNCTION__ . ' => error => empty section data for ' . $api_section_id );
        return array();
    }

    // if ( !array_key_exists( 'data', $api_data['single_tmpl'] ) || !array_key_exists( 'metas',$api_data['single_tmpl'] ) ) {
    //     sek_error_log( __FUNCTION__ . ' => error => invalid section data for ' . $api_section_id );
    //     return array();
    // }
    //return [];
    return maybe_unserialize( $api_data['single_section'] );
}



//////////////////////////////////////////////////
/// LATESTS POSTS
// @return array of posts
function sek_get_latest_posts_api_data( $force_update = false ) {
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;
    $api_data = sek_get_nimble_api_data([
        'what' => 'latest_posts_and_start_msg',
        'force_update' => $force_update
    ]);
    $api_data = is_array( $api_data ) ? $api_data : [];
    $api_data = wp_parse_args( $api_data, [
        'timestamp' => '',
        'latest_posts' => null
    ]);
    if ( !is_array( $api_data['latest_posts'] ) || empty( $api_data['latest_posts'] ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no latest_posts' );
        return [];
    }
    return $api_data['latest_posts'];
}

// @return html string
function sek_start_msg_from_api( $theme_name, $force_update = false ) {
    if ( !sek_is_presscustomizr_theme( $theme_name ) ) {
        return '';
    }
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;

    $api_data = sek_get_nimble_api_data( [
        'what' => 'latest_posts_and_start_msg',
        'force_update' => $force_update
    ]);
    $api_data = is_array( $api_data ) ? $api_data : [];
    $api_data = wp_parse_args( $api_data, [
        'timestamp' => '',
        'start_msg' => null
    ]);

    $msg = '';
    $api_msg = isset( $api_data['start_msg'] ) ? $api_data['start_msg'] : null;

    if ( !is_null($api_msg) && is_string($api_msg) ) {
        $msg = $api_msg;
    }
    return $msg;
}

// Attempt to refresh the api template data => will store in a transient if not done yet, to make it faster to render in the customizer
// add_action( 'wp_head', '\Nimble\sek_maybe_refresh_nimble_api_tmpl_data');
// function sek_maybe_refresh_nimble_api_tmpl_data() {
//     if ( skp_is_customizing() || false !== get_transient( 'nimble_api_all_tmpl' ) )
//         return;
//     sek_get_nimble_api_data(['what' => 'all_tmpl']);
// }


?><?php
// This file has been introduced on May 21st 2019 => back to the local data
// after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445

/////////////////////////////////////////////////////////////
// REGISTRATION PARAMS FOR PRESET SECTIONS
// @return array()
function sek_get_sections_registration_params( $force_update = false ) {

    // JULY 2020 => not stored in a transient anymore. For https://github.com/presscustomizr/nimble-builder/issues/730
    // + clean previously created transients
    $bw_fixes_options = get_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES );
    $bw_fixes_options = is_array( $bw_fixes_options ) ? $bw_fixes_options : array();
    if ( !array_key_exists('clean_section_params_transient_0720', $bw_fixes_options ) || 'done' != $bw_fixes_options['clean_section_params_transient_0720'] ) {
        sek_clean_transients_like( 'section_params_transient' );
        $bw_fixes_options['clean_section_params_transient_0720'] = 'done';
        // flag as done
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }

    // $section_params_transient_name = 'section_params_transient_' . NIMBLE_VERSION;
    // $registration_params = get_transient( $section_params_transient_name );
    // // Refresh every 30 days, unless force_update set to true
    // if ( $force_update || false === $registration_params ) {
    //     $registration_params = sek_get_raw_section_registration_params();
    //     set_transient( $section_params_transient_name, $registration_params, 30 * DAY_IN_SECONDS );
    // }

    $registration_params = sek_get_raw_section_registration_params();
    return $registration_params;
}

function sek_get_raw_section_registration_params() {
    return apply_filters( 'sek_get_raw_section_registration_params', [
        'sek_intro_sec_picker_module' => [
            'name' => __('Sections for an introduction', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'intro_three',
                    'title' => __('1 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_three.jpg',
                    'demo_url' => '#intro-one'
                ),
                array(
                    'content-id' => 'intro_one',
                    'title' => __('1 column, full-width background', 'text-domain' ),
                    'thumb' => 'intro_one.jpg',
                    'demo_url' => '#intro-two'
                ),
                array(
                    'content-id' => 'intro_two',
                    'title' => __('2 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_two.jpg',
                    'demo_url' => '#intro-three'
                ),
                array(
                    'content-id' => 'pro_intro_two',
                    'title' => __('3 columns, call to actions', 'text-domain' ),
                    'thumb' => 'pro_intro_two.jpg',
                    'active' => sek_is_pro(),
                    'is_pro' => true,
                    'demo_url' => 'https://nimblebuilder.com/special-image-demo?utm_source=usersite&utm_medium=link&utm_campaign=section_demos'
                ),
                array(
                    'content-id' => 'pro_intro_one',
                    'title' => __('2 columns, call to actions, image carousel', 'text-domain' ),
                    'thumb' => 'pro_intro_one.jpg',
                    'active' => sek_is_pro(),
                    'is_pro' => true,
                    'demo_url' => '#intro-four'
                )
            )
        ],
        'sek_post_grids_sec_picker_module' => [
            'name' => __('Post lists sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'grid_one',
                    'title' => __('Simple post grid', 'text-domain' ),
                    'thumb' => 'grid_one.jpg',
                    'demo_url' => 'https://nimblebuilder.com/post-grid-sections?utm_source=usersite&utm_medium=link&utm_campaign=section_demos#grid-one'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'grid_two',
                    'title' => __('Posts on two columns', 'text-domain' ),
                    'thumb' => 'grid_two.jpg',
                    'demo_url' => 'https://nimblebuilder.com/post-grid-sections?utm_source=usersite&utm_medium=link&utm_campaign=section_demos#grid-two'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'pro_grid_one',
                    'title' => __('Masonry post grid', 'text-domain' ),
                    'thumb' => 'pro_grid_one.jpg',
                    'demo_url' => 'https://nimblebuilder.com/post-grid-sections?utm_source=usersite&utm_medium=link&utm_campaign=section_demos#pro-grid-one',
                    'active' => sek_is_pro(),
                    'is_pro' => true
                    //'height' => '188px'
                )
            )
        ],
        'sek_features_sec_picker_module' => [
            'name' => __('Sections for services and features', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'features_one',
                    'title' => __('3 columns with icon and call to action', 'text-domain' ),
                    'thumb' => 'features_one.jpg',
                    'demo_url' => '#service-one'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'features_two',
                    'title' => __('3 columns with icon', 'text-domain' ),
                    'thumb' => 'features_two.jpg',
                    'demo_url' => '#service-two'
                    //'height' => '188px'
                )
            )
        ],
        'sek_about_sec_picker_module' => [
            'name' => __('About us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'about_one',
                    'title' => __('A simple about us section with 2 columns', 'text-domain' ),
                    'thumb' => 'about_one.jpg',
                    'demo_url' => '#about-one'
                    //'height' => '188px'
                )
            )
        ],
        'sek_contact_sec_picker_module' => [
            'name' => __('Contact-us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'contact_one',
                    'title' => __('A contact form and a Google map', 'text-domain' ),
                    'thumb' => 'contact_one.jpg',
                    'demo_url' => '#contact-one'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'contact_two',
                    'title' => __('A contact form with an image background', 'text-domain' ),
                    'thumb' => 'contact_two.jpg',
                    'demo_url' => '#contact-two'
                    //'height' => '188px'
                )
            )
        ],
        'sek_team_sec_picker_module' => [
            'name' => __('Sections for teams', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'team_one',
                    'title' => __('4 column', 'text-domain' ),
                    'thumb' => 'team_one.jpg',
                    'demo_url' => '#team-one'
                ),
                array(
                    'content-id' => 'team_two',
                    'title' => __('3 columns', 'text-domain' ),
                    'thumb' => 'team_two.jpg',
                    'height' => '180px',
                    'demo_url' => '#team-two'
                ),
                array(
                    'content-id' => 'pro_team_one',
                    'title' => __('3 columns, call to action', 'text-domain' ),
                    'thumb' => 'pro_team_one.jpg',
                    'active' => sek_is_pro(),
                    'height' => '180px',
                    'is_pro' => true,
                    'demo_url' => '#team-three'
                )
            )
        ],
        'sek_column_layouts_sec_picker_module' => [
            'name' => __('Empty sections with columns layout', 'text_doma'),
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
            'name' => __('Header sections', 'text_doma'),
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
            'name' => __('Footer sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'footer_pro_one',
                    'title' => __('simple 2 columns footer', 'text-domain' ),
                    'thumb' => 'footer_pro_one.jpg',
                    'section_type' => 'footer',
                    'height' => '75px',
                    'active' => sek_is_pro(),
                    'is_pro' => true
                ),
                array(
                    'content-id' => 'footer_with_social_links_one',
                    'title' => __('footer with dynamic date, site title and social links', 'text-domain' ),
                    'thumb' => 'footer_with_social_links_one.jpg',
                    'section_type' => 'footer',
                    'height' => '51px'
                ),
                array(
                    'content-id' => 'footer_one',
                    'title' => __('simple 3 columns footer', 'text-domain' ),
                    'thumb' => 'footer_one.jpg',
                    'section_type' => 'footer',
                    'height' => '75px'
                )
            )
        ]
    ]);
}
?><?php
add_action( 'admin_bar_menu', '\Nimble\sek_add_customize_link', 1000 );
function sek_add_customize_link() {
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    global $wp_admin_bar;
    // Don't show for users who can't access the customizer
    if ( !current_user_can( 'customize' ) )
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
        if ( is_customize_preview() && $wp_customize->changeset_post_id() && !current_user_can( get_post_type_object( 'customize_changeset' )->cap->edit_post, $wp_customize->changeset_post_id() ) ) {
          return;
        }
        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
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
          apply_filters( 'nb_admin_bar_title', __( 'Build with Nimble Builder', 'text_domain' ) )
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
        && !( 'post' === $post_type_object->name && 'posts' === get_option( 'show_on_front' ) ) )
    {
        $customize_url = get_post_type_archive_link( $current_screen->post_type );
    } elseif ( 'term' == $current_screen->base
        && isset( $tag ) && is_object( $tag ) && !is_wp_error( $tag )
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

    if ( !empty( $customize_url ) ) {
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
// introduced for #799
function sek_maybe_optimize_options() {
    $bw_fixes_options = get_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES );
    $bw_fixes_options = is_array( $bw_fixes_options ) ? $bw_fixes_options : array();

    // March 13th 2021 => removed previous option used to store api post news, now handled with a transient.
    if ( !array_key_exists('optimize_opts_0321_2', $bw_fixes_options ) || 'done' != $bw_fixes_options['optimize_opts_0321_2'] ) {
        delete_option( 'nimble_api_news_data' );

        // flag as done
        $bw_fixes_options['optimize_opts_0321_2'] = 'done';
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }


    if ( !array_key_exists('optimize_opts_0321', $bw_fixes_options ) || 'done' != $bw_fixes_options['optimize_opts_0321'] ) {
        $current_global_opts = get_option('__nimble_options__');
        if ( false !== $current_global_opts ) {
            update_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS, $current_global_opts, 'no' );
            delete_option( '__nimble_options__' );
        }
        // delete previous option for prebuild section json
        // => the option will be re-created with autoload set to "no" and renamed "nimble_prebuild_sections"
        delete_option( 'nb_prebuild_section_json' );

        // flag as done
        $bw_fixes_options['optimize_opts_0321'] = 'done';
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }



    // If the move in post index has been done, let's update to autoload = false the previous post_id options LIKE nimble___skp__post_page_*****, nimble___skp__tax_product_cat_*****
    // As of March 2021, event if those previous options are not used anymore, let's keep them in DB to cover potential retro-compat problems
    // in a future release, if no regression was reported, we'll remove them forever.
    if ( array_key_exists('move_in_post_index_0321', $bw_fixes_options ) && 'done' === $bw_fixes_options['move_in_post_index_0321'] ) {
        if ( !array_key_exists('fix_skope_opt_autoload_0321', $bw_fixes_options ) || 'done' != $bw_fixes_options['fix_skope_opt_autoload_0321'] ) {
            // MOVE ALL OPTIONS LIKE nimble___skp__post_page_*****, nimble___skp__tax_product_cat_***** in a new option ( NIMBLE_OPT_SEKTION_POST_INDEX ), not autoloaded
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE autoload = 'yes' and option_name like 'nimble___skp_%'", ARRAY_A );
            if ( is_array( $results ) ) {
                foreach( $results as $old_opt_data ) {
                    if ( !is_array($old_opt_data) )
                        continue;
                    if ( empty($old_opt_data['option_name']) || empty($old_opt_data['option_value']) )
                        continue;
                    // update it with autoload set to "no"
                    update_option( $old_opt_data['option_name'], (int)$old_opt_data['option_value'], 'no' );
                }
            }

            // flag as done
            $bw_fixes_options['fix_skope_opt_autoload_0321'] = 'done';
            update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
        }
    }


    if ( !array_key_exists('move_in_post_index_0321', $bw_fixes_options ) || 'done' != $bw_fixes_options['move_in_post_index_0321'] ) {
        // MOVE ALL OPTIONS LIKE nimble___skp__post_page_*****, nimble___skp__tax_product_cat_***** in a new option ( NIMBLE_OPT_SEKTION_POST_INDEX ), not autoloaded
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE autoload = 'yes' and option_name like 'nimble___skp_%'", ARRAY_A );
        if ( is_array( $results ) ) {
            // Populate the new option ( it should not exists at this point )
            $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
            $nb_posts_index = is_array($nb_posts_index) ? $nb_posts_index : [];
            foreach( $results as $old_opt_data ) {
                if ( !is_array($old_opt_data) )
                    continue;
                if ( empty($old_opt_data['option_name']) || empty($old_opt_data['option_value']) )
                    continue;
                
                $nb_posts_index[ $old_opt_data['option_name'] ] = (int)$old_opt_data['option_value'];
            }
            // update it with autoload set to "no"
            update_option( NIMBLE_OPT_SEKTION_POST_INDEX, $nb_posts_index, 'no');
        }

        // flag as done
        $bw_fixes_options['move_in_post_index_0321'] = 'done';
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }
}


// JULY 2020 => NOT FIRED ANYMORE ( because introduced in oct 2018 ) => DEACTIVATED IN nimble-builder.php
// fired @wp_loaded
// Note : if fired @plugins_loaded, invoking wp_update_post() generates php notices
function sek_maybe_do_version_mapping() {
    // if ( !is_user_logged_in() || !current_user_can( 'customize' ) )
    //   return;
    // //delete_option(NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS);
    // $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    // $global_options = is_array( $global_options ) ? $global_options : array();
    // $global_options['retro_compat_mappings'] = isset( $global_options['retro_compat_mappings'] ) ? $global_options['retro_compat_mappings'] : array();

    // // To 1_0_4 was introduced in december 2018
    // // It's related to a modification of the skope_id when home is a static page
    // if ( !array_key_exists( 'to_1_4_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['to_1_4_0'] ) {
    //     $status_to_1_4_0 = sek_do_compat_to_1_4_0();
    //     //sek_error_log('$status_1_0_4_to_1_1_0 ' . $status_1_0_4_to_1_1_0, $global_options );
    //     $global_options['retro_compat_mappings']['to_1_4_0'] = 'done';
    // }

    // // 1_0_4_to_1_1_0 introduced in October 2018
    // if ( !array_key_exists( '1_0_4_to_1_1_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] ) {
    //     $status_1_0_4_to_1_1_0 = sek_do_compat_1_0_4_to_1_1_0();
    //     //sek_error_log('$status_1_0_4_to_1_1_0 ' . $status_1_0_4_to_1_1_0, $global_options );
    //     $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] = 'done';
    // }
    // update_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS, $global_options );
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
                update_option( $new_option_name, $post_id_storing_home_page_sections, 'no' );
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
        //'name'                   => sanitize_title(),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( !is_array( $query->posts ) || empty( $query->posts ) )
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
        if ( is_array($value) && array_key_exists('level', $value) && !array_key_exists('ver_ini', $value) ) {
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
        if ( !empty( $value ) && is_array( $value ) && 'options' === $key ) {
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
      'name'          => sek_is_cpt_debug_mode() ? __( '[NB debug] skoped section') : __( 'NB skoped section'),
      'singular_name' => __( 'NB skoped section')
    ),
    'public'           => sek_is_cpt_debug_mode(),
    'hierarchical'     => false,
    'rewrite'          => false,
    'query_var'        => false,
    'delete_with_user' => false,
    'can_export'       => true,
    //'_builtin'         => true, /* internal use only. don't use this when registering your own post type. */
    'supports'         => sek_is_cpt_debug_mode() ? array( 'editor', 'title', 'revisions' ) : array( 'title', 'revisions' ),
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

// Returns the id of the post in which the local collection is stored
// This option NIMBLE_OPT_SEKTION_POST_INDEX is updated when publishing in the customizer and may also be updated when getting the collection in sek_get_seks_post()
// introduced for #799
function sek_get_nb_post_id_from_index( $skope_id ) {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    $post_id = 0;
    // Backward compat => march 2021, NB introduces a new option 'nimble_posts_index' dedicated to store the NB post_id associated to a skope_id.
    // For previous user, a backward compatibility code is ran on each load at 'wp_loaded', to transfer all previous options to the new one.
    // if the transfer went wrong, or if the option 'nimble_posts_index' was deleted, we can attempt to get the post_id from the previous option
    if ( !is_array( $nb_posts_index ) ) {
        $post_id = get_option( $option_name );
    } else {
        if ( array_key_exists( $option_name, $nb_posts_index ) ) {
            $post_id = (int)$nb_posts_index[$option_name];
        }
    }
    return $post_id;
}

// Associates a skope_id to a NB post id in the NB post index option
// introduced for #799
function sek_set_nb_post_id_in_index( $skope_id, $post_id ) {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $nb_posts_index = is_array($nb_posts_index) ? $nb_posts_index : [];
    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    $nb_posts_index[$option_name] = (int)$post_id;
    update_option( NIMBLE_OPT_SEKTION_POST_INDEX, $nb_posts_index, 'no');
}

// Associates a skope_id to a NB post id in the NB post index option
// introduced for #799
function sek_remove_nb_post_id_in_index( $skope_id ) {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $nb_posts_index = is_array($nb_posts_index) ? $nb_posts_index : [];
    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    if ( array_key_exists( $option_name, $nb_posts_index ) ) {
        unset( $nb_posts_index[$option_name] );
    }
    update_option( NIMBLE_OPT_SEKTION_POST_INDEX, $nb_posts_index, 'no');
}


// @return int
function sek_get_index_for_api() {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $nb_posts_index = is_array( $nb_posts_index ) ? $nb_posts_index : [];
    return count( $nb_posts_index );
}

/**
 * Fetch the `nimble_post_type` post for a given {skope_id}
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A theme object stylesheet name. Defaults to the current theme.
 * @return WP_Post|null The skope post or null if none exists.
 */
function sek_get_seks_post( $skope_id = '', $skope_level = 'local' ) {
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
        'post_status'            => get_post_stati(),//'publish'
        'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );

    $post = null;

    // First attempt to query the post with its ID stored in the index
    // If no results or post has been trashed, NB will try to get it with a query by name + update the index of skoped post ids
    $post_id = sek_get_nb_post_id_from_index( $skope_id );

    if ( !is_int( $post_id ) ) {
        error_log( 'sek_get_seks_post => post_id !is_int() for options => ' . NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id );
    }
    // if the options has not been set yet, it will return (int) 0
    // id #1 is already taken by the 'Hello World' post.
    // skip this check when in NIMBLE_CPT_DEBUG_MODE
    if ( 1 > $post_id && !( defined( "NIMBLE_CPT_DEBUG_MODE" ) && NIMBLE_CPT_DEBUG_MODE ) ) {
        //error_log( 'sek_get_seks_post => post_id is not valid for options => ' . NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id );
        return;
    }

    if ( is_int( $post_id ) && $post_id > 0 ) {
        $post = get_post( $post_id );
    }

    $no_post_found = !$post || -1 !== $post_id;
    $post_trashed = !empty($post) && is_object($post) && 'trash' === $post->post_status;

    // `-1` indicates no post exists; no query necessary.
    // always query post when in NIMBLE_CPT_DEBUG_MODE
    if ( $no_post_found || $post_trashed ) {
        $query = new \WP_Query( $sek_post_query_vars );
        $post = $query->post;
        $post_id = $post ? $post->ID : -1;
        /*
         * Cache the lookup. See sek_update_sek_post().
         * @todo This should get cleared if a skope post is added/removed.
         */
        sek_set_nb_post_id_in_index( $skope_id, (int)$post_id );
    }
    
    if ( !skp_is_customizing() ) {
        $cached_seks_posts[$skope_id] = $post;
        Nimble_Manager()->seks_posts = $cached_seks_posts;
        return $cached_seks_posts[$skope_id];
    } else {
        return $post;
    }
}


function sek_set_ids( $collection ) {
    if ( is_array( $collection ) ) {
        // if ( array_key_exists('level', $collection ) && in_array( $collection['level'], ['section', 'column', 'module'] ) && array_key_exists('id', $collection ) ) {
        //     $collection['id'] = sek_generate_level_guid();
        // }
        foreach( $collection as $key => $data ) {
            if ( '__rep__me__' === $data && 'id' === $key ) {
                $collection[$key] = sek_generate_level_guid();
            } else if ( is_array( $data ) ) {
                $collection[$key] = sek_set_ids($data);
            }
        }
    }
    return $collection;
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
    $cache_key = 'nimble_get_skoped_seks_' . $skope_id;
    $cached = wp_cache_get( $cache_key );

    // use the cached value when available ( after did_action('wp') )
    if ( did_action('wp') ) {
        if ( is_array( $cached ) ) {
            $is_cached = true;
            $seks_data = $cached;
        }
    }

    // If not cached get the seks data from the skoped post
    if ( !$is_cached ) {
        $default_collection = sek_get_default_location_model( $skope_id );
        // Feb 2021 : filter skope id now
        // if the current context has no local sektions set and a site template set, replace the skope id by the group skope id
        // if ( !$is_global_skope ) {
        //     $skope_id = apply_filters( 'nb_set_skope_id_before_caching_local_sektions', $skope_id );
        //     //sek_error_log('alors local skope id for fetching local sections ?', $skope_id );
        // }
        $seks_data = sek_get_seks_without_group_inheritance( $skope_id );
        
        // March 2021 : added for site templates #478
        // Use site template if
        // - ! global skope
        // - no local skoped sections
        // - a site template is defined for this "group" skope
        if ( 'local' === $skope_level && !$is_global_skope ) {
            $seks_data = sek_maybe_get_seks_for_group_site_template( $skope_id, $seks_data );
        }

        // normalizes
        // [ 'collection' => [], 'local_options' => [], "fonts": [], '__inherits_group_skope_tmpl_when_exists__' => true ];
        $seks_data = wp_parse_args( $seks_data, $default_collection );

        // Maybe add missing registered locations
        $seks_data = sek_maybe_add_incomplete_locations( $seks_data, $is_global_skope );

        // cache now 
        wp_cache_set( $cache_key, $seks_data );
        //sek_error_log('/////////////////////////// CACHED for skope ' . $skope_id);
    }//end if

    if ( skp_is_customizing() ) {
        // when customizing, let us filter the value with the 'customized' ones
        $seks_data = apply_filters(
            'sek_get_skoped_seks',
            $seks_data,
            $skope_id,
            $location_id
        );

        if ( 'local' === $skope_level && !$is_global_skope ) {
            $seks_data = is_array( $seks_data ) ? $seks_data : array();
            if ( !array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $seks_data ) ) {
                // Retro-compat => make sure we set property '__inherits_group_skope_tmpl_when_exists__' to false if it's not set yet, because NB bases group inheritance on it
                $seks_data['__inherits_group_skope_tmpl_when_exists__'] = false;
            }
            $seks_data = sek_maybe_get_seks_for_group_site_template( $skope_id, $seks_data );
        }

        $default_collection = sek_get_default_location_model( $skope_id );
        $seks_data = wp_parse_args( $seks_data, $default_collection );
        // Maybe add missing registered locations when customizing
        // December 2020 => needed when importing an entire template
        $seks_data = sek_maybe_add_incomplete_locations( $seks_data, $is_global_skope );
    }

    // if a location is specified, return specifically the sections of this location
    if ( array_key_exists( 'collection', $seks_data ) && !empty( $location_id ) ) {
        // sek_error_log( 'sek_get_skoped_seks() location => ' . $location_id .  array_key_exists( 'collection', $seks_data ) );
        if ( !array_key_exists( $location_id, sek_get_locations() ) ) {
            error_log( __FUNCTION__ . ' Error => location ' . $location_id . ' is not registered in the available locations' );
        } else {
            $seks_data = sek_get_level_model( $location_id, $seks_data['collection'] );
        }
    }

    //sek_error_log( __FUNCTION__ . ' THEERE !');

    return 'no_match' === $seks_data ? Nimble_Manager()->default_location_model : $seks_data;
}

// Return and cache the local or group skope seks data
// Without inheritance because not filtered with the group site template content
function sek_get_seks_without_group_inheritance( $skope_id ) {
    if ( empty($skope_id) || !is_string($skope_id) ) {
        sek_error_log( 'Error missing skope id');
        return [];
    }

    $cache_key = 'nimble_seks_data_for_skope_' . $skope_id;
    $cached = wp_cache_get( $cache_key );
    if ( is_array($cached) ) {
        return $cached;
    }

    $is_global_skope = NIMBLE_GLOBAL_SKOPE_ID === $skope_id;
    $seks_data = array();
    $post = sek_get_seks_post( $skope_id, $is_global_skope ? 'global' : 'local' );//Cached

    if ( $post ) {
        $seks_data = maybe_unserialize( $post->post_content );
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        if ( !$is_global_skope && !array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $seks_data ) ) {
            // Retro-compat => make sure we set property '__inherits_group_skope_tmpl_when_exists__' to false if it's not set yet, because NB bases group inheritance on it
            $seks_data['__inherits_group_skope_tmpl_when_exists__'] = false;
        }
    }

    // normalizes
    // [ 'collection' => [], 'local_options' => [], "fonts": [], '__inherits_group_skope_tmpl_when_exists__' => true ];
    $default_collection = sek_get_default_location_model( $skope_id );
    $seks_data = wp_parse_args( $seks_data, $default_collection );
    wp_cache_set( $cache_key, $seks_data );
    return $seks_data;
}




// make sure the locations in the skoped locations tree match the registered locations for the context
function sek_maybe_add_incomplete_locations( $seks_data, $is_global_skope ) {
    // Maybe add missing registered locations
    $maybe_incomplete_locations = [];
    foreach( $seks_data['collection'] as $location_data ) {
        if ( !empty( $location_data['id'] ) ) {
            $maybe_incomplete_locations[] = $location_data['id'];
        }
    }

    foreach( sek_get_locations() as $loc_id => $params ) {
        if ( !in_array( $loc_id, $maybe_incomplete_locations ) ) {
            if ( ( sek_is_global_location( $loc_id ) && $is_global_skope ) || ( !sek_is_global_location( $loc_id ) && !$is_global_skope  ) ) {
                $seks_data['collection'][] = wp_parse_args( [ 'id' => $loc_id ], Nimble_Manager()->default_location_model );
            }
        }
    }
    return $seks_data;
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

    if ( !is_array( $seks_data ) ) {
        error_log( 'sek_update_sek_post => $seks_data is not an array' );
        return new \WP_Error( 'sek_update_sek_post => $seks_data is not an array');
    }

    $skope_id = $args['skope_id'];
    if ( empty( $skope_id ) ) {
        error_log( 'sek_update_sek_post => empty skope_id' );
        return new \WP_Error( 'sek_update_sek_post => empty skope_id');
    }

    $post_title = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;

    //sek_error_log('IN UPDATE SEK_POST ?', $seks_data );


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
        //sek_error_log('IINSERT NEW POST ', $post_title );
        $r = wp_insert_post( wp_slash( $post_data ), true );
        if ( !is_wp_error( $r ) ) {
            $post_id = $r;//$r is the post ID

            sek_set_nb_post_id_in_index( $skope_id, (int)$post_id ); 

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

// Introduced March 2021 for #478
// Removes the post id in the skope index + removes the post in DB + remove the stylesheet
function sek_remove_seks_post( $skope_id = null ) {
    if ( is_null( $skope_id ) || empty( $skope_id ) ) {
        sek_error_log( __FUNCTION__  . ' => error => empty skope_id' );
        return new \WP_Error( 'sek_update_sek_post => empty skope_id');
    }

    //$post_title = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    $tmpl_post_to_remove = sek_get_seks_post( $skope_id );

    //sek_error_log( __FUNCTION__ . ' => so $tmpl_post_to_remove => ' . $skope_id, $tmpl_post_to_remove );
    // Remove the associated post id in the skope index
    sek_remove_nb_post_id_in_index( $skope_id );

    // Remove the post in DB
    if ( $tmpl_post_to_remove && is_object( $tmpl_post_to_remove ) ) {
        // the CPT is permanently deleted instead of moved to Trash when using wp_trash_post()
        $r = wp_delete_post( $tmpl_post_to_remove->ID );
        if ( is_wp_error( $r ) ) {
            sek_error_log( __FUNCTION__ . '=> _removal_error', $r );
        }
    } else {
        // TMPL POST NOT FOUND
        //sek_error_log( __FUNCTION__ . '=> _tmpl_post_not_found' );
    }

    // Remove the corresponding stylesheet
    $css_handler_instance = new Sek_Dyn_CSS_Handler( array(
        'id'             => $skope_id,
        'skope_id'       => $skope_id,
        'mode'           => 'delete'
    ));
    $css_handler_instance->sek_dyn_css_delete_file();
}

?><?php
///////////////////////////////////////////////////////
/// SITE TEMPLATES
// Feb 2021 => experimental for https://github.com/presscustomizr/nimble-builder/issues/478


/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES OPTIONS HELPERS
/* ------------------------------------------------------------------------- */
// @return array of site template info for a given group skope
function sek_get_site_tmpl_params_for_skope( $group_skope = null ) {
    if ( is_null($group_skope) || !is_string($group_skope) || empty($group_skope) )
        return;
    $cache_key = 'nimble_site_tmpl_params_for_skope_'.$group_skope;
    $cached = wp_cache_get( $cache_key );
    if ( false !== $cached )
        return $cached;

    $default_params = [
        'site_tmpl_id' => '_no_site_tmpl_',
        'site_tmpl_source' => 'user_tmpl',
        'site_tmpl_title' => ''
    ];
    $opts = sek_get_global_option_value( 'site_templates' );
    $site_tmpl_params = $default_params;
    if ( is_array( $opts) && !empty( $opts[$group_skope] ) && is_array($opts[$group_skope]) ) {
        $site_tmpl_params = wp_parse_args( $opts[$group_skope], $site_tmpl_params );
    }

    // DO SOME CHECKS BEFORE RETURNING THE PARAMS
    $site_tmpl_id = $site_tmpl_params['site_tmpl_id'];
    $site_tmpl_source = $site_tmpl_params['site_tmpl_source'];
    // Check that id is well formed
    if ( is_null( $site_tmpl_id ) || !is_string( $site_tmpl_id ) ) {
        sek_error_log( 'Error => invalid tmpl post id', $tmpl_params );
        return $default_params;
    }
    //sek_error_log('site_templates params ?' . $group_skope, $opts );
    // Check that tmpl source is OK
    if ( !in_array($site_tmpl_source, ['user_tmpl', 'api_tmpl'] ) ) {
        sek_error_log( 'Error => invalid tmpl source', $tmpl_params );
        return $default_params;
    }
    wp_cache_set($cache_key, $site_tmpl_params);
    return $site_tmpl_params;
}

/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES SKOPE HELPER
/* ------------------------------------------------------------------------- */
// when registering site template global options the suffix '_for_site_tmpl' is added to 'no group skope' scopes : 'skp__search_for_site_tmpl', 'skp__404_for_site_tmpl', 'skp__date_for_site_tmpl'
// see sek_get_module_params_for_sek_site_tmpl_pickers()
function sek_get_group_skope_for_site_tmpl() {
    $group_skope = skp_get_skope_id( 'group' );
    if ( '_skope_not_set_' === $group_skope ) {
        $skope_id = skp_get_skope_id();
        if ( sek_is_no_group_skope( $skope_id ) ) {
            $group_skope = $skope_id . '_for_site_tmpl';
        } else {
            if ( defined('NIMBLE_DEV') && NIMBLE_DEV ) {
                //sek_error_log('group skope could not be set');
            }
        }
    }
    return $group_skope;
}

// @return bool
// no group skope are array( 'home', 'search', '404', 'date' );
function sek_is_no_group_skope( $skope_id = null ) {
    if ( is_null( $skope_id ) ) {
        $skope_id = skp_get_skope_id();
    }
    $skope_id_without_prefix = str_replace( 'skp__', '', $skope_id );
    $skope_with_no_group = skp_get_no_group_skope_list();
    return in_array( $skope_id_without_prefix, $skope_with_no_group );
}

//@return bool
// Tells if the local NB skope has been customized
function sek_local_skope_has_been_customized( $skope_id = '', $local_seks_data = null ) {
    $skope_id = empty( $skope_id ) ? skp_get_skope_id() : $skope_id;

    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return false;
    }

    // When the collection is provided use it otherwise get it
    if ( is_null($local_seks_data) || !is_array($local_seks_data) ) {
        $local_seks_data = sek_get_skoped_seks( $skope_id );
    }
    // normally, we should get an array from the previous function
    if ( !is_array( $local_seks_data ) )
        return false;
    // the local skoped data include property '__inherits_group_skope_tmpl_when_exists__' since site template implementation april 2021
    // If not, it means that we may have a local customized skoped data
    if ( is_array($local_seks_data) && !array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $local_seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' => error => missing property __inherits_group_skope_tmpl_when_exists__' );
        return true;
    }
    // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
    // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
    // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control::resetCollectionSetting )
    // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
    return is_array($local_seks_data) && array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $local_seks_data ) && !$local_seks_data['__inherits_group_skope_tmpl_when_exists__'];
}

//@return bool
function sek_is_static_front_page_on_front_and_when_customizing() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
        $is_front_page = sek_get_posted_query_param_when_customizing( 'is_front_page' );
    } else {
        $is_front_page = is_front_page();
    }
    return $is_front_page && 'page' == get_option( 'show_on_front' );
}

// @return boolean
// solves the problem of preventing group template inheritance after a local reset
function sek_is_inheritance_locally_disabled() {
    $local_reset_data = sek_get_local_option_value_without_inheritance( 'local_reset' );
    if ( is_array( $local_reset_data ) && array_key_exists( 'inherit_group_scope', $local_reset_data ) && !sek_booleanize_checkbox_val($local_reset_data['inherit_group_scope'] ) ) {
        return true;
    }
    return false;
}


/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES CSS
/* ------------------------------------------------------------------------- */
// filter declared in inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
//@hook 'nb_set_skope_id_before_generating_local_front_css'
function sek_set_skope_id_before_generating_local_front_css($skope_id) {
    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return $skope_id;
    }
    // if is viewing front page, we don't want to inherit 'skp__all_page' scope
    if ( sek_is_static_front_page_on_front_and_when_customizing() )
        return $skope_id;

    // checkbox 'inherit_group_scope' set to true by default
    if ( sek_is_inheritance_locally_disabled() ) {
        return $skope_id;
    }

    // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
    // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
    // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
    // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
    if ( !sek_local_skope_has_been_customized( $skope_id ) ) {
        $group_site_tmpl_data = sek_get_group_site_template_data();//<= is cached when called
        $has_group_skope_template_data = !( !$group_site_tmpl_data || empty($group_site_tmpl_data) );
        if ( $has_group_skope_template_data ) {
            $group_skope = sek_get_group_skope_for_site_tmpl();
            if ( !empty($group_skope) && '_skope_not_set_' !== $group_skope ) {
                $skope_id = $group_skope;
            }
        }
    }
    return $skope_id;
}
add_filter( 'nb_set_skope_id_before_generating_local_front_css', '\Nimble\sek_set_skope_id_before_generating_local_front_css');



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES CONTENT
/* ------------------------------------------------------------------------- */
// Called in sek_get_skoped_seks()
function sek_maybe_get_seks_for_group_site_template( $skope_id, $local_seks_data ) {
    // NB will only inherit group skope for local sektions
    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return $local_seks_data;
    }
    // if is viewing front page, we don't want to inherit 'skp__all_page' scope
    if ( sek_is_static_front_page_on_front_and_when_customizing() )
        return $local_seks_data;

    // checkbox 'inherit_group_scope' set to true by default
    if ( sek_is_inheritance_locally_disabled() ) {
        return $local_seks_data;
    }
    // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
    // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
    // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
    // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
    if ( sek_local_skope_has_been_customized($skope_id, $local_seks_data) )  {
        return $local_seks_data;
    }

    $group_site_tmpl_data = sek_get_group_site_template_data();

    if ( !$group_site_tmpl_data || empty($group_site_tmpl_data) )
        return $local_seks_data;

    return $group_site_tmpl_data;      
}

// @return null || array
// get and cache the group site template data
function sek_get_group_site_template_data( $group_skope = null ) {
    // When ajaxing while customizing, no need to get the group site template data
    if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX )
        return;
    $group_site_tmpl_data = [];
    if ( is_null( $group_skope ) ) {
        $group_skope = sek_get_group_skope_for_site_tmpl();
    }
    $cache_key = 'nimble_group_site_template_data_' . $group_skope;
    $cached = wp_cache_get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }
    // Site template params are structured as follow :
    // [
    //     'site_tmpl_id' : '_no_site_tmpl_',
    //     'site_tmpl_source' : 'user_tmpl',
    //     'site_tmpl_title' : ''
    //];
    $tmpl_params = sek_get_site_tmpl_params_for_skope( $group_skope );
    if ( '_no_site_tmpl_' === $tmpl_params['site_tmpl_id'] ) {
        wp_cache_set( $cache_key, $group_site_tmpl_data );
        return;
    }

    $site_tmpl_id = $tmpl_params['site_tmpl_id'];
    $site_tmpl_source = $tmpl_params['site_tmpl_source'];

    // Is this group template already saved ?
    // For example, for pages, there should be a nimble CPT post named nimble___skp__all_page
    $post = sek_get_seks_post( $group_skope );

    // if not, let's insert it
    if ( !$post ) {
        $current_tmpl_post = null;
        $current_tmpl_data = null;
        switch ($site_tmpl_source) {
            case 'user_tmpl':
                $current_tmpl_post = sek_get_saved_tmpl_post( $site_tmpl_id );
                if ( $current_tmpl_post ) {
                    $raw_tmpl_data = maybe_unserialize( $current_tmpl_post->post_content );
                    if ( is_array($raw_tmpl_data) && isset($raw_tmpl_data['data']) && is_array($raw_tmpl_data['data']) && !empty($raw_tmpl_data['data']) ) {
                        $current_tmpl_data = $raw_tmpl_data['data'];
                        $current_tmpl_data = sek_set_ids( $current_tmpl_data );
                    }
                }
            break;

            case 'api_tmpl':
                $raw_tmpl_data = sek_get_single_tmpl_api_data( $site_tmpl_id );
                if( !is_array( $raw_tmpl_data) || empty( $raw_tmpl_data ) ) {
                    sek_error_log( ' problem when getting template : ' . $site_tmpl_id );
                }
                //sek_error_log( __FUNCTION__ . ' api template collection', $raw_tmpl_data );
                if ( !isset($raw_tmpl_data['data'] ) || empty( $raw_tmpl_data['data'] ) ) {
                    sek_error_log( __FUNCTION__ . ' problem => missing or invalid data property for template : ' .$site_tmpl_id, $raw_tmpl_data );
                } else {
                    // $tmpl_decoded = $raw_tmpl_data;
                    $raw_tmpl_data['data'] = sek_maybe_import_imgs( $raw_tmpl_data['data'], $do_import_images = true );
                    //$raw_tmpl_data['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
                    $current_tmpl_data = sek_set_ids( $raw_tmpl_data['data'] );
                }
            break;
        }

        if( !is_null($current_tmpl_data) ) {
            //sek_error_log('SITE TEMPLATE => UPDATE OR INSERT GROUP SKOPE POST => ' .$group_skope );
            $post = sek_update_sek_post( $current_tmpl_data, [ 'skope_id' => $group_skope ]);
        }
    }//if ( !$post ) {

    if ( $post ) {
        $group_site_tmpl_data = maybe_unserialize( $post->post_content );
    }
    wp_cache_set( $cache_key, $group_site_tmpl_data );
    return $group_site_tmpl_data;
}


// @return bool
function sek_has_group_site_template_data() {
    $group_skope = sek_get_group_skope_for_site_tmpl();
    $cache_key = 'nimble_has_group_site_template_' . $group_skope;
    $cached = wp_cache_get($cache_key);
    if (  'yes' === $cached || 'no' === $cached ) {
        return 'yes' === $cached;
    }
    
    $group_site_tmpl_data = sek_get_group_site_template_data();//<= is cached when called
    $has_group_skope_template_data = !( !$group_site_tmpl_data || empty($group_site_tmpl_data) );
    wp_cache_set( $cache_key, $has_group_skope_template_data  ? 'yes' : 'no' );
    return $has_group_skope_template_data;
}


/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES SAVE CUSTOMIZER ACTION
/* ------------------------------------------------------------------------- */
// Action declared in class Nimble_Options_Setting
// When a site template is modified, the following action removes the skoped post + removes the corresponding CSS stylesheet
// For example, when the page site template is changed, we need to remove the associated skoped post named 'nimble___skp__all_page'
// This post has been inserted when running sek_maybe_get_seks_for_group_site_template(), fired from sek_get_skoped_seks()
//@'nb_on_save_customizer_global_options'
function sek_on_save_customizer_global_options( $opt_name, $value ) {
    $current_site_tmpl_opts = sek_get_global_option_value( 'site_templates' );
    if ( !is_array( $value ) || !is_array($current_site_tmpl_opts) )
        return;
    
    // NB stores the site template id as a concatenation of template source + '___' + template name
    // Ex : user_tmpl___landing-page-for-services
    $updated_site_templates = isset($value['site_templates']) ? $value['site_templates'] : [];

    foreach( $current_site_tmpl_opts as $group_skope => $current_tmpl_params ) {
        if ( !is_array($current_tmpl_params) || !array_key_exists('site_tmpl_id', $current_tmpl_params ) )
            continue;
        $current_tmpl_id = $current_tmpl_params['site_tmpl_id'];
        $updated_tmpl_params = [];
        if ( array_key_exists( $group_skope, $updated_site_templates ) ) {
            $updated_tmpl_params = $updated_site_templates[$group_skope];
        } else {
            //sek_error_log('GROUP SKOPE POST TO REMOVE BECAUSE NO MORE TEMPLATE SET => ' . $group_skope . ' | ' . $current_tmpl_id );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
        if ( is_array($updated_tmpl_params) && array_key_exists('site_tmpl_id', $updated_tmpl_params ) && $updated_tmpl_params['site_tmpl_id'] != $current_tmpl_params['site_tmpl_id'] ) {
            //sek_error_log('GROUP SKOPE POST TO REMOVE BECAUSE TEMPLATE UPDATED => ' . $group_skope . ' | ' . $updated_site_templates[$group_skope] );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
    }
}
add_action('nb_on_save_customizer_global_options', '\Nimble\sek_on_save_customizer_global_options', 10, 2);



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES : UPDATED TEMPLATE IN CUSTOMIZER
/* ------------------------------------------------------------------------- */
// Action fired during server ajax callback sek_update_user_tmpl_post
// Solves the problem of template synchronization between the group skope post ( in which the chosen template is saved with permanent level ids ), and the current state of the template
// Solution => each time a template is updated, NB checks if the template is being used by a group skope
// if so, then the group skope post is removed ( along with the index and the css stylesheet )
// 
// When will the removed skope post be re-inserted ?
// next time the group skope will be printed ( for example skp__all_page in a single page ), NB checks if a template is assigned to this group skope, and tries to get the skope post.
// If the group skope post is not found, NB attempts to re-insert it
//@hook 'nb_on_update_user_tmpl_post'
function sek_on_update_or_remove_user_tmpl_post( $site_tmpl_id ) {
    if ( is_null( $site_tmpl_id ) || !is_string( $site_tmpl_id ) )
        return;

    $site_tmpl_opts = sek_get_global_option_value( 'site_templates' );
    if ( !is_array($site_tmpl_opts) )
        return;

    foreach( $site_tmpl_opts as $group_skope => $tmpl_params ) {
        if ( !is_array($tmpl_params) || !array_key_exists('site_tmpl_id', $tmpl_params ) )
            continue;

        if ( $site_tmpl_id === $tmpl_params['site_tmpl_id'] ) {
            //sek_error_log('UPDATED OR REMOVED TEMPLATE => REMOVE GROUP SKOPE POST ' . $group_skope . ' for template ' . $tmpl_name );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
    }
}
add_action('nb_on_update_user_tmpl_post', '\Nimble\sek_on_update_or_remove_user_tmpl_post', 10, 1);
add_action('nb_on_remove_saved_tmpl_post', '\Nimble\sek_on_update_or_remove_user_tmpl_post', 10, 1);

?><?php
/* ------------------------------------------------------------------------- *
 *  SAVED SECTIONS
/* ------------------------------------------------------------------------- */
// SAVED SECTION POST TYPE
// CPT for section : 'nimble_section'
register_post_type( NIMBLE_SECTION_CPT , array(
    'labels' => array(
      'name'          => sek_is_cpt_debug_mode() ? __( '[NB debug] user prebuilt sections') : __( 'NB user prebuilt sections'),
      'singular_name' => __( 'NB User prebuilt sections')
    ),
    'public'           => sek_is_cpt_debug_mode(),
    'hierarchical'     => false,
    'rewrite'          => false,
    'query_var'        => false,
    'delete_with_user' => false,
    'can_export'       => true,
    //'_builtin'         => true, /* internal use only. don't use this when registering your own post type. */
    'supports'         => sek_is_cpt_debug_mode() ? array( 'editor', 'title', 'revisions' ) : array( 'title', 'revisions' ),
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
 * Fetch the 'nimble_section' post for a given post_name
 *
 * @return WP_Post|null
 */
function sek_get_saved_section_post( $section_post_name ) {
    $cache_group = 'nimble_section_post';
    $section_post = wp_cache_get( $section_post_name, $cache_group );
    // is it cached already ?
    if ( $section_post && is_object($section_post) && NIMBLE_SECTION_CPT === get_post_type( $section_post->id ) ) {
      return $section_post;
    }

    $section_post_query = new \WP_Query(
      array(
        'post_type'              => NIMBLE_SECTION_CPT,
        'post_status'            => get_post_stati(),
        'name'                   => sanitize_title( $section_post_name ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
      )
    );
    if ( !empty( $section_post_query->posts ) ) {
        $section_post = $section_post_query->posts[0];
        wp_cache_set( $section_post_name, $section_post, $cache_group );
        return $section_post;
    }

    return null;
}


// // @return the saved section data collection
// // NOT USED
// function sek_get_saved_section_data( $section_post_name ) {
//     $sek_post = sek_get_saved_section_post( $section_post_name );
//     $section_data = array();
//     if ( $sek_post ) {
//         $section_data_decoded = maybe_unserialize( $sek_post->post_content );
//         // The section data are described as an array
//         // array(
//         //     'title' => '',
//         //     'description' => '',
//         //     'id' => '',
//         //     'type' => 'content',//in the future will be used to differentiate header, content and footer sections
//         //     'creation_date' => date("Y-m-d H:i:s"),
//         //     'update_date' => '',
//         //     'data' => array(),<= this is where we describe the columns and options
//         //     'nimble_version' => NIMBLE_VERSION
//         // )
//         if ( is_array( $section_data_decoded ) && !empty( $section_data_decoded['data'] ) && is_string( $section_data_decoded['data'] ) ) {
//             $section_data = json_decode( wp_unslash( $section_data_decoded['data'], true ) );
//         }
//     }
//     return $section_data;
// }


// invoked on 'wp_ajax_sek_get_user_saved_sections'
// @return an unserialized array of all sections saved by user
function sek_get_all_saved_sections() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_SECTION_CPT,
        'post_status'            => 'publish',
        //'name'                   => sanitize_title(),
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
    $collection = array();
    if ( is_wp_error( $query ) ) {
        wp_send_json_error(  __FUNCTION__ . '_error_when_querying_nimble_sections' );
    }
    if ( !is_array( $query->posts ) || empty( $query->posts ) ) {
      return $collection;
    }

    foreach ( $query->posts as $post_object ) {
        $content = maybe_unserialize( $post_object->post_content );
        // Structure of $content :
        // array(
        //     'data' => $_POST['section_data'],//<= json stringified
        //     'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? $_POST['section_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['section_title'],
        //         'description' => $_POST['section_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'active_locations' => is_string( $_POST['active_locations'] ) ? explode( ',', $_POST['active_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( !is_array($content) ) {
            sek_error_log(__FUNCTION__ . ' error in content structure for section post name : ' . $post_object->post_name );
            continue;
        }
        if ( empty($content['metas']) ) {
            sek_error_log(__FUNCTION__ . ' error => missing metas for section post name : ' . $post_object->post_name );
            continue;
        }

        // When updating a section, we only need to return title and description
        $collection[$post_object->post_name] = array(
            'title' => !empty($content['metas']['title']) ? sek_maybe_decode_richtext( $content['metas']['title'] ) : '',
            'description' => !empty($content['metas']['description']) ? sek_maybe_decode_richtext( $content['metas']['description'] ) : '',
            'last_modified_date' => mysql2date( 'Y-m-d H:i:s', $post_object->post_modified )
        );
    }

    return $collection;
}



 // Update the 'nimble_section' post
 // Inserts a 'nimble_section' post when one doesn't yet exist.
 // $section_data = array(
  //     'data' => $_POST['section_data'],//<= json stringified
  //     'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? $_POST['section_post_name'] : null,
  //     'metas' => array(
  //         'title' => $_POST['section_title'],
  //         'description' => $_POST['section_description'],
  //         'skope_id' => $_POST['skope_id'],
  //         'version' => NIMBLE_VERSION,
  //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
  //         'active_locations' => is_string( $_POST['active_locations'] ) ? explode( ',', $_POST['active_locations'] ) : array(),
  //         'date' => date("Y-m-d"),
  //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
  //     )
  // );
// @return WP_Post|WP_Error Post on success, error on failure.
function sek_update_saved_section_post( $section_data, $is_edit_metas_only_case = false ) {
    if ( !is_array( $section_data ) ) {
        sek_error_log( __FUNCTION__ . ' => $section_data is not an array' );
        return new \WP_Error( __FUNCTION__ . ' => $section_data is not an array');
    }

    if ( !isset( $section_data['data']) || !isset( $section_data['metas']) ) {
        sek_error_log( __FUNCTION__ . ' => invalid $section_data' );
        return new \WP_Error( __FUNCTION__ . ' => invalid $section_data');
    }

    $section_data = wp_parse_args( $section_data, array(
        'data' => array(),
        'section_post_name' => null,
        'metas' => array(
            'title' => '',
            'description' => '',
            'skope_id' => '',
            'version' => NIMBLE_VERSION,
            //'active_locations' => array(),
            'date' => '',
            'theme' => ''
        )
    ));

    // the section post name is provided only when updating
    $is_update_case = !is_null($section_data['section_post_name']);

    // $post_name_to_update will be used when user updates an existing section
    if ( !is_null($section_data['section_post_name']) ) {
        $section_post_name = $section_data['section_post_name'];
    } else {
        $section_post_name = NIMBLE_PREFIX_FOR_SAVED_SECTION .  sanitize_title( $section_data['metas']['title'] );//nb_section_my-section-name
    }

    // Update the post name now
    $section_data['section_post_name'] = $section_post_name;

    // Update post if it already exists, otherwise create a new one.
    $current_section_post = null;
    if ( $is_update_case ) {
        // When this is an update case, we fetch the existing tmpl_post in order to later get its id
        $current_section_post = sek_get_saved_section_post( $section_post_name );

        // if this is an update case + editing metas only, then we use the current content
        if ( $is_edit_metas_only_case && isset($current_section_post->post_content) ) {
            //sek_error_log('IS EDIT METAS ONLY ?');
            $current_section_data = maybe_unserialize( $current_section_post->post_content );
            if ( is_array($current_section_data) && isset($current_section_data['data']) && is_array($current_section_data['data']) && !empty($current_section_data['data']) ) {
                $section_data['data'] = $current_section_data['data'];
            }
        }
    }

    // March 2021 : make sure text input are sanitized like in #544 #792
    $section_data = sek_sektion_collection_sanitize_cb( $section_data );

    $new_or_updated_post_data = array(
        'post_title' => $section_post_name,
        'post_name' => $section_post_name,
        'post_type' => NIMBLE_SECTION_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $section_data )
    );

    

    if ( $current_section_post && is_object($current_section_post) ) {
        $new_or_updated_post_data['ID'] = $current_section_post->ID;
        $r = wp_update_post( wp_slash( $new_or_updated_post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $new_or_updated_post_data ), true );
        if ( !is_wp_error( $r ) ) {
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
 *  SAVED TEMPLATES
/* ------------------------------------------------------------------------- */
// SAVED TEMPLATES POST TYPE
// CPT for template : 'nimble_template'
register_post_type( NIMBLE_TEMPLATE_CPT , array(
    'labels' => array(
      'name'          => sek_is_cpt_debug_mode() ? __( '[NB debug] user templates') : __( 'NB user templates'),
      'singular_name' => __( 'NB user templates')
    ),
    'public'           => sek_is_cpt_debug_mode(),
    'hierarchical'     => false,
    'rewrite'          => false,
    'query_var'        => false,
    'delete_with_user' => false,
    'can_export'       => true,
    //'_builtin'         => true, /* internal use only. don't use this when registering your own post type. */
    'supports'         => sek_is_cpt_debug_mode() ? array( 'editor', 'title', 'revisions' ) : array( 'title', 'revisions' ),
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
    $cache_group = 'nimble_template_post';
    $template_post = wp_cache_get( $tmpl_post_name, $cache_group );
    // is it cached already ?
    if ( $template_post && is_object($template_post) && NIMBLE_TEMPLATE_CPT === get_post_type( $template_post->id ) ) {
      return $template_post;
    }

    $tmpl_post_query = new \WP_Query(
      array(
        'post_type'              => NIMBLE_TEMPLATE_CPT,
        'post_status'            => get_post_stati(),
        'name'                   => sanitize_title( $tmpl_post_name ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
      )
    );
    if ( !empty( $tmpl_post_query->posts ) ) {
        $template_post = $tmpl_post_query->posts[0];
        wp_cache_set( $tmpl_post_name, $template_post, $cache_group );
        return $template_post;
    }

    return null;
}



// // @return the saved template data collection
// // NOT USED
// function sek_get_saved_template_data( $tmpl_post_name ) {
//     $sek_post = sek_get_saved_template_post( $tmpl_post_name );
//     $tmpl_data = array();
//     if ( $sek_post ) {
//         $tmpl_data_decoded = maybe_unserialize( $sek_post->post_content );
//         // The section data are described as an array
//         // array(
//         //     'title' => '',
//         //     'description' => '',
//         //     'id' => '',
//         //     'type' => 'content',//in the future will be used to differentiate header, content and footer sections
//         //     'creation_date' => date("Y-m-d H:i:s"),
//         //     'update_date' => '',
//         //     'data' => array(),<= this is where we describe the columns and options
//         //     'nimble_version' => NIMBLE_VERSION
//         // )
//         if ( is_array( $tmpl_data_decoded ) && !empty( $tmpl_data_decoded['data'] ) && is_string( $tmpl_data_decoded['data'] ) ) {
//             $tmpl_data = json_decode( wp_unslash( $tmpl_data_decoded['data'], true ) );
//         }
//     }
//     return $tmpl_data;
// }


// invoked on 'wp_ajax_sek_get_user_saved_templates'
// @return an unserialized array of all templates saved by user
function sek_get_all_saved_templates() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_TEMPLATE_CPT,
        'post_status'            => 'publish',
        //'name'                   => sanitize_title(),
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
    $collection = array();
    if ( is_wp_error( $query ) ) {
        wp_send_json_error(  __FUNCTION__ . '_error_when_querying_nimble_templates' );
    }
    if ( !is_array( $query->posts ) || empty( $query->posts ) ) {
      return $collection;
    }

    foreach ( $query->posts as $post_object ) {
        $content = maybe_unserialize( $post_object->post_content );
        // sek_error_log( __FUNCTION__ . ' TYPE ?', gettype($post_object->post_content ) );
        // sek_error_log( __FUNCTION__ . ' POST OBJECT ?', $post_object->post_content );
        // Structure of $content :
        // array(
        //     'data' => $_POST['tmpl_data'],//<= json stringified
        //     'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['tmpl_title'],
        //         'description' => $_POST['tmpl_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'tmpl_locations' => is_string( $_POST['tmpl_locations'] ) ? explode( ',', $_POST['tmpl_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( !is_array($content) ) {
            sek_error_log(__FUNCTION__ . ' error in content structure for template post name : ' . $post_object->post_name );
            continue;
        }
        if ( empty($content['metas']) ) {
            sek_error_log(__FUNCTION__ . ' error => missing metas for template post name : ' . $post_object->post_name );
            continue;
        }

        // When updating a template, we only need to return title and description
        $collection[$post_object->post_name] = array(
            'title' => !empty($content['metas']['title']) ? sek_maybe_decode_richtext( $content['metas']['title'] ) : '',
            'description' => !empty($content['metas']['description']) ? sek_maybe_decode_richtext( $content['metas']['description'] ) : '',
            'last_modified_date' => mysql2date( 'Y-m-d H:i:s', $post_object->post_modified )
        );
    }
    //sek_error_log('GET ALL SAVED TMPL', $collection );
    return $collection;
}


// invoked on 'wp_ajax_sek_get_all_api_tmpl'
// @return an unserialized array of api templates
function sek_get_all_api_templates() {
    $raw_tmpl = sek_get_all_tmpl_api_data();
    $collection = [];

    if( !is_array( $raw_tmpl) )
        return $collection;
        
    foreach ( $raw_tmpl as $tmpl_cpt_post_name => $metas) {
        if ( !is_array( $metas ) || empty($metas) )
            continue;

        $metas = wp_parse_args( $metas, [
            'title' => '',
            'description' => '',
            'date' => '',
            'thumb_url' => '',
            'is_pro_tmpl' => false,
            'demo_url' => false,
            'is_site_tmpl' => false
        ]);

        $collection[$tmpl_cpt_post_name] = [
            'title' => sek_maybe_decode_richtext( $metas['title'] ),
            'description' => sek_maybe_decode_richtext( $metas['description'] ),
            'last_modified_date' => mysql2date( 'Y-m-d', $metas['date'] ),
            'thumb_url' => !empty( $metas['thumb_url'] ) ? $metas['thumb_url'] : '',
            'is_pro_tmpl' => !empty( $metas['is_pro_tmpl'] ) ? $metas['is_pro_tmpl'] : false,
            'demo_url' => !empty( $metas['demo_url'] ) ? $metas['demo_url'] : false,
            'is_site_tmpl' => array_key_exists('is_site_tmpl', $metas ) && $metas['is_site_tmpl']
        ];
    }
    return $collection;
}


 // Update the 'nimble_template' post
 // Inserts a 'nimble_template' post when one doesn't yet exist.
 // $tmpl_data = array(
  //     'data' => $_POST['tmpl_data'],//<= json stringified
  //     'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
  //     'metas' => array(
  //         'title' => $_POST['tmpl_title'],
  //         'description' => $_POST['tmpl_description'],
  //         'skope_id' => $_POST['skope_id'],
  //         'version' => NIMBLE_VERSION,
  //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
  //         'tmpl_locations' => is_string( $_POST['tmpl_locations'] ) ? explode( ',', $_POST['tmpl_locations'] ) : array(),
  //         'date' => date("Y-m-d"),
  //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
  //     )
  // );
// @return WP_Post|WP_Error Post on success, error on failure.
function sek_update_user_tmpl_post( $tmpl_data, $is_edit_metas_only_case = false ) {
    if ( !is_array( $tmpl_data ) ) {
        sek_error_log( __FUNCTION__ . ' => $tmpl_data is not an array' );
        return new \WP_Error( __FUNCTION__ . ' => $tmpl_data is not an array');
    }

    if ( !isset( $tmpl_data['data']) || !isset( $tmpl_data['metas']) ) {
        sek_error_log( __FUNCTION__ . ' => invalid $tmpl_data' );
        return new \WP_Error( __FUNCTION__ . ' => invalid $tmpl_data');
    }

    $tmpl_data = wp_parse_args( $tmpl_data, array(
        'data' => array(),
        'tmpl_post_name' => null,
        'metas' => array(
            'title' => '',
            'description' => '',
            'skope_id' => '',
            'version' => NIMBLE_VERSION,
            'tmpl_locations' => array(),
            'tmpl_header_location' => '',
            'tmpl_footer_location' => '',
            'date' => '',
            'theme' => '',
            // for api templates
            'is_pro_tmpl' => false,
            'thumb_url' => ''
        )
    ));

    // the template post name is provided only when updating
    $is_update_case = !is_null($tmpl_data['tmpl_post_name']);

    // $post_name_to_update will be used when user updates an existing template
    if ( !is_null($tmpl_data['tmpl_post_name']) ) {
        $tmpl_post_name = $tmpl_data['tmpl_post_name'];
    } else {
        $tmpl_post_name = NIMBLE_PREFIX_FOR_SAVED_TMPL .  sanitize_title( $tmpl_data['metas']['title'] );//nb_tmpl_my-template-name
    }

    //sek_error_log('$tmpl_data??', $tmpl_data );

    // Update the post name now
    $tmpl_data['tmpl_post_name'] = $tmpl_post_name;

    //sek_error_log('serialized $tmpl_data??', maybe_serialize( $tmpl_data ) );
    // Update post if it already exists, otherwise create a new one.
    $current_tmpl_post = null;
    if ( $is_update_case ) {
        // When this is an update case, we fetch the existing tmpl_post in order to later get its id
        $current_tmpl_post = sek_get_saved_tmpl_post( $tmpl_post_name );

        // if this is an update case + editing metas only, then we use the current content
        if ( $is_edit_metas_only_case && isset($current_tmpl_post->post_content) ) {
            $current_tmpl_data = maybe_unserialize( $current_tmpl_post->post_content );
            if ( is_array($current_tmpl_data) && isset($current_tmpl_data['data']) && is_array($current_tmpl_data['data']) && !empty($current_tmpl_data['data']) ) {
                $tmpl_data['data'] = $current_tmpl_data['data'];
            }
        }
    }

    // March 2021 : make sure text input are sanitized like in #544 #792
    $tmpl_data = sek_sektion_collection_sanitize_cb( $tmpl_data );

    $new_or_updated_post_data = array(
        'post_title' => $tmpl_post_name,
        'post_name' => $tmpl_post_name,
        'post_type' => NIMBLE_TEMPLATE_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $tmpl_data )
    );

    // sek_error_log( __FUNCTION__ . ' => so $is_edit_metas_only_case ' . $is_edit_metas_only_case );
    // sek_error_log( __FUNCTION__ . ' => so $tmpl_data for skope ' . $tmpl_post_name, $current_tmpl_data['data'] );
    $r = '';

    if ( $current_tmpl_post && is_object($current_tmpl_post) ) {
        $new_or_updated_post_data['ID'] = $current_tmpl_post->ID;
        $r = wp_update_post( wp_slash( $new_or_updated_post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $new_or_updated_post_data ), true );
        if ( !is_wp_error( $r ) ) {
            $post_id = $r;//$r is the post ID
            // Trigger creation of a revision. This should be removed once #30854 is resolved.
            if ( 0 === count( wp_get_post_revisions( $r ) ) ) {
                wp_save_post_revision( $r );
            }
        }
    }

    // Added March 2021 for stie templates #478
    do_action('nb_on_update_user_tmpl_post', $tmpl_post_name );

    if ( is_wp_error( $r ) ) {
        return $r;
    }
    return get_post( $r );
}

?><?php
// Nov 2020
// When sek_is_cpt_debug_mode() = isset( $_GET['nimble_cpt_debug'] ) || (defined('NIMBLE_CPT_DEBUG_MODE') && NIMBLE_CPT_DEBUG_MODE);
// NB custom post types for skoped sections, user saved sections and templates are set to "public" in the WP admin
// To properly vizualise and edit the CPT we need :
// 1) to filter content before it's rendered in the CPT admin editor, so it's rendered as a JSON
// This is done with 'content_edit_pre'
// 2) to disable the rich editor when editing NB CPT, to prevent any html tags insertion done by rich text editor ( done at 'current_screen' )
// 3) before db insertion, to make sur the NB CPT is turned from JSON to serialized value ( done with 'wp_insert_post_data' )
// Disable rich editor when editing NB custom post types
add_action( 'current_screen', function() {
    if ( !sek_is_cpt_debug_mode() || !is_admin() || skp_is_customizing() )
      return;

    global $pagenow;
    global $typenow;
    if ( 'post.php' === $pagenow && in_array($typenow, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT]) ) {
        add_filter( 'user_can_richedit' , '__return_false', 50 );
    }
});

// Jsonify
add_filter( 'content_edit_pre', function( $content, $post_id ) {
    if ( !sek_is_cpt_debug_mode() )
      return $content;
    $post_type = get_post_type( $post_id );
    if ( in_array($post_type, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT]) ) {
        return wp_json_encode(maybe_unserialize($content), JSON_PRETTY_PRINT);
    }
    return $content;
}, 10, 2 );


// BEFORE INSERTION / UPDATE
// @see wp-includes/post.php
// Reformat edited custom post type when updating from the editor
// We need to make sure that the reformating occurs ONLY when this is a manual update
// - not an update when customizing => check if skp_is_customzing() and DOING_AJAX
// - not an insertion of the revision post type => check on $data['post_type']
// Note that the post status can be 'publish', 'draft', 'pending'
add_filter( 'wp_insert_post_data', function( $data, $postarr, $unsanitized_postarr ) {
    global $pagenow;
    // error_log(' PAGE NOW ?'.$pagenow );
    // error_log(' POST TYPE FROM DATA ?'. $data['post_type']);
    // error_log(' POST STATUS ?'. $data['post_status'] );

    // we must be in an single CPT edit screen
    // prevent processing data when restoring a revision
    if ( 'post.php' !== $pagenow )
      return $data;

    if ( !sek_is_cpt_debug_mode() || skp_is_customizing() || (defined('DOING_AJAX') && DOING_AJAX) )
      return $data;

    // $data should be An array of slashed, sanitized, and processed post data.
    // @see wp-includes/post.php
    if ( !is_array($data) )
      return $data;

    $post_type = 'not_set';
    if ( !empty($data['post_type']) ) {
        $post_type = $data['post_type'];
    }
    // make sure we only process nimble CPT post type. Not the 'revision' post types.
    if ( !in_array( $post_type, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT] ) )
      return $data;

    // Stop here if the post is being removed
    if ( 'trash' == $data['post_status'] )
      return $data;

    //sek_error_log('$data ??', $data);


    $pre_content = isset($data['post_content']) ? $data['post_content'] : null;
    //sek_error_log('is SERIALIZED ?', is_serialized( $pre_content ));
    // Serialized if content has been jsonified in the editor
    // Important : this check is needed in a scenario when the post has been trashed and is restored. In this case the content is already serialized.
    if ( isset($pre_content) && !empty($pre_content) && !is_serialized( $pre_content ) ) {
        $pre_content = json_decode( wp_unslash( $pre_content ), true );
        // Check if content is a valid json ?
        if ( json_last_error() == JSON_ERROR_NONE ) {
            // if no json error, serialize
            $data['post_content'] = maybe_serialize( $pre_content );
            //sek_error_log('VALID JSON => new post_content ??', $data['post_content'] );
        } else {
            //sek_error_log('INVALID JSON', json_last_error() );
            return new \WP_Error( 'db_insert_error', __('Could not insert NB template into the database : invalid JSON'), json_last_error() );
        }
    }
    return wp_slash($data);
}, 10 , 3);

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
    $post_id = sek_get_nb_post_id_from_index($skope_id);
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

?><?php
/* ------------------------------------------------------------------------- *
 *  SANIIZATION AND VALIDATION HELPERS
 *  used before saving NB main settings in DB
 *  used before saving user template in DB
 *  added March 2021 for https://github.com/presscustomizr/nimble-builder/issues/792, after fixing formatting issues : #544 #791
/* ------------------------------------------------------------------------- */
// Uses the sanitize_callback function specified on module registration if any
// Recursively loop on the local or global main NB collection and fire the sanitize callback
// the $setting_instance param is passed when sanitizing the customizer settings. Not used when sanitizing a user template
function sek_sektion_collection_sanitize_cb( $setting_data, $setting_instance = null ) {
    if ( !is_array( $setting_data ) ) {
        return $setting_data;
    } else {
        if ( !is_array( $setting_data ) ) {
            return $setting_data;
        } else {
            if ( array_key_exists('module_type', $setting_data ) ) {
                $san_callback = sek_get_registered_module_type_property( $setting_data['module_type'], 'sanitize_callback' );
                if ( !empty( $san_callback ) && is_string( $san_callback ) && function_exists( $san_callback ) && array_key_exists('value', $setting_data ) ) {
                    //sek_error_log('SANITIZE ??', $san_callback );
                    $setting_data['value'] = $san_callback( $setting_data['value'] );
                }
            } else {
                foreach( $setting_data as $k => $data ) {
                    $setting_data[$k] = sek_sektion_collection_sanitize_cb( $data, $setting_instance );
                }
            }
        }
    }
    //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_doma' ), $setting_data );
    return $setting_data;
}

// Uses the validate_callback function specified on module registration if any
// @return validity object
function sek_sektion_collection_validate_cb( $validity, $setting_data, $setting_instance = null ) {
    $validated = true;
    if ( !is_array( $setting_data ) ) {
        return $setting_data;
    } else {
        if ( !is_array( $setting_data ) ) {
            return $setting_data;
        } else {
            if ( array_key_exists('module_type', $setting_data ) ) {
                $validation_callback = sek_get_registered_module_type_property( $setting_data['module_type'], 'validate_callback' );
                if ( !empty( $validation_callback ) && is_string( $validation_callback ) && function_exists( $validation_callback ) && array_key_exists('value', $setting_data ) ) {
                    $validated = $validation_callback( $setting_data );
                }
            } else {
                foreach( $setting_data as $k => $data ) {
                    $validated = sek_sektion_collection_validate_cb($validity, $data, $setting_instance);
                }
            }
        }
    }

    //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_doma' ), $setting_data );
    if ( true !== $validated ) {
        if ( is_wp_error( $validated ) ) {
            $validation_msg = $validation_msg->get_error_message();
            $validity->add(
                is_null( $setting_instance ) ? 'nimble_validation_error' : 'nimble_validation_error_in_' . $setting_instance->id,
                $validation_msg
            );
        }

    }
    return $validity;
}




// @return bool
function sek_is_json( $string ){
    if ( !is_string( $string ) )
      return false;
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
  
// @return string
function sek_maybe_decode_richtext( $string ){
    if ( !is_string($string) )
    return $string;

    $json_decoded_candidate = json_decode($string, true);
    if ( json_last_error() == JSON_ERROR_NONE ) {
        // https://stackoverflow.com/questions/6465263/how-to-reverse-htmlentities
        // added to fix regression https://github.com/presscustomizr/nimble-builder/issues/791
        $json_decoded_candidate = html_entity_decode($json_decoded_candidate, ENT_QUOTES, get_bloginfo( 'charset' ) );
        //sek_error_log('DECODED DECODED ?', $json_decoded_candidate );
        return $json_decoded_candidate;
    }
    
    return $string;
}

// @return string
function sek_maybe_encode_richtext( $string ){
    if ( !is_string($string) )
    return $string;
    // only encode if not already encoded
    if ( !sek_is_json($string) ) {
        // https://stackoverflow.com/questions/6465263/how-to-reverse-htmlentities
        // added to fix regression https://github.com/presscustomizr/nimble-builder/issues/791
        $string = htmlentities($string, ENT_COMPAT, get_bloginfo( 'charset' ) );//reversed with html_entity_decode
        //$string = wp_encode_emoji( $string );
        $string = wp_json_encode($string);
        //sek_error_log('JSON ENCODED ?', $string );
    }
    return $string;
}




// Feb 2021 added to fix regression https://github.com/presscustomizr/nimble-builder/issues/791
// Recursive
function sek_sniff_and_decode_richtext( $seks_data ) {
    if ( is_array( $seks_data ) ) {
        foreach( $seks_data as $key => $data ) {
            if ( is_array( $data ) ) {
                $seks_data[$key] = sek_sniff_and_decode_richtext( $data );
            } else {
                if ( is_string($data) ) {
                    $seks_data[$key] = sek_maybe_decode_richtext( $data );
                }
            }
        }
    }
    return $seks_data;
  }

?>