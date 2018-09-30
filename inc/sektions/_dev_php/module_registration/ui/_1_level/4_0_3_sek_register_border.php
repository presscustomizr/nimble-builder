<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_border_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_border_module',
        'name' => __('Borders', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'borders' => array(
                '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
            )
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'border-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Border shape', 'text_domain_to_be_replaced'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' )
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'border-radius'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_domain_to_be_replaced' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    //'refresh_markup' => false,
                    //'refresh_stylesheet' => true,
                    //'css_identifier' => 'border_radius',
                    //'css_selectors'=> $css_selectors
                ),
                'shadow' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Apply a shadow', 'text_domain_to_be_replaced'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'default' => 0
                )
            )//item-inputs
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_border', 10, 3 );
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_boxshadow', 10, 3 );


function sek_add_css_rules_for_border( $rules, $level ) {
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
    //     [border-type] => 'solid'
    //     [borders] => Array
    //         (
    //             [_all_] => Array
    //                 (
    //                     [wght] => 55px
    //                     [col] => #359615
    //                 )

    //             [top] => Array
    //                 (
    //                     [wght] => 6em
    //                     [col] => #dd3333
    //                 )

    //             [bottom] => Array
    //                 (
    //                     [wght] => 76%
    //                     [col] => #eeee22
    //                 )
    //     [shadow] => 0
    // )
    $default_value_model  = sek_get_default_module_model( 'sek_level_border_module' );
    $normalized_border_options = ( ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) ) ? $options[ 'border' ] : array();
    $normalized_border_options = wp_parse_args( $normalized_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $normalized_border_options ) )
      return $rules;

    $border_settings = ! empty( $normalized_border_options[ 'borders' ] ) ? $normalized_border_options[ 'borders' ] : FALSE;
    $border_type = $normalized_border_options[ 'border-type' ];
    $has_border_settings  = FALSE !== $border_settings && is_array( $border_settings ) && ! empty( $border_type ) && 'none' != $border_type;

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options( $rules, $border_settings, $border_type, '[data-sek-id="'.$level['id'].'"]'  );
    }

    $has_border_radius = ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) && !empty( $options[ 'border' ]['border-radius'] );
    if ( $has_border_radius ) {
        $radius_settings = $normalized_border_options['border-radius'];
        $rules = sek_generate_css_rules_for_border_radius_options( $rules, $normalized_border_options['border-radius'], '[data-sek-id="'.$level['id'].'"]' );
    }

    return $rules;
}



function sek_add_css_rules_for_boxshadow( $rules, $level ) {
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
    $default_value_model  = sek_get_default_module_model( 'sek_level_border_module' );
    $normalized_border_options = ( ! empty( $options[ 'border' ] ) && is_array( $options[ 'border' ] ) ) ? $options[ 'border' ] : array();
    $normalized_border_options = wp_parse_args( $normalized_border_options , is_array( $default_value_model ) ? $default_value_model : array() );

    if ( empty( $normalized_border_options) )
      return $rules;

    if ( !empty( $normalized_border_options[ 'shadow' ] ) &&  sek_is_checked( $normalized_border_options[ 'shadow'] ) ) {
        //$css_rules = 'box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2); -webkit-box-shadow: 1px 1px 2px 0 rgba(75, 75, 85, 0.2);';
        $css_rules = '-webkit-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;-moz-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px;';
        // Set to !important when customizing, to override the sek-highlight-active-ui effect
        if ( skp_is_customizing() ) {
            $css_rules = '-webkit-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;-moz-box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;box-shadow: rgba(0, 0, 0, 0.25) 0px 3px 11px 0px!important;';
        }
        $rules[]     = array(
                'selector' => '[data-sek-id="'.$level['id'].'"]',
                'css_rules' => $css_rules,
                'mq' =>null
        );
    }
    return $rules;
}
?>