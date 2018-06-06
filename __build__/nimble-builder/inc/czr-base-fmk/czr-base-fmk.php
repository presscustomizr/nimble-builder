<?php
namespace Nimble;

if ( did_action('nimble_base_fmk_loaded') ) {
    error_log( __FILE__ . '  => The czr_base_fmk has already been loaded' );
    return;
}

// Set the namsepace as a global so we can use it when fired from another theme/plugin using the fmk
global $czr_base_fmk_namespace;
$czr_base_fmk_namespace = __NAMESPACE__ . '\\';

do_action( 'nimble_base_fmk_loaded' );
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( ! class_exists( 'CZR_Fmk_Base_Construct' ) ) :
    class CZR_Fmk_Base_Construct {
        static $instance;

        public $registered_modules = array();//stores the collection of dynamic modules registration candidates
        public $registered_settings = array();//stores the collection of dynamic settings registration candidates

        public $default_dynamic_setting_params = array();
        public $default_dynamic_module_params = array();

        public $czr_css_attr = array();

        public static function czr_fmk_get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CZR_Fmk_Base ) ) {
              self::$instance = new CZR_Fmk_Base( $params );
            }
            return self::$instance;
        }

        //@param $params = array(
        //  'base_url' => '' <= path to root class folder
        //)
        function __construct( $params = array() ) {
            if ( ! is_array( $params ) || empty( $params ) ) {
                error_log( 'CZR_Fmk_Base => constructor => missing params');
                return;
            }
            if ( empty( $params['base_url'] ) ) {
                error_log( 'CZR_Fmk_Base => constructor => wrong params');
                return;
            }

            // DEFINITIONS
            if ( ! defined( 'NIMBLE_FMK_BASE_URL' ) ) { define( 'NIMBLE_FMK_BASE_URL' , $params['base_url'] ); }
            if ( ! defined( 'NIMBLE_FMK_BASE_VERSION' ) ) { define( 'NIMBLE_FMK_BASE_VERSION' , isset( $params['version'] ) ? $params['version'] : '1.0.0' ); }

            // Cache the css attr used in the tmpl builder and in the localized params
            $this -> czr_css_attr = $this -> czr_fmk_get_customizer_controls_css_attr();

            // Cache the default dynamic params
            $this -> default_dynamic_setting_params = $this -> czr_fmk_get_default_dynamic_setting_params();
            $this -> default_dynamic_module_params = $this -> czr_fmk_get_default_dynamic_module_params();

            // Enqueue the fmk control js + a module tmpl
            $this -> czr_enqueue_fmk_resources();

            // ajax filters + template generator
            $this -> czr_setup_ajax_tmpl();

            // Dynamic Module Registration
            $this -> czr_setup_dynamic_settings_registration();
            $this -> czr_setup_dynamic_modules_registration();
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

?><?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( ! class_exists( 'CZR_Fmk_Base_Load_Resources' ) ) :
    class CZR_Fmk_Base_Load_Resources extends CZR_Fmk_Base_Construct {

        // fired in the constructor
        function czr_enqueue_fmk_resources() {
            // Enqueue the fmk control js
            add_action ( 'customize_controls_enqueue_scripts' , array( $this, 'ac_load_additional_controls_js' ) );
            add_action ( 'customize_controls_enqueue_scripts' , array( $this, 'ac_load_additional_controls_css' ) );

            // Enqueue the base preview js
            //hook : customize_preview_init
            add_action ( 'customize_preview_init' , array( $this, 'ac_customize_load_preview_js' ) );

            // adds specific js templates for the czr_module control
            add_action( 'customize_controls_print_footer_scripts', array( $this, 'ac_print_module_control_templates' ) , 1 );
        }


        // hook : 'customize_controls_enqueue_scripts'
        function ac_load_additional_controls_js() {
            // Enqueue scripts/styles for the color picker.
            // Probably already enqueued by the theme controls, but let's make sure they are.
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_style( 'wp-color-picker' );

            //'czr-customizer-fmk' will be enqueued as a dependency of 'font-customizer-control' only in plugin mode
            wp_enqueue_script(
                'czr-customizer-fmk',
                //dev / debug mode mode?
                sprintf(
                    '%1$s/assets/js/%2$s',
                    NIMBLE_FMK_BASE_URL,
                    defined('CZR_DEV') && true === CZR_DEV ? '_0_ccat_czr-base-fmk.js' : '_0_ccat_czr-base-fmk.min.js'
                ),
                array('customize-controls' , 'jquery', 'underscore'),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $in_footer = true
            );

            // When used with Customizr or Hueman, free and pro, we also need to load the theme js part
            if ( false !== strpos( czr_get_parent_theme_slug(), 'customizr' ) || false !== strpos( czr_get_parent_theme_slug(), 'hueman' ) ) {
                wp_enqueue_script(
                    'czr-theme-customizer-fmk',
                    //dev / debug mode mode?
                    sprintf(
                        '%1$s/assets/js/%2$s',
                        NIMBLE_FMK_BASE_URL,
                        defined('CZR_DEV') && true === CZR_DEV ? '_1_ccat_czr-theme-fmk.js' : '_1_ccat_czr-theme-fmk.min.js'
                    ),
                    array( 'czr-customizer-fmk' ),
                    ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                    $in_footer = true
                );
            }


            //additional localized param when standalone plugin mode
            wp_localize_script(
                'czr-customizer-fmk',
                'serverControlParams',
                apply_filters( 'czr_js_customizer_control_params' ,
                  array(
                      'css_attr' => $this -> czr_css_attr,
                      'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('CZR_DEV') && true === CZR_DEV ),
                      'docURL'          => esc_url('docs.presscustomizr.com/'),
                      'i18n' => array(
                            'edit' => __('Edit', 'nimble-builder'),
                            'close' => __('Close', 'nimble-builder'),
                            'notset' => __('Not set', 'nimble-builder'),
                            'successMessage' => __('Done !', 'nimble-builder'),

                            'readDocumentation' => __('Learn more about this in the documentation', 'nimble-builder'),
                            'Settings' => __('Settings', 'nimble-builder'),
                            'Options for' => __('Options for', 'nimble-builder'),

                            // img upload translation
                            'select_image'        => __( 'Select Image', 'nimble-builder' ),
                            'change_image'        => __( 'Change Image', 'nimble-builder' ),
                            'remove_image'        => __( 'Remove', 'nimble-builder' ),
                            'default_image'       => __( 'Default', 'nimble-builder'  ),
                            'placeholder_image'   => __( 'No image selected', 'nimble-builder' ),
                            'frame_title_image'   => __( 'Select Image', 'nimble-builder' ),
                            'frame_button_image'  => __( 'Choose Image', 'nimble-builder' ),
                            'isThemeSwitchOn' => true
                      ),
                      'paramsForDynamicRegistration' => apply_filters( 'czr_fmk_dynamic_setting_js_params', array() )
                  )
                )
            );
        }



        // Enqueue the fmk css when standalone plugin
        // hook : 'customize_controls_enqueue_scripts'
        function ac_load_additional_controls_css() {
            wp_enqueue_style(
                'czr-fmk-controls-style',
                sprintf('%1$s/assets/css/czr-ccat-control-base%2$s.css', NIMBLE_FMK_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                array( 'customize-controls' ),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $media = 'all'
            );

            //select2 stylesheet
            //overriden by some specific style in czr-control-base.css
            wp_enqueue_style(
                'select2-css',
                 sprintf('%1$s/assets/css/lib/select2.min.css', NIMBLE_FMK_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                array( 'customize-controls' ),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $media = 'all'
            );

            wp_enqueue_style(
                'font-awesome',
                sprintf('%1$s/assets/fonts/css/fontawesome-all.min.css', NIMBLE_FMK_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                array(),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $media = 'all'
            );
        }


        //hook : customize_preview_init
        function ac_customize_load_preview_js() {
            global $wp_version;

            wp_enqueue_script(
                'czr-customizer-preview' ,
                  sprintf(
                      '%1$s/assets/js/%2$s',
                      NIMBLE_FMK_BASE_URL,
                      defined('CZR_DEV') && true === CZR_DEV ? 'czr-preview-base.js' : 'czr-preview-base.min.js'
                  ),
                  array( 'customize-preview', 'underscore'),
                  ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                  true
            );

            //localizes
            wp_localize_script(
                  'czr-customizer-preview',
                  'serverPreviewParams',
                  apply_filters('czr_base_fmk_customizer_preview_params' ,
                      array(
                          'themeFolder'     => get_template_directory_uri(),
                          //patch for old wp versions which don't trigger preview-ready signal => since WP 4.1
                          'preview_ready_event_exists'   => version_compare( $wp_version, '4.1' , '>=' ),
                          'blogname' => get_bloginfo('name'),
                          'isRTL'    => is_rtl()
                      )
                  )
            );
        }

        // DO WE STILL NEED TO PRINT THIS TMPL ?
        /////////////////////////////////////////////////////
        /// WHEN EMBEDDED IN A CONTROL //////////////////////
        /////////////////////////////////////////////////////
        //add specific js templates for the czr_module control
        //this is usually called in the manager for "registered" controls that need to be rendered with js
        //for this control, we'll do it another way because we need several js templates
        //=> that's why this control has not been "registered" and js templates are printed with the following action
        function ac_print_module_control_templates() {
            //Render the control wrapper for the CRUD types modules
            ?>
              <?php //Render the control wrapper for the CRUD types modules ?>
              <script type="text/html" id="tmpl-customize-control-czr_module-content">
                <label for="{{ data.settings['default'] }}-button">

                  <# if ( data.label ) { #>
                    <span class="customize-control-title">{{ data.label }}</span>
                  <# } #>
                  <# if ( data.description ) { #>
                    <span class="description customize-control-description">{{{ data.description }}}</span>
                  <# } #>
                </label>
              </script>
            <?php
        }
    }//class
endif;

?><?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( ! class_exists( 'CZR_Fmk_Base_Ajax_Filter' ) ) :
    class CZR_Fmk_Base_Ajax_Filter extends CZR_Fmk_Base_Load_Resources {

        // fired in the constructor
        function czr_setup_ajax_tmpl() {
            // this dynamic filter is declared on wp_ajax_ac_get_template
            // It allows us to populate the server response with the relevant module html template
            // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
            add_filter( "ac_set_ajax_czr_tmpl___all_modules", array( $this, 'ac_get_all_modules_tmpl' ), 10, 3 );

            // fetch templates
            add_action( 'wp_ajax_ac_get_template', array( $this, 'ac_set_ajax_czr_tmpl' ) );

            // Set input content
            // @see ::ac_generate_czr_tmpl_from_map
            add_action( 'czr_set_input_tmpl_content', array( $this, 'ac_set_input_tmpl_content' ), 10, 3 );
        }

        // hook : 'wp_ajax_ac_get_template'
        function ac_set_ajax_czr_tmpl() {
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => unauthenticated' );
            }
            if ( ! current_user_can( 'edit_theme_options' ) ) {
              wp_send_json_error('ac_set_ajax_czr_tmpl => user_cant_edit_theme_options');
            }
            if ( ! current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => customize_not_allowed' );
            } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => bad_method' );
            }
            $action = 'save-customize_' . get_stylesheet();
            if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                  'code' => 'invalid_nonce',
                  'message' => __( 'ac_set_ajax_czr_tmpl => Security check failed.', 'nimble-builder' ),
                ) );
            }

            if ( ! isset( $_POST['module_type'] ) || empty( $_POST['module_type'] ) ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => missing module_type property in posted data' );
            }
            if ( ! isset( $_POST['tmpl'] ) || empty( $_POST['tmpl'] ) ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => missing tmpl property in posted data' );
            }
            $tmpl = $_POST['tmpl'];
            $module_type = $_POST['module_type'];
            $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl, $_POST );

            if ( empty( $html ) ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => module ' . $module_type . ' => template empty for requested tmpl : ' . $tmpl );
            } else {
                wp_send_json_success( apply_filters( 'tmpl_results', $html, $tmpl ) );
            }
        }


        // hook : ac_set_ajax_czr_tmpl___all_modules
        // this dynamic filter is declared on wp_ajax_ac_get_template
        // It allows us to populate the server response with the relevant module html template
        // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
        //
        // For all modules, there are 3 types of templates :
        // 1) the pre-item, rendered when adding an item
        // 2) the module meta options, or mod-opt
        // 3) the item input options
        function ac_get_all_modules_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
            $css_attr = $this -> czr_css_attr;
            if ( empty( $requested_tmpl ) ) {
                wp_send_json_error( 'ac_get_all_modules_tmpl => the requested tmpl is empty' );
            }

            ob_start();
            switch ( $requested_tmpl ) {
                case 'crud-module-part' :
                    ?>
                      <button class="<?php echo $css_attr['open_pre_add_btn']; ?>"><?php _e('Add New', 'nimble-builder'); ?> <span class="fas fa-plus-square"></span></button>
                      <div class="<?php echo $css_attr['pre_add_wrapper']; ?>">
                        <div class="<?php echo $css_attr['pre_add_success']; ?>"><p></p></div>
                        <div class="<?php echo $css_attr['pre_add_item_content']; ?>">

                          <span class="<?php echo $css_attr['cancel_pre_add_btn']; ?> button"><?php _e('Cancel', 'nimble-builder'); ?></span> <span class="<?php echo $css_attr['add_new_btn']; ?> button"><?php _e('Add it', 'nimble-builder'); ?></span>
                        </div>
                      </div>
                    <?php
                break;
                case 'rud-item-part' :
                    ?>
                      <div class="<?php echo $css_attr['item_header']; ?> czr-custom-model">
                        <div class="<?php echo $css_attr['item_title']; ?> <?php echo $css_attr['item_sort_handle']; ?>"><h4>{{ data.title }}</h4></div>
                        <div class="<?php echo $css_attr['item_btns']; ?>"><a title="<?php _e('Edit', 'nimble-builder'); ?>" href="javascript:void(0);" class="fas fa-pencil-alt <?php echo $css_attr['edit_view_btn']; ?>"></a>&nbsp;<a title="<?php _e('Remove', 'nimble-builder'); ?>" href="javascript:void(0);" class="fas fa-trash <?php echo $css_attr['display_alert_btn']; ?>"></a></div>
                        <div class="<?php echo $css_attr['remove_alert_wrapper']; ?>"></div>
                      </div>
                    <?php
                break;

                // only used in the Widget module for the Hueman theme
                // to prevent the removal of the theme's builtin widget zones
                case 'ru-item-part' :
                    ?>
                      <div class="<?php echo $css_attr['item_header']; ?> czr-custom-model">
                        <div class="<?php echo $css_attr['item_title']; ?> <?php echo $css_attr['item_sort_handle']; ?>"><h4>{{ data.title }}</h4></div>
                          <div class="<?php echo $css_attr['item_btns']; ?>"><a title="<?php _e('Edit', 'nimble-builder'); ?>" href="javascript:void(0);" class="fas fa-pencil-alt <?php echo $css_attr['edit_view_btn']; ?>"></a></div>
                        </div>
                      </div>
                    <?php
                break;

                case 'rud-item-alert-part' :
                    ?>
                      <p class="czr-item-removal-title"><?php _e('Are you sure you want to remove : <strong>{{ data.title }} ?</strong>', 'nimble-builder'); ?></p>
                      <span class="<?php echo $css_attr['remove_view_btn']; ?> button"><?php _e('Yes', 'nimble-builder'); ?></span> <span class="<?php echo $css_attr['cancel_alert_btn']; ?> button"><?php _e('No', 'nimble-builder'); ?></span>
                    <?php
                break;

                // this template is used in setupImageUploaderSaveAsId and setupImageUploaderSaveAsUrl
                case 'img-uploader' :
                    ?>
                      <?php // case when a regular attachement object is provided, fetched from an id with wp.media.attachment( id ) ?>
                      <# if ( ( data.attachment && data.attachment.id ) ) { #>
                        <div class="attachment-media-view attachment-media-view-{{ data.attachment.type }} {{ data.attachment.orientation }}">
                          <div class="thumbnail thumbnail-{{ data.attachment.type }}">
                            <# if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.medium ) { #>
                              <img class="attachment-thumb" src="{{ data.attachment.sizes.medium.url }}" draggable="false" alt="" />
                            <# } else if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.full ) { #>
                              <img class="attachment-thumb" src="{{ data.attachment.sizes.full.url }}" draggable="false" alt="" />
                            <# } #>
                          </div>
                          <div class="actions">
                            <# if ( data.canUpload ) { #>
                            <button type="button" class="button remove-button">{{ data.button_labels.remove }}</button>
                            <button type="button" class="button upload-button control-focus" id="{{ data.settings['default'] }}-button">{{ data.button_labels.change }}</button>
                            <div style="clear:both"></div>
                            <# } #>
                          </div>
                        </div>
                      <?php // case when an url is provided ?>
                      <# } else if ( ! _.isEmpty( data.fromUrl ) ) { #>
                        <div class="attachment-media-view">
                          <div class="thumbnail thumbnail-thumb">
                              <img class="attachment-thumb" src="{{ data.fromUrl }}" draggable="false" alt="" />
                          </div>
                          <div class="actions">
                            <# if ( data.canUpload ) { #>
                            <button type="button" class="button remove-button">{{ data.button_labels.remove }}</button>
                            <button type="button" class="button upload-button control-focus" id="{{ data.settings['default'] }}-button">{{ data.button_labels.change }}</button>
                            <div style="clear:both"></div>
                            <# } #>
                          </div>
                        </div>
                      <?php // case when neither attachement or url are provided => placeholder ?>
                      <# } else { #>
                        <div class="attachment-media-view">
                          <div class="placeholder">
                            {{ data.button_labels.placeholder }}
                          </div>
                          <div class="actions">
                            <# if ( data.canUpload ) { #>
                            <button type="button" class="button upload-button" id="{{ data.settings['default'] }}-button">{{ data.button_labels.select }}</button>
                            <# } #>
                            <div style="clear:both"></div>
                          </div>
                        </div>
                      <# } #>
                    <?php
                break;
            }//switch

            $html = ob_get_clean();
            if ( empty( $html ) ) {
                wp_send_json_error( 'ac_get_all_modules_tmpl => no template was found for tmpl => ' . $requested_tmpl );
            }

            return $html;//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }
    }//class
