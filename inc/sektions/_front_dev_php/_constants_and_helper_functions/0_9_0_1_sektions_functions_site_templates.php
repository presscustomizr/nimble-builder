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


// filter declared in inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
add_filter( 'nb_set_skope_id_before_generating_local_front_css', function( $skope_id ) {
    if ( !sek_is_site_tmpl_enabled() )
        return $skope_id;
    
        //sek_error_log('BEFORE alors local skope id for CSS GENERATION ?', $skope_id );

    if ( !sek_local_skope_has_nimble_sections( $skope_id ) ) {
        $group_site_tmpl_data = sek_get_group_site_template_data();//<= is cached when called
        $has_group_skope_template_data = !$group_site_tmpl_data || empty($group_site_tmpl_data);
        //sek_error_log('group skope id for CSS GENERATION ?', $group_skope );
        //sek_error_log('SITE template for skope ' . $group_skope . ' => ' . $tmpl_post_name );
        if ( $has_group_skope_template_data ) {
            $skope_id = $group_skope;
        }
    }
    // if ( 'skp__all_page' === $group_skope ) {
    //     $skope_id = $group_skope;
    // }

    //sek_error_log('alors local skope id for CSS GENERATION ?', $skope_id );

    return $skope_id;
});




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
// Is cached
function sek_get_group_site_template_data() {
    $cached = wp_cache_get('nimble_group_site_template_data');
    if ( $cached && is_array($cached) && !empty($cached) )
        return $cached;

    sek_error_log('CACHE SITE TEMPLATE DATA NOW');

    $group_site_tmpl_data = [];
    
    $group_skope = skp_get_skope_id( 'group' );
    $tmpl_post_name = sek_get_site_tmpl_for_skope( $group_skope );
    if ( is_null( $tmpl_post_name ) || !is_string( $tmpl_post_name ) )
        return;
    // Is this group template already saved ?
    // For example, for pages, there should be a nimble CPT post named nimble___skp__all_page
    $post = sek_get_seks_post( $group_skope );

    //sek_error_log('POST ??' . $tmpl_post_name, $post );
    // if not, let's insert it
    if ( !$post ) {
        $current_tmpl_post = sek_get_saved_tmpl_post( $tmpl_post_name );
        if ( $current_tmpl_post ) {
            //sek_error_log( 'TEMPLATE POST ?', $current_tmpl_post );
            $current_tmpl_data = maybe_unserialize( $current_tmpl_post->post_content );
            if ( is_array($current_tmpl_data) && isset($current_tmpl_data['data']) && is_array($current_tmpl_data['data']) && !empty($current_tmpl_data['data']) ) {
                $current_tmpl_data = $current_tmpl_data['data'];
                //sek_error_log( 'current_tmpl_data ?', $current_tmpl_data );
                $current_tmpl_data = sek_set_ids( $current_tmpl_data );
                sek_error_log( __FUNCTION__ .' => sek_update_sek_post => ' . $tmpl_post_name );
                $post = sek_update_sek_post( $current_tmpl_data, [ 'skope_id' => $group_skope ]);
                //sek_error_log('POST DATA ?', maybe_unserialize( $post->post_content ) );
            
            }
        }
    }
    if ( $post ) {
        $group_site_tmpl_data = maybe_unserialize( $post->post_content );
    }
    wp_cache_set('nimble_group_site_template_data', $group_site_tmpl_data );
    return $group_site_tmpl_data;
}


// Action declared in class Nimble_Options_Setting
// When a site template is modified, the following action removes the skoped post + removes the corresponding CSS stylesheet
// For example, when the page site template is changed, we need to remove the associated skoped post named 'nimble___skp__all_page'
// This post has been inserted when running sek_maybe_get_seks_for_group_site_template(), fired from sek_get_skoped_seks()
add_action('nb_on_customizer_global_options_update', function( $opt_name, $value ) {
    if ( !sek_is_site_tmpl_enabled() )
        return;

    $current_site_tmpl = sek_get_global_option_value( 'site_templates' );
    if ( !is_array( $value ) || !is_array($current_site_tmpl) )
        return;

    $new_site_tmpl = isset($value['site_templates']) ? $value['site_templates'] : [];
    foreach( $new_site_tmpl as $skope => $new_tmpl ) {
        if ( array_key_exists($skope, $current_site_tmpl ) && $current_site_tmpl[$skope] != $new_tmpl ) {
            sek_error_log('TEMPLATE POST TO REMOVE => ' . $skope . ' | ' . $new_tmpl );
            sek_remove_seks_post( $skope );
        }
    }
}, 10, 2);

?>