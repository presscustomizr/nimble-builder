<?php
/* ------------------------------------------------------------------------- *
 *  SAVED TEMPLATES
/* ------------------------------------------------------------------------- */
// SAVED TEMPLATES POST TYPE
// CPT for template : 'nimble_template'
register_post_type( NIMBLE_TEMPLATE_CPT , array(
    'labels' => array(
      'name'          => sek_is_cpt_debug_mode() ? __( '[NB debug] user templates') : __( 'NB user templates'),
      'singular_name' => __( 'NB user templates')
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
 * Fetch the 'nimble_template' post for a given post_name
 *
 * @return WP_Post|null
 */
function sek_get_saved_tmpl_post( $tmpl_post_name ) {
    $cache_group = 'nimble_template_post';
    $template_post = wp_cache_get( $tmpl_post_name, $cache_group );
    // is it cached already ?
    if ( $template_post && is_object($template_post) && NIMBLE_TEMPLATE_CPT === get_post_type( $template_post->id ) ) {
      return $template_post;
    }

    $tmpl_post_query = new \WP_Query(
      array(
        'post_type'              => NIMBLE_TEMPLATE_CPT,
        'post_status'            => get_post_stati(),
        'name'                   => sanitize_title( $tmpl_post_name ),
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
      )
    );
    if ( !empty( $tmpl_post_query->posts ) ) {
        $template_post = $tmpl_post_query->posts[0];
        wp_cache_set( $tmpl_post_name, $template_post, $cache_group );
        return $template_post;
    }

    return null;
}



// // @return the saved template data collection
// // NOT USED
// function sek_get_saved_template_data( $tmpl_post_name ) {
//     $sek_post = sek_get_saved_template_post( $tmpl_post_name );
//     $tmpl_data = array();
//     if ( $sek_post ) {
//         $tmpl_data_decoded = maybe_unserialize( $sek_post->post_content );
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
//         if ( is_array( $tmpl_data_decoded ) && !empty( $tmpl_data_decoded['data'] ) && is_string( $tmpl_data_decoded['data'] ) ) {
//             $tmpl_data = json_decode( wp_unslash( $tmpl_data_decoded['data'], true ) );
//         }
//     }
//     return $tmpl_data;
// }


// invoked on 'wp_ajax_sek_get_user_saved_templates'
// @return an unserialized array of all templates saved by user
function sek_get_all_saved_templates() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_TEMPLATE_CPT,
        'post_status'            => 'publish',
        //'name'                   => sanitize_title(),
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
        wp_send_json_error(  __FUNCTION__ . '_error_when_querying_nimble_templates' );
    }
    if ( !is_array( $query->posts ) || empty( $query->posts ) ) {
      return $collection;
    }

    foreach ( $query->posts as $post_object ) {
        $content = maybe_unserialize( $post_object->post_content );
        // sek_error_log( __FUNCTION__ . ' TYPE ?', gettype($post_object->post_content ) );
        // sek_error_log( __FUNCTION__ . ' POST OBJECT ?', $post_object->post_content );
        // Structure of $content :
        // array(
        //     'data' => $_POST['tmpl_data'],//<= json stringified
        //     'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['tmpl_title'],
        //         'description' => $_POST['tmpl_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'tmpl_locations' => is_string( $_POST['tmpl_locations'] ) ? explode( ',', $_POST['tmpl_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( !is_array($content) ) {
            sek_error_log(__FUNCTION__ . ' error in content structure for template post name : ' . $post_object->post_name );
            continue;
        }
        if ( empty($content['metas']) ) {
            sek_error_log(__FUNCTION__ . ' error => missing metas for template post name : ' . $post_object->post_name );
            continue;
        }

        // When updating a template, we only need to return title and description
        $collection[$post_object->post_name] = array(
            'title' => !empty($content['metas']['title']) ? sek_maybe_decode_richtext( $content['metas']['title'] ) : '',
            'description' => !empty($content['metas']['description']) ? sek_maybe_decode_richtext( $content['metas']['description'] ) : '',
            'last_modified_date' => mysql2date( 'Y-m-d H:i:s', $post_object->post_modified )
        );
    }
    //sek_error_log('GET ALL SAVED TMPL', $collection );
    return $collection;
}


// invoked on 'wp_ajax_sek_get_all_api_tmpl'
// @return an unserialized array of api templates
function sek_get_all_api_templates() {
    $raw_tmpl = sek_get_all_tmpl_api_data();
    $collection = [];

    if( !is_array( $raw_tmpl) )
        return $collection;
        
    foreach ( $raw_tmpl as $tmpl_cpt_post_name => $metas) {
        if ( !is_array( $metas ) || empty($metas) )
            continue;

        $metas = wp_parse_args( $metas, [
            'title' => '',
            'description' => '',
            'date' => '',
            'thumb_url' => '',
            'is_pro_tmpl' => false,
            'demo_url' => false,
            'is_site_tmpl' => false
        ]);

        $collection[$tmpl_cpt_post_name] = [
            'title' => sek_maybe_decode_richtext( $metas['title'] ),
            'description' => sek_maybe_decode_richtext( $metas['description'] ),
            'last_modified_date' => mysql2date( 'Y-m-d', $metas['date'] ),
            'thumb_url' => !empty( $metas['thumb_url'] ) ? $metas['thumb_url'] : '',
            'is_pro_tmpl' => !empty( $metas['is_pro_tmpl'] ) ? $metas['is_pro_tmpl'] : false,
            'demo_url' => !empty( $metas['demo_url'] ) ? $metas['demo_url'] : false,
            'is_site_tmpl' => array_key_exists('is_site_tmpl', $metas ) && $metas['is_site_tmpl']
        ];
    }
    return $collection;
}


 // Update the 'nimble_template' post
 // Inserts a 'nimble_template' post when one doesn't yet exist.
 // $tmpl_data = array(
  //     'data' => $_POST['tmpl_data'],//<= json stringified
  //     'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
  //     'metas' => array(
  //         'title' => $_POST['tmpl_title'],
  //         'description' => $_POST['tmpl_description'],
  //         'skope_id' => $_POST['skope_id'],
  //         'version' => NIMBLE_VERSION,
  //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
  //         'tmpl_locations' => is_string( $_POST['tmpl_locations'] ) ? explode( ',', $_POST['tmpl_locations'] ) : array(),
  //         'date' => date("Y-m-d"),
  //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
  //     )
  // );
