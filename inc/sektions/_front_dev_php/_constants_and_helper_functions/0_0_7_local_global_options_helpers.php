<?php
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


?>