<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! defined( 'NIMBLE_CPT' ) ) { define( 'NIMBLE_CPT' , 'nimble_post_type' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'nimble___' ); }
if ( ! defined( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_LEVEL_UI' , '__nimble__' ); }

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
        }

        if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the default model.' );
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
        }

        if ( empty( $registered_modules[ $module_type ][ 'tmpl' ] ) ) {
            error_log( __FUNCTION__ . ' => ' . $module_type . ' => missing "tmpl" property => impossible to build the default model.' );
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
?><?php
// SEKTION POST
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
) );







/**
 * Fetch the `nimble_post_type` post for a given {skope_id}
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A theme object stylesheet name. Defaults to the current theme.
 * @return WP_Post|null The skope post or null if none exists.
 */
function sek_get_seks_post( $skope_id = '', $skope_level = 'local' ) {
    //sek_error_log('skope_id in sek_get_seks_post => ' . $skope_id );
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
    // if the options has not been set yet, it will return (int) 0
    // id #1 is already taken by the 'Hello World' post.
    if ( 1 > $post_id ) {
        //error_log( 'sek_get_seks_post => post_id is not valid for options => ' . $option_name );
        return;
    }

    if ( ! is_int( $post_id ) ) {
        error_log( 'sek_get_seks_post => post_id ! is_int() for options => ' . $option_name );
    }

    if ( is_int( $post_id ) && $post_id > 0 && get_post( $post_id ) ) {
        $post = get_post( $post_id );
    }

    // `-1` indicates no post exists; no query necessary.
    if ( ! $post && -1 !== $post_id ) {
        $query = new WP_Query( $sek_post_query_vars );
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
function sek_get_skoped_seks( $skope_id = '', $location = '', $skope_level = 'local' ) {
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }
    // use the cached value when NOT in a customization scenario
    if ( ! skp_is_customizing() && did_action('wp') && 'not_cached' != SEK_Front()->local_seks ) {
        $seks_data = SEK_Front()->local_seks;
    } else {
        $seks_data = array();
        $post = sek_get_seks_post( $skope_id );
        // sek_error_log( 'sek_get_skoped_seks() => $post', $post);
        if ( $post ) {
            $seks_data = maybe_unserialize( $post->post_content );
        }
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        // cache now
        SEK_Front()->local_seks = $seks_data;
    }

    // when customizing, let us filter the value with the 'customized' ones
    $seks_data = apply_filters(
        'sek_get_skoped_seks',
        $seks_data,
        $skope_id,
        $location
    );

    // normalizes
    $seks_data = wp_parse_args( $seks_data, sek_get_default_sektions_value() );

    // sek_error_log( '<sek_get_skoped_seks() location => ' . $location .  array_key_exists( 'collection', $seks_data ), $seks_data );
    // if a location is specified, return specifically the sections of this location
    if ( array_key_exists( 'collection', $seks_data ) && ! empty( $location ) ) {
        if ( ! in_array( $location, sek_get_locations() ) ) {
            error_log('Error => location ' . $location . ' is not registered in the available locations' );
        } else {
            $seks_data = sek_get_level_model( $location, $seks_data['collection'] );
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

    if ( ! is_array( $seks_data ) ) {
        error_log( 'sek_update_sek_post => $seks_data is not an array' );
        return new WP_Error( 'sek_update_sek_post => $seks_data is not an array');
    }

    $skope_id = $args['skope_id'];
    if ( empty( $skope_id ) ) {
        error_log( 'sek_update_sek_post => empty skope_id' );
        return new WP_Error( 'sek_update_sek_post => empty skope_id');
    }

    $post_title = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;

    $post_data = array(
        'post_title' => $post_title,
        'post_name' => sanitize_title( $post_title ),
        'post_type' => NIMBLE_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $seks_data )
    );

    // Update post if it already exists, otherwise create a new one.
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
// TINY MCE EDITOR
require_once(  dirname( __FILE__ ) . '/customizer/seks_tiny_mce_editor_actions.php' );

// ENQUEUE CUSTOMIZER JAVASCRIPT + PRINT LOCALIZED DATA
add_action ( 'customize_controls_enqueue_scripts', '\Nimble\sek_enqueue_controls_js_css', 20 );
function sek_enqueue_controls_js_css() {
    wp_enqueue_style(
        'sek-control',
        sprintf(
            '%1$s/assets/czr/sek/css/%2$s' ,
            NIMBLE_BASE_URL,
            defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'sek-control.css' : 'sek-control.min.css'
        ),
        array(),
        NIMBLE_ASSETS_VERSION,
        'all'
    );


    wp_enqueue_script(
        'czr-sektions',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/%2$s' ,
            NIMBLE_BASE_URL,
            defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'ccat-sek-control.js' : 'ccat-sek-control.min.js'
        ),
        array( 'czr-skope-base' , 'jquery', 'underscore' ),
        NIMBLE_ASSETS_VERSION,
        $in_footer = true
    );

    wp_enqueue_script(
        'czr-color-picker',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/libs/%2$s' ,
            NIMBLE_BASE_URL,
            defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'czr-color-picker.js' : 'czr-color-picker.min.js'
        ),
        array( 'jquery' ),
        NIMBLE_ASSETS_VERSION,
        $in_footer = true
    );

    wp_localize_script(
        'czr-sektions',
        'sektionsLocalizedData',
        apply_filters( 'nimble-sek-localized-customizer-control-params',
            array(
                'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('NIMBLE_DEV') && true === NIMBLE_DEV ),
                'baseUrl' => NIMBLE_BASE_URL,
                'sektionsPanelId' => '__sektions__',
                'addNewSektionId' => 'sek_add_new_sektion',
                'addNewColumnId' => 'sek_add_new_column',
                'addNewModuleId' => 'sek_add_new_module',

                'optPrefixForSektionSetting' => NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION,//'nimble___'
                'optPrefixForSektionsNotSaved' => NIMBLE_OPT_PREFIX_FOR_LEVEL_UI,//"__nimble__"

                'defaultSektionSettingValue' => sek_get_default_sektions_value(),

                'presetSections' => sek_get_preset_sektions(),

                'registeredModules' => CZR_Fmk_Base() -> registered_modules,

                // Dnd
                'preDropElementClass' => 'sortable-placeholder',
                'dropSelectors' => implode(',', [
                    // 'module' type
                    //'.sek-module-drop-zone-for-first-module',//the drop zone when there's no module or nested sektion in the column
                    //'[data-sek-level="location"]',
                    //'.sek-not-empty-col',// the drop zone when there is at least one module
                    //'.sek-column > .sek-column-inner sek-section',// the drop zone when there is at least one nested section
                    //'.sek-content-module-drop-zone',//between sections
                    '.sek-drop-zone', //This is the selector for all eligible drop zones printed statically or dynamically on dragstart
                    'body',// body will not be eligible for drop, but setting the body as drop zone allows us to fire dragenter / dragover actions, like toggling the "approaching" or "close" css class to real drop zone

                    // 'preset_section' type
                    '.sek-content-preset_section-drop-zone'//between sections
                ])
            )
        )
    );//wp_localize_script()

    nimble_enqueue_code_editor();
}//sek_enqueue_controls_js_css()





/**
 * Enqueue all code editor assets
 */
function nimble_enqueue_code_editor() {
    wp_enqueue_script( 'code-editor' );
    wp_enqueue_style( 'code-editor' );

    wp_enqueue_script( 'csslint' );
    wp_enqueue_script( 'htmlhint' );
    wp_enqueue_script( 'csslint' );
    wp_enqueue_script( 'jshint' );
    wp_enqueue_script( 'htmlhint-kses' );
    wp_enqueue_script( 'jshint' );
    wp_enqueue_script( 'jsonlint' );
}



/**
 * Enqueue assets needed by the code editor for the given settings.
 *
 * @param array $args {
 *     Args.
 *
 *     @type string   $type       The MIME type of the file to be edited.
 *     @type array    $codemirror Additional CodeMirror setting overrides.
 *     @type array    $csslint    CSSLint rule overrides.
 *     @type array    $jshint     JSHint rule overrides.
 *     @type array    $htmlhint   JSHint rule overrides.
 *     @returns array Settings for the enqueued code editor.
 * }
 */
function nimble_get_code_editor_settings( $args ) {
    $settings = array(
        'codemirror' => array(
            'indentUnit' => 2,
            'tabSize' => 2,
            'indentWithTabs' => true,
            'inputStyle' => 'contenteditable',
            'lineNumbers' => true,
            'lineWrapping' => true,
            'styleActiveLine' => true,
            'continueComments' => true,
            'extraKeys' => array(
                'Ctrl-Space' => 'autocomplete',
                'Ctrl-/' => 'toggleComment',
                'Cmd-/' => 'toggleComment',
                'Alt-F' => 'findPersistent',
                'Ctrl-F'     => 'findPersistent',
                'Cmd-F'      => 'findPersistent',
            ),
            'direction' => 'ltr', // Code is shown in LTR even in RTL languages.
            'gutters' => array(),
        ),
        'csslint' => array(
            'errors' => true, // Parsing errors.
            'box-model' => true,
            'display-property-grouping' => true,
            'duplicate-properties' => true,
            'known-properties' => true,
            'outline-none' => true,
        ),
        'jshint' => array(
            // The following are copied from <https://github.com/WordPress/wordpress-develop/blob/4.8.1/.jshintrc>.
            'boss' => true,
            'curly' => true,
            'eqeqeq' => true,
            'eqnull' => true,
            'es3' => true,
            'expr' => true,
            'immed' => true,
            'noarg' => true,
            'nonbsp' => true,
            'onevar' => true,
            'quotmark' => 'single',
            'trailing' => true,
            'undef' => true,
            'unused' => true,

            'browser' => true,

            'globals' => array(
                '_' => false,
                'Backbone' => false,
                'jQuery' => false,
                'JSON' => false,
                'wp' => false,
            ),
        ),
        'htmlhint' => array(
            'tagname-lowercase' => true,
            'attr-lowercase' => true,
            'attr-value-double-quotes' => false,
            'doctype-first' => false,
            'tag-pair' => true,
            'spec-char-escape' => true,
            'id-unique' => true,
            'src-not-empty' => true,
            'attr-no-duplication' => true,
            'alt-require' => true,
            'space-tab-mixed-disabled' => 'tab',
            'attr-unsafe-chars' => true,
        ),
    );

    $type = '';

    if ( isset( $args['type'] ) ) {
        $type = $args['type'];

        // Remap MIME types to ones that CodeMirror modes will recognize.
        if ( 'application/x-patch' === $type || 'text/x-patch' === $type ) {
            $type = 'text/x-diff';
        }
    } //we do not treat the "file" case


    if ( 'text/css' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'css',
            'lint' => true,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( 'text/x-scss' === $type || 'text/x-less' === $type || 'text/x-sass' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => $type,
            'lint' => false,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( 'text/x-diff' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'diff',
        ) );
    } elseif ( 'text/html' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'htmlmixed',
            'lint' => true,
            'autoCloseBrackets' => true,
            'autoCloseTags' => true,
            'matchTags' => array(
                'bothTags' => true,
            ),
        ) );

        if ( ! current_user_can( 'unfiltered_html' ) ) {
            $settings['htmlhint']['kses'] = wp_kses_allowed_html( 'post' );
        }
    } elseif ( 'text/x-gfm' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'gfm',
            'highlightFormatting' => true,
        ) );
    } elseif ( 'application/javascript' === $type || 'text/javascript' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'javascript',
            'lint' => true,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( false !== strpos( $type, 'json' ) ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => array(
                'name' => 'javascript',
            ),
            'lint' => true,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
        if ( 'application/ld+json' === $type ) {
            $settings['codemirror']['mode']['jsonld'] = true;
        } else {
            $settings['codemirror']['mode']['json'] = true;
        }
    } elseif ( false !== strpos( $type, 'jsx' ) ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'jsx',
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( 'text/x-markdown' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'markdown',
            'highlightFormatting' => true,
        ) );
    } elseif ( 'text/nginx' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'nginx',
        ) );
    } elseif ( 'application/x-httpd-php' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'php',
            'autoCloseBrackets' => true,
            'autoCloseTags' => true,
            'matchBrackets' => true,
            'matchTags' => array(
                'bothTags' => true,
            ),
        ) );
    } elseif ( 'text/x-sql' === $type || 'text/x-mysql' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'sql',
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( false !== strpos( $type, 'xml' ) ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'xml',
            'autoCloseBrackets' => true,
            'autoCloseTags' => true,
            'matchTags' => array(
                'bothTags' => true,
            ),
        ) );
    } elseif ( 'text/x-yaml' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'yaml',
        ) );
    } else {
        $settings['codemirror']['mode'] = $type;
    }

    if ( ! empty( $settings['codemirror']['lint'] ) ) {
        $settings['codemirror']['gutters'][] = 'CodeMirror-lint-markers';
    }

    // Let settings supplied via args override any defaults.
    foreach ( wp_array_slice_assoc( $args, array( 'codemirror', 'csslint', 'jshint', 'htmlhint' ) ) as $key => $value ) {
        $settings[ $key ] = array_merge(
            $settings[ $key ],
            $value
        );
    }

    $settings = apply_filters( 'nimble_code_editor_settings', $settings, $args );

    if ( empty( $settings ) || empty( $settings['codemirror'] ) ) {
        return false;
    }

    if ( isset( $settings['codemirror']['mode'] ) ) {
        $mode = $settings['codemirror']['mode'];
        if ( is_string( $mode ) ) {
            $mode = array(
                'name' => $mode,
            );
        }
    }

    return $settings;
}




/* ------------------------------------------------------------------------- *
 *  LOCALIZED PARAMS I18N
/* ------------------------------------------------------------------------- */
add_filter( 'nimble-sek-localized-customizer-control-params', '\Nimble\nimble_add_i18n_localized_control_params' );
function nimble_add_i18n_localized_control_params( $params ) {
    return array_merge( $params, array(
        'i18n' => array(
            'Sections' => __( 'Sections', 'text_domain_to_be_replaced'),

            'Nimble Builder' => __('Nimble Builder', 'text_domain_to_be_replaced'),

            "You've reached the maximum number of allowed nested sections." => __("You've reached the maximum number of allowed nested sections.", 'text_domain_to_be_replaced'),
            "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
            "A section must have at least one column." => __( "A section must have at least one column.", 'text_domain_to_be_replaced'),

            'If this problem locks the Nimble builder, you might try to reset the sections for this page.' => __('If this problem locks the Nimble builder, you might try to reset the sections for this page.', 'text_domain_to_be_replaced'),
            'Reset' => __('Reset', 'text_domain_to_be_replaced'),
            'Reset complete' => __('Reset complete', 'text_domain_to_be_replaced'),

            // Generated UI
            'Module Picker' => __('Module Picker', 'text_domain_to_be_replaced'),
            'Drag and drop a module in one of the possible locations of the previewed page.' => __( 'Drag and drop a module in one of the possible locations of the previewed page.', 'text_domain_to_be_replaced' ),

            'Section Picker' => __('Section Picker', 'text_domain_to_be_replaced'),

            'Module' => __('Module', 'text_domain_to_be_replaced'),
            'Content for' => __('Content for', 'text_domain_to_be_replaced'),
            'Customize the options for module :' => __('Customize the options for module :', 'text_domain_to_be_replaced'),

            'Layout settings for the' => __('Layout settings for the', 'text_domain_to_be_replaced'),
            'Background and border settings for the' => __('Background and border settings for the', 'text_domain_to_be_replaced'),
            'Padding and margin settings for the' => __('Padding and margin settings for the', 'text_domain_to_be_replaced'),
            'Height settings for the' => __('Height settings for the', 'text_domain_to_be_replaced'),

            'Settings for the' => __('Settings for the', 'text_domain_to_be_replaced'),//section / column / module

            // Levels
            'location' => __('location', 'text_domain_to_be_replaced'),
            'section' => __('section', 'text_domain_to_be_replaced'),
            'column' => __('column', 'text_domain_to_be_replaced'),
            'module' => __('module', 'text_domain_to_be_replaced'),

            'This browser does not support drag and drop. You might need to update your browser or use another one.' => __('This browser does not support drag and drop. You might need to update your browser or use another one.', 'text_domain_to_be_replaced'),

            // DRAG n DROP
            'Insert here' => __('Insert here', 'text_domain_to_be_replaced'),
            'Insert in a new section' => __('Insert in a new section', 'text_domain_to_be_replaced'),
            'Insert a new section here' => __('Insert a new section here', 'text_domain_to_be_replaced'),

            // MODULES
            'Select a font family' => __('Select a font family', 'text_domain_to_be_replaced'),
            'Web Safe Fonts' => __('Web Safe Fonts', 'text_domain_to_be_replaced'),
            'Google Fonts' => __('Google Fonts', 'text_domain_to_be_replaced'),

            'Set a custom url' => __('Set a custom url', 'text_domain_to_be_replaced'),

            'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_domain_to_be_replaced'),

            'Select an icon'     => __( 'Select an icon', 'text_domain_to_be_replaced' ),

            // Code Editor
            'codeEditorSingular'   => __( 'There is %d error in your %s code which might break your site. Please fix it before saving.', 'text_domain_to_be_replaced' ),
            'codeEditorPlural'     => __( 'There are %d errors in your %s code which might break your site. Please fix them before saving.', 'text_domain_to_be_replaced' ),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),

        )//array()
    )//array()
    );//array_merge
}//'nimble_add_i18n_localized_control_params'







