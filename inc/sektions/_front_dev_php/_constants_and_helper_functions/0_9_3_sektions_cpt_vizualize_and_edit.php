<?php
// Nov 2020
// When sek_is_cpt_debug_mode() = isset( $_GET['nimble_cpt_debug'] ) || (defined('NIMBLE_CPT_DEBUG_MODE') && NIMBLE_CPT_DEBUG_MODE);
// NB custom post types for skoped sections, user saved sections and templates are set to "public" in the WP admin
// To properly vizualise and edit the CPT we need :
// 1) to filter content before it's rendered in the CPT admin editor, so it's rendered as a JSON
// This is done with 'content_edit_pre'
// 2) to disable the rich editor when editing NB CPT, to prevent any html tags insertion done by rich text editor ( done at 'current_screen' )
// 3) before db insertion, to make sur the NB CPT is turned from JSON to serialized value ( done with 'wp_insert_post_data' )
// Disable rich editor when editing NB custom post types
add_action( 'current_screen', function() {
    if ( !sek_is_cpt_debug_mode() || !is_admin() || skp_is_customizing() )
      return;

    global $pagenow;
    global $typenow;
    if ( 'post.php' === $pagenow && in_array($typenow, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT]) ) {
        add_filter( 'user_can_richedit' , '__return_false', 50 );
    }
});

// Jsonify
add_filter( 'content_edit_pre', function( $content, $post_id ) {
    if ( !sek_is_cpt_debug_mode() )
      return $content;
    $post_type = get_post_type( $post_id );
    if ( in_array($post_type, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT]) ) {
        return wp_json_encode(maybe_unserialize($content), JSON_PRETTY_PRINT);
    }
    return $content;
}, 10, 2 );


// BEFORE INSERTION / UPDATE
// @see wp-includes/post.php
// Reformat edited custom post type when updating from the editor
add_filter( 'wp_insert_post_data', function( $data, $postarr, $unsanitized_postarr ) {
    sek_error_log('skp_is_customizing() ??', skp_is_customizing() );

    global $pagenow;
    global $typenow;
    error_log('MEEEEEEEEEEEEEEEEEEEEE'.$pagenow . get_post_type() . $typenow);

    if ( !sek_is_cpt_debug_mode() || skp_is_customizing() || (defined('DOING_AJAX') && DOING_AJAX) )
      return $data;

    sek_error_log('$_POST ??' . (defined('DOING_AJAX') && DOING_AJAX), $_POST  );

    if ( 'post.php' !== $pagenow || !in_array($typenow, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT]) )
      return $data;

    sek_error_log('$data ??', $data);

    if ( is_array($data) && isset($data['post_status']) && 'publish' !== $data['post_status'] )
      return $data;


    $post_type = 'not_set';
    if ( is_array($postarr) && !empty($postarr['post_type']) ) {
        $post_type = $postarr['post_type'];
    }
    $pre_content = $data['post_content'];
    if ( in_array($post_type, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT]) && is_array($data) && isset($data['post_content']) ) {
        $pre_content = json_decode( wp_unslash( $pre_content ), true );

        sek_error_log('$pre_content ??', $pre_content);

        $data['post_content'] = maybe_serialize( $pre_content );
    }
    return $data;
}, 10 , 3);
?>