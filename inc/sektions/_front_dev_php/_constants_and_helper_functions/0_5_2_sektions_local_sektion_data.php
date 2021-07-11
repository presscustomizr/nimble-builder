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
            'name' => __('Sections for an introduction', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'intro_three',
                    'title' => __('1 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_three.jpg',
                    'demo_url' => '#intro-one'
                ),
                array(
                    'content-id' => 'intro_one',
                    'title' => __('1 column, full-width background', 'text-domain' ),
                    'thumb' => 'intro_one.jpg',
                    'demo_url' => '#intro-two'
                ),
                array(
                    'content-id' => 'intro_two',
                    'title' => __('2 columns, call to action, full-width background', 'text-domain' ),
                    'thumb' => 'intro_two.jpg',
                    'demo_url' => '#intro-three'
                ),
                array(
                    'content-id' => 'pro_intro_two',
                    'title' => __('3 columns, call to actions', 'text-domain' ),
                    'thumb' => 'pro_intro_two.jpg',
                    'active' => sek_is_pro(),
                    'is_pro' => true,
                    'demo_url' => 'https://nimblebuilder.com/special-image-demo?utm_source=usersite&utm_medium=link&utm_campaign=section_demos'
                ),
                array(
                    'content-id' => 'pro_intro_one',
                    'title' => __('2 columns, call to actions, image carousel', 'text-domain' ),
                    'thumb' => 'pro_intro_one.jpg',
                    'active' => sek_is_pro(),
                    'is_pro' => true,
                    'demo_url' => '#intro-four'
                )
            )
        ],
        'sek_post_grids_sec_picker_module' => [
            'name' => __('Post lists sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'grid_one',
                    'title' => __('Simple post grid', 'text-domain' ),
                    'thumb' => 'grid_one.jpg',
                    'demo_url' => 'https://nimblebuilder.com/post-grid-sections?utm_source=usersite&utm_medium=link&utm_campaign=section_demos#grid-one'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'grid_two',
                    'title' => __('Posts on two columns', 'text-domain' ),
                    'thumb' => 'grid_two.jpg',
                    'demo_url' => 'https://nimblebuilder.com/post-grid-sections?utm_source=usersite&utm_medium=link&utm_campaign=section_demos#grid-two'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'pro_grid_one',
                    'title' => __('Masonry post grid', 'text-domain' ),
                    'thumb' => 'pro_grid_one.jpg',
                    'demo_url' => 'https://nimblebuilder.com/post-grid-sections?utm_source=usersite&utm_medium=link&utm_campaign=section_demos#pro-grid-one',
                    'active' => sek_is_pro(),
                    'is_pro' => true
                    //'height' => '188px'
                )
            )
        ],
        'sek_features_sec_picker_module' => [
            'name' => __('Sections for services and features', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'features_one',
                    'title' => __('3 columns with icon and call to action', 'text-domain' ),
                    'thumb' => 'features_one.jpg',
                    'demo_url' => '#service-one'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'features_two',
                    'title' => __('3 columns with icon', 'text-domain' ),
                    'thumb' => 'features_two.jpg',
                    'demo_url' => '#service-two'
                    //'height' => '188px'
                )
            )
        ],
        'sek_about_sec_picker_module' => [
            'name' => __('About us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'about_one',
                    'title' => __('A simple about us section with 2 columns', 'text-domain' ),
                    'thumb' => 'about_one.jpg',
                    'demo_url' => '#about-one'
                    //'height' => '188px'
                )
            )
        ],
        'sek_contact_sec_picker_module' => [
            'name' => __('Contact-us sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'contact_one',
                    'title' => __('A contact form and a Google map', 'text-domain' ),
                    'thumb' => 'contact_one.jpg',
                    'demo_url' => '#contact-one'
                    //'height' => '188px'
                ),
                array(
                    'content-id' => 'contact_two',
                    'title' => __('A contact form with an image background', 'text-domain' ),
                    'thumb' => 'contact_two.jpg',
                    'demo_url' => '#contact-two'
                    //'height' => '188px'
                )
            )
        ],
        'sek_team_sec_picker_module' => [
            'name' => __('Sections for teams', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'team_one',
                    'title' => __('4 column', 'text-domain' ),
                    'thumb' => 'team_one.jpg',
                    'demo_url' => '#team-one'
                ),
                array(
                    'content-id' => 'team_two',
                    'title' => __('3 columns', 'text-domain' ),
                    'thumb' => 'team_two.jpg',
                    'height' => '180px',
                    'demo_url' => '#team-two'
                ),
                array(
                    'content-id' => 'pro_team_one',
                    'title' => __('3 columns, call to action', 'text-domain' ),
                    'thumb' => 'pro_team_one.jpg',
                    'active' => sek_is_pro(),
                    'height' => '180px',
                    'is_pro' => true,
                    'demo_url' => '#team-three'
                )
            )
        ],
        'sek_column_layouts_sec_picker_module' => [
            'name' => __('Empty sections with columns layout', 'text_doma'),
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
            'name' => __('Header sections', 'text_doma'),
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
            'name' => __('Footer sections', 'text_doma'),
            'section_collection' => array(
                array(
                    'content-id' => 'footer_pro_one',
                    'title' => __('simple 2 columns footer', 'text-domain' ),
                    'thumb' => 'footer_pro_one.jpg',
                    'section_type' => 'footer',
                    'height' => '75px',
                    'active' => sek_is_pro(),
                    'is_pro' => true
                ),
                array(
                    'content-id' => 'footer_with_social_links_one',
                    'title' => __('footer with dynamic date, site title and social links', 'text-domain' ),
                    'thumb' => 'footer_with_social_links_one.jpg',
                    'section_type' => 'footer',
                    'height' => '51px'
                ),
                array(
                    'content-id' => 'footer_one',
                    'title' => __('simple 3 columns footer', 'text-domain' ),
                    'thumb' => 'footer_one.jpg',
                    'section_type' => 'footer',
                    'height' => '75px'
                )
            )
        ]
    ]);
}
?>