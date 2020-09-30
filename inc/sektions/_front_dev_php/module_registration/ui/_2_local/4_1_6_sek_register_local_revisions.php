<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_revisions() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_revisions',
        //'name' => __('Revision history', 'text_doma'),
        // 'starting_value' => array(
        //     'local_custom_css' => sprintf( '/* %1$s */', __('Add your own CSS code here', 'text_doma' ) )
        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_revisions' => array(
                    'input_type'  => 'revision_history',
                    'title'       => __('Browse your revision history', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'notice_before' => __('This is the revision history of the sections of the currently customized page.', 'text_doma'),
                    'notice_after' => __('Select a revision from the drop-down list to preview it. You can then restore it by clicking the Publish button at the top of the page.', 'text_doma')
                )
            )
        )//tmpl
    );
}
?>