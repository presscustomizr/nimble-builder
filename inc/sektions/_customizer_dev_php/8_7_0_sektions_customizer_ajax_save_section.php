<?php
////////////////////////////////////////////////////////////////
// Fetches the user saved sections
add_action( 'wp_ajax_sek_get_all_saved_sections', '\Nimble\sek_ajax_get_all_saved_sections' );
// @hook wp_ajax_sek_get_user_saved_sections
function sek_ajax_get_all_saved_sections() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    $decoded_sections = sek_get_all_saved_sections();

    if ( is_array($decoded_sections) ) {
        wp_send_json_success( $decoded_sections );
    } else {
        if ( !empty( $decoded_sections ) ) {
            sek_error_log(  __FUNCTION__ . ' error => invalid sections returned', $decoded_sections );
            wp_send_json_error(  __FUNCTION__ . ' error => invalid sections returned' );
        }
    }
}



// Fetches the preset_sections
add_action( 'wp_ajax_sek_get_single_api_section_data', '\Nimble\sek_ajax_get_single_api_section_data' );
////////////////////////////////////////////////////////////////
// PRESET SECTIONS
// Fired in __construct()
// hook : 'wp_ajax_sek_get_preset_sektions'
function sek_ajax_get_single_api_section_data() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // September 2020 => force update every 24 hours so users won't miss a new pre-build section
    // Note that the refresh should have take place on 'upgrader_process_complete'
    // always force refresh when developing
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a api_section_id
    if ( empty( $_POST['api_section_id']) || !is_string( $_POST['api_section_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_api_section_id' );
    }
    $api_section_id = sanitize_text_field($_POST['api_section_id']);

    $is_pro_section_id = sek_is_pro() && is_string($api_section_id) && 'pro_' === substr($api_section_id,0,4);
    $pro_key_status = apply_filters( 'nimble_pro_key_status_OK', 'nok' );
    if ( $is_pro_section_id && 'pro_key_status_ok' !== $pro_key_status ) {
        wp_send_json_error( $pro_key_status );
        return;
    }
    $raw_api_sec_data = sek_api_get_single_section_data( $api_section_id );// <= returns an unserialized array

    // When injecting a pro section, NB checks the validity of the key.
    // if the api response is not an array, there was a problem when checking the key
    // and in this case the response is a string like : 'Expired.'
    if ( $is_pro_section_id && is_string($raw_api_sec_data) && !empty($raw_api_sec_data) ) {
        wp_send_json_error( $raw_api_sec_data );
        return;
    }

    if( !is_array( $raw_api_sec_data) || empty( $raw_api_sec_data ) ) {
        sek_error_log( __FUNCTION__ . ' problem when getting section : ' . $api_section_id );
        wp_send_json_error( 'Error : empty or invalid section data : '. $api_section_id );
        return;
    }
    //sek_error_log( __FUNCTION__ . ' api section data', $raw_api_sec_data );
    if ( !isset($raw_api_sec_data['collection'] ) || empty( $raw_api_sec_data['collection'] ) ) {
        sek_error_log( __FUNCTION__ . ' problem => missing or invalid data property for section : ' . $api_section_id, $raw_api_sec_data );
        wp_send_json_error( 'Error : missing_data_property_for_section : ' . $api_section_id );
    } else {
        // $tmpl_decoded = $raw_api_sec_data;
        $raw_api_sec_data['collection'] = sek_maybe_import_imgs( $raw_api_sec_data['collection'], $do_import_images = true );
        //$raw_api_sec_data['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
        // Make sure we decode encoded rich text before sending to the customizer
        // see #544 and #791
        $raw_api_sec_data['collection'] = sek_sniff_and_decode_richtext( $raw_api_sec_data['collection'] );

        wp_send_json_success( $raw_api_sec_data );
    }
}






////////////////////////////////////////////////////////////////
// SECTION GET CONTENT + METAS
// Fetches the json of a given user section
add_action( 'wp_ajax_sek_get_user_section_json', '\Nimble\sek_ajax_sek_get_user_section_json' );
// @hook wp_ajax_sek_get_user_saved_sections
function sek_ajax_sek_get_user_section_json() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a section_post_name
    if ( empty( $_POST['section_post_name']) || !is_string( $_POST['section_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_post_name' );
    }
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $section_post = sek_get_saved_section_post( sanitize_text_field($_POST['section_post_name']) );
    if ( !is_wp_error( $section_post ) && $section_post && is_object( $section_post ) ) {
        $section_decoded = maybe_unserialize( $section_post->post_content );
        // Structure of $content :
        // array(
        //     'data' => $_POST['section_data'],//<= json stringified
        //     'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? $_POST['section_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['section_title'],
        //         'description' => $_POST['section_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'active_locations' => is_string( $_POST['active_locations'] ) ? explode( ',', $_POST['active_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( is_array( $section_decoded ) && !empty( $section_decoded['data'] ) && is_string( $section_decoded['data'] ) ) {
            $section_decoded['data'] = json_decode( wp_unslash( $section_decoded['data'], true ) );
        }
        // Make sure we decode encoded rich text before sending to the customizer
        // see #544 and #791
        $section_decoded['data'] = sek_sniff_and_decode_richtext( $section_decoded['data'] );
        wp_send_json_success( $section_decoded );
    } else {
        wp_send_json_error( __FUNCTION__ . '_section_post_not_found' );
    }
}






