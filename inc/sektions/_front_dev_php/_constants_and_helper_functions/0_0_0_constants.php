<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// @return bool
function sek_is_dev_mode() {
  return ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
}
// @return bool
// Nov 2020 : helper used to display NB CPT in admin
function sek_is_cpt_debug_mode() {
  return isset( $_GET['nimble_cpt_debug'] ) || (defined('NIMBLE_CPT_DEBUG_MODE') && NIMBLE_CPT_DEBUG_MODE);
}

if ( !defined( 'NIMBLE_CPT' ) ) { define( 'NIMBLE_CPT' , 'nimble_post_type' ); }
if ( !defined( 'NIMBLE_TEMPLATE_CPT' ) ) { define( 'NIMBLE_TEMPLATE_CPT' , 'nimble_template' ); }
if ( !defined( 'NIMBLE_SECTION_CPT' ) ) { define( 'NIMBLE_SECTION_CPT' , 'nimble_section' ); }

if ( !defined( 'NIMBLE_PREFIX_FOR_SAVED_TMPL' ) ) { define( 'NIMBLE_PREFIX_FOR_SAVED_TMPL' , 'nb_tmpl_' ); }
if ( !defined( 'NIMBLE_PREFIX_FOR_SAVED_SECTION' ) ) { define( 'NIMBLE_PREFIX_FOR_SAVED_SECTION' , 'nb_section_' ); }

if ( !defined( 'NIMBLE_DEPREC_ONE_CSS_FOLDER_NAME' ) ) { define( 'NIMBLE_DEPREC_ONE_CSS_FOLDER_NAME' , 'sek_css' ); }//<= folder name deprecated in july 2020
if ( !defined( 'NIMBLE_DEPREC_TWO_CSS_FOLDER_NAME' ) ) { define( 'NIMBLE_DEPREC_TWO_CSS_FOLDER_NAME' , 'nb_css' ); }//<= folder name deprecated in october 2020
if ( !defined( 'NIMBLE_CSS_FOLDER_NAME' ) ) { define( 'NIMBLE_CSS_FOLDER_NAME' , 'nimble_css' ); }
if ( !defined( 'NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS' ) ) { define( 'NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS' , 'nimble_module_css_read_status' ); }

if ( !defined( 'NIMBLE_OPT_SEKTION_POST_INDEX' ) ) { define( 'NIMBLE_OPT_SEKTION_POST_INDEX' , 'nimble_posts_index' ); }
if ( !defined( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' ) ) { define( 'NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION' , 'nimble___' ); }
if ( !defined( 'NIMBLE_GLOBAL_SKOPE_ID' ) ) { define( 'NIMBLE_GLOBAL_SKOPE_ID' , 'skp__global' ); }

if ( !defined( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS' , 'nimble_global_opts' ); }// <= name updated in march 2021 was __nimble_options__

//if ( !defined( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS' , 'nimble_saved_sektions' ); } //<= June 2020 to be removed
if ( !defined( 'NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS' , 'nimble_most_used_fonts' ); }
if ( !defined( 'NIMBLE_OPT_FOR_GLOBAL_CSS' ) ) { define( 'NIMBLE_OPT_FOR_GLOBAL_CSS' , 'nimble_global_css' ); }

if ( !defined( 'NIMBLE_OPT_NAME_FOR_SECTION_JSON' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SECTION_JSON' , 'nimble_prebuild_sections' ); }// <= name updated in march 2021, was nb_prebuild_section_json

if ( !defined( 'NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES' ) ) { define( 'NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES' , 'nb_backward_fixes' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING' ) ) { define( 'NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING' , 'nb_shortcodes_parsed_in_czr' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE' ) ) { define( 'NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE' , 'nb_widgets_disabled_in_czr' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_DEBUG_MODE' ) ) { define( 'NIMBLE_OPT_NAME_FOR_DEBUG_MODE' , 'nb_debug_mode_active' ); }
if ( !defined( 'NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS' ) ) { define( 'NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS' , 'nb_google_font_disable' ); }


if ( !defined( 'NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED' ) ) { define( 'NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED' , '__nimble__' ); }
if ( !defined( 'NIMBLE_WIDGET_PREFIX' ) ) { define( 'NIMBLE_WIDGET_PREFIX' , 'nimble-widget-area-' ); }
if ( !defined( 'NIMBLE_ASSETS_VERSION' ) ) { define( 'NIMBLE_ASSETS_VERSION', sek_is_dev_mode() ? time() : NIMBLE_VERSION ); }
if ( !defined( 'NIMBLE_MODULE_ICON_PATH' ) ) { define( 'NIMBLE_MODULE_ICON_PATH' , NIMBLE_BASE_URL . '/assets/czr/sek/icons/modules/' ); }
if ( !defined( 'NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID') ) { define( 'NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID' , 'czr-customize-content_editor' ); }

// TRANSIENTS ID
if ( !defined( 'NIMBLE_WELCOME_NOTICE_ID' ) ) { define ( 'NIMBLE_WELCOME_NOTICE_ID', 'nimble-welcome-notice-12-2018' ); }
//mt_rand(0, 65535) . 'test-nimble-feedback-notice-04-2019'
if ( !defined( 'NIMBLE_FEEDBACK_NOTICE_ID' ) ) { define ( 'NIMBLE_FEEDBACK_NOTICE_ID', 'nimble-feedback-notice-01-2022' ); }
if ( !defined( 'NIMBLE_FAWESOME_TRANSIENT_ID' ) ) { define ( 'NIMBLE_FAWESOME_TRANSIENT_ID', 'sek_font_awesome_october_2021' ); }
if ( !defined( 'NIMBLE_GFONTS_TRANSIENT_ID' ) ) { define ( 'NIMBLE_GFONTS_TRANSIENT_ID', 'sek_gfonts_march_2020' ); }
if ( !defined( 'NIMBLE_FEEDBACK_STATUS_TRANSIENT_ID' ) ) { define ( 'NIMBLE_FEEDBACK_STATUS_TRANSIENT_ID', 'nimble_feedback_status' ); }
if ( !defined( 'NIMBLE_API_CHECK_TRANSIENT_ID' ) ) { define ( 'NIMBLE_API_CHECK_TRANSIENT_ID', 'nimble_version_check_for_api' ); }


if ( !defined( 'NIMBLE_GOOGLE_FONTS_STYLESHEET_ID' ) ) { define ( 'NIMBLE_GOOGLE_FONTS_STYLESHEET_ID', 'sek-gfonts-local-and-global' ); }
if ( !defined( 'NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID' ) ) { define ( 'NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID', 'nimble-global-inline-style' ); }

if ( !defined( "NIMBLE_DATA_API_URL_V2" ) ) { define( "NIMBLE_DATA_API_URL_V2",
  ( defined('NIMBLE_FETCH_API_LOCALLY') && NIMBLE_FETCH_API_LOCALLY && defined('NIMBLE_LOCAL_API_URL') ) ? NIMBLE_LOCAL_API_URL : 'https://api.nimblebuilder.com/wp-json/nimble/v2/cravan'
); }
if ( !defined( 'NIMBLE_PRO_URL' ) ) { define ( 'NIMBLE_PRO_URL', 'https://presscustomizr.com/nimble-builder-pro' ); }

// @return bool
function sek_is_debug_mode() {
  return isset( $_GET['nimble_debug'] ) || sek_booleanize_checkbox_val( get_option( NIMBLE_OPT_NAME_FOR_DEBUG_MODE ) );
}

?>