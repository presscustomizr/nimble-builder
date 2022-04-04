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
      'the_archive_title' => 'sek_get_the_archive_title',// works for authors, CPT, taxonomies
      'the_archive_description' => 'sek_get_the_archive_description',// works for authors, CPT, taxonomies
      'the_content' => 'sek_get_the_content',
      'the_tags' => 'sek_get_the_tags',
      'the_categories' => 'sek_get_the_categories',

      'the_author_link' => 'sek_get_the_author_link',
      'the_author_name' => 'sek_get_the_author_name',
      'the_author_avatar' => 'sek_get_the_author_avatar',
      'the_author_bio' => 'sek_get_the_author_bio',

      'the_published_date' => 'sek_get_the_published_date',
      'the_modified_date' => 'sek_get_the_modified_date',
      'the_comments' => 'sek_get_the_comments',
      'the_previous_post_link' => 'sek_get_previous_post_link',
      'the_next_post_link' => 'sek_get_next_post_link',
      'the_comment_number' => 'sek_get_the_comment_number',

      'the_search_query' => 'sek_get_search_query',
      'the_search_results_number' => 'sek_get_search_results_nb'
    ));

    // Are we good after the filter ?
    if ( !is_array($replace_values) )
      return;

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




// CALLBACKS WHEN IS_ARCHIVE()
function sek_get_the_archive_title() {
  $is_archive = is_archive();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_archive = sek_get_posted_query_param_when_customizing( 'is_archive' );
  }
  if ( !$is_archive ) {
    return sek_get_tmpl_tag_error( $tag = 'the_archive_title', $msg = __('It can be used in archive pages only.', 'text_doma') );
  }

  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_archive_title' );
  } else {
    add_filter('get_the_archive_title_prefix', '__return_false');
    $title = get_the_archive_title();
    remove_filter('get_the_archive_title_prefix', '__return_false');
  }
  return $title;
}

function sek_get_the_archive_description() {
  $is_archive = is_archive();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_archive = sek_get_posted_query_param_when_customizing( 'is_archive' );
  }
  if ( !$is_archive ) {
    return sek_get_tmpl_tag_error( $tag = 'the_archive_description', $msg = __('It can be used in archive pages only.', 'text_doma') );
  }

  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_archive_description' );
  } else {
    $title = get_the_archive_description();
  }
  return $title;
}


// CALLBACKS WHEN IS_SINGULAR()
function sek_get_next_post_link() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_next_post_link', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_next_post_link' );
  } else {
    $title = get_next_post_link( $format = '%link' );
  }
  if ( empty( $title ) ) {
    return '';
  } else {
    return sprintf( '<span class="sek-next-post-link">%1$s</span>', $title );
  }
}

function sek_get_previous_post_link() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_previous_post_link', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $title = sek_get_posted_query_param_when_customizing( 'the_previous_post_link' );
  } else {
    $title = get_previous_post_link( $format = '%link' );
  }
  if ( empty( $title ) ) {
    return '';
  } else {
    return sprintf( '<span class="sek-previous-post-link">%1$s</span>', $title );
  }
}

function sek_get_the_comments() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_comments', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    return sprintf('<div class="nimble-notice-in-preview"><i class="fas fa-info-circle"></i>&nbsp;%1$s</div>',
      __('Comment template can not be refreshed while customizing', 'text_doma')
    );
  }

  ob_start();
  //load_template( $tmpl_path, false );
  if ( comments_open() || get_comments_number() ) {
    add_filter('comments_template', '\Nimble\sek_set_nb_comments_template_path');
    comments_template();
    remove_filter('comments_template', '\Nimble\sek_set_nb_comments_template_path');
  }
  return ob_get_clean();
}

//@filter 'comments_template'
function sek_set_nb_comments_template_path( $original_path ) {
  //@to do => make this path overridable
  $nb_path = sek_get_templates_dir() . "/wp/comments-template.php";
  if ( file_exists( $nb_path ) ) {
    return $nb_path;
  }
  return $original_path;
}

function sek_get_the_published_date() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_published_date', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  $post_id = sek_get_post_id_on_front_and_when_customizing();
  $published_date = get_the_date( get_option('date_format'), $post_id);
  $machine_readable_published_date = esc_attr( get_the_date( 'c' , $post_id ) );
  return sprintf( '<time class="sek-published-date" datetime="%1$s">%2$s</time>',
    $machine_readable_published_date,
    $published_date
  );
}

function sek_get_the_modified_date() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_modified_date', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  $post_id = sek_get_post_id_on_front_and_when_customizing();
  $modified_date = get_the_modified_date( get_option('date_format'), $post_id );
  $machine_readable_modified_date = esc_attr( get_the_modified_date( 'c' ), $post_id );
  return sprintf( '<time class="sek-modified-date" datetime="%1$s">%2$s</time>',
    $machine_readable_modified_date ,
    $modified_date
  );
}

function sek_get_the_tags( $separator = ' &middot; ') {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_tags', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return sprintf( '<span class="sek-post-tags">%1$s</span>', get_the_tag_list( $before = '', $sep = $separator, $after = '', $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}


function sek_get_the_categories( $separator = ' / ') {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_categories', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return sprintf( '<span class="sek-post-category">%1$s</span>', get_the_category_list( $separator, '', $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}

function sek_get_the_comment_number() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_comment_number', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return sprintf( '<span class="sek-post-comment-number">%1$s</span>', get_comments_number_text( $zero = false, $one = false, $more = false, $post_id = sek_get_post_id_on_front_and_when_customizing() ) );
}


// AUTHOR DATA
// 2 CASES : singular or author archives
function sek_get_the_author_link() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    $display_name = get_the_author_meta( 'display_name', $author_id );
    return sprintf(
      '<a href="%1$s" title="%2$s" class="sek-author-link" rel="author">%3$s</a>',
      esc_url( get_author_posts_url( $author_id, get_the_author_meta( 'user_nicename', $author_id ) ) ),
      /* translators: %s: Author's display name. */
      esc_attr( sprintf( __( 'Posts by %s' ), $display_name ) ),
      $display_name
    );
  }
  return null;
}

