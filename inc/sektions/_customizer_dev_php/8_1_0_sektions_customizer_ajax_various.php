<?php
////////////////////////////////////////////////////////////////
// GENERIC HELPER FIRED IN ALL AJAX CALLBACKS
// @param $params = array('check_nonce' => true )
function sek_do_ajax_pre_checks( $params = array() ) {
    $params = wp_parse_args( $params, array( 'check_nonce' => true ) );
    if ( $params['check_nonce'] ) {
        $action = 'save-customize_' . get_stylesheet();
        if ( !check_ajax_referer( $action, 'nonce', false ) ) {
             wp_send_json_error( array(
                'code' => 'invalid_nonce',
                'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' => check_ajax_referer() failed.' ),
            ) );
        }
    }

    if ( !is_user_logged_in() ) {
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
    }
    if ( !current_user_can( 'customize' ) ) {
      wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
    }
    if ( !current_user_can( 'customize' ) ) {
        status_header( 403 );
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
    } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
        status_header( 405 );
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
    }
}//sek_do_ajax_pre_checks()



// IMPORT IMG
add_action( 'wp_ajax_sek_import_attachment', '\Nimble\sek_ajax_import_attachment' );
// Fetches the list of revision for a given skope_id
add_action( 'wp_ajax_sek_get_revision_history', '\Nimble\sek_get_revision_history' );
// Fetches the revision for a given post id
add_action( 'wp_ajax_sek_get_single_revision', '\Nimble\sek_get_single_revision' );
// Fetches the category collection to generate the options for a select input
// @see api.czrInputMap.category_picker
add_action( 'wp_ajax_sek_get_post_categories', '\Nimble\sek_get_post_categories' );

// Fetches the code editor params to generate the options for a textarea input
// @see api.czrInputMap.code_editor
add_action( 'wp_ajax_sek_get_code_editor_params', '\Nimble\sek_get_code_editor_params' );

add_action( 'wp_ajax_sek_postpone_feedback', '\Nimble\sek_postpone_feedback_notification' );

// <AJAX TO FETCH INPUT COMPONENTS>
// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___fa_icon_picker_input", '\Nimble\sek_get_fa_icon_list_tmpl', 10, 3 );

// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___font_picker_input", '\Nimble\sek_get_font_list_tmpl', 10, 3 );
// </AJAX TO FETCH INPUT COMPONENTS>

/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_import_attachment
function sek_ajax_import_attachment() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

    if ( !isset( $_POST['img_url'] ) || !is_string($_POST['img_url']) ) {
        wp_send_json_error( 'missing_or_invalid_img_url_when_importing_image');
    }

    $id = sek_sideload_img_and_return_attachment_id( sanitize_text_field($_POST['img_url']) );
    if ( is_wp_error( $id ) ) {
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => problem when trying to wp_insert_attachment() for img : ' . sanitize_text_field($_POST['img_url']) . ' | SERVER ERROR => ' . json_encode( $id ) );
    } else {
        wp_send_json_success([
          'id' => $id,
          'url' => wp_get_attachment_url( $id )
        ]);
    }
}





////////////////////////////////////////////////////////////////
// REVISIONS
// Fired in __construct()
function sek_get_revision_history() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
    }
    $rev_list = sek_get_revision_history_from_posts( sanitize_text_field($_POST['skope_id']) );
    wp_send_json_success( $rev_list );
}


function sek_get_single_revision() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    if ( !isset( $_POST['revision_post_id'] ) || empty( $_POST['revision_post_id'] ) ) {
        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing revision_post_id' );
    }
    $revision = sek_get_single_post_revision( sanitize_text_field($_POST['revision_post_id']) );
    wp_send_json_success( $revision );
}



////////////////////////////////////////////////////////////////
// POST CATEGORIES => to be used in the category picker select input
// Fired in __construct()
function sek_get_post_categories() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $raw_cats = get_categories();
    $raw_cats = is_array( $raw_cats ) ? $raw_cats : array();
    $cat_collection = array();
    foreach( $raw_cats as $cat ) {
        $cat_collection[] = array(
            'id' => $cat->term_id,
            'slug' => $cat->slug,
            'name' => sprintf( '%s (%s %s)', $cat->cat_name, $cat->count, __('posts', 'text_doma') )
        );
    }
    wp_send_json_success( $cat_collection );
}



////////////////////////////////////////////////////////////////
// CODE EDITOR PARAMS => to be used in the code editor input
// Fired in __construct()
function sek_get_code_editor_params() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $code_type = isset( $_POST['code_type'] ) ? sanitize_text_field($_POST['code_type']) : 'text/html';
    $editor_params = nimble_get_code_editor_settings( array(
        'type' => $code_type
    ));
    wp_send_json_success( $editor_params );
}

////////////////////////////////////////////////////////////////
// POSTPONE FEEDBACK NOTIFICATION IN CUSTOMIZER
// INSPIRED FROM CORE DISMISS POINTER MECHANISM
// @see wp-admin/includes/ajax-actions.php
// Nov 2020 => DEPRECATED https://github.com/presscustomizr/nimble-builder/issues/701
function sek_postpone_feedback_notification() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    if ( !isset( $_POST['transient_duration_in_days'] ) ||!is_numeric( $_POST['transient_duration_in_days'] ) ) {
        $transient_duration = 7 * DAY_IN_SECONDS;
    } else {
        $transient_duration = sanitize_text_field($_POST['transient_duration_in_days']) * DAY_IN_SECONDS;
    }
    set_transient( NIMBLE_FEEDBACK_NOTICE_ID, 'maybe_later', $transient_duration );
    wp_die( 1 );
}


