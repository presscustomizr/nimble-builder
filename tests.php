<?php
namespace Nimble;

/* ------------------------------------------------------------------------- *
 * FOR TEST
/* ------------------------------------------------------------------------- */
add_action('loop_end', function() {
    if ( ! skp_is_customizing() )
      return;
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
}, 50 );