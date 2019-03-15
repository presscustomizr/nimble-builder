<?php
/* ------------------------------------------------------------------------- *
 *  IMPORT / EXPORT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___imp_exp( $input_id, $input_data ) {
    ?>
      <?php ////console.log( 'IN php::sek_set_input_tmpl___detached_tinymce_edito() => input data => ', data ); #> ?>
      <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-import"><?php _e('Import', 'text_doma' ); ?></button>&nbsp;
      <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-export"><?php _e('Export', 'text_doma' ); ?></button>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value="{{ data.value }}"/>
  <?php
}
?>
