<?php
if ( ! class_exists( 'SEK_Front_Ajax' ) ) :
    class SEK_Front_Ajax extends SEK_Front_Construct {
        // Fired in __construct()
        function _schedule_front_ajax_actions() {
            add_action( 'wp_ajax_sek_get_content', array( $this, 'sek_get_level_content_for_injection' ) );
            //add_action( 'wp_ajax_sek_get_preview_ui_element', array( $this, 'sek_get_ui_content_for_injection' ) );

            // Fetches the preset_sections
            add_action( 'wp_ajax_sek_get_preset_sections', array( $this, 'sek_get_preset_sektions' ) );

            // Fetches the list of revision for a given skope_id
            add_action( 'wp_ajax_sek_get_revision_list', array( $this, 'sek_get_revision_list' ) );

            // Fetches the revision for a given post id
            add_action( 'wp_ajax_sek_get_single_revision', array( $this, 'sek_get_single_revision' ) );
            // hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module

            // This is the list of accepted actions
            $this -> ajax_action_map = array(
                  'sek-add-section',
                  'sek-remove-section',
                  'sek-duplicate-section',

                  // fired when dropping a module or a preset_section
                  'sek-add-content-in-new-nested-sektion',
                  'sek-add-content-in-new-sektion',

                  // add, duplicate, remove column is a re-rendering of the parent sektion collection
                  'sek-add-column',
                  'sek-remove-column',
                  'sek-duplicate-column',
                  'sek-resize-columns',
                  'sek-refresh-columns-in-sektion',

                  'sek-add-module',
                  'sek-remove-module',
                  'sek-duplicate-module',
                  'sek-refresh-modules-in-column',

                  'sek-refresh-stylesheet',

                  'sek-refresh-level'
            );
        }

        ////////////////////////////////////////////////////////////////
        // IMPORT IMG
        // Fired in __construct()
        function _schedule_img_import_ajax_actions() {
            add_action( 'wp_ajax_sek_import_attachment', array( $this, 'sek_ajax_import_attachemnt' ) );
        }

        ////////////////////////////////////////////////////////////////
        // SECTION SAVING
        // Fired in __construct()
        function _schedule_section_saving_ajax_actions() {
            // Writes the saved section in a CPT + update the saved section option
            add_action( 'wp_ajax_sek_save_section', array( $this, 'sek_ajax_save_section' ) );
            // Fetches the user_saved sections
            add_action( 'wp_ajax_sek_get_user_saved_sections', array( $this, 'sek_sek_get_user_saved_sections' ) );
        }

        ////////////////////////////////////////////////////////////////
        // PRESET SECTIONS
        // Fired in __construct()
        // hook : 'wp_ajax_sek_get_preset_sektions'
        function sek_get_preset_sektions() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' => check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }
            $preset_sections = sek_get_preset_sektions();
            if ( empty( $preset_sections ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no preset_sections when running sek_get_preset_sektions()' );
            }
            wp_send_json_success( $preset_sections );
        }



        // hook : 'wp_ajax_sek_get_html_for_injection'
        function sek_get_level_content_for_injection( $params ) {
            //sek_error_log( __CLASS__ . '::' . __FUNCTION__ , $_POST );
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }

            if ( ! isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }

            if ( ! isset( $_POST['sek_action'] ) || empty( $_POST['sek_action'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing sek_action' );
            }
            $sek_action = $_POST['sek_action'];

            $exported_setting_validities = array();

            // CHECK THE SETTING VALIDITIES BEFORE RENDERING
            // When a module has been registered with a sanitize_callback, we can collect the possible problems here before sending the response.
            // Then, on ajax.done(), in SekPreviewPrototype::schedulePanelMsgReactions, we will send the setting validities object to the panel
            if ( is_customize_preview() ) {
                global $wp_customize;
                // prepare the setting validities so we can pass them when sending the ajax response
                $setting_validities = $wp_customize->validate_setting_values( $wp_customize->unsanitized_post_values() );
                $raw_exported_setting_validities = array_map( array( $wp_customize, 'prepare_setting_validity_for_js' ), $setting_validities );

                // filter the setting validity to only keep the __nimble__ prefixed ui settings
                $exported_setting_validities = array();
                foreach( $raw_exported_setting_validities as $setting_id => $validity ) {
                    // don't consider the not Nimble UI settings, not starting with __nimble__
                    if ( false === strpos( $setting_id , NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) )
                      continue;
                    $exported_setting_validities[ $setting_id ] = $validity;
                }
            }

            // is this action possible ?
            if ( in_array( $sek_action, $this -> ajax_action_map ) ) {
                $html = $this -> sek_ajax_fetch_content( $sek_action );
                //sek_error_log(__CLASS__ . '::' . __FUNCTION__ , $html );
                if ( is_wp_error( $html ) ) {
                    wp_send_json_error( $html );
                } else {
                    $response = array(
                        'contents' => $html,
                        'setting_validities' => $exported_setting_validities
                    );
                    wp_send_json_success( apply_filters( 'sek_content_results', $response, $sek_action ) );
                }
            } else {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => this ajax action ( ' . $sek_action . ' ) is not listed in the map ' );
            }


        }//sek_get_content_for_injection()


        // hook : add_filter( "sek_set_ajax_content___{$action}", array( $this, 'sek_ajax_fetch_content' ) );
        // $_POST looks like Array
        // (
        //     [action] => sek_get_content
        //     [withNonce] => false
        //     [id] => __nimble__0b7c85561448ab4eb8adb978
        //     [skope_id] => skp__post_page_home
        //     [sek_action] => sek-add-section
        //     [SEKFrontNonce] => 3713b8ac5c
        //     [customized] => {\"nimble___loop_start[skp__post_page_home]\":{...}}
        // )
        // @return string
        // @param $sek_action is $_POST['sek_action']
        private function sek_ajax_fetch_content( $sek_action = '' ) {
            //sek_error_log( __CLASS__ . '::' . __FUNCTION__ , $_POST );
            // the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // since wp has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
            if ( ! is_array( $sektionSettingValue ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue => it should be an array().' );
                return;
            }
            if ( empty( $sek_action ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => invalid sek_action param' );
                return;
            }
            $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
            if ( ! is_array( $sektion_collection ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektion_collection => it should be an array().' );
                return;
            }

            $candidate_id = '';
            $collection = array();
            $level_model = array();

            $is_stylesheet = false;

            switch ( $sek_action ) {
                case 'sek-add-section' :
                // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                case 'sek-add-content-in-new-sektion' :
                case 'sek-add-content-in-new-nested-sektion' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;

                //only used for nested section
                case 'sek-remove-section' :
                    if ( ! array_key_exists( 'is_nested', $_POST ) || true !== json_decode( $_POST['is_nested'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' sek-remove-section => the section must be nested in this ajax action' );
                        break;
                    } else {
                        // we need to set the parent_model here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    }
                break;

                case 'sek-duplicate-section' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;

                // We re-render the entire parent sektion collection in all cases
                case 'sek-add-column' :
                case 'sek-remove-column' :
                case 'sek-duplicate-column' :
                case 'sek-refresh-columns-in-sektion' :
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST['in_sektion'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_sektion param' );
                        break;
                    }
                    // sek_error_log('sektion_collection', $sektion_collection );
                    $level_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                break;

                // We re-render the entire parent column collection
                case 'sek-add-module' :
                case 'sek-remove-module' :
                case 'sek-refresh-modules-in-column' :
                case 'sek-duplicate-module' :
                    if ( ! array_key_exists( 'in_column', $_POST ) || empty( $_POST['in_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_column param' );
                        break;
                    }
                    if ( ! array_key_exists( 'in_sektion', $_POST ) || empty( $_POST[ 'in_sektion' ] ) ) {
                        $this -> parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $this -> parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                break;

                case 'sek-resize-columns' :
                    if ( ! array_key_exists( 'resized_column', $_POST ) || empty( $_POST['resized_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing resized_column' );
                        break;
                    }
                    $is_stylesheet = true;
                break;

                case 'sek-refresh-stylesheet' :
                    $is_stylesheet = true;
                break;

                 case 'sek-refresh-level' :
                    if ( ! array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing level id' );
                        break;
                    }
                    if ( !empty( $_POST['level'] ) && 'column' === $_POST['level'] ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this -> parent_model = sek_get_parent_level_model( $_POST['id'], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                break;
            }//Switch sek_action

            // sek_error_log('LEVEL MODEL WHEN AJAXING', $level_model );

            ob_start();

            if ( $is_stylesheet ) {
                $r = $this -> print_or_enqueue_seks_style( $_POST['location_skope_id'] );
            } else {
                if ( 'no_match' == $level_model ) {
                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action . ' => missing level model' );
                    ob_end_clean();
                    return;
                }
                if ( empty( $level_model ) || ! is_array( $level_model ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => empty or invalid $level_model' );
                    ob_end_clean();
                    return;
                }
                // note that in the case of a sektion nested inside a column, the parent_model has been set in the switch{ case : ... } above ,so we can access it in the ::render method to calculate the column width.
                $r = $this -> render( $level_model );
            }
            $html = ob_get_clean();
            if ( is_wp_error( $r ) ) {
                return $r;
            } else {
                // the $html content should not be empty when ajaxing a template
                // it can be empty when ajaxing a stylesheet
                if ( ! $is_stylesheet && empty( $html ) ) {
                      // return a new WP_Error that will be intercepted in sek_get_level_content_for_injection
                      $html = new \WP_Error( 'ajax_fetch_content_error', __CLASS__ . '::' . __FUNCTION__ . ' => no content returned for sek_action : ' . $sek_action );
                }
                return apply_filters( "sek_set_ajax_content", $html, $sek_action );// this is sent with wp_send_json_success( apply_filters( 'sek_content_results', $html, $sek_action ) );
            }
        }











        /////////////////////////////////////////////////////////////////
        // hook : wp_ajax_sek_import_attachment
        function sek_ajax_import_attachemnt() {
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }

            //sek_error_log( __CLASS__ . '::' . __FUNCTION__ . '$_POST' ,  $_POST);
            $relative_path = $_POST['rel_path'];

            // Generate the file name from the url.
            $filename = 'nimble_asset_' . basename( $relative_path );
            $args = array(
                'posts_per_page' => 1,
                'post_type'      => 'attachment',
                'name'           => trim ( $filename ),
            );

            // Make sure this img has not already been uploaded
            $get_attachment = new \WP_Query( $args );
            //error_log( print_r( $get_attachment->posts, true ) );
            if ( is_array( $get_attachment->posts ) && array_key_exists(0, $get_attachment->posts) ) {
                //wp_send_json_error( __CLASS__ . '::' . __CLASS__ . '::' . __FUNCTION__ . ' => file already uploaded : ' . $relative_path );
                $new_attachment = array(
                    'id'  => $get_attachment->posts[0] -> ID,
                    'url' => $get_attachment->posts[0] -> guid
                );
            }

            // stop now if the attachment was already uploaded
            if ( isset($new_attachment ) ) {
                wp_send_json_success( $new_attachment );
            } else {
                // Does it exists ?
                //error_log( "dirname(__FILE__ ) . $relative_path => " . dirname(__FILE__ ) . $relative_path );
                //error_log("file_exists( dirname(__FILE__ ) . $relative_path => " . file_exists( dirname(__FILE__ ) . $relative_path ) );
                if ( ! file_exists( NIMBLE_BASE_PATH . $relative_path ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __CLASS__ . '::' . __FUNCTION__ . ' => no file found for relative path : ' . dirname( __FILE__ ) . $relative_path );
                    return;
                }

                // Does it return a 200 code ?
                $url = NIMBLE_BASE_URL . $relative_path;
                //error_log('$url' .$url );
                $url_content = wp_safe_remote_get( $url );

                //sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' response code ?', $url_content['response']['code'] );

                if ( '404' == $url_content['response']['code'] ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => 404 response when wp_safe_remote_get() url : ' . $url );
                    return;
                }
                $file_content = wp_remote_retrieve_body( $url_content );
                //sek_error_log( __FUNCTION__ . ' file content ?', $file_content );

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
                // Did everything go well when attempting to insert ?
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
        }
















        /////////////////////////////////////////////////////////////////
        // hook : wp_ajax_sek_save_section
        function sek_ajax_save_section() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( 'sek_ajax_save_section => check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __FUNCTION__ . ' => bad_method' );
            }
            // We must have a title and a section_id and sektion data
            if ( empty( $_POST['sek_title']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing title' );
            }
            if ( empty( $_POST['sek_id']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing sektion_id' );
            }
            if ( empty( $_POST['sek_data']) ) {
                wp_send_json_error( __FUNCTION__ . ' => missing sektion data' );
            }
            if ( ! is_string( $_POST['sek_data'] ) ) {
                wp_send_json_error( __FUNCTION__ . ' => the sektion data must be a json stringified' );
            }
            // sek_error_log('SEKS DATA ?', $_POST['sek_data'] );
            // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
            $sektion_to_save = array(
                'title' => $_POST['sek_title'],
                'description' => $_POST['sek_description'],
                'id' => $_POST['sek_id'],
                'type' => 'content',//in the future will be used to differentiate header, content and footer sections
                'creation_date' => date("Y-m-d H:i:s"),
                'update_date' => '',
                'data' => $_POST['sek_data']//<= json stringified
            );

            $saved_section_post = sek_update_saved_seks_post( $sektion_to_save );
            if ( is_wp_error( $saved_section_post ) ) {
                wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_saved_seks_post()' );
            } else {
                // sek_error_log( 'ALORS CE POST?', $saved_section_post );
                wp_send_json_success( [ 'section_post_id' => $saved_section_post -> ID ] );
            }

            //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
        }


        // @hook wp_ajax_sek_sek_get_user_saved_sections
        function sek_sek_get_user_saved_sections() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( 'sek_ajax_save_section => check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __FUNCTION__ . ' => bad_method' );
            }
            // We must have a section_id provided
            if ( empty( $_POST['preset_section_id']) || ! is_string( $_POST['preset_section_id'] ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing or invalid preset_section_id' );
            }
            $section_id = $_POST['preset_section_id'];

            $section_data_decoded_from_custom_post_type = sek_get_saved_sektion_data( $section_id );
            if ( ! empty( $section_data_decoded_from_custom_post_type ) ) {
                wp_send_json_success( $section_data_decoded_from_custom_post_type );
            } else {
                $all_saved_seks = get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS );
                if ( ! is_array( $all_saved_seks ) || empty( $all_saved_seks[$section_id]) ) {
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing section data in get_option( NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS )' );
                }
                // $section_infos is an array
                // Array
                // (
                //     [post_id] => 399
                //     [title] => My section one
                //     [description] =>
                //     [creation_date] => 2018-10-29 13:52:54
                //     [type] => content
                // )
                $section_infos = $all_saved_seks[$section_id];
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => missing post data for section title ' . $section_infos['title'] );
            }
        }






        ////////////////////////////////////////////////////////////////
        // REVISIONS
        // Fired in __construct()
        function sek_get_revision_list() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }

            if ( ! isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }
            $rev_list = sek_get_seks_post_revision_list( $_POST['skope_id'] );
            wp_send_json_success( $rev_list );
        }


        function sek_get_single_revision() {
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                    'code' => 'invalid_nonce',
                    'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' check_ajax_referer() failed.' ),
                ) );
            }
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }

            if ( ! isset( $_POST['revision_post_id'] ) || empty( $_POST['revision_post_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }
            $revision = sek_get_single_post_revision( $_POST['revision_post_id'] );
            wp_send_json_success( $revision );
        }




        // hook : 'wp_ajax_sek_get_preview_ui_element'
        /*function sek_get_ui_content_for_injection( $params ) {
            // error_log( print_r( $_POST, true ) );
            // error_log( print_r( sek_get_skoped_seks( "skp__post_page_home", 'loop_start' ), true ) );
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
                return;
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
                return;
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
                return;
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
                return;
            }

            if ( ! isset( $_POST['level'] ) || empty( $_POST['level'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing level' );
                return;
            }
            if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing level id' );
                return;
            }
            if ( ! isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
                return;
            }


            // the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // since wp has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
            if ( ! is_array( $sektionSettingValue ) || ! array_key_exists( 'collection', $sektionSettingValue ) || ! is_array( $sektionSettingValue['collection'] ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue' );
                return;
            }
            // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
            $this -> parent_model = sek_get_parent_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );
            $this -> model = sek_get_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );

            $level = $_POST['level'];

            $html = '';
            ob_start();
                load_template( dirname( __FILE__ ) . "/tmpl/ui/block-overlay-{$level}.php", false );
            $html = ob_get_clean();

            if ( empty( $html ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no content returned' );
            } else {
                wp_send_json_success( apply_filters( 'sek_ui_content_results', $html ) );
            }
        }//sek_get_content_for_injection()*/

    }//class
endif;
?>