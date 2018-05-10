<?php
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

?><?php
// SEKTION POST
register_post_type( SEK_CPT , array(
    'labels' => array(
      'name'          => __( 'Sektion settings', 'text_domain_to_be_replaced' ),
      'singular_name' => __( 'Sektion settings', 'text_domain_to_be_replaced' ),
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
 * Fetch the `sek_post_type` post for a given {skope_id}
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A theme object stylesheet name. Defaults to the current theme.
 * @return WP_Post|null The skope post or null if none exists.
 */
function sek_get_seks_post( $skope_id = '', $skope_level = 'local' ) {
    //error_log('skope_id in sek_get_seks_post => ' . $skope_id );
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }
    // if ( empty( $location ) ) {
    //     $location = 'loop_start';
    // }
    $sek_post_query_vars = array(
        'post_type'              => SEK_CPT,
        'post_status'            => get_post_stati(),
        'name'                   => sanitize_title( SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );

    $post = null;

    $option_name = SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;

    // $seks_options = get_option( $option_name );
    // $seks_options = is_array( $seks_options ) ? $seks_options : array();

    // $post_id = array_key_exists( $skope_id, $seks_options ) ? $seks_options : -1;

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
    // if ( empty( $location ) ) {
    //     $location = 'loop_start';
    // }
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }
    // use the cached value when NOT in a customization scenario
    if ( ! skp_is_customizing() && did_action('wp') && 'not_cached' != SEK_Front()->local_seks ) {
        $seks_data = SEK_Front()->local_seks;
    } else {
        $seks_data = array();
        $post = sek_get_seks_post( $skope_id );
        // error_log( '<sek_get_skoped_seks() => $post>');
        // error_log( print_r( $post, true ) );
        // error_log( '</sek_get_skoped_seks() => $post>');
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

    // error_log( '<sek_get_skoped_seks()>');
    // error_log('location => ' . $location .  array_key_exists( 'collection', $seks_data ));
    // error_log( print_r( $seks_data, true ) );
    // error_log( '</sek_get_skoped_seks()>');
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
 * Update the `sek_post_type` post for a given "{$skope_id}"
 *
 * Inserts a `sek_post_type` post when one doesn't yet exist.
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

    $post_title = SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    //$post_title = "{$location}_{$skope_id}";// as defined in sek_get_seks_post

    $post_data = array(
        'post_title' => $post_title,
        'post_name' => sanitize_title( $post_title ),
        'post_type' => SEK_CPT,
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
            //$option_name = SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION . $location;
            $option_name = SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
            //$seks_options = get_option( $option_name );
            //$seks_options = is_array( $seks_options ) ? $seks_options : array();
            //$seks_options[$skope_id] = $r;//$r is the post ID
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
// DEPRECATED WAS USED TO DISPLAY UI BUTTON IN THE PANEL
// if ( ! defined( 'SEK_BUTTON_SECTION_TMPL_SUFFIX' ) ) { define( 'SEK_BUTTON_SECTION_TMPL_SUFFIX', 'sek-add-new-sektion-button' ); }
// if ( ! defined( 'SEK_BUTTON_COLUMN_TMPL_SUFFIX' ) ) { define( 'SEK_BUTTON_COLUMN_TMPL_SUFFIX', 'sek-add-new-column-button' ); }
// if ( ! defined( 'SEK_BUTTON_MODULE_TMPL_SUFFIX' ) ) { define( 'SEK_BUTTON_MODULE_TMPL_SUFFIX', 'sek-add-new-module-button' ); }

// TINY MCE EDITOR
require_once(  dirname( __FILE__ ) . '/customizer/seks_tiny_mce_editor_actions.php' );

// CONTENT PICKER AJAX
add_action( 'customize_register', function() {
    require_once(  dirname( __FILE__ ) . '/customizer/seks_content_picker-ajax_actions.php' );
    new SEK_customize_ajax_content_picker_actions();
});


// ENQUEUE CUSTOMIZER JAVASCRIPT + PRINT LOCALIZED DATA
add_action ( 'customize_controls_enqueue_scripts', 'sek_enqueue_controls_js_css', 20 );
function sek_enqueue_controls_js_css() {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    // registered modules
    $registered_modules = $CZR_Fmk_Base_fn() -> registered_modules;

    wp_enqueue_script(
        'czr-sektions',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/%2$s' ,
            NIMBLE_BASE_URL,
            'ccat-sektions.js'
        ),
        array( 'czr-skope-base' , 'jquery', 'underscore' ),
        ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() :  wp_get_theme() -> version,
        $in_footer = true
    );
    wp_enqueue_script(
        'sek-drag-n-drop',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/libs/%2$s' ,
            NIMBLE_BASE_URL,
            'dragdrop.js'
        ),
        array( 'jquery' ),
        ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() :  wp_get_theme() -> version,
        $in_footer = true
    );
    wp_enqueue_script(
        'czr-color-picker',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/libs/%2$s' ,
            NIMBLE_BASE_URL,
            'czr-color-picker.js'
        ),
        array( 'jquery' ),
        ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() :  wp_get_theme() -> version,
        $in_footer = true
    );

    wp_localize_script(
        'czr-sektions',
        'sektionsLocalizedData',
        array(
            'sektionsPanelId' => '__sektions__',
            'addNewSektionId' => 'sek_add_new_sektion',
            'addNewColumnId' => 'sek_add_new_column',
            'addNewModuleId' => 'sek_add_new_module',

            'optPrefixForSektionSetting' => SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION,//'sek___'
            'optPrefixForSektionsNotSaved' => SEK_OPT_PREFIX_FOR_SEKTIONS_NOT_SAVED,//"__sek__"

            'defaultSektionSettingValue' => sek_get_default_sektions_value(),

            'presetSections' => sek_get_preset_sektions(),

            'registeredModules' => $registered_modules,

            'selectOptions' => array(
                  // IMAGE MODULE
                  'link-to' => array(
                      'no-link' => __('No link', 'text_domain_to_be_replaced' ),
                      'url' => __('Site content or custom url', 'text_domain_to_be_replaced' ),
                      'img-file' => __('Image file', 'text_domain_to_be_replaced' ),
                      'img-page' =>__('Image page', 'text_domain_to_be_replaced' )
                  ),
                  'img-size' => sek_get_img_sizes(),


                  // FEATURED PAGE MODULE
                  'img-type' => array(
                      'none' => __('No image', 'text_domain_to_be_replaced' ),
                      'featured' => __('Use the page featured image', 'text_domain_to_be_replaced' ),
                      'custom' => __('Use a custom image', 'text_domain_to_be_replaced' ),
                  ),
                  'content-type' => array(
                      'none' => __('No text', 'text_domain_to_be_replaced' ),
                      'page-excerpt' => __('Use the page excerpt', 'text_domain_to_be_replaced' ),
                      'custom' => __('Use a custom text', 'text_domain_to_be_replaced' ),
                  ),


                  // SPACING MODULE
                  'spacingUnits' => array(
                      'px' => __('Pixels', 'text_domain_to_be_replaced' ),
                      'em' => __('Em', 'text_domain_to_be_replaced'),
                      'percent' => __('Percents', 'text_domain_to_be_replaced' )
                  ),

                  // LAYOUT BACKGROUND BORDER
                  'boxed-wide' => array(
                      'boxed' => __('Boxed', 'text_domain_to_be_replaced'),
                      'fullwidth' => __('Full Width', 'text_domain_to_be_replaced')
                  ),
                  'height-type' => array(
                      'default' => __('default', 'text_domain_to_be_replaced'),
                      'fit-to-screen' => __('Fit to screen', 'text_domain_to_be_replaced'),
                      'custom' => __('Custom', 'text_domain_to_be_replaced' )
                  ),
                  'bg-scale' => array(
                      'default' => __('default', 'text_domain_to_be_replaced'),
                      'auto' => __('auto', 'text_domain_to_be_replaced'),
                      'cover' => __('scale to fill', 'text_domain_to_be_replaced'),
                      'contain' => __('fit', 'text_domain_to_be_replaced'),
                  ),
                  'bg-position' => array(
                      'default' => __('default', 'text_domain_to_be_replaced'),
                  ),
                  'border-type' => array(
                      'none' => __('none', 'text_domain_to_be_replaced'),
                      'solid' => __('solid', 'text_domain_to_be_replaced'),
                      'double' => __('double', 'text_domain_to_be_replaced'),
                      'dotted' => __('dotted', 'text_domain_to_be_replaced'),
                      'dashed' => __('dashed', 'text_domain_to_be_replaced')
                  )
            ),


            'i18n' => array(
                'Sektions' => __( 'Sektions', 'text_domain_to_be_replaced'),
                'Customizing' => __('Customizing', 'text_domain_to_be_replaced'),
                "You've reached the maximum number of allowed nested sections." => __("You've reached the maximum number of allowed nested sections.", 'text_domain_to_be_replaced'),
                "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
                "A section must have at least one column." => __( "A section must have at least one column.", 'text_domain_to_be_replaced')
            )
        )
    );

    wp_enqueue_style(
        'sek-control',
        NIMBLE_BASE_URL . '/assets/czr/sek/css/sek-control.css',
        array(),
        time(),
        'all'
    );
}


// ADD SEKTION VALUES TO EXPORTED DATA IN THE CUSTOMIZER PREVIEW
add_filter( 'skp_json_export_ready_skopes', function( $skopes ) {
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
            'setting_id' => sek_get_seks_setting_id( $skope_id )//sek___loop_start[skp__post_page_home]
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
        //         'setting_id' => sek_get_seks_setting_id( $skope_id, $location )//sek___loop_start[skp__post_page_home]
        //     );
        // }
        $new_skopes[] = $skp_data;
    }

    // error_log( '<////////////////////$new_skopes>' );
    // error_log( print_r($new_skopes, true ) );
    // error_log( '</////////////////////$new_skopes>' );

    return $new_skopes;
} );



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



// PRINT THE ADD NEW SEKTION BUTTON TMPL
// DEPRECATED WAS USED TO DISPLAY UI BUTTON IN THE PANEL
/*add_action( 'customize_controls_print_footer_scripts', function() {
  ?>
  <script type="text/html" id="tmpl-<?php echo SEK_BUTTON_SECTION_TMPL_SUFFIX;//'sek-add-new-sektion-button' ?>">
    <h3>
      <button type="button" class="button czr-add-sektion-button">
        <?php _e( ' + Add New Sektion' ); ?>
      </button>&nbsp;
      <button type="button" class="button czr-remove-all-sektions-button">
        <?php _e( ' - Remove All Sektions' ); ?>
      </button>
    </h3>
  </script>
  <script type="text/html" id="tmpl-<?php echo SEK_BUTTON_COLUMN_TMPL_SUFFIX;//'sek-add-new-column-button' ?>">
    <h3>
      <button type="button" class="button czr-add-column-button">
        <?php _e( ' + Add New Column' ); ?>
      </button>
    </h3>
  </script>
  <script type="text/html" id="tmpl-<?php echo SEK_BUTTON_MODULE_TMPL_SUFFIX;//'sek-add-new-module-button' ?>">
    <h3>
      <button type="button" class="button czr-add-module-button">
        <?php _e( ' + Add New Module' ); ?>
      </button>
    </h3>
  </script>
  <?php
});*/

?><?php
/* ------------------------------------------------------------------------- *
 *  SETUP DYNAMIC SERVER REGISTRATION FOR SETTING
/* ------------------------------------------------------------------------- */
// Schedule the loading the skoped settings class
add_action( 'customize_register', function() {
      require_once(  dirname( __FILE__ ) . '/customizer/seks_setting_class.php' );
});

add_filter( 'customize_dynamic_setting_args', function( $setting_args, $setting_id ) {
    // shall start with "sek__"
    if ( 0 === strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) {
        //error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id );
        return array(
            'transport' => 'refresh',
            'type' => 'option',
            'default' => array()
        );
    } else if ( 0 === strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTIONS_NOT_SAVED ) ) {
        //error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id );
        return array(
            'transport' => 'refresh',
            'type' => '_no_intended_to_be_saved_',
            'default' => array(),
            'sanitize_callback'    => 'sek_sanitize_callback',
            'validate_callback'    => 'sek_validate_callback'
        );
    }

    //error_log( print_r( $setting_args, true ) );
    return $setting_args;
    //return wp_parse_args( array( 'default' => array() ), $setting_args );
}, 10, 2 );

