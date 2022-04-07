<?php
if ( !class_exists( 'SEK_Front_Ajax' ) ) :
    class SEK_Front_Ajax extends SEK_Front_Construct {
        // Fired in __construct()
        function _schedule_front_ajax_actions() {
            add_action( 'wp_ajax_sek_get_content', array( $this, 'sek_get_level_content_for_injection' ) );

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
                if ( !check_ajax_referer( $action, 'nonce', false ) ) {
                     wp_send_json_error( array(
                        'code' => 'invalid_nonce',
                        'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' => check_ajax_referer() failed.' ),
                    ) );
                }
            }

            if ( !is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( !current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }
        }//sek_do_ajax_pre_checks()



        // hook : 'wp_ajax_sek_get_html_for_injection'
        function sek_get_level_content_for_injection( $params ) {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( !isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }

            // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
            // september 2019
            // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
            // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
            // otherwise the preview UI can be broken
            if ( !isset( $_POST['preview-level-guid'] ) || empty( $_POST['preview-level-guid'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing preview-level-guid' );
            }

            if ( !isset( $_POST['sek_action'] ) || empty( $_POST['sek_action'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing sek_action' );
            }
            $sek_action = sanitize_text_field($_POST['sek_action']);

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
                    if ( false === strpos( $setting_id , NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED ) )
                      continue;
                    $exported_setting_validities[ $setting_id ] = $validity;
                }
            }

            $html = '';
            // is this action possible ?
            if ( in_array( $sek_action, $this->ajax_action_map ) ) {
                $content_type = null;
                if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                    $content_type = sanitize_text_field($_POST['content_type']);
                }

                // This 'preset_section' === $content_type statement has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                if ( 'preset_section' === $content_type ) {
                    switch ( $sek_action ) {
                        // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                        case 'sek-add-content-in-new-sektion' :
                        case 'sek-add-content-in-new-nested-sektion' :
                            if ( 'preset_section' === $content_type ) {
                                if ( !array_key_exists( 'collection_of_preset_section_id', $_POST ) || !is_array( $_POST['collection_of_preset_section_id'] ) || empty( $_POST['collection_of_preset_section_id'] ) ) {
                                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                                    break;
                                }
                                foreach ( $_POST['collection_of_preset_section_id'] as $preset_section_id ) {
                                    $html .= $this->sek_ajax_fetch_content( $sek_action, sanitize_text_field($preset_section_id ));
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
            //sek_error_log( __CLASS__ . '::' . __FUNCTION__  . ' POST ?', $_POST );
            // Important Notes :
            // 1) at this stage => the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // How $_POST['customized'] is getting populated without a full refresh of the preview ?
            // a) Each time the main collection setting id is updated ( @see CZRSeksPrototype::mayBeUpdateSektionsSetting() ), api.Setting.prototype.preview sends a 'setting' event to the preview
            // ( note that api.Setting.prototype.preview is overriden by NB to send other events )
            // b) when the core customize-preview receives the event, it updates the customized dirties
            // c) then when ajaxing, the $_POST['customized'] param is added by WP core with $.ajaxPrefilter() in customize-preview.js
            //
            // 2) since 'wp' hook has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( sanitize_text_field($_POST['location_skope_id']) );
            if ( !is_array( $sektionSettingValue ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue => it should be an array().' );
                return;
            }
            if ( empty( $sek_action ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => invalid sek_action param' );
                return;
            }
            $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
            if ( !is_array( $sektion_collection ) ) {
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
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( sanitize_text_field($_POST['is_nested']) ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    } else {
                        //$level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'id' ]), $sektion_collection );
                    }
                break;

                // This $content_type var has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                case 'sek-add-content-in-new-sektion' :
                case 'sek-add-content-in-new-nested-sektion' :
                    $content_type = null;
                    if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                        $content_type = sanitize_text_field($_POST['content_type']);
                    }
                    if ( 'preset_section' === $content_type ) {
                        if ( !array_key_exists( 'collection_of_preset_section_id', $_POST ) || !is_array( $_POST['collection_of_preset_section_id'] ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                            break;
                        }
                        if ( !is_string( $maybe_preset_section_id ) || empty( $maybe_preset_section_id ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => invalid preset section id' );
                            break;
                        }
                        $level_id = $maybe_preset_section_id;
                    // module content type case.
                    // the level id has been passed the regular way
                    } else {
                        $level_id = sanitize_text_field($_POST[ 'id' ]);
                    }

                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( sanitize_text_field($_POST['is_nested']) ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $level_id, $sektion_collection );
                    }
                break;

                //only used for nested section
                case 'sek-remove-section' :
                    if ( !array_key_exists( 'is_nested', $_POST ) || true !== json_decode( sanitize_text_field($_POST['is_nested'] )) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => the section must be nested in this ajax action' );
                        break;
                    } else {
                        // we need to set the parent_model here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_parent_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    }
                break;

                // We re-render the entire parent sektion collection in all cases
                case 'sek-add-column' :
                case 'sek-remove-column' :
                case 'sek-duplicate-column' :
                case 'sek-refresh-columns-in-sektion' :
                    if ( !array_key_exists( 'in_sektion', $_POST ) || empty( $_POST['in_sektion'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_sektion param' );
                        break;
                    }
                    // sek_error_log('sektion_collection', $sektion_collection );
                    $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                break;

                // We re-render the entire parent column collection
                case 'sek-add-module' :
                case 'sek-remove-module' :
                case 'sek-refresh-modules-in-column' :
                case 'sek-duplicate-module' :
                    if ( !array_key_exists( 'in_column', $_POST ) || empty( $_POST['in_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_column param' );
                        break;
                    }
                    if ( !array_key_exists( 'in_sektion', $_POST ) || empty( $_POST[ 'in_sektion' ] ) ) {
                        $this->parent_model = sek_get_parent_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    } else {
                        $this->parent_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                    }
                    $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                break;

                case 'sek-resize-columns' :
                    if ( !array_key_exists( 'resized_column', $_POST ) || empty( $_POST['resized_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing resized_column' );
                        break;
                    }
                    $is_stylesheet = true;
                break;

                case 'sek-refresh-stylesheet' :
                    $is_stylesheet = true;
                break;

                 case 'sek-refresh-level' :
                    if ( !array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing level id' );
                        break;
                    }
                    if ( !empty( $_POST['level'] ) && 'column' === sanitize_text_field($_POST['level']) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_parent_level_model( sanitize_text_field($_POST['id']), $sektion_collection );
                    }
                    $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'id' ]), $sektion_collection );
                break;
            }//Switch sek_action

            // sek_error_log('LEVEL MODEL WHEN AJAXING', $level_model );

            ob_start();

            if ( $is_stylesheet ) {
                $r = $this->print_or_enqueue_seks_style( sanitize_text_field($_POST['location_skope_id']) );
            } else {
                if ( 'no_match' == $level_model ) {
                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action . ' => missing level model' );
                    ob_end_clean();
                    return;
                }
                if ( empty( $level_model ) || !is_array( $level_model ) ) {
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
                if ( !$is_stylesheet && empty( $html ) ) {
                      // return a new WP_Error that will be intercepted in sek_get_level_content_for_injection
                      $html = new \WP_Error( 'ajax_fetch_content_error', __CLASS__ . '::' . __FUNCTION__ . ' => no content returned for sek_action : ' . $sek_action );
                }
                return apply_filters( "sek_set_ajax_content", $html, $sek_action );// this is sent with wp_send_json_success( apply_filters( 'sek_content_results', $html, $sek_action ) );
            }
        }



        ////////////////////////////////////////////////////////////////
        // USED TO PRINT THE BUTTON EDIT WITH NIMBLE ON POSTS AND PAGES
        // when using Gutenberg editor
        // implemented for https://github.com/presscustomizr/nimble-builder/issues/449
        function sek_get_customize_url_for_nimble_edit_button() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( !isset( $_POST['nimble_edit_post_id'] ) || empty( $_POST['nimble_edit_post_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing post_id' );
            }

            $post_id = sanitize_text_field($_POST['nimble_edit_post_id']);

            // Build customize_url
            // @see function sek_get_customize_url_when_is_admin()
            $return_url_after_customization = '';//"/wp-admin/post.php?post={$post_id}&action=edit";
            $customize_url = sek_get_customize_url_for_post_id( $post_id, $return_url_after_customization );
            wp_send_json_success( $customize_url );
        }
    }//class
endif;
?>