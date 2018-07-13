<?php

/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// AND
/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT FOR TEXT => includes the 'justify' icon
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___font_size_line_height( $input_id, $input_data ) {
    ?>
        <div class="sek-font-size-line-height-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <?php
            // we save the int value + unit
            // we want to keep only the numbers when printing the tmpl
          ?>
          <#
            var value = data['<?php echo $input_id; ?>'];
            value = _.isString( value ) ? value.replace(/\D+/g, '') : '';
          #>
          <?php
              printf( '<input type="number" %1$s %2$s %3$s value="{{ value }}" />',
                  ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                  ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                  ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
                );
          ?>
          <div aria-label="Font Size" class="components-button-group" role="group">
            <button type="button" aria-pressed="true" class="components-button is-button is-default is-large">px</button>
            <button type="button" aria-pressed="false" class="components-button is-button is-default is-large">em</button>
            <button type="button" aria-pressed="false" class="components-button is-button is-default is-large">%</button>
          </div>
        </div><?php // sek-font-size-wrapper ?>
    <?php
}
?>