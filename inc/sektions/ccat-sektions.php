<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function sek_is_debug_mode() {
  return isset( $_GET['nimble_debug'] );
}
function sek_is_dev_mode() {
  return ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || sek_is_debug_mode();
}

if ( ! defined( 'NIMBLE_CPT' ) ) { define( 'NIMBLE_CPT' , 'nimble_post_type' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'nimble___' ); }
if ( ! defined( 'NIMBLE_GLOBAL_SKOPE_ID' ) ) { define( 'NIMBLE_GLOBAL_SKOPE_ID' , 'skp__global' ); }
if ( ! defined( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' , '__nimble_options__' ); }
if ( ! defined( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' , 'nimble_saved_sektions' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' , '__nimble__' ); }
if ( !defined( 'NIMBLE_ASSETS_VERSION' ) ) {
    define( 'NIMBLE_ASSETS_VERSION', sek_is_dev_mode() ? time() : NIMBLE_VERSION );
}

/* ------------------------------------------------------------------------- *
 *  LOCATIONS UTILITIES
/* ------------------------------------------------------------------------- */
function sek_get_locations() {
    if ( ! is_array( Nimble_Manager()->registered_locations ) ) {
        sek_error_log( __FUNCTION__ . ' error => the registered locations must be an array');
        return Nimble_Manager()->default_locations;
    }
    return apply_filters( 'sek_get_locations', Nimble_Manager()->registered_locations );
}
function sek_get_local_locations() {
    $locations = array();
    $all_locations = sek_get_locations();
    if ( is_array( $all_locations ) ) {
        foreach ( $all_locations as $loc_id => $loc_data) {
            if ( ! sek_is_global_location( $loc_id ) ) {
                $locations[$loc_id] = $loc_data;
            }
        }
    }
    return $locations;
}
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
function sek_get_registered_location_property( $location_id, $property_name = '' ) {
    $all_locations = sek_get_locations();
    $default_property_val = 'not_set';
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
function sek_is_global_location( $location_id ) {
    if ( ! is_string( $location_id ) || empty( $location_id ) ) {
        sek_error_log( __FUNCTION__ . ' error => missing or invalid location_id param' );
        return false;
    }
    $is_global_location = sek_get_registered_location_property( $location_id, 'is_global_location' );
    return 'not_set' === $is_global_location ? false : true === $is_global_location;
}
function register_location( $location_id, $params = array() ) {
    $params = is_array( $params ) ? $params : array();
    $params = wp_parse_args( $params, Nimble_Manager()->default_registered_location_model );
    $registered_locations = Nimble_Manager()->registered_locations;
    if ( is_array( $registered_locations ) ) {
        $registered_locations[$location_id] = $params;
    }
    Nimble_Manager()->registered_locations = $registered_locations;
}
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
function sek_get_seks_setting_id( $skope_id = '' ) {
  if ( empty( $skope_id ) ) {
      error_log( 'sek_get_seks_setting_id => empty skope id or location => collection setting id impossible to build' );
  }
  return NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . "[{$skope_id}]";
}
function sek_has_global_sections() {
    if ( skp_is_customizing() )
      return true;
    $maybe_global_sek_post = sek_get_seks_post( NIMBLE_GLOBAL_SKOPE_ID );
    return ! is_null($maybe_global_sek_post) || !!$maybe_global_sek_post;
}
/*function sek_get_module_placeholder( $placeholder_icon = 'short_text' ) {
  $placeholder_icon = empty( $placeholder_icon ) ? 'not_interested' : $placeholder_icon;
  ?>
    <div class="sek-module-placeholder">
      <i class="material-icons"><?php echo $placeholder_icon; ?></i>
    </div>
  <?php
}*/
function sek_get_level_model( $id, $collection = array() ) {
    $_data = 'no_match';
    if ( ! is_array( $collection ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid collection param when getting model for id : ' . $id );
        return $_data;
    }
    foreach ( $collection as $level_data ) {
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
function sek_get_parent_level_model( $child_level_id = '', $collection = array(), $skope_id = '' ) {
    $_parent_level_data = 'no_match';
    if ( ! is_string( $child_level_id ) || empty( $child_level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $_parent_level_data;
    }
    if ( empty( $collection ) ) {
        if ( empty( $skope_id ) ) {
            if ( is_array( $_POST ) && ! empty( $_POST['location_skope_id'] ) ) {
                $skope_id = $_POST['location_skope_id'];
            } else {
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
        if ( 'no_match' !== $_parent_level_data )
          break;
        if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            foreach ( $level_data['collection'] as $child_level_data ) {
                if ( array_key_exists( 'id', $child_level_data ) && $child_level_id == $child_level_data['id'] ) {
                    $_parent_level_data = $level_data;
                    break;
                } else {
                    $_parent_level_data = sek_get_parent_level_model( $child_level_id, $level_data['collection'], $skope_id );
                }
            }
        }
    }
    return $_parent_level_data;
}
function sek_section_has_modules( $model, $has_module = null ) {
    $has_module = is_null( $has_module ) ? false : (bool)$has_module;
    foreach ( $model as $level_data ) {
        if ( true === $has_module )
          break;
        if ( is_array( $level_data ) && array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            foreach ( $level_data['collection'] as $child_level_data ) {
                if ( 'module'== $child_level_data['level'] ) {
                    $has_module = true;
                    break;
                } else {
                    $has_module = sek_section_has_modules( $child_level_data, $has_module );
                }
            }
        }
    }
    return $has_module;
}
function sek_get_level_skope_id( $level_id = '' ) {
    $level_skope_id = skp_get_skope_id();
    if ( ! is_string( $level_id ) || empty( $level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $level_skope_id;
    }

    $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
    $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
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
 *  REGISTERED MODULES => GET PROPERTY
/* ------------------------------------------------------------------------- */
function sek_get_registered_module_type_property( $module_type, $property = '' ) {
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
function sek_get_default_module_model( $module_type = '' ) {
    $default = array();
    if ( empty( $module_type ) || is_null( $module_type ) )
      return $default;
    $default_models = Nimble_Manager()->default_models;
    if ( ! empty( $default_models[ $module_type ] ) ) {
        $default = $default_models[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base() -> registered_modules;
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $default;
        }
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
            $default = _sek_build_default_model( $registered_modules[ $module_type ][ 'tmpl' ] );
        }
        $default_models[ $module_type ] = $default;
        Nimble_Manager()->default_models = $default_models;
    }
    return $default;
}
function _sek_build_default_model( $module_tmpl_data, $default_model = null ) {
    $default_model = is_array( $default_model ) ? $default_model : array();
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
function sek_get_registered_module_input_list( $module_type = '' ) {
    $input_list = array();
    if ( empty( $module_type ) || is_null( $module_type ) )
      return $input_list;
    $cached_input_lists = Nimble_Manager()->cached_input_lists;
    if ( ! empty( $cached_input_lists[ $module_type ] ) ) {
        $input_list = $cached_input_lists[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base() -> registered_modules;
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $input_list;
        }
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

                $input_list[$opt_group] = _sek_build_input_list( $registered_modules[ $mod_type ][ 'tmpl' ] );
            }
        } else {
            if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
                error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the input_list.' );
                return $input_list;
            }
            $input_list = _sek_build_input_list( $registered_modules[ $module_type ][ 'tmpl' ] );
        }
        $cached_input_lists[ $module_type ] = $input_list;
        Nimble_Manager()->cached_input_lists = $cached_input_lists;
    }
    return $input_list;
}
function _sek_build_input_list( $module_tmpl_data, $input_list = null ) {
    $input_list = is_array( $input_list ) ? $input_list : array();
    foreach( $module_tmpl_data as $key => $data ) {
        if ( 'pre-item' === $key )
          continue;
        if ( is_array( $data ) && array_key_exists( 'input_type', $data ) ) {
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
function sek_normalize_module_value_with_defaults( $raw_module_model ) {
    $normalized_model = $raw_module_model;
    $module_type = $normalized_model['module_type'];
    $is_father = sek_get_registered_module_type_property( $module_type, 'is_father' );

    $raw_module_value = ( ! empty( $raw_module_model['value'] ) && is_array( $raw_module_model['value'] ) ) ? $raw_module_model['value'] : array();
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
    return $normalized_model;
}
function _sek_normalize_single_module_values( $raw_module_value, $module_type ) {
    $default_value_model  = sek_get_default_module_model( $module_type );//<= walk the registered modules tree and generates the module default if not already cached
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
function sek_front_needs_font_awesome( $bool = false, $recursive_data = null ) {
    if ( !$bool ) {
        if ( is_null( $recursive_data ) ) {
            $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
            $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
            $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

            $recursive_data = array_merge( $local_collection, $global_collection );
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
        } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
            $sizes[ $_size ] = array(
                'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                'title' =>  $first_to_upper_size
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
function sek_is_img_smartload_enabled() {
    if ( skp_is_customizing() )
      return false;
    if ( 'not_cached' !== Nimble_Manager()->img_smartload_enabled ) {
        return Nimble_Manager()->img_smartload_enabled;
    }
    $is_img_smartload_enabled = false;
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? $_POST['local_skope_id'] : skp_build_skope_id() );
    $local_smartload = 'inherit';
    if ( is_array( $local_options ) && !empty( $local_options['local_options']) && is_array( $local_options['local_options']) && !empty($local_options['local_options']['local_performances'] ) && is_array( $local_options['local_options']['local_performances'] ) ) {
        if ( ! empty( $local_options['local_options']['local_performances']['local-img-smart-load'] ) && 'inherit' !== $local_options['local_options']['local_performances']['local-img-smart-load'] ) {
              $local_smartload = 'yes' === $local_options['local_options']['local_performances']['local-img-smart-load'];
        }
    }

    if ( 'inherit' !== $local_smartload ) {
        $is_img_smartload_enabled = $local_smartload;
    } else {
        $glob_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
        if ( is_array( $glob_options ) && !empty($glob_options['performances']) && is_array( $glob_options['performances'] ) && !empty( $glob_options['performances']['global-img-smart-load'] ) ) {
            $is_img_smartload_enabled = sek_booleanize_checkbox_val( $glob_options['performances']['global-img-smart-load'] );
        }
    }
    Nimble_Manager()->img_smartload_enabled = $is_img_smartload_enabled;

    return Nimble_Manager()->img_smartload_enabled;
}



/**
* Returns a boolean
* check if user started to use the plugin before ( strictly < ) the requested version
* @param $_ver : string free version
*/
function sek_user_started_before_version( $requested_version ) {
    $started_with = get_option( 'nimble_started_with_version' );
    if ( ! $started_with )
      return false;

    if ( ! is_string( $requested_version ) )
      return false;

    return version_compare( $started_with , $requested_version, '<' );
}
add_filter( 'skp_get_skope_id', '\Nimble\sek_filter_skp_get_skope_id', 10, 2 );
function sek_filter_skp_get_skope_id( $skope_id, $level ) {
    if ( 'local' === $level && is_array( $_POST ) && ! empty( $_POST['local_skope_id'] ) && 'customize_save' === $_POST['action'] ) {
        $skope_id = $_POST['local_skope_id'];
    }
    return $skope_id;
}

?><?php
function sek_maybe_do_version_mapping() {
    if ( ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) )
      return;
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    $global_options = is_array( $global_options ) ? $global_options : array();
    $global_options['retro_compat_mappings'] = isset( $global_options['retro_compat_mappings'] ) ? $global_options['retro_compat_mappings'] : array();

    if ( ! array_key_exists( '1_0_4_to_1_1_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] ) {
        $status_1_0_4_to_1_1_0 = sek_do_compat_1_0_4_to_1_1_0();
        $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] = 'done';
    }
    update_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS, $global_options );
}
function sek_do_compat_1_0_4_to_1_1_0() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
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
        $r = wp_update_post( wp_slash( $new_post_data ), true );
        if ( is_wp_error( $r ) ) {
            $status = 'error';
            sek_error_log( __FUNCTION__ . ' => error', $r );
        }
    }//foreach
    return $status;
}
function sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $seks_data ) {
    $new_seks_data = array();
    foreach ( $seks_data as $key => $value ) {
        if ( is_array($value) && array_key_exists('level', $value) && ! array_key_exists('ver_ini', $value) ) {
            $value['ver_ini'] = '1.0.4';
        }
        $new_seks_data[$key] = $value;
        if ( ! empty( $value ) && is_array( $value ) && 'options' === $key ) {
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
        else if ( is_array( $value ) && array_key_exists('module_type', $value ) ) {
            $new_seks_data[$key] = $value;
            $new_value = $value['value'];

            switch ( $value['module_type'] ) {
                case 'czr_image_module':
                    if ( is_array( $value['value'] ) ) {
                        if ( array_key_exists( 'main_settings', $value['value'] ) || array_key_exists( 'borders_corners', $value['value'] ) )
                          break;
                        $new_value = array( 'main_settings' => array(), 'borders_corners' => array() );
                        foreach ( $value['value'] as $input_id => $input_data ) {
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
                        if ( array_key_exists( 'main_settings', $value['value'] ) || array_key_exists( 'font_settings', $value['value'] ) )
                          break;
                        $new_value = array( 'main_settings' => array(), 'font_settings' => array() );
                        foreach ( $value['value'] as $input_id => $input_data ) {
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
        else if ( is_array($value) ) {
            $new_seks_data[$key] = sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $value );
        }
    }
    return $new_seks_data;
}
function sek_map_compat_1_0_4_to_1_1_0_do_level_spacing_mapping( $old_user_data ) {
    $old_data_structure = array(
        'desktop_pad_marg',
        'desktop_unit',
        'tablet_pad_marg',
        'tablet_unit',
        'mobile_pad_marg',
        'mobile_unit'
    );
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
register_post_type( NIMBLE_CPT , array(
    'labels' => array(
      'name'          => __( 'Nimble sections', 'text_domain_to_be_replaced' ),
      'singular_name' => __( 'Nimble sections', 'text_domain_to_be_replaced' ),
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
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }

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
    if ( 1 > $post_id ) {
        return;
    }

    if ( ! is_int( $post_id ) ) {
        error_log( 'sek_get_seks_post => post_id ! is_int() for options => ' . $option_name );
    }

    if ( is_int( $post_id ) && $post_id > 0 && get_post( $post_id ) ) {
        $post = get_post( $post_id );
    }
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

    return $post;
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
        $post = sek_get_seks_post( $skope_id );
        if ( $post ) {
            $seks_data = maybe_unserialize( $post->post_content );
        }
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        $default_collection = sek_get_default_location_model( $skope_id );
        $seks_data = wp_parse_args( $seks_data, $default_collection );
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
        if ( $is_global_skope ) {
            Nimble_Manager()->global_seks = $seks_data;
        } else {
            Nimble_Manager()->local_seks = $seks_data;
        }

    }//end if
    $seks_data = apply_filters(
        'sek_get_skoped_seks',
        $seks_data,
        $skope_id,
        $location_id
    );
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
    $post = sek_get_seks_post( $skope_id );

    if ( $post ) {
        $post_data['ID'] = $post->ID;
        $r = wp_update_post( wp_slash( $post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $post_data ), true );
        if ( ! is_wp_error( $r ) ) {
            $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
            $post_id = $r;//$r is the post ID

            update_option( $option_name, (int)$post_id );
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










/* ------------------------------------------------------------------------- *
 *  SAVED SEKTIONS
/* ------------------------------------------------------------------------- */
register_post_type( 'nimble_saved_seks' , array(
    'labels' => array(
      'name'          => __( 'Nimble saved sections', 'text_domain_to_be_replaced' ),
      'singular_name' => __( 'Nimble saved sections', 'text_domain_to_be_replaced' ),
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
function sek_get_saved_sektion_data( $saved_section_id ) {
    $sek_post = sek_get_saved_seks_post( $saved_section_id );
    $section_data = array();
    if ( $sek_post ) {
        $section_data_decoded = maybe_unserialize( $sek_post -> post_content );
        if ( is_array( $section_data_decoded ) && ! empty( $section_data_decoded['data'] ) && is_string( $section_data_decoded['data'] ) ) {
            $section_data = json_decode( wp_unslash( $section_data_decoded['data'], true ) );
        }
    }
    return $section_data;
}

/**
 * Fetch the `nimble_saved_seks` post for a given {skope_id}
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A theme object stylesheet name. Defaults to the current theme.
 * @return WP_Post|null The skope post or null if none exists.
 */
function sek_get_saved_seks_post( $saved_section_id ) {

    $post = null;
    $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
    $section_data = array_key_exists( $saved_section_id, $all_saved_seks ) ? $all_saved_seks[$saved_section_id] : array();
    $post_id = array_key_exists( 'post_id', $section_data ) ? $section_data['post_id'] : -1;
    if ( 0 > $post_id ) {
        return;
    }

    if ( ! is_int( $post_id ) ) {
        error_log( __FUNCTION__ .' => post_id ! is_int() for options => ' . $saved_section_id );
    }

    if ( is_int( $post_id ) && $post_id > 0 && get_post( $post_id ) ) {
        $post = get_post( $post_id );
    }

    return $post;
}
function sek_update_saved_seks_post( $seks_data ) {
    if ( ! is_array( $seks_data ) ) {
        error_log( 'sek_update_saved_seks_post => $seks_data is not an array' );
        return new \WP_Error( 'sek_update_saved_seks_post => $seks_data is not an array');
    }

    $seks_data = wp_parse_args( $seks_data, array(
        'title' => '',
        'description' => '',
        'id' => '',
        'type' => 'content',//in the future will be used to differentiate header, content and footer sections
        'creation_date' => date("Y-m-d H:i:s"),
        'update_date' => '',
        'data' => array(),
        'nimble_version' => NIMBLE_VERSION
    ));

    $saved_section_id = NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS . $seks_data['id'];

    $post_data = array(
        'post_title' => $saved_section_id,
        'post_name' => sanitize_title( $saved_section_id ),
        'post_type' => 'nimble_saved_seks',
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $seks_data )
    );
    $post = sek_get_saved_seks_post( $saved_section_id );

    if ( $post ) {
        $post_data['ID'] = $post->ID;
        $r = wp_update_post( wp_slash( $post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $post_data ), true );
        if ( ! is_wp_error( $r ) ) {
            $post_id = $r;//$r is the post ID

            $all_saved_seks = get_option(NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS);
            $all_saved_seks = is_array( $all_saved_seks ) ? $all_saved_seks : array();

            $all_saved_seks[ $saved_section_id ] = array(
                'post_id'       => (int)$post_id,
                'title'         => $seks_data['title'],
                'description'   => $seks_data['description'],
                'creation_date' => $seks_data['creation_date'],
                'type'          => $seks_data['type'],
                'nimble_version' => NIMBLE_VERSION
            );

            update_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS, $all_saved_seks );
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
function sek_generate_css_rules_for_multidimensional_border_options( $rules, $border_settings, $border_type, $css_selectors = '' ) {
    if ( ! is_array( $rules ) )
      return array();

    $default_data = array( 'wght' => '1px', 'col' => '#000000' );
    if ( array_key_exists('_all_', $border_settings) ) {
        $default_data = wp_parse_args( $border_settings['_all_'] , $default_data );
    }

    $css_rules = array();
    foreach ( $border_settings as $border_dimension => $data ) {
        if ( ! is_array( $data ) ) {
            sek_error_log( __FUNCTION__ . " => ERROR, the border setting should be an array formed like : array( 'wght' => '1px', 'col' => '#000000' )");
        }
        $data = wp_parse_args( $data, $default_data );

        $border_properties = array();
        $numeric = sek_extract_numeric_value( $data['wght'] );
        if ( is_numeric( $numeric ) ) {
            $unit = sek_extract_unit( $data['wght'] );
            $border_properties[] = $numeric . $unit;
            $border_properties[] = $border_type;
            if ( ! empty( $data[ 'col' ] ) ) {
                $border_properties[] = $data[ 'col' ];
            }

            $css_property = 'border';
            if ( '_all_' !== $border_dimension ) {
                $css_property = 'border-' . $border_dimension;
            }

            $css_rules[] = "{$css_property}:" . implode( ' ', array_filter( $border_properties ) );
        }//if ( !empty( $numeric ) )
    }//foreach
    $rules[]     = array(
        'selector' => $css_selectors,
        'css_rules' => implode( ';', array_filter( $css_rules ) ),//"border:" . implode( ' ', array_filter( $border_properties ) ),
        'mq' =>null
    );
    return $rules;
}
function sek_generate_css_rules_for_border_radius_options( $rules, $border_radius_settings, $css_selectors = '' ) {
    if ( ! is_array( $rules ) )
      return array();

    if ( empty( $border_radius_settings ) )
      return $rules;

    $default_radius = '0px';
    if ( array_key_exists('_all_', $border_radius_settings ) ) {
        $default_val = sek_extract_numeric_value( $border_radius_settings['_all_'] );
        if ( is_numeric( $default_val ) ) {
            $unit = sek_extract_unit( $border_radius_settings['_all_'] );
            $default_radius = $default_val . $unit;
        }
    }
    $radius_dimensions_default = array(
        'top_left' => $default_radius,
        'top_right' => $default_radius,
        'bottom_right' => $default_radius,
        'bottom_left' => $default_radius
    );

    $css_rules = '';
    if ( array_key_exists( '_all_', $border_radius_settings ) && 1 === count( $border_radius_settings ) ) {
        $css_rules = "border-radius:" . $default_radius.';';
    } else {
        $normalized_border_radius_values = array();
        foreach ( $radius_dimensions_default as $dim => $default_radius) {
            if ( array_key_exists( $dim, $border_radius_settings ) ) {
                $numeric = sek_extract_numeric_value( $border_radius_settings[$dim] );
                if ( is_numeric( $numeric ) ) {
                    $unit = sek_extract_unit( $border_radius_settings[$dim] );
                    $normalized_border_radius_values[] = $numeric . $unit;
                } else {
                    $normalized_border_radius_values[] = $default_radius;
                }
            } else {
                $normalized_border_radius_values[] = $default_radius;
            }
        }
        $css_rules = "border-radius:" . implode( ' ', array_filter( $normalized_border_radius_values ) ).';';
    }

    if ( ! empty( $css_rules ) ) {
        $rules[]     = array(
            'selector' => $css_selectors,
            'css_rules' => $css_rules,
            'mq' => null
        );
    }

    return $rules;
}
function sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $spacing_settings, $css_selectors = '' ) {
    if ( empty( $spacing_settings ) || ! is_array( $spacing_settings ) )
      return $rules;


    $default_unit = 'px';
    $_desktop_rules = $_mobile_rules = $_tablet_rules = null;

    if ( !empty( $spacing_settings['desktop'] ) ) {
         $_desktop_rules = array( 'rules' => $spacing_settings['desktop'] );
    }
    $_pad_marg = array(
        'desktop' => array(),
        'tablet' => array(),
        'mobile' => array()
    );

    foreach( array_keys( $_pad_marg ) as $device  ) {
        if ( !empty( $spacing_settings[ $device ] ) ) {
            $rules_candidates = $spacing_settings[ $device ];
            $unit                 = !empty( $rules_candidates['unit'] ) ? $rules_candidates['unit'] : $default_unit;
            $unit                 = 'percent' == $unit ? '%' : $unit;

            $filtered_rules_candidates = array_filter( $rules_candidates, function( $k ) {
                return 'unit' !== $k;
            }, ARRAY_FILTER_USE_KEY );

            $_pad_marg[ $device ] = array( 'rules' => $filtered_rules_candidates );

            array_walk( $_pad_marg[ $device ][ 'rules' ],
                function( &$val, $key, $unit ) {
                    if ( FALSE !== strpos( 'padding', $key ) ) {
                        $val = abs( $val );
                    }

                    $val .= $unit;
            }, $unit );
        }
    }


    /*
    * TABLETS AND MOBILES WILL INHERIT UPPER MQ LEVELS IF NOT OTHERWISE SPECIFIED
    */
    if ( ! empty( $_pad_marg[ 'desktop' ] ) ) {
        $_pad_marg[ 'desktop' ][ 'mq' ] = null;
    }

    if ( ! empty( $_pad_marg[ 'tablet' ] ) ) {
        $_pad_marg[ 'tablet' ][ 'mq' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['md'] - 1 ) . 'px)'; //max-width: 767
    }

    if ( ! empty( $_pad_marg[ 'mobile' ] ) ) {
        $_pad_marg[ 'mobile' ][ 'mq' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['sm'] - 1 ) . 'px)'; //max-width: 575
    }

    foreach( array_filter( $_pad_marg ) as $_spacing_rules ) {
        $css_rules = implode(';',
            array_map( function( $key, $value ) {
                return "$key:{$value};";
            }, array_keys( $_spacing_rules[ 'rules' ] ), array_values( $_spacing_rules[ 'rules' ] )
        ) );

        $rules[] = array(
            'selector' => $css_selectors,//'[data-sek-id="'.$level['id'].'"]',
            'css_rules' => $css_rules,
            'mq' =>$_spacing_rules[ 'mq' ]
        );
    }
    return $rules;
}
function sek_set_mq_css_rules( $params, $rules ) {
    $params = wp_parse_args( $params, array(
        'value' => array(),
        'css_property' => '',
        'selector' => '',
        'is_important' => false
    ));
    if ( ! empty( $params['value'][ 'desktop' ] ) ) {
        $_font_size_mq[ 'desktop' ] = null;
    }

    if ( ! empty( $params['value'][ 'tablet' ] ) ) {
        $_font_size_mq[ 'tablet' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['md'] - 1 ) . 'px)'; //max-width: 767
    }

    if ( ! empty( $params['value'][ 'mobile' ] ) ) {
        $_font_size_mq[ 'mobile' ]  = '(max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['sm'] - 1 ) . 'px)'; //max-width: 575
    }
    foreach ( $params['value'] as $device => $val ) {
        if ( ! in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
            sek_error_log( __FUNCTION__ . ' => error => unknown device : ' . $device );
            continue;
        }
        if ( ! empty(  $val ) ) {
            $rules[] = array(
                'selector' => $params['selector'],
                'css_rules' => sprintf( '%1$s:%2$s%3$s;', $params['css_property'], $val, $params['is_important'] ? '!important' : '' ),
                'mq' => $_font_size_mq[ $device ]
            );
        }
    }
    return $rules;
}
/**
* SASS COLOR DARKEN/LIGHTEN UTILS
*/

/**
 *  Darken hex color
 */
function sek_darken_hex( $hex, $percent, $make_prop_value = true ) {
    $hsl        = sek_hex2hsl( $hex );

    $dark_hsl   = sek_darken_hsl( $hsl, $percent );
    return sek_hsl2hex( $dark_hsl, $make_prop_value );
}



/**
 *Lighten hex color
 */
function sek_lighten_hex($hex, $percent, $make_prop_value = true) {
    $hsl         = sek_hex2hsl( $hex );

    $light_hsl   = sek_lighten_hsl( $hsl, $percent );
    return sek_hsl2hex( $light_hsl, $make_prop_value );
}


/**
 * Darken rgb color
 */
function sek_darken_rgb( $rgb, $percent, $array = false, $make_prop_value = false ) {
    $hsl      = sek_rgb2hsl( $rgb, true );
    $dark_hsl   = sek_darken_hsl( $hsl, $percent );

    return sek_hsl2rgb( $dark_hsl, $array, $make_prop_value );
}


/**
 * Lighten rgb color
 */
function sek_lighten_rgb($rgb, $percent, $array = false, $make_prop_value = false ) {
    $hsl      = sek_rgb2hsl( $rgb, true );

    $light_hsl = sek_lighten_hsl( $light_hsl, $percent );

    return sek_hsl2rgb( $light_hsl, $array, $make_prop_value );
}



/**
 * Darken/Lighten hsl
 */
function sek_darken_hsl( $hsl, $percentage, $array = true ) {
    $percentage = trim( $percentage, '% ' );

    $hsl[2] = ( $hsl[2] * 100 ) - $percentage;
    $hsl[2] = ( $hsl[2] < 0 ) ? 0: $hsl[2]/100;

    if ( !$array ) {
        $hsl = implode( ",", $hsl );
    }

    return $hsl;
}


/**
 * Lighten hsl
 */
function sek_lighten_hsl( $hsl, $percentage, $array = true  ) {
    $percentage = trim( $percentage, '% ' );

    $hsl[2] = ( $hsl[2] * 100 ) + $percentage;
    $hsl[2] = ( $hsl[2] > 100 ) ? 1 : $hsl[2]/100;

    if ( !$array ) {
        $hsl = implode( ",", $hsl );
    }
    return $hsl;
}



/**
 *  Convert hexadecimal to rgb
 */
function sek_hex2rgb( $hex, $array = false, $make_prop_value = false ) {
    $hex = trim( $hex, '# ' );

    if ( 3 == strlen( $hex ) ) {
        $r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
        $g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
        $b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );

    }
    else {
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );

    }

    $rgb = array( $r, $g, $b );

    if ( !$array ) {
        $rgb = implode( ",", $rgb );
        $rgb = $make_prop_value ? "rgb($rgb)" : $rgb;
    }

    return $rgb;
}


/**
 *  Convert hexadecimal to rgba
 */
 function sek_hex2rgba( $hex, $alpha = 0.7, $array = false, $make_prop_value = false ) {
    $rgb = $rgba = sek_hex2rgb( $hex, $_array = true );

    $rgba[]     = $alpha;

    if ( !$array ) {
       $rgba = implode( ",", $rgba );
       $rgba = $make_prop_value ? "rgba($rgba)" : $rgba;
    }

    return $rgba;
}



/**
 *  Convert rgb to rgba
 */
function sek_rgb2rgba( $rgb, $alpha = 0.7, $array = false, $make_prop_value = false ) {
    $rgb   = is_array( $rgb ) ? $rgb : explode( ',', $rgb );
    $rgb   = is_array( $rgb) ? $rgb : array( $rgb );
    $rgb   = count( $rgb ) < 3 ? array_pad( $rgb, 3, 255 ) : $rgb;

    $rgba[] = $alpha;

    if ( !$array ) {
        $rgba = implode( ",", $rgba );
        $rgba = $make_prop_value ? "rgba($rgba)" : $rgba;
    }

    return $rgba;
}


/*
* Following heavily based on
* https://github.com/mexitek/phpColors
* MIT License
*/
/**
 *  Convert rgb to hexadecimal
 */
function sek_rgb2hex( $rgb, $make_prop_value = false ) {
    $rgb = is_array( $rgb ) ? $rgb : explode( ',', $rgb );
    $rgb = is_array( $rgb) ? $rgb : array( $rgb );
    $rgb = count( $rgb ) < 3 ? array_pad( $rgb, 3, 255 ) : $rgb;
    $hex[0] = str_pad( dechex( $rgb[0] ), 2, '0', STR_PAD_LEFT );
    $hex[1] = str_pad( dechex( $rgb[1] ), 2, '0', STR_PAD_LEFT );
    $hex[2] = str_pad( dechex( $rgb[2] ), 2, '0', STR_PAD_LEFT );

    $hex = implode( '', $hex );

    return $make_prop_value ? "#{$hex}" : $hex;
}


/**
 *  Convert rgba to rgb array + alpha
 */
function sek_rgba2rgb_a( $rgba ) {
    $rgba = is_array( $rgba ) ? $rgba : explode( ',', $rgba );
    $rgba = is_array( $rgba) ? $rgba : array( $rgba );
    return array(
        array_slice( $rgba, 0, 3 ),
        $rgba[4]
    );
}
/*
* heavily based on
* phpColors
*/
/**
 *  Convert rgb to hsl
 */
function sek_rgb2hsl( $rgb, $array = false ) {
    $rgb       = is_array( $rgb ) ? $rgb : explode( ',', $rgb );
    $rgb       = is_array( $rgb) ? $rgb : array( $rgb );
    $rgb       = count( $rgb ) < 3 ? array_pad( $rgb, 3, 255 ) : $rgb;

    $deltas    = array();

    $RGB       = array(
        'R'   => ( $rgb[0] / 255 ),
        'G'   => ( $rgb[1] / 255 ),
        'B'   => ( $rgb[2] / 255 )
    );


    $min       = min( array_values( $RGB ) );
    $max       = max( array_values( $RGB ) );
    $span      = $max - $min;

    $H = $S    = 0;
    $L         = ($max + $min)/2;

    if ( 0 != $span ) {

        if ( $L < 0.5 ) {
            $S = $span / ( $max + $min );
        }
        else {
            $S = $span / ( 2 - $max - $min );
        }

        foreach ( array( 'R', 'G', 'B' ) as $var ) {
            $deltas[$var] = ( ( ( $max - $RGB[$var] ) / 6 ) + ( $span / 2 ) ) / $span;
        }

        if ( $max == $RGB['R'] ) {
            $H = $deltas['B'] - $deltas['G'];
        }
        else if ( $max == $RGB['G'] ) {
            $H = ( 1 / 3 ) + $deltas['R'] - $deltas['B'];
        }
        else if ( $max == $RGB['B'] ) {
            $H = ( 2 / 3 ) + $deltas['G'] - $deltas['R'];
        }

        if ( $H<0 ) {
            $H++;
        }

        if ( $H>1 ) {
            $H--;
        }
    }

    $hsl = array( $H*360, $S, $L );


    if ( !$array ) {
        $hsl = implode( ",", $hsl );
    }

    return $hsl;
}


/**
 * Convert hsl to rgb
*/
function sek_hsl2rgb( $hsl, $array=false, $make_prop_value = false ) {
    list($H,$S,$L) = array( $hsl[0]/360, $hsl[1], $hsl[2] );

    $rgb           = array_fill( 0, 3, $L * 255 );

    if ( 0 !=$S ) {
        if ($L < 0.5 ) {
            $var_2 = $L * ( 1 + $S );
        } else {
            $var_2 = ( $L + $S ) - ( $S * $L );
        }

        $var_1  = 2 * $L - $var_2;

        $rgb[0] = sek_hue2rgbtone( $var_1, $var_2, $H + ( 1/3 ) );
        $rgb[1] = sek_hue2rgbtone( $var_1, $var_2, $H );
        $rgb[2] = sek_hue2rgbtone( $var_1, $var_2, $H - ( 1/3 ) );
    }

    if ( !$array ) {
        $rgb = implode(",", $rgb);
        $rgb = $make_prop_value ? "rgb($rgb)" : $rgb;
    }

    return $rgb;
}


/**
 * Convert hsl to hex
 */
function sek_hsl2hex( $hsl = array(), $make_prop_value = false ) {
    $rgb = sek_hsl2rgb( $hsl, $array = true );
    return sek_rgb2hex( $rgb, $make_prop_value );
}


/**
 * Convert hex to hsl
 */
function sek_hex2hsl( $hex ) {
    $rgb = sek_hex2rgb( $hex, true );
    return sek_rgb2hsl( $rgb, true );
}

/**
 * Convert hue to rgb
 */
function sek_hue2rgbtone( $v1, $v2, $vH ) {
    $_to_return = $v1;

    if( $vH < 0 ) {
        $vH += 1;
    }
    if( $vH > 1 ) {
        $vH -= 1;
    }

    if ( (6*$vH) < 1 ) {
        $_to_return = ($v1 + ($v2 - $v1) * 6 * $vH);
    }
    elseif ( (2*$vH) < 1 ) {
        $_to_return = $v2;
    }
    elseif ( (3*$vH) < 2 ) {
        $_to_return = ($v1 + ($v2-$v1) * ( (2/3)-$vH ) * 6);
    }

    return round( 255 * $_to_return );
}



/*
 *  Returns the complementary hsl color
 */
function sek_rgb_invert( $rgb )  {
    $rgb_inverted =  array(
        255 - $rgb[0],
        255 - $rgb[1],
        255 - $rgb[2]
    );

    return $rgb_inverted;
}


/**
 * Returns the complementary hsl color
 */
function sek_hex_invert( $hex, $make_prop_value = true )  {
    $rgb           = sek_hex2rgb( $hex, $array = true );
    $rgb_inverted  = sek_rgb_invert( $rgb );

    return sek_rgb2hex( $rgb_inverted, $make_prop_value );
}
function sek_extract_unit( $value ) {
    $unit = preg_replace('/[0-9]|\.|,/', '', $value );
    return  0 === preg_match( "/(px|em|%)/i", $unit ) ? 'px' : $unit;
}
function sek_extract_numeric_value( $value ) {
    if ( ! is_scalar( $value ) )
      return null;
    $numeric = preg_replace('/px|em|%/', '', $value);
    return is_numeric( $numeric ) ? $numeric : null;
}


?><?php
add_action( 'after_setup_theme', '\Nimble\sek_register_modules', 50 );
function sek_register_modules() {
    foreach( [
        'sek_content_type_switcher_module',
        'sek_module_picker_module',

        'sek_intro_sec_picker_module',
        'sek_features_sec_picker_module',
        'sek_contact_sec_picker_module',
        'sek_column_layouts_sec_picker_module',
        'sek_my_sections_sec_picker_module',
        'sek_level_bg_module',
        'sek_level_border_module',
        'sek_level_height_module',
        'sek_level_spacing_module',
        'sek_level_width_module',
        'sek_level_width_section',
        'sek_level_anchor_module',
        'sek_level_visibility_module',
        'sek_level_breakpoint_module',
        'sek_local_template',
        'sek_local_widths',
        'sek_local_custom_css',
        'sek_local_reset',
        'sek_local_performances',
        'sek_global_breakpoint',
        'sek_global_widths',
        'sek_global_performances',
        'czr_simple_html_module',

        'czr_tiny_mce_editor_module',
        'czr_tinymce_child',

        'czr_image_module',
        'czr_image_main_settings_child',
        'czr_image_borders_corners_child',
        'czr_heading_module',
        'czr_heading_child',
        'czr_heading_spacing_child',

        'czr_spacer_module',
        'czr_divider_module',

        'czr_icon_module',
        'czr_icon_settings_child',
        'czr_icon_spacing_border_child',

        'czr_map_module',

        'czr_quote_module',
        'czr_quote_quote_child',
        'czr_quote_cite_child',
        'czr_quote_design_child',

        'czr_button_module',
        'czr_btn_content_child',
        'czr_btn_design_child',
        'czr_simple_form_module',
        'czr_simple_form_fields_child',
        'czr_simple_form_button_child',
        'czr_simple_form_design_child',
        'czr_simple_form_fonts_child',
        'czr_simple_form_submission_child',
        'czr_font_child'
    ] as $module_name ) {
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
function sek_get_select_options_for_input_id( $input_id ) {
    $options = array();
    switch( $input_id ) {
        case 'img-link-to' :
            $options = array(
                'no-link' => __('No link', 'text_domain_to_be_replaced' ),
                'url' => __('Site content or custom url', 'text_domain_to_be_replaced' ),
                'img-file' => __('Image file', 'text_domain_to_be_replaced' ),
                'img-page' =>__('Image page', 'text_domain_to_be_replaced' )
            );
        break;
        case 'img_hover_effect' :
            $options = array(
                'none' => __('No effect', 'text_domain_to_be_replaced' ),
                'opacity' => __('Opacity', 'text_domain_to_be_replaced' ),
                'zoom-out' => __('Zoom out', 'text_domain_to_be_replaced' ),
                'zoom-in' => __('Zoom in', 'text_domain_to_be_replaced' ),
                'move-up' =>__('Move up', 'text_domain_to_be_replaced' ),
                'move-down' =>__('Move down', 'text_domain_to_be_replaced' ),
                'blur' =>__('Blur', 'text_domain_to_be_replaced' ),
                'grayscale' =>__('Grayscale', 'text_domain_to_be_replaced' ),
                'reverse-grayscale' =>__('Reverse grayscale', 'text_domain_to_be_replaced' )
            );
        break;
        case 'img-size' :
            $options = sek_get_img_sizes();
        break;
        case 'link-to' :
            $options = array(
                'no-link' => __('No link', 'text_domain_to_be_replaced' ),
                'url' => __('Site content or custom url', 'text_domain_to_be_replaced' ),
            );
        break;
        case 'img-type' :
            $options = array(
                'none' => __( 'No image', 'text_domain_to_be_replaced' ),
                'featured' => __( 'Use the page featured image', 'text_domain_to_be_replaced' ),
                'custom' => __( 'Use a custom image', 'text_domain_to_be_replaced' ),
            );
        break;
        case 'content-type' :
            $options = array(
                'none' => __( 'No text', 'text_domain_to_be_replaced' ),
                'page-excerpt' => __( 'Use the page excerpt', 'text_domain_to_be_replaced' ),
                'custom' => __( 'Use a custom text', 'text_domain_to_be_replaced' ),
            );
        break;
        case 'heading_tag':
            $options = array(
                /* Not totally sure these should be localized as they strictly refer to html tags */
                'h1' => __('H1', 'text_domain_to_be_replaced' ),
                'h2' => __('H2', 'text_domain_to_be_replaced' ),
                'h3' => __('H3', 'text_domain_to_be_replaced' ),
                'h4' => __('H4', 'text_domain_to_be_replaced' ),
                'h5' => __('H5', 'text_domain_to_be_replaced' ),
                'h6' => __('H6', 'text_domain_to_be_replaced' ),
            );
        break;
        case 'font_weight_css' :
            $options = array(
                'normal'  => __( 'normal', 'text_domain_to_be_replaced' ),
                'bold'    => __( 'bold', 'text_domain_to_be_replaced' ),
                'bolder'  => __( 'bolder', 'text_domain_to_be_replaced' ),
                'lighter'   => __( 'lighter', 'text_domain_to_be_replaced' ),
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
                'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                'italic'  => __( 'italic', 'text_domain_to_be_replaced' ),
                'normal'  => __( 'normal', 'text_domain_to_be_replaced' ),
                'oblique' => __( 'oblique', 'text_domain_to_be_replaced' )
            );
        break;
        case 'text_decoration_css'  :
            $options = array(
                'none'      => __( 'none', 'text_domain_to_be_replaced' ),
                'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                'line-through' => __( 'line-through', 'text_domain_to_be_replaced' ),
                'overline'    => __( 'overline', 'text_domain_to_be_replaced' ),
                'underline'   => __( 'underline', 'text_domain_to_be_replaced' )
            );
        break;
        case 'text_transform_css' :
            $options = array(
                'none'      => __( 'none', 'text_domain_to_be_replaced' ),
                'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                'capitalize'  => __( 'capitalize', 'text_domain_to_be_replaced' ),
                'uppercase'   => __( 'uppercase', 'text_domain_to_be_replaced' ),
                'lowercase'   => __( 'lowercase', 'text_domain_to_be_replaced' )
            );
        break;
        case 'css_unit' :
            $options = array(
                'px' => __('Pixels', 'text_domain_to_be_replaced' ),
                'em' => __('Em', 'text_domain_to_be_replaced'),
                'percent' => __('Percents', 'text_domain_to_be_replaced' )
            );
        break;
        case 'quote_design' :
            $options = array(
                'none' => __( 'Text only', 'text_domain_to_be_replaced' ),
                'border-before' => __( 'Side Border', 'text_domain_to_be_replaced' ),
                'quote-icon-before' => __( 'Quote Icon', 'text_domain_to_be_replaced' ),
            );
        break;
        case 'boxed-wide' :
            $options = array(
                'boxed' => __('Boxed', 'text_domain_to_be_replaced'),
                'fullwidth' => __('Full Width', 'text_domain_to_be_replaced')
            );
        break;
        case 'height-type' :
            $options = array(
                'auto' => __('Adapt to content', 'text_domain_to_be_replaced'),
                'custom' => __('Custom', 'text_domain_to_be_replaced' )
            );
        break;
        case 'width-type' :
            $options = array(
                'default' => __('default', 'text_domain_to_be_replaced'),
                'custom' => __('Custom', 'text_domain_to_be_replaced' )
            );
        break;
        case 'bg-scale' :
            $options = array(
                'default' => __('default', 'text_domain_to_be_replaced'),
                'auto' => __('auto', 'text_domain_to_be_replaced'),
                'cover' => __('scale to fill', 'text_domain_to_be_replaced'),
                'contain' => __('fit', 'text_domain_to_be_replaced'),
            );
        break;
        case 'bg-position' :
            $options = array(
                'default' => __('default', 'text_domain_to_be_replaced'),
            );
        break;
        case 'border-type' :
            $options = array(
                'none' => __('none', 'text_domain_to_be_replaced'),
                'solid' => __('solid', 'text_domain_to_be_replaced'),
                'double' => __('double', 'text_domain_to_be_replaced'),
                'dotted' => __('dotted', 'text_domain_to_be_replaced'),
                'dashed' => __('dashed', 'text_domain_to_be_replaced')
            );
        break;
        case 'local_template' :
            $options = array(
                'default' => __('Default theme template','text_domain_to_be_replaced'),
                'nimble_template' => __('Template with Nimble Builder content. Header and footer from the theme','text_domain_to_be_replaced'),
                'nimble_full_tmpl_ghf' => __('Full Nimble Builder template (beta)','text_domain_to_be_replaced')
            );
        break;

        default :
            sek_error_log( __FUNCTION__ . ' => no case set for input id : '. $input_id );
        break;
    }
    return $options;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_sek_content_type_switcher_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_content_type_switcher_module',
        'name' => __('Content type', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'content_type' => array(
                    'input_type'  => 'content_type_switcher',
                    'title'       => '',//__('Which type of content would you like to drop in your page ?', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => sprintf(
                        __('Note : you can %1$s to replace your default theme template. Depending on your theme structure, it can allow you to display your sections in full-width mode.'),
                        sprintf('<a href="%2$s" title="%1$s">%1$s</a>',
                            __('use the Nimble page template', 'text-domain'),
                            "javascript:if ( sektionsLocalizedData && sektionsLocalizedData.sektionsPanelId ) { wp.customize.panel(sektionsLocalizedData.sektionsPanelId, function( _panel_ ) { try{wp.customize.czr_sektions.rootPanelFocus(); _panel_.focus();}catch(er){} } ) }"
                        )
                    )
                )
            )
        )
    );
}


/* ------------------------------------------------------------------------- *
 *  MODULE PICKER MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_sek_module_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_module_picker_module',
        'name' => __('Content Picker', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'module_id' => array(
                    'input_type'  => 'module_picker',
                    'title'       => __('Drag and drop modules in the previewed page', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )
    );
}


/* ------------------------------------------------------------------------- *
 *  SEKTION PICKER MODULES
/* ------------------------------------------------------------------------- */
function sek_get_default_section_input_params() {
    return array(
        'input_type'  => 'section_picker',
        'title'       => __('Drag and drop sections in the previewed page', 'text_domain_to_be_replaced'),
        'width-100'   => true,
        'title_width' => 'width-100'
    );

}
function sek_get_module_params_for_sek_intro_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_intro_sec_picker_module',
        'name' => __('Sections for an introduction', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'intro_sections' => sek_get_default_section_input_params()
            )
        )
    );
}
function sek_get_module_params_for_sek_features_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_features_sec_picker_module',
        'name' => __('Sections for services and features', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'features_sections' => sek_get_default_section_input_params()
            )
        )
    );
}

function sek_get_module_params_for_sek_contact_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_contact_sec_picker_module',
        'name' => __('Contact-us sections', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'contact_sections' => sek_get_default_section_input_params()
            )
        )
    );
}

function sek_get_module_params_for_sek_column_layouts_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_column_layouts_sec_picker_module',
        'name' => __('Empty sections with columns layout', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'layout_sections' => sek_get_default_section_input_params()
            )
        )
    );
}

function sek_get_module_params_for_sek_my_sections_sec_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_my_sections_sec_picker_module',
        'name' => __('My sections', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'my_sections' => sek_get_default_section_input_params()
            )
        )
    );
}
?><?php
function sek_get_module_params_for_sek_level_bg_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_bg_module',
        'name' => __('Background', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'bg-color' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Background color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'     => '',
                ),
                'bg-image' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Image', 'text_domain_to_be_replaced'),
                    'default'     => '',
                ),
                'bg-position' => array(
                    'input_type'  => 'bgPositionWithDeviceSwitcher',
                    'title'       => __('Image position', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => 'center' ),
                    'title_width' => 'width-100',
                ),
                'bg-attachment' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Fixed background', 'text_domain_to_be_replaced'),
                    'default'     => 0
                ),
                'bg-parallax' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Parallax effect on scroll', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'notice_after' => __('When enabled, the background image moves slower than the page elements on scroll. This effect is not enabled on mobile devices.', 'text_domain_to_be_replaced'),
                    'refresh_markup' => true,
                ),
                'bg-scale' => array(
                    'input_type'  => 'select',
                    'title'       => __('Scale', 'text_domain_to_be_replaced'),
                    'default'     => 'cover',
                    'choices'     => sek_get_select_options_for_input_id( 'bg-scale' )
                ),
                'bg-apply-overlay' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Apply a background overlay', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0
                ),
                'bg-color-overlay' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Overlay Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'     => '#000000'
                ),
                'bg-opacity-overlay' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Opacity (in percents)', 'text_domain_to_be_replaced'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    'default'  => '40',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_background', 10, 3 );

