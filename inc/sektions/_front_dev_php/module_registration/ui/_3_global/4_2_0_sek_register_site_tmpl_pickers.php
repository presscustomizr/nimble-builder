<?php
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_sek_site_tmpl_pickers() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('templates for custom post types, custom taxonomies, ....', 'text-doma') );
    }
    $default_params = [ 'site_tmpl_id' => '_no_site_tmpl_', 'site_tmpl_source' => 'user_tmpl', 'site_tmpl_title' => '' ];
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
                //     'default'     => $default_params,
                //     //'refresh_preview' => true,
                //     'notice_before_title' => '',
                //     'width-100'   => true,
                //     'title_width' => 'width-100'
                // ),
                'skp__all_page' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for single pages', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false,
                    'html_before' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> <a href="%2$s" target="_blank" rel="noopener noreferrer">%1$s</a></span><hr/>',
                        __('How to use site templates with Nimble Builder ?'),
                        'https://docs.presscustomizr.com/article/428-how-to-use-site-templates-with-nimble-builder'
                    ),
                ),
                'skp__all_post' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for single posts', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_category' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for categories', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_post_tag' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for tags', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_author' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for authors', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                'skp__all_attachment'  => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for attachment pages', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                // this skope has no group skope => this is why we need to add the suffix '_for_site_tmpl' to differentiate with local sektion skope
                // @ see skp_get_no_group_skope_list()
                'skp__search_for_site_tmpl' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for search page', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_preview' => false
                ),
                // this skope has no group skope => this is why we need to add the suffix '_for_site_tmpl' to differentiate with local sektion skope
                // @ see skp_get_no_group_skope_list()
                'skp__404_for_site_tmpl' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for 404 error page', 'text_doma'),
                    'default'     => $default_params,
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_after' => $pro_text,
                    'refresh_preview' => false
                ),
                // this skope has no group skope => this is why we need to add the suffix '_for_site_tmpl' to differentiate with local sektion skope
                // @ see skp_get_no_group_skope_list()
                'skp__date_for_site_tmpl' => array(
                    'input_type'  => 'site_tmpl_picker',
                    'title'       => __('Template for date pages', 'text_doma'),
                    'default'     => $default_params,
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