function sek_sanitize_callback( $sektion_data ) {
    //error_log( 'in_sek_sanitize_callback' );
    return $sektion_data;
}

function sek_validate_callback( $validity, $sektion_data ) {
    //error_log( 'in_sek_validate_callback' );
    return null;
    //return new WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $sektion_data );
}


add_filter( 'customize_dynamic_setting_class', function( $class, $setting_id, $args ) {
  // shall start with 'sek___'
  if ( 0 !== strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION ) )
    return $class;
  //error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
  return 'Sek_Customizer_Setting';
}, 10, 3 );

// add_filter( 'customize_dynamic_setting_class', function( $class, $setting_id, $args ) {
//   // shall start with 'sek_for_customizer___sektion_'
//   if ( 0 !== strpos( $setting_id, '__sek__' ) )
//     return $class;
//   //error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
//   return 'Sek_Not_Saved_Customizer_Setting';
// }, 10, 3 );

?><?php
// The base fmk is loaded on after_setup_theme before 50


add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_register_modules() {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }



    /* ------------------------------------------------------------------------- *
     *  MODULE PICKER MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'sek_module_picker_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'module_id' => array(
                    'input_type'  => 'module_picker',
                    'title'       => __('Pick a module', 'text_domain_to_be_replaced')
                )
            )
        )
    ));




    /* ------------------------------------------------------------------------- *
     *  SECTION PICKER MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'sek_section_picker_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'section_id' => array(
                    'input_type'  => 'section_picker',
                    'title'       => __('Pick a section', 'text_domain_to_be_replaced')
                )
            )
        )
    ));





    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_layout_bg_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Background', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'bg-color' => array(
                                'input_type'  => 'wp_color_apha',
                                'title'       => __('Background color', 'text_domain_to_be_replaced'),
                                'width-100'   => true
                            ),
                            'bg-image' => array(
                                'input_type'  => 'upload',
                                'title'       => __('Image', 'text_domain_to_be_replaced')
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
                                'title'       => __('Fixed background', 'text_domain_to_be_replaced')
                            ),
                            // 'bg-repeat' => array(
                            //     'input_type'  => 'select',
                            //     'title'       => __('repeat', 'text_domain_to_be_replaced')
                            // ),
                            'bg-scale' => array(
                                'input_type'  => 'select',
                                'title'       => __('scale', 'text_domain_to_be_replaced')
                            ),
                            'bg-video' => array(
                                'input_type'  => 'text',
                                'title'       => __('Video', 'text_domain_to_be_replaced')
                            ),
                            'bg-apply-overlay' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply a background overlay', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'bg-color-overlay' => array(
                                'input_type'  => 'wp_color_apha',
                                'title'       => __('Overlay Color', 'text_domain_to_be_replaced'),
                                'width-100'   => true
                            ),
                            'bg-opacity-overlay' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Opacity', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                'unit' => '%'
                            )
                        )
                    ),
                    array(
                        'title' => __('Layout', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'boxed-wide' => array(
                                'input_type'  => 'select',
                                'title'       => __('Boxed or full width', 'text_domain_to_be_replaced')
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
                            'height-type' => array(
                                'input_type'  => 'select',
                                'title'       => __('Height : fit to screen or custom', 'text_domain_to_be_replaced')
                            ),
                            'custom-height' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Custom height', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                'unit' => '%'
                            ),
                            'v-alignment' => array(
                                'input_type'  => 'v_alignment',
                                'title'       => __('Vertical alignment', 'text_domain_to_be_replaced'),
                                'default'     => 'center'
                            ),
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
                                'unit' => 'px'
                            ),
                            'border-type' => array(
                                'input_type'  => 'select',
                                'title'       => __('Border shape', 'text_domain_to_be_replaced')
                            ),
                            'border-color' => array(
                                'input_type'  => 'wp_color_apha',
                                'title'       => __('Border color', 'text_domain_to_be_replaced'),
                                'width-100'   => true,
                            ),
                            'shadow' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply a shadow', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            )
                        )
                    ),
                )//tabs
            )//item-inputs
        )//tmpl
    ));





    /* ------------------------------------------------------------------------- *
     *  SPACING MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
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
                                'title'       => __('Set padding and margin for Desktop', 'text_domain_to_be_replaced')
                            ),
                            'desktop_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced')
                            )
                        )
                    ),
                    array(
                        'title' => __('Tablet', 'text_domain_to_be_replaced'),
                        'attributes' => 'data-sek-device="tablet"',
                        'inputs' => array(
                            'tablet_pad_marg' => array(
                                'input_type'  => 'spacing',
                                'title'       => __('Set padding and margin for tablet devices', 'text_domain_to_be_replaced')
                            ),
                            'tablet_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced')
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
                            ),
                            'mobile_unit' =>  array(
                                'input_type'  => 'select',
                                'title'       => __('Unit', 'text_domain_to_be_replaced')
                            )
                        )
                    )

                )
            )
        )
    ));




    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER THE SIMPLE HTML MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_html_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'html_content' => array(
                    'input_type'  => 'textarea',
                    'title'       => __('HTML Content', 'text_domain_to_be_replaced')
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_html_module_tmpl.php",
        'placeholder_icon' => 'code'
    ));




    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER THE TEXT EDITOR MODULE
    /* ------------------------------------------------------------------------- */
    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content' => array(
                    'input_type'  => 'tiny_mce_editor',
                    'title'       => __('Content', 'text_domain_to_be_replaced')
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    ));




    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER IMAGE MODULE
    /* ------------------------------------------------------------------------- */
        // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_domain_to_be_replaced')
                ),
                'img-size' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                    'default'     => 'large'
                ),
                'alignment' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center'
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced')
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_domain_to_be_replaced')
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Link url', 'text_domain_to_be_replaced')
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_domain_to_be_replaced')
                ),
                'lightbox' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Activate a lightbox on click', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 'center'
                ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    ));






    /* ------------------------------------------------------------------------- *
     *  LOAD AND REGISTER FEATURED PAGES MODULE
    /* ------------------------------------------------------------------------- */
        // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => true,
        'module_type' => 'czr_featured_pages_module',

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
                    'default'     => 'featured'
                ),
            ),
            'item-inputs' => array(
                'page-id' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Pick a page', 'text_domain_to_be_replaced')
                ),
                'img-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display an image', 'text_domain_to_be_replaced'),
                    'default'     => 'featured'
                ),
                'img-id' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_domain_to_be_replaced')
                ),
                'img-size' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                    'default'     => 'large'
                ),
                'content-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Display a text', 'text_domain_to_be_replaced'),
                    'default'     => 'page-excerpt'
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
    ));
}//sek_register_modules()
?><?php
/* ------------------------------------------------------------------------- *
 *  MODULE PICKER INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___module_picker', 'sek_set_input_tmpl___module_picker', 10,3 );
function sek_set_input_tmpl___module_picker( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Module Picker => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

            printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
            ?>
              <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                  <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
              <?php endif; ?>
            <div class="czr-input">
              <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
              <div class="sek-content-type-wrapper">
                <?php
                  $content_collection = array(
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_tiny_mce_editor_module',
                        'title' => '@missi18n Text Editor'),
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_image_module',
                        'title' => '@missi18n Image'
                      ),
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_simple_html_module',
                        'title' => '@missi18n Html Content'
                      ),
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_featured_pages_module',
                        'title' => '@missi18n Featured pages'
                      ),

                  );
                  $i = 0;
                  foreach( $content_collection as $_params) {
                      if ( $i % 2 == 0 ) {
                        //printf('<div class="sek-module-raw"></div');
                      }
                      printf('<div draggable="true" style="%1$s" data-sek-content-type="%2$s" data-sek-content-id="%3$s"><p style="%4$s">%5$s</p></div>',
                          "width: 40%;float: left;padding: 5%;text-align: center;",
                          $_params['content-type'],
                          $_params['content-id'],
                          "padding: 9%;background: #eee;cursor: move;",
                          $_params['title']
                      );
                      $i++;
                  }
                ?>
              </div>
            </div><?php // class="czr-input" ?>
            <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
            <?php endif; ?>
          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}






/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___section_picker', 'sek_set_input_tmpl___section_picker', 10, 3 );
function sek_set_input_tmpl___section_picker( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Section Picker => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

            printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
            ?>
              <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                  <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
              <?php endif; ?>
            <# //console.log('DATA IN SECTION PICKER INPUT'); #>
            <div class="czr-input">
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
                      printf('<div draggable="true" style="%1$s" data-sek-content-type="%2$s" data-sek-content-id="%3$s"><p style="%4$s">%5$s</p></div>',
                          "width: 40%;float: left;padding: 5%;text-align: center;",
                          $_params['content-type'],
                          $_params['content-id'],
                          "padding: 9%;background: #eee;cursor: move;",
                          $_params['title']
                      );
                  }
                ?>
              </div>
            </div><?php // class="czr-input" ?>
            <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
            <?php endif; ?>
          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}








/* ------------------------------------------------------------------------- *
 *  SPACING INPUT
/* ------------------------------------------------------------------------- */
// SPACING INPUT
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___spacing', 'sek_set_input_tmpl___spacing', 10, 3 );
function sek_set_input_tmpl___spacing( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title width-100">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>
                <# //console.log('DATA IN SPACING INPUT'); #>

                <div class="czr-input">
                  <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                  <div class="sek-spacing-wrapper">
                      <div class="Spacing-spacingContainer-12n">
                        <div class="Spacing-spacingRow-K2n Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
                          <div class="SmartTextLabel-main-22n SmartTextLabel-selected-R2n" data-sek-spacing="margin-top">
                            <div class="SmartTextLabel-input-12n TextBox-container-32n">
                              <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                            </div>
                          </div>
                        </div>
                        <div class="Spacing-spacingRow--large-32n Flex-main-32n Flex-row-12n" style="display: flex; justify-content: space-between;">
                          <div class="SmartTextLabel-main-22n Spacing-col-outer-left-32n SmartTextLabel-editable-false-22n" data-sek-spacing="margin-left">
                            <div class="SmartTextLabel-input-12n TextBox-container-32n">
                              <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                            </div>
                          </div>

                          <div class="Spacing-innerSpacingContainer-22n">
                            <div class="Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
                              <div class="SmartTextLabel-main-22n" data-sek-spacing="padding-top">
                                <div class="SmartTextLabel-input-12n TextBox-container-32n">
                                  <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                                </div>
                              </div>
                            </div>
                              <div class="Flex-main-32n Flex-row-12n" style="display: flex; justify-content: space-between;">
                                <div class="SmartTextLabel-main-22n SmartTextLabel-editable-false-22n" data-sek-spacing="padding-left">
                                  <div class="SmartTextLabel-input-12n TextBox-container-32n">
                                    <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                                  </div>
                                </div>
                                <div class="SmartTextLabel-main-22n" data-sek-spacing="padding-right">
                                  <div class="SmartTextLabel-input-12n TextBox-container-32n">
                                    <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                                  </div>
                                </div>
                              </div>
                            <div class="Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
                              <div class="SmartTextLabel-main-22n" data-sek-spacing="padding-bottom">
                                <div class="SmartTextLabel-input-12n TextBox-container-32n">
                                  <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="SmartTextLabel-main-22n Spacing-col-outer-right-22n" data-sek-spacing="margin-right">
                            <div class="SmartTextLabel-input-12n TextBox-container-32n">
                              <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                            </div>
                          </div>
                        </div>
                        <div class="Spacing-spacingRow-K2n Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
                          <div class="SmartTextLabel-main-22n SmartTextLabel-editable-false-22n" data-sek-spacing="margin-bottom">
                            <div class="SmartTextLabel-input-12n TextBox-container-32n">
                              <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                            </div>
                          </div>
                        </div>
                      </div><?php //Spacing-spacingContainer-12n ?>
                      <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_domain_to_be_replaced' ); ?></span></div>
                  </div><?php // sek-spacing-wrapper ?>
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}









