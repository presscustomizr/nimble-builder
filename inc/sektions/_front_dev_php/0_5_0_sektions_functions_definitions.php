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
if ( ! defined( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' , '__nimble_options__' ); }
if ( ! defined( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' , 'nimble_saved_sektions' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' , '__nimble__' ); }
if ( !defined( 'NIMBLE_ASSETS_VERSION' ) ) {
    define( 'NIMBLE_ASSETS_VERSION', sek_is_dev_mode() ? time() : NIMBLE_VERSION );
}

// @return array
function sek_get_locations() {
  return apply_filters( 'sek_locations', array_merge( SEK_Fire()->default_locations, SEK_Fire()->registered_locations ) );
}

// @param $location_id ( string ). Example '__after_header'
function register_location( $location_id, $params = array() ) {
    $params = is_array( $params ) ? $params : array();
    $params = wp_parse_args( $params, array( 'priority' => 10 ) );
    $registered_locations = SEK_Fire()->registered_locations;
    if ( is_array( $registered_locations ) ) {
        $registered_locations[$location_id] = $params;
    }
    SEK_Fire()->registered_locations = $registered_locations;
    //sek_error_log('SEK_Fire()->registered_locations', SEK_Fire()->registered_locations );
}

// @return array
// @used when populating the customizer localized params
function sek_get_default_sektions_value() {
    $defaut_sektions_value = [ 'collection' => [], 'local_options' => [] ];
    foreach( sek_get_locations() as $location_id => $params ) {
        $defaut_sektions_value['collection'][] = wp_parse_args( [ 'id' => $location_id ], SEK_Fire()->default_location_model );
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
// @param $collection = sek_get_skoped_seks( $skope_id )['collection']; <= the root collection must always be provided
function sek_get_level_model( $id, $collection = array() ) {
    $_data = 'no_match';
    foreach ( $collection as $level_data ) {
        // stop here and return if a match was recursively found
        if ( 'no_match' != $_data )
          break;
        if ( $id === $level_data['id'] ) {
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
function sek_get_parent_level_model( $child_level_id, $collection = array(), $skope_id = '' ) {
    if ( empty( $collection ) ) {
        if ( empty( $skope_id ) ) {
            if ( is_array( $_POST ) && ! empty( $_POST['skope_id'] ) ) {
                $skope_id = $_POST['skope_id'];
            } else {
                $skope_id = skp_get_skope_id();
            }
        }
        $skoped_setting = sek_get_skoped_seks( $skope_id );
        $collection = ( is_array( $skoped_setting ) && !empty( $skoped_setting['collection'] ) ) ? $skoped_setting['collection'] : array();
    }
    $_parent_level_data = 'no_match';
    foreach ( $collection as $level_data ) {
        // stop here and return if a match was recursively found
        if ( 'no_match' !== $_parent_level_data )
          break;
        if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            foreach ( $level_data['collection'] as $child_level_data ) {
                if ( $child_level_id == $child_level_data['id'] ) {
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
    $default_models = SEK_Fire()->default_models;
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
        SEK_Fire()->default_models = $default_models;
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
    $cached_input_lists = SEK_Fire()->cached_input_lists;
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
        SEK_Fire()->cached_input_lists = $cached_input_lists;
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







/* HELPER FOR CHECKBOX OPTIONS */
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


/* VARIOUS HELPERS */
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

// @return mixed null || string
function sek_get_locale_template(){
    $path = null;
    $localSkopeNimble = sek_get_skoped_seks( skp_get_skope_id() );
    $local_options = ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['local_options'] ) && is_array( $localSkopeNimble['local_options'] ) ) ? $localSkopeNimble['local_options'] : array();
    if ( ! empty( $local_options ) && ! empty( $local_options['template'] ) && ! empty( $local_options['template']['local_template'] ) && 'default' !== $local_options['template']['local_template'] ) {
        $template_file_name = $local_options['template']['local_template'];
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

//@return void()
function render_content_sections_for_nimble_template() {
    foreach( sek_get_locations() as $location_id => $params ) {
        $locationSettingValue = sek_get_skoped_seks( skp_get_skope_id(), $location_id );
        // We don't need to render the locations with no sections
        // But we need at least one location : let's always render loop_start.
        // => so if the user switches from the nimble_template to the default theme one, the loop_start section will always be rendered.
        if ( 'loop_start' === $location_id || ( is_array( $locationSettingValue ) && ! empty( $locationSettingValue['collection'] ) ) ) {
            do_action( "sek_before_location_{$location_id}" );
            SEK_Fire()->_render_seks_for_location( $location_id, $locationSettingValue );
            do_action( "sek_after_location_{$location_id}" );
        }
    }
}







/* ------------------------------------------------------------------------- *
 *  BREAKPOINTS HELPER
/* ------------------------------------------------------------------------- */
function sek_get_global_custom_breakpoint() {
    $options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    if ( ! is_array( $options ) || empty( $options['breakpoint'] ) || empty( $options['breakpoint']['global-custom-breakpoint'] ) )
      return;

    if ( empty( $options['breakpoint'][ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $options['breakpoint'][ 'use-custom-breakpoint'] ) )
      return;

    return intval( $options['breakpoint']['global-custom-breakpoint'] );
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
// 2 modules use font awesome :
// czr_button_module and czr_icon_module
function sek_front_needs_font_awesome( $bool = false, $recursive_data = null ) {
    if ( !$bool ) {
        if ( is_null( $recursive_data ) ) {
            $recursive_data = sek_get_skoped_seks( skp_get_skope_id() );
        }
        foreach ($recursive_data as $key => $value) {
            if ( is_array( $value ) && array_key_exists('module_type', $value) && in_array($value['module_type'], array( 'czr_button_module', 'czr_icon_module' ) ) ) {
                $bool = true;
                break;
            } else if ( is_array( $value ) ) {
                $bool = sek_front_needs_font_awesome( $bool, $value );
            }
        }
    }
    return $bool;
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
    if ( 'not_cached' !== SEK_Fire()->img_smartload_enabled ) {
        return SEK_Fire()->img_smartload_enabled;
    }
    $is_img_smartload_enabled = false;
    // LOCAL OPTION
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $local_options = sek_get_skoped_seks( !empty( $_POST['skope_id'] ) ? $_POST['skope_id'] : skp_build_skope_id() );
    $local_smartload = 'inherit';
    if ( is_array( $local_options ) && !empty( $local_options['local_options']) && is_array( $local_options['local_options']) && !empty($local_options['local_options']['local_performances'] ) && is_array( $local_options['local_options']['local_performances'] ) ) {
        if ( ! empty( $local_options['local_options']['local_performances']['local-img-smart-load'] ) && 'inherit' !== $local_options['local_options']['local_performances']['local-img-smart-load'] ) {
              $local_smartload = 'yes' === $local_options['local_options']['local_performances']['local-img-smart-load'];
        }
    }

    if ( 'inherit' !== $local_smartload ) {
        $is_img_smartload_enabled = $local_smartload;
    } else {
        // GLOBAL OPTION
        $glob_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
        if ( is_array( $glob_options ) && !empty($glob_options['performances']) && is_array( $glob_options['performances'] ) && !empty( $glob_options['performances']['global-img-smart-load'] ) ) {
            $is_img_smartload_enabled = sek_booleanize_checkbox_val( $glob_options['performances']['global-img-smart-load'] );
        }
    }

    // CACHE THE OPTION
    SEK_Fire()->img_smartload_enabled = $is_img_smartload_enabled;

    return SEK_Fire()->img_smartload_enabled;
}



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
?>