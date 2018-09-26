<?php
// Set input content
add_action( 'czr_set_input_tmpl_content', '\Nimble\sek_set_input_tmpl_content', 10, 3 );
function sek_set_input_tmpl_content( $input_type, $input_id, $input_data ) {
    // error_log( print_r( $input_data, true ) );
    // error_log('$input_type' . $input_type );
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'sek_set_input_tmpl_content => missing input type for input id : ' . $input_id );
    }
    switch( $input_type ) {
        // Content picker group
        case 'content_type_switcher' :
            sek_set_input_tmpl___content_type_switcher( $input_id, $input_data );
        break;
        case 'module_picker' :
            sek_set_input_tmpl___module_picker( $input_id, $input_data );
        break;
        case 'section_picker' :
            sek_set_input_tmpl___section_picker( $input_id, $input_data );
        break;

        case 'spacing' :
        case 'spacingWithDeviceSwitcher' :
            sek_set_input_tmpl___spacing( $input_id, $input_data );
        break;
        case 'bg_position' :
            sek_set_input_tmpl___bg_position( $input_id, $input_data );
        break;
        case 'h_alignment' :
            sek_set_input_tmpl___h_alignment( $input_id, $input_data );
        break;
         case 'h_text_alignment' :
            sek_set_input_tmpl___h_text_alignment( $input_id, $input_data );
        break;
        case 'v_alignment' :
            sek_set_input_tmpl___v_alignment( $input_id, $input_data );
        break;
        case 'font_picker' :
            sek_set_input_tmpl___font_picker( $input_id, $input_data );
        break;
        case 'fa_icon_picker' :
            sek_set_input_tmpl___fa_icon_picker( $input_id, $input_data );
        break;
        case 'font_size' :
        case 'line_height' :
            sek_set_input_tmpl___font_size_line_height( $input_id, $input_data );
        break;
        case 'code_editor' :
            sek_set_input_tmpl___code_editor( $input_id, $input_data );
        break;
        case 'range_with_unit_picker' :
            sek_set_input_tmpl___range_with_unit_picker( $input_id, $input_data );
        break;
        case 'range_with_unit_picker_device_switcher' :
            sek_set_input_tmpl___range_with_unit_picker_device_switcher( $input_id, $input_data );
        break;
        case 'range_simple' :
            sek_set_input_tmpl___range_simple( $input_id, $input_data );
        break;
        case 'borders' :
            sek_set_input_tmpl___borders( $input_id, $input_data );
        break;
        case 'border_radius' :
            sek_set_input_tmpl___border_radius( $input_id, $input_data );
        break;
        case 'buttons_choice' :
            sek_set_input_tmpl___buttons_choice( $input_id, $input_data );
        break;
    }
}
?>