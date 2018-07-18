<?php
/* ------------------------------------------------------------------------- *
 *  SPACING MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_spacing_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_spacing_module',
        'name' => __('Spacing options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',

        'tmpl' => array(
            'item-inputs' => array(
                'pad_marg' => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __('Set padding and margin', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'default'     => array( 'desktop' => array() ),
                    'has_device_switcher' => true
                )
            )
        )
    );
}





/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_spacing', 10, 3 );
// hook : sek_dyn_css_builder_rules
// @return array() of css rules
function sek_add_css_rules_for_spacing( $rules, $level ) {

    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];

    //spacing
    if ( empty( $options[ 'spacing' ] ) || empty( $options[ 'spacing' ][ 'pad_marg' ] ) )
      return $rules;


    $default_unit = 'px';

    //not mobile first
    $_desktop_rules = $_mobile_rules = $_tablet_rules = null;

    if ( !empty( $options[ 'spacing' ][ 'pad_marg' ]['desktop'] ) ) {
         $_desktop_rules = array( 'rules' => $options[ 'spacing' ][ 'pad_marg' ]['desktop'] );
    }

    // POPULATES AN ARRAY FROM THE RAW SAVED OPTIONS
    $_pad_marg = array(
        'desktop' => array(),
        'tablet' => array(),
        'mobile' => array()
    );



    foreach( array_keys( $_pad_marg ) as $device  ) {
        if ( !empty( $options[ 'spacing' ][ 'pad_marg' ][ $device ] ) ) {
            //$rules_candidates = $options[ 'spacing' ][ 'pad_marg' ][ $device ];

            $rules_candidates = array_filter( $options[ 'spacing' ][ 'pad_marg' ][ $device ], function( $k ) {
                return 'unit' !== $k;
            }, ARRAY_FILTER_USE_KEY );

            $_pad_marg[ $device ] = array( 'rules' => $rules_candidates );

            //add unit and sanitize padding (cannot have negative padding)
            $unit                 = !empty( $rules_candidates['unit'] ) ? $rules_candidates['unit'] : $default_unit;
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
    // Sek_Dyn_CSS_Builder::$breakpoints = [
    //     'xs' => 0,
    //     'sm' => 576,
    //     'md' => 768,
    //     'lg' => 992,
    //     'xl' => 1200
    // ];
    if ( ! empty( $_pad_marg[ 'desktop' ] ) ) {
        $_pad_marg[ 'desktop' ][ 'mq' ] = null;
    }

    if ( ! empty( $_pad_marg[ 'tablet' ] ) ) {
        $_pad_marg[ 'tablet' ][ 'mq' ]  = 'max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['md'] - 1 ) . 'px'; //max-width: 767
    }

    if ( ! empty( $_pad_marg[ 'mobile' ] ) ) {
        $_pad_marg[ 'mobile' ][ 'mq' ]  = 'max-width:'. ( Sek_Dyn_CSS_Builder::$breakpoints['sm'] - 1 ) . 'px'; //max-width: 575
    }



    foreach( array_filter( $_pad_marg ) as $_spacing_rules ) {
        $css_rules = implode(';',
            array_map( function( $key, $value ) {
                return "$key:{$value}";
            }, array_keys( $_spacing_rules[ 'rules' ] ), array_values( $_spacing_rules[ 'rules' ] )
        ) );

        $rules[] = array(
            'selector' => '[data-sek-id="'.$level['id'].'"]',
            'css_rules' => $css_rules,
            'mq' =>$_spacing_rules[ 'mq' ]
        );
    }
    //sek_error_log('SPACING RULES', $rules );
    return $rules;
}

?>