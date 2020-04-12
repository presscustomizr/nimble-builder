<?php
// APRIL 2020 : for https://github.com/presscustomizr/nimble-builder/issues/657
namespace Nimble;
add_action( 'wp_ajax_sek_get_nimble_content_for_yoast', '\Nimble\sek_ajax_get_nimble_content_for_yoast' );
function sek_ajax_get_nimble_content_for_yoast() {
    if ( !is_user_logged_in() ) {
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error(  __FUNCTION__ . ' => missing skope_id' );
    }

    $skope_id = $_POST['skope_id'];

    // Register contextually active modules modules
    sek_register_modules_when_not_customizing_and_not_ajaxing( $skope_id );

    // Capture Nimble content normally rendered on front
    // Make sure to skip header and footer sections
    ob_start();
    foreach ( sek_get_locations() as $loc_id => $loc_params ) {
        $loc_params = is_array($loc_params) ? $loc_params : array();
        $loc_params = wp_parse_args( $loc_params, array('is_header_location' => false, 'is_footer_location' => false ) );
        if ( $loc_params['is_header_location'] || $loc_params['is_footer_location'] )
          continue;
        Nimble_Manager()->_render_seks_for_location( $loc_id, array(), $skope_id );
    }
    $html = ob_get_clean();

    // Remove script tags that could break seo analysis
    $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
    // apply filter the_content => sure of that ?
    $html = apply_filters( 'the_content', $html );
    // Minify html
    $html = preg_replace('/>\s*</', '><', $html);//https://stackoverflow.com/questions/466437/minifying-html
    wp_send_json_success($html);
}

add_action( 'admin_footer', '\Nimble\sek_print_js_for_yoast_analysis' );
function sek_print_js_for_yoast_analysis() {
    if ( ! defined( 'WPSEO_VERSION' ) )
      return;
    // This script is only printed when editing posts and pages
    $current_screen = get_current_screen();
    if ( 'post' !== $current_screen->base )
      return;
    $post = get_post();
    $manually_built_skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'post_' . $post->post_type . '_' . $post->ID );
    // documented here : https://github.com/Yoast/javascript/blob/master/packages/yoastseo/docs/Customization.md
    ?>
    <script id="nimble-add-content-to-yoast-analysis">
        jQuery(function($){
            var NimblePlugin = function() {
                YoastSEO.app.registerPlugin( 'nimblePlugin', {status: 'loading'} );
                wp.ajax.post( 'sek_get_nimble_content_for_yoast', {
                      skope_id : '<?php echo $manually_built_skope_id; ?>'
                }).done( function( nimbleContent ) {
                    YoastSEO.app.pluginReady('nimblePlugin');
                    var _appendNimbleContent = function(originalContent) {
                        return originalContent + nimbleContent;
                    };
                    YoastSEO.app.registerModification( 'content', _appendNimbleContent, 'nimblePlugin', 5 );
                }).fail( function( er ) {
                    console.log('NimblePlugin for Yoast => error when fetching Nimble content.');
                });
            }
            $(window).on('YoastSEO:ready', function() {
                try { new NimblePlugin(); } catch(er){ console.log('Yoast NimblePlugin error', er );}
            });
        });
    </script>
    <?php
}