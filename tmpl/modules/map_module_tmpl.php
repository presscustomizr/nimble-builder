<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();


if ( empty( $value['address'] ) ) {
    return;
}

if ( 0 === absint( $value['zoom'] ) ) {
    $value['zoom'] = 10;
}

printf(
    '<div class="sek-embed"><iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=%s&amp;t=m&amp;z=%d&amp;output=embed&amp;iwloc=near" aria-label="%s"></iframe></div>',
    rawurlencode( $value['address'] ),
    absint( $value['zoom'] ),
    esc_attr( $value['address'] )
);