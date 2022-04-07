<?php
add_action( 'customize_register', '\Nimble\sek_catch_export_action', PHP_INT_MAX );
function sek_catch_export_action( $wp_customize ) {
    if ( current_user_can( 'customize' ) ) {
        if ( isset( $_REQUEST['sek_export_nonce'] ) ) {
            sek_maybe_export();
        }
    }
}

// fire from sek_catch_export_action() @hook 'customize_register'
function sek_maybe_export() {
    $nonce = 'save-customize_' . get_stylesheet();
    if ( !isset( $_REQUEST['sek_export_nonce'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing nonce.');
        return;
    }
    if ( !isset( $_REQUEST['skope_id']) || empty( $_REQUEST['skope_id'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or empty skope_id.');
        return;
    }
    if ( !isset( $_REQUEST['active_locations'] ) || empty( $_REQUEST['active_locations'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing active locations param.');
        return;
    }
    if ( !wp_verify_nonce( $_REQUEST['sek_export_nonce'], $nonce ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid none.');
        return;
    }
    if ( !is_user_logged_in() ) {
        sek_error_log( __FUNCTION__ . ' => user not logged in.');
        return;
    }
    if ( !current_user_can( 'customize' ) ) {
        sek_error_log( __FUNCTION__ . ' => missing customize capabilities.');
        return;
    }

    $seks_data = sek_get_skoped_seks( sanitize_text_field($_REQUEST['skope_id']) );

    //sek_error_log('EXPORT BEFORE FILTER ? ' . $_REQUEST['skope_id'] , $seks_data );
    // the filter 'nimble_pre_export' is used to :
    // replace image id by the absolute url
    // clean level ids and replace them with a placeholder string
    $seks_data = apply_filters( 'nimble_pre_export', $seks_data );

    // March 2021 : make sure text input are sanitized like in #544 #792
    //$seks_data = sek_sektion_collection_sanitize_cb( $seks_data );

    $theme_name = sanitize_title_with_dashes( get_stylesheet() );
    
    //sek_error_log('EXPORT AFTER FILTER ?', $seks_data );
    $export = array(
        'data' => $seks_data,
        'metas' => array(
            'skope_id' => sanitize_text_field($_REQUEST['skope_id']),
            'version' => NIMBLE_VERSION,
            // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
            'active_locations' => is_string( $_REQUEST['active_locations'] ) ? explode( ',', sanitize_text_field($_REQUEST['active_locations']) ) : array(),
            'date' => date("Y-m-d"),
            'theme' => $theme_name
        )
    );

    //sek_error_log('$export ?', $export );

    $skope_id = str_replace('skp__', '',  sanitize_text_field($_REQUEST['skope_id']) );
    $filename = $theme_name . '_' . $skope_id . '.nimblebuilder';

    // Set the download headers.
    header( 'Content-disposition: attachment; filename=' . $filename );
    header( 'Content-Type: application/octet-stream; charset=' . get_option( 'blog_charset' ) );

    echo wp_json_encode( $export );

    // Start the download.
    die();
}

// Ajax action before processing the export
// control that all required fields are there
// This is to avoid a white screen when generating the download window afterwards
add_action( 'wp_ajax_sek_pre_export_checks', '\Nimble\sek_ajax_pre_export_checks' );
function sek_ajax_pre_export_checks() {
    //sek_error_log('PRE EXPORT CHECKS ?', $_POST );
    $action = 'save-customize_' . get_stylesheet();
    if ( !check_ajax_referer( $action, 'nonce', false ) ) {
        wp_send_json_error( 'check_ajax_referer_failed' );
    }
    if ( !is_user_logged_in() ) {
        wp_send_json_error( 'user_unauthenticated' );
    }
    if ( !current_user_can( 'customize' ) ) {
        status_header( 403 );
        wp_send_json_error( 'customize_not_allowed' );
    } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
        status_header( 405 );
        wp_send_json_error( 'bad_ajax_method' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error( 'missing_skope_id' );
    }
    if ( !isset( $_POST['active_locations'] ) || empty( $_POST['active_locations'] ) ) {
        wp_send_json_error( 'no_active_locations_to_export' );
    }
    wp_send_json_success();
}






// EXPORT FILTER
add_filter( 'nimble_pre_export', '\Nimble\sek_parse_img_and_clean_id' );
function sek_parse_img_and_clean_id( $seks_data ) {
    $new_seks_data = array();
    foreach ( $seks_data as $key => $value ) {
        if ( is_array($value) ) {
            $new_seks_data[$key] = sek_parse_img_and_clean_id( $value );
        } else {
            switch( $key ) {
                case 'bg-image' :
                case 'img' :
                    if ( is_int( $value ) && (int)$value > 0 ) {
                        $value = '__img_url__' . wp_get_attachment_url((int)$value);
                    }
                break;
                case 'id' :
                    if ( is_string( $value ) && false !== strpos( $value, '__nimble__' ) ) {
                        $value = '__rep__me__';
                    }
                break;
            }
            $new_seks_data[$key] = $value;
        }
    }
    return $new_seks_data;
}






// fetch the content from a user imported file
add_action( 'wp_ajax_sek_get_manually_imported_file_content', '\Nimble\sek_ajax_get_manually_imported_file_content' );
function sek_ajax_get_manually_imported_file_content() {
    // sek_error_log(__FUNCTION__ . ' AJAX $_POST ?', $_POST );
    // sek_error_log(__FUNCTION__ . ' AJAX $_FILES ?', $_FILES );
    // sek_error_log(__FUNCTION__ . ' AJAX $_REQUEST ?', $_REQUEST );

    $action = 'save-customize_' . get_stylesheet();
    if ( !check_ajax_referer( $action, 'nonce', false ) ) {
        wp_send_json_error( 'check_ajax_referer_failed' );
    }
    if ( !is_user_logged_in() ) {
        wp_send_json_error( 'user_unauthenticated' );
    }
    if ( !current_user_can( 'customize' ) ) {
        status_header( 403 );
        wp_send_json_error( 'customize_not_allowed' );
    } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
        status_header( 405 );
        wp_send_json_error( 'bad_ajax_method' );
    }
    if ( !isset( $_FILES['file_candidate'] ) || empty( $_FILES['file_candidate'] ) ) {
        wp_send_json_error( 'missing_file_candidate' );
    }
    if ( !isset( $_POST['skope'] ) || empty( $_POST['skope'] ) ) {
        wp_send_json_error( 'missing_skope' );
    }

    // load WP upload if not done yet
    if ( !function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    // @see https://codex.wordpress.org/Function_Reference/wp_handle_upload
    // Important => always run unlink( $file['file'] ) before sending the json success or error
    // otherwise WP will write the file in the /wp-content folder
    $file = wp_handle_upload(
        $_FILES['file_candidate'],
        array(
            'test_form' => false,
            'test_type' => false,
            'mimes' => array(
                'text' => 'text/plain',
                //'nimblebuilder' => 'text/plain',
                'json' => 'application/json',
                'nimblebuilder' => 'application/json'
            )
        )
    );

    // Make sure we have an uploaded file.
    if ( isset( $file['error'] ) ) {
        unlink( $file['file'] );
        wp_send_json_error( 'import_file_error' );
        return;
    }
    if ( !file_exists( $file['file'] ) ) {
        unlink( $file['file'] );
        wp_send_json_error( 'import_file_do_not_exist' );
        return;
    }

    // Get the upload data.
    $raw = file_get_contents( $file['file'] );
    //$raw_unserialized_data = @unserialize( $raw );
    $raw_unserialized_data = json_decode( $raw, true );

    // VALIDATE IMPORTED CONTENT
    // data structure :
    // $raw_unserialized_data = array(
    //     'data' => $seks_data,
    //     'metas' => array(
    //         'skope_id' => $_REQUEST['skope_id'],
    //         'version' => NIMBLE_VERSION,
    //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
    //         'active_locations' => is_string( $_REQUEST['active_locations'] ) ? explode( ',', $_REQUEST['active_locations'] ) : array(),
    //         'date' => date("Y-m-d")
    //     )
    // );
    // check import structure
    if ( !is_array( $raw_unserialized_data ) || empty( $raw_unserialized_data['data']) || !is_array( $raw_unserialized_data['data'] ) || empty( $raw_unserialized_data['metas'] ) || !is_array( $raw_unserialized_data['metas'] ) ) {
        unlink( $file['file'] );
        wp_send_json_error(  'invalid_import_content' );
        return;
    }
    // check version
    // => current Nimble Version must be at least import version
    if ( !empty( $raw_unserialized_data['metas']['version'] ) && version_compare( NIMBLE_VERSION, $raw_unserialized_data['metas']['version'], '<' ) ) {
        unlink( $file['file'] );
        wp_send_json_error( 'nimble_builder_needs_update' );
        return;
    }

    $maybe_import_images = true;
    // in a pre-import-check context, we don't need to sniff and upload images
    if ( array_key_exists( 'pre_import_check', $_POST ) && true === sek_booleanize_checkbox_val( sanitize_text_field($_POST['pre_import_check']) ) ) {
        $maybe_import_images = false;
    }
    // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
    if ( array_key_exists( 'import_img', $_POST ) && false === sek_booleanize_checkbox_val( sanitize_text_field($_POST['import_img']) ) ) {
        $maybe_import_images = false;
    }

    // Make sure NB decodes encoded rich text before sending to the customizer
    // see #544 and #791
    $raw_unserialized_data['data'] = sek_sniff_and_decode_richtext( $raw_unserialized_data['data'] );

    $imported_content = array(
        //'data' => apply_filters( 'nimble_pre_import', $raw_unserialized_data['data'], $do_import_images ),
        'data' => sek_maybe_import_imgs( $raw_unserialized_data['data'], $maybe_import_images ),
        'metas' => $raw_unserialized_data['metas'],
        // the image import errors won't block the import
        // they are used when notifying user in the customizer
        'img_errors' => !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array()
    );

    // Remove the uploaded file
    // Important => always run unlink( $file['file'] ) before sending the json success or error
    // otherwise WP will write the file in the /wp-content folder
    unlink( $file['file'] );
    // Send
    wp_send_json_success( $imported_content );
}
?>