<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_local_reset() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_local_reset',
        //'name' => __('Reset the sections of the current page', 'text_doma'),
        'tmpl' => array(
            'item-inputs' => array(
                // Added April 2021 for #478
                // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
                // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
                // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
                // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
                'inherit_group_scope' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('After removal : inherit the site template if specified', 'text_doma'),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                    'refresh_preview' => true,
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> <a href="%2$s" target="_blank" rel="noopener noreferrer">%1$s</a></span>',
                        __('How to use site templates with Nimble Builder ?'),
                        'https://docs.presscustomizr.com/article/428-how-to-use-site-templates-with-nimble-builder'
                    ),
                    'notice_after' => __('If a site template is defined for this context, this page will inherit the site template by default, unless this option is unchecked.', 'text_doma'),
                    //'notice_after' => __( 'Check this option if you want to keep the existing sections of this page, and combine them with the imported ones.', 'text_doma'),
                ),
                'reset_local' => array(
                    'input_type'  => 'reset_button',
                    'title'       => __( 'Remove all sections and Nimble Builder options of this page' , 'text_doma' ),
                    'scope'       => 'local',
                    'html_before' => '<hr/>',
                    'notice_after' => __('This will remove the options and sections created for the currently previewed page only. All other sections and options in other contexts will be preserved.', 'text_doma'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                )
            )
        )//tmpl
    );
}
?>