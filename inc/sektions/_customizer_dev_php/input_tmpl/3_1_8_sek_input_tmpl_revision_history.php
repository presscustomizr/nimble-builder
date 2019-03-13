<?php
/* ------------------------------------------------------------------------- *
 *  RESET BUTTON INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___revision_history( $input_id, $input_data ) {
    ?>
      <?php //<# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #> ?>
      <?php
        if ( ! is_array( $input_data ) || empty( $input_data['scope'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing scope property' );
            return;
        }
      ?>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
  <?php
}
?>