/* ------------------------------------------------------------------------- *
 *  BACKGROUND POSITION INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___bg_position', 'sek_set_input_tmpl___bg_position', 10, 3 );
function sek_set_input_tmpl___bg_position( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>
                <# //console.log('DATA IN SPACING INPUT'); #>

                <div class="czr-input">
                  <div class="sek-bg-pos-wrapper">
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                    <div class="items">
                      <label class="item">
                        <input type="radio" name="rb_0" value="top_left">
                        <span>
                          <svg class="symbol symbol-alignTypeTopLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M14.96 16v-1h-1v-1h-1v-1h-1v-1h-1v-1.001h-1V14h-1v-4-1h5v1h-3v.938h1v.999h1v1h1v1.001h1v1h1V16h-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="top">
                        <span>
                          <svg class="symbol symbol-alignTypeTop" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M14.969 12v-1h-1v-1h-1v7h-1v-7h-1v1h-1v1h-1v-1.062h1V9.937h1v-1h1V8h1v.937h1v1h1v1.001h1V12h-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="top_right">
                        <span>
                          <svg class="symbol symbol-alignTypeTopRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M9.969 16v-1h1v-1h1v-1h1v-1h1v-1.001h1V14h1v-4-1h-1-4v1h3v.938h-1v.999h-1v1h-1v1.001h-1v1h-1V16h1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="left">
                        <span>
                          <svg class="symbol symbol-alignTypeLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M11.469 9.5h-1v1h-1v1h7v1h-7v1h1v1h1v1h-1.063v-1h-1v-1h-1v-1h-.937v-1h.937v-1h1v-1h1v-1h1.063v1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="center">
                        <span>
                          <svg class="symbol symbol-alignTypeCenter" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="right">
                        <span>
                          <svg class="symbol symbol-alignTypeRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M12.469 14.5h1v-1h1v-1h-7v-1h7v-1h-1v-1h-1v-1h1.062v1h1v1h1v1h.938v1h-.938v1h-1v1h-1v1h-1.062v-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="bottom_left">
                        <span>
                          <svg class="symbol symbol-alignTypeBottomLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M14.969 9v1h-1v1h-1v1h-1v1h-1v1.001h-1V11h-1v5h5v-1h-3v-.938h1v-.999h1v-1h1v-1.001h1v-1h1V9h-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="bottom">
                        <span>
                          <svg class="symbol symbol-alignTypeBottom" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M9.969 13v1h1v1h1V8h1v7h1v-1h1v-1h1v1.063h-1v.999h-1v1.001h-1V17h-1v-.937h-1v-1.001h-1v-.999h-1V13h1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="bottom_right">
                        <span>
                          <svg class="symbol symbol-alignTypeBottomRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M9.969 9v1h1v1h1v1h1v1h1v1.001h1V11h1v5h-1-4v-1h3v-.938h-1v-.999h-1v-1h-1v-1.001h-1v-1h-1V9h1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                    </div><?php // .items ?>
                  </div><?php // control-alignment ?>
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}






/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___h_alignment', 'sek_set_input_tmpl___h_alignment', 10, 3 );
function sek_set_input_tmpl___h_alignment( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                '',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>

                <div class="czr-input">
                  <div class="sek-h-align-wrapper">
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                    <div class="sek-align-icons">
                      <div data-sek-align="left" title="<?php _e('Align left','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_left</i></div>
                      <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_center</i></div>
                      <div data-sek-align="right" title="<?php _e('Align right','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_right</i></div>
                    </div>
                  </div><?php // sek-h-align-wrapper ?>
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}





/* ------------------------------------------------------------------------- *
 *  VERTICAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___v_alignment', 'sek_set_input_tmpl___v_alignment', 10, 3 );
function sek_set_input_tmpl___v_alignment( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                '',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>

                <div class="czr-input">
                  <div class="sek-v-align-wrapper">
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                    <div class="sek-align-icons">
                      <div data-sek-align="top" title="<?php _e('Align top','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_top</i></div>
                      <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_center</i></div>
                      <div data-sek-align="bottom" title="<?php _e('Align bottom','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_bottom</i></div>
                    </div>
                  </div><?php // sek-h-align-wrapper ?>
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}

?><?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
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
    private static $breakpoints = [
        'xs' => 0,
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
        'xl' => 1200
    ];

    const COLS_MOBILE_BREAKPOINT  = 'md';

    private $stylesheet;
    private $sek_model;

    private $rules_builder_methods_type = array(
        'width',
        'spacing',
        'border',
        'background',
        'boxshadow',
        'height'
    );

    public function __construct( $sek_model = array(), Sek_Stylesheet $stylesheet ) {
        $this->stylesheet = $stylesheet;
        $this->sek_model  = $sek_model;

        $this->sek_dyn_css_builder_setup_parse_rules_hooks();

        $this->sek_dyn_css_builder_build_stylesheet();
    }


    private function sek_dyn_css_builder_setup_parse_rules_hooks() {
        foreach ( $this->rules_builder_methods_type as $rules_builder_method_type ) {
            if ( method_exists( $this, "sek_dyn_css_builder_{$rules_builder_method_type}_parse_rules" ) )
               add_filter( 'sek_dyn_css_builder_rules', array( $this, "sek_dyn_css_builder_{$rules_builder_method_type}_parse_rules" ), 10, 2 );
        }

    }


    public function sek_dyn_css_builder_build_stylesheet( $level = null, $stylesheet = null ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        $stylesheet = is_null( $stylesheet ) ? $this->stylesheet : $stylesheet;


        $collection = empty( $level[ 'collection' ] ) ? array() : $level[ 'collection' ];

        foreach ( $collection as $level ) {

            //do this level
            if ( !empty( $level[ 'options' ] ) || !empty( $level[ 'width' ] ) ) {
                //build rules
                $this->sek_dyn_css_builder_build_rules( $level  );
            }

            if ( !empty( $level[ 'collection' ] ) ) {
                $this->sek_dyn_css_builder_build_stylesheet( $level, $stylesheet );
            }
        }
    }




    public function sek_dyn_css_builder_get_stylesheet() {
        return $this->stylesheet;
    }




    public function sek_dyn_css_builder_build_rules( $level ) {
        $rules   = apply_filters( 'sek_dyn_css_builder_rules', array(), $level  );
        //fill the stylesheet
        if ( !empty( $rules ) ) {
            //TODO: MAKE SURE RULE ARE NORMALIZED
            foreach( $rules as $rule ) {
                $this->stylesheet->sek_add_rule( $rule[ 'selector' ], $rule[ 'style_rules' ], $rule[ 'mq' ] );
            }
        }
    }





    public function sek_dyn_css_builder_width_parse_rules( array $rules, array $level ) {
        $width   = empty( $level[ 'width' ] ) || !is_numeric( $level[ 'width' ] ) ? '' : $level['width'];

        //width
        if ( !empty( $width ) ) {
            $style_rules = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width );
            $rules[] = array(
                'selector'      => '.sek-column[data-sek-id="'.$level['id'].'"]',
                'style_rules'   => $style_rules,
                'mq'            => array( 'min' => self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ] )
            );
        }

        return $rules;
    }


    public function sek_dyn_css_builder_spacing_parse_rules( array $rules, array $level ) {

        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

        //spacing
        if ( !empty( $options[ 'spacing' ] ) ) {

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
            if ( ! empty( $_pad_marg[ 'desktop' ] ) ) {
                $_pad_marg[ 'desktop' ][ 'mq' ] = null;
            }

            if ( ! empty( $_pad_marg[ 'tablet' ] ) ) {
                $_pad_marg[ 'tablet' ][ 'mq' ]  = array( 'max' => (int)( self::$breakpoints['lg'] - 1 ) ); //max-width: 991
            }

            if ( ! empty( $_pad_marg[ 'mobile' ] ) ) {
                $_pad_marg[ 'mobile' ][ 'mq' ]  = array( 'max' => (int)( self::$breakpoints['sm'] - 1 ) ); //max-width: 575
            }

            foreach( array_filter( $_pad_marg ) as $_spacing_rules ) {
                $style_rules = implode(';',
                    array_map( function( $key, $value ) {
                        return "$key:{$value}";
                    }, array_keys( $_spacing_rules[ 'rules' ] ), array_values( $_spacing_rules[ 'rules' ] )
                ) );

                $rules[] = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'style_rules' => $style_rules,
                    'mq' =>$_spacing_rules[ 'mq' ]
                );
            }
        }

        return $rules;
    }


    public function sek_dyn_css_builder_background_parse_rules( array $rules, array $level ) {
        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
        // LBB - background
        // bg-apply-overlay
        // bg-attachment
        // bg-color
        // bg-color-overlay
        // bg-image
        // bg-opacity-overlay
        // bg-position
        // bg-scale
        // bg-video

        //TODO:
        // border-color
        // border-type
        // border-width
        // boxed-wide
        // boxed-width
        // custom-height
        // height-type
        // shadow

        if ( !empty( $options[ 'lbb' ] ) ) {
            $background_properties = array();

            /* The general syntax of the background property is:
            * https://www.webpagefx.com/blog/web-design/background-css-shorthand/
            * background: [background-image] [background-position] / [background-size] [background-repeat] [background-attachment] [background-origin] [background-clip] [background-color];
            */
            // Img background
            if ( ! empty( $options['lbb'][ 'bg-image'] ) && is_numeric( $options['lbb'][ 'bg-image'] ) ) {
                //no repeat by default?
                $background_properties[] = 'url("'. wp_get_attachment_url( $options['lbb'][ 'bg-image'] ) .'")';

                // Img Bg Position
                if ( ! empty( $options['lbb'][ 'bg-position'] ) ) {
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

                    $raw_pos                    = $options['lbb'][ 'bg-position'];
                    $background_properties[]         = array_key_exists($raw_pos, $pos_map) ? $pos_map[ $raw_pos ] : $pos_map[ 'center' ];
                }


                //background size
                if ( ! empty( $options['lbb'][ 'bg-scale'] ) && 'default' != $options['lbb'][ 'bg-scale'] ) {
                    //When specifying a background-size value, it must immediately follow the background-position value.
                    if ( ! empty( $options['lbb'][ 'bg-position'] ) ) {
                        $background_properties[] = '/ ' . $options['lbb'][ 'bg-scale'];
                    } else {
                        $background_size    = $options['lbb'][ 'bg-scale'];
                    }
                }

                //add no-repeat by default?
                $background_properties[] = 'no-repeat';

                // write the bg-attachment rule only if true <=> set to "fixed"
                if ( ! empty( $options['lbb'][ 'bg-attachment'] ) && sek_is_checked( $options['lbb'][ 'bg-attachment'] ) ) {
                    $background_properties[] = 'fixed';
                }

            }


            //background color (needs validation: we need a sanitize hex or rgba color)
            if ( ! empty( $options[ 'lbb' ][ 'bg-color' ] ) ) {
                $background_properties[] = $options[ 'lbb' ][ 'bg-color' ];
            }


            //build background rule
            if ( ! empty( $background_properties ) ) {
                $background_style_rules      = "background:" . implode( ' ', array_filter( $background_properties ) );

                //do we need to add the background-size property separately?
                $background_style_rules      = isset( $background_size ) ? $style_rules . ';background-size:' . $background_size : $background_style_rules;

                $rules[] = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'style_rules' => $background_style_rules,
                    'mq' =>null
                );
            }

            //Background overlay?
            if ( ! empty( $options['lbb'][ 'bg-apply-overlay'] ) && sek_is_checked( $options['lbb'][ 'bg-apply-overlay'] ) ) {
                //(needs validation: we need a sanitize hex or rgba color)
                $bg_color_overlay = isset( $options[ 'lbb' ][ 'bg-color-overlay' ] ) ? $options[ 'lbb' ][ 'bg-color-overlay' ] : null;
                if ( $bg_color_overlay ) {
                    //overlay pseudo element
                    $bg_overlay_style_rules = 'content:"";display:block;position:absolute;top:0;left:0;right:0;bottom:0;background-color:'.$bg_color_overlay;

                    //opacity
                    //validate/sanitize
                    $bg_overlay_opacity     = isset( $options[ 'lbb' ][ 'bg-opacity-overlay' ] ) ? filter_var( $options[ 'lbb' ][ 'bg-opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                        array( "min_range"=>0, "max_range"=>100 ) )
                    ) : FALSE;
                    $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

                    $bg_overlay_style_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_style_rules . ';opacity:' . $bg_overlay_opacity : $bg_overlay_style_rules;

                    $rules[]     = array(
                            'selector' => '[data-sek-id="'.$level['id'].'"]::before',
                            'style_rules' => $bg_overlay_style_rules,
                            'mq' =>null
                    );
                    //we have to also:
                    // 1) make '[data-sek-id="'.$level['id'].'"] to be relative positioned (to make the overlay absolute element referring to it)
                    // 2) make any '[data-sek-id="'.$level['id'].'"] first child to be relative (not to the resizable handle div)
                    $rules[]     = array(
                            'selector' => '[data-sek-id="'.$level['id'].'"]',
                            'style_rules' => 'position:relative',
                            'mq' => null
                    );

                    $first_child_selector = '[data-sek-id="'.$level['id'].'"]>*';
                    //in the preview we still want some elements to be absoluted positioned
                    //1) the .ui-resizable-handle (jquery-ui)
                    //2) the block overlay
                    //3) the add content button
                    if ( is_customize_preview() ) {
                        $first_child_selector .= ':not(.ui-resizable-handle):not(.sek-block-overlay):not(.sek-add-content-button)';
                    }
                    $rules[]     = array(
                        'selector' => $first_child_selector,
                        'style_rules' => 'position:relative',
                        'mq' =>null
                    );
                }
            }

        }

        return $rules;
    }



    public function sek_dyn_css_builder_border_parse_rules( array $rules, array $level ) {
        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

        //TODO: we actually should allow multidimensional border widths plus different units
        if ( !empty( $options[ 'lbb' ] ) ) {
            $border_width = ! empty( $options['lbb'][ 'border-width' ] ) ? filter_var( $options['lbb'][ 'border-width' ], FILTER_VALIDATE_INT ) : FALSE;
            $border_type  = FALSE !== $border_width && ! empty( $options['lbb'][ 'border-type' ] ) && 'none' != $options['lbb'][ 'border-type' ] ? $options['lbb'][ 'border-type' ] : FALSE;

            //border width
            if ( $border_type ) {
                $border_properties = array();
                $border_properties[] = $border_width . 'px';

                //border type
                $border_properties[] = $border_type;

                //border color
                //(needs validation: we need a sanitize hex or rgba color)
                if ( ! empty( $options['lbb'][ 'border-color' ] ) ) {
                    $border_properties[] = $options['lbb'][ 'border-color' ];
                }

                //append border rules
                $rules[]     = array(
                        'selector' => '[data-sek-id="'.$level['id'].'"]',
                        'style_rules' => "border:" . implode( ' ', array_filter( $border_properties ) ),
                        'mq' =>null
                );
            }
        }
        return $rules;
    }




    public function sek_dyn_css_builder_boxshadow_parse_rules( array $rules, array $level ) {
        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

        if ( !empty( $options[ 'lbb' ][ 'shadow' ] ) &&  sek_is_checked( $options['lbb'][ 'shadow'] ) ) {
            $style_rules = 'box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2); -webkit-box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2);';

            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'style_rules' => $style_rules,
                    'mq' =>null
            );
        }
        return $rules;
    }


    public function sek_dyn_css_builder_height_parse_rules( array $rules, array $level ) {
        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

        if ( !empty( $options[ 'lbb' ][ 'height-type' ] ) ) {
            if ( 'fit-to-screen' == $options[ 'lbb' ][ 'height-type' ] ) {
                $height = '100';
            }
            elseif ( 'custom' == $options[ 'lbb' ][ 'height-type' ] && FALSE !== $height_value = filter_var( $options[ 'lbb' ][ 'custom-height' ], FILTER_VALIDATE_INT, array( 'options' =>
                        array( "min_range"=>0, "max_range"=>100 ) ) ) ) {
                $height = $height_value;
            }
            $style_rules = '';
            if ( isset( $height ) && FALSE !== $height ) {
                $style_rules .= 'height:' . $height . 'vh;';
            }
            if ( !empty( $options[ 'lbb' ][ 'v-alignment' ]) ) {
                switch( $options[ 'lbb' ][ 'v-alignment' ] ) {
                    case 'top' :
                        $style_rules .= "align-items: flex-start;";
                    break;
                    case 'center' :
                        $style_rules .= "align-items: center;";
                    break;
                    case 'bottom' :
                        $style_rules .= "align-items: flex-end;";
                    break;
                }
            }
            if ( !empty( $style_rules ) ) {
                $rules[]     = array(
                        'selector' => '[data-sek-id="'.$level['id'].'"]',
                        'style_rules' => $style_rules,
                        'mq' =>null
                );
            }
            //error_log( print_r($rules, true) );

        }
        return $rules;
    }


}//end class

















