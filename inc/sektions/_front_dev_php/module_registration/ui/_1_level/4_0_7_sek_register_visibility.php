<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_level_visibility_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_level_visibility_module',
        //'name' => __('Set visibility on devices', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'desktops' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">desktop_mac</i> %1$s', __('Visible on desktop devices', 'text_doma') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'tablets' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">tablet_mac</i> %1$s', __('Visible on tablet devices', 'text_doma') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'mobiles' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('<i class="material-icons" style="font-size: 1.2em;">phone_iphone</i> %1$s', __('Visible on mobile devices', 'text_doma') ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __('Note that those options are not applied during the live customization of your site, but only when the changes are published.', 'text_domain')
                ),
            )
        )//tmpl
    );
}


/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// levels are visible by default
// the default CSS rule should be :
// @media (min-width:768px){
//   [data-sek-level="location"] .sek-hidden-on-desktops { display: none; }
// }
// @media (min-width:575px) and (max-width:767px){
//   [data-sek-level="location"] .sek-hidden-on-tablets { display: none; }
// }
// @media (max-width:575px){
//   [data-sek-level="location"] .sek-hidden-on-mobiles { display: none; }
// }
//
// Dec 2019 : since issue https://github.com/presscustomizr/nimble-builder/issues/555, we use a dynamic CSS rule generation instead of static CSS
add_filter( 'sek_add_css_rules_for_level_options', '\Nimble\sek_add_css_rules_for_level_visibility', 10, 3 );
function sek_add_css_rules_for_level_visibility( $rules, $level ) {
    $options = empty( $level[ 'options' ] ) ? array() : $level['options'];
    if ( empty( $options[ 'visibility' ] ) )
      return $rules;

    $visibility_options = is_array( $options[ 'visibility' ] ) ? $options[ 'visibility' ] : array();

    // Get the default breakpoint values
    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];// 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    // nested section should inherit the custom breakpoint of the parent
    // @fixes https://github.com/presscustomizr/nimble-builder/issues/554
    $custom_tablet_breakpoint =  intval( sek_get_closest_section_custom_breakpoint( array( 'searched_level_id' => $level['id'] ) ) );

    if ( $custom_tablet_breakpoint >= 1 ) {
        $tablet_breakpoint = $custom_tablet_breakpoint;
    }

    // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
    $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;

    $visibility_value =  array(
        'desktop' => ( array_key_exists('desktops', $visibility_options ) && true !== sek_booleanize_checkbox_val( $visibility_options['desktops'] ) ) ? 'hide' : '',
        'tablet' => ( array_key_exists('tablets', $visibility_options ) && true !== sek_booleanize_checkbox_val( $visibility_options['tablets'] ) ) ? 'hide' : '',
        'mobile' => ( array_key_exists('mobiles', $visibility_options ) && true !== sek_booleanize_checkbox_val( $visibility_options['mobiles'] ) ) ? 'hide' : ''
    );

    $mob_bp_val = $mobile_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551
    foreach ( $visibility_value as $device => $visibility_val ) {
        if ( 'hide' !== $visibility_val )
          continue;

        switch( $device ) {
            case 'desktop' :
                $media_qu = "(min-width:{$tablet_breakpoint}px)";
            break;
            case 'tablet' :
                $tab_bp_val = $tablet_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551
                if ( $mobile_breakpoint >= ( $tab_bp_val ) ) {
                    $media_qu = "(max-width:{$tab_bp_val}px)";
                } else {
                    $media_qu = "(min-width:{$mob_bp_val}px) and (max-width:{$tab_bp_val}px)";
                }
            break;
            case 'mobile' :
                $media_qu = "(max-width:{$mob_bp_val}px)";
            break;
        }
        /* WHEN CUSTOMIZING MAKE SURE WE CAN SEE THE LEVELS, EVEN IF SETUP TO BE HIDDEN WITH THE CURRENT PREVIEWED DEVICE */
        $rules[] = array(
            'selector' => '.customizer-preview [data-sek-level="location"] [data-sek-id="'.$level['id'].'"]',
            'css_rules' => 'display: -ms-flexbox;display: -webkit-box;display: flex;-webkit-filter: grayscale(50%);filter: grayscale(50%);-webkit-filter: gray;filter: gray;opacity: 0.7;',
            'mq' => $media_qu
        );
        $rules[] = array(
            'selector' => '[data-sek-level="location"] [data-sek-id="'.$level['id'].'"]',
            'css_rules' => 'display:none',
            'mq' => $media_qu
        );
    }
    return $rules;
}

?>