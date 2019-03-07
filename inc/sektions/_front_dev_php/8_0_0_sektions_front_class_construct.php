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
            'global_header_footer' => 'sek_global_header_footer',
            'breakpoint' => 'sek_global_breakpoint',
            'widths' => 'sek_global_widths',
            'performances' => 'sek_global_performances',
            'recaptcha' => 'sek_global_recaptcha',
            'global_revisions' => 'sek_global_revisions',
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
            'local_revisions' => 'sek_local_revisions'
        ];

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
        public function sek_nimble_widgets_init() {
            // Header and footer have been introduced in v1.4.0 but not enabled by default
            // The Nimble widget areas registration is on hold until "header and footer" feature is released.
            if ( ! sek_is_header_footer_enabled() )
              return;
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