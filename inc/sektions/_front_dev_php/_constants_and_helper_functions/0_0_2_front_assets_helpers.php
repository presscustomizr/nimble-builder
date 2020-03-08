<?php
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
    if ( skp_is_customizing() )
      return true;

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
    if ( skp_is_customizing() )
      return true;

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
    if ( skp_is_customizing() )
      return true;

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
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/626
function sek_is_front_jquery_dequeued() {
    return !skp_is_customizing() && defined('NIMBLE_DEQUEUE_JQUERY') && NIMBLE_DEQUEUE_JQUERY;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/626
function sek_load_front_assets_on_scroll() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['load_front_assets_in_ajax'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['load_front_assets_in_ajax'] );
    }
    return false;
    //return defined('NIMBLE_LOAD_FRONT_MODULE_JS_ON_SCROLL') && NIMBLE_LOAD_FRONT_MODULE_JS_ON_SCROLL;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_inline_module_stylesheets_on_front() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['print_partial_module_stylesheets_inline'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['print_partial_module_stylesheets_inline'] );
    }
    return false;
    //return !skp_is_customizing() && defined('NIMBLE_PRINT_MODULE_STYLESHEETS_INLINE') && NIMBLE_PRINT_MODULE_STYLESHEETS_INLINE;
}

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
    //return !skp_is_customizing() && defined('NIMBLE_USE_SPLIT_STYLESHEETS') && NIMBLE_USE_SPLIT_STYLESHEETS;
}


// Adds async/defer attributes to enqueued / registered scripts.
// works with ::sek_filter_script_loader_tag loaded @'script_loader_tag'
function sek_wp_script_add_data( $handle, $attribute = 'async', $bool = true ) {
    // if ( skp_is_customizing() || ( defined('NIMBLE_LOAD_FRONT_ASSETS_ASYNC') && !NIMBLE_LOAD_FRONT_ASSETS_ASYNC ) )
    //   return;
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !skp_is_customizing() && !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['load_js_async'] ) && sek_booleanize_checkbox_val( $glob_perf['load_js_async'] ) ) {
        wp_script_add_data( $handle, $attribute, $bool );
    }
}
?>