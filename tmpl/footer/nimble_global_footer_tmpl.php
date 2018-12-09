<?php
// nimble_full_tmpl_ghf =>  nimble full tmpl with global header and footer
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
?>

      </div><!-- #nimble-content -->
      <footer id="nimble-footer" class="">
        <?php Nimble_Manager()->render_nimble_locations('nimble_global_footer'); ?>
      </footer><!-- #nimble-footer -->
    </div><!-- #nimble-page -->
  <?php wp_footer(); ?>
</body>
</html>