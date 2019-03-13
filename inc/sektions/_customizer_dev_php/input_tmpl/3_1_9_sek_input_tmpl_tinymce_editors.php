<?php
/* ------------------------------------------------------------------------- *
 *  DETACHED WP EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___detached_tinymce_editor( $input_id, $input_data ) {
    ?>
      <?php ////console.log( 'IN php::sek_set_input_tmpl___detached_tinymce_edito() => input data => ', data ); #> ?>
      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="open-tinymce-editor"><?php _e('Edit', 'text_doma' ); ?></button>&nbsp;
      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="close-tinymce-editor"><?php _e('Hide editor', 'text_doma' ); ?></button>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value="{{ data.value }}"/>
  <?php
}

/* ------------------------------------------------------------------------- *
 *  WP EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___nimble_tinymce_editor( $input_id, $input_data ) {
    // Added an id attribute for https://github.com/presscustomizr/nimble-builder/issues/403
    // needed to instantiate wp.editor.initialize(...)
    ?>
    <?php //<# console.log( 'IN php::ac_get_default_input_tmpl() => data range_slide => ', data ); #> ?>
      <textarea id="textarea-{{ data.control_id }}" data-czrtype="<?php echo $input_id; ?>" class="width-100" name="textarea" rows="10" cols="">{{ data.value }}</textarea>
    <?php
}
?>
