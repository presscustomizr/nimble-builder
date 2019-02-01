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

if ( ! defined( 'NIMBLE_CPT' ) ) { define( 'NIMBLE_CPT' , 'nimble_post_type' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'nimble___' ); }
if ( ! defined( 'NIMBLE_GLOBAL_SKOPE_ID' ) ) { define( 'NIMBLE_GLOBAL_SKOPE_ID' , 'skp__global' ); }
if ( ! defined( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' , '__nimble_options__' ); }
if ( ! defined( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' , 'nimble_saved_sektions' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' , '__nimble__' ); }
if ( ! defined( 'NIMBLE_WIDGET_PREFIX' ) ) { define( 'NIMBLE_WIDGET_PREFIX' , 'nimble-widget-area-' ); }
if ( !defined( 'NIMBLE_ASSETS_VERSION' ) ) { define( 'NIMBLE_ASSETS_VERSION', sek_is_dev_mode() ? time() : NIMBLE_VERSION ); }


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
        $defaut_sektions_value = [ 'collection' => [] ];
    } else {
        $defaut_sektions_value = [ 'collection' => [], 'local_options' => [] ];
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


//@return string
function sek_get_seks_setting_id( $skope_id = '' ) {
  if ( empty( $skope_id ) ) {
      error_log( 'sek_get_seks_setting_id => empty skope id or location => collection setting id impossible to build' );
  }
  return NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . "[{$skope_id}]";
}


// return bool
function sek_has_global_sections() {
    if ( skp_is_customizing() )
      return true;
    $maybe_global_sek_post = sek_get_seks_post( NIMBLE_GLOBAL_SKOPE_ID );
    return ! is_null($maybe_global_sek_post) || !!$maybe_global_sek_post;
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


/* ------------------------------------------------------------------------- *
 *  HEADER FOOTER
/* ------------------------------------------------------------------------- */
function sek_page_uses_nimble_header_footer() {
    return Nimble_Manager()->has_local_header_footer || Nimble_Manager()->has_global_header_footer;
}



/* ------------------------------------------------------------------------- *
 *  REGISTERED MODULES => GET PROPERTY
/* ------------------------------------------------------------------------- */
// Helper
function sek_get_registered_module_type_property( $module_type, $property = '' ) {
    // registered modules
    $registered_modules = CZR_Fmk_Base() -> registered_modules;
    if ( ! array_key_exists( $module_type, $registered_modules ) ) {
        error_log( __FUNCTION__ . ' => ' . $module_type . ' not registered.' );
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

    // Did we already cache it ?
    $default_models = Nimble_Manager()->default_models;
    if ( ! empty( $default_models[ $module_type ] ) ) {
        $default = $default_models[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base() -> registered_modules;
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $default;
        }

        // Is this module a father ?
        if ( !empty( $registered_modules[ $module_type ]['is_father'] ) && true === $registered_modules[ $module_type ]['is_father'] ) {
            if ( empty( $registered_modules[ $module_type ][ 'children' ] ) ) {
                error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
                return $default;
            }
            if ( ! is_array( $registered_modules[ $module_type ][ 'children' ] ) ) {
                error_log( __FUNCTION__ . ' => ' . $module_type . ' children modules should be an array' );
                return $default;
            }
            foreach ( $registered_modules[ $module_type ][ 'children' ] as $opt_group => $mod_type ) {
                if ( empty( $registered_modules[ $mod_type ][ 'tmpl' ] ) ) {
                    error_log( __FUNCTION__ . ' => ' . $mod_type . ' => missing "tmpl" property => impossible to build the father default model.' );
                    continue;
                }
                $default[$opt_group] = _sek_build_default_model( $registered_modules[ $mod_type ][ 'tmpl' ] );
            }
        } else {
            if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
                error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the default model.' );
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
//             'title'       => __('Select an icon', 'text_domain_to_be_replaced')
//         ),
//     ),
//     'mod-opt' => array(
//         'social-size' => array(
//             'input_type'  => 'number',
//             'title'       => __('Size in px', 'text_domain_to_be_replaced'),
//             'step'        => 1,
//             'min'         => 5,
//             'transport' => 'postMessage'
//         )
//     ),
//     'item-inputs' => array(
//         'item-inputs' => array(
                // 'tabs' => array(
                //     array(
                //         'title' => __('Content', 'text_domain_to_be_replaced'),
                //         //'attributes' => 'data-sek-device="desktop"',
                //         'inputs' => array(
                //             'content' => array(
                //                 'input_type'  => 'tiny_mce_editor',
                //                 'title'       => __('Content', 'text_domain_to_be_replaced')
                //             ),
                //             'h_alignment_css' => array(
                //                 'input_type'  => 'h_text_alignment',
                //                 'title'       => __('Alignment', 'text_domain_to_be_replaced'),
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

    // Did we already cache it ?
    $cached_input_lists = Nimble_Manager()->cached_input_lists;
    if ( ! empty( $cached_input_lists[ $module_type ] ) ) {
        $input_list = $cached_input_lists[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base() -> registered_modules;
        // sek_error_log( __FUNCTION__ . ' => registered_modules', $registered_modules );
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $input_list;
        }


        // Is this module a father ?
        if ( !empty( $registered_modules[ $module_type ]['is_father'] ) && true === $registered_modules[ $module_type ]['is_father'] ) {
            if ( empty( $registered_modules[ $module_type ][ 'children' ] ) ) {
                error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
                return $input_list;
            }
            if ( ! is_array( $registered_modules[ $module_type ][ 'children' ] ) ) {
                error_log( __FUNCTION__ . ' => ' . $module_type . ' children modules should be an array' );
                return $input_list;
            }
            $temp = array();
            foreach ( $registered_modules[ $module_type ][ 'children' ] as $opt_group => $mod_type ) {
                if ( empty( $registered_modules[ $mod_type ][ 'tmpl' ] ) ) {
                    error_log( __FUNCTION__ . ' => ' . $mod_type . ' => missing "tmpl" property => impossible to build the master input_list.' );
                    continue;
                }
                // $temp[$opt_group] = _sek_build_input_list( $registered_modules[ $mod_type ][ 'tmpl' ] );
                // $input_list = array_merge( $input_list, $temp[$opt_group] );

                $input_list[$opt_group] = _sek_build_input_list( $registered_modules[ $mod_type ][ 'tmpl' ] );
            }
        } else {
            if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
                error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the input_list.' );
                return $input_list;
            }
            // Build
            $input_list = _sek_build_input_list( $registered_modules[ $module_type ][ 'tmpl' ] );
        }




        // if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
        //     error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the input_list.' );
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
//             'title'       => __('Select an icon', 'text_domain_to_be_replaced')
//         ),
//     ),
//     'mod-opt' => array(
//         'social-size' => array(
//             'input_type'  => 'number',
//             'title'       => __('Size in px', 'text_domain_to_be_replaced'),
//             'step'        => 1,
//             'min'         => 5,
//             'transport' => 'postMessage'
//         )
//     ),
//     'item-inputs' => array(
//         'item-inputs' => array(
                // 'tabs' => array(
                //     array(
                //         'title' => __('Content', 'text_domain_to_be_replaced'),
                //         //'attributes' => 'data-sek-device="desktop"',
                //         'inputs' => array(
                //             'content' => array(
                //                 'input_type'  => 'tiny_mce_editor',
                //                 'title'       => __('Content', 'text_domain_to_be_replaced')
                //             ),
                //             'h_alignment_css' => array(
                //                 'input_type'  => 'h_text_alignment',
                //                 'title'       => __('Alignment', 'text_domain_to_be_replaced'),
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
    $module_type = $normalized_model['module_type'];
    $is_father = sek_get_registered_module_type_property( $module_type, 'is_father' );

    $raw_module_value = ( ! empty( $raw_module_model['value'] ) && is_array( $raw_module_model['value'] ) ) ? $raw_module_model['value'] : array();

    // reset the model value and rewrite it normalized with the defaults
    $normalized_model['value'] = array();
    if ( $is_father ) {
        $children = sek_get_registered_module_type_property( $module_type, 'children' );
        if ( empty( $children ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' missing children modules' );
            return $default;
        }
        if ( ! is_array( $children ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' children modules should be an array' );
            return $default;
        }
        foreach ( $children as $opt_group => $mod_type ) {
            $children_value = ( ! empty( $raw_module_value[$opt_group] ) && is_array( $raw_module_value[$opt_group] ) ) ? $raw_module_value[$opt_group] : array();
            $normalized_model['value'][ $opt_group ] = _sek_normalize_single_module_values( $children_value, $mod_type );
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
 *   LOCAL OPTIONS HELPERS
/* ------------------------------------------------------------------------- */
// @return mixed null || string
function sek_get_locale_template(){
    $path = null;
    $local_template_data = sek_get_local_option_value( 'template' );
    if ( ! empty( $local_template_data ) && ! empty( $local_template_data['local_template'] ) && 'default' !== $local_template_data['local_template'] ) {
        $template_file_name = $local_template_data['local_template'];
        $path = apply_filters( 'nimble_get_locale_template_path', NIMBLE_BASE_PATH . '/tmpl/page-templates/' . $template_file_name . '.php', $template_file_name );
        if ( file_exists( $path ) ) {
            $template = $path;
        } else {
            sek_error_log( __FUNCTION__ .' the custom template does not exist', $path );
            $path = null;
        }
    }
    return $path;
}

// @param $option_name = string
function sek_get_local_option_value( $option_name, $skope_id = null ) {
    // use the provided skope_id if in the signature
    $skope_id = ( !empty( $skope_id ) && is_string( $skope_id ))? $skope_id : skp_get_skope_id();
    $localSkopeNimble = sek_get_skoped_seks( skp_get_skope_id() );
    $local_options = ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['local_options'] ) && is_array( $localSkopeNimble['local_options'] ) ) ? $localSkopeNimble['local_options'] : array();
    return ( ! empty( $local_options ) && ! empty( $local_options[ $option_name ] ) ) ? $local_options[ $option_name ] : null;
}


// @param $option_name = string
function sek_get_global_option_value( $option_name ) {
    $options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    return ( is_array( $options ) && ! empty( $options[ $option_name ] ) ) ? $options[ $option_name ] : null;
}





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

// invoked when filtering 'sek_add_css_rules_for__section__options'
function sek_get_section_custom_breakpoint( $section ) {
    if ( ! is_array( $section ) )
      return;

    $options = empty( $section[ 'options' ] ) ? array() : $section['options'];
    if ( empty( $options[ 'breakpoint' ] ) )
      return;

    if ( empty( $options[ 'breakpoint' ][ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $options[ 'breakpoint' ][ 'use-custom-breakpoint'] ) )
      return;

    if ( empty( $options[ 'breakpoint' ][ 'custom-breakpoint' ] ) )
      return;

    if ( empty($section['id']) )
      return;

    $custom_breakpoint = intval( $options[ 'breakpoint' ][ 'custom-breakpoint' ] );
    if ( $custom_breakpoint < 0 )
      return;

    return $custom_breakpoint;
}




/* ------------------------------------------------------------------------- *
 *  FONT AWESOME HELPER
/* ------------------------------------------------------------------------- */
// @return bool
// some modules uses font awesome :
// Fired in 'wp_enqueue_scripts' to check if font awesome is needed
function sek_front_needs_font_awesome( $bool = false, $recursive_data = null ) {
    if ( !$bool ) {
        if ( is_null( $recursive_data ) ) {
            $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
            $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

            $recursive_data = array_merge( $local_collection, $global_collection );
        }

        $font_awesome_dependant_modules = array( 'czr_button_module', 'czr_icon_module', 'czr_menu_module' );

        foreach ($recursive_data as $key => $value) {
            if ( is_array( $value ) && array_key_exists('module_type', $value) && in_array($value['module_type'], $font_awesome_dependant_modules ) ) {
                $bool = true;
                break;
            } else if ( is_array( $value ) ) {
                $bool = sek_front_needs_font_awesome( $bool, $value );
            }
        }
    }
    return $bool;
}


// @return bool
// Fired in 'wp_enqueue_scripts'
// Recursively sniff the local and global sections to find a 'img-lightbox' string
// @see sek_get_module_params_for_czr_image_main_settings_child
function sek_front_needs_magnific_popup( $bool = '_not_set_', $recursive_data = null ) {
    if ( '_not_set_' === $bool ) {
        if ( is_null( $recursive_data ) ) {
            $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
            $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

            $recursive_data = array_merge( $local_collection, $global_collection );
        }
        foreach ($recursive_data as $key => $value) {
            // @see sek_get_module_params_for_czr_image_main_settings_child
            if (  is_string( $value ) && 'img-lightbox' === $value ) {
                $bool = true;
                break;
            } else if ( '_not_set_' === $bool ) {
                if ( is_array( $value ) ) {
                    $bool = sek_front_needs_magnific_popup( $bool, $value );
                }
            }
        }
    }
    return true === $bool;
}




/* ------------------------------------------------------------------------- *
 *  IMAGE HELPER
/* ------------------------------------------------------------------------- */
// @see https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
// used in sek_get_select_options_for_input_id()
function sek_get_img_sizes() {
    global $_wp_additional_image_sizes;

    $sizes = array();
    $to_return = array(
        'original' => __('Original image dimensions', 'text_domain_to_be_replaced')
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
 *  SMART LOAD HELPER
/* ------------------------------------------------------------------------- */
/**
* callback of preg_replace_callback in SEK_Front_Render::sek_maybe_process_img_for_js_smart_load
* @return string
*/
function nimble_regex_callback( $matches ) {
    $_placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    if ( false !== strpos( $matches[0], 'data-sek-src' ) || preg_match('/ data-sek-smartload *= *"false" */', $matches[0]) ) {
      return $matches[0];
    } else {
      return apply_filters( 'nimble_img_smartloaded',
        str_replace( array('srcset=', 'sizes='), array('data-sek-srcset=', 'data-sek-sizes='),
            sprintf('<img %1$s src="%2$s" data-sek-src="%3$s" %4$s>',
                $matches[1],
                $_placeholder,
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
    if ( skp_is_customizing() )
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



function sek_error_log( $title, $content = null ) {
    if ( is_null( $content ) ) {
        error_log( '<' . $title . '>' );
    } else {
        error_log( '<' . $title . '>' );
        error_log( print_r( $content, true ) );
        error_log( '</' . $title . '>' );
    }
}


// DEPRECATED SINCE Nimble v1.3.0, november 2018
// was used in the Hueman theme before version 3.4.9
function render_content_sections_for_nimble_template() {
    Nimble_Manager()->render_nimble_locations(
        array_keys( Nimble_Manager()->default_locations ),//array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
        array( 'fallback_location' => 'loop_start' )
    );
}

/* ------------------------------------------------------------------------- *
 *  Page Menu
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
    if ( true === $args['show_home'] || '1' === $args['show_home'] || 1 === $args['show_home'] )
      $text = __('Home' , 'text_domain_to_replace');
    else
      $text = $args['show_home'];
    $class = '';
    if ( is_front_page() && !is_paged() )
      $class = 'class="current_page_item"';
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
  $menu .= str_replace( array( "\r", "\n", "\t" ), '', sek_list_pages($list_args) );
   // if ( $menu )
  //   $menu = '<ul>' . $menu . '</ul>';
   //$menu = '<div class="' . esc_attr($args['menu_class']) . '">' . $menu . "</div>\n";
   if ( $menu )
    $menu = '<ul class="' . esc_attr($args['menu_class']) . '">' . $menu . '</ul>';
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
function sek_walk_page_tree($pages, $depth, $current_page, $r) {
  // if ( empty($r['walker']) )
  //   $walker = new Walker_Page;
  // else
  //   $walker = $r['walker'];
  $walker = new \Walker_Page;
   foreach ( (array) $pages as $page ) {
    if ( $page->post_parent )
      $r['pages_with_children'][ $page->post_parent ] = true;
  }
   $args = array($pages, $depth, $r, $current_page);
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
 *  Dynamic variables parsing
/* ------------------------------------------------------------------------- */
function sek_find_pattern_match($matches) {
    $replace_values = array(
      'home_url' => 'home_url'
    );

    if ( array_key_exists( $matches[1], $replace_values ) ) {
      $dyn_content = $replace_values[$matches[1]];
      if ( function_exists( $dyn_content ) ) {
        return $dyn_content();//<= use call_user_func() here + handle the case when the callback is a method
      } else if ( is_string($dyn_content) ) {
        return $dyn_content;
      } else {
        return null;
      }
    }
    return null;
}

function sek_parse_template_tags( $val ) {
    //the pattern could also be '!\{\{(\w+)\}\}!', but adding \s? allows us to allow spaces around the term inside curly braces
    //see https://stackoverflow.com/questions/959017/php-regex-templating-find-all-occurrences-of-var#comment71815465_959026
    return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(\w+)\s?\}\}!', '\Nimble\sek_find_pattern_match', $val) : $val;
}
add_filter( 'nimble_parse_template_tags', '\Nimble\sek_parse_template_tags' );


/* ------------------------------------------------------------------------- *
 *  Beta Features
/* ------------------------------------------------------------------------- */
// December 2018 => preparation of the header / footer feature
// The beta features can be control by a constant
// and by a global option
function sek_is_header_footer_enabled() {
    $global_beta_feature = sek_get_global_option_value( 'beta_features');
    if ( is_array( $global_beta_feature ) && array_key_exists('beta-enabled', $global_beta_feature ) ) {
          return (bool)$global_beta_feature['beta-enabled'];
    }
    return NIMBLE_HEADER_FOOTER_ENABLED;
}
?>