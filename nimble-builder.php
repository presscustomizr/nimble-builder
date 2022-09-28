<?php
/**
* Plugin Name: Nimble Page Builder
* Plugin URI: https://nimblebuilder.com
* Description: Simple and smart companion that allows you to insert sections into any existing page, create landing pages or entire websites including header and footer.
* Version: 3.3.2
* Text Domain: nimble-builder
* Author: Press Customizr
* Author URI: https://nimblebuilder.com/?utm_source=wp-plugins&utm_medium=wp-dashboard&utm_campaign=author-uri
* License: GPLv3
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/
if ( !defined( 'ABSPATH' ) ) {
  exit;
}
/* ------------------------------------------------------------------------- *
 *  CONSTANTS
/* ------------------------------------------------------------------------- */
$current_version = "3.3.2";

if ( !defined( "NIMBLE_VERSION" ) ) { define( "NIMBLE_VERSION", $current_version ); }
if ( !defined( 'NIMBLE_DIR_NAME' ) ) { define( 'NIMBLE_DIR_NAME' , basename( dirname( __FILE__ ) ) ); }
if ( !defined( 'NIMBLE_BASE_URL' ) ) { define( 'NIMBLE_BASE_URL' , plugins_url( NIMBLE_DIR_NAME ) ); }
if ( !defined( 'NIMBLE_BASE_PATH' ) ) { define( 'NIMBLE_BASE_PATH' , dirname( __FILE__ ) ); }
if ( !defined( 'NIMBLE_MIN_PHP_VERSION' ) ) { define ( 'NIMBLE_MIN_PHP_VERSION', '5.4' ); }
if ( !defined( 'NIMBLE_MIN_WP_VERSION' ) ) { define ( 'NIMBLE_MIN_WP_VERSION', '4.7' ); }
if ( !defined( 'NIMBLE_PLUGIN_FILE' ) ) { define( 'NIMBLE_PLUGIN_FILE', __FILE__ ); }// Plugin Root File used register_activation_hook( NIMBLE_PLUGIN_FILE, 'nimble_install' );

if ( !defined( 'NIMBLE_BETA_FEATURES_ENABLED' ) ) { define ( 'NIMBLE_BETA_FEATURES_ENABLED', false ); }

if ( !defined( 'NIMBLE_SHOW_UPDATE_NOTICE_FOR_VERSION' ) ) { define( 'NIMBLE_SHOW_UPDATE_NOTICE_FOR_VERSION', '1.8.3' ); }
if ( !defined( 'NIMBLE_RELEASE_NOTE_URL' ) ) { define( 'NIMBLE_RELEASE_NOTE_URL', 'https://presscustomizr.com/nimble-builder-introduces-a-new-about-us-pre-built-section-and-global-text-options/' ); }

// when NIMBLE_IS_PREVIEW_UI_DEBUG_MODE or $_GET['preview_ui_debug'] is true, the levels UI in the preview are not being auto removed, so we can inspect the markup and CSS
if ( !defined( 'NIMBLE_IS_PREVIEW_UI_DEBUG_MODE' ) ) { define ( 'NIMBLE_IS_PREVIEW_UI_DEBUG_MODE', false ); }

// Admin page
if ( !defined( 'NIMBLE_OPTIONS_PAGE' ) ) { define ( 'NIMBLE_OPTIONS_PAGE', 'nb-options' ); }
if ( !defined( 'NIMBLE_OPTIONS_PAGE_URL' ) ) { define ( 'NIMBLE_OPTIONS_PAGE_URL', 'options-general.php?page=' . NIMBLE_OPTIONS_PAGE ); }

/* ------------------------------------------------------------------------- *
 *  CHECK PHP AND WP REQUIREMENTS
/* ------------------------------------------------------------------------- */
if ( version_compare( phpversion(), NIMBLE_MIN_PHP_VERSION, '<' ) ) {
    add_action( 'admin_notices' , 'nimble_display_min_php_message' );
    return;
}
global $wp_version;
if ( version_compare( $wp_version, NIMBLE_MIN_WP_VERSION, '<' ) ) {
    add_action( 'admin_notices' , 'nimble_display_min_wp_message' );
    return;
}

function nimble_passes_requirements(){
  global $wp_version;
  return !version_compare( phpversion(), NIMBLE_MIN_PHP_VERSION, '<' ) && !version_compare( $wp_version, NIMBLE_MIN_WP_VERSION, '<' );
}

