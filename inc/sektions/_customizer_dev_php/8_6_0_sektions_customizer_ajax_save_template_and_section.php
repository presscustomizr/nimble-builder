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


    //sek_error_log(__FUNCTION__ .  '$_POST?', $_POST );

    // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
    $template_to_save = array(
        'data' => $_POST['tmpl_data'],//<= json stringified
        'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
        'metas' => array(
            'title' => $_POST['tmpl_title'],
            'description' => $_POST['tmpl_description'],
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
    if ( !is_string( $_POST['sek_data'] ) ) {
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
    if ( empty( $_POST['preset_section_id']) || !is_string( $_POST['preset_section_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . ' => missing or invalid preset_section_id' );
    }
    $section_id = $_POST['preset_section_id'];

    $section_data_decoded_from_custom_post_type = sek_get_saved_sektion_data( $section_id );
    if ( !empty( $section_data_decoded_from_custom_post_type ) ) {
        wp_send_json_success( $section_data_decoded_from_custom_post_type );
    } else {
        $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
        if ( !is_array( $all_saved_seks ) || empty( $all_saved_seks[$section_id]) ) {
            sek_error_log( __FUNCTION__ . ' => missing section data in get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS )' );
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
        wp_send_json_error( __FUNCTION__ . ' => missing post data for section title ' . $section_infos['title'] );
    }
}

?>