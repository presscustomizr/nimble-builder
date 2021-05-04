<?php
///////////////////////////////////////////////////////
/// SITE TEMPLATES
// Feb 2021 => experimental for https://github.com/presscustomizr/nimble-builder/issues/478


/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES OPTIONS HELPERS
/* ------------------------------------------------------------------------- */
// @return array of site template info for a given group skope
function sek_get_site_tmpl_params_for_skope( $group_skope = null ) {
    if ( is_null($group_skope) || !is_string($group_skope) || empty($group_skope) )
        return;
    $cache_key = 'nimble_site_tmpl_params_for_skope_'.$group_skope;
    $cached = wp_cache_get( $cache_key );
    if ( false !== $cached )
        return $cached;

    $default_params = [
        'site_tmpl_id' => '_no_site_tmpl_',
        'site_tmpl_source' => 'user_tmpl',
        'site_tmpl_title' => ''
    ];
    $opts = sek_get_global_option_value( 'site_templates' );
    $site_tmpl_params = $default_params;
    if ( is_array( $opts) && !empty( $opts[$group_skope] ) && is_array($opts[$group_skope]) ) {
        $site_tmpl_params = wp_parse_args( $opts[$group_skope], $site_tmpl_params );
    }

    // DO SOME CHECKS BEFORE RETURNING THE PARAMS
    $site_tmpl_id = $site_tmpl_params['site_tmpl_id'];
    $site_tmpl_source = $site_tmpl_params['site_tmpl_source'];
    // Check that id is well formed
    if ( is_null( $site_tmpl_id ) || !is_string( $site_tmpl_id ) ) {
        sek_error_log( 'Error => invalid tmpl post id', $tmpl_params );
        return $default_params;
    }
    //sek_error_log('site_templates params ?' . $group_skope, $opts );
    // Check that tmpl source is OK
    if ( !in_array($site_tmpl_source, ['user_tmpl', 'api_tmpl'] ) ) {
        sek_error_log( 'Error => invalid tmpl source', $tmpl_params );
        return $default_params;
    }
    wp_cache_set($cache_key, $site_tmpl_params);
    return $site_tmpl_params;
}

/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES SKOPE HELPER
/* ------------------------------------------------------------------------- */
// when registering site template global options the suffix '_for_site_tmpl' is added to 'no group skope' scopes : 'skp__search_for_site_tmpl', 'skp__404_for_site_tmpl', 'skp__date_for_site_tmpl'
// see sek_get_module_params_for_sek_site_tmpl_pickers()
function sek_get_group_skope_for_site_tmpl() {
    $group_skope = skp_get_skope_id( 'group' );
    if ( '_skope_not_set_' === $group_skope ) {
        $skope_id = skp_get_skope_id();
        if ( sek_is_no_group_skope( $skope_id ) ) {
            $group_skope = $skope_id . '_for_site_tmpl';
        } else {
            if ( defined('NIMBLE_DEV') && NIMBLE_DEV ) {
                //sek_error_log('group skope could not be set');
            }
        }
    }
    return $group_skope;
}

// @return bool
// no group skope are array( 'home', 'search', '404', 'date' );
function sek_is_no_group_skope( $skope_id = null ) {
    if ( is_null( $skope_id ) ) {
        $skope_id = skp_get_skope_id();
    }
    $skope_id_without_prefix = str_replace( 'skp__', '', $skope_id );
    $skope_with_no_group = skp_get_no_group_skope_list();
    return in_array( $skope_id_without_prefix, $skope_with_no_group );
}

//@return bool
// Tells if the local NB skope has been customized
function sek_local_skope_has_been_customized( $skope_id = '', $local_seks_data = null ) {
    $skope_id = empty( $skope_id ) ? skp_get_skope_id() : $skope_id;

    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return false;
    }

    // When the collection is provided use it otherwise get it
    if ( is_null($local_seks_data) || !is_array($local_seks_data) ) {
        $local_seks_data = sek_get_skoped_seks( $skope_id );
    }
    // normally, we should get an array from the previous function
    if ( !is_array( $local_seks_data ) )
        return false;
    // the local skoped data include property '__inherits_group_skope_tmpl_when_exists__' since site template implementation april 2021
    // If not, it means that we may have a local customized skoped data
    if ( is_array($local_seks_data) && !array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $local_seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' => error => missing property __inherits_group_skope_tmpl_when_exists__' );
        return true;
    }
    // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
    // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
    // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control::resetCollectionSetting )
    // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
    return is_array($local_seks_data) && array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $local_seks_data ) && !$local_seks_data['__inherits_group_skope_tmpl_when_exists__'];
}

