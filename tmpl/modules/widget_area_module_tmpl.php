<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$widget_area_has_at_least_one_widget = is_active_sidebar( $value['widget-area-id'] );

if ( !function_exists( 'Nimble\sek_maybe_print_widget_placeholder') ) {
  function sek_maybe_print_widget_placeholder( $id ) {
      global $wp_registered_sidebars;
      if ( !sek_is_nimble_widget_id( $id ) || !array_key_exists( $id, $wp_registered_sidebars ) ) {
          $placeholder_text = __('Select a Nimble widget area and start adding widgets.', 'text_domain_to_replace');
      } else {
          $widget_area_model = $wp_registered_sidebars[ $id ];
          $placeholder_text = sprintf( '%1$s <span class="zone-name" style="font-weight:bold">%2$s</span>',
              __('Add widgets to', 'text_domain_to_replace'),
              isset( $widget_area_model['name'] ) ? $widget_area_model['name'] : $widget_area_model['id']
          );
      }

      printf('<div class="widget" data-czr-panel-focus="widgets"><div class="czr-placeholder-widget" %1$s><h3 %2$s>%3$s</h3></div></div>',
          'style="background:#f7f8f9;padding:30px;text-align:center;outline:3px dotted #858585;;font-size:.875em;"',
          'style="margin:0.5em;font-size:17px;line-height:1.5em;color:#444"',
          wp_kses_post($placeholder_text)
      );
  }
}

if ( ! sek_is_widget_module_disabled() ) {
    if ( array_key_exists( 'widget-area-id', $value ) && is_string( $value['widget-area-id'] )  ) {
        if ( is_active_sidebar( $value['widget-area-id'] ) ) {
            dynamic_sidebar( $value['widget-area-id'] );
        } else {
            if ( skp_is_customizing() ) {
            sek_maybe_print_widget_placeholder( $value['widget-area-id'] );
            }
        }
    }
}