// ADD SEKTION VALUES TO EXPORTED DATA IN THE CUSTOMIZER PREVIEW
add_filter( 'skp_json_export_ready_skopes', '\Nimble\add_sektion_values_to_skope_export' );
function add_sektion_values_to_skope_export( $skopes ) {
    if ( ! is_array( $skopes ) ) {
        error_log( 'skp_json_export_ready_skopes filter => the filtered skopes must be an array.' );
    }
    $new_skopes = array();
    foreach ( $skopes as $skp_data ) {
        if ( 'global' == $skp_data['skope'] || 'group' == $skp_data['skope'] ) {
            $new_skopes[] = $skp_data;
            continue;
        }
        if ( ! is_array( $skp_data ) ) {
            error_log( 'skp_json_export_ready_skopes filter => the skope data must be an array.' );
            continue;
        }
        $skope_id = skp_get_skope_id( $skp_data['skope'] );
        $skp_data[ 'sektions' ] = array(
            'db_values' => sek_get_skoped_seks( $skope_id ),
            'setting_id' => sek_get_seks_setting_id( $skope_id )//nimble___loop_start[skp__post_page_home]
        );
        // foreach( [
        //     'loop_start',
        //     'loop_end',
        //     'before_content',
        //     'after_content',
        //     'global'
        //     ] as $location ) {
        //     $skp_data[ 'sektions' ][ $location ] = array(
        //         'db_values' => sek_get_skoped_seks( $skope_id, $location ),
        //         'setting_id' => sek_get_seks_setting_id( $skope_id, $location )//nimble___loop_start[skp__post_page_home]
        //     );
        // }
        $new_skopes[] = $skp_data;
    }

    // sek_error_log( '//////////////////// => new_skopes', $new_skopes);

    return $new_skopes;
}



function sek_get_preset_sektions() {
    return array(
        'alternate_text_right' => '{"id":"","level":"section","collection":[{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_image_module"}],"width":""},{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_tiny_mce_editor_module","value":{"content":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor."}}]}]}',

        'alternate_text_left' => '{"id":"","level":"section","collection":[{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_tiny_mce_editor_module","value":{"content":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor."}}],"width":""},{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_image_module"}],"width":""}]}',
    );
}


// @see https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
function sek_get_img_sizes() {
    global $_wp_additional_image_sizes;

    $sizes = array();
    $to_return = array(
        'original' => __('Original image dimensions', 'text_domain_to_be_replaced')
    );

    foreach ( get_intermediate_image_sizes() as $_size ) {

        $first_to_upper_size = ucfirst(strtolower($_size));
        $first_to_upper_size = preg_replace_callback('/[.!?].*?\w/', create_function('$matches', 'return strtoupper($matches[0]);'), $first_to_upper_size);

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
?><?php
/* ------------------------------------------------------------------------- *
 *  SETUP DYNAMIC SERVER REGISTRATION FOR SETTING
/* ------------------------------------------------------------------------- */
if ( ! class_exists( 'SEK_CZR_Dyn_Register' ) ) :
    class SEK_CZR_Dyn_Register {
        static $instance;
        public $sanitize_callbacks = array();// <= will be populated to cache the callbacks when invoking sek_get_module_sanitize_callbacks().

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SEK_CZR_Dyn_Register ) )
              self::$instance = new SEK_CZR_Dyn_Register( $params );
            return self::$instance;
        }

        function __construct( $params = array() ) {
            // Schedule the loading the skoped settings class
            add_action( 'customize_register', array( $this, 'load_nimble_setting_class' ) );

            add_filter( 'customize_dynamic_setting_args', array( $this, 'set_dyn_setting_args' ), 10, 2 );
            add_filter( 'customize_dynamic_setting_class', array( $this, 'set_dyn_setting_class') , 10, 3 );
        }//__construct

        //@action 'customize_register'
        function load_nimble_setting_class() {
            require_once(  dirname( __FILE__ ) . '/customizer/seks_setting_class.php' );
        }

        //@filter 'customize_dynamic_setting_args'
        function set_dyn_setting_args( $setting_args, $setting_id ) {
            // shall start with "sek__"
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => 'option',
                    'default' => array(),
                    //'sanitize_callback'    => array( $this, 'sanitize_callback' )
                    //'validate_callback'    => array( $this, 'validate_callback' )
                );
            } else if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => '_nimble_ui_',//won't be saved as is,
                    'default' => array(),
                    'sanitize_callback' => array( $this, 'sanitize_callback' ),
                    'validate_callback' => array( $this, 'validate_callback' )
                );
            }
            return $setting_args;
            //return wp_parse_args( array( 'default' => array() ), $setting_args );
        }


        //@filter 'customize_dynamic_setting_class'
        function set_dyn_setting_class( $class, $setting_id, $args ) {
            // shall start with 'nimble___'
            if ( 0 !== strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) )
              return $class;
            //sek_error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
            return '\Nimble\Nimble_Customizer_Setting';
        }


        // Uses the sanitize_callback function specified on module registration if any
        function sanitize_callback( $setting_data, $setting_instance ) {
            if ( isset( $_POST['skope_id'] ) ) {
                $sektionSettingValue = sek_get_skoped_seks( $_POST['skope_id'] );
                if ( is_array( $sektionSettingValue ) ) {
                    $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
                    if ( is_array( $sektion_collection ) ) {
                        $model = sek_get_level_model( $setting_instance->id, $sektion_collection );
                        if ( is_array( $model ) && ! empty( $model['module_type'] ) ) {
                            $sanitize_callback = sek_get_registered_module_type_property( $model['module_type'], 'sanitize_callback' );
                            if ( ! empty( $sanitize_callback ) && is_string( $sanitize_callback ) && function_exists( $sanitize_callback ) ) {
                                $setting_data = $sanitize_callback( $setting_data );
                            }
                        }
                    }
                }
            }
            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $setting_data );
            return $setting_data;
        }

        // Uses the validate_callback function specified on module registration if any
        // @return validity object
        function validate_callback( $validity, $setting_data, $setting_instance ) {
            $validated = true;
            if ( isset( $_POST['skope_id'] ) ) {
                $sektionSettingValue = sek_get_skoped_seks( $_POST['skope_id'] );
                if ( is_array( $sektionSettingValue ) ) {
                    $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
                    if ( is_array( $sektion_collection ) ) {
                        $model = sek_get_level_model( $setting_instance->id, $sektion_collection );
                        if ( is_array( $model ) && ! empty( $model['module_type'] ) ) {
                            $validate_callback = sek_get_registered_module_type_property( $model['module_type'], 'validate_callback' );
                            if ( ! empty( $validate_callback ) && is_string( $validate_callback ) && function_exists( $validate_callback ) ) {
                                $validated = $validate_callback( $setting_data );
                            }
                        }
                    }
                }
            }
            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $setting_data );
            if ( true !== $validated ) {
                if ( is_wp_error( $validated ) ) {
                    $validation_msg = $validation_msg->get_error_message();
                    $validity->add(
                        'nimble_validation_error_in_' . $setting_instance->id ,
                        $validation_msg
                    );
                }

            }
            return $validity;
        }


 }//class
endif;

