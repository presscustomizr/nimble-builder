<?php
add_action( 'customize_register', '\Nimble\sek_catch_import_export_action', PHP_INT_MAX );
function sek_catch_import_export_action( $wp_customize ) {
    if ( current_user_can( 'edit_theme_options' ) ) {
        if ( isset( $_REQUEST['sek_export_nonce'] ) ) {
            sek_maybe_export();
        }
        // if ( isset( $_FILES['sek-import-file'] ) ) {
        //     sek_maybe_import();
        // }
    }
}

function sek_maybe_export() {
    $nonce = 'save-customize_' . get_stylesheet();
    if ( ! isset( $_REQUEST['sek_export_nonce'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing nonce.');
        return;
    }
    if ( !isset( $_REQUEST['skope_id']) || empty( $_REQUEST['skope_id'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or empty skope_id.');
        return;
    }
    if ( ! wp_verify_nonce( $_REQUEST['sek_export_nonce'], $nonce ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid none.');
        return;
    }
    if ( ! is_user_logged_in() ) {
        sek_error_log( __FUNCTION__ . ' => user not logged in.');
        return;
    }
    if ( ! current_user_can( 'customize' ) ) {
        sek_error_log( __FUNCTION__ . ' => missing customize capabilities.');
        return;
    }

    $export = sek_get_skoped_seks( $_REQUEST['skope_id'] );

    // Set the download headers.
    header( 'Content-disposition: attachment; filename=nimble-' . $_REQUEST['skope_id'] . '-export.txt' );
    header( 'Content-Type: application/octet-stream; charset=' . get_option( 'blog_charset' ) );

    // Serialize the export data.
    echo serialize( $export );

    // Start the download.
    die();
}
// fetch the content from a user imported file
add_action( 'wp_ajax_sek_get_imported_file_content', '\Nimble\sek_ajax_get_imported_file_content' );
function sek_ajax_get_imported_file_content() {
    sek_error_log('AJAX $_POST ?', $_POST );
    sek_error_log('AJAX $_FILES ?', $_FILES );
    sek_error_log('AJAX $_REQUEST ?', $_REQUEST );
    $action = 'save-customize_' . get_stylesheet();
    // if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
    //      wp_send_json_error( array(
    //         'code' => 'invalid_nonce',
    //         'message' => __( __FUNCTION__ . ' check_ajax_referer() failed.' ),
    //     ) );
    // }
    // if ( ! is_user_logged_in() ) {
    //     wp_send_json_error( __FUNCTION__ . ' => unauthenticated' );
    // }
    // if ( ! current_user_can( 'edit_theme_options' ) ) {
    //   wp_send_json_error( __FUNCTION__ . ' => user_cant_edit_theme_options');
    // }
    // if ( ! current_user_can( 'customize' ) ) {
    //     status_header( 403 );
    //     wp_send_json_error( __FUNCTION__ . ' => customize_not_allowed' );
    // } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
    //     status_header( 405 );
    //     wp_send_json_error( __FUNCTION__ . ' => bad_method' );
    // }
    // if ( ! isset( $_POST['file_candidate'] ) || empty( $_POST['file_candidate'] ) ) {
    //     wp_send_json_error(  __FUNCTION__ . ' => missing import file candidate' );
    // }
    // if ( ! isset( $_POST['skope'] ) || empty( $_POST['skope'] ) ) {
    //     wp_send_json_error(  __FUNCTION__ . ' => missing skope' );
    // }

    // load WP upload if not done yet
    if ( ! function_exists( 'wp_handle_upload' ) ) {
      require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    $overrides   = array( 'test_form' => false, 'test_type' => false, 'mimes' => array('text' => 'text/plain') );
    $file = wp_handle_upload( $_FILES['file_candidate'], $overrides );

    // Make sure we have an uploaded file.
    if ( isset( $file['error'] ) ) {
      $cei_error = $file['error'];
      return;
    }
    if ( ! file_exists( $file['file'] ) ) {
      $cei_error = __( 'Error importing your Nimble Builder file! Please try again.', 'text_doma' );
      return;
    }

    // Get the upload data.
    $raw  = file_get_contents( $file['file'] );
    $unserialized_data = @unserialize( $raw );

    // Remove the uploaded file.
    unlink( $file['file'] );
    wp_send_json_success( $unserialized_data );
}
?>