<?php
// nimble_full_tmpl_ghf =>  nimble full tmpl with global header and footer
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
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
    <div id="nimble-page" class="">
      <a class="sek-skip-link sek-screen-reader-text" href="#nimble-page"><?php _e( 'Skip to content', 'text_domain_to_replace' ); ?></a>
      <header id="nimble-header" class="">
        <?php Nimble_Manager()->render_nimble_locations( 'nimble_global_header' ); ?>
      </header><!-- #nimble-header -->
      <div id="nimble-content" class="">