//@return bool
function sek_is_static_front_page_on_front_and_when_customizing() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && skp_is_customizing() ) {
        $is_front_page = sek_get_posted_query_param_when_customizing( 'is_front_page' );
    } else {
        $is_front_page = is_front_page();
    }
    return $is_front_page && 'page' == get_option( 'show_on_front' );
}

// @return boolean
// solves the problem of preventing group template inheritance after a local reset
function sek_is_inheritance_locally_disabled() {
    $local_reset_data = sek_get_local_option_value_without_inheritance( 'local_reset' );
    if ( is_array( $local_reset_data ) && array_key_exists( 'inherit_group_scope', $local_reset_data ) && !sek_booleanize_checkbox_val($local_reset_data['inherit_group_scope'] ) ) {
        return true;
    }
    return false;
}


/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES CSS
/* ------------------------------------------------------------------------- */
// filter declared in inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
//@hook 'nb_set_skope_id_before_generating_local_front_css'
function sek_set_skope_id_before_generating_local_front_css($skope_id) {
    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return $skope_id;
    }
    // if is viewing front page, we don't want to inherit 'skp__all_page' scope
    if ( sek_is_static_front_page_on_front_and_when_customizing() )
        return $skope_id;

    // checkbox 'inherit_group_scope' set to true by default
    if ( sek_is_inheritance_locally_disabled() ) {
        return $skope_id;
    }

    // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
    // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
    // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
    // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
    if ( !sek_local_skope_has_been_customized( $skope_id ) ) {
        $group_site_tmpl_data = sek_get_group_site_template_data();//<= is cached when called
        $has_group_skope_template_data = !( !$group_site_tmpl_data || empty($group_site_tmpl_data) );
        if ( $has_group_skope_template_data ) {
            $group_skope = sek_get_group_skope_for_site_tmpl();
            if ( !empty($group_skope) && '_skope_not_set_' !== $group_skope ) {
                $skope_id = $group_skope;
            }
        }
    }
    return $skope_id;
}
add_filter( 'nb_set_skope_id_before_generating_local_front_css', '\Nimble\sek_set_skope_id_before_generating_local_front_css');



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES CONTENT
/* ------------------------------------------------------------------------- */
// Called in sek_get_skoped_seks()
function sek_maybe_get_seks_for_group_site_template( $skope_id, $local_seks_data ) {
    // NB will only inherit group skope for local sektions
    if ( NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) {
        sek_error_log( __FUNCTION__ . ' => error => function should not be used with global skope id' );
        return $local_seks_data;
    }
    // if is viewing front page, we don't want to inherit 'skp__all_page' scope
    if ( sek_is_static_front_page_on_front_and_when_customizing() )
        return $local_seks_data;

    // checkbox 'inherit_group_scope' set to true by default
    if ( sek_is_inheritance_locally_disabled() ) {
        return $local_seks_data;
    }
    // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
    // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
    // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
    // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
    if ( sek_local_skope_has_been_customized($skope_id, $local_seks_data) )  {
        return $local_seks_data;
    }

    $group_site_tmpl_data = sek_get_group_site_template_data();

    if ( !$group_site_tmpl_data || empty($group_site_tmpl_data) )
        return $local_seks_data;

    return $group_site_tmpl_data;      
}

