<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER QUOTE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_quote_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_module',
        'is_father' => true,
        'children' => array(
            'quote_content' => 'czr_quote_quote_child',
            'cite_content' => 'czr_quote_cite_child',
            'design' => 'czr_quote_design_child'
        ),
        'name' => __('Quote', 'text_doma' ),
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_callback__czr_quote_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'quote_content' => array(
                'quote_text'  => __('Hey, careful, man, there\'s a beverage here!','text_doma'),
            ),
            'cite_content' => array(
                'cite_text'   => sprintf( __('The Dude in %1s', 'text_doma'), '<a href="https://www.imdb.com/title/tt0118715/quotes/qt0464770" rel="nofollow noopener noreferrer" target="_blank">The Big Lebowski</a>' ),
                'cite_font_style_css' => 'italic',
            ),
            'design' => array(
                'quote_design' => 'border-before'
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => "quote_module_tmpl.php",
        'front_assets' => array(
              'czr-font-awesome' => array(
                  'type' => 'css',
                  'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
              )
        )
    );
}








/* ------------------------------------------------------------------------- *
 *  QUOTE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_quote_child() {
    $quote_font_selectors = array( '.sek-quote .sek-quote-content', '.sek-quote .sek-quote-content *');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_quote_child',
        'name' => __( 'Quote content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'quote_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                    ),
                    'title'             => __( 'Main quote content', 'text_doma' ),
                    'default'           => '',
                    'width-100'         => true,
                    //'notice_before'     => __( 'You may use some html tags like a, br,p, div, span with attributes like style, id, class ...', 'text_doma'),
                    'refresh_markup'    => '.sek-quote-content'
                ),
                'quote_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $quote_font_selectors,
                ),
                'quote_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $quote_font_selectors,
                ),//16,//"14px",
                'quote_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $quote_font_selectors,
                ),//24,//"20px",
                'quote_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font weight', 'text_doma' ),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'quote_font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font style', 'text_doma' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'quote_text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text decoration', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'quote_text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text transform', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'quote_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $quote_font_selectors,
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'quote___flag_important'       => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see sek_is_flagged_important
                    'important_input_list' => array(
                        'quote_font_family_css',
                        'quote_font_size_css',
                        'quote_line_height_css',
                        'quote_font_weight_css',
                        'quote_font_style_css',
                        'quote_text_decoration_css',
                        'quote_text_transform_css',
                        'quote_letter_spacing_css',
                        'quote_color_css',
                        'quote_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}





/* ------------------------------------------------------------------------- *
 *  CITE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_cite_child() {
    $cite_font_selectors  = array( '.sek-cite', '.sek-cite *');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_cite_child',
        'name' => __( 'Cite content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'cite_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                        'height' => 50
                    ),
                    'refresh_markup' => '.sek-cite',
                    'title'              => __( 'Cite text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    //'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                ),
                'cite_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $cite_font_selectors,
                ),
                'cite_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    'default'     => array( 'desktop' => '13px' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $cite_font_selectors,
                ),//16,//"14px",
                'cite_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $cite_font_selectors,
                ),//24,//"20px",
                'cite_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font weight', 'text_doma' ),
                    'default'     => 'normal',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'cite_font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font style', 'text_doma' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $cite_font_selectors,
                    'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'cite_text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text decoration', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'cite_text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text transform', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'cite_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $cite_font_selectors,
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'cite___flag_important'       => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see Nsek_is_flagged_important
                    'important_input_list' => array(
                        'cite_font_family_css',
                        'cite_font_size_css',
                        'cite_line_height_css',
                        'cite_font_weight_css',
                        'cite_font_style_css',
                        'cite_text_decoration_css',
                        'cite_text_transform_css',
                        'cite_letter_spacing_css',
                        'cite_color_css',
                        'cite_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}










/* ------------------------------------------------------------------------- *
 *  DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_design_child() {
    $cite_font_selectors  = array( '.sek-quote-design .sek-cite', '.sek-quote-design .sek-cite a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_design_child',
        'name' => __( 'Design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'quote_design' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Design', 'text_doma' ),
                    'default'     => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'quote_design' )
                ),
                'border_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Border weight', 'text_doma' ),
                    'min' => 1,
                    'max' => 80,
                    'default' => '5px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_width',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'border_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Border Color', 'text_doma' ),
                    'width-100'   => true,
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'icon_size_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Icon Size', 'text_doma' ),
                    'default'     => '32px',
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => array( '.sek-quote.sek-quote-design.sek-quote-icon-before::before', '.sek-quote.sek-quote-design.sek-quote-icon-before' )
                ),
                'icon_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Icon Color', 'text_doma' ),
                    'width-100'   => true,
                    'default'     => '#ccc',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-quote-icon-before::before'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}










function sanitize_callback__czr_quote_module( $value ) {
    if ( !current_user_can( 'unfiltered_html' ) ) {
        if ( array_key_exists( 'quote_text', $value ) ) {
            //sanitize quote_text
            $value[ 'quote_text' ] = wp_kses_post( $value[ 'quote_text' ] );
        }
        if ( array_key_exists( 'cite_text', $value ) ) {
            //sanitize cite_text
            $value[ 'cite_text' ] = wp_kses_post( $value[ 'cite_text' ] );
        }
    }
    return $value;
}

?>
