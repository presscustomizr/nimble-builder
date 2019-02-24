<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>
      </div><!-- #nimble-content -->
      <?php do_action('before_nimble_footer'); ?>
      <footer id="nimble-footer" class="">
        <?php Nimble_Manager()->render_nimble_locations( true === Nimble_Manager()->has_local_header_footer ? 'nimble_local_footer' :'nimble_global_footer' ); ?>
      </footer><!-- #nimble-footer -->
      <?php do_action('after_nimble_footer'); ?>
    </div><!-- #nimble-page -->
  <?php wp_footer(); ?>
</body>
</html>