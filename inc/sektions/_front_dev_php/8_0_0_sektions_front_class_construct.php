<?php
////////////////////////////////////////////////////////////////
// SEK Front Class
if ( ! class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $local_seks = 'not_cached';// <= used to cache the sektions for the local skope_id
        public $global_seks = 'not_cached';// <= used to cache the sektions for the global skope_id
        public $model = array();//<= when rendering, the current level model
        public $parent_model = array();//<= when rendering, the current parent model
        public $default_models = array();// <= will be populated to cache the default models when invoking sek_get_default_module_model
        public $cached_input_lists = array(); // <= will be populated to cache the input_list of each registered module. Useful when we need to get info like css_selector for a particular input type or id.
        public $ajax_action_map = array();
        public $default_locations = [
            'loop_start' => array( 'priority' => 10 ),
            'before_content' => array(),
            'after_content' => array(),
            'loop_end' => array( 'priority' => 10 ),
        ];
        public $registered_locations = [];
        // the model used to register a location
        public $default_registered_location_model = [
          'priority' => 10,
          'is_global_location' => false,
          'is_header_location' => false,
          'is_footer_location' => false
        ];
        // the model used when saving a location in db
        public $default_location_model = [
            'id' => '',
            'level' => 'location',
            'collection' => [],
            'options' => [],
            'ver_ini' => NIMBLE_VERSION
        ];
        public $rendered_levels = [];//<= stores the ids of the level rendered with ::render()

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sek_Nimble_Manager ) ) {
                self::$instance = new Sek_Nimble_Manager( $params );

                // this hook is used to add_action( 'nimble_front_classes_ready', array( $this, 'sek_register_nimble_global_locations') );
                do_action( 'nimble_front_classes_ready', self::$instance );
            }
            return self::$instance;
        }

        // store the local and global options
        public $local_options = '_not_cached_yet_';
        public $global_nimble_options = '_not_cached_yet_';

        public $img_smartload_enabled = 'not_cached';

        public $has_local_header_footer = '_not_cached_yet_';//used in sek_maybe_set_local_nimble_header() and sek_maybe_set_local_nimble_footer()
        public $has_global_header_footer = '_not_cached_yet_';//used in sek_maybe_set_local_nimble_header() and sek_maybe_set_local_nimble_footer()

        public $recaptcha_enabled = '_not_cached_yet_';//enabled in the global options
        public $recaptcha_badge_displayed = '_not_cached_yet_';//enabled in the global options

        // option key as saved in db => module_type
        // is used in _1_6_5_sektions_generate_UI_global_options.js and when normalizing the global option in sek_normalize_global_options_with_defaults()
        public static $global_options_map = [
            'global_text' => 'sek_global_text',
            'widths' => 'sek_global_widths',
            'breakpoint' => 'sek_global_breakpoint',
            'global_header_footer' => 'sek_global_header_footer',
            'performances' => 'sek_global_performances',
            'recaptcha' => 'sek_global_recaptcha',
            'global_revisions' => 'sek_global_revisions',
            'global_reset' => 'sek_global_reset',
            'beta_features' => 'sek_global_beta_features'
        ];
        // option key as saved in db => module_type
        // is used in _1_6_4_sektions_generate_UI_local_skope_options.js and when normalizing the global option in sek_normalize_global_options_with_defaults()
        public static $local_options_map = [
            'template' => 'sek_local_template',
            'local_header_footer' => 'sek_local_header_footer',
            'widths' => 'sek_local_widths',
            'custom_css' => 'sek_local_custom_css',
            'local_performances' => 'sek_local_performances',
            'local_reset' => 'sek_local_reset',
            'import_export' => 'sek_local_imp_exp',
            'local_revisions' => 'sek_local_revisions'
        ];
        // introduced when implementing import/export feature
        // @see https://github.com/presscustomizr/nimble-builder/issues/411
        public $img_import_errors = [];

        // stores the active module collection
        // @see populated in sek_get_contextually_active_module_list()
        public $contextually_active_modules = [];

        public static $ui_picker_modules = [
          // UI CONTENT PICKER
          'sek_content_type_switcher_module',
          'sek_module_picker_module'
        ];

        public static $ui_level_modules = [
          // UI LEVEL MODULES
          'sek_mod_option_switcher_module',
          'sek_level_bg_module',
          'sek_level_border_module',
          //'sek_level_section_layout_module',<// deactivated for now. Replaced by sek_level_width_section
          'sek_level_height_module',
          'sek_level_spacing_module',
          'sek_level_width_module',
          'sek_level_width_column',
          'sek_level_width_section',
          'sek_level_anchor_module',
          'sek_level_visibility_module',
          'sek_level_breakpoint_module'
        ];

        public static $ui_local_global_options_modules = [
          // local skope options modules
          'sek_local_template',
          'sek_local_widths',
          'sek_local_custom_css',
          'sek_local_reset',
          'sek_local_performances',
          'sek_local_header_footer',
          'sek_local_revisions',
          'sek_local_imp_exp',

          // global options modules
          'sek_global_text',
          'sek_global_widths',
          'sek_global_breakpoint',
          'sek_global_header_footer',
          'sek_global_performances',
          'sek_global_recaptcha',
          'sek_global_revisions',
          'sek_global_reset',
          'sek_global_beta_features'
        ];

        public static $ui_front_modules = [
          // FRONT MODULES
          'czr_simple_html_module',

          'czr_tiny_mce_editor_module' => array(
            'czr_tiny_mce_editor_module',
            'czr_tinymce_child',
            'czr_font_child'
          ),

          'czr_image_module' => array(
            'czr_image_module',
            'czr_image_main_settings_child',
            'czr_image_borders_corners_child'
          ),

          //'czr_featured_pages_module',
          'czr_heading_module'  => array(
            'czr_heading_module',
            'czr_heading_child',
            'czr_heading_spacing_child',
            'czr_font_child'
          ),

          'czr_spacer_module',
          'czr_divider_module',

          'czr_icon_module' => array(
            'czr_icon_module',
            'czr_icon_settings_child',
            'czr_icon_spacing_border_child',
          ),


          'czr_map_module',

          'czr_quote_module' => array(
            'czr_quote_module',
            'czr_quote_quote_child',
            'czr_quote_cite_child',
            'czr_quote_design_child',
          ),

          'czr_button_module' => array(
            'czr_button_module',
            'czr_btn_content_child',
            'czr_btn_design_child',
            'czr_font_child'
          ),

          // simple form father + children
          'czr_simple_form_module' => array(
            'czr_simple_form_module',
            'czr_simple_form_fields_child',
            'czr_simple_form_button_child',
            'czr_simple_form_design_child',
            'czr_simple_form_fonts_child',
            'czr_simple_form_submission_child'
          ),

          'czr_post_grid_module' => array(
            'czr_post_grid_module',
            'czr_post_grid_main_child',
            'czr_post_grid_thumb_child',
            'czr_post_grid_metas_child',
            'czr_post_grid_fonts_child'
          ),

          // widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
          'czr_menu_module' => array(
            'czr_menu_module',
            'czr_menu_content_child',
            'czr_menu_mobile_options',
            'czr_font_child'
          ),
          //'czr_menu_design_child',

          'czr_widget_area_module',

          'czr_social_icons_module' => array(
            'czr_social_icons_module',
            'czr_social_icons_settings_child',
            'czr_social_icons_style_child'
          ),

          'czr_img_slider_module' => array(
            'czr_img_slider_module',
            'czr_img_slider_collection_child',
            'czr_img_slider_opts_child'
          ),
        ];

        // Is merged with front module when sek_is_header_footer_enabled() === true
        // @see sek_register_modules_when_customizing_or_ajaxing
        // and sek_register_modules_when_not_customizing_and_not_ajaxing
        public static $ui_front_beta_modules = [];

        // introduced for https://github.com/presscustomizr/nimble-builder/issues/456
        public $global_sections_rendered = false;

        /////////////////////////////////////////////////////////////////
        // <CONSTRUCTOR>
        function __construct( $params = array() ) {
            // INITIALIZE THE REGISTERED LOCATIONS WITH THE DEFAULT LOCATIONS
            $this->registered_locations = $this->default_locations;

            // AJAX
            $this -> _schedule_front_ajax_actions();
            $this -> _schedule_img_import_ajax_actions();
            if ( defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) && NIMBLE_SAVED_SECTIONS_ENABLED ) {
                $this -> _schedule_section_saving_ajax_actions();
            }
            // ASSETS
            $this -> _schedule_front_and_preview_assets_printing();
            // RENDERING
            $this -> _schedule_front_rendering();
            // RENDERING
            $this -> _setup_hook_for_front_css_printing_or_enqueuing();
            // LOADS SIMPLE FORM
            $this -> _setup_simple_forms();
            // REGISTER NIMBLE WIDGET ZONES
            add_action( 'widgets_init', array( $this, 'sek_nimble_widgets_init' ) );
        }//__construct

        // @fired @hook 'widgets_init'
        // Creates 10 widget zones
        public function sek_nimble_widgets_init() {
            // Header/footer, widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
            $defaults = array(
                'name'          => '',
                'id'            => '',
                'description'   => '',
                'class'         => '',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget'  => '</aside>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            );
            for ( $i=1; $i < 11; $i++ ) {
                $args['id'] = NIMBLE_WIDGET_PREFIX . $i;//'nimble-widget-area-'
                $args['name'] = sprintf( __('Nimble widget area #%1$s', 'text_domain_to_replace' ), $i );
                $args['description'] = $args['name'];
                $args = wp_parse_args( $args, $defaults );
                register_sidebar( $args );
            }
        }
    }//class
endif;
?>