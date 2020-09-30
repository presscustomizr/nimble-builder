<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_reset() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_reset',
        //'name' => __('Reset the sections of the current page', 'text_doma'),
        'tmpl' => array(
            'item-inputs' => array(
                'reset_local' => array(
                    'input_type'  => 'reset_button',
                    'title'       => __( 'Remove the Nimble sections in the current page' , 'text_doma' ),
                    'scope'       => 'local',
                    'notice_after' => __('This will reset the sections created for the currently previewed page only. All other sections in other contexts will be preserved.', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                )
            )
        )//tmpl
    );
}
?>