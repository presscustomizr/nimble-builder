<?php
register_activation_hook( NIMBLE_PLUGIN_FILE, 'nimble_install' );
function nimble_install() {
    // Add Upgraded From Option
    $current_version = get_option( 'nimble_version' );
    if ( $current_version ) {
        update_option( 'nimble_version_upgraded_from', $current_version );
    }
    update_option( 'nimble_version', NIMBLE_VERSION );
    $started_with = get_option( 'nimble_started_with_version' );
    if ( empty( $started_with ) ) {
        update_option( 'nimble_started_with_version', $current_version );
    }
}