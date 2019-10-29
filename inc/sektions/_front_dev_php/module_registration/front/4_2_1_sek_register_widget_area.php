<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER WIDGET ZONE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_widget_area_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_widget_area_module',
        'name' => __('Widget Zone', 'text_doma'),
        //'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'widget-area-id' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a widget area', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => array(),
                    'refresh_preview' => true,// <= so that the partial refresh links are displayed
                    'html_before' => '<span class="czr-notice">' . __('This module allows you to embed any WordPress widgets in your Nimble sections.', 'text_doma') . '<br/>' . __('1) Select a widget area in the dropdown list,', 'text_doma') . '<br/>' . sprintf( __( '2) once selected an area, you can add and edit the WordPress widgets in it in the %1$s.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s"><strong>%2$s</strong></a>',
                            "javascript:wp.customize.panel('widgets', function( _p_ ){ _p_.focus(); })",
                            __('widget panel', 'text_doma')
                        )
                    ) . '</span><br/>'
                )
            )
        ),
        'render_tmpl_path' => "widget_area_module_tmpl.php",
    );
}

?>