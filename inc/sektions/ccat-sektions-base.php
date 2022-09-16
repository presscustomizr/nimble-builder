<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
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
    private $module_types = [];
    private $sek_model;
    // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
    private $is_global_stylesheet;
    private $parent_level_model = array();

    public $customizer_active_locations = '_not_set_';//June 2020 => added to prevent printing css for not active locations
    public $current_sniffed_location = '_not_set_';//June 2020 => added to prevent printing css for not active locations

    public $sniffed_locations = [];//oct 2020 => will populate a collection of location sniffed while parsing the sek_model
    public $sniffed_modules = [];//oct 2020 => will populate a collection of modules sniffed while parsing the sek_model

    private $needs_wp_comments_stylesheet = false;//April 2021 => for site templates

    public function __construct( $sek_model = array(), $is_global_stylesheet = false ) {
        $this->sek_model  = $sek_model;

        // June 2020 : this property is set when saving the customizer
        // and used to determine if we need to generate css for a given location
        // typically useful when a local header is populated with sections but not used on the page. While still present in the collection of location, we don't want to generate css for it.
        $raw_customizer_active_locations = ( isset($_POST['active_locations']) && is_array($_POST['active_locations']) ) ? $_POST['active_locations'] : '_not_set_';
        
        // sanitize now and cache
        if ( !is_array($raw_customizer_active_locations) ) {
            $this->customizer_active_locations = sanitize_text_field($raw_customizer_active_locations);
        } else {
            $_active_locations = [];
            foreach($raw_customizer_active_locations as $loc ) {
                $_active_locations[] = sanitize_text_field($loc);
            }
            $this->customizer_active_locations = $_active_locations;
        }

        $this->is_global_stylesheet = $is_global_stylesheet;
        // set the css rules for columns
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        // filter fired in sek_css_rules_sniffer_walker()
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

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
        if ( !empty( $parent_level ) ) {
            $this->parent_level_model = $parent_level;
        }

        foreach ( $level as $key => $entry ) {
            // Let's cache the currently sniffed location
            if ( is_array($entry) && isset($entry['level']) && 'location' === $entry['level'] ) {
                $this->current_sniffed_location = $entry['id'];
                $this->sniffed_locations[ $this->current_sniffed_location ] = [];
            }

            // When saving in the customizer, the active locations are passed in $_POST
            // so we can determine if a location is currently active or not, and if not, we don't need to generate CSS for it.
            // Oct 2020 : case of the global stylesheet :
            // The global stylesheet may be inactive on a given customization, which means that the customizer_active_locations won't include any global locations.
            // But this does not mean that the global stylesheet is inactive on other pages.
            // That's why we only verify the active location condition when !$this->is_global_stylesheet
            if ( !$this->is_global_stylesheet && '_not_set_' !== $this->customizer_active_locations && is_array($this->customizer_active_locations) && '_not_set_' !== $this->current_sniffed_location && !in_array($this->current_sniffed_location, $this->customizer_active_locations ) ) {
                continue;
            }

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

                    // Populates the sniffed module collection for later use
                    $current_location_modules = $this->sniffed_locations[ $this->current_sniffed_location ];
                    $current_location_modules = is_array($current_location_modules) ? $current_location_modules : [];
                    if ( !in_array( $parent_level['module_type'], $current_location_modules ) ) {
                        $this->sniffed_modules[] = $parent_level['module_type'];
                        $this->sniffed_locations[ $this->current_sniffed_location ][] = $parent_level['module_type'];
                    }

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
                                  if ( !is_array( $children ) ) {
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
                    // param is_global_stylesheet says that we're building the global stylesheet
                    // introduced for the custom CSS, to know if we're building CSS for a local or a global section
                    // @see https://github.com/presscustomizr/nimble-builder-pro/issues/67
                    $rules = apply_filters( 'sek_add_css_rules_for_level_options', $rules, $entry, $this->is_global_stylesheet );
                }

                // populate rules for modules values
                if ( !empty( $entry[ 'level' ] ) && 'module' === $entry['level'] ) {
                    if ( !empty( $entry['module_type'] ) ) {
                        $module_type = $entry['module_type'];
                        // populate module types list so we can add their stylesheet afterward
                        $this->module_types[] = $module_type;

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
                    if ( !is_array( $rule ) ) {
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
            if ( !empty( $parent_level ) ) {
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
              } else {
                  // added April 2021 for site templates
                  // sniff if we need to add the comments css => looking for {{the_comments}}
                  if ( !$this->is_global_stylesheet && is_string($_input_val) ) {
                    preg_replace_callback( '!\{\{\s?(.*?)\s?\}\}!', array( $this, "sek_sniff_wp_comment_template_tag" ), $_input_val);
                  }
              }
        }
        return $rules;
    }

    // added Arpil 2021 for site templates
    public function sek_sniff_wp_comment_template_tag($matches) {
        //sek_error_log('$matches ??', $matches );
        if ( !is_array($matches) || empty($matches[1]) )
            return;
        if ( !$this->needs_wp_comments_stylesheet && is_string($matches[1] ) ) {
            $this->needs_wp_comments_stylesheet = false !== strpos($matches[1], 'the_comments');
        }
    }








    // @return void()
    // populates the css rules ::collection property, organized by media queries
    public function sek_populate( $selector, $css_rules, $mq = '' ) {
        if ( !is_string( $selector ) )
            return;
        if ( !is_string( $css_rules ) )
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

        // CONCATENATE MODULE STYLESHEETS
        // Oct 2020 => https://github.com/presscustomizr/nimble-builder/issues/749
        $this->module_types = array_unique($this->module_types);

        $modules_css = '';
        $base_uri = NIMBLE_BASE_PATH . '/assets/front/css/modules/';
        global $wp_filesystem;
        $reading_issue = false;
        $read_attempt = false;
        $concatenated_module_stylesheets = Nimble_Manager()->concatenated_module_stylesheets;

        foreach (Nimble_Manager()->big_module_stylesheet_map as $module_type => $stylesheet_name ) {
            if ( $reading_issue )
                continue;
            if ( !in_array($module_type , $this->module_types ) )
              continue;
            // abort if the module stylesheet has already been concatenated in another stylesheet
            if ( in_array( $module_type, $concatenated_module_stylesheets ) )
                continue;

            $uri = sprintf( '%1$s%2$s%3$s',
                $base_uri ,
                $stylesheet_name,
                sek_is_dev_mode() ? '.css' : '.min.css'
            );

            $uri =  wp_normalize_path($uri);
            $read_attempt = true;
            //sek_error_log('$uri ??' . $module_type . $stylesheet_name, $uri );
            if ( $wp_filesystem->exists($uri) && $wp_filesystem->is_readable($uri) ) {
                $modules_css .= $wp_filesystem->get_contents($uri);
                // add this stylesheet to the already concatenated list
                $concatenated_module_stylesheets[] = $module_type; 
            } else {
                $reading_issue = true;
            }
        }//foreach
        
        // COMMENTS CSS
        $comments_css = '';
        //if ( apply_filters('include_comments_css', true ) ) {
        if ( !$this->is_global_stylesheet && $this->needs_wp_comments_stylesheet ) {
            $read_attempt = true;

            $uri = sprintf( '%1$s%2$s%3$s',
                NIMBLE_BASE_PATH . '/assets/front/css/',
                'sek-wp-comments',
                sek_is_dev_mode() ? '.css' : '.min.css'
            );

            $uri =  wp_normalize_path($uri);

            //sek_error_log('$uri ??' . $module_type . $stylesheet_name, $uri );
            if ( $wp_filesystem->exists($uri) && $wp_filesystem->is_readable($uri) ) {
                $comments_css = $wp_filesystem->get_contents($uri);
            } else {
                $reading_issue = true;
            }
        }

        // update the list of concatenated module stylesheets so that NB doesn't concatenate a module stylesheet twice for the local css and for the global css
        Nimble_Manager()->concatenated_module_stylesheets = array_unique($concatenated_module_stylesheets);

        if ( $read_attempt ) {
            if ( $reading_issue ) {
                update_option( NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS, 'failed');
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => reading issue => impossible to concatenate module stylesheets');
            } else {
                update_option( NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS, 'OK');
            }
        }
        //sek_error_log('$modules_css ??', $modules_css );



        // ORGANIZE CSS RULES BY MEDIA QUERIES
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
        
        // CONCATENATE MODULE CSS + GENERATED CSS
        return apply_filters( 'nimble_get_dynamic_stylesheet',
            $modules_css . $comments_css . $css,
            $this->is_global_stylesheet,
            $this->sniffed_locations,
            $this->sniffed_modules
        );
    }







    // Helper
    // @return css string including media queries
    // @used for example when generating the rules for used defined section widths locally and globally
    public static function sek_generate_css_stylesheet_for_a_set_of_rules( $rules ) {
        $rules_collection = array();
        $css = '';

        if ( empty( $rules ) || !is_array( $rules ) )
          return $css;

        // POPULATE THE CSS RULES COLLECTION
        foreach( $rules as $rule ) {
            if ( !is_array( $rule ) ) {
                sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                continue;
            }
            if ( empty($rule['selector']) || !is_string( $rule['selector'] ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
                continue;
            }

            $selector = $rule[ 'selector' ];
            $css_rules = $rule[ 'css_rules' ];
            $mq = $rule[ 'mq' ];

            if ( !is_string( $css_rules ) )
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
        if ( !is_array( $column ) )
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
        $section_custom_breakpoint = intval( sek_get_section_custom_breakpoint( array( 'section_model' => $parent_section, 'for_responsive_columns' => true ) ) );
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

?><?php
/**
 *  Sek Dyn CSS Handler: class responsible for enqueuing/writing CSS file or enqueuing/printing inline CSS
 */
class Sek_Dyn_CSS_Handler {

    /**
     * CSS files base dir constant
     * Relative dir in the WordPress uploads dir
     *
     * @access public
     */
    const CSS_BASE_DIR = NIMBLE_CSS_FOLDER_NAME;

    /**
     * Functioning mode constant
     * @access public
     */
    const MODE_INLINE  = 'inline';

    /**
     * Functioning mode constant
     * @access public
     */
    const MODE_FILE    = 'file';

    /**
     * CSS resource ID
     *
     * Holds the CSS resource ID
     * Will be used to generate both the file name and the CSS handle when enqueued_or_printed
     * Usually set to skope_id
     *
     * @access private
     * @var string
     */
    private $id;



    /**
     * Requested skope_id
     *
     * Will be used as id
     * Must be provided
     *
     * @access private
     * @var string
     */
    private $skope_id;

    // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
    private $is_global_stylesheet;

    /**
     * the CSS
     *
     * Holds the CSS string: whether to inline print or to write in the proper file
     *
     * @access private
     * @var string
     */
    private $css_string_to_enqueue_or_print = '';


    /**
     * CSS enqueuing / inline printing status
     *
     * Hold the enqueuing status
     *
     * @access private
     * @var bool
     */
    private $enqueued_or_printed = false;



    /**
     * Enqueuing hook
     *
     * Holds the wp action hook name at whose occurrence the CSS will be enqueued_or_printed
     *
     * @access private
     * @var string
     */
    private $hook;


    /**
     * Enqueuing hook priority
     *
     * Holds the wp action hook priority at whose occurrence the CSS will be enqueued
     * (see the $hook param)
     *
     * @var int
     */
    private $priority = 10;



    /**
     * Enqueuing dependencies
     *
     * Holds the style dependencies for this CSS
     *
     * @access private
     * @var array
     */
    private $dep = array();



    /**
     * Functioning mode
     *
     * Holds the object functioning mode: MODE_FILE or MODE_INLINE
     *
     * @access private
     * @var string
     */
    private $mode;

    /**
     * File writing flag
     *
     * Indicates if we need to only write, not print or enqueuing
     * This is used when saving the customizer options + writing the css file.
     *
     * @access private
     * @var bool
     */
    private $customizer_save = false;


    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing if the file doesn't exist
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_write = false;



    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing even if the file exists
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_rewrite = false;


    /**
     * File status
     *
     * Holds the file existence status (true|false)
     *
     * @access private
     * @var bool
     */
    private $file_exists = false;



    /**
     * CSS file base PATH
     *
     * Holds the CSS relative base path
     * This is simply CSS_BASE_DIR in single sites, while its structure takes in account network and site id in multisites
     *
     * @access private
     * @var string
     */
    private $relative_base_path;



    /**
     * CSS file base URI
     *
     * Holds the CSS folder URI
     *
     * @access private
     * @var string
     */
    private $base_uri;


    /**
     * CSS file base URL
     *
     * Holds the CSS folder URL
     *
     * @access private
     * @var string
     */
    private $base_url;



    /**
     * CSS file URL
     *
     * Holds the CSS file URL
     *
     * @access private
     * @var string
     */
    private $url;




    /**
     * CSS file URI
     *
     * Holds the CSS file URI
     *
     * @access private
     * @var string
     */
    private $uri;

    private $builder;//will hold the Sek_Dyn_CSS_Builder instance

    public $sek_model = 'no_set';


    /**
     * Sek Dyn CSS Handler constructor.
     *
     * Initializing the object.
     *
     * @access public
     * @param array $args Optional.
     *
     */
    public function __construct( $args = array() ) {

        $defaults = array(
            'id'                              => 'sek-'.rand(),
            'skope_id'                        => '',
            // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
            'is_global_stylesheet'            => false,
            'mode'                            => self::MODE_FILE,
            'css_string_to_enqueue_or_print'  => $this->css_string_to_enqueue_or_print,
            'dep'                             => $this->dep,
            'hook'                            => '',
            'priority'                        => $this->priority,
            'customizer_save'                 => false,//<= used when saving the customizer settins => we want to write the css file on Nimble_Collection_Setting::update()
            'force_write'                     => $this->force_write,
            'force_rewrite'                   => $this->force_rewrite
        );

        $args = wp_parse_args( $args, $defaults );

        //normalize some parameters
        $args[ 'dep' ]          = is_array( $args[ 'dep' ] ) ? $args[ 'dep' ]  : array();
        $args[ 'priority']      = is_numeric( $args[ 'priority' ] ) ? $args[ 'priority' ] : $this->priority;

        //turn $args into object properties
        foreach ( $args as $key => $value ) {
            if ( property_exists( $this, $key ) && array_key_exists( $key, $defaults) ) {
                    $this->$key = $value;
            }
        }

        if ( empty( $this->skope_id ) ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ .' => __construct => skope_id not provided' );
            return;
        }

        //build no parameterized properties
        $this->_sek_dyn_css_set_properties();

        // Introduced March 2021 for #478
        if ( 'delete' !==  $this->mode ) {
            // Possible scenarii :
            // 1) customizing :
            //    the css is always printed inline. If there's already an existing css file for this skope_id, it's not enqueued.
            // 2) saving in the customizer :
            //    the css file is written in a "force_rewrite" mode, meaning that any existing css file gets re-written.
            //    There's no enqueing scheduled, 'customizer_save' mode.
            // 3) front, user logged in + 'customize' capabilities :
            //    the css file is re-written on each page load + enqueued. If writing a css file is not possible, we fallback on inline printing.
            // 4) front, user not logged in :
            //    the default behavior is that the css file is enqueued.
            //    It should have been written when saving in the customizer. If no file available, we try to write it. If writing a css file is not possible, we fallback on inline printing.
            if ( is_customize_preview() || !$this->_sek_dyn_css_file_exists_is_readable_and_has_content() || $this->force_rewrite || $this->customizer_save ) {
                $this->sek_model = sek_get_skoped_seks( $this->skope_id );

                //  on front, when no stylesheet is available, the fallback hook must be set to wp_head, because the hook property might be empty
                // fixes https://github.com/presscustomizr/nimble-builder/issues/328
                if ( !is_customize_preview() && !$this->_sek_dyn_css_file_exists_is_readable_and_has_content() ) {
                    $this->hook = 'wp_head';
                }

                //build stylesheet
                $this->builder = new Sek_Dyn_CSS_Builder( $this->sek_model, $this->is_global_stylesheet );

                // now that the stylesheet is ready let's cache it
                // Note that esc_html() cannot be used because `div &gt; span` is not interpreted properly.
                $this->css_string_to_enqueue_or_print = (string)strip_tags($this->builder->get_stylesheet());
            }

            // Do we have any rules to print / enqueue ?
            // If yes, print in the dom or enqueue depending on the current context ( customization or front )
            // If not, delete any previouly created stylesheet

            //hook setup for printing or enqueuing
            //bail if "customizer_save" == true, typically when saving the customizer settings @see Nimble_Collection_Setting::update()
            if ( !$this->customizer_save ) {
                // when not customizing, we write and enqueue :
                // - if the file already exists,
                // - or if we just have generated the CSS because the file had been deleted
                if ( !empty($this->css_string_to_enqueue_or_print) || $this->_sek_dyn_css_file_exists_is_readable_and_has_content() ) {
                    $this->_schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook();
                } else {
                    $this->sek_dyn_css_delete_file_if_empty();
                }
            } else {
                //sek_error_log( __CLASS__ . '::' . __FUNCTION__ .' ?? => $this->css_string_to_enqueue_or_print => ', $this->css_string_to_enqueue_or_print );
                if ( !empty($this->css_string_to_enqueue_or_print) ) {
                    $this->sek_dyn_css_maybe_write_css_file();
                } else {
                    // When customizing, the stylesheet is always generated.
                    // So if it is empty, it means we have to delete it
                    $this->sek_dyn_css_delete_file();
                }
            }

            // Maybe update global inline style now with a filter
            // This CSS is the one generated by global options like global text, global width, global breakpoint
            // It is printed @wp_head inline
            // for better performances on front, NB only wants to re-generate this style when customizing, and we user is logged in ( force_rewrite )
            // see https://github.com/presscustomizr/nimble-builder/issues/750
            if ( $this->is_global_stylesheet ) {
                if ( is_customize_preview() || $this->force_rewrite || $this->customizer_save ) {
                    $global_style = Nimble_Manager()->sek_build_global_options_inline_css();
                    //sek_error_log('SOO GLOBAL INLINE CSS?', $global_style );
                    update_option( NIMBLE_OPT_FOR_GLOBAL_CSS, $global_style, 'no' );
                }
            }
        }//if 'delete' !==  $this->mode
    }//__construct





    /*
    * Private methods
    */

    /**
     *
     * Build these instance properties based on the params passed on instantiation
     * called in the constructor
     *
     * @access private
     *
     */
    private function _sek_dyn_css_set_properties() {
        $this->_sek_dyn_css_require_wp_filesystem();

        $this->relative_base_path   = $this->_sek_dyn_css_build_relative_base_path();

        $this->base_uri             = $this->_sek_dyn_css_build_base_uri();
        $this->base_url             = $this->_sek_dyn_css_build_base_url();

        $this->uri                  = $this->_sek_dyn_css_build_uri();
        $this->url                  = $this->_ssl_maybe_fix_url( $this->_sek_dyn_css_build_url() );

        $this->file_exists          = $this->_sek_dyn_css_file_exists_is_readable_and_has_content();

        if ( self::MODE_FILE == $this->mode ) {
            if ( !$this->_sek_dyn_css_write_file_is_possible() ) {
                $this->mode = self::MODE_INLINE;
            }
        }

        // July 2020 remove previous folder
        // see https://github.com/presscustomizr/nimble-builder/issues/727
        // if ( 'done' != get_transient( 'nimble_update_css_folder_name_0720' ) ) {
        //     set_transient( 'nimble_update_css_folder_name_0720', 'done', 30 * YEAR_IN_SECONDS );
        // }
        $upload_dir = wp_get_upload_dir();
        $prev_folder_path = $this->_sek_dyn_css_build_relative_base_path( NIMBLE_DEPREC_ONE_CSS_FOLDER_NAME );
        $previous_folder_one = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $prev_folder_path );
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $previous_folder_one ) ) {
            $wp_filesystem->rmdir( $previous_folder_one, true );
        }

        // October 2020 remove previous folder when implementing dynamic module stylesheet concatenation
        $prev_folder_path = $this->_sek_dyn_css_build_relative_base_path( NIMBLE_DEPREC_TWO_CSS_FOLDER_NAME );
        $previous_folder_two = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $prev_folder_path );
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $previous_folder_two ) ) {
            $wp_filesystem->rmdir( $previous_folder_two, true );
        }
    }


    /**
    * replace http: URL with https: URL
    * @fix https://github.com/presscustomizr/nimble-builder/issues/188
    * @param string $url
    * @return string
    */
    private function _ssl_maybe_fix_url($url) {
      // only fix if source URL starts with http://
      if ( is_ssl() && is_string($url) && stripos($url, 'http://') === 0 ) {
        $url = 'https' . substr($url, 4);
      }

      return $url;
    }


    /**
     *
     * Maybe setup hooks
     * called in the constructor
     *
     * @access private
     *
     */
    private function _schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook() {
        if ( $this->hook ) {
            add_action( $this->hook, array( $this, 'sek_dyn_css_enqueue_or_print_and_google_fonts_print' ), $this->priority );
        } else {
            //enqueue or print
            $this->sek_dyn_css_enqueue_or_print_and_google_fonts_print();
        }
    }




    /**
     * Enqueue CSS.
     *
     * Either enqueue the CSS file or add inline style, depending on the object mode property.
     * The inline enqueuing is also the fall-back if anything goes wrong while trying to enqueuing the file.
     *
     * This method can also write the file under some circumstances (see when the object force_write || force_rewrite are enabled)
     *
     * @access public
     * @return void()
     */
    public function sek_dyn_css_enqueue_or_print_and_google_fonts_print() {
        // CSS FILE
        //case enqueue file : front end + user with customize caps not logged in
        if ( self::MODE_FILE == $this->mode ) {
            //in case we need to write the file before enqueuing
            //1) $this->css_string_to_enqueue_or_print must exists
            //2) we might need to force the rewrite even if the file exists or to write it if the file doesn't exist
            if ( $this->css_string_to_enqueue_or_print ) {
                if ( $this->force_rewrite || ( !$this->file_exists && $this->force_write ) ) {
                    $this->file_exists = $this->sek_dyn_css_maybe_write_css_file();
                }
            }

            //if the file exists
            if ( $this->file_exists ) {
                //this resource version is built upon the file last modification time
                wp_enqueue_style( "sek-dyn-{$this->id}", $this->url, $this->dep, filemtime($this->uri) );

                $this->enqueued_or_printed = true;
            }
        }// if ( self::MODE_FILE )

        //if $this->mode != 'file' or the file enqueuing didn't go through (fall back)
        //print inline style
        if ( $this->css_string_to_enqueue_or_print && !$this->enqueued_or_printed ) {
            $dep =  array_pop( $this->dep );

            if ( !$dep || wp_style_is( $dep, 'done' ) || !wp_style_is( $dep, 'done' ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                // only fired when doing ajax during customization in order to return a refreshed partial stylesheet
                printf( '<style id="sek-%1$s" media="all">%2$s</style>',
                    esc_attr($this->id),
                    // Note that esc_html() cannot be used because `div &gt; span` is not interpreted properly.
                    strip_tags($this->css_string_to_enqueue_or_print)
                );
            } else {
                wp_add_inline_style( $dep , $this->css_string_to_enqueue_or_print );
            }

            $this->mode     = self::MODE_INLINE;
            $this->enqueued_or_printed = true;
        }
    }


    /*
    * Public 'actions'
    */
    /**
     *
     * Write the CSS to the disk, if we can
     *
     * @access public
     *
     * @return bool TRUE if the CSS file has been written, FALSE otherwise
     */
    public function sek_dyn_css_maybe_write_css_file() {
        global $wp_filesystem;

        $error = false;

        $base_uri = $this->base_uri;

        // Can we create the folder?
        if ( !$wp_filesystem->is_dir( $base_uri ) ) {
            $error = !wp_mkdir_p( $base_uri );
        }

        if ( $error ) {
            return false;
        }

        if ( !file_exists( $index_path = wp_normalize_path( trailingslashit( $base_uri ) . 'index.php' ) ) ) {
            // predefined mode settings for WP files
            // Make sure NB uses the proper FS_CHMOD_DIR value
            // fixes https://github.com/presscustomizr/nimble-builder/issues/862
            // doc : https://wordpress.org/support/article/editing-wp-config-php/#override-of-default-file-permissions
            // doc : https://wordpress.stackexchange.com/questions/253274/use-of-undefined-constant-fs-chmod-dir-assumed-fs-chmod-dir
            $chmod_dir = ( 0755 & ~ umask() );
            if ( defined( 'FS_CHMOD_DIR' ) ) {
                $chmod_dir = FS_CHMOD_DIR;
            }
            $wp_filesystem->put_contents( $index_path, "<?php\n// Silence is golden.\n", $chmod_dir );
        }


        if ( !wp_is_writable( $base_uri ) ) {
            return false;
        }

        //actual write try and update the file_exists status
        $this->file_exists = $wp_filesystem->put_contents(
            $this->uri,
            $this->css_string_to_enqueue_or_print,//secured earlier with strip_tags()
            // predefined mode settings for WP files
            FS_CHMOD_FILE
        );

        //return whether or not the writing succeeded
        return $this->file_exists;
    }



    /**
     *
     * Maybe remove the CSS file from the disk, if it exists and if empty
     * Note : July 2020 => function updated for https://github.com/presscustomizr/nimble-builder/issues/727
     *
     * @return bool TRUE if the CSS file has been deleted (or didn't exist already), FALSE otherwise
     */
    public function sek_dyn_css_delete_file_if_empty() {
        global $wp_filesystem;
        if ( $this->_sek_dyn_css_file_exists_and_is_empty() ) {
            $this->file_exists != $wp_filesystem->delete( $this->uri );
            return !$this->file_exists;
        }
        return !$this->file_exists;
    }


    /**
     *
     * Remove the CSS file from the disk, if it exists, and even if not empty
     * Note : July 2020 => function updated for https://github.com/presscustomizr/nimble-builder/issues/727
     *
     * @return bool TRUE if the CSS file has been deleted (or didn't exist already), FALSE otherwise
     */
    public function sek_dyn_css_delete_file() {
        global $wp_filesystem;
        if ( $this->file_exists ) {
            $this->file_exists != $wp_filesystem->delete( $this->uri );
            //sek_error_log('CSS HANDLER => REMOVE FILE => ' . $this->uri);
            return !$this->file_exists;
        }
        return !$this->file_exists;
    }


    /*
    * Private helpers
    */

    /**
     *
     * Retrieve the actual CSS file existence on the file system
     *
     * @access private
     *
     * @return bool TRUE if the CSS file exists, FALSE otherwise
     */
    private function _sek_dyn_css_file_exists_is_readable_and_has_content() {
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $this->uri ) ) {
            $file_content = $wp_filesystem->get_contents( $this->uri );
            return $wp_filesystem->is_readable( $this->uri ) && !empty( $file_content );
        } else {
            return false;
        }
    }

    // Note : July 2020 => function introduced for https://github.com/presscustomizr/nimble-builder/issues/727
    private function _sek_dyn_css_file_exists_and_is_empty() {
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $this->uri ) ) {
            $file_content = $wp_filesystem->get_contents( $this->uri );
            return empty( $file_content );
        } else {
            return false;
        }
    }


    /**
     *
     * Build normalized URI of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URI
     */
    private function _sek_dyn_css_build_uri() {
        if ( !isset( $this->base_uri ) ) {
            $this->_sek_dyn_css_build_base_uri();
        }
        //sek_error_log('///////////////////ALORS CSS FILE NAME ??', $this->id );
        return wp_normalize_path( trailingslashit( $this->base_uri ) . "{$this->id}.css" );
    }




    /**
     *
     * Build the URL of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URL
     */
    private function _sek_dyn_css_build_url() {
        if ( !isset( $this->base_url ) ) {
            $this->_sek_dyn_css_build_base_url();
        }
        return trailingslashit( $this->base_url ) . "{$this->id}.css";
    }




    /**
     *
     * Build the URI of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URI
     */
    private function _sek_dyn_css_build_base_uri() {
        //since 4.5.0
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $relative_base_path );
    }




    /**
     *
     * Build the URL of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URL
     */
    private function _sek_dyn_css_build_base_url() {
        //since 4.5.0
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return set_url_scheme( trailingslashit( $upload_dir['baseurl'] ) . $relative_base_path );
    }




    /**
     *
     * Retrieve the relative path (to the 'uploads' dir ) of the CSS base directory
     * July 2020 => added a $base_dir param for #727
     *
     * @access private
     *
     * @return string The relative path (to the 'uploads' dir) of the CSS base directory
     */
    private function _sek_dyn_css_build_relative_base_path( $base_dir = null ) {
        $css_base_dir     = is_null($base_dir) ? self::CSS_BASE_DIR : $base_dir;

        if ( is_multisite() ) {
            $site        = get_site();
            $network_id  = $site->site_id;
            $site_id     = $site->blog_id;
            $css_dir     = trailingslashit( $css_base_dir ) . trailingslashit( $network_id ) . $site_id;
        }

        return $css_base_dir;
    }




    /**
     *
     * Checks whether or not we can write to the disk
     *
     * @access private
     *
     * @return bool Whether or not we have filesystem credentials
     */
    //TODO: try to extend this to other methods e.g. FTP when FTP credentials are already defined
    private function _sek_dyn_css_write_file_is_possible() {
        $upload_dir      = wp_get_upload_dir();
        //Note: if the 'uploads' dir has not been created, this check will not pass, hence no file will never be created
        //unless something else creates the 'uploads' dir
        if ( 'direct' === get_filesystem_method( array(), $upload_dir['basedir'] ) ) {
            $creds = request_filesystem_credentials( '', '', false, false, array() );

            /* initialize the API */
            if ( !WP_Filesystem($creds) ) {
                /* any problems and we exit */
                return false;
            }
            return true;
        }

        return false;
    }



    /**
     *
     * Simple helper to require the WordPress filesystem relevant file
     *
     * @access private
     */
    private function _sek_dyn_css_require_wp_filesystem() {
        global $wp_filesystem;

        // Initialize the WordPress filesystem.
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }
    }

}

