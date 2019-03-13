<?php
/* ------------------------------------------------------------------------- *
 *  RESET BUTTON INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___reset_button( $input_id, $input_data ) {
    ?>
      <?php //<# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #> ?>
      <?php
        if ( ! is_array( $input_data ) || empty( $input_data['scope'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing scope property' );
            return;
        }
      ?>
      <div class="sek-button-choice-wrapper">
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <button type="button" aria-pressed="false" class="sek-ui-button sek-float-right" title="<?php _e('Reset', 'text-domain'); ?>" data-sek-reset-scope="<?php echo $input_data['scope']; ?>"><?php _e('Reset', 'text-domain'); ?></button>
      </div>
  <?php
}
?>
