<?php
///////////////////////////////////////////////////////
/// SITE TEMPLATES
// Feb 2021 => experimental for https://github.com/presscustomizr/nimble-builder/issues/478


/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES OPTIONS HELPERS
/* ------------------------------------------------------------------------- */
function sek_get_site_tmpl_for_skope( $group_skope = null ) {
    if ( is_null($group_skope) || !is_string($group_skope) || empty($group_skope) )
        return;
    $site_tmpl = null;
    $opts = sek_get_global_option_value( 'site_templates' );

    //sek_error_log('site_templates options ?', $opts );

    if ( is_array( $opts) && !empty( $opts[$group_skope] ) && '_no_site_tmpl_' != $opts[$group_skope] ) {
        $site_tmpl = $opts[$group_skope];
    }
    return $site_tmpl;
}



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES CSS
/* ------------------------------------------------------------------------- */
// filter declared in inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
add_filter( 'nb_set_skope_id_before_generating_local_front_css', function( $skope_id ) {
    if ( !sek_is_site_tmpl_enabled() )
        return $skope_id;

    if ( !sek_local_skope_has_nimble_sections( $skope_id ) ) {
        $group_site_tmpl_data = sek_get_group_site_template_data();//<= is cached when called
        $has_group_skope_template_data = !( !$group_site_tmpl_data || empty($group_site_tmpl_data) );
        if ( $has_group_skope_template_data ) {
            $group_skope = skp_get_skope_id( 'group' );
            if ( !empty($group_skope) && '_skope_not_set_' !== $group_skope ) {
                $skope_id = $group_skope;
            }
        }
    }
    return $skope_id;
});



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES CONTENT
/* ------------------------------------------------------------------------- */
// Called in sek_get_skoped_seks()
function sek_maybe_get_seks_for_group_site_template( $skope_id, $local_seks_data ) {
    if ( !sek_is_site_tmpl_enabled() )
        return $local_seks_data;
    // NB will only inherit group skope for local sektions
    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return $local_seks_data;
    }

    // If the local skoped already includes at least a section, no inheritance
    $has_local_sections = is_array( $local_seks_data ) ? ( sek_count_not_empty_sections_in_page( $local_seks_data ) > 0 ) : false;
    if ( $has_local_sections )
        return $local_seks_data;

    $group_site_tmpl_data = sek_get_group_site_template_data();

    if ( !$group_site_tmpl_data || empty($group_site_tmpl_data) )
        return $local_seks_data;

    return $group_site_tmpl_data;      
}

