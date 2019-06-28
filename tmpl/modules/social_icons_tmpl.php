<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();

$icons_collection = !empty($value['icons_collection']) ? $value['icons_collection'] : array();
$icons_style = !empty($value['icons_style']) ? $value['icons_style'] : array();


// sek_error_log('ALORS MODEL SOCIAL ICONS', $model );



if ( ! function_exists( 'Nimble\sek_print_social_links' ) ) {
  function sek_print_social_links( $icons_collection, $icons_style ) {
      echo '<ul class="sek-social-icons-wrapper">';
          foreach( $icons_collection as $item ) {
              // normalize
              $item = !is_array( $item ) ? array() : $item;
              $default_item = array(
                  'id' => '',
                  'icon' => '',
                  'link' => '',
                  'title_attr' => '',
                  'link_target' => false,
                  'color_css' => '',
                  'use_custom_color_on_hover' => false,
                  'color_hover' => ''
              );

              $item = wp_parse_args( $item, $default_item );

              // links like tel:*** or skype:**** or call:**** should work
              // implemented for https://github.com/presscustomizr/social-links-modules/issues/7
              $social_link = 'javascript:void(0)';
              if ( isset($item['link']) && ! empty( $item['link'] ) ) {
                  if ( false !== strpos($item['link'], 'callto:') || false !== strpos($item['link'], 'tel:') || false !== strpos($item['link'], 'skype:') ) {
                      $social_link = esc_attr( $item['link'] );
                  } else {
                      $social_link = esc_url( $item['link'] );
                  }
              }

              // Put them together
              printf( '<li data-sek-item-id="%5$s"><a rel="nofollow" title="%1$s" aria-label="%1$s" href="%2$s" %3$s>%4$s</a></li>',
                  esc_attr( $item['title_attr'] ),
                  $social_link,
                  false != $item['link_target'] ? 'target="_blank"' : '',
                  ( ( empty( $item['icon'] ) || ! is_string( $item['icon'] ) ) && skp_is_customizing() ) ? '<i class="material-icons">pan_tool</i>' : '<i class="sek-social-icon ' . $item['icon'] .'"></i>',
                  $item['id']
              );
          }//foreach
      echo '</ul>';
  }
}


if ( !empty( $icons_collection ) ) {
    sek_print_social_links( $icons_collection, $icons_style );
} else {
    if ( skp_is_customizing() ) {
        printf( '<ul class="sek-social-icons-wrapper"><li class="sek-social-icons-placeholder"><span><i>%1$s</i></span></li></ul>',
            __('Click to start adding social icons.', 'hueman')
        );
    }
}
