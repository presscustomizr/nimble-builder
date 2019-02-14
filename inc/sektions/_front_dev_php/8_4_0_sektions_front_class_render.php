<?php
if ( ! class_exists( 'SEK_Front_Render' ) ) :
    class SEK_Front_Render extends SEK_Front_Assets {
        // Fired in __construct()
        function _schedule_front_rendering() {
            if ( !defined( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY", 1 - PHP_INT_MAX ); }
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
            $this -> sek_setup_tiny_mce_content_filters();

            // REGISTER HEADER AND FOOTER GLOBAL LOCATIONS
            add_action( 'nimble_front_classes_ready', array( $this, 'sek_register_nimble_global_locations') );

            // CONTENT : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
            add_filter( 'template_include', array( $this, 'sek_maybe_set_local_nimble_template' ) );

            // HEADER FOOTER
            if ( sek_is_header_footer_enabled() ) {
                add_action( 'template_redirect', array( $this, 'sek_maybe_set_nimble_header_footer' ) );
                // HEADER : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
                add_filter( 'get_header', array( $this, 'sek_maybe_set_local_nimble_header') );
                // FOOTER : USE THE DEFAULT WP TEMPLATE OR A CUSTOM NIMBLE ONE
                add_filter( 'get_footer', array( $this, 'sek_maybe_set_local_nimble_footer') );
            }
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



        // hook : loop_start, loop_end, and all custom locations
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
            if ( did_action( 'sek_before_location_before_content' ) )
              return $html;

            do_action( 'sek_before_location_before_content' );
            return $this -> _filter_the_content( $html, 'before_content' );
        }

        // hook : 'the_content'::9999
        function sek_schedule_sektion_rendering_after_content( $html ) {
            if ( did_action( 'sek_before_location_after_content' ) )
              return $html;

            do_action( 'sek_before_location_after_content' );
            return $this -> _filter_the_content( $html, 'after_content' );
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
            if ( empty( $location_data ) ) {
                $skope_id = sek_is_global_location( $location_id )  ? NIMBLE_GLOBAL_SKOPE_ID : skp_build_skope_id();
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

            // sek_error_log( __FUNCTION__ . ' $locationSettingValue ', $locationSettingValue );

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
            $parent_model = $this -> parent_model;
            $this -> model = $model;

            $collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();

            //sek_error_log( __FUNCTION__ . ' WHAT ARE WE RENDERING? ' . $id, current_filter() . ' | ' . current_action() );

            switch ( $level_type ) {
                case 'location' :
                    //sek_error_log( __FUNCTION__ . ' WHAT ARE WE RENDERING? ' . $id , $collection );
                    //empty sektions wrapper are only printed when customizing
                    ?>
                      <?php if ( skp_is_customizing() || ( ! skp_is_customizing() && ! empty( $collection ) ) ) : ?>
                            <?php
                              $is_header_location = true === sek_get_registered_location_property( $id, 'is_header_location' );
                              $is_footer_location = true === sek_get_registered_location_property( $id, 'is_footer_location' );
                              printf( '<div class="sektion-wrapper" data-sek-level="location" data-sek-id="%1$s" %2$s %3$s %4$s>',
                                  $id,
                                  sprintf('data-sek-is-global-location="%1$s"', sek_is_global_location( $id ) ? 'true' : 'false'),
                                  $is_header_location ? 'data-sek-is-header-location="true"' : '',
                                  $is_footer_location ? 'data-sek-is-footer-location="true"' : ''
                              );
                            ?>
                            <?php
                              $this -> parent_model = $model;
                              foreach ( $collection as $_key => $sec_model ) { $this -> render( $sec_model ); }
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
                                            $is_header_location ? __('Start designing the header', 'text_domain_to_be_replaced') : __('Start designing the footer', 'text_domain_to_be_replaced')
                                        );
                                    }
                                  ?>
                                </div>
                            <?php endif; ?>
                          </div><?php //class="sektion-wrapper" ?>
                      <?php endif; ?>
                    <?php
                break;

                case 'section' :
                    $is_nested = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
                    $has_at_least_one_module = sek_section_has_modules( $collection );
                    $column_container_class = 'sek-container-fluid';
                    //when boxed use proper container class
                    if ( !empty( $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) {
                        $column_container_class = 'sek-container';
                    }
                    $custom_anchor = null;
                    if ( !empty( $model[ 'options' ] ) && !empty( $model[ 'options' ][ 'anchor' ] ) && !empty( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                        if ( is_string( $model[ 'options' ][ 'anchor' ]['custom_anchor'] ) ) {
                            $custom_anchor = $model[ 'options' ][ 'anchor' ]['custom_anchor'];
                        }
                    }

                    ?>
                    <?php printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section %3$s %4$s" %5$s %6$s>',
                        $id,
                        $is_nested ? 'data-sek-is-nested="true"' : '',
                        $has_at_least_one_module ? 'sek-has-modules' : '',
                        $this->get_level_visibility_css_class( $model ),
                        is_null( $custom_anchor ) ? '' : 'id="' . $custom_anchor . '"',
                        // add smartload + parallax attributes
                        $this -> sek_maybe_add_bg_attributes( $model )
                    ); ?>
                          <div class="<?php echo $column_container_class; ?>">
                            <div class="sek-row sek-sektion-inner">
                                <?php
                                  // Set the parent model now
                                  $this -> parent_model = $model;
                                  foreach ( $collection as $col_model ) {$this -> render( $col_model ); }
                                ?>
                            </div>
                          </div>
                      </div><?php //data-sek-level="section" ?>
                    <?php
                break;

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
                    $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;

                    // SETUP THE LEVEL CUSTOM BREAKPOINT CSS CLASS
                    $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( $parent_model ) );
                    $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

                    $grid_column_class = "sek-col-{$col_suffix}";
                    if ( $has_section_custom_breakpoint ) {
                        $grid_column_class = "sek-section-custom-breakpoint-col-{$col_suffix}";
                    } else if ( $has_global_custom_breakpoint ) {
                        $grid_column_class = "sek-global-custom-breakpoint-col-{$col_suffix}";
                    }
                    ?>
                      <?php
                          printf('<div data-sek-level="column" data-sek-id="%1$s" class="sek-column sek-col-base %2$s %3$s" %4$s %5$s>',
                              $id,
                              $grid_column_class,
                              $this->get_level_visibility_css_class( $model ),
                              empty( $collection ) ? 'data-sek-no-modules="true"' : '',
                              // add smartload + parallax attributes
                              $this -> sek_maybe_add_bg_attributes( $model )
                          );
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
                                  $title = 'section' === $content_type ? __('Drag and drop a section or a module here', 'text_domain_to_be_replaced' ) : __('Drag and drop a block of content here', 'text_domain_to_be_replaced' );
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
                                  $this -> parent_model = $model;
                                  foreach ( $collection as $module_or_nested_section_model ) {
                                      ?>
                                      <?php
                                      $this -> render( $module_or_nested_section_model );
                                  }
                                  ?>
                                  <?php
                              }
                            ?>
                        </div>
                      </div><?php //data-sek-level="column" ?>
                    <?php
                break;

                case 'module' :
                    if ( empty( $model['module_type'] ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing module_type for a module', $model );
                        break;
                    }
                    $module_type = $model['module_type'];
                    $model = sek_normalize_module_value_with_defaults( $model );
                    // update the current cached model
                    $this -> model = $model;
                    $title_attribute = '';
                    if ( skp_is_customizing() ) {
                        $title_attribute = __('Edit module settings', 'text-domain');
                        $title_attribute = 'title="'.$title_attribute.'"';
                    }
                    ?>
                      <?php printf('<div data-sek-level="module" data-sek-id="%1$s" data-sek-module-type="%2$s" class="sek-module %3$s" %4$s %5$s>',
                          $id,
                          $module_type,
                          $this->get_level_visibility_css_class( $model ),
                          $title_attribute,
                          // add smartload + parallax attributes
                          $this -> sek_maybe_add_bg_attributes( $model )
                        );?>
                            <div class="sek-module-inner">
                              <?php $this -> sek_print_module_tmpl( $model ); ?>
                            </div>
                      </div><?php //data-sek-level="module" ?>
                    <?php
                break;

                default :
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' error => a level is invalid : ' . $level_type  );
                break;
            }

            $this -> parent_model = $parent_model;
        }//render









        /* ------------------------------------------------------------------------- *
         * VARIOUS HELPERS
        /* ------------------------------------------------------------------------- */
        /* HELPER TO PRINT THE VISIBILITY CSS CLASS IN THE LEVEL CONTAINER */
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
        // Fires the render callback of the module
        // The placeholder(s) rendering is delegated to each module template
        private function sek_print_module_tmpl( $model ) {
            if ( ! is_array( $model ) ) {
                error_log( __FUNCTION__ . ' => $model param should be an array' );
                return;
            }
            if ( ! array_key_exists( 'module_type', $model ) ) {
                error_log( __FUNCTION__ . ' => a module type must be provided' );
                return;
            }
            $module_type = $model['module_type'];
            $render_tmpl_path = apply_filters( 'nimble_module_tmpl_path', sek_get_registered_module_type_property( $module_type, 'render_tmpl_path' ), $module_type );
            if ( !empty( $render_tmpl_path ) ) {
                load_template( $render_tmpl_path, false );
            } else {
                error_log( __FUNCTION__ . ' => no template found for module type ' . $module_type  );
            }

            //$placeholder_icon = sek_get_registered_module_type_property( $module_type, 'placeholder_icon' );

            // if ( is_string( $render_callback ) && function_exists( $render_callback ) ) {
            //     call_user_func_array( $render_callback, array( $model ) );
            // } else {
            //     error_log( __FUNCTION__ . ' => not render_callback defined for ' . $model['module_type'] );
            //     return;
            // }

        }


        function sek_get_input_placeholder_content( $input_type = '', $input_id = '' ) {
            $ph = '<i class="material-icons">pan_tool</i>';
            switch( $input_type ) {
                case 'tiny_mce_editor' :
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
                        $enqueuing_candidates = $this -> sek_sniff_assets_to_enqueue( $level_data['collection'], $enqueuing_candidates );
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
            $attributes = '';
            $bg_url = '';
            $parallax_enabled = false;
            $width = '';
            $height = '';

            if ( !empty( $model[ 'options' ] ) && is_array( $model['options'] ) ) {
                $bg_options = ( ! empty( $model[ 'options' ][ 'bg' ] ) && is_array( $model[ 'options' ][ 'bg' ] ) ) ? $model[ 'options' ][ 'bg' ] : array();
                if ( ! empty( $bg_options[ 'bg-image'] ) && is_numeric( $bg_options[ 'bg-image'] ) ) {
                    if ( sek_is_img_smartload_enabled() ) {
                        $bg_url = wp_get_attachment_url( $bg_options[ 'bg-image'] );
                    }
                    $parallax_enabled = !empty( $bg_options['bg-parallax'] ) && sek_booleanize_checkbox_val( $bg_options['bg-parallax'] );
                    if ( $parallax_enabled ) {
                        $image = wp_get_attachment_image_src( $bg_options[ 'bg-image'], 'full' );
                        if ( $image ) {
                            list( $src, $width, $height ) = $image;
                        }
                    }
                }
            }

            if ( ! empty( $bg_url ) ) {
                $attributes = sprintf('data-sek-lazy-bg="true" data-sek-src="%1$s"', $bg_url );
            }
            if ( $parallax_enabled ) {
                $attributes .= sprintf('%1$s data-sek-bg-parallax="true" data-bg-width="%2$s" data-bg-height="%3$s"', $attributes, $width, $height );
            }
            return $attributes;
        }


        // @filter nimble_parse_for_smart_load
        // this filter is used in several modules : tiny_mce_editor, image module, ...
        // img smartload can be set globally with 'global-img-smart-load' and locally with 'local-img-smart-load'
        // deactivated when customizing @see function sek_is_img_smartload_enabled()
        // @return html string
        function sek_maybe_process_img_for_js_smart_load( $html ) {
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
            $pattern                = '#<img([^>]+?)src=[\'"]?([^\'"\s>]+\.'.$img_extensions_pattern.'[^\'"\s>]*)[\'"]?([^>]*)>#i';

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
        // @return void()
        // set the value of the properties
        // has_local_header_footer
        // has_global_header_footer
        function sek_maybe_set_nimble_header_footer() {
            //sek_error_log(' SOO ?? sek_get_skoped_seks( skp_get_skope_id() ) ' . skp_get_skope_id(), sek_get_skoped_seks( skp_get_skope_id() ) );
            $local_header_footer_data = sek_get_local_option_value('local_header_footer');
            $global_header_footer_data = sek_get_global_option_value('global_header_footer');
            $apply_local_option = !is_null( $local_header_footer_data ) && 'inherit' !== $local_header_footer_data['header-footer'];

            $this->has_global_header_footer = !is_null( $global_header_footer_data ) && is_array( $global_header_footer_data ) && !empty( $global_header_footer_data['header-footer'] ) && 'nimble_global' === $global_header_footer_data['header-footer'];

            if ( $apply_local_option ) {
                $this->has_local_header_footer = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data['header-footer'] ) && 'nimble_local' === $local_header_footer_data['header-footer'];
                $this->has_global_header_footer = !is_null( $local_header_footer_data ) && is_array( $local_header_footer_data ) && !empty( $local_header_footer_data['header-footer'] ) && 'nimble_global' === $local_header_footer_data['header-footer'];
            }
        }



        // fired @filter get_header()
        function sek_maybe_set_local_nimble_header( $header_name ) {
            // if Nimble_Manager()->has_local_header_footer || Nimble_Manager()->has_global_header_footer
            if ( sek_page_uses_nimble_header_footer() ) {
                // load the Nimble template which includes a call to wp_head()
                load_template( NIMBLE_BASE_PATH . '/tmpl/header/nimble_header_tmpl.php', false );

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
        function sek_maybe_set_local_nimble_footer( $footer_name ) {
            // if Nimble_Manager()->has_local_header_footer || Nimble_Manager()->has_global_header_footer
            if ( sek_page_uses_nimble_header_footer() ) {
                // load the Nimble template which includes a call to wp_footer()
                load_template( NIMBLE_BASE_PATH . '/tmpl/footer/nimble_footer_tmpl.php', false );

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

    }//class
endif;
?>