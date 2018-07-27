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
                  'title' => __( 'WordPress Editor', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_rich-text-editor_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_image_module',
                  'title' => __( 'Image', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__image_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_heading_module',
                  'title' => __( 'Heading', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__heading_icon.svg'
                ),

                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_icon_module',
                  'title' => __( 'Icon', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__icon_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_button_module',
                  'title' => __( 'Button', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_button_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_map_module',
                  'title' => __( 'Map', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_map_icon.svg'
                ),

                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'two_columns',
                  'title' => __( 'Two Columns', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_2-columns_icon.svg'
                ),
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'three_columns',
                  'title' => __( 'Three Columns', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_3-columns_icon.svg'
                ),
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'four_columns',
                  'title' => __( 'Four Columns', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_4-columns_icon.svg'
                ),

                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_simple_html_module',
                  'title' => __( 'Html Content', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_html_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_quote_module',
                  'title' => __( 'Quote', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_quote_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_spacer_module',
                  'title' => __( 'Spacer', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__spacer_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_divider_module',
                  'title' => __( 'Divider', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__divider_icon.svg'
                ),


                // array(
                //   'content-type' => 'module',
                //   'content-id' => 'czr_featured_pages_module',
                //   'title' => __( 'Featured pages',  'text_domain_to_be_replaced' ),
                //   'icon' => 'Nimble__featured_icon.svg'
                // ),


            );
            $i = 0;
            foreach( $content_collection as $_params) {
                // if ( $i % 2 == 0 ) {
                //   //printf('<div class="sek-module-raw"></div');
                // }
                $icon_img_src = '';
                if ( !empty( $_params['icon'] ) ) {
                    $icon_img_src = NIMBLE_BASE_URL . '/assets/czr/sek/icons/modules/' . $_params['icon'];
                }

                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s" title="%5$s"><div class="sek-module-icon">%3$s</div><div class="sek-module-title"><div class="sek-centered-module-title">%4$s</div></div></div>',
                      $_params['content-type'],
                      $_params['content-id'],
                      empty( $icon_img_src ) ? '<i style="color:red">Missing Icon</i>' : '<img draggable="false" title="'. $_params['title'] . '" alt="'. $_params['title'] . '" class="nimble-module-icons" src="' . $icon_img_src .'"/>',
                      $_params['title'],
                      __('Drag and drop the module in the previewed page.', 'text_domain_to_be_replaced' )
                );
            }
          ?>
        </div>
    <?php
}

?>