<?php
// invoked ( and instantiated ) when skp_is_customizing()
function SEK_CZR_Dyn_Register( $params = array() ) {
    return SEK_CZR_Dyn_Register::get_instance( $params );
}
function SEK_Front( $params = array() ) {
    return SEK_Front_Render_Css::get_instance( $params );
}
if (  skp_is_customizing() ) {
  SEK_CZR_Dyn_Register();
}
SEK_Front();
?>