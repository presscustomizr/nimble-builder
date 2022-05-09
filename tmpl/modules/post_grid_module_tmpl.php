<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$main_settings = $value['grid_main'];
$metas_settings = $value['grid_metas'];
$thumb_settings = $value['grid_thumb'];

// filter for 'get_pagenum_link' and 'paginate_links'
// for https://github.com/presscustomizr/nimble-builder/issues/672
// June 2020 updated for https://github.com/presscustomizr/nimble-builder/issues/716
if ( !function_exists( 'Nimble\sek_filter_pagination_nav_url') ) {
    function sek_filter_pagination_nav_url( $result ) {
          $url = add_query_arg(
            array('nb_grid_module_go_to' => Nimble_Manager()->model['id'] ),
            $result
          );
          return $url;
    }
}

/**
 * The template for displaying the pagination links
 */
if ( !function_exists( 'Nimble\sek_render_post_navigation') ) {
  function sek_render_post_navigation( $post_query ) {
    $next_dir          = is_rtl() ? 'right' : 'left';
    $prev_dir          = is_rtl() ? 'left' : 'right';
    $tnext_align_class = "sek-text-{$next_dir}";
    $tprev_align_class = "sek-text-{$prev_dir}";
    $_older_label      = __( 'Older' , 'text_doma' );
    $_newer_label      = __( 'Newer' , 'text_doma' );

    // filter to add nimble module id ( ex : #__nimble__b4b942df40e5 ) at the end of the url so we focus on grid when navigating pagination
    add_filter('get_pagenum_link', 'Nimble\sek_filter_pagination_nav_url' );
    /* Generate links */
    $prev_link = get_next_posts_link(
      '<span class="sek-meta-nav"><span class="sek-meta-nav-title">' . $_older_label . '</span></span>', //label
      $post_query->max_num_pages //max pages
    );

    $next_link  = get_previous_posts_link(
      '<span class="sek-meta-nav"><span class="sek-meta-nav-title">' . $_newer_label . '</span></span>', //label
        $post_query->max_num_pages //max pages
    );
    remove_filter('get_pagenum_link', 'Nimble\sek_filter_pagination_nav_url' );

    /* If no links are present do not display this */
    if ( null != $prev_link || null != $next_link ) :

    ?>
    <div class="sek-row sek-post-navigation">
      <nav id="sek-nav-below" class="sek-col-100">
        <h2 class="sek-screen-reader-text"><?php _e('Posts navigation', 'text_doma') ?></h2>
        <ul class="sek-czr-pager sek-row">
          <li class="sek-next-posts sek-col-base sek-col-33 <?php echo esc_attr($tnext_align_class); ?> ">
          <?php if ( null != $next_link ) : ?>
            <span class="sek-screen-reader-text"><?php echo wp_kses_post($_newer_label); ?></span>
            <span class="sek-nav-next sek-nav-dir"><?php echo wp_kses_post($next_link); ?></span>
          <?php endif ?>
          </li>
          <li class="sek-pagination sek-col-base sek-col-33">
            <ul class="sek-pag-list">
            <?php
              // April 2020 : fixes pagination not working on a static page used as front page
              // see https://github.com/presscustomizr/nimble-builder/issues/664
              // https://developer.wordpress.org/reference/classes/wp_query/#pagination-parameters
              $pagination_query_var = Nimble_Manager()->is_viewing_static_front_page ? 'page' :'paged';
              $paged = get_query_var($pagination_query_var);
              $paged = $paged ? $paged : 1;
              $model = Nimble_Manager()->model;
              $is_nimble_pagination = isset($_GET['nb_grid_module_go_to']);
              $is_current_grid_paginated = $is_nimble_pagination && $model['id'] === sanitize_text_field($_GET['nb_grid_module_go_to']);
              // When user clicked on a pagination link, NB adds query params to the url ( removed via js once the page is loaded )
              // in this case, if there are several grids printed on the page we want to paginate only the paginated one
              // otherwise, if the pagination is accessed directly, or if the page is refreshed, all grids should be paginated according to the get_query_var($pagination_query_var) param
              if ( $is_nimble_pagination ) {
                $paged = $is_current_grid_paginated ? $paged : 1;
              }

              // filter to add nimble module id ( ex : #__nimble__b4b942df40e5 ) at the end of the url so we focus on grid when navigating pagination
              add_filter('paginate_links', 'Nimble\sek_filter_pagination_nav_url' );
              $_paginate_links = paginate_links( array(
                  'prev_next' => false,
                  'mid_size'  => 1,
                  'type'      => 'array',
                  'current'    => max( 1, $paged ),
                  'total'      => $post_query->max_num_pages
              ));
              remove_filter('paginate_links', 'Nimble\sek_filter_pagination_nav_url' );

              if ( is_array( $_paginate_links ) ) {
                foreach ( $_paginate_links as $_page ) {
                    echo wp_kses_post("<li class='sek-paginat-item'>$_page</li>");
                }
              }
            ?>
            </ul>
          </li>
          <li class="sek-previous-posts sek-col-base sek-col-33 <?php echo esc_attr($tprev_align_class); ?>">
          <?php if ( null != $prev_link ) : ?>
            <span class="sek-screen-reader-text"><?php echo wp_kses_post($_older_label); ?></span>
            <span class="sek-nav-previous sek-nav-dir"><?php echo wp_kses_post($prev_link); ?></span>
          <?php endif; ?>
          </li>
      </ul>
      </nav>
    </div>
    <?php endif;
  }
}//sek_render_post_navigation



