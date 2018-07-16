<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER BUTTON MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_button_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_button_module',
        'name' => __( 'Button', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            'button_text' => 'This is a button.',
            'color_css'  => '#ffffff',
            'bg_color_css' => '#020202',
            'bg_color_hover' => '#1c1c1c', //lighten 12%,
            'border_radius_css' => '2',
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-button' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Button', 'text_domain_to_be_replaced' ),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'button_text' => array(
                                'input_type'         => 'text',
                                'title'              => __( 'Button text', 'text_domain_to_be_replaced' ),
                                'default'            => '',
                                'width-100'         => true,
                            ),
                            'link-to' => array(
                                'input_type'  => 'select',
                                'title'       => __('Link to', 'text_domain_to_be_replaced'),
                                'default'     => 'no-link',
                                'choices'     => sek_get_select_options_for_input_id( 'link-to' )
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
                                'default'     => false
                            ),
                            'icon' => array(
                                'input_type'  => 'fa_icon_picker',
                                'title'       => __( 'Select an Icon that will appear before the button text', 'text_domain_to_be_replaced' ),
                                //'default'     => 'no-link'
                            ),
                            'bg_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Background color', 'text_domain_to_be_replaced' ),
                                'width-100'   => true,
                                'default'    => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors'=> $css_selectors
                            ),
                            'use_custom_bg_color_on_hover' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Set a custom background color on mouse hover', 'text_domain_to_be_replaced' ),
                                'title_width' => 'width-100',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'default'     => 0,
                            ),
                            'bg_color_hover' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Background color on mouse hover', 'text_domain_to_be_replaced' ),
                                'width-100'   => true,
                                'default'    => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                //'css_identifier' => 'background_color_hover',
                                'css_selectors'=> $css_selectors
                            ),
                            'border_radius_css'       => array(
                                'input_type'  => 'number',
                                'title'       => __( 'Rounded corners in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '2',
                                'min'         => '0',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_radius',
                                'css_selectors'=> $css_selectors
                            ),//16,//"14px",
                        )
                    ),
                    array(
                        'title' => __( 'Font style', 'text_domain_to_be_replaced' ),
                        'attributes' => 'data-sek-google-font-tab="true"',
                        'inputs' => array(
                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $css_font_selectors
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'font_size',
                                'title'       => __( 'Font size', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $css_font_selectors
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __( 'Line height', 'text_domain_to_be_replaced' ),
                                'default'     => '1.5em',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $css_font_selectors
                            ),//24,//"20px",
                            'font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' ),
                                'css_selectors' => $css_font_selectors
                            ),//null,
                            'font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' ),
                                'css_selectors' => $css_font_selectors
                            ),//null,
                            'text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' ),
                                'css_selectors' => $css_font_selectors . ' .sek-btn-text'
                            ),//null,
                            'text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' ),
                                'css_selectors' => $css_font_selectors . ' .sek-btn-text'
                            ),//null,

                            'letter_spacing_css'  => array(
                                'input_type'  => 'number',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $css_font_selectors
                            ),//0,
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $css_font_selectors
                            ),//"#000000",
                            'color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $css_font_selectors
                            ),//"#000000",
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'button___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'font_family_css',
                                    'font_size_css',
                                    'line_height_css',
                                    'font_weight_css',
                                    'font_style_css',
                                    'text_decoration_css',
                                    'text_transform_css',
                                    'letter_spacing_css',
                                    'color_css',
                                    'color_hover_css'
                                )
                            ),
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/button_module_tmpl.php",
    );
}


function sanitize_callback__czr_button_module( $value ) {
    $value[ 'button_text' ] = sanitize_text_field( $value[ 'button_text' ] );
    return $value;
}


/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_modules', '\Nimble\sek_add_css_rules_for_button_front_module', 10, 3 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_button_front_module( $rules, $complete_modul_model ) {
    if ( !is_array( $complete_modul_model ) || empty( $complete_modul_model['module_type'] ) || 'czr_button_module' !== $complete_modul_model['module_type'] )
      return $rules;

    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $bg_color = $value['bg_color_css'];
    if ( sek_booleanize_checkbox_val( $value['use_custom_bg_color_on_hover'] ) ) {
        $bg_color_hover = $value['bg_color_hover'];
    } else {
        // Build the lighter rgb from the user picked bg color
        if ( 0 === strpos( $bg_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
            $darken_rgb          = sek_lighten_rgb( $rgb, $percent=12, $array = true );
            $bg_color_hover      = sek_rgb2rgba( $darken_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
            $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=12 );
        } else {
            $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=12 );
        }
    }
    $rules[] = array(
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-btn:hover',
        'css_rules' => 'background-color:' . $bg_color_hover . ';',
        'mq' =>null
    );
    return $rules;
}

?>
