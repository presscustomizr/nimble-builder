<?php
/*
* To generate latest font awesome json file
*/
add_action( 'plugins_loaded', 'generate_json' );
function generate_json() {
    $base_path = NIMBLE_BASE_PATH . '/assets/front/fonts/webfonts';
    $json_dest = NIMBLE_BASE_PATH . '/assets/faicons.json';
    $files_src = array(
        'solid.svg'   => 'fas',
        'regular.svg' => 'far',
        'brands.svg'  => 'fab',
    );

    $icons = array();
    foreach ( $files_src as $file => $icon_prefix ) {
        // $xml will look like (a).
        $xml    = @simplexml_load_file( $base_path . '/' . $file );

        if ( ! $xml ) {
            continue;
        }

        /* if ( ! isset( $xml->defs->font->glyph ) || ! $xml->defs->font->glyph->count() ) {
            continue;
        } */
        if ( ! isset( $xml->symbol ) || ! $xml->symbol->count() ) {
            continue;
        }


        // $xml->defs->font->glyph is an array of (SimpleXMLElement Object(s)) icon defintions, as you can see in (a).
        $glyph = $xml->symbol;


        foreach( $glyph as $glyph_item ) {
            // Each icon name is accessible via the 'glyp-name' attribute.
            //$glyph_item_name = (string)$glyph_item->attributes()->{'glyph-name'}[0];
            $glyph_item_name = (string)$glyph_item->attributes()->{'id'}[0];

            // This associative array is created so that we can sort by the icon name.
            $icons[ $icon_prefix . ' fa-' . $glyph_item_name ] = $glyph_item_name;
        }
    }

    if ( empty( $icons ) ) {
        error_log( 'Font Awesome icon list is empty' );
    }
    /*
     * Sort icons by value (name) A-Z ASC and then by key DESC (far, fas)
     * This way we ensure multiple occurrences of the same icon name always respect the same order
     * See (c)
     */
    $icon_classes = array_keys( $icons );
    $icon_names   = array_values( $icons );
    array_multisort( $icon_names, SORT_ASC, $icon_classes, SORT_ASC );
    $icons        = array_combine( $icon_classes, $icon_names );


    // Now that all is sorted let's grab only the keys ( icon classes )
    $icon_keys = array_keys( $icons );

    //json generation
    $json      = json_encode( $icon_keys );

    if ( $json ) {
        //write the jstton
        @file_put_contents( $json_dest, $json );

        error_log( sprintf( 'Font Awesome json file generated at: %s', $json_dest ) );
    }
}//end function