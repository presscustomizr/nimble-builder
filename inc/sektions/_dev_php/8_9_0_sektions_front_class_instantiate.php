<?php
// invoked ( and instantiated ) when skp_is_customizing()
function SEK_CZR_Dyn_Register( $params = array() ) {
    if (  skp_is_customizing() ) {
        return SEK_CZR_Dyn_Register::get_instance( $params );
    }
}
add_action('after_setup_theme', '\Nimble\SEK_CZR_Dyn_Register', 20 );

function SEK_Front( $params = array() ) {
    return SEK_Front_Render_Css::get_instance( $params );
}

SEK_Front();
?>