class Sek_Stylesheet {

    private $rules = array();


    public function sek_add_rule( $selector, $style_rules, array $mq = null ) {

        if ( ! is_string( $selector ) )
            return;

        if ( ! is_string( $style_rules ) )
            return;

        //TODO: allowed media query?
        $mq_hash = 'all';

        if ( $mq ) {
            $mq_hash = $this->sek_mq_to_hash( $mq );
        }

        if ( !isset( $this->rules[ $mq_hash ] ) ) {
            $this->sek_add_mq_hash( $mq_hash );
        }

        if ( !isset( $this->rules[ $mq_hash ][ $selector ] ) ) {
            $this->rules[ $mq_hash ][ $selector ] = array();
        }

        $this->rules[ $mq_hash ][ $selector ][] = $style_rules;
    }


    //totally Elementor inpired
    //add and sort media queries
    private function sek_add_mq_hash( $mq_hash ) {
        $this->rules[ $mq_hash ] = array();

        //TODO: test and probably improve ordering: need to think about convoluted use cases
        uksort(
            $this->rules, function( $a, $b ) {
                if ( 'all' === $a ) {
                    return -1;
                }

                if ( 'all' === $b ) {
                    return 1;
                }

                $a_query = $this->sek_hash_to_mq( $a );

                $b_query = $this->sek_hash_to_mq( $b );

                if ( isset( $a_query['min'] ) xor isset( $b_query['min'] ) ) {
                    return 1;
                }

                if ( isset( $a_query['min'] ) ) {
                    return $a_query['min'] - $b_query['min'];
                }

                return $b_query['max'] - $a_query['max'];
            }
        );
    }


