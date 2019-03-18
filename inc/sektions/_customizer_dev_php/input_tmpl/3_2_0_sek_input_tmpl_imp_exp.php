<?php
/* ------------------------------------------------------------------------- *
 *  IMPORT / EXPORT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___imp_exp( $input_id, $input_data ) {
    ?>
      <?php //<# console.log( 'IN php::sek_set_input_tmpl___detached_tinymce_edito() => input data => ', data ); #> ?>
      <?php // sek_error_log( 'INPUT DATA ??', $input_data ); ?>
      <div class="sek-import-btn-wrap">
        <span class="czr-notice"><?php _e('Select the file to import and click on the import button.', 'text_doma' ); ?></span><br/>
        <span class="czr-notice"><?php _e('Be sure to import a file generated with the Nimble Builder export system.', 'text_doma' ); ?></span>
        <input type="file" name="sek-import-file" class="sek-import-file" />
        <input type="hidden" name="sek-skope" value="<?php echo $input_data['scope']; ?>" />
        <button type="button" class="button disabled" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-import"><?php _e('Import', 'text_doma' ); ?></button>
        <div class="sek-uploading"><?php _e( 'Uploading...', 'text_doma' ); ?></div>
      </div>
      <div class="sek-export-btn-wrap">
        <div class="customize-control-title width-100"><?php _e('Export', 'text_doma'); ?></div>
        <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-export"><?php _e('Export', 'text_doma' ); ?></button>
      </div>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value="{{ data.value }}"/>
  <?php
}
?>
