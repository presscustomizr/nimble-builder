<?php
/* ------------------------------------------------------------------------- *
 *  IMPORT / EXPORT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___imp_exp( $input_id, $input_data ) {
    ?>
      <?php //<# console.log( 'IN php::sek_set_input_tmpl___detached_tinymce_edito() => input data => ', data ); #> ?>
      <?php // sek_error_log( 'INPUT DATA ??', $input_data ); ?>
      <div class="sek-export-btn-wrap">
        <div class="customize-control-title width-100"><?php //_e('Export', 'text_doma'); ?></div>
        <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-export"><?php _e('Export', 'text_doma' ); ?></button>
      </div>
      <div class="sek-import-btn-wrap">
        <div class="customize-control-title width-100"><?php _e('IMPORT', 'text_doma'); ?></div>
        <span class="czr-notice"><?php _e('Select the file to import and click on Import button.', 'text_doma' ); ?></span>
        <span class="czr-notice"><?php _e('Be sure to import a file generated with the Nimble Builder export system.', 'text_doma' ); ?></span>
        <div class="czr-import-dialog notice notice-info">
            <div class="czr-import-message"><?php _e('Some of the imported sections need a location that is not active on this page. Sections in missing locations will not be rendered. You can continue importing or assign those sections to a contextually active location.', 'text_doma' ); ?></div>
            <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-import-as-is"><?php _e('Import without modification', 'text_doma' ); ?></button>
            <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-import-assign"><?php _e('Import in existing locations', 'text_doma' ); ?></button>
            <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-cancel-import"><?php _e('Cancel import', 'text_doma' ); ?></button>
        </div>
        <div class="sek-uploading"><?php _e( 'Uploading...', 'text_doma' ); ?></div>
        <input type="file" name="sek-import-file" class="sek-import-file" />
        <input type="hidden" name="sek-skope" value="<?php echo $input_data['scope']; ?>" />
        <button type="button" class="button disabled" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="sek-pre-import"><?php _e('Import', 'text_doma' ); ?></button>

      </div>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value="{{ data.value }}"/>
  <?php
}
?>
