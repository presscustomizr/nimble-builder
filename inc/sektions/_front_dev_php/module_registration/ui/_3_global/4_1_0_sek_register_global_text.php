<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_text() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_text',
        'name' => __('Global text', 'text_doma'),
        // 'starting_value' => array(
        //     'global_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'default_font_family' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'refresh_preview' => false,
                    'html_before' => '<h3>' . __('GLOBAL TEXT STYLE') .'</h3>'
                ),
                'default_font_size'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                ),//16,//"14px",
                'default_line_height'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                ),//24,//"20px",
                'default_color'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'width-100'   => true,
                    'notice_before' => __('Inherits your active theme\'s option when not set.', 'text_doma')
                ),//"#000000",

                'links_color'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Links color', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'width-100'   => true,
                    'notice_before' => __('Inherits your active theme\'s option when not set.', 'text_doma'),
                    'html_before' => '<hr/><h3>' . __('GLOBAL STYLE OPTIONS FOR LINKS') .'</h3>'
                ),//"#000000",
                'links_color_hover'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Links color on mouse hover', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'width-100'   => true,
                    'notice_before' => __('Inherits your active theme\'s option when not set.', 'text_doma'),
                    'title_width' => 'width-100'
                ),//"#000000",
                'links_underlining'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link underlining', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'choices'            => array(
                        'inherit' => __('Inherit theme', 'text_doma'),
                        'underlined' => __( 'Underlined', 'text_doma'),
                        'not_underlined' => __( 'Not underlined', 'text_doma'),
                    )
                ),//null,
                'links_underlining_hover'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link underlining on mouse hover', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_stylesheet' => true,
                    'refresh_preview' => false,
                    'choices'            => array(
                        'inherit' => __('Inherit theme', 'text_doma'),
                        'underlined' => __( 'Underlined', 'text_doma'),
                        'not_underlined' => __( 'Not underlined', 'text_doma'),
                    )
                ),//null,

                'headings_font_family' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'refresh_preview' => false,
                    'html_before' => '<hr/><h3>' . __('GLOBAL STYLE OPTIONS FOR HEADINGS') .'</h3>'
                ),
            )
        )//tmpl
    );
}



