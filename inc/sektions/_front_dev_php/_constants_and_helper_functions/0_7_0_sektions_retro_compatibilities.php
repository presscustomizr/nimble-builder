<?php
// introduced for #799
function sek_maybe_optimize_options() {
    $bw_fixes_options = get_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES );
    $bw_fixes_options = is_array( $bw_fixes_options ) ? $bw_fixes_options : array();

    // March 13th 2021 => removed previous option used to store api post news, now handled with a transient.
    if ( !array_key_exists('optimize_opts_0321_2', $bw_fixes_options ) || 'done' != $bw_fixes_options['optimize_opts_0321_2'] ) {
        delete_option( 'nimble_api_news_data' );

        // flag as done
        $bw_fixes_options['optimize_opts_0321_2'] = 'done';
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }


    if ( !array_key_exists('optimize_opts_0321', $bw_fixes_options ) || 'done' != $bw_fixes_options['optimize_opts_0321'] ) {
        $current_global_opts = get_option('__nimble_options__');
        if ( false !== $current_global_opts ) {
            update_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS, $current_global_opts, 'no' );
            delete_option( '__nimble_options__' );
        }
        // delete previous option for prebuild section json
        // => the option will be re-created with autoload set to "no" and renamed "nimble_prebuild_sections"
        delete_option( 'nb_prebuild_section_json' );

        // flag as done
        $bw_fixes_options['optimize_opts_0321'] = 'done';
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }



    // If the move in post index has been done, let's update to autoload = false the previous post_id options LIKE nimble___skp__post_page_*****, nimble___skp__tax_product_cat_*****
    // As of March 2021, event if those previous options are not used anymore, let's keep them in DB to cover potential retro-compat problems
    // in a future release, if no regression was reported, we'll remove them forever.
    if ( array_key_exists('move_in_post_index_0321', $bw_fixes_options ) && 'done' === $bw_fixes_options['move_in_post_index_0321'] ) {
        if ( !array_key_exists('fix_skope_opt_autoload_0321', $bw_fixes_options ) || 'done' != $bw_fixes_options['fix_skope_opt_autoload_0321'] ) {
            // MOVE ALL OPTIONS LIKE nimble___skp__post_page_*****, nimble___skp__tax_product_cat_***** in a new option ( NIMBLE_OPT_SEKTION_POST_INDEX ), not autoloaded
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE autoload = 'yes' and option_name like 'nimble___skp_%'", ARRAY_A );
            if ( is_array( $results ) ) {
                foreach( $results as $old_opt_data ) {
                    if ( !is_array($old_opt_data) )
                        continue;
                    if ( empty($old_opt_data['option_name']) || empty($old_opt_data['option_value']) )
                        continue;
                    // update it with autoload set to "no"
                    update_option( $old_opt_data['option_name'], (int)$old_opt_data['option_value'], 'no' );
                }
            }

            // flag as done
            $bw_fixes_options['fix_skope_opt_autoload_0321'] = 'done';
            update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
        }
    }


    if ( !array_key_exists('move_in_post_index_0321', $bw_fixes_options ) || 'done' != $bw_fixes_options['move_in_post_index_0321'] ) {
        // MOVE ALL OPTIONS LIKE nimble___skp__post_page_*****, nimble___skp__tax_product_cat_***** in a new option ( NIMBLE_OPT_SEKTION_POST_INDEX ), not autoloaded
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE autoload = 'yes' and option_name like 'nimble___skp_%'", ARRAY_A );
        if ( is_array( $results ) ) {
            // Populate the new option ( it should not exists at this point )
            $nb_posts_index = get_option(NIMBLE_OPT_SEKTION_POST_INDEX);
            $nb_posts_index = is_array($nb_posts_index) ? $nb_posts_index : [];
            foreach( $results as $old_opt_data ) {
                if ( !is_array($old_opt_data) )
                    continue;
                if ( empty($old_opt_data['option_name']) || empty($old_opt_data['option_value']) )
                    continue;
                
                $nb_posts_index[ $old_opt_data['option_name'] ] = (int)$old_opt_data['option_value'];
            }
            // update it with autoload set to "no"
            update_option( NIMBLE_OPT_SEKTION_POST_INDEX, $nb_posts_index, 'no');
        }

        // flag as done
        $bw_fixes_options['move_in_post_index_0321'] = 'done';
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }
}


// JULY 2020 => NOT FIRED ANYMORE ( because introduced in oct 2018 ) => DEACTIVATED IN nimble-builder.php
// fired @wp_loaded
// Note : if fired @plugins_loaded, invoking wp_update_post() generates php notices
function sek_maybe_do_version_mapping() {
    // if ( !is_user_logged_in() || !current_user_can( 'customize' ) )
    //   return;
    // //delete_option(NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS);
    // $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    // $global_options = is_array( $global_options ) ? $global_options : array();
    // $global_options['retro_compat_mappings'] = isset( $global_options['retro_compat_mappings'] ) ? $global_options['retro_compat_mappings'] : array();

    // // To 1_0_4 was introduced in december 2018
    // // It's related to a modification of the skope_id when home is a static page
    // if ( !array_key_exists( 'to_1_4_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['to_1_4_0'] ) {
    //     $status_to_1_4_0 = sek_do_compat_to_1_4_0();
    //     //sek_error_log('$status_1_0_4_to_1_1_0 ' . $status_1_0_4_to_1_1_0, $global_options );
    //     $global_options['retro_compat_mappings']['to_1_4_0'] = 'done';
    // }

    // // 1_0_4_to_1_1_0 introduced in October 2018
    // if ( !array_key_exists( '1_0_4_to_1_1_0', $global_options['retro_compat_mappings'] ) || 'done' != $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] ) {
    //     $status_1_0_4_to_1_1_0 = sek_do_compat_1_0_4_to_1_1_0();
    //     //sek_error_log('$status_1_0_4_to_1_1_0 ' . $status_1_0_4_to_1_1_0, $global_options );
    //     $global_options['retro_compat_mappings']['1_0_4_to_1_1_0'] = 'done';
    // }
    // update_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS, $global_options );
}

