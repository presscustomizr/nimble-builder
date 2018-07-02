<?php
/* ------------------------------------------------------------------------- *
 *  FONT AWESOME ICON PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___fa_icon_picker( $input_id, $input_data ) {
    ?>
        <select data-czrtype="<?php echo $input_id; ?>"></select>
    <?php
}


// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___fa_icon_picker_input", '\Nimble\sek_get_fa_icon_list_tmpl', 10, 3 );
// hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
// this dynamic filter is declared on wp_ajax_ac_get_template
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
//
// For czr_tiny_mce_editor_module, we request the font_list tmpl
function sek_get_fa_icon_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    // sek_error_log( __FUNCTION__ . ' => ajax posted params', $posted_params );

    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }

    // ob_start();
    /*  ?>

      <?php*/
    // $html = ob_get_clean();
    // if ( empty( $html ) ) {
    //     wp_send_json_error( 'ac_get_all_modules_tmpl => no template was found for tmpl => ' . $requested_tmpl );
    // }

    return wp_json_encode( array(
          'fas' => array(
            'address-book',
            'adjust'
          ),
          'far' => array(
            'calendar',
            'calendar-alt'
          ),
          'fab' => array(
            'adn',
            'adversal'
         ),
        )
    );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}



//retrieves gfonts:
// 1) from webfonts.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
// otherwise
// 2) from the transient set if it exists
// function sek_retrieve_decoded_font_awesome_icons() {
//     if ( false == get_transient( 'sek_font_awesome_may_2018' ) ) {
//         $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

//         if ( $gfont_raw === false ) {
//           $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
//         }

//         $gfonts_decoded   = json_decode( $gfont_raw, true );
//         set_transient( 'sek_font_awesome_may_2018' , $gfonts_decoded , 60*60*24*3000 );
//     }
//     else {
//       $gfonts_decoded = get_transient( 'sek_font_awesome_may_2018' );
//     }

//     return $gfonts_decoded;
// }


?>