?><?php
// filter declared in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker()
// $rules = apply_filters( "sek_add_css_rules_for_input_id", $rules, $key, $entry, $this->parent_level );
// the rules are filtered if ( false !== strpos( $input_id_candidate, '_css') )
// Example of input id candidate filtered : 'h_alignment_css'
// @see function sek_loop_on_input_candidates_and_maybe_generate_css_rules( $params ) {}
add_filter( "sek_add_css_rules_for_input_id", '\Nimble\sek_add_css_rules_for_css_sniffed_input_id', 10, 2 );

//@param $params = array()
//@param $rules <= the in-progress global array of css rules to be populated
function sek_add_css_rules_for_css_sniffed_input_id( $rules, $params ) {
    // normalize params
    $default_params = array(
        'css_val' => '',//string or array(), //<= the css property value
        'input_id' => '',//string// <= the unique input_id as it as been declared on module registration
        'registered_input_list' => array(),// <= the full list of input for the module
        'parent_module_level' => array(),// <= the parent level. name is misleading because can be module but also other levels array( 'location', 'section', 'column', 'module' )
        'module_css_selector' => '',//<= a default set of css_selectors might have been specified on module registration
        'is_multi_items' => false,// <= for multi-item modules, the input selectors will be made specific for each item-id. In module templates, we'll use data-sek-item-id="%5$s"
        'item_id' => '' // <= a multi-item module has a unique id for each item
    );

    $params = wp_parse_args( $params, $default_params );

    // map variables
    $value = $params['css_val'];
    $input_id = $params['input_id'];
    $registered_input_list = $params['registered_input_list'];
    $parent_level = $params['parent_module_level'];
    $module_level_css_selectors = $params['module_css_selector'];
    $is_multi_items = $params['is_multi_items'];
    $item_id = $params['item_id'];

    if ( !is_string( $input_id ) || empty( $input_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing input_id', $parent_level);
        return $rules;
    }
    if ( !is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
        sek_error_log( __FUNCTION__ . ' => missing input_list', $parent_level);
        return $rules;
    }
    if ( empty( $registered_input_list[ $input_id ] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing input_id : ' . $input_id, $parent_level);
        return $rules;
    }
    $input_registration_params = $registered_input_list[ $input_id ];
    if ( !is_string( $input_registration_params['css_identifier'] ) || empty( $input_registration_params['css_identifier'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing css_identifier for parent level', $parent_level );
        sek_error_log('$registered_input_list', $registered_input_list );
        return $rules;
    }

    // Make sure we have the right specificity depending on the level type
    // When styling a section, the specificity has to be > to the global option one
    // This is important in particular to make sure that text CSS rules follow the rule : Global < Section < Module
    if ( 'section' === $parent_level['level'] ) {
        $selector = sprintf( '.nb-loc [data-sek-id="%1$s"] [data-sek-level]', $parent_level['id'] );
    } else {
        // for modules and columns
        $selector = sprintf( '.nb-loc .sek-row [data-sek-id="%1$s"]', $parent_level['id'] );
    }

    // for multi-items module, each item has a unique id allowing us to identify it
    // implemented to allow CSS rules to be generated on a per-item basis
    // for https://github.com/presscustomizr/nimble-builder/issues/78
    if ( $is_multi_items ) {
        $selector = sprintf( '.nb-loc [data-sek-id="%1$s"] [data-sek-item-id="%2$s"]', $parent_level['id'], $item_id );
    }
    $css_identifier = $input_registration_params['css_identifier'];

    // SPECIFIC CSS SELECTOR AT MODULE LEVEL
    // are there more specific css selectors specified on module registration ?
    if ( !is_null( $module_level_css_selectors ) && !empty( $module_level_css_selectors ) ) {
        if ( is_array( $module_level_css_selectors ) ) {
            $new_selectors = array();
            foreach ( $module_level_css_selectors as $spec_selector ) {
                $new_selectors[] = $selector . ' ' . $spec_selector;
            }
            $new_selectors = implode(',', $new_selectors );
            $selector = $new_selectors;
        } else if ( is_string( $module_level_css_selectors ) ) {
            $selector .= ' ' . $module_level_css_selectors;
        }
    }
    // for a module level, increase the default specifity to the .sek-module-inner container by default
    // @fixes https://github.com/presscustomizr/nimble-builder/issues/85
    else if ( 'module' === $parent_level['level'] ) {
        $selector .= ' .sek-module-inner';
    }


    // SPECIFIC CSS SELECTOR AT INPUT LEVEL
    // => Overrides the module level specific selector, if it was set.
    if ( 'module' === $parent_level['level'] ) {
        //$start = microtime(true) * 1000;
        if ( !is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input list' );
        } else if ( is_array( $registered_input_list ) && empty( $registered_input_list[ $input_id ] ) ) {
            sek_error_log( __FUNCTION__ . ' => missing input id ' . $input_id . ' in input list for module type ' . $parent_level['module_type'] );
        }
        if ( is_array( $registered_input_list ) && !empty( $registered_input_list[ $input_id ] ) && !empty( $registered_input_list[ $input_id ]['css_selectors'] ) ) {
            // reset the selector to the level id selector, in case it was previously set specifically at the module level
            $selector = '.nb-loc .sek-row [data-sek-id="'.$parent_level['id'].'"]';
            if ( $is_multi_items ) {
                $selector = sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"]', $parent_level['id'], $item_id );
            }
            $input_level_css_selectors = $registered_input_list[ $input_id ]['css_selectors'];
            $new_selectors = array();
            if ( is_array( $input_level_css_selectors ) ) {
                foreach ( $input_level_css_selectors as $spec_selector ) {
                    $new_selectors[] = $selector . ' ' . $spec_selector;
                }
            } else if ( is_string( $input_level_css_selectors ) ) {
                $new_selectors[] = $selector . ' ' . $input_level_css_selectors;
            }

            $new_selectors = implode(',', $new_selectors );
            $selector = $new_selectors;
            //sek_error_log( '$input_level_css_selectors', $selector );
        }
        // sek_error_log( 'input_id', $input_id );
        // sek_error_log( '$registered_input_list', $registered_input_list );

        // $end = microtime(true) * 1000;
        // $time_elapsed_secs = $end - $start;
        // sek_error_log('$time_elapsed_secs to get module params', $time_elapsed_secs );
    }


    $mq = null;
    $properties_to_render = array();

    switch ( $css_identifier ) {
        // 2 cases for the font_size
        // 1) we save it as a string : '16px'
        // 2) we save it as an array of strings by devices : [ 'desktop' => '55px', 'tablet' => '40px', 'mobile' => '36px']
        case 'font_size' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( !empty( $numeric ) ) {
                      $properties_to_render['font-size'] = $value;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '16px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $rules = sek_set_mq_css_rules( array(
                      'value' => $value,
                      'css_property' => 'font-size',
                      'selector' => $selector,
                      'is_important' => $important,
                      'level_id' => $parent_level['id']
                  ), $rules );
            }
        break;
        case 'line_height' :
            $properties_to_render['line-height'] = $value;
        break;
        case 'font_weight' :
            $properties_to_render['font-weight'] = $value;
        break;
        case 'font_style' :
            $properties_to_render['font-style'] = $value;
        break;
        case 'text_decoration' :
            $properties_to_render['text-decoration'] = $value;
        break;
        case 'text_transform' :
            $properties_to_render['text-transform'] = $value;
        break;
        case 'letter_spacing' :
            $properties_to_render['letter-spacing'] = $value . 'px';
        break;
        case 'color' :
            $properties_to_render['color'] = $value;
        break;
        case 'color_hover' :
            //$selector = '[data-sek-id="'.$parent_level['id'].'"]:hover';
            // Add ':hover to each selectors'
            $new_selectors = array();
            $exploded = explode(',', $selector);
            foreach ( $exploded as $sel ) {
                $new_selectors[] = $sel.':hover';
                $new_selectors[] = $sel.':focus';
            }

            $selector = implode(',', $new_selectors);
            $properties_to_render['color'] = $value;
        break;
        case 'background_color' :
            $properties_to_render['background-color'] = $value;
        break;
        case 'h_flex_alignment' :
            $important = false;
            if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
            }
            // convert to flex
            $flex_ready_value = array();
            foreach( $value as $device => $val ) {
                switch ( $val ) {
                    case 'left' :
                        $h_align_value = sprintf('justify-content:flex-start%1$s;-webkit-box-pack:start%1$s;-ms-flex-pack:start%1$s;', $important ? '!important' : '' );
                    break;
                    case 'center' :
                        $h_align_value = sprintf('justify-content:center%1$s;-webkit-box-pack:center%1$s;-ms-flex-pack:center%1$s;', $important ? '!important' : '' );
                    break;
                    case 'right' :
                        $h_align_value = sprintf('justify-content:flex-end%1$s;-webkit-box-pack:end%1$s;-ms-flex-pack:end%1$s;', $important ? '!important' : '' );
                    break;
                    default :
                        $h_align_value = sprintf('justify-content:center%1$s;-webkit-box-pack:center%1$s;-ms-flex-pack:center%1$s;', $important ? '!important' : '' );
                    break;
                }
                $flex_ready_value[$device] = $h_align_value;
            }
            $flex_ready_value = wp_parse_args( $flex_ready_value, array(
                'desktop' => '',
                'tablet' => '',
                'mobile' => ''
            ));

            $rules = sek_set_mq_css_rules_supporting_vendor_prefixes( array(
                'css_rules_by_device' => $flex_ready_value,
                'selector' => $selector,
                'level_id' => $parent_level['id']
            ), $rules );
        break;

        // handles simple or by device option
        case 'h_alignment' :
            if ( is_string( $value ) ) {// <= simple
                $properties_to_render['text-align'] = $value;
            } else if ( is_array( $value ) ) {// <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  $rules = sek_set_mq_css_rules( array(
                      'value' => $value,
                      'css_property' => 'text-align',
                      'selector' => $selector,
                      'is_important' => $important,
                      'level_id' => $parent_level['id'],
                  ), $rules );
            }
        break;

        // -webkit-box-align:end;
        // -ms-flex-align:end;
        // align-items:flex-end;
        case 'v_alignment' :
            switch ( $value ) {
                case 'top' :
                    $v_align_value = "flex-start";
                    $v_vendor_value = "start";
                break;
                case 'center' :
                    $v_align_value = "center";
                    $v_vendor_value = "center";
                break;
                case 'bottom' :
                    $v_align_value = "flex-end";
                    $v_vendor_value = "end";
                break;
                default :
                    $v_align_value = "center";
                    $v_vendor_value = "center";
                break;
            }
            $properties_to_render['align-items'] = $v_align_value;
            $properties_to_render['-webkit-box-align'] = $v_vendor_value;
            $properties_to_render['-ms-flex-align'] = $v_vendor_value;
        break;
        case 'font_family' :
            $ffamily = sek_extract_css_font_family_from_customizer_option( $value );
            if ( !empty( $ffamily ) ) {
                $properties_to_render['font-family'] = $ffamily;
            }
        break;

        /* Spacer */
        // The unit should be included in the $value
        case 'height' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( !empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $unit = '%' === $unit ? 'vh' : $unit;
                      $properties_to_render['height'] = $numeric . $unit;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '20px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( !empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $unit = '%' === $unit ? 'vh' : $unit;
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'height',
                      'selector' => $selector,
                      'is_important' => $important,
                      'level_id' => $parent_level['id']
                  ), $rules );
            }
        break;
        /* Quote border */
        case 'border_width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( 0 === intval($numeric) || !empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $properties_to_render['border-width'] = $numeric . $unit;
            }
        break;
        case 'border_color' :
            $properties_to_render['border-color'] = $value ? $value : '';
        break;
        /* Divider */
        case 'border_top_width' :
            $numeric = sek_extract_numeric_value( $value );
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $value );
                $unit = '%' === $unit ? 'vh' : $unit;
                $properties_to_render['border-top-width'] = $numeric . $unit;
                //$properties_to_render['border-top-width'] = $value > 0 ? $value . 'px' : '1px';
            }
        break;
        case 'border_top_style' :
            $properties_to_render['border-top-style'] = $value ? $value : 'solid';
        break;
        case 'border_top_color' :
            $properties_to_render['border-top-color'] = $value ? $value : '#5a5a5a';
        break;
        case 'border_radius' :
            if ( is_string( $value ) ) {
                $numeric = sek_extract_numeric_value( $value );
                if ( !empty( $numeric ) ) {
                    $unit = sek_extract_unit( $value );
                    $properties_to_render['border-radius'] = $numeric . $unit;
                }
            } else if ( is_array( $value ) ) {
                $rules = sek_generate_css_rules_for_border_radius_options( $rules, $value, $selector );
            }
        break;

        case 'width' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( !empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $properties_to_render['width'] = $numeric . $unit;
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '100%',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( !empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'width',
                      'selector' => $selector,
                      'is_important' => $important,
                      'level_id' => $parent_level['id']
                  ), $rules );
            }
        break;

        case 'v_spacing' :
            if ( is_string( $value ) ) { // <= simple
                  $numeric = sek_extract_numeric_value($value);
                  if ( !empty( $numeric ) ) {
                      $unit = sek_extract_unit( $value );
                      $unit = '%' === $unit ? 'vh' : $unit;
                      $properties_to_render = array(
                          'margin-top'  => $numeric . $unit,
                          'margin-bottom' => $numeric . $unit
                      );
                  }
            } else if ( is_array( $value ) ) { // <= by device
                  $important = false;
                  if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
                      $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
                  }
                  $value = wp_parse_args( $value, array(
                      'desktop' => '15px',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $value;
                  foreach ($value as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( !empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $unit = '%' === $unit ? 'vh' : $unit;
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'margin-top',
                      'selector' => $selector,
                      'is_important' => $important,
                      'level_id' => $parent_level['id']
                  ), $rules );
                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'margin-bottom',
                      'selector' => $selector,
                      'is_important' => $important,
                      'level_id' => $parent_level['id']
                  ), $rules );
            }
        break;
        //not used at the moment, but it might if we want to display the divider as block (e.g. a div instead of a span)
        case 'h_alignment_block' :
            switch ( $value ) {
                case 'right' :
                    $properties_to_render = array(
                        'margin-right'  => '0'
                    );
                break;
                case 'left' :
                    $properties_to_render = array(
                        'margin-left'  => '0'
                    );
                break;
                default :
                    $properties_to_render = array(
                        'margin-left'  => 'auto',
                        'margin-right' => 'auto'
                    );
            }
        break;
        case 'padding_margin_spacing' :
            $default_unit = 'px';
            $rules_candidates = $value;
            //add unit and sanitize padding (cannot have negative padding)
            $unit                 = !empty( $rules_candidates['unit'] ) ? $rules_candidates['unit'] : $default_unit;
            $unit                 = 'percent' == $unit ? '%' : $unit;

            $new_filtered_rules = array();
            foreach ( $rules_candidates as $k => $v) {
                if ( 'unit' !== $k ) {
                    $new_filtered_rules[ $k ] = $v;
                }
            }

            $properties_to_render = $new_filtered_rules;

            array_walk( $properties_to_render,
                function( &$val, $key, $unit ) {
                    //make sure paddings are positive values
                    if ( FALSE !== strpos( 'padding', $key ) ) {
                        $val = abs( $val );
                    }

                    $val .= $unit;
            }, $unit );
        break;
        // May 2020 => @todo those media query css rules doesn't take into account the custom breakpoint if set
        case 'spacing_with_device_switcher' :
            if ( !empty( $value ) && is_array( $value ) ) {
                $rules = sek_generate_css_rules_for_spacing_with_device_switcher( $rules, $value, $selector );
            }
        break;

        // The default is simply there to let us know if a css_identifier is missing
        default :
            sek_error_log( __FUNCTION__ . ' => the css_identifier : ' . $css_identifier . ' has no css rules defined for input id ' . $input_id );
        break;
    }//switch

    // when the module has an '*_flag_important' input,
    // => check if the input_id belongs to the list of "important_input_list"
    // => and maybe flag the css rules with !important
    if ( !empty( $properties_to_render ) ) {
        $important = false;
        if ( 'module' === $parent_level['level'] && !empty( $parent_level['value'] ) ) {
            $important = sek_is_flagged_important( $input_id, $parent_level['value'], $registered_input_list );
        }
        $css_rules = '';
        foreach ( $properties_to_render as $prop => $prop_val ) {
            $css_rules .= sprintf( '%1$s:%2$s%3$s;', $prop, $prop_val, $important ? '!important' : '' );
        }//end foreach

        $rules[] = array(
            'selector'    => $selector,
            'css_rules'   => $css_rules,
            'mq'          => $mq
        );
    }
    return $rules;
}//sek_add_css_rules_for_css_sniffed_input_id






