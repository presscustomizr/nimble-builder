<?php
if ( ! class_exists( 'SEK_Front_Render' ) ) :
    class SEK_Front_Render extends SEK_Front_Assets {
        // Fired in __construct()
        function _schedule_front_rendering() {
            if ( !defined( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY", 1 - PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY" ) ) { define( "NIMBLE_AFTER_CONTENT_FILTER_PRIORITY", PHP_INT_MAX ); }
            if ( !defined( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY" ) ) { define( "NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY", - PHP_INT_MAX ); }

            // SCHEDULE THE ACTIONS ON HOOKS AND CONTENT FILTERS
            foreach( sek_get_locations() as $hook ) {
                switch ( $hook ) {
                    case 'loop_start' :
                    case 'loop_end' :
                        add_action( $hook, array( $this, 'sek_schedule_sektions_rendering' ) );
                    break;
                    case 'before_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                    break;
                    case 'after_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );
                    break;
                }
            }

            add_filter( 'the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

            // SCHEDULE THE ASSETS ENQUEUING
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_the_printed_module_assets') );
            // add_filter( 'template_include', function( $template ) {
            //       // error_log( 'TEMPLATE ? => ' . $template );
            //       // error_log( 'DID_ACTION WP => ' . did_action('wp') );
            //       return NIMBLE_BASE_PATH. "/tmpl/page-templates/full-width.php";// $template;
            // });
        }

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


        // hook : loop_start, loop_end
        function sek_schedule_sektions_rendering() {
            // A location can be rendered only once
            // for loop_start and loop_end, checking with is_main_query() is not enough because the main loop might be used 2 times in the same page
            // @see issue with Twenty Seventeen here : https://github.com/presscustomizr/nimble-builder/issues/14
            // That's why we check if did_action( ... )
            if ( did_action( 'sek_before_location_' . current_filter() ) )
              return;
            do_action( 'sek_before_location_' . current_filter() );
            $this->_render_seks_for_location( current_filter() );
            do_action( 'sek_after_location_' . current_filter() );
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
            }

            return $html;
        }


        private function _render_seks_for_location( $location = '' ) {
            if ( ! in_array( $location, sek_get_locations() ) ) {
                error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location . ' is not registered in sek_get_locations()');
                return;
            }
            $locationSettingValue = sek_get_skoped_seks( skp_build_skope_id(), $location );
            if ( is_array( $locationSettingValue ) ) {

                remove_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );
                // sek_error_log( 'LEVEL MODEL IN ::sek_schedule_sektions_rendering()', $locationSettingValue);
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                $this->render( $locationSettingValue, $location );

                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ),NIMBLE_BEFORE_CONTENT_FILTER_PRIORITY );
                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), NIMBLE_AFTER_CONTENT_FILTER_PRIORITY );

                add_filter('the_content', array( $this, 'sek_wrap_wp_content' ), NIMBLE_WP_CONTENT_WRAP_FILTER_PRIORITY );

            } else {
                error_log( __CLASS__ . ' :: ' . __FUNCTION__ .' => sek_get_skoped_seks() should always return an array().');
            }
        }









        // Walk a model tree recursively and render each level with a specific template
        function render( $model = array(), $location = 'loop_start' ) {
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
            $id = $model['id'];
            $level = $model['level'];

            // Cache the parent model
            // => used when calculating the width of the column to be added
            $parent_model = $this -> parent_model;
            $this -> model = $model;

            $collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();

            switch ( $level ) {
                case 'location' :
                    //empty sektions wrapper are only printed when customizing
                    ?>
                      <?php if ( skp_is_customizing() || ( ! skp_is_customizing() && ! empty( $collection ) ) ) : ?>
                          <div class="sektion-wrapper" data-sek-level="location" data-sek-id="<?php echo $id ?>">
                            <?php
                              $this -> parent_model = $model;
                              foreach ( $collection as $_key => $sec_model ) { $this -> render( $sec_model ); }
                            ?>

                             <?php if ( empty( $collection ) ) : ?>
                                <div class="sek-empty-location-placeholder"></div>
                            <?php endif; ?>
                          </div>
                      <?php endif; ?>
                    <?php
                break;

                case 'section' :
                    $is_nested            = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
                    $column_container_class = 'sek-container-fluid';
                    //when boxed use proper container class
                    if ( ! empty( $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) {
                        $column_container_class = 'sek-container';
                    }
                    ?>
                    <?php printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section">', $id, $is_nested ? 'data-sek-is-nested="true"' : '' ); ?>
                          <div class="<?php echo $column_container_class ?>">
                            <div class="sek-row sek-sektion-inner">
                                <?php
                                  // Set the parent model now
                                  $this -> parent_model = $model;
                                  foreach ( $collection as $col_model ) {$this -> render( $col_model ); }
                                ?>
                            </div>
                          </div>
                      </div>
                    <?php
                break;

                case 'column' :
                    // if ( defined('DOING_AJAX') && DOING_AJAX ) {
                    //     error_log( print_r( $parent_model, true ) );
                    // }
                    // sek_error_log( 'PARENT MODEL WHEN RENDERING', $parent_model );

                    $col_number = ( array_key_exists( 'collection', $parent_model ) && is_array( $parent_model['collection'] ) ) ? count( $parent_model['collection'] ) : 1;
                    $col_number = 12 < $col_number ? 12 : $col_number;
                    $col_width_in_percent = 100/$col_number;

                    //TODO, we might want to be sure the $col_suffix is related to an allowed size
                    $col_suffix = floor( $col_width_in_percent );
                    ?>
                      <?php
                          printf('<div data-sek-level="column" data-sek-id="%1$s" class="sek-column sek-col-base sek-col-%2$s" %3$s>',
                              $id,
                              $col_suffix,
                              empty( $collection ) ? 'data-sek-no-modules="true"' : ''
                          );
                      ?>
                        <?php // Drop zone : if no modules, the drop zone is wrapped in sek-no-modules-columns
                        // if at least one module, the sek-drop-zone is the .sek-column-inner wrapper ?>
                        <div class="sek-column-inner <?php echo empty( $collection ) ? 'sek-empty-col' : ''; ?>">
                            <?php
                              if ( skp_is_customizing() && empty( $collection ) ) {
                                  ?>
                                  <div class="sek-no-modules-column">
                                    <div class="sek-module-drop-zone-for-first-module sek-content-module-drop-zone sek-drop-zone">
                                      <i data-sek-click-on="pick-module" class="material-icons sek-click-on" title="<?php _e('Drag and drop a module here', 'text_domain_to_be_replaced' ); ?>">add_circle_outline</i>
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
                      </div>
                    <?php
                break;

                case 'module' :
                    if ( empty( $model['module_type'] ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing module_type for a module', $model );
                        break;
                    }
                    $module_type = $model['module_type'];

                    $default_value_model  = sek_get_default_module_model( $module_type );//<= walk the registered modules tree and generates the module default if not already cached
                    $raw_module_value = ( ! empty( $model['value'] ) && is_array( $model['value'] ) ) ? $model['value'] : array();

                    // reset the model value and rewrite it normalized with the defaults
                    $model['value'] = array();
                    if ( czr_is_multi_item_module( $module_type ) ) {
                        foreach ( $raw_module_value as $item ) {
                            $model['value'][] = wp_parse_args( $item, $default_value_model );
                        }
                    } else {
                        $model['value'] = wp_parse_args( $raw_module_value, $default_value_model );
                    }

                    // update the current cached model
                    $this -> model = $model;
                    ?>
                      <div data-sek-level="module" data-sek-id="<?php echo $id; ?>" data-sek-module-type="<?php echo $module_type; ?>" class="sek-module">
                            <div class="sek-module-inner">
                              <?php $this -> sek_print_module_tmpl( $model ); ?>
                            </div>
                      </div>
                    <?php
                break;
            }

            $this -> parent_model = $parent_model;
        }//render







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
            $render_tmpl_path = sek_get_registered_module_type_property( $module_type, 'render_tmpl_path' );
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
                  $ph = skp_is_customizing() ? '<div style="padding:10px;border: 1px dotted;background:#eee">' . __('Click to edit', 'here') .'</div>' : '<i class="material-icons">short_text</i>';
                break;
                case 'upload' :
                  $ph = '<i class="material-icons">image</i>';
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
    }//class
endif;
?>