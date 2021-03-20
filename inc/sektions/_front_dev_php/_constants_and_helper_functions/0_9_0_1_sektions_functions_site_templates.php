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
    
    if ( !sek_local_skope_has_nimble_sections( $skope_id ) ) {
        $group_skope = skp_get_skope_id( 'group' );
        $tmpl_post_name = sek_get_site_tmpl_for_skope( $group_skope );
        //sek_error_log('group skope id for CSS GENERATION ?', $group_skope );
        //sek_error_log('SITE template for skope ' . $group_skope . ' => ' . $tmpl_post_name );

        if ( !is_null( $tmpl_post_name ) && is_string( $tmpl_post_name ) ) {
            $skope_id = $group_skope;
        }
    }
    // if ( 'skp__all_page' === $group_skope ) {
    //     $skope_id = $group_skope;
    // }

    sek_error_log('alors local skope id for CSS GENERATION ?', $skope_id );

    return $skope_id;
});





function sek_maybe_get_seks_for_group_site_template( $group_skope = null ) {
    if ( !sek_is_site_tmpl_enabled() )
        return [];

    $group_skope = skp_get_skope_id( 'group' );
    $seks_data = [];

    // do we have a template assigned to this group skope ?
    // For example is skp__all_page assigned to template 'nb_tmpl_nimble-template-loop-start-only'
    $tmpl_post_name = sek_get_site_tmpl_for_skope( $group_skope );

    sek_error_log('SITE template for skope ' . $group_skope . ' => ' . $tmpl_post_name );

    if ( is_null( $tmpl_post_name ) || !is_string( $tmpl_post_name ) )
        return [];

    if ( skp_is_customizing() ) {
        $current_tmpl_post = sek_get_saved_tmpl_post( $tmpl_post_name );
        if ( $current_tmpl_post ) {
            $current_tmpl_data = maybe_unserialize( $current_tmpl_post->post_content );
            if ( is_array($current_tmpl_data) && isset($current_tmpl_data['data']) && is_array($current_tmpl_data['data']) && !empty($current_tmpl_data['data']) ) {
                $current_tmpl_data = $current_tmpl_data['data'];
                $current_tmpl_data = sek_set_ids( $current_tmpl_data );
                sek_error_log( 'CUSTOMIZING SEKS DATA FROM TEMPLATE => ' . $tmpl_post_name );
                $seks_data = $current_tmpl_data;
            }
        }
    } else {
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
                    sek_error_log( 'sek_update_sek_post => ' . $tmpl_post_name );
                    $post = sek_update_sek_post( $current_tmpl_data, [ 'skope_id' => $group_skope ]);
                    //sek_error_log('POST DATA ?', maybe_unserialize( $post->post_content ) );
                
                }
            }
        }

        if ( $post ) {
            $seks_data = maybe_unserialize( $post->post_content );
        }
    }
    

    return $seks_data;      
}

// Action declared in class Nimble_Options_Setting
add_action('nb_on_customizer_global_options_update', function( $opt_name, $value ) {
    sek_error_log('on nb_on_customizer_global_options_update => CURRENT OPTION VAL ?' . $opt_name , get_option( $opt_name ) );
    sek_error_log('on nb_on_customizer_global_options_update => NEW OPTION VAL ?' . $opt_name , $value );
}, 10, 2);

?>