// @return boolean
// Recursive
// Check if a *_flag_important input id is part of the registered input list of the module
// then verify is the provided input_id is part of the list of input that should be set to important => 'important_input_list'
// Example of a *_flag_important input:
// 'quote___flag_important'       => array(
//     'input_type'  => 'nimblecheck',
//     'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
//     'default'     => 0,
//     'refresh_markup' => false,
//     'refresh_stylesheet' => true,
//     'title_width' => 'width-80',
//     'input_width' => 'width-20',
//     // declare the list of input_id that will be flagged with !important when the option is checked
//     // @see sek_add_css_rules_for_css_sniffed_input_id
//     // @see sek_is_flagged_important
//     'important_input_list' => array(
//         'quote_font_family_css',
//         'quote_font_size_css',
//         'quote_line_height_css',
//         'quote_font_weight_css',
//         'quote_font_style_css',
//         'quote_text_decoration_css',
//         'quote_text_transform_css',
//         'quote_letter_spacing_css',
//         'quote_color_css',
//         'quote_color_hover_css'
//     )
// )
function sek_is_flagged_important( $input_id, $module_value, $registered_input_list ) {
    $important = false;

    if ( !is_array( $registered_input_list ) || empty( $registered_input_list ) ) {
        sek_error_log( __FUNCTION__ . ' => error => the $registered_input_list param should be an array not empty');
        return $important;
    }

    // loop on the registered input list and try to find a *_flag_important input id
    foreach ( $registered_input_list as $_id => $input_data ) {
        if ( is_string( $_id ) && false !== strpos( $_id, '_flag_important' ) ) {
            if ( empty( $input_data[ 'important_input_list' ] ) ) {
                sek_error_log( __FUNCTION__ . ' => error => missing important_input_list for input id ' . $_id );
            } else {
                $important_list_candidate = $input_data[ 'important_input_list' ];
                if ( in_array( $input_id, $important_list_candidate ) ) {
                    $important = sek_booleanize_checkbox_val( sek_get_input_value_in_module_model( $_id, $module_value ) );
                }
            }
        }
    }
    return $important;
}
?><?php
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
?><?php
if ( !class_exists( 'SEK_Front_Ajax' ) ) :
    class SEK_Front_Ajax extends SEK_Front_Construct {
        // Fired in __construct()
        function _schedule_front_ajax_actions() {
            add_action( 'wp_ajax_sek_get_content', array( $this, 'sek_get_level_content_for_injection' ) );

            // Returns the customize url for the edit button when using Gutenberg editor
            // implemented for https://github.com/presscustomizr/nimble-builder/issues/449
            // @see assets/admin/js/nimble-gutenberg.js
            add_action( 'wp_ajax_sek_get_customize_url_for_nimble_edit_button', array( $this, 'sek_get_customize_url_for_nimble_edit_button' ) );

            // This is the list of accepted actions
            $this->ajax_action_map = array(
                  'sek-add-section',
                  'sek-remove-section',
                  'sek-duplicate-section',

                  // fired when dropping a module or a preset_section
                  'sek-add-content-in-new-nested-sektion',
                  'sek-add-content-in-new-sektion',

                  // add, duplicate, remove column is a re-rendering of the parent sektion collection
                  'sek-add-column',
                  'sek-remove-column',
                  'sek-duplicate-column',
                  'sek-resize-columns',
                  'sek-refresh-columns-in-sektion',

                  'sek-add-module',
                  'sek-remove-module',
                  'sek-duplicate-module',
                  'sek-refresh-modules-in-column',

                  'sek-refresh-stylesheet',

                  'sek-refresh-level'
            );
        }

        ////////////////////////////////////////////////////////////////
        // GENERIC HELPER FIRED IN ALL AJAX CALLBACKS
        // @param $params = array('check_nonce' => true )
        function sek_do_ajax_pre_checks( $params = array() ) {
            $params = wp_parse_args( $params, array( 'check_nonce' => true ) );
            if ( $params['check_nonce'] ) {
                $action = 'save-customize_' . get_stylesheet();
                if ( !check_ajax_referer( $action, 'nonce', false ) ) {
                     wp_send_json_error( array(
                        'code' => 'invalid_nonce',
                        'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' => check_ajax_referer() failed.' ),
                    ) );
                }
            }

            if ( !is_user_logged_in() ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
            }
            if ( !current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
            } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
            }
        }//sek_do_ajax_pre_checks()



        // hook : 'wp_ajax_sek_get_html_for_injection'
        function sek_get_level_content_for_injection( $params ) {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( !isset( $_POST['location_skope_id'] ) || empty( $_POST['location_skope_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
            }

            // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
            // september 2019
            // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
            // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
            // otherwise the preview UI can be broken
            if ( !isset( $_POST['preview-level-guid'] ) || empty( $_POST['preview-level-guid'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing preview-level-guid' );
            }

            if ( !isset( $_POST['sek_action'] ) || empty( $_POST['sek_action'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing sek_action' );
            }
            $sek_action = sanitize_text_field($_POST['sek_action']);

            $exported_setting_validities = array();

            // CHECK THE SETTING VALIDITIES BEFORE RENDERING
            // When a module has been registered with a sanitize_callback, we can collect the possible problems here before sending the response.
            // Then, on ajax.done(), in SekPreviewPrototype::schedulePanelMsgReactions, we will send the setting validities object to the panel
            if ( is_customize_preview() ) {
                global $wp_customize;
                // prepare the setting validities so we can pass them when sending the ajax response
                $setting_validities = $wp_customize->validate_setting_values( $wp_customize->unsanitized_post_values() );
                $raw_exported_setting_validities = array_map( array( $wp_customize, 'prepare_setting_validity_for_js' ), $setting_validities );

                // filter the setting validity to only keep the __nimble__ prefixed ui settings
                $exported_setting_validities = array();
                foreach( $raw_exported_setting_validities as $setting_id => $validity ) {
                    // don't consider the not Nimble UI settings, not starting with __nimble__
                    if ( false === strpos( $setting_id , NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED ) )
                      continue;
                    $exported_setting_validities[ $setting_id ] = $validity;
                }
            }

            $html = '';
            // is this action possible ?
            if ( in_array( $sek_action, $this->ajax_action_map ) ) {
                $content_type = null;
                if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                    $content_type = sanitize_text_field($_POST['content_type']);
                }

                // This 'preset_section' === $content_type statement has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                if ( 'preset_section' === $content_type ) {
                    switch ( $sek_action ) {
                        // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                        case 'sek-add-content-in-new-sektion' :
                        case 'sek-add-content-in-new-nested-sektion' :
                            if ( 'preset_section' === $content_type ) {
                                if ( !array_key_exists( 'collection_of_preset_section_id', $_POST ) || !is_array( $_POST['collection_of_preset_section_id'] ) || empty( $_POST['collection_of_preset_section_id'] ) ) {
                                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                                    break;
                                }
                                foreach ( $_POST['collection_of_preset_section_id'] as $preset_section_id ) {
                                    $html .= $this->sek_ajax_fetch_content( $sek_action, sanitize_text_field($preset_section_id ));
                                }
                            // 'module' === $content_type
                            } else {
                                $html = $this->sek_ajax_fetch_content( $sek_action );
                            }

                        break;

                        default :
                            $html = $this->sek_ajax_fetch_content( $sek_action );
                        break;
                    }
                } else {
                      $html = $this->sek_ajax_fetch_content( $sek_action );
                }

                //sek_error_log(__CLASS__ . '::' . __FUNCTION__ , $html );
                if ( is_wp_error( $html ) ) {
                    wp_send_json_error( $html );
                } else {
                    $response = array(
                        'contents' => $html,
                        'setting_validities' => $exported_setting_validities
                    );
                    wp_send_json_success( apply_filters( 'sek_content_results', $response, $sek_action ) );
                }
            } else {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => this ajax action ( ' . $sek_action . ' ) is not listed in the map ' );
            }


        }//sek_get_content_for_injection()


        // hook : add_filter( "sek_set_ajax_content___{$action}", array( $this, 'sek_ajax_fetch_content' ) );
        // $_POST looks like Array
        // (
        //     [action] => sek_get_content
        //     [withNonce] => false
        //     [id] => __nimble__0b7c85561448ab4eb8adb978
        //     [skope_id] => skp__post_page_home
        //     [sek_action] => sek-add-section
        //     [SEKFrontNonce] => 3713b8ac5c
        //     [customized] => {\"nimble___loop_start[skp__post_page_home]\":{...}}
        // )
        // @return string
        // @param $sek_action is $_POST['sek_action']
        // @param $maybe_preset_section_id is used when injecting a collection of preset sections
        private function sek_ajax_fetch_content( $sek_action = '', $maybe_preset_section_id = '' ) {
            //sek_error_log( __CLASS__ . '::' . __FUNCTION__  . ' POST ?', $_POST );
            // Important Notes :
            // 1) at this stage => the $_POST['customized'] has already been updated
            // so invoking sek_get_skoped_seks() will ensure that we get the latest data
            // How $_POST['customized'] is getting populated without a full refresh of the preview ?
            // a) Each time the main collection setting id is updated ( @see CZRSeksPrototype::mayBeUpdateSektionsSetting() ), api.Setting.prototype.preview sends a 'setting' event to the preview
            // ( note that api.Setting.prototype.preview is overriden by NB to send other events )
            // b) when the core customize-preview receives the event, it updates the customized dirties
            // c) then when ajaxing, the $_POST['customized'] param is added by WP core with $.ajaxPrefilter() in customize-preview.js
            //
            // 2) since 'wp' hook has not been fired yet, we need to use the posted skope_id param.
            $sektionSettingValue = sek_get_skoped_seks( sanitize_text_field($_POST['location_skope_id']) );
            if ( !is_array( $sektionSettingValue ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektionSettingValue => it should be an array().' );
                return;
            }
            if ( empty( $sek_action ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => invalid sek_action param' );
                return;
            }
            $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
            if ( !is_array( $sektion_collection ) ) {
                wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => invalid sektion_collection => it should be an array().' );
                return;
            }

            $candidate_id = '';
            $collection = array();
            $level_model = array();

            $is_stylesheet = false;

            switch ( $sek_action ) {
                case 'sek-add-section' :
                case 'sek-duplicate-section' :
                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( sanitize_text_field($_POST['is_nested']) ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    } else {
                        //$level_model = sek_get_level_model( $_POST[ 'id' ], $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'id' ]), $sektion_collection );
                    }
                break;

                // This $content_type var has been introduced when implementing support for multi-section pre-build sections
                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                // when 'sek-add-content-in-new-sektion' is fired, the section has already been populated with a column and a module
                case 'sek-add-content-in-new-sektion' :
                case 'sek-add-content-in-new-nested-sektion' :
                    $content_type = null;
                    if ( array_key_exists( 'content_type', $_POST ) && is_string( $_POST['content_type'] ) ) {
                        $content_type = sanitize_text_field($_POST['content_type']);
                    }
                    if ( 'preset_section' === $content_type ) {
                        if ( !array_key_exists( 'collection_of_preset_section_id', $_POST ) || !is_array( $_POST['collection_of_preset_section_id'] ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing param collection_of_preset_section_id when injecting a preset section' );
                            break;
                        }
                        if ( !is_string( $maybe_preset_section_id ) || empty( $maybe_preset_section_id ) ) {
                            wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => invalid preset section id' );
                            break;
                        }
                        $level_id = $maybe_preset_section_id;
                    // module content type case.
                    // the level id has been passed the regular way
                    } else {
                        $level_id = sanitize_text_field($_POST[ 'id' ]);
                    }

                    if ( array_key_exists( 'is_nested', $_POST ) && true === json_decode( sanitize_text_field($_POST['is_nested']) ) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    } else {
                        $level_model = sek_get_level_model( $level_id, $sektion_collection );
                    }
                break;

                //only used for nested section
                case 'sek-remove-section' :
                    if ( !array_key_exists( 'is_nested', $_POST ) || true !== json_decode( sanitize_text_field($_POST['is_nested'] )) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => the section must be nested in this ajax action' );
                        break;
                    } else {
                        // we need to set the parent_model here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_parent_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                        $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    }
                break;

                // We re-render the entire parent sektion collection in all cases
                case 'sek-add-column' :
                case 'sek-remove-column' :
                case 'sek-duplicate-column' :
                case 'sek-refresh-columns-in-sektion' :
                    if ( !array_key_exists( 'in_sektion', $_POST ) || empty( $_POST['in_sektion'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_sektion param' );
                        break;
                    }
                    // sek_error_log('sektion_collection', $sektion_collection );
                    $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                break;

                // We re-render the entire parent column collection
                case 'sek-add-module' :
                case 'sek-remove-module' :
                case 'sek-refresh-modules-in-column' :
                case 'sek-duplicate-module' :
                    if ( !array_key_exists( 'in_column', $_POST ) || empty( $_POST['in_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing in_column param' );
                        break;
                    }
                    if ( !array_key_exists( 'in_sektion', $_POST ) || empty( $_POST[ 'in_sektion' ] ) ) {
                        $this->parent_model = sek_get_parent_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                    } else {
                        $this->parent_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_sektion' ]), $sektion_collection );
                    }
                    $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'in_column' ]), $sektion_collection );
                break;

                case 'sek-resize-columns' :
                    if ( !array_key_exists( 'resized_column', $_POST ) || empty( $_POST['resized_column'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing resized_column' );
                        break;
                    }
                    $is_stylesheet = true;
                break;

                case 'sek-refresh-stylesheet' :
                    $is_stylesheet = true;
                break;

                 case 'sek-refresh-level' :
                    if ( !array_key_exists( 'id', $_POST ) || empty( $_POST['id'] ) ) {
                        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action .' => missing level id' );
                        break;
                    }
                    if ( !empty( $_POST['level'] ) && 'column' === sanitize_text_field($_POST['level']) ) {
                        // we need to set the parent_mode here to access it later in the ::render method to calculate the column width.
                        $this->parent_model = sek_get_parent_level_model( sanitize_text_field($_POST['id']), $sektion_collection );
                    }
                    $level_model = sek_get_level_model( sanitize_text_field($_POST[ 'id' ]), $sektion_collection );
                break;
            }//Switch sek_action

            // sek_error_log('LEVEL MODEL WHEN AJAXING', $level_model );

            ob_start();

            if ( $is_stylesheet ) {
                $r = $this->print_or_enqueue_seks_style( sanitize_text_field($_POST['location_skope_id']) );
            } else {
                if ( 'no_match' == $level_model ) {
                    wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' ' . $sek_action . ' => missing level model' );
                    ob_end_clean();
                    return;
                }
                if ( empty( $level_model ) || !is_array( $level_model ) ) {
                    wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => empty or invalid $level_model' );
                    ob_end_clean();
                    return;
                }
                // note that in the case of a sektion nested inside a column, the parent_model has been set in the switch{ case : ... } above ,so we can access it in the ::render method to calculate the column width.
                $r = $this->render( $level_model );
            }
            $html = ob_get_clean();
            if ( is_wp_error( $r ) ) {
                return $r;
            } else {
                // the $html content should not be empty when ajaxing a template
                // it can be empty when ajaxing a stylesheet
                if ( !$is_stylesheet && empty( $html ) ) {
                      // return a new WP_Error that will be intercepted in sek_get_level_content_for_injection
                      $html = new \WP_Error( 'ajax_fetch_content_error', __CLASS__ . '::' . __FUNCTION__ . ' => no content returned for sek_action : ' . $sek_action );
                }
                return apply_filters( "sek_set_ajax_content", $html, $sek_action );// this is sent with wp_send_json_success( apply_filters( 'sek_content_results', $html, $sek_action ) );
            }
        }



        ////////////////////////////////////////////////////////////////
        // USED TO PRINT THE BUTTON EDIT WITH NIMBLE ON POSTS AND PAGES
        // when using Gutenberg editor
        // implemented for https://github.com/presscustomizr/nimble-builder/issues/449
        function sek_get_customize_url_for_nimble_edit_button() {
            $this->sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

            if ( !isset( $_POST['nimble_edit_post_id'] ) || empty( $_POST['nimble_edit_post_id'] ) ) {
                wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing post_id' );
            }

            $post_id = sanitize_text_field($_POST['nimble_edit_post_id']);

            // Build customize_url
            // @see function sek_get_customize_url_when_is_admin()
            $return_url_after_customization = '';//"/wp-admin/post.php?post={$post_id}&action=edit";
            $customize_url = sek_get_customize_url_for_post_id( $post_id, $return_url_after_customization );
            wp_send_json_success( $customize_url );
        }
    }//class
endif;
?><?php
if ( !class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_assets_printing() {
            // Maybe adds `defer` support for scripts registered or enqueued
            // and for which we've added an attribute with sek_defer_script( $_hand, 'defer', true );
            // inspired from Twentytwenty WP theme
            // @see https://core.trac.wordpress.org/ticket/12009
            add_filter( 'script_loader_tag', array( $this, 'sek_filter_script_loader_tag' ), 10, 2 );

            add_action( 'template_redirect', array( $this, 'sek_check_if_page_has_nimble_content' ) );
            
            // Load Front CSS
            // Contextual stylesheets for local and global sections are loaded with ::print_or_enqueue_seks_style()
            // see inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_maybe_enqueue_front_css_assets' ) );

            // Load Front JS
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_js_assets' ) );

            // Scheduling loading of needed js files
            add_action( 'wp_head', array( $this, 'sek_main_front_js_preloading_when_not_customizing') );

            // If supported by the browser, woff2 fonts of Font Awesome will be preloaded
            add_action( 'wp_head', array( $this, 'sek_maybe_preload_fa_fonts') );
            // Maybe preload Font assets when really needed ( sniff first ) + nb_.listenTo('nb-needs-fa')
            add_action( 'wp_head', array( $this, 'sek_maybe_preload_front_assets_when_not_customizing' ), PHP_INT_MAX );

            add_action( 'wp_head', array( $this, 'sek_inline_init_js'), 0 );
        }//_schedule_front_and_preview_assets_printing


        //@template redirect
        function sek_check_if_page_has_nimble_content() {
            // do we have local or global sections to render in this page ?
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            // we know the skope_id because 'wp' has been fired
            // October 2020
            if ( 'not_set' === Nimble_Manager()->page_has_local_or_global_sections ) {
                Nimble_Manager()->page_has_local_or_global_sections = sek_local_skope_has_nimble_sections( skp_get_skope_id() ) || sek_has_global_sections();
            }
        }


        // hook : 'wp_enqueue_scripts'
        // Contextual stylesheets for local and global sections are loaded with ::print_or_enqueue_seks_style()
        // see inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
        function sek_maybe_enqueue_front_css_assets() {
            /* ------------------------------------------------------------------------- *
             *  MAIN STYLESHEET
            /* ------------------------------------------------------------------------- */
            // Oct 2020 => use sek-base ( which includes all module stylesheets ) if Nimble could not concatenate module stylesheets when generating the dynamic stylesheet
            // for https://github.com/presscustomizr/nimble-builder/issues/749
            if ( 'failed' === get_option(NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS) ) {
                $main_stylesheet_name = 'sek-base';
            } else {
                // the light split stylesheet is never used when customizing
                $main_stylesheet_name = !skp_is_customizing() ? 'sek-base-light' : 'sek-base';
            }


            // Always load the base Nimble style when user logged in so we can display properly the button in the top admin bar.
            if ( is_user_logged_in() || false != Nimble_Manager()->page_has_local_or_global_sections ) {
                $rtl_suffix = is_rtl() ? '-rtl' : '';

                //wp_enqueue_style( 'google-material-icons', '//fonts.googleapis.com/icon?family=Material+Icons', array(), null, 'all' );
                //base custom CSS bootstrap inspired
                wp_enqueue_style(
                    $main_stylesheet_name,
                    sprintf(
                        '%1$s/assets/front/css/%2$s' ,
                        NIMBLE_BASE_URL,
                        sek_is_dev_mode() ? "{$main_stylesheet_name}{$rtl_suffix}.css" : "{$main_stylesheet_name}{$rtl_suffix}.min.css"
                    ),
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    'all'
                );
            }


            /* ------------------------------------------------------------------------- *
             *  STOP HERE IF NOT CUSTOMIZING AND THERE IS NOTHING TO PRINT
            /* ------------------------------------------------------------------------- */
            // We don't need Nimble Builder assets when no local or global sections have been created
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
              return;


            /* ------------------------------------------------------------------------- *
             *  MODULE PARTIAL STYLESHEETS
            /* ------------------------------------------------------------------------- */
            // populate the collection of module displayed in current context : local + global
            // introduced march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            // formed like :
            // [czr_heading_module] => Array
            //     (
            //         [0] => __nimble__9a02775e86ec
            //         [1] => __nimble__01f1e8d56415
            //         [2] => __nimble__8fc8dac22299
            //         [3] => __nimble__b71c69fd674d
            //         [4] => __nimble__b74a63e1dc57
            //         [5] => __nimble__ca13a73ca586
            //         [6] => __nimble__e66b407f0f2b
            //         [7] => __nimble__7d6526ab1812
            //     )

            // [czr_img_slider_module] => Array
            //     (
            //         [0] => __nimble__3a38fe3587b2
            //     )

            // [czr_accordion_module] => Array
            //     (
            //         [0] => __nimble__ec3d7956fe17
            //     )

            // [czr_social_icons_module] => Array
            //     (
            //         [0] => __nimble__c1526193134e
            //     )
            $contextually_active_modules = sek_get_collection_of_contextually_active_modules();

            //sek_error_log('$contextually_active_modules ?', $contextually_active_modules );


            // public $big_module_stylesheet_map = [
            //     'czr_quote_module' => 'quote-module',
            //     'czr_icon_module' => 'icon-module',
            //     'czr_img_slider_module' => 'img-slider-module',
            //     'czr_accordion_module' => 'accordion-module',
            //     'czr_menu_module' => 'menu-module',
            //     'czr_post_grid_module' => 'post-grid-module',
            //     'czr_simple_form_module' => 'simple-form-module'
            // ];
            // SPLIT STYLESHEETS
            // introduced march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            // if the module stylesheets are inline, see wp_head action
            // October 2020 => modules stylesheets are now concatenated in the dynamically generated stylesheet
            // if ( !skp_is_customizing() && $is_stylesheet_split_for_performance ) {
            //     // loop on the map module type (candidates for split) => stylesheet file name
            //     foreach (Nimble_Manager()->big_module_stylesheet_map as $module_type => $stylesheet_name ) {
            //         if ( !array_key_exists($module_type , $contextually_active_modules ) )
            //           continue;

            //         wp_enqueue_style(
            //             $module_type,
            //             sprintf( '%1$s%2$s%3$s',
            //                 NIMBLE_BASE_URL . '/assets/front/css/modules/',
            //                 $stylesheet_name,
            //                 sek_is_dev_mode() ? '.css' : '.min.css'
            //             ),
            //             array( $main_stylesheet_name ),
            //             NIMBLE_ASSETS_VERSION,
            //             $media = 'all'
            //         );
            //     }
            // }


            /* ------------------------------------------------------------------------- *
             *  LIGHT BOX WITH MAGNIFIC POPUP
            /* ------------------------------------------------------------------------- */
            // the stylesheet is always preloaded on front
            if ( skp_is_customizing() ) {
                wp_enqueue_style(
                    'nb-swipebox',
                    NIMBLE_BASE_URL . '/assets/front/css/libs/swipebox.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }

            /* ------------------------------------------------------------------------- *
             *  SWIPER FOR SLIDERS
            /* ------------------------------------------------------------------------- */
            if ( array_key_exists('czr_img_slider_module' , $contextually_active_modules) || skp_is_customizing() ) {
                // march 2020 :
                // when loading assets in ajax, swiper stylesheet is loaded dynamically
                // so we don't need to enqueue it
                // added for https://github.com/presscustomizr/nimble-builder/issues/612
                // added for https://github.com/presscustomizr/nimble-builder/issues/635
                if ( skp_is_customizing() || !sek_load_front_assets_dynamically() ) {
                      wp_enqueue_style(
                          'nb-swiper',
                          sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/css/libs/swiper-bundle.css' : NIMBLE_BASE_URL . '/assets/front/css/libs/swiper-bundle.min.css',
                          array(),
                          NIMBLE_ASSETS_VERSION,
                          $media = 'all'
                      );
                }
            }

            /* ------------------------------------------------------------------------- *
             *  FONT AWESOME STYLESHEET
            /* ------------------------------------------------------------------------- */
            if ( skp_is_customizing() ) {
                wp_enqueue_style(
                    'nb-font-awesome',
                    NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }

        }//sek_enqueue_front_assets

        // @wp_head 0
        function sek_inline_init_js() {
          // Nimble-init  
          if ( skp_is_customizing() || Nimble_Manager()->page_has_local_or_global_sections ) {
            // Minified version of assets/front/nimble-init.js
            ob_start();
            ?>
window.nb_={},function(e,t){if(window.nb_={isArray:function(e){return Array.isArray(e)||"[object Array]"===toString.call(e)},inArray:function(e,t){return!(!nb_.isArray(e)||nb_.isUndefined(t))&&e.indexOf(t)>-1},isUndefined:function(e){return void 0===e},isObject:function(e){var t=typeof e;return"function"===t||"object"===t&&!!e},errorLog:function(){nb_.isUndefined(console)||"function"!=typeof window.console.log||console.log.apply(console,arguments)},hasPreloadSupport:function(e){var t=document.createElement("link").relList;return!(!t||!t.supports)&&t.supports("preload")},listenTo:function(e,t){nb_.eventsListenedTo.push(e);var n={"nb-jquery-loaded":function(){return"undefined"!=typeof jQuery},"nb-app-ready":function(){return void 0!==window.nb_&&nb_.wasListenedTo("nb-jquery-loaded")},"nb-swipebox-parsed":function(){return"undefined"!=typeof jQuery&&void 0!==jQuery.fn.swipebox},"nb-main-swiper-parsed":function(){return void 0!==window.Swiper}},o=function(o){nb_.isUndefined(n[e])||!1!==n[e]()?t():nb_.errorLog("Nimble error => an event callback could not be fired because conditions not met => ",e,nb_.eventsListenedTo,t)};"function"==typeof t?nb_.wasEmitted(e)?o():document.addEventListener(e,o):nb_.errorLog("Nimble error => listenTo func param is not a function for event => ",e)},eventsEmitted:[],eventsListenedTo:[],emit:function(e,t){if(!(nb_.isUndefined(t)||t.fire_once)||!nb_.wasEmitted(e)){var n=document.createEvent("Event");n.initEvent(e,!0,!0),document.dispatchEvent(n),nb_.eventsEmitted.push(e)}},wasListenedTo:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsListenedTo,e)},wasEmitted:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsEmitted,e)},isInScreen:function(e){if(!nb_.isObject(e))return!1;var t=e.getBoundingClientRect(),n=Math.max(document.documentElement.clientHeight,window.innerHeight);return!(t.bottom<0||t.top-n>=0)},isCustomizing:function(){return!1},isLazyLoadEnabled:function(){return!nb_.isCustomizing()&&!1},preloadOrDeferAsset:function(e){if(e=e||{},nb_.preloadedAssets=nb_.preloadedAssets||[],!nb_.inArray(nb_.preloadedAssets,e.id)){var t,n=document.getElementsByTagName("head")[0],o=function(){if("style"===e.as)this.setAttribute("rel","stylesheet"),this.setAttribute("type","text/css"),this.setAttribute("media","all");else{var t=document.createElement("script");t.setAttribute("src",e.href),t.setAttribute("id",e.id),"script"===e.as&&t.setAttribute("defer","defer"),n.appendChild(t),i.call(this)}e.eventOnLoad&&nb_.emit(e.eventOnLoad)},i=function(){if(this&&this.parentNode&&this.parentNode.contains(this))try{this.parentNode.removeChild(this)}catch(e){nb_.errorLog("NB error when removing a script el",el)}};("font"!==e.as||nb_.hasPreloadSupport())&&(t=document.createElement("link"),"script"===e.as?e.onEvent?nb_.listenTo(e.onEvent,function(){o.call(t)}):o.call(t):(t.setAttribute("href",e.href),"style"===e.as?t.setAttribute("rel",nb_.hasPreloadSupport()?"preload":"stylesheet"):"font"===e.as&&nb_.hasPreloadSupport()&&t.setAttribute("rel","preload"),t.setAttribute("id",e.id),t.setAttribute("as",e.as),"font"===e.as&&(t.setAttribute("type",e.type),t.setAttribute("crossorigin","anonymous")),t.onload=function(){this.onload=null,"font"!==e.as?e.onEvent?nb_.listenTo(e.onEvent,function(){o.call(t)}):o.call(t):e.eventOnLoad&&nb_.emit(e.eventOnLoad)},t.onerror=function(t){nb_.errorLog("Nimble preloadOrDeferAsset error",t,e)}),n.appendChild(t),nb_.preloadedAssets.push(e.id),i.call(e.scriptEl))}},mayBeRevealBG:function(){this.getAttribute("data-sek-src")&&(this.setAttribute("style",'background-image:url("'+this.getAttribute("data-sek-src")+'")'),this.className+=" sek-lazy-loaded",this.querySelectorAll(".sek-css-loader").forEach(function(e){nb_.isObject(e)&&e.parentNode.removeChild(e)}))}},window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(e,t){t=t||window;for(var n=0;n<this.length;n++)e.call(t,this[n],n,this)}),nb_.listenTo("nb-docready",function(){var e=document.querySelectorAll("div.sek-has-bg");!nb_.isObject(e)||e.length<1||e.forEach(function(e){nb_.isObject(e)&&(window.sekFrontLocalized&&window.sekFrontLocalized.lazyload_enabled?nb_.isInScreen(e)&&nb_.mayBeRevealBG.call(e):nb_.mayBeRevealBG.call(e))})}),"complete"===document.readyState||"loading"!==document.readyState&&!document.documentElement.doScroll)nb_.emit("nb-docready");else{var n=function(){nb_.wasEmitted("nb-docready")||nb_.emit("nb-docready")};document.addEventListener("DOMContentLoaded",n),window.addEventListener("load",n)}}(window,document),function(){var e=function(){var e="nb-jquery-loaded";nb_.wasEmitted(e)||nb_.emit(e)},t=function(n){n=n||0,void 0!==window.jQuery?e():n<30?setTimeout(function(){t(++n)},200):window.console&&window.console.log&&console.log("Nimble Builder problem : jQuery.js was not detected on your website")},n=document.getElementById("nb-jquery");n&&n.addEventListener("load",function(){e()}),t()}(),window,document,nb_.listenTo("nb-jquery-loaded",function(){sekFrontLocalized.load_front_assets_on_dynamically&&(nb_.scriptsLoadingStatus={},nb_.ajaxLoadScript=function(e){jQuery(function(t){e=t.extend({path:"",complete:"",loadcheck:!1},e),nb_.scriptsLoadingStatus[e.path]&&"pending"===nb_.scriptsLoadingStatus[e.path].state()||(nb_.scriptsLoadingStatus[e.path]=nb_.scriptsLoadingStatus[e.path]||t.Deferred(),jQuery.ajax({url:sekFrontLocalized.frontAssetsPath+e.path+"?"+sekFrontLocalized.assetVersion,cache:!0,dataType:"script"}).done(function(){"function"!=typeof e.loadcheck||e.loadcheck()?"function"==typeof e.complete&&e.complete():nb_.errorLog("ajaxLoadScript success but loadcheck failed for => "+e.path)}).fail(function(){nb_.errorLog("ajaxLoadScript failed for => "+e.path)}))})})}),nb_.listenTo("nb-jquery-loaded",function(){jQuery(function(e){sekFrontLocalized.load_front_assets_on_dynamically&&(nb_.ajaxLoadScript({path:sekFrontLocalized.isDevMode?"js/ccat-nimble-front.js":"js/ccat-nimble-front.min.js"}),e.each(sekFrontLocalized.partialFrontScripts,function(e,t){nb_.listenTo(t,function(){nb_.ajaxLoadScript({path:sekFrontLocalized.isDevMode?"js/partials/"+e+".js":"js/partials/"+e+".min.js"})})}))})});
            <?php
            $init_script = ob_get_clean();
            wp_register_script( 'nb-js-app', '');
            wp_enqueue_script( 'nb-js-app' );
            wp_add_inline_script( 'nb-js-app', $init_script );
          }
        }


        //@'wp_enqueue_scripts'
        // ==>>> when not customizing, all js assets but nb-js-init are injected with javascript <<===
        
        // Loading sequence :
        // 1) window.nb_ utils starts being populated
        // 2) 'nb-jquery-loaded' => fired in footer when jQuery is defined <= window.nb_ utils is completed with jQuery dependant helper properties and methods
        // 3) 'nb-app-ready' => fired in footer on 'nb-jquery-loaded' <= all module scripts are fired on this event
        // 4) 'nb-{js-library}-parsed', ... are emitted in each script files
        function sek_enqueue_front_js_assets() {
            if ( !skp_is_customizing() ) {
              wp_enqueue_script('jquery');
            }
            if ( skp_is_customizing() || Nimble_Manager()->page_has_local_or_global_sections ) {
                // Google reCAPTCHA
                $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
                $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();
                $l10n = array(
                    'isDevMode' => sek_is_dev_mode(),
                    'isCustomizing' => skp_is_customizing(),
                    //'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
                    // 'localSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks() ) : '',
                    // 'globalSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID ) ) : '',
                    'skope_id' => skp_get_skope_id(), //added for debugging purposes
                    'recaptcha_public_key' => !empty( $global_recaptcha_opts['public_key'] ) ? $global_recaptcha_opts['public_key'] : '',

                    'lazyload_enabled' => sek_is_img_smartload_enabled(),
                    'video_bg_lazyload_enabled' => sek_is_video_bg_lazyload_enabled(),
                    'load_front_assets_on_dynamically' => sek_load_front_assets_dynamically(),

                    'assetVersion' => NIMBLE_ASSETS_VERSION,
                    'frontAssetsPath' => NIMBLE_BASE_URL . '/assets/front/',
                    'contextuallyActiveModules' => sek_get_collection_of_contextually_active_modules(),
                    'fontAwesomeAlreadyEnqueued' => wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued'),

                    'partialFrontScripts' => Nimble_Manager()->partial_front_scripts,

                    // Debug for https://github.com/presscustomizr/nimble-builder/issues/795
                    // 'debug' => [
                    //   'nb_debug_save' => get_transient('nb_debug_save'),
                    //   'nb_debug_get' => get_transient('nb_debug_get')
                    // ]
                );
                $l10n = apply_filters( 'nimble-localized-js-front', $l10n );
                wp_localize_script( 'nb-js-app', 'sekFrontLocalized',$l10n);
            }
          


            // When not customizing, the main and partial front scripts are loaded only when needed on front, with nb_.listenTo('{event_name}')
            // When customizing, we need to enqueue them the regular way
            if ( !skp_is_customizing() )
              return;
            /* ------------------------------------------------------------------------- *
            *  FRONT MAIN SCRIPT
            /* ------------------------------------------------------------------------- */
            wp_enqueue_script(
                'nb-main-js',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.js' : NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.min.js',
                array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                false
            );
            // added for https://github.com/presscustomizr/nimble-builder/issues/583
            sek_defer_script('nb-main-js');


            /* ------------------------------------------------------------------------- *
             *  FRONT PARTIAL SCRIPTS
            /* ------------------------------------------------------------------------- */
            // public $partial_front_scripts = [
            //     'slider-module' => 'nb-needs-swiper',
            //     'menu-module' => 'nb-needs-menu-js',
            //     'front-parallax' => 'nb-needs-parallax',
            //     'accordion-module' => 'nb-needs-accordion'
            // ];
            foreach (Nimble_Manager()->partial_front_scripts as $name => $event) {
                $handle = "nb-{$name}";
                wp_enqueue_script(
                    $handle,
                    sprintf('%1$s/assets/front/js/partials/%2$s.%3$s', NIMBLE_BASE_URL, $name, sek_is_dev_mode() ? 'js' : 'min.js'),
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    false
                );
                sek_defer_script($handle);
            }



            /* ------------------------------------------------------------------------- *
             *  LIGHT BOX WITH MAGNIFIC POPUP
            /* ------------------------------------------------------------------------- */
            wp_enqueue_script(
                'nb-swipebox',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-swipebox.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-swipebox.min.js',
                array(),
                NIMBLE_ASSETS_VERSION,
                false
            );
            sek_defer_script('nb-swipebox');


            /* ------------------------------------------------------------------------- *
             *  SWIPER FOR SLIDERS
             /* ------------------------------------------------------------------------- */
            // SWIPER JS LIB + MODULE SCRIPT
            // Swiper js is needed for the czr_img_slider_module
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            wp_enqueue_script(
                'nb-swiper',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/swiper-bundle.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/swiper-bundle.min.js',
                array(),
                NIMBLE_ASSETS_VERSION,
                false
            );
            // not added when customizing
            sek_defer_script('nb-swiper');


            /* ------------------------------------------------------------------------- *
             *  VIDEO BG
             /* ------------------------------------------------------------------------- */
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            wp_enqueue_script(
                'nb-video-bg-plugin',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-video-bg.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-video-bg.min.js',
                array(),
                NIMBLE_ASSETS_VERSION,
                false
            );
            // not added when customizing
            sek_defer_script('nb-video-bg-plugin');
        }//sek_enqueue_front_js_assets













        //@wp_head
        // ==>>> when customizing, all assets are enqueued the wp way <<===
        function sek_main_front_js_preloading_when_not_customizing() {
            if ( skp_is_customizing() )
              return;
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            if ( sek_load_front_assets_dynamically() )
              return;
            
            // Load main script on nb-docready event
            $script_url = sprintf('%1$s/assets/front/js/ccat-nimble-front.%2$s?v=%3$s', NIMBLE_BASE_URL, sek_is_dev_mode() ? 'js' : 'min.js', NIMBLE_ASSETS_VERSION);
            ob_start();
            ?>
            nb_.listenTo('nb-docready', function() {
                nb_.preloadOrDeferAsset( {
                  id : 'nb-main-js',
                  as : 'script',
                  href : "<?php echo esc_url($script_url); ?>",
                  scriptEl : document.getElementById('<?php echo "nb-load-main-script"; ?>')
                });
            });
            <?php


            // Schedule loading of partial scripts
            $partial_front_scripts = Nimble_Manager()->partial_front_scripts;
            foreach ($partial_front_scripts as $name => $event) {
                $url = sprintf('%1$s/assets/front/js/partials/%2$s.%3$s?v=%4$s', NIMBLE_BASE_URL, $name, sek_is_dev_mode() ? 'js' : 'min.js', NIMBLE_ASSETS_VERSION);
                ?>
                nb_.listenTo('<?php echo esc_attr($event); ?>', function() {
                    nb_.preloadOrDeferAsset( {
                      id : "<?php echo esc_attr($name); ?>",
                      as : 'script',
                      href : "<?php echo esc_url($url); ?>",
                      scriptEl : document.getElementById('<?php echo esc_attr("nb-load-script-{$name}"); ?>')
                    });
                });
                <?php
            }
            $script = ob_get_clean();
            wp_register_script( 'nb_main_front_js_preloading', '');
            wp_enqueue_script( 'nb_main_front_js_preloading' );
            wp_add_inline_script( 'nb_main_front_js_preloading', $script );
        }





        //@wp_footer
        // preload is applied when 'load_assets_in_ajax' is not active
        // ==>>> when customizing, all assets are enqueued the wp way <<===
        function sek_maybe_preload_front_assets_when_not_customizing() {
            if ( skp_is_customizing() )
              return;

            // Check that current page has Nimble content before printing anything
            // For https://github.com/presscustomizr/nimble-builder/issues/649
            // When customizing, all assets are enqueued the WP way
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;

            // When we load assets in ajax, we stop here
            if ( sek_load_front_assets_dynamically() )
              return;

            /* ------------------------------------------------------------------------- *
             *  PRELOAD FRONT SCRIPT
            /* ------------------------------------------------------------------------- */
            $assets_urls = [
                'nb-swipebox' => sek_is_dev_mode() ? '/assets/front/js/libs/jquery-swipebox.js' : '/assets/front/js/libs/jquery-swipebox.min.js',
                'nb-swiper' => sek_is_dev_mode() ? '/assets/front/js/libs/swiper-bundle.js' : '/assets/front/js/libs/swiper-bundle.min.js',
                'nb-video-bg-plugin' => sek_is_dev_mode() ? '/assets/front/js/libs/nimble-video-bg.js' : '/assets/front/js/libs/nimble-video-bg.min.js',

                'nb-swipebox-style' => '/assets/front/css/libs/swipebox.min.css',
            ];

            // add version
            foreach( $assets_urls as $k => $path ) {
                $assets_urls[$k] = NIMBLE_BASE_URL .$path .'?'.NIMBLE_ASSETS_VERSION;
            }
            ob_start();
            ?>
            nb_.listenTo('nb-needs-swipebox', function() {
                nb_.preloadOrDeferAsset( {
                    id : 'nb-swipebox',
                    as : 'script',
                    href : "<?php echo esc_url($assets_urls['nb-swipebox']); ?>",
                    onEvent : 'nb-docready',
                    // scriptEl : document.currentScript
                });
                nb_.preloadOrDeferAsset( {
                  id : 'nb-swipebox-style',
                  as : 'style',
                  href : "<?php echo esc_url($assets_urls['nb-swipebox-style']); ?>",
                  onEvent : 'nb-docready',
                  // scriptEl : document.currentScript
                });
            });

            nb_.listenTo('nb-needs-swiper', function() {
                nb_.preloadOrDeferAsset( {
                    id : 'nb-swiper',
                    as : 'script',
                    href : "<?php echo esc_url($assets_urls['nb-swiper']); ?>",
                    onEvent : 'nb-docready',
                    // scriptEl : document.currentScript
                });
            });
            nb_.listenTo('nb-needs-videobg-js', function() {
                nb_.preloadOrDeferAsset( {
                    id : 'nb-video-bg-plugin',
                    as : 'script',
                    href : "<?php echo esc_url($assets_urls['nb-video-bg-plugin']); ?>",
                    onEvent : 'nb-docready',
                    // scriptEl : document.currentScript
                });
            });
            <?php

            /* ------------------------------------------------------------------------- *
             *  FONT AWESOME
            /* ------------------------------------------------------------------------- */
            // if active theme is Hueman or Customizr, Font Awesome may already been enqueued.
            // asset handle for Customizr => 'customizr-fa'
            // asset handle for Hueman => 'hueman-font-awesome'
            if ( !wp_style_is('customizr-fa', 'enqueued') && !wp_style_is('hueman-font-awesome', 'enqueued') ) {
                // Font awesome is always loaded when customizing
                ?>
                <?php $fa_style_url = NIMBLE_BASE_URL .'/assets/front/fonts/css/fontawesome-all.min.css?'.NIMBLE_ASSETS_VERSION; ?>
                nb_.listenTo('nb-needs-fa', function() {
                    nb_.preloadOrDeferAsset( {
                      id : 'nb-font-awesome',
                      as : 'style',
                      href : "<?php echo esc_url($fa_style_url); ?>",
                      onEvent : 'nb-docready',
                      scriptEl : document.currentScript
                    });
                });
                <?php
            }
            
            $script = ob_get_clean();
            wp_register_script( 'nb_preload_front_assets', '');
            wp_enqueue_script( 'nb_preload_front_assets' );
            wp_add_inline_script( 'nb_preload_front_assets', $script );
        }



        // hook : wp_head
        // October 2020 Better preload implementation
        // As explained here https://stackoverflow.com/questions/49268352/preload-font-awesome
        // FA fonts can be preloaded. the crossorigin param has to be added
        // => this removes Google Speed tests message "preload key requests"
        // important => the url of the font must be exactly the same as in font awesome stylesheet, including the query param at the end fa-brands-400.woff2?5.15.2
        // note that we could preload all other types available ( eot, woff, ttf, svg )
        // but NB focus on preloading woff2 which is the type used by most recent browsers
        // see https://css-tricks.com/snippets/css/using-font-face/
        function sek_maybe_preload_fa_fonts() {
            if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            $fonts = [
                'fa-brands' => 'fa-brands-400.woff2?v=5.15.2',
                'fa-regular' => 'fa-regular-400.woff2?v=5.15.2',
                'fa-solid' => 'fa-solid-900.woff2?v=5.15.2'
            ];
            ob_start();
            ?>
              <?php foreach( $fonts as $id => $name ) : ?>
                <?php $font_url = NIMBLE_BASE_URL .'/assets/front/fonts/webfonts/'.$name; ?>
                  nb_.listenTo('nb-needs-fa', function() {
                      nb_.preloadOrDeferAsset( {
                        id : "<?php echo esc_attr($id); ?>",
                        as : 'font',
                        href : "<?php echo esc_url($font_url); ?>",
                        type : 'font/woff2',
                        //onEvent : 'nb-docready',
                        //eventOnLoad : 'nb-font-awesome-preloaded',
                        scriptEl : document.getElementById('<?php echo esc_attr("nb-load-{$id}"); ?>')
                      });
                  });
              <?php endforeach; ?>
            <?php
            $script = ob_get_clean();
            wp_add_inline_script( 'nb-js-init', $script );
        }


        /**
         * Fired @'script_loader_tag'
         * Adds async/defer attributes to enqueued / registered scripts.
         * works with sek_defer_script()
         * see https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
         * based on a solution found in Twentytwenty
         * and for which we've added an attribute with sek_defer_script( $_hand, 'defer', true );
         * If #12009 lands in WordPress, this function can no-op since it would be handled in core.
         *
         * @param string $tag    The script tag.
         * @param string $handle The script handle.
         * @return string Script HTML string.
        */
        public function sek_filter_script_loader_tag( $tag, $handle ) {
            // adds an id to jquery core so we can detect when it's loaded
            if ( 'jquery-core' === $handle ) {
                // tag is a string and looks like script src='http://customizr-dev.test/wp-includes/js/jquery/jquery-migrate.js?ver=1.4.1'></script>
                $tag = str_replace('src=', 'id="nb-jquery" src=', $tag);
            }

            // if ( skp_is_customizing() )
            //   return $tag;

            foreach ( [ 'async', 'defer' ] as $attr ) {
              if ( !wp_scripts()->get_data( $handle, $attr ) ) {
                continue;
              }
              // Prevent adding attribute when already added in #12009.
              if ( !preg_match( ":\s$attr(=|>|\s):", $tag ) ) {
                $tag = preg_replace( ':(?=></script>):', " $attr", $tag, 1 );
              }
              // Only allow async or defer, not both.
              break;
            }
            return $tag;
        }
    }//class
endif;
?><?php
if ( !class_exists( 'SEK_Front_Assets_Customizer_Preview' ) ) :
    class SEK_Front_Assets_Customizer_Preview extends SEK_Front_Assets {
        // Fired in __construct()
        function _schedule_preview_assets_printing() {
            add_action( 'wp_footer', array( $this, 'sek_customizr_js_stuff' ), PHP_INT_MAX  );

            // Load customize preview js
            add_action( 'customize_preview_init', array( $this, 'sek_schedule_customize_preview_assets' ) );
        }//_schedule_preview_assets_printing

        // @'wp_footer'
        function sek_customizr_js_stuff() {
            if ( !sek_current_user_can_access_nb_ui() )
              return;
            if( !skp_is_customizing() )
              return;

            ob_start();
            ?>
            (function(w, d){
      nb_.listenTo( 'nb-app-ready', function() {
          //PREVIEWED DEVICE ?
          //Listen to the customizer previewed device
          var _setPreviewedDevice = function() {
                wp.customize.preview.bind( 'previewed-device', function( device ) {
                      nb_.previewedDevice = device;// desktop, tablet, mobile
                });
          };
          if ( wp.customize.preview ) {
              _setPreviewedDevice();
          } else {
                wp.customize.bind( 'preview-ready', function() {
                      _setPreviewedDevice();
                });
          }
          // REVEAL BG IMAGE ON CHANGE ?
          jQuery( function($) {
              $('body').on( 'sek-level-refreshed', '[data-sek-level="location"]', function( evt, params  ) {
                    var matches = document.querySelectorAll('div.sek-has-bg');
                    if ( !nb_.isObject( matches ) || matches.length < 1 )
                      return;

                    var imgSrc;
                    matches.forEach( function(el) {
                        if ( !nb_.isObject(el) )
                          return;

                        // Maybe reveal BG if lazyload is on
                        if ( nb_.isCustomizing() ) {
                            nb_.mayBeRevealBG.call(el);
                        }
                    });
              });
          });
      });
}(window, document));
            <?php
            $script = ob_get_clean();
            wp_add_inline_script( 'nb-js-init', $script );
        }

        // enqueue / print customize preview assets
        // hook : 'customize_preview_init'
        function sek_schedule_customize_preview_assets() {
            if ( !sek_current_user_can_access_nb_ui() )
              return;

            // we don't need those assets when previewing a customize changeset
            // added when fixing https://github.com/presscustomizr/nimble-builder/issues/351
            if ( sek_is_customize_previewing_a_changeset_post() )
              return;

            // Load preview ui js tmpl
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                sprintf(
                    '%1$s/assets/czr/sek/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'sek-preview.css' : 'sek-preview.min.css'
                ),
                array( 'sek-base' ),
                NIMBLE_ASSETS_VERSION,
                'all'
            );

            // Communication between preview and customizer panel
            wp_enqueue_script(
                'sek-customize-preview',
                sprintf(
                    '%1$s/assets/czr/sek/js/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'ccat-sek-preview.js' : 'ccat-sek-preview.min.js'
                ),
                array( 'customize-preview', 'underscore'),
                NIMBLE_ASSETS_VERSION,
                true
            );

            wp_localize_script(
                'sek-customize-preview',
                'sekPreviewLocalized',
                array(
                    'i18n' => array(
                        "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_doma'),
                        "Moving elements between global and local sections is not allowed." => __( "Moving elements between global and local sections is not allowed.", 'text_doma'),
                        'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_doma'),
                        'Insert here' => __('Insert here', 'text_doma'),
                        'This content has been created with the WordPress editor.' => __('This content has been created with the WordPress editor.', 'text_domain' ),

                        'Insert a new section' => __('Insert a new section', 'text_doma' ),
                        '@location' => __('@location', 'text_domain_to_be'),
                        'Insert a new global section' => __('Insert a new global section', 'text_doma' ),

                        'section' => __('section', 'text_doma'),
                        'header section' => __('header section', 'text_doma'),
                        'footer section' => __('footer section', 'text_doma'),
                        '(global)' => __('(global)', 'text_doma'),
                        'nested section' => __('nested section', 'text_doma'),

                        'Shift-click to visit the link' => __('Shift-click to visit the link', 'text_doma'),
                        'External links are disabled when customizing' => __('External links are disabled when customizing', 'text_doma'),
                        'Link deactivated while previewing' => __('Link deactivated while previewing', 'text_doma')
                    ),
                    'isDevMode' => sek_is_dev_mode(),
                    'isPreviewUIDebugMode' => isset( $_GET['preview_ui_debug'] ) || NIMBLE_IS_PREVIEW_UI_DEBUG_MODE,
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),

                    'registeredModules' => CZR_Fmk_Base()->registered_modules,

                    // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
                    // september 2019
                    // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
                    // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
                    // when generating the ui, we check if the localized guid matches the one rendered server side
                    // otherwise the preview UI can be broken
                    'previewLevelGuid' => $this->sek_get_preview_level_guid(),

                    // Assets id
                    'googleFontsStyleId' => NIMBLE_GOOGLE_FONTS_STYLESHEET_ID,
                    'globalOptionsStyleId' => NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID
                )
            );

            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_style(
                'ui-sortable',
                '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
                array(),
                null,//time(),
                $media = 'all'
            );
            wp_enqueue_script( 'jquery-ui-resizable' );
        }


        //'wp_footer' in the preview frame
        function sek_print_ui_tmpl() {
            ?>
              <script type="text/html" id="sek-tmpl-add-content-button">
                  <# //console.log( 'data', data ); #>
                  <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                    <div class="sek-add-content-button-wrapper">
                     <# var hook_location = '', btn_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['Insert a new section'] : sekPreviewLocalized.i18n['Insert a new global section'], addContentBtnWidth = true !== data.is_global_location ? '83px' : '113px' #>
                      <# if ( data.location ) {
                          hook_location = ['(' , sekPreviewLocalized.i18n['@location'] , ':"',data.location , '")'].join('');
                      } #>
                      <button title="{{btn_title}} {{hook_location}}" data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:{{addContentBtnWidth}};">
                        <span class="sek-click-on-button-icon sek-click-on">+</span><span class="action-button-text">{{btn_title}}</span>
                      </button>
                    </div>
                  </div>
              </script>

              <?php
                  $icon_right_side_class = is_rtl() ? 'sek-dyn-left-icons' : 'sek-dyn-right-icons';
                  $icon_left_side_class = is_rtl() ? 'sek-dyn-right-icons' : 'sek-dyn-left-icons';
              ?>

              <script type="text/html" id="sek-dyn-ui-tmpl-section">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-dyn-ui-wrapper sek-section-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo esc_attr($icon_left_side_class); ?>">
                      <div class="sek-dyn-ui-icons">

                        <?php if ( sek_is_dev_mode() ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <?php
                          // Code before implementing https://github.com/presscustomizr/nimble-builder/issues/521 :
                          /* <# if ( true !== data.is_first_section_in_parent ) { #>
                          <i data-sek-click-on="move-section-up" class="material-icons sek-click-on" title="<?php _e( 'Move section up', 'text_domain' ); ?>">keyboard_arrow_up</i>
                        <# } #>
                        <# if ( true !== data.is_last_section_in_parent ) { #>
                          <i data-sek-click-on="move-section-down" class="material-icons sek-click-on" title="<?php _e( 'Move section down', 'text_domain' ); ?>">keyboard_arrow_down</i>
                        <# } #>*/
                        ?>
                        <i data-sek-click-on="move-section-up" class="material-icons sek-click-on" title="<?php _e( 'Move section up', 'text_domain' ); ?>">keyboard_arrow_up</i>
                        <i data-sek-click-on="move-section-down" class="material-icons sek-click-on" title="<?php _e( 'Move section down', 'text_domain' ); ?>">keyboard_arrow_down</i>


                        <?php // if this is a nested section, it has the is_nested property set to true. We don't want to make it draggable for the moment. @todo ?>
                        <# if ( !data.is_nested ) { #>
                          <# if ( true !== data.is_global_location ) { #>
                            <i class="fas fa-arrows-alt sek-move-section" title="<?php _e( 'Drag section', 'text_domain' ); ?>"></i>
                           <# } #>
                        <# } #>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">tune</i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-click-on="add-column" class="material-icons sek-click-on" title="<?php _e( 'Add a column', 'text_domain' ); ?>">view_column</i>
                        <# } #>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate section', 'text_domain' ); ?>">filter_none</i>
                        <i data-sek-click-on="toggle-save-section-ui" class="sek-save far fa-save" title="<?php _e( 'Save this section', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove section', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <#
                          var section_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['section'] : sekPreviewLocalized.i18n['section (global)'];
                          var section_title = !data.is_nested ? sekPreviewLocalized.i18n['section'] : sekPreviewLocalized.i18n['nested section'];
                          if ( true === data.is_header_location && !data.is_nested ) {
                                section_title = sekPreviewLocalized.i18n['header section'];
                          } else if ( true === data.is_footer_location && !data.is_nested ) {
                                section_title = sekPreviewLocalized.i18n['footer section'];
                          }

                          section_title = true !== data.is_global_location ? section_title : [ section_title, sekPreviewLocalized.i18n['(global)'] ].join(' ');
                        #>
                        <div class="sek-dyn-ui-level-type">{{section_title}}</div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <?php
                    // when a column has nested section(s), its ui might be hidden by deeper columns.
                    // that's why a CSS class is added to position it on the top right corner, instead of bottom right
                    // @see https://github.com/presscustomizr/nimble-builder/issues/488
                  ?>
                  <# var has_nested_section_class = true === data.has_nested_section ? 'sek-col-has-nested-section' : ''; #>
                  <div class="sek-dyn-ui-wrapper sek-column-dyn-ui {{has_nested_section_class}}">
                    <div class="sek-dyn-ui-inner <?php echo esc_attr($icon_right_side_class); ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-column" title="<?php _e( 'Move column', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">tune</i>
                        <# if ( !data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-click-on="add-section" class="material-icons sek-click-on" title="<?php _e( 'Add a nested section', 'text_domain' ); ?>">account_balance_wallet</i>
                        <# } #>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate column', 'text_domain' ); ?>">filter_none</i>
                        <# } #>

                        <i data-sek-click-on="pick-content" data-sek-content-type="module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <# if ( !data.parent_is_single_column ) { #>
                          <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove column', 'text_domain' ); ?>">delete_forever</i>
                        <# } #>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type"><?php _e( 'column', 'text_domain' ); ?></div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-module">
                  <div class="sek-dyn-ui-wrapper sek-module-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo esc_attr($icon_left_side_class); ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-module" title="<?php _e( 'Move module', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-module" class="fas fa-pencil-alt sek-tip sek-click-on" title="<?php _e( 'Edit module content', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">tune</i>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate module', 'text_domain' ); ?>">filter_none</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove module', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <#
                      var module_name = !_.isEmpty( data.module_name ) ? data.module_name + ' ' + '<?php _e("module", "text_domain"); ?>' : '<?php _e("module", "text_domain"); ?>';
                    #>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-module" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type">{{module_name}}</div>
                      </div>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-wp-content">
                  <div class="sek-dyn-ui-wrapper sek-wp-content-dyn-ui">
                    <div class="sek-dyn-ui-inner">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-pencil-alt sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"></i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <span class="sek-dyn-ui-location-type" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <i class="fab fa-wordpress sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"> <?php _e( 'WordPress content', 'text_domain'); ?></i>
                    </span>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>
            <?php
        }
    }//class
endif;
?><?php
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
?><?php
if ( !class_exists( 'SEK_Front_Render_Css' ) ) :
    class SEK_Front_Render_Css extends SEK_Front_Render {
        // Fired in __construct()
        function _setup_hook_for_front_css_printing_or_enqueuing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'print_or_enqueue_seks_style'), PHP_INT_MAX );

            // wp_add_inline_style for global CSS
            add_action( 'wp_head', array( $this, 'sek_enqueue_global_css' ) );
            
        }


        // Can be fired :
        // 1) on wp_enqueue_scripts or wp_head
        // 2) when ajaxing, for actions 'sek-resize-columns', 'sek-refresh-stylesheet'
        function print_or_enqueue_seks_style( $skope_id = null ) {
            // when this method is fired in a customize preview context :
            //    - the skope_id has to be built. Since we are after 'wp', this is not a problem.
            //    - the css rules are printed inline in the <head>
            //    - we set to hook to wp_head
            //
            // when the method is fired in an ajax refresh scenario, like 'sek-refresh-stylesheet'
            //    - the skope_id must be passed as param
            //    - the css rules are printed inline in the <head>
            //    - we set the hook to ''

            // AJAX REQUESTED STYLESHEET
            if ( ( !is_null( $skope_id ) && !empty( $skope_id ) ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                if ( !isset($_POST['local_skope_id']) ) {
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => error missing local_skope_id');
                    return;
                }
                $local_skope_id = sanitize_text_field($_POST['local_skope_id']);

                 // Feb 2021 => for site template #478
                $local_skope_id = apply_filters( 'nb_set_skope_id_before_generating_local_front_css', $local_skope_id );

                $css_handler_instance = $this->_instantiate_css_handler( array( 'skope_id' => $skope_id, 'is_global_stylesheet' => NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) );
                $this->sek_get_global_css_for_ajax();
            }
            // in a front normal context, the css is enqueued from the already written file.
            else {
                // Feb 2021 => for site template #478
                $local_skope_id = apply_filters( 'nb_set_skope_id_before_generating_local_front_css', skp_build_skope_id() );

                // LOCAL SECTIONS STYLESHEET
                $this->_instantiate_css_handler( array( 'skope_id' => $local_skope_id ) );
                // GLOBAL SECTIONS STYLESHEET
                // Can hold rules for global sections and global styling
                $this->_instantiate_css_handler( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) );
            }
            $google_fonts_print_candidates = $this->sek_get_gfont_print_candidates( $local_skope_id );
            // GOOGLE FONTS
            if ( !empty( $google_fonts_print_candidates ) && get_option( NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS ) != 'on' ) {
                // When customizing we get the google font content
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    $this->sek_get_gfont_in_ajax( $google_fonts_print_candidates );
                } else {
                    // preload implemented for https://github.com/presscustomizr/nimble-builder/issues/629
                    if ( !skp_is_customizing() && sek_preload_google_fonts_on_front() ) {
                        add_action( 'wp_head', array( $this, 'sek_gfont_print_with_preload') );
                    } else {
                        // March 2020 added param display=swap => Ensure text remains visible during webfont load #572
                        wp_enqueue_style(
                            NIMBLE_GOOGLE_FONTS_STYLESHEET_ID,
                            sprintf( '//fonts.googleapis.com/css?family=%s&display=swap', $google_fonts_print_candidates ),
                            array(),
                            null,
                            'all'
                        );
                    }
                }
            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX && empty( $local_skope_id ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . ' => the skope_id should not be empty' );
            }
        }//print_or_enqueue_seks_style


        


        
        // @param params = array( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) )
        // fired @'wp_enqueue_scripts'
        private function _instantiate_css_handler( $params = array() ) {
            $params = wp_parse_args( $params, array( 'skope_id' => '', 'is_global_stylesheet' => false ) );

            // Print inline or enqueue ?
            $print_mode = Sek_Dyn_CSS_Handler::MODE_FILE;
            if ( is_customize_preview() ) {
              $print_mode = Sek_Dyn_CSS_Handler::MODE_INLINE;
            }
            // Which hook ?
            $fire_at_hook = '';
            if ( !defined( 'DOING_AJAX' ) && is_customize_preview() ) {
              $fire_at_hook = 'wp_head';
            }

            $css_handler_instance = new Sek_Dyn_CSS_Handler( array(
                'id'             => $params['skope_id'],
                'skope_id'       => $params['skope_id'],
                // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
                'is_global_stylesheet' => $params['is_global_stylesheet'],
                'mode'           => $print_mode,
                //these are taken in account only when 'mode' is 'file'
                'force_write'    => true, //<- write if the file doesn't exist
                'force_rewrite'  => is_user_logged_in() && current_user_can( 'customize' ), //<- write even if the file exists
                'hook'           => $fire_at_hook
            ));
            return $css_handler_instance;
        }
        

        // When ajaxing, the link#sek-gfonts-{$this->id} gets removed from the dom and replaced by this string
        // March 2020 added param display=swap => Ensure text remains visible during webfont load #572
        function sek_get_gfont_in_ajax( $print_candidates ) {
            if ( !empty( $print_candidates ) ) {
                printf('<link rel="stylesheet" id="%1$s" href="%2$s">',
                    esc_attr(NIMBLE_GOOGLE_FONTS_STYLESHEET_ID),
                    esc_url("//fonts.googleapis.com/css?family={$print_candidates}&display=swap")
                );
            }
        }

        // hook : wp_footer
        // fired on front only when not customizing
        // March 2020 preload implemented for https://github.com/presscustomizr/nimble-builder/issues/629
        // March 2020 added param display=swap => Ensure text remains visible during webfont load #572
        function sek_gfont_print_with_preload( $print_candidates = '' ) {
            // Check that current page has Nimble content before printing any Google fonts
            // For https://github.com/presscustomizr/nimble-builder/issues/649
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            // print candidates must be fetched when sek_preload_google_fonts_on_front()
            $print_candidates = $this->sek_get_gfont_print_candidates();

            if ( !empty( $print_candidates ) ) {
                ob_start();
                ?>
                nb_.preloadOrDeferAsset( { id : '<?php echo esc_attr(NIMBLE_GOOGLE_FONTS_STYLESHEET_ID); ?>', as : 'style', href : '//fonts.googleapis.com/css?family=<?php echo esc_attr($print_candidates); ?>&display=swap', scriptEl : document.currentScript } );
                <?php
                $script = ob_get_clean();
                wp_register_script( 'nb_preload_gfonts', '');
                wp_enqueue_script( 'nb_preload_gfonts' );
                wp_add_inline_script( 'nb_preload_gfonts', $script );
            }
        }



        // invoked when ajaxing during customization
        function sek_get_global_css_for_ajax() {
            // During customization, always rebuild the css from fresh values instead of relying on the saved option
            // because on first call we get the customized option value, but on another one quickly after, we get the current option value in the database
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                $global_css = $this->sek_build_global_options_inline_css();
                if ( is_string( $global_css ) && !empty( $global_css ) ) {
                    printf('<style id="%1$s">%2$s</style>', NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID, $global_css );
                }
            }
        }


        // hook wp_enqueue_script
        function sek_enqueue_global_css() {
            $global_css = get_option(NIMBLE_OPT_FOR_GLOBAL_CSS);
            // following https://developer.wordpress.org/reference/functions/wp_add_inline_script/#comment-5304
            wp_register_style( NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID, false );
            wp_enqueue_style( NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID );
            wp_add_inline_style( NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID, $global_css );
        }


        // Maybe update global inline style with a filter
        // @return a css string
        // This CSS is the one generated by global options like global text, global width, global breakpoint
        function sek_build_global_options_inline_css() {
              return apply_filters('nimble_set_global_inline_style', '');
        }


        //@return string
        // sek_model is passed when customizing in SEK_Front_Render_Css::print_or_enqueue_seks_style()
        function sek_get_gfont_print_candidates( $local_skope_id = null ) {
            // return the cache version if already set
            if ( 'not_set' !== Nimble_Manager()->google_fonts_print_candidates )
              return Nimble_Manager()->google_fonts_print_candidates;

            $local_skope_id = is_null( $local_skope_id ) ? apply_filters( 'maybe_set_skope_id_for_site_template_css', skp_build_skope_id() ) : $local_skope_id;
            // local sections
            $local_seks = sek_get_skoped_seks( $local_skope_id );
            // global sections
            $global_seks = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            // global options
            $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );

            $print_candidates = '';
            $ffamilies = array();

            // Let's build the collection of google fonts from local sections, global sections, global options
            if ( is_array( $local_seks ) && !empty( $local_seks['fonts'] ) && is_array( $local_seks['fonts'] ) ) {
                $ffamilies = $local_seks['fonts'];
            }
            if ( is_array( $global_seks ) && !empty( $global_seks['fonts'] ) && is_array( $global_seks['fonts'] ) ) {
                $ffamilies = array_merge( $ffamilies, $global_seks['fonts'] );
            }
            if ( is_array( $global_options ) && !empty( $global_options['fonts'] ) && is_array( $global_options['fonts'] ) ) {
                $ffamilies = array_merge( $ffamilies, $global_options['fonts'] );
            }

            // remove duplicate if any
            $ffamilies = array_unique( $ffamilies );

            if ( !empty( $ffamilies ) ) {
                $ffamilies = implode( "|", $ffamilies );
                $print_candidates = str_replace( '|', '%7C', $ffamilies );
                $print_candidates = str_replace( '[gfont]', '' , $print_candidates );
            }
            // cache now
            Nimble_Manager()->google_fonts_print_candidates = $print_candidates;
            return Nimble_Manager()->google_fonts_print_candidates;
        }
    }//class
