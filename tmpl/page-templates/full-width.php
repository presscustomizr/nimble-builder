<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

get_header();

//  echo sections here.
//  the_content should be a default section in singulars ?
do_action('loop_start');

get_footer();