// @return null || array
// get and cache the group site template data
function sek_get_group_site_template_data() {
    $cached = wp_cache_get('nimble_group_site_template_data');
    if ( $cached && is_array($cached) && !empty($cached) )
        return $cached;

    $group_site_tmpl_data = [];
    
    $group_skope = skp_get_skope_id( 'group' );
    $tmpl_post_name = sek_get_site_tmpl_for_skope( $group_skope );
    if ( is_null( $tmpl_post_name ) || !is_string( $tmpl_post_name ) )
        return;
    // Is this group template already saved ?
    // For example, for pages, there should be a nimble CPT post named nimble___skp__all_page
    $post = sek_get_seks_post( $group_skope );

    // if not, let's insert it
    if ( !$post ) {
        $current_tmpl_post = sek_get_saved_tmpl_post( $tmpl_post_name );
        if ( $current_tmpl_post ) {
            $current_tmpl_data = maybe_unserialize( $current_tmpl_post->post_content );
            if ( is_array($current_tmpl_data) && isset($current_tmpl_data['data']) && is_array($current_tmpl_data['data']) && !empty($current_tmpl_data['data']) ) {
                $current_tmpl_data = $current_tmpl_data['data'];
                $current_tmpl_data = sek_set_ids( $current_tmpl_data );
                $post = sek_update_sek_post( $current_tmpl_data, [ 'skope_id' => $group_skope ]);
            }
        }
    }
    if ( $post ) {
        $group_site_tmpl_data = maybe_unserialize( $post->post_content );
    }
    wp_cache_set('nimble_group_site_template_data', $group_site_tmpl_data );
    return $group_site_tmpl_data;
}



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES SAVE CUSTOMIZER ACTION
/* ------------------------------------------------------------------------- */
// Action declared in class Nimble_Options_Setting
// When a site template is modified, the following action removes the skoped post + removes the corresponding CSS stylesheet
// For example, when the page site template is changed, we need to remove the associated skoped post named 'nimble___skp__all_page'
// This post has been inserted when running sek_maybe_get_seks_for_group_site_template(), fired from sek_get_skoped_seks()
add_action('nb_on_save_customizer_global_options', function( $opt_name, $value ) {
    if ( !sek_is_site_tmpl_enabled() )
        return;

    $current_site_tmpl = sek_get_global_option_value( 'site_templates' );
    if ( !is_array( $value ) || !is_array($current_site_tmpl) )
        return;

    $new_site_tmpl = isset($value['site_templates']) ? $value['site_templates'] : [];
    foreach( $new_site_tmpl as $group_skope => $new_tmpl ) {
        if ( array_key_exists($group_skope, $current_site_tmpl ) && $current_site_tmpl[$group_skope] != $new_tmpl ) {
            //sek_error_log('TEMPLATE POST TO REMOVE => ' . $group_skope . ' | ' . $new_tmpl );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
    }
}, 10, 2);



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES : UPDATED TEMPLATE IN CUSTOMIZER
/* ------------------------------------------------------------------------- */
// Action fired during server ajax callback sek_update_saved_tmpl_post
// Solves the problem of template synchronization between the group skope post ( in which the chosen template is saved with permanent level ids ), and the current state of the template
// Solution => each time a template is updated, NB checks if the template is being used by a group skope
// if so, then the group skope post is removed ( along with the index and the css stylesheet )
// 
// When will the removed skope post be re-inserted ?
// next time the group skope will be printed ( for example skp__all_page in a single page ), NB checks if a template is assigned to this group skope, and tries to get the skope post.
// If the group skope post is not found, NB attempts to re-insert it
add_action('nb_on_update_saved_tmpl_post', function( $tmpl_post_name ) {
    if ( !sek_is_site_tmpl_enabled() )
        return;

    if ( is_null( $tmpl_post_name ) || !is_string( $tmpl_post_name ) )
        return;

    $site_tmpl_opts = sek_get_global_option_value( 'site_templates' );
    if ( !is_array($site_tmpl_opts) )
        return;

    foreach( $site_tmpl_opts as $group_skope => $tmpl_name ) {
        if ( $tmpl_post_name === $tmpl_name ) {
            sek_error_log('REMOVE GROUP SKOPE POST ' . $group_skope . ' for template ' . $tmpl_name );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
    }
},10, 1);




/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES AJAX HELPERS
/* ------------------------------------------------------------------------- */
// Ajax action fired each time a site template is being modified
// Solves the problem of template synchronization between the group skope post ( in which the chosen template is saved with permanent level ids ), and the current state of the template
// Solution => each time a template is updated, NB removes all group skope posts ( along with their option indexes and the css stylesheets )
// 
// When will the removed skope posts be re-inserted ?
// next time the group skope will be printed ( for example skp__all_page in a single page ), NB checks if a template is assigned to this group skope, and tries to get the skope post.
// If the group skope post is not found, NB attempts to re-insert it
add_action( 'wp_ajax_sek_reset_site_template', '\Nimble\sek_ajax_reset_site_template' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_ajax_reset_site_template
function sek_ajax_reset_site_template() {
    if ( !sek_is_site_tmpl_enabled() )
        return;

    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
   
    if ( !isset( $_POST['site_template_opts'] ) || empty( $_POST['site_template_opts'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_site_template_opts' );
    }
    // $_POST['site_template_opts'] is an array looking like :
    // [id] => sek_site_tmpl_pickers_0
    // [title] => 
    // [skp__all_page] => nb_tmpl_home-page-template
    // [skp__all_post] => _no_site_tmpl_
    // [skp__all_category] => _no_site_tmpl_
    // [skp__all_post_tag] => _no_site_tmpl_
    // [skp__all_author] => _no_site_tmpl_
    // [skp__search] => _no_site_tmpl_
    // [skp__404] => _no_site_tmpl_
    
    $site_tmpl_opts = is_array( $_POST['site_template_opts'] ) ? $_POST['site_template_opts'] : [];
    if ( empty( $site_tmpl_opts ) ) {
        wp_send_json_error( __FUNCTION__ . '_empty_site_template_opts' );
    }

    // Reset all group skope post + update the index + removes the associated stylesheets
    foreach( $site_tmpl_opts as $group_skope => $tmpl_name ) {
        if ( false === strpos($group_skope, 'skp_') )
            continue;
        if ( '_no_site_tmpl_' === $tmpl_name ) {
            continue;
        }
        $r = sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
    }

    if ( is_wp_error( $r ) ) {
        wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_remove_seks_post()' );
    } else {
        // sek_error_log( 'ALORS CE POST?', $saved_template_post );
        wp_send_json_success( __FUNCTION__ . ' => all group skope post cleaned');
    }
}

?>