function sek_add_css_rules_for_level_background( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    $default_value_model  = sek_get_default_module_model( 'sek_level_bg_module' );
    $bg_options = ( ! empty( $options[ 'bg' ] ) && is_array( $options[ 'bg' ] ) ) ? $options[ 'bg' ] : array();
    $bg_options = wp_parse_args( $bg_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $bg_options ) )
      return $rules;

    $background_properties = array();
    $bg_property_selector = '[data-sek-id="'.$level['id'].'"]';

    /* The general syntax of the background property is:
    * https://www.webpagefx.com/blog/web-design/background-css-shorthand/
    * background: [background-image] [background-position] / [background-size] [background-repeat] [background-attachment] [background-origin] [background-clip] [background-color];
    */
    if ( ! empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
        if ( ! sek_is_img_smartload_enabled() ) {
            $background_properties[ 'background-image' ] = 'url("'. wp_get_attachment_url( $bg_options[ 'bg-image'] ) .'")';
        }
        if ( ! empty( $bg_options[ 'bg-position'] ) ) {
            $pos_map = array(
                'top_left'    => '0% 0%',
                'top'         => '50% 0%',
                'top_right'   => '100% 0%',
                'left'        => '0% 50%',
                'center'      => '50% 50%',
                'right'       => '100% 50%',
                'bottom_left' => '0% 100%',
                'bottom'      => '50% 100%',
                'bottom_right'=> '100% 100%'
            );
            if ( is_string( $bg_options[ 'bg-position'] ) ) {
                $raw_pos                    = $bg_options[ 'bg-position'];
                $background_properties[ 'background-position' ] = array_key_exists($raw_pos, $pos_map) ? $pos_map[ $raw_pos ] : $pos_map[ 'center' ];
            } else if ( is_array( $bg_options[ 'bg-position'] ) ) {
                $mapped_bg_options = array();
                foreach ($bg_options[ 'bg-position'] as $device => $user_val ) {
                    if ( ! in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
                        sek_error_log( __FUNCTION__ . ' => error => unknown device : ' . $device );
                        continue;
                    }
                    $mapped_bg_options[$device] = array_key_exists($user_val, $pos_map) ? $pos_map[ $user_val ] : $pos_map[ 'center' ];
                }

                $rules = sek_set_mq_css_rules(array(
                    'value' => $mapped_bg_options,
                    'css_property' => 'background-position',
                    'selector' => $bg_property_selector
                ), $rules );
            }
        }
        if ( ! empty( $bg_options[ 'bg-scale'] ) && 'default' != $bg_options[ 'bg-scale'] ) {
            $background_properties[ 'background-size' ] = $bg_options[ 'bg-scale'];
        }
        $background_properties[ 'background-repeat' ] = 'no-repeat';
        if ( ! empty( $bg_options[ 'bg-attachment'] ) && sek_is_checked( $bg_options[ 'bg-attachment'] ) ) {
            $background_properties[ 'background-attachment' ] = 'fixed';
        }

    }
    if ( ! empty( $bg_options[ 'bg-color' ] ) ) {
        $background_properties[ 'background-color' ] = $bg_options[ 'bg-color' ];
    }
    if ( ! empty( $background_properties ) ) {
        $background_css_rules = '';
        foreach ($background_properties as $bg_prop => $bg_css_val ) {
            $background_css_rules .= sprintf('%1$s:%2$s;', $bg_prop, $bg_css_val );
        }
        $rules[] = array(
            'selector' => $bg_property_selector,
            'css_rules' => $background_css_rules,
            'mq' =>null
        );
    }
    if ( !empty( $bg_options['bg-image']) && ! empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {
        $bg_color_overlay = isset( $bg_options[ 'bg-color-overlay' ] ) ? $bg_options[ 'bg-color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            $bg_overlay_css_rules = 'content:"";display:block;position:absolute;top:0;left:0;right:0;bottom:0;background-color:'.$bg_color_overlay;
            $bg_overlay_opacity     = isset( $bg_options[ 'bg-opacity-overlay' ] ) ? filter_var( $bg_options[ 'bg-opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_css_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_css_rules . ';opacity:' . $bg_overlay_opacity : $bg_overlay_css_rules;

            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]::before',
                    'css_rules' => $bg_overlay_css_rules,
                    'mq' =>null
            );
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => 'position:relative',
                    'mq' => null
            );

            $first_child_selector = '[data-sek-id="'.$level['id'].'"]>*';
            if ( is_customize_preview() ) {
                $first_child_selector .= ':not(.ui-resizable-handle):not(.sek-dyn-ui-wrapper):not(.sek-add-content-button)';
            }
            $rules[]     = array(
                'selector' => $first_child_selector,
                'css_rules' => 'position:relative',
                'mq' =>null
            );
        }
    }//if ( ! empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {}

    return $rules;
}

?><?php
function sek_get_module_params_for_sek_level_border_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_border_module',
        'name' => __('Borders', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'borders' => array(
                '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
            )
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border shape', 'text_domain_to_be_replaced'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' )
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'border-radius'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                ),
                'shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Apply a shadow', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default' => 0
                )
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_border', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_boxshadow', 10, 3 );


function sek_add_css_rules_for_border( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    $default_value_model  = sek_get_default_module_model( 'sek_level_border_module' );
    $normalized_border_options = ( ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) ) ? $options[ 'border' ] : array();
    $normalized_border_options = wp_parse_args( $normalized_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $normalized_border_options ) )
      return $rules;

    $border_settings = ! empty( $normalized_border_options[ 'borders' ] ) ? $normalized_border_options[ 'borders' ] : FALSE;
    $border_type = $normalized_border_options[ 'border-type' ];
    $has_border_settings  = FALSE !== $border_settings && is_array( $border_settings ) && ! empty( $border_type ) && 'none' != $border_type;
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options( $rules, $border_settings, $border_type, '[data-sek-id="'.$level['id'].'"]'  );
    }

    $has_border_radius = ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) && !empty( $options[ 'border' ]['border-radius'] );
    if ( $has_border_radius ) {
        $radius_settings = $normalized_border_options['border-radius'];
        $rules = sek_generate_css_rules_for_border_radius_options( $rules, $normalized_border_options['border-radius'], '[data-sek-id="'.$level['id'].'"]' );
    }

    return $rules;
}



