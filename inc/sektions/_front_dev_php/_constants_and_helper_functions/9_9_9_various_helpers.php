<?php

// Filter the local skope id when invoking skp_get_skope_id in a customize_save ajax action
add_filter( 'skp_get_skope_id', '\Nimble\sek_filter_skp_get_skope_id', 10, 2 );
function sek_filter_skp_get_skope_id( $skope_id, $level ) {
    // When ajaxing, @see the js callback on 'save-request-params', core hooks for the save query
    // api.bind('save-request-params', function( query ) {
    //       $.extend( query, { local_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ) } );
    // });
    // implemented to fix : https://github.com/presscustomizr/nimble-builder/issues/242
    if ( 'local' === $level && is_array( $_POST ) && !empty( $_POST['local_skope_id'] ) && 'customize_save' === sanitize_text_field($_POST['action']) ) {
        $skope_id = sanitize_text_field($_POST['local_skope_id']);
    }
    return $skope_id;
}

//@return string
function sek_get_seks_setting_id( $skope_id = '' ) {
  if ( empty( $skope_id ) ) {
      sek_error_log( __FUNCTION__ . ' => empty skope id or location => collection setting id impossible to build' );
  }
  return NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . "[{$skope_id}]";
}


/* ------------------------------------------------------------------------- *
 *  HELPER FOR CHECKBOX OPTIONS
/* ------------------------------------------------------------------------- */
function sek_is_checked( $val ) {
    //cast to string if array
    $val = is_array($val) ? $val[0] : $val;
    return sek_booleanize_checkbox_val( $val );
}

function sek_booleanize_checkbox_val( $val ) {
    if ( !$val || is_array( $val ) ) {
      return false;
    }
    if ( is_bool( $val ) && $val )
      return true;
    switch ( (string) $val ) {
      case 'off':
      case '' :
      case 'false' :
        return false;
      case 'on':
      case '1' :
      case 'true' :
        return true;
      default : return false;
    }
}




/* ------------------------------------------------------------------------- *
 *  Nimble Widgets Areas
/* ------------------------------------------------------------------------- */
// @return the list of Nimble registered widget areas
function sek_get_registered_widget_areas() {
    global $wp_registered_sidebars;
    $widget_areas = array();
    if ( is_array( $wp_registered_sidebars ) && !empty( $wp_registered_sidebars ) ) {
        foreach ( $wp_registered_sidebars as $registered_sb ) {
            $id = $registered_sb['id'];
            if ( !sek_is_nimble_widget_id( $id ) )
              continue;
            $widget_areas[ $id ] = $registered_sb['name'];
        }
    }
    return $widget_areas;
}
// @return bool
// @ param $id string
function sek_is_nimble_widget_id( $id ) {
    // NIMBLE_WIDGET_PREFIX = nimble-widget-area-
    return NIMBLE_WIDGET_PREFIX === substr( $id, 0, strlen( NIMBLE_WIDGET_PREFIX ) );
}

// @return bool
// introduced for #883
function sek_is_widget_module_disabled() {
    return sek_booleanize_checkbox_val( get_option( NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE ) );
}



/* ------------------------------------------------------------------------- *
 *  Beta Features
/* ------------------------------------------------------------------------- */
// December 2018 => preparation of the header / footer feature
// The beta features can be control by a constant
// and by a global option
function sek_are_beta_features_enabled() {
    $global_beta_feature = sek_get_global_option_value( 'beta_features');
    if ( is_array( $global_beta_feature ) && array_key_exists('beta-enabled', $global_beta_feature ) ) {
          return (bool)$global_beta_feature['beta-enabled'];
    }
    return NIMBLE_BETA_FEATURES_ENABLED;
}

/* ------------------------------------------------------------------------- *
 *  PRO
/* ------------------------------------------------------------------------- */
function sek_is_pro() {
    return defined('NB_PRO_VERSION');
}



