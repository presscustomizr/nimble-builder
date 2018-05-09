<?php
/* ------------------------------------------------------------------------- *
 *  MODULE PICKER INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___module_picker', 'sek_set_input_tmpl___module_picker', 10,3 );
function sek_set_input_tmpl___module_picker( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Module Picker => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

            printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
            ?>
              <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                  <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
              <?php endif; ?>
            <div class="czr-input">
              <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
              <div class="sek-content-type-wrapper">
                <?php
                  $content_collection = array(
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_tiny_mce_editor_module',
                        'title' => '@missi18n Text Editor'),
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_image_module',
                        'title' => '@missi18n Image'
                      ),
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_simple_html_module',
                        'title' => '@missi18n Html Content'
                      ),
                      array(
                        'content-type' => 'module',
                        'content-id' => 'czr_featured_pages_module',
                        'title' => '@missi18n Featured pages'
                      ),

                  );
                  $i = 0;
                  foreach( $content_collection as $_params) {
                      if ( $i % 2 == 0 ) {
                        //printf('<div class="sek-module-raw"></div');
                      }
                      printf('<div draggable="true" style="%1$s" data-sek-content-type="%2$s" data-sek-content-id="%3$s"><p style="%4$s">%5$s</p></div>',
                          "width: 40%;float: left;padding: 5%;text-align: center;",
                          $_params['content-type'],
                          $_params['content-id'],
                          "padding: 9%;background: #eee;cursor: move;",
                          $_params['title']
                      );
                      $i++;
                  }
                ?>
              </div>
            </div><?php // class="czr-input" ?>
            <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
            <?php endif; ?>
          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}






/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___section_picker', 'sek_set_input_tmpl___section_picker', 10, 3 );
function sek_set_input_tmpl___section_picker( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Section Picker => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

            printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
            ?>
              <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                  <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
              <?php endif; ?>
            <# //console.log('DATA IN SECTION PICKER INPUT'); #>
            <div class="czr-input">
              <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
              <div class="sek-content-type-wrapper">
                <?php
                  $content_collection = array(
                      array(
                        'content-type' => 'preset_section',
                        'content-id' => 'alternate_text_right',
                        'title' => 'Image + Text'
                      ),
                      array(
                        'content-type' => 'preset_section',
                        'content-id' => 'alternate_text_left',
                        'title' => 'Text + Image'
                      )
                  );
                  foreach( $content_collection as $_params) {
                      printf('<div draggable="true" style="%1$s" data-sek-content-type="%2$s" data-sek-content-id="%3$s"><p style="%4$s">%5$s</p></div>',
                          "width: 40%;float: left;padding: 5%;text-align: center;",
                          $_params['content-type'],
                          $_params['content-id'],
                          "padding: 9%;background: #eee;cursor: move;",
                          $_params['title']
                      );
                  }
                ?>
              </div>
            </div><?php // class="czr-input" ?>
            <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
            <?php endif; ?>
          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}








/* ------------------------------------------------------------------------- *
 *  SPACING INPUT
/* ------------------------------------------------------------------------- */
// SPACING INPUT
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___spacing', 'sek_set_input_tmpl___spacing', 10, 3 );
function sek_set_input_tmpl___spacing( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title width-100">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>
                <# //console.log('DATA IN SPACING INPUT'); #>

                <div class="czr-input">
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
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}









