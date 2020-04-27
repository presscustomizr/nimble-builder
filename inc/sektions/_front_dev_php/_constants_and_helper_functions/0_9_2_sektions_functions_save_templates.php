<?php
/* ------------------------------------------------------------------------- *
 *  SAVED TEMPLATES
/* ------------------------------------------------------------------------- */
// SAVED TEMPLATES POST TYPE
// CPT for template : 'nimble_template'
register_post_type( NIMBLE_TEMPLATE_CPT , array(
    'labels' => array(
      'name'          => __( 'Nimble templates', 'text_doma' ),
      'singular_name' => __( 'Nimble templates', 'text_doma' ),
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
 * Fetch the 'nimble_template' post for a given post_name
 *
 * @return WP_Post|null
 */
function sek_get_saved_tmpl_post( $tmpl_post_name ) {
    // $sek_post_query_vars = array(
    //     'post_type'              => NIMBLE_CPT,
    //     'post_status'            => get_post_stati(),
    //     'name'                   => sanitize_title( $tmpl_post_name ),
    //     'posts_per_page'         => 1,
    //     'no_found_rows'          => true,
    //     'cache_results'          => true,
    //     'update_post_meta_cache' => false,
    //     'update_post_term_cache' => false,
    //     'lazy_load_term_meta'    => false,
    // );

    $post = null;
    $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
    $tmpl_data = array_key_exists( $saved_tmpl_id, $all_saved_seks ) ? $all_saved_seks[$saved_tmpl_id] : array();
    $post_id = array_key_exists( 'post_id', $tmpl_data ) ? $tmpl_data['post_id'] : -1;

    // if the options has not been set yet, it will return (int) 0
    if ( 0 > $post_id ) {
        //error_log( 'sek_get_seks_post => post_id is not valid for options => ' . $saved_tmpl_id );
        return;
    }

    if ( !is_int( $post_id ) ) {
        error_log( __FUNCTION__ .' => post_id !is_int() for options => ' . $saved_tmpl_id );
    }

    if ( is_int( $post_id ) && $post_id > 0 && get_post( $post_id ) ) {
        $post = get_post( $post_id );
    }

    // // `-1` indicates no post exists; no query necessary.
    // if ( !$post && -1 !== $post_id ) {
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



// @return the saved template data collection
function sek_get_saved_template_data( $tmpl_post_name ) {
    $sek_post = sek_get_saved_template_post( $tmpl_post_name );
    $tmpl_data = array();
    if ( $sek_post ) {
        $tmpl_data_decoded = maybe_unserialize( $sek_post->post_content );
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
        if ( is_array( $tmpl_data_decoded ) && !empty( $tmpl_data_decoded['data'] ) && is_string( $tmpl_data_decoded['data'] ) ) {
            $tmpl_data = json_decode( wp_unslash( $tmpl_data_decoded['data'], true ) );
        }
    }
    return $tmpl_data;
}


// invoked on 'wp_ajax_sek_get_user_saved_templates'
// @return an unserialized array of all templates saved by user
function sek_get_all_saved_templates() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_TEMPLATE_CPT,
        'post_status'            => 'publish',
        //'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,

        'orderby' => 'modified',
        'order' => 'DESC'
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( !is_array( $query->posts ) || empty( $query->posts ) )
      return;

    $collection = array();

    sek_error_log('QUERY ??', $query );

    foreach ( $query->posts as $post_object ) {
        $content = maybe_unserialize( $post_object->post_content );
        if ( !is_array($content) ) {
            continue;
        }
        // When updating a template, we only need to return title and description
        $collection[$post_object->post_name] = array(
            'title' => !empty($content['title']) ? $content['title'] : '',
            'description' => !empty($content['description']) ? $content['description'] : ''
        );
    }

    return $collection;
}



 // Update the 'nimble_template' post
 // Inserts a 'nimble_template' post when one doesn't yet exist.
 // $tmpl_data = array(
  //     'title' => $_POST['sek_tmpl_title'],
  //     'description' => $_POST['sek_tmpl_description'],
  //     'data' => $_POST['sek_tmpl_data']//<= json stringified
  // );
// @return WP_Post|WP_Error Post on success, error on failure.
function sek_update_saved_tmpl_post( $tmpl_data, $post_name_to_update = '' ) {
    if ( !is_array( $tmpl_data ) ) {
        sek_error_log( __FUNCTION__ . ' => $tmpl_data is not an array' );
        return new \WP_Error( __FUNCTION__ . ' => $tmpl_data is not an array');
    }

    $tmpl_data = wp_parse_args( $tmpl_data, array(
        'title' => '',
        'description' => '',
        'data' => array(),
        'nimble_version' => NIMBLE_VERSION
    ));

    // $post_name_to_update will be used when user updates an existing template
    if ( !empty($post_name_to_update) ) {
        $tmpl_post_name = $post_name_to_update;
    } else {
        $tmpl_post_name = NIMBLE_PREFIX_FOR_SAVED_TMPL .  sanitize_title( $tmpl_data['title'] );
    }

    $post_data = array(
        'post_title' => esc_attr( $tmpl_data['title'] ),
        'post_name' => $tmpl_post_name,
        'post_type' => NIMBLE_TEMPLATE_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $tmpl_data )
    );

    // Update post if it already exists, otherwise create a new one.
    $post = sek_get_saved_tmpl_post( $tmpl_post_name );

    sek_error_log( __FUNCTION__ . ' => so $tmpl_data for skope ' . $tmpl_post_name, $tmpl_data );

    if ( $post ) {
        $post_data['ID'] = $post->ID;
        $r = wp_update_post( wp_slash( $post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $post_data ), true );
        if ( !is_wp_error( $r ) ) {
            $post_id = $r;//$r is the post ID
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