endif;

?><?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( ! class_exists( 'CZR_Fmk_Base_Tmpl_Builder' ) ) :
    class CZR_Fmk_Base_Tmpl_Builder extends CZR_Fmk_Base_Ajax_Filter {
        /*********************************************************
        ** TMPL BUILDER
        *********************************************************/
        // This is the standard method to be used in a module to generate the item input template
        // for pre-item, mod-opts and item-inputs
        // fired in self::ac_get_ajax_module_tmpl
        function ac_generate_czr_tmpl_from_map( $tmpl_map ) {
            $html = '';
            $default_input_entries = array(
                'input_type'  => 'text',
                'title'        => '',
                'default'  => '',

                'notice_before' => '',
                'notice_after' => '',
                'placeholder' => '',

                // typically used for the number and range inputs
                'step' => '',
                'min' => '',
                'max' => '',
                'orientation' => '',//vertical / horizontal
                'unit' => '',//% or px for example

                'transport' => '',//<= can be set as a data property of the input wrapper, and used when instanciating the input

                'input_template' => '',//<= a static html template can be provided to render the input, in this case it will be used in priority
                'tmpl_callback' => '',//<= a callback function to be used to print the entire input template, including the wrapper

                'width-100' => false,//<= to force a width of 100%
                'title_width' => '',//width-80
                'input_width' => '',//width-20

                'refresh-markup' => null,
                'refresh-stylesheet' => null,
                'refresh-fonts' => null,

                'sanitize_cb' => '',
                'validate_cb' => ''
            );
            foreach( $tmpl_map as $input_id => $input_data ) {
                if ( ! is_string( $input_id ) || empty( $input_id ) ) {
                    wp_send_json_error( 'ac_generate_czr_tmpl_from_map => wrong input id' );
                    break;
                }
                if ( ! is_array( $input_data ) ) {
                    wp_send_json_error( 'ac_generate_czr_tmpl_from_map => wrong var type for the input_data of input id : ' . $input_id );
                    break;
                }
                // check that we have no unknown entries in the provided input_data
                $maybe_diff = array_diff_key( $input_data, $default_input_entries );
                if ( ! empty( $maybe_diff ) ) {
                    error_log('<ac_generate_czr_tmpl_from_map>');
                    error_log( '=> at least one unknow entry in the input data for input id : ' . $input_id );
                    error_log( print_r( $maybe_diff, true ) );
                    error_log('</ac_generate_czr_tmpl_from_map>');
                    wp_send_json_error( 'ac_generate_czr_tmpl_from_map => at least one unknow entry in the input data for input id : ' . $input_id );
                    break;
                }

                // we're clear, let's go
                $input_data = wp_parse_args( $input_data, $default_input_entries );

                // Do we have a specific template provided ?
                if ( ! empty( $input_data[ 'tmpl_callback' ] ) && function_exists( $input_data[ 'tmpl_callback' ] ) ) {
                    $html .= call_user_func_array( $input_data[ 'tmpl_callback' ], array( $input_data ) );
                } else {
                    $html .= $this -> ac_get_default_input_tmpl( $input_id, $input_data );
                }

            }
            return $html;////will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }



        // Fired in ac_generate_czr_tmpl_from_map
        function ac_get_default_input_tmpl( $input_id, $input_data ) {
            if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
                 wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . $input_id );
            }
            $input_type = $input_data[ 'input_type' ];

            // some inputs have a width of 100% even if not specified in the input_data
            $is_width_100 = true === $input_data[ 'width-100' ];
            if ( in_array( $input_type, array( 'color', 'radio', 'textarea' ) ) ) {
                $is_width_100 = true;
            }

            $css_attr = $this -> czr_css_attr;

            ob_start();
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s %6$s %7$s %8$s>',
                $css_attr['sub_set_wrapper'],
                $is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                $input_type,
                ! empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : '',
                // introduced for Nimble
                // allows us to fine tune the ajax action on input change
                ! is_null( $input_data['refresh-markup'] ) ? 'data-refresh-markup="'. (int)$input_data['refresh-markup'] .'"' : '',
                ! is_null( $input_data['refresh-stylesheet'] ) ? 'data-refresh-stylesheet="'. (int)$input_data['refresh-stylesheet'] .'"' : '',
                ! is_null( $input_data['refresh-fonts'] ) ? 'data-refresh-fonts="'. (int)$input_data['refresh-fonts'] .'"' : ''
            );
            // no need to print a title for an hidden input
            if ( $input_type !== 'hidden' ) {
                printf( '<div class="customize-control-title %1$s">%2$s</div>', ! empty( $input_data['title_width'] ) ? $input_data['title_width'] : '', $input_data['title'] );
            }
            ?>
              <?php if ( ! empty( $input_data['notice_before'] ) ) : ?>
                  <span class="czr-notice"><?php echo $input_data['notice_before']; ?></span>
              <?php endif; ?>

            <?php printf( '<div class="czr-input %1$s">', ! empty( $input_data['input_width'] ) ? $input_data['input_width'] : '' ); ?>

            <?php
            if ( ! empty( $input_data['input_template'] ) && is_string( $input_data['input_template'] ) ) {
                echo $input_data['input_template'];
            } else {

                // THIS IS WHERE THE ACTUAL INPUT CONTENT IS SET
                do_action( 'czr_set_input_tmpl_content', $input_type, $input_id, $input_data );

            }
            ?>
              </div><?php // class="czr-input" ?>
              <?php if ( ! empty( $input_data['notice_after'] ) ) : ?>
                  <span class="czr-notice"><?php echo $input_data['notice_after']; ?></span>
              <?php endif; ?>
            </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
            <?php
            // </INPUT WRAPPER>

            $tmpl_html = apply_filters( "czr_set_input_tmpl___{$input_type}", ob_get_clean(), $input_id, $input_data );
            //error_log( print_r($tmpl_html, true ) );
            if ( empty( $tmpl_html ) ) {
                wp_send_json_error( 'ac_get_input_tmpl => no html returned for input ' . $input_id );
            }
            return $tmpl_html;
        }//ac_get_input_tmpl()



        // hook : ac_set_input_tmpl_content
        function ac_set_input_tmpl_content( $input_type, $input_id, $input_data ) {
            $css_attr = $this -> czr_css_attr;
            switch ( $input_type ) {
                /* ------------------------------------------------------------------------- *
                 *  HIDDEN
                /* ------------------------------------------------------------------------- */
                case 'hidden':
                  ?>
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value=""></input>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  SELECT
                /* ------------------------------------------------------------------------- */
                case 'select':
                  ?>
                    <select data-czrtype="<?php echo $input_id; ?>"></select>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  TEXT
                /* ------------------------------------------------------------------------- */
                case 'text' :
                  ?>
                    <input data-czrtype="<?php echo $input_id; ?>" type="text" value="" placeholder="<?php echo $input_data['placeholder']; ?>"></input>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  NUMBER
                /* ------------------------------------------------------------------------- */
                case 'number' :
                  ?>
                    <?php
                    printf( '<input data-czrtype="%4$s" type="number" %1$s %2$s %3$s value="{{ data[\'%4$s\'] }}" />',
                      ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                      ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                      ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : '',
                      $input_id
                    );
                    ?>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  COLOR
                /* ------------------------------------------------------------------------- */
                case 'wp_color_alpha' :
                  ?>
                    <input data-czrtype="<?php echo $input_id; ?>" class="width-100"  data-alpha="true" type="text" value="{{ data['<?php echo $input_id; ?>'] }}"></input>
                  <?php
                break;
                case 'color' :
                  ?>
                    <input data-czrtype="<?php echo $input_id; ?>" type="text" value="{{ data['<?php echo $input_id; ?>'] }}"></input>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  CHECK
                /* ------------------------------------------------------------------------- */
                case 'check' :
                  ?>
                    <#
                      var _checked = ( false != data['<?php echo $input_id; ?>'] ) ? "checked=checked" : '';
                    #>
                    <input data-czrtype="<?php echo $input_id; ?>" type="checkbox" {{ _checked }}></input>
                  <?php
                break;

                case 'gutencheck' :
                    ?>
                      <#
                        var _checked = ( false != data['<?php echo $input_id; ?>'] ) ? "checked=checked" : '';
                      #>
                      <span class="czr-toggle-check"><input class="czr-toggle-check__input" id="pending-toggle-0" data-czrtype="<?php echo $input_id; ?>" type="checkbox" {{ _checked }}><span class="czr-toggle-check__track"></span><span class="czr-toggle-check__thumb"></span></span>
                    <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  TEXTAREA
                /* ------------------------------------------------------------------------- */
                case 'textarea' :
                  ?>
                    <textarea data-czrtype="<?php echo $input_id; ?>" class="width-100" name="textarea" rows="10" cols="">{{ data.value }}</textarea>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  IMG UPLOAD AND UPLOAD URL
                /* ------------------------------------------------------------------------- */
                case 'upload' :
                case 'upload_url' :
                  ?>
                    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
                    <div class="<?php echo $css_attr['img_upload_container']; ?>"></div>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  TINY MCE EDITOR
                /* ------------------------------------------------------------------------- */
                case 'tiny_mce_editor' :
                    ?>
                      <# //console.log( 'IN php::ac_get_default_input_tmpl() => data sent to the tmpl => ', data ); #>
                      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="open-tinymce-editor"><?php _e('Edit', 'nimble-builder' ); ?></button>&nbsp;
                      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="close-tinymce-editor"><?php _e('Close', 'nimble-builder' ); ?></button>
                      <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value="{{ data.value }}"/>
                    <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  RANGE
                /* ------------------------------------------------------------------------- */
                case 'range_slider' :
                  ?>
                    <# //console.log( 'IN php::ac_get_default_input_tmpl() => data range_slide => ', data ); #>
                    <?php
                    printf( '<input data-czrtype="%5$s" type="range" %1$s %2$s %3$s %4$s value="{{ data[\'%5$s\'] }}" />',
                      ! empty( $input_data['orientation'] ) ? 'data-orientation="'. $input_data['orientation'] .'"' : '',
                      ! empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : '',
                      ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                      ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : '',
                      $input_id
                    );
                    ?>
                  <?php
                break;

                /* ------------------------------------------------------------------------- *
                 *  CONTENT PICKER
                /* ------------------------------------------------------------------------- */
                case 'content_picker' :
                  ?>
                    <?php
                    printf( '<span data-czrtype="%1$s"></span>', $input_id );
                    ?>
                  <?php
                break;
            }//switch
        }

    }//class
