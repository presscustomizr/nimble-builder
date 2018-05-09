<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


/**
 *  Sek Dyn CSS Builder: class responsible for building Stylesheet from a sek model
 */
class Sek_Dyn_CSS_Builder {

    /*min widths, considering CSS min widths BP:
    $grid-breakpoints: (
        xs: 0,
        sm: 576px,
        md: 768px,
        lg: 992px,
        xl: 1200px
    )

    we could have a constant array since php 5.6
    */
    private static $breakpoints = [
        'xs' => 0,
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
        'xl' => 1200
    ];

    const COLS_MOBILE_BREAKPOINT  = 'md';

    private $stylesheet;
    private $sek_model;

    private $rules_builder_methods_type = array(
        'width',
        'spacing',
        'border',
        'background',
        'boxshadow',
        'height'
    );

    public function __construct( $sek_model = array(), Sek_Stylesheet $stylesheet ) {
        $this->stylesheet = $stylesheet;
        $this->sek_model  = $sek_model;

        $this->sek_dyn_css_builder_setup_parse_rules_hooks();

        $this->sek_dyn_css_builder_build_stylesheet();
    }


    private function sek_dyn_css_builder_setup_parse_rules_hooks() {
        foreach ( $this->rules_builder_methods_type as $rules_builder_method_type ) {
            if ( method_exists( $this, "sek_dyn_css_builder_{$rules_builder_method_type}_parse_rules" ) )
               add_filter( 'sek_dyn_css_builder_rules', array( $this, "sek_dyn_css_builder_{$rules_builder_method_type}_parse_rules" ), 10, 2 );
        }

    }


    public function sek_dyn_css_builder_build_stylesheet( $level = null, $stylesheet = null ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        $stylesheet = is_null( $stylesheet ) ? $this->stylesheet : $stylesheet;


        $collection = empty( $level[ 'collection' ] ) ? array() : $level[ 'collection' ];

        foreach ( $collection as $level ) {

            //do this level
            if ( !empty( $level[ 'options' ] ) || !empty( $level[ 'width' ] ) ) {
                //build rules
                $this->sek_dyn_css_builder_build_rules( $level  );
            }

            if ( !empty( $level[ 'collection' ] ) ) {
                $this->sek_dyn_css_builder_build_stylesheet( $level, $stylesheet );
            }
        }
    }




    public function sek_dyn_css_builder_get_stylesheet() {
        return $this->stylesheet;
    }




    public function sek_dyn_css_builder_build_rules( $level ) {
        $rules   = apply_filters( 'sek_dyn_css_builder_rules', array(), $level  );
        //fill the stylesheet
        if ( !empty( $rules ) ) {
            //TODO: MAKE SURE RULE ARE NORMALIZED
            foreach( $rules as $rule ) {
                $this->stylesheet->sek_add_rule( $rule[ 'selector' ], $rule[ 'style_rules' ], $rule[ 'mq' ] );
            }
        }
    }





