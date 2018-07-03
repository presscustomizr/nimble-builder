<?php
/* ------------------------------------------------------------------------- *
 *  SETUP DYNAMIC SERVER REGISTRATION FOR SETTING
/* ------------------------------------------------------------------------- */
if ( ! class_exists( 'SEK_CZR_Dyn_Register' ) ) :
    class SEK_CZR_Dyn_Register {
        static $instance;
        public $sanitize_callbacks = array();// <= will be populated to cache the callbacks when invoking sek_get_module_sanitize_callbacks().

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SEK_CZR_Dyn_Register ) )
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
            require_once(  dirname( __FILE__ ) . '/customizer/seks_setting_class.php' );
        }

        //@filter 'customize_dynamic_setting_args'
        function set_dyn_setting_args( $setting_args, $setting_id ) {
            // shall start with "sek__"
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => 'option',
                    'default' => array(),
                    //'sanitize_callback'    => array( $this, 'sanitize_callback' )
                    //'validate_callback'    => array( $this, 'validate_callback' )
                );
            } else if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => '_nimble_ui_',//won't be saved as is,
                    'default' => array(),
                    'sanitize_callback' => array( $this, 'sanitize_callback' )
                );
            }

            //sek_error_log( print_r( $setting_args, true ) );
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


        // Use the sanitize_callback function specified on module registration if any
        function sanitize_callback( $setting_data, $setting_instance ) {
            if ( isset( $_POST['skope_id'] ) ) {
                //sek_error_log( 'in_sek_sanitize_callback for setting id ' . $setting_instance->id, sek_get_skoped_seks( $_POST['skope_id'] ) );//sek_get_level_model( $setting_instance->id ) );
                $sektionSettingValue = sek_get_skoped_seks( $_POST['skope_id'] );
                if ( is_array( $sektionSettingValue ) ) {
                    $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
                    if ( is_array( $sektion_collection ) ) {
                        $model = sek_get_level_model( $setting_instance->id, $sektion_collection );
                        if ( is_array( $model ) && ! empty( $model['module_type'] ) ) {
                            //sek_error_log( 'in_sek_sanitize_callback for setting id ' . $setting_instance->id, sek_get_level_model( $setting_instance->id, $sektion_collection ) );
                            $module_params = CZR_Fmk_Base() -> czr_get_registered_dynamic_module( $model['module_type'] );
                            if ( is_array( $module_params ) && array_key_exists( 'sanitize_callback', $module_params ) && function_exists( $module_params[ 'sanitize_callback' ] ) ) {
                                $setting_data = $module_params[ 'sanitize_callback' ]( $setting_data );
                            }
                        }
                    }
                }
            }
            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $setting_data );
            return $setting_data;
        }

        function validate_callback( $validity, $setting_data, $setting_instance ) {
            //sek_error_log( 'in sek_validate_callback for setting id ' . $setting_instance->id, $setting_data );
            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $setting_data );
            return null;
        }


 }//class
endif;

?>