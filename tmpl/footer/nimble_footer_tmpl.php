<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

      </div><!-- #nimble-content -->
      <footer id="nimble-footer" class="">
        <?php Nimble_Manager()->render_nimble_locations( Nimble_Manager()->has_local_header_footer ? 'nimble_local_footer' :'nimble_global_footer' ); ?>
      </footer><!-- #nimble-footer -->
    </div><!-- #nimble-page -->
  <?php wp_footer(); ?>
</body>
</html>