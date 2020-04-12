<?php

/* ------------------------------------------------------------------------- *
 *  IMAGE HELPER
/* ------------------------------------------------------------------------- */
// @see https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
// used in sek_get_select_options_for_input_id()
function sek_get_img_sizes() {
    global $_wp_additional_image_sizes;

    $sizes = array();
    $to_return = array(
        'original' => __('Original image dimensions', 'text_doma')
    );

    foreach ( get_intermediate_image_sizes() as $_size ) {

        $first_to_upper_size = ucfirst(strtolower($_size));
        $first_to_upper_size = preg_replace_callback( '/[.!?].*?\w/', '\Nimble\sek_img_sizes_preg_replace_callback', $first_to_upper_size );

        if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
            $sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
            $sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
            $sizes[ $_size ]['title'] =  $first_to_upper_size;
            //$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
        } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
            $sizes[ $_size ] = array(
                'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                'title' =>  $first_to_upper_size
                //'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
            );
        }
    }
    foreach ( $sizes as $_size => $data ) {
        $to_return[ $_size ] = $data['title'] . ' - ' . $data['width'] . ' x ' . $data['height'];
    }

    return $to_return;
}

function sek_img_sizes_preg_replace_callback( $matches ) {
    return strtoupper( $matches[0] );
}





/* ------------------------------------------------------------------------- *
 *  SMART LOAD HELPER FOR IMAGES AND VIDEOS
/* ------------------------------------------------------------------------- */
/**
* callback of preg_replace_callback in SEK_Front_Render::sek_maybe_process_img_for_js_smart_load
* @return string
*/
function nimble_regex_callback( $matches ) {
    // bail if the img has already been parsed for swiper slider lazyloading ( https://github.com/presscustomizr/nimble-builder/issues/596 )
    if ( false !== strpos( $matches[0], 'data-srcset' ) || false !== strpos( $matches[0], 'data-src' ) ) {
      return $matches[0];
    // bail if already parsed by this regex or if smartload is disabled
    } else if ( false !== strpos( $matches[0], 'data-sek-src' ) || preg_match('/ data-sek-smartload *= *"false" */', $matches[0]) ) {
      return $matches[0];
    // otherwise go ahead and parse
    } else {
      return apply_filters( 'nimble_img_smartloaded',
        str_replace( array('srcset=', 'sizes='), array('data-sek-srcset=', 'data-sek-sizes='),
            sprintf('<img %1$s src="%2$s" data-sek-src="%3$s" %4$s>',
                $matches[1],
                'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
                $matches[2],
                $matches[3]
            )
        )
      );
    }
}


// @return boolean
// img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
// the local option wins
// if local is set to inherit, return the global option
// This option is cached
// deactivated when customizing
function sek_is_img_smartload_enabled() {
    // condition added in april 2020 when implementing yoast compat https://github.com/presscustomizr/nimble-builder/issues/657
    if ( is_admin() && !skp_is_customizing() )
      return false;

    if ( 'not_cached' !== Nimble_Manager()->img_smartload_enabled ) {
        return Nimble_Manager()->img_smartload_enabled;
    }

    $is_img_smartload_enabled = false;
    // LOCAL OPTION
    // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
    // otherwise we fallback on the normal utility skp_build_skope_id()
    $local_performances_data = sek_get_local_option_value( 'local_performances' );
    $local_smartload = 'inherit';
    if ( !is_null( $local_performances_data ) && is_array( $local_performances_data ) ) {
        if ( ! empty( $local_performances_data['local-img-smart-load'] ) && 'inherit' !== $local_performances_data['local-img-smart-load'] ) {
              $local_smartload = 'yes' === $local_performances_data['local-img-smart-load'];
        }
    }

    if ( 'inherit' !== $local_smartload ) {
        $is_img_smartload_enabled = $local_smartload;
    } else {
        // GLOBAL OPTION
        $glob_performances_data = sek_get_global_option_value( 'performances' );
        if ( !is_null( $glob_performances_data ) && is_array( $glob_performances_data ) && !empty( $glob_performances_data['global-img-smart-load'] ) ) {
            $is_img_smartload_enabled = sek_booleanize_checkbox_val( $glob_performances_data['global-img-smart-load'] );
        }
    }

    // CACHE THE OPTION
    Nimble_Manager()->img_smartload_enabled = $is_img_smartload_enabled;

    return Nimble_Manager()->img_smartload_enabled;
}


// @return boolean
// video background lazy load can be set globally with 'global-bg-video-lazy-load'
// implemented in nov 2019 for https://github.com/presscustomizr/nimble-builder/issues/287
// This option is cached
function sek_is_video_bg_lazyload_enabled() {
    // if ( skp_is_customizing() )
    //   return false;
    if ( 'not_cached' !== Nimble_Manager()->video_bg_lazyload_enabled ) {
        return Nimble_Manager()->video_bg_lazyload_enabled;
    }
    $is_video_bg_lazyload_enabled = false;
    $glob_performances_data = sek_get_global_option_value( 'performances' );
    if ( !is_null( $glob_performances_data ) && is_array( $glob_performances_data ) && !empty( $glob_performances_data['global-bg-video-lazy-load'] ) ) {
        $is_video_bg_lazyload_enabled = sek_booleanize_checkbox_val( $glob_performances_data['global-bg-video-lazy-load'] );
    }

    // CACHE THE OPTION
    Nimble_Manager()->video_bg_lazyload_enabled = $is_video_bg_lazyload_enabled;

    return Nimble_Manager()->video_bg_lazyload_enabled;
}



