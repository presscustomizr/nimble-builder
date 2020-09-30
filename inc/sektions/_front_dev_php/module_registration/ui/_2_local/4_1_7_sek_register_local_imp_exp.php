<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_imp_exp() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_imp_exp',
        //'name' => __('Export / Import', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'import_export' => array(
                    'input_type'  => 'import_export',
                    'scope' => 'local',
                    'title'       => __('EXPORT', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    //'notice_before' => __('Make sure you import a file generated with Nimble Builder export system.', 'text_doma'),
                    // 'notice_after' => __('Select a revision from the drop-down list to preview it. You can then restore it by clicking the Publish button at the top of the page.', 'text_doma')
                ),
                'keep_existing_sections' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Combine the imported sections with the current ones.', 'text_doma'),
                    'default'     => 0,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'notice_after' => __( 'Check this option if you want to keep the existing sections of this page, and combine them with the imported ones.', 'text_doma'),
                ),
                // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                'import_img' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Import images in your media library.', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'notice_after' => __( 'When this option is unchecked, Nimble Builder will not import images and use instead the url of the original images.', 'text_doma'),
                )
            )
        )//tmpl
    );
}
?>