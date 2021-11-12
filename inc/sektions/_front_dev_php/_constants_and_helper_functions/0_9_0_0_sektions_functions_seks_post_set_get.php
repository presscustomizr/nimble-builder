<?php
// SEKTION POST
register_post_type( NIMBLE_CPT , array(
    'labels' => array(
      'name'          => sek_is_cpt_debug_mode() ? __( '[NB debug] skoped section') : __( 'NB skoped section'),
      'singular_name' => __( 'NB skoped section')
    ),
    'public'           => sek_is_cpt_debug_mode(),
    'hierarchical'     => false,
    'rewrite'          => false,
    'query_var'        => false,
    'delete_with_user' => false,
    'can_export'       => true,
    //'_builtin'         => true, /* internal use only. don't use this when registering your own post type. */
    'supports'         => sek_is_cpt_debug_mode() ? array( 'editor', 'title', 'revisions' ) : array( 'title', 'revisions' ),
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

// Returns the id of the post in which the local collection is stored
// This option NIMBLE_OPT_SEKTION_POST_INDEX is updated when publishing in the customizer and may also be updated when getting the collection in sek_get_seks_post()
// introduced for #799
function sek_get_nb_post_id_from_index( $skope_id ) {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    $post_id = 0;
    // Backward compat => march 2021, NB introduces a new option 'nimble_posts_index' dedicated to store the NB post_id associated to a skope_id.
    // For previous user, a backward compatibility code is ran on each load at 'wp_loaded', to transfer all previous options to the new one.
    // if the transfer went wrong, or if the option 'nimble_posts_index' was deleted, we can attempt to get the post_id from the previous option
    if ( !is_array( $nb_posts_index ) ) {
        $post_id = get_option( $option_name );
    } else {
        if ( array_key_exists( $option_name, $nb_posts_index ) ) {
            $post_id = (int)$nb_posts_index[$option_name];
        }
    }
    return $post_id;
}

// Associates a skope_id to a NB post id in the NB post index option
// introduced for #799
function sek_set_nb_post_id_in_index( $skope_id, $post_id ) {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $nb_posts_index = is_array($nb_posts_index) ? $nb_posts_index : [];
    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    $nb_posts_index[$option_name] = (int)$post_id;
    update_option( NIMBLE_OPT_SEKTION_POST_INDEX, $nb_posts_index, 'no');
}

// Associates a skope_id to a NB post id in the NB post index option
// introduced for #799
function sek_remove_nb_post_id_in_index( $skope_id ) {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $nb_posts_index = is_array($nb_posts_index) ? $nb_posts_index : [];
    $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    if ( array_key_exists( $option_name, $nb_posts_index ) ) {
        unset( $nb_posts_index[$option_name] );
    }
    update_option( NIMBLE_OPT_SEKTION_POST_INDEX, $nb_posts_index, 'no');
}


// @return int
function sek_get_index_for_api() {
    $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
    $nb_posts_index = is_array( $nb_posts_index ) ? $nb_posts_index : [];
    return count( $nb_posts_index );
}

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

    $cached_seks_posts = Nimble_Manager()->seks_posts;
    if ( !is_array($cached_seks_posts) ) {
        sek_error_log( __FUNCTION__ .' => error => $cached_seks_posts must be an array' );
        $cached_seks_posts = array();
    }

    if ( !skp_is_customizing() && array_key_exists( $skope_id, $cached_seks_posts ) && !empty( $cached_seks_posts[$skope_id] ) ) {
        return $cached_seks_posts[$skope_id];
    }
    //sek_error_log('sek_get_seks_post => ' . $skope_id . ' skope level : ' . $skope_level );

    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),//'publish'
        'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );

    $post = null;

    // First attempt to query the post with its ID stored in the index
    // If no results or post has been trashed, NB will try to get it with a query by name + update the index of skoped post ids
    $post_id = sek_get_nb_post_id_from_index( $skope_id );

    if ( !is_int( $post_id ) ) {
        error_log( 'sek_get_seks_post => post_id !is_int() for options => ' . NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id );
    }
    // if the options has not been set yet, it will return (int) 0
    // id #1 is already taken by the 'Hello World' post.
    // skip this check when in NIMBLE_CPT_DEBUG_MODE
    if ( 1 > $post_id && !( defined( "NIMBLE_CPT_DEBUG_MODE" ) && NIMBLE_CPT_DEBUG_MODE ) ) {
        //error_log( 'sek_get_seks_post => post_id is not valid for options => ' . NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id );
        return;
    }

    if ( is_int( $post_id ) && $post_id > 0 ) {
        $post = get_post( $post_id );
    }

    $no_post_found = !$post || -1 !== $post_id;
    $post_trashed = !empty($post) && is_object($post) && 'trash' === $post->post_status;

    // `-1` indicates no post exists; no query necessary.
    // always query post when in NIMBLE_CPT_DEBUG_MODE
    if ( $no_post_found || $post_trashed ) {
        $query = new \WP_Query( $sek_post_query_vars );
        $post = $query->post;
        $post_id = $post ? $post->ID : -1;
        /*
         * Cache the lookup. See sek_update_sek_post().
         * @todo This should get cleared if a skope post is added/removed.
         */
        sek_set_nb_post_id_in_index( $skope_id, (int)$post_id );
    }
    
    if ( !skp_is_customizing() ) {
        $cached_seks_posts[$skope_id] = $post;
        Nimble_Manager()->seks_posts = $cached_seks_posts;
        return $cached_seks_posts[$skope_id];
    } else {
        return $post;
    }
}


