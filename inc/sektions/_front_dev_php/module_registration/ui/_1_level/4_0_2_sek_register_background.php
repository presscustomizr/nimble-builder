<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_bg_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_bg_module',
        //'name' => __('Background', 'text_doma'),
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
                    'title'       => __('Background color', 'text_doma'),
                    'width-100'   => true,
                    'default'     => '',
                ),
                'bg-image' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Image', 'text_doma'),
                    'default'     => '',
                    'notice_after' => sprintf( __('To ensure better performances, use optimized images for your backgrounds. You can also enable the lazy loading option in the %1$s.', 'text_doma'),
                      sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                          "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })",
                          __('site wide options', 'text_doma')
                      )
                    ),
                    'refresh_markup' => true,
                    'html_before' => '<hr/><h3>' . __('Image background', 'text-doma') .'</h3>'
                ),
                'bg-use-post-thumb' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use the contextual post thumbnail', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'default'     => 0,
                    'notice_after' => __('When enabled and possible, Nimble will use the post thumbnail.', 'text_doma'),
                ),
                'bg-position' => array(
                    'input_type'  => 'bgPositionWithDeviceSwitcher',
                    'title'       => __('Image position', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'title_width' => 'width-100',
                ),
                // 'bg-parallax' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Parallax scrolling', 'text_doma')
                // ),
                'bg-attachment' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Fixed background', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'default'     => 0
                ),
                'bg-parallax' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Parallax effect on scroll', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    'notice_after' => __('When enabled, the background image moves slower than the page elements on scroll. This effect is not enabled on mobile devices.', 'text_doma'),
                    'refresh_markup' => true,
                ),
                'bg-parallax-force' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Parallax force (in percents)', 'text_doma'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    // 'unit' => '%',
                    'default'  => '60',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_after' => __('Customize the magnitude of the visual effect when scrolling.', 'text_doma'),
                    'refresh_markup' => true
                ),
                'bg-scale' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Scale', 'text_doma'),
                    'default'     => 'cover',
                    'choices'     => sek_get_select_options_for_input_id( 'bg-scale' )
                ),
                'bg-repeat' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Repeat', 'text_doma'),
                    'default'     => 'no-repeat',
                    'choices'     => array(
                        'default' => __('Default', 'text_dom'),
                        'no-repeat' => __('No repeat', 'text_dom'),
                        'repeat' => __('Repeat', 'text_dom'),
                        'repeat-x' => __('Repeat x', 'text_dom'),
                        'repeat-y' => __('Repeat y', 'text_dom'),
                        'round' => __('Round', 'text_dom'),
                        'space' => __('Space', 'text_dom'),
                    )
                ),
                'bg-use-video' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a video background', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    //'notice_after' => __('', 'text_doma'),
                    'refresh_markup' => true,
                    'html_before' => '<hr/><h3>' . __('Video background', 'text-doma') .'</h3>'
                ),
                'bg-video' => array(
                    'input_type'  => 'text',
                    'title'       => __('Video link', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true,
                    'notice_after' => __('Video link from YouTube, Vimeo, or a self-hosted file ( mp4 format is recommended )', 'text_doma'),
                ),
                'bg-video-loop' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Loop infinitely', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 1,
                    //'notice_after' => __('', 'text_doma'),
                    'refresh_markup' => true,
                ),
                'bg-video-delay-start' => array(
                    'input_type'  => 'number_simple',
                    'title'       => __('Play after a delay', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true,
                    'notice_after' => __('Set an optional delay in seconds before playing the video', 'text-doma')
                ),
                'bg-video-on-mobile' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Play on mobile devices', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    'notice_after' => __('Not recommended if you don\'t use a self-hosted video file', 'text_doma'),
                    'refresh_markup' => true,
                ),
                'bg-video-start-time' => array(
                    'input_type'  => 'number_simple',
                    'title'       => __('Start time', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true
                ),
                'bg-video-end-time' => array(
                    'input_type'  => 'number_simple',
                    'title'       => __('End time', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => true,
                    'notice_after' => __('Set an optional start and end time in seconds', 'text-doma')
                ),
                'bg-apply-overlay' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a background overlay', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default'     => 0,
                    'html_before' => '<hr/><h3>' . __('Overlay color', 'text-doma') .'</h3>'
                ),
                'bg-color-overlay' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Overlay Color', 'text_doma'),
                    'width-100'   => true,
                    'default'     => '#000000'
                ),
                'bg-opacity-overlay' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __('Opacity (in percents)', 'text_doma'),
                    'orientation' => 'horizontal',
                    'min' => 0,
                    'max' => 100,
                    // 'unit' => '%',
                    'default'  => '40',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
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
    $bg_options = ( !empty( $options[ 'bg' ] ) && is_array( $options[ 'bg' ] ) ) ? $options[ 'bg' ] : array();
    $bg_options = wp_parse_args( $bg_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $bg_options ) )
      return $rules;

    $background_properties = array();
    $bg_property_selector = '[data-sek-id="'.$level['id'].'"]';

    /* The general syntax of the background property is:
    * https://www.webpagefx.com/blog/web-design/background-css-shorthand/
    * background: [background-image] [background-position] / [background-size] [background-repeat] [background-attachment] [background-origin] [background-clip] [background-color];
    */
    // Img background
    if ( !empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
        // deactivated when customizing @see function sek_is_img_smartload_enabled()

        //$background_properties[ 'background-image' ] = 'url("'. wp_get_attachment_url( $bg_options[ 'bg-image'] ) .'")';

        // Img Bg Position
        // 'center' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( !empty( $bg_options[ 'bg-position'] ) && 'center' != $bg_options[ 'bg-position'] ) {
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
            // Retro-compat for old bg-position option without device switcher
            if ( is_string( $bg_options[ 'bg-position'] ) ) {
                $raw_pos = $bg_options[ 'bg-position'];
                $background_properties[ 'background-position' ] = array_key_exists($raw_pos, $pos_map) ? $pos_map[ $raw_pos ] : $pos_map[ 'center' ];
            } else if ( is_array( $bg_options[ 'bg-position'] ) ) {
                $mapped_bg_options = array();
                // map option with css value
                foreach ($bg_options[ 'bg-position'] as $device => $user_val ) {
                    if ( !in_array( $device, array( 'desktop', 'tablet', 'mobile' ) ) ) {
                        sek_error_log( __FUNCTION__ . ' => error => unknown device : ' . $device );
                        continue;
                    }
                    $mapped_bg_options[$device] = array_key_exists($user_val, $pos_map) ? $pos_map[ $user_val ] : $pos_map[ 'center' ];
                }

                $rules = sek_set_mq_css_rules(array(
                    'value' => $mapped_bg_options,
                    'css_property' => 'background-position',
                    'selector' => $bg_property_selector,
                    'level_id' => $level['id']
                ), $rules );
            }
        }

        // background size
        // 'cover' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( !empty( $bg_options['bg-scale'] ) && 'default' != $bg_options['bg-scale'] && 'cover' != $bg_options['bg-scale'] ) {
            //When specifying a background-size value, it must immediately follow the background-position value.
            $background_properties['background-size'] = $bg_options['bg-scale'];
        }

        // add no-repeat by default?
        // 'no-repeat' is the default value. the CSS rule is declared in assets/front/scss/sek-base.scss
        if ( !empty( $bg_options['bg-repeat'] ) && 'default' != $bg_options['bg-repeat'] ) {
            $background_properties['background-repeat'] = $bg_options['bg-repeat'];
        }

        // write the bg-attachment rule only if true <=> set to "fixed"
        if ( !empty( $bg_options['bg-attachment'] ) && sek_is_checked( $bg_options['bg-attachment'] ) ) {
            $background_properties['background-attachment'] = 'fixed';
        }

    }

    //background color (needs validation: we need a sanitize hex or rgba color)
    if ( !empty( $bg_options['bg-color'] ) ) {
        $background_properties['background-color'] = $bg_options[ 'bg-color' ];
    }


    //build background rule
    if ( !empty( $background_properties ) ) {
        $background_css_rules = '';
        foreach ($background_properties as $bg_prop => $bg_css_val ) {
            $background_css_rules .= sprintf('%1$s:%2$s;', $bg_prop, $bg_css_val );
        }
        $rules[] = array(
            'selector' => $bg_property_selector,
            'css_rules' => $background_css_rules,
            'mq' =>null
        );
    }

    //Background overlay?
    // 1) a background image or video should be set
    // 2) the option should be checked
    if ( ( !empty( $bg_options['bg-image']) || sek_is_checked( $bg_options['bg-use-post-thumb'] ) || ( sek_is_checked( $bg_options['bg-use-video'] ) && !empty( $bg_options['bg-video'] ) ) ) && !empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {
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

            // nov 2019 : added new selector '> .sek-bg-video-wrapper' for https://github.com/presscustomizr/nimble-builder/issues/287
            $rules[]     = array(
                    'selector' => implode(',', array( '[data-sek-id="'.$level['id'].'"]::before', '[data-sek-id="'.$level['id'].'"] > .sek-bg-video-wrapper::after' ) ),
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
    }//if ( !empty( $bg_options[ 'bg-apply-overlay'] ) && sek_is_checked( $bg_options[ 'bg-apply-overlay'] ) ) {}

    return $rules;
}

?>