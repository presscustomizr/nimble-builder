<?php
// /* ------------------------------------------------------------------------- *
// *  FEEDBACK NOTIF
// /* ------------------------------------------------------------------------- */
// Invoked when printing the review note in the plugin table, in the 'plugin_row_meta'
// Since this is a quite heavy check, NB stores it in a 7 days long transient
function sek_get_feedback_notif_status() {
    if ( sek_feedback_notice_is_dismissed() )
      return;

    // Check if we already stored the status in a transient first
    $transient_name = NIMBLE_FEEDBACK_STATUS_TRANSIENT_ID;
    $transient_value = get_transient( $transient_name );
    if ( false != $transient_value ) {
        return $transient_value;
    }

    // If transient not set or expired, let's set it and return the feedback status
    // $start_version = get_option( 'nimble_started_with_version', NIMBLE_VERSION );

    // Bail if user started after v2.1.20, October 22nd 2020 ( set on November 23th 2020 )
    // if ( !version_compare( $start_version, '3.1.12', '<=' ) )
    //   return;

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

    $transient_value = 'not_eligible';
    // sek_error_log('$section_created ??', $nb_section_created );
    // sek_error_log('$modules_used ?? ' . count($modules_used), $modules_used );
    // sek_error_log('$customized_pages ??', $customized_pages );
    //version_compare( $this->wp_version, '4.1', '>=' )
    if ( $customized_pages > 1 && $nb_section_created > 1 && count($modules_used) > 1 ) {
        $transient_value = 'eligible';
    }
    set_transient( $transient_name, $transient_value, 7 * DAY_IN_SECONDS );
    return $transient_value;
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

?>