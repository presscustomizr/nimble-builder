<?php
namespace Nimble;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init' , '\Nimble\sek_redirect_nimblizer' );
function sek_redirect_nimblizer() {
    if ( isset( $_GET['nimblizer'] ) ) {
        set_current_screen();
        require_once( NIMBLE_BASE_PATH . '/inc/nimblizer/nimblizer.php' );
        exit;
    }
}


// Replace normal instanciation of WP customize manager  `new WP_Customize_Manager` ) 
// Activated if `is_admin() && 'customize.php' === basename( $_SERVER['PHP_SELF'] )` 
// with add_action( 'plugins_loaded', '_wp_customize_include' );
add_action( 'plugins_loaded', '\Nimble\_nb_nimblize_include');

/**
 * Includes and instantiates the WP_Customize_Manager class.
 *
 * Loads the Customizer at plugins_loaded when accessing the customize.php admin
 * page or when any request includes a wp_customize=on param or a customize_changeset
 * param (a UUID). This param is a signal for whether to bootstrap the Customizer when
 * WordPress is loading, especially in the Customizer preview
 * or when making Customizer Ajax requests for widgets or menus.
 *
 * @since 3.4.0
 *
 * @global WP_Customize_Manager $wp_customize
 */
function _nb_nimblize_include() {

	$is_customize_admin_page = ( is_admin() && isset( $_GET['nimblizer'] ) );
	$should_include          = (
		$is_customize_admin_page
		||
		( isset( $_REQUEST['wp_customize'] ) && 'on' === sanitize_text_field($_REQUEST['wp_customize']) )
		||
		( ! empty( $_GET['customize_changeset_uuid'] ) || ! empty( sanitize_text_field($_POST['customize_changeset_uuid'] )) )
	);

	if ( ! $should_include ) {
		return;
	}

	/*
	 * Note that wp_unslash() is not being used on the input vars because it is
	 * called before wp_magic_quotes() gets called. Besides this fact, none of
	 * the values should contain any characters needing slashes anyway.
	 */
	$keys       = array( 'changeset_uuid', 'customize_changeset_uuid', 'customize_theme', 'theme', 'customize_messenger_channel', 'customize_autosaved' );
	$input_vars = array_merge(
		wp_array_slice_assoc( $_GET, $keys ),
		wp_array_slice_assoc( $_POST, $keys )
	);

	$theme             = null;
	$autosaved         = null;
	$messenger_channel = null;

	// Value false indicates UUID should be determined after_setup_theme
	// to either re-use existing saved changeset or else generate a new UUID if none exists.
	$changeset_uuid = false;

	// Set initially fo false since defaults to true for back-compat;
	// can be overridden via the customize_changeset_branching filter.
	$branching = false;

	if ( $is_customize_admin_page && isset( $input_vars['changeset_uuid'] ) ) {
		$changeset_uuid = sanitize_key( $input_vars['changeset_uuid'] );
	} elseif ( ! empty( $input_vars['customize_changeset_uuid'] ) ) {
		$changeset_uuid = sanitize_key( $input_vars['customize_changeset_uuid'] );
	}

	// Note that theme will be sanitized via WP_Theme.
	if ( $is_customize_admin_page && isset( $input_vars['theme'] ) ) {
		$theme = $input_vars['theme'];
	} elseif ( isset( $input_vars['customize_theme'] ) ) {
		$theme = $input_vars['customize_theme'];
	}

	if ( ! empty( $input_vars['customize_autosaved'] ) ) {
		$autosaved = true;
	}

	if ( isset( $input_vars['customize_messenger_channel'] ) ) {
		$messenger_channel = sanitize_key( $input_vars['customize_messenger_channel'] );
	}

	/*
	 * Note that settings must be previewed even outside the customizer preview
	 * and also in the customizer pane itself. This is to enable loading an existing
	 * changeset into the customizer. Previewing the settings only has to be prevented
	 * here in the case of a customize_save action because this will cause WP to think
	 * there is nothing changed that needs to be saved.
	 */
	$is_customize_save_action = (
		wp_doing_ajax()
		&&
		isset( $_REQUEST['action'] )
		&&
		'customize_save' === wp_unslash( $_REQUEST['action'] )
	);
	$settings_previewed       = ! $is_customize_save_action;

	require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
	$GLOBALS['wp_customize'] = new \WP_Customize_Manager( compact( 'changeset_uuid', 'theme', 'messenger_channel', 'settings_previewed', 'autosaved', 'branching' ) );
}