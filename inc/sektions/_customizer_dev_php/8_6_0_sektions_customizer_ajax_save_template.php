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
// Fetches the api templates
add_action( 'wp_ajax_sek_get_all_api_tmpl', '\Nimble\sek_ajax_get_all_api_templates' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_get_all_api_templates() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    $decoded_templates = sek_get_all_api_templates();

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
        //         'tmpl_locations' => is_string( $_POST['tmpl_locations'] ) ? explode( ',', $_POST['tmpl_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( is_array( $tmpl_decoded ) && !empty( $tmpl_decoded['data'] ) && is_array( $tmpl_decoded['data'] ) ) {
            //$tmpl_decoded['data'] = json_decode( wp_unslash( $tmpl_decoded['data'], true ) );
            $tmpl_decoded['data'] = sek_maybe_import_imgs( $tmpl_decoded['data'], $do_import_images = true );
            // the image import errors won't block the import
            // they are used when notifying user in the customizer
            $tmpl_decoded['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
            wp_send_json_success( $tmpl_decoded );
        } else {
            wp_send_json_error( __FUNCTION__ . '_invalid_tmpl_post_data' );
        }
    } else {
        wp_send_json_error( __FUNCTION__ . '_tmpl_post_not_found' );
    }
}



add_action( 'wp_ajax_sek_get_api_tmpl_json', '\Nimble\sek_ajax_sek_get_api_tmpl_json' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_sek_get_api_tmpl_json() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a tmpl_post_name
    if ( empty( $_POST['api_tmpl_name']) || !is_string( $_POST['api_tmpl_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_post_name' );
    }
    $tmpl_name = $_POST['api_tmpl_name'];
    $raw_tmpl = sek_get_tmpl_api_data();// <= returns an unserialized array, in which the template['data'] is NOT a JSON, unlike for user saved templates
    if( !is_array( $raw_tmpl) || empty( $raw_tmpl ) ) {
        sek_error_log( __FUNCTION__ . ' problem => no api template collection available when getting template : ' . $tmpl_name );
        wp_send_json_error( __FUNCTION__ . '_empty_api_template_collection' );
    }
    //sek_error_log( __FUNCTION__ . ' api template collection', $raw_tmpl[$tmpl_name] );
    if ( empty( $raw_tmpl[$tmpl_name] ) ) {
        sek_error_log( __FUNCTION__ . ' problem => template not found in api template collection : ' . $tmpl_name );
        wp_send_json_error( __FUNCTION__ . '_api_template_not_found' );
    // Note that $raw_tmpl[$tmpl_name]['data'] is saved as a json
    } else if ( !isset($raw_tmpl[$tmpl_name]['data'] ) || empty( $raw_tmpl[$tmpl_name]['data'] ) ) {
        sek_error_log( __FUNCTION__ . ' problem => missing or invalid data property for template : ' . $tmpl_name, $raw_tmpl[$tmpl_name] );
        wp_send_json_error( __FUNCTION__ . '_missing_data_property' );
    } else {
        // $tmpl_decoded = $raw_tmpl[$tmpl_name];
        $tmpl_as_array = $raw_tmpl[$tmpl_name];
        $raw_tmpl[$tmpl_name]['data'] = sek_maybe_import_imgs( $raw_tmpl[$tmpl_name]['data'], $do_import_images = true );
        $raw_tmpl[$tmpl_name]['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
        wp_send_json_success( $raw_tmpl[$tmpl_name] );
    }
    //return [];
}



////////////////////////////////////////////////////////////////
// TEMPLATE SAVE
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_TEMPLATE_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_save_user_template', '\Nimble\sek_ajax_save_user_template' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_save_user_template
function sek_ajax_save_user_template() {
    //sek_error_log( __FUNCTION__ . ' ALORS ??', $_POST );

    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $is_edit_metas_only_case = isset( $_POST['edit_metas_only'] ) && 'yes' === $_POST['edit_metas_only'];

    // TMPL DATA => the nimble content
    if ( !$is_edit_metas_only_case && empty( $_POST['tmpl_data']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_template_data' );
    }
    if ( !$is_edit_metas_only_case && !is_string( $_POST['tmpl_data'] ) ) {
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
    if ( !isset( $_POST['tmpl_locations'] ) || empty( $_POST['tmpl_locations'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_locations' );
    }

    if ( $is_edit_metas_only_case ) {
        $tmpl_data = [];
    } else {
        // clean level ids and replace them with a placeholder string
        $tmpl_data = json_decode( wp_unslash( $_POST['tmpl_data'] ), true );
        $tmpl_data = sek_template_save_clean_id( $tmpl_data );
    }
    
    // make sure description and title are clean before DB
    $tmpl_title = wp_strip_all_tags( $_POST['tmpl_title'] );
    $tmpl_description = wp_strip_all_tags( $_POST['tmpl_description'] );

    //sek_error_log(__FUNCTION__ .  '$_POST?', $_POST );

    // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
    $template_to_save = array(
        'data' => $tmpl_data,//<= array
        'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
        'metas' => array(
            'title' => $tmpl_title,
            'description' => $tmpl_description,
            'skope_id' => $_POST['skope_id'],
            'version' => NIMBLE_VERSION,
            // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
            'tmpl_locations' => is_array( $_POST['tmpl_locations'] ) ? $_POST['tmpl_locations'] : array(),
            'tmpl_header_location' => isset( $_POST['tmpl_header_location'] ) ? $_POST['tmpl_header_location'] : '',
            'tmpl_footer_location' => isset( $_POST['tmpl_footer_location'] ) ? $_POST['tmpl_footer_location'] : '',
            'date' => date("Y-m-d"),
            'theme' => sanitize_title_with_dashes( get_stylesheet() )
        ),
        'edit_metas_only' => $is_edit_metas_only_case ? 'yes' : 'no'
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


// SAVE FILTER
function sek_template_save_clean_id( $tmpl_data = array() ) {
    $new_tmpl_data = array();
    if ( !is_array( $tmpl_data ) ) {
        sek_error_log( __FUNCTION__ . ' error => tmpl_data should be an array');
        return array();
    }
    $level = null;
    if ( isset($tmpl_data['level'] ) ) {
        $level = $tmpl_data['level'];
    }
    foreach ( $tmpl_data as $key => $value ) {
        if ( is_array($value) ) {
            $new_tmpl_data[$key] = sek_template_save_clean_id( $value );
        } else {
            switch( $key ) {
                // we want to replace ids for all levels but locations
                // only section, columns and modules have an id which starts by __nimble__, for ex : __nimble__2024500518bf
                // locations id are like : loop_start
                case 'id' :
                    if ( 'location' !== $level && is_string( $value ) && false !== strpos( $value, '__nimble__' ) ) {
                        $value = '__rep__me__';
                    }
                break;
            }
            $new_tmpl_data[$key] = $value;
        }
    }
    return $new_tmpl_data;
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

    //sek_error_log( __FUNCTION__ . ' => so $tmpl_post_to_remove ' . $_POST['tmpl_post_name'], $tmpl_post_to_remove );

    if ( $tmpl_post_to_remove && is_object( $tmpl_post_to_remove ) ) {
        // the CPT is moved to Trash instead of permanently deleted when using wp_delete_post()
        $r = wp_trash_post( $tmpl_post_to_remove->ID );
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