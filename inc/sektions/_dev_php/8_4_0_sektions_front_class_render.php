<?php
if ( ! class_exists( 'SEK_Front_Render' ) ) :
    class SEK_Front_Render extends SEK_Front_Assets {
        // Fired in __construct()
        function _schedule_front_rendering() {
            foreach( sek_get_locations() as $hook ) {
                switch ( $hook ) {
                    case 'loop_start' :
                    case 'loop_end' :
                        add_action( $hook, array( $this, 'sek_schedule_sektions_rendering' ) );
                    break;
                    case 'before_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), -9999 );
                    break;
                    case 'after_content' :
                        add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), 9999 );
                    break;
                }
            }

            // add_filter( 'template_include', function( $template ) {
            //       // error_log( 'TEMPLATE ? => ' . $template );
            //       // error_log( 'DID_ACTION WP => ' . did_action('wp') );
            //       return NIMBLE_BASE_PATH. "/tmpl/page-templates/full-width.php";// $template;
            // });
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

        // hook : before_content
        function sek_schedule_sektion_rendering_before_content( $html ) {
            if ( did_action( 'sek_before_location_before_content' ) )
              return $html;
            do_action( 'sek_before_location_before_content' );
            return $this -> _filter_the_content( $html, 'before_content' );
            do_action( 'sek_after_location_before_content' );
        }

        // hook : after_content
        function sek_schedule_sektion_rendering_after_content( $html ) {
            if ( did_action( 'sek_before_location_after_content' ) )
              return $html;
            do_action( 'sek_before_location_after_content' );
            return $this -> _filter_the_content( $html, 'after_content' );
            do_action( 'sek_after_location_after_content' );
        }

        private function _render_seks_for_location( $location = '' ) {
            if ( ! in_array( $location, sek_get_locations() ) ) {
                error_log( __CLASS__ . '::' . __FUNCTION__ . ' Error => the location ' . $location . ' is not registered in sek_get_locations()');
                return;
            }
            $locationSettingValue = sek_get_skoped_seks( skp_build_skope_id(), $location );
            if ( is_array( $locationSettingValue ) ) {
                // error_log( '<LEVEL MODEL IN ::sek_schedule_sektions_rendering()>');
                // error_log( print_r( $locationSettingValue, true ) );
                // error_log( '</LEVEL MODEL IN ::sek_schedule_sektions_rendering()>');
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), -9999 );
                remove_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), 9999 );

                $this->render( $locationSettingValue, $location );

                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_before_content' ), -9999 );
                add_filter('the_content', array( $this, 'sek_schedule_sektion_rendering_after_content' ), 9999 );
            } else {
                error_log( __CLASS__ . ' :: ' . __FUNCTION__ .' => sek_get_skoped_seks() should always return an array().');
            }
        }

        private function _filter_the_content( $html, $where ) {
            if ( is_singular() && in_the_loop() && is_main_query() ) {
                ob_start();
                $this->_render_seks_for_location( $where );
                return 'before_content' == $where ? ob_get_clean() . $html : $html . ob_get_clean();
            }
            return $html;
        }



        // Walk a model tree recursively and render each level with a specific template
        // Each level is described with at least 2 properties : collection and options
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
                    ?>
                      <div class="sektion-wrapper" data-sek-level="location" data-sek-id="<?php echo $id ?>">
                        <?php
                          $this -> parent_model = $model;
                          foreach ( $collection as $_key => $sec_model ) { $this -> render( $sec_model ); }
                        ?>

                         <?php if ( skp_is_customizing() && empty( $collection ) ) : //if ( skp_is_customizing() ) : ?>
                            <div class="sek-empty-location-placeholder">
                                <?php //_e( '+ Add a section', 'text_domain_to_be_replaced'); echo ' ' . $location; ?>
                            </div>
                        <?php endif; ?>
                      </div>
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
                    // error_log( '<PARENT MODEL WHEN RENDERING>');
                    // error_log( print_r( $parent_model, true ) );
                    // error_log( '</PARENT MODEL WHEN RENDERING>');

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
                                      <i data-sek-click-on="pick-module" class="material-icons sek-click-on" title="<?php _e('Add a module here', 'text_domain_to_be_replaced' ); ?>">add</i>
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

                    // Prepare the module value with the defaults
                    $default_value_model  = sek_get_default_module_model( $module_type );//walk the registered modules tree and generates the module default if not already cached
                    $model['value'] = ( ! empty( $model['value'] ) && is_array( $model['value'] ) ) ? $model['value'] : array();
                    $model['value'] = wp_parse_args( $model['value'], $default_value_model );

                    //sek_error_log( __FUNCTION__ , $model['value'] );

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


        // Utility to print the text content generated with tinyMce
        // should be wrapped in a specific selector when customizing,
        //  => so we can listen to user click actions and open the editor on for each separate tiny_mce_editor input
        function sek_print_tiny_mce_text_content( $tiny_mce_content, $input_id, $module_model ) {
            if ( empty( $tiny_mce_content ) ) {
                echo $this -> sek_get_input_placeholder_content( 'tiny_mce_editor', $input_id );
            } else {
                $content = apply_filters( 'the_content', $tiny_mce_content );
                if ( skp_is_customizing() ) {
                    printf('<div title="%3$s" data-sek-input-type="tiny_mce_editor" data-sek-input-id="%1$s">%2$s</div>', $input_id, $content, __('Click to edit', 'here') );
                } else {
                    echo $content;
                }
            }
        }
    }//class
endif;
?>