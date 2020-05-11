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
    'nb-options',
    '\Nimble\nb_options_page'
  );
}
add_action( 'admin_menu', '\Nimble\nb_register_options_page');



function nb_options_page() {
  ?>
  <div class="wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
        <div id="universal-message-container">
            <h1><?php echo '🕵' . __('Decide which users can use Nimble Builder on this website', 'text-domain'); ?></h1>
            <strong><?php _e('Nimble Builder by default allows all users with "manage_options" capability (administrator role) to build pages. Use the fields below to authorize or unauthorize building to a selection of users.', 'text-domain'); ?></strong><br/>
            <div class="options">
                <h2><?php _e('Authorize building with Nimble Builder only for the following users.', 'text-domain'); ?></h2>
                <p><?php _e('Only administrator users listed here will be allowed. All admin users are allowed if left empty.', 'text-domain'); ?></p>
                <p>
                    <?php
                      $auth_user_ids = get_option('nb-authorized-user');
                      $html = '';
                      if ( is_array($auth_user_ids) ) {
                        foreach ($auth_user_ids as $id) {
                          $user_data = get_userdata($id);
                          if ($user_data && is_object($user_data) && isset($user_data->user_email) ) {
                              $html .= $user_data->user_email . "\n";
                          }
                        }
                      }
                    ?>
                    <textarea type="text" name="nb-auth-user-email" style="min-width:50%;min-height:150px"><?php echo $html; ?></textarea>
                    <br/>
                    <label><i><?php _e('Enter one user email per line and hit save.', 'text-domain'); ?></i></label>
                </p>
                </br>
                <hr/>
                </br>
                <h2><?php echo '🔴 ' . __('Unauthorize building with Nimble Builder for the following users.', 'text-domain'); ?></h2>
                <p><?php _e('Users listed here will not be allowed.', 'text-domain'); ?></p>
                <p>
                    <?php
                      $unauth_user_ids = get_option('nb-unauthorized-user');
                      $html = '';
                      if ( is_array($unauth_user_ids) ) {
                        foreach ($unauth_user_ids as $id) {
                          $user_data = get_userdata($id);
                          if ($user_data && is_object($user_data) && isset($user_data->user_email) ) {
                              $html .= $user_data->user_email . "\n";
                          }
                        }
                      }
                    ?>
                    <textarea type="text" name="nb-unauth-user-email" style="min-width:50%;min-height:150px"><?php echo $html; ?></textarea>
                    <br/>
                    <label><i><?php _e('Enter one user email per line and hit save.', 'text-domain'); ?></i></label>
                </p>
        </div><!-- #universal-message-container -->
        <?php
            wp_nonce_field( 'nb-options-save', 'nb-options-nonce' );
            submit_button();
        ?>
      </form>
  </div><!-- .wrap -->
  <?php
}



function nb_save_options() {
  // First, validate the nonce and verify the user as permission to save.
  if ( ! ( nb_has_valid_nonce() && current_user_can( 'manage_options' ) ) ) {
      // TODO: Display an error message.
  }
  $current_user_id = get_current_user_id();
  $errors = array( 'wrong_email' => array(), 'not_admin' => array(), 'not_registered' => array() );
  $auth_candidates = array();
  $unauth_candidates = array();
  // AUTHORIZED
  if ( null !== wp_unslash( $_POST['nb-auth-user-email'] ) ) {
      $raw_list_of_auth = nb_extract_emails_from_user_input( $_POST['nb-auth-user-email'] );
      if ( is_array( $raw_list_of_auth ) ) {
        foreach ($raw_list_of_auth as $email) {
            $sanit_email = sanitize_email( $email );
            if( !is_email( $sanit_email ) ) {
                if ( !empty( $sanit_email ) ) {
                    $errors['wrong_email'][] = $email;
                }
            } else {
                $user = get_user_by( 'email', $sanit_email );
                $user_exists = $user && is_object($user) && isset( $user->ID );
                if ( !$user_exists ) {
                    $errors['not_registered'][] = $sanit_email;
                } else if ( $current_user_id !== $user->ID ) {
                    if ( !user_can( $user->ID, 'manage_options' ) ) {
                        $errors['not_admin'][] = $sanit_email;
                    } else {
                        $auth_candidates[] = $user->ID;
                    }
                }
            }
        }
      }

      // if a list of authorized users has been set, prevent the current admin user to self-disallow by adding it
      if ( !empty( $auth_candidates ) && !in_array($current_user_id, $auth_candidates) ) {
          array_unshift( $auth_candidates, $current_user_id );
      }
      update_option( 'nb-authorized-user', empty($auth_candidates) ? null : $auth_candidates );
  }

  // UNAUTHORIZED
  if ( null !== wp_unslash( $_POST['nb-unauth-user-email'] ) ) {
      $raw_list_of_unauth = nb_extract_emails_from_user_input( $_POST['nb-unauth-user-email'] );
      if ( is_array( $raw_list_of_unauth ) ) {
        foreach ($raw_list_of_unauth as $email) {
            $sanit_email = sanitize_email( $email );
            if( !is_email( $sanit_email ) ) {
                if ( !empty( $email ) ) {
                    $errors['wrong_email'][] = $email;
                }
            } else {
                $user = get_user_by( 'email', $sanit_email );
                $user_exists = $user && is_object($user) && isset( $user->ID );
                if ( !$user_exists ) {
                    $errors['not_registered'][] = $sanit_email;
                }
                // prevent the current admin user to self-disallow
                // prevent a user to be simultaneously allowed and disallowed
                else if ( $current_user_id !== $user->ID && !in_array( $user->ID, $auth_candidates ) ) {
                    $unauth_candidates[] = $user->ID;
                } else if ( $current_user_id === $user->ID) {
                    $errors['self_disallow_forbidden'][] = $sanit_email;
                }
            }
        }
      }

      update_option( 'nb-unauthorized-user', empty($unauth_candidates) ? null : $unauth_candidates );
  }


  nb_admin_redirect( array( 'errors' => $errors ) );
}

