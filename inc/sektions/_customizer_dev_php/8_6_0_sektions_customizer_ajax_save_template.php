<?php
////////////////////////////////////////////////////////////////
// Fetches the user saved templates
add_action( 'wp_ajax_sek_get_all_saved_tmpl', '\Nimble\sek_ajax_get_all_saved_templates' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_get_all_saved_templates() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    $decoded_templates = sek_get_all_saved_templates();

    if ( is_array($decoded_templates) ) {
        wp_send_json_success( $decoded_templates );
    } else {
        if ( !empty( $decoded_templates ) ) {
            sek_error_log(  __FUNCTION__ . ' error => invalid templates returned', $decoded_templates );
            wp_send_json_error(  __FUNCTION__ . ' error => invalid templates returned' );
        }
    }
}

////////////////////////////////////////////////////////////////
// TEMPLATE GET CONTENT + METAS
// Fetches the json of a given user template
add_action( 'wp_ajax_sek_get_user_tmpl_json', '\Nimble\sek_ajax_sek_get_user_tmpl_json' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_sek_get_user_tmpl_json() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a tmpl_post_name
    if ( empty( $_POST['tmpl_post_name']) || !is_string( $_POST['tmpl_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_post_name' );
    }
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $tmpl_post = sek_get_saved_tmpl_post( $_POST['tmpl_post_name'] );
    if ( !is_wp_error( $tmpl_post ) && $tmpl_post && is_object( $tmpl_post ) ) {
        $tmpl_decoded = maybe_unserialize( $tmpl_post->post_content );
        // Structure of $content :
        // array(
        //     'data' => $_POST['tmpl_data'],//<= json stringified
        //     'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['tmpl_title'],
        //         'description' => $_POST['tmpl_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'active_locations' => is_string( $_POST['active_locations'] ) ? explode( ',', $_POST['active_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( is_array( $tmpl_decoded ) && !empty( $tmpl_decoded['data'] ) && is_string( $tmpl_decoded['data'] ) ) {
            $tmpl_decoded['data'] = json_decode( wp_unslash( $tmpl_decoded['data'], true ) );
        }
        wp_send_json_success( $tmpl_decoded );
    } else {
        wp_send_json_error( __FUNCTION__ . '_tmpl_post_not_found' );
    }
}

////////////////////////////////////////////////////////////////
// TEMPLATE SAVE
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_TEMPLATE_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_save_user_template', '\Nimble\sek_ajax_save_user_template' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_save_user_template
function sek_ajax_save_user_template() {
    //sek_error_log( __FUNCTION__ . ' ALORS YEAH ? ?', $_POST );

    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    // TMPL DATA => the nimble content
    if ( empty( $_POST['tmpl_data']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_template_data' );
    }
    if ( !is_string( $_POST['tmpl_data'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_template_data_must_be_a_json_stringified' );
    }

    // TMPL METAS
    // We must have a title
    if ( empty( $_POST['tmpl_title']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_template_title' );
    }
    if ( !is_string( $_POST['tmpl_description'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_template_description_must_be_a_string' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    }
    if ( !isset( $_POST['active_locations'] ) || empty( $_POST['active_locations'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_active_locations' );
    }

    // make sure description and title are clean before DB
    $tmpl_title = wp_strip_all_tags( $_POST['tmpl_title'] );
    $tmpl_description = wp_strip_all_tags( $_POST['tmpl_description'] );

    //sek_error_log(__FUNCTION__ .  '$_POST?', $_POST );

    // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
    $template_to_save = array(
        'data' => $_POST['tmpl_data'],//<= json stringified
        'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
        'metas' => array(
            'title' => $tmpl_title,
            'description' => $tmpl_description,
            'skope_id' => $_POST['skope_id'],
            'version' => NIMBLE_VERSION,
            // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
            'active_locations' => is_array( $_POST['active_locations'] ) ? $_POST['active_locations'] : array(),
            'date' => date("Y-m-d"),
            'theme' => sanitize_title_with_dashes( get_stylesheet() )
        )
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

////////////////////////////////////////////////////////////////
// TEMPLATE REMOVE
// introduced in may 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_TEMPLATE_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_remove_user_template', '\Nimble\sek_ajax_remove_user_template' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_remove_user_template
function sek_ajax_remove_user_template() {
    //sek_error_log( __FUNCTION__ . ' ALORS YEAH IN REMOVAL ? ?', $_POST );

    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a tmpl_post_name
    if ( empty( $_POST['tmpl_post_name']) || !is_string( $_POST['tmpl_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_post_name' );
    }
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $tmpl_post_to_remove = sek_get_saved_tmpl_post( $_POST['tmpl_post_name'] );

    sek_error_log( __FUNCTION__ . ' => so $tmpl_post_to_remove ' . $_POST['tmpl_post_name'], $tmpl_post_to_remove );

    if ( $tmpl_post_to_remove && is_object( $tmpl_post_to_remove ) ) {
        $r = wp_delete_post( $tmpl_post_to_remove->ID, true );
        if ( is_wp_error( $r ) ) {
            wp_send_json_error( __FUNCTION__ . '_removal_error' );
        }
    } else {
        wp_send_json_error( __FUNCTION__ . '_tmpl_post_not_found' );
    }

    if ( is_wp_error( $tmpl_post_to_remove ) || is_null($tmpl_post_to_remove) || empty($tmpl_post_to_remove) ) {
        wp_send_json_error( __FUNCTION__ . '_removal_error' );
    } else {
        // sek_error_log( 'ALORS CE POST?', $saved_template_post );
        wp_send_json_success( [ 'tmpl_post_removed' => $_POST['tmpl_post_name'] ] );
    }
    //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
}

?>