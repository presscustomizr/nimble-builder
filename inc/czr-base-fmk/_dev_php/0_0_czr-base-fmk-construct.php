<?php
namespace Nimble;

if ( did_action('nimble_base_fmk_loaded') ) {
    if ( ( defined( 'CZR_DEV' ) && CZR_DEV ) || ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) ) {
        error_log( __FILE__ . '  => The czr_base_fmk has already been loaded' );
    }
    return;
}

// Set the namsepace as a global so we can use it when fired from another theme/plugin using the fmk
global $czr_base_fmk_namespace;
$czr_base_fmk_namespace = __NAMESPACE__ . '\\';

do_action( 'nimble_base_fmk_loaded' );
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( !class_exists( 'CZR_Fmk_Base_Construct' ) ) :
    class CZR_Fmk_Base_Construct {
        static $instance;

        public $registered_modules = array();//stores the collection of dynamic modules registration candidates
        public $registered_settings = array();//stores the collection of dynamic settings registration candidates

        public $default_dynamic_setting_params = array();
        public $default_dynamic_module_params = array();

        public $czr_css_attr = array();

        public $current_module_params_when_ajaxing;// store the params when ajaxing and allows us to access the currently requested module params at any point of the ajax action

        public static function czr_fmk_get_instance( $params ) {
            if ( !isset( self::$instance ) && !( self::$instance instanceof CZR_Fmk_Base ) ) {
              self::$instance = new CZR_Fmk_Base( $params );
            }
            return self::$instance;
        }

        //@param $params = array(
        //  'base_url' => '' <= path to root class folder
        //)
        function __construct( $params = array() ) {
            if ( !is_array( $params ) || empty( $params ) ) {
                error_log( 'CZR_Fmk_Base => constructor => missing params');
                return;
            }
            if ( empty( $params['base_url'] ) ) {
                error_log( 'CZR_Fmk_Base => constructor => wrong params');
                return;
            }

            // DEFINITIONS
            if ( !defined( 'NIMBLE_FMK_BASE_URL' ) ) { define( 'NIMBLE_FMK_BASE_URL' , $params['base_url'] ); }
            if ( !defined( 'NIMBLE_FMK_BASE_VERSION' ) ) { define( 'NIMBLE_FMK_BASE_VERSION' , isset( $params['version'] ) ? $params['version'] : '1.0.0' ); }

            // Cache the css attr used in the tmpl builder and in the localized params
            $this->czr_css_attr = $this->czr_fmk_get_customizer_controls_css_attr();

            // Cache the default dynamic params
            $this->default_dynamic_setting_params = $this->czr_fmk_get_default_dynamic_setting_params();
            $this->default_dynamic_module_params = $this->czr_fmk_get_default_dynamic_module_params();

            // Enqueue the fmk control js + a module tmpl
            $this->czr_enqueue_fmk_resources();

            // ajax filters + template generator
            $this->czr_setup_ajax_tmpl();

            // Dynamic Module Registration
            $this->czr_setup_dynamic_settings_registration();
            $this->czr_setup_dynamic_modules_registration();

            // Content picker
            $this->czr_setup_content_picker_ajax_actions();
        }//__construct


        // fired in the constructor to cache the params in a property
        private function czr_fmk_get_default_dynamic_setting_params() {
          return array(
                'setting_id' => '',
                'dynamic_registration' => true,
                'module_type' => '',
                'option_value' => array(),

                'setting' => array(
                    'type' => 'option',
                    'default'  => array(),
                    'transport' => 'refresh',
                    'setting_class' => '',//array( 'path' => '', 'name' => '' )
                    'sanitize_callback' => '',
                    'validate_callback' => '',
                ),

                'section' => array(
                    'id' => '',
                    'title' => '',
                    'panel' => '',
                    'priority' => 10
                ),

                'control' => array(
                    'label' => '',
                    'type'  => 'czr_module',
                    'priority' => 10,
                    'control_class' => ''//array( 'path' => '', 'name' => '' )
                )
            );
        }

        // fired in the constructor to cache the params in a property
        private function czr_fmk_get_default_dynamic_module_params() {
          return array(
                'dynamic_registration' => true,
                'module_type' => '',

                'sanitize_callback' => '', //<= used when dynamically registering a setting
                'validate_callback' => '', //<= used when dynamically registering a setting

                'customizer_assets' => array(
                    'control_js' => array(),
                    'localized_control_js' => array()
                ),
                'tmpl' => array()
            );
        }

        // Copy of czr_fn_get_controls_css_attr() and the equivalent in Hueman Pro
        public function czr_fmk_get_customizer_controls_css_attr() {
          return apply_filters('czr_fmk_controls_css_attr',
            array(
              'multi_input_wrapper' => 'czr-multi-input-wrapper',
              'sub_set_wrapper'     => 'czr-sub-set',
              'sub_set_input'       => 'czr-input',
              'img_upload_container' => 'czr-imgup-container',

              'edit_modopt_icon'    => 'czr-toggle-modopt',
              'close_modopt_icon'   => 'czr-close-modopt',
              'mod_opt_wrapper'     => 'czr-mod-opt-wrapper',


              'items_wrapper'     => 'czr-items-wrapper',
              'single_item'        => 'czr-single-item',
              'item_content'      => 'czr-item-content',
              'item_header'       => 'czr-item-header',
              'item_title'        => 'czr-item-title',
              'item_btns'         => 'czr-item-btns',
              'item_sort_handle'   => 'czr-item-sort-handle',

              //remove dialog
              'display_alert_btn' => 'czr-display-alert',
              'remove_alert_wrapper'   => 'czr-remove-alert-wrapper',
              'cancel_alert_btn'  => 'czr-cancel-button',
              'remove_view_btn'        => 'czr-remove-button',

              'edit_view_btn'     => 'czr-edit-view',
              //pre add dialog
              'open_pre_add_btn'      => 'czr-open-pre-add-new',
              'adding_new'        => 'czr-adding-new',
              'pre_add_wrapper'   => 'czr-pre-add-wrapper',
              'pre_add_item_content'   => 'czr-pre-add-view-content',
              'cancel_pre_add_btn'  => 'czr-cancel-add-new',
              'add_new_btn'       => 'czr-add-new',
              'pre_add_success'   => 'czr-add-success'
            )
          );
        }
}//class
endif;

?>