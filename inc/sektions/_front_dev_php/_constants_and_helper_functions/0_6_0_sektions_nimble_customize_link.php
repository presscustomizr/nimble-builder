<?php
add_action( 'admin_bar_menu', '\Nimble\sek_add_customize_link', 1000 );
function sek_add_customize_link() {
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    global $wp_admin_bar;
    // Don't show for users who can't access the customizer
    if ( !current_user_can( 'customize' ) )
      return;

    $return_customize_url = '';
    $customize_url = '';
    if ( is_admin() ) {
        if ( !is_admin_bar_showing() )
            return;

        $customize_url = sek_get_customize_url_when_is_admin();
    } else {
        global $wp_customize;
        // Don't show if the user cannot edit a given customize_changeset post currently being previewed.
        if ( is_customize_preview() && $wp_customize->changeset_post_id() && !current_user_can( get_post_type_object( 'customize_changeset' )->cap->edit_post, $wp_customize->changeset_post_id() ) ) {
          return;
        }
        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        if ( is_customize_preview() && $wp_customize->changeset_uuid() ) {
            $current_url = remove_query_arg( 'customize_changeset_uuid', $current_url );
        }

        $customize_url = add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() );
        if ( is_customize_preview() ) {
            $customize_url = add_query_arg( array( 'changeset_uuid' => $wp_customize->changeset_uuid() ), $customize_url );
        }
    }

    if ( empty( $customize_url ) )
      return;
    $customize_url = add_query_arg(
        array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
        $customize_url
    );

    $wp_admin_bar->add_menu( array(
      'id'     => 'nimble_customize',
      'title'  => sprintf( '<span class="sek-nimble-icon" title="%3$s"><img src="%1$s" alt="%2$s"/><span class="sek-nimble-admin-bar-title">%4$s</span></span>',
          NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION,
          __('Nimble Builder','text_domain_to_replace'),
          __('Add sections in live preview with Nimble Builder', 'text_domain'),
          apply_filters( 'nb_admin_bar_title', __( 'Build with Nimble Builder', 'text_domain' ) )
      ),
      'href'   => $customize_url,
      'meta'   => array(
        'class' => 'hide-if-no-customize',
      ),
    ) );
}//sek_add_customize_link

// returns a customize link when is_admin() for posts and terms
// inspired from wp-includes/admin-bar.php#wp_admin_bar_edit_menu()
// @param $post is a post object
function sek_get_customize_url_when_is_admin( $post = null ) {
    global $tag, $user_id;
    $customize_url = '';
    $current_screen = get_current_screen();
    $post = is_null( $post ) ? get_post() : $post;

    // July 2019 => Don't display the admin button in post and pages, where we already have the edit button next to the post title
    // if ( 'post' == $current_screen->base
    //     && 'add' != $current_screen->action
    //     && ( $post_type_object = get_post_type_object( $post->post_type ) )
    //     && current_user_can( 'read_post', $post->ID )
    //     && ( $post_type_object->public )
    //     && ( $post_type_object->show_in_admin_bar ) )
    // {
    //     if ( 'draft' == $post->post_status ) {
    //         $preview_link = get_preview_post_link( $post );
    //         $customize_url = esc_url( $preview_link );
    //     } else {
    //         $customize_url = get_permalink( $post->ID );
    //     }
    // } else
    if ( 'edit' == $current_screen->base
        && ( $post_type_object = get_post_type_object( $current_screen->post_type ) )
        && ( $post_type_object->public )
        && ( $post_type_object->show_in_admin_bar )
        && ( get_post_type_archive_link( $post_type_object->name ) )
        && !( 'post' === $post_type_object->name && 'posts' === get_option( 'show_on_front' ) ) )
    {
        $customize_url = get_post_type_archive_link( $current_screen->post_type );
    } elseif ( 'term' == $current_screen->base
        && isset( $tag ) && is_object( $tag ) && !is_wp_error( $tag )
        && ( $tax = get_taxonomy( $tag->taxonomy ) )
        && $tax->public )
    {
        $customize_url = get_term_link( $tag );
    } elseif ( 'user-edit' == $current_screen->base
        && isset( $user_id )
        && ( $user_object = get_userdata( $user_id ) )
        && $user_object->exists()
        && $view_link = get_author_posts_url( $user_object->ID ) )
    {
        $customize_url = $view_link;
    }

    if ( !empty( $customize_url ) ) {
        $return_customize_url = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), wp_customize_url() );
        $customize_url = add_query_arg( 'url', urlencode( $customize_url ), $return_customize_url );
    }
    return $customize_url;
}

// introduced for https://github.com/presscustomizr/nimble-builder/issues/436
function sek_get_customize_url_for_post_id( $post_id, $return_url = '' ) {
    // Build customize_url
    // @see function sek_get_customize_url_when_is_admin()
    $customize_url = get_permalink( $post_id );
    $return_url = empty( $return_url ) ? $customize_url : $return_url;
    $return_customize_url = add_query_arg(
        'return',
        urlencode(
            remove_query_arg( wp_removable_query_args(), wp_unslash( $return_url ) )
        ),
        wp_customize_url()
    );
    $customize_url = add_query_arg( 'url', urlencode( $customize_url ), $return_customize_url );
    $customize_url = add_query_arg(
        array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
        $customize_url
    );

    return $customize_url;
}

?>