add_action( 'admin_post', '\Nimble\nb_save_options' );



function nb_admin_redirect( $args ) {
    // To make the Coding Standards happy, we have to initialize this.
    // if ( ! isset( $_POST['_wp_http_referer'] ) ) { // Input var okay.
    //     $_POST['_wp_http_referer'] = wp_login_url();
    // }

    // Sanitize the value of the $_POST collection for the Coding Standards.
    // $url = sanitize_text_field(
    //     wp_unslash( $_POST['_wp_http_referer'] ) // Input var okay.
    // );
    $query_args = array( 'success' => 'yes' );
    //$errors = array( 'wrong_email' => array(), 'not_admin' => array(), 'not_registered' => array(), 'self_disallow_forbidden' => array() );
    foreach ($args['errors'] as $k => $emails) {
        if ( !empty($emails) ) {
            $query_args['success'] = 'no';
            $query_args[$k] = implode(',', $emails);
        }
    }
    $url = add_query_arg(
        $query_args,
        admin_url('options-general.php?page=nb-options')
    );
    // Finally, redirect back to the admin page.
    wp_safe_redirect( urldecode( $url ) );
    exit;
}


function nb_options_admin_message() {
  if ( !isset( $_GET['page'] ) || 'nb-options' !== $_GET['page'] )
    return;
  if ( !isset($_GET['success'] ) )
    return;

  if ( 'yes' === $_GET['success'] ) {
    ?>
    <div class="notice notice-success is-dismissible">
      <p><strong><?php _e('Settings saved', 'text-domain'); ?></strong></p>
    </div>
    <?php
  }
  //$errors = array( 'wrong_email' => array(), 'not_admin' => array(), 'not_registered' => array(), 'self_disallow_forbidden' => array() );
  if ( 'no' === $_GET['success'] ) {
    ?>
    <div class="notice notice-warning is-dismissible">
      <p><strong><?php _e('Some users could not be allowed/disallowed :', 'text-domain'); ?></strong></p>

        <?php
          $html = '';
          if ( isset($_GET['wrong_email'] ) ) {
              $html .= sprintf('<li>- ⚠ <strong>%1$s</strong> : %2$s</li>', __('Invalid email(s)', 'text-domain'), $_GET['wrong_email'] );
          }
          if ( isset($_GET['not_admin'] ) ) {
              $html .= sprintf('<li>- ⚠ <strong>%1$s</strong> : %2$s</li>', __('User(s) miss "manage_options" capability', 'text-domain'), $_GET['not_admin'] );
          }
          if ( isset($_GET['not_registered'] ) ) {
              $html .= sprintf('<li>- ⚠ <strong>%1$s</strong> : %2$s</li>', __('User(s) not registered', 'text-domain'), $_GET['not_registered'] );
          }
          if ( isset($_GET['self_disallow_forbidden'] ) ) {
              $html .= sprintf('<li>- ⚠ <strong>%1$s</strong> : %2$s</li>', __('You cannot disallow yourself', 'text-domain'), $_GET['self_disallow_forbidden'] );
          }
          if ( !empty($html) ) {
            printf('<ul>%1$s</ul>', $html);
          }
        ?>
    </div>
    <?php
  }
}
add_action('admin_notices', '\Nimble\nb_options_admin_message');


//@return array()
function nb_extract_emails_from_user_input( $raw_string ) {
    if ( !is_string($raw_string) )
      return array();
    $email_list = array();
    $line_break_array = preg_split ('/$\R?^/m', $raw_string );
    foreach ($line_break_array as $maybe_comma_list ) {
        if ( empty($maybe_comma_list) )
          continue;
        $exploded = explode( ',', $maybe_comma_list );
        if ( count($exploded) > 1 ) {
          foreach ($exploded as $email) {
            $email_list[] = trim($email);
          }
        } else {
          $email_list[] = trim($maybe_comma_list);
        }
    }
    return array_unique( $email_list );
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