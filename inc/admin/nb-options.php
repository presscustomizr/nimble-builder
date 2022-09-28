<?php
namespace Nimble;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------------------- *
*  REGISTER PAGE
/* ------------------------------------------------------------------------- */
// function nb_register_settings() {
//    add_option( 'myplugin_option_name', 'This is my option value.');
//    register_setting( 'myplugin_options_group', 'myplugin_option_name', '\Nimble\myplugin_callback' );
// }
// add_action( 'admin_init', '\Nimble\nb_register_settings' );

function nb_register_options_page() {
  if ( !sek_current_user_can_access_nb_ui() )
    return;
  add_options_page(
    apply_filters( 'nb_admin_settings_title', __('Nimble Builder', 'text-domain') ),
    apply_filters( 'nb_admin_settings_title', __('Nimble Builder', 'text-domain') ),
    'manage_options',
    NIMBLE_OPTIONS_PAGE,
    '\Nimble\nb_options_page'
  );
}
add_action( 'admin_menu', '\Nimble\nb_register_options_page');

// callback of add_options_page()
// fired @'admin_menu'
function nb_options_page() {
  $option_tabs = Nimble_Manager()->admin_option_tabs;
  $active_tab_id = nb_get_active_option_tab();
  $default_title = esc_html( get_admin_page_title() );
  $page_title = isset( $option_tabs[$active_tab_id] ) ? $option_tabs[$active_tab_id]['page_title'] : $default_title;
  $page_title = empty($page_title) ? $default_title : $page_title;
  ?>

  <div id="nimble-options" class="wrap">
      <h1 class="nb-option-page-title">
        <?php
        printf('<span class="sek-nimble-title-icon"><img src="%1$s" alt="Build with Nimble Builder"></span>',
            esc_url( NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION )
        );
        echo apply_filters( 'nimble_parse_admin_text', esc_html( $page_title ) ) . wp_kses_post( apply_filters( 'nimble_option_title_icon_after', '' ) );
        ?>
      </h1>
      <div class="nav-tab-wrapper">
          <?php
            $allowed_tags = array(
              'div' => array('class'=>true),
              'span' => array('class'=>true),
              'img' => array('class'=>true, 'src'=>true, 'alt'=>true),
            ); 
            foreach ($option_tabs as $tab_id => $tab_data ) {
              printf('<a class="nav-tab %1$s" href="%2$s">%3$s</a>',
                  $tab_id === nb_get_active_option_tab() ? 'nav-tab-active' : '',
                  esc_url( admin_url( NIMBLE_OPTIONS_PAGE_URL ) . '&tab=' . $tab_id ),
                  wp_kses($tab_data['title'], $allowed_tags)
              );
            }
          ?>
      </div>
      <div class="tab-content-wrapper">
        <?php
          $_cb = $option_tabs[$active_tab_id]['content'];
          if( is_string( $_cb ) && !empty( $_cb ) ) {
            if ( function_exists( $_cb ) ) {
              call_user_func( $_cb );
            } else {
              echo esc_attr($_cb);
            }
          } else if ( is_array($_cb) && 2 == count($_cb) ) {
            if ( is_object($_cb[0]) ) {
              $to_return = call_user_func( array( $_cb[0] ,  $_cb[1] ) );
            }
            //instantiated with an instance property holding the object ?
            else if ( class_exists($_cb[0]) ) {

              /* PHP 5.3- compliant*/
              $class_vars = get_class_vars( $_cb[0] );

              if ( isset( $class_vars[ 'instance' ] ) && method_exists( $class_vars[ 'instance' ], $_cb[1]) ) {
                $to_return = call_user_func( array( $class_vars[ 'instance' ] ,  $_cb[1] ) );
              }

              else {
                $_class_obj = new $_cb[0]();
                if ( method_exists($_class_obj, $_cb[1]) )
                  $to_return = call_user_func( array( $_class_obj, $_cb[1] ) );
              }
            }
          }
        ?>
      <div>
  </div><!-- .wrap -->
  <?php
}