function sek_add_css_rules_for_boxshadow( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    $default_value_model  = sek_get_default_module_model( 'sek_level_border_module' );
    $normalized_border_options = ( ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) ) ? $options[ 'border' ] : array();
    $normalized_border_options = wp_parse_args( $normalized_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $normalized_border_options) )
      return $rules;

    if ( !empty( $normalized_border_options[ 'shadow' ] ) &&  sek_is_checked( $normalized_border_options[ 'shadow'] ) ) {
        $css_rules = '-webkit-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;-moz-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;';
        if ( skp_is_customizing() ) {
            $css_rules = '-webkit-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;-moz-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;';
        }
        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'css_rules' => $css_rules,
                'mq' =>null
        );
    }
    return $rules;
}
?><?php
function sek_get_module_params_for_sek_level_height_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_height_module',
        'name' => __('Height options', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'custom-height'  => array( 'desktop' => '50%' ),
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'height-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Height : auto or custom', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'height-type' )
                ),
                'custom-height' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom height', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '50%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),
                'v_alignment' => array(
                    'input_type'  => 'verticalAlignWithDeviceSwitcher',
                    'title'       => __('Inner vertical alignment', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'v_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_height', 10, 3 );
function sek_add_css_rules_for_level_height( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'height' ] ) )
      return $rules;

    $height_options = is_array( $options[ 'height' ] ) ? $options[ 'height' ] : array();

    if ( ! empty( $height_options[ 'v_alignment' ] ) ) {
        if ( ! is_array( $height_options[ 'v_alignment' ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
        }
        $v_alignment_value = is_array( $height_options[ 'v_alignment' ] ) ? $height_options[ 'v_alignment' ] : array();
        $v_alignment_value = wp_parse_args( $v_alignment_value, array(
            'desktop' => 'center',
            'tablet' => '',
            'mobile' => ''
        ));
        $mapped_values = array();
        foreach ( $v_alignment_value as $device => $align_val ) {
            switch ( $align_val ) {
                case 'top' :
                    $mapped_values[$device] = "flex-start";
                break;
                case 'center' :
                    $mapped_values[$device] = "center";
                break;
                case 'bottom' :
                    $mapped_values[$device] = "flex-end";
                break;
            }
        }
        $rules = sek_set_mq_css_rules( array(
            'value' => $mapped_values,
            'css_property' => 'align-items',
            'selector' => '[data-sek-id="'.$level['id'].'"]'
        ), $rules );
    }
    if ( ! empty( $height_options[ 'height-type' ] ) ) {
        if ( 'custom' === $height_options[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $height_options ) ? $height_options[ 'custom-height' ] : array();
            $selector = '[data-sek-id="'.$level['id'].'"]';
            if ( ! is_array( $custom_user_height ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the height option should be an array( {device} => {number}{unit} )', $custom_user_height);
            }
            $custom_user_height = is_array( $custom_user_height ) ? $custom_user_height : array();
            $custom_user_height = wp_parse_args( $custom_user_height, array(
                'desktop' => '50%',
                'tablet' => '',
                'mobile' => ''
            ));
            $height_value = $custom_user_height;
            foreach ( $custom_user_height as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $unit = '%' === $unit ? 'vh' : $unit;
                    $height_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $height_value,
                'css_property' => 'height',
                'selector' => $selector
            ), $rules );
        }
    }
    return $rules;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_sek_level_spacing_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_spacing_module',
        'name' => __('Spacing options', 'text_domain_to_be_replaced'),

        'tmpl' => array(
            'item-inputs' => array(
                'pad_marg' => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __('Set padding and margin', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'default'     => array( 'desktop' => array() ),
                    'has_device_switcher' => true
                )
            )
        )
    );
}





/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_spacing', 10, 2 );
function sek_add_css_rules_for_spacing( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'spacing' ] ) || empty( $options[ 'spacing' ][ 'pad_marg' ] ) )
      return $rules;
    $pad_marg_options = $options[ 'spacing' ][ 'pad_marg' ];
    if ( ! is_array( $pad_marg_options ) )
      return $rules;

    $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $pad_marg_options, '[data-sek-id="'.$level['id'].'"]' );
    if ( 'column' === $level['level'] && ! empty( $pad_marg_options['desktop'] ) ) {
        $margin_left = array_key_exists('margin-left', $pad_marg_options['desktop'] ) ? $pad_marg_options['desktop']['margin-left'] : 0;
        $margin_right = array_key_exists('margin-right', $pad_marg_options['desktop'] ) ? $pad_marg_options['desktop']['margin-right'] : 0;
        $device_unit = array_key_exists('unit', $pad_marg_options['desktop'] ) ? $pad_marg_options['desktop']['unit'] : 'px';

        $total_horizontal_margin = (int)$margin_left + (int)$margin_right;

        $parent_section = sek_get_parent_level_model( $level['id'] );
        if ( 'no_match' === $parent_section ) {
            sek_error_log( __FUNCTION__ . ' => $parent_section not found for level id : ' . $level['id'] );
            return $rules;
        }

        if ( $total_horizontal_margin > 0 && is_array( $parent_section ) && !empty( $parent_section ) ) {
            $total_horizontal_margin_with_unit = $total_horizontal_margin . $device_unit;//20px

            $col_number = ( array_key_exists( 'collection', $parent_section ) && is_array( $parent_section['collection'] ) ) ? count( $parent_section['collection'] ) : 1;
            $col_number = 12 < $col_number ? 12 : $col_number;

            $col_width_in_percent = 100/$col_number;
            $col_suffix = floor( $col_width_in_percent );
            $custom_width   = ( ! empty( $level[ 'width' ] ) && is_numeric( $level[ 'width' ] ) ) ? $level['width'] : null;
            if ( ! is_null( $custom_width ) ) {
                $col_width_in_percent = $custom_width;
            }
            if ( $col_suffix < 1 )
              return $rules;
            $breakpoint = Sek_Dyn_CSS_Builder::$breakpoints[ Sek_Dyn_CSS_Builder::COLS_MOBILE_BREAKPOINT ];//COLS_MOBILE_BREAKPOINT = 'md' <=> 768px
            $selector = sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );
            $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
            $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;
            $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( $parent_section ) );
            $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

            if ( $has_section_custom_breakpoint ) {
                $breakpoint = $section_custom_breakpoint;
                $selector =  sprintf('[data-sek-level="location"] [data-sek-id="%1$s"] .sek-sektion-inner > .sek-section-custom-breakpoint-col-%2$s[data-sek-id="%3$s"]', $parent_section['id'], $col_suffix, $level['id'] );
            } else if ( $has_global_custom_breakpoint ) {
                $breakpoint = $global_custom_breakpoint;
            }

            $responsive_css_rules = sprintf( '-ms-flex: 0 0 calc(%1$s%% - %2$s) ;flex: 0 0 calc(%1$s%% - %2$s);max-width: calc(%1$s%% - %2$s)', $col_width_in_percent, $total_horizontal_margin_with_unit );
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => $responsive_css_rules,
                'mq' => "(min-width: {$breakpoint}px)"
            );
            $responsive_css_rules_for_100_percent_width = sprintf( '-ms-flex: 0 0 calc(%1$s%% - %2$s) ;flex: 0 0 calc(%1$s%% - %2$s);max-width: calc(%1$s%% - %2$s)', 100, $total_horizontal_margin_with_unit );
            $rules[] = array(
                'selector' => sprintf('.sek-sektion-inner > [data-sek-id="%1$s"]', $level['id'] ),
                'css_rules' => $responsive_css_rules_for_100_percent_width,
                'mq' => null,// "(max-width: {$breakpoint_for_100_percent_width}px)"
            );
        }//if ( $total_horizontal_margin > 0 && !empty( $parent_section ) ) {
    }// if column

    return $rules;
}

?><?php
function sek_get_module_params_for_sek_level_width_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_module',
        'name' => __('Width options', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'width-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Width : 100% or custom', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'width-type' )
                ),
                'custom-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),
                'h_alignment' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Horizontal alignment', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for__module__options', '\Nimble\sek_add_css_rules_for_module_width', 10, 3 );
function sek_add_css_rules_for_module_width( $rules, $module ) {
    $options = empty( $module[ 'options' ] ) ? array() : $module['options'];
    if ( empty( $options[ 'width' ] ) || ! is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = is_array( $options[ 'width' ] ) ? $options[ 'width' ] : array();
    if ( ! empty( $width_options[ 'h_alignment' ] ) ) {
        if ( ! is_array( $width_options[ 'h_alignment' ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the h_alignment option should be an array( {device} => {alignment} )');
        }
        $h_alignment_value = is_array( $width_options[ 'h_alignment' ] ) ? $width_options[ 'h_alignment' ] : array();
        $h_alignment_value = wp_parse_args( $h_alignment_value, array(
            'desktop' => '',
            'tablet' => '',
            'mobile' => ''
        ));
        $mapped_values = array();
        foreach ( $h_alignment_value as $device => $align_val ) {
            switch ( $align_val ) {
                case 'left' :
                    $mapped_values[$device] = "flex-start";
                break;
                case 'center' :
                    $mapped_values[$device] = "center";
                break;
                case 'right' :
                    $mapped_values[$device] = "flex-end";
                break;
            }
        }

        $rules = sek_set_mq_css_rules( array(
            'value' => $mapped_values,
            'css_property' => 'align-self',
            'selector' => '[data-sek-id="'.$module['id'].'"]'
        ), $rules );
    }
    if ( ! empty( $width_options[ 'width-type' ] ) ) {
        if ( 'custom' == $width_options[ 'width-type' ] && array_key_exists( 'custom-width', $width_options ) ) {
            $user_custom_width_value = $width_options[ 'custom-width' ];
            $selector = '[data-sek-id="'.$module['id'].'"]';

            if ( ! empty( $user_custom_width_value ) && ! is_array( $user_custom_width_value ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
            }
            $user_custom_width_value = is_array( $user_custom_width_value ) ? $user_custom_width_value : array();
            $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
                'desktop' => '100%',
                'tablet' => '',
                'mobile' => ''
            ));
            $width_value = $user_custom_width_value;
            foreach ( $user_custom_width_value as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $width_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $width_value,
                'css_property' => 'width',
                'selector' => $selector
            ), $rules );
        }
    }
    return $rules;
}

?><?php
function sek_get_module_params_for_sek_level_width_section() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_section',
        'name' => __('Width options', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom outer width for this section', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer section width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom inner width for this section', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner section width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for__section__options', '\Nimble\sek_add_css_rules_for_section_width', 10, 3 );
function sek_add_css_rules_for_section_width( $rules, $section ) {
    $options = empty( $section[ 'options' ] ) ? array() : $section['options'];
    if ( empty( $options[ 'width' ] ) || ! is_array( $options[ 'width' ] ) )
      return $rules;

    $width_options = $options[ 'width' ];
    $user_defined_widths = array();

    if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"]';
    }
    if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"] > .sek-container-fluid > .sek-sektion-inner';
    }

    if ( empty( $user_defined_widths ) )
      return $rules;
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
                $padding_of_the_parent_container[$device] = 'inherit';
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $max_width_value,
            'css_property' => 'max-width',
            'selector' => $selector
        ), $rules );
        if ( 'inner-section-width' === $width_opt_name ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-left',
                'selector' => 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"] > .sek-container-fluid'
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => 'body .sektion-wrapper [data-sek-id="'.$section['id'].'"] > .sek-container-fluid'
            ), $rules );
        }

        if ( ! empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector
            ), $rules );
        }
    }//foreach

    return $rules;
}

?><?php
function sek_get_module_params_for_sek_level_anchor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_anchor_module',
        'name' => __('Set a custom anchor', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'custom_anchor' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom anchor', 'text_domain_to_be_replaced'),
                    'default'     => '',
                    'notice_after' => __('Note : white spaces, numbers and special characters are not allowed when setting an anchor.')
                ),
            )
        )//tmpl
    );
}

?><?php
function sek_get_module_params_for_sek_level_visibility_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_visibility_module',
        'name' => __('Set visibility on devices', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'desktops' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">desktop_mac</i> %1$s', __('Visible on desktop devices', 'text_domain_to_be_replaced') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => false
                ),
                'tablets' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">tablet_mac</i> %1$s', __('Visible on tablet devices', 'text_domain_to_be_replaced') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => false
                ),
                'mobiles' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">phone_iphone</i> %1$s', __('Visible on mobile devices', 'text_domain_to_be_replaced') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => false,
                    'notice_after' => __('Note that those options are not applied during the live customization of your site, but only when the changes are published.', 'text_domain')
                ),
            )
        )//tmpl
    );
}

?><?php
function sek_get_module_params_for_sek_level_breakpoint_module() {
    $global_custom_breakpoint = sek_get_global_custom_breakpoint();
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_breakpoint_module',
        'name' => __('Set a custom breakpoint', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                  'use-custom-breakpoint' => array(
                      'input_type'  => 'gutencheck',
                      'title'       => __('Use a custom breakpoint for the vertical reorganization of columns', 'text_domain_to_be_replaced'),
                      'default'     => 0,
                      'title_width' => 'width-80',
                      'input_width' => 'width-20',
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true
                  ),
                  'custom-breakpoint'  => array(
                      'input_type'  => 'range_simple',
                      'title'       => __( 'Define a custom breakpoint in pixels', 'text_domain_to_be_replaced' ),
                      'default'     => $global_custom_breakpoint > 0 ? $global_custom_breakpoint : 768,
                      'min'         => 1,
                      'max'         => 2000,
                      'step'        => 1,
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true,
                      'width-100'   => true,
                      'title_width' => 'width-100',
                      'notice_after' => __( 'This is the breakpoint under which columns are reorganized vertically. The default breakpoint is 768px.', 'text_domain_to_be_replaced')
                  ),//0,
                  'reverse-col-at-breakpoint' => array(
                      'input_type'  => 'gutencheck',
                      'title'       => __('Reverse the columns direction on devices smaller than the breakpoint.', 'text_domain_to_be_replaced'),
                      'default'     => 0,
                      'title_width' => 'width-80',
                      'input_width' => 'width-20',
                      'refresh_markup' => true,
                      'refresh_stylesheet' => true
                  ),
            )
        )//tmpl
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for__section__options', '\Nimble\sek_add_css_rules_for_sections_breakpoint', 10, 3 );
function sek_add_css_rules_for_sections_breakpoint( $rules, $section ) {
    $custom_breakpoint = intval( sek_get_section_custom_breakpoint( $section ) );
    if ( $custom_breakpoint > 0 ) {
        $col_number = ( array_key_exists( 'collection', $section ) && is_array( $section['collection'] ) ) ? count( $section['collection'] ) : 1;
        $col_number = 12 < $col_number ? 12 : $col_number;
        $col_width_in_percent = 100/$col_number;
        $col_suffix = floor( $col_width_in_percent );

        $responsive_css_rules = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $col_suffix );
        $rules[] = array(
            'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner > .sek-section-custom-breakpoint-col-'.$col_suffix,
            'css_rules' => $responsive_css_rules,
            'mq' => "(min-width: {$custom_breakpoint}px)"
        );
    }
    if ( isset( $section[ 'options' ] ) && isset( $section[ 'options' ]['breakpoint'] ) && array_key_exists( 'reverse-col-at-breakpoint', $section[ 'options' ]['breakpoint'] ) ) {
        $default_md_breakpoint = '768';
        if ( class_exists('\Nimble\Sek_Dyn_CSS_Builder') ) {
            $default_md_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];
        }
        $breakpoint = $custom_breakpoint > 0 ? $custom_breakpoint : $default_md_breakpoint;
        $responsive_css_rules = "-ms-flex-direction: column-reverse;flex-direction: column-reverse;";
        $rules[] = array(
            'selector' => '[data-sek-id="'.$section['id'].'"] .sek-sektion-inner',
            'css_rules' => $responsive_css_rules,
            'mq' => "(max-width: {$breakpoint}px)"
        );
    }


    return $rules;
}

?><?php
function sek_get_module_params_for_sek_local_template() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_template',
        'name' => __('Template for the current page', 'text_domain_to_be_replaced'),
        'starting_value' => array(),
        'tmpl' => array(
            'item-inputs' => array(
                'local_template' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select a template', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'width-100'   => true,
                    'choices'     => sek_get_select_options_for_input_id( 'local_template' ),
                    'refresh_preview' => true
                )
            )
        )//tmpl
    );
}
?><?php
function sek_get_module_params_for_sek_local_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_widths',
        'name' => __('Width settings of the sections in the current page', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom outer width for the sections of this page', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('This option will be inherited by all Nimble sections of the currently previewed page, unless for sections with a specific width option.')
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom inner width for the sections of this page', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('This option will be inherited by all Nimble sections of the currently previewed page, unless for sections with a specific width option.')
                )
            )
        )//tmpl
    );
}
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_local_widths_css' );
function sek_add_raw_local_widths_css( $css ) {
    $css = is_string( $css ) ? $css : '';
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? $_POST['local_skope_id'] : skp_build_skope_id() );

    if ( ! is_array( $local_options ) || empty( $local_options['local_options']) || empty( $local_options['local_options']['widths'] ) )
      return $css;

    $width_options = $local_options['local_options']['widths'];
    $user_defined_widths = array();

    if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = '.sektion-wrapper [data-sek-level="section"]';
    }
    if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner';
    }

    $rules = array();
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
                $padding_of_the_parent_container[$device] = 'inherit';
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $max_width_value,
            'css_property' => 'max-width',
            'selector' => $selector
        ), $rules );
        if ( 'inner-section-width' === $width_opt_name ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-left',
                'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid'
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid'
            ), $rules );
        }

        if ( ! empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector
            ), $rules );
        }
    }//foreach

    $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    return is_string( $width_options_css ) ? $css . $width_options_css : $css;
}
?><?php
function sek_get_module_params_for_sek_local_custom_css() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_custom_css',
        'name' => __('Custom CSS for the sections of the current page', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'local_custom_css' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Custom css' , 'text_domain_to_be_replaced' ),
                    'default'     => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_domain_to_be_replaced' ) ),
                    'code_type' => 'text/css',// 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
                    'notice_after' => __('This CSS code will be restricted to the currently previewed page only, and not applied site wide.', 'text_domain_to_be_replaced'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                )
            )
        )//tmpl
    );
}
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_local_custom_css' );
function sek_add_raw_local_custom_css( $css ) {
    $local_options = sek_get_skoped_seks( !empty( $_POST['local_skope_id'] ) ? $_POST['local_skope_id'] : skp_build_skope_id() );
    if ( is_array( $local_options ) && !empty( $local_options['local_options']) && ! empty( $local_options['local_options']['custom_css'] ) ) {
        $options = $local_options['local_options']['custom_css'];
        if ( ! empty( $options['local_custom_css'] ) ) {
            $css .= $options['local_custom_css'];
        }
    }
    return $css;
}
?><?php
function sek_get_module_params_for_sek_local_reset() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_reset',
        'name' => __('Reset the sections of the current page', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'reset_local' => array(
                    'input_type'  => 'reset_button',
                    'title'       => __( 'Remove the Nimble sections in the current page' , 'text_domain_to_be_replaced' ),
                    'scope'       => 'local',
                    'notice_after' => __('This will reset the sections created for the currently previewed page only. All other sections in other contexts will be preserved.', 'text_domain_to_be_replaced'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                )
            )
        )//tmpl
    );
}
?><?php
function sek_get_module_params_for_sek_local_performances() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_performances',
        'name' => __('Performance optimizations', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'local-img-smart-load' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select how you want to load the images in the sections of this page.', 'text_domain_to_be_replaced'),
                    'default'     => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the site wide option', 'text_domain' ),
                        'yes' => __('Load images on scroll ( optimized )', 'text_domain' ),
                        'no'  => __('Load all images on page load ( not optimized )', 'text_domain' )
                    ),
                    'notice_after' => __( 'When you select "Load images on scroll", images below the viewport are loaded dynamically on scroll. This can boost performances by reducing the weight of long web pages designed with several images.', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )
        )//tmpl
    );
}
?><?php
function sek_get_module_params_for_sek_global_breakpoint() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_breakpoint',
        'name' => __('Site wide breakpoint options', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-breakpoint' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Use a global custom breakpoint for the vertical reorganization of columns', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __( 'This is the breakpoint under which columns are reorganized vertically. The default global breakpoint is 768px.', 'text_domain_to_be_replaced')
                ),
                'global-custom-breakpoint'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Define a custom breakpoint in pixels', 'text_domain_to_be_replaced' ),
                    'default'     => 768,
                    'min'         => 1,
                    'max'         => 2000,
                    'step'        => 1,
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )//0,
            )
        )//tmpl
    );
}


add_action('wp_head', '\Nimble\sek_write_global_custom_breakpoint', 1000 );
function sek_write_global_custom_breakpoint() {
    $css = '';
    $custom_breakpoint = sek_get_global_custom_breakpoint();
    if ( $custom_breakpoint >= 1 ) {
        $css .= '@media (min-width:' . $custom_breakpoint . 'px) {.sek-global-custom-breakpoint-col-8 {-ms-flex: 0 0 8.333%;flex: 0 0 8.333%;max-width: 8.333%;}.sek-global-custom-breakpoint-col-9 {-ms-flex: 0 0 9.090909%;flex: 0 0 9.090909%;max-width: 9.090909%;}.sek-global-custom-breakpoint-col-10 {-ms-flex: 0 0 10%;flex: 0 0 10%;max-width: 10%;}.sek-global-custom-breakpoint-col-11 {-ms-flex: 0 0 11.111%;flex: 0 0 11.111%;max-width: 11.111%;}.sek-global-custom-breakpoint-col-12 {-ms-flex: 0 0 12.5%;flex: 0 0 12.5%;max-width: 12.5%;}.sek-global-custom-breakpoint-col-14 {-ms-flex: 0 0 14.285%;flex: 0 0 14.285%;max-width: 14.285%;}.sek-global-custom-breakpoint-col-16 {-ms-flex: 0 0 16.666%;flex: 0 0 16.666%;max-width: 16.666%;}.sek-global-custom-breakpoint-col-20 {-ms-flex: 0 0 20%;flex: 0 0 20%;max-width: 20%;}.sek-global-custom-breakpoint-col-25 {-ms-flex: 0 0 25%;flex: 0 0 25%;max-width: 25%;}.sek-global-custom-breakpoint-col-30 {-ms-flex: 0 0 30%;flex: 0 0 30%;max-width: 30%;}.sek-global-custom-breakpoint-col-33 {-ms-flex: 0 0 33.333%;flex: 0 0 33.333%;max-width: 33.333%;}.sek-global-custom-breakpoint-col-40 {-ms-flex: 0 0 40%;flex: 0 0 40%;max-width: 40%;}.sek-global-custom-breakpoint-col-50 {-ms-flex: 0 0 50%;flex: 0 0 50%;max-width: 50%;}.sek-global-custom-breakpoint-col-60 {-ms-flex: 0 0 60%;flex: 0 0 60%;max-width: 60%;}.sek-global-custom-breakpoint-col-66 {-ms-flex: 0 0 66.666%;flex: 0 0 66.666%;max-width: 66.666%;}.sek-global-custom-breakpoint-col-70 {-ms-flex: 0 0 70%;flex: 0 0 70%;max-width: 70%;}.sek-global-custom-breakpoint-col-75 {-ms-flex: 0 0 75%;flex: 0 0 75%;max-width: 75%;}.sek-global-custom-breakpoint-col-80 {-ms-flex: 0 0 80%;flex: 0 0 80%;max-width: 80%;}.sek-global-custom-breakpoint-col-83 {-ms-flex: 0 0 83.333%;flex: 0 0 83.333%;max-width: 83.333%;}.sek-global-custom-breakpoint-col-90 {-ms-flex: 0 0 90%;flex: 0 0 90%;max-width: 90%;}.sek-global-custom-breakpoint-col-100 {-ms-flex: 0 0 100%;flex: 0 0 100%;max-width: 100%;}}';
    }

    printf('<style type="text/css" id="nimble-global-options">%1$s</style>', $css );
}
?><?php
function sek_get_module_params_for_sek_global_widths() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_widths',
        'name' => __('Site wide width options', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-outer-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom outer width for the sections site wide', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Outer sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __('This option will be inherited by all Nimble sections of your site, unless for pages or sections with specific width options.')
                ),
                'use-custom-inner-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define a custom inner width for the sections site wide', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Inner sections width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '100%' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __('This option will be inherited by all Nimble sections of your site, unless for pages or sections with specific width options.')
                )
            )
        )//tmpl
    );
}
add_action('wp_head', '\Nimble\sek_write_global_custom_section_widths', 1000 );
function sek_write_global_custom_section_widths() {
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );

    if ( ! is_array( $global_options ) || empty( $global_options['widths'] ) || !is_array( $global_options['widths'] ) )
      return;

    $width_options = $global_options['widths'];
    $user_defined_widths = array();

    if ( ! empty( $width_options[ 'use-custom-outer-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-outer-width' ] ) ) {
        $user_defined_widths['outer-section-width'] = '[data-sek-level="section"]';
    }
    if ( ! empty( $width_options[ 'use-custom-inner-width' ] ) && true === sek_booleanize_checkbox_val( $width_options[ 'use-custom-inner-width' ] ) ) {
        $user_defined_widths['inner-section-width'] = '[data-sek-level="section"] > .sek-container-fluid > .sek-sektion-inner';
    }

    $rules = array();
    foreach ( $user_defined_widths as $width_opt_name => $selector ) {
        if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
        }
        $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
        $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
            'desktop' => '100%',
            'tablet' => '',
            'mobile' => ''
        ));
        $max_width_value = $user_custom_width_value;
        $margin_value = array();

        foreach ( $user_custom_width_value as $device => $num_unit ) {
            $numeric = sek_extract_numeric_value( $num_unit );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $num_unit );
                $max_width_value[$device] = $numeric . $unit;
                $margin_value[$device] = '0 auto';
                $padding_of_the_parent_container[$device] = 'inherit';
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $max_width_value,
            'css_property' => 'max-width',
            'selector' => $selector
        ), $rules );
        if ( 'inner-section-width' === $width_opt_name ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-left',
                'selector' => '[data-sek-level="section"] > .sek-container-fluid'
            ), $rules );
            $rules = sek_set_mq_css_rules(array(
                'value' => $padding_of_the_parent_container,
                'css_property' => 'padding-right',
                'selector' => '[data-sek-level="section"] > .sek-container-fluid'
            ), $rules );
        }

        if ( ! empty( $margin_value ) ) {
            $rules = sek_set_mq_css_rules(array(
                'value' => $margin_value,
                'css_property' => 'margin',
                'selector' => $selector
            ), $rules );
        }
    }//foreach

    $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    if ( is_string( $width_options_css ) && ! empty( $width_options_css ) ) {
        printf('<style type="text/css" id="nimble-global-options">%1$s</style>', $width_options_css );
    }
}
?><?php
function sek_get_module_params_for_sek_global_performances() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_performances',
        'name' => __('Site wide performance options', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'global-img-smart-load' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Load images on scroll', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __( 'Check this option to delay the loading of non visible images. Images below the viewport will be loaded dynamically on scroll. This can boost performances by reducing the weight of long web pages designed with several images.', 'text_domain_to_be_replaced')
                ),
            )
        )//tmpl
    );
}

