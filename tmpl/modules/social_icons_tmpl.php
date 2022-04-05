<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'Nimble\sek_print_social_links' ) ) {
  function sek_print_social_links( $icons_collection, $icons_style ) {
      // Add more protocols to be allowed as safe urls. See: https://github.com/presscustomizr/nimble-builder/issues/461:wq
      $allowed_protocols =  array_merge( (array) wp_allowed_protocols(), array( 'skype', 'callto' ) );

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

              $link_attr = array();
              // target attr.
              $link_attr[] = false != $item['link_target'] ? 'target="_blank"' : '';
              // rel attr.
              $link_attr[] = false != $item['link_target'] ? 'rel="nofollow noopener noreferrer"' : 'rel="nofollow"';

              // Put them together
              printf( '<li data-sek-item-id="%5$s"><a title="%1$s" aria-label="%1$s" href="%2$s" %3$s>%4$s<span class="screen-reader-text">%6$s</span></a></li>',
                  esc_attr( $item['title_attr'] ),
                  (isset($item['link']) && !empty( $item['link'] )) ? esc_url( $item['link'], $allowed_protocols ) : 'javascript:void(0)',
                  esc_attr( implode( ' ', $link_attr ) ),
                  wp_kses_post( ( ( empty( $item['icon'] ) || !is_string( $item['icon'] ) ) && skp_is_customizing() ) ? '<i class="material-icons">pan_tool</i>' : '<i class="sek-social-icon ' . $item['icon'] .'"></i>' ),
                  esc_attr( $item['id'] ),
                  wp_kses_post(( empty( $item['icon'] ) || !is_string( $item['icon'] ) ) ? 'social-link' : $item['icon'] )
              );
          }//foreach
      echo '</ul>';

  }
}

$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$icons_collection = !empty($value['icons_collection']) ? $value['icons_collection'] : array();
$icons_style = !empty($value['icons_style']) ? $value['icons_style'] : array();

if ( !empty( $icons_collection ) ) {
    sek_print_social_links( $icons_collection, $icons_style );
    sek_emit_js_event('nb-needs-fa');
} else {
    if ( skp_is_customizing() ) {
        printf( '<div class="sek-mod-preview-placeholder"><div class="sek-preview-ph-text" style="%2$s"><p>%1$s</p></div></div>',
            __('Click to start adding social icons.', 'text_doma'),
            'background: url(' . esc_url(NIMBLE_MODULE_ICON_PATH) . 'Nimble_social_icon.svg) no-repeat 50% 75%;background-size: 150px;'
        );
    }
}