if ( !function_exists( 'Nimble\sek_render_post') ) {
  function sek_render_post( $main_settings, $metas_settings, $thumb_settings ) {
    global $post;

    if ( is_object($post) && isset($post->ID) ) {
        $post_id = $post->ID;
    } else {
        $post_id = $post;
    }
    // thumb, title, excerpt visibility
    foreach ( ['thumb'] as $element ) {
        ${'show_' . $element} = sek_booleanize_checkbox_val( $thumb_settings["show_{$element}"] );
    }
    foreach ( ['title', 'excerpt'] as $element ) {
        ${'show_' . $element} = sek_booleanize_checkbox_val( $main_settings["show_{$element}"] );
    }
    // meta visibility
    foreach ( ['cats', 'comments', 'author', 'date'] as $meta) {
        ${'show_' . $meta} = sek_booleanize_checkbox_val( $metas_settings["show_{$meta}"] );
    }
    $has_post_thumbnail = has_post_thumbnail();
    $use_post_thumb_placeholder = true === sek_booleanize_checkbox_val( $thumb_settings['use_post_thumb_placeholder'] );
    $post_thumb_class = ( $show_thumb && ( $has_post_thumbnail || $use_post_thumb_placeholder ) ) ? 'sek-has-thumb' : '';
    $post_classes = array( $post_thumb_class );// get_post_class( $post_thumb_class, $post_id );
    // note : not using WP generated post classes to avoid collision with theme styles often using those CSS classes to style posts
    ?>
      <article id="sek-pg-<?php the_ID(); ?>" class="<?php echo esc_attr( implode( ' ', $post_classes ) ); ?>">
        <?php if ( $show_thumb && ( $has_post_thumbnail || $use_post_thumb_placeholder ) ) : ?>
          <figure class="sek-pg-thumbnail">
            <?php // when title is not displayed, print it as an attribute of the image ?>
            <?php if ( $show_title ) : ?>
              <a href="<?php the_permalink(); ?>">
            <?php else : ?>
              <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php endif; ?>
              <?php
                  if( $has_post_thumbnail ) {
                      // Nov 2020 : removes any additional styles added by a theme ( Twenty Twenty one ) or a plugin to the image
                      add_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );
                      $img_html = wp_get_attachment_image( get_post_thumbnail_id(), empty( $thumb_settings['img_size'] ) ? 'medium' : $thumb_settings['img_size']);
                      remove_filter( 'wp_get_attachment_image_attributes', '\Nimble\sek_remove_image_style_attr', 999 );
                      
                      if ( !skp_is_customizing() && false !== strpos($img_html, 'data-sek-src="http') ) {
                          $img_html = $img_html.Nimble_Manager()->css_loader_html;
                      }
                      if ( !skp_is_customizing() ) {
                        // april 2020 : when customizing, smart load randomly breaks when refreshing the grids
                        // so let's apply smart load only on front for now
                        echo apply_filters( 'nimble_parse_for_smart_load', wp_kses_post($img_html) );
                      } else {
                        echo wp_kses_post($img_html);
                      }
                  } else if ( $use_post_thumb_placeholder ) {
                      echo apply_filters( 'nimble_post_grid_module_default_featured_image', sprintf( '<img alt="default img" class="no-lazy" data-skip-lazyload="true" src="%1$s"/>', esc_url(NIMBLE_BASE_URL . '/assets/img/default-img.png' ) ) );
                  }
              ?>
            </a>
          </figure>
        <?php endif; ?>
        <?php if ( $show_cats || $show_title || $show_author || $show_date || $show_comments || $show_excerpt ) : ?>
          <div class="sek-pg-content">
            <?php if ( $show_cats ) : ?>
              <div class="sek-pg-category"><?php the_category(' / '); ?></div>
            <?php endif; ?>
            <?php if ( $show_title ) : ?>
              <h2 class="sek-pg-title">
                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php echo get_the_title($post_id); ?></a>
              </h2><!--/.pg-title-->
            <?php endif; ?>
            <?php if ( $show_author || $show_date || $show_comments ) : ?>
              <aside class="sek-pg-metas">
                <?php if ( $show_author ) : ?>
                  <span><?php the_author_posts_link(); ?></span>
                <?php endif; ?>
                <?php if ( $show_date ) : ?>
                  <span class="published updated"><?php echo get_the_date( get_option('date_format') ); ?></span>
                <?php endif; ?>
                <?php if ( $show_comments ) : ?>
                  <span><?php comments_number( __('0 comments', 'text_doma'), __('1 comment', 'text_doma'), __('% comments', 'text_doma') ); ?></span>
                <?php endif; ?>
              </aside><!--/.pg-meta-->
            <?php endif; ?>
            <?php if ( $show_excerpt ) : ?>
              <div class="sek-excerpt">
                <?php
                  // note : using add_filter( 'excerpt_length' ) do not work when using a custom excerpt
                  // code inspired from WP core formatting.php
                ?>
                <?php echo apply_filters( 'the_excerpt', wp_trim_words( get_the_excerpt(), sek_pg_get_excerpt_length( 55 ), ' ' . '[&hellip;]' ) ); ?>
              </div>
            <?php endif; ?>
          </div><?php //.sek-pg-content ?>
        <?php endif; ?>
      </article><!--/#sek-pg-->
    <?php
  }
}






