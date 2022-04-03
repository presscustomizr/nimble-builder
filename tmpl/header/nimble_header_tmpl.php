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
    $header_classes = apply_filters( 'nb_header_css_classes', array() );
    if ( is_array( $header_classes ) ) {
        $header_classes = implode( ' ', $header_classes );
    }
    $header_classes = is_string($header_classes) ? $header_classes : '';
    ?>
    <div id="nimble-page" class="">
      <a class="sek-skip-link sek-screen-reader-text" href="#nimble-page"><?php _e( 'Skip to content', 'text_domain_to_replace' ); ?></a>
      <header id="nimble-header" class="<?php echo esc_attr($header_classes); ?>">
        <?php do_action('before_nimble_header'); ?>
        <?php Nimble_Manager()->render_nimble_locations( true === Nimble_Manager()->has_local_header_footer ? 'nimble_local_header' :'nimble_global_header' ); ?>
      </header><!-- #nimble-header -->
      <div id="nimble-content" class="">
        <?php
          // Note : hook 'after_nimble_header' is used by Customizr theme to render NB sections located in Customizr's hook '__after_header'
          // it must be printed inside #nimble-content because NB pro top-margin used by header visibility setting is always assigned to nimble-content
          // see https://github.com/presscustomizr/nimble-builder/issues/854
        ?>
        <?php do_action('after_nimble_header'); ?>
