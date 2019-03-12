<?php
/* ------------------------------------------------------------------------- *
 *  DETACHED WP EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___detached_tinymce_editor( $input_id, $input_data ) {
    ?>
      <# //console.log( 'IN php::sek_set_input_tmpl___detached_tinymce_edito() => input data => ', data ); #>
      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="open-tinymce-editor"><?php _e('Edit', 'text_doma' ); ?></button>&nbsp;
      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="close-tinymce-editor"><?php _e('Hide editor', 'text_doma' ); ?></button>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value="{{ data.value }}"/>
  <?php
}
?>
