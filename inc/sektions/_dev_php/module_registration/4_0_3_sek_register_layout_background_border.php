<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_layout_bg_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_layout_bg_module',

        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __('Background', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'bg-color' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Background color', 'text_domain_to_be_replaced'),
                                'width-100'   => true
                            ),
                            'bg-image' => array(
                                'input_type'  => 'upload',
                                'title'       => __('Image', 'text_domain_to_be_replaced')
                            ),
                            'bg-position' => array(
                                'input_type'  => 'bg_position',
                                'title'       => __('Image position', 'text_domain_to_be_replaced'),
                                'default'     => 'center'
                            ),
                            // 'bg-parallax' => array(
                            //     'input_type'  => 'gutencheck',
                            //     'title'       => __('Parallax scrolling', 'text_domain_to_be_replaced')
                            // ),
                            'bg-attachment' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Fixed background', 'text_domain_to_be_replaced')
                            ),
                            // 'bg-repeat' => array(
                            //     'input_type'  => 'select',
                            //     'title'       => __('repeat', 'text_domain_to_be_replaced')
                            // ),
                            'bg-scale' => array(
                                'input_type'  => 'select',
                                'title'       => __('scale', 'text_domain_to_be_replaced')
                            ),
                            'bg-video' => array(
                                'input_type'  => 'text',
                                'title'       => __('Video', 'text_domain_to_be_replaced')
                            ),
                            'bg-apply-overlay' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply a background overlay', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'bg-color-overlay' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Overlay Color', 'text_domain_to_be_replaced'),
                                'width-100'   => true
                            ),
                            'bg-opacity-overlay' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Opacity', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                'unit' => '%'
                            )
                        )
                    ),
                    array(
                        'title' => __('Layout', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'boxed-wide' => array(
                                'input_type'  => 'select',
                                'title'       => __('Boxed or full width', 'text_domain_to_be_replaced'),
                                'refresh-markup' => true,
                                'refresh-stylesheet' => false
                            ),

                            /* suspended, needs more thoughts
                            'boxed-width' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Custom boxed width', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 500,
                                'max' => 1600,
                                'unit' => 'px'
                            ),*/
                            'height-type' => array(
                                'input_type'  => 'select',
                                'title'       => __('Height : fit to screen or custom', 'text_domain_to_be_replaced')
                            ),
                            'custom-height' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Custom height', 'text_domain_to_be_replaced'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                'unit' => '%'
                            ),
                            'v-alignment' => array(
                                'input_type'  => 'v_alignment',
                                'title'       => __('Vertical alignment', 'text_domain_to_be_replaced'),
                                'default'     => 'center'
                            ),
                        )
                    ),
                    array(
                        'title' => __('Border', 'text_domain_to_be_replaced'),
                        'inputs' => array(
                            'border-width' => array(
                                'input_type'  => 'range_slider',
                                'title'       => __('Border width', 'text_domain_to_be_replaced'),
                                'min' => 0,
                                'max' => 100,
                                'unit' => 'px'
                            ),
                            'border-type' => array(
                                'input_type'  => 'select',
                                'title'       => __('Border shape', 'text_domain_to_be_replaced')
                            ),
                            'border-color' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Border color', 'text_domain_to_be_replaced'),
                                'width-100'   => true,
                            ),
                            'shadow' => array(
                                'input_type'  => 'gutencheck',
                                'title'       => __('Apply a shadow', 'text_domain_to_be_replaced'),
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            )
                        )
                    ),
                )//tabs
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', 'sek_add_css_rules_for_lbb_background', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', 'sek_add_css_rules_for_lbb_border', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', 'sek_add_css_rules_for_lbb_boxshadow', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', 'sek_add_css_rules_for_lbb_height', 10, 3 );
function sek_add_css_rules_for_lbb_background( array $rules, array $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    // LBB - background
    // bg-apply-overlay
    // bg-attachment
    // bg-color
    // bg-color-overlay
    // bg-image
    // bg-opacity-overlay
    // bg-position
    // bg-scale
    // bg-video

    //TODO:
    // border-color
    // border-type
    // border-width
    // boxed-wide
    // boxed-width
    // custom-height
    // height-type
    // shadow

    if ( empty( $options[ 'lbb' ] ) )
      return $rules;

    $background_properties = array();

    /* The general syntax of the background property is:
    * https://www.webpagefx.com/blog/web-design/background-css-shorthand/
    * background: [background-image] [background-position] / [background-size] [background-repeat] [background-attachment] [background-origin] [background-clip] [background-color];
    */
    // Img background
    if ( ! empty( $options['lbb'][ 'bg-image'] ) && is_numeric( $options['lbb'][ 'bg-image'] ) ) {
        //no repeat by default?
        $background_properties[] = 'url("'. wp_get_attachment_url( $options['lbb'][ 'bg-image'] ) .'")';

        // Img Bg Position
        if ( ! empty( $options['lbb'][ 'bg-position'] ) ) {
            $pos_map = array(
                'top_left'    => '0% 0%',
                'top'         => '50% 0%',
                'top_right'   => '100% 0%',
                'left'        => '0% 50%',
                'center'      => '50% 50%',
                'right'       => '100% 50%',
                'bottom_left' => '0% 100%',
                'bottom'      => '50% 100%',
                'bottom_right'=> '100% 100%'
            );

            $raw_pos                    = $options['lbb'][ 'bg-position'];
            $background_properties[]         = array_key_exists($raw_pos, $pos_map) ? $pos_map[ $raw_pos ] : $pos_map[ 'center' ];
        }


        //background size
        if ( ! empty( $options['lbb'][ 'bg-scale'] ) && 'default' != $options['lbb'][ 'bg-scale'] ) {
            //When specifying a background-size value, it must immediately follow the background-position value.
            if ( ! empty( $options['lbb'][ 'bg-position'] ) ) {
                $background_properties[] = '/ ' . $options['lbb'][ 'bg-scale'];
            } else {
                $background_size    = $options['lbb'][ 'bg-scale'];
            }
        }

        //add no-repeat by default?
        $background_properties[] = 'no-repeat';

        // write the bg-attachment rule only if true <=> set to "fixed"
        if ( ! empty( $options['lbb'][ 'bg-attachment'] ) && sek_is_checked( $options['lbb'][ 'bg-attachment'] ) ) {
            $background_properties[] = 'fixed';
        }

    }


    //background color (needs validation: we need a sanitize hex or rgba color)
    if ( ! empty( $options[ 'lbb' ][ 'bg-color' ] ) ) {
        $background_properties[] = $options[ 'lbb' ][ 'bg-color' ];
    }


    //build background rule
    if ( ! empty( $background_properties ) ) {
        $background_style_rules      = "background:" . implode( ' ', array_filter( $background_properties ) );

        //do we need to add the background-size property separately?
        $background_style_rules      = isset( $background_size ) ? $style_rules . ';background-size:' . $background_size : $background_style_rules;

        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'style_rules' => $background_style_rules,
            'mq' =>null
        );
    }

    //Background overlay?
    if ( ! empty( $options['lbb'][ 'bg-apply-overlay'] ) && sek_is_checked( $options['lbb'][ 'bg-apply-overlay'] ) ) {
        //(needs validation: we need a sanitize hex or rgba color)
        $bg_color_overlay = isset( $options[ 'lbb' ][ 'bg-color-overlay' ] ) ? $options[ 'lbb' ][ 'bg-color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            //overlay pseudo element
            $bg_overlay_style_rules = 'content:"";display:block;position:absolute;top:0;left:0;right:0;bottom:0;background-color:'.$bg_color_overlay;

            //opacity
            //validate/sanitize
            $bg_overlay_opacity     = isset( $options[ 'lbb' ][ 'bg-opacity-overlay' ] ) ? filter_var( $options[ 'lbb' ][ 'bg-opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_style_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_style_rules . ';opacity:' . $bg_overlay_opacity : $bg_overlay_style_rules;

            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]::before',
                    'style_rules' => $bg_overlay_style_rules,
                    'mq' =>null
            );
            //we have to also:
            // 1) make '[data-sek-id="'.$level['id'].'"] to be relative positioned (to make the overlay absolute element referring to it)
            // 2) make any '[data-sek-id="'.$level['id'].'"] first child to be relative (not to the resizable handle div)
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'style_rules' => 'position:relative',
                    'mq' => null
            );

            $first_child_selector = '[data-sek-id="'.$level['id'].'"]>*';
            //in the preview we still want some elements to be absoluted positioned
            //1) the .ui-resizable-handle (jquery-ui)
            //2) the block overlay
            //3) the add content button
            if ( is_customize_preview() ) {
                $first_child_selector .= ':not(.ui-resizable-handle):not(.sek-block-overlay):not(.sek-add-content-button)';
            }
            $rules[]     = array(
                'selector' => $first_child_selector,
                'style_rules' => 'position:relative',
                'mq' =>null
            );
        }
    }//if ( ! empty( $options['lbb'][ 'bg-apply-overlay'] ) && sek_is_checked( $options['lbb'][ 'bg-apply-overlay'] ) ) {}

    return $rules;
}











