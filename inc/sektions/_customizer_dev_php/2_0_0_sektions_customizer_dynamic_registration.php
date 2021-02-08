<?php
/* ------------------------------------------------------------------------- *
 *  SETUP DYNAMIC SERVER REGISTRATION FOR SETTING
/* ------------------------------------------------------------------------- */
// Fired @'after_setup_theme:20'
if ( !class_exists( 'SEK_CZR_Dyn_Register' ) ) :
    class SEK_CZR_Dyn_Register {
        static $instance;
        public $sanitize_callbacks = array();// <= will be populated to cache the callbacks when invoking sek_get_module_sanitize_callbacks().

        public static function get_instance( $params ) {
            if ( !isset( self::$instance ) && !( self::$instance instanceof SEK_CZR_Dyn_Register ) )
              self::$instance = new SEK_CZR_Dyn_Register( $params );
            return self::$instance;
        }

        function __construct( $params = array() ) {
            // Schedule the loading the skoped settings class
            add_action( 'customize_register', array( $this, 'load_nimble_setting_class' ) );

            add_filter( 'customize_dynamic_setting_args', array( $this, 'set_dyn_setting_args' ), 10, 2 );
            add_filter( 'customize_dynamic_setting_class', array( $this, 'set_dyn_setting_class') , 10, 3 );
        }//__construct

        //@action 'customize_register'
        function load_nimble_setting_class() {
            require_once(  NIMBLE_BASE_PATH . '/inc/sektions/seks_setting_class.php' );
        }

        //@filter 'customize_dynamic_setting_args'
        function set_dyn_setting_args( $setting_args, $setting_id ) {
            // shall start with "nimble___" or "__nimble_options__"
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) || 0 === strpos( $setting_id, NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => 'option',
                    'default' => array(),
                    'sanitize_callback'    => array( $this, 'sanitize_callback' )
                    //'validate_callback'    => array( $this, 'validate_callback' )
                );
            } else if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => '_nimble_ui_',//won't be saved as is,
                    'default' => array(),
                    //'sanitize_callback' => array( $this, 'sanitize_callback' ),
                    //'validate_callback' => array( $this, 'validate_callback' )
                );
            }
            return $setting_args;
            //return wp_parse_args( array( 'default' => array() ), $setting_args );
        }


        //@filter 'customize_dynamic_setting_class'
        function set_dyn_setting_class( $class, $setting_id, $args ) {
            // shall start with 'nimble___'
            if ( 0 !== strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) )
              return $class;
            //sek_error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
            return '\Nimble\Nimble_Customizer_Setting';
        }


        // Uses the sanitize_callback function specified on module registration if any
        function sanitize_callback( $setting_data, $setting_instance ) {
            if ( !is_array( $setting_data ) ) {
                return $setting_data;
            } else {
                if ( !is_array( $setting_data ) ) {
                    return $setting_data;
                } else {
                    if ( array_key_exists('module_type', $setting_data ) ) {
                        $san_callback = sek_get_registered_module_type_property( $setting_data['module_type'], 'sanitize_callback' );
                        if ( !empty( $san_callback ) && is_string( $san_callback ) && function_exists( $san_callback ) && array_key_exists('value', $setting_data ) ) {
                            $setting_data['value'] = $san_callback( $setting_data['value'] );
                        }
                    } else {
                        foreach( $setting_data as $k => $data ) {
                            $setting_data[$k] = $this->sanitize_callback($data, $setting_instance);
                        }
                    }
                }
            }

            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_doma' ), $setting_data );
            return $setting_data;
        }

        // Uses the validate_callback function specified on module registration if any
        // @return validity object
        function validate_callback( $validity, $setting_data, $setting_instance ) {
            $validated = true;
            if ( !is_array( $setting_data ) ) {
                return $setting_data;
            } else {
                if ( !is_array( $setting_data ) ) {
                    return $setting_data;
                } else {
                    if ( array_key_exists('module_type', $setting_data ) ) {
                        $validation_callback = sek_get_registered_module_type_property( $setting_data['module_type'], 'validate_callback' );
                        if ( !empty( $validation_callback ) && is_string( $validation_callback ) && function_exists( $validation_callback ) && array_key_exists('value', $setting_data ) ) {
                            $validated = $validation_callback( $setting_data );
                        }
                    } else {
                        foreach( $setting_data as $k => $data ) {
                            $validated = $this->validate_callback($validity, $data, $setting_instance);
                        }
                    }
                }
            }

            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_doma' ), $setting_data );
            if ( true !== $validated ) {
                if ( is_wp_error( $validated ) ) {
                    $validation_msg = $validation_msg->get_error_message();
                    $validity->add(
                        'nimble_validation_error_in_' . $setting_instance->id ,
                        $validation_msg
                    );
                }

            }
            return $validity;
        }


 }//class
endif;

?>