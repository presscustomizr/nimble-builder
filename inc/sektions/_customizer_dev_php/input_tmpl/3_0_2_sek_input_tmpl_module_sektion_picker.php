<?php

/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___content_type_switcher( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <div aria-label="<?php _e( 'Content type', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a section', 'text_domain');?>" data-sek-content-type="section"><?php _e('Pick a section', 'text_domain');?></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a module', 'text_domain');?>" data-sek-content-type="module"><?php _e('Pick a module', 'text_domain');?></button>
            </div>
        </div>
  <?php
}


/* ------------------------------------------------------------------------- *
 *  MODULE PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___module_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = [
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_tiny_mce_editor_module',
                  'title' => __( 'WordPress Editor', 'text_doma' ),
                  'icon' => 'Nimble_rich-text-editor_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_image_module',
                  'title' => __( 'Image', 'text_doma' ),
                  'icon' => 'Nimble__image_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_heading_module',
                  'title' => __( 'Heading', 'text_doma' ),
                  'icon' => 'Nimble__heading_icon.svg'
                ),

                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_icon_module',
                  'title' => __( 'Icon', 'text_doma' ),
                  'icon' => 'Nimble__icon_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_button_module',
                  'title' => __( 'Button', 'text_doma' ),
                  'icon' => 'Nimble_button_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_map_module',
                  'title' => __( 'Map', 'text_doma' ),
                  'icon' => 'Nimble_map_icon.svg'
                ),

                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'two_columns',
                  'title' => __( 'Two Columns', 'text_doma' ),
                  'icon' => 'Nimble_2-columns_icon.svg'
                ),
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'three_columns',
                  'title' => __( 'Three Columns', 'text_doma' ),
                  'icon' => 'Nimble_3-columns_icon.svg'
                ),
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'four_columns',
                  'title' => __( 'Four Columns', 'text_doma' ),
                  'icon' => 'Nimble_4-columns_icon.svg'
                ),

                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_simple_html_module',
                  'title' => __( 'Html Content', 'text_doma' ),
                  'icon' => 'Nimble_html_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_quote_module',
                  'title' => __( 'Quote', 'text_doma' ),
                  'icon' => 'Nimble_quote_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_spacer_module',
                  'title' => __( 'Spacer', 'text_doma' ),
                  'icon' => 'Nimble__spacer_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_divider_module',
                  'title' => __( 'Divider', 'text_doma' ),
                  'icon' => 'Nimble__divider_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_simple_form_module',
                  'title' => __( 'Simple Contact Form', 'text_doma' ),
                  'icon' => 'Nimble_contact-form_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_widget_area_module',
                  'title' => __( 'WordPress widget area', 'text_doma' ),
                  'font_icon' => '<i class="fab fa-wordpress-simple"></i>',
                  'active' => sek_is_header_footer_enabled()
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_menu_module',
                  'title' => __( 'Menu', 'text_doma' ),
                  'font_icon' => '<i class="material-icons">menu</i>',
                  'active' => sek_is_header_footer_enabled()
                ),

                // array(
                //   'content-type' => 'module',
                //   'content-id' => 'czr_special_img_module',
                //   'title' => __( 'Nimble Image', 'text_doma' ),
                //   'font_icon' => '<i class="material-icons">all_out</i>',
                //   'active' => sek_is_pro()
                // )
                // array(
                //   'content-type' => 'module',
                //   'content-id' => 'czr_featured_pages_module',
                //   'title' => __( 'Featured pages',  'text_doma' ),
                //   'icon' => 'Nimble__featured_icon.svg'
                // ),


            ];

            $i = 0;
            foreach( $content_collection as $_params ) {
                // if ( $i % 2 == 0 ) {
                //   //printf('<div class="sek-module-raw"></div');
                // }
                $_params = wp_parse_args( $_params, array(
                    'content-type' => 'module',
                    'content-id' => '',
                    'title' => '',
                    'icon' => '',
                    'font_icon' => '',
                    'active' => true
                ));

                $icon_img_html = '<i style="color:red">Missing Icon</i>';
                if ( !empty( $_params['icon'] ) ) {
                    $icon_img_src = NIMBLE_BASE_URL . '/assets/czr/sek/icons/modules/' . $_params['icon'];
                    $icon_img_html = '<img draggable="false" title="'. $_params['title'] . '" alt="'. $_params['title'] . '" class="nimble-module-icons" src="' . $icon_img_src .'"/>';
                } else if ( !empty( $_params['font_icon'] ) ) {
                    $icon_img_html = $_params['font_icon'];
                }

                printf('<div draggable="%7$s" data-sek-content-type="%1$s" data-sek-content-id="%2$s" title="%5$s"><div class="sek-module-icon %6$s">%3$s</div><div class="sek-module-title"><div class="sek-centered-module-title">%4$s</div></div></div>',
                      $_params['content-type'],
                      $_params['content-id'],
                      $icon_img_html,
                      $_params['title'],
                      true === $_params['active'] ? __('Drag and drop or double-click to insert in your chosen target element.', 'text_doma' ) : __('Available soon ! This module is currently in beta, you can activate it in Site Wide Options > Beta features', 'text_doma'),
                      !empty( $_params['font_icon'] ) ? 'is-font-icon' : '',
                      true === $_params['active'] ? 'true' : 'false'
                );
            }
          ?>
        </div>
    <?php
}









/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___section_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = array();
            switch( $input_id ) {
                case 'intro_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'intro_three',
                            'title' => __('1 columns, call to action, full-width background', 'text-domain' ),
                            'thumb' => 'intro_three.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'intro_one',
                            'title' => __('1 column, full-width background', 'text-domain' ),
                            'thumb' => 'intro_one.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'intro_two',
                            'title' => __('2 columns, call to action, full-width background', 'text-domain' ),
                            'thumb' => 'intro_two.jpg'
                        )
                    );
                break;
                case 'features_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'features_one',
                            'title' => __('3 columns with icon and call to action', 'text-domain' ),
                            'thumb' => 'features_one.jpg',
                            //'height' => '188px'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'features_two',
                            'title' => __('3 columns with icon', 'text-domain' ),
                            'thumb' => 'features_two.jpg',
                            //'height' => '188px'
                        )
                    );
                break;
                case 'contact_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'contact_one',
                            'title' => __('A contact form and a Google map', 'text-domain' ),
                            'thumb' => 'contact_one.jpg',
                            //'height' => '188px'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'contact_two',
                            'title' => __('A contact form with an image background', 'text-domain' ),
                            'thumb' => 'contact_two.jpg',
                            //'height' => '188px'
                        )
                    );
                break;
                case 'layout_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'two_columns',
                            'title' => __('two columns layout', 'text-domain' ),
                            'thumb' => 'two_columns.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'three_columns',
                            'title' => __('three columns layout', 'text-domain' ),
                            'thumb' => 'three_columns.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'four_columns',
                            'title' => __('four columns layout', 'text-domain' ),
                            'thumb' => 'four_columns.jpg'
                        ),
                    );
                break;
                case 'header_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'header_one',
                            'title' => __('simple header with a logo on the right, menu on the left', 'text-domain' ),
                            'thumb' => 'header_one.jpg',
                            'height' => '33px'
                        )
                    );
                break;
                case 'footer_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'footer_one',
                            'title' => __('simple footer with 3 columns and large bottom zone', 'text-domain' ),
                            'thumb' => 'footer_one.jpg'
                        )
                    );
                break;
            }
            foreach( $content_collection as $_params) {
                $section_type = 'content';
                if ( false !== strpos($_params['content-id'], 'header_') ) {
                    $section_type = 'header';
                } else if ( false !== strpos($_params['content-id'], 'footer_') ) {
                    $section_type = 'footer';
                }

                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s" style="%3$s" title="%4$s" data-sek-section-type="%5$s"><div class="sek-overlay"></div></div>',
                    $_params['content-type'],
                    $_params['content-id'],
                    sprintf( 'background: url(%1$s) 50% 50% / cover no-repeat;%2$s',
                        // v1.4.2 : added the ?ver param to make sure we always display the latest shot of the section
                        NIMBLE_BASE_URL . '/assets/img/section_assets/thumbs/' . $_params['thumb'] . '?ver=' . NIMBLE_VERSION,
                        isset( $_params['height'] ) ? 'height:'.$_params['height'] : ''
                    ),
                    $_params['title'],
                    $section_type
                );
            }
          ?>
        </div>
  <?php
}

?>