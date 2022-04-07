<?php
if ( !class_exists( 'SEK_Front_Render' ) ) :
    class SEK_Front_Render extends SEK_Front_Assets_Customizer_Preview {
        // Fired in __construct()
        function _schedule_front_rendering() {
            if ( !defined( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY", PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY", PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY" ) ) { define( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY", - PHP_INT_MAX ); }

            // Fires after 'wp' and before the 'get_header' template file is loaded.
            add_action( 'template_redirect', array( $this, 'sek_schedule_rendering_hooks') );

            // Encapsulate the singular post / page content so we can generate a dynamic ui around it when customizing
            add_filter( 'the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

            // SCHEDULE THE ASSETS ENQUEUING
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_the_printed_module_assets') );

            // SMART LOAD
            add_filter( 'nimble_parse_for_smart_load', array( $this, 'sek_maybe_process_img_for_js_smart_load') );

            // SETUP OUR the_content FILTER for the Tiny MCE module
            $this->sek_setup_tiny_mce_content_filters();

            // REGISTER HEADER AND FOOTER GLOBAL LOCATIONS
            add_action( 'nimble_front_classes_ready', array( $this, 'sek_register_nimble_global_locations') );

            // CONTENT : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
            add_filter( 'template_include', array( $this, 'sek_maybe_set_local_nimble_template' ) );

            // HEADER FOOTER
            // Header/footer, widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
            add_action( 'template_redirect', array( $this, 'sek_maybe_set_nimble_header_footer' ) );
            // HEADER : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
            add_filter( 'get_header', array( $this, 'sek_maybe_set_local_nimble_header') );
            // FOOTER : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
            add_filter( 'get_footer', array( $this, 'sek_maybe_set_local_nimble_footer') );

            // INCLUDE NIMBLE CONTENT IN SEARCH RESULTS
            add_action( 'wp_head', array( $this, 'sek_maybe_include_nimble_content_in_search_results' ) );

            add_filter( 'body_class', array( $this, 'sek_add_front_body_class') );

            // PASSWORD FORM AND CONTENT RESTRICTION ( PLUGINS )
            $this->sek_schedule_content_restriction_actions();
        }//_schedule_front_rendering()


        // @'body_class'
        function sek_add_front_body_class( $classes ) {
            $classes = is_array($classes) ? $classes : array();
            // Check whether we're in the customizer preview.
            if ( is_customize_preview() ) {
                $classes[] = 'customizer-preview';
            }
            if ( !is_customize_preview() ) {
                $skope_id = skp_get_skope_id();
                $group_skope = sek_get_group_skope_for_site_tmpl();
                if ( sek_is_inheritance_locally_disabled() ) {
                    array_unshift( $classes, 'nimble-site-tmpl-inheritance-disabled' );
                }
                if ( sek_has_group_site_template_data() ) {
                    // Site template params are structured as follow :
                    // [
                    //     'site_tmpl_id' : '_no_site_tmpl_',
                    //     'site_tmpl_source' : 'user_tmpl',
                    //     'site_tmpl_title' : ''
                    //];
                    $tmpl_params = sek_get_site_tmpl_params_for_skope( $group_skope );
                    array_unshift( $classes, 'nimble-site-tmpl__' . $tmpl_params['site_tmpl_source'] . '__' . $tmpl_params['site_tmpl_id'] );
                    array_unshift( $classes, 'nimble-has-group-site-tmpl-' . $group_skope );
                } else {
                    array_unshift( $classes, 'nimble-no-group-site-tmpl-' . $group_skope );
                }
                array_unshift( $classes, !sek_local_skope_has_been_customized() ? 'nimble-no-local-data-' . $skope_id : 'nimble-has-local-data-' . $skope_id );
            }
            if ( sek_is_pro() ) {
                array_unshift( $classes, 'nb-pro-' . str_replace('.', '-', NB_PRO_VERSION ) );
            }
            array_unshift( $classes, 'nb-' . str_replace('.', '-', NIMBLE_VERSION ) );

            return $classes;
        }

        // Encapsulate the singular post / page content so we can generate a dynamic ui around it when customizing
        // @filter the_content::NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY
        function sek_wrap_wp_content( $html ) {
            if ( !skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) )
              return $html;
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                global $post;
                // note : the edit url is printed as a data attribute to prevent being automatically parsed by wp when customizing and turned into a changeset url
                $html = sprintf( '<div class="sek-wp-content-wrapper" data-sek-wp-post-id="%1$s" data-sek-wp-edit-link="%2$s" title="%3$s">%4$s</div>',
                      $post->ID,
                      // we can't rely on the get_edit_post_link() function when customizing because emptied by wp core
                      $this->get_unfiltered_edit_post_link( $post->ID ),
                      __( 'WordPress content', 'text_domain'),
                      wpautop( $html )
                );
            }
            return $html;
        }


        // Fired in the constructor
        function sek_register_nimble_global_locations() {
            register_location('nimble_local_header', array( 'is_header_location' => true ) );
            register_location('nimble_local_footer', array( 'is_footer_location' => true ) );
            register_location('nimble_global_header', array( 'is_global_location' => true, 'is_header_location' => true ) );
            register_location('nimble_global_footer', array( 'is_global_location' => true, 'is_footer_location' => true ) );
        }

        // @template_redirect
        // When using the default theme template, let's schedule the default hooks rendering
        // When using the Nimble template, this is done with render_content_sections_for_nimble_template();
        function sek_schedule_rendering_hooks() {
            $locale_template = sek_get_locale_template();
            // cache all locations now
            $all_locations = sek_get_locations();

            // $default_locations = [
            //     'loop_start' => array( 'priority' => 10 ),
            //     'before_content' => array(),
            //     'after_content' => array(),
            //     'loop_end' => array( 'priority' => 10 ),
            // ]
            // SCHEDULE THE ACTIONS ON HOOKS AND CONTENT FILTERS
            foreach( $all_locations as $location_id => $params ) {
                $params = is_array( $params ) ? $params : array();
                $params = wp_parse_args( $params, array( 'priority' => 10 ) );

                // When a local template is used, the default locations are rendered with :
                // render_nimble_locations(
                //     array_keys( Nimble_Manager()->default_locations ),//array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
                // );
                // @see nimble tmpl/ template files
                // That's why we don't need to add the rendering actions for the default locations. We only need to add action for the possible locations registered on the theme hooks
                if ( !empty( $locale_template ) && !array_key_exists( $location_id, Nimble_Manager()->default_locations ) ) {
                    add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                } else {
                    switch ( $location_id ) {
                        case 'loop_start' :
                        case 'loop_end' :
                            // Do not add loop_start, loop_end action hooks when in a jetpack's like "infinite scroll" query
                            // see: https://github.com/presscustomizr/nimble-builder/issues/228
                            // the filter 'infinite_scroll_got_infinity' is documented both in jetpack's infinite module
                            // and in Customizr-Pro/Hueman-Pro infinite scroll code. They both use the same $_GET var too.
                            // Actually this is not needed anymore for our themes, see:
                            // https://github.com/presscustomizr/nimble-builder/issues/228#issuecomment-449362111
                            if ( !( apply_filters( 'infinite_scroll_got_infinity', isset( $_GET[ 'infinity' ] ) ) ) ) {
                                add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                            }
                        break;
                        case 'before_content' :
                            add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                        break;
                        case 'after_content' :
                            add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );
                        break;
                        // Default is typically used for custom locations
                        default :
                            add_action( $location_id, array( $this, 'sek_schedule_sektions_rendering' ), $params['priority'] );
                        break;
                    }
                }

            }
        }



        // hook : loop_start, loop_end, and all custom locations like __before_main_wrapper, __after_header or __before_footer in the Customizr theme.
        // @return void()
        function sek_schedule_sektions_rendering( $query = null ) {
            // Check if the passed query is the main_query, bail if not
            // fixes: https://github.com/presscustomizr/nimble-builder/issues/154 2.
            // Note: a check using $query instanceof WP_Query would return false here, probably because the
            // query object is passed by reference
            // accidentally this would also fix the same point 1. of the same issue if the 'sek_schedule_rendering_hooks' method will be fired
            // with an early hook (earlier than wp_head).
            if ( is_object( $query ) && is_a( $query, 'WP_Query' ) && !$query->is_main_query() ) {
                return;
            }

            $location_id = current_filter();
            $this->_render_seks_for_location( $location_id );
        }

        // hook : 'the_content'::-9999
        function sek_schedule_sektion_rendering_before_content( $html ) {
            return $this->_filter_the_content( $html, 'before_content' );
        }

        // hook : 'the_content'::9999
        function sek_schedule_sektion_rendering_after_content( $html ) {
            return $this->_filter_the_content( $html, 'after_content' );
        }

        private function _filter_the_content( $html, $where ) {
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                ob_start();
                $this->_render_seks_for_location( $where );
                $html = 'before_content' == $where ? ob_get_clean() . $html : $html . ob_get_clean();
                // Collapse line breaks before and after <div> elements so they don't get autop'd.
                // @see function wpautop() in wp-includes\formatting.php
                // @fixes https://github.com/presscustomizr/nimble-builder/issues/32
                if ( strpos( $html, '<div' ) !== false ) {
                  $html = preg_replace( '|\s*<div|', '<div', $html );
                  $html = preg_replace( '|</div>\s*|', '</div>', $html );
                }
            }

            return $html;
        }

        // the $location_data can be provided. Typically when using the function render_content_sections_for_nimble_template in the Nimble page template.
        // @param $skope_id added april 2020 for https://github.com/presscustomizr/nimble-builder/issues/657
        public function _render_seks_for_location( $location_id = '', $location_data = array(), $skope_id = '' ) {
            // why check if did_action( ... ) ?
            //  => A location can be rendered only once
            // => for loop_start and loop_end, checking with is_main_query() is not enough because the main loop might be used 2 times in the same page
            // => for a custom location, it can be rendered by do_action() somewhere, and be rendered also with render_nimble_locations()
            // @see issue with Twenty Seventeen here : https://github.com/presscustomizr/nimble-builder/issues/14
            if ( is_string( $location_id) && did_action( "sek_before_location_{$location_id}" ) )
              return;

            $all_locations = sek_get_locations();

            if ( !array_key_exists( $location_id, $all_locations ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location_id . ' is not registered in sek_get_locations()');
                return;
            }

            do_action( "sek_before_location_{$location_id}" );

            $locationSettingValue = array();
            $is_global_location = sek_is_global_location( $location_id );
            if ( empty( $location_data ) ) {
                // APRIL 2020 added for for https://github.com/presscustomizr/nimble-builder/issues/657
                if ( empty($skope_id) ) {
                    $skope_id = $is_global_location ? NIMBLE_GLOBAL_SKOPE_ID : skp_build_skope_id();
                }
                $locationSettingValue = sek_get_skoped_seks( $skope_id, $location_id );
            } else {
                $locationSettingValue = $location_data;
            }
            if ( is_array( $locationSettingValue ) ) {

                remove_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );
                // sek_error_log( 'LEVEL MODEL IN ::sek_schedule_sektions_rendering()', $locationSettingValue);
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );
                // rendering property allows us to determine if we're rendering NB content while filtering WP core functions, like the one of the lazy load attributes
                Nimble_Manager()->rendering = true;

                $this->render( $locationSettingValue, $location_id );

                Nimble_Manager()->rendering = false;

                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ),NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                add_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

                // inform Nimble Builder that a global section has been rendered
                // introduced for https://github.com/presscustomizr/nimble-builder/issues/456
                if ( $is_global_location ) {
                    Nimble_Manager()->global_sections_rendered = true;
                }

            } else {
                error_log( __CLASS__ . ' :: ' . __FUNCTION__ .' => sek_get_skoped_seks() should always return an array().');
            }

            do_action( "sek_after_location_{$location_id}" );

        }//_render_seks_for_location(






        /* ------------------------------------------------------------------------- *
         * RENDERING UTILITIES USED IN NIMBLE TEMPLATES
        /* ------------------------------------------------------------------------- */
        // @return void()
        // @param $locations. mixed type
        // @param $options (array)
        // Note that a location can be rendered only once in a given page.
        // That's why we need to check if did_action(''), like in ::sek_schedule_sektions_rendering
        function render_nimble_locations( $locations, $options = array() ) {
            if ( is_string( $locations ) && !empty( $locations ) ) {
                $locations = array( $locations );
            }
            if ( !is_array( $locations ) ) {
                sek_error_log( __FUNCTION__ . ' error => missing or invalid locations provided');
                return;
            }

            // Normalize the $options
            $options = !is_array( $options ) ? array() : $options;
            $options = wp_parse_args( $options, array(
                // fallback_location => the location rendered even if empty.
                // This way, the user starts customizing with only one location for the content instead of four
                // But if the other locations were already customized, they will be printed.
                'fallback_location' => null, // Typically set as 'loop_start' in the nimble templates
            ));

            //$is_global = sek_is_global_location( $location_id );
            // $skopeLocationCollection = array();
            // $skopeSettingValue = sek_get_skoped_seks( $skope_id );
            // if ( is_array( ) && array_key_exists('collection', search) ) {
            //     $skopeLocationCollection = $skopeSettingValue['collection'];
            // }

            //sek_error_log( __FUNCTION__ . ' sek_get_skoped_seks(  ', sek_get_skoped_seks() );
            foreach( $locations as $location_id ) {
                if ( !is_string( $location_id ) || empty( $location_id ) ) {
                    sek_error_log( __FUNCTION__ . ' => error => a location_id is not valid in the provided locations', $locations );
                    continue;
                }
                // why check if did_action( ... ) ?
                // => A location can be rendered only once
                // => for loop_start and loop_end, checking with is_main_query() is not enough because the main loop might be used 2 times in the same page
                // => for a custom location, it can be rendered by do_action() somewhere, and be rendered also with render_nimble_locations()
                // @see issue with Twenty Seventeen here : https://github.com/presscustomizr/nimble-builder/issues/14
                if ( did_action( "sek_before_location_{$location_id}" ) )
                  continue;

                $is_global = sek_is_global_location( $location_id );
                $skope_id = $is_global ? NIMBLE_GLOBAL_SKOPE_ID : skp_get_skope_id();
                $locationSettingValue = sek_get_skoped_seks( $skope_id, $location_id );
                if ( !is_null( $options[ 'fallback_location' ]) ) {
                    // We don't need to render the locations with no sections
                    // But we need at least one location : let's always render loop_start.
                    // => so if the user switches from the nimble_template to the default theme one, the loop_start section will always be rendered.
                    if ( $options[ 'fallback_location' ] === $location_id || ( is_array( $locationSettingValue ) && !empty( $locationSettingValue['collection'] ) ) ) {
                        Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                    }
                } else {
                    Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                }

            }//render_nimble_locations()
        }







        /* ------------------------------------------------------------------------- *
         *  MAIN RENDERING METHOD
        /* ------------------------------------------------------------------------- */
        // Walk a model tree recursively and render each level with a specific template
        function render( $model = array(), $location = 'loop_start' ) {
            //sek_error_log('LOCATIONS IN ::render()', sek_get_locations() );
            //sek_error_log('LEVEL MODEL IN ::RENDER()', $model );
            // Is it the root level ?
            // The root level has no id and no level entry
            if ( !is_array( $model ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a model must be an array', $model );
                return;
            }
            if ( !array_key_exists( 'level', $model ) || !array_key_exists( 'id', $model ) ) {
                error_log( '::render() => a level model is missing the level or the id property' );
                return;
            }
            // The level "id" is a string not empty
            $id = $model['id'];
            if ( !is_string( $id ) || empty( $id ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a level id must be a string not empty', $model );
                return;
            }

            // The level "level" can take 4 values : location, section, column, module
            $level_type = $model['level'];
            if ( !is_string( $level_type ) || empty( $level_type ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a level type must be a string not empty', $model );
                return;
            }

            // A level id can be rendered only once by the recursive ::render method
            if ( in_array( $id, Nimble_Manager()->rendered_levels ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a ' . $level_type . ' level id has already been rendered : ' . $id );
                return;
            }
            // Record the rendered id now
            Nimble_Manager()->rendered_levels[] = $id;

            // Cache the parent model
            // => used when calculating the width of the column to be added
            $parent_model = $this->parent_model;
            $this->model = $model;

            $collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();

            //sek_error_log( __FUNCTION__ . ' WHAT ARE WE RENDERING? ' . $id, current_filter() . ' | ' . current_action() );
            $level_custom_anchor = null;
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                    $level_custom_anchor = esc_attr( $model[ 'options' ][ 'anchor' ]['custom_anchor'] );
                }
            }
            $level_css_classes = '';
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] ) ) {
                if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] ) ) {
                    $level_css_classes = esc_attr( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] );
                    //clean commas
                    $level_css_classes = preg_replace("/(?<!\d)(\,|\.)(?!\d)/", "", $level_css_classes);
                    //$level_css_classes = preg_replace("/[^0-9a-zA-Z]/","", $level_css_classes);
                }
            }

            // sept 2020 => Box shadow CSS class
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'border' ] ) && !empty( $model[ 'options' ][ 'border' ]['shadow'] ) ) {
                if ( sek_is_checked( $model[ 'options' ][ 'border' ]['shadow'] ) ) {
                    $level_css_classes .= 'sek-level-has-shadow';
                }
            }

            Nimble_Manager()->level_css_classes = apply_filters( 'nimble_level_css_classes', $level_css_classes, $model );
            Nimble_Manager()->level_custom_anchor = $level_custom_anchor;
            Nimble_Manager()->level_custom_attr = apply_filters( 'nimble_level_custom_data_attributes', '', $model );

            do_action('nimble_before_rendering_level', $model, Nimble_Manager()->level_css_classes, Nimble_Manager()->level_custom_attr );

            switch ( $level_type ) {
                case 'location' :
                    load_template( sek_get_templates_dir() . "/base-tmpl/location.php", false );
                break;
                case 'section' :
                    load_template( sek_get_templates_dir() . "/base-tmpl/section.php", false );
                break;
                case 'column' :
                    load_template( sek_get_templates_dir() . "/base-tmpl/column.php", false );
                break;
                case 'module' :
                    load_template( sek_get_templates_dir() . "/base-tmpl/module.php", false );
                break;
                default :
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' error => a level is invalid : ' . $level_type  );
                break;
            }

            $this->parent_model = $parent_model;
        }//render









        /* ------------------------------------------------------------------------- *
         * VARIOUS HELPERS
        /* ------------------------------------------------------------------------- */
        /* HELPER TO PRINT THE VISIBILITY CSS CLASS IN THE LEVEL CONTAINER */
        // Dec 2019 : since issue https://github.com/presscustomizr/nimble-builder/issues/555, we use a dynamic CSS rule generation instead of static CSS
        // The CSS class are kept only for information when inspecting the markup
        // @see sek_add_css_rules_for_level_visibility()
        // @return string
        public function get_level_visibility_css_class( $model ) {
            if ( !is_array( $model ) ) {
                error_log( __FUNCTION__ . ' => $model param should be an array' );
                return;
            }
            $visibility_class = '';
            //when boxed use proper container class
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'visibility' ] ) ) {
                if ( is_array( $model[ 'options' ][ 'visibility' ] ) ) {
                    foreach ( $model[ 'options' ][ 'visibility' ] as $device_type => $device_visibility_bool ) {
                        if ( true !== sek_booleanize_checkbox_val( $device_visibility_bool ) ) {
                            $visibility_class .= " sek-hidden-on-{$device_type}";
                        }
                    }
                }
            }
            return $visibility_class;
        }





        /* MODULE AND PLACEHOLDER */
        // module templates can be overriden from a child theme when located in nimble_templates/modules/{template_name}.php
        // for example /wp-content/themes/twenty-nineteen-child/nimble_templates/modules/image_module_tmpl.php
        // added for #532, october 2019
        public function sek_maybe_get_overriden_template_path_for_module( $template_name = '') {
            if ( empty( $template_name ) )
              return;
            $overriden_template_path = '';
            // try locating this template file by looping through the template paths
            // inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
            foreach( sek_get_theme_template_base_paths() as $path_candidate ) {
              if( file_exists( $path_candidate . 'modules/' . $template_name ) ) {
                $overriden_template_path = $path_candidate . 'modules/' . $template_name;
                break;
              }
            }

            return $overriden_template_path;
        }


        // march 2020 : not used anymore
        function sek_get_input_placeholder_content( $input_type = '', $input_id = '' ) {
            $ph = '<i class="material-icons">pan_tool</i>';
            switch( $input_type ) {
                case 'detached_tinymce_editor' :
                case 'nimble_tinymce_editor' :
                case 'text' :
                  $ph = skp_is_customizing() ? '<div class="sek-tiny-mce-module-placeholder-text">' . __('Click to edit', 'here') .'</div>' : '';
                break;
                case 'upload' :
                  $ph = '<i class="material-icons">image</i>';
                break;
            }
            switch( $input_id ) {
                case 'html_content' :
                  $ph = skp_is_customizing() ? sprintf('<pre>%1$s<br/>%2$s</pre>', __('Html code goes here', 'text-domain'), __('Click to edit', 'here') ) : '';
                break;
            }
            if ( skp_is_customizing() ) {
                return sprintf('<div class="sek-module-placeholder" title="%4$s" data-sek-input-type="%1$s" data-sek-input-id="%2$s">%3$s</div>', $input_type, $input_id, $ph, __('Click to edit', 'here') );
            } else {
                return $ph;
            }
        }



        /**
         * unfiltered version of get_edit_post_link() located in wp-includes/link-template.php
         * ( filtered by wp core when invoked in customize-preview )
         */
        function get_unfiltered_edit_post_link( $id = 0, $context = 'display' ) {
            if ( !$post = get_post( $id ) )
              return;

            if ( 'revision' === $post->post_type )
              $action = '';
            elseif ( 'display' == $context )
              $action = '&amp;action=edit';
            else
              $action = '&action=edit';

            $post_type_object = get_post_type_object( $post->post_type );
            if ( !$post_type_object )
              return;

            if ( !current_user_can( 'edit_post', $post->ID ) )
              return;

            if ( $post_type_object->_edit_link ) {
              $link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
            } else {
              $link = '';
            }
            return $link;
        }



        // @hook wp_enqueue_scripts
        function sek_enqueue_the_printed_module_assets() {
            $skope_id = skp_get_skope_id();
            $skoped_seks = sek_get_skoped_seks( $skope_id );

            if ( !is_array( $skoped_seks ) || empty( $skoped_seks['collection'] ) )
              return;

            $enqueueing_candidates = $this->sek_sniff_assets_to_enqueue( $skoped_seks['collection'] );

            foreach ( $enqueueing_candidates as $handle => $asset_params ) {
                if ( empty( $asset_params['type'] ) ) {
                    sek_error_log( __FUNCTION__ . ' => missing asset type', $asset_params );
                    continue;
                }
                switch ( $asset_params['type'] ) {
                    case 'css' :
                        wp_enqueue_style(
                            $handle,
                            array_key_exists( 'src', $asset_params ) ? $asset_params['src'] : null,
                            array_key_exists( 'deps', $asset_params ) ? $asset_params['deps'] : array(),
                            NIMBLE_ASSETS_VERSION,
                            'all'
                        );
                    break;
                    case 'js' :
                        wp_enqueue_script(
                            $handle,
                            array_key_exists( 'src', $asset_params ) ? $asset_params['src'] : null,
                            array_key_exists( 'deps', $asset_params ) ? $asset_params['deps'] : null,
                            array_key_exists( 'ver', $asset_params ) ? $asset_params['ver'] : null,
                            array_key_exists( 'in_footer', $asset_params ) ? $asset_params['in_footer'] : false
                        );
                    break;
                }
            }
        }//sek_enqueue_the_printed_module_assets()

        // @hook sek_sniff_assets_to_enqueue
        function sek_sniff_assets_to_enqueue( $collection, $enqueuing_candidates = array() ) {
            foreach ( $collection as $level_data ) {
                if ( array_key_exists( 'level', $level_data ) && 'module' === $level_data['level'] && !empty( $level_data['module_type'] ) ) {
                    $front_assets = sek_get_registered_module_type_property( $level_data['module_type'], 'front_assets' );
                    if ( is_array( $front_assets ) ) {
                        foreach ( $front_assets as $handle => $asset_params ) {
                            if ( is_string( $handle ) && !array_key_exists( $handle, $enqueuing_candidates ) ) {
                                $enqueuing_candidates[ $handle ] = $asset_params;
                            }
                        }
                    }
                } else {
                    if ( array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
                        $enqueuing_candidates = $this->sek_sniff_assets_to_enqueue( $level_data['collection'], $enqueuing_candidates );
                    }
                }
            }//foreach
            return $enqueuing_candidates;
        }

        /* ------------------------------------------------------------------------- *
         *  SMART LOAD.
        /* ------------------------------------------------------------------------- */
        // @return array of bg attributes
        // adds the lazy load data attributes when sek_is_img_smartload_enabled()
        // adds the parallax attributes
        // img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
        // the local option wins
        // deactivated when customizing @see function sek_is_img_smartload_enabled()
        function sek_maybe_add_bg_attributes( $model ) {
            $new_attributes = [];
            $bg_img_url = '';
            $parallax_enabled = false;
            $fixed_bg_enabled = false;
            $width = '';
            $height = '';
            $level_type = array_key_exists( 'level', $model ) ? $model['level'] : 'section';

            // will be used for sections (not columns and modules ) that have a video background
            // implemented for video bg https://github.com/presscustomizr/nimble-builder/issues/287
            $video_bg_url = '';
            $video_bg_loop = true;
            $video_bg_delay_before_start = null;
            $video_bg_on_mobile = false;
            $video_bg_start_time = null;
            $video_bg_end_time = null;


            if ( !empty( $model[ 'options' ] ) && is_array( $model['options'] ) ) {
                $bg_options = ( !empty( $model[ 'options' ][ 'bg' ] ) && is_array( $model[ 'options' ][ 'bg' ] ) ) ? $model[ 'options' ][ 'bg' ] : array();
                $use_post_thumbnail_bg = !empty( $bg_options['bg-use-post-thumb'] ) && sek_is_checked( $bg_options['bg-use-post-thumb'] );
                if ( !empty( $bg_options[ 'bg-image'] ) || $use_post_thumbnail_bg ) {
                    $bg_image_id_or_url = '';
                    
                    // Feb 2021
                    // First check if user wants to use the contextual post thumbnail
                    // Fallback on the regular image background if not
                    if ( $use_post_thumbnail_bg ) {
                        $current_post_id = sek_get_post_id_on_front_and_when_customizing();
                        $bg_image_id_or_url = ( has_post_thumbnail( $current_post_id ) ) ? get_post_thumbnail_id( $current_post_id ) : $bg_image_id_or_url;
                    }
                    if ( empty($bg_image_id_or_url) ) {
                        $bg_image_id_or_url = $bg_options[ 'bg-image'];
                    }
                    
                    // April 2020 :
                    // on import, user can decide to use the image url instead of importing
                    // we need to check if the image is set as an attachement id or starts with 'http'
                    // introduced for https://github.com/presscustomizr/nimble-builder/issues/663
                    if ( is_numeric( $bg_image_id_or_url ) ) {
                        $bg_img_url = wp_get_attachment_url( $bg_image_id_or_url );
                    } else if ( "http" === substr( $bg_image_id_or_url, 0, 4 ) ) {
                        $bg_img_url = $bg_image_id_or_url;
                    }

                    // At this point we may not have a valid $bg_img_url
                    // let's check
                    if ( !empty( $bg_img_url ) ) {
                        $new_attributes['data-sek-has-bg'] = 'true';
                        if ( defined('DOING_AJAX') && DOING_AJAX ) {
                            $new_attributes['style'] = sprintf('background-image:url(\'%1$s\');', esc_url( $bg_img_url ));
                        } else {
                            $new_attributes['data-sek-src'] = sprintf( '%1$s', esc_url($bg_img_url) );
                            if ( sek_is_img_smartload_enabled() ) {
                                $new_attributes['data-sek-lazy-bg'] = 'true';
                            }
                        }

                        // When the fixed background is ckecked, it wins against parallax
                        $fixed_bg_enabled = !empty( $bg_options['bg-attachment'] ) && sek_booleanize_checkbox_val( $bg_options['bg-attachment'] );
                        $parallax_enabled = !$fixed_bg_enabled && !empty( $bg_options['bg-parallax'] ) && sek_booleanize_checkbox_val( $bg_options['bg-parallax'] );
                        if ( $parallax_enabled && is_numeric( $bg_image_id_or_url ) ) {
                            $image = wp_get_attachment_image_src( $bg_image_id_or_url, 'full' );
                            if ( $image ) {
                                list( $src, $width, $height ) = $image;
                            }
                        }
                    }
                }


                // Nov 2019, for video background https://github.com/presscustomizr/nimble-builder/issues/287
                // should be added for sections and columns only
                if ( in_array( $level_type, array( 'section', 'column') ) && !empty( $bg_options[ 'bg-use-video'] ) && sek_booleanize_checkbox_val( $bg_options[ 'bg-use-video'] ) ) {
                    if ( !empty( $bg_options[ 'bg-video' ] ) ) {
                        $video_bg_url = $bg_options[ 'bg-video' ];
                        // replace http by https if needed for mp4 video url
                        // fixes https://github.com/presscustomizr/nimble-builder/issues/550
                        if ( is_ssl() && is_string($video_bg_url) && stripos($video_bg_url, 'http://') === 0 ) {
                            $video_bg_url = 'https' . substr($video_bg_url, 4);
                        }
                    }
                    if ( array_key_exists( 'bg-video-loop', $bg_options ) ) {
                        $video_bg_loop = sek_booleanize_checkbox_val( $bg_options[ 'bg-video-loop' ] );
                    }
                    if ( !empty( $bg_options[ 'bg-video-delay-start' ] ) ) {
                        $video_bg_delay_before_start = abs( (int)$bg_options[ 'bg-video-delay-start' ] );
                    }

                    if ( array_key_exists( 'bg-video-on-mobile', $bg_options ) ) {
                        $video_bg_on_mobile = sek_booleanize_checkbox_val( $bg_options[ 'bg-video-on-mobile' ] );
                    }
                    if ( !empty( $bg_options[ 'bg-video-start-time' ] ) ) {
                        $video_bg_start_time = abs( (int)$bg_options[ 'bg-video-start-time' ] );
                    }
                    if ( !empty( $bg_options[ 'bg-video-end-time' ] ) ) {
                        $video_bg_end_time = abs( (int)$bg_options[ 'bg-video-end-time' ] );
                    }
                }
            }


            // data-sek-bg-fixed attribute has been added for https://github.com/presscustomizr/nimble-builder/issues/414
            // @see css rules related
            // we can't have both fixed and parallax option together
            // when the fixed background is ckecked, it wins against parallax
            if ( $fixed_bg_enabled ) {
                $new_attributes['data-sek-bg-fixed'] = 'true';
            } else if ( $parallax_enabled ) {
                $new_attributes['data-sek-bg-parallax'] = 'true';
                $new_attributes['data-bg-width'] = $width;
                $new_attributes['data-bg-height'] = $height;
                $new_attributes['data-sek-parallax-force'] = array_key_exists('bg-parallax-force', $bg_options) ? $bg_options['bg-parallax-force'] : '40';
            }

            // video background insertion can only be done for sections and columns
            if ( in_array( $level_type, array( 'section', 'column') ) ) {
                if ( !empty( $video_bg_url ) && is_string( $video_bg_url ) ) {
                    $new_attributes['data-sek-video-bg-src'] = esc_url( $video_bg_url );
                    $new_attributes['data-sek-video-bg-loop'] = $video_bg_loop ? 'true' : 'false';
                    if ( !is_null( $video_bg_delay_before_start ) && $video_bg_delay_before_start >= 0 ) {
                        $new_attributes['data-sek-video-delay-before'] = $video_bg_delay_before_start;
                    }
                    $new_attributes['data-sek-video-bg-on-mobile'] = $video_bg_on_mobile ? 'true' : 'false';
                    if ( !is_null( $video_bg_start_time ) && $video_bg_start_time >= 0 ) {
                        $new_attributes['data-sek-video-start-at'] = $video_bg_start_time;
                    }
                    if ( !is_null( $video_bg_end_time ) && $video_bg_end_time >= 0 ) {
                        $new_attributes['data-sek-video-end-at'] = $video_bg_end_time;
                    }
                }
            }
            return $new_attributes;
        }


        // @filter nimble_parse_for_smart_load
        // this filter is used in several modules : tiny_mce_editor, image module, post grid
        // img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
        // deactivated when customizing @see function sek_is_img_smartload_enabled()
        // @return html string
        function sek_maybe_process_img_for_js_smart_load( $html ) {
            // if ( skp_is_customizing() || !sek_is_img_smartload_enabled() )
            //   return $html;

            // Disable smart load parsing when building in the customizer
            if ( defined('DOING_AJAX') && DOING_AJAX ) {
                return $html;
            }

            // prevent lazyloading images when in header section
            // @see https://github.com/presscustomizr/nimble-builder/issues/705
            if ( Nimble_Manager()->current_location_is_header )
              return $html;

            if ( !sek_is_img_smartload_enabled() )
              return $html;
            if ( !is_string( $html ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => provided html is not a string', $html );
                return $html;
            }
            if ( is_feed() || is_preview() )
                return $html;

            $allowed_image_extensions = apply_filters( 'nimble_smartload_allowed_img_extensions', array(
                'bmp',
                'gif',
                'jpeg',
                'jpg',
                'jpe',
                'tif',
                'tiff',
                'ico',
                'png',
                'svg',
                'svgz'
            ) );

            if ( empty( $allowed_image_extensions ) || !is_array( $allowed_image_extensions ) ) {
              return $html;
            }

            $img_extensions_pattern = sprintf( "(?:%s)", implode( '|', $allowed_image_extensions ) );
            $pattern = '#<img([^>]+?)src=[\'"]?([^\'"\s>]+\.'.$img_extensions_pattern.'[^\'"\s>]*)[\'"]?([^>]*)>#i';

            return preg_replace_callback( $pattern, '\Nimble\nimble_regex_callback', $html);
        }


        ////////////////////////////////////////////////////////////////
        // SETUP CONTENT FILTERS FOR TINYMCE MODULE
        // Fired in the constructor
        private function sek_setup_tiny_mce_content_filters() {
            // @see filters in wp-includes/default-filters.php
            // always check if 'do_blocks' exists for retrocompatibility with WP < 5.0. @see https://github.com/presscustomizr/nimble-builder/issues/237
            if ( function_exists( 'do_blocks' ) ) {
                add_filter( 'the_nimble_tinymce_module_content', 'do_blocks', 9 );
            }
            add_filter( 'the_nimble_tinymce_module_content', 'wptexturize' );
            add_filter( 'the_nimble_tinymce_module_content', 'convert_smilies', 20 );
            add_filter( 'the_nimble_tinymce_module_content', 'wpautop' );
            add_filter( 'the_nimble_tinymce_module_content', 'shortcode_unautop' );

            // april 2021 => 'prepend_attachment' is normally in the list of default-filters for the_content.
            // it is used to Wrap attachment in paragraph tag before content. ( see wp-includes/post-templates.php )
            // NB doesn't need it and it can break {{the_title}} template tag when used in an attachment page.
            //add_filter( 'the_nimble_tinymce_module_content', 'prepend_attachment' );

            // July 2020 : compatibility with WP 5.5
            if ( function_exists('wp_filter_content_tags') ) {
                add_filter( 'the_nimble_tinymce_module_content', 'wp_filter_content_tags' );
            } else {
                add_filter( 'the_nimble_tinymce_module_content', 'wp_make_content_images_responsive' );
            }
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_do_shortcode' ), 11 ); // AFTER wpautop()
            add_filter( 'the_nimble_tinymce_module_content', 'capital_P_dangit', 9 );
            add_filter( 'the_nimble_tinymce_module_content', '\Nimble\sek_parse_template_tags', 21 );

            // Hack to get the [embed] shortcode to run before wpautop()
            // fixes Video Embed not showing when using Add Media > Insert from Url
            // @see https://github.com/presscustomizr/nimble-builder/issues/250
            // @see wp-includes/class-wp-embed.php
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_run_shortcode' ), 8 );

            // @see filters in wp-includes/class-wp-embed.php
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_parse_content_for_video_embed'), 8 );
        }

        // fired @filter the_nimble_tinymce_module_content
        // updated May 2020 : prevent doing shortcode when customizing
        // fixes https://github.com/presscustomizr/nimble-builder/issues/704
        function sek_do_shortcode( $content ) {
            if ( !skp_is_customizing() ) {
                $content = do_shortcode( $content );
            } else {
                $allow_shortcode_parsing_when_customizing = sek_booleanize_checkbox_val( get_option( NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING ) );
                if ( $allow_shortcode_parsing_when_customizing ) {
                    $content = do_shortcode( $content );
                } else {
                    global $shortcode_tags;
                    // Find all registered tag names in $content.
                    preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
                    $tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );

                    if ( !empty( $tagnames ) ) {
                    $content = sprintf('<div class="nimble-notice-in-preview"><i class="fas fa-info-circle"></i>&nbsp;%1$s</div>%2$s',
                        __('Shortcodes are not parsed by default when customizing. You can change this setting in your WP admin > Settings > Nimble Builder options.', 'text-doma'),
                        $content
                    );
                    }
                }
            }
            return $content;
        }

        // fired @filter the_nimble_tinymce_module_content
        // updated May 2020 : prevent doing shortcode when customizing
        // fixes https://github.com/presscustomizr/nimble-builder/issues/704
        function sek_run_shortcode( $content ) {
            // customizing => check if NB can parse the shortcode
            if ( skp_is_customizing() ) {
                $allow_shortcode_parsing_when_customizing = sek_booleanize_checkbox_val( get_option( NIMBLE_OPT_NAME_FOR_SHORTCODE_PARSING ) );
                if ( !$allow_shortcode_parsing_when_customizing ) {
                    return $content;
                }
            }
            // Not customizing always run
            if ( array_key_exists( 'wp_embed', $GLOBALS ) && $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
                $content = $GLOBALS['wp_embed']->run_shortcode( $content );
            }
            return $content;
        }

        // fired @filter the_nimble_tinymce_module_content
        function sek_parse_content_for_video_embed( $content ) {
            if ( array_key_exists( 'wp_embed', $GLOBALS ) && $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
                $content = $GLOBALS['wp_embed']->autoembed( $content );
            }
            return $content;
        }





        /* ------------------------------------------------------------------------- *
         *  CONTENT, HEADER, FOOTER
        /* ------------------------------------------------------------------------- */
        // fired @hook 'template_include'
        // @return template path
        function sek_maybe_set_local_nimble_template( $template ) {
            $locale_template = sek_get_locale_template();
            if ( !empty( $locale_template ) ) {
                $template = $locale_template;
            }
            //sek_error_log( 'TEMPLATE ? => ' . did_action('wp'), $template );
            return $template;
        }


        // fired @hook 'template_redirect'
        // fired by sek_maybe_set_local_nimble_footer() @get_footer()
        // fired by sek_maybe_set_local_nimble_header() @get_header()
        // @return void()
        // set the value of the properties
        // has_local_header_footer
        // has_global_header_footer
        function sek_maybe_set_nimble_header_footer() {
            if ( !did_action('nimble_front_classes_ready') || !did_action('wp') ) {
                sek_error_log( __FUNCTION__ . ' has been invoked too early at hook ' . current_filter() );
                return;
            }
            if ( '_not_cached_yet_' === $this->has_local_header_footer || '_not_cached_yet_' === $this->has_global_header_footer ) {
                $local_header_footer_data = sek_get_local_option_value('local_header_footer');
                $global_header_footer_data = sek_get_global_option_value('global_header_footer');

                $apply_local_option = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data ) && 'inherit' !== $local_header_footer_data['header-footer'];

                $this->has_global_header_footer = !is_null( $global_header_footer_data ) && is_array( $global_header_footer_data ) && !empty( $global_header_footer_data['header-footer'] ) && 'nimble_global' === $global_header_footer_data['header-footer'];
                $this->has_local_header_footer = false;
                if ( $apply_local_option ) {
                    $this->has_local_header_footer = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data['header-footer'] ) && 'nimble_local' === $local_header_footer_data['header-footer'];
                    $this->has_global_header_footer = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data['header-footer'] ) && 'nimble_global' === $local_header_footer_data['header-footer'];
                }
            }
        }



        // fired @filter get_header()
        // Nimble will use an overridable template if a local or global header/footer is used
        // template located in /tmpl/header/ or /tmpl/footer
        // developers can override this template from a theme with a file that has this path : 'nimble_templates/header/nimble_header_tmpl.php
        function sek_maybe_set_local_nimble_header( $header_name ) {
            // if Nimble_Manager()->has_local_header_footer || Nimble_Manager()->has_global_header_footer
            if ( sek_page_uses_nimble_header_footer() ) {
                // load the Nimble template which includes a call to wp_head()
                $template_file_name_with_php_extension = 'nimble_header_tmpl.php';
                $template_path = apply_filters( 'nimble_set_header_template_path', NIMBLE_BASE_PATH . "/tmpl/header/{$template_file_name_with_php_extension}", $template_file_name_with_php_extension );

                // dec 2019 : can be overriden from a child theme
                // see https://github.com/presscustomizr/nimble-builder/issues/568
                $overriden_template_path = sek_maybe_get_overriden_local_template_path( array( 'file_name' => $template_file_name_with_php_extension, 'folder' => 'header' ) );
                if ( !empty( $overriden_template_path ) ) {
                    $template_path = $overriden_template_path;
                }

                load_template( $template_path, false );

                // do like in wp core get_header()
                $templates = array();
                $header_name = (string) $header_name;
                if ( '' !== $header_name ) {
                  $templates[] = "header-{$header_name}.php";
                }

                $templates[] = 'header.php';

                // don't run wp_head a second time
                remove_all_actions( 'wp_head' );
                // capture the print and clean it.
                ob_start();
                // won't be re-loaded by the second call performed by WP
                // see https://developer.wordpress.org/reference/functions/locate_template/
                // and https://developer.wordpress.org/reference/functions/load_template/
                locate_template( $templates, true );
                ob_get_clean();
            }
        }

        // fired @filter get_footer()
        // Nimble will use an overridable template if a local or global header/footer is used
        // template located in /tmpl/header/ or /tmpl/footer
        // developers can override this template from a theme with a file that has this path : 'nimble_templates/footer/nimble_footer_tmpl.php
        function sek_maybe_set_local_nimble_footer( $footer_name ) {
            // if Nimble_Manager()->has_local_header_footer || Nimble_Manager()->has_global_header_footer
            if ( sek_page_uses_nimble_header_footer() ) {
                // load the Nimble template which includes a call to wp_footer()
                $template_file_name_with_php_extension = 'nimble_footer_tmpl.php';
                $template_path = apply_filters( 'nimble_set_header_template_path', NIMBLE_BASE_PATH . "/tmpl/footer/{$template_file_name_with_php_extension}", $template_file_name_with_php_extension );

                // dec 2019 : can be overriden from a child theme
                // see https://github.com/presscustomizr/nimble-builder/issues/568
                $overriden_template_path = sek_maybe_get_overriden_local_template_path( array( 'file_name' => $template_file_name_with_php_extension, 'folder' => 'footer' ) );
                if ( !empty( $overriden_template_path ) ) {
                    $template_path = $overriden_template_path;
                }

                load_template( $template_path, false );

                // do like in wp core get_footer()
                $templates = array();
                $name = (string) $footer_name;
                if ( '' !== $footer_name ) {
                    $templates[] = "footer-{$footer_name}.php";
                }

                $templates[]    = 'footer.php';

                // don't run wp_footer a second time
                remove_all_actions( 'wp_footer' );
                // capture the print and clean it.
                ob_start();
                // won't be re-loaded by the second call performed by WP
                // see https://developer.wordpress.org/reference/functions/locate_template/
                // and https://developer.wordpress.org/reference/functions/load_template/
                locate_template( $templates, true );
                ob_get_clean();
            }
        }//sek_maybe_set_local_nimble_footer


        // @hook wp_head
        // Elements of decisions for this implementation :
        // The problem to solve here is to add the post ( or pages ) where user has created Nimble sections for which the content matches the search term.
        // 1) we need a way to find the matches
        // 2) then to "map" the Nimble post to its related post or page
        // 3) then include the related post / page to the list of search result.
        // This can't be done by filtering the WP core query params, because Nimble sections are saved as separate posts, not post metas.
        // That's why the posts are added to the array of posts of the main query.
        //
        // fixes https://github.com/presscustomizr/nimble-builder/issues/439
        //
        // May 2019 => note that this implementation won't include Nimble sections created in other contexts than page or post.
        // This could be added in the future.
        // April 2020 : the found_posts number is not correct when search results are paginated. see https://github.com/presscustomizr/nimble-builder/issues/666
        //
        // partially inspired by https://stackoverflow.com/questions/24195818/add-results-into-wordpress-search-results
        function sek_maybe_include_nimble_content_in_search_results(){
            if ( !is_search() )
              return;
            global $wp_query;

            $query_vars = $wp_query->query_vars;
            if ( !is_array( $query_vars ) || empty( $query_vars['s'] ) )
              return;

            // Search query on Nimble CPT
            $sek_post_query_vars = array(
                'post_type'              => NIMBLE_CPT,
                'post_status'            => 'publish',//get_post_stati(),
                'posts_per_page'         => -1,
                'no_found_rows'          => true,
                'cache_results'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'lazy_load_term_meta'    => false,
                's' => $query_vars['s']
            );
            $query = new \WP_Query( $sek_post_query_vars );
            $nimble_post_candidates = array();
            // The search string has been found in a set of Nimble posts
            if ( is_array( $query->posts ) ) {
                foreach ( $query->posts as $post_object ) {
                    // The related WP object ( == skope ) is written in the title of Nimble CPT
                    // ex : nimble___skp__post_post_114, where 114 is the post_id
                    if ( preg_match('(post_page|post_post)', $post_object->post_title ) ) {
                        $_post_id = preg_replace('/[^0-9]/', '', $post_object->post_title );
                        $_post_id = intval($_post_id);
                        $post_candidate = get_post( $_post_id );
                        if ( is_object( $post_candidate ) ) {
                            array_push($nimble_post_candidates, $post_candidate);
                        }
                    }
                }
            }

            // april 2020 : found post for https://github.com/presscustomizr/nimble-builder/issues/666
            $nimble_found_posts = (int)count($nimble_post_candidates);

            // Merge Nimble posts to WP posts but only on the first result page
            // => this means that the first paginated result page may be > to the user post_per_page setting
            // fixes https://github.com/presscustomizr/nimble-builder/issues/666
            if ( !is_paged() ) {
                // important : when search results are paginated, $wp_query->posts includes the posts of the result page only, not ALL the search results posts.
                // => this means that $wp_query->posts is not equal to $wp_query->found_posts when results are paginated.
                $wp_query->posts = is_array($wp_query->posts) ? $wp_query->posts : array();

                // $wp_query->post_count : make sure we remove posts found both by initial query and Nimble search query
                // => this way we avoid pagination problems by setting a correct value for $wp_query->post_count
                $maybe_includes_duplicated = array_merge( $wp_query->posts, $nimble_post_candidates );
                $without_duplicated = array();
                $post_ids = array();
                foreach ( $maybe_includes_duplicated as $post_obj ) {
                    if ( in_array( $post_obj->ID, $post_ids ) )
                      continue;
                    $post_ids[] = $post_obj->ID;
                    $without_duplicated[] = $post_obj;
                }
                $wp_query->posts = $without_duplicated;
                $wp_query->post_count = (int)count($without_duplicated);
            }

            // Found post may include duplicated posts because the search result has been found both in the WP search query and in Nimble one.
            // This should be improved in the future.
            // The problem to solve here is that when a search query is paginated, $wp_query->posts only includes the posts of the current page, not all the posts of the search results.
            // If we had the entire set of WP results, we could create an array merging WP results with Nimble results, remove the duplicates and then calculate a real found_posts value. A possible solution would be to get the wp_query->request, remove the limit per page, and re-run a new query to get the entire set of search results.
            if ( is_numeric($nimble_found_posts) ) {
                $wp_query->found_posts = $wp_query->found_posts + $nimble_found_posts;
            }
        }// return



        // @return unique guid()
        // inspired from https://stackoverflow.com/questions/21671179/how-to-generate-a-new-guid#26163679
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
        function sek_get_preview_level_guid() {
              if ( '_preview_level_guid_not_set_' === $this->preview_level_guid ) {
                  // When ajaxing, typically creating content, we need to make sure that we use the initial guid generated last time the preview was refreshed
                  // @see preview::doAjax()
                  if ( isset( $_POST['preview-level-guid'] ) ) {
                      if ( empty( $_POST['preview-level-guid'] ) ) {
                            sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => error, preview-level-guid can not be empty' );
                      }
                      $this->preview_level_guid = sanitize_text_field($_POST['preview-level-guid']);
                  } else {
                      $this->preview_level_guid = sprintf('%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) );
                  }

              }
              return $this->preview_level_guid;
        }





        /* ------------------------------------------------------------------------- *
         *  CONTENT RESTRICTION
        /* ------------------------------------------------------------------------- */
        // fired in _schedule_front_rendering()
        // PASSWORD FORM AND CONTENT RESTRICTION ( PLUGINS )
        // - built-in WP password protection => make the wp pwd form is rendered only one time in a singular ( see #673 and #679 )
        // - membership and content restriction plugins => https://github.com/presscustomizr/nimble-builder/issues/685
        function sek_schedule_content_restriction_actions() {
            add_action( 'wp', array( $this, 'sek_set_password_protection_status') );
            // built-in WP password form
            add_action( 'the_password_form', array( $this, 'sek_maybe_empty_password_form' ), PHP_INT_MAX );
            add_action( 'nimble_content_restriction_for_location', array( $this, 'sek_maybe_print_restriction_stuffs' ), PHP_INT_MAX );
            // april 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/685
            // Compatibility with Members plugin : https://wordpress.org/plugins/members/
            add_filter( 'nimble_is_content_restricted', array( $this, 'sek_is_content_restricted_by_members_plugin') );
            // Compatibility with Paid Memberships Pro
            add_filter( 'nimble_is_content_restricted', array( $this, 'sek_is_content_restricted_by_paidmembershippro_plugin') );
            // Compatibility with WP Members
            add_filter( 'nimble_is_content_restricted', array( $this, 'sek_is_content_restricted_by_wp_members_plugin') );
            // Compatibility with Simple Membership plugin
            add_filter( 'nimble_is_content_restricted', array( $this, 'sek_is_content_restricted_by_simple_membership_plugin') );
            // Compatibility with premium Memberpress plugin http://www.memberpress.com/
            add_filter( 'nimble_is_content_restricted', array( $this, 'sek_is_content_restricted_by_memberpress_plugin') );
        }

        // hook : 'wp'
        // april 2020 for #673 and #679
        // Never restrict when customizing
        // @return void
        function sek_set_password_protection_status() {
            if ( skp_is_customizing() ) {
                Nimble_Manager()->is_content_restricted = false;
            } else {
                // the default restriction status is the one provided by the built-in WP password protection
                // the filter allows us to add compatibility with other membership or content restriction plugins
                Nimble_Manager()->is_content_restricted = apply_filters('nimble_is_content_restricted', is_singular() && post_password_required() );
            }

        }


        // hook : 'the_password_form'@PHP_INT_MAX documented in wp-includes/post-template.php
        // Empty the password form if it's been already rendered, either in the WP content or in a Nimble location before the content.
        // april 2020 for see #673 and #679
        // @return html output for the form
        function sek_maybe_empty_password_form( $output ) {
            // bail if there's no local Nimble section in the page
            if ( !sek_local_skope_has_nimble_sections( skp_get_skope_id() ) )
              return $output;

            if ( skp_is_customizing() || !post_password_required() )
              return $output;

            if ( is_singular() && post_password_required() ) {
                if ( !did_action('nimble_wp_pwd_form_rendered') ) {
                    // fire an action => we know the password form has been rendered so we won't have to render it several times
                    // see ::render() location
                    do_action('nimble_wp_pwd_form_rendered');
                    return $output;
                } else {
                    // Empty the form if it's been already rendered, either in the WP content or in a Nimble location before the content.
                    return '';
                }
            }
            return $output;
        }


        // hook : 'nimble_is_content_restricted'
        // Compatibility with Members plugin : https://wordpress.org/plugins/members/
        // for #685
        function sek_is_content_restricted_by_members_plugin( $bool ) {
            if ( !function_exists('members_can_current_user_view_post') || !is_singular() ) {
                return $bool;
            }
            return !members_can_current_user_view_post( get_the_ID() );
        }

        // hook : 'nimble_is_content_restricted'
        // Compatibility with Paid Membership Pro plugin
        // for #685
        function sek_is_content_restricted_by_paidmembershippro_plugin( $bool ) {
            if ( !function_exists('pmpro_has_membership_access') )
              return $bool;
            $hasaccess = pmpro_has_membership_access( NULL, NULL, true );
            if ( is_array( $hasaccess ) ){
                $hasaccess = $hasaccess[0];
            }
            return !$hasaccess;
        }

        // hook : 'nimble_is_content_restricted'
        // Compatibility with Simple WP Membership Protection plugin
        // for #685
        function sek_is_content_restricted_by_simple_membership_plugin( $bool ) {
            if ( !class_exists('\SwpmAccessControl') || !class_exists('\SwpmUtils') || !is_singular() )
              return $bool;

            $acl = \SwpmAccessControl::get_instance();
            global $post;
            if ( $acl->expired_user_has_access_to_this_page() ) {
                return false;
            }
            $content = '';
            if( \SwpmUtils::is_first_click_free($content) ) {
                return false;
            }
            if ( !method_exists($acl, 'can_i_read_post') )
              return false;
            if( $acl->can_i_read_post($post) ) {
                return false;
            }
            // Content is protected
            return true;
        }

        // hook : 'nimble_is_content_restricted'
        // Compatibility with WP Members plugin : https://wordpress.org/plugins/wp-members/
        // for #685
        function sek_is_content_restricted_by_wp_members_plugin( $bool ) {
            if ( !function_exists('wpmem_is_blocked') || !is_singular() ) {
                return $bool;
            }
            return !is_user_logged_in() && wpmem_is_blocked( get_the_ID() );
        }

        // hook : 'nimble_is_content_restricted'
        // following #685
        function sek_is_content_restricted_by_memberpress_plugin( $bool ) {
            if ( !defined('MEPR_VERSION') )
              return $bool;
            return !current_user_can('mepr-auth');
        }

        // hook : 'nimble_content_restriction_for_location'
        // april 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/685
        function sek_maybe_print_restriction_stuffs( $location_model ) {
            if ( !Nimble_Manager()->is_content_restricted )
              return;

            if ( post_password_required() ) {
                echo get_the_password_form();//<= we filter the output of this function to maybe empty and fire the action 'nimble_wp_pwd_form_rendered'
            }

            // 1) Compatibility with Members plugin : https://wordpress.org/plugins/members/
            if ( function_exists('members_can_current_user_view_post') ) {
                $post_id = get_the_ID();
                if ( !members_can_current_user_view_post( $post_id ) && function_exists('members_get_post_error_message') ) {
                    echo wp_kses_post(members_get_post_error_message( $post_id ));
                }
            // 2) for other plugins, if not printed already, print a default fitrable message
            } else if ( !did_action('nimble_after_restricted_content_html') ) {
                echo apply_filters('nimble_restricted_content_html', sprintf( '<p>%1$s</p>', __('You need to login to view this content.', 'text_doma') ) );
                do_action('nimble_after_restricted_content_html');
            }
        }
    }//class
endif;
?>