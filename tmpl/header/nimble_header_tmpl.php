<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/header/nimble_header_tmpl.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
  exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="profile" href="https://gmpg.org/xfn/11" />
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>
    <?php
    if ( function_exists( 'wp_body_open' ) ) {
      wp_body_open();
    } else {
      do_action( 'wp_body_open' );
    }
    ?>
    <div id="nimble-page" class="">
      <a class="sek-skip-link sek-screen-reader-text" href="#nimble-page"><?php _e( 'Skip to content', 'text_domain_to_replace' ); ?></a>
      <?php do_action('before_nimble_header'); ?>
      <header id="nimble-header" class="">
        <?php Nimble_Manager()->render_nimble_locations( true === Nimble_Manager()->has_local_header_footer ? 'nimble_local_header' :'nimble_global_header' ); ?>
      </header><!-- #nimble-header -->
      <?php do_action('after_nimble_header'); ?>
      <div id="nimble-content" class="">
