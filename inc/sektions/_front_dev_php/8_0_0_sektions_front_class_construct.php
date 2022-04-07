<?php
////////////////////////////////////////////////////////////////
// SEK Front Class
if ( !class_exists( 'SEK_Front_Construct' ) ) :
    class SEK_Front_Construct {
        static $instance;
        public $seks_posts = [];// <= march 2020 : used to cache the current local and global sektion posts
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
            if ( !isset( self::$instance ) && !( self::$instance instanceof Sek_Nimble_Manager ) ) {
                self::$instance = new Sek_Nimble_Manager( $params );

                // this hook is used to add_action( 'nimble_front_classes_ready', array( $this, 'sek_register_nimble_global_locations') );
                do_action( 'nimble_front_classes_ready', self::$instance );
            }
            return self::$instance;
        }

        // store the local and global options
        public $local_options = '_not_cached_yet_';
        public $local_options_without_tmpl_inheritance = '_not_cached_yet_';//Introduced for site templates, when using function sek_is_inheritance_locally_disabled()
        public $global_nimble_options = '_not_cached_yet_';

        public $img_smartload_enabled = 'not_cached';
        public $video_bg_lazyload_enabled = 'not_cached';//<= for https://github.com/presscustomizr/nimble-builder/issues/287

        public $has_local_header_footer = '_not_cached_yet_';//used in sek_maybe_set_local_nimble_header() and sek_maybe_set_local_nimble_footer()
        public $has_global_header_footer = '_not_cached_yet_';//used in sek_maybe_set_local_nimble_header() and sek_maybe_set_local_nimble_footer()

        public $recaptcha_enabled = '_not_cached_yet_';//enabled in the global options
        public $recaptcha_badge_displayed = '_not_cached_yet_';//enabled in the global options

        // option key as saved in db => module_type
        // is used in _1_6_5_sektions_generate_UI_global_options.js and when normalizing the global option in sek_normalize_global_options_with_defaults()
        public static $global_options_map = [
            'global_header_footer' => 'sek_global_header_footer',
            'global_text' => 'sek_global_text',
            'site_templates' => 'sek_site_tmpl_pickers',
            'widths' => 'sek_global_widths',
            'breakpoint' => 'sek_global_breakpoint',
            'performances' => 'sek_global_performances',
            'recaptcha' => 'sek_global_recaptcha',
            'global_revisions' => 'sek_global_revisions',
            'global_reset' => 'sek_global_reset',
            'global_imp_exp' => 'sek_global_imp_exp',
            'beta_features' => 'sek_global_beta_features'// may 2021 not rendered anymore  in ::controls customizer
        ];
        // option key as saved in db => module_type
        // is used in _1_6_4_sektions_generate_UI_local_skope_options.js and when normalizing the global option in sek_normalize_local_options_with_defaults()
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
        // @see populated in sek_populate_collection_of_contextually_active_modules()
        // list of modules displayed on local + global sektions for a givent page.
        // populated 'wp'@PHP_INT_MAX and used to
        // 1) determine which module should be registered when not customizing or ajaxing. See sek_register_modules_when_not_customizing_and_not_ajaxing()
        // 2) determine which assets ( css / js ) is needed for this context. see ::sek_enqueue_front_assets
        //
        // updated for https://github.com/presscustomizr/nimble-builder/issues/612
        public $contextually_active_modules = 'not_set';

        public static $ui_picker_modules = [
          // UI CONTENT PICKER
          'sek_content_type_switcher_module',
          'sek_module_picker_module'
        ];

        // JUNE 2020
        // PREBUILT AND USER SECTION MODULES ARE REGISTERED IN add_action( 'after_setup_theme', '\Nimble\sek_schedule_module_registration', 50 );
        // with sek_register_prebuilt_section_modules(); and sek_register_user_sections_module();

        public static $ui_level_modules = [
          // UI LEVEL MODULES
          'sek_mod_option_switcher_module',
          'sek_level_bg_module',
          'sek_level_text_module',
          'sek_level_border_module',
          //'sek_level_section_layout_module',<// deactivated for now. Replaced by sek_level_width_section
          'sek_level_height_module',
          'sek_level_spacing_module',
          'sek_level_spacing_module_for_columns',
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
          'sek_global_imp_exp',
          'sek_global_beta_features',

          // site template options module
          'sek_site_tmpl_pickers'
        ];

        // Is merged with front module when sek_is_header_footer_enabled() === true
        // @see sek_register_modules_when_customizing_or_ajaxing
        // and sek_register_modules_when_not_customizing_and_not_ajaxing
        public static $ui_front_beta_modules = [];

        // introduced for https://github.com/presscustomizr/nimble-builder/issues/456
        public $global_sections_rendered = false;

        // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
        // september 2019
        // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
        // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
        // @see ::render() method
        // otherwise the preview UI can be broken
        public $preview_level_guid = '_preview_level_guid_not_set_';

        // March 2020 : introduction of individual stylesheet for some modules
        // October 2020 : implementation of dynamic stylesheet concatenation when generating stylesheets
        public $big_module_stylesheet_map = [
            'czr_quote_module' => 'quote-module',
            'czr_icon_module' => 'icon-module',
            'czr_img_slider_module' => 'img-slider-module',
            'czr_accordion_module' => 'accordion-module',
            'czr_menu_module' => 'menu-module',
            'czr_post_grid_module' => 'post-grid-module',
            'czr_simple_form_module' => 'simple-form-module',
            'czr_image_module' => 'image-module',

            'czr_special_img_module' => 'special-image-module',
            'czr_advanced_list_module' => 'advanced-list-module',

            'czr_social_icons_module' => 'social-icons-module',
            'czr_button_module' => 'button-module',
            'czr_heading_module' => 'heading-module',
            'czr_gallery_module' => 'gallery-module',
        ];

        // March 2020, for https://github.com/presscustomizr/nimble-builder/issues/629
        public $google_fonts_print_candidates = 'not_set';// will cache the google font candidates to print in ::_setup_hook_for_front_css_printing_or_enqueuing()

        public $css_loader_html = '<div class="sek-css-loader sek-mr-loader"><div></div><div></div><div></div></div>';

        // March 2020, for https://github.com/presscustomizr/nimble-builder/issues/649
        public $nimble_customizing_or_content_is_printed_on_this_page = false;//<= tells if any Nimble Content has been printed.
        // October 2020
        public $page_has_local_or_global_sections = 'not_set';//<= set @wp_enqueue_script, used to determine if we should load css, js and fonts assets or not.
        // feb 2021, introduced for #478
        public $page_has_local_sections = 'not_set';
        public $page_has_global_sections = 'not_set';

        // April 2020 for https://github.com/presscustomizr/nimble-builder/issues/679
        public $is_content_restricted = false; //<= set at 'wp'

        // May 2020
        // those location properties are set when walking Nimble content on rendering
        // @see #705 prevent lazyloading images when in header section.
        public $current_location_is_header = false;
        public $current_location_is_footer = false;

        // September 2020 for https://github.com/presscustomizr/nimble-builder-pro/issues/67
        public $local_levels_custom_css = '';
        public $global_levels_custom_css = '';

        // October 2020
        public $rendering = false;//<= set to true when rendering NB content

        // October 2020
        public $emitted_js_event = [];//<= collection of unique js event emitted with a script like nb_.emit('nb-needs-parallax')

        // October 2020, for https://github.com/presscustomizr/nimble-builder/issues/751
        public $partial_front_scripts = [
            'slider-module' => 'nb-needs-swiper',
            'menu-module' => 'nb-needs-menu-js',
            'front-parallax' => 'nb-needs-parallax',
            'accordion-module' => 'nb-needs-accordion'
        ];

        // janv 2021 => will populate the modules stylesheets already concatenated, so that NB doesn't concatenate a module stylesheet twice for the local css and for the global css (if any)
        // see in inc\sektions\_front_dev_php\dyn_css_builder_and_google_fonts_printer\5_0_1_class-sek-dyn-css-builder.php
        public $concatenated_module_stylesheets = [];

        // April 2021 => added some properties when implementing late escape for attributes
        // @see ::render() and base-tmpl PHP files
        public $level_css_classes;
        public $level_custom_anchor;
        public $level_custom_attr;

        /////////////////////////////////////////////////////////////////
        // <CONSTRUCTOR>
        function __construct( $params = array() ) {
            if ( did_action('nimble_manager_ready') )
              return;
            // INITIALIZE THE REGISTERED LOCATIONS WITH THE DEFAULT LOCATIONS
            $this->registered_locations = $this->default_locations;

            // AJAX
            $this->_schedule_front_ajax_actions();

            // FRONT ASSETS
            $this->_schedule_front_assets_printing();
            // CUSTOOMIZER PREVIEW ASSETS
            $this->_schedule_preview_assets_printing();
            // RENDERING
            $this->_schedule_front_rendering();
            // RENDERING
            $this->_setup_hook_for_front_css_printing_or_enqueuing();
            // LOADS SIMPLE FORM
            $this->_setup_simple_forms();
            // REGISTER NIMBLE WIDGET ZONES
            add_action( 'widgets_init', array( $this, 'sek_nimble_widgets_init' ) );
            do_action('nimble_manager_ready');

            // MAYBE REGISTER PRO UPSELL MODUlES
            add_filter('nb_level_module_collection', function( $module_collection ) {
                if ( is_array($module_collection) && ( sek_is_pro() || sek_is_upsell_enabled() ) ) {
                    array_push($module_collection, 'sek_level_cust_css_level' );
                    array_push($module_collection, 'sek_level_animation_module' );
                }
                return $module_collection;
            });

            // see #838
            // prevents using persistent cache object systems like Memcached which override the default WP class WP_Object_Cache () which is normally refreshed on each page load )
            add_action('init', array( $this, 'sek_clear_cached_objects_when_customizing') );

            // FLUSH CACHE OBJECT ON POST SAVE / UPDATE
            // for https://github.com/presscustomizr/nimble-builder/issues/867
            add_action( 'save_post', array( $this, 'sek_flush_object_cache_on_post_update') );
        }//__construct

        // @init
        public function sek_clear_cached_objects_when_customizing() {
            if ( skp_is_customizing() ) {
                // Make sure cached objects are cleaned
                wp_cache_flush();
            }
        }
        // @save_post
        function sek_flush_object_cache_on_post_update() {
          wp_cache_flush();
        }

        // @fired @hook 'widgets_init'
        // Creates 10 widget zones
        public function sek_nimble_widgets_init() {
            if ( sek_is_widget_module_disabled() )
              return;

            $number_of_widgets = apply_filters( 'nimble_number_of_wp_widgets', 10 );

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
            for ( $i=1; $i < ( intval( $number_of_widgets) + 1 ); $i++ ) {
                $args['id'] = NIMBLE_WIDGET_PREFIX . $i;//'nimble-widget-area-'
                $args['name'] = sprintf( __('Nimble widget area #%1$s', 'text_domain_to_replace' ), $i );
                $args['description'] = $args['name'];
                $args = wp_parse_args( $args, $defaults );
                register_sidebar( $args );
            }
        }

        // Invoked @'after_setup_theme'
        static function sek_get_front_module_collection() {
            $front_module_collection = [
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

              'czr_accordion_module' => array(
                'czr_accordion_module',
                'czr_accordion_collection_child',
                'czr_accordion_opts_child'
              ),

              'czr_gallery_module' => array(
                'czr_gallery_module',
                'czr_gallery_collection_child',
                'czr_gallery_opts_child'
              ),

              'czr_shortcode_module',
            ];

            if ( !sek_is_widget_module_disabled() ) {
              $front_module_collection[] = 'czr_widget_area_module';
            }

            return apply_filters( 'sek_get_front_module_collection', $front_module_collection );
        }

    }//class
endif;
?>