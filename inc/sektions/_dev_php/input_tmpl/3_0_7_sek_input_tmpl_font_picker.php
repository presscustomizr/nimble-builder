<?php
/* ------------------------------------------------------------------------- *
 *  FONT PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___font_picker( $input_id, $input_data ) {
    ?>
        <select data-czrtype="<?php echo $input_id; ?>"></select>
    <?php
}


// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___font_picker_input", '\Nimble\sek_get_font_list_tmpl', 10, 3 );
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
    if ( false == get_transient( 'sek_gfonts_may_2018' ) ) {
        $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

        if ( $gfont_raw === false ) {
          $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
        }

        $gfonts_decoded   = json_decode( $gfont_raw, true );
        set_transient( 'sek_gfonts_may_2018' , $gfonts_decoded , 60*60*24*3000 );
    }
    else {
      $gfonts_decoded = get_transient( 'sek_gfonts_may_2018' );
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
  //   __( 'All languages' , 'text_domain_to_be_replaced' ),
  //   count($gfonts_decoded['items']) + count( $this -> get_cfonts() ),
  //   __('fonts' , 'text_domain_to_be_replaced' )
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
  //     __('fonts' , 'text_domain_to_be_replaced' )
  //   );
  // }

  return ('subsets' == $what) ? apply_filters( 'sek_font_picker_gfonts_subsets ', $subsets ) : apply_filters( 'sek_font_picker_gfonts', $gfonts )  ;
}

?>