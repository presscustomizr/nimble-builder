<?php
function SEK_Front( $params = array() ) {
    return SEK_Front_Render_Css::sek_get_instance( $params );
}
SEK_Front();
?>