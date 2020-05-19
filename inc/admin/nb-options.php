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
    __('Nimble Builder', 'text-domain'),
    __('Nimble Builder', 'text-domain'),
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
      <h1 class="nb-option-page-title"><span class="sek-nimble-title-icon"><img src="http://customizr-dev.test/wp-content/plugins/nimble-builder/assets/img/nimble/nimble_icon.svg?ver=' . <?php echo NIMBLE_VERSION; ?>" alt="Build with Nimble Builder"></span><?php echo apply_filters( 'nimble_parse_admin_text', $page_title ); ?></h1>
      <div class="nav-tab-wrapper">
          <?php
            foreach ($option_tabs as $tab_id => $tab_data ) {
              printf('<a class="nav-tab %1$s" href="%2$s">%3$s</a>',
                  $tab_id === nb_get_active_option_tab() ? 'nav-tab-active' : '',
                  admin_url( NIMBLE_OPTIONS_PAGE_URL ) . '&tab=' . $tab_id,
                  $tab_data['title']
              );
            }
          ?>
      </div>
      <div class="tab-content-wrapper">
        <?php
          $content_cb = $option_tabs[$active_tab_id]['content'];
          if( is_string( $content_cb ) && !empty( $content_cb ) ) {
            if ( function_exists( $content_cb ) ) {
              call_user_func( $content_cb );
            } else {
              echo $content_cb;
            }
          }
        ?>
      <div>
      <?php //do_action('nimble-option-content'); ?>
  </div><!-- .wrap -->
  <?php
}


/* ------------------------------------------------------------------------- *
*  ADD SETTINGS LINKS
/* ------------------------------------------------------------------------- */
function nb_settings_link($links) {
  $doc_link = sprintf('<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', 'https://docs.presscustomizr.com/article/337-getting-started-with-the-nimble-builder-plugin', __('Docs', 'text-doma') );
  array_unshift($links, $doc_link );
  $settings_link = sprintf('<a href="%1$s">%2$s</a>', admin_url( NIMBLE_OPTIONS_PAGE_URL ), __('Settings', 'text-doma') );
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
    // Finally, redirect back to the admin page.
    // Note : filter 'nimble_admin_redirect_url' is used in NB pro to add query params used to display warning/error messages
    wp_safe_redirect( apply_filters('nimble_admin_redirect_url', urldecode( admin_url( NIMBLE_OPTIONS_PAGE_URL ) ) ) );
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
    $tab_id = isset( $_GET['tab'] ) ? $_GET['tab'] : 'welcome';
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
    'page_title' => __('Nimble Page Builder', 'nimble' ),
    'content' => '\Nimble\print_welcome_page',
]);
function print_welcome_page() {
    ?>
    <div class="nimble-welcome-content">
      <?php echo sek_get_welcome_block(); ?>
    </div>
    <hr/>
    <h2><?php _e('Watch the video below for a brief overview of Nimble Builder features', 'text-doma'); ?></h2>
    <iframe src="https://player.vimeo.com/video/328473405?loop=1&title=0&byline=0&portrait=0" width="640" height="424" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
    <?php
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
      <textarea readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="tc-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'text_domain_to_be_chg' ); ?>" style="width: 800px;min-height: 800px;font-family: Menlo,Monaco,monospace;background: 0 0;white-space: pre;overflow: auto;display:block;"><?php echo sek_config_infos(); ?></textarea>
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
        <a href="https://docs.presscustomizr.com" target="_blank" class="button button-primary button-hero" rel="noopener noreferrer"><span class="dashicons dashicons-search"></span>&nbsp;<?php _e('Explore Nimble Builder knowledge base', 'text-doma'); ?></a>
      </div>

    <?php
}
?>