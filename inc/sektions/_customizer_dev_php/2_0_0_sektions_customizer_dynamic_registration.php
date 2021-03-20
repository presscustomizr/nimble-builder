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
            // shall start with "nimble___" or "nimble_global_opts"
            // those are the setting that will actually be saved in DB : 
            // - sektion collections ( local and global skope )
            // - global options
            // - site template options
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) || 0 === strpos( $setting_id, NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => 'option',
                    'default' => array(),
                    // Only the section collections are sanitized on save
                    'sanitize_callback' => 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ? '\Nimble\sek_sektion_collection_sanitize_cb' : null
                    //'validate_callback'    => '\Nimble\sek_sektion_collection_validate_cb'
                );
            } else if ( 0 === strpos( $setting_id, NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                        'type' => '_nimble_ui_',//won't be saved as is,
                    'default' => array(),
                    //'sanitize_callback' => array( $this, 'sanitize_callback' ),
                    //'validate_callback'    => '\Nimble\sek_sektion_collection_validate_cb'
                );
            }
            return $setting_args;
            //return wp_parse_args( array( 'default' => array() ), $setting_args );
        }


        //@filter 'customize_dynamic_setting_class'
        // We use a custom setting class only for the section collections ( local and global ), not for global options and site template options
        function set_dyn_setting_class( $class, $setting_id, $args ) {
            //sek_error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
            // Setting class for NB global options and Site Template options
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ) ) {
                return '\Nimble\Nimble_Options_Setting';
            }
            
            // Setting class for NB sektion collections => shall start with 'nimble___'
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) {
                return '\Nimble\Nimble_Collection_Setting';
            }
            return $class;
        }

 }//class
endif;

?>