// // Solves the problem of setting both the maximum number of posts and the posts_per_page in a custom WP_Query
// // Filter is added and removed before and after the query call
// if ( !function_exists( 'Nimble\sek_filter_found_posts') ) {
//   function sek_filter_found_posts() {
//     $model = Nimble_Manager()->model;
//     $value = array_key_exists( 'value', $model ) ? $model['value'] : array();
//     $main_settings = $value['grid_main'];
//     $post_nb = (int)$main_settings['post_number'];
//     $post_nb = $post_nb < 0 ? 0 : $post_nb;
//     return $post_nb;
//   }
// }


// filters @hook 'excerpt_length'
if ( !function_exists( 'Nimble\sek_pg_get_excerpt_length') ) {
  function sek_pg_get_excerpt_length( $original_length ) {
    $model = Nimble_Manager()->model;
    $value = array_key_exists( 'value', $model ) ? $model['value'] : array();
    $main_settings = $value['grid_main'];
    $_custom = (int)$main_settings['excerpt_length'];
    $_custom = $_custom < 1 ? 1 : $_custom;

    return !is_numeric($_custom) ? $original_length : $_custom;
  }
}

$user_selected_cat_slugs = $main_settings['categories'];
$must_have_all_cats = sek_booleanize_checkbox_val( $main_settings['must_have_all_cats'] );