/* ------------------------------------------------------------------------- *
 *  BACKGROUND POSITION INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___bg_position', 'sek_set_input_tmpl___bg_position', 10, 3 );
function sek_set_input_tmpl___bg_position( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                'width-100',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>
                <# //console.log('DATA IN SPACING INPUT'); #>

                <div class="czr-input">
                  <div class="sek-bg-pos-wrapper">
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                    <div class="items">
                      <label class="item">
                        <input type="radio" name="rb_0" value="top_left">
                        <span>
                          <svg class="symbol symbol-alignTypeTopLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M14.96 16v-1h-1v-1h-1v-1h-1v-1h-1v-1.001h-1V14h-1v-4-1h5v1h-3v.938h1v.999h1v1h1v1.001h1v1h1V16h-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="top">
                        <span>
                          <svg class="symbol symbol-alignTypeTop" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M14.969 12v-1h-1v-1h-1v7h-1v-7h-1v1h-1v1h-1v-1.062h1V9.937h1v-1h1V8h1v.937h1v1h1v1.001h1V12h-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="top_right">
                        <span>
                          <svg class="symbol symbol-alignTypeTopRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M9.969 16v-1h1v-1h1v-1h1v-1h1v-1.001h1V14h1v-4-1h-1-4v1h3v.938h-1v.999h-1v1h-1v1.001h-1v1h-1V16h1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="left">
                        <span>
                          <svg class="symbol symbol-alignTypeLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M11.469 9.5h-1v1h-1v1h7v1h-7v1h1v1h1v1h-1.063v-1h-1v-1h-1v-1h-.937v-1h.937v-1h1v-1h1v-1h1.063v1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="center">
                        <span>
                          <svg class="symbol symbol-alignTypeCenter" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="right">
                        <span>
                          <svg class="symbol symbol-alignTypeRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M12.469 14.5h1v-1h1v-1h-7v-1h7v-1h-1v-1h-1v-1h1.062v1h1v1h1v1h.938v1h-.938v1h-1v1h-1v1h-1.062v-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="bottom_left">
                        <span>
                          <svg class="symbol symbol-alignTypeBottomLeft" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M14.969 9v1h-1v1h-1v1h-1v1h-1v1.001h-1V11h-1v5h5v-1h-3v-.938h1v-.999h1v-1h1v-1.001h1v-1h1V9h-1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="bottom">
                        <span>
                          <svg class="symbol symbol-alignTypeBottom" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M9.969 13v1h1v1h1V8h1v7h1v-1h1v-1h1v1.063h-1v.999h-1v1.001h-1V17h-1v-.937h-1v-1.001h-1v-.999h-1V13h1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                      <label class="item">
                        <input type="radio" name="rb_0" value="bottom_right">
                        <span>
                          <svg class="symbol symbol-alignTypeBottomRight" width="24" height="24" preserveAspectRatio="xMidYMid" viewBox="0 0 24 24">
                            <path id="path-1" fill-rule="evenodd" d="M9.969 9v1h1v1h1v1h1v1h1v1.001h1V11h1v5h-1-4v-1h3v-.938h-1v-.999h-1v-1h-1v-1.001h-1v-1h-1V9h1z" class="cls-5">
                            </path>
                          </svg>
                        </span>
                      </label>
                    </div><?php // .items ?>
                  </div><?php // control-alignment ?>
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}






/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___h_alignment', 'sek_set_input_tmpl___h_alignment', 10, 3 );
function sek_set_input_tmpl___h_alignment( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                '',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>

                <div class="czr-input">
                  <div class="sek-h-align-wrapper">
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                    <div class="sek-align-icons">
                      <div data-sek-align="left" title="<?php _e('Align left','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_left</i></div>
                      <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_center</i></div>
                      <div data-sek-align="right" title="<?php _e('Align right','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_right</i></div>
                    </div>
                  </div><?php // sek-h-align-wrapper ?>
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}





/* ------------------------------------------------------------------------- *
 *  VERTICAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
// filter declared in CZR_Fmk_Base_Tmpl_Builder::ac_get_default_input_tmpl
add_filter( 'czr_set_input_tmpl___v_alignment', 'sek_set_input_tmpl___v_alignment', 10, 3 );
function sek_set_input_tmpl___v_alignment( $html, $input_id, $input_data ) {
    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( 'Spacing input => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
         return;
    }
    $input_type = $input_data[ 'input_type' ];
    $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

    ob_start();
        ?>
         <?php
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                $css_attr['sub_set_wrapper'],
                '',//$is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : ''
            );

                printf( '<div class="customize-control-title">%1$s</div>', $input_data['title'] );
                ?>
                  <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                      <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
                  <?php endif; ?>

                <div class="czr-input">
                  <div class="sek-v-align-wrapper">
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                    <div class="sek-align-icons">
                      <div data-sek-align="top" title="<?php _e('Align top','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_top</i></div>
                      <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_center</i></div>
                      <div data-sek-align="bottom" title="<?php _e('Align bottom','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_bottom</i></div>
                    </div>
                  </div><?php // sek-h-align-wrapper ?>
                </div><?php // class="czr-input" ?>

                <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                    <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
                <?php endif; ?>

          </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
        <?php
    return ob_get_clean();
}

?>