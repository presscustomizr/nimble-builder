<?php


/* ------------------------------------------------------------------------- *
 *  Dynamic variables parsing
/* ------------------------------------------------------------------------- */
function sek_find_pattern_match($matches) {
    $replace_values = apply_filters( 'sek_template_tags', array(
      'home_url' => 'home_url',
      'the_title' => 'sek_get_the_title',
      'the_content' => 'sek_get_the_content'
    ));

    if ( array_key_exists( $matches[1], $replace_values ) ) {
      $dyn_content = $replace_values[$matches[1]];
      $fn_name = $dyn_content;// <= typically not namespaced if WP core function, or function added with a filter from a child theme for example
      $namespaced_fn_name = __NAMESPACE__ . '\\' . $dyn_content; // <= namespaced if Nimble Builder function, introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
      if ( function_exists( $namespaced_fn_name ) ) {
        return $namespaced_fn_name();//<= @TODO use call_user_func() here + handle the case when the callback is a method
      } else if ( function_exists( $fn_name ) ) {
        return $fn_name();//<= @TODO use call_user_func() here + handle the case when the callback is a method
      } else if ( is_string($dyn_content) ) {
        return $dyn_content;
      } else {
        return null;
      }
    }
    return null;
}
// fired @filter 'nimble_parse_template_tags'
function sek_parse_template_tags( $val ) {
    //the pattern could also be '!\{\{(\w+)\}\}!', but adding \s? allows us to allow spaces around the term inside curly braces
    //see https://stackoverflow.com/questions/959017/php-regex-templating-find-all-occurrences-of-var#comment71815465_959026
    return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(\w+)\s?\}\}!', '\Nimble\sek_find_pattern_match', $val) : $val;
}
add_filter( 'nimble_parse_template_tags', '\Nimble\sek_parse_template_tags' );

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_title() {
  return get_the_title( sek_get_post_id_on_front_and_when_customizing() );
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_content() {
  if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      $post_id = sek_get_posted_query_param_when_customizing( 'post_id' );
      $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
      if ( $is_singular && is_int($post_id) ) {
          $post_object = get_post( $post_id );
          return !empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
      }
  } else {
      if( is_singular() ) {
        $post_object = get_post();
        return !empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
      }
  }
}

// @return the post id in all cases
// when performing ajax action, we need the posted query params made available from the ajax params
function sek_get_post_id_on_front_and_when_customizing() {
    if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        $post_id = sek_get_posted_query_param_when_customizing( 'post_id' );
    } else {
        $post_id = get_the_ID();
    }
    return is_int($post_id) ? $post_id : null;
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
// Possible params as of October 2019
// @see inc/czr-skope/_dev/1_1_0_skop_customizer_preview_load_assets.php::
// 'is_singular' => $wp_query->is_singular,
// 'post_id' => get_the_ID()
function sek_get_posted_query_param_when_customizing( $param ) {
  if ( isset( $_POST['czr_query_params'] ) ) {
      $query_params = json_decode( wp_unslash( $_POST['czr_query_params'] ), true );
      if ( array_key_exists( $param, $query_params ) ) {
          return $query_params[$param];
      } else {
          sek_error_log( __FUNCTION__ . ' => invalid param requested');
          return null;
      }
  }
  return null;
}

?>