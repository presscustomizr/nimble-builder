<?php
// WP 5.0.0 compat. until the bug is fixed
// this hook fires before the customize changeset is inserter / updated in database
// Removing the wp_targeted_link_rel callback from the 'content_save_pre' filter prevents corrupting the changeset JSON
// more details in this ticket : https://core.trac.wordpress.org/ticket/45292
add_action( 'customize_save_validation_before', '\Nimble\sek_remove_callback_wp_targeted_link_rel' );
function sek_remove_callback_wp_targeted_link_rel( $wp_customize ) {
    if ( false !== has_filter( 'content_save_pre', 'wp_targeted_link_rel' ) ) {
        remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );
    }
};

?>