?><?php
// Set input content
add_action( 'czr_set_input_tmpl_content', '\Nimble\sek_set_input_tmpl_content', 10, 3 );
function sek_set_input_tmpl_content( $input_type, $input_id, $input_data ) {
    // error_log( print_r( $input_data, true ) );
    // error_log('$input_type' . $input_type );
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'sek_set_input_tmpl_content => missing input type for input id : ' . $input_id );
    }
    switch( $input_type ) {
        case 'module_picker' :
            sek_set_input_tmpl___module_picker( $input_id, $input_data );
        break;
        case 'section_picker' :
            sek_set_input_tmpl___section_picker( $input_id, $input_data );
        break;
        case 'spacing' :
            sek_set_input_tmpl___spacing( $input_id, $input_data );
        break;
        case 'bg_position' :
            sek_set_input_tmpl___bg_position( $input_id, $input_data );
        break;
        case 'h_alignment' :
            sek_set_input_tmpl___h_alignment( $input_id, $input_data );
        break;
         case 'h_text_alignment' :
            sek_set_input_tmpl___h_text_alignment( $input_id, $input_data );
        break;
        case 'v_alignment' :
            sek_set_input_tmpl___v_alignment( $input_id, $input_data );
        break;
        case 'font_picker' :
            sek_set_input_tmpl___font_picker( $input_id, $input_data );
        break;
        case 'fa_icon_picker' :
            sek_set_input_tmpl___fa_icon_picker( $input_id, $input_data );
        break;
        case 'font_size' :
        case 'line_height' :
            sek_set_input_tmpl___font_size_line_height( $input_id, $input_data );
        break;
        case 'code_editor' :
            sek_set_input_tmpl___code_editor( $input_id, $input_data );
        break;
    }
}
?><?php
/* ------------------------------------------------------------------------- *
 *  MODULE PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___module_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = array(
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_tiny_mce_editor_module',
                  'title' => __( 'Text Editor', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__text_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_image_module',
                  'title' => __( 'Image', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__image_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_heading_module',
                  'title' => __( 'Heading', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__heading_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_spacer_module',
                  'title' => __( 'Spacer', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__spacer_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_divider_module',
                  'title' => __( 'Divider', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__divider_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_icon_module',
                  'title' => __( 'Icon', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__icon_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_featured_pages_module',
                  'title' => __( 'Featured pages',  'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__featured_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_simple_html_module',
                  'title' => __( 'Html Content', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_html_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_map_module',
                  'title' => __( 'Map', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_map_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_quote_module',
                  'title' => __( 'Quote', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_quote_icon.svg'
                ),
            );
            $i = 0;
            foreach( $content_collection as $_params) {
                // if ( $i % 2 == 0 ) {
                //   //printf('<div class="sek-module-raw"></div');
                // }
                $icon_img_src = '';
                if ( !empty( $_params['icon'] ) ) {
                    $icon_img_src = NIMBLE_BASE_URL . '/assets/czr/sek/icons/modules/' . $_params['icon'];
                }

                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s" title="%5$s"><span class="sek-module-icon">%3$s</span><span class="sek-module-title">%4$s</span></div>',
                      $_params['content-type'],
                      $_params['content-id'],
                      empty( $icon_img_src ) ? '<i style="color:red">Missing Icon</i>' : '<img title="'. $_params['title'] . '" alt="'. $_params['title'] . '" class="nimble-module-icons" src="' . $icon_img_src .'"/>',
                      $_params['title'],
                      __('Drag and drop the module in the previewed page.', 'text_domain_to_be_replaced' )
                );
            }
          ?>
        </div>
    <?php
}

?><?php

/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___section_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = array(
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'alternate_text_right',
                  'title' => 'Image + Text'
                ),
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'alternate_text_left',
                  'title' => 'Text + Image'
                )
            );
            foreach( $content_collection as $_params) {
                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s"><p>%3$s</p></div>',
                    $_params['content-type'],
                    $_params['content-id'],
                    $_params['title']
                );
            }
          ?>
        </div>
  <?php
}

?><?php

/* ------------------------------------------------------------------------- *
 *  SPACING INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___spacing( $input_id, $input_data ) {
    ?>
    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
    <div class="sek-spacing-wrapper">
        <div class="sek-pad-marg-inner">
          <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch" data-sek-spacing="margin-top">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>
          <div class="sek-pm-middle-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch sek-pm-margin-left" data-sek-spacing="margin-left">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>

            <div class="sek-pm-padding-wrapper">
              <div class="sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="padding-top">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
                <div class="sek-flex-justify-center sek-flex-space-between">
                  <div class="sek-flex-center-stretch" data-sek-spacing="padding-left">
                    <div class="sek-pm-input-parent">
                      <input class="sek-pm-input" value="" type="number"  >
                    </div>
                  </div>
                  <div class="sek-flex-center-stretch" data-sek-spacing="padding-right">
                    <div class="sek-pm-input-parent">
                      <input class="sek-pm-input" value="" type="number"  >
                    </div>
                  </div>
                </div>
              <div class="sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="padding-bottom">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
            </div>

            <div class="sek-flex-center-stretch sek-pm-margin-right" data-sek-spacing="margin-right">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>

          <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch" data-sek-spacing="margin-bottom">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>
        </div><?php //sek-pad-marg-inner ?>
        <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_domain_to_be_replaced' ); ?></span></div>
    </div><?php // sek-spacing-wrapper ?>
    <?php
}

?><?php

/* ------------------------------------------------------------------------- *
 *  BACKGROUND POSITION INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___bg_position( $input_id, $input_data ) {
    ?>
        <div class="sek-bg-pos-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_left">
            <span>
              <svg class="symbol symbol-alignTypeTopLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M14.96 16v-1h-1v-1h-1v-1h-1v-1h-1v-1.001h-1V14h-1v-4-1h5v1h-3v.938h1v.999h1v1h1v1.001h1v1h1V16h-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top">
            <span>
              <svg class="symbol symbol-alignTypeTop" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M14.969 12v-1h-1v-1h-1v7h-1v-7h-1v1h-1v1h-1v-1.062h1V9.937h1v-1h1V8h1v.937h1v1h1v1.001h1V12h-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_right">
            <span>
              <svg class="symbol symbol-alignTypeTopRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M9.969 16v-1h1v-1h1v-1h1v-1h1v-1.001h1V14h1v-4-1h-1-4v1h3v.938h-1v.999h-1v1h-1v1.001h-1v1h-1V16h1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="left">
            <span>
              <svg class="symbol symbol-alignTypeLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M11.469 9.5h-1v1h-1v1h7v1h-7v1h1v1h1v1h-1.063v-1h-1v-1h-1v-1h-.937v-1h.937v-1h1v-1h1v-1h1.063v1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="center">
            <span>
              <svg class="symbol symbol-alignTypeCenter" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="right">
            <span>
              <svg class="symbol symbol-alignTypeRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M12.469 14.5h1v-1h1v-1h-7v-1h7v-1h-1v-1h-1v-1h1.062v1h1v1h1v1h.938v1h-.938v1h-1v1h-1v1h-1.062v-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_left">
            <span>
              <svg class="symbol symbol-alignTypeBottomLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M14.969 9v1h-1v1h-1v1h-1v1h-1v1.001h-1V11h-1v5h5v-1h-3v-.938h1v-.999h1v-1h1v-1.001h1v-1h1V9h-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom">
            <span>
              <svg class="symbol symbol-alignTypeBottom" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M9.969 13v1h1v1h1V8h1v7h1v-1h1v-1h1v1.063h-1v.999h-1v1.001h-1V17h-1v-.937h-1v-1.001h-1v-.999h-1V13h1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_right">
            <span>
              <svg class="symbol symbol-alignTypeBottomRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M9.969 9v1h1v1h1v1h1v1h1v1.001h1V11h1v5h-1-4v-1h3v-.938h-1v-.999h-1v-1h-1v-1.001h-1v-1h-1V9h1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
        </div><?php // sek-bg-pos-wrapper ?>
    <?php
}

?><?php

/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// AND
/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT FOR TEXT => includes the 'justify' icon
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___h_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_right</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}

function sek_set_input_tmpl___h_text_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_right</i></div>
            <div data-sek-align="justify" title="<?php _e('Justified','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_justify</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}
?><?php
/* ------------------------------------------------------------------------- *
 *  VERTICAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___v_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-v-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="top" title="<?php _e('Align top','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_top</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_center</i></div>
            <div data-sek-align="bottom" title="<?php _e('Align bottom','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_bottom</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}

?><?php
/* ------------------------------------------------------------------------- *
 *  FONT AWESOME ICON PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___fa_icon_picker( $input_id, $input_data ) {
    ?>
        <select data-czrtype="<?php echo $input_id; ?>"></select>
    <?php
}


// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___fa_icon_picker_input", '\Nimble\sek_get_fa_icon_list_tmpl', 10, 3 );
// hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
// this dynamic filter is declared on wp_ajax_ac_get_template
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
//
// For czr_tiny_mce_editor_module, we request the font_list tmpl
function sek_get_fa_icon_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }

    return wp_json_encode(
        sek_retrieve_decoded_font_awesome_icons()
    );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}



//retrieves faicons:
// 1) from faicons.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
// otherwise
// 2) from the transient set if it exists
function sek_retrieve_decoded_font_awesome_icons() {
    // this file must be generated with: https://github.com/presscustomizr/nimble-builder/issues/57
    $faicons_json_path      = NIMBLE_BASE_PATH . '/assets/faicons.json';
    $faicons_transient_name = 'sek_font_awesome_july_2018';
    if ( false == get_transient( $faicons_transient_name ) ) {
        if ( file_exists( $faicons_json_path ) ) {
            $faicons_raw      = @file_get_contents( $faicons_json_path );

            if ( false === $faicons_raw ) {
                $faicons_raw = wp_remote_fopen( $faicons_json_path );
            }

            $faicons_decoded   = json_decode( $faicons_raw, true );
            set_transient( $faicons_transient_name , $faicons_decoded , 60*60*24*3000 );
        } else {
            wp_send_json_error( __FUNCTION__ . ' => the file faicons.json is missing' );
        }
    }
    else {
        $faicons_decoded = get_transient( $faicons_transient_name );
    }

    return $faicons_decoded;
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  FONT PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___font_picker( $input_id, $input_data ) {
    ?>
        <select data-czrtype="<?php echo $input_id; ?>"></select>
    <?php
}


// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___font_picker_input", '\Nimble\sek_get_font_list_tmpl', 10, 3 );
// hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
// this dynamic filter is declared on wp_ajax_ac_get_template
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
//
// For czr_tiny_mce_editor_module, we request the font_list tmpl
function sek_get_font_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }

    return wp_json_encode( array(
        'cfonts' => sek_get_cfonts(),
        'gfonts' => sek_get_gfonts(),
    ) );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}


function sek_get_cfonts() {
    $cfonts = array();
    $raw_cfonts = array(
        'Arial Black,Arial Black,Gadget,sans-serif',
        'Century Gothic',
        'Comic Sans MS,Comic Sans MS,cursive',
        'Courier New,Courier New,Courier,monospace',
        'Georgia,Georgia,serif',
        'Helvetica Neue, Helvetica, Arial, sans-serif',
        'Impact,Charcoal,sans-serif',
        'Lucida Console,Monaco,monospace',
        'Lucida Sans Unicode,Lucida Grande,sans-serif',
        'Palatino Linotype,Book Antiqua,Palatino,serif',
        'Tahoma,Geneva,sans-serif',
        'Times New Roman,Times,serif',
        'Trebuchet MS,Helvetica,sans-serif',
        'Verdana,Geneva,sans-serif',
    );
    foreach ( $raw_cfonts as $font ) {
      //no subsets for cfonts => epty array()
      $cfonts[] = array(
          'name'    => $font ,
          'subsets'   => array()
      );
    }
    return apply_filters( 'sek_font_picker_cfonts', $cfonts );
}


//retrieves gfonts:
// 1) from webfonts.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
// otherwise
// 2) from the transiet set if it exists
//
// => Until June 2017, the webfonts have been stored in 'tc_gfonts' transient
// => In June 2017, the Google Fonts have been updated with a new webfonts.json
// generated from : https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBID8gp8nBOpWyH5MrsF7doP4fczXGaHdA
//
// => The transient name is now : czr_gfonts_june_2017
function sek_retrieve_decoded_gfonts() {
    if ( false == get_transient( 'sek_gfonts_may_2018' ) ) {
        $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

        if ( $gfont_raw === false ) {
          $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
        }

        $gfonts_decoded   = json_decode( $gfont_raw, true );
        set_transient( 'sek_gfonts_may_2018' , $gfonts_decoded , 60*60*24*3000 );
    }
    else {
      $gfonts_decoded = get_transient( 'sek_gfonts_may_2018' );
    }

    return $gfonts_decoded;
}



//@return the google fonts
function sek_get_gfonts( $what = null ) {
  //checks if transient exists or has expired

  $gfonts_decoded = sek_retrieve_decoded_gfonts();
  $gfonts = array();
  //$subsets = array();

  // $subsets['all-subsets'] = sprintf( '%1$s ( %2$s %3$s )',
  //   __( 'All languages' , 'text_domain_to_be_replaced' ),
  //   count($gfonts_decoded['items']) + count( $this -> get_cfonts() ),
  //   __('fonts' , 'text_domain_to_be_replaced' )
  // );

  foreach ( $gfonts_decoded['items'] as $font ) {
    foreach ( $font['variants'] as $variant ) {
      $name     = str_replace( ' ', '+', $font['family'] );
      $gfonts[]   = array(
          'name'    => $name . ':' .$variant
          //'subsets'   => $font['subsets']
      );
    }
    //generates subset list : subset => font number
    // foreach ( $font['subsets'] as $sub ) {
    //   $subsets[$sub] = isset($subsets[$sub]) ? $subsets[$sub]+1 : 1;
    // }
  }

  //finalizes the subset array
  // foreach ( $subsets as $subset => $font_number ) {
  //   if ( 'all-subsets' == $subset )
  //     continue;
  //   $subsets[$subset] = sprintf('%1$s ( %2$s %3$s )',
  //     $subset,
  //     $font_number,
  //     __('fonts' , 'text_domain_to_be_replaced' )
  //   );
  // }

  return ('subsets' == $what) ? apply_filters( 'sek_font_picker_gfonts_subsets ', $subsets ) : apply_filters( 'sek_font_picker_gfonts', $gfonts )  ;
}

?><?php

/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// AND
/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT FOR TEXT => includes the 'justify' icon
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___font_size_line_height( $input_id, $input_data ) {
    ?>
        <div class="sek-font-size-line-height-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <?php
            // we save the int value + unit
            // we want to keep only the numbers when printing the tmpl
          ?>
          <#
            var value = data['<?php echo $input_id; ?>'];
            value = _.isString( value ) ? value.replace(/\D+/g, '') : '';
          #>
          <?php
              printf( '<input type="number" %1$s %2$s %3$s value="{{ value }}" />',
                  ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                  ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                  ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
                );
          ?>
        </div><?php // sek-font-size-wrapper ?>
    <?php
}
?><?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___code_editor( $input_id, $input_data ) {
    /*
    * Needed to form the correct params to pass to the code mirror editor, based on the code type
    */
    $code_editor_params = nimble_get_code_editor_settings( array(
        'type' => ! empty( $input_data[ 'code_type' ] ) ? $input_data[ 'code_type' ] : 'text/html',
    ));
    ?>
        <textarea data-czrtype="<?php echo $input_id; ?>" class="width-100" name="textarea" rows="10" cols="" data-editor-params="<?php echo htmlspecialchars( json_encode( $code_editor_params ) ); ?>">{{ data.value }}</textarea>
    <?php
}
?>
<?php
// The base fmk is loaded @after_setup_theme:10
add_action( 'after_setup_theme', '\Nimble\sek_register_modules', 50 );
function sek_register_modules() {
    foreach( [
        'sek_module_picker_module',
        //'sek_section_picker_module',
        'sek_level_bg_border_module',
        'sek_level_section_layout_module',
        'sek_level_height_module',
        'sek_spacing_module',
        'czr_simple_html_module',
        'czr_tiny_mce_editor_module',
        'czr_image_module',
        'czr_featured_pages_module',
        'czr_heading_module',
        'czr_spacer_module',
        'czr_divider_module',
        'czr_icon_module',
        'czr_map_module',
        'czr_quote_module'
    ] as $module_name ) {
        $fn = "\Nimble\sek_get_module_params_for_{$module_name}";
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


// HELPERS
// Used when registrating a select input in a module
// @return an array of options that will be used to populate the select input in js
function sek_get_select_options_for_input_id( $input_id ) {
    $options = array();
    switch( $input_id ) {
        // IMAGE MODULE
        case 'link-to' :
            $options = array(
                'no-link' => __('No link', 'text_domain_to_be_replaced' ),
                'url' => __('Site content or custom url', 'text_domain_to_be_replaced' ),
                'img-file' => __('Image file', 'text_domain_to_be_replaced' ),
                'img-page' =>__('Image page', 'text_domain_to_be_replaced' )
            );
        break;
        case 'img-size' :
            $options = sek_get_img_sizes();
        break;

        // FEATURED PAGE MODULE
        case 'img-type' :
            $options = array(
                'none' => __('No text', 'text_domain_to_be_replaced' ),
                'page-excerpt' => __('Use the page excerpt', 'text_domain_to_be_replaced' ),
                'custom' => __('Use a custom text', 'text_domain_to_be_replaced' ),
            );
        break;
        case 'content-type' :
            $options = array(
                'none' => __('No image', 'text_domain_to_be_replaced' ),
                'featured' => __('Use the page featured image', 'text_domain_to_be_replaced' ),
                'custom' => __('Use a custom image', 'text_domain_to_be_replaced' ),
            );
        break;

        // HEADING MODULE
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

        // CSS MODIFIERS INPUT ID
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

        // SPACING MODULE
        case 'css_unit' :
            $options = array(
                'px' => __('Pixels', 'text_domain_to_be_replaced' ),
                'em' => __('Em', 'text_domain_to_be_replaced'),
                'percent' => __('Percents', 'text_domain_to_be_replaced' )
            );
        break;

        //QUOTE MODULE
        case 'quote_design' :
            $options = array(
                'none' => __( 'No design', 'text_domain_to_be_replaced' ),
                'border-before' => __( 'Border on the left (right for RTL users)', 'text_domain_to_be_replaced' ),
            );
        break;

        // LAYOUT BACKGROUND BORDER
        case 'boxed-wide' :
            $options = array(
                'boxed' => __('Boxed', 'text_domain_to_be_replaced'),
                'fullwidth' => __('Full Width', 'text_domain_to_be_replaced')
            );
        break;
        case 'height-type' :
            $options = array(
                'default' => __('default', 'text_domain_to_be_replaced'),
                'fit-to-screen' => __('Fit to screen', 'text_domain_to_be_replaced'),
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


        default :
            sek_error_log( __FUNCTION__ . ' => no case set for input id : '. $input_id );
        break;
    }
    return $options;
}

?><?php

/* ------------------------------------------------------------------------- *
 *  MODULE PICKER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_module_picker_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_module_picker_module',
        'name' => __('Module Picker', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
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


?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_bg_border_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_bg_border_module',
        'name' => __('Background and borders', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Background', 'text_domain_to_be_replaced'),
                        'inputs' => array(
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
                                'input_type'  => 'bg_position',
                                'title'       => __('Image position', 'text_domain_to_be_replaced'),
                                'default'     => 'center'
                            ),
                            // 'bg-parallax' => array(
                            //     'input_type'  => 'gutencheck',
                            //     'title'       => __('Parallax scrolling', 'text_domain_to_be_replaced')
                            // ),
                            'bg-attachment' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Fixed background', 'text_domain_to_be_replaced'),
                                'default'     => 0
                            ),
                            // 'bg-repeat' => array(
                            //     'input_type'  => 'select',
                            //     'title'       => __('repeat', 'text_domain_to_be_replaced')
                            // ),
                            'bg-scale' => array(
                                'input_type'  => 'select',
                                'title'       => __('scale', 'text_domain_to_be_replaced'),
                                'default'     => 'cover',
                                'choices'     => sek_get_select_options_for_input_id( 'bg-scale' )
                            ),
                            // 'bg-video' => array(
                            //     'input_type'  => 'text',
                            //     'title'       => __('Video', 'text_domain_to_be_replaced'),
                            //     'default'     => ''
                            // ),
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
                                'default'     => ''
                            ),
                            'bg-opacity-overlay' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Opacity', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                'unit' => '%',
                                'default'  => 50
                            )
                        )
                    ),
                    array(
                        'title' => __('Border', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'border-width' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Border width', 'text_domain_to_be_replaced'),
                                'min' => 0,
                                'max' => 100,
                                'unit' => 'px',
                                'default' => 1
                            ),
                            'border-type' => array(
                                'input_type'  => 'select',
                                'title'       => __('Border shape', 'text_domain_to_be_replaced'),
                                'default' => 'none',
                                'choices'     => sek_get_select_options_for_input_id( 'border-type' )
                            ),
                            'border-color' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Border color', 'text_domain_to_be_replaced'),
                                'width-100'   => true,
                                'default' => ''
                            ),
                            'shadow' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply a shadow', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'default' => 0
                            )
                        )
                    ),
                )//tabs
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_bg_border_background', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_bg_border_border', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_bg_border_boxshadow', 10, 3 );

function sek_add_css_rules_for_bg_border_background( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

    // $default_value_model = Array
    // (
    //     [bg-color] =>
    //     [bg-image] =>
    //     [bg-position] => center
    //     [bg-attachment] => 0
    //     [bg-scale] => default
    //     [bg-apply-overlay] => 0
    //     [bg-color-overlay] =>
    //     [bg-opacity-overlay] => 50
    //     [border-width] => 1
    //     [border-type] => none
    //     [border-color] =>
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_bg_border_module' );
    $bg_border_options = ( ! empty( $options[ 'bg_border' ] ) && is_array( $options[ 'bg_border' ] ) ) ? $options[ 'bg_border' ] : array();
    $bg_border_options = wp_parse_args( $bg_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $bg_border_options ) )
      return $rules;

    $background_properties = array();

    /* The general syntax of the background property is:
    * https://www.webpagefx.com/blog/web-design/background-css-shorthand/
    * background: [background-image] [background-position] / [background-size] [background-repeat] [background-attachment] [background-origin] [background-clip] [background-color];
    */
    // Img background
    if ( ! empty( $bg_border_options[ 'bg-image'] ) && is_numeric( $bg_border_options[ 'bg-image'] ) ) {
        //no repeat by default?
        $background_properties[] = 'url("'. wp_get_attachment_url( $bg_border_options[ 'bg-image'] ) .'")';

        // Img Bg Position
        if ( ! empty( $bg_border_options[ 'bg-position'] ) ) {
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

            $raw_pos                    = $bg_border_options[ 'bg-position'];
            $background_properties[]         = array_key_exists($raw_pos, $pos_map) ? $pos_map[ $raw_pos ] : $pos_map[ 'center' ];
        }


        //background size
        if ( ! empty( $bg_border_options[ 'bg-scale'] ) && 'default' != $bg_border_options[ 'bg-scale'] ) {
            //When specifying a background-size value, it must immediately follow the background-position value.
            if ( ! empty( $bg_border_options[ 'bg-position'] ) ) {
                $background_properties[] = '/ ' . $bg_border_options[ 'bg-scale'];
            } else {
                $background_size    = $bg_border_options[ 'bg-scale'];
            }
        }

        //add no-repeat by default?
        $background_properties[] = 'no-repeat';

        // write the bg-attachment rule only if true <=> set to "fixed"
        if ( ! empty( $bg_border_options[ 'bg-attachment'] ) && sek_is_checked( $bg_border_options[ 'bg-attachment'] ) ) {
            $background_properties[] = 'fixed';
        }

    }


    //background color (needs validation: we need a sanitize hex or rgba color)
    if ( ! empty( $bg_border_options[ 'bg-color' ] ) ) {
        $background_properties[] = $bg_border_options[ 'bg-color' ];
    }


    //build background rule
    if ( ! empty( $background_properties ) ) {
        $background_css_rules      = "background:" . implode( ' ', array_filter( $background_properties ) );

        //do we need to add the background-size property separately?
        $background_css_rules      = isset( $background_size ) ? $css_rules . ';background-size:' . $background_size : $background_css_rules;

        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'css_rules' => $background_css_rules,
            'mq' =>null
        );
    }

    //Background overlay?
    // 1) a background image should be set
    // 2) the option should be checked
    if ( !empty( $bg_border_options['bg-image']) && ! empty( $bg_border_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_border_options[ 'bg-apply-overlay'] ) ) {
        //(needs validation: we need a sanitize hex or rgba color)
        $bg_color_overlay = isset( $bg_border_options[ 'bg-color-overlay' ] ) ? $bg_border_options[ 'bg-color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            //overlay pseudo element
            $bg_overlay_css_rules = 'content:"";display:block;position:absolute;top:0;left:0;right:0;bottom:0;background-color:'.$bg_color_overlay;

            //opacity
            //validate/sanitize
            $bg_overlay_opacity     = isset( $bg_border_options[ 'bg-opacity-overlay' ] ) ? filter_var( $bg_border_options[ 'bg-opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_css_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_css_rules . ';opacity:' . $bg_overlay_opacity : $bg_overlay_css_rules;

            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]::before',
                    'css_rules' => $bg_overlay_css_rules,
                    'mq' =>null
            );
            //we have to also:
            // 1) make '[data-sek-id="'.$level['id'].'"] to be relative positioned (to make the overlay absolute element referring to it)
            // 2) make any '[data-sek-id="'.$level['id'].'"] first child to be relative (not to the resizable handle div)
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => 'position:relative',
                    'mq' => null
            );

            $first_child_selector = '[data-sek-id="'.$level['id'].'"]>*';
            //in the preview we still want some elements to be absoluted positioned
            //1) the .ui-resizable-handle (jquery-ui)
            //2) the block overlay
            //3) the add content button
            if ( is_customize_preview() ) {
                $first_child_selector .= ':not(.ui-resizable-handle):not(.sek-dyn-ui-wrapper):not(.sek-add-content-button)';
            }
            $rules[]     = array(
                'selector' => $first_child_selector,
                'css_rules' => 'position:relative',
                'mq' =>null
            );
        }
    }//if ( ! empty( $bg_border_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_border_options[ 'bg-apply-overlay'] ) ) {}

    return $rules;
}











