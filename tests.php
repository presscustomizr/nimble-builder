<?php
namespace Nimble;

/* ------------------------------------------------------------------------- *
 * FOR TEST
/* ------------------------------------------------------------------------- */
function render_test_logs( $query = null ) {
    if ( ! skp_is_customizing() )
      return;
    if ( is_object( $query ) && is_a( $query, 'WP_Query' ) && ! $query->is_main_query() ) {
        return;
    }
    $skope_id = skp_build_skope_id();
    //delete_option( "nimble___{$skope_id}" );
    /* if ( is_array(') )
      array_walk_recursive(', function(&$v) { $v = htmlspecialchars($v); }); */
    ?>

      <div>
        <h2>GLOBAL OPTIONS</h2>
        <pre style="font-size: 0.6em;overflow:visible;">
          <?php print_r( get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS )  ); ?>
        </pre>
      </div>
      <br/>

      <div>
        <h2>SEKTIONS</h2>
        <pre style="font-size: 0.6em;overflow:visible;">
          <?php print_r( sek_get_skoped_seks( $skope_id )  ); ?>
        </pre>
      </div>
    <?php
}
add_action('loop_end', '\Nimble\render_test_logs', 50 );
add_action('nimble_after_content_sections', '\Nimble\render_test_logs', 50 );//@see tmpl/page-templates/nimble_template.php