?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE HTML MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_html_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_html_module',
        'name' => __( 'Html Content', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_html_module',
        'starting_value' => array(
            'html_content' => sprintf('<pre>%1$s</pre>', __('html code goes here', 'text-domain') )
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'html_content' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'HTML Content' , 'text_domain_to_be_replaced' ),
                    'default'     => sprintf('<pre>%1$s</pre>', __('html code goes here', 'text-domain') )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_html_module_tmpl.php",
        'placeholder_icon' => 'code'
    );
}

function sanitize_callback__czr_simple_html_module( $value ) {
    if ( array_key_exists( 'html_content', $value ) ) {
        if ( !current_user_can( 'unfiltered_html' ) ) {
            $value[ 'html_content' ] = wp_kses_post( $value[ 'html_content' ] );
        }
    }
    return $value;
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_tinymce_child',
            'font_settings' => 'czr_font_child'
        ),
        'name' => __('Text Editor', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'main_settings' => array(
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}



/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_tinymce_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tinymce_child',
        'name' => __('Content', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'content' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Content', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'autop' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Automatically convert text into paragraph', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('WordPress wraps the editor text inside "p" tags by default. You can disable this behaviour by unchecking this option.', 'text-domain')
                ),
            )
        ),
        'render_tmpl_path' =>'',
    );
}


?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMAGE MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_image_module() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_image_main_settings_child',
            'borders_corners' => 'czr_image_borders_corners_child'
        ),
        'name' => __('Image', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'main_settings' => array(
                'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png',
                'custom_width' => ''
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}




/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_image_main_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_main_settings_child',
        'name' => __( 'Image main settings', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'img-size' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' )
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'img-link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_domain_to_be_replaced'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_domain_to_be_replaced'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'use_custom_width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Custom image width', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                    'refresh_stylesheet' => true
                ),
                'custom_width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Width', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 100,
                    'default'     => array( 'desktop' => '100%' ),
                    'max'     => 500,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply a shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                ),
                'img_hover_effect' => array(
                    'input_type'  => 'select',
                    'title'       => __('Mouse over effect', 'text_domain_to_be_replaced'),
                    'default'     => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'img_hover_effect' )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}






/* ------------------------------------------------------------------------- *
 *  IMAGE BORDERS AND BORDER RADIUS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_image_borders_corners_child() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_borders_corners_child',
        'name' => __( 'Borders and corners', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border', 'text_domain_to_be_replaced'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> $css_selectors
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_image_module', '\Nimble\sek_add_css_rules_for_czr_image_module', 10, 2 );
function sek_add_css_rules_for_czr_image_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $main_settings = $complete_modul_model['value']['main_settings'];
    $borders_corners_settings = $complete_modul_model['value']['borders_corners'];
    if ( sek_booleanize_checkbox_val( $main_settings['use_custom_width'] ) ) {
        $width = $main_settings[ 'custom_width' ];
        $css_rules = '';
        if ( isset( $width ) && FALSE !== $width ) {
            $numeric = sek_extract_numeric_value( $width );
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $width );
                $css_rules .= 'width:' . $numeric . $unit . ';';
            }
            if ( is_string( $width ) ) {
                  $numeric = sek_extract_numeric_value($width);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $width );
                      $css_rules .= 'width:' . $numeric . $unit . ';';
                  }
            } else if ( is_array( $width ) ) {
                  $width = wp_parse_args( $width, array(
                      'desktop' => '100%',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $ready_value = $width;
                  foreach ($width as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'width',
                      'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                      'is_important' => false
                  ), $rules );
            }
        }//if


        if ( !empty( $css_rules ) ) {
            $rules[] = array(
                'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                'css_rules' => $css_rules,
                'mq' =>null
            );
        }
    }
    $border_settings = $borders_corners_settings[ 'borders' ];
    $border_type = $borders_corners_settings[ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img'
        );
    }

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER FEATURED PAGES MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_featured_pages_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_featured_pages_module',
        'is_crud' => true,
        'name' => __('Featured Pages', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'pre-item' => array(
                'img-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display an image', 'text_domain_to_be_replaced'),
                    'default'     => 'featured',
                    'choices'     => sek_get_select_options_for_input_id( 'img-type' )
                ),
            ),
            'item-inputs' => array(
                'page-id' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Pick a page', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'img-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display an image', 'text_domain_to_be_replaced'),
                    'default'     => 'featured',
                    'choices'     => sek_get_select_options_for_input_id( 'img-type' )
                ),
                'img-id' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'img-size' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' )
                ),
                'content-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display a text', 'text_domain_to_be_replaced'),
                    'default'     => 'page-excerpt',
                    'choices'     => sek_get_select_options_for_input_id( 'content-type' )
                ),
                'content-custom-text' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Custom text content', 'text_domain_to_be_replaced'),
                    'default'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
                ),
                'btn-display' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display a call to action button', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'btn-custom-text' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Custom button text', 'text_domain_to_be_replaced'),
                    'default'     => __('Read More', 'text_domain_to_be_replaced'),
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/featured_pages_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}

?><?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_heading_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_heading_child',
            'font_settings' => 'czr_font_child',
            'spacing' => 'czr_heading_spacing_child'
        ),
        'name' => __('Heading', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'main_settings' => array(
                'heading_text' => 'This is a heading.',
                'h_alignment_css' => array( 'desktop' => 'center')
            )
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/heading_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}



/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_heading_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_child',
        'name' => __('Content', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'heading_text' => array(
                    'input_type'         => 'text',
                    'title'              => __( 'Heading text', 'text_domain_to_be_replaced' ),
                    'default'            => '',
                    'width-100'         => true,
                    'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),

                ),
                'heading_tag' => array(
                    'input_type'         => 'select',
                    'title'              => __( 'Heading tag', 'text_domain_to_be_replaced' ),
                    'default'            => 'h1',
                    'choices'            => sek_get_select_options_for_input_id( 'heading_tag' )
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
            )
        ),
        'render_tmpl_path' =>'',
    );
}


/* ------------------------------------------------------------------------- *
 *  HEADING SPACING CHILD
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_heading_spacing_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_spacing_child',
        'name' => __('Spacing', 'text_domain_to_be_replaced'),
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Margin and padding', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => array() ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                )
            )
        ),
        'render_tmpl_path' =>'',
    );
}




function sanitize_callback__czr_heading_module( $value ) {
    if (  !current_user_can( 'unfiltered_html' ) && array_key_exists('main_settings', $value ) && is_array( $value['main_settings'] ) && array_key_exists('heading_text', $value['main_settings'] ) ) {
        if ( function_exists( 'czr_heading_module_kses_text' ) ) {
            $value['main_settings'][ 'heading_text' ] = czr_heading_module_kses_text( $value['main_settings'][ 'heading_text' ] );
        }
    }
    return $value;
}
function validate_callback__czr_heading_module( $value ) {
    return true;
}


?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SPACER MODULE
/* ------------------------------------------------------------------------- */

function sek_get_module_params_for_czr_spacer_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_spacer_module',
        'name' => __('Spacer', 'text_domain_to_be_replaced'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        'tmpl' => array(
            'item-inputs' => array(
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'min'         => 0,
                    'max'         => 100,
                    'step'        => 1,
                    'title'       => __('Space', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => '20px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => array( '.sek-spacer' ),
                    'css_identifier' => 'height'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/spacer_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER DIVIDER MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_divider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_divider_module',
        'name' => __('Divider', 'text_domain_to_be_replaced'),
        'css_selectors' => array( '.sek-divider' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border_top_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Weight', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 50,
                    'default' => '1px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_width'
                ),
                'border_top_style_css' => array(
                    'input_type'  => 'select',
                    'title'       => __('Style', 'text_domain_to_be_replaced'),
                    'default' => 'solid',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_style',
                    'choices'    => sek_get_select_options_for_input_id( 'border-type' )
                ),
                'border_top_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_color'
                ),
                'width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Width', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 100,
                    'default' => '100%',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'width'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => '.sek-module-inner',
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'v_spacing_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space before and after', 'text_domain_to_be_replaced'),
                    'min'         => 1,
                    'max'         => 100,
                    'default'     => array( 'desktop' => '15px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'v_spacing'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/divider_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ICON MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_module',
        'is_father' => true,
        'children' => array(
            'icon_settings' => 'czr_icon_settings_child',
            'spacing_border' => 'czr_icon_spacing_border_child'
        ),
        'name' => __('Icon', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'icon_settings' => array(
                'icon' =>  'far fa-star',
                'font_size_css' => '40px',
                'color_css' => '#707070',
                'color_hover' => '#969696'
            )
        ),
        'css_selectors' => array( '.sek-icon i' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/icon_module_tmpl.php",
        'front_assets' => array(
              'czr-font-awesome' => array(
                  'type' => 'css',
                  'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
              )
        )
    );
}





/* ------------------------------------------------------------------------- *
 *  MAIN ICON SETTINGS : ICON, SIZE, COLOR, LINK
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_settings_child',
        'name' => __( 'Icon settings', 'text_domain_to_be_replaced' ),
        'css_selectors' => array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an Icon', 'text_domain_to_be_replaced'),
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_domain_to_be_replaced'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_domain_to_be_replaced'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'font_size_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Size', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'       => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors' => '.sek-icon',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'use_custom_color_on_hover' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Set a custom icon color on mouse hover', 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#969696',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  ICON SPACING BORDER SHADOW
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_spacing_border_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_spacing_border_child',
        'name' => __( 'Icon options for background, spacing, border, shadow', 'text_domain_to_be_replaced' ),
        'css_selectors' => array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Spacing', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => array() ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    'css_selectors'=> '.sek-icon i'
                ),
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border', 'text_domain_to_be_replaced'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> '.sek-icon i'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply a shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}











/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_icon_module', '\Nimble\sek_add_css_rules_for_icon_front_module', 10, 2 );
function sek_add_css_rules_for_icon_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];

    $icon_settings = $value['icon_settings'];
    $icon_color = $icon_settings['color_css'];
    if ( sek_booleanize_checkbox_val( $icon_settings['use_custom_color_on_hover'] ) ) {
        $color_hover = $icon_settings['color_hover'];
    } else {
        if ( 0 === strpos( $icon_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $icon_color );
            $color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $color_hover      = sek_rgb2rgba( $color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $icon_color, 'rgb' ) ) {
            $color_hover      = sek_lighten_rgb( $icon_color, $percent=15 );
        } else {
            $color_hover      = sek_lighten_hex( $icon_color, $percent=15 );
        }
    }
    $rules[] = array(
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon i:hover',
        'css_rules' => 'color:' . $color_hover . ';',
        'mq' =>null
    );
    $border_settings = $value[ 'spacing_border' ][ 'borders' ];
    $border_type = $value[ 'spacing_border' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon i'
        );
    }

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MAP MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_map_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_map_module',
        'name' => __('Map', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'address'       => 'Nice, France',
            'zoom'          => 10,
            'height_css'    => '200px'
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'address' => array(
                    'input_type'  => 'text',
                    'title'       => __( 'Address', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '',
                ),
                'zoom' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Zoom', 'text_domain_to_be_replaced' ),
                    'min' => 1,
                    'max' => 20,
                    'unit' => '',
                    'default' => 10,
                    'width-100'   => true
                ),
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Height', 'text_domain_to_be_replaced' ),
                    'min' => 1,
                    'max' => 600,
                    'default'     => array( 'desktop' => '200px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors' => array( '.sek-embed::before' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'height'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/map_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER QUOTE MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_module',
        'is_father' => true,
        'children' => array(
            'quote_content' => 'czr_quote_quote_child',
            'cite_content' => 'czr_quote_cite_child',
            'design' => 'czr_quote_design_child'
        ),
        'name' => __('Quote', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_callback__czr_quote_module',
        'starting_value' => array(
            'quote_content' => array(
                'quote_text'  => __('Hey, careful, man, there\'s a beverage here!','text_domain_to_be_replaced'),
            ),
            'cite_content' => array(
                'cite_text'   => sprintf( __('The Dude in %1s', 'text_domain_to_be_replaced'), '<a href="https://www.imdb.com/title/tt0118715/quotes/qt0464770" rel="nofollow noopener noreferrer" target="_blank">The Big Lebowski</a>' ),
                'cite_font_style_css' => 'italic',
            ),
            'design' => array(
                'quote_design' => 'border-before'
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/quote_module_tmpl.php",
        'front_assets' => array(
              'czr-font-awesome' => array(
                  'type' => 'css',
                  'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
              )
        )
    );
}








/* ------------------------------------------------------------------------- *
 *  QUOTE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_quote_child() {
    $quote_font_selectors = array( '.sek-quote-content', '.sek-quote-content p', '.sek-quote-content ul', '.sek-quote-content ol', '.sek-quote-content a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_quote_child',
        'name' => __( 'Quote content', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'quote_text' => array(
                    'input_type'         => 'textarea',
                    'title'              => __( 'Quote text', 'text_domain_to_be_replaced' ),
                    'default'            => '',
                    'width-100'         => true,
                    'notice_before'      => __( 'You may use some html tags like a, br,p, div, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),
                ),
                'quote_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $quote_font_selectors,
                ),
                'quote_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $quote_font_selectors,
                ),//16,//"14px",
                'quote_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $quote_font_selectors,
                ),//24,//"20px",
                'quote_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_font_weight_css'     => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'quote_font_style_css'      => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'quote_text_decoration_css' => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'quote_text_transform_css'  => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'quote_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $quote_font_selectors,
                    'width-100'   => true,
                ),//0,
                'quote___flag_important'       => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'important_input_list' => array(
                        'quote_font_family_css',
                        'quote_font_size_css',
                        'quote_line_height_css',
                        'quote_font_weight_css',
                        'quote_font_style_css',
                        'quote_text_decoration_css',
                        'quote_text_transform_css',
                        'quote_letter_spacing_css',
                        'quote_color_css',
                        'quote_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}





/* ------------------------------------------------------------------------- *
 *  CITE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_cite_child() {
    $cite_font_selectors  = array( '.sek-quote-design .sek-cite', '.sek-quote-design .sek-cite a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_cite_child',
        'name' => __( 'Cite content', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'cite_text' => array(
                    'input_type'         => 'textarea',
                    'title'              => __( 'Cite text', 'text_domain_to_be_replaced' ),
                    'default'            => '',
                    'width-100'         => true,
                    'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),
                ),
                'cite_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $cite_font_selectors,
                ),
                'cite_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => '13px' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $cite_font_selectors,
                ),//16,//"14px",
                'cite_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $cite_font_selectors,
                ),//24,//"20px",
                'cite_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_font_weight_css'     => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                    'default'     => 'normal',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'cite_font_style_css'      => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $cite_font_selectors,
                    'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'cite_text_decoration_css' => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'cite_text_transform_css'  => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'cite_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $cite_font_selectors,
                    'width-100'   => true,
                ),//0,
                'cite___flag_important'       => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'important_input_list' => array(
                        'cite_font_family_css',
                        'cite_font_size_css',
                        'cite_line_height_css',
                        'cite_font_weight_css',
                        'cite_font_style_css',
                        'cite_text_decoration_css',
                        'cite_text_transform_css',
                        'cite_letter_spacing_css',
                        'cite_color_css',
                        'cite_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}










/* ------------------------------------------------------------------------- *
 *  DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_design_child() {
    $cite_font_selectors  = array( '.sek-quote-design .sek-cite', '.sek-quote-design .sek-cite a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_design_child',
        'name' => __( 'Design', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'quote_design' => array(
                    'input_type'  => 'select',
                    'title'       => __( 'Design', 'text_domain_to_be_replaced' ),
                    'default'     => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'quote_design' )
                ),
                'border_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Border weight', 'text_domain_to_be_replaced' ),
                    'min' => 1,
                    'max' => 80,
                    'default' => '5px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_width',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'border_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Border Color', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'icon_size_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Icon Size', 'text_domain_to_be_replaced' ),
                    'default'     => '32px',
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => array( '.sek-quote.sek-quote-design.sek-quote-icon-before::before', '.sek-quote.sek-quote-design.sek-quote-icon-before' )
                ),
                'icon_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Icon Color', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'default'     => '#ccc',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-quote-icon-before::before'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}










function sanitize_callback__czr_quote_module( $value ) {
    if ( !current_user_can( 'unfiltered_html' ) ) {
        if ( array_key_exists( 'quote_text', $value ) ) {
            $value[ 'quote_text' ] = wp_kses_post( $value[ 'quote_text' ] );
        }
        if ( array_key_exists( 'cite_text', $value ) ) {
            $value[ 'cite_text' ] = wp_kses_post( $value[ 'cite_text' ] );
        }
    }
    return $value;
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER BUTTON MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_button_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_button_module',
        'is_father' => true,
        'children' => array(
            'content' => 'czr_btn_content_child',
            'design' => 'czr_btn_design_child',
            'font' => 'czr_font_child'
        ),
        'name' => __( 'Button', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            'content' => array(
                'button_text' => __('Click me','text_domain_to_be_replaced'),
            ),
            'design' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => 'center',
                'use_box_shadow' => 1,
                'push_effect' => 1,
            ),
            'font' => array(
                'color_css'  => '#ffffff',
            )
        ),
        'css_selectors' => array( '.sek-btn' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/button_module_tmpl.php"
    );
}



/* ------------------------------------------------------------------------- *
 *  BUTTON CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_content_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_content_child',
        'name' => __( 'Button content', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'button_text' => array(
                    'input_type'         => 'text',
                    'title'              => __( 'Button text', 'text_domain_to_be_replaced' ),
                    'default'            => '',
                    'width-100'         => true,
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_domain_to_be_replaced'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_domain_to_be_replaced'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __( 'Icon next to the button text', 'text_domain_to_be_replaced' ),
                ),
                'icon-side' => array(
                    'input_type'  => 'buttons_choice',
                    'title'       => __("Icon's position", 'text_domain_to_be_replaced'),
                    'default'     => 'left',
                    'choices'     => array( 'left' => __('Left', 'text-domain'), 'right' => __('Right', 'text-domain') )
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_design_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_design_child',
        'name' => __( 'Button design', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'default'    => '#020202',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                ),
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border', 'text_domain_to_be_replaced'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Button alignment', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_domain_to_be_replaced' ),
                    'default'            => array(
                        'padding-top'    => .5,
                        'padding-bottom' => .5,
                        'padding-right'  => 1,
                        'padding-left'   => 1,
                        'unit' => 'em'
                    ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'padding_margin_spacing',
                    'css_selectors'=> '.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply a shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Push visual effect', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}
















function sanitize_callback__czr_button_module( $value ) {
    if ( is_array( $value ) && is_array( $value['content'] ) && array_key_exists( 'button_text', $value['content'] ) ) {
        $value['content'][ 'button_text' ] = sanitize_text_field( $value['content'][ 'button_text' ] );
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_button_module', '\Nimble\sek_add_css_rules_for_button_front_module', 10, 2 );
function sek_add_css_rules_for_button_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;
    $value = $complete_modul_model['value'];
    $design_settings = $value['design'];
    $bg_color = $design_settings['bg_color_css'];
    if ( sek_booleanize_checkbox_val( $design_settings['use_custom_bg_color_on_hover'] ) ) {
        $bg_color_hover = $design_settings['bg_color_hover'];
    } else {
        if ( 0 === strpos( $bg_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
            $bg_color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $bg_color_hover      = sek_rgb2rgba( $bg_color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
            $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=15 );
        } else {
            $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=15 );
        }
    }
    $rules[] = array(
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-btn:hover',
        'css_rules' => 'background-color:' . $bg_color_hover . ';',
        'mq' =>null
    );
    $border_settings = $design_settings[ 'borders' ];
    $border_type = $design_settings[ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-btn'
        );
    }
    return $rules;
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE FORM MODULES
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_module',
        'is_father' => true,
        'children' => array(
            'form_fields'   => 'czr_simple_form_fields_child',
            'fields_design' => 'czr_simple_form_design_child',
            'form_button'   => 'czr_simple_form_button_child',
            'form_fonts'    => 'czr_simple_form_fonts_child',
            'form_submission'    => 'czr_simple_form_submission_child'
        ),
        'name' => __( 'Simple Form', 'text_domain_to_be_replaced' ),
        'starting_value' => array(
            'fields_design' => array(
                'border' => array(
                    '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                )
            ),
            'form_button' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => is_rtl() ? 'right' : 'left',
                'use_box_shadow' => 1,
                'push_effect' => 1
            ),
            'form_fonts' => array(
                'fl_font_family_css' => '[cfont]Lucida Console,Monaco,monospace',
                'btn_color_css' => '#ffffff'
            ),
            'form_submission' => array(
                'email_footer' => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_domain_to_be_replaced' ),
                    get_bloginfo( 'name' ),
                    get_site_url( 'url' )
                )
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_form_module_tmpl.php",
    );
}











/* ------------------------------------------------------------------------- *
 *  FIELDS VISIBILITY AND REQUIRED
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fields_child() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fields_child',
        'name' => __( 'Form fields and button labels', 'text_domain_to_be_replaced' ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_name_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display name field', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'name_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Name field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Name', 'translate')
                ),
                'name_field_required' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Name field is required', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'show_subject_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display subject field', 'text_domain_to_be_replaced'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'subject_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Subject field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Subject', 'translate')
                ),
                'subject_field_required' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Subject field is required', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'show_message_field' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Display message field', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'message_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Message field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Message', 'translate')
                ),
                'message_field_required' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Message field is required', 'text_domain_to_be_replaced'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),

                'email_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email field label', 'text_domain_to_be_replaced'),
                    'default'     => __('Email', 'translate')
                ),

                'button_text' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Button text', 'text_domain_to_be_replaced'),
                    'default'     => __('Submit', 'translate')
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_design_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_design_child',
        'name' => __( 'Form fields design', 'text_domain_to_be_replaced' ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Fields background color', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Fields border shape', 'text_domain_to_be_replaced'),
                    'default' => 'solid',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders options', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> $css_selectors
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Fields rounded corners', 'text_domain_to_be_replaced' ),
                    'default'     => '3px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'use_inset_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply an inset shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                ),
                'use_outset_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply an outset shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SUBMIT BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_button_child() {
    $css_selectors = 'form input[type="submit"]';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_button_child',
        'name' => __( 'Form button design', 'text_domain_to_be_replaced' ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                      'input_type'  => 'wp_color_alpha',
                      'title'       => __( 'Background color', 'text_domain_to_be_replaced' ),
                      'width-100'   => true,
                      'default'    => '',
                      'refresh_markup' => false,
                      'refresh_stylesheet' => true,
                      'css_identifier' => 'background_color',
                      'css_selectors'=> $css_selectors
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_domain_to_be_replaced' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border', 'text_domain_to_be_replaced'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default'     => '2px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __( 'Button alignment', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors'=> '.sek-form-btn-wrapper',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_domain_to_be_replaced' ),
                    'default'            => array(
                        'margin-top'     => .5,
                        'padding-top'    => .5,
                        'padding-bottom' => .5,
                        'padding-right'  => 1,
                        'padding-left'   => 1,
                        'unit' => 'em'
                    ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'padding_margin_spacing',
                    'css_selectors'=> $css_selectors,//'.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Apply a shadow', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __( 'Push visual effect', 'text_domain_to_be_replaced' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}












/* ------------------------------------------------------------------------- *
 *  FONTS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fonts_child() {
    $fl_font_selectors = array( 'form label', '.sek-form-message' ); //<= .sek-form-message is the wrapper of the form status message : Thanks, etc...
    $ft_font_selectors = array( 'form input[type="text"]', 'form input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    $btn_font_selectors = array( 'form input[type="submit"]' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fonts_child',
        'name' => __( 'Form texts options : fonts, colors, ...', 'text_domain_to_be_replaced' ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Fields labels', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'fl_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $fl_font_selectors,
                            ),
                            'fl_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $fl_font_selectors,
                            ),//16,//"14px",
                            'fl_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $fl_font_selectors,
                            ),//24,//"20px",
                            'fl_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'fl_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'fl_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'fl_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'fl_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $fl_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            'fl___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'important_input_list' => array(
                                    'fl_font_family_css',
                                    'fl_font_size_css',
                                    'fl_line_height_css',
                                    'fl_font_weight_css',
                                    'fl_font_style_css',
                                    'fl_text_decoration_css',
                                    'fl_text_transform_css',
                                    'fl_letter_spacing_css',
                                    'fl_color_css',
                                    'fl_color_hover_css'
                                )
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Field Text', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'ft_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $ft_font_selectors,
                            ),
                            'ft_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $ft_font_selectors,
                            ),//16,//"14px",
                            'ft_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $ft_font_selectors,
                            ),//24,//"20px",
                            'ft_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'ft_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $ft_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'ft_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'ft_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'ft_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $ft_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            'ft___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'important_input_list' => array(
                                    'ft_font_family_css',
                                    'ft_font_size_css',
                                    'ft_line_height_css',
                                    'ft_font_weight_css',
                                    'ft_font_style_css',
                                    'ft_text_decoration_css',
                                    'ft_text_transform_css',
                                    'ft_letter_spacing_css',
                                    'ft_color_css',
                                    'ft_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Button', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'btn_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $btn_font_selectors,
                            ),
                            'btn_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $btn_font_selectors,
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $btn_font_selectors,
                            ),//24,//"20px",
                            'btn_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'btn_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $btn_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'btn_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'btn_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'btn_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $btn_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            'btn___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'important_input_list' => array(
                                    'btn_font_family_css',
                                    'btn_font_size_css',
                                    'btn_line_height_css',
                                    'btn_font_weight_css',
                                    'btn_font_style_css',
                                    'btn_text_decoration_css',
                                    'btn_text_transform_css',
                                    'btn_letter_spacing_css',
                                    'btn_color_css',
                                    'btn_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}



/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_submission_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_submission_child',
        'name' => __( 'Form submission options', 'text_domain_to_be_replaced' ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'recipients' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email recipient', 'text_domain_to_be_replaced'),
                    'default'     => get_option( 'admin_email' ),
                    'refresh_preview'  => false
                ),
                'success_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Success message on submission' , 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Thanks! Your message has been sent.', 'text_domain_to_be_replaced'),
                    'refresh_preview'  => false
                ),
                'error_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Error message on submission' , 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Invalid form submission : some fields have not been entered properly.', 'text_domain_to_be_replaced'),
                    'refresh_preview'  => false
                ),
                'failure_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Failure message on submission' , 'text_domain_to_be_replaced' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Your message was not sent. Try Again.', 'text_domain_to_be_replaced'),
                    'refresh_preview'  => false
                ),
                'email_footer' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Email footer' , 'text_domain_to_be_replaced' ),
                    'notice_before' => __('Html code is allowed', 'text-domain'),
                    'default'     => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_domain_to_be_replaced' ),
                        get_bloginfo( 'name' ),
                        get_site_url( 'url' )
                    ),
                    'refresh_preview'  => false
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING FOR THE FORM MODULE
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_simple_form_module', '\Nimble\sek_add_css_rules_for_czr_simple_form_module', 10, 2 );
function sek_add_css_rules_for_czr_simple_form_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    if ( ! empty( $value['form_button'] ) && is_array( $value['form_button'] ) ) {
        $form_button_options = $value['form_button'];
        $bg_color = $form_button_options['bg_color_css'];
        if ( sek_booleanize_checkbox_val( $form_button_options['use_custom_bg_color_on_hover'] ) ) {
            $bg_color_hover = $form_button_options['bg_color_hover'];
        } else {
            if ( 0 === strpos( $bg_color, 'rgba' ) ) {
                list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
                $bg_color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
                $bg_color_hover      = sek_rgb2rgba( $bg_color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
            } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
                $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=15 );
            } else {
                $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=15 );
            }
        }

        $rules[] = array(
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] input[type="submit"]:hover',
            'css_rules' => 'background-color:' . $bg_color_hover . ';',
            'mq' =>null
        );
        $border_settings = $form_button_options[ 'borders' ];
        $border_type = $form_button_options[ 'border-type' ];
        $has_border_settings  = 'none' != $border_type && !empty( $border_type );
        if ( $has_border_settings ) {
            $rules = sek_generate_css_rules_for_multidimensional_border_options(
                $rules,
                $border_settings,
                $border_type,
                '[data-sek-id="'.$complete_modul_model['id'].'"] input[type="submit"]'
            );
        }
    }
    $border_settings = $value[ 'fields_design' ][ 'borders' ];
    $border_type = $value[ 'fields_design' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );
    if ( $has_border_settings ) {
        $selector_list = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
        $css_selectors = array();
        foreach( $selector_list as $selector ) {
            $css_selectors[] = '[data-sek-id="'.$complete_modul_model['id'].'"]' . ' ' . $selector;
        }
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            implode( ', ', $css_selectors )
        );
    }
    return $rules;
}



?>
<?php
/* ------------------------------------------------------------------------- *
 *  GENERIC FONT CHILD MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_font_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_font_child',
        'name' => __( 'Text settings : font, color, size, ...', 'text_domain_to_be_replaced' ),
        'tmpl' => array(
            'item-inputs' => array(
                'font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_domain_to_be_replaced'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family'
                ),
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height'
                ),//24,//"20px",
                'color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_domain_to_be_replaced'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color'
                ),//"#000000",
                'color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color on mouse over', 'text_domain_to_be_replaced'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover'
                ),//"#000000",
                'font_weight_css'     => array(
                    'input_type'  => 'select',
                    'title'       => __('Font weight', 'text_domain_to_be_replaced'),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'font_style_css'      => array(
                    'input_type'  => 'select',
                    'title'       => __('Font style', 'text_domain_to_be_replaced'),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'text_decoration_css' => array(
                    'input_type'  => 'select',
                    'title'       => __('Text decoration', 'text_domain_to_be_replaced'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'text_transform_css'  => array(
                    'input_type'  => 'select',
                    'title'       => __('Text transform', 'text_domain_to_be_replaced'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,

                'letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                ),//0,
                'fonts___flag_important'  => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Apply the style options in priority (uses !important).', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'important_input_list' => array(
                        'font_family_css',
                        'font_size_css',
                        'line_height_css',
                        'font_weight_css',
                        'font_style_css',
                        'text_decoration_css',
                        'text_transform_css',
                        'letter_spacing_css',
                        'color_css',
                        'color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}
?><?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 *  Sek Dyn CSS Builder: class responsible for building Stylesheet from a sek model
 */
class Sek_Dyn_CSS_Builder {

    /*min widths, considering CSS min widths BP:
    $grid-breakpoints: (
        xs: 0,
        sm: 576px,
        md: 768px,
        lg: 992px,
        xl: 1200px
    )

    we could have a constant array since php 5.6
    */
    public static $breakpoints = [
        'xs' => 0,
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
        'xl' => 1200
    ];

    const COLS_MOBILE_BREAKPOINT  = 'md';

    private $collection;//the collection of css rules
    private $sek_model;
    private $parent_level_model = array();

    public function __construct( $sek_model = array() ) {
        $this->sek_model  = $sek_model;
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

        $this->sek_css_rules_sniffer_walker();
    }
    public function sek_css_rules_sniffer_walker( $level = null, $parent_level = array() ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();
        if ( ! empty( $parent_level ) ) {
            $this -> parent_level_model = $parent_level;
        }

        foreach ( $level as $key => $entry ) {
            $rules = array();
            if ( is_string( $key ) && 1 < strlen( $key ) ) {
                if ( !empty( $parent_level ) && is_array( $parent_level ) && !empty( $parent_level['module_type'] ) ) {
                    $module_level_css_selectors = sek_get_registered_module_type_property( $parent_level['module_type'], 'css_selectors' );

                    $registered_input_list = sek_get_registered_module_input_list( $parent_level['module_type'] );
                    if ( 'value' === $key && is_array( $entry ) ) {
                          $is_father = sek_get_registered_module_type_property( $parent_level['module_type'], 'is_father' );
                          $father_mod_type = $parent_level['module_type'];
                          if ( $is_father ) {
                              $children = sek_get_registered_module_type_property( $father_mod_type, 'children' );
                              foreach ( $entry as $opt_group_type => $input_candidates ) {
                                  if ( ! is_array( $children ) ) {
                                      sek_error_log( 'Father module ' . $father_mod_type . ' has invalid children');
                                      continue;
                                  }
                                  if ( empty( $children[$opt_group_type] ) ) {
                                      sek_error_log( 'Father module ' . $father_mod_type . ' has a invalid child for option group : '. $opt_group_type);
                                      continue;
                                  }
                                  $child_mod_type = $children[ $opt_group_type ];
                                  $child_css_selector = sek_get_registered_module_type_property( $child_mod_type, 'css_selectors' );
                                  $child_css_selector = empty( $child_css_selector ) ? $module_level_css_selectors : $child_css_selector;

                                  foreach ( $input_candidates as $input_id_candidate => $_input_val ) {
                                      if ( false !== strpos( $input_id_candidate, '_css') ) {
                                          if ( is_array( $registered_input_list ) && is_array( $registered_input_list[ $opt_group_type ] )&& ! empty( $registered_input_list[ $opt_group_type ][ $input_id_candidate ] ) && ! empty( $registered_input_list[ $opt_group_type ][ $input_id_candidate ]['css_identifier'] ) ) {
                                              $rules = apply_filters(
                                                  "sek_add_css_rules_for_input_id",
                                                  $rules,// <= the in-progress array of css rules to be populated
                                                  $_input_val,// <= the css property value
                                                  $input_id_candidate, // <= the unique input_id as it as been declared on module registration
                                                  $registered_input_list[ $opt_group_type ],// <= the full list of input for the module
                                                  $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                                  $child_css_selector
                                              );
                                          } else {
                                              sek_error_log( __FUNCTION__ . ' => missing the css_identifier param when registering father module ' . $father_mod_type . ' for a css input candidate : ' . $input_id_candidate . ' in option group : ' . $opt_group_type, $parent_level );
                                              sek_error_log('$registered_input_list', $registered_input_list );
                                          }
                                      }
                                  }//foreach
                              }//foreach
                          } else {
                              foreach ( $entry as $input_id_candidate => $_input_val ) {
                                  if ( false !== strpos( $input_id_candidate, '_css') ) {
                                      if ( is_array( $registered_input_list ) && ! empty( $registered_input_list[ $input_id_candidate ] ) && ! empty( $registered_input_list[ $input_id_candidate ]['css_identifier'] ) ) {
                                          $rules = apply_filters(
                                              "sek_add_css_rules_for_input_id",
                                              $rules,// <= the in-progress array of css rules to be populated
                                              $_input_val,// <= the css property value
                                              $input_id_candidate, // <= the unique input_id as it as been declared on module registration
                                              $registered_input_list,// <= the full list of input for the module
                                              $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                              $module_level_css_selectors // <= if the parent is a module, a default set of css_selectors might have been specified on module registration
                                          );
                                      } else {
                                          sek_error_log( __FUNCTION__ . ' => missing the css_identifier param when registering module ' . $parent_level['module_type'] . ' for a css input candidate : ' . $input_id_candidate, $parent_level );
                                          sek_error_log('$registered_input_list', $registered_input_list );
                                      }
                                  }
                              }//foreach
                          }//if is_father
                    }//if
                }//if
            }//if
            if ( is_array( $entry ) ) {
                if ( !empty( $entry[ 'level' ] ) && 'location' != $entry[ 'level' ] ) {
                    $level_type = $entry[ 'level' ];
                    $rules = apply_filters( "sek_add_css_rules_for__{$level_type}__options", $rules, $entry );
                    $rules = apply_filters( 'sek_add_css_rules_for_level_options', $rules, $entry );
                }
                if ( !empty( $entry[ 'level' ] ) && 'module' === $entry['level'] ) {
                    if ( ! empty( $entry['module_type'] ) ) {
                        $module_type = $entry['module_type'];
                        $rules = apply_filters( "sek_add_css_rules_for_module_type___{$module_type}", $rules, sek_normalize_module_value_with_defaults( $entry ) );
                    }
                }
            } // if ( is_array( $entry ) ) {
            if ( !empty( $rules ) ) {
                foreach( $rules as $rule ) {
                    if ( ! is_array( $rule ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                        continue;
                    }
                    if ( empty( $rule['selector']) ) {
                        sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
                        continue;
                    }
                    $this->sek_populate(
                        $rule[ 'selector' ],
                        $rule[ 'css_rules' ],
                        $rule[ 'mq' ]
                    );
                }//foreach
            }
            if ( is_array( $entry ) ) {
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $parent_level = $entry;
                }
                $this->sek_css_rules_sniffer_walker( $entry, $parent_level );
            }
            if ( ! empty( $parent_level ) ) {
                $this -> parent_level_model = $parent_level;
            }

        }//foreach
    }//sek_css_rules_sniffer_walker()
    public function sek_populate( $selector, $css_rules, $mq = '' ) {
        if ( ! is_string( $selector ) )
            return;
        if ( ! is_string( $css_rules ) )
            return;
        $mq_device = 'all_devices';
        if ( !empty( $mq ) ) {
            if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
            } else {
                $mq_device = $mq;
            }
        }
        if ( !isset( $this->collection[ $mq_device ] ) ) {
            $this->collection[ $mq_device ] = array();
        }

        if ( !isset( $this->collection[ $mq_device ][ $selector ] ) ) {
            $this->collection[ $mq_device ][ $selector ] = array();
        }

        $this->collection[ $mq_device ][ $selector ][] = $css_rules;
    }//sek_populate
    public static function sek_maybe_wrap_in_media_query( $css,  $mq_device = 'all_devices' ) {
        if ( 'all_devices' === $mq_device ) {
            return $css;
        }
        if ( false === strpos($mq_device, '(') || false === strpos($mq_device, ')') ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing parenthesis in the media queries', $mq_device );
            return $css;
        }
        return sprintf( '@media%1$s{%2$s}', $mq_device, $css);
    }
    public static function user_defined_array_key_sort_fn($a, $b) {
        if ( 'all_devices' === $a ) {
            return -1;
        }
        if ( 'all_devices' === $b ) {
            return 1;
        }
        $a_int = (int)preg_replace('/[^0-9]/', '', $a) * 1;
        $b_int = (int)preg_replace('/[^0-9]/', '', $b) * 1;

        return $b_int - $a_int;
    }
    public function get_stylesheet() {
        $css = '';
        $collection = apply_filters( 'nimble_css_rules_collection_before_printing_stylesheet', $this->collection );
        if ( is_array( $collection ) && !empty( $collection ) ) {
            uksort( $collection, array( get_called_class(), 'user_defined_array_key_sort_fn' ) );
            foreach ( $collection as $mq_device => $selectors ) {
                $_css = '';
                foreach ( $selectors as $selector => $css_rules ) {
                    $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                    $_css .=  $selector . '{' . $css_rules . '}';
                    $_css =  str_replace(';;', ';', $_css);//@fixes https://github.com/presscustomizr/nimble-builder/issues/137
                }
                $_css = self::sek_maybe_wrap_in_media_query( $_css, $mq_device );
                $css .= $_css;
            }
        }
        return apply_filters( 'nimble_get_dynamic_stylesheet', $css );
    }
    public static function sek_generate_css_stylesheet_for_a_set_of_rules( $rules ) {
        $rules_collection = array();
        $css = '';

        if ( empty( $rules ) || ! is_array( $rules ) )
          return $css;
        foreach( $rules as $rule ) {
            if ( ! is_array( $rule ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                continue;
            }
            if ( empty($rule['selector']) || ! is_string( $rule['selector'] ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
                continue;
            }

            $selector = $rule[ 'selector' ];
            $css_rules = $rule[ 'css_rules' ];
            $mq = $rule[ 'mq' ];

            if ( ! is_string( $css_rules ) )
              continue;
            $mq_device = 'all_devices';
            if ( !empty( $mq ) ) {
                if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                    error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
                } else {
                    $mq_device = $mq;
                }
            }
            if ( !isset( $rules_collection[ $mq_device ] ) ) {
                $rules_collection[ $mq_device ] = array();
            }

            if ( !isset( $rules_collection[ $mq_device ][ $selector ] ) ) {
                $rules_collection[ $mq_device ][ $selector ] = array();
            }

            $rules_collection[ $mq_device ][ $selector ][] = $css_rules;
        }//foreach
        if ( is_array( $rules_collection ) && !empty( $rules_collection ) ) {
            uksort( $rules_collection, array( get_called_class(), 'user_defined_array_key_sort_fn' ) );
            foreach ( $rules_collection as $mq_device => $selectors ) {
                $_css = '';
                foreach ( $selectors as $selector => $css_rules ) {
                    $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                    $_css .=  $selector . '{' . $css_rules . '}';
                    $_css =  str_replace(';;', ';', $_css);//@fixes https://github.com/presscustomizr/nimble-builder/issues/137
                }
                $_css = self::sek_maybe_wrap_in_media_query( $_css, $mq_device );
                $css .= $_css;
            }
        }

        return $css;
    }//sek_generate_css_stylesheet_for_a_set_of_rules()
    public function sek_add_rules_for_column_width( $rules, $column ) {
        if ( ! is_array( $column ) )
          return $rules;

        if ( empty( $column['level'] ) || 'column' !== $column['level'] )
          return $rules;

        $width   = empty( $column[ 'width' ] ) || !is_numeric( $column[ 'width' ] ) ? '' : $column['width'];
        if ( empty( $width ) )
          return $rules;
        $breakpoint = self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ];
        $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
        $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;
        $parent_section = sek_get_parent_level_model( $column['id'] );
        if ( 'no_match' === $parent_section ) {
            sek_error_log( __FUNCTION__ . ' => $parent_section not found for column id : ' . $column['id'] );
            return $rules;
        }
        $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( $parent_section ) );
        $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

        if ( $has_section_custom_breakpoint ) {
            $breakpoint = $section_custom_breakpoint;
        } else if ( $has_global_custom_breakpoint ) {
            $breakpoint = $global_custom_breakpoint;
        }
        $rules[] = array(
            'selector'      => sprintf( '[data-sek-id="%1$s"] .sek-sektion-inner > .sek-column[data-sek-id="%2$s"]', $parent_section['id'], $column['id'] ),
            'css_rules'     => sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width ),
            'mq'            => "(min-width:{$breakpoint}px)"
        );
        return $rules;
    }
}//end class