function sek_add_css_rules_for_bg_border_border( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    // $default_value_model = Array
    // (
    //     [bg-color] =>
    //     [bg-image] =>
    //     [bg-position] => center
    //     [bg-attachment] => 0
    //     [bg-scale] => default
    //     [bg-apply-overlay] => 0
    //     [bg-color-overlay] =>
    //     [bg-opacity-overlay] => 50
    //     [border-width] => 1
    //     [border-type] => none
    //     [border-color] =>
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_bg_border_module' );
    $bg_border_options = ( ! empty( $options[ 'bg_border' ] ) && is_array( $options[ 'bg_border' ] ) ) ? $options[ 'bg_border' ] : array();
    $bg_border_options = wp_parse_args( $bg_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    //TODO: we actually should allow multidimensional border widths plus different units
    if ( empty( $bg_border_options ) )
      return $rules;

    $border_width = ! empty( $bg_border_options[ 'border-width' ] ) ? filter_var( $bg_border_options[ 'border-width' ], FILTER_VALIDATE_INT ) : FALSE;
    $border_type  = FALSE !== $border_width && ! empty( $bg_border_options[ 'border-type' ] ) && 'none' != $bg_border_options[ 'border-type' ] ? $bg_border_options[ 'border-type' ] : FALSE;

    //border width
    if ( $border_type ) {
        $border_properties = array();
        $border_properties[] = $border_width . 'px';

        //border type
        $border_properties[] = $border_type;

        //border color
        //(needs validation: we need a sanitize hex or rgba color)
        if ( ! empty( $bg_border_options[ 'border-color' ] ) ) {
            $border_properties[] = $bg_border_options[ 'border-color' ];
        }

        //append border rules
        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'css_rules' => "border:" . implode( ' ', array_filter( $border_properties ) ),
                'mq' =>null
        );
    }

    return $rules;
}














function sek_add_css_rules_for_bg_border_boxshadow( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    // $default_value_model = Array
    // (
    //     [bg-color] =>
    //     [bg-image] =>
    //     [bg-position] => center
    //     [bg-attachment] => 0
    //     [bg-scale] => default
    //     [bg-apply-overlay] => 0
    //     [bg-color-overlay] =>
    //     [bg-opacity-overlay] => 50
    //     [border-width] => 1
    //     [border-type] => none
    //     [border-color] =>
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_bg_border_module' );
    $bg_border_options = ( ! empty( $options[ 'bg_border' ] ) && is_array( $options[ 'bg_border' ] ) ) ? $options[ 'bg_border' ] : array();
    $bg_border_options = wp_parse_args( $bg_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $bg_border_options) )
      return $rules;

    if ( !empty( $bg_border_options[ 'shadow' ] ) &&  sek_is_checked( $bg_border_options[ 'shadow'] ) ) {
        $css_rules = 'box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2); -webkit-box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2);';

        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'css_rules' => $css_rules,
                'mq' =>null
        );
    }
    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_section_layout_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_section_layout_module',
        'name' => __('Section Layout', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'boxed-wide' => array(
                    'input_type'  => 'select',
                    'title'       => __('Boxed or full width', 'text_domain_to_be_replaced'),
                    'refresh_markup' => true,
                    'refresh_stylesheet' => false,
                    'default'     => 'fullwidth',
                    'choices'     => sek_get_select_options_for_input_id( 'boxed-wide' )
                ),

                /* suspended, needs more thoughts
                'boxed-width' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __('Custom boxed width', 'text_domain_to_be_replaced'),
                    'orientation' => 'horizontal',
                    'min' => 500,
                    'max' => 1600,
                    'unit' => 'px'
                ),*/
            )
        )//tmpl
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_height_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_height_module',
        'name' => __('Height options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Height : fit to screen or custom', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'height-type' )
                ),
                'custom-height' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __('Custom height', 'text_domain_to_be_replaced'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    'unit' => '%',
                    'default' => 50
                ),
                'v_alignment' => array(
                    'input_type'  => 'v_alignment',
                    'title'       => __('Inner vertical alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'v_alignment'
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

    if ( ! empty( $options[ 'height' ][ 'v_alignment' ] ) ) {
        $v_alignment_value = $options[ 'height' ][ 'v_alignment' ];
        switch ( $v_alignment_value ) {
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
        $css_rules = '';
        if ( isset( $v_align_value ) ) {
            $css_rules .= 'align-items:' . $v_align_value;
        }

        if ( !empty( $css_rules ) ) {
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => $css_rules,
                    'mq' =>null
            );
        }
    }

    if ( ! empty( $options[ 'height' ][ 'height-type' ] ) ) {

        if ( 'fit-to-screen' == $options[ 'height' ][ 'height-type' ] ) {
            $height = '100';
        }
        elseif ( 'custom' == $options[ 'height' ][ 'height-type' ] && array_key_exists( 'custom-height', $options[ 'height' ] ) && FALSE !== $height_value = filter_var( $options[ 'height' ][ 'custom-height' ], FILTER_VALIDATE_INT, array( 'options' =>
                    array( "min_range"=>0, "max_range"=>100 ) ) ) ) {
            $height = $height_value;
        }
        $css_rules = '';
        if ( isset( $height ) && FALSE !== $height ) {
            $css_rules .= 'height:' . $height . 'vh;';
        }

        if ( !empty( $css_rules ) ) {
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => $css_rules,
                    'mq' =>null
            );
        }
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_spacing_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_spacing_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',

        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Desktop', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'desktop_pad_marg' => array(
                                'input_type'  => 'spacing',
                                'title'       => __('Set padding and margin for desktops', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'default'     => array()
                            ),
                            'desktop_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced'),
                                'default'     => 'px',
                                'choices'     => sek_get_select_options_for_input_id( 'css_unit' )
                            )
                        )
                    ),
                    array(
                        'title' => __('Tablet', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-device="tablet"',
                        'inputs' => array(
                            'tablet_pad_marg' => array(
                                'input_type'  => 'spacing',
                                'title'       => __('Set padding and margin for tablet devices', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'default'     => array()
                            ),
                            'tablet_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced'),
                                'default'     => 'px',
                                'choices'     => sek_get_select_options_for_input_id( 'css_unit' )
                            )
                        )
                    ),
                    array(
                        'title' => __('Mobile', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-device="mobile"',
                        'inputs' => array(
                            'mobile_pad_marg' => array(
                                'input_type'  => 'spacing',
                                'title'       => __('Set padding and margin for mobile devices', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'default'     => array()
                            ),
                            'mobile_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced'),
                                'default'     => 'px',
                                'choices'     => sek_get_select_options_for_input_id( 'css_unit' )
                            )
                        )
                    )

                )
            )
        )
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_spacing', 10, 3 );
// hook : sek_dyn_css_builder_rules
// @return array() of css rules
function sek_add_css_rules_for_spacing( $rules, $level ) {

    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

    //spacing
    if ( empty( $options[ 'spacing' ] ) )
      return $rules;


    $default_unit = 'px';

    //not mobile first
    $_desktop_rules = $_mobile_rules = $_tablet_rules = null;

    if ( !empty( $options[ 'spacing' ][ 'desktop_pad_marg' ] ) ) {
         $_desktop_rules = array( 'rules' => $options[ 'spacing' ][ 'desktop_pad_marg' ] );
    }

    $_pad_marg = array(
        'desktop' => array(),
        'tablet' => array(),
        'mobile' => array()
    );

    foreach( array_keys( $_pad_marg ) as $device  ) {
        if ( !empty( $options[ 'spacing' ][ "{$device}_pad_marg" ] ) ) {
            $_pad_marg[ $device ] = array( 'rules' => $options[ 'spacing' ][ "{$device}_pad_marg" ] );

            //add unit and sanitize padding (cannot have negative padding)
            $unit                 = !empty( $options[ 'spacing' ][ "{$device}_unit" ] ) ? $options[ 'spacing' ][ "{$device}_unit" ] : $default_unit;
            $unit                 = 'percent' == $unit ? '%' : $unit;
            array_walk( $_pad_marg[ $device ][ 'rules' ],
                function( &$val, $key, $unit ) {
                    //make sure paddings are positive values
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
    // Sek_Dyn_CSS_Builder::$breakpoints = [
    //     'xs' => 0,
    //     'sm' => 576,
    //     'md' => 768,
    //     'lg' => 992,
    //     'xl' => 1200
    // ];
    if ( ! empty( $_pad_marg[ 'desktop' ] ) ) {
        $_pad_marg[ 'desktop' ][ 'mq' ] = null;
    }

    if ( ! empty( $_pad_marg[ 'tablet' ] ) ) {
        $_pad_marg[ 'tablet' ][ 'mq' ]  = 'max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['lg'] - 1 ) . 'px'; //max-width: 991
    }

    if ( ! empty( $_pad_marg[ 'mobile' ] ) ) {
        $_pad_marg[ 'mobile' ][ 'mq' ]  = 'max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['sm'] - 1 ) . 'px'; //max-width: 575
    }

    foreach( array_filter( $_pad_marg ) as $_spacing_rules ) {
        $css_rules = implode(';',
            array_map( function( $key, $value ) {
                return "$key:{$value}";
            }, array_keys( $_spacing_rules[ 'rules' ] ), array_values( $_spacing_rules[ 'rules' ] )
        ) );

        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'css_rules' => $css_rules,
            'mq' =>$_spacing_rules[ 'mq' ]
        );
    }

    return $rules;
}

?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE HTML MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_simple_html_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_html_module',
        'name' => __( 'Html Content', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_html_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'html_content' => '<!-- Write your Html code here --><pre>html code goes here</pre>'
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'html_content' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'HTML Content' , 'text_domain_to_be_replaced' ),
                    //'code_type' => 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
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
 *  LOAD AND REGISTER THE TEXT EDITOR MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',
        'name' => __('Text Editor', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Content', 'text_domain_to_be_replaced' ),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'content' => array(
                                'input_type'  => 'tiny_mce_editor',
                                'title'       => __('Content', 'text_domain_to_be_replaced'),
                                'default'     => ''
                            ),
                            'h_alignment_css' => array(
                                'input_type'  => 'h_text_alignment',
                                'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                                'default'     => is_rtl() ? 'right' : 'left',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment'
                            )
                        )
                    ),
                    array(
                        'title' => __('Font style', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-google-font-tab="true"',
                        'inputs' => array(
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
                                'input_type'  => 'font_size',
                                'title'       => __('Font size in pixels', 'text_domain_to_be_replaced'),
                                'default'     => '16px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size'
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __('Line height in pixels', 'text_domain_to_be_replaced'),
                                'default'     => '24px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height'
                            ),//24,//"20px",
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
                                'input_type'  => 'number',
                                'title'       => __('Letter spacing', 'text_domain_to_be_replaced'),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing'
                            ),//0,
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
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'tiny_mce___flag_important'  => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Make those style options win if other rules are applied.', 'text_domain_to_be_replaced'),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
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
                            ),//false
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}