endif;

?><?php
if ( !class_exists( '\Nimble\Sek_Simple_Form' ) ) :
class Sek_Simple_Form extends SEK_Front_Render_Css {

    private $form;
    private $fields;
    private $mailer;

    private $form_composition;

    function _setup_simple_forms() {
        //Hooks
        add_action( 'parse_request', array( $this, 'simple_form_parse_request' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_recaptcha_scripts' ), 0 );
        add_filter( 'body_class', array( $this, 'set_the_recaptcha_badge_visibility_class') );

        // Note : form input need to be prefixed to avoid a collision with reserved WordPress input
        // @see : https://stackoverflow.com/questions/15685020/wordpress-form-submission-and-the-404-error-page#16636051
        $this->form_composition = array(
            'nimble_simple_cf'              => array(
                'type'            => 'hidden',
                'value'           => 'nimble_simple_cf'
            ),
            'nimble_recaptcha_resp'   => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_skope_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_level_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_name' => array(
                'label'            => __( 'Name', 'text_doma' ),
                'required'         => true,
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_email' => array(
                'label'            => __( 'Email', 'text_doma' ),
                'required'         => true,
                'type'             => 'email',
                'wrapper_tag'      => 'div'
            ),
            'nimble_subject' => array(
                'label'            => __( 'Subject', 'text_doma' ),
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_message' => array(
                'label'            => __( 'Message', 'text_doma' ),
                'required'         => true,
                'additional_attrs' => array( 'rows' => "10", 'cols' => "50" ),
                'type'             => 'textarea',
                'wrapper_tag'      => 'div'
            ),
            'nimble_privacy' => array(
                'label'            => __( 'I have read and agree to the privacy policy.', 'text_doma' ),
                'type'             => 'checkbox',
                'required'         => true,
                'value'            => false,
                //'additional_attrs' => array( 'class' => 'sek-btn' ),
                'wrapper_tag'      => 'div',
                'wrapper_class'    => array( 'sek-form-field', 'sek-privacy-wrapper' )
            ),
            'nimble_submit' => array(
                'type'             => 'submit',
                'value'            => __( 'Submit', 'text_doma' ),
                'additional_attrs' => array( 'class' => 'sek-btn' ),
                'wrapper_tag'      => 'div',
                'wrapper_class'    => array( 'sek-form-field', 'sek-form-btn-wrapper' )
            )
        );
    }//_setup_simple_forms


    //@hook: parse_request
    function simple_form_parse_request() {
        if ( !isset( $_POST['nimble_simple_cf'] ) )
          return;

        // get the module options
        // we are before 'wp', so let's use the posted skope_id and level_id to get our $module_user_values
        $module_model = array();
        if ( isset( $_POST['nimble_skope_id'] ) && '_skope_not_set_' !== sanitize_text_field($_POST['nimble_skope_id']) ) {
            $local_sektions = sek_get_skoped_seks( sanitize_text_field($_POST['nimble_skope_id']) );
            if ( is_array( $local_sektions ) && !empty( $local_sektions ) ) {
            $sektion_collection = array_key_exists('collection', $local_sektions) ? $local_sektions['collection'] : array();
            }
            if ( is_array($sektion_collection) && !empty( $sektion_collection ) && isset( $_POST['nimble_level_id'] ) ) {
                $module_model = sek_get_level_model( sanitize_text_field($_POST['nimble_level_id']), $sektion_collection );
                $module_model = sek_normalize_module_value_with_defaults( $module_model );
            }
        } else {
            sek_error_log( __FUNCTION__ . ' => skope_id problem');
            return;
        }

        if ( empty( $module_model ) ) {
            sek_error_log( __FUNCTION__ . ' => invalid module model');
            return;
        }

        //update the form with the posted values
        foreach ( $this->form_composition as $name => $field ) {
            $form_composition[ $name ]                = $field;
            if ( isset( $_POST[ $name ] ) ) {
                $form_composition[ $name ][ 'value' ] = sanitize_text_field($_POST[ $name ]);
            }
        }
        //set the form composition according to the user's options
        $form_composition = $this->_set_form_composition( $form_composition, $module_model );
        //generate fields
        $this->fields = $this->simple_form_generate_fields( $form_composition );
        //generate form
        $this->form = $this->simple_form_generate_form( $this->fields, $module_model );

        //mailer
        $this->mailer = new Sek_Mailer( $this->form );
        $this->mailer->maybe_send( $form_composition, $module_model );
    }

    // Fired @hook wp_enqueue_scripts
    // @return void()
    function maybe_enqueue_recaptcha_scripts() {
        // enabled if
        // - not customizing
        // - global 'recaptcha' options has the following values
        //    - enabled === true
        //    - public_key entered
        //    - private_key entered
        // - the current page does not include a form in a local or global location
        if ( skp_is_customizing() || !sek_is_recaptcha_globally_enabled() || !sek_front_sections_include_a_form() )
          return;

        // @todo, we don't handle the case when reCaptcha is globally enabled but disabled for a particular form.

        $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
        $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();

        $url = add_query_arg(
            array( 'render' => esc_attr( $global_recaptcha_opts['public_key'] ) ),
            'https://www.google.com/recaptcha/api.js'
        );

        wp_enqueue_script( 'google-recaptcha', $url, array(), '3.0', true );
        add_action('wp_head', array( $this, 'print_recaptcha_inline_js'), PHP_INT_MAX );
    }

    // @hook wp_footer
    // printed only when sek_is_recaptcha_globally_enabled()
    // AND
    // sek_front_sections_include_a_form()
    function print_recaptcha_inline_js() {
        ob_start();
        ?>
              if ( sekFrontLocalized.recaptcha_public_key ) {
                !( function( grecaptcha, sitekey ) {
                    var recaptcha = {
                        execute: function() {
                            var _action = ( window.sekFrontLocalized && sekFrontLocalized.skope_id ) ? sekFrontLocalized.skope_id.replace( 'skp__' , 'nimble_form__' ) : 'nimble_builder_form';
                            grecaptcha.execute(
                                sitekey,
                                // see https://developers.google.com/recaptcha/docs/v3#actions
                                { action: _action }
                            ).then( function( token ) {
                                var forms = document.getElementsByTagName( 'form' );
                                for ( var i = 0; i < forms.length; i++ ) {
                                    var fields = forms[ i ].getElementsByTagName( 'input' );
                                    for ( var j = 0; j < fields.length; j++ ) {
                                        var field = fields[ j ];
                                        if ( 'nimble_recaptcha_resp' === field.getAttribute( 'name' ) ) {
                                            field.setAttribute( 'value', token );
                                            break;
                                        }
                                    }
                                }
                            } );
                        }
                    };
                    grecaptcha.ready( recaptcha.execute );
                })( grecaptcha, sekFrontLocalized.recaptcha_public_key );
              } else {
                if ( window.console && window.console.log ) {
                    console.log('Nimble Builder form error => missing reCAPTCHA key');
                }
              }
        <?php
        $script = ob_get_clean();
        wp_register_script( 'nb_recaptcha_js', '');
        wp_enqueue_script( 'nb_recaptcha_js' );
        wp_add_inline_script( 'nb_recaptcha_js', $script );
    }

    // @hook body_class
    public function set_the_recaptcha_badge_visibility_class( $classes ) {
        // Shall we print the badge ?
        // @todo : we don't handle the case when recaptcha badge is globally displayed but the current page has disabled recaptcha
        $classes[] = !sek_is_recaptcha_badge_globally_displayed() ? 'sek-hide-rc-badge' : 'sek-show-rc-badge';
        return $classes;
    }


    // Rendering
    // Invoked from the tmpl
    // @return string
    // @param module_options is the module level "value" property. @see tmpl/modules/simple_form_module_tmpl.php
    function get_simple_form_html( $module_model ) {
        // sek_error_log('$module_model ?', $module_model );
        // sek_error_log('$this->fields ?', $this->fields );
        // sek_error_log('$this->form ?', $this->form );
        // sek_error_log('$this->mailer ?', $this->mailer );
        // sek_error_log('$_POST ?', $_POST );
        $html         = '';
        //set the form composition according to the user's options
        $form_composition = $this->_set_form_composition( $this->form_composition, $module_model );
        //generate fields
        $fields       = isset( $this->fields ) ? $this->fields : $this->simple_form_generate_fields( $form_composition );
        //generate form
        $form         = isset( $this->form ) ? $this->form : $this->simple_form_generate_form( $fields, $module_model );

        $module_id = is_array( $module_model ) && array_key_exists('id', $module_model ) ? $module_model['id'] : '';
        ob_start();
        ?>
        <div id="sek-form-respond">
          <?php
            $echo_form = true;
            // When loading the page after a send attempt, focus on the module html element with a javascript animation
            // In this case, don't echo the form, but only the user defined message which should be displayed after submitting the form
            if ( !is_null( $this->mailer ) ) {
                // Make sure we target the right form if several forms are displayed in a page
                $current_form_has_been_submitted = isset( $_POST['nimble_level_id'] ) && sanitize_text_field($_POST['nimble_level_id']) === $module_id;

                if ( in_array( $this->mailer->get_status(), ['sent', 'not_sent', 'aborted'] ) && $current_form_has_been_submitted ) {
                    $echo_form = false;
                }
            }

            if ( !$echo_form ) {
                ob_start();
                ?>
                      nb_.listenTo( 'nb-jquery-loaded', function() {
                            jQuery( function($) {
                                var $elToFocusOn = $('div[data-sek-id="<?php echo esc_attr($module_id); ?>"]' );
                                if ( $elToFocusOn.length > 0 ) {
                                      var _do = function() {
                                          $('html, body').animate({
                                              scrollTop : $elToFocusOn.offset().top - ( $(window).height() / 2 ) + ( $elToFocusOn.outerHeight() / 2 )
                                          }, 'slow');
                                      };
                                      try { _do(); } catch(er) {}
                                }
                            });
                      });
                <?php
                $script = ob_get_clean();
                wp_register_script( 'nb_simple_form_js', '');
                wp_enqueue_script( 'nb_simple_form_js' );
                wp_add_inline_script( 'nb_simple_form_js', $script );

                $message = $this->mailer->get_message( $this->mailer->get_status(), $module_model );
                if ( !empty($message) ) {
                    $class = 'sek-mail-failure';
                    switch( $this->mailer->get_status() ) {
                          case 'sent' :
                              $class = 'sek-mail-success';
                          break;
                          case 'not_sent' :
                              $class = '';
                          break;
                          case 'aborted' :
                              $class = 'sek-mail-aborted';
                          break;
                    }
                    printf( '<div class="sek-form-message %1$s">%2$s</div>', esc_attr($class), wp_kses_post($message) );
                }
            } else {
                // If we're in the regular case ( not after submission ), echo the form
                echo $form;//The output is late escaped in tmpl/modules/simple_form_module_tmpl.php, as this function only returns the html content
            }
          ?>
        </div>
        <?php
        return ob_get_clean();
    }


    //set the fields to render
    private function _set_form_composition( $form_composition, $module_model = array() ) {

        $user_form_composition = array();
        if ( !is_array( $module_model ) ) {
              sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => ERROR : invalid module options array');
              return $user_form_composition;
        }
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $form_fields_options = empty( $module_user_values['form_fields'] ) ? array() : $module_user_values['form_fields'];
        $form_button_options = empty( $module_user_values['form_button'] ) ? array() : $module_user_values['form_button'];
        $form_submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        foreach ( $form_composition as $field_id => $field_data ) {
            //sek_error_log( '$field_data', $field_data );
            switch ( $field_id ) {
                case 'nimble_name':
                    if ( !empty( $form_fields_options['show_name_field'] ) && sek_is_checked( $form_fields_options['show_name_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['name_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['name_field_label'] );
                    }
                break;
                case 'nimble_subject':
                    if ( !empty( $form_fields_options['show_subject_field'] ) && sek_is_checked( $form_fields_options['show_subject_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['subject_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['subject_field_label'] );
                    }
                break;
                case 'nimble_message':
                    if ( !empty( $form_fields_options['show_message_field'] ) && sek_is_checked( $form_fields_options['show_message_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['message_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['message_field_label'] );
                    }
                break;
                case 'nimble_email':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['email_field_label'] );
                break;
                case 'nimble_privacy':
                    if ( !empty( $form_fields_options['show_privacy_field'] ) && sek_is_checked( $form_fields_options['show_privacy_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['privacy_field_required'] );
                        // prevent users running script in this field while customizing
                        $user_form_composition[$field_id]['label'] = sek_strip_script_tags_and_print_js_inline( $form_fields_options['privacy_field_label'], $module_model );
                        // Feb 2021 : now saved as a json to fix emojis issues
                        // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
                        // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
                        $user_form_composition[$field_id]['label'] = sek_maybe_decode_richtext( $user_form_composition[$field_id]['label'] );
                    }
                break;

                //'additional_attrs' => array( 'class' => 'sek-btn' ),
                case 'nimble_submit':
                    $user_form_composition[$field_id] = $field_data;
                    $visual_effect_class = '';
                    //visual effect classes
                    if ( array_key_exists( 'use_box_shadow', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['use_box_shadow'] ) ) {
                        $visual_effect_class = ' box-shadow';
                        if ( array_key_exists( 'push_effect', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['push_effect'] ) ) {
                            $visual_effect_class .= ' push-effect';
                        }
                    }
                    $user_form_composition[$field_id]['additional_attrs']['class'] = 'sek-btn' . $visual_effect_class;

                    // Feb 2021 : now saved as a json to fix emojis issues
                    // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
                    // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
                    $user_form_composition[$field_id]['value'] = sek_maybe_decode_richtext( $form_fields_options['button_text'] );
                break;
                case 'nimble_skope_id':
                    $user_form_composition[$field_id] = $field_data;
                    // When the form is submitted, we grab the skope_id from the posted value, because it is too early to build it.
                    // of course we don't need to set this input value when customizing.
                    $skope_id = '';
                    if ( !skp_is_customizing() ) {
                        $skope_id = isset( $_POST['nimble_skope_id'] ) ? sanitize_text_field($_POST['nimble_skope_id']) : sek_get_level_skope_id( $module_model['id'] );
                    }

                    // always use the posted skope_id
                    // => in a scenario in which we post the form several times, the skp_get_skope_id() won't be available after the first submit action
                    $user_form_composition[$field_id]['value'] = $skope_id;
                break;
                case 'nimble_level_id':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['value'] = $module_model['id'];
                break;
                // print the recaptcha input field if
                // 1) reCAPTCHA enabled in the global options AND properly setup with non empty keys
                // 2) reCAPTCHA enabled for this particular form
                case 'nimble_recaptcha_resp' :
                    if ( !skp_is_customizing() && sek_is_recaptcha_globally_enabled() && 'disabled' !== $form_submission_options['recaptcha_enabled'] ) {
                        $user_form_composition[$field_id] = $field_data;
                    }
                break;
                default:
                    $user_form_composition[$field_id] = $field_data;
                break;
            }

        }
        return $user_form_composition;
    }


    //generate the fields
    function simple_form_generate_fields( $form_composition = array() ) {
        $form_composition = empty( $form_composition ) ? $this->form_composition : $form_composition;
        $fields_ = array();
        $id_suffix = rand();
        foreach ( $form_composition as $name => $field ) {
            $field = wp_parse_args( $field, array( 'type' => 'text' ) );

            if ( class_exists( $class = '\Nimble\Sek_Input_' . ucfirst( $field['type'] ) ) ) {
                $fields_ [] = new Sek_Field (
                    new $class( array_merge( array( 'id_suffix'=> $id_suffix ), $field, array( 'name' => $name ) ) ),
                    $field
                );
            }
        }

        return $fields_;
    }


    //generate the fields
    function simple_form_generate_form( $fields, $module_model ) {
        $form   = new Sek_Form( [
            'action' => is_array( $module_model ) && !empty( $module_model['id']) ? '#' . $module_model['id'] :'#',
            'method' => 'post'
        ] );
        $form->add_fields( $fields );

        return $form;
    }
}//Sek_Simple_Form
endif;

?><?php
/*
*&
*
*/
if ( !class_exists( '\Nimble\Sek_Form' ) ) :
class Sek_Form {
    private $fields;
    private $attributes;

    // Sek_Form is instantiated from Sek_Simple_Form::simple_form_generate_form
    //$form   = new Sek_Form( [
    //     'action' => is_array( $module_model ) && !empty( $module_model['id']) ? $module_model['id'] :'#',
    //     'method' => 'post'
    // ] );
    public function __construct( $args = array() ) {
        $this->fields        = array();
        $this->attributes    = wp_parse_args( $args, array(
            'action' => '#',// the action attribute doesn't have to be specified when form data is sent to the same page
            // see https://developer.mozilla.org/en-US/docs/Learn/Forms/Sending_and_retrieving_form_data,
            // for the moment we use the parent module id, example : #__nimble__cd4d5b307a3b
            'method' => 'post'
            //TODO: add html callback
        ) );
    }

    public function add_field( Sek_Field $field ) {
        $this->fields[ sanitize_key( $field->get_input()->get_data('name') ) ] = $field;
    }

    public function add_fields( array $fields ) {
        foreach ($fields as $field) {
            $this->add_field( $field );
        }
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_field( $field_name ) {
        if ( is_array( $this->fields ) && array_key_exists(sanitize_key( $field_name ), $this->fields) ) {
            return $this->fields[ sanitize_key( $field_name ) ];
        }
        return null;
    }

    //make sure fields are well formed
    public function has_invalid_field() {
        $has_invalid_field = false;

        foreach ( $this->fields as $form_field ) {
            if ( false !== $has_invalid_field )
              continue;
            $input        = $form_field->get_input();
            $value        = $input->get_value();
            $filter       = $input->get_data( 'filter' );
            $can_be_empty = true !== $input->get_data( 'required' );

            if ( $can_be_empty && !$value ) {
                continue;
            }
            if ( $filter && !filter_var( $value, $filter ) ) {
                $has_invalid_field = $input->get_data('label');
                break;
            }
        }

        return $has_invalid_field;
    }

    public function get_attributes_html() {
        return implode( ' ', array_map(
            function ($k, $v) {
                return sanitize_key( $k ) .'="'. esc_attr( $v ) .'"';
            },
            array_keys( $this->attributes ), $this->attributes
        ) );
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $fields = '';

        foreach ($this->fields as $name => $field) {
            $fields .= $field;
        }

        // The form output is late escaped on rendering in tmpl\modules\simple_form_module_tmpl.php
        return sprintf('<form %1$s>%2$s</form>',
            $this->get_attributes_html(),
            $fields
        );
    }
}//Sek_Form
endif;











/*
* Field class definition
*
* label and/or wrapper + input field
*/
if ( !class_exists( '\Nimble\Sek_Field' ) ) :
class Sek_Field {
    private $input;
    private $data;

    public function __construct( Sek_Input_Interface $input, $args = array() ) {
        $this->input = $input;

        $this->data  = wp_parse_args( $args, [
            'wrapper_tag'         => '',
            'wrapper_class'       => array( 'sek-form-field' ),
            'label'               => '',
            //TODO: allow callbacks
            'before_label'        => '',
            'after_label'         => '',
            'before_input'        => '',
            'after_input'         => '',
        ]);
    }

    public function get_input() {
        return $this->input;
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $label = $this->data[ 'label' ];

        //label stuff
        if ( $label ) {
            if ( true == $this->input->get_data( 'required' ) ) {
                $label .= ' *';
                //$label .= ' ' . esc_html__( '(required)', 'text_doma' );
            }
            $label = sprintf( '%1$s<label for="%2$s">%3$s</label>%4$s',
                $this->data[ 'before_label' ],
                esc_attr( $this->input->get_data( 'id' ) ),
                wp_specialchars_decode($label),
                $this->data[ 'after_label' ]
            );
        }

        //the input
        if ( !empty($this->data['type']) && 'checkbox' === $this->data['type'] ) {
            $html = sprintf( '%s%s%s%s',
                $this->data[ 'before_input' ],
                $this->input,
                $this->data[ 'after_input' ],
                $label
            );
        } else {
            $html = sprintf( '%s%s%s%s',
                $label,
                $this->data[ 'before_input' ],
                $this->input,
                $this->data[ 'after_input' ]
            );
        }

        //any wrapper?
        if ( $this->data[ 'wrapper_tag' ] ) {
            $wrapper_tag   = tag_escape( $this->data[ 'wrapper_tag' ] );
            $wrapper_class = $this->data[ 'wrapper_class' ] ? ' class="'. implode( ' ', array_map('sanitize_html_class', $this->data[ 'wrapper_class' ] ) ) .'"' : '';

            $html = sprintf( '<%1$s%2$s>%3$s</%1$s>',
                $wrapper_tag,
                esc_attr($wrapper_class),
                $html
            );
        }

        return $html;
    }
}
endif;

?><?php
/*
*
* Input objects definition
*
*/
interface Sek_Input_Interface {
    public function sanitize( $value );
    public function escape( $value );
    public function get_value();
    public function set_value( $value );
    public function get_data( $data_key );
    public function html();
}

abstract class Sek_Input_Abstract implements Sek_Input_Interface {
    private $data;
    protected $attributes = array( 'id', 'name', 'required' );

    public function __construct( $args ) {
        //no name no party
        //TODO: raise exception
        if ( !isset( $args['name'] ) ) {
            error_log( __FUNCTION__ . ' => contact form input name not set' );
            return;
        }

        $defaults = array(
            'name'               => '',
            'id'                 => '',
            'id_suffix'          => null,
            'additional_attrs'   => array(),
            'sanitize_cb'        => array( $this, 'sanitize' ),
            'escape_cb'          => array( $this, 'escape' ),
            'required'           => false,
            'filter'             => '',
            'value'              => ''
        );

        $data = wp_parse_args( $args, $defaults );


        $data[ 'id_suffix' ]        = is_null( $data[ 'id_suffix' ] ) ? rand() : $data[ 'id_suffix' ];
        $data[ 'id' ]               = empty( $data[ 'id' ] ) ? $data[ 'name' ] : $data[ 'id' ];
        $data[ 'id' ]               = $data[ 'id' ] . $data[ 'id_suffix' ];
        $data[ 'additional_attrs' ] = is_array( $data[ 'additional_attrs' ] ) ? $data[ 'additional_attrs' ] : array();

        $this->data = $data;

        if ( $data[ 'value' ] ) {
            $this->set_value( $data[ 'value' ]  );
        }

    }

    public function sanitize( $value ) {
        return $value;
    }

    public function escape( $value ) {
        return esc_attr( $value );
    }


    public function get_value() {
        $data = (array)$this->data;
        $value = $this->data['escape_cb']( $data['value'] );
        if ( skp_is_customizing() ) {
            $field_name = $this->get_data('name');
            switch( $field_name ) {
                case 'nimble_name' :
                    $value = '';
                break;
                case 'nimble_email' :
                    $value = '';
                break;
                case 'nimble_subject' :
                    $value = '';
                break;
                // case 'nimble_message' :
                //     $value = __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', 'text-domain');
                // break;
            }
        }
        return $value;
    }


    public function set_value( $value ) {
        $this->data['value'] = $this->data['sanitize_cb']( $value );
    }

    public function get_data( $data_key ){
        return isset( $this->data[ $data_key ] ) ? $this->data[ $data_key ] : null;
    }

    public function get_attributes_html() {
        $attributes = array_merge(
            array_intersect_key(
                array_filter( $this->data ),
                array_flip( $this->attributes )
            ),
            $this->data[ 'additional_attrs' ]
        );
        if ( skp_is_customizing() ) {
            $attributes['value'] = array_key_exists('value', $attributes ) ? $attributes['value'] : '';
        }
        $attributes = array_map(
            function ($k, $v) {
                switch ( $k ) {
                  case 'value':
                      $v = $this->get_value();
                  break;
                  default:
                      $v = esc_attr( $v );
                  break;
                }
                // 'required' attribute doesn't need a value : <input name="nimble_email" id="nimble_email1163989492" type="text" required/>
                return 'required' === $k ? 'required' : sanitize_key( $k ) .'="'. $v .'"';
            },
            array_keys($attributes), $attributes
        );

        return implode( ' ', $attributes );
    }


    public function __toString() {
        return $this->html();
    }

}//end abstract class









if ( !class_exists( '\Nimble\Sek_Input_Basic' ) ) :
class Sek_Input_Basic extends Sek_Input_Abstract {

    public function __construct( $args ) {
        $this->attributes   = array_merge( $this->attributes, array( 'value', 'type' ) );
        parent::__construct( $args );
    }

    public function html() {
        return sprintf( '<input %s/>',
            $this->get_attributes_html()
        );
    }
}
endif;

if ( !class_exists( '\Nimble\Sek_Input_Hidden' ) ) :
class Sek_Input_Hidden extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args[ 'type' ]     = 'hidden';
        parent::__construct( $args );
    }
}
endif;

if ( !class_exists( '\Nimble\Sek_Input_Checkbox' ) ) :
class Sek_Input_Checkbox extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args[ 'type' ]     = 'checkbox';
        parent::__construct( $args );
    }
}
endif;


if ( !class_exists( '\Nimble\Sek_Input_Text' ) ) :
class Sek_Input_Text extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args               = is_array( $args ) ? $args : array();
        $args[ 'type' ]     = 'text';
        $args[ 'filter' ]   = FILTER_SANITIZE_STRING;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return sanitize_text_field( $value );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( !class_exists( '\Nimble\Sek_Input_Email' ) ) :
class Sek_Input_Email extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'text';
        $args[ 'filter' ] = FILTER_SANITIZE_EMAIL;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        if ( !is_email( $value ) ) {
            return '';
        }
        return sanitize_email($value);
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( !class_exists( '\Nimble\Sek_Input_URL' ) ) :
class Sek_Input_URL extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'url';
        $args[ 'filter' ] = FILTER_SANITIZE_URL;

        parent::__construct( $args );
    }

