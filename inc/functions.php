<?php
// @return void()
function nimble_register_location( $location, $params = array() ) {
    if ( empty( $location ) || !is_string( $location ) )
      return;
    \Nimble\register_location( $location, $params );
}

//@param $locations. mixed type
//@param $options (array)$options = wp_parse_args( $options, array(
//     'fallback_location' => null, // Typically set as 'loop_start' in the nimble templates
// ));
function render_nimble_locations( $locations, $options = array() ) {
    \Nimble\render_nimble_locations( $locations, $options );
}

function nimble_get_content_as_json() {
    $skope_id = \Nimble\skp_get_skope_id();
    // bail now if called before skope_id is set (before @hook 'wp')
    if ( empty( $skope_id ) || '_skope_not_set_' === $skope_id )
        return '{}';

    $global_sections = \Nimble\sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
    $local_sections = \Nimble\sek_get_skoped_seks( $skope_id );
    $raw_content = \Nimble\sek_sniff_and_decode_richtext([
        'local_sections' => $local_sections,
        'global_sections' => $global_sections
    ]);
    return wp_json_encode( $raw_content );
}

function nimble_allow_display_attribute( $styles ){
    $styles[] = 'display';
    return $styles;
}