    //totally Elementor inpired
    private function sek_mq_to_hash( array $mq ) {
        $hash = [];

        foreach ( $mq as $min_max => $value ) {
            $hash[] = $min_max . '_' . $value;
        }

        return implode( '-', $hash );
    }


    //totally Elementor inpired
    private function sek_hash_to_mq( $mq_hash ) {
        $mq = [];

        $mq_hash = array_filter( explode( '-', $mq_hash ) );

        foreach ( $mq_hash as $single_mq ) {
            $single_mq_parts = explode( '_', $single_mq );

            $mq[ $single_mq_parts[0] ] = $single_mq_parts[1];

        }

        return $mq;
    }


    private function sek_maybe_wrap_in_media_query( $css,  $mq_hash = 'all' ) {
        if ( 'all' === $mq_hash ) {
            return $css;
        }

        $mq           = $this->sek_hash_to_mq( $mq_hash );

        return '@media ' . implode( ' and ', array_map(
                function( $min_max, $value ) {
                    return "({$min_max}-width:{$value}px)";
                },
                array_keys( $mq ),
                array_values( $mq )
            )
        ) . '{' . $css . '}';
    }



    private function sek_parse_rules( $selector, $style_rules = array() ) {
        $style_rules = is_array( $style_rules ) ? implode( ';', $style_rules ) : $style_rules;
        return $selector . '{' . $style_rules . '}';
    }