    public function sanitize($value) {
        return esc_url_raw( $value );
    }

    public function escape( $value ){
        return esc_url( $value );
    }
}
endif;


if ( !class_exists( '\Nimble\Sek_Input_Submit' ) ) :
class Sek_Input_Submit extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'submit';
        $args             = wp_parse_args($args, [
            'value' => esc_html__( 'Contact', 'text_doma' ),
        ]);

        parent::__construct( $args );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;



if ( !class_exists( '\Nimble\Sek_Input_Textarea' ) ) :
class Sek_Input_Textarea extends Sek_Input_Abstract {

    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'textarea';

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return wp_kses_post($value);
    }

    public function escape( $value ) {
        return $this->sanitize( $value );
    }


    public function html() {
        return sprintf( '<textarea %1$s>%2$s</textarea>',
            $this->get_attributes_html(),
            $this->get_value()
        );
    }
}
endif;
?><?php
/*
*
* Mailer class definition
*
*/
if ( !class_exists( '\Nimble\Sek_Mailer' ) ) :
class Sek_Mailer {
    private $form;
    private $status;
    private $messages;
    private $invalid_field = false;
    public $recaptcha_errors = '_no_error_';//will store array( 'endpoint' => $endpoint, 'request' => $request, 'response' => '' );

    public function __construct( Sek_Form $form ) {
        $this->form = $form;

        $this->messages = array(
            //status          => message
            //'not_sent'        => __( 'Message was not sent. Try Again.', 'text_doma'),
            //'sent'            => __( 'Thanks!Your message has been sent.', 'text_doma'),
            'aborted'         => __( 'Please supply correct information.', 'text_doma') //<-todo too much generic
        );
        $this->status = 'init';

        // Validate reCAPTCHA if submitted
        // When sek_is_recaptcha_globally_enabled(), the hidden input 'nimble_recaptcha_resp' is rendered with a value set to a token remotely fetched with a js script
        // @see print_recaptcha_inline_js
        // on submission, we get the posted token value, and validate it with a remote http request to the google api
        if ( isset( $_POST['nimble_recaptcha_resp'] ) ) {
            if ( !$this->validate_recaptcha( sanitize_text_field($_POST['nimble_recaptcha_resp']) ) ) {
                $this->status = 'recaptcha_fail';
                if ( sek_is_dev_mode() ) {
                    sek_error_log('reCAPTCHA failure', $this->recaptcha_errors );
                }
            }
        }
    }


