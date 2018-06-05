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
            if ( 0 === strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id );
                return array(
                    'transport' => 'refresh',
                    'type' => 'option',
                    'default' => array()
                    //'sanitize_callback'    => array( $this, 'sanitize_callback' )
                    //'validate_callback'    => array( $this, 'validate_callback' )
                );
            } else if ( 0 === strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTIONS_NOT_SAVED ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id );
                return array(
                    'transport' => 'refresh',
                    'type' => '_no_intended_to_be_saved_',
                    'default' => array()
                    //'sanitize_callback'    => array( $this, 'sanitize_callback' )
                    //'validate_callback'    => array( $this, 'validate_callback' )
                );
            }

            //sek_error_log( print_r( $setting_args, true ) );
            return $setting_args;
            //return wp_parse_args( array( 'default' => array() ), $setting_args );
        }

        //@filter 'customize_dynamic_setting_class'
        function set_dyn_setting_class( $class, $setting_id, $args ) {
            // shall start with 'sek___'
            if ( 0 !== strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION ) )
              return $class;
            //sek_error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
            return '\Nimble\Nimble_Customizer_Setting';
        }




        // done in javascript
        function sanitize_callback( $setting_data, $setting_instance ) {
            sek_error_log( 'in_sek_sanitize_callback for setting id ' . $setting_instance->id, $setting_data );
            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $setting_data );
            return $setting_data;
        }

        // done in javascript
        function validate_callback( $validity, $setting_data, $setting_instance ) {
            //sek_error_log( 'in sek_validate_callback for setting id ' . $setting_instance->id, $setting_data );
            //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $setting_data );
            return null;
        }


 }//class
endif;

?>