function sek_set_ids( $collection ) {
    if ( is_array( $collection ) ) {
        // if ( array_key_exists('level', $collection ) && in_array( $collection['level'], ['section', 'column', 'module'] ) && array_key_exists('id', $collection ) ) {
        //     $collection['id'] = sek_generate_level_guid();
        // }
        foreach( $collection as $key => $data ) {
            if ( '__rep__me__' === $data && 'id' === $key ) {
                $collection[$key] = sek_generate_level_guid();
            } else if ( is_array( $data ) ) {
                $collection[$key] = sek_set_ids($data);
            }
        }
    }
    return $collection;
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
    $cache_key = 'nimble_get_skoped_seks_' . $skope_id;
    $cached = wp_cache_get( $cache_key );

    // use the cached value when available ( after did_action('wp') )
    if ( did_action('wp') ) {
        if ( is_array( $cached ) ) {
            $is_cached = true;
            $seks_data = $cached;
        }
    }

    // If not cached get the seks data from the skoped post
    if ( !$is_cached ) {
        $default_collection = sek_get_default_location_model( $skope_id );
        // Feb 2021 : filter skope id now
        // if the current context has no local sektions set and a site template set, replace the skope id by the group skope id
        // if ( !$is_global_skope ) {
        //     $skope_id = apply_filters( 'nb_set_skope_id_before_caching_local_sektions', $skope_id );
        //     //sek_error_log('alors local skope id for fetching local sections ?', $skope_id );
        // }
        $seks_data = sek_get_seks_without_group_inheritance( $skope_id );
        
        // March 2021 : added for site templates #478
        // Use site template if
        // - ! global skope
        // - no local skoped sections
        // - a site template is defined for this "group" skope
        if ( 'local' === $skope_level && !$is_global_skope ) {
            $seks_data = sek_maybe_get_seks_for_group_site_template( $skope_id, $seks_data );
        }

        // normalizes
        // [ 'collection' => [], 'local_options' => [], "fonts": [], '__inherits_group_skope_tmpl_when_exists__' => true ];
        $seks_data = wp_parse_args( $seks_data, $default_collection );

        // Maybe add missing registered locations
        $seks_data = sek_maybe_add_incomplete_locations( $seks_data, $is_global_skope );

        // cache now 
        wp_cache_set( $cache_key, $seks_data );
        //sek_error_log('/////////////////////////// CACHED for skope ' . $skope_id);
    }//end if

    if ( skp_is_customizing() ) {
        // when customizing, let us filter the value with the 'customized' ones
        $seks_data = apply_filters(
            'sek_get_skoped_seks',
            $seks_data,
            $skope_id,
            $location_id
        );

        if ( 'local' === $skope_level && !$is_global_skope ) {
            $seks_data = is_array( $seks_data ) ? $seks_data : array();
            if ( !array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $seks_data ) ) {
                // Retro-compat => make sure we set property '__inherits_group_skope_tmpl_when_exists__' to false if it's not set yet, because NB bases group inheritance on it
                $seks_data['__inherits_group_skope_tmpl_when_exists__'] = false;
            }
            $seks_data = sek_maybe_get_seks_for_group_site_template( $skope_id, $seks_data );
        }

        $default_collection = sek_get_default_location_model( $skope_id );
        $seks_data = wp_parse_args( $seks_data, $default_collection );
        // Maybe add missing registered locations when customizing
        // December 2020 => needed when importing an entire template
        $seks_data = sek_maybe_add_incomplete_locations( $seks_data, $is_global_skope );
    }

    // if a location is specified, return specifically the sections of this location
    if ( array_key_exists( 'collection', $seks_data ) && !empty( $location_id ) ) {
        // sek_error_log( 'sek_get_skoped_seks() location => ' . $location_id .  array_key_exists( 'collection', $seks_data ) );
        if ( !array_key_exists( $location_id, sek_get_locations() ) ) {
            error_log( __FUNCTION__ . ' Error => location ' . $location_id . ' is not registered in the available locations' );
        } else {
            $seks_data = sek_get_level_model( $location_id, $seks_data['collection'] );
        }
    }

    //sek_error_log( __FUNCTION__ . ' THEERE !');

    return 'no_match' === $seks_data ? Nimble_Manager()->default_location_model : $seks_data;
}

