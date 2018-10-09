<?php
/* ------------------------------------------------------------------------- *
 *  MULTIPLE BUTTON CHOICES INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___buttons_choice( $input_id, $input_data ) {
    ?>
      <# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #>
      <?php
        if ( ! is_array( $input_data ) || empty( $input_data['choices'] ) || ! is_array( $input_data['choices'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing choices property' );
            return;
        }
      ?>
      <div class="sek-button-choice-wrapper">
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group sek-float-right" role="group">
            <?php
              foreach( $input_data['choices'] as $choice => $label ) {
                  printf('<button type="button" aria-pressed="false" class="sek-ui-button" title="%1$s" data-sek-choice="%2$s">%1$s</button>',
                    $label,
                    $choice
                  );
              }
            ?>
        </div>
      </div>
  <?php
}
?>
