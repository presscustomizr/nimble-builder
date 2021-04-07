<?php


/* ------------------------------------------------------------------------- *
 *  Dynamic variables parsing
/* ------------------------------------------------------------------------- */
function sek_find_pattern_match($matches) {
    $replace_values = apply_filters( 'sek_template_tags', array(
      'home_url' => 'home_url',
      'year_now' => date("Y"),
      'site_title' => 'get_bloginfo',
      'the_title' => 'sek_get_the_title',
      'the_archive_title' => 'sek_get_the_archive_title',
      'the_content' => 'sek_get_the_content',
      'the_tags' => 'sek_get_the_tags',
      'the_categories' => 'sek_get_the_categories',
      'the_author' => 'sek_get_the_author',
      'the_published_date' => 'sek_get_the_published_date',
      'the_modified_date' => 'sek_get_the_modified_date'
    ));

    //sek_error_log('$matches ??', $matches );
    if ( !is_array($matches) || empty($matches[1]) )
      return;

    //$data = html_entity_decode($matches[1], ENT_QUOTES, get_bloginfo( 'charset' ) );
    $data = explode(' ', $matches[1] );

    // Filter so that {{the_categories sep="/"}} becomes array('the_categories', 'sep="/"' ) with no empty entries
    // => the first entry is the template tag name, the other entries are the callback arguments ( to implement April 2021 )
    $new_data = array_filter($data, function($value) {
        if ( !is_string($value) )
          return false;
      $value = ltrim($value);
      return !is_null($value) && !empty($value) && preg_match("/[a-z]/i", $value) ;
    });

    if ( isset($new_data[0]) && array_key_exists( $new_data[0], $replace_values ) ) {
      // @todo => authorize arguments passed as an array
      $dyn_content = $replace_values[$new_data[0]];
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
    //return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(\w+)\s?\}\}!', '\Nimble\sek_find_pattern_match', $val) : $val;
    return is_string( $val ) ? preg_replace_callback( '!\{\{\s?(.*?)\s?\}\}!', '\Nimble\sek_find_pattern_match', $val) : $val;
}
add_filter( 'nimble_parse_template_tags', '\Nimble\sek_parse_template_tags' );




// CALLBACKS
function sek_get_the_archive_title() {
  if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_archive_title' );
  } else {
    add_filter('get_the_archive_title_prefix', '__return_false');
    $title = get_the_archive_title();
    remove_filter('get_the_archive_title_prefix', '__return_false');
  }
  return $title;
}




function sek_get_the_published_date() {
  $post_id = sek_get_post_id_on_front_and_when_customizing();
  $published_date = get_the_date( get_option('date_format'), $post_id);
  $machine_readable_published_date = esc_attr( get_the_date( 'c' , $post_id ) );
  return sprintf( '<time class="sek-published-date" datetime="%1$s">%2$s</time>',
    $machine_readable_published_date,
    $published_date
  );
}

function sek_get_the_modified_date() {
  $post_id = sek_get_post_id_on_front_and_when_customizing();
  $modified_date = get_the_modified_date( get_option('date_format'), $post_id );
  $machine_readable_modified_date = esc_attr( get_the_modified_date( 'c' ), $post_id );
  return sprintf( '<time class="sek-modified-date" datetime="%1$s">%2$s</time>',
    $machine_readable_modified_date ,
    $modified_date
  );
}

function sek_get_the_tags( $separator = ' &middot; ') {
  return sprintf( '<span class="sek-post-tags">%1$s</span>', get_the_tag_list( $before = '', $sep = $separator, $after = '', $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}


function sek_get_the_categories( $separator = ' / ') {
    return sprintf( '<span class="sek-post-category">%1$s</span>', get_the_category_list( $separator, '', $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}

function sek_get_the_author() {
  $is_singular = is_singular();
  if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular )
    return null;
  

  $post_id = sek_get_post_id_on_front_and_when_customizing();
  $post_object = get_post( $post_id );
  if ( empty( $post_object ) || !is_object( $post_object ) )
    return null;
  $author_id = $post_object->post_author;
  $display_name = get_the_author_meta( 'display_name', $author_id );
  return sprintf(
    '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
    esc_url( get_author_posts_url( $author_id, get_the_author_meta( 'user_nicename', $author_id ) ) ),
    /* translators: %s: Author's display name. */
    esc_attr( sprintf( __( 'Posts by %s' ), $display_name ) ),
    $display_name
  );
}

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
  return null;
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