/* ------------------------------------------------------------------------- *
*  ADD SETTINGS LINKS
/* ------------------------------------------------------------------------- */
function nb_settings_link($links) {
    $doc_link = sprintf('<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', 'https://docs.presscustomizr.com/article/337-getting-started-with-the-nimble-builder-plugin', __('Docs', 'text-doma') );
    array_unshift($links, $doc_link );
    $settings_link = sprintf('<a href="%1$s">%2$s</a>',
        add_query_arg( array( 'tab' => 'options' ), admin_url( NIMBLE_OPTIONS_PAGE_URL ) ),
        __('Settings', 'text-doma')
    );
    array_unshift($links, $settings_link );
    return $links;
}
add_filter("plugin_action_links_".plugin_basename(NIMBLE_PLUGIN_FILE), '\Nimble\nb_settings_link' );


/* ------------------------------------------------------------------------- *
*  SAVE OPTION HOOK + CUSTOMIZABLE REDIRECTION
/* ------------------------------------------------------------------------- */
// fired @'admin_post'
function nb_save_options() {
    do_action('nb_admin_post');
    //wp_safe_redirect( urldecode( admin_url( NIMBLE_OPTIONS_PAGE_URL ) ) );
    nb_admin_redirect();
}
add_action( 'admin_post', '\Nimble\nb_save_options' );


// fired @'admin_post'
function nb_admin_redirect() {
    $url = sanitize_text_field(
            wp_unslash( $_POST['_wp_http_referer'] ) // Input var okay.
    );
    // Default option url : urldecode( admin_url( NIMBLE_OPTIONS_PAGE_URL ) )
    $url = urldecode( $url );
    $url = empty($url) ? urldecode( admin_url( NIMBLE_OPTIONS_PAGE_URL ) ) : $url;
    // Finally, redirect back to the admin page.
    // Note : filter 'nimble_admin_redirect_url' is used in NB pro to add query params used to display warning/error messages
    wp_safe_redirect( apply_filters('nimble_admin_redirect_url', $url ) );
    exit;
}

// @return bool
function nb_has_valid_nonce( $option_group = 'nb-options-save', $nonce = 'nb-options-nonce' ) {
    // If the field isn't even in the $_POST, then it's invalid.
    if ( !isset( $_POST[$nonce] ) ) { // Input var okay.
        return false;
    }
    return wp_verify_nonce( wp_unslash( $_POST[$nonce] ), $option_group );
}


/* ------------------------------------------------------------------------- *
*  REGISTER TABS
/* ------------------------------------------------------------------------- */
Nimble_Manager()->admin_option_tabs = array();
// @return void
function nb_register_option_tab( $tab ) {
    $tab = wp_parse_args( $tab, array(
        'id' => '',
        'title' => '',
        'page_title' => '',
        'content' => '',
    ));
    Nimble_Manager()->admin_option_tabs[$tab['id']] = $tab;
}



function nb_get_active_option_tab() {
    // check that we have a tab param and that this tab is registered
    $tab_id = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : 'welcome';
    if ( !array_key_exists( $tab_id, Nimble_Manager()->admin_option_tabs ) ) {
        sek_error_log( __FUNCTION__ . ' error => invalid tab');
        $tab_id = 'welcome';
    }
    return $tab_id;
}

/* ------------------------------------------------------------------------- *
*  WELCOME PAGE
/* ------------------------------------------------------------------------- */
nb_register_option_tab([
    'id' => 'welcome',
    'title' => __('Welcome', 'text-doma'),
    'page_title' => __('Nimble Builder', 'nimble' ),
    'content' => '\Nimble\print_welcome_page',
]);
function print_welcome_page() {
    ?>
    <div class="nimble-welcome-content">
      <?php echo wp_kses_post(sek_get_welcome_block()); ?>
    </div>
    <div class="clear"></div>
    <hr/>
    <div>
      <h2><?php _e('Watch the video below for a brief overview of Nimble Builder features', 'text-doma'); ?></h2>
      <iframe src="https://player.vimeo.com/video/328473405?loop=1&title=0&byline=0&portrait=0" width="640" height="424" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
    </div>

    <?php
}