    public function sek_dyn_css_builder_width_parse_rules( array $rules, array $level ) {
        $width   = empty( $level[ 'width' ] ) || !is_numeric( $level[ 'width' ] ) ? '' : $level['width'];

        //width
        if ( !empty( $width ) ) {
            $style_rules = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width );
            $rules[] = array(
                'selector'      => '.sek-column[data-sek-id="'.$level['id'].'"]',
                'style_rules'   => $style_rules,
                'mq'            => array( 'min' => self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ] )
            );
        }

        return $rules;
    }


    public function sek_dyn_css_builder_spacing_parse_rules( array $rules, array $level ) {

        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

        //spacing
        if ( !empty( $options[ 'spacing' ] ) ) {

            $default_unit = 'px';

            //not mobile first
            $_desktop_rules = $_mobile_rules = $_tablet_rules = null;

            if ( !empty( $options[ 'spacing' ][ 'desktop_pad_marg' ] ) ) {
                 $_desktop_rules = array( 'rules' => $options[ 'spacing' ][ 'desktop_pad_marg' ] );
            }

            $_pad_marg = array(
                'desktop' => array(),
                'tablet' => array(),
                'mobile' => array()
            );

            foreach( array_keys( $_pad_marg ) as $device  ) {
                if ( !empty( $options[ 'spacing' ][ "{$device}_pad_marg" ] ) ) {
                    $_pad_marg[ $device ] = array( 'rules' => $options[ 'spacing' ][ "{$device}_pad_marg" ] );

                    //add unit and sanitize padding (cannot have negative padding)
                    $unit                 = !empty( $options[ 'spacing' ][ "{$device}_unit" ] ) ? $options[ 'spacing' ][ "{$device}_unit" ] : $default_unit;
                    $unit                 = 'percent' == $unit ? '%' : $unit;
                    array_walk( $_pad_marg[ $device ][ 'rules' ],
                        function( &$val, $key, $unit ) {
                            //make sure paddings are positive values
                            if ( FALSE !== strpos( 'padding', $key ) ) {
                                $val = abs( $val );
                            }

                            $val .= $unit;
                    }, $unit );
                }
            }


            /*
            * TABLETS AND MOBILES WILL INHERIT UPPER MQ LEVELS IF NOT OTHERWISE SPECIFIED
            */
            if ( ! empty( $_pad_marg[ 'desktop' ] ) ) {
                $_pad_marg[ 'desktop' ][ 'mq' ] = null;
            }

            if ( ! empty( $_pad_marg[ 'tablet' ] ) ) {
                $_pad_marg[ 'tablet' ][ 'mq' ]  = array( 'max' => (int)( self::$breakpoints['lg'] - 1 ) ); //max-width: 991
            }

            if ( ! empty( $_pad_marg[ 'mobile' ] ) ) {
                $_pad_marg[ 'mobile' ][ 'mq' ]  = array( 'max' => (int)( self::$breakpoints['sm'] - 1 ) ); //max-width: 575
            }

            foreach( array_filter( $_pad_marg ) as $_spacing_rules ) {
                $style_rules = implode(';',
                    array_map( function( $key, $value ) {
                        return "$key:{$value}";
                    }, array_keys( $_spacing_rules[ 'rules' ] ), array_values( $_spacing_rules[ 'rules' ] )
                ) );

                $rules[] = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'style_rules' => $style_rules,
                    'mq' =>$_spacing_rules[ 'mq' ]
                );
            }
        }

        return $rules;
    }


    public function sek_dyn_css_builder_background_parse_rules( array $rules, array $level ) {
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

        if ( !empty( $options[ 'lbb' ] ) ) {
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
            }

        }

        return $rules;
    }



    public function sek_dyn_css_builder_border_parse_rules( array $rules, array $level ) {
        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

        //TODO: we actually should allow multidimensional border widths plus different units
        if ( !empty( $options[ 'lbb' ] ) ) {
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
        }
        return $rules;
    }




    public function sek_dyn_css_builder_boxshadow_parse_rules( array $rules, array $level ) {
        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

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


    public function sek_dyn_css_builder_height_parse_rules( array $rules, array $level ) {
        $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

        if ( !empty( $options[ 'lbb' ][ 'height-type' ] ) ) {
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

        }
        return $rules;
    }


}//end class

















class Sek_Stylesheet {

    private $rules = array();


    public function sek_add_rule( $selector, $style_rules, array $mq = null ) {

        if ( ! is_string( $selector ) )
            return;

        if ( ! is_string( $style_rules ) )
            return;

        //TODO: allowed media query?
        $mq_hash = 'all';

        if ( $mq ) {
            $mq_hash = $this->sek_mq_to_hash( $mq );
        }

        if ( !isset( $this->rules[ $mq_hash ] ) ) {
            $this->sek_add_mq_hash( $mq_hash );
        }

        if ( !isset( $this->rules[ $mq_hash ][ $selector ] ) ) {
            $this->rules[ $mq_hash ][ $selector ] = array();
        }

        $this->rules[ $mq_hash ][ $selector ][] = $style_rules;
    }


    //totally Elementor inpired
    //add and sort media queries
    private function sek_add_mq_hash( $mq_hash ) {
        $this->rules[ $mq_hash ] = array();

        //TODO: test and probably improve ordering: need to think about convoluted use cases
        uksort(
            $this->rules, function( $a, $b ) {
                if ( 'all' === $a ) {
                    return -1;
                }

                if ( 'all' === $b ) {
                    return 1;
                }

                $a_query = $this->sek_hash_to_mq( $a );

                $b_query = $this->sek_hash_to_mq( $b );

                if ( isset( $a_query['min'] ) xor isset( $b_query['min'] ) ) {
                    return 1;
                }

                if ( isset( $a_query['min'] ) ) {
                    return $a_query['min'] - $b_query['min'];
                }

                return $b_query['max'] - $a_query['max'];
            }
        );
    }


    //totally Elementor inpired
    private function sek_mq_to_hash( array $mq ) {
        $hash = [];

        foreach ( $mq as $min_max => $value ) {
            $hash[] = $min_max . '_' . $value;
        }

        return implode( '-', $hash );
    }


    //totally Elementor inpired
    private function sek_hash_to_mq( $mq_hash ) {
        $mq = [];

        $mq_hash = array_filter( explode( '-', $mq_hash ) );

        foreach ( $mq_hash as $single_mq ) {
            $single_mq_parts = explode( '_', $single_mq );

            $mq[ $single_mq_parts[0] ] = $single_mq_parts[1];

        }

        return $mq;
    }


    private function sek_maybe_wrap_in_media_query( $css,  $mq_hash = 'all' ) {
        if ( 'all' === $mq_hash ) {
            return $css;
        }

        $mq           = $this->sek_hash_to_mq( $mq_hash );

        return '@media ' . implode( ' and ', array_map(
                function( $min_max, $value ) {
                    return "({$min_max}-width:{$value}px)";
                },
                array_keys( $mq ),
                array_values( $mq )
            )
        ) . '{' . $css . '}';
    }



