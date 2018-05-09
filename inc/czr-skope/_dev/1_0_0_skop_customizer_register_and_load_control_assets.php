<?php
/////////////////////////////////////////////////////////////////
// PRINT CUSTOMIZER JAVASCRIPT + LOCALIZED DATA
add_action ( 'customize_controls_enqueue_scripts', 'skp_enqueue_controls_js_css', 20 );
function skp_enqueue_controls_js_css() {
    $_use_unminified = defined('CZR_DEV')
        && true === CZR_DEV
        // && false === strpos( dirname( dirname( dirname (__FILE__) ) ) , 'inc/wfc' )
        && file_exists( sprintf( '%s/assets/czr/js/czr-skope-base.js' , dirname( __FILE__ ) ) );

    $_prod_script_path          = sprintf(
        '%1$s/assets/czr/js/%2$s' ,
        SKOPE_BASE_URL,
        $_use_unminified ? 'czr-skope-base.js' : 'czr-skope-base.min.js'
    );

    wp_enqueue_script(
        'czr-skope-base',
        //dev / debug mode mode?
        $_prod_script_path,
        array('customize-controls' , 'jquery', 'underscore'),
        ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() :  wp_get_theme() -> version,
        $in_footer = true
    );

    wp_localize_script(
        'czr-skope-base',
        'FlatSkopeLocalizedData',
        array(
            'noGroupSkopeList' => skp_get_no_group_skope_list(),
            'defaultSkopeModel' => skp_get_default_skope_model(),
            'i18n' => array()
        )
    );
}

?>