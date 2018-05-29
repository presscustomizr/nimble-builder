<?php

/* ------------------------------------------------------------------------- *
 *  BACKGROUND POSITION INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___bg_position( $input_id, $input_data ) {
    ?>
        <div class="sek-bg-pos-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_left">
            <span>
              <svg class="symbol symbol-alignTypeTopLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M14.96 16v-1h-1v-1h-1v-1h-1v-1h-1v-1.001h-1V14h-1v-4-1h5v1h-3v.938h1v.999h1v1h1v1.001h1v1h1V16h-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top">
            <span>
              <svg class="symbol symbol-alignTypeTop" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M14.969 12v-1h-1v-1h-1v7h-1v-7h-1v1h-1v1h-1v-1.062h1V9.937h1v-1h1V8h1v.937h1v1h1v1.001h1V12h-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_right">
            <span>
              <svg class="symbol symbol-alignTypeTopRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M9.969 16v-1h1v-1h1v-1h1v-1h1v-1.001h1V14h1v-4-1h-1-4v1h3v.938h-1v.999h-1v1h-1v1.001h-1v1h-1V16h1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="left">
            <span>
              <svg class="symbol symbol-alignTypeLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M11.469 9.5h-1v1h-1v1h7v1h-7v1h1v1h1v1h-1.063v-1h-1v-1h-1v-1h-.937v-1h.937v-1h1v-1h1v-1h1.063v1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="center">
            <span>
              <svg class="symbol symbol-alignTypeCenter" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="right">
            <span>
              <svg class="symbol symbol-alignTypeRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M12.469 14.5h1v-1h1v-1h-7v-1h7v-1h-1v-1h-1v-1h1.062v1h1v1h1v1h.938v1h-.938v1h-1v1h-1v1h-1.062v-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_left">
            <span>
              <svg class="symbol symbol-alignTypeBottomLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M14.969 9v1h-1v1h-1v1h-1v1h-1v1.001h-1V11h-1v5h5v-1h-3v-.938h1v-.999h1v-1h1v-1.001h1v-1h1V9h-1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom">
            <span>
              <svg class="symbol symbol-alignTypeBottom" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M9.969 13v1h1v1h1V8h1v7h1v-1h1v-1h1v1.063h-1v.999h-1v1.001h-1V17h-1v-.937h-1v-1.001h-1v-.999h-1V13h1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_right">
            <span>
              <svg class="symbol symbol-alignTypeBottomRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                <path id="path-1" fill-rule="evenodd" d="M9.969 9v1h1v1h1v1h1v1h1v1.001h1V11h1v5h-1-4v-1h3v-.938h-1v-.999h-1v-1h-1v-1.001h-1v-1h-1V9h1z" class="cls-5">
                </path>
              </svg>
            </span>
          </label>
        </div><?php // sek-bg-pos-wrapper ?>
    <?php
}

?>