    //stringify the stylesheet object
    public function __toString() {
        $css = '';
        foreach ( $this->rules as $mq_hash => $selectors ) {
            $_css = '';
            foreach ( $selectors as $selector => $style_rules ) {
                $_css .=  $this->sek_parse_rules( $selector, $style_rules );
            }
            $_css = $this->sek_maybe_wrap_in_media_query( $_css, $mq_hash );
            $css .= $_css;
        }

        return $css;
    }


}//end class

















/*
array(
    //Section 1
    array(
        id => 1
        collection => array(
            //column 1
            array(
                id => 12
                collection => array(
                      //module 1
                      array(
                            id => 123,
                            options => array()
                      ),
                      // module 2
                      array(),
                      ...
                ),
                options => array(),
                width => ''
            ),
            //column 2
            array(),
            ...
        ),
        options => array(
            // layout, background, border
            lbb => array(
                bg-img = ''
                bg-color = ''
            ),
            // spacing
            spacing => array(

            )
        )
    ),

    //Section  2
    array(),
    ...
)
*/
// $_sektions = array(
//     'collection' => array(

//         array(
//                 'id' => '__sek__db36a8b7642a7a5a2a19e1f6',
//                 'level' => 'section',
//                 'collection' => array(
//                         array(
//                                 'id' => '__sek__5af332dc6784ce1f1e17a3ba',
//                                 'level' => 'column',
//                                 'collection' => array(),
//                                 'options' => array(
//                                         'lbb' => array(
//                                                 'bg-color' => '#dd9933'
//                                         ),
//                                         'spacing' => array(
//                                             'desktop_pad_marg' => array(
//                                                 'padding-top' => 10,
//                                             ),
//                                         )
//                                 ),
//                         ),
//                 ),

//         ),//end sek-1

//         array(
//                 'id' => '__sek__659b99908c05fd55d7a09401',
//                 'level' => 'section',
//                 'collection' => array(
//                         array(
//                                 'id' => '__sek__da7fe9822690cc0fd0f843e9',
//                                 'level' => 'column',
//                                 'collection' => array(),
//                                 'options' => array(
//                                         'spacing' => array(
//                                             'desktop_pad_marg' => array(
//                                                 'padding-top' => 10,
//                                                 'padding-bottom' => 10,
//                                                 'margin-top' => 10,
//                                             ),
//                                             'tablet_pad_marg' => array(
//                                                 'padding-top' => 20
//                                             )
//                                         )

//                                 )
//                         )
//                 )

//         )//end sek-2
//     )//end sek collection
// );










//TEST
// require_once( 'class-sek-dyn-css-handler.php' );
// add_action( 'wp_head', function() use ( $_sektions ) {

//     $skope_id = skp_build_skope_id();

//     /*
//     * Once this file is required, the whole code below can be
//     * placed in SEK_Front_Render::print_dyn_inline_stylesheet()
//     * after
//             if ( is_null( $skope_id ) ) {
//                 $skope_id = skp_build_skope_id();
//             }
//     * in place of :
    /*?>
        <style id="sek-<?php echo $skope_id; ?>" type="text/css">
            <?php // COLUMN WIDTH ?>
            @media (min-width: 768px) { <?php echo $this -> print_custom_width_styles( $skope_id ); ?> }
            <?php // SECTION BACKGROUND ?>
            <?php echo $this -> print_level_background( $skope_id ); ?>
        </style>
    <?php*/

//     Also remember to uncomment the very line below
//     */
//     //$_sektions = sek_get_skoped_seks( $skope_id );

//     //build stylesheet
//     $stylesheet = new Sek_Stylesheet();
//     $builder    = new Sek_Dyn_CSS_Builder( $_sektions, $stylesheet );
//     $builder->sek_dyn_css_builder_build_stylesheet();


//     //enqueuing
//     $dyn_css_handler_params = array(
//         'id'             => $skope_id,
//         'mode'           => Sek_Dyn_CSS_Handler::MODE_FILE,
//         //these are taken in account only when 'mode' is 'file'
//         'force_write'    => true, //<- write if the file doesn't exist

//         //TEMPORARY: we actually need to refresh the file on customize_save only when needed
//         'force_rewrite'  => true, //<- write even if the file exists
//     );

//     $_is_preview     = is_customize_preview();

//     if ( $_is_preview ) {
//         $dyn_css_handler_params = array_merge( $dyn_css_handler_params, array(
//             'mode'       => Sek_Dyn_CSS_Handler::MODE_INLINE,
//         ) );
//     }

//     //Init the enqueuer
//     $dyn_css_handler = new Sek_Dyn_CSS_Handler( $dyn_css_handler_params );
//     $dyn_css_handler->sek_dyn_css_set_css( (string)$stylesheet );

//     //finally enqueue
//     $dyn_css_handler->sek_dyn_css_enqueue();
// });

