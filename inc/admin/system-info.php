<?php
namespace Nimble;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get system info
 * Inspired by the system infos page for Easy Digital Download plugin
 * @return      string $return A string containing the info to output
 */
function sek_config_infos() {
    global $wpdb;

    if ( !class_exists( 'Browser' ) ) {
        require_once( NIMBLE_BASE_PATH . '/inc/libs/browser.php' );
    }

    $browser = new \Browser();

    // Get theme info
    $theme_data   = wp_get_theme();
    $theme        = $theme_data->Name . ' ' . $theme_data->Version;
    $parent_theme = $theme_data->Template;
    if ( !empty( $parent_theme ) ) {
      $parent_theme_data = wp_get_theme( $parent_theme );
      $parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
    }

    $return  = '### Begin System Info (Generated ' . date( 'Y-m-d H:i:s' ) . ') ###' . "";

    // Site infos
    $return .= "\n" .'------------ SITE INFO' . "\n";
    $return .= 'Site URL:                 ' . site_url() . "\n";
    $return .= 'Home URL:                 ' . home_url() . "\n";
    $return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

    // Browser infos
    $return .= "\n\n" . '------------ USER BROWSER' . "\n";
    $return .= $browser;

    $locale = get_locale();

    // WordPress config
    $return .= "\n\n" . '------------ WORDPRESS CONFIG' . "\n";
    $return .= 'WP Version:               ' . get_bloginfo( 'version' ) . "\n";
    $return .= 'Language:                 ' . ( !empty( $locale ) ? $locale : 'en_US' ) . "\n";
    $return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
    $return .= 'Active Theme:             ' . $theme . "\n";
    if ( $parent_theme !== $theme ) {
      $return .= 'Parent Theme:             ' . $parent_theme . "\n";
    }
    $return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

    // Only show page specs if frontpage is set to 'page'
    if( get_option( 'show_on_front' ) == 'page' ) {
      $front_page_id = get_option( 'page_on_front' );
      $blog_page_id = get_option( 'page_for_posts' );

      $return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
      $return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
    }

    $return .= 'ABSPATH:                  ' . ABSPATH . "\n";

    $return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
    $return .= 'WP Memory Limit:          ' . ( sek_let_to_num( WP_MEMORY_LIMIT )/( 1024 ) ) ."MB" . "\n";
    //$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

    // Nimble configuration
    $return .= "\n\n" . '------------ NIMBLE CONFIGURATION' . "\n";
    $return .= 'Version:                  ' . NIMBLE_VERSION . "\n";
    $return .= 'Upgraded From:            ' . get_option( 'nimble_version_upgraded_from', 'None' ) . "\n";
    $return .= 'Started With:             ' . get_option( 'nimble_started_with_version', 'None' ) . "\n";

    // sept 2020 : filter added to allow printing NB Pro versions info
    $return = apply_filters('nb_admin_syst_info_after_nb_config', $return);

    // Get plugins that have an update
    $updates = get_plugin_updates();

    // Must-use plugins
    // NOTE: MU plugins can't show updates!
    $muplugins = get_mu_plugins();
    if( count( $muplugins ) > 0 ) {
      $return .= "\n\n" . '------------ MU PLUGINS' . "\n";

      foreach( $muplugins as $plugin => $plugin_data ) {
        $return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
      }
    }

    // WordPress active plugins
    $return .= "\n\n" . '------------ WP ACTIVE PLUGINS' . "\n";

    $plugins = get_plugins();
    $active_plugins = get_option( 'active_plugins', array() );

    foreach( $plugins as $plugin_path => $plugin ) {
      if( !in_array( $plugin_path, $active_plugins ) )
        continue;

      $update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
      $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
    }

    // WordPress inactive plugins
    $return .= "\n\n" . '------------ WP INACTIVE PLUGINS' . "\n";

    foreach( $plugins as $plugin_path => $plugin ) {
      if( in_array( $plugin_path, $active_plugins ) )
        continue;

      $update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
      $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
    }

    if( is_multisite() ) {
      // WordPress Multisite active plugins
      $return .= "\n\n" . '------------ NETWORK ACTIVE PLUGINS' . "\n";

      $plugins = wp_get_active_network_plugins();
      $active_plugins = get_site_option( 'active_sitewide_plugins', array() );

      foreach( $plugins as $plugin_path ) {
        $plugin_base = plugin_basename( $plugin_path );

        if( !array_key_exists( $plugin_base, $active_plugins ) )
          continue;

        $update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
        $plugin  = get_plugin_data( $plugin_path );
        $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
      }
    }

    // Server configuration
    $return .= "\n\n" . '------------ WEBSERVER CONFIG' . "\n";
    $return .= 'PHP Version:              ' . PHP_VERSION . "\n";
    $return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
    $return .= 'Webserver Info:           ' . sanitize_text_field($_SERVER['SERVER_SOFTWARE']) . "\n";
    $return .= 'Write/Read permissions:   ' . sek_get_write_permissions_status() . "\n";

    // PHP configs
    $return .= "\n\n" . '------------ PHP CONFIG' . "\n";
    $return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
    $return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
    $return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
    $return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
    $return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
    $return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
    $return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";
    $return .= 'PHP Arg Separator:        ' . ini_get( 'arg_separator.output' ) . "\n";
    $return .= 'PHP Allow URL File Open:  ' . ini_get( 'allow_url_fopen' ) . "\n";

    // PHP extensions and such
    // $return .= "\n\n" . '------------ PHP EXTENSIONS' . "\n";
    // $return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
    // $return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
    // $return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
    // $return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

    $return .= "\n\n" . '### End System Info ###';

    return $return;
}


/**
 * Does Size Conversions
 */
function sek_let_to_num( $v ) {
    $l   = substr( $v, -1 );
    $ret = substr( $v, 0, -1 );

    switch ( strtoupper( $l ) ) {
      case 'P': // fall-through
      case 'T': // fall-through
      case 'G': // fall-through
      case 'M': // fall-through
      case 'K': // fall-through
        $ret *= 1024;
        break;
      default:
        break;
    }
    return $ret;
}


function sek_get_write_permissions_status() {
    $permission_issues = array();
    $writing_path_candidates = array();

    $wp_upload_dir = wp_upload_dir();
    if ( $wp_upload_dir['error'] ) {
        $permission_issues[] = 'WordPress root uploads folder';
    }

    $nimble_css_folder_path = $wp_upload_dir['basedir'] . '/' . NIMBLE_CSS_FOLDER_NAME;

    if ( is_dir( $nimble_css_folder_path ) ) {
        $writing_path_candidates[ $nimble_css_folder_path ] = 'Nimble uploads folder';
    }
    $writing_path_candidates[ ABSPATH ] = 'WP root directory';

    foreach ( $writing_path_candidates as $dir => $description ) {
        if ( !is_writable( $dir ) ) {
            $permission_issues[] = $description;
        }
    }

    // oct 2020, for https://github.com/presscustomizr/nimble-builder/issues/749
    $base_module_css_uri = NIMBLE_BASE_PATH . '/assets/front/css/modules/';
    if ( !is_readable($base_module_css_uri) ) {
        $permission_issues[] = 'module css folder not readable';
    }

    if ( $permission_issues ) {
        $message = 'NOK => issues with : ';
        $message .= implode( ' and ', $permission_issues );
    } else {
        $message = 'OK';
    }

    return $message;
}

?>