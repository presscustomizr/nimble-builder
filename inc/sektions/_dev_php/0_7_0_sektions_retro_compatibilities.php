<?php
// fired @wp_loaded
// Note : if fired @plugins_loaded, invoking wp_update_post() generates php notices
function sek_maybe_do_version_mapping() {
    if ( ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) )
      return;
    //delete_option(NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS);
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    $global_options = is_array( $global_options ) ? $global_options : array();
    $global_options['retro_compat_mappings'] = isset( $global_options['retro_compat_mappings'] ) ? $global_options['retro_compat_mappings'] : array();

    if ( ! array_key_exists( '1_0_4_to_1_1_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] ) {
        $status_1_0_4_to_1_1_0 = sek_do_compat_1_0_4_to_1_1_0();
        //sek_error_log('$status_1_0_4_to_1_1_0 ' . $status_1_0_4_to_1_1_0, $global_options );
        $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] = 'done';
    }
    update_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS, $global_options );
}


////////////////////////////////////////////////////////////////
// RETRO COMPAT 1.0.4 to 1.1.0
// Introduced when upgrading from version 1.0.4 to version 1.1 +. October 2018.
// 1) Retro compat for image and tinymce module, turned multidimensional ( father - child logic ) since 1.1+
// 2) Ensure each level has a "ver_ini" property set to 1.0.4
function sek_do_compat_1_0_4_to_1_1_0() {
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title( NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $skope_id ),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( ! is_array( $query->posts ) || empty( $query->posts ) )
      return;

    $status = 'success';
    foreach ($query->posts as $post_object ) {
        if ( $post_object ) {
            $seks_data = maybe_unserialize( $post_object->post_content );
        }

        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        if ( empty( $seks_data ) )
          continue;
        $seks_data = sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $seks_data );
        $new_post_data = array(
            'ID'          => $post_object->ID,
            'post_title'  => $post_object->post_title,
            'post_name'   => sanitize_title( $post_object->post_title ),
            'post_type'   => NIMBLE_CPT,
            'post_status' => 'publish',
            'post_content' => maybe_serialize( $seks_data )
        );
        //sek_error_log('$new_post_data ??', $seks_data );
        $r = wp_update_post( wp_slash( $new_post_data ), true );
        if ( is_wp_error( $r ) ) {
            $status = 'error';
            sek_error_log( __FUNCTION__ . ' => error', $r );
        }
    }//foreach
    return $status;
}



// Recursive helper
// Sniff the modules that need a compatibility mapping
// do the mapping
// @return an updated sektions collection
function sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $seks_data ) {
    $new_seks_data = array();
    foreach ( $seks_data as $key => $value ) {
        // Set level ver_ini
        // If the ver_ini property is not set, it means the level has been created with the previous version of Nimble ( v1.4.0 )
        if ( is_array($value) && array_key_exists('level', $value) && ! array_key_exists('ver_ini', $value) ) {
            $value['ver_ini'] = '1.0.4';
        }
        $new_seks_data[$key] = $value;
        // Level options mapping
        // remove spacing
        // remove layout
        // copy all background related options ( bg-* ) from "bg_border" to "bg"
        // options => array(
        //    spacing => array(),
        //    height => array(),
        //    bg_border => array()
        // )
        if ( ! empty( $value ) && is_array( $value ) && 'options' === $key ) {
            // bail if the mapping has already been done
            if ( array_key_exists( 'bg', $value ) )
              continue;
            $new_seks_data[$key] = array();
            foreach( $value as $_opt_group => $_opt_group_data ) {
                if ( 'spacing' === $_opt_group || 'layout' === $_opt_group )
                  continue;
                if ( 'bg_border' === $_opt_group ) {
                    foreach ( $_opt_group_data as $input_id => $val ) {
                        if ( false !== strpos( $input_id , 'bg-' ) ) {
                            $new_seks_data[$key]['bg'][$input_id] = $val;
                        }
                    }
                }
            }
        } // module mapping
        else if ( is_array( $value ) && array_key_exists('module_type', $value ) ) {
            $new_seks_data[$key] = $value;
            // Assign a default value to the new_value in case we have no matching case
            $new_value = $value['value'];

            switch ( $value['module_type'] ) {
                case 'czr_image_module':
                    if ( is_array( $value['value'] ) ) {
                        // make sure we don't map twice
                        if ( array_key_exists( 'main_settings', $value['value'] ) || array_key_exists( 'borders_corners', $value['value'] ) )
                          break;
                        $new_value = array( 'main_settings' => array(), 'borders_corners' => array() );
                        foreach ( $value['value'] as $input_id => $input_data ) {
                            // make sure we don't map twice
                            if ( in_array( $input_id, array( 'main_settings', 'borders_corners' ) ) )
                              break;
                            switch ($input_id) {
                                case 'border-type':
                                case 'borders':
                                case 'border_radius_css':
                                    $new_value['borders_corners'][$input_id] = $input_data;
                                break;

                                default:
                                    $new_value['main_settings'][$input_id] = $input_data;
                                break;
                            }
                        }
                    }
                break;

                case 'czr_tiny_mce_editor_module':
                    if ( is_array( $value['value'] ) ) {
                        // make sure we don't map twice
                        if ( array_key_exists( 'main_settings', $value['value'] ) || array_key_exists( 'font_settings', $value['value'] ) )
                          break;
                        $new_value = array( 'main_settings' => array(), 'font_settings' => array() );
                        foreach ( $value['value'] as $input_id => $input_data ) {
                            // make sure we don't map twice
                            if ( in_array( $input_id, array( 'main_settings', 'font_settings' ) ) )
                              break;
                            switch ($input_id) {
                                case 'content':
                                case 'h_alignment_css':
                                    $new_value['main_settings'][$input_id] = $input_data;
                                break;

                                default:
                                    $new_value['font_settings'][$input_id] = $input_data;
                                break;
                            }
                        }
                    }
                break;
                default :
                    $new_value = $value['value'];
                break;
            }

            $new_seks_data[$key]['value'] = $new_value;
        } else if ( is_array($value) ) {
            // go recursive
            $new_seks_data[$key] = sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $value );
        }
    }
    return $new_seks_data;
}
?>