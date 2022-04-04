<?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( !class_exists( 'CZR_Fmk_Dyn_Module_Registration' ) ) :
    class CZR_Fmk_Dyn_Module_Registration extends CZR_Fmk_Dyn_Setting_Registration {

        //fired in the constructor
        function czr_setup_dynamic_modules_registration() {
            // Dynamic Module Registration
            add_action( 'init', array( $this, 'czr_schedule_ajax_tmpl' ) );
            // Enqueue the module customizer control assets
            add_action( 'customize_controls_enqueue_scripts' , array( $this, 'czr_register_dynamic_modules_customizer_control_assets' ) );
        }






        ////////////////////////////////////////////////////////////////
        // PRE REGISTRATION FOR MODULES
        // Default params
        // array(
        //     'module_type' => '',
        //     'customizer_assets' => array(
        //         'control_js' => array(),
        //         'localized_control_js' => array()
        //     ),
        //     'tmpl' => array()
        // )
        function czr_pre_register_dynamic_module( $module_params ) {
            // error_log( '<czr_pre_register_dynamic_module>' );
            // error_log( print_r( $module_params, true ) );
            // error_log( '</czr_pre_register_dynamic_module>' );

            if ( !is_array( $module_params ) || empty( $module_params ) ) {
                sek_error_log( 'czr_pre_register_dynamic_module => empty $module_params submitted' );
                return;
            }
            if ( !array_key_exists( 'module_type', $module_params ) || empty( $module_params['module_type'] ) ) {
                sek_error_log( 'czr_pre_register_dynamic_module => missing module_type' );
                return;
            }

            // normalize
            $module_params = wp_parse_args( $module_params, $this->default_dynamic_module_params );

            $registered = $this->registered_modules;
            $module_type_candidate = $module_params['module_type'];

            // A module type can be registered only once.
            // Already registered ?
            if ( array_key_exists( $module_type_candidate, $registered ) ) {
                //sek_error_log( 'czr_pre_register_dynamic_module => module type already registered => ' . $module_type_candidate );
                return;
            }
            $registered[ $module_type_candidate ] = $module_params;
            $this->registered_modules = $registered;
        }



        // HELPER
        // @return boolean or array of module params
        function czr_get_registered_dynamic_module( $module_type = '' ) {
            $registered = $this->registered_modules;
            if ( empty( $module_type ) || !is_array( $registered ) || empty( $registered ) )
              return;
            return array_key_exists( $module_type , $registered ) ? $registered[ $module_type ] : false;
        }

        // @return bool
        function czr_is_module_registered( $module_type = '' ) {
            $registered = $this->registered_modules;
            if ( empty( $module_type ) || !is_array( $registered ) || empty( $registered ) )
              return;
            return array_key_exists( $module_type , $registered );
        }


        ////////////////////////////////////////////////////////////////
        // ENQUEUE ASSETS
        // hook : customize_controls_enqueue_scripts
        //
        // 'customizer_assets' => array(
        //     'control_js' => array(
        //         // handle + params for wp_enqueue_script()
        //         // @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
        //         'czr-social-links-module' => array(
        //             'src' => sprintf(
        //                 '%1$s/assets/js/%2$s',
        //                 $args['base_url_path'],
        //                 '_2_7_socials_module.js'
        //             ),
        //             'deps' => array('customize-controls' , 'jquery', 'underscore'),
        //             'ver' => ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : $args['version'],
        //             'in_footer' => true
        //         )
        //     ),
        //     'localized_control_js' => array(
        //         'deps' => 'czr-customizer-fmk',
        //         'global_var_name' => 'socialLocalized',
        //         'params' => array(
        //             //Social Module
        //             'defaultSocialColor' => 'rgb(90,90,90)',
        //             'defaultSocialSize'  => 14,
        //             //option value for dynamic registration
        //         )
        //     )
        // ),
        function czr_register_dynamic_modules_customizer_control_assets() {
            if ( !is_array( $this->registered_modules ) || empty( $this->registered_modules ) )
              return;

            $wp_scripts = wp_scripts();

            // loop on each registered modules
            foreach ( $this->registered_modules as $module_type => $params ) {
                $params = wp_parse_args( $params, $this->default_dynamic_module_params );
                //error_log( print_r( $params, true ) );
                $control_js_params = $params[ 'customizer_assets' ][ 'control_js' ];
                // Enqueue the list of registered scripts
                if ( !empty( $control_js_params ) ) {
                    foreach ( $control_js_params as $handle => $script_args ) {
                        if ( !isset( $wp_scripts->registered[$handle] ) ) {
                            wp_enqueue_script(
                                $handle,
                                array_key_exists( 'src', $script_args ) ? $script_args['src'] : null,
                                array_key_exists( 'deps', $script_args ) ? $script_args['deps'] : null,
                                array_key_exists( 'ver', $script_args ) ? $script_args['ver'] : null,
                                array_key_exists( 'in_footer', $script_args ) ? $script_args['in_footer'] : false
                            );
                        } else {
                            error_log( __CLASS__ . '::' . __FUNCTION__ . " => handle already registered : " . $handle . " , this asset won't be enqueued => " . $script_args['src'] );
                        }
                    }

                }

                //  'localized_control_js' => array(
                //     'deps' => 'czr-customizer-fmk',
                //     'global_var_name' => 'socialLocalized',
                //     'params' => array(
                //         //Social Module
                //         'defaultSocialColor' => 'rgb(90,90,90)',
                //         'defaultSocialSize'  => 14,
                //         //option value for dynamic registration
                //     )
                // )
                // Print localized params if any
                if ( array_key_exists( 'localized_control_js', $params[ 'customizer_assets' ] ) ) {
                    $localized_control_js_params = is_array( $params[ 'customizer_assets' ][ 'localized_control_js' ] ) ? $params[ 'customizer_assets' ][ 'localized_control_js' ] : array();

                    if ( is_array( $localized_control_js_params ) && !empty( $localized_control_js_params ) ) {
                        wp_localize_script(
                            array_key_exists( 'deps', $localized_control_js_params ) ? $localized_control_js_params['deps'] : '',
                            array_key_exists( 'global_var_name', $localized_control_js_params ) ? $localized_control_js_params['global_var_name'] : '',
                            array_key_exists( 'params', $localized_control_js_params ) ? $localized_control_js_params['params'] : array()
                        );
                    }
                }
            }//foreach
        }



        ////////////////////////////////////////////////////////////////
        // AJAX TEMPLATE FILTERS
        // hook : init
        function czr_schedule_ajax_tmpl() {
            if ( !is_array( $this->registered_modules ) || empty( $this->registered_modules ) )
              return;

            foreach ( $this->registered_modules as $module_type => $params ) {
                $params = wp_parse_args( $params, $this->default_dynamic_module_params );
                if ( !empty( $params['tmpl'] ) ) {
                    $module_type = $params['module_type'];
                    // filter declared with $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl, $_POST );
                    add_filter( "ac_set_ajax_czr_tmpl___{$module_type}", array( $this, 'ac_get_ajax_module_tmpl'), 10, 3 );
                }
            }//foreach
        }


        // AJAX TMPL FILTERS
        // this dynamic filter is declared on wp_ajax_ac_get_template
        // It allows us to populate the server response with the relevant module html template
        // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
        //
        // Each template is built from a map, each input type having its own unique piece of tmpl
        //
        // 3 types of templates :
        // 1) the pre-item, rendered when adding an item
        // 2) the module meta options, or mod-opt
        // 3) the item input options
        // @param $posted_params is the $_POST
        // hook : ac_set_ajax_czr_tmpl___{$module_type}
        function ac_get_ajax_module_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
            // error_log( '<REGISTERED MODULES>' );
            // error_log( print_r( $this->registered_modules, true ) );
            // error_log( '</REGISTERED MODULES>' );
            // error_log( '<GET AJAX MODULE TMPL>' );
            // error_log( print_r( $posted_params, true ) );
            // error_log( '</GET AJAX MODULE TMPL>' );
            // the module type is sent in the $posted_params
            if ( !is_array( $posted_params ) || empty( $posted_params ) ) {
                wp_send_json_error( 'ac_get_ajax_module_tmpl => empty posted_params' );
            }
            if ( !array_key_exists( 'module_type', $posted_params  ) || empty( $posted_params['module_type'] ) ) {
                wp_send_json_error( 'ac_get_ajax_module_tmpl => missing module_type' );
            }
            // if ( !array_key_exists( 'control_id', $posted_params  ) || empty( $posted_params['control_id'] ) ) {
            //    wp_send_json_error( 'ac_get_ajax_module_tmpl => missing control_id' );
            // }

            // find the requested module_id in the list of registered modules
            $registered_modules = $this->registered_modules;
            $module_type = $posted_params['module_type'];
            if ( !array_key_exists( $module_type, $registered_modules  ) || empty( $registered_modules[ $module_type ] ) ) {
                return;
            }

            $module_params = $registered_modules[ $module_type ];
            // Store the params now, so we can access them when rendering the input templates
            $this->current_module_params_when_ajaxing = $module_params;

            $tmpl_params = $module_params[ 'tmpl' ];
            // Enqueue the list of registered scripts
            if ( empty( $tmpl_params ) ) {
                return;
            }
            // the requested_tmpl can be pre-item, mod-opt or item-inputs
            $tmpl_map = array_key_exists( $requested_tmpl, $tmpl_params ) ? $tmpl_params[ $requested_tmpl ] : array();
            if ( empty( $tmpl_map ) ) {
                return;
            }
            // Do we have tabs ?
            // With tabs
            // 'tabs' => array(
              // array(
              //     'title' => __('Spacing', 'text_doma'),
              //     'inputs' => array(
              //         'padding' => array(
              //             'input_type'  => 'number',
              //             'title'       => __('Padding', 'text_doma')
              //         ),
              //         'margin' => array(
              //             'input_type'  => 'number',
              //             'title'       => __('Margin', 'text_doma')
              //         )
              //     )
              // ),
              // array( ... )
              //
              //
              // Without tabs :
              //  'padding' => array(
              //       'input_type'  => 'number',
              //       'title'       => __('Padding', 'text_doma')
              //  ),
              //   'margin' => array(
              //      'input_type'  => 'number',
              //      'title'       => __('Margin', 'text_doma')
              //  )
            if ( array_key_exists( 'tabs', $tmpl_map ) ) {
                ob_start();
                ?>
                <div class="tabs tabs-style-topline">
                  <nav>
                    <ul>
                      <?php
                        // print the tabs nav
                        foreach ( $tmpl_map['tabs'] as $_key => $tab ) {
                          printf( '<li data-tab-id="section-topline-%1$s" %2$s><a href="#"><span>%3$s</span></a></li>',
                              esc_attr($_key + 1),
                              esc_attr(array_key_exists('attributes', $tab) ? $tab['attributes'] : ''),
                              esc_html($tab['title'])
                          );
                        }//foreach
                      ?>
                    </ul>
                  </nav>
                  <div class="content-wrap">
                    <?php
                      foreach ( $tmpl_map['tabs'] as $_key => $tab ) {
                        printf( '<section id="section-topline-%1$s">%2$s</section>',
                            esc_attr($_key + 1),
                            wp_kses_post($this->ac_generate_czr_tmpl_from_map( $tab['inputs'] ))
                        );
                      }//foreach
                    ?>
                  </div><?php //.content-wrap ?>
                </div><?php //.tabs ?>
                <?php
                return ob_get_clean();
            } else {
                return $this->ac_generate_czr_tmpl_from_map( $tmpl_map );
            }
        }

    }//class
endif;

?>