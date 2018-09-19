<?php

/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___section_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = array(
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'img_text_one',
                  'title' => __('2 columns with image and text', 'text-domain' ),
                  'thumb' => 'img_text_one.jpg'
                )
            );
            foreach( $content_collection as $_params) {
                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s" style="%3$s" title="%4$s"></div>',
                    $_params['content-type'],
                    $_params['content-id'],
                    sprintf( 'background: url(%1$s) 50% 50% / cover no-repeat;',
                        NIMBLE_BASE_URL . '/assets/img/section_assets/thumbs/' . $_params['thumb']
                    ),
                    $_params['title']
                );
            }
          ?>
        </div>
  <?php
}

?>