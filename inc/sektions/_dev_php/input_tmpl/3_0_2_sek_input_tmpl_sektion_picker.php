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
                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s"><p>%3$s</p></div>',
                    $_params['content-type'],
                    $_params['content-id'],
                    $_params['title']
                );
            }
          ?>
        </div>
  <?php
}

?>