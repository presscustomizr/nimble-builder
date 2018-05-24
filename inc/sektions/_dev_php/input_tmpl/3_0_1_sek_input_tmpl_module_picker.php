<?php
/* ------------------------------------------------------------------------- *
 *  MODULE PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___module_picker( $input_id, $input_data ) {
    ?>
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
                // array(
                //   'content-type' => 'module',
                //   'content-id' => 'czr_simple_html_module',
                //   'title' => '@missi18n Html Content'
                // ),
                // array(
                //   'content-type' => 'module',
                //   'content-id' => 'czr_featured_pages_module',
                //   'title' => '@missi18n Featured pages'
                // ),

            );
            $i = 0;
            foreach( $content_collection as $_params) {
                if ( $i % 2 == 0 ) {
                  //printf('<div class="sek-module-raw"></div');
                }
                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s"><p>%3$s</p></div>',
                    $_params['content-type'],
                    $_params['content-id'],
                    $_params['title']
                );
                $i++;
            }
          ?>
        </div>
    <?php
}

?>