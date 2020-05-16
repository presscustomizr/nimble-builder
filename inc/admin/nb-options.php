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



function nb_options_page() {
  ?>
  <div class="wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <?php do_action('nimble-after-nb-free-options'); ?>
      <?php
        wp_nonce_field( 'nb-options-save', 'nb-options-nonce' );
        submit_button();
      ?>
  </div><!-- .wrap -->
  <?php
}


function nb_has_valid_nonce() {
    // If the field isn't even in the $_POST, then it's invalid.
    if ( ! isset( $_POST['nb-options-nonce'] ) ) { // Input var okay.
        return false;
    }

    $field  = wp_unslash( $_POST['nb-options-nonce'] );
    return wp_verify_nonce( $field, 'nb-options-save' );
}


?>