// October 2019
// To solve the problem of children cats being displayed when picking the parent, we introduced 'category__in' Query param
// But we need to keep the "category_name" param when user checks "must have all cats" option because 'category__in' does not allow to do a + statement
// @see https://developer.wordpress.org/reference/classes/wp_query/#category-parameters
$category_names = null;
$categories_in = null;

$categories_id_raw = [];
foreach ($user_selected_cat_slugs as $cat_slug ) {
    $cat_object = get_category_by_slug( $cat_slug );
    if ( is_object($cat_object) ) {
      $categories_id_raw[] = $cat_object->term_id;
    }
}

if ( is_array( $user_selected_cat_slugs ) ) {
  if ( count( $user_selected_cat_slugs ) > 1 ) {
    if ( $must_have_all_cats ) {
      // https://codex.wordpress.org/Class_Reference/WP_Query#Category_Parameters
      $category_names = implode( '+', $user_selected_cat_slugs );
    } else {
      $categories_in = $categories_id_raw;
    }
  } else {
    // https://codex.wordpress.org/Class_Reference/WP_Query#Category_Parameters
    $categories_in = $categories_id_raw;
  }
}//is_array( $main_settings['categories'] )



$order = 'DESC';
$orderby = 'title';

if ( !empty( $main_settings['order_by'] ) && is_string( $main_settings['order_by'] ) ) {
    $order_params = explode('_', $main_settings['order_by'] );
    // 'date_desc' will give orderby = date and order = DESC
    if ( is_array( $order_params ) && 2 === count($order_params) ) {
        $order = strtoupper( $order_params[1] );
        $orderby = $order_params[0];
    }
}

$post_nb = (int)$main_settings['post_number'];
$post_nb = $post_nb < 0 ? 0 : $post_nb;
$query_params = $default_query_params = [
  'no_found_rows'          => false,
  'update_post_meta_cache' => false,
  'update_post_term_cache' => false,
  'ignore_sticky_posts'    => 1,
  'post_status'            => 'publish',// because otherwise 'draft' posts are showing up. (wp bug) fixes https://github.com/presscustomizr/nimble-builder/issues/466
  'posts_per_page'         => $post_nb,
  //@see https://codex.wordpress.org/Class_Reference/WP_Query#Category_Parameters
  'category_name'          => $category_names,
  'category__in'           => $categories_in,
  'order'                  => $order,
  'orderby'                => $orderby,
  // may 2020 : querying ids is enough and much more performant on large queries
  // @see : https://pluginrepublic.com/how-to-handle-large-queries-in-wordpress/
  'fields'                 => 'ids'
];