?><?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 *  Sek Dyn CSS Handler: class responsible for enqueuing/writing CSS file or enqueuing/printing inline CSS
 */
class Sek_Dyn_CSS_Handler {

    /**
     * CSS files base dir constant
     * Relative dir in the WordPress uploads dir
     *
     * @access public
     */
    const CSS_BASE_DIR = 'sek_css';

    /**
     * Functioning mode constant
     *
     * @access public
     */
    const MODE_INLINE  = 'inline';

    /**
     * Functioning mode constant
     *
     * @access public
     */
    const MODE_FILE    = 'file';

    /**
     * CSS resource ID
     *
     * Holds the CSS resource ID
     * Will be used to generate both the file name and the CSS handle when enqueued_or_printed
     * Usually set to skope_id
     *
     * @access private
     * @var string
     */
    private $id;



    /**
     * Requested skope_id
     *
     * Will be used as id
     * Must be provided
     *
     * @access private
     * @var string
     */
    private $skope_id;


    /**
     * the CSS
     *
     * Holds the CSS string: whether to inline print or to write in the proper file
     *
     * @access private
     * @var string
     */
    private $css_string_to_enqueue_or_print = '';


    /**
     * CSS enqueuing / inline printing status
     *
     * Hold the enqueuing status
     *
     * @access private
     * @var bool
     */
    private $enqueued_or_printed = false;



    /**
     * Enqueuing hook
     *
     * Holds the wp action hook name at whose occurrence the CSS will be enqueued_or_printed
     *
     * @access private
     * @var string
     */
    private $hook;


    /**
     * Enqueuing hook priority
     *
     * Holds the wp action hook priority at whose occurrence the CSS will be enqueued
     * (see the $hook param)
     *
     * @var int
     */
    private $priority = 10;



    /**
     * Enqueuing dependencies
     *
     * Holds the style dependencies for this CSS
     *
     * @access private
     * @var array
     */
    private $dep = array();



    /**
     * Functioning mode
     *
     * Holds the object functioning mode: MODE_FILE or MODE_INLINE
     *
     * @access private
     * @var string
     */
    private $mode;

    /**
     * File writing flag
     *
     * Indicates if we need to only write, not print or enqueuing
     * This is used when saving the customizer options + writing the css file.
     *
     * @access private
     * @var bool
     */
    private $customizer_save = false;


    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing if the file doesn't exist
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_write = false;



    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing even if the file exists
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_rewrite = false;


    /**
     * File status
     *
     * Holds the file existence status (true|false)
     *
     * @access private
     * @var bool
     */
    private $file_exists = false;



    /**
     * CSS file base PATH
     *
     * Holds the CSS relative base path
     * This is simply CSS_BASE_DIR in single sites, while its structure takes in account network and site id in multisites
     *
     * @access private
     * @var string
     */
    private $relative_base_path;



    /**
     * CSS file base URI
     *
     * Holds the CSS folder URI
     *
     * @access private
     * @var string
     */
    private $base_uri;


    /**
     * CSS file base URL
     *
     * Holds the CSS folder URL
     *
     * @access private
     * @var string
     */
    private $base_url;



    /**
     * CSS file URL
     *
     * Holds the CSS file URL
     *
     * @access private
     * @var string
     */
    private $url;




    /**
     * CSS file URI
     *
     * Holds the CSS file URI
     *
     * @access private
     * @var string
     */
    private $uri;

    private $builder;//will hold the Sek_Dyn_CSS_Builder instance

    private $sek_model = 'no_set';


    /**
     * Sek Dyn CSS Handler constructor.
     *
     * Initializing the object.
     *
     * @access public
     * @param array $args Optional.
     *
     */
    public function __construct( $args = array() ) {

        $defaults = array(
            'id'                              => 'sek-'.rand(),
            'skope_id'                        => '',
            'mode'                            => self::MODE_FILE,
            'css_string_to_enqueue_or_print'  => $this->css_string_to_enqueue_or_print,
            'dep'                             => $this->dep,
            'hook'                            => '',
            'priority'                        => $this->priority,
            'customizer_save'                 => false,//<= used when saving the customizer settins => we want to write the css file on Nimble_Customizer_Setting::update()
            'force_write'                     => $this->force_write,
            'force_rewrite'                   => $this->force_rewrite
        );


        $args = wp_parse_args( $args, $defaults );
        $args[ 'dep' ]          = is_array( $args[ 'dep' ] ) ? $args[ 'dep' ]  : array();
        $args[ 'priority']      = is_numeric( $args[ 'priority' ] ) ? $args[ 'priority' ] : $this->priority;
        foreach ( $args as $key => $value ) {
            if ( property_exists( $this, $key ) && array_key_exists( $key, $defaults) ) {
                    $this->$key = $value;
            }
        }

        if ( empty( $this -> skope_id ) ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ .' => __construct => skope_id not provided' );
            return;
        }
        $this->_sek_dyn_css_set_properties();
        if ( is_customize_preview() || ! $this->_sek_dyn_css_file_exists() || $this->force_rewrite || $this->customizer_save ) {
            $this->sek_model = sek_get_skoped_seks( $this -> skope_id );
            $this->builder = new Sek_Dyn_CSS_Builder( $this->sek_model );
            $this->css_string_to_enqueue_or_print = (string)$this->builder-> get_stylesheet();
        }
        if ( ! $this->customizer_save ) {
            $this->_schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook();
        } else {
            if ( $this->css_string_to_enqueue_or_print ) {
                $this->sek_dyn_css_maybe_write_css_file();
            } else {
                $this->sek_dyn_css_maybe_delete_file();
            }
        }
    }//__construct





    /*
    * Private methods
    */

    /**
     *
     * Build these instance properties based on the params passed on instantiation
     * called in the constructor
     *
     * @access private
     *
     */
    private function _sek_dyn_css_set_properties() {
        $this->_sek_dyn_css_require_wp_filesystem();

        $this->relative_base_path   = $this->_sek_dyn_css_build_relative_base_path();

        $this->base_uri             = $this->_sek_dyn_css_build_base_uri();
        $this->base_url             = $this->_sek_dyn_css_build_base_url();

        $this->uri                  = $this->_sek_dyn_css_build_uri();
        $this->url                  = $this->_ssl_maybe_fix_url( $this->_sek_dyn_css_build_url() );

        $this->file_exists          = $this->_sek_dyn_css_file_exists();

        if ( self::MODE_FILE == $this->mode ) {
            if ( ! $this->_sek_dyn_css_write_file_is_possible() ) {
                $this->mode = self::MODE_INLINE;
            }
        }
    }


    /**
    * replace http: URL with https: URL
    * @fix https://github.com/presscustomizr/nimble-builder/issues/188
    * @param string $url
    * @return string
    */
    private function _ssl_maybe_fix_url($url) {
      if ( is_ssl() && is_string($url) && stripos($url, 'http://') === 0 ) {
        $url = 'https' . substr($url, 4);
      }

      return $url;
    }


    /**
     *
     * Maybe setup hooks
     * called in the constructor
     *
     * @access private
     *
     */
    private function _schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook() {
        if ( $this->hook ) {
            add_action( $this->hook, array( $this, 'sek_dyn_css_enqueue_or_print_and_google_fonts_print' ), $this->priority );
        } else {
            $this->sek_dyn_css_enqueue_or_print_and_google_fonts_print();
        }
    }




    /**
     * Enqueue CSS.
     *
     * Either enqueue the CSS file or add inline style, depending on the object mode property.
     * The inline enqueuing is also the fall-back if anything goes wrong while trying to enqueuing the file.
     *
     * This method can also write the file under some circumstances (see when the object force_write || force_rewrite are enabled)
     *
     * @access public
     * @return void()
     */
    public function sek_dyn_css_enqueue_or_print_and_google_fonts_print() {
        if ( self::MODE_FILE == $this->mode ) {
            if ( $this->css_string_to_enqueue_or_print ) {
                if ( $this->force_rewrite || ( !$this->file_exists && $this->force_write ) ) {
                    $this->file_exists = $this->sek_dyn_css_maybe_write_css_file();
                }
            }
            if ( $this->file_exists ) {
                if ( in_array( current_filter(), array( 'wp_footer', 'wp_head' ) ) ) {
                    /*
                    * TODO: make sure all the deps are enqueued
                    */
                    printf( '<link rel="stylesheet" id="sek-dyn-%1$s-css" href="%2$s" type="text/css" media="all" />',
                        $this->id,
                        add_query_arg( array( 'ver' => filemtime($this->uri) ), $this->url )
                    );
                } else {
                    wp_enqueue_style( "sek-dyn-{$this->id}", $this->url, $this->dep, filemtime($this->uri) );
                }

                $this->enqueued_or_printed = true;
            }

        }// if ( self::MODE_FILE )
        if ( $this->css_string_to_enqueue_or_print && ! $this->enqueued_or_printed ) {
            $dep =  array_pop( $this->dep );

            if ( !$dep || wp_style_is( $dep, 'done' ) || !wp_style_is( $dep, 'done' ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                printf( '<style id="sek-%1$s" type="text/css" media="all">%2$s</style>', $this->id, $this->css_string_to_enqueue_or_print );
            } else {
                wp_add_inline_style( $dep , $this->css_string_to_enqueue_or_print );
            }

            $this->mode     = self::MODE_INLINE;
            $this->enqueued_or_printed = true;
        }
        $print_candidates = $this->sek_get_gfont_print_candidates();
        if ( !empty( $print_candidates ) ) {
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                $this -> sek_gfont_print( $print_candidates );
            } else {
                if ( in_array( current_filter(), array( 'wp_footer', 'wp_head' ) ) ) {
                    $this -> sek_gfont_print( $print_candidates );
                } else {
                    wp_enqueue_style(
                        'sek-gfonts-'.$this->id,
                        sprintf( '//fonts.googleapis.com/css?family=%s', $print_candidates ),
                        array(),
                        null,
                        'all'
                    );
                }
            }
        }
    }
    function sek_gfont_print( $print_candidates ) {
       if ( ! empty( $print_candidates ) ) {
            printf('<link rel="stylesheet" id="sek-gfonts-%1$s" href="%2$s">',
                $this->id,
                "//fonts.googleapis.com/css?family={$print_candidates}"
            );
        }
    }
    private function sek_get_gfont_print_candidates() {
        $sektions = 'no_set' === $this->sek_model ? sek_get_skoped_seks( $this -> skope_id ) : $this->sek_model;
        $print_candidates = '';

        if ( !empty( $sektions['fonts'] ) && is_array( $sektions['fonts'] ) ) {
            $ffamilies = implode( "|", $sektions['fonts'] );
            $print_candidates = str_replace( '|', '%7C', $ffamilies );
            $print_candidates = str_replace( '[gfont]', '' , $print_candidates );
        }
        return $print_candidates;
    }


    /*
    * Public 'actions'
    */
    /**
     *
     * Write the CSS to the disk, if we can
     *
     * @access public
     *
     * @return bool TRUE if the CSS file has been written, FALSE otherwise
     */
    public function sek_dyn_css_maybe_write_css_file() {
        global $wp_filesystem;

        $error = false;

        $base_uri = $this->base_uri;
        if ( ! $wp_filesystem->is_dir( $base_uri ) ) {
            $error = !wp_mkdir_p( $base_uri );
        }

        if ( $error ) {
            return false;
        }

        if ( ! file_exists( $index_path = wp_normalize_path( trailingslashit( $base_uri ) . 'index.php' ) ) ) {
            $wp_filesystem->put_contents( $index_path, "<?php\n// Silence is golden.\n", FS_CHMOD_FILE );
        }


        if ( ! wp_is_writable( $base_uri ) ) {
            return false;
        }
        $this->file_exists = $wp_filesystem->put_contents(
            $this->uri,
            $this->css_string_to_enqueue_or_print,
            FS_CHMOD_FILE
        );
        return $this->file_exists;
    }



    /**
     *
     * Remove the CSS file from the disk, if it exists
     *
     * @access public
     *
     * @return bool TRUE if the CSS file has been deleted (or didn't exist already), FALSE otherwise
     */
    public function sek_dyn_css_maybe_delete_file() {
        if ( $this->file_exists ) {
            global $wp_filesystem;
            $this->file_exists != $wp_filesystem->delete( $this->uri );
            return !$this->file_exists;
        }
        return !$this->file_exists;
    }




    /*
    * Private helpers
    */

    /**
     *
     * Retrieve the actual CSS file existence on the file system
     *
     * @access private
     *
     * @return bool TRUE if the CSS file exists, FALSE otherwise
     */
    private function _sek_dyn_css_file_exists() {
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $this->uri ) ) {
            $file_content = $wp_filesystem->get_contents( $this->uri );
            return $wp_filesystem->is_readable( $this->uri ) && !empty( $file_content );
        } else {
            return false;
        }

    }



    /**
     *
     * Build normalized URI of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URI
     */
    private function _sek_dyn_css_build_uri() {
        if ( ! isset( $this->base_uri ) ) {
            $this->_sek_dyn_css_build_base_uri();
        }
        return wp_normalize_path( trailingslashit( $this->base_uri ) . "{$this->id}.css" );
    }




    /**
     *
     * Build the URL of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URL
     */
    private function _sek_dyn_css_build_url() {
        if ( ! isset( $this->base_url ) ) {
            $this->_sek_dyn_css_build_base_url();
        }
        return trailingslashit( $this->base_url ) . "{$this->id}.css";
    }




    /**
     *
     * Build the URI of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URI
     */
    private function _sek_dyn_css_build_base_uri() {
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $relative_base_path );
    }




    /**
     *
     * Build the URL of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URL
     */
    private function _sek_dyn_css_build_base_url() {
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return set_url_scheme( trailingslashit( $upload_dir['baseurl'] ) . $relative_base_path );
    }




    /**
     *
     * Retrieve the relative path (to the 'uploads' dir ) of the CSS base directory
     *
     * @access private
     *
     * @return string The relative path (to the 'uploads' dir) of the CSS base directory
     */
    private function _sek_dyn_css_build_relative_base_path() {
        $css_base_dir     = self::CSS_BASE_DIR;

        if ( is_multisite() ) {
            $site        = get_site();
            $network_id  = $site->site_id;
            $site_id     = $site->blog_id;
            $css_dir     = trailingslashit( $css_base_dir ) . trailingslashit( $network_id ) . $site_id;
        }

        return $css_base_dir;
    }




    /**
     *
     * Checks whether or not we can write to the disk
     *
     * @access private
     *
     * @return bool Whether or not we have filesystem credentials
     */
    private function _sek_dyn_css_write_file_is_possible() {
        $upload_dir      = wp_get_upload_dir();
        if ( 'direct' === get_filesystem_method( array(), $upload_dir['basedir'] ) ) {
            $creds = request_filesystem_credentials( '', '', false, false, array() );

            /* initialize the API */
            if ( ! WP_Filesystem($creds) ) {
                /* any problems and we exit */
                return false;
            }
            return true;
        }

        return false;
    }



    /**
     *
     * Simple helper to require the WordPress filesystem relevant file
     *
     * @access private
     */
    private function _sek_dyn_css_require_wp_filesystem() {
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }
    }

}

