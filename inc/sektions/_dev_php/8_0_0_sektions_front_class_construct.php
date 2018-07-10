<?php
////////////////////////////////////////////////////////////////
// SEK Front Class
if ( ! class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $local_seks = 'not_cached';// <= used to cache the sektions for the local skope_id
        public $model = array();//<= when rendering, the current level model
        public $parent_model = array();//<= when rendering, the current parent model
        public $default_models = array();// <= will be populated to cache the default models when invoking sek_get_default_module_model
        public $cached_input_lists = array(); // <= will be populated to cache the input_list of each registered module. Useful when we need to get info like css_selector for a particular input type or id.
        public $ajax_action_map = array();

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SEK_Front_Render ) )
              self::$instance = new SEK_Front_Render_Css( $params );
            return self::$instance;
        }

        /////////////////////////////////////////////////////////////////
        // <CONSTRUCTOR>
        function __construct( $params = array() ) {
            //AJAX
            $this -> _schedule_front_ajax_actions();
            // ASSETS
            $this -> _schedule_front_and_preview_assets_printing();
            // RENDERING
            $this -> _schedule_front_rendering();
            // RENDERING
            $this -> _setup_hook_for_front_css_printing_or_enqueuing();

            // TEST
            //add_action( 'wp_ajax_sek_import_attachment', array( $this, '__import__' ) );
        }//__construct

        /////////////////////////////////////////////////////////////////
        // TEST IMG IMPORT
        // hook : wp_ajax_sek_import_attachment
        function __import__() {
            $relative_path = $_POST['rel_path'];

            // Generate the file name from the url.
            $filename = 'nimble_asset_' . basename( $relative_path );
            $args = array(
                'posts_per_page' => 1,
                'post_type'      => 'attachment',
                'name'           => trim ( $filename ),
            );

            // Make sure this img has not already been uploaded
            $get_attachment = new WP_Query( $args );
            //error_log( print_r( $get_attachment->posts, true ) );
            if ( is_array( $get_attachment->posts ) && array_key_exists(0, $get_attachment->posts) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => file already uploaded : ' . $relative_path );
                return;
            }

            // Does it exists ?
            //error_log( "dirname(__FILE__ ) . $relative_path => " . dirname(__FILE__ ) . $relative_path );
            //error_log("file_exists( dirname(__FILE__ ) . $relative_path => " . file_exists( dirname(__FILE__ ) . $relative_path ) );
            if ( ! file_exists( dirname(__FILE__ ) . $relative_path ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no file found for relative path : ' . $relative_path );
                return;
            }

            // Does it return a 200 code ?
            $url = NIMBLE_BASE_URL . '/inc/sektions'. $relative_path;
            //error_log('$url' .$url );
            $url_content = wp_safe_remote_get( $url );
            if ( '404' == $url_content['response']['code'] ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => 404 response when wp_safe_remote_get() url : ' . $url );
                return;
            }
            $file_content = wp_remote_retrieve_body( $url_content );
            //error_log( print_r( $img_content['response'], true ) );

            // Is it something ?
            if ( empty( $file_content ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => empty file_content when wp_remote_retrieve_body() for url : ' . $url );
                return;
            }

            $upload = wp_upload_bits(
              $filename,
              '',
              $file_content
            );

            $attachment = [
              'post_title' => $filename,
              'guid' => $upload['url'],
            ];

            // Set the mime type
            $info = wp_check_filetype( $upload['file'] );
            if ( $info ) {
                $attachment['post_mime_type'] = $info['type'];
            } else {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no info available with wp_check_filetype() when setting the mime type of img : ' . $url );
                return;
            }

            $attachment_id = wp_insert_attachment( $attachment, $upload['file'] );
            // Did everything went well when attempting to insert ?
            if ( is_wp_error( $attachment_id ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => problem when trying to wp_insert_attachment() for img : ' . $url );
            }

            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
            );

            $new_attachment = [
              'id' => $attachment_id,
              'url' => $upload['url'],
            ];
            wp_send_json_success( $new_attachment );
        }
    }//class
endif;
?>