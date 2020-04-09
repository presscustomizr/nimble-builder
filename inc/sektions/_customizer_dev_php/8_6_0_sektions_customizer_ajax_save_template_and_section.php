<?php
////////////////////////////////////////////////////////////////
// TEMPLATE SAVE
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_TEMPLATE_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_save_user_template', '\Nimble\sek_ajax_save_user_template' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_save_section
function sek_ajax_save_user_template() {
    sek_error_log( __FUNCTION__ . ' ALORS YEAH ? ?', $_POST );

    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a title and a section_id and sektion data
    if ( empty( $_POST['tmpl_title']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_template_title' );
    }
    // if ( ! isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    if ( empty( $_POST['tmpl_data']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_template_data' );
    }
    if ( ! is_string( $_POST['tmpl_data'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_template_data_must_be_a_json_stringified' );
    }
    if ( ! is_string( $_POST['tmpl_description'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_template_description_must_be_a_string' );
    }
    // sek_error_log('SEKS DATA ?', $_POST['sek_data'] );
    // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
    $template_to_save = array(
        'title' => $_POST['tmpl_title'],
        'description' => $_POST['tmpl_description'],
        'data' => $_POST['tmpl_data']//<= json stringified
    );

    $saved_template_post = sek_update_saved_tmpl_post( $template_to_save );
    if ( is_wp_error( $saved_template_post ) || is_null($saved_template_post) || empty($saved_template_post) ) {
        wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_saved_tmpl_post()' );
    } else {
        // sek_error_log( 'ALORS CE POST?', $saved_template_post );
        wp_send_json_success( [ 'tmpl_post_id' => $saved_template_post->ID ] );
    }
    //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
}















// APRIL 2020 DISABLED, waiting to be implemented
////////////////////////////////////////////////////////////////
// SECTION SAVE
// ENABLED WHEN CONSTANT NIMBLE_SAVED_SECTIONS_ENABLED === true
// Writes the saved section in a CPT + update the saved section option
add_action( 'wp_ajax_sek_save_section', '\Nimble\sek_ajax_save_section' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_save_section
function sek_ajax_save_section() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a title and a section_id and sektion data
    if ( empty( $_POST['sek_title']) ) {
        wp_send_json_error( __FUNCTION__ . ' => missing title' );
    }
    if ( empty( $_POST['sek_id']) ) {
        wp_send_json_error( __FUNCTION__ . ' => missing sektion_id' );
    }
    if ( empty( $_POST['sek_data']) ) {
        wp_send_json_error( __FUNCTION__ . ' => missing sektion data' );
    }
    if ( ! is_string( $_POST['sek_data'] ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the sektion data must be a json stringified' );
    }
    // sek_error_log('SEKS DATA ?', $_POST['sek_data'] );
    // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
    $sektion_to_save = array(
        'title' => $_POST['sek_title'],
        'description' => $_POST['sek_description'],
        'id' => $_POST['sek_id'],
        'type' => 'content',//in the future will be used to differentiate header, content and footer sections
        'creation_date' => date("Y-m-d H:i:s"),
        'update_date' => '',
        'data' => $_POST['sek_data']//<= json stringified
    );

    $saved_section_post = sek_update_saved_seks_post( $sektion_to_save );
    if ( is_wp_error( $saved_section_post ) ) {
        wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_saved_seks_post()' );
    } else {
        // sek_error_log( 'ALORS CE POST?', $saved_section_post );
        wp_send_json_success( [ 'section_post_id' => $saved_section_post->ID ] );
    }

    //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
}

// Fetches the user_saved sections
add_action( 'wp_ajax_sek_get_user_saved_sections', '\Nimble\sek_sek_get_user_saved_sections' );
// @hook wp_ajax_sek_sek_get_user_saved_sections
function sek_sek_get_user_saved_sections() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a section_id provided
    if ( empty( $_POST['preset_section_id']) || ! is_string( $_POST['preset_section_id'] ) ) {
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing or invalid preset_section_id' );
    }
    $section_id = $_POST['preset_section_id'];

    $section_data_decoded_from_custom_post_type = sek_get_saved_sektion_data( $section_id );
    if ( ! empty( $section_data_decoded_from_custom_post_type ) ) {
        wp_send_json_success( $section_data_decoded_from_custom_post_type );
    } else {
        $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
        if ( ! is_array( $all_saved_seks ) || empty( $all_saved_seks[$section_id]) ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing section data in get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS )' );
        }
        // $section_infos is an array
        // Array
        // (
        //     [post_id] => 399
        //     [title] => My section one
        //     [description] =>
        //     [creation_date] => 2018-10-29 13:52:54
        //     [type] => content
        // )
        $section_infos = $all_saved_seks[$section_id];
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing post data for section title ' . $section_infos['title'] );
    }
}

?>