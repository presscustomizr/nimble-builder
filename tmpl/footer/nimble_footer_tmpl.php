<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/footer/nimble_footer_tmpl.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
  exit;
}
?>
    <?php do_action('before_nimble_footer'); ?>
      </div><!-- #nimble-content -->
      <footer id="nimble-footer" class="">
        <?php Nimble_Manager()->render_nimble_locations( true === Nimble_Manager()->has_local_header_footer ? 'nimble_local_footer' :'nimble_global_footer' ); ?>
        <?php do_action('after_nimble_footer'); ?>
      </footer><!-- #nimble-footer -->
    </div><!-- #nimble-page -->
  <?php wp_footer(); ?>
</body>
</html>