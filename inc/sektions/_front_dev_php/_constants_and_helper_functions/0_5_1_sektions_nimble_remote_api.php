<?php
// /* ------------------------------------------------------------------------- *
// *  NIMBLE API
// /* ------------------------------------------------------------------------- */
// if ( !defined( "NIMBLE_SECTIONS_LIBRARY_OPT_NAME" ) ) { define( "NIMBLE_SECTIONS_LIBRARY_OPT_NAME", 'nimble_api_prebuilt_sections_data' ); } <= DEPRECATED, Now uses local json

// Nimble api returns a set of value structured as follow
// return array(
//     'timestamp' => time(),
//     'upgrade_notice' => array(),
//     'library' => array(
//         'sections' => array(
//             'registration_params' => sek_get_sections_registration_params(),
//             'json_collection' => sek_get_json_collection()
//         ),
//         'templates' => array()
//     ),
//     'latest_posts' => $post_data,
//     'cta' => array( 'started_before' => $go_pro_if_started_before, 'html' => $go_pro_html )
//     // 'testtest' => $_GET,
//     // 'testreferer' => $_SERVER => to get the
// );
// @return array|false Info data, or false.
// api data is refreshed on plugin update and theme switch
// @$what param can be 'latest_posts_and_start_msg', 'templates'
function sek_get_nimble_api_data( $what = null, $force_update = false ) {
    if ( is_null( $what ) || !is_string($what) ) {
        sek_error_log( __FUNCTION__ . ' => error => $what param not set');
        return false;
    }

    $cached_api_data = wp_cache_get( 'nimble_api_data_'.$what );

    if ( $cached_api_data && is_array($cached_api_data) && !empty($cached_api_data) ) {
        return $cached_api_data;
    }

    $transient_name = '';
    $transient_duration = 24 * HOUR_IN_SECONDS;

    switch ( $what ) {
        case 'latest_posts_and_start_msg':
            $transient_name = 'nimble_api_posts';
            $transient_duration = 48 * HOUR_IN_SECONDS;
        break;
        case 'templates':
            $transient_name = 'nimble_api_templates';
        break;
        default:
            sek_error_log( __FUNCTION__ . ' => error => invalid $what param => ' . $what );
        break;
    }

    if ( empty( $transient_name ) ) {
        return false;
    }

    $theme_slug = sek_get_parent_theme_slug();
    $version_transient_value = get_transient( 'nimble_version_check_for_api');
    $expected_version_transient_value = NIMBLE_VERSION . '_' . $theme_slug;
    $api_needs_update = $version_transient_value != $expected_version_transient_value;

    $api_data = maybe_unserialize( get_transient( $transient_name ) );

    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;
    if ( true === $force_update ) {
          sek_error_log( __FUNCTION__ . ' API is in force update mode. API data requested => ' . $transient_name );
    }


    // Connect to remote NB api when :
    // 1) api data transient is not set or has expired ( false === $api_data )
    // 2) force_update param is true
    // 3) NB has been updated to a new version ( $api_needs_update case )
    // 4) Theme has been changed ( $api_needs_update case )
    if ( $force_update || false === $api_data || $api_needs_update ) {

        $query_params = [
            'timeout' => ( $force_update ) ? 25 : 8,
            'body' => [
                'api_version' => NIMBLE_VERSION,
                'site_lang' => get_bloginfo( 'language' ),
                'what' => $what//<= latest posts about Nimble Builder or templates
            ]
        ];

        //sek_error_log('CALL TO REMOTE API NOW FOR DATA => ' . $transient_name . ' | ' . $force_update . ' | ' . $api_needs_update, $query_params );

        $response = wp_remote_get( NIMBLE_DATA_API_URL_V2, $query_params );
        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            // set the transient to '_api_error_', so that we don't hammer the api if not reachable. next call will be done after transient expiration
            $api_data = '_api_error_';
            sek_error_log( __FUNCTION__ . ' invalid api response');
        }

        $api_data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $api_data ) || !is_array( $api_data ) ) {
            // set the transient to '_api_error_', so that we don't hammer the api if not reachable. next call will be done after transient expiration
            $api_data = '_api_error_';
            sek_error_log( __FUNCTION__ . ' invalid api data after json decode');
        }

        //sek_error_log('API DATA ?', $api_data );
        set_transient( $transient_name, $api_data, $transient_duration );
        // The api data will be refreshed on next plugin update, or next theme switch. Or if $transient_name has expired.
        // $expected_version_transient_value = NIMBLE_VERSION . '_' . $theme_slug;
        set_transient( 'nimble_version_check_for_api', $expected_version_transient_value, 100 * DAY_IN_SECONDS );
    }//if ( $force_update || false === $api_data )
    
    // if api_error a new api call will be done when the relevant transient will expire
    if ( '_api_error_' === $api_data ) {
        sek_error_log( __FUNCTION__ . ' API data value is _api_error_ for transient data : ' . $transient_name );
    }

    $api_data = '_api_error_' === $api_data ? null : $api_data;
    wp_cache_set( 'nimble_api_data_'. $what , $api_data );

    //sek_error_log('API DATA for ' . $transient_name, $api_data );

    return $api_data;
}


