<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

//sek_error_log('Simple form value', $value);
$visual_effect_class = '';
//visual effect classes
if ( true === sek_booleanize_checkbox_val( $value['fields_design']['use_outset_shadow'] ) ) {
    $visual_effect_class .= 'use-outset-shadow';
}
if ( true === sek_booleanize_checkbox_val( $value['fields_design']['use_inset_shadow'] ) ) {
    $visual_effect_class .= ' use-inset-shadow';
}
?>
<div class="sek-simple-form-wrapper <?php echo $visual_effect_class; ?>">
  <?php echo Nimble_Manager()->get_simple_form_html( $model ); ?>
</div>