if ( !function_exists( 'Nimble\sek_maybe_add_sticky_posts_to_query') ) {
  // inspired from WP core way to add sticky posts. @see class WP_Query
  function sek_maybe_add_sticky_posts_to_query( $query, $query_params, $paged ) {
    // Put sticky posts at the top of the posts array.
    $sticky_posts = get_option( 'sticky_posts' );
    if ( $paged <= 1 && is_array( $sticky_posts ) && ! empty( $sticky_posts ) && ! $query_params['ignore_sticky_posts'] ) {
      $num_posts     = count( $query->posts );
      $sticky_offset = 0;
      // Loop over posts and relocate stickies to the front.
      for ( $i = 0; $i < $num_posts; $i++ ) {
        if ( in_array( $query->posts[ $i ], $sticky_posts, true ) ) {
          $sticky_post = $query->posts[ $i ];
          // Remove sticky from current position.
          array_splice( $query->posts, $i, 1 );
          // Move to front, after other stickies.
          array_splice( $query->posts, $sticky_offset, 0, array( $sticky_post ) );
          // Increment the sticky offset. The next sticky will be placed at this offset.
          $sticky_offset++;
          // Remove post from sticky posts array.
          $offset = array_search( $sticky_post, $sticky_posts, true );
          unset( $sticky_posts[ $offset ] );
        }
      }

      // If any posts have been excluded specifically, Ignore those that are sticky.
      if ( ! empty( $sticky_posts ) && ! empty( $query_params['post__not_in'] ) ) {
        $sticky_posts = array_diff( $sticky_posts, $query_params['post__not_in'] );
      }

      $post_type = ( array_key_exists('post_type', $query_params ) && !empty($query_params['post_type'] ) ) ? $query_params['post_type'] : 'post';
      // Fetch sticky posts that weren't in the query results.
      if ( ! empty( $sticky_posts ) ) {
        $stickies = get_posts(
          array(
            'post__in'    => $sticky_posts,
            'post_type'   => $post_type,
            'post_status' => 'publish',
            'nopaging'    => true,
          )
        );

        foreach ( $stickies as $sticky_post ) {
          array_splice( $query->posts, $sticky_offset, 0, array( $sticky_post ) );
          $sticky_offset++;
        }
      }
    }
    return $query;
  }
}

$use_current_query = array_key_exists('use_current_query', $main_settings ) && sek_booleanize_checkbox_val($main_settings['use_current_query']);
$replace_current_query = true;// when user checks use_current_query, $replace_current_query is set to false

// Shall NB use current WP query ?
if ( $use_current_query ) {
  if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      $query_params = sek_get_posted_query_param_when_customizing('query_vars');
      $query_params['post_status'] = 'publish';// because otherwise 'draft' posts are showing up. (wp bug) fixes https://github.com/presscustomizr/nimble-builder/issues/466
  } else {
      global $wp_query;
      $query_params = $wp_query->query_vars;
  }
  // make sure we didn't lose the query params at this point.
  $query_params = is_array($query_params) ? $query_params : $default_query_params;

  // When using the current query, NB uses normally only WP query vars. Unless user checks 'replace_query' option.
  $replace_current_query = array_key_exists('replace_query', $main_settings ) && sek_booleanize_checkbox_val($main_settings['replace_query']);
  if ( $replace_current_query ) {
    $query_params['posts_per_page'] = $post_nb;
    $query_params['orderby'] = $orderby;
    $query_params['order'] = $order;
  }
}

$paged = 1;
$is_nimble_pagination_on = isset($_GET['nb_grid_module_go_to']);
$is_current_grid_paginated = isset($_GET['nb_grid_module_go_to']) && $model['id'] === sanitize_text_field($_GET['nb_grid_module_go_to']);
// may 2020 => is_front_page() was wrong to check if home was a static front page.
// fixes https://github.com/presscustomizr/nimble-builder/issues/664
Nimble_Manager()->is_viewing_static_front_page = is_front_page() && 'page' == get_option( 'show_on_front' );
if ( !$use_current_query && $replace_current_query && true === sek_booleanize_checkbox_val($main_settings['display_pagination']) ) {
  $posts_per_page = (int)$main_settings['posts_per_page'];
  $posts_per_page = $posts_per_page <= 0 ? 1 : $posts_per_page;
  // April 2020 : fixes pagination not working on a static page used as front page
  // see https://github.com/presscustomizr/nimble-builder/issues/664
  // https://developer.wordpress.org/reference/classes/wp_query/#pagination-parameters
  $pagination_query_var = Nimble_Manager()->is_viewing_static_front_page ? 'page' :'paged';
  $paged = get_query_var($pagination_query_var);
  if ( $is_nimble_pagination_on ) {
    $query_params['paged'] = ( $paged && $is_current_grid_paginated ) ? $paged : 1;
  } else {
    $query_params['paged'] = $paged;
  }
  $query_params['posts_per_page'] = $posts_per_page;
}