?><?php
add_filter( "sek_add_css_rules_for_input_id", '\Nimble\sek_add_css_rules_for_css_sniffed_input_id', 10, 6 );
function sek_add_css_rules_for_css_sniffed_input_id( $rules, $value, $input_id, $registered_input_list, $parent_level, $module_level_css_selectors ) {

    if ( ! is_string( $input_id ) || empty( $input_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing input_id', $parent_level);
        return $rules;
    }
    if ( ! is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
        sek_error_log( __FUNCTION__ . ' => missing input_list', $parent_level);
        return $rules;
    }
    $input_registration_params = $registered_input_list[ $input_id ];
    if ( ! is_string( $input_registration_params['css_identifier'] ) || empty( $input_registration_params['css_identifier'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing css_identifier', $parent_level );
        return $rules;
    }

    $selector = '[data-sek-id="'.$parent_level['id'].'"]';
    $css_identifier = $input_registration_params['css_identifier'];
    if ( !is_null( $module_level_css_selectors ) && !empty( $module_level_css_selectors ) ) {
        if ( is_array( $module_level_css_selectors ) ) {
            $new_selectors = array();
            foreach ( $module_level_css_selectors as $spec_selector ) {
                $new_selectors[] = $selector . ' ' . $spec_selector;
            }
            $new_selectors = implode(',', $new_selectors );
            $selector = $new_selectors;
        } else if ( is_string( $module_level_css_selectors ) ) {
            $selector .= ' ' . $module_level_css_selectors;
        }
    }
    else if ( 'module' === $parent_level['level'] ) {
        $selector .= ' .sek-module-inner';
    }
    if ( 'module' === $parent_level['level'] ) {
        if ( ! is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input list' );
        } else if ( is_array( $registered_input_list ) && empty( $registered_input_list[ $input_id ] ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input id ' . $input_id . ' in input list for module type ' . $parent_level['module_type'] );
        }
        if ( is_array( $registered_input_list ) && ! empty( $registered_input_list[ $input_id ] ) && ! empty( $registered_input_list[ $input_id ]['css_selectors'] ) ) {
            $selector = '[data-sek-id="'.$parent_level['id'].'"]';
            $input_level_css_selectors = $registered_input_list[ $input_id ]['css_selectors'];
            $new_selectors = array();
            if ( is_array( $input_level_css_selectors ) ) {
                foreach ( $input_level_css_selectors as $spec_selector ) {
                    $new_selectors[] = $selector . ' ' . $spec_selector;
                }
            } else if ( is_string( $input_level_css_selectors ) ) {
                $new_selectors[] = $selector . ' ' . $input_level_css_selectors;
            }

            $new_selectors = implode(',', $new_selectors );
            $selector = $new_selectors;
        }
    }


    $mq = null;
    $properties_to_render = array();

    switch ( $css_identifier ) {
        case 'font_size' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $properties_to_render['font-size'] = $value;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '16px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $rules = sek_set_mq_css_rules( array(
                      'value' => $value,
                      'css_property' => 'font-size',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        case 'line_height' :
            $properties_to_render['line-height'] = $value;
        break;
        case 'font_weight' :
            $properties_to_render['font-weight'] = $value;
        break;
        case 'font_style' :
            $properties_to_render['font-style'] = $value;
        break;
        case 'text_decoration' :
            $properties_to_render['text-decoration'] = $value;
        break;
        case 'text_transform' :
            $properties_to_render['text-transform'] = $value;
        break;
        case 'letter_spacing' :
            $properties_to_render['letter-spacing'] = $value . 'px';
        break;
        case 'color' :
            $properties_to_render['color'] = $value;
        break;
        case 'color_hover' :
            $new_selectors = array();
            $exploded = explode(',', $selector);
            foreach ( $exploded as $sel ) {
                $new_selectors[] = $sel.':hover';
            }

            $selector = implode(',', $new_selectors);
            $properties_to_render['color'] = $value;
        break;
        case 'background_color' :
            $properties_to_render['background-color'] = $value;
        break;
        case 'h_alignment' :
            if ( is_string( $value ) ) {// <= simple
                $properties_to_render['text-align'] = $value;
            } else if ( is_array( $value ) ) {// <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $rules = sek_set_mq_css_rules( array(
                      'value' => $value,
                      'css_property' => 'text-align',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        case 'v_alignment' :
            switch ( $value ) {
                case 'top' :
                    $v_align_value = "flex-start";
                break;
                case 'center' :
                    $v_align_value = "center";
                break;
                case 'bottom' :
                    $v_align_value = "flex-end";
                break;
                default :
                    $v_align_value = "center";
                break;
            }
            $properties_to_render['align-items'] = $v_align_value;
        break;
        case 'font_family' :
            $family = $value;
            if ( false != strstr( $value, '[gfont]') ) {
                $split = explode(":", $family);
                $family = $split[0];
                $properties_to_render['font-weight']    = $split[1] ? preg_replace('/\D/', '', $split[1]) : '';
                $properties_to_render['font-weight']    = empty($properties_to_render['font-weight']) ? 400 : $properties_to_render['font-weight'];
                $properties_to_render['font-style']     = ( $split[1] && strstr($split[1], 'italic') ) ? 'italic' : 'normal';
            }

            $family = str_replace( array( '[gfont]', '[cfont]') , '' , $family );
            $properties_to_render['font-family'] = false != strstr( $value, '[cfont]') ? $family : "'" . str_replace( '+' , ' ' , $family ) . "'";
        break;

        /* Spacer */
        case 'height' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $unit = '%' === $unit ? 'vh' : $unit;
                      $properties_to_render['height'] = $numeric . $unit;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '20px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $unit = '%' === $unit ? 'vh' : $unit;
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'height',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        /* Quote border */
        case 'border_width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $properties_to_render['border-width'] = $numeric . $unit;
            }
        break;
        case 'border_color' :
            $properties_to_render['border-color'] = $value ? $value : '';
        break;
        /* Divider */
        case 'border_top_width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( ! empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $unit = '%' === $unit ? 'vh' : $unit;
                $properties_to_render['border-top-width'] = $numeric . $unit;
            }
        break;
        case 'border_top_style' :
            $properties_to_render['border-top-style'] = $value ? $value : 'solid';
        break;
        case 'border_top_color' :
            $properties_to_render['border-top-color'] = $value ? $value : '#5a5a5a';
        break;
        case 'border_radius' :
            if ( is_string( $value ) ) {
                $numeric = sek_extract_numeric_value( $value );
                if ( ! empty( $numeric ) ) {
                    $unit = sek_extract_unit( $value );
                    $properties_to_render['border-radius'] = $numeric . $unit;
                }
            } else if ( is_array( $value ) ) {
                $rules = sek_generate_css_rules_for_border_radius_options( $rules, $value, $selector );
            }
        break;

        case 'width' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $properties_to_render['width'] = $numeric . $unit;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '100%',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'width',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;

        case 'v_spacing' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( ! empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $unit = '%' === $unit ? 'vh' : $unit;
                      $properties_to_render = array(
                          'margin-top'  => $numeric . $unit,
                          'margin-bottom' => $numeric . $unit
                      );
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '15px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( ! empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $unit = '%' === $unit ? 'vh' : $unit;
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'margin-top',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'margin-bottom',
                      'selector' => $selector,
                      'is_important' => $important,
                  ), $rules );
            }
        break;
        case 'h_alignment_block' :
            switch ( $value ) {
                case 'right' :
                    $properties_to_render = array(
                        'margin-right'  => '0'
                    );
                break;
                case 'left' :
                    $properties_to_render = array(
                        'margin-left'  => '0'
                    );
                break;
                default :
                    $properties_to_render = array(
                        'margin-left'  => 'auto',
                        'margin-right' => 'auto'
                    );
            }
        break;
        case 'padding_margin_spacing' :
            $default_unit = 'px';
            $rules_candidates = $value;
            $unit                 = !empty( $rules_candidates['unit'] ) ? $rules_candidates['unit'] : $default_unit;
            $unit                 = 'percent' == $unit ? '%' : $unit;

            $filtered_rules_candidates = array_filter( $rules_candidates, function( $k ) {
                return 'unit' !== $k;
            }, ARRAY_FILTER_USE_KEY );

            $properties_to_render = $filtered_rules_candidates;

            array_walk( $properties_to_render,
                function( &$val, $key, $unit ) {
                    if ( FALSE !== strpos( 'padding', $key ) ) {
                        $val = abs( $val );
                    }

                    $val .= $unit;
            }, $unit );
        break;
        case 'spacing_with_device_switcher' :
            if ( ! empty( $value ) && is_array( $value ) ) {
                $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $value, $selector );
            }
        break;
        default :
            sek_error_log( __FUNCTION__ . ' => the css_identifier : ' . $css_identifier . ' has no css rules defined for input id ' . $input_id );
        break;
    }//switch
    if ( ! empty( $properties_to_render ) ) {
        $important = false;
        if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
            $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
        }
        $css_rules = '';
        foreach ( $properties_to_render as $prop => $prop_val ) {
            $css_rules .= sprintf( '%1$s:%2$s%3$s;', $prop, $prop_val, $important ? '!important' : '' );
        }//end foreach

        $rules[] = array(
            'selector'    => $selector,
            'css_rules'   => $css_rules,
            'mq'          => $mq
        );
    }
    return $rules;
}
function sek_is_flagged_important( $input_id, $module_value, $registered_input_list ) {
    $important = false;

    if ( ! is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
        sek_error_log( __FUNCTION__ . ' => error => the $registered_input_list param should be an array not empty');
        return $important;
    }
    foreach ( $registered_input_list as $_id => $input_data ) {
        if ( is_string( $_id ) && false !== strpos( $_id, '_flag_important' ) ) {
            if ( empty( $input_data[ 'important_input_list' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => error => missing important_input_list for input id ' . $_id );
            } else {
                $important_list_candidate = $input_data[ 'important_input_list' ];
                if ( in_array( $input_id, $important_list_candidate ) ) {
                    $important = sek_booleanize_checkbox_val( sek_get_input_value_in_module_model( $_id, $module_value ) );
                }
            }
        }
    }
    return $important;
}
?><?php
if ( ! class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $local_seks = 'not_cached';// <= used to cache the sektions for the local skope_id
        public $global_seks = 'not_cached';// <= used to cache the sektions for the global skope_id
        public $model = array();//<= when rendering, the current level model
        public $parent_model = array();//<= when rendering, the current parent model
        public $default_models = array();// <= will be populated to cache the default models when invoking sek_get_default_module_model
        public $cached_input_lists = array(); // <= will be populated to cache the input_list of each registered module. Useful when we need to get info like css_selector for a particular input type or id.
        public $ajax_action_map = array();
        public $default_locations = [
            'loop_start' => array( 'priority' => 10 ),
            'before_content' => array(),
            'after_content' => array(),
            'loop_end' => array( 'priority' => 10 ),
        ];
        public $registered_locations = [];
        public $default_registered_location_model = [
          'priority' => 10,
          'is_global_location' => false,
          'is_header_location' => false,
          'is_footer_location' => false
        ];
        public $default_location_model = [
            'id' => '',
            'level' => 'location',
            'collection' => [],
            'options' => [],
            'ver_ini' => NIMBLE_VERSION
        ];
        public $rendered_levels = [];//<= stores the ids of the level rendered with ::render()

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sek_Nimble_Manager ) ) {
                self::$instance = new Sek_Nimble_Manager( $params );
                do_action( 'nimble_front_classes_ready', self::$instance );
            }
            return self::$instance;
        }
        public $img_smartload_enabled = 'not_cached';
        function __construct( $params = array() ) {
            $this->registered_locations = $this->default_locations;
            $this -> _schedule_front_ajax_actions();
            $this -> _schedule_img_import_ajax_actions();
            $this -> _schedule_section_saving_ajax_actions();
            $this -> _schedule_front_and_preview_assets_printing();
            $this -> _schedule_front_rendering();
            $this -> _setup_hook_for_front_css_printing_or_enqueuing();
            $this -> _setup_simple_forms();
        }//__construct
    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Ajax' ) ) :
    class SEK_Front_Ajax extends SEK_Front_Construct {
        function _schedule_front_ajax_actions() {
            add_action( 'wp_ajax_sek_get_content', array( $this, 'sek_get_level_content_for_injection' ) );
            add_action( 'wp_ajax_sek_get_preset_sections', array( $this, 'sek_get_preset_sektions' ) );
            $this -> ajax_action_map = array(
                  'sek-add-section',
                  'sek-remove-section',
                  'sek-duplicate-section',
                  'sek-add-content-in-new-nested-sektion',
                  'sek-add-content-in-new-sektion',
                  'sek-add-column',
                  'sek-remove-column',
                  'sek-duplicate-column',
                  'sek-resize-columns',
                  'sek-refresh-columns-in-sektion',

                  'sek-add-module',
                  'sek-remove-module',
                  'sek-duplicate-module',
                  'sek-refresh-modules-in-column',

                  'sek-refresh-stylesheet',

                  'sek-refresh-level'
            );
        }
        function _schedule_img_import_ajax_actions() {
            add_action( 'wp_ajax_sek_import_attachment', array( $this, 'sek_ajax_import_attachemnt' ) );
        }
        function _schedule_section_saving_ajax_actions() {
            add_action( 'wp_ajax_sek_save_section', array( $this, 'sek_ajax_save_section' ) );
            add_action( 'wp_ajax_sek_get_user_saved_sections', array( $this, 'sek_sek_get_user_saved_sections' ) );
        }
        function sek_get_preset_sektions() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( 'sek_ajax_save_section => check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }
            $preset_sections = sek_get_preset_sektions();
            if ( empty( $preset_sections ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no preset_sections when running sek_get_preset_sektions()' );
            }
            wp_send_json_success( $preset_sections );
        }
        function sek_get_level_content_for_injection( $params ) {
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }

            if ( ! isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }

            if ( ! isset( $_POST['sek_action'] ) || empty( $_POST['sek_action'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing sek_action' );
            }
            $sek_action = $_POST['sek_action'];

            $exported_setting_validities = array();
            if ( is_customize_preview() ) {
                global $wp_customize;
                $setting_validities = $wp_customize->validate_setting_values( $wp_customize->unsanitized_post_values() );
                $raw_exported_setting_validities = array_map( array( $wp_customize, 'prepare_setting_validity_for_js' ), $setting_validities );
                $exported_setting_validities = array();
                foreach( $raw_exported_setting_validities as $setting_id => $validity ) {
                    if ( false === strpos( $setting_id , NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) )
                      continue;
                    $exported_setting_validities[ $setting_id ] = $validity;
                }
            }
            if ( in_array( $sek_action, $this -> ajax_action_map ) ) {
                $html = $this -> sek_ajax_fetch_content( $sek_action );
                if ( is_wp_error( $html ) ) {
                    wp_send_json_error( $html );
                } else {
                    $response = array(
                        'contents' => $html,
                        'setting_validities' => $exported_setting_validities
                    );
                    wp_send_json_success( apply_filters( 'sek_content_results', $response, $sek_action ) );
                }
            } else {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => this ajax action ( ' . $sek_action . ' ) is not listed in the map ' );
            }


        }//sek_get_content_for_injection()
        private function sek_ajax_fetch_content( $sek_action = '' ) {
            $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
            if ( ! is_array( $sektionSettingValue ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue => it should be an array().' );
                return;
            }
            if ( empty( $sek_action ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => invalid sek_action param' );
                return;
            }
            $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
            if ( ! is_array( $sektion_collection ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektion_collection => it should be an array().' );
                return;
            }

            $candidate_id = '';
            $collection = array();
            $level_model = array();

            $is_stylesheet = false;

            switch ( $sek_action ) {
                case 'sek-add-section' :
                case 'sek-add-content-in-new-sektion' :
                case 'sek-add-content-in-new-nested-sektion' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        $this -> parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;
                case 'sek-remove-section' :
                    if ( ! array_key_exists( 'is_nested', $_POST ) || true !== json_decode( $_POST['is_nested'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' sek-remove-section => the section must be nested in this ajax action' );
                        break;
                    } else {
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    }
                break;

                case 'sek-duplicate-section' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;
                case 'sek-add-column' :
                case 'sek-remove-column' :
                case 'sek-duplicate-column' :
                case 'sek-refresh-columns-in-sektion' :
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST['in_sektion'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_sektion param' );
                        break;
                    }
                    $level_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                break;
                case 'sek-add-module' :
                case 'sek-remove-module' :
                case 'sek-refresh-modules-in-column' :
                case 'sek-duplicate-module' :
                    if ( ! array_key_exists( 'in_column', $_POST ) || empty( $_POST['in_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_column param' );
                        break;
                    }
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST[ 'in_sektion' ] ) ) {
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $this -> parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                break;

                case 'sek-resize-columns' :
                    if ( ! array_key_exists( 'resized_column', $_POST ) || empty( $_POST['resized_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing resized_column' );
                        break;
                    }
                    $is_stylesheet = true;
                break;

                case 'sek-refresh-stylesheet' :
                    $is_stylesheet = true;
                break;

                 case 'sek-refresh-level' :
                    if ( ! array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing level id' );
                        break;
                    }
                    if ( !empty( $_POST['level'] ) && 'column' === $_POST['level'] ) {
                        $this -> parent_model = sek_get_parent_level_model( $_POST['id'], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                break;
            }//Switch sek_action

            ob_start();

            if ( $is_stylesheet ) {
                $r = $this -> print_or_enqueue_seks_style( $_POST['location_skope_id'] );
            } else {
                if ( 'no_match' == $level_model ) {
                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action . ' => missing level model' );
                    ob_end_clean();
                    return;
                }
                if ( empty( $level_model ) || ! is_array( $level_model ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => empty or invalid $level_model' );
                    ob_end_clean();
                    return;
                }
                $r = $this -> render( $level_model );
            }
            $html = ob_get_clean();
            if ( is_wp_error( $r ) ) {
                return $r;
            } else {
                if ( ! $is_stylesheet && empty( $html ) ) {
                      $html = new \WP_Error( 'ajax_fetch_content_error', __CLASS__ . '::' . __FUNCTION__ . ' => no content returned for sek_action : ' . $sek_action );
                }
                return apply_filters( "sek_set_ajax_content", $html, $sek_action );// this is sent with wp_send_json_success( apply_filters( 'sek_content_results', $html, $sek_action ) );
            }
        }
        function sek_ajax_import_attachemnt() {
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }
            $relative_path = $_POST['rel_path'];
            $filename = 'nimble_asset_' . basename( $relative_path );
            $args = array(
                'posts_per_page' => 1,
                'post_type'      => 'attachment',
                'name'           => trim ( $filename ),
            );
            $get_attachment = new \WP_Query( $args );
            if ( is_array( $get_attachment->posts ) && array_key_exists(0, $get_attachment->posts) ) {
                $new_attachment = array(
                    'id'  => $get_attachment->posts[0] -> ID,
                    'url' => $get_attachment->posts[0] -> guid
                );
            }
            if ( isset($new_attachment ) ) {
                wp_send_json_success( $new_attachment );
            } else {
                if ( ! file_exists( NIMBLE_BASE_PATH . $relative_path ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __CLASS__ . '::' . __FUNCTION__ . ' => no file found for relative path : ' . dirname( __FILE__ ) . $relative_path );
                    return;
                }
                $url = NIMBLE_BASE_URL . $relative_path;
                $url_content = wp_safe_remote_get( $url );

                if ( '404' == $url_content['response']['code'] ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => 404 response when wp_safe_remote_get() url : ' . $url );
                    return;
                }
                $file_content = wp_remote_retrieve_body( $url_content );
                if ( empty( $file_content ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => empty file_content when wp_remote_retrieve_body() for url : ' . $url );
                    return;
                }

                $upload = wp_upload_bits(
                  $filename,
                  '',
                  $file_content
                );

                $attachment = [
                  'post_title' => $filename,
                  'guid' => $upload['url'],
                ];
                $info = wp_check_filetype( $upload['file'] );
                if ( $info ) {
                    $attachment['post_mime_type'] = $info['type'];
                } else {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no info available with wp_check_filetype() when setting the mime type of img : ' . $url );
                    return;
                }

                $attachment_id = wp_insert_attachment( $attachment, $upload['file'] );
                if ( is_wp_error( $attachment_id ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => problem when trying to wp_insert_attachment() for img : ' . $url );
                }

                wp_update_attachment_metadata(
                    $attachment_id,
                    wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
                );

                $new_attachment = [
                  'id' => $attachment_id,
                  'url' => $upload['url'],
                ];
                wp_send_json_success( $new_attachment );
            }
        }
        function sek_ajax_save_section() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( 'sek_ajax_save_section => check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __FUNCTION__ . ' => bad_method' );
            }
            if ( empty( $_POST['sek_title']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing title' );
            }
            if ( empty( $_POST['sek_id']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing sektion_id' );
            }
            if ( empty( $_POST['sek_data']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing sektion data' );
            }
            if ( ! is_string( $_POST['sek_data'] ) ) {
                wp_send_json_error( __FUNCTION__ . ' => the sektion data must be a json stringified' );
            }
            $sektion_to_save = array(
                'title' => $_POST['sek_title'],
                'description' => $_POST['sek_description'],
                'id' => $_POST['sek_id'],
                'type' => 'content',//in the future will be used to differentiate header, content and footer sections
                'creation_date' => date("Y-m-d H:i:s"),
                'update_date' => '',
                'data' => $_POST['sek_data']//<= json stringified
            );

            $saved_section_post = sek_update_saved_seks_post( $sektion_to_save );
            if ( is_wp_error( $saved_section_post ) ) {
                wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_saved_seks_post()' );
            } else {
                wp_send_json_success( [ 'section_post_id' => $saved_section_post -> ID ] );
            }
        }
        function sek_sek_get_user_saved_sections() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( 'sek_ajax_save_section => check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __FUNCTION__ . ' => bad_method' );
            }
            if ( empty( $_POST['preset_section_id']) || ! is_string( $_POST['preset_section_id'] ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing or invalid preset_section_id' );
            }
            $section_id = $_POST['preset_section_id'];

            $section_data_decoded_from_custom_post_type = sek_get_saved_sektion_data( $section_id );
            if ( ! empty( $section_data_decoded_from_custom_post_type ) ) {
                wp_send_json_success( $section_data_decoded_from_custom_post_type );
            } else {
                $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
                if ( ! is_array( $all_saved_seks ) || empty( $all_saved_seks[$section_id]) ) {
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing section data in get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS )' );
                }
                $section_infos = $all_saved_seks[$section_id];
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing post data for section title ' . $section_infos['title'] );
            }
        }
        /*function sek_get_ui_content_for_injection( $params ) {
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
                return;
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
                return;
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
                return;
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
                return;
            }

            if ( ! isset( $_POST['level'] ) || empty( $_POST['level'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing level' );
                return;
            }
            if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing level id' );
                return;
            }
            if ( ! isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
                return;
            }
            $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
            if ( ! is_array( $sektionSettingValue ) || ! array_key_exists( 'collection', $sektionSettingValue ) || ! is_array( $sektionSettingValue['collection'] ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue' );
                return;
            }
            $this -> parent_model = sek_get_parent_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );
            $this -> model = sek_get_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );

            $level = $_POST['level'];

            $html = '';
            ob_start();
                load_template( dirname( __FILE__ ) . "/tmpl/ui/block-overlay-{$level}.php", false );
            $html = ob_get_clean();

            if ( empty( $html ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no content returned' );
            } else {
                wp_send_json_success( apply_filters( 'sek_ui_content_results', $html ) );
            }
        }//sek_get_content_for_injection()*/

    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        function _schedule_front_and_preview_assets_printing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_assets' ) );
            add_action ( 'customize_preview_init' , array( $this, 'sek_schedule_customize_preview_assets' ) );
        }
        function sek_enqueue_front_assets() {
            $rtl_suffix = is_rtl() ? '-rtl' : '';
            wp_enqueue_style(
                'sek-base',
                sprintf(
                    '%1$s/assets/front/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? "sek-base{$rtl_suffix}.css" : "sek-base{$rtl_suffix}.min.css"
                ),
                array(),
                NIMBLE_ASSETS_VERSION,
                'all'
            );
            wp_enqueue_script(
                'sek-main-js',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.js' : NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.min.js',
                array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                true
            );
            if ( ! skp_is_customizing() && sek_front_needs_font_awesome() ) {
                wp_enqueue_style(
                    'czr-font-awesome',
                    NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }
            wp_localize_script(
                'sek-main-js',
                'sekFrontLocalized',
                array(
                    'isDevMode' => sek_is_dev_mode(),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
                    'localSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks() ) : '',
                    'globalSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID ) ) : ''
                )
            );
        }
        function sek_schedule_customize_preview_assets() {
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                sprintf(
                    '%1$s/assets/czr/sek/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'sek-preview.css' : 'sek-preview.min.css'
                ),
                array( 'sek-base' ),
                NIMBLE_ASSETS_VERSION,
                'all'
            );
            wp_enqueue_style(
                'czr-font-awesome',
                NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                array(),
                NIMBLE_ASSETS_VERSION,
                $media = 'all'
            );
            wp_enqueue_script(
                'sek-customize-preview',
                sprintf(
                    '%1$s/assets/czr/sek/js/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'ccat-sek-preview.js' : 'ccat-sek-preview.min.js'
                ),
                array( 'customize-preview', 'underscore'),
                NIMBLE_ASSETS_VERSION,
                true
            );

            wp_localize_script(
                'sek-customize-preview',
                'sekPreviewLocalized',
                array(
                    'i18n' => array(
                        "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
                        "Moving elements between global and local sections is not allowed." => __( "Moving elements between global and local sections is not allowed.", 'text_domain_to_be_replaced'),
                        'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_domain_to_be_replaced'),
                        'Insert here' => __('Insert here', 'text_domain_to_be_replaced'),
                        'This content has been created with the WordPress editor.' => __('This content has been created with the WordPress editor.', 'text_domain' ),

                        'Insert a new section' => __('Insert a new section', 'text_domain_to_be_replaced' ),
                        'Insert a new global section' => __('Insert a new global section', 'text_domain_to_be_replaced' ),

                        'section' => __('section', 'text_domain_to_be_replaced'),
                        'section (global)' => __('section (global)', 'text_domain_to_be_replaced'),
                        'nested section' => __('nested section', 'text_domain_to_be_replaced'),
                    ),
                    'isDevMode' => sek_is_dev_mode(),
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),

                    'registeredModules' => CZR_Fmk_Base() -> registered_modules,
                )
            );

            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_style(
                'ui-sortable',
                '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
                array(),
                null,//time(),
                $media = 'all'
            );
            wp_enqueue_script( 'jquery-ui-resizable' );
        }
        function sek_print_ui_tmpl() {
            ?>
              <script type="text/html" id="sek-tmpl-add-content-button">
                  <# //console.log( 'data', data ); #>
                  <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                    <div class="sek-add-content-button-wrapper">
                     <# var hook_location = '', btn_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['Insert a new section'] : sekPreviewLocalized.i18n['Insert a new global section'], addContentBtnWidth = true !== data.is_global_location ? '83px' : '113px' #>
                      <?php if ( sek_is_dev_mode() ) : ?>
                          <# if ( data.location ) {
                              hook_location = '( @hook : ' + data.location + ')';
                          } #>
                      <?php endif; ?>
                      <button title="{{btn_title}} {{hook_location}}" data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:{{addContentBtnWidth}};">
                        <span class="sek-click-on-button-icon sek-click-on">+</span><span class="action-button-text">{{btn_title}}</span>
                      </button>
                    </div>
                  </div>
              </script>

              <?php
                  $icon_right_side_class = is_rtl() ? 'sek-dyn-left-icons' : 'sek-dyn-right-icons';
                  $icon_left_side_class = is_rtl() ? 'sek-dyn-right-icons' : 'sek-dyn-left-icons';
              ?>

              <script type="text/html" id="sek-dyn-ui-tmpl-section">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <# //console.log( 'data', data ); #>
                  <div class="sek-dyn-ui-wrapper sek-section-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <?php // if this is a nested section, it has the is_nested property set to true. We don't want to make it movable for the moment. @todo ?>
                        <?php if ( sek_is_dev_mode() ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <# if ( ! data.is_nested && true !== data.is_global_location ) { #>
                          <i class="fas fa-arrows-alt sek-move-section" title="<?php _e( 'Move section', 'text_domain' ); ?>"></i>
                        <# } #>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">tune</i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-click-on="add-column" class="material-icons sek-click-on" title="<?php _e( 'Add a column', 'text_domain' ); ?>">view_column</i>
                        <# } #>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate section', 'text_domain' ); ?>">filter_none</i>
                        <?php if ( defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) && NIMBLE_SAVED_SECTIONS_ENABLED ) : ?>
                          <i data-sek-click-on="toggle-save-section-ui" class="sek-save far fa-save" title="<?php _e( 'Save this section', 'text_domain' ); ?>"></i>
                        <?php endif; ?>
                        <i data-sek-click-on="pick-content" data-sek-content-type="module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove section', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-span-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <#
                          var section_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['section'] : sekPreviewLocalized.i18n['section (global)'];
                          section_title = ! data.is_nested ? section_title : sekPreviewLocalized.i18n['nested section'];
                        #>
                        <div class="sek-dyn-ui-level-type">{{section_title}}</div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-dyn-ui-wrapper sek-column-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_right_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-column" title="<?php _e( 'Move column', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">tune</i>
                        <# if ( ! data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-click-on="add-section" class="material-icons sek-click-on" title="<?php _e( 'Add a nested section', 'text_domain' ); ?>">account_balance_wallet</i>
                        <# } #>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate column', 'text_domain' ); ?>">filter_none</i>
                        <# } #>

                        <i data-sek-click-on="pick-content" data-sek-content-type="module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <# if ( ! data.parent_is_single_column ) { #>
                          <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove column', 'text_domain' ); ?>">delete_forever</i>
                        <# } #>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-span-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type"><?php _e( 'column', 'text_domain' ); ?></div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-module">
                  <div class="sek-dyn-ui-wrapper sek-module-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-module" title="<?php _e( 'Move module', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-module" class="fas fa-pencil-alt sek-tip sek-click-on" title="<?php _e( 'Edit module content', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">tune</i>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate module', 'text_domain' ); ?>">filter_none</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove module', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <#
                      var module_name = ! _.isEmpty( data.module_name ) ? data.module_name + ' ' + '<?php _e("module", "text_domain"); ?>' : '<?php _e("module", "text_domain"); ?>';
                    #>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-module" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-span-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type">{{module_name}}</div>
                      </div>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-wp-content">
                  <div class="sek-dyn-ui-wrapper sek-wp-content-dyn-ui">
                    <div class="sek-dyn-ui-inner">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-pencil-alt sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"></i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <span class="sek-dyn-ui-location-type" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <i class="fab fa-wordpress sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"> <?php _e( 'WordPress content', 'text_domain'); ?></i>
                    </span>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>
            <?php
        }
    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Render' ) ) :
    class SEK_Front_Render extends SEK_Front_Assets {
        function _schedule_front_rendering() {
            if ( !defined( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY", 1 - PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY", PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY" ) ) { define( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY", - PHP_INT_MAX ); }
            add_action( 'template_redirect', array( $this, 'sek_schedule_rendering_hooks') );
            add_filter( 'the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_the_printed_module_assets') );
            add_filter( 'template_include', array( $this, 'sek_maybe_set_local_nimble_template') );
            add_filter( 'nimble_parse_for_smart_load', array( $this, 'sek_maybe_process_img_for_js_smart_load') );
            $this -> sek_setup_tiny_mce_content_filters();
            add_action( 'nimble_front_classes_ready', array( $this, 'sek_register_nimble_global_locations') );
        }//_schedule_front_rendering()
        function sek_wrap_wp_content( $html ) {
            if ( ! skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) )
              return $html;
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                global $post;
                $html = sprintf( '<div class="sek-wp-content-wrapper" data-sek-wp-post-id="%1$s" data-sek-wp-edit-link="%2$s" title="%3$s">%4$s</div>',
                      $post->ID,
                      $this->get_unfiltered_edit_post_link( $post->ID ),
                      __( 'WordPress content', 'text_domain'),
                      wpautop( $html )
                );
            }
            return $html;
        }
        function sek_register_nimble_global_locations() {
            register_location('nimble_global_header', array( 'is_global_location' => true, 'is_header_location' => true ) );
            register_location('nimble_global_footer', array( 'is_global_location' => true, 'is_footer_location' => true ) );
        }
        function sek_schedule_rendering_hooks() {
            $locale_template = sek_get_locale_template();
            $all_locations = sek_get_locations();
            foreach( $all_locations as $location_id => $params ) {
                $params = is_array( $params ) ? $params : array();
                $params = wp_parse_args( $params, array( 'priority' => 10 ) );
                if ( !empty( $locale_template ) && !array_key_exists( $location_id, Nimble_Manager()->default_locations ) ) {
                    add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                } else {
                    switch ( $location_id ) {
                        case 'loop_start' :
                        case 'loop_end' :
                            add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                        break;
                        case 'before_content' :
                            add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                        break;
                        case 'after_content' :
                            add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );
                        break;
                        default :
                            add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                        break;
                    }
                }

            }
        }
        function sek_schedule_sektions_rendering( $query = null ) {
            if ( is_object( $query ) && is_a( $query, 'WP_Query' ) && ! $query->is_main_query() ) {
                return;
            }

            $location_id = current_filter();
            if ( did_action( "sek_before_location_{$location_id}" ) )
              return;

            do_action( "sek_before_location_{$location_id}" );
            $this->_render_seks_for_location( $location_id );
            do_action( "sek_after_location_{$location_id}" );
        }
        function sek_schedule_sektion_rendering_before_content( $html ) {
            if ( did_action( 'sek_before_location_before_content' ) )
              return $html;

            do_action( 'sek_before_location_before_content' );
            return $this -> _filter_the_content( $html, 'before_content' );
        }
        function sek_schedule_sektion_rendering_after_content( $html ) {
            if ( did_action( 'sek_before_location_after_content' ) )
              return $html;

            do_action( 'sek_before_location_after_content' );
            return $this -> _filter_the_content( $html, 'after_content' );
        }

        private function _filter_the_content( $html, $where ) {
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                ob_start();
                $this->_render_seks_for_location( $where );
                $html = 'before_content' == $where ? ob_get_clean() . $html : $html . ob_get_clean();
                if ( strpos( $html, '<div' ) !== false ) {
                  $html = preg_replace( '|\s*<div|', '<div', $html );
                  $html = preg_replace( '|</div>\s*|', '</div>', $html );
                }
            }

            return $html;
        }
        public function _render_seks_for_location( $location_id = '', $location_data = array() ) {
            $all_locations = sek_get_locations();

            if ( ! array_key_exists( $location_id, $all_locations ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location_id . ' is not registered in sek_get_locations()');
                return;
            }
            $locationSettingValue = array();
            if ( empty( $location_data ) ) {
                $skope_id = sek_is_global_location( $location_id )  ? NIMBLE_GLOBAL_SKOPE_ID : skp_build_skope_id();
                $locationSettingValue = sek_get_skoped_seks( $skope_id, $location_id );
            } else {
                $locationSettingValue = $location_data;
            }
            if ( is_array( $locationSettingValue ) ) {

                remove_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                $this->render( $locationSettingValue, $location_id );

                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ),NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                add_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );


            } else {
                error_log( __CLASS__ . ' :: ' . __FUNCTION__ .' => sek_get_skoped_seks() should always return an array().');
            }
        }










        /* ------------------------------------------------------------------------- *
         * RENDERING UTILITIES USED IN NIMBLE TEMPLATES
        /* ------------------------------------------------------------------------- */
        function render_nimble_locations( $locations, $options = array() ) {
            if ( is_string( $locations ) && ! empty( $locations ) ) {
                $locations = array( $locations );
            }
            if ( ! is_array( $locations ) ) {
                sek_error_log( __FUNCTION__ . ' error => missing or invalid locations provided');
                return;
            }
            $options = ! is_array( $options ) ? array() : $options;
            $options = wp_parse_args( $options, array(
                'fallback_location' => null, // Typically set as 'loop_start' in the nimble templates
            ));

            foreach( $locations as $location_id ) {
                if ( ! is_string( $location_id ) || empty( $location_id ) ) {
                    sek_error_log( __FUNCTION__ . ' => error => a location_id is not valid in the provided locations', $locations );
                    continue;
                }
                if ( did_action( "sek_before_location_{$location_id}" ) )
                  continue;

                $is_global = sek_is_global_location( $location_id );
                $skope_id = $is_global ? NIMBLE_GLOBAL_SKOPE_ID : skp_get_skope_id();
                $locationSettingValue = sek_get_skoped_seks( $skope_id, $location_id );
                if ( ! is_null( $options[ 'fallback_location' ]) ) {
                    if ( $options[ 'fallback_location' ] === $location_id || ( is_array( $locationSettingValue ) && ! empty( $locationSettingValue['collection'] ) ) ) {
                        do_action( "sek_before_location_{$location_id}" );
                        Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                        do_action( "sek_after_location_{$location_id}" );
                    }
                } else {
                    do_action( "sek_before_location_{$location_id}" );
                    Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                    do_action( "sek_after_location_{$location_id}" );
                }

            }//render_nimble_locations()
        }
        function render_content_sections_for_nimble_template() {
            render_nimble_locations(
                array_keys( \Nimble\Nimble_Manager()->default_locations ),//array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
                array( 'fallback_location' => 'loop_start' )
            );
        }









        /* ------------------------------------------------------------------------- *
         *  MAIN RENDERING METHOD
        /* ------------------------------------------------------------------------- */
        function render( $model = array(), $location = 'loop_start' ) {
            if ( ! is_array( $model ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a model must be an array', $model );
                return;
            }
            if ( ! array_key_exists( 'level', $model ) || ! array_key_exists( 'id', $model ) ) {
                error_log( '::render() => a level model is missing the level or the id property' );
                return;
            }
            $id = $model['id'];
            if ( ! is_string( $id ) || empty( $id ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a level id must be a string not empty', $model );
                return;
            }
            $level_type = $model['level'];
            if ( ! is_string( $level_type ) || empty( $level_type ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a level type must be a string not empty', $model );
                return;
            }
            if ( in_array( $id, Nimble_Manager()->rendered_levels ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a ' . $level_type . ' level id has already been rendered : ' . $id );
                return;
            }
            Nimble_Manager()->rendered_levels[] = $id;
            $parent_model = $this -> parent_model;
            $this -> model = $model;

            $collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();

            switch ( $level_type ) {
                case 'location' :
                    ?>
                      <?php if ( skp_is_customizing() || ( ! skp_is_customizing() && ! empty( $collection ) ) ) : ?>
                            <?php
                              printf( '<div class="sektion-wrapper" data-sek-level="location" data-sek-id="%1$s" %2$s %3$s %4$s>',
                                  $id,
                                  sprintf('data-sek-is-global-location="%1$s"', sek_is_global_location( $id ) ? 'true' : 'false'),
                                  true === sek_get_registered_location_property( $id, 'is_header_location' ) ? 'data-sek-is-header-location="true"' : '',
                                  true === sek_get_registered_location_property( $id, 'is_footer_location' ) ? 'data-sek-is-footer-location="true"' : ''
                              );
                            ?>
                            <?php
                              $this -> parent_model = $model;
                              foreach ( $collection as $_key => $sec_model ) { $this -> render( $sec_model ); }
                            ?>

                             <?php if ( empty( $collection ) ) : ?>
                                <div class="sek-empty-location-placeholder"></div>
                            <?php endif; ?>
                          </div><?php //class="sektion-wrapper" ?>
                      <?php endif; ?>
                    <?php
                break;

                case 'section' :
                    $is_nested = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
                    $has_at_least_one_module = sek_section_has_modules( $collection );
                    $column_container_class = 'sek-container-fluid';
                    if ( !empty( $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) {
                        $column_container_class = 'sek-container';
                    }
                    $custom_anchor = null;
                    if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                        if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                            $custom_anchor = $model[ 'options' ][ 'anchor' ]['custom_anchor'];
                        }
                    }

                    ?>
                    <?php printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section %3$s %4$s" %5$s %6$s>',
                        $id,
                        $is_nested ? 'data-sek-is-nested="true"' : '',
                        $has_at_least_one_module ? 'sek-has-modules' : '',
                        $this->get_level_visibility_css_class( $model ),
                        is_null( $custom_anchor ) ? '' : 'id="' . $custom_anchor . '"',
                        $this -> sek_maybe_add_bg_attributes( $model )
                    ); ?>
                          <div class="<?php echo $column_container_class; ?>">
                            <div class="sek-row sek-sektion-inner">
                                <?php
                                  $this -> parent_model = $model;
                                  foreach ( $collection as $col_model ) {$this -> render( $col_model ); }
                                ?>
                            </div>
                          </div>
                      </div><?php //data-sek-level="section" ?>
                    <?php
                break;

                case 'column' :
                    $col_number = ( array_key_exists( 'collection', $parent_model ) && is_array( $parent_model['collection'] ) ) ? count( $parent_model['collection'] ) : 1;
                    $col_number = 12 < $col_number ? 12 : $col_number;
                    $col_width_in_percent = 100/$col_number;
                    $col_suffix = floor( $col_width_in_percent );
                    $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
                    $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;
                    $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( $parent_model ) );
                    $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

                    $grid_column_class = "sek-col-{$col_suffix}";
                    if ( $has_section_custom_breakpoint ) {
                        $grid_column_class = "sek-section-custom-breakpoint-col-{$col_suffix}";
                    } else if ( $has_global_custom_breakpoint ) {
                        $grid_column_class = "sek-global-custom-breakpoint-col-{$col_suffix}";
                    }
                    ?>
                      <?php
                          printf('<div data-sek-level="column" data-sek-id="%1$s" class="sek-column sek-col-base %2$s %3$s" %4$s %5$s>',
                              $id,
                              $grid_column_class,
                              $this->get_level_visibility_css_class( $model ),
                              empty( $collection ) ? 'data-sek-no-modules="true"' : '',
                              $this -> sek_maybe_add_bg_attributes( $model )
                          );
                      ?>
                        <?php
                        ?>
                        <div class="sek-column-inner <?php echo empty( $collection ) ? 'sek-empty-col' : ''; ?>">
                            <?php
                              if ( skp_is_customizing() && empty( $collection ) ) {
                                  $content_type = 1 === $col_number ? 'section' : 'module';
                                  $title = 'section' === $content_type ? __('Drag and drop a section here', 'text_domain_to_be_replaced' ) : __('Drag and drop a module here', 'text_domain_to_be_replaced' );
                                  ?>
                                  <div class="sek-no-modules-column">
                                    <div class="sek-module-drop-zone-for-first-module sek-content-module-drop-zone sek-drop-zone">
                                      <i data-sek-click-on="pick-content" data-sek-content-type="<?php echo $content_type; ?>" class="material-icons sek-click-on" title="<?php echo $title; ?>">add_circle_outline</i>
                                    </div>
                                  </div>
                                  <?php
                              } else {
                                  $this -> parent_model = $model;
                                  foreach ( $collection as $module_or_nested_section_model ) {
                                      ?>
                                      <?php
                                      $this -> render( $module_or_nested_section_model );
                                  }
                                  ?>
                                  <?php
                              }
                            ?>
                        </div>
                      </div><?php //data-sek-level="column" ?>
                    <?php
                break;

                case 'module' :
                    if ( empty( $model['module_type'] ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing module_type for a module', $model );
                        break;
                    }
                    $module_type = $model['module_type'];
                    $model = sek_normalize_module_value_with_defaults( $model );
                    $this -> model = $model;
                    $title_attribute = '';
                    if ( skp_is_customizing() ) {
                        $title_attribute = __('Edit module settings', 'text-domain');
                        $title_attribute = 'title="'.$title_attribute.'"';
                    }
                    ?>
                      <?php printf('<div data-sek-level="module" data-sek-id="%1$s" data-sek-module-type="%2$s" class="sek-module %3$s" %4$s %5$s>',
                          $id,
                          $module_type,
                          $this->get_level_visibility_css_class( $model ),
                          $title_attribute,
                          $this -> sek_maybe_add_bg_attributes( $model )
                        );?>
                            <div class="sek-module-inner">
                              <?php $this -> sek_print_module_tmpl( $model ); ?>
                            </div>
                      </div><?php //data-sek-level="module" ?>
                    <?php
                break;

                default :
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' error => a level is invalid : ' . $level_type  );
                break;
            }

            $this -> parent_model = $parent_model;
        }//render









        /* ------------------------------------------------------------------------- *
         * VARIOUS HELPERS
        /* ------------------------------------------------------------------------- */
        /* HELPER TO PRINT THE VISIBILITY CSS CLASS IN THE LEVEL CONTAINER */
        private function get_level_visibility_css_class( $model ) {
            if ( ! is_array( $model ) ) {
                error_log( __FUNCTION__ . ' => $model param should be an array' );
                return;
            }
            $visibility_class = '';
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'visibility' ] ) ) {
                if ( is_array( $model[ 'options' ][ 'visibility' ] ) ) {
                    foreach ( $model[ 'options' ][ 'visibility' ] as $device_type => $device_visibility_bool ) {
                        if ( true !== sek_booleanize_checkbox_val( $device_visibility_bool ) ) {
                            $visibility_class .= " sek-hidden-on-{$device_type}";
                        }
                    }
                }
            }
            return $visibility_class;
        }








        /* MODULE AND PLACEHOLDER */
        private function sek_print_module_tmpl( $model ) {
            if ( ! is_array( $model ) ) {
                error_log( __FUNCTION__ . ' => $model param should be an array' );
                return;
            }
            if ( ! array_key_exists( 'module_type', $model ) ) {
                error_log( __FUNCTION__ . ' => a module type must be provided' );
                return;
            }
            $module_type = $model['module_type'];
            $render_tmpl_path = sek_get_registered_module_type_property( $module_type, 'render_tmpl_path' );
            if ( !empty( $render_tmpl_path ) ) {
                load_template( $render_tmpl_path, false );
            } else {
                error_log( __FUNCTION__ . ' => no template found for module type ' . $module_type  );
            }

        }


        function sek_get_input_placeholder_content( $input_type = '', $input_id = '' ) {
            $ph = '<i class="material-icons">pan_tool</i>';
            switch( $input_type ) {
                case 'tiny_mce_editor' :
                case 'text' :
                  $ph = skp_is_customizing() ? '<div class="sek-tiny-mce-module-placeholder-text">' . __('Click to edit', 'here') .'</div>' : '';
                break;
                case 'upload' :
                  $ph = '<i class="material-icons">image</i>';
                break;
            }
            if ( skp_is_customizing() ) {
                return sprintf('<div class="sek-module-placeholder" title="%4$s" data-sek-input-type="%1$s" data-sek-input-id="%2$s">%3$s</div>', $input_type, $input_id, $ph, __('Click to edit', 'here') );
            } else {
                return $ph;
            }
        }



        /**
         * unfiltered version of get_edit_post_link() located in wp-includes/link-template.php
         * ( filtered by wp core when invoked in customize-preview )
         */
        function get_unfiltered_edit_post_link( $id = 0, $context = 'display' ) {
            if ( ! $post = get_post( $id ) )
              return;

            if ( 'revision' === $post->post_type )
              $action = '';
            elseif ( 'display' == $context )
              $action = '&amp;action=edit';
            else
              $action = '&action=edit';

            $post_type_object = get_post_type_object( $post->post_type );
            if ( !$post_type_object )
              return;

            if ( !current_user_can( 'edit_post', $post->ID ) )
              return;

            if ( $post_type_object->_edit_link ) {
              $link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
            } else {
              $link = '';
            }
            return $link;
        }
        function sek_enqueue_the_printed_module_assets() {
            $skope_id = skp_get_skope_id();
            $skoped_seks = sek_get_skoped_seks( $skope_id );

            if ( ! is_array( $skoped_seks ) || empty( $skoped_seks['collection'] ) )
              return;

            $enqueueing_candidates = $this->sek_sniff_assets_to_enqueue( $skoped_seks['collection'] );

            foreach ( $enqueueing_candidates as $handle => $asset_params ) {
                if ( empty( $asset_params['type'] ) ) {
                    sek_error_log( __FUNCTION__ . ' => missing asset type', $asset_params );
                    continue;
                }
                switch ( $asset_params['type'] ) {
                    case 'css' :
                        wp_enqueue_style(
                            $handle,
                            array_key_exists( 'src', $asset_params ) ? $asset_params['src'] : null,
                            array_key_exists( 'deps', $asset_params ) ? $asset_params['deps'] : array(),
                            NIMBLE_ASSETS_VERSION,
                            'all'
                        );
                    break;
                    case 'js' :
                        wp_enqueue_script(
                            $handle,
                            array_key_exists( 'src', $asset_params ) ? $asset_params['src'] : null,
                            array_key_exists( 'deps', $asset_params ) ? $asset_params['deps'] : null,
                            array_key_exists( 'ver', $asset_params ) ? $asset_params['ver'] : null,
                            array_key_exists( 'in_footer', $asset_params ) ? $asset_params['in_footer'] : false
                        );
                    break;
                }
            }
        }//sek_enqueue_the_printed_module_assets()
        function sek_sniff_assets_to_enqueue( $collection, $enqueuing_candidates = array() ) {
            foreach ( $collection as $level_data ) {
                if ( array_key_exists( 'level', $level_data ) && 'module' === $level_data['level'] && ! empty( $level_data['module_type'] ) ) {
                    $front_assets = sek_get_registered_module_type_property( $level_data['module_type'], 'front_assets' );
                    if ( is_array( $front_assets ) ) {
                        foreach ( $front_assets as $handle => $asset_params ) {
                            if ( is_string( $handle ) && ! array_key_exists( $handle, $enqueuing_candidates ) ) {
                                $enqueuing_candidates[ $handle ] = $asset_params;
                            }
                        }
                    }
                } else {
                    if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
                        $enqueuing_candidates = $this -> sek_sniff_assets_to_enqueue( $level_data['collection'], $enqueuing_candidates );
                    }
                }
            }//foreach
            return $enqueuing_candidates;
        }
        function sek_maybe_set_local_nimble_template( $template ) {
            $locale_template = sek_get_locale_template();
            if ( !empty( $locale_template ) ) {
                $template = $locale_template;
            }
            return $template;
        }



        /* ------------------------------------------------------------------------- *
         *  SMART LOAD.
        /* ------------------------------------------------------------------------- */
        function sek_maybe_add_bg_attributes( $model ) {
            $attributes = '';
            $bg_url = '';
            $parallax_enabled = false;
            $width = '';
            $height = '';

            if ( !empty( $model[ 'options' ] ) && is_array( $model['options'] ) ) {
                $bg_options = ( ! empty( $model[ 'options' ][ 'bg' ] ) && is_array( $model[ 'options' ][ 'bg' ] ) ) ? $model[ 'options' ][ 'bg' ] : array();
                if ( ! empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
                    if ( sek_is_img_smartload_enabled() ) {
                        $bg_url = wp_get_attachment_url( $bg_options[ 'bg-image'] );
                    }
                    $parallax_enabled = !empty( $bg_options['bg-parallax'] ) && sek_booleanize_checkbox_val( $bg_options['bg-parallax'] );
                    if ( $parallax_enabled ) {
                        $image = wp_get_attachment_image_src( $bg_options[ 'bg-image'], 'full' );
                        if ( $image ) {
                            list( $src, $width, $height ) = $image;
                        }
                    }
                }
            }

            if ( ! empty( $bg_url ) ) {
                $attributes = sprintf('data-sek-lazy-bg="true" data-sek-src="%1$s"', $bg_url );
            }
            if ( $parallax_enabled ) {
                $attributes .= sprintf('%1$s data-sek-bg-parallax="true" data-bg-width="%2$s" data-bg-height="%3$s"', $attributes, $width, $height );
            }
            return $attributes;
        }
        function sek_maybe_process_img_for_js_smart_load( $html ) {
            if ( !sek_is_img_smartload_enabled() )
              return $html;
            if ( ! is_string( $html ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => provided html is not a string', $html );
                return $html;
            }
            if ( is_feed() || is_preview() )
                return $html;

            $allowed_image_extensions = apply_filters( 'nimble_smartload_allowed_img_extensions', array(
                'bmp',
                'gif',
                'jpeg',
                'jpg',
                'jpe',
                'tif',
                'tiff',
                'ico',
                'png',
                'svg',
                'svgz'
            ) );

            if ( empty( $allowed_image_extensions ) || ! is_array( $allowed_image_extensions ) ) {
              return $html;
            }

            $img_extensions_pattern = sprintf( "(?:%s)", implode( '|', $allowed_image_extensions ) );
            $pattern                = '#<img([^>]+?)src=[\'"]?([^\'"\s>]+\.'.$img_extensions_pattern.'[^\'"\s>]*)[\'"]?([^>]*)>#i';

            return preg_replace_callback( $pattern, '\Nimble\nimble_regex_callback', $html);
        }
        private function sek_setup_tiny_mce_content_filters() {
            if ( function_exists( 'do_blocks' ) ) {
                add_filter( 'the_nimble_tinymce_module_content', 'do_blocks', 9 );
            }
            add_filter( 'the_nimble_tinymce_module_content', 'wptexturize' );
            add_filter( 'the_nimble_tinymce_module_content', 'convert_smilies', 20 );
            add_filter( 'the_nimble_tinymce_module_content', 'wpautop' );
            add_filter( 'the_nimble_tinymce_module_content', 'shortcode_unautop' );
            add_filter( 'the_nimble_tinymce_module_content', 'prepend_attachment' );
            add_filter( 'the_nimble_tinymce_module_content', 'wp_make_content_images_responsive' );
            add_filter( 'the_nimble_tinymce_module_content', 'do_shortcode', 11 ); // AFTER wpautop()
            add_filter( 'the_nimble_tinymce_module_content', 'capital_P_dangit', 9 );
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_parse_content_for_video_embed') , 8 );
        }
        function sek_parse_content_for_video_embed( $content ) {
            if ( array_key_exists( 'wp_embed', $GLOBALS ) && $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
                return $GLOBALS['wp_embed']->autoembed( $content );
            } else {
                return $content;
            }
        }
    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Render_Css' ) ) :
    class SEK_Front_Render_Css extends SEK_Front_Render {
        function _setup_hook_for_front_css_printing_or_enqueuing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'print_or_enqueue_seks_style') );
        }
        function print_or_enqueue_seks_style( $skope_id = null ) {
            if ( ( ! is_null( $skope_id ) && ! empty( $skope_id ) ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                $this->_instantiate_css_handler( $skope_id );
            } else {
                $skope_id = skp_build_skope_id();
                $this->_instantiate_css_handler( skp_build_skope_id() );
                if ( sek_has_global_sections() ) {
                    $this->_instantiate_css_handler( NIMBLE_GLOBAL_SKOPE_ID );
                }
            }
            if ( empty( $skope_id ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . ' =>the skope_id should not be empty' );
            }
        }//print_or_enqueue_seks_style


        private function _instantiate_css_handler( $skope_id ) {
            new Sek_Dyn_CSS_Handler( array(
                'id'             => $skope_id,
                'skope_id'       => $skope_id,
                'mode'           => is_customize_preview() ? Sek_Dyn_CSS_Handler::MODE_INLINE : Sek_Dyn_CSS_Handler::MODE_FILE,
                'force_write'    => true, //<- write if the file doesn't exist
                'force_rewrite'  => is_user_logged_in() && current_user_can( 'customize' ), //<- write even if the file exists
                'hook'           => ( ! defined( 'DOING_AJAX' ) && is_customize_preview() ) ? 'wp_head' : ''
            ));
        }

    }//class
