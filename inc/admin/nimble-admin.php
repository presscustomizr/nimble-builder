<?php
namespace Nimble;
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// /* ------------------------------------------------------------------------- *
// *  WELCOME NOTICE
// /* ------------------------------------------------------------------------- */
/* beautify admin notice text using some defaults the_content filter callbacks */
foreach ( array( 'wptexturize', 'convert_smilies' ) as $callback ) {
  add_filter( 'nimble_update_notice', $callback );
}
add_action( 'admin_notices', '\Nimble\sek_render_welcome_notice' );
function sek_render_welcome_notice() {
    if ( ! current_user_can( 'customize' ) )
      return;

    $notice_id = 'nimble-welcome-notice-12-2018';
    $dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
    $dismissed_array = array_filter( explode( ',', (string) $dismissed ) );

    if ( in_array( $notice_id, $dismissed_array ) ) {
      return;
    }

    // If the notice has not been dismissed, make sure it is still relevant to display it.
    // If user has started created sections, we should not display it anymore => update the dismissed pointers array
    // @see https://developer.wordpress.org/reference/functions/wp_ajax_dismiss_wp_pointer/
    if ( sek_site_has_nimble_sections_created() ) {
        $dismissed_array[] = $notice_id;
        $dismissed = implode( ',', $dismissed_array );
        update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );
        return;
    }

    ?>
    <div class="nimble-welcome-notice notice notice-info is-dismissible" id="<?php echo esc_attr( $notice_id ); ?>">
      <div class="notice-dismiss"></div>
      <div class="nimble-welcome-icon-holder">
        <img class="nimble-welcome-icon" src="<?php echo NIMBLE_BASE_URL.'/assets/img/nimble/nimble_banner.svg?ver='.NIMBLE_VERSION; ?>" alt="<?php esc_html_e( 'Nimble Builder', 'nimble' ); ?>" />
      </div>
      <h1><?php echo apply_filters( 'nimble_update_notice', __('Welcome to the Nimble Builder for WordPress :D', 'nimble' ) ); ?></h1>
      <h3><?php _e( 'The Nimble Builder takes the native WordPress customizer to a level you\'ve never seen before.', 'nimble' ); ?></h3>
      <h3><?php _e( 'Nimble allows you to insert content into any page and edit it in <i>real</i> live preview. The plugin automatically creates fluid layouts for your visitors using smartphones or tablets, without the need to add complex code. Nimble Builder comes with a set of carefully crafted and attractive pre-built sections.', 'nimble' ); ?></h3>
      <?php printf( '<a href="%1$s" target="_blank" class="button button-primary button-hero"><span class="dashicons dashicons-admin-appearance"></span> %2$s</a>',
          esc_url( add_query_arg(
              array(
                array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
                'return' => urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) )
              ),
              admin_url( 'customize.php' )
          ) ),
          __( 'Start creating content in live preview', 'nimble' )
      ); ?>
      <div class="nimble-link-to-doc">
        <?php printf( '<div class="nimble-doc-link-wrap">%1$s <a href="%2$s" target="_blank" class="">%3$s</a>.</div>',
            __('Or', 'nimble'),
            esc_url( add_query_arg(
                array(
                  'utm_source' => 'usersite',
                  'utm_medium' => 'link',
                  'utm_campaign' => 'nimble-welcome-notice'
                ),
                'docs.presscustomizr.com/article/337-getting-started-with-the-nimble-builder-plugin'
            ) ),
            __( 'read the getting started guide', 'nimble' )
        ); ?>
      </div>
    </div>

    <script>
    jQuery( function( $ ) {
      // On dismissing the notice, make a POST request to store this notice with the dismissed WP pointers so it doesn't display again.
      $( <?php echo wp_json_encode( "#$notice_id" ); ?> ).on( 'click', '.notice-dismiss', function() {
        $.post( ajaxurl, {
          pointer: <?php echo wp_json_encode( $notice_id ); ?>,
          action: 'dismiss-wp-pointer'
        } );
      } );
    } );
    </script>
    <style type="text/css">
      .nimble-welcome-notice {
        padding: 38px;
      }
      .nimble-welcome-notice .dashicons {
        line-height: 44px;
      }
      .nimble-welcome-icon-holder {
        width: 550px;
        height: 200px;
        float: left;
        margin: 0 38px 38px 0;
      }
      .nimble-welcome-icon {
        width: 100%;
        height: 100%;
        display: block;
      }
      .nimble-welcome-notice h1 {
        font-weight: bold;
      }
      .nimble-welcome-notice h3 {
        font-size: 16px;
        font-weight: 500;
      }
      .nimble-link-to-doc {
        position: relative;
        display: inline-block;
        width: 200px;
        height: 46px;
      }
      .nimble-link-to-doc .nimble-doc-link-wrap {
        position: absolute;
        bottom: 0;
      }

    </style>
    <?php
  }


