<?php

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/629
// Firefox doesn not support preload
// IE is supposed to support it, but tests show that google fonts may not be loaded on each page refresh
function sek_preload_google_fonts_on_front() {
    // When preload is active, browser support is checked with javascript
    // with a fallback on regular style fetching
    // if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) // 'Internet explorer'
    //   return;
    // elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== FALSE) // 'Mozilla Firefox'
    //   return;
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['preload_google_fonts'] ) ) {
        return sek_booleanize_checkbox_val( $glob_perf['preload_google_fonts'] );
    }
    return false;
}

// @return bool
// march 2020 introduced for https://github.com/presscustomizr/nimble-builder/issues/635
function sek_load_front_assets_dynamically() {
    $glob_perf = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_perf ) && is_array( $glob_perf ) && !empty( $glob_perf['load_assets_in_ajax'] ) ) {
        return !skp_is_customizing() && sek_booleanize_checkbox_val( $glob_perf['load_assets_in_ajax'] );
    }
    return false;
}


// Adds defer attribute to enqueued / registered scripts.
// fired @wp_enqueue_scripts
function sek_defer_script($handle) {
    // Adds defer attribute to enqueued / registered scripts.
    wp_script_add_data( $handle, 'defer', true );
}

// oct 2020 => introduction of a normalized way to emit a js event to NB front api
// in particular to make sure NB doesn't print a <script> twice to emit the same event
function sek_emit_js_event( $event = '', $echo = true ) {
    $emitted = Nimble_Manager()->emitted_js_event;
    if ( !is_string($event) || in_array($event, $emitted) )
      return;
    $emitted[] = $event;
    Nimble_Manager()->emitted_js_event = $emitted;
    
    if ( $echo ) {
        $html = sprintf('(function(){if(window.nb_){nb_.emit("%1$s");}})();', $event );
        wp_register_script( 'nb_emit_' . $event, '');
        wp_enqueue_script( 'nb_emit_' . $event );
        wp_add_inline_script( 'nb_emit_' . $event, $html );
    } else {
        $html = sprintf('<script>(function(){if(window.nb_){nb_.emit("%1$s");}})();</script>', $event );
        return $html;
    }
}

?>