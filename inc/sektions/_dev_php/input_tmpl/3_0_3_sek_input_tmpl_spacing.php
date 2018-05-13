<?php

/* ------------------------------------------------------------------------- *
 *  SPACING INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___spacing( $input_id, $input_data ) {
    ?>
    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
    <div class="sek-spacing-wrapper">
        <div class="Spacing-spacingContainer-12n">
          <div class="Spacing-spacingRow-K2n Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
            <div class="SmartTextLabel-main-22n SmartTextLabel-selected-R2n" data-sek-spacing="margin-top">
              <div class="SmartTextLabel-input-12n TextBox-container-32n">
                <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
              </div>
            </div>
          </div>
          <div class="Spacing-spacingRow--large-32n Flex-main-32n Flex-row-12n" style="display: flex; justify-content: space-between;">
            <div class="SmartTextLabel-main-22n Spacing-col-outer-left-32n SmartTextLabel-editable-false-22n" data-sek-spacing="margin-left">
              <div class="SmartTextLabel-input-12n TextBox-container-32n">
                <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
              </div>
            </div>

            <div class="Spacing-innerSpacingContainer-22n">
              <div class="Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
                <div class="SmartTextLabel-main-22n" data-sek-spacing="padding-top">
                  <div class="SmartTextLabel-input-12n TextBox-container-32n">
                    <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                  </div>
                </div>
              </div>
                <div class="Flex-main-32n Flex-row-12n" style="display: flex; justify-content: space-between;">
                  <div class="SmartTextLabel-main-22n SmartTextLabel-editable-false-22n" data-sek-spacing="padding-left">
                    <div class="SmartTextLabel-input-12n TextBox-container-32n">
                      <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                    </div>
                  </div>
                  <div class="SmartTextLabel-main-22n" data-sek-spacing="padding-right">
                    <div class="SmartTextLabel-input-12n TextBox-container-32n">
                      <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                    </div>
                  </div>
                </div>
              <div class="Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
                <div class="SmartTextLabel-main-22n" data-sek-spacing="padding-bottom">
                  <div class="SmartTextLabel-input-12n TextBox-container-32n">
                    <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
                  </div>
                </div>
              </div>
            </div>

            <div class="SmartTextLabel-main-22n Spacing-col-outer-right-22n" data-sek-spacing="margin-right">
              <div class="SmartTextLabel-input-12n TextBox-container-32n">
                <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
              </div>
            </div>
          </div>
          <div class="Spacing-spacingRow-K2n Flex-main-32n Flex-row-12n" style="display: flex; justify-content: center;">
            <div class="SmartTextLabel-main-22n SmartTextLabel-editable-false-22n" data-sek-spacing="margin-bottom">
              <div class="SmartTextLabel-input-12n TextBox-container-32n">
                <input class="textBox--input TextBox-layout-small-22n TextBox-main-22n" value="0" type="number"  >
              </div>
            </div>
          </div>
        </div><?php //Spacing-spacingContainer-12n ?>
        <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_domain_to_be_replaced' ); ?></span></div>
    </div><?php // sek-spacing-wrapper ?>
    <?php
}

?>