    private function sek_parse_rules( $selector, $style_rules = array() ) {
        $style_rules = is_array( $style_rules ) ? implode( ';', $style_rules ) : $style_rules;
        return $selector . '{' . $style_rules . '}';
    }




    //stringify the stylesheet object
    public function __toString() {
        $css = '';
        foreach ( $this->rules as $mq_hash => $selectors ) {
            $_css = '';
            foreach ( $selectors as $selector => $style_rules ) {
                $_css .=  $this->sek_parse_rules( $selector, $style_rules );
            }
            $_css = $this->sek_maybe_wrap_in_media_query( $_css, $mq_hash );
            $css .= $_css;
        }

        return $css;
    }


}//end class

















/*
array(
    //Section 1
    array(
        id => 1
        collection => array(
            //column 1
            array(
                id => 12
                collection => array(
                      //module 1
                      array(
                            id => 123,
                            options => array()
                      ),
                      // module 2
                      array(),
                      ...
                ),
                options => array(),
                width => ''
            ),
            //column 2
            array(),
            ...
        ),
        options => array(
            // layout, background, border
            lbb => array(
                bg-img = ''
                bg-color = ''
            ),
            // spacing
            spacing => array(

            )
        )
    ),

    //Section  2
    array(),
    ...
)
*/
// $_sektions = array(
//     'collection' => array(

//         array(
//                 'id' => '__sek__db36a8b7642a7a5a2a19e1f6',
//                 'level' => 'section',
//                 'collection' => array(
//                         array(
//                                 'id' => '__sek__5af332dc6784ce1f1e17a3ba',
//                                 'level' => 'column',
//                                 'collection' => array(),
//                                 'options' => array(
//                                         'lbb' => array(
//                                                 'bg-color' => '#dd9933'
//                                         ),
//                                         'spacing' => array(
//                                             'desktop_pad_marg' => array(
//                                                 'padding-top' => 10,
//                                             ),
//                                         )
//                                 ),
//                         ),
//                 ),

//         ),//end sek-1

//         array(
//                 'id' => '__sek__659b99908c05fd55d7a09401',
//                 'level' => 'section',
//                 'collection' => array(
//                         array(
//                                 'id' => '__sek__da7fe9822690cc0fd0f843e9',
//                                 'level' => 'column',
//                                 'collection' => array(),
//                                 'options' => array(
//                                         'spacing' => array(
//                                             'desktop_pad_marg' => array(
//                                                 'padding-top' => 10,
//                                                 'padding-bottom' => 10,
//                                                 'margin-top' => 10,
//                                             ),
//                                             'tablet_pad_marg' => array(
//                                                 'padding-top' => 20
//                                             )
//                                         )

//                                 )
//                         )
//                 )

//         )//end sek-2
//     )//end sek collection
// );










//TEST
// require_once( 'class-sek-dyn-css-handler.php' );
// add_action( 'wp_head', function() use ( $_sektions ) {

//     $skope_id = skp_build_skope_id();

//     /*
//     * Once this file is required, the whole code below can be
//     * placed in SEK_Front_Render::print_dyn_inline_stylesheet()
//     * after
//             if ( is_null( $skope_id ) ) {
//                 $skope_id = skp_build_skope_id();
//             }
//     * in place of :
    /*?>
        <style id="sek-<?php echo $skope_id; ?>" type="text/css">
            <?php // COLUMN WIDTH ?>
            @media (min-width: 768px) { <?php echo $this -> print_custom_width_styles( $skope_id ); ?> }
            <?php // SECTION BACKGROUND ?>
            <?php echo $this -> print_level_background( $skope_id ); ?>
        </style>
    <?php*/

//     Also remember to uncomment the very line below
//     */
//     //$_sektions = sek_get_skoped_seks( $skope_id );

//     //build stylesheet
//     $stylesheet = new Sek_Stylesheet();
//     $builder    = new Sek_Dyn_CSS_Builder( $_sektions, $stylesheet );
//     $builder->sek_dyn_css_builder_build_stylesheet();


//     //enqueuing
//     $dyn_css_handler_params = array(
//         'id'             => $skope_id,
//         'mode'           => Sek_Dyn_CSS_Handler::MODE_FILE,
//         //these are taken in account only when 'mode' is 'file'
//         'force_write'    => true, //<- write if the file doesn't exist

//         //TEMPORARY: we actually need to refresh the file on customize_save only when needed
//         'force_rewrite'  => true, //<- write even if the file exists
//     );

//     $_is_preview     = is_customize_preview();

//     if ( $_is_preview ) {
//         $dyn_css_handler_params = array_merge( $dyn_css_handler_params, array(
//             'mode'       => Sek_Dyn_CSS_Handler::MODE_INLINE,
//         ) );
//     }

//     //Init the enqueuer
//     $dyn_css_handler = new Sek_Dyn_CSS_Handler( $dyn_css_handler_params );
//     $dyn_css_handler->sek_dyn_css_set_css( (string)$stylesheet );

//     //finally enqueue
//     $dyn_css_handler->sek_dyn_css_enqueue();
// });

?>