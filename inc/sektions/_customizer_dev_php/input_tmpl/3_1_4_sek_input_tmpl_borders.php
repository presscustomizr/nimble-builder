<?php
/* ------------------------------------------------------------------------- *
 *  BORDERS INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___borders( $input_id, $input_data ) {
    ?>
    <?php
      // we save the int value + unit
      // we want to keep only the numbers when printing the tmpl
      // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
    ?>
    <div class="sek-borders">
        <?php //<# //console.log( 'IN php::sek_set_input_tmpl___borders() => data range_slide => ', data ); #> ?>
        <div class="sek-border-type-wrapper">
            <div aria-label="unit" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('All', 'text-domain');?>" data-sek-border-type="_all_"><?php _e('All', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Left', 'text-domain');?>" data-sek-border-type="left"><?php _e('Left', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top', 'text-domain');?>" data-sek-border-type="top"><?php _e('Top', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Right', 'text-domain');?>" data-sek-border-type="right"><?php _e('Right', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom', 'text-domain');?>" data-sek-border-type="bottom"><?php _e('Bottom', 'text-domain');?></button></div>
        </div>
        <div class="sek-range-unit-wrapper">
            <div class="sek-range-wrapper">
              <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
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
                    <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button></div>
            </div>
        </div>
        <div class="sek-color-wrapper">
            <div class="sek-color-picker"><input class="sek-alpha-color-input" data-alpha="true" type="text" value=""/></div>
            <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right"><?php _e( 'Reset', 'text_domain'); ?></button></div>
        </div>
    </div><?php // sek-borders ?>
  <?php
}
?>
