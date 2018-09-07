<?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___range_with_unit_picker_device_switcher( $input_id, $input_data ) {
    ?>
    <?php
      // we save the int value + unit
      // we want to keep only the numbers when printing the tmpl
      // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
    ?>
    <#
      var value = data['<?php echo $input_id; ?>'],
          unit = data['<?php echo $input_id; ?>'];
      value = _.isString( value ) ? value.replace(/px|em|%/g,'') : value;
      unit = _.isString( unit ) ? unit.replace(/[0-9]|\.|,/g, '') : 'px';
      unit = _.isEmpty( unit ) ? 'px' : unit;
    #>
    <div class="sek-range-with-unit-picker-wrapper">
        <# //console.log( 'IN php::sek_set_input_tmpl___range_with_unit_picker_device_switcher() => data range_slide => ', data ); #>
        <div class="sek-range-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden" data-sek-unit="{{ unit }}"/>
          <?php
          printf( '<input class="sek-range-input" type="range" %1$s %2$s %3$s %4$s/>',
            ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
            ! empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : '',
            ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : 'min="0"',
            ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
          );
          ?>
        </div>
        <div class="sek-number-wrapper">
            <input class="sek-pm-input" value="{{ value }}" type="number"  >
        </div>
        <div class="sek-unit-wrapper">
          <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_domain');?>" data-sek-unit="%"><?php _e('%', 'text_domain');?></button></div>
        </div>
    </div><?php // sek-spacing-wrapper ?>
  <?php
}
?>
