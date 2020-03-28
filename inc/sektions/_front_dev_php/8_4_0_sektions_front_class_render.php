<?php
if ( ! class_exists( 'SEK_Front_Render' ) ) :
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
        }//_schedule_front_rendering()



        // Encapsulate the singular post / page content so we can generate a dynamic ui around it when customizing
        // @filter the_content::NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY
        function sek_wrap_wp_content( $html ) {
            if ( ! skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) )
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
                            if ( ! ( apply_filters( 'infinite_scroll_got_infinity', isset( $_GET[ 'infinity' ] ) ) ) ) {
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
            if ( is_object( $query ) && is_a( $query, 'WP_Query' ) && ! $query->is_main_query() ) {
                return;
            }

            $location_id = current_filter();
            // why check if did_action( ... ) ?
            //  => A location can be rendered only once
            // => for loop_start and loop_end, checking with is_main_query() is not enough because the main loop might be used 2 times in the same page
            // => for a custom location, it can be rendered by do_action() somewhere, and be rendered also with render_nimble_locations()
            // @see issue with Twenty Seventeen here : https://github.com/presscustomizr/nimble-builder/issues/14
            if ( did_action( "sek_before_location_{$location_id}" ) )
              return;

            do_action( "sek_before_location_{$location_id}" );
            $this->_render_seks_for_location( $location_id );
            do_action( "sek_after_location_{$location_id}" );
        }

        // hook : 'the_content'::-9999
        function sek_schedule_sektion_rendering_before_content( $html ) {
            // Disable because https://github.com/presscustomizr/nimble-builder/issues/380
            // No regression ?

            // if ( did_action( 'sek_before_location_before_content' ) )
            //   return $html;

            do_action( 'sek_before_location_before_content' );
            return $this->_filter_the_content( $html, 'before_content' );
        }

        // hook : 'the_content'::9999
        function sek_schedule_sektion_rendering_after_content( $html ) {
            // Disable because https://github.com/presscustomizr/nimble-builder/issues/380
            // No regression ?

            // if ( did_action( 'sek_before_location_after_content' ) )
            //   return $html;

            do_action( 'sek_before_location_after_content' );
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
        public function _render_seks_for_location( $location_id = '', $location_data = array() ) {
            $all_locations = sek_get_locations();

            if ( ! array_key_exists( $location_id, $all_locations ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location_id . ' is not registered in sek_get_locations()');
                return;
            }
            $locationSettingValue = array();
            $is_global_location = sek_is_global_location( $location_id );
            if ( empty( $location_data ) ) {
                $skope_id = $is_global_location ? NIMBLE_GLOBAL_SKOPE_ID : skp_build_skope_id();
                $locationSettingValue = sek_get_skoped_seks( $skope_id, $location_id );
            } else {
                $locationSettingValue = $location_data;
            }
            if ( is_array( $locationSettingValue ) ) {

                remove_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );
                // sek_error_log( 'LEVEL MODEL IN ::sek_schedule_sektions_rendering()', $locationSettingValue);
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                $this->render( $locationSettingValue, $location_id );

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
        }






        /* ------------------------------------------------------------------------- *
         * RENDERING UTILITIES USED IN NIMBLE TEMPLATES
        /* ------------------------------------------------------------------------- */
        // @return void()
        // @param $locations. mixed type
        // @param $options (array)
        // Note that a location can be rendered only once in a given page.
        // That's why we need to check if did_action(''), like in ::sek_schedule_sektions_rendering
        function render_nimble_locations( $locations, $options = array() ) {
            if ( is_string( $locations ) && ! empty( $locations ) ) {
                $locations = array( $locations );
            }
            if ( ! is_array( $locations ) ) {
                sek_error_log( __FUNCTION__ . ' error => missing or invalid locations provided');
                return;
            }

            // Normalize the $options
            $options = ! is_array( $options ) ? array() : $options;
            $options = wp_parse_args( $options, array(
                // fallback_location => the location rendered even if empty.
                // This way, the user starts customizing with only one location for the content instead of four
                // But if the other locations were already customized, they will be printed.
                'fallback_location' => null, // Typically set as 'loop_start' in the nimble templates
            ));

            //$is_global = sek_is_global_location( $location_id );
            // $skope_id = skp_get_skope_id();
            // $skopeLocationCollection = array();
            // $skopeSettingValue = sek_get_skoped_seks( $skope_id );
            // if ( is_array( ) && array_key_exists('collection', search) ) {
            //     $skopeLocationCollection = $skopeSettingValue['collection'];
            // }

            //sek_error_log( __FUNCTION__ . ' sek_get_skoped_seks(  ', sek_get_skoped_seks() );

            foreach( $locations as $location_id ) {
                if ( ! is_string( $location_id ) || empty( $location_id ) ) {
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
                //sek_error_log('$locationSettingValue ??? => ' . $location_id, $locationSettingValue );
                if ( ! is_null( $options[ 'fallback_location' ]) ) {
                    // We don't need to render the locations with no sections
                    // But we need at least one location : let's always render loop_start.
                    // => so if the user switches from the nimble_template to the default theme one, the loop_start section will always be rendered.
                    if ( $options[ 'fallback_location' ] === $location_id || ( is_array( $locationSettingValue ) && ! empty( $locationSettingValue['collection'] ) ) ) {
                        do_action( "sek_before_location_{$location_id}" );
                        Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                        do_action( "sek_after_location_{$location_id}" );
                    }
                } else {
                    do_action( "sek_before_location_{$location_id}" );
                    Nimble_Manager()->_render_seks_for_location( $location_id, $locationSettingValue );
                    do_action( "sek_after_location_{$location_id}" );
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
            if ( ! is_array( $model ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a model must be an array', $model );
                return;
            }
            if ( ! array_key_exists( 'level', $model ) || ! array_key_exists( 'id', $model ) ) {
                error_log( '::render() => a level model is missing the level or the id property' );
                return;
            }
            // The level "id" is a string not empty
            $id = $model['id'];
            if ( ! is_string( $id ) || empty( $id ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => a level id must be a string not empty', $model );
                return;
            }

            // The level "level" can take 4 values : location, section, column, module
            $level_type = $model['level'];
            if ( ! is_string( $level_type ) || empty( $level_type ) ) {
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
            $custom_anchor = null;
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                    $custom_anchor = esc_attr( $model[ 'options' ][ 'anchor' ]['custom_anchor'] );
                }
            }
            $custom_css_classes = null;
            if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] ) ) {
                if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] ) ) {
                    $custom_css_classes = esc_attr( $model[ 'options' ][ 'anchor' ]['custom_css_classes'] );
                    //$custom_css_classes = preg_replace("/[^0-9a-zA-Z]/","", $custom_css_classes);
                }
            }

            switch ( $level_type ) {
                /********************************************************
                 LOCATIONS
                ********************************************************/
                case 'location' :
                    //sek_error_log( __FUNCTION__ . ' WHAT ARE WE RENDERING? ' . $id , $collection );
                    //empty sektions wrapper are only printed when customizing
                    ?>
                      <?php if ( skp_is_customizing() || ( ! skp_is_customizing() && ! empty( $collection ) ) ) : ?>
                            <?php
                              Nimble_Manager()->page_has_nimble_content = true;
                              $is_header_location = true === sek_get_registered_location_property( $id, 'is_header_location' );
                              $is_footer_location = true === sek_get_registered_location_property( $id, 'is_footer_location' );
                              printf( '<div class="sektion-wrapper" data-sek-level="location" data-sek-id="%1$s" %2$s %3$s %4$s %5$s>',
                                  $id,
                                  sprintf('data-sek-is-global-location="%1$s"', sek_is_global_location( $id ) ? 'true' : 'false'),
                                  $is_header_location ? 'data-sek-is-header-location="true"' : '',
                                  $is_footer_location ? 'data-sek-is-footer-location="true"' : '',
                                  $this->sek_maybe_print_preview_level_guid_html()//<= added for #494
                              );
                            ?>
                            <?php
                              $this->parent_model = $model;
                              foreach ( $collection as $_key => $sec_model ) { $this->render( $sec_model ); }
                            ?>
                            <?php
                              // empty global locations placeholders are only printed when customizing But not previewing a changeset post
                              // since https://github.com/presscustomizr/nimble-builder/issues/351
                            ?>
                            <?php if ( empty( $collection ) && !sek_is_customize_previewing_a_changeset_post() ) : ?>
                                <div class="sek-empty-location-placeholder">
                                  <?php
                                    if ( $is_header_location || $is_footer_location ) {
                                        printf('<span class="sek-header-footer-location-placeholder">%1$s %2$s</span>',
                                            sprintf( '<span class="sek-nimble-icon"><img src="%1$s"/></span>',
                                                NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION
                                            ),
                                            $is_header_location ? __('Start designing the header', 'text_doma') : __('Start designing the footer', 'text_doma')
                                        );
                                    }
                                  ?>
                                </div>
                            <?php endif; ?>
                          </div><?php //class="sektion-wrapper" ?>
                      <?php endif; ?>
                    <?php
                break;


                /********************************************************
                 SECTIONS
                ********************************************************/
                case 'section' :
                    $is_nested = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
                    $has_at_least_one_module = sek_section_has_modules( $collection );
                    $column_container_class = 'sek-container-fluid';
                    //when boxed use proper container class
                    if ( !empty( $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) {
                        $column_container_class = 'sek-container';
                    }
                    // if there's a video background or a parallax bg we need to inform js api
                    $bg_attributes = $this->sek_maybe_add_bg_attributes( $model );

                    // if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
                    $has_bg_img = false;
                    if ( false !== strpos( $bg_attributes, 'data-sek-src="http') ) {
                        $has_bg_img = true;
                    }
                    printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section %3$s %4$s %5$s %6$s" %7$s %8$s %9$s>%10$s',
                        $id,
                        $is_nested ? 'data-sek-is-nested="true"' : '',
                        $has_at_least_one_module ? 'sek-has-modules' : '',
                        $this->get_level_visibility_css_class( $model ),
                        $has_bg_img ? 'sek-has-bg' : '',
                        is_null( $custom_css_classes ) ? '' : $custom_css_classes,

                        is_null( $custom_anchor ) ? '' : 'id="' . ltrim( $custom_anchor , '#' ) . '"',// make sure we clean the hash if user left it
                        // add smartload + parallax attributes
                        $bg_attributes,

                        $this->sek_maybe_print_preview_level_guid_html(),//<= added for #494
                        ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? Nimble_Manager()->css_loader_html : ''
                    );
                    if ( false !== strpos($bg_attributes, 'data-sek-video-bg-src') ) {
                      ?><script>nb_.emit('nb-needs-videobg-js');</script><?php
                    }
                    if ( false !== strpos($bg_attributes, 'data-sek-bg-parallax="true"') ) {
                      ?><script>nb_.emit('nb-needs-parallax');</script><?php
                    }
                    ?>

                          <div class="<?php echo $column_container_class; ?>">
                            <div class="sek-row sek-sektion-inner">
                                <?php
                                  // Set the parent model now
                                  $this->parent_model = $model;
                                  foreach ( $collection as $col_model ) {$this->render( $col_model ); }
                                ?>
                            </div>
                          </div>
                      </div><?php //data-sek-level="section" ?>
                    <?php
                break;


                /********************************************************
                 COLUMNS
                ********************************************************/
                case 'column' :
                    // if ( defined('DOING_AJAX') && DOING_AJAX ) {
                    //     error_log( print_r( $parent_model, true ) );
                    // }
                    // sek_error_log( 'PARENT MODEL WHEN RENDERING', $parent_model );

                    // SETUP THE DEFAULT CSS CLASS
                    // Note : the css rules for custom width are generated in Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
                    $col_number = ( array_key_exists( 'collection', $parent_model ) && is_array( $parent_model['collection'] ) ) ? count( $parent_model['collection'] ) : 1;
                    $col_number = 12 < $col_number ? 12 : $col_number;
                    $col_width_in_percent = 100/$col_number;

                    //@note : we use the same logic in the customizer preview js to compute the column css classes when dragging them
                    //@see sek_preview::makeColumnsSortableInSektion
                    //TODO, we might want to be sure the $col_suffix is related to an allowed size
                    $col_suffix = floor( $col_width_in_percent );

                    // SETUP THE GLOBAL CUSTOM BREAKPOINT CSS CLASS
                    $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );

                    // SETUP THE LEVEL CUSTOM BREAKPOINT CSS CLASS
                    // nested section should inherit the custom breakpoint of the parent
                    // @fixes https://github.com/presscustomizr/nimble-builder/issues/554

                    // the 'for_responsive_columns' param has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
                    // so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
                    // when for columns, we always apply the custom breakpoint defined by the user
                    // otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
                    // 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
                    $section_custom_breakpoint =  sek_get_closest_section_custom_breakpoint( array(
                        'searched_level_id' => $parent_model['id'],
                        'for_responsive_columns' => true
                    ));

                    $grid_column_class = "sek-col-{$col_suffix}";
                    if ( $section_custom_breakpoint >= 1 ) {
                        $grid_column_class = "sek-section-custom-breakpoint-col-{$col_suffix}";
                    } else if ( $global_custom_breakpoint >= 1 ) {
                        $grid_column_class = "sek-global-custom-breakpoint-col-{$col_suffix}";
                    }
                    $bg_attributes = $this->sek_maybe_add_bg_attributes( $model );

                    // if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
                    $has_bg_img = false;
                    if ( false !== strpos( $bg_attributes, 'data-sek-src="http') ) {
                        $has_bg_img = true;
                    }
                    printf('<div data-sek-level="column" data-sek-id="%1$s" class="sek-column sek-col-base %2$s %3$s %4$s %5$s" %6$s %7$s %8$s %9$s>%10$s',
                        $id,
                        $grid_column_class,
                        $this->get_level_visibility_css_class( $model ),
                        $has_bg_img ? 'sek-has-bg' : '',
                        is_null( $custom_css_classes ) ? '' : $custom_css_classes,

                        empty( $collection ) ? 'data-sek-no-modules="true"' : '',
                        // add smartload + parallax attributes
                        $bg_attributes,
                        is_null( $custom_anchor ) ? '' : 'id="' . $custom_anchor . '"',

                        $this->sek_maybe_print_preview_level_guid_html(),//<= added for #494
                        ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? Nimble_Manager()->css_loader_html : ''
                    );
                    if ( false !== strpos($bg_attributes, 'data-sek-video-bg-src') ) {
                      ?><script>nb_.emit('nb-needs-videobg-js');</script><?php
                    }
                    if ( false !== strpos($bg_attributes, 'data-sek-bg-parallax="true"') ) {
                      ?><script>nb_.emit('nb-needs-parallax');</script><?php
                    }
                      ?>
                        <?php
                        // Drop zone : if no modules, the drop zone is wrapped in sek-no-modules-columns
                        // if at least one module, the sek-drop-zone is the .sek-column-inner wrapper
                        ?>
                        <div class="sek-column-inner <?php echo empty( $collection ) ? 'sek-empty-col' : ''; ?>">
                            <?php
                              // the drop zone is inserted when customizing but not when previewing a changeset post
                              // since https://github.com/presscustomizr/nimble-builder/issues/351
                              if ( skp_is_customizing() && !sek_is_customize_previewing_a_changeset_post() && empty( $collection ) ) {
                                  //$content_type = 1 === $col_number ? 'section' : 'module';
                                  $content_type = 'module';
                                  $title = 'section' === $content_type ? __('Drag and drop a section or a module here', 'text_doma' ) : __('Drag and drop a block of content here', 'text_doma' );
                                  ?>
                                  <div class="sek-no-modules-column">
                                    <div class="sek-module-drop-zone-for-first-module sek-content-module-drop-zone sek-drop-zone">
                                      <i data-sek-click-on="pick-content" data-sek-content-type="<?php echo $content_type; ?>" class="material-icons sek-click-on" title="<?php echo $title; ?>">add_circle_outline</i>
                                      <span class="sek-injection-instructions"><?php _e('Drag and drop or double-click the content that you want to insert here.', 'text_domain_to_rep'); ?></span>
                                    </div>
                                  </div>
                                  <?php
                              } else {
                                  // Set the parent model now
                                  $this->parent_model = $model;
                                  foreach ( $collection as $module_or_nested_section_model ) {
                                      ?>
                                      <?php
                                      $this->render( $module_or_nested_section_model );
                                  }
                                  ?>
                                  <?php
                              }
                            ?>
                        </div>
                      </div><?php //data-sek-level="column" ?>
                    <?php
                break;


                /********************************************************
                 MODULES
                ********************************************************/
                case 'module' :
                    if ( empty( $model['module_type'] ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing module_type for a module', $model );
                        break;
                    }

                    $module_type = $model['module_type'];

                    if ( ! CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => module_type not registered', $module_type );
                        break;
                    }

                    $model = sek_normalize_module_value_with_defaults( $model );
                    // update the current cached model
                    $this->model = $model;
                    $title_attribute = '';
                    if ( skp_is_customizing() ) {
                        $title_attribute = __('Edit module settings', 'text-domain');
                        $title_attribute = 'title="'.$title_attribute.'"';
                    }

                    // SETUP MODULE TEMPLATE PATH
                    // introduced for #532, october 2019
                    // Default tmpl path looks like : NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
                    //
                    // Important note :
                    // @fixes https://github.com/presscustomizr/nimble-builder/issues/537
                    // since #532, module registered in Nimble Builder core have a render_tmpl_path property looking like 'render_tmpl_path' => "simple_html_module_tmpl.php",
                    // But if a developer wants to register a custom module with a specific template path, it is still possible by using a full path
                    // 1) We first check if the file exists, if it is a full path this will return TRUE and the render tmpl path will be set this way
                    // , for example, we use a custom gif module on presscustomizr.com, for which the render_tmpl_path is a full path:
                    // 'render_tmpl_path' => TC_BASE_CHILD . "inc/nimble-modules/modules-registration/tmpl/modules/gif_image_module_tmpl.php",
                    // 2) then we check if there's an override
                    // 3) finally we use the default Nimble Builder path

                    // render_tmpl_path can be
                    // 1) simple_html_module_tmpl.php <= most common case, the module is registered by Nimble Builder
                    // 2) srv/www/pc-dev/htdocs/wp-content/themes/tc/inc/nimble-modules/modules-registration/tmpl/modules/gif_image_module_tmpl.php <= case of a custom module
                    $template_name_or_path = sek_get_registered_module_type_property( $module_type, 'render_tmpl_path' );

                    $template_name = basename( $template_name_or_path );
                    $template_name = ltrim( $template_name_or_path, '/' );

                    if ( file_exists( $template_name_or_path ) ) {
                        $template_path = $template_name_or_path;
                    } else {
                        $template_path = sek_get_templates_dir() . "/modules/{$template_name}";
                    }

                    // make this filtrable
                    $render_tmpl_path = apply_filters( 'nimble_module_tmpl_path', $template_path, $module_type );

                    // Then check if there's an override
                    $overriden_template_path = $this->sek_maybe_get_overriden_template_path_for_module( $template_name );

                    $is_module_template_overriden = false;
                    if ( !empty( $overriden_template_path ) ) {
                        $render_tmpl_path = $overriden_template_path;
                        $is_module_template_overriden = true;
                    }
                    // if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
                    $bg_attributes = $this->sek_maybe_add_bg_attributes( $model );
                    $has_bg_img = false;
                    if ( false !== strpos( $bg_attributes, 'data-sek-src="http') ) {
                        $has_bg_img = true;
                    }
                    if ( false !== strpos($bg_attributes, 'data-sek-bg-parallax="true"') ) {
                      ?><script>nb_.emit('nb-needs-parallax');</script><?php
                    }

                    printf('<div data-sek-level="module" data-sek-id="%1$s" data-sek-module-type="%2$s" class="sek-module %3$s %4$s" %5$s %6$s %7$s %8$s %9$s %10$s>%11$s',
                        $id,
                        $module_type,
                        $this->get_level_visibility_css_class( $model ),
                        $has_bg_img ? 'sek-has-bg' : '',
                        is_null( $custom_css_classes ) ? '' : $custom_css_classes,

                        $title_attribute,
                        // add smartload + parallax attributes
                        $bg_attributes,
                        is_null( $custom_anchor ) ? '' : 'id="' . $custom_anchor . '"',

                        $this->sek_maybe_print_preview_level_guid_html(), //<= added for #494
                        $is_module_template_overriden ? 'data-sek-module-template-overriden="true"': '',// <= added for #532
                        ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? Nimble_Manager()->css_loader_html : ''
                    );
                      ?>
                        <div class="sek-module-inner">
                          <?php
                            if ( !empty( $render_tmpl_path ) && file_exists( $render_tmpl_path ) ) {
                                load_template( $render_tmpl_path, false );
                            } else {
                                error_log( __FUNCTION__ . ' => no template found for module type ' . $module_type  );
                            }
                          ?>
                        </div>
                    </div><?php //data-sek-level="module" ?>
                    <?php
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
        private function get_level_visibility_css_class( $model ) {
            if ( ! is_array( $model ) ) {
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
        private function sek_maybe_get_overriden_template_path_for_module( $template_name = '') {
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
            if ( ! $post = get_post( $id ) )
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

            if ( ! is_array( $skoped_seks ) || empty( $skoped_seks['collection'] ) )
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
                if ( array_key_exists( 'level', $level_data ) && 'module' === $level_data['level'] && ! empty( $level_data['module_type'] ) ) {
                    $front_assets = sek_get_registered_module_type_property( $level_data['module_type'], 'front_assets' );
                    if ( is_array( $front_assets ) ) {
                        foreach ( $front_assets as $handle => $asset_params ) {
                            if ( is_string( $handle ) && ! array_key_exists( $handle, $enqueuing_candidates ) ) {
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
        // @return string
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
                $bg_options = ( ! empty( $model[ 'options' ][ 'bg' ] ) && is_array( $model[ 'options' ][ 'bg' ] ) ) ? $model[ 'options' ][ 'bg' ] : array();
                if ( !empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
                    $new_attributes[] = 'data-sek-has-bg="true"';
                    $bg_img_url = wp_get_attachment_url( $bg_options[ 'bg-image'] );
                    // When the fixed background is ckecked, it wins against parallax
                    $fixed_bg_enabled = !empty( $bg_options['bg-attachment'] ) && sek_booleanize_checkbox_val( $bg_options['bg-attachment'] );
                    $parallax_enabled = !$fixed_bg_enabled && !empty( $bg_options['bg-parallax'] ) && sek_booleanize_checkbox_val( $bg_options['bg-parallax'] );
                    if ( $parallax_enabled ) {
                        $image = wp_get_attachment_image_src( $bg_options[ 'bg-image'], 'full' );
                        if ( $image ) {
                            list( $src, $width, $height ) = $image;
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

            if ( !empty( $bg_img_url ) ) {
                $new_attributes[] = sprintf( 'data-sek-src="%1$s"', $bg_img_url );
            }
            if ( sek_is_img_smartload_enabled() ) {
                $new_attributes[] = sprintf( 'data-sek-lazy-bg="true"' );
            }
            // data-sek-bg-fixed attribute has been added for https://github.com/presscustomizr/nimble-builder/issues/414
            // @see css rules related
            // we can't have both fixed and parallax option together
            // when the fixed background is ckecked, it wins against parallax
            if ( $fixed_bg_enabled ) {
                $new_attributes[] = 'data-sek-bg-fixed="true"';
            } else if ( $parallax_enabled ) {
                $new_attributes[] = sprintf('data-sek-bg-parallax="true" data-bg-width="%1$s" data-bg-height="%2$s" data-sek-parallax-force="%3$s"',
                    $width,
                    $height,
                    array_key_exists('bg-parallax-force', $bg_options) ? $bg_options['bg-parallax-force'] : '40'
                    //!empty( $bg_options['bg-parallax-force'] ) ? $bg_options['bg-parallax-force'] : '40'
                );
            }

            // video background insertion can only be done for sections and columns
            if ( in_array( $level_type, array( 'section', 'column') ) ) {
                if ( !empty( $video_bg_url ) && is_string( $video_bg_url ) ) {
                    $new_attributes[] = sprintf('data-sek-video-bg-src="%1$s"', $video_bg_url );
                    $new_attributes[] = sprintf('data-sek-video-bg-loop="%1$s"', $video_bg_loop ? 'true' : 'false' );
                    if ( !is_null( $video_bg_delay_before_start ) && $video_bg_delay_before_start >= 0 ) {
                        $new_attributes[] = sprintf('data-sek-video-delay-before="%1$s"', $video_bg_delay_before_start );
                    }
                    $new_attributes[] = sprintf('data-sek-video-bg-on-mobile="%1$s"', $video_bg_on_mobile ? 'true' : 'false' );
                    if ( !is_null( $video_bg_start_time ) && $video_bg_start_time >= 0 ) {
                        $new_attributes[] = sprintf('data-sek-video-start-at="%1$s"', $video_bg_start_time );
                    }
                    if ( !is_null( $video_bg_end_time ) && $video_bg_end_time >= 0 ) {
                        $new_attributes[] = sprintf('data-sek-video-end-at="%1$s"', $video_bg_end_time );
                    }
                }
            }
            return implode( ' ', $new_attributes );
        }


        // @filter nimble_parse_for_smart_load
        // this filter is used in several modules : tiny_mce_editor, image module, post grid
        // img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
        // deactivated when customizing @see function sek_is_img_smartload_enabled()
        // @return html string
        function sek_maybe_process_img_for_js_smart_load( $html ) {
            // if ( skp_is_customizing() || !sek_is_img_smartload_enabled() )
            //   return $html;
            if ( !sek_is_img_smartload_enabled() )
              return $html;
            if ( ! is_string( $html ) ) {
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

            if ( empty( $allowed_image_extensions ) || ! is_array( $allowed_image_extensions ) ) {
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
            add_filter( 'the_nimble_tinymce_module_content', 'prepend_attachment' );
            add_filter( 'the_nimble_tinymce_module_content', 'wp_make_content_images_responsive' );
            add_filter( 'the_nimble_tinymce_module_content', 'do_shortcode', 11 ); // AFTER wpautop()
            add_filter( 'the_nimble_tinymce_module_content', 'capital_P_dangit', 9 );
            add_filter( 'the_nimble_tinymce_module_content', '\Nimble\sek_parse_template_tags', 21 );

            // Hack to get the [embed] shortcode to run before wpautop()
            // fixes Video Embed not showing when using Add Media > Insert from Url
            // @see https://github.com/presscustomizr/nimble-builder/issues/250
            // @see wp-includes/class-wp-embed.php
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_run_shortcode' ), 8 );

            // @see filters in wp-includes/class-wp-embed.php
            add_filter( 'the_nimble_tinymce_module_content', array( $this, 'sek_parse_content_for_video_embed') , 8 );
        }

         // fired @filter the_nimble_tinymce_module_content
        function sek_run_shortcode( $content ) {
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
            //sek_error_log(' SOO ?? sek_get_skoped_seks( skp_get_skope_id() ) ' . skp_get_skope_id(), sek_get_skoped_seks( skp_get_skope_id() ) );
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
                //sek_error_log(' SOO ?? sek_get_skoped_seks( skp_get_skope_id() ) ' . skp_get_skope_id(), sek_get_skoped_seks( skp_get_skope_id() ) );
                $local_header_footer_data = sek_get_local_option_value('local_header_footer');
                $global_header_footer_data = sek_get_global_option_value('global_header_footer');

                $apply_local_option = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data ) && 'inherit' !== $local_header_footer_data['header-footer'];

                $this->has_global_header_footer = !is_null( $global_header_footer_data ) && is_array( $global_header_footer_data ) && !empty( $global_header_footer_data['header-footer'] ) && 'nimble_global' === $global_header_footer_data['header-footer'];

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
        //
        // partially inspired by https://stackoverflow.com/questions/24195818/add-results-into-wordpress-search-results
        function sek_maybe_include_nimble_content_in_search_results(){
            if ( !is_search() )
              return;
            global $wp_query;

            $query_vars = $wp_query->query_vars;
            if ( ! is_array( $query_vars ) || empty( $query_vars['s'] ) )
              return;

            // Search query on Nimble CPT
            $sek_post_query_vars = array(
                'post_type'              => NIMBLE_CPT,
                'post_status'            => get_post_stati(),
                'posts_per_page'         => -1,
                'no_found_rows'          => true,
                'cache_results'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'lazy_load_term_meta'    => false,
                's' => $query_vars['s']
            );
            $query = new \WP_Query( $sek_post_query_vars );

            // The search string has been found in a set of Nimble posts
            if ( is_array( $query->posts ) ) {
                foreach ( $query->posts as $post_object ) {
                    // The related WP object ( == skope ) is written in the title of Nimble CPT
                    // ex : nimble___skp__post_post_114
                    if ( preg_match('(post_page|post_post)', $post_object->post_title ) ) {
                        $post_number = preg_replace('/[^0-9]/', '', $post_object->post_title );
                        $post_number = intval($post_number);

                        $post_candidate = get_post( $post_number );

                        if ( is_object( $post_candidate ) ) {
                            // Merge Nimble posts to WP posts
                            array_push( $wp_query->posts, $post_candidate );
                        }
                    }
                }
            }

            // Maybe clean duplicated posts
            $maybe_includes_duplicated = $wp_query->posts;
            $without_duplicated = array();
            $post_ids = array();

            foreach ( $maybe_includes_duplicated as $post_obj ) {
                if ( in_array( $post_obj->ID, $post_ids ) )
                  continue;
                $post_ids[] = $post_obj->ID;
                $without_duplicated[] = $post_obj;
            }
            $wp_query->posts = $without_duplicated;

            // Make sure the post_count and found_posts are updated
            $wp_query->post_count = count($wp_query->posts);
            $wp_query->found_posts = $wp_query->post_count;
        }// sek_maybe_include_nimble_content_in_search_results


        // @return html string
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
        function sek_maybe_print_preview_level_guid_html() {
              if ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
                  return sprintf( 'data-sek-preview-level-guid="%1$s"', $this->sek_get_preview_level_guid() );
              }
              return '';
        }

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
                      $this->preview_level_guid = $_POST['preview-level-guid'];
                  } else {
                      $this->preview_level_guid = sprintf('%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) );
                  }

              }
              return $this->preview_level_guid;
        }
    }//class
endif;
?>