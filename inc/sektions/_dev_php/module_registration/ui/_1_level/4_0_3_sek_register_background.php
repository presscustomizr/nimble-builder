<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_bg_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_bg_module',
        'name' => __('Background', 'text_domain_to_be_replaced'),
        // 'starting_value' => array(
        //     'bg-color-overlay'  => '#000000',
        //     'bg-opacity-overlay' => '40'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'bg-color' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Background color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'     => '',
                ),
                'bg-image' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Image', 'text_domain_to_be_replaced'),
                    'default'     => '',
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
                    'title'       => __('Fixed background', 'text_domain_to_be_replaced'),
                    'default'     => 0
                ),
                // 'bg-repeat' => array(
                //     'input_type'  => 'select',
                //     'title'       => __('repeat', 'text_domain_to_be_replaced')
                // ),
                'bg-scale' => array(
                    'input_type'  => 'select',
                    'title'       => __('scale', 'text_domain_to_be_replaced'),
                    'default'     => 'cover',
                    'choices'     => sek_get_select_options_for_input_id( 'bg-scale' )
                ),
                // 'bg-video' => array(
                //     'input_type'  => 'text',
                //     'title'       => __('Video', 'text_domain_to_be_replaced'),
                //     'default'     => ''
                // ),
                'bg-apply-overlay' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Apply a background overlay', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0
                ),
                'bg-color-overlay' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Overlay Color', 'text_domain_to_be_replaced'),
                    'width-100'   => true,
                    'default'     => '#000000'
                ),
                'bg-opacity-overlay' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Opacity (in percents)', 'text_domain_to_be_replaced'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    // 'unit' => '%',
                    'default'  => '40',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                )
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_background', 10, 3 );

function sek_add_css_rules_for_level_background( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

    // $default_value_model = Array
    // (
    //     [bg-color] =>
    //     [bg-image] =>
    //     [bg-position] => center
    //     [bg-attachment] => 0
    //     [bg-scale] => default
    //     [bg-apply-overlay] => 0
    //     [bg-color-overlay] =>
    //     [bg-opacity-overlay] => 50
    //     [border-width] => 1
    //     [border-type] => none
    //     [border-color] =>
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_bg_module' );
    $bg_options = ( ! empty( $options[ 'bg' ] ) && is_array( $options[ 'bg' ] ) ) ? $options[ 'bg' ] : array();
    $bg_options = wp_parse_args( $bg_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $bg_options ) )
      return $rules;

    $background_properties = array();

    /* The general syntax of the background property is:
    * https://www.webpagefx.com/blog/web-design/background-css-shorthand/
    * background: [background-image] [background-position] / [background-size] [background-repeat] [background-attachment] [background-origin] [background-clip] [background-color];
    */
    // Img background
    if ( ! empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
        //no repeat by default?
        $background_properties[] = 'url("'. wp_get_attachment_url( $bg_options[ 'bg-image'] ) .'")';

        // Img Bg Position
        if ( ! empty( $bg_options[ 'bg-position'] ) ) {
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

            $raw_pos                    = $bg_options[ 'bg-position'];
            $background_properties[]         = array_key_exists($raw_pos, $pos_map) ? $pos_map[ $raw_pos ] : $pos_map[ 'center' ];
        }


        //background size
        if ( ! empty( $bg_options[ 'bg-scale'] ) && 'default' != $bg_options[ 'bg-scale'] ) {
            //When specifying a background-size value, it must immediately follow the background-position value.
            if ( ! empty( $bg_options[ 'bg-position'] ) ) {
                $background_properties[] = '/ ' . $bg_options[ 'bg-scale'];
            } else {
                $background_size    = $bg_options[ 'bg-scale'];
            }
        }

        //add no-repeat by default?
        $background_properties[] = 'no-repeat';

        // write the bg-attachment rule only if true <=> set to "fixed"
        if ( ! empty( $bg_options[ 'bg-attachment'] ) && sek_is_checked( $bg_options[ 'bg-attachment'] ) ) {
            $background_properties[] = 'fixed';
        }

    }


    //background color (needs validation: we need a sanitize hex or rgba color)
    if ( ! empty( $bg_options[ 'bg-color' ] ) ) {
        $background_properties[] = $bg_options[ 'bg-color' ];
    }


    //build background rule
    if ( ! empty( $background_properties ) ) {
        $background_css_rules      = "background:" . implode( ' ', array_filter( $background_properties ) );

        //do we need to add the background-size property separately?
        $background_css_rules      = isset( $background_size ) ? $css_rules . ';background-size:' . $background_size : $background_css_rules;

        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'css_rules' => $background_css_rules,
            'mq' =>null
        );
    }

    //Background overlay?
    // 1) a background image should be set
    // 2) the option should be checked
    if ( !empty( $bg_options['bg-image']) && ! empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {
        //(needs validation: we need a sanitize hex or rgba color)
        $bg_color_overlay = isset( $bg_options[ 'bg-color-overlay' ] ) ? $bg_options[ 'bg-color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            //overlay pseudo element
            $bg_overlay_css_rules = 'content:"";display:block;position:absolute;top:0;left:0;right:0;bottom:0;background-color:'.$bg_color_overlay;

            //opacity
            //validate/sanitize
            $bg_overlay_opacity     = isset( $bg_options[ 'bg-opacity-overlay' ] ) ? filter_var( $bg_options[ 'bg-opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_css_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_css_rules . ';opacity:' . $bg_overlay_opacity : $bg_overlay_css_rules;

            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]::before',
                    'css_rules' => $bg_overlay_css_rules,
                    'mq' =>null
            );
            //we have to also:
            // 1) make '[data-sek-id="'.$level['id'].'"] to be relative positioned (to make the overlay absolute element referring to it)
            // 2) make any '[data-sek-id="'.$level['id'].'"] first child to be relative (not to the resizable handle div)
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => 'position:relative',
                    'mq' => null
            );

            $first_child_selector = '[data-sek-id="'.$level['id'].'"]>*';
            //in the preview we still want some elements to be absoluted positioned
            //1) the .ui-resizable-handle (jquery-ui)
            //2) the block overlay
            //3) the add content button
            if ( is_customize_preview() ) {
                $first_child_selector .= ':not(.ui-resizable-handle):not(.sek-dyn-ui-wrapper):not(.sek-add-content-button)';
            }
            $rules[]     = array(
                'selector' => $first_child_selector,
                'css_rules' => 'position:relative',
                'mq' =>null
            );
        }
    }//if ( ! empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {}

    return $rules;
}

?>