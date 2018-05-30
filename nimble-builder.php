<?php
/**
* Plugin Name: Nimble Builder
* Plugin URI: https://presscustomizr.com
* Description: Live Page Builder for the WordPress Customizer
* Version: 1.0.0-beta
* Text Domain: nimble-builder
* Author: Press Customizr
* Author URI: https://presscustomizr.com
* License: GPLv2 or later
*/

/* ------------------------------------------------------------------------- *
 *  CONSTANTS
/* ------------------------------------------------------------------------- */
if ( ! defined( 'NIMBLE_VERSION' ) ) { define( 'NIMBLE_VERSION' , '1.0.0-beta' ); }
if ( ! defined( 'NIMBLE_ASSETS_VERSION' ) ) {
    define( 'NIMBLE_ASSETS_VERSION', ( ( defined( 'CZR_DEV' ) && CZR_DEV ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) ? time() : NIMBLE_VERSION );
}
if ( ! defined( 'NIMBLE_DIR_NAME' ) ) { define( 'NIMBLE_DIR_NAME' , basename( dirname( __FILE__ ) ) ); }
if ( ! defined( 'NIMBLE_BASE_URL' ) ) { define( 'NIMBLE_BASE_URL' , plugins_url( NIMBLE_DIR_NAME ) ); }
if ( ! defined( 'NIMBLE_BASE_PATH' ) ) { define( 'NIMBLE_BASE_PATH' , dirname( __FILE__ ) ); }

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

//error_log( 'get_stylesheet ' . get_stylesheet() );
// if ( 0 === strpos( get_stylesheet(), 'customizr' ) )
//   return;

// add_action('wp', function() {
//     error_log( '<////////////////////THEMEMODS>' );
//     error_log( print_r( get_theme_mods(), true ) );
//     error_log( '</////////////////////THEMEMODS>' );
// });