    //@return bool
    private function validate_recaptcha( $recaptcha_token ) {
        $is_valid = false;
        $endpoint = 'https://www.google.com/recaptcha/api/siteverify';
        $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
        $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();
        // the user did not enter the key yet.
        // let's validate
        if ( empty($global_recaptcha_opts['private_key']) )
          return true;

        //$public = $global_recaptcha_opts['public_key'];
        $request = array(
            'body' => array(
                'secret' => $global_recaptcha_opts['private_key'],
                'response' => $recaptcha_token
            ),
        );

        // cache the recaptcha_errors
        $response = wp_remote_post( esc_url_raw( $endpoint ), $request );
        if ( is_array( $response ) ) {
            $maybe_recaptcha_errors = wp_remote_retrieve_body( $response );
            $maybe_recaptcha_errors = json_decode( $maybe_recaptcha_errors );
            $maybe_recaptcha_errors = is_object($maybe_recaptcha_errors) ? (array)$maybe_recaptcha_errors : $maybe_recaptcha_errors;
            if ( is_array( $maybe_recaptcha_errors ) && isset( $maybe_recaptcha_errors['error-codes'] ) && is_array( $maybe_recaptcha_errors['error-codes'] ) ) {
                $this->recaptcha_errors = implode(', ', $maybe_recaptcha_errors['error-codes'] );
            }

        }

        //sek_error_log('reCAPTCHA response ?', $response );
        // There
        if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
            $this->recaptcha_errors = sprintf( __('There was a problem when performing the reCAPTCHA http request.') );
            return $is_valid;
        }