/* ------------------------------------------------------------------------- *
 *  VERSION HELPERS
/* ------------------------------------------------------------------------- */
/**
* Returns a boolean
* check if user started to use the plugin before ( strictly < ) the requested version
* @param $_ver : string free version
*/
function sek_user_started_before_version( $requested_version ) {
    $started_with = get_option( 'nimble_started_with_version' );
    //the transient is set in HU_utils::hu_init_properties()
    if ( !$started_with )
      return false;

    if ( !is_string( $requested_version ) )
      return false;

    return version_compare( $started_with , $requested_version, '<' );
}



/* ------------------------------------------------------------------------- *
 *   VARIOUS HELPERS
/* ------------------------------------------------------------------------- */
function sek_text_truncate( $text, $max_text_length, $more, $strip_tags = true ) {
    if ( !$text )
        return '';

    if ( $strip_tags )
        $text       = strip_tags( $text );

    if ( !$max_text_length )
        return $text;

    $end_substr = $text_length = strlen( $text );
    if ( $text_length > $max_text_length ) {
        $text      .= ' ';
        $end_substr = strpos( $text, ' ' , $max_text_length);
        $end_substr = ( FALSE !== $end_substr ) ? $end_substr : $max_text_length;
        $text       = trim( substr( $text , 0 , $end_substr ) );
    }

    if ( $more && $end_substr < $text_length )
        return $text . ' ' .$more;

    return $text;
}


// @return a bool
// typically when
// previewing a changeset on front with a link generated in the publish menu of the customizer
// looking like : mysite.com/?customize_changeset_uuid=67862e7f-427c-4183-b3f7-62eb86f79899
// in this case the $_REQUEST super global, doesn't include a customize_messenger_channel paral
// added when fixing https://github.com/presscustomizr/nimble-builder/issues/351
function sek_is_customize_previewing_a_changeset_post() {
    return !( defined('DOING_AJAX') && DOING_AJAX ) && is_customize_preview() && !isset( $_REQUEST['customize_messenger_channel']);
}




// @return string theme name
// always return the parent theme name
function sek_get_parent_theme_slug() {
    $theme_slug = get_option( 'stylesheet' );
    // $_REQUEST['theme'] is set both in live preview and when we're customizing a non active theme
    $theme_slug = sanitize_text_field( isset($_REQUEST['theme']) ? $_REQUEST['theme'] : $theme_slug ); //old wp versions
    $theme_slug = sanitize_text_field( isset($_REQUEST['customize_theme']) ? $_REQUEST['customize_theme'] : $theme_slug );

    //gets the theme name (or parent if child)
    $theme_data = wp_get_theme( $theme_slug );
    if ( $theme_data->parent() ) {
        $theme_slug = $theme_data->parent()->Name;
    }

    return sanitize_file_name( strtolower( $theme_slug ) );
}




function sek_error_log( $title, $content = null ) {
    // Know in which function sek_error_log() was called
    // debug_backtrace()[1]['function'];
    $backtrace = debug_backtrace();
    //error_log( print_r( $backtrace, true ) );
    $content = is_null( $content ) ? '' : $content;
    if ( !sek_is_dev_mode() )
      return;

    $btrace = '';
    if ( is_array($backtrace) && isset($backtrace[1]) ) {
        if ( !empty( $backtrace[1]['file'] ) && !empty( $backtrace[1]['line'] ) ) {
            $btrace = $btrace . "\n ====> " . $backtrace[1]['file'] . '#' . $backtrace[1]['line'];
        }
        if ( !empty( $backtrace[1]['class'] ) && !empty( $backtrace[1]['function'] ) ) {
            $btrace = $btrace . "\n ====> " . $backtrace[1]['class'] . '::' . $backtrace[1]['function'];
        } else if ( !empty( $backtrace[1]['function']) ) {
            $btrace = $btrace . "\n ====> " . $backtrace[1]['function'];
        }
    }
    if ( is_null( $content ) ) {
        error_log( '<' . $title . '>' );
        if ( !empty($btrace) ) {
          error_log( '// backtrace :' . $btrace . '//' );
        }
    } else {
        error_log( '<' . $title . '>' );
        error_log( print_r( $content, true ) );
        if ( !empty($btrace) ) {
          error_log( '// backtrace :' . $btrace . '//' );
        }
        error_log( '</' . $title . '>' );
    }
    
}




