<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_site_tmpl_pickers() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('templates for custom post types, custom taxonomies, ....', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'sek_site_tmpl_pickers',
        //'name' => __('Site wide header', 'text_doma'),
        // 'starting_value' => array(

        // ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                // 'skp__home' => array(
                //     'input_type'  => 'site_tmpl_picker',
                //     'title'       => __('Template for home', 'text_doma'),
                //     'default'     => '_no_site_tmpl_',
                //     'choices'     => [
                //         '_no_site_tmpl_' => 'No template',
                //         'nb_tmpl_page-template' => 'Page template',
                //         'nb_tmpl_home-page-template' => 'Home page template',
                //         'nb_tmpl_nimble-template-loop-start-only' => 'Nimble Template + Loop start only'
                //     ],
                //     //'refresh_preview' => true,
                //     'notice_before_title' => '',
                //     'width-100'   => true,
                //     'title_width' => 'width-100'
                // ),
                'skp__all_page' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for pages', 'text_doma'),
                    'default'     => '_no_site_tmpl_',
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_post' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for posts', 'text_doma'),
                    'default'     => '_no_site_tmpl_',
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_category' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for categories', 'text_doma'),
                    'default'     => '_no_site_tmpl_',
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_post_tag' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for tags', 'text_doma'),
                    'default'     => '_no_site_tmpl_',
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_author' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for authors', 'text_doma'),
                    'default'     => '_no_site_tmpl_',
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__search' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for search page', 'text_doma'),
                    'default'     => '_no_site_tmpl_',
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__404' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for 404 error page', 'text_doma'),
                    'default'     => '_no_site_tmpl_',
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_after' => $pro_text,
                    'refresh_preview' => false
                )
            )
        )//tmpl
    );
}

?>