// @return null || array
// get and cache the group site template data
function sek_get_group_site_template_data( $group_skope = null ) {
    // When ajaxing while customizing, no need to get the group site template data
    if ( skp_is_customizing() && defined( 'DOING_AJAX' ) && DOING_AJAX )
        return;
    $group_site_tmpl_data = [];
    if ( is_null( $group_skope ) ) {
        $group_skope = sek_get_group_skope_for_site_tmpl();
    }
    $cache_key = 'nimble_group_site_template_data_' . $group_skope;
    $cached = wp_cache_get( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }
    // Site template params are structured as follow :
    // [
    //     'site_tmpl_id' : '_no_site_tmpl_',
    //     'site_tmpl_source' : 'user_tmpl',
    //     'site_tmpl_title' : ''
    //];
    $tmpl_params = sek_get_site_tmpl_params_for_skope( $group_skope );
    if ( '_no_site_tmpl_' === $tmpl_params['site_tmpl_id'] ) {
        wp_cache_set( $cache_key, $group_site_tmpl_data );
        return;
    }

    $site_tmpl_id = $tmpl_params['site_tmpl_id'];
    $site_tmpl_source = $tmpl_params['site_tmpl_source'];

    // Is this group template already saved ?
    // For example, for pages, there should be a nimble CPT post named nimble___skp__all_page
    $post = sek_get_seks_post( $group_skope );

    // if not, let's insert it
    if ( !$post ) {
        $current_tmpl_post = null;
        $current_tmpl_data = null;
        switch ($site_tmpl_source) {
            case 'user_tmpl':
                $current_tmpl_post = sek_get_saved_tmpl_post( $site_tmpl_id );
                if ( $current_tmpl_post ) {
                    $raw_tmpl_data = maybe_unserialize( $current_tmpl_post->post_content );
                    if ( is_array($raw_tmpl_data) && isset($raw_tmpl_data['data']) && is_array($raw_tmpl_data['data']) && !empty($raw_tmpl_data['data']) ) {
                        $current_tmpl_data = $raw_tmpl_data['data'];
                        $current_tmpl_data = sek_set_ids( $current_tmpl_data );
                    }
                }
            break;

            case 'api_tmpl':
                $raw_tmpl_data = sek_get_single_tmpl_api_data( $site_tmpl_id );
                if( !is_array( $raw_tmpl_data) || empty( $raw_tmpl_data ) ) {
                    sek_error_log( ' problem when getting template : ' . $site_tmpl_id );
                }
                //sek_error_log( __FUNCTION__ . ' api template collection', $raw_tmpl_data );
                if ( !isset($raw_tmpl_data['data'] ) || empty( $raw_tmpl_data['data'] ) ) {
                    sek_error_log( __FUNCTION__ . ' problem => missing or invalid data property for template : ' .$site_tmpl_id, $raw_tmpl_data );
                } else {
                    // $tmpl_decoded = $raw_tmpl_data;
                    $raw_tmpl_data['data'] = sek_maybe_import_imgs( $raw_tmpl_data['data'], $do_import_images = true );
                    //$raw_tmpl_data['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
                    $current_tmpl_data = sek_set_ids( $raw_tmpl_data['data'] );
                }
            break;
        }

        if( !is_null($current_tmpl_data) ) {
            //sek_error_log('SITE TEMPLATE => UPDATE OR INSERT GROUP SKOPE POST => ' .$group_skope );
            $post = sek_update_sek_post( $current_tmpl_data, [ 'skope_id' => $group_skope ]);
        }
    }//if ( !$post ) {

    if ( $post ) {
        $group_site_tmpl_data = maybe_unserialize( $post->post_content );
    }
    wp_cache_set( $cache_key, $group_site_tmpl_data );
    return $group_site_tmpl_data;
}


// @return bool
function sek_has_group_site_template_data() {
    $group_skope = sek_get_group_skope_for_site_tmpl();
    $cache_key = 'nimble_has_group_site_template_' . $group_skope;
    $cached = wp_cache_get($cache_key);
    if (  'yes' === $cached || 'no' === $cached ) {
        return 'yes' === $cached;
    }
    
    $group_site_tmpl_data = sek_get_group_site_template_data();//<= is cached when called
    $has_group_skope_template_data = !( !$group_site_tmpl_data || empty($group_site_tmpl_data) );
    wp_cache_set( $cache_key, $has_group_skope_template_data  ? 'yes' : 'no' );
    return $has_group_skope_template_data;
}