// Nimble implements an inheritance for both logic, determined by the css selectors, and the media query rules.
// For example, an inner width of 85% applied for skope will win against the global one, but can be overriden by a specific inner width set at a section level.
add_filter( 'nimble_get_dynamic_stylesheet', '\Nimble\sek_add_raw_global_text_css', 10, 2 );
// @filter 'nimble_get_dynamic_stylesheet'
// this filter is declared in Sek_Dyn_CSS_Builder::get_stylesheet() with 2 parameters
// apply_filters( 'nimble_get_dynamic_stylesheet', $css, $this->is_global_stylesheet );
function sek_add_raw_global_text_css( $css, $is_global_stylesheet ) {
    // the global text rules must be restricted to the local stylesheet
    if ( !$is_global_stylesheet )
      return $css;

    $css = is_string( $css ) ? $css : '';

    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    if ( ! is_array( $global_options ) || empty( $global_options['global_text'] ) || !is_array( $global_options['global_text'] ) )
      return $css;

    $text_options = $global_options['global_text'];
    if ( ! is_array( $text_options  ) )
      return $css;

    $rules = array();
    // SELECTORS
    $default_text_selector = '.sektion-wrapper [data-sek-level], [data-sek-level] p, [data-sek-level] .sek-btn';
    $links_selector = '.sektion-wrapper [data-sek-level] a';
    $links_hover_selector = '.sektion-wrapper [data-sek-level] a:hover';
    $headings_selector = '.sektion-wrapper [data-sek-level] h1, .sektion-wrapper [data-sek-level] h2, .sektion-wrapper [data-sek-level] h3, .sektion-wrapper [data-sek-level] h4, .sektion-wrapper [data-sek-level] h5, .sektion-wrapper [data-sek-level] h6';

    // DEFAULT TEXT OPTIONS
    // Font Family
    if ( !empty( $text_options['default_font_family'] ) ) {
        $rules[] = array(
            'selector'    => $default_text_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'font-family', sek_extract_css_font_family_from_customizer_option( $text_options['default_font_family'] ) ),
            'mq'          => null
        );
    }
    // Font size by devices
    // @see sek_add_css_rules_for_css_sniffed_input_id()
    if ( !empty( $text_options['default_font_size'] ) ) {
        $default_font_size = $text_options['default_font_size'];
        $default_font_size = !is_array($default_font_size) ? array() : $default_font_size;
        $default_font_size = wp_parse_args( $default_font_size, array(
            'desktop' => '16px',
            'tablet' => '',
            'mobile' => ''
        ));
        $rules = sek_set_mq_css_rules( array(
            'value' => $default_font_size,
            'css_property' => 'font-size',
            'selector' => $default_text_selector,
            'is_important' => false,
        ), $rules );
    }
    // Line height
    if ( !empty( $text_options['default_line_height'] ) ) {
        $rules[] = array(
            'selector'    => $default_text_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'line-height', $text_options['default_line_height'] ),
            'mq'          => null
        );
    }
    // Color
    if ( !empty( $text_options['default_color'] ) ) {
        $rules[] = array(
            'selector'    => $default_text_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'color', $text_options['default_color'] ),
            'mq'          => null
        );
    }

    // LINKS OPTIONS
    // Color
    if ( !empty( $text_options['links_color'] ) ) {
        $rules[] = array(
            'selector'    => $links_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'color', $text_options['links_color'] ),
            'mq'          => null
        );
    }
    // Color on hover
    if ( !empty( $text_options['links_color_hover'] ) ) {
        $rules[] = array(
            'selector'    => $links_hover_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'color', $text_options['links_color_hover'] ),
            'mq'          => null
        );
    }
    // Underline
    if ( !empty( $text_options['links_underlining'] ) && 'inherit' !== $text_options['links_underlining'] ) {
        $rules[] = array(
            'selector'    => $links_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'text-decoration', 'underlined' === $text_options['links_underlining'] ? 'underline' : 'solid' ),
            'mq'          => null
        );
    }
    // Underline on hover
    if ( !empty( $text_options['links_underlining_hover'] ) && 'inherit' !== $text_options['links_underlining_hover'] ) {
        $rules[] = array(
            'selector'    => $links_hover_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'text-decoration', 'underlined' === $text_options['links_underlining_hover'] ? 'underline' : 'solid' ),
            'mq'          => null
        );
    }

    // HEADINGS OPTIONS
    // Font Family
    if ( !empty( $text_options['headings_font_family'] ) ) {
        $rules[] = array(
            'selector'    => $headings_selector,
            'css_rules'   => sprintf( '%1$s:%2$s;', 'font-family', sek_extract_css_font_family_from_customizer_option( $text_options['headings_font_family'] ) ),
            'mq'          => null
        );
    }

    sek_error_log('ALORS text_options ?', $text_options);


    $global_text_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    //sek_error_log('ALORS CSS ?', $global_text_options_css );

    return is_string( $global_text_options_css ) ? $css . $global_text_options_css : $css;

    // // Note that the option 'outer-section-width' and 'inner-section-width' can be empty when set to a value === default
    // // @see js czr_setions::normalizeAndSanitizeSingleItemInputValues()
    // foreach ( $user_defined_widths as $width_opt_name => $selector ) {
    //     if ( ! empty( $width_options[ $width_opt_name ] ) && ! is_array( $width_options[ $width_opt_name ] ) ) {
    //         sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
    //     }
    //     // $width_options[ $width_opt_name ] should be an array( {device} => {number}{unit} )
    //     // If not set in the width options , it means that it is equal to default
    //     $user_custom_width_value = ( empty( $width_options[ $width_opt_name ] ) || ! is_array( $width_options[ $width_opt_name ] ) ) ? array('desktop' => '100%') : $width_options[ $width_opt_name ];
    //     $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
    //         'desktop' => '100%',
    //         'tablet' => '',
    //         'mobile' => ''
    //     ));
    //     $max_width_value = $user_custom_width_value;
    //     $margin_value = array();

    //     foreach ( $user_custom_width_value as $device => $num_unit ) {
    //         $numeric = sek_extract_numeric_value( $num_unit );
    //         if ( ! empty( $numeric ) ) {
    //             $unit = sek_extract_unit( $num_unit );
    //             $max_width_value[$device] = $numeric . $unit;
    //             $margin_value[$device] = '0 auto';
    //             $padding_of_the_parent_container[$device] = 'inherit';
    //         }
    //     }

    //     $rules = sek_set_mq_css_rules(array(
    //         'value' => $max_width_value,
    //         'css_property' => 'max-width',
    //         'selector' => $selector
    //     ), $rules );

    //     // when customizing the inner section width, we need to reset the default padding rules for .sek-container-fluid {padding-right:10px; padding-left:10px}
    //     // @see assets/front/scss/_grid.scss
    //     if ( 'inner-section-width' === $width_opt_name ) {
    //         $rules = sek_set_mq_css_rules(array(
    //             'value' => $padding_of_the_parent_container,
    //             'css_property' => 'padding-left',
    //             'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid'
    //         ), $rules );
    //         $rules = sek_set_mq_css_rules(array(
    //             'value' => $padding_of_the_parent_container,
    //             'css_property' => 'padding-right',
    //             'selector' => '.sektion-wrapper [data-sek-level="section"] > .sek-container-fluid'
    //         ), $rules );
    //     }

    //     if ( ! empty( $margin_value ) ) {
    //         $rules = sek_set_mq_css_rules(array(
    //             'value' => $margin_value,
    //             'css_property' => 'margin',
    //             'selector' => $selector
    //         ), $rules );
    //     }
    // }//foreach

    // $width_options_css = Sek_Dyn_CSS_Builder::sek_generate_css_stylesheet_for_a_set_of_rules( $rules );

    // return is_string( $width_options_css ) ? $css . $width_options_css : $css;
}

?>