////////////////////////////////////////////////////////////////
// FETCH FONT AWESOME ICONS
// hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
// this dynamic filter is declared on wp_ajax_ac_get_template
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
//
// For czr_tiny_mce_editor_module, we request the font_list tmpl
function sek_get_fa_icon_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }
    return wp_json_encode(
        sek_retrieve_decoded_font_awesome_icons()
    );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}


//retrieves faicons:
// 1) from faicons.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
// otherwise
// 2) from the transient set if it exists
function sek_retrieve_decoded_font_awesome_icons() {
    // this file must be generated with: https://github.com/presscustomizr/nimble-builder/issues/57
    $faicons_json_path      = NIMBLE_BASE_PATH . '/assets/faicons.json';
    $faicons_transient_name = NIMBLE_FAWESOME_TRANSIENT_ID;
    if ( false == get_transient( $faicons_transient_name ) ) {
        if ( file_exists( $faicons_json_path ) ) {
            $faicons_raw      = @file_get_contents( $faicons_json_path );

            if ( false === $faicons_raw ) {
                $faicons_raw = wp_remote_fopen( $faicons_json_path );
            }

            $faicons_decoded   = json_decode( $faicons_raw, true );
            set_transient( $faicons_transient_name , $faicons_decoded , 60*60*24*3000 );
        } else {
            wp_send_json_error( __FUNCTION__ . ' => the file faicons.json is missing' );
        }
    }
    else {
        $faicons_decoded = get_transient( $faicons_transient_name );
    }

    return $faicons_decoded;
}




////////////////////////////////////////////////////////////////
// FETCH FONT LISTS
// hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
// For czr_tiny_mce_editor_module, we request the font_list tmpl
function sek_get_font_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }

    return wp_json_encode( array(
        'cfonts' => sek_get_cfonts(),
        'gfonts' => sek_get_gfonts(),
    ) );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}


function sek_get_cfonts() {
    $cfonts = array();
    $raw_cfonts = array(
        '-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue, Arial, sans-serif',
        'Arial Black,Arial Black,Gadget,sans-serif',
        'Century Gothic',
        'Comic Sans MS,Comic Sans MS,cursive',
        'Courier New,Courier New,Courier,monospace',
        'Georgia,Georgia,serif',
        'Helvetica Neue, Helvetica, Arial, sans-serif',
        'Impact,Charcoal,sans-serif',
        'Lucida Console,Monaco,monospace',
        'Lucida Sans Unicode,Lucida Grande,sans-serif',
        'Palatino Linotype,Book Antiqua,Palatino,serif',
        'Tahoma,Geneva,sans-serif',
        'Times New Roman,Times,serif',
        'Trebuchet MS,Helvetica,sans-serif',
        'Verdana,Geneva,sans-serif',
    );
    foreach ( $raw_cfonts as $font ) {
      //no subsets for cfonts => epty array()
      $cfonts[] = array(
          'name'    => $font ,
          'subsets'   => array()
      );
    }
    return apply_filters( 'sek_font_picker_cfonts', $cfonts );
}


//retrieves gfonts:
// 1) from webfonts.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
// otherwise
// 2) from the transiet set if it exists
//
// => Until June 2017, the webfonts have been stored in 'tc_gfonts' transient
// => In June 2017, the Google Fonts have been updated with a new webfonts.json
// generated from : https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBID8gp8nBOpWyH5MrsF7doP4fczXGaHdA
//
// => The transient name is now : czr_gfonts_june_2017
function sek_retrieve_decoded_gfonts() {
    if ( false == get_transient( NIMBLE_GFONTS_TRANSIENT_ID ) ) {
        $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

        if ( $gfont_raw === false ) {
          $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
        }

        $gfonts_decoded   = json_decode( $gfont_raw, true );
        set_transient( NIMBLE_GFONTS_TRANSIENT_ID , $gfonts_decoded , 60*60*24*3000 );
    }
    else {
      $gfonts_decoded = get_transient( NIMBLE_GFONTS_TRANSIENT_ID );
    }

    return $gfonts_decoded;
}

//@return the google fonts
function sek_get_gfonts( $what = null ) {
    //checks if transient exists or has expired

    $gfonts_decoded = sek_retrieve_decoded_gfonts();
    $gfonts = array();
    //$subsets = array();

    // $subsets['all-subsets'] = sprintf( '%1$s ( %2$s %3$s )',
    //   __( 'All languages' , 'text_doma' ),
    //   count($gfonts_decoded['items']) + count( get_cfonts() ),
    //   __('fonts' , 'text_doma' )
    // );

    foreach ( $gfonts_decoded['items'] as $font ) {
      foreach ( $font['variants'] as $variant ) {
        $name     = str_replace( ' ', '+', $font['family'] );
        $gfonts[]   = array(
            'name'    => $name . ':' .$variant
            //'subsets'   => $font['subsets']
        );
      }
      //generates subset list : subset => font number
      // foreach ( $font['subsets'] as $sub ) {
      //   $subsets[$sub] = isset($subsets[$sub]) ? $subsets[$sub]+1 : 1;
      // }
    }

    //finalizes the subset array
    // foreach ( $subsets as $subset => $font_number ) {
    //   if ( 'all-subsets' == $subset )
    //     continue;
    //   $subsets[$subset] = sprintf('%1$s ( %2$s %3$s )',
    //     $subset,
    //     $font_number,
    //     __('fonts' , 'text_doma' )
    //   );
    // }

    return ('subsets' == $what) ? apply_filters( 'sek_font_picker_gfonts_subsets ', $subsets ) : apply_filters( 'sek_font_picker_gfonts', $gfonts )  ;
}
?>