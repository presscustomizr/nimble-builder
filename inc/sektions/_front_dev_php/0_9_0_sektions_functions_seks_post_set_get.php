<?php
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
function sek_get_skoped_seks( $skope_id = '', $location_id = '', $skope_level = 'local' ) {
    if ( empty( $skope_id ) ) {
        $skope_id = skp_get_skope_id( $skope_level );
    }
    $is_global_skope = NIMBLE_GLOBAL_SKOPE_ID === $skope_id;
    $is_cached = false;

    // use the cached value when available ( after did_action('wp') )
    if ( did_action('wp') ) {
        if ( !$is_global_skope && 'not_cached' != SEK_Fire()->local_seks ) {
            $is_cached = true;
            $seks_data = SEK_Fire()->local_seks;
        }
        if ( $is_global_skope && 'not_cached' != SEK_Fire()->global_seks ) {
            $is_cached = true;
            $seks_data = SEK_Fire()->global_seks;
        }
    }

    if ( ! $is_cached ) {
        $seks_data = array();
        $post = sek_get_seks_post( $skope_id );
        if ( $post ) {
            $seks_data = maybe_unserialize( $post->post_content );
        }
        $seks_data = is_array( $seks_data ) ? $seks_data : array();

        // normalizes
        // [ 'collection' => [], 'local_options' => [] ];
        $default_collection = sek_get_default_location_model( $skope_id );
        $seks_data = wp_parse_args( $seks_data, $default_collection );

        // Maybe add missing registered locations
        $maybe_incomplete_locations = [];
        foreach( $seks_data['collection'] as $location_data ) {
            if ( !empty( $location_data['id'] ) ) {
                $maybe_incomplete_locations[] = $location_data['id'];
            }
        }

        foreach( SEK_Fire()->registered_locations as $loc_id => $params ) {
            if ( !in_array( $loc_id, $maybe_incomplete_locations ) ) {
                $seks_data['collection'][] = wp_parse_args( [ 'id' => $loc_id ], SEK_Fire()->default_location_model );
            }
        }
        // cache now
        if ( $is_global_skope ) {
            SEK_Fire()->global_seks = $seks_data;
        } else {
            SEK_Fire()->local_seks = $seks_data;
        }

    }//end if

    // when customizing, let us filter the value with the 'customized' ones
    $seks_data = apply_filters(
        'sek_get_skoped_seks',
        $seks_data,
        $skope_id,
        $location_id
    );

    // sek_error_log( '<sek_get_skoped_seks() location => ' . $location .  array_key_exists( 'collection', $seks_data ), $seks_data );
    // if a location is specified, return specifically the sections of this location
    if ( array_key_exists( 'collection', $seks_data ) && ! empty( $location_id ) ) {
        if ( ! array_key_exists( $location_id, sek_get_locations() ) ) {
            error_log( __FUNCTION__ . ' Error => location ' . $location_id . ' is not registered in the available locations' );
        } else {
            $seks_data = sek_get_level_model( $location_id, $seks_data['collection'] );
        }
    }

    return 'no_match' === $seks_data ? SEK_Fire()->default_location_model : $seks_data;
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










/* ------------------------------------------------------------------------- *
 *  SAVED SEKTIONS
/* ------------------------------------------------------------------------- */
// SAVED SEKTIONS POST TYPE
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

// @return the saved sektion data collection : columns, options as an array
function sek_get_saved_sektion_data( $saved_section_id ) {
    $sek_post = sek_get_saved_seks_post( $saved_section_id );
    $section_data = array();
    if ( $sek_post ) {
        $section_data_decoded = maybe_unserialize( $sek_post -> post_content );
        // The section data are described as an array
        // array(
        //     'title' => '',
        //     'description' => '',
        //     'id' => '',
        //     'type' => 'content',//in the future will be used to differentiate header, content and footer sections
        //     'creation_date' => date("Y-m-d H:i:s"),
        //     'update_date' => '',
        //     'data' => array(),<= this is where we describe the columns and options
        //     'nimble_version' => NIMBLE_VERSION
        // )
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
    // $sek_post_query_vars = array(
    //     'post_type'              => NIMBLE_CPT,
    //     'post_status'            => get_post_stati(),
    //     'name'                   => sanitize_title( $saved_section_id ),
    //     'posts_per_page'         => 1,
    //     'no_found_rows'          => true,
    //     'cache_results'          => true,
    //     'update_post_meta_cache' => false,
    //     'update_post_term_cache' => false,
    //     'lazy_load_term_meta'    => false,
    // );

    $post = null;
    $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
    $section_data = array_key_exists( $saved_section_id, $all_saved_seks ) ? $all_saved_seks[$saved_section_id] : array();
    $post_id = array_key_exists( 'post_id', $section_data ) ? $section_data['post_id'] : -1;

    // if the options has not been set yet, it will return (int) 0
    if ( 0 > $post_id ) {
        //error_log( 'sek_get_seks_post => post_id is not valid for options => ' . $saved_section_id );
        return;
    }

    if ( ! is_int( $post_id ) ) {
        error_log( __FUNCTION__ .' => post_id ! is_int() for options => ' . $saved_section_id );
    }

    if ( is_int( $post_id ) && $post_id > 0 && get_post( $post_id ) ) {
        $post = get_post( $post_id );
    }

    // // `-1` indicates no post exists; no query necessary.
    // if ( ! $post && -1 !== $post_id ) {
    //     $query = new WP_Query( $sek_post_query_vars );
    //     $post = $query->post;
    //     $post_id = $post ? $post->ID : -1;
    //     /*
    //      * Cache the lookup. See sek_update_sek_post().
    //      * @todo This should get cleared if a skope post is added/removed.
    //      */
    //     update_option( $option_name, (int)$post_id );
    // }

    return $post;
}




 // Update the `nimble_saved_seks` post for a given "{$skope_id}"
 // Inserts a `nimble_saved_seks` post when one doesn't yet exist.
 // $seks_data = array(
//     'title' => $_POST['sek_title'],
//     'description' => $_POST['sek_description'],
//     'id' => $_POST['sek_id'],
//     'type' => 'content',//in the future will be used to differentiate header, content and footer sections
//     'creation_date' => date("Y-m-d H:i:s"),
//     'update_date' => '',
//     'data' => $_POST['sek_data']
// )
// @return WP_Post|WP_Error Post on success, error on failure.
function sek_update_saved_seks_post( $seks_data ) {
    if ( ! is_array( $seks_data ) ) {
        error_log( 'sek_update_saved_seks_post => $seks_data is not an array' );
        return new WP_Error( 'sek_update_saved_seks_post => $seks_data is not an array');
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

    // Update post if it already exists, otherwise create a new one.
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
?>