$post_query = null;
if ( $replace_current_query && $post_nb > 0 ) {
    $cache_key = 'nb_post_q_' . $model['id'] . '_paged_' . $paged;
    $cache_group = 'nb_post_queries';
    // Use cached data when not customizing
    $cached = false;
    if ( !skp_is_customizing() ) {
      $cached = wp_cache_get( $cache_key, $cache_group );
    }
    if ( false !== $cached ) {
        $post_query = $cached;
    } else {
        // Sticky posts
        $include_sticky = array_key_exists('include_sticky', $main_settings ) && sek_booleanize_checkbox_val($main_settings['include_sticky']);
        if ( $include_sticky ) {
          $query_params['ignore_sticky_posts'] = 0;
        }
        $query_params = apply_filters( 'nimble_post_grid_module_query_params', $query_params , Nimble_Manager()->model );
        if ( is_array( $query_params ) ) {
          //add_filter( 'found_posts', '\Nimble\sek_filter_found_posts', 10, 2 );
          // Query featured entries
          $post_query = new \WP_Query($query_params);
          if ( $include_sticky ) {
            $post_query = sek_maybe_add_sticky_posts_to_query( $post_query, $query_params, $paged );
          }
          //remove_filter( 'found_posts', '\Nimble\sek_filter_found_posts', 10, 2 );
        } else {
          sek_error_log('post_grid_module_tmpl => query params is invalid');
        }
        if ( !skp_is_customizing() ) {
          wp_cache_add( $cache_key, $post_query, $cache_group );
        }
    }
} else if ( !$replace_current_query ) {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
        $post_query = new \WP_Query($query_params);
    } else {
        global $wp_query;
        $post_query = $wp_query;
    }
}




// Copy of WP_Query::have_post(), without do_action_ref_array( 'loop_start', array( &$this ) );
// implemented to fix https://github.com/presscustomizr/nimble-builder/issues/467
if ( !function_exists( 'Nimble\sek_pg_the_nimble_have_post') ) {
  function sek_pg_the_nimble_have_post( $query ) {
    if ( $query->current_post + 1 < $query->post_count ) {
      return true;
    } elseif ( $query->current_post + 1 == $query->post_count && $query->post_count > 0 ) {
      /**
       * Fires once the loop has ended.
       *
       * @since 2.0.0
       *
       * @param WP_Query $this The WP_Query instance (passed by reference).
       */
      //do_action_ref_array( 'loop_end', array( &$this ) );
      // Do some cleaning up after the loop
      $query->rewind_posts();
    } elseif ( 0 === $query->post_count ) {
      /**
       * Fires if no results are found in a post query.
       *
       * @since 4.9.0
       *
       * @param WP_Query $this The WP_Query instance.
       */
      do_action( 'loop_no_results', $query );
    }

    $query->in_the_loop = false;
    return false;
  }
}


// Copy of WP_Query::the_post(), without do_action_ref_array( 'loop_start', array( &$this ) );
// implemented to fix https://github.com/presscustomizr/nimble-builder/issues/467
if ( !function_exists( 'Nimble\sek_pg_the_nimble_post') ) {
  function sek_pg_the_nimble_post( $query ) {
    global $post;
    $query->in_the_loop = true;
    $post = $query->next_post();
    $query->setup_postdata( $post );
  }
}


