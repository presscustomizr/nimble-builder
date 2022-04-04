<?php
/* ------------------------------------------------------------------------- *
 *  Page Menu for menu module
/* ------------------------------------------------------------------------- */
/**
 * Display or retrieve list of pages with optional home link.
 * Modified copy of wp_page_menu()
 * @return string html menu
 */
function sek_page_menu_fallback( $args = array() ) {
    $defaults = array('show_home' => true, 'sort_column' => 'menu_order, post_title', 'menu_class' => 'menu', 'echo' => true, 'link_before' => '', 'link_after' => '');
    $args = wp_parse_args( $args, $defaults );
    $args = apply_filters( 'wp_page_menu_args', $args );
    $menu = '';
    $list_args = $args;

    // Show Home in the menu
    if ( !empty($args['show_home']) ) {
        if ( true === $args['show_home'] || '1' === $args['show_home'] || 1 === $args['show_home'] ) {
            $text = __('Home' , 'text_domain_to_replace');
        } else {
            $text = $args['show_home'];
        }
        $class = '';
        if ( is_front_page() && !is_paged() ) {
            $class = 'class="current_page_item"';
        }
        $menu .= '<li ' . $class . '><a href="' . home_url( '/' ) . '">' . $args['link_before'] . $text . $args['link_after'] . '</a></li>';
        // If the front page is a page, add it to the exclude list
        if (get_option('show_on_front') == 'page') {
            if ( !empty( $list_args['exclude'] ) ) {
                $list_args['exclude'] .= ',';
            } else {
                $list_args['exclude'] = '';
            }
            $list_args['exclude'] .= get_option('page_on_front');
        }
    }

    $list_args['echo'] = false;
    $list_args['title_li'] = '';

    // limit the number of pages displayed, home excluded from the count if included
    $list_args['number'] = 4;

    $menu .= str_replace( array( "\r", "\n", "\t" ), '', sek_list_pages( $list_args ) );
     // if ( $menu )
    //   $menu = '<ul>' . $menu . '</ul>';
     //$menu = '<div class="' . esc_attr($args['menu_class']) . '">' . $menu . "</div>\n";
    if ( $menu ) {
        $menu = '<ul class="' . esc_attr( $args['menu_class'] ) . '">' . $menu . '</ul>';
    }

    //$menu = apply_filters( 'wp_page_menu', $menu, $args );
    if ( $args['echo'] )
      echo wp_kses_post($menu);
    else
      return $menu;
}
 /**
 * Retrieve or display list of pages in list (li) format.
 * Modified copy of wp_list_pages
 * @return string HTML list of pages.
 */
function sek_list_pages( $args = '' ) {
    $defaults = array(
        'depth' => 0,
        'show_date' => '',
        'date_format' => get_option( 'date_format' ),
        'child_of' => 0,
        'exclude' => '',
        'title_li' => __( 'Pages', 'text_domain_to_replace' ),
        'echo' => 1,
        'authors' => '',
        'sort_column' => 'menu_order, post_title',
        'link_before' => '',
        'link_after' => '',
        'walker' => ''
    );
    $r = wp_parse_args( $args, $defaults );
    $output = '';
    $current_page = 0;
     // sanitize, mostly to keep spaces out
    $r['exclude'] = preg_replace( '/[^0-9,]/', '', $r['exclude'] );
     // Allow plugins to filter an array of excluded pages (but don't put a nullstring into the array)
    $exclude_array = ( $r['exclude'] ) ? explode( ',', $r['exclude'] ) : array();
    $r['exclude'] = implode( ',', apply_filters( 'wp_list_pages_excludes', $exclude_array ) );
     // Query pages.
    $r['hierarchical'] = 0;
    $pages = get_pages( $r );
    if ( !empty( $pages ) ) {
      if ( $r['title_li'] ) {
        $output .= '<li class="pagenav">' . $r['title_li'] . '<ul>';
      }
      global $wp_query;
      if ( is_page() || is_attachment() || $wp_query->is_posts_page ) {
        $current_page = get_queried_object_id();
      } elseif ( is_singular() ) {
        $queried_object = get_queried_object();
        if ( is_post_type_hierarchical( $queried_object->post_type ) ) {
          $current_page = $queried_object->ID;
        }
      }
      $output .= sek_walk_page_tree( $pages, $r['depth'], $current_page, $r );
      if ( $r['title_li'] ) {
          $output .= '</ul></li>';
      }
    }
    $html = apply_filters( 'wp_list_pages', $output, $r );
    if ( $r['echo'] ) {
        echo wp_kses_post($html);
    } else {
        return $html;
    }
}


/**
 * Retrieve HTML list content for page list.
 *
 * @uses Walker_Page to create HTML list content.
 * @since 2.1.0
 * @see Walker_Page::walk() for parameters and return description.
*/
function sek_walk_page_tree( $pages, $depth, $current_page, $r ) {
  // if ( empty($r['walker']) )
  //   $walker = new Walker_Page;
  // else
  //   $walker = $r['walker'];
  $walker = new \Walker_Page;
  foreach ( (array) $pages as $page ) {
      if ( $page->post_parent ) {
          $r['pages_with_children'][ $page->post_parent ] = true;
      }
  }
  $args = array( $pages, $depth, $r, $current_page );
  return call_user_func_array(array($walker, 'walk'), $args);
}

function sek_get_user_created_menus() {
    // if ( !skp_is_customizing() )
    //   return array();
    $all_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
    $user_menus = array();
    foreach ( $all_menus as $menu_obj ) {
        if ( is_string( $menu_obj->slug ) && !empty( $menu_obj->slug ) && !empty( $menu_obj->name ) ) {
            $user_menus[ $menu_obj->slug ] = $menu_obj->name;
        }
    }
    // sek_error_log( 'sek_get_user_created_menus', array_merge(
    //     array( 'nimble_page_menu' => __('Default page menu', 'text_domain_to_replace') )
    // , $user_menus ) );
    return array_merge(
        array( 'nimble_page_menu' => __('Default page menu', 'text_domain_to_replace') )
    , $user_menus );
}

?>