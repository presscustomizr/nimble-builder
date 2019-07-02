<?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___code_editor( $input_id, $input_data ) {
    /*
    * Needed to form the correct params to pass to the code mirror editor, based on the code type
    */
    $code_type = ! empty( $input_data[ 'code_type' ] ) ? $input_data[ 'code_type' ] : 'text/html';
    $code_editor_params = nimble_get_code_editor_settings( array(
        'type' => $code_type
    ));
    ?>
        <textarea data-czrtype="<?php echo $input_id; ?>" data-editor-code-type="<?php echo $code_type; ?>" class="width-100" name="textarea" rows="10" cols="" data-editor-params="<?php echo htmlspecialchars( json_encode( $code_editor_params ) ); ?>">{{ data.value }}</textarea>
    <?php
}
?>