// /* ------------------------------------------------------------------------- *
// *  HELPERS FOR ADMIN AND API TO DETERMINE / CHECK CURRENT THEME NAME
// /* ------------------------------------------------------------------------- */
// @return bool
function sek_is_presscustomizr_theme( $theme_name ) {
  $bool = false;
  if ( is_string( $theme_name ) ) {
    foreach ( ['customizr', 'hueman'] as $pc_theme ) {
      // handle the case when the theme name looks like customizr-4.1.29
      if ( !$bool && $pc_theme === substr( $theme_name, 0, strlen($pc_theme) ) ) {
          $bool = true;
      }
    }
  }
  return $bool;
}

// @return the theme name string, exact if customizr or hueman
function sek_maybe_get_presscustomizr_theme_name( $theme_name ) {
  if ( is_string( $theme_name ) ) {
    foreach ( ['customizr', 'hueman'] as $pc_theme ) {
      // handle the case when the theme name looks like customizr-4.1.29
      if ( $pc_theme === substr( $theme_name, 0, strlen($pc_theme) ) ) {
          $theme_name = $pc_theme;
      }
    }
  }
  return $theme_name;
}

// @return a string
function sek_get_th_start_ver( $theme_name ) {
  if ( !in_array( $theme_name, ['customizr', 'hueman'] ) )
    return '';
  $start_ver = '';
  switch( $theme_name ) {
      case 'customizr' :
          $start_ver = defined( 'CZR_USER_STARTED_USING_FREE_THEME' ) ? CZR_USER_STARTED_USING_FREE_THEME : '';
      break;
      case 'hueman' :
          $start_ver = get_transient( 'started_using_hueman' );
      break;
  }
  return $start_ver;
}




/* ------------------------------------------------------------------------- *
 *  STRIP SCRIPT TAG WHEN CUSTOMIZING
 *  to prevent customizer breakages. See https://github.com/presscustomizr/nimble-builder/issues/688
/* ------------------------------------------------------------------------- */
function sek_strip_script_tags_and_print_js_inline( $html, $model ) {
    if ( !is_string( $html ) )
        return $html;
    if ( skp_is_customizing() ) {
        // June 2020 => added a notice for https://github.com/presscustomizr/nimble-builder/issues/710
        $script_notice = sprintf('<div class="nimble-notice-in-preview"><i class="fas fa-info-circle"></i>&nbsp;%1$s</div>',
        __('Custom javascript code is not executed when customizing.', 'text-doma')
        );
        return preg_replace('#<script(.*?)>(.*?)</script>#is', $script_notice, $html);
    } else {
        $js = "";
        // Get the script tags content
        preg_match_all('/<script>(.*?)<\/script>/s', $html, $matches);
        foreach ($matches[1] as $value) {
            if (!empty($value)) {
                $js .= $value .";\n";
            }
        }
        
        // Remove the scripts from the content
        $html = preg_replace('#<script(.*?)</script>#is', '', $html);
        
        // Print scripts inline safely
        if ( !empty($js) ) {
            wp_register_script( 'nb_module_custom_js_' . $model['id'], '');
            wp_enqueue_script( 'nb_module_custom_js_' . $model['id'] );
            wp_add_inline_script( 'nb_module_custom_js_' . $model['id'], $js );
        }
        return $html;
    }
}

function sek_strip_script_tags( $html = '' ) {
      if (!is_string( $html ) ) {
          return $html;
      }
      return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
}

// @return bool
// Introduced May 2020
function sek_current_user_can_access_nb_ui() {
    return apply_filters('nb_user_has_access', true );
}




