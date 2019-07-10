<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'Nimble\sek_slider_find_pattern_match') ) {
    function sek_slider_find_pattern_match( $matches, $item ) {
        $img_attr = '';
        if ( empty( $item ) )
          return $img_attr;
        $replace_values = array('caption', 'title', 'description');
        if ( ! in_array( $matches[1], $replace_values ) )
          return $img_attr;


        $requested_img_attr = $matches[1];
        //   'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
        //   'caption' => $attachment->post_excerpt,
        //   'description' => $attachment->post_content,
        //   'href' => get_permalink( $attachment->ID ),
        //   'src' => $attachment->guid,
        //   'title' => $attachment->post_title
        $img_post = get_post( $item['img'] );
        if ( is_int( $item['img'] ) ) {
          $img_post = get_post( $item['img'] );
          if ( !is_wp_error( $img_post ) && is_object( $img_post ) && 'attachment' === $img_post->post_type ) {
            switch( $requested_img_attr ) {
              case 'caption' :
                $img_attr = $img_post->post_excerpt;
              break;
              case 'description' :
                $img_attr = $img_post->post_content;
              break;
              case 'title' :
                $img_attr = $img_post->post_title;
              break;
            }
          }
        }

        return $img_attr;
    }
}

if ( ! function_exists( 'Nimble\sek_slider_parse_template_tags') ) {
    // fired @filter 'nimble_parse_template_tags'
    function sek_slider_parse_template_tags( $val, $item = array() ) {
        //the pattern could also be '!\{\{(\w+)\}\}!', but adding \s? allows us to allow spaces around the term inside curly braces
        //see https://stackoverflow.com/questions/959017/php-regex-templating-find-all-occurrences-of-var#comment71815465_959026
        return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(\w+)\s?\}\}!', function( $matches ) use( $item ) {
            return sek_slider_find_pattern_match( $matches, $item );
        }, $val) : $val;
    }
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
            //falls back on an a placeholder
            // if ( skp_is_customizing() ) {
            //     $html = Nimble_Manager() -> sek_get_input_placeholder_content( 'upload' );
            // }
            $html = sprintf( '<img alt="default img" data-sek-smartload="false" src="%1$s"/>', NIMBLE_BASE_URL . '/assets/img/default-img.png'  );
        }
        return $html;
        //return apply_filters( 'nimble_parse_for_smart_load', sprintf('<figure class="%1$s" title="%3$s">%2$s</figure>', $visual_effect_class, $html, esc_html( $title ) ) );
    }
}


if ( ! function_exists( 'Nimble\sek_print_img_slider' ) ) {
  function sek_print_img_slider( $img_collection, $slider_options, $model ) {
      $autoplay = ( ! skp_is_customizing() && true === sek_booleanize_checkbox_val( $slider_options['autoplay'] ) ) ? "true" : "false";
      // don't authorize value < 300 ms
      $autoplay_delay = intval( $slider_options['autoplay_delay'] ) < 300 ? 1000 : intval( $slider_options['autoplay_delay'] );
      $pause_on_hover = true === sek_booleanize_checkbox_val( $slider_options['pause_on_hover'] ) ? "true" : "false";;
      $loop_on = true === sek_booleanize_checkbox_val( $slider_options['infinite_loop'] ) ? "true" : "false";
      ?>
        <?php printf('<div class="swiper-container sek-swiper%1$s" data-sek-swiper-id="%1$s" data-sek-autoplay="%2$s" data-sek-autoplay-delay="%3$s" data-sek-pause-on-hover="%4$s" data-sek-loop="%5$s" data-sek-image-layout="%6$s">',
            $model['id'],
            $autoplay,
            $autoplay_delay,
            $pause_on_hover,
            $loop_on,
            $slider_options['image-layout']
          ); ?>
          <div class="swiper-wrapper">
          <?php
          //sek_error_log('slider_collection???', $img_collection );
          foreach( $img_collection as $item ) {
              $is_text_enabled = true === sek_booleanize_checkbox_val( $item['enable_text'] );
              $text_content = $is_text_enabled ? $item['text_content'] : '';
              $has_text_content = ! empty( $text_content );
              $text_html = sprintf('<div class="sek-slider-text-wrapper"><div class="sek-slider-text-content">%1$s</div></div>', $text_content );
              if ( ! skp_is_customizing() ) {
                  $text_html = !$has_text_content ? '' : $text_html;
              }

              $has_overlay = true === sek_booleanize_checkbox_val( $item['apply-overlay'] );

              // Put them together
              printf( '<div class="swiper-slide" title="%1$s" data-sek-item-id="%4$s" data-sek-has-overlay="%5$s"><figure class="sek-carousel-img">%2$s</figure>%3$s</div>',
                  sek_slider_parse_template_tags( esc_html( esc_attr( $item['title_attr'] ) ), $item ),
                  sek_get_img_slider_module_img_html( $item ),
                  sek_slider_parse_template_tags( $text_html, $item ),
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
        printf( '<div class="sek-slider-placeholder"><i>%1$s</i></div>',
            __('Click to start adding images.', 'hueman')
        );
    }
}
