<?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___code_editor( $input_id, $input_data ) {
    /*
    * see wp-includes/general-template.php
    *
    * Needed to form the correct params to pass to the code mirror editor, based on the code type
    * It also enqueue the needed scripts but since this template is loaded via ajax we won't have any benefint from this.
    * This might be a problem if the needed scripts (codemirror/linters/styles) for some reason have not been enqueued when starting customizing.
    * To make sure they are we might want to run wp_enqueue_code_editor (with the most comprehensive args )
    */
    $code_editor_params = wp_enqueue_code_editor( array(
        'type' => isset( $input_data[ 'code_type' ] ) ? $input_data[ 'code_type' ] : 'text/html',
        'codemirror' => array(
            'indentUnit' => 2,
            'tabSize' => 2,
        ),
    ));
    ?>
        <textarea data-czrtype="<?php echo $input_id; ?>" class="width-100" name="textarea" rows="10" cols="" data-editor-params="<?php echo htmlspecialchars( json_encode( $code_editor_params ) ); ?>">
            {{ data.value }}
        </textarea>
    <?php
}
?>