?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMAGE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_image_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',
        'name' => __('Image', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        ),
        // 'sanitize_callback' => '\Nimble\czr_image_module_sanitize_validate',
        // 'validate_callback' => '\Nimble\czr_image_module_sanitize_validate',
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
                'h_alignment_css' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment'
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
                    'default'     => false
                ),
                // 'lightbox' => array(
                //     'input_type'  => 'gutencheck',
                //     'title'       => __('Activate a lightbox on click', 'text_domain_to_be_replaced'),
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'default'     => 'center'
                // ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER FEATURED PAGES MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_featured_pages_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_featured_pages_module',
        'is_crud' => true,
        'name' => __('Featured Pages', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(
        //     'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_domain_to_be_replaced')
                // ),
                'img-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display an image', 'text_domain_to_be_replaced'),
                    'default'     => 'featured',
                    'choices'     => sek_get_select_options_for_input_id( 'img-type' )
                ),
            ),
            // 'mod-opt' => array(
            //     // 'page-id' => array(
            //     //     'input_type'  => 'content_picker',
            //     //     'title'       => __('Pick a page', 'text_domain_to_be_replaced')
            //     // ),
            //     'mod_opt_test' => array(
            //         'input_type'  => 'select',
            //         'title'       => __('Display an image', 'text_domain_to_be_replaced'),
            //         'default'     => 'featured'
            //     ),
            // ),
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
                    'default'     => true
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
 *  LOAD AND REGISTER HEADING MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_heading_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_module',
        'name' => __( 'Heading', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_heading_module',
        'validate_callback' => '\Nimble\validate_callback__czr_heading_module',
        'starting_value' => array(
            'heading_text' => 'This is a heading.'
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Heading', 'text_domain_to_be_replaced' ),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'heading_text' => array(
                                'input_type'         => 'text',
                                'title'              => __( 'Heading text', 'text_domain_to_be_replaced' ),
                                'default'            => '',
                                'width-100'         => true,
                                // The following might be useful to me, but it generates a pretty long list of allowed HTML tags
                                // would be great if we could have a "collapsible notice", collapsed by default that will expand on click
                                // similar to the section description used by wp, e.g. in the Additional CSS section
                                'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),
                                // 'notice_before'      => sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ),
                                //     '<code>' . czr_heading_module_text_allowed_tags() . '</code>', 'text_domain_to_be_replaced' ),

                            ),
                            'heading_tag' => array(
                                'input_type'         => 'select',
                                'title'              => __( 'Heading tag', 'text_domain_to_be_replaced' ),
                                'default'            => 'h1',
                                'choices'            => sek_get_select_options_for_input_id( 'heading_tag' )
                            ),
                            'h_alignment_css'        => array(
                                'input_type'         => 'h_text_alignment',
                                'title'              => __( 'Alignment', 'text_domain_to_be_replaced' ),
                                'default'            => is_rtl() ? 'right' : 'left',
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment'
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Font style', 'text_domain_to_be_replaced' ),
                        'attributes' => 'data-sek-google-font-tab="true"',
                        'inputs' => array(
                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family'
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'font_size',
                                'title'       => __( 'Font size in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size'
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __( 'Line height in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '24px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height'
                            ),//24,//"20px",
                            'font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,

                            'letter_spacing_css'  => array(
                                'input_type'  => 'number',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing'
                            ),//0,
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color'
                            ),//"#000000",
                            'color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover'
                            ),//"#000000",
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'heading___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
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
                            ),//false
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/heading_module_tmpl.php",
    );
}

function sanitize_callback__czr_heading_module( $value ) {
    if ( array_key_exists('heading_text', $value ) ) {
        //sanitize heading_text
        $value[ 'heading_text' ] = czr_heading_module_kses_text( $value[ 'heading_text' ] );
    }
    return $value;
    //return new \WP_Error('required' ,'heading did not pass sanitization');
}

// @see SEK_CZR_Dyn_Register::set_dyn_setting_args
// Only the boolean true or a WP_error object will be valid returned value considered when validating
function validate_callback__czr_heading_module( $value ) {
    //return new \WP_Error('required' ,'heading did not pass ');
    return true;
}

/**
 * Filter headings text output WordPress's KSES API.
 */
function czr_heading_module_kses_text( $content = '' ) {
    $allowed_tags = wp_kses_allowed_html( 'sek-heading_text' );

    // Return KSES'ed content, allowing the above tags.
    return wp_kses( $content, $allowed_tags );
}

add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\czr_heading_module_text_get_allowedtags', 10, 2 );
/**
 * Get headings text allowed tags
 */
