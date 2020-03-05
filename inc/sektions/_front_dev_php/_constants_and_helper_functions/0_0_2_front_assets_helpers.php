<?php
/* ------------------------------------------------------------------------- *
 *  FRONT ASSET SNIFFERS
/* ------------------------------------------------------------------------- */

// @return bool
// some modules uses font awesome :
// Fired in 'wp_enqueue_scripts' to check if font awesome is needed
function sek_front_needs_font_awesome( $bool = false, $recursive_data = null ) {
    $contextually_active_modules = sek_get_collection_of_contextually_active_modules();
    $font_awesome_dependant_modules = array( 'czr_button_module', 'czr_icon_module', 'czr_social_icons_module', 'czr_quote_module' );
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
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/626
function sek_load_front_assets_with_js() {
    return !skp_is_customizing() && defined('NIMBLE_LOAD_FRONT_ASSETS_ON_SCROLL') && NIMBLE_LOAD_FRONT_ASSETS_ON_SCROLL;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_inline_stylesheets_on_front() {
    return !skp_is_customizing() && defined('NIMBLE_PRINT_MODULE_STYLESHEETS_INLINE') && NIMBLE_PRINT_MODULE_STYLESHEETS_INLINE;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_use_split_stylesheets_on_front() {
    return !skp_is_customizing() && defined('NIMBLE_USE_SPLIT_STYLESHEETS') && NIMBLE_USE_SPLIT_STYLESHEETS;
}


// Adds async/defer attributes to enqueued / registered scripts.
// works with ::sek_filter_script_loader_tag loaded @'script_loader_tag'
function sek_wp_script_add_data( $handle, $attribute = 'async', $bool = true ) {
    if ( skp_is_customizing() )
      return;
    wp_script_add_data( $handle, $attribute, $bool );
}
?>