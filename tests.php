<?php


/* ------------------------------------------------------------------------- *
 * FOR TEST
/* ------------------------------------------------------------------------- */
add_action('loop_end', function() {
    if ( ! skp_is_customizing() )
      return;
    $skope_id = skp_build_skope_id();
    //delete_option( "sek___{$skope_id}" );
    /* if ( is_array(') )
      array_walk_recursive(', function(&$v) { $v = htmlspecialchars($v); }); */
    ?>
      <pre>
        <?php print_r( sek_get_skoped_seks( $skope_id )  ); ?>
      </pre>
    <?php
}, 50 );