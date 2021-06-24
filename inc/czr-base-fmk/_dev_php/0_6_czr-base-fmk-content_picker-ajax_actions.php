<?php

/**
* Customizer ajax content picker actions
*
*/
// CZR_Fmk_Base
if ( !class_exists( 'CZR_Fmk_Base' ) ) :
    class CZR_Fmk_Base extends CZR_Fmk_Dyn_Module_Registration {

      //fired in the constructor
      public function czr_setup_content_picker_ajax_actions() {
          if ( current_user_can( 'customize' ) ) {
              add_action( 'wp_ajax_load-available-content-items-customizer'   , array( $this, 'ajax_load_available_items' ) );
              add_action( 'wp_ajax_search-available-content-items-customizer' , array( $this, 'ajax_search_available_items' ) );
          }

            // CONTENT PICKER INPUT
            //add the _custom_ item to the content picker retrieved in ajax
            //add_filter( 'content_picker_ajax_items', array( $this, 'czr_add_custom_item_to_ajax_results' ), 10, 3 );
      }

      // hook : 'content_picker_ajax_items'
      function czr_add_custom_item_to_ajax_results( $items, $page, $context ) {
          if ( is_numeric( $page ) && $page < 1 ) {
              return array_merge(
                  array(
                      array(
                         'title'      => sprintf( '<span style="font-weight:bold">%1$s</span>', __('Set a custom url', 'text_doma') ),
                         'type'       => '',
                         'type_label' => '',
                         'object'     => '',
                         'id'         => '_custom_',
                         'url'        => ''
                      )
                  ),
                  $items
              );
          } else {
              return $items;
          }
      }


      //hook : 'pre_post_link'
      function dont_use_fancy_permalinks() {
        return '';
      }


      /* ------------------------------------------------------------------------- *
       *  LOAD
      /* ------------------------------------------------------------------------- */
      /**
       * Ajax handler for loading available content items.
       * hook : wp_ajax_load-available-content-items-customizer
       */
      public function ajax_load_available_items() {
            $action = 'save-customize_' . get_stylesheet();
            if ( !check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                  'code' => 'invalid_nonce',
                  'message' => __( 'ajax_load_available_items => Security check failed.' ),
                ) );
            }

            if ( !current_user_can( 'customize' ) ) {
                wp_send_json_error('ajax_load_available_items => user_cant_edit_theme_options');
            }
            if ( !isset( $_POST['wp_object_types'] ) || empty( $_POST['wp_object_types'] ) ) {
                wp_send_json_error( 'czr_ajax_content_picker_missing_type_or_object_parameter' );
            }

            if ( !isset( $_POST['page'] ) ) {
                wp_send_json_error( 'czr_ajax_content_picker_missing_pagination_param' );
            }

            //error_log( print_r($_POST, true ) );

            $wp_object_types = json_decode( wp_unslash( $_POST['wp_object_types'] ), true );

            //$wp_object_types should look like :
            //array(
            //      post : '',//<= all post types
            //      taxonomy : ''//<= all taxonomy types
            //) OR
            //array(
            //      post : [ 'page', 'cpt1', ...]
            //      taxonomy : [ 'category', 'tag', 'Custom_Tax_1', ... ]
            //) OR
            //array(
            //      post : [ 'page', 'cpt1', ...]
            //      taxonomy : '_none_'//<= don't load or search taxonomies
            //)
            if ( !is_array( $wp_object_types ) || empty( $wp_object_types ) ) {
              wp_send_json_error( 'czr_ajax_content_picker_missing_object_types' );
            }
            $page = empty( $_POST['page'] ) ? 0 : absint( $_POST['page'] );
            //do we need that ?
            // if ( $page < 1 ) {
            //   $page = 1;
            // }

            $items = array();

            add_filter( 'pre_post_link', array( $this, 'dont_use_fancy_permalinks' ), 999 );
            foreach ( $wp_object_types as $_type => $_obj_types ) {
                if ( '_none_' == $_obj_types )
                  continue;
                $item_candidates = $this -> load_available_items_query(
                    array(
                        'type'          => $_type, //<= post or taxonomy
                        'object_types'  => $_obj_types,//<= '' or array( type1, type2, ... )
                        'page'          => $page
                    )
                );
                if ( is_array( $item_candidates ) ) {
                    $items = array_merge(
                        $items,
                        $item_candidates
                    );
                }
            }
            remove_filter( 'pre_post_link', array( $this, 'dont_use_fancy_permalinks' ), 999 );

            if ( is_wp_error( $items ) ) {
                wp_send_json_error( $items->get_error_code() );
            } else {
                wp_send_json_success( array(
                    'items' => apply_filters( 'content_picker_ajax_items', $items, $page, 'ajax_load_available_items' )
                ) );
            }
      }


      /**
       * Performs the post_type and taxonomy queries for loading available items.
       *
       * @since
       * @access public
       *
       * @param string $type   Optional. Accepts any custom object type and has built-in support for
       *                         'post_type' and 'taxonomy'. Default is 'post_type'.
       * @param string $object Optional. Accepts any registered taxonomy or post type name. Default is 'page'.
       * @param int    $page   Optional. The page number used to generate the query offset. Default is '0'.
       * @return WP_Error|array Returns either a WP_Error object or an array of menu items.
       */
      public function load_available_items_query( $args ) {
            //normalize args
            $args = wp_parse_args( $args, array(
                  'type'          => 'post',
                  'object_types'  => '_all_',//could be page, post, or any CPT
                  'page'          => 0
            ) );

            $type         = $args['type'];
            $object_types = $args['object_types'];
            $page         = $args['page'];

            $items = array();
            if ( 'post' === $type ) {
                  //What are the post types we need to fetch ?
                  if ( '_all_' == $object_types || !is_array( $object_types ) || ( is_array( $object_types ) && empty( $object_types ) ) ) {
                      $post_types = get_post_types( array( 'public' => true ) );
                  } else {
                      $post_types = $object_types;
                  }
                  if ( !$post_types || !is_array( $post_types ) || empty( $post_types ) ) {
                      return new \WP_Error( 'czr_contents_invalid_post_type' );
                  }

                  $posts = get_posts( array(
                      'numberposts' => 5,
                      'offset'      => 5 * $page,
                      'orderby'     => 'date',
                      'order'       => 'DESC',
                      'post_type'   => $post_types,
                  ) );

                  foreach ( $posts as $post ) {
                        $post_title = $post->post_title;
                        if ( '' === $post_title ) {
                          // translators: %d: ID of a post
                          $post_title = sprintf( __( '#%d (no title)', 'text_doma' ), $post->ID );
                        }
                        $items[] = array(
                            'title'      => html_entity_decode( $post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                            'type'       => 'post',
                            'type_label' => get_post_type_object( $post->post_type )->labels->singular_name,
                            'object'     => $post->post_type,
                            'id'         => intval( $post->ID ),
                            'url'        => get_permalink( intval( $post->ID ) ),
                        );
                  }

            } elseif ( 'taxonomy' === $type ) {
                  //What are the taxonomy types we need to fetch ?
                  if ( '_all_' == $object_types || !is_array( $object_types ) || ( is_array( $object_types ) && empty( $object_types ) ) ) {
                      $taxonomies = get_taxonomies( array( 'show_in_nav_menus' => true ), 'names' );
                  } else {
                      $taxonomies = $object_types;
                  }
                  if ( !$taxonomies || !is_array( $taxonomies ) || empty( $taxonomies ) ) {
                      return new \WP_Error( 'czr_contents_invalid_post_type' );
                  }
                  $terms = get_terms( $taxonomies, array(
                      'child_of'     => 0,
                      'exclude'      => '',
                      'hide_empty'   => false,
                      'hierarchical' => 1,
                      'include'      => '',
                      'number'       => 5,
                      'offset'       => 5 * $page,
                      'order'        => 'DESC',
                      'orderby'      => 'count',
                      'pad_counts'   => false,
                  ) );
                  if ( is_wp_error( $terms ) ) {
                    return $terms;
                  }

                  foreach ( $terms as $term ) {
                        $items[] = array(
                            'title'      => html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                            'type'       => 'taxonomy',
                            'type_label' => get_taxonomy( $term->taxonomy )->labels->singular_name,
                            'object'     => $term->taxonomy,
                            'id'         => intval( $term->term_id ),
                            'url'        => get_term_link( intval( $term->term_id ), $term->taxonomy ),
                        );
                  }
            }

            /**
             * Filters the available items.
             *
             * @since
             *
             * @param array  $items  The array of menu items.
             * @param string $type   The object type.
             * @param string $object The object name.
             * @param int    $page   The current page number.
             */
            $items = apply_filters( 'czr_customize_content_picker_available_items', $items, $type, $object_types, $page );
            return $items;
      }




      /* ------------------------------------------------------------------------- *
       *  SEARCH
      /* ------------------------------------------------------------------------- */
      /**
       * Ajax handler for searching available menu items.
       * hook : wp_ajax_search-available-content-items-customizer
       */
      public function ajax_search_available_items() {
            $action = 'save-customize_' . get_stylesheet();
            if ( !check_ajax_referer( $action, 'nonce', false ) ) {
                wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( 'ajax_load_available_items => Security check failed.' ),
                ) );
            }

            if ( !current_user_can( 'customize' ) ) {
                wp_send_json_error('ajax_load_available_items => user_cant_edit_theme_options');
            }
            if ( !isset( $_POST['wp_object_types'] ) || empty( $_POST['wp_object_types'] ) ) {
                wp_send_json_error( 'czr_ajax_content_picker_missing_type_or_object_parameter' );
            }
            if ( empty( $_POST['search'] ) ) {
                wp_send_json_error( 'czr_contents_missing_search_parameter' );
            }

            $p = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 0;
            if ( $p < 1 ) {
              $p = 1;
            }
            $s = sanitize_text_field( wp_unslash( $_POST['search'] ) );

            $wp_object_types = json_decode( wp_unslash( $_POST['wp_object_types'] ), true );

            //$wp_object_types should look like :
            //array(
            //      post : '',//<= all post types
            //      taxonomy : ''//<= all taxonomy types
            //) OR
            //array(
            //      post : [ 'page', 'cpt1', ...]
            //      taxonomy : [ 'category', 'tag', 'Custom_Tax_1', ... ]
            //) OR
            //array(
            //      post : [ 'page', 'cpt1', ...]
            //      taxonomy : '_none_'//<= don't load or search taxonomies
            //)
            if ( !is_array( $wp_object_types ) || empty( $wp_object_types ) ) {
              wp_send_json_error( 'czr_ajax_content_picker_missing_object_types' );
            }

            $items = array();

            add_filter( 'pre_post_link', array( $this, 'dont_use_fancy_permalinks' ), 999 );

            foreach ( $wp_object_types as $_type => $_obj_types ) {
                if ( '_none_' == $_obj_types )
                  continue;
                $item_candidates = $this -> search_available_items_query(
                    array(
                        'type'          => $_type, //<= post or taxonomy
                        'object_types'  => $_obj_types,//<= '' or array( type1, type2, ... )
                        'pagenum'       => $p,
                        's'             => $s
                    )
                );
                if ( is_array( $item_candidates ) ) {
                    $items = array_merge(
                        $items,
                        $item_candidates
                    );
                }
            }
            remove_filter( 'pre_post_link', array( $this, 'dont_use_fancy_permalinks' ), 999 );

            if ( empty( $items ) ) {
                wp_send_json_success( array( 'message' => __( 'No results found.', 'text_doma') ) );
            } else {
                wp_send_json_success( array(
                    'items' => apply_filters( 'content_picker_ajax_items', $items, $p, 'ajax_search_available_items' )
                ) );
            }
      }


      /**
       * Performs post queries for available-item searching.
       *
       * Based on WP_Editor::wp_link_query().
       *
       * @since 4.3.0
       * @access public
       *
       * @param array $args Optional. Accepts 'pagenum' and 's' (search) arguments.
       * @return array Menu items.
       */
      public function search_available_items_query( $args = array() ) {
            //normalize args
            $args = wp_parse_args( $args, array(
                  'pagenum'       => 1,
                  's'             => '',
                  'type'          => 'post',
                  'object_types'  => '_all_'//could be page, post, or any CPT
            ) );
            $object_types = $args['object_types'];

            //TODO: need a search only on the allowed types
            $items = array();
            if ( 'post' === $args['type'] ) {
                  //What are the post types we need to fetch ?
                  if ( '_all_' == $object_types || !is_array( $object_types ) || ( is_array( $object_types ) && empty( $object_types ) ) ) {
                      $post_types = get_post_types( array( 'public' => true ) );
                  } else {
                      $post_types = $object_types;
                  }
                  if ( !$post_types || empty( $post_types ) ) {
                      return new \WP_Error( 'czr_contents_invalid_post_type' );
                  }

                  $query = array(
                      'suppress_filters'       => true,
                      'update_post_term_cache' => false,
                      'update_post_meta_cache' => false,
                      'post_status'            => 'publish',
                      'posts_per_page'         => 10,
                  );
                  $args['pagenum']    = isset( $args['pagenum'] ) ? absint( $args['pagenum'] ) : 1;
                  $query['offset']    = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;
                  $query['post_type'] = $post_types;

                  if ( isset( $args['s'] ) ) {
                      $query['s'] = $args['s'];
                  }

                  // Query posts.
                  $get_posts = new \WP_Query( $query );
                  // Check if any posts were found.
                  if ( $get_posts->post_count ) {
                      foreach ( $get_posts->posts as $post ) {
                            $post_title = $post->post_title;
                            if ( '' === $post_title ) {
                              /* translators: %d: ID of a post */
                              $post_title = sprintf( __( '#%d (no title)', 'text_doma' ), $post->ID );
                            }
                            $items[] = array(
                                'title'      => html_entity_decode( $post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                                'type'       => 'post',
                                'type_label' => get_post_type_object( $post->post_type )->labels->singular_name,
                                'object'     => $post->post_type,
                                'id'         => intval( $post->ID ),
                                'url'        => get_permalink( intval( $post->ID ) ),
                            );
                      }
                  }
            } elseif ( 'taxonomy' === $args['type'] ) {
                  //What are the taxonomy types we need to fetch ?
                  if ( '_all_' == $object_types || !is_array( $object_types ) || ( is_array( $object_types ) && empty( $object_types ) ) ) {
                      $taxonomies = get_taxonomies( array( 'show_in_nav_menus' => true ), 'names' );
                  } else {
                      $taxonomies = $object_types;
                  }
                  if ( !$taxonomies || !is_array( $taxonomies ) || empty( $taxonomies ) ) {
                      return new \WP_Error( 'czr_contents_invalid_post_type' );
                  }
                  $terms = get_terms( $taxonomies, array(
                      'name__like' => $args['s'],
                      'number'     => 10,
                      'offset'     => 10 * ( $args['pagenum'] - 1 )
                  ) );

                  // Check if any taxonomies were found.
                  if ( !empty( $terms ) ) {
                        foreach ( $terms as $term ) {
                              $items[] = array(
                                  'title'      => html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
                                  'type'       => 'taxonomy',
                                  'type_label' => get_taxonomy( $term->taxonomy )->labels->singular_name,
                                  'object'     => $term->taxonomy,
                                  'id'         => intval( $term->term_id ),
                                  'url'        => get_term_link( intval( $term->term_id ), $term->taxonomy ),
                              );
                        }
                  }
                  /**
                  * Filters the available menu items during a search request.
                   *
                   * @since 4.5.0
                   *
                   * @param array $items The array of menu items.
                   * @param array $args  Includes 'pagenum' and 's' (search) arguments.
                   */
                  $items = apply_filters( 'czr_customize_content_picker_searched_items', $items, $args );
            }
            return $items;
      }
}
endif;

?>