if ( is_object( $post_query ) && $post_query->have_posts() ) {
  $columns_by_device = $main_settings['columns'];
  $columns_by_device = is_array( $columns_by_device ) ? $columns_by_device : array();
  $columns_by_device = wp_parse_args( $columns_by_device, array(
      'desktop' => 2,
      'tablet' => '',
      'mobile' => ''
  ));
  $normalized_columns_by_device = array();
  // normalizes
  foreach ( $columns_by_device as $device => $column_nb ) {
      $column_nb = (int)$column_nb;
      if ( !empty( $column_nb ) ) {
        $column_nb = $column_nb > 12 ? 12 : $column_nb;
        $column_nb = $column_nb < 1 ? 1 : $column_nb;
      }
      $normalized_columns_by_device[$device] = $column_nb;
  }


  $layout_class = 'list' === $main_settings['layout'] ? 'sek-list-layout' : 'sek-grid-layout';

  $shadow_class = true === sek_booleanize_checkbox_val( $main_settings['apply_shadow_on_hover'] ) ? 'sek-shadow-on-hover' : '';

  $has_thumb_custom_height = true === sek_booleanize_checkbox_val( $thumb_settings['img_has_custom_height'] ) ? 'sek-thumb-custom-height' : 'sek-thumb-no-custom-height';

  $tablet_breakpoint_class = true === sek_booleanize_checkbox_val( $main_settings['has_tablet_breakpoint'] ) ? 'sek-has-tablet-breakpoint' : '';
  $mobile_breakpoint_class = true === sek_booleanize_checkbox_val( $main_settings['has_mobile_breakpoint'] ) ? 'sek-has-mobile-breakpoint' : '';

  $grid_wrapper_classes = [ $tablet_breakpoint_class, $mobile_breakpoint_class ];
  $grid_wrapper_classes = apply_filters('nb_grid_wrapper_classes', $grid_wrapper_classes, $value );
  $grid_wrapper_classes = implode(' ', $grid_wrapper_classes );

  $grid_items_classes = [ $layout_class, $has_thumb_custom_height, $shadow_class ];

  if ( 'grid' === $main_settings['layout'] ) {
    foreach ( $normalized_columns_by_device as $device => $column_nb ) {
      if ( empty( $column_nb ) )
        continue;
      $grid_items_classes[] = "sek-{$device}-col-{$column_nb}";
      if ( 'desktop' === $device ) {
        $grid_items_classes[] = "sek-all-col-{$column_nb}";
      }
    }
  }

  $grid_items_classes = implode(' ', $grid_items_classes );
  do_action( 'nb_before_post_grid_wrapper' );
  ?>
  <div class="sek-post-grid-wrapper <?php echo esc_attr($grid_wrapper_classes); ?>" id="<?php echo esc_attr($model['id']); ?>">
    <div class="sek-grid-items <?php echo esc_attr($grid_items_classes); ?>">
      <?php
        // $post_query->have_posts() fires 'loop_end', which we don't want
        while ( sek_pg_the_nimble_have_post( $post_query ) ) {
            sek_pg_the_nimble_post( $post_query );// implemented to fix https://github.com/presscustomizr/nimble-builder/issues/467 because when using core $post_query->the_post(), the action 'loop_start' is fired
            sek_render_post( $main_settings, $metas_settings, $thumb_settings );
        }//while
      ?>
    </div><?php //.sek-grid-item ?>

    <?php
    if ( true === sek_booleanize_checkbox_val($main_settings['display_pagination']) ) {
      sek_render_post_navigation( $post_query );
    }
    ?>
    <?php
      // After looping through a separate query, this function restores the $post global to the current post in the main query.
      wp_reset_postdata();
      //  This will remove obscure bugs that occur when the previous WP_Query object is not destroyed properly before another is set up.
      // $GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];
      wp_reset_query();
    ?>
  </div><?php //.sek-post-grid-wrapper ?>
  <?php
}//if ( $post_query->have_posts() )

else if ( skp_is_customizing() ) {
  ?>
  <div class="sek-module-placeholder sek-post-grid"><i class="material-icons">view_list</i></div>
  <?php
}
