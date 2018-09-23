<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMAGE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_image_module() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',
        'name' => __('Image', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png',
            'custom_width' => ''
        ),
        // 'sanitize_callback' => '\Nimble\czr_image_module_sanitize_validate',
        // 'validate_callback' => '\Nimble\czr_image_module_sanitize_validate',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Content', 'text_domain_to_be_replaced' ),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'img' => array(
                                'input_type'  => 'upload',
                                'title'       => __('Pick an image', 'text_domain_to_be_replaced'),
                                'default'     => ''
                            ),
                            'img-size' => array(
                                'input_type'  => 'select',
                                'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                                'default'     => 'large',
                                'choices'     => sek_get_select_options_for_input_id( 'img-size' )
                            ),
                                                        'link-to' => array(
                                'input_type'  => 'select',
                                'title'       => __('Link to', 'text_domain_to_be_replaced'),
                                'default'     => 'no-link',
                                'choices'     => sek_get_select_options_for_input_id( 'img-link-to' )
                            ),
                            'link-pick-url' => array(
                                'input_type'  => 'content_picker',
                                'title'       => __('Link url', 'text_domain_to_be_replaced'),
                                'default'     => array()
                            ),
                            'link-custom-url' => array(
                                'input_type'  => 'text',
                                'title'       => __('Custom link url', 'text_domain_to_be_replaced'),
                                'default'     => ''
                            ),
                            'link-target' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Open link in a new page', 'text_domain_to_be_replaced'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                            ),
                            'h_alignment_css' => array(
                                'input_type'  => 'h_alignment',
                                'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                                'default'     => 'center',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment'
                            )


                            // 'lightbox' => array(
                            //     'input_type'  => 'gutencheck',
                            //     'title'       => __('Activate a lightbox on click', 'text_domain_to_be_replaced'),
                            //     'title_width' => 'width-80',
                            //     'input_width' => 'width-20',
                            //     'default'     => 'center'
                            // ),
                        )
                    ),
                    array(
                        'title' => __( 'Style', 'text_domain_to_be_replaced' ),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'border_width_css' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Border weight', 'text_domain_to_be_replaced' ),
                                'min' => 0,
                                'max' => 80,
                                'default' => '',
                                'width-100'   => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => $css_selectors
                            ),
                            'border_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border Color', 'text_domain_to_be_replaced' ),
                                'width-100'   => true,
                                'default'     => '#f2f2f2',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => $css_selectors
                            ),
                            'border_radius_css'       => array(
                                'input_type'  => 'border_radius',
                                'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                                'default' => array( '_all_' => '0px' ),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'min'         => 0,
                                'max'         => 500,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_radius',
                                'css_selectors'=> $css_selectors
                            ),
                            'use_custom_width' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Custom image width', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_stylesheet' => true
                            ),
                            'custom_width' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __('Width', 'text_domain_to_be_replaced'),
                                'min' => 1,
                                'max' => 100,
                                //'unit' => '%',
                                'default' => '',
                                'max'     => 500,
                                'width-100'   => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true
                            ),
                            'use_box_shadow' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Apply a shadow', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                            ),
                            'img_hover_effect' => array(
                                'input_type'  => 'select',
                                'title'       => __('Mouse over effect', 'text_domain_to_be_replaced'),
                                'default'     => 'none',
                                'choices'     => sek_get_select_options_for_input_id( 'img_hover_effect' )
                            ),
                        )
                    )
                )//tabs
            )//item-inputs
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_image_module', '\Nimble\sek_add_css_rules_for_czr_image_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_image_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    if ( sek_booleanize_checkbox_val( $value['use_custom_width'] ) ) {
        $width = $value[ 'custom_width' ];
        $css_rules = '';
        if ( isset( $width ) && FALSE !== $width ) {
            $numeric = sek_extract_numeric_value( $width );
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $width );
                $css_rules .= 'width:' . $numeric . $unit . ';';
            }
        }
        if ( !empty( $css_rules ) ) {
            $rules[] = array(
                'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                'css_rules' => $css_rules,
                'mq' =>null
            );
        }
    }

    return $rules;
}
?>