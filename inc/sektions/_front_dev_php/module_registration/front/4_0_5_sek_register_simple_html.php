<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE HTML MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
function sek_get_module_params_for_czr_simple_html_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_html_module',
        'name' => __( 'Html Content', 'text_domain_to_be_replaced' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_html_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'html_content' => sprintf('<pre>%1$s</pre>', __('html code goes here', 'text-domain') )
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'html_content' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'HTML Content' , 'text_domain_to_be_replaced' ),
                    'default'     => sprintf('<pre>%1$s</pre>', __('html code goes here', 'text-domain') )
                    //'code_type' => 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/simple_html_module_tmpl.php",
        'placeholder_icon' => 'code'
    );
}

function sanitize_callback__czr_simple_html_module( $value ) {
    if ( array_key_exists( 'html_content', $value ) ) {
        if ( !current_user_can( 'unfiltered_html' ) ) {
            $value[ 'html_content' ] = wp_kses_post( $value[ 'html_content' ] );
        }
    }
    return $value;
}
?>
