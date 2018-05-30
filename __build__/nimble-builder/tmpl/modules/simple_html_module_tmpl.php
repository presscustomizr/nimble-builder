<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Front() -> model;
if ( ! is_array( $model ) ) {
  error_log( 'module_tmpl => $model should be an array' );
  return;
}
if ( ! array_key_exists( 'module_type', $model ) ) {
    error_log( 'module_tmpl => a module type must be provided' );
    return;
}
$module_type = $model['module_type'];
// print the module content if not empty
if ( ! array_key_exists( 'value', $model ) ) {
  return;
} else {
  if ( array_key_exists('html_content', $model['value'] ) ) {
      $module_content = $model['value']['html_content'];
      if ( empty( $module_content ) ) {
          // $placeholder_icon = sek_get_registered_module_type_property( $module_type, 'placeholder_icon' );
          // sek_get_module_placeholder( $placeholder_icon );
        SEK_Front() -> sek_get_input_placeholder_content( 'text', 'html_content' );
      } else {
          ?>
            <p><?php echo $module_content; ?></p>
          <?php
      }
  }
}

