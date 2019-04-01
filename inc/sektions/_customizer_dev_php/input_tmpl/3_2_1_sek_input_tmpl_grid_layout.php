<?php

/* ------------------------------------------------------------------------- *
 *  POST GRID LAYOUT PICKER
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___grid_layout( $input_id, $input_data ) {
    ?>
        <div class="sek-grid-layout-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-grid-icons">
            <div data-sek-grid-layout="list" title="<?php _e('List layout','text_doma'); ?>"><i class="material-icons">view_list</i></div>
            <div data-sek-grid-layout="grid" title="<?php _e('Grid layout','text_doma'); ?>"><i class="material-icons">view_module</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}
?>