/* ------------------------------------------------------------------------- *
*  DATA CLEANING for #826
/* ------------------------------------------------------------------------- */
// Fired when click on button in admin options
// introduced for https://github.com/presscustomizr/nimble-builder/issues/826
  function sek_clean_all_nimble_data() {
    if ( !isset( $_GET['clean_nb'] ) )
      return;

    // Do we have a nonce passed ?
    if ( !isset( $_GET['ecnon'] ) )
      return;

    // validate the nonce and verify the user as permission to save.
    if ( !wp_verify_nonce( wp_unslash( $_GET['ecnon'] ), 'nb-base-options' ) || !current_user_can( 'manage_options' ) )
      return;

    // Nimble CPT for skoped sections, for user templates, for user sections
    $nb_cpt_list = [ 'NIMBLE_CPT', 'NIMBLE_TEMPLATE_CPT', 'NIMBLE_SECTION_CPT' ];
    foreach( $nb_cpt_list as $nb_cpt ) {
        if ( !defined( $nb_cpt ) )
          continue;
        $nb_cpt = constant($nb_cpt);
        $query = new \WP_Query(
          array(
            'post_type'              => $nb_cpt,
            'post_status'            => get_post_stati(),
            'posts_per_page'         => -1,
            'no_found_rows'          => true,
            'cache_results'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'lazy_load_term_meta'    => false,
          )
        );
        if ( !is_array( $query->posts ) || empty( $query->posts ) ) {
          continue;
        }
        foreach ( $query->posts as $post_object ) {
          //permanently delete post ( unlike wp_trash_post() )
          wp_delete_post($post_object->ID);
        }
    }

    // Nimble options
    $nb_opts = [
      'NIMBLE_OPT_SEKTION_POST_INDEX',
      'NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS',
      'NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS',
      'NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS',
      'NIMBLE_OPT_FOR_GLOBAL_CSS',
      'NIMBLE_OPT_NAME_FOR_SECTION_JSON',
      'NIMBLE_OPT_NAME_FOR_BACKWARD_FIXES',
      // admin options
      'NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING',
      'NIMBLE_OPT_NAME_FOR_DEBUG_MODE',
      'NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS'
    ];
    foreach( $nb_opts as $opt_name ) {
      if ( !defined( $opt_name ) )
        continue;
      $opt_name = constant($opt_name);
      delete_option( $opt_name );
    }

    // clean other options like : nimble_start_date, nimble_started_with_version, nimble_version,
    foreach( [
      'nimble_start_date',
      'nimble_started_with_version',
      'nimble_version_upgraded_from',
      'nimble_version',
      'nimble_last_update_notice',
      '__nimble_options__'//<= deprecated option name
    ] as $opt_name ) {
      delete_option( $opt_name );
    }

    // clean options like nimble___skp__post_page_1010 ( old way to map skope_id and skoped post )
    sek_clean_options_starting_like( 'nimble___');
    sek_clean_options_starting_like( 'nimblebuilder_pro_');

    // Nimble transients
    $nb_transients = [
      'NIMBLE_FEEDBACK_NOTICE_ID',
      'NIMBLE_FAWESOME_TRANSIENT_ID',
      'NIMBLE_GFONTS_TRANSIENT_ID',
      'NIMBLE_FEEDBACK_STATUS_TRANSIENT_ID',
      'NIMBLE_API_CHECK_TRANSIENT_ID'
    ];
    foreach( $nb_transients as $trans_id ) {
      if ( !defined( $trans_id ) )
        continue;
      $trans_id = constant($trans_id);
      delete_transient( $trans_id );
    }
    // => remove all other transients
    sek_clean_transients_like( 'nimble_' );//'nimble_api_posts', 'nimble_api_tmpl_' . $tmpl_name
    sek_clean_transients_like( 'nimble_preset_sections_' );//old transient for storing preset sections. Now fetched remotely. See #802
    sek_clean_transients_like( 'section_params_transient' );//old transient that may still be there
    sek_clean_transients_like( 'section_params_transient' );//old transient that may still be there


    // Nimble CSS stylesheets
    $css_dir_list = [ 'NIMBLE_DEPREC_ONE_CSS_FOLDER_NAME', 'NIMBLE_DEPREC_TWO_CSS_FOLDER_NAME', 'NIMBLE_CSS_FOLDER_NAME' ];
    global $wp_filesystem;
    require_once ( ABSPATH . '/wp-admin/includes/file.php' );
    WP_Filesystem();
    $upload_dir = wp_get_upload_dir();

    foreach( $css_dir_list as $css_dir ) {
      if ( !defined( $css_dir ) )
        continue;
      $css_dir = constant( $css_dir );
      if ( is_multisite() ) {
          $site        = get_site();
          $network_id  = $site->site_id;
          $site_id     = $site->blog_id;
          $css_dir     = trailingslashit( $css_dir ) . trailingslashit( $network_id ) . $site_id;
      }

      $folder_path = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $css_dir );
      if ( $wp_filesystem->exists( $folder_path ) ) {
          $wp_filesystem->rmdir( $folder_path, true );
      }
    }
    return 'success';
  }


