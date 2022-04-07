<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'Nimble\sek_slider_find_pattern_match') ) {
    function sek_slider_find_pattern_match( $matches, $item ) {
        $img_attr = '';
        if ( empty( $item ) )
          return $img_attr;
        $replace_values = array('caption', 'title', 'description');
        if ( !in_array( $matches[1], $replace_values ) )
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

if ( !function_exists( 'Nimble\sek_slider_parse_template_tags') ) {
    // fired @filter 'nimble_parse_template_tags'
    function sek_slider_parse_template_tags( $val, $item = array() ) {
        //the pattern could also be '!\{\{(\w+)\}\}!', but adding \s? allows us to allow spaces around the term inside curly braces
        //see https://stackoverflow.com/questions/959017/php-regex-templating-find-all-occurrences-of-var#comment71815465_959026
        return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(\w+)\s?\}\}!', function( $matches ) use( $item ) {
            return sek_slider_find_pattern_match( $matches, $item );
        }, $val) : $val;
    }
}


if ( !function_exists('Nimble\sek_maybe_parse_slider_img_html_for_lazyload') ) {
    // @return html string
    function sek_maybe_parse_slider_img_html_for_lazyload( $attachment_id, $is_first_img, $lazy_load_on, $size = 'thumbnail' ) {
        // Skip when :
        // - is customizing
        // - slider lazy loading is not active
        // - global Nimble lazy load is active, and this is the first image ( in this case we want to lazy load the first image of the slider if offscreen )
        if ( skp_is_customizing() || !$lazy_load_on || ( sek_is_img_smartload_enabled() && $is_first_img ) ) {
            // Nov 2020 : removes any additional styles added by a theme ( Twenty Twenty one ) or a plugin to the image
            add_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );
            $img_html = wp_get_attachment_image( $attachment_id, $size );
            remove_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );
            return $img_html;
        }

        // If lazy loaded, preprocess the image like wp_get_attachment_image()
        // added in dec 2019 for https://github.com/presscustomizr/nimble-builder/issues/570
        $html  = '';
        $image = wp_get_attachment_image_src( $attachment_id, $size, $icon = false );
        if ( $image ) {
            list($src, $width, $height) = $image;
            $hwstring                   = image_hwstring( $width, $height );
            $size_class                 = $size;
            if ( is_array( $size_class ) ) {
                $size_class = join( 'x', $size_class );
            }
            $attachment   = get_post( $attachment_id );
            $default_attr = array(
                'src'   => $src,
                'class' => "attachment-$size_class size-$size_class swiper-lazy",// add swiper class for lazyloading @see https://swiperjs.com/api/#lazy
                'alt'   => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
            );

            $attr = $default_attr;

            // Generate 'srcset' and 'sizes' if not already present.
            if ( empty( $attr['srcset'] ) ) {
                $image_meta = wp_get_attachment_metadata( $attachment_id );

                if ( is_array( $image_meta ) ) {
                    $size_array = array( absint( $width ), absint( $height ) );
                    $srcset     = wp_calculate_image_srcset( $size_array, $src, $image_meta, $attachment_id );
                    $sizes      = wp_calculate_image_sizes( $size_array, $src, $image_meta, $attachment_id );

                    if ( $srcset && ( $sizes || !empty( $attr['sizes'] ) ) ) {
                        $attr['srcset'] = $srcset;

                        if ( empty( $attr['sizes'] ) ) {
                            $attr['sizes'] = $sizes;
                        }
                    }
                }
            }

            /**
             * Filters the list of attachment image attributes.
             *
             * @since 2.8.0
             *
             * @param array        $attr       Attributes for the image markup.
             * @param WP_Post      $attachment Image attachment post.
             * @param string|array $size       Requested size. Image size or array of width and height values
             *                                 (in that order). Default 'thumbnail'.
             */
            // Nov 2020 : removes any additional styles added by a theme ( Twenty Twenty one ) or a plugin to the image
            add_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );
            $attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $size );
            remove_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );

            // add swiper data-* stuffs for lazyloading now, after all filters
            // @see https://swiperjs.com/api/#lazy
            if ( !empty( $attr['srcset'] ) ) {
                $attr['data-srcset'] = $attr['srcset'];
                unset( $attr['srcset'] );
            }

            // april 22 : deactivated when implementing late escape for #885 because it breaks data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7
            // No idea how to escape this without breaking it for now
            // if ( !empty( $attr['src'] ) ) {
            //     $attr['data-src'] = $attr['src'];
            //     $attr['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            //     //unset( $attr['src'] );
            // }
            if ( !empty( $attr['sizes'] ) ) {
                $attr['data-sek-img-sizes'] = $attr['sizes'];
                unset( $attr['sizes'] );
            }

            $attr = array_map( 'esc_attr', $attr );
            $html = rtrim( "<img $hwstring" );
            foreach ( $attr as $name => $value ) {
                $html .= " $name=" . '"' . $value . '"';
            }
            $html .= ' />';
        }

        return $html;
    }
}

