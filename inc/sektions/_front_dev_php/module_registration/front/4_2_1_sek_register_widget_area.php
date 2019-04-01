<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER WIDGET ZONE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
//Availabe input types
// $.extend( api.czrInputMap, {
//       text      : '',
//       textarea  : '',
//       check     : 'setupIcheck',
//       gutencheck : 'setupGutenCheck',
//       select    : 'setupSelect',
//       radio     : 'setupRadio',
//       number    : 'setupStepper',
//       upload    : 'setupImageUploaderSaveAsId',
//       upload_url : 'setupImageUploaderSaveAsUrl',
//       color     : 'setupColorPicker',
//       wp_color_alpha : 'setupColorPickerAlpha',
//       wp_color  : 'setupWPColorPicker',//not used for the moment
//       content_picker : 'setupContentPicker',
//       tiny_mce_editor : 'setupTinyMceEditor',
//       password : '',
//       range : 'setupSimpleRange',
//       range_slider : 'setupRangeSlider',
//       hidden : '',
//       h_alignment : 'setupHAlignement',
//       h_text_alignment : 'setupHAlignement'
// });
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
                    'notice_after' => sprintf( __( 'Once you have added a widget area to a section, you can add and edit the WordPress widgets in it in the %1$s.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.panel('widgets', function( _p_ ){ _p_.focus(); })",
                            __('widget panel', 'text_doma')
                        )
                    ),
                )
            )
        ),
        'render_tmpl_path' => NIMBLE_BASE_PATH . "/tmpl/modules/widget_area_module_tmpl.php",
    );
}

?>