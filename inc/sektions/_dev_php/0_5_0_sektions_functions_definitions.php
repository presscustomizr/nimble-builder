<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'SEK_CPT' ) ) { define( 'SEK_CPT' , 'sek_post_type' ); }
if ( ! defined( 'SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'sek___' ); }
if ( ! defined( 'SEK_OPT_PREFIX_FOR_SEKTIONS_NOT_SAVED' ) ) { define( 'SEK_OPT_PREFIX_FOR_SEKTIONS_NOT_SAVED' , '__sek__' ); }

// @return array
function sek_get_locations() {
  return apply_filters( 'sek_locations', [
      'loop_start',
      'loop_end',
      'before_content',
      'after_content'
  ] );
}

// @return array
// @used when buiding the customizer localized params
function sek_get_default_sektions_value() {
    $defaut_sektions_value = [ 'collection' => [], 'options' => [] ];
    foreach( sek_get_locations() as $location ) {
        $defaut_sektions_value['collection'][] = [
            'id' => $location,
            'level' => 'location',
            'collection' => [],
            'options' => []
        ];
    }
    return $defaut_sektions_value;
}

//@return string
function sek_get_seks_setting_id( $skope_id = '' ) {
  if ( empty( $skope_id ) ) {
      error_log( 'sek_get_seks_setting_id => empty skope id or location => collection setting id impossible to build' );
  }
  return SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION . "[{$skope_id}]";
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



// Helper
function sek_get_registered_module_type_property( $module_type, $property = '' ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    // registered modules
    $registered_modules = $CZR_Fmk_Base_fn() -> registered_modules;
    if ( ! array_key_exists( $module_type, $registered_modules ) ) {
        error_log( __FUNCTION__ . ' => ' . $module_type . ' not registered.' );
        return;
    }
    if ( array_key_exists( $property , $registered_modules[ $module_type ] ) ) {
        return $registered_modules[ $module_type ][$property];
    }
    return;
}

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
function sek_get_parent_level_model( $child_level_id, $collection = array(), $skope_id = '' ) {
    if ( empty( $collection ) ) {
        if ( empty( $skope_id ) ) {
            $skope_id = skp_get_skope_id( $skope_level );
        }
        $collection = sek_get_skoped_seks( $skope_id );
    }
    $_parent_level_data = 'no_match';
    foreach ( $collection as $level_data ) {
        // stop here and return if a match was recursively found
        if ( 'no_match' != $_parent_level_data )
          break;
        if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            foreach ( $level_data['collection'] as $child_level_data ) {
                if ( $child_level_id == $child_level_data['id'] ) {
                    $_parent_level_data = $level_data;
                    //match found, break this loop
                    break;
                } else {
                    $_parent_level_data = sek_get_parent_level_model( $child_level_id, $level_data['collection'] );
                }
            }
        }
    }
    return $_parent_level_data;
}








// @param module_type
// walk the registered modules tree and generates the module default if not already cached
// @return array;
function sek_get_default_module_model( $module_type = '' ) {
    $default = array();
    if ( empty( $module_type ) || is_null( $module_type ) )
      return $default;

    // Did we already cache it ?
    $default_models = SEK_Front()->default_models;
    if ( ! empty( $default_models[ $module_type ] ) ) {
        $default = $default_models[ $module_type ];
    } else {
        $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
        //czr_fn\czr_register_dynamic_module
        $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
        if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
            error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
            return array();
        }
        $registered_modules = $CZR_Fmk_Base_fn() -> registered_modules;

        // error_log('<registered_modules>');
        // error_log( print_r( $registered_modules, true ) );
        // error_log('</registered_modules>');
        if ( ! array( $registered_modules ) || ! array_key_exists( $module_type, $registered_modules ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' is not registered in the $CZR_Fmk_Base_fn()->registered_modules;' );
        }

        if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the default model.' );
        }
        // Build
        $default = _sek_build_default_model( $registered_modules[ $module_type ][ 'tmpl' ] );

        // Cache
        $default_models[ $module_type ] = $default;
        SEK_Front()->default_models = $default_models;
        // error_log('<$default_models>');
        // error_log( print_r( $default_models, true ) );
        // error_log('</$default_models>');
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
                //                 'refresh-markup' => false,
                //                 'refresh-stylesheet' => true
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















/* HELPER FOR CHECKBOX OPTIONS */
function sek_is_checked( $val ) {
    //cast to string if array
    $val = is_array($val) ? $val[0] : $val;
    return sek_booleanize_checkbox_val( $val );
}

function sek_booleanize_checkbox_val( $val ) {
    if ( ! $val )
      return false;
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
        error_log( '<' . strtoupper( $title ) . '>' );
    } else {
        error_log( '<' . strtoupper( $title ) . '>' );
        error_log( print_r( $content, true ) );
        error_log( '<' . strtoupper( $title ) . '>' );
    }
}
?>