function czr_heading_module_text_get_allowedtags( $tags, $context ) {
    if ( 'sek-heading_text' != $context ) {
        return $tags;
    }

    // limit wp_kses allowed tags.
    return array(
        'a' => array(
            'href' => 1,
            'rel' => 1,
            'rev' => 1,
            'name' => 1,
            'target' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'b' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'big' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'br' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'del' => array(
            'datetime' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'em' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'i' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ins' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'span' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'small' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strike' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strong' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sub' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sup' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'u' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
    );
}

/**
 * Display all of the allowed tags in HTML format with attributes.
 *
 * This is useful for displaying which elements and attributes are supported
 * see wp-includes/general-template::allowed_tags()
 */
function czr_heading_module_text_allowed_tags() {
    $allowedtags = wp_kses_allowed_html( 'sek-heading_text' );
    $allowed = '';
    foreach ( (array) $allowedtags as $tag => $attributes ) {
        $allowed .= '<'.$tag;
        if ( 0 < count($attributes) ) {
            foreach ( $attributes as $attribute => $limits ) {
                $allowed .= ' '.$attribute.'=""';
            }
        }
        $allowed .= '> ';
    }
    return htmlentities( $allowed );
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SPACER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });

function sek_get_module_params_for_czr_spacer_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_spacer_module',
        'name' => __('Spacer', 'text_domain_to_be_replaced'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height_css' => array(
                    'input_type'  => 'number',
                    'min'         => 1,
                    'title'       => __('Space in pixels', 'text_domain_to_be_replaced'),
                    'default'     => 20,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
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
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_divider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_divider_module',
        'name' => __('Divider', 'text_domain_to_be_replaced'),
        'css_selectors' => array( '.sek-divider' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border_top_width_css' => array(
                    'input_type'  => 'range_slider',
                    'title'       => __('Weight', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 50,
                    'unit' => 'px',
                    'default' => 1,
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
                    'input_type'  => 'range_slider',
                    'title'       => __('Width', 'text_domain_to_be_replaced'),
                    'min' => 1,
                    'max' => 100,
                    'unit' => '%',
                    'default' => 100,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'width'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => '.sek-module-inner',
                    'css_identifier' => 'h_alignment'
                ),
                'v_spacing_css' => array(
                    'input_type'  => 'number',
                    'title'       => __('Space before and after in pixels', 'text_domain_to_be_replaced'),
                    'min'         => 1,
                    'max'         => 100,
                    'default'     => 15,
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
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_icon_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_module',
        'name' => __('Icon', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'icon' =>  'far fa-star',
            'font_size_css' => '40px',
            'color_css' => '#707070'
        ),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_icon_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an Icon', 'text_domain_to_be_replaced'),
                    //'default'     => 'no-link'
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
                    'default'     => false
                ),
                'font_size_css' => array(
                    'input_type'  => 'font_size',
                    'title'       => __('Size in pixels', 'text_domain_to_be_replaced'),
                    'default'     => '16px',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment'
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'color_hover_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color_hover'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/icon_module_tmpl.php",
        'front_assets' => array(
              'czr-font-awesome' => array(
                  'type' => 'css',
                  //'handle' => 'czr-font-awesome',
                  'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
                  //'deps' => array()
              )
        )
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MAP MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_map_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_map_module',
        'name' => __('Map', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_gmap_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        //'css_selectors' => array( '.sek-module-inner' ),
        'starting_value' => array(
            'address'       => 'Nice, France',
            'zoom'          => 10,
            'height_css'    => 200
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
                    'input_type'  => 'range_slider',
                    'title'       => __( 'Zoom', 'text_domain_to_be_replaced' ),
                    'min' => 1,
                    'max' => 20,
                    'unit' => '',
                    'default' => 10,
                ),
                'height_css' => array(
                    'input_type'  => 'number',
                    'title'       => __( 'Height in pixels', 'text_domain_to_be_replaced' ),
                    'min' => 1,
                    'default' => 200,
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
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_quote_module() {
    $quote_font_selectors = array( '.sek-quote-content', '.sek-quote-content p', '.sek-quote-content ul', '.sek-quote-content ol', '.sek-quote-content a' );
    $cite_font_selectors  = array( '.sek-cite', '.sek-cite a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_module',
        'name' => __('Quote', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_callback__czr_quote_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'quote_text'  => 'Hey, careful, man, there\'s a beverage here!',
            'cite_text'   => 'The Dude in <a href="https://www.imdb.com/title/tt0118715/quotes/qt0464770" rel="nofollow noopener noreferrer" target="_blank">The Big Lebowski</a>',
            'quote_design' => 'border-before'
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Quote', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
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
                                'input_type'  => 'font_size',
                                'title'       => __( 'Font size in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $quote_font_selectors,
                            ),//16,//"14px",
                            'quote_line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __( 'Line height in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '24px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $quote_font_selectors,
                            ),//24,//"20px",
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
                                'input_type'  => 'number',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $quote_font_selectors,
                            ),//0,
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
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'quote___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
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
                            ),
                        )

                    ),
                    array(
                        'title' => __( 'Cite', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
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
                                'input_type'  => 'font_size',
                                'title'       => __( 'Font size in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $cite_font_selectors,
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __( 'Line height in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '24px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $cite_font_selectors,
                            ),//24,//"20px",
                            'cite_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
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
                                'input_type'  => 'number',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $cite_font_selectors,
                            ),//0,
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
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'cite___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
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
                            ),
                        ),
                    ),
                    array(
                        'title' => __( 'Design', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'quote_design' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Design', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'choices'     => sek_get_select_options_for_input_id( 'quote_design' )
                            ),
                            'border_width_css' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __( 'Border weight', 'text_domain_to_be_replaced' ),
                                'min' => 1,
                                'max' => 80,
                                'unit' => 'px',
                                'default' => 5,
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
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/quote_module_tmpl.php",
    );
}




function sanitize_callback__czr_quote_module( $value ) {
    if ( array_key_exists( 'quote_text', $value ) ) {
        //sanitize quote_text
        $value[ 'quote_text' ] = czr_quote_module_kses_quote_text( $value[ 'quote_text' ] );
    }
    if ( array_key_exists( 'cite_text', $value ) ) {
        //sanitize cite_text
        $value[ 'cite_text' ] = czr_quote_module_kses_cite_text( $value[ 'cite_text' ] );
    }
    return $value;
}


/**
 * Filter quote text output WordPress's KSES API.
 */
function czr_quote_module_kses_quote_text( $content = '' ) {
    $allowed_tags = wp_kses_allowed_html( 'sek-quote_text' );

    // Return KSES'ed content, allowing the above tags.
    return wp_kses( $content, $allowed_tags );
}


/**
 * Filter cite text output WordPress's KSES API.
 */
function czr_quote_module_kses_cite_text( $content = '' ) {
    $allowed_tags = wp_kses_allowed_html( 'sek-cite_text' );

    // Return KSES'ed content, allowing the above tags.
    return wp_kses( $content, $allowed_tags );
}



add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\czr_quote_module_quote_text_get_allowedtags', 10, 2 );
add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\czr_quote_module_cite_text_get_allowedtags', 10, 2 );
/**
 * Get quote text allowed tags
 * blockquote specifications:
 * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/blockquote
 * can contain: https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#Flow_content
 *
 * here we use just a subset of the allowed tags in a blockquote
 */
function czr_quote_module_quote_text_get_allowedtags( $tags, $context ) {
    if ( 'sek-quote_text' != $context ) {
        return $tags;
    }

    // limit wp_kses allowed tags.
    return array(
        'a' => array(
            'href' => 1,
            'rel' => 1,
            'rev' => 1,
            'name' => 1,
            'target' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'abbr' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'b' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'big' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'br' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'button' => array(
            'disabled' => 1,
            'name' => 1,
            'type' => 1,
            'value' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'code' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'del' => array(
            'datetime' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'em' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'i' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ins' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'li' => array(
            'align' => 1,
            'value' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'p' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'q' => array(
            'cite' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'section' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'small' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'span' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strike' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strong' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sub' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sup' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'u' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ol' => array(
            'start' => 1,
            'type' => 1,
            'reversed' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ul' => array(
            'type' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
    );
}


/**
 * Get quote text allowed tags
 * cite specifications:
 * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite
 * can contain: https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#Phrasing_content
 *
 * here we use just a subset of the allowed tags in a cite
 */
function czr_quote_module_cite_text_get_allowedtags( $tags, $context ) {
    if ( 'sek-cite_text' != $context ) {
        return $tags;
    }


    // limit wp_kses allowed tags.
    return array(
        'a' => array(
            'href' => 1,
            'rel' => 1,
            'rev' => 1,
            'name' => 1,
            'target' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'abbr' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'b' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'big' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'br' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'button' => array(
            'disabled' => 1,
            'name' => 1,
            'type' => 1,
            'value' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'code' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'del' => array(
            'datetime' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'em' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'i' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ins' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'span' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'small' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strike' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strong' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sub' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sup' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'u' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
    );
}
?>
<?php
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
        // set the css rules for columns
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        // filter fired in sek_css_rules_sniffer_walker()
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

        $this->sek_css_rules_sniffer_walker();
    }


    // Fired in the constructor
    // Walk the level tree and build rules when needed
    // The rules are filtered when some conditions are met.
    // This allows us to schedule the css rules addition remotely :
    // - from the module registration php file
    // - from the generic input types ( @see sek_add_css_rules_for_generic_css_input_types() )
    public function sek_css_rules_sniffer_walker( $level = null, $parent_level = array() ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        // The parent level is set when the function is invoked recursively, from a level where we actually have a 'level' property
        if ( ! empty( $parent_level ) ) {
            $this -> parent_level_model = $parent_level;
        }

        // If the current level is a module, check if the module has more specific css selectors specified on registration
        $module_level_css_selectors = null;
        $registered_input_list = null;
        if ( ! empty( $parent_level['module_type'] ) ) {
            $module_level_css_selectors = sek_get_registered_module_type_property( $parent_level['module_type'], 'css_selectors' );
            $registered_input_list = sek_get_registered_module_input_list( $parent_level['module_type'] );
        }

        foreach ( $level as $key => $entry ) {
             $rules = array();
            // Populate rules for sections / columns / modules
            if ( !empty( $entry[ 'level' ] ) && ( !empty( $entry[ 'options' ] ) || !empty( $entry[ 'width' ] ) ) ) {
                // build rules for level options => section / column / module
                $rules = apply_filters( 'sek_add_css_rules_for_level_options', $rules, $entry );
            }

            // populate rules for modules values
            if ( !empty( $entry[ 'level' ] ) && 'module' === $entry['level'] ) {
                // build rules for modules
                $rules = apply_filters( 'sek_add_css_rules_for_modules', $rules, $entry );
            }

            // When we are inside the associative arrays of the module 'value' or the level 'options' entries
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( empty( $entry[ 'level' ] ) && is_string( $key ) && 1 < strlen( $key ) ) {
                // we need to have a level model set
                if ( !empty( $parent_level ) && is_array( $parent_level ) && ! empty( $parent_level['module_type'] ) ) {
                    // the input_id candidate to filter is the $key
                    $input_id_candidate = $key;
                    // let's skip the $key that are reserved for the structure of the sektion tree
                    // ! in_array( $key, [ 'level', 'collection', 'id', 'module_type', 'options', 'value' ] )
                    // The generic rules must be suffixed with '_css'
                    if ( false !== strpos( $input_id_candidate, '_css') ) {
                        if ( is_array( $registered_input_list ) && ! empty( $registered_input_list[ $input_id_candidate ] ) && ! empty( $registered_input_list[ $input_id_candidate ]['css_identifier'] ) ) {
                            $rules = apply_filters(
                                "sek_add_css_rules_for_input_id",
                                $rules,// <= the in-progress array of css rules to be populated
                                $entry,// <= the css property value
                                $input_id_candidate, // <= the unique input_id as it as been declared on module registration
                                $registered_input_list,// <= the full list of input for the module
                                $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                $module_level_css_selectors // <= if the parent is a module, a default set of css_selectors might have been specified on module registration
                            );
                        } else {
                            sek_error_log( __FUNCTION__ . ' => missing the css_identifier param when registering module ' . $parent_level['module_type'] . ' for a css input candidate : ' . $key, $parent_level );
                        }
                    }
                }//if
            }//if

            // populates the rules collection
            if ( !empty( $rules ) ) {

                //TODO: MAKE SURE RULE ARE NORMALIZED
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

            // keep walking if the current $entry is an array
            // make sure that the parent_level_model is set right before jumping down to the next level
            if ( is_array( $entry ) ) {
                // Can we set a parent level ?
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $parent_level = $entry;
                }
                // Let's go recursive
                $this->sek_css_rules_sniffer_walker( $entry, $parent_level );


            }
            // Reset the parent level model because it might have been modified after walking the sublevels
            if ( ! empty( $parent_level ) ) {
                $this -> parent_level_model = $parent_level;
            }
        }//foreach
    }//sek_css_rules_sniffer_walker()



    // @return void()
    // populates the css rules ::collection property, organized by media queries
    public function sek_populate( $selector, $css_rules, $mq = '' ) {
        if ( ! is_string( $selector ) )
            return;
        if ( ! is_string( $css_rules ) )
            return;

        // Assign a default media device
        //TODO: allowed media query?
        $mq_device = 'all_devices';

        // If a media query is requested, build it
        if ( !empty( $mq ) ) {
            if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
            } else {
                $mq_device = $mq;
            }
        }

        // if the media query for this device is not yet added, add it
        if ( !isset( $this->collection[ $mq_device ] ) ) {
            $this->collection[ $mq_device ] = array();
        }

        if ( !isset( $this->collection[ $mq_device ][ $selector ] ) ) {
            $this->collection[ $mq_device ][ $selector ] = array();
        }

        $this->collection[ $mq_device ][ $selector ][] = $css_rules;
    }//sek_populate



    // @return string
    private function sek_maybe_wrap_in_media_query( $css,  $mq_device = 'all_devices' ) {
        if ( 'all_devices' === $mq_device ) {
            return $css;
        }
        return sprintf( '@media(%1$s){%2$s}', $mq_device, $css);
    }


    // sorts the media queries from all_devices to the smallest width
    // This doesn't make the difference between max-width and min-width
    // @return integer
    private function user_defined_array_key_sort_fn($a, $b) {
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

    //@returns a stringified stylesheet, ready to be printed on the page or in a file
    public function get_stylesheet() {
        $css = '';
        if ( ! is_array( $this->collection ) || empty( $this->collection ) )
          return $css;
        // Sort the collection by media queries
        uksort( $this->collection, array( $this, 'user_defined_array_key_sort_fn' ) );

        // process
        foreach ( $this->collection as $mq_device => $selectors ) {
            $_css = '';
            foreach ( $selectors as $selector => $css_rules ) {
                $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                $_css .=  $selector . '{' . $css_rules . '}';
            }
            $_css = $this->sek_maybe_wrap_in_media_query( $_css, $mq_device );
            $css .= $_css;
        }
        return $css;
    }








    // hook : sek_add_css_rules_for_level_options
    public function sek_add_rules_for_column_width( $rules, $level ) {
        $width   = empty( $level[ 'width' ] ) || !is_numeric( $level[ 'width' ] ) ? '' : $level['width'];

        //width
        if ( empty( $width ) )
          return $rules;

        $css_rules = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width );
        $rules[] = array(
            'selector'      => '.sek-column[data-sek-id="'.$level['id'].'"]',
            'css_rules'     => $css_rules,
            'mq'            => 'min-width:' . self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ] .'px'
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

        //normalize some parameters
        $args[ 'dep' ]          = is_array( $args[ 'dep' ] ) ? $args[ 'dep' ]  : array();
        $args[ 'priority']      = is_numeric( $args[ 'priority' ] ) ? $args[ 'priority' ] : $this->priority;

        //turn $args into object properties
        foreach ( $args as $key => $value ) {
            if ( property_exists( $this, $key ) && array_key_exists( $key, $defaults) ) {
                    $this->$key = $value;
            }
        }

        if ( empty( $this -> skope_id ) ) {
            throw new Exception( 'Sek_Dyn_CSS_Handler => __construct => skope_id not provided' );
        }

        //build no parameterized properties
        $this->_sek_dyn_css_set_properties();

        // Possible scenarios :
        // 1) customizing :
        //    the css is always printed inline. If there's already an existing css file for this skope_id, it's not enqueued.
        // 2) saving in the customizer :
        //    the css file is written in a "force_rewrite" mode, meaning that any existing css file gets re-written.
        //    There's no enqueing scheduled, 'customizer_save' mode.
        // 3) front, user logged in + 'customize' capabilities :
        //    the css file is re-written on each page load + enqueued. If writing a css file is not possible, we fallback on inline printing.
        // 4) front, user not logged in :
        //    the normal behaviour is that the css file is enqueued.
        //    It should have been written when saving in the customizer. If no file available, we try to write it. If writing a css file is not possible, we fallback on inline printing.
        if ( is_customize_preview() || ! $this->_sek_dyn_css_file_exists() || $this->force_rewrite || $this->customizer_save ) {
            $this->sek_model = sek_get_skoped_seks( $this -> skope_id );

            //build stylesheet
            $this->builder = new Sek_Dyn_CSS_Builder( $this->sek_model );

            // now that the stylesheet is ready let's cache it
            $this->css_string_to_enqueue_or_print = (string)$this->builder-> get_stylesheet();
        }

        //hook setup for printing or enqueuing
        //bail if "customizer_save" == true, typically when saving the customizer settings @see Nimble_Customizer_Setting::update()
        if ( ! $this->customizer_save ) {
            $this->_schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook();
        } else {
            if ( $this->css_string_to_enqueue_or_print ) {
                $this->sek_dyn_css_maybe_write_css_file();
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
        $this->url                  = $this->_sek_dyn_css_build_url();

        $this->file_exists          = $this->_sek_dyn_css_file_exists();

        if ( self::MODE_FILE == $this->mode ) {
            if ( ! $this->_sek_dyn_css_write_file_is_possible() ) {
                $this->mode = self::MODE_INLINE;
            }
        }
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
            //enqueue or print
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
        // CSS FILE
        //case enqueue file : front end + user with customize caps not logged in
        if ( self::MODE_FILE == $this->mode ) {
            //in case we need to write the file before enqueuing
            //1) $this->css_string_to_enqueue_or_print must exists
            //2) we might need to force the rewrite even if the file exists or to write it if the file doesn't exist
            if ( $this->css_string_to_enqueue_or_print ) {
                if ( $this->force_rewrite || ( !$this->file_exists && $this->force_write ) ) {
                    $this->file_exists = $this->sek_dyn_css_maybe_write_css_file();
                }
            }

            //if the file exists
            if ( $this->file_exists ) {
                //print the needed html to enqueue a style only if we're in wp_footer or wp_head
                if ( in_array( current_filter(), array( 'wp_footer', 'wp_head' ) ) ) {
                    /*
                    * TODO: make sure all the deps are enqueued
                    */
                    printf( '<link rel="stylesheet" id="sek-dyn-%1$s-css" href="%2$s" type="text/css" media="all" />',
                        $this->id,
                        //this resource version is built upon the file last modification time
                        add_query_arg( array( 'ver' => filemtime($this->uri) ), $this->url )
                    );
                } else {
                    //this resource version is built upon the file last modification time
                    wp_enqueue_style( "sek-dyn-{$this->id}", $this->url, $this->dep, filemtime($this->uri) );
                }

                $this->enqueued_or_printed = true;
            }

        }// if ( self::MODE_FILE )


        //if $this->mode != 'file' or the file enqueuing didn't go through (fall back)
        //print inline style
        if ( $this->css_string_to_enqueue_or_print && ! $this->enqueued_or_printed ) {
            $dep =  array_pop( $this->dep );

            if ( !$dep || wp_style_is( $dep, 'done' ) || !wp_style_is( $dep, 'done' ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                printf( '<style id="sek-%1$s" type="text/css" media="all">%2$s</style>', $this->id, $this->css_string_to_enqueue_or_print );
            } else {
                //not sure
                wp_add_inline_style( $dep , $this->css_string_to_enqueue_or_print );
            }

            $this->mode     = self::MODE_INLINE;
            $this->enqueued_or_printed = true;
        }

        // GOOGLE FONTS
        // When customizing
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

    // hook : wp_head
    // or fired directly when ajaxing
    // When ajaxing, the link#sek-gfonts-{$this->id} gets removed from the dom and replaced by this string
    function sek_gfont_print( $print_candidates ) {
       if ( ! empty( $print_candidates ) ) {
            printf('<link rel="stylesheet" id="sek-gfonts-%1$s" href="%2$s">',
                $this->id,
                "//fonts.googleapis.com/css?family={$print_candidates}"
            );
        }
    }

    //@return string
    private function sek_get_gfont_print_candidates() {
        // in a front end, not logged in scenario, the sek_model is 'not set', because the stylesheet has not been re-built in the constructor
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

        // Can we create the folder?
        if ( ! $wp_filesystem->is_dir( $base_uri ) ) {
            $error = !wp_mkdir_p( $base_uri );
        }

        if ( $error ) {
            return false;
        }

        if ( ! file_exists( $index_path = wp_normalize_path( trailingslashit( $base_uri ) . 'index.php' ) ) ) {
            // predefined mode settings for WP files
            $wp_filesystem->put_contents( $index_path, "<?php\n// Silence is golden.\n", FS_CHMOD_FILE );
        }


        if ( ! wp_is_writable( $base_uri ) ) {
            return false;
        }

        //actual write try and update the file_exists status
        $this->file_exists = $wp_filesystem->put_contents(
            $this->uri,
            $this->css_string_to_enqueue_or_print,
            // predefined mode settings for WP files
            FS_CHMOD_FILE
        );

        //return whether or not the writing succeeded
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
        return $wp_filesystem->is_readable( $this->uri );
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
        $base_uri = isset( $this->base_uri ) ? $this->base_uri : $this->_sek_dyn_css_build_base_uri();
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
        $base_url = isset( $this->base_uri ) ? $this->base_url : $this->_sek_dyn_css_build_base_uri();
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
        //since 4.5.0
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
        //since 4.5.0
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return trailingslashit( $upload_dir['baseurl'] ) . $relative_base_path;
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
    //TODO: try to extend this to other methods e.g. FTP when FTP credentials are already defined
    private function _sek_dyn_css_write_file_is_possible() {
        $upload_dir      = wp_get_upload_dir();
        //Note: if the 'uploads' dir has not been created, this check will not pass, hence no file will never be created
        //unless something else creates the 'uploads' dir
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

        // Initialize the WordPress filesystem.
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }
    }

}

?><?php
// filter declared in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker()
// $rules = apply_filters( "sek_add_css_rules_for_input_id", $rules, $key, $entry, $this -> parent_level );
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

    // SPECIFIC CSS SELECTOR AT MODULE LEVEL
    // are there more specific css selectors specified on module registration ?
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
    // for a module level, increase the default specifity to the .sek-module-inner container by default
    // @fixes https://github.com/presscustomizr/nimble-builder/issues/85
    else if ( 'module' === $parent_level['level'] ) {
        $selector .= ' .sek-module-inner';
    }


    // SPECIFIC CSS SELECTOR AT INPUT LEVEL
    // => Overrides the module level specific selector, if it was set.
    if ( 'module' === $parent_level['level'] ) {
        //$start = microtime(true) * 1000;
        if ( ! is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input list' );
        } else if ( is_array( $registered_input_list ) && empty( $registered_input_list[ $input_id ] ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input id ' . $input_id . ' in input list for module type ' . $parent_level['module_type'] );
        }
        if ( is_array( $registered_input_list ) && ! empty( $registered_input_list[ $input_id ] ) && ! empty( $registered_input_list[ $input_id ]['css_selectors'] ) ) {
            // reset the selector to the level id selector, in case it was previously set spcifically at the module level
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
            //sek_error_log( '$input_level_css_selectors', $selector );
        }
        // sek_error_log( 'input_id', $input_id );
        // sek_error_log( '$registered_input_list', $registered_input_list );

        // $end = microtime(true) * 1000;
        // $time_elapsed_secs = $end - $start;
        // sek_error_log('$time_elapsed_secs to get module params', $time_elapsed_secs );
    }


    $mq = null;
    $properties_to_render = array();

    switch ( $css_identifier ) {
        case 'font_size' :
            $properties_to_render['font-size'] = $value;
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
            //$selector = '[data-sek-id="'.$parent_level['id'].'"]:hover';
            // Add ':hover to each selectors'
            $new_selectors = array();
            $exploded = explode(',', $selector);
            foreach ( $exploded as $sel ) {
                $new_selectors[] = $sel.':hover';
            }

            $selector = implode(',', $new_selectors);
            $properties_to_render['color'] = $value;
        break;
        case 'h_alignment' :
            $properties_to_render['text-align'] = $value;
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
            // Preprocess the selected font family
            //font: [font-stretch] [font-style] [font-variant] [font-weight] [font-size]/[line-height] [font-family];
            //special treatment for font-family
            if ( false != strstr( $value, '[gfont]') ) {
                $split = explode(":", $family);
                $family = $split[0];
                //only numbers for font-weight. 400 is default
                $properties_to_render['font-weight']    = $split[1] ? preg_replace('/\D/', '', $split[1]) : '';
                $properties_to_render['font-weight']    = empty($properties_to_render['font-weight']) ? 400 : $properties_to_render['font-weight'];
                $properties_to_render['font-style']     = ( $split[1] && strstr($split[1], 'italic') ) ? 'italic' : 'normal';
            }

            $family = str_replace( array( '[gfont]', '[cfont]') , '' , $family );
            $properties_to_render['font-family'] = false != strstr( $value, '[cfont]') ? $family : "'" . str_replace( '+' , ' ' , $family ) . "'";
        break;

        /* Spacer */
        case 'height' :
            $properties_to_render['height'] = $value > 0 ? $value . 'px' : '1px';
        break;
        /* Quote border */
        case 'border_width' :
            $properties_to_render['border-width'] = $value > 0 ? $value . 'px' : '1px';
        break;
        case 'border_color' :
            $properties_to_render['border-color'] = $value ? $value : '';
        break;
        /* Divider */
        case 'border_top_width' :
            $properties_to_render['border-top-width'] = $value > 0 ? $value . 'px' : '1px';
        break;
        case 'border_top_style' :
            $properties_to_render['border-top-style'] = $value ? $value : 'solid';
        break;
        case 'border_top_color' :
            $properties_to_render['border-top-color'] = $value ? $value : '#5a5a5a';
        break;
        case 'width' :
            $properties_to_render['width'] = in_array( $value, range( 1, 100 ) ) ? $value . '%' : 100 . '%';
        break;
        case 'v_spacing' :
            $value = in_array( $value, range( 1, 100 ) ) ? $value . 'px' : '15px' ;
            $properties_to_render = array(
                'margin-top'  => $value,
                'margin-bottom' => $value
            );
        break;
        //not used at the moment, but it might if we want to display the divider as block (e.g. a div instead of a span)
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

        // The default is simply there to let us know if a css_identifier is missing
        default :
            sek_error_log( __FUNCTION__ . ' => the css_identifier : ' . $css_identifier . ' has no css rules defined for input id ' . $input_id );
        break;
    }//switch

    // when the module has an '*_flag_important' input,
    // => check if the input_id belongs to the list of "important_input_list"
    // => and maybe flag the css rules with !important
    if ( ! empty( $properties_to_render ) ) {
        $important = sek_is_flagged_important( $input_id, $parent_level, $registered_input_list );
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


// @return boolean
function sek_is_flagged_important( $input_id, $parent_level, $registered_input_list ) {
    // is the important flag on ?
    $important = false;
    if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
        // loop on the module input values, and find _flag_important.
        // then check if the current input_id, is in the list of important_input_list
        foreach( $parent_level['value'] as $id => $input_value ) {
            if ( false !== strpos( $id, '_flag_important' ) ) {
                //sek_error_log( __FUNCTION__ . ' => $registered_input_list ?', $registered_input_list );
                if ( is_array( $registered_input_list ) && array_key_exists( $id, $registered_input_list ) ) {
                    if ( empty( $registered_input_list[ $id ][ 'important_input_list' ] ) ) {
                        sek_error_log( __FUNCTION__ . ' => missing important_input_list for input id ' . $id );
                    } else {
                        $important_list_candidate = $registered_input_list[ $id ][ 'important_input_list' ];
                        //sek_error_log( __FUNCTION__ . ' => ALORS ?', $important_list_candidate );
                        if ( in_array( $input_id, $important_list_candidate ) ) {
                            $important = (bool)sek_is_checked( $input_value );
                        }
                    }
                }
            }
        }
    }
    return $important;
}
?><?php
////////////////////////////////////////////////////////////////
// SEK Front Class
if ( ! class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $local_seks = 'not_cached';// <= used to cache the sektions for the local skope_id
        public $model = array();//<= when rendering, the current level model
        public $parent_model = array();//<= when rendering, the current parent model
        public $default_models = array();// <= will be populated to cache the default models when invoking sek_get_default_module_model
        public $cached_input_lists = array(); // <= will be populated to cache the input_list of each registered module. Useful when we need to get info like css_selector for a particular input type or id.
        public $ajax_action_map = array();

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SEK_Front_Render ) )
              self::$instance = new SEK_Front_Render_Css( $params );
            return self::$instance;
        }

        /////////////////////////////////////////////////////////////////
        // <CONSTRUCTOR>
        function __construct( $params = array() ) {
            //AJAX
            $this -> _schedule_front_ajax_actions();
            // ASSETS
            $this -> _schedule_front_and_preview_assets_printing();
            // RENDERING
            $this -> _schedule_front_rendering();
            // RENDERING
            $this -> _setup_hook_for_front_css_printing_or_enqueuing();

            // TEST
            //add_action( 'wp_ajax_sek_import_attachment', array( $this, '__import__' ) );
        }//__construct

        /////////////////////////////////////////////////////////////////
        // TEST IMG IMPORT
        // hook : wp_ajax_sek_import_attachment
        function __import__() {
            $relative_path = $_POST['rel_path'];

            // Generate the file name from the url.
            $filename = 'nimble_asset_' . basename( $relative_path );
            $args = array(
                'posts_per_page' => 1,
                'post_type'      => 'attachment',
                'name'           => trim ( $filename ),
            );

            // Make sure this img has not already been uploaded
            $get_attachment = new WP_Query( $args );
            //error_log( print_r( $get_attachment->posts, true ) );
            if ( is_array( $get_attachment->posts ) && array_key_exists(0, $get_attachment->posts) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => file already uploaded : ' . $relative_path );
                return;
            }

            // Does it exists ?
            //error_log( "dirname(__FILE__ ) . $relative_path => " . dirname(__FILE__ ) . $relative_path );
            //error_log("file_exists( dirname(__FILE__ ) . $relative_path => " . file_exists( dirname(__FILE__ ) . $relative_path ) );
            if ( ! file_exists( dirname(__FILE__ ) . $relative_path ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no file found for relative path : ' . $relative_path );
                return;
            }

            // Does it return a 200 code ?
            $url = NIMBLE_BASE_URL . '/inc/sektions'. $relative_path;
            //error_log('$url' .$url );
            $url_content = wp_safe_remote_get( $url );
            if ( '404' == $url_content['response']['code'] ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => 404 response when wp_safe_remote_get() url : ' . $url );
                return;
            }
            $file_content = wp_remote_retrieve_body( $url_content );
            //error_log( print_r( $img_content['response'], true ) );

            // Is it something ?
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

            // Set the mime type
            $info = wp_check_filetype( $upload['file'] );
            if ( $info ) {
                $attachment['post_mime_type'] = $info['type'];
            } else {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no info available with wp_check_filetype() when setting the mime type of img : ' . $url );
                return;
            }

            $attachment_id = wp_insert_attachment( $attachment, $upload['file'] );
            // Did everything went well when attempting to insert ?
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
    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Ajax' ) ) :
    class SEK_Front_Ajax extends SEK_Front_Construct {
        // Fired in __construct()
        function _schedule_front_ajax_actions() {
            add_action( 'wp_ajax_sek_get_content', array( $this, 'sek_get_level_content_for_injection' ) );
            //add_action( 'wp_ajax_sek_get_preview_ui_element', array( $this, 'sek_get_ui_content_for_injection' ) );

            // This is the list of accepted actions
            $this -> ajax_action_map = array(
                  'sek-add-section',
                  'sek-remove-section',
                  'sek-duplicate-section',
                  'sek-add-content-in-new-sektion',

                  // add, duplicate, remove column is a re-rendering of the parent sektion collection
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

        // hook : 'wp_ajax_sek_get_html_for_injection'
        function sek_get_level_content_for_injection( $params ) {
            // sek_error_log( 'ajax sek_get_level_content_for_injection', $_POST );
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

            if ( ! isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
                wp_send_json_error(  __FUNCTION__ . ' => missing skope_id' );
            }

            if ( ! isset( $_POST['sek_action'] ) || empty( $_POST['sek_action'] ) ) {
                wp_send_json_error(  __FUNCTION__ . ' => missing sek_action' );
            }
            $sek_action = $_POST['sek_action'];

            $exported_setting_validities = array();

            // CHECK THE SETTING VALIDITIES BEFORE RENDERING
            // When a module has been registered with a sanitize_callback, we can collect the possible problems here before sending the response.
            // Then, on ajax.done(), in SekPreviewPrototype::schedulePanelMsgReactions, we will send the setting validities object to the panel
            if ( is_customize_preview() ) {
                global $wp_customize;
                // prepare the setting validities so we can pass them when sending the ajax response
                $setting_validities = $wp_customize->validate_setting_values( $wp_customize->unsanitized_post_values() );
                $raw_exported_setting_validities = array_map( array( $wp_customize, 'prepare_setting_validity_for_js' ), $setting_validities );

                // filter the setting validity to only keep the __nimble__ prefixed ui settings
                $exported_setting_validities = array();
                foreach( $raw_exported_setting_validities as $setting_id => $validity ) {
                    // don't consider the not Nimble UI settings, not starting with __nimble__
                    if ( false === strpos( $setting_id , NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) )
                      continue;
                    $exported_setting_validities[ $setting_id ] = $validity;
                }
            }

            // is this action possible ?
            if ( in_array( $sek_action, $this -> ajax_action_map ) ) {
                $html = $this -> sek_ajax_fetch_content( $sek_action );
                //sek_error_log('sek_ajax_fetch_content()', $html );
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
                wp_send_json_error(  __FUNCTION__ . ' => this ajax action ( ' . $sek_action . ' ) is not listed in the map ' );
            }


        }//sek_get_content_for_injection()


        // hook : add_filter( "sek_set_ajax_content___{$action}", array( $this, 'sek_ajax_fetch_content' ) );
        // $_POST looks like Array
        // (
        //     [action] => sek_get_content
        //     [withNonce] => false
        //     [id] => __nimble__0b7c85561448ab4eb8adb978
        //     [skope_id] => skp__post_page_home
        //     [sek_action] => sek-add-section
        //     [SEKFrontNonce] => 3713b8ac5c
        //     [customized] => {\"nimble___loop_start[skp__post_page_home]\":{...}}
        // )
        // @return string
        // @param $sek_action is $_POST['sek_action']
        private function sek_ajax_fetch_content( $sek_action = '' ) {
            // sek_error_log( 'sek_ajax_fetch_content', $_POST );
            // the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // since wp has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( $_POST['skope_id'] );
            if ( ! is_array( $sektionSettingValue ) ) {
                wp_send_json_error( __FUNCTION__ . ' => invalid sektionSettingValue => it should be an array().' );
                return;
            }
            if ( empty( $sek_action ) ) {
                wp_send_json_error(  __FUNCTION__ . ' => invalid sek_action param' );
                return;
            }
            $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
            if ( ! is_array( $sektion_collection ) ) {
                wp_send_json_error( __FUNCTION__ . ' => invalid sektion_collection => it should be an array().' );
                return;
            }

            $candidate_id = '';
            $collection = array();
            $level_model = array();

            $is_stylesheet = false;

            switch ( $sek_action ) {
                case 'sek-add-section' :
                // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                case 'sek-add-content-in-new-sektion' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;

                //only used for nested section
                case 'sek-remove-section' :
                    if ( ! array_key_exists( 'is_nested', $_POST ) || true !== json_decode( $_POST['is_nested'] ) ) {
                        wp_send_json_error(  __FUNCTION__ . ' sek-remove-section => the section must be nested in this ajax action' );
                        break;
                    } else {
                        // we need to set the parent_model here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    }
                break;

                case 'sek-duplicate-section' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;

                // We re-render the entire parent sektion collection in all cases
                case 'sek-add-column' :
                case 'sek-remove-column' :
                case 'sek-duplicate-column' :
                case 'sek-refresh-columns-in-sektion' :
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST['in_sektion'] ) ) {
                        wp_send_json_error(  __FUNCTION__ . ' ' . $sek_action .' => missing in_sektion param' );
                        break;
                    }
                    // sek_error_log('sektion_collection', $sektion_collection );
                    $level_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                break;

                // We re-render the entire parent column collection
                case 'sek-add-module' :
                case 'sek-remove-module' :
                case 'sek-refresh-modules-in-column' :
                case 'sek-duplicate-module' :
                    if ( ! array_key_exists( 'in_column', $_POST ) || empty( $_POST['in_column'] ) ) {
                        wp_send_json_error(  __FUNCTION__ . ' ' . $sek_action .' => missing in_column param' );
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
                        wp_send_json_error(  __FUNCTION__ . ' ' . $sek_action .' => missing resized_column' );
                        break;
                    }
                    $is_stylesheet = true;
                break;

                case 'sek-refresh-stylesheet' :
                    $is_stylesheet = true;
                break;

                 case 'sek-refresh-level' :
                    if ( ! array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __FUNCTION__ . ' ' . $sek_action .' => missing level id' );
                        break;
                    }
                    $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                break;
            }//Switch sek_action

            // sek_error_log('LEVEL MODEL WHEN AJAXING', $level_model );

            ob_start();

            if ( $is_stylesheet ) {
                $r = $this -> print_or_enqueue_seks_style( $_POST['skope_id'] );
            } else {
                if ( 'no_match' == $level_model ) {
                    wp_send_json_error(  __FUNCTION__ . ' ' . $sek_action . ' => missing level model' );
                    ob_end_clean();
                    return;
                }
                if ( empty( $level_model ) || ! is_array( $level_model ) ) {
                    wp_send_json_error( __FUNCTION__ . ' => empty or invalid $level_model' );
                    ob_end_clean();
                    return;
                }
                // note that in the case of a sektion nested inside a column, the parent_model has been set in the switch{ case : ... } above ,so we can access it in the ::render method to calculate the column width.
                $r = $this -> render( $level_model );
            }
            $html = ob_get_clean();
            if ( is_wp_error( $r ) ) {
                return $r;
            } else {
                // the $html content should not be empty when ajaxing a template
                // it can be empty when ajaxing a stylesheet
                if ( ! $is_stylesheet && empty( $html ) ) {
                      // return a new WP_Error that will be intercepted in sek_get_level_content_for_injection
                      $html = new WP_Error( 'ajax_fetch_content_error', __FUNCTION__ . ' => no content returned for sek_action : ' . $sek_action );
                }
                return apply_filters( "sek_set_ajax_content", $html, $sek_action );// this is sent with wp_send_json_success( apply_filters( 'sek_content_results', $html, $sek_action ) );
            }
        }














        // hook : 'wp_ajax_sek_get_preview_ui_element'
        /*function sek_get_ui_content_for_injection( $params ) {
            // error_log( print_r( $_POST, true ) );
            // error_log( print_r( sek_get_skoped_seks( "skp__post_page_home", 'loop_start' ), true ) );
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __FUNCTION__ . ' => unauthenticated' );
                return;
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
                wp_send_json_error( __FUNCTION__ . ' => user_cant_edit_theme_options');
                return;
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __FUNCTION__ . ' => customize_not_allowed' );
                return;
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __FUNCTION__ . ' => bad_method' );
                return;
            }

            if ( ! isset( $_POST['level'] ) || empty( $_POST['level'] ) ) {
                wp_send_json_error(  __FUNCTION__ . ' => missing level' );
                return;
            }
            if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) {
                wp_send_json_error(  __FUNCTION__ . ' => missing level id' );
                return;
            }
            if ( ! isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
                wp_send_json_error(  __FUNCTION__ . ' => missing skope_id' );
                return;
            }


            // the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // since wp has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( $_POST['skope_id'] );
            if ( ! is_array( $sektionSettingValue ) || ! array_key_exists( 'collection', $sektionSettingValue ) || ! is_array( $sektionSettingValue['collection'] ) ) {
                wp_send_json_error( __FUNCTION__ . ' => invalid sektionSettingValue' );
                return;
            }
            // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
            $this -> parent_model = sek_get_parent_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );
            $this -> model = sek_get_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );

            $level = $_POST['level'];

            $html = '';
            ob_start();
                load_template( dirname( __FILE__ ) . "/tmpl/ui/block-overlay-{$level}.php", false );
            $html = ob_get_clean();

            if ( empty( $html ) ) {
                wp_send_json_error( __FUNCTION__ . ' => no content returned' );
            } else {
                wp_send_json_success( apply_filters( 'sek_ui_content_results', $html ) );
            }
        }//sek_get_content_for_injection()*/

    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_and_preview_assets_printing() {
            // Load Front Assets
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_assets' ) );
            // Load customize preview js
            add_action ( 'customize_preview_init' , array( $this, 'sek_schedule_customize_preview_assets' ) );
        }

        // hook : 'wp_enqueue_scripts'
        function sek_enqueue_front_assets() {
            $rtl_suffix = is_rtl() ? '-rtl' : '';

            //wp_enqueue_style( 'google-material-icons', '//fonts.googleapis.com/icon?family=Material+Icons', array(), null, 'all' );
            //base custom CSS bootstrap inspired
            wp_enqueue_style(
                'sek-base',
                sprintf(
                    '%1$s/assets/front/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    defined('NIMBLE_DEV') && true === NIMBLE_DEV ? "sek-base{$rtl_suffix}.css" : "sek-base{$rtl_suffix}.min.css"
                ),
                array(),
                NIMBLE_ASSETS_VERSION,
                'all'
            );


            // wp_register_script(
            //     'sek-front-fmk-js',
            //     NIMBLE_BASE_URL . '/assets/front/js/_front_js_fmk.js',
            //     array( 'jquery', 'underscore'),
            //     time(),
            //     true
            // );
            wp_enqueue_script(
                'sek-main-js',
                NIMBLE_BASE_URL . '/assets/front/js/sek-main.js',
                array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                true
            );
            // wp_localize_script(
            //     'sek-main-js',
            //     'sekFrontLocalized',
            //     array(
            //         'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('NIMBLE_DEV') && true === NIMBLE_DEV ),
            //         'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            //         'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
            //     )
            // );
        }

        // enqueue / print customize preview assets
        // hook : 'customize_preview_init'
        function sek_schedule_customize_preview_assets() {
            // Load preview ui js tmpl
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                sprintf(
                    '%1$s/assets/czr/sek/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'sek-preview.css' : 'sek-preview.min.css'
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
            // Communication between preview and customizer panel
            wp_enqueue_script(
                'sek-customize-preview',
                sprintf(
                    '%1$s/assets/czr/sek/js/%2$s' ,
                    NIMBLE_BASE_URL,
                    defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'ccat-sek-preview.js' : 'ccat-sek-preview.min.js'
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
                        'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_domain_to_be_replaced'),
                        'Insert here' => __('Insert here', 'text_domain_to_be_replaced'),
                        'This content has been created with the WordPress editor.' => __('This content has been created with the WordPress editor.', 'text_domain' )
                    ),
                    'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('NIMBLE_DEV') && true === NIMBLE_DEV ),
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

        //'wp_footer' in the preview frame
        function sek_print_ui_tmpl() {
            ?>
              <script type="text/html" id="sek-tmpl-add-content-button">
                  <# //console.log( 'data', data ); #>
                  <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                    <div class="sek-add-content-button-wrapper">
                     <# var hook_location = ''; #>
                      <?php if ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) : ?>
                          <# if ( data.location ) {
                              hook_location = '( @hook : ' + data.location + ')';
                          } #>
                      <?php endif; ?>
                      <button title="<?php _e('Insert a new section', 'text_domain_to_be_replaced' ); ?> {{hook_location}}" data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:83px;">
                        <span class="sek-click-on-button-icon sek-click-on">+</span><span class="action-button-text"><?php _e('Insert a new section', 'text_domain_to_be_replaced' ); ?></span>
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
                        <?php if ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <# if ( ! data.is_last_possible_section ) { #>
                          <i class="fas fa-ellipsis-v sek-move-section" title="<?php _e( 'Move section', 'text_domain' ); ?>"></i>
                        <# } #>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">settings</i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-click-on="add-column" class="material-icons sek-click-on" title="<?php _e( 'Add a column', 'text_domain' ); ?>">view_column</i>
                        <# } #>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate section', 'text_domain' ); ?>">filter_none</i>
                        <i data-sek-click-on="pick-module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove section', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <span class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>"><?php _e( 'section', 'text_domain' ); ?></span>
                    <?php if ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-dyn-ui-wrapper sek-column-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-ellipsis-v sek-move-column" title="<?php _e( 'Move column', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">settings</i>
                        <# if ( ! data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-click-on="add-section" class="material-icons sek-click-on" title="<?php _e( 'Add a nested section', 'text_domain' ); ?>">account_balance_wallet</i>
                        <# } #>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate column', 'text_domain' ); ?>">filter_none</i>
                        <# } #>

                        <i data-sek-click-on="pick-module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <# if ( ! data.parent_is_single_column ) { #>
                          <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove column', 'text_domain' ); ?>">delete_forever</i>
                        <# } #>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <span class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>"><?php _e( 'column', 'text_domain' ); ?></span>
                    <?php if ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-module">
                  <div class="sek-dyn-ui-wrapper sek-module-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-ellipsis-v sek-move-module" title="<?php _e( 'Move module', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-module" class="fas fa-pencil-alt sek-tip sek-click-on" title="<?php _e( 'Edit module content', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">settings</i>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate module', 'text_domain' ); ?>">filter_none</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove module', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <#
                      var module_name = ! _.isEmpty( data.module_name ) ? data.module_name + ' ' + '<?php _e("module", "text_domain"); ?>' : '<?php _e("module", "text_domain"); ?>';
                    #>
                    <span class="sek-dyn-ui-location-type" data-sek-click-on="edit-module" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">{{module_name}}</span>
                    <?php if ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
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
        // Fired in __construct()
        function _schedule_front_rendering() {
            if ( !defined( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY", 1 - PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY", PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY" ) ) { define( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY", - PHP_INT_MAX ); }

            // SCHEDULE THE ACTIONS ON HOOKS AND CONTENT FILTERS
            foreach( sek_get_locations() as $hook ) {
                switch ( $hook ) {
                    case 'loop_start' :
                    case 'loop_end' :
                        add_action( $hook, array( $this, 'sek_schedule_sektions_rendering' ) );
                    break;
                    case 'before_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                    break;
                    case 'after_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );
                    break;
                }
            }

            add_filter( 'the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

            // SCHEDULE THE ASSETS ENQUEUING
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_the_printed_module_assets') );
            // add_filter( 'template_include', function( $template ) {
            //       // error_log( 'TEMPLATE ? => ' . $template );
            //       // error_log( 'DID_ACTION WP => ' . did_action('wp') );
            //       return NIMBLE_BASE_PATH. "/tmpl/page-templates/full-width.php";// $template;
            // });
        }

        // Encapsulate the singular post / page content so we can generate a dynamic ui around it when customizing
        // @filter the_content::NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY
        function sek_wrap_wp_content( $html ) {
            if ( ! skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) )
              return $html;
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                global $post;
                // note : the edit url is printed as a data attribute to prevent being automatically parsed by wp when customizing and turned into a changeset url
                $html = sprintf( '<div class="sek-wp-content-wrapper" data-sek-wp-post-id="%1$s" data-sek-wp-edit-link="%2$s" title="%3$s">%4$s</div>',
                      $post->ID,
                      // we can't rely on the get_edit_post_link() function when customizing because emptied by wp core
                      $this->get_unfiltered_edit_post_link( $post->ID ),
                      __( 'WordPress content', 'text_domain'),
                      wpautop( $html )
                );
            }
            return $html;
        }


        // hook : loop_start, loop_end
        function sek_schedule_sektions_rendering() {
            // A location can be rendered only once
            // for loop_start and loop_end, checking with is_main_query() is not enough because the main loop might be used 2 times in the same page
            // @see issue with Twenty Seventeen here : https://github.com/presscustomizr/nimble-builder/issues/14
            // That's why we check if did_action( ... )
            if ( did_action( 'sek_before_location_' . current_filter() ) )
              return;
            do_action( 'sek_before_location_' . current_filter() );
            $this->_render_seks_for_location( current_filter() );
            do_action( 'sek_after_location_' . current_filter() );
        }

        // hook : 'the_content'::-9999
        function sek_schedule_sektion_rendering_before_content( $html ) {
            if ( did_action( 'sek_before_location_before_content' ) )
              return $html;

            do_action( 'sek_before_location_before_content' );
            return $this -> _filter_the_content( $html, 'before_content' );
        }

        // hook : 'the_content'::9999
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
            }

            return $html;
        }


        private function _render_seks_for_location( $location = '' ) {
            if ( ! in_array( $location, sek_get_locations() ) ) {
                error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location . ' is not registered in sek_get_locations()');
                return;
            }
            $locationSettingValue = sek_get_skoped_seks( skp_build_skope_id(), $location );
            if ( is_array( $locationSettingValue ) ) {

                remove_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );
                // sek_error_log( 'LEVEL MODEL IN ::sek_schedule_sektions_rendering()', $locationSettingValue);
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                $this->render( $locationSettingValue, $location );

                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ),NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                add_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

            } else {
                error_log( __CLASS__ . ' :: ' . __FUNCTION__ .' => sek_get_skoped_seks() should always return an array().');
            }
        }









        // Walk a model tree recursively and render each level with a specific template
        function render( $model = array(), $location = 'loop_start' ) {
            //sek_error_log('LEVEL MODEL IN ::RENDER()', $model );
            // Is it the root level ?
            // The root level has no id and no level entry
            if ( ! is_array( $model ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a model must be an array', $model );
                return;
            }
            if ( ! array_key_exists( 'level', $model ) || ! array_key_exists( 'id', $model ) ) {
                error_log( '::render() => a level model is missing the level or the id property' );
                return;
            }
            $id = $model['id'];
            $level = $model['level'];

            // Cache the parent model
            // => used when calculating the width of the column to be added
            $parent_model = $this -> parent_model;
            $this -> model = $model;

            $collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();

            switch ( $level ) {
                case 'location' :
                    //empty sektions wrapper are only printed when customizing
                    ?>
                      <?php if ( skp_is_customizing() || ( ! skp_is_customizing() && ! empty( $collection ) ) ) : ?>
                          <div class="sektion-wrapper" data-sek-level="location" data-sek-id="<?php echo $id ?>">
                            <?php
                              $this -> parent_model = $model;
                              foreach ( $collection as $_key => $sec_model ) { $this -> render( $sec_model ); }
                            ?>

                             <?php if ( empty( $collection ) ) : ?>
                                <div class="sek-empty-location-placeholder"></div>
                            <?php endif; ?>
                          </div>
                      <?php endif; ?>
                    <?php
                break;

                case 'section' :
                    $is_nested            = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
                    $column_container_class = 'sek-container-fluid';
                    //when boxed use proper container class
                    if ( ! empty( $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) {
                        $column_container_class = 'sek-container';
                    }
                    ?>
                    <?php printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section">', $id, $is_nested ? 'data-sek-is-nested="true"' : '' ); ?>
                          <div class="<?php echo $column_container_class ?>">
                            <div class="sek-row sek-sektion-inner">
                                <?php
                                  // Set the parent model now
                                  $this -> parent_model = $model;
                                  foreach ( $collection as $col_model ) {$this -> render( $col_model ); }
                                ?>
                            </div>
                          </div>
                      </div>
                    <?php
                break;

                case 'column' :
                    // if ( defined('DOING_AJAX') && DOING_AJAX ) {
                    //     error_log( print_r( $parent_model, true ) );
                    // }
                    // sek_error_log( 'PARENT MODEL WHEN RENDERING', $parent_model );

                    $col_number = ( array_key_exists( 'collection', $parent_model ) && is_array( $parent_model['collection'] ) ) ? count( $parent_model['collection'] ) : 1;
                    $col_number = 12 < $col_number ? 12 : $col_number;
                    $col_width_in_percent = 100/$col_number;

                    //TODO, we might want to be sure the $col_suffix is related to an allowed size
                    $col_suffix = floor( $col_width_in_percent );
                    ?>
                      <?php
                          printf('<div data-sek-level="column" data-sek-id="%1$s" class="sek-column sek-col-base sek-col-%2$s" %3$s>',
                              $id,
                              $col_suffix,
                              empty( $collection ) ? 'data-sek-no-modules="true"' : ''
                          );
                      ?>
                        <?php // Drop zone : if no modules, the drop zone is wrapped in sek-no-modules-columns
                        // if at least one module, the sek-drop-zone is the .sek-column-inner wrapper ?>
                        <div class="sek-column-inner <?php echo empty( $collection ) ? 'sek-empty-col' : ''; ?>">
                            <?php
                              if ( skp_is_customizing() && empty( $collection ) ) {
                                  ?>
                                  <div class="sek-no-modules-column">
                                    <div class="sek-module-drop-zone-for-first-module sek-content-module-drop-zone sek-drop-zone">
                                      <i data-sek-click-on="pick-module" class="material-icons sek-click-on" title="<?php _e('Drag and drop a module here', 'text_domain_to_be_replaced' ); ?>">add_circle_outline</i>
                                    </div>
                                  </div>
                                  <?php
                              } else {
                                  // Set the parent model now
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
                      </div>
                    <?php
                break;

                case 'module' :
                    if ( empty( $model['module_type'] ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing module_type for a module', $model );
                        break;
                    }
                    $module_type = $model['module_type'];

                    $default_value_model  = sek_get_default_module_model( $module_type );//<= walk the registered modules tree and generates the module default if not already cached
                    $raw_module_value = ( ! empty( $model['value'] ) && is_array( $model['value'] ) ) ? $model['value'] : array();

                    // reset the model value and rewrite it normalized with the defaults
                    $model['value'] = array();
                    if ( czr_is_multi_item_module( $module_type ) ) {
                        foreach ( $raw_module_value as $item ) {
                            $model['value'][] = wp_parse_args( $item, $default_value_model );
                        }
                    } else {
                        $model['value'] = wp_parse_args( $raw_module_value, $default_value_model );
                    }

                    // update the current cached model
                    $this -> model = $model;
                    ?>
                      <div data-sek-level="module" data-sek-id="<?php echo $id; ?>" data-sek-module-type="<?php echo $module_type; ?>" class="sek-module">
                            <div class="sek-module-inner">
                              <?php $this -> sek_print_module_tmpl( $model ); ?>
                            </div>
                      </div>
                    <?php
                break;
            }

            $this -> parent_model = $parent_model;
        }//render







        /* MODULE AND PLACEHOLDER */
        // Fires the render callback of the module
        // The placeholder(s) rendering is delegated to each module template
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

            //$placeholder_icon = sek_get_registered_module_type_property( $module_type, 'placeholder_icon' );

            // if ( is_string( $render_callback ) && function_exists( $render_callback ) ) {
            //     call_user_func_array( $render_callback, array( $model ) );
            // } else {
            //     error_log( __FUNCTION__ . ' => not render_callback defined for ' . $model['module_type'] );
            //     return;
            // }

        }


        function sek_get_input_placeholder_content( $input_type = '', $input_id = '' ) {
            $ph = '<i class="material-icons">pan_tool</i>';
            switch( $input_type ) {
                case 'tiny_mce_editor' :
                case 'text' :
                  $ph = skp_is_customizing() ? '<div style="padding:10px;border: 1px dotted;background:#eee">' . __('Click to edit', 'here') .'</div>' : '<i class="material-icons">short_text</i>';
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



        // @hook wp_enqueue_scripts
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

        // @hook sek_sniff_assets_to_enqueue
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
    }//class
endif;
?><?php
if ( ! class_exists( 'SEK_Front_Render_Css' ) ) :
    class SEK_Front_Render_Css extends SEK_Front_Render {
        // Fired in __construct()
        function _setup_hook_for_front_css_printing_or_enqueuing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'print_or_enqueue_seks_style') );
        }

        // Can be fired :
        // 1) on wp_enqueue_scripts or wp_head
        // 2) when ajaxing, for actions 'sek-resize-columns', 'sek-refresh-stylesheet'
        function print_or_enqueue_seks_style( $skope_id = null ) {
            // when this method is fired in a customize preview context :
            //    - the skope_id has to be built. Since we are after 'wp', this is not a problem.
            //    - the css rules are printed inline in the <head>
            //    - we set to hook to wp_head
            //
            // when the method is fired in an ajax refresh scenario
            //    - the skope_id must be passed as param
            //    - the css rules are printed inline in the <head>
            //    - we set the hook to ''
            //
            // in a front normal context, the css is enqueued from the already written file.
            if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
                $skope_id = skp_build_skope_id();
            } else {
                if ( empty( $skope_id ) ) {
                    wp_send_json_error(  __FUNCTION__ . ' => missing skope_id' );
                    return;
                }
            }

            new Sek_Dyn_CSS_Handler( array(
                'id'             => $skope_id,
                'skope_id'       => $skope_id,
                'mode'           => is_customize_preview() ? Sek_Dyn_CSS_Handler::MODE_INLINE : Sek_Dyn_CSS_Handler::MODE_FILE,
                //these are taken in account only when 'mode' is 'file'
                'force_write'    => true, //<- write if the file doesn't exist
                'force_rewrite'  => is_user_logged_in() && current_user_can( 'customize' ), //<- write even if the file exists
                'hook'           => ( ! defined( 'DOING_AJAX' ) && is_customize_preview() ) ? 'wp_head' : ''
            ) );


        }//print_or_enqueue_seks_style
    }//class
endif;

?><?php
// invoked ( and instanciated ) when skp_is_customizing()
function SEK_CZR_Dyn_Register( $params = array() ) {
    return SEK_CZR_Dyn_Register::get_instance( $params );
}
function SEK_Front( $params = array() ) {
    return SEK_Front_Render_Css::get_instance( $params );
}
if (  skp_is_customizing() ) {
  SEK_CZR_Dyn_Register();
}
SEK_Front();
?>