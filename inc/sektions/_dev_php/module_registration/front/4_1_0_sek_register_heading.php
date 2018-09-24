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
            'font_settings' => 'czr_font_child'
        ),
        'name' => __('Text Editor', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'main_settings' => array(
                'heading_text' => 'This is a heading.',
                'h_alignment_css' => 'center'
            )
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/heading_module_tmpl.php",
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
        'name' => __('Content', 'text_domain_to_be_replaced'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'heading_text' => array(
                    'input_type'         => 'text',
                    'title'              => __( 'Heading text', 'text_domain_to_be_replaced' ),
                    'default'            => '',
                    'width-100'         => true,
                    'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_domain_to_be_replaced'),

                ),
                'heading_tag' => array(
                    'input_type'         => 'select',
                    'title'              => __( 'Heading tag', 'text_domain_to_be_replaced' ),
                    'default'            => 'h1',
                    'choices'            => sek_get_select_options_for_input_id( 'heading_tag' )
                ),
                'h_alignment_css'        => array(
                    'input_type'         => 'h_text_alignment',
                    'title'              => __( 'Alignment', 'text_domain_to_be_replaced' ),
                    'default'            => is_rtl() ? 'right' : 'left',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment'
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