// /* ------------------------------------------------------------------------- *
// *  Adaptation of wp_get_attachment_image() for preprocessing lazy loading carousel images
//  added in dec 2019 for https://github.com/presscustomizr/nimble-builder/issues/570
//  used in tmpl/modules/img_slider_tmpl.php
// /* ------------------------------------------------------------------------- */
function sek_get_attachment_image_for_lazyloading_images_in_swiper_carousel( $attachment_id, $size = 'thumbnail', $is_first_img ) {
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

                if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
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
        $attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $size );

        // add swiper data-* stuffs for lazyloading now, after all filters
        // @see https://swiperjs.com/api/#lazy
        if ( !empty( $attr['srcset'] ) ) {
            $attr['data-srcset'] = $attr['srcset'];
            unset( $attr['srcset'] );
        }

        if ( !empty( $attr['src'] ) ) {
            $attr['data-src'] = $attr['src'];
            $attr['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            //unset( $attr['src'] );
        }
        if ( !empty( $attr['sizes'] ) ) {
            $attr['data-sek-img-sizes'] = $attr['sizes'];
            unset( $attr['sizes'] );
        }
        // when lazy load is active, we want to lazy load the first image of the slider if offscreen
        if ( $is_first_img && sek_is_img_smartload_enabled() ) {
            $attr['data-sek-src'] = $attr['src'];
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


// /* ------------------------------------------------------------------------- *
// *  IMPORT IMAGE IF NOT ALREADY IN MEDIA LIB
// /* ------------------------------------------------------------------------- */
// @return attachment id or WP_Error
// this method uses download_url()
// it first checks if the media already exists in the media library
function sek_sideload_img_and_return_attachment_id( $img_url ) {
    // Set variables for storage, fix file filename for query strings.
    preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $img_url, $matches );
    $filename = basename( $matches[0] );
    // prefix with nimble_asset_ if not done yet
    // for example, when importing a file, the img might already have the nimble_asset_ prefix if it's been uploaded by Nimble
    if ( 'nimble_asset_' !== substr($filename, 0, strlen('nimble_asset_') ) ) {
        $filename = 'nimble_asset_' . $filename;
    }

    // remove the extension
    $img_title = preg_replace( '/\.[^.]+$/', '', trim( $filename ) );

    //sek_error_log( __FUNCTION__ . ' ALORS img_title?', preg_replace( '/\.[^.]+$/', '', trim( $img_title ) ) );

    // Make sure this img has not already been uploaded
    // Meta query on the alt property, better than the title
    // because of https://github.com/presscustomizr/nimble-builder/issues/435
    $args = array(
        'posts_per_page' => 1,
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        //'name' => $img_title,
        'meta_query' => array(
          array(
            'key'     => '_wp_attachment_image_alt',
            'value'   => $img_title,
            'compare' => '='
          ),
        ),
    );
    $get_attachment = new \WP_Query( $args );

    //error_log( print_r( $get_attachment->posts, true ) );
    if ( is_array( $get_attachment->posts ) && array_key_exists(0, $get_attachment->posts) ) {
        //wp_send_json_error( __CLASS__ . '::' . __CLASS__ . '::' . __FUNCTION__ . ' => file already uploaded : ' . $relative_path );
        $img_id_already_uploaded = $get_attachment->posts[0]->ID;
    }
    // stop now and return the id if the attachment was already uploaded
    if ( isset($img_id_already_uploaded) ) {
        //sek_error_log( __FUNCTION__ . ' ALREADY UPLOADED ?', $img_id_already_uploaded );
        return $img_id_already_uploaded;
    }

    // Insert the media
    // Prepare the file_array that we will pass to media_handle_sideload()
    $file_array = array();
    $file_array['name'] = $filename;

    // Download file to temp location.
    $file_array['tmp_name'] = download_url( $img_url );

    // If error storing temporarily, return the error.
    if ( is_wp_error( $file_array['tmp_name'] ) ) {
        return $file_array['tmp_name'];
    }

    // Do the validation and storage stuff.
    $id = media_handle_sideload( $file_array, 0 );

    // If error storing permanently, unlink.
    if ( is_wp_error( $id ) ) {
        @unlink( $file_array['tmp_name'] );
    } else {
        // Store the title as image alt property
        // so we can identify it uniquely next time when checking if already uploaded
        // of course, if the alt property has been manually modified meanwhile, the image will be loaded again
        // fixes https://github.com/presscustomizr/nimble-builder/issues/435
        add_post_meta( $id, '_wp_attachment_image_alt', $img_title, true );
    }

    return $id;
}

?>