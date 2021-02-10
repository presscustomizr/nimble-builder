<?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_heading_child',
            'font_settings' => 'czr_font_child',
            'spacing' => 'czr_heading_spacing_child'
        ),
        'name' => __('Heading', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'heading_text' => 'This is a heading.'
            )
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        'sanitize_callback' => '\Nimble\sek_sanitize_czr_heading_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'render_tmpl_path' => "heading_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}


/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_child',
        'name' => __('Content', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'heading_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                        'height' => 50
                    ),
                    'title'              => __( 'Heading text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'refresh_markup'    => '.sek-heading [data-sek-input-type="textarea"]'
                    //'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                ),
                'heading_tag' => array(
                    'input_type'         => 'simpleselect',
                    'title'              => __( 'Heading tag', 'text_doma' ),
                    'default'            => 'h1',
                    'choices'            => sek_get_select_options_for_input_id( 'heading_tag' )
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'heading_title' => array(
                    'input_type'         => 'text',
                    'title' => __('Display a tooltip text when the mouse is held over', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'notice_after' => __('Not previewable during customization', 'text_domain_to')
                ),
                'link-to' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Turn into a link', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new page', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' =>'',
    );
}


/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sek_sanitize_czr_heading_module( $content ) {
    if ( is_array($content) && is_array($content['main_settings']) ) {
        // main heading text
        if ( !empty($content['main_settings']['heading_text']) && !sek_is_json( $content['main_settings']['heading_text'] ) ) {
            $content['main_settings']['heading_text'] = json_encode($content['main_settings']['heading_text']);
        }
        if ( !empty($content['main_settings']['heading_title']) && !sek_is_json( $content['main_settings']['heading_title'] ) ) {
            $content['main_settings']['heading_title'] = json_encode($content['main_settings']['heading_title']);
        }
    }
    return $content;
}



/* ------------------------------------------------------------------------- *
 *  HEADING SPACING CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_spacing_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_spacing_child',
        'name' => __('Spacing', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Margin and padding', 'text_doma' ),
                    'default'     => array('desktop' => array('margin-bottom' => '0.6', 'margin-top' => '0.6', 'unit' => 'em')),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    //'css_selectors'=> ''
                )
            )
        ),
        'render_tmpl_path' =>'',
    );
}




function sanitize_callback__czr_heading_module( $value ) {
    if (  !current_user_can( 'unfiltered_html' ) && array_key_exists('main_settings', $value ) && is_array( $value['main_settings'] ) && array_key_exists('heading_text', $value['main_settings'] ) ) {
        //sanitize heading_text
        if ( function_exists( 'czr_heading_module_kses_text' ) ) {
            $value['main_settings'][ 'heading_text' ] = czr_heading_module_kses_text( $value['main_settings'][ 'heading_text' ] );
        }
    }
    return $value;
    //return new \WP_Error('required' ,'heading did not pass sanitization');
}

// @see SEK_CZR_Dyn_Register::set_dyn_setting_args
// Only the boolean true or a WP_error object will be valid returned value considered when validating
function validate_callback__czr_heading_module( $value ) {
    //return new \WP_Error('required' ,'heading did not pass ');
    return true;
}


?>
