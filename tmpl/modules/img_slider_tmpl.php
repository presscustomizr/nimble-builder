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
  function sek_print_img_slider( $img_collection, $slider_options, $model ) {
      ?>
        <div class="swiper-container sek-swiper<?php echo $model['id']; ?>" data-swiper-id="<?php echo $model['id']; ?>">
          <div class="swiper-wrapper">
          <?php
          //sek_error_log('$img_collection???', $img_collection );
          foreach( $img_collection as $item ) {
              $is_text_enabled = true === sek_booleanize_checkbox_val( $item['enable_text'] );
              $has_text_content = ! empty( $item['text_content'] );

              if ( skp_is_customizing() ) {
                    // text content uses post message, so we need to have the text content wrapper already printed
                    $text_content = sprintf('<div class="sek-slider-text-content">%1$s</div>', $item['text_content'] );
              } else {
                    $text_content = !$has_text_content ? '' : sprintf('<div class="sek-slider-text-content">%1$s</div>', $item['text_content'] );
              }

              $has_overlay = $is_text_enabled && true === sek_booleanize_checkbox_val( $item['apply_overlay'] );

              // Put them together
              printf( '<div class="swiper-slide" title="%1$s" data-sek-item-id="%4$s" data-sek-has-overlay="%5$s"><div class="sek-carousel-img">%2$s</div>%3$s</div>',
                  esc_attr( $item['title_attr'] ),
                  sek_get_img_slider_module_img_html( $item ),
                  $text_content,
                  $item['id'],
                  true === sek_booleanize_checkbox_val( $has_overlay ) ? 'true' : 'false'
              );

          }//foreach
          ?>
          </div><!-- swiper-container -->
        </div><!-- swiper-wrapper -->
        <!-- Add Arrows -->
        <div class="swiper-button-next swiper-button-next<?php echo $model['id']; ?>"></div>
        <div class="swiper-button-prev swiper-button-prev<?php echo $model['id']; ?>"></div>
      <?php
  }
}

$model = Nimble_Manager() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$img_collection = !empty($value['img_collection']) ? $value['img_collection'] : array();
$slider_options = !empty($value['slider_options']) ? $value['slider_options'] : array();

if ( !empty( $img_collection ) ) {
    sek_print_img_slider( $img_collection, $slider_options, $model );
} else {
    if ( skp_is_customizing() ) {
        printf( '<ul class="sek-social-icons-wrapper"><li class="sek-social-icons-placeholder"><span><i>%1$s</i></span></li></ul>',
            __('Click to start adding images.', 'hueman')
        );
    }
}
