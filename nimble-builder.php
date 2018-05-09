<?php
/**
* Plugin Name: Advanced Customizer
* Plugin URI: https://presscustomizr.com
* Description: Enhanced Customizer for WordPress
* Version: 1.0.0-alpha
* Text Domain: advanced-customizer
* Author: Press Customizr
* Author URI: https://presscustomizr.com
* License: GPLv2 or later
*/

//error_log( 'get_stylesheet ' . get_stylesheet() );
// if ( 0 === strpos( get_stylesheet(), 'customizr' ) )
//   return;

/* ------------------------------------------------------------------------- *
 *  LOADS TESTS
/* ------------------------------------------------------------------------- */
require_once( plugin_dir_path( __FILE__ ) . 'tests.php' );

/* ------------------------------------------------------------------------- *
 *  CONSTANTS
/* ------------------------------------------------------------------------- */
if ( ! defined( 'PC_AC_IS_PLUGIN' ) ) { define( 'PC_AC_IS_PLUGIN' , ! did_action('plugins_loaded') ); }
if ( ! defined( 'PC_AC_VERSION' ) ) { define( 'PC_AC_VERSION' , '1.0.0' ); }
if ( ! defined( 'PC_AC_DIR_NAME' ) ) { define( 'PC_AC_DIR_NAME' , basename( dirname( __FILE__ ) ) ); }
if ( ! defined( 'PC_AC_BASE_URL' ) ) {
    //plugin context
    if ( ! ( defined( 'TC_BASE_URL' ) || defined( 'HU_BASE_URL' ) ) ) {
        // 2 cases:
        // a) in hueman pro addons
        // b) standalone
        //case a)
        if ( method_exists( 'HU_AD',  'ha_is_pro_addons' ) && HU_AD()->ha_is_pro_addons() ) {
            define( 'PC_AC_BASE_URL' , HA_BASE_URL . 'addons/' . PC_AC_DIR_NAME );
        }
        //case b)
        else {
            define( 'PC_AC_BASE_URL' , plugins_url( PC_AC_DIR_NAME ) );
        }
    } else { //addon context
        //a) in Customizr-PRO
        // if ( defined( 'TC_BASE_URL' ) ) {
        //     define( 'PC_AC_BASE_URL' , sprintf('%s/%s' , TC_BASE_URL . 'addons' , PC_AC_DIR_NAME ) );
        // }
        //b) in Hueman-PRO
        // else {
        //     define( 'PC_AC_BASE_URL' , sprintf('%s/%s' , HU_BASE_URL . 'addons/pro' , PC_AC_DIR_NAME ) );
        // }
        define( 'PC_AC_BASE_URL' , sprintf('%s/%s' , HU_BASE_URL . 'addons/pro' , PC_AC_DIR_NAME ) );
    }
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
    //if ( ac_is_customizing() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        // when is used a stand alone plugin, but not embedded in the hueman pro addons dev plugins
        if ( PC_AC_IS_PLUGIN && ! ( method_exists( 'HU_AD',  'ha_is_pro_addons' ) && HU_AD()->ha_is_pro_addons() ) ) {
            require_once(  dirname( __FILE__ ) . '/inc/czr-base-fmk/czr-base-fmk.php' );
            \czr_fn\CZR_Fmk_Base( array(
               'base_url' => PC_AC_BASE_URL . '/inc/czr-base-fmk',
               'version' => PC_AC_VERSION
            ));
        }
    //}
}



/* ------------------------------------------------------------------------- *
 *  LOADS SKOP
/* ------------------------------------------------------------------------- */
require_once( plugin_dir_path( __FILE__ ) . 'inc/czr-skope/index.php' );
// If in hueman-pro-addons or in hueman-pro theme
// add_action('hu_hueman_loaded', function() {
//     Flat_Skop_Base();
// });
add_action( 'after_setup_theme', function() {
    Flat_Skop_Base( array(
        'base_url_path' => PC_AC_BASE_URL . '/inc/czr-skope'
    ) );
});


/* ------------------------------------------------------------------------- *
 *  LOADS CONTEXTUALIZER
/* ------------------------------------------------------------------------- */
require_once( plugin_dir_path( __FILE__ ) . 'inc/contextualizer/ccat-contualizer.php' );
// If in hueman-pro-addons or in hueman-pro theme
// add_action('hu_hueman_loaded', function() {
//     Flat_Skop_Base();
// });
add_action('after_setup_theme', function() {
    Contx( array(
        'base_url_path' => PC_AC_BASE_URL . '/inc/contextualizer'
    ) );
}, 50 );

// add_action('wp', function() {
//     error_log( '<////////////////////THEMEMODS>' );
//     error_log( print_r( get_theme_mods(), true ) );
//     error_log( '</////////////////////THEMEMODS>' );
// });


/* ------------------------------------------------------------------------- *
 *  LOADS SOCIAL LINKS
/* ------------------------------------------------------------------------- */
add_action('after_setup_theme', 'ac_load_module_social_links', 50 );
function ac_load_module_social_links() {
    require_once( plugin_dir_path( __FILE__ ) . 'inc/czr-modules/social-links/social_links_module.php' );
    $pc_opt_test_options = get_option('pc_ac_opt_test');
    function_prefix_to_be_replaced_register_social_links_module(
        array(
            'setting_id' => 'pc_ac_opt_test[test_five]',

            'base_url_path' => PC_AC_BASE_URL . '/inc/czr-modules/social-links',
            'version' => PC_AC_VERSION,

            'option_value' => ( is_array( $pc_opt_test_options) && array_key_exists( 'test_five', $pc_opt_test_options ) ) ? $pc_opt_test_options['test_five'] : array(), // for dynamic registration

            'setting' => array(
                'type' => 'option',
                'default'  => array(),
                'transport' => 'refresh',
                'sanitize_callback' => '', //<= set in the module
                'validate_callback' => '' //<= set in the module
            ),

            'section' => array(
                'id' => 'social_links',
                'title' => __( 'Social links', 'text_domain_to_be_replaced' ),
                'panel' => '',
                'priority' => 10
            ),

            'control' => array(
                'priority' => 10,
                'label' => __( 'Create and organize your social links', 'text_domain_to_be_replaced' ),
                'type'  => 'czr_module',
            ),
        )
    );
}


/* ------------------------------------------------------------------------- *
 *  LOADS SEKTION BUILDER
/* ------------------------------------------------------------------------- */
require_once( plugin_dir_path( __FILE__ ) . 'inc/sektions/ccat-sektions.php' );