// /* ------------------------------------------------------------------------- *
// *  VERSIONNING
// /* ------------------------------------------------------------------------- */
add_action( 'plugins_loaded', '\Nimble\sek_versionning');
// @see https://wordpress.stackexchange.com/questions/144870/wordpress-update-plugin-hook-action-since-3-9
function sek_versionning() {
    // Add Upgraded From Option + update current version if needed
    $current_version = get_option( 'nimble_version' );
    if ( $current_version != NIMBLE_VERSION ) {
        update_option( 'nimble_version_upgraded_from', $current_version );
        update_option( 'nimble_version', NIMBLE_VERSION );
    }
    // Write the version that the user started with.
    // Note : this has been implemented starting from v1.1.8 in October 2018. At this time 4000+ websites were already using the plugin, and therefore started with a version <= 1.1.7.
    $started_with = get_option( 'nimble_started_with_version' );
    if ( empty( $started_with ) ) {
        update_option( 'nimble_started_with_version', $current_version );
    }
    $start_date = get_option( 'nimble_start_date' );
    if ( empty( $start_date ) ) {
        update_option( 'nimble_start_date', date("Y-m-d H:i:s") );
    }
}


// /* ------------------------------------------------------------------------- *
// *  SYSTEM INFOS
// /* ------------------------------------------------------------------------- */
add_action('admin_menu', '\Nimble\sek_plugin_menu');
function sek_plugin_menu() {
  add_plugins_page(__( 'System infos', 'text_domain' ), __( 'System infos', 'text_domain' ), 'read', 'nimble-builder', '\Nimble\sek_plugin_page');
}

function sek_plugin_page() {
    ?>
    <div class="wrap">
      <h3><?php _e( 'System Informations', 'text_domain_to_be_chg' ); ?></h3>
      <h4 style="text-align: left"><?php _e( 'Please include your system informations when posting support requests.' , 'text_domain_to_be_chg' ) ?></h4>
      <textarea readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="tc-sysinfo" title="<?php _e( 'To copy the system infos, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'text_domain_to_be_chg' ); ?>" style="width: 800px;min-height: 800px;font-family: Menlo,Monaco,monospace;background: 0 0;white-space: pre;overflow: auto;display:block;"><?php echo sek_config_infos(); ?></textarea>
    </div>
    <?php
}





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
    if ( ! empty( $parent_theme ) ) {
      $parent_theme_data = wp_get_theme( $parent_theme );
      $parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
    }

    $return  = '### Begin System Infos (Generated ' . date( 'Y-m-d H:i:s' ) . ') ###' . "";

    // Site infos
    $return .= "\n" .'------------ SITE INFO' . "\n";
    $return .= 'Site URL:                 ' . site_url() . "\n";
    $return .= 'Home URL:                 ' . home_url() . "\n";
    $return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

    // Browser ingos
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
    $return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

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

    $return .= "\n\n" . '### End System Infos ###';

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

