<?php
// This file has been introduced on May 21st 2019 => back to the local data
// after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445

/////////////////////////////////////////////////////////////
// REGISTRATION PARAMS FOR PRESET SECTIONS
// @return array()
function sek_get_sections_registration_params( $force_update = false ) {

    // JULY 2020 => not stored in a transient anymore. For https://github.com/presscustomizr/nimble-builder/issues/730
    // + clean previously created transients
    $bw_fixes_options = get_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES );
    $bw_fixes_options = is_array( $bw_fixes_options ) ? $bw_fixes_options : array();
    if ( !array_key_exists('clean_section_params_transient_0720', $bw_fixes_options ) || 'done' != $bw_fixes_options['clean_section_params_transient_0720'] ) {
        sek_clean_transients_like( 'section_params_transient' );
        $bw_fixes_options['clean_section_params_transient_0720'] = 'done';
        // flag as done
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }

    // $section_params_transient_name = 'section_params_transient_' . NIMBLE_VERSION;
    // $registration_params = get_transient( $section_params_transient_name );
    // // Refresh every 30 days, unless force_update set to true
    // if ( $force_update || false === $registration_params ) {
    //     $registration_params = sek_get_raw_section_registration_params();
    //     set_transient( $section_params_transient_name, $registration_params, 30 * DAY_IN_SECONDS );
    // }

    $registration_params = sek_get_raw_section_registration_params();
    return $registration_params;
}

function sek_get_raw_section_registration_params() {
    return apply_filters( 'sek_get_raw_section_registration_params', [
        'sek_intro_sec_picker_module' => [
            'module_title' => __('Sections for an introduction', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'intro_three',
                    'title' => __('1 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_three.jpg',
                    'demo_url' => 'https://nimblebuilder.com/landing-page-one/#contact'
                ),
                array(
                    'content-id' => 'intro_one',
                    'title' => __('1 column, full-width background', 'text-domain' ),
                    'thumb' => 'intro_one.jpg'
                ),
                array(
                    'content-id' => 'intro_two',
                    'title' => __('2 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_two.jpg'
                ),
                array(
                    'content-id' => 'pro_intro_one',
                    'title' => __('2 columns, call to actions, image carousel', 'text-domain' ),
                    'thumb' => 'pro_intro_one.jpg',
                    'active' => sek_is_pro(),
                    'is_pro' => true
                )
            )
        ],
        'sek_features_sec_picker_module' => [
            'module_title' => __('Sections for services and features', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'features_one',
                    'title' => __('3 columns with icon and call to action', 'text-domain' ),
                    'thumb' => 'features_one.jpg',
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'features_two',
                    'title' => __('3 columns with icon', 'text-domain' ),
                    'thumb' => 'features_two.jpg',
                    //'height' => '188px'
                )
            )
        ],
        'sek_about_sec_picker_module' => [
            'module_title' => __('Contact-us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'about_one',
                    'title' => __('A simple about us section with 2 columns', 'text-domain' ),
                    'thumb' => 'about_one.jpg',
                    //'height' => '188px'
                )
            )
        ],
        'sek_contact_sec_picker_module' => [
            'module_title' => __('Contact-us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'contact_one',
                    'title' => __('A contact form and a Google map', 'text-domain' ),
                    'thumb' => 'contact_one.jpg',
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'contact_two',
                    'title' => __('A contact form with an image background', 'text-domain' ),
                    'thumb' => 'contact_two.jpg',
                    //'height' => '188px'
                )
            )
        ],
        'sek_team_sec_picker_module' => [
            'module_title' => __('Sections for teams', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'team_one',
                    'title' => __('4 column', 'text-domain' ),
                    'thumb' => 'team_one.jpg'
                ),
                array(
                    'content-id' => 'team_two',
                    'title' => __('3 columns', 'text-domain' ),
                    'thumb' => 'team_two.jpg',
                    'height' => '180px'
                ),
                array(
                    'content-id' => 'pro_team_one',
                    'title' => __('3 columns, call to action', 'text-domain' ),
                    'thumb' => 'pro_team_one.jpg',
                    'active' => sek_is_pro(),
                    'height' => '180px',
                    'is_pro' => true
                )
            )
        ],
        'sek_column_layouts_sec_picker_module' => [
            'module_title' => __('Empty sections with columns layout', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'two_columns',
                    'title' => __('two columns layout', 'text-domain' ),
                    'thumb' => 'two_columns.jpg'
                ),
                array(
                    'content-id' => 'three_columns',
                    'title' => __('three columns layout', 'text-domain' ),
                    'thumb' => 'three_columns.jpg'
                ),
                array(
                    'content-id' => 'four_columns',
                    'title' => __('four columns layout', 'text-domain' ),
                    'thumb' => 'four_columns.jpg'
                ),
            )
        ],
        // pre-built sections for header and footer
        'sek_header_sec_picker_module' => [
            'module_title' => __('Header sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'header_one',
                    'title' => __('simple header with a logo on the left and a menu on the right', 'text-domain' ),
                    'thumb' => 'header_one.jpg',
                    'height' => '33px',
                    'section_type' => 'header'
                ),
                array(
                    'content-id' => 'header_two',
                    'title' => __('simple header with a logo on the right and a menu on the left', 'text-domain' ),
                    'thumb' => 'header_two.jpg',
                    'height' => '33px',
                    'section_type' => 'header'
                )
            )
        ],
        'sek_footer_sec_picker_module' => [
            'module_title' => __('Footer sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'footer_one',
                    'title' => __('simple footer with 3 columns and large bottom zone', 'text-domain' ),
                    'thumb' => 'footer_one.jpg',
                    'section_type' => 'footer'
                )
            )
        ]
    ]);
}

