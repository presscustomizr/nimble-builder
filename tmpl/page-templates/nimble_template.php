<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

get_header();

do_action('nimble_template_before_content_sections');
  render_nimble_locations(
      array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
      array(
          // the location rendered even if empty.
          // This way, the user starts customizing with only one location for the content instead of four
          // But if the other locations were already customized, they will be printed.
          'fallback_location' => 'loop_start'
      )
  );
do_action('nimble_template_after_content_sections');

get_footer();