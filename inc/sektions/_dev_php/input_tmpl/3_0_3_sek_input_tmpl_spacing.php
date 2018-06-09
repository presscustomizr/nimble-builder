<?php

/* ------------------------------------------------------------------------- *
 *  SPACING INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___spacing( $input_id, $input_data ) {
    ?>
    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
    <div class="sek-spacing-wrapper">
        <div class="sek-pad-marg-inner">
          <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch" data-sek-spacing="margin-top">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>
          <div class="sek-pm-middle-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch sek-pm-margin-left" data-sek-spacing="margin-left">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>

            <div class="sek-pm-padding-wrapper">
              <div class="sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="padding-top">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
                <div class="sek-flex-justify-center sek-flex-space-between">
                  <div class="sek-flex-center-stretch" data-sek-spacing="padding-left">
                    <div class="sek-pm-input-parent">
                      <input class="sek-pm-input" value="" type="number"  >
                    </div>
                  </div>
                  <div class="sek-flex-center-stretch" data-sek-spacing="padding-right">
                    <div class="sek-pm-input-parent">
                      <input class="sek-pm-input" value="" type="number"  >
                    </div>
                  </div>
                </div>
              <div class="sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="padding-bottom">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
            </div>

            <div class="sek-flex-center-stretch sek-pm-margin-right" data-sek-spacing="margin-right">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>

          <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch" data-sek-spacing="margin-bottom">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>
        </div><?php //sek-pad-marg-inner ?>
        <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_domain_to_be_replaced' ); ?></span></div>
    </div><?php // sek-spacing-wrapper ?>
    <?php
}

?>