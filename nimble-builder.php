<?php
/**
* Plugin Name: Nimble Builder
* Description: Drag-and-drop section builder companion of the Customizr and Hueman themes.
* Version: 1.1.9
* Text Domain: nimble-builder
* Author: Press Customizr
* Author URI: https://presscustomizr.com
*/

/* ------------------------------------------------------------------------- *
 *  CONSTANTS
/* ------------------------------------------------------------------------- */
$current_version = "1.1.9";
if ( !defined( "NIMBLE_VERSION" ) ) { define( "NIMBLE_VERSION", $current_version ); }
if ( !defined( 'NIMBLE_DIR_NAME' ) ) { define( 'NIMBLE_DIR_NAME' , basename( dirname( __FILE__ ) ) ); }
if ( !defined( 'NIMBLE_BASE_URL' ) ) { define( 'NIMBLE_BASE_URL' , plugins_url( NIMBLE_DIR_NAME ) ); }
if ( !defined( 'NIMBLE_BASE_PATH' ) ) { define( 'NIMBLE_BASE_PATH' , dirname( __FILE__ ) ); }
if ( !defined( 'NIMBLE_MIN_PHP_VERSION' ) ) { define ( 'NIMBLE_MIN_PHP_VERSION', '5.4' ); }
if ( !defined( 'NIMBLE_MIN_WP_VERSION' ) ) { define ( 'NIMBLE_MIN_WP_VERSION', '4.7' ); }
if ( !defined( 'NIMBLE_PLUGIN_FILE' ) ) { define( 'NIMBLE_PLUGIN_FILE', __FILE__ ); }// Plugin Root File used register_activation_hook( NIMBLE_PLUGIN_FILE, 'nimble_install' );
if ( !defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) ) { define ( 'NIMBLE_SAVED_SECTIONS_ENABLED', true ); }

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

function nimble_pass_requirements(){
  global $wp_version;
  return ! version_compare( phpversion(), NIMBLE_MIN_PHP_VERSION, '<' ) && ! version_compare( $wp_version, NIMBLE_MIN_WP_VERSION, '<' );
}

function nimble_display_min_php_message() {
    nimble_display_min_requirement_notice( __( 'PHP', 'text_domain_to_be_replaced' ), NIMBLE_MIN_PHP_VERSION );
}
function nimble_display_min_wp_message() {
    nimble_display_min_requirement_notice( __( 'WordPress', 'text_domain_to_be_replaced' ), NIMBLE_MIN_WP_VERSION );
}
function nimble_display_min_requirement_notice( $requires_what, $requires_what_version ) {
    printf( '<div class="error"><p>%1$s</p></div>',
        sprintf( __( 'The <strong>%1$s</strong> plugin requires at least %2$s version %3$s.', 'text_domain_to_be_replaced' ),
            __('Nimble Builder', 'text_domain_to_be_replaced'),
            $requires_what,
            $requires_what_version
        )
    );
}

/* ------------------------------------------------------------------------- *
 *  LOAD
/* ------------------------------------------------------------------------- */
add_action( 'after_setup_theme', 'nimble_load_czr_base_fmk', 10 );
function nimble_load_czr_base_fmk() {
    if ( ! nimble_pass_requirements() )
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
if ( nimble_pass_requirements() ) {

    require_once( NIMBLE_BASE_PATH . '/inc/czr-skope/index.php' );
    add_action( 'after_setup_theme', 'nimble_load_skope_php');
    function nimble_load_skope_php() {
        if ( class_exists('\Nimble\Flat_Skop_Base') ) {
            \Nimble\Flat_Skop_Base( array(
                'base_url_path' => NIMBLE_BASE_URL . '/inc/czr-skope'
            ) );
        }
    }

    require_once( NIMBLE_BASE_PATH . '/inc/sektions/ccat-sektions.php' );
    // $_POST['ac_get_template'] <= scenario of an input template getting ajaxily fetched
    if ( \Nimble\skp_is_customizing() || isset( $_POST['ac_get_template']) || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
        require_once( NIMBLE_BASE_PATH . '/inc/sektions/ccat-czr-sektions.php' );
    }

    add_action( 'after_setup_theme', 'nimble_setup_dyn_register', 20 );
    function nimble_setup_dyn_register( $params = array() ) {
        if ( class_exists('\Nimble\SEK_CZR_Dyn_Register') ) {
            // instantiate if not done yet
            \Nimble\SEK_CZR_Dyn_Register::get_instance( $params );
        }
    }

    if ( defined( 'NIMBLE_PRINT_TEST' ) && NIMBLE_PRINT_TEST && file_exists( plugin_dir_path( __FILE__ ) . 'tests.php' ) ) {
        require_once( NIMBLE_BASE_PATH . '/tests.php' );
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

    // @return void()
    function nimble_register_location( $location, $params = array() ) {
        if ( empty( $location ) || ! is_string( $location ) )
          return;
        \Nimble\register_location( $location, $params );
    }

    // Fire the retro compatibility functions
    // Note : if fired @plugins_loaded, invoking wp_update_post() generates php notices
    add_action( 'wp_loaded', '\Nimble\sek_maybe_do_version_mapping' );

    // Load admin
    if ( is_admin() ) {
        require_once( NIMBLE_BASE_PATH . '/inc/admin/nimble-admin.php' );
    }
}


// WP 5.0.0 compat
// @see https://core.trac.wordpress.org/ticket/45292
if ( false !== has_filter( 'content_save_pre', 'wp_targeted_link_rel' ) ) { remove_filter( 'content_save_pre', 'wp_targeted_link_rel' ); }