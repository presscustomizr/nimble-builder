<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER FEATURED PAGES MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_featured_pages_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_featured_pages_module',
        'is_crud' => true,
        'name' => __('Featured Pages', 'text_doma'),
        // 'starting_value' => array(
        //     'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Display an image', 'text_doma'),
                    'default'     => 'featured',
                    'choices'     => sek_get_select_options_for_input_id( 'img-type' )
                ),
            ),
            // 'mod-opt' => array(
            //     // 'page-id' => array(
            //     //     'input_type'  => 'content_picker',
            //     //     'title'       => __('Pick a page', 'text_doma')
            //     // ),
            //     'mod_opt_test' => array(
            //         'input_type'  => 'simpleselect',
            //         'title'       => __('Display an image', 'text_doma'),
            //         'default'     => 'featured'
            //     ),
            // ),
            'item-inputs' => array(
                'page-id' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Pick a page', 'text_doma'),
                    'default'     => ''
                ),
                'img-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Display an image', 'text_doma'),
                    'default'     => 'featured',
                    'choices'     => sek_get_select_options_for_input_id( 'img-type' )
                ),
                'img-id' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'img-size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' )
                ),
                'content-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Display a text', 'text_doma'),
                    'default'     => 'page-excerpt',
                    'choices'     => sek_get_select_options_for_input_id( 'content-type' )
                ),
                'content-custom-text' => array(
                    'input_type'  => 'nimble_tinymce_editor',
                    'title'       => __('Custom text content', 'text_doma'),
                    'default'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
                ),
                'btn-display' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display a call to action button', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'btn-custom-text' => array(
                    'input_type'  => 'nimble_tinymce_editor',
                    'title'       => __('Custom button text', 'text_doma'),
                    'default'     => __('Read More', 'text_doma'),
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/featured_pages_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}

?>