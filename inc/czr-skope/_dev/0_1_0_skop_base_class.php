<?php

////////////////////////////////////////////////////////////////
// FLAT SKOPE BASE
//  This Class is instantiated on 'hu_hueman_loaded', declared in /init-core.php
if ( ! class_exists( 'Flat_Skop_Base' ) ) :
    class Flat_Skop_Base {
        static $instance;
        public $current_skope_ids = array();//will be cached on the first invokation of skp_get_skope_id, if 'wp' done

        public static function skp_get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Flat_Skop_Base ) )
              self::$instance = new Flat_Skope_Clean_Final( $params );
            return self::$instance;
        }
        function __construct( $params = array() ) {
            $defaults = array(
                'base_url_path' => ''//NIMBLE_BASE_URL . '/inc/czr-skope/'
            );
            $params = wp_parse_args( $params, $defaults );
            if ( ! defined( 'SKOPE_BASE_URL' ) ) { define( 'SKOPE_BASE_URL' , $params['base_url_path'] ); }
            if ( ! defined( 'SKOPE_ID_PREFIX' ) ) { define( 'SKOPE_ID_PREFIX' , "skp__" ); }

            $this->skp_register_and_load_control_assets();
            $this->skp_export_skope_data_and_schedule_sending_to_panel();
            $this->skp_schedule_cleaning_on_object_delete();
        }//__construct
    }
endif;
?>