function sek_add_css_rules_for_lbb_border( array $rules, array $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

    //TODO: we actually should allow multidimensional border widths plus different units
    if ( empty( $options[ 'lbb' ] ) )
      return $rules;

    $border_width = ! empty( $options['lbb'][ 'border-width' ] ) ? filter_var( $options['lbb'][ 'border-width' ], FILTER_VALIDATE_INT ) : FALSE;
    $border_type  = FALSE !== $border_width && ! empty( $options['lbb'][ 'border-type' ] ) && 'none' != $options['lbb'][ 'border-type' ] ? $options['lbb'][ 'border-type' ] : FALSE;

    //border width
    if ( $border_type ) {
        $border_properties = array();
        $border_properties[] = $border_width . 'px';

        //border type
        $border_properties[] = $border_type;

        //border color
        //(needs validation: we need a sanitize hex or rgba color)
        if ( ! empty( $options['lbb'][ 'border-color' ] ) ) {
            $border_properties[] = $options['lbb'][ 'border-color' ];
        }

        //append border rules
        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'style_rules' => "border:" . implode( ' ', array_filter( $border_properties ) ),
                'mq' =>null
        );
    }

    return $rules;
}














function sek_add_css_rules_for_lbb_boxshadow( array $rules, array $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'lbb' ] ) )
      return $rules;

    if ( !empty( $options[ 'lbb' ][ 'shadow' ] ) &&  sek_is_checked( $options['lbb'][ 'shadow'] ) ) {
        $style_rules = 'box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2); -webkit-box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2);';

        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'style_rules' => $style_rules,
                'mq' =>null
        );
    }
    return $rules;
}











function sek_add_css_rules_for_lbb_height( array $rules, array $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'lbb' ] ) )
      return $rules;

    if ( empty( $options[ 'lbb' ][ 'height-type' ] ) )
      return $rules;

    if ( 'fit-to-screen' == $options[ 'lbb' ][ 'height-type' ] ) {
        $height = '100';
    }
    elseif ( 'custom' == $options[ 'lbb' ][ 'height-type' ] && FALSE !== $height_value = filter_var( $options[ 'lbb' ][ 'custom-height' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) ) ) ) {
        $height = $height_value;
    }
    $style_rules = '';
    if ( isset( $height ) && FALSE !== $height ) {
        $style_rules .= 'height:' . $height . 'vh;';
    }
    if ( !empty( $options[ 'lbb' ][ 'v-alignment' ]) ) {
        switch( $options[ 'lbb' ][ 'v-alignment' ] ) {
            case 'top' :
                $style_rules .= "align-items: flex-start;";
            break;
            case 'center' :
                $style_rules .= "align-items: center;";
            break;
            case 'bottom' :
                $style_rules .= "align-items: flex-end;";
            break;
        }
    }
    if ( !empty( $style_rules ) ) {
        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'style_rules' => $style_rules,
                'mq' =>null
        );
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?>