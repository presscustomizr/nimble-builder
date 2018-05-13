<?php
/* ------------------------------------------------------------------------- *
 *  VERTICAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
//add_filter( 'czr_set_input_tmpl___v_alignment', 'sek_set_input_tmpl___v_alignment', 10, 3 );
function sek_set_input_tmpl___v_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-v-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="top" title="<?php _e('Align top','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_top</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_center</i></div>
            <div data-sek-align="bottom" title="<?php _e('Align bottom','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_bottom</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}

?>