<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_breakpoint() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_breakpoint',
        //'name' => __('Site wide breakpoint options', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'use-custom-breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a global custom breakpoint for responsive columns', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_before_title' => __( 'This is the viewport width from which columns are rearranged vertically. The default global breakpoint is 768px. A custom breakpoint can also be set for each section.', 'text_doma')
                ),
                'global-custom-breakpoint'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Define a custom breakpoint in pixels', 'text_doma' ),
                    'default'     => 768,
                    'min'         => 1,
                    'max'         => 2000,
                    'step'        => 1,
                    'refresh_markup' => true,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'apply-to-all' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply this breakpoint to all by-device customizations', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf(
                        __( '%s When enabled, this custom breakpoint is applied not only to responsive columns but also to all by-device customizations, like alignment for example.', 'text_doma'),
                        '<span class="sek-mobile-device-icons"><i class="sek-switcher preview-desktop"></i>&nbsp;<i class="sek-switcher preview-tablet"></i>&nbsp;<i class="sek-switcher preview-mobile"></i></span>'
                    )
                ),
            )
        )//tmpl
    );
}


add_filter('nimble_set_global_inline_style', '\Nimble\sek_write_global_custom_breakpoint' );
function sek_write_global_custom_breakpoint($global_css = '') {
    $custom_breakpoint = sek_get_global_custom_breakpoint();
    if ( $custom_breakpoint >= 1 ) {
        $global_css = is_string($global_css) ? $global_css : '';
        $css = '@media (min-width:' . $custom_breakpoint . 'px) {.sek-global-custom-breakpoint-col-8 {-ms-flex: 0 0 8.333%;flex: 0 0 8.333%;max-width: 8.333%;}.sek-global-custom-breakpoint-col-9 {-ms-flex: 0 0 9.090909%;flex: 0 0 9.090909%;max-width: 9.090909%;}.sek-global-custom-breakpoint-col-10 {-ms-flex: 0 0 10%;flex: 0 0 10%;max-width: 10%;}.sek-global-custom-breakpoint-col-11 {-ms-flex: 0 0 11.111%;flex: 0 0 11.111%;max-width: 11.111%;}.sek-global-custom-breakpoint-col-12 {-ms-flex: 0 0 12.5%;flex: 0 0 12.5%;max-width: 12.5%;}.sek-global-custom-breakpoint-col-14 {-ms-flex: 0 0 14.285%;flex: 0 0 14.285%;max-width: 14.285%;}.sek-global-custom-breakpoint-col-16 {-ms-flex: 0 0 16.666%;flex: 0 0 16.666%;max-width: 16.666%;}.sek-global-custom-breakpoint-col-20 {-ms-flex: 0 0 20%;flex: 0 0 20%;max-width: 20%;}.sek-global-custom-breakpoint-col-25 {-ms-flex: 0 0 25%;flex: 0 0 25%;max-width: 25%;}.sek-global-custom-breakpoint-col-30 {-ms-flex: 0 0 30%;flex: 0 0 30%;max-width: 30%;}.sek-global-custom-breakpoint-col-33 {-ms-flex: 0 0 33.333%;flex: 0 0 33.333%;max-width: 33.333%;}.sek-global-custom-breakpoint-col-40 {-ms-flex: 0 0 40%;flex: 0 0 40%;max-width: 40%;}.sek-global-custom-breakpoint-col-50 {-ms-flex: 0 0 50%;flex: 0 0 50%;max-width: 50%;}.sek-global-custom-breakpoint-col-60 {-ms-flex: 0 0 60%;flex: 0 0 60%;max-width: 60%;}.sek-global-custom-breakpoint-col-66 {-ms-flex: 0 0 66.666%;flex: 0 0 66.666%;max-width: 66.666%;}.sek-global-custom-breakpoint-col-70 {-ms-flex: 0 0 70%;flex: 0 0 70%;max-width: 70%;}.sek-global-custom-breakpoint-col-75 {-ms-flex: 0 0 75%;flex: 0 0 75%;max-width: 75%;}.sek-global-custom-breakpoint-col-80 {-ms-flex: 0 0 80%;flex: 0 0 80%;max-width: 80%;}.sek-global-custom-breakpoint-col-83 {-ms-flex: 0 0 83.333%;flex: 0 0 83.333%;max-width: 83.333%;}.sek-global-custom-breakpoint-col-90 {-ms-flex: 0 0 90%;flex: 0 0 90%;max-width: 90%;}.sek-global-custom-breakpoint-col-100 {-ms-flex: 0 0 100%;flex: 0 0 100%;max-width: 100%;}}';
          $global_css .= $css;
    }
    return $global_css;
}
?>