// Return and cache the local or group skope seks data
// Without inheritance because not filtered with the group site template content
function sek_get_seks_without_group_inheritance( $skope_id ) {
    if ( empty($skope_id) || !is_string($skope_id) ) {
        sek_error_log( 'Error missing skope id');
        return [];
    }

    $cache_key = 'nimble_seks_data_for_skope_' . $skope_id;
    $cached = wp_cache_get( $cache_key );
    if ( is_array($cached) ) {
        return $cached;
    }

    $is_global_skope = NIMBLE_GLOBAL_SKOPE_ID === $skope_id;
    $seks_data = array();
    $post = sek_get_seks_post( $skope_id, $is_global_skope ? 'global' : 'local' );//Cached

    if ( $post ) {
        $seks_data = maybe_unserialize( $post->post_content );
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        if ( !$is_global_skope && !array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $seks_data ) ) {
            // Retro-compat => make sure we set property '__inherits_group_skope_tmpl_when_exists__' to false if it's not set yet, because NB bases group inheritance on it
            $seks_data['__inherits_group_skope_tmpl_when_exists__'] = false;
        }
    }

    // normalizes
    // [ 'collection' => [], 'local_options' => [], "fonts": [], '__inherits_group_skope_tmpl_when_exists__' => true ];
    $default_collection = sek_get_default_location_model( $skope_id );
    $seks_data = wp_parse_args( $seks_data, $default_collection );
    wp_cache_set( $cache_key, $seks_data );
    return $seks_data;
}




// make sure the locations in the skoped locations tree match the registered locations for the context
function sek_maybe_add_incomplete_locations( $seks_data, $is_global_skope ) {
    // Maybe add missing registered locations
    $maybe_incomplete_locations = [];
    foreach( $seks_data['collection'] as $location_data ) {
        if ( !empty( $location_data['id'] ) ) {
            $maybe_incomplete_locations[] = $location_data['id'];
        }
    }

    foreach( sek_get_locations() as $loc_id => $params ) {
        if ( !in_array( $loc_id, $maybe_incomplete_locations ) ) {
            if ( ( sek_is_global_location( $loc_id ) && $is_global_skope ) || ( !sek_is_global_location( $loc_id ) && !$is_global_skope  ) ) {
                $seks_data['collection'][] = wp_parse_args( [ 'id' => $loc_id ], Nimble_Manager()->default_location_model );
            }
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

    if ( !is_array( $seks_data ) ) {
        error_log( 'sek_update_sek_post => $seks_data is not an array' );
        return new \WP_Error( 'sek_update_sek_post => $seks_data is not an array');
    }

    $skope_id = $args['skope_id'];
    if ( empty( $skope_id ) ) {
        error_log( 'sek_update_sek_post => empty skope_id' );
        return new \WP_Error( 'sek_update_sek_post => empty skope_id');
    }

    $post_title = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;

    //sek_error_log('IN UPDATE SEK_POST ?', $seks_data );


    $post_data = array(
        'post_title' => $post_title,
        'post_name' => sanitize_title( $post_title ),
        'post_type' => NIMBLE_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $seks_data )
    );

    // Update post if it already exists, otherwise create a new one.
    $post = sek_get_seks_post( $skope_id, NIMBLE_GLOBAL_SKOPE_ID !== $skope_id ? 'local' : 'global' );

    if ( $post ) {
        $post_data['ID'] = $post->ID;
        $r = wp_update_post( wp_slash( $post_data ), true );
    } else {
        //sek_error_log('IINSERT NEW POST ', $post_title );
        $r = wp_insert_post( wp_slash( $post_data ), true );
        if ( !is_wp_error( $r ) ) {
            $post_id = $r;//$r is the post ID

            sek_set_nb_post_id_in_index( $skope_id, (int)$post_id ); 

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

// Introduced March 2021 for #478
// Removes the post id in the skope index + removes the post in DB + remove the stylesheet
function sek_remove_seks_post( $skope_id = null ) {
    if ( is_null( $skope_id ) || empty( $skope_id ) ) {
        sek_error_log( __FUNCTION__  . ' => error => empty skope_id' );
        return new \WP_Error( 'sek_update_sek_post => empty skope_id');
    }

    //$post_title = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id;
    $tmpl_post_to_remove = sek_get_seks_post( $skope_id );

    //sek_error_log( __FUNCTION__ . ' => so $tmpl_post_to_remove => ' . $skope_id, $tmpl_post_to_remove );
    // Remove the associated post id in the skope index
    sek_remove_nb_post_id_in_index( $skope_id );

    // Remove the post in DB
    if ( $tmpl_post_to_remove && is_object( $tmpl_post_to_remove ) ) {
        // the CPT is permanently deleted instead of moved to Trash when using wp_trash_post()
        $r = wp_delete_post( $tmpl_post_to_remove->ID );
        if ( is_wp_error( $r ) ) {
            sek_error_log( __FUNCTION__ . '=> _removal_error', $r );
        }
    } else {
        // TMPL POST NOT FOUND
        //sek_error_log( __FUNCTION__ . '=> _tmpl_post_not_found' );
    }

    // Remove the corresponding stylesheet
    $css_handler_instance = new Sek_Dyn_CSS_Handler( array(
        'id'             => $skope_id,
        'skope_id'       => $skope_id,
        'mode'           => 'delete'
    ));
    $css_handler_instance->sek_dyn_css_delete_file();
}

?>