// /* ------------------------------------------------------------------------- *
// *  ENQUEUE ADMIN STYLE
// /* ------------------------------------------------------------------------- */
add_action( 'admin_init' , '\Nimble\sek_admin_style' );
function sek_admin_style() {
    if ( skp_is_customizing() )
      return;
    wp_enqueue_style(
        'nimble-admin-css',
        sprintf(
            '%1$s/assets/admin/css/%2$s' ,
            NIMBLE_BASE_URL,
            'nimble-admin.css'
        ),
        array(),
        NIMBLE_ASSETS_VERSION,
        'all'
    );
}



// /* ------------------------------------------------------------------------- *
// *  UPDATE NOTIFICATIONS
// /* ------------------------------------------------------------------------- */
if( ! defined( 'DISPLAY_UPDATE_NOTIFICATION' ) ) { define( 'DISPLAY_UPDATE_NOTIFICATION', true ); }
add_action( 'admin_notices'                         , '\Nimble\sek_may_be_display_update_notice');
// always add the ajax action
add_action( 'wp_ajax_dismiss_nimble_update_notice'  ,  '\Nimble\sek_dismiss_update_notice_action' );
add_action( 'admin_footer'                          ,  '\Nimble\sek_write_ajax_dismis_script' );
// beautify admin notice text using some defaults the_content filter callbacks
foreach ( array( 'wptexturize', 'convert_smilies', 'wpautop') as $callback ) {
  if ( function_exists( $callback ) )
      add_filter( 'sek_update_notice', $callback );
}


/**
* @hook : admin_notices
*/
function sek_may_be_display_update_notice() {
    if ( ! defined('DISPLAY_UPDATE_NOTIFICATION') || ! DISPLAY_UPDATE_NOTIFICATION )
      return;
    $last_update_notice_values  = get_option( 'nimble_last_update_notice' );
    $show_new_notice = false;
    $display_ct = 5;

    if ( ! $last_update_notice_values || ! is_array($last_update_notice_values) ) {
        //first time user of the theme, the option does not exist
        // 1) initialize it => set it to the current Hueman version, displayed 0 times.
        // 2) update in db
        $last_update_notice_values = array( "version" => NIMBLE_VERSION, "display_count" => 0 );
        update_option( 'nimble_last_update_notice', $last_update_notice_values );
        //already user of the theme ? => show the notice if
        if ( sek_user_started_before_version( NIMBLE_VERSION ) ) {
            $show_new_notice = true;
        }
    }

    $_db_version          = $last_update_notice_values["version"];
    $_db_displayed_count  = $last_update_notice_values["display_count"];

    //user who just upgraded the theme will be notified until he/she clicks on the dismiss link
    //or until the notice has been displayed n times.
    if ( version_compare( NIMBLE_VERSION, $_db_version , '>' ) ) {
        //CASE 1 : displayed less than n times
        if ( $_db_displayed_count < $display_ct ) {
            $show_new_notice = true;
            //increments the counter
            (int) $_db_displayed_count++;
            $last_update_notice_values["display_count"] = $_db_displayed_count;
            //updates the option val with the new count
            update_option( 'nimble_last_update_notice', $last_update_notice_values );
        }
        //CASE 2 : displayed n times => automatic dismiss
        else {
            //reset option value with new version and counter to 0
            $new_val  = array( "version" => NIMBLE_VERSION, "display_count" => 0 );
            update_option('nimble_last_update_notice', $new_val );
        }//end else
    }//end if

    //always display in dev mode
    //$show_new_notice = ( defined( 'CZR_DEV' ) && CZR_DEV ) ? true : $show_new_notice;

    if ( ! $show_new_notice )
      return;

    ob_start();
      ?>
      <div class="updated czr-update-notice" style="position:relative;">
        <?php
          printf('<h3>%1$s %2$s %3$s %4$s :D</h3>',
              __( "Thanks, you successfully upgraded", 'text_domain_to_be_replaced'),
              'Nimble Builder',
              __( "to version", 'text_domain_to_be_replaced'),
              NIMBLE_VERSION
          );
        ?>
        <?php
          printf( '<h4>%1$s <a class="" href="%2$s" title="%3$s" target="_blank">%3$s &raquo;</a></h4>',
              '',//__( "Let us introduce the new features we've been working on.", 'text_domain_to_be_replaced'),
              "http://presscustomizr.com/release-note-for-the-nimble-builder-version-1-4-0/",
              __( "Read the detailled release notes" , 'text_domain_to_be_replaced' )
              // ! NIMBLE_IS_PRO ? sprintf( '<p style="position: absolute;right: 7px;top: 4px;"><a class="button button-primary upgrade-to-pro" href="%1$s" title="%2$s" target="_blank">%2$s &raquo;</a></p>',
              //   esc_url('presscustomizr.com/hueman-pro?ref=a'),
              //   __( "Upgrade to Hueman Pro", 'text_domain_to_be_replaced' )
              // ) : ''
          );
        ?>
        <p style="text-align:right;position: absolute;font-size: 1.1em;<?php echo is_rtl()? 'left' : 'right';?>: 7px;bottom: -6px;">
        <?php printf('<a href="#" title="%1$s" class="nimble-dismiss-update-notice"> ( %1$s <strong>X</strong> ) </a>',
            __('close' , 'text_domain_to_be_replaced')
          );
        ?>
        </p>
        <!-- <p>
          <?php
          // printf(
          //   __( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'text_domain_to_be_replaced' ),
          //   sprintf( '<strong>%s</strong>', esc_html__( 'the Nimble Builder', 'text_domain_to_be_replaced' ) ),
          //   sprintf( '<a href="%1$s" target="_blank" class="czr-rating-link">&#9733;&#9733;&#9733;&#9733;&#9733;</a>', esc_url( 'wordpress.org/support/plugin/nimble-builder/reviews/?filter=5#new-post') )
          // );
          ?>
        </p> -->
      </div>
      <?php
    $_html = ob_get_contents();
    if ($_html) ob_end_clean();
    echo apply_filters( 'sek_update_notice', $_html );
}


