<?php
// this.defaultItemModel = {
//     img : '',
//     'img-size' : 'large',
//     'alignment' : '',
//     'link-to' : '',
//     'link-pick-url' : '',
//     'link-custom-url' : '',
//     'link-target' : '',
//     'lightbox' : true
// };
$model = SEK_Front() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

if ( array_key_exists('img', $value ) && is_int( $value['img'] ) ) {
    echo wp_get_attachment_image( $value['img'], empty( $value['img-size'] ) ? 'large' : $value['img-size']);
} else if ( array_key_exists('img', $value ) && is_string( $value['img'] ) ) {
    ?>
      <img src="<?php echo $value['img']; ?>"/>
    <?php
} else {
    //falls back on an icon if previewing
    if ( skp_is_customizing() ) {
        echo SEK_Front() -> sek_get_input_placeholder_content( 'upload' );
    }
}

