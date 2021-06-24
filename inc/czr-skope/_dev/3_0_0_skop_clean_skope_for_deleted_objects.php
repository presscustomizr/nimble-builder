<?php

if ( !class_exists( 'Flat_Skope_Clean_Final' ) ) :
    final class Flat_Skope_Clean_Final extends Flat_Export_Skope_Data_And_Send_To_Panel {
          // Fired in Flat_Skop_Base::__construct()
          public function skp_schedule_cleaning_on_object_delete() {
              add_action( 'delete_post', array( $this, 'skp_clean_skopified_posts' ) );
              add_action( 'delete_term_taxonomy', array( $this, 'skp_clean_skopified_taxonomies' ) );
              add_action( 'delete_user', array( $this, 'skp_clean_skopified_users' ) );
          }


          // Clean any associated skope post for all public post types : post, page, public cpt
          // 'delete_post' Fires immediately before a post is deleted from the database.
          // @see wp-includes/post.php
          // don't have to return anything
          public function skp_clean_skopified_posts( $postid ) {
              $deletion_candidate = get_post( $postid );
              if ( !$deletion_candidate || !is_object( $deletion_candidate ) )
                return;

              // Stop here if the post type is not considered "viewable".
              // For built-in post types such as posts and pages, the 'public' value will be evaluated.
              // For all others, the 'publicly_queryable' value will be used.
              // For example, the 'revision' post type, which is purely internal and not skopable, won't pass this test.
              if ( !is_post_type_viewable( $deletion_candidate->post_type ) )
                return;

              // Force the skope parts normally retrieved with skp_get_query_skope()
              $skope_string = skp_get_skope( null, true, array(
                  'meta_type' => 'post',
                  'type'      => $deletion_candidate->post_type,
                  'obj_id'    => $postid
              ) );

              // build a skope_id with the normalized function
              $skope_id = skp_build_skope_id( array( 'skope_string' => $skope_string, 'skope_level' => 'local' ) );

              // fetch the skope post id which, if exists, is set as a theme mod
              $skope_post_id_candidate = get_theme_mod( $skope_id );
              if ( $skope_post_id_candidate > 0 && get_post( $skope_post_id_candidate ) ) {
                  // permanently delete the skope post from db
                  wp_delete_post( $skope_post_id_candidate );
                  // remove the theme_mod
                  remove_theme_mod( $skope_id );
              }
          }


          // 'delete_term_taxonomy' Fires immediately before a term taxonomy ID is deleted.
          public function skp_clean_skopified_taxonomies( $term_id ) {
              $deletion_candidate = get_term( $term_id );
              if ( !$deletion_candidate || !is_object( $deletion_candidate ) )
                return;

              //error_log( print_r( $deletion_candidate, true ) );

              // Force the skope parts normally retrieved with skp_get_query_skope()
              $skope_string = skp_get_skope( null, true, array(
                  'meta_type' => 'tax',
                  'type'      => $deletion_candidate->taxonomy,
                  'obj_id'    => $term_id
              ) );

              // build a skope_id with the normalized function
              $skope_id = skp_build_skope_id( array( 'skope_string' => $skope_string, 'skope_level' => 'local' ) );

              // fetch the skope post id which, if exists, is set as a theme mod
              $skope_post_id_candidate = get_theme_mod( $skope_id );
              if ( $skope_post_id_candidate > 0 && get_post( $skope_post_id_candidate ) ) {
                  // permanently delete the skope post from db
                  wp_delete_post( $skope_post_id_candidate );
                  // remove the theme_mod
                  remove_theme_mod( $skope_id );
                  //error_log( 'SUCCESSFULLY REMOVED SKOPE POST ID ' . $skope_post_id_candidate . ' AND THEME MOD ' . $skope_id );
              }
          }


          // 'delete_user' Fires immediately before a user is deleted from the database.
          public function skp_clean_skopified_users( $user_id ) {
              // Force the skope parts normally retrieved with skp_get_query_skope()
              $skope_string = skp_get_skope( null, true, array(
                  'meta_type' => 'user',
                  'type'      => 'author',
                  'obj_id'    => $user_id
              ) );

              // build a skope_id with the normalized function
              $skope_id = skp_build_skope_id( array( 'skope_string' => $skope_string, 'skope_level' => 'local' ) );

              // fetch the skope post id which, if exists, is set as a theme mod
              $skope_post_id_candidate = get_theme_mod( $skope_id );
              if ( $skope_post_id_candidate > 0 && get_post( $skope_post_id_candidate ) ) {
                  // permanently delete the skope post from db
                  wp_delete_post( $skope_post_id_candidate );
                  // remove the theme_mod
                  remove_theme_mod( $skope_id );
                  //error_log( 'SUCCESSFULLY REMOVED SKOPE POST ID ' . $skope_post_id_candidate . ' AND THEME MOD ' . $skope_id );
              }
          }
    }//class
endif;

?>