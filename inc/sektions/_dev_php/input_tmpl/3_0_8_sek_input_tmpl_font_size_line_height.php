<?php

/* ------------------------------------------------------------------------- *
 *  FONT SIZE
/* ------------------------------------------------------------------------- */
// AND
/* ------------------------------------------------------------------------- *
 *  LINE HEIGHT INPUT TMPLS
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___font_size_line_height( $input_id, $input_data ) {
    ?>
      <?php
            // we save the int value + unit
            // we want to keep only the numbers when printing the tmpl
            // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
          ?>
          <#
            var value = data['<?php echo $input_id; ?>'],
                unit = data['<?php echo $input_id; ?>'];
            value = _.isString( value ) ? value.replace(/px|em|%/g,'') : '';
            unit = _.isString( unit ) ? unit.replace(/[0-9]|\.|,/g, '') : 'px';
            unit = _.isEmpty( unit ) ? 'px' : unit;
          #>
        <div class="sek-font-size-line-height-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden" data-sek-unit="{{ unit }}"/>

          <?php
              printf( '<input type="number" %1$s %2$s %3$s value="{{ value }}" />',
                  ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                  ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                  ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
                );
          ?>
          <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group sek-float-right" role="group">
              <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_domain');?>" data-sek-unit="%"><?php _e('%', 'text_domain');?></button></div>
        </div><?php // sek-font-size-wrapper ?>
    <?php
}
?>