/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES SAVE CUSTOMIZER ACTION
/* ------------------------------------------------------------------------- */
// Action declared in class Nimble_Options_Setting
// When a site template is modified, the following action removes the skoped post + removes the corresponding CSS stylesheet
// For example, when the page site template is changed, we need to remove the associated skoped post named 'nimble___skp__all_page'
// This post has been inserted when running sek_maybe_get_seks_for_group_site_template(), fired from sek_get_skoped_seks()
//@'nb_on_save_customizer_global_options'
function sek_on_save_customizer_global_options( $opt_name, $value ) {
    $current_site_tmpl_opts = sek_get_global_option_value( 'site_templates' );
    if ( !is_array( $value ) || !is_array($current_site_tmpl_opts) )
        return;
    
    // NB stores the site template id as a concatenation of template source + '___' + template name
    // Ex : user_tmpl___landing-page-for-services
    $updated_site_templates = isset($value['site_templates']) ? $value['site_templates'] : [];

    foreach( $current_site_tmpl_opts as $group_skope => $current_tmpl_params ) {
        if ( !is_array($current_tmpl_params) || !array_key_exists('site_tmpl_id', $current_tmpl_params ) )
            continue;
        $current_tmpl_id = $current_tmpl_params['site_tmpl_id'];
        $updated_tmpl_params = [];
        if ( array_key_exists( $group_skope, $updated_site_templates ) ) {
            $updated_tmpl_params = $updated_site_templates[$group_skope];
        } else {
            //sek_error_log('GROUP SKOPE POST TO REMOVE BECAUSE NO MORE TEMPLATE SET => ' . $group_skope . ' | ' . $current_tmpl_id );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
        if ( is_array($updated_tmpl_params) && array_key_exists('site_tmpl_id', $updated_tmpl_params ) && $updated_tmpl_params['site_tmpl_id'] != $current_tmpl_params['site_tmpl_id'] ) {
            //sek_error_log('GROUP SKOPE POST TO REMOVE BECAUSE TEMPLATE UPDATED => ' . $group_skope . ' | ' . $updated_site_templates[$group_skope] );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
    }
}
add_action('nb_on_save_customizer_global_options', '\Nimble\sek_on_save_customizer_global_options', 10, 2);



/* ------------------------------------------------------------------------- *
 *  SITE TEMPLATES : UPDATED TEMPLATE IN CUSTOMIZER
/* ------------------------------------------------------------------------- */
// Action fired during server ajax callback sek_update_user_tmpl_post
// Solves the problem of template synchronization between the group skope post ( in which the chosen template is saved with permanent level ids ), and the current state of the template
// Solution => each time a template is updated, NB checks if the template is being used by a group skope
// if so, then the group skope post is removed ( along with the index and the css stylesheet )
// 
// When will the removed skope post be re-inserted ?
// next time the group skope will be printed ( for example skp__all_page in a single page ), NB checks if a template is assigned to this group skope, and tries to get the skope post.
// If the group skope post is not found, NB attempts to re-insert it
//@hook 'nb_on_update_user_tmpl_post'
function sek_on_update_or_remove_user_tmpl_post( $site_tmpl_id ) {
    if ( is_null( $site_tmpl_id ) || !is_string( $site_tmpl_id ) )
        return;

    $site_tmpl_opts = sek_get_global_option_value( 'site_templates' );
    if ( !is_array($site_tmpl_opts) )
        return;

    foreach( $site_tmpl_opts as $group_skope => $tmpl_params ) {
        if ( !is_array($tmpl_params) || !array_key_exists('site_tmpl_id', $tmpl_params ) )
            continue;

        if ( $site_tmpl_id === $tmpl_params['site_tmpl_id'] ) {
            //sek_error_log('UPDATED OR REMOVED TEMPLATE => REMOVE GROUP SKOPE POST ' . $group_skope . ' for template ' . $tmpl_name );
            sek_remove_seks_post( $group_skope );//Removes the post id in the skope index + removes the post in DB + remove the stylesheet
        }
    }
}
add_action('nb_on_update_user_tmpl_post', '\Nimble\sek_on_update_or_remove_user_tmpl_post', 10, 1);
add_action('nb_on_remove_saved_tmpl_post', '\Nimble\sek_on_update_or_remove_user_tmpl_post', 10, 1);

?>