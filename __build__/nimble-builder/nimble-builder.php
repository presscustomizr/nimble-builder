<?php
/**
* Plugin Name: Nimble Builder
* Plugin URI: https://presscustomizr.com
* Description: Live drag and drop builder for the WordPress Customizer
* Version: 1.0.0-beta-2
* Text Domain: nimble-builder
* Author: Press Customizr
* Author URI: https://presscustomizr.com
*/

/* ------------------------------------------------------------------------- *
 *  CONSTANTS
/* ------------------------------------------------------------------------- */
$current_version = "1.0.0-beta-2";
if ( ! defined( "NIMBLE_VERSION" ) ) { define( "NIMBLE_VERSION", $current_version );}
if ( ! defined( 'NIMBLE_ASSETS_VERSION' ) ) {
    define( 'NIMBLE_ASSETS_VERSION', ( ( defined( 'CZR_DEV' ) && CZR_DEV ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) ? time() : NIMBLE_VERSION );
}
if ( ! defined( 'NIMBLE_DIR_NAME' ) ) { define( 'NIMBLE_DIR_NAME' , basename( dirname( __FILE__ ) ) ); }
if ( ! defined( 'NIMBLE_BASE_URL' ) ) { define( 'NIMBLE_BASE_URL' , plugins_url( NIMBLE_DIR_NAME ) ); }
if ( ! defined( 'NIMBLE_BASE_PATH' ) ) { define( 'NIMBLE_BASE_PATH' , dirname( __FILE__ ) ); }
if ( !defined( 'NIMBLE_MIN_PHP_VERSION' ) ) define ( 'NIMBLE_MIN_PHP_VERSION', '5.4' );
if ( !defined( 'NIMBLE_MIN_WP_VERSION' ) ) define ( 'NIMBLE_MIN_WP_VERSION', '4.7' );


/* ------------------------------------------------------------------------- *
 *  MINIMAL PHP AND WP REQUIREMENTS
/* ------------------------------------------------------------------------- */
if ( version_compare( phpversion(), NIMBLE_MIN_PHP_VERSION, '<' ) ) {
    add_action( 'admin_notices' , 'sek_display_min_php_message' );
    return;
}
global $wp_version;
if ( version_compare( $wp_version, NIMBLE_MIN_WP_VERSION, '<' ) ) {
    add_action( 'admin_notices' , 'sek_display_min_wp_message' );
    return;
}
function sek_display_min_php_message() {
    sek_display_min_requirement_notice( __( 'PHP', 'nimble-builder' ), NIMBLE_MIN_PHP_VERSION );
}
function sek_display_min_wp_message() {
    sek_display_min_requirement_notice( __( 'WordPress', 'nimble-builder' ), NIMBLE_MIN_WP_VERSION );
}
function sek_display_min_requirement_notice( $requires_what, $requires_what_version ) {
    printf( '<div class="error"><p>%1$s</p></div>',
        sprintf( __( 'The <strong>%1$s</strong> plugin requires at least %2$s version %3$s.', 'nimble-builder' ),
            __('Nimble Builder', 'nimble-builder'),
            $requires_what,
            $requires_what_version
        )
    );
}

/* ------------------------------------------------------------------------- *
 *  LOAD THE BASE CUSTOMIZER FMK => NEEDED WHEN USED AS STANDALONE PLUGIN
 *  WE ALSO NEED TO FIRE THIS FILE WHEN AJAXING FROM THE CUSTOMIZER
/* ------------------------------------------------------------------------- */
add_action( 'after_setup_theme', 'ac_load_czr_base_fmk', 10 );
function ac_load_czr_base_fmk() {
    if ( isset( $GLOBALS['czr_base_fmk_namespace'] ) ) {
        //error_log('The czr_base_fmk has already been loaded');
        return;
    }
    require_once(  dirname( __FILE__ ) . '/inc/czr-base-fmk/czr-base-fmk.php' );
    \czr_fn\CZR_Fmk_Base( array(
       'base_url' => NIMBLE_BASE_URL . '/inc/czr-base-fmk',
       'version' => NIMBLE_VERSION
    ));
}

/* ------------------------------------------------------------------------- *
 *  LOADS SKOP
/* ------------------------------------------------------------------------- */
require_once( plugin_dir_path( __FILE__ ) . 'inc/czr-skope/index.php' );
add_action( 'after_setup_theme', function() {
    Flat_Skop_Base( array(
        'base_url_path' => NIMBLE_BASE_URL . '/inc/czr-skope'
    ) );
});

/* ------------------------------------------------------------------------- *
 *  LOADS SEKS
/* ------------------------------------------------------------------------- */
require_once( plugin_dir_path( __FILE__ ) . 'inc/sektions/ccat-sektions.php' );

/* ------------------------------------------------------------------------- *
 *  LOADS TESTS
/* ------------------------------------------------------------------------- */
if ( defined( 'CZR_DEV' ) && CZR_DEV && file_exists( plugin_dir_path( __FILE__ ) . 'tests.php' ) ) {
    require_once( plugin_dir_path( __FILE__ ) . 'tests.php' );
}

add_action('plugins_loaded', 'sek_load_plugin_textdomain');
/**
* Load language files
* @action plugins_loaded
*/
function sek_load_plugin_textdomain() {
  // Note to self, the third argument must not be hardcoded, to account for relocated folders.
  load_plugin_textdomain( 'nimble-builder' );
}
//error_log( 'get_stylesheet ' . get_stylesheet() );
// if ( 0 === strpos( get_stylesheet(), 'customizr' ) )
//   return;

// add_action('wp', function() {
//     error_log( '<////////////////////THEMEMODS>' );
//     error_log( print_r( get_theme_mods(), true ) );
//     error_log( '</////////////////////THEMEMODS>' );
// });