        // At this point, we can check the score if there not already an error messages, like a re-submission problem for example
        if ( '_no_error_' === $this->recaptcha_errors ) {
            $response_body = wp_remote_retrieve_body( $response );
            $response_body = json_decode( $response_body, true );

            // see https://developers.google.com/recaptcha/docs/v3#score
            $score = isset( $response_body['score'] ) ? $response_body['score'] : 0;

            // get the user defined threshold
            // must be normalized to be 0 >= threshold >= 1
            $user_score_threshold = array_key_exists('score', $global_recaptcha_opts) ? $global_recaptcha_opts['score'] : 0.5;
            $user_score_threshold = !is_numeric( $user_score_threshold ) ? 0.5 : $user_score_threshold;
            $user_score_threshold = $user_score_threshold > 1 ? 1 : $user_score_threshold;
            $user_score_threshold = $user_score_threshold < 0 ? 0 : $user_score_threshold;
            $user_score_threshold = apply_filters( 'nimble_recaptcha_score_treshold', $user_score_threshold );

            $is_valid = $is_human = $user_score_threshold < $score;
            if ( !$is_valid ) {
                $this->recaptcha_errors = sprintf( __('Google reCAPTCHA returned a score of %s, which is lower than your threshold of %s.', 'text_dom' ), $score, $user_score_threshold );
            }
        }

