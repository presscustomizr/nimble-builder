<?php
// filter declared in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker()
// $rules = apply_filters( "sek_add_css_rules_for_input_id", $rules, $key, $entry, $this -> parent_level );
add_filter( "sek_add_css_rules_for_input_id", 'sek_add_css_rules_for_generic_css_input_types', 10, 4 );
function sek_add_css_rules_for_generic_css_input_types( array $rules, $value, string $input_id, array $parent_level ) {
    //error_log( $input_id );
    //error_log( print_r( $parent_level, true ) );
    $selector = '[data-sek-id="'.$parent_level['id'].'"]';
    $mq = null;
    $properties_to_render = array();

    switch( $input_id ) {
        case 'font_weight_css' :
            $properties_to_render['font-weight'] = $value;
        break;
        case 'font_style_css' :
            $properties_to_render['font-style'] = $value;
        break;
        case 'text_decoration_css' :
            $properties_to_render['text-decoration'] = $value;
        break;
        case 'text_transform_css' :
            $properties_to_render['text-transform'] = $value;
        break;
        case 'letter_spacing_css' :
            $properties_to_render['letter-spacing'] = $value . 'px';
        break;
        case 'color_css' :
            $properties_to_render['color'] = $value;
        break;
        case 'color_hover_css' :
            $selector = '[data-sek-id="'.$parent_level['id'].'"]:hover';
            $properties_to_render['color'] = $value;
        break;
        case 'h_alignment_css' :
            $properties_to_render['text-align'] = $value;
        break;
        case 'font_family_css' :
            $family = $value;
            // Preprocess the selected font family
            //font: [font-stretch] [font-style] [font-variant] [font-weight] [font-size]/[line-height] [font-family];
            //special treatment for font-family
            if ( false != strstr( $value, '[gfont]') ) {
                $split = explode(":", $family);
                $family = $split[0];
                //only numbers for font-weight. 400 is default
                $properties_to_render['font-weight']    = $split[1] ? preg_replace('/\D/', '', $split[1]) : '';
                $properties_to_render['font-weight']    = empty($properties_to_render['font-weight']) ? 400 : $properties_to_render['font-weight'];
                $properties_to_render['font-style']     = ( $split[1] && strstr($split[1], 'italic') ) ? 'italic' : 'normal';
            }

            $family = str_replace( array( '[gfont]', '[cfont]') , '' , $family );
            $properties_to_render['font-family'] = false != strstr( $value, '[cfont]') ? $family : "'" . str_replace( '+' , ' ' , $family ) . "'";
        break;
    }//switch

    if ( ! empty( $properties_to_render ) ) {
        // is the important flag on ?
        $important = false;
        if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) && !empty( $parent_level['value']['important_css'] ) ) {
            $important = (bool)sek_is_checked( $parent_level['value']['important_css'] );
        }

        $style_rules = '';
        foreach ($properties_to_render as $prop => $prop_val) {
            $style_rules .= sprintf( '%1$s:%2$s%3$s;', $prop, $prop_val, $important ? '!important' : '' );
        }//end foreach

        $rules[] = array(
            'selector'      => $selector,
            'style_rules'   => $style_rules,
            'mq'            => $mq
        );
    }
    return $rules;
}
?>