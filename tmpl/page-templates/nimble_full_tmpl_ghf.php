<?php
// DEPRECATED SINCE v1.4.0
// Was introduced in v1.3.2 to allow user creating pages from a blank canvase, with a global header and footer.
//
// Since v1.4.0, header and footer are handled with a separate and more flexible set of options, globally ( site wide ) and locally ( on a by-page basis )
//
// The template has been kept to ensure retro-compatibility with users using if before transitionning to v1.4.0
// nimble_full_tmpl_ghf =>  nimble full tmpl with global header and footer
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
// load the Nimble template which includes a call to wp_head()
load_template( NIMBLE_BASE_PATH . '/tmpl/header/nimble_header_tmpl.php', false );

do_action('nimble_template_before_content_sections');
Nimble_Manager()->render_nimble_locations(
    array_keys( sek_get_local_content_locations() ),//array( 'loop_start', 'before_content', 'after_content', 'loop_end', + other custom registered locations ),
    array(
        // the location rendered even if empty.
        // This way, the user starts customizing with only one location for the content instead of four
        // But if the other locations were already customized, they will be printed.
        'fallback_location' => 'loop_start'
    )
);
do_action('nimble_template_after_content_sections');

// load the Nimble template which includes a call to wp_footer()
load_template( NIMBLE_BASE_PATH . '/tmpl/footer/nimble_footer_tmpl.php', false );