/**
* hook : wp_ajax_dismiss_nimble_update_notice
* => sets the last_update_notice to the current Hueman version when user click on dismiss notice link
*/
function sek_dismiss_update_notice_action() {
    check_ajax_referer( 'dismiss-update-notice-nonce', 'dismissUpdateNoticeNonce' );
    //reset option value with new version and counter to 0
    $new_val  = array( "version" => NIMBLE_VERSION, "display_count" => 0 );
    update_option( 'nimble_last_update_notice', $new_val );
    wp_die();
}



/**
* hook : admin_footer
*/
function sek_write_ajax_dismis_script() {
    ?>
    <script type="text/javascript" id="nimble-dismiss-update-notice">
      ( function($){
        var _ajax_action = function( $_el ) {
            var AjaxUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                _query  = {
                    action  : 'dismiss_nimble_update_notice',
                    dismissUpdateNoticeNonce :  "<?php echo wp_create_nonce( 'dismiss-update-notice-nonce' ); ?>"
                },
                $ = jQuery,
                request = $.post( AjaxUrl, _query );

            request.fail( function ( response ) {
              console.log('response when failed : ', response);
            });
            request.done( function( response ) {
              console.log('RESPONSE DONE', $_el, response);
              // Check if the user is logged out.
              if ( '0' === response )
                return;
              // Check for cheaters.
              if ( '-1' === response )
                return;

              $_el.closest('.updated').slideToggle('fast');
            });
        };//end of fn

        //on load
        $( function($) {
          $('.nimble-dismiss-update-notice').click( function( e ) {
            e.preventDefault();
            _ajax_action( $(this) );
          } );
        } );

      } )( jQuery );


    </script>
    <?php
}