////////////////////////////////////////////////////////////////
// RETRO COMPAT => to 1.4.0
// It's related to a modification of the skope_id when home is a static page
// Was skp__post_page_home
// Now is skp__post_page_{$static_home_page_id}
// This was introduced to facilitate the compatibility of Nimble Builder with multilanguage plugins like polylang
// => Allows user to create a different home page for each languages
//
// If the current home page is not a static page, we don't have to do anything
// If not, the sections currently saved for skope skp__post_page_home, must be moved to skope skp__post_page_{$static_home_page_id}
// => this means that we need to update the post_id saved for option NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . 'skp__post_page_{$static_home_page_id}';
// to the value of the one saved for option NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . 'skp__post_page_home';
function sek_do_compat_to_1_4_0() {
    if ( 'page' === get_option( 'show_on_front' ) ) {
        $home_page_id = (int)get_option( 'page_on_front' );
        if ( 0 < $home_page_id ) {
            // get the post id storing the current sections on home
            // @see sek_get_seks_post()
            $current_option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . 'skp__post_page_home';
            $post_id_storing_home_page_sections = (int)get_option( $current_option_name );
            if ( $post_id_storing_home_page_sections > 0 ) {
                $new_option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . "skp__post_page_{$home_page_id}";
                update_option( $new_option_name, $post_id_storing_home_page_sections, 'no' );
            }
        }
    }
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
        //'name'                   => sanitize_title(),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( !is_array( $query->posts ) || empty( $query->posts ) )
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
        // If the ver_ini property is not set, it means the level has been created with the previous version of Nimble ( v1.0.4 )
        // Let's add it
        if ( is_array($value) && array_key_exists('level', $value) && !array_key_exists('ver_ini', $value) ) {
            $value['ver_ini'] = '1.0.4';
        }
        $new_seks_data[$key] = $value;
        // LEVEL OPTIONS mapping
        // remove spacing
        // remove layout
        // copy all background related options ( bg-* ) from "bg_border" to "bg"
        // options => array(
        //    spacing => array(),
        //    height => array(),
        //    bg_border => array()
        // )
        if ( !empty( $value ) && is_array( $value ) && 'options' === $key ) {
            // bail if the mapping has already been done
            if ( array_key_exists( 'bg', $value ) )
              continue;
            $new_seks_data[$key] = array();
            foreach( $value as $_opt_group => $_opt_group_data ) {
                if ( 'layout' === $_opt_group )
                  continue;
                if ( 'bg_border' === $_opt_group ) {
                    foreach ( $_opt_group_data as $input_id => $val ) {
                        if ( false !== strpos( $input_id , 'bg-' ) ) {
                            $new_seks_data[$key]['bg'][$input_id] = $val;
                        }
                    }
                }
                if ( 'spacing' === $_opt_group ) {
                    $new_seks_data[$key]['spacing'] = array( 'pad_marg' => sek_map_compat_1_0_4_to_1_1_0_do_level_spacing_mapping( $_opt_group_data ) );
                }
            }
        } // end of Level mapping
        // MODULE mapping
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
        } // End of module mapping
        // go recursive if possible
        else if ( is_array($value) ) {
            $new_seks_data[$key] = sek_walk_levels_and_do_map_compat_1_0_4_to_1_1_0( $value );
        }
    }
    return $new_seks_data;
}

// mapping from
// [spacing] => Array
// (
//     [desktop_pad_marg] => Array
//         (
//             [padding-top] => 20
//             [padding-bottom] => 20
//         )

//     [desktop_unit] => em
//     [tablet_pad_marg] => Array
//         (
//             [padding-left] => 30
//             [padding-right] => 30
//         )

//     [tablet_unit] => percent
// )
// to
// [spacing] => Array
// (
//     [pad_marg] => Array
//         (
//             [desktop] => Array
//                 (
//                     [padding-top] => 20
//                     [padding-bottom] => 20
//                     [unit] => em
//                 )

//             [tablet] => Array
//                 (
//                     [padding-right] => 30
//                     [padding-left] => 30
//                     [unit] => %
//                 )

//         )

// )
function sek_map_compat_1_0_4_to_1_1_0_do_level_spacing_mapping( $old_user_data ) {
    $old_data_structure = array(
        'desktop_pad_marg',
        'desktop_unit',
        'tablet_pad_marg',
        'tablet_unit',
        'mobile_pad_marg',
        'mobile_unit'
    );
    //sek_error_log('$old_user_data', $old_user_data);
    $mapped_data = array();
    foreach ( $old_data_structure as $old_key ) {
        if ( false !== strpos( $old_key , 'pad_marg' ) ) {
            $device = str_replace('_pad_marg', '', $old_key );
            if ( array_key_exists( $old_key, $old_user_data ) ) {
                $mapped_data[$device] = $old_user_data[$old_key];
            }
        }
        if ( false !== strpos( $old_key , 'unit' ) ) {
            $device = str_replace('_unit', '', $old_key );
            if ( array_key_exists( $old_key, $old_user_data ) ) {
                $mapped_data[$device] = is_array( $mapped_data[$device] ) ? $mapped_data[$device] : array();
                $mapped_data[$device]['unit'] = 'percent' === $old_user_data[$old_key] ? '%' : $old_user_data[$old_key];
            }
        }
    }
    return $mapped_data;
}
?>