function sek_get_the_author_name() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    return sprintf( '<span class="sek-author-name">%1$s</span>', get_the_author_meta( 'display_name', $author_id ) );
  }
  return null;
}

function sek_get_the_author_avatar() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    return get_avatar( get_the_author_meta( 'ID', $author_id ), '85' );
  }
  return null;
}

function sek_get_the_author_bio() {
  $author_id = sek_get_author_id_on_front_and_when_customizing();
  if ( $author_id ) {
    return sprintf( '<span class="sek-author-description">%1$s</span>', get_the_author_meta( 'description', $author_id ) );
  }
  return null;
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_title() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_title', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  return get_the_title( sek_get_post_id_on_front_and_when_customizing() );
}

// introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
function sek_get_the_content() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( !$is_singular ) {
    return sek_get_tmpl_tag_error( $tag = 'the_content', $msg = __('It can only be used in single pages or single posts.', 'text_doma') );
  }
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
      $post_id = (int)sek_get_posted_query_param_when_customizing( 'post_id' );
      if ( is_int($post_id) ) {
          $post_object = get_post( $post_id );
          return !empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
      }
  } else {
      $post_object = get_post();
      return !empty( $post_object ) ? apply_filters( 'the_content', $post_object->post_content ) : null;
  }
  return null;
}

// CALLBACKS WHEN IS_SEARCH()
function sek_get_search_query() {
  $is_search = is_search();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_search = sek_get_posted_query_param_when_customizing( 'is_search' );
    $search_query = sek_get_posted_query_param_when_customizing( 'the_search_query' );
  } else {
    $search_query = get_search_query();
  }
  if ( !$is_search ) {
    return sek_get_tmpl_tag_error( $tag = 'the_search_query', $msg = __('It can only be used in search results page.', 'text_doma') );
  }
  return sprintf( '<span class="sek-search-query">%1$s</span>', esc_html( $search_query ) );
}

function sek_get_search_results_nb() {
  $is_search = is_search();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_search = sek_get_posted_query_param_when_customizing( 'is_search' );
    $search_res_nb = sek_get_posted_query_param_when_customizing( 'the_search_results_nb' );
  } else {
    global $wp_query;
    $search_res_nb = (int)$wp_query->found_posts;
  }
  if ( !$is_search ) {
    return sek_get_tmpl_tag_error( $tag = 'the_search_results_number', $msg = __('It can only be used in search results page.', 'text_doma') );
  }
  return sprintf( '<span class="sek-search-results-number">%1$s</span>', esc_html( $search_res_nb ) );
}

//////////////////////////////////////////////////
///// HELPERS
/////////////////////////////////////////////////
function sek_get_author_id_on_front_and_when_customizing() {
  $is_singular = is_singular();
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
    $is_singular = sek_get_posted_query_param_when_customizing( 'is_singular' );
  }
  if ( $is_singular ) {
    $post_id = sek_get_post_id_on_front_and_when_customizing();
    $post_object = get_post( $post_id );
    if ( empty( $post_object ) || !is_object( $post_object ) ) {
      $author_id = null;
    }
    $author_id = $post_object->post_author;
  } else {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
      $author_id = sek_get_posted_query_param_when_customizing( 'the_author_id' );
    } else {
      global $authordata;
      $author_id = isset( $authordata->ID ) ? $authordata->ID : 0;
    }
  }
  return $author_id;
}

// @return the post id in all cases
// when performing ajax action, we need the posted query params made available from the ajax params
function sek_get_post_id_on_front_and_when_customizing() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
        $post_id = (int)sek_get_posted_query_param_when_customizing( 'post_id' );
    } else {
        $post_id = get_the_ID();
    }
    return is_int($post_id) ? $post_id : null;
}

// recursively sanitize an array of posted ($_POST) query_params to be used when customzing
// @param params (array)
function sek_sanitize_query_params_array( $params = array()) {
  foreach ($params as $prm => $val) {
    if ( is_array($val) ) {
      if ( empty($val) ) {
        $sanitized_query_params[$prm] = [];
      } else {
        $sanitized_query_params[$prm] = sek_sanitize_query_params_array($params);
      }
    } else {
      $sanitized_query_params[$prm] = sanitize_text_field($val);
    }
  }
  return $sanitized_query_params;
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
          if ( !is_array($query_params[$param]) ) {
            return sanitize_text_field($query_params[$param]);
          } else {
            return sek_sanitize_query_params_array($query_params[$param]);
          }
      } else {
          sek_error_log( __FUNCTION__ . ' => invalid param requested');
          return null;
      }
  }
  return null;
}

function sek_get_tmpl_tag_error( $tag, $msg ) {
  if ( !skp_is_customizing() )
    return;
  return sprintf('<div class="nimble-notice-in-preview nimble-inline-notice-in-preview"><i class="fas fa-info-circle"></i> %1$s %2$s</div>',
    '{{' . $tag . '}} ' . __('could not be printed.', 'text_doma'),
    $msg
  );
}

?>