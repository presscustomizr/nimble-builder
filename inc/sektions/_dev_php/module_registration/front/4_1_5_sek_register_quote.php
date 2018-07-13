<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER QUOTE MODULE
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
function sek_get_module_params_for_czr_quote_module() {
    $quote_font_selectors = array( '.sek-quote-content', '.sek-quote-content p', '.sek-quote-content ul', '.sek-quote-content ol', '.sek-quote-content a' );
    $cite_font_selectors  = array( '.sek-cite', '.sek-cite a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_module',
        'name' => __('Quote', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_callback__czr_quote_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'quote_text'  => 'Hey, careful, man, there\'s a beverage here!',
            'cite_text'   => 'The Dude in <a href="https://www.imdb.com/title/tt0118715/quotes/qt0464770" rel="nofollow noopener noreferrer" target="_blank">The Big Lebowski</a>'
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Quote', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'quote_text' => array(
                                'input_type'         => 'textarea',
                                'title'              => __( 'Quote text', 'text_domain_to_be_replaced' ),
                                'default'            => '',
                                'width-100'         => true,
                                'notice_before'      => __( 'You may use some html tags like a, br,p, div, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),
                            ),
                            'quote_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $quote_font_selectors,
                            ),
                            'quote_font_size_css'       => array(
                                'input_type'  => 'font_size',
                                'title'       => __( 'Font size in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $quote_font_selectors,
                            ),//16,//"14px",
                            'quote_line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __( 'Line height in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '24px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $quote_font_selectors,
                            ),//24,//"20px",
                            'quote_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $quote_font_selectors,
                            ),//null,
                            'quote_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $quote_font_selectors,
                            ),//null,
                            'quote_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $quote_font_selectors,
                            ),//null,
                            'quote_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $quote_font_selectors,
                            ),//null,
                            'quote_letter_spacing_css'  => array(
                                'input_type'  => 'number',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $quote_font_selectors,
                            ),//0,
                            'quote_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $quote_font_selectors,
                            ),//"#000000",
                            'quote_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $quote_font_selectors,
                            ),//"#000000",
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'quote___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
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
                            ),
                        )

                    ),
                    array(
                        'title' => __( 'Cite', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'cite_text' => array(
                                'input_type'         => 'textarea',
                                'title'              => __( 'Cite text', 'text_domain_to_be_replaced' ),
                                'default'            => '',
                                'width-100'         => true,
                                'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),
                            ),
                            'cite_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $cite_font_selectors,
                            ),
                            'cite_font_size_css'       => array(
                                'input_type'  => 'font_size',
                                'title'       => __( 'Font size in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $cite_font_selectors,
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __( 'Line height in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '24px',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $cite_font_selectors,
                            ),//24,//"20px",
                            'cite_font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $cite_font_selectors,
                            ),//null,
                            'cite_font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $cite_font_selectors,
                            ),//null,
                            'cite_text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $cite_font_selectors,
                            ),//null,
                            'cite_text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $cite_font_selectors,
                            ),//null,
                            'cite_letter_spacing_css'  => array(
                                'input_type'  => 'number',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $cite_font_selectors,
                            ),//0,
                            'cite_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $cite_font_selectors,
                            ),//"#000000",
                            'cite_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $cite_font_selectors,
                            ),//"#000000",
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'cite___flag_important'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
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
                            ),
                        ),
                    ),
                    array(
                        'title' => __( 'Design', 'text_domain_to_be_replaced' ),
                        'inputs' => array(
                            'quote_design' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Design', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                            ),
                            'border_width_css' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __( 'Border weight', 'text_domain_to_be_replaced' ),
                                'min' => 1,
                                'max' => 80,
                                'unit' => 'px',
                                'default' => 5,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                            ),
                            'border_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border Color', 'text_domain_to_be_replaced' ),
                                'width-100'   => true,
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                            ),
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/quote_module_tmpl.php",
    );
}




function sanitize_callback__czr_quote_module( $value ) {
    if ( array_key_exists( 'quote_text', $value ) ) {
        //sanitize quote_text
        $value[ 'quote_text' ] = czr_quote_module_kses_quote_text( $value[ 'quote_text' ] );
    }
    if ( array_key_exists( 'cite_text', $value ) ) {
        //sanitize cite_text
        $value[ 'cite_text' ] = czr_quote_module_kses_cite_text( $value[ 'cite_text' ] );
    }
    return $value;
}


/**
 * Filter quote text output WordPress's KSES API.
 */
function czr_quote_module_kses_quote_text( $content = '' ) {
    $allowed_tags = wp_kses_allowed_html( 'sek-quote_text' );

    // Return KSES'ed content, allowing the above tags.
    return wp_kses( $content, $allowed_tags );
}


/**
 * Filter cite text output WordPress's KSES API.
 */
function czr_quote_module_kses_cite_text( $content = '' ) {
    $allowed_tags = wp_kses_allowed_html( 'sek-cite_text' );

    // Return KSES'ed content, allowing the above tags.
    return wp_kses( $content, $allowed_tags );
}



add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\czr_quote_module_quote_text_get_allowedtags', 10, 2 );
add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\czr_quote_module_cite_text_get_allowedtags', 10, 2 );
/**
 * Get quote text allowed tags
 * blockquote specifications:
 * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/blockquote
 * can contain: https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#Flow_content
 *
 * here we use just a subset of the allowed tags in a blockquote
 */
function czr_quote_module_quote_text_get_allowedtags( $tags, $context ) {
    if ( 'sek-quote_text' != $context ) {
        return $tags;
    }

    // limit wp_kses allowed tags.
    return array(
        'a' => array(
            'href' => 1,
            'rel' => 1,
            'rev' => 1,
            'name' => 1,
            'target' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'abbr' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'b' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'big' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'br' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'button' => array(
            'disabled' => 1,
            'name' => 1,
            'type' => 1,
            'value' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'code' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'del' => array(
            'datetime' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'em' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'i' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ins' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'li' => array(
            'align' => 1,
            'value' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'p' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'q' => array(
            'cite' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'section' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'small' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'span' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strike' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strong' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sub' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sup' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'u' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ol' => array(
            'start' => 1,
            'type' => 1,
            'reversed' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ul' => array(
            'type' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
    );
}


/**
 * Get quote text allowed tags
 * cite specifications:
 * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/cite
 * can contain: https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_categories#Phrasing_content
 *
 * here we use just a subset of the allowed tags in a cite
 */
function czr_quote_module_cite_text_get_allowedtags( $tags, $context ) {
    if ( 'sek-cite_text' != $context ) {
        return $tags;
    }


    // limit wp_kses allowed tags.
    return array(
        'a' => array(
            'href' => 1,
            'rel' => 1,
            'rev' => 1,
            'name' => 1,
            'target' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'abbr' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'b' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'big' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'br' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'button' => array(
            'disabled' => 1,
            'name' => 1,
            'type' => 1,
            'value' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'code' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'del' => array(
            'datetime' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'em' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'i' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'ins' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'span' => array(
            'dir'   => 1,
            'align' => 1,
            'lang'  => 1,
            'xml:lang' => 1,
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'small' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strike' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'strong' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sub' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'sup' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
        'u' => array(
            'class' => 1,
            'id' => 1,
            'style' => 1,
            'title' => 1,
            'role' => 1,
        ),
    );
}
?>
