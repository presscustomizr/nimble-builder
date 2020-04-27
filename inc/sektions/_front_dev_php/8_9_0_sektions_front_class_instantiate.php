<?php

if ( !class_exists( '\Nimble\Sek_Nimble_Manager' ) ) :
  final class Sek_Nimble_Manager extends Sek_Simple_Form {}
endif;

function Nimble_Manager( $params = array() ) {
    return Sek_Nimble_Manager::get_instance( $params );
}

Nimble_Manager();
?>