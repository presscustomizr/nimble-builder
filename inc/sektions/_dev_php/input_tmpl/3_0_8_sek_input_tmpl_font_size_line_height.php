<?php

/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// AND
/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT FOR TEXT => includes the 'justify' icon
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___font_size( $input_id, $input_data ) {
    ?>
        <div class="sek-font-size-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <?php
              printf( '<input type="number" %1$s %2$s %3$s value="16" />',
                  ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                  ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                  ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
                );
          ?>
        </div><?php // sek-font-size-wrapper ?>
    <?php
}

function sek_set_input_tmpl___line_height( $input_id, $input_data ) {
    ?>
        <div class="sek-line-height-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <?php
              printf( '<input type="number" %1$s %2$s %3$s value="24" />',
                  ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                  ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                  ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
                );
          ?>
        </div><?php // sek-line-height-wrapper ?>
    <?php
}
?>