<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER HEADING MODULE
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
function sek_get_module_params_for_czr_heading_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_module',
        'name' => __( 'Heading', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_heading_module',
        'validate_callback' => '\Nimble\validate_callback__czr_heading_module',
        'starting_value' => array(
            'heading_text' => 'This is a heading.'
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Heading', 'text_domain_to_be_replaced' ),
                        //'attributes' => 'data-sek-device="desktop"',
                        'inputs' => array(
                            'heading_text' => array(
                                'input_type'         => 'textarea',
                                'title'              => __( 'Heading text', 'text_domain_to_be_replaced' ),
                                'default'            => '',
                                // The following might be useful to me, but it generates a pretty long list of allowed HTML tags
                                // would be great if we could have a "collapsible notice", collapsed by default that will expand on click
                                // similar to the section description used by wp, e.g. in the Additional CSS section
                                // 'notice_before'      => sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ),
                                //     '<code>' . czr_heading_module_text_allowed_tags() . '</code>', 'text_domain_to_be_replaced' ),

                            ),
                            'heading_tag' => array(
                                'input_type'         => 'select',
                                'title'              => __( 'Heading tag', 'text_domain_to_be_replaced' ),
                                'default'            => 'h1'
                            ),
                            'h_alignment_css'        => array(
                                'input_type'         => 'h_text_alignment',
                                'title'              => __( 'Alignment', 'text_domain_to_be_replaced' ),
                                'default'            => is_rtl() ? 'right' : 'left',
                                'refresh-markup'     => false,
                                'refresh-stylesheet' => true
                            ),
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
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                                'refresh-fonts' => true,
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'font_size',
                                'title'       => __( 'Font size in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '16px',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'line_height',
                                'title'       => __( 'Line height in pixels', 'text_domain_to_be_replaced' ),
                                'default'     => '24px',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//24,//"20px",
                            'font_weight_css'     => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font weight', 'text_domain_to_be_replaced' ),
                                'default'     => 400,
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//null,
                            'font_style_css'      => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Font style', 'text_domain_to_be_replaced' ),
                                'default'     => 'inherit',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                            ),//null,
                            'text_decoration_css' => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text decoration', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//null,
                            'text_transform_css'  => array(
                                'input_type'  => 'select',
                                'title'       => __( 'Text transform', 'text_domain_to_be_replaced' ),
                                'default'     => 'none',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//null,

                            'letter_spacing_css'  => array(
                                'input_type'  => 'number',
                                'title'       => __( 'Letter spacing', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//0,
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                                'width-100'   => true
                            ),//"#000000",
                            'color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_domain_to_be_replaced' ),
                                'default'     => '',
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100'
                            ),//"#000000",
                            'important_css'       => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_domain_to_be_replaced' ),
                                'default'     => 0,
                                'refresh-markup' => false,
                                'refresh-stylesheet' => true
                            ),//false
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/heading_module_tmpl.php",
    );
}

function sanitize_callback__czr_heading_module( $value ) {
    if ( array_key_exists('heading_text', $value ) ) {
        //sanitize heading_text
        $value[ 'heading_text' ] = czr_heading_module_kses_text( $value[ 'heading_text' ] );
    }
    return $value;
    //return new \WP_Error('required' ,'heading did not pass sanitization');
}

// @see SEK_CZR_Dyn_Register::set_dyn_setting_args
function validate_callback__czr_heading_module( $value ) {
    //return new \WP_Error('required' ,'heading did not pass ');
    return $value;
}

/**
 * Filter headings text output WordPress's KSES API.
 */
function czr_heading_module_kses_text( $content = '' ) {
    $allowed_tags = czr_heading_module_text_get_allowedtags();

    // Return KSES'ed content, allowing the above tags.
    return wp_kses( $content, $allowed_tags );
}


/**
 * Get headings text allowed tags
 */
function czr_heading_module_text_get_allowedtags() {
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

/**
 * Display all of the allowed tags in HTML format with attributes.
 *
 * This is useful for displaying which elements and attributes are supported
 * see wp-includes/general-template::allowed_tags()
 */
function czr_heading_module_text_allowed_tags() {
    $allowedtags = czr_heading_module_text_get_allowedtags();

    foreach ( (array) $allowedtags as $tag => $attributes ) {
        $allowed .= '<'.$tag;
        if ( 0 < count($attributes) ) {
            foreach ( $attributes as $attribute => $limits ) {
                $allowed .= ' '.$attribute.'=""';
            }
        }
        $allowed .= '> ';
    }
    return htmlentities( $allowed );
}
?>
