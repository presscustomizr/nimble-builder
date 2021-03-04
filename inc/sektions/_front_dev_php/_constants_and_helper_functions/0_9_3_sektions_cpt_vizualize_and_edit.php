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
// We need to make sure that the reformating occurs ONLY when this is a manual update
// - not an update when customizing => check if skp_is_customzing() and DOING_AJAX
// - not an insertion of the revision post type => check on $data['post_type']
// Note that the post status can be 'publish', 'draft', 'pending'
add_filter( 'wp_insert_post_data', function( $data, $postarr, $unsanitized_postarr ) {
    global $pagenow;
    // error_log(' PAGE NOW ?'.$pagenow );
    // error_log(' POST TYPE FROM DATA ?'. $data['post_type']);
    // error_log(' POST STATUS ?'. $data['post_status'] );

    // we must be in an single CPT edit screen
    // prevent processing data when restoring a revision
    if ( 'post.php' !== $pagenow )
      return $data;

    if ( !sek_is_cpt_debug_mode() || skp_is_customizing() || (defined('DOING_AJAX') && DOING_AJAX) )
      return $data;

    // $data should be An array of slashed, sanitized, and processed post data.
    // @see wp-includes/post.php
    if ( !is_array($data) )
      return $data;

    $post_type = 'not_set';
    if ( !empty($data['post_type']) ) {
        $post_type = $data['post_type'];
    }
    // make sure we only process nimble CPT post type. Not the 'revision' post types.
    if ( !in_array( $post_type, [NIMBLE_CPT,NIMBLE_SECTION_CPT,NIMBLE_TEMPLATE_CPT] ) )
      return $data;

    // Stop here if the post is being removed
    if ( 'trash' == $data['post_status'] )
      return $data;

    //sek_error_log('$data ??', $data);


    $pre_content = isset($data['post_content']) ? $data['post_content'] : null;
    //sek_error_log('is SERIALIZED ?', is_serialized( $pre_content ));
    // Serialized if content has been jsonified in the editor
    // Important : this check is needed in a scenario when the post has been trashed and is restored. In this case the content is already serialized.
    if ( isset($pre_content) && !empty($pre_content) && !is_serialized( $pre_content ) ) {
        $pre_content = json_decode( wp_unslash( $pre_content ), true );
        // Check if content is a valid json ?
        if ( json_last_error() == JSON_ERROR_NONE ) {
            // if no json error, serialize
            $data['post_content'] = maybe_serialize( $pre_content );
            //sek_error_log('VALID JSON => new post_content ??', $data['post_content'] );
        } else {
            //sek_error_log('INVALID JSON', json_last_error() );
            return new \WP_Error( 'db_insert_error', __('Could not insert NB template into the database : invalid JSON'), json_last_error() );
        }
    }
    return wp_slash($data);
}, 10 , 3);

?>