function nimble_display_min_php_message() {
    nimble_display_min_requirement_notice( __( 'PHP', 'text_doma' ), NIMBLE_MIN_PHP_VERSION );
}
function nimble_display_min_wp_message() {
    nimble_display_min_requirement_notice( __( 'WordPress', 'text_doma' ), NIMBLE_MIN_WP_VERSION );
}
function nimble_display_min_requirement_notice( $requires_what, $requires_what_version ) {
    printf( '<div class="error"><p>%1$s</p></div>',
        sprintf( __( 'The <strong>%1$s</strong> plugin requires at least %2$s version %3$s.', 'text_doma' ),
            __('Nimble Builder', 'text_doma'),
            esc_attr($requires_what),
            esc_attr($requires_what_version)
        )
    );
}

/* ------------------------------------------------------------------------- *
 *  LOAD
/* ------------------------------------------------------------------------- */
add_action( 'after_setup_theme', 'nimble_load_czr_base_fmk', 10 );
function nimble_load_czr_base_fmk() {
    if ( !nimble_passes_requirements() )
      return;
    if ( did_action( 'nimble_base_fmk_loaded' ) ) {
        if ( ( defined( 'CZR_DEV' ) && CZR_DEV ) || ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) ) {
            error_log( __FILE__ . '  => The czr_base_fmk has already been loaded');
        }
        return;
    }

    require_once(  NIMBLE_BASE_PATH . '/inc/czr-base-fmk/czr-base-fmk.php' );
    if ( class_exists('\Nimble\CZR_Fmk_Base') ) {
        \Nimble\CZR_Fmk_Base( array(
           'base_url' => NIMBLE_BASE_URL . '/inc/czr-base-fmk',
           'version' => NIMBLE_VERSION
        ));
    }
}

if ( nimble_passes_requirements() ) {
    require_once( NIMBLE_BASE_PATH . '/inc/czr-skope/index.php' );
    add_action( 'after_setup_theme', 'nimble_load_skope_php');
    function nimble_load_skope_php() {
        if ( class_exists('\Nimble\Flat_Skop_Base') ) {
            \Nimble\Flat_Skop_Base( array(
                'base_url_path' => NIMBLE_BASE_URL . '/inc/czr-skope'
            ) );
        }
    }

    do_action('nimble_before_loading');
    require_once( NIMBLE_BASE_PATH . '/inc/sektions/ccat-constants-and-helper-functions.php' );
    do_action('nimble_base_loaded');
    require_once( NIMBLE_BASE_PATH . '/inc/sektions/ccat-sektions-ui-modules.php' );
    require_once( NIMBLE_BASE_PATH . '/inc/sektions/ccat-sektions-front-modules.php' );
    require_once( NIMBLE_BASE_PATH . '/inc/sektions/ccat-sektions-base.php' );
    do_action('nimble_after_loading');

    // $_POST['ac_get_template'] <= scenario of an input template getting ajaxily fetched
    if ( \Nimble\skp_is_customizing() || isset( $_POST['ac_get_template']) || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
        require_once( NIMBLE_BASE_PATH . '/inc/sektions/ccat-czr-sektions.php' );
        do_action('nimble_base_czr_loaded');
    }

    add_action( 'after_setup_theme', 'nimble_setup_dyn_register', 20 );
    function nimble_setup_dyn_register( $params = array() ) {
        if ( class_exists('\Nimble\SEK_CZR_Dyn_Register') ) {
            // instantiate if not done yet
            \Nimble\SEK_CZR_Dyn_Register::get_instance( $params );
        }
    }

    if ( defined( 'NIMBLE_PRINT_DEV_LOGS' ) && NIMBLE_PRINT_DEV_LOGS && file_exists( plugin_dir_path( __FILE__ ) . 'dev_logs.php' ) ) {
        require_once( NIMBLE_BASE_PATH . '/dev_logs.php' );
    }

    add_action('plugins_loaded', 'nimble_load_plugin_textdomain');
    /**
    * Load language files
    * @action plugins_loaded
    */
    function nimble_load_plugin_textdomain() {
        // Note to self, the third argument must not be hardcoded, to account for relocated folders.
        load_plugin_textdomain( 'nimble-builder' );
    }

    require_once( NIMBLE_BASE_PATH . '/inc/functions.php' );

    // Fire the retro compatibility functions
    // Note : if fired @plugins_loaded, invoking wp_update_post() generates php notices
    //add_action( 'wp_loaded', '\Nimble\sek_maybe_do_version_mapping' );
    // introduced for https://github.com/presscustomizr/nimble-builder/issues/799
    add_action( 'wp_loaded', '\Nimble\sek_maybe_optimize_options' );

    // Load admin
    if ( is_admin() ) {
        require_once( NIMBLE_BASE_PATH . '/inc/admin/nimble-admin.php' );
        do_action('nimble_admin_loaded');
    }
}//if ( nimble_passes_requirements() )