endif;

?><?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( ! class_exists( 'CZR_Fmk_Dyn_Setting_Registration' ) ) :
    class CZR_Fmk_Dyn_Setting_Registration extends CZR_Fmk_Base_Tmpl_Builder {

        //fired in the constructor
        function czr_setup_dynamic_settings_registration() {
            add_action( 'customize_register', array( $this, 'czr_setup_dynamic_setting_registration' ), 10 );

            // if we have dynamic setting params, let's add a filter to serverControlParams
            // filter declared when localizing 'serverControlParams' @see resources
            add_filter( 'czr_fmk_dynamic_setting_js_params', array( $this, 'czr_setup_localized_params_for_dynamic_js_registration' ), 20 );

            // when not dynamically registered
            // TO DEPRECATE ?
            add_action( 'customize_register', array( $this, 'czr_register_not_dynamic_settings' ), 20 );
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
            if ( ! is_array( $setting_params ) || empty( $setting_params ) ) {
                error_log( 'czr_pre_register_dynamic_setting => empty $setting_params submitted' );
                return;
            }
            if ( ! array_key_exists( 'setting_id', $setting_params ) || empty( $setting_params['setting_id'] ) ) {
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
            if ( ! is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
              return $setting_args;

            // let's initialize the args to the provided param
            $registered_setting_args = $setting_args;

            // loop on each registered modules
            foreach ( $this->registered_settings as $registerered_setting_id => $params ) {
                $params = wp_parse_args( $params, $this -> default_dynamic_setting_params );
                if ( true !== $params['dynamic_registration'] ) {
                  continue;
                }
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
                    'sanitize_callback'    => ( ! empty( $setting_args[ 'sanitize_callback' ] ) && function_exists( $setting_args[ 'sanitize_callback' ] ) ) ? $setting_args[ 'sanitize_callback' ] : '',
                    'validate_callback'    => ( ! empty( $setting_args[ 'validate_callback' ] ) && function_exists( $setting_args[ 'validate_callback' ] ) ) ? $setting_args[ 'validate_callback' ] : ''
                );

                // if this is a module setting, it can have specific sanitize and validate callback set for the module
                // Let's check if the module_type is registered, and if there are any callback set.
                // If a match is found, we'll use those callback
                $module = $this -> czr_get_registered_dynamic_module( $params[ 'module_type' ] );
                if ( false !== $module  && is_array( $module ) ) {
                    if ( array_key_exists( 'validate_callback', $module ) && function_exists( $module[ 'validate_callback' ] ) ) {
                        $registered_setting_args[ 'validate_callback' ] = $module[ 'validate_callback' ];
                    }
                    if ( array_key_exists( 'sanitize_callback', $module ) && function_exists( $module[ 'sanitize_callback' ] ) ) {
                        $registered_setting_args[ 'sanitize_callback' ] = $module[ 'sanitize_callback' ];
                    }
                }
                //error_log( 'REGISTERING DYNAMICALLY for setting =>'. $setting_id );
            }
            return $registered_setting_args;
        }


        // hook : 'customize_dynamic_setting_class'
        function czr_setup_customizer_dynamic_setting_class( $class, $setting_id, $args ) {
            if ( ! is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
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
                        if ( ! class_exists( $setting_args[ 'setting_class' ][ 'name' ] ) && file_exists( $setting_args[ 'setting_class' ]['path'] ) ) {
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
            if ( ! is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
              return $js_params;
            $js_params = ! is_array( $js_params ) ? array() : $js_params;

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
                // The dynamic registration should be explicitely set
                if ( true !== $params['dynamic_registration'] )
                  continue;
                // We need the 'option_value' entry, even if empty
                if ( ! array_key_exists( 'option_value', $params ) || ! is_array( $params['option_value'] ) )
                  continue;
                // Check if not already setup
                if ( array_key_exists( $registerered_setting_id, $params ) ) {
                    error_log( 'czr_setup_localized_params_for_dynamic_js_registration => js_params already setup for setting : ' . $registerered_setting_id );
                }

                $js_params[ $registerered_setting_id ] = array(
                    'setting_id' => $registerered_setting_id,
                    'module_type' => $params[ 'module_type' ],
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

            if ( ! is_array( $this->registered_settings ) || empty( $this->registered_settings ) )
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
                        if ( ! class_exists( $setting_args[ 'setting_class' ][ 'name' ] ) && file_exists( $setting_args[ 'setting_class' ]['path'] ) ) {
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
                        if ( ! class_exists( $control_args[ 'control_class' ][ 'name' ] ) && file_exists( $control_args[ 'control_class' ]['path'] ) ) {
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

?><?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( ! class_exists( 'CZR_Fmk_Base' ) ) :
    class CZR_Fmk_Base extends CZR_Fmk_Dyn_Setting_Registration {

        //fired in the constructor
        function czr_setup_dynamic_modules_registration() {
            // Dynamic Module Registration
            add_action( 'init', array( $this, 'czr_schedule_ajax_tmpl' ) );
            // Enqueue the module assets
            add_action( 'customize_controls_enqueue_scripts' , array( $this, 'czr_register_dynamic_modules_assets' ) );
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
            if ( ! is_array( $module_params ) || empty( $module_params ) ) {
                error_log( 'czr_pre_register_dynamic_module => empty $module_params submitted' );
                return;
            }
            if ( ! array_key_exists( 'module_type', $module_params ) || empty( $module_params['module_type'] ) ) {
                error_log( 'czr_pre_register_dynamic_module => missing module_type' );
                return;
            }

            // normalize
            $module_params = wp_parse_args( $module_params, $this -> default_dynamic_module_params );

            $registered = $this->registered_modules;
            $module_type_candidate = $module_params['module_type'];

            // A module type can be registered only once.
            // Already registered ?
            if ( array_key_exists( $module_type_candidate, $registered ) ) {
                error_log( 'czr_pre_register_dynamic_module => module type already registered => ' . $module_type_candidate );
                return;
            }
            $registered[ $module_type_candidate ] = $module_params;
            $this->registered_modules = $registered;
        }



        // HELPER
        // @return boolean or array of module params
        function czr_get_registered_dynamic_module( $module_type = '' ) {
            $registered = $this->registered_modules;
            if ( empty( $module_type ) || ! is_array( $registered ) || empty( $registered ) )
              return;
            return array_key_exists( $module_type , $registered ) ? $registered[ $module_type ] : false;
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
        function czr_register_dynamic_modules_assets() {
            if ( ! is_array( $this->registered_modules ) || empty( $this->registered_modules ) )
              return;

            // loop on each registered modules
            foreach ( $this->registered_modules as $module_type => $params ) {
                $params = wp_parse_args( $params, $this -> default_dynamic_module_params );
                //error_log( print_r( $params, true ) );
                $control_js_params = $params[ 'customizer_assets' ][ 'control_js' ];
                // Enqueue the list of registered scripts
                if ( ! empty( $control_js_params ) ) {
                    foreach ( $control_js_params as $handle => $script_args ) {
                        wp_enqueue_script(
                            $handle,
                            array_key_exists( 'src', $script_args ) ? $script_args['src'] : null,
                            array_key_exists( 'deps', $script_args ) ? $script_args['deps'] : null,
                            array_key_exists( 'ver', $script_args ) ? $script_args['ver'] : null,
                            array_key_exists( 'in_footer', $script_args ) ? $script_args['in_footer'] : false
                        );
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

                    if ( is_array( $localized_control_js_params ) && ! empty( $localized_control_js_params ) ) {
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
            if ( ! is_array( $this->registered_modules ) || empty( $this->registered_modules ) )
              return;

            foreach ( $this->registered_modules as $module_type => $params ) {
                $params = wp_parse_args( $params, $this -> default_dynamic_module_params );
                if ( ! empty( $params['tmpl'] ) ) {
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
            if ( ! is_array( $posted_params ) || empty( $posted_params ) ) {
                wp_send_json_error( 'ac_get_ajax_module_tmpl => empty posted_params' );
            }
            if ( ! array_key_exists( 'module_type', $posted_params  ) || empty( $posted_params['module_type'] ) ) {
                wp_send_json_error( 'ac_get_ajax_module_tmpl => missing module_type' );
            }
            // if ( ! array_key_exists( 'control_id', $posted_params  ) || empty( $posted_params['control_id'] ) ) {
            //    wp_send_json_error( 'ac_get_ajax_module_tmpl => missing control_id' );
            // }

            // find the requested module_id in the list of registered modules
            $registered_modules = $this->registered_modules;
            $module_type = $posted_params['module_type'];
            if ( ! array_key_exists( $module_type, $registered_modules  ) || empty( $registered_modules[ $module_type ] ) ) {
                return;
            }

            $module_params = $registered_modules[ $module_type ];
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
              //     'title' => __('Spacing', 'text_domain_to_be_replaced'),
              //     'inputs' => array(
              //         'padding' => array(
              //             'input_type'  => 'number',
              //             'title'       => __('Padding', 'text_domain_to_be_replaced')
              //         ),
              //         'margin' => array(
              //             'input_type'  => 'number',
              //             'title'       => __('Margin', 'text_domain_to_be_replaced')
              //         )
              //     )
              // ),
              // array( ... )
              //
              //
              // Without tabs :
              //  'padding' => array(
              //       'input_type'  => 'number',
              //       'title'       => __('Padding', 'text_domain_to_be_replaced')
              //  ),
              //   'margin' => array(
              //      'input_type'  => 'number',
              //      'title'       => __('Margin', 'text_domain_to_be_replaced')
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
                              $_key + 1,
                              array_key_exists('attributes', $tab) ? $tab['attributes'] : '',
                              $tab['title']
                          );
                        }//foreach
                      ?>
                    </ul>
                  </nav>
                  <div class="content-wrap">
                    <?php
                      foreach ( $tmpl_map['tabs'] as $_key => $tab ) {
                        printf( '<section id="section-topline-%1$s">%2$s</section>',
                            $_key + 1,
                            $this -> ac_generate_czr_tmpl_from_map( $tab['inputs'] )
                        );
                      }//foreach
                    ?>
                  </div><?php //.content-wrap ?>
                </div><?php //.tabs ?>
                <?php
                return ob_get_clean();
            } else {
                return $this -> ac_generate_czr_tmpl_from_map( $tmpl_map );
            }
        }

    }//class
endif;

?><?php
/**
* @uses  wp_get_theme() the optional stylesheet parameter value takes into account the possible preview of a theme different than the one activated
*/
function czr_get_parent_theme_slug() {
    $theme_slug = get_option( 'stylesheet' );
    // $_REQUEST['theme'] is set both in live preview and when we're customizing a non active theme
    $theme_slug = isset($_REQUEST['theme']) ? $_REQUEST['theme'] : $theme_slug; //old wp versions
    $theme_slug = isset($_REQUEST['customize_theme']) ? $_REQUEST['customize_theme'] : $theme_slug;

    //gets the theme name (or parent if child)
    $theme_data = wp_get_theme( $theme_slug );
    if ( $theme_data -> parent() ) {
        $theme_slug = $theme_data -> parent() -> Name;
    }

    return sanitize_file_name( strtolower( $theme_slug ) );
}

//Creates a new instance
//@params ex :
//array(
//    'base_url' => NIMBLE_BASE_URL . '/inc/czr-base-fmk'
// )
function CZR_Fmk_Base( $params = array() ) {
    return CZR_Fmk_Base::czr_fmk_get_instance( $params );
}

?>