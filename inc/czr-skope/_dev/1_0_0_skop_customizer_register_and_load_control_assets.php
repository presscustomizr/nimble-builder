<?php
/////////////////////////////////////////////////////////////////
// PRINT CUSTOMIZER JAVASCRIPT + LOCALIZED DATA
if ( !class_exists( 'Flat_Skop_Register_And_Load_Control_Assets' ) ) :
    class Flat_Skop_Register_And_Load_Control_Assets extends Flat_Skop_Base {
          // Fired in Flat_Skop_Base::__construct()
          public function skp_register_and_load_control_assets() {
              add_action( 'customize_controls_enqueue_scripts', array( $this, 'skp_enqueue_controls_js_css' ), 20 );
          }

          public function skp_enqueue_controls_js_css() {
              $_use_unminified = defined('CZR_DEV')
                  && true === CZR_DEV
                  // && false === strpos( dirname( dirname( dirname (__FILE__) ) ) , 'inc/wfc' )
                  && file_exists( sprintf( '%s/assets/czr/js/czr-skope-base.js' , dirname( __FILE__ ) ) );

              $_prod_script_path = sprintf(
                  '%1$s/assets/czr/js/%2$s' ,
                  NIMBLE_SKOPE_BASE_URL,
                  $_use_unminified ? 'czr-skope-base.js' : 'czr-skope-base.min.js'
              );

              wp_enqueue_script(
                  'czr-skope-base',
                  //dev / debug mode mode?
                  $_prod_script_path,
                  array('customize-controls' , 'jquery', 'underscore'),
                  ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() :  wp_get_theme()->version,
                  $in_footer = true
              );

              wp_localize_script(
                  'czr-skope-base',
                  'FlatSkopeLocalizedData',
                  array(
                      'noGroupSkopeList' => skp_get_no_group_skope_list(),
                      'defaultSkopeModel' => skp_get_default_skope_model(),
                      'i18n' => array()
                  )
              );
          }
    }//class
endif;

?>