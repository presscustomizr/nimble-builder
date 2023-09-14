<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_imp_exp() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_imp_exp',
        //'name' => __('Export / Import global sections', 'nimble-builder'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'nimble-builder' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'import_export' => array(
                    'input_type'  => 'import_export',
                    'scope' => 'global',
                    'title'       => __('EXPORT', 'nimble-builder'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_before' => sprintf('<span class="czr-notice">%1$s</span><br/>',__('These options allows you to export and import global sections like a global header-footer.', 'nimble-builder') )
                    // 'notice_after' => __('Select a revision from the drop-down list to preview it. You can then restore it by clicking the Publish button at the top of the page.', 'nimble-builder')
                ),
                // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                // https://github.com/presscustomizr/nimble-builder/issues/676
                'import_img' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Import images in your media library.', 'nimble-builder'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'notice_after' => __( 'When this option is unchecked, Nimble Builder will not import images and use instead the url of the original images.', 'nimble-builder'),
                )
                // 'keep_existing_sections' => array(
                //     'input_type'  => 'nimblecheck',
                //     'title'       => __('Combine the imported sections with the current ones.', 'nimble-builder'),
                //     'default'     => 0,
                //     'title_width' => 'width-80',
                //     'input_width' => 'width-20',
                //     'refresh_markup' => false,
                //     'refresh_stylesheet' => false,
                //     'refresh_preview' => true,
                //     'notice_after' => __( 'Check this option if you want to keep the existing sections of this page, and combine them with the imported ones.', 'nimble-builder'),
                // )
            )
        )//tmpl
    );
}
?>