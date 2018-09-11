<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = SEK_Front() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

//sek_error_log('Simple form value', $value);

?>
<div class="simple-form-wrapper">
  <?php echo SEK_Front() -> get_simple_form_html( $value['form_fields'] ); ?>
</div>