/////////////////////////////////////////////////////////////
// JSON FOR PRESET SECTIONS
// update is forced every 24 hours, see transient : 'nimble_preset_sections_refreshed'
// update is forced on 'upgrader_process_complete', on 'after_theme_switch'
function sek_get_preset_section_collection_from_json( $force_update = false ) {
    // JULY 2020 => not stored in a transient anymore. For https://github.com/presscustomizr/nimble-builder/issues/730
    // + clean previously created transients
    $bw_fixes_options = get_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES );
    $bw_fixes_options = is_array( $bw_fixes_options ) ? $bw_fixes_options : array();
    if ( !array_key_exists('clean_section_json_transient_0720', $bw_fixes_options ) || 'done' != $bw_fixes_options['clean_section_json_transient_0720'] ) {
        sek_clean_transients_like( 'section_json_transient' );
        $bw_fixes_options['clean_section_json_transient_0720'] = 'done';
        // flag as done
        update_option( NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES, $bw_fixes_options );
    }

    $json_collection = get_option( NIMBLE_OPT_NAME_FOR_SECTION_JSON );

    // Refresh every 30 days, unless force_update set to true
    // force update is activated on plugin update, theme_switch
    if ( $force_update || false == $json_collection ) {
        $json_raw = @file_get_contents( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        if ( $json_raw === false ) {
            $json_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        }

        $json_collection = json_decode( $json_raw, true );
        // Save now as option for faster access next time
        update_option( NIMBLE_OPT_NAME_FOR_SECTION_JSON, $json_collection );
    }
    // Filter used by NB Pro to add pro sections
    return apply_filters( 'nimble_preset_sections_collection', $json_collection, $force_update );
}


// Maybe set / refresh section data
// - theme switch
// - nimble upgrade
// - nimble is loaded ( only when is_admin() ) <= This makes the loading of the customizer faster on the first load, because the transient is ready.
//add_action( 'nimble_front_classes_ready', '\Nimble\sek_refresh_preset_sections_data');
add_action( 'after_switch_theme', '\Nimble\sek_refresh_preset_sections_data');
add_action( 'upgrader_process_complete', '\Nimble\sek_refresh_preset_sections_data');
function sek_refresh_preset_sections_data() {
    // force refresh only on after_switch_theme and upgrader_process_complete actions
    sek_get_preset_section_collection_from_json( 'nimble_front_classes_ready' != current_filter() );
}

?>