endif;

?><?php
if ( ! class_exists( '\Nimble\Sek_Simple_Form' ) ) :
class Sek_Simple_Form extends SEK_Front_Render_Css {

    private $form;
    private $fields;
    private $mailer;

    private $form_composition;

    function _setup_simple_forms() {
        add_action( 'parse_request', array( $this, 'simple_form_parse_request' ), 20 );
        $this->form_composition = array(
            'nimble_simple_cf'              => array(
                'type'            => 'hidden',
                'value'           => 'nimble_simple_cf'
            ),
            'nimble_skope_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_level_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_name' => array(
                'label'            => __( 'Name', 'text_domain_to_be_replaced' ),
                'required'         => true,
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_email' => array(
                'label'            => __( 'Email', 'text_domain_to_be_replaced' ),
                'required'         => true,
                'type'             => 'email',
                'wrapper_tag'      => 'div'
            ),
            'nimble_subject' => array(
                'label'            => __( 'Subject', 'text_domain_to_be_replaced' ),
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_message' => array(
                'label'            => __( 'Message', 'text_domain_to_be_replaced' ),
                'required'         => true,
                'additional_attrs' => array( 'rows' => "10", 'cols' => "50" ),
                'type'             => 'textarea',
                'wrapper_tag'      => 'div'
            ),
            'nimble_submit' => array(
                'type'             => 'submit',
                'value'            => __( 'Submit', 'text_domain_to_be_replaced' ),
                'additional_attrs' => array( 'class' => 'sek-btn' ),
                'wrapper_tag'      => 'div',
                'wrapper_class'    => array( 'sek-form-field', 'sek-form-btn-wrapper' )
            )
        );
    }//_setup_simple_forms
    function simple_form_parse_request() {
        if ( isset( $_POST['nimble_simple_cf'] ) ) {
            $module_model = array();
            if ( isset( $_POST['nimble_skope_id'] ) && '_skope_not_set_' !== $_POST['nimble_skope_id'] ) {
                $local_sektions = sek_get_skoped_seks( $_POST['nimble_skope_id'] );
                if ( is_array( $local_sektions ) && !empty( $local_sektions ) ) {
                $sektion_collection = array_key_exists('collection', $local_sektions) ? $local_sektions['collection'] : array();
                }
                if ( is_array($sektion_collection) && ! empty( $sektion_collection ) && isset( $_POST['nimble_level_id'] ) ) {
                    $module_model = sek_get_level_model($_POST['nimble_level_id'], $sektion_collection );
                    $module_model = sek_normalize_module_value_with_defaults( $module_model );
                }
            } else {
                sek_error_log( __FUNCTION__ . ' => skope_id problem');
                return;
            }

            if ( empty( $module_model ) ) {
                sek_error_log( __FUNCTION__ . ' => invalid module model');
                return;
            }
            foreach ( $this->form_composition as $name => $field ) {
                $form_composition[ $name ]                = $field;
                if ( isset( $_POST[ $name ] ) ) {
                    $form_composition[ $name ][ 'value' ] = $_POST[ $name ];
                }
            }
            $form_composition = $this->_set_form_composition( $form_composition, $module_model );
            $this->fields = $this->simple_form_generate_fields( $form_composition );
            $this->form   = $this->simple_form_generate_form( $this->fields );
            $this->mailer = new Sek_Mailer( $this-> form );

            $this->mailer->maybe_send( $form_composition, $module_model );
        }
    }
    function get_simple_form_html( $module_model ) {
        $html         = '';
        $form_composition = $this->_set_form_composition( $this->form_composition, $module_model );
        $fields       = isset( $this->fields ) ? $this->fields : $this->simple_form_generate_fields( $form_composition );
        $form         = isset( $this->form ) ? $this->form : $this->simple_form_generate_form( $fields );

        $module_id = is_array( $module_model ) && array_key_exists('id', $module_model ) ? $module_model['id'] : '';
        ob_start();
        ?>
        <div id="sek-form-respond">
          <?php
            $echo_form = true;
            if ( ! is_null( $this->mailer ) ) {
                if ( 'sent' == $status_code = $this->mailer->get_status() ) {
                    $echo_form = false;
                }
                ?>
                  <script type="text/javascript">
                      jQuery( function($) {
                          var $elToFocusOn = $('div[data-sek-id="<?php echo $module_id; ?>"]' );
                          if ( $elToFocusOn.length > 0 ) {
                                var _do = function() {
                                    $('html, body').animate({
                                        scrollTop : $elToFocusOn.offset().top - ( $(window).height() / 2 ) + ( $elToFocusOn.outerHeight() / 2 )
                                    }, 'slow');
                                };
                                try { _do(); } catch(er) {}
                          }
                      });
                  </script>
                <?php
                printf( '<span class="sek-form-message">%1$s</span>', $this->mailer->get_message( $status_code, $module_model ) );
            }

            if ( $echo_form ) {
                echo $form;
            }
          ?>
        </div>
        <?php
        return ob_get_clean();
    }
    private function _set_form_composition( $form_composition, $module_model = array() ) {

        $user_form_composition = array();
        if ( ! is_array( $module_model ) ) {
              sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => ERROR : invalid module options array');
              return $user_form_composition;
        }
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        $form_fields_options = empty( $module_user_values['form_fields'] ) ? array() : $module_user_values['form_fields'];
        $form_button_options = empty( $module_user_values['form_button'] ) ? array() : $module_user_values['form_button'];
        foreach ( $form_composition as $field_id => $field_data ) {
            switch ( $field_id ) {
                case 'nimble_name':
                    if ( ! empty( $form_fields_options['show_name_field'] ) && sek_is_checked( $form_fields_options['show_name_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['name_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['name_field_label'] );
                    }
                break;
                case 'nimble_subject':
                    if ( ! empty( $form_fields_options['show_subject_field'] ) && sek_is_checked( $form_fields_options['show_subject_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['subject_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['subject_field_label'] );
                    }
                break;
                case 'nimble_message':
                    if ( ! empty( $form_fields_options['show_message_field'] ) && sek_is_checked( $form_fields_options['show_message_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['message_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['message_field_label'] );
                    }
                break;
                case 'nimble_email':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['email_field_label'] );
                break;
                case 'nimble_submit':
                    $user_form_composition[$field_id] = $field_data;
                    $visual_effect_class = '';
                    if ( array_key_exists( 'use_box_shadow', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['use_box_shadow'] ) ) {
                        $visual_effect_class = ' box-shadow';
                        if ( array_key_exists( 'push_effect', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['push_effect'] ) ) {
                            $visual_effect_class .= ' push-effect';
                        }
                    }
                    $user_form_composition[$field_id]['additional_attrs']['class'] = 'sek-btn' . $visual_effect_class;
                    $user_form_composition[$field_id]['value'] = esc_attr( $form_fields_options['button_text'] );
                break;
                case 'nimble_skope_id':
                    $user_form_composition[$field_id] = $field_data;
                    $skope_id = '';
                    if ( ! skp_is_customizing() ) {
                        $skope_id = isset( $_POST['nimble_skope_id'] ) ? $_POST['nimble_skope_id'] : sek_get_level_skope_id( $module_model['id'] );
                    }
                    $user_form_composition[$field_id]['value'] = $skope_id;
                break;
                case 'nimble_level_id':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['value'] = $module_model['id'];
                break;
                default:
                    $user_form_composition[$field_id] = $field_data;
                break;
            }

        }
        return $user_form_composition;
    }
    function simple_form_generate_fields( $form_composition = array() ) {
        $form_composition = empty( $form_composition ) ? $this->form_composition : $form_composition;
        $fields_ = array();
        $id_suffix = rand();
        foreach ( $form_composition as $name => $field ) {
            $field = wp_parse_args( $field, array( 'type' => 'text' ) );

            if ( class_exists( $class = '\Nimble\Sek_Input_' . ucfirst( $field['type'] ) ) ) {
                $fields_ [] = new Sek_Field (
                    new $class( array_merge( array( 'id_suffix'=> $id_suffix ), $field, array( 'name' => $name ) ) ),
                    $field
                );
            }
        }

        return $fields_;
    }
    function simple_form_generate_form( $fields ) {
        $form   = new Sek_Form();
        $form->add_fields( $fields );

        return $form;
    }
}//Sek_Simple_Form
endif;











/*
*
* Form class definition
*
*/
if ( ! class_exists( '\Nimble\Sek_Form' ) ) :
class Sek_Form {
    private $fields;
    private $attributes;

    public function __construct( $args = array() ) {
        $this->fields        = array();
        $this->attributes    = wp_parse_args( $args, array(
            'action' => get_the_permalink(),
            'method' => 'post'
        ) );
    }

    public function add_field( Sek_Field $field ) {
        $this->fields[ sanitize_key( $field->get_input()->get_data('name') ) ] = $field;
    }

    public function add_fields( array $fields ) {
        foreach ($fields as $field) {
            $this->add_field( $field );
        }
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_field( $field_name ) {
        return $this->fields[ sanitize_key( $field_name ) ] ? $this->fields[ sanitize_key( $field_name ) ] : null;
    }
    public function has_invalid_field() {
        $has_invalid_field = false;

        foreach ( $this->fields as $form_field ) {
            if ( false !== $has_invalid_field )
              continue;
            $input        = $form_field->get_input();
            $value        = $input->get_value();
            $filter       = $input->get_data( 'filter' );
            $can_be_empty = true !== $input->get_data( 'required' );

            if ( $can_be_empty && ! $value ) {
                continue;
            }
            if ( $filter && ! filter_var( $value, $filter ) ) {
                $has_invalid_field = $input->get_data('label');
                break;
            }
        }

        return $has_invalid_field;
    }

    public function get_attributes_html() {
        return implode( ' ', array_map(
            function ($k, $v) {
                return sanitize_key( $k ) .'="'. esc_attr( $v ) .'"';
            },
            array_keys( $this->attributes ), $this->attributes
        ) );
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $fields = '';

        foreach ($this->fields as $name => $field) {
            $fields .= $field;
        }

        return sprintf('<form %1$s>%2$s</form>',
            $this->get_attributes_html(),
            $fields
        );
    }
}//Sek_Form
endif;











/*
* Field class definition
*
* label and/or wrapper + input field
*/
if ( ! class_exists( '\Nimble\Sek_Field' ) ) :
class Sek_Field {
    private $input;
    private $data;

    public function __construct( Sek_Input_Interface $input, $args = array() ) {
        $this->input = $input;

        $this->data  = wp_parse_args( $args, [
            'wrapper_tag'         => '',
            'wrapper_class'       => array( 'sek-form-field' ),
            'label'               => '',
            'before_label'        => '',
            'after_label'         => '',
            'before_input'        => '',
            'after_input'         => '',
        ]);
    }

    public function get_input() {
        return $this->input;
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $label = $this->data[ 'label' ];
        if ( $label ) {
            if ( true == $this->input->get_data( 'required' ) ) {
                $label .= ' *';
            }
            $label = sprintf( '%1$s<label for="%2$s">%3$s</label>%4$s',
                $this->data[ 'before_label' ],
                esc_attr( $this->input->get_data( 'id' ) ),
                esc_html($label),
                $this->data[ 'after_label' ]
            );
        }
        $html = sprintf( '%s%s%s%s',
            $label,
            $this->data[ 'before_input' ],
            $this->input,
            $this->data[ 'after_input' ]
        );
        if ( $this->data[ 'wrapper_tag' ] ) {
            $wrapper_tag   = tag_escape( $this->data[ 'wrapper_tag' ] );
            $wrapper_class = $this->data[ 'wrapper_class' ] ? ' class="'. implode( ' ', array_map('sanitize_html_class', $this->data[ 'wrapper_class' ] ) ) .'"' : '';

            $html = sprintf( '<%1$s%2$s>%3$s</%1$s>',
                $wrapper_tag,
                $wrapper_class,
                $html
            );
        }

        return $html;
    }
}
endif;





















/*
*
* Input objects definition
*
*/
interface Sek_Input_Interface {
    public function sanitize( $value );
    public function escape( $value );
    public function get_value();
    public function set_value( $value );
    public function get_data( $data_key );
    public function html();
}

abstract class Sek_Input_Abstract implements Sek_Input_Interface {
    private $data;
    protected $attributes = array( 'id', 'name', 'required' );

    public function __construct( $args ) {
        if ( ! isset( $args['name'] ) ) {
            error_log( __FUNCTION__ . ' => contact form input name not set' );
            return;
        }

        $defaults = array(
            'name'               => '',
            'id'                 => '',
            'id_suffix'          => null,
            'additional_attrs'   => array(),
            'sanitize_cb'        => array( $this, 'sanitize' ),
            'escape_cb'          => array( $this, 'escape' ),
            'required'           => false,
            'filter'             => '',
            'value'              => ''
        );

        $data = wp_parse_args( $args, $defaults );


        $data[ 'id_suffix' ]        = is_null( $data[ 'id_suffix' ] ) ? rand() : $data[ 'id_suffix' ];
        $data[ 'id' ]               = empty( $data[ 'id' ] ) ? $data[ 'name' ] : $data[ 'id' ];
        $data[ 'id' ]               = $data[ 'id' ] . $data[ 'id_suffix' ];
        $data[ 'additional_attrs' ] = is_array( $data[ 'additional_attrs' ] ) ? $data[ 'additional_attrs' ] : array();

        $this->data = $data;

        if ( $data[ 'value' ] ) {
            $this->set_value( $data[ 'value' ]  );
        }

    }

    public function sanitize( $value ) {
        return $value;
    }

    public function escape( $value ) {
        return esc_attr( $value );
    }


    public function get_value() {
        $data = (array)$this->data;
        $value = $this->data['escape_cb']( $data['value'] );
        if ( skp_is_customizing() ) {
            $field_name = $this->get_data('name');
            switch( $field_name ) {
                case 'nimble_name' :
                    $value = __('John Doe', 'text-domain');
                break;
                case 'nimble_email' :
                    $value = __('john@doe.com', 'text-domain');
                break;
                case 'nimble_subject' :
                    $value = __('An email subject', 'text-domain');
                break;
            }
        }
        return $value;
    }


    public function set_value( $value ) {
        $this->data['value'] = $this->data['sanitize_cb']( $value );
    }

    public function get_data( $data_key ){
        return isset( $this->data[ $data_key ] ) ? $this->data[ $data_key ] : null;
    }

    public function get_attributes_html() {
        $attributes = array_merge(
            array_intersect_key(
                array_filter( $this->data ),
                array_flip( $this->attributes )
            ),
            $this->data[ 'additional_attrs' ]
        );
        if ( skp_is_customizing() ) {
            $attributes['value'] = array_key_exists('value', $attributes ) ? $attributes['value'] : '';
        }
        $attributes = array_map(
            function ($k, $v) {
                $v     =  'value' == $k ? $this->get_value() : esc_attr( $v );
                return sanitize_key( $k ) .'="'. $v .'"';
            },
            array_keys($attributes), $attributes
        );

        return implode( ' ', $attributes );
    }


    public function __toString() {
        return $this->html();
    }

}//end abstract class









if ( ! class_exists( '\Nimble\Sek_Input_Basic' ) ) :
class Sek_Input_Basic extends Sek_Input_Abstract {

    public function __construct( $args ) {
        $this->attributes   = array_merge( $this->attributes, array( 'value', 'type' ) );
        parent::__construct( $args );
    }

    public function html() {
        return sprintf( '<input %s/>',
            $this->get_attributes_html()
        );
    }
}
endif;

if ( ! class_exists( '\Nimble\Sek_Input_Hidden' ) ) :
class Sek_Input_Hidden extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args[ 'type' ]     = 'hidden';
        parent::__construct( $args );
    }
}
endif;

if ( ! class_exists( '\Nimble\Sek_Input_Text' ) ) :
class Sek_Input_Text extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args               = is_array( $args ) ? $args : array();
        $args[ 'type' ]     = 'text';
        $args[ 'filter' ]   = FILTER_SANITIZE_STRING;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return sanitize_text_field( $value );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_Email' ) ) :
class Sek_Input_Email extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'text';
        $args[ 'filter' ] = FILTER_SANITIZE_EMAIL;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        if ( ! is_email( $value ) ) {
            return '';
        }
        return sanitize_email($value);
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_URL' ) ) :
class Sek_Input_URL extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'url';
        $args[ 'filter' ] = FILTER_SANITIZE_URL;