if ( !function_exists( 'Nimble\sek_get_img_slider_module_img_html') ) {
    function sek_get_img_slider_module_img_html( $item, $lazy_load_on, $index ) {
        $html = '';
        $is_first_img = 0 == $index;
        if ( is_int( $item['img'] ) ) {
            // don't parse the first image of the carousel for lazyloading
            // @see https://github.com/presscustomizr/nimble-builder/issues/596 ( Lazy load break layout of first slide )
            // if ( $lazy_load_on && !$is_first_img ) {
            //     $html = sek_maybe_parse_slider_img_html_for_lazyload(
            //       $item['img'],
            //       empty( $item['img-size'] ) ? 'large' : $item['img-size'],
            //       $is_first_img//<= // when lazy load is active, we want to lazy load the first image of the slider if offscreen by adding 'data-sek-src' attribute
            //     );
            //     $html .= '<div class="swiper-lazy-preloader"></div>';//this element is removed by swiper.js once the image is loaded @see https://swiperjs.com/api/#lazy
            // } else {
            //     $html = wp_get_attachment_image( $item['img'], empty( $item['img-size'] ) ? 'large' : $item['img-size']);
            // }

            $html = sek_maybe_parse_slider_img_html_for_lazyload(
              $item['img'],
              $is_first_img,//<= // when lazy load is active, we want to lazy load the first image of the slider if offscreen by adding 'data-sek-src' attribute
              $lazy_load_on,
              empty( $item['img-size'] ) ? 'large' : $item['img-size']
            );
            if ( $lazy_load_on && !$is_first_img ) {
                $html .= '<div class="swiper-lazy-preloader"></div>';//this element is removed by swiper.js once the image is loaded @see https://swiperjs.com/api/#lazy
            }
        } else if ( !empty( $item['img'] ) && is_string( $item['img'] ) ) {
            // the default img is excluded from the Nimble Builder smart loading parsing @see nimble_regex_callback()
            // => this is needed because this image has no specific dimensions set. And therefore can create false javascript computations of other element's distance to top on page load.
            // in particular when calculting if is_visible() to decide if we smart load.
            $html = sprintf( '<img alt="default img" data-skip-lazyload="true" src="%1$s"/>', esc_url( $item['img'] )  );
        }
        return $html;
    }
}


