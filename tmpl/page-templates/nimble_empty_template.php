<?php
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
<div id="page" class="site">
  <a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'text_domain_to_replace' ); ?></a>
  <header id="nimble-header" class="">
    <?php render_nimble_locations( 'nimble_global_header' ); ?>
  </header><!-- #masthead -->

  <div id="content" class="site-content">
    <?php
      render_nimble_locations(
          array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
          array(
              // the location rendered even if empty.
              // This way, the user starts customizing with only one location for the content instead of four
              // But if the other locations were already customized, they will be printed.
              'fallback_location' => 'loop_start'
          )
      );
    ?>
  </div><!-- #content -->

  <footer id="nimble-footer" class="">
    <?php render_nimble_locations('nimble_global_footer'); ?>
  </footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
get_footer();