<?php
/**
* Plugin Name: Nimble Builder
* Description: Drag-and-drop section builder companion of the Customizr and Hueman themes.
* Version: 1.1.4
* Text Domain: nimble-builder
* Author: Press Customizr
* Author URI: https://presscustomizr.com
*/

/* ------------------------------------------------------------------------- *
 *  CONSTANTS
/* ------------------------------------------------------------------------- */
$current_version = "1.1.4";
if ( !defined( "NIMBLE_VERSION" ) ) { define( "NIMBLE_VERSION", $current_version ); }
if ( !defined( 'NIMBLE_ASSETS_VERSION' ) ) {
    define( 'NIMBLE_ASSETS_VERSION', ( ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) ? time() : NIMBLE_VERSION );
}
if ( !defined( 'NIMBLE_DIR_NAME' ) ) { define( 'NIMBLE_DIR_NAME' , basename( dirname( __FILE__ ) ) ); }
if ( !defined( 'NIMBLE_BASE_URL' ) ) { define( 'NIMBLE_BASE_URL' , plugins_url( NIMBLE_DIR_NAME ) ); }
if ( !defined( 'NIMBLE_BASE_PATH' ) ) { define( 'NIMBLE_BASE_PATH' , dirname( __FILE__ ) ); }
if ( !defined( 'NIMBLE_MIN_PHP_VERSION' ) ) { define ( 'NIMBLE_MIN_PHP_VERSION', '5.4' ); }
if ( !defined( 'NIMBLE_MIN_WP_VERSION' ) ) { define ( 'NIMBLE_MIN_WP_VERSION', '4.7' ); }


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
    if ( did_action( 'nimble_base_fmk_loaded' ) ) {
        if ( ( defined( 'CZR_DEV' ) && CZR_DEV ) || ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) ) {
            error_log( __FILE__ . '  => The czr_base_fmk has already been loaded');
        }
        return;
    }
    require_once(  dirname( __FILE__ ) . '/inc/czr-base-fmk/czr-base-fmk.php' );
    \Nimble\CZR_Fmk_Base( array(
       'base_url' => NIMBLE_BASE_URL . '/inc/czr-base-fmk',
       'version' => NIMBLE_VERSION
    ));
}

require_once( plugin_dir_path( __FILE__ ) . 'inc/czr-skope/index.php' );
add_action( 'after_setup_theme', function() {
    \Nimble\Flat_Skop_Base( array(
        'base_url_path' => NIMBLE_BASE_URL . '/inc/czr-skope'
    ) );
});

require_once( plugin_dir_path( __FILE__ ) . 'inc/sektions/ccat-sektions.php' );

if ( defined( 'NIMBLE_PRINT_TEST' ) && NIMBLE_PRINT_TEST && file_exists( plugin_dir_path( __FILE__ ) . 'tests.php' ) ) {
    require_once( plugin_dir_path( __FILE__ ) . 'tests.php' );
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
add_action( 'wp_loaded', '\Nimble\sek_maybe_do_version_mapping' );