?><?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
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
    private $write_only = false;


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
            'write_only'                      => false,//<= used when writing the css file on Sek_Customizer_Setting::update()
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
        $this->_sek_dyn_css_build_properties();

        // Possible scenarios :
        // 1) customizing :
        //    the css is always printed inline. If there's already an existing css file for this skope_id, it's not enqueued.
        // 2) saving in the customizer :
        //    the css file is written in a "force_rewrite" mode, meaning that any existing css file gets re-written.
        //    There's no enqueing scheduled, 'write_only' mode.
        // 3) front, user logged in + 'customize' capabilities :
        //    the css file is re-written on each page load + enqueued. If writing a css file is not possible, we fallback on inline printing.
        // 4) front, user not logged in :
        //    the normal behaviour is that the css file is enqueued.
        //    It should have been written when saving in the customizer. If no file available, we try to write it. If writing a css file is not possible, we fallback on inline printing.
        if ( is_customize_preview() || ! $this->_sek_dyn_css_file_exists() || $this->force_rewrite ) {
            $_sektions = sek_get_skoped_seks( $this -> skope_id );

            //build stylesheet
            $stylesheet = new Sek_Stylesheet();
            $builder    = new Sek_Dyn_CSS_Builder( $_sektions, $stylesheet );
            //Already done in the constructor
            //$builder->sek_dyn_css_builder_build_stylesheet();
            $this->sek_dyn_css_set_css( (string)$stylesheet );
        }

        //hook setup for printing or enqueuing
        //bail if "write_only" == true, typically when saving the customizer settings @see Sek_Customizer_Setting::update()
        if ( ! $this->write_only ) {
            $this->_schedule_css_enqueuing_or_printing_maybe_on_custom_hook();
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
    private function _sek_dyn_css_build_properties() {
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
    private function _schedule_css_enqueuing_or_printing_maybe_on_custom_hook() {
        if ( $this->hook ) {
            add_action( $this->hook, array( $this, 'sek_dyn_css_enqueue_or_print' ), $this->priority );
        } else {
            //enqueue or print
            $this->sek_dyn_css_enqueue_or_print();
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
     */
    public function sek_dyn_css_enqueue_or_print() {
        //case enqueue file
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
                return true;
            }

        }

        //if $this->mode != 'file' or the file enqueuing didn't go through (fall back)
        //print inline style
        if ( $this->css_string_to_enqueue_or_print ) {
            $dep =  array_pop( $this->dep );

            if ( !$dep || wp_style_is( $dep, 'done' ) || !wp_style_is( $dep, 'done' ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                printf( '<style id="sek-%1$s" type="text/css" media="all">%2$s</style>', $this->id, $this->css_string_to_enqueue_or_print );
            } else {
                //not sure
                wp_add_inline_style( $dep , $this->css_string_to_enqueue_or_print );
            }

            $this->mode     = self::MODE_INLINE;
            $this->enqueued_or_printed = true;
            return true;
        }

        return false;
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



    /*
    * Public Getters
    */

    /**
     *
     * Retrieve the enqueuing status
     *
     * @access public
     *
     * @return bool TRUE if the style has been enqueued_or_printed, FALSE otherwise
     */
    public function sek_dyn_css_get_enqueued_or_printed_status() {
        return $this->enqueued_or_printed;
    }


    /**
     *
     * Get the mode
     *
     * Retrieve the functioning mode
     *
     * @access public
     *
     * @return string MODE_INLINE or MODE_FILE
     */
    public function sek_dyn_css_get_mode() {
        return $this->mode;
    }


    /**
     *
     * Retrieve the file_exists property
     *
     *
     * @access public
     *
     * @return bool TRUE if the CSS file exists, FALSE otherwise
     */
    public function sek_dyn_css_file_exists() {
        return $this->file_exists;
    }


    /*
    * Setters
    */
    /**
     *
     * Set CSS file from the disk, if it exists
     *
     * @access public
     *
     * @return void()
     */
    public function sek_dyn_css_set_css( $css ) {
        $this->css_string_to_enqueue_or_print = $css;
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
////////////////////////////////////////////////////////////////
// FLAT SKOPE BASE
//  This Class is instantiated on 'hu_hueman_loaded', declared in /init-core.php
if ( ! class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $local_seks = 'not_cached';// <= used to cache the sektions for the local skope_id
        public $model = array();//<= when rendering, the current level model
        public $parent_model = array();//<= when rendering, the current parent model
        public $ajax_action_map = array();

        public static function sek_get_instance( $params ) {
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
                  'sek-set-module-value',

                  'sek-refresh-stylesheet',

                  'sek-refresh-level'
            );
        }

        // hook : 'wp_ajax_sek_get_html_for_injection'
        function sek_get_level_content_for_injection( $params ) {
            // error_log('<ajax sek_get_level_content_for_injection>');
            // error_log( print_r( $_POST, true ) );
            // error_log('</ajax sek_get_level_content_for_injection>');
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
            // is this action possible ?
            if ( in_array( $sek_action, $this -> ajax_action_map ) ) {
                $html = $this -> sek_ajax_fetch_content( $sek_action );
                if ( is_wp_error( $html ) ) {
                    wp_send_json_error( $html );
                }
            } else {
                wp_send_json_error(  __FUNCTION__ . ' => this ajax action ( ' . $sek_action . ' ) is not listed in the map ' );
            }

            wp_send_json_success( apply_filters( 'sek_content_results', $html, $sek_action ) );
        }//sek_get_content_for_injection()


        // hook : add_filter( "sek_set_ajax_content___{$action}", array( $this, 'sek_ajax_fetch_content' ) );
        // $_POST looks like Array
        // (
        //     [action] => sek_get_content
        //     [withNonce] => false
        //     [id] => __sek__0b7c85561448ab4eb8adb978
        //     [skope_id] => skp__post_page_home
        //     [sek_action] => sek-add-section
        //     [SEKFrontNonce] => 3713b8ac5c
        //     [customized] => {\"sek___loop_start[skp__post_page_home]\":{...}}
        // )
        // @return string
        // @param $sek_action is $_POST['sek_action']
        private function sek_ajax_fetch_content( $sek_action = '' ) {
            // error_log('<ajax sek_ajax_fetch_content>');
            // error_log( print_r( $_POST, true ) );
            // error_log('</ajax sek_ajax_fetch_content>');
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
                    //  error_log('<ajax sek-remove-section>');
                    // error_log( print_r( $_POST, true ) );
                    // error_log('</ajax sek-remove-section>');
                break;

                //only used for nested section
                case 'sek-remove-section' :
                    // error_log('<ajax sek-remove-section>');
                    // error_log( print_r( $_POST, true ) );
                    // error_log('</ajax sek-remove-section>');
                    if ( ! array_key_exists( 'is_nested', $_POST ) || true !== json_decode( $_POST['is_nested'] ) ) {
                        wp_send_json_error(  __FUNCTION__ . ' sek-remove-section => the section must be nested in this ajax action' );
                        break;
                    } else {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    }
                break;

                case 'sek-duplicate-section' :
                    // error_log('<ajax sek-duplicate-section>');
                    // error_log( print_r( $_POST, true ) );
                    // error_log('</ajax sek-duplicate-section>');
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
                    // error_log('<sek_ajax_fetch_content => $_POST>');
                    // error_log( print_r( $_POST, true ) );
                    // error_log('</sek_ajax_fetch_content => $_POST>');
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST[ 'in_sektion' ] ) ) {
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $this -> parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                break;

                case 'sek-set-module-value' :
                    //error_log( print_r( $_POST, true ) );
                    if ( ! array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __FUNCTION__ . ' ' . $sek_action .' => missing module id' );
                        break;
                    }

                    $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
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
                    //error_log( print_r( $_POST, true ) );
                    if ( ! array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __FUNCTION__ . ' ' . $sek_action .' => missing level id' );
                        break;
                    }
                    $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                break;
            }//Switch sek_action

            // error_log( '<$_POST AJAXING>');
            // error_log( print_r( $_POST, true ) );
            // error_log( '</$_POST AJAXING>');
            // error_log( '<///////////////////////////////////////////////////>');
            // error_log( '<PARENT LEVEL MODEL WHEN AJAXING>');
            // error_log( print_r( $this -> parent_model, true ) );
            // error_log( '</PARENT LEVEL MODEL WHEN AJAXING>');
            // error_log( '<///////////////////////////////////////////////////>');
            // error_log( '<LEVEL MODEL WHEN AJAXING>');
            // error_log( 'ALORS? => ' . $sek_action );
            // error_log( print_r( $level_model, true ) );
            // error_log( '</LEVEL MODEL WHEN AJAXING>');

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

            error_log( '<$_POST AJAXING>');
            error_log( print_r( $_POST, true ) );
            error_log( '</$_POST AJAXING>');
            error_log( '<///////////////////////////////////////////////////>');
            error_log( '<PARENT LEVEL MODEL WHEN AJAXING>');
            error_log( print_r( $this -> parent_model, true ) );
            error_log( '</PARENT LEVEL MODEL WHEN AJAXING>');
            error_log( '<///////////////////////////////////////////////////>');
            error_log( '<LEVEL MODEL WHEN AJAXING>');
            error_log( print_r( $this -> model , true ) );
            error_log( '</LEVEL MODEL WHEN AJAXING>');


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
            //wp_enqueue_style( 'google-material-icons', '//fonts.googleapis.com/icon?family=Material+Icons', array(), null, 'all' );
         /*   wp_register_style(
                'sek-bootstrap',
                NIMBLE_BASE_URL . '/inc/sektions/assets/front/css/custom-bootstrap.css',
                array(),
                time(),
                'all'
            );*/
            //base custom CSS bootstrap inspired
            wp_enqueue_style(
                'sek-base',
                NIMBLE_BASE_URL . '/assets/front/css/sek-base.css',
                array(),
                time(),
                'all'
            );
            wp_enqueue_style(
                'sek-main',
                NIMBLE_BASE_URL . '/assets/front/css/sek-main.css',
                array( 'sek-base' ),
                time(),
                'all'
            );
            wp_enqueue_style(
                'font-awesome',
                NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                array(),
                time(),
                $media = 'all'
            );

            wp_register_script(
                'sek-front-fmk-js',
                NIMBLE_BASE_URL . '/assets/front/js/_front_js_fmk.js',
                array( 'jquery', 'underscore'),
                time(),
                true
            );
            wp_enqueue_script(
                'sek-main-js',
                NIMBLE_BASE_URL . '/assets/front/js/sek-main.js',
                array( 'jquery', 'sek-front-fmk-js'),
                time(),
                true
            );
            wp_localize_script(
                'sek-main-js',
                'sekFrontLocalized',
                array(
                    'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('CZR_DEV') && true === CZR_DEV ),
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
                )
            );
        }

        // enqueue / print customize preview assets
        // hook : 'customize_preview_init'
        function sek_schedule_customize_preview_assets() {
            // Load preview ui js tmpl
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                NIMBLE_BASE_URL . '/assets/czr/sek/css/sek-preview.css',
                array( 'sek-main' ),
                time(),
                'all'
            );

            // Communication between preview and customizer panel
            wp_enqueue_script(
                'sek-customize-preview',
                NIMBLE_BASE_URL . '/assets/czr/sek/js/sek-preview.js',
                array( 'customize-preview', 'underscore'),
                time(),
                true
            );
            wp_localize_script(
                'sek-customize-preview',
                'sektionsLocalizedData',
                array(
                    'i18n' => array(
                        "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
                    )
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
                  <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                    <div class="sek-add-content-button-wrapper">
                      <button data-sek-action="add-content" class="sek-add-content-btn" style="--sek-add-content-btn-width:60px;">
                        <span title="<?php _e('Add Content', 'text_domain_to_be_replaced' ); ?>" class="sek-action-button-icon fas fa-plus-circle sek-action"></span><span class="action-button-text"><?php _e('Add Content', 'text_domain_to_be_replaced' ); ?></span>
                      </button>
                    </div>
                  </div>
              </script>

              <script type="text/html" id="sek-tmpl-overlay-ui-section">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <# //console.log( 'data', data ); #>
                  <div class="sek-block-overlay sek-section-overlay">
                    <div class="sek-block-overlay-header">
                      <div class="sek-block-overlay-actions">
                        <?php // if this is a nested section, it has the is_nested property set to true. We don't want to make it movable for the moment. @todo ?>
                        <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <# if ( ! data.is_last_possible_section ) { #>
                          <i class="fas fa-arrows-alt sek-move-section" title="<?php _e( 'Move', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Options', 'sek-builder' ); ?>"></i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-action="add-column" class="fas fa-plus-circle sek-action" title="<?php _e( 'Add Column', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove', 'sek-builder' ); ?>"></i>
                      </div>

                      <div class="sek-clear"></div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div>
                    <?php endif; ?>
                  </div>
              </script>

              <script type="text/html" id="sek-tmpl-overlay-ui-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-block-overlay sek-column-overlay">
                    <div class="sek-block-overlay-header">
                      <div class="sek-block-overlay-actions">
                        <i class="fas fa-arrows-alt sek-move-column" title="<?php _e( 'Move', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Options', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="pick-module" class="fas fa-plus-circle sek-action" title="<?php _e( 'Add Module', 'sek-builder' ); ?>"></i>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <# if ( ! data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-action="add-section" class="fas far fa-plus-square sek-action" title="<?php _e( 'Add Sektion', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <# if ( ! data.parent_is_single_column ) { #>
                          <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove', 'sek-builder' ); ?>"></i>
                        <# } #>
                      </div>
                      <div class="sek-clear"></div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div>
                    <?php endif; ?>
                  </div>
              </script>

              <script type="text/html" id="sek-tmpl-overlay-ui-module">
                  <div class="sek-block-overlay sek-module-overlay">
                    <div class="editor-block-settings-menu"><?php // add class  is-visible on hover ?>
                      <div>
                        <div>
                          <button type="button" aria-expanded="false" aria-label="More Options" class="components-button components-icon-button editor-block-settings-menu__toggle">
                            <svg aria-hidden="true" role="img" focusable="false" class="dashicon dashicons-ellipsis" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                              <path d="M5 10c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm12-2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-7 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z">
                              </path>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div><?php // .editor-block-settings-menu ?>
                    <div class="sek-block-overlay-header">
                      <div class="sek-block-overlay-actions">
                        <i class="fas fa-arrows-alt sek-move-module" title="<?php _e( 'Move', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="edit-module" class="fas fa-pencil-alt sek-tip sek-action" title="<?php _e( 'Edit Module', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Options', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove', 'sek-builder' ); ?>"></i>
                      </div>
                      <div class="sek-clear"></div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div>
                    <?php endif; ?>
                  </div><?php // .sek-block-overlay-header ?>
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
            foreach( sek_get_locations() as $hook ) {
                switch ( $hook ) {
                    case 'loop_start' :
                    case 'loop_end' :
                        add_action( $hook, array( $this, 'sek_schedule_sektions_rendering' ) );
                    break;
                    case 'before_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), -9999 );
                    break;
                    case 'after_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), 9999 );
                    break;
                }
            }

            // add_filter( 'template_include', function( $template ) {
            //       // error_log( 'TEMPLATE ? => ' . $template );
            //       // error_log( 'DID_ACTION WP => ' . did_action('wp') );
            //       return dirname( __FILE__ ). "/tmpl/page-templates/full-width.php";// $template;
            // });
        }

        // hook : loop_start, loop_end
        function sek_schedule_sektions_rendering() {
            $this->_render_seks_for_location( current_filter() );
        }

        // hook : before_content
        function sek_schedule_sektion_rendering_before_content( $html ) {
            return $this -> _filter_the_content( $html, 'before_content' );
        }

        // hook : after_content
        function sek_schedule_sektion_rendering_after_content( $html ) {
            return $this -> _filter_the_content( $html, 'after_content' );
        }

        private function _render_seks_for_location( $location = '' ) {
            if ( ! in_array( $location,sek_get_locations() ) ) {
                error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location . ' is not registered in sek_get_locations()');
                return;
            }
            $locationSettingValue = sek_get_skoped_seks( skp_build_skope_id(), $location );
            if ( is_array( $locationSettingValue ) ) {
                // error_log( '<LEVEL MODEL IN ::sek_schedule_sektions_rendering()>');
                // error_log( print_r( $locationSettingValue, true ) );
                // error_log( '</LEVEL MODEL IN ::sek_schedule_sektions_rendering()>');
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), -9999 );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), 9999 );

                $this->render( $locationSettingValue, $location );

                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), -9999 );
                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), 9999 );
            } else {
                error_log( __CLASS__ . ' :: ' . __FUNCTION__ .' => sek_get_skoped_seks() should always return an array().');
            }
        }

        private function _filter_the_content( $html, $where ) {
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                ob_start();
                $this->_render_seks_for_location( $where );
                return 'before_content' == $where ? ob_get_clean() . $html : $html . ob_get_clean();
            }
            return $html;
        }



        // Walk a model tree recursively and render each level with a specific template
        // Each level is described with at least 2 properties : collection and options
        function render( $model = array(), $location = 'loop_start' ) {
            // error_log( '<LEVEL MODEL IN ::RENDER()>');
            // error_log( print_r( $model, true ) );
            // error_log( '</LEVEL MODEL IN ::RENDER()>');
            // Is it the root level ?
            // The root level has no id and no level entry
            if ( ! array_key_exists( 'level', $model ) || ! array_key_exists( 'id', $model ) ) {
                error_log( 'render => a level model is missing the level or the id property' );
                return;
            }
            $id = $model['id'];
            $level = $model['level'];

            // Cache the parent model
            // => used when calculating the width of the column to be added
            $parent_model = $this -> parent_model;
            $this -> model = $model;

            $collection = array_key_exists('collection', $model ) ? $model['collection'] : array();

            switch ( $level ) {
                case 'location' :
                    ?>
                      <div class="sektion-wrapper" data-sek-level="location" data-sek-id="<?php echo $location ?>">
                        <?php
                          $this -> parent_model = $model;
                          foreach ( $collection as $_key => $sec_model ) { $this -> render( $sec_model ); }
                        ?>
                        <?php if ( skp_is_customizing() ) : ?>
                            <div class="sek-add-button-wrapper"><button class="btn btn-edit" data-sek-add="section"><?php _e( '+ Add a section', 'text_domain_to_be_replaced'); echo ' ' . $location; ?></button></div>
                        <?php endif; ?>
                      </div>
                    <?php
                break;

                case 'section' :
                    $is_nested            = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
                    $column_wrapper_class = 'sek-container-fluid';
                    //when boxed use proper container class
                    if ( ! empty( $model[ 'options' ][ 'lbb' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'lbb' ][ 'boxed-wide' ] ) {
                      $column_wrapper_class = 'sek-container';
                    }
                    ?>
                    <?php printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section">', $id, $is_nested ? 'data-sek-is-nested="true"' : '' ); ?>
                          <div class="<?php echo $column_wrapper_class ?> sek-column-wrapper">
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
                    // error_log( '<PARENT MODEL WHEN RENDERING>');
                    // error_log( print_r( $parent_model, true ) );
                    // error_log( '</PARENT MODEL WHEN RENDERING>');

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
                          <div class="sek-module-wrapper">
                            <div class="sek-column-inner <?php echo empty( $collection ) ? 'sek-empty-col' : ''; ?>">
                                <?php
                                  if ( empty( $collection ) ) {
                                      ?>
                                      <div class="sek-no-modules-column">
                                        <div class="sek-module-drop-zone-for-first-module">
                                          <i data-sek-action="pick-module" class="fas fa-plus-circle sek-action" title="Add Module"></i>
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
                      </div>
                    <?php
                break;

                case 'module' :
                    ?>
                      <div data-sek-level="module" data-sek-id="<?php echo $id; ?>" class="sek-module">
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
            load_template( $render_tmpl_path, false );

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


        // Utility to print the text content generated with tinyMce
        // should be wrapped in a specific selector when customizing,
        //  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
        function sek_print_tiny_mce_text_content( $tiny_mce_content, $input_id, $module_model ) {
            if ( empty( $tiny_mce_content ) ) {
                echo $this -> sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
            } else {
                $content = apply_filters( 'the_content', $tiny_mce_content );
                if ( skp_is_customizing() ) {
                    printf('<div title="%3$s" data-sek-input-type="tiny_mce_editor" data-sek-input-id="%1$s">%2$s</div>', $input_id, $content, __('Click to edit', 'here') );
                } else {
                    echo $content;
                }
            }
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
function SEK_Front( $params = array() ) {
    return SEK_Front_Render_Css::sek_get_instance( $params );
}
SEK_Front();
?>