/* ------------------------------------------------------------------------- *
*  OPTIONS PAGE
/* ------------------------------------------------------------------------- */
nb_register_option_tab([
    'id' => 'options',
    'title' => __('Options', 'text-doma'),
    'page_title' => __('Nimble Builder Options', 'nimble' ),
    'content' => '\Nimble\print_options_page',
]);
function print_options_page() {
    ?>
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><?php _e('Shortcodes', 'text_doma'); ?></th>
          <td>
            <fieldset><legend class="screen-reader-text"><span><?php _e('Shortcodes', 'text_doma'); ?></span></legend>
              <?php
                $shortcode_opt_val = get_option( NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING );
              ?>
              <label for="nb_shortcodes_parsed_in_czr"><input name="nb_shortcodes_parsed_in_czr" type="checkbox" id="nb_shortcodes_parsed_in_czr" value="on" <?php checked( $shortcode_opt_val, 'on' ); ?>>
              <?php _e('Parse shortcodes when building your pages in the customizer', 'text_doma'); ?></label>
              <p class="description"><?php _e('Shortcodes are disabled by default when customizing to prevent any conflicts with Nimble Builder interface.', 'text_doma'); ?></p>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php _e('Widgets Module', 'text_doma'); ?></th>
          <td>
            <fieldset><legend class="screen-reader-text"><span><?php _e('Widgets module', 'text_doma'); ?></span></legend>
              <?php
                $widget_disabled_opt_val = get_option( NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE );
              ?>
              <label for="nb_widgets_disabled_in_czr"><input name="nb_widgets_disabled_in_czr" type="checkbox" id="nb_widgets_disabled_in_czr" value="on" <?php checked( $widget_disabled_opt_val, 'on' ); ?>>
              <?php _e('Disable the Widgets Module', 'text_doma'); ?></label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php _e('Google Fonts', 'text_doma'); ?></th>
          <td>
            <fieldset><legend class="screen-reader-text"><span><?php _e('Disable Google Fonts', 'text_doma'); ?></span></legend>
              <?php
                $nb_debug_mode_opt_val = get_option( NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS );
              ?>
              <label for="nb_google_font_disable"><input name="nb_google_font_disable" type="checkbox" id="nb_google_font_disable" value="on" <?php checked( $nb_debug_mode_opt_val, 'on' ); ?>>
              <?php _e('Activate to disable Google fonts', 'text_doma'); ?></label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php _e('Debug Mode', 'text_doma'); ?></th>
          <td>
            <fieldset><legend class="screen-reader-text"><span><?php _e('Debug Mode', 'text_doma'); ?></span></legend>
              <?php
                $nb_debug_mode_opt_val = get_option( NIMBLE_OPT_NAME_FOR_DEBUG_MODE );
              ?>
              <label for="nb_debug_mode_active"><input name="nb_debug_mode_active" type="checkbox" id="nb_debug_mode_active" value="on" <?php checked( $nb_debug_mode_opt_val, 'on' ); ?>>
              <?php _e('Activate the debug mode when customizing', 'text_doma'); ?></label>
              <p class="description"><?php _e('In debug mode, during customization Nimble Builder deactivates all modules content and prints only the structure of your sections. This lets you troubleshoot, remove or edit your modules safely.', 'text_doma'); ?></p>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <?php
      do_action('nb_admin_options_tab_after_content');
      wp_nonce_field( 'nb-base-options', 'nb-base-options-nonce' );
      submit_button();
    ?>
    </form>
    <hr/>
    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><?php _e('Remove all Nimble Builder data', 'text_doma'); ?></th>
          <td>
            <fieldset><legend class="screen-reader-text"><span><?php _e('Remove all Nimble Builder data', 'text_doma'); ?></span></legend>
              <?php
                $refresh_url = add_query_arg( array( 'tab' => 'options', 'clean_nb' => 'true' ), admin_url( NIMBLE_OPTIONS_PAGE_URL ));
              ob_start();
              ?>
                var nb_toggle_clean_button = function() {
                  jQuery( function($) {
                    $('.nb-clean-traces-confirm').stop().slideToggle('fast');
                  });
                };
                var _nonce_value, _url
                var nb_refresh_opt_page = function() {
                  jQuery( function($) {
                    _nonce_value = $('#nb-base-options-nonce').val();
                    _url = '<?php echo esc_url($refresh_url); ?>';
                    // add nonce as param so NB can verify it when the page reloads
                    if ( _nonce_value ) {
                      _url = _url + '&ecnon=' + _nonce_value;// looks like site.com/wp-admin/options-general.php?page=nb-options&tab=options&clean_nb=true&ecnon=7cc5758b65
                    }
                    window.location.href = _url;
                  });
                };
              <?php
              $script = ob_get_clean();
              wp_register_script( 'nb_options_js', '');
              wp_enqueue_script( 'nb_options_js' );
              wp_add_inline_script( 'nb_options_js', $script );
              ?>
              <?php $clean_nb = isset( $_GET['clean_nb'] ) ? sanitize_text_field($_GET['clean_nb']) : false; ?>
              <?php if ( $clean_nb ) : ?>
                  <?php $status = sek_clean_all_nimble_data(); ?>
                    <?php if ( 'success' === $status ) : ?>
                      <div id="message" class="updated notice">
                        <p class="nb-clean-traces-success"><strong><?php _e('All Nimble Builder data have been successfully removed from your WordPress website.', 'text_doma'); ?></strong></p>
                      </div>
                    <?php else : ?>
                      <div id="message" class="error notice">
                        <p><strong><?php _e('Security problem when trying to remove Nimble Builder data.', 'text_doma'); ?></strong></p>
                      </div>
                    <?php endif; ?>
              <?php else : ?>
                  <p class="description"><?php _e('This will permanently remove all data created by Nimble Builder and stored in your database or as stylesheets : page customizations, custom sections, custom templates, options, CSS stylesheets.', 'text_doma'); ?></p><br/>
                  <button class="button" onclick="window.nb_toggle_clean_button()"><?php _e('Remove now', 'text_doma'); ?></button>
                  <div class="nb-clean-traces-confirm" style="display:none">
                    <p class="description"><?php _e('Once you delete Nimble Builder data, there is no going back. Please be certain. ', 'text_doma'); ?></p><br/>
                    <button class="button nb-permanent-removal-btn" onclick="window.nb_refresh_opt_page()"><?php _e('Yes I want to clean all data', 'text_doma'); ?></button>
                  </div>
              <?php endif; ?>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <?php
}
add_action( 'nb_admin_post', '\Nimble\nb_save_base_options' );
// hook : nb_admin_post
function nb_save_base_options() {
    // First, validate the nonce and verify the user as permission to save.
    if ( !nb_has_valid_nonce( 'nb-base-options', 'nb-base-options-nonce' ) || !current_user_can( 'manage_options' ) )
        return;

    // Shortcode parsing when customizing
    nb_maybe_update_checkbox_option( NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING, 'off' );
    // Widgets disabled when customizing
    nb_maybe_update_checkbox_option( NIMBLE_OPT_NAME_FOR_DISABLING_WIDGET_MODULE, 'off' );
    // Debug mode
    nb_maybe_update_checkbox_option( NIMBLE_OPT_NAME_FOR_DEBUG_MODE, 'off' );
    // Google font disabled
    nb_maybe_update_checkbox_option( NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS, 'off' );
}

