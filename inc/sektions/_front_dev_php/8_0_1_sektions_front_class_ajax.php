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
            add_action( 'wp_ajax_sek_get_revision_history', array( $this, 'sek_get_revision_history' ) );
            // Fetches the revision for a given post id
            add_action( 'wp_ajax_sek_get_single_revision', array( $this, 'sek_get_single_revision' ) );
            // Fetches the category collection to generate the options for a select input
            // @see api.czrInputMap.category_picker
            add_action( 'wp_ajax_sek_get_post_categories', array( $this, 'sek_get_post_categories' ) );

            // Fetches the code editor params to generate the options for a textarea input
            // @see api.czrInputMap.code_editor
            add_action( 'wp_ajax_sek_get_code_editor_params', array( $this, 'sek_get_code_editor_params' ) );

            add_action( 'wp_ajax_sek_postpone_feedback', array( $this, 'sek_postpone_feedback_notification' ) );

            // <AJAX TO FETCH INPUT COMPONENTS>
            // this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
            // It allows us to populate the server response with the relevant module html template
            // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
            add_filter( "ac_set_ajax_czr_tmpl___fa_icon_picker_input", array( $this, 'sek_get_fa_icon_list_tmpl' ), 10, 3 );

            // this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
            // It allows us to populate the server response with the relevant module html template
            // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
            add_filter( "ac_set_ajax_czr_tmpl___font_picker_input", array( $this, 'sek_get_font_list_tmpl' ), 10, 3 );
            // </AJAX TO FETCH INPUT COMPONENTS>

            // Returns the customize url for the edit button when using Gutenberg editor
            // implemented for https://github.com/presscustomizr/nimble-builder/issues/449
            // @see assets/admin/js/nimble-gutenberg.js
            add_action( 'wp_ajax_sek_get_customize_url_for_nimble_edit_button', array( $this, 'sek_get_customize_url_for_nimble_edit_button' ) );


            // This is the list of accepted actions
            $this->ajax_action_map = array(
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
        // GENERIC HELPER FIRED IN ALL AJAX CALLBACKS
        // @param $params = array('check_nonce' => true )
        function sek_do_ajax_pre_checks( $params = array() ) {
            $params = wp_parse_args( $params, array( 'check_nonce' => true ) );
            if ( $params['check_nonce'] ) {
                $action = 'save-customize_' . get_stylesheet();
                if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                     wp_send_json_error( array(
                        'code' => 'invalid_nonce',
                        'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' => check_ajax_referer() failed.' ),
                    ) );
                }
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
        }//sek_do_ajax_pre_checks()


        ////////////////////////////////////////////////////////////////
        // IMPORT IMG
        // Fired in __construct()
        function _schedule_img_import_ajax_actions() {
            add_action( 'wp_ajax_sek_import_attachment', array( $this, 'sek_ajax_import_attachment' ) );
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
            $this->sek_do_ajax_pre_checks();
            // May 21st => back to the local data
            // after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
            //$preset_sections = sek_get_preset_sections_api_data();
            $preset_sections = sek_get_preset_section_collection_from_json();
            if ( empty( $preset_sections ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => no preset_sections when running sek_get_preset_sections_api_data()' );
            }
            wp_send_json_success( $preset_sections );
        }



        // hook : 'wp_ajax_sek_get_html_for_injection'
        function sek_get_level_content_for_injection( $params ) {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( ! isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }

            // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
            // september 2019
            // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
            // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
            // otherwise the preview UI can be broken
            if ( ! isset( $_POST['preview-level-guid'] ) || empty( $_POST['preview-level-guid'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing preview-level-guid' );
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

            $html = '';
            // is this action possible ?
            if ( in_array( $sek_action, $this->ajax_action_map ) ) {
                $content_type = null;
                if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                    $content_type = $_POST['content_type'];
                }

                // This 'preset_section' === $content_type statement has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                if ( 'preset_section' === $content_type ) {
                    $collection_of_preset_section_id = null;
                    if ( array_key_exists( 'collection_of_preset_section_id', $_POST ) && is_array( $_POST['collection_of_preset_section_id'] ) ) {
                        $collection_of_preset_section_id = $_POST['collection_of_preset_section_id'];
                    }

                    switch ( $sek_action ) {
                        // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                        case 'sek-add-content-in-new-sektion' :
                        case 'sek-add-content-in-new-nested-sektion' :
                            if ( 'preset_section' === $content_type ) {
                                if ( !is_array( $collection_of_preset_section_id ) || empty( $collection_of_preset_section_id ) ) {
                                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                                    break;
                                }
                                foreach ( $_POST['collection_of_preset_section_id'] as $preset_section_id ) {
                                    $html .= $this->sek_ajax_fetch_content( $sek_action, $preset_section_id );
                                }
                            // 'module' === $content_type
                            } else {
                                $html = $this->sek_ajax_fetch_content( $sek_action );
                            }

                        break;

                        default :
                            $html = $this->sek_ajax_fetch_content( $sek_action );
                        break;
                    }
                } else {
                      $html = $this->sek_ajax_fetch_content( $sek_action );
                }

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
        // @param $maybe_preset_section_id is used when injecting a collection of preset sections
        private function sek_ajax_fetch_content( $sek_action = '', $maybe_preset_section_id = '' ) {
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
                case 'sek-duplicate-section' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        //$level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                    }
                break;

                // This $content_type var has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                case 'sek-add-content-in-new-sektion' :
                case 'sek-add-content-in-new-nested-sektion' :
                    $content_type = null;
                    if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                        $content_type = $_POST['content_type'];
                    }
                    if ( 'preset_section' === $content_type ) {
                        if ( ! array_key_exists( 'collection_of_preset_section_id', $_POST ) || ! is_array( $_POST['collection_of_preset_section_id'] ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                            break;
                        }
                        if ( ! is_string( $maybe_preset_section_id ) || empty( $maybe_preset_section_id ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => inavalid preset section id' );
                            break;
                        }
                        $level_id = $maybe_preset_section_id;
                    // module content type case.
                    // the level id has been passed the regular way
                    } else {
                        $level_id = $_POST[ 'id' ];
                    }

                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( $_POST['is_nested'] ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        //$level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                        $level_model = sek_get_level_model( $level_id, $sektion_collection );
                    }
                break;

                //only used for nested section
                case 'sek-remove-section' :
                    if ( ! array_key_exists( 'is_nested', $_POST ) || true !== json_decode( $_POST['is_nested'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => the section must be nested in this ajax action' );
                        break;
                    } else {
                        // we need to set the parent_model here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                        $level_model = sek_get_level_model( $_POST[ 'in_column' ], $sektion_collection );
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
                        $this->parent_model = sek_get_parent_level_model( $_POST[ 'in_column' ], $sektion_collection );
                    } else {
                        $this->parent_model = sek_get_level_model( $_POST[ 'in_sektion' ], $sektion_collection );
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
                        $this->parent_model = sek_get_parent_level_model( $_POST['id'], $sektion_collection );
                    }
                    $level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                break;
            }//Switch sek_action

            // sek_error_log('LEVEL MODEL WHEN AJAXING', $level_model );

            ob_start();

            if ( $is_stylesheet ) {
                $r = $this->print_or_enqueue_seks_style( $_POST['location_skope_id'] );
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
                $r = $this->render( $level_model );
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
        function sek_ajax_import_attachment() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( !isset( $_POST['img_url'] ) || !is_string($_POST['img_url']) ) {
                wp_send_json_error( 'missing_or_invalid_img_url_when_importing_image');
            }

            $id = sek_sideload_img_and_return_attachment_id( $_POST['img_url'] );
            if ( is_wp_error( $id ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => problem when trying to wp_insert_attachment() for img : ' . $_POST['img_url'] . ' | SERVER ERROR => ' . json_encode( $id ) );
            } else {
                wp_send_json_success([
                  'id' => $id,
                  'url' => wp_get_attachment_url( $id )
                ]);
            }
        }
















        /////////////////////////////////////////////////////////////////
        // hook : wp_ajax_sek_save_section
        function sek_ajax_save_section() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

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
                wp_send_json_success( [ 'section_post_id' => $saved_section_post->ID ] );
            }

            //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
        }


        // @hook wp_ajax_sek_sek_get_user_saved_sections
        function sek_sek_get_user_saved_sections() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

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
        function sek_get_revision_history() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            if ( ! isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }
            $rev_list = sek_get_revision_history_from_posts( $_POST['skope_id'] );
            wp_send_json_success( $rev_list );
        }


        function sek_get_single_revision() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            if ( ! isset( $_POST['revision_post_id'] ) || empty( $_POST['revision_post_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing revision_post_id' );
            }
            $revision = sek_get_single_post_revision( $_POST['revision_post_id'] );
            wp_send_json_success( $revision );
        }



        ////////////////////////////////////////////////////////////////
        // POST CATEGORIES => to be used in the category picker select input
        // Fired in __construct()
        function sek_get_post_categories() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
            $raw_cats = get_categories();
            $raw_cats = is_array( $raw_cats ) ? $raw_cats : array();
            $cat_collection = array();
            foreach( $raw_cats as $cat ) {
                $cat_collection[] = array(
                    'id' => $cat->term_id,
                    'slug' => $cat->slug,
                    'name' => sprintf( '%s (%s %s)', $cat->cat_name, $cat->count, __('posts', 'text_doma') )
                );
            }
            wp_send_json_success( $cat_collection );
        }

        ////////////////////////////////////////////////////////////////
        // CODE EDITOR PARAMS => to be used in the code editor input
        // Fired in __construct()
        function sek_get_code_editor_params() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
            $code_type = isset( $_POST['code_type'] ) ? $_POST['code_type'] : 'text/html';
            $editor_params = nimble_get_code_editor_settings( array(
                'type' => $code_type
            ));
            wp_send_json_success( $editor_params );
        }

        ////////////////////////////////////////////////////////////////
        // POSTPONE FEEDBACK NOTIFICATION IN CUSTOMIZER
        // INSPIRED FROM CORE DISMISS POINTER MECHANISM
        // @see wp-admin/includes/ajax-actions.php
        function sek_postpone_feedback_notification() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

            if ( !isset( $_POST['transient_duration_in_days'] ) ||!is_numeric( $_POST['transient_duration_in_days'] ) ) {
                $transient_duration = 7 * DAY_IN_SECONDS;
            } else {
                $transient_duration = $_POST['transient_duration_in_days'] * DAY_IN_SECONDS;
            }
            set_transient( NIMBLE_FEEDBACK_NOTICE_ID, 'maybe_later', $transient_duration );
            wp_die( 1 );
        }


        ////////////////////////////////////////////////////////////////
        // USED TO PRINT THE BUTTON EDIT WITH NIMBLE ON POSTS AND PAGES
        // when using Gutenberg editor
        // implemented for https://github.com/presscustomizr/nimble-builder/issues/449
        function sek_get_customize_url_for_nimble_edit_button() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( ! isset( $_POST['nimble_edit_post_id'] ) || empty( $_POST['nimble_edit_post_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing post_id' );
            }

            $post_id = $_POST['nimble_edit_post_id'];

            // Build customize_url
            // @see function sek_get_customize_url_when_is_admin()
            $ajax_server_request_uri = "/wp-admin/post.php?post={$post_id}&action=edit";
            $customize_url = get_permalink( $post_id );
            $return_customize_url = add_query_arg(
                'return',
                urlencode(
                    remove_query_arg( wp_removable_query_args(), wp_unslash( $ajax_server_request_uri ) )
                ),
                wp_customize_url()
            );
            $customize_url = add_query_arg( 'url', urlencode( $customize_url ), $return_customize_url );
            $customize_url = add_query_arg(
                array( 'autofocus' => array( 'section' => '__content_picker__' ) ),
                $customize_url
            );

            wp_send_json_success( $customize_url );
        }


        ////////////////////////////////////////////////////////////////
        // FETCH FONT AWESOME ICONS
        // hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
        // this dynamic filter is declared on wp_ajax_ac_get_template
        // It allows us to populate the server response with the relevant module html template
        // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
        //
        // For czr_tiny_mce_editor_module, we request the font_list tmpl
        function sek_get_fa_icon_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
            if ( empty( $requested_tmpl ) ) {
                wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
            }
            return wp_json_encode(
                $this->sek_retrieve_decoded_font_awesome_icons()
            );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }



        //retrieves faicons:
        // 1) from faicons.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
        // otherwise
        // 2) from the transient set if it exists
        function sek_retrieve_decoded_font_awesome_icons() {
            // this file must be generated with: https://github.com/presscustomizr/nimble-builder/issues/57
            $faicons_json_path      = NIMBLE_BASE_PATH . '/assets/faicons.json';
            $faicons_transient_name = 'sek_font_awesome_november_2018';
            if ( false == get_transient( $faicons_transient_name ) ) {
                if ( file_exists( $faicons_json_path ) ) {
                    $faicons_raw      = @file_get_contents( $faicons_json_path );

                    if ( false === $faicons_raw ) {
                        $faicons_raw = wp_remote_fopen( $faicons_json_path );
                    }

                    $faicons_decoded   = json_decode( $faicons_raw, true );
                    set_transient( $faicons_transient_name , $faicons_decoded , 60*60*24*3000 );
                } else {
                    wp_send_json_error( __FUNCTION__ . ' => the file faicons.json is missing' );
                }
            }
            else {
                $faicons_decoded = get_transient( $faicons_transient_name );
            }

            return $faicons_decoded;
        }








        ////////////////////////////////////////////////////////////////
        // FETCH FONT LISTS
        // hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
        // For czr_tiny_mce_editor_module, we request the font_list tmpl
        function sek_get_font_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
            if ( empty( $requested_tmpl ) ) {
                wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
            }

            return wp_json_encode( array(
                'cfonts' => $this->sek_get_cfonts(),
                'gfonts' => $this->sek_get_gfonts(),
            ) );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }


        function sek_get_cfonts() {
            $cfonts = array();
            $raw_cfonts = array(
                'Arial Black,Arial Black,Gadget,sans-serif',
                'Century Gothic',
                'Comic Sans MS,Comic Sans MS,cursive',
                'Courier New,Courier New,Courier,monospace',
                'Georgia,Georgia,serif',
                'Helvetica Neue, Helvetica, Arial, sans-serif',
                'Impact,Charcoal,sans-serif',
                'Lucida Console,Monaco,monospace',
                'Lucida Sans Unicode,Lucida Grande,sans-serif',
                'Palatino Linotype,Book Antiqua,Palatino,serif',
                'Tahoma,Geneva,sans-serif',
                'Times New Roman,Times,serif',
                'Trebuchet MS,Helvetica,sans-serif',
                'Verdana,Geneva,sans-serif',
            );
            foreach ( $raw_cfonts as $font ) {
              //no subsets for cfonts => epty array()
              $cfonts[] = array(
                  'name'    => $font ,
                  'subsets'   => array()
              );
            }
            return apply_filters( 'sek_font_picker_cfonts', $cfonts );
        }


        //retrieves gfonts:
        // 1) from webfonts.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
        // otherwise
        // 2) from the transiet set if it exists
        //
        // => Until June 2017, the webfonts have been stored in 'tc_gfonts' transient
        // => In June 2017, the Google Fonts have been updated with a new webfonts.json
        // generated from : https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBID8gp8nBOpWyH5MrsF7doP4fczXGaHdA
        //
        // => The transient name is now : czr_gfonts_june_2017
        function sek_retrieve_decoded_gfonts() {
            if ( false == get_transient( 'sek_gfonts_may_2018' ) ) {
                $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

                if ( $gfont_raw === false ) {
                  $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
                }

                $gfonts_decoded   = json_decode( $gfont_raw, true );
                set_transient( 'sek_gfonts_may_2018' , $gfonts_decoded , 60*60*24*3000 );
            }
            else {
              $gfonts_decoded = get_transient( 'sek_gfonts_may_2018' );
            }

            return $gfonts_decoded;
        }



        //@return the google fonts
        function sek_get_gfonts( $what = null ) {
          //checks if transient exists or has expired

          $gfonts_decoded = $this->sek_retrieve_decoded_gfonts();
          $gfonts = array();
          //$subsets = array();

          // $subsets['all-subsets'] = sprintf( '%1$s ( %2$s %3$s )',
          //   __( 'All languages' , 'text_doma' ),
          //   count($gfonts_decoded['items']) + count( $this->get_cfonts() ),
          //   __('fonts' , 'text_doma' )
          // );

          foreach ( $gfonts_decoded['items'] as $font ) {
            foreach ( $font['variants'] as $variant ) {
              $name     = str_replace( ' ', '+', $font['family'] );
              $gfonts[]   = array(
                  'name'    => $name . ':' .$variant
                  //'subsets'   => $font['subsets']
              );
            }
            //generates subset list : subset => font number
            // foreach ( $font['subsets'] as $sub ) {
            //   $subsets[$sub] = isset($subsets[$sub]) ? $subsets[$sub]+1 : 1;
            // }
          }

          //finalizes the subset array
          // foreach ( $subsets as $subset => $font_number ) {
          //   if ( 'all-subsets' == $subset )
          //     continue;
          //   $subsets[$subset] = sprintf('%1$s ( %2$s %3$s )',
          //     $subset,
          //     $font_number,
          //     __('fonts' , 'text_doma' )
          //   );
          // }

          return ('subsets' == $what) ? apply_filters( 'sek_font_picker_gfonts_subsets ', $subsets ) : apply_filters( 'sek_font_picker_gfonts', $gfonts )  ;
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
            $this->parent_model = sek_get_parent_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );
            $this->model = sek_get_level_model( $_POST[ 'id' ], $sektionSettingValue['collection'] );

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