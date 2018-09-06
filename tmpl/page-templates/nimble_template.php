<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

get_header();

do_action('nimble_before_content_sections');
  render_content_sections_for_nimble_template();
do_action('nimble_after_content_sections');

get_footer();