// helper to update a checkbox option
// the option is updated only if different than the default val or if the option exists already
function nb_maybe_update_checkbox_option( $opt_name, $unchecked_value ) {
    $opt_value = get_option( $opt_name );
    $posted_value = array_key_exists( $opt_name, $_POST ) ? sanitize_text_field($_POST[$opt_name]) : $unchecked_value;
    if ( $unchecked_value !== $posted_value ) {
        update_option( $opt_name, esc_attr( $posted_value ), 'no' );
    } else {
        // if the option was never set before, then leave it not set
        // otherwise update it to 'off'
        if ( false !== $opt_value ) {
            update_option( $opt_name, $unchecked_value, 'no' );
        }
    }
}

do_action('nb_base_admin_options_registered');



/* ------------------------------------------------------------------------- *
*  RESTRICT USERS
/* ------------------------------------------------------------------------- */
//register option tab and print the form
if ( sek_is_pro() || sek_is_upsell_enabled() ) {
    $restrict_users_title = __('Manage authorized users', 'text-doma');
    if ( !sek_is_pro() ) {
        $restrict_users_title = sprintf( '<span class="sek-pro-icon"><img src="%1$s" alt="Pro feature"></span><span class="sek-title-after-icon">%2$s</span>',
            NIMBLE_BASE_URL.'/assets/czr/sek/img/pro_orange.svg?ver='.NIMBLE_VERSION,
            __('Manage authorized users', 'nimble' )
        );
    }
    nb_register_option_tab([
        'id' => 'restrict_users',
        'title' => $restrict_users_title,
        'page_title' => __('Manage authorized users', 'nimble' ),
        'content' => '\Nimble\print_restrict_users_options_content',
    ]);

    function print_restrict_users_options_content() {
        if ( !sek_is_pro() ) {
          ?>
            <h4><?php _e('Nimble Builder can be used by default by all users with an administrator role. With Nimble Builder Pro you can decide which administrators are allowed to use the plugin.', 'text_domain'); ?></h4>
            <h4><?php _e('Unauthorized users will not see any reference to Nimble Builder when editing a page, in the customizer and in the WordPress admin screens.', 'text_domain') ?></h4>
            <a class="sek-pro-link" href="https://presscustomizr.com/nimble-builder-pro/" rel="noopener noreferrer" title="Go Pro" target="_blank"><?php _e('Go Pro', 'text_domain'); ?> <span class="dashicons dashicons-external"></span></a>
          <?php
        }
        do_action( 'nb_restrict_user_content' );
    }
}


