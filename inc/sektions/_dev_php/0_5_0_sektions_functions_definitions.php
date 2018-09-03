<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'NIMBLE_CPT' ) ) { define( 'NIMBLE_CPT' , 'nimble_post_type' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'nimble___' ); }
if ( ! defined( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' , '__nimble_options__' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' , '__nimble__' ); }

// @return array
function sek_get_locations() {
  return apply_filters( 'sek_locations', array_merge( SEK_Front()->default_locations, SEK_Front()->registered_locations ) );
}

function register_location( $location ) {
    $registered_locations = SEK_Front()->registered_locations;
    if ( is_array( $registered_locations ) ) {
        $registered_locations[] = $location;
    }
    SEK_Front()->registered_locations = $registered_locations;
}

// @return array
// @used when populating the customizer localized params
function sek_get_default_sektions_value() {
    $defaut_sektions_value = [ 'collection' => [], 'options' => [] ];
    foreach( sek_get_locations() as $location ) {
        $defaut_sektions_value['collection'][] = wp_parse_args( [ 'id' => $location ], SEK_Front()->default_location_model );
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
 *  REGISTERED MODULES => DEFAULT MODULE MODEL
/* ------------------------------------------------------------------------- */
// @param (string) module_type
// Walk the registered modules tree and generates the module default if not already cached
// used :
// - when preprocessing the module model before printing the module template. @seeSEL_Front::render()
// - when setting the level option css. @see sek_add_css_rules_for_bg_border_background()
// @return array()
function sek_get_default_module_model( $module_type = '' ) {
    $default = array();
    if ( empty( $module_type ) || is_null( $module_type ) )
      return $default;

    // Did we already cache it ?
    $default_models = SEK_Front()->default_models;
    if ( ! empty( $default_models[ $module_type ] ) ) {
        $default = $default_models[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base() -> registered_modules;
        // sek_error_log( __FUNCTION__ . ' => registered_modules', $registered_modules );
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $default;
        }

        if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the default model.' );
            return $default;
        }
        // Build
        $default = _sek_build_default_model( $registered_modules[ $module_type ][ 'tmpl' ] );

        // Cache
        $default_models[ $module_type ] = $default;
        SEK_Front()->default_models = $default_models;
        // sek_error_log( __FUNCTION__ . ' => $default_models', $default_models );
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
    $cached_input_lists = SEK_Front()->cached_input_lists;
    if ( ! empty( $cached_input_lists[ $module_type ] ) ) {
        $input_list = $cached_input_lists[ $module_type ];
    } else {
        $registered_modules = CZR_Fmk_Base() -> registered_modules;
        // sek_error_log( __FUNCTION__ . ' => registered_modules', $registered_modules );
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
            return $input_list;
        }

        if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the default model.' );
            return $input_list;
        }
        // Build
        $input_list = _sek_build_input_list( $registered_modules[ $module_type ][ 'tmpl' ] );

        // Cache
        $cached_input_lists[ $module_type ] = $input_list;
        SEK_Front()->cached_input_lists = $cached_input_lists;
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
    //error_log( print_r(  $module_tmpl_data , true ) );
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
 *  used before rendering or generating css
/* ------------------------------------------------------------------------- */
function sek_normalize_module_value_with_defaults( $raw_module_model ) {
    $normalized_model = $raw_module_model;
    $module_type = $normalized_model['module_type'];
    $default_value_model  = sek_get_default_module_model( $module_type );//<= walk the registered modules tree and generates the module default if not already cached
    $raw_module_value = ( ! empty( $raw_module_model['value'] ) && is_array( $raw_module_model['value'] ) ) ? $raw_module_model['value'] : array();

    // reset the model value and rewrite it normalized with the defaults
    $normalized_model['value'] = array();
    if ( czr_is_multi_item_module( $module_type ) ) {
        foreach ( $raw_module_value as $item ) {
            $normalized_model['value'][] = wp_parse_args( $item, $default_value_model );
        }
    } else {
        $normalized_model['value'] = wp_parse_args( $raw_module_value, $default_value_model );
    }

    return $normalized_model;
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
    $localSkopeNimble = sek_get_skoped_seks( skp_build_skope_id() );
    if ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['options']) && ! empty( $localSkopeNimble['options']['general'] ) && ! empty( $localSkopeNimble['options']['general']['local_template'] ) && 'default' !== $localSkopeNimble['options']['general']['local_template'] ) {
        $path = NIMBLE_BASE_PATH . "/tmpl/page-templates/" . $localSkopeNimble['options']['general']['local_template'] . '.php';
        if ( file_exists( $path ) ) {
            $template = $path;
        } else {
            sek_error_log( __FUNCTION__ .' the custom template does not exist', $path );
        }
    }
    return $path;
}

//@return void()
function render_content_sections() {
    foreach( sek_get_locations() as $hook ) {
        do_action( "sek_before_location_{$hook}" );
        SEK_Front()->_render_seks_for_location( $hook );
        do_action( "sek_after_location_{$hook}" );
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
?>