        parent::__construct( $args );
    }


    public function sanitize($value) {
        return esc_url_raw( $value );
    }

    public function escape( $value ){
        return esc_url( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_Submit' ) ) :
class Sek_Input_Submit extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();

        $args[ 'type' ]   = 'submit';
        $args             = wp_parse_args($args, [
            'value' => esc_html__( 'Contact', 'text_domain_to_be_replaced' ),
        ]);

        parent::__construct( $args );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;



if ( ! class_exists( '\Nimble\Sek_Input_Textarea' ) ) :
class Sek_Input_Textarea extends Sek_Input_Abstract {

    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'textarea';

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return wp_kses_post($value);
    }

    public function escape( $value ) {
        return $this->sanitize( $value );
    }


    public function html() {
        return sprintf( '<textarea %1$s/>%2$s</textarea>',
            $this->get_attributes_html(),
            $this->get_value()
        );
    }
}
endif;






















/*
*
* Mailer class definition
*
*/
if ( ! class_exists( '\Nimble\Sek_Mailer' ) ) :
class Sek_Mailer {
    private $form;
    private $status;
    private $messages;
    private $invalid_field = false;

    public function __construct( Sek_Form $form ) {
        $this-> form = $form;

        $this->messages = array(
            'not_sent'        => __( 'Message was not sent. Try Again.', 'text_domain_to_be_replaced'),
            'sent'            => __( 'Thanks! Your message has been sent.', 'text_domain_to_be_replaced'),
            'aborted'         => __( 'Please supply correct information.', 'text_domain_to_be_replaced'), //<-todo too much generic
        );

        $this->status = 'init';
    }



    public function maybe_send( $form_composition, $module_model ) {
        $invalid_field = $this->form->has_invalid_field();
        if ( false !== $invalid_field ) {
            $this->status = 'aborted';
            $this->invalid_field = $invalid_field;
            return;
        }

        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];
        $allow_html     = true;

        $sender_email   = $this->form->get_field('nimble_email')->get_input()->get_value();
        $sender_name    = sprintf( '%1$s', $this->form->get_field('nimble_name')->get_input()->get_value() );

        if ( array_key_exists( 'recipients', $submission_options ) ) {
            $recipient      = $submission_options['recipients'];
        } else {
            $recipient      = get_option( 'admin_email' );
        }

        if ( array_key_exists( 'nimble_subject' , $form_composition ) ) {
            $subject = $this->form->get_field('nimble_subject')->get_input()->get_value();
        } else {
            $subject = sprintf( __( 'Someone sent a message from %1$s', 'text_domain_to_be_replaced' ), get_bloginfo( 'name' ) );
        }
        $before_message = sprintf( '%1$s: %2$s &lt;%3$s&gt;', __('From', 'text_domain_to_be_replaced'), $sender_name, $sender_email );//$sender_website;
        $before_message .= sprintf( '<br>%1$s: %2$s', __('Subject', 'text_domain_to_be_replaced'), $subject );
        $after_message  = '';

        if ( array_key_exists( 'email_footer', $submission_options ) ) {
            $email_footer = $submission_options['email_footer'];
        } else {
            $email_footer = sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_domain_to_be_replaced' ),
                get_bloginfo( 'name' ),
                get_site_url( 'url' )
            );
        }

        $body           = sprintf( '%1$s%2$s%3$s%4$s%5$s',
                            $before_message,
                            sprintf( '<br><br>%1$s: <br>%2$s',
                                __('Message body', 'text_domain_to_be_replaced'),
                                $this->form->get_field('nimble_message')->get_input()->get_value()
                            ),
                            $after_message,
                            $allow_html ? '<br><br>--<br>': "\r\n\r\n--\r\n",
                            $email_footer
        );

        $headers        = array();
        $headers[]      = $allow_html ? 'Content-Type: text/html' : '';
        $headers[]      = 'charset=UTF-8'; //TODO: maybe turn into option

        $headers[]      = sprintf( 'From: %1$s <%2$s>', $sender_name, $this->get_from_email() );
        $headers[]      = sprintf( 'Reply-To: %1$s <%2$s>', $sender_name, $sender_email );

        $this->status   = wp_mail( $recipient, $subject, $body, $headers ) ? 'sent' : 'not_sent';
    }



    public function get_status() {
        return $this->status;
    }


    public function get_message( $status, $module_model ) {
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        $submission_message = isset( $this->messages[ $status ] ) ? $this->messages[ $status ] : '';
        switch( $status ) {
            case 'not_sent' :
                if ( array_key_exists( 'failure_message', $submission_options ) && !empty( $submission_options['failure_message'] ) ) {
                    $submission_message = $submission_options['failure_message'];
                }
            break;
            case 'sent' :
                if ( array_key_exists( 'success_message', $submission_options ) && !empty( $submission_options['success_message'] ) ) {
                    $submission_message = $submission_options['success_message'];
                }
            break;
            case 'aborted' :
                if ( array_key_exists( 'error_message', $submission_options ) && !empty( $submission_options['error_message'] ) ) {
                    $submission_message = $submission_options['error_message'];
                }
                if ( false !== $this->invalid_field ) {
                    $submission_message = sprintf( __( '%1$s The following field is not well formed : <strong>%2$s</strong>.', 'text-domain' ), $submission_message, $this->invalid_field );
                }
            break;
        }
        return $submission_message;
    }



    /*
    * inspired from wpcf7
    */
    private function get_from_email() {
        $admin_email = get_option( 'admin_email' );
        $sitename    = strtolower( $_SERVER['SERVER_NAME'] );

        if ( in_array( $sitename, array( 'localhost', '127.0.0.1' ) ) ) {
            return $admin_email;
        }

        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }

        if ( strpbrk( $admin_email, '@' ) == '@' . $sitename ) {
            return $admin_email;
        }

        return 'wordpress@' . $sitename;
    }
}//Sek_Mailer
endif;











/*
* inspired by wpcf7
*/
function simple_form_mail_template() {
    $template = array(
        'subject' =>
            sprintf( __( '%1$s: new contact request', 'text_domain_to_be_replaced' ),
                get_bloginfo( 'name' )
            ),
        'sender' => sprintf( '[your-name] <%s>', simple_form_from_email() ),
        'body' =>
            /* translators: %s: [your-name] <[your-email]> */
            sprintf( __( 'From: %s', 'text_domain_to_be_replaced' ),
                '[your-name] <[your-email]>' ) . "\n"
            /* translators: %s: [your-subject] */
            . sprintf( __( 'Subject: %s', 'text_domain_to_be_replaced' ),
                '[your-subject]' ) . "\n\n"
            . __( 'Message Body:', 'text_domain_to_be_replaced' )
                . "\n" . '[your-message]' . "\n\n"
            . '-- ' . "\n"
            /* translators: 1: blog name, 2: blog URL */
            . sprintf(
                __( 'This e-mail was sent from a contact form on %1$s (%2$s)', 'text_domain_to_be_replaced' ),
                get_bloginfo( 'name' ),
                get_bloginfo( 'url' ) ),
        'recipient' => get_option( 'admin_email' ),
        'additional_headers' => 'Reply-To: [your-email]',
        'attachments' => '',
        'use_html' => 0,
        'exclude_blank' => 0,
    );

    return $template;
}//simple_form_mail_template


?><?php

if ( ! class_exists( '\Nimble\Sek_Nimble_Manager' ) ) :
  final class Sek_Nimble_Manager extends Sek_Simple_Form {}
endif;

function Nimble_Manager( $params = array() ) {
    return Sek_Nimble_Manager::get_instance( $params );
}

Nimble_Manager();
?>