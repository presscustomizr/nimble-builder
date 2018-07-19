<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMAGE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_image_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',
        'name' => __('Image', 'text_domain_to_be_replaced'),
        'starting_value' => array(
            'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        ),
        // 'sanitize_callback' => '\Nimble\czr_image_module_sanitize_validate',
        // 'validate_callback' => '\Nimble\czr_image_module_sanitize_validate',
        'tmpl' => array(
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'img-size' => array(
                    'input_type'  => 'select',
                    'title'       => __('Select the image size', 'text_domain_to_be_replaced'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' )
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'h_alignment',
                    'title'       => __('Alignment', 'text_domain_to_be_replaced'),
                    'default'     => 'center',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment'
                ),
                'link-to' => array(
                    'input_type'  => 'select',
                    'title'       => __('Link to', 'text_domain_to_be_replaced'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'img-link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_domain_to_be_replaced'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_domain_to_be_replaced'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'gutencheck',
                    'title'       => __('Open link in a new page', 'text_domain_to_be_replaced'),
                    'default'     => false
                ),
                // 'lightbox' => array(
                //     'input_type'  => 'gutencheck',
                //     'title'       => __('Activate a lightbox on click', 'text_domain_to_be_replaced'),
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'default'     => 'center'
                // ),
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}
?>