////////////////////////////////////////////////////////////////
// SECTION SAVE
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_SECTION_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_save_user_section', '\Nimble\sek_ajax_save_user_section' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_save_user_section
function sek_ajax_save_user_section() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $is_edit_metas_only_case = isset( $_POST['edit_metas_only'] ) && 'yes' === sanitize_text_field($_POST['edit_metas_only']);
    // TMPL DATA => the nimble content
    if ( !$is_edit_metas_only_case && empty( $_POST['section_data']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_data' );
    }
    if ( !$is_edit_metas_only_case && !is_string( $_POST['section_data'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_section_data_must_be_a_json_stringified' );
    }

    // TMPL METAS
    // We must have a title
    if ( empty( $_POST['section_title']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_title' );
    }
    if ( !is_string( $_POST['section_description'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_section_description_must_be_a_string' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    }
    // if ( !isset( $_POST['active_locations'] ) || empty( $_POST['active_locations'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_active_locations' );
    // }

    if ( $is_edit_metas_only_case ) {
        $seks_data = [];
    } else {
        // clean level ids and replace them with a placeholder string
        $seks_data = json_decode( wp_unslash( $_POST['section_data'] ), true );
        $seks_data = sek_section_save_clean_id( $seks_data );
    }

    // make sure description and title are clean before DB
    $sec_title = sek_maybe_encode_richtext( sanitize_text_field($_POST['section_title']) );
    $sec_description = sek_maybe_encode_richtext( sanitize_text_field($_POST['section_description']) );

    $section_to_save = array(
        'data' => $seks_data,//<= json stringified
        // the section post name is provided only when updating
        'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? sanitize_text_field($_POST['section_post_name']) : null,
        'metas' => array(
            'title' => $sec_title,
            'description' => $sec_description,
            'skope_id' => sanitize_text_field($_POST['skope_id']),
            'version' => NIMBLE_VERSION,
            // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
            //'active_locations' => is_array( $_POST['active_locations'] ) ? $_POST['active_locations'] : array(),
            'date' => date("Y-m-d"),
            'theme' => sanitize_title_with_dashes( get_stylesheet() )
        )
    );

    $saved_section_post = sek_update_saved_section_post( $section_to_save, $is_edit_metas_only_case );
    if ( is_wp_error( $saved_section_post ) || is_null($saved_section_post) || empty($saved_section_post) ) {
        wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_saved_section_post()' );
    } else {
        wp_send_json_success( [ 'section_post_id' => $saved_section_post->ID ] );
    }
}


// SAVE FILTER
function sek_section_save_clean_id( $seks_data = array() ) {
    $new_seks_data = array();
    if ( !is_array( $seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' error => seks_data should be an array');
        return array();
    }

    foreach ( $seks_data as $key => $value ) {
        if ( is_array($value) ) {
            $new_seks_data[$key] = sek_section_save_clean_id( $value );
        } else {
            switch( $key ) {
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


////////////////////////////////////////////////////////////////
// SECTION REMOVE
// introduced in may 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_SECTION_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_remove_user_section', '\Nimble\sek_ajax_remove_user_section' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_remove_user_section
function sek_ajax_remove_user_section() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a section_post_name
    if ( empty( $_POST['section_post_name']) || !is_string( $_POST['section_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_post_name' );
    }
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $section_post_to_remove = sek_get_saved_section_post( sanitize_text_field($_POST['section_post_name']) );

    if ( $section_post_to_remove && is_object( $section_post_to_remove ) ) {
        // the CPT is moved to Trash instead of permanently deleted when using wp_delete_post()
        $r = wp_trash_post( $section_post_to_remove->ID );
        if ( is_wp_error( $r ) ) {
            wp_send_json_error( __FUNCTION__ . '_removal_error' );
        }
    } else {
        wp_send_json_error( __FUNCTION__ . '_section_post_not_found' );
    }

    if ( is_wp_error( $section_post_to_remove ) || is_null($section_post_to_remove) || empty($section_post_to_remove) ) {
        wp_send_json_error( __FUNCTION__ . '_removal_error' );
    } else {
        wp_send_json_success( [ 'section_post_removed' => sanitize_text_field($_POST['section_post_name']) ] );
    }
}
?>