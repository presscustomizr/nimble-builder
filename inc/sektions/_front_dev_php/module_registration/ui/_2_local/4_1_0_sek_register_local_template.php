<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_template() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_template',
        //'name' => __('Template for the current page', 'nimble-builder'),
        'starting_value' => array(),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'local_template' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a template', 'nimble-builder'),
                    'default'     => 'default',
                    'width-100'   => true,
                    'choices'     => array(
                        'default' => __('Default theme template','nimble-builder'),
                        'nimble_template' => __('Nimble Builder template','nimble-builder')
                    ),
                    'refresh_preview' => true,
                    'notice_before_title' => __('Use Nimble Builder\'s template to display content created only with Nimble Builder on this page. Your theme\'s default template will be overriden','nimble-builder')
                    //'notice_after' => __('When you select Nimble Builder\'s template, only the Nimble sections are displayed.')
                )
            )
        )//tmpl
    );
}
?>