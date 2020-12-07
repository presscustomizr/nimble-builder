<?php
/* ------------------------------------------------------------------------- *
 *  SAVED SECTIONS
/* ------------------------------------------------------------------------- */
// SAVED SECTION POST TYPE
// CPT for section : 'nimble_section'
register_post_type( NIMBLE_SECTION_CPT , array(
    'labels' => array(
      'name'          => sek_is_cpt_debug_mode() ? __( '[NB debug] user prebuilt sections') : __( 'NB user prebuilt sections'),
      'singular_name' => __( 'NB User prebuilt sections')
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



/**
 * Fetch the 'nimble_section' post for a given post_name
 *
 * @return WP_Post|null
 */
function sek_get_saved_section_post( $section_post_name ) {
    $cache_group = 'nimble_section_post';
    $section_post = wp_cache_get( $section_post_name, $cache_group );
    // is it cached already ?
    if ( $section_post && is_object($section_post) && NIMBLE_SECTION_CPT === get_post_type( $section_post->id ) ) {
      return $section_post;
    }

    $section_post_query = new \WP_Query(
      array(
        'post_type'              => NIMBLE_SECTION_CPT,
        'post_status'            => get_post_stati(),
        'name'                   => sanitize_title( $section_post_name ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
      )
    );
    if ( !empty( $section_post_query->posts ) ) {
        $section_post = $section_post_query->posts[0];
        wp_cache_set( $section_post_name, $section_post, $cache_group );
        return $section_post;
    }

    return null;
}


// // @return the saved section data collection
// // NOT USED
// function sek_get_saved_section_data( $section_post_name ) {
//     $sek_post = sek_get_saved_section_post( $section_post_name );
//     $section_data = array();
//     if ( $sek_post ) {
//         $section_data_decoded = maybe_unserialize( $sek_post->post_content );
//         // The section data are described as an array
//         // array(
//         //     'title' => '',
//         //     'description' => '',
//         //     'id' => '',
//         //     'type' => 'content',//in the future will be used to differentiate header, content and footer sections
//         //     'creation_date' => date("Y-m-d H:i:s"),
//         //     'update_date' => '',
//         //     'data' => array(),<= this is where we describe the columns and options
//         //     'nimble_version' => NIMBLE_VERSION
//         // )
//         if ( is_array( $section_data_decoded ) && !empty( $section_data_decoded['data'] ) && is_string( $section_data_decoded['data'] ) ) {
//             $section_data = json_decode( wp_unslash( $section_data_decoded['data'], true ) );
//         }
//     }
//     return $section_data;
// }


// invoked on 'wp_ajax_sek_get_user_saved_sections'
// @return an unserialized array of all sections saved by user
function sek_get_all_saved_sections() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_SECTION_CPT,
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
    $collection = array();
    if ( is_wp_error( $query ) ) {
        wp_send_json_error(  __FUNCTION__ . '_error_when_querying_nimble_sections' );
    }
    if ( !is_array( $query->posts ) || empty( $query->posts ) ) {
      return $collection;
    }

    foreach ( $query->posts as $post_object ) {
        $content = maybe_unserialize( $post_object->post_content );
        // Structure of $content :
        // array(
        //     'data' => $_POST['section_data'],//<= json stringified
        //     'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? $_POST['section_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['section_title'],
        //         'description' => $_POST['section_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'active_locations' => is_string( $_POST['active_locations'] ) ? explode( ',', $_POST['active_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( !is_array($content) ) {
            sek_error_log(__FUNCTION__ . ' error in content structure for section post name : ' . $post_object->post_name );
            continue;
        }
        if ( empty($content['metas']) ) {
            sek_error_log(__FUNCTION__ . ' error => missing metas for section post name : ' . $post_object->post_name );
            continue;
        }

        // When updating a section, we only need to return title and description
        $collection[$post_object->post_name] = array(
            'title' => !empty($content['metas']['title']) ? $content['metas']['title'] : '',
            'description' => !empty($content['metas']['description']) ? $content['metas']['description'] : '',
            'last_modified_date' => mysql2date( 'Y-m-d H:i:s', $post_object->post_modified )
        );
    }

    return $collection;
}



 // Update the 'nimble_section' post
 // Inserts a 'nimble_section' post when one doesn't yet exist.
 // $section_data = array(
  //     'data' => $_POST['section_data'],//<= json stringified
  //     'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? $_POST['section_post_name'] : null,
  //     'metas' => array(
  //         'title' => $_POST['section_title'],
  //         'description' => $_POST['section_description'],
  //         'skope_id' => $_POST['skope_id'],
  //         'version' => NIMBLE_VERSION,
  //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
  //         'active_locations' => is_string( $_POST['active_locations'] ) ? explode( ',', $_POST['active_locations'] ) : array(),
  //         'date' => date("Y-m-d"),
  //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
  //     )
  // );
// @return WP_Post|WP_Error Post on success, error on failure.
function sek_update_saved_section_post( $section_data ) {
    if ( !is_array( $section_data ) ) {
        sek_error_log( __FUNCTION__ . ' => $section_data is not an array' );
        return new \WP_Error( __FUNCTION__ . ' => $section_data is not an array');
    }

    if ( !isset( $section_data['data']) || !isset( $section_data['metas']) ) {
        sek_error_log( __FUNCTION__ . ' => invalid $section_data' );
        return new \WP_Error( __FUNCTION__ . ' => invalid $section_data');
    }

    $section_data = wp_parse_args( $section_data, array(
        'data' => array(),
        'section_post_name' => null,
        'metas' => array(
            'title' => '',
            'description' => '',
            'skope_id' => '',
            'version' => NIMBLE_VERSION,
            //'active_locations' => array(),
            'date' => '',
            'theme' => ''
        )
    ));

    // the section post name is provided only when updating
    $is_update_case = !is_null($section_data['section_post_name']);

    // $post_name_to_update will be used when user updates an existing section
    if ( !is_null($section_data['section_post_name']) ) {
        $section_post_name = $section_data['section_post_name'];
    } else {
        $section_post_name = NIMBLE_PREFIX_FOR_SAVED_SECTION .  sanitize_title( $section_data['metas']['title'] );//nb_section_my-section-name
    }

    // Update the post name now
    $section_data['section_post_name'] = $section_post_name;

    $post_data = array(
        'post_title' => esc_attr( $section_data['metas']['title'] ),
        'post_name' => $section_post_name,
        'post_type' => NIMBLE_SECTION_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $section_data )
    );

    // Update post if it already exists, otherwise create a new one.
    $section_post = null;
    if ( $is_update_case ) {
        $section_post = sek_get_saved_section_post( $section_post_name );
    }

    if ( $section_post && is_object($section_post) ) {
        $post_data['ID'] = $section_post->ID;
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