/* ------------------------------------------------------------------------- *
 *  TRANSIENTS
/* ------------------------------------------------------------------------- */
// July 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/730
function sek_clean_transients_like( $transient_string ) {
    global $wpdb;
    $where_like = '%'.$transient_string.'%';
    $sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
            FROM  $wpdb->options
            WHERE `option_name` LIKE '$where_like'
            ORDER BY `option_name`";

    $results = $wpdb->get_results( $sql );
    $transients = array();

    // TRANSIENTS
    foreach ( $results as $result ) {
        if ( 0 === strpos( $result->name, '_transient' ) ) {
            if ( 0 === strpos( $result->name, '_transient_timeout_') ) {
                $transients['transient_timeout'][ $result->name ] = $result->value;
            } else {
                $transients['transient'][ $result->name ] = maybe_unserialize( $result->value );
            }
        }
    }

    //sek_error_log('CLEAN PAST TRANSIENTS ' . $transient_string, $transients );

    // Clean the transients found
    // transient name looks like _transient_section_json_transient_2.1.5
    // when deleted, it also removes the associated _transient_timeout_... option
    foreach ($transients as $group => $list) {
        if ( 'transient' === $group && is_array($list) ) {
            foreach ($list as $name => $val) {
              // the transient name does not include the "_transient_" prefix
              $trans_name = substr($name, strlen('_transient_') );
              delete_transient( $trans_name );
            }
        }
    }
}





/* ------------------------------------------------------------------------- *
 *  OPTIONS CLEANING
/* ------------------------------------------------------------------------- */
//  introduced for https://github.com/presscustomizr/nimble-builder/issues/826
function sek_clean_options_starting_like( $opt_string ) {
    global $wpdb;
    $where_like = '%'.$opt_string.'%';
    $sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
            FROM  $wpdb->options
            WHERE `option_name` LIKE '$where_like'
            ORDER BY `option_name`";

    $results = $wpdb->get_results( $sql );
    if ( !is_array( $results ) )
      return;

    foreach ( $results as $result ) {
        if ( 0 === strpos( $result->name, $opt_string ) ) {
            delete_option( $result->name );
        }
    }
}






// July 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/720
// @param $features (string) list of features
function sek_get_pro_notice_for_czr_input( $features = '' ) {
  if ( !sek_is_upsell_enabled() )
    return '';
  return sprintf( '<hr/><p class="sek-pro-notice"><img class="sek-pro-icon" src="%1$s"/><span class="sek-pro-notice-icon-bef-text"><img src="%2$s"/></span><span class="sek-pro-notice-text">%3$s : %4$s<br/><br/>%5$s</span><p>',
      NIMBLE_BASE_URL.'/assets/czr/sek/img/pro_white.svg?ver='.NIMBLE_VERSION,
      NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION,
      __('Unlock more features with Nimble Builder Pro', 'text-doma'),
      $features,
      sprintf('<a href="%1$s" rel="noopener noreferrer" title="%2$s" target="_blank">%2$s <i class="fas fa-external-link-alt"></i></a>',
          'https://presscustomizr.com/nimble-builder-pro/?utm_source=usersite&utm_medium=link&utm_campaign=czr',
          __('Go Pro', 'text-doma')
      )
  );
}


// September 2020 : filter the collection of pre-built sections
// Removes pro upsell modules if sek_is_upsell_enabled() is false
// filter declared in _front_dev_php/_constants_and_helper_functions/0_5_2_sektions_local_sektion_data.php
add_filter('sek_get_raw_section_registration_params', function( $collection ) {
    if ( sek_is_pro() )
      return $collection;
    if ( sek_is_upsell_enabled() )
      return $collection;

    $filtered = [];
    foreach ($collection as $section_group_name => $group_data) {
        $filtered[$section_group_name] = $group_data;
        foreach ( $group_data['section_collection'] as $sec_key => $sec_data) {
            if ( array_key_exists('is_pro', $sec_data) && $sec_data['is_pro'] ) {
                unset($filtered[$section_group_name]['section_collection'][$sec_key]);
            }
        }
    }
    return $filtered;
});

// inspired from https://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string-for-use-in-a-secret-l
// equivalent of js customizer method CZRSeksPrototype::guid()
// introduced feb 2021 for https://github.com/presscustomizr/nimble-builder/issues/478
// used when generating id server side for a site template
function sek_generate_level_guid() {
    return NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED . substr( strval( md5( uniqid( rand(), true) ) ),0, 12 );//__nimble__4cdf8be5ce8f
}


function sek_is_upsell_enabled() {
  return !sek_is_pro();
}

?>