//////////////////////////////////////////////////
/// TEMPLATE DATA
function sek_get_tmpl_api_data( $force_update = false ) {
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;

    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update( upgrader_process_complete ), theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $api_data = sek_get_nimble_api_data( 'templates', $force_update );
    $api_data = is_array( $api_data ) ? $api_data : [];

    //sek_error_log('TMPL DATA ?', $tmpl_data);
    if ( empty($api_data) || !array_key_exists('lib', $api_data) || !is_array($api_data['lib']) || empty($api_data['lib']['templates']) || !is_array($api_data['lib']['templates']) ) {
        sek_error_log( __FUNCTION__ . ' => error => no json_collection' );
        return array();
    }
   
    //return [];
    return maybe_unserialize( $api_data['lib']['templates'] );
}


//////////////////////////////////////////////////
/// LATESTS POSTS
// @return array of posts
function sek_get_latest_posts_api_data( $force_update = false ) {
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;
    $api_data = sek_get_nimble_api_data( 'latest_posts_and_start_msg', $force_update );
    if ( !is_array( $api_data['latest_posts'] ) || empty( $api_data['latest_posts'] ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no latest_posts' );
        return [];
    }
    return $api_data['latest_posts'];
}

// @return html string
function sek_start_msg_from_api( $theme_name, $force_update = false ) {
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;

    $api_data = sek_get_nimble_api_data( 'latest_posts_and_start_msg', $force_update );
    if ( !sek_is_presscustomizr_theme( $theme_name ) || !is_array( $api_data ) ) {
        return '';
    }
    $msg = '';
    $api_data = is_array($api_data) ? $api_data : [];
    $api_msg = isset( $api_data['start_msg'] ) ? $api_data['start_msg'] : null;

    if ( !is_null($api_msg) && is_string($api_msg) ) {
        $msg = $api_msg;
    }
    return $msg;
}

// Refresh the api data on plugin update and theme switch
// add_action( 'after_switch_theme', '\Nimble\sek_refresh_nimble_api_data');
// add_action( 'upgrader_process_complete', '\Nimble\sek_refresh_nimble_api_data');
// function sek_refresh_nimble_api_data() {
//     // Refresh data on theme switch
//     // => so the posts and message are up to date
//     sek_get_nimble_api_data( 'all_data', $force_update = true );
// }


//////////////////////////////////////////////////
/// SECTIONS DATA
/// DEPRECATED, NOW USING LOCAL DATA AND JSON
// function sek_get_sections_registration_params_api_data( $force_update = false ) {
//     // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
//     // Let's use the data saved as options
//     // Those data are updated on plugin install, plugin update, theme switch
//     // @see https://github.com/presscustomizr/nimble-builder/issues/441
//     $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
//     if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['registration_params'] ) ) {
//         sek_get_nimble_api_data( true );//<= true for "force_update"
//         $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
//     }

//     if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['registration_params'] ) ) {
//         sek_error_log( __FUNCTION__ . ' => error => no section registration params' );
//         return array();
//     }
//     return $sections_data['registration_params'];
// }

// function sek_get_preset_sections_api_data( $force_update = false ) {
//     // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
//     // Let's use the data saved as options
//     // Those data are updated on plugin install, plugin update( upgrader_process_complete ), theme switch
//     // @see https://github.com/presscustomizr/nimble-builder/issues/441
//     $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
//     if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['json_collection'] ) ) {
//         sek_get_nimble_api_data( true );//<= true for "force_update"
//         $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
//     }

//     if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['json_collection'] ) ) {
//         sek_error_log( __FUNCTION__ . ' => error => no json_collection' );
//         return array();
//     }
//     return $sections_data['json_collection'];
// }



?>