if ( !function_exists( 'Nimble\sek_print_img_slider' ) ) {
  function sek_print_img_slider( $img_collection, $slider_options, $model ) {
      $img_collection = is_array( $img_collection ) ? $img_collection : array();
      $is_multislide = count( $img_collection ) > 1;
      $autoplay = ( !skp_is_customizing() && true === sek_booleanize_checkbox_val( $slider_options['autoplay'] ) ) ? "true" : "false";
      // don't authorize value < 300 ms
      $autoplay_delay = intval( $slider_options['autoplay_delay'] ) < 300 ? 1000 : intval( $slider_options['autoplay_delay'] );
      $pause_on_hover = true === sek_booleanize_checkbox_val( $slider_options['pause_on_hover'] ) ? "true" : "false";
      $loop_on = true === sek_booleanize_checkbox_val( $slider_options['infinite_loop'] ) ? "true" : "false";
      $lazy_load_on = true === sek_booleanize_checkbox_val( $slider_options['lazy_load'] ) ? "true" : "false";
      $nav_type = ( is_string( $slider_options['nav_type'] ) && !empty( $slider_options['nav_type'] ) ) ? esc_attr($slider_options['nav_type']) : 'arrows_dots';
      $hide_nav_on_mobiles = true === sek_booleanize_checkbox_val( $slider_options['hide_nav_on_mobiles'] );
      ?>
        <?php printf('<div class="swiper sek-swiper-loading sek-swiper%1$s" data-sek-swiper-id="%1$s" data-sek-autoplay="%2$s" data-sek-autoplay-delay="%3$s" data-sek-pause-on-hover="%4$s" data-sek-loop="%5$s" data-sek-image-layout="%6$s" data-sek-navtype="%7$s" data-sek-is-multislide="%8$s" data-sek-hide-nav-on-mobile="%9$s" data-sek-lazyload="%10$s" %11$s>',
            esc_attr($model['id']),
            esc_attr($autoplay),
            esc_attr($autoplay_delay),
            esc_attr($pause_on_hover),
            esc_attr($loop_on),
            esc_attr($slider_options['image-layout']),
            esc_attr($nav_type),
            $is_multislide ? 'true' : 'false',
            $hide_nav_on_mobiles ? 'true' : 'false',
            esc_attr($lazy_load_on),
            wp_kses_post(apply_filters('nb_slider_wrapper_custom_attributes', '', $slider_options, $model ))
          ); ?>
          <?php if ( is_array( $img_collection ) && count( $img_collection ) > 0 ) : ?>
            <div class="swiper-wrapper">
              <?php
              foreach( $img_collection as $index => $item ) {
                  $is_text_enabled = true === sek_booleanize_checkbox_val( $item['enable_text'] );
                  $text_content = $is_text_enabled ? $item['text_content'] : '';
                  $has_text_content = !empty( $text_content );

                  // Feb 2021 : now saved as a json to fix emojis issues
                  // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
                  // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
                  $text_content = sek_maybe_decode_richtext( $text_content );
                  $text_content = sek_strip_script_tags( $text_content );
                  $text_html = sprintf('<div class="sek-slider-text-wrapper"><div class="sek-slider-text-content">%1$s</div></div>', $text_content );
                  if ( !skp_is_customizing() ) {
                      $text_html = !$has_text_content ? '' : $text_html;
                  }

                  $has_overlay = true === sek_booleanize_checkbox_val( $item['apply-overlay'] );

                  // Put them together
                  $to_render = sprintf( '<div class="swiper-slide" title="%1$s" data-sek-item-id="%4$s" data-sek-has-overlay="%5$s" %6$s><figure class="sek-carousel-img">%2$s</figure>%3$s</div>',
                      sek_slider_parse_template_tags( strip_tags( esc_attr( $item['title_attr'] ) ), $item ),
                      sek_get_img_slider_module_img_html( $item, "true" === $lazy_load_on, $index ),
                      sek_slider_parse_template_tags( $text_html, $item ),
                      esc_attr($item['id']),
                      true === sek_booleanize_checkbox_val( $has_overlay ) ? 'true' : 'false',
                      esc_attr( apply_filters('nb_single_slide_custom_attributes', '', $item, $model ) )
                  );
                  echo skp_is_customizing() ? wp_kses_post($to_render) : apply_filters( 'nimble_parse_for_smart_load', wp_kses_post($to_render) );

              }//foreach
              ?>
            </div><?php //.swiper-wrapper ?>
          <?php endif; ?>
          <?php if ( in_array($nav_type,array('arrows_dots', 'dots') ) && $is_multislide ) : ?>
            <div class="swiper-pagination swiper-pagination<?php echo esc_attr($model['id']); ?>"></div>
          <?php endif; ?>

          <?php if ( in_array($nav_type,array('arrows_dots', 'arrows') ) && $is_multislide ) : ?>
            <div class="sek-swiper-nav">
              <div class="sek-swiper-arrows sek-swiper-prev sek-swiper-prev<?php echo esc_attr($model['id']); ?>" title="<?php _e('previous', 'textdom'); ?>"><div class="sek-chevron"></div></div>
              <div class="sek-swiper-arrows sek-swiper-next sek-swiper-next<?php echo esc_attr($model['id']); ?>" title="<?php _e('next', 'textdom'); ?>"><div class="sek-chevron"></div></div>
            </div>
          <?php endif; ?>
          <?php
            if ( !skp_is_customizing() ) {
              echo '<div class="sek-css-loader sek-mr-loader"><div></div><div></div><div></div></div>';
            }
          ?>
        </div><?php //.swiper ?>

      <?php
  }
}

$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$img_collection = !empty($value['img_collection']) ? $value['img_collection'] : array();
$slider_options = !empty($value['slider_options']) ? $value['slider_options'] : array();

if ( !empty( $img_collection ) ) {
    sek_print_img_slider( $img_collection, $slider_options, $model );
    sek_emit_js_event('nb-needs-swiper');
} else {
    if ( skp_is_customizing() ) {
        printf( '<div class="sek-mod-preview-placeholder"><div class="sek-preview-ph-text" style="%2$s"><p>%1$s</p></div></div>',
            __('Click to start adding images.', 'text_doma'),
            'background: url(' . esc_url(NIMBLE_MODULE_ICON_PATH) . 'Nimble_slideshow_icon.svg) no-repeat 50% 75%;background-size: 200px;'
        );
    }
}
