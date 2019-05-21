<?php
namespace Nimble;
// This file has been introduced on May 21st 2019 => back to the local data
// after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445

/////////////////////////////////////////////////////////////
// REGISTRATION PARAMS FOR PRESET SECTIONS
// Store the params in transient, refreshed every hour
// @return array()
function sek_get_sections_registration_params( $force_update = false ) {
    $section_params_transient_name = 'section_params_transient_' . NIMBLE_VERSION;
    $registration_params = get_transient( $section_params_transient_name );
    // Refresh every 30 days, unless force_update set to true
    if ( $force_update || false === $registration_params ) {
        $registration_params = sek_get_raw_registration_params();
        set_transient( $section_params_transient_name, $registration_params, 30 * DAY_IN_SECONDS );
    }
    return $registration_params;
}

function sek_get_raw_registration_params() {
    return [
        'sek_intro_sec_picker_module' => [
            'module_title' => __('Sections for an introduction', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'intro_three',
                    'title' => __('1 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_three.jpg'
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
                    'title' => __('simple header with a logo on the right, menu on the left', 'text-domain' ),
                    'thumb' => 'header_one.jpg',
                    'height' => '33px'
                )
            )
        ],
        'sek_footer_sec_picker_module' => [
            'module_title' => __('Footer sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'footer_one',
                    'title' => __('simple footer with 3 columns and large bottom zone', 'text-domain' ),
                    'thumb' => 'footer_one.jpg'
                )
            )
        ]
    ];
}

/////////////////////////////////////////////////////////////
// JSON FOR PRESET SECTIONS
function sek_get_preset_section_collection_from_json( $force_update = false ) {
    $section_json_transient_name = 'section_json_transient_' . NIMBLE_VERSION;
    $json_collection = get_transient( $section_json_transient_name );
    // Refresh every 30 days, unless force_update set to true
    if ( $force_update || false === $json_collection ) {
        $json_raw = @file_get_contents( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        if ( $json_raw === false ) {
            $json_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        }

        $json_collection = json_decode( $json_raw, true );
        set_transient( $section_json_transient_name, $json_collection, 30 * DAY_IN_SECONDS );
    }
    return $json_collection;
}


// Maybe refresh data on
// - theme switch
// - nimble upgrade
// - nimble is loaded ( only when is_admin() ) <= This makes the loading of the customizer faster on the first load, because the transient is ready.
add_action( 'nimble_front_classes_ready', '\Nimble\sek_refresh_preset_sections_data');
add_action( 'after_switch_theme', '\Nimble\sek_refresh_preset_sections_data');
add_action( 'upgrader_process_complete', '\Nimble\sek_refresh_preset_sections_data');
function sek_refresh_preset_sections_data() {
    if ( 'nimble_front_classes_ready' === current_filter() && !is_admin() )
      return;
    if ( 'nimble_front_classes_ready' === current_filter() && defined( 'DOING_AJAX') && DOING_AJAX )
      return;

    // => so the posts and message are up to date
    sek_get_preset_section_collection_from_json(true);
    sek_get_sections_registration_params(true);
}

?>