<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_width_section() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_section',
        'name' => __('Width options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-width' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Define custom outer and inner widths for this section', 'text_domain_to_be_replaced'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true
                ),
                'outer-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Outer section width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true
                ),
                'inner-section-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Inner section width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 500,
                    'default' => '100%',
                    'width-100'   => true
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_section_width', 10, 3 );
function sek_add_css_rules_for_section_width( $rules, $section ) {
    if ( ! is_array( $section ) )
      return $rules;
    // this filter is fired for all level types. Make sure we filter only the sections.
    if ( empty( $section['level'] ) || 'section' !== $section['level'] )
      return $rules;

    $options = empty( $section[ 'options' ] ) ? array() : $section['options'];
    if ( empty( $options[ 'width' ] ) )
      return $rules;

    if ( empty( $options[ 'width' ][ 'use-custom-width' ] ) || false === sek_booleanize_checkbox_val( $options[ 'width' ][ 'use-custom-width' ] ) )
      return $rules;
    if ( ! empty( $options[ 'width' ][ 'outer-section-width'] ) ) {
          $numeric = sek_extract_numeric_value( $options[ 'width' ][ 'outer-section-width'] );
          if ( ! empty( $numeric ) ) {
              $unit = sek_extract_unit( $options[ 'width' ][ 'outer-section-width'] );
              $rules[]     = array(
                      'selector' => '[data-sek-id="'.$section['id'].'"]',
                      'css_rules' => sprintf( 'max-width:%1$s%2$s;margin: 0 auto;', $numeric, $unit ),
                      'mq' =>null
              );
          }
    }
    if ( ! empty( $options[ 'width' ][ 'inner-section-width'] ) ) {
          $numeric = sek_extract_numeric_value( $options[ 'width' ][ 'inner-section-width'] );
          if ( ! empty( $numeric ) ) {
              $unit = sek_extract_unit( $options[ 'width' ][ 'inner-section-width'] );
              $rules[]     = array(
                      'selector' => '[data-sek-id="'.$section['id'].'"] > .sek-container-fluid > .sek-sektion-inner',
                      'css_rules' => sprintf( 'max-width:%1$s%2$s;margin: 0 auto;', $numeric, $unit ),
                      'mq' =>null
              );
          }
    }

    //error_log( print_r($rules, true) );
    return $rules;
}

?>