        return $is_valid;
    }


    // Depending on the user options, some fields might exists in the $form object
    // We need to check their existence ( @see https://github.com/presscustomizr/nimble-builder/issues/399 )
    public function maybe_send( $form_composition, $module_model ) {
        // the captcha validation has been made on Sek_Mailer instantiation
        if ( 'recaptcha_fail' === $this->status ) {
            return;
        }

        //sek_error_log('$form_composition ??', $form_composition );
        //sek_error_log('$module_model ??', $module_model );
        //sek_error_log('$this->form ??', $form_composition , $this->form );

        $invalid_field = $this->form->has_invalid_field();
        if ( false !== $invalid_field ) {
            $this->status = 'aborted';
            $this->invalid_field = $invalid_field;
            return;
        }

        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        //<-allow html?->TODO: turn into option
        $allow_html     = true;

        $sender_email   = $this->form->get_field('nimble_email')->get_input()->get_value();

        // Define a default sender name + make sure the field exists
        // fixes https://github.com/presscustomizr/nimble-builder/issues/513
        $sender_name    = __('Someone', 'text_doma');
        $sender_name_is_set = false;
        if ( is_array( $form_composition ) && array_key_exists( 'nimble_name', $form_composition ) ) {
            $sender_name_candidate  = sprintf( '%1$s', $this->form->get_field('nimble_name')->get_input()->get_value() );
            if ( !empty( $sender_name_candidate ) ) {
                $sender_name = $sender_name_candidate;
                $sender_name_is_set = true;
            }
        }

        $sender_body_message = null === $this->form->get_field('nimble_message') ? '' : $this->form->get_field('nimble_message')->get_input()->get_value();

        if ( array_key_exists( 'recipients', $submission_options ) ) {
            $recipient      = $submission_options['recipients'];
        } else {
            $recipient      = get_option( 'admin_email' );
        }

        if ( array_key_exists( 'nimble_subject' , $form_composition ) ) {
            $subject = $this->form->get_field('nimble_subject')->get_input()->get_value();
        } else if ( $sender_name_is_set ) {
            $subject = sprintf( __( '%1$s sent a message from %2$s', 'text_doma' ), $sender_name, get_bloginfo( 'name' ) );
        } else {
            $subject = sprintf( __( 'Someone sent a message from %1$s', 'text_doma' ), get_bloginfo( 'name' ) );
        }



        // $sender_website = sprintf( __( 'Website: %1$s %2$s', 'text_doma' ),
        //     $this->form->get_field('website')->get_input()->get_value(),
        //     $allow_html ? '<br><br><br>': "\r\n\r\n\r\n"
        // );

        // the sender's email is written in the email's header reply-to field.
        // But it is also written inside the message body following this issue, https://github.com/presscustomizr/nimble-builder/issues/218
        $before_message = sprintf( '%1$s: %2$s &lt;%3$s&gt;', __('From', 'text_doma'), $sender_name, $sender_email );//$sender_website;
        $before_message .= sprintf( '<br>%1$s: %2$s', __('Subject', 'text_doma'), $subject );
        $after_message  = '';

        if ( array_key_exists( 'email_footer', $submission_options ) ) {
            // Feb 2021 : now saved as a json to fix emojis issues
            // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
            // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
            $email_footer = sek_maybe_decode_richtext( $submission_options['email_footer'] );
            $email_footer = sek_strip_script_tags( $email_footer );
        } else {
            $email_footer = sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                get_bloginfo( 'name' ),
                get_site_url( 'url' )
            );
        }

        if ( !empty( $sender_body_message ) ) {
            $sender_body_message = sprintf( '<br><br>%1$s: <br>%2$s',
                __('Message body', 'text_doma'),
                //$allow_html ? '<br><br>': "\r\n\r\n",
                $sender_body_message
            );
        }

        $body = sprintf( '%1$s%2$s%3$s%4$s%5$s',
            $before_message,
            $sender_body_message,
            $after_message,
            $allow_html ? '<br><br>--<br>': "\r\n\r\n--\r\n",
            $email_footer
        );

        $headers        = array();
        $headers[]      = $allow_html ? 'Content-Type: text/html' : '';
        $headers[]      = 'charset=UTF-8'; //TODO: maybe turn into option

        $headers[]      = sprintf( 'From: %1$s <%2$s>', $sender_name, $this->get_from_email() );
        $headers[]      = sprintf( 'Reply-To: %1$s <%2$s>', $sender_name, $sender_email );

        $this->status   = wp_mail( $recipient, $subject, $body, $headers ) ? 'sent' : 'not_sent';
    }



    public function get_status() {
        return $this->status;
    }


    public function get_message( $status, $module_model ) {
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        $submission_message = isset( $this->messages[ $status ] ) ? $this->messages[ $status ] : '';

        // the check with strlen( preg_replace('/\s+/' ... ) allow user to "hack" the custom submission message with a blank space
        // because if the field is empty it will fallback on the default value
        switch( $status ) {
            case 'not_sent' :
                if ( array_key_exists( 'failure_message', $submission_options ) && !empty( $submission_options['failure_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['failure_message'] ) ) ) {
                    $submission_message = $submission_options['failure_message'];
                }
            break;
            case 'sent' :
                if ( array_key_exists( 'success_message', $submission_options ) && !empty( $submission_options['success_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['success_message'] ) ) ) {
                    $submission_message = $submission_options['success_message'];
                }
            break;
            case 'aborted' :
                if ( array_key_exists( 'error_message', $submission_options ) && !empty( $submission_options['error_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['error_message'] ) ) ) {
                    $submission_message = $submission_options['error_message'];
                }
                if ( false !== $this->invalid_field ) {
                    $submission_message = sprintf( __( '%1$s : <strong>%2$s</strong>.', 'text-domain' ), $submission_message, $this->invalid_field );
                }
            break;
            case 'recaptcha_fail' :
                $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
                $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();
                if ( true === sek_booleanize_checkbox_val($global_recaptcha_opts['show_failure_message']) ) {
                    $submission_message = !empty($global_recaptcha_opts['failure_message']) ? $global_recaptcha_opts['failure_message'] : '';
                }
            break;
        }

        if ( '_no_error_' !== $this->recaptcha_errors && current_user_can( 'customize' ) ) {
              $submission_message .= sprintf( '<br/>%s : <i>%s</i>', __('reCAPTCHA problem (only visible by a logged in administrator )', 'text_doma'), $this->recaptcha_errors );
        }
        return $submission_message;
    }




    // inspired from wpcf7
    private function get_from_email() {
        $admin_email = get_option( 'admin_email' );
        $sitename    = strtolower( sanitize_text_field($_SERVER['SERVER_NAME']) );

        if ( in_array( $sitename, array( 'localhost', '127.0.0.1' ) ) ) {
            return $admin_email;
        }

        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }

        if ( strpbrk( $admin_email, '@' ) == '@' . $sitename ) {
            return $admin_email;
        }

        return 'wordpress@' . $sitename;
    }
}//Sek_Mailer
endif;












// inspired by wpcf7
function sek_simple_form_mail_template() {
    $template = array(
        'subject' =>
            sprintf( __( '%1$s: new contact request', 'text_doma' ),
                get_bloginfo( 'name' )
            ),
        'sender' => sprintf( '[your-name] <%s>', simple_form_from_email() ),
        'body' =>
            /* translators: %s: [your-name] <[your-email]> */
            sprintf( __( 'From: %s', 'text_doma' ),
                '[your-name] <[your-email]>' ) . "\n"
            /* translators: %s: [your-subject] */
            . sprintf( __( 'Subject: %s', 'text_doma' ),
                '[your-subject]' ) . "\n\n"
            . __( 'Message Body:', 'text_doma' )
                . "\n" . '[your-message]' . "\n\n"
            . '-- ' . "\n"
            /* translators: 1: blog name, 2: blog URL */
            . sprintf(
                __( 'This e-mail was sent from a contact form on %1$s (%2$s)', 'text_doma' ),
                get_bloginfo( 'name' ),
                get_bloginfo( 'url' ) ),
        'recipient' => get_option( 'admin_email' ),
        'additional_headers' => 'Reply-To: [your-email]',
        'attachments' => '',
        'use_html' => 0,
        'exclude_blank' => 0,
    );

    return $template;
}//simple_form_mail_template


?><?php

if ( !class_exists( '\Nimble\Sek_Nimble_Manager' ) ) :
  final class Sek_Nimble_Manager extends Sek_Simple_Form {}
endif;

function Nimble_Manager( $params = array() ) {
    return Sek_Nimble_Manager::get_instance( $params );
}

Nimble_Manager();
?>