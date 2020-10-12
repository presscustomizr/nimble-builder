<?php


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
// function sek_preload_font_awesome() {
//     $glob_perf = sek_get_global_option_value( 'performances' );
//     if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['preload_font_awesome'] ) ) {
//         return sek_booleanize_checkbox_val( $glob_perf['preload_font_awesome'] );
//     }
//     return false;
// }

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
    $html = sprintf('<script>nb_.emit("%1$s");</script>', $event );
    if ( $echo ) {
        echo $html;
    } else {
        return $html;
    }
}

/* ------------------------------------------------------------------------- *
 *  FRONT ASSET SNIFFERS
 *  Deprecated in October 2020 in favor of a js detection using events like nb-needs-video-bg
/* ------------------------------------------------------------------------- */

// // @return bool
// // some modules uses font awesome :
// // Fired in 'wp_enqueue_scripts' to check if font awesome is needed
// function sek_front_needs_font_awesome( $bool = false, $recursive_data = null ) {
//     $contextually_active_modules = sek_get_collection_of_contextually_active_modules();
//     $font_awesome_dependant_modules = Nimble_Manager()->modules_dependant_of_font_awesome;//'czr_button_module', 'czr_icon_module', 'czr_social_icons_module'
//     foreach ( $font_awesome_dependant_modules as $module_type ) {
//       if ( array_key_exists($module_type , $contextually_active_modules) )
//         $bool = true;
//     }
//     return $bool;
// }

// @return bool
// Fired in 'wp_enqueue_scripts'
// Recursively sniff the local and global sections to find a 'img-lightbox' string
// @see sek_get_module_params_for_czr_image_main_settings_child
// function sek_front_needs_magnific_popup( $bool = false, $recursive_data = null ) {
//     if ( !$bool ) {
//         if ( is_null( $recursive_data ) ) {
//             $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
//             $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
//             $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
//             $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

//             $recursive_data = array_merge( $local_collection, $global_collection );
//         }

//         foreach ($recursive_data as $key => $value) {
//             // @see sek_get_module_params_for_czr_image_main_settings_child
//             if ( is_string( $value ) && 'img-lightbox' === $value ) {
//                 $bool = true;
//                 break;
//             }
//             if ( is_array( $value ) ) {
//                 $bool = sek_front_needs_magnific_popup( $bool, $value );
//             }
//         }
//     }
//     return true === $bool;
// }

// @return bool
// Fired in 'wp_enqueue_scripts'
// function sek_front_needs_parallax_bg( $bool = false, $recursive_data = null ) {
//     if ( !$bool ) {
//         if ( is_null( $recursive_data ) ) {
//             $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
//             $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
//             $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
//             $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

//             $recursive_data = array_merge( $local_collection, $global_collection );
//         }

//         foreach ($recursive_data as $key => $value) {
//             // @see sek_get_module_params_for_czr_image_main_settings_child
//             if ( 'bg-parallax' === $key && sek_booleanize_checkbox_val($value) ) {
//                 $bool = true;
//                 break;
//             }
//             if ( is_array( $value ) ) {
//                 $bool = sek_front_needs_parallax_bg( $bool, $value );
//             }
//         }
//     }
//     return true === $bool;
// }

// @return bool
// Fired in 'wp_enqueue_scripts'
// function sek_front_needs_video_bg( $bool = false, $recursive_data = null ) {
//     if ( !$bool ) {
//         if ( is_null( $recursive_data ) ) {
//             $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
//             $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
//             $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
//             $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

//             $recursive_data = array_merge( $local_collection, $global_collection );
//         }

//         foreach ($recursive_data as $key => $value) {
//             // @see sek_get_module_params_for_czr_image_main_settings_child
//             if ( 'bg-video' === $key && !empty($value) ) {
//                 $bool = true;
//                 break;
//             }
//             if ( is_array( $value ) ) {
//                 $bool = sek_front_needs_video_bg( $bool, $value );
//             }
//         }
//     }
//     return true === $bool;
// }

?>