<?php

/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// AND
/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT FOR TEXT => includes the 'justify' icon
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
// add_filter( 'czr_set_input_tmpl___h_alignment', 'sek_set_input_tmpl___h_alignment', 10, 3 );
// add_filter( 'czr_set_input_tmpl___h_text_alignment', 'sek_set_input_tmpl___h_alignment', 10, 3 );
function sek_set_input_tmpl___h_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_right</i></div>
            <?php if ( 'czr_set_input_tmpl___h_text_alignment' == current_filter() ) : ?>
              <div data-sek-align="justify" title="<?php _e('Justified','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_justify</i></div>
            <?php endif; ?>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}

?>