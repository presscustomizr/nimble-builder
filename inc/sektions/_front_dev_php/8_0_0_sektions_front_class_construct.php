<?php
////////////////////////////////////////////////////////////////
// SEK Front Class
if ( ! class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $local_seks = 'not_cached';// <= used to cache the sektions for the local skope_id
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
        public $default_location_model = [
            'id' => '',
            'level' => 'location',
            'collection' => [],
            'options' => [],
            'ver_ini' => NIMBLE_VERSION
        ];

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sek_Simple_Form ) )
              self::$instance = new Sek_Simple_Form( $params );
            return self::$instance;
        }
        public $img_smartload_enabled = 'not_cached';

        /////////////////////////////////////////////////////////////////
        // <CONSTRUCTOR>
        function __construct( $params = array() ) {
            // AJAX
            $this -> _schedule_front_ajax_actions();
            $this -> _schedule_img_import_ajax_actions();
            $this -> _schedule_section_saving_ajax_actions();
            // ASSETS
            $this -> _schedule_front_and_preview_assets_printing();
            // RENDERING
            $this -> _schedule_front_rendering();
            // RENDERING
            $this -> _setup_hook_for_front_css_printing_or_enqueuing();
            // LOADS SIMPLE FORM
            $this -> _setup_simple_forms();
        }//__construct
    }//class
endif;
?>