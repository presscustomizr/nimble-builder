<?php
// /* ------------------------------------------------------------------------- *
// *  NIMBLE API
// /* ------------------------------------------------------------------------- */
if ( !defined( "NIMBLE_SECTIONS_LIBRARY_OPT_NAME" ) ) { define( "NIMBLE_SECTIONS_LIBRARY_OPT_NAME", 'nimble_api_prebuilt_sections_data' ); }
if ( !defined( "NIMBLE_NEWS_OPT_NAME" ) ) { define( "NIMBLE_NEWS_OPT_NAME", 'nimble_api_news_data' ); }
// NIMBLE_DATA_API_URL_V2 SINCE MAY 21ST 2019
// after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
// DOES NOT RETURN THE DATA FOR PRESET SECTIONS
// if ( !defined( "NIMBLE_DATA_API_URL" ) ) { define( "NIMBLE_DATA_API_URL", 'https://api.nimblebuilder.com/wp-json/nimble/v1/cravan' ); }
if ( !defined( "NIMBLE_DATA_API_URL_V2" ) ) { define( "NIMBLE_DATA_API_URL_V2", 'https://api.nimblebuilder.com/wp-json/nimble/v2/cravan' ); }

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
function sek_get_nimble_api_data( $force_update = false ) {
    $api_data_transient_name = 'nimble_api_data_' . NIMBLE_VERSION;
    $info_data = get_transient( $api_data_transient_name );
    $theme_slug = sek_get_parent_theme_slug();
    $pc_theme_name = sek_maybe_get_presscustomizr_theme_name( $theme_slug );
    // set this constant in wp_config.php
    $force_update = ( defined( 'NIMBLE_FORCE_UPDATE_API_DATA') && NIMBLE_FORCE_UPDATE_API_DATA ) ? true : $force_update;
    if ( true === $force_update && sek_is_dev_mode() ) {
          sek_error_log('API is in force update mode');
    }

    // Refresh every 12 hours, unless force_update set to true
    if ( $force_update || false === $info_data ) {
        $timeout = ( $force_update ) ? 25 : 8;
        $response = wp_remote_get( NIMBLE_DATA_API_URL_V2, array(
          'timeout' => $timeout,
          'body' => [
            'api_version' => NIMBLE_VERSION,
            'site_lang' => get_bloginfo( 'language' ),
            'theme_name' => $pc_theme_name,
            'start_ver' => sek_get_th_start_ver( $pc_theme_name )
          ],
        ) );

        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            // HOUR_IN_SECONDS is a default WP constant
            set_transient( $api_data_transient_name, [], 2 * HOUR_IN_SECONDS );
            return false;
        }

        $info_data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $info_data ) || !is_array( $info_data ) ) {
            set_transient( $api_data_transient_name, [], 2 * HOUR_IN_SECONDS );
            return false;
        }

        // on May 21st 2019 => back to the local data for preset sections
        // after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
        // if ( !empty( $info_data['library'] ) ) {
        //     if ( !empty( $info_data['library']['sections'] ) ) {
        //         update_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME, $info_data['library']['sections'], 'no' );
        //     }
        //     unset( $info_data['library'] );
        // }

        if ( isset( $info_data['latest_posts'] ) ) {
            update_option( NIMBLE_NEWS_OPT_NAME, $info_data['latest_posts'], 'no' );
            unset( $info_data['latest_posts'] );
        }

        set_transient( $api_data_transient_name, $info_data, 12 * HOUR_IN_SECONDS );
    }//if ( $force_update || false === $info_data ) {

    return $info_data;
}


//////////////////////////////////////////////////
/// SECTIONS DATA
function sek_get_sections_registration_params_api_data( $force_update = false ) {
    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update, theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['registration_params'] ) ) {
        sek_get_nimble_api_data( true );//<= true for "force_update"
        $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    }

    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['registration_params'] ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no section registration params' );
        return array();
    }
    return $sections_data['registration_params'];
}

function sek_get_preset_sections_api_data( $force_update = false ) {
    // To avoid a possible refresh, hence a reconnection to the api when opening the customizer
    // Let's use the data saved as options
    // Those data are updated on plugin install, plugin update( upgrader_process_complete ), theme switch
    // @see https://github.com/presscustomizr/nimble-builder/issues/441
    $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['json_collection'] ) ) {
        sek_get_nimble_api_data( true );//<= true for "force_update"
        $sections_data = get_option( NIMBLE_SECTIONS_LIBRARY_OPT_NAME );
    }

    if ( empty( $sections_data ) || !is_array( $sections_data ) || empty( $sections_data['json_collection'] ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no json_collection' );
        return array();
    }
    return $sections_data['json_collection'];
}


//////////////////////////////////////////////////
/// LATESTS POSTS
// @return array of posts
function sek_get_latest_posts_api_data( $force_update = false ) {
    sek_get_nimble_api_data( $force_update );
    $latest_posts = get_option( NIMBLE_NEWS_OPT_NAME );
    if ( empty( $latest_posts ) ) {
        sek_error_log( __FUNCTION__ . ' => error => no latest_posts' );
        return array();
    }
    return $latest_posts;
}

// @return html string
function sek_start_msg_from_api( $theme_name, $force_update = false ) {
    $info_data = sek_get_nimble_api_data( $force_update );
    if ( !sek_is_presscustomizr_theme( $theme_name ) || !is_array( $info_data ) ) {
        return '';
    }
    $msg = '';
    $api_msg = isset( $info_data['start_msg'] ) ? $info_data['start_msg'] : null;

    if ( !is_null($api_msg) && is_string($api_msg) ) {
        $msg = $api_msg;
    }
    return $msg;
}

// Refresh the api data on plugin update and theme switch
add_action( 'after_switch_theme', '\Nimble\sek_refresh_nimble_api_data');
add_action( 'upgrader_process_complete', '\Nimble\sek_refresh_nimble_api_data');
function sek_refresh_nimble_api_data() {
    // Refresh data on theme switch
    // => so the posts and message are up to date
    sek_get_nimble_api_data(true);
}

?>