<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER LEVEL LAYOUT BACKGROUND BORDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_width_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_width_module',
        'name' => __('Width options', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'width-type' => array(
                    'input_type'  => 'select',
                    'title'       => __('Width : 100% or custom', 'text_domain_to_be_replaced'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'width-type' )
                ),
                'custom-width' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Custom width', 'text_domain_to_be_replaced'),
                    'min' => 0,
                    'max' => 100,
                    'default' => '100%',
                    'width-100'   => true,
                ),
                'h_alignment' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Horizontal alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment'
                )
            )
        )//tmpl
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_width', 10, 3 );
function sek_add_css_rules_for_level_width( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'width' ] ) )
      return $rules;

    if ( ! empty( $options[ 'width' ][ 'h_alignment' ] ) ) {
        $h_alignment_value = $options[ 'width' ][ 'h_alignment' ];
        switch ( $h_alignment_value ) {
            case 'left' :
                $h_align_value = "flex-start";
            break;
            case 'center' :
                $h_align_value = "center";
            break;
            case 'right' :
                $h_align_value = "flex-end";
            break;
            default :
                $h_align_value = "center";
            break;
        }
        $css_rules = '';
        if ( isset( $h_align_value ) ) {
            $css_rules .= 'align-self:' . $h_align_value;
        }

        if ( !empty( $css_rules ) ) {
            $rules[]     = array(
                    'selector' => '[data-sek-id="'.$level['id'].'"]',
                    'css_rules' => $css_rules,
                    'mq' =>null
            );
        }
    }

    if ( ! empty( $options[ 'width' ][ 'width-type' ] ) ) {
        if ( 'custom' == $options[ 'width' ][ 'width-type' ] && array_key_exists( 'custom-width', $options[ 'width' ] ) ) {
            $width = $options[ 'width' ][ 'custom-width' ];
            $css_rules = '';
            if ( isset( $width ) && FALSE !== $width ) {
                $numeric = sek_extract_numeric_value( $width );
                $unit = sek_extract_unit( $width );
                $unit = '%' === $unit ? 'vw' : $unit;
                $css_rules .= 'width:' . $numeric . $unit . ';';
            }
            if ( !empty( $css_rules ) ) {
                $rules[]     = array(
                        'selector' => '[data-sek-id="'.$level['id'].'"]',
                        'css_rules' => $css_rules,
                        'mq' =>null
                );
            }
        }
    }
    //error_log( print_r($rules, true) );
    return $rules;
}

?>