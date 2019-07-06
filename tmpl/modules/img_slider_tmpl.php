<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'Nimble\sek_get_img_slider_module_img_html') ) {
    function sek_get_img_slider_module_img_html( $item ) {
        $html = '';
        if ( is_int( $item['img'] ) ) {
            $html = wp_get_attachment_image( $item['img'], empty( $item['img-size'] ) ? 'large' : $item['img-size']);
        } else if ( ! empty( $item['img'] ) && is_string( $item['img'] ) ) {
            // the default img is excluded from the smart loading parsing @see nimble_regex_callback()
            // => this is needed because this image has no specific dimensions set. And therefore can create false javascript computations of other element's distance to top on page load.
            // in particular when calculting if is_visible() to decide if we smart load.
            $html = sprintf( '<img alt="default img" data-sek-smartload="false" src="%1$s"/>', esc_url(  $item['img'] )  );
        } else {
            //falls back on an icon if previewing
            if ( skp_is_customizing() ) {
                $html = Nimble_Manager() -> sek_get_input_placeholder_content( 'upload' );
            }
        }
        return $html;
        //return apply_filters( 'nimble_parse_for_smart_load', sprintf('<figure class="%1$s" title="%3$s">%2$s</figure>', $visual_effect_class, $html, esc_html( $title ) ) );
    }
}


if ( ! function_exists( 'Nimble\sek_print_img_slider' ) ) {
  function sek_print_img_slider( $img_collection, $slider_options ) {
      ?>
        <div class="swiper-container">
          <div class="swiper-wrapper">
          <?php
          foreach( $img_collection as $item ) {
              // normalize
              $item = !is_array( $item ) ? array() : $item;
              $default_item = array(
                  'id' => '',
                  'img' => ''
              );

              $item = wp_parse_args( $item, $default_item );

              // links like tel:*** or skype:**** or call:**** should work
              // implemented for https://github.com/presscustomizr/social-links-modules/issues/7
              // $social_link = 'javascript:void(0)';
              // if ( isset($item['link']) && ! empty( $item['link'] ) ) {
              //     if ( false !== strpos($item['link'], 'callto:') || false !== strpos($item['link'], 'tel:') || false !== strpos($item['link'], 'skype:') ) {
              //         $social_link = esc_attr( $item['link'] );
              //     } else {
              //         $social_link = esc_url( $item['link'] );
              //     }
              // }

              // Put them together
              printf( '<div class="swiper-slide" title="%1$s">%2$s</div>',
                  esc_attr( $item['title_attr'] ),
                  sek_get_img_slider_module_img_html( $item )
              );

          }//foreach
          ?>
          </div><!-- swiper-container -->
        </div><!-- swiper-wrapper -->
        <!-- Add Arrows -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
      <?php
  }
}

$model = Nimble_Manager() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$img_collection = !empty($value['img_collection']) ? $value['img_collection'] : array();
$slider_options = !empty($value['slider_options']) ? $value['slider_options'] : array();

if ( !empty( $img_collection ) ) {
    sek_print_img_slider( $img_collection, $slider_options );
} else {
    if ( skp_is_customizing() ) {
        printf( '<ul class="sek-social-icons-wrapper"><li class="sek-social-icons-placeholder"><span><i>%1$s</i></span></li></ul>',
            __('Click to start adding images.', 'hueman')
        );
    }
}
