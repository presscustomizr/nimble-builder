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
                'home' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for home', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [
                        'nb_tmpl_home-page-template' => 'Home page template',
                        'nb_tmpl_nimble-template-loop-start-only' => 'Nimble Template + Loop start only'
                    ],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'pages' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for pages', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [
                        'nb_tmpl_home-page-template' => 'Home page template',
                        'nb_tmpl_nimble-template-loop-start-only' => 'Nimble Template + Loop start only'
                    ],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'posts' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for posts', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'categories' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for categories', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'tags' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for tags', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'authors' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for authors', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                'search' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for search page', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),
                '404' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Template for 404 error page', 'text_doma'),
                    'default'     => 'inherit',
                    'choices'     => [],
                    //'refresh_preview' => true,
                    'notice_before_title' => '',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_after' => $pro_text
                )
            )
        )//tmpl
    );
}

?>