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
    if ( did_action( 'nimble_logs_rendered' ) )
      return;
    $skope_id = skp_build_skope_id();
    //delete_option( "nimble___{$skope_id}" );
    /* if ( is_array(') )
      array_walk_recursive(', function(&$v) { $v = htmlspecialchars($v); }); */
    ?>

      <div>
        <h2>GLOBAL OPTIONS</h2>
        <pre style="font-size: 11px;overflow:visible;">
          <?php print_r( get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS )  ); ?>
        </pre>
      </div>
      <br/>

      <div>
        <h2>GLOBAL SEKTIONS</h2>
        <pre style="font-size: 11px;overflow:visible;">
          <?php print_r( sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID )  ); ?>
        </pre>
      </div>

      <div>
        <h2>LOCAL SEKTIONS</h2>
        <pre style="font-size: 11px;overflow:visible;">
          <?php print_r( sek_get_skoped_seks( $skope_id )  ); ?>
        </pre>
      </div>

      <div>
        <h2>REGISTERED LOCATIONS</h2>
        <pre style="font-size: 11px;overflow:visible;">
          <?php print_r( sek_get_locations()  ); ?>
        </pre>
      </div>

      <div>
        <h2>LOCAL SKOPE REVISIONS</h2>
        <pre style="font-size: 11px;overflow:visible;">
          <?php print_r( sek_get_revision_history_from_posts() ); ?>
        </pre>
      </div>
    <?php
    do_action('nimble_logs_rendered');
}
//add_action('loop_end', '\Nimble\render_test_logs', 50 );
add_action('sek_after_location_loop_end', '\Nimble\render_test_logs', 50 );
add_action('nimble_template_after_content_sections', '\Nimble\render_test_logs', 50 );//@see tmpl/page-templates/nimble_template.php