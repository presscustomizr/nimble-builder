<?php

/* ------------------------------------------------------------------------- *
 *  MODULES COLLECTION
/* ------------------------------------------------------------------------- */
// introduced when implementing the level tree #359
function sek_get_module_collection() {
    return apply_filters( 'sek_get_module_collection', array(
        array(
          'content-type' => 'preset_section',
          'content-id' => 'two_columns',
          'title' => __( 'Two Columns', 'text_doma' ),
          'icon' => 'Nimble_2-columns_icon.svg'
        ),
        array(
          'content-type' => 'preset_section',
          'content-id' => 'three_columns',
          'title' => __( 'Three Columns', 'text_doma' ),
          'icon' => 'Nimble_3-columns_icon.svg'
        ),
        array(
          'content-type' => 'preset_section',
          'content-id' => 'four_columns',
          'title' => __( 'Four Columns', 'text_doma' ),
          'icon' => 'Nimble_4-columns_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_tiny_mce_editor_module',
          'title' => __( 'WordPress Editor', 'text_doma' ),
          'icon' => 'Nimble_rich-text-editor_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_image_module',
          'title' => __( 'Image', 'text_doma' ),
          'icon' => 'Nimble__image_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_heading_module',
          'title' => __( 'Heading', 'text_doma' ),
          'icon' => 'Nimble__heading_icon.svg'
        ),

        array(
          'content-type' => 'module',
          'content-id' => 'czr_icon_module',
          'title' => __( 'Icon', 'text_doma' ),
          'icon' => 'Nimble__icon_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_button_module',
          'title' => __( 'Button', 'text_doma' ),
          'icon' => 'Nimble_button_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_img_slider_module',
          'title' => __( 'Image & Text Carousel', 'text_doma' ),
          'icon' => 'Nimble_slideshow_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_accordion_module',
          'title' => __( 'Accordion', 'text_doma' ),
          'icon' => 'Nimble_accordion_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_simple_html_module',
          'title' => __( 'Html Content', 'text_doma' ),
          'icon' => 'Nimble_html_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_post_grid_module',
          'title' => __( 'Post Grid', 'text_doma' ),
          'icon' => 'Nimble_posts-list_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_quote_module',
          'title' => __( 'Quote', 'text_doma' ),
          'icon' => 'Nimble_quote_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_shortcode_module',
          'title' => __( 'Shortcode', 'text_doma' ),
          'icon' => 'Nimble_shortcode_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_spacer_module',
          'title' => __( 'Spacer', 'text_doma' ),
          'icon' => 'Nimble__spacer_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_divider_module',
          'title' => __( 'Divider', 'text_doma' ),
          'icon' => 'Nimble__divider_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_map_module',
          'title' => __( 'Map', 'text_doma' ),
          'icon' => 'Nimble_map_icon.svg'
        ),

        array(
          'content-type' => 'module',
          'content-id' => 'czr_widget_area_module',
          'title' => __( 'WordPress widget area', 'text_doma' ),
          'font_icon' => '<i class="fab fa-wordpress-simple"></i>'
          //'active' => sek_are_beta_features_enabled()
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_social_icons_module',
          'title' => __( 'Social Profiles', 'text_doma' ),
          'icon' => 'Nimble_social_icon.svg'
        ),
        array(
          'content-type' => 'module',
          'content-id' => 'czr_simple_form_module',
          'title' => __( 'Simple Contact Form', 'text_doma' ),
          'icon' => 'Nimble_contact-form_icon.svg'
        ),

        array(
          'content-type' => 'module',
          'content-id' => 'czr_menu_module',
          'title' => __( 'Menu', 'text_doma' ),
          'font_icon' => '<i class="material-icons">menu</i>'
          //'active' => sek_are_beta_features_enabled()
        )
        // array(
        //   'content-type' => 'module',
        //   'content-id' => 'czr_special_img_module',
        //   'title' => __( 'Nimble Image', 'text_doma' ),
        //   'font_icon' => '<i class="material-icons">all_out</i>',
        //   'active' => sek_is_pro()
        // )
        // array(
        //   'content-type' => 'module',
        //   'content-id' => 'czr_featured_pages_module',
        //   'title' => __( 'Featured pages',  'text_doma' ),
        //   'icon' => 'Nimble__featured_icon.svg'
        // ),


    ));
}


// recursive helper to generate a list of module used in a given set of sections data
function sek_populate_list_of_modules_used( $seks_data ) {
    global $modules_used;
    if ( ! is_array( $seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid seks_data param');
        return $count;
    }
    foreach ( $seks_data as $key => $data ) {
        if ( is_array( $data ) ) {
            if ( !empty( $data['level'] ) && 'module' === $data['level'] && !empty( $data['module_type'] ) ) {
                $modules_used[] = $data['module_type'];
            } else {
                //$modules_used = array_merge( $modules_used, sek_populate_list_of_modules_used( $data, $modules_used ) );
                sek_populate_list_of_modules_used( $data, $modules_used );
            }
        }
    }
}


// @return void()
// Fired in 'wp_enqueue_scripts'
// Recursively sniff the local and global sections to populate Nimble_Manager()->modules_currently_displayed
// introduced for https://github.com/presscustomizr/nimble-builder/issues/612
function sek_populate_collection_of_module_displayed( $recursive_data = null, $module_collection = null ) {
    if ( is_null( $recursive_data ) ) {
        $local_skope_settings = sek_get_skoped_seks( skp_get_skope_id() );
        $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
        $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
        $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

        $recursive_data = array_merge( $local_collection, $global_collection );
    }
    if ( is_null( $module_collection ) ) {
        $module_collection = Nimble_Manager()->modules_currently_displayed;
    }

    foreach ($recursive_data as $key => $value) {
        if ( is_array( $value ) && array_key_exists('module_type', $value) ) {
            $module_type = $value['module_type'];
            if ( !array_key_exists($module_type, $module_collection) ) {
                $module_collection[$module_type] = [];
            }
            if ( !in_array( $value['id'], $module_collection[$module_type] ) ) {
                $module_collection[$module_type][] = $value['id'];
            }
        } else if ( is_array( $value ) ) {
            $module_collection = sek_populate_collection_of_module_displayed( $value, $module_collection );
        }
    }
    Nimble_Manager()->modules_currently_displayed = $module_collection;
    return Nimble_Manager()->modules_currently_displayed;
}

// return the cached collection or build it when needed
function sek_get_collection_of_module_displayed( $recursive_data = null, $module_collection = null ) {
    if ( empty( Nimble_Manager()->modules_currently_displayed ) )
      return sek_populate_collection_of_module_displayed();
    return Nimble_Manager()->modules_currently_displayed;
}

?>