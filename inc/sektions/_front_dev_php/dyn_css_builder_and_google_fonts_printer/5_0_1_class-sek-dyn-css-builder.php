<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 *  Sek Dyn CSS Builder: class responsible for building Stylesheet from a sek model
 */
class Sek_Dyn_CSS_Builder {

    /*min widths, considering CSS min widths BP:
    $grid-breakpoints: (
        xs: 0,
        sm: 576px,
        md: 768px,
        lg: 992px,
        xl: 1200px
    )

    we could have a constant array since php 5.6
    */
    public static $breakpoints = [
        'xs' => 0,
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
        'xl' => 1200
    ];

    const COLS_MOBILE_BREAKPOINT  = 'md';

    private $collection;//the collection of css rules
    private $sek_model;
    // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
    private $is_global_stylesheet;
    private $parent_level_model = array();

    public function __construct( $sek_model = array(), $is_global_stylesheet = false ) {
        $this->sek_model  = $sek_model;
        $this->is_global_stylesheet = $is_global_stylesheet;
        // set the css rules for columns
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        // filter fired in sek_css_rules_sniffer_walker()
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

        //sek_error_log('FIRING THE CSS BUILDER');

        $this->sek_css_rules_sniffer_walker();
    }












    // Fired in the constructor
    // Walk the level tree and build rules when needed
    // The rules are filtered when some conditions are met.
    // This allows us to schedule the css rules addition remotely :
    // - from the module registration php file
    // - from the generic input types ( @see sek_add_css_rules_for_generic_css_input_types() )
    public function sek_css_rules_sniffer_walker( $level = null, $parent_level = array() ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        // The parent level is set when the function is invoked recursively, from a level where we actually have a 'level' property
        if ( ! empty( $parent_level ) ) {
            $this->parent_level_model = $parent_level;
        }

        foreach ( $level as $key => $entry ) {
            $rules = array();

            // INPUT CSS RULES <= used in front modules only
            // When we are inside the associative arrays of
            // - the module 'value'
            // - or the level 'options' entries <= NOT ANYMORE
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( is_string( $key ) && 1 < strlen( $key ) ) {
                // we need to have a level model set
                if ( !empty( $parent_level ) && is_array( $parent_level ) && !empty( $parent_level['module_type'] ) ) {
                     // If the current level is a module, check if the module has generic css ( *_css suffixed ) selectors specified on registration
                    // $module_level_css_selectors = null;
                    // $registered_input_list = null;
                    $module_level_css_selectors = sek_get_registered_module_type_property( $parent_level['module_type'], 'css_selectors' );

                    $registered_input_list = sek_get_registered_module_input_list( $parent_level['module_type'] );
                    if ( 'value' === $key && is_array( $entry ) ) {
                          $is_father = sek_get_registered_module_type_property( $parent_level['module_type'], 'is_father' );
                          $father_mod_type = $parent_level['module_type'];
                          // If the module has children ( the module is_father ), let's loop on each option group
                          if ( $is_father ) {
                              $children = sek_get_registered_module_type_property( $father_mod_type, 'children' );
                              // Loop on the children
                              foreach ( $entry as $opt_group_type => $input_candidates ) {
                                  if ( ! is_array( $children ) ) {
                                      sek_error_log( 'Father module ' . $father_mod_type . ' has invalid children');
                                      continue;
                                  }
                                  if ( empty( $children[$opt_group_type] ) ) {
                                      sek_error_log( 'Father module ' . $father_mod_type . ' has a invalid child for option group : '. $opt_group_type);
                                      continue;
                                  }
                                  // The module type of the currently looped child
                                  $child_mod_type = $children[ $opt_group_type ];

                                  // If the child module has no css_selectors set, we fallback on the father css_selector
                                  $child_css_selector = sek_get_registered_module_type_property( $child_mod_type, 'css_selectors' );
                                  $child_css_selector = empty( $child_css_selector ) ? $module_level_css_selectors : $child_css_selector;

                                  // Is is a multi-item module ?
                                  $is_multi_items_module = true === sek_get_registered_module_type_property( $child_mod_type, 'is_crud' );

                                  if ( $is_multi_items_module ) {
                                      foreach ( $input_candidates as $item_input_list ) {
                                          $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                              'input_list' => $item_input_list,
                                              'registered_input_list' => $registered_input_list[ $opt_group_type ],// <= the full list of input for the module
                                              'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                              'module_css_selector' => $child_css_selector, //a default set of css_se
                                              'is_multi_items' => true
                                          ) );

                                          $rules = apply_filters( "sek_add_css_rules_for_single_item_in_module_type___{$child_mod_type}", $rules, array(
                                              'input_list' => wp_parse_args( $item_input_list, sek_get_default_module_model( $child_mod_type ) ),
                                              'parent_module_type' => $child_mod_type,// 'registered_input_list' => $registered_input_list[ $opt_group_type ],// <= the full list of input for the module
                                              'parent_module_id' => $parent_level['id'],// <= the parent module level id, used to increase the CSS specificity
                                              'module_css_selector' => $child_css_selector //a default set of css_se
                                          ) );
                                      }
                                  } else {
                                      $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                          'input_list' => $input_candidates,
                                          'registered_input_list' => $registered_input_list[ $opt_group_type ],// <= the full list of input for the module
                                          'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                          'module_css_selector' => $child_css_selector //a default set of css_selectors might have been specified on module registration
                                      ));
                                  }
                              }//foreach
                          } //if ( $is_father )
                          else {
                              // Is is a multi-item module ?
                              $is_multi_items_module = true === sek_get_registered_module_type_property( $father_mod_type, 'is_crud' );

                              if ( $is_multi_items_module ) {
                                  foreach ( $entry as $item_input_list ) {
                                      $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                          'input_list' => $item_input_list,
                                          'registered_input_list' => $registered_input_list,// <= the full list of input for the module
                                          'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                          'module_css_selector' => $module_level_css_selectors, //a default set of css_se
                                          'is_multi_items' => true
                                      ) );

                                      $rules = apply_filters( "sek_add_css_rules_for_multi_item_module_type___{$father_mod_type}", $rules, array(
                                          'input_list' => wp_parse_args( $item_input_list, sek_get_default_module_model( $father_mod_type ) ),
                                          'parent_module_type' => $father_mod_type,// <= the full list of input for the module
                                          'parent_module_id' => $parent_level['id'],// <= the parent module level id, used to increase the CSS specificity
                                          'module_css_selector' => $module_level_css_selectors, //a default set of css_se
                                      ) );
                                  }
                              } else {
                                  $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                      'input_list' => $entry,
                                      'registered_input_list' => $registered_input_list,// <= the full list of input for the module
                                      'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                      'module_css_selector' => $module_level_css_selectors //a default set of css_selectors might have been specified on module registration
                                  ));
                              }
                          }//if is_father
                    }//if
                }//if
            }//if


            // INPUT TEXT LEVEL CSS RULES
            // @added in sept 2019 for https://github.com/presscustomizr/nimble-builder/issues/499
            // When we are inside the associative arrays of the level 'options'
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( is_string( $key ) && 1 < strlen( $key ) && 'options' === $key ) {
                // we need to have a level model set
                if ( !empty( $parent_level ) && is_array( $parent_level ) ) {
                    if ( is_array( $entry ) ) {

                        // Level options are structured as an associative array of option groups
                        // $entry = array(
                        //    'text' => array(
                        //        font_size_css => ...
                        //        color_css => ...
                        //    ),
                        //    'bg' => array(),
                        //    ...
                        // )
                        foreach ( $entry as $opt_group_type => $input_candidates ) {
                            if ( 'level_text' !== $opt_group_type )
                              continue;

                            $level_text_registered_input_list = sek_get_registered_module_input_list( 'sek_level_text_module' );
                            $level_text_css_selectors = sek_get_registered_module_type_property( 'sek_level_text_module', 'css_selectors' );

                            $rules = $this->sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, array(
                                'input_list' => $input_candidates,
                                'registered_input_list' => $level_text_registered_input_list,// <= the full list of input for the module
                                'parent_module_level' => $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                'module_css_selector' => $level_text_css_selectors //a default set of css_selectors might have been specified on module registration
                            ));
                        }
                    }//if
                }//if
            }//if


            // LEVEL CSS RULES
            if ( is_array( $entry ) ) {
                // Populate rules for sections / columns / modules
                // Location level are excluded
                if ( !empty( $entry[ 'level' ] ) && 'location' != $entry[ 'level' ] ) {
                    $level_type = $entry[ 'level' ];
                    $rules = apply_filters( "sek_add_css_rules_for__{$level_type}__options", $rules, $entry );
                    // build rules for level options => section / column / module
                    $rules = apply_filters( 'sek_add_css_rules_for_level_options', $rules, $entry );
                }

                // populate rules for modules values
                if ( !empty( $entry[ 'level' ] ) && 'module' === $entry['level'] ) {
                    if ( ! empty( $entry['module_type'] ) ) {
                        $module_type = $entry['module_type'];
                        // build rules for modules
                        // applying sek_normalize_module_value_with_defaults() allows us to access all the value properties of the module without needing to check their existence
                        $rules = apply_filters( "sek_add_css_rules_for_module_type___{$module_type}", $rules, sek_normalize_module_value_with_defaults( $entry ) );
                    }
                }
            } // if ( is_array( $entry ) ) {


            // POPULATE THE CSS RULES COLLECTION
            if ( !empty( $rules ) ) {
                //@TODO: MAKE SURE RULE ARE NORMALIZED
                foreach( $rules as $rule ) {
                    if ( ! is_array( $rule ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                        continue;
                    }
                    if ( empty( $rule['selector']) ) {
                        sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
                        continue;
                    }
                    $this->sek_populate(
                        $rule[ 'selector' ],
                        $rule[ 'css_rules' ],
                        $rule[ 'mq' ]
                    );
                }//foreach
            }

            // keep walking if the current $entry is an array
            // make sure that the parent_level_model is set right before jumping down to the next level
            if ( is_array( $entry ) ) {
                // Can we set a parent level ?
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $parent_level = $entry;
                }
                // Let's go recursive
                $this->sek_css_rules_sniffer_walker( $entry, $parent_level );
            }

            // Reset the parent level model because it might have been modified after walking the sublevels
            if ( ! empty( $parent_level ) ) {
                $this->parent_level_model = $parent_level;
            }

        }//foreach
    }//sek_css_rules_sniffer_walker()




    // @param $rules // <= the in-progress global array of css rules to be populated
    // @param $params= array()
    // @return array of css rules*
    // The input ids prefixed with '_css' are eligible for automaric CSS rules generation.
    // @see add_filter( "sek_add_css_rules_for_input_id", '\Nimble\sek_add_css_rules_for_css_sniffed_input_id', 10, 1 );
    function sek_loop_on_input_candidates_and_maybe_generate_css_rules( $rules, $params ) {
        // normalize params
        $default_params = array(
            'input_list' => array(),
            'registered_input_list' => array(),// <= the full list of input for the module
            'parent_module_level' => array(),// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
            'module_css_selector' => '',//a default set of css_selectors might have been specified on module registration
            'is_multi_items' => false
        );
        $params = wp_parse_args( $params, $default_params );

        // FOR MULTI-ITEM MODULES=> add the item-id
        // a multi-item module has a unique id for each item
        // An item looks like :
        // Array
        // (
        //     [id] => 34913f6eef98
        //     [icon] => fab fa-accusoft
        //     [color_css] => #dd9933
        // )
        $item_id = null;
        if ( $params['is_multi_items'] ) {
            if ( !is_array( $params['input_list'] ) || !isset($params['input_list']['id']) ) {
                sek_error_log( __FUNCTION__ . ' => Error => each item of a multi-item module must have an id', $params );
            } else {
                $item_id = $params['input_list']['id'];
            }
        }

        foreach( $params['input_list'] as $input_id_candidate => $_input_val ) {
              if ( false !== strpos( $input_id_candidate, '_css') ) {
                  $rules = apply_filters( 'sek_add_css_rules_for_input_id', $rules, array(
                      'css_val' => $_input_val,//string or array(), //<= the css property value
                      'input_id' => $input_id_candidate,//string// <= the unique input_id as it as been declared on module registration
                      'registered_input_list' => $params['registered_input_list'],// <= the full list of input for the module
                      'parent_module_level' => $params['parent_module_level'],// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                      'module_css_selector' => $params['module_css_selector'],// <= a default set of css_selectors might have been specified on module registration
                      'is_multi_items' => $params['is_multi_items'],// <= for multi-item modules, the input selectors will be made specific for each item-id. In module templates, we'll use data-sek-item-id="%5$s"
                      // implemented to allow CSS rules to be generated on a per-item basis
                      // for https://github.com/presscustomizr/nimble-builder/issues/78
                      'item_id' => $item_id // <= a multi-item module has a unique id for each item
                  ));
              }
        }
        return $rules;
    }











    // @return void()
    // populates the css rules ::collection property, organized by media queries
    public function sek_populate( $selector, $css_rules, $mq = '' ) {
        if ( ! is_string( $selector ) )
            return;
        if ( ! is_string( $css_rules ) )
            return;

        // Assign a default media device
        //TODO: allowed media query?
        $mq_device = 'all_devices';

        // If a media query is requested, build it
        if ( !empty( $mq ) ) {
            if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
            } else {
                $mq_device = $mq;
            }
        }

        // if the media query for this device is not yet added, add it
        if ( !isset( $this->collection[ $mq_device ] ) ) {
            $this->collection[ $mq_device ] = array();
        }

        if ( !isset( $this->collection[ $mq_device ][ $selector ] ) ) {
            $this->collection[ $mq_device ][ $selector ] = array();
        }

        $this->collection[ $mq_device ][ $selector ][] = $css_rules;
    }//sek_populate



    // @return string
    public static function sek_maybe_wrap_in_media_query( $css,  $mq_device = 'all_devices' ) {
        if ( 'all_devices' === $mq_device ) {
            return $css;
        }
        if ( false === strpos($mq_device, '(') || false === strpos($mq_device, ')') ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing parenthesis in the media queries', $mq_device );
            return $css;
        }
        return sprintf( '@media%1$s{%2$s}', $mq_device, $css);
    }


    // sorts the media queries from all_devices to the smallest width
    // This doesn't make the difference between max-width and min-width
    // @return integer
    public static function user_defined_array_key_sort_fn($a, $b) {
        if ( 'all_devices' === $a ) {
            return -1;
        }
        if ( 'all_devices' === $b ) {
            return 1;
        }
        $a_int = (int)preg_replace('/[^0-9]/', '', $a) * 1;
        $b_int = (int)preg_replace('/[^0-9]/', '', $b) * 1;

        return $b_int - $a_int;
    }

    //@returns a stringified stylesheet, ready to be printed on the page or in a file
    public function get_stylesheet() {
        $css = '';
        $collection = apply_filters( 'nimble_css_rules_collection_before_printing_stylesheet', $this->collection );
        if ( is_array( $collection ) && !empty( $collection ) ) {
            // Sort the collection by media queries
            uksort( $collection, array( get_called_class(), 'user_defined_array_key_sort_fn' ) );

            // process
            foreach ( $collection as $mq_device => $selectors ) {
                $_css = '';
                foreach ( $selectors as $selector => $css_rules ) {
                    $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                    $_css .=  $selector . '{' . $css_rules . '}';
                    $_css =  str_replace(';;', ';', $_css);//@fixes https://github.com/presscustomizr/nimble-builder/issues/137
                }
                $_css = self::sek_maybe_wrap_in_media_query( $_css, $mq_device );
                $css .= $_css;
            }
        }
        return apply_filters( 'nimble_get_dynamic_stylesheet', $css, $this->is_global_stylesheet );
    }







    // Helper
    // @return css string including media queries
    // @used for example when generating the rules for used defined section widths locally and globally
    public static function sek_generate_css_stylesheet_for_a_set_of_rules( $rules ) {
        $rules_collection = array();
        $css = '';

        if ( empty( $rules ) || ! is_array( $rules ) )
          return $css;

        // POPULATE THE CSS RULES COLLECTION
        foreach( $rules as $rule ) {
            if ( ! is_array( $rule ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                continue;
            }
            if ( empty($rule['selector']) || ! is_string( $rule['selector'] ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
                continue;
            }

            $selector = $rule[ 'selector' ];
            $css_rules = $rule[ 'css_rules' ];
            $mq = $rule[ 'mq' ];

            if ( ! is_string( $css_rules ) )
              continue;

            // Assign a default media device
            //TODO: allowed media query?
            $mq_device = 'all_devices';

            // If a media query is requested, build it
            if ( !empty( $mq ) ) {
                if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                    error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
                } else {
                    $mq_device = $mq;
                }
            }

            // if the media query for this device is not yet added, add it
            if ( !isset( $rules_collection[ $mq_device ] ) ) {
                $rules_collection[ $mq_device ] = array();
            }

            if ( !isset( $rules_collection[ $mq_device ][ $selector ] ) ) {
                $rules_collection[ $mq_device ][ $selector ] = array();
            }

            $rules_collection[ $mq_device ][ $selector ][] = $css_rules;
        }//foreach

        // GENERATE CSS
        if ( is_array( $rules_collection ) && !empty( $rules_collection ) ) {
            // Sort the collection by media queries
            // get_called_class() is supported by php >= 5.3.0. Nimble needs 5.4
            // @see https://developer.wordpress.org/reference/functions/add_action/
            uksort( $rules_collection, array( get_called_class(), 'user_defined_array_key_sort_fn' ) );

            // process
            foreach ( $rules_collection as $mq_device => $selectors ) {
                $_css = '';
                foreach ( $selectors as $selector => $css_rules ) {
                    $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                    $_css .=  $selector . '{' . $css_rules . '}';
                    $_css =  str_replace(';;', ';', $_css);//@fixes https://github.com/presscustomizr/nimble-builder/issues/137
                }
                $_css = self::sek_maybe_wrap_in_media_query( $_css, $mq_device );
                $css .= $_css;
            }
        }

        return $css;
    }//sek_generate_css_stylesheet_for_a_set_of_rules()









    // hook : sek_add_css_rules_for_level_options
    // fired this class constructor
    public function sek_add_rules_for_column_width( $rules, $column ) {
        if ( ! is_array( $column ) )
          return $rules;

        if ( empty( $column['level'] ) || 'column' !== $column['level'] || empty( $column['id'] ) )
          return $rules;

        $width = null;
        // First try to find a width value in options, then look in the previous width property for backward compatibility
        // After implementing https://github.com/presscustomizr/nimble-builder/issues/279
        $column_options = isset( $column['options'] ) ? $column['options'] : array();
        //sek_error_log( 'COLUMN MODEL WHEN ADDING RULES ?', $column_options );

        if ( !empty( $column_options['width'] ) && !empty( $column_options['width']['custom-width'] ) ) {
            $width_candidate = (float)$column_options['width']['custom-width'];
            if ( $width_candidate < 0 || $width_candidate > 100 ) {
                sek_error_log( __FUNCTION__ . ' => invalid width value for column id : ' . $column['id'] );
            } else {
                $width = $width_candidate;
            }
        } else {
            // Backward compat since June 2019
            // After implementing https://github.com/presscustomizr/nimble-builder/issues/279
            $width = empty( $column[ 'width' ] ) || !is_numeric( $column[ 'width' ] ) ? '' : $column['width'];
        }

        // width
        if ( empty( $width ) )
          return $rules;

        // define a default breakpoint : 768
        $breakpoint = self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ];

        // Does the parent section have a custom breakpoint set ?
        $parent_section = sek_get_parent_level_model( $column['id'] );
        if ( 'no_match' === $parent_section ) {
            sek_error_log( __FUNCTION__ . ' => $parent_section not found for column id : ' . $column['id'] );
            return $rules;
        }
        $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( array( 'section_model' => $parent_section ) ) );
        if ( $section_custom_breakpoint >= 1 ) {
            $breakpoint = $section_custom_breakpoint;
        } else {
            // Is there a global custom breakpoint set ?
            $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
            if ( $global_custom_breakpoint >= 1 ) {
                $breakpoint = $global_custom_breakpoint;
            }
        }

        // Note : the css selector must be specific enough to override the possible parent section ( or global ) custom breakpoint one.
        // @see sek_add_css_rules_for_level_breakpoint()
        $rules[] = array(
            'selector'      => sprintf( '[data-sek-id="%1$s"] .sek-sektion-inner > .sek-column[data-sek-id="%2$s"]', $parent_section['id'], $column['id'] ),
            'css_rules'     => sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width ),
            'mq'            => "(min-width:{$breakpoint}px)"
        );
        return $rules;
    }
}//end class

?>