// @return WP_Post|WP_Error Post on success, error on failure.
function sek_update_user_tmpl_post( $tmpl_data, $is_edit_metas_only_case = false ) {
    if ( !is_array( $tmpl_data ) ) {
        sek_error_log( __FUNCTION__ . ' => $tmpl_data is not an array' );
        return new \WP_Error( __FUNCTION__ . ' => $tmpl_data is not an array');
    }

    if ( !isset( $tmpl_data['data']) || !isset( $tmpl_data['metas']) ) {
        sek_error_log( __FUNCTION__ . ' => invalid $tmpl_data' );
        return new \WP_Error( __FUNCTION__ . ' => invalid $tmpl_data');
    }

    $tmpl_data = wp_parse_args( $tmpl_data, array(
        'data' => array(),
        'tmpl_post_name' => null,
        'metas' => array(
            'title' => '',
            'description' => '',
            'skope_id' => '',
            'version' => NIMBLE_VERSION,
            'tmpl_locations' => array(),
            'tmpl_header_location' => '',
            'tmpl_footer_location' => '',
            'date' => '',
            'theme' => '',
            // for api templates
            'is_pro_tmpl' => false,
            'thumb_url' => ''
        )
    ));

    // the template post name is provided only when updating
    $is_update_case = !is_null($tmpl_data['tmpl_post_name']);

    // $post_name_to_update will be used when user updates an existing template
    if ( !is_null($tmpl_data['tmpl_post_name']) ) {
        $tmpl_post_name = $tmpl_data['tmpl_post_name'];
    } else {
        $tmpl_post_name = NIMBLE_PREFIX_FOR_SAVED_TMPL .  sanitize_title( $tmpl_data['metas']['title'] );//nb_tmpl_my-template-name
    }

    //sek_error_log('$tmpl_data??', $tmpl_data );

    // Update the post name now
    $tmpl_data['tmpl_post_name'] = $tmpl_post_name;

    //sek_error_log('serialized $tmpl_data??', maybe_serialize( $tmpl_data ) );
    // Update post if it already exists, otherwise create a new one.
    $current_tmpl_post = null;
    if ( $is_update_case ) {
        // When this is an update case, we fetch the existing tmpl_post in order to later get its id
        $current_tmpl_post = sek_get_saved_tmpl_post( $tmpl_post_name );

        // if this is an update case + editing metas only, then we use the current content
        if ( $is_edit_metas_only_case && isset($current_tmpl_post->post_content) ) {
            $current_tmpl_data = maybe_unserialize( $current_tmpl_post->post_content );
            if ( is_array($current_tmpl_data) && isset($current_tmpl_data['data']) && is_array($current_tmpl_data['data']) && !empty($current_tmpl_data['data']) ) {
                $tmpl_data['data'] = $current_tmpl_data['data'];
            }
        }
    }

    // March 2021 : make sure text input are sanitized like in #544 #792
    $tmpl_data = sek_sektion_collection_sanitize_cb( $tmpl_data );

    $new_or_updated_post_data = array(
        'post_title' => $tmpl_post_name,
        'post_name' => $tmpl_post_name,
        'post_type' => NIMBLE_TEMPLATE_CPT,
        'post_status' => 'publish',
        'post_content' => maybe_serialize( $tmpl_data )
    );

    // sek_error_log( __FUNCTION__ . ' => so $is_edit_metas_only_case ' . $is_edit_metas_only_case );
    // sek_error_log( __FUNCTION__ . ' => so $tmpl_data for skope ' . $tmpl_post_name, $current_tmpl_data['data'] );
    $r = '';

    if ( $current_tmpl_post && is_object($current_tmpl_post) ) {
        $new_or_updated_post_data['ID'] = $current_tmpl_post->ID;
        $r = wp_update_post( wp_slash( $new_or_updated_post_data ), true );
    } else {
        $r = wp_insert_post( wp_slash( $new_or_updated_post_data ), true );
        if ( !is_wp_error( $r ) ) {
            $post_id = $r;//$r is the post ID
            // Trigger creation of a revision. This should be removed once #30854 is resolved.
            if ( 0 === count( wp_get_post_revisions( $r ) ) ) {
                wp_save_post_revision( $r );
            }
        }
    }

    // Added March 2021 for stie templates #478
    do_action('nb_on_update_user_tmpl_post', $tmpl_post_name );

    if ( is_wp_error( $r ) ) {
        return $r;
    }
    return get_post( $r );
}

?>