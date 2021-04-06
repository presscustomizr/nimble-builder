<?php
// return bool
// count the number of global section created, no matter if they are header footer or other global locations
// can be used to determine if we need to render Nimble Builder assets on front. See ::sek_enqueue_front_assets()
function sek_has_global_sections() {
    if ( skp_is_customizing() )
      return true;
    if ( 'not_set' !== Nimble_Manager()->page_has_global_sections )
        return Nimble_Manager()->page_has_global_sections;

    $maybe_global_sek_post = sek_get_seks_post( NIMBLE_GLOBAL_SKOPE_ID, 'global' );
    $nb_section_created = 0;
    if ( is_object($maybe_global_sek_post) ) {
        $seks_data = maybe_unserialize($maybe_global_sek_post->post_content);
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        $nb_section_created = sek_count_not_empty_sections_in_page( $seks_data );
    }
    // cache now
    Nimble_Manager()->page_has_global_sections = $nb_section_created > 0;
    return Nimble_Manager()->page_has_global_sections;
}


// @return bool
// added for https://github.com/presscustomizr/nimble-builder/issues/436
// initially used to determine if a post or a page has been customized with Nimble Builder => if so, we add an edit link in the post/page list
// when used in admin, the skope_id must be provided
// can be used to determine if we need to render Nimble Builder assets on front. See ::sek_enqueue_front_assets()
// March 2021 => fixed : function sek_local_skope_has_nimble_sections() => when customzing => seks_data param should be provided after being filtered with customized values
function sek_local_skope_has_nimble_sections( $skope_id = '', $seks_data = null ) {
    $skope_id = empty( $skope_id ) ? skp_get_skope_id() : $skope_id;

    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return false;
    }

    if ( 'not_set' !== Nimble_Manager()->page_has_local_sections )
        return Nimble_Manager()->page_has_local_sections;

    $nb_section_created = 0;
    
    // When the collection is provided use it otherwise get it
    if ( is_null($seks_data) || !is_array($seks_data) ) {
        $seks_data = sek_get_skoped_seks( $skope_id );
    }
    if ( is_array( $seks_data ) ) {
        $nb_section_created = sek_count_not_empty_sections_in_page( $seks_data );
    }
    // cache now
    Nimble_Manager()->page_has_local_sections = $nb_section_created > 0;
    return Nimble_Manager()->page_has_local_sections;
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
 *  HAS USER STARTED CREATING SECTIONS ?
/* ------------------------------------------------------------------------- */
// @return a boolean
// Used to check if we should render the welcome notice in sek_render_welcome_notice()
function sek_site_has_nimble_sections_created() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title(),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    //sek_error_log('DO WE HAVE SECTIONS ?', $query );
    return is_array( $query->posts ) && !empty( $query->posts );
}




// recursive helper to count the number of sections in a given set of sections data
function sek_count_not_empty_sections_in_page( $seks_data, $count = 0 ) {
    if ( !is_array( $seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid seks_data param');
        return $count;
    }
    foreach ( $seks_data as $key => $data ) {
        if ( is_array( $data ) ) {
            if ( !empty( $data['level'] ) && 'section' === $data['level'] ) {
                if ( !empty( $data['collection'] ) ) {
                    $count++;
                }
            } else {
                $count = sek_count_not_empty_sections_in_page( $data, $count );
            }
        }
    }
    return $count;
}

?>