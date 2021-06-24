<?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( !class_exists( 'CZR_Fmk_Dyn_Setting_Registration' ) ) :
    class CZR_Fmk_Dyn_Setting_Registration extends CZR_Fmk_Base_Tmpl_Builder {

        //fired in the constructor
        function czr_setup_dynamic_settings_registration() {
            add_action( 'customize_register', array( $this, 'czr_setup_dynamic_setting_registration' ), 10 );

            // if we have dynamic setting params, let's add a filter to serverControlParams
            // filter declared when localizing 'serverControlParams' @see resources
            add_filter( 'czr_fmk_dynamic_setting_js_params', array( $this, 'czr_setup_localized_params_for_dynamic_js_registration' ), 20 );

            // when not dynamically registered
            // TO DEPRECATE ?
            //add_action( 'customize_register', array( $this, 'czr_register_not_dynamic_settings' ), 20 );
        }


        ////////////////////////////////////////////////////////////////
        // PRE REGISTRATION FOR SETTINGS
        // Default params
        // array(
        //     'setting_id' => '',
        //     'dynamic_registration' => true,
        //     'module_type' => '',
        //     'option_value' => array(),

        //     'setting' => array(
        //         'type' => 'option',
        //         'default'  => array(),
        //         'transport' => 'refresh',
        //         'setting_class' => '',//array( 'path' => '', 'name' => '' )
        //         'sanitize_callback' => '',
        //         'validate_callback' => '',
        //     ),

        //     'section' => array(
        //         'id' => '',
        //         'title' => '',
        //         'panel' => '',
        //         'priority' => 10
        //     ),

        //     'control' => array(
        //         'label' => '',
        //         'type'  => 'czr_module',
        //         'priority' => 10,
        //         'control_class' => ''//array( 'path' => '', 'name' => '' )
        //     )
        // )
        function czr_pre_register_dynamic_setting( $setting_params ) {
            if ( !is_array( $setting_params ) || empty( $setting_params ) ) {
                error_log( 'czr_pre_register_dynamic_setting => empty $setting_params submitted' );
                return;
            }
            if ( !array_key_exists( 'setting_id', $setting_params ) || empty( $setting_params['setting_id'] ) ) {
                error_log( 'czr_pre_register_dynamic_setting => missing setting id' );
                return;
            }

            // normalize
            $setting_params = wp_parse_args( $setting_params, $this -> default_dynamic_setting_params );

            $registered = $this->registered_settings;
            $setting_id_candidate = $setting_params['setting_id'];

            // A setting id can be registered only once.
            // Already registered ?
            if ( array_key_exists( $setting_id_candidate, $registered ) ) {
                error_log( 'czr_pre_register_dynamic_setting => setting id already registered => ' . $setting_id_candidate );
                return;
            }
            $registered[ $setting_id_candidate ] = $setting_params;
            $this->registered_settings = $registered;
        }



        ////////////////////////////////////////////////////////////////
        // FILTER DYNAMIC SETTING AND CLASS ARGS
        // hook : customize_register
        // Those filters are declared by WP core in class-wp-customize-manager.php
        // in => add_action( 'customize_register', array( $this, 'register_dynamic_settings' ), 11 );
        function czr_setup_dynamic_setting_registration( $wp_customize ) {
            add_filter( 'customize_dynamic_setting_args', array( $this, 'czr_setup_customizer_dynamic_setting_args' ), 10, 2  );
            add_filter( 'customize_dynamic_setting_class', array( $this, 'czr_setup_customizer_dynamic_setting_class' ), 10, 3 );
        }

        // hook : 'customize_dynamic_setting_args'
        function czr_setup_customizer_dynamic_setting_args( $setting_args, $setting_id ) {
            //sek_error_log( __CLASS__ . '::' . __FUNCTION__ ,  $this->registered_settings );

            if ( !is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
              return $setting_args;

            // let's initialize the args to the provided param
            $registered_setting_args = $setting_args;

            // loop on each registered modules
            foreach ( $this->registered_settings as $registerered_setting_id => $params ) {

                if ( array_key_exists('dynamic_registration', $params) && true !== $params['dynamic_registration'] ) {
                  continue;
                }

                $params = wp_parse_args( $params, $this -> default_dynamic_setting_params );

                if ( $registerered_setting_id != $setting_id || empty( $registerered_setting_id ) )
                  continue;

                $setting_args = is_array( $params['setting'] ) ? $params['setting'] : array();
                $setting_args = wp_parse_args( $setting_args, array(
                    'type'                 => 'option',
                    'default'              => array(),
                    'transport'            => 'refresh',
                    'sanitize_callback'    => '',
                    'validate_callback'    => ''
                ) );

                // Provide new setting args
                $registered_setting_args = array(
                    'type'                 => empty( $setting_args[ 'type' ] ) ? 'option' : $setting_args[ 'type' ],
                    'default'              => array(),
                    'transport'            => $setting_args[ 'transport' ],
                    'sanitize_callback'    => ( !empty( $setting_args[ 'sanitize_callback' ] ) && function_exists( $setting_args[ 'sanitize_callback' ] ) ) ? $setting_args[ 'sanitize_callback' ] : '',
                    'validate_callback'    => ( !empty( $setting_args[ 'validate_callback' ] ) && function_exists( $setting_args[ 'validate_callback' ] ) ) ? $setting_args[ 'validate_callback' ] : ''
                );

                // if this is a module setting, it can have specific sanitize and validate callback set for the module
                // Let's check if the module_type is registered, and if there are any callback set.
                // If a match is found, we'll use those callback
                $module_params = $this -> czr_get_registered_dynamic_module( $params[ 'module_type' ] );
                if ( false !== $module_params  && is_array( $module_params ) ) {
                    if ( array_key_exists( 'validate_callback', $module_params ) && function_exists( $module_params[ 'validate_callback' ] ) ) {
                        $registered_setting_args[ 'validate_callback' ] = $module_params[ 'validate_callback' ];
                    }
                    if ( array_key_exists( 'sanitize_callback', $module_params ) && function_exists( $module_params[ 'sanitize_callback' ] ) ) {
                        $registered_setting_args[ 'sanitize_callback' ] = $module_params[ 'sanitize_callback' ];
                    }
                }
                //error_log( 'REGISTERING DYNAMICALLY for setting =>'. $setting_id );
            }
            return $registered_setting_args;
        }


        // hook : 'customize_dynamic_setting_class'
        function czr_setup_customizer_dynamic_setting_class( $class, $setting_id, $args ) {
            if ( !is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
              return $class;


            // let's initialize the args to the provided class
            $registered_setting_class = $class;//'WP_Customize_Setting' by default

            // loop on each registered modules
            foreach ( $this->registered_settings as $registerered_setting_id => $params ) {
                $params = wp_parse_args( $params, $this -> default_dynamic_setting_params );
                if ( true !== $params['dynamic_registration'] ) {
                  continue;
                }
                if ( $registerered_setting_id != $setting_id || empty( $registerered_setting_id ) )
                  continue;

                $setting_args = $params['setting'];

                if ( is_array( $setting_args ) && array_key_exists( 'setting_class', $setting_args ) ) {
                    // provide new setting class if exists and not yet loaded
                    if ( is_array( $setting_args[ 'setting_class' ] ) && array_key_exists( 'name', $setting_args[ 'setting_class' ] ) && array_key_exists( 'path', $setting_args[ 'setting_class' ] ) ) {
                        if ( !class_exists( $setting_args[ 'setting_class' ][ 'name' ] ) && file_exists( $setting_args[ 'setting_class' ]['path'] ) ) {
                            require_once(  $setting_args[ 'setting_class' ]['path'] );
                        }
                        if ( class_exists( $setting_args[ 'setting_class' ][ 'name' ] ) ) {
                            $registered_setting_class = $setting_args[ 'setting_class' ][ 'name' ];
                        }
                    }
                }
                //error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>'. $setting_id );
            }

            return $registered_setting_class;
        }


        ////////////////////////////////////////////////////////////////
        // Print js params for dynamic control and section registration
        // hook : czr_fmk_dynamic_setting_js_params'
        // @param js_param = array()
        function czr_setup_localized_params_for_dynamic_js_registration( $js_params ) {
            // error_log( '<REGISTERED SETTINGS>' );
            // error_log( print_r( $this->registered_settings, true ) );
            // error_log( '</REGISTERED SETTINGS>' );
            if ( !is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
              return $js_params;
            $js_params = !is_array( $js_params ) ? array() : $js_params;

            //  'localized_control_js' => array(
            //     'deps' => 'czr-customizer-fmk',
            //     'global_var_name' => 'socialLocalized',
            //     'params' => array(
            //         //Social Module
            //         'defaultSocialColor' => 'rgb(90,90,90)',
            //         'defaultSocialSize'  => 14,
            //         //option value for dynamic registration
            //
            //     )
            //     'dynamic_setting_registration' => array(
            //         'values' => $args['option_value'],
            //         'section' => $args['section']
            //      )
            // )
            // loop on each registered modules
            foreach ( $this->registered_settings as $registerered_setting_id => $params ) {
                $params = wp_parse_args( $params, $this -> default_dynamic_setting_params );
                // We need the 'option_value' entry, even if empty
                if ( !array_key_exists( 'option_value', $params ) || !is_array( $params['option_value'] ) )
                  continue;
                // Check if not already setup
                if ( array_key_exists( $registerered_setting_id, $params ) ) {
                    error_log( 'czr_setup_localized_params_for_dynamic_js_registration => js_params already setup for setting : ' . $registerered_setting_id );
                }

                $js_params[ $registerered_setting_id ] = array(
                    'setting_id' => $registerered_setting_id,
                    'module_type' => $params[ 'module_type' ],
                    'module_registration_params' => $this -> czr_get_registered_dynamic_module( $params[ 'module_type' ] ),
                    'option_value'  => $params['option_value'],

                    // 'setting' => array(
                    //     'type' => 'option',
                    //     'default'  => array(),
                    //     'transport' => 'refresh',
                    //     'setting_class' => '',//array( 'path' => '', 'name' => '' )
                    //     'sanitize_callback' => '',
                    //     'validate_callback' => '',
                    // ),
                    'setting' => array_key_exists( 'setting', $params ) ? $params[ 'setting' ] : array(),

                    // 'section' => array(
                    //     'id' => '',
                    //     'title' => '',
                    //     'panel' => '',
                    //     'priority' => 10
                    // ),
                    'section' => array_key_exists( 'section', $params ) ? $params[ 'section' ] : array(),

                    // 'control' => array(
                    //     'label' => '',
                    //     'type'  => 'czr_module',
                    //     'priority' => 10,
                    //     'control_class' => ''//array( 'path' => '', 'name' => '' )
                    // ),
                    'control' => array_key_exists( 'control', $params ) ? $params[ 'control' ] : array(),
                );
            }
            return $js_params;
        }














        ////////////////////////////////////////////////////////////////
        // TO DEPRECATE ?
        // REGISTER IF NOT DYNAMIC
        // hook : customize_register
        function czr_register_not_dynamic_settings( $wp_customize ) {
            // error_log('<MODULE REGISTRATION>');
            // error_log(print_r( $this->registered_settings, true ));
            // error_log('</MODULE REGISTRATION>');

            if ( !is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
              return;

            // loop on each registered modules
            foreach ( $this->registered_settings as $setting_id => $params ) {
                $params = wp_parse_args( $params, $this -> default_dynamic_setting_params );
                if ( true === $params['dynamic_registration'] )
                  continue;


                // SETTING
                $setting_args = $params['setting'];
                $registered_setting_class = 'WP_Customize_Setting';
                if ( is_array( $setting_args ) && array_key_exists( 'setting_class', $setting_args ) ) {
                    // provide new setting class if exists and not yet loaded
                    if ( is_array( $setting_args[ 'setting_class' ] ) && array_key_exists( 'name', $setting_args[ 'setting_class' ] ) && array_key_exists( 'path', $setting_args[ 'setting_class' ] ) ) {
                        if ( !class_exists( $setting_args[ 'setting_class' ][ 'name' ] ) && file_exists( $setting_args[ 'setting_class' ]['path'] ) ) {
                            require_once(  $setting_args[ 'setting_class' ]['path'] );
                        }
                        if ( class_exists( $setting_args[ 'setting_class' ][ 'name' ] ) ) {
                            $registered_setting_class = $setting_args[ 'setting_class' ][ 'name' ];
                        }
                    }
                }

                $wp_customize->add_setting( new $registered_setting_class( $wp_customize, $setting_id,  array(
                    'default'  => $setting_args[ 'default' ],
                    'type'  => $setting_args[ 'type' ],
                    'sanitize_callback' => isset( $settings_args[ 'sanitize_callback' ] ) ? $settings_args[ 'sanitize_callback' ] : ''
                ) ) );


                // CONTROL
                $control_args = $params['control'];
                $registered_control_class = 'WP_Customize_Control';
                if ( is_array( $control_args ) && array_key_exists( 'control_class', $control_args ) ) {
                    // provide new setting class if exists and not yet loaded
                    if ( is_array( $control_args[ 'control_class' ] ) && array_key_exists( 'name', $control_args[ 'control_class' ] ) && array_key_exists( 'path', $control_args[ 'control_class' ] ) ) {
                        if ( !class_exists( $control_args[ 'control_class' ][ 'name' ] ) && file_exists( $control_args[ 'control_class' ]['path'] ) ) {
                            require_once(  $control_args[ 'control_class' ]['path'] );
                        }
                        if ( class_exists( $control_args[ 'control_class' ][ 'name' ] ) ) {
                            $registered_control_class = $control_args[ 'control_class' ][ 'name' ];
                        }
                    }
                }

                $wp_customize -> add_control( new $registered_control_class( $wp_customize, $setting_id, array(
                    'type'      => $control_args[ 'type' ],
                    'label'     => $control_args[ 'label' ],
                    'section'   => $params[ 'section' ]['id'],
                    'module_type' => $params[ 'module_type' ]
                ) ) );

            }//foreach
        }//czr_register_not_dynamic_settings

    }//class
endif;

?>