<?php
// /* ------------------------------------------------------------------------- *
// *  FEEDBACK NOTIF
// /* ------------------------------------------------------------------------- */
// Invoked when generating the customizer localized js params 'sektionsLocalizedData'
function sek_get_feedback_notif_status() {
    if ( sek_feedback_notice_is_dismissed() )
      return;
    if ( sek_feedback_notice_is_postponed() )
      return;

    // Did we set the status already ?
    if ( 'not_set' !== Nimble_Manager()->feedback_notif_status )
      return Nimble_Manager()->feedback_notif_status;

    // If not let's set it

    $start_version = get_option( 'nimble_started_with_version', NIMBLE_VERSION );
    //sek_error_log('START VERSION ?' . $start_version, version_compare( $start_version, '1.6.0', '<=' ) );

    // Bail if user did not start before v1.10.10, February 15th 2020 ( set on March 3rd 2020 )
    if ( !version_compare( $start_version, '1.10.10', '<=' ) )
      return;

    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( !is_array( $query->posts ) || empty( $query->posts ) )
      return;

    $customized_pages = 0;
    $nb_section_created = 0;
    // the global var is easier to handle for array when populated recursively
    global $modules_used;
    $module_used = array();

    foreach ( $query->posts as $post_object ) {
        $seks_data = maybe_unserialize($post_object->post_content);
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        $nb_section_created += sek_count_not_empty_sections_in_page( $seks_data );
        sek_populate_list_of_modules_used( $seks_data );
        $customized_pages++;
    }

    if ( !is_array( $modules_used ) || !is_numeric( $nb_section_created ) || !is_numeric($customized_pages) )
      return;

    $modules_used = array_unique($modules_used);

    // sek_error_log('$section_created ??', $nb_section_created );
    // sek_error_log('$modules_used ?? ' . count($modules_used), $modules_used );
    // sek_error_log('$customized_pages ??', $customized_pages );
    //version_compare( $this->wp_version, '4.1', '>=' )
    Nimble_Manager()->feedback_notif_status = $customized_pages > 0 && $nb_section_created > 2 && count($modules_used) > 2;
    return Nimble_Manager()->feedback_notif_status;
}


// recursive helper to generate a list of module used in a given set of sections data
function sek_populate_list_of_modules_used( $seks_data ) {
    global $modules_used;
    if ( !is_array( $seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid seks_data param');
        return $count;
    }
    foreach ( $seks_data as $key => $data ) {
        if ( is_array( $data ) ) {
            if ( !empty( $data['level'] ) && 'module' === $data['level'] && !empty( $data['module_type'] ) ) {
                $modules_used[] = $data['module_type'];
            } else {
                //$modules_used = array_merge( $modules_used, sek_populate_list_of_modules_used( $data, $modules_used ) );
                sek_populate_list_of_modules_used( $data, $modules_used );
            }
        }
    }
}


function sek_feedback_notice_is_dismissed() {
    $dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
    $dismissed_array = array_filter( explode( ',', (string) $dismissed ) );
    return in_array( NIMBLE_FEEDBACK_NOTICE_ID, $dismissed_array );
}

// @uses get_user_meta( get_current_user_id(), 'nimble_user_transients', true );
// populated in ajax class
function sek_feedback_notice_is_postponed() {
    return 'maybe_later' === get_transient( NIMBLE_FEEDBACK_NOTICE_ID );
}

?>