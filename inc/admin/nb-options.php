<?php
namespace Nimble;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


// function nb_register_settings() {
//    add_option( 'myplugin_option_name', 'This is my option value.');
//    register_setting( 'myplugin_options_group', 'myplugin_option_name', '\Nimble\myplugin_callback' );
// }
// add_action( 'admin_init', '\Nimble\nb_register_settings' );

function nb_register_options_page() {
  if ( !sek_current_user_can_access_nb_ui() )
    return;
  add_options_page(
    __('Nimble Builder', 'text-domain'),
    __('Nimble Builder', 'text-domain'),
    'manage_options',
    NIMBLE_OPTIONS_PAGE,
    '\Nimble\nb_options_page'
  );
}
add_action( 'admin_menu', '\Nimble\nb_register_options_page');

// callback of add_options_page()
// fired @'admin_menu'
function nb_options_page() {
  ?>
  <div class="wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <?php do_action('nimble-after-nb-free-options'); ?>
  </div><!-- .wrap -->
  <?php
}

// fired @'admin_post'
function nb_save_options() {
  do_action('nb_admin_post');
  //wp_safe_redirect( urldecode( admin_url( NIMBLE_OPTIONS_PAGE_URL ) ) );
  nb_admin_redirect();
}
add_action( 'admin_post', '\Nimble\nb_save_options' );


// fired @'admin_post'
function nb_admin_redirect() {
    // Finally, redirect back to the admin page.
    // Note : filter 'nimble_admin_redirect_url' is used in NB pro to add query params used to display warning/error messages
    wp_safe_redirect( apply_filters('nimble_admin_redirect_url', urldecode( admin_url( NIMBLE_OPTIONS_PAGE_URL ) ) ) );
    exit;
}

// @return bool
function nb_has_valid_nonce( $option_group = 'nb-options-save', $nonce = 'nb-options-nonce' ) {
    // If the field isn't even in the $_POST, then it's invalid.
    if ( !isset( $_POST[$nonce] ) ) { // Input var okay.
        return false;
    }
    return wp_verify_nonce( wp_unslash( $_POST[$nonce] ), $option_group );
}


?>