/* ------------------------------------------------------------------------- *
*  SYSTEM INFO
/* ------------------------------------------------------------------------- */
nb_register_option_tab([
    'id' => 'system-info',
    'title' => __('System info', 'text-doma'),
    'page_title' => __('System info', 'nimble' ),
    'content' => '\Nimble\print_system_info',
]);
function print_system_info() {
    require_once( NIMBLE_BASE_PATH . '/inc/admin/system-info.php' );
    ?>
     <h3><?php _e( 'System Informations', 'text_domain_to_be_chg' ); ?></h3>
      <h4><?php _e( 'Please include your system informations when posting support requests.' , 'text_domain_to_be_chg' ) ?></h4>
      <textarea readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="tc-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'text_domain_to_be_chg' ); ?>" style="width: 800px;min-height: 800px;font-family: Menlo,Monaco,monospace;background: 0 0;white-space: pre;overflow: auto;display:block;"><?php echo wp_kses_post(sek_config_infos()); ?></textarea>
    <?php
}

/* ------------------------------------------------------------------------- *
*  DOCUMENTATION
/* ------------------------------------------------------------------------- */
nb_register_option_tab([
    'id' => 'doc',
    'title' => __('Documentation', 'text-doma'),
    'page_title' => __('Nimble Builder knowledge base', 'nimble' ),
    'content' => '\Nimble\print_doc_page',
]);
function print_doc_page() {
    ?>
      <div class="nimble-doc">
          <ul>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/337-getting-started-with-the-nimble-builder-plugin"><span>Getting started with Nimble Page Builder for WordPress</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/386-how-to-access-the-live-customization-interface-of-the-nimble-builder"><span>How to access the live customization interface of Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/371-how-to-start-building-from-a-blank-page-with-the-wordpress-nimble-builder"><span>How to start building from a blank ( full width ) page with WordPress Nimble Builder?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href=" https://docs.presscustomizr.com/article/427-how-to-insert-and-edit-a-module-with-nimble-builder"><span>How to insert and edit a module with Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/358-building-your-header-and-footer-with-the-nimble-builder"><span>How to build your WordPress header and footer with Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/350-how-to-use-shortcodes-from-other-plugins-with-the-nimble-builder-plugin"><span>How to embed WordPress shortcodes in your pages with Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/366-how-to-add-an-anchor-to-a-section-and-integrate-it-into-the-menu-with-the-nimble-page-builder"><span>How to add an anchor to a section and integrate it into the menu with Nimble Page Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/380-how-to-set-a-parallax-background-for-a-section-in-wordpress-with-the-nimble-builder"><span>How to set a parallax background for a section in WordPress with Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/343-designing-for-mobile-devices-with-wordpress-nimble-builder"><span>Designing for mobile devices with the WordPress Nimble Builder</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/414-nimble-builder-and-website-performances"><span>Nimble Builder and website performance 🚀</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/393-how-to-add-post-grids-to-any-wordpress-page-with-nimble-builder"><span>How to add post grids to any WordPress page with Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/372-design-your-404-page-with-the-nimble-builder"><span>How to design your 404 error page with Nimble Builder</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/391-how-to-export-and-import-templates-with-nimble-builder"><span>How to reuse sections and templates with the export / import feature of Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/401-how-to-create-a-video-background-with-nimble-builder-wordpress-plugin"><span>How to create a video background with Nimble Builder WordPress plugin ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/408-how-to-insert-a-responsive-carousel-in-your-wordpress-pages-with-nimble-builder"><span>How to insert a responsive carousel in your WordPress pages with Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/389-how-to-visualize-the-structure-of-the-content-created-with-nimble-builder"><span>How to visualize the structure of the content created with Nimble Builder ?</span></a></li>
            <li><a target="_blank" rel="noopener noreferrer" href="https://docs.presscustomizr.com/article/383-how-to-customize-the-height-of-your-sections-and-columns-with-the-nimble-builder"><span>How to customize the height of your sections and columns with Nimble Builder ?</span></a></li>

          </ul>
        <a href="https://docs.presscustomizr.com/collection/334-nimble-page-builder" target="_blank" class="button button-primary button-hero" rel="noopener noreferrer"><span class="dashicons dashicons-search"></span>&nbsp;<?php _e('Explore Nimble Builder knowledge base', 'text-doma'); ?></a>
      </div>

    <?php
}

?>