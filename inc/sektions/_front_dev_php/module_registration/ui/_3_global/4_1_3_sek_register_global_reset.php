<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_global_reset() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_global_reset',
        //'name' => __('Reset global scope sections', 'nimble-builder'),
        'tmpl' => array(
            'item-inputs' => array(
                'reset_global' => array(
                    'input_type'  => 'reset_button',
                    'title'       => __( 'Remove the sections displayed globally' , 'nimble-builder' ),
                    'scope'       => 'global',
                    'notice_after' => __('This will remove the sections displayed on global